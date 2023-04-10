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
    require './Model/System.php';
    /**
     * 顧客コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class SystemController extends BaseController
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
            $system = new system();      
            //print_r($_POST);
            //echo "test:".$_POST[0];

            $system->updatedata();

            
            $this->initialDisplay();
            $Log->trace("END   addInputAction");
        } 

        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delAction");
            $system = new system();      
            //print_r($_GET);
            if ($_GET["organization_id"]) {
                $system->deldata($_GET["organization_id"]);            
                $this->initialDisplay();
                $Log->trace("END   delAction");
            }
            $Log->trace("END delAction");
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

            $mst0010            = '';
            $mst8401            = '';
            // 顧客モデルインスタンス化
            $system = new system();
            // 顧客一覧データ取得
            $org_id_list = $system->getorgidlist();
            //print_r($org_id_list);
            for($i=0;$i < count($org_id_list); $i++){
                
                $mst0010[$org_id_list[$i]["organization_id"]] = $system->getmst0010($org_id_list[$i]["organization_id"]);
            }
            //print_r($org_id_list);
            $mst8401 = $system->getrankdata();
            require_once './View/SystemPanel.html';

            $Log->trace("END   initialDisplay");
        }
    }
