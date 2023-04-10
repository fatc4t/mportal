<?php
    /**
     * @file      モデルテスト用コントローラ
     * @author    USE Y.Sakata
     * @date      2016/08/18
     * @version   1.00
     * @note      モデルテスト用コントローラ
     */

    // BaseClassの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwDirectAccessController.php';
    // テスト対象モデル
    require './Model/AggregateLaborCosts.php';

    /**
     * 打刻管理コントローラクラス
     * @note   打刻用機器からのアクセスも対応する
     */
    class TestDriverController extends FwDirectAccessController
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
         * 打刻情報を保存
         * @return   なし
         */
        public function test1Action()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setEmbossingAction");

            $laborCosts = new AggregateLaborCosts();
            
            $userIDList = array(1 );
            $orgIDList  = array( );
            $startDate  = '2016/08/01';
            $endDate    = '2016/09/01';

            // スキーマ取得
            $_SESSION["SCHEMA"] = 'use';

            $ret = $laborCosts->getUserLaborAggregateData( $userIDList, $orgIDList, $startDate, $endDate, true, 3, true );
            
            var_dump($ret);
            
            $Log->trace("END   setEmbossingAction");
        }

        /**
         * 打刻情報を保存
         * @return   なし
         */
        public function test2Action()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setEmbossingAction");

            $laborCosts = new AggregateLaborCosts();
            
            $orgIDList = array(  );
            $startDate  = '2016/08/01';
            $endDate    = '2016/09/01';

            // スキーマ取得
            $_SESSION["SCHEMA"] = 'use';

            $ret = $laborCosts->getOrgLaborAggregateData( $orgIDList, $startDate, $endDate, 2, 3, true );
            
            var_dump($ret);
            
            $Log->trace("END   setEmbossingAction");
        }

        /**
         * 打刻情報を保存
         * @return   なし
         */
        public function test3Action()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setEmbossingAction");

            $laborCosts = new AggregateLaborCosts();
            
            $orgIDList = array(  );
            $startDate  = '2016/08/01';
            $endDate    = '2016/09/01';

            // スキーマ取得
            $_SESSION["SCHEMA"] = 'use';

            $ret = $laborCosts->getOrgLaborEmbossingLocationData( $orgIDList, $startDate, $endDate );
            
            var_dump($ret);
            
            $Log->trace("END   setEmbossingAction");
        }

    }
?>
