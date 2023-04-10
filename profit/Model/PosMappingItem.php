<?php
    /**
     * @file      商品別マッピング設定マスタ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      商品別マッピング設定マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class PosMappingItem extends BaseModel
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
         * 商品別マッピング一覧画面一覧表
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
         * 商品別マッピングマスタ新規データ登録
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
         * 商品別マッピングマスタ登録データ修正
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
         * 商品別マッピングマスタ登録データ削除
         * @param    $postArray   入力パラメータ(マッピング名ID/削除フラグ1/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            $sql = 'UPDATE m_pos_mapping_item SET'
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
         * 商品別マッピング一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/sectionName/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   マッピング名マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = " SELECT  p.*"
                 . "        ,k.pos_key_file_name"
                 . "   FROM m_pos_mapping_item p"
                 . "   left join m_pos_key_file k on (p.pos_key_file_id = k.pos_key_file_id)"
                 . "  WHERE true"  ;
            
            if( $postArray['is_del'] == 0 )
            {
                $sql .= ' AND p.is_del = :is_del ';
                $isDelArray = array(':is_del' => $postArray['is_del'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            $Log->trace("END creatSQL");
            
            return $sql;
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
    }
?>
