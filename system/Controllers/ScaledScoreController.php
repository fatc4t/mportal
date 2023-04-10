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
    require './Model/ScaledScore.php';
    /**
     * 顧客コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class ScaledScoreController extends BaseController
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
            $scaledscore = new ScaledScore();      
            //print_r($_POST);           
            if($_GET["org_id"]){
                $org_id = $_GET["org_id"];
            }
            
            //print_r($_POST["data"]);
            if($_POST["data"]){ 
                $T_data = json_decode($_POST["data"],true);
                //print_r($T_data);
                foreach ($T_data as $key => $value) {
                    //echo "value:";
                    //print_r($value);
                    $scaledscore->addData_mst1601($org_id,$key,$value);
                }

            }
            if($_POST["mst1602list"]){
                $T_mst1602list = json_decode($_POST["mst1602list"],true);
                foreach ($T_mst1602list as $key => $value) {                    
                    $scaledscore->addData_mst1602($org_id,$key,$value);
                }                
                
            }
            //$prodcustomer->updatedata();
    
            $this->initialDisplay();
            $Log->trace("END   addInputAction");
        } 

//        public function delAction()
//        {
//            global $Log; // グローバル変数宣言
//            $Log->trace("START delAction");
//            $prodcustomer = new prodcustomer();      
//            //print_r($_GET);
//            if ($_GET["organization_id"]) {
//                $prodcustomer->deldata($_GET["organization_id"]);            
//                $this->initialDisplay();
//                $Log->trace("END   delAction");
//            }
//            $Log->trace("END delAction");
//        }
//        public function addRowAction(){
//            global $Log; // グローバル変数宣言
//            $Log->trace("START addRowAction");
//            $this->initialDisplay();
//            $Log->trace("END   addRowAction");              
//        }
//        public function custsearchAction(){
//            global $Log; // グローバル変数宣言
//            $Log->trace("START custsearchAction");
//            // 顧客モデルインスタンス化
//            $prodcustomer = new prodcustomer();
//            // 顧客一覧データ取得
//            $mst0101list        = $prodcustomer->search_getCustomerlist();
//            $mst0101_searchdata = $prodcustomer->search_getCustomersearchdata();
//            require_once './View/CustomerSearch.html';            
//            $Log->trace("END   custsearchAction"); 
//        }
//        public function searchProdAction(){
//            global $Log; // グローバル変数宣言
//            $Log->trace("START custsearchAction");
//            // 顧客モデルインスタンス化
//            $prodcustomer = new prodcustomer(); 
//            if($_GET["org_id"]){
//                $org_id = $_GET["org_id"];
//            }else {$this->initialDisplay();}
//            if($_GET["cust_cd"]){
//                $cust_cd = $_GET["cust_cd"];
//            } 
//            $mst1201 = [];
//            $mst1101 = [];
//            $mst0201 = [];
//            $data    = [];
//            if($_POST['data']){
//                $T_data = json_decode($_POST["data"],true);
//                for($i=0;$i<count($T_data);$i++){
//                    $data[] =  $T_data['"'.$i.'"'];
//                }
//            }
//            echo "post: ";
//            //print_r($_POST['data']);
//            echo "data: ";
//            //print_r($data);
//            $cond = [];
//            $cond["organization_id"] = $org_id;
//            //if($_POST["cond"]){
//            $mst0201 = $prodcustomer->getProdsearchdata($cond);
//                //echo "mst0201 row: ".count($mst0201);
//            //}
//            $mst1101 = $prodcustomer->getmst1101($org_id);
//            $mst1201 = $prodcustomer->getmst1201($org_id);
//             require_once './View/ProdSearch.html';            
//            $Log->trace("END   searchProdAction");            
//        }


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
            $scaledscore = new ScaledScore();


            $mst1601                = [];
            $mst1602list            = [];
            $mst1201                = [];
            $org_id_list            = [];
            $org_id                 = '0';

            if($_GET["org_id"]){
                $org_id = $_GET["org_id"];
            } 
            
            // 顧客一覧データ取得
            $org_id_list         = $scaledscore->getorg_detail();
            //print_r($org_id_list);
            if($org_id !== 0){
                //echo "search data org_id = $org_id";
                $mst1601             = $scaledscore->getmst1601($org_id);
                $mst1602list         = $scaledscore->getmst1602list($org_id);
                $mst1201             = $scaledscore->getmst1201($org_id);
            }
            
            //print_r($org_id_list);
            require_once './View/ScaledScorePanel.html';

            $Log->trace("END   initialDisplay");
        }
    }
