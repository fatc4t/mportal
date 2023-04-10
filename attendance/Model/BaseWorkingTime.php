<?php
    /**
     * @file      労働時間計算ベースモデル
     * @author    USE Y.Sakata
     * @date      2016/08/22
     * @version   1.00
     * @note      労働時間計算に必要な処理制御を行う
     */

    // BaseModel.phpを読み込む
    require_once './Model/Common/BaseModel.php';

    /**
     * 労働時間計算ベース
     * @note   労働時間計算に必要な処理制御を行う
     */
    class BaseWorkingTime extends BaseModel
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
         * 時給変更帯情報を取得する
         * @param    $attendanceTime    出勤時間
         * @param    $clockOutTime      退勤時間
         * @param    $lrAllInfo         就業規則
         * @param    $organizationID    組織ID
         * @return   時給変更時間帯リスト
         */
        protected function getChangeHourlyWage( $attendanceTime, $clockOutTime, $lrAllInfo, $organizationID )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getChangeHourlyWage");

            // 一日の開始時間を取得
            $startTimeDay = $this->getStartTimeDay( $organizationID );
            $startTimeDay = $this->changeMinuteFromTime( $startTimeDay );

            $ret = array();
            
            // 時給設定変更分の情報を取得する
            foreach( $lrAllInfo['m_hourly_wage_change'] as $hourlyWageChange )
            {
                $hourlyWage = array(
                                        'time'              =>  0,
                                        'hourly_wage'       =>  0,
                                        'hourly_wage_value' =>  0,
                                    );

                // 時給変更開始時間/終了時間を、実際の勤怠から取得
                $startTime = $this->timeTransformation( $hourlyWageChange['hourly_wage_start_time'] );
                $endTime   = $this->timeTransformation( $hourlyWageChange['hourly_wage_end_time'] );
                
                // 一日の開始時間未満の場合、24時間(60 * 24)を足す
                if( $startTime < $startTimeDay )
                {
                    $startTime = $startTime + 1440;
                }
                
                // 一日の開始時間未満の場合、24時間(60 * 24)を足す
                if( $endTime < $startTimeDay )
                {
                    $endTime = $endTime + 1440;
                }

                // 勤務時間内に時給変更開始時間がない
                if( !( $attendanceTime <= $startTime && $startTime <= $clockOutTime ) )
                {
                    // 退勤時間よりも、前に時給変更開始時間がある
                    if( $startTime <= $clockOutTime )
                    {
                        // 出勤時間を開始時間に設定
                        $startTime = $attendanceTime;
                    }
                    else
                    {
                        // 範囲対象外
                        $startTime = 0;
                    }
                }
                
                // 勤務時間内にが時給変更終了時間がある
                if( !( $attendanceTime <= $endTime && $endTime <= $clockOutTime ) )
                {
                    // 退勤時間よりも、前に時給変更終了時間がある
                    if( $endTime >= $clockOutTime )
                    {
                        // 退勤時間を終了時間に設定
                        $endTime = $clockOutTime;
                    }
                    else
                    {
                        // 範囲対象外
                        $endTime = 0;
                    }
                }
                
                // どちらも設定あり
                if( $startTime > 0 && $endTime > 0 )
                {
                    $hourlyWage['time']                 = $this->roundingTime( $lrAllInfo, ( $endTime - $startTime ) );
                    $hourlyWage['hourly_wage']          = $hourlyWageChange['hourly_wage'];
                    $hourlyWage['hourly_wage_value']    = $hourlyWageChange['hourly_wage_value'];
                }
                
                array_push( $ret, $hourlyWage );
            }
            
            $Log->trace("END getChangeHourlyWage");
            return $ret;
        }

        /**
         * 勤務時間帯を取得する
         * @param    $attendanceTime    出勤時間
         * @param    $clockOutTime      退勤時間
         * @return   勤務時間帯(最下位bitから、0時スタートで、23bitまで使用)
         */
        protected function getWorkHours( $attendanceTime, $clockOutTime )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getWorkHours");
            
            // 同一時間の場合、勤務時間帯を0とする
            if( $attendanceTime === $clockOutTime )
            {
                $Log->trace("END getWorkHours");
                return 0;
            }
            
            $ret = array();
            // 0時から、23時まで配列で、データを取得
            for( $i = 0; $i < 24; $i++ )
            {
                $ret[$i] = 0;
                $time = 60 * $i;
                if( $attendanceTime <= $time && $time <= $clockOutTime )
                {
                    // 指定時間に働いている
                    $ret[$i] = 1;
                }
            }
            
            // 24時以降もデータ再設定
            for( $i = 0; $i < 24; $i++ )
            {
                $time = 60 * 24 + ( $i * 60 );
                if( $time <= $clockOutTime )
                {
                    // 指定時間に働いている
                    $ret[$i] = 1;
                }
            }
            
            $iRet = 0;
            // ビット配列をint型へ修正
            for( $i = 23; $i >= 0 ; $i-- )
            {
                // 左へ1ビットシフト
                $iRet = $iRet << 1;
                $iRet = $iRet + $ret[$i];
            }
            
            $Log->trace("END getWorkHours");
            return $iRet;
        }

        /**
         * 残業時間を取得する
         * @param    $userID            ユーザID
         * @param    $lrAllInfo         就業規則
         * @param    $totalWorkTime     総労働時間
         * @param    $nightWorkHour     深夜労働時間
         * @param    $date              勤務日
         * @param    $keyID             勤怠ID or シフトID
         * @param    $tableName         テーブル名
         * @param    $attendanceTime    出勤時間
         * @param    $clockOutTime      退勤時間
         * @return   残業時間(所定内/法定外)
         */
        protected function getOvertimeHours( $userID, $lrAllInfo, $totalWorkTime, $nightWorkHour, $date, $keyID, $tableName, $attendanceTime, $clockOutTime )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getOvertimeHours");
            
            $ret = array(
                            'inOvertime'    =>  0,
                            'outOvertime'   =>  0,
                            'nightOvertime' =>  0,
                        );
            
            // 所定労働時間を取得
            $predeterminedTime = $this->getPredeterminedTime( $userID, $lrAllInfo, $date, $tableName );
            
            // 当日の直前までの勤務時間を取得
            $workingHoursDay = array( 'total_working_time'  => 0, );
            $this->getTodayOvertimeHours( $userID, $date, $keyID, $workingHoursDay, $tableName );
            // 直前勤務までの残業時間を取得
            $dayOverTime = $this->calcOvertime( $predeterminedTime['workingHoursTime'], 
                                        $predeterminedTime['workingOverHoursTime'],
                                        $workingHoursDay['total_working_time'], $workingHoursDay['night_working_time'], $lrAllInfo, $attendanceTime, $clockOutTime );
            
            // 勤務日すべての労働時間
            $dayTotalWorkTime = $totalWorkTime + $workingHoursDay['total_working_time'];

            // 残業時間を算出
            $ret = $this->calcOvertime( $predeterminedTime['workingHoursTime'], 
                                        $predeterminedTime['workingOverHoursTime'],
                                        $dayTotalWorkTime, $nightWorkHour, $lrAllInfo, $attendanceTime, $clockOutTime );

            // 2回目以降の勤務の場合、残業時間再計算する
            if( $workingHoursDay['total_working_time'] > 0 )
            {
                 $ret['inOvertime']  = $ret['inOvertime']  - $dayOverTime['inOvertime'];
                 $ret['outOvertime'] = $ret['outOvertime'] - $dayOverTime['outOvertime'];
            }

            $Log->trace("END getOvertimeHours");
            return $ret;
        }

        /**
         * 残業時間を計算する
         * @param    $prescribedTime    所定労働時間(分)
         * @param    $overtime          所定外労働時間(分)
         * @param    $totalWorkTime     総労働時間(分)
         * @param    $nightWorkHour     深夜労働時間(分)
         * @param    $lrAllInfo         就業規則
         * @param    $attendanceTime    出勤時間
         * @param    $clockOutTime      退勤時間
         * @return   残業時間(所定内/法定外/深夜)
         */
        protected function calcOvertime( $prescribedTime, $overtime, $totalWorkTime, $nightWorkHour, $lrAllInfo, $attendanceTime, $clockOutTime )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START calcOvertime");
            
            $ret = array(
                            'inOvertime'    =>  0,
                            'outOvertime'   =>  0,
                            'nightOvertime' =>  0,
                        );

            // 所定内外の残業時間を算出する
            if( $prescribedTime <= $totalWorkTime )
            {
                // 所定外労働時間以上
                if( $overtime <= $totalWorkTime )
                {
                    $ret['inOvertime']  = $overtime - $prescribedTime;
                    $ret['outOvertime'] = $totalWorkTime - $overtime;
                }
                else
                {
                    $ret['inOvertime'] = $totalWorkTime - $prescribedTime;
                }
            }
            
            $total = $ret['inOvertime'] + $ret['outOvertime'];
            // 深夜残業時間を算出する
            if( 0 < $total )
            {
                
                // 0時～早朝までの深夜時間に関する対応 2017/08/09 millionet oota
                // 深夜時間の範囲を設定
                $this->getNightTimeZone( $lrAllInfo, $sNightTime, $eNightTime );

                $cal = 0;

                $sNightTime2 = 0;
                $eNightTime2 = $eNightTime - (60 * 24);

                // 出勤が0時～深夜時帯終了までにあるかを判定
                if( $sNightTime2 <= $attendanceTime && $attendanceTime <= $eNightTime2 )
                {
                    // 早朝深夜労働時間あり
                    // 深夜労働時間開始時間を求める
                    $factSNightTime = $sNightTime2;
                    // 深夜時間開始時刻よりも、出勤時間が遅い
                    if( $sNightTime2 < $attendanceTime )
                    {
                        $factSNightTime = $attendanceTime;
                    }

                    // 深夜労働時間終了時間を求める
                    $factENightTime = $eNightTime2;
                    // 深夜時間終了時刻よりも、退勤時間が早い
                    if( $clockOutTime < $eNightTime2 )
                    {
                        $factENightTime = $clockOutTime;
                    }

                    // 深夜労働時間を計算する
                    $cal += $factENightTime - $factSNightTime;
                }

                // 深夜時間 - 早朝深夜の時間を深夜時間にする
                $nightWorkHour = $nightWorkHour - $cal;
                
                $ret['nightOvertime'] = $nightWorkHour;
                
                // 総残業時間よりも深夜残業時間が多い
                if( $total < $nightWorkHour )
                {
                    $ret['nightOvertime'] = $total;
                }
            }
            
            $Log->trace("END calcOvertime");
            return $ret;
        }

        /**
         * 深夜時間労働時間を取得する
         * @param    $lrAllInfo         就業規則
         * @param    $attendanceTime    出勤時間
         * @param    $clockOutTime      退勤時間
         * @return   深夜労働時間
         */
        protected function getNightWorkingHours( $lrAllInfo, $attendanceTime, $clockOutTime )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getNightWorkingHours");
            
            // 深夜時間の範囲を設定
            $this->getNightTimeZone( $lrAllInfo, $sNightTime, $eNightTime );

            $ret = 0;
            
            // 0時～早朝までの深夜時間に関する対応 2017/08/09 millionet oota
            $sNightTime2 = 0;
            $eNightTime2 = $eNightTime - (60 * 24);
            
            // 退勤がない場合は終了
            if($clockOutTime == 0){
                $Log->trace("END getNightWorkingHours");
                return 0;
            }
            
            // 出勤が0時～深夜時帯終了までにあるかを判定
            if( $sNightTime2 <= $attendanceTime && $attendanceTime <= $eNightTime2 )
            {
                // 早朝深夜労働時間あり
                // 深夜労働時間開始時間を求める
                $factSNightTime = $sNightTime2;
                // 深夜時間開始時刻よりも、出勤時間が遅い
                if( $sNightTime2 < $attendanceTime )
                {
                    $factSNightTime = $attendanceTime;
                }

                // 深夜労働時間終了時間を求める
                $factENightTime = $eNightTime2;
                // 深夜時間終了時刻よりも、退勤時間が早い
                if( $clockOutTime < $eNightTime2 )
                {
                    $factENightTime = $clockOutTime;
                }

                // 深夜労働時間を計算する
                $ret += $factENightTime - $factSNightTime;
            }
            
            // 深夜時間開始～深夜時間終了までにあるかを判定
            // ① 出勤が深夜時間開始～深夜時間終了にあるか
            // ② 退勤が深夜時間開始～深夜時間終了にあるか
            // ③ 出勤が深夜時間開始よりも早く、退勤が深夜終了よりも遅いか
            // → 出勤も退勤も通常時間の場合は計算不要
            if( $eNightTime2 <= $attendanceTime && $attendanceTime <= $sNightTime &&
                    $eNightTime2 <= $clockOutTime && $clockOutTime <= $sNightTime ){

                // 通常時間勤務の場合は終了
                $Log->trace("END getNightWorkingHours");
                return 0;
            }else if($sNightTime <= $clockOutTime){
                // 深夜労働時間あり
                // 深夜労働時間開始時間を求める
                $factSNightTime = $sNightTime;
                // 深夜時間開始時刻よりも、出勤時間が遅い
                if( $sNightTime < $attendanceTime )
                {
                    $factSNightTime = $attendanceTime;
                }

                // 深夜労働時間終了時間を求める
                $factENightTime = $eNightTime;
                // 深夜時間終了時刻よりも、退勤時間が早い
                if( $clockOutTime < $eNightTime )
                {
                    $factENightTime = $clockOutTime;
                }

                // 深夜労働時間を計算する
                $ret += $factENightTime - $factSNightTime;
            }
            
            $Log->trace("END getNightWorkingHours");
            return $ret;
        }

        /**
         * 時間を分変換し取得する(36時間対応)
         * @param    $dateTime         対象時間
         * @param    $referenceDate    基準日
         * @return   なし
         */
        protected function timeTransformation( $dateTime, $referenceDate = '2016-04-01' )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START timeTransformation");
            
            if( empty( $dateTime ) )
            {
                $Log->trace("END timeTransformation");
                return 0;
            }
            
            // 深夜時間の範囲を設定
            $date =substr( $dateTime, 0, 10);
            
            $upCntTime = 0;
            if( $date != $referenceDate )
            {
                $upCntTime = 60 * 24; // 24時間(分)
            }
            $time = $this->changeMinuteFromTime( substr( $dateTime, 11, 5) ) + $upCntTime;

            $Log->trace("END timeTransformation");
            
            return $time;
        }

        /**
         * 1勤務の休憩時間を取得する
         * @param    $attendanceInfo    勤怠情報
         * @param    $lrAllInfo         就業規則
         * @param    $bindingHour       拘束時間
         * @return   休憩時間
         */
        protected function getBreakTime( $attendanceInfo, $lrAllInfo, $inBindingHour )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getBreakTime");

            $retBreakTime = array();
            $retBreakTime['dayTimeBreakTime'] = 0;
            $retBreakTime['nightBreakTime']   = 0;

            // 休憩時間の取得方法(手動以外)
            if( $lrAllInfo['m_work_rules_time'][0]['break_time_acquisition'] > 1 )
            {
                // 手動と自動併用も含めて、休憩時間を計算する
                // 設定時間分を付与する
                if( $lrAllInfo['m_work_rules_time'][0]['automatic_break_time_acquisition'] == 1 )
                {
                    $lrBreakTime = 0;

                    // 拘束時間が、休憩付与時間を超えているか(設定順に比べて、あとがちになる)
                    foreach( $lrAllInfo['m_work_rules_break'] as $workRulesBreak )
                    {
                        $bindingHour = $this->changeMinuteFromTime( $workRulesBreak['binding_hour'] );

                        if( $bindingHour <= $inBindingHour )
                        {
                            $lrBreakTime = $this->changeMinuteFromTime( $workRulesBreak['break_time'] );
                        }
                    }

                    // 休憩付与する必要があるか
                    if( $lrBreakTime > 0 )
                    {
                        $totalBreakTime = 0;
                        // 出勤からの経過時間ごとに休憩時間を付与する
                        foreach( $lrAllInfo['m_work_rules_shift_break'] as $workShiftBreak )
                        {
                            // 休憩時間帯を計算
                            $sTime = ( strtotime( $attendanceInfo['attendance_time'] ) / 60 ) + $this->changeMinuteFromTime( $workShiftBreak['elapsed_time'] );
                            $sTime = date("Y/m/d H:i:s", ($sTime*60));
                            
                            // 休憩時間を設定(昼間/夜間)を分けて算出
                            $breakTime = $this->changeMinuteFromTime( $workShiftBreak['break_time'] );
                            $totalBreakTime += $breakTime;
                            
                            // 付与休憩時間を超えていないか
                            if( $lrBreakTime >= $totalBreakTime )
                            {
                                $retBreakTime = $this->getBreakTimeDivision( $lrAllInfo, $sTime, $breakTime, $retBreakTime );
                            }
                        }
                    }
                }
                // 設定時間の範囲内で休憩を付与する
                else if( $lrAllInfo['m_work_rules_time'][0]['automatic_break_time_acquisition'] == 2 )
                {
                    foreach( $lrAllInfo['m_break_time_zone'] as $breakTimeZone )
                    {
                        //休憩開始時間（時刻のみ）
                        $startTime = substr( $breakTimeZone['hourly_wage_start_time'], 11, 5);
                        //休憩開始時間を分に修正
                        $mstartTime = $this->changeMinuteFromTime($startTime);
                        //休憩終了時間（分に修正）
                        $endTime = $this->changeMinuteFromTime( substr( $breakTimeZone['hourly_wage_end_time'], 11, 5) );
                        //出勤時間（分に修正）
                        $attendanceTime = $this->changeMinuteFromTime( substr( $attendanceInfo['attendance_time'], 11, 5) );
                        //退勤時間(分に修正)
                        $clockOutTime = $this->changeMinuteFromTime( substr( $attendanceInfo['clock_out_time'], 11, 5) );

                        //組織の開始時間を取得し、分に修正
                        $startTimeDay = $this->getStartTimeDay($attendanceInfo['organization_id']);
                        $startTimeDay = $this->changeMinuteFromTime( $startTimeDay );
                       
                        // 出勤時間が組織の開始時間よりも小さい場合、前日分の勤怠として24時間(分)を足す
                        if( $attendanceTime < $startTimeDay )
                        {
                            $attendanceTime = $attendanceTime + 60 * 24;
                        }

                        // 退勤時間が組織の開始時間よりも小さい場合、前日分の勤怠として24時間(分)を足す
                        if( $clockOutTime < $startTimeDay )
                        {
                            $clockOutTime = $clockOutTime + 60 * 24;
                        }
                        
                        // 休憩開始時間が組織の開始時間よりも小さい場合、前日分の勤怠として24時間(分)を足す
                        if( $mstartTime < $startTimeDay )
                        {
                            $mstartTime = $mstartTime + 60 * 24;
                        }
                        
                        // 休憩終了時間が組織の開始時間よりも小さい場合、前日分の勤怠として24時間(分)を足す
                        if( $endTime < $startTimeDay )
                        {
                            $endTime = $endTime + 60 * 24;
                        }

                        //休憩開始時間の取得
                        $workday = substr_replace($attendanceInfo['attendance_time'], "00:00:00", 11,8);
                        $sTime = ( strtotime( $workday ) / 60 ) + $this->changeMinuteFromTime( $startTime);
                        $sTime = date("Y/m/d H:i:s", ($sTime*60));

                        //休憩時間の取得
                        $start = strtotime($breakTimeZone['hourly_wage_start_time']);
                        $end = strtotime($breakTimeZone['hourly_wage_end_time']);
                        $differences = $end - $start;
                        $breakTime = gmdate("H:i:s", $differences) ;
                        $breakTime = $this->changeMinuteFromTime( $breakTime );
                   
                        //通常通り出勤した場合
                        if($attendanceTime < $mstartTime && $endTime < $clockOutTime)
                        {
                            // 休憩時間を設定(昼間/夜間)を分けて算出
                            $retBreakTime = $this->getBreakTimeDivision( $lrAllInfo, $sTime, $breakTime, $retBreakTime );
                        }
                        //休憩時間の途中で出勤した場合
                        elseif($mstartTime < $attendanceTime && $attendanceTime < $endTime && $endTime <$clockOutTime )
                        {
                            //休憩時間は休憩終了時間から出勤時間を引いた差
                            $breakTime = $endTime - $attendanceTime;
                            
                            //休憩開始時間は出勤時間
                            $sTime = $attendanceInfo['attendance_time'];

                            // 休憩時間を設定(昼間/夜間)を分けて算出
                            $retBreakTime = $this->getBreakTimeDivision( $lrAllInfo, $sTime, $breakTime, $retBreakTime );
                        }                    
                        //休憩時間の途中で退勤した場合
                        elseif($attendanceTime < $mstartTime && $mstartTime < $clockOutTime && $clockOutTime <$endTime )
                        {
                            //休憩時間は退勤時間から休憩開始時間を引いた差
                            $breakTime = $clockOutTime - $mstartTime;
                            
                            // 休憩時間を設定(昼間/夜間)を分けて算出
                            $retBreakTime = $this->getBreakTimeDivision( $lrAllInfo, $sTime, $breakTime, $retBreakTime );
                        }
                    }
                }
            }
            
            // 手動用
            $manualBreakTime = array();
            $manualBreakTime['dayTimeBreakTime'] = 0;
            $manualBreakTime['nightBreakTime']   = 0;
            // 休憩時間の取得方法が手動、もしくは手動と自動併用の場合
            if($lrAllInfo['m_work_rules_time'][0]['break_time_acquisition'] == 1 || $lrAllInfo['m_work_rules_time'][0]['break_time_acquisition'] == 3)
            {
                //休憩時間1の休憩時間を設定(昼間/夜間)を分けて算出
                $manualBreakTime = $this->getBreakTimeManual($attendanceInfo['s_break_time_1'], $attendanceInfo['e_break_time_1'], $lrAllInfo, $manualBreakTime);
                //休憩時間2の休憩時間を設定(昼間/夜間)を分けて算出
                $manualBreakTime = $this->getBreakTimeManual($attendanceInfo['s_break_time_2'], $attendanceInfo['e_break_time_2'], $lrAllInfo, $manualBreakTime);
                //休憩時間3の休憩時間を設定(昼間/夜間)を分けて算出
                $manualBreakTime = $this->getBreakTimeManual($attendanceInfo['s_break_time_3'], $attendanceInfo['e_break_time_3'], $lrAllInfo, $manualBreakTime);
                
                // 手動設定時は、計算結果を返す
                if( $lrAllInfo['m_work_rules_time'][0]['break_time_acquisition'] == 1 )
                {
                    return $manualBreakTime;
                }
            }
            
            //手動で休憩時間の設定した場合、手動の休憩時間を返却する
            if( 0 != $manualBreakTime['dayTimeBreakTime'] || 0 != $manualBreakTime['nightBreakTime']  )
            {
                $Log->trace("END getBreakTime");
                return $manualBreakTime;
            }
            
            $Log->trace("END getBreakTime");
            return $retBreakTime;
        }

        /**
         * 深夜時間帯を取得する
         * @param    $lrAllInfo         就業規則
         * @param    &$sNightTime       深夜時間開始時刻
         * @param    &$eNightTime       深夜時間終了時刻
         * @return   なし
         */
        protected function getNightTimeZone( $lrAllInfo, &$sNightTime, &$eNightTime )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getNightTimeZone");
            
            // 深夜時間の範囲を設定
            $sNightTime = $this->changeMinuteFromTime( substr( $lrAllInfo['m_work_rules_time'][0]['late_at_night_start'], 11, 5) );
            $upCntTime = 0;

            if( "2" == substr( $lrAllInfo['m_work_rules_time'][0]['late_at_night_end'], 9, 1) )
            {
                $upCntTime = 60 * 24; // 24時間(分)

            }
            $eNightTime = $this->changeMinuteFromTime( substr( $lrAllInfo['m_work_rules_time'][0]['late_at_night_end'], 11, 5) ) + $upCntTime;
            
            $Log->trace("END getNightTimeZone");
        }

        /**
         * 累積休憩時間を取得する
         * @param    $lrAllInfo         就業規則
         * @param    $sBreakTime        休憩開始時間
         * @param    $breakTime         休憩付与時間
         * @param    $retBreakTime      累積休憩時間
         * @return   累積休憩時間
         */
        protected function getBreakTimeDivision( $lrAllInfo, $sBreakTime, $breakTime, $retBreakTime )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getBreakTimeDivision");

            // 深夜時間の範囲を設定
            $this->getNightTimeZone( $lrAllInfo, $sNightTime, $eNightTime );

            // 休憩時間を分に修正
            $sBreakTime = $this->changeMinuteFromTime( substr( $sBreakTime, 11, 5) );

            // 休憩終了時間
            $eBreakTime = $sBreakTime + $breakTime;

            // 休憩終了時間が深夜開始時刻よりも小さい場合、24時間(分)を足す
            if( $eBreakTime < $sNightTime )
            {
                $eBreakTime = $eBreakTime + 60 * 24;
            }

            // 休憩開始時間が深夜開始時刻よりも小さい場合、24時間(分)を足す
            if( $sBreakTime < $sNightTime )
            {
                $sBreakTime = $sBreakTime + 60 * 24;
            }

            // 休憩時間が昼間か夜間かを判定
            // 深夜時間帯の設定が無効の場合
            if( $sNightTime == $eNightTime )
            {
                // 全て昼に合算
                $retBreakTime['dayTimeBreakTime'] += $breakTime;
            }
            // 休憩時間がすべて昼間時間帯の場合
            elseif($eNightTime <= $sBreakTime && $eNightTime <= $eBreakTime)
            {
                $retBreakTime['dayTimeBreakTime'] += $breakTime;
            }
            // 休憩開始が昼間時間帯、休憩終了が深夜時間帯の場合
            elseif($eNightTime < $sBreakTime && $eBreakTime < $eNightTime)
            {
                $retBreakTime['nightBreakTime']   += $eBreakTime - $sNightTime;
                $retBreakTime['dayTimeBreakTime'] += $breakTime - $retBreakTime['nightBreakTime'];
            }
            // 休憩開始が深夜時間帯、休憩終了が昼間時間帯の場合
            elseif($sBreakTime < $eNightTime && $eNightTime < $eBreakTime )
            {
                $retBreakTime['dayTimeBreakTime'] += $eBreakTime - $eNightTime;
                $retBreakTime['nightBreakTime']   += $eNightTime - $sBreakTime;
            }
            // 休憩時間がすべて深夜時間帯の場合
            elseif($sNightTime <= $sBreakTime && $eBreakTime <= $eNightTime )
            {
                $retBreakTime['nightBreakTime']   += $breakTime;
            }
            
            $Log->trace("END getBreakTimeDivision");
            return $retBreakTime;
        }

        /**
         * 組織の開始時間を取得する
         * @param    $attendanceID     勤怠ID
         * @return   勤怠情報
         */
        protected function getStartTimeDay( $organizationID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getStartTimeDay");
            
            // 従業員Noから、ユーザIDを取得
            $sql  = " SELECT start_time_day"
                  . " FROM v_organization "
                  . " WHERE organization_id = :organization_id AND eff_code = '適用中' ";

            $parameters = array( ':organization_id'  => $organizationID, );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getStartTimeDay");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data['start_time_day'];
            }

            $Log->trace("END getStartTimeDay");
            return $ret;
        }

        /**
         * 休憩時間の取得方法が手動の際の休憩時間の取得
         * @param    $attendanceID     勤怠ID
         * @return   勤怠情報
         */
        protected function getBreakTimeManual( $sbreakTime, $ebreakTime, $lrAllInfo, $ret)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getBreakTimeManual");
            
            // 休憩時間の初期化
            $breakTime = 0;

            // 休憩開始/終了ともに値が入っている場合、計算する
            if( !empty( $sbreakTime ) && !empty( $ebreakTime ) )
            {
                //休憩時間の取得
                $start = strtotime($sbreakTime);
                $end = strtotime($ebreakTime);
                
                // 休憩開始と終了打刻が入れ替わっていた場合、計算は差し替える
                if( $end < $start )
                {
                    $temp = $start;
                    $start = $end;
                    $end = $temp;
                }
                
                $differences = $end - $start;
                $breakTime = gmdate("H:i:s", $differences) ;
                $breakTime = $this->changeMinuteFromTime( $breakTime );
            }

            //休憩時間を設定(昼間/夜間)を分けて算出
            $ret = $this->getBreakTimeDivision( $lrAllInfo, $sbreakTime, $breakTime, $ret );

            $Log->trace("END getBreakTimeManual");
            return $ret;
        }

        /**
         * シフト情報の所定時間を取得する
         * @param    $userID        ユーザID
         * @param    $date          勤務日
         * @return   シフトの所定時間
         */
        protected function getShiftPredeterminedTime( $userID, $date )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getShiftPredeterminedTime");
            
            $ret = 0;   // 初期値0時間を設定
            // シフトの設定日を取得
            $sql  = " SELECT total_working_time FROM t_shift WHERE day = :date AND user_id = :user_id ";
            $parameters = array( 
                                    ':user_id' => $userID, 
                                    ':date'    => $date, 
                                );
            $result = $DBA->executeSQL($sql, $parameters);

            if( $result === false )
            {
                $Log->trace("END getShiftPredeterminedTime");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                // シフトの総労働時間数を合算
                $ret = $ret + $this->changeMinuteFromTime( $data['total_working_time'] );
            }

            $Log->trace("END getShiftPredeterminedTime");

            return $ret;
        }

        /**
         * 所定時間を取得する
         * @param    $userID            ユーザID
         * @param    $lrAllInfo         就業規則
         * @param    $date              勤務日
         * @param    $tableName         テーブル名
         * @param    &$monthDay         月初め
         * @return   所定労働時間(所定内/法定外)
         */
        public function getPrescribedOvertimeHours( $userID, $lrAllInfo, $date, $tableName, &$monthDay )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getPrescribedOvertimeHours");
            
            $ret = array(
                            'workingHoursTime'       =>  0,
                            'workingOverHoursTime'   =>  0,
                        );

            // 日の場合
            if( $lrAllInfo['m_work_rules_allowance'][0]['overtime_setting'] == 1 )
            {
                // 日の場合
                $ret['workingHoursTime']     = $lrAllInfo['m_overtime'][0]['regular_working_hours_time'];
                $ret['workingOverHoursTime'] = $lrAllInfo['m_overtime'][0]['overtime_reference_time'];
            }
            // 週の場合
            else if( $lrAllInfo['m_work_rules_allowance'][0]['overtime_setting'] == 2 )
            {
                // 週の場合
                $ret['workingHoursTime']     = $lrAllInfo['m_overtime'][0]['regular_working_hours_time'];
                $ret['workingOverHoursTime'] = $lrAllInfo['m_overtime'][0]['overtime_reference_time'];
            }
            // 日と週の場合
            else if( $lrAllInfo['m_work_rules_allowance'][0]['overtime_setting'] == 4 )
            {
                // 日と週の所定労働時間を算出する「'm_work_rules_time' 'balance_payments'」
                $ret = $this->getPredeterminedTimeOfWeek( $userID, $date, $lrAllInfo['m_work_rules_time'][0]['balance_payments'], $lrAllInfo );
            }
            // 曜日の場合
            else if( $lrAllInfo['m_work_rules_allowance'][0]['overtime_setting'] == 5 )
            {
                // 祝日であるか
                if( $this->getPublicHolidayName( $date ) != "" )
                {
                    // 祝日番号を取得
                    $weekNo = 7;
                }
                else
                {
                    // 曜日番号を取得
                    $datetime = new DateTime($date);
                    $weekNo = (int)$datetime->format('w') - 1;
                    if( $weekNo == -1 )
                    {
                        // 日曜日の場合、番号を修正
                        $weekNo = 6;
                    }
                }

                $ret['workingHoursTime']     = $lrAllInfo['m_overtime'][$weekNo]['regular_working_hours_time'];
                $ret['workingOverHoursTime'] = $lrAllInfo['m_overtime'][$weekNo]['overtime_reference_time'];
            }
            // 月の総日数別の場合
            else if( $lrAllInfo['m_work_rules_allowance'][0]['overtime_setting'] == 6 )
            {
                // 月の締め情報を取得
                $monthTightening = $lrAllInfo['m_work_rules_time'][0]['month_tightening'];
                $monthDayList = $this->getMonthDayList( $date, $monthTightening );
                $monthDays = count( $monthDayList );

                $index = 0;
                // 月の総日数から、所定時間を設定
                if( $monthDays == 31 )
                {
                    $index = 3;
                }
                else if( $monthDays == 30 )
                {
                    $index = 2;
                }
                else if( $monthDays == 29 )
                {
                    $index = 1;
                }
                $monthDay = $monthDayList[0];
                $ret['workingHoursTime']     = $lrAllInfo['m_overtime'][$index]['regular_working_hours_time'];
                $ret['workingOverHoursTime'] = $lrAllInfo['m_overtime'][$index]['overtime_reference_time'];
            }
            // 各月別又は、年の場合
            else if( $lrAllInfo['m_work_rules_allowance'][0]['overtime_setting'] == 7 || $lrAllInfo['m_work_rules_allowance'][0]['overtime_setting'] == 3 )
            {
                // 月の締め情報を取得
                $monthTightening = $lrAllInfo['m_work_rules_time'][0]['month_tightening'];
                $monthDayList = $this->getMonthDayList( $date, $monthTightening );

                // 月の開始日を取得
                $monthDay = $monthDayList[0];
                $monthStartDay = $monthDayList[0];
                $monthStartDayList = explode( "-", $monthStartDay );

                // INDEXは、月-1の値を使用する
                $index = intval($monthStartDayList[1]) - 1;

                // 月の締めが月末以外
                if( $monthTightening != 31 && intval($monthStartDayList[2]) < 16 )
                {
                    // 15日未満の場合、前月に設定
                    $index -= 1;
                }

                // indexが-1(1月の前月設定時)の場合、11(12月)を設定
                if( $index == -1 )
                {
                    $index = 11;
                }

                $ret['workingHoursTime']     = $lrAllInfo['m_overtime'][$index]['regular_working_hours_time'];
                $ret['workingOverHoursTime'] = $lrAllInfo['m_overtime'][$index]['overtime_reference_time'];
            }
            // シフトの場合
            else if( $lrAllInfo['m_work_rules_allowance'][0]['overtime_setting'] == 8 )
            {
                // シフト時間を使用する
                $shiftTime = $this->getShiftPredeterminedTime( $userID, $date );
                $ret['workingHoursTime']     = $shiftTime;
                $ret['workingOverHoursTime'] = $shiftTime;
            }

            $Log->trace("END getPrescribedOvertimeHours");
            return $ret;
        }

        /**
         * 直近の勤怠までの所定時間の残りを取得する
         * @param    $userID            ユーザID
         * @param    $lrAllInfo         就業規則
         * @param    $date              勤務日
         * @param    $tableName         テーブル名
         * @return   所定労働時間(所定内/法定外)
         */
        public function getPredeterminedTime( $userID, $lrAllInfo, $date, $tableName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getPredeterminedTime");
            
            $monthDay = 0;
            $ret = $this->getPrescribedOvertimeHours( $userID, $lrAllInfo, $date, $tableName, $monthDay );

            // 範囲(週/月/年)の場合、該当期間の労働時間を、所定労働時間から引く
            $setOverTime = $lrAllInfo['m_work_rules_allowance'][0]['overtime_setting'];
            if( $setOverTime != 1 && $setOverTime != 5 && $setOverTime != 8 )
            {
                $time = 0;
                // 今日以前の労働時間を算出
                if( $setOverTime == 2 || $setOverTime == 4 )
                {
                    // 週の場合
                    // 指定日から、開始日までの日付リストを作成
                    $dateList = $this->getDateList( $date, $lrAllInfo['m_work_rules_time'][0]['balance_payments'] );
                    // 指定範囲内の労働時間を取得する
                    $cnt = count( $dateList ) - 1;
                    $time = $this->getWorkingHoursSpecifiedRange( $userID, $dateList[$cnt], $dateList[0], $tableName );
                }
                else if( $setOverTime == 6 || $setOverTime == 7 || $setOverTime == 3  )
                {
                    // 月又は年の場合
                    $time = 0;
                    // 労働日が集計の開始日でない
                    if( $date != $monthDay )
                    {
                        // 前日までの労働時間を計算
                        $time = $this->getWorkingHoursSpecifiedRange( $userID, $monthDay, $date, $tableName );
                    }
                }

                // 今日以前の労働時間を所定労働時間から引く
                if( $ret['workingHoursTime'] <= $time )
                {
                    $ret['workingHoursTime']     = 0;
                    $ret['workingOverHoursTime'] = 0;
                }
                else
                {
                    $ret['workingHoursTime']     -= $time;
                    $ret['workingOverHoursTime'] -= $time;
                }
            }

            $Log->trace("END getPredeterminedTime");
            return $ret;
        }

        /**
         * 1勤務の概算を取得する
         * @param    $attendanceInfo       勤怠情報
         * @param    $lrAllInfo            就業規則
         * @param    $totalWorkTime        総労働時間
         * @param    $nightWorkHour        深夜労働時間
         * @param    $overtimeHours        残業時間
         * @param    &$setModOvertimeList  DB登録用残業時間リスト(返却用)
         * @param    $tableName            DBテーブル名
         * @param    $changeHourlyWageList 時給変更リスト
         * @param    &$addAllowanceList    手当割り当てリスト
         * @return   残業時間(所定内/法定外)
         */
        protected function getApproximateAmountMoney( $attendanceInfo, $lrAllInfo, $totalWorkTime, $nightWorkHour, $overtimeHours, &$setModOvertimeList, $tableName, $changeHourlyWageList, &$addAllowanceList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getApproximateAmountMoney");

            // 初期化
            $retMoney = 0;
            $setModOvertimeList = array(
                            'statutory_overtime_hours'                =>  0,
                            'nonstatutory_overtime_hours'             =>  0,
                            'nonstatutory_overtime_hours_45h'         =>  0,
                            'nonstatutory_overtime_hours_60h'         =>  0,
                            'statutory_overtime_hours_no_pub'         =>  0,
                            'nonstatutory_overtime_hours_no_pub'      =>  0,
                            'nonstatutory_overtime_hours_45h_no_pub'  =>  0,
                            'nonstatutory_overtime_hours_60h_no_pub'  =>  0,
                            'overtime_hours_no_considered'            =>  0,
                            'overtime_hours_no_considered_no_pub'     =>  0,
                        );

            // 所定労働時間を取得
            $predeterminedTime = $this->getPredeterminedTime( $attendanceInfo['user_id'], $lrAllInfo, $attendanceInfo['date'], $tableName );

            // ユーザの給与情報を取得する
            $salaryInfo = $this->getSalaryInfo( $attendanceInfo['user_id'] );
            // ユーザの給与情報の取得ができたか？
            if( $salaryInfo['user_detail_id'] == 0 )
            {
                // ユーザの給与情報取得に失敗
                $Log->trace("END getApproximateAmountMoney");
                return $retMoney;
            }

            // 公休日判定
            $isPublic = false;
            if( $attendanceInfo['is_holiday'] == SystemParameters::$PUBLIC_HOLIDAY )
            {
                $isPublic = true;
            }

            $keyID = $attendanceInfo['attendance_id'];
            if( $keyID == 0 )
            {
                $keyID = $attendanceInfo['shift_id'];
            }

            // 累積法定外残業時間を取得
            $currentOvertimeList = $this->getAccumulatedOvertimeHours( $attendanceInfo['user_id'], $lrAllInfo, $attendanceInfo['date'], $tableName );
            // 当日の直前勤務までの法定外残業時間を取得
            $this->getTodayOvertimeHours( $attendanceInfo['user_id'], $attendanceInfo['date'], $keyID, $currentOvertimeList, $tableName );
            // 当日の直前までの勤務時間を取得
            $workingHoursDay = array( 'total_working_time'  => 0, );
            $this->getTodayOvertimeHours( $attendanceInfo['user_id'], $attendanceInfo['date'], $keyID, $workingHoursDay, $tableName );

            $calcPredeterminedTime = $predeterminedTime['workingHoursTime'];
            $calcTotalWorkTime = $totalWorkTime;
            // 2回目以降の勤務は、所定労働を前回の労働時間から引く
            if( $workingHoursDay['total_working_time'] > 0 )
            {
                $calcPredeterminedTime = $predeterminedTime['workingHoursTime'] - $workingHoursDay['total_working_time'];
                // 0以下の場合は、0を設定
                if( $calcPredeterminedTime < 0 )
                {
                    $calcPredeterminedTime = 0;
                }
                $calcTotalWorkTime = $totalWorkTime - $overtimeHours['inOvertime'] - $overtimeHours['outOvertime'];
            }

            // 試用期間の判定
            $trialPeriod = $this->isTrialPeriod( $salaryInfo, $totalWorkTime, $workingHoursDay['total_working_time'], $attendanceInfo['user_id'], $attendanceInfo['date'] );
            // 日の基本給を取得
            $retMoney = $this->getBaseSalaryDay( $calcPredeterminedTime, $salaryInfo, $calcTotalWorkTime, $trialPeriod, $workingHoursDay['work_number_times'], $attendanceInfo['date'], $attendanceInfo['user_id'], $lrAllInfo );

            // 日の基本時給を取得 millionet oota 2017/11/17 追加
            if($salaryInfo['hourly_wage'] == 0){
                // 時給が設定されていなかったら
                $salaryInfo['hourly_wage'] = $this->getBaseSalaryDay( $calcPredeterminedTime, $salaryInfo, $calcTotalWorkTime, $trialPeriod, $workingHoursDay['work_number_times'], $attendanceInfo['date'], $attendanceInfo['user_id'], $lrAllInfo )
                        / ($lrAllInfo['m_overtime'][0]['regular_working_hours_time'] / 60);
            }

            // 深夜残業代のベース
            $nightOverTimeM = $salaryInfo['hourly_wage'];
            // 残業代を計算
            if( $trialPeriod['result'] != 0 )
            {
                // 試用期間の場合、試用期間分を計算する
                if( $trialPeriod['result'] == 1 && $salaryInfo['trial_period_write_down_criteria'] == 1 )
                {
                    $nightOverTimeM = $salaryInfo['trial_period_wages_value'];  // 試用期間中は、試用期間の金額で対応する
                    // 時給
                    $retMoney += $this->getOvertimeHoursRegistration( $lrAllInfo, $currentOvertimeList, $overtimeHours, $isPublic, $salaryInfo['trial_period_wages_value'], $setModOvertimeList );
                }
                else if( $trialPeriod['result'] == 2 && $salaryInfo['trial_period_write_down_criteria'] == 1 )
                {
                    $nightOverTimeM = $salaryInfo['trial_period_wages_value'];  // 試用期間中は、試用期間の金額で対応する
                    // 勤務中に試用期間が解除（一旦は、正規の金額で計算）
                    $retMoney += $this->getOvertimeHoursRegistration( $lrAllInfo, $currentOvertimeList, $overtimeHours, $isPublic, $salaryInfo['hourly_wage'], $setModOvertimeList );

                    // 試用期間中の残業時間
                    $trialOverTime = $trialPeriod['remaining'] - $calcPredeterminedTime;
                    if( $trialOverTime > 0 )
                    {
                        // 試用期間分の残業代を算出
                        $diffHourlyWage = $salaryInfo['hourly_wage'] - $salaryInfo['trial_period_wages_value'];
                        // 残業時間がある場合のみ、計算
                        $trialMoney = $diffHourlyWage * ( $trialOverTime / 60 );
                        $retMoney -= $trialMoney;
                    }
                }
                else
                {
                    if( $salaryInfo['trial_period_write_down_criteria'] == 2 )
                    {
                        // 日給
                        $nightOverTimeM = $salaryInfo['trial_period_wages_value'];  // 試用期間中は、試用期間の金額で対応する
                    }
                    else if( $salaryInfo['trial_period_write_down_criteria'] == 3 )
                    {
                        // 月給
                        $nightOverTimeM = $this->getBaseSalary( $salaryInfo['trial_period_wages_value'], $lrAllInfo, $attendanceInfo['date'], $attendanceInfo['user_id'] );
                    }
                    else if( $salaryInfo['trial_period_write_down_criteria'] == 4 )
                    {
                        // 年俸
                        $wagesValue = $salaryInfo['trial_period_wages_value'] / 12;
                        $nightOverTimeM = $this->getBaseSalary( $wagesValue, $lrAllInfo, $attendanceInfo['date'], $attendanceInfo['user_id'] );
                    }

                    $nightOverTimeM = round ( $nightOverTimeM / 8 );  // 試用期間の残業時間は、日の基本給を8Hで割って算出する
                    // 時給
                    $retMoney += $this->getOvertimeHoursRegistration( $lrAllInfo, $currentOvertimeList, $overtimeHours, $isPublic, $nightOverTimeM, $setModOvertimeList );
                }
            }
            else
            {
                // 正規の計算
                $retMoney += $this->getOvertimeHoursRegistration( $lrAllInfo, $currentOvertimeList, $overtimeHours, $isPublic, $salaryInfo['hourly_wage'], $setModOvertimeList );
            }

            // 時給設定変更の加算
            foreach( $changeHourlyWageList as $changeHourlyWage )
            {
                // 時給変更あり
                if( $changeHourlyWage['time'] > 0 )
                {
                    // 時給変更分を、概算の値から、引く( 基本時給 × 時給変更時間 )
                    $retMoney  = $retMoney - ( $salaryInfo['hourly_wage'] * ( $changeHourlyWage['time'] / 60 ) );
                    $retMoney += $this->calcOvertimePayment( $changeHourlyWage['hourly_wage'],
                                                             $changeHourlyWage['hourly_wage_value'],
                                                             $salaryInfo['hourly_wage'], $changeHourlyWage['time'] );
                }
            }

            // 法定休日である
            if( $attendanceInfo['is_holiday'] == SystemParameters::$STATUTORY_HOLIDAY )
            {
                $retMoney = $this->calcOvertimePayment( $lrAllInfo['m_work_rules_allowance'][0]['legal_holiday_allowance'],
                                                        $lrAllInfo['m_work_rules_allowance'][0]['legal_holiday_allowance_value'],
                                                        $salaryInfo['hourly_wage'], $totalWorkTime );
                
            }
            // 公休日である
            else if( $attendanceInfo['is_holiday'] == SystemParameters::$PUBLIC_HOLIDAY )
            {
                $retMoney = $this->calcOvertimePayment( $lrAllInfo['m_work_rules_allowance'][0]['prescribed_holiday_allowance'],
                                                        $lrAllInfo['m_work_rules_allowance'][0]['prescribed_holiday_allowance_value'],
                                                        $salaryInfo['hourly_wage'], $totalWorkTime );
            }

            // 深夜割増の加算
            $retMoney += $this->calcOvertimePayment( $lrAllInfo['m_work_rules_allowance'][0]['late_at_night_out_overtime'],
                                                     $lrAllInfo['m_work_rules_allowance'][0]['late_at_night_out_overtime_value'],
                                                     $nightOverTimeM, $nightWorkHour );

            $addAllowanceList = array(); // 手当支給リスト
            // 手当の加算
            $retMoney += $this->calcAllowanceAmount( $attendanceInfo['user_id'], $attendanceInfo['date'], $tableName, $totalWorkTime, $currentOvertimeList['total_working_time'], 
                                                     $workingHoursDay['total_working_time'], $currentOvertimeList['working_days'], $workingHoursDay['work_number_times'],
                                                     $attendanceInfo['attendance_id'], $salaryInfo['organization_id'], $addAllowanceList );

            $Log->trace("END getApproximateAmountMoney");

            // 四捨五入を行う
            return round( $retMoney );
        }

        /**
         * 日の労働時間丸め処理
         * @param    $employInfo        就業規則情報
         * @param    $embossingTime     時間（分）
         * @return   丸めた時間(分)
         */
        protected function roundingTime( $employInfo, $embossingTime )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START roundingTime");

            // 0分の場合、何もしない
            if( $embossingTime == 0 )
            {
                $Log->trace("END roundingTime");
                return 0;
            }

            $ret = $embossingTime;
            // 打刻丸め時間
            $roundingTime = $employInfo['m_work_rules_time'][0]['total_working_day_rounding_time'];

            // 丸め時刻と一致した場合、処理を行わない
            if( ( $embossingTime % $roundingTime ) == 0 )
            {
                $Log->trace("END roundingTime");
                return $ret;
            }

            // 打刻の丸め処理
            if( $employInfo['m_work_rules_time'][0]['total_working_day_rounding'] == 1 )
            {
                // 切り上げ
                $ret = ceil( $embossingTime / $roundingTime ) * $roundingTime;
            }
            else
            {
                // 切り捨て
                $ret = floor( $embossingTime / $roundingTime ) * $roundingTime;
            }

            $Log->trace("END roundingTime");
            
            return $ret;
        }

        /**
         * ユーザの給与の1日の基本代金を計算する
         * @param    $predeterminedTime     所定労働時間
         * @param    $salaryInfo            給与情報
         * @param    $totalWorkTime         総労働時間(計算用)
         * @param    $trialPeriod           試用期間情報
         * @param    $workNumberTimes       出勤回数(1回目の出勤は、「0」、2回目の出勤は、「1」)
         * @param    $date                  労働日
         * @param    $userID                ユーザID
         * @param    $lrAllInfo             就業規則
         * @return   日の基本代
         */
        private function getBaseSalaryDay( $predeterminedTime, $salaryInfo, $totalWorkTime, $trialPeriod, $workNumberTimes, $date, $userID, $lrAllInfo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getBaseSalaryDay");

            $ret = 0;
            // 試用期間の場合、試用期間分を計算する
            if( $trialPeriod['result'] != 0 )
            {
                if( $trialPeriod['result'] == 1 && $salaryInfo['trial_period_write_down_criteria'] == 1 )
                {
                    // 時給
                    // 総労働時間が、所定時間を超えているか
                    if( $totalWorkTime < $predeterminedTime )
                    {
                        // 所定時間未満の為、総労働時間分を計算
                        $ret = $this->calcHourlyRate( $salaryInfo['trial_period_wages_value'], $totalWorkTime );
                    }
                    else
                    {
                        // 所定時間以上の為、所定時間分を計算
                        $ret =  $this->calcHourlyRate( $salaryInfo['trial_period_wages_value'], $predeterminedTime );
                    }
                }
                else if( $trialPeriod['result'] == 2 && $salaryInfo['trial_period_write_down_criteria'] == 1 )
                {
                    // 時給
                    // 総労働時間が、所定時間を超えているか
                    if( $totalWorkTime < $predeterminedTime )
                    {
                        $normalTime = $totalWorkTime - $trialPeriod['remaining'];
                        // 所定時間未満の為、総労働時間分を計算
                        $ret = $this->calcHourlyRate( $salaryInfo['trial_period_wages_value'], $trialPeriod['remaining'] );
                        $ret += $this->calcHourlyRate( $salaryInfo['hourly_wage'], $normalTime );
                    }
                    else
                    {
                        // 所定時間以上の為、所定時間分を計算
                        $normalTime = $predeterminedTime - $trialPeriod['remaining'];
                        if( $normalTime < 0 )
                        {
                            $ret =  $this->calcHourlyRate( $salaryInfo['trial_period_wages_value'], $predeterminedTime );
                        }
                        else
                        {
                            $ret =  $this->calcHourlyRate( $salaryInfo['trial_period_wages_value'], $trialPeriod['remaining'] );
                            $ret +=  $this->calcHourlyRate( $salaryInfo['hourly_wage'], $normalTime );
                        }
                    }
                }
                else if( $salaryInfo['trial_period_write_down_criteria'] == 2 )
                {
                    // 日給
                    if( $workNumberTimes == 0 )
                    {
                        $ret = $salaryInfo['trial_period_wages_value'];
                    }
                }
                else if( $salaryInfo['trial_period_write_down_criteria'] == 3 )
                {
                    // 月給
                    if( $workNumberTimes == 0 )
                    {
                        $ret = $this->getBaseSalary( $salaryInfo['trial_period_wages_value'], $lrAllInfo, $date, $userID );
                    }
                }
                else if( $salaryInfo['trial_period_write_down_criteria'] == 4 )
                {
                    // 年俸
                    if( $workNumberTimes == 0 )
                    {
                        $wagesValue = $salaryInfo['trial_period_wages_value'] / 12;
                        $ret = $this->getBaseSalary( $wagesValue, $lrAllInfo, $date, $userID );
                    }
                }
            }
            else
            {
                // 賃金形態ごとの日の基本給を計算
                if( $salaryInfo['wage_form_id'] == 1 )
                {
                    // 時給
                    // 総労働時間が、所定時間を超えているか
                    if( $totalWorkTime < $predeterminedTime )
                    {
                        // 所定時間未満の為、総労働時間分を計算
                        $ret = $this->calcHourlyRate( $salaryInfo['hourly_wage'], $totalWorkTime );
                    }
                    else
                    {
                        // 所定時間以上の為、所定時間分を計算
                        $ret =  $this->calcHourlyRate( $salaryInfo['hourly_wage'], $predeterminedTime );
                    }
                }
                else if( $salaryInfo['wage_form_id'] == 2 )
                {
                    // 日給
                    if( $workNumberTimes == 0 )
                    {
                        $ret = $salaryInfo['base_salary'];
                    }
                }
                else if( $salaryInfo['wage_form_id'] == 3 )
                {
                    // 月給
                    if( $workNumberTimes == 0 )
                    {
                        $ret = $this->getBaseSalary( $salaryInfo['base_salary'], $lrAllInfo, $date, $userID );
                    }
                }
                else if( $salaryInfo['wage_form_id'] == 4 )
                {
                    // 年俸
                    if( $workNumberTimes == 0 )
                    {
                        $wagesValue = $salaryInfo['base_salary'] / 12;
                        $ret = $this->getBaseSalary( $wagesValue, $lrAllInfo, $date, $userID );
                    }
                }
            }

            $Log->trace("END getBaseSalaryDay");
            return $ret;
        }

        /**
         * ユーザは試用期間である
         * @param    $salaryInfo            給与情報
         * @param    $oneWorkTime           1勤怠の総労働時間(試用期間算出用)
         * @param    $oneTotalWorkingTime   1日勤怠の総労働時間(試用期間算出用)
         * @param    $userID                ユーザID
         * @param    $date                  勤務日
         * @return   試用期間情報
         */
        private function isTrialPeriod( $salaryInfo, $oneWorkTime, $oneTotalWorkingTime, $userID, $date )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START isTrialPeriod");
            
            $totalWorkingInfo = $this->getServiceAccumulatedSituation( $userID, $date );

            // 試用期間の判定
            $ret = array(
                            'result'    =>  0,
                            'remaining' =>  0,
                        );

            // 試用期間の種別設定
            if( $salaryInfo['trial_period_type_id'] == 2 )
            {
                // 試用期間の時間を分に修正
                $trialPeriodTime = ( $salaryInfo['trial_period_criteria_value'] * 60 );
                // 時間
                if( $trialPeriodTime > ( $oneWorkTime + $oneTotalWorkingTime + $totalWorkingInfo['totalWorkTime'] ) )
                {
                    // 試用期間中である
                    $ret['result']  = 1;
                    $ret['remaining']  = $trialPeriodTime - $oneWorkTime - $oneTotalWorkingTime;
                }
                else
                {
                    // 今回の勤務で超えたのか
                    if( $trialPeriodTime > ( $oneTotalWorkingTime + $totalWorkingInfo['totalWorkTime'] ) )
                    {
                        // 本日の途中までは、試用期間中である
                        $ret['result']  = 2;
                        $ret['remaining']  = $trialPeriodTime - $oneTotalWorkingTime - $totalWorkingInfo['totalWorkTime'];
                    }
                }
            }
            else if( $salaryInfo['trial_period_type_id'] == 3 )
            {
                // 労働日数
                if( $salaryInfo['trial_period_criteria_value'] > $totalWorkingInfo['totalWorkDays'] )
                {
                    // 試用期間中である
                    $ret['result']  = 1;
                    $ret['remaining']  = 0;
                }
            }
            else if( $salaryInfo['trial_period_type_id'] == 4 )
            {
                // 労働月数
                if( $salaryInfo['trial_period_criteria_value'] > $totalWorkingInfo['totalWorkMonths'] )
                {
                    // 試用期間中である
                    $ret['result']  = 1;
                    $ret['remaining']  = 0;
                }
            }
            else if( $salaryInfo['trial_period_type_id'] == 5 )
            {
                // 労働年数
                if( $salaryInfo['trial_period_criteria_value'] > $totalWorkingInfo['totalWorkYears'] )
                {
                    // 試用期間中である
                    $ret['result']  = 1;
                    $ret['remaining']  = 0;
                }
            }

            $Log->trace("END isTrialPeriod");
            return $ret;
        }

        /**
         * 月給/年俸制の日の基本給を取得する（年俸制は、12割した後に呼び出す）
         * @param    $baseSalary        基本給
         * @param    $lrAllInfo         就業規則
         * @param    $date              労働日
         * @param    $userID            ユーザID
         * @return   月給/年俸制の日の基本給
         */
        private function getBaseSalary( $baseSalary, $lrAllInfo, $date, $userID )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getBaseSalary");
            
            // 基本給を割る係数を算出
            $days = -1;

            // 月給制
            $monthTightening = $lrAllInfo['m_work_rules_time'][0]['month_tightening'];
            $monthDayList = $this->getMonthDayList( $date, $monthTightening );
            if( $lrAllInfo['m_work_rules_allowance'][0]['labor_cost_calculation'] == 1 )
            {
                $index = count( $monthDayList ) - 1;
                // 所定日数
                if( $lrAllInfo['m_work_rules_time'][0]['is_shift_holiday_use'] == 1 )
                {
                    // シフトの所定日数取得
                    $days = $this->getShiftPredeterminedTimeMultiple( $userID, $monthDayList[0], $monthDayList[$index] );
                }

                // シフト設定なし かつ 組織カレンダーから取得
                if( $days == -1 && $lrAllInfo['m_work_rules_time'][0]['is_organization_calendar_holiday_use'] == 1 )
                {
                    $days = $this->getCalendarPredeterminedTimeMultiple( $userID, $monthDayList[0], $monthDayList[$index], $monthDayList );
                }

            }

            // 所定日数が定義されなった場合
            if($days == -1)
            {
                // 月の総日数
                $days = count( $monthDayList );
            }

            $ret = $baseSalary / $days;

            $Log->trace("END getBaseSalary");
            
            return round( $ret );
        }

        /**
         * ユーザの給与情報を取得する
         * @param    $userID            ユーザID
         * @return   給与計算情報
         */
        private function getSalaryInfo( $userID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSalaryInfo");
            
            // 初期化
            $ret = array(
                            'user_detail_id'                    =>  0,
                            'organization_id'                   =>  0,
                            'wage_form_id'                      =>  0,
                            'base_salary'                       =>  0,
                            'hourly_wage'                       =>  0,
                            'trial_period_type_id'              =>  0,
                            'trial_period_criteria_value'       =>  0,
                            'trial_period_write_down_criteria'  =>  0,
                            'trial_period_wages_value'          =>  0,
                        );

            // ユーザのサラリー情報を取得
            $sql  = " SELECT "
                  . "     user_detail_id "
                  . "   , organization_id "
                  . "   , wage_form_id "
                  . "   , base_salary "
                  . "   , hourly_wage "
                  . "   , trial_period_type_id "
                  . "   , trial_period_criteria_value "
                  . "   , trial_period_write_down_criteria "
                  . "   , trial_period_wages_value "
                  . " FROM v_user "
                  . " WHERE eff_code = '適用中' AND user_id = :user_id ";

            $parameters = array( ':user_id'  => $userID, );

            $result = $DBA->executeSQL($sql, $parameters);

            if( $result === false )
            {
                $Log->trace("END getSalaryInfo");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret['organization_id'] = is_null( $data['organization_id'] ) ? 0 : $data['organization_id'];
                $ret['user_detail_id'] = is_null( $data['user_detail_id'] ) ? 0 : $data['user_detail_id'];
                $ret['wage_form_id'] = is_null( $data['wage_form_id'] ) ? 0 : $data['wage_form_id'];
                $ret['base_salary'] = is_null( $data['base_salary'] ) ? 0 : $data['base_salary'];
                $ret['hourly_wage'] = is_null( $data['hourly_wage'] ) ? 0 : $data['hourly_wage'];
                $ret['trial_period_type_id'] = is_null( $data['trial_period_type_id'] ) ? 0 : $data['trial_period_type_id'];
                $ret['trial_period_criteria_value'] = is_null( $data['trial_period_criteria_value'] ) ? 0 : $data['trial_period_criteria_value'];
                $ret['trial_period_write_down_criteria'] = is_null( $data['trial_period_write_down_criteria'] ) ? 0 : $data['trial_period_write_down_criteria'];
                $ret['trial_period_wages_value'] = is_null( $data['trial_period_wages_value'] ) ? 0 : $data['trial_period_wages_value'];
            }

            $Log->trace("END getSalaryInfo");
            return $ret;
        }

        /**
         * 勤務日前日までの累積法定外労働時間を取得する
         * @param    $userID         ユーザID
         * @param    $lrAllInfo      就業規則情報
         * @param    $date           勤務日
         * @param    $tableName      DBテーブル名
         * @return   法定外労働時間
         */
        private function getAccumulatedOvertimeHours( $userID, $lrAllInfo, $date, $tableName )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAccumulatedOvertimeHours");
            
            // 月初を設定
            $firstDate = date('Y-m-d', strtotime('first day of ' . $date));
            // 月の締日が、31日以外(月末以外)
            if( $lrAllInfo['m_work_rules_time'][0]['month_tightening'] != 31 )
            {
                // 締日の次の日が、開始日付
                $setDay = $lrAllInfo['m_work_rules_time'][0]['month_tightening'] + 1;
                
                // 勤務日の日にちを取得
                $day = substr( $date, 8, 2 );
                // 締日よりも、勤務日が前であるか
                if( $day <= $lrAllInfo['m_work_rules_time'][0]['month_tightening'] )
                {
                    // 先月
                    $firstDate = date('Y-m', strtotime('-1 month' . $date));
                }
                else
                {
                    // 当月
                    $firstDate = substr( $date, 0, 7 );
                }
                
                $firstDate .= '-' . $setDay;
            }

            // 初期化
            $ret = array(
                            'work_number_times'                       =>  0,
                            'working_days'                            =>  0,
                            'total_working_time'                      =>  0,
                            'statutory_overtime_hours'                =>  0,
                            'nonstatutory_overtime_hours'             =>  0,
                            'nonstatutory_overtime_hours_45h'         =>  0,
                            'nonstatutory_overtime_hours_60h'         =>  0,
                            'statutory_overtime_hours_no_pub'         =>  0,
                            'nonstatutory_overtime_hours_no_pub'      =>  0,
                            'nonstatutory_overtime_hours_45h_no_pub'  =>  0,
                            'nonstatutory_overtime_hours_60h_no_pub'  =>  0,
                            'overtime_hours_no_considered'            =>  0,
                            'overtime_hours_no_considered_no_pub'     =>  0,
                        );

            $dayName   = 'date';
            $isDel = ' AND is_del = 0 ';
            $orderName = 'attendance_time';
            // シフトテーブルの問い合わせ
            if( $tableName == 't_shift' )
            {
                $dayName = 'day';
                $isDel = '';
                $orderName = 'attendance';
            }

            // 累積残業情報を取得
            $sql  = " SELECT "
                  . "     SUM( total_working_time ) as total_working_time "
                  . "   , COUNT( " . $orderName . " ) as working_days "
                  . "   , SUM( statutory_overtime_hours ) as statutory_overtime_hours "
                  . "   , SUM( nonstatutory_overtime_hours ) as nonstatutory_overtime_hours "
                  . "   , SUM( nonstatutory_overtime_hours_45h ) as nonstatutory_overtime_hours_45h "
                  . "   , SUM( nonstatutory_overtime_hours_60h ) as nonstatutory_overtime_hours_60h "
                  . "   , SUM( statutory_overtime_hours_no_pub ) as statutory_overtime_hours_no_pub "
                  . "   , SUM( nonstatutory_overtime_hours_no_pub ) as nonstatutory_overtime_hours_no_pub "
                  . "   , SUM( nonstatutory_overtime_hours_45h_no_pub ) as nonstatutory_overtime_hours_45h_no_pub "
                  . "   , SUM( nonstatutory_overtime_hours_60h_no_pub ) as nonstatutory_overtime_hours_60h_no_pub "
                  . "   , SUM( overtime_hours_no_considered ) as overtime_hours_no_considered "
                  . "   , SUM( overtime_hours_no_considered_no_pub ) as overtime_hours_no_considered_no_pub "
                  . " FROM " . $tableName 
                  . " WHERE :s_date <= " . $dayName . " AND " . $dayName . " < :e_date and user_id = :user_id " . $isDel;

            $parameters = array( 
                                  ':user_id'  => $userID,
                                  ':s_date'   => $firstDate,
                                  ':e_date'   => $date,
                               );

            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END getAccumulatedOvertimeHours");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data;
                $ret['total_working_time'] = $this->changeMinuteFromTime( $data['total_working_time'] );
            }

            $Log->trace("END getAccumulatedOvertimeHours");
            return $ret;
        }
        
        /**
         * 指定範囲内の労働時間を取得する
         * @param    $userID         ユーザID
         * @param    $sDate          集計開始日
         * @param    $eDate          集計終了日(未満)
         * @param    $tableName      テーブル名
         * @return   指定範囲内の労働時間(分)
         */
        private function getWorkingHoursSpecifiedRange( $userID, $sDate, $eDate, $tableName )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getWorkingHoursSpecifiedRange");

            $dayName   = 'date';
            $isDel = ' AND is_del = 0 ';
            // シフトテーブルの問い合わせ
            if( $tableName == 't_shift' )
            {
                $dayName = 'day';
                $isDel = '';
            }

            // 当日の残業時間情報を取得
            $sql  = " SELECT SUM( total_working_time ) as total_working_time "
                  . " FROM " . $tableName 
                  . " WHERE :sDate <= " . $dayName . " AND " . $dayName . " < :eDate AND user_id = :user_id " . $isDel
                  . " GROUP BY user_id ";

            $parameters = array( 
                                  ':user_id'  => $userID,
                                  ':sDate'    => $sDate,
                                  ':eDate'    => $eDate,
                               );

            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END getWorkingHoursSpecifiedRange");
                return 0;
            }

            $ret = 0;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $this->changeMinuteFromTime( $data['total_working_time'] );
            }

            $Log->trace("END getWorkingHoursSpecifiedRange");

            return $ret;
        }
        
        /**
         * 勤務日当日の直近の法定外残業時間を取得する
         * @param    $userID                ユーザID
         * @param    $date                  勤務日
         * @param    $attendanceID          勤怠テーブルID
         * @param    &$currentOvertimeList  累積法定外労働時間
         * @return   なし
         */
        private function getTodayOvertimeHours( $userID, $date, $attendanceID, &$currentOvertimeList, $tableName )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getTodayOvertimeHours");

            $idName    = 'attendance_id';
            $dayName   = 'date';
            $orderName = 'attendance_time';
            $isDel = ' AND is_del = 0 ';
            // シフトテーブルの問い合わせ
            if( $tableName == 't_shift' )
            {
                $idName  = 'shift_id';
                $dayName = 'day';
                $orderName = 'attendance';
                $isDel = '';
            }

            // 当日の残業時間情報を取得
            $sql  = " SELECT "
                  . " " . $idName 
                  . "   , night_working_time "
                  . "   , total_working_time "
                  . "   , statutory_overtime_hours "
                  . "   , nonstatutory_overtime_hours "
                  . "   , nonstatutory_overtime_hours_45h "
                  . "   , nonstatutory_overtime_hours_60h "
                  . "   , statutory_overtime_hours_no_pub "
                  . "   , nonstatutory_overtime_hours_no_pub "
                  . "   , nonstatutory_overtime_hours_45h_no_pub "
                  . "   , nonstatutory_overtime_hours_60h_no_pub "
                  . "   , overtime_hours_no_considered "
                  . "   , overtime_hours_no_considered_no_pub "
                  . " FROM " . $tableName 
                  . " WHERE :date = " . $dayName . " AND user_id = :user_id " . $isDel
                  . " ORDER BY " . $orderName;

            $parameters = array( 
                                  ':user_id'  => $userID,
                                  ':date'     => $date,
                               );

            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END getTodayOvertimeHours");
                return;
            }

            $currentOvertimeList['work_number_times'] = 0;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                // 今回登録予定の勤怠レコードである
                if( $data[$idName] == $attendanceID )
                {
                    // 積算は終了する
                    break;
                }
                // 直前の勤怠での残業時間を積算する
                $currentOvertimeList['work_number_times'] += 1;
                $currentOvertimeList['total_working_time'] += $this->changeMinuteFromTime( $data['total_working_time'] );
                $currentOvertimeList['night_working_time'] += $this->changeMinuteFromTime( $data['night_working_time'] );
                $currentOvertimeList['statutory_overtime_hours'] += $data['statutory_overtime_hours'];
                $currentOvertimeList['nonstatutory_overtime_hours'] += $data['nonstatutory_overtime_hours'];
                $currentOvertimeList['nonstatutory_overtime_hours_45h'] += $data['nonstatutory_overtime_hours_45h'];
                $currentOvertimeList['nonstatutory_overtime_hours_60h'] += $data['nonstatutory_overtime_hours_60h'];
                $currentOvertimeList['statutory_overtime_hours_no_pub'] += $data['statutory_overtime_hours_no_pub'];
                $currentOvertimeList['nonstatutory_overtime_hours_no_pub'] += $data['nonstatutory_overtime_hours_no_pub'];
                $currentOvertimeList['nonstatutory_overtime_hours_45h_no_pub'] += $data['nonstatutory_overtime_hours_45h_no_pub'];
                $currentOvertimeList['nonstatutory_overtime_hours_60h_no_pub'] += $data['nonstatutory_overtime_hours_60h_no_pub'];
                $currentOvertimeList['overtime_hours_no_considered'] += $data['overtime_hours_no_considered'];
                $currentOvertimeList['overtime_hours_no_considered_no_pub'] += $data['overtime_hours_no_considered_no_pub'];
            }

            $Log->trace("END getAccumulatedOvertimeHours");
        }
        
        /**
         * 残業代計算
         * @param    $formula        計算方式
         * @param    $calcValue      計算に使用する基準値
         * @param    $hourlyWage     時給
         * @param    $time           勤務時間
         * @return   残業代
         */
        private function calcOvertimePayment( $formula, $calcValue, $hourlyWage, $time )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START calcOvertimePayment");

            $ret = 0;
            if( $formula == 1 )
            {
                // 割増率
                if( $calcValue == 100 )
                {
                    // 100%の場合、割増なしで計算
                    $ret = $this->calcHourlyRate( $hourlyWage, $time );
                }
                else
                {
                    // 割増率を100分の1にして計算する
                    $ret = $this->calcHourlyRate( ( $hourlyWage * ( $calcValue / 100 ) ), $time );
                }
            }
            else if( $formula == 1 )
            {
                // 残業単価( 基準値 × 勤務時間 )
                $ret = $this->calcHourlyRate( $calcValue * $time );
            }
            else
            {
                // 残業代
                if( $time > 0 )
                {
                    // 0時間以上勤務時に一律支給
                    $ret = $calcValue;
                }
            }

            $Log->trace("END calcOvertimePayment");
            return $ret;
        }

        /**
         * 時間(分)×金額の計算
         * @param    $hourlyWage     時給
         * @param    $minute         勤務時間(分)
         * @return   残業代
         */
        private function calcHourlyRate( $hourlyWage, $minute )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START calcHourlyRate");
            
            $ret = 0;
            // 分を時間形式に修正
            $time = $this->changeTimeFromMinute( $minute );

            // 時間を時と分に分割
            $timeSplit = explode( ':', $time );
            // 時間については、そのまま掛け算する
            $ret = ( $hourlyWage * $timeSplit[0] );
            
            // 分については、四捨五入する
            $minuteRate = round( $hourlyWage * ( $timeSplit[1] / 60 ) );

            // 時間＋分で計算した結果を返す
            $ret = $ret + $minuteRate;

            $Log->trace("END calcHourlyRate");
            return $ret;
        }
        
        /**
         * 登録用残業時間/残業代の計算
         * @param    $lrAllInfo               就業規則
         * @param    $currentOvertimeList     累積残業時間
         * @param    $overtimeHours           残業時間リスト(分)
         * @param    $isPublic                公休日を含む
         * @param    $hourlyWage              時給
         * @param    &$setModOvertimeList     登録用法定外残業時間リスト(分)
         * @return   残業代
         */
        private function getOvertimeHoursRegistration( $lrAllInfo, $currentOvertimeList, $overtimeHours, $isPublic, $hourlyWage, &$setModOvertimeList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getOvertimeHoursRegistration");
            
            $ret = 0;
            $setModOvertimeList['statutory_overtime_hours'] = $overtimeHours['inOvertime'];
            $setModOvertimeList['statutory_overtime_hours_no_pub'] = 0;
            // 該当する法定外残業時間を調査する(公休日含まない)
            if( $isPublic == false )
            {
                // 公休日以外は、計算
                $setModOvertimeList['statutory_overtime_hours_no_pub'] = $overtimeHours['inOvertime'];
                $this->getCalcOvertimeHours( $currentOvertimeList, $overtimeHours['outOvertime'], '_no_pub', $setModOvertimeList );
            }
            // 公休日含む
            $this->getCalcOvertimeHours( $currentOvertimeList, $overtimeHours['outOvertime'], '', $setModOvertimeList );

            // 残業代を計算する(公休日は、含めない時間で計算する)
            // 所定内残業時間代
            $ret += $this->calcOvertimePayment( $lrAllInfo['m_work_rules_allowance'][0]['legal_time_in_overtime'],
                                                $lrAllInfo['m_work_rules_allowance'][0]['legal_time_in_overtime_value'],
                                                $hourlyWage, $overtimeHours['inOvertime'] );

            // 法定外残業代(45時間以下)である
            if( $setModOvertimeList['nonstatutory_overtime_hours_no_pub'] < 2700 )
            {
                // 法定外残業代(45時間以下)
                $ret += $this->calcOvertimePayment( $lrAllInfo['m_work_rules_allowance'][0]['legal_time_out_overtime'],
                                                    $lrAllInfo['m_work_rules_allowance'][0]['legal_time_out_overtime_value'],
                                                    $hourlyWage, $overtimeHours['outOvertime'] );
            }
            else if( $setModOvertimeList['nonstatutory_overtime_hours_45h_no_pub'] < 900 )
            {
                // 法定外残業代(45時間超え、60時間以下)
                $ret += $this->calcOvertimePayment( $lrAllInfo['m_work_rules_allowance'][0]['legal_time_out_overtime_45'],
                                                    $lrAllInfo['m_work_rules_allowance'][0]['legal_time_out_overtime_value_45'],
                                                    $hourlyWage, $overtimeHours['outOvertime'] );
            }
            else
            {
                // 法定外残業代(60時間超え)
                $ret += $this->calcOvertimePayment( $lrAllInfo['m_work_rules_allowance'][0]['legal_time_out_overtime_60'],
                                                    $lrAllInfo['m_work_rules_allowance'][0]['legal_time_out_overtime_value_60'],
                                                    $hourlyWage, $overtimeHours['outOvertime'] );
            }

            $Log->trace("END getOvertimeHoursRegistration");
            return $ret;
        }
        
        /**
         * 登録用残業時間の計算
         * @param    $currentOvertimeList     累積残業時間
         * @param    $outOvertime             法定外残業時間(分)
         * @param    $idName                  公休日を含む/含まないの変数名(no_pub)
         * @param    &$setModOvertimeList     登録用法定外残業時間リスト(分)
         * @return   なし
         */
        private function getCalcOvertimeHours( $currentOvertimeList, $outOvertime, $idName, &$setModOvertimeList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getCalcOvertimeHours");
            
            // 該当する法定外残業時間を調査する
            // 45時間未満(2700分)である
            if( $currentOvertimeList['nonstatutory_overtime_hours' . $idName] < 2700 )
            {
                // 今回の法定外残業時間を足して、45時間以内であるか？
                if( ( $currentOvertimeList['nonstatutory_overtime_hours' . $idName] + $outOvertime ) <= 2700 )
                {
                    $setModOvertimeList['nonstatutory_overtime_hours' . $idName] = $outOvertime;
                }
                else
                {
                    // 合算時、45時間を超えた場合、超えた分を次の残業累積に合算する
                    $overtime = ( $currentOvertimeList['nonstatutory_overtime_hours' . $idName] + $outOvertime ) - 2700;
                    // 45時間以上の値
                    $setModOvertimeList['nonstatutory_overtime_hours_45h' . $idName] = $overtime;
                    // 45時間(最大値)を設定
                    $setModOvertimeList['nonstatutory_overtime_hours' . $idName] = 2700;
                }
            }
            
            // 45時間超え、60時間以下である(範囲は、0～15)
            if( $currentOvertimeList['nonstatutory_overtime_hours' . $idName] == 2700 && $currentOvertimeList['nonstatutory_overtime_hours_45h' . $idName] < 900 )
            {
                // 今回の法定外残業時間を足して、15(60)時間以内であるか？
                if( ( $currentOvertimeList['nonstatutory_overtime_hours_45h' . $idName] + $outOvertime ) <= 900 )
                {
                    $setModOvertimeList['nonstatutory_overtime_hours_45h' . $idName] = $outOvertime;
                }
                else
                {
                    // 合算時、15(60)時間を超えた場合、超えた分を次の残業累積に合算する
                    $overtime = ( $currentOvertimeList['nonstatutory_overtime_hours_45h' . $idName] + $outOvertime ) - 900;
                    // 15(60)時間以上の値
                    $setModOvertimeList['nonstatutory_overtime_hours_60h' . $idName] = $overtime;
                    // 45時間(最大値)を設定
                    $setModOvertimeList['nonstatutory_overtime_hours_45h' . $idName] = 900;
                }
            }
            
            // 60時間超えである
            if( $currentOvertimeList['nonstatutory_overtime_hours_45h' . $idName] == 900  )
            {
                $setModOvertimeList['nonstatutory_overtime_hours_60h' . $idName] = $outOvertime;
            }

            // みなし残業の計算
            $setModOvertimeList['overtime_hours_no_considered' . $idName] = $setModOvertimeList['statutory_overtime_hours' . $idName] + $setModOvertimeList['nonstatutory_overtime_hours' . $idName]
                                                                          + $setModOvertimeList['nonstatutory_overtime_hours_45h' . $idName] + $setModOvertimeList['nonstatutory_overtime_hours_60h' . $idName];

            $Log->trace("END getCalcOvertimeHours");
        }
        
        /**
         * 手当金額の計算
         * @param    $userID                ユーザID
         * @param    $date                  勤務日
         * @param    $tableName             テーブル名
         * @param    $totalWorkTime         1勤務の総労働時間
         * @param    $monthWorkingHours     直前の勤務まで総労働時間(月)
         * @param    $dayWorkingHours       直前の勤務まで総労働時間(日)
         * @param    $workingDays           前日までの出勤日数
         * @param    $workNumberTimes       当日の出勤回数
         * @param    $attendanceID          勤怠ID(シフトの場合,0が入ってくる)
         * @param    $organizationID        所属組織ID
         * @param    &$addAllowanceList     手当割り当てリスト
         * @return   手当金額
         */
        private function calcAllowanceAmount( $userID, $date, $tableName, $totalWorkTime, $monthWorkingHours, $dayWorkingHours, $workingDays, $workNumberTimes, $attendanceID, $organizationID, &$addAllowanceList )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START calcAllowanceAmount");

            $ret = 0;
            // ユーザに設定されている、手当を取得する
            $userAllowanceList = $this->getUserAllowance( $userID );

            // 当日の勤務日数を算出する(1回目の勤務のみ、1勤務とする)
            $work = 0;
            if( $workNumberTimes == 0 )
            {
                $work = 1;
            }

            // ユーザに設定されている手当数分ループ
            foreach( $userAllowanceList as $userAllowance )
            {
                $retAllowance = array(
                                        'user_id'           => $userID,
                                        'attendance_id'     => $attendanceID,
                                        'allowance_id'      => $userAllowance['allowance_id'],
                                        'allowance_amount'  => 0,
                                     );
                $hourlyRateTime = $totalWorkTime;
                // 支給条件の確認
                $isSupply = false;
                if( $userAllowance['payment_conditions_id'] == 1 )
                {
                    // 支給条件を確認する
                    if( $userAllowance['wage_form_type_id'] == 1 && $monthWorkingHours == 0 )
                    {
                        // 初回のみ
                        $isSupply = true;
                    }
                    else if( $userAllowance['wage_form_type_id'] == 2 && $dayWorkingHours == 0 )
                    {
                        // 初回のみ
                        $isSupply = true;
                    }
                    else if( $userAllowance['wage_form_type_id'] != 1 && $userAllowance['wage_form_type_id'] != 2 )
                    {
                        // 月/日以外は、基準時間クリアで、設定可能とする
                        $isSupply = true;
                    }
                }
                else if( $userAllowance['payment_conditions_id'] == 2 )
                {
                    // 労働時間
                    $workTime = $dayWorkingHours;
                    if( $userAllowance['wage_form_type_id'] == 1 )
                    {
                        $workTime = $monthWorkingHours;
                    }

                    // 基準時間をクリアしているか
                    if( ( $userAllowance['working_hours']  ) <= ( $workTime + $totalWorkTime ) )
                    {
                        // 支給条件を確認する
                        if( $userAllowance['wage_form_type_id'] == 1 || $userAllowance['wage_form_type_id'] == 2 )
                        {
                            // 月に1回又は、日に1回の場合、今回の勤務で超えた場合のみ、金額の追加を許可する
                            if( ( $userAllowance['working_hours'] ) > $workTime )
                            {
                                $isSupply = true;
                            }
                        }
                        else
                        {
                            // 月/日以外は、基準時間クリアで、設定可能とする
                            $isSupply = true;
                        }
                        
                        if( $isSupply )
                        {
                            $hourlyRateTime = ( $workTime + $totalWorkTime ) - ( $userAllowance['working_hours'] );
                        }
                    }
                }
                else if( $userAllowance['payment_conditions_id'] == 3 )
                {
                    // 労働日数
                    // 基準時間をクリアしているか
                    if( $userAllowance['working_days'] <= ( $workingDays + $work ) )
                    {
                        // 月に1回の場合、今回の勤務で超えた場合のみ、金額の追加を許可する
                        if( $userAllowance['working_days'] > $workingDays )
                        {
                            $isSupply = true;
                        }
                    }
                }
                else if( $userAllowance['payment_conditions_id'] == 4 )
                {
                    // 手動
                    if( $attendanceID > 0 )
                    {
                        // 実際の勤怠のみ、集計対象とする
                        $isSupply = $this->isAddManuallyAllowance( $userID, $attendanceID, $userAllowance['allowance_id'] );
                        if( $userAllowance['wage_form_type_id'] == 2 )
                        {
                            // 日に1回の場合、初回の勤務のみ、金額の追加を許可する
                            if( $dayWorkingHours != 0 )
                            {
                                $isSupply = false;
                            }
                        }
                    }
                }
                else if( $userAllowance['payment_conditions_id'] == 5 )
                {
                    // 期間
                    $isSupply = $this->isAddOrganizationCalendarAllowance( $organizationID, $date, $userAllowance['allowance_id'] );

                    // 日に1回の場合、初回の勤務のみ、金額の追加を許可する
                    if( $userAllowance['wage_form_type_id'] == 2 )
                    {
                        // 日に1回の場合、初回の勤務のみ、金額の追加を許可する
                        if( $dayWorkingHours != 0 )
                        {
                            $isSupply = false;
                        }
                    }
                }
                
                // 手当金額合算
                if( $isSupply )
                {
                    $amountMoney = 0;
                    if( $userAllowance['wage_form_type_id'] == 3 )
                    {
                        // 1時間に一回は、総労働時間分かけて算出する
                        $amountMoney = round( $userAllowance['allowance_amount'] * floor( $hourlyRateTime / 60 ) );
                    }
                    else
                    {
                        // 該当の手当のみ計算する
                        $amountMoney = $userAllowance['allowance_amount'];
                    }
                    
                    $ret += $amountMoney;
                    // 手当割り当てリストに登録するか
                    if( $attendanceID != 0 )
                    {
                        $retAllowance['allowance_amount'] = $amountMoney;
                        array_push( $addAllowanceList, $retAllowance );
                    }
                }
            }

            $Log->trace("END calcAllowanceAmount");
            
            return $ret;
        }

        /**
         * ユーザに紐づいている手当リスト
         * @param    $userID        ユーザID
         * @return   手当リスト
         */
        private function getUserAllowance( $userID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserAllowance");

            // ユーザに設定されている、手当を取得する
            $sql  = " SELECT "
                  . "    mua.allowance_amount "
                  . "  , ma.allowance_id "
                  . "  , ma.wage_form_type_id "
                  . "  , ma.payment_conditions_id "
                  . "  , ma.working_hours "
                  . "  , ma.working_days "
                  . " FROM m_user_allowance mua INNER JOIN v_user vu ON mua.user_detail_id = vu.user_detail_id AND vu.eff_code = '適用中' "
                  . "                           INNER JOIN m_allowance ma ON mua.allowance_id = ma.allowance_id AND ma.is_del = 0 "
                  . " WHERE vu.user_id = :user_id ";

            $parameters = array( 
                                  ':user_id'  => $userID,
                               );

            $result = $DBA->executeSQL($sql, $parameters);
            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getUserAllowance");
                return;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $ret, $data );
            }

            $Log->trace("END getUserAllowance");
            
            return $ret;
        }

        /**
         * 手動の手当を追加
         * @param    $userID        ユーザID
         * @param    $attendanceID  勤怠テーブルID
         * @param    $allowanceID   手当ID
         * @return   true：手当追加   false：手当を追加しない
         */
        private function isAddManuallyAllowance( $userID, $attendanceID, $allowanceID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START isAddManuallyAllowance");

            // 手当テーブルから、手動の手当がついているか取得する
            $sql  = " SELECT COUNT( allowance_id ) as count "
                  . " FROM t_allowance " 
                  . " WHERE user_id = :user_id AND attendance_id = :attendance_id AND allowance_id = :allowance_id ";

            $parameters = array( 
                                  ':user_id'        => $userID,
                                  ':attendance_id'  => $attendanceID,
                                  ':allowance_id'   => $allowanceID,
                               );

            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END isAddManuallyAllowance");
                return;
            }

            $ret = false;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['count'] == 1 )
                {
                    $ret = true;
                }
            }

            $Log->trace("END isAddManuallyAllowance");
            
            return $ret;
        }

        /**
         * 組織カレンダーの手当を追加
         * @param    $organizationID    ユーザID
         * @param    $date              勤怠日
         * @param    $allowanceID       手当ID
         * @return   true：手当追加   false：手当を追加しない
         */
        private function isAddOrganizationCalendarAllowance( $organizationID, $date, $allowanceID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START isAddOrganizationCalendarAllowance");

            // 当日の残業時間情報を取得
            $sql  = " SELECT COUNT( mcd.allowance_id ) as count "
                  . " FROM  m_organization_calendar moc INNER JOIN m_calendar_detail mcd " 
                  . "   ON  moc.organization_calendar_id = mcd.organization_calendar_id " 
                  . " WHERE moc.organization_id = :organization_id  "
                  . "   AND mcd.date = :date AND mcd.allowance_id = :allowance_id ";

            $parameters = array( 
                                  ':organization_id'   => $organizationID,
                                  ':date'              => $date,
                                  ':allowance_id'      => $allowanceID,
                               );

            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END isAddOrganizationCalendarAllowance");
                return;
            }

            $ret = false;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['count'] == 1 )
                {
                    $ret = true;
                }
            }

            $Log->trace("END isAddOrganizationCalendarAllowance");
            
            return $ret;
        }

        /**
         * 累積勤務状況の取得
         * @param    $userID        ユーザID
         * @param    $workingDays   労働日
         * @return   累積勤務状況
         */
        private function getServiceAccumulatedSituation( $userID, $workingDays )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getServiceAccumulatedSituation");

            // 総労働時間/日数を取得する
            $sql  = " SELECT user_id, date, total_working_time FROM t_attendance "
                  . " WHERE  user_id = :user_id AND is_del = 0 AND date < :date ORDER BY date";

            $parameters = array( 
                                  ':user_id'  => $userID,
                                  ':date'     => $workingDays,
                               );

            $result = $DBA->executeSQL($sql, $parameters);
            $ret = array(
                            'totalWorkTime'     =>  0,
                            'totalWorkDays'     =>  0,
                            'totalWorkMonths'   =>  0,
                            'totalWorkYears'    =>  0,
                        );
            if( $result === false )
            {
                $Log->trace("END getServiceAccumulatedSituation");
                return;
            }

            $targetWorkDays      = 0;
            $targetWorkMonths    = 0;
            $targetWorkYears     = 0;
            $initFlg = false;

            if( 0 < count($result) )
            {
                $initFlg = true;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                // 労働日を分割
                $date = explode( '-', $data['date'] );
                
                // 初期化を行うか
                if( $initFlg )
                {
                    $initFlg = false;
                    $ret['totalWorkDays'] += 1;
                    $targetWorkDays = $date[2];
                    $ret['totalWorkMonths'] += 1;
                    $targetWorkMonths = $date[1];
                    $ret['totalWorkYears'] += 1;
                    $targetWorkYears = $date[0];
                }
                
                // 総労働時間を合算
                $ret['totalWorkTime'] += $this->changeMinuteFromTime( $data['total_working_time'] );

                // 労働日数の合算
                if( $date[0] == $targetWorkYears && $date[1] == $targetWorkMonths && $date[2] != $targetWorkDays )
                {
                    $ret['totalWorkDays'] += 1;
                    $targetWorkDays = $date[2];
                }
                
                // 労働月数の合算
                if( $date[0] == $targetWorkYears && $date[1] != $targetWorkMonths )
                {
                    $ret['totalWorkMonths'] += 1;
                    $targetWorkMonths = $date[1];
                }
                
                // 労働年数の合算
                if( $date[0] != $targetWorkYears )
                {
                    $ret['targetWorkYears'] += 1;
                    $targetWorkYears = $date[0];
                }
            }

            $Log->trace("END getServiceAccumulatedSituation");

            return $ret;
        }

        /**
         * 週の所定時間を取得
         * @param    $userID        ユーザID
         * @param    $workingDays   労働日
         * @param    $weekDeadline  週の〆曜日(1：月 ～ 7：日)
         * @param    $lrAllInfo     就業規則
         * @return   累積勤務状況
         */
        private function getPredeterminedTimeOfWeek( $userID, $workingDays, $weekDeadline, $lrAllInfo )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getPredeterminedTimeOfWeek");

            // 日付リスト(指定日から一週間分)を取得
            $searchDayList = $this->getWeeklyList( $workingDays, $weekDeadline );

            $val = -1;
            if( $lrAllInfo['m_work_rules_time'][0]['is_shift_working_hours_use'] == 1 )
            {
                // シフトの所定日数取得
                $val = $this->getShiftPredeterminedTimeMultiple( $userID, $searchDayList[0], $searchDayList[6] );
            }

            // シフト設定なし かつ 組織カレンダーから取得
            if( $val == -1 && $lrAllInfo['m_work_rules_time'][0]['is_work_rules_working_hours_use'] == 1 )
            {
                $val = $this->getCalendarPredeterminedTimeMultiple( $userID, $searchDayList[0], $searchDayList[6], $searchDayList );
            }
            
            // 日の所定時間を計算
            $ret = array();
            $ret['workingHoursTime']     = $val * $lrAllInfo['m_overtime'][0]['regular_working_hours_time'];
            $ret['workingOverHoursTime'] = $val * $lrAllInfo['m_overtime'][0]['overtime_reference_time'];
            
            // 日と週の大きい方を採用する
            if( $ret['workingHoursTime'] < $lrAllInfo['m_overtime'][1]['regular_working_hours_time'] )
            {
                $ret['workingHoursTime'] = $lrAllInfo['m_overtime'][1]['regular_working_hours_time'];
            }
            
            if( $ret['workingOverHoursTime'] < $lrAllInfo['m_overtime'][1]['overtime_reference_time'] )
            {
                $ret['workingOverHoursTime'] = $lrAllInfo['m_overtime'][1]['overtime_reference_time'];
            }

            $Log->trace("END getPredeterminedTimeOfWeek");

            return $ret;
        }

        /**
         * 指定日から、1週間分の日付リストを取得する
         * @param    $workingDays   労働日
         * @param    $weekDeadline  週の〆曜日(1：月 ～ 7：日)
         * @return   1週間区切りの日付リスト
         */
        protected function getDateList( $workingDays, $weekDeadline )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getDateList");
            
            // 週の開始を算出する
            $weekStart = $weekDeadline + 1;
            if( $weekStart == 8 )
            {
                $weekStart = 1;
            }

            // 曜日番号を取得
            $searchDayList = array();
            $checkDate = $workingDays;
            for( $i = 0; $i < 7; $i++ )
            {
                array_push( $searchDayList, $checkDate );
                $datetime = new DateTime($checkDate);
                $weekNo = (int)$datetime->format('w');
                if( $weekNo == 0 )
                {
                    // 日曜日の場合、番号を修正
                    $weekNo = 7;
                }
                
                // 週の締日(開始曜日)まで戻ったか
                if( $weekStart == $weekNo )
                {
                    break;
                }
                else
                {
                    // 前日の日付を設定
                    $checkDate = date('Y-m-d', strtotime('-1 day' . $checkDate));
                }
            }

            $Log->trace("END getDateList");

            return $searchDayList;
        }

        /**
         * 指定日を含む1週間分の日付リストを取得する
         * @param    $workingDays   労働日
         * @param    $weekDeadline  週の〆曜日(1：月 ～ 7：日)
         * @return   1週間区切りの日付リスト
         */
        protected function getWeeklyList( $workingDays, $weekDeadline )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getWeeklyList");
            
            // 週の始めを取得
            $dayList = $this->getDateList( $workingDays, $weekDeadline );

            // 曜日番号を取得
            $searchDayList = array();
            $checkDate = array_pop( $dayList );

            for( $i = 0; $i < 7; $i++ )
            {
                array_push( $searchDayList, $checkDate );
                $datetime = new DateTime($checkDate);
                $weekNo = (int)$datetime->format('w');
                if( $weekNo == 0 )
                {
                    // 日曜日の場合、番号を修正
                    $weekNo = 7;
                }
                
                // 週の締日まで進んだのか
                if( $weekDeadline == $weekNo )
                {
                    break;
                }
                else
                {
                    // 前日の日付を設定
                    $checkDate = date('Y-m-d', strtotime('+1 day' . $checkDate));
                }
            }

            $Log->trace("END getWeeklyList");

            return $searchDayList;
        }

        /**
         * 指定日の1ヶ月の日数を取得する
         * @param    $workingDays       労働日
         * @param    $cutoffMonthDay    月の締日
         * @return   1ヵ月分の日付リスト
         */
        public function getMonthDayList( $workingDays, $cutoffMonthDay )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMonthDayList");
            
            $workYm =substr( $workingDays, 0, 7);
            
            $endMonth   = date('Y-m-t', strtotime($workYm));
            $beginMonth = date('Y-m-01', strtotime($workYm));
            // 月末以外を指定
            if( $cutoffMonthDay != 31 )
            {
                $endMonth   = $workYm . "-" . sprintf("%02d", $cutoffMonthDay);
                $beginMonth = date('Y-m-d', strtotime('-1 months' . $endMonth));
                $beginMonth = date('Y-m-d', strtotime('+1 day' . $beginMonth));
                
                // 労働日の日付を取得
                $day = substr( $workingDays, 8, 2);
                
                // 労働日が締日以降である
                if( $cutoffMonthDay < $day )
                {
                    // それぞれ、一ヶ月後の範囲を設定する
                    $endMonth   = date('Y-m-d', strtotime('+1 months' . $endMonth));
                    $beginMonth = date('Y-m-d', strtotime('+1 months' . $beginMonth));
                }
            }

            // 一ヶ月分の日付リストを取得する
            $searchDayList = array();
            $checkDate = $beginMonth;
            for( $i = 0; $i < 31; $i++ )
            {
                array_push( $searchDayList, $checkDate );
                
                // 月の締日まで進んだのか
                if( $endMonth == $checkDate )
                {
                    break;
                }
                else
                {
                    // 翌日の日付を設定
                    $checkDate = date('Y-m-d', strtotime('+1 day' . $checkDate));
                }
            }

            $Log->trace("END getMonthDayList");

            return $searchDayList;
        }

        /**
         * シフト情報の所定日数を取得する
         * @param    $userID        ユーザID
         * @param    $sDate         集計開始日
         * @param    $eDate         集計終了日
         * @return   シフトの所定日数
         */
        protected function getShiftPredeterminedTimeMultiple( $userID, $sDate, $eDate )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getShiftPredeterminedTimeMultiple");
            
            $ret = -1;   // 初期値-1時間を設定
            // シフトの設定日を取得
            $sql  = " SELECT is_holiday, day FROM t_shift WHERE day >= :sDate AND day <= :eDate AND user_id = :user_id ";
            $parameters = array( 
                                    ':user_id' => $userID, 
                                    ':sDate'   => $sDate, 
                                    ':eDate'   => $eDate, 
                                );
            $result = $DBA->executeSQL($sql, $parameters);

            if( $result === false )
            {
                $Log->trace("END getShiftPredeterminedTimeMultiple");
                return $ret;
            }

            $searchDay = "";
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( SystemParameters::$STATUTORY_HOLIDAY != $data['is_holiday'] && $searchDay != $data['day'] )
                {
                    $ret++;
                }
                
                $searchDay = $data['day'];
            }

            if( $ret != -1 )
            {
                // -1スタート分を補てん
                $ret = $ret + 1;
            }

            $Log->trace("END getShiftPredeterminedTimeMultiple");

            return $ret;
        }

        /**
         * 組織カレンダーから、所定労働日数を取得
         * @param    $userID            ユーザID
         * @param    $sDate             集計開始日
         * @param    $eDate             集計終了日
         * @param    $searchDayList     調査対象日のリスト
         * @return   組織カレンダーの所定日数
         */
        private function getCalendarPredeterminedTimeMultiple( $userID, $sData, $eDate, $searchDayList )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCalendarPredeterminedTimeMultiple");

            // ユーザ所属の組織IDを設定
            $organizationID = $this->getParentOrganization( $userID );

            // 組織カレンダー情報を取得
            $sql  = " SELECT mcd.date, mcd.is_holiday, moc.is_sunday, moc.is_public_holiday, moc.is_saturday_1 "
                  . "      , moc.is_saturday_2, moc.is_saturday_3, moc.is_saturday_4, moc.is_saturday_5 "
                  . " FROM  m_organization_calendar moc LEFT OUTER JOIN m_calendar_detail mcd " 
                  . "   ON  moc.organization_calendar_id = mcd.organization_calendar_id AND mcd.date >= :sDate AND mcd.date <= :eDate " 
                  . " WHERE moc.organization_id = :organization_id  ";

            $parameters = array( 
                                  ':organization_id'   => $organizationID,
                                  ':sDate'             => $sData,
                                  ':eDate'             => $eDate,
                               );

            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END getCalendarPredeterminedTimeMultiple");
                return 0;
            }

            $holidayInfoList = array();
            // カレンダーの休日情報リストを作成
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $holidayInfoList, $data );
            }

            $ret = 0;
            // 調査日数分のリスト分検索する
            foreach( $searchDayList as $searchDay )
            {
                $addFlag = true;
                $holidayDetailInfo = "";
                // 組織カレンダーに設定されている各日付を調査する
                foreach( $holidayInfoList as $holidayInfo )
                {
                    if( $holidayInfo['date'] == $searchDay )
                    {
                        $holidayDetailInfo = $holidayInfo['is_holiday'];
                        if( $holidayInfo['is_holiday'] == SystemParameters::$STATUTORY_HOLIDAY )
                        {
                            // 日付が一致し、法定休日の場合、カウントしない
                            $addFlag = false;
                            break;
                        }
                    }
                }
                
                // カレンダー詳細で、法定休日を設定済み
                if( !$addFlag )
                {
                    continue;
                }
                
                // カレンダー詳細で、何か設定済みの場合、カウントする
                if( $holidayDetailInfo != '' )
                {
                    $ret++;
                    continue;
                }
                
                // 調査日の曜日を算出
                $datetime = new DateTime($searchDay);
                $weekNo = (int)$datetime->format('w');
                
                // 土曜日(6)の場合
                if( $weekNo == 6 )
                {
                    // 調査日付から、年間の週数を取得
                    $searchDayW = intval(date('W',strtotime($searchDay)));
                    // 調査日付の月初を取得する
                    $firstDate = date('Y-m-d', strtotime('first day of ' . $searchDay));
                    // 調査日付の月初の年間の週数を取得
                    $firstDateW = intval(date('W',strtotime($firstDate)));
                    // 調査月の第何週かを求める
                    $weekEyes = $searchDayW - $firstDateW + 1;
                    // 調査対象日が、法定休日であるか確認
                    if( $holidayInfoList[0]['is_saturday_'.$weekEyes] == SystemParameters::$STATUTORY_HOLIDAY )
                    {
                        // 法定休日の場合、カウントしない
                        $addFlag = false;
                    }
                }
                
                // 日曜日(0)の場合
                if( $weekNo == 0 )
                {
                    // 調査対象日が、法定休日であるか確認
                    if( $holidayInfoList[0]['is_sunday'] == SystemParameters::$STATUTORY_HOLIDAY )
                    {
                        // 法定休日の場合、カウントしない
                        $addFlag = false;
                    }
                }
                
                // 祝日に法定休日の設定がある
                if( $holidayInfoList[0]['is_public_holiday'] == SystemParameters::$STATUTORY_HOLIDAY )
                {
                    // 調査日付が祝日である
                    if( "" != $this->getPublicHolidayName( $searchDay ) )
                    {
                        // 祝日で、法定休日の為、カウントしない
                        $addFlag = false;
                    }
                }
                
                if( $addFlag )
                {
                    $ret++;
                }
            }

            $Log->trace("END getCalendarPredeterminedTimeMultiple");
            
            return $ret;
        }

        /**
         * 所属の組織IDを取得する
         * @param    $userID        ユーザID
         * @return   所属の組織ID
         */
        private function getParentOrganization( $userID )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START getParentOrganization");

            $sql = ' SELECT organization_id FROM v_user '
                 . " WHERE eff_code = '適用中' AND user_id = :user_id ";

            $parameters = array( ':user_id' => $userID, );

            $result = $DBA->executeSQL($sql, $parameters);

            if( $result === false )
            {
                $Log->trace("END getParentOrganization");
                return 0;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                return $data['organization_id'];
            }

            $Log->trace("END getParentOrganization");

            return 0;
        }

    }
?>
