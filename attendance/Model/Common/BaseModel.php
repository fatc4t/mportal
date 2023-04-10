<?php
    /**
     * @file    共通モデル(Model)
     * @author  USE Y.Sakata
     * @date    2016/04/27
     * @version 1.00
     * @note    共通で使用するモデルの処理を定義
     */

    // FwBaseControllerの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwBaseModel.php';
    require_once 'Model/Common/SecurityProcess.php';
    require_once 'Model/Common/SetPulldown.php';

    /**
     * 各モデルの基本クラス
     * @note    共通で使用するモデルの処理を定義
     */
    class BaseModel extends FwBaseModel
    {
        protected $securityProcess = null;       ///< セキュリティクラス
        public    $setPulldown = null;           ///< プルダウンクラス

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseModelのコンストラクタ
            parent::__construct();
            $this->securityProcess       = new SecurityProcess();
            $this->setPulldown           = new SetPulldown();
            $this->securityProcess->getAccessViewOrder();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // FwBaseModelのデストラクタ
            parent::__destruct();
        }

        /**
         * 使用中のマスタ情報を取得
         * @param   $idName（各機能のシーケンスID名）
         * @return  呼び出し先のシーケンスIDリスト
         */
        public function getInUseCheckList($idName)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getInUseCheckList");

            $sql = 'SELECT ud.organization_id, ud.position_id'
                . ' , ud.employment_id, ud.section_id'
                . ' , ud.security_id FROM m_user_detail ud';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $inUseCheckArray = array();
            
            if( $result === false )
            {
                $Log->trace("END getInUseCheckList");
                return $inUseCheckArray;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($inUseCheckArray, $data[$idName]);
            }
            
            //配列で重複している物を削除する
            $unique = array_unique($inUseCheckArray);
            //キーが飛び飛びになっているので、キーを振り直す
            $inUseCheckList = array_values($unique);

            $Log->trace("END getInUseCheckList");

            return $inUseCheckList;
        }

        /**
         * 従業員マスタで使用している手当マスタ情報を取得
         * @param   $allowanceIdName（各機能のシーケンスID名）
         * @return  呼び出し先のシーケンスIDリスト
         */
        public function getInUseAllowanceCheckList($allowanceIdName)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getInUseAllowanceCheckList");

            $sql = 'SELECT ua.user_allowance_id, ua.allowance_id'
                . ' FROM m_user_allowance ua';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $inUseCheckArray = array();
            
            if( $result === false )
            {
                $Log->trace("END getInUseAllowanceCheckList");
                return $inUseCheckArray;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($inUseCheckArray, $data[$allowanceIdName]);
            }
            
            //配列で重複している物を削除する
            $unique = array_unique($inUseCheckArray);
            //キーが飛び飛びになっているので、キーを振り直す
            $inUseCheckList = array_values($unique);

            $Log->trace("END getInUseAllowanceCheckList");

            return $inUseCheckList;
        }

        /**
         * 表示項目マスタ情報取得
         * @return   $displayItemList
         */
        public function getDisplayItemData()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getDisplayItemData");

            $searchArray = array( ':display_item_id' => $_SESSION["DISPLAY_ITEM_ID"], );

            $sql = ' SELECT display_format, no_data_format FROM m_display_item WHERE display_item_id = :display_item_id ';
            $result = $DBA->executeSQL($sql, $searchArray);
            $displayItemData = array();
            if( $result === false )
            {
                $Log->trace("END getDisplayItemList");
                return $displayItemData;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $displayItemData = array(
                    'display_format' => $data['display_format'],
                    'no_data_format' => $data['no_data_format'],
                );
            }

            $Log->trace("END getDisplayItemData");

            return $displayItemData;
        }

        /**
         * 表示項目マスタ情報取得
         * @param    $postArray
         * @param    $dateFlag
         * @return   $displayItemList
         */
        public function getDisplayItemList( $postArray, $dateFlag )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getDisplayItemList");

            $searchArray = array( ':display_item_id' => $_SESSION["DISPLAY_ITEM_ID"], );

            $sql = ' SELECT dd.output_type_id, dd.output_item_id, dd.output_item_branch, dd.item_name '
                 . ' , ( SELECT count( output_item_branch ) as branch_main FROM m_display_item_detail d WHERE d.output_type_id = dd.output_type_id AND d.output_item_id = dd.output_item_id AND d.display_item_id = :display_item_id AND d.output_item_branch = 0 AND d.is_display = 1 ) '
                 . ' , ( SELECT count( output_item_branch ) as branch_cnt FROM m_display_item_detail d WHERE d.output_type_id = dd.output_type_id AND d.output_item_id = dd.output_item_id AND d.display_item_id = :display_item_id AND d.output_item_branch > 0 AND d.is_display = 1 ) '
                 . ' , CASE WHEN moil.width IS NULL THEN 50 '
                 . '        ELSE moil.width '
                 . '   END width '
                 . ' FROM m_display_item_detail dd LEFT OUTER JOIN public.m_output_item_list moil ON dd.output_type_id = 1 AND dd.output_item_id = moil.output_item_list_id '
                 . ' WHERE dd.display_item_id = :display_item_id AND dd.is_display = 1 ORDER BY dd.disp_order ';

            $result = $DBA->executeSQL($sql, $searchArray);
            $displayItemList = array();
            if( $result === false )
            {
                $Log->trace("END getDisplayItemList");
                return $displayItemList;
            }

            $displayItemList = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $displayItemList = $this->setDisplayList( $displayItemList, $data, $postArray, $dateFlag );
            }

            $Log->trace("END getDisplayItemList");
            return $displayItemList;
        }

        /**
         * 給与出力マスタ情報取得
         * @param    $postArray
         * @param    $payrollSystemId
         * @return   $payrollSystemList
         */
        public function getPayrollSystemList( $postArray, $payrollSystemID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getPayrollSystemList");

            $searchArray = array( ':payroll_system_id' => $payrollSystemID, );

            $sql = ' SELECT ps.output_type_id, ps.output_item_id, ps.output_item_branch, ps.item_name '
                 . ' , ( SELECT count( output_item_branch ) as branch_main FROM m_payroll_system_detail p WHERE p.output_type_id = ps.output_type_id AND p.output_item_id = ps.output_item_id AND p.payroll_system_id = :payroll_system_id AND p.output_item_branch = 0 AND p.is_display = 1 ) '
                 . ' , ( SELECT count( output_item_branch ) as branch_cnt FROM m_payroll_system_detail p WHERE p.output_type_id = ps.output_type_id AND p.output_item_id = ps.output_item_id AND p.payroll_system_id = :payroll_system_id AND p.output_item_branch > 0 AND p.is_display = 1 ) '
                 . ' FROM m_payroll_system_detail ps WHERE ps.payroll_system_id = :payroll_system_id AND ps.is_display = 1 ORDER BY ps.disp_order ';

            $result = $DBA->executeSQL( $sql, $searchArray );
            $payrollSystemList = array();
            if( $result === false )
            {
                $Log->trace("END getPayrollSystemList");
                return $payrollSystemList;
            }

            $payrollList = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $payrollList, $data);
            }
            foreach( $payrollList as $payroll )
            {
                if( $payroll['output_type_id'] == 1)
                {
                    if( !in_array( $payroll['output_item_id'], $postArray ) )
                    {
                        array_push( $payrollSystemList, $payroll);
                    }
                }
                else
                {
                    array_push( $payrollSystemList, $payroll);
                }
            }

            $Log->trace("END getPayrollSystemList");
            return $payrollSystemList;
        }

        /**
         * 指定期間開始日のUnix値取得
         * @param    $year
         * @param    $month
         * @param    $day
         * @return   $dateUnix
         */
        public function setStartDateUnix( $year, $month, $day )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setStartDateUnix");

            // 検索対象年が空の場合、勤怠一覧に登録されているもっとも古い日時データを取得し展開
            if( empty( $year ) )
            {
                $periodDate = $this->searchAttendanceRecordOldestDate();
                list($year, $month, $day) = explode("/", $periodDate);
            }
            // 開始月が空であれば01月を指定する
            $month = $this->setStartInitialDate($month);
            // 開始日が空であれば01日を指定する
            $day = $this->setStartInitialDate($day);
            $startDate = $year . "/" . $month . "/" . $day;

            // 指定期間のUNIX変換
            $dateUnix = strtotime($startDate);

            $Log->trace("END setStartDateUnix");

            return $dateUnix;
        }

        /**
         * 指定期間終了日のUnix値取得
         * @param    $year
         * @param    $month
         * @param    $day
         * @return   $dateUnix
         */
        public function setEndDateUnix( $year, $month, $day )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setEndDateUnix");

            // 検索対象年が空の場合,現在日前日の日付を展開
            if( empty( $year ) )
            {
                $periodDate = date("Y/m/d", strtotime("-1 day"));
                list($year, $month, $day) = explode("/", $periodDate);
            }
            // 終了月が空であれば12月を指定する
            $month = $this->setEndInitialDate($month, false, $year);
            // 終了日が空であれば終了月の月末最終日を指定する
            $day = $this->setEndInitialDate($day, $month, $year);
            $endDate = $year . "/" . $month . "/" . $day;

            // 指定期間のUNIX変換
            $dateUnix = strtotime($endDate);

            $Log->trace("END setEndDateUnix");

            return $dateUnix;
        }

        /**
         * 指定日に、ユーザが在籍していたを取得する
         * @param    $userID   ユーザID
         * @param    $date     日付
         * @return   true：在籍     false：在籍していない
         */
        public function isEnrollment( $userID, $date )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START isEnrollment");
            
            // ユーザIDから、在籍状況を取得
            $sql  = " SELECT user_id FROM m_user  "
                  . " WHERE  user_id = :user_id AND hire_date <= :date AND ( :date <= leaving_date OR leaving_date IS NULL ) AND is_del = 0 ";

            $parameters = array( ':user_id'  => $userID,
                                 ':date'     => $date,  
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = false;
            if( $result === false )
            {
                $Log->trace("END isEnrollment");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = true;
            }

            $Log->trace("END isEnrollment");
            return $ret;
        }

        /**
         * 指定配列の重複削除
         * @param    $array           プルダウンリスト
         * @param    $column          配列のキーとする名称
         * @return   $uniqueArray     重複削除したリスト
         */
        protected function getUniqueArray($array, $column)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getUniqueArray");
            
            $uniqueArray = $this->setPulldown->getUniqueArray($array, $column);
            
            $Log->trace("END getUniqueArray");
            return $uniqueArray;
        }
        
        /**
         * 1テーブル更新
         * @param    $sql           実行するSQL文
         * @param    $parameters    実行するSQL文のパラメータ
         * @param    $tableName     INSERTしたテーブル名を指定する(その他は指定なし)
         * @return   実行結果
         */
        protected function executeOneTableSQL( $sql, $parameters, $tableName = "" )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START executeOneTableSQL");

            // 親クラスの呼び出し
            $ret = parent::executeOneTableSQL( $sql, $parameters, $tableName );

            // 更新結果がOKである
            if( "MSG_FW_OK" === $ret )
            {
                // 勤怠システム用のOKフラグに修正
                $ret = "MSG_BASE_0000";
            }

            $Log->trace("END executeOneTableSQL");
            return $ret;
        }

        /**
         * 指定された期間の日付リストを作成する
         * @param    $startUnix
         * @param    $endUnix
         * @param    $sec
         * @return   $dateList
         */
        protected function creatDateList( $startUnix, $endUnix, $sec )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatDateList");

            // 指定期間の日時リスト作成
            $dateList =array();
            for ( $i = $startUnix; $i <= $endUnix; $i += $sec )
            {
                $date = array(
                    'periodSpecified' => date("Y/m/d",$i),
                );
                array_push($dateList, $date);
            }

            $Log->trace("END creatDateList");
            return $dateList;
        }
        
        /**
         * タイムスタンプ型変換設定
         * @param    $timeCheck(postArrayの値)
         * @param    $startFlag(開始時間か終了時間かのフラグ)
         * @return   $timetime(変換後の値)
         */
        protected function changeTimestamp($timeCheck, $startFlag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeTimestamp");
            
            $timetime = $timeCheck;
            if(!empty($timetime))
            {
                if( !empty( $startFlag ) )
                {
                    $timetime = '2016-04-01 ' . $timetime . ':00';
                }
                else
                {
                    list($time , $minute) = explode(":", $timetime);
                    if((int)$time <  24)
                    {
                        $timetime = '2016-04-01 ' . $timetime . ':00';
                    }
                    else
                    {
                        $timetime = $time - 24 . ":" . $minute;
                        $timetime = '2016-04-02 ' .  $timetime  . ':00';
                    }
                }
            }
            
            $Log->trace("END changeTimestamp");
            return $timetime;
        }

        /**
         * 指定期間の各月月初日月末日のリスト作成
         * @param    $startYear
         * @param    $startMonth
         * @param    $endYear
         * @param    $endMonth
         * @return   $startEndList
         */
        protected function creatMonthStartEndList( $startYear, $startMonth, $endYear, $endMonth )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatMonthStartEndList");

            $startEndList = array();

            $startYearInt = intval($startYear);
            $endYearInt = intval($endYear);
            $yearIntdifference = $endYearInt - $startYearInt;
            $startMonthInt = intval($startMonth);
            $endMonthInt = intval($endMonth);

            for( $yearCnt = $startYearInt; $yearCnt <= $endYearInt; $yearCnt++ )
            {
                if( $yearIntdifference > 0 )
                {
                    // 指定期間開始年が指定期間終了年の前年以降の場合、
                    $monthPeriodArray = $this->checkMonthPeriod( $yearCnt, $startYearInt, $endYearInt, $startMonthInt, $endMonthInt );

                    $startEndList = $this->setStartEndList( $startEndList, $yearCnt, $monthPeriodArray['monthfirst'], $monthPeriodArray['monthLimit'] );
                }
                else
                {
                    $startEndList = $this->setStartEndList( $startEndList, $yearCnt, $startMonthInt, $endMonthInt);
                }
            }

            $Log->trace("END creatMonthStartEndList");

            return $startEndList;
        }

        /**
         * 指定期間の各年の年始年末のリスト作成
         * @param    $startYear
         * @param    $endYear
         * @return   $startEndList
         */
        protected function creatYearStartEndList( $startYear, $endYear )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatYearStartEndList");

            $startEndList = array();

            $startYearInt = intval($startYear);
            $endYearInt = intval($endYear);

            for( $yearCnt = $startYearInt; $yearCnt <= $endYearInt; $yearCnt++ )
            {
                $startDate = $yearCnt . "/01/01";
                $endDate = $yearCnt . "/12/31";
                $dateArray = array(
                    'targetYear'      => strval( $yearCnt ),
                    'targetFirstDate' => $startDate,
                    'targetLastDate'  => $endDate,
                );
                array_push($startEndList, $dateArray);
            }

            $Log->trace("END creatYearStartEndList");

            return $startEndList;
        }

        /**
         * SQL実行
         * @param    $sql
         * @param    $searchArray
         * @return   $infoList
         */
        protected function runListGet( $sql, $searchArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START runListGet");

            $infoList = array();
            $result = $DBA->executeSQL($sql, $searchArray);
            if( $result === false )
            {
                $Log->trace("END runListGet");
                return $infoList;
            }
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $infoList, $data);
            }

            $Log->trace("END runListGet");

            return $infoList;
        }

        /**
         * ユーザの就業情報を取得する
         * @param    $userInfo     ユーザ情報
         * @return   ユーザの就業情報
         */
        protected function getUserEmploymentInfo( $userInfo )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserEmploymentInfo");
            
            // 就業開始時間と、就業規則IDを取得
            $sql  = " SELECT "
                  . "     vo.start_time_day "
                  . "   , vo.priority_o "
                  . "   , vo.priority_p "
                  . "   , vo.priority_e "
                  . "   , vo.labor_regulations_id as o_labor_id "
                  . "   , me.labor_regulations_id as e_labor_id "
                  . "   , mp.labor_regulations_id as p_labor_id "
                  . " FROM v_organization vo "
                  . "      INNER JOIN m_employment me ON me.employment_id = :employment_id "
                  . "      INNER JOIN m_position mp ON mp.position_id = :position_id "
                  . " WHERE vo.eff_code = '適用中' AND vo.organization_id = :organization_id ";

            $parameters = array( 
                                    ':employment_id'    => $userInfo['employment_id'],
                                    ':position_id'      => $userInfo['position_id'],
                                    ':organization_id'  => $userInfo['organization_id'],
                                );

            $result = $DBA->executeSQL($sql, $parameters);

            if( $result === false )
            {
                $Log->trace("END getUserEmploymentInfo");
                return false;
            }

            $startTime = "";
            $laborList = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $startTime = substr( $data['start_time_day'], 0, 5 );
                // 0の場合、未選択となる為、値を入れない
                if( $data['priority_o'] > 0 )
                {
                    $laborList[$data['priority_o']] = $data['o_labor_id'];
                }
                if( $data['priority_e'] > 0 )
                {
                    $laborList[$data['priority_e']] = $data['e_labor_id'];
                }
                if( $data['priority_p'] > 0 )
                {
                    $laborList[$data['priority_p']] = $data['p_labor_id'];
                }
            }

            // 就業規則の適用順を優先順位の昇順で並び替え
            ksort($laborList);

            $laborRegulationsInfo = array();
            foreach( $laborList as $laborID )
            {
                if( 0 != $laborID )
                {
                    $laborRegulationsInfo = $this->getLaborRegulationsInfo( $laborID );
                    break;
                }
            }
            $ret = array();
            // 開始時間を返り値にマージ
            $ret = array_merge($laborRegulationsInfo, array( 'start_time_day' => $startTime ) );
            
            $Log->trace("END getUserEmploymentInfo");
            return $ret;
        }

        /**
         * 就業規則情報を取得する
         * @param    $laborRegulationsID     就業規則ID
         * @return   就業規則情報
         */
        protected function getLaborRegulationsInfo( $laborRegulationsID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getLaborRegulationsInfo");
            
            // 就業開始時間と、就業規則IDを取得
            $sql  = " SELECT "
                  . "     vlr.labor_regulations_id "
                  . "   , vlr.application_date_id "
                  . "   , mwrt.attendance_rounding_time "
                  . "   , mwrt.attendance_rounding "
                  . "   , mwrt.break_rounding_start_time "
                  . "   , mwrt.break_rounding_start "
                  . "   , mwrt.break_rounding_end_time "
                  . "   , mwrt.break_rounding_end "
                  . "   , mwrt.leave_work_rounding_time "
                  . "   , mwrt.leave_work_rounding "
                  . "   , is_shift_holiday_use "
                  . "   , is_organization_calendar_holiday_use"
                  . " FROM v_labor_regulations vlr INNER JOIN m_work_rules_time mwrt  ON  mwrt.labor_regulations_id = vlr.labor_regulations_id "
                  . "                                     AND vlr.eff_code = '適用中' AND mwrt.application_date_id  = vlr.application_date_id "
                  . " WHERE vlr.labor_regulations_id = :labor_regulations_id ";

            $parameters = array( ':labor_regulations_id'    => $laborRegulationsID, );
            $result = $DBA->executeSQL($sql, $parameters);

            if( $result === false )
            {
                $Log->trace("END getLaborRegulationsInfo");
                return false;
            }

            $ret = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data;
            }

            $Log->trace("END getLaborRegulationsInfo");
            return $ret;
        }

        /**
         * 就業規則情報を取得する
         * @param    $laborRegulationsID   就業規則ID
         * @param    $targetDate           対象日(指定なしは、当日)
         * @return   就業規則情報
         */
        protected function getLaborRegulationsAllInfo( $laborRegulationsID, $targetDate = '' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getLaborRegulationsAllInfo");
            
            // 取得日を設定
            if( $targetDate == '' )
            {
                // 当日の日付を設定
                $targetDate = date("Y/m/d");
            }
            
            // 就業規則IDから、就業規則適用期間IDを取得
            $appDateID = $this->getApplicationDateID( $laborRegulationsID, $targetDate );

            $ret = array();
            // 就業規則休憩時間マスタ情報を取得
            $ret['m_work_rules_break'] = $this->getBreakTimeForBinding( $laborRegulationsID, $appDateID );
            // 就業規則休憩シフトマスタ情報を取得
            $ret['m_work_rules_shift_break'] = $this->getBreakTimeForBindingShift( $laborRegulationsID, $appDateID );
            // 就業規則休憩時間帯マスタ情報を取得
            $ret['m_break_time_zone'] = $this->getBreakTimeZoneForBindingShift( $laborRegulationsID, $appDateID );
            // 就業規則時給変更マスタ情報を取得
            $ret['m_hourly_wage_change'] = $this->getHourlyWageChange( $laborRegulationsID, $appDateID );
            // 残業時間マスタ情報を取得
            $ret['m_overtime'] = $this->getOvertime( $laborRegulationsID, $appDateID );
            // 就業規則手当マスタ情報を取得
            $ret['m_work_rules_allowance'] = $this->getWorkRulesAllowance( $laborRegulationsID, $appDateID );
            // 就業規則時間マスタ情報を取得
            $ret['m_work_rules_time'] = $this->getWorkRulesTime( $laborRegulationsID, $appDateID );

            $Log->trace("END getLaborRegulationsAllInfo");
            return $ret;
        }

        /**
         * シフト情報を取得する
         * @param    $userInfo          ユーザ情報
         * @param    $embossingDate     打刻日
         * @param    $iEmbossingTime    打刻時間(分)
         * @return   シフト情報
         */
        protected function getShiftInfo( $userInfo, $embossingDate, $iEmbossingTime )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getShiftInfo");
            
            // シフト情報を取得
            $sql  = " SELECT shift_id, is_holiday, attendance, taikin FROM t_shift WHERE  user_id = :user_id AND day = :day ORDER BY attendance";

            $parameters = array( 
                                    ':user_id'    => $userInfo['user_id'], 
                                    ':day'        => $embossingDate, 
                                );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $ret = array(
                            'shift_id'      =>  0,
                            'is_holiday'    =>  0,
                            'attendance'    =>  '',
                            'taikin'        =>  '',
                        );
                $Log->trace("END getShiftInfo");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( empty($ret) )
                {
                    $ret = $data;
                }
                // 現在のシフト時間を分に修正
                $iRetTime = $this->changeMinuteFromTime( substr($ret['attendance'], 11, 5 ) );
                
                // 取得データのシフト時間を分に修正
                $iDataTime = $this->changeMinuteFromTime( substr($data['attendance'], 11, 5 ) );

                // 打刻時間との差分が少ないデータを保持する
                if( abs( $iEmbossingTime - $iRetTime ) > abs( $iEmbossingTime - $iDataTime ) )
                {
                    // 新規で取得したデータの方が差分が少ない為、保持データを更新
                    $ret = $data;
                }
            }

            // データが未取得の場合
            if( empty($ret) )
            {
                $ret = array(
                                'shift_id'      =>  0,
                                'is_holiday'    =>  0,
                                'attendance'    =>  '',
                                'taikin'        =>  '',
                            );
            }

            $Log->trace("END getShiftInfo");

            return $ret;
        }

        /**
         * ユーザ情報を取得する
         * @param    $userID      ユーザID
         * @param    $targetDate  対象日(指定なしは、当日)
         * @return   従業員情報
         */
        protected function getUserInfo( $userID, $targetDate = '' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserInfo");
            
            // 取得日を設定
            if( $targetDate == '' )
            {
                // 当日の日付を設定
                $targetDate = date("Y/m/d");
            }
            
            // 従業員Noから、ユーザIDを取得
            $sql  = " SELECT user_id, employment_id, employment_name, position_id, position_name, organization_id, abbreviated_name, employees_no, application_date_start FROM v_user "
                  . " WHERE  application_date_start <= :targetDate AND user_id = :user_id "
                  . " ORDER BY application_date_start DESC offset 0 limit 1 ";

            $parameters = array( 
                                    ':user_id'      => $userID, 
                                    ':targetDate'   => $targetDate,
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array(
                            'user_id'           => 0,
                            'employment_id'     => 0,
                            'position_id'       => 0,
                            'organization_id'   => 0,
                            'employment_name'   => '',
                            'position_name'     => '',
                            'abbreviated_name'  => '',
                            'employees_no'      => '',
                        );
            if( $result === false )
            {
                $Log->trace("END getUserInfo");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = array(
                                'user_id'           => $data['user_id'],
                                'employment_id'     => $data['employment_id'],
                                'position_id'       => $data['position_id'],
                                'organization_id'   => $data['organization_id'],
                                'employment_name'   => $data['employment_name'],
                                'position_name'     => $data['position_name'],
                                'abbreviated_name'  => $data['abbreviated_name'],
                                'employees_no'      => $data['employees_no'],
                            );
            }

            $Log->trace("END getUserInfo");
            return $ret;
        }

        /**
         * 休日情報を取得
         * @param    $locationOrganID   打刻場所の組織ID
         * @param    $userInfo          ユーザ情報
         * @param    $shiftInfo         シフト情報
         * @param    $employInfo        就業規則情報
         * @param    $embossingDate     打刻日
         * @return   休暇情報
         */
        protected function getHolidayInfo( $locationOrganID, $userInfo, $shiftInfo, $employInfo, $embossingDate )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getHolidayInfo");
            // 休日情報を、シフトから取得する
            if( $employInfo['attendance_rounding_time'] == 1 )
            {
                // シフト情報が存在するか
                if( $shiftInfo['shift_id'] != 0 )
                {
                    // シフトから休日情報を取得
                    $Log->trace("END getHolidayInfo");

                    return $shiftInfo['is_holiday'];
                }
            }

            // 休日情報を組織カレンダーから取得する
            if( $employInfo['is_organization_calendar_holiday_use' ] == 1 )
            {
                 $shiftInfo = $this->getCalenderHolidayDetailInfo( $embossingDate, $locationOrganID );

                 $Log->trace("END getHolidayInfo");
                 return $shiftInfo;
            }
            $Log->trace("END getHolidayInfo");
            
            // シフト/組織カレンダーともに休日情報を取得しない場合、0を設定
            return 0;
        }

        /**
         * 勤務時間帯を配列に分割する
         * @param    $workHours   勤務時間帯
         * @return   勤務時間帯の配列
         */
        protected function splitWorkHours( $workHours )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START splitWorkHours");

            $ret = array();

            $checkBit = 1; // チェックビット
            for( $i = 0; $i < 24; $i++ )
            {
                $bit = $workHours & $checkBit;
                $setBit =  0;
                if( $bit > 0 )
                {
                    $setBit = 1;
                }
                array_push( $ret, $setBit );
                $checkBit = $checkBit * 2;  // 次のビットをチェック
            }

            $Log->trace("END splitWorkHours");

            return $ret;
        }

        /**
         * 就業規則適用期間マスタIDを取得する
         * @param    $laborRegulationsID   就業規則ID
         * @param    $targetDate           対象日
         * @return   就業規則適用期間マスタID
         */
        protected function getApplicationDateID( $laborRegulationsID, $targetDate )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getApplicationDateID");
            
            // 就業規則IDから、就業規則適用期間IDを取得
            $sql  = " SELECT application_date_id "
                  . " FROM v_labor_regulations "
                  . " WHERE labor_regulations_id = :labor_regulations_id AND application_date_start <= :targetDate "
                  . " ORDER BY application_date_start DESC offset 0 limit 1 ";

            $parameters = array( 
                                    ':labor_regulations_id'    => $laborRegulationsID, 
                                    ':targetDate'              => $targetDate,
                               );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = 0;
            if( $result === false )
            {
                $Log->trace("END getApplicationDateID");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data['application_date_id'];
            }

            $Log->trace("END getApplicationDateID");
            return $ret;
        }

        /**
         * 残業時間マスタ情報を取得する
         * @param    $laborRegulationsID    就業規則ID
         * @param    $appDateID             就業規則適用期間ID
         * @return   残業時間マスタ情報
         */
        protected function getOvertime ( $laborRegulationsID, $appDateID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getHourlyWageChange");
            
            // 就業規則IDから、就業規則適用期間IDを取得
            $sql  = " SELECT overtime_detail_id, regular_working_hours_time, overtime_reference_time, closing_date_set_id  "
                  . " FROM m_overtime "
                  . " WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id "
                  . " ORDER BY overtime_id ";

            $parameters = array(
                                    ':labor_regulations_id'    => $laborRegulationsID, 
                                    ':application_date_id'     => $appDateID,
                               );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getOvertime");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret, $data);
            }

            $Log->trace("END getOvertime");
            return $ret;
        }

        /**
         * 表示項目マスタ情報取得
         * @param    $displayItemList
         * @param    $data
         * @param    $postArray
         * @param    $dateFlag
         * @return   $displayItemList
         */
        private function setDisplayList( $displayItemList, $data, $postArray, $dateFlag )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setDisplayList");
            
            if($dateFlag === $Log->getMsgLog('MSG_CUMULATIVE_TIME') )
            {
                if( $data['output_type_id'] == 1)
                {
                    if( in_array( $data['output_item_id'], $postArray ) )
                    {
                        array_push( $displayItemList, $data);
                    }
                }
            }
            else
            {
                if( $data['output_type_id'] == 1)
                {
                    if( !in_array( $data['output_item_id'], $postArray ) )
                    {
                        array_push( $displayItemList, $data);
                    }
                }
                else
                {
                    array_push( $displayItemList, $data);
                }
            }

            $Log->trace("END setDisplayList");

            return $displayItemList;
        }

        /**
         * カレンダー詳細マスタ、組織カレンダーマスタから休日情報を取得する
         * @param    $embossingDate     日付
         * @param    $locationOrganID   組織ID
         * @return   休日情報
         */
        private function getCalenderHolidayDetailInfo( $embossingDate, $locationOrganID)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCalenderHolidayDetailInfo");

            $sql = ' SELECT mcd.is_holiday '
                  . ' FROM  m_calendar_detail mcd INNER JOIN m_organization_calendar moc ON mcd.organization_calendar_id = moc.organization_calendar_id '
                  . '       WHERE mcd.date = :date AND moc.organization_id = :organization_id';

            $parameters = array( 
                                    ':date'    => $embossingDate, 
                                    ':organization_id'        => $locationOrganID, 
                                );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = 0;
            //カレンダー詳細マスタにデータがある場合
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $Log->trace("END getCalenderHolidayDetailInfo");
                return $data['is_holiday'];
            }
            
            //カレンダー詳細マスタにデータがない場合
            //組織カレンダーマスタから休日情報を取得する
            $ret = $this->getCalenderHolidayInfo( $embossingDate, $locationOrganID);

            $Log->trace("END getCalenderHolidayDetailInfo");
            return $ret;
        }

        /**
         * 組織カレンダーマスタから休日情報を取得する
         * @param    $embossingDate     日付
         * @param    $locationOrganID   組織ID
         * @return   休日情報
         */
        private function getCalenderHolidayInfo( $embossingDate, $locationOrganID)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCalenderHolidayInfo");

            $sql = ' SELECT is_sunday, is_public_holiday, is_saturday_1, is_saturday_2, is_saturday_3, is_saturday_4, is_saturday_5  '
                  . ' FROM  m_organization_calendar '
                  . ' WHERE organization_id = :organization_id';

            $parameters = array( 
                                    ':organization_id'        => $locationOrganID, 
                                );

            $result = $DBA->executeSQL($sql, $parameters);

            //日付から曜日を取得する
            $datetime = new DateTime($embossingDate);
            $weeklist = (int)$datetime->format('weeklist');

            //日付を"-"区切りに修正
            $replaceDate = str_replace("/", "-", $embossingDate);

            //組織カレンダーマスタにデータがある場合
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                
                //該当の日付が日曜日の場合
                if($weeklist === 0)
                {
                    $Log->trace("END getCalenderHolidayInfo");
                    return $data['is_sunday'];
                }

                //該当の日付が祝日の場合
                else if( "" != $this->getPublicHolidayName($replaceDate) )
                {
                   $Log->trace("END getCalenderHolidayInfo");
                   return $data['is_public_holiday'];
                }

                //該当の日付が土曜日の場合
                else if( $weeklist === 6 )
                {
                    //該当の日付が月の第何週か確認する
                    $getWeek = $this->getWeek($embossingDate);
                    $str = 'is_saturday_' . $getWeek;
                    $Log->trace("END getCalenderHolidayInfo");
                    return $data[$str];
                }
            }
            
            //組織カレンダーマスタにデータがない場合
            $Log->trace("END getCalenderHolidayDetailInfo");

            return 0;
        }

        /**
         * 該当の日付が月の第何週目か取得する
         * @param    $embossingDate     日付
         * @return   月の第何週目か
         */
        private function getWeek($embossingDate)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getWeek");
            
            $now = strtotime($embossingDate); 
            $saturday = 6;
            $week_day = 7;
            $w = intval(date('w',$now));
            $d = intval(date('d',$now));
            if ($w!=$saturday)
            {
                $w = ($saturday - $w) + $d;
            } 
            else 
            { 
                $w = $d;
            }
            
            $Log->trace("END getWeek");
            
            return ceil($w/$week_day);
         
        }

        /**
         * 閲覧権限がある組織所属の従業員の勤怠一覧の一番古い登録日付を取得
         * @return   $oldestDate
         */
        private function searchAttendanceRecordOldestDate()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START searchAttendanceRecordOldestDate");

            $searchedColumn = ' WHERE ';
            $sql = " SELECT to_char(min(date), 'yyyy/mm/dd') as oldest_date FROM v_attendance_record ";
            $sql .= $this->creatSqlWhereInConditions($searchedColumn);
            $sql .= ' AND is_del = :is_del ';
            $parameters = array( ':is_del' => 0, );
            $result = $DBA->executeSQL($sql, $parameters);

            $oldestDate = "";
            if( $result === false )
            {
                $Log->trace("END searchAttendanceRecordOldestDate");
                return $oldestDate;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $oldestDate = $data['oldest_date'];
            }

            $Log->trace("END searchAttendanceRecordOldestDate");

            return $oldestDate;
        }

        /**
         * 指定期間開始月開始日が空であれば01を返す
         * @param    $startPeriod
         * @return   $initial
         */
        private function setStartInitialDate($startPeriod)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setStartInitialDate");

            // 開始月が空であれば1月を指定する
            if(empty( $startPeriod ))
            {
                $initial = "01";
            }
            else
            {
                $initial = $startPeriod;
            }

            $Log->trace("END setStartInitialDate");
            return $initial;
        }

        /**
         * 指定期間終了月が空であれば12、終了日が空であれば、対象日の月末日を返す
         * @param    $endPeriod
         * @param    $monthVal
         * @param    $yearVal
         * @return   $initial
         */
        private function setEndInitialDate($endPeriod, $monthVal, $yearVal)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setEndInitialDate");

            // 開始月が空であれば1月を指定する
            if( empty( $endPeriod ) )
            {
                if( empty($monthVal) )
                {
                    $initial = "12";
                }
                else
                {
                    $initial = date('d', mktime(0, 0, 0, $monthVal + 1, 0, $yearVal));
                }
            }
            else
            {
                $initial = $endPeriod;
            }

            $Log->trace("END setEndInitialDate");
            return $initial;
        }

        /**
         * 指定期間の各月月初日月末日のリスト作成
         * @param    $yearCnt
         * @param    $startYearInt
         * @param    $endYearInt
         * @param    $startMonthInt
         * @param    $endMonthInt
         * @return   $monthPeriodArray
         */
        private function checkMonthPeriod( $yearCnt, $startYearInt, $endYearInt, $startMonthInt, $endMonthInt )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START checkMonthPeriod");

            $monthPeriodArray = array();

            // 年の繰り返しカウントが指定終了年未満の場合は月の初めを指定開始月か1月に設定。それ以外は1月に設定した上で指定終了つきまでのリストを作成
            if( ( $endYearInt - $yearCnt ) > 0)
            {
                // 年の繰り返しカウントが指定開始年と同じ場合は指定開始月から12月までのリストを作るため初めの値を指定開始月とする。それ以外は1から12までのリストを作成
                if( $startYearInt == $yearCnt )
                {
                    $monthfirst = $startMonthInt;
                    $monthLimit = 12;
                }
                else
                {
                    $monthfirst = 1;
                    $monthLimit = 12;
                }
            }
            else
            {
                $monthfirst = 1;
                $monthLimit = $endMonthInt;
            }

            $monthPeriodArray =array(
                'monthfirst' => $monthfirst,
                'monthLimit' => $monthLimit,
            );

            $Log->trace("END checkMonthPeriod");

            return $monthPeriodArray;
        }

        /**
         * 指定期間の各月月初日月末日のリストセット
         * @param    $startEndList
         * @param    $yearCnt
         * @param    $start
         * @param    $end
         * @return   $startEndList
         */
        private function setStartEndList( $startEndList, $yearCnt, $start, $end )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setStartEndList");

            for( $monthCnt = $start; $monthCnt <= $end; $monthCnt++ )
            {
                $eachMonth = sprintf( '%02d', $monthCnt );
                $targetMonth = $yearCnt . "/" . $eachMonth;
                $eachMonthStart = $targetMonth . "/01" ;
                $eachMonthEnd = date( 'Y/m/d', mktime(0, 0, 0, date( $eachMonth ) + 1, 0, date( $yearCnt ) ) );
                $dateArray = array(
                    'targetMonth'     => $targetMonth,
                    'targetFirstDate' => $eachMonthStart,
                    'targetLastDate'  => $eachMonthEnd,
                );
                array_push($startEndList, $dateArray);
            }

            $Log->trace("END setStartEndList");

            return $startEndList;
        }

        /**
         * 就業規則休憩時間マスタ情報(拘束時間基準)を取得する
         * @param    $laborRegulationsID    就業規則ID
         * @param    $appDateID             就業規則適用期間ID
         * @return   就業規則休憩時間マスタ情報(拘束時間基準)
         */
        private function getBreakTimeForBinding( $laborRegulationsID, $appDateID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getBreakTimeForBinding");
      
            // 就業規則IDから、就業規則適用期間IDを取得
            $sql  = " SELECT binding_hour, break_time  "
                  . " FROM m_work_rules_break "
                  . " WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id "
                  . " ORDER BY work_rules_break_id ";

            $parameters = array(
                                    ':labor_regulations_id'    => $laborRegulationsID, 
                                    ':application_date_id'     => $appDateID,
                               );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getBreakTimeForBinding");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret, $data);
            }

            $Log->trace("END getBreakTimeForBinding");
            return $ret;
        }

        /**
         * 就業規則休憩シフトマスタ情報(拘束時間基準時の付与に使用)を取得する
         * @param    $laborRegulationsID    就業規則ID
         * @param    $appDateID             就業規則適用期間ID
         * @return   就業規則休憩シフトマスタ情報(拘束時間基準時の付与に使用)
         */
        private function getBreakTimeForBindingShift( $laborRegulationsID, $appDateID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getBreakTimeForBindingShift");
            
            // 就業規則IDから、就業規則適用期間IDを取得
            $sql  = " SELECT elapsed_time, break_time  "
                  . " FROM m_work_rules_shift_break "
                  . " WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id "
                  . " ORDER BY work_rules_shift_break_id ";

            $parameters = array(
                                    ':labor_regulations_id'    => $laborRegulationsID, 
                                    ':application_date_id'     => $appDateID,
                               );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getBreakTimeForBindingShift");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret, $data);
            }

            $Log->trace("END getBreakTimeForBindingShift");
            return $ret;
        }


        /**
         * 就業規則休憩時間帯マスタ情報を取得する
         * @param    $laborRegulationsID    就業規則ID
         * @param    $appDateID             就業規則適用期間ID
         * @return   就業規則休憩時間帯マスタ情報
         */
        private function getBreakTimeZoneForBindingShift( $laborRegulationsID, $appDateID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getBreakTimeZoneForBindingShift");
            
            // 就業規則IDから、就業規則適用期間IDを取得
            $sql  = " SELECT hourly_wage_start_time, hourly_wage_end_time  "
                  . " FROM m_break_time_zone "
                  . " WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id "
                  . " ORDER BY hourly_wage_change_id ";

            $parameters = array(
                                    ':labor_regulations_id'    => $laborRegulationsID, 
                                    ':application_date_id'     => $appDateID,
                               );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getBreakTimeZoneForBindingShift");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret, $data);
            }

            $Log->trace("END getBreakTimeZoneForBindingShift");
            return $ret;
        }


        /**
         * 就業規則時給変更マスタ情報を取得する
         * @param    $laborRegulationsID    就業規則ID
         * @param    $appDateID             就業規則適用期間ID
         * @return   就業規則時給変更マスタ情報
         */
        private function getHourlyWageChange( $laborRegulationsID, $appDateID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getHourlyWageChange");
            
            // 就業規則IDから、就業規則適用期間IDを取得
            $sql  = " SELECT hourly_wage_start_time, hourly_wage_end_time, hourly_wage, hourly_wage_value  "
                  . " FROM m_hourly_wage_change "
                  . " WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id "
                  . " ORDER BY hourly_wage_change_id ";

            $parameters = array(
                                    ':labor_regulations_id'    => $laborRegulationsID, 
                                    ':application_date_id'     => $appDateID,
                               );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getHourlyWageChange");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret, $data);
            }

            $Log->trace("END getHourlyWageChange");
            return $ret;
        }

        /**
         * 就業規則手当マスタ情報を取得する
         * @param    $laborRegulationsID    就業規則ID
         * @param    $appDateID             就業規則適用期間ID
         * @return   就業規則手当マスタ情報
         */
        private function getWorkRulesAllowance ( $laborRegulationsID, $appDateID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getWorkRulesAllowance");
            
            // 就業規則IDから、就業規則適用期間IDを取得
            $sql  = " SELECT labor_cost_calculation, overtime_setting, legal_time_in_overtime, legal_time_in_overtime, legal_time_in_overtime_value, "
                  . " legal_time_out_overtime, legal_time_out_overtime_value, fixed_overtime, fixed_overtime_type, "
                  . " fixed_overtime_time, legal_time_out_overtime_45, legal_time_out_overtime_value_45, legal_time_out_overtime_60, "
                  . " legal_time_out_overtime_value_60, late_at_night_out_overtime, late_at_night_out_overtime_value, legal_holiday_allowance, "
                  . " legal_holiday_allowance_value, prescribed_holiday_allowance, prescribed_holiday_allowance_value"
                  . " FROM m_work_rules_allowance "
                  . " WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id ";

            $parameters = array(
                                    ':labor_regulations_id'    => $laborRegulationsID, 
                                    ':application_date_id'     => $appDateID,
                               );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getWorkRulesAllowance");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret, $data);
            }

            $Log->trace("END getWorkRulesAllowance");
            return $ret;
        }


        /**
         * 就業規則時間マスタ情報を取得する
         * @param    $laborRegulationsID    就業規則ID
         * @param    $appDateID             就業規則適用期間ID
         * @return   就業規則時間マスタ情報
         */
        private function getWorkRulesTime ( $laborRegulationsID, $appDateID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getWorkRulesTime");
            
            // 就業規則IDから、就業規則適用期間IDを取得
            $sql  = " SELECT is_shift_working_hours_use, is_work_rules_working_hours_use, start_working_hours, end_working_hours, "
                  . " attendance_rounding_time, attendance_rounding, break_rounding_start_time, break_rounding_start, "
                  . " break_rounding_end_time, break_rounding_end, leave_work_rounding_time, leave_work_rounding, "
                  . " total_working_day_rounding_time, total_working_day_rounding, total_working_month_rounding_time, total_working_month_rounding, "
                  . " early_shift_approval, early_shift_approval_time, overtime_approval, overtime_approval_time, max_break_time, "
                  . " is_shift_holiday_use, is_organization_calendar_holiday_use, mod_break_time, break_time_acquisition, "
                  . " automatic_break_time_acquisition, work_handling_travel, work_handling_travel_time, recorded_travel_time, "
                  . " late_at_night_start, late_at_night_end, balance_payments, month_tightening, year_tighten, trial_period_type_id, "
                  . " trial_period_criteria_value, shift_time_unit"
                  . " FROM m_work_rules_time "
                  . " WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id ";

            $parameters = array(
                                    ':labor_regulations_id'    => $laborRegulationsID, 
                                    ':application_date_id'     => $appDateID,
                               );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getWorkRulesTime");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret, $data);
            }

            $Log->trace("END getWorkRulesTime");
            return $ret;
        }


    }

?>
