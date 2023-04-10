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
    class SetSystemInitialization extends FwBaseClass
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
        public function setSystemInit()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setSystemInit");

            // 顧客管理機能用のメニューリスト設定
            $this->setMenuList();

            $Log->trace("END setSystemInit");
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
            
            $_SESSION["S_MANAGEMENT_MENU"] = array(
                                                    SystemParameters::$V_S_SYSTEM             => "システムマスタ画面",
                                                    SystemParameters::$V_S_SCALEDSCORE        => "得点倍率マスタ画面",
                                                    SystemParameters::$V_S_CREDIT             => "クレジットマスタ画面",
                                                    SystemParameters::$V_S_GIFT               => "商品券マスタ画面",
                                                    SystemParameters::$V_S_STAFF              => "担当者マスタ画面",
                                                    SystemParameters::$V_S_DEPWITHDRAW        => "入出金マスタ画面",
                                                    SystemParameters::$V_S_MEMO               => "レシートメモマスタ",
                                                    SystemParameters::$V_S_TIMEZONE           => "時間帯マスタ"
                                                );
            $_SESSION["S_TOP_MENU"]        = array(
                                                    SystemParameters::$V_S_TOP                => "システム",
                                                );
            $Log->trace("END setMenuList");
        }
        
    }

?>
