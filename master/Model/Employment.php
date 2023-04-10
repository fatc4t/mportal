<?php
    /**
     * @file      雇用形態マスタ
     * @author    USE K.Narita
     * @date      2016/06/09
     * @version   1.00
     * @note      雇用形態マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 雇用形態クラス
     * @note   雇用形態マスタテーブルの管理を行う
     */
    class Employment extends BaseModel
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
         * 雇用形態マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(organization_id/is_del/organizationID/employmentName/sort)
         * @return   成功時：$employmentList(organization_id/employment_name/is_del/code/disp_order/organization_id/abbreviated_name)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $employmentDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $employmentDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $employmentDataList, $data);
            }

            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $employmentList = $employmentDataList;
            }
            else
            {
                $employmentList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $employmentDataList);
                if( $postArray['sort'] == 1 )
                {
                    $employmentList = array_reverse($employmentList);
                }
            }

            $Log->trace("END getListData");

            return $employmentList;
        }

        /**
         * 雇用形態マスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/組織ID/雇用形態名/就業規則/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO m_employment( organization_id'
                . '                      , code'
                . '                      , employment_name'
                . '                      , labor_regulations_id'
                . '                      , is_del'
                . '                      , disp_order'
                . '                      , registration_time'
                . '                      , registration_user_id'
                . '                      , registration_organization'
                . '                      , update_time'
                . '                      , update_user_id'
                . '                      , update_organization'
                . '                      ) VALUES ('
                . '                      :organization_id'
                . '                      , :code'
                . '                      , :employment_name'
                . '                      , :labor_regulations_id'
                . '                      , :is_del'
                . '                      , :disp_order'
                . '                      , current_timestamp'
                . '                      , :registration_user_id'
                . '                      , :registration_organization'
                . '                      , current_timestamp'
                . '                      , :update_user_id'
                . '                      , :update_organization)';

            $parameters = array(
                ':organization_id'           => $postArray['organizationID'],
                ':code'                      => $postArray['employmentCode'],
                ':employment_name'             => $postArray['employmentName'],
                ':labor_regulations_id'      => $postArray['laborRegulationsID'],
                ':is_del'                    => $postArray['is_del'],
                ':disp_order'                => $postArray['dispOrder'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "m_employment" );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * 雇用形態マスタ登録データ修正
         * @param    $postArray   入力パラメータ(雇用形態ID/コード/組織ID/雇用形態名/就業規則/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $id_name = "employment_id";
            $inUseArray = $this->getInUseCheckList($id_name);
            // POSTで来た雇用形態IDを数値化する
            $intEmploymentId = intval($postArray['employmentID']);
            // 使用済みの雇用形態情報の判定
            if(in_array($intEmploymentId, $inUseArray))
            {
                // 使用中の場合、組織名を変更するときにはエラーとする
                $used_organization_id = $this->getUseDataOrganizationId($intEmploymentId);
                // POSTで来た組織IDを数値化する
                $intOrganizationId = intval($postArray['organizationID']);
                if($used_organization_id != $intOrganizationId)
                {
                    return "MSG_WAR_2100";
                }
            }

            $sql = 'UPDATE m_employment SET'
                . ' organization_id = :organization_id'
                . ' , code = :code'
                . ' , employment_name = :employment_name'
                . ' , labor_regulations_id = :labor_regulations_id'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE employment_id = :employment_id AND update_time = :update_time ';

            $parameters = array(
                ':organization_id'           => $postArray['organizationID'],
                ':code'                      => $postArray['employmentCode'],
                ':employment_name'             => $postArray['employmentName'],
                ':labor_regulations_id'      => $postArray['laborRegulationsID'],
                ':disp_order'                => $postArray['dispOrder'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':employment_id'               => $postArray['employmentID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * 雇用形態マスタ登録データ削除
         * @param    $postArray   入力パラメータ(雇用形態ID/削除フラグ1/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            $id_name = "employment_id";
            $inUseArray = $this->getInUseCheckList($id_name);
            $intEmploymentId = intval($postArray['employmentID']);
            if(in_array($intEmploymentId, $inUseArray))
            {
                return "MSG_WAR_2101";
            }

            $sql = 'UPDATE m_employment SET'
                . ' is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE employment_id = :employment_id AND update_time = :update_time ';

            $parameters = array(
                ':is_del'                    => $postArray['is_del'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':employment_id'               => $postArray['employmentID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * 雇用形態マスタの検索用コードのプルダウン
         * @return   コードリスト(コード) 
         */
        public function getSearchCodeList()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchCodeList");

            $sql = ' SELECT DISTINCT code, organization_id FROM m_employment '
                 . ' WHERE is_del = :is_del ORDER BY code ';
            $parametersArray = array( ':is_del' => 0, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $codeList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchCodeList");
                return $codeList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($codeList, $data);
            }

            $outList = array();
            $outList = $this->creatAccessControlledList( $_SESSION["REFERENCE"], $codeList );

            $initial = array('code' => '',);
            array_unshift($outList, $initial);

            $column = "code";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchCodeList");
            return $outList;
        }

        /**
         * 雇用形態マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/employmentName/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   雇用形態マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $searchedColumn = ' AND organization.';
            $sqlWhereIn = $this->creatSqlWhereInConditions($searchedColumn);

            $sql = ' SELECT p.employment_id, p.employment_name, p.is_del, p.code, p.labor_regulations_id, mlr.labor_regulations_name, p.update_time, '
                 . '        p.disp_order, organization.organization_id, mod.abbreviated_name '
                 . ' FROM m_employment p INNER JOIN m_organization_detail mod ON p.organization_id = mod.organization_id '
                 . '       LEFT OUTER JOIN m_labor_regulations mlr ON p.labor_regulations_id = mlr.labor_regulations_id , '
                 . '     ( SELECT od.organization_id, MAX(od.application_date_start) as application_date_start '
                 . '       FROM m_organization_detail od INNER JOIN m_organization o ON od.organization_id = o.organization_id '
                 . '       WHERE o.is_del = 0 '
                 . '       GROUP BY od.organization_id, od.department_code, o.disp_order'
                 . '       ORDER BY o.disp_order, od.department_code ) organization '
                 . ' WHERE p.organization_id = organization.organization_id '
                 . '       AND mod.application_date_start = organization.application_date_start ';
            $sql .= $sqlWhereIn;

            if( !empty( $postArray['organizationID'] ) )
            {
                $sql .= ' AND p.organization_id = :organizationID ';
                $organizationIDArray = array(':organizationID' => $postArray['organizationID'],);
                $searchArray = array_merge($searchArray, $organizationIDArray);
            }
            if( !empty( $postArray['employmentName'] ) )
            {
                $sql .= ' AND p.employment_name = :employmentName ';
                $employmentNameArray = array(':employmentName' => $postArray['employmentName'],);
                $searchArray = array_merge($searchArray, $employmentNameArray);
            }
            if( !empty( $postArray['laborRegulationsID'] ) )
            {
                $sql .= ' AND p.labor_regulations_id = :laborRegulationsID ';
                $laborRegIDArray = array(':laborRegulationsID' => $postArray['laborRegulationsID'],);
                $searchArray = array_merge($searchArray, $laborRegIDArray);
            }
            if( !empty( $postArray['codeID'] ) )
            {
                $sql .= ' AND p.code = :codeID ';
                $codeIDArray = array(':codeID' => $postArray['codeID'],);
                $searchArray = array_merge($searchArray, $codeIDArray);
            }
            
            if( $postArray['is_del'] == 0 )
            {
                $sql .= ' AND p.is_del = :is_del ';
                $isDelArray = array(':is_del' => $postArray['is_del'],);
                $searchArray = array_merge($searchArray, $isDelArray);

            }

            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 雇用形態マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   雇用形態マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = ' ORDER BY p.disp_order, p.code ';

            // ソート条件作成
            $sortSqlList = array(
                                3       =>  ' ORDER BY p.is_del DESC, organization.organization_id, p.disp_order, p.code',               // 状態の降順
                                4       =>  ' ORDER BY p.is_del, organization.organization_id, p.disp_order, p.code',                    // 状態の昇順
                                5       =>  ' ORDER BY p.code DESC, organization.organization_id, p.disp_order',                         // コードの降順
                                6       =>  ' ORDER BY p.code, organization.organization_id, p.disp_order',                              // コードの昇順
                                7       =>  ' ORDER BY mod.abbreviated_name DESC, organization.organization_id, p.disp_order, p.code',   // 組織名の降順
                                8       =>  ' ORDER BY mod.abbreviated_name, organization.organization_id, p.disp_order, p.code',        // 組織名の昇順
                                9       =>  ' ORDER BY p.employment_name DESC, organization.organization_id, p.disp_order, p.code',        // 雇用形態名の降順
                               10       =>  ' ORDER BY p.employment_name, organization.organization_id, p.disp_order, p.code',             // 雇用形態名の昇順
                               11       =>  ' ORDER BY p.labor_regulations_id DESC, organization.organization_id, p.disp_order, p.code', // 就業規則の降順
                               12       =>  ' ORDER BY p.labor_regulations_id, organization.organization_id, p.disp_order, p.code',      // 就業規則の昇順
                               13       =>  ' ORDER BY p.disp_order DESC, organization.organization_id, p.code',                         // 表示順の降順
                               14       =>  ' ORDER BY p.disp_order, organization.organization_id, p.code',                              // 表示順の昇順
                            );
            // ソート条件
            if( array_key_exists( $sortNo, $sortSqlList ) )
            {
                $sql = $sortSqlList[$sortNo];
            }

            $Log->trace("END creatSortSQL");
            return $sql;
        }

        /**
         * 雇用形態マスタ更新時使用済み雇用形態データ組織ID取得
         * @param    $employmentID   入力パラメータ(雇用形態ID)
         * @return   組織ID
         */
        private function getUseDataOrganizationId( $employmentID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUseDataOrganizationId");

            $sql = 'SELECT organization_id FROM m_employment  WHERE employment_id = :employment_id';
            $searchArray = array(':employment_id' => $employmentID,);
            // sql実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            if( $result === false )
            {
                $Log->trace("END getUseDataOrganizationId");
                return -1;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $organization_id = $data['organization_id'];
            }

            $Log->trace("END getUseDataOrganizationId");

            return $organization_id;
        }

    }
?>
