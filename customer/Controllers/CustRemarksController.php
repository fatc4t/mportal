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
    require './Model/CustRemarks.php';
    /**
     * 顧客コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class CustRemarksController extends BaseController
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

        public function changeinputAction(){
            global $Log; // グローバル変数宣言
            $Log->trace("START changeinputAction");
            $custremarks = new custremarks();
            if($_GET["org_id"]){
                $org_id = $_GET["org_id"];
                if($_POST){
//print_r(json_decode($_POST['new_data'],true));
                        if($_POST['new_data'] ){
                            $new_data = json_decode($_POST['new_data'],true);
                            foreach ($new_data as $key => $value) {
                                $custremarks->add_mst0701($org_id,$key,$value);
                            }
                        }
                        if($_POST['del_data'] ){
                            $del_data = json_decode($_POST['del_data'],true);
                            foreach ($del_data as $key => $value) {
                                $custremarks->del_mst0701($org_id,$key,$value);
                            }
                        } 
                        if($_POST['upd_data'] ){
                            $upd_data = json_decode($_POST['upd_data'],true);
                            foreach ($upd_data as $key => $value) {
                                $custremarks->upd_mst0701($org_id,$key,$value);
                            }
                        }                        
//                        if($_POST['del_data'] ){
//                            $del_data = json_decode($_POST['del_data'],true);
//                            $custremarks->del_mst0701($del_data);
//                        }
//                        if($_POST['upd_data'] ){
//                            $upd_data = json_decode($_POST['upd_data'],true);
//                            $custremarks->upd_mst0701($upd_data);
//                        }
                    
                    if($_POST['mst0010_upd'] ){
                        $mst0010_upd = json_decode($_POST['mst0010_upd'],true);
                        $custremarks->upd_mst0010($org_id,$mst0010_upd);
                    }                
                }
            }
            $this->initialDisplay();
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
            $custremarks = new custremarks(); 
            $mst0701 = [];
            $mst0010 = [];
            if($_GET["org_id"]){
                $org_id = $_GET["org_id"];            
                $mst0701          = $custremarks->get_mst0701_data();
                $mst0010          = $custremarks->get_mst0010_data($org_id);
            }
            $title            = ["コード","顧客備考名"];
            $key              = ["cust_b_cd","cust_b_nm"];
            $pr_key_id        = ["cust_b_cd"];
            $pr_key_list      = "cust_b_cd";
            $addrow           = 1;
            $save             = 1;
            $controller       = 'CustRemarks';
            $pagetitle        = '顧客マスタ　顧客備考マスタ';
            $org_id_list         = $custremarks->getorg_detail();
            
            require_once './View/CustRemarksPanel.html';

            $Log->trace("END   initialDisplay");
        }
    }
