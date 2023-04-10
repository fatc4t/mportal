<?php
    /**
     * @file    勤怠用DBアクセスクラス
     * @author  USE Y.Sakata
     * @date    2016/06/23
     * @version 1.00
     * @note    勤怠用DBアクセスクラス
     */

    // BaseDBAccess.phpを読み込む
    require_once SystemParameters::$FW_COMMON_PATH . 'BaseDBAccess.php';

    /**
     * DBアクセスクラス
     * @note    DB共通アクセスクラス( 1アクション 1接続 )
     */
    class DBAccess extends BaseDBAccess
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // ModelBaseのコンストラクタ
            parent::__construct();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // ModelBaseのデストラクタ
            parent::__destruct();
        }

        /**
         * シーケンステーブル名取得
         * @param     $tabelName   Insert実施したテーブル名
         * @return    成功：シーケンステーブル名 失敗：空白
         */
        protected function getSequenceName($tabelName)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getSequenceName");

            //シーケンス名
            $seqName = array(
                "m_alert" => "m_alert_alert_id_seq",
                "m_allowance" =>"m_allowance_allowance_id_seq",
                "m_application_date" =>"m_application_date_application_date_id_seq",
                "m_break_time_zone" =>"m_break_time_zone_hourly_wage_change_id_seq",
                "m_calendar_detail" =>"m_calendar_detail_calendar_detail_id_seq",
                "m_display_item" =>"m_display_item_display_item_id_seq",
                "m_display_item_detail" =>"m_display_item_detail_display_item_detail_id_seq",
                "m_employment" =>"m_employment_employment_id_seq",
                "m_holiday" =>"m_holiday_holiday_id_seq",
                "m_holiday_name" =>"m_holiday_name_holiday_name_id_seq",
                "m_hourly_wage_change" =>"m_hourly_wage_change_hourly_wage_change_id_seq",
                "m_labor_regulations" =>"m_labor_regulations_labor_regulations_id_seq",
                "m_organization" =>"m_organization_organization_id_seq",
                "m_organization_calendar" =>"m_organization_calendar_organization_calendar_id_seq",
                "m_organization_detail" =>"m_organization_detail_organization_detail_id_seq",
                "m_overtime" =>"m_overtime_overtime_id_seq",
                "m_payroll_system_cooperation" =>"m_payroll_system_cooperation_payroll_system_id_seq",
                "m_payroll_system_detail" =>"m_payroll_system_detail_payroll_system_detail_id_seq",
                "m_position" =>"m_position_position_id_seq",
                "m_section" =>"m_section_section_id_seq",
                "m_security" =>"m_security_security_id_seq",
                "m_security_detail" =>"m_security_detail_security_detail_id_seq",
                "m_user" =>"m_user_user_id_seq",
                "m_user_detail" =>"m_user_detail_user_detail_id_seq",
                "m_work_rules_break" =>"m_work_rules_break_work_rules_break_id_seq",
                "m_work_rules_shift_break" =>"m_work_rules_shift_break_work_rules_shift_break_id_seq",
                "t_allowance" =>"t_allowance_id_seq",
                "t_attendance" =>"t_attendance_attendance_id_seq",
                "t_attendance_tighten" =>"t_attendance_tighten_attendance_tighten_id_seq",
                "t_embossing" =>"t_embossing_embossing_id_seq",
                "t_shift" =>"t_shift_shift_id_seq",
            );

            // テーブル名から、シーケンス名を取得する
            if(array_key_exists("$tabelName",$seqName))
            {
                $Log->trace("END getSequenceName");
                return $seqName[$tabelName];
            }

            $Log->debug("シーケンステーブル未設定");
            $Log->trace("END getSequenceName");
            return "";
        }
    }
?>
