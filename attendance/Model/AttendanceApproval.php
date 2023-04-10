<?php
    /**
     * @file      勤怠承認マスタ
     * @author    USE R.dendo
     * @date      2016/07/14
     * @version   1.00
     * @note      勤怠承認マスタテーブルの管理を行う
     */

    // CheckAlert.phpを読み込む
    require './Model/CheckAlert.php';
    // WorkingTimeRegistration.phpを読み込む
    require './Model/WorkingTimeRegistration.php';

    /**
     * 勤怠承認マスタクラス
     * @note   勤怠承認マスタテーブルの管理を行うの初期設定を行う
     */
    class AttendanceApproval extends CheckAlert
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
         * 個人日別勤怠承認画面一覧表
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
         * 割増手当期間データ取得
         * @param    $allowanceOrganID  取得対象の組織ID
         * @param    $sendingDate       表示対象の日付(例：YYYY/MM/DD)
         * @param    $attendanceFlag    取得対象フラグ 1：日単位 2：月単位 3：対象日一括
         * @return   成功時：割増手当期間リスト  失敗時：空のリスト
         */
        public function getPremiumAllowanceList( $allowanceOrganID, $sendingDate, $attendanceFlag )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getPremiumAllowanceList");

            // 割増手当期間を取得する範囲を指定する
            $startDate = $sendingDate;
            $endDate   = $sendingDate;

            // 月単位の場合
            if( $attendanceFlag == 2 )
            {
                // 前後一ヶ月分のデータを取得する
                $startDate = date('Y/m/01', strtotime( "-1 month", strtotime($sendingDate) ) );
                $endDate   = date('Y/m/01', strtotime( "+2 month", strtotime($sendingDate) ) );
            }

            // 空の返り値を作成
            $ret = array();

            // ユーザ情報を取得する
            $sql = " SELECT mcd.date, ma.allowance_name "
                 . " FROM m_organization_calendar moc   "
                 . "      INNER JOIN m_calendar_detail mcd ON moc.organization_calendar_id = mcd.organization_calendar_id "
                 . "      INNER JOIN m_allowance ma ON ma.allowance_id = mcd.allowance_id "
                 . " WHERE moc.organization_id = :organization_id AND :startDate <= mcd.date AND mcd.date <= :endDate ";

            $parameters = array( 
                                    ':organization_id' => $allowanceOrganID, 
                                    ':startDate'       => $startDate, 
                                    ':endDate'         => $endDate, 
                               );

            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END getPremiumAllowanceList");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $ret, $data );
            }

            $Log->trace("END getPremiumAllowanceList");

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
         * 勤怠マスタデータ登録
         * @param    $postArray     登録データ
         * @return   SQLの実行結果
         */
        public function addNewData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
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
         * 1ユーザの勤怠情報の締めを行う
         * @param    $userID         ユーザID
         * @param    $date           勤怠承認日
         * @return   SQLの実行結果
         */
        public function attendanceDeadline( $userID, $date )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START attendanceDeadline");

            if($DBA->beginTransaction())
            {
                $ret = "";
                
                // ユーザ情報を取得
                $userInfo = $this->getUserInfo( $userID, $date );
                // 就業規則情報を取得
                $employInfo = $this->getUserEmploymentInfo($userInfo);

                // 就業規則取得
                $lrAllInfo = $this->getLaborRegulationsAllInfo( $employInfo['labor_regulations_id'], $date );
                
                // 月初を設定
                $firstDate   = date('Y-m-d', strtotime('first day of ' . $date));
                $closingDate = date('Y-m', strtotime('last day of ' . $date));
                // 検索用に来月初めを取得
                $searchDate  = date('Y-m-d', strtotime(' + 1 month ' . $firstDate));

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
                        $closingDate = date('Y-m', strtotime('-1 month' . $date));
                        $searchDate = date('Y-m', strtotime('+1 month' . $firstDate));
                        $searchDate = substr( $searchDate, 0, 7 );
                    }
                    else
                    {
                        // 当月
                        $searchDate = date('Y-m', strtotime('+1 month' . $firstDate));
                        $searchDate = substr( $searchDate, 0, 7 );
                        $firstDate = substr( $date, 0, 7 );
                    }
                    
                    $firstDate .= '-' . $setDay;
                    $searchDate .= '-' . $setDay;
                    
                    // /を-に変換する
                    $firstDate = str_replace( '/', '-', $firstDate );
                    $searchDate = str_replace( '/', '-', $searchDate );
                }

                // 勤怠〆情報削除
                $ret = $this->delAttendanceDeadline( $userID, $closingDate );
                if( $ret !== "MSG_BASE_0000" )
                {
                    $DBA->rollBack();
                    $errMsg = "勤怠〆テーブルの登録更新処理にエラーが生じました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END attendanceDeadline");
                    return $ret;
                }

                // ユーザの対象月の勤怠情報を取得
                $attendanceInfo = $this->getApprovedAttendanceInfo( $userID, $firstDate, $searchDate );
                // 勤怠〆登録データ作成
                $regData = $this->creatRegistrationData( $attendanceInfo, $lrAllInfo['m_work_rules_allowance'][0]['fixed_overtime_time'], $userID, $closingDate );

                // 勤怠締めデータの登録
                $ret = $this->addAttendanceDeadline( $regData );
                if( $ret !== "MSG_BASE_0000" )
                {
                    $DBA->rollBack();
                    $errMsg = "勤怠〆テーブルの登録更新処理にエラーが生じました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END attendanceDeadline");
                    return $ret;
                }

                // 休日出勤テーブル更新
                $holidayWorkList = $this->creatHolidayWork($attendanceInfo, $userID, $closingDate);
                foreach( $holidayWorkList as $holidayWork )
                {
                    // 勤怠締めデータの登録
                    $ret = $this->addHolidayWork( $holidayWork );
                    if( $ret !== "MSG_BASE_0000" )
                    {
                        $DBA->rollBack();
                        $errMsg = "休日出勤テーブルの登録更新処理にエラーが生じました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END attendanceDeadline");
                        return $ret;
                    }
                }

                // 手当テーブル更新
                $allowanceList = $this->creatAllowance($attendanceInfo, $userID, $closingDate);

                foreach( $allowanceList as $allowance )
                {
                    // 勤怠締めデータの登録
                    $ret = $this->addAllowance( $allowance );
                    if( $ret !== "MSG_BASE_0000" )
                    {
                        $DBA->rollBack();
                        $errMsg = "手当締めテーブルの登録更新処理にエラーが生じました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END attendanceDeadline");
                        return $ret;
                    }
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
                    $Log->trace("END attendanceDeadline");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "勤怠ID：" . $postarray['attendance_id']. "ユーザID：" . $postarray['user_id'] . "サーバエラー";
                $Log->fatalDetail($errMsg);
                $Log->trace("END attendanceDeadline");
                return "MSG_FW_DB_TRANSACTION_NG";
            }

            $Log->trace("END attendanceDeadline");
            return $ret;
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
         * 指定ユーザが承認可能な組織IDリストを取得する
         * @param    $userID        ユーザID
         * @return   承認可能の組織IDリスト
         */
        public function getUserApprovalList( $userID )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START getUserApprovalList");

            $sql = ' SELECT approval_id_list FROM v_user '
                 . " WHERE eff_code = '適用中' AND user_id = :user_id ";

            $parameters = array( ':user_id' => $userID, );

            $ret = array();
            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END getUserApprovalList");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $temp = str_replace( "{", "", $data['approval_id_list'] );
                $temp = str_replace( "}", "", $temp );
                $ret = explode( ',', $temp );
            }

            $Log->trace("END getUserApprovalList");

            return $ret;
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
         * 勤怠マスタ新規データ登録
         * @param    $postArray   入力パラメータ
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
            $iAttendanceTime = 0;
            $shiftInfo = $this->getShiftInfo( $userInfo, $postArray['date'], $iAttendanceTime );

            // 休日情報取得
            $isHoliday = $this->getHolidayInfo( $postArray['organizationID'], $userInfo, $shiftInfo, $employInfo, $postArray['date'] );

            $sql = 'INSERT INTO t_attendance( organization_id'
                . '                      , labor_regulations_id'
                . '                      , shift_id'
                . '                      , date'
                . '                      , user_id'
                . '                      , is_holiday'
                . '                      , approval_id '
                . '                      , approval '
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
                . '                      , :date'
                . '                      , :user_id'
                . '                      , :is_holiday'
                . '                      , :update_user_id '
                . '                      , :approval '
                . '                      , current_timestamp'
                . '                      , :registration_user_id'
                . '                      , :registration_organization'
                . '                      , current_timestamp'
                . '                      , :update_user_id'
                . '                      , :update_organization)';

            $parameters = array(
                ':organization_id'               => $postArray['organizationID'],
                ':labor_regulations_id'          => $employInfo['labor_regulations_id'],
                ':shift_id'                      => $shiftInfo['shift_id'],
                ':date'                          => $postArray['date'],
                ':user_id'                       => $postArray['userId'],
                ':is_holiday'                    => $isHoliday,
                ':approval'                      => $postArray['approval'],
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
         * @param    $postArray   
         * @return   SQLの実行結果
         */
        private function modAttendanceUpdateData( $postArray )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START modUpdateData");
            $sql = 'UPDATE t_attendance SET'
                . '   approval_id = :update_user_id'
                . ' , approval = :approval'
                . ' , update_user_id = :update_user_id'
                . ' , update_time = current_timestamp'
                . ' , update_organization = :update_organization'
                . ' WHERE attendance_id = :attendance_id AND update_time = :update_time ';

            $parameters = array(
                ':approval'                      => $postArray['approval'],
                ':attendance_id'                 => $postArray['attendanceId'],
                ':update_user_id'                => $postArray['user_id'],
                ':update_time'                   => $postArray['updateTime'],
                ':update_organization'           => $postArray['organization'],
            );

            // 更新SQLの実行
            if( !$DBA->executeSQL($sql, $parameters, true ) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3113");
                $errMsg = "勤怠ID：" . $postArray['attendanceId']. "ユーザID：" . $postArray['userId'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_BASE_0001";
            }

            $Log->trace("END modUpdateData");

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
            $sql .= ' WHERE dayList.day = :date ORDER BY embossing_attendance_time';

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
        private function getMonthListData($userID, $selectDate, &$isEnrollment )
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
            $sql .= " WHERE to_date(:earlierMonth, 'yyyy/MM/DD' ) <= dayList.day AND  dayList.day < to_date(:nextMonth, 'yyyy/MM/DD' ) ORDER BY date, var.embossing_attendance_time";

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
                $tmp = $data;
                
                // 従業員Noが取得できているか
                if( $tmp['employees_no'] == '' )
                {
                    $tmp['employees_no'] = $userInfo['employees_no'];
                    $tmp['employment_name'] = $userInfo['employment_name'];
                    $tmp['position_name'] = $userInfo['position_name'];
                    $tmp['abbreviated_name'] = $userInfo['abbreviated_name'];
                }
                
                array_push( $attendanceCorList, $tmp);
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
            $sql .= " WHERE vu.organization_id = :organization_id AND vu.hire_date <= :date AND ( :date <= vu.leaving_date OR vu.leaving_date IS NULL ) "
                 .  "       AND vu.eff_code <> '適用外' AND vu.eff_code <> '適用予定' "
                 .  " ORDER BY vu.p_disp_order, vu.employees_no, var.embossing_attendance_time ";

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

            $sql  = ' SELECT DISTINCT var.attendance_id, var.update_time '
                 .  ' , vu.user_id, vu.user_name , vu.abbreviated_name, vu.p_disp_order, vu.employees_no ';
            if( $isTargetDayBulk )
            {
                $sql .= ' , var.date ';
            }
            else
            {
                $sql .= ' , dayList.day as date ';
            }
            $sql .= ' , vu.hourly_wage, vu.position_name, vu.p_disp_order, vu.employment_name, vu.status '
                 .  ' , var.embossing_organization_name, var.organization_name, var.embossing_organization_id '
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
                 .  ' , var.total_working_time, var.overtime, var.night_working_time'
                 .  ' , var.shift_id, var.shift_attendance_time, var.shift_taikin_time, var.shift_break_time'
                 .  ' , var.shift_travel_time, var.shift_working_time, var.shift_overtime, var.shift_night_working_time, var.night_overtime '
                 .  ' , var.rough_estimate, var.shift_rough_estimate, var.break_time, var.approval, var.organization_id, var.is_holiday '
                 .  " , CASE WHEN var.absence_count is NULL THEN 0 ELSE var.absence_count END absence_count "
                 .  " , CASE WHEN var.late_time = '00:00' THEN 0 WHEN var.late_time is NULL THEN 0 ELSE 1 END late_time "
                 .  " , CASE WHEN var.leave_early_time = '00:00' THEN 0 WHEN var.leave_early_time is NULL THEN 0 ELSE 1 END leave_early_time "
                 .  ' , var.embossing_abbreviated_name, var.embossing_status, var.hourly_wage '
                 .  ' FROM v_attendance_record var '
                 .  "      RIGHT OUTER JOIN v_user vu ON vu.eff_code = '適用中' AND var.is_del = 0 AND  vu.user_id = var.user_id ";
            if( $isTargetDayBulk )
            {
                $sql .=  '   AND var.date = :date ';
            }
            else
            {
                $sql .=  "      RIGHT OUTER JOIN ( SELECT to_date(:earlierMonth, 'yyyy/MM/DD' ) + arr.i as day FROM generate_series( 0, 62 ) AS arr( i ) ) dayList ON "
                     .   '                   dayList.day = date  AND var.is_del = 0 AND var.user_id = :user_id ';
            }

            $Log->trace("END creatSelectSQL");

            return $sql;
        }

        /**
         * 勤怠修正マスタ一覧取得SQL文作成
         * @param    $userID
         * @param    $selectDate
         * @return   $userName
         */
        private function getUserName($userID, $selectDate)
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
         * 承認済み勤怠情報を取得
         * @param    $userID    ユーザID
         * @param    $sDate     勤怠集計開始日
         * @param    $eDate     勤怠集計終了日
         * @return   勤怠情報リスト
         */
        private function getApprovedAttendanceInfo( $userID, $sDate, $eDate )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getApprovedAttendanceInfo");

            $sql = ' SELECT '
                 . '     ta.attendance_id, ta.user_id, ta.date, ta.is_holiday, ta.break_time, ta.travel_time, ta.late_time, ta.leave_early_time '
                 . '   , ta.total_working_time, ta.night_working_time, ta.overtime, ta.night_overtime, ta.rough_estimate, ta.rough_estimate_hour, ta.rough_estimate_month '
                 . '   , ta.absence_count, ta.prescribed_working_days, ta.prescribed_working_hours, ta.statutory_overtime_hours, ta.nonstatutory_overtime_hours '
                 . '   , ta.nonstatutory_overtime_hours_45h, ta.nonstatutory_overtime_hours_60h, ta.statutory_overtime_hours_no_pub, ta.nonstatutory_overtime_hours_no_pub '
                 . '   , ta.nonstatutory_overtime_hours_45h_no_pub, ta.nonstatutory_overtime_hours_60h_no_pub, ta.overtime_hours_no_considered, ta.overtime_hours_no_considered_no_pub '
                 . '   , ta.approval, ta.labor_regulations_id, ts.shift_id, ts.rough_estimate as s_rough_estimate, ta.modify_count '
                 . ' FROM  t_attendance ta LEFT OUTER JOIN t_shift ts ON ta.shift_id = ts.shift_id '
                 . ' WHERE ta.user_id = :user_id AND :sDate <= ta.date AND ta.date < :eDate AND is_del = 0 '
                 . ' ORDER BY ta.date, ta.attendance_time ';

            $searchArray = array(
                                  ':user_id'  => $userID, 
                                  ':sDate'    => $sDate, 
                                  ':eDate'    => $eDate, 
                                 );
            $result = $DBA->executeSQL( $sql, $searchArray );

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getApprovedAttendanceInfo");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $ret, $data );
            }

            $Log->trace("END getApprovedAttendanceInfo");
            return $ret;
        }

        /**
         * 勤怠締め登録データの作成
         * @param    $attendanceInfo   勤怠情報
         * @param    $fixedOvertime    みなし残業時間
         * @param    $userID           ユーザID
         * @param    $endDate          締日
         * @return   勤怠締め登録情報
         */
        private function creatRegistrationData( $attendanceInfo, $fixedOvertime, $userID, $endDate )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START creatRegistrationData");

            $ret = array(
                            ':user_id'                                       => $userID,
                            ':closing_date'                                  => $endDate,
                            ':update_user_id'                                => $_SESSION["USER_ID"],
                            ':update_organization'                           => $_SESSION["ORGANIZATION_ID"],
                            ':working_time'                                  => 0,
                            ':night_working_time'                            => 0,
                            ':normal_working_time'                           => 0,
                            ':normal_overtime_hours'                         => 0,
                            ':normal_night_working_time'                     => 0,
                            ':normal_night_overtime_hours'                   => 0,
                            ':absence_count'                                 => 0,
                            ':being_late_count'                              => 0,
                            ':leave_early_count'                             => 0,
                            ':prescribed_working_days'                       => 0,
                            ':production_dates'                              => 0,
                            ':weekday_attendance_dates'                      => 0,
                            ':holiday_work_days'                             => 0,
                            ':public_holiday_attendance_dates'               => 0,
                            ':legal_holiday_work_days'                       => 0,
                            ':prescribed_working_hours'                      => 0,
                            ':weekday_working_hours'                         => 0,
                            ':weekday_prescribed_working_hours'              => 0,
                            ':weekday_overtime'                              => 0,
                            ':holiday_working_hours'                         => 0,
                            ':holiday_prescribed_working_hours'              => 0,
                            ':holiday_overtime_hours'                        => 0,
                            ':public_holiday_working_hours'                  => 0,
                            ':public_holiday_prescribed_working_hours'       => 0,
                            ':public_holiday_overtime_hours'                 => 0,
                            ':statutory_holiday_working_hours'               => 0,
                            ':statutory_holiday_prescribed_working_hours'    => 0,
                            ':statutory_holiday_overtime_hours'              => 0,
                            ':statutory_overtime_hours'                      => 0,
                            ':nonstatutory_overtime_hours'                   => 0,
                            ':nonstatutory_overtime_hours_45h'               => 0,
                            ':nonstatutory_overtime_hours_60h'               => 0,
                            ':statutory_overtime_hours_no_pub'               => 0,
                            ':nonstatutory_overtime_hours_no_pub'            => 0,
                            ':nonstatutory_overtime_hours_45h_no_pub'        => 0,
                            ':nonstatutory_overtime_hours_60h_no_pub'        => 0,
                            ':weekdays_midnight_time'                        => 0,
                            ':holiday_late_at_night_time'                    => 0,
                            ':public_holidays_late_at_night_time'            => 0,
                            ':legal_holiday_late_at_night_time'              => 0,
                            ':considered_overtime'                           => 0,
                            ':overtime_hours_no_considered'                  => 0,
                            ':overtime_hours_no_considered_no_pub'           => 0,
                            ':break_time'                                    => 0,
                            ':late_time'                                     => 0,
                            ':leave_early_time'                              => 0,
                            ':estimate_performance'                          => 0,
                            ':approximate_schedule'                          => 0,
                            ':approval'                                      => 0,
                            ':labor_regulations_id'                          => 0,
                            ':weekday_night_overtime_hours'                  => 0,
                            ':holiday_night_overtime_hours'                  => 0,
                            ':public_night_overtime_hours'                   => 0,
                            ':statutory_holiday_night_overtime_hours'        => 0,
                            ':modify_count'                                  => 0,
                            ':weekday_normal_time'                           => 0,
                            ':weekday_midnight_time_only'                    => 0,
                            ':holiday_normal_time'                           => 0,
                            ':holiday_midnight_time_only'                    => 0,
                            ':public_holiday_normal_time'                    => 0,
                            ':public_holiday_midnight_time_only'             => 0,
                            ':statutory_holiday_normal_time'                 => 0,
                            ':statutory_holiday_midnight_time_only'          => 0,
                        );

            $isApproval = 1; // 承認フラグ
            $productionDates = '';
            $laborRegulationsID = '';
            // 勤怠情報
            foreach( $attendanceInfo as $info )
            {
                // 勤怠未承認である
                if( $info['approval'] == 0 )
                {
                    // 勤怠未承認の場合、積算の対象外
                    $isApproval = 0;
                    continue;
                }

                // 実労働時間
                $ret[':working_time'] += $this->changeMinuteFromTime( $info['total_working_time'] );
                // 実深夜時間
                $ret[':night_working_time'] += $this->changeMinuteFromTime( $info['night_working_time'] );
                // 勤務日の休日情報によって、集計対象データを変更する
                if( $info['is_holiday'] == SystemParameters::$PUBLIC_HOLIDAY )
                {
                    if( 0 < $this->changeMinuteFromTime( $info['total_working_time'] ) )
                    {
                        // 公休日
                        if( $productionDates != $info['date'] )
                        {
                            // 公休日出勤日数
                            $ret[':public_holiday_attendance_dates'] += 1;
                        }
                        // 公休日労働時間
                        $ret[':public_holiday_working_hours'] += $this->changeMinuteFromTime( $info['total_working_time'] );
                        // 公休日所定内労働時間
                        $ret[':public_holiday_prescribed_working_hours'] += $info['prescribed_working_hours'];
                        // 公休日残業時間
                        $ret[':public_holiday_overtime_hours'] += $this->changeMinuteFromTime( $info['overtime'] );
                        // 公休日深夜時間
                        $ret[':public_holidays_late_at_night_time'] += $this->changeMinuteFromTime( $info['night_working_time'] );
                        // 公休日深夜残業時間
                        $ret[':public_night_overtime_hours'] += $this->changeMinuteFromTime( $info['night_overtime'] );

                        // 公休日普通
                        $ret[':public_holiday_normal_time'] += $this->changeMinuteFromTime( $info['total_working_time'] ) - $this->changeMinuteFromTime( $info['night_working_time'] ) - $this->changeMinuteFromTime( $info['overtime'] ) + $this->changeMinuteFromTime( $info['night_overtime'] );
                        // 公休日深夜
                        $ret[':public_holiday_midnight_time_only'] += $this->changeMinuteFromTime( $info['night_working_time'] ) - $this->changeMinuteFromTime( $info['night_overtime'] );
                        
                    }
                }
                else if( $info['is_holiday'] == SystemParameters::$STATUTORY_HOLIDAY )
                {
                    if( 0 < $this->changeMinuteFromTime( $info['total_working_time'] ) )
                    {
                        // 法定休日
                        if( $productionDates != $info['date'] )
                        {
                            // 法定休日出勤日数
                            $ret[':legal_holiday_work_days'] += 1;
                        }
                        // 法定休日労働時間
                        $ret[':statutory_holiday_working_hours'] += $this->changeMinuteFromTime( $info['total_working_time'] );
                        // 法定休日所定内労働時間
                        $ret[':statutory_holiday_prescribed_working_hours'] += $info['prescribed_working_hours'];
                        // 法定休日残業時間
                        $ret[':statutory_holiday_overtime_hours'] += $this->changeMinuteFromTime( $info['overtime'] );
                        // 法定休日深夜時間
                        $ret[':legal_holiday_late_at_night_time'] += $this->changeMinuteFromTime( $info['night_working_time'] );
                        // 法定休日深夜残業時間
                        $ret[':statutory_holiday_night_overtime_hours'] += $this->changeMinuteFromTime( $info['night_overtime'] );
                        
                        // 法定休日普通
                        $ret[':statutory_holiday_normal_time'] += $this->changeMinuteFromTime( $info['total_working_time'] ) - $this->changeMinuteFromTime( $info['night_working_time'] ) - $this->changeMinuteFromTime( $info['overtime'] ) + $this->changeMinuteFromTime( $info['night_overtime'] );
                        // 法定休日深夜
                        $ret[':statutory_holiday_midnight_time_only'] += $this->changeMinuteFromTime( $info['night_working_time'] ) - $this->changeMinuteFromTime( $info['night_overtime'] );
                        
                    }
                }
                else
                {
                    if( 0 < $this->changeMinuteFromTime( $info['total_working_time'] ) )
                    {
                        // 普通労働時間(公休日/法定休日以外、休日マスタ設定の休日は含まれる)
                        $ret[':normal_working_time']   += ( $this->changeMinuteFromTime( $info['total_working_time'] ) - $this->changeMinuteFromTime( $info['overtime'] ) );
                        $ret[':normal_overtime_hours'] += ( $this->changeMinuteFromTime( $info['overtime'] ) - $this->changeMinuteFromTime( $info['night_overtime'] ) );
                        $ret[':normal_night_working_time']   += $this->changeMinuteFromTime( $info['night_working_time'] );
                        $ret[':normal_night_overtime_hours'] += $this->changeMinuteFromTime( $info['night_overtime'] );
                    }
                }
                
                // 平日の出勤日であるか
                if( $info['is_holiday'] == 0 || $attendanceInfo['is_holiday'] != SystemParameters::$ATTENDANCE || $attendanceInfo['is_holiday'] != SystemParameters::$ABSENCE  )
                {
                    if( 0 < $this->changeMinuteFromTime( $info['total_working_time'] ) )
                    {
                        // 平日
                        if( $productionDates != $info['date'] )
                        {
                            // 平日出勤日数
                            $ret[':weekday_attendance_dates'] += 1;
                        }
                        // 平日労働時間
                        $ret[':weekday_working_hours'] += $this->changeMinuteFromTime( $info['total_working_time'] );
                        // 平日所定内労働時間
                        $ret[':weekday_prescribed_working_hours'] += $info['prescribed_working_hours'];
                        // 平日残業時間
                        $ret[':weekday_overtime'] += $this->changeMinuteFromTime( $info['overtime'] );
                        // 平日深夜時間
                        $ret[':weekdays_midnight_time'] += $this->changeMinuteFromTime( $info['night_working_time'] );
                        // 平日深夜残業時間
                        $ret[':weekday_night_overtime_hours'] += $this->changeMinuteFromTime( $info['night_overtime'] );
                        
                        // 平日普通
                        $ret[':weekday_normal_time'] += $this->changeMinuteFromTime( $info['total_working_time'] ) - $this->changeMinuteFromTime( $info['night_working_time'] ) - $this->changeMinuteFromTime( $info['overtime'] ) + $this->changeMinuteFromTime( $info['night_overtime'] );
                        // 平日深夜
                        $ret[':weekday_midnight_time_only'] += $this->changeMinuteFromTime( $info['night_working_time'] ) - $this->changeMinuteFromTime( $info['night_overtime'] );
                        
                    }
                }
                else
                {
                    // 休日(公休日/法定休日、休日マスタ設定の休日を含む)
                    if( 0 < $this->changeMinuteFromTime( $info['total_working_time'] ) )
                    {
                        if( $productionDates != $info['date'] )
                        {
                            // 休日出勤日数
                            $ret[':holiday_work_days'] += 1;
                        }
                        // 休日労働時間
                        $ret[':holiday_working_hours'] += $this->changeMinuteFromTime( $info['total_working_time'] );
                        // 休日所定内労働時間
                        $ret[':holiday_prescribed_working_hours'] += $info['prescribed_working_hours'];
                        // 休日残業時間
                        $ret[':holiday_overtime_hours'] += $this->changeMinuteFromTime( $info['overtime'] );
                        // 休日深夜時間
                        $ret[':holiday_late_at_night_time'] += $this->changeMinuteFromTime( $info['night_working_time'] );
                        // 休日深夜残業時間
                        $ret[':holiday_night_overtime_hours'] += $this->changeMinuteFromTime( $info['night_overtime'] );
                        
                        // 休日普通
                        $ret[':holiday_normal_time'] += $this->changeMinuteFromTime( $info['total_working_time'] ) - $this->changeMinuteFromTime( $info['night_working_time'] ) - $this->changeMinuteFromTime( $info['overtime'] ) + $this->changeMinuteFromTime( $info['night_overtime'] );
                        // 休日深夜
                        $ret[':holiday_midnight_time_only'] += $this->changeMinuteFromTime( $info['night_working_time'] ) - $this->changeMinuteFromTime( $info['night_overtime'] );
                        
                    }
                }
                
                // 欠勤回数
                $ret[':absence_count'] += $info['absence_count'];
                // 遅刻回数
                $cnt = 1;
                if( is_null( $info['late_time'] ) || $info['late_time'] == '00:00:00' )
                {
                    $cnt = 0;
                }
                $ret[':being_late_count'] += $cnt;
                // 早退回数
                $cnt = 1;
                if( is_null( $info['leave_early_time'] ) || $info['leave_early_time'] == '00:00:00' )
                {
                    $cnt = 0;
                }
                $ret[':leave_early_count'] += $cnt;
                
                // 所定労働日数
                $ret[':prescribed_working_days'] += $info['prescribed_working_days'];
                // 実働日数
                if( $productionDates != $info['date'] )
                {
                    $productionDates = $info['date'];
                    if( 0 < $this->changeMinuteFromTime( $info['total_working_time'] ) )
                    {
                        // 労働時間あり
                        $ret[':production_dates'] += 1;
                    }
                }
                // 所定内労働時間
                $ret[':prescribed_working_hours'] += $info['prescribed_working_hours'];
                // 法定内残業時間(公休日含む)
                $ret[':statutory_overtime_hours'] += $info['statutory_overtime_hours'];
                // 法定外残業時間 (公休日含む)(45H以下)
                $ret[':nonstatutory_overtime_hours'] += $info['nonstatutory_overtime_hours'];
                // 法定外残業時間(公休日含む)(45H超え60H以下)
                $ret[':nonstatutory_overtime_hours_45h'] += $info['nonstatutory_overtime_hours_45h'];
                // 法定外残業時間(公休日含む)(60H超え)
                $ret[':nonstatutory_overtime_hours_60h'] += $info['nonstatutory_overtime_hours_60h'];
                // 法定内残業時間(公休日含まない)
                $ret[':statutory_overtime_hours_no_pub'] += $info['statutory_overtime_hours_no_pub'];
                // 法定外残業時間 (公休日含まない)(45H以下)
                $ret[':nonstatutory_overtime_hours_no_pub'] += $info['nonstatutory_overtime_hours_no_pub'];
                // 法定外残業時間(公休日含まない)(45H超え60H以下)
                $ret[':nonstatutory_overtime_hours_45h_no_pub'] += $info['nonstatutory_overtime_hours_45h_no_pub'];
                // 法定外残業時間(公休日含まない)(60H超え)
                $ret[':nonstatutory_overtime_hours_60h_no_pub'] += $info['nonstatutory_overtime_hours_60h_no_pub'];
                // 残業時間(みなし除く)(公休日含む)
                $ret[':overtime_hours_no_considered'] += $info['overtime_hours_no_considered'];
                // 残業時間(みなし除く)(公休日含まない)
                $ret[':overtime_hours_no_considered_no_pub'] += $info['overtime_hours_no_considered_no_pub'];
                // 休憩時間
                $ret[':break_time'] += $this->changeMinuteFromTime( $info['break_time'] );
                // 遅刻時間
                $ret[':late_time'] += $this->changeMinuteFromTime( $info['late_time'] );
                // 早退時間
                $ret[':leave_early_time'] += $this->changeMinuteFromTime( $info['leave_early_time'] );
                // 概算給与(実績)
                $ret[':estimate_performance'] += $info['rough_estimate'];
                // 概算給与(予定)
                $ret[':approximate_schedule'] += $info['s_rough_estimate'];
                // 修正回数
                $ret[':modify_count'] += $info['modify_count'];
                // 就業規則ID(最終レコードの値を設定)
                $ret[':labor_regulations_id'] = $info['labor_regulations_id'];

            }

            // TWレストランツ用対応追加(暫定対応) 0915 millionet oota
            if( $_SESSION['SCHEMA'] == 'twrestaurant'){
                // 就業規則IDで社員かアルバイトか判断
                if($ret[':labor_regulations_id'] == 1){
                    // ----------------------社員----------------------
                    // 所定労働日数 →　歴日 - 9 2月は-8 2月以外は-9
                    // ※count($attendanceInfo)だとレコード数で影響が出る。。。
                    $ret[':prescribed_working_days'] = count($attendanceInfo) - 9;
                    
                    // 法定休日出勤日数　→　公休日数 = 歴日 - 出勤日数
                    $ret[':legal_holiday_work_days'] = count($attendanceInfo) - $ret[':production_dates'];

                    // 公休日出勤日数　→　公休出勤 = 9 - 公休日数
                    $ret[':public_holiday_attendance_dates'] = 9 - $ret[':legal_holiday_work_days'];
                    
                    // 実働日数　→　所定日数と同じにする ※公休日数の計算がおわった後に行うこと
                    $ret[':production_dates'] = count($attendanceInfo) - 9;
                    
                }else{
                    // ----------------------アルバイト----------------------
                    // 所定労働日数 →　所定日数 = 出勤日数
                    $ret[':prescribed_working_days'] = $ret[':production_dates'];
                    
                    // 実働日数　→　出勤日数 上部で設定した数値でよい
                    
                    // 法定休日出勤日数　→　公休日数 なし
                    $ret[':legal_holiday_work_days'] = 0;
                    
                    // 公休日出勤日数　→　公休出勤 なし
                    $ret[':public_holiday_attendance_dates'] = 0;
                }
            }
                    
            // みなし残業時間
            $ret[':considered_overtime'] = $fixedOvertime;

            // 承認フラグ
            $ret[':approval'] = $isApproval;

            $Log->trace("END creatRegistrationData");
            return $ret;
        }

        /**
         * 休日出勤テーブル登録データの作成
         * @param    $attendanceInfo   勤怠情報
         * @param    $userID           ユーザID
         * @param    $endDate          締日
         * @return   勤怠締め登録情報
         */
        private function creatHolidayWork( $attendanceInfo, $userID, $endDate )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START creatHolidayWork");

            $org = array(
                            ':user_id'                              => $userID,
                            ':closing_date'                         => $endDate,
                            ':holiday_id'                           => 0,
                            ':holiday_name_id'                      => 0,
                            ':holiday_get_dates'                    => 0,
                            ':holiday_work_days'                    => 0,
                            ':holiday_working_hours'                => 0,
                            ':holiday_prescribed_working_hours'     => 0,
                            ':holiday_overtime_hours'               => 0,
                            ':holiday_late_night_time'              => 0,
                            ':holiday_midnight_overtime_hours'      => 0,
                        );

            $ret = array();
            $productionDates = '';
            // 勤怠情報
            foreach( $attendanceInfo as $info )
            {
                // 勤怠未承認である
                if( $info['approval'] == 0 )
                {
                    // 勤怠未承認の場合、積算の対象外
                    continue;
                }

                // 休日マスタ定義の出勤日であるか
                if( $info['is_holiday'] != 0 )
                {
                    if( empty($ret[$info['is_holiday']]) )
                    {
                        $ret[$info['is_holiday']] = $org;
                        $ret[$info['is_holiday']][':holiday_id'] = $info['is_holiday'];
                        $ret[$info['is_holiday']][':holiday_name_id'] = $this->getHolidayName( $info['is_holiday'] );
                    }
                    
                    // 勤務実績があるか
                    if( 0 < $this->changeMinuteFromTime( $info['total_working_time'] ) && !empty( $info['attendance_time'] ) )
                    {
                        if( $productionDates != $info['date'] )
                        {
                            // 休日出勤日数
                            $ret[$info['is_holiday']][':holiday_work_days'] += 1;
                        }
                        // 休日労働時間
                        $ret[$info['is_holiday']][':holiday_working_hours'] += $this->changeMinuteFromTime( $info['total_working_time'] );
                        // 休日所定内労働時間
                        $ret[$info['is_holiday']][':holiday_prescribed_working_hours'] += $info['prescribed_working_hours'];
                        // 休日残業時間
                        $ret[$info['is_holiday']][':holiday_overtime_hours'] += $this->changeMinuteFromTime( $info['overtime'] );
                        // 休日深夜時間
                        $ret[$info['is_holiday']][':holiday_late_night_time'] += $this->changeMinuteFromTime( $info['night_working_time'] );
                        // 休日深夜残業時間
                        $ret[$info['is_holiday']][':holiday_midnight_overtime_hours'] += $this->changeMinuteFromTime( $info['night_overtime'] );
                    }
                    else
                    {
                        if( $productionDates != $info['date'] )
                        {
                            // 休日取得日数
                            $ret[$info['is_holiday']][':holiday_get_dates'] += 1;
                        }
                    }
                    $productionDates = $info['date'];
                }

            }

            $Log->trace("END creatHolidayWork");
            return $ret;
        }

        /**
         * 手当締めテーブル登録データの作成
         * @param    $attendanceInfo   勤怠情報
         * @param    $userID           ユーザID
         * @param    $endDate          締日
         * @return   手当締め登録情報
         */
        private function creatAllowance( $attendanceInfo, $userID, $endDate )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START creatAllowance");

            // 手当データを取得する
            $sql = ' SELECT allowance_id, count( allowance_id ) as allowance_num, SUM( allowance_amount ) as allowance_amount '
                 . ' FROM   t_allowance '
                 . ' WHERE  user_id = :user_id AND attendance_id IN ( ';
            foreach( $attendanceInfo as $info )
            {
                // 勤怠未承認である
                if( $info['approval'] == 0 )
                {
                    // 勤怠未承認の場合、積算の対象外
                    continue;
                }
                $sql .= $info['attendance_id'] . ",";
            }
            // 末尾のカンマ削除
            $sql = substr( $sql, 0, -1 );
            $sql .= ' ) GROUP BY user_id, allowance_id ';

            $searchArray = array(
                                  ':user_id'  => $userID, 
                                 );
            $result = $DBA->executeSQL( $sql, $searchArray );

            $allowanceList = array();
            if( $result === false )
            {
                $Log->trace("END creatAllowance");
                return $allowanceList;
            }

            $addData = array(
                            ':user_id'          => $userID,
                            ':closing_date'     => $endDate,
                            ':allowance_id'     => 0,
                            ':allowance_num'    => 0,
                            ':allowance_amount' => 0,
                        );
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $addData[':allowance_id']     = $data['allowance_id'];
                $addData[':allowance_num']    = $data['allowance_num'];
                $addData[':allowance_amount'] = $data['allowance_amount'];
                
                array_push( $allowanceList, $addData );
            }

            $Log->trace("END creatAllowance");

            return $allowanceList;
        }

        /**
         * 手当締めテーブル登録
         * @param    $parameters   登録パラメータ
         * @return   SQLの実行結果
         */
        private function addAllowance( $parameters )
        {
            global $DBA,$Log; // グローバル変数宣言
            $Log->trace("START addAllowance");
            
            $sql = ' INSERT INTO t_allowance_tighten( user_id, closing_date, allowance_id, allowance_num, allowance_amount )  '
                 . '        VALUES ( :user_id, :closing_date, :allowance_id, :allowance_num, :allowance_amount )';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3129");
                $errMsg = "ユーザID：" . $parameters[':user_id'] . "締日：" . $parameters[':closing_date'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3129";
            }

            $Log->trace("END addAllowance");

            return "MSG_BASE_0000";
        }

        /**
         * 勤怠〆テーブル群の削除
         * @param    $userID           ユーザID
         * @param    $endDate          締日
         * @return   SQLの実行結果
         */
        private function delAttendanceDeadline( $userID, $endDate )
        {
            global $DBA,$Log; // グローバル変数宣言
            $Log->trace("START delAttendanceDeadline");
            
            $parameters = array(
                      ':user_id'        => $userID, 
                      ':closing_date'   => $endDate, 
                     );

            $sql = ' DELETE FROM t_allowance_tighten WHERE user_id = :user_id AND closing_date = :closing_date ';
            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3125");
                $errMsg = "ユーザID：" . $userID . "締日：" . $endDate . "の登録失敗";
                $Log->warnDetail($errMsg);
                $Log->trace("END delAttendanceDeadline");
                return "MSG_ERR_3125";
            }

            $sql = ' DELETE FROM t_holiday WHERE user_id = :user_id AND closing_date = :closing_date ';
            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3126");
                $errMsg = "ユーザID：" . $userID . "締日：" . $endDate . "の登録失敗";
                $Log->warnDetail($errMsg);
                $Log->trace("END delAttendanceDeadline");
                return "MSG_ERR_3126";
            }

            $sql = ' DELETE FROM t_attendance_tighten WHERE user_id = :user_id AND closing_date = :closing_date ';
            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3124");
                $errMsg = "ユーザID：" . $userID . "締日：" . $endDate . "の登録失敗";
                $Log->warnDetail($errMsg);
                $Log->trace("END delAttendanceDeadline");
                return "MSG_ERR_3124";
            }

            $Log->trace("END delAttendanceDeadline");

            return "MSG_BASE_0000";
        }

        /**
         * 勤怠〆テーブル登録
         * @param    $parameters   登録パラメータ
         * @return   SQLの実行結果
         */
        private function addAttendanceDeadline( $parameters )
        {
            global $DBA,$Log; // グローバル変数宣言
            $Log->trace("START addAttendanceDeadline");
            
            $sql = ' INSERT INTO t_attendance_tighten( '
                 . '      user_id, closing_date, working_time, night_working_time, normal_working_time, normal_overtime_hours '
                 . '    , normal_night_working_time, normal_night_overtime_hours, absence_count, being_late_count, leave_early_count '
                 . '    , prescribed_working_days, production_dates, weekday_attendance_dates, holiday_work_days, public_holiday_attendance_dates '
                 . '    , legal_holiday_work_days, prescribed_working_hours, weekday_working_hours, weekday_prescribed_working_hours, weekday_overtime '
                 . '    , holiday_working_hours, holiday_prescribed_working_hours, holiday_overtime_hours, public_holiday_working_hours '
                 . '    , public_holiday_prescribed_working_hours, public_holiday_overtime_hours, statutory_holiday_working_hours, statutory_holiday_prescribed_working_hours '
                 . '    , statutory_holiday_overtime_hours, statutory_overtime_hours, nonstatutory_overtime_hours, nonstatutory_overtime_hours_45h, nonstatutory_overtime_hours_60h '
                 . '    , statutory_overtime_hours_no_pub, nonstatutory_overtime_hours_no_pub, nonstatutory_overtime_hours_45h_no_pub, nonstatutory_overtime_hours_60h_no_pub '
                 . '    , weekdays_midnight_time, holiday_late_at_night_time, public_holidays_late_at_night_time, legal_holiday_late_at_night_time, considered_overtime '
                 . '    , overtime_hours_no_considered, overtime_hours_no_considered_no_pub, break_time, late_time, leave_early_time, estimate_performance, approximate_schedule '
                 . '    , approval, labor_regulations_id, registration_time, registration_user_id, registration_organization, update_time, update_user_id, update_organization '
                 . '    , weekday_night_overtime_hours, holiday_night_overtime_hours, public_night_overtime_hours, statutory_holiday_night_overtime_hours, modify_count '
                 . '    , weekday_normal_time, weekday_midnight_time_only, holiday_normal_time, holiday_midnight_time_only '
                 . '    , public_holiday_normal_time, public_holiday_midnight_time_only, statutory_holiday_normal_time, statutory_holiday_midnight_time_only '
                 . '  ) VALUES (  '
                 . '      :user_id, :closing_date, :working_time, :night_working_time, :normal_working_time, :normal_overtime_hours, :normal_night_working_time '
                 . '    , :normal_night_overtime_hours, :absence_count, :being_late_count, :leave_early_count, :prescribed_working_days, :production_dates, :weekday_attendance_dates '
                 . '    , :holiday_work_days, :public_holiday_attendance_dates, :legal_holiday_work_days, :prescribed_working_hours, :weekday_working_hours, :weekday_prescribed_working_hours '
                 . '    , :weekday_overtime, :holiday_working_hours, :holiday_prescribed_working_hours, :holiday_overtime_hours, :public_holiday_working_hours, :public_holiday_prescribed_working_hours '
                 . '    , :public_holiday_overtime_hours, :statutory_holiday_working_hours, :statutory_holiday_prescribed_working_hours, :statutory_holiday_overtime_hours, :statutory_overtime_hours '
                 . '    , :nonstatutory_overtime_hours, :nonstatutory_overtime_hours_45h, :nonstatutory_overtime_hours_60h, :statutory_overtime_hours_no_pub, :nonstatutory_overtime_hours_no_pub '
                 . '    , :nonstatutory_overtime_hours_45h_no_pub, :nonstatutory_overtime_hours_60h_no_pub, :weekdays_midnight_time, :holiday_late_at_night_time, :public_holidays_late_at_night_time '
                 . '    , :legal_holiday_late_at_night_time, :considered_overtime, :overtime_hours_no_considered, :overtime_hours_no_considered_no_pub, :break_time, :late_time, :leave_early_time '
                 . '    , :estimate_performance, :approximate_schedule, :approval, :labor_regulations_id, current_timestamp, :update_user_id, :update_organization, current_timestamp, :update_user_id, :update_organization '
                 . '    , :weekday_night_overtime_hours, :holiday_night_overtime_hours, :public_night_overtime_hours, :statutory_holiday_night_overtime_hours, :modify_count '
                 . '    , :weekday_normal_time, :weekday_midnight_time_only, :holiday_normal_time, :holiday_midnight_time_only '
                 . '    , :public_holiday_normal_time, :public_holiday_midnight_time_only, :statutory_holiday_normal_time, :statutory_holiday_midnight_time_only'
                 . '  );  ';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3123");
                $errMsg = "ユーザID：" . $parameters[':user_id'] . "締日：" . $parameters[':closing_date'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3123";
            }

            $Log->trace("END addAttendanceDeadline");

            return "MSG_BASE_0000";
        }

        /**
         * 休日出勤テーブル登録
         * @param    $parameters   登録パラメータ
         * @return   SQLの実行結果
         */
        private function addHolidayWork( $parameters )
        {
            global $DBA,$Log; // グローバル変数宣言
            $Log->trace("START addHolidayWork");
            
            $sql = ' INSERT INTO t_holiday(  '
                 . '     user_id, closing_date, holiday_id, holiday_name_id, holiday_get_dates '
                 . '   , holiday_work_days, holiday_working_hours, holiday_prescribed_working_hours '
                 . '   , holiday_overtime_hours, holiday_late_night_time, holiday_midnight_overtime_hours '
                 . ' ) VALUES (  '
                 . '   :user_id, :closing_date, :holiday_id, :holiday_name_id, :holiday_get_dates, :holiday_work_days '
                 . '   , :holiday_working_hours, :holiday_prescribed_working_hours, :holiday_overtime_hours '
                 . '   , :holiday_late_night_time, :holiday_midnight_overtime_hours '
                 . ' ) ';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3127");
                $errMsg = "ユーザID：" . $parameters[':user_id'] . "締日：" . $parameters[':closing_date'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3127";
            }

            $Log->trace("END addHolidayWork");

            return "MSG_BASE_0000";
        }

        /**
         * 休日名称IDを取得する
         * @param    $holidayID     休日マスタID
         * @return   SQLの実行結果
         */
        private function getHolidayName( $holidayID )
        {
            global $DBA,$Log; // グローバル変数宣言
            $Log->trace("START getHolidayName");
            
            $sql = ' SELECT holiday_name_id FROM  m_holiday WHERE holiday_id = :holiday_id ';
            $searchArray = array(
                                  ':holiday_id'  => $holidayID, 
                                 );
            $result = $DBA->executeSQL( $sql, $searchArray );
            
            $ret = 0;
            if( $result === false )
            {
                $Log->trace("END getApprovedAttendanceInfo");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data['holiday_name_id'];
            }

            $Log->trace("END getHolidayName");

            return $ret;
        }

    }
?>
