<?php
    /**
     * @file      顧客コントローラ
     * @author    USE M.Higashihara
     * @date      2016/06/09
     * @version   1.00
     * @note      顧客の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 顧客モデル
    require './Model/ProdCustomer.php';
    /**
     * 顧客コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class ProdCustomerController extends BaseController
    {
        //private $scust_cd = "";
		 /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseControllerのコンストラクタ
            parent::__construct();
        }

         /**
         * デストラクタ
         */
        public function __destruct()
        {
            // BaseControllerのデストラクタ
            parent::__destruct();
        }

        /**
         * 顧客一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $_SESSION["PAGE_NO"] = 1;
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addInputAction");
            $prodcustomer = new prodcustomer();      
            //print_r($_POST);           
            if($_GET["org_id"]){
                $org_id = $_GET["org_id"];
            }
            if($_GET["cust_cd"]){
                $cust_cd = $_GET["cust_cd"];
            } 
            if($_POST["data"]){ 
                //case coming from pushed button 
                $T_data = json_decode($_POST["data"],true);
                for($i=0;$i<count($T_data);$i++){
                    if($T_data['"'.$i.'"']["state"] === "del" ){
                        $prodcustomer->deletedata($cust_cd,$org_id,$T_data['"'.$i.'"']['prod_cd']);
                    }elseif($T_data['"'.$i.'"']["state"] === "new" ){
                        $prodcustomer->insertdata($cust_cd,$org_id,$T_data['"'.$i.'"']['prod_cd'],$T_data['"'.$i.'"']['bycust_saleprice']);                        
                    }elseif($T_data['"'.$i.'"']["state"] === "upd" ){
                        $prodcustomer->updatedata($cust_cd,$org_id,$T_data['"'.$i.'"']['prod_cd'],$T_data['"'.$i.'"']['bycust_saleprice']);
                    }                    
                }
            }
            //$prodcustomer->updatedata();
    
            $this->initialDisplay(1);
            $Log->trace("END   addInputAction");
        } 

        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delAction");
            $prodcustomer = new prodcustomer();      
            //print_r($_GET);
            if ($_GET["organization_id"]) {
                $prodcustomer->deldata($_GET["organization_id"]);            
                $this->initialDisplay();
                $Log->trace("END   delAction");
            }
            $Log->trace("END delAction");
        }
        public function addRowAction(){
            global $Log; // グローバル変数宣言
            $Log->trace("START addRowAction");
            $this->initialDisplay();
            $Log->trace("END   addRowAction");              
        }
        public function custsearchAction(){
            global $Log; // グローバル変数宣言
            $Log->trace("START custsearchAction");
            // 顧客モデルインスタンス化
            $prodcustomer = new prodcustomer();
            // 顧客一覧データ取得
            $mst0101list        = $prodcustomer->search_getCustomerlist();
            $mst0101_searchdata = $prodcustomer->search_getCustomersearchdata();
            require_once './View/CustomerSearch.html';            
            $Log->trace("END   custsearchAction"); 
        }
        public function searchProdAction(){
            global $Log; // グローバル変数宣言
            $Log->trace("START custsearchAction");
            // 顧客モデルインスタンス化
            $prodcustomer = new prodcustomer(); 
            if($_GET["org_id"]){
                $org_id = $_GET["org_id"];
            }else {$this->initialDisplay();}
            if($_GET["cust_cd"]){
                $cust_cd = $_GET["cust_cd"];
            } 
            $mst1201 = [];
            $mst1101 = [];
            $mst0201 = [];
            $data    = [];
            if($_POST['data']){
                $T_data = json_decode($_POST["data"],true);
                for($i=0;$i<count($T_data);$i++){
                    $data[] =  $T_data['"'.$i.'"'];
                }
            }
//            echo "post: ";
//            print_r($_POST['data']);
//            echo "data: ";
//            print_r($data);
            $cond = [];
            $cond["organization_id"] = $org_id;
            //if($_POST["cond"]){
            $mst0201 = $prodcustomer->getProdsearchdata($cond);
                //echo "mst0201 row: ".count($mst0201);
            //}
            $mst1101 = $prodcustomer->getmst1101($org_id);
            $mst1201 = $prodcustomer->getmst1201($org_id);
             require_once './View/ProdSearch.html';            
            $Log->trace("END   searchProdAction");            
        }


//        /**
//         * 顧客一覧画面
//         * @note     パラメータは、View側で使用
//         * @param    $startFlag (初期表示時フラグ)
//         * @return   無
//         */
        private function initialDisplay($init=0)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");
            // 顧客モデルインスタンス化
            $prodcustomer = new prodcustomer();


            $mst0101list            = '';
            $mst0101                = '';
            $mst0201list            = [];
            $mst0201list['list']    = "";
            $mst0201                = '';
            $mst1701                = [];
            $org_detail             = '';
            $data                   = '';
            $org_id                 = '0';
            $cust_cd                = '';
            $data                   = [];
            if($_GET["org_id"]){
                $org_id = $_GET["org_id"];
            }
            if($_GET["cust_cd"]){
                $cust_cd = $_GET["cust_cd"];
            } 
           //print_r($_POST["data"]);
            if($_POST["data"] && $init === 0){ 
                //case coming from pushed button 
                $T_data = json_decode($_POST["data"],true);
                //print_r($T_data);
                for($i=0;$i<count($T_data);$i++){
                    $data[] =  $T_data['"'.$i.'"'];
                    if($T_data['"'.$i.'"']["state"] !== "del"){
                        
                        if($T_data['"'.$i.'"']["state"] === "old" ){
                            // case data fron mst1701 without change
                                                        
                            $temp = $prodcustomer->getmst1701($cust_cd,$org_id,$T_data['"'.$i.'"']["prod_cd"]);
                            //echo "mst1701 count = ".count($mst1701);
                            $mst1701[count($mst1701)] = $temp[0];
                            //echo "old + mst1701: ";
                            //print_r($mst1701);
                            
                        }else{                        
                            //case new entry or value changed (state = upd or ins)
                            $con = [];
                            $con['prod_cd']         = $T_data['"'.$i.'"']["prod_cd"];
                            $con['organization_id'] = $org_id;
                            //echo "con: ";
                            //print_r($con);
                            $res = $prodcustomer->getProdsearchdata($con)[0];
                            //echo "res: ";
                            //print_r($res);
                            $index = count($mst1701);
                            $mst1701[$index] = [];
                            $mst1701[$index]["prod_cd"]             = $con['prod_cd'];
                            $mst1701[$index]["prod_nm"]             = $res["prod_nm"];
                            $mst1701[$index]["upddatetime"]         = "";
                            $mst1701[$index]["head_costprice"]      = $res["head_costprice"];
                            $mst1701[$index]["saleprice"]           = $res["saleprice"];
                            $mst1701[$index]["bycust_saleprice"]    = $T_data['"'.$i.'"']["bycust_saleprice"];
                           // print_r( $mst1701[$index]);
                        }
                    }
                }                            
            }elseif($cust_cd !== '' && $org_id !== '0'){               
                // init case
                $mst1701     = $prodcustomer->getmst1701($cust_cd,$org_id);
                $data        = $prodcustomer->getdata($cust_cd,$org_id);
                $mst0201list = $prodcustomer->getProdlist($org_id);
                //print_r($data);
            }
            //echo "mst1701 final: ";
            //print_r($mst1701);
            $title            = ["商品コード","商品名","更新日","原価","通常売価","顧客別売価"];
            $key              = ["prod_cd","prod_nm","upddatetime","head_costprice","saleprice","bycust_saleprice"];
            $pr_key_id        = ["prod_cd",];
            $pr_key_list      = "prod_cd";
            $addrow           = 1;
            $save             = 1;
            $controller       = 'ProdCustomer';
            $pagetitle        = '顧客マスタ　顧客別商品売価マスタ';            
            

            // 顧客一覧データ取得
            $org_id_list = $prodcustomer->getorg_detail();
            //print_r($org_id_list);
            $mst0101list = $prodcustomer->getCustomerlist();
            
            //$mst0201list = $prodcustomer->getProdlist();
            $con = [];
            $con["cust_cd"] = $cust_cd;
            $mst0101     = $prodcustomer->getCustomersearchdata($con);
            //$mst0201     = $prodcustomer->getProdsearchdata();
            

            //print_r($org_id_list);
            require_once './View/ProdCustomerPanel.html';

            $Log->trace("END   initialDisplay");
        }
    }
