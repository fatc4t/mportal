<?php
    /**
     * @file    トップメッセージ 初期化用クラス(Model)
     * @author  oota
     * @date    2017/02/02
     * @version 1.00
     * @note    トップメッセージで使用する初期データの設定について定義する
     */

    // DBAccessClass.phpを読み込む
    require_once 'Model/Common/DBAccess.php';

    /**
     * セキュリティクラス
     * @note    セキュリティ処理を共通で使用するモデルの処理を定義
     */
    class SetTopMessageInitialization extends FwBaseClass
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
         * トップメッセージの初期化
         * @note     ログイン時に行う処理の初期化
         * @param     $user_id   ユーザID
         * @return   無
         */
        public function setTopMessageInit()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setTopMessageInit");

            // 管理機能用のメニューリスト設定
            $this->setMenuList();
            // マスタごとの参照テーブル一覧設定
            $_SESSION["TM_ACCESS_AUTHORITY_TABLE"] = $this->getAccessAuthorityTableList();

            $Log->trace("END setTopMessageInit");
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
            
            $_SESSION["TM_TOP_MENU"]        = array(
                                                    SystemParameters::$V_TM_HOME                    => "トップメッセージ",
                                                );

            $Log->trace("END setMenuList");
        }
        
        /**
         * マスタごとの参照テーブル一覧取得
         * @note     マスタごとの参照テーブル一覧取得
         * @return   管理メニューパスとテーブルのリスト
         */
        private function getAccessAuthorityTableList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAccessAuthorityTableList");
            
            $ret = array(
                            SystemParameters::$V_TM_HOME             => "t_top_message",  // トップメッセージテーブル
                        );
            
            $Log->trace("END getAccessAuthorityTableList");
            return $ret;
        }
    }

?>
