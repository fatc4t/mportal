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
    require './Model/Memo.php';
    /**
     * 顧客コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class MemoController extends BaseController
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
         * 顧客一覧初期表示
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

        public function addInputAction(){
            global $Log; // グローバル変数宣言
            $Log->trace("START addInputAction");
            $org_id = $_GET["org_id"];
            $receipt_cd = $_GET["receipt_cd"];
            $memo= new memo();
            //print_r($_POST);
            //echo $org_id;
            if($_POST){
                if($_POST['upd'] === "upd" ){
                    $memo->upd_mst6201($org_id,$receipt_cd,$_POST);
                }
                else{
                    $memo->add_mst6201($org_id,$receipt_cd,$_POST);
                }
            }
            $this->initialDisplay();
        }
        
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delAction");
            $memo= new memo();      
            //print_r($_GET);
            if ($_GET["org_id"] && $_GET["receipt_cd"] !== "") {
                echo "here";
                $memo->del_mst6201($_GET["org_id"], $_GET["receipt_cd"]);            
            }
            $this->initialDisplay();
            $Log->trace("END delAction");
        }        

//        /**
//         * 顧客一覧
//         * @note     パラメータは、View側で使用
//         * @param    $startFlag (初期表示時フラグ)
//         * @return   無
//         */
        private function initialDisplay()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");
            $memo = new memo();
            $data = [];
            $upd = "";
            $org_id_list = "";
            $mst6201 = [];
            $mst6201list = [];
            if($_GET["org_id"]){
                $org_id      = $_GET["org_id"];
                $mst6201list    = $memo->get_mst6201_lst($org_id,$receipt_cd);
                if($_GET["receipt_cd"]){
                    $receipt_cd     = $_GET["receipt_cd"];
                    $mst6201        = $memo->get_mst6201_data($org_id,$receipt_cd);
                    if($mst6201){
                        $upd = "upd";
                    }
				}
            }            
              
            $pagetitle        = 'マスタ管理　担当者マスタ画面';
            $org_id_list = $memo->getorg_detail();
            require_once './View/MemoPanel.html';

            $Log->trace("END   initialDisplay");
        }
    }
