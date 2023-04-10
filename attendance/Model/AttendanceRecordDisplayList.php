<?php
    /**
     * @file      勤怠一覧表示データ
     * @author    USE M.Higashihara
     * @date      2016/07/15
     * @version   1.00
     * @note      勤怠一覧表示用データの制御
     */

    // BaseModel.phpを読み込む
    require './Model/AttendanceRecordSqlInstallation.php';

    /**
     * 勤怠一覧画面表示データ制御
     * @note   勤怠一覧画面の表示するデータの取得/加工
     */
    class AttendanceRecordDisplayList extends AttendanceRecordSqlInstallation
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
         * 個人勤怠一覧リスト作成
         * @param    $postArray
         * @return   $attendanceUserList
         */
        protected function creatAttendanceUserList( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAttendanceUserList");
            
            if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_DAY') || $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_TIME') )
            {
                $attendanceUserList = $this->creatUserPeriodDayList( $postArray );
            }
            else
            {
                // 指定期間（月/年）対象の従業員リスト取得
                $addingUpUserList = $this->creatAddingUpUserList( $postArray );
                // 検索条件による対象外リストのキーNoリストを取得
                $keyNoList = $this->setKeyNoList( $postArray, $addingUpUserList );
                // 対象外リストの重複を削除して、キーを整理する
                $keyNoList = $this->setUniqueArray( $keyNoList );
                // 検索対象外キーの行を削除して配列の再構築
                $attUnitCnt = 1;
                $attendanceSerch = $this->liquidationAttendanceList( $addingUpUserList, $keyNoList, $attUnitCnt );
                // 閲覧権限によるソート処理
                $attendanceUserList = $this->sortAttendanceListAccessLevel( $postArray, $attendanceSerch );
                // 集計行
                $userMonthSummaryRow = $this->creatUserSummaryRow( $attendanceSerch, $attUnitCnt, 1 );
                // 集計行挿入
                array_push( $attendanceUserList, $userMonthSummaryRow );
            }

            $Log->trace("END creatAttendanceUserList");

            return $attendanceUserList;
        }

        /**
         * 検索対象期間をプラス1か月または1年とする
         * @param    $dateTimeUnit
         * @param    $endUnix
         * @return   $endUnixOnePlus
         */
        protected function setEndUnixOnePlus( $dateTimeUnit, $endUnix )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setEndUnixOnePlus");

            if( $dateTimeUnit === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') )
            {
                $endUnixOnePlus = ( $endUnix + 2678400 );
            }
            else
            {
                $endUnixOnePlus = ( $endUnix + 31622400 );
            }

            $Log->trace("END setEndUnixOnePlus");

            return $endUnixOnePlus;
        }

        /**
         * 対象期間リスト作成(月/年)
         * @param    $dateTimeUnit
         * @param    $startUnix
         * @param    $endUnix
         * @param    &$periodName
         * @return   $targetPeriodList
         */
        protected function setTargetPeriodList( $dateTimeUnit, $startUnix, $endUnix, &$periodName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setTargetPeriodList");

            $targetPeriodList = array();
            if( $dateTimeUnit === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') )
            {
                $targetPeriodList = $this->creatMonthStartEndList( date("Y", $startUnix), date("m", $startUnix), date("Y", $endUnix), date("m", $endUnix) );
                $periodName = "targetMonth";
            }
            else if( $dateTimeUnit === $Log->getMsgLog('MSG_CUMULATIVE_YEAR') )
            {
                $targetPeriodList = $this->creatYearStartEndList( date("Y", $startUnix), date("Y", $endUnix) );
                $periodName = "targetYear";
            }

            $Log->trace("END setTargetPeriodList");

            return $targetPeriodList;
        }

        /**
         * 月/年キー名設定
         * @param    $dateTimeUnit
         * @return   $targetKeyName
         */
        protected function setTargetKeyName( $dateTimeUnit )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setTargetKeyName");

            $targetKeyName = "";
            if( $dateTimeUnit === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') )
            {
                $targetKeyName = "target_month_tightening";
            }
            else if( $dateTimeUnit === $Log->getMsgLog('MSG_CUMULATIVE_YEAR'))
            {
                $targetKeyName = "target_year_tighten";
            }

            $Log->trace("END setTargetKeyName");

            return $targetKeyName;
        }

        /**
         * 月合算初期値
         * @return   $initialValList
         */
        protected function setInitialValList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setInitialValList");

            $initialValList = array(
                'total_working_time_con_minute'                => 0,
                'weekday_working_con_minute'                   => 0,
                'weekend_working_con_minute'                   => 0,
                'legal_holiday_working_con_minute'             => 0,
                'law_closed_working_con_minute'                => 0,
                'overtime_con_minute'                          => 0,
                'weekday_overtime_con_minute'                  => 0,
                'weekend_overtime_con_minute'                  => 0,
                'legal_holiday_overtime_con_minute'            => 0,
                'law_closed_overtime_con_minute'               => 0,
                'night_working_time_con_minute'                => 0,
                'weekday_night_working_con_minute'             => 0,
                'weekend_night_working_con_minute'             => 0,
                'legal_holiday_night_working_con_minute'       => 0,
                'law_closed_night_working_con_minute'          => 0,
                'night_overtime_con_minute'                    => 0,
                'weekday_night_overtime_con_minute'            => 0,
                'weekend_night_overtime_con_minute'            => 0,
                'legal_holiday_night_overtime_con_minute'      => 0,
                'law_closed_night_overtime_con_minute'         => 0,
                'break_time_con_minute'                        => 0,
                'late_time_con_minute'                         => 0,
                'leave_early_time_con_minute'                  => 0,
                'travel_time_con_minute'                       => 0,
                'late_leave_time_con_minute'                   => 0,
                'modify_count'                                 => "",
                'fixed_overtime_time'                          => "",
                'late_time_count'                              => "",
                'leave_early_time_count'                       => "",
                'late_leave_count'                             => "",
                'attendance_time_count'                        => "",
                'weekday_attendance_time_count'                => "",
                'weekend_attendance_time_count'                => "",
                'legal_holiday_attendance_time_count'          => "",
                'law_closed_attendance_time_count'             => "",
                'rough_estimate'                               => "",
                'shift_rough_estimate'                         => "",
                'rough_estimate_month'                         => "",
                'absence_count'                                => "",
                'prescribed_working_days'                      => "",
                'prescribed_working_hours'                     => "",
                'weekday_prescribed_working_hours'             => "",
                'weekend_prescribed_working_hours'             => "",
                'legal_holiday_prescribed_working_hours'       => "",
                'law_closed_prescribed_working_hours'          => "",
                'statutory_overtime_hours'                     => "",
                'nonstatutory_overtime_hours_all'              => "",
                'nonstatutory_overtime_hours'                  => "",
                'nonstatutory_overtime_hours_45h'              => "",
                'nonstatutory_overtime_hours_less_than'        => "",
                'nonstatutory_overtime_hours_60h'              => "",
                'statutory_overtime_hours_no_pub'              => "",
                'nonstatutory_overtime_hours_no_pub_all'       => "",
                'nonstatutory_overtime_hours_no_pub'           => "",
                'nonstatutory_overtime_hours_45h_no_pub'       => "",
                'nonstatutory_overtime_hours_no_pub_less_than' => "",
                'nonstatutory_overtime_hours_60h_no_pub'       => "",
                'overtime_hours_no_considered'                 => "",
                'overtime_hours_no_considered_no_pub'          => "",
                'normal_working_con_minute'                    => "",
                'normal_overtime_con_minute'                   => "",
                'normal_night_working_con_minute'              => "",
                'normal_night_overtime_con_minute'             => "",
                'embossing_organization_id'                    => "",
                'embossing_department_code'                    => "",
                'embossing_organization_name'                  => "",
                'embossing_abbreviated_name'                   => "",
                'embossing_status'                             => "",
                'work_classification'                          => "",
                'weekday_normal_time'                          => "",
                'weekday_midnight_time_only'                   => "",
                'holiday_normal_time'                          => "",
                'holiday_midnight_time_only'                   => "",
                'statutory_holiday_normal_time'                => "",
                'statutory_holiday_midnight_time_only'         => "",
                'public_holiday_normal_time'                   => "",
                'public_holiday_midnight_time_only'            => "",
            );

            $Log->trace("END setInitialValList");

            return $initialValList;
        }

        /**
         * 従業員勤怠月合算処理
         * @param    $initialValList
         * @param    $attendanceArray
         * @return   $addingUpUserList
         */
        protected function attendanceListTotalCalculation( $initialValList, $attendanceArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START attendanceListTotalCalculation");

            $initialValList['total_working_time_con_minute'] = $initialValList['total_working_time_con_minute'] + $attendanceArray['total_working_time_con_minute'];
            $initialValList['weekday_working_con_minute'] = $initialValList['weekday_working_con_minute'] + $attendanceArray['weekday_working_con_minute'];
            $initialValList['weekend_working_con_minute'] = $initialValList['weekend_working_con_minute'] + $attendanceArray['weekend_working_con_minute'];
            $initialValList['legal_holiday_working_con_minute'] = $initialValList['legal_holiday_working_con_minute'] + $attendanceArray['legal_holiday_working_con_minute'];
            $initialValList['law_closed_working_con_minute'] = $initialValList['law_closed_working_con_minute'] + $attendanceArray['law_closed_working_con_minute'];
            $initialValList['overtime_con_minute'] = $initialValList['overtime_con_minute'] + $attendanceArray['overtime_con_minute'];
            $initialValList['weekday_overtime_con_minute'] = $initialValList['weekday_overtime_con_minute'] + $attendanceArray['weekday_overtime_con_minute'];
            $initialValList['weekend_overtime_con_minute'] = $initialValList['weekend_overtime_con_minute'] + $attendanceArray['weekend_overtime_con_minute'];
            $initialValList['legal_holiday_overtime_con_minute'] = $initialValList['legal_holiday_overtime_con_minute'] + $attendanceArray['legal_holiday_overtime_con_minute'];
            $initialValList['law_closed_overtime_con_minute'] = $initialValList['law_closed_overtime_con_minute'] + $attendanceArray['law_closed_overtime_con_minute'];
            $initialValList['night_working_time_con_minute'] = $initialValList['night_working_time_con_minute'] + $attendanceArray['night_working_time_con_minute'];
            $initialValList['weekday_night_working_con_minute'] = $initialValList['weekday_night_working_con_minute'] + $attendanceArray['weekday_night_working_con_minute'];
            $initialValList['weekend_night_working_con_minute'] = $initialValList['weekend_night_working_con_minute'] + $attendanceArray['weekend_night_working_con_minute'];
            $initialValList['legal_holiday_night_working_con_minute'] = $initialValList['legal_holiday_night_working_con_minute'] + $attendanceArray['legal_holiday_night_working_con_minute'];
            $initialValList['law_closed_night_working_con_minute'] = $initialValList['law_closed_night_working_con_minute'] + $attendanceArray['law_closed_night_working_con_minute'];
            $initialValList['night_overtime_con_minute'] = $initialValList['night_overtime_con_minute'] + $attendanceArray['night_overtime_con_minute'];
            $initialValList['weekday_night_overtime_con_minute'] = $initialValList['weekday_night_overtime_con_minute'] + $attendanceArray['weekday_night_overtime_con_minute'];
            $initialValList['weekend_night_overtime_con_minute'] = $initialValList['weekend_night_overtime_con_minute'] + $attendanceArray['weekend_night_overtime_con_minute'];
            $initialValList['legal_holiday_night_overtime_con_minute'] = $initialValList['legal_holiday_night_overtime_con_minute'] + $attendanceArray['legal_holiday_night_overtime_con_minute'];
            $initialValList['law_closed_night_overtime_con_minute'] = $initialValList['law_closed_night_overtime_con_minute'] + $attendanceArray['law_closed_night_overtime_con_minute'];
            $initialValList['break_time_con_minute'] = $initialValList['break_time_con_minute'] + $attendanceArray['break_time_con_minute'];
            $initialValList['late_time_con_minute'] = $initialValList['late_time_con_minute'] + $attendanceArray['late_time_con_minute'];
            $initialValList['leave_early_time_con_minute'] = $initialValList['leave_early_time_con_minute'] + $attendanceArray['leave_early_time_con_minute'];
            $initialValList['travel_time_con_minute'] = $initialValList['travel_time_con_minute'] + $attendanceArray['travel_time_con_minute'];
            $initialValList['late_leave_time_con_minute'] = $initialValList['late_leave_time_con_minute'] + $attendanceArray['late_leave_time_con_minute'];
            $initialValList['fixed_overtime_time'] = $attendanceArray['fixed_overtime_time'];
            $initialValList['modify_count'] = $initialValList['modify_count'] + $attendanceArray['modify_count'];
            $initialValList['late_time_count'] = $initialValList['late_time_count'] + $attendanceArray['late_time_count'];
            $initialValList['leave_early_time_count'] = $initialValList['leave_early_time_count'] + $attendanceArray['leave_early_time_count'];
            $initialValList['late_leave_count'] = $initialValList['late_leave_count'] + $attendanceArray['late_leave_count'];
            $initialValList['attendance_time_count'] = $initialValList['attendance_time_count'] + $attendanceArray['attendance_time_count'];
            $initialValList['weekday_attendance_time_count'] = $initialValList['weekday_attendance_time_count'] + $attendanceArray['weekday_attendance_time_count'];
            $initialValList['weekend_attendance_time_count'] = $initialValList['weekend_attendance_time_count'] + $attendanceArray['weekend_attendance_time_count'];
            $initialValList['legal_holiday_attendance_time_count'] = $initialValList['legal_holiday_attendance_time_count'] + $attendanceArray['legal_holiday_attendance_time_count'];
            $initialValList['law_closed_attendance_time_count'] = $initialValList['law_closed_attendance_time_count'] + $attendanceArray['law_closed_attendance_time_count'];
            $initialValList['rough_estimate'] = $initialValList['rough_estimate'] + $attendanceArray['rough_estimate'];
            $initialValList['shift_rough_estimate'] = $initialValList['shift_rough_estimate'] + $attendanceArray['shift_rough_estimate'];
            $initialValList['rough_estimate_month'] = $initialValList['rough_estimate_month'] + $attendanceArray['rough_estimate_month'];
            $initialValList['absence_count'] = $initialValList['absence_count'] + $attendanceArray['absence_count'];
            $initialValList['prescribed_working_days'] = $initialValList['prescribed_working_days'] + $attendanceArray['prescribed_working_days'];
            $initialValList['prescribed_working_hours'] = $initialValList['prescribed_working_hours'] + $attendanceArray['prescribed_working_hours'];
            $initialValList['weekday_prescribed_working_hours'] = $initialValList['weekday_prescribed_working_hours'] + $attendanceArray['weekday_prescribed_working_hours'];
            $initialValList['weekend_prescribed_working_hours'] = $initialValList['weekend_prescribed_working_hours'] + $attendanceArray['weekend_prescribed_working_hours'];
            $initialValList['legal_holiday_prescribed_working_hours'] = $initialValList['legal_holiday_prescribed_working_hours'] + $attendanceArray['legal_holiday_prescribed_working_hours'];
            $initialValList['law_closed_prescribed_working_hours'] = $initialValList['law_closed_prescribed_working_hours'] + $attendanceArray['law_closed_prescribed_working_hours'];
            $initialValList['statutory_overtime_hours'] = $initialValList['statutory_overtime_hours'] + $attendanceArray['statutory_overtime_hours'];
            $initialValList['nonstatutory_overtime_hours_all'] = $initialValList['nonstatutory_overtime_hours_all'] + $attendanceArray['nonstatutory_overtime_hours_all'];
            $initialValList['nonstatutory_overtime_hours'] = $initialValList['nonstatutory_overtime_hours'] + $attendanceArray['nonstatutory_overtime_hours'];
            $initialValList['nonstatutory_overtime_hours_45h'] = $initialValList['nonstatutory_overtime_hours_45h'] + $attendanceArray['nonstatutory_overtime_hours_45h'];
            $initialValList['nonstatutory_overtime_hours_less_than'] = $initialValList['nonstatutory_overtime_hours_less_than'] + $attendanceArray['nonstatutory_overtime_hours_less_than'];
            $initialValList['nonstatutory_overtime_hours_60h'] = $initialValList['nonstatutory_overtime_hours_60h'] + $attendanceArray['nonstatutory_overtime_hours_60h'];
            $initialValList['statutory_overtime_hours_no_pub'] = $initialValList['statutory_overtime_hours_no_pub'] + $attendanceArray['statutory_overtime_hours_no_pub'];
            $initialValList['nonstatutory_overtime_hours_no_pub_all'] = $initialValList['nonstatutory_overtime_hours_no_pub_all'] + $attendanceArray['nonstatutory_overtime_hours_no_pub_all'];
            $initialValList['nonstatutory_overtime_hours_no_pub'] = $initialValList['nonstatutory_overtime_hours_no_pub'] + $attendanceArray['nonstatutory_overtime_hours_no_pub'];
            $initialValList['nonstatutory_overtime_hours_45h_no_pub'] = $initialValList['nonstatutory_overtime_hours_45h_no_pub'] + $attendanceArray['nonstatutory_overtime_hours_45h_no_pub'];
            $initialValList['nonstatutory_overtime_hours_no_pub_less_than'] = $initialValList['nonstatutory_overtime_hours_no_pub_less_than'] + $attendanceArray['nonstatutory_overtime_hours_no_pub_less_than'];
            $initialValList['nonstatutory_overtime_hours_60h_no_pub'] = $initialValList['nonstatutory_overtime_hours_60h_no_pub'] + $attendanceArray['nonstatutory_overtime_hours_60h_no_pub'];
            $initialValList['overtime_hours_no_considered'] = $initialValList['overtime_hours_no_considered'] + $attendanceArray['overtime_hours_no_considered'];
            $initialValList['overtime_hours_no_considered_no_pub'] = $initialValList['overtime_hours_no_considered_no_pub'] + $attendanceArray['overtime_hours_no_considered_no_pub'];
            $initialValList['normal_working_con_minute'] = $initialValList['normal_working_con_minute'] + $attendanceArray['normal_working_con_minute'];
            $initialValList['normal_overtime_con_minute'] = $initialValList['normal_overtime_con_minute'] + $attendanceArray['normal_overtime_con_minute'];
            $initialValList['normal_night_working_con_minute'] = $initialValList['normal_night_working_con_minute'] + $attendanceArray['normal_night_working_con_minute'];
            $initialValList['normal_night_overtime_con_minute'] = $initialValList['normal_night_overtime_con_minute'] + $attendanceArray['normal_night_overtime_con_minute'];
            $initialValList['embossing_organization_id'] = $attendanceArray['embossing_organization_id'];
            $initialValList['embossing_department_code'] = $attendanceArray['embossing_department_code'];
            $initialValList['embossing_organization_name'] = $attendanceArray['embossing_organization_name'];
            $initialValList['embossing_abbreviated_name'] = $attendanceArray['embossing_abbreviated_name'];
            $initialValList['embossing_status'] = $attendanceArray['embossing_status'];
            $initialValList['work_classification'] = $attendanceArray['work_classification'];

            // millionet oota 追加
            $initialValList['weekday_normal_time'] = $initialValList['weekday_normal_time'] + $attendanceArray['weekday_normal_time'];
            $initialValList['weekday_midnight_time_only'] = $initialValList['weekday_midnight_time_only'] + $attendanceArray['weekday_midnight_time_only'];
            $initialValList['holiday_normal_time'] = $initialValList['holiday_normal_time'] + $attendanceArray['holiday_normal_time'];
            $initialValList['holiday_midnight_time_only'] = $initialValList['holiday_midnight_time_only'] + $attendanceArray['holiday_midnight_time_only'];
            $initialValList['statutory_holiday_normal_time'] = $initialValList['statutory_holiday_normal_time'] + $attendanceArray['statutory_holiday_normal_time'];
            $initialValList['statutory_holiday_midnight_time_only'] = $initialValList['statutory_holiday_midnight_time_only'] + $attendanceArray['statutory_holiday_midnight_time_only'];
            $initialValList['public_holiday_normal_time'] = $initialValList['public_holiday_normal_time'] + $attendanceArray['public_holiday_normal_time'];
            $initialValList['public_holiday_midnight_time_only'] = $initialValList['public_holiday_midnight_time_only'] + $attendanceArray['public_holiday_midnight_time_only'];
            
            $Log->trace("END attendanceListTotalCalculation");

            return $initialValList;
        }

        /**
         * 打刻状況の検索結果以外消去
         * @param    $addingUpList
         * @param    $postArray
         * @param    $keyName
         * @param    $attendanceKey
         * @return   $embossingList
         */
        protected function refineEmbossingSituation( $addingUpList, $postArray, $postKey, $attendanceKey )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START refineEmbossingSituation");

            $embossingList = array();
            if( !empty( $postArray[$postKey] ) )
            {
                foreach( $addingUpList as $adding )
                {
                    if( !empty( $adding[$attendanceKey] ) )
                    {
                        array_push( $embossingList, $adding );
                    }
                }
            }
            else
            {
                $embossingList = $addingUpList;
            }

            $Log->trace("END refineEmbossingSituation");

            return $embossingList;
        }

        /**
         * 検索対象外キーの行を削除して配列の再構築
         * @param    $addingUpList
         * @param    $keyNoList
         * @param    &$attUnitCnt
         * @return   $attendanceList
         */
        protected function liquidationAttendanceList( $addingUpList, $keyNoList, &$attUnitCnt )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START liquidationAttendanceList");

            $attendanceList = array();
            $keyCnt = 0;
            foreach( $addingUpList as $adding )
            {
                if( !in_array( $keyCnt, $keyNoList ) )
                {
                    $adding['attendance_unit_id'] = $attUnitCnt;
                    array_push( $attendanceList, $adding );
                    $attUnitCnt++;
                }
                $keyCnt++;
            }

            $Log->trace("END liquidationAttendanceList");

            return $attendanceList;
        }

        /**
         * 集計
         * @param    $attendanceList
         * @return   $attendanceNullList
         */
        protected function entireAggregate( $attendanceList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START entireAggregate");

            $attendanceNullList = $this->setAttendanceNullList();

            foreach( $attendanceList as $attendance )
            {
                $attendanceNullList['break_time_con_minute'] = $attendanceNullList['break_time_con_minute'] + $attendance['break_time_con_minute'];
                $attendanceNullList['late_time_con_minute'] = $attendanceNullList['late_time_con_minute'] + $attendance['late_time_con_minute'];
                $attendanceNullList['leave_early_time_con_minute'] = $attendanceNullList['leave_early_time_con_minute'] + $attendance['leave_early_time_con_minute'];
                $attendanceNullList['travel_time'] = $attendanceNullList['travel_time'] + $attendance['travel_time_con_minute'];
                $attendanceNullList['late_leave_time_con_minute'] = $attendanceNullList['late_leave_time_con_minute'] + $attendance['late_leave_time_con_minute'];
                $attendanceNullList['total_working_time_con_minute'] = $attendanceNullList['total_working_time_con_minute'] + $attendance['total_working_time_con_minute'];
                $attendanceNullList['night_working_time_con_minute'] = $attendanceNullList['night_working_time_con_minute'] + $attendance['night_working_time_con_minute'];
                $attendanceNullList['overtime_con_minute'] = $attendanceNullList['overtime_con_minute'] + $attendance['overtime_con_minute'];
                $attendanceNullList['night_overtime_con_minute'] = $attendanceNullList['night_overtime_con_minute'] + $attendance['night_overtime_con_minute'];
                $attendanceNullList['modify_count'] = $attendanceNullList['modify_count'] + $attendance['modify_count'];
                $attendanceNullList['late_time_count'] = $attendanceNullList['late_time_count'] + $attendance['late_time_count'];
                $attendanceNullList['leave_early_time_count'] = $attendanceNullList['leave_early_time_count'] + $attendance['leave_early_time_count'];
                $attendanceNullList['late_leave_count'] = $attendanceNullList['late_leave_count'] + $attendance['late_leave_count'];
                $attendanceNullList['attendance_time_count'] = $attendanceNullList['attendance_time_count'] + $attendance['attendance_time_count'];
                $attendanceNullList['weekday_attendance_time_count'] = $attendanceNullList['weekday_attendance_time_count'] + $attendance['weekday_attendance_time_count'];
                $attendanceNullList['weekend_attendance_time_count'] = $attendanceNullList['weekend_attendance_time_count'] + $attendance['weekend_attendance_time_count'];
                $attendanceNullList['legal_holiday_attendance_time_count'] = $attendanceNullList['legal_holiday_attendance_time_count'] + $attendance['legal_holiday_attendance_time_count'];
                $attendanceNullList['law_closed_attendance_time_count'] = $attendanceNullList['law_closed_attendance_time_count'] + $attendance['law_closed_attendance_time_count'];
                $attendanceNullList['weekday_working_con_minute'] = $attendanceNullList['weekday_working_con_minute'] + $attendance['weekday_working_con_minute'];
                $attendanceNullList['weekend_working_con_minute'] = $attendanceNullList['weekend_working_con_minute'] + $attendance['weekend_working_con_minute'];
                $attendanceNullList['legal_holiday_working_con_minute'] = $attendanceNullList['legal_holiday_working_con_minute'] + $attendance['legal_holiday_working_con_minute'];
                $attendanceNullList['law_closed_working_con_minute'] = $attendanceNullList['law_closed_working_con_minute'] + $attendance['law_closed_working_con_minute'];
                $attendanceNullList['weekday_night_working_con_minute'] = $attendanceNullList['weekday_night_working_con_minute'] + $attendance['weekday_night_working_con_minute'];
                $attendanceNullList['weekend_night_working_con_minute'] = $attendanceNullList['weekend_night_working_con_minute'] + $attendance['weekend_night_working_con_minute'];
                $attendanceNullList['legal_holiday_night_working_con_minute'] = $attendanceNullList['legal_holiday_night_working_con_minute'] + $attendance['legal_holiday_night_working_con_minute'];
                $attendanceNullList['law_closed_night_working_con_minute'] = $attendanceNullList['law_closed_night_working_con_minute'] + $attendance['law_closed_night_working_con_minute'];
                $attendanceNullList['weekday_overtime_con_minute'] = $attendanceNullList['weekday_overtime_con_minute'] + $attendance['weekday_overtime_con_minute'];
                $attendanceNullList['weekend_overtime_con_minute'] = $attendanceNullList['weekend_overtime_con_minute'] + $attendance['weekend_overtime_con_minute'];
                $attendanceNullList['legal_holiday_overtime_con_minute'] = $attendanceNullList['legal_holiday_overtime_con_minute'] + $attendance['legal_holiday_overtime_con_minute'];
                $attendanceNullList['law_closed_overtime_con_minute'] = $attendanceNullList['law_closed_overtime_con_minute'] + $attendance['law_closed_overtime_con_minute'];
                $attendanceNullList['weekday_night_overtime_con_minute'] = $attendanceNullList['weekday_night_overtime_con_minute'] + $attendance['weekday_night_overtime_con_minute'];
                $attendanceNullList['weekend_night_overtime_con_minute'] = $attendanceNullList['weekend_night_overtime_con_minute'] + $attendance['weekend_night_overtime_con_minute'];
                $attendanceNullList['legal_holiday_night_overtime_con_minute'] = $attendanceNullList['legal_holiday_night_overtime_con_minute'] + $attendance['legal_holiday_night_overtime_con_minute'];
                $attendanceNullList['law_closed_night_overtime_con_minute'] = $attendanceNullList['law_closed_night_overtime_con_minute'] + $attendance['law_closed_night_overtime_con_minute'];
                $attendanceNullList['rough_estimate'] = $attendanceNullList['rough_estimate'] + $attendance['rough_estimate'];
                $attendanceNullList['shift_rough_estimate'] = $attendanceNullList['shift_rough_estimate'] + $attendance['shift_rough_estimate'];
                $attendanceNullList['rough_estimate_month'] = $attendanceNullList['rough_estimate_month'] + $attendance['rough_estimate_month'];
                $attendanceNullList['fixed_overtime_time'] = $attendanceNullList['fixed_overtime_time'] + $attendance['fixed_overtime_time'];
                $attendanceNullList['absence_count'] = $attendanceNullList['absence_count'] + $attendance['absence_count'];
                $attendanceNullList['prescribed_working_days'] = $attendanceNullList['prescribed_working_days'] + $attendance['prescribed_working_days'];
                $attendanceNullList['prescribed_working_hours'] = $attendanceNullList['prescribed_working_hours'] + $attendance['prescribed_working_hours'];
                $attendanceNullList['weekday_prescribed_working_hours'] = $attendanceNullList['weekday_prescribed_working_hours'] + $attendance['weekday_prescribed_working_hours'];
                $attendanceNullList['weekend_prescribed_working_hours'] = $attendanceNullList['weekend_prescribed_working_hours'] + $attendance['weekend_prescribed_working_hours'];
                $attendanceNullList['legal_holiday_prescribed_working_hours'] = $attendanceNullList['legal_holiday_prescribed_working_hours'] + $attendance['legal_holiday_prescribed_working_hours'];
                $attendanceNullList['law_closed_prescribed_working_hours'] = $attendanceNullList['law_closed_prescribed_working_hours'] + $attendance['law_closed_prescribed_working_hours'];
                $attendanceNullList['statutory_overtime_hours'] = $attendanceNullList['statutory_overtime_hours'] + $attendance['statutory_overtime_hours'];
                $attendanceNullList['nonstatutory_overtime_hours_all'] = $attendanceNullList['nonstatutory_overtime_hours_all'] + $attendance['nonstatutory_overtime_hours_all'];
                $attendanceNullList['nonstatutory_overtime_hours'] = $attendanceNullList['nonstatutory_overtime_hours'] + $attendance['nonstatutory_overtime_hours'];
                $attendanceNullList['nonstatutory_overtime_hours_45h'] = $attendanceNullList['nonstatutory_overtime_hours_45h'] + $attendance['nonstatutory_overtime_hours_45h'];
                $attendanceNullList['nonstatutory_overtime_hours_less_than'] = $attendanceNullList['nonstatutory_overtime_hours_less_than'] + $attendance['nonstatutory_overtime_hours_less_than'];
                $attendanceNullList['nonstatutory_overtime_hours_60h'] = $attendanceNullList['nonstatutory_overtime_hours_60h'] + $attendance['nonstatutory_overtime_hours_60h'];
                $attendanceNullList['statutory_overtime_hours_no_pub'] = $attendanceNullList['statutory_overtime_hours_no_pub'] + $attendance['statutory_overtime_hours_no_pub'];
                $attendanceNullList['nonstatutory_overtime_hours_no_pub_all'] = $attendanceNullList['nonstatutory_overtime_hours_no_pub_all'] + $attendance['nonstatutory_overtime_hours_no_pub_all'];
                $attendanceNullList['nonstatutory_overtime_hours_no_pub'] = $attendanceNullList['nonstatutory_overtime_hours_no_pub'] + $attendance['nonstatutory_overtime_hours_no_pub'];
                $attendanceNullList['nonstatutory_overtime_hours_45h_no_pub'] = $attendanceNullList['nonstatutory_overtime_hours_45h_no_pub'] + $attendance['nonstatutory_overtime_hours_45h_no_pub'];
                $attendanceNullList['nonstatutory_overtime_hours_no_pub_less_than'] = $attendanceNullList['nonstatutory_overtime_hours_no_pub_less_than'] + $attendance['nonstatutory_overtime_hours_no_pub_less_than'];
                $attendanceNullList['nonstatutory_overtime_hours_60h_no_pub'] = $attendanceNullList['nonstatutory_overtime_hours_60h_no_pub'] + $attendance['nonstatutory_overtime_hours_60h_no_pub'];
                $attendanceNullList['overtime_hours_no_considered'] = $attendanceNullList['overtime_hours_no_considered'] + $attendance['overtime_hours_no_considered'];
                $attendanceNullList['overtime_hours_no_considered_no_pub'] = $attendanceNullList['overtime_hours_no_considered_no_pub'] + $attendance['overtime_hours_no_considered_no_pub'];
                $attendanceNullList['normal_working_con_minute'] = $attendanceNullList['normal_working_con_minute'] + $attendance['normal_working_con_minute'];
                $attendanceNullList['normal_overtime_con_minute'] = $attendanceNullList['normal_overtime_con_minute'] + $attendance['normal_overtime_con_minute'];
                $attendanceNullList['normal_night_working_con_minute'] = $attendanceNullList['normal_night_working_con_minute'] + $attendance['normal_night_working_con_minute'];
                $attendanceNullList['normal_night_overtime_con_minute'] = $attendanceNullList['normal_night_overtime_con_minute'] + $attendance['normal_night_overtime_con_minute'];

                // millionet oota 追加
                $attendanceNullList['weekday_normal_time'] = $attendanceNullList['weekday_normal_time'] + $attendance['weekday_normal_time'];
                $attendanceNullList['weekday_midnight_time_only'] = $attendanceNullList['weekday_midnight_time_only'] + $attendance['weekday_midnight_time_only'];
                $attendanceNullList['holiday_normal_time'] = $attendanceNullList['holiday_normal_time'] + $attendance['holiday_normal_time'];
                $attendanceNullList['holiday_midnight_time_only'] = $attendanceNullList['holiday_midnight_time_only'] + $attendance['holiday_midnight_time_only'];
                $attendanceNullList['statutory_holiday_normal_time'] = $attendanceNullList['statutory_holiday_normal_time'] + $attendance['statutory_holiday_normal_time'];
                $attendanceNullList['statutory_holiday_midnight_time_only'] = $attendanceNullList['statutory_holiday_midnight_time_only'] + $attendance['statutory_holiday_midnight_time_only'];
                $attendanceNullList['public_holiday_normal_time'] = $attendanceNullList['public_holiday_normal_time'] + $attendance['public_holiday_normal_time'];
                $attendanceNullList['public_holiday_midnight_time_only'] = $attendanceNullList['public_holiday_midnight_time_only'] + $attendance['public_holiday_midnight_time_only'];
                
            }

            $Log->trace("END entireAggregate");

            return $attendanceNullList;
        }

/*******************************
* 個人日単位処理
********************************/

        /**
         * 個人単位の日毎の勤怠一覧作成
         * @param    $postArray
         * @return   $userPeriodDayList
         */
        private function creatUserPeriodDayList( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatUserPeriodDayList");

            // 指定期間開始日UNIX値
            $startUnix = $this->setStartDateUnix( $postArray['minPeriodSpecifiedYear'], $postArray['minPeriodSpecifiedMonth'], $postArray['minPeriodSpecifiedDay'] );
            // 指定期間終了日UNIX値
            $endUnix = $this->setEndDateUnix( $postArray['maxPeriodSpecifiedYear'], $postArray['maxPeriodSpecifiedMonth'], $postArray['maxPeriodSpecifiedDay'] );
            // 一日秒換算
            $sec = 86400;
            // 指定期間の日時リスト作成
            $periodDateList = $this->creatDateList($startUnix, $endUnix, $sec);
            // 指定期間開始日UNIXを年/月/日形式へ変更
            $startDate = date("Y/m/d",$startUnix);
            // 指定期間終了日UNIXを年/月/日形式へ変更
            $endDate = date("Y/m/d",$endUnix);
            // 指定期間内の従業員リスト取得
            $userList = $this->getListDataBranch($postArray, 1, $startDate, $endDate);
            // 指定期間の日数分の従業員リストの作成
            $periodDaysUserList = $this->setPeriodDaysUserList( $periodDateList, $userList, $endDate );
            // 勤怠入力データリスト取得
            $attendanceList = $this->getListDataBranch($postArray, 2, $startDate, $endDate);
            // 勤怠データがない従業員用に項目分の空の配列を取得
            $attendanceNullList = $this->setAttendanceNullList();
            // 勤怠情報を合体
            $attendanceCnt = 1;
            $attendanceUserDList = $this->setAttendanceUserDayList( $periodDaysUserList, $attendanceList, $attendanceNullList, $postArray, $attendanceCnt );
            // 時間での検索の場合リスト再構築
            $attendanceUserDList = $this->timeConversionAttendanceData( $postArray, $attendanceUserDList );
            // 組織の表示順へ並び替え
            $userPeriodDayList = $this->sortAttendanceListAccessLevel( $postArray, $attendanceUserDList );
            // 集計行作成
            if( $postArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_DAY') )
            {
                $userDateSummaryRow = $this->creatUserSummaryRow( $attendanceList, $attendanceCnt, 0 );
            }
            else
            {
                $userDateSummaryRow = $this->creatUserTimeSummaryRow( $attendanceUserDList );
            }
            // 集計行挿入
            array_push($userPeriodDayList, $userDateSummaryRow);

            $Log->trace("END creatUserPeriodDayList");

            return $userPeriodDayList;
        }

        /**
         * 個人単位の日毎の勤怠一覧作成
         * @param    $postArray
         * @param    $attendanceList
         * @return   $userPeriodDayList
         */
        private function timeConversionAttendanceData( $postArray, $attendanceList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START timeConversionAttendanceData");

            if( $postArray['dateTimeUnit'] !== $Log->getMsgLog('MSG_CUMULATIVE_TIME') )
            {
                $Log->trace("END timeConversionAttendanceData");
                return $attendanceList;
            }

            // 検索開始時間
            $startHour = $this->checkTimeDayStride( $postArray['minPeriodSpecifiedHour'] );
            // 検索終了時間
            $endHour = $this->checkTimeDayStride( $postArray['maxPeriodSpecifiedHour'] );
            // 検索開始時間と検索終了時間の間に日またぎが存在するかの確認
            $twentyFourHour = $this->checkTimeStartEndDayStride( $postArray['minPeriodSpecifiedHour'], $postArray['maxPeriodSpecifiedHour'] );

            $attendanceTimeList = array();
            foreach( $attendanceList as $attendance )
            {
                $workHours = $this->splitWorkHours( $attendance['work_hours'] );
                foreach( $workHours as $key => $bit )
                {
                    $attendanceTimeList = $this->creatTemporalDecompositionList( $attendanceTimeList, $attendance, $twentyFourHour, $key, $startHour, $endHour, $bit );
                }
            }

            $Log->trace("END timeConversionAttendanceData");

            return $attendanceTimeList;
        }

        /**
         * 時間帯検索の対象時間の24時間判定
         * @param    $time
         * @return   $criteriaHour
         */
        private function checkTimeDayStride( $time )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START checkTimeDayStride");

            if( $time <= 24 )
            {
                $criteriaHour = intval( $time );
            }
            else
            {
                $criteriaHour = intval( $time ) - 24;
            }

            $Log->trace("END checkTimeDayStride");

            return $criteriaHour;
        }

        /**
         * 24時の判定有無確認
         * @note     検索開始時間が当日で検索終了時間が翌日の場合24を返す
         * @param    $start
         * @param    $end
         * @return   $twentyFourHour
         */
        private function checkTimeStartEndDayStride( $start, $end )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START checkTimeStartEndDayStride");

            $twentyFourHour = "";
            if( ( $start <= 23 ) && ( $end > 23 ) )
            {
                $twentyFourHour = 24;
            }

            $Log->trace("END checkTimeStartEndDayStride");

            return $twentyFourHour;
        }

        /**
         * 24時の判定有無確認
         * @param    $attendanceTimeList
         * @param    $attendance
         * @param    $key
         * @param    $start
         * @param    $end
         * @param    $bit
         * @return   $userPeriodDayList
         */
        private function creatTemporalDecompositionList( $attendanceTimeList, $attendance, $twentyFourHour, $key, $start, $end, $bit )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatTemporalDecompositionList");

            if( empty( $twentyFourHour ) )
            {
                if( $start <= $key && $end >= $key )
                {
                    if( !empty( $bit ) )
                    {
                        $timeList = $this->insertTimeList( $attendance, $key );
                        array_push($attendanceTimeList, $timeList);
                    }
                }
            }
            else
            {
                if( $start <= $key && $twentyFourHour > $key )
                {
                    if(  !empty( $bit )  )
                    {
                        $timeList = $this->insertTimeList( $attendance, $key );
                        array_push($attendanceTimeList, $timeList);
                    }
                }
                else if( 0 <= $key && $end > $key )
                {
                    if(  !empty( $bit )  )
                    {
                        $timeList = $this->insertTimeList( $attendance, $key );
                        array_push($attendanceTimeList, $timeList);
                    }
                }
            }

            $Log->trace("END creatTemporalDecompositionList");

            return $attendanceTimeList;
        }

        /**
         * 時間累計リストデータ挿入
         * @param    $attendance
         * @param    $key
         * @return   $userPeriodDayList
         */
        private function insertTimeList( $attendance, $key )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START insertTimeList");

            $timeList = array();
            
            $timeList['periodDate'] = strval($key) . " 時";
            $timeList['user_id'] = $attendance['user_id'];
            $timeList['user_detail_id'] = $attendance['user_detail_id'];
            $timeList['organization_id'] = $attendance['organization_id'];
            $timeList['employees_no'] = $attendance['employees_no'];
            $timeList['user_name'] = $attendance['user_name'];
            $timeList['tel'] = $attendance['tel'];
            $timeList['cellphone'] = $attendance['cellphone'];
            $timeList['mail_address'] = $attendance['mail_address'];
            $timeList['base_salary'] = $attendance['base_salary'];
            $timeList['hourly_wage'] = $attendance['hourly_wage'];
            $timeList['user_comment'] = $attendance['user_comment'];
            $timeList['position_name'] = $attendance['position_name'];
            $timeList['employment_name'] = $attendance['employment_name'];
            $timeList['section_name'] = $attendance['section_name'];
            $timeList['wage_form_name'] = $attendance['wage_form_name'];
            $timeList['organization_name'] = $attendance['organization_name'];
            $timeList['abbreviated_name'] = $attendance['abbreviated_name'];
            $timeList['department_code'] = $attendance['department_code'];
            $timeList['organization_comment'] = $attendance['organization_comment'];
            $timeList['embossing_department_code'] = $attendance['embossing_department_code'];
            $timeList['embossing_organization_name'] = $attendance['embossing_organization_name'];
            $timeList['embossing_abbreviated_name'] = $attendance['embossing_abbreviated_name'];
            $timeList['embossing_status'] = $attendance['embossing_status'];
            $timeList['work_classification'] = $attendance['work_classification'];
            $timeList['rough_estimate'] = $attendance['rough_estimate_hour'];
            $timeList['shift_rough_estimate'] = $attendance['shift_rough_estimate_hour'];

            $Log->trace("END insertTimeList");

            return $timeList;
        }

        /**
         * 日別勤怠情報従業員情報取得切り分け
         * @param    $postArray
         * @param    $sqlFlag
         * @param    $startDate
         * @param    $endDate
         * @return   $userInfoList
         */
        private function getListDataBranch($postArray, $sqlFlag, $startDate, $endDate)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getListDataBranch");

            $searchArray = array();
            if($sqlFlag == 1)
            {
                // 日毎の従業員リストを取得するSQLの生成（$searchArrayは参照渡しで中身が変更されて戻ってくる）
                $sql = $this->creatUserSQL( $postArray, $searchArray, $startDate, $endDate );
            }
            else
            {
                // 日毎の勤怠情報を取得するSQLの生成（$searchArrayは参照渡しで中身が変更されて戻ってくる）
                $sql = $this->creatAttendanceSQL( $postArray, $searchArray, $startDate, $endDate );
            }
            $infoList = $this->runListGet( $sql, $searchArray );

            $Log->trace("END getListDataBranch");

            return $infoList;
        }


        /**
         * 指定期間リストと従業員リストの組み合わせ
         * @param    $periodDateList
         * @param    $userList
         * @param    $endDate
         * @return   $periodDaysUserList
         */
        private function setPeriodDaysUserList( $periodDateList, $userList, $endDate )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setPeriodDaysUserList");

            $periodDaysUserList = array();
            $periodCnt = 1;
            foreach( $userList as $user)
            {
                // 従業員の更新情報がない場合には指定期間の最終日を入れる
                $user = $this->setNextApplicationDateStart( $user, $endDate );
                foreach( $periodDateList as $periodDate )
                {
                    $periodDaysUserList = $this->insertPeriodDateUserList( $periodDaysUserList, $user, $periodDate, $periodCnt );
                }
            }

            $Log->trace("END setPeriodDaysUserList");
            return $periodDaysUserList;
        }

        /**
         * 更新の予定がない従業員に対して仮の次期適用開始日を設定する
         * @param    $userList
         * @param    $endDate
         * @return   $userList
         */
        private function setNextApplicationDateStart( $userList, $endDate )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setNextApplicationDateStart");

            if( empty( $userList['next_application_date_start'] ) )
            {
                $userList['next_application_date_start'] = date("Y/m/d", strtotime("+1 day", strtotime( $endDate ) ) );
            }

            $Log->trace("END setPeriodDaysUserList");

            return $userList;
        }

        /**
         * 従業員リストに指定期間の日時を挿入
         * @param    $periodDaysUserList
         * @param    $userList
         * @param    $periodList
         * @param    &$periodCnt
         * @return   $userList
         */
        private function insertPeriodDateUserList( $periodDaysUserList, $userList, $periodList, &$periodCnt )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START insertPeriodDateUserList");

            if( ( strtotime( $userList['application_date_start'] ) <= strtotime( $periodList['periodSpecified'] ) ) && ( strtotime($periodList['periodSpecified'] ) < strtotime( $userList['next_application_date_start'] ) ) )
            {
                $userList['period_id'] = $periodCnt;
                $userList['periodDate'] = $periodList['periodSpecified'];
                $userList['attendance_id'] = "";
                $userList['date'] = "";
                array_push($periodDaysUserList, $userList);
                $periodCnt++;
            }

            $Log->trace("END insertPeriodDateUserList");

            return $periodDaysUserList;
        }

        /**
         * 指定期間従業員リストと勤怠実績リストの組み合わせ
         * @param    $periodDaysUserList
         * @param    $attendanceList
         * @param    $attendanceNullList
         * @param    $postArray
         * @param    $attendanceCnt
         * @return   $attendanceUserDList
         */
        private function setAttendanceUserDayList( $periodDaysUserList, $attendanceList, $attendanceNullList, $postArray, &$attendanceCnt )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setAttendanceUserDayList");

            $attendanceUserDList = array();
            $period_id = 0;
            foreach( $periodDaysUserList as $periodDaysUser )
            {
                foreach( $attendanceList as $attendance )
                {
                    $attendanceUserDList = $this->insertAttendanceData( $periodDaysUser, $attendance, $attendanceUserDList, $attendanceCnt, $period_id );
                }
                if( empty( $postArray['searchCondition'] ) )
                {
                    $attendanceUserDList = $this->insertNullList( $periodDaysUser, $attendanceNullList, $attendanceUserDList, $attendanceCnt, $period_id );
                }
            }

            $Log->trace("END setAttendanceUserDayList");
            return $attendanceUserDList;
        }

        /**
         * 指定期間従業員リストと勤怠実績リストの組み合わせ
         * @param    $periodDaysUser
         * @param    $attendance
         * @param    $attendanceUserDList
         * @param    &$attendanceCnt
         * @param    &$period_id
         * @return   $attendanceUserDList
         */
        private function insertAttendanceData( $periodDaysUser, $attendance, $attendanceUserDList, &$attendanceCnt, &$period_id )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START insertAttendanceData");

            if( ( $periodDaysUser['user_detail_id'] == $attendance['user_detail_id'] ) && ( strtotime( $periodDaysUser['periodDate'] ) == strtotime( $attendance['date'] ) ) )
            {
                $periodDaysUser['attendance_unit_id'] = $attendanceCnt;
                $periodDaysUser['attendance_id'] = $attendance['attendance_id'];
                $periodDaysUser['date'] = $attendance['date'];
                $periodDaysUser['embossing_department_code'] = $attendance['embossing_department_code'];
                $periodDaysUser['embossing_organization_name'] = $attendance['embossing_organization_name'];
                $periodDaysUser['embossing_abbreviated_name'] = $attendance['embossing_abbreviated_name'];
                $periodDaysUser['embossing_status'] = $attendance['embossing_status'];
                $periodDaysUser['work_classification'] = $attendance['work_classification'];
                $periodDaysUser['attendance_time'] = $attendance['attendance_time'];
                $periodDaysUser['clock_out_time'] = $attendance['clock_out_time'];
                $periodDaysUser['break_time_con_minute'] = $attendance['break_time_con_minute'];
                $periodDaysUser['travel_time_con_minute'] = $attendance['travel_time_con_minute'];
                $periodDaysUser['late_time_con_minute'] = $attendance['late_time_con_minute'];
                $periodDaysUser['leave_early_time_con_minute'] = $attendance['leave_early_time_con_minute'];
                $periodDaysUser['late_leave_time_con_minute'] = $attendance['late_leave_time_con_minute'];
                $periodDaysUser['total_working_time_con_minute'] = $attendance['total_working_time_con_minute'];
                $periodDaysUser['night_working_time_con_minute'] = $attendance['night_working_time_con_minute'];
                $periodDaysUser['overtime_con_minute'] = $attendance['overtime_con_minute'];
                $periodDaysUser['night_overtime_con_minute'] = $attendance['night_overtime_con_minute'];
                $periodDaysUser['embossing_attendance_time'] = $attendance['embossing_attendance_time'];
                $periodDaysUser['embossing_clock_out_time'] = $attendance['embossing_clock_out_time'];
                $periodDaysUser['attendance_comment'] = $attendance['attendance_comment'];
                $periodDaysUser['modify_count'] = $attendance['modify_count'];
                $periodDaysUser['shift_section_id'] = $attendance['shift_section_id'];
                $periodDaysUser['shift_is_holiday'] = $attendance['shift_is_holiday'];
                $periodDaysUser['shift_attendance_time'] = $attendance['shift_attendance_time'];
                $periodDaysUser['shift_taikin_time'] = $attendance['shift_taikin_time'];
                $periodDaysUser['shift_break_time'] = $attendance['shift_break_time'];
                $periodDaysUser['late_time_count'] = $attendance['late_time_count'];
                $periodDaysUser['leave_early_time_count'] = $attendance['leave_early_time_count'];
                $periodDaysUser['late_leave_count'] = $attendance['late_leave_count'];
                $periodDaysUser['attendance_time_count'] = $attendance['attendance_time_count'];
                $periodDaysUser['weekday_attendance_time_count'] = $attendance['weekday_attendance_time_count'];
                $periodDaysUser['weekend_attendance_time_count'] = $attendance['weekend_attendance_time_count'];
                $periodDaysUser['legal_holiday_attendance_time_count'] = $attendance['legal_holiday_attendance_time_count'];
                $periodDaysUser['law_closed_attendance_time_count'] = $attendance['law_closed_attendance_time_count'];
                $periodDaysUser['weekday_working_con_minute'] = $attendance['weekday_working_con_minute'];
                $periodDaysUser['weekend_working_con_minute'] = $attendance['weekend_working_con_minute'];
                $periodDaysUser['legal_holiday_working_con_minute'] = $attendance['legal_holiday_working_con_minute'];
                $periodDaysUser['law_closed_working_con_minute'] = $attendance['law_closed_working_con_minute'];
                $periodDaysUser['weekday_night_working_con_minute'] = $attendance['weekday_night_working_con_minute'];
                $periodDaysUser['weekend_night_working_con_minute'] = $attendance['weekend_night_working_con_minute'];
                $periodDaysUser['legal_holiday_night_working_con_minute'] = $attendance['legal_holiday_night_working_con_minute'];
                $periodDaysUser['law_closed_night_working_con_minute'] = $attendance['law_closed_night_working_con_minute'];
                $periodDaysUser['weekday_overtime_con_minute'] = $attendance['weekday_overtime_con_minute'];
                $periodDaysUser['weekend_overtime_con_minute'] = $attendance['weekend_overtime_con_minute'];
                $periodDaysUser['legal_holiday_overtime_con_minute'] = $attendance['legal_holiday_overtime_con_minute'];
                $periodDaysUser['law_closed_overtime_con_minute'] = $attendance['law_closed_overtime_con_minute'];
                $periodDaysUser['weekday_night_overtime_con_minute'] = $attendance['weekday_night_overtime_con_minute'];
                $periodDaysUser['weekend_night_overtime_con_minute'] = $attendance['weekend_night_overtime_con_minute'];
                $periodDaysUser['legal_holiday_night_overtime_con_minute'] = $attendance['legal_holiday_night_overtime_con_minute'];
                $periodDaysUser['law_closed_night_overtime_con_minute'] = $attendance['law_closed_night_overtime_con_minute'];
                $periodDaysUser['rough_estimate'] = $attendance['rough_estimate'];
                $periodDaysUser['shift_rough_estimate'] = $attendance['shift_rough_estimate'];
                $periodDaysUser['rough_estimate_month'] = $attendance['rough_estimate_month'];
                $periodDaysUser['fixed_overtime_time'] = "";
                $periodDaysUser['absence_count'] = $attendance['absence_count'];
                $periodDaysUser['prescribed_working_days'] = $attendance['prescribed_working_days'];
                $periodDaysUser['prescribed_working_hours'] = $attendance['prescribed_working_hours'];
                $periodDaysUser['weekday_prescribed_working_hours'] = $attendance['weekday_prescribed_working_hours'];
                $periodDaysUser['weekend_prescribed_working_hours'] = $attendance['weekend_prescribed_working_hours'];
                $periodDaysUser['legal_holiday_prescribed_working_hours'] = $attendance['legal_holiday_prescribed_working_hours'];
                $periodDaysUser['law_closed_prescribed_working_hours'] = $attendance['law_closed_prescribed_working_hours'];
                $periodDaysUser['statutory_overtime_hours'] = $attendance['statutory_overtime_hours'];
                $periodDaysUser['nonstatutory_overtime_hours_all'] = $attendance['nonstatutory_overtime_hours_all'];
                $periodDaysUser['nonstatutory_overtime_hours'] = $attendance['nonstatutory_overtime_hours'];
                $periodDaysUser['nonstatutory_overtime_hours_45h'] = $attendance['nonstatutory_overtime_hours_45h'];
                $periodDaysUser['nonstatutory_overtime_hours_less_than'] = $attendance['nonstatutory_overtime_hours_less_than'];
                $periodDaysUser['nonstatutory_overtime_hours_60h'] = $attendance['nonstatutory_overtime_hours_60h'];
                $periodDaysUser['statutory_overtime_hours_no_pub'] = $attendance['statutory_overtime_hours_no_pub'];
                $periodDaysUser['nonstatutory_overtime_hours_no_pub_all'] = $attendance['nonstatutory_overtime_hours_no_pub_all'];
                $periodDaysUser['nonstatutory_overtime_hours_no_pub'] = $attendance['nonstatutory_overtime_hours_no_pub'];
                $periodDaysUser['nonstatutory_overtime_hours_45h_no_pub'] = $attendance['nonstatutory_overtime_hours_45h_no_pub'];
                $periodDaysUser['nonstatutory_overtime_hours_no_pub_less_than'] = $attendance['nonstatutory_overtime_hours_no_pub_less_than'];
                $periodDaysUser['nonstatutory_overtime_hours_60h_no_pub'] = $attendance['nonstatutory_overtime_hours_60h_no_pub'];
                $periodDaysUser['overtime_hours_no_considered'] = $attendance['overtime_hours_no_considered'];
                $periodDaysUser['overtime_hours_no_considered_no_pub'] = $attendance['overtime_hours_no_considered_no_pub'];
                $periodDaysUser['normal_working_con_minute'] = $attendance['normal_working_con_minute'];
                $periodDaysUser['normal_overtime_con_minute'] = $attendance['normal_overtime_con_minute'];
                $periodDaysUser['normal_night_working_con_minute'] = $attendance['normal_night_working_con_minute'];
                $periodDaysUser['normal_night_overtime_con_minute'] = $attendance['normal_night_overtime_con_minute'];
                $periodDaysUser['work_hours'] = $attendance['work_hours'];
                $periodDaysUser['rough_estimate_hour'] = $attendance['rough_estimate_hour'];
                $periodDaysUser['shift_rough_estimate_hour'] = $attendance['shift_rough_estimate_hour'];

                // millionet oota 追加
                $periodDaysUser['weekday_normal_time'] = $attendance['weekday_normal_time'];
                $periodDaysUser['weekday_midnight_time_only'] = $attendance['weekday_midnight_time_only'];
                $periodDaysUser['holiday_normal_time'] = $attendance['holiday_normal_time'];
                $periodDaysUser['holiday_midnight_time_only'] = $attendance['holiday_midnight_time_only'];
                $periodDaysUser['statutory_holiday_normal_time'] = $attendance['statutory_holiday_normal_time'];
                $periodDaysUser['statutory_holiday_midnight_time_only'] = $attendance['statutory_holiday_midnight_time_only'];
                $periodDaysUser['public_holiday_normal_time'] = $attendance['public_holiday_normal_time'];
                $periodDaysUser['public_holiday_midnight_time_only'] = $attendance['public_holiday_midnight_time_only'];
                
                array_push($attendanceUserDList, $periodDaysUser);
                $attendanceCnt++;
                $period_id = $periodDaysUser['period_id'];
            }

            $Log->trace("END insertAttendanceData");

            return $attendanceUserDList;
        }

        /**
         * 指定期間従業員リストと空リストの組み合わせ
         * @param    $periodDaysUser
         * @param    $attendanceNullList
         * @param    $attendanceUserDList
         * @param    &$attendanceCnt
         * @param    &$period_id
         * @return   $attendanceUserDList
         */
        private function insertNullList( $periodDaysUser, $attendanceNullList, $attendanceUserDList, &$attendanceCnt, &$period_id )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START insertNullList");

            if($period_id != $periodDaysUser['period_id'])
            {
                $periodDaysUser['attendance_unit_id'] = $attendanceCnt;
                $periodDaysUser = array_merge($periodDaysUser, $attendanceNullList);
                array_push($attendanceUserDList, $periodDaysUser);
                $attendanceCnt++;
                $period_id = $periodDaysUser['period_id'];
            }

            $Log->trace("END insertNullList");

            return $attendanceUserDList;
        }

        /**
         * 集計行の整理
         * @param    $aggregateList
         * @param    $attendanceCnt
         * @return   $uDSummaryRow
         */
        private function changeFormatUserDateSummaryRow( $aggregateList, $attendanceCnt )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeFormatUserDateSummaryRow");

            $uDSummaryRow = array();

            $uDSummaryRow['user_id'] = 0;
            $uDSummaryRow['user_detail_id'] = 0;
            $uDSummaryRow['organization_id'] = 0;
            $uDSummaryRow['employees_no'] = "-";
            $uDSummaryRow['user_name'] = "-";
            $uDSummaryRow['tel'] = "-";
            $uDSummaryRow['cellphone'] = "-";
            $uDSummaryRow['mail_address'] = "-";
            $uDSummaryRow['base_salary'] = "-";
            $uDSummaryRow['hourly_wage'] = "-";
            $uDSummaryRow['application_date_start'] = "-";
            $uDSummaryRow['next_application_date_start'] = "-";
            $uDSummaryRow['user_comment'] = "-";
            $uDSummaryRow['wage_form_name'] = "-";
            $uDSummaryRow['work_classification'] = "-";
            $uDSummaryRow['employment_name'] = "-";
            $uDSummaryRow['department_code'] = "-";
            $uDSummaryRow['organization_name'] = "-";
            $uDSummaryRow['abbreviated_name'] = "-";
            $uDSummaryRow['position_name'] = "-";
            $uDSummaryRow['section_name'] = "-";
            $uDSummaryRow['organization_comment'] = "-";
            $uDSummaryRow['attendance_unit_id'] = $attendanceCnt;
            $uDSummaryRow['attendance_id'] = "-";
            $uDSummaryRow['periodDate'] = "-";
            $uDSummaryRow['embossing_department_code'] = "-";
            $uDSummaryRow['embossing_organization_name'] = "-";
            $uDSummaryRow['embossing_abbreviated_name'] = "-";
            $uDSummaryRow['embossing_status'] = "-";
            $uDSummaryRow['attendance_time'] = "-";
            $uDSummaryRow['clock_out_time'] = "-";
            $uDSummaryRow['break_time_con_minute'] = $aggregateList['break_time_con_minute'];
            $uDSummaryRow['travel_time_con_minute'] = $aggregateList['travel_time_con_minute'];
            $uDSummaryRow['late_time_con_minute'] = $aggregateList['late_time_con_minute'];
            $uDSummaryRow['leave_early_time_con_minute'] = $aggregateList['leave_early_time_con_minute'];
            $uDSummaryRow['late_leave_time_con_minute'] = $aggregateList['late_leave_time_con_minute'];
            $uDSummaryRow['total_working_time_con_minute'] = $aggregateList['total_working_time_con_minute'];
            $uDSummaryRow['night_working_time_con_minute'] = $aggregateList['night_working_time_con_minute'];
            $uDSummaryRow['overtime_con_minute'] = $aggregateList['overtime_con_minute'];
            $uDSummaryRow['night_overtime_con_minute'] = $aggregateList['night_overtime_con_minute'];
            $uDSummaryRow['embossing_attendance_time'] = "-";
            $uDSummaryRow['embossing_clock_out_time'] = "-";
            $uDSummaryRow['attendance_comment'] = "-";
            $uDSummaryRow['modify_count'] = $aggregateList['modify_count'];
            $uDSummaryRow['shift_section_id'] = "-";
            $uDSummaryRow['shift_is_holiday'] = "-";
            $uDSummaryRow['shift_attendance_time'] = "-";
            $uDSummaryRow['shift_taikin_time'] = "-";
            $uDSummaryRow['shift_break_time'] = "-";
            $uDSummaryRow['late_time_count'] = $aggregateList['late_time_count'];
            $uDSummaryRow['leave_early_time_count'] = $aggregateList['leave_early_time_count'];
            $uDSummaryRow['late_leave_count'] = $aggregateList['late_leave_count'];
            $uDSummaryRow['attendance_time_count'] = $aggregateList['attendance_time_count'];
            $uDSummaryRow['weekday_attendance_time_count'] = $aggregateList['weekday_attendance_time_count'];
            $uDSummaryRow['weekend_attendance_time_count'] = $aggregateList['weekend_attendance_time_count'];
            $uDSummaryRow['legal_holiday_attendance_time_count'] = $aggregateList['legal_holiday_attendance_time_count'];
            $uDSummaryRow['law_closed_attendance_time_count'] = $aggregateList['law_closed_attendance_time_count'];
            $uDSummaryRow['weekday_working_con_minute'] = $aggregateList['weekday_working_con_minute'];
            $uDSummaryRow['weekend_working_con_minute'] = $aggregateList['weekend_working_con_minute'];
            $uDSummaryRow['legal_holiday_working_con_minute'] = $aggregateList['legal_holiday_working_con_minute'];
            $uDSummaryRow['law_closed_working_con_minute'] = $aggregateList['law_closed_working_con_minute'];
            $uDSummaryRow['weekday_night_working_con_minute'] = $aggregateList['weekday_night_working_con_minute'];
            $uDSummaryRow['weekend_night_working_con_minute'] = $aggregateList['weekend_night_working_con_minute'];
            $uDSummaryRow['legal_holiday_night_working_con_minute'] = $aggregateList['legal_holiday_night_working_con_minute'];
            $uDSummaryRow['law_closed_night_working_con_minute'] = $aggregateList['law_closed_night_working_con_minute'];
            $uDSummaryRow['weekday_overtime_con_minute'] = $aggregateList['weekday_overtime_con_minute'];
            $uDSummaryRow['weekend_overtime_con_minute'] = $aggregateList['weekend_overtime_con_minute'];
            $uDSummaryRow['legal_holiday_overtime_con_minute'] = $aggregateList['legal_holiday_overtime_con_minute'];
            $uDSummaryRow['law_closed_overtime_con_minute'] = $aggregateList['law_closed_overtime_con_minute'];
            $uDSummaryRow['weekday_night_overtime_con_minute'] = $aggregateList['weekday_night_overtime_con_minute'];
            $uDSummaryRow['weekend_night_overtime_con_minute'] = $aggregateList['weekend_night_overtime_con_minute'];
            $uDSummaryRow['legal_holiday_night_overtime_con_minute'] = $aggregateList['legal_holiday_night_overtime_con_minute'];
            $uDSummaryRow['law_closed_night_overtime_con_minute'] = $aggregateList['law_closed_night_overtime'];
            $uDSummaryRow['rough_estimate'] = $aggregateList['rough_estimate'];
            $uDSummaryRow['shift_rough_estimate'] = $aggregateList['shift_rough_estimate'];
            $uDSummaryRow['rough_estimate_month'] = $aggregateList['rough_estimate_month'];
            $uDSummaryRow['fixed_overtime_time'] = "-";
            $uDSummaryRow['absence_count'] = $aggregateList['absence_count'];
            $uDSummaryRow['prescribed_working_days'] = $aggregateList['prescribed_working_days'];
            $uDSummaryRow['prescribed_working_hours'] = $aggregateList['prescribed_working_hours'];
            $uDSummaryRow['weekday_prescribed_working_hours'] = $aggregateList['weekday_prescribed_working_hours'];
            $uDSummaryRow['weekend_prescribed_working_hours'] = $aggregateList['weekend_prescribed_working_hours'];
            $uDSummaryRow['legal_holiday_prescribed_working_hours'] = $aggregateList['legal_holiday_prescribed_working_hours'];
            $uDSummaryRow['law_closed_prescribed_working_hours'] = $aggregateList['law_closed_prescribed_working_hours'];
            $uDSummaryRow['statutory_overtime_hours'] = $aggregateList['statutory_overtime_hours'];
            $uDSummaryRow['nonstatutory_overtime_hours_all'] = $aggregateList['nonstatutory_overtime_hours_all'];
            $uDSummaryRow['nonstatutory_overtime_hours'] = $aggregateList['nonstatutory_overtime_hours'];
            $uDSummaryRow['nonstatutory_overtime_hours_45h'] = $aggregateList['nonstatutory_overtime_hours_45h'];
            $uDSummaryRow['nonstatutory_overtime_hours_less_than'] = $aggregateList['nonstatutory_overtime_hours_less_than'];
            $uDSummaryRow['nonstatutory_overtime_hours_60h'] = $aggregateList['nonstatutory_overtime_hours_60h'];
            $uDSummaryRow['statutory_overtime_hours_no_pub'] = $aggregateList['statutory_overtime_hours_no_pub'];
            $uDSummaryRow['nonstatutory_overtime_hours_no_pub_all'] = $aggregateList['nonstatutory_overtime_hours_no_pub_all'];
            $uDSummaryRow['nonstatutory_overtime_hours_no_pub'] = $aggregateList['nonstatutory_overtime_hours_no_pub'];
            $uDSummaryRow['nonstatutory_overtime_hours_45h_no_pub'] = $aggregateList['nonstatutory_overtime_hours_45h_no_pub'];
            $uDSummaryRow['nonstatutory_overtime_hours_no_pub_less_than'] = $aggregateList['nonstatutory_overtime_hours_no_pub_less_than'];
            $uDSummaryRow['nonstatutory_overtime_hours_60h_no_pub'] = $aggregateList['nonstatutory_overtime_hours_60h_no_pub'];
            $uDSummaryRow['overtime_hours_no_considered'] = $aggregateList['overtime_hours_no_considered'];
            $uDSummaryRow['overtime_hours_no_considered_no_pub'] = $aggregateList['overtime_hours_no_considered_no_pub'];
            $uDSummaryRow['normal_working_con_minute'] = $aggregateList['normal_working_con_minute'];
            $uDSummaryRow['normal_overtime_con_minute'] = $aggregateList['normal_overtime_con_minute'];
            $uDSummaryRow['normal_night_working_con_minute'] = $aggregateList['normal_night_working'];
            $uDSummaryRow['normal_night_overtime_con_minute'] = $aggregateList['normal_night_overtime_con_minute'];

            // millionet oota 追加
            $uDSummaryRow['weekday_normal_time'] = $aggregateList['weekday_normal_time'];
            $uDSummaryRow['weekday_midnight_time_only'] = $aggregateList['weekday_midnight_time_only'];
            $uDSummaryRow['holiday_normal_time'] = $aggregateList['holiday_normal_time'];
            $uDSummaryRow['holiday_midnight_time_only'] = $aggregateList['holiday_midnight_time_only'];
            $uDSummaryRow['statutory_holiday_normal_time'] = $aggregateList['statutory_holiday_normal_time'];
            $uDSummaryRow['statutory_holiday_midnight_time_only'] = $aggregateList['statutory_holiday_midnight_time_only'];
            $uDSummaryRow['public_holiday_normal_time'] = $aggregateList['public_holiday_normal_time'];
            $uDSummaryRow['public_holiday_midnight_time_only'] = $aggregateList['public_holiday_midnight_time_only'];
            
            $Log->trace("END changeFormatUserDateSummaryRow");

            return $uDSummaryRow;
        }

        /**
         * 集計
         * @param    $attendanceList
         * @return   $attendanceNullList
         */
        private function creatUserTimeSummaryRow( $attendanceList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatUserTimeSummaryRow");

            $attendanceNullList = array(
                'periodDate'                  => "-",
                'user_id'                     => 0,
                'user_detail_id'              => 0,
                'organization_id'             => 0,
                'employees_no'                => "-",
                'user_name'                   => "-",
                'tel'                         => "-",
                'cellphone'                   => "-",
                'mail_address'                => "-",
                'base_salary'                 => "-",
                'hourly_wage'                 => "-",
                'user_comment'                => "-",
                'position_name'               => "-",
                'employment_name'             => "-",
                'section_name'                => "-",
                'wage_form_name'              => "-",
                'organization_name'           => "-",
                'abbreviated_name'            => "-",
                'department_code'             => "-",
                'organization_comment'        => "-",
                'embossing_department_code'   => "-",
                'embossing_organization_name' => "-",
                'embossing_abbreviated_name'  => "-",
                'embossing_status'            => "-",
                'work_classification'         => "-",
                'rough_estimate'              => "",
                'shift_rough_estimate'        => "",
            );

            foreach( $attendanceList as $attendance )
            {
                $attendanceNullList['rough_estimate'] = $attendanceNullList['rough_estimate'] + $attendance['rough_estimate'];
                $attendanceNullList['shift_rough_estimate'] = $attendanceNullList['shift_rough_estimate'] + $attendance['shift_rough_estimate'];
            }

            $Log->trace("END creatUserTimeSummaryRow");

            return $attendanceNullList;
        }

        /**
         * 勤怠一覧項目空リスト作成
         * @return   $attendanceNullList
         */
        private function setAttendanceNullList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setAttendanceNullList");

            $attendanceNullList = array(
                'attendance_id'                                => "",
                'date'                                         => "",
                'embossing_department_code'                    => "",
                'embossing_organization_name'                  => "",
                'embossing_abbreviated_name'                   => "",
                'embossing_status'                             => "",
                'work_classification'                          => "",
                'attendance_time'                              => "",
                'clock_out_time'                               => "",
                'break_time_con_minute'                        => "",
                'travel_time_con_minute'                       => "",
                'late_time_con_minute'                         => "",
                'leave_early_time_con_minute'                  => "",
                'late_leave_time_con_minute'                   => "",
                'total_working_time_con_minute'                => "",
                'night_working_time_con_minute'                => "",
                'overtime_con_minute'                          => "",
                'night_overtime'                               => "",
                'embossing_attendance_time'                    => "",
                'embossing_clock_out_time'                     => "",
                'attendance_comment'                           => "",
                'modify_count'                                 => "",
                'shift_section_id'                             => "",
                'shift_is_holiday'                             => "",
                'shift_attendance_time'                        => "",
                'shift_taikin_time'                            => "",
                'shift_break_time'                             => "",
                'late_time_count'                              => "",
                'leave_early_time_count'                       => "",
                'late_leave_count'                             => "",
                'attendance_time_count'                        => "",
                'weekday_attendance_time_count'                => "",
                'weekend_attendance_time_count'                => "",
                'legal_holiday_attendance_time_count'          => "",
                'law_closed_attendance_time_count'             => "",
                'weekday_working_con_minute'                   => "",
                'weekend_working_con_minute'                   => "",
                'legal_holiday_working_con_minute'             => "",
                'law_closed_working_con_minute'                => "",
                'weekday_night_working_con_minute'             => "",
                'weekend_night_working_con_minute'             => "",
                'legal_holiday_night_working_con_minute'       => "",
                'law_closed_night_working_con_minute'          => "",
                'weekday_overtime_con_minute'                  => "",
                'weekend_overtime_con_minute'                  => "",
                'legal_holiday_overtime_con_minute'            => "",
                'law_closed_overtime_con_minute'               => "",
                'weekday_night_overtime_con_minute'            => "",
                'weekend_night_overtime_con_minute'            => "",
                'legal_holiday_night_overtime_con_minute'      => "",
                'law_closed_night_overtime_con_minute'         => "",
                'rough_estimate'                               => "",
                'shift_rough_estimate'                         => "",
                'rough_estimate_month'                         => "",
                'fixed_overtime_time'                          => "",
                'absence_count'                                => "",
                'prescribed_working_days'                      => "",
                'prescribed_working_hours'                     => "",
                'weekday_prescribed_working_hours'             => "",
                'weekend_prescribed_working_hours'             => "",
                'legal_holiday_prescribed_working_hours'       => "",
                'law_closed_prescribed_working_hours'          => "",
                'statutory_overtime_hours'                     => "",
                'nonstatutory_overtime_hours_all'              => "",
                'nonstatutory_overtime_hours'                  => "",
                'nonstatutory_overtime_hours_45h'              => "",
                'nonstatutory_overtime_hours_less_than'        => "",
                'nonstatutory_overtime_hours_60h'              => "",
                'statutory_overtime_hours_no_pub'              => "",
                'nonstatutory_overtime_hours_no_pub_all'       => "",
                'nonstatutory_overtime_hours_no_pub'           => "",
                'nonstatutory_overtime_hours_45h_no_pub'       => "",
                'nonstatutory_overtime_hours_no_pub_less_than' => "",
                'nonstatutory_overtime_hours_60h_no_pub'       => "",
                'overtime_hours_no_considered'                 => "",
                'overtime_hours_no_considered_no_pub'          => "",
                'normal_working_con_minute'                    => "",
                'normal_overtime_con_minute'                   => "",
                'normal_night_working_con_minute'              => "",
                'normal_night_overtime_con_minute'             => "",
                'work_hours'                                   => "",
                'rough_estimate_hour'                          => "",
                'shift_rough_estimate_hour'                    => "",
                'weekday_normal_time'                          => "",
                'weekday_midnight_time_only'                   => "",
                'holiday_normal_time'                          => "",
                'holiday_midnight_time_only'                   => "",
                'statutory_holiday_normal_time'                => "",
                'statutory_holiday_midnight_time_only'         => "",
                'public_holiday_normal_time'                   => "",
                'public_holiday_midnight_time_only'            => "",
            );

            $Log->trace("END setAttendanceNullList");
            return $attendanceNullList;
        }

/*******************************
* 個人(月/年)合算処理
********************************/

        /**
         * (月/年)合算用従業員リスト取得
         * @param    $postArray
         * @return   $addingUpUserList
         */
        private function creatAddingUpUserList( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAddingUpUserList");

            // 指定期間開始日UNIX値
            $startUnix = $this->setStartDateUnix( $postArray['minPeriodSpecifiedYear'], $postArray['minPeriodSpecifiedMonth'], $postArray['minPeriodSpecifiedDay'] );
            // 指定期間終了日UNIX値
            $endUnix = $this->setEndDateUnix( $postArray['maxPeriodSpecifiedYear'], $postArray['maxPeriodSpecifiedMonth'], $postArray['maxPeriodSpecifiedDay'] );
            // 検索する範囲を1か月後または1年後までとする
            $endUnixOnePlus = $this->setEndUnixOnePlus( $postArray['dateTimeUnit'], $endUnix );
            // 指定期間開始日UNIXを年/月/日形式へ変更
            $startDate = date("Y/m/d",$startUnix);
            // 指定期間終了日UNIXを年/月/日形式へ変更
            $endDate = date("Y/m/d",$endUnixOnePlus);
            // 従業員リストを指定期間作成する際に対象期間のキー名を月または年で指定する
            $periodName = "";
            // 検索対象期間のリストをセット
            $targetPeriodList = $this->setTargetPeriodList( $postArray['dateTimeUnit'], $startUnix, $endUnix, $periodName );
            // 検索用のリスト初期化
            $searchArray = array();
            // 指定期間内での各ユーザリスト取得SQL
            $userSql = $this->creatUserPeriodSQL( $postArray, $searchArray, $startDate, $endDate );
            // SQL実行従業員リスト取得
            $userList = $this->runListGet( $userSql, $searchArray );
            // 指定期間（月/年）分の従業員リストを作成
            $userPeriodList = $this->setUserPeriodList( $userList, $targetPeriodList, $periodName );
            // 検索用のリスト初期化
            $searchArray = array();
            // 勤怠一覧合算用取得SQL
            $attendanceSql = $this->creatUnitAttendanceListSQL( $postArray, $searchArray, $startDate, $endDate );
            // SQL実行勤怠リスト取得
            $attendanceDate = $this->runListGet( $attendanceSql, $searchArray );
            // 期間指定合算
            $addingUpUserList = $this->addingUpUserAttendanceData( $userPeriodList, $attendanceDate, $postArray['dateTimeUnit'] );
            // 検索条件（打刻関係）での絞り込み
            $addingUpUserList = $this->refineEmbossingSituation( $addingUpUserList, $postArray, "embossingOrganizationID", "embossing_organization_id" );
            $addingUpUserList = $this->refineEmbossingSituation( $addingUpUserList, $postArray, "embossingSituation", "embossing_status" );

            $Log->trace("END creatAddingUpUserList");

            return $addingUpUserList;
        }

        /**
         * 対象月分の従業員リストをセット
         * @param    $userList
         * @param    $targetMonthList
         * @param    $periodName
         * @return   $userPeriodList
         */
        private function setUserPeriodList( $userList, $targetMonthList, $periodName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setUserPeriodList");

            $userPeriodList = array();
            $periodCnt = 1;
            foreach( $userList as $user)
            {
                foreach( $targetMonthList as $target )
                {
                    // 従業員の適用開始日が指定期間の各月の月末より前の日でかつ次期適用開始日がNULLまたは各月の月初よりも先の日付なら対象月のリストを作る
                    if( ( strtotime( $user['application_date_start'] ) <= strtotime( $target['targetLastDate'] ) ) && ( ( empty( $user['next_application_date_start'] ) ) || ( strtotime( $user['next_application_date_start'] ) >= strtotime( $target['targetFirstDate'] ) ) ) )
                    {
                        $user['period_id'] = $periodCnt;
                        $user['target_period'] = $target[$periodName];
                        array_push( $userPeriodList, $user );
                        $periodCnt++;
                    }
                }
            }

            $Log->trace("END setUserPeriodList");

            return $userPeriodList;
        }

        /**
         * 従業員勤怠月合算処理
         * @param    $userPeriodList
         * @param    $attendanceDate
         * @param    $dateTimeUnit
         * @return   $addingUpUserList
         */
        private function addingUpUserAttendanceData( $userPeriodList, $attendanceDate, $dateTimeUnit )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addingUpUserAttendanceData");

            $targetKeyName = $this->setTargetKeyName( $dateTimeUnit );

            $addingUpUserList = array();
            foreach( $userPeriodList as $userPeriod )
            {
                $initialValList = $this->setInitialValList();

                foreach( $attendanceDate as $attendance )
                {
                    $initialValList = $this->insertUserAttendanceList( $initialValList, $userPeriod, $attendance, $targetKeyName );
                }
                $userPeriod['total_working_time_con_minute'] = $initialValList['total_working_time_con_minute'];
                $userPeriod['weekday_working_con_minute'] = $initialValList['weekday_working_con_minute'];
                $userPeriod['weekend_working_con_minute'] = $initialValList['weekend_working_con_minute'];
                $userPeriod['legal_holiday_working_con_minute'] = $initialValList['legal_holiday_working_con_minute'];
                $userPeriod['law_closed_working_con_minute'] = $initialValList['law_closed_working_con_minute'];
                $userPeriod['overtime_con_minute'] = $initialValList['overtime_con_minute'];
                $userPeriod['weekday_overtime_con_minute'] = $initialValList['weekday_overtime_con_minute'];
                $userPeriod['weekend_overtime_con_minute'] = $initialValList['weekend_overtime_con_minute'];
                $userPeriod['legal_holiday_overtime_con_minute'] = $initialValList['legal_holiday_overtime_con_minute'];
                $userPeriod['law_closed_overtime_con_minute'] = $initialValList['law_closed_overtime_con_minute'];
                $userPeriod['night_working_time_con_minute'] = $initialValList['night_working_time_con_minute'];
                $userPeriod['weekday_night_working_con_minute'] = $initialValList['weekday_night_working_con_minute'];
                $userPeriod['weekend_night_working_con_minute'] = $initialValList['weekend_night_working_con_minute'];
                $userPeriod['legal_holiday_night_working_con_minute'] = $initialValList['legal_holiday_night_working_con_minute'];
                $userPeriod['law_closed_night_working_con_minute'] = $initialValList['law_closed_night_working_con_minute'];
                $userPeriod['night_overtime_con_minute'] = $initialValList['night_overtime_con_minute'];
                $userPeriod['weekday_night_overtime_con_minute'] = $initialValList['weekday_night_overtime_con_minute'];
                $userPeriod['weekend_night_overtime_con_minute'] = $initialValList['weekend_night_overtime_con_minute'];
                $userPeriod['legal_holiday_night_overtime_con_minute'] = $initialValList['legal_holiday_night_overtime_con_minute'];
                $userPeriod['law_closed_night_overtime_con_minute'] = $initialValList['law_closed_night_overtime_con_minute'];
                $userPeriod['break_time_con_minute'] = $initialValList['break_time_con_minute'];
                $userPeriod['late_time_con_minute'] = $initialValList['late_time_con_minute'];
                $userPeriod['leave_early_time_con_minute'] = $initialValList['leave_early_time_con_minute'];
                $userPeriod['travel_time_con_minute'] = $initialValList['travel_time_con_minute'];
                $userPeriod['late_leave_time_con_minute'] = $initialValList['late_leave_time_con_minute'];
                $userPeriod['modify_count'] = $initialValList['modify_count'];
                $userPeriod['late_time_count'] = $initialValList['late_time_count'];
                $userPeriod['leave_early_time_count'] = $initialValList['leave_early_time_count'];
                $userPeriod['late_leave_count'] = $initialValList['late_leave_count'];
                $userPeriod['fixed_overtime_time_dis'] = $initialValList['fixed_overtime_time'];
                $userPeriod['attendance_time_count'] = $initialValList['attendance_time_count'];
                $userPeriod['weekday_attendance_time_count'] = $initialValList['weekday_attendance_time_count'];
                $userPeriod['weekend_attendance_time_count'] = $initialValList['weekend_attendance_time_count'];
                $userPeriod['legal_holiday_attendance_time_count'] = $initialValList['legal_holiday_attendance_time_count'];
                $userPeriod['law_closed_attendance_time_count'] = $initialValList['law_closed_attendance_time_count'];
                $userPeriod['rough_estimate'] = $initialValList['rough_estimate'];
                $userPeriod['shift_rough_estimate'] = $initialValList['shift_rough_estimate'];
                $userPeriod['rough_estimate_month'] = $initialValList['rough_estimate_month'];
                $userPeriod['absence_count'] = $initialValList['absence_count'];
                $userPeriod['prescribed_working_days'] = $initialValList['prescribed_working_days'];
                $userPeriod['prescribed_working_hours'] = $initialValList['prescribed_working_hours'];
                $userPeriod['weekday_prescribed_working_hours'] = $initialValList['weekday_prescribed_working_hours'];
                $userPeriod['weekend_prescribed_working_hours'] = $initialValList['weekend_prescribed_working_hours'];
                $userPeriod['legal_holiday_prescribed_working_hours'] = $initialValList['legal_holiday_prescribed_working_hours'];
                $userPeriod['law_closed_prescribed_working_hours'] = $initialValList['law_closed_prescribed_working_hours'];
                $userPeriod['statutory_overtime_hours'] = $initialValList['statutory_overtime_hours'];
                $userPeriod['nonstatutory_overtime_hours_all'] = $initialValList['nonstatutory_overtime_hours_all'];
                $userPeriod['nonstatutory_overtime_hours'] = $initialValList['nonstatutory_overtime_hours'];
                $userPeriod['nonstatutory_overtime_hours_45h'] = $initialValList['nonstatutory_overtime_hours_45h'];
                $userPeriod['nonstatutory_overtime_hours_less_than'] = $initialValList['nonstatutory_overtime_hours_less_than'];
                $userPeriod['nonstatutory_overtime_hours_60h'] = $initialValList['nonstatutory_overtime_hours_60h'];
                $userPeriod['statutory_overtime_hours_no_pub'] = $initialValList['statutory_overtime_hours_no_pub'];
                $userPeriod['nonstatutory_overtime_hours_no_pub_all'] = $initialValList['nonstatutory_overtime_hours_no_pub_all'];
                $userPeriod['nonstatutory_overtime_hours_no_pub'] = $initialValList['nonstatutory_overtime_hours_no_pub'];
                $userPeriod['nonstatutory_overtime_hours_45h_no_pub'] = $initialValList['nonstatutory_overtime_hours_45h_no_pub'];
                $userPeriod['nonstatutory_overtime_hours_no_pub_less_than'] = $initialValList['nonstatutory_overtime_hours_no_pub_less_than'];
                $userPeriod['nonstatutory_overtime_hours_60h_no_pub'] = $initialValList['nonstatutory_overtime_hours_60h_no_pub'];
                $userPeriod['overtime_hours_no_considered'] = $initialValList['overtime_hours_no_considered'];
                $userPeriod['overtime_hours_no_considered_no_pub'] = $initialValList['overtime_hours_no_considered_no_pub'];
                $userPeriod['normal_working_con_minute'] = $initialValList['normal_working_con_minute'];
                $userPeriod['normal_overtime_con_minute'] = $initialValList['normal_overtime_con_minute'];
                $userPeriod['normal_night_working_con_minute'] = $initialValList['normal_night_working_con_minute'];
                $userPeriod['normal_night_overtime_con_minute'] = $initialValList['normal_night_overtime_con_minute'];
                $userPeriod['embossing_organization_id'] = $initialValList['embossing_organization_id'];
                $userPeriod['embossing_department_code'] = $initialValList['embossing_department_code'];
                $userPeriod['embossing_organization_name'] = $initialValList['embossing_organization_name'];
                $userPeriod['embossing_abbreviated_name'] = $initialValList['embossing_abbreviated_name'];
                $userPeriod['embossing_status'] = $initialValList['embossing_status'];
                $userPeriod['work_classification'] = $initialValList['work_classification'];

                // millionet oota 追加
                $userPeriod['weekday_normal_time'] = $initialValList['weekday_normal_time'];
                $userPeriod['weekday_midnight_time_only'] = $initialValList['weekday_midnight_time_only'];
                $userPeriod['holiday_normal_time'] = $initialValList['holiday_normal_time'];
                $userPeriod['holiday_midnight_time_only'] = $initialValList['holiday_midnight_time_only'];
                $userPeriod['statutory_holiday_normal_time'] = $initialValList['statutory_holiday_normal_time'];
                $userPeriod['statutory_holiday_midnight_time_only'] = $initialValList['statutory_holiday_midnight_time_only'];
                $userPeriod['public_holiday_normal_time'] = $initialValList['public_holiday_normal_time'];
                $userPeriod['public_holiday_midnight_time_only'] = $initialValList['public_holiday_midnight_time_only'];

                array_push($addingUpUserList, $userPeriod);
            }

            $Log->trace("END addingUpUserAttendanceData");

            return $addingUpUserList;
        }

        /**
         * 従業員勤怠月合算処理
         * @param    $initialValList
         * @param    $userArray
         * @param    $attendanceArray
         * @param    $targetKeyName
         * @return   $addingUpUserList
         */
        private function insertUserAttendanceList( $initialValList, $userArray, $attendanceArray, $targetKeyName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START insertUserAttendanceList");

            if( ( $userArray['user_id'] == $attendanceArray['user_id'] ) && ( $userArray['organization_id'] == $attendanceArray['organization_id'] ) && ( $userArray['target_period']  === $attendanceArray[$targetKeyName]  ) )
            {
                $initialValList = $this->attendanceListTotalCalculation( $initialValList, $attendanceArray );
            }

            $Log->trace("END insertUserAttendanceList");

            return $initialValList;
        }

        /**
         * 集計行の整理
         * @param    $aggregateList
         * @param    $attendanceCnt
         * @return   $uDSummaryRow
         */
        private function changeFormatUserMonthSummaryRow( $aggregateList, $attendanceCnt )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeFormatUserMonthSummaryRow");

            $uMSummaryRow = array();

            $uMSummaryRow['user_id'] = 0;
            $uMSummaryRow['organization_id'] = 0;
            $uMSummaryRow['target_period'] = "-";
            $uMSummaryRow['user_name'] = "-";
            $uMSummaryRow['employees_no'] = "-";
            $uMSummaryRow['tel'] = "-";
            $uMSummaryRow['cellphone'] = "-";
            $uMSummaryRow['mail_address'] = "-";
            $uMSummaryRow['user_comment'] = "-";
            $uMSummaryRow['position_name'] = "-";
            $uMSummaryRow['employment_name'] = "-";
            $uMSummaryRow['section_name'] = "-";
            $uMSummaryRow['wage_form_name'] = "-";
            $uMSummaryRow['work_classification'] = "-";
            $uMSummaryRow['hourly_wage'] = "-";
            $uMSummaryRow['base_salary'] = "-";
            $uMSummaryRow['department_code'] = "-";
            $uMSummaryRow['organization_name'] = "-";
            $uMSummaryRow['abbreviated_name'] = "-";
            $uMSummaryRow['organization_comment'] = "-";
            $uMSummaryRow['total_working_time_con_minute'] = $aggregateList['total_working_time_con_minute'];
            $uMSummaryRow['weekday_working_con_minute'] = $aggregateList['weekday_working_con_minute'];
            $uMSummaryRow['weekend_working_con_minute'] = $aggregateList['weekend_working_con_minute'];
            $uMSummaryRow['legal_holiday_working_con_minute'] = $aggregateList['legal_holiday_working_con_minute'];
            $uMSummaryRow['law_closed_working_con_minute'] = $aggregateList['law_closed_working_con_minute'];
            $uMSummaryRow['overtime_con_minute'] = $aggregateList['overtime_con_minute'];
            $uMSummaryRow['weekday_overtime_con_minute'] = $aggregateList['weekday_overtime_con_minute'];
            $uMSummaryRow['weekend_overtime_con_minute'] = $aggregateList['weekend_overtime_con_minute'];
            $uMSummaryRow['legal_holiday_overtime_con_minute'] = $aggregateList['legal_holiday_overtime_con_minute'];
            $uMSummaryRow['law_closed_overtime_con_minute'] = $aggregateList['law_closed_overtime_con_minute'];
            $uMSummaryRow['night_working_time_con_minute'] = $aggregateList['night_working_time_con_minute'];
            $uMSummaryRow['weekday_night_working_con_minute'] = $aggregateList['weekday_night_working_con_minute'];
            $uMSummaryRow['weekend_night_working_con_minute'] = $aggregateList['weekend_night_working_con_minute'];
            $uMSummaryRow['legal_holiday_night_working_con_minute'] = $aggregateList['legal_holiday_night_working_con_minute'];
            $uMSummaryRow['law_closed_night_working_con_minute'] = $aggregateList['law_closed_night_working_con_minute'];
            $uMSummaryRow['night_overtime_con_minute'] = $aggregateList['night_overtime_con_minute'];
            $uMSummaryRow['weekday_night_overtime_con_minute'] = $aggregateList['weekday_night_overtime_con_minute'];
            $uMSummaryRow['weekend_night_overtime_con_minute'] = $aggregateList['weekend_night_overtime_con_minute'];
            $uMSummaryRow['legal_holiday_night_overtime_con_minute'] = $aggregateList['legal_holiday_night_overtime_con_minute'];
            $uMSummaryRow['law_closed_night_overtime_con_minute'] = $aggregateList['law_closed_night_overtime_con_minute'];
            $uMSummaryRow['break_time_con_minute'] = $aggregateList['break_time_con_minute'];
            $uMSummaryRow['late_time_con_minute'] = $aggregateList['late_time_con_minute'];
            $uMSummaryRow['leave_early_time_con_minute'] = $aggregateList['leave_early_time'];
            $uMSummaryRow['travel_time_con_minute'] = $aggregateList['travel_time_con_minute'];
            $uMSummaryRow['late_leave_time_con_minute'] = $aggregateList['late_leave_time'];
            $uMSummaryRow['fixed_overtime_time'] = $aggregateList['fixed_overtime_time'];
            $uMSummaryRow['modify_count'] = $aggregateList['modify_count'];
            $uMSummaryRow['late_time_count'] = $aggregateList['late_time_count'];
            $uMSummaryRow['leave_early_time_count'] = $aggregateList['leave_early_time_count'];
            $uMSummaryRow['late_leave_count'] = $aggregateList['late_leave_count'];
            $uMSummaryRow['attendance_time_count'] = $aggregateList['attendance_time_count'];
            $uMSummaryRow['weekday_attendance_time_count'] = $aggregateList['weekday_attendance_time_count'];
            $uMSummaryRow['weekend_attendance_time_count'] = $aggregateList['weekend_attendance_time_count'];
            $uMSummaryRow['legal_holiday_attendance_time_count'] = $aggregateList['legal_holiday_attendance_time_count'];
            $uMSummaryRow['law_closed_attendance_time_count'] = $aggregateList['law_closed_attendance_time_count'];
            $uMSummaryRow['rough_estimate'] = $aggregateList['rough_estimate'];
            $uMSummaryRow['shift_rough_estimate'] = $aggregateList['shift_rough_estimate'];
            $uMSummaryRow['absence_count'] = $aggregateList['absence_count'];
            $uMSummaryRow['prescribed_working_days'] = $aggregateList['prescribed_working_days'];
            $uMSummaryRow['prescribed_working_hours'] = $aggregateList['prescribed_working_hours'];
            $uMSummaryRow['weekday_prescribed_working_hours'] = $aggregateList['weekday_prescribed_working_hours'];
            $uMSummaryRow['weekend_prescribed_working_hours'] = $aggregateList['weekend_prescribed_working_hours'];
            $uMSummaryRow['legal_holiday_prescribed_working_hours'] = $aggregateList['legal_holiday_prescribed_working_hours'];
            $uMSummaryRow['law_closed_prescribed_working_hours'] = $aggregateList['law_closed_prescribed_working_hours'];
            $uMSummaryRow['statutory_overtime_hours'] = $aggregateList['statutory_overtime_hours'];
            $uMSummaryRow['nonstatutory_overtime_hours_all'] = $aggregateList['nonstatutory_overtime_hours_all'];
            $uMSummaryRow['nonstatutory_overtime_hours'] = $aggregateList['nonstatutory_overtime_hours'];
            $uMSummaryRow['nonstatutory_overtime_hours_45h'] = $aggregateList['nonstatutory_overtime_hours_45h'];
            $uMSummaryRow['nonstatutory_overtime_hours_less_than'] = $aggregateList['nonstatutory_overtime_hours_less_than'];
            $uMSummaryRow['nonstatutory_overtime_hours_60h'] = $aggregateList['nonstatutory_overtime_hours_60h'];
            $uMSummaryRow['statutory_overtime_hours_no_pub'] = $aggregateList['statutory_overtime_hours_no_pub'];
            $uMSummaryRow['nonstatutory_overtime_hours_no_pub_all'] = $aggregateList['nonstatutory_overtime_hours_no_pub_all'];
            $uMSummaryRow['nonstatutory_overtime_hours_no_pub'] = $aggregateList['nonstatutory_overtime_hours_no_pub'];
            $uMSummaryRow['nonstatutory_overtime_hours_45h_no_pub'] = $aggregateList['nonstatutory_overtime_hours_45h_no_pub'];
            $uMSummaryRow['nonstatutory_overtime_hours_no_pub_less_than'] = $aggregateList['nonstatutory_overtime_hours_no_pub_less_than'];
            $uMSummaryRow['nonstatutory_overtime_hours_60h_no_pub'] = $aggregateList['nonstatutory_overtime_hours_60h_no_pub'];
            $uMSummaryRow['overtime_hours_no_considered'] = $aggregateList['overtime_hours_no_considered'];
            $uMSummaryRow['overtime_hours_no_considered_no_pub'] = $aggregateList['overtime_hours_no_considered_no_pub'];
            $uMSummaryRow['normal_working_con_minute'] = $aggregateList['normal_working_con_minute'];
            $uMSummaryRow['normal_overtime_con_minute'] = $aggregateList['normal_overtime_con_minute'];
            $uMSummaryRow['normal_night_working_con_minute'] = $aggregateList['normal_night_working_con_minute'];
            $uMSummaryRow['normal_night_overtime_con_minute'] = $aggregateList['normal_night_overtime_con_minute'];
            $uMSummaryRow['embossing_department_code'] = "-";
            $uMSummaryRow['embossing_department_code'] = "-";
            $uMSummaryRow['embossing_organization_name'] = "-";
            $uMSummaryRow['embossing_abbreviated_name'] = "-";
            $uMSummaryRow['embossing_status'] = "-";
            $uMSummaryRow['attendance_unit_id'] = $attendanceCnt;

            // millionet oota 追加
            $uMSummaryRow['weekday_normal_time'] = $aggregateList['weekday_normal_time'];
            $uMSummaryRow['weekday_midnight_time_only'] = $aggregateList['weekday_midnight_time_only'];
            $uMSummaryRow['holiday_normal_time'] = $aggregateList['holiday_normal_time'];
            $uMSummaryRow['holiday_midnight_time_only'] = $aggregateList['holiday_midnight_time_only'];
            $uMSummaryRow['statutory_holiday_normal_time'] = $aggregateList['statutory_holiday_normal_time'];
            $uMSummaryRow['statutory_holiday_midnight_time_only'] = $aggregateList['statutory_holiday_midnight_time_only'];
            $uMSummaryRow['public_holiday_normal_time'] = $aggregateList['public_holiday_normal_time'];
            $uMSummaryRow['public_holiday_midnight_time_only'] = $aggregateList['public_holiday_midnight_time_only'];
            
            $Log->trace("END changeFormatUserMonthSummaryRow");

            return $uMSummaryRow;
        }

/*******************************
* 個人共通メソッド
********************************/

        /**
         * 閲覧権限による並び替え判定
         * @param    $postArray
         * @param    $attendanceList
         * @return   $userPeriodDayList
         */
        private function sortAttendanceListAccessLevel( $postArray, $attendanceList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START sortAttendanceListAccessLevel");

            if( $postArray['attendanceAccessId'] < 5 )
            {
                $sortAttendanceList = $this->creatAccessControlledList( $_SESSION["REFERENCE"], $attendanceList );
            }
            else
            {
                $sortAttendanceList = $attendanceList;
            }

            $Log->trace("END sortAttendanceListAccessLevel");

            return $sortAttendanceList;
        }

        /**
         * 集計行作成
         * @param    $attendanceList
         * @param    $attUnitCnt
         * @param    $dateFlag
         * @return   $userMonthSummaryRow
         */
        private function creatUserSummaryRow( $attendanceList, $attUnitCnt, $dateFlag )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatUserSummaryRow");

            $aggregateList = $this->entireAggregate( $attendanceList );

            if( empty( $dateFlag ) )
            {
                $userSummaryRow = $this->changeFormatUserDateSummaryRow( $aggregateList, $attUnitCnt );
            }
            else
            {
                $userSummaryRow = $this->changeFormatUserMonthSummaryRow( $aggregateList, $attUnitCnt );
            }

            $Log->trace("END creatUserSummaryRow");

            return $userSummaryRow;
        }

    }
?>
