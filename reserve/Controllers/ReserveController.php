<?php
    /**
     * @file    トップコントローラ
     * @author  スクリプト作者
     * @date    日付
     * @version バージョン
     * @note    注釈を記述
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';

    // Login処理モデル
    require './Model/Reserve.php';

    /**
     * トップコントローラ
     * @note   注釈を記述
     */
    class ReserveController extends BaseController
    {
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
         * 初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->info( "MSG_INFO_1000" );
            
            $this->initialDisplay();
        }

        /**
         * HOGE検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            
            $this->initialDisplay();
            $Log->trace("END   searchAction");
        }

        /**
         * HOGE登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            
            $this->initialDisplay();
            $Log->trace("END   addAction");
        }

        /**
         * HOGE修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            
            $this->initialDisplay();
            $Log->trace("END   modAction");
        }

        /**
         * HOGE削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            
            $this->initialDisplay();
            $Log->trace("END   delAction");
        }

        /**
         * 初期表示設定
         * @return    なし
         */
        private function initialDisplay()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START initialDisplay" );
            
            $reserve = new Reserve();
           
            require_once '../reserve/View/ReservePanel.html';

            $Log->trace("END   initialDisplay");
        }

    }

?>
