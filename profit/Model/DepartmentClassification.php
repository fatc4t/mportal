<?php
    /**
     * @file      POS種別マスタ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      POS種別マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * POS種別クラス
     * @note   POS種別マスタテーブルの管理を行う
     */
    class DepartmentClassification extends BaseModel
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
         * POS種別一覧画面一覧表
         * @param    $postArray   入力パラメータ(is_del/pos_brand_code/pos_brand_name/sort)
         * @return   成功時：$posBrandList(pos_brand_id/pos_brand_code/pos_brand_name/pos_brand_name_kana/disp_order/is_del)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $posBrandDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $posBrandDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $posBrandDataList, $data);
            }
            
            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $posBrandList = $posBrandDataList;
            }
            else
            {
               // $posBrandList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $posBrandDataList);
                $posBrandList = $posBrandDataList;
                if( $postArray['sort'] == 1 )
                {
                    $posBrandList = array_reverse($posBrandList);
                }
            }

            $Log->trace("END getListData");

            return $posBrandList;
        }

        /**
         * POS種別マスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/POS種別名/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO m_pos_brand( pos_brand_code'
                . '                      , pos_brand_name'
                . '                      , pos_brand_name_kana'
                . '                      , disp_order'
                . '                      , is_del'
                . '                      , registration_time'
                . '                      , registration_user_id'
                . '                      , registration_organization'
                . '                      , update_time'
                . '                      , update_user_id'
                . '                      , update_organization'
                . '                      ) VALUES ('
                . '                        :pos_brand_code'
                . '                      , :pos_brand_name'
                . '                      , :pos_brand_name_kana'
                . '                      , :disp_order'
                . '                      , :is_del'
                . '                      , current_timestamp'
                . '                      , :registration_user_id'
                . '                      , :registration_organization'
                . '                      , current_timestamp'
                . '                      , :update_user_id'
                . '                      , :update_organization)';

            $parameters = array(
                ':pos_brand_code'            => $postArray['posBrandCode'],
                ':pos_brand_name'            => $postArray['posBrandName'],
                ':pos_brand_name_kana'       => $postArray['posBrandNameKana'],
                ':disp_order'                => $postArray['dispOrder'],
                ':is_del'                    => $postArray['is_del'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "m_pos_brand" );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * POS種別マスタ登録データ修正
         * @param    $postArray   入力パラメータ(POS種別ID/コード/組織ID/POS種別名/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $sql = 'UPDATE m_pos_brand SET'
                . '   pos_brand_code = :pos_brand_code'
                . ' , pos_brand_name = :pos_brand_name'
                . ' , pos_brand_name_kana = :pos_brand_name_kana'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE pos_brand_id = :pos_brand_id AND update_time = :update_time ';

            $parameters = array(
                ':pos_brand_code'            => $postArray['posBrandCode'],
                ':pos_brand_name'            => $postArray['posBrandName'],
                ':pos_brand_name_kana'       => $postArray['posBrandNameKana'],
                ':disp_order'                => $postArray['dispOrder'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':pos_brand_id'              => $postArray['posBrandId'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * POS種別マスタ登録データ削除
         * @param    $postArray   入力パラメータ(POS種別ID/削除フラグ1/ユーザID/更新組織)
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
            $sql = 'UPDATE m_pos_brand SET'
                . '   is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE pos_brand_id = :pos_brand_id AND update_time = :update_time ';

            $parameters = array(
                ':is_del'                    => $postArray['is_del'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':pos_brand_id'              => $postArray['posBrandId'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * POS種別一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/sectionName/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   POS種別マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT pos_brand_id,pos_brand_code,pos_brand_name,pos_brand_name_kana,disp_order, '
                 . '        is_del,registration_time,registration_user_id,update_time,update_user_id '
                 . '   FROM m_pos_brand '
                 . '  WHERE true'  ;
            
            if( !empty( $postArray['posBrandName'] ) )
            {
                $sql .= ' AND pos_brand_name Like :posBrandName ';
                $posBrandNameArray = array(':posBrandName' => '%'.$postArray['posBrandName'].'%',);
                $searchArray = array_merge($searchArray, $posBrandNameArray);
            }
            if( !empty( $postArray['posBrandCode'] ) )
            {
                $sql .= ' AND pos_brand_code = :posBrandCode ';
                $posBrandCodeArray = array(':posBrandCode' => $postArray['posBrandCode'],);
                $searchArray = array_merge($searchArray, $posBrandCodeArray);
            }
            
            if( $postArray['is_del'] == 0 )
            {
                $sql .= ' AND is_del = :is_del ';
                $isDelArray = array(':is_del' => $postArray['is_del'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");
            
            return $sql;
        }

        /**
         * POS種別マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   POS種別マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = ' ORDER BY disp_order, pos_brand_code';

            // ソート条件作成
            $sortSqlList = array(
                                3       =>  ' ORDER BY disp_order DESC, pos_brand_code',           // 表示順の降順
                                4       =>  ' ORDER BY disp_order, pos_brand_code',                // 表示順の昇順
                                5       =>  ' ORDER BY pos_brand_code DESC',                       // POS種別コードの降順
                                6       =>  ' ORDER BY pos_brand_code',                            // POS種別コードの昇順
                                7       =>  ' ORDER BY pos_brand_name DESC, pos_brand_code',       // POS種別名の降順
                                8       =>  ' ORDER BY pos_brand_name, pos_brand_code',            // POS種別名の昇順
                                9       =>  ' ORDER BY pos_brand_name_kana DESC, pos_brand_code',  // よみがなの降順
                               10       =>  ' ORDER BY pos_brand_name_kana, pos_brand_code',       // よみがなの昇順
                               11       =>  ' ORDER BY registration_time DESC, pos_brand_code',    // 初回登録日の降順
                               12       =>  ' ORDER BY registration_time, pos_brand_code',         // 初回登録日の昇順
                               13       =>  ' ORDER BY update_time DESC, pos_brand_code',          // 更新日の降順
                               14       =>  ' ORDER BY update_time, pos_brand_code',               // 更新日の昇順
                               15       =>  ' ORDER BY is_del DESC, pos_brand_code',               // 状態の降順
                               16       =>  ' ORDER BY is_del, pos_brand_code',                    // 状態の昇順
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
