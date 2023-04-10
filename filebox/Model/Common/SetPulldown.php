<?php
    /**
     * @file    プルダウン取得(Model)
     * @author  USE Y.Sakata
     * @date    2016/06/27
     * @version 1.00
     * @note    共通で使用するプルダウンモデルの処理を定義
     */

    // FwBaseControllerの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'Model/FwSetPulldown.php';
    require_once 'Model/Common/SecurityProcess.php';

    /**
     * プルダウン取得クラス
     * @note    共通で使用するプルダウンモデルの処理を定義
     */
    class SetPulldown extends FwSetPulldown
    {
        protected $securityProcess = null; ///< セキュリティクラス

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseModelのコンストラクタ
            parent::__construct();
            $this->securityProcess = new SecurityProcess();
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
         * 検索(支給単位)プルダウン
         * @param    $OrganList         表示対象の組織IDリスト
         * @return   支給単位リスト(支給単位ID/支給単位名) 
         */
        public function getSearchWageTypeList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchWageTypeList");

            $sql = 'SELECT payment_unit_id, payment_unit_name FROM public.m_payment_unit '
                . ' ORDER BY disp_order';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $wageTypeList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchWageTypeList");
                return $wageTypeList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($wageTypeList, $data);
            }
            $initial = array('payment_unit_id' => '', 'payment_unit_name' => '',);
            array_unshift($wageTypeList, $initial );

            $Log->trace("END getSearchWageTypeList");

            return $wageTypeList;
        }

        /**
         * 検索(支給条件)プルダウン
         * @param    $OrganList         表示対象の組織IDリスト
         * @return   支給条件リスト(支給条件ID/支給条件名) 
         */
        public function getSearchPaymentConditionsList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchPaymentConditionsList");

            $sql = 'SELECT payment_conditions_id, payment_conditions_name FROM public.m_payment_conditions '
                . ' ORDER BY disp_order';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $payCndList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchPaymentConditionsList");
                return $payCndList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($payCndList, $data);
            }

            $initial = array('payment_conditions_id' => '', 'payment_conditions_name' => '',);
            array_unshift($payCndList, $initial );

            $Log->trace("END getSearchPaymentConditionsList");

            return $payCndList;
        }

        /**
         * 検索(休日名称)プルダウン
         * @param    $authority     アクセス権限名
         * @return   休日名称リスト(休日名称ID/休日名称名) 
         */
        public function getSearchHolidayNameNameList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchHolidayNameNameList");

            $sql = 'SELECT holiday_name_id, organization_id, holiday_name FROM m_holiday_name '
                . ' WHERE is_del = :is_del ORDER BY disp_order, code';
            $parametersArray = array( ':is_del' => 0, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $holidayNameNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchHolidayNameNameList");
                return $holidayNameNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($holidayNameNameList, $data);
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDList( SystemParameters::$V_A_HOLIDAY_NAME, $authority );

            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $holidayNameNameList );

            $initial = array('holiday_name_id' => '', 'holiday_name' => '',);
            array_unshift($outList, $initial );

            $column = "holiday_name";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchHolidayNameNameList");

            return $outList;
        }

        /**
         * 検索(休日名)プルダウン
         * @param    $authority     アクセス権限名
         * @return   休日リスト(休日マスタID/休日名) 
         */
        public function getSearchHolidayNameList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchHolidayNameList");

            $sql = 'SELECT holiday_id, holiday, organization_id FROM m_holiday '
                . ' WHERE is_del = :is_del ORDER BY disp_order, code';
            $parametersArray = array( ':is_del' => 0, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $holidayNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchHolidayNameList");
                return $holidayNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($holidayNameList, $data);
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDList( SystemParameters::$V_A_HOLIDAY, $authority );

            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $holidayNameList );

            $initial = array('holiday_id' => '', 'holiday' => '',);
            array_unshift($outList, $initial );

            $column = "holiday";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchHolidayNameList");

            return $outList;
        }

        /**
         * 検索(手当)プルダウン
         * @param    $authority     アクセス権限名
         * @return   手当リスト(手当ID/手当名) 
         */
        public function getSearchAllowanceList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchAllowanceList");

            $sql = 'SELECT allowance_id, allowance_name, organization_id FROM m_allowance '
                . ' WHERE is_del = :is_del ORDER BY disp_order';
            $parametersArray = array( ':is_del' => 0, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $allowanceNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchAllowanceList");
                return $allowanceNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($allowanceNameList, $data);
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDList( SystemParameters::$V_A_ALLOWANCE, $authority );

            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $allowanceNameList );

            $initial = array('allowance_id' => '', 'allowance_name' => '',);
            array_unshift($outList, $initial );

            $column = "allowance_name";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchAllowanceList");

            return $outList;
        }

        /**
         * 検索(残業設定)プルダウン
         * @param    $authority     アクセス権限名
         * @return   残業設定リスト(残業設定種別ID/残業設定種別名) 
         */
        public function getSearchOvertimeSettingList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchOvertimeSettingList");

            $sql = 'SELECT overtime_setting_id, overtime_setting_name FROM public.m_overtime_setting '
                . ' ORDER BY disp_order';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $overtimeSetNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchOvertimeSettingList");
                return $overtimeSetNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($overtimeSetNameList, $data);
            }

            $initial = array('overtime_setting_id' => '', 'overtime_setting_name' => '',);
            array_unshift($overtimeSetNameList, $initial );

            $Log->trace("END getSearchOvertimeSettingList");

            return $overtimeSetNameList;
        }

        /**
         * 検索(法定内残業代)プルダウン
         * @param    $authority     アクセス権限名
         * @return   法定内残業代リスト(割増減額種別ID/割増減額種別名) 
         */
        public function getSearchLegalTimeInOvertimeList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchLegalTimeInOvertimeList");

            $sql = 'SELECT premium_reduction_type_id, premium_reduction_type_name FROM public.m_premium_reduction_type '
                . ' WHERE premium_reduction_type_id < 4 '
                . ' ORDER BY disp_order';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $legalInNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchLegalTimeInOvertimeList");
                return $legalInNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($legalInNameList, $data);
            }

            $initial = array('premium_reduction_type_id' => '', 'premium_reduction_type_name' => '',);
            array_unshift($legalInNameList, $initial );

            $Log->trace("END getSearchLegalTimeInOvertimeList");

            return $legalInNameList;
        }

        /**
         * 検索(法定外残業代)プルダウン
         * @param    $authority     アクセス権限名
         * @return   法定外残業代リスト(割増減額種別ID/割増減額種別名) 
         */
        public function getSearchLegalTimeOutOvertimeList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchLegalTimeOutOvertimeList");

            $sql = 'SELECT premium_reduction_type_id, premium_reduction_type_name FROM public.m_premium_reduction_type '
                . ' WHERE premium_reduction_type_id < 4 '
                . ' ORDER BY disp_order';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $legalOutNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchLegalTimeOutOvertimeList");
                return $legalOutNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($legalOutNameList, $data);
            }

            $initial = array('premium_reduction_type_id' => '', 'premium_reduction_type_name' => '',);
            array_unshift($legalOutNameList, $initial );

            $Log->trace("END getSearchLegalTimeOutOvertimeList");

            return $legalOutNameList;
        }

        /**
         * 検索(法定休日残業代)プルダウン
         * @param    $authority     アクセス権限名
         * @return   法定休日残業代リスト(割増減額種別ID/割増減額種別名) 
         */
        public function getSearchLegalHolidayAllowanceList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchLegalHolidayAllowanceList");

            $sql = 'SELECT premium_reduction_type_id, premium_reduction_type_name FROM public.m_premium_reduction_type '
                . ' WHERE premium_reduction_type_id < 4 '
                . ' ORDER BY disp_order';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $legHolidayNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchLegalHolidayAllowanceList");
                return $legHolidayNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($legHolidayNameList, $data);
            }

            $initial = array('premium_reduction_type_id' => '', 'premium_reduction_type_name' => '',);
            array_unshift($legHolidayNameList, $initial );

            $Log->trace("END getSearchLegalHolidayAllowanceList");

            return $legHolidayNameList;
        }

        /**
         * 検索(公休日残業代)プルダウン
         * @param    $authority     アクセス権限名
         * @return   公休日残業代リスト(割増減額種別ID/割増減額種別名) 
         */
        public function getSearchPrescribedHolidayList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchPrescribedHolidayList");

            $sql = 'SELECT premium_reduction_type_id, premium_reduction_type_name FROM public.m_premium_reduction_type '
                . ' WHERE premium_reduction_type_id < 4 '
                . ' ORDER BY disp_order';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $prescribedNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchPrescribedHolidayList");
                return $prescribedNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($prescribedNameList, $data);
            }

            $initial = array('premium_reduction_type_id' => '', 'premium_reduction_type_name' => '',);
            array_unshift($prescribedNameList, $initial );

            $Log->trace("END getSearchPrescribedHolidayList");

            return $prescribedNameList;
        }

        /**
         * 検索(みなし残業)プルダウン
         * @param    $authority     アクセス権限名
         * @return   みなし残業リスト(適用期間ID・就業規則ID/みなし残業) 
         */
        public function getSearchFixedOvertimeList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchFixedOvertimeList");

            $sql = 'SELECT application_date_id, labor_regulations_id, fixed_overtime FROM m_work_rules_allowance '
                . ' ORDER BY application_date_id, labor_regulations_id';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $fixedOverNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchFixedOvertimeList");
                return $fixedOverNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($fixedOverNameList, $data);
            }

            $initial = array('application_date_id' => '', 'labor_regulations_id' => '', 'fixed_overtime' => '',);
            array_unshift($fixedOverNameList, $initial );

            $outList = $fixedOverNameList;
            $column = "fixed_overtime";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchFixedOvertimeList");

            return $outList;
        }

        /**
         * 検索(深夜残業代)プルダウン
         * @param    $authority     アクセス権限名
         * @return   深夜残業代リスト(適用期間ID・就業規則ID/深夜残業代) 
         */
        public function getSearchLateAtNightOutOvertimeList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchLateAtNightOutOvertimeList");

            $sql = 'SELECT premium_reduction_type_id, premium_reduction_type_name FROM public.m_premium_reduction_type '
                . ' WHERE premium_reduction_type_id < 4 '
                . ' ORDER BY disp_order';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $lateAtNightNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchLateAtNightOutOvertimeList");
                return $lateAtNightNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($lateAtNightNameList, $data);
            }

            $initial = array('premium_reduction_type_id' => '', 'premium_reduction_type_name' => '',);
            array_unshift($lateAtNightNameList, $initial );

            $Log->trace("END getSearchLateAtNightOutOvertimeList");

            return $lateAtNightNameList;
        }

        
        /**
         * 検索(休憩時間の判定)プルダウン
         * @param    $authority     アクセス権限名
         * @return   休憩時間の判定リスト(適用期間ID・就業規則ID/休憩時間取得方法) 
         */
        public function getSearchBreakTimeAcquisitionList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchBreakTimeAcquisitionList");

            $sql = 'SELECT application_date_id, labor_regulations_id, break_time_acquisition FROM m_work_rules_time '
                . ' ORDER BY application_date_id, labor_regulations_id';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $breakTimeNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchBreakTimeAcquisitionList");
                return $breakTimeNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($breakTimeNameList, $data);
            }

            $initial = array('application_date_id' => '', 'labor_regulations_id' => '', 'break_time_acquisition' => '',);
            array_unshift($breakTimeNameList, $initial );

            $outList = $breakTimeNameList;
            $column = "break_time_acquisition";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchBreakTimeAcquisitionList");

            return $outList;
        }

        /**
         * アクセス可能な組織ID一覧取得
         * @note     セキュリティ設定用に、アクセス可能な組織IDの一覧を取得
         * @param    $urlKey        該当機能URL
         * @param    $authority     アクセス権限名
         * @return   上位組織ID一覧(section_id/section_name)
         */
        public function getAccessIDList( $urlKey, $authority )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAccessIDList");
            
            // アクセス可能な組織IDリストを作成
            $masterAccessAuthorityList = $this->securityProcess->getAccessAuthorityListForUrl( $urlKey );
            
            $organList = $this->securityProcess->getAccessDisplayOrderHierarchy( $masterAccessAuthorityList[$authority], $_SESSION["P_ACCESS_AUTHORITY_TABLE"][$urlKey] );

            $Log->trace("END getAccessIDList");
            
            return $organList;
        }
    }

?>
