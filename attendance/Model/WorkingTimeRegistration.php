<?php
    /**
     * @file      労働時間登録モデル
     * @author    USE Y.Sakata
     * @date      2016/08/09
     * @version   1.00
     * @note      労働時間登録に必要なDBアクセスの制御を行う
     */

    // BaseWorkingTime.phpを読み込む
    require_once './Model/BaseWorkingTime.php';

    /**
     * 労働時間登録クラス
     * @note   労働時間登録に必要なDBアクセスの制御を行う
     */
    class WorkingTimeRegistration extends BaseWorkingTime
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
         * 1勤務の勤怠時間を保存する
         * @param    $attendanceID     勤怠ID
         * @return   SQLの実行結果
         */
        public function setAttendanceInfo( $attendanceID )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setAttendanceInfo");
            
            // 勤怠情報を取得する
            $attendanceInfo = $this->getAttendanceInfo( $attendanceID );

            // 就業規則取得
            $lrAllInfo = $this->getLaborRegulationsAllInfo( $attendanceInfo['labor_regulations_id'], $attendanceInfo['date'] );

            // 1勤務の拘束時間(分)を設定
            $bindingHour = 0;
            if( !is_null( $attendanceInfo['attendance_time'] ) && !is_null( $attendanceInfo['clock_out_time'] ) )
            {
                $bindingHour = ( strtotime( $attendanceInfo['clock_out_time'] ) - strtotime( $attendanceInfo['attendance_time'] ) ) / 60;
            }

            // 休憩時間の設定
            $breakTime = $this->getBreakTime( $attendanceInfo, $lrAllInfo, $bindingHour );
            // 日の休憩時間丸め
            $breakTime['dayTimeBreakTime'] = $this->roundingTime( $lrAllInfo, $breakTime['dayTimeBreakTime'] );
            $breakTime['nightBreakTime'] = $this->roundingTime( $lrAllInfo, $breakTime['nightBreakTime'] );

            // 1日出勤/退勤の基準時間を求める
            $openingTime = 0;   // 始業時間
            $closingTime = 0;   // 終業時間
            // 就業規則を使用
            if( $lrAllInfo['m_work_rules_time'][0]['is_work_rules_working_hours_use'] == 1 )
            {
                // 始業時間
                $openingTime = $this->timeTransformation( $lrAllInfo['m_work_rules_time'][0]['start_working_hours'] );
                // 終業時間
                $closingTime = $this->timeTransformation( $lrAllInfo['m_work_rules_time'][0]['end_working_hours'] );
            }
            // シフトを使用
            if( $lrAllInfo['m_work_rules_time'][0]['is_shift_working_hours_use'] == 1 && $attendanceInfo['shift_id'] != 0 )
            {
                // シフト時間を使用する
                $shiftInfo = $this->getShiftTime( $attendanceInfo['shift_id'] );

                // 始業時間
                $openingTime = $shiftInfo['attendance'];
                // 終業時間
                $closingTime = $shiftInfo['taikin'];
            }

            // 1日の出勤/退勤時間を算出する
            $attendanceTime = $this->timeTransformation( $attendanceInfo['attendance_time'], $attendanceInfo['date'] );
            $clockOutTime   = $this->timeTransformation( $attendanceInfo['clock_out_time'],  $attendanceInfo['date'] );

            $lateTime = 0;
            // 始業時間が未設定(チェックなし)の場合、
            if( $openingTime != 0 )
            {
                // 遅刻時間を計算
                $lateTime = $attendanceTime - $openingTime;
                $lateTime = $lateTime < 0 ? 0 : $lateTime;
            }
            
            $earlyTime = 0;
            // 終業時間が未設定(チェックなし)の場合、
            if( $clockOutTime != 0 )
            {
                // 早退時間を計算
                $earlyTime = $closingTime - $clockOutTime;
                $earlyTime = $earlyTime < 0 ? 0 : $earlyTime;
            }

            // 休日情報を取得
            $holidayInfo = $this->getHolidayTimeInfo( $attendanceInfo['is_holiday'] );

            // 移動時間の取得
            $travelTimeList = $this->getTravelTime( $attendanceInfo['date'], $attendanceInfo['user_id'], $attendanceID, $lrAllInfo );
            $travelTime = $travelTimeList['dayTime'] + $travelTimeList['nightTime'];

            // 総労働時間を計算( 拘束時間 - 休憩時間 )
            $totalWorkTime = $bindingHour - $breakTime['dayTimeBreakTime'] - $breakTime['nightBreakTime'] + ( $holidayInfo['working_hours'] ) + $travelTime;
            // 日の労働時間丸め
            $totalWorkTime = $this->roundingTime( $lrAllInfo, $totalWorkTime );

            // 深夜労働時間を取得
            $nightWorkHour = $this->getNightWorkingHours( $lrAllInfo, $attendanceTime, $clockOutTime ) - $breakTime['nightBreakTime'];
            // 深夜労働時間を日の労働時間丸め
            $nightWorkHour = $this->roundingTime( $lrAllInfo, $nightWorkHour );

            // 勤務時間帯を取得
            $workHours = $this->getWorkHours( $attendanceTime, $clockOutTime );

            // 時給変更帯情報を取得
            $changeHourlyWageList = $this->getChangeHourlyWage( $attendanceTime, $clockOutTime, $lrAllInfo, $attendanceInfo['organization_id'] );

            // 残業時間の取得 2017/08/10 millionet oota 早朝深夜を考慮してパラメーターを修正
            $overtimeList = $this->getOvertimeHours( $attendanceInfo['user_id'], $lrAllInfo, $totalWorkTime, $nightWorkHour, $attendanceInfo['date'], $attendanceID, 't_attendance', $attendanceTime, $clockOutTime );

            // 1勤怠の概算を取得
            $setModOvertimeList = array();
            $addAllowanceList = array();
            $money = $this->getApproximateAmountMoney( $attendanceInfo, $lrAllInfo, $totalWorkTime, $nightWorkHour, $overtimeList, $setModOvertimeList, 't_attendance', $changeHourlyWageList, $addAllowanceList );
            $totalWorkHour = $totalWorkTime / 60;
            
            $money1h = 0;
            if( $totalWorkHour != 0 )
            {
                $money1h = round( $money / $totalWorkHour );
            }

            // 休日出勤の場合、労働時間全てを、残業時間とする ← ×
            $overtime = $this->changeTimeFromMinute( $overtimeList['inOvertime'] + $overtimeList['outOvertime'] );
            $nightOvertime = $this->changeTimeFromMinute( $overtimeList['nightOvertime'] );
            if( $attendanceInfo['is_holiday'] != 0 && $attendanceInfo['is_holiday'] != SystemParameters::$ATTENDANCE && $attendanceInfo['is_holiday'] != SystemParameters::$ABSENCE )
            {
//                // 勤務時間/勤務日数の設定が0の場合、すべて残業とする
//                if( $holidayInfo['working_hours'] == 0 && $holidayInfo['working_day'] == 0 )
//                {
//                    $overtime = $this->changeTimeFromMinute( $totalWorkTime );
//                    $nightOvertime = $this->changeTimeFromMinute( $nightWorkHour );
//                }
            }

            // 休憩時間の設定
            $setBreakTime = $breakTime['dayTimeBreakTime'] + $breakTime['nightBreakTime'];
            if( $totalWorkTime <= 0 )
            {
                // 労働時間が0場合、休憩時間/早退時間も0に設定
                $setBreakTime = 0;
                $earlyTime = 0;
            }

            // 所定労働時間を取得
            $predeterminedTime = $this->getPredeterminedTime( $attendanceInfo['user_id'], $lrAllInfo, $attendanceInfo['date'], 't_attendance' );

            // DB登録用パラメータ設定
            $parameters = array( 
                                    ':attendance_id'        => $attendanceID,
                                    ':break_time'           => $this->changeTimeFromMinute( $setBreakTime ), 
                                    ':late_time'            => $this->changeTimeFromMinute( $lateTime ), 
                                    ':leave_early_time'     => $this->changeTimeFromMinute( $earlyTime ),
                                    ':total_working_time'   => $this->changeTimeFromMinute( $totalWorkTime ),
                                    ':night_working_time'   => $this->changeTimeFromMinute( $nightWorkHour ),
                                    ':travel_time'          => $this->changeTimeFromMinute( $travelTime ),
                                    ':work_hours'           => $workHours, 
                                    ':overtime'             => $overtime, 
                                    ':night_overtime'       => $nightOvertime, 
                                    ':rough_estimate'       => $money, 
                                    ':rough_estimate_month' => $money, 
                                    ':rough_estimate_hour'  => $money1h, 
                                    ':prescribed_working_hours'                 => $predeterminedTime['workingHoursTime'],
                                    ':statutory_overtime_hours'                 => $setModOvertimeList['statutory_overtime_hours'], 
                                    ':nonstatutory_overtime_hours'              => $setModOvertimeList['nonstatutory_overtime_hours'], 
                                    ':nonstatutory_overtime_hours_45h'          => $setModOvertimeList['nonstatutory_overtime_hours_45h'], 
                                    ':nonstatutory_overtime_hours_60h'          => $setModOvertimeList['nonstatutory_overtime_hours_60h'], 
                                    ':statutory_overtime_hours_no_pub'          => $setModOvertimeList['statutory_overtime_hours_no_pub'], 
                                    ':nonstatutory_overtime_hours_no_pub'       => $setModOvertimeList['nonstatutory_overtime_hours_no_pub'], 
                                    ':nonstatutory_overtime_hours_45h_no_pub'   => $setModOvertimeList['nonstatutory_overtime_hours_45h_no_pub'], 
                                    ':nonstatutory_overtime_hours_60h_no_pub'   => $setModOvertimeList['nonstatutory_overtime_hours_60h_no_pub'], 
                                    ':overtime_hours_no_considered'             => $setModOvertimeList['overtime_hours_no_considered'], 
                                    ':overtime_hours_no_considered_no_pub'      => $setModOvertimeList['overtime_hours_no_considered_no_pub'], 
                                );

            $ret = $this->setAttendanceTime( $parameters );
            // 正常以外
            if( "MSG_BASE_0000" != $ret )
            {
                // 登録失敗
                $Log->trace("END   setAttendanceInfo");
                return $ret;
            }

            $Log->trace("END   setAttendanceInfo");

            return $this->setAllowance( $addAllowanceList );
        }

        /**
         * 手当情報を登録する
         * @param    $addAllowanceList     割り当て手当情報リスト
         * @return   更新結果
         */
        private function setAllowance( $addAllowanceList )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START setAllowance");
            
            if( count( $addAllowanceList ) == 0 )
            {
                // 割り当て手当なし
                $Log->trace("END setAllowance");
                return "MSG_BASE_0000";
            }
            
            // 手当情報を一度削除する
            $delSql = " DELETE FROM t_allowance WHERE user_id = :user_id AND attendance_id = :attendance_id ";
            $delPra = array( ':user_id' => $addAllowanceList[0]['user_id'], 
                             ':attendance_id'  => $addAllowanceList[0]['attendance_id'], );
            if( !$DBA->executeSQL( $delSql, $delPra ) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3128");
                $errMsg = "勤怠ID：" . $addAllowanceList[0]['attendance_id'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3128";
            }
            
            // 手当情報更新用SQL
            $sql  = " INSERT INTO t_allowance( user_id, attendance_id, allowance_id, allowance_amount ) "
                  . "      VALUES ( :user_id, :attendance_id, :allowance_id, :allowance_amount ) ";

            // 手当数分ループ
            foreach( $addAllowanceList as $addAllowance )
            {
                if( !$DBA->executeSQL( $sql, $addAllowance, true ) )
                {
                    // SQL実行エラー
                    $Log->warn("MSG_ERR_3129");
                    $errMsg = "勤怠ID：" . $addAllowance['attendanceId'] . "の登録失敗";
                    $Log->warnDetail($errMsg);
                    return "MSG_ERR_3129";
                }
            }

            $Log->trace("END setAllowance");

            return "MSG_BASE_0000";
        }

        /**
         * 勤怠情報を登録する
         * @param    $parameters     更新パラメータ
         * @return   更新結果
         */
        private function setAttendanceTime( $parameters )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START setAttendanceTime");

            // 勤怠情報を更新
            $sql  = " UPDATE t_attendance SET "
                  . "        break_time = :break_time "
                  . "      , late_time = :late_time "
                  . "      , leave_early_time = :leave_early_time "
                  . "      , total_working_time = :total_working_time "
                  . "      , night_working_time = :night_working_time "
                  . "      , work_hours = :work_hours "
                  . "      , overtime = :overtime "
                  . "      , travel_time = :travel_time "
                  . "      , night_overtime = :night_overtime "
                  . "      , rough_estimate = :rough_estimate "
                  . "      , rough_estimate_month = :rough_estimate_month "
                  . "      , rough_estimate_hour = :rough_estimate_hour "
                  . "      , prescribed_working_hours = :prescribed_working_hours "
                  . "      , statutory_overtime_hours = :statutory_overtime_hours "
                  . "      , nonstatutory_overtime_hours = :nonstatutory_overtime_hours "
                  . "      , nonstatutory_overtime_hours_45h = :nonstatutory_overtime_hours_45h "
                  . "      , nonstatutory_overtime_hours_60h = :nonstatutory_overtime_hours_60h "
                  . "      , statutory_overtime_hours_no_pub = :statutory_overtime_hours_no_pub "
                  . "      , nonstatutory_overtime_hours_no_pub = :nonstatutory_overtime_hours_no_pub "
                  . "      , nonstatutory_overtime_hours_45h_no_pub = :nonstatutory_overtime_hours_45h_no_pub "
                  . "      , nonstatutory_overtime_hours_60h_no_pub = :nonstatutory_overtime_hours_60h_no_pub "
                  . "      , overtime_hours_no_considered = :overtime_hours_no_considered "
                  . "      , overtime_hours_no_considered_no_pub = :overtime_hours_no_considered_no_pub "
                  . " WHERE attendance_id = :attendance_id ";

            if( !$DBA->executeSQL( $sql, $parameters, true ) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3117");
                $errMsg = "勤怠ID：" . $parameters['attendanceId'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3117";
            }

            $Log->trace("END setAttendanceTime");

            return "MSG_BASE_0000";
        }

        /**
         * 休日情報を取得する
         * @param    $holidayID     休日ID
         * @return   休日情報
         */
        private function getHolidayTimeInfo( $holidayID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getHolidayTimeInfo");
            
            // 勤怠IDから勤怠情報を取得
            $sql  = " SELECT working_hours, working_day "
                  . " FROM m_holiday WHERE holiday_id = :holiday_id AND is_del = 0 ";

            $parameters = array( ':holiday_id'  => $holidayID, );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array(
                            'working_hours'  =>  0,
                            'working_day'    =>  0,
                        );
            if( $result === false )
            {
                $Log->trace("END getAttendanceInfo");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret['working_hours'] = $data['working_hours'];
                $ret['working_day']   = $data['working_day'];
            }

            $Log->trace("END getHolidayTimeInfo");
            return $ret;
        }

        /**
         * 勤怠情報を取得する
         * @param    $attendanceID     勤怠ID
         * @return   勤怠情報
         */
        private function getAttendanceInfo( $attendanceID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAttendanceInfo");
            
            // 勤怠IDから勤怠情報を取得
            $sql  = " SELECT attendance_id, user_id, organization_id, date, is_holiday, shift_id "
                  . "      , attendance_time, clock_out_time, s_break_time_1, e_break_time_1 "
                  . "      , s_break_time_2, e_break_time_2, s_break_time_3, e_break_time_3, labor_regulations_id "
                  . " FROM t_attendance "
                  . " WHERE attendance_id = :attendance_id ";

            $parameters = array( ':attendance_id'  => $attendanceID, );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getAttendanceInfo");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data;
            }

            $Log->trace("END getAttendanceInfo");
            return $ret;
        }

        /**
         * シフト情報を取得する
         * @param    $shiftID          シフトID
         * @return   シフト情報
         */
        private function getShiftTime( $shiftID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getShiftTime");
            
            // シフト情報を取得
            $sql  = " SELECT attendance, taikin FROM t_shift WHERE  shift_id = :shift_id ";

            $parameters = array( 
                                    ':shift_id'    => $shiftID, 
                                );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array(
                            'attendance'    =>  0,
                            'taikin'        =>  0,
                        );
            if( $result === false )
            {
                $Log->trace("END getShiftTime");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                // シフトの出勤/退勤情報を取得
                $ret['attendance'] = $this->timeTransformation( $data['attendance'] );
                $ret['taikin'] = $this->timeTransformation( $data['taikin'] );
            }

            $Log->trace("END getShiftTime");

            return $ret;
        }

        /**
         * 勤怠の移動時間を取得
         * @param    $date           労働日
         * @param    $userID         ユーザID
         * @param    $attendanceID   勤怠ID
         * @param    $lrAllInfo      就業規則情報
         * @return   移動時間(分)
         */
        private function getTravelTime( $date, $userID, $attendanceID, $lrAllInfo )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getTravelTime");
            
            // 初期化
            $ret = array(
                            'dayTime'   => 0,
                            'nightTime' => 0,
                        );

            // 移動時間が勤務時間としてみなされるか
            if( $lrAllInfo['m_work_rules_time'][0]['work_handling_travel'] == 2 )
            {
                // 移動時間を勤務としてみなさない
                $Log->trace("END getTravelTime");
                return $ret;
            }

            // 労働日の勤務回数分、勤怠IDを取得
            $sql  = " SELECT attendance_id, organization_id, attendance_time, clock_out_time FROM t_attendance "
                  . " WHERE date = :date AND user_id = :user_id AND is_del = 0 ORDER BY attendance_time ";
            $parameters = array( 
                                 ':date'    => $date,
                                 ':user_id' => $userID,
                               );
            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END getTravelTime");
                return $ret;
            }

            $attendanceList = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $attendanceList, $data );
            }

            $attendanceCnt = count( $attendanceList );
            if( $attendanceCnt <= 1 )
            {
                // 勤務回数が1回以下の場合、移動無し
                $Log->trace("END getTravelTime");
                return $ret;
            }

            $isTarget = false;
            $beforeStore   = 0;
            $beforeAtime   = 0;
            $beforeTtime   = 0;
            $afterStore    = 0;
            $afterAtime    = 0;
            $afterTtime    = 0;
            $targetStore   = 0;
            $targetAtime   = 0;
            $targetTtime   = 0;
            // 勤務が複数回ある場合
            for( $i = 0; $i < $attendanceCnt; $i++ )
            {
                // 現在の登録対象の勤怠である
                if( $attendanceID == $attendanceList[$i]['attendance_id'] )
                {
                    $targetStore = $attendanceList[$i]['organization_id'];
                    $targetAtime = $this->timeTransformation( $attendanceList[$i]['attendance_time'], $date );
                    $targetTtime = $this->timeTransformation( $attendanceList[$i]['clock_out_time'], $date );
                    $isTarget = true;
                }
                else if( $isTarget == true )
                {
                    // 移動先の情報を設定
                    $afterStore = $attendanceList[$i]['organization_id'];
                    $afterAtime = $this->timeTransformation( $attendanceList[$i]['attendance_time'], $date );
                    $afterTtime = $this->timeTransformation( $attendanceList[$i]['clock_out_time'], $date );
                    break;
                }
                else
                {
                    // 移動元の情報を設定
                    $beforeStore = $attendanceList[$i]['organization_id'];
                    $beforeAtime = $this->timeTransformation( $attendanceList[$i]['attendance_time'], $date );
                    $beforeTtime = $this->timeTransformation( $attendanceList[$i]['clock_out_time'], $date );
                }
            }

            $totalTravelHour = 0;
            $nightTravelHour = 0;
            // 移動時間をどちらにつけるか
            if( $lrAllInfo['m_work_rules_time'][0]['recorded_travel_time'] == 1 )
            {
                // 移動先
                if( $targetStore != $afterStore && $afterStore != 0 )
                {
                    // 移動時間を計算する
                    // 総移動時間
                    $totalTravelHour = $afterAtime - $targetTtime;
                    // 深夜移動時間を取得
                    $nightTravelHour = $this->getNightWorkingHours( $lrAllInfo, $targetTtime, $afterAtime );
                }
            }
            else
            {
                // 移動元
                if( $targetStore != $beforeStore && $beforeStore != 0 )
                {
                    // 移動時間を計算する
                    // 総移動時間
                    $totalTravelHour = $targetAtime - $beforeTtime;
                    // 深夜移動時間を取得
                    $nightTravelHour = $this->getNightWorkingHours( $lrAllInfo, $beforeTtime, $targetAtime );
                }
            }

            // 最大移動時間の調整
            $maxTravelTime = $this->changeMinuteFromTime( $lrAllInfo['m_work_rules_time'][0]['work_handling_travel_time'] );
            if( $maxTravelTime < $totalTravelHour )
            {
                $totalTravelHour = $maxTravelTime;
            }

            if( $maxTravelTime < $nightTravelHour )
            {
                $nightTravelHour = $maxTravelTime;
            }

            // 移動時間を設定
            $ret['dayTime']   = $totalTravelHour - $nightTravelHour;
            // 深夜の移動時間のほうが長い
            if( $totalTravelHour < $nightTravelHour )
            {
                $ret['dayTime'] = 0;
            }
            $ret['nightTime'] = $nightTravelHour;

            $Log->trace("END getTravelTime");
            return $ret;
        }
    }
?>
