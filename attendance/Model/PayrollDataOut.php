<?php
    /**
     * @file      給与データ出力マスタ
     * @author    USE R.dendo
     * @date      2016/07/13
     * @version   1.00
     * @note      給与データ出力マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 給与データ出力マスタクラス
     * @note   給与データ出力マスタテーブルの管理を行うの初期設定を行う
     */
    class PayrollDataOut extends BaseModel
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
         * 給与データ出力マスタ一覧画面一覧表
         * @param    $postArray     検索条件
         * @return   成功時：$payOutList  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            // 勤怠締め情報を取得
            $result = $DBA->executeSQL( $sql, $searchArray );

            $payOutDataList = array();
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $payOutDataList;
            }
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $payOutDataList, $data);
            }

            $org = array( 
                          'organization_id'  =>  0,
                          'closing_date'     =>  $postArray['closingDate'],
                          'o_disp_order'     =>  0,
                          'approval'         =>  1,
                          'beneathApproval'  =>  1,
                          'abbreviated_name' => '',
                        );
            $outDataList = array();
            // データの集計（同一組織内のユーザを纏める）o_disp_order
            foreach( $payOutDataList as $pay )
            {
                // 初回作成
                if( empty( $outDataList[$pay['organization_id']] ) )
                {
                    $outDataList[$pay['organization_id']] = $org;
                    $outDataList[$pay['organization_id']]['organization_id'] = $pay['organization_id'];
                    $outDataList[$pay['organization_id']]['abbreviated_name'] = $pay['abbreviated_name'];
                    $outDataList[$pay['organization_id']]['o_disp_order'] = $pay['o_disp_order'];
                }

                if( $pay['approval'] == 0  )
                {
                    $outDataList[$pay['organization_id']]['approval'] = 0;
                }
                
                // 一度〆た後、承認解除された場合も、承認なしとする
                if( !$this->isApproval( $pay['user_id'], $postArray['closingDate'] ) )
                {
                    $outDataList[$pay['organization_id']]['approval'] = 0;
                }
            }

            // 各組織の下位の締め状況を設定
            foreach( $outDataList as $key => $value )
            {
                // 対象組織の下位組織IDを取得
                $salesOrgList = $this->securityProcess->getDisplayOrderHierarchy( $key );
                foreach( $salesOrgList as $salesOrg )
                {
                    // 組織IDが調査対象と同一の場合、調査を飛ばす
                    if( $value['organization_id'] == $salesOrg['organization_id'] )
                    {
                        continue;
                    }

                    // 下位組織が、締めデータに存在するか（ユーザが存在しない組織は、締めデータなし）
                    if( !empty( $outDataList[$salesOrg['organization_id']] ) )
                    {
                        // 下位組織は、締め済みではない
                        if( $outDataList[$salesOrg['organization_id']]['approval'] == 0 )
                        {
                            $outDataList[$key]['beneathApproval'] = 0;
                            break;
                        }
                    }
                }
            }

            // 表示対象が限定されている場合、該当以外のデータは削除する
            if( $postArray['isBeneath'] == 0 )
            {
                $temp = $outDataList[$postArray['organizationID']];
                $outDataList = array();
                $outDataList[$postArray['organizationID']] = $temp;
            }

            $payOutDataList = array();
            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                // 出力用変数に代入
                $payOutDataList = $outDataList;
                
                if( $postArray['sort'] == 5 )
                {
                    $this->sortArrayByKeyForTwoDimensions( $payOutDataList, 'o_disp_order', SORT_DESC   );
                }
                else if( $postArray['sort'] == 6 )
                {
                    $this->sortArrayByKeyForTwoDimensions( $payOutDataList, 'o_disp_order' );
                }
                else if( $postArray['sort'] == 7 )
                {
                    $this->sortArrayByKeyForTwoDimensions( $payOutDataList, 'approval', SORT_DESC   );
                }
                else if( $postArray['sort'] == 8 )
                {
                    $this->sortArrayByKeyForTwoDimensions( $payOutDataList, 'approval' );
                }
                else if( $postArray['sort'] == 9 )
                {
                    $this->sortArrayByKeyForTwoDimensions( $payOutDataList, 'beneathApproval', SORT_DESC   );
                }
                else if( $postArray['sort'] == 10 )
                {
                    $this->sortArrayByKeyForTwoDimensions( $payOutDataList, 'beneathApproval' );
                }
            }
            else
            {
                // 組織の階層順に並び替え
                foreach( $_SESSION["REFERENCE"] as $viewable)
                {
                    foreach( $outDataList as $data )
                    {
                        if( $viewable['organization_id'] == $data['organization_id'] && 
                            ( false === array_search( $data, $payOutDataList ) ) )
                        {
                            $payOutDataList[$viewable['organization_id']] = $data;
                        }
                    }
                }

                if( $postArray['sort'] == 1 )
                {
                    $payOutDataList = array_reverse($payOutDataList);
                }
            }

            $Log->trace("END getListData");
            return $payOutDataList;
        }

        /**
         * 給与出力連携方法取得
         * @param    $payrollSystemID     給与システム連携マスタID
         * @return   $payrollCooperation
         */
        public function getPayrollCooperationInfo( $payrollSystemID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getPayrollCooperationInfo");

            $searchArray = array( ':payroll_system_id' => $payrollSystemID, );

            $sql = ' SELECT is_item_name, display_format, no_data_format, counting_unit '
                 . ' FROM m_payroll_system_cooperation WHERE payroll_system_id = :payroll_system_id ';

            $result = $DBA->executeSQL( $sql, $searchArray );
            $payrollCooperation = array();
            if( $result === false )
            {
                $Log->trace("END getPayrollCooperationInfo");
                return $payrollCooperation;
            }
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $payrollCooperation = $data;
            }

            $Log->trace("END getPayrollCooperationInfo");

            return $payrollCooperation;
        }

        /**
         * 対象従業員情報取得
         * @param    $postArray
         * @return   $userInfoList
         */
        public function getTargetUserInfoList( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getTargetUserInfoList");

            // 対象ユーザIDリスト取得
            $userIDList = $this->getTargetUserIDList($postArray);

            $searchArray = array();

            $sql = ' SELECT v.user_id, v.organization_id, v.employees_no, v.user_name, v.tel, v.cellphone, v.mail_address, v.base_salary '
                 . ' , v.hourly_wage, v.user_comment, v.position_name, v.p_disp_order, v.p_code, v.employment_name, v.e_disp_order '
                 . ' , v.organization_name, v.abbreviated_name, v.o_disp_order, v.department_code, v.organization_comment, v.payroll_system_id, v.payroll_system_name '
                 . ' FROM v_user v '
                 . ' , ( SELECT user_id, max( application_date_start ) as application_date_start FROM v_user WHERE ';
            $sql .= $this->getTargetUserSql( $userIDList, $searchArray );
            $sql .= ' AND application_date_start <= :end_date GROUP BY user_id ) nowData ';
            $sql .= ' WHERE v.user_id = nowData.user_id AND v.application_date_start = nowData.application_date_start ';
            $sql .= ' ORDER BY v.p_disp_order, v.p_code, v.e_disp_order, v.e_code, v.employees_no ';

            list( $year, $month ) = explode( "/", $postArray['closingDate'] );
            $end_date = date('Y/m/d', mktime( 0, 0, 0, $month + 1, 0, $year ) );

            $searchArray[':end_date'] = $end_date;

            $userInfoList = $this->runListGet( $sql, $searchArray );
            // 組織の表示順へ並び替え
            $userInfoList = $this->creatAccessControlledList( $_SESSION["OUTPUT"], $userInfoList );

            $Log->trace("END getTargetUserInfoList");

            return $userInfoList;
        }

        /**
         * 勤怠締め情報取得
         * @param    $postArray
         * @return   $attendanceList
         */
        public function getAttendanceTightenInfo( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAttendanceTightenInfo");

            // 対象ユーザIDリスト取得
            $userIDList = $this->getTargetUserIDList($postArray);

            $searchArray = array();

            $sql = ' SELECT user_id, working_time, night_working_time, normal_working_time, normal_overtime_hours, normal_night_working_time '
                 . ' , normal_night_overtime_hours, absence_count, being_late_count, leave_early_count, prescribed_working_days '
                 . ' , production_dates, weekday_attendance_dates, holiday_work_days, public_holiday_attendance_dates, legal_holiday_work_days '
                 . ' , prescribed_working_hours, weekday_working_hours, weekday_prescribed_working_hours, weekday_overtime, holiday_working_hours '
                 . ' , holiday_prescribed_working_hours, holiday_overtime_hours, public_holiday_working_hours, public_holiday_prescribed_working_hours '
                 . ' , public_holiday_overtime_hours, statutory_holiday_working_hours, statutory_holiday_prescribed_working_hours '
                 . ' , statutory_holiday_overtime_hours, statutory_overtime_hours, nonstatutory_overtime_hours, nonstatutory_overtime_hours_45h '
                 . ' , nonstatutory_overtime_hours_60h, statutory_overtime_hours_no_pub, nonstatutory_overtime_hours_no_pub '
                 . ' , nonstatutory_overtime_hours_45h_no_pub, nonstatutory_overtime_hours_60h_no_pub, weekdays_midnight_time, holiday_late_at_night_time '
                 . ' , public_holidays_late_at_night_time, legal_holiday_late_at_night_time, considered_overtime, overtime_hours_no_considered '
                 . ' , overtime_hours_no_considered_no_pub, break_time, late_time, leave_early_time, estimate_performance, approximate_schedule '
                 . ' , approval, labor_regulations_id, weekday_night_overtime_hours, holiday_night_overtime_hours, public_night_overtime_hours '
                 . ' , statutory_holiday_night_overtime_hours, modify_count '
                 . ' , weekday_normal_time, weekday_midnight_time_only, holiday_normal_time, holiday_midnight_time_only'
                 . ' , statutory_holiday_normal_time, statutory_holiday_midnight_time_only, public_holiday_normal_time, public_holiday_midnight_time_only FROM t_attendance_tighten WHERE ';
            $sql .= $this->getTargetUserSql( $userIDList, $searchArray );
            $sql .= ' AND closing_date = :closing_date ';

            $searchArray[':closing_date'] = $postArray['closingDate'];

            $attendanceList = array();
            $result = $DBA->executeSQL($sql, $searchArray);
            if( $result === false )
            {
                $Log->trace("END getAttendanceTightenInfo");
                return $attendanceList;
            }
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $attendanceList[ $data['user_id'] ] = array();
                $attendanceList[ $data['user_id'] ][0] = array();
                $attendanceList[ $data['user_id'] ][0] = $data;
            }

            $Log->trace("END getAttendanceTightenInfo");

            return $attendanceList;
        }

        /**
         * 繰り返し対象情報リスト取得
         * @param    $postArray
         * @param    $idName
         * @return   $loopinfoList
         */
        public function getLoopInfoList( $postArray, $idName )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getLoopInfoList");

            $searchArray = array();

            // 対象ユーザIDリスト取得
            $userIDList = $this->getTargetUserIDList( $postArray );

            if( $idName === 'allowance_id' )
            {
                $sql = $this->creatAllowanceTightenSQL( $userIDList, $searchArray );
            }
            else if( $idName === 'holiday_id' )
            {
                $sql = $this->creatHolidaySQL( $userIDList, $idName, $searchArray );
            }
            else
            {
                $sql = $this->creatHolidaySQL( $userIDList, $idName, $searchArray );
            }

            $searchArray[':closing_date'] = $postArray['closingDate'];

            $loopinfoList = array();
            $result = $DBA->executeSQL($sql, $searchArray);
            if( $result === false )
            {
                $Log->trace("END getLoopInfoList");
                return $loopinfoList;
            }
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( empty($loopinfoList[$data['user_id']][0]) )
                {
                    $loopinfoList[$data['user_id']][0] = array();
                }
                array_push( $loopinfoList[$data['user_id']][0], $data );
            }

            $Log->trace("END getLoopInfoList");

            return $loopinfoList;
        }

        /**
         * 日集計リスト作成
         * @param    $postArray
         * @param    $countingUnit
         * @return   $attendanceList
         */
        public function creatAttendanceDaysInfoList( $postArray, $counting )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAttendanceDaysInfoList");

            $daysInfoList = $this->getDaysAggregateInfoList( $postArray );

            $retMonth = $this->setInitialValList();

            $attendanceList = array();
            $monthCnt = 0;
            $userId = 0;
            if( !empty( $daysInfoList ) )
            {
                $userId = $daysInfoList[0]['user_id'];
            }

            foreach( $daysInfoList as $info )
            {
                if( $userId != $info['user_id'] )
                {
                    if( $counting == 3 )
                    {
                        $attendanceList[$userId][$monthCnt] = $retMonth;
                    }
                    $retMonth = $this->setInitialValList();
                }
                $ret = $this->setInitialValList();

                // ユーザID
                $ret['user_id'] = $info['user_id'];
                $retMonth['user_id'] = $info['user_id'];
                // 実労働時間
                $ret['working_time'] = $info['total_working_time_con_minute'];
                $retMonth['working_time'] += $info['total_working_time_con_minute'];
                // 実深夜時間
                $ret['night_working_time'] = $info['night_working_time_con_minute'];
                $retMonth['night_working_time'] += $info['night_working_time_con_minute'];
                // 普通労働時間
                $ret['normal_working_time'] = $info['normal_working_con_minute'];
                $retMonth['normal_working_time'] += $info['normal_working_con_minute'];
                // 普通残業時間
                $ret['normal_overtime_hours'] = $info['normal_overtime_con_minute'];
                $retMonth['normal_overtime_hours'] += $info['normal_overtime_con_minute'];
                // 普通深夜時間
                $ret['normal_night_working_time'] = $info['normal_night_working_con_minute'];
                $retMonth['normal_night_working_time'] += $info['normal_night_working_con_minute'];
                // 普通深夜残業時間
                $ret['normal_night_overtime_hours'] = $info['normal_night_overtime_con_minute'];
                $retMonth['normal_night_overtime_hours'] += $info['normal_night_overtime_con_minute'];
                // 欠勤回数
                $ret['absence_count'] = $info['absence_count'];
                $retMonth['absence_count'] += $info['absence_count'];
                // 遅刻回数
                $ret['being_late_count'] = $info['late_time_count'];
                $retMonth['being_late_count'] += $info['late_time_count'];
                // 早退回数
                $ret['leave_early_count'] = $info['leave_early_time_count'];
                $retMonth['leave_early_count'] += $info['leave_early_time_count'];
                // 所定労働日数
                $ret['prescribed_working_days'] = $info['prescribed_working_days'];
                $retMonth['prescribed_working_days'] += $info['prescribed_working_days'];
                // 実労働日数
                $ret['production_dates'] = $info['attendance_time_count'];
                $retMonth['production_dates'] += $info['attendance_time_count'];
                // 平日出勤日数
                $ret['weekday_attendance_dates'] = $info['weekday_attendance_time_count'];
                $retMonth['weekday_attendance_dates'] += $info['weekday_attendance_time_count'];
                // 休日出勤日数
                $ret['holiday_work_days'] = $info['weekend_attendance_time_count'];
                $retMonth['holiday_work_days'] += $info['weekend_attendance_time_count'];
                // 公休日出勤日数
                $ret['public_holiday_attendance_dates'] = $info['legal_holiday_attendance_time_count'];
                $retMonth['public_holiday_attendance_dates'] += $info['legal_holiday_attendance_time_count'];
                // 法定休日出勤日数
                $ret['legal_holiday_work_days'] = $info['law_closed_attendance_time_count'];
                $retMonth['legal_holiday_work_days'] += $info['law_closed_attendance_time_count'];
                // 所定労働時間
                $ret['prescribed_working_hours'] = $info['prescribed_working_hours'];
                $retMonth['prescribed_working_hours'] += $info['prescribed_working_hours'];
                // 平日労働時間
                $ret['weekday_working_hours'] = $info['weekday_working_con_minute'];
                $retMonth['weekday_working_hours'] += $info['weekday_working_con_minute'];
                // 平日所定労働時間
                $ret['weekday_prescribed_working_hours'] = $info['weekday_prescribed_working_hours'];
                $retMonth['weekday_prescribed_working_hours'] += $info['weekday_prescribed_working_hours'];
                // 平日残業時間
                $ret['weekday_overtime'] = $info['weekday_overtime_con_minute'];
                $retMonth['weekday_overtime'] += $info['weekday_overtime_con_minute'];
                // 休日労働時間
                $ret['holiday_working_hours'] = $info['weekend_working_con_minute'];
                $retMonth['holiday_working_hours'] += $info['weekend_working_con_minute'];
                // 休日所定労働時間
                $ret['holiday_prescribed_working_hours'] = $info['weekend_prescribed_working_hours'];
                $retMonth['holiday_prescribed_working_hours'] += $info['weekend_prescribed_working_hours'];
                // 休日残業時間
                $ret['holiday_overtime_hours'] = $info['weekend_overtime_con_minute'];
                $retMonth['holiday_overtime_hours'] += $info['weekend_overtime_con_minute'];
                // 公休日労働時間
                $ret['public_holiday_working_hours'] = $info['legal_holiday_working_con_minute'];
                $retMonth['public_holiday_working_hours'] += $info['legal_holiday_working_con_minute'];
                // 公休日所定労働時間
                $ret['public_holiday_prescribed_working_hours'] = $info['legal_holiday_prescribed_working_hours'];
                $retMonth['public_holiday_prescribed_working_hours'] += $info['legal_holiday_prescribed_working_hours'];
                // 公休日残業時間
                $ret['public_holiday_overtime_hours'] = $info['legal_holiday_overtime_con_minute'];
                $retMonth['public_holiday_overtime_hours'] += $info['legal_holiday_overtime_con_minute'];
                // 法定休日労働時間
                $ret['statutory_holiday_working_hours'] = $info['law_closed_working_con_minute'];
                $retMonth['statutory_holiday_working_hours'] += $info['law_closed_working_con_minute'];
                // 法定休日所定労働時間
                $ret['statutory_holiday_prescribed_working_hours'] = $info['law_closed_prescribed_working_hours'];
                $retMonth['statutory_holiday_prescribed_working_hours'] += $info['law_closed_prescribed_working_hours'];
                // 法定休日残業時間
                $ret['statutory_holiday_overtime_hours'] = $info['law_closed_overtime_con_minute'];
                $retMonth['statutory_holiday_overtime_hours'] += $info['law_closed_overtime_con_minute'];
                // 法定内残業時間（公休日含む）
                $ret['statutory_overtime_hours'] = $info['statutory_overtime_hours'];
                $retMonth['statutory_overtime_hours'] += $info['statutory_overtime_hours'];
                // 法定外残業時間（公休日含む）（45H以下）
                $ret['nonstatutory_overtime_hours'] = $info['nonstatutory_overtime_hours'];
                $retMonth['nonstatutory_overtime_hours'] += $info['nonstatutory_overtime_hours'];
                // 法定外残業時間（公休日含む）（45H超え60H以下）
                $ret['nonstatutory_overtime_hours_45h'] = $info['nonstatutory_overtime_hours_45h'];
                $retMonth['nonstatutory_overtime_hours_45h'] += $info['nonstatutory_overtime_hours_45h'];
                // 法定外残業時間（公休日含む）（60H超え）
                $ret['nonstatutory_overtime_hours_60h'] = $info['nonstatutory_overtime_hours_60h'];
                $retMonth['nonstatutory_overtime_hours_60h'] += $info['nonstatutory_overtime_hours_60h'];
                // 法定内残業時間（公休日含まない）
                $ret['statutory_overtime_hours_no_pub'] = $info['statutory_overtime_hours_no_pub'];
                $retMonth['statutory_overtime_hours_no_pub'] += $info['statutory_overtime_hours_no_pub'];
                // 法定外残業時間（公休日含まない）（45H以下）
                $ret['nonstatutory_overtime_hours_no_pub'] = $info['nonstatutory_overtime_hours_no_pub'];
                $retMonth['nonstatutory_overtime_hours_no_pub'] += $info['nonstatutory_overtime_hours_no_pub'];
                // 法定外残業時間（公休日含まない）（45H超え60H以下）
                $ret['nonstatutory_overtime_hours_45h_no_pub'] = $info['nonstatutory_overtime_hours_45h_no_pub'];
                $retMonth['nonstatutory_overtime_hours_45h_no_pub'] += $info['nonstatutory_overtime_hours_45h_no_pub'];
                // 法定外残業時間（公休日含まない）（60H超え）
                $ret['nonstatutory_overtime_hours_60h_no_pub'] = $info['nonstatutory_overtime_hours_60h_no_pub'];
                $retMonth['nonstatutory_overtime_hours_60h_no_pub'] += $info['nonstatutory_overtime_hours_60h_no_pub'];
                // 平日深夜時間
                $ret['weekdays_midnight_time'] = $info['weekday_night_working_con_minute'];
                $retMonth['weekdays_midnight_time'] += $info['weekday_night_working_con_minute'];
                // 休日深夜時間
                $ret['holiday_late_at_night_time'] = $info['weekend_night_working_con_minute'];
                $retMonth['holiday_late_at_night_time'] += $info['weekend_night_working_con_minute'];
                // 公休日深夜時間
                $ret['public_holidays_late_at_night_time'] = $info['legal_holiday_night_working_con_minute'];
                $retMonth['public_holidays_late_at_night_time'] += $info['legal_holiday_night_working_con_minute'];
                // 法定休日深夜時間
                $ret['legal_holiday_late_at_night_time'] = $info['law_closed_night_working_con_minute'];
                $retMonth['legal_holiday_late_at_night_time'] += $info['law_closed_night_working_con_minute'];
                // みなし残業時間
                $ret['considered_overtime'] = "";
                $retMonth['considered_overtime'] = $info['fixed_overtime_time'];
                // 残業時間（みなし除く）（公休日含む）
                $ret['overtime_hours_no_considered'] = $info['overtime_hours_no_considered'];
                $retMonth['overtime_hours_no_considered'] += $info['overtime_hours_no_considered'];
                // 残業時間（みなし除く）（公休日含まない）
                $ret['overtime_hours_no_considered_no_pub'] = $info['overtime_hours_no_considered_no_pub'];
                $retMonth['overtime_hours_no_considered_no_pub'] += $info['overtime_hours_no_considered_no_pub'];
                // 休憩時間
                $ret['break_time'] = $info['break_time_con_minute'];
                $retMonth['break_time'] += $info['break_time_con_minute'];
                // 遅刻時間
                $ret['late_time'] = $info['late_time_con_minute'];
                $retMonth['late_time'] += $info['late_time_con_minute'];
                // 早退時間
                $ret['leave_early_time'] = $info['leave_early_time_con_minute'];
                $retMonth['leave_early_time'] += $info['leave_early_time_con_minute'];
                // 概算給与（実績）
                $ret['estimate_performance'] = $info['rough_estimate'];
                $retMonth['estimate_performance'] += $info['rough_estimate'];
                // 概算給与（予定）
                $ret['approximate_schedule'] = $info['shift_rough_estimate'];
                $retMonth['approximate_schedule'] += $info['shift_rough_estimate'];
                // 承認フラグ
                $ret['approval'] = $info['approval'];
                $retMonth['approval'] = $info['approval'];
                // 就業規則ID
                $ret['labor_regulations_id'] = $info['labor_regulations_id'];
                $retMonth['labor_regulations_id'] = $info['labor_regulations_id'];
                // 平日深夜残業時間
                $ret['weekday_night_overtime_hours'] = $info['weekday_night_overtime_con_minute'];
                $retMonth['weekday_night_overtime_hours'] += $info['weekday_night_overtime_con_minute'];
                // 休日深夜残業時間
                $ret['holiday_night_overtime_hours'] = $info['weekend_night_overtime_con_minute'];
                $retMonth['holiday_night_overtime_hours'] += $info['weekend_night_overtime_con_minute'];
                // 公休日深夜残業時間
                $ret['public_night_overtime_hours'] = $info['legal_holiday_night_overtime_con_minute'];
                $retMonth['public_night_overtime_hours'] += $info['legal_holiday_night_overtime_con_minute'];
                // 法定休日深夜残業時間
                $ret['statutory_holiday_night_overtime_hours'] = $info['law_closed_night_overtime_con_minute'];
                $retMonth['statutory_holiday_night_overtime_hours'] += $info['law_closed_night_overtime_con_minute'];
                // 修正回数
                $ret['modify_count'] = $info['modify_count'];
                $retMonth['modify_count'] += $info['modify_count'];
                // 日付
                $ret['date'] = $info['date'];
                // 打刻場所コード
                $ret['embossing_department_code'] = $info['embossing_department_code'];
                // 打刻場所組織名
                $ret['embossing_organization_name'] = $info['embossing_organization_name'];
                // 打刻場所組織略称
                $ret['embossing_abbreviated_name'] = $info['embossing_abbreviated_name'];
                // 打刻状況
                $ret['embossing_status'] = $info['embossing_status'];
                // 勤務状況
                $ret['work_classification'] = $info['work_classification'];
                // シフト出勤時間
                $ret['shift_attendance_time'] = $info['shift_attendance_time'];
                // シフト退勤時間
                $ret['shift_taikin_time'] = $info['shift_taikin_time'];
                // シフト休憩時間
                $ret['shift_break_time'] = $info['shift_break_time'];
                // 出勤時刻
                $ret['attendance_time'] = $info['attendance_time'];
                // 退勤時刻
                $ret['clock_out_time'] = $info['clock_out_time'];
                // 実出勤時刻
                $ret['embossing_attendance_time'] = $info['embossing_attendance_time'];
                // 実退勤時刻
                $ret['embossing_clock_out_time'] = $info['embossing_clock_out_time'];
                // 概算給与（実績+予定）
                $ret['rough_estimate_month'] = $info['rough_estimate_month'];
                $retMonth['rough_estimate_month'] += $info['rough_estimate_month'];
                // 勤怠コメント
                $ret['attendance_comment'] = $info['attendance_comment'];
                // 平日普通
                $ret['weekday_normal_time'] = $info['weekday_working_con_minute'] - $info['weekday_night_working_con_minute'] - $info['weekday_overtime_con_minute'] + $info['weekday_night_overtime_con_minute'];
                $retMonth['weekday_normal_time'] += $info['weekday_working_con_minute'] - $info['weekday_night_working_con_minute'] - $info['weekday_overtime_con_minute'] + $info['weekday_night_overtime_con_minute'];
                // 平日深夜
                $ret['weekday_midnight_time_only'] = $info['weekday_night_working_con_minute'] - $info['weekday_night_overtime_con_minute'];
                $retMonth['weekday_midnight_time_only'] += $info['weekday_night_working_con_minute'] - $info['weekday_night_overtime_con_minute'];
                // 休日普通
                $ret['holiday_normal_time'] = $info['weekend_working_con_minute'] - $info['weekend_night_working_con_minute'] - $info['weekend_overtime_con_minute'] + $info['weekend_night_overtime_con_minute'];
                $retMonth['holiday_normal_time'] += $info['weekend_working_con_minute'] - $info['weekend_night_working_con_minute'] - $info['weekend_overtime_con_minute'] + $info['weekend_night_overtime_con_minute'];
                // 休日深夜
                $ret['holiday_midnight_time_only'] = $info['weekend_night_working_con_minute'] - $info['weekend_night_overtime_con_minute'];
                $retMonth['holiday_midnight_time_only'] += $info['weekend_night_working_con_minute'] - $info['weekend_night_overtime_con_minute'];
                // 法定休日普通
                $ret['statutory_holiday_normal_time'] = $info['law_closed_working_con_minute'] - $info['law_closed_night_working_con_minute'] - $info['law_closed_overtime_con_minute'] + $info['law_closed_night_overtime_con_minute'];
                $retMonth['statutory_holiday_normal_time'] += $info['law_closed_working_con_minute'] - $info['law_closed_night_working_con_minute'] - $info['law_closed_overtime_con_minute'] + $info['law_closed_night_overtime_con_minute'];
                // 法定休日深夜
                $ret['statutory_holiday_midnight_time_only'] = $info['law_closed_night_working_con_minute'] - $info['law_closed_night_overtime_con_minute'];
                $retMonth['statutory_holiday_midnight_time_only'] += $info['law_closed_night_working_con_minute'] - $info['law_closed_night_overtime_con_minute'];
                // 公休日普通
                $ret['public_holiday_normal_time'] = $info['legal_holiday_working_con_minute'] - $info['legal_holiday_night_working_con_minute'] - $info['legal_holiday_overtime_con_minute'] + $info['legal_holiday_night_overtime_con_minute'];
                $retMonth['public_holiday_normal_time'] += $info['legal_holiday_working_con_minute'] - $info['legal_holiday_night_working_con_minute'] - $info['legal_holiday_overtime_con_minute'] + $info['legal_holiday_night_overtime_con_minute'];
                // 公休日深夜
                $ret['public_holiday_midnight_time_only'] = $info['legal_holiday_night_working_con_minute'] - $info['legal_holiday_night_overtime_con_minute'];
                $retMonth['public_holiday_midnight_time_only'] += $info['legal_holiday_night_working_con_minute'] - $info['legal_holiday_night_overtime_con_minute'];
                
                if( empty( $attendanceList[$info['user_id']][$info['attendance_id']] ) )
                {
                    $attendanceList[$info['user_id']][$info['attendance_id']] = array();
                }
                $attendanceList[$info['user_id']][$info['attendance_id']] = $ret;
                $userId = $info['user_id'];
            }
            if( $counting == 3 )
            {
                $attendanceList[$userId][$monthCnt] = $retMonth;
            }

            $Log->trace("END creatAttendanceDaysInfoList");

            return $attendanceList;
        }

        /**
         * 出力対象の給与システムIDリストを取得
         * @param    $userInfoList    出力対象のユーザリスト
         * @return   $sqlWhereIn
         */
        public function getPayrollSystemIDList( $userInfoList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getPayrollSystemIDList");
            
            $ret = array();
            foreach( $userInfoList as $user )
            {
                $isAdd = true;
                foreach( $ret as $val )
                {
                    if( $val['payroll_system_id'] == $user['payroll_system_id'] )
                    {
                        $isAdd = false;
                        break;
                    }
                }
                
                // 重複無し
                if( $isAdd )
                {
                    $setArray = array( 
                                        'payroll_system_id'     => $user['payroll_system_id'],
                                        'payroll_system_name'   => $user['payroll_system_name'], 
                                     );
                    array_push( $ret, $setArray );
                }
            }
            
            $Log->trace("END getPayrollSystemIDList");
            return $ret;
        }

        /**
         * 日集計リスト作成
         * @param    $postArray
         * @param    $counting
         * @param    $idName
         * @return   $loopinfoList
         */
        public function creatLoopDaysInfoList( $postArray, $counting, $idName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatLoopDaysInfoList");

            $daysInfoList = $this->getLoopDaysInfoList( $postArray, $idName );

            $retMonth[0] = $this->setLoopInitialValList( $idName );

            $loopinfoList = array();
            $monthCnt = 0;
            $userId = 0;
            if( !empty( $daysInfoList ) )
            {
                $userId = $daysInfoList[0]['user_id'];
            }

            foreach( $daysInfoList as $info )
            {
                if( $userId != $info['user_id'] )
                {
                    if( $counting == 3 )
                    {
                        $loopinfoList[$userId][$monthCnt] = $retMonth;
                    }
                    $retMonth = array();
                    $retMonth[0] = $this->setLoopInitialValList( $idName );
                }
                $ret = $this->setLoopInitialValList( $idName );

                if( $idName === 'allowance_id' )
                {
                    // ユーザID
                    $ret['user_id'] = $info['user_id'];
                    // 手当ID
                    $ret['allowance_id'] = $info['allowance_id'];
                    // 手当取得回数
                    $ret['allowance_num'] = $info['allowance_cnt'];
                    // 手当金額
                    $ret['allowance_amount'] = $info['allowance_amount'];

                    // ユーザID
                    $retMonth[$info['allowance_id']]['user_id'] = $info['user_id'];
                    // 手当ID
                    $retMonth[$info['allowance_id']]['allowance_id'] = $info['allowance_id'];
                    // 手当取得回数
                    $retMonth[$info['allowance_id']]['allowance_num'] += $info['allowance_cnt'];
                    // 手当金額
                    $retMonth[$info['allowance_id']]['allowance_amount'] += $info['allowance_amount'];

                    if( empty( $loopinfoList[$info['user_id']][$info['attendance_id']][$info['allowance_id']] ))
                    {
                        $loopinfoList[$info['user_id']][$info['attendance_id']][$info['allowance_id']] = array();
                    }
                    $loopinfoList[$info['user_id']][$info['attendance_id']][$info['allowance_id']] = $ret;
                }
                else if( $idName === 'is_holiday' )
                {
                    // 日
                    // ユーザID
                    $ret['user_id'] = $info['user_id'];
                    // 休日ID
                    $ret['holiday_id'] = $info['is_holiday'];
                    // 休日取得回数
                    $ret['holiday_get_dates'] = $info['holiday_get_cnt'];
                    // 休日出勤回数
                    $ret['holiday_work_days'] = $info['holiday_attendance_cnt'];
                    // 休日労働時間
                    $ret['holiday_working_hours'] = $info['total_working_time_con_minute'];
                    // 休日所定内労働時間
                    $ret['holiday_prescribed_working_hours'] = $info['prescribed_working_hours'];
                    // 休日残業時間
                    $ret['holiday_overtime_hours'] = $info['overtime_con_minute'];
                    // 休日深夜労働時間
                    $ret['holiday_late_night_time'] = $info['night_working_time_con_minute'];
                    // 休日深夜残業時間
                    $ret['holiday_midnight_overtime_hours'] = $info['night_overtime_con_minute'];

                    // 月
                    // ユーザID
                    $retMonth[$info['is_holiday']]['user_id'] = $info['user_id'];
                    // 手当ID
                    $retMonth[$info['is_holiday']]['holiday_id'] = $info['is_holiday'];
                    // 休日取得回数
                    $retMonth[$info['is_holiday']]['holiday_get_dates'] += $info['holiday_get_cnt'];
                    // 休日出勤回数
                    $retMonth[$info['is_holiday']]['holiday_work_days'] += $info['holiday_attendance_cnt'];
                    // 休日労働時間
                    $retMonth[$info['is_holiday']]['holiday_working_hours'] += $info['total_working_time_con_minute'];
                    // 休日所定内労働時間
                    $retMonth[$info['is_holiday']]['holiday_prescribed_working_hours'] += $info['prescribed_working_hours'];
                    // 休日残業時間
                    $retMonth[$info['is_holiday']]['holiday_overtime_hours'] += $info['overtime_con_minute'];
                    // 休日深夜労働時間
                    $retMonth[$info['is_holiday']]['holiday_late_night_time'] += $info['night_working_time_con_minute'];
                    // 休日深夜残業時間
                    $retMonth[$info['is_holiday']]['holiday_midnight_overtime_hours'] += $info['night_overtime_con_minute'];

                    if( empty( $loopinfoList[$info['user_id']][$info['attendance_id']][$info['is_holiday']] ))
                    {
                        $loopinfoList[$info['user_id']][$info['attendance_id']][$info['is_holiday']] = array();
                    }
                    $loopinfoList[$info['user_id']][$info['attendance_id']][$info['is_holiday']] = $ret;
                }
                else
                {
                    // 日
                    // ユーザID
                    $ret['user_id'] = $info['user_id'];
                    // 休日ID
                    $ret['holiday_name_id'] = $info['holiday_name_id'];
                    // 休日取得回数
                    $ret['holiday_get_dates'] = $info['holiday_get_cnt'];
                    // 休日出勤回数
                    $ret['holiday_work_days'] = $info['holiday_attendance_cnt'];
                    // 休日労働時間
                    $ret['holiday_working_hours'] = $info['total_working_time_con_minute'];
                    // 休日所定内労働時間
                    $ret['holiday_prescribed_working_hours'] = $info['prescribed_working_hours'];
                    // 休日残業時間
                    $ret['holiday_overtime_hours'] = $info['overtime_con_minute'];
                    // 休日深夜労働時間
                    $ret['holiday_late_night_time'] = $info['night_working_time_con_minute'];
                    // 休日深夜残業時間
                    $ret['holiday_midnight_overtime_hours'] = $info['night_overtime_con_minute'];

                    // 月
                    // ユーザID
                    $retMonth[$info['holiday_name_id']]['user_id'] = $info['user_id'];
                    // 手当ID
                    $retMonth[$info['holiday_name_id']]['holiday_name_id'] = $info['holiday_name_id'];
                    // 休日取得回数
                    $retMonth[$info['holiday_name_id']]['holiday_get_dates'] += $info['holiday_get_cnt'];
                    // 休日出勤回数
                    $retMonth[$info['holiday_name_id']]['holiday_work_days'] += $info['holiday_attendance_cnt'];
                    // 休日労働時間
                    $retMonth[$info['holiday_name_id']]['holiday_working_hours'] += $info['total_working_time_con_minute'];
                    // 休日所定内労働時間
                    $retMonth[$info['holiday_name_id']]['holiday_prescribed_working_hours'] += $info['prescribed_working_hours'];
                    // 休日残業時間
                    $retMonth[$info['holiday_name_id']]['holiday_overtime_hours'] += $info['overtime_con_minute'];
                    // 休日深夜労働時間
                    $retMonth[$info['holiday_name_id']]['holiday_late_night_time'] += $info['night_working_time_con_minute'];
                    // 休日深夜残業時間
                    $retMonth[$info['holiday_name_id']]['holiday_midnight_overtime_hours'] += $info['night_overtime_con_minute'];

                    if( empty( $loopinfoList[$info['user_id']][$info['attendance_id']][$info['holiday_name_id']] ))
                    {
                        $loopinfoList[$info['user_id']][$info['attendance_id']][$info['holiday_name_id']] = array();
                    }
                    $loopinfoList[$info['user_id']][$info['attendance_id']][$info['holiday_name_id']] = $ret;
                }
                $userId = $info['user_id'];
            }
            if( $counting == 3 )
            {
                $loopinfoList[$userId][$monthCnt] = $retMonth;
            }


            $Log->trace("END creatLoopDaysInfoList");

            return $loopinfoList;
        }

        /**
         * 給与出力日集計データ取得
         * @param    $postArray
         * @return   $daysInfoList
         */
        private function getDaysAggregateInfoList( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getDaysAggregateInfoList");

            $searchArray = array();

            // 対象ユーザIDリスト取得
            $userIDList = $this->getTargetUserIDList( $postArray );

            $sql = ' SELECT user_id, attendance_id, date, total_working_time_con_minute , night_working_time_con_minute, ( normal_working_con_minute - normal_overtime_con_minute ) as normal_working_con_minute '
                 . ' , normal_overtime_con_minute, normal_night_working_con_minute, normal_night_overtime_con_minute, absence_count, late_time_count, leave_early_time_count, prescribed_working_days '
                 . ' , attendance_time_count, weekday_attendance_time_count, weekend_attendance_time_count, legal_holiday_attendance_time_count, law_closed_attendance_time_count, prescribed_working_hours '
                 . ' , weekday_working_con_minute, weekday_prescribed_working_hours, weekday_overtime_con_minute, weekend_working_con_minute, weekend_prescribed_working_hours, weekend_overtime_con_minute '
                 . ' , legal_holiday_working_con_minute, legal_holiday_prescribed_working_hours, legal_holiday_overtime_con_minute, law_closed_working_con_minute, law_closed_prescribed_working_hours '
                 . ' , law_closed_overtime_con_minute, statutory_overtime_hours, nonstatutory_overtime_hours, nonstatutory_overtime_hours_45h, nonstatutory_overtime_hours_60h, statutory_overtime_hours_no_pub '
                 . ' , nonstatutory_overtime_hours_no_pub, nonstatutory_overtime_hours_45h_no_pub, nonstatutory_overtime_hours_60h_no_pub, weekday_night_working_con_minute, weekend_night_working_con_minute '
                 . ' , legal_holiday_night_working_con_minute, law_closed_night_working_con_minute, fixed_overtime_time, overtime_hours_no_considered, overtime_hours_no_considered_no_pub, break_time_con_minute '
                 . ' , late_time_con_minute, leave_early_time_con_minute, rough_estimate, shift_rough_estimate, approval, labor_regulations_id, weekday_night_overtime_con_minute, weekend_night_overtime_con_minute '
                 . ' , legal_holiday_night_overtime_con_minute, law_closed_night_overtime_con_minute, modify_count, embossing_department_code, embossing_organization_name, embossing_abbreviated_name, embossing_status '
                 . ' , shift_attendance_time, shift_taikin_time, shift_break_time, attendance_time, clock_out_time, embossing_attendance_time, embossing_clock_out_time, rough_estimate_month, attendance_comment, work_classification '
                 . ' FROM v_attendance_record WHERE ';
            $sql .= $this->getTargetUserSql( $userIDList, $searchArray );
            $sql .= ' AND is_del = 0 AND target_month_tightening = :closing_date ORDER BY user_id, date ';

            $searchArray[':closing_date'] = $postArray['closingDate'];

            $daysInfoList = array();
            $result = $DBA->executeSQL($sql, $searchArray);
            if( $result === false )
            {
                $Log->trace("END getDaysAggregateInfoList");
                return $daysInfoList;
            }
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $daysInfoList, $data );
            }

            $Log->trace("END getDaysAggregateInfoList");

            return $daysInfoList;
        }

        /**
         * 対象ユーザIDリスト取得
         * @param    $postArray
         * @return   $userIDList
         */
        private function getTargetUserIDList( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getTargetUserIDList");

            $searchArray = array();

            $sql = ' SELECT v.user_id FROM v_user v , ( SELECT user_id, max(application_date_start) as application_date_start FROM v_user WHERE ';
            $sql .= $this->getTargetOrganizationSql( $postArray, $searchArray );
            $sql .= ' AND is_del = :is_del AND ( next_application_date_start IS NULL OR next_application_date_start > :start_date ) ';
            $sql .= ' AND application_date_start <= :end_date AND ( leaving_date IS NULL OR leaving_date >= :start_date ) GROUP BY user_id ) nowData ';
            $sql .= ' WHERE v.user_id = nowData.user_id AND v.application_date_start = nowData.application_date_start ';

            list( $year, $month ) = explode( "/", $postArray['closingDate'] );
            $start_date = $year . "/" . $month . "/01";
            $end_date = date('Y/m/d', mktime( 0, 0, 0, $month + 1, 0, $year ) );

            $searchArray[':is_del'] = 0;
            $searchArray[':start_date'] = $start_date;
            $searchArray[':end_date'] = $end_date;

            $userIDList = array();
            $result = $DBA->executeSQL($sql, $searchArray);
            if( $result === false )
            {
                $Log->trace("END getTargetUserIDList");
                return $userIDList;
            }
            $userIdCnt = 0;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $userIDList[$userIdCnt] = $data['user_id'];
                $userIdCnt++;
            }

            $Log->trace("END getTargetUserIDList");

            return $userIDList;
        }

        /**
         * 組織検索範囲SQL取得
         * @param    $postArray
         * @param    &$searchArray
         * @return   $organizationSql
         */
        private function getTargetOrganizationSql( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getTargetOrganizationSql");

            if( !empty( $postArray['isBeneath'] ) )
            {
                $orgIDList = $this->getSalesOrganizationList( $postArray );
                $organizationSql = ' organization_id IN ( ';
                $orgCnt = 1;
                foreach( $orgIDList as $orgID )
                {
                    $keyName = ':organization_id' . $orgCnt;
                    $organizationSql .= $keyName . ',';
                    $searchArray[$keyName] = $orgID;
                    $orgCnt++;
                }
                $organizationSql = substr($organizationSql, 0, -1);
                $organizationSql .= ' ) ';
            }
            else
            {
                $organizationSql = ' organization_id = :organization_id ';
                $searchArray[':organization_id'] = $postArray['organizationID'];
            }

            $Log->trace("END getTargetOrganizationSql");

            return $organizationSql;
        }

        /**
         * 系列組織ID一覧取得
         * @param    $postArray
         * @return   $orgIDList
         */
        private function getSalesOrganizationList( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getSalesOrganizationList");

            $salesOrgList = $this->securityProcess->getDisplayOrderHierarchy( $postArray['organizationID'] );

            $orgIDList = array();
            $orgIDCnt = 0;
            foreach($salesOrgList as $sales)
            {
                foreach($_SESSION["OUTPUT"] as $output)
                {
                    if( $sales['organization_id'] === $output['organization_id'])
                    {
                        $orgIDList[$orgIDCnt] = $sales['organization_id'];
                        $orgIDCnt++;
                    }
                }
            }

            $Log->trace("END getSalesOrganizationList");

            return $orgIDList;
        }

        /**
         * 手当SQL作成
         * @param    $postArray
         * @param    &$searchArray
         * @return   $sql
         */
        private function creatAllowanceTightenSQL( $userIDList, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAllowanceTightenSQL");

            $sql = ' SELECT user_id, allowance_id, allowance_num, allowance_amount FROM t_allowance_tighten WHERE ';
            $sql .= $this->getTargetUserSql( $userIDList, $searchArray );
            $sql .= ' AND closing_date = :closing_date ORDER BY user_id, allowance_id ';

            $Log->trace("END creatAllowanceTightenSQL");

            return $sql;
        }

        /**
         * 休日SQL作成
         * @param    $userIDList
         * @param    $idName
         * @param    &$searchArray
         * @return   $sql
         */
        private function creatHolidaySQL( $userIDList, $idName, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatHolidaySQL");

            $sql = ' SELECT user_id ';
            if( $idName === "holiday_id" )
            {
                $sql .= ' , holiday_get_dates, holiday_work_days, holiday_working_hours '
                      . ' , holiday_prescribed_working_hours, holiday_overtime_hours '
                      . ' , holiday_late_night_time, holiday_midnight_overtime_hours, ';
                $sqlGROUP = '';
            }
            else
            {
                $sql .= ' , SUM( holiday_get_dates ) as holiday_get_dates, SUM( holiday_work_days ) as holiday_work_days '
                      . ' , SUM( holiday_working_hours ) as holiday_working_hours, SUM( holiday_prescribed_working_hours ) as holiday_prescribed_working_hours '
                      . ' , SUM( holiday_overtime_hours ) as holiday_overtime_hours, SUM( holiday_late_night_time ) as holiday_late_night_time '
                      . ' , SUM( holiday_midnight_overtime_hours ) as holiday_midnight_overtime_hours, ';
                $sqlGROUP = ' GROUP BY user_id, holiday_name_id ';
            }
            $sql .= $idName;
            $sql .= ' FROM t_holiday WHERE ';
            $sql .= $this->getTargetUserSql( $userIDList, $searchArray );
            $sql .= ' AND closing_date = :closing_date ';
            $sql .= $sqlGROUP;
            $sql .= ' ORDER BY user_id, ';
            $sql .= $idName;

            $Log->trace("END creatHolidaySQL");

            return $sql;
        }

        /**
         * 対象従業員情報取得
         * @param    $userIDList
         * @param    &$searchArray
         * @return   $userIdSql
         */
        private function getTargetUserSql( $userIDList, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getTargetUserSql");

            $userIdSql = ' user_id IN ( ';
            $userCnt = 1;
            foreach( $userIDList as $userID )
            {
                $keyName = ':user_id' . $userCnt;
                $userIdSql .= $keyName . ',';
                $searchArray[$keyName] = $userID;
                $userCnt++;
            }
            $userIdSql = substr($userIdSql, 0, -1);
            $userIdSql .= ' ) ';

            $Log->trace("END getTargetUserSql");

            return $userIdSql;
        }

        /**
         * 給与出力初期化配列セット
         * @return   $initialValList
         */
        private function setInitialValList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setInitialValList");

            $initialValList = array(
                'user_id'                                       => "",
                'closing_date'                                  => "",
                'working_time'                                  => 0,
                'night_working_time'                            => 0,
                'normal_working_time'                           => 0,
                'normal_overtime_hours'                         => 0,
                'normal_night_working_time'                     => 0,
                'normal_night_overtime_hours'                   => 0,
                'absence_count'                                 => 0,
                'being_late_count'                              => 0,
                'leave_early_count'                             => 0,
                'prescribed_working_days'                       => 0,
                'production_dates'                              => 0,
                'weekday_attendance_dates'                      => 0,
                'holiday_work_days'                             => 0,
                'public_holiday_attendance_dates'               => 0,
                'legal_holiday_work_days'                       => 0,
                'prescribed_working_hours'                      => 0,
                'weekday_working_hours'                         => 0,
                'weekday_prescribed_working_hours'              => 0,
                'weekday_overtime'                              => 0,
                'holiday_working_hours'                         => 0,
                'holiday_prescribed_working_hours'              => 0,
                'holiday_overtime_hours'                        => 0,
                'public_holiday_working_hours'                  => 0,
                'public_holiday_prescribed_working_hours'       => 0,
                'public_holiday_overtime_hours'                 => 0,
                'statutory_holiday_working_hours'               => 0,
                'statutory_holiday_prescribed_working_hours'    => 0,
                'statutory_holiday_overtime_hours'              => 0,
                'statutory_overtime_hours'                      => 0,
                'nonstatutory_overtime_hours'                   => 0,
                'nonstatutory_overtime_hours_45h'               => 0,
                'nonstatutory_overtime_hours_60h'               => 0,
                'statutory_overtime_hours_no_pub'               => 0,
                'nonstatutory_overtime_hours_no_pub'            => 0,
                'nonstatutory_overtime_hours_45h_no_pub'        => 0,
                'nonstatutory_overtime_hours_60h_no_pub'        => 0,
                'weekdays_midnight_time'                        => 0,
                'holiday_late_at_night_time'                    => 0,
                'public_holidays_late_at_night_time'            => 0,
                'legal_holiday_late_at_night_time'              => 0,
                'considered_overtime'                           => "-",
                'overtime_hours_no_considered'                  => 0,
                'overtime_hours_no_considered_no_pub'           => 0,
                'break_time'                                    => 0,
                'late_time'                                     => 0,
                'leave_early_time'                              => 0,
                'estimate_performance'                          => 0,
                'approximate_schedule'                          => 0,
                'approval'                                      => 0,
                'labor_regulations_id'                          => 0,
                'weekday_night_overtime_hours'                  => 0,
                'holiday_night_overtime_hours'                  => 0,
                'public_night_overtime_hours'                   => 0,
                'statutory_holiday_night_overtime_hours'        => 0,
                'modify_count'                                  => 0,
                'date'                                          => "-",
                'embossing_department_code'                     => "-",
                'embossing_organization_name'                   => "-",
                'embossing_abbreviated_name'                    => "-",
                'embossing_status'                              => "-",
                'shift_attendance_time'                         => "-",
                'shift_taikin_time'                             => "-",
                'shift_break_time'                              => "-",
                'attendance_time'                               => "-",
                'clock_out_time'                                => "-",
                'embossing_attendance_time'                     => "-",
                'embossing_clock_out_time'                      => "-",
                'rough_estimate_month'                          => 0,
                'attendance_comment'                            => "-",
                'work_classification'                           => "-",
                'weekday_normal_time'                           => 0,
                'weekday_midnight_time_only'                    => 0,
                'holiday_normal_time'                           => 0,
                'holiday_midnight_time_only'                    => 0,
                'statutory_holiday_normal_time'                 => 0,
                'statutory_holiday_midnight_time_only'          => 0,
                'public_holiday_normal_time'                    => 0,
                'public_holiday_midnight_time_only'             => 0,
            );

            $Log->trace("END setInitialValList");

            return $initialValList;
        }

        /**
         * 給与データ出力マスタ一覧取得SQL文作成
         * @return   給与データ出力マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql  = " SELECT u.organization_id, a.closing_date, a.approval, u.user_id, u.abbreviated_name, u.o_disp_order "
                 .  " FROM t_attendance_tighten a "
                 .  "      INNER JOIN v_user u ON a.user_id = u.user_id AND u.eff_code = '適用中' AND :start = a.closing_date ";
            $sql .= " WHERE " . $this->creatSqlWhereIn();
            $userIDList = $this->getTargetUserIDList( $postArray );
            $sql .= " AND u.user_id IN ( ";
            foreach( $userIDList as $userID )
            {
                $sql .=  $userID . ",";
            }
            $sql = substr( $sql, 0, -1);
            
            $sql .= " ) " . "ORDER BY u.organization_id, a.closing_date, u.user_id";

            //出力対象年月をYMDの配列に直す
            $closingDateArray = array(
                                    ':start' => $postArray['closingDate'],
                                    );
            $searchArray = array_merge($searchArray, $closingDateArray);

            $Log->trace("END creatSQL");
            return $sql;
        }
        
        /**
         * 給与データ出力画面ソート時の閲覧範囲組織限定のためのSQL文作成
         * @return   $sqlWhereIn
         */
        private function creatSqlWhereIn()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSqlWhereIn");
            
            $sqlWhereIn = "";
            if( 0 < count( $_SESSION["REFERENCE"] ) )
            {
                $sqlWhereIn = ' u.organization_id IN ( ';
                foreach($_SESSION["REFERENCE"] as $viewable)
                {
                    $sqlWhereIn .=  $viewable['organization_id'] . ', ';
                }
                $sqlWhereIn = substr($sqlWhereIn, 0, -2);
                $sqlWhereIn .= ' ) ';
            }
            $Log->trace("END creatSqlWhereIn");
            return $sqlWhereIn;
        }

        /**
         * 繰り返し対象情報リスト取得
         * @param    $postArray
         * @param    $idName
         * @return   $loopinfoList
         */
        private function getLoopDaysInfoList( $postArray, $idName )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getLoopDaysInfoList");

            $searchArray = array();

            // 対象ユーザIDリスト取得
            $userIDList = $this->getTargetUserIDList( $postArray );

            if( $idName === 'allowance_id' )
            {
                $sql = $this->creatAllowanceDaysSQL( $userIDList, $searchArray );
            }
            else if( $idName === 'is_holiday' )
            {
                $sql = $this->creatHolidayInfoDaysSQL( $userIDList, $idName, $searchArray );
            }
            else
            {
                $sql = $this->creatHolidayInfoDaysSQL( $userIDList, $idName, $searchArray );
            }

            $searchArray[':closing_date'] = $postArray['closingDate'];

            $loopinfoList = array();
            $result = $DBA->executeSQL($sql, $searchArray);
            if( $result === false )
            {
                $Log->trace("END getLoopDaysInfoList");
                return $loopinfoList;
            }
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $loopinfoList, $data );
            }

            $Log->trace("END getLoopDaysInfoList");

            return $loopinfoList;
        }

        /**
         * 手当情報日集計取得
         * @param    $userIDList
         * @param    &$searchArray
         * @return   $sqlWhereIn
         */
        private function creatAllowanceDaysSQL( $userIDList, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAllowanceDaysSQL");

            $sql = ' SELECT user_id, attendance_id, allowance_id, allowance_cnt, allowance_amount, date FROM v_allowance_table  WHERE ';
            $sql .= $this->getTargetUserSql( $userIDList, $searchArray );
            $sql .= ' AND is_del = 0 AND target_month_tightening = :closing_date ORDER BY user_id, allowance_id, attendance_id ';

            $Log->trace("END creatAllowanceDaysSQL");

            return $sql;
        }

        /**
         * 休日情報日集計取得
         * @param    $userIDList
         * @param    $mainIdName
         * @param    &$searchArray
         * @return   $sqlWhereIn
         */
        private function creatHolidayInfoDaysSQL( $userIDList, $mainIdName, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSqlWhereIn");

            $sql = ' SELECT user_id, attendance_id, holiday_get_cnt, holiday_attendance_cnt '
                 . ' , total_working_time_con_minute, prescribed_working_hours, overtime_con_minute'
                 . ' , night_working_time_con_minute, night_overtime_con_minute, date, ';
            $sql .= $mainIdName;
            $sql .= ' FROM v_attendance_record ';
            $sql .= ' WHERE ';
            $sql .= $this->getTargetUserSql( $userIDList, $searchArray );
            $sql .= ' AND is_holiday > 0 AND is_holiday < 99999998 ';
            $sql .= ' AND is_del = 0 AND target_month_tightening = :closing_date ORDER BY user_id, ';
            $sql .= $mainIdName;
            $sql .= ' , attendance_id ';

            $Log->trace("END creatAllowanceDaysSQL");

            return $sql;
        }

        /**
         * 給与出力初期化配列セット
         * @param    $idName
         * @return   $initialValList
         */
        private function setLoopInitialValList( $idName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setLoopInitialValList");

            if( $idName === 'allowance_id' )
            {
                $initialValList = array(
                    'user_id'                                       => "",
                    'allowance_id'                                  => "",
                    'allowance_num'                                 => 0,
                    'allowance_amount'                              => 0,
                );
            }
            else if( $idName === 'is_holiday' )
            {
                $initialValList = array(
                    'user_id'                                       => "",
                    'holiday_id'                                    => "",
                    'holiday_get_dates'                             => 0,
                    'holiday_work_days'                             => 0,
                    'holiday_working_hours'                         => 0,
                    'holiday_prescribed_working_hours'              => 0,
                    'holiday_overtime_hours'                        => 0,
                    'holiday_late_night_time'                       => 0,
                    'holiday_midnight_overtime_hours'               => 0,
                );
            }
            else
            {
                $initialValList = array(
                    'user_id'                                       => "",
                    'holiday_name_id'                               => "",
                    'holiday_get_dates'                             => 0,
                    'holiday_work_days'                             => 0,
                    'holiday_working_hours'                         => 0,
                    'holiday_prescribed_working_hours'              => 0,
                    'holiday_overtime_hours'                        => 0,
                    'holiday_late_night_time'                       => 0,
                    'holiday_midnight_overtime_hours'               => 0,
                );
            }

            $Log->trace("END setLoopInitialValList");

            return $initialValList;
        }
        
        /**
         * 最新の承認状態を取得する
         * @param    $userID        ユーザID
         * @param    $targetMonth   〆月
         * @return   SQLの実行結果
         */
        private function isApproval( $userID, $targetMonth )
        {
            global $DBA,$Log; // グローバル変数宣言
            $Log->trace("START isApproval");
            
            $sql = ' SELECT COUNT( attendance_id ) as count FROM  v_attendance_record '
                 . ' WHERE approval = 0 AND is_del = 0 AND '
                 . '       target_month_tightening = :target_month_tightening AND user_id = :user_id';
            $searchArray = array(
                                  ':target_month_tightening'  => $targetMonth, 
                                  ':user_id'                  => $userID, 
                                 );
            $result = $DBA->executeSQL( $sql, $searchArray );
            
            $ret = false;
            if( $result === false )
            {
                $Log->trace("END isApproval");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( 0 == $data['count'] )
                {
                    $ret = true;
                }
            }

            $Log->trace("END  isApproval");

            return $ret;
        }
    }
?>
