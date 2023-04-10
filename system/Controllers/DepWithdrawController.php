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
    require './Model/DepWithdraw.php';
    /**
     * 顧客コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class DepWithdrawController extends BaseController
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
            $depwithdraw = new depwithdraw();
            if($_GET["org_id"]){
                $org_id = $_GET["org_id"];
                if($_POST){
//print_r(json_decode($_POST['new_data'],true));
                        if($_POST['new_data'] ){
                            $new_data = json_decode($_POST['new_data'],true);
                            foreach ($new_data as $key => $value) {
                                //print_r(str_replace('"',"",$key));
                                $depwithdraw->add_mst6401($org_id,str_replace('"',"",$key),$value);
                            }
                        }
                        if($_POST['del_data'] ){
                            $del_data = json_decode($_POST['del_data'],true);
                            foreach ($del_data as $key => $value) {
                                $depwithdraw->del_mst6401($org_id,str_replace('"',"",$key),$value);
                            }
                        } 
                        if($_POST['upd_data'] ){
                            $upd_data = json_decode($_POST['upd_data'],true);
                            foreach ($upd_data as $key => $value) {
                                $depwithdraw->upd_mst6401($org_id,str_replace('"',"",$key),$value);
                            }
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
            $depwithdraw = new depwithdraw(); 
            $mst6401 = [];
            $mst0010 = [];
            if($_GET["org_id"]){
                $org_id = $_GET["org_id"];            
                $mst6401          = $depwithdraw->get_mst6401_data($org_id);
            }
            $title            = ["コード","名称"];
            $key              = ["acct_in_out_cd","acct_in_out_name"];
            $pr_key_id        = ["acct_in_out_cd"];
            $pr_key_list      = "acct_in_out_cd";
            $addrow           = 1;
            $save             = 1;
            $controller       = 'DepWithdraw';
            $pagetitle        = 'システム管理　入出金マスタ';
            $org_id_list         = $depwithdraw->getorg_detail();
            
            require_once './View/DepWithdrawPanel.html';

            $Log->trace("END   initialDisplay");
        }
    }
