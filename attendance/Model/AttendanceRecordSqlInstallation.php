<?php
    /**
     * @file      勤怠一覧SQL文
     * @author    USE M.Higashihara
     * @date      2016/08/04
     * @version   1.00
     * @note      勤怠一覧SQL文を配置
     */

     // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 勤怠一覧画面表示データ制御
     * @note   勤怠一覧画面の表示するデータの取得/加工
     */
    class AttendanceRecordSqlInstallation extends BaseModel
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
         * 従業員情報取得(日)
         * @param    $postArray
         * @param    &$searchArray
         * @param    $startDate
         * @param    $endDate
         * @return   $sql
         */
        protected function creatUserSQL( $postArray, &$searchArray, $startDate, $endDate )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatUserSQL");

            // 検索用リスト検索期間セット
            $searchArray = array(
                ':is_del'             => 0,
                ':minPeriodSpecified' => $startDate,
                ':maxPeriodSpecified' => $endDate,
            );
            // 検索条件文リスト
            $searchedList = $this->getSearchedList( 0 );
            
            // SQL文生成
            $sql = ' SELECT v.user_id, v.user_detail_id, v.eff_code, v.status, v.hire_date, v.leaving_date, v.organization_id, v.employees_no '
                 . ' , v.user_name, v.tel, v.cellphone, v.mail_address, v.base_salary, v.hourly_wage, v.application_date_start, v.next_application_date_start '
                 . ' , v.user_comment, v.position_id, v.position_name, v.employment_id, v.employment_name, v.section_id, v.section_name, v.wage_form_id '
                 . ' , v.wage_form_name, v.organization_name, v.abbreviated_name, v.department_code, v.organization_comment ';
            $sql .= ' FROM v_user v ';
            if( $postArray['attendanceAccessId'] < 5 )
            {
                // 参照権限による検索条件追加
                $searchedColumn = ' WHERE v.';
                $sql .= $this->creatSqlWhereInConditions($searchedColumn);
            }
            else
            {
                $searchArray[':user_id'] = $_SESSION['USER_ID'];
                $sql .= ' WHERE v.user_id = :user_id ';
            }
            $sql .= $this->creatUserRefinersSQL();
            $sql .= $this->searchConditionsAdditional( $postArray, $searchArray, $searchedList );
            $sql .= ' ORDER BY v.p_disp_order, v.p_code, v.e_disp_order, v.e_code, v.user_id, v.user_detail_id ';

            $Log->trace("END creatUserSQL");

            return $sql;
        }

        /**
         * 従業員情報取得（合算用）
         * @param    $postArray
         * @param    &$searchArray
         * @param    $startDate
         * @param    $endDate
         * @return   $sql
         */
        protected function creatUserPeriodSQL( $postArray, &$searchArray, $startDate, $endDate )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatUserPeriodSQL");

            // 検索用リスト検索期間セット
            $searchArray = array(
                ':is_del'             => 0,
                ':minPeriodSpecified' => $startDate,
                ':maxPeriodSpecified' => $endDate,
            );
            if( $postArray['attendanceAccessId'] < 5 )
            {
                // 参照権限による検索条件追加
                $searchedColumn = ' WHERE v.';
            }
            // 検索条件文リスト
            $searchedList = $this->getSearchedList( 0 );
            // SQL文生成
            $sql = ' SELECT v.user_id, v.organization_id, min( v.application_date_start ) as application_date_start, ( SELECT mu.leaving_date FROM m_user mu WHERE mu.user_id = v.user_id ) '
                 . ' , ( SELECT vu.next_application_date_start FROM v_user vu WHERE vu.user_id = v.user_id AND vu.application_date_start = orderBYTarget.application_date_start ) '
                 . ' , ( SELECT ud.user_name FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) as user_name '
                 . ' , ( SELECT ud.employees_no FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) as employees_no '
                 . ' , ( SELECT ud.tel FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) as tel '
                 . ' , ( SELECT ud.cellphone FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) as cellphone '
                 . ' , ( SELECT ud.mail_address FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) as mail_address '
                 . ' , ( SELECT ud.comment FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) as user_comment '
                 . ' , ( SELECT position_name FROM m_position WHERE position_id = ( SELECT ud.position_id FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) ) '
                 . ' , ( SELECT employment_name FROM m_employment WHERE employment_id = ( SELECT ud.employment_id FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) ) '
                 . ' , ( SELECT section_name FROM m_section WHERE section_id = ( SELECT ud.section_id FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) ) '
                 . ' , ( SELECT wage_form_name FROM public.m_wage_form WHERE wage_form_id = ( SELECT ud.wage_form_id FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) ) '
                 . ' , ( SELECT ud.hourly_wage FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) as hourly_wage '
                 . ' , ( SELECT ud.base_salary FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) as base_salary '
                 . ' , ( SELECT od.department_code FROM m_organization_detail od , ( SELECT od.organization_id, max( od.application_date_start ) as application_date_start FROM m_organization_detail od WHERE od.organization_id = v.organization_id AND od.application_date_start <= :maxPeriodSpecified GROUP BY od.organization_id ) newData WHERE od.organization_id = newData.organization_id AND od.application_date_start = newData.application_date_start ) as department_code '
                 . ' , ( SELECT od.organization_name FROM m_organization_detail od , ( SELECT od.organization_id, max( od.application_date_start ) as application_date_start FROM m_organization_detail od WHERE od.organization_id = v.organization_id AND od.application_date_start <= :maxPeriodSpecified GROUP BY od.organization_id ) newData WHERE od.organization_id = newData.organization_id AND od.application_date_start = newData.application_date_start ) as organization_name '
                 . ' , ( SELECT od.abbreviated_name FROM m_organization_detail od , ( SELECT od.organization_id, max( od.application_date_start ) as application_date_start FROM m_organization_detail od WHERE od.organization_id = v.organization_id AND od.application_date_start <= :maxPeriodSpecified GROUP BY od.organization_id ) newData WHERE od.organization_id = newData.organization_id AND od.application_date_start = newData.application_date_start ) as abbreviated_name '
                 . ' , ( SELECT od.comment FROM m_organization_detail od , ( SELECT od.organization_id, max( od.application_date_start ) as application_date_start FROM m_organization_detail od WHERE od.organization_id = v.organization_id AND od.application_date_start <= :maxPeriodSpecified GROUP BY od.organization_id ) newData WHERE od.organization_id = newData.organization_id AND od.application_date_start = newData.application_date_start ) as organization_comment '
                 . ' FROM v_user v INNER JOIN ( '
                 . '     SELECT v.user_id, v.organization_id, max(v.application_date_start) as application_date_start '
                 . '         , ( SELECT disp_order FROM m_position WHERE position_id = ( SELECT ud.position_id FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) ) as p_disp_order '
                 . '         , ( SELECT code FROM m_position WHERE position_id = ( SELECT ud.position_id FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) ) as p_code '
                 . '         , ( SELECT disp_order FROM m_employment WHERE employment_id = ( SELECT ud.employment_id FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) ) as e_disp_order '
                 . '         , ( SELECT code FROM m_employment WHERE employment_id = ( SELECT ud.employment_id FROM m_user_detail ud , ( SELECT ud.user_id, max(ud.application_date_start) as application_date_start FROM m_user_detail ud WHERE ud.user_id = v.user_id AND ud.application_date_start <= max( v.application_date_start ) GROUP BY ud.user_id ) newData WHERE ud.user_id = newData.user_id AND ud.application_date_start = newData.application_date_start ) ) as e_code '
                 . '     FROM v_user v ';
            if( $postArray['attendanceAccessId'] < 5 )
            {
                $sql .= $this->creatSqlWhereInConditions( $searchedColumn );
            }
            else
            {
                $searchArray[':user_id'] = $_SESSION['USER_ID'];
                $sql .= ' WHERE v.user_id = :user_id ';
            }
            $sql .= $this->creatUserRefinersSQL();
            $sql .= ' GROUP BY v.user_id, v.organization_id ) orderBYTarget ';
            $sql .= ' ON v.user_id = orderBYTarget.user_id AND v.organization_id = orderBYTarget.organization_id ';
            if( $postArray['attendanceAccessId'] < 5 )
            {
                $sql .= $this->creatSqlWhereInConditions( $searchedColumn );
            }
            else
            {
                $searchArray[':user_id'] = $_SESSION['USER_ID'];
                $sql .= ' WHERE v.user_id = :user_id ';
            }
            $sql .= $this->creatUserRefinersSQL();
            $sql .= $this->searchConditionsAdditional( $postArray, $searchArray, $searchedList );
            $sql .= ' GROUP BY v.user_id, v.organization_id, orderBYTarget.application_date_start ';
            $sql .= ' , orderBYTarget.p_disp_order, orderBYTarget.p_code, orderBYTarget.e_disp_order, orderBYTarget.e_code ORDER BY orderBYTarget.p_disp_order, orderBYTarget.p_code, orderBYTarget.e_disp_order, orderBYTarget.e_code, v.user_id, v.organization_id ';

            $Log->trace("END creatUserPeriodSQL");

            return $sql;
        }

        /**
         * 組織情報取得
         * @param    $postArray
         * @param    &$searchArray
         * @return   $sql
         */
        protected function creatOrganizationSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatOrganizationSQL");

            $searchArray = array( ':is_del' => 0, );
            $searchSqlList = array( 'organizationID' => ' AND v.organization_id = :organizationID ', );

            // 参照権限による検索条件追加
            $searchedColumn = ' AND v.';

            $sql = ' SELECT v.organization_id, v.organization_name, v.abbreviated_name, v.department_code, v.upper_level_organization, v.up_organization_name, v.comment as organization_comment '
                 . ' , ( SELECT min( mod.application_date_start ) as first_date FROM m_organization_detail mod WHERE mod.organization_id = v.organization_id ) '
                 . ' FROM v_organization v INNER JOIN ( SELECT od.organization_id, max( od.application_date_start ) as application_date_start FROM m_organization_detail od GROUP BY od.organization_id ) newData '
                 . ' ON v.organization_id = newData.organization_id WHERE v.application_date_start = newData.application_date_start AND v.is_del = :is_del ';
            $sql .= $this->creatSqlWhereInConditions($searchedColumn);
            $sql .= $this->searchConditionsAdditional( $postArray, $searchArray, $searchSqlList );

            $Log->trace("END creatOrganizationSQL");

            return $sql;
        }

        /**
         * 役職情報取得
         * @param    $postArray
         * @param    &$searchArray
         * @return   $sql
         */
        protected function creatPositionSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatPositionSQL");

            $searchArray = array( ':is_del' => 0, );
            $searchSqlList = array(
                'organizationID' => ' AND v.organization_id = :organizationID ',
                'position_name'  => ' AND v.position_name LIKE :position_name ',
            );

            // 参照権限による検索条件追加
            $searchedColumn = ' WHERE v.';

            $sql = ' SELECT v.position_id, v.position_name, v.organization_id, v.organization_name, v.abbreviated_name, v.department_code, v.organization_comment, v.organization_first '
                 . ' , ( SELECT min( va.date ) as attendance_first FROM v_attendance_record va WHERE va.position_id = v.position_id ) '
                 . ' FROM v_user v ';
            $sql .= $this->creatSqlWhereInConditions( $searchedColumn );
            $sql .= ' AND v.is_del = :is_del ';
            $sql .= $this->searchConditionsAdditional( $postArray, $searchArray, $searchSqlList );
            $sql .= ' GROUP BY v.position_id, v.position_name, v.organization_id, v.organization_name, v.abbreviated_name, v.department_code, v.organization_comment, v.organization_first, v.p_disp_order, v.p_code ORDER BY v.p_disp_order, v.p_code ';

            $Log->trace("END creatPositionSQL");

            return $sql;
        }

        /**
         * 雇用形態情報取得
         * @param    $postArray
         * @param    &$searchArray
         * @return   $sql
         */
        protected function creatEmploymentSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatEmploymentSQL");

            $searchArray = array( ':is_del' => 0, );
            $searchSqlList = array(
                'organizationID'  => ' AND v.organization_id = :organizationID ',
                'employment_name' => ' AND v.employment_name LIKE :employment_name ',
            );

            // 参照権限による検索条件追加
            $searchedColumn = ' WHERE v.';

            $sql = ' SELECT v.employment_id, v.employment_name, v.organization_id, v.organization_name, v.abbreviated_name, v.department_code, v.organization_comment, v.organization_first '
                 . ' , ( SELECT min( va.date ) as attendance_first FROM v_attendance_record va WHERE va.employment_id = v.employment_id ) '
                 . ' FROM v_user v  ';
            $sql .= $this->creatSqlWhereInConditions($searchedColumn);
            $sql .= ' AND is_del = :is_del ';
            $sql .= $this->searchConditionsAdditional( $postArray, $searchArray, $searchSqlList );
            $sql .= ' GROUP BY v.employment_id, v.employment_name, v.organization_id, v.organization_name, v.abbreviated_name, v.department_code, v.organization_comment, v.organization_first, v.e_disp_order, v.e_code ORDER BY v.e_disp_order, v.e_code ';

            $Log->trace("END creatEmploymentSQL");

            return $sql;
        }

        /**
         * 勤怠一覧情報取得(日)
         * @param    $postArray
         * @param    &$searchArray
         * @param    $startDate
         * @param    $endDate
         * @return   $sql
         */
        protected function creatAttendanceSQL( $postArray, &$searchArray, $startDate, $endDate )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAttendanceSQL");

            // 検索用リスト検索期間セット
            $searchArray = array(
                ':is_del'             => 0,
                ':minPeriodSpecified' => $startDate,
                ':maxPeriodSpecified' => $endDate,
            );

            $searchedList = $this->getSearchedList( 2 );

            $sql = ' SELECT v.attendance_id, v.date, v.user_detail_id, v.embossing_department_code, v.embossing_organization_name '
                 . ' , v.embossing_abbreviated_name, v.embossing_status, v.attendance_time, v.clock_out_time, v.s_break_time_1 '
                 . ' , v.e_break_time_1, v.s_break_time_2, v.e_break_time_2, v.s_break_time_3, v.e_break_time_3 '
                 . ' , v.break_time_con_minute, v.travel_time_con_minute, v.late_time_con_minute, v.leave_early_time_con_minute '
                 . ' , ( v.late_time_con_minute + v.leave_early_time_con_minute ) as late_leave_time_con_minute '
                 . ' , v.total_working_time_con_minute, v.night_working_time_con_minute, v.overtime_con_minute, v.night_overtime_con_minute '
                 . ' , v.embossing_attendance_time, v.embossing_clock_out_time, v.embossing_s_break_time_1, v.embossing_e_break_time_1 '
                 . ' , v.embossing_s_break_time_2, v.embossing_e_break_time_2, v.embossing_s_break_time_3, v.embossing_e_break_time_3 '
                 . ' , v.attendance_comment, v.modify_count, v.shift_section_id, v.shift_is_holiday, v.shift_attendance_time, v.shift_taikin_time '
                 . ' , v.shift_break_time, v.late_time_count, v.leave_early_time_count, ( v.late_time_count + v.leave_early_time_count ) as late_leave_count '
                 . ' , v.attendance_time_count, v.weekday_attendance_time_count, v.weekend_attendance_time_count, v.legal_holiday_attendance_time_count '
                 . ' , v.law_closed_attendance_time_count, v.weekday_working_con_minute, v.weekend_working_con_minute, v.legal_holiday_working_con_minute '
                 . ' , v.law_closed_working_con_minute, v.weekday_night_working_con_minute, v.weekend_night_working_con_minute '
                 . ' , v.legal_holiday_night_working_con_minute, v.law_closed_night_working_con_minute, v.weekday_overtime_con_minute '
                 . ' , v.weekend_overtime_con_minute, v.legal_holiday_overtime_con_minute, v.law_closed_overtime_con_minute '
                 . ' , v.weekday_night_overtime_con_minute, v.weekend_night_overtime_con_minute, v.legal_holiday_night_overtime_con_minute '
                 . ' , v.law_closed_night_overtime_con_minute, v.day_of_the_week_int, v.rough_estimate, v.shift_rough_estimate, v.rough_estimate_month, v.work_classification '
                 . ' , v.absence_count, v.prescribed_working_days, v.prescribed_working_hours, v.weekday_prescribed_working_hours, v.weekend_prescribed_working_hours '
                 . ' , v.legal_holiday_prescribed_working_hours, v.law_closed_prescribed_working_hours, v.statutory_overtime_hours, v.nonstatutory_overtime_hours_all '
                 . ' , v.nonstatutory_overtime_hours, v.nonstatutory_overtime_hours_45h, v.nonstatutory_overtime_hours_less_than, v.nonstatutory_overtime_hours_60h '
                 . ' , v.statutory_overtime_hours_no_pub, v.nonstatutory_overtime_hours_no_pub_all, v.nonstatutory_overtime_hours_no_pub '
                 . ' , v.nonstatutory_overtime_hours_45h_no_pub, v.nonstatutory_overtime_hours_no_pub_less_than, v.nonstatutory_overtime_hours_60h_no_pub '
                 . ' , v.overtime_hours_no_considered, v.overtime_hours_no_considered_no_pub, v.normal_overtime_con_minute, v.normal_night_working_con_minute '
                 . ' , v.normal_night_overtime_con_minute, ( v.normal_working_con_minute - v.normal_overtime_con_minute ) as normal_working_con_minute, v.work_hours '
                 . ' , v.rough_estimate_hour, v.shift_rough_estimate_hour '
                 . ' , (v.weekday_working_con_minute - v.weekday_night_working_con_minute - v.weekday_overtime_con_minute + v.weekday_night_overtime_con_minute) as weekday_normal_time'
                 . ' , (v.weekday_night_working_con_minute - v.weekday_night_overtime_con_minute) as weekday_midnight_time_only'
                 . ' , (v.total_working_time_con_minute - v.weekend_night_working_con_minute - v.overtime_con_minute + v.weekend_night_overtime_con_minute) as holiday_normal_time'
                 . ' , (v.weekend_night_working_con_minute - v.weekend_night_overtime_con_minute) as holiday_midnight_time_only'
                 . ' , (v.law_closed_working_con_minute - v.law_closed_night_working_con_minute - v.law_closed_overtime_con_minute + v.law_closed_night_overtime_con_minute) as statutory_holiday_normal_time'
                 . ' , (v.law_closed_night_working_con_minute - v.law_closed_night_overtime_con_minute) as statutory_holiday_midnight_time_only'
                 . ' , (v.legal_holiday_working_con_minute - v.legal_holiday_night_working_con_minute - v.legal_holiday_overtime_con_minute + v.legal_holiday_night_overtime_con_minute) as public_holiday_normal_time'
                 . ' , (v.legal_holiday_night_working_con_minute - v.legal_holiday_night_overtime_con_minute) as public_holiday_midnight_time_only'
                 . ' FROM v_attendance_record v ';
            if( $postArray['attendanceAccessId'] < 5 )
            {
                // 参照権限による検索条件追加
                $searchedColumn = ' WHERE v.';
                $sql .= $this->creatSqlWhereInConditions( $searchedColumn );
            }
            else
            {
                $searchArray[':user_id'] = $_SESSION['USER_ID'];
                $sql .= ' WHERE v.user_id = :user_id ';
            }
            $sql .= ' AND v.is_del = :is_del AND v.date >= :minPeriodSpecified AND v.date <= :maxPeriodSpecified ';
            $sql .= $this->searchConditionsAdditional( $postArray, $searchArray, $searchedList );
            $sql .= ' ORDER BY v.attendance_id ';
            $Log->debug($sql);

            $Log->trace("END creatAttendanceSQL");

            return $sql;
        }

        /**
         * 勤怠一覧情報取得(合算用)
         * @param    $postArray
         * @param    &$searchArray
         * @param    $startDate
         * @param    $endDate
         * @return   $sql
         */
        protected function creatUnitAttendanceListSQL( $postArray, &$searchArray, $startDate, $endDate )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatUnitAttendanceListSQL");

            // 検索用リスト検索期間セット
            $searchArray = array(
                ':is_del'             => 0,
                ':minPeriodSpecified' => $startDate,
                ':maxPeriodSpecified' => $endDate,
            );
            // 検索条件文
            $searchedList = $this->getSearchedList( 1 );

            $sql = ' SELECT v.attendance_id, v.date, v.user_id, v.organization_id, v.position_id, v.employment_id, v.embossing_organization_id '
                 . ' , v.embossing_department_code, v.embossing_organization_name, v.embossing_abbreviated_name, v.embossing_status, v.work_classification '
                 . ' , v.total_working_time_con_minute, v.weekday_working_con_minute, v.weekend_working_con_minute, v.legal_holiday_working_con_minute '
                 . ' , v.law_closed_working_con_minute, v.overtime_con_minute, v.weekday_overtime_con_minute , v.weekend_overtime_con_minute '
                 . ' , v.legal_holiday_overtime_con_minute, v.law_closed_overtime_con_minute, v.night_working_time_con_minute, v.weekday_night_working_con_minute '
                 . ' , v.weekend_night_working_con_minute, v.legal_holiday_night_working_con_minute, v.law_closed_night_working_con_minute '
                 . ' , v.night_overtime_con_minute, v.weekday_night_overtime_con_minute, v.weekend_night_overtime_con_minute, v.legal_holiday_night_overtime_con_minute '
                 . ' , v.law_closed_night_overtime_con_minute, v.break_time_con_minute, v.late_time_con_minute, v.leave_early_time_con_minute, v.travel_time_con_minute '
                 . ' , ( v.late_time_con_minute + v.leave_early_time_con_minute ) as late_leave_time_con_minute, v.modify_count, v.late_time_count, v.leave_early_time_count '
                 . ' , ( v.late_time_count + v.leave_early_time_count) as late_leave_count, v.attendance_time_count, v.weekday_attendance_time_count '
                 . ' , v.weekend_attendance_time_count, v.legal_holiday_attendance_time_count, v.law_closed_attendance_time_count, v.rough_estimate, v.shift_rough_estimate '
                 . ' , v.rough_estimate_month, v.month_tightening, v.target_month_tightening, v.year_tighten, v.target_year_tighten, v.fixed_overtime_time, v.absence_count '
                 . ' , v.prescribed_working_days, v.prescribed_working_hours, v.weekday_prescribed_working_hours, v.weekend_prescribed_working_hours, v.legal_holiday_prescribed_working_hours '
                 . ' , v.law_closed_prescribed_working_hours, v.statutory_overtime_hours, v.nonstatutory_overtime_hours_all, v.nonstatutory_overtime_hours, v.nonstatutory_overtime_hours_45h '
                 . ' , v.nonstatutory_overtime_hours_less_than, v.nonstatutory_overtime_hours_60h, v.statutory_overtime_hours_no_pub, v.nonstatutory_overtime_hours_no_pub_all '
                 . ' , v.nonstatutory_overtime_hours_no_pub, v.nonstatutory_overtime_hours_45h_no_pub, v.nonstatutory_overtime_hours_no_pub_less_than '
                 . ' , v.nonstatutory_overtime_hours_60h_no_pub, v.overtime_hours_no_considered, v.overtime_hours_no_considered_no_pub, v.normal_overtime_con_minute '
                 . ' , v.normal_night_working_con_minute, v.normal_night_overtime_con_minute, ( v.normal_working_con_minute - v.normal_overtime_con_minute ) as normal_working_con_minute '
                 . ' , (v.weekday_working_con_minute - v.weekday_night_working_con_minute - v.weekday_overtime_con_minute + v.weekday_night_overtime_con_minute) as weekday_normal_time'
                 . ' , (v.weekday_night_working_con_minute - v.weekday_night_overtime_con_minute) as weekday_midnight_time_only'
                 . ' , (v.total_working_time_con_minute - v.weekend_night_working_con_minute - v.overtime_con_minute + v.weekend_night_overtime_con_minute) as holiday_normal_time'
                 . ' , (v.weekend_night_working_con_minute - v.weekend_night_overtime_con_minute) as holiday_midnight_time_only'
                 . ' , (v.law_closed_working_con_minute - v.law_closed_night_working_con_minute - v.law_closed_overtime_con_minute + v.law_closed_night_overtime_con_minute) as statutory_holiday_normal_time'
                 . ' , (v.law_closed_night_working_con_minute - v.law_closed_night_overtime_con_minute) as statutory_holiday_midnight_time_only'
                 . ' , (v.legal_holiday_working_con_minute - v.legal_holiday_night_working_con_minute - v.legal_holiday_overtime_con_minute + v.legal_holiday_night_overtime_con_minute) as public_holiday_normal_time'
                 . ' , (v.legal_holiday_night_working_con_minute - v.legal_holiday_night_overtime_con_minute) as public_holiday_midnight_time_only';
            $sql .= ' FROM v_attendance_record v ';
            if( $postArray['attendanceAccessId'] < 5 )
            {
                // 参照権限による検索条件追加
                $searchedColumn = ' WHERE v.';
                $sql .= $this->creatSqlWhereInConditions( $searchedColumn );
            }
            else
            {
                $searchArray[':user_id'] = $_SESSION['USER_ID'];
                $sql .= ' WHERE v.user_id = :user_id ';
            }
            $sql .= $this->searchConditionsAdditional( $postArray, $searchArray, $searchedList );
            $sql .= ' AND v.is_del = :is_del AND v.date >= :minPeriodSpecified AND v.date <= :maxPeriodSpecified ORDER BY v.date';

            $Log->trace("END creatUnitAttendanceListSQL");

            return $sql;
        }

        /**
         * 取得手当情報SQL
         * @param    $parameters
         * @param    $mobileUnitId
         * @param    $dateUnitKey
         * @param    $sumMsg
         * @param    $groupby
         * @param    $targetPeriodList
         * @param    $periodName
         * @return   $allowanceAmountList
         */
        protected function getAllowanceAmountInfo( $parameters, $mobileUnitId, $dateUnitKey, $sumMsg, $groupby, $targetPeriodList, $periodName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAllowanceAmountInfo");

            // 検索用リスト検索期間セット
            $searchArray = array(
                ':is_del'             => 0,
            );

            $sql = ' SELECT organization_id, allowance_id ';
            $sql .= $mobileUnitId;
            $sql .= $dateUnitKey;
            $sql .= $sumMsg;
            $sql .= ' FROM v_allowance_table ';
            if( $parameters['access_id'] < 5 )
            {
                // 参照権限による検索条件追加
                $searchedColumn = ' WHERE ';
                $sql .= $this->creatSqlWhereInConditions( $searchedColumn );
            }
            else
            {
                $searchArray[':user_id'] = $_SESSION['USER_ID'];
                $sql .= ' WHERE user_id = :user_id ';
            }
            $sql .= ' AND is_del = :is_del ';
            $sql .= $this->setPeriodConditionalSentence( $parameters, $dateUnitKey, $searchArray, $targetPeriodList, $periodName );
            $sql .= $groupby;
            $sql .= ' ORDER BY allowance_id ';

            $allowanceAmountList = $this->runListGet( $sql, $searchArray );

            $Log->trace("END getAllowanceAmountInfo");

            return $allowanceAmountList;
        }

        /**
         * 取得休日情報SQL
         * @param    $parameters
         * @param    $mobileUnitId
         * @param    $dateUnitKey
         * @param    $sumMsg
         * @param    $groupby
         * @param    $targetPeriodList
         * @param    $periodName
         * @return   $holidayInfoList
         */
        protected function getHolidayAcquisitionInfo( $parameters, $mobileUnitId, $dateUnitKey, $sumMsg, $groupby, $targetPeriodList, $periodName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getHolidayAcquisitionInfo");

            // 検索用リスト検索期間セット
            $searchArray = array(
                ':is_del'             => 0,
            );

            $sql = ' SELECT organization_id ';
            $sql .= $mobileUnitId;
            $sql .= $dateUnitKey;
            $sql .= $sumMsg;
            $sql .= ' FROM v_attendance_record ';
            if( $parameters['access_id'] < 5 )
            {
                // 参照権限による検索条件追加
                $searchedColumn = ' WHERE ';
                $sql .= $this->creatSqlWhereInConditions( $searchedColumn );
            }
            else
            {
                $searchArray[':user_id'] = $_SESSION['USER_ID'];
                $sql .= ' WHERE user_id = :user_id ';
            }
            $sql .= ' AND is_del = :is_del AND is_holiday > 0 AND is_holiday < 99999998 ';
            $sql .= $this->setPeriodConditionalSentence( $parameters, $dateUnitKey, $searchArray, $targetPeriodList, $periodName );
            $sql .= $groupby;
            $sql .= ' ORDER BY ';
            $orderId = ltrim( $mobileUnitId, ', ');
            $sql .= $orderId;

            $holidayInfoList = $this->runListGet( $sql, $searchArray );

            $Log->trace("END getHolidayAcquisitionInfo");

            return $holidayInfoList;
        }

        /**
         * 取得手当/休日IDリスト取得
         * @param    $parameters
         * @param    $dateUnitKey
         * @param    $targetPeriodList
         * @param    $periodName
         * @param    $targetList
         * @return   $allowanceAmountList
         */
        protected function getSummaryKeyList( $parameters, $dateUnitKey, $targetPeriodList, $periodName, $targetList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getSummaryKeyList");

            // 検索用リスト検索期間セット
            $searchArray = array(
                ':is_del'             => 0,
            );

            $sql = ' SELECT ';
            $sql .= $targetList['idName'];
            $sql .= ' FROM ';
            $sql .= $targetList['tableName'];
            if( $parameters['access_id'] < 5 )
            {
                // 参照権限による検索条件追加
                $searchedColumn = ' WHERE ';
                $sql .= $this->creatSqlWhereInConditions( $searchedColumn );
            }
            else
            {
                $searchArray[':user_id'] = $_SESSION['USER_ID'];
                $sql .= ' WHERE user_id = :user_id ';
            }
            $sql .= ' AND is_del = :is_del ';
            $sql .= $targetList['exclusion'];
            $sql .= $this->setPeriodConditionalSentence( $parameters, $dateUnitKey, $searchArray, $targetPeriodList, $periodName );
            $sql .= ' GROUP BY ';
            $sql .= $targetList['idName'];
            $sql .= ' ORDER BY ';
            $sql .= $targetList['idName'];

            $summaryKeyList = $this->runListGet( $sql, $searchArray );

            $Log->trace("END getSummaryKeyList");

            return $summaryKeyList;
        }

        /**
         * 検索条件追加
         * @param    $postArray
         * @param    &$searchArray
         * @param    $searchedList
         * @return   $sql
         */
        protected function searchConditionsAdditional( $postArray, &$searchArray, $searchedList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START searchConditionsAdditional");

            foreach( $searchedList as $key => $val )
            {
                if( !empty( $postArray[$key] ) )
                {
                    $sql .= $val;
                    $column = $postArray[$key];
                    if( substr( $key, -4 ) === "name" )
                    {
                        $column = "%" . $column . "%";
                    }
                    $setKey = ":" . $key;
                    $searchArray = array_merge($searchArray, array( $setKey => $column,));
                }
            }

            $Log->trace("END searchConditionsAdditional");
            
            return $sql;
        }

        /**
         * 勤怠月合算リストから検索対象外となるキーリストをセット
         * @param    $postArray
         * @param    $addingUpList
         * @return   $keyNoList
         */
        protected function setKeyNoList( $postArray, $addingUpList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setKeyNoList");

            $KeyNoList = array();
            if( !empty( $postArray['searchCondition'] ) )
            {
                $searchCnt = 0;
                foreach( $addingUpList as $adding )
                {
                    foreach( $postArray as $key => $val )
                    {
                        $KeyNoList = $this->rangeSearchTargertNo( $KeyNoList, $postArray, $key, $adding, "min", $searchCnt );
                        $KeyNoList = $this->rangeSearchTargertNo( $KeyNoList, $postArray, $key, $adding, "max", $searchCnt );
                    }
                    $searchCnt++;
                }
            }

            $Log->trace("END setKeyNoList");

            return $KeyNoList;
        }

        /**
         * 対象外の配列のキー名を取得後比較して、対象外キーリストを作成
         * @param    $KeyNoList
         * @param    $postArray
         * @param    $key
         * @param    $addingList
         * @param    $keyMsg
         * @param    $searchCnt
         * @return   $KeyNoList
         */
        private function rangeSearchTargertNo( $KeyNoList, $postArray, $key, $addingList, $keyMsg, $searchCnt )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START rangeSearchTargertNo");

            if( ( !empty( $postArray[$key] ) ) && ( substr( $key, 0, 3) === $keyMsg ) && ( substr( $key, 3, 6 ) !== "Period" ) )
            {
                $strCnt3CharDel = ( mb_strlen( $key ) - 3 );
                $searchKeyName = substr( $key, 3, $strCnt3CharDel );
                $attendanceKey = $this->setAttendanceKey( $searchKeyName );
                $KeyNoList = $this->searchResultsMinArrayStore( $KeyNoList, $postArray, $key, $addingList, $keyMsg, $searchCnt, $attendanceKey );
            }

            $Log->trace("END rangeSearchTargertNo");

            return $KeyNoList;
        }

        /**
         * 検索のキー名取得
         * @param    $searchKeyName
         * @return   $attendanceKey
         */
        private function setAttendanceKey( $searchKeyName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setAttendanceKey");

            if( $searchKeyName === "EstimationShift" )
            {
                $attendanceKey = "shift_rough_estimate";
            }
            else if( $searchKeyName === "EstimationPerfo" )
            {
                $attendanceKey = "rough_estimate";
            }
            else if( $searchKeyName === "TotalWorkTime" )
            {
                $attendanceKey = "total_working_time_con_minute";
            }
            else if( $searchKeyName === "Overtime" )
            {
                $attendanceKey = "overtime_con_minute";
            }
            else if( $searchKeyName === "BreakTime" )
            {
                $attendanceKey = "break_time_con_minute";
            }
            else if( $searchKeyName === "AbsenteeismNo" )
            {
                $attendanceKey = "absence_count";
            }
            else if( $searchKeyName === "LateNo" )
            {
                $attendanceKey = "late_time_count";
            }
            else if( $searchKeyName === "LeaveNo" )
            {
                $attendanceKey = "leave_early_time_count";
            }
            else if( $searchKeyName === "ModifyNo" )
            {
                 $attendanceKey = "modify_count";
            }

            $Log->trace("END setAttendanceKey");

            return $attendanceKey;
        }

        /**
         * 検索対象除外リストの作成
         * @param    $KeyNoList
         * @param    $postArray
         * @param    $key
         * @param    $addingList
         * @param    $keyMsg
         * @param    $searchCnt
         * @param    $attendanceKey
         * @return   $attendanceList
         */
        private function searchResultsMinArrayStore( $KeyNoList, $postArray, $key, $addingList, $keyMsg, $searchCnt, $attendanceKey )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START searchResultsMinArrayStore");

            if( $keyMsg === "min" )
            {
                if( $addingList[$attendanceKey] < $postArray[$key] )
                {
                    array_push( $KeyNoList, $searchCnt );
                }
            }
            else
            {
                if( $addingList[$attendanceKey] > $postArray[$key] )
                {
                    array_push( $KeyNoList, $searchCnt );
                }
            }

            $Log->trace("END searchResultsMinArrayStore");

            return $KeyNoList;
        }

        /**
         * 従業員情報期間等の絞り込み条件文
         * @return   $sql
         */
        private function creatUserRefinersSQL()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatUserRefinersSQL");

            $sql = ' AND v.is_del = :is_del '
                 . ' AND ( v.next_application_date_start IS NULL OR v.next_application_date_start > :minPeriodSpecified ) '
                 . ' AND v.application_date_start <= :maxPeriodSpecified '
                 . ' AND ( v.leaving_date IS NULL OR v.leaving_date >= :minPeriodSpecified ) ';

            $Log->trace("END creatUserRefinersSQL");

            return $sql;
        }

        /**
         * 追加検索条件
         * @param    $attendanceFlag
         * @return   $searchedList
         */
        private function getSearchedList( $attendanceFlag )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getSearchedList");

            $searchedList['organizationID'] = ' AND v.organization_id = :organizationID ';
            $searchedList['position_name'] = ' AND v.position_name LIKE :position_name ';
            $searchedList['employment_name'] = ' AND v.employment_name LIKE :employment_name ';
            $searchedList['personal_name'] = ' AND v.user_name LIKE :personal_name ';
            if( !empty( $attendanceFlag ) )
            {
                $searchedList['embossingOrganizationID'] = ' AND v.embossing_organization_id = :embossingOrganizationID ';
                $searchedList['embossingSituation'] = ' AND v.embossing_status = :embossingSituation ';
                if( $attendanceFlag > 1 )
                {
                    $searchedList['minEstimationShift'] = ' AND v.shift_rough_estimate >= :minEstimationShift ';
                    $searchedList['maxEstimationShift'] = ' AND v.shift_rough_estimate <= :maxEstimationShift ';
                    $searchedList['minEstimationPerfo'] = ' AND v.rough_estimate >= :minEstimationPerfo ';
                    $searchedList['maxEstimationPerfo'] = ' AND v.rough_estimate <= :maxEstimationPerfo ';
                    $searchedList['minLateNo'] = ' AND v.late_time_count >= :minLateNo ';
                    $searchedList['maxLateNo'] = ' AND v.late_time_count <= :maxLateNo ';
                    $searchedList['minLeaveNo'] = ' AND v.leave_early_time_count >= :minLeaveNo ';
                    $searchedList['maxLeaveNo'] = ' AND v.leave_early_time_count <= :maxLeaveNo ';
                    $searchedList['minModifyNo'] = ' AND v.modify_count >= :minModifyNo ';
                    $searchedList['maxModifyNo'] = ' AND v.modify_count <= :maxModifyNo ';
                    $searchedList['minTotalWorkTime'] = ' AND v.total_working_time_con_minute >= :minTotalWorkTime ';
                    $searchedList['maxTotalWorkTime'] = ' AND v.total_working_time_con_minute <= :maxTotalWorkTime ';
                    $searchedList['minOvertime'] = ' AND v.overtime_con_minute >= :minOvertime ';
                    $searchedList['maxOvertime'] = ' AND v.overtime_con_minute <= :maxOvertime ';
                    $searchedList['minBreakTime'] = ' AND v.break_time_con_minute >= :minBreakTime ';
                    $searchedList['maxBreakTime'] = ' AND v.break_time_con_minute <= :maxBreakTime ';
                }
            }

            $Log->trace("END getSearchedList");

            return $searchedList;
        }

        /**
         * 取得手当情報SQL
         * @param    $parameters
         * @param    $dateUnitKey
         * @param    &$searchArray
         * @param    $targetPeriodList
         * @param    $periodName
         * @return   $allowanceAmountList
         */
        private function setPeriodConditionalSentence( $parameters, $dateUnitKey, &$searchArray, $targetPeriodList, $periodName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setPeriodConditionalSentence");

            if( empty( $periodName ) )
            {
                // 集計単位が日または時間の時、検索条件を開始日から終了日までのSQL条件文を追加
                $sql = ' AND date >= :minPeriodSpecified AND date <= :maxPeriodSpecified ';
                $searchArray[':minPeriodSpecified'] = $parameters['startDate'];
                $searchArray[':maxPeriodSpecified'] = $parameters['endDate'];
            }
            else
            {
                // 集計単位が月または年の時、検索条件を対象の締日をIN句の中に入れるSQL条件文を追加
                $str = ltrim($dateUnitKey, ', ');
                $sql = ' AND ';
                $sql .= $str;
                $sql .= ' IN ( ';
                $periodCnt = 1;
                foreach( $targetPeriodList as $targetPeriod )
                {
                    $periodKey = ':period' . $periodCnt;
                    $sql .= $periodKey . ',';
                    $searchArray[$periodKey] = $targetPeriod[$periodName];
                    $periodCnt++;
                }
                $sql = substr($sql, 0, -1);
                $sql .= ' ) ';
            }

            $Log->trace("END setPeriodConditionalSentence");

            return $sql;
        }

    }
?>