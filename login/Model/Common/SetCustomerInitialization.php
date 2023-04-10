<?php
    /**
     * @file    顧客管理 初期化用クラス(Model)
     * @author  K.Sakamoto
     * @date    2017/07/25
     * @version 1.00
     * @note    顧客管理で使用する初期データの設定について定義する
     */

    // DBAccessClass.phpを読み込む
    require_once 'Model/Common/DBAccess.php';

    /**
     * セキュリティクラス
     * @note    セキュリティ処理を共通で使用するモデルの処理を定義
     */
    class SetCustomerInitialization extends FwBaseClass
    {
        // DBアクセスクラス
        protected $DBA = null;    ///< DBアクセスクラス

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseClassのコンストラクタ
            parent::__construct();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // FwBaseClassのデストラクタ
            parent::__destruct();
        }
        
        /**
         * 顧客管理システムの初期化
         * @note     ログイン時に行う勤怠処理の初期化
         * @return   無
         */
        public function setCustomerInit()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setCustomerInit");

            // 顧客管理機能用のメニューリスト設定
            $this->setMenuList();

            $Log->trace("END setCustomerInit");
        }
        
        /**
         * 管理機能用のメニューリスト取得
         * @note     管理機能用のメニューリスト取得
         * @return   管理メニューパスと画面名のリスト
         */
        private function setMenuList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setMenuList");
            
            $_SESSION["C_MANAGEMENT_MENU"] = array(
                                                    SystemParameters::$V_C_CUSTOMER             => "顧客分類マスタ画面",
                                                    SystemParameters::$V_C_PRODCUSTOMER         => "顧客別商品売価マスタ画面",
                                                    SystemParameters::$V_C_CUSTCLASS            => "顧客分類マスタ画面",
                                                    SystemParameters::$V_C_RANK                 => "顧客ランクマスタ画面",
                                                    SystemParameters::$V_C_CUSTREMARKS          => "顧客備考マスタ画面",
                                                    SystemParameters::$V_C_RELATIONSHIP          => "続柄マスタ画面"
                                                );
            $_SESSION["C_TOP_MENU"]        = array(
                                                    SystemParameters::$V_C_CUSTOMER             => "顧客管理",
                                                );
            $Log->trace("END setMenuList");
        }
        
    }

?>
