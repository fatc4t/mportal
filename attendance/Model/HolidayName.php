<?php
    /**
     * @file      休日名称マスタ
     * @author    USE S.Nakamura
     * @date      2016/06/14
     * @version   1.00
     * @note      休日名称マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * ログインクラス
     * @note   ログイン時の認証処理及び、セッションの初期設定を行う
     */
    class holidayName extends BaseModel
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
         * 休日名称マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(organization_id/is_del/organizationID/holidayName/sort)
         * @return   成功時：$holidayList(organization_id/holiday_name/is_del/code/disp_order/organization_id/abbreviated_name)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $holidayNameDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $holidayNameDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $holidayNameDataList, $data);
            }

            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $holidayList = $holidayNameDataList;
            }
            else
            {
                $holidayList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $holidayNameDataList);
                if( $postArray['sort'] == 1 )
                {
                    $holidayList = array_reverse($holidayList);
                }
            }

            $Log->trace("END getListData");

            return $holidayList;
        }
        /**
         * 休日名称マスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/組織ID/休日名称/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO m_holiday_name( organization_id'
                . '                      , code'
                . '                      , holiday_name'
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
                . '                      , :holiday_name'
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
                ':code'                      => $postArray['holidayNameCode'],
                ':holiday_name'              => $postArray['holidayName'],
                ':is_del'                    => $postArray['is_del'],
                ':disp_order'                => $postArray['dispOrder'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "m_holiday_name" );
            
            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * 休日名称マスタ登録データ修正
         * @param    $postArray   入力パラメータ(休日名称ID/コード/組織ID/休日名称/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $id_name = "holiday_name_id";
            $inUseArray = $this->getUseCheckList($id_name);

            // POSTで来た休日名称IDを数値化する
            $intHolidayNameId = intval($postArray['holidayNameID']);
            // 使用済みの休日名称情報の判定
            if(in_array($intHolidayNameId, $inUseArray))
            {
                // POSTで来た組織IDを数値化する
                $intOrganizationId = intval($postArray['organizationID']);
                // 使用中の場合、組織名を変更するときにはエラーとする
                $used_organization_id = $this->getUseDataOrganizationId($intHolidayNameId);
                if($used_organization_id != $intOrganizationId)
                {
                    $Log->trace("END modUpdateData");
                    return "MSG_WAR_2100";
                }
            }

            $sql = 'UPDATE m_holiday_name SET'
                . ' organization_id = :organization_id'
                . ' , code = :code'
                . ' , holiday_name = :holiday_name'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE holiday_name_id = :holiday_name_id AND update_time = :update_time ';

            $parameters = array(
                ':organization_id'           => $postArray['organizationID'],
                ':code'                      => $postArray['holidayNameCode'],
                ':holiday_name'              => $postArray['holidayName'],
                ':disp_order'                => $postArray['dispOrder'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':holiday_name_id'           => $postArray['holidayNameID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }
        /**
         * 休日名称マスタ登録データ削除
         * @param    $postArray   入力パラメータ(休日名称ID/削除フラグ1/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            $id_name = "holiday_name_id";
            $inUseArray = $this->getUseCheckList($id_name);
            $intHolidayNameId = intval($postArray['holidayNameID']);
            if(in_array($intHolidayNameId, $inUseArray))
            {
                return "MSG_WAR_2101";
            }
           
            $sql = 'UPDATE m_holiday_name SET'
                . ' is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE holiday_name_id = :holiday_name_id AND update_time = :update_time ';

            $parameters = array(
                ':is_del'                    => $postArray['is_del'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':holiday_name_id'           => $postArray['holidayNameID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * 休日名称マスタの検索用コードのプルダウン
         * @return   コードリスト(コード) 
         */
        public function getSearchCodeList()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchCodeList");

            $sql = ' SELECT DISTINCT code, organization_id FROM m_holiday_name '
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
         * 休日名称マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/holidayName/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   休日名称マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");
            
            // 参照権限による検索条件追加
            $searchedColumn = ' AND organization.';
            $sqlWhereIn = $this->creatSqlWhereInConditions($searchedColumn);
            

            $sql = ' SELECT s.holiday_name_id, s.holiday_name, s.is_del, s.code, s.update_time, '
                . '        s.disp_order, organization.organization_id, mod.abbreviated_name '
                . ' FROM m_holiday_name s INNER JOIN m_organization_detail mod ON s.organization_id = mod.organization_id , '
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
            if( !empty( $postArray['holidayNameID'] ) )
            {
                $sql .= ' AND s.holiday_name = :holidayName ';
                $holidayNameArray = array(':holidayName' => $postArray['holidayNameID'],);
                $searchArray = array_merge($searchArray, $holidayNameArray);
                
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
            $Log->debug($sql);

            $Log->trace("END creatSQL");
            
            return $sql;
        }

        /**
         * 休日名称マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   休日名称マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = 'ORDER BY s.disp_order, s.code';

            // ソート条件作成
            $sortSqlList = array(
                                3       =>  ' ORDER BY s.is_del DESC, organization.organization_id, s.disp_order, s.code',              // 状態の降順
                                4       =>  ' ORDER BY s.is_del, organization.organization_id, s.disp_order, s.code',                   // 状態の昇順
                                5       =>  ' ORDER BY s.code DESC, organization.organization_id, s.disp_order',                        // コードの降順
                                6       =>  ' ORDER BY s.code, organization.organization_id, s.disp_order',                             // コードの昇順
                                7       =>  ' ORDER BY mod.abbreviated_name DESC, organization.organization_id, s.disp_order, s.code',  // 組織名の降順
                                8       =>  ' ORDER BY mod.abbreviated_name, organization.organization_id, s.disp_order, s.code',       // 組織名の昇順
                                9       =>  ' ORDER BY s.holiday_name DESC, organization.organization_id, s.disp_order, s.code',        // 休日名称の降順
                               10       =>  ' ORDER BY s.holiday_name, organization.organization_id, s.disp_order, s.code',             // 休日名称の昇順
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
         * 休日名称マスタ更新時使用済み休日名称データ組織ID取得
         * @param    $HolidayNameID   休日名称ID
         * @return   組織ID
         */
        private function getUseDataOrganizationId( $holidayNameID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUseDataOrganizationId");

            $sql = 'SELECT s.organization_id FROM m_holiday_name s '
                .  ' WHERE s.holiday_name_id = :holiday_name_id ';
            $searchArray = array(':holiday_name_id' => $holidayNameID,);

            // sql実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            $organization_id = -1;
            if( $result === false )
            {
                $Log->trace("END getUseDataOrganizationId");
                return $organization_id;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $organization_id = $data['organization_id'];
            }
            
            $Log->trace("END getUseDataOrganizationId");
            return $organization_id;
        }

        /**
         * 使用中のマスタ情報を取得
         * @param   $idName（各機能のシーケンスID名）
         * @return  呼び出し先のシーケンスIDリスト
         */
        private function getUseCheckList($id_Name)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUseCheckList");

            $sql = 'SELECT ud.organization_id, ud.holiday_name_id FROM m_holiday ud';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $inUseCheckArray = array();
            
            if( $result === false )
            {
                $Log->trace("END getUseCheckList");
                return $inUseCheckArray;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($inUseCheckArray, $data[$id_Name]);
            }
            
            //配列で重複している物を削除する
            $unique = array_unique($inUseCheckArray);
            //キーが飛び飛びになっているので、キーを振り直す
            $inUseCheckList = array_values($unique);

            $Log->trace("END getUseCheckList");

            return $inUseCheckList;
        }
    }
?>
