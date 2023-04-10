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
    require './Model/Rank.php';
    /**
     * 顧客コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class RankController extends BaseController
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
            $rank= new rank();
            if($_POST){
                if($_POST['new_data'] ){
                    $new_data = json_decode($_POST['new_data'],true);
                    $rank->add_mst8401($new_data);
                }
                if($_POST['del_data'] ){
                    $del_data = json_decode($_POST['del_data'],true);
                    $rank->del_mst8401($del_data);
                }
                if($_POST['upd_data'] ){
                    $upd_data = json_decode($_POST['upd_data'],true);
                    $rank->upd_mst8401($upd_data);
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
            $rank = new rank();            
            $data             = $rank->get_mst8401_data();
            $title            = ["コード","ランク名","得点倍率","区分","得点除外名"];
            $key              = ["cust_rank_cd","cust_rank_nm","cust_rank_prate","cust_rank_p_kbn","cust_rank_p_nm"];
            $pr_key_id        = ["cust_rank_cd"];
            $pr_key_list      = "cust_rank_cd , cust_rank_nm";
            $addrow           = 1;
            $save             = 1;
            $controller       = 'Rank';
            $pagetitle        = '顧客マスタ　顧客ランクマスタ';
            
            require_once '../pagetemplate1/View/template2.html';

            $Log->trace("END   initialDisplay");
        }
    }
