<?php
    /**
     * @file      アラートチェックモデル
     * @author    USE Y.Sakata
     * @date      2016/10/19
     * @version   1.00
     * @note      アラートチェックを行う
     */

    // BaseModel.phpを読み込む
    require_once './Model/Common/BaseModel.php';

    /**
     * アラートチェッククラス
     * @note   アラートチェックを行う
     */
    class CheckAlert extends BaseModel
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
         * アラートに必要な就業規則を取得する
         * @param    $userID               ユーザID
         * @param    $laborRegulationsID   就業規則ID
         * @param    $targetDate           対象日
         * @return   SQLの実行結果
         */
        public function getLaborRegulationsAlertInfo( $userID, $laborRegulationsID, $targetDate )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getLaborRegulationsAlertInfo");

            // 就業規則IDが存在するか
            if( empty( $laborRegulationsID ) )
            {
                // ユーザ情報を取得
                $userInfo = $this->getUserInfo( $userID, $targetDate );
                // 就業規則情報を取得
                $employInfo = $this->getUserEmploymentInfo($userInfo);
                $laborRegulationsID = $employInfo['labor_regulations_id'];
            }

            // 就業規則IDから、就業規則適用期間IDを取得
            $appDateID = $this->getApplicationDateID( $laborRegulationsID, $targetDate );

            // 就業規則アラート情報を取得
            $ret['alert_info'] = $this->getAlertInfo( $laborRegulationsID, $appDateID );
            // 労働基準法情報を取得
            $ret['labor_standards_act'] = $this->getLaborStandardsAct( $targetDate );
            
            $Log->trace("END   getLaborRegulationsAlertInfo");

            return $ret;
        }

        /**
         * 出勤日数を取得する
         * @param    $userID               ユーザID
         * @param    $sDate                取得開始日
         * @param    $eDate                取得終了日
         * @return   出勤日数
         */
        public function getNumberAttendances( $userID, $sDate, $eDate )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getNumberAttendances");
            
            // 勤怠テーブルから、出勤日数を取得
            $sql  = " SELECT DISTINCT date FROM  t_attendance "
                  . " WHERE is_del = 0 AND attendance_time IS NOT NULL AND  "
                  . " user_id = :user_id AND :sDate <= date AND date < :eDate "
                  . " ORDER BY date ";

            $parameters = array( 
                                    ':user_id'    => $userID, 
                                    ':sDate'      => $sDate,
                                    ':eDate'      => $eDate,
                               );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = 0;
            if( $result === false )
            {
                $Log->trace("END getNumberAttendances");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret++;
            }

            $Log->trace("END getNumberAttendances");
            return $ret;
        }

        /**
         * シフト出勤日数を取得する
         * @param    $userID               ユーザID
         * @param    $sDate                取得開始日
         * @param    $eDate                取得終了日
         * @return   出勤日数
         */
        public function getShiftNumberAttendances( $userID, $sDate, $eDate )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getShiftNumberAttendances");
            
            // 勤怠テーブルから、出勤日数を取得
            $sql  = " SELECT DISTINCT day as date FROM  t_shift "
                  . " WHERE attendance IS NOT NULL AND  "
                  . " user_id = :user_id AND :sDate <= day AND day < :eDate "
                  . " ORDER BY day ";

            $parameters = array( 
                                    ':user_id'    => $userID, 
                                    ':sDate'      => $sDate,
                                    ':eDate'      => $eDate,
                               );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = 0;
            if( $result === false )
            {
                $Log->trace("END getShiftNumberAttendances");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret++;
            }

            $Log->trace("END getShiftNumberAttendances");
            return $ret;
        }

        /**
         * アラートに必要な就業規則を取得する
         * @param    $userID               ユーザID
         * @param    $laborRegulationsID   就業規則ID
         * @param    $targetDate           対象日
         * @return   SQLの実行結果
         */
        public function getLaborRegulationsID( $userID, $laborRegulationsID, $targetDate )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getLaborRegulationsID");

            // 就業規則IDが存在するか
            if( empty( $laborRegulationsID ) )
            {
                // ユーザ情報を取得
                $userInfo = $this->getUserInfo( $userID, $targetDate );
                // 就業規則情報を取得
                $employInfo = $this->getUserEmploymentInfo($userInfo);
                $laborRegulationsID = $employInfo['labor_regulations_id'];
            }
            
            $Log->trace("END   getLaborRegulationsID");

            return $laborRegulationsID;
        }

        /**
         * アラート表示する組織名を取得する
         * @param    $organizationID   組織ID
         * @param    $date             該当日
         * @return   組織名(略称名)
         */
        public function getOrganizationName( $organizationID, $date )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getOrganizationName");
            
            // 組織IDから、組織名を取得
            $sql  = " SELECT abbreviated_name FROM  v_organization "
                  . " WHERE organization_id = :organization_id AND application_date_start <= :application_date_start "
                  . " ORDER BY application_date_start DESC offset 0 limit 1 ";

            $parameters = array( 
                                    ':organization_id'         => $organizationID, 
                                    ':application_date_start'  => $date,
                               );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = '';
            if( $result === false )
            {
                $Log->trace("END getOrganizationName");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data['abbreviated_name'];
            }

            $Log->trace("END getOrganizationName");
            return $ret;
        }

        /**
         * アラート送信する従業員を取得する
         * @param    $organizationID   組織ID
         * @return   承認権限がある、従業員のメールアドレス
         */
        public function getUserMailList( $organizationID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserMailList");
            
            // 組織IDから、メールアドレスを取得
            $sql  = " SELECT vu.mail_address FROM   m_approval_organization mao INNER JOIN v_user vu ON "
                  . "        mao.user_detail_id = vu.user_detail_id AND vu.eff_code = '適用中' AND vu.status = '在籍' "
                  . " WHERE  mao.organization_id = :organization_id ORDER BY vu.employees_no ";

            $parameters = array( ':organization_id' => $organizationID, );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getUserMailList");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $ret, $data['mail_address'] );
            }

            $Log->trace("END getUserMailList");
            return $ret;
        }

        /**
         * アラート送信する従業員を取得する(上位組織の従業員情報)
         * @param    $organizationID   組織ID
         * @return   承認権限がある、従業員のメールアドレス
         */
        public function getUserMailListForOrg( $organizationID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserMailListForOrg");
            
            // 自組織から組織のTopまでのリストを取得
            $organizationIdList = $this->securityProcess->getTopOrganizationIDForOrg( $organizationID );

            // 組織IDから、メールアドレスを取得
            $sql  = " SELECT  vu.mail_address "
                  . " FROM    v_user vu  INNER JOIN m_security_detail msd ON vu.security_id = msd.security_id AND msd.access_authority_id = 513 "
                  . "         AND vu.eff_code = '適用中' AND vu.status = '在籍'  "
                  . " WHERE   vu.organization_id = :organization_id AND msd.approval < :approval ORDER BY vu.employees_no ";

            $ret = array();
            foreach( $organizationIdList as $value )
            {
                $approval = 4;  // 配下組織以上を指定
                // 自組織の場合、承認権限は、自組織も含める
                if( $organizationID === $value )
                {
                    $approval = 5;
                }
                
                $parameters = array( 
                                     ':organization_id' => $value, 
                                     ':approval' => $approval, 
                                   );
                $result = $DBA->executeSQL($sql, $parameters);

                if( $result === false )
                {
                    $Log->trace("END getUserMailListForOrg");
                    return $ret;
                }

                while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
                {
                    array_push( $ret, $data['mail_address'] );
                }
            }

            $Log->trace("END getUserMailListForOrg");

            return $ret;
        }

        /**
         * アラート情報を取得する
         * @param    $laborRegulationsID   就業規則ID
         * @param    $appDateID            就業規則適用期間マスタID
         * @return   アラート情報
         */
        protected function getAlertInfo( $laborRegulationsID, $appDateID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAlertInfo");
            
            // 就業規則IDから、就業規則適用期間IDを取得
            $sql  = " SELECT mad.is_overtime_alert, mad.overtime_alert_value, mad.holiday_settings, mad.e_mail, "
                  . "        mwrt.mod_break_time, mwrt.balance_payments, mwrt.month_tightening"
                  . " FROM m_application_date mad INNER JOIN m_work_rules_time mwrt ON "
                  . "      mad.labor_regulations_id = mwrt.labor_regulations_id AND mad.application_date_id = mwrt.application_date_id "
                  . " WHERE mad.application_date_id = :application_date_id AND mad.labor_regulations_id = :labor_regulations_id  ";

            $parameters = array( 
                                    ':labor_regulations_id'    => $laborRegulationsID, 
                                    ':application_date_id'     => $appDateID,
                               );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getAlertInfo");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data;
            }

            $Log->trace("END getAlertInfo");
            return $ret;
        }

        /**
         * 労働基準法情報を取得する
         * @param    $targetDate   対象日
         * @return   アラート情報
         */
        protected function getLaborStandardsAct( $targetDate )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getLaborStandardsAct");
            
            // 就業規則IDから、就業規則適用期間IDを取得
            $sql  = " SELECT holiday_dates_1week, holiday_dates_4week, break_time_6_hours_work_time, break_time_8_hours_work_time "
                  . " FROM public.m_labor_standards_act  "
                  . " WHERE application_date <= :application_date "
                  . " ORDER BY application_date DESC offset 0 limit 1 ";

            $parameters = array( ':application_date'    => $targetDate, );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getLaborStandardsAct");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data;
            }

            $Log->trace("END getLaborStandardsAct");
            return $ret;
        }

    }
?>
