<?php
    /**
     * @file      簡易勤怠モデル
     * @author    USE Y.Sakata
     * @date      2016/09/30
     * @version   1.00
     * @note      簡易勤怠モデル
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    // WorkingTimeRegistration.phpを読み込む
    require './Model/WorkingTimeRegistration.php';

    /**
     * 勤怠修正マスタクラス
     * @note   勤怠修正マスタテーブルの管理を行うの初期設定を行う
     */
    class SimpleAttendance extends BaseModel
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

            $userIDList = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( !in_array( $data['user_id'], $userIDList ) )
                {
                    array_push( $userIDList, $data['user_id'] );
                    array_push( $attendanceCorList, $data);
                }
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
                 .  ' , var.attendance_time, var.clock_out_time '
                 .  ' , var.s_break_time_1, var.e_break_time_1 '
                 .  ' , var.s_break_time_2, var.e_break_time_2 '
                 .  ' , var.s_break_time_3, var.e_break_time_3 '
                 .  ' , var.total_working_time, var.overtime, var.night_working_time, var.night_overtime, var.absence_count '
                 .  ' , var.shift_id, var.shift_attendance_time, var.shift_taikin_time, var.shift_break_time, var.is_holiday '
                 .  ' , var.shift_travel_time, var.shift_working_time, var.shift_overtime, var.shift_night_working_time'
                 .  ' , var.rough_estimate, var.shift_rough_estimate, var.break_time, var.approval, var.organization_id '
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
         * ユーザ名一覧取得SQL文作成
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
            
            $displayItemID = "";
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
        

    }
?>
