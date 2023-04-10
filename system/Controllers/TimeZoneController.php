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
    require './Model/TimeZone.php';
    /**
     * 顧客コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class TimeZoneController extends BaseController
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
            $timezone = new timezone();
            if($_POST){
                if($_POST['new_data'] ){
                    $new_data = json_decode($_POST['new_data'],true);
                    $timezone->add_timezone($new_data);
                }
                if($_POST['del_data'] ){
                    $del_data = json_decode($_POST['del_data'],true);
                    $timezone->del_timezone($del_data);
                }
                if($_POST['upd_data'] ){
                    $upd_data = json_decode($_POST['upd_data'],true);
                    $timezone->upd_timezone($upd_data);
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
            $timezone = new timezone();
            $data  = [];
            $other = [];
            
            if($_GET["org_id"]){
                $org_id = $_GET["org_id"];
                $data             =$timezone->get_timezone_data($org_id);
                $other['org_id']  = $org_id;
                //$supp_detail       = $timezone->getsupp($org_id);
                //$other['supp_detail'] = $supp_detail;
            }             
            $title            = ["コード","開始時刻","終了時刻"];
            $key              = ["tmzone_cd","tmzone_str","tmzone_end"];
            $pr_key_id        = ["tmzone_cd"];
            $pr_key_list      = "tmzone_cd";
            $addrow           = 0;
            $save             = 0;
            $controller       = 'TimeZone';
            $pagetitle        = 'システム管理　 時間帯マスタ';
            $other['org_detail'] = $timezone->getorg_detail();
            
            require_once '../pagetemplate1/View/template2.html';

            $Log->trace("END   initialDisplay");
        }
    }
