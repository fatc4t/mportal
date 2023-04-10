<?php
    /**
     * @file      発注履歴画面コントローラ
     * @author    USE K.Kanda(media-craft.co.jp)
     * @date      2016/01/23
     * @version   1.00
     * @note      発注履歴画面
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // アクセス権限処理モデル
    require './Model/History.php';

    /**
     * 発注履歴画面コントローラクラス
     * @note   発注履歴画面に履歴データの表示を行う。
     */
    class HistoryController extends BaseController
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
         * 発注画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");

            $this->initialDisplay();
            $Log->trace("END   showAction");
        }
		/**
         * 発注履歴一覧の表示
         * @return    なし
         */
        public function listAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START listAction");

			$kbnArr[0] = "下書き";
			$kbnArr[1] = "発注済";

			$history = new History();
			$historyArr = $history->getHistoryList($_POST);

            $Log->trace("END   ilstAction");

			require_once("./View/HistoryList.html");

        }
        /**
         * 発注画面
         * @return   HTMLソース
         */
        private function initialDisplay()
        {

			       $kbnArr[0] = "下書き";
			          $kbnArr[1] = "発注済";



            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");
			$Log->trace("END   initialDisplay");

          $pha_id = $_SESSION['PHA_ID'];


            require_once './View/History.html';
        }
    }
?>
