<?php
    /**
     * @file    FW共通プルダウン取得(Model)
     * @author  USE Y.Sakata
     * @date    2016/07/03
     * @version 1.00
     * @note    共通で使用するプルダウンモデルの処理を定義
     */

    // FwBaseControllerの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwBaseModel.php';
    require_once 'Model/Common/SecurityProcess.php';

    /**
     * プルダウン取得クラス
     * @note    共通で使用するプルダウンモデルの処理を定義
     */
    class FwSetPulldown extends FwBaseModel
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
         * 検索(組織)プルダウン
         * @param    $authority     アクセス権限名
         * @param    $isLevelView   階層表示
         * @param    $isViewPlans   適用予定の表示
         * @return   組織リスト(組織ID/組織略称) 
         */
        public function getSearchOrganizationList( $authority = 'reference', $isLevelView = false, $isViewPlans = false )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchOrganizationList List");

            $sql = 'SELECT org.organization_id , mod.abbreviated_name, mod.upper_level_organization'
                . ' FROM m_organization_detail mod'
                . ' INNER JOIN m_organization mo ON mod.organization_id = mo.organization_id'
                . ' , (SELECT od.organization_id '
                . ' , max(od.application_date_start) as application_date_start'
                . ' FROM m_organization_detail od';
            if( $authority === 'registration' )
            {
                $sql .= ' INNER JOIN m_organization o ON o.organization_id = od.organization_id ';
            }
            $sql .= ' WHERE od.application_date_start <= current_date';
            if( $authority === 'registration' )
            {
                $sql .= ' AND o.is_del = 0 ';
            }
            $sql .= ' GROUP BY od.organization_id ORDER BY od.organization_id ) org'
                  . ' WHERE mod.organization_id = org.organization_id'
                  . ' AND mod.application_date_start = org.application_date_start';
            if( $authority === 'registration' )
            {
                $sql .= ' AND mo.is_del = 0 ';
            }
            $sql .= ' ORDER BY mo.disp_order, mod.department_code';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);
            
            $organizationList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchOrganizationList");
                return $organizationList;
            }

            $useOrganIdList = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
               $organization = array(
                   'organization_id'  => $data['organization_id'],                   // 組織ID
                   'abbreviated_name' => $data['abbreviated_name'],                  // 略称名
                   'upper_level_organization' => $data['upper_level_organization'],  // 上位組織ID
               );
               array_push($organizationList, $organization);
               array_push($useOrganIdList, $data['organization_id']);
            }

            // 適用予定の組織も必要か
            if( $isViewPlans )
            {
                $sql = " SELECT organization_id, abbreviated_name, upper_level_organization "
                     . " FROM v_organization WHERE eff_code = '適用予定' ";
                $result = $DBA->executeSQL($sql, $parametersArray);
                if( $result === false )
                {
                    $organizationList = array();
                    $Log->trace("END getSearchOrganizationList");
                    return $organizationList;
                }
                
                while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
                {
                   $organization = array(
                       'organization_id'  => $data['organization_id'],                   // 組織ID
                       'abbreviated_name' => $data['abbreviated_name'],                  // 略称名
                       'upper_level_organization' => $data['upper_level_organization'],  // 上位組織ID
                   );
                   
                   if( false == in_array( $data['organization_id'], $useOrganIdList ) )
                   {
                        array_push($organizationList, $organization);
                        array_push($useOrganIdList, $data['organization_id']);
                   }
                }
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDList( SystemParameters::$V_M_ORGANIZATION, $authority );

            // アクセス可能な組織IDリスト作成時、組織マスタのみ、適用中以外も混じるため、削除する
            $aOrganList = array();
            $aOrganIdList = array();
            foreach ( $OrganList as $value )
            {
                if( ( $value['eff_code'] == '適用中' && false == in_array( $value['organization_id'], $aOrganIdList ) ) ||
                    ( $value['eff_code'] == '適用予定' && false == in_array( $value['organization_id'], $aOrganIdList ) ) )
                {
                    array_push( $aOrganList, $value );
                    array_push( $aOrganIdList, $value['organization_id'] );
                }
            }

            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $aOrganList, $organizationList );

            $initial = array('organization_id' => '', 'abbreviated_name' => '',);
            array_unshift($outList, $initial );

            // 階層表示する
            if( $isLevelView )
            {
                // 配列の総数設定
                $maxCnt = count( $outList );
                // 各組織の階層を設定する
                for( $i=1; $i < $maxCnt; $i++ )
                {
                    // 現データが最上位である
                    if( 0 == $outList[$i]['upper_level_organization'] )
                    {
                        $outList[$i]['level'] = 0;
                        $outList[$i]['isSameParent'] = false;
                        $outList[$i]['upperLvList'] = array( $i );
                        $level = 1;
                    }
                    else
                    {
                        if( $outList[$i]['upper_level_organization'] == $outList[$i-1]['organization_id'] )
                        {
                            // 1階層ダウン
                            $level++;
                            $outList[$i]['level'] = $level;
                            $outList[$i]['upperLvList'] = $outList[$i-1]['upperLvList'];
                            array_push( $outList[$i]['upperLvList'], $i );
                        }
                        else
                        {
                            // 現行の階層を再計算する
                            for( $j=$i-1; $j > 0; $j-- )
                            {
                                if( $outList[$i]['upper_level_organization'] == $outList[$j]['upper_level_organization'] )
                                {
                                    $level = $outList[$j]['level'];
                                    $outList[$i]['level'] = $level;
                                    $outList[$i]['upperLvList'] = $outList[$j]['upperLvList'];
                                    // 自分と同列の組織ID(最後)を削除
                                    array_pop($outList[$i]['upperLvList']);
                                    array_push( $outList[$i]['upperLvList'], $i );
                                    break;
                                }
                            }
                        }
                        
                        // 自分と同一の上位IDをもつ組織が存在するか
                        $outList[$i]['isSameParent'] = false;
                        for( $j=$i+1; $j < $maxCnt; $j++ )
                        {
                            if( $outList[$i]['upper_level_organization'] == $outList[$j]['upper_level_organization'] )
                            {
                                $outList[$i]['isSameParent'] = true;
                                break;
                            }
                        }
                    }
                }
                
                // 出力設定
                for( $i=1; $i < $maxCnt; $i++ )
                {
                    $line = "";
                    $upperLvCnt = count( $outList[$i]['upperLvList'] );
                    // 階層分、空白を入れる
                    for( $j=1; $j < $upperLvCnt-1; $j++ )
                    {
                        if( $outList[$outList[$i]['upperLvList'][$j]]['isSameParent'] )
                        {
                            $line .= "｜";
                        }
                        $line .= "　";
                    }
                    
                    if( $outList[$i]['isSameParent'] )
                    {
                        $line .= "├";
                    }
                    else if( $outList[$i]['upper_level_organization'] != 0 )
                    {
                        $line .= "└";
                    }
                    
                    $outList[$i]['abbreviated_name'] = $line . $outList[$i]['abbreviated_name'];
                }
            }

            $Log->trace("END getSearchOrganizationList");
            return $outList;
        }

        /**
         * 検索(組織)プルダウン ※アクセス権に関わらずに全てのリストを作成する
         * @param    $isLevelView   階層表示
         * @param    $isViewPlans   適用予定の表示
         * @return   組織リスト(組織ID/組織略称) 
         */
        public function getSearchOrganizationAllList( $authority = 'reference', $isLevelView = false, $isViewPlans = false )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchOrganizationAllList List");

            $sql = 'SELECT org.organization_id , mod.abbreviated_name, mod.upper_level_organization'
                . ' FROM m_organization_detail mod'
                . ' INNER JOIN m_organization mo ON mod.organization_id = mo.organization_id'
                . ' , (SELECT od.organization_id '
                . ' , max(od.application_date_start) as application_date_start'
                . ' FROM m_organization_detail od';
            if( $authority === 'registration' )
            {
                $sql .= ' INNER JOIN m_organization o ON o.organization_id = od.organization_id ';
            }
            $sql .= ' WHERE od.application_date_start <= current_date';
            if( $authority === 'registration' )
            {
                $sql .= ' AND o.is_del = 0 ';
            }
            $sql .= ' GROUP BY od.organization_id ORDER BY od.organization_id ) org'
                  . ' WHERE mod.organization_id = org.organization_id'
                  . ' AND mod.application_date_start = org.application_date_start';
            if( $authority === 'registration' )
            {
                $sql .= ' AND mo.is_del = 0 ';
            }
            $sql .= ' ORDER BY mo.disp_order, mod.department_code';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);
            
            $organizationList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchOrganizationList");
                return $organizationList;
            }

            $useOrganIdList = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
               $organization = array(
                   'organization_id'  => $data['organization_id'],                   // 組織ID
                   'abbreviated_name' => $data['abbreviated_name'],                  // 略称名
                   'upper_level_organization' => $data['upper_level_organization'],  // 上位組織ID
               );
               array_push($organizationList, $organization);
               array_push($useOrganIdList, $data['organization_id']);
            }

            // 適用予定の組織も必要か
            if( $isViewPlans )
            {
                $sql = " SELECT organization_id, abbreviated_name, upper_level_organization "
                     . " FROM v_organization WHERE eff_code = '適用予定' ";
                $result = $DBA->executeSQL($sql, $parametersArray);
                if( $result === false )
                {
                    $organizationList = array();
                    $Log->trace("END getSearchOrganizationList");
                    return $organizationList;
                }
                
                while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
                {
                   $organization = array(
                       'organization_id'  => $data['organization_id'],                   // 組織ID
                       'abbreviated_name' => $data['abbreviated_name'],                  // 略称名
                       'upper_level_organization' => $data['upper_level_organization'],  // 上位組織ID
                   );
                   
                   if( false == in_array( $data['organization_id'], $useOrganIdList ) )
                   {
                        array_push($organizationList, $organization);
                        array_push($useOrganIdList, $data['organization_id']);
                   }
                }
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDListALL( SystemParameters::$V_M_ORGANIZATION, $authority );

            // アクセス可能な組織IDリスト作成時、組織マスタのみ、適用中以外も混じるため、削除する
            $aOrganList = array();
            $aOrganIdList = array();
            foreach ( $OrganList as $value )
            {
                if( ( $value['eff_code'] == '適用中' && false == in_array( $value['organization_id'], $aOrganIdList ) ) ||
                    ( $value['eff_code'] == '適用予定' && false == in_array( $value['organization_id'], $aOrganIdList ) ) )
                {
                    array_push( $aOrganList, $value );
                    array_push( $aOrganIdList, $value['organization_id'] );
                }
            }

            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $aOrganList, $organizationList );

            $initial = array('organization_id' => '', 'abbreviated_name' => '',);
            array_unshift($outList, $initial );

            // 階層表示する
            if( $isLevelView )
            {
                // 配列の総数設定
                $maxCnt = count( $outList );
                // 各組織の階層を設定する
                for( $i=1; $i < $maxCnt; $i++ )
                {
                    // 現データが最上位である
                    if( 0 == $outList[$i]['upper_level_organization'] )
                    {
                        $outList[$i]['level'] = 0;
                        $outList[$i]['isSameParent'] = false;
                        $outList[$i]['upperLvList'] = array( $i );
                        $level = 1;
                    }
                    else
                    {
                        if( $outList[$i]['upper_level_organization'] == $outList[$i-1]['organization_id'] )
                        {
                            // 1階層ダウン
                            $level++;
                            $outList[$i]['level'] = $level;
                            $outList[$i]['upperLvList'] = $outList[$i-1]['upperLvList'];
                            array_push( $outList[$i]['upperLvList'], $i );
                        }
                        else
                        {
                            // 現行の階層を再計算する
                            for( $j=$i-1; $j > 0; $j-- )
                            {
                                if( $outList[$i]['upper_level_organization'] == $outList[$j]['upper_level_organization'] )
                                {
                                    $level = $outList[$j]['level'];
                                    $outList[$i]['level'] = $level;
                                    $outList[$i]['upperLvList'] = $outList[$j]['upperLvList'];
                                    // 自分と同列の組織ID(最後)を削除
                                    array_pop($outList[$i]['upperLvList']);
                                    array_push( $outList[$i]['upperLvList'], $i );
                                    break;
                                }
                            }
                        }
                        
                        // 自分と同一の上位IDをもつ組織が存在するか
                        $outList[$i]['isSameParent'] = false;
                        for( $j=$i+1; $j < $maxCnt; $j++ )
                        {
                            if( $outList[$i]['upper_level_organization'] == $outList[$j]['upper_level_organization'] )
                            {
                                $outList[$i]['isSameParent'] = true;
                                break;
                            }
                        }
                    }
                }
                
                // 出力設定
                for( $i=1; $i < $maxCnt; $i++ )
                {
                    $line = "";
                    $upperLvCnt = count( $outList[$i]['upperLvList'] );
                    // 階層分、空白を入れる
                    for( $j=1; $j < $upperLvCnt-1; $j++ )
                    {
                        if( $outList[$outList[$i]['upperLvList'][$j]]['isSameParent'] )
                        {
                            $line .= "｜";
                        }
                        $line .= "　";
                    }
                    
                    if( $outList[$i]['isSameParent'] )
                    {
                        $line .= "├";
                    }
                    else if( $outList[$i]['upper_level_organization'] != 0 )
                    {
                        $line .= "└";
                    }
                    
                    $outList[$i]['abbreviated_name'] = $line . $outList[$i]['abbreviated_name'];
                }
            }

            $Log->trace("END getSearchOrganizationAllList");
            return $outList;
        }

        /**
         * 表示項目設定マスタの検索用設定名のプルダウン
         * @param    $authority     アクセス権限名
         * @return   設定名リスト(設定名) 
         */
        public function getSearchOptionNameList( $authority = 'reference' )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchOptionNameList");

            $sql  = ' SELECT DISTINCT display_item_id,name, organization_id FROM m_display_item ';
            $sql .= ' WHERE is_del = 0 ';

            $sql .= ' ORDER BY display_item_id ';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $optionList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchOptionNameList");
                return $optionList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($optionList, $data);
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDList( SystemParameters::$V_A_DISPLAY_ITEM, $authority );

            // アクセス可能な組織IDリスト作成時、組織マスタのみ、適用中以外も混じるため、削除する
            $aOrganList = array();
            $aOrganIdList = array();
            foreach ( $OrganList as $value )
            {
                if( ( $value['eff_code'] == '適用中'   && false == in_array( $value['organization_id'], $aOrganIdList ) ) ||
                    ( $value['eff_code'] == '適用予定' && false == in_array( $value['organization_id'], $aOrganIdList ) ) )
                {
                    array_push( $aOrganList, $value );
                    array_push( $aOrganIdList, $value['organization_id'] );
                }
            }

            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $aOrganList, $optionList );

            $initial = array('display_item_id' => '', 'name' => '',);
            array_unshift($outList, $initial);

            // 参照のみ、重複データ削除
            if( $authority == 'reference' )
            {
                $column = "name";
                $outList = $this->getUniqueArray($outList, $column);
            }

            $Log->trace("END getSearchOptionList");
            return $outList;
        }

        /**
         * 検索(セクション)プルダウン
         * @param    $authority     アクセス権限名
         * @return   セクションリスト(セクションID/セクション名) 
         */
        public function getSearchSectionList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchSectionList");

            $sql = 'SELECT section_id, section_name, organization_id FROM m_section '
                . ' WHERE is_del = :is_del ORDER BY disp_order, code';
            $parametersArray = array( ':is_del' => 0, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $sectionNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchSectionList");
                return $sectionNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($sectionNameList, $data);
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDList( SystemParameters::$V_A_SECTION, $authority );
            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $sectionNameList );

            $initial = array('section_id' => '', 'section_name' => '',);
            array_unshift($outList, $initial );

            $column = "section_name";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchSectionList");

            return $outList;
        }
        

        /**
         * 検索(就業規則)プルダウン
         * @param    $authority     アクセス権限名
         * @return   就業規則リスト(就業規則ID/就業規則名) 
         */
        public function getSearchLaborRegulationsList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchLaborRegulationsList");

            $sql = 'SELECT labor_regulations_id, organization_id, labor_regulations_name '
                 . ' FROM m_labor_regulations ';
            $sql .= ' WHERE is_del = 0 ';

            $sql .= ' ORDER BY organization_id, labor_regulations_id';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $laborRegulationsList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchLaborRegulationsList");
                return $laborRegulationsList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($laborRegulationsList, $data);
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDList( SystemParameters::$V_A_LABOR_REGULATIONS, $authority );

            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $laborRegulationsList );

            $initial = array('labor_regulations_id' => '', 'labor_regulations_name' => '',);
            array_unshift($outList, $initial );

            $column = "labor_regulations_name";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchLaborRegulationsList");

            return $outList;
        }

        /**
         * 試用期間プルダウン
         * @return   試用期間リスト(試用期間ID/試用期間名) 
         */
        public function getSearchTrialPeriodCrieriaList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchTrialPeriodCrieriaList List");

            $sql = ' SELECT trial_period_criteria_id, trial_period_criteria_name '
                 . ' FROM public.m_trial_period_criteria ORDER BY disp_order ';

            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $trialPeriodList = array();

            if( $result === false )
            {
                $Log->trace("END getSearchTrialPeriodCrieriaList");
                return $trialPeriodList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($trialPeriodList, $data);
            }

            $Log->trace("END getSearchTrialPeriodCrieriaList");

            return $trialPeriodList;
        }

        /**
         * 指定配列の重複削除
         * @param    $array           プルダウンリスト
         * @param    $column          配列のキーとする名称
         * @return   $uniqueArray     重複削除したリスト
         */
        public function getUniqueArray($array, $column)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getUniqueArray");
            
            $tmp = [];
            $uniqueArray = [];

            foreach ($array as $value)
            {
                if (!in_array($value[$column], $tmp, true))
                {
                    $tmp[] = $value[$column];
                    $uniqueArray[] = $value;
                }
            }
            
            $Log->trace("END getUniqueArray");
            return $uniqueArray;
        }

        /**
         * 検索(役職名)プルダウン
         * @param    $authority     アクセス権限名
         * @return   役職リスト(役職ID/役職名) 
         */
        public function getSearchPositionList( $authority = 'reference' )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchPositionList");

            $sql = 'SELECT position_id, position_name, organization_id FROM m_position '
                . ' WHERE is_del = :is_del ORDER BY disp_order, code';
            $parametersArray = array( ':is_del' => 0, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $positionNameList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchPositionList");
                return $positionNameList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($positionNameList, $data);
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDList( SystemParameters::$V_M_POSITION, $authority );
            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $positionNameList );

            $initial = array('position_id' => '', 'position_name' => '',);
            array_unshift($outList, $initial );

            $column = "position_name";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchPositionList");

            return $outList;
        }

        /**
         * 雇用形態マスタの検索用コードのプルダウン
         * @param    $authority     アクセス権限名
         * @return   雇用形態リスト(雇用形態ID、雇用形態名) 
         */
        public function getSearchEmploymentList( $authority = 'reference' )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchEmploymentList");

            $sql = ' SELECT DISTINCT employment_id,employment_name,organization_id FROM m_employment '
                 . ' WHERE is_del = :is_del ORDER BY employment_id';
            $parametersArray = array( ':is_del' => 0, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $employmentList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchEmploymentList");
                return $employmentList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($employmentList, $data);
            }

            // アクセス可能な組織IDリストを作成
            $OrganList = $this->getAccessIDList( SystemParameters::$V_M_EMPLOYMENT, $authority );

            $outList = array();
            $outList = $this->securityProcess->creatAccessControlledList( $OrganList, $employmentList );

            $initial = array('employment_id' => '', 'employment_name' => '',);
            array_unshift($outList, $initial);

            $column = "employment_name";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchEmploymentList");
            return $outList;
        }

        /**
         * 従業員検索用のプルダウン(1組織限定)
         * @param    $organID      組織ID
         * @return   従業員リスト  (ユーザID、ユーザ名) 
         */
        public function getSearchUserList( $organID )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchUserList");

            $userList = array();
            // 組織IDが数字ではない場合、空のリストを返却
            if( !is_numeric( $organID ) )
            {
                $Log->trace("END getSearchUserList");
                return $userList;
            }

            $sql = ' SELECT user_id, user_name FROM v_user '
                 . "  WHERE eff_code = '適用中' "
                 . "    AND organization_id = :organization_id "
                 . "    AND status = '在籍' "
                 . '  ORDER BY  e_disp_order, p_disp_order, employees_no ';
            $parametersArray = array( ':organization_id' => $organID, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END getSearchUserList");
                return $userList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($userList, $data);
            }

            $Log->trace("END getSearchUserList");
            return $userList;
        }

        /**
         * 従業員検索用のプルダウン(1組織限定)
         * @param    $organID      組織ID
         * @return   従業員リスト  (ユーザID、ユーザ名) 
         */
        public function getSearchUserListALL( $organID )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchUserList");

            $userList = array();

            $sql = ' SELECT user_id, user_name FROM v_user '
                 . " WHERE eff_code = '適用中' "
                 . "   AND status = '在籍' ";

            if( !empty( $organID ) )
            {
                $sql .= ' AND organization_id = :organization_id ';
                $parametersArray = array( ':organization_id' => $organID, );
            }

            $sql .= ' ORDER BY  o_disp_order, e_disp_order, p_disp_order, employees_no ';
            
            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END getSearchUserList");
                return $userList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($userList, $data);
            }

            $Log->trace("END getSearchUserList");
            return $userList;
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
            
            // 継承先で実装する
            $urlKey = "";
            $authority = "";
            $organList = array();
            
            $Log->trace("END getAccessIDList");
            
            return $organList;
        }
        
        /**
         * グループのプルダウン
         * @param     
         * @return   グループリスト  (グループID、グループ名) 
         */
        public function getSearchGroupList()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchGroupList");

            $groupList = array();

            $sql = ' SELECT group_id, group_name FROM m_group ORDER BY disp_order';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END getSearchGroupList");
                return $groupList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($groupList, $data);
            }

            $Log->trace("END getSearchGroupList");
            return $groupList;
        }

        /**
         * グループのユーザーリスト
         * @param     
         * @return   ユーザーリスト  (ユーザーID、ユーザー名) 
         */
        public function getSearchGroupUserList( $groupID )
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchGroupUserList");

            $groupUserList = array();

            $sql = ' SELECT menber_id, menber_name FROM m_group';

            if( !empty( $groupID ) )
            {
                $sql .= ' where group_id = :group_id ';
                $parametersArray = array( ':group_id' => $groupID, );
            }
            
            $result = $DBA->executeSQL($sql, $parametersArray);
            
            if( $result === false )
            {
                $Log->trace("END getSearchGroupUserList");
                return $groupUserList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                // グループリスト分、展開しなおす
                $userList =  explode(',',$data['menber_id']);
                $userNameList =  explode(',',$data['menber_name']);

                if($userList[0] != ""){
                    // 対象者データを再構成
                    for ($hc = 0; $hc < count($userList); $hc++){

                        if($userList[$hc] != ""){
                            // 種別ごとに判別
                            if(substr($userList[$hc], 0, 2) == '1-' ){
                                // 組織の場合、組織IDでスタッフを検索して登録
                                $sList = $this->getSearchUserList( substr($userList[$hc], 2, strlen($userList[$hc])));

                                // スタッフ数分登録する
                                foreach($sList as $dateListOne){

                                    // スタッフの場合そのまま登録
                                    $postArray = array(
                                        'menber_id'           => $dateListOne["user_id"],
                                        'menber_name'         => $dateListOne["user_name"],
                                    );

                                    array_push($groupUserList, $postArray);

                                }

                            }else if(substr($userList[$hc], 0, 2) == '2-' ){
                                // 役職の場合、役職IDでスタッフを検索して登録
                                $pList =$this->getPositionUser(substr($userList[$hc], 2, strlen($userList[$hc])));

                                // スタッフ数分登録する
                                foreach($pList as $dateListOne){

                                    // スタッフの場合そのまま登録
                                    $postArray = array(
                                        'menber_id'           => $dateListOne["user_id"],
                                        'menber_name'         => $dateListOne["user_name"],
                                    );

                                    array_push($groupUserList, $postArray);

                                }

                            }else{
                                // スタッフの場合そのまま登録
                                $postArray = array(
                                    'menber_id'         => substr($userList[$hc], 2, strlen($userList[$hc])),
                                    'menber_name'       => $userNameList[$hc],
                                );

                                array_push($groupUserList, $postArray);
                            }
                        }
                    }
                }
                
            }

            $Log->trace("END getSearchGroupUserList");
            return $groupUserList;
        }

        /**
         * 対象役職のスタッフを取得する
         * @param    $position_id
         * @return   SQLの実行結果
         */
        public function getPositionUser( $position_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getPositionUser");

            $sql = ' SELECT * FROM v_user where position_id = :position_id';
            
            $searchArray = array( ':position_id' => $position_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $noticeContactDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result == false )
            {
                $Log->trace("END getPositionUser");
                return $noticeContactDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $noticeContactDataList = $data;
            }

            $Log->trace("END getPositionUser");

            return $noticeContactDataList;
        }
        
         
        
    }

?>
