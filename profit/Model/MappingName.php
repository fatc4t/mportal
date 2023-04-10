<?php
    /**
     * @file      マッピング名マスタ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      マッピング名マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * マッピング名クラス
     * @note   マッピング名マスタテーブルの管理を行う
     */
    class MappingName extends BaseModel
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
         * マッピング名一覧画面一覧表
         * @param    $postArray   入力パラメータ(is_del/sort)
         * @return   成功時：$mappingNameList(mapping_name_id/mapping_code/mapping_type/mapping_name/mapping_name_kana/link/list_f/disp_order/is_del)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $mappingNameDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $mappingNameDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mappingNameDataList, $data);
            }
            
            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $mappingNameList = $mappingNameDataList;
            }
            else
            {
               // $posBrandList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $posBrandDataList);
                $mappingNameList = $mappingNameDataList;
                if( $postArray['sort'] == 1 )
                {
                    $mappingNameList = array_reverse($mappingNameList);
                }
            }

            $Log->trace("END getListData");

            return $mappingNameList;
        }

        /**
         * マッピング名マスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/マッピング名/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO m_mapping_name( mapping_code'
                . '                      , mapping_type'
                . '                      , mapping_name'
                . '                      , mapping_name_kana'
                . '                      , link'
                . '                      , list_f'
                . '                      , disp_order'
                . '                      , is_del'
                . '                      , registration_time'
                . '                      , registration_user_id'
                . '                      , registration_organization'
                . '                      , update_time'
                . '                      , update_user_id'
                . '                      , update_organization'
                . '                      ) VALUES ('
                . '                        :mapping_code'
                . '                      , :mapping_type'
                . '                      , :mapping_name'
                . '                      , :mapping_name_kana'
                . '                      , :link'
                . '                      , :list_f'
                . '                      , :disp_order'
                . '                      , :is_del'
                . '                      , current_timestamp'
                . '                      , :registration_user_id'
                . '                      , :registration_organization'
                . '                      , current_timestamp'
                . '                      , :update_user_id'
                . '                      , :update_organization)';

            $parameters = array(
                ':mapping_code'              => $postArray['mappingCode'],
                ':mapping_type'              => $postArray['mappingType'],
                ':mapping_name'              => $postArray['mappingName'],
                ':mapping_name_kana'         => $postArray['mappingNameKana'],
                ':link'                      => $postArray['link'],
                ':list_f'                    => $postArray['listF'],
                ':disp_order'                => $postArray['dispOrder'],
                ':is_del'                    => $postArray['is_del'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "m_mapping_name" );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * マッピング名マスタ登録データ修正
         * @param    $postArray   入力パラメータ(マッピング名ID/コード/組織ID/マッピング名/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $sql = 'UPDATE m_mapping_name SET'
                . '   mapping_code = :mapping_code'
                . ' , mapping_type = :mapping_type'
                . ' , mapping_name = :mapping_name'
                . ' , mapping_name_kana = :mapping_name_kana'
                . ' , link = :link'
                . ' , list_f = :list_f'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE mapping_name_id = :mapping_name_id AND update_time = :update_time ';

            $parameters = array(
                ':mapping_name_id'         => $postArray['mappingNameId'],
                ':mapping_code'            => $postArray['mappingCode'],
                ':mapping_type'            => $postArray['mappingType'],
                ':mapping_name'            => $postArray['mappingName'],
                ':mapping_name_kana'       => $postArray['mappingNameKana'],
                ':link'                    => $postArray['link'],
                ':list_f'                  => $postArray['listF'],
                ':disp_order'              => $postArray['dispOrder'],
                ':update_user_id'          => $postArray['user_id'],
                ':update_organization'     => $postArray['organization'],
                ':update_time'             => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * マッピング名マスタ登録データ削除
         * @param    $postArray   入力パラメータ(マッピング名ID/削除フラグ1/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

/* 使用中のチェック後で追加予定 2016/07/19 
            $id_name = "pos_brand_id";
            $inUseArray = $this->getInUseCheckList($id_name);
            $intPosBrandId = intval($postArray['posBrandID']);
            if(in_array($intPosBrandId, $inUseArray))
            {
                return "MSG_WAR_2101";
            }
*/
            $sql = 'UPDATE m_mapping_name SET'
                . '   is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE mapping_name_id = :mapping_name_id AND update_time = :update_time ';

            $parameters = array(
                ':mapping_name_id'           => $postArray['mappingNameId'],
                ':is_del'                    => $postArray['is_del'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * マッピング名一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/sectionName/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   マッピング名マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = " SELECT  * "
                 . "        , CASE WHEN mapping_type = 11 THEN '売上'"
                 . "               WHEN mapping_type = 12 THEN 'コスト(日)'"
                 . "               WHEN mapping_type = 13 THEN 'コスト(月)'"
                 . "               WHEN mapping_type = 14 THEN '目標(日)'"
                 . "               WHEN mapping_type = 15 THEN '目標(月)'"
                 . "               WHEN mapping_type = 16 THEN '仕入'"
                 . "               WHEN mapping_type = 17 THEN '勤怠'"
                 . "               WHEN mapping_type = 18 THEN 'その他'"
                 . "               WHEN mapping_type = 19 THEN '商品別'"
                 . "               WHEN mapping_type = 20 THEN '時間別'"
                 . "           END as mapping_type_name"
                 . "        , CASE WHEN link = 0 THEN '自動'"
                 . "               WHEN link = 1 THEN '手動'"
                 . "               WHEN link = 2 THEN '入力'"
                 . "           END as link_name"
                 . "        , CASE WHEN list_f = 0 THEN '表示しない'"
                 . "               WHEN list_f = 1 THEN '表示する'"
                 . "           END as list_f_name"
                 . "   FROM m_mapping_name"
                 . "  WHERE true"  ;
            
            if( !empty( $postArray['mappingName'] ) )
            {
                $sql .= " AND mapping_name like :mappingName";
                $mappingNameArray = array(':mappingName' => '%'.$postArray['mappingName'].'%',);
                $searchArray = array_merge($searchArray, $mappingNameArray);
            }
            
            if( !empty( $postArray['mappingType'] ) )
            {
                $sql .= ' AND mapping_type = :mappingType ';
                $mappingNameArray = array(':mappingType' => $postArray['mappingType'],);
                $searchArray = array_merge($searchArray, $mappingNameArray);
            }

            if( !empty( $postArray['link'] ) )
            {
                $sql .= ' AND link = :link ';
                $mappingNameArray = array(':link' => $postArray['link'],);
                $searchArray = array_merge($searchArray, $mappingNameArray);
            }

            if( !empty( $postArray['is_del'] ) )
            {
                $sql .= ' AND is_del = :is_del ';
                $isDelArray = array(':is_del' => $postArray['is_del'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty( $postArray['mcode'] ) )
            {
                $sql .= ' AND mapping_code = :mcode ';
                $isDelArray = array(':mcode' => $postArray['mcode'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");
            
            return $sql;
        }

        /**
         * マッピング名マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   マッピング名マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = ' ORDER BY disp_order,mapping_code';

            // ソート条件作成
            $sortSqlList = array(
                                3       =>  ' ORDER BY disp_order DESC',                         // 表示順の降順
                                4       =>  ' ORDER BY disp_order',                              // 表示順の昇順
                                5       =>  ' ORDER BY mapping_type DESC, mapping_code',         // タイプの降順
                                6       =>  ' ORDER BY mapping_type, mapping_code',              // タイプの昇順
                                7       =>  ' ORDER BY link DESC, mapping_code',                 // 連携の降順
                                8       =>  ' ORDER BY link, mapping_code',                      // 連携の昇順
                                9       =>  ' ORDER BY list_f DESC, mapping_code',               // 報告書一覧の降順
                               10       =>  ' ORDER BY list_f, mapping_code',                    // 報告書一覧の昇順
                               11       =>  ' ORDER BY mapping_code DESC',                       // Mコードの降順
                               12       =>  ' ORDER BY mapping_code',                            // Mコードの昇順
                               13       =>  ' ORDER BY mapping_name DESC, mapping_code',         // 種別名の降順
                               14       =>  ' ORDER BY mapping_name, mapping_code',              // 種別名の昇順
                               15       =>  ' ORDER BY mapping_name_kana DESC, mapping_code',    // よみがなの降順
                               16       =>  ' ORDER BY mapping_name_kana, mapping_code',         // よみがなの昇順
                               17       =>  ' ORDER BY registration_time DESC, mapping_code',    // 初回登録日の降順
                               18       =>  ' ORDER BY registration_time, mapping_code',         // 初回登録日の昇順
                               19       =>  ' ORDER BY update_time DESC, mapping_code',          // 更新日の降順
                               20       =>  ' ORDER BY update_time, mapping_code',               // 更新日の昇順
                               21       =>  ' ORDER BY is_del DESC, mapping_code',               // 状態の降順
                               22       =>  ' ORDER BY is_del, mapping_code',                    // 状態の昇順
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
         * タイプリスト作成
         * @param    $postArray                 入力パラメータ()
         * @param    $searchArray               検索条件用パラメータ
         * @return   タイププルダウン
         */
        public function getMappingTypeList( $authority = 'reference' )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getMappingTypeList List");

            $mappingTypeList = array();

            // 最初の空行を追加
            $mappingType = array(
                   'mapping_type'      => "",
                   'mapping_type_name' => "----",
                );
            array_push($mappingTypeList, $mappingType);

            $mappingType = array(
                   'mapping_type'      => "11",
                   'mapping_type_name' => "売上",
               );
            array_push($mappingTypeList, $mappingType);

            $mappingType = array(
                   'mapping_type'      => "12",
                   'mapping_type_name' => "コスト(日)",
               );
            array_push($mappingTypeList, $mappingType);

            $mappingType = array(
                   'mapping_type'      => "13",
                   'mapping_type_name' => "コスト(月)",
               );
            array_push($mappingTypeList, $mappingType);

            $mappingType = array(
                   'mapping_type'      => "14",
                   'mapping_type_name' => "目標(日)",
               );
            array_push($mappingTypeList, $mappingType);

            $mappingType = array(
                   'mapping_type'      => "15",
                   'mapping_type_name' => "目標(月)",
               );
            array_push($mappingTypeList, $mappingType);

            $mappingType = array(
                   'mapping_type'      => "16",
                   'mapping_type_name' => "仕入",
               );
            array_push($mappingTypeList, $mappingType);

            $mappingType = array(
                   'mapping_type'      => "17",
                   'mapping_type_name' => "勤怠",
               );
            array_push($mappingTypeList, $mappingType);

            $mappingType = array(
                   'mapping_type'      => "19",
                   'mapping_type_name' => "商品別",
               );
            array_push($mappingTypeList, $mappingType);

            $mappingType = array(
                   'mapping_type'      => "20",
                   'mapping_type_name' => "時間別",
               );
            array_push($mappingTypeList, $mappingType);

            $mappingType = array(
                   'mapping_type'      => "18",
                   'mapping_type_name' => "その他",
               );
            array_push($mappingTypeList, $mappingType);

            $Log->trace("END getMappingTypeList");
            
            return $mappingTypeList;
        }

        /**
         * 連携リスト作成
         * @param    $postArray                 入力パラメータ()
         * @param    $searchArray               検索条件用パラメータ
         * @return   連携リストプルダウン
         */
        public function getLinkList( $authority = 'reference' )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getLinkList List");

            $linkList = array();

            // 最初の空行を追加
            $link = array(
                   'link'      => "",
                   'link_name' => "----",
                );
            array_push($linkList, $link);

            $link = array(
                   'link'      => "0",
                   'link_name' => "自動",
               );
            array_push($linkList, $link);

            $link = array(
                   'link'      => "1",
                   'link_name' => "手動",
               );
            array_push($linkList, $link);

            $link = array(
                   'link'      => "2",
                   'link_name' => "入力",
               );
            array_push($linkList, $link);

            $Log->trace("END getLinkList");
            
            return $linkList;
        }

        /**
         * 報告書一覧作成
         * @param    $postArray                 入力パラメータ()
         * @param    $searchArray               検索条件用パラメータ
         * @return   報告書一覧取得SQL
         */
        public function getListFList( $authority = 'reference' )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getListFList List");

            $listFList = array();

            // 最初の空行を追加
            $listF = array(
                   'list_f'      => "",
                   'list_f_name' => "----",
                );
            array_push($listFList, $listF);

            $listF = array(
                   'list_f'      => "0",
                   'list_f_name' => "表示しない",
               );

            $listF = array(
                   'list_f'      => "1",
                   'list_f_name' => "表示する",
               );

            array_push($listFList, $listF);

            $Log->trace("END getListFList");
            
            return $listFList;
        }

    }
?>
