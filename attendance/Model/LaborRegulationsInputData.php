<?php
    /**
     * @file      就業規則入力画面マスタ
     * @author    USE S.Kasai
     * @date      2016/07/07
     * @version   1.00
     * @note      就業規則マスタ入力画面に表示するデータの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/BaseLaborRegulations.php';

    /**
     * 就業規則入力画面クラス
     * @note   就業規則マスタテーブルの管理を行う。
     */
    class LaborRegulationsInputData extends BaseLaborRegulations
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
         * 就業規則マスタデータ修正画面登録データ検索
         * @param    $labor_reg_id
         * @return   SQLの実行結果
         */
        public function getLaborRegulationsData( $labor_reg_id, $app_date_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getLaborRegulationsData");

            $sql = ' SELECT lr.labor_regulations_id, lr.organization_id, lr.labor_regulations_name, lr.is_del, ad.application_date_id'
                . " , to_char(ad.application_date_start, 'yyyy/mm/dd') as application_date_start "
                . ' , ad.is_labor_regulations_alert , ad.labor_regulations_alert_value'
                . ' , ad.is_overtime_alert, ad.overtime_alert_value , ad.comment'
                . ' , ad.holiday_settings, ad.e_mail '
                . ' , wrt.is_shift_working_hours_use, wrt.is_work_rules_working_hours_use '
                . " , to_char(wrt.start_working_hours, 'yyyy/mm/dd hh24:mi:ss') as start_working_hours "
                . " , to_char(wrt.end_working_hours, 'yyyy/mm/dd hh24:mi:ss') as end_working_hours "
                . ' , wrt.attendance_rounding_time, wrt.attendance_rounding, wrt.break_rounding_start_time, wrt.break_rounding_start '
                . ' , wrt.break_rounding_end_time, wrt.break_rounding_end, wrt.leave_work_rounding_time, wrt.leave_work_rounding '
                . ' , wrt.total_working_day_rounding_time, wrt.total_working_day_rounding, wrt.total_working_month_rounding_time, wrt.total_working_month_rounding '
                . ' , wrt.early_shift_approval, wrt.early_shift_approval_time, wrt.overtime_approval, wrt.overtime_approval_time '
                . ' , wrt.max_break_time, wrt.is_shift_holiday_use, wrt.is_organization_calendar_holiday_use, wrt.mod_break_time '
                . ' , wrt.break_time_acquisition, wrt.automatic_break_time_acquisition, wrt.work_handling_travel '
                . " , to_char(wrt.work_handling_travel_time, 'hh24:mi') as work_handling_travel_time "
                . ' , wrt.recorded_travel_time '
                . " , to_char(wrt.late_at_night_start, 'yyyy/mm/dd hh24:mi:ss') as late_at_night_start "
                . " , to_char(wrt.late_at_night_end, 'yyyy/mm/dd hh24:mi:ss') as late_at_night_end "
                . ' , wrt.balance_payments, wrt.month_tightening '
                . " , to_char(wrt.year_tighten, 'yyyy/mm/dd') as year_tighten "
                . ' , wrt.trial_period_type_id, wrt.trial_period_criteria_value, wrt.shift_time_unit '
                . ' , wra.labor_cost_calculation, wra.overtime_setting, wra.legal_time_in_overtime '
                . ' , wra.legal_time_in_overtime_value, wra.legal_time_out_overtime, wra.legal_time_out_overtime_value '
                . ' , wra.fixed_overtime, wra.fixed_overtime_type, wra.fixed_overtime_time '
                . ' , wra.legal_time_out_overtime_45, wra.legal_time_out_overtime_value_45, wra.legal_time_out_overtime_60 '
                . ' , wra.legal_time_out_overtime_value_60, wra.late_at_night_out_overtime, wra.late_at_night_out_overtime_value '
                . ' , wra.legal_holiday_allowance, wra.legal_holiday_allowance_value, wra.prescribed_holiday_allowance, wra.prescribed_holiday_allowance_value'
                . ' , lr.update_time, lr.disp_order '
                . ' FROM m_labor_regulations lr '
                . ' INNER JOIN m_application_date ad ON lr.labor_regulations_id = ad.labor_regulations_id '
                . ' INNER JOIN m_work_rules_time wrt ON lr.labor_regulations_id = wrt.labor_regulations_id AND ad.application_date_id = wrt.application_date_id'
                . ' INNER JOIN m_work_rules_allowance wra ON lr.labor_regulations_id = wra.labor_regulations_id AND ad.application_date_id = wra.application_date_id'
                . ' WHERE lr.labor_regulations_id = :labor_regulations_id AND ad.application_date_id = :application_date_id';
            
            $searchArray = array(
                                    ':labor_regulations_id' => $labor_reg_id ,
                                    ':application_date_id'  => $app_date_id ,
                                );
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $laborRegDataList = array();
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getLaborRegulationsData");
                return $laborRegDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $laborRegDataList = $data;
            }
            
            $laborRegDataList['start_working_hours'] = $this->changeTime($laborRegDataList['start_working_hours'], true);
            $laborRegDataList['end_working_hours'] = $this->changeTime($laborRegDataList['end_working_hours'], false);
            $laborRegDataList['late_at_night_start'] = $this->changeTime($laborRegDataList['late_at_night_start'], true);
            $laborRegDataList['late_at_night_end'] = $this->changeTime($laborRegDataList['late_at_night_end'], false);
            $laborRegDataList['work_handling_travel_time'] = $this->changeMinuteFromTime($laborRegDataList['work_handling_travel_time']);
            

            $Log->trace("END getLaborRegulationsData");

            return $laborRegDataList;
        }
        
        /**
         * 就業規則休憩時間帯マスタ一覧取得SQL文作成
         * @param    $labRegId 
         * @param    $appDateId
         * @return   $breakTimeZoneList
         */
        public function getBreakTimeZone ( $labRegId, $appDateId )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getBreakTimeZone");
            

            $sql = ' SELECT application_date_id, labor_regulations_id '
                 . " , CASE WHEN CAST( to_char( hourly_wage_start_time, 'dd') AS INTEGER ) = 1 THEN to_char( hourly_wage_start_time, 'HH24:MI') "
                 . "        ELSE CAST( CAST( to_char( hourly_wage_start_time, 'HH24') AS INTEGER ) + 24 AS text ) || to_char( hourly_wage_start_time, ':MI')  "
                 . '   END hourly_wage_start_time '
                 . " , CASE WHEN CAST( to_char( hourly_wage_end_time, 'dd') AS INTEGER ) = 1 THEN to_char( hourly_wage_end_time, 'HH24:MI') "
                 . "        ELSE CAST( CAST( to_char( hourly_wage_end_time, 'HH24') AS INTEGER ) + 24 AS text ) || to_char( hourly_wage_end_time, ':MI') "
                 . '   END hourly_wage_end_time '
                 . ' FROM m_break_time_zone '
                 . ' WHERE application_date_id = :application_date_id '
                 . ' AND labor_regulations_id = :labor_regulations_id  ';

            $parametersArray = array(
                                        ':labor_regulations_id' => $labRegId,
                                        ':application_date_id'  => $appDateId,
                                    );
            $result = $DBA->executeSQL($sql, $parametersArray);
            $breakZoneList = array();
            if( $result === false )
            {
                $Log->trace("END getBreakTimeList");
                return $breakZoneList;
            }

            $wageStartTime = "";
            $wageEndTime = "";
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
            
                $wageStartTime = $data['hourly_wage_start_time'];
                array_push( $breakZoneList, $wageStartTime );     // 拘束時間
                
                $wageEndTime = $data['hourly_wage_end_time'];
                array_push( $breakZoneList, $wageEndTime );    // 付与時間（休憩時間)
            }

            $Log->trace("END getBreakTimeList");
            return $breakZoneList;
        }
        
        
        /**
         * 就業規則時給変更マスタ一覧取得SQL文作成
         * @param    $labRegId 
         * @param    $appDateId
         * @return   $hourlyChangeList
         */
        public function getHourlyWageChange ( $labRegId, $appDateId )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getHourlyWageChange");
            

            $sql = ' SELECT application_date_id , labor_regulations_id '
                 . " , CASE WHEN CAST( to_char( hourly_wage_start_time, 'dd') AS INTEGER ) = 1 THEN to_char( hourly_wage_start_time, 'HH24:MI') "
                 . "        ELSE CAST( CAST( to_char( hourly_wage_start_time, 'HH24') AS INTEGER ) + 24 AS text ) || to_char( hourly_wage_start_time, ':MI') "
                 . '   END hourly_wage_start_time '
                 . " , CASE WHEN CAST( to_char( hourly_wage_end_time, 'dd') AS INTEGER ) = 1 THEN to_char( hourly_wage_end_time, 'HH24:MI') "
                 . "        ELSE CAST( CAST( to_char( hourly_wage_end_time, 'HH24') AS INTEGER ) + 24 AS text ) || to_char( hourly_wage_end_time, ':MI') "
                 . '   END hourly_wage_end_time '
                 . ' , hourly_wage, hourly_wage_value  '
                 . ' FROM m_hourly_wage_change '
                 . ' WHERE application_date_id = :application_date_id '
                 . ' AND labor_regulations_id = :labor_regulations_id  ';
            
            $parametersArray = array(
                                        ':labor_regulations_id' => $labRegId,
                                        ':application_date_id'  => $appDateId,
                                    );
            
            // SQLの実行
            $result = $DBA->executeSQL($sql, $parametersArray);

            // 一覧表を格納する空の配列宣言
            $hourlyChangeList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END selectHourlyWageChange");
                return $hourlyChangeList;
            }
            
            $hourlyWageStartTime = "";
            $hourlyWageEndTime = "";
            $hourlyWageList = "";
            $hourlyWageValueList = "";
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
            
                $hourlyWageStartTime = $data['hourly_wage_start_time'];
                array_push( $hourlyChangeList, $hourlyWageStartTime );     // 休憩開始時間
                
                $hourlyWageEndTime = $data['hourly_wage_end_time'];
                array_push( $hourlyChangeList, $hourlyWageEndTime );    // 休憩終了時間
                
                $hourlyWageList = $data['hourly_wage'];
                array_push( $hourlyChangeList, $hourlyWageList );     // 設定時間帯手当代(プルダウン)
                
                $hourlyWageValueList = $data['hourly_wage_value'];
                array_push( $hourlyChangeList, $hourlyWageValueList );    // 設定時間帯手当代
            }

            $Log->trace("END selectHourlyWageChange");

            // 一覧表を返す
            return $hourlyChangeList;
        }
        
        
        /**
         * 残業時間マスタ一覧取得SQL文作成
         * @param    $labRegId 
         * @param    $appDateId
         * @return   $overTimeList
         */
        public function getOvertime ( $labRegId, $appDateId )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START selectOverTime");
            

            $sql = ' SELECT application_date_id, labor_regulations_id, overtime_detail_id, regular_working_hours_time, overtime_reference_time, closing_date_set_id  '
                 . ' FROM m_overtime '
                 . ' WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id '
                 . ' ORDER BY overtime_id, overtime_detail_id';
            
            $parametersArray = array(
                                        ':labor_regulations_id' => $labRegId,
                                        ':application_date_id'  => $appDateId,
                                    );
            $result = $DBA->executeSQL($sql, $parametersArray);
            $overTimeList = array();
            if( $result === false )
            {
                $Log->trace("END getOvertime");
                return $overTimeList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $regularWorking = $this->changeTimeFromMinute($data['regular_working_hours_time']);   // 所定時間
                $overtimeReference = $this->changeTimeFromMinute($data['overtime_reference_time']);   // 残業時間
                array_push( $overTimeList, $regularWorking );       // 所定時間
                array_push( $overTimeList, $overtimeReference );    // 残業時間
            }

            $Log->trace("END getOvertime");
            return $overTimeList;
        }
        
        /**
         * 残業時間マスタ〆IDの取得SQL文作成
         * @param    $labRegId 
         * @param    $appDateId
         * @return   closingDateSetId(数値)
         */
        public function getClosingDateSetId ( $labRegId, $appDateId )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getClosingDateSetId");
            

            $sql = ' SELECT closing_date_set_id  '
                 . ' FROM m_overtime '
                 . ' WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id ';
            
            $parametersArray = array(
                                        ':labor_regulations_id' => $labRegId,
                                        ':application_date_id'  => $appDateId,
                                    );
            $result = $DBA->executeSQL($sql, $parametersArray);
            
            $closingDateSetId = 0;
            if( $result === false )
            {
                $Log->trace("END getClosingDateSetId");
                return $closingDateSetId;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $closingDateSetId = $data['closing_date_set_id'];
            }

            $Log->trace("END getClosingDateSetId");
            return $closingDateSetId;
        }
        
        /**
         * 就業規則休憩時間マスタ一覧取得SQL文作成
         * @param    $labRegId 
         * @param    $appDateId
         * @return   $breakTimeList
         */
        public function getBreakTimeList ( $labRegId, $appDateId )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getBreakTimeList");
            

            $sql = ' SELECT application_date_id, labor_regulations_id, binding_hour, break_time   '
                 . ' FROM m_work_rules_break '
                 . ' WHERE application_date_id = :application_date_id '
                 . ' AND labor_regulations_id = :labor_regulations_id  ';
           
            $parametersArray = array(
                                        ':labor_regulations_id' => $labRegId,
                                        ':application_date_id'  => $appDateId,
                                    );
            $result = $DBA->executeSQL($sql, $parametersArray);
            $breakTimeList = array();
            if( $result === false )
            {
                $Log->trace("END getBreakTimeList");
                return $breakTimeList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $bindingHour = $data['binding_hour'];
                $bindingHour = substr( $bindingHour, 0, -3);
                array_push( $breakTimeList, $bindingHour );     // 拘束時間
                
                $tmpBreakTime = $data['break_time'];
                $tmpBreakTime = substr( $tmpBreakTime, 0, -3);
                array_push( $breakTimeList, $tmpBreakTime );    // 付与時間（休憩時間)
            }

            $Log->trace("END getBreakTimeList");
            return $breakTimeList;
        }
 
        /**
         * 就業規則休憩シフトマスタ一覧取得SQL文作成
         * @param    $labRegId 
         * @param    $appDateId
         * @return   $shiftBreakList
         */
        public function getShiftBreakTimeList ( $labRegId, $appDateId )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAttendanceTimeList");
            

            $sql = ' SELECT application_date_id, labor_regulations_id, elapsed_time, break_time   '
                 . ' FROM m_work_rules_shift_break '
                 . ' WHERE application_date_id = :application_date_id '
                 . ' AND labor_regulations_id = :labor_regulations_id  ';
            
            $parametersArray = array(
                                        ':labor_regulations_id' => $labRegId,
                                        ':application_date_id'  => $appDateId,
                                    );
            $result = $DBA->executeSQL($sql, $parametersArray);
            $shiftBreakList = array();
            if( $result === false )
            {
                $Log->trace("END getAttendanceTimeList");
                return $shiftBreakList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $elapsedTime = $data['elapsed_time'];
                $elapsedTime = substr( $elapsedTime, 0, -3);
                array_push( $shiftBreakList, $elapsedTime );            // 出勤時間（経過時間）
                
                $elapsedBreakTime = $data['break_time'];
                $elapsedBreakTime = substr( $elapsedBreakTime, 0, -3);
                array_push( $shiftBreakList, $elapsedBreakTime );       // 付与時間（休憩時間）
            }

            $Log->trace("END getAttendanceTimeList");
            return $shiftBreakList;
        }

        /**
         * 就業規則マスタデータ削除
         * @param    $postArray(就業規則ID/削除フラグ（1）/ユーザID/更新組織ID)
         * @return   SQLの実行結果
         */
        public function delUpdateData($postArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            // 適用予定の場合、就業規則マスタを物理削除する
            $today = date("Y/m/d");
            $targetDay = $postArray['applicationDateStart'];
            if( strtotime($today) < strtotime($targetDay) )
            {
                // 物理削除
                $ret = $this->executeDelSQL( $postArray );
            }
            else
            {
                if( !$this->isDel( $postArray['laborRegulationsID'] ) )
                {
                    $Log->trace("END delUpdateData");
                    return "MSG_WAR_2101";
                }

                $sql = 'UPDATE m_labor_regulations SET'
                    . '   is_del = 1 '
                    . ' , update_time = current_timestamp'
                    . ' , update_user_id = :update_user_id'
                    . ' , update_organization = :update_organization'
                    . ' WHERE labor_regulations_id = :labor_regulations_id AND update_time = :update_time ';

                $parameters = array(
                    ':labor_regulations_id'      => $postArray['laborRegulationsID'],
                    ':update_user_id'            => $postArray['user_id'],
                    ':update_organization'       => $postArray['organization'],
                    ':update_time'               => $postArray['updateTime'],
                );

                // 削除SQL実行
                $ret = $this->executeOneTableSQL( $sql, $parameters );
            }

            $Log->trace("END delUpdateData");

            return $ret;
        }
        
        /**
         * 就業規則マスタデータ更新
         * @param    $postArray(就業規則ID/組織ID/就業規則名/削除フラグ/更新ユーザID/更新組織ID)
         * @param    $laborRegId
         * @return   SQLの実行結果(true/false)
         */
        protected function modLaborRegulationsUpdateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modLaborRegulationsUpdateData");

            $parameters = array(
                ':labor_regulations_id'      => $postArray['up_labor_regulations_id'],
                ':organization_id'           => $postArray['organization_id'],
                ':labor_regulations_name'    => $postArray['labor_regulations_name'],
                ':disp_order'                => $postArray['disp_order'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':update_time'               => $postArray['updateTime'],
            );

            $sql = 'UPDATE m_labor_regulations SET'
                . '   labor_regulations_id = :labor_regulations_id'
                . ' , organization_id = :organization_id'
                . ' , labor_regulations_name = :labor_regulations_name'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE labor_regulations_id = :labor_regulations_id AND update_time = :update_time ';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "就業規則ID：" . $postArray['up_labor_regulations_id']. "の更新失敗しました。";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            $Log->trace("END modLaborRegulationsUpdateData");

            return "MSG_BASE_0000";
        }

        /**
         * 就業規則適用期間マスタデータ更新
         * @param    $postArray(適用期間ID/就業規則ID/適用開始日/就業規則アラート/就業規則閾値/残業時間アラート/残業時間閾値/コメント)
         * @return   SQLの実行結果(true/false)
         */
        protected function modApplicationDateUpdateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modApplicationDateUpdateData");

            $parameters = array(
                ':application_date_id'                 => $postArray['up_application_date_id'],
                ':labor_regulations_id'                => $postArray['up_labor_regulations_id'],
                ':application_date_start'              => $postArray['application_date_start'],
                ':is_labor_regulations_alert'          => $postArray['is_labor_regulations_alert'],
                ':labor_regulations_alert_value'       => $postArray['labor_regulations_alert_value'],
                ':is_overtime_alert'                   => $postArray['is_overtime_alert'],
                ':overtime_alert_value'                => $postArray['overtime_alert_value'],
                ':app_comment'                         => $postArray['app_comment'],
                ':e_mail'                              => $postArray['e_mail'],
                ':holiday_settings'                    => $postArray['holiday_settings'],
                ':update_user_id'                      => $postArray['user_id'],
                ':update_organization'                 => $postArray['organization'],
            );

            $sql = 'UPDATE m_application_date SET'
                . '   application_date_id = :application_date_id'
                . ' , labor_regulations_id = :labor_regulations_id'
                . ' , application_date_start = :application_date_start'
                . ' , is_labor_regulations_alert = :is_labor_regulations_alert'
                . ' , labor_regulations_alert_value = :labor_regulations_alert_value'
                . ' , is_overtime_alert = :is_overtime_alert'
                . ' , overtime_alert_value = :overtime_alert_value'
                . ' , e_mail = :e_mail'
                . ' , holiday_settings = :holiday_settings'
                . ' , comment = :app_comment'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id ';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "就業規則ID：" . $postArray['up_labor_regulations_id']. "適用期間ID：" . $postArray['up_labor_regulations_id']. "の更新失敗";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            $Log->trace("END modApplicationDateUpdateData");

            return "MSG_BASE_0000";
        }

        /**
         * 対象のマスタ内の対象就業規則登録データの有無を確認
         * @note     新規登録ではない場合のみ判断
         * @param    $targetId
         * @param    $addFlag
         * @param    $tableName
         * @param    $columnName
         * @return   $loginAccountFlag
         */
        protected function getTargetDataPresence($targetId, $addFlag, $tableName, $columnName)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getTargetDataPresence");

            $targetDataCnt = "";
            if(empty($addFlag))
            {
                $targetDataCnt = $this->generationSQL->getRegistrationCount($targetId, $tableName, $columnName);
            }

            $Log->trace("END getTargetDataPresence");
            return $targetDataCnt;
        }

        /**
         * 対象のマスタ内の対象就業規則登録データの削除
         * @note     対象のマスタ内の対象就業規則登録データが存在する場合のみ
         * @param    $targetId
         * @param    $dataCnt
         * @param    $tableName
         * @param    $columnName
         * @return   $loginAccountFlag
         */
        protected function getTargetDataPresenceDelete($targetId, $dataCnt, $tableName, $columnName)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getTargetDataPresenceDelete");

            // 登録データがある場合、データを削除する。
            $ret = "MSG_BASE_0000";
            if(!empty($dataCnt))
            {
                $ret = $this->generationSQL->physicalDeleteData($targetId, $tableName, $columnName);
            }

            $Log->trace("END getTargetDataPresenceDelete");
            return $ret;
        }
        
        /**
         * 削除SQL実行
         * @param    $applicationDateId  適用期間ID
         * @param    $laborRegulationsID  就業規則ID
         * @return   なし
         */
        protected function modDel( $applicationDateId, $laborRegulationsID)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modDel");

            // SQL実行
            //就業規則休憩時間帯マスタ削除
            $ret = $this->breakTimeZoneDel($applicationDateId, $laborRegulationsID);
            
            //就業規則時給変更マスタ削除
            $ret = $this->hourlyWageChangeDel($applicationDateId, $laborRegulationsID);
            
            //残業時間マスタ削除
            $ret = $this->overTimeDel($applicationDateId, $laborRegulationsID);
            
            //就業規則手当マスタ削除
            $ret = $this->workRulesAllowanceDel($applicationDateId, $laborRegulationsID);
            
            //就業規則休憩時間マスタ削除
            $ret = $this->workRulesBreakDel($applicationDateId, $laborRegulationsID);
            
            //就業規則時間マスタ削除
            $ret = $this->workRulesTimeDel($applicationDateId, $laborRegulationsID);
            
            //就業規則休憩シフトマスタ削除
            $ret = $this->workRulesShiftBreakIdDel($applicationDateId, $laborRegulationsID);

            $Log->trace("END modDel");
        }
        
        /**
         * 就業規則休憩時間帯マスタ削除
         * @param    $hourlyWageChangeId
         * @return   なし
         */
        private function breakTimeZoneDel( $applicationDateId, $laborRegulationsID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START breakTimeZoneDel");

            $sql = ' DELETE FROM m_break_time_zone WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id ';
            $parameters = array( 
                ':application_date_id' => $applicationDateId,
                ':labor_regulations_id' => $laborRegulationsID,
            );

            // SQL実行
            $ret = $DBA->executeSQL($sql, $parameters, false);

            $Log->trace("END breakTimeZoneDel");
            return $ret;
        }


        /**
         * 就業規則時給変更マスタ削除
         * @param    $hourlyWageChangeId
         * @return   なし
         */
        private function hourlyWageChangeDel( $applicationDateId, $laborRegulationsID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START hourlyWageChangeDel");

            $sql = ' DELETE FROM m_hourly_wage_change WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id ';
            $parameters = array( 
                ':application_date_id' => $applicationDateId,
                ':labor_regulations_id' => $laborRegulationsID,
            );

            // SQL実行
            $ret = $DBA->executeSQL($sql, $parameters, false);

            $Log->trace("END hourlyWageChangeDel");
            return $ret;
        }


        /**
         * 就業規則手当マスタ削除
         * @param    $applicationDateId
         * @return   なし
         */

        private function workRulesAllowanceDel( $applicationDateId, $laborRegulationsID  )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START workRulesAllowanceDel");

            $sql = ' DELETE FROM m_work_rules_allowance WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id ';
            $parameters = array( 
                ':application_date_id' => $applicationDateId,
                ':labor_regulations_id' => $laborRegulationsID,
            );

            // SQL実行
            $ret = $DBA->executeSQL($sql, $parameters, false);

            $Log->trace("END workRulesAllowanceDel");
            return $ret;
        }


        /**
         * 残業時間マスタ削除
         * @param    $overTimeId
         * @return   なし
         */
        private function overTimeDel( $applicationDateId, $laborRegulationsID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START overTimeDel");

            $sql = ' DELETE FROM m_overtime WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id ';
            $parameters = array( 
                ':application_date_id' => $applicationDateId,
                ':labor_regulations_id' => $laborRegulationsID,
            );

            // SQL実行
            $ret = $DBA->executeSQL($sql, $parameters, false);

            $Log->trace("END overTimeDel");
            return $ret;
        }
    
    
        /**
         * 就業規則休憩時間マスタ削除
         * @param    $workRulesBreakId
         * @return   なし
         */
        private function workRulesBreakDel( $applicationDateId, $laborRegulationsID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START workRulesBreakDel");

            $sql = ' DELETE FROM m_work_rules_break WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id ';
            $parameters = array( 
                ':application_date_id' => $applicationDateId,
                ':labor_regulations_id' => $laborRegulationsID,
            );

            // SQL実行
            $ret = $DBA->executeSQL($sql, $parameters, false);

            $Log->trace("END workRulesBreakDel");
            return $ret;
        }
 
  
        /**
         * 就業規則時間マスタ削除
         * @param    $applicationDateId,$laborRegulationsID
         * @return   なし
         */
        private function workRulesTimeDel( $applicationDateId, $laborRegulationsID)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START workRulesTimeDel");

            $sql = ' DELETE FROM m_work_rules_time WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id ';
            $parameters = array( 
                ':application_date_id' => $applicationDateId,
                ':labor_regulations_id' => $laborRegulationsID,
            );

            // SQL実行
            $ret = $DBA->executeSQL($sql, $parameters, false);

            $Log->trace("END workRulesTimeDel");
            return $ret;
        }


        /**
         * 就業規則休憩シフトマスタ削除
         * @param    $workShiftBreakId
         * @return   なし
         */
        private function workRulesShiftBreakIdDel( $applicationDateId, $laborRegulationsID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START workRulesShiftBreakIdDel");

            $sql = ' DELETE FROM m_work_rules_shift_break WHERE application_date_id = :application_date_id AND labor_regulations_id = :labor_regulations_id ';
            $parameters = array( 
                ':application_date_id' => $applicationDateId,
                ':labor_regulations_id' => $laborRegulationsID,
            );

            // SQL実行
            $ret = $DBA->executeSQL($sql, $parameters, false);

            $Log->trace("END workRulesShiftBreakIdDel");
            return $ret;
        }
        
        /**
         * タイムスタンプ型からタイム型に変換設定
         * @param    $timeCheck(postArrayの値)
         * @param    $startFlag(開始時間か終了時間かのフラグ)
         * @return   $timetime(変換後の値)
         */
        private function changeTime($timeCheck, $startFlag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeTime");
            
            $timetime = $timeCheck;
            if(!empty($timetime))
            {
                if( !empty( $startFlag ) )
                {
                    $timetime = substr($timetime, 11, 5);
                }
                else
                {
                    if(substr($timetime, 0, 10) === '2016/04/01' )
                    {
                        $timetime = substr($timetime, 11, 5);
                    }
                    else
                    {
                        $timetime = substr($timetime, 11, 5);
                        list($time , $minute) = explode(":", $timetime);
                        $timetime = $time + 24 . ":" . $minute;
                    }
                }
            }
            
            $Log->trace("END changeTime");
            return $timetime;
        }
        
        /**
         * 削除してよいか
         * @param    $laborRegulationsID     就業規則ID
         * @return   true：削除可   false：削除不可
         */
        protected function isDel( $laborRegulationsID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START isDel");

            $relevantMasterList = array(
                                            "m_position",
                                            "m_employment",
                                        );

            // 各SQLで使用するパラメータ設定
            $parametersArray = array( ':labor_regulations_id'	=> $laborRegulationsID,);

            // 同一仕様のマスタテーブルでの使用有無をチェック
            foreach( $relevantMasterList as $registration )
            {
                $sql = " SELECT COUNT( labor_regulations_id ) as count "
                     . " FROM " . $registration . " WHERE is_del = 0 AND labor_regulations_id = :labor_regulations_id ";
                
                $result = $DBA->executeSQL($sql, $parametersArray);

                if( $result === false )
                {
                    $Log->trace("END isDel");
                    return false;
                    
                }

                while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
                {
                    if( $data['count'] != 0 )
                    {
                        return false;
                    }
                }
            }

            $sql = " SELECT COUNT( od.labor_regulations_id ) as count "
                 . " FROM m_organization_detail od INNER JOIN m_organization o ON o.organization_id = od.organization_id WHERE o.is_del = 0 AND od.labor_regulations_id = :labor_regulations_id ";
            
            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END isDel");
                return false;
                
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['count'] != 0 )
                {
                    return false;
                }
            }

            $Log->trace("END isDel");

            // 全てで未使用の場合、trueを返す
            return true;
        }
        
        /**
         * データの物理削除
         * @param    $postArray     入力パラメータ
         * @return   実行結果
         */
        private function executeDelSQL( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START executeOneTableSQL");

            // 就業規則マスタに適用予定しか存在しない
            $sql = " SELECT COUNT( application_date_id ) as count "
                 . " FROM m_application_date WHERE labor_regulations_id = :labor_regulations_id ";

            $parameters = array(
                ':labor_regulations_id'           => $postArray['laborRegulationsID'],
            );
            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                // SQL実行エラー
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "SQL実行に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END executeDelSQL");
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            $allDel = false;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['count'] == 1 )
                {
                    $allDel = true;
                }
            }

            if( $allDel )
            {
                // 就業規則マスタも物理削除する場合のみ、関連チェックを実行する
                if( !$this->isDel( $postArray['laborRegulationsID'] ) )
                {
                    $Log->trace("END executeDelSQL");
                    return "MSG_WAR_2101";
                }
            }

            if( $DBA->beginTransaction() )
            {
                $parameters = array(
                    ':application_date_id'           => $postArray['applicationDateId'],
                );
                
                // 残業マスタの物理削除
                $sql = ' DELETE FROM m_overtime WHERE application_date_id = :application_date_id  ';
                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters ) )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "残業マスタの削除に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeDelSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 就業規則手当マスタの物理削除
                $sql = ' DELETE FROM m_work_rules_allowance WHERE application_date_id = :application_date_id  ';
                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters ) )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "就業規則手当マスタの削除に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeDelSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 就業規則時給変更マスタの物理削除
                $sql = ' DELETE FROM m_hourly_wage_change WHERE application_date_id = :application_date_id  ';
                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters ) )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "就業規則時給変更マスタの削除に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeDelSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 就業規則休憩時間帯マスタの物理削除
                $sql = ' DELETE FROM m_break_time_zone WHERE application_date_id = :application_date_id  ';
                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters ) )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "就業規則休憩時間帯マスタの削除に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeDelSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 就業規則休憩シフトマスタの物理削除
                $sql = ' DELETE FROM m_work_rules_shift_break WHERE application_date_id = :application_date_id  ';
                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters ) )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "就業規則休憩シフトマスタの削除に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeDelSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 就業規則休憩時間マスタの物理削除
                $sql = ' DELETE FROM m_work_rules_break WHERE application_date_id = :application_date_id  ';
                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters ) )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "就業規則休憩時間マスタの削除に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeDelSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 就業規則時間マスタの物理削除
                $sql = ' DELETE FROM m_work_rules_time WHERE application_date_id = :application_date_id  ';
                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters ) )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "就業規則時間マスタの削除に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeDelSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 就業規則適用期間マスタの物理削除
                $sql = ' DELETE FROM m_application_date WHERE application_date_id = :application_date_id  ';
                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters ) )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "就業規則適用期間マスタの削除に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeDelSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 就業規則マスタも削除する
                if( $allDel )
                {
                    // 就業規則マスタの物理削除
                    $sql = ' DELETE FROM m_labor_regulations WHERE labor_regulations_id = :labor_regulations_id  AND update_time = :update_time ';

                    $parameters = array(
                        ':labor_regulations_id'  => $postArray['laborRegulationsID'],
                        ':update_time'           => $postArray['updateTime'],
                    );

                    // SQL実行
                    if( !$DBA->executeSQL($sql, $parameters, true) )
                    {
                        // SQL実行エラー ロールバック対応
                        $DBA->rollBack();
                        // SQL実行エラー
                        $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                        $errMsg = "就業規則マスタの削除に失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END executeDelSQL");
                        return "MSG_FW_DB_EXCLUSION_NG";
                    }
                }

                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラーロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "コミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeOneTableSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "トランザクション開始エラー";
                $Log->fatalDetail($errMsg);
                $Log->trace("END executeOneTableSQL");
                return "MSG_FW_DB_TRANSACTION_NG";
            }

            $Log->trace("END executeOneTableSQL");

            return "MSG_BASE_0000";
        }
    }

?>