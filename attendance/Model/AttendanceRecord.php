<?php
    /**
     * @file      勤怠一覧表示
     * @author    USE M.Higashihara
     * @date      2016/07/12
     * @version   1.00
     * @note      勤怠一覧テーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/AttendanceRecordDisplayList.php';

    /**
     * 勤怠一覧画面制御
     * @note   勤怠一覧DB接続共通クラス
     */
    class AttendanceRecord extends AttendanceRecordDisplayList
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
         * 勤怠一覧の閲覧権限を取得
         * @return   $attendanceAccessId
         */
        public function getAttendanceAccessId()
        {
            global  $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAttendanceAccessId");

            $sql = ' SELECT reference FROM m_security_detail WHERE security_id = :security_id AND access_authority_id = 511 ';
            $searchArray = array( ':security_id' => $_SESSION["SECURITY_ID"], );

            $result = $DBA->executeSQL($sql, $searchArray);
            $attendanceAccessId = "";
            if( $result === false )
            {
                $Log->trace("END getAttendanceAccessId");
                return $attendanceAccessId;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $attendanceAccessId = $data['reference'];
            }

            $Log->trace("END getAttendanceAccessId");

            return $attendanceAccessId;
        }

        /**
         * 勤怠一覧画面一覧表
         * @param    $postArray
         * @return   $attendanceRecList
         */
        public function getListData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            if( $postArray['mobileUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_INDIVIDUAL') )
            {
                // 個人単位のデータ
                $periodDateList = $this->creatAttendanceUserList( $postArray );
            }
            else
            {
                $periodDateList = $this->allocationCountingUnit( $postArray );
            }

            $Log->trace("END getListData");

            return $periodDateList;
        }

        /**
         * 手当テーブル情報取得
         * @param    $postArray
         * @param    $exclusionKeyList
         * @return   $allowanceInfoList
         */
        public function getAllowanceInfoList( $postArray, $exclusionKeyList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAllowanceInfoList");

            // 指定期間開始日UNIX値
            $startUnix = $this->setStartDateUnix( $postArray['minPeriodSpecifiedYear'], $postArray['minPeriodSpecifiedMonth'], $postArray['minPeriodSpecifiedDay'] );
            // 指定期間終了日UNIX値
            $endUnix = $this->setEndDateUnix( $postArray['maxPeriodSpecifiedYear'], $postArray['maxPeriodSpecifiedMonth'], $postArray['maxPeriodSpecifiedDay'] );
            $endUnixPeriod = $this->setEndDateUnix( $postArray['maxPeriodSpecifiedYear'], $postArray['maxPeriodSpecifiedMonth'], $postArray['maxPeriodSpecifiedDay'] );
            $periodName = "";
            $targetPeriodList = array();
            if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') || $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_YEAR') )
            {
                // 集計単位（日時）が月/年の時、締日リストを作成する
                $endUnix = $this->setEndUnixOnePlus( $postArray['dateTimeUnit'], $endUnix );
                $targetPeriodList = $this->setTargetPeriodList( $postArray['dateTimeUnit'], $startUnix, $endUnixPeriod, $periodName );
            }
            // 指定期間開始日UNIXを年/月/日形式へ変更
            $startDate = date("Y/m/d", $startUnix);
            // 指定期間終了日UNIXを年/月/日形式へ変更
            $endDate = date("Y/m/d", $endUnix);
            // 勤怠ID一覧取得
            $parameters = array( 'access_id' => $postArray['attendanceAccessId'], 'startDate' => $startDate, 'endDate' => $endDate );
            // 勤怠一覧と連動させるためにキーとなるIDを設定する(user_id/position_id/employment_id)
            $mobileUnitId = "";
            $mobileUnitId = $this->setMobileUnitId( $postArray, $mobileUnitId );

            $dateUnitKey = ""; // 期間の基準（date(1日毎)/target_month_tightening（締め日を基準とした各月の情報）/target_year_tighten(締め日を基準とした各年の情報)）
            $sumMsg = "";      // 個人・日の場合のみ(, allowance_amount, allowance_cnt) それ以外（, SUM( allowance_amount ) as allowance_amount, SUM( allowance_cnt ) as allowance_cnt）
            $groupby = "";     // 個人・日集計時は空白、それ以外は合算するもの以外GROUP BY句へ入れる
            // 集計単位（日時）によって、SQL文を入れ替える
            if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_DAY') )
            {
                // 集計単位（日時）日の場合
                $dateUnitKey = ", to_char( date, 'yyyy/mm/dd' ) as date";
                if( $postArray['mobileUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_INDIVIDUAL') )
                {
                    // 集計単位（形態）個人
                    $sumMsg = ", allowance_amount, allowance_cnt";
                }
                else
                {
                    // 集計単位（形態）個人以外
                    $sumMsg = ", SUM( allowance_amount ) as allowance_amount, SUM( allowance_cnt ) as allowance_cnt";
                    $groupby = "GROUP BY allowance_id, organization_id, date";
                    $groupby .= $mobileUnitId;
                }
            }
            else if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') )
            {
                // 集計単位（日時）月の場合
                $dateUnitKey = ", target_month_tightening";
                $sumMsg = ", SUM( allowance_amount ) as allowance_amount, SUM( allowance_cnt ) as allowance_cnt";
                $groupby = "GROUP BY allowance_id, organization_id, target_month_tightening";
                $groupby .= $mobileUnitId;
            }
            else if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_YEAR') )
            {
                // 集計単位（日時）年の場合
                $dateUnitKey = ", target_year_tighten";
                $sumMsg = ", SUM( allowance_amount ) as allowance_amount, SUM( allowance_cnt ) as allowance_cnt";
                $groupby = "GROUP BY allowance_id, organization_id, target_year_tighten";
                $groupby .= $mobileUnitId;
            }
            else
            {
                $dateUnitKey = "";
            }

            // 取得手当リスト情報取得
            $allowanceAmountList = $this->getAllowanceAmountInfo( $parameters, $mobileUnitId, $dateUnitKey, $sumMsg, $groupby, $targetPeriodList, $periodName );
            $allowanceAmountList = $this->exclusionAttendanceSearchConditions( $allowanceAmountList, $exclusionKeyList, $postArray );
            // 集計行作成のため検索対象期間内の取得手当IDリストを取得する
            $targetList = array( 'idName' => 'allowance_id', 'tableName' => 'v_allowance_table', 'exclusion' => '', );
            $summaryKeyList = $this->getSummaryKeyList( $parameters, $dateUnitKey, $targetPeriodList, $periodName, $targetList );
            // 集計行リスト作成
            $allowanceAmountList = $this->getAllowanceAggregateList( $allowanceAmountList, $summaryKeyList );

            $Log->trace("END getAllowanceInfoList");

            return $allowanceAmountList;
        }

        /**
         * 休日取得情報取得
         * @param    $postArray
         * @param    $exclusionKeyList
         * @return   $allowanceInfoList
         */
        public function getHolidayInfoList( $postArray, $exclusionKeyList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getHolidayInfoList");

            // 指定期間開始日UNIX値
            $startUnix = $this->setStartDateUnix( $postArray['minPeriodSpecifiedYear'], $postArray['minPeriodSpecifiedMonth'], $postArray['minPeriodSpecifiedDay'] );
            // 指定期間終了日UNIX値
            $endUnix = $this->setEndDateUnix( $postArray['maxPeriodSpecifiedYear'], $postArray['maxPeriodSpecifiedMonth'], $postArray['maxPeriodSpecifiedDay'] );
            $endUnixPeriod = $this->setEndDateUnix( $postArray['maxPeriodSpecifiedYear'], $postArray['maxPeriodSpecifiedMonth'], $postArray['maxPeriodSpecifiedDay'] );
            $periodName = "";
            $targetPeriodList = array();
            // 集計単位（日時）が月/年の時、締日リストを作成する
            if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') || $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_YEAR') )
            {
                $endUnix = $this->setEndUnixOnePlus( $postArray['dateTimeUnit'], $endUnix );
                $targetPeriodList = $this->setTargetPeriodList( $postArray['dateTimeUnit'], $startUnix, $endUnixPeriod, $periodName );
            }
            // 指定期間開始日UNIXを年/月/日形式へ変更
            $startDate = date("Y/m/d", $startUnix);
            // 指定期間終了日UNIXを年/月/日形式へ変更
            $endDate = date("Y/m/d", $endUnix);
            // 勤怠ID一覧取得
            $parameters = array( 'access_id' => $postArray['attendanceAccessId'], 'startDate' => $startDate, 'endDate' => $endDate );
            // 勤怠一覧と連動させるためにキーとなるIDを設定する
            $mobileUnitId = ", is_holiday";
            $mobileUnitId = $this->setMobileUnitId( $postArray, $mobileUnitId );

            $dateUnitKey = "";
            $sumMsg = "";
            $groupby = "";
            if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_DAY') )
            {
                // 集計単位（日時）日の場合
                $dateUnitKey = ", to_char( date, 'yyyy/mm/dd' ) as date";
                if( $postArray['mobileUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_INDIVIDUAL') )
                {
                    // 集計単位（形態）個人
                    $sumMsg = ", holiday_get_cnt, holiday_attendance_cnt, total_working_time_con_minute, prescribed_working_hours, overtime_con_minute, night_working_time_con_minute, night_overtime_con_minute";
                }
                else
                {
                    // 集計単位（形態）個人以外
                    $sumMsg = ", SUM( holiday_get_cnt ) as holiday_get_cnt, SUM( holiday_attendance_cnt ) as holiday_attendance_cnt, SUM( total_working_time_con_minute ) as total_working_time_con_minute, SUM( prescribed_working_hours ) as prescribed_working_hours";
                    $sumMsg .= ", SUM( overtime_con_minute ) as overtime_con_minute, SUM( night_working_time_con_minute ) as night_working_time_con_minute, SUM( night_overtime_con_minute ) as night_overtime_con_minute";
                    $groupby = "GROUP BY is_holiday, organization_id, date";
                    $groupby .= $mobileUnitId;
                }
            }
            else if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') )
            {
                // 集計単位（日時）月の場合
                $dateUnitKey = ", target_month_tightening";
                $sumMsg = ", SUM( holiday_get_cnt ) as holiday_get_cnt, SUM( holiday_attendance_cnt ) as holiday_attendance_cnt, SUM( total_working_time_con_minute ) as total_working_time_con_minute, SUM( prescribed_working_hours ) as prescribed_working_hours";
                $sumMsg .= ", SUM( overtime_con_minute ) as overtime_con_minute, SUM( night_working_time_con_minute ) as night_working_time_con_minute, SUM( night_overtime_con_minute ) as night_overtime_con_minute";
                $groupby = "GROUP BY is_holiday, organization_id, target_month_tightening";
                $groupby .= $mobileUnitId;
            }
            else if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_YEAR') )
            {
                // 集計単位（日時）年の場合
                $dateUnitKey = ", target_year_tighten";
                $sumMsg = ", SUM( holiday_get_cnt ) as holiday_get_cnt, SUM( holiday_attendance_cnt ) as holiday_attendance_cnt, SUM( total_working_time_con_minute ) as total_working_time_con_minute, SUM( prescribed_working_hours ) as prescribed_working_hours";
                $sumMsg .= ", SUM( overtime_con_minute ) as overtime_con_minute, SUM( night_working_time_con_minute ) as night_working_time_con_minute, SUM( night_overtime_con_minute ) as night_overtime_con_minute";
                $groupby = "GROUP BY is_holiday, organization_id, target_year_tighten";
                $groupby .= $mobileUnitId;
            }
            else
            {
                $dateUnitKey = "";
            }

            // 取得休日リスト情報取得
            $holidayInfoIntList = $this->getHolidayAcquisitionInfo( $parameters, $mobileUnitId, $dateUnitKey, $sumMsg, $groupby, $targetPeriodList, $periodName );
            $holidayInfoIntList = $this->exclusionAttendanceSearchConditions( $holidayInfoIntList, $exclusionKeyList, $postArray );
            // 集計行作成のため検索対象期間内の取得休日フラグリストを取得する
            $targetList = array( 'idName' => 'is_holiday', 'tableName' => 'v_attendance_record', 'exclusion' => ' AND is_holiday > 0 AND is_holiday < 99999998 ', );
            $summaryKeyList = $this->getSummaryKeyList( $parameters, $dateUnitKey, $targetPeriodList, $periodName, $targetList );
            // 集計行リスト作成
            $holidayInfoIntList = $this->getHolidayAggregateList( $holidayInfoIntList, $summaryKeyList );

            $Log->trace("END getHolidayInfoList");

            return $holidayInfoIntList;
        }

        /**
         * 休日名称取得情報取得
         * @param    $postArray
         * @param    $exclusionKeyList
         * @return   $allowanceInfoList
         */
        public function getHolidayNameInfoList( $postArray, $exclusionKeyList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getHolidayNameInfoList");

            // 指定期間開始日UNIX値
            $startUnix = $this->setStartDateUnix( $postArray['minPeriodSpecifiedYear'], $postArray['minPeriodSpecifiedMonth'], $postArray['minPeriodSpecifiedDay'] );
            // 指定期間終了日UNIX値
            $endUnix = $this->setEndDateUnix( $postArray['maxPeriodSpecifiedYear'], $postArray['maxPeriodSpecifiedMonth'], $postArray['maxPeriodSpecifiedDay'] );
            $endUnixPeriod = $this->setEndDateUnix( $postArray['maxPeriodSpecifiedYear'], $postArray['maxPeriodSpecifiedMonth'], $postArray['maxPeriodSpecifiedDay'] );
            $periodName = "";
            $targetPeriodList = array();
            // 集計単位（日時）が月/年の時、締日リストを作成する
            if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') || $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_YEAR') )
            {
                $endUnix = $this->setEndUnixOnePlus( $postArray['dateTimeUnit'], $endUnix );
                $targetPeriodList = $this->setTargetPeriodList( $postArray['dateTimeUnit'], $startUnix, $endUnixPeriod, $periodName );
            }
            // 指定期間開始日UNIXを年/月/日形式へ変更
            $startDate = date("Y/m/d", $startUnix);
            // 指定期間終了日UNIXを年/月/日形式へ変更
            $endDate = date("Y/m/d", $endUnix);
            // 勤怠ID一覧取得
            $parameters = array( 'access_id' => $postArray['attendanceAccessId'], 'startDate' => $startDate, 'endDate' => $endDate );
            // 勤怠一覧と連動させるためにキーとなるIDを設定する
            $mobileUnitId = ", holiday_name_id";
            $mobileUnitId = $this->setMobileUnitId( $postArray, $mobileUnitId );

            $dateUnitKey = "";
            $sumMsg = "";
            $groupby = "";
            if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_DAY') )
            {
                // 集計単位（日時）日の場合
                $dateUnitKey = ", to_char( date, 'yyyy/mm/dd' ) as date";
                if( $postArray['mobileUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_INDIVIDUAL') )
                {
                    // 集計単位（形態）個人
                    $sumMsg = ", holiday_name_get_cnt, holiday_name_attendance_cnt, total_working_time_con_minute, prescribed_working_hours, overtime_con_minute, night_working_time_con_minute, night_overtime_con_minute";
                }
                else
                {
                    // 集計単位（形態）個人以外
                    $sumMsg = ", SUM( holiday_name_get_cnt ) as holiday_name_get_cnt, SUM( holiday_name_attendance_cnt ) as holiday_name_attendance_cnt, SUM( total_working_time_con_minute ) as total_working_time_con_minute, SUM( prescribed_working_hours ) as prescribed_working_hours";
                    $sumMsg .= ", SUM( overtime_con_minute ) as overtime_con_minute, SUM( night_working_time_con_minute ) as night_working_time_con_minute, SUM( night_overtime_con_minute ) as night_overtime_con_minute";
                    $groupby = "GROUP BY organization_id, date";
                    $groupby .= $mobileUnitId;
                }
            }
            else if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') )
            {
                // 集計単位（日時）月の場合
                $dateUnitKey = ", target_month_tightening";
                $sumMsg = ", SUM( holiday_name_get_cnt ) as holiday_name_get_cnt, SUM( holiday_name_attendance_cnt ) as holiday_name_attendance_cnt, SUM( total_working_time_con_minute ) as total_working_time_con_minute, SUM( prescribed_working_hours ) as prescribed_working_hours";
                $sumMsg .= ", SUM( overtime_con_minute ) as overtime_con_minute, SUM( night_working_time_con_minute ) as night_working_time_con_minute, SUM( night_overtime_con_minute ) as night_overtime_con_minute";
                $groupby = "GROUP BY organization_id, target_month_tightening";
                $groupby .= $mobileUnitId;
            }
            else if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_YEAR') )
            {
                
                $dateUnitKey = ", target_year_tighten";
                $sumMsg = ", SUM( holiday_name_get_cnt ) as holiday_name_get_cnt, SUM( holiday_name_attendance_cnt ) as holiday_name_attendance_cnt, SUM( total_working_time_con_minute ) as total_working_time_con_minute, SUM( prescribed_working_hours ) as prescribed_working_hours";
                $sumMsg .= ", SUM( overtime_con_minute ) as overtime_con_minute, SUM( night_working_time_con_minute ) as night_working_time_con_minute, SUM( night_overtime_con_minute ) as night_overtime_con_minute";
                $groupby = "GROUP BY organization_id, target_year_tighten";
                $groupby .= $mobileUnitId;
            }
            else
            {
                $dateUnitKey = "";
            }

            // 取得休日名称リスト情報取得
            $holidayNameIntList = $this->getHolidayAcquisitionInfo( $parameters, $mobileUnitId, $dateUnitKey, $sumMsg, $groupby, $targetPeriodList, $periodName );
            $holidayNameIntList = $this->exclusionAttendanceSearchConditions( $holidayNameIntList, $exclusionKeyList, $postArray );
            // 集計行作成のため検索対象期間内の取得休日フラグリストを取得する
            $targetList = array( 'idName' => 'holiday_name_id', 'tableName' => 'v_attendance_record', 'exclusion' => ' AND is_holiday > 0 AND is_holiday < 99999998 ', );
            $summaryKeyList = $this->getSummaryKeyList( $parameters, $dateUnitKey, $targetPeriodList, $periodName, $targetList );
            // 集計行リスト作成
            $holidayNameIntList = $this->getHolidayNameAggregateList( $holidayNameIntList, $summaryKeyList );

            $Log->trace("END getHolidayNameInfoList");

            return $holidayNameIntList;
        }

        /**
         * 検索対象組織名取得
         * @param    $organizationId
         * @return   $allowanceInfoList
         */
        public function getSearchTargetOrganizationName( $organizationId )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchTargetOrganizationName");

            $organizationName = "";

            // 検索用リスト検索期間セット
            $searchArray = array(
                ':organization_id' => $organizationId,
            );

            $sql = ' SELECT organization_name FROM m_organization_detail od '
                 . ' , ( SELECT organization_id, max(application_date_start) as application_date_start FROM m_organization_detail '
                 . '     WHERE organization_id = :organization_id AND application_date_start <= current_timestamp GROUP BY organization_id ) newData '
                 . ' WHERE od.organization_id = newData.organization_id AND od.application_date_start = newData.application_date_start ';
            if( !empty( $organizationId ) )
            {
                $result = $DBA->executeSQL($sql, $searchArray);
                while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
                {
                    $organizationName = $data['organization_name'];
                }
            }

            $Log->trace("END getSearchTargetOrganizationName");

            return $organizationName;
        }

        /**
         * 勤怠の検索条件除外済リストによる手当等の絞り込み
         * @param    $infoList
         * @param    $exclusionKeyList
         * @param    $postArray
         * @return   $infoExclusionList
         */
        private function exclusionAttendanceSearchConditions( $infoList, $exclusionKeyList, $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START exclusionAttendanceSearchConditions");

            $exclusionKeyIdName = $this->getExclusionKeyIdName( $postArray['mobileUnit'] );
            $exclusionPeriodList = $this->getExclusionPeriod( $postArray );
            $exclusionList = array();
            foreach( $exclusionKeyList as $exclusionKey )
            {
                foreach( $infoList as $info )
                {
                    if( $exclusionKey[$exclusionKeyIdName] == $info[$exclusionKeyIdName] &&  $exclusionKey[$exclusionPeriodList['exclusionPeriod']] == $info[$exclusionPeriodList['exclusion']]  )
                    {
                        if( $exclusionKeyIdName === 'position_id' ||  $exclusionKeyIdName === 'employment_id' )
                        {
                            if( $exclusionKey['organization_id'] == $info['organization_id'] )
                            {
                                $exclusionSettled = $info;
                                array_push( $exclusionList, $exclusionSettled );
                            }
                        }
                        else
                        {
                            $exclusionSettled = $info;
                            array_push( $exclusionList, $exclusionSettled );
                        }
                        
                    }
                }
            }

            $Log->trace("END exclusionAttendanceSearchConditions");

            return $exclusionList;
        }

        /**
         * 検索条件除外キー名取得
         * @param    $mobileUnit
         * @return   $exclusionKeyIdName
         */
        private function getExclusionKeyIdName( $mobileUnit )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getExclusionKeyIdName");

            if( $mobileUnit === $Log->getMsgLog('MSG_CUMULATIVE_INDIVIDUAL') )
            {
                $exclusionKeyIdName = 'user_id';
            }
            else if( $mobileUnit === $Log->getMsgLog('MSG_CUMULATIVE_ORGANIZATION') )
            {
                $exclusionKeyIdName = 'organization_id';
            }
            else if( $mobileUnit === $Log->getMsgLog('MSG_CUMULATIVE_POSITION') )
            {
                $exclusionKeyIdName = 'position_id';
            }
            else
            {
                $exclusionKeyIdName = 'employment_id';
            }

            $Log->trace("END getExclusionKeyIdName");

            return $exclusionKeyIdName;
        }

        /**
         * 検索条件除外キー名取得
         * @param    $postArray
         * @return   $exclusionPeriodList
         */
        private function getExclusionPeriod( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getExclusionPeriod");

            $exclusionPeriodList = array();
            if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_DAY') )
            {
                $exclusion = 'date';
                $exclusionPeriod = 'periodDate';
                if( $postArray['mobileUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_INDIVIDUAL') )
                {
                    $exclusion = 'attendance_id';
                    $exclusionPeriod = 'attendance_id';
                }
            }
            else if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') )
            {
                $exclusion = 'target_month_tightening';
                $exclusionPeriod = 'target_period';
            }
            else if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_YEAR') )
            {
                $exclusion = 'target_year_tighten';
                $exclusionPeriod = 'target_period';
            }
            else
            {
                $exclusionPeriod = '';
            }
            $exclusionPeriodList = array(
                'exclusion'       => $exclusion,
                'exclusionPeriod' => $exclusionPeriod,
            );

            $Log->trace("END getExclusionPeriod");

            return $exclusionPeriodList;
        }

        /**
         * 取得手当集計行作成
         * @param    $allowanceAmountList
         * @param    $summaryKeyList
         * @return   $aggregateList
         */
        private function getAllowanceAggregateList( $allowanceAmountList, $summaryKeyList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAllowanceAggregateList");

            foreach( $summaryKeyList as $summaryKey )
            {
                $aggregateNullList =array(
                    'user_id'                 => 0,
                    'organization_id'         => 0,
                    'position_id'             => 0,
                    'employment_id'           => 0,
                    'attendance_id'           => "-",
                    'date'                    => "-",
                    'target_month_tightening' => "-",
                    'target_year_tighten'     => "-",
                    'allowance_id'            => "",
                    'allowance_amount'        => "",
                    'allowance_cnt'           => "",
                );
                foreach( $allowanceAmountList as $allowance )
                {
                    if( $summaryKey['allowance_id'] == $allowance['allowance_id'] )
                    {
                        $aggregateNullList['allowance_id'] = $allowance['allowance_id'];
                        $aggregateNullList['allowance_amount'] = $aggregateNullList['allowance_amount'] + $allowance['allowance_amount'];
                        $aggregateNullList['allowance_cnt'] = $aggregateNullList['allowance_cnt'] + $allowance['allowance_cnt'];
                    }
                }
                array_push( $allowanceAmountList, $aggregateNullList );
            }

            $Log->trace("END getAllowanceAggregateList");

            return $allowanceAmountList;
        }

        /**
         * 取得休日集計行作成
         * @param    $holidayInfoIntList
         * @param    $summaryKeyList
         * @return   $holidayInfoIntList
         */
        private function getHolidayAggregateList( $holidayInfoIntList, $summaryKeyList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getHolidayAggregateList");

            foreach( $summaryKeyList as $summaryKey )
            {
                $aggregateNullList =array(
                    'user_id'                       => 0,
                    'organization_id'               => 0,
                    'position_id'                   => 0,
                    'employment_id'                 => 0,
                    'attendance_id'                 => "-",
                    'date'                          => "-",
                    'target_month_tightening'       => "-",
                    'target_year_tighten'           => "-",
                    'is_holiday'                    => "",
                    'holiday_get_cnt'               => "",
                    'holiday_attendance_cnt'        => "",
                    'total_working_time_con_minute' => "",
                    'prescribed_working_hours'      => "",
                    'overtime_con_minute'           => "",
                    'night_working_time_con_minute' => "",
                    'night_overtime_con_minute'     => "",
                );
                foreach( $holidayInfoIntList as $holidayInfo )
                {
                    if( $summaryKey['is_holiday'] == $holidayInfo['is_holiday'] )
                    {
                        $aggregateNullList['is_holiday'] = $holidayInfo['is_holiday'];
                        $aggregateNullList['holiday_get_cnt'] = $aggregateNullList['holiday_get_cnt'] + $holidayInfo['holiday_get_cnt'];
                        $aggregateNullList['holiday_attendance_cnt'] = $aggregateNullList['holiday_attendance_cnt'] + $holidayInfo['holiday_attendance_cnt'];
                        $aggregateNullList['total_working_time_con_minute'] = $aggregateNullList['total_working_time_con_minute'] + $holidayInfo['total_working_time_con_minute'];
                        $aggregateNullList['prescribed_working_hours'] = $aggregateNullList['prescribed_working_hours'] + $holidayInfo['prescribed_working_hours'];
                        $aggregateNullList['overtime_con_minute'] = $aggregateNullList['overtime_con_minute'] + $holidayInfo['overtime_con_minute'];
                        $aggregateNullList['night_working_time_con_minute'] = $aggregateNullList['night_working_time_con_minute'] + $holidayInfo['night_working_time_con_minute'];
                        $aggregateNullList['night_overtime_con_minute'] = $aggregateNullList['night_overtime_con_minute'] + $holidayInfo['night_overtime_con_minute'];
                    }
                }
                array_push( $holidayInfoIntList, $aggregateNullList );
            }

            $Log->trace("END getHolidayAggregateList");

            return $holidayInfoIntList;
        }

        /**
         * 取得休日名称集計行作成
         * @param    $holidayNameIntList
         * @param    $summaryKeyList
         * @return   $holidayNameIntList
         */
        private function getHolidayNameAggregateList( $holidayNameIntList, $summaryKeyList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getHolidayAggregateList");

            foreach( $summaryKeyList as $summaryKey )
            {
                $aggregateNullList =array(
                    'user_id'                       => 0,
                    'organization_id'               => 0,
                    'position_id'                   => 0,
                    'employment_id'                 => 0,
                    'attendance_id'                 => "-",
                    'date'                          => "-",
                    'target_month_tightening'       => "-",
                    'target_year_tighten'           => "-",
                    'holiday_name_id'               => "",
                    'holiday_name_get_cnt'          => "",
                    'holiday_name_attendance_cnt'   => "",
                    'total_working_time_con_minute' => "",
                    'prescribed_working_hours'      => "",
                    'overtime_con_minute'           => "",
                    'night_working_time_con_minute' => "",
                    'night_overtime_con_minute'     => "",
                );
                foreach( $holidayNameIntList as $holidayName )
                {
                    if( $summaryKey['holiday_name_id'] == $holidayName['holiday_name_id'] )
                    {
                        $aggregateNullList['holiday_name_id'] = $holidayName['holiday_name_id'];
                        $aggregateNullList['holiday_name_get_cnt'] = $aggregateNullList['holiday_name_get_cnt'] + $holidayName['holiday_name_get_cnt'];
                        $aggregateNullList['holiday_name_attendance_cnt'] = $aggregateNullList['holiday_name_attendance_cnt'] + $holidayName['holiday_name_attendance_cnt'];
                        $aggregateNullList['total_working_time_con_minute'] = $aggregateNullList['total_working_time_con_minute'] + $holidayName['total_working_time_con_minute'];
                        $aggregateNullList['prescribed_working_hours'] = $aggregateNullList['prescribed_working_hours'] + $holidayName['prescribed_working_hours'];
                        $aggregateNullList['overtime_con_minute'] = $aggregateNullList['overtime_con_minute'] + $holidayName['overtime_con_minute'];
                        $aggregateNullList['night_working_time_con_minute'] = $aggregateNullList['night_working_time_con_minute'] + $holidayName['night_working_time_con_minute'];
                        $aggregateNullList['night_overtime_con_minute'] = $aggregateNullList['night_overtime_con_minute'] + $holidayName['night_overtime_con_minute'];
                    }
                }
                array_push( $holidayNameIntList, $aggregateNullList );
            }

            $Log->trace("END getHolidayAggregateList");

            return $holidayNameIntList;
        }

        /**
         * 累計形態個人以外の場合に勤怠集計の単位にて処理を振り分ける
         * @param    $postArray
         * @return   $attendanceUnitList
         */
        private function allocationCountingUnit( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START allocationCountingUnit");

            // 検索対象（組織（１）、役職（２）、雇用形態（３））のフラグを設定
            $unitFlag = $this->setUnitFlag( $postArray['mobileUnit'] );

            $attendanceUnitList = array();

            if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_DAY') )
            {
                // 指定期間（日）対象情報リスト取得
                $addingUpUnitList = $this->creatAddingUpUnitDList( $postArray, $unitFlag );
               
            }
            else if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_TIME') )
            {
                $attendanceUnitList = array();
            }
            else
            {
                // 指定期間（月）対象情報リスト取得
                $addingUpUnitList = $this->creatAddingUpUnitList( $postArray, $unitFlag );
            }
            // 検索条件による対象外となる行Noリストを取得
            $keyNoList = $this->setKeyNoList( $postArray, $addingUpUnitList );
            // 対象外リストの重複を削除して、キーを整理する
            $keyNoList = $this->setUniqueArray( $keyNoList );
            // 検索対象外キーの行を削除して配列の再構築
            $attUnitCnt = 1;
            $attendanceSerch = $this->liquidationAttendanceList( $addingUpUnitList, $keyNoList, $attUnitCnt );
            // 組織の表示順へ並び替え
            $attendanceUnitList = $this->creatAccessControlledList( $_SESSION["REFERENCE"], $attendanceSerch );
            // 集計行
            $unitSummaryRow = $this->creatUnitSummaryRow( $attendanceSerch, $attUnitCnt );
            array_push( $attendanceUnitList, $unitSummaryRow );

            $Log->trace("END allocationCountingUnit");

            return $attendanceUnitList;
        }

        /**
         * 繰返項目判定キーの設定
         * @param    $postArray
         * @return   $mobileUnitId
         */
        private function setMobileUnitId( $postArray, $mobileUnitId )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setMobileUnitId");

            if( $postArray['mobileUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_INDIVIDUAL') )
            {
                $mobileUnitId .= ", user_id";
                if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_DAY') )
                {
                    $mobileUnitId .= ", attendance_id";
                }
            }
            else if( $postArray['mobileUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_POSITION') )
            {
                $mobileUnitId .= ", position_id";
            }
            else if( $postArray['mobileUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_EMPLOYMENT') )
            {
                $mobileUnitId .= ", employment_id";
            }

            $Log->trace("END setMobileUnitId");

            return $mobileUnitId;
        }

/*******************************
* 個人以外日単位処理
********************************/

        /**
         * 組織、役職、雇用形態での一覧データ取得処理
         * @param    $postArray
         * @param    $unitFlag
         * @return   $addingUpUnitList
         */
        private function creatAddingUpUnitDList( $postArray, $unitFlag )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAddingUpUnitDList");

            // 指定期間開始日UNIX値
            $startUnix = $this->setStartDateUnix( $postArray['minPeriodSpecifiedYear'], $postArray['minPeriodSpecifiedMonth'], $postArray['minPeriodSpecifiedDay'] );
            // 指定期間終了日UNIX値
            $endUnix = $this->setEndDateUnix( $postArray['maxPeriodSpecifiedYear'], $postArray['maxPeriodSpecifiedMonth'], $postArray['maxPeriodSpecifiedDay'] );
            // 指定期間開始日を年月日で分割
            $sec = 86400;
            // 指定期間の日時リスト作成
            $periodDateList = $this->creatDateList($startUnix, $endUnix, $sec);
            // 指定期間開始日UNIXを年/月/日形式へ変更
            $startDate = date("Y/m/d",$startUnix);
            // 指定期間終了日UNIXを年/月/日形式へ変更
            $endDate = date("Y/m/d",$endUnix);
            // 検索対象の累計単位（形態）のリストを取得
            $unitList = $this->getUnitList( $postArray, $unitFlag );
            // 指定期間の日数分の累計単位（形態）リストの作成
            $periodDaysUnitList = $this->setPeriodDaysUnitList( $periodDateList, $unitList );
            // 累計単位（形態）の勤怠データ取得
            $attendanceList = $this->getUnitAttendanceListBranch( $postArray, $startDate, $endDate );
            // 勤怠情報を合体
            $addingUpUnitList = $this->setAddingUpUnitDList( $periodDaysUnitList, $attendanceList, $unitFlag );
            // 検索条件（打刻関係）での絞り込み
            $addingUpUnitList = $this->refineEmbossingSituation( $addingUpUnitList, $postArray, "embossingOrganizationID", "embossing_organization_id" );
            $addingUpUnitList = $this->refineEmbossingSituation( $addingUpUnitList, $postArray, "embossingSituation", "embossing_status" );

            $Log->trace("END creatAddingUpUnitDList");

            return $addingUpUnitList;
        }

        /**
         * 指定期間リストと形態リストの組み合わせ
         * @param    $periodDateList
         * @param    $unitList
         * @return   $periodDaysUnitList
         */
        private function setPeriodDaysUnitList( $periodDateList, $unitList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setPeriodDaysUnitList");

            $periodDaysUnitList = array();
            $periodCnt = 1;
            foreach( $unitList as $unit)
            {
                foreach($periodDateList as $periodDate)
                {
                    if( strtotime( $unit['first_date'] ) <= strtotime( $periodDate['periodSpecified'] ) )
                    {
                        $unit['period_id'] = $periodCnt;
                        $unit['periodDate'] = $periodDate['periodSpecified'];
                        array_push($periodDaysUnitList, $unit);
                        $periodCnt++;
                    }
                }
            }

            $Log->trace("END setPeriodDaysUnitList");

            return $periodDaysUnitList;
        }

        /**
         * 個人以外日別集計
         * @param    $periodDaysUnitList
         * @param    $attendanceList
         * @param    $unitFlag
         * @return   $addingUpUnitDList
         */
        private function setAddingUpUnitDList( $periodDaysUnitList, $attendanceList, $unitFlag )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setAddingUpUnitDList");

            $addingUpUnitDList = array();
            foreach( $periodDaysUnitList as $unit )
            {
                $initialValList = $this->setInitialValList();

                foreach( $attendanceList as $attendance )
                {
                    if( $unitFlag == 1 )
                    {
                        $initialValList = $this->insertOrgAttendanceDaysList( $initialValList, $unit, $attendance );
                    }
                    else if( $unitFlag == 2 )
                    {
                        $initialValList = $this->insertPosAttendanceDaysList( $initialValList, $unit, $attendance );
                    }
                    else
                    {
                        $initialValList = $this->insertEmpAttendanceDaysList( $initialValList, $unit, $attendance );
                    }
                }
                $unit['total_working_time_con_minute'] = $initialValList['total_working_time_con_minute'];
                $unit['weekday_working_con_minute'] = $initialValList['weekday_working_con_minute'];
                $unit['weekend_working_con_minute'] = $initialValList['weekend_working_con_minute'];
                $unit['legal_holiday_working_con_minute'] = $initialValList['legal_holiday_working_con_minute'];
                $unit['law_closed_working_con_minute'] = $initialValList['law_closed_working_con_minute'];
                $unit['overtime_con_minute'] = $initialValList['overtime_con_minute'];
                $unit['weekday_overtime_con_minute'] = $initialValList['weekday_overtime_con_minute'];
                $unit['weekend_overtime_con_minute'] = $initialValList['weekend_overtime_con_minute'];
                $unit['legal_holiday_overtime_con_minute'] = $initialValList['legal_holiday_overtime_con_minute'];
                $unit['law_closed_overtime_con_minute'] = $initialValList['law_closed_overtime_con_minute'];
                $unit['night_working_time_con_minute'] = $initialValList['night_working_time_con_minute'];
                $unit['weekday_night_working_con_minute'] = $initialValList['weekday_night_working_con_minute'];
                $unit['weekend_night_working_con_minute'] = $initialValList['weekend_night_working_con_minute'];
                $unit['legal_holiday_night_working_con_minute'] = $initialValList['legal_holiday_night_working_con_minute'];
                $unit['law_closed_night_working_con_minute'] = $initialValList['law_closed_night_working_con_minute'];
                $unit['night_overtime_con_minute'] = $initialValList['night_overtime_con_minute'];
                $unit['weekday_night_overtime_con_minute'] = $initialValList['weekday_night_overtime_con_minute'];
                $unit['weekend_night_overtime_con_minute'] = $initialValList['weekend_night_overtime_con_minute'];
                $unit['legal_holiday_night_overtime_con_minute'] = $initialValList['legal_holiday_night_overtime_con_minute'];
                $unit['law_closed_night_overtime_con_minute'] = $initialValList['law_closed_night_overtime_con_minute'];
                $unit['break_time_con_minute'] = $initialValList['break_time_con_minute'];
                $unit['late_time_con_minute'] = $initialValList['late_time_con_minute'];
                $unit['leave_early_time_con_minute'] = $initialValList['leave_early_time_con_minute'];
                $unit['travel_time_con_minute'] = $initialValList['travel_time_con_minute'];
                $unit['late_leave_time_con_minute'] = $initialValList['late_leave_time_con_minute'];
                $unit['modify_count'] = $initialValList['modify_count'];
                $unit['late_time_count'] = $initialValList['late_time_count'];
                $unit['leave_early_time_count'] = $initialValList['leave_early_time_count'];
                $unit['late_leave_count'] = $initialValList['late_leave_count'];
                $unit['attendance_time_count'] = $initialValList['attendance_time_count'];
                $unit['weekday_attendance_time_count'] = $initialValList['weekday_attendance_time_count'];
                $unit['weekend_attendance_time_count'] = $initialValList['weekend_attendance_time_count'];
                $unit['legal_holiday_attendance_time_count'] = $initialValList['legal_holiday_attendance_time_count'];
                $unit['law_closed_attendance_time_count'] = $initialValList['law_closed_attendance_time_count'];
                $unit['rough_estimate'] = $initialValList['rough_estimate'];
                $unit['shift_rough_estimate'] = $initialValList['shift_rough_estimate'];
                $unit['rough_estimate_month'] = $initialValList['rough_estimate_month'];
                $unit['fixed_overtime_time'] = $initialValList['fixed_overtime_time'];
                $unit['absence_count'] = $initialValList['absence_count'];
                $unit['prescribed_working_days'] = $initialValList['prescribed_working_days'];
                $unit['prescribed_working_hours'] = $initialValList['prescribed_working_hours'];
                $unit['weekday_prescribed_working_hours'] = $initialValList['weekday_prescribed_working_hours'];
                $unit['weekend_prescribed_working_hours'] = $initialValList['weekend_prescribed_working_hours'];
                $unit['legal_holiday_prescribed_working_hours'] = $initialValList['legal_holiday_prescribed_working_hours'];
                $unit['law_closed_prescribed_working_hours'] = $initialValList['law_closed_prescribed_working_hours'];
                $unit['statutory_overtime_hours'] = $initialValList['statutory_overtime_hours'];
                $unit['nonstatutory_overtime_hours_all'] = $initialValList['nonstatutory_overtime_hours_all'];
                $unit['nonstatutory_overtime_hours'] = $initialValList['nonstatutory_overtime_hours'];
                $unit['nonstatutory_overtime_hours_45h'] = $initialValList['nonstatutory_overtime_hours_45h'];
                $unit['nonstatutory_overtime_hours_less_than'] = $initialValList['nonstatutory_overtime_hours_less_than'];
                $unit['nonstatutory_overtime_hours_60h'] = $initialValList['nonstatutory_overtime_hours_60h'];
                $unit['statutory_overtime_hours_no_pub'] = $initialValList['statutory_overtime_hours_no_pub'];
                $unit['nonstatutory_overtime_hours_no_pub_all'] = $initialValList['nonstatutory_overtime_hours_no_pub_all'];
                $unit['nonstatutory_overtime_hours_no_pub'] = $initialValList['nonstatutory_overtime_hours_no_pub'];
                $unit['nonstatutory_overtime_hours_45h_no_pub'] = $initialValList['nonstatutory_overtime_hours_45h_no_pub'];
                $unit['nonstatutory_overtime_hours_no_pub_less_than'] = $initialValList['nonstatutory_overtime_hours_no_pub_less_than'];
                $unit['nonstatutory_overtime_hours_60h_no_pub'] = $initialValList['nonstatutory_overtime_hours_60h_no_pub'];
                $unit['overtime_hours_no_considered'] = $initialValList['overtime_hours_no_considered'];
                $unit['overtime_hours_no_considered_no_pub'] = $initialValList['overtime_hours_no_considered_no_pub'];
                $unit['normal_working_con_minute'] = $initialValList['normal_working_con_minute'];
                $unit['normal_overtime_con_minute'] = $initialValList['normal_overtime_con_minute'];
                $unit['normal_night_working_con_minute'] = $initialValList['normal_night_working_con_minute'];
                $unit['normal_night_overtime_con_minute'] = $initialValList['normal_night_overtime_con_minute'];
                $unit['embossing_organization_id'] = $initialValList['embossing_organization_id'];
                $unit['embossing_department_code'] = $initialValList['embossing_department_code'];
                $unit['embossing_organization_name'] = $initialValList['embossing_organization_name'];
                $unit['embossing_abbreviated_name'] = $initialValList['embossing_abbreviated_name'];
                $unit['embossing_status'] = $initialValList['embossing_status'];
                $unit['work_classification'] = $initialValList['work_classification'];

                // millionet oota 追加
                $unit['weekday_normal_time'] = $initialValList['weekday_normal_time'];
                $unit['weekday_midnight_time_only'] = $initialValList['weekday_midnight_time_only'];
                $unit['holiday_normal_time'] = $initialValList['holiday_normal_time'];
                $unit['holiday_midnight_time_only'] = $initialValList['holiday_midnight_time_only'];
                $unit['statutory_holiday_normal_time'] = $initialValList['statutory_holiday_normal_time'];
                $unit['statutory_holiday_midnight_time_only'] = $initialValList['statutory_holiday_midnight_time_only'];
                $unit['public_holiday_normal_time'] = $initialValList['public_holiday_normal_time'];
                $unit['public_holiday_midnight_time_only'] = $initialValList['public_holiday_midnight_time_only'];
                
                array_push($addingUpUnitDList, $unit);
            }

            $Log->trace("END setAddingUpUnitDList");


            return $addingUpUnitDList;
        }

        /**
         * 組織別勤怠日合算処理
         * @param    $initialValList
         * @param    $unitArray
         * @param    $attendanceArray
         * @return   $initialValList
         */
        private function insertOrgAttendanceDaysList( $initialValList, $unitArray, $attendanceArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START insertOrgAttendanceDaysList");

            if( ( $unitArray['organization_id'] == $attendanceArray['organization_id'] ) && ( strtotime( $unitArray['periodDate'] ) == strtotime( $attendanceArray['date'] ) ) )
            {
                $initialValList = $this->attendanceListTotalCalculation( $initialValList, $attendanceArray );
            }

            $Log->trace("END insertOrgAttendanceDaysList");

            return $initialValList;
        }

        /**
         * 役職別勤怠日合算処理
         * @param    $initialValList
         * @param    $unitArray
         * @param    $attendanceArray
         * @return   $initialValList
         */
        private function insertPosAttendanceDaysList( $initialValList, $unitArray, $attendanceArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START insertPosAttendanceDaysList");

            if( ( $unitArray['organization_id'] == $attendanceArray['organization_id'] ) && ( $unitArray['position_id'] == $attendanceArray['position_id'] ) && ( strtotime( $unitArray['periodDate'] ) == strtotime( $attendanceArray['date'] ) ) )
            {
                $initialValList = $this->attendanceListTotalCalculation( $initialValList, $attendanceArray );
            }

            $Log->trace("END insertPosAttendanceDaysList");


            return $initialValList;
        }

        /**
         * 雇用形態別勤怠日合算処理
         * @param    $initialValList
         * @param    $unitArray
         * @param    $attendanceArray
         * @return   $initialValList
         */
        private function insertEmpAttendanceDaysList( $initialValList, $unitArray, $attendanceArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START insertEmpAttendanceDaysList");

            if( ( $unitArray['organization_id'] == $attendanceArray['organization_id'] ) && ( $unitArray['employment_id'] == $attendanceArray['employment_id'] ) && ( strtotime( $unitArray['periodDate'] ) == strtotime( $attendanceArray['date'] ) ) )
            {
                $initialValList = $this->attendanceListTotalCalculation( $initialValList, $attendanceArray );
            }

            $Log->trace("END insertEmpAttendanceDaysList");


            return $initialValList;
        }

/*******************************
* 個人以外月合算処理
********************************/

        /**
         * 月合算用個人以外リスト取得
         * @param    $postArray
         * @param    $unitFlag
         * @return   $addingUpUnitList
         */
        private function creatAddingUpUnitList( $postArray, $unitFlag )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAddingUpUnitList");

            // 指定期間開始日UNIX値
            $startUnix = $this->setStartDateUnix( $postArray['minPeriodSpecifiedYear'], $postArray['minPeriodSpecifiedMonth'], $postArray['minPeriodSpecifiedDay'] );
            // 指定期間終了日UNIX値
            $endUnix = $this->setEndDateUnix( $postArray['maxPeriodSpecifiedYear'], $postArray['maxPeriodSpecifiedMonth'], $postArray['maxPeriodSpecifiedDay'] );
            // 検索する範囲を1か月追加する
            $endUnixOnePlus = $this->setEndUnixOnePlus( $postArray['dateTimeUnit'], $endUnix );
            // 指定期間開始日UNIXを年/月/日形式へ変更
            $startDate = date("Y/m/d",$startUnix);
            // 指定期間終了日UNIXを年/月/日形式へ変更
            $endDate = date("Y/m/d",$endUnixOnePlus);
            // 従業員リストを指定期間作成する際に対象期間のキー名を月または年で指定する
            $periodName = "";
            // 検索対象月のリストをセット
            $targetPeriodList = $this->setTargetPeriodList( $postArray['dateTimeUnit'], $startUnix, $endUnix, $periodName );
            // 検索対象の累計単位（形態）のリストを取得
            $unitList = $this->getUnitList( $postArray, $unitFlag );
            // 指定期間（月/年）分の従業員リストを作成
            $unitPeriodList = $this->setUnitPeriodList( $targetPeriodList, $unitList, $periodName );
            // 累計単位（形態）の勤怠データ取得
            $attendanceList = $this->getUnitAttendanceListBranch( $postArray, $startDate, $endDate );
            // 合算処理
            $addingUpUnitList = $this->addingUpUnitAttendanceData( $unitPeriodList, $attendanceList, $unitFlag, $postArray['dateTimeUnit'] );
            // 検索条件（打刻関係）での絞り込み
            $addingUpUnitList = $this->refineEmbossingSituation( $addingUpUnitList, $postArray, "embossingOrganizationID", "embossing_organization_id" );
            $addingUpUnitList = $this->refineEmbossingSituation( $addingUpUnitList, $postArray, "embossingSituation", "embossing_status" );

            $Log->trace("END creatAddingUpUnitList");

            return $addingUpUnitList;
        }

        /**
         * 指定期間リストと形態リストの組み合わせ
         * @param    $targetMonthList
         * @param    $unitList
         * @param    $periodName
         * @return   $periodDaysUnitList
         */
        private function setUnitPeriodList( $targetMonthList, $unitList, $periodName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setUnitPeriodList");

            $unitPeriodList = array();
            $periodCnt = 1;
            foreach( $unitList as $unit)
            {
                foreach($targetMonthList as $target)
                {
                    if( strtotime( $unit['first_date'] ) <= strtotime( $target['targetLastDate'] ) )
                    {
                        $unit['period_id'] = $periodCnt;
                        $unit['target_period'] = $target[$periodName];
                        array_push($unitPeriodList, $unit);
                        $periodCnt++;
                    }
                }
            }

            $Log->trace("END setUnitPeriodList");

            return $unitPeriodList;
        }

        /**
         * 従業員以外勤怠月合算処理
         * @param    $unitPeriodList
         * @param    $attendanceList
         * @param    $unitFlag
         * @param    $dateTimeUnit
         * @return   $addingUpUnitList
         */
        private function addingUpUnitAttendanceData( $unitPeriodList, $attendanceList, $unitFlag, $dateTimeUnit )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addingUpUnitAttendanceData");

            $targetKeyName = $this->setTargetKeyName( $dateTimeUnit );

            $addingUpUnitList = array();
            foreach( $unitPeriodList as $unit )
            {
                $initialValList = $this->setInitialValList();

                foreach( $attendanceList as $attendance )
                {
                    if( $unitFlag == 1 )
                    {
                        $initialValList = $this->insertOrganizationAttendanceList( $initialValList, $unit, $attendance, $targetKeyName );
                    }
                    else if( $unitFlag == 2 )
                    {
                        $initialValList = $this->insertPositionAttendanceList( $initialValList, $unit, $attendance, $targetKeyName );
                    }
                    else
                    {
                        $initialValList = $this->insertEmploymentAttendanceList( $initialValList, $unit, $attendance, $targetKeyName );
                    }
                }
                $unit['total_working_time_con_minute'] = $initialValList['total_working_time_con_minute'];
                $unit['weekday_working_con_minute'] = $initialValList['weekday_working_con_minute'];
                $unit['weekend_working_con_minute'] = $initialValList['weekend_working_con_minute'];
                $unit['legal_holiday_working_con_minute'] = $initialValList['legal_holiday_working_con_minute'];
                $unit['law_closed_working_con_minute'] = $initialValList['law_closed_working_con_minute'];
                $unit['overtime_con_minute'] = $initialValList['overtime_con_minute'];
                $unit['weekday_overtime_con_minute'] = $initialValList['weekday_overtime_con_minute'];
                $unit['weekend_overtime_con_minute'] = $initialValList['weekend_overtime_con_minute'];
                $unit['legal_holiday_overtime_con_minute'] = $initialValList['legal_holiday_overtime_con_minute'];
                $unit['law_closed_overtime_con_minute'] = $initialValList['law_closed_overtime_con_minute'];
                $unit['night_working_time_con_minute'] = $initialValList['night_working_time_con_minute'];
                $unit['weekday_night_working_con_minute'] = $initialValList['weekday_night_working_con_minute'];
                $unit['weekend_night_working_con_minute'] = $initialValList['weekend_night_working_con_minute'];
                $unit['legal_holiday_night_working_con_minute'] = $initialValList['legal_holiday_night_working_con_minute'];
                $unit['law_closed_night_working_con_minute'] = $initialValList['law_closed_night_working_con_minute'];
                $unit['night_overtime_con_minute'] = $initialValList['night_overtime_con_minute'];
                $unit['weekday_night_overtime_con_minute'] = $initialValList['weekday_night_overtime_con_minute'];
                $unit['weekend_night_overtime_con_minute'] = $initialValList['weekend_night_overtime_con_minute'];
                $unit['legal_holiday_night_overtime_con_minute'] = $initialValList['legal_holiday_night_overtime_con_minute'];
                $unit['law_closed_night_overtime_con_minute'] = $initialValList['law_closed_night_overtime_con_minute'];
                $unit['break_time_con_minute'] = $initialValList['break_time_con_minute'];
                $unit['late_time_con_minute'] = $initialValList['late_time_con_minute'];
                $unit['leave_early_time_con_minute'] = $initialValList['leave_early_time_con_minute'];
                $unit['travel_time_con_minute'] = $initialValList['travel_time_con_minute'];
                $unit['late_leave_time_con_minute'] = $initialValList['late_leave_time_con_minute'];
                $unit['modify_count'] = $initialValList['modify_count'];
                $unit['late_time_count'] = $initialValList['late_time_count'];
                $unit['leave_early_time_count'] = $initialValList['leave_early_time_count'];
                $unit['late_leave_count'] = $initialValList['late_leave_count'];
                $unit['attendance_time_count'] = $initialValList['attendance_time_count'];
                $unit['weekday_attendance_time_count'] = $initialValList['weekday_attendance_time_count'];
                $unit['weekend_attendance_time_count'] = $initialValList['weekend_attendance_time_count'];
                $unit['legal_holiday_attendance_time_count'] = $initialValList['legal_holiday_attendance_time_count'];
                $unit['law_closed_attendance_time_count'] = $initialValList['law_closed_attendance_time_count'];
                $unit['rough_estimate'] = $initialValList['rough_estimate'];
                $unit['shift_rough_estimate'] = $initialValList['shift_rough_estimate'];
                $unit['rough_estimate_month'] = $initialValList['rough_estimate_month'];
                $unit['fixed_overtime_time'] = $initialValList['fixed_overtime_time'];
                $unit['absence_count'] = $initialValList['absence_count'];
                $unit['prescribed_working_days'] = $initialValList['prescribed_working_days'];
                $unit['prescribed_working_hours_con_minute'] = $initialValList['prescribed_working_hours'];
                $unit['weekday_prescribed_working_hours'] = $initialValList['weekday_prescribed_working_hours'];
                $unit['weekend_prescribed_working_hours'] = $initialValList['weekend_prescribed_working_hours'];
                $unit['legal_holiday_prescribed_working_hours'] = $initialValList['legal_holiday_prescribed_working_hours'];
                $unit['law_closed_prescribed_working_hours'] = $initialValList['law_closed_prescribed_working_hours'];
                $unit['statutory_overtime_hours'] = $initialValList['statutory_overtime_hours'];
                $unit['nonstatutory_overtime_hours_all'] = $initialValList['nonstatutory_overtime_hours_all'];
                $unit['nonstatutory_overtime_hours'] = $initialValList['nonstatutory_overtime_hours'];
                $unit['nonstatutory_overtime_hours_45h'] = $initialValList['nonstatutory_overtime_hours_45h'];
                $unit['nonstatutory_overtime_hours_less_than'] = $initialValList['nonstatutory_overtime_hours_less_than'];
                $unit['nonstatutory_overtime_hours_60h'] = $initialValList['nonstatutory_overtime_hours_60h'];
                $unit['statutory_overtime_hours_no_pub'] = $initialValList['statutory_overtime_hours_no_pub'];
                $unit['nonstatutory_overtime_hours_no_pub_all'] = $initialValList['nonstatutory_overtime_hours_no_pub_all'];
                $unit['nonstatutory_overtime_hours_no_pub'] = $initialValList['nonstatutory_overtime_hours_no_pub'];
                $unit['nonstatutory_overtime_hours_45h_no_pub'] = $initialValList['nonstatutory_overtime_hours_45h_no_pub'];
                $unit['nonstatutory_overtime_hours_no_pub_less_than'] = $initialValList['nonstatutory_overtime_hours_no_pub_less_than'];
                $unit['nonstatutory_overtime_hours_60h_no_pub'] = $initialValList['nonstatutory_overtime_hours_60h_no_pub'];
                $unit['overtime_hours_no_considered'] = $initialValList['overtime_hours_no_considered'];
                $unit['overtime_hours_no_considered_no_pub'] = $initialValList['overtime_hours_no_considered_no_pub'];
                $unit['normal_working_con_minute'] = $initialValList['normal_working_con_minute'];
                $unit['normal_overtime_con_minute'] = $initialValList['normal_overtime_con_minute'];
                $unit['normal_night_working_con_minute'] = $initialValList['normal_night_working_con_minute'];
                $unit['normal_night_overtime_con_minute'] = $initialValList['normal_night_overtime_con_minute'];
                $unit['embossing_organization_id'] = $initialValList['embossing_organization_id'];
                $unit['embossing_department_code'] = $initialValList['embossing_department_code'];
                $unit['embossing_organization_name'] = $initialValList['embossing_organization_name'];
                $unit['embossing_abbreviated_name'] = $initialValList['embossing_abbreviated_name'];
                $unit['embossing_status'] = $initialValList['embossing_status'];
                $unit['work_classification'] = $initialValList['work_classification'];
                
                // millionet oota 追加
                $unit['weekday_normal_time'] = $initialValList['weekday_normal_time'];
                $unit['weekday_midnight_time_only'] = $initialValList['weekday_midnight_time_only'];
                $unit['holiday_normal_time'] = $initialValList['holiday_normal_time'];
                $unit['holiday_midnight_time_only'] = $initialValList['holiday_midnight_time_only'];
                $unit['statutory_holiday_normal_time'] = $initialValList['statutory_holiday_normal_time'];
                $unit['statutory_holiday_midnight_time_only'] = $initialValList['statutory_holiday_midnight_time_only'];
                $unit['public_holiday_normal_time'] = $initialValList['public_holiday_normal_time'];
                $unit['public_holiday_midnight_time_only'] = $initialValList['public_holiday_midnight_time_only'];
                
                array_push($addingUpUnitList, $unit);

            }

            $Log->trace("END addingUpUnitAttendanceData");

            return $addingUpUnitList;
        }

        /**
         * 組織勤怠年月合算処理
         * @param    $initialValList
         * @param    $unitArray
         * @param    $attendanceArray
         * @param    $targetKeyName
         * @return   $initialValList
         */
        private function insertOrganizationAttendanceList( $initialValList, $unitArray, $attendanceArray, $targetKeyName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START insertOrganizationAttendanceList");

            if( ( $unitArray['organization_id'] == $attendanceArray['organization_id'] ) && ( $unitArray['target_period'] === $attendanceArray[$targetKeyName] ) )
            {
                $initialValList = $this->attendanceListTotalCalculation( $initialValList, $attendanceArray );
            }

            $Log->trace("END insertOrganizationAttendanceList");

            return $initialValList;
        }

        /**
         * 役職別勤怠月年合算処理
         * @param    $initialValList
         * @param    $unitArray
         * @param    $attendanceArray
         * @param    $targetKeyName
         * @return   $initialValList
         */
        private function insertPositionAttendanceList( $initialValList, $unitArray, $attendanceArray, $targetKeyName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START insertPositionAttendanceList");

            if( ( $unitArray['organization_id'] == $attendanceArray['organization_id'] ) && ( $unitArray['position_id'] === $attendanceArray['position_id'] ) && ( $unitArray['target_period'] === $attendanceArray[$targetKeyName] ) )
            {
                $initialValList = $this->attendanceListTotalCalculation( $initialValList, $attendanceArray );
            }

            $Log->trace("END insertPositionAttendanceList");

            return $initialValList;
        }

        /**
         * 雇用形態別勤怠月年合算処理
         * @param    $initialValList
         * @param    $unitArray
         * @param    $attendanceArray
         * @param    $targetKeyName
         * @return   $initialValList
         */
        private function insertEmploymentAttendanceList( $initialValList, $unitArray, $attendanceArray, $targetKeyName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START insertEmploymentAttendanceList");

            if( ( $unitArray['organization_id'] == $attendanceArray['organization_id'] ) && ( $unitArray['employment_id'] === $attendanceArray['employment_id'] ) && ( $unitArray['target_period'] === $attendanceArray[$targetKeyName] ) )
            {
                $initialValList = $this->attendanceListTotalCalculation( $initialValList, $attendanceArray );
            }

            $Log->trace("END insertEmploymentAttendanceList");

            return $initialValList;
        }

/*******************************
* 個人以外勤怠一覧処理共通
********************************/

        /**
         * 検索対象累計単位（形態）のフラグをセット
         * @note     組織（1）役職（2）雇用形態（3）
         * @param    $mobileUnitName
         * @return   $unitFlag
         */
        private function setUnitFlag( $mobileUnitName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setUnitFlag");

            if( $mobileUnitName === $Log->getMsgLog('MSG_CUMULATIVE_ORGANIZATION') )
            {
                $unitFlag = 1;
            }
            else if( $mobileUnitName === $Log->getMsgLog('MSG_CUMULATIVE_POSITION') )
            {
                $unitFlag = 2;
            }
            else
            {
                $unitFlag = 3;
            }

            $Log->trace("END setUnitFlag");

            return $unitFlag;
        }

        /**
         * 累計単位のデータ取得
         * @param    $postArray
         * @param    $unitFlag
         * @return   $unitInfoList
         */
        private function getUnitList( $postArray, $unitFlag )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUnitList");

            $searchArray = array();
            if( $unitFlag == 1 )
            {
                $sql = $this->creatOrganizationSQL( $postArray, $searchArray );
            }
            else if( $unitFlag == 2 )
            {
                $sql = $this->creatPositionSQL( $postArray, $searchArray );
            }
            else
            {
                $sql = $this->creatEmploymentSQL( $postArray, $searchArray );
            }

            $infoList = $this->runListGet( $sql, $searchArray );

            $unitInfoList = $this->insertFirstDate( $infoList, $unitFlag );

            $Log->trace("END getUnitList");

            return $unitInfoList;

        }

        /**
         * 役職、雇用形態の適用開始時を設定
         * @note     指定期間の対象の判定に使用
         * @param    $infoList
         * @param    $unitFlag
         * @return   $unitInfoList
         */
        private function insertFirstDate( $infoList, $unitFlag )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START insertFirstDate");

            $unitInfoList = array();
            if( $unitFlag == 1 )
            {
                $unitInfoList = $infoList;
                $Log->trace("END insertFirstDate");
                return $unitInfoList;
            }
            
            foreach( $infoList as $info )
            {
                if( ( empty( $info['attendance_first'] ) ) || ( strtotime( $info['attendance_first'] ) >= strtotime( $info['organization_first'] ) ) )
                {
                    $info['first_date'] = $info['organization_first'];
                    array_push( $unitInfoList, $info );
                }
                else
                {
                    $info['first_date'] = $info['attendance_first'];
                    array_push( $unitInfoList, $info );
                }
            }

            $Log->trace("END insertFirstDate");

            return $unitInfoList;
        }

        /**
         * 累計単位のデータ取得
         * @param    $postArray
         * @param    $startDate
         * @param    $endDate
         * @param    $mainIdName
         * @return   $infoList
         */
        private function getUnitAttendanceListBranch( $postArray, $startDate, $endDate )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUnitAttendanceListBranch");

            $searchArray = array();
            if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_DAY') )
            {
                $sql = $this->creatUnitAttendanceListSQL( $postArray, $searchArray, $startDate, $endDate );
            }
            else if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') )
            {
                $sql = $this->creatUnitAttendanceListSQL( $postArray, $searchArray, $startDate, $endDate );
            }
            else if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_YEAR') )
            {
                $sql = $this->creatUnitAttendanceListSQL( $postArray, $searchArray, $startDate, $endDate );
            }
            else
            {
                $sql = $this->creatUnitAttendanceListSQL( $postArray, $searchArray, $startDate, $endDate );
            }

            $infoList = $this->runListGet( $sql, $searchArray );

            $Log->trace("END getUnitAttendanceListBranch");

            return $infoList;

        }

        /**
         * 集計行作成
         * @param    $attendanceSerch
         * @param    $attUnitCnt
         * @return   $unitSummaryRow
         */
        private function creatUnitSummaryRow( $attendanceSerch, $attUnitCnt )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatUnitSummaryRow");

            $aggregateList = $this->entireAggregate( $attendanceSerch );

            $unitSummaryRow = $this->changeFormatUnitSummaryRow( $aggregateList, $attUnitCnt );

            $Log->trace("END creatUnitSummaryRow");

            return $unitSummaryRow;
        }

        /**
         * 集計行の整理
         * @param    $aggregateList
         * @param    $attendanceCnt
         * @return   $unitSummaryRow
         */
        private function changeFormatUnitSummaryRow( $aggregateList, $attendanceCnt )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeFormatUnitSummaryRow");

            $unitSummaryRow = array();

            $unitSummaryRow['organization_id'] = 0;
            $unitSummaryRow['position_id'] = 0;
            $unitSummaryRow['employment_id'] = 0;
            $unitSummaryRow['periodDate'] = "-";
            $unitSummaryRow['target_period'] = "-";
            $unitSummaryRow['organization_name'] = "-";
            $unitSummaryRow['abbreviated_name'] = "-";
            $unitSummaryRow['department_code'] = "-";
            $unitSummaryRow['organization_comment'] = "-";
            $unitSummaryRow['position_name'] = "-";
            $unitSummaryRow['employment_name'] = "-";
            $unitSummaryRow['total_working_time_con_minute'] = $aggregateList['total_working_time_con_minute'];
            $unitSummaryRow['weekday_working_con_minute'] = $aggregateList['weekday_working_con_minute'];
            $unitSummaryRow['weekend_working_con_minute'] = $aggregateList['weekend_working_con_minute'];
            $unitSummaryRow['legal_holiday_working_con_minute'] = $aggregateList['legal_holiday_working_con_minute'];
            $unitSummaryRow['law_closed_working_con_minute'] = $aggregateList['law_closed_working_con_minute'];
            $unitSummaryRow['overtime_con_minute'] = $aggregateList['overtime_con_minute'];
            $unitSummaryRow['weekday_overtime_con_minute'] = $aggregateList['weekday_overtime_con_minute'];
            $unitSummaryRow['weekend_overtime_con_minute'] = $aggregateList['weekend_overtime_con_minute'];
            $unitSummaryRow['legal_holiday_overtime_con_minute'] = $aggregateList['legal_holiday_overtime_con_minute'];
            $unitSummaryRow['law_closed_overtime_con_minute'] = $aggregateList['law_closed_overtime_con_minute'];
            $unitSummaryRow['night_working_time_con_minute'] = $aggregateList['night_working_time_con_minute'];
            $unitSummaryRow['weekday_night_working_con_minute'] = $aggregateList['weekday_night_working_con_minute'];
            $unitSummaryRow['weekend_night_working_con_minute'] = $aggregateList['weekend_night_working_con_minute'];
            $unitSummaryRow['legal_holiday_night_working_con_minute'] = $aggregateList['legal_holiday_night_working_con_minute'];
            $unitSummaryRow['law_closed_night_working_con_minute'] = $aggregateList['law_closed_night_working_con_minute'];
            $unitSummaryRow['night_overtime_con_minute'] = $aggregateList['night_overtime_con_minute'];
            $unitSummaryRow['weekday_night_overtime_con_minute'] = $aggregateList['weekday_night_overtime_con_minute'];
            $unitSummaryRow['weekend_night_overtime_con_minute'] = $aggregateList['weekend_night_overtime_con_minute'];
            $unitSummaryRow['legal_holiday_night_overtime_con_minute'] = $aggregateList['legal_holiday_night_overtime_con_minute'];
            $unitSummaryRow['law_closed_night_overtime_con_minute'] = $aggregateList['law_closed_night_overtime_con_minute'];
            $unitSummaryRow['break_time_con_minute'] = $aggregateList['break_time_con_minute'];
            $unitSummaryRow['late_time_con_minute'] = $aggregateList['late_time_con_minute'];
            $unitSummaryRow['leave_early_time_con_minute'] = $aggregateList['leave_early_time'];
            $unitSummaryRow['travel_time_con_minute'] = $aggregateList['travel_time_con_minute'];
            $unitSummaryRow['late_leave_time_con_minute'] = $aggregateList['late_leave_time_con_minute'];
            $unitSummaryRow['modify_count'] =  $aggregateList['modify_count'];
            $unitSummaryRow['late_time_count'] = $aggregateList['late_time_count'];
            $unitSummaryRow['leave_early_time_count'] = $aggregateList['leave_early_time_count'];
            $unitSummaryRow['late_leave_count'] = $aggregateList['late_leave_count'];
            $unitSummaryRow['attendance_time_count'] = $aggregateList['attendance_time_count'];
            $unitSummaryRow['weekday_attendance_time_count'] = $aggregateList['weekday_attendance_time_count'];
            $unitSummaryRow['weekend_attendance_time_count'] = $aggregateList['weekend_attendance_time_count'];
            $unitSummaryRow['legal_holiday_attendance_time_count'] = $aggregateList['legal_holiday_attendance_time_count'];
            $unitSummaryRow['law_closed_attendance_time_count'] = $aggregateList['law_closed_attendance_time_count'];
            $unitSummaryRow['rough_estimate'] = $aggregateList['rough_estimate'];
            $unitSummaryRow['shift_rough_estimate'] = $aggregateList['shift_rough_estimate'];
            $unitSummaryRow['rough_estimate_month'] = $aggregateList['rough_estimate_month'];
            $unitSummaryRow['fixed_overtime_time'] = "-";
            $unitSummaryRow['absence_count'] = $aggregateList['absence_count'];
            $unitSummaryRow['prescribed_working_days'] = $aggregateList['prescribed_working_days'];
            $unitSummaryRow['prescribed_working_hours'] = $aggregateList['prescribed_working_hours'];
            $unitSummaryRow['weekday_prescribed_working_hours'] = $aggregateList['weekday_prescribed_working_hours'];
            $unitSummaryRow['weekend_prescribed_working_hours'] = $aggregateList['weekend_prescribed_working_hours'];
            $unitSummaryRow['legal_holiday_prescribed_working_hours'] = $aggregateList['legal_holiday_prescribed_working_hours'];
            $unitSummaryRow['statutory_overtime_hours'] = $aggregateList['statutory_overtime_hours'];
            $unitSummaryRow['nonstatutory_overtime_hours_all'] = $aggregateList['nonstatutory_overtime_hours_all'];
            $unitSummaryRow['nonstatutory_overtime_hours'] = $aggregateList['nonstatutory_overtime_hours'];
            $unitSummaryRow['nonstatutory_overtime_hours_45h'] = $aggregateList['nonstatutory_overtime_hours_45h'];
            $unitSummaryRow['nonstatutory_overtime_hours_less_than'] = $aggregateList['nonstatutory_overtime_hours_less_than'];
            $unitSummaryRow['nonstatutory_overtime_hours_60h'] = $aggregateList['nonstatutory_overtime_hours_60h'];
            $unitSummaryRow['statutory_overtime_hours_no_pub'] = $aggregateList['statutory_overtime_hours_no_pub'];
            $unitSummaryRow['nonstatutory_overtime_hours_no_pub_all'] = $aggregateList['nonstatutory_overtime_hours_no_pub_all'];
            $unitSummaryRow['nonstatutory_overtime_hours_no_pub'] = $aggregateList['nonstatutory_overtime_hours_no_pub'];
            $unitSummaryRow['nonstatutory_overtime_hours_45h_no_pub'] = $aggregateList['nonstatutory_overtime_hours_45h_no_pub'];
            $unitSummaryRow['nonstatutory_overtime_hours_no_pub_less_than'] = $aggregateList['nonstatutory_overtime_hours_no_pub_less_than'];
            $unitSummaryRow['nonstatutory_overtime_hours_60h_no_pub'] = $aggregateList['nonstatutory_overtime_hours_60h_no_pub'];
            $unitSummaryRow['overtime_hours_no_considered'] = $aggregateList['overtime_hours_no_considered'];
            $unitSummaryRow['overtime_hours_no_considered_no_pub'] = $aggregateList['overtime_hours_no_considered_no_pub'];
            $unitSummaryRow['normal_working_con_minute'] = $aggregateList['normal_working_con_minute'];
            $unitSummaryRow['normal_overtime_con_minute'] = $aggregateList['normal_overtime_con_minute'];
            $unitSummaryRow['normal_night_working_con_minute'] = $aggregateList['normal_night_working_con_minute'];
            $unitSummaryRow['normal_night_overtime_con_minute'] = $aggregateList['normal_night_overtime_con_minute'];

            // millionet oota 追加
            $unitSummaryRow['weekday_normal_time'] = $aggregateList['weekday_normal_time'];
            $unitSummaryRow['weekday_midnight_time_only'] = $aggregateList['weekday_midnight_time_only'];
            $unitSummaryRow['holiday_normal_time'] = $aggregateList['holiday_normal_time'];
            $unitSummaryRow['holiday_midnight_time_only'] = $aggregateList['holiday_midnight_time_only'];
            $unitSummaryRow['statutory_holiday_normal_time'] = $aggregateList['statutory_holiday_normal_time'];
            $unitSummaryRow['statutory_holiday_midnight_time_only'] = $aggregateList['statutory_holiday_midnight_time_only'];
            $unitSummaryRow['public_holiday_normal_time'] = $aggregateList['public_holiday_normal_time'];
            $unitSummaryRow['public_holiday_midnight_time_only'] = $aggregateList['public_holiday_midnight_time_only'];
            
            $Log->trace("END changeFormatUnitSummaryRow");

            return $unitSummaryRow;
        }

    }

?>