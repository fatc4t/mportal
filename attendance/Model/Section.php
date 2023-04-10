<?php
    /**
     * @file      セクションマスタ
     * @author    USE M.Higashihara
     * @date      2016/05/25
     * @version   1.00
     * @note      セクションマスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * セクションマスタクラス
     * @note   セクションマスタテーブルの管理を行う
     */
    class Section extends BaseModel
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
         * セクションマスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(organization_id/is_del/organizationID/sectionName/sort)
         * @return   成功時：$sectionList(organization_id/section_name/is_del/code/disp_order/organization_id/abbreviated_name)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $sectionDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $sectionDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $sectionDataList, $data);
            }

            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $sectionList = $sectionDataList;
            }
            else
            {
                $sectionList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $sectionDataList);
                if( $postArray['sort'] == 1 )
                {
                    $sectionList = array_reverse($sectionList);
                }
            }

            $Log->trace("END getListData");

            return $sectionList;
        }

        /**
         * セクションマスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/組織ID/セクション名/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO m_section( organization_id'
                . '                      , code'
                . '                      , section_name'
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
                . '                      , :section_name'
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
                ':code'                      => $postArray['sectionCode'],
                ':section_name'              => $postArray['sectionName'],
                ':is_del'                    => $postArray['is_del'],
                ':disp_order'                => $postArray['dispOrder'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "m_section" );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * セクションマスタ登録データ修正
         * @param    $postArray   入力パラメータ(セクションID/コード/組織ID/セクション名/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $id_name = "section_id";
            $inUseArray = $this->getInUseCheckList($id_name);
            // POSTで来たセクションIDを数値化する
            $intSectionId = intval($postArray['sectionID']);
            // 使用済みのセクション情報の判定
            if(in_array($intSectionId, $inUseArray))
            {
                // 使用中の場合、組織名を変更するときにはエラーとする
                $used_organization_id = $this->getUseDataOrganizationId($intSectionId);
                // POSTで来た組織IDを数値化する
                $intOrganizationId = intval($postArray['organizationID']);
                if($used_organization_id != $intOrganizationId)
                {
                    $Log->trace("END modUpdateData");
                    return "MSG_WAR_2100";
                }
            }

            $sql = 'UPDATE m_section SET'
                . ' organization_id = :organization_id'
                . ' , code = :code'
                . ' , section_name = :section_name'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE section_id = :section_id AND update_time = :update_time ';

            $parameters = array(
                ':organization_id'           => $postArray['organizationID'],
                ':code'                      => $postArray['sectionCode'],
                ':section_name'              => $postArray['sectionName'],
                ':disp_order'                => $postArray['dispOrder'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':section_id'                => $postArray['sectionID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * セクションマスタ登録データ削除
         * @param    $postArray   入力パラメータ(セクションID/削除フラグ1/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            $id_name = "section_id";
            $inUseArray = $this->getInUseCheckList($id_name);
            $intSectionId = intval($postArray['sectionID']);
            if(in_array($intSectionId, $inUseArray))
            {
                return "MSG_WAR_2101";
            }

            $sql = 'UPDATE m_section SET'
                . ' is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE section_id = :section_id AND update_time = :update_time ';

            $parameters = array(
                ':is_del'                    => $postArray['is_del'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':section_id'                => $postArray['sectionID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * セクションマスタの検索用コードのプルダウン
         * @return   コードリスト(コード) 
         */
        public function getSearchCodeList()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchCodeList");

            $sql = ' SELECT DISTINCT code, organization_id FROM m_section '
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
         * セクションマスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/sectionName/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   セクションマスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $searchedColumn = ' AND organization.';
            $sqlWhereIn = $this->creatSqlWhereInConditions($searchedColumn);

            $sql = ' SELECT s.section_id, s.section_name, s.is_del, s.code, s.update_time, '
                 . '        s.disp_order, organization.organization_id, mod.abbreviated_name '
                 . ' FROM m_section s INNER JOIN m_organization_detail mod ON s.organization_id = mod.organization_id , '
                 . '     ( SELECT od.organization_id, MAX(od.application_date_start) as application_date_start '
                 . '       FROM m_organization_detail od INNER JOIN m_organization o ON od.organization_id = o.organization_id '
                 . '       WHERE od.application_date_start <= current_date AND o.is_del = 0 '
                 . '       GROUP BY od.organization_id, od.department_code, o.disp_order'
                 . '       ORDER BY o.disp_order, od.department_code ) organization '
                 . ' WHERE s.organization_id = organization.organization_id '
                 . '       AND mod.application_date_start = organization.application_date_start ';
            $sql .= $sqlWhereIn;
            
            if( !empty( $postArray['organizationID'] ) )
            {
                $sql .= ' AND s.organization_id = :organizationID ';
                $organizationIDArray = array(':organizationID' => $postArray['organizationID'],);
                $searchArray = array_merge($searchArray, $organizationIDArray);
            }
            if( !empty( $postArray['sectionName'] ) )
            {
                $sql .= ' AND s.section_name = :sectionName ';
                $sectionNameArray = array(':sectionName' => $postArray['sectionName'],);
                $searchArray = array_merge($searchArray, $sectionNameArray);
            }
            if( !empty( $postArray['codeID'] ) )
            {
                $sql .= ' AND s.code = :codeID ';
                $codeIDArray = array(':codeID' => $postArray['codeID'],);
                $searchArray = array_merge($searchArray, $codeIDArray);
            }
            
            if( $postArray['is_del'] == 0 )
            {
                $sql .= ' AND s.is_del = :is_del ';
                $isDelArray = array(':is_del' => $postArray['is_del'],);
                $searchArray = array_merge($searchArray, $isDelArray);

            }

            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");
            
            return $sql;
        }

        /**
         * セクションマスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   セクションマスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = ' ORDER BY s.disp_order, s.code';

            // ソート条件作成
            $sortSqlList = array(
                                3       =>  ' ORDER BY s.is_del DESC, organization.organization_id, s.disp_order, s.code',              // 状態の降順
                                4       =>  ' ORDER BY s.is_del, organization.organization_id, s.disp_order, s.code',                   // 状態の昇順
                                5       =>  ' ORDER BY s.code DESC, organization.organization_id, s.disp_order',                        // コードの降順
                                6       =>  ' ORDER BY s.code, organization.organization_id, s.disp_order',                             // コードの昇順
                                7       =>  ' ORDER BY mod.abbreviated_name DESC, organization.organization_id, s.disp_order, s.code',  // 組織名の降順
                                8       =>  ' ORDER BY mod.abbreviated_name, organization.organization_id, s.disp_order, s.code',       // 組織名の昇順
                                9       =>  ' ORDER BY s.section_name DESC, organization.organization_id, s.disp_order, s.code',        // セクション名の降順
                               10       =>  ' ORDER BY s.section_name, organization.organization_id, s.disp_order, s.code',             // セクション名の昇順
                               11       =>  ' ORDER BY s.disp_order DESC, organization.organization_id, s.code',                        // 表示順の降順
                               12       =>  ' ORDER BY s.disp_order, organization.organization_id, s.code',                             // 表示順の昇順
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
         * セクションマスタ更新時使用済みセクションデータ組織ID取得
         * @param    $sectionID   セクションID
         * @return   組織ID
         */
        private function getUseDataOrganizationId( $sectionID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUseDataOrganizationId");

            $sql = 'SELECT s.organization_id FROM m_section s WHERE s.section_id = :section_id';
            $searchArray = array(':section_id' => $sectionID,);
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
