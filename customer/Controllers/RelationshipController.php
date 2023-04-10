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
    require './Model/Relationship.php';
    /**
     * 顧客コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class RelationshipController extends BaseController
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
            $relationship = new relationship();
            if($_POST){
                if($_POST['new_data'] ){
                    $new_data = json_decode($_POST['new_data'],true);
                    $relationship->add_relationship($new_data);
                }
                if($_POST['del_data'] ){
                    $del_data = json_decode($_POST['del_data'],true);
                    $relationship->del_relationship($del_data);
                }
                if($_POST['upd_data'] ){
                    $upd_data = json_decode($_POST['upd_data'],true);
                    $relationship->upd_relationship($upd_data);
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
            $relationship = new relationship();            
            $data             = $relationship->get_relationship_data();
            $title            = ["コード","続柄名"];
            $key              = ["relation_cd","relation_nm"];
            $pr_key_id        = ["relation_cd"];
            $pr_key_list      = "relation_cd";
            $addrow           = 1;
            $save             = 1;
            $controller       = 'Relationship';
            $pagetitle        = '顧客マスタ　続柄';
            
            require_once '../pagetemplate1/View/template2.html';

            $Log->trace("END   initialDisplay");
        }
    }
