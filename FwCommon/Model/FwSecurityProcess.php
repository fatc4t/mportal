<?php
    /**
     * @file    FW共通セキュリティ(Model)
     * @author  USE Y.Sakata
     * @date    2016/06/30
     * @version 1.00
     * @note    FW共通で行うセキュリティ処理を定義
     */

    // FwBaseControllerの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwBaseModel.php';
    
    /**
     * セキュリティクラス
     * @note    セキュリティ処理を共通で使用するモデルの処理を定義
     */
    class FwSecurityProcess extends FwBaseClass
    {
        // DBアクセスクラス
        protected $DBA = null;              ///< DBアクセスクラス
        protected $isOrganMaster = false;   ///< 組織マスタ画面であるか
        protected $isPlans = false;         ///< 組織マスタの予定を表示する画面であるか

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseClassのコンストラクタ
            parent::__construct();
            global $isOrganMaster, $isPlans;
            $isOrganMaster = false;
            $isPlans       = false;
            $urlKey = $this->getUrlKey();
            // 組織の未来日を取得していいマスタを設定
            // 組織マスタ
            if( SystemParameters::$V_M_ORGANIZATION === $urlKey )
            {
                $isOrganMaster = true;
            }
            // 役職マスタ
            if( SystemParameters::$V_M_POSITION === $urlKey )
            {
                $isPlans = true;
            }
            // 雇用形態マスタ
            if( SystemParameters::$V_M_EMPLOYMENT === $urlKey )
            {
                $isPlans = true;
            }
            // 従業員マスタ
            if( SystemParameters::$V_M_USER === $urlKey )
            {
                $isPlans = true;
            }
            // セキュリティマスタ
            if( SystemParameters::$V_M_SECURITY === $urlKey )
            {
                $isPlans = true;
            }
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // FwBaseClassのデストラクタ
            parent::__destruct();
        }
        
        /**
         * 指定した認証キーの組織IDを取得
         * @note     指定した認証キーの組織IDを取得
         * @param    $authenKey     認証キー
         * @return   組織ID
         */
        public function getAuthenKeyToOrganID( $authenKey )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAuthenKeyToOrganID");

            $sql =  ' SELECT organization_id FROM m_organization '
                 .  ' WHERE authentication_key = :authentication_key AND is_del = 0 ';
            
            $parameters = array( ':authentication_key' => $authenKey,);

            $result = $DBA->executeSQL( $sql, $parameters );
            $organID = 0;
            if( $result === false )
            {
                $Log->trace("END getAuthenKeyToOrganID");
                return $organID;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $organID = $data['organization_id'];
            }

            $Log->trace("END getAuthenKeyToOrganID");
            return $organID;
        }
        
        /**
         * 該当画面のアクセス権限を取得
         * @note     アクセス画面のアクセス権限を取得
         * @return   アクセス権限リスト
         */
        public function getAccessAuthorityList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAccessAuthorityList");

            $urlKey = $this->getUrlKey();

            $Log->trace("END getAccessAuthorityList");
            return $this->getAccessAuthorityListForUrl( $urlKey );
        }
        
        /**
         * 該当画面のアクセス権限を取得
         * @note     アクセス画面のアクセス権限を取得
         * @param    $urlKey     URLKey
         * @return   アクセス権限リスト
         */
        public function getAccessAuthorityListForUrl( $urlKey )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAccessAuthorityListForUrl");

            $sql   =  ' SELECT ms.reference, ms.registration, ms.delete, ms.approval, ms.printing, ms.output '
                  .   ' FROM   m_security_detail ms INNER JOIN public.m_access_authority ma ON ms.access_authority_id = ma.access_authority_id '
                  .   ' WHERE  ma.url = :url AND ms.security_id = :security_id ';

            $ret = array(
                            'reference'    => 6,
                            'registration' => 6,
                            'delete'       => 6,
                            'approval'     => 6,
                            'printing'     => 6,
                            'output'       => 6,
                        );
            
            // urlがアクセス権限の設定対象の場合、アクセス権限なしを返却
            if( "" === $urlKey )
            {
                $Log->trace("END getAccessAuthorityListForUrl");
                return $ret;
            }
            
            $securityID = isset( $_SESSION["SECURITY_ID"] ) ? $_SESSION["SECURITY_ID"] : 0;
            $parametersArray = array( 
                ':url'           => $urlKey,
                ':security_id'   => $securityID,
            );
            
            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END getAccessAuthorityListForUrl");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data;
            }

            $Log->trace("END getAccessAuthorityListForUrl");
            return $ret;
        }
        
        /**
         * 組織階層表示順を取得
         * @note     表示する最上位の組織IDを指定し、組織IDを階層ごと表示順に取得する
         * @param    $organization_id   組織ID
         * @return   組織IDを階層ごと表示順リスト
         */
        public function getDisplayOrderHierarchy( $organization_id )
        {
            global $Log, $isOrganMaster, $isPlans; // グローバル変数宣言
            $Log->trace("START getDisplayOrderHierarchy");

            $sql =  ' SELECT organization_id, organization_detail_id, upper_level_organization, '
                 .  ' application_date_start, authentication_key, abbreviated_name, eff_code '
                 .  ' FROM v_organization WHERE upper_level_organization = :organization_id ';
            if( !$isOrganMaster && !$isPlans )
            {
                // 組織マスタ以外は、「適用中」の組織のみ表示
                $sql .= " AND eff_code like '適用中' ";
            }
            
            if( $isPlans )
            {
                // 組織マスタ以外で、「適用中」/「適用予定」を表示する
                $sql .= " AND ( eff_code like '適用中' OR eff_code like '適用予定' ) ";
            }
            $sql .= ' ORDER BY disp_order, department_code ';

            $levelOrganList = $this->getOrganizationInfo( $organization_id );

            $this->getRecursionDisplayOrderHierarchy( $organization_id, $sql, $levelOrganList );

            $Log->trace("END getDisplayOrderHierarchy");
            return $levelOrganList;
        }
        

        /**
         * 閲覧可能組織情報のみに、一覧を編集
         * @param    $viewOrganList     閲覧可能組織リスト(組織ID/組織詳細ID/上位組織ID/適用開始日)
         * @param    $dataList          編集対象の情報一覧
         * @return   $viewList          閲覧可能組織の情報一覧
         */
        public function creatAccessControlledList( $viewOrganList, $dataList )
        {
            global $Log; // グローバル変数宣言

            $Log->trace("START creatAccessControlledList");
            
            $viewList = array();
            if( empty($viewOrganList[0]['organization_id']) || empty($dataList[0]['organization_id']) )
            {
                $Log->trace("END creatAccessControlledList");
                return $viewList;
            }

            foreach($viewOrganList as $viewable)
            {
                foreach($dataList as $data)
                {
                    if($viewable['organization_id'] == $data['organization_id'])
                    {
                        array_push($viewList, $data);
                    }
                }
            }
            
            $Log->trace("END creatAccessControlledList");

            return $viewList;
        }

        /**
         * アクセス可能な組織の階層表示順を取得
         * @note     セキュリティ設定用に、アクセス可能な組織の階層表示順を取得
         */
        public function getAccessViewOrder()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAccessViewOrder");
            
            // アクセス権限参照範囲を見て、組織IDを設定する
            $accessAuthorityList = $this->getAccessAuthorityList();
            
            // 参照権限
            $_SESSION["REFERENCE"]    = $this->getAccessDisplayOrderHierarchy( $accessAuthorityList['reference'] );
            // 編集権限
            $_SESSION["REGISTRATION"] = $this->getAccessDisplayOrderHierarchy( $accessAuthorityList['registration'] );
            // 削除権限
            $_SESSION["DELETE"]       = $this->getAccessDisplayOrderHierarchy( $accessAuthorityList['delete'] );
            // 承認権限
            $_SESSION["APPROVAL"]     = $this->getAccessDisplayOrderHierarchy( $accessAuthorityList['approval'] );
            // 印刷
            $_SESSION["PRINTING"]     = $this->getAccessDisplayOrderHierarchy( $accessAuthorityList['printing'] );
            // ファイル出力
            $_SESSION["OUTPUT"]       = $this->getAccessDisplayOrderHierarchy( $accessAuthorityList['output'] );
            
            $Log->trace("END getAccessViewOrder");
        }

        /**
         * アクセス可能な組織ID一覧取得
         * @note     セキュリティ設定用に、アクセス可能な組織IDの一覧を取得
         * @return   上位組織ID一覧(section_id/section_name)
         */
        public function getAccessDisplayOrderHierarchy( $accessAuthority, $tableName = "" )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAccessDisplayOrderHierarchy");
            
            $organization_id = isset( $_SESSION["ORGANIZATION_ID"] ) ? $_SESSION["ORGANIZATION_ID"] : 0;
            $topID = $this->getTopOrganizationID($organization_id, $tableName );
            $accessOrganList = array();
            // 管理者
            if( $accessAuthority === 1 )
            {
                $accessOrganList = $this->getAllOrganizationInfo();
            }
            // 全組織
            else if( $accessAuthority === 2 )
            {
                // 配下組織と区別するために修正 2017/04/28
                // $accessOrganList = $this->getDisplayOrderHierarchy($topID);
                $accessOrganList = $this->getAllOrganizationInfo();
            }
            // 配下組織
            else if( $accessAuthority === 3 )
            {
                $accessOrganList = $this->getDisplayOrderHierarchy($topID);
            }
            // 自組織
            else if( $accessAuthority === 4 )
            {
                $accessOrganList = $this->getOrganizationInfo($topID);
            }
            // 自分 or なし
            else
            {
                $accessOrganList = array(
                    'organization_id'          => '',    // 組織ID
                    'organization_detail_id'   => '',    // 組織詳細ID
                    'upper_level_organization' => '',    // 上位組織ID
                    'application_date_start'   => '',    // 適用開始日
                );
            }
            
            $Log->trace("END getAccessDisplayOrderHierarchy");
            
            return $accessOrganList;
        }

        /**
         * 上位組織ID一覧取得
         * @note     セキュリティ設定用に、指定した組織ID以上の上位組織IDを階層順(下→上)に取得する
         * @param    $organization_id   組織ID
         * @return   上位組織ID一覧
         */
        public function getLevelOrganizationList( $organization_id )
        {
            global $DBA, $Log, $isOrganMaster, $isPlans; // グローバル変数宣言
            $Log->trace("START getLevelOrganizationList");

            $sql =  ' SELECT organization_id, organization_detail_id, upper_level_organization, '
                 .  ' application_date_start, authentication_key, abbreviated_name, eff_code '
                 .  ' FROM v_organization WHERE organization_id = :organization_id ';
            if( !$isOrganMaster && !$isPlans )
            {
                // 組織マスタ以外は、「適用中」の組織のみ表示
                $sql .= " AND eff_code like '適用中' ";
            }
            
            if( $isPlans )
            {
                // 組織マスタ以外で、「適用中」/「適用予定」を表示する
                $sql .= " AND ( eff_code like '適用中' OR eff_code like '適用予定' ) ";
            }
            
            $sql .= ' ORDER BY disp_order, department_code ';

            $levelOrganList = array();
            $organID = $organization_id;
            while ( $organID != 0 )
            {
                $parametersArray = array( 
                    ':organization_id' => $organID,
                );
                
                $result = $DBA->executeSQL($sql, $parametersArray);

                if( $result === false )
                {
                    $Log->trace("END getLevelOrganizationList");
                    break;
                }

                while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
                {
                    $levelOrganization = array(
                        'organization_id'          => $data['organization_id'],             // 組織ID
                        'organization_detail_id'   => $data['organization_detail_id'],      // 組織詳細ID
                        'upper_level_organization' => $data['upper_level_organization'],    // 上位組織ID
                        'application_date_start'   => $data['application_date_start'],      // 適用開始日
                        'authentication_key'       => $data['authentication_key'],          // 認証キー
                        'abbreviated_name'         => $data['abbreviated_name'],            // 組織略称名
                        'eff_code'                 => $data['eff_code'],                    // 状況
                    );
                    array_push($levelOrganList, $levelOrganization);
                    $organID = $data['upper_level_organization'];
                }
            }
            $Log->trace("END getLevelOrganizationList");
            return $levelOrganList;
        }

        /**
         * キーURL取得
         * @note     アクセスされたURLから、キーURLを取得する
         * @return   URLキー
         */
        protected function getUrlKey()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getUrlKey");

            // 継承先で実装する
            $keyURL = '';

            $Log->trace("END getUrlKey");

            return $keyURL;
        }

        /**
         * コントローラ名から、キーURL取得
         * @note     コントローラ名から、キーURLを取得する
         * @param    $menuList         メニューリスト
         * @param    $controllerName   コントローラ名
         * @return   URLキー
         */
        protected function getControllerUrlKey( $menuList, $controllerName )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getControllerUrlKey");

            $keyURL = '';
            // メニューにURLキーがあるか
            $keys = array_keys($menuList);
            foreach( $keys as $key )
            {
                if( false !== strpos( $key, $controllerName ) )
                {
                    $keyURL = $key;
                }
            }
            
            $Log->trace("END getControllerUrlKey");
            return $keyURL;
        }

        /**
         * 同一の上位組織をもつ階層の一覧
         * @note     組織IDを指定し、同一の上位組織をもつ階層の一覧を表示順に取得する
         * @param    $organization_id   組織ID
         * @param    $sql               SQL文
         * @return   同一階層内の組織IDの表示順リスト
         */
        private function getDisplayOrderSameHierarchy( $organization_id, $sql )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getDisplayOrderSameHierarchy");

            $parametersArray = array( 
                ':organization_id' => $organization_id,
            );
            
            $result = $DBA->executeSQL($sql, $parametersArray);

            $levelOrganList = array();
            if( $result === false )
            {
                $Log->trace("END getDisplayOrderSameHierarchy");
                return $levelOrganList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $levelOrganization = array(
                    'organization_id'          => $data['organization_id'],             // 組織ID
                    'organization_detail_id'   => $data['organization_detail_id'],      // 組織詳細ID
                    'upper_level_organization' => $data['upper_level_organization'],    // 上位組織ID
                    'application_date_start'   => $data['application_date_start'],      // 適用開始日
                    'authentication_key'       => $data['authentication_key'],          // 認証キー
                    'abbreviated_name'         => $data['abbreviated_name'],            // 組織略称名
                    'eff_code'                 => $data['eff_code'],                    // 状況
                );
                array_push($levelOrganList, $levelOrganization);
            }

            $Log->trace("END getDisplayOrderSameHierarchy");
            return $levelOrganList;
        }
        
        /**
         * 再帰的に、組織階層表示順を取得する(private)
         * @note     表示する最上位の組織IDを指定し、組織IDを階層ごと表示順に取得する
         * @param    $organization_id       組織ID
         * @param    $sql                   SQL文
         * @param    $levelOrganList 組織階層の表示順リスト
         */
        private function getRecursionDisplayOrderHierarchy( $organization_id, &$sql, &$levelOrganList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getRecursionDisplayOrderHierarchy");

            $orderSameHierarchy = $this->getDisplayOrderSameHierarchy( $organization_id, $sql );
            
            foreach ( $orderSameHierarchy as $value )
            {
                $levelOrganization = array(
                    'organization_id'          => $value['organization_id'],             // 組織ID
                    'organization_detail_id'   => $value['organization_detail_id'],      // 組織詳細ID
                    'upper_level_organization' => $value['upper_level_organization'],    // 上位組織ID
                    'application_date_start'   => $value['application_date_start'],      // 適用開始日
                    'authentication_key'       => $value['authentication_key'],          // 認証キー
                    'abbreviated_name'         => $value['abbreviated_name'],            // 組織略称名
                    'eff_code'                 => $value['eff_code'],                     // 状況
                );
                array_push($levelOrganList, $levelOrganization);
                $this->getRecursionDisplayOrderHierarchy( $value['organization_id'], $sql, $levelOrganList );
            }

            $Log->trace("END getRecursionDisplayOrderHierarchy");
        }
        
        /**
         * 組織情報表示
         * @note     指定した組織IDの情報を取得する
         * @param    $organization_id   組織ID
         * @return   組織情報
         */
        private function getOrganizationInfo( $organization_id )
        {
            global $DBA, $Log, $isOrganMaster, $isPlans; // グローバル変数宣言
            $Log->trace("START getOrganizationInfo");

            $sql =  ' SELECT organization_id, organization_detail_id, upper_level_organization, '
                 .  ' application_date_start, authentication_key, abbreviated_name, eff_code '
                 .  ' FROM v_organization WHERE organization_id = :organization_id ';
            if( !$isOrganMaster && !$isPlans )
            {
                // 組織マスタ以外は、「適用中」の組織のみ表示
                $sql .= " AND eff_code like '適用中' ";
            }
            
            if( $isPlans )
            {
                // 組織マスタ以外で、「適用中」/「適用予定」を表示する
                $sql .= " AND ( eff_code like '適用中' OR eff_code like '適用予定' ) ";
            }
            
            $sql .= ' ORDER BY disp_order, department_code ';

            $levelOrganList = array();

            $parametersArray = array( 
                ':organization_id' => $organization_id,
            );
            
            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END getOrganizationInfo");
                return $levelOrganList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($levelOrganList, $data);
            }

            $Log->trace("END getOrganizationInfo");
            return $levelOrganList;
        }

//=============================================================================
//   動きが遅いため、修正 2021.12.24 by TAS start
//=============================================================================
        /**
         * 全組織情報表示
         * @note     システム全組織IDの情報を取得する
         * @return   システム全組織情報
         */
        private function getAllOrganizationInfo()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAllOrganizationInfo");

            // 全組織IDを取得用SQL作成
            $subSql  = " SELECT organization_id ";
			$subSql .= "   FROM m_organization ";
			$subSql .= "  ORDER BY organization_id ";

			$sql  = " SELECT organization_id, organization_detail_id, upper_level_organization, ";
			$sql .= "        application_date_start, authentication_key, abbreviated_name, eff_code ";
			$sql .= "  FROM v_organization ";
			$sql .= " WHERE organization_id in (".$subSql.") ";

            if (!$isOrganMaster && !$isPlans) {
                // 組織マスタ以外は、「適用中」の組織のみ表示
                $sql .= " AND eff_code like '適用中' ";
            }
            
            if ($isPlans) {
                // 組織マスタ以外で、「適用中」/「適用予定」を表示する
                $sql .= " AND ( eff_code like '適用中' OR eff_code like '適用予定' ) ";
            }
            $sql .= " ORDER BY disp_order, department_code ";

            $levelOrganList = array();
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            if( $result === false )
            {
                $Log->trace("END getAllOrganizationInfo");
                return $levelOrganList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
			{
                $levelOrganization = [
                    'organization_id'          => $data['organization_id'],             // 組織ID
                    'organization_detail_id'   => $data['organization_detail_id'],      // 組織詳細ID
                    'upper_level_organization' => $data['upper_level_organization'],    // 上位組織ID
                    'application_date_start'   => $data['application_date_start'],      // 適用開始日
                    'authentication_key'       => $data['authentication_key'],          // 認証キー
                    'abbreviated_name'         => $data['abbreviated_name'],            // 組織略称名
                    'eff_code'                 => $data['eff_code'],                     // 状況
                ];
                array_push($levelOrganList, $levelOrganization);
            }

            $Log->trace("END getAllOrganizationInfo");
            return $levelOrganList;
        }

//=============================================================================
//   動きが遅いため、修正 2021.12.24 by TAS start
//=============================================================================
        /**
         * 全組織情報表示
         * @note     システム全組織IDの情報を取得する
         * @return   システム全組織情報
         */
/*    2021.12.24 tas 修正
        private function getAllOrganizationInfo()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAllOrganizationInfo");

            // 全組織IDを取得する
            $sql =  ' SELECT organization_id FROM m_organization ORDER BY organization_id';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);
            if( $result === false )
            {
                $Log->trace("END getAllOrganizationInfo");
                return $levelOrganList;
            }
            
            $allID = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($allID, $data['organization_id']);
            }
            
            // 全組織IDの情報を取得し、最上位(上位ID=0)の組織IDのリストを作成
            $topIDList = array();
            foreach( $allID as $id )
            {
                $info = $this->getOrganizationInfo($id);
                if( !empty($info) )
                {
                    if( 0 === $info[0]['upper_level_organization'] )
                    {
                        array_push( $topIDList, $id );
                    }
                }
            }

            // 最上位(上位ID=0)の組織IDリスト分、表示リストを作成
            $levelOrganList = array();
            foreach( $topIDList as $topID )
            {
                $dispList = $this->getDisplayOrderHierarchy( $topID );
                foreach( $dispList as $disp )
                {
                    array_push($levelOrganList, $disp);
                }
            }

            $Log->trace("END getAllOrganizationInfo");
            return $levelOrganList;
        }
*/
//=============================================================================
//   動きが遅いため、修正 2021.12.24 by TAS end
//=============================================================================
        
        /**
         * 企業情報を取得する
         * @note     企業情報リストを取得する
         * @return   指定した企業コードの情報
         */
        public function getCompanyContract()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCompanyContractList");

            // 企業情報情報を取得する
            $sql   =  ' SELECT session_time_out '
                  .   ' FROM   public.m_company_contract '
                  .   ' WHERE  company_name = :company_name  ';

            $parameters = array( 
                ':company_name'   => $_SESSION["SCHEMA"],
            );
                
            try {
                $result = $DBA->executeSQL( $sql, $parameters );
            } catch (MyException $e) {
                echo '捕捉した例外: ',  $e->getMessage(), "\n";
            }

            $result = $DBA->executeSQL( $sql, $parameters );
            
            $sessionTimeOut = array();
            
            if( $result === false )
            {
                $Log->trace("END getCompanyContractList");
                return $sessionTimeOut;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($sessionTimeOut, $data['session_time_out']);
            }

            $Log->trace("END getCompanyContractList");
            return $sessionTimeOut;
        }
        
    }

?>
