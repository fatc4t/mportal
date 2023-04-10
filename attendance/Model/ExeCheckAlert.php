<?php
    /**
     * @file      日々の打刻漏れ、アラートチェック
     * @author    USE Y.Sakata
     * @date      2016/10/25
     * @version   1.00
     * @note      日々の打刻漏れ、アラートチェック(組織単位)
     */

    // CheckAlert.phpを読み込む
    require './Model/CheckAlert.php';
    // WorkingTimeRegistration.phpを読み込む
    require './Model/WorkingTimeRegistration.php';

    /**
     * 勤怠修正マスタクラス
     * @note   勤怠修正マスタテーブルの管理を行うの初期設定を行う
     */
    class ExeCheckAlert extends CheckAlert
    {
        protected $workTimeReg = null;              ///< 労働時間計算用モデル
        
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // ModelBaseのコンストラクタ
            parent::__construct();
            global $workTimeReg;
            $workTimeReg = new WorkingTimeRegistration();
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
         * 契約企業のリストを取得
         * @return   契約企業リスト
         */
        public function getCompanyContract()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCompanyContract");

            // 契約企業の一覧を取得
            $sql =  ' SELECT company_name FROM public.m_company_contract WHERE is_del = 0 ';
            $parameters = array();
            $result = $DBA->executeSQL( $sql, $parameters );
            // SQLエラー
            if( $result === false )
            {
                $errMsg = "SQLエラー" . $sql;
                $Log->fatalDetail($errMsg);
                $Log->trace("END getCompanyContract");
                return;
            }

            $companyList = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $companyList, $data['company_name'] );
            }
            
            $Log->trace("END getCompanyContract");
            
            return $companyList;
        }

        /**
         * 指定時間内に開店時間がある組織の一覧
         * @param    $organizationID    組織ID
         * @param    $selectDate        対象日
         * @param    $startTime         開始時間
         * @param    $endTime           終了時間
         * @return   $attendanceCorList
         */
        public function getTargetDayBulk( $organizationID, $selectDate, $startTime, $endTime )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getTargetDayBulk");

            $searchArray = array();

            $sql  = $this->creatSelectSQL(true);
            $sql .= " WHERE vu.organization_id = :organization_id  AND vu.hire_date <= :date AND ( :date <= vu.leaving_date OR vu.leaving_date IS NULL ) "
                 .  "       AND vu.eff_code <> '適用外' AND vu.eff_code <> '適用予定' AND :startTime <= var.start_time_day AND var.start_time_day < :endTime "
                 .  " ORDER BY vu.p_disp_order, vu.employees_no, var.attendance_time, var.shift_attendance_time ";

            $searchArray = array( 
                                    ':organization_id' => $organizationID, 
                                    ':date'            => $selectDate, 
                                    ':startTime'       => $startTime, 
                                    ':endTime'         => $endTime, 
                                );
            $result = $DBA->executeSQL($sql, $searchArray);

            $attendanceCorList = array();
            if( $result === false )
            {
                $Log->trace("END getTargetDayBulk");
                return $attendanceCorList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $attendanceCorList, $data);
            }
 
            $Log->trace("END getTargetDayBulk");
            return $attendanceCorList;
        }

        /**
         * 全組織取得
         * @return   $organizationList
         */
        public function getAllOrganization()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAllOrganization");

            $searchArray = array();

            $sql  = " SELECT organization_id, abbreviated_name FROM v_organization";
            $sql .= " WHERE is_del = 0 AND eff_code = '適用中'  ";

            $searchArray = array();
            $result = $DBA->executeSQL($sql, $searchArray);

            $allOrganizationList = array();
            if( $result === false )
            {
                $Log->trace("END getAllOrganization");
                return $allOrganizationList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $allOrganizationList, $data );
            }
 
            $Log->trace("END getAllOrganization");
            return $allOrganizationList;
        }

        /**
         * 所定残業時間を取得する
         * @param    $userID               ユーザID
         * @param    $laborRegulationsID   就業規則ID
         * @param    $targetDate           対象日
         * @return   所定残業時間
         */
        public function getPrescribedOvertimeHours( $userID, $laborRegulationsID, $targetDate )
        {
            global $Log, $DBA, $workTimeReg; // グローバル変数宣言
            $Log->trace("START getPrescribedOvertimeHours");

            // 就業規則を取得
            $lrAllInfo = $this->getLaborRegulationsAllInfo( $laborRegulationsID, $targetDate );
            $monthDay = 0;
            $prescribedOvertimeHours = $workTimeReg->getPrescribedOvertimeHours( $userID, $lrAllInfo, $targetDate, 't_attendance', $monthDay );

            $Log->trace("END getPrescribedOvertimeHours");

            return $prescribedOvertimeHours;
        }

        /**
         * 当日の所定残業時間の残りを取得する
         * @param    $userID               ユーザID
         * @param    $laborRegulationsID   就業規則ID
         * @param    $targetDate           対象日
         * @return   当日の所定残業時間
         */
        public function getPredeterminedTime( $userID, $laborRegulationsID, $targetDate )
        {
            global $Log, $DBA, $workTimeReg; // グローバル変数宣言
            $Log->trace("START getPredeterminedTime");

            // 就業規則を取得
            $lrAllInfo = $this->getLaborRegulationsAllInfo( $laborRegulationsID, $targetDate );
            $monthDay = 0;
            $prescribedOvertimeHours = $workTimeReg->getPredeterminedTime( $userID, $lrAllInfo, $targetDate, 't_attendance' );

            $Log->trace("END getPredeterminedTime");

            return $prescribedOvertimeHours;
        }

        /**
         * 勤怠処理の取得SELECT文作成
         * @return   select文
         */
        private function creatSelectSQL()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSelectSQL");

            $sql  = ' SELECT DISTINCT var.attendance_id, var.update_time ';
            $sql .= ' , vu.user_id, vu.user_name , vu.abbreviated_name , var.date , vu.p_disp_order, vu.employees_no ';
            $sql .= ' , var.embossing_organization_name, var.organization_name, var.embossing_organization_id, var.embossing_abbreviated_name '
                 .  ' , var.embossing_attendance_time, var.embossing_clock_out_time '
                 .  ' , var.embossing_s_break_time_1, var.embossing_e_break_time_1 '
                 .  ' , var.embossing_s_break_time_2, var.embossing_e_break_time_2 '
                 .  ' , var.embossing_s_break_time_3, var.embossing_e_break_time_3 '
                 .  ' , var.is_embossing_attendance_time, var.is_embossing_clock_out_time '
                 .  ' , var.is_embossing_s_break_time_1, var.is_embossing_e_break_time_1 '
                 .  ' , var.is_embossing_s_break_time_2, var.is_embossing_e_break_time_2 '
                 .  ' , var.is_embossing_s_break_time_3, var.is_embossing_e_break_time_3 '
                 .  ' , var.attendance_time, var.clock_out_time '
                 .  ' , var.s_break_time_1, var.e_break_time_1 '
                 .  ' , var.s_break_time_2, var.e_break_time_2 '
                 .  ' , var.s_break_time_3, var.e_break_time_3 '
                 .  ' , var.total_working_time, var.overtime, var.night_working_time, var.night_overtime, var.absence_count '
                 .  ' , var.shift_id, var.shift_attendance_time, var.shift_taikin_time, var.shift_break_time, var.is_holiday '
                 .  ' , var.shift_travel_time, var.shift_working_time, var.shift_overtime, var.shift_night_working_time'
                 .  ' , var.rough_estimate, var.shift_rough_estimate, var.break_time, var.approval, var.organization_id, var.labor_regulations_id '
                 .  ' FROM v_attendance_record var ';
            $sql .= "      RIGHT OUTER JOIN v_user vu ON vu.eff_code = '適用中' AND var.date = :date AND vu.user_id = var.user_id AND var.is_del = 0 ";

            $Log->trace("END creatSelectSQL");

            return $sql;
        }
    }
?>
