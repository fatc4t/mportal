<?php
    /**
     * @file      アプリ用打刻管理モデル
     * @author    USE Y.Sakata
     * @date      2016/07/09
     * @version   1.00
     * @note      アプリ用打刻管理に必要なDBアクセスの制御を行う
     */

    // BaseEmbossing.phpを読み込む
    require './Model/BaseEmbossing.php';

    /**
     * 打刻管理クラス
     * @note   打刻管理に必要なDBアクセスの制御を行う
     */
    class Embossing extends BaseEmbossing
    {
        /**
         * コンストラクタ
         * @param    $companyID   会社ID
         */
        public function __construct( $companyID )
        {
            // ModelBaseのコンストラクタ
            parent::__construct();
            
            // スキーマ取得
            $_SESSION["SCHEMA"] = $this->getSchema( $companyID );
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
         * 組織一覧取得
         * @param    $authenKey   認証キー
         * @return   組織名リスト
         */
        public function getOrganizationList( $authenKey )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getOrganizationList");
            
            // 認証キーから、組織IDを取得
            $organID = $this->securityProcess->getAuthenKeyToOrganID($authenKey);

            $Log->trace("END getOrganizationList");
            return $this->securityProcess->getDisplayOrderHierarchy($organID);
        }

        /**
         * 全組織一覧取得
         * @param    $authenKey   認証キー
         * @return   組織名リスト
         */
        public function getAllOrganizationList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAllOrganizationList");
            
            $sql =  ' SELECT organization_id, authentication_key '
                 .  " FROM v_organization WHERE eff_code = '適用中' ";

            $parameters = array();

            $result = $DBA->executeSQL($sql, $parameters);

            $organizationList = array();
            if( $result === false )
            {
                $Log->trace("END getAllOrganizationList");
                return $organizationList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($organizationList, $data);
            }

            $Log->trace("END getAllOrganizationList");
            return $organizationList;
        }

        /**
         * 削除組織一覧取得
         * @param    $authenKey   認証キー
         * @return   組織名リスト
         */
        public function getDelOrganizationList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getDelOrganizationList");
            
            $sql =  ' SELECT organization_id, authentication_key '
                 .  " FROM v_organization WHERE eff_code = '削除' ";

            $parameters = array();

            $result = $DBA->executeSQL($sql, $parameters);

            $organizationList = array();
            if( $result === false )
            {
                $Log->trace("END getDelOrganizationList");
                return $organizationList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($organizationList, $data);
            }

            $Log->trace("END getDelOrganizationList");
            return $organizationList;
        }

        /**
         * 従業員一覧取得
         * @param    $authenKey   認証キー
         * @return   従業員リスト
         */
        public function getUserList( $authenKey )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getOrganizationList");
            
            $sql  = " SELECT organization_id, employees_no, user_name, is_embossing, biological_info FROM v_user WHERE eff_code = '適用中' AND status = '在籍' ";
            $parameters = array();
            
            // 認証キーがある場合
            if( $authenKey != null )
            {
                // 認証キーから、組織IDを取得
                $organID = $this->securityProcess->getAuthenKeyToOrganID($authenKey);

                $sql .= " AND organization_id = :organization_id ";

                $parameters = array( ':organization_id'  => $organID, );
            }

            $result = $DBA->executeSQL($sql, $parameters);

            $userIdList = array();
            if( $result === false )
            {
                $Log->trace("END getUserList");
                return $userIdList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($userIdList, $data);
            }

            $Log->trace("END   getUserList");
            
            return $userIdList;
        }

        /**
         * シフト一覧取得
         * @param    $authenKey   認証キー
         * @return   シフト一覧
         */
        public function getShiftList( $authenKey )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getShiftList");
            
            $sDate = date( 'Y-m-1', strtotime( date('Y-m-1') . ' -1 month' ) );
            $eDate = date( 'Y-m-1', strtotime( date('Y-m-1') . ' +2 month' ) );
            
            // 認証キーから、組織IDを取得
            $organID = $this->securityProcess->getAuthenKeyToOrganID($authenKey);
            
            $sql  = " SELECT ts.organization_id, vu.employees_no, ts.day, ts.attendance, ts.taikin ";
            $sql .= " FROM t_shift ts INNER JOIN v_user vu ON ts.user_id = vu.user_id AND vu.is_del = 0 AND vu.eff_code = '適用中' ";
            $sql .= " WHERE  ts.organization_id = :organization_id AND :sDate <= day AND day < :eDate ";
            $sql .= " ORDER BY ts.user_id, ts.day ";
            $parameters = array( 
                                    ':organization_id'  => $organID, 
                                    ':sDate'            => $sDate,
                                    ':eDate'            => $eDate,
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $shiftList = array();
            if( $result === false )
            {
                $Log->trace("END getShiftList");
                return $shiftList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($shiftList, $data);
            }

            $Log->trace("END   getShiftList");
            
            return $shiftList;
        }

        /**
         * 削除従業員一覧取得
         * @return   従業員リスト
         */
        public function getDelUserList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getDelUserList");
            
            $sql  = " SELECT user_id, employees_no, user_name, biological_info FROM v_user "
                  . " WHERE ( eff_code = '適用中' AND status = '退職' ) OR ( eff_code = '削除' ) ";
            $parameters = array();

            $result = $DBA->executeSQL($sql, $parameters);

            $userIdList = array();
            if( $result === false )
            {
                $Log->trace("END getDelUserList");
                return $userIdList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($userIdList, $data);
            }

            $Log->trace("END   getDelUserList");
            
            return $userIdList;
        }

        /**
         * 生体情報を保存する
         * @param    $organID          組織ID
         * @param    $employeesNo      従業員No
         * @param    $biologicalInfo   生体情報
         * @return   SQLの実行結果
         */
        public function setBiologicalInfo( $organID, $employeesNo, $biologicalInfo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getOrganizationList");
            
            // 組織IDと従業員Noから、ユーザIDを取得
            $userInfo = $this->getUserInfoE( $organID, $employeesNo );

            $sql  = " UPDATE m_user SET biological_info = :biological_info, update_time = current_timestamp, "
                  . " update_user_id = :update_user_id, update_organization = :update_organization "
                  . " WHERE user_id = :user_id ";

            $parameters = array( 
                                 ':update_user_id'      => $userInfo['user_id'],
                                 ':update_organization' => $organID,
                                 ':user_id'             => $userInfo['user_id'],
                                 ':biological_info'     => $biologicalInfo,
                                );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END   setBiologicalInfo");

            return $ret;
        }
        
        /**
         * 組織名を取得
         * @param    $authenKey   認証キー
         * @return   組織名
         */
        public function getOrganizationName( $authenKey )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getOrganizationName");
            
            $sql  = " SELECT abbreviated_name FROM v_organization  "
                  . " WHERE eff_code = '適用中' AND authentication_key = :authentication_key";
            $parameters = array( ':authentication_key'  => $authenKey );

            $result = $DBA->executeSQL($sql, $parameters);

            $organizationName = "";
            if( $result === false )
            {
                $Log->trace("END getOrganizationName");
                return $organizationName;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $organizationName = $data['abbreviated_name'];
            }

            $Log->trace("END   getOrganizationName");
            
            return $organizationName;
        }
    }
?>
