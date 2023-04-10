<?php
    /**
     * @file      役職マスタ
     * @author    USE S.Kasai
     * @date      2016/06/09
     * @version   1.00
     * @note      役職マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 役職クラス
     * @note   役職マスタテーブルの管理を行う
     */
    class Position extends BaseModel
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
         * 役職マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(organization_id/is_del/organizationID/positionName/sort)
         * @return   成功時：$positionList(organization_id/position_name/is_del/code/disp_order/organization_id/abbreviated_name)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $positionDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $positionDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $positionDataList, $data);
            }

            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $positionList = $positionDataList;
            }
            else
            {
                $positionList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $positionDataList);
                if( $postArray['sort'] == 1 )
                {
                    $positionList = array_reverse($positionList);
                }
            }

            $Log->trace("END getListData");

            return $positionList;
        }
        
        /**
         * 役職マスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/組織ID/役職名/就業規則/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO m_position( organization_id'
                . '                      , code'
                . '                      , position_name'
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
                . '                      , :position_name'
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
                ':code'                      => $postArray['positionCode'],
                ':position_name'             => $postArray['positionName'],
                ':labor_regulations_id'      => $postArray['laborRegulationsID'],
                ':is_del'                    => $postArray['is_del'],
                ':disp_order'                => $postArray['dispOrder'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "m_position" );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * 役職マスタ登録データ修正
         * @param    $postArray   入力パラメータ(役職ID/コード/組織ID/役職名/就業規則/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $id_name = "position_id";
            $inUseArray = $this->getInUseCheckList($id_name);
            // POSTで来た役職IDを数値化する
            $intPositionId = intval($postArray['positionID']);
            // 使用済みの役職情報の判定
            if(in_array($intPositionId, $inUseArray))
            {
                // 使用中の場合、組織名を変更するときにはエラーとする
                $used_organization_id = $this->getUseDataOrganizationId($intPositionId);
                // POSTで来た組織IDを数値化する
                $intOrganizationId = intval($postArray['organizationID']);
                if($used_organization_id != $intOrganizationId)
                {
                    return "MSG_WAR_2100";
                }
            }

            $sql = 'UPDATE m_position SET'
                . ' organization_id = :organization_id'
                . ' , code = :code'
                . ' , position_name = :position_name'
                . ' , labor_regulations_id = :labor_regulations_id'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE position_id = :position_id AND update_time = :update_time ';

            $parameters = array(
                ':organization_id'           => $postArray['organizationID'],
                ':code'                      => $postArray['positionCode'],
                ':position_name'             => $postArray['positionName'],
                ':labor_regulations_id'      => $postArray['laborRegulationsID'],
                ':disp_order'                => $postArray['dispOrder'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':position_id'               => $postArray['positionID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * 役職マスタ登録データ削除
         * @param    $postArray   入力パラメータ(役職ID/削除フラグ1/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            $id_name = "position_id";
            $inUseArray = $this->getInUseCheckList($id_name);
            $intPositionId = intval($postArray['positionID']);
            if(in_array($intPositionId, $inUseArray))
            {
                return "MSG_WAR_2101";
            }

            $sql = 'UPDATE m_position SET'
                . ' is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE position_id = :position_id AND update_time = :update_time ';

            $parameters = array(
                ':is_del'                    => $postArray['is_del'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':position_id'               => $postArray['positionID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * 役職マスタの検索用コードのプルダウン
         * @return   コードリスト(コード) 
         */
        public function getSearchCodeList()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchCodeList");

            $sql = ' SELECT DISTINCT code, organization_id FROM m_position '
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
         * 役職マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/positionName/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   役職マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");
            
            $searchedColumn = ' AND organization.';
            $sqlWhereIn = $this->creatSqlWhereInConditions($searchedColumn);

            $sql = ' SELECT p.position_id, p.position_name, p.is_del, p.code, p.labor_regulations_id, mlr.labor_regulations_name, p.update_time, '
                 . '        p.disp_order, organization.organization_id, mod.abbreviated_name '
                 . ' FROM m_position p INNER JOIN m_organization_detail mod ON p.organization_id = mod.organization_id '
                 . '       LEFT OUTER JOIN m_labor_regulations mlr ON p.labor_regulations_id = mlr.labor_regulations_id , '
                 . '     ( SELECT od.organization_id, MAX(od.application_date_start) as application_date_start '
                 . '       FROM m_organization_detail od INNER JOIN m_organization o ON od.organization_id = o.organization_id '
                 . '       WHERE  o.is_del = 0 '
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
            if( !empty( $postArray['positionName'] ) )
            {
                $sql .= ' AND p.position_name = :positionName ';
                $positionNameArray = array(':positionName' => $postArray['positionName'],);
                $searchArray = array_merge($searchArray, $positionNameArray);
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
         * 役職マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   役職マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = 'ORDER BY p.disp_order, p.code ';

            // ソート条件作成
            $sortSqlList = array(
                                3       =>  ' ORDER BY p.is_del DESC, organization.organization_id, p.disp_order, p.code',               // 状態の降順
                                4       =>  ' ORDER BY p.is_del, organization.organization_id, p.disp_order, p.code',                    // 状態の昇順
                                5       =>  ' ORDER BY p.code DESC, organization.organization_id, p.disp_order',                         // コードの降順
                                6       =>  ' ORDER BY p.code, organization.organization_id, p.disp_order',                              // コードの昇順
                                7       =>  ' ORDER BY mod.abbreviated_name DESC, organization.organization_id, p.disp_order, p.code',   // 組織名の降順
                                8       =>  ' ORDER BY mod.abbreviated_name, organization.organization_id, p.disp_order, p.code',        // 組織名の昇順
                                9       =>  ' ORDER BY p.position_name DESC, organization.organization_id, p.disp_order, p.code',        // 役職名の降順
                                10       =>  ' ORDER BY p.position_name, organization.organization_id, p.disp_order, p.code',             // 役職名の昇順
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
         * 役職マスタ更新時使用済み役職データ組織ID取得
         * @param    $positionID   入力パラメータ(役職ID)
         * @return   組織ID
         */
        private function getUseDataOrganizationId( $positionID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUseDataOrganizationId");

            $sql = 'SELECT organization_id FROM m_position  WHERE position_id = :position_id';
            $searchArray = array(':position_id' => $positionID,);
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
