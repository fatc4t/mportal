<?php
    /**
     * @file    売上管理 初期化用クラス(Model)
     * @author  oota
     * @date    2016/06/30
     * @version 1.00
     * @note    売上で使用する初期データの設定について定義する
     */

    // DBAccessClass.phpを読み込む
    require_once 'Model/Common/DBAccess.php';

    /**
     * セキュリティクラス
     * @note    セキュリティ処理を共通で使用するモデルの処理を定義
     */
    class SetProfitInitialization extends FwBaseClass
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
         * 売上管理システムの初期化
         * @note     ログイン時に行う勤怠処理の初期化
         * @param     $user_id   ユーザID
         * @return   無
         */
        public function setProfitInit()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setProfitInit");

            // 管理機能用のメニューリスト設定
            $this->setMenuList();
            // マスタごとの参照テーブル一覧設定
            $_SESSION["P_ACCESS_AUTHORITY_TABLE"] = $this->getAccessAuthorityTableList();

            $Log->trace("END Profit");
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
            
            $_SESSION["P_MANAGEMENT_MENU"] = array(
                                                    SystemParameters::$V_P_POS_BRAND              => "POS種別名",
                                                    SystemParameters::$V_P_POS_KEY_FILE           => "POSキーファイル",
                                                    SystemParameters::$V_P_MAPPING_NAME           => "マッピング名",
                                                    SystemParameters::$V_P_POS_MAPPING            => "日次マッピング設定",
                                                    SystemParameters::$V_P_ITEM_MAPING            => "商品別マッピング設定",
                                                    SystemParameters::$V_P_TIME_MAPPING           => "時間別マッピング設定",
                                                    SystemParameters::$V_P_REPORT_FORM            => "報告書設定",
                                                    SystemParameters::$V_P_LEDGER_SHEET_FORM      => "帳票設定",
                                                    SystemParameters::$V_P_VOID                   => "VOID設定",
                                                    SystemParameters::$V_P_TAX_RATE               => "税率設定",
                                                    SystemParameters::$V_P_TIME_ZONE              => "時間帯設定",
                                                    SystemParameters::$V_P_DEPARTMENT_CLASSIFICATION             => "部門分類マスタ",
                                                    SystemParameters::$V_P_DEPARTMENT             => "部門マスタ",
                                                    SystemParameters::$V_P_ITEM_CLASSIFICATION    => "商品分類マスタ",
                                                    SystemParameters::$V_P_ITEM_DIVISION          => "商品区分マスタ",
                                                    SystemParameters::$V_P_ITEM                   => "商品マスタ",
                                                    SystemParameters::$V_P_MST0201                => "POS商品マスタ",
                                                    SystemParameters::$V_P_MST0211                => "POS予約商品マスタ",
                                                    SystemParameters::$V_P_MST1201                => "POS商品部門マスタ",
                                                    SystemParameters::$V_P_MST1101                => "POS仕入先マスタ",
                                                    SystemParameters::$V_P_MST5101                => "POS商品セット構成マスタ",
                                                    SystemParameters::$V_P_MST1205                => "POS商品部門分類マスタ",
                                                    SystemParameters::$V_P_MST5501                => "POS商品自社分類マスタ",
                                                    SystemParameters::$V_P_MST5401                => "POS商品JICFS分類マスタ",
                                                    SystemParameters::$V_P_MST1001                => "POS商品メーカーマスタ",
                                                    SystemParameters::$V_P_MST0901                => "POS商品区分マスタ",
                                                    SystemParameters::$V_P_MST0801                => "POS商品分類マスタ",
                                                    SystemParameters::$V_P_MST1301                => "POS商品特売マスタ",
                                                    SystemParameters::$V_P_MST5102                => "POS商品入数構成マスタ",
                                                    SystemParameters::$V_P_MST5301                => "POS商品バンドルマスタ",
                                                    SystemParameters::$V_P_MST5201                => "POS商品ミックスマッチマスタ",
                                                    SystemParameters::$V_P_MST1401                => "POS商品期間原価マスタ",
                                                );
            $_SESSION["REPORT_MENU"]       = array(
                                                    SystemParameters::$V_P_SALES_REPORT_DAY       => "日次報告書 - 売上報告書",
                                                    SystemParameters::$V_P_ORDER_REPORT_DAY       => "日次報告書 - 仕入れ",
                                                    SystemParameters::$V_P_COST_REPORT_MONTH      => "月次報告書 - コスト",
                                                    SystemParameters::$V_P_TETGET_REPORT_DAY      => "目標       - 日次",
                                                    SystemParameters::$V_P_TETGET_REPORT_MONTH    => "目標       - 月次",
                                                );
            $_SESSION["LEDGER_SHEET_MENU"]       = array(
                                                    SystemParameters::$V_P_LEDGER_SHEET_DAY         => "帳票",
                                                    SystemParameters::$V_P_LEDGER_SHEET_STOCKPILE   => "帳票1",
                                                    SystemParameters::$V_P_LEDGER_SHEET_ABNORMALITY => "帳票2",
                                                    SystemParameters::$V_P_LEDGER_SHEET_ORDER       => "帳票3",
                                                    SystemParameters::$V_P_LEDGER_SHEET_DETAILS     => "帳票4",
                                                    SystemParameters::$V_P_LEDGER_SHEET_SALED       => "帳票5",
                                                    SystemParameters::$V_P_LEDGER_SHEET_UNPOPULAR   => "帳票6",
                                                    SystemParameters::$V_P_LEDGER_SHEET_PRODUCT     => "帳票7",
                                                    SystemParameters::$V_P_LEDGER_SHEET_CLIENT      => "帳票8",
                                                    SystemParameters::$V_P_LEDGER_SHEET_RANK        => "商品実績順位表",
                                                    SystemParameters::$V_P_LEDGER_SHEET_STOCKLIST   => "在庫一覧表(明細一覧表)",
                                                    SystemParameters::$V_P_LEDGER_SHEET_STOCKTOTAL  => "在庫一覧表(合計表)",
                                                );
            $_SESSION["P_TOP_MENU"]        = array(
                                                    SystemParameters::$V_P_TOP                    => "売上トップ",
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
                            SystemParameters::$V_P_POS_BRAND             => "m_pos_brand",                  // POS種別名
                            SystemParameters::$V_P_POS_KEY_FILE          => "m_pos_key_file",               // POSキーファイル
                            SystemParameters::$V_P_MAPPING_NAME          => "m_mapping_name",               // マッピング名
                            SystemParameters::$V_P_POS_MAPPING           => "m_pos_mapping",                // 日次マッピング設定
                            SystemParameters::$V_P_ITEM_MAPING           => "m_item_mapping",               // 商品別マッピング設定
                            SystemParameters::$V_P_TIME_MAPPING          => "m_time_mapping",               // 時間別マッピング設定
                            SystemParameters::$V_P_REPORT_FORM           => "m_profit_report_form",         // 報告書設定
                            SystemParameters::$V_P_LEDGER_SHEET_FORM     => "m_profit_ledger_sheet_form",   // 帳票設定
                            SystemParameters::$V_P_VOID                  => "m_void",                       // VOID設定
                            SystemParameters::$V_P_TAX_RATE              => "m_profit_tax_rate",            // 税率マスタ
                            SystemParameters::$V_P_TIME_ZONE             => "m_profit_time_zone",           // 時間帯設定
                            SystemParameters::$V_P_DEPARTMENT_CLASSIFICATION            => "m_profit_department",          // 部門分類マスタ
                            SystemParameters::$V_P_DEPARTMENT            => "m_profit_department",          // 部門マスタ
                            SystemParameters::$V_P_ITEM_CLASSIFICATION   => "m_profit_item",                // 商品分類マスタ
                            SystemParameters::$V_P_ITEM_DIVISION         => "m_profit_item",                // 商品区分マスタ
                            SystemParameters::$V_P_ITEM                  => "m_profit_item",                // 商品マスタ
                            SystemParameters::$V_P_MST0201               => "mst0201",                      // POS商品マスタ
                            SystemParameters::$V_P_MST0211               => "mst0211",                      // POS予約商品マスタ
                            SystemParameters::$V_P_MST1201               => "mst1201",                      // POS商品部門マスタ
                            SystemParameters::$V_P_MST1101               => "mst1101",                      // POS仕入先マスタ
                            SystemParameters::$V_P_MST5101               => "mst5101",                      // POS商品セット構成マスタ
                            SystemParameters::$V_P_MST1205               => "mst1205",                      // POS商品部門分類マスタ
                            SystemParameters::$V_P_MST5501               => "mst5501",                      // POS商品自社分類マスタ
                            SystemParameters::$V_P_MST5401               => "mst5401",                      // POS商品JICFS分類マスタ
                            SystemParameters::$V_P_MST1001               => "mst1001",                      // POS商品メーカーマスタ
                            SystemParameters::$V_P_MST0901               => "mst0901",                      // POS商品区分マスタ
                            SystemParameters::$V_P_MST0801               => "mst0801",                      // POS商品分類マスタ
                            SystemParameters::$V_P_MST1301               => "mst1301",                      // POS商品特売マスタ
                            SystemParameters::$V_P_MST5102               => "mst5102",                      // POS商品入数構成マスタ
                            SystemParameters::$V_P_MST5301               => "mst5301",                      // POS商品バンドルマスタ
                            SystemParameters::$V_P_MST5201               => "mst5201",                      // POS商品ミックスマッチマスタ
                            SystemParameters::$V_P_MST1401               => "mst1401",                      // POS商品期間原価マスタ
                        );
            
            $Log->trace("END getAccessAuthorityTableList");
            return $ret;
        }
    }

?>
