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
         * 賃金形態プルダウン
         * @return   賃金形態リスト(賃金形態ID/賃金形態名) 
         */
        public function getSearchWageFormList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchWageFormList List");

            $sql = 'SELECT wage_form_id, wage_form_name FROM public.m_wage_form ORDER BY disp_order';

            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $wageFormList = array();

            if( $result === false )
            {
                $Log->trace("END getSearchWageFormList");
                return $wageFormList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($wageFormList, $data);
            }

            $initial = array('wage_form_id' => '', 'wage_form_name' => '',);
            array_unshift($wageFormList, $initial );

            $Log->trace("END getSearchWageFormList");

            return $wageFormList;
        }

        /**
         * 従業員マスタの検索用従業員Noのプルダウン
         * @param    $authority     アクセス権限名
         * @return   従業員リスト(従業員No) 
         */
        public function getSearchUserNoList( $authority = 'reference' )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchUserNoList");

            $sql = ' SELECT DISTINCT ud.user_detail_id,ud.employees_no, ud.organization_id '
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
         * 検索(セキュリティ)プルダウン
         * @param    $authority     アクセス権限名
         * @return   セキュリティリスト(セキュリティID/セキュリティ名) 
         */
        public function getSearchSecurityList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchSecurityList");

            $sql = 'SELECT security_id, security_name, organization_id FROM m_security ';
            if( $authority === 'registration' )
            {
                $sql .= ' WHERE is_del = 0 ';
            }
            $sql .= ' ORDER BY disp_order';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $securityNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchSecurityList");
                return $securityNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($securityNameList, $data);
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDList( SystemParameters::$V_M_SECURITY, $authority );

            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $securityNameList );

            $initial = array('security_id' => '', 'security_name' => '',);
            array_unshift($outList, $initial );

            $column = "security_name";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchSecurityList");

            return $outList;
        }

        /**
         * 検索(給与フォーマット)プルダウン
         * @param    $authority     アクセス権限名
         * @return   給与フォーマットリスト(給与システムID、設定名) 
         */
        public function getSearchPayrollList( $authority = 'reference' )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchPayrollList");

            $sql = ' SELECT payroll_system_id,organization_id,name '
                 . ' FROM m_payroll_system_cooperation ';
            $sql .= ' WHERE is_del = 0 ';

            $sql .= ' ORDER BY organization_id, payroll_system_id';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $payrollList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchPayrollList");
                return $payrollList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($payrollList, $data);
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDList( SystemParameters::$V_A_PAYROLL_SYSTEM, $authority );
            // アクセス可能な組織IDは、適用中のみとする
            $OrganList = $this->getOrganList( $OrganList );
            
            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $payrollList );

            $initial = array('payroll_system_id' => '', 'name' => '',);
            array_unshift($outList, $initial);

            $Log->trace("END getSearchPayrollList");
            return $outList;
        }

        /**
         * 登録(就業規則優先順位)プルダウン
         * @param    $authority     アクセス権限名
         * @return   給与フォーマットリスト(組織詳細ID、優先順位_組織、優先順位_役職、優先順位_雇用形態) 
         */
        public function getSearchPriorityList( $authority = 'reference' )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchPriorityList");

            $sql = ' SELECT organization_detail_id,organization_id,priority_o,priority_p,priority_e FROM m_organization_detail '
                 . ' ORDER BY organization_detail_id, organization_id';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $priorityList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchPriorityList");
                return $priorityList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($priorityList, $data);
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDList( SystemParameters::$V_M_ORGANIZATION, $authority );
            // アクセス可能な組織IDは、適用中のみとする
            $OrganList = $this->getOrganList( $OrganList );

            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $priorityList );

            $initial = array('priority_o' => '', 'priority_p' => '','priority_e' => '',);
            array_unshift($outList, $initial);

            $Log->trace("END getSearchPriorityList");
            return $outList;
        }

        /**
         * 組織に紐づく登録用プルダウンリスト取得
         * @note    登録組織に紐づく選択肢のみ取得するプルダウンを作成する
         * @param   $target_id(対象組織ID)
         * @param   $columnId(項目ID)
         * @param   $columnName(項目名)
         * @param   $tableName(検索対象テーブル名)
         * @return  SQLの実行結果
         */
        public function getRegPulldownList($target_id, $columnId = "", $columnName = "",  $tableName = "" )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getRegPulldownList");

            $sql = ' SELECT ';
            $sql .= $columnId;
            $sql .= ' , ';
            $sql .= $columnName;
            $sql .= ' FROM ';
            $sql .= $tableName; 
            $sql .= ' WHERE organization_id = :organization_id AND is_del = :is_del ORDER BY disp_order ';
            $parametersArray = array(
                ':organization_id' => $target_id,
                ':is_del'          => 0, 
            );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $pulldownList = array();

            if( $result === false )
            {
                $Log->trace("END getrRegPositionList");
                return $pulldownList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($pulldownList, $data);
            }

            $initial = array($columnId => '', $columnName => '',);
            array_unshift($pulldownList, $initial);

            $Log->trace("END getRegPulldownList");

            return $pulldownList;
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
            $mAccessList = $this->securityProcess->getAccessAuthorityListForUrl( $urlKey );
            
            $organList = $this->securityProcess->getAccessDisplayOrderHierarchy( $mAccessList[$authority], $_SESSION["M_ACCESS_AUTHORITY_TABLE"][$urlKey] );

            $Log->trace("END getAccessIDList");
            
            return $organList;
        }
        
        /**
         * アクセス可能な組織ID一覧取得(適用中のみとする)
         * @note     セキュリティ設定用に、アクセス可能な組織IDの一覧を取得
         * @param    $organList     アクセス可能組織IDリスト
         * @return   アクセス可能な組織ID一覧(適用中のみ)
         */
        protected function getOrganList( $organList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getOrganList");
            
            $ret = array();
            // アクセス可能な組織IDリストを作成
            foreach($organList as $organ)
            {
                if( '適用中' === $organ['eff_code'] && ( false === array_search( $organ, $ret ) ) )
                {
                    array_push( $ret, $organ );
                }
            }

            $Log->trace("END getOrganList");
            
            return $ret;
        }
    }

?>
