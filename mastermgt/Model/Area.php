<?php
    /**
     * @file      エリアマスタ
     * @author    millionet oota
     * @date      2016/07/26
     * @version   1.00
     * @note      エリアマスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    
    /**
     * エリアマスタクラス
     * @note   エリアマスタテーブルの管理を行う。
     */
    class Area extends BaseModel
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
         * エリアマスタ一覧画面一覧表
         * @param    $postArray    入力パラメータ
         * @param    $effFlag      状態フラグ
         * @param    $statusFlag   在籍状況フラグ
         * @return   成功時：$userDataList 失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            // 一覧検索用のSQL文と検索条件が入った配列の生成
            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $areaDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $areaDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $areaDataList, $data);
            }

            $Log->trace("END getListData");

            // 一覧表を返す
            return $areaDataList;
        }

        /**
         * エリアマスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   エリアマスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $searchArray = array();
            
            $sql  = " SELECT * FROM";
            $sql .= " (SELECT  ARRAY_TO_STRING(ARRAY(SELECT organization_name FROM v_organization WHERE eff_code = '適用中' and area_cd IN(m.area_cd)), ',') as shop_name";
            $sql .= "        ,m.*";
            $sql .= "   FROM m_area m";
            $sql .= " ) as a";
            $sql .= "  WHERE area_id is not null";
            
            // 検索条件追加
            // エリア名:エリア名カナ
            if( !empty( $postArray['area_name'] ) )
            {
                $sql .= ' AND area_nm LIKE :area_name ';
                $areaName = "%" . $postArray['area_name'] . "%";
                $searchArray = array_merge($searchArray, array(':area_name' => $areaName,));
                
                $sql .= ' AND area_kn LIKE :area_name ';
                $areaName = "%" . $postArray['area_name'] . "%";
                $searchArray = array_merge($searchArray, array(':area_name' => $areaName,));

            }
            
            // 店舗名
            if( !empty( $postArray['shop_name'] ) )
            {
                $sql .= ' AND shop_name LIKE :shop_name ';
                $areaName = "%" . $postArray['shop_name'] . "%";
                $searchArray = array_merge($searchArray, array(':shop_name' => $areaName,));
            }
            
            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");

            return $sql;
        }

        /**
         * エリアマスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   エリアマスタ条件追加SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sqlSort = ' ORDER BY area_cd';

            // ソート条件作成
            $sortSqlList = array(
                                3    => ' ORDER BY disp_order, registration_time',
                                4    => ' ORDER BY disp_order DESC, registration_time DESC',
                                5    => ' ORDER BY group_name, registration_time',
                                6    => ' ORDER BY group_name DESC, registration_time DESC',
                                5    => ' ORDER BY menber_name, registration_time',
                                6    => ' ORDER BY menber_name DESC, registration_time DESC',
                                7    => ' ORDER BY registration_time, disp_order',
                                8    => ' ORDER BY registration_time DESC, disp_order DESC',
            );

            // ソート条件
            if( array_key_exists( $sortNo, $sortSqlList ) )
            {
                $sqlSort = $sortSqlList[$sortNo];
            }

            $Log->trace("END creatSortSQL");

            return $sqlSort;
        }
        
        /**
         * エリアマスタ修正画面登録データ検索
         * @param    $notice_contact_id
         * @return   SQLの実行結果
         */
        public function getGroupData( $group_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserData");

            $sql = ' SELECT * FROM m_group where group_id = :group_id';
            
            $searchArray = array( ':group_id' => $group_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $groupDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getUserData");
                return $groupDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $groupDataList = $data;
            }

            $Log->trace("END getUserData");

            return $groupDataList;
        }
        
        /**
         * エリアマスタ新規データ登録
         * @param    $postArray(エリアマスタテーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function addNewData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            // 一覧表を格納する空の配列宣言
            $groupDataList = array();

            $parameters = array(
                ':group_name'                   => $postArray['group_name'],
                ':menber_id'                    => $postArray['menber_id'],
                ':menber_name'                  => $postArray['menber_name'],
                ':disp_order'                   => $postArray['disp_order'],
                ':registration_user_id'         => $postArray['registration_user_id'],
                ':registration_organization'    => $postArray['registration_organization'],
                ':update_user_id'               => $postArray['update_user_id'],
                ':update_organization'          => $postArray['update_organization'],
            );

            $sql = ' INSERT INTO m_group( '
                 . '                    group_name'
                 . '                  , menber_id'
                 . '                  , menber_name'
                 . '                  , disp_order'
                 . '                  , registration_time'
                 . '                  , registration_user_id'
                 . '                  , registration_organization'
                 . '                  , update_time'
                 . '                  , update_user_id'
                 . '                  , update_organization'
                 . '                  ) VALUES ('
                 . '                    :group_name'
                 . '                  , :menber_id'
                 . '                  , :menber_name'
                 . '                  , :disp_order'
                 . '                  , current_timestamp'
                 . '                  , :registration_user_id'
                 . '                  , :registration_organization'
                 . '                  , current_timestamp'
                 . '                  , :update_user_id'
                 . '                  , :update_organization'
                 . ')';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3080");
                $errMsg = "エリアマスタの登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            $Log->trace("END addNewData");

            return "MSG_BASE_0000";
        }

        /**
         * エリアマスタデータを更新
         * @param    $postArray(エリアマスタテーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function updateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $parameters = array(
                ':group_id'               => $postArray['group_id'],
                ':group_name'             => $postArray['group_name'],
                ':menber_id'              => $postArray['menber_id'],
                ':menber_name'            => $postArray['menber_name'],
                ':disp_order'             => $postArray['disp_order'],
                ':update_user_id'         => $postArray['update_user_id'],
                ':update_organization'    => $postArray['update_organization'],
            );

            // エリアマスタテーブル更新
            $sql = 'UPDATE m_group SET'
                . '   group_name = :group_name'
                . ' , menber_id = :menber_id'
                . ' , menber_name = :menber_name'
                . ' , disp_order = :disp_order'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE group_id = :group_id';
//                . ' WHERE notice_contact_id = :notice_contact_id AND update_user_id = :update_user_id AND update_time = :update_time '; 排他制御用

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // エリアマスタテーブルの登録結果がfalseの場合、エラーメッセージを出力
                $Log->warn("MSG_ERR_3085");
                $errMsg = "エリアマスタの登録処理にエラーが生じました。";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END addNewData");
            return "MSG_BASE_0000";
        }

        /**
         * エリアマスタのデータ削除
         * @param    $postArray(エリアマスタID)
         * @return   SQLの実行結果
         */
        public function delData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delData");

            $sql = ' DELETE FROM m_group WHERE group_id = :group_id  ';
            $parameters = array(
                ':group_id' => $postArray['group_id'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();
                // SQL実行エラー
                $Log->warn("エリアマスタの削除に失敗しました。");
                $errMsg = "エリアマスタの削除に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END delData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END delData");

            return "MSG_BASE_0000";
        }

        /**
         * 対象役職のスタッフを取得する
         * @param    $position_id
         * @return   SQLの実行結果
         */
        public function getPositionUser( $position_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserData");

            $sql = ' SELECT * FROM v_user where position_id = :position_id';
            
            $searchArray = array( ':position_id' => $position_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $groupDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getUserData");
                return $groupDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $groupDataList = $data;
            }

            $Log->trace("END getUserData");

            return $groupDataList;
        }

        /**
         * 全てのスタッフを取得する
         * @param    $position_id
         * @return   SQLの実行結果
         */
        public function getAllUser()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAllUser");

            $sql = ' SELECT user_id, user_name FROM v_user '
                 . "  WHERE eff_code = '適用中'"
                 . '  ORDER BY  e_disp_order, p_disp_order, employees_no ';

            $searchArray = array();
            
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $groupDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getUserData");
                return $groupDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $groupDataList, $data);
            }
            
            $Log->trace("END getUserData");

            return $groupDataList;
        }
    }
?>