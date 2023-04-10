<?php
    /**
     * @file      個人日別勤怠修正画面(View)
     * @author    USE R.dendo
     * @date      2016/07/22
     * @version   1.00
     * @note      個人日別勤怠修正画面
     */

    // CheckAlert.phpを読み込む
    require './Model/CheckAlert.php';
    // WorkingTimeRegistration.phpを読み込む
    require './Model/WorkingTimeRegistration.php';

    /**
     * 勤怠修正マスタクラス
     * @note   勤怠修正マスタテーブルの管理を行うの初期設定を行う
     */
    class AttendanceCorrection extends CheckAlert
    {
        protected $ESTIMATE_SHIFT = 79;             ///< 概算シフトのID
        protected $ESTIMATE_PERFORMANCE = 78;       ///< 概算実績のID
        protected $workTimeReg = null;              ///< 労働時間計算用モデル

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // ModelBaseのコンストラクタ
            parent::__construct();
            global $ESTIMATE_SHIFT, $ESTIMATE_PERFORMANCE, $workTimeReg;
            $ESTIMATE_SHIFT = 79;
            $ESTIMATE_PERFORMANCE = 78;
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
         * 個人日別勤怠修正画面一覧表
         * @param    $sendingUserID     表示対象のユーザID
         * @param    $sendingDate       表示対象の日付(例：YYYY/MM/DD)
         * @param    $attendanceFlag    取得対象フラグ 1：日単位 2：月単位 3：対象日一括
         * @param    &$isEnrollment     在籍フラグ     true：在籍   false：在籍していない
         * @return   成功時：$attendanceCorListまたは$userName
         */
        public function getAttendanceData($sendingUserID, $sendingDate, $attendanceFlag, &$isEnrollment )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAttendanceData");

            if( $attendanceFlag == 1 )
            {
                // 勤怠データ(個人日一覧)
                $attendanceCorList = $this->getListData($sendingUserID, $sendingDate);
                $isEnrollment = $this->isEnrollment($sendingUserID, $sendingDate);
            }
            else if( $attendanceFlag == 2 )
            {
                // 勤怠データ(個人月一覧)を取得
                $attendanceCorList = $this->getMonthListData($sendingUserID, $sendingDate, $isEnrollment);
            }
            else if( $attendanceFlag == 3 )
            {
                // 勤怠データ(対象日一括修正一覧)を取得
                $attendanceCorList = $this->getTargetDayBulk($sendingUserID, $sendingDate);
                $isEnrollment = true;
            }

            // ユーザ名を取得
            $userName = $this->getUserName($sendingUserID, $sendingDate);
            
            if( $attendanceFlag != 3 )
            {
                // ユーザID/ユーザ名をリストへ再設定
                foreach ($attendanceCorList as &$value)
                {
                    $value['user_id']   = $sendingUserID;
                    $value['user_name'] = $userName;
                }
                unset($value);
            }
            $Log->trace("END getAttendanceData");

            return $attendanceCorList;
        }
        
        /**
         * 休日IDを取得
         * @param    $userID     ユーザID
         * @param    $date       打刻日
         * @param    $shiftInfo  シフト情報
         * @return   休日ID
         */
        public function getIsHoliday( $userID, $date, $shiftInfo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getIsHoliday");

            // ユーザIDを取得する
            $userInfo = $this->getUserInfo( $userID, $date );
            $employInfo = $this->getUserEmploymentInfo($userInfo);

            // 休日IDを取得
            $ret = $this->getHolidayInfo( $userInfo['organization_id'], $userInfo, $shiftInfo, $employInfo, $date );

            $Log->trace("END getIsHoliday");

            return $ret;
        }
        
        /**
         * 概算シフト/概算実績一覧取得
         * @param    $sendingUserID
         * @return   $ret
         */
        public function getProposedBudgetList( $sendingUserID )
        {
            global $DBA, $Log, $ESTIMATE_SHIFT, $ESTIMATE_PERFORMANCE; // グローバル変数宣言
            $Log->trace("START getProposedBudgetList");
            // 概算シフトの表示可否
            $view_s = $this->getProposedBudget( $sendingUserID, $ESTIMATE_SHIFT );

            // 概算実績の表示可否
            $view_p = $this->getProposedBudget( $sendingUserID, $ESTIMATE_PERFORMANCE );
            
            $ret = array( 
                            $ESTIMATE_SHIFT => $view_s,
                            $ESTIMATE_PERFORMANCE => $view_p,
                        );
            $Log->trace("END getProposedBudgetList");
            return $ret;
        }
        
        /**
         * 手当テーブル一覧取得
         * @param    $sendingUserID
         * @param    $attendanceId
         * @return   $allowanceAllList
         */
        public function getAllowanceTableList( $sendingUserID, $attendanceId )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAllowanceTableList");
            
            $searchArray = array();
            
            $sql = ' SELECT ta.attendance_id, ta.user_id, ta.allowance_id FROM t_allowance ta '
                 . '        INNER JOIN m_allowance ma ON ta.allowance_id = ma.allowance_id '
                 . ' WHERE ta.user_id = :user_id AND ta.attendance_id = :attendance_id AND ma.is_del = 0 ';
            $searchArray = array( ':user_id' => $sendingUserID, ':attendance_id' => $attendanceId );
            $result = $DBA->executeSQL($sql, $searchArray);
            
            $allowanceAllList = array();
            if( $result === false )
            {
                $Log->trace("END getAllowanceTableList");
                return $allowanceAllList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $allowanceAllList, $data);
            }

            $Log->trace("END getAllowanceTableList");
            return $allowanceAllList;
        }
        
        /**
         * 勤怠マスタデータ登録
         * @param    $postArray     登録データ
         * @return   SQLの実行結果
         */
        public function addNewData($postArray)
        {
            global $DBA, $Log, $workTimeReg; // グローバル変数宣言
            $Log->trace("START addNewData");

            if($DBA->beginTransaction())
            {
                $ret = "";

                // 勤怠マスタ追加/更新振り分け後、対象の勤怠ID取得
                $attendanceId = 0;
                $ret = $this->attendanceProcessingDistribution($postArray, $attendanceId);

                // 勤怠マスタの追加/更新の結果がfalseの場合ロールバック
                if( ( $attendanceId == 0 ) || $ret !== "MSG_BASE_0000" )
                {
                    $DBA->rollBack();
                    $errMsg = "勤怠マスタへの登録更新処理にエラーが生じました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return $ret;
                }

                // 手当テーブル追加/更新/削除処理
                $ret = $this->attendanceTableProcessingDistribution($postArray, $attendanceId);
                if($ret !== "MSG_BASE_0000")
                {
                    $DBA->rollBack();
                    $errMsg = "手当テーブルへの登録更新処理にエラーが生じました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return $ret;
                }

                // 勤怠時間の更新を行う
                $ret = $workTimeReg->setAttendanceInfo( $attendanceId );
                if($ret !== "MSG_BASE_0000")
                {
                    // 下位でログ出力している為、省略
                    $DBA->rollBack();
                    $Log->trace("END addNewData");
                    return $ret;
                }

                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "勤怠マスタ登録処理のコミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "勤怠ID：" . $postarray['attendance_id']. "ユーザID：" . $postarray['user_id'] . "サーバエラー";
                $Log->fatalDetail($errMsg);
                return "MSG_FW_DB_TRANSACTION_NG";
            }

            $Log->trace("END addNewData");
            return $ret;
        }

        /**
         * 勤怠マスタ登録データ削除
         * @param    $postArray   入力パラメータ(削除フラグ1/ユーザID/勤怠ID/更新時間)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            $sql = 'UPDATE t_attendance SET'
                . ' is_del = 1 '
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE attendance_id = :attendance_id AND update_time = :update_time ';

            $parameters = array(
                ':attendance_id'                 => $postArray['attendanceId'],
                ':update_user_id'                => $postArray['user_id'],
                ':update_time'                   => $postArray['updateTime'],
                ':update_organization'           => $postArray['organization'],
            );

            // 削除SQLの実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3114");
                $errMsg = "勤怠ID：" . $postarray['attendanceId']. "ユーザID：" . $postarray['userId'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_BASE_0001";
            }

            $Log->trace("END delUpdateData");

            return "MSG_BASE_0000";
        }
        
        /**
         * 所属の組織IDを取得する
         * @param    $userID        ユーザID
         * @return   所属の組織ID
         */
        public function getParentOrganization( $userID )
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
         * 指定日の1ヶ月の日数を取得する
         * @param    $workingDays       労働日
         * @param    $cutoffMonthDay    月の締日
         * @return   1ヵ月分の日付リスト
         */
        public function getMonthDayList( $workingDays, $cutoffMonthDay )
        {
            global $Log, $DBA, $workTimeReg; // グローバル変数宣言
            $Log->trace("START getMonthDayList");

            $dayList = $workTimeReg->getMonthDayList( $workingDays, $cutoffMonthDay );

            $Log->trace("END getMonthDayList");

            return $dayList;
        }

        /**
         * ユーザ名一覧取得SQL文作成
         * @param    $userID       ユーザID
         * @param    $selectDate   対象日
         * @return   $userName
         */
        public function getUserName( $userID, $selectDate )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserName");

            $searchArray = array();
            
            $sql = ' SELECT ud.user_name FROM m_user_detail ud '
                 . ' , (SELECT user_id, max(application_date_start) as application_date_start '
                 . ' FROM m_user_detail WHERE user_id = :user_id AND application_date_start <= :date '
                 . ' GROUP BY user_id ) newData '
                 . ' WHERE ud.user_id = newData.user_id '
                 . ' AND ud.application_date_start = newData.application_date_start ';
            $searchArray = array( ':user_id' => $userID, ':date' => $selectDate, );
            $result = $DBA->executeSQL($sql, $searchArray);

            $userName = "";
            if( $result === false )
            {
                $Log->trace("END getUserName");
                return $userName;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $userName = $data['user_name'];
            }

            $Log->trace("END getUserName");
            return $userName;
        }

        /**
         * 打刻時間の整合性チェック
         * @param    $postArray       労働日
         * @return   整合性チェック結果 OK：''    NG：エラーメッセージ
         */
        public function isTimeConsistency( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START isTimeConsistency");

            // 入力データの必須チェック(ユーザID)
            if( empty( $postArray['userId'] ) )
            {
                // 氏名が選択されていません
                $Log->trace("END isTimeConsistency");
                return "MSG_WAR_2127";
            }
            
            // 入力データの必須チェック(日付)
            if( empty( $postArray['date'] ) )
            {
                // 日付が選択されていません
                $Log->trace("END isTimeConsistency");
                return "MSG_WAR_2128";
            }

            // 打刻時間を分へ修正
            $attendanceTime = $this->convertDateToTime ( $postArray['attendance_time'], $postArray['date'] );
            $clockOutTime   = $this->convertDateToTime ( $postArray['clock_out_time'], $postArray['date'] );
            $sBreakTime_1   = $this->convertDateToTime ( $postArray['s_break_time_1'], $postArray['date'] );
            $eBreakTime_1   = $this->convertDateToTime ( $postArray['e_break_time_1'], $postArray['date'] );
            $sBreakTime_2   = $this->convertDateToTime ( $postArray['s_break_time_2'], $postArray['date'] );
            $eBreakTime_2   = $this->convertDateToTime ( $postArray['e_break_time_2'], $postArray['date'] );
            $sBreakTime_3   = $this->convertDateToTime ( $postArray['s_break_time_3'], $postArray['date'] );
            $eBreakTime_3   = $this->convertDateToTime ( $postArray['e_break_time_3'], $postArray['date'] );

            // 欠勤チェック
            if( $attendanceTime <> -1 && $postArray['isholiday'] == SystemParameters::$ABSENCE )
            {
                // 欠勤設定で、出勤時間が入力されている場合、エラーとする
                $Log->trace("END isTimeConsistency");
                return "MSG_WAR_2132";
            }

            // 出勤時間の入力チェック
            if( $attendanceTime == -1 && ( $clockOutTime <> -1 || 
                $sBreakTime_1 <> -1   || $eBreakTime_1 <> -1 || 
                $sBreakTime_2 <> -1   || $eBreakTime_2 <> -1 || 
                $sBreakTime_3 <> -1   || $eBreakTime_3 <> -1 )  )
            {
                // 出勤時間の入力ないが、他の時間が入力されている
                $Log->trace("END isTimeConsistency");
                return "MSG_WAR_2110";
            }

            // 出勤時間と退勤時間のチェック(出勤時間より、退勤時間が後に設定されているか)
            if( $attendanceTime > $clockOutTime && 0 <= $clockOutTime )
            {
                // 出勤時間の方が遅い
                $Log->trace("END isTimeConsistency");
                return "MSG_WAR_2111";
            }
            
            // 出勤時間と退勤時間のチェック(出勤時間と退勤時間が同じ時間ではないか)
            if( $attendanceTime == $clockOutTime && 0 <= $attendanceTime )
            {
                // 出勤時間の方が遅い
                $Log->trace("END isTimeConsistency");
                return "MSG_WAR_2124";
            }
            
            // 出勤時間と休憩時間のチェック(出勤時間より、休憩時間が後に設定されているか)
            for( $i = 1; $i < 4; $i++ )
            {
                // 休憩開始
                if( $attendanceTime > ${'sBreakTime_' . $i} && 0 <= ${'sBreakTime_' . $i} )
                {
                    // 出勤時間の方が遅い
                    $Log->trace("END isTimeConsistency");
                    return "MSG_WAR_2112";
                }
                
                // 休憩終了
                if( $attendanceTime > ${'eBreakTime_' . $i} && 0 <= ${'eBreakTime_' . $i} )
                {
                    // 出勤時間の方が遅い
                    $Log->trace("END isTimeConsistency");
                    return "MSG_WAR_2113";
                }
            }
            
            // 退勤時間と休憩時間のチェック(退勤時間より、休憩時間が前に設定されているか)
            for( $i = 1; $i < 4; $i++ )
            {
                // 休憩開始
                if( $clockOutTime < ${'sBreakTime_' . $i} && 0 <= ${'sBreakTime_' . $i} && 0 <= $clockOutTime )
                {
                    // 退勤時間の方が早い
                    $Log->trace("END isTimeConsistency");
                    return "MSG_WAR_2114";
                }
                
                // 休憩終了
                if( $clockOutTime < ${'eBreakTime_' . $i} && 0 <= ${'eBreakTime_' . $i} && 0 <= $clockOutTime )
                {
                    // 退勤時間の方が早い
                    $Log->trace("END isTimeConsistency");
                    return "MSG_WAR_2115";
                }
            }
            
            // 休憩開始時間と休憩終了時間のチェック(休憩開始時間より、休憩終了時間が前に設定されているか)
            for( $i = 1; $i < 4; $i++ )
            {
                // 休憩開始時間と休憩終了時間
                if( 0 > ${'sBreakTime_' . $i} && 0 <= ${'eBreakTime_' . $i} )
                {
                    // 休憩開始時間の入力がない
                    $Log->trace("END isTimeConsistency");
                    return "MSG_WAR_2118";
                }
            
                // 休憩開始時間と休憩終了時間
                if( ${'eBreakTime_' . $i} < ${'sBreakTime_' . $i} && 0 <= ${'sBreakTime_' . $i} && 0 <= ${'eBreakTime_' . $i} )
                {
                    // 休憩終了時間の方が早い
                    $Log->trace("END isTimeConsistency");
                    return "MSG_WAR_2116";
                }
                
                // 休憩開始時間と休憩終了時間
                if( ${'eBreakTime_' . $i} == ${'sBreakTime_' . $i} && 0 <= ${'sBreakTime_' . $i} )
                {
                    // 休憩開始時間と休憩終了時間が一緒
                    $Log->trace("END isTimeConsistency");
                    return "MSG_WAR_2125";
                }
            }

            // 休憩時間帯の整合性チェック(休憩1/休憩2/休憩3の順に設定されているか)
            for( $i = 2; $i < 4; $i++ )
            {
                // 1つ前が入力していないのに、入力していないか
                if( ( 0 > ${'sBreakTime_' . ($i-1)} || 0 > ${'eBreakTime_' . ($i-1)} ) && 
                    ( 0 <= ${'sBreakTime_' . $i} || 0 <= ${'eBreakTime_' . $i} ) )
                {
                    // 休憩時間若い方が遅い
                    $Log->trace("END isTimeConsistency");
                    return "MSG_WAR_2117";
                }
                
                // 休憩時間帯内の順番
                if( ${'eBreakTime_' . ($i-1) } > ${'sBreakTime_' . $i} && 0 <= ${'sBreakTime_' . $i} && 0 <= ${'eBreakTime_' . ($i-1) } )
                {
                    // 休憩時間若い方が遅い
                    $Log->trace("END isTimeConsistency");
                    return "MSG_WAR_2117";
                }
            }

            // 入力漏れチェック(退勤打刻があるのに、休憩終了していない打刻がないか)
            if(   $clockOutTime <> -1 && (
                ( $sBreakTime_1 <> -1 && $eBreakTime_1 == -1 ) || 
                ( $sBreakTime_2 <> -1 && $eBreakTime_2 == -1 ) || 
                ( $sBreakTime_3 <> -1 && $eBreakTime_3 == -1 ) )  )
            {
                // 退勤打刻があるのに、休憩終了していない打刻がないか
                $Log->trace("END isTimeConsistency");
                return "MSG_WAR_2119";
            }

            // 同日の勤怠の出退勤時間で、整合をチェック
            $attendanceInfoList = $this->getAttendanceInfo( $postArray['userId'], $postArray['date'] );
            foreach( $attendanceInfoList as $attendanceInfo )
            {
                // 入力されたデータ以外
                if( $attendanceInfo['attendance_id'] != $postArray['attendanceId'] )
                {
                    $attendanceTimeDB = $this->changeMinuteFromTime( $attendanceInfo['attendance_time'] );
                    $clockOutTimeDB   = $this->changeMinuteFromTime( $attendanceInfo['clock_out_time'] );

                    // 入力された出勤時間が、他の時間と重複していないか
                    if( $attendanceTimeDB < $attendanceTime && $attendanceTime < $clockOutTimeDB )
                    {
                        // 出勤時間が、他の勤務時間帯と被っている
                        $Log->trace("END isTimeConsistency");
                        return "MSG_WAR_2120";
                    }
                    
                    // 入力された退勤時間が、他の時間と重複していないか
                    if( $attendanceTimeDB < $clockOutTime && $clockOutTime < $clockOutTimeDB )
                    {
                        // 退勤時間が、他の勤務時間帯と被っている
                        $Log->trace("END isTimeConsistency");
                        return "MSG_WAR_2121";
                    }
                    
                    // 入力された出勤/退勤時間が、他の勤怠と一致していないか
                    if( $attendanceTimeDB == $attendanceTime && $clockOutTime == $clockOutTimeDB )
                    {
                        // 出勤/退勤時間が、他の勤怠と一致
                        $Log->trace("END isTimeConsistency");
                        return "MSG_WAR_2122";
                    }
                    
                    // 入力された出勤時間が、他の時間と重複していないか
                    if( $attendanceTime < $attendanceTimeDB && $attendanceTimeDB < $clockOutTime )
                    {
                        // 出勤時間が、他の勤務時間帯と被っている
                        $Log->trace("END isTimeConsistency");
                        return "MSG_WAR_2123";
                    }
                }
            }

            // 労働時間が1分以上あるか（出退勤時間設定時）
            if( $attendanceTime <> -1 && $clockOutTime <> -1 )
            {
                $workingHours = $clockOutTime - $attendanceTime;
                $breakTime = 0;
                // 休憩時間の計算
                for( $i = 1; $i < 4; $i++ )
                {
                    // 休憩開始時間と休憩終了時間
                    if( ${'sBreakTime_' . $i} <> -1 && ${'eBreakTime_' . $i} <> -1 )
                    {
                        // 休憩時間の累積
                        $breakTime = $breakTime + ( ${'eBreakTime_' . $i} - ${'sBreakTime_' . $i} );
                    }
                }
                
//                // 勤務時間と休憩時間がイコールではない
//                if( 0 >= ( $workingHours - $breakTime ) )
//                {
//                    // 労働時間が0分以下
//                    $Log->trace("END isTimeConsistency");
//                    return "MSG_WAR_2126";
//                }
                
                // 労働時間が24時間を超えている
                if( ( $workingHours - $breakTime ) > 1440 )
                {
                    // 労働時間が24時間超えている
                    $Log->trace("END isTimeConsistency");
                    return "MSG_WAR_2130";
                }
            }

            // 1日の開始時間の整合性チェック
            if( !empty( $postArray['organizationID'] ) )
            {
                $startTimeDay      = $this->getStartTimeDay( $postArray['organizationID'], $postArray['date'] );
                $startTimeDayToday = $this->changeMinuteFromTime( $startTimeDay );
                $startTimeDayMin   = $startTimeDayToday + 1440;
                if( $startTimeDayMin < $clockOutTime )
                {
                    // 退勤時間が、1日の開始時間より後の時間を設定している
                    $Log->trace("END isTimeConsistency");
                    return "MSG_WAR_2129";
                }
                
                // 出勤時間の制限を排除 2017/10/06 millionet oota
                // 出勤時間が入力している
//                if( $attendanceTime <> -1 )
//                {
//                    if( $attendanceTime < ( $startTimeDayToday - 60 ) )
//                    {
//                        // 出勤時間が、1日の開始時間より1時間以上前を設定している
//                        $Log->trace("END isTimeConsistency");
//                        return "MSG_WAR_2131";
//                    }
//                }
            }

            // 前日の勤怠の出退勤時間で、整合をチェック
            // 出勤時間が入力している
            if( $attendanceTime <> -1 )
            {
                $targetTime = strtotime( $postArray['date'] . ' 00:00:00');
                $beforeDate = date('Y-m-d', strtotime('-1 day', $targetTime));
                $attendanceInfoList = $this->getAttendanceInfo( $postArray['userId'], $beforeDate );
                foreach( $attendanceInfoList as $attendanceInfo )
                {
                    // 前日の退勤時間が、当日の出勤時間を超えていないか
                    $beforeClockOutTime = $this->changeMinuteFromTime( $attendanceInfo['clock_out_time'] );
                    if( 1440 < $beforeClockOutTime )
                    {
                        $beforeClockOutTime = $beforeClockOutTime - 1440;
                        if( $attendanceTime < $beforeClockOutTime )
                        {
                            // 前日の勤務時間と重複している
                            $Log->trace("END isTimeConsistency");
                            return "MSG_WAR_2120";
                        }
                    }
                }
            }

            // 次日の勤怠の出退勤時間で、整合をチェック
            // 退勤時間が入力している
            if( $clockOutTime <> -1 )
            {
                $targetTime = strtotime( $postArray['date'] . ' 00:00:00');
                $nextDate = date('Y-m-d', strtotime('+1 day', $targetTime));
                $attendanceInfoList = $this->getAttendanceInfo( $postArray['userId'], $nextDate );
                foreach( $attendanceInfoList as $attendanceInfo )
                {
                    // 当日の退勤時間が、次日の出勤時間を超えていないか
                    $nextAttendance = $this->changeMinuteFromTime( $attendanceInfo['attendance_time'] );
                    $beforeClockOutTime = $clockOutTime;
                    if( 1440 < $beforeClockOutTime )
                    {
                        $beforeClockOutTime = $beforeClockOutTime - 1440;
                        if( $nextAttendance < $beforeClockOutTime )
                        {
                            // 次日の勤務時間と重複している
                            $Log->trace("END isTimeConsistency");
                            return "MSG_WAR_2121";
                        }
                    }
                }
            }

            $Log->trace("END isTimeConsistency");
            // エラーなし
            return '';
        }

        /**
         * 指定日の勤怠情報を取得する
         * @param    $userID        ユーザID
         * @param    $date          勤務日
         * @return   指定日の勤怠情報
         */
        private function getAttendanceInfo( $userID, $date )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START getAttendanceInfo");

            $sql = " SELECT attendance_id, attendance_time, clock_out_time FROM  v_attendance_record "
                 . " WHERE  is_del = 0 AND user_id = :user_id AND date = :date ";
            $parameters = array( 
                                 ':user_id' => $userID, 
                                 ':date'    => $date, 
                               );
            
            $ret = array();
            
            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END getAttendanceInfo");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $ret, $data );
            }

            $Log->trace("END getAttendanceInfo");

            return $ret;
        }

        /**
         * 組織の1日の開始時間を取得する
         * @param    $organizationID  ユーザID
         * @param    $date            勤務日
         * @return   指定日の勤怠情報
         */
        private function getStartTimeDay( $organizationID, $date )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START getStartTimeDay");

            $sql = " SELECT od.start_time_day FROM m_organization_detail od, "
                 . "        ( SELECT od.organization_id, max(od.application_date_start) as application_date_start  "
                 . "          FROM m_organization_detail od  WHERE od.application_date_start <= :date "
                 . "          GROUP BY od.organization_id "
                 . "        ) newOrg    "
                 . " WHERE od.organization_id = newOrg.organization_id AND od.application_date_start = newOrg.application_date_start "
                 . "       AND od.organization_id = :organization_id";

            $parameters = array( 
                                 ':organization_id' => $organizationID, 
                                 ':date'            => $date, 
                               );
            
            $ret = "";
            $result = $DBA->executeSQL($sql, $parameters);
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
         * 打刻時間の分単位に修正
         * @param    $date     日付(YYYY-MM-DD hh:mm)
         * @param    $target   日付(YYYY-MM-DD)
         * @return   分
         */
        private function convertDateToTime( $date, $target )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START convertDateToTime");

            // NULLの場合は、-1を返す
            if( empty( $date ) )
            {
                $Log->trace("END convertDateToTime");
                return -1;
            }

            // 日付部分をカット
            $time = substr( $date, 11 );
            $ret = $this->changeMinuteFromTime( $time );

            // 日付の桁上がりしているか
            if( $target != substr( $date, 0, 10 ) )
            {
                $ret = $ret + (60 * 24);
            }

            $Log->trace("END convertDateToTime");

            return $ret;
        }
        
        /**
         * 勤怠マスタ追加/更新処理振り分け
         * @param    $postArray(組織カレンダーマスタ登録情報)
         * @return   SQL実行結果（定型文）
         */
        private function attendanceProcessingDistribution($postArray, &$attendanceId)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START attendanceProcessingDistribution");

            // 新規登録か既存組織の適用予定作成かの判定
            if(!empty($postArray['attendanceId']))
            {
                // 勤怠マスタ更新
                $ret = $this->modAttendanceUpdateData($postArray);
                $attendanceId = $postArray['attendanceId'];
            }
            else
            {
                // 新規登録の場合勤怠マスタへ登録
                $ret = $this->addAttendanceNewData($postArray, $attendanceId);
            }

            $Log->trace("END attendanceProcessingDistribution");
            return $ret;
        }
        
        /**
         * 手当テーブル追加/更新処理振り分け
         * @param    $applicationDateId
         * @param    $laborRegId
         * @param    $postArray
         * @return   SQL実行結果（定型文）
         */
        private function attendanceTableProcessingDistribution($postArray, $attendanceId)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START attendanceTableProcessingDistribution");
            $ret = "";
            
            $userId = $postArray['userId'];

            // 手当テーブルを削除
            $this->allowanceTableDel( $attendanceId, $userId );

            // 手当テーブルの更新
            $ret = $this->addNewAllowanceData($postArray, $attendanceId);
            
            // 更新結果の確認
            if($ret !== "MSG_BASE_0000")
            {
                $errMsg = "手当テーブルの更新処理に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END attendanceTableProcessingDistribution");
                return $ret;
            }

            $Log->trace("END attendanceTableProcessingDistribution");
            return $ret;
        }

        /**
         * 勤怠マスタ新規データ登録
         * @param    $postArray   入力パラメータ( organization_id/attendance_time/clock_out_time/s_break_time_1/e_break_time_1/s_break_time_2/e_break_time_2/s_break_time_3/e_break_time_3
                                                /dateuser_id/is_holiday/registration_user_id/registration_organization/update_user_id/update_organization)
         * @return   SQLの実行結果
         */
        private function addAttendanceNewData( $postArray, &$attendanceId )
        {
            global $DBA,$Log; // グローバル変数宣言
            $Log->trace("START addAttendanceNewData");
            
            // 就業規則IDを取得する
            $userInfo = $this->getUserInfo( $postArray['userId'], $postArray['date'] );
            $employInfo = $this->getUserEmploymentInfo($userInfo);
            
            // シフト情報を取得
            $attendanceTime  = substr($postArray['attendance_time'], 11, 5 );
            $iAttendanceTime = $this->changeMinuteFromTime( $attendanceTime );;
            $shiftInfo = $this->getShiftInfo( $userInfo, $postArray['date'], $iAttendanceTime );

            // 既に登録済みのシフトIDではないかチェックする
            $shiftID = $this->isShiftID( $shiftInfo['shift_id'] );

            // 勤務状況が公休の場合に実績登録する場合の対応を追加 2017/08/10 millionet oota
            // 欠勤フラグ
            $absenceCount = 1;
            if( $postArray['isholiday'] != 0 && $postArray['isholiday'] != '' ) {
                // 勤怠状況が出勤以外の場合、欠勤フラグを0にする
                $absenceCount = 0;
                
            } else if( $postArray['attendance_time'] != '' ) {
                // 勤怠状況が出勤で勤務時間があれば欠勤フラグを0にする
                $absenceCount = 0;
            }

            $sql = 'INSERT INTO t_attendance( organization_id'
                . '                      , labor_regulations_id'
                . '                      , shift_id'
                . '                      , attendance_time'
                . '                      , clock_out_time'
                . '                      , s_break_time_1'
                . '                      , e_break_time_1'
                . '                      , s_break_time_2'
                . '                      , e_break_time_2'
                . '                      , s_break_time_3'
                . '                      , e_break_time_3'
                . '                      , date'
                . '                      , absence_count'
                . '                      , user_id'
                . '                      , is_holiday'
                . '                      , registration_time'
                . '                      , registration_user_id'
                . '                      , registration_organization'
                . '                      , update_time'
                . '                      , update_user_id'
                . '                      , update_organization'
                . '                      ) VALUES ('
                . '                      :organization_id'
                . '                      , :labor_regulations_id'
                . '                      , :shift_id'
                . '                      , :attendance_time'
                . '                      , :clock_out_time'
                . '                      , :s_break_time_1'
                . '                      , :e_break_time_1'
                . '                      , :s_break_time_2'
                . '                      , :e_break_time_2'
                . '                      , :s_break_time_3'
                . '                      , :e_break_time_3'
                . '                      , :date'
                . '                      , :absence_count'
                . '                      , :user_id'
                . '                      , :is_holiday'
                . '                      , current_timestamp'
                . '                      , :registration_user_id'
                . '                      , :registration_organization'
                . '                      , current_timestamp'
                . '                      , :update_user_id'
                . '                      , :update_organization)';

            $parameters = array(
                ':organization_id'               => $postArray['organizationID'],
                ':labor_regulations_id'          => $employInfo['labor_regulations_id'],
                ':shift_id'                      => $shiftID,
                ':attendance_time'               => $postArray['attendance_time'],
                ':clock_out_time'                => $postArray['clock_out_time'] ,
                ':s_break_time_1'                => $postArray['s_break_time_1'] ,
                ':e_break_time_1'                => $postArray['e_break_time_1'] ,
                ':s_break_time_2'                => $postArray['s_break_time_2'] ,
                ':e_break_time_2'                => $postArray['e_break_time_2'] ,
                ':s_break_time_3'                => $postArray['s_break_time_3'] ,
                ':e_break_time_3'                => $postArray['e_break_time_3'] ,
                ':date'                          => $postArray['date'],
                ':absence_count'                 => $absenceCount,
                ':user_id'                       => $postArray['userId'],
                ':is_holiday'                    => $postArray['isholiday'],
                ':registration_user_id'          => $postArray['user_id'],
                ':registration_organization'     => $postArray['organization'],
                ':update_user_id'                => $postArray['user_id'],
                ':update_organization'           => $postArray['organization'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3110");
                $errMsg = "勤怠ID：" . $postarray['attendanceId']. "ユーザID：" . $postarray['userId'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3110";
            }
            
            // Lastidの更新
            $attendanceId = $DBA->lastInsertId( "t_attendance" );

            $Log->trace("END addAttendanceNewData");

            return "MSG_BASE_0000";
        }

        /**
         * 勤怠マスタ登録データ修正
         * @param    $postArray   入力パラメータ( attendance_time/clock_out_time/s_break_time_1/e_break_time_1/s_break_time_2/e_break_time_2/s_break_time_3/e_break_time_3
                                                /attendance_id/update_time)
         * @return   SQLの実行結果
         */
        private function modAttendanceUpdateData( $postArray )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START modUpdateData");
            $sql = 'UPDATE t_attendance SET'
                . '   attendance_time = :attendance_time'
                . ' , organization_id = :organization_id'
                . ' , is_holiday      = :isholiday'
                . ' , clock_out_time = :clock_out_time'
                . ' , s_break_time_1 = :s_break_time_1'
                . ' , e_break_time_1 = :e_break_time_1'
                . ' , s_break_time_2 = :s_break_time_2'
                . ' , e_break_time_2 = :e_break_time_2'
                . ' , s_break_time_3 = :s_break_time_3'
                . ' , e_break_time_3 = :e_break_time_3'
                . ' , absence_count  = :absence_count' 
                . ' , modify_count = modify_count + 1'
                . ' , update_user_id = :update_user_id'
                . ' , update_time = current_timestamp'
                . ' , update_organization = :update_organization'
                . ' WHERE attendance_id = :attendance_id AND update_time = :update_time AND approval = 0 ';

            // 勤務状況が公休の場合に実績登録する場合の対応を追加 2017/08/10 millionet oota
            // 欠勤フラグ
            $absenceCount = 1;
            if( $postArray['isholiday'] != 0 && $postArray['isholiday'] != '' ) {
                // 勤怠状況が出勤以外の場合、欠勤フラグを0にする
                $absenceCount = 0;
                
            } else if( $postArray['attendance_time'] != '' ) {
                // 勤怠状況が出勤で勤務時間があれば欠勤フラグを0にする
                $absenceCount = 0;
            }

            $parameters = array(
                ':organization_id'               => $postArray['organizationID'],
                ':isholiday'                     => $postArray['isholiday'],
                ':attendance_time'               => $postArray['attendance_time'],
                ':clock_out_time'                => $postArray['clock_out_time'] ,
                ':s_break_time_1'                => $postArray['s_break_time_1'] ,
                ':e_break_time_1'                => $postArray['e_break_time_1'] ,
                ':s_break_time_2'                => $postArray['s_break_time_2'] ,
                ':e_break_time_2'                => $postArray['e_break_time_2'] ,
                ':s_break_time_3'                => $postArray['s_break_time_3'] ,
                ':e_break_time_3'                => $postArray['e_break_time_3'] ,
                ':attendance_id'                 => $postArray['attendanceId'],
                ':absence_count'                 => $absenceCount,
                ':update_user_id'                => $postArray['user_id'],
                ':update_time'                   => $postArray['updateTime'],
                ':update_organization'           => $postArray['organization'],
            );

            // 更新SQLの実行
            if( !$DBA->executeSQL($sql, $parameters, true ) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3113");
                $errMsg = "勤怠ID：" . $postarray['attendanceId']. "ユーザID：" . $postarray['userId'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_BASE_0001";
            }

            $Log->trace("END modUpdateData");

            return "MSG_BASE_0000";
        }
        
        /**
         * 手当テーブル新規登録
         * @param    $postArray
         * @return   SQLの実行結果
         */
        private function addNewAllowanceData( $postArray, $attendanceId )
        {
            global $DBA,$Log; // グローバル変数宣言
            $Log->trace("START addNewAllowanceData");

            $sql = 'INSERT INTO t_allowance( attendance_id'
                . '                      , user_id'
                . '                      , allowance_id'
                . '                      ) VALUES ('
                . '                      :attendance_id'
                . '                      , :user_id'
                . '                      , :allowance_id)';

            foreach( $postArray['allowanceList'] as $allowance )
            {
                $isInsert = 'false';
                foreach( $allowance as $key => $val )
                {
                    $param = array(
                        ':attendance_id'         => $attendanceId,
                        ':user_id'               => $postArray['userId'],
                        ':allowance_id'          => $key ,
                    );
                    $isInsert = $val;
                }

                if( $isInsert == 'true' )
                {
                    // 新規登録SQLの実行
                    if( !$DBA->executeSQL($sql, $param, true ) )
                    {
                        // SQL実行エラー
                        $Log->warn("MSG_ERR_3112");
                        $errMsg = "勤怠ID：" . $postarray['attendanceId']. "ユーザID：" . $postarray['userId'] . "の登録失敗";
                        $Log->warnDetail($errMsg);
                        return "MSG_BASE_0001";
                    }
                }
            }
            
            $Log->trace("END addNewAllowanceData");

            return "MSG_BASE_0000";
        }
        
        /**
         * 手当テーブル削除
         * @param    $hourlyWageChangeId
         * @return   なし
         */
        private function allowanceTableDel( $attendanceId, $userId )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START allowanceTableDel");

            $sql = ' DELETE FROM t_allowance WHERE attendance_id = :attendance_id AND user_id = :user_id ';
            $parameters = array( 
                ':attendance_id' => $attendanceId,
                ':user_id' => $userId,
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3115");
                $errMsg = "勤怠ID：" . $postarray['attendanceId']. "ユーザID：" . $postarray['userId'] . "の失敗";
                $Log->warnDetail($errMsg);
                return "MSG_BASE_0001";
            }

            $Log->trace("END allowanceTableDel");
            return "MSG_BASE_0000";
        }

        /**
         * 概算シフト/概算実績一覧取得SQL文作成
         * @param    $sendingUserID
         * @param    $outputItemID
         * @return   $ret
         */
        private function getProposedBudget( $sendingUserID, $outputItemID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getProposedBudget");

            $securityID = $this->getSecurityId($sendingUserID);
            $displayItemID = $this->getDisplayItemId($sendingUserID, $securityID);
            
            $sql = ' SELECT vu.security_id, ms.display_item_id, md.output_type_id, md.output_item_id, md.is_display '
                 . ' FROM v_user vu '
                 . ' INNER JOIN m_security ms ON vu.security_id = ms.security_id '
                 . ' INNER JOIN m_display_item_detail md ON ms.display_item_id = md.display_item_id '
                 . ' WHERE md.output_type_id = 1 AND md.display_item_id = :display_item_id AND vu.security_id = :security_id AND md.output_item_id = :output_item_id ';
            $parametersArray = array(
                                        ':security_id'      =>  $securityID,
                                        ':display_item_id'  =>  $displayItemID,
                                        ':output_item_id'   =>  $outputItemID,
                                    );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $ret = false;
            if( $result === false )
            {
                $Log->trace("END getProposedBudget");
                return $ret;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['is_display'] == 1 )
                {
                    $ret = true;
                }
            }
            
            $Log->trace("END getProposedBudget");

            return $ret;
        }

        /**
         * 勤怠修正マスタ一覧取得SQL文作成
         * @param    $userID
         * @param    $selectDate
         * @return   $attendanceCorList
         */
        private function getListData($userID, $selectDate)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $earlierMonth = date('Y/m/01', strtotime($selectDate));
            $searchArray = array();

            $sql  = $this->creatSelectSQL(false);
            $sql .= ' WHERE dayList.day = :date ORDER BY var.attendance_time, var.shift_attendance_time ';

            $searchArray = array( 
                                    ':user_id' => $userID, 
                                    ':earlierMonth' => $earlierMonth, 
                                    ':date' => $selectDate, 
                                );
            $result = $DBA->executeSQL($sql, $searchArray);

            $attendanceCorList = array();
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $attendanceCorList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $attendanceCorList, $data);
            }

            $Log->trace("END getListData");

            return $attendanceCorList;
        }

        /**
         * 勤怠修正マスタ一覧取得(月)SQL文作成
         * @param    $userID            ユーザID
         * @param    $selectDate        対象月
         * @param    &$isEnrollment     在籍フラグ     true：在籍   false：在籍していない
         * @return   $attendanceCorList
         */
        private function getMonthListData( $userID, $selectDate, &$isEnrollment )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMonthListData");

            // 対象日を設定
            $targetDate = date("Y/m/d");
            if( $selectDate != $targetDate )
            {
                $targetDate = $selectDate;
            }

            // ユーザ情報を取得
            $userInfo = $this->getUserInfo( $userID, $targetDate );
            // 就業規則情報を取得
            $employInfo = $this->getUserEmploymentInfo($userInfo);

            // 就業規則取得
            $lrAllInfo = $this->getLaborRegulationsAllInfo( $employInfo['labor_regulations_id'], $targetDate );

            // 月の締日を取得
            $monthTightening = $lrAllInfo['m_work_rules_time'][0]['month_tightening'];
            $searchMonthDay = '01';
            if( $monthTightening != 31 )
            {
                $searchMonthDay = $monthTightening + 1;
            }

            // 月の締日を見て、該当する月を設定する
            $targetDate = $selectDate;
            if( $monthTightening >= 16 && $monthTightening != 31 )
            {
                // 16日以降は、前月の扱いとする
                $targetDate = date('Y/m/01', strtotime( "-1 month", strtotime($targetDate) ) );
            }

            $earlierMonth = date('Y/m/'. $searchMonthDay , strtotime($targetDate));
            $nextMonth = date('Y/m/' . $searchMonthDay , strtotime( "+1 month", strtotime($targetDate) ) );

            $isEnrollment = $this->isEnrollment($userID, $earlierMonth);
            if( !$isEnrollment )
            {
                $isEnrollment = $this->isEnrollment($userID, $nextMonth);
            }

            $searchArray = array();

            $sql  = $this->creatSelectSQL(false);
            $sql .= " WHERE to_date(:earlierMonth, 'yyyy/MM/DD' ) <= dayList.day AND  dayList.day < to_date(:nextMonth, 'yyyy/MM/DD' ) ORDER BY date, var.attendance_time, var.shift_attendance_time ";

            $searchArray = array( 
                                    ':user_id' => $userID, 
                                    ':earlierMonth' => $earlierMonth, 
                                    ':nextMonth' => $nextMonth,
                                );
            $result = $DBA->executeSQL($sql, $searchArray);

            $attendanceCorList = array();
            if( $result === false )
            {
                $Log->trace("END getMonthListData");
                return $attendanceCorList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $attendanceCorList, $data);
            }

            $Log->trace("END getMonthListData");
            return $attendanceCorList;
        }

        /**
         * 勤怠修正マスタ一覧取得(対象日一覧)SQL文作成
         * @param    $organizationID    組織ID
         * @param    $selectDate        対象日
         * @return   $attendanceCorList
         */
        private function getTargetDayBulk($organizationID, $selectDate)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getTargetDayBulk");

            $searchArray = array();

            $sql  = $this->creatSelectSQL(true);
            $sql .= " WHERE vu.organization_id = :organization_id  AND vu.hire_date <= :date AND ( :date <= vu.leaving_date OR vu.leaving_date IS NULL ) "
                 .  "       AND vu.eff_code <> '適用外' AND vu.eff_code <> '適用予定' AND vu.eff_code <> '削除' "
                 .  " ORDER BY vu.p_disp_order, vu.employees_no, var.attendance_time, var.shift_attendance_time ";

            $searchArray = array( 
                                    ':organization_id' => $organizationID, 
                                    ':date' => $selectDate, 
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
         * 勤怠処理の取得SELECT文作成
         * @return   select文
         */
        private function creatSelectSQL( $isTargetDayBulk )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSelectSQL");

            $sql = ' SELECT DISTINCT var.attendance_id, var.update_time ';
            
            // 対象日一括修正
            if( $isTargetDayBulk )
            {
                $sql .= ' , vu.user_id, vu.user_name , vu.abbreviated_name , var.date , vu.p_disp_order, vu.employees_no ';
            }
            else
            {
                $sql .= ' , var.user_id, var.user_name , dayList.day as date ';
            }
            
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
                 .  ' , var.shift_id, var.shift_attendance_time, var.shift_taikin_time, var.shift_break_time, var.is_holiday, var.shift_is_holiday '
                 .  ' , var.shift_travel_time, var.shift_working_time, var.shift_overtime, var.shift_night_working_time'
                 .  ' , var.rough_estimate, var.shift_rough_estimate, var.break_time, var.approval, var.organization_id, var.labor_regulations_id '
                 .  ' FROM v_attendance_record var ';

            // 対象日一括修正
            if( $isTargetDayBulk )
            {
                $sql .= "      RIGHT OUTER JOIN v_user vu ON vu.eff_code = '適用中' AND var.date = :date AND vu.user_id = var.user_id AND var.is_del = 0 ";
            }
            else
            {
                $sql .= "      RIGHT OUTER JOIN ( SELECT to_date(:earlierMonth, 'yyyy/MM/DD' ) + arr.i as day FROM generate_series( 0, 62 ) AS arr( i ) ) dayList ON "
                     .  '        dayList.day = date  AND is_del = 0 AND user_id = :user_id';
            }

            $Log->trace("END creatSelectSQL");

            return $sql;
        }
        
        /**
         * セキュリティID取得SQL文作成
         * @param    $sendingUserID
         * @return   $securityID
         */
        private function getSecurityId($sendingUserID)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSecurityId");

            $searchArray = array();
            
            $sql = ' SELECT vu.user_id, vu.security_id '
                 . ' FROM v_user vu '
                 . ' INNER JOIN m_security ms ON vu.security_id = ms.security_id '
                 . ' WHERE vu.user_id = :user_id ';
            $searchArray = array( ':user_id' => $sendingUserID, );
            $result = $DBA->executeSQL($sql, $searchArray);

            $securityID = "";
            if( $result === false )
            {
                $Log->trace("END getSecurityId");
                return $securityID;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $securityID = $data['security_id'];
            }

            $Log->trace("END getSecurityId");
            return $securityID;
        }
        
        /**
         * 表示項目ID取得SQL文作成
         * @param    $sendingUserID
         * @param    $securityID
         * @return   $userName
         */
        private function getDisplayItemId($sendingUserID, $securityID)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getDisplayItemId");

            $searchArray = array();
            
            $sql = ' SELECT vu.user_id, vu.security_id, ms.display_item_id '
                 . ' FROM v_user vu '
                 . ' INNER JOIN m_security ms ON vu.security_id = ms.security_id '
                 . ' INNER JOIN m_display_item_detail md ON ms.display_item_id = md.display_item_id '
                 . ' WHERE vu.user_id = :user_id '
                 . ' AND vu.security_id = :security_id ';
            $searchArray = array(
                                  ':user_id' => $sendingUserID, 
                                  ':security_id' => $securityID, 
                                 );
            $result = $DBA->executeSQL($sql, $searchArray);
            
            $displayItemID = 0;
            if( $result === false )
            {
                $Log->trace("END getDisplayItemId");
                return $displayItemID;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $displayItemID = $data['display_item_id'];
            }

            $Log->trace("END getDisplayItemId");
            return $displayItemID;
        }
        
        /**
         * 出力項目ID取得SQL文作成
         * @param    $sendingUserID
         * @param    $securityID
         * @param    $displayItemID
         * @return   $outputItemID
         */
        private function getOutputItemId($sendingUserID, $securityID, $displayItemID)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getOutputItemId");

            $searchArray = array();
            
            $sql = ' SELECT vu.user_id, vu.security_id, ms.display_item_id, md.output_type_id'
                 . ' FROM v_user vu '
                 . ' INNER JOIN m_security ms ON vu.security_id = ms.security_id '
                 . ' INNER JOIN m_display_item_detail md ON ms.display_item_id = md.display_item_id '
                 . ' WHERE vu.user_id = :user_id '
                 . ' AND md.display_item_id = :display_item_id AND vu.security_id = :security_id AND md.output_item_id = :output_item_id ';
            $searchArray = array(
                                     ':user_id' => $sendingUserID,
                                     ':security_id' => $securityID,
                                     ':display_item_id' => $displayItemID,
                                 );
            $result = $DBA->executeSQL($sql, $searchArray);

            $outputItemID = "";
            if( $result === false )
            {
                $Log->trace("END getOutputItemId");
                return $outputItemID;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $outputItemID = $data['output_type_id'];
            }

            $Log->trace("END getOutputItemId");
            return $outputItemID;
        }
        
        /**
         * 勤怠テーブルで、使用済みでないシフトIDを取得する
         * @param    $shiftID        ユーザID
         * @return   未使用シフトID
         */
        private function isShiftID( $shiftID )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START isShiftID");

            $sql = ' SELECT attendance_id FROM t_attendance WHERE shift_id = :shift_id AND is_del = 0  ';

            $parameters = array( ':shift_id' => $shiftID, );

            $result = $DBA->executeSQL($sql, $parameters);

            if( $result === false )
            {
                $Log->trace("END isShiftID");
                return 0;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( !empty( $data['attendance_id'] ) )
                {
                    $Log->trace("END isShiftID");
                    return 0;
                }
            }

            $Log->trace("END isShiftID");

            return $shiftID;
        }
        
    }
?>
