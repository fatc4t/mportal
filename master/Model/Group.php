<?php
    /**
     * @file      グループマスタ
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      グループマスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    
    /**
     * グループマスタクラス
     * @note   グループマスタテーブルの管理を行う。
     */
    class Group extends BaseModel
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
         * グループマスタ一覧画面一覧表
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
            $groupDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $groupDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $groupDataList, $data);
            }

            $Log->trace("END getListData");

            // 一覧表を返す
            return $groupDataList;
        }

        /**
         * グループマスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   グループマスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $searchArray = array();
            
            $sql = ' SELECT * FROM m_group WHERE group_id is not null';
            
            // 検索条件追加
            // グループ名
            if( !empty( $postArray['group_name'] ) )
            {
                $sql .= ' AND group_name LIKE :group_name ';
                $groupName = "%" . $postArray['group_name'] . "%";
                $searchArray = array_merge($searchArray, array(':group_name' => $groupName,));
            }
            
            // メンバー名
            if( !empty( $postArray['contents'] ) )
            {
                $sql .= ' AND menber_name LIKE :menber_name ';
                $menberName = "%" . $postArray['menber_name'] . "%";
                $searchArray = array_merge($searchArray, array(':menber_name' => $menberName,));
            }
            
            // 作成日
            if( !empty( $postArray['sAppStartDate'] ) )
            {
                $sql .= " AND registration_time >= :registration_time ";
                $searchArray = array_merge($searchArray, array(':registration_time' => $postArray['sAppStartDate'],));
            }
            if( !empty( $postArray['eAppStartDate'] ) )
            {
                $sql .= " AND registration_time <= :registration_time ";
                $searchArray = array_merge($searchArray, array(':registration_time' => $postArray['eAppStartDate'],));
            }
            
            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");

            return $sql;
        }

        /**
         * グループマスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   グループマスタ条件追加SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sqlSort = ' ORDER BY disp_order';

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
         * グループマスタ修正画面登録データ検索
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
         * グループマスタ新規データ登録
         * @param    $postArray(グループマスタテーブルへの登録情報)
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
                $errMsg = "グループマスタの登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            $Log->trace("END addNewData");

            return "MSG_BASE_0000";
        }

        /**
         * グループマスタデータを更新
         * @param    $postArray(グループマスタテーブルへの登録情報)
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

            // グループマスタテーブル更新
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
                // グループマスタテーブルの登録結果がfalseの場合、エラーメッセージを出力
                $Log->warn("MSG_ERR_3085");
                $errMsg = "グループマスタの登録処理にエラーが生じました。";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END addNewData");
            return "MSG_BASE_0000";
        }

        /**
         * グループマスタのデータ削除
         * @param    $postArray(グループマスタID)
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
                $Log->warn("グループマスタの削除に失敗しました。");
                $errMsg = "グループマスタの削除に失敗しました。";
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