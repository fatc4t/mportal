<?php
    /**
     * @file      休日マスタ
     * @author    USE S.Kasai
     * @date      2016/06/10
     * @version   1.00
     * @note      休日マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 休日マスタクラス
     * @note   休日マスタテーブルの管理を行う
     */
    class Holiday extends BaseModel
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
         * 休日マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(organization_id/is_del/organizationID/holidayName/holidayNameID/sort)
         * @return   成功時：$holidayList(organization_id/holiday_name/holiday_name_id/is_del/code/disp_order/organization_id/abbreviated_name)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );
            $result = $DBA->executeSQL($sql, $searchArray);

            $holidayDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $holidayDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $holidayDataList, $data);
            }

            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $holidayList = $holidayDataList;
            }
            else
            {
                $holidayList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $holidayDataList);
                if( $postArray['sort'] == 1 )
                {
                    $holidayList = array_reverse($holidayList);
                }
            }

            $Log->trace("END getListData");

            return $holidayList;
        }

        /**
         * 休日マスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/組織ID/休日名称名/休日名/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO m_holiday( organization_id'
                . '                    , code'
                . '                    , holiday'
                . '                    , holiday_name_id'
                . '                    , working_hours'
                . '                    , working_day'
                . '                    , comment'
                . '                    , is_del'
                . '                    , disp_order'
                . '                    , registration_time'
                . '                    , registration_user_id'
                . '                    , registration_organization'
                . '                    , update_time'
                . '                    , update_user_id'
                . '                    , update_organization'
                . '                    ) VALUES ('
                . '                    :organization_id'
                . '                    , :code'
                . '                    , :holiday'
                . '                    , :holiday_name_id'
                . '                    , :working_hours'
                . '                    , :working_day'
                . '                    , :comment'
                . '                    , :is_del'
                . '                    , :disp_order'
                . '                    , current_timestamp'
                . '                    , :registration_user_id'
                . '                    , :registration_organization'
                . '                    , current_timestamp'
                . '                    , :update_user_id'
                . '                    , :update_organization)';

            $parameters = array(
                ':organization_id'           => $postArray['organizationID'],
                ':code'                      => $postArray['holidayCode'],
                ':holiday'                   => $postArray['holidayName'],
                ':holiday_name_id'           => $postArray['holidayNameID'],
                ':working_hours'             => $postArray['workingHours'],
                ':working_day'               => $postArray['workingDay'],
                ':comment'                   => $postArray['comment'],
                ':is_del'                    => $postArray['is_del'],
                ':disp_order'                => $postArray['dispOrder'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "m_holiday" );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * 休日マスタ登録データ修正
         * @param    $postArray   入力パラメータ(休日ID/コード/組織ID/休日名称名/休日名/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            

            $sql = 'UPDATE m_holiday SET'
                . ' organization_id = :organization_id'
                . ' , code = :code'
                . ' , holiday_name_id = :holiday_name_id'
                . ' , holiday = :holiday'
                . ' , working_hours = :working_hours'
                . ' , working_day = :working_day'
                . ' , comment = :comment'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE holiday_id = :holiday_id AND update_time = :update_time ';

            $parameters = array(
                ':organization_id'           => $postArray['organizationID'],
                ':code'                      => $postArray['holidayCode'],
                ':holiday_name_id'           => $postArray['holidayNameID'],
                ':holiday'                   => $postArray['holidayName'],
                ':working_hours'             => $postArray['workingHours'],
                ':working_day'               => $postArray['workingDay'],
                ':comment'                   => $postArray['comment'],
                ':disp_order'                => $postArray['dispOrder'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':holiday_id'                => $postArray['holidayID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * 休日マスタ登録データ削除
         * @param    $postArray   入力パラメータ(休日ID/削除フラグ1/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            $sql = 'UPDATE m_holiday SET'
                . ' is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE holiday_id = :holiday_id AND update_time = :update_time ';

            $parameters = array(
                ':is_del'                    => $postArray['is_del'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':holiday_id'                => $postArray['holidayID'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * 休日マスタの検索用コードのプルダウン
         * @return   コードリスト(コード) 
         */
        public function getSearchCodeList()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchCodeList");

            $sql = ' SELECT DISTINCT code, organization_id FROM m_holiday '
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
         * 休日マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/holidayName/holidayNameID/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   休日マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $searchedColumn = ' AND organization.';
            $sqlWhereIn = $this->creatSqlWhereInConditions($searchedColumn);

            $sql = ' SELECT h.holiday_id, h.holiday, h.is_del, h.code, h.holiday_name_id, mhn.holiday_name, h.update_time, '
                 . '        h.disp_order, organization.organization_id, mod.abbreviated_name, h.working_hours, h.working_day, h.comment '
                 . ' FROM m_holiday h INNER JOIN m_organization_detail mod ON h.organization_id = mod.organization_id '
                 . '       INNER JOIN m_holiday_name mhn ON h.holiday_name_id = mhn.holiday_name_id , '
                 . '     ( SELECT od.organization_id, MAX(od.application_date_start) as application_date_start '
                 . '       FROM m_organization_detail od INNER JOIN m_organization o ON od.organization_id = o.organization_id '
                 . '       WHERE od.application_date_start <= current_date AND o.is_del = 0 '
                 . '       GROUP BY od.organization_id, od.department_code, o.disp_order'
                 . '       ORDER BY o.disp_order, od.department_code ) organization '
                 . ' WHERE h.organization_id = organization.organization_id '
                 . '       AND mod.application_date_start = organization.application_date_start ';
            $sql .= $sqlWhereIn;

            $sql .= $this->creatWhereSQL($postArray, $searchArray);

            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 休日マスタ一覧の検索条件リスト作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/holidayName/holidayNameID/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   休日マスタ一覧検索条件リストSQL文
         */
        private function creatWhereSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatWhereSQL");

            $searchArray = array();
            $sql = "";
            
            // 検索条件リスト作成
            $whereSqlList = array(
                                'organizationID'      =>  ' AND h.organization_id = :organizationID ',      // 
                                'holidayName'         =>  ' AND h.holiday = :holidayName ',                 // 
                                'holidayNameID'       =>  ' AND mhn.holiday_name = :holidayNameID ',       // 
                                'codeID'              =>  ' AND h.code = :codeID ',                         // 
                                'workingHoursStart'   =>  ' AND h.working_hours >= :workingHoursStart ',    // 
                                'workingHoursEnd'     =>  ' AND h.working_hours <= :workingHoursEnd ',      // 
                                'workingDayStart'     =>  ' AND h.working_day >= :workingDayStart ',        // 
                                'workingDayEnd'       =>  ' AND h.working_day <= :workingDayEnd ',          // 
                            );
            
            foreach($whereSqlList as $key => $val)
            {
                if( !empty( $postArray[$key] ) )
                {
                    $sql .= $val;
                    $param = ':' . $key;
                    $searchParamArray = array( $param => $postArray[$key],);
                    $searchArray = array_merge($searchArray, $searchParamArray);
                }
                if( !empty( $postArray['comment'] ) )
                {
                    $sql .= ' AND h.comment LIKE :comment ';
                    $commentArray = array(':comment' => "%" . $postArray['comment'] . "%" ,);
                    $searchArray = array_merge($searchArray, $commentArray);
                }
                if( $postArray['is_del'] == 0 )
                {
                    $sql .= ' AND h.is_del = :is_del ';
                    $isDelArray = array(':is_del' => $postArray['is_del'],);
                    $searchArray = array_merge($searchArray, $isDelArray);
                }
            }

            $Log->trace("END creatWhereSQL");
            return $sql;
        }

        /**
         * 休日マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   休日マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = ' ORDER BY h.disp_order, h.code ';

            // ソート条件作成
            $sortSqlList = array(
                                3       =>  ' ORDER BY h.is_del DESC, organization.organization_id, h.disp_order, h.code',               // 状態の降順
                                4       =>  ' ORDER BY h.is_del, organization.organization_id, h.disp_order, h.code',                    // 状態の昇順
                                5       =>  ' ORDER BY h.code DESC, organization.organization_id, h.disp_order',                         // コードの降順
                                6       =>  ' ORDER BY h.code, organization.organization_id, h.disp_order',                              // コードの昇順
                                7       =>  ' ORDER BY mod.abbreviated_name DESC, organization.organization_id, h.disp_order, h.code',   // 組織名の降順
                                8       =>  ' ORDER BY mod.abbreviated_name, organization.organization_id, h.disp_order, h.code',        // 組織名の昇順
                                9       =>  ' ORDER BY mhn.holiday_name DESC, organization.organization_id, h.disp_order, h.code',       // 休日名称の降順
                                10      =>  ' ORDER BY mhn.holiday_name, organization.organization_id, h.disp_order, h.code',            // 休日名称の昇順
                                11      =>  ' ORDER BY h.holiday DESC, organization.organization_id, h.disp_order, h.code',              // 休日名の降順
                                12      =>  ' ORDER BY h.holiday, organization.organization_id, h.disp_order, h.code',                   // 休日名の昇順
                                13      =>  ' ORDER BY h.working_hours DESC, organization.organization_id, h.disp_order, h.code',        // 勤務時間の降順
                                14      =>  ' ORDER BY h.working_hours, organization.organization_id, h.disp_order, h.code',             // 勤務時間の昇順
                                15      =>  ' ORDER BY h.working_day DESC, organization.organization_id, h.disp_order, h.code',          // 勤務日数の降順
                                16      =>  ' ORDER BY h.working_day, organization.organization_id, h.disp_order, h.code',               // 勤務日数の昇順
                                17      =>  ' ORDER BY h.comment DESC, organization.organization_id, h.disp_order, h.code',              // コメントの降順
                                18      =>  ' ORDER BY h.comment, organization.organization_id, h.disp_order, h.code',                   // コメントの昇順
                                19      =>  ' ORDER BY h.disp_order DESC, organization.organization_id, h.code',                         // 表示順の降順
                                20      =>  ' ORDER BY h.disp_order, organization.organization_id, h.code',                              // 表示順の昇順
                            );
            // ソート条件
            if( array_key_exists( $sortNo, $sortSqlList ) )
            {
                $sql = $sortSqlList[$sortNo];
            }

            $Log->trace("END creatSortSQL");
            return $sql;
        }
    }
?>
