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
    class SetFileBoxInitialization extends FwBaseClass
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
        public function setFileBoxInit()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setFileBoxInit");

            // 管理機能用のメニューリスト設定
            $this->setMenuList();
            // マスタごとの参照テーブル一覧設定
            $_SESSION["P_ACCESS_AUTHORITY_TABLE"] = $this->getAccessAuthorityTableList();

            $Log->trace("END setFileBoxInit");
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
            
//            $_SESSION["P_MANAGEMENT_MENU"] = array(
//                                                    SystemParameters::$V_P_POS_BRAND              => "POS種別名",
//                                                    SystemParameters::$V_P_POS_KEY_FILE           => "POSキーファイル",
//                                                    SystemParameters::$V_P_MAPPING_NAME           => "マッピング名",
//                                                    SystemParameters::$V_P_POS_MAPPING            => "日次マッピング設定",
//                                                    SystemParameters::$V_P_ITEM_MAPING            => "商品別マッピング設定",
//                                                    SystemParameters::$V_P_TIME_MAPPING           => "時間別マッピング設定",
//                                                    SystemParameters::$V_P_REPORT_FORM            => "報告書設定",
//                                                    SystemParameters::$V_P_LEDGER_SHEET_FORM      => "帳票設定",
//                                                    SystemParameters::$V_P_VOID                   => "VOID設定",
//                                                    SystemParameters::$V_P_TAX_RATE               => "税率設定",
//                                                    SystemParameters::$V_P_TIME_ZONE              => "時間帯設定",
//                                                    SystemParameters::$V_P_DEPARTMENT             => "部門設定",
//                                                    SystemParameters::$V_P_MENU                   => "商品設定",
//                                                );
//            $_SESSION["REPORT_MENU"]       = array(
//                                                    SystemParameters::$V_P_SALES_REPORT_DAY       => "日次報告書 - 売上報告書",
//                                                    SystemParameters::$V_P_ORDER_REPORT_DAY       => "日次報告書 - 仕入れ",
//                                                    SystemParameters::$V_P_COST_REPORT_MONTH      => "月次報告書 - コスト",
//                                                    SystemParameters::$V_P_TETGET_REPORT_DAY      => "目標       - 日次",
//                                                    SystemParameters::$V_P_TETGET_REPORT_MONTH    => "目標       - 月次",
//                                                );
//            $_SESSION["LEDGER_SHEET_MENU"]       = array(
//                                                    SystemParameters::$V_P_LEDGER_SHEET_DAY       => "帳票",
//                                                );
            $_SESSION["TM_TOP_MENU"]        = array(
                                                    SystemParameters::$V_TM_HOME                    => "トップメッセージトップ",
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
