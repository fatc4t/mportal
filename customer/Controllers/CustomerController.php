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
    require './Model/Customer.php';
    /**
     * 顧客コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class CustomerController extends BaseController
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
            $customer = new customer();      
            //print_r($_POST);
            
            if ($_POST["updins"] == "ins" ) {
                $customer->insertdata();
            }
            else{
                $customer->updatedata();
            }
            
            $customer->del_mst0103();
            $customer->add_mst0103();
            $this->initialDisplay();
            $Log->trace("END   addInputAction");
        }
        
        public function changeinputAction(){
            global $Log; // グローバル変数宣言
            $Log->trace("START changeinputAction");
            $customer = new customer();
            if($_POST){
                if($_POST['new_data'] ){
                    $new_data = json_decode($_POST['new_data'],true);
                    $customer->add_area($new_data);
                }
                if($_POST['del_data'] ){
                    $del_data = json_decode($_POST['del_data'],true);
                    $customer->del_area($del_data);
                }
                if($_POST['upd_data'] ){
                    $upd_data = json_decode($_POST['upd_data'],true);
                    $customer->upd_area($upd_data);
                }
            }
            $this->templateAction();
        }
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delAction");
            $customer = new customer();      
            //print_r($_GET);
            if ($_GET["custcd"]) {
                $customer->deldata($_GET["custcd"]);            
                $_GET["custcd"]="";
                $this->initialDisplay();
                $Log->trace("END   delAction");
            }
            $Log->trace("END delAction");
        }
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START searchAction");
            // 顧客モデルインスタンス化
            $customer = new customer();
            // 顧客一覧データ取得
            $mst0101list        = $customer->getCustomerlist();
            $mst0101_searchdata = $customer->getCustomersearchdata();
            require_once './View/CustomerSearch.html';
            $Log->trace("END searchAction");
        }
        public function templateAction(){
            $customer = new customer();            
            $data             = $customer->get_area_data("");
            $title            = ["コード","地区名","地区カナ"];
            $key              = ["area_cd","area_nm","area_kn"];
            $pr_key_id        = ["area_cd"];
            $pr_key_list      = "area_cd";
            $addrow           = 1;
            $save             = 1;
            $controller       = 'Customer';
            $pagetitle        = '顧客マスタ　地区';
            
            require_once '../pagetemplate1/View/template1.html';
        }
//        /**
//         * 顧客一覧画面
//         * @note     パラメータは、View側で使用
//         * @param    $startFlag (初期表示時フラグ)
//         * @return   無
//         */
        private function initialDisplay()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");
            $mst0101            = '';
            $mst0401            = '';
            $mst0501            = '';
            $mst0601            = '';
            $mst0701            = '';
            $mst6101            = '';
            $mst8401            = '';
            $jsk4110            = '';
            $jsk4120            = '';
            $jsk4130            = '';
            $jsk4140            = '';
            $mst0103            = '';
            $mst0101_sd         = '';
            $mst0101list        = '';
            $custsize           = '';
            // 顧客モデルインスタンス化
            $customer = new customer();
            // 顧客一覧データ取得
            $mst0101list        = $customer->getCustomerlist();
            $mst0101_searchdata = $customer->getCustomersearchdata();
            $custsize           = $customer->get_cust_size();
//print_r($mst0101_searchdata);
            if ($_GET["custcd"])    {$scust_cd = $_GET["custcd"];}
            if ($_POST["CUST_CD"])  {$scust_cd = $_POST["CUST_CD"];}
            //print_r($scust_cd);
            // 一覧表用検索項目
            if ($scust_cd != "" ){
                    $mst0101 		= $customer->getCustomerData($scust_cd);
                    if($mst0101["area_cd01"]){$area[]=$mst0101["area_cd01"];}
                    if($mst0101["area_cd02"]){$area[]=$mst0101["area_cd02"];}
                    $mst0501="";
                    if(count($area) > 0 ){
                        $mst0501	= $customer->get_area_data($area);
                    }
                    $jsk4110 		= $customer->get_jsk4110($scust_cd);
                    $jsk4120 		= $customer->get_jsk4120($scust_cd);
                    $jsk4130 		= $customer->get_table_data($scust_cd,'jsk4130');
                    $jsk4140 		= $customer->get_table_data($scust_cd,'jsk4140');
                    $mst0101_staff 	= $customer->get_table_data($scust_cd,'mst0101_staff','organization_id');
                    $mst0103 		= $customer->get_table_data($scust_cd,'mst0103','fam_seq');
                    $mst0401		= $customer->get_table_data($mst0101["cust_type_cd"],'mst0401');
                    $mst6101		= $customer->get_Relationship_data();
                    $mst8401            = $customer->get_rank_data($mst0101["cust_rank_cd"]);
                    $staff_search = "";
                    foreach($mst0101_staff as $key){
                        $staff_search .= "(".$key['organization_id'].",'".$key['staff_cd']."'),";
                    }
                    $staff_search=rtrim($staff_search,",");
                    if($staff_search){
                        $mst0601            = $customer->get_staff_data($staff_search);
                    }
           
                    for ($i=1;$i<11;$i++){
                        $col="cust_b_cd".sprintf('%02d', $i);
                        //echo  "mst0101[$col]: ".$mst0101[$col];
                        if($mst0101[$col]) $acust_b_cd[$col] = $mst0101[$col];					
                    }
                    $mst0701="";
                    if (count($acust_b_cd) > 0){
                        $mst0701		= $customer->get_note_data($acust_b_cd);
                    }
            }
            require_once './View/CustomerPanel.html';

            $Log->trace("END   initialDisplay");
        }
    }
