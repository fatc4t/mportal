<?php
    /**
     * @file    勤怠管理 初期化用クラス(Model)
     * @author  USE Y.Sakata
     * @date    2016/06/27
     * @version 1.00
     * @note    勤怠で使用する初期データの設定について定義する
     */

    // DBAccessClass.phpを読み込む
    require_once 'Model/Common/DBAccess.php';

    /**
     * セキュリティクラス
     * @note    セキュリティ処理を共通で使用するモデルの処理を定義
     */
    class SetAttendanceInitialization extends FwBaseClass
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
         * 勤怠管理システムの初期化
         * @note     ログイン時に行う勤怠処理の初期化
         * @return   無
         */
        public function setAttendanceInit()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setAttendanceInit");

            // 勤怠管理機能用のメニューリスト設定
            $this->setMenuList();
            // 勤怠管理機能で使用するマスタごとの参照テーブル一覧設定
            $_SESSION["A_ACCESS_AUTHORITY_TABLE"] = $this->getAccessAuthorityTableList();

            $Log->trace("END setAttendanceInit");
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
            
            $_SESSION["A_MANAGEMENT_MENU"] = array(
                                                    SystemParameters::$V_A_SECTION                   => "セクションマスタ画面",
                                                    SystemParameters::$V_A_HOLIDAY_NAME              => "休日名称マスタ画面",
                                                    SystemParameters::$V_A_HOLIDAY                   => "休日マスタ画面",
                                                    SystemParameters::$V_A_ALLOWANCE                 => "手当マスタ画面",
                                                    SystemParameters::$V_A_LABOR_REGULATIONS         => "就業規則マスタ画面",
                                                    SystemParameters::$V_A_ORGANIZATION_CALENDAR     => "組織カレンダーマスタ画面",
                                                    SystemParameters::$V_A_ALERT                     => "アラートマスタ画面",
                                                    SystemParameters::$V_A_DISPLAY_ITEM              => "表示項目設定マスタ画面",
                                                    SystemParameters::$V_A_PAYROLL_SYSTEM            => "給与システム連携マスタ",
                                                    SystemParameters::$V_A_PAYROLL_DATA_OUT          => "給与データ出力画面",
                                                );
            $_SESSION["A_TIME_MENU"]       = array(
                                                    SystemParameters::$V_A_TIME_LIST                 => "勤怠一覧画面",
                                                    SystemParameters::$V_A_TIME_CORRECTION           => "勤怠修正画面",
                                                    SystemParameters::$V_A_ATTENDANCE_APPROVAL       => "勤怠承認画面",
                                                );
            $_SESSION["A_SHIFT_MENU"]      = array(
                                                    SystemParameters::$V_A_SHIFT_LIST                => "シフト一覧画面",
                                                    SystemParameters::$V_A_SHIFT_DL_UP               => "シフトインポート画面",
                                                );
            $_SESSION["A_TOP_MENU"]        = array(
                                                    SystemParameters::$V_A_TOP                       => "勤怠トップ",
                                                );

            $_SESSION["A_INPUT_MENU"]      = array(
                                                    SystemParameters::$V_A_M_LABOR_REGULATIONS       => "就業規則マスタ編集画面",
                                                    SystemParameters::$V_A_M_ORGANIZATION_CALENDAR   => "組織カレンダーマスタ編集画面",
                                                    SystemParameters::$V_A_M_DISPLAY_ITEM            => "表示項目設定マスタ編集画面",
                                                    SystemParameters::$V_A_M_PAYROLL_SYSTEM          => "給与システム連携マスタ編集画面",
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
                            SystemParameters::$V_A_SECTION               => "m_section",
                            SystemParameters::$V_A_HOLIDAY_NAME          => "m_holiday_name",
                            SystemParameters::$V_A_HOLIDAY               => "m_holiday",
                            SystemParameters::$V_A_ALLOWANCE             => "m_allowance",
                            SystemParameters::$V_A_LABOR_REGULATIONS     => "m_labor_regulations",
                            SystemParameters::$V_A_ORGANIZATION_CALENDAR => "m_organization_calendar",
                            SystemParameters::$V_A_DISPLAY_ITEM          => "m_display_item",
                            SystemParameters::$V_A_PAYROLL_SYSTEM        => "m_payroll_system_cooperation",
                            SystemParameters::$V_A_PAYROLL_DATA_OUT      => "",
                            SystemParameters::$V_M_ORGANIZATION          => "m_organization_detail",
                            SystemParameters::$V_M_POSITION              => "m_position",
                            SystemParameters::$V_M_EMPLOYMENT            => "m_employment",
                            SystemParameters::$V_M_USER                  => "m_user_detail",
                            SystemParameters::$V_M_SECURITY              => "m_security",
                        );
            
            $Log->trace("END getAccessAuthorityTableList");
            return $ret;
        }
    }

?>
