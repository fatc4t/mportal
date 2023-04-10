<?php
    /**
     * @file      時間別マッピング設定コントローラ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      時間別マッピング設定の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/PosMappingTime.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class PosMappingTimeController extends BaseController
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
         * 表示項目設定一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            require_once './View/PosMappingTimePanel.html';
        }

    }
?>
