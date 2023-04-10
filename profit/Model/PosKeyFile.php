<?php
    /**
     * @file      POSキーファイルマスタ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      POSキーファイルマスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * POSキーファイルクラス
     * @note   POSキーファイルマスタテーブルの管理を行う
     */
    class PosKeyFile extends BaseModel
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
         * POSキーファイル一覧画面一覧表
         * @param    $postArray   入力パラメータ(is_del/pos_brand_id/sort)
         * @return   成功時：$posBrandList(pos_key_file_id/pos_brand_id/pos_key_file_name/comment/pos_key_type/cooperation_code/disp_order/is_del)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $posKeyFileDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $posKeyFileDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $posKeyFileDataList, $data);
            }
            
            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $posKeyFileList = $posKeyFileDataList;
            }
            else
            {
               // $posBrandList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $posBrandDataList);
                $posKeyFileList = $posKeyFileDataList;
                if( $postArray['sort'] == 1 )
                {
                    $posKeyFileList = array_reverse($posKeyFileList);
                }
            }

            $Log->trace("END getListData");

            return $posKeyFileList;
        }

        /**
         * POSキーファイルマスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/POSキーファイル名/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO m_pos_key_file( pos_brand_id'
                . '                      , pos_key_file_name'
                . '                      , comment'
                . '                      , pos_key_type'
                . '                      , cooperation_code'
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
                . '                      , :pos_key_file_name'
                . '                      , :comment'
                . '                      , :pos_key_type'
                . '                      , :cooperation_code'
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
                ':pos_key_file_name'         => $postArray['posKeyFileName'],
                ':comment'                   => $postArray['comment'],
                ':pos_key_type'              => $postArray['posKeyType'],
                ':cooperation_code'          => $postArray['cooperationCode'],
                ':disp_order'                => $postArray['dispOrder'],
                ':is_del'                    => $postArray['is_del'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "m_pos_key_file" );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * POSキーファイルマスタ登録データ修正
         * @param    $postArray   入力パラメータ(POSキーファイルID/コード/組織ID/POSキーファイル名/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $sql = 'UPDATE m_pos_key_file SET'
                . '   pos_brand_id = :pos_brand_id'
                . ' , pos_key_file_name = :pos_key_file_name'
                . ' , comment = :comment'
                . ' , pos_key_type = :pos_key_type'
                . ' , cooperation_code = :cooperation_code'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE pos_key_file_id = :pos_key_file_id AND update_time = :update_time ';

            $parameters = array(
                ':pos_key_file_id'         => $postArray['posKeyFileId'],
                ':pos_brand_id'            => $postArray['posBrandId'],
                ':pos_key_file_name'       => $postArray['posKeyFileName'],
                ':comment'                 => $postArray['comment'],
                ':pos_key_type'            => $postArray['posKeyType'],
                ':cooperation_code'        => $postArray['cooperationCode'],
                ':disp_order'              => $postArray['dispOrder'],
                ':update_user_id'          => $postArray['user_id'],
                ':update_organization'     => $postArray['organization'],
                ':pos_brand_id'            => $postArray['posBrandId'],
                ':update_time'             => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * POSキーファイルマスタ登録データ削除
         * @param    $postArray   入力パラメータ(POSキーファイルID/削除フラグ1/ユーザID/更新組織)
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
            $sql = 'UPDATE m_pos_key_file SET'
                . '   is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE pos_key_file_id = :pos_key_file_id AND update_time = :update_time ';

            $parameters = array(
                ':pos_key_file_id'           => $postArray['posKeyFileId'],
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
         * POSキーファイル一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/sectionName/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   POSキーファイルマスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT f.*,b.pos_brand_name '
                 . '   FROM m_pos_key_file f left join m_pos_brand b on (f.pos_brand_id = b.pos_brand_id)'
                 . '  WHERE true'  ;
            
            if( !empty( $postArray['posBrandId'] ) )
            {
                $sql .= ' AND f.pos_brand_id = :posBrandId ';
                $posBrandNameArray = array(':posBrandId' => $postArray['posBrandId'],);
                $searchArray = array_merge($searchArray, $posBrandNameArray);
            }
            
            if( $postArray['is_del'] == 0 )
            {
                $sql .= ' AND f.is_del = :is_del ';
                $isDelArray = array(':is_del' => $postArray['is_del'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");
            
            return $sql;
        }

        /**
         * POSキーファイルマスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   POSキーファイルマスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = ' ORDER BY disp_order';

            // ソート条件作成
            $sortSqlList = array(
                                3       =>  ' ORDER BY f.disp_order DESC',                           // 表示順の降順
                                4       =>  ' ORDER BY f.disp_order',                                // 表示順の昇順
                                5       =>  ' ORDER BY b.pos_brand_name DESC',                       // POS種別名の降順
                                6       =>  ' ORDER BY b.pos_brand_name',                            // POS種別名の昇順
                                7       =>  ' ORDER BY f.pos_key_file_name DESC, pos_brand_code',    // POSキーファイル名の降順
                                8       =>  ' ORDER BY f.pos_key_file_name, pos_brand_code',         // POSキーファイル名の昇順
                                9       =>  ' ORDER BY f.pos_key_type DESC, pos_brand_code',         // POSキーファイルキー種別の降順
                               10       =>  ' ORDER BY f.pos_key_type, pos_brand_code',              // POSキーファイルキー種別の昇順
                               11       =>  ' ORDER BY f.cooperation_code DESC, pos_brand_code',     // 連携コードの降順
                               12       =>  ' ORDER BY f.cooperation_code, pos_brand_code',          // 連携コードの昇順
                               13       =>  ' ORDER BY f.comment DESC, pos_brand_code',              // コメントの降順
                               14       =>  ' ORDER BY f.comment, pos_brand_code',                   // コメントの昇順
                               15       =>  ' ORDER BY f.registration_time DESC, pos_brand_code',    // 初回登録日の降順
                               16       =>  ' ORDER BY f.registration_time, pos_brand_code',         // 初回登録日の昇順
                               17       =>  ' ORDER BY f.update_time DESC, pos_brand_code',          // 更新日の降順
                               18       =>  ' ORDER BY f.update_time, pos_brand_code',               // 更新日の昇順
                               19       =>  ' ORDER BY f.is_del DESC, pos_brand_code',               // 状態の降順
                               20       =>  ' ORDER BY f.is_del, pos_brand_code',                    // 状態の昇順
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
         * @return   POS種別名マスタ一覧取得SQL文
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

    }
?>
