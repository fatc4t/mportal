<?php
    /**
     * @file      日次マッピングマスタ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      マッピング名マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 日次マッピングクラス
     * @note   日次マッピングマスタテーブルの管理を行う
     */
    class PosMapping extends BaseModel
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
         * 日次マッピング一覧画面一覧表
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
         * 日次マッピングマスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/マッピング名/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO m_pos_mapping( pos_brand_id'
                . '                      , mapping_name_id'
                . '                      , logic_type'
                . '                      , logic'
                . '                      , keta'
                . '                      , symbol'
                . '                      , round_type'
                . '                      , pos_key_file_id'
                . '                      , disp_order'
                . '                      , is_del'
                . '                      , registration_time'
                . '                      , registration_user_id'
                . '                      , registration_organization'
                . '                      , update_time'
                . '                      , update_user_id'
                . '                      , update_organization'
                . '                      ) VALUES ('
                . '                        :pos_brand_id'
                . '                      , :mapping_name_id'
                . '                      , :logic_type'
                . '                      , :logic'
                . '                      , :keta'
                . '                      , :symbol'
                . '                      , :round_type'
                . '                      , :pos_key_file_id'
                . '                      , :disp_order'
                . '                      , :is_del'
                . '                      , current_timestamp'
                . '                      , :registration_user_id'
                . '                      , :registration_organization'
                . '                      , current_timestamp'
                . '                      , :update_user_id'
                . '                      , :update_organization)';

            $parameters = array(
                ':pos_brand_id'              => $postArray['posBrandId'],
                ':mapping_name_id'           => $postArray['mappingNameId'],
                ':logic_type'                => $postArray['logicType'],
                ':logic'                     => $postArray['logic'],
                ':keta'                      => $postArray['keta'],
                ':symbol'                    => $postArray['symbol'],
                ':round_type'                => $postArray['roundType'],
                ':pos_key_file_id'           => $postArray['posKeyFileId'],
                ':disp_order'                => $postArray['dispOrder'],
                ':is_del'                    => $postArray['is_del'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "m_pos_mapping" );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * 日次マッピングマスタ登録データ修正
         * @param    $postArray   入力パラメータ(マッピング名ID/コード/組織ID/マッピング名/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $sql = 'UPDATE m_pos_mapping SET'
                . '   pos_brand_id = :pos_brand_id'
                . ' , mapping_name_id = :mapping_name_id'
                . ' , logic_type = :logic_type'
                . ' , logic = :logic'
                . ' , keta = :keta'
                . ' , symbol = :symbol'
                . ' , round_type = :round_type'
                . ' , pos_key_file_id = :pos_key_file_id'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE pos_mapping_id = :pos_mapping_id AND update_time = :update_time ';

            $parameters = array(
                ':pos_mapping_id'          => $postArray['posMappingId'],
                ':pos_brand_id'            => $postArray['posBrandId'],
                ':mapping_name_id'         => $postArray['mappingNameId'],
                ':logic_type'              => $postArray['logicType'],
                ':logic'                   => $postArray['logic'],
                ':keta'                    => $postArray['keta'],
                ':symbol'                  => $postArray['symbol'],
                ':round_type'              => $postArray['roundType'],
                ':pos_key_file_id'         => $postArray['posKeyFileId'],
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
         * 日次マッピングマスタ登録データ削除
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
            $sql = 'UPDATE m_pos_mapping SET'
                . '   is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE pos_mapping_id = :pos_mapping_id AND update_time = :update_time ';

            $parameters = array(
                ':pos_mapping_id'           => $postArray['posMappingId'],
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
         * 日次マッピング一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/sectionName/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   マッピング名マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = " SELECT  p.*"
                 . "        ,b.pos_brand_name"
                 . "        ,k.pos_key_file_name"
                 . "        ,m.mapping_code"
                 . "        ,m.mapping_name"
                 . "        , CASE WHEN p.logic_type = 0 THEN '取得'"
                 . "               WHEN p.logic_type = 1 THEN '計算式'"
                 . "           END as logic_type_name"
                 . "        , CASE WHEN p.round_type = 0 THEN '切捨て'"
                 . "               WHEN p.round_type = 1 THEN '切上げ'"
                 . "               WHEN p.round_type = 2 THEN '四捨五入'"
                 . "           END as round_type_name"
                 . "   FROM m_pos_mapping p"
                 . "   left join m_pos_brand b on (p.pos_brand_id = b.pos_brand_id)"
                 . "   left join m_pos_key_file k on (p.pos_key_file_id = k.pos_key_file_id)"
                 . "   left join m_mapping_name m on (p.mapping_name_id = m.mapping_name_id)"
                 . "  WHERE true"  ;
            
            if( !empty( $postArray['posBrandId'] ) )
            {
                $sql .= " AND p.pos_brand_id = :posBrandId";
                $mappingNameArray = array(':posBrandId' => $postArray['posBrandId'],);
                $searchArray = array_merge($searchArray, $mappingNameArray);
            }
            
            if( !empty( $postArray['mappingNameId'] ) )
            {
                $sql .= ' AND p.mapping_name_id = :mappingNameId ';
                $mappingNameArray = array(':mappingNameId' => $postArray['mappingNameId'],);
                $searchArray = array_merge($searchArray, $mappingNameArray);
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
         * 日次マッピングマスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   マッピング名マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = ' ORDER BY p.disp_order';

            // ソート条件作成
            $sortSqlList = array(
                                3       =>  ' ORDER BY p.disp_order DESC',         // 表示順の降順
                                4       =>  ' ORDER BY p.disp_order',              // 表示順の昇順
                                5       =>  ' ORDER BY b.pos_brand_name DESC',     // POS種別名の降順
                                6       =>  ' ORDER BY b.pos_brand_name',          // POS種別名の昇順
                                7       =>  ' ORDER BY m.mapping_code DESC',       // Mコードの降順
                                8       =>  ' ORDER BY m.mapping_code',            // Mコードの昇順
                                9       =>  ' ORDER BY m.mapping_name DESC',       // POSマッピング名の降順
                               10       =>  ' ORDER BY m.mapping_name',            // POSマッピング名の昇順
                               11       =>  ' ORDER BY p.logic_type DESC',         // タイプの降順
                               12       =>  ' ORDER BY p.logic_type',              // タイプの昇順
                               13       =>  ' ORDER BY p.logic DESC',              // ロジックの降順
                               14       =>  ' ORDER BY p.logic',                   // ロジックの昇順
                               15       =>  ' ORDER BY k.pos_key_file_name DESC',  // キーファイル名の降順
                               16       =>  ' ORDER BY k.pos_key_file_name',       // キーファイル名の昇順
                               17       =>  ' ORDER BY keta DESC ',                // 端数処理 桁数の降順
                               18       =>  ' ORDER BY keta',                      // 端数処理 桁数の昇順
                               19       =>  ' ORDER BY round_type DESC',           // 端数処理 タイプの降順
                               20       =>  ' ORDER BY round_type',                // 端数処理 タイプの昇順
                               21       =>  ' ORDER BY symbol DESC',               // 単位の降順
                               22       =>  ' ORDER BY symbol',                    // 単位の昇順
                               23       =>  ' ORDER BY registration_time DESC',    // 初回登録日の降順
                               24       =>  ' ORDER BY registration_time',         // 初回登録日の昇順
                               25       =>  ' ORDER BY update_time DESC',          // 更新日の降順
                               26       =>  ' ORDER BY update_time',               // 更新日の昇順
                               27       =>  ' ORDER BY is_del DESC',               // 状態の降順
                               28       =>  ' ORDER BY is_del',                    // 状態の昇順
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
         * POS種別名リスト作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/sectionName/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   POS種別名マスタプルダウンリスト
         */
        public function getPosBrandList( $authority = 'reference' )
        {
            global $DBA,$Log; // グローバル変数宣言
            $Log->trace("START getPosBrandList List");

            $sql = ' SELECT pos_brand_id,pos_brand_name'
                 . '   FROM m_pos_brand '
                 . '  WHERE is_del = 0 '
                 . '  ORDER BY disp_order '  ;

            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $posBrandList = array();

            if( $result === false )
            {
                $Log->trace("END getPosBrandList");
                return $posBrandList;
            }

            // 最初の空行を追加
            $posBrand = array(
                   'pos_brand_id'   => "",     // POS種別ID
                   'pos_brand_name' => "----",  // POS種別名
                );
            array_push($posBrandList, $posBrand);

            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
               $posBrand = array(
                   'pos_brand_id'  => $data['pos_brand_id'],     // POS種別ID
                   'pos_brand_name' => $data['pos_brand_name'],  // POS種別名
               );
               array_push($posBrandList, $posBrand);
            }

            $Log->trace("END getPosBrandList");
            
            return $posBrandList;
        }

        /**
         * POSマッピング名プルダウン
         * @param    $postArray                 入力パラメータ()
         * @param    $searchArray               検索条件用パラメータ
         * @return   POSマッピング名プルダウンリスト
         */
        public function getMappingNameList( $authority = 'reference' )
        {
            global $DBA,$Log; // グローバル変数宣言
            $Log->trace("START getMappingNameList");

            $sql = ' SELECT mapping_name_id,mapping_name'
                 . '   FROM m_mapping_name '
                 . '  WHERE is_del = 0 '
                 . '  ORDER BY disp_order '  ;

            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $mappingNameList = array();

            if( $result === false )
            {
                $Log->trace("END getMappingNameList");
                return $mappingNameList;
            }

            // 最初の空行を追加
            $mappingName = array(
                   'mapping_name_id'   => "", // マッピング名称ID
                   'mapping_name' => "----",  // マッピング名
                );
            array_push($mappingNameList, $mappingName);

            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
               $mappingName = array(
                   'mapping_name_id'  => $data['mapping_name_id'],     // マッピング名称ID
                   'mapping_name' => $data['mapping_name'],            // マッピング名
               );
               array_push($mappingNameList, $mappingName);
            }

            $Log->trace("END getMappingNameList");
            
            return $mappingNameList;
        }

        /**
         * 取得タイププルダウン
         * @param    $postArray                 入力パラメータ()
         * @param    $searchArray               検索条件用パラメータ
         * @return   取得タイププルダウンリスト
         */
        public function getTypeList( $authority = 'reference' )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getTypeList");

            $logicList = array();

            // 最初の空行を追加
            $logic = array(
                   'logic_type'      => "",
                   'logic_name' => "----",
                );
            array_push($logicList, $logic);

            $logic = array(
                   'logic_type'      => "0",
                   'logic_name' => "取得",
               );

            array_push($logicList, $logic);

            $logic = array(
                   'logic_type'      => "1",
                   'logic_name' => "計算式",
               );

            array_push($logicList, $logic);

            $Log->trace("END getTypeList");
            
            return $logicList;
        }

        /**
         * POSキーファイル名プルダウン
         * @param    $postArray                 入力パラメータ()
         * @param    $searchArray               検索条件用パラメータ
         * @return   POSキーファイル名プルダウンリスト
         */
        public function getPosKeyFileList( $authority = 'reference' )
        {
            global $DBA,$Log; // グローバル変数宣言
            $Log->trace("START getPosKeyFileList");

            $sql = ' SELECT pos_key_file_id,pos_key_file_name'
                 . '   FROM m_pos_key_file '
                 . '  WHERE is_del = 0 '
                 . '  ORDER BY disp_order '  ;

            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $posKeyFileList = array();

            if( $result === false )
            {
                $Log->trace("END getPosKeyFileList");
                return $posKeyFileList;
            }

            // 最初の空行を追加
            $posKeyFile = array(
                   'pos_key_file_id'   => "",      // POSキーファイルID
                   'pos_key_file_name' => "----",  // POSキーファイル名
                );
            array_push($posKeyFileList, $posKeyFile);

            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
               $posKeyFile = array(
                   'pos_key_file_id'  => $data['pos_key_file_id'],     // POSキーファイルID
                   'pos_key_file_name' => $data['pos_key_file_name'],  // POSキーファイル名
               );
               array_push($posKeyFileList, $posKeyFile);
            }

            $Log->trace("END getPosKeyFileList");
            
            return $posKeyFileList;
        }

        /**
         * 端数タイププルダウン
         * @param    $postArray                 入力パラメータ()
         * @param    $searchArray               検索条件用パラメータ
         * @return   端数タイププルダウンリスト
         */
        public function getRoundTypeList( $authority = 'reference' )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getRoundTypeList");

            $roundTypeList = array();

            // 最初の空行を追加
            $roundType = array(
                   'round_type'      => "",
                   'round_type_name' => "----",
                );
            array_push($roundTypeList, $roundType);

            $roundType = array(
                   'round_type'      => "0",
                   'round_type_name' => "切捨て",
               );

            array_push($roundTypeList, $roundType);

            $roundType = array(
                   'round_type'      => "1",
                   'round_type_name' => "切上げ",
               );

            array_push($roundTypeList, $roundType);

            $roundType = array(
                   'round_type'      => "2",
                   'round_type_name' => "四捨五入",
               );

            array_push($roundTypeList, $roundType);

            $Log->trace("END getRoundTypeList");
            
            return $roundTypeList;
        }

    }
?>
