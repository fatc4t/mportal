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
         * 検索(従業員マスタ従業員No)プルダウン
         * @param    $authority     アクセス権限名
         * @return   従業員リスト(従業員No) 
         */
        public function getSearchUserNoList( $authority = 'reference' )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchUserNoList");

            $sql = ' SELECT DISTINCT ud.user_detail_id,ud.employees_no, ud.organization_id, ud.user_name '
                 . ' FROM m_user u INNER JOIN m_user_detail ud ON u.user_id = ud.user_id '
                 . ' WHERE is_del = :is_del '
                 . ' ORDER BY ud.user_detail_id ';
            $parametersArray = array(':is_del' => 0, );
            $result = $DBA->executeSQL($sql, $parametersArray);
            
            $userList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchUserNoList");
                return $userList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($userList, $data);
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDList( SystemParameters::$V_M_USER, $authority );
            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $userList );
            
            $initial = array('employees_no' => '',);
            array_unshift($outList, $initial);

            $column = "employees_no";
            $outList = $this->getUniqueArray($outList, $column);

            $tmpArray = array();
            foreach ( $outList as $key => $row )
            {
                $tmpArray[$key] = $row['employees_no'];
            }
            array_multisort( $tmpArray, SORT_ASC, $outList );

            $Log->trace("END getSearchUserNoList");
            return $outList;
        }

        /**
         * 検索(顧客分類)プルダウン
         * @return   顧客分類リスト(顧客分類ID/顧客分類名) 
         */
        public function getSearchCustomerClassificationList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchCustomerClassList");

            $sql = 'SELECT cust_type_cd, cust_type_nm FROM m_customer_classification '
                . " WHERE disabled = '0' ORDER BY cust_type_cd";
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $customerClassificationList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchCustomerClassList");
                return $customerClassificationList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($customerClassificationList, $data);
            }
            $initial = array('cust_type_id' => '', 'cust_type_nm' => '',);
            array_unshift($customerClassificationList, $initial );

            $Log->trace("END getSearchCustomerClassList");

            return $customerClassificationList;
        }

        /**
         * 検索(地区)プルダウン
         * @return   地区リスト(地区ID/地区名) 
         */
        public function getSearchAreaList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchAreaList");

            $sql = 'SELECT area_cd, area_nm FROM m_area '
                . "  WHERE disabled = '0' ORDER BY area_cd";
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $areaList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchAreaList");
                return $areaList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($areaList, $data);
            }
            $initial = array('area_id' => '', 'area_nm' => '',);
            array_unshift($areaList, $initial );

            $Log->trace("END getSearchAreaList");

            return $areaList;
        }

        /**
         * 検索(顧客備考)プルダウン
         * @return   顧客備考リスト(顧客備考ID/顧客備考名) 
         */
//        public function getSearchCustomerRemarksList($custBType)
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchCustomerRemarksList");
//
//            $sql = 'SELECT cust_b_cd, cust_b_nm FROM m_customer_remarks '
//                . " WHERE disabled = '0' AND cust_b_type = :type ORDER BY cust_b_cd";
//            $parametersArray = array( ':type' => $custBType, );
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $customerRemarksList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchCustomerRemarksList");
//                return $customerRemarksList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($customerRemarksList, $data);
//            }
//            $initial = array('cust_b_id' => '', 'cust_b_nm' => '',);
//            array_unshift($customerRemarksList, $initial );
//
//            $Log->trace("END getSearchCustomerRemarksList");
//
//            return $customerRemarksList;
//        }

        /**
         * 検索(商品)プルダウン
         * @return   商品リスト(商品ID/商品名) 
         */
        public function getSearchProfitMenuList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchProfitMenuList");

            $sql = 'SELECT prod_cd, prod_nm FROM m_profit_menu '
                . "  WHERE disabled = '0' ORDER BY prod_cd";
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $profitMenuList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchProfitMenuList");
                return $profitMenuList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($profitMenuList, $data);
            }
            $initial = array('prod_id' => '', 'prod_nm' => '',);
            array_unshift($profitMenuList, $initial );

            $Log->trace("END getSearchProfitMenuList");

            return $profitMenuList;
        }

//
//        /**
//         * 検索(支給条件)プルダウン
//         * @param    $OrganList         表示対象の組織IDリスト
//         * @return   支給条件リスト(支給条件ID/支給条件名) 
//         */
//        public function getSearchPaymentConditionsList()
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchPaymentConditionsList");
//
//            $sql = 'SELECT payment_conditions_id, payment_conditions_name FROM public.m_payment_conditions '
//                . ' ORDER BY disp_order';
//            $parametersArray = array();
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $payCndList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchPaymentConditionsList");
//                return $payCndList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($payCndList, $data);
//            }
//
//            $initial = array('payment_conditions_id' => '', 'payment_conditions_name' => '',);
//            array_unshift($payCndList, $initial );
//
//            $Log->trace("END getSearchPaymentConditionsList");
//
//            return $payCndList;
//        }
//
//        /**
//         * 検索(休日名称)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   休日名称リスト(休日名称ID/休日名称名) 
//         */
//        public function getSearchHolidayNameNameList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchHolidayNameNameList");
//
//            $sql = 'SELECT holiday_name_id, organization_id, holiday_name FROM m_holiday_name '
//                . ' WHERE is_del = :is_del ORDER BY disp_order, code';
//            $parametersArray = array( ':is_del' => 0, );
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $holidayNameNameList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchHolidayNameNameList");
//                return $holidayNameNameList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($holidayNameNameList, $data);
//            }
//
//            // アクセス可能な組織IDリストを作成
//            $OrganList = $this->getAccessIDList( SystemParameters::$V_A_HOLIDAY_NAME, $authority );
//
//            $outList = array();
//            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $holidayNameNameList );
//
//            $initial = array('holiday_name_id' => '', 'holiday_name' => '',);
//            array_unshift($outList, $initial );
//
//            if( $authority == 'reference' )
//            {
//                $column = "holiday_name";
//                $outList = $this->getUniqueArray($outList, $column);
//            }
//
//            $Log->trace("END getSearchHolidayNameNameList");
//
//            return $outList;
//        }
//
//        /**
//         * 検索(休日名)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   休日リスト(休日マスタID/休日名) 
//         */
//        public function getSearchHolidayNameList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchHolidayNameList");
//
//            $sql = 'SELECT holiday_id, holiday, organization_id FROM m_holiday '
//                . ' WHERE is_del = :is_del ORDER BY disp_order, code';
//            $parametersArray = array( ':is_del' => 0, );
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $holidayNameList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchHolidayNameList");
//                return $holidayNameList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($holidayNameList, $data);
//            }
//
//            // アクセス可能な組織IDリストを作成
//            $OrganList = $this->getAccessIDList( SystemParameters::$V_A_HOLIDAY, $authority );
//
//            $outList = array();
//            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $holidayNameList );
//
//            $initial = array('holiday_id' => '', 'holiday' => '',);
//            array_unshift($outList, $initial );
//
//            $column = "holiday";
//            $outList = $this->getUniqueArray($outList, $column);
//
//            $Log->trace("END getSearchHolidayNameList");
//
//            return $outList;
//        }
//
//        /**
//         * 検索(手当)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   手当リスト(手当ID/手当名) 
//         */
//        public function getSearchAllowanceList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchAllowanceList");
//
//            $sql = 'SELECT allowance_id, allowance_name, organization_id FROM m_allowance '
//                . ' WHERE is_del = :is_del ORDER BY disp_order';
//            $parametersArray = array( ':is_del' => 0, );
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $allowanceNameList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchAllowanceList");
//                return $allowanceNameList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($allowanceNameList, $data);
//            }
//
//            // アクセス可能な組織IDリストを作成
//            $OrganList = $this->getAccessIDList( SystemParameters::$V_A_ALLOWANCE, $authority );
//
//            $outList = array();
//            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $allowanceNameList );
//
//            $initial = array('allowance_id' => '', 'allowance_name' => '',);
//            array_unshift($outList, $initial );
//
//            $column = "allowance_name";
//            $outList = $this->getUniqueArray($outList, $column);
//
//            $Log->trace("END getSearchAllowanceList");
//
//            return $outList;
//        }
//
//        /**
//         * 検索(残業設定)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   残業設定リスト(残業設定種別ID/残業設定種別名) 
//         */
//        public function getSearchOvertimeSettingList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchOvertimeSettingList");
//
//            $sql = 'SELECT overtime_setting_id, overtime_setting_name FROM public.m_overtime_setting '
//                . ' ORDER BY disp_order';
//            $parametersArray = array();
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $overtimeSetNameList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchOvertimeSettingList");
//                return $overtimeSetNameList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($overtimeSetNameList, $data);
//            }
//
//            $initial = array('overtime_setting_id' => '', 'overtime_setting_name' => '',);
//            array_unshift($overtimeSetNameList, $initial );
//
//            $Log->trace("END getSearchOvertimeSettingList");
//
//            return $overtimeSetNameList;
//        }
//
//        /**
//         * 検索(法定内残業代)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   法定内残業代リスト(割増減額種別ID/割増減額種別名) 
//         */
//        public function getSearchLegalTimeInOvertimeList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchLegalTimeInOvertimeList");
//
//            $sql = 'SELECT premium_reduction_type_id, premium_reduction_type_name FROM public.m_premium_reduction_type '
//                . ' WHERE premium_reduction_type_id < 4 '
//                . ' ORDER BY disp_order';
//            $parametersArray = array();
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $legalInNameList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchLegalTimeInOvertimeList");
//                return $legalInNameList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($legalInNameList, $data);
//            }
//
//            $initial = array('premium_reduction_type_id' => '', 'premium_reduction_type_name' => '',);
//            array_unshift($legalInNameList, $initial );
//
//            $Log->trace("END getSearchLegalTimeInOvertimeList");
//
//            return $legalInNameList;
//        }
//
//        /**
//         * 検索(法定外残業代)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   法定外残業代リスト(割増減額種別ID/割増減額種別名) 
//         */
//        public function getSearchLegalTimeOutOvertimeList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchLegalTimeOutOvertimeList");
//
//            $sql = 'SELECT premium_reduction_type_id, premium_reduction_type_name FROM public.m_premium_reduction_type '
//                . ' WHERE premium_reduction_type_id < 4 '
//                . ' ORDER BY disp_order';
//            $parametersArray = array();
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $legalOutNameList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchLegalTimeOutOvertimeList");
//                return $legalOutNameList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($legalOutNameList, $data);
//            }
//
//            $initial = array('premium_reduction_type_id' => '', 'premium_reduction_type_name' => '',);
//            array_unshift($legalOutNameList, $initial );
//
//            $Log->trace("END getSearchLegalTimeOutOvertimeList");
//
//            return $legalOutNameList;
//        }
//
//        /**
//         * 検索(法定休日残業代)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   法定休日残業代リスト(割増減額種別ID/割増減額種別名) 
//         */
//        public function getSearchLegalHolidayAllowanceList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchLegalHolidayAllowanceList");
//
//            $sql = 'SELECT premium_reduction_type_id, premium_reduction_type_name FROM public.m_premium_reduction_type '
//                . ' WHERE premium_reduction_type_id < 4 '
//                . ' ORDER BY disp_order';
//            $parametersArray = array();
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $legHolidayNameList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchLegalHolidayAllowanceList");
//                return $legHolidayNameList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($legHolidayNameList, $data);
//            }
//
//            $initial = array('premium_reduction_type_id' => '', 'premium_reduction_type_name' => '',);
//            array_unshift($legHolidayNameList, $initial );
//
//            $Log->trace("END getSearchLegalHolidayAllowanceList");
//
//            return $legHolidayNameList;
//        }
//
//        /**
//         * 検索(公休日残業代)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   公休日残業代リスト(割増減額種別ID/割増減額種別名) 
//         */
//        public function getSearchPrescribedHolidayList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchPrescribedHolidayList");
//
//            $sql = 'SELECT premium_reduction_type_id, premium_reduction_type_name FROM public.m_premium_reduction_type '
//                . ' WHERE premium_reduction_type_id < 4 '
//                . ' ORDER BY disp_order';
//            $parametersArray = array();
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $prescribedNameList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchPrescribedHolidayList");
//                return $prescribedNameList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($prescribedNameList, $data);
//            }
//
//            $initial = array('premium_reduction_type_id' => '', 'premium_reduction_type_name' => '',);
//            array_unshift($prescribedNameList, $initial );
//
//            $Log->trace("END getSearchPrescribedHolidayList");
//
//            return $prescribedNameList;
//        }
//
//        /**
//         * 検索(深夜残業代)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   深夜残業代リスト(適用期間ID・就業規則ID/深夜残業代) 
//         */
//        public function getSearchLateAtNightOutOvertimeList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchLateAtNightOutOvertimeList");
//
//            $sql = 'SELECT premium_reduction_type_id, premium_reduction_type_name FROM public.m_premium_reduction_type '
//                . ' WHERE premium_reduction_type_id < 4 '
//                . ' ORDER BY disp_order';
//            $parametersArray = array();
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $lateAtNightNameList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchLateAtNightOutOvertimeList");
//                return $lateAtNightNameList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($lateAtNightNameList, $data);
//            }
//
//            $initial = array('premium_reduction_type_id' => '', 'premium_reduction_type_name' => '',);
//            array_unshift($lateAtNightNameList, $initial );
//
//            $Log->trace("END getSearchLateAtNightOutOvertimeList");
//
//            return $lateAtNightNameList;
//        }
//
//        
//        /**
//         * 登録(時間設定)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   時間設定リスト(時間種別ID/時間種別名) 
//         */
//        public function getSearchTimeTypeList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchTimeTypeList");
//
//            $sql = 'SELECT time_type_id, time_type_name FROM public.m_time_type '
//                . ' ORDER BY disp_order';
//            $parametersArray = array();
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $timeTypeNameList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchTimeTypeList");
//                return $timeTypeNameList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($timeTypeNameList, $data);
//            }
//
//            $outList = $timeTypeNameList;
//            $column = "time_type_name";
//            $outList = $this->getUniqueArray($outList, $column);
//
//            $Log->trace("END getSearchTimeTypeList");
//
//            return $outList;
//        }
//        
//        /**
//         * 登録(早出残業認定種別)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   早出残業認定種別リスト(早出残業認定種別ID/早出残業認定種別名) 
//         */
//        public function getSearchEarlyShiftOvertimeList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchEarlyShiftOvertimeList");
//
//            $sql = 'SELECT early_shift_overtime_id, early_shift_overtime_name FROM public.m_early_shift_overtime '
//                . ' ORDER BY disp_order';
//            $parametersArray = array();
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $earlyShiftNameList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchEarlyShiftOvertimeList");
//                return $earlyShiftNameList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($earlyShiftNameList, $data);
//            }
//
//            $outList = $earlyShiftNameList;
//            $column = "early_shift_overtime_name";
//            $outList = $this->getUniqueArray($outList, $column);
//
//            $Log->trace("END getSearchEarlyShiftOvertimeList");
//
//            return $outList;
//        }
//        
//        /**
//         * 登録(週締日種別)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   週締日種別リスト(週締日種別ID/週締日種別名) 
//         */
//        public function getSearchWeekCutoffDateList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchWeekCutoffDateList");
//
//            $sql = 'SELECT week_cutoff_date_id, week_cutoff_date_name FROM public.m_week_cutoff_date '
//                . ' ORDER BY disp_order';
//            $parametersArray = array();
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $weekCutDateNameList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchWeekCutoffDateList");
//                return $weekCutDateNameList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($weekCutDateNameList, $data);
//            }
//            
//            $initial = array('week_cutoff_date_id' => '', 'week_cutoff_date_name' => '',);
//            array_unshift($weekCutDateNameList, $initial );
//
//            $outList = $weekCutDateNameList;
//            $column = "week_cutoff_date_name";
//            $outList = $this->getUniqueArray($outList, $column);
//
//            $Log->trace("END getSearchWeekCutoffDateList");
//
//            return $outList;
//        }
//        
//        /**
//         * 登録(設定時間帯手当代)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   設定時間帯手当代リスト(割増減額種別ID/割増減額種別名) 
//         */
//        public function getSearchSettingTimeAllowanceList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchSettingTimeAllowanceList");
//
//            $sql = 'SELECT premium_reduction_type_id, premium_reduction_type_name FROM public.m_premium_reduction_type '
//                . ' ORDER BY premium_reduction_type_id';
//            $parametersArray = array();
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $settingTimeNameList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchSettingTimeAllowanceList");
//                return $settingTimeNameList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($settingTimeNameList, $data);
//            }
//
//            $initial = array('premium_reduction_type_id' => '', 'premium_reduction_type_name' => '',);
//            array_unshift($settingTimeNameList, $initial );
//            
//            $outList = $settingTimeNameList;
//            $column = "premium_reduction_type_name";
//            $outList = $this->getUniqueArray($outList, $column);
//
//            $Log->trace("END getSearchSettingTimeAllowanceList");
//
//            return $outList;
//        }
//        
//        /**
//         * 登録(割増手当名)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   割増手当リスト(手当ID/手当名) 
//         */
//        public function getSearchAdditionalAllowanceList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchAdditionalAllowanceList");
//
//            $sql = 'SELECT allowance_id, organization_id, allowance_name, payment_conditions_id FROM m_allowance '
//                . ' WHERE payment_conditions_id = 5 '
//                . ' ORDER BY allowance_id ';
//            $parametersArray = array();
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $addAllowanceList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchAdditionalAllowanceList");
//                return $addAllowanceList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($addAllowanceList, $data);
//            }
//
//            // アクセス可能な組織IDリストを作成
//            $OrganList = $this->getAccessIDList( SystemParameters::$V_A_ALLOWANCE, $authority );
//
//            $outList = array();
//            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $addAllowanceList );
//
//            $initial = array('allowance_id' => '', 'allowance_name' => '',);
//            array_unshift($outList, $initial );
//
//            $Log->trace("END getSearchAllowanceList");
//
//            return $outList;
//        }
//        
//        /**
//         * 登録(手動手当)プルダウン
//         * @param    $authority     アクセス権限名
//         * @return   手動手当リスト(手当ID/手当名) 
//         */
//        public function getSearchManualAllowanceList( $authority = 'reference' )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getSearchManualAllowanceList");
//
//            $sql = 'SELECT allowance_id, organization_id, allowance_name, payment_conditions_id FROM m_allowance '
//                . ' WHERE payment_conditions_id = 4 AND is_del = 0 '
//                . ' ORDER BY allowance_id ';
//            $parametersArray = array();
//
//            $result = $DBA->executeSQL($sql, $parametersArray);
//
//            $manualAllowanceList = array();
//            
//            if( $result === false )
//            {
//                $Log->trace("END getSearchManualAllowanceList");
//                return $manualAllowanceList;
//            }
//            
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push($manualAllowanceList, $data);
//            }
//
//            // アクセス可能な組織IDリストを作成
//            $OrganList = $this->getAccessIDList( SystemParameters::$V_A_ALLOWANCE, $authority );
//
//            $outList = array();
//            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $manualAllowanceList );
//
//            $column = "allowance_name";
//            $outList = $this->getUniqueArray($outList, $column);
//
//            $Log->trace("END getSearchManualAllowanceList");
//
//            return $outList;
//        }
//
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
            
            $organList = $this->securityProcess->getAccessDisplayOrderHierarchy( $masterAccessAuthorityList[$authority], $_SESSION["A_ACCESS_AUTHORITY_TABLE"][$urlKey] );

            $Log->trace("END getAccessIDList");
            
            return $organList;
        }
    }

?>
