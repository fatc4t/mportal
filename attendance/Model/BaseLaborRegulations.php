<?php
    /**
     * @file      就業規則ベースマスタ
     * @author    USE S.kasai
     * @date      2016/07/07
     * @version   1.00
     * @note      就業規則マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 就業規則ベースクラス
     * @note   就業規則マスタテーブルの管理を行う。
     */
    class BaseLaborRegulations extends BaseModel
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
         * 就業規則マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ
         * @return   成功時：$laborRegList  失敗：無
         */
        public function getListData( $postArray, $effFlag )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            // 一覧検索用のSQL文と検索条件が入った配列の生成
            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray, $effFlag );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $laborRegDataList = array();
            
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $laborRegDataList;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $laborRegDataList, $data);
            }

            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $laborRegList = $laborRegDataList;
            }
            else
            {
                // 組織の表示順に並びかえる
                $laborRegList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $laborRegDataList);
                if( $postArray['sort'] == 1 )
                {
                    $laborRegList = array_reverse($laborRegList);
                }
            }
            
            // 一覧画面で閲覧ができるユーザが所属する組織に編集と削除権限があるか判定
            $laborRegList = $this->updateLaborControl($laborRegList);

            $Log->trace("END getListData");

            // 一覧表を返す
            return $laborRegList;
        }

        /**
         * 労働基準法の情報を取得する
         * @param    $targetDate  対象日(指定なしは、当日)
         * @return   労働基準法の情報
         */
        public function getLaborStandardsAct( $targetDate = '' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START  getLaborStandardsAct");
            
            // 取得日を設定
            if( $targetDate == '' )
            {
                // 当日の日付を設定
                $targetDate = date("Y/m/d");
            }
            
            // 労働基準法の情報を取得
            $sql  = " SELECT legal_in_overtime, legal_in_overtime_set_value, legal_out_overtime, legal_out_overtime_set_value, legal_out_overtime_45, legal_out_overtime_set_value_45, "
                  . "        legal_out_overtime_60, legal_out_overtime_set_value_60, late_night_work, late_night_work_set_value, statutory_holiday_overtime,  "
                  . "        statutory_holiday_overtime_set_value, public_holiday_overtime, public_holiday_overtime_set_value, late_night_work_start_time1, late_night_work_end_time1, "
                  . "        late_night_work_start_time2, late_night_work_end_time2, holiday_dates_1week, holiday_dates_4week, break_time_6_hours_work_time, break_time_8_hours_work_time, "
                  . "        month_of_days_28, month_of_days_29, month_of_days_30, month_of_days_31, working_hours_day,  working_hours_week, working_hours_year "
                  . " FROM   public.m_labor_standards_act WHERE  application_date <= :application_date "
                  . " ORDER BY application_date DESC offset 0 limit 1 ";

            $parameters = array( 
                                    ':application_date'      => $targetDate, 
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END  getLaborStandardsAct");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data;
            }

            // 深夜残業
            $ret['late_night_work_start_time1'] = $this->setTimeShaping( $ret['late_night_work_start_time1'] );
            $ret['late_night_work_end_time1'] = $this->setTimeShaping( $ret['late_night_work_end_time1'] );
            $ret['late_night_work_start_time2'] = $this->setTimeShaping( $ret['late_night_work_start_time2'] );
            $ret['late_night_work_end_time2'] = $this->setTimeShaping( $ret['late_night_work_end_time2'] );

            $Log->trace("END  getLaborStandardsAct");
            return $ret;
        }

        /**
         * アラートマスタの情報を取得する
         * @return   アラートマスタ情報
         */
        public function getAlertM()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START  getAlertM");

            // アラートマスタの情報を取得
            $sql  = " SELECT is_labor_standards_act, is_labor_standards_act_warning, warning_value FROM m_alert ";

            $parameters = array();

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END  getAlertM");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data;
            }

            $Log->trace("END  getAlertM");
            return $ret;
        }

        /**
         * 就業規則マスタ新規データ登録
         * @param    $postArray(就業規則ID/組織ID/就業規則名/削除フラグ/登録ユーザID/登録組織ID)
         * @return   SQLの実行結果($lastLaborRegId/false)
         */
        protected function addLaborRegulationsNewData($postArray, &$laborRegId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addLaborRegulationsNewData");

            $parameters = array(
                ':organization_id'                  => $postArray['organization_id'],
                ':labor_regulations_name'           => $postArray['labor_regulations_name'],
                ':is_del'                           => $postArray['is_del'],
                ':disp_order'                       => $postArray['disp_order'],
                ':registration_user_id'             => $postArray['user_id'],
                ':registration_organization'        => $postArray['organization'],
                ':update_user_id'                   => $postArray['user_id'],
                ':update_organization'              => $postArray['organization'],
            );

            $sql = ' INSERT INTO m_labor_regulations(organization_id'
                . '                  , labor_regulations_name'
                . '                  , is_del'
                . '                  , disp_order'
                . '                  , registration_time'
                . '                  , registration_user_id'
                . '                  , registration_organization'
                . '                  , update_time'
                . '                  , update_user_id'
                . '                  , update_organization'
                . '                  ) VALUES ('
                . '                    :organization_id'
                . '                  , :labor_regulations_name'
                . '                  , :is_del'
                . '                  , :disp_order'
                . '                  , current_timestamp'
                . '                  , :registration_user_id'
                . '                  , :registration_organization'
                . '                  , current_timestamp'
                . '                  , :update_user_id'
                . '                  , :update_organization)';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3100");
                $errMsg = "組織ID：" . $postarray['organization_id']. "就業規則名：" . $postarray['labor_regulations_name'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3100";
            }

            // Lastidの更新
            $laborRegId = $DBA->lastInsertId( "m_labor_regulations" );

            $Log->trace("END addLaborRegulationsNewData");

            return "MSG_BASE_0000";
        }

        /**
         * 就業規則適用期間マスタ新規データ登録
         * @param    $postArray(適用期間ID/就業規則ID/適用開始日/就業規則アラート/就業規則閾値/残業時間アラート/残業時間閾値/コメント)
         * @param    $applicationDateId 
         * @return   SQLの実行結果($lastApplicationDtId/false)
         */
        protected function addApplicationDateNewData($postArray, &$applicationDateId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addApplicationDateNewData");
            
            $parameters = array(
                ':labor_regulations_id'                => $postArray['regLaborRegulationsId'],
                ':application_date_start'              => $postArray['application_date_start'],
                ':is_labor_regulations_alert'          => $postArray['is_labor_regulations_alert'],
                ':labor_regulations_alert_value'       => $postArray['labor_regulations_alert_value'],
                ':is_overtime_alert'                   => $postArray['is_overtime_alert'],
                ':overtime_alert_value'                => $postArray['overtime_alert_value'],
                ':e_mail'                              => $postArray['e_mail'],
                ':holiday_settings'                    => $postArray['holiday_settings'],
                ':app_comment'                         => $postArray['app_comment'],
                ':registration_user_id'                => $postArray['user_id'],
                ':registration_organization'           => $postArray['organization'],
                ':update_user_id'                      => $postArray['user_id'],
                ':update_organization'                 => $postArray['organization'],
            );

            $sql = 'INSERT INTO m_application_date( labor_regulations_id'
                . '                  , application_date_start'
                . '                  , is_labor_regulations_alert'
                . '                  , labor_regulations_alert_value'
                . '                  , is_overtime_alert'
                . '                  , overtime_alert_value'
                . '                  , comment'
                . '                  , e_mail'
                . '                  , holiday_settings'
                . '                  , registration_time'
                . '                  , registration_user_id'
                . '                  , registration_organization'
                . '                  , update_time'
                . '                  , update_user_id'
                . '                  , update_organization'
                . '                  ) VALUES ('
                . '                    :labor_regulations_id'
                . '                  , :application_date_start'
                . '                  , :is_labor_regulations_alert'
                . '                  , :labor_regulations_alert_value'
                . '                  , :is_overtime_alert'
                . '                  , :overtime_alert_value'
                . '                  , :app_comment'
                . '                  , :e_mail'
                . '                  , :holiday_settings'
                . '                  , current_timestamp'
                . '                  , :registration_user_id'
                . '                  , :registration_organization'
                . '                  , current_timestamp'
                . '                  , :update_user_id'
                . '                  , :update_organization)';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3101");
                $errMsg = "就業規則ID：" . $postarray['labor_regulations_id'] . "適用期間：" . $postarray['application_date_start'] . "の登録に失敗しました。";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3101";
            }

            // Lastidの更新
            $applicationDateId = $DBA->lastInsertId( "m_application_date" );

            $Log->trace("END addApplicationDateNewData");

            return "MSG_BASE_0000";
        }
        
        /**
         * 就業規則時間マスタの新規データ登録
         * @param    $applicationDateId(適用期間ID)
         * @param    $laborRegId(就業規則ID)
         * @return   SQLの実行結果(true/false)
         */
        protected function addWorkRulesTimeNewData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addWorkRulesTimeNewData");
            
            $sqlTimeParamenter = "";
            
            $parameters = array(
                ':application_date_id'           => $postArray['regApplicationDateId'],
                ':labor_regulations_id'          => $postArray['regLaborRegulationsId'],
            );
            
            // 就業規則時間マスタのカラムリスト
            $timeColumnList = $this->getWorkRulesTimeColumnList();
            
            $postArray['start_working_hours'] = $this->changeTimestamp($postArray['start_working_hours'], true);
            $postArray['end_working_hours'] = $this->changeTimestamp($postArray['end_working_hours'], false);
            $postArray['late_at_night_start'] = $this->changeTimestamp($postArray['late_at_night_start'], false);
            $postArray['late_at_night_end'] = $this->changeTimestamp($postArray['late_at_night_end'], false);
            $postArray['work_handling_travel_time'] = $this->changeTimeFromMinute($postArray['work_handling_travel_time']);
            
            
            $sqlTimeColumn = $this->generationSQL->creatInsertSQL($postArray, $sqlTimeParamenter, $parameters, $timeColumnList);

            $sql = 'INSERT INTO m_work_rules_time(application_date_id'
                 . '                                    , labor_regulations_id';
            $sql .= $sqlTimeColumn;
            $sql .= '                                    ) VALUES ('
                 . '                                      :application_date_id'
                 . '                                    , :labor_regulations_id';
            $sql .= $sqlTimeParamenter;
            $sql .= ')';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3102");
                $errMsg = "適用期間ID：" . $postarray['application_date_id']. "就業規則ID：" . $postarray['labor_regulations_id'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3102";
            }

            $Log->trace("END addWorkRulesTimeNewData");

            return "MSG_BASE_0000";
        }
        
        /**
         * 就業規則休憩時間マスタ新規データ登録
         * @param    $applicationDateId(適用期間ID)
         * @param    $laborRegId(就業規則ID)
         * @return   SQLの実行結果(true/false)
         */
        protected function addWorkRulesBreakNewData($postArray, $allTimeArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addWorkRulesBreakNewData");
            
            $breakLoopCount = count($allTimeArray) / 2;
            for ( $i = 0; $i < $breakLoopCount; $i++ )
            {
                $sql = 'INSERT INTO m_work_rules_break( application_date_id'
                    . '                             , labor_regulations_id'
                    . '                             , binding_hour'
                    . '                             , break_time'
                    . '                              ) VALUES ('
                    . '                               :application_date_id'
                    . '                             , :labor_regulations_id'
                    . '                             , :binding_hour'
                    . '                             , :break_time )';

                $parameters = array(
                    ':application_date_id'           => $postArray['regApplicationDateId'],
                    ':labor_regulations_id'          => $postArray['regLaborRegulationsId'],
                    ':binding_hour'                  => $allTimeArray['binding_hour'.$i],
                    ':break_time'                    => $allTimeArray['break_time'.$i],
                );

                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters) )
                {
                    // SQL実行エラー
                    $Log->warn("MSG_ERR_3103");
                    $errMsg = "適用期間ID：" . $postarray['application_date_id']. "就業規則ID：" . $postarray['labor_regulations_id'] . "の承認組織登録失敗";
                    $Log->warnDetail($errMsg);
                    return "MSG_ERR_3103";
                }
            }
            
            $Log->trace("END addWorkRulesBreakNewData");

            return "MSG_BASE_0000";
        }

        /**
         * 就業規則休憩時間帯マスタ新規データ登録
         * @param    $applicationDateId(適用期間ID)
         * @param    $laborRegId(就業規則ID)
         * @return   SQLの実行結果(true/false)
         */
        protected function addBreakTimeZoneNewData($postArray, $allBreakZoneArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addBreakTimeZoneNewData");
            
            $zoneLoopCount = count($allBreakZoneArray) / 2;
            for ( $i = 0; $i < $zoneLoopCount; $i++ )
            {
                $sql = 'INSERT INTO m_break_time_zone( application_date_id'
                    . '                            , labor_regulations_id'
                    . '                            , hourly_wage_start_time'
                    . '                            , hourly_wage_end_time'
                    . '                            ) VALUES ('
                    . '                              :application_date_id'
                    . '                            , :labor_regulations_id'
                    . '                            , :hourly_wage_start_time'
                    . '                            , :hourly_wage_end_time)';
            
                $parameters = array(
                    ':application_date_id'           => $postArray['regApplicationDateId'],
                    ':labor_regulations_id'          => $postArray['regLaborRegulationsId'],
                    ':hourly_wage_start_time'        => $this->changeTimestamp($allBreakZoneArray['hourly_wage_start_time'.$i], false),
                    ':hourly_wage_end_time'          => $this->changeTimestamp($allBreakZoneArray['hourly_wage_end_time'.$i], false),
                );

                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters) )
                {
                    // SQL実行エラー
                    $Log->warn("MSG_ERR_3104");
                    $errMsg = "適用期間ID：" . applicationDateId . "就業規則ID：" . laborRegId . "の登録失敗";
                    $Log->warnDetail($errMsg);
                    return "MSG_ERR_3104";
                }
            }
            
            $Log->trace("END addBreakTimeZoneNewData");
            
            return "MSG_BASE_0000";
        }
        
        /**
         * 就業規則休憩シフトマスタ新規データ登録
         * @param    $applicationDateId(適用期間ID)
         * @param    $laborRegId(就業規則ID)
         * @return   SQLの実行結果(true/false)
         */
        protected function addWorkRulesShiftBreakNewData($postArray, $allBreakArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addWorkRulesShiftBreakNewData");
            
            $shiftLoopCount = count($allBreakArray) / 2;
            for ( $i = 0; $i < $shiftLoopCount; $i++ )
            {
                $sql = 'INSERT INTO m_work_rules_shift_break( application_date_id'
                    . '                            , labor_regulations_id'
                    . '                            , elapsed_time'
                    . '                            , break_time'
                    . '                            ) VALUES ('
                    . '                              :application_date_id'
                    . '                            , :labor_regulations_id'
                    . '                            , :elapsed_time'
                    . '                            , :break_time)';
                
                $parameters = array(
                    ':application_date_id'           => $postArray['regApplicationDateId'],
                    ':labor_regulations_id'          => $postArray['regLaborRegulationsId'],
                    ':elapsed_time'                  => $allBreakArray['elapsed_time'.$i],
                    ':break_time'                    => $allBreakArray['break_time'.$i],
                );

                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters) )
                {
                    // SQL実行エラー
                    $Log->warn("MSG_ERR_3105");
                    $errMsg = "適用期間ID：" . $postarray['application_date_id']. "就業規則ID：" . $postarray['labor_regulations_id'] . "の登録失敗";
                    $Log->warnDetail($errMsg);
                    return "MSG_ERR_3105";
                }
            }

            $Log->trace("END addWorkRulesShiftBreakNewData");

            return "MSG_BASE_0000";
        }
        
        /**
         * 就業規則手当マスタ新規データ登録
         * @param    $applicationDateId(適用期間ID)
         * @param    $laborRegId(就業規則ID)
         * @return   SQLの実行結果(true/false)
         */
        protected function addWorkRulesAllowanceNewData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addWorkRulesAllowanceNewData");
            
            $sqlAllParamenter = "";
            
            $parameters = array(
                ':application_date_id'           => $postArray['regApplicationDateId'],
                ':labor_regulations_id'          => $postArray['regLaborRegulationsId'],
            );
            
            // 就業規則手当マスタのカラムリスト
            $allowanceColumnList = $this->getWorkRulesAllowanceColumnList();
            
            $sqlAllColumn = $this->generationSQL->creatInsertSQL($postArray, $sqlAllParamenter, $parameters, $allowanceColumnList);

            $sql = 'INSERT INTO m_work_rules_allowance( application_date_id'
                . '                            , labor_regulations_id';
            $sql.= $sqlAllColumn;
            $sql.= '                            ) VALUES ('
                . '                              :application_date_id'
                . '                            , :labor_regulations_id';
            $sql.= $sqlAllParamenter;
            $sql.= ')';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3106");
                $errMsg = "適用期間ID：" . $postarray['application_date_id']. "就業規則ID：" . $postarray['labor_regulations_id'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3106";
            }

            $Log->trace("END addWorkRulesAllowanceNewData");

            return "MSG_BASE_0000";
        }
        
        /**
         * 就業規則時給変更マスタ新規データ登録
         * @param    $applicationDateId(適用期間ID)
         * @param    $laborRegId(就業規則ID)
         * @return   SQLの実行結果(true/false)
         */
        protected function addHourlyWageChangeNewData($postArray, $allHourlyChange)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addHourlyWageChangeNewData");
            
            $changeLoopCount = count($allHourlyChange) / 4;
            for ( $i = 0; $i < $changeLoopCount; $i++ )
            {
                $sql = 'INSERT INTO m_hourly_wage_change( application_date_id'
                    . '                            , labor_regulations_id'
                    . '                            , hourly_wage_start_time'
                    . '                            , hourly_wage_end_time'
                    . '                            , hourly_wage'
                    . '                            , hourly_wage_value'
                    . '                            ) VALUES ('
                    . '                              :application_date_id'
                    . '                            , :labor_regulations_id'
                    . '                            , :hourly_wage_start_time'
                    . '                            , :hourly_wage_end_time'
                    . '                            , :hourly_wage'
                    . '                            , :hourly_wage_value)';
            
                $parameters = array(
                    ':application_date_id'           => $postArray['regApplicationDateId'],
                    ':labor_regulations_id'          => $postArray['regLaborRegulationsId'],
                    ':hourly_wage_start_time'        => $this->changeTimestamp($allHourlyChange['hourly_wage_start_time'.$i], false),
                    ':hourly_wage_end_time'          => $this->changeTimestamp($allHourlyChange['hourly_wage_end_time'.$i], false),
                    ':hourly_wage'                   => $allHourlyChange['hourly_wage'.$i],
                    ':hourly_wage_value'             => $allHourlyChange['hourly_wage_value'.$i],
                );

                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters) )
                {
                    // SQL実行エラー
                    $Log->warn("MSG_ERR_3107");
                    $errMsg = "適用期間ID：" . $postarray['application_date_id'] . "就業規則ID：" . $postarray['labor_regulations_id'] . "の登録失敗";
                    $Log->warnDetail($errMsg);
                    return "MSG_ERR_3107";
                }
            }

            $Log->trace("END addHourlyWageChangeNewData");

            return "MSG_BASE_0000";
        }
        
        /**
         * 残業時間マスタ新規データ登録
         * @param    $applicationDateId(適用期間ID)
         * @param    $laborRegId(就業規則ID)
         * @return   SQLの実行結果(true/false)
         */
        protected function addOvertimeNewData($postArray, $regWorkingHoursTime, $overtimeRefTime, $overtimeDetailID, $closingDateSetID)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addOvertimeNewData");

            $sql = 'INSERT INTO m_overtime( application_date_id'
                . '                            , labor_regulations_id'
                . '                            , overtime_detail_id'
                . '                            , regular_working_hours_time'
                . '                            , overtime_reference_time'
                . '                            , closing_date_set_id '
                . '                            ) VALUES ('
                . '                              :application_date_id'
                . '                            , :labor_regulations_id'
                . '                            , :overtime_detail_id'
                . '                            , :regular_working_hours_time'
                . '                            , :overtime_reference_time'
                . '                            , :closing_date_set_id )';

            $parameters = array(
                ':application_date_id'         => $postArray['regApplicationDateId'],
                ':labor_regulations_id'        => $postArray['regLaborRegulationsId'],
                ':overtime_detail_id'          => $overtimeDetailID,
                ':regular_working_hours_time'  => $regWorkingHoursTime,
                ':overtime_reference_time'     => $overtimeRefTime,
                ':closing_date_set_id'         => $closingDateSetID
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3108");
                $errMsg = "適用期間ID：" . $postarray['application_date_id']. "就業規則ID：" . $postarray['labor_regulations_id'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3108";
            }

            $Log->trace("END addOvertimeNewData");

            return "MSG_BASE_0000";
        }
        
        /**
         * 就業規則時間マスタのカラムリスト
         * @return   カラムのリスト
         */
        protected function getWorkRulesTimeColumnList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getWorkRulesTimeColumnList");

            $timeColumnList = array();
            $timeColumnList = array(
                'is_shift_working_hours_use'           => '',
                'is_work_rules_working_hours_use'      => '',
                'start_working_hours'                  => 'date',
                'end_working_hours'                    => 'date',
                'attendance_rounding_time'             => '',
                'attendance_rounding'                  => '',
                'break_rounding_start_time'            => '',
                'break_rounding_start'                 => '',
                'break_rounding_end_time'              => '',
                'break_rounding_end'                   => '',
                'leave_work_rounding_time'             => '',
                'leave_work_rounding'                  => '',
                'total_working_day_rounding_time'      => '',
                'total_working_day_rounding'           => '',
                'total_working_month_rounding_time'    => '',
                'total_working_month_rounding'         => '',
                'early_shift_approval'                 => '',
                'early_shift_approval_time'            => '',
                'overtime_approval'                    => '',
                'overtime_approval_time'               => '',
                'max_break_time'                       => '',
                'is_shift_holiday_use'                 => '',
                'is_organization_calendar_holiday_use' => '',
                'mod_break_time'                       => '',
                'break_time_acquisition'               => '',
                'automatic_break_time_acquisition'     => '',
                'work_handling_travel'                 => '',
                'work_handling_travel_time'            => 'date',
                'recorded_travel_time'                 => '',
                'late_at_night_start'                  => 'date',
                'late_at_night_end'                    => 'date',
                'balance_payments'                     => '',
                'month_tightening'                     => '',
                'year_tighten'                         => 'date',
                'trial_period_type_id'                 => '',
                'trial_period_criteria_value'          => '',
                'shift_time_unit'                      => '',
            );

            $Log->trace("END getWorkRulesTimeColumnList");

            return $timeColumnList;
        }
        
        /**
         * 就業規則手当マスタのカラムリスト
         * @return   カラムのリスト
         */
        protected function getWorkRulesAllowanceColumnList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getWorkRulesAllowanceColumnList");

            $allowanceColumnList = array();
            $allowanceColumnList = array(
                'labor_cost_calculation'              => '',
                'overtime_setting'                    => '',
                'legal_time_in_overtime'              => '',
                'legal_time_in_overtime_value'        => '',
                'legal_time_out_overtime'             => '',
                'legal_time_out_overtime_value'       => '',
                'fixed_overtime'                      => '',
                'fixed_overtime_type'                 => '',
                'fixed_overtime_time'                 => '',
                'legal_time_out_overtime_45'          => '',
                'legal_time_out_overtime_value_45'    => '',
                'legal_time_out_overtime_60'          => '',
                'legal_time_out_overtime_value_60'    => '',
                'late_at_night_out_overtime'          => '',
                'late_at_night_out_overtime_value'    => '',
                'legal_holiday_allowance'             => '',
                'legal_holiday_allowance_value'       => '',
                'prescribed_holiday_allowance'        => '',
                'prescribed_holiday_allowance_value'  => '',
            );

            $Log->trace("END getWorkRulesAllowanceColumnList");

            return $allowanceColumnList;
        }

        /**
         * 就業規則マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   就業規則マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray, $effFlag )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");
            
            // 参照権限による検索条件追加
            $searchedColumn = ' WHERE ';

            $sql = ' SELECT labor_regulations_id, eff_code, organization_id, abbreviated_name, labor_regulations_name, application_date_id, application_date_start, '
                . '        overtime_setting_name, legal_time_in_overtime, legal_time_out_overtime, fixed_overtime, legal_holiday_allowance, '
                . '        prescribed_holiday_allowance, late_at_night_start, late_at_night_end, late_at_night_out_overtime, break_time_acquisition, '
                . '        comment, disp_order '
                . ' FROM v_labor_regulations ';
            $sql .= $this->creatSqlWhereInConditions($searchedColumn);
            $sql .= $this->creatAndSQL($postArray, $searchArray);
            $sql .= $this->generationSQL->creatAndEffCheckSQL($effFlag);
            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");

            return $sql;
        }
        
        /**
         * 一覧検索条件追加SQL作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ（参照渡しで返す）
         * @return   就業規則マスタ一覧取得SQL文
         */
        private function creatAndSQL($postArray, &$searchArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAndSQL");

            $searchArray = array();
            $sqlAnd = '';
            
            $whereSqlList = array(
                                'organizationID'             =>  ' AND organization_id = :organizationID ',                            // 組織
                                'laborRegulationsID'         =>  ' AND labor_regulations_id = :laborRegulationsID ',                   // 就業規則名
                                'overtimeSetting'            =>  ' AND overtime_setting_name = :overtimeSetting ',                     // 残業設定
                                'legalTimeInOvertime'        =>  ' AND legal_time_in_overtime = :legalTimeInOvertime ',                // 法定内残業代
                                'legalTimeOutOvertime'       =>  ' AND legal_time_out_overtime = :legalTimeOutOvertime ',              // 法定外残業代
                                'fixedOvertime'              =>  ' AND fixed_overtime = :fixedOvertime ',                              // みなし残業
                                'legalHolidayAllowance'      =>  ' AND legal_holiday_allowance = :legalHolidayAllowance ',             // 法定休日残業代
                                'prescribedHolidayAllowance' =>  ' AND prescribed_holiday_allowance = :prescribedHolidayAllowance ',   // 公休日残業代
                                'lateAtNightOutOvertime'     =>  ' AND late_at_night_out_overtime = :lateAtNightOutOvertime ',         // 深夜残業代
                                'breakTimeAcquisition'       =>  ' AND break_time_acquisition = :breakTimeAcquisition ',               // 休憩時間の判定
                            );

            foreach($whereSqlList as $key => $val)
            {
                if( !empty( $postArray[$key] ) )
                {
                    $sqlAnd .= $val;
                    $param = ':' . $key;
                    $searchParamArray = array( $param => $postArray[$key],);
                    $searchArray = array_merge($searchArray, $searchParamArray);
                }
            }
            
            if( !empty( $postArray['lateAtNightStart'] ) )
            {
                $sqlAnd .= " AND late_at_night_start >= cast( '2016-04-0" . $postArray['lateAtNightStart'] . ":00' as timestamp) ";
                $lateNightStartArray = array();
                $searchArray = array_merge($searchArray, $lateNightStartArray);
            }
            if( !empty( $postArray['lateAtNightEnd'] ) )
            {
                $sqlAnd .= " AND late_at_night_end <= cast( '2016-04-0" . $postArray['lateAtNightEnd'] . ":00' as timestamp) ";
                $lateNightEndArray = array();
                $searchArray = array_merge($searchArray, $lateNightEndArray);
            }
            if( !empty( $postArray['comment'] ) )
            {
                $sqlAnd .= ' AND comment LIKE :comment ';
                $comment = "%" . $postArray['comment'] . "%";
                $commentArray = array(':comment' => $comment,);
                $searchArray = array_merge($searchArray, $commentArray);
            }

            $Log->trace("END creatAndSQL");

            return $sqlAnd;
        }
        
        /**
         * 就業規則マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   就業規則マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            // ソート条件作成
            $sortSqlList = array(
                                3       =>  ' ORDER BY eff_code DESC, organization_id, disp_order',                      // 状態の降順
                                4       =>  ' ORDER BY eff_code, organization_id, disp_order',                           // 状態の昇順
                                5       =>  ' ORDER BY abbreviated_name DESC, organization_id, disp_order',              // 組織名の降順
                                6       =>  ' ORDER BY abbreviated_name, organization_id, disp_order',                   // 組織名の昇順
                                7       =>  ' ORDER BY labor_regulations_name DESC, organization_id, disp_order',        // 就業規則名の降順
                                8       =>  ' ORDER BY labor_regulations_name, organization_id, disp_order',             // 就業規則名の昇順
                                9       =>  ' ORDER BY application_date_start DESC, organization_id, disp_order',        // 適用開始日の降順
                                10      =>  ' ORDER BY application_date_start, organization_id, disp_order',             // 適用開始日の昇順
                                11      =>  ' ORDER BY overtime_setting_name DESC, organization_id, disp_order',         // 残業設定の降順
                                12      =>  ' ORDER BY overtime_setting_name, organization_id, disp_order',              // 残業設定の昇順
                                13      =>  ' ORDER BY legal_time_in_overtime DESC, organization_id, disp_order',        // 法定内残業代の降順
                                14      =>  ' ORDER BY legal_time_in_overtime, organization_id, disp_order',             // 法定内残業代の昇順
                                15      =>  ' ORDER BY legal_time_out_overtime DESC, organization_id, disp_order',       // 法定外残業代の降順
                                16      =>  ' ORDER BY legal_time_out_overtime, organization_id, disp_order',            // 法定外残業代の昇順
                                17      =>  ' ORDER BY fixed_overtime DESC, organization_id, disp_order',                // みなし残業の降順
                                18      =>  ' ORDER BY fixed_overtime, organization_id, disp_order',                     // みなし残業の昇順
                                19      =>  ' ORDER BY legal_holiday_allowance DESC, organization_id, disp_order',       // 法定休日残業代の降順
                                20      =>  ' ORDER BY legal_holiday_allowance, organization_id, disp_order',            // 法定休日残業代の昇順
                                21      =>  ' ORDER BY prescribed_holiday_allowance DESC, organization_id, disp_order',  // 公休日残業代の降順
                                22      =>  ' ORDER BY prescribed_holiday_allowance, organization_id, disp_order',       // 公休日残業代の昇順
                                23      =>  ' ORDER BY late_at_night_start DESC, organization_id, disp_order',           // 深夜労働時間帯の降順
                                24      =>  ' ORDER BY late_at_night_start, organization_id, disp_order',                // 深夜労働時間帯の昇順
                                25      =>  ' ORDER BY late_at_night_out_overtime DESC, organization_id, disp_order',    // 深夜残業代の降順
                                26      =>  ' ORDER BY late_at_night_out_overtime, organization_id, disp_order',         // 深夜残業代の昇順
                                27      =>  ' ORDER BY break_time_acquisition DESC, organization_id, disp_order',        // 休憩時間の判定の降順
                                28      =>  ' ORDER BY break_time_acquisition, organization_id, disp_order',             // 休憩時間の判定の昇順
                                29      =>  ' ORDER BY comment DESC, organization_id, disp_order',                       // コメントの降順
                                30      =>  ' ORDER BY comment, organization_id, disp_order',                            // コメントの昇順
                                31      =>  ' ORDER BY disp_order DESC, organization_id',                                // 表示順の降順
                                32      =>  ' ORDER BY disp_order, organization_id',                                     // 表示順の昇順
                            );
            $sql = ' ORDER BY organization_id, disp_order ';
            // ソート条件
            if( array_key_exists( $sortNo, $sortSqlList ) )
            {
                $sql = $sortSqlList[$sortNo];
            }
            
            $Log->trace("END creatSortSQL");
            
            return $sql;
        }
        
        /**
         * 一覧表編集削除権限の判定
         * @note     画面遷移する機能で対象のデータの設定組織に対して、閲覧ユーザに編集/削除権限がない場合入力画面への遷移を制御するためのフラグを設ける
         * @param    $dataList
         * @return   $list(disabledの有無を追加したリスト)
         */
        private function updateLaborControl($dataList)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START updateControl");

            foreach($_SESSION["REGISTRATION"] as $reg)
            {
                $regArray[] = $reg['organization_id'];
            }
            foreach($_SESSION["DELETE"] as $del)
            {
                $delArray[] = $del['organization_id'];
            }

            foreach($dataList as $data)
            {
                $updateDisabled = "";
                if(!in_array($data['organization_id'], $regArray) && !in_array($data['organization_id'], $delArray))
                {
                    $updateDisabled = "disabled";
                }

                if( ( $data['eff_code'] === $Log->getMsgLog('MSG_FW_STATE_NOT_APPLICABLE') ) || ( $data['eff_code'] === $Log->getMsgLog('MSG_FW_STATE_DELETE') ) )
                {
                    $updateDisabled = "disabled";
                }

                $disabledCheck = array('updateDisabled' => $updateDisabled,);
                $list[] = array_merge($data, $disabledCheck);
            }

            $Log->trace("END updateControl");

            return $list;
        }

        /**
         * 深夜残業代の時間整形
         * @return   時間の整形結果
         */
        private function setTimeShaping( $inTime )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START  setTimeShaping");

            $ret = substr( $inTime, 11, 5 );

            // 深夜残業代の時間整形
            if( substr( $inTime, 9, 1 ) == "2" )
            {
                $time = substr( $inTime, 11, 5 );
                $timeList = explode( ":", $time );
                $ret = ($timeList[0] + 24) . ":" . $timeList[1];
            }

            $Log->trace("END  setTimeShaping");
            return $ret;
        }

    }

?>