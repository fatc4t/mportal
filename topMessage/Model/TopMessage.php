<?php
    /**
     * @file      トップメッセージマスタ
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      トップメッセージマスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    
    /**
     * トップメッセージクラス
     * @note   トップメッセージテーブルの管理を行う。
     */
    class TopMessage extends BaseModel
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
         * トップメッセージ一覧画面一覧表
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
            $topMessageDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $topMessageDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $topMessageDataList, $data);
            }

            $Log->trace("END getListData");

            // 一覧表を返す
            return $topMessageDataList;
        }

        /**
         * トップメッセージ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   トップメッセージ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $searchArray = array();
            
            $sql = ' SELECT * FROM t_top_message WHERE top_message_id is not null';
            
            // 検索条件追加
            // タイトル
            if( !empty( $postArray['title'] ) )
            {
                $sql .= ' AND title LIKE :title ';
                $title = "%" . $postArray['title'] . "%";
                $searchArray = array_merge($searchArray, array(':title' => $title,));
            }
            
            // 内容
            if( !empty( $postArray['contents'] ) )
            {
                $sql .= ' AND contents LIKE :contents ';
                $contents = "%" . $postArray['contents'] . "%";
                $searchArray = array_merge($searchArray, array(':contents' => $contents,));
            }
            
            // 添付ファイル名
            if( !empty( $postArray['file'] ) )
            {
                $sql .= ' AND file LIKE :file ';
                $file = "%" . $postArray['file'] . "%";
                $searchArray = array_merge($searchArray, array(':file' => $file,));
            }

            // 閲覧数
            if( !empty( $postArray['viewer_count'] ) )
            {
                $sql .= ' AND viewer_count >= :viewer_count ';
                $searchArray = array_merge($searchArray, array(':viewer_count' => $postArray['viewer_count'],));
            }

            // 作成日
            if( !empty( $postArray['sAppStartDate'] ) )
            {
                $sql .= " AND registration_time >= :sAppStartDate ";
                $searchArray = array_merge($searchArray, array(':sAppStartDate' => $postArray['sAppStartDate'],));
            }
            if( !empty( $postArray['eAppStartDate'] ) )
            {
                $sql .= " AND registration_time <= :eAppStartDate ";
                $searchArray = array_merge($searchArray, array(':eAppStartDate' => $postArray['eAppStartDate'],));
            }
            
            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");

            return $sql;
        }

        /**
         * トップメッセージ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   トップメッセージ条件追加SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sqlSort = ' ORDER BY registration_time DESC';

            // ソート条件作成
            $sortSqlList = array(
                                3    => ' ORDER BY title DESC, registration_time DESC',
                                4    => ' ORDER BY title, registration_time',
                                5    => ' ORDER BY contents DESC, registration_time DESC',
                                6    => ' ORDER BY contents, registration_time',
                                7    => ' ORDER BY registration_time DESC',
                                8    => ' ORDER BY registration_time',
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
         * トップメッセージ修正画面登録データ検索
         * @param    $top_message_id
         * @return   SQLの実行結果
         */
        public function getTopMessageData( $top_message_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserData");

            $sql = ' SELECT * FROM t_top_message where top_message_id = :top_message_id';
            
            $searchArray = array( ':top_message_id' => $top_message_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $topMessageDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getUserData");
                return $topMessageDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $topMessageDataList = $data;
            }

            $Log->trace("END getUserData");

            return $topMessageDataList;
        }
        
        /**
         * トップメッセージ新規データ登録
         * @param    $postArray(トップメッセージテーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function addNewData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $parameters = array(
                ':title'                        => $postArray['title'],
                ':contents'                     => $postArray['contents'],
                ':thumbnail'                    => $postArray['thumbnail'],
                ':file'                         => $postArray['file'],
                ':viewer'                       => $postArray['viewer'],
                ':viewer_count'                 => $postArray['viewer_count'],
                ':registration_user_id'         => $postArray['registration_user_id'],
                ':registration_organization'    => $postArray['registration_organization'],
                ':update_user_id'               => $postArray['update_user_id'],
                ':update_organization'          => $postArray['update_organization'],
            );

            $sql = ' INSERT INTO t_top_message( '
                 . '                    title'
                 . '                  , contents'
                 . '                  , thumbnail'
                 . '                  , file'
                 . '                  , viewer'
                 . '                  , viewer_count'
                 . '                  , registration_time'
                 . '                  , registration_user_id'
                 . '                  , registration_organization'
                 . '                  , update_time'
                 . '                  , update_user_id'
                 . '                  , update_organization'
                 . '                  ) VALUES ('
                 . '                    :title'
                 . '                  , :contents'
                 . '                  , :thumbnail'
                 . '                  , :file'
                 . '                  , :viewer'
                 . '                  , :viewer_count'
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
                $errMsg = "トップメッセージの登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3080";
            }

            $Log->trace("END addNewData");
            return "MSG_BASE_0000";
        }

        /**
         * トップメッセージ新規データ登録
         * @param    $postArray(トップメッセージテーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function updateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $parameters = array(
                ':top_message_id'               => $postArray['top_message_id'],
                ':title'                        => $postArray['title'],
                ':contents'                     => $postArray['contents'],
                ':thumbnail'                    => $postArray['thumbnail'],
                ':update_user_id'               => $postArray['update_user_id'],
                ':update_organization'          => $postArray['update_organization'],
            );

            // トップメッセージテーブル更新
            $sql = 'UPDATE t_top_message SET'
                . '   title = :title'
                . ' , contents = :contents'
                . ' , thumbnail = :thumbnail'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE top_message_id = :top_message_id';
//                . ' WHERE top_message_id = :top_message_id AND update_user_id = :update_user_id AND update_time = :update_time '; 排他制御用

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // トップメッセージテーブルの登録結果がfalseの場合、エラーメッセージを出力
                $Log->warn("MSG_ERR_3085");
                $errMsg = "トップメッセージの登録処理にエラーが生じました。";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END addNewData");
            return "MSG_BASE_0000";
        }

        /**
         * トップメッセージのデータ削除
         * @param    $postArray(トップメッセージID/サムネイルファイル名/添付ファイル名)
         * @return   SQLの実行結果
         */
        public function delData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delData");

            $sql = ' DELETE FROM t_top_message WHERE top_message_id = :top_message_id  ';
            $parameters = array(
                ':top_message_id' => $postArray['top_message_id'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();
                // SQL実行エラー
                $Log->warn("トップメッセージの削除に失敗しました。");
                $errMsg = "トップメッセージの削除に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END delData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            // サムネイルファイルの削除
            
            // 添付ファイルの削除
            
            $Log->trace("END delData");

            return "MSG_BASE_0000";
        }
        
    }

?>