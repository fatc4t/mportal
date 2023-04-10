<?php
    /**
     * @file      打刻管理用ベースクラス
     * @author    USE Y.Sakata
     * @date      2016/08/17
     * @version   1.00
     * @note      打刻管理用に必要なDBアクセスの制御を行う
     */

    // BaseModel.phpを読み込む
    require_once './Model/Common/BaseModel.php';
    // WorkingTimeRegistration.phpを読み込む
    require_once './Model/WorkingTimeRegistration.php';

    /**
     * 打刻管理クラス
     * @note   打刻管理に必要なDBアクセスの制御を行う
     */
    class BaseEmbossing extends BaseModel
    {
        protected $workTimeReg = null;              ///< 労働時間計算用モデル

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // ModelBaseのコンストラクタ
            parent::__construct();
            global $workTimeReg;
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
         * 打刻情報を保存する
         * @param    $organID          従業員所属組織ID
         * @param    $employeesNo      従業員No
         * @param    $authenKey        打刻場所認証キー
         * @param    $embossingType    打刻種別
         * @param    $dateTime         ローカル時間
         * @param    $isViolation      違反フラグ
         * @param    $isAttendance     勤怠反映フラグ
         * @return   SQLの実行結果
         */
        public function setEmbossing( $organID, $employeesNo, $authenKey, $embossingType, $dateTime, $isViolation, $isAttendance )
        {
            global $DBA, $Log, $workTimeReg; // グローバル変数宣言
            $Log->trace("START setEmbossing");

            if( $DBA->beginTransaction() )
            {
                // 認証キーから、打刻場所組織IDを取得
                $locationOrganID = $this->securityProcess->getAuthenKeyToOrganID( $authenKey );
                
                // 組織IDと従業員Noから、ユーザIDを取得
                $userInfo = $this->getUserInfoE( $organID, $employeesNo );
                
                // ユーザIDが0の場合、エラーを返却する
                if( $userInfo['user_id'] == 0 )
                {
                    // SQL実行エラー　ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_NO_SET_USER_NO");
                    $Log->trace("END setEmbossing");
                    return "MSG_FW_NO_SET_USER_NO";
                }
                
                // 打刻の正当性チェック
                $isCheck = $this->checkEmbossing( $userInfo['user_id'], $locationOrganID, $embossingType, $userInfo, $dateTime );
                if( "MSG_BASE_0000" != $isCheck )
                {
                    // SQL実行エラー　ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn($isCheck);
                    $Log->trace("END setEmbossing");
                    return $isCheck;
                }

                // SQL実行 (打刻情報テーブルの更新)
                if( !$this->exeEmbossing( $userInfo, $locationOrganID, $embossingType, $dateTime, $isViolation, $isAttendance ) )
                {
                    // SQL実行エラー　ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "SQL実行に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END setEmbossing");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 勤怠テーブルも反映させるか
                if( $isAttendance == 1 )
                {
                    // Lastidの更新
                    $Lastid = $DBA->lastInsertId( "t_embossing" );

                    // 打刻時間の取得
                    $embossingTime = $this->getEmbossingTime( $Lastid );

                    // 勤怠テーブルの更新
                    $attendanceID = $this->exeAttendance( $userInfo, $locationOrganID, $embossingType, $Lastid, $embossingTime);
                    // 勤怠テーブルの更新失敗
                    if( 0 == $attendanceID )
                    {
                        // SQL実行エラー　ロールバック対応
                        $DBA->rollBack();
                        // SQL実行エラー
                        $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                        $errMsg = "SQL実行に失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END setEmbossing");
                        return "MSG_FW_DB_EXCLUSION_NG";
                    }

                    // 勤怠時間の更新を行う
                    $workTimeReg->setAttendanceInfo( $attendanceID );
                }

                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "コミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END setEmbossing");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "トランザクション開始エラー";
                $Log->fatalDetail($errMsg);
                $Log->trace("END setEmbossing");
                return "MSG_FW_DB_TRANSACTION_NG";
            }

            $Log->trace("END setEmbossing");
            return  "MSG_BASE_0000";
        }


        /**
         * 勤怠テーブル更新
         * @param    $userInfo          ユーザ情報
         * @param    $locationOrganID   打刻場所の組織ID
         * @param    $embossingType     打刻種別
         * @param    $embossingID       打刻ID
         * @param    $embossingTime     打刻時間
         * @return   勤怠情報ID
         */
        protected function exeAttendance( $userInfo, $locationOrganID, $embossingType, $embossingID, $embossingTime )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START exeAttendance");

            // 就業規則情報を取得
            $employInfo = $this->getUserEmploymentInfo($userInfo);
            
            //打刻日付の取得
            $date = new DateTime($embossingTime);
            $embossingDateOrg = $date->format('Y/m/d');
            $embossingDate = $embossingDateOrg;
            
            // 打刻の丸め情報を取得
            $embossingTime = $this->roundingEmbossingTime( $employInfo, $embossingType, $embossingTime );

            // 打刻時間を分に修正
            $iEmbossingTime = $this->changeMinuteFromTime( $embossingTime );
            
            // 開始時間を分に修正
            $iStartTime = $this->changeMinuteFromTime( $employInfo['start_time_day'] );

            // 打刻データがおかしくなるので、出社制限を排除 2017/10/06 millionet oota
            // 打刻時間が開始時間を超えていない場合、前日の勤怠とする又は、出勤時間が、開始時間の60分以上前の場合
//            if( ( $embossingType != 2 && $iEmbossingTime < $iStartTime ) || ( $embossingType == 2 && $iEmbossingTime < ( $iStartTime - SystemParameters::$ATTENDANCE_DECISION_TIME ) ) )
            if( ( $embossingType != 2 && $iEmbossingTime < $iStartTime ) )
            {
                // 日付を-1する
                $embossingDate = date("Y/m/d", strtotime("$embossingDateOrg -1 day" ));
            }

            // シフト情報を取得
            $shiftInfo = $this->getShiftInfo( $userInfo, $embossingDate, $iEmbossingTime );

            // 休日情報を取得
            $holidayInfo = $this->getHolidayInfo( $locationOrganID, $userInfo, $shiftInfo, $employInfo, $embossingDate );

            $embossingTime = $embossingDateOrg." ". $embossingTime;

            $attendanceID = $this->getAttendanceID( $userInfo['user_id'], $locationOrganID, $embossingDate, $holidayInfo, $shiftInfo['shift_id'], $embossingType, true );
            // 勤怠情報がある
            if( $attendanceID != 0 )
            {
                // 更新処理
                $ret = $this->modAttendanceData($userInfo['user_id'],$userInfo['organization_id'], $locationOrganID, $embossingType, $embossingTime, $embossingDate, $shiftInfo['shift_id'] ,$holidayInfo, $embossingID, $attendanceID);
            }
            else
            {
                // 新規追加
                $ret = $this->addAttendanceNewData($userInfo['user_id'],$userInfo['organization_id'], $locationOrganID,$embossingType, $embossingTime, $embossingDate, $shiftInfo['shift_id'] ,$holidayInfo, $embossingID, $employInfo);
            }
            
            // 勤怠情報の更新結果を確認
            if( $ret != "MSG_BASE_0000" )
            {
                // 更新エラー
                return 0;
            }

            // 勤怠IDを取り直す
            $attendanceID = $this->getAttendanceID( $userInfo['user_id'], $locationOrganID, $embossingDate, $holidayInfo, $shiftInfo['shift_id'], $embossingType, false );

            $Log->trace("END exeAttendance");
            return $attendanceID;
        }

        /**
         * ユーザ情報を取得する
         * @param    $organID      従業員の所属組織ID
         * @param    $employeesNo  従業員No
         * @return   従業員リスト
         */
        protected function getUserInfoE( $organID, $employeesNo )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserInfoE");
            
            // 従業員Noから、ユーザIDを取得
            $sql  = " SELECT user_id, employment_id, position_id FROM v_user "
                  . " WHERE eff_code like '適用中' AND employees_no = :employees_no AND organization_id = :organization_id ";

            $parameters = array( ':organization_id'  => $organID,
                                 ':employees_no'     => $employeesNo, 
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array(
                            'user_id'           => 0,
                            'employment_id'     => 0,
                            'position_id'       => 0,
                            'organization_id'   => $organID,
                        );
            if( $result === false )
            {
                $Log->trace("END getUserInfoE");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = array(
                                'user_id'           => $data['user_id'],
                                'employment_id'     => $data['employment_id'],
                                'position_id'       => $data['position_id'],
                                'organization_id'   => $organID,
                            );
            }

            $Log->trace("END getUserInfoE");
            return $ret;
        }

        /**
         * 打刻情報を保存する
         * @param   $userInfo         ユーザ情報
         * @param   $locationOrganID  打刻場所の組織ID
         * @param   $embossingType    打刻種別
         * @param   $dateTime         ローカル時間
         * @param   $isViolation      違反フラグ
         * @param   $isAttendance     勤怠反映フラグ
         * @return  更新結果 true：OK  false：NG
         */
        private function exeEmbossing( $userInfo, $locationOrganID, $embossingType, $dateTime, $isViolation, $isAttendance )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START exeEmbossing");

            $sql  = " INSERT INTO t_embossing( user_id, organization_id, date_time, "
                  . "                          embossing_type, is_local_time, is_violation, is_attendance, "
                  . "                          registration_time, registration_user_id, registration_organization, "
                  . "                          update_time, update_user_id, update_organization ) VALUES ( "
                  . "                          :user_id, :organization_id, ";

            if($dateTime != "")
            {
                $sql  .= ":date_time, ";
            }
            else
            {
                $sql  .= " current_timestamp, ";
            }

            $sql  .=  "                        :embossing_type, :is_local_time, :is_violation, :is_attendance, "
                  .   "                        current_timestamp, :user_id, :update_organization, "
                  .   "                        current_timestamp, :user_id, :update_organization ) ";

            $isLocalTime = 0;
            
            if( $dateTime != "" )
            {
                $isLocalTime = 1;
            }

            $parameters = array( 
                                 ':user_id'             => $userInfo['user_id'],
                                 ':organization_id'     => $locationOrganID,
                                 ':embossing_type'      => $embossingType,
                                 ':update_organization' => $userInfo['organization_id'],
                                 ':is_local_time'       => $isLocalTime,
                                 ':is_violation'        => $isViolation,
                                 ':is_attendance'       => $isAttendance,
                               );
            if($dateTime != "")
            {
                $parameters = array_merge( $parameters , array( ':date_time' => $dateTime, ));
            }

            // SQL実行 (打刻情報テーブルの更新)
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "SQL実行に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END exeEmbossing");
                return false;
            }

            $Log->trace("END   exeEmbossing");

            return true;
        }

        /**
         * 打刻情報を取得する
         * @param    $embossingID     打刻ID
         * @return   打刻時間
         */
        private function getEmbossingTime( $embossingID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getEmbossingTime");
            
            // 就業開始時間と、就業規則IDを取得
            $sql  = " SELECT date_time  FROM t_embossing WHERE embossing_id = :embossing_id ";

            $parameters = array( 
                                    ':embossing_id'    => $embossingID, 
                                );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = "";
            if( $result === false )
            {
                $Log->trace("END getEmbossingTime");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data['date_time'];
            }

            $Log->trace("END getEmbossingTime");
            return $ret;
        }

        /**
         * 丸め打刻情報を取得
         * @param    $employInfo        就業規則情報
         * @param    $embossingType     打刻種別
         * @param    $embossingTime     打刻時間
         * @return   丸めた打刻時間
         */
        private function roundingEmbossingTime( $employInfo, $embossingType, $embossingTime )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START roundingEmbossingTime");
           
            // 打刻時間を分に修正
            $localTime = substr($embossingTime, 11, 5 );
            $iEmbossingTime = $this->changeMinuteFromTime( $localTime );
            
            $roundEmbossingList = array(
                                            1 => array( $employInfo['attendance_rounding_time'], $employInfo['attendance_rounding'] ),
                                            2 => array( $employInfo['attendance_rounding_time'], $employInfo['attendance_rounding'] ),
                                            3 => array( $employInfo['break_rounding_start_time'], $employInfo['break_rounding_start'] ),
                                            4 => array( $employInfo['break_rounding_end_time'], $employInfo['break_rounding_end'] ),
                                            5 => array( $employInfo['leave_work_rounding_time'], $employInfo['leave_work_rounding'] ),
                                        );

            // 打刻の丸め処理
            if( $roundEmbossingList[$embossingType][1] == 1 )
            {
                // 切り上げ
                $iEmbossingTime = ceil( $iEmbossingTime / $roundEmbossingList[$embossingType][0] ) * $roundEmbossingList[$embossingType][0];
                // 1分単位の切り上げの場合、1分足す
                if( $roundEmbossingList[$embossingType][0] == 1 )
                {
                    $iEmbossingTime++;
                }
            }
            else
            {
                // 切り捨て
                $iEmbossingTime = floor( $iEmbossingTime / $roundEmbossingList[$embossingType][0] ) * $roundEmbossingList[$embossingType][0];
            }
            
            $addEmbossingTime = $this->changeTimeFromMinute( $iEmbossingTime );

            $Log->trace("END roundingEmbossingTime");
            
            return $addEmbossingTime;
        }

        /**
         * 勤怠IDを取得する
         * @param    $userID            ユーザID
         * @param    $locationOrganID   打刻場所の組織ID
         * @param    $embossingDate     打刻日
         * @param    $holidayInfo       休日情報
         * @param    $shiftID           シフトID
         * @param    $embossingType     打刻タイプ
         * @param    $isAttendance      出勤判定フラグ
         * @return   勤怠ID             0：勤怠レコードなし   0以外：勤怠ID
         */
        private function getAttendanceID( $userID, $locationOrganID, $embossingDate, $holidayInfo, $shiftID, $embossingType, $isAttendance )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAttendanceID");
            
            // 勤怠情報がデータを取得する
            $sql  = " SELECT attendance_id, attendance_time_id FROM t_attendance "
                  . " WHERE  user_id = :user_id AND organization_id = :organization_id "
                  . "   AND  date = :date AND is_holiday = :is_holiday AND shift_id = :shift_id AND is_del = 0 "
                  . " ORDER BY attendance_id DESC ";

            $parameters = array( 
                                    ':user_id'          => $userID, 
                                    ':organization_id'  => $locationOrganID, 
                                    ':date'             => $embossingDate, 
                                    ':is_holiday'       => $holidayInfo, 
                                    ':shift_id'         => $shiftID,
                                );

            $result = $DBA->executeSQL($sql, $parameters);

            if( $result === false )
            {
                $Log->trace("END getAttendanceID");
                return 0;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                // 2度目の出勤であるか判定する必要があるか
                if( $isAttendance && $embossingType == 2 )
                {
                    if( !empty( $data['attendance_time_id'] ) )
                    {
                        break;
                    }
                }
                
                $Log->trace("END getAttendanceID");
                return $data['attendance_id'];
            }

            $Log->trace("END getAttendanceID");

            return 0;
        }

        /**
         * 勤怠テーブル新規データ登録
         * @param    $userId            ユーザID
         * @param    $organizationID    ユーザ所属組織ID
         * @param    $locationOrganID   打刻場所組織ID
         * @param    $embossingType     打刻種別
         * @param    $embossingTime     打刻時間
         * @param    $embossingDate     打刻日
         * @param    $shiftInfoID       シフトID
         * @param    $holidayInfo       休日情報
         * @param    $embossingID       打刻ID
         * @param    $employInfo        就業規則情報
         * @return   SQLの実行結果
         */
        private function addAttendanceNewData( $userId, $organizationID, $locationOrganID, $embossingType, $embossingTime, $embossingDate, $shiftInfoID ,$holidayInfo, $embossingID, $employInfo)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addAttendanceNewData");

            $sqlParamenter = "";
            $parameters = array(
                    ':user_id'                          => $userId,
                    ':registration_user_id'             => $userId,
                    ':registration_organization'        => $organizationID,
                    ':update_user_id'                   => $userId,
                    ':update_organization'              => $organizationID,
                    ':organization_id'                  => $locationOrganID,
                    ':date'                             => $embossingDate,
                    ':is_holiday'                       => $holidayInfo,
                    ':shift_id'                         => $shiftInfoID,
                    ':labor_regulations_id'             => $employInfo['labor_regulations_id'],
                );

            if( $embossingType == 1 )
            {
                $parameters[':attendance_time_id'] = $embossingID;
                $parameters[':attendance_time']    = $embossingTime;
            }
            if( $embossingType == 2 )
            {
                $parameters[':attendance_time_id'] = $embossingID;
                $parameters[':attendance_time']    = $embossingTime;
            }
            if( $embossingType == 3 )
            {
                $parameters[':s_break_time_1_id'] = $embossingID;
                $parameters[':s_break_time_1']    = $embossingTime;
            }
            if( $embossingType == 4 )
            {
                $parameters[':e_break_time_1_id'] = $embossingID;
                $parameters[':e_break_time_1']    = $embossingTime;
            }
            if( $embossingType == 5 )
            {
                $parameters[':clock_out_time_id'] = $embossingID;
                $parameters[':clock_out_time']    = $embossingTime;
            }

            $sql = 'INSERT INTO t_attendance( user_id'
                 . '                        , organization_id'
                 . '                        , date'
                 . '                        , is_holiday'
                 . '                        , shift_id'
                 . '                        , labor_regulations_id'
                 . '                        , registration_time'
                 . '                        , registration_user_id'
                 . '                        , registration_organization'
                 . '                        , update_time'
                 . '                        , update_user_id'
                 . '                        , update_organization';
            $sql .= $this->creatTypeSQL( $embossingType );
            $sql .= '                       ) VALUES ('
                 . '                         :user_id'
                 . '                       , :organization_id'
                 . '                       , :date'
                 . '                       , :is_holiday'
                 . '                       , :shift_id'
                 . '                       , :labor_regulations_id'
                 . '                       , current_timestamp'
                 . '                       , :registration_user_id'
                 . '                       , :registration_organization'
                 . '                       , current_timestamp'
                 . '                       , :update_user_id'
                 . '                       , :update_organization';
            $sql .= $this->creatTypeValueSQL( $embossingType );
            $sql .= ')';

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
         * 勤怠テーブルのカラムリスト
         * @return   カラムのリスト
         */
        private function getAttendanceColumnList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAttendanceColumnList");

            $ColumnList = array();
            
            $ColumnList = array(
                'organization_id'           => '',
                'date'                      => 'date',
                'is_holiday'                => '',
                'shift_id'                  => '',
            );

            $Log->trace("END getAttendanceColumnList");

            return $ColumnList;
        }


        /**
         * 勤怠テーブル 打刻種別によるsql文追加（value）
         * @return   sql文
         */
        private function creatTypeValueSQL( $embossingType )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatTypeValueSQL");


            $sql = ' ';

            $embossingTypeList = array (
                                         1  => ',:attendance_time_id, :attendance_time',
                                         2  => ',:attendance_time_id, :attendance_time',
                                         3  => ',:s_break_time_1_id, :s_break_time_1',
                                         4  => ',:e_break_time_1_id, :e_break_time_1',
                                         5  => ',:clock_out_time_id, :clock_out_time',
                                        );

            if( array_key_exists( $embossingType, $embossingTypeList ) )
            {
                $sql = $embossingTypeList[$embossingType];
            }

            $Log->trace("END creatTypeValueSQL");
            return $sql;

        }

        /**
         * 勤怠テーブル 打刻種別によるsql文追加
         * @return   sql文
         */
        private function creatTypeSQL( $embossingType )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatTypeSQL");

            $sql = ' ';
            $embossingTypeList = array (
                                         1  => ',attendance_time_id, attendance_time',
                                         2  => ',attendance_time_id, attendance_time',
                                         3  => ',s_break_time_1_id, s_break_time_1',
                                         4  => ',e_break_time_1_id, e_break_time_1',
                                         5  => ',clock_out_time_id,clock_out_time',
                                        );

            if( array_key_exists( $embossingType, $embossingTypeList ) )
            {
                $sql = $embossingTypeList[$embossingType];
            }

            $Log->trace("END creatTypeSQL");
            return $sql;

        }

        /**
         * 勤怠テーブルデータ更新
         * @param    $userId            ユーザID
         * @param    $organizationID    ユーザ所属組織ID
         * @param    $locationOrganID   打刻場所組織ID
         * @param    $embossingType     打刻種別
         * @param    $embossingTime     打刻時間
         * @param    $embossingDate     打刻日
         * @param    $shiftInfoID       シフトID
         * @param    $holidayInfo       休日情報
         * @param    $embossingID       打刻ID
         * @param    $attendanceID      勤怠ID
         * @return   勤怠テーブル更新SQL文
         */
        private function modAttendanceData( $userId, $organizationID, $locationOrganID, $embossingType, $embossingTime, $embossingDate, $shiftInfoID ,$holidayInfo, $embossingID, $attendanceID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modAttendanceData");

            $timeID = $this->getTimeID($userId, $locationOrganID, $embossingDate, $shiftInfoID ,$holidayInfo);

            $sql  = 'UPDATE t_attendance SET'
                 . '       update_time = current_timestamp'
                 . '     , absence_count  = :absence_count'
                 . '     , update_user_id = :update_user_id'
                 . '     , update_organization = :update_organization ';
            $sql .= $this->creatmodTypeSQL( $userId, $locationOrganID, $embossingType, $embossingDate, $shiftInfoID ,$holidayInfo, $timeID);

            $sql .= '    WHERE user_id = :user_id And organization_id = :organization_id And date = :date '
                 . '     And is_holiday = :is_holiday And shift_id = :shift_id AND attendance_id = :attendance_id ';

            $parameters = array( 
                                    ':user_id'                   => $userId, 
                                    ':organization_id'           => $locationOrganID, 
                                    ':date'                      => $embossingDate, 
                                    ':is_holiday'                => $holidayInfo, 
                                    ':shift_id'                  => $shiftInfoID,
                                    ':absence_count'             => 0,
                                    ':attendance_id'             => $attendanceID,
                                    ':update_user_id'            => $userId, 
                                    ':update_organization'       => $organizationID, 
                                );

            $param = $this->creatmodParameters($embossingType, $embossingTime, $embossingID, $timeID);
            $parameters = array_merge($parameters,$param);

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
         * 勤怠テーブル 打刻種別によるsql文追加 (mod)
         * @return   sql文
         */
        private function creatmodTypeSQL( $userId, $locationOrganID, $embossingType, $embossingDate, $shiftInfoID ,$holidayInfo, $timeID )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatmodTypeSQL");
            
            $sql = ' ';

            if( $embossingType == 1 )
            {
                if(!array_filter($timeID))
                {
                    $sql = ',attendance_time_id = :attendance_time_id, attendance_time = :attendance_time';
                }
                elseif(!empty($timeID['attendance_time_id']) && empty($timeID['clock_out_time_id']) )
                {
                    $sql = ',clock_out_time_id = :clock_out_time_id, clock_out_time = :clock_out_time';
                }
                elseif(!empty($timeID['clock_out_time_id']) && empty($timeID['s_break_time_1_id']) )
                {
                    $sql = ',s_break_time_1_id = :s_break_time_1_id, s_break_time_1 = :s_break_time_1'
                         . ',clock_out_time_id = :clock_out_time_id, clock_out_time = :clock_out_time';
                }
                elseif(!empty($timeID['s_break_time_1_id']) && empty($timeID['e_break_time_1_id']) )
                {
                    $sql = ',e_break_time_1_id = :e_break_time_1_id, e_break_time_1 = :e_break_time_1'
                         . ',clock_out_time_id = :clock_out_time_id, clock_out_time = :clock_out_time';
                }
                elseif(!empty($timeID['e_break_time_1_id']) && empty($timeID['s_break_time_2_id']) )
                {
                    $sql = ',s_break_time_2_id = :s_break_time_2_id, s_break_time_2 = :s_break_time_2'
                         . ',clock_out_time_id = :clock_out_time_id, clock_out_time = :clock_out_time';
                }
                elseif(!empty($timeID['s_break_time_2_id']) && empty($timeID['e_break_time_2_id']) )
                {
                    $sql = ',e_break_time_2_id = :e_break_time_2_id, e_break_time_2 = :e_break_time_2'
                         . ',clock_out_time_id = :clock_out_time_id, clock_out_time = :clock_out_time';
                }
                elseif(!empty($timeID['e_break_time_2_id']) && empty($timeID['s_break_time_3_id']) )
                {
                    $sql = ',s_break_time_3_id = :s_break_time_3_id, s_break_time_3 = :s_break_time_3'
                         . ',clock_out_time_id = :clock_out_time_id, clock_out_time = :clock_out_time';
                }
                elseif(!empty($timeID['s_break_time_3_id']) && empty($timeID['e_break_time_3_id']) )
                {
                    $sql = ',e_break_time_3_id = :e_break_time_3_id, e_break_time_3 = :e_break_time_3'
                         . ',clock_out_time_id = :clock_out_time_id, clock_out_time = :clock_out_time';
                }
                elseif(!empty($timeID['e_break_time_3_id']))
                {
                    $sql = ',clock_out_time_id = :clock_out_time_id, clock_out_time = :clock_out_time';
                }
            }
            if( $embossingType == 2 )
            {
                $sql = ',attendance_time_id = :attendance_time_id, attendance_time = :attendance_time';
            }
            if( $embossingType == 3 )
            {
                if( empty($timeID['s_break_time_1_id']))
                {
                $sql = ',s_break_time_1_id = :s_break_time_1_id, s_break_time_1 = :s_break_time_1';
                }
                elseif( empty($timeID['s_break_time_2_id']))
                {
                $sql = ',s_break_time_2_id = :s_break_time_2_id, s_break_time_2 = :s_break_time_2';
                }
                elseif(empty($timeID['s_break_time_3_id']))
                {
                $sql = ',s_break_time_3_id = :s_break_time_3_id, s_break_time_3 = :s_break_time_3';
                }

            }
            if( $embossingType == 4 )
            {
                if( empty($timeID['e_break_time_1_id']))
                {
                $sql = ',e_break_time_1_id = :e_break_time_1_id, e_break_time_1 = :e_break_time_1';
                }
                elseif( empty($timeID['e_break_time_2_id']))
                {
                $sql = ',e_break_time_2_id = :e_break_time_2_id, e_break_time_2 = :e_break_time_2';
                }
                elseif( empty($timeID['e_break_time_3_id']))
                {
                $sql = ',e_break_time_3_id = :e_break_time_3_id, e_break_time_3 = :e_break_time_3';
                }
            }
            if( $embossingType == 5 )
            {
                $sql = ',clock_out_time_id = :clock_out_time_id, clock_out_time = :clock_out_time';
            }


            $Log->trace("END creatmodTypeSQL");
            return $sql;
        }

        /**
         * 勤怠テーブル 打刻種別によるパラメータ追加 (mod)
         * @return   パラメータ
         */
        private function creatmodParameters( $embossingType, $embossingTime, $Lastid, $timeID)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatmodParameters");
            
            // 初期化
            $parameters = array();

            if( $embossingType == 1 )
            {
                if(!array_filter($timeID))
                {
                    $parameters = array( 
                                        ':attendance_time_id'         => $Lastid, 
                                        ':attendance_time'            => $embossingTime, 
                                        );
                }
                elseif(!empty($timeID['attendance_time_id']) && empty($timeID['clock_out_time_id']) )
                {
                    $parameters = array( 
                                        ':clock_out_time_id'         => $Lastid, 
                                        ':clock_out_time'            => $embossingTime, 
                                        );
                }
                elseif(!empty($timeID['clock_out_time_id']) && empty($timeID['s_break_time_1_id']) )
                {
                    $parameters = array( 
                                        ':s_break_time_1_id'         => $timeID['clock_out_time_id'], 
                                        ':s_break_time_1'            => $timeID['clock_out_time'], 
                                        ':clock_out_time_id'         => $Lastid, 
                                        ':clock_out_time'            => $embossingTime, 
                                        );
                }
                elseif(!empty($timeID['s_break_time_1_id']) && empty($timeID['e_break_time_1_id']) )
                {
                    $parameters = array( 
                                        ':e_break_time_1_id'         => $timeID['clock_out_time_id'], 
                                        ':e_break_time_1'            => $timeID['clock_out_time'], 
                                        ':clock_out_time_id'         => $Lastid, 
                                        ':clock_out_time'            => $embossingTime, 
                                        );
                }
                elseif(!empty($timeID['e_break_time_1_id']) && empty($timeID['s_break_time_2_id']) )
                {
                    $parameters = array( 
                                        ':s_break_time_2_id'         => $timeID['clock_out_time_id'], 
                                        ':s_break_time_2'            => $timeID['clock_out_time'], 
                                        ':clock_out_time_id'         => $Lastid, 
                                        ':clock_out_time'            => $embossingTime, 
                                        );
                }
                elseif(!empty($timeID['s_break_time_2_id']) && empty($timeID['e_break_time_2_id']) )
                {
                    $parameters = array( 
                                        ':e_break_time_2_id'         => $timeID['clock_out_time_id'], 
                                        ':e_break_time_2'            => $timeID['clock_out_time'], 
                                        ':clock_out_time_id'         => $Lastid, 
                                        ':clock_out_time'            => $embossingTime, 
                                        );
                }
                elseif(!empty($timeID['e_break_time_2_id']) && empty($timeID['s_break_time_3_id']) )
                {
                    $parameters = array( 
                                        ':s_break_time_3_id'         => $timeID['clock_out_time_id'], 
                                        ':s_break_time_3'            => $timeID['clock_out_time'], 
                                        ':clock_out_time_id'         => $Lastid, 
                                        ':clock_out_time'            => $embossingTime, 
                                        );
                }
                elseif(!empty($timeID['s_break_time_3_id']) && empty($timeID['e_break_time_3_id']) )
                {
                    $parameters = array( 
                                        ':e_break_time_3_id'         => $timeID['clock_out_time_id'], 
                                        ':e_break_time_3'            => $timeID['clock_out_time'], 
                                        ':clock_out_time_id'         => $Lastid, 
                                        ':clock_out_time'            => $embossingTime, 
                                        );
                }

                elseif(!empty($timeID['e_break_time_3_id']))
                {
                    $parameters = array( 
                                        ':clock_out_time_id'         => $Lastid, 
                                        ':clock_out_time'            => $embossingTime, 
                                        );
                }
            }
            if( $embossingType == 2 )
            {
                $parameters = array( 
                                    ':attendance_time_id'         => $Lastid, 
                                    ':attendance_time'            => $embossingTime, 
                                    );
            }
            if( $embossingType == 3 )
            {
                if( empty($timeID['s_break_time_1_id']))
                {
                $parameters = array( 
                                    ':s_break_time_1_id'         => $Lastid, 
                                    ':s_break_time_1'            => $embossingTime, 
                                    );
                }
                elseif( empty($timeID['s_break_time_2_id']))
                {
                $parameters = array( 
                                    ':s_break_time_2_id'         => $Lastid, 
                                    ':s_break_time_2'            => $embossingTime, 
                                    );
                }
                elseif(empty($timeID['s_break_time_3_id']))
                {
                $parameters = array( 
                                    ':s_break_time_3_id'         => $Lastid, 
                                    ':s_break_time_3'            => $embossingTime, 
                                    );
                }
            }
            if( $embossingType == 4 )
            {
                if( empty($timeID['e_break_time_1_id']))
                {
                $parameters = array( 
                                    ':e_break_time_1_id'         => $Lastid, 
                                    ':e_break_time_1'            => $embossingTime, 
                                    );
                }
                elseif( empty($timeID['e_break_time_2_id']))
                {
                $parameters = array( 
                                    ':e_break_time_2_id'         => $Lastid, 
                                    ':e_break_time_2'            => $embossingTime, 
                                    );
                }
                elseif( empty($timeID['e_break_time_3_id']))
                {
                $parameters = array( 
                                    ':e_break_time_3_id'         => $Lastid, 
                                    ':e_break_time_3'            => $embossingTime, 
                                    );
                }
            }
            if( $embossingType == 5 )
            {
                $parameters = array( 
                                    ':clock_out_time_id'         => $Lastid, 
                                    ':clock_out_time'            => $embossingTime, 
                                    );
            }

            $Log->trace("END creatmodParameters");
            return $parameters;

        }

        /**
         * 休憩時間、出勤、退勤時にIDと時刻が入っているか確認
         * @param    $postArray            
         * @param    $userDetailId 
         * @return   SQLの実行結果
         */
        private function getTimeID($userId, $locationOrganID, $embossingDate, $shiftInfoID ,$holidayInfo)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getTimeID");


            $sql = 'SELECT attendance_time_id, clock_out_time_id, s_break_time_1_id, e_break_time_1_id, '
                 . ' s_break_time_2_id, e_break_time_2_id, s_break_time_3_id, e_break_time_3_id, '
                 . ' attendance_time, clock_out_time, s_break_time_1, e_break_time_1, '
                 . ' s_break_time_2, e_break_time_2, s_break_time_3, e_break_time_3 '
                 . 'FROM t_attendance WHERE user_id = :user_id And organization_id = :organization_id And date = :date '
                 . 'And is_holiday = :is_holiday And shift_id = :shift_id AND is_del = 0 ';

            $parameters = array(
                                ':user_id'                          => $userId,
                                ':organization_id'                  => $locationOrganID,
                                ':date'                             => $embossingDate,
                                ':is_holiday'                       => $holidayInfo,
                                ':shift_id'                         => $shiftInfoID,
                                );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array(
                            'attendance_time_id'     =>  '',
                            'clock_out_time_id'      =>  '',
                            's_break_time_1_id'      =>  '',
                            'e_break_time_1_id'      =>  '',
                            's_break_time_2_id'      =>  '',
                            'e_break_time_2_id'      =>  '',
                            's_break_time_3_id'      =>  '',
                            'e_break_time_3_id'      =>  '',
                            'attendance_time'        =>  '',
                            'clock_out_time'         =>  '',
                            's_break_time_1'         =>  '',
                            'e_break_time_1'         =>  '',
                            's_break_time_2'         =>  '',
                            'e_break_time_2'         =>  '',
                            's_break_time_3'         =>  '',
                            'e_break_time_3'         =>  '',
                        );

            if( $result === false )
            {
                $Log->trace("END getTimeID");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $ret = $data;
            }

            $Log->trace("END getTimeID");
            return $ret;
        }
        
        /**
         * 打刻時間の正当性チェック
         * @param    $userId             打刻したユーザID
         * @param    $locationOrganID    打刻した店舗
         * @param    $embossingType      打刻タイプ
         * @param    $userInfo           ユーザ情報
         * @param    $dateTime           打刻時間
         * @return   打刻チェック結果
         */
        private function checkEmbossing( $userId, $locationOrganID, $embossingType, $userInfo, $dateTime )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START checkEmbossing");

            if( $locationOrganID === 0 )
            {
                // 打刻店舗が存在しない
                $Log->trace("END checkEmbossing");
                return "MSG_BASE_0009";
            }

            $sql = ' SELECT embossing_type, date_time FROM t_embossing  '
                 . ' WHERE  user_id = :user_id AND organization_id = :organization_id ORDER BY date_time DESC LIMIT 1 ';

            $parameters = array(
                                    ':user_id'          => $userId,
                                    ':organization_id'  => $locationOrganID,
                                );

            $result = $DBA->executeSQL($sql, $parameters);

            if( $result === false )
            {
                $Log->trace("END checkEmbossing");
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            $ret = "MSG_BASE_0000";
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                // 最後の打刻タイプで、次の打刻可否をチェックする
                if( $embossingType == 2 )
                {
                    // 2回目の出勤
                    if( $data['embossing_type'] != 5 )
                    {
                        // 組織の1日の開始時間を取得する
                        $startTimeDay = $this->getStartTimeDay( $locationOrganID, $dateTime );
                        
                        // 開始時間を分に修正
                        $iStartTime = $this->changeMinuteFromTime( $startTimeDay );

                        // 打刻時間を設定する
                        $today = date("Y-m-d H:i:s");

                        if( !empty( $dateTime ) )
                        {
                            $today = $dateTime;
                        }
                        $time = substr( $today, 11, 5 );
                        $iEmbossingTime = $this->changeMinuteFromTime( $time);

                        $currentEmbossingDay = substr( $today, 0, 10 );
                        $beforeEmbossingDay  = substr( $data['date_time'], 0, 10 );

                        // 前回の打刻日と打刻日が等しくない場合、24H(1440)を足す
                        if( $currentEmbossingDay != $beforeEmbossingDay )
                        {
                            $iStartTime += 1440;
                        }

                        // 出勤時間が、開始時間の60分以上前の場合
                        if( $iEmbossingTime > ( $iStartTime - SystemParameters::$ATTENDANCE_DECISION_TIME ) )
                        {
                            // 退勤打刻がされていない
                            $ret = "MSG_BASE_0004";
                        }
                    }
                }
                else if( $embossingType == 3  )
                {
                    // 出勤打刻 又は 休憩終了打刻以外
                    if( $data['embossing_type'] != 2 && $data['embossing_type'] != 4 )
                    {
                        $ret = "MSG_BASE_0006";
                    }
                }
                else if( $embossingType == 4  )
                {
                    // 休憩開始打刻以外
                    if( $data['embossing_type'] != 3 )
                    {
                        $ret = "MSG_BASE_0005";
                    }
                }
                else if( $embossingType == 5  )
                {
                    // 出勤打刻 又は 休憩終了打刻以外
                    if( $data['embossing_type'] != 2 && $data['embossing_type'] != 4 )
                    {
                        $ret = "MSG_BASE_0006";
                    }
                }
            }

            // 打刻判定に問題なし
            if( $ret == "MSG_BASE_0000" )
            {
                // 他店舗の打刻で、打刻可能か判定する
                $ret = $this->checkEmbossingOtherStores( $userId, $locationOrganID, $embossingType, $userInfo, $dateTime );
            }

            $Log->trace("END checkEmbossing");
            return $ret;
        }

        /**
         * 組織の1日の開始時間を取得する
         * @param    $organizationID  ユーザID
         * @param    $dateTime        勤務日
         * @return   指定日の組織の1日の開始時間
         */
        private function getStartTimeDay( $organizationID, $dateTime )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START getStartTimeDay");

            $date = substr( $dateTime, 0, 10 );
            if( empty( $date ) )
            {
                $date = date("Y-m-d");
            }
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
         * 他店舗打刻の可否を判定
         * @param    $userId             打刻したユーザID
         * @param    $locationOrganID    打刻した店舗
         * @param    $embossingType      打刻タイプ
         * @param    $userInfo           ユーザ情報
         * @param    $dateTime           打刻時間
         * @return   打刻チェック結果
         */
        private function checkEmbossingOtherStores( $userId, $locationOrganID, $embossingType, $userInfo, $dateTime )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START checkEmbossingOtherStores");

            // ユーザ情報を取得する
            $sql = " SELECT organization_id, is_embossing   "
                 . " FROM v_user WHERE user_id = :user_id AND eff_code = '適用中' AND is_del = 0 ";

            $parameters = array( ':user_id' => $userId, );
            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END checkEmbossingOtherStores");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $affiliationOrganID = 0;
            $isEmbossing = 0;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $affiliationOrganID = $data['organization_id'];
                $isEmbossing        = $data['is_embossing'];
            }

            $ret = "MSG_BASE_0000";
            // 所属店舗での打刻である
            if( $affiliationOrganID == $locationOrganID )
            {
                $Log->trace("END checkEmbossingOtherStores");
                return $ret;
            }

            // 打刻チェック対象外の打刻である
            if( $isEmbossing == 0 )
            {
                $Log->trace("END checkEmbossingOtherStores");
                return $ret;
            }

            // 他店舗の打刻かつ、シフトチェックが必要な打刻を処理
            // 打刻店舗の開始時間を取得する
            $sql = " SELECT start_time_day   "
                 . " FROM v_organization WHERE organization_id = :organization_id AND eff_code = '適用中' AND is_del = 0 ";

            $parameters = array( ':organization_id' => $locationOrganID, );
            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END checkEmbossingOtherStores");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            $startTimeDay = "";
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $startTimeDay = $data['start_time_day'];
            }
            
            // 開始時間を分に修正
            $iStartTime = $this->changeMinuteFromTime( $startTimeDay );

            // 出勤日を特定する
            $today = date("Y-m-d H:i:s");
            if( !empty( $dateTime ) )
            {
                // ローカル時間を設定
                $today = $dateTime;
            }
            $time = substr( $today, 11, 5 );
            $iEmbossingTime = $this->changeMinuteFromTime( $time );

            $workingDay = date("Y-m-d", strtotime( $today ) );
            // 開始時間前の打刻である
            if( ( $iEmbossingTime < ( $iStartTime - SystemParameters::$ATTENDANCE_DECISION_TIME ) && $embossingType == 2) ||
                ( $iEmbossingTime <   $iStartTime && $embossingType != 2 ) )
            {
                // 前日の勤怠とする
                $workingDay = date("Y-m-d", strtotime( $today . " -1 day" ) );
            }

            // 店舗のシフト情報を取得する
            $sql = " SELECT attendance, taikin "
                 . " FROM   t_shift WHERE user_id = :user_id AND day = :day ";

            $parameters = array(
                                    ':user_id' => $userId,
                                    ':day'     => $workingDay,
                                );
            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                $Log->trace("END checkEmbossingOtherStores");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            $attendance = false;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                // 出勤時間と退勤時間が異なっている場合、出勤のシフトと判断
                if( $data['attendance'] != $data['taikin'] )
                {
                    $attendance = true;
                }
            }

            // シフト上、出勤ではない
            if( !$attendance )
            {
                $Log->trace("END checkEmbossingOtherStores");
                return "MSG_BASE_0008";
            }

            // シフト上、出勤である
            $Log->trace("END checkEmbossingOtherStores");
            return $ret;
        }
        
    }

?>
