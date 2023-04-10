<?php
    /**
     * @file      シフト労働時間登録モデル
     * @author    USE Y.Sakata
     * @date      2016/08/22
     * @version   1.00
     * @note      シフト労働時間登録に必要なDBアクセスの制御を行う
     */

    // BaseWorkingTime.phpを読み込む
    require_once './Model/BaseWorkingTime.php';

    /**
     * シフト労働時間登録クラス
     * @note   シフト労働時間登録に必要なDBアクセスの制御を行う
     */
    class ShiftTimeRegistration extends BaseWorkingTime
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
         * 1シフトの勤怠時間を保存する
         * @param    $shiftID         勤怠ID
         * @return   SQLの実行結果
         */
        public function setShiftInfo( $shiftID )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setShiftInfo");
            
            // シフト情報を取得する
            $shiftInfo = $this->getShiftTimeInfo( $shiftID );

            // ユーザ情報を取得
            $userInfo = $this->getUserInfo( $shiftInfo['user_id'], $shiftInfo['day'] );
            // 就業規則情報を取得
            $employInfo = $this->getUserEmploymentInfo($userInfo);

            // 就業規則取得
            $lrAllInfo = $this->getLaborRegulationsAllInfo( $employInfo['labor_regulations_id'], $shiftInfo['day'] );

            // 1勤務の拘束時間(分)を設定
            $bindingHour = 0;
            if( !is_null( $shiftInfo['attendance'] ) && !is_null( $shiftInfo['taikin'] ) )
            {
                $bindingHour = ( $shiftInfo['taikin'] - $shiftInfo['attendance'] );
            }

            // 休憩時間の設定
            $breakTime = $shiftInfo['break_time'];

            // 1日の出勤/退勤時間を算出する
            $attendanceTime = $shiftInfo['attendance'];
            $clockOutTime   = $shiftInfo['taikin'];

            // 移動時間の取得
            $travelTimeList = $this->getTravelTime( $shiftInfo['day'], $shiftInfo['user_id'], $shiftID, $lrAllInfo );
            $travelTime = $travelTimeList['dayTime'] + $travelTimeList['nightTime'];

            // 総労働時間を計算( 拘束時間 - 休憩時間 + 移動時間 )
            $totalWorkTime = $bindingHour - $breakTime + $travelTime;

            // 深夜労働時間を取得
            $nightWorkHour = $this->getNightWorkingHours( $lrAllInfo, $attendanceTime, $clockOutTime ) + $travelTimeList['nightTime'];
            // 総労働時間と深夜労働時間が逆転している場合、深夜労働時間を修正
            if( $totalWorkTime < $nightWorkHour )
            {
                $nightWorkHour = $totalWorkTime;
            }

            // 勤務時間帯を取得
            $workHours = $this->getWorkHours( $attendanceTime, $clockOutTime );

            // 時給変更帯情報を取得
            $changeHourlyWageList = $this->getChangeHourlyWage( $attendanceTime, $clockOutTime, $lrAllInfo, $shiftInfo['organization_id'] );

            // 残業時間の取得
            $overtimeList = $this->getOvertimeHours( $shiftInfo['user_id'], $lrAllInfo, $totalWorkTime, $nightWorkHour, $shiftInfo['day'], $shiftID, 't_shift', $attendanceTime, $clockOutTime );
            // 1シフトの概算を取得
            $setModOvertimeList = array();
            $addAllowanceList = array();    // 呼び出し用に定義。シフトでは使用しない
            $money = $this->getApproximateAmountMoney( $shiftInfo, $lrAllInfo, $totalWorkTime, $nightWorkHour, $overtimeList, $setModOvertimeList, 't_shift', $changeHourlyWageList, $addAllowanceList );
            $totalWorkHour = $totalWorkTime / 60;
            $money1h = round( $money / $totalWorkHour );

            // DB登録用パラメータ設定
            $parameters = array( 
                                    ':shift_id'             => $shiftID,
                                    ':break_time'           => $this->changeTimeFromMinute( $breakTime ),
                                    ':total_working_time'   => $this->changeTimeFromMinute( $totalWorkTime ),
                                    ':night_working_time'   => $this->changeTimeFromMinute( $nightWorkHour ),
                                    ':work_hours'           => $workHours, 
                                    ':overtime'             => $this->changeTimeFromMinute( $overtimeList['inOvertime'] + $overtimeList['outOvertime'] ), 
                                    ':night_overtime'       => $this->changeTimeFromMinute( $overtimeList['nightOvertime'] ), 
                                    ':travel_time'          => $this->changeTimeFromMinute( $travelTime ), 
                                    ':rough_estimate'       => $money, 
                                    ':rough_estimate_hour'  => $money1h, 
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

            $Log->trace("END   setShiftInfo");

            return $this->setShiftTime( $parameters );
        }

        /**
         * 1シフト分を勤怠レコードへ保存する
         * @param    $shiftID         勤怠ID
         * @return   SQLの実行結果
         */
        public function setAttendanceRecord( $shiftID )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setAttendanceRecord");
            
            // 勤怠情報の更新有無を確認する
            if( $this->isAttendanceRecord( $shiftID ) )
            {
                // 勤怠レコードと結んでいる場合、何も更新しない
                $Log->trace("END setAttendanceRecord");
                return "MSG_BASE_0000";
            }

            // 勤怠レコードと紐づけを行うレコードIDを検索する
            $attendanceID = $this->getAttendanceRecordID( $shiftID );
            if( $attendanceID == 0 )
            {
                // 勤怠レコードがない為、新規追加
                $ret = $this->addAttendanceNewData( $shiftID );
            }
            else
            {
                // 勤怠レコードがあるので、シフトIDのみを更新
                $ret = $this->modAttendanceData( $attendanceID, $shiftID );
            }

            $Log->trace("END   setAttendanceRecord");

            return $ret;
        }
        
        /**
         * 勤怠テーブル新規データ登録
         * @param    $shiftID       シフトID
         * @return   SQLの実行結果
         */
        private function addAttendanceNewData( $shiftID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addAttendanceNewData");

            // シフト情報を取得する
            $shiftInfo = $this->getShiftTimeInfo( $shiftID );
            // ユーザ情報を取得
            $userInfo = $this->getUserInfo( $shiftInfo['user_id'], $shiftInfo['day'] );
            // 就業規則情報を取得
            $employInfo = $this->getUserEmploymentInfo($userInfo);
            // 就業規則取得
            $lrAllInfo = $this->getLaborRegulationsAllInfo( $employInfo['labor_regulations_id'], $shiftInfo['day'] );
            // 所定労働時間を取得
            $predetTime = $this->getPredeterminedTime( $shiftInfo['user_id'], $lrAllInfo, $shiftInfo['day'], 't_shift' );
            // 所定労働日数
            $workingDay = 1;
            // 所定内労働時間
            $workingHoursTime = $predetTime['workingHoursTime'];
            // 欠勤回数
            $absenceCount = 1;

            // 1日1回ののみの勤怠情報レコードである
            if( false == $this->isAddRecord( $shiftInfo['day'], $shiftInfo['user_id'], $shiftID ) )
            {
                // 1回目の勤怠で入力済みの為、クリア
                $workingDay       = 0;
                $workingHoursTime = 0;
            }
            // 休日であるか
            if( $shiftInfo['is_holiday'] != 0 )
            {
                // 休日の為、所定時間を0に設定
                $workingDay       = 0;
                $workingHoursTime = 0;
                $absenceCount     = 0;
            }

            $sqlParamenter = "";
            $parameters = array(
                    ':user_id'                          => $shiftInfo['user_id'],
                    ':update_user_id'                   => $_SESSION["USER_ID"], 
                    ':update_organization'              => $_SESSION["ORGANIZATION_ID"], 
                    ':organization_id'                  => $shiftInfo['organization_id'],
                    ':date'                             => $shiftInfo['day'],
                    ':is_holiday'                       => $shiftInfo['is_holiday'],
                    ':shift_id'                         => $shiftID,
                    ':labor_regulations_id'             => $employInfo['labor_regulations_id'],
                    ':prescribed_working_days'          => $workingDay,
                    ':prescribed_working_hours'         => $workingHoursTime,
                    ':absence_count'                    => $absenceCount,
                    ':rough_estimate_month'             => $shiftInfo['rough_estimate'],
                );

            $sql = 'INSERT INTO t_attendance( user_id '
                 . '                        , organization_id'
                 . '                        , date'
                 . '                        , is_holiday'
                 . '                        , shift_id'
                 . '                        , labor_regulations_id'
                 . '                        , absence_count'
                 . '                        , prescribed_working_days'
                 . '                        , prescribed_working_hours'
                 . '                        , rough_estimate_month'
                 . '                        , registration_time'
                 . '                        , registration_user_id'
                 . '                        , registration_organization'
                 . '                        , update_time'
                 . '                        , update_user_id'
                 . '                        , update_organization'
                 . '                       ) VALUES ('
                 . '                         :user_id'
                 . '                       , :organization_id'
                 . '                       , :date'
                 . '                       , :is_holiday'
                 . '                       , :shift_id'
                 . '                       , :labor_regulations_id'
                 . '                       , :absence_count '
                 . '                       , :prescribed_working_days'
                 . '                       , :prescribed_working_hours'
                 . '                       , :rough_estimate_month'
                 . '                       , current_timestamp'
                 . '                       , :update_user_id'
                 . '                       , :update_organization'
                 . '                       , current_timestamp'
                 . '                       , :update_user_id'
                 . '                       , :update_organization )';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー 
                $Log->warn("MSG_ERR_3110");
                $errMsg = "勤怠テーブルの新規登録処理に失敗しました。";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3110";
            }

            $Log->trace("END addAttendanceNewData");
            return "MSG_BASE_0000";
        }

        /**
         * 勤怠テーブルデータ更新
         * @param    $attendanceID  勤怠ID
         * @param    $shiftID       シフトID
         * @return   勤怠テーブル更新結果
         */
        private function modAttendanceData( $attendanceID, $shiftID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modAttendanceData");

            $sql  = 'UPDATE t_attendance SET'
                 . '       shift_id = :shift_id'
                 . '     , update_time = current_timestamp'
                 . '     , update_user_id = :update_user_id'
                 . '     , update_organization = :update_organization '
                 . '    WHERE attendance_id = :attendance_id';

            $parameters = array( 
                                    ':attendance_id'             => $attendanceID, 
                                    ':shift_id'                  => $shiftID,
                                    ':update_user_id'            => $_SESSION["USER_ID"], 
                                    ':update_organization'       => $_SESSION["ORGANIZATION_ID"], 
                                );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー 
                $Log->warn("MSG_ERR_3116");
                $errMsg = "勤怠テーブルの更新処理に失敗しました。";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3116";
            }

            $Log->trace("END modAttendanceData");

            return "MSG_BASE_0000";
        }

        /**
         * シフトIDと紐づいている勤怠レコードがある
         * @param    $shiftID       シフトID
         * @return   勤怠レコード有：true    勤怠レコード無：false
         */
        private function isAttendanceRecord( $shiftID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START isAttendanceRecord");
            
            // 勤怠情報を更新
            $sql  = " SELECT COUNT( attendance_id ) as cnt FROM t_attendance "
                  . " WHERE shift_id = :shift_id AND is_del = 0 ";

            $parameters = array( 
                                    ':shift_id'    => $shiftID, 
                                );
            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END isAttendanceRecord");
                return false;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['cnt'] == 0 )
                {
                    $Log->trace("END isAttendanceRecord");
                    return false;
                }
            }

            $Log->trace("END isAttendanceRecord");

            return true;
        }
        
        /**
         * シフト設定の勤怠レコードIDを取得する
         * @param    $shiftID       シフトID
         * @return   勤怠レコード有：attendance_id    勤怠レコード無：0
         */
        private function getAttendanceRecordID( $shiftID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAttendanceRecordID");
            
            // シフト情報を取得する
            $shiftInfo = $this->getShiftTimeInfo( $shiftID );
            
            // 勤怠情報を更新
            $sql  = " SELECT attendance_id FROM t_attendance "
                  . " WHERE date = :date AND user_id = :user_id AND shift_id = 0 AND organization_id = :organization_id AND is_del = 0 "
                  . " ORDER BY attendance_id DESC ";

            $parameters = array( 
                                    ':date'    => $shiftInfo['day'], 
                                    ':user_id' => $shiftInfo['user_id'], 
                                    ':organization_id' => $shiftInfo['organization_id'], 
                                );
            $result = $DBA->executeSQL($sql, $parameters);
            $ret = 0;
            if( $result === false )
            {
                $Log->trace("END getAttendanceRecordID");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data['attendance_id'];
            }

            $Log->trace("END getAttendanceRecordID");

            return $ret;
        }

        /**
         * シフトの労働時間を登録する
         * @param    $parameters     更新パラメータ
         * @return   更新結果
         */
        private function setShiftTime( $parameters )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START setShiftTime");
            
            // 勤怠情報を更新
            $sql  = " UPDATE t_shift SET "
                  . "        break_time = :break_time "
                  . "      , total_working_time = :total_working_time "
                  . "      , night_working_time = :night_working_time "
                  . "      , work_hours = :work_hours "
                  . "      , overtime = :overtime "
                  . "      , night_overtime = :night_overtime "
                  . "      , travel_time = :travel_time "
                  . "      , rough_estimate = :rough_estimate "
                  . "      , rough_estimate_hour = :rough_estimate_hour "
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
                  . " WHERE shift_id = :shift_id ";

            if( !$DBA->executeSQL( $sql, $parameters, true ) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3122");
                $errMsg = "シフトID：" . $parameters[':shift_id'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3122";
            }

            $Log->trace("END setShiftTime");

            return "MSG_BASE_0000";
        }

        /**
         * シフト情報を取得する
         * @param    $shiftID          シフトID
         * @return   シフト情報
         */
        private function getShiftTimeInfo( $shiftID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getShiftTimeInfo");
            
            // シフト情報を取得
            $sql  = " SELECT attendance, taikin, organization_id, user_id, break_time, day, is_holiday, shift_id, rough_estimate FROM t_shift WHERE  shift_id = :shift_id ";

            $parameters = array( 
                                    ':shift_id'    => $shiftID, 
                                );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array(
                            'attendance_id'     =>  0,
                            'shift_id'          =>  0,
                            'rough_estimate'    =>  0,
                            'attendance'        =>  0,
                            'taikin'            =>  0,
                            'organization_id'   =>  0,
                            'user_id'           =>  0,
                            'break_time'        =>  0,
                            'day'               =>  0,
                            'date'              =>  0,
                            'is_holiday'        =>  0,
                        );
            if( $result === false )
            {
                $Log->trace("END getShiftTimeInfo");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                // シフトの出勤/退勤情報を取得
                $ret['shift_id']        = $data['shift_id'];
                $ret['rough_estimate']  = $data['rough_estimate'];
                $ret['attendance']      = $this->timeTransformation( $data['attendance'] );
                $ret['taikin']          = $this->timeTransformation( $data['taikin'] );
                $ret['organization_id'] = $data['organization_id'];
                $ret['user_id']         = $data['user_id'];
                $ret['break_time']      = $this->changeMinuteFromTime( $data['break_time'] );
                $ret['day']             = $data['day'];
                $ret['date']            = $data['day'];
                $ret['is_holiday']      = $data['is_holiday'];
            }

            $Log->trace("END getShiftTimeInfo");

            return $ret;
        }

        /**
         * シフトの複数レコード時の追加可否を取得
         * @param    $day            労働日
         * @param    $userID         ユーザID
         * @param    $shiftID        シフトID
         * @return   追加可：true    追加不可：false
         */
        private function isAddRecord( $day, $userID, $shiftID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START isAddRecord");
            
            // 初期化
            $ret = false;

            // 労働日の勤務回数分、シフトIDを取得
            $sql  = " SELECT shift_id FROM t_shift WHERE day = :day AND user_id = :user_id ORDER BY attendance ";

            $parameters = array( 
                                 ':day'     => $day,
                                 ':user_id' => $userID,
                               );
            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END isAddRecord");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['shift_id'] == $shiftID )
                {
                    $ret = true;
                }
                // 勤怠最初のレコード以外は、追加不可
                break;
            }

            $Log->trace("END isAddRecord");
            return $ret;
        }

        /**
         * シフト移動時間の取得
         * @param    $day            労働日
         * @param    $userID         ユーザID
         * @param    $shiftID        シフトID
         * @param    $lrAllInfo      就業規則情報
         * @return   移動時間(分)
         */
        private function getTravelTime( $day, $userID, $shiftID, $lrAllInfo )
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

            // 労働日の勤務回数分、シフトIDを取得
            $sql  = " SELECT shift_id, organization_id, attendance, taikin FROM t_shift "
                  . " WHERE day = :day AND user_id = :user_id ORDER BY attendance ";
            $parameters = array( 
                                 ':day'     => $day,
                                 ':user_id' => $userID,
                               );
            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END getTravelTime");
                return $ret;
            }

            $shiftList = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $shiftList, $data );
            }

            $shiftCnt = count( $shiftList );
            if( $shiftCnt <= 1 )
            {
                // シフト回数が1回以下の場合、移動無し
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
            // シフトが複数回ある場合
            for( $i = 0; $i < $shiftCnt; $i++ )
            {
                // 現在の登録対象のシフトである
                if( $shiftID == $shiftList[$i]['shift_id'] )
                {
                    $targetStore = $shiftList[$i]['organization_id'];
                    $targetAtime = $this->timeTransformation( $shiftList[$i]['attendance'] );
                    $targetTtime = $this->timeTransformation( $shiftList[$i]['taikin'] );
                    $isTarget = true;
                }
                else if( $isTarget == true )
                {
                    // 移動先の情報を設定
                    $afterStore = $shiftList[$i]['organization_id'];
                    $afterAtime = $this->timeTransformation( $shiftList[$i]['attendance'] );
                    $afterTtime = $this->timeTransformation( $shiftList[$i]['taikin'] );
                    break;
                }
                else
                {
                    // 移動元の情報を設定
                    $beforeStore = $shiftList[$i]['organization_id'];
                    $beforeAtime = $this->timeTransformation( $shiftList[$i]['attendance'] );
                    $beforeTtime = $this->timeTransformation( $shiftList[$i]['taikin'] );
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
