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
    require './Model/Staff.php';
    /**
     * 顧客コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class StaffController extends BaseController
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
            $org_id = $_GET["org_id"];
            $staff= new staff();
            //print_r($_POST);
            //echo $org_id;
            if($_POST){
                if($_POST['new_data'] ){
                    $new_data = json_decode($_POST['new_data'],true);
                    $staff->add_mst0601($org_id,$new_data);
                }
                if($_POST['del_data'] ){
                    $del_data = json_decode($_POST['del_data'],true);
                    $staff->del_mst0601($org_id,$del_data);
                }
                if($_POST['upd_data'] ){
                    $upd_data = json_decode($_POST['upd_data'],true);
                    $staff->upd_mst0601($org_id,$upd_data);
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
            $staff = new staff();
            $data = [];
            if($_GET["org_id"]){
                $org_id = $_GET["org_id"];
                $data             = $staff->get_mst0601_data($org_id);
                $other['org_id']  = $org_id;
                $staff_size       = $staff->staff_size($org_id);
                $other['staffsize'] = $staff_size;
                $supp_detail       = $staff->getsupp($org_id);
                $other['supp_detail'] = $supp_detail;
            }            
              
            $title            = ["コード","担当者名","担当者カナ","所属店舗名","社員番号","区分","社員区分名"];
            $key              = ["staff_cd","staff_nm","staff_kn","supp_cd","employee_cd","employee_kbn","employee_nm"];
            $pr_key_id        = ["staff_cd"];
            $pr_key_list      = "staff_cd , employee_kbn";
            $addrow           = 1;
            $save             = 1;
            $controller       = 'Staff';
            $pagetitle        = 'マスタ管理　担当者マスタ';
            $other['org_detail'] = $staff->getorg_detail();
            require_once '../pagetemplate1/View/template2.html';

            $Log->trace("END   initialDisplay");
        }
    }
