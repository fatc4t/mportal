<?php
    /**
     * @file      通達連絡マスタ
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      通達連絡マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    
    /**
     * 通達連絡クラス
     * @note   通達連絡テーブルの管理を行う。
     */
    class NoticeContact extends BaseModel
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
         * 通達連絡一覧画面一覧表
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
            $noticeContactDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $noticeContactDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $noticeContactDataList, $data);
            }

            $Log->trace("END getListData");

            // 一覧表を返す
            return $noticeContactDataList;
        }

        /**
         * 通達連絡一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   通達連絡一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $searchArray = array();

            $join = "";

            // セキュリティで判定
            if( !empty( $postArray['securityLebel'] ) && $postArray['securityLebel'] > 1 )
            {
                // 管理者以外の場合
                $join = "   JOIN";
            }else{
                // 管理者の場合
                $join = "   LEFT JOIN";
            }

            $sql = " SELECT  nc.notice_contact_id"
                 . "        ,nc.title"
                 . "        ,CASE WHEN LENGTH(nc.contents) > 22 THEN SUBSTR(nc.contents, 0,  22) || ' ...' "
                 . "              ELSE nc.contents "
                 . "         END as contents"
                 . "        ,CASE WHEN ncd.state is null THEN 1"
                 . "              WHEN cast(nc.registration_user_id as integer) = :user_id THEN 1"
                 . "              ELSE ncd.state"
                 . "         END as state"
                 . "        ,CASE WHEN nc.registration_time > current_timestamp + '-3 days' THEN 1"
                 . "              ELSE 0"
                 . "         END as is_new"
                 . "        ,nc.thumbnail"
                 . "        ,nc.file"
                 . "        ,v.organization_name"
                 . "        ,v.user_name"
                 . "        ,to_char(nc.registration_time,'YYYY/MM/DD HH24時MI分SS秒') as registration_time"
                 . "        ,(SELECT count('x') FROM t_notice_contact_details WHERE notice_contact_id = nc.notice_contact_id AND state = 0) as unread_cnt"
                 . "        ,(SELECT count('x') FROM t_notice_contact_details WHERE notice_contact_id = nc.notice_contact_id AND state = 1) as already_read_cnt"
                 . "   FROM t_notice_contact as nc"
                 . "   ".$join." (SELECT *"
                 . "                FROM t_notice_contact_details"
                 . "               WHERE user_id = :user_id"
                 . "             ) as ncd on (nc.notice_contact_id =  ncd.notice_contact_id)"
                 . "   LEFT JOIN (SELECT * FROM v_user WHERE eff_code = '適用中') v on (cast(nc.registration_user_id as integer) = v.user_id)"
                 . "  WHERE nc.notice_contact_id is not null";
            
            // 検索条件追加
            $searchArray = array_merge($searchArray, array(':user_id' => $postArray['user_id'],));

            // 対象者
            if( !empty( $postArray['securityLebel'] ) && $postArray['securityLebel'] > 1 )
            {
                $sql .= " AND ncd.user_id = :user_id ";
            }
            
            // 発信者
            if( !empty( $postArray['restriction_user_id'] ) )
            {
                $sql .= " AND nc.registration_user_id = :restriction_user_id ";
                $searchArray = array_merge($searchArray, array(':restriction_user_id' => $postArray['restriction_user_id'],));
            }
            
            // 発信組織
            if( !empty( $postArray['registration_organization'] ) )
            {
                $sql .= " AND nc.registration_organization = :registration_organization ";
                $searchArray = array_merge($searchArray, array(':registration_organization' => $postArray['registration_organization'],));
            }
            
            // タイトル
            if( !empty( $postArray['title'] ) )
            {
                $sql .= " AND sf_translate_case(title) LIKE '%' || sf_translate_case('" . $postArray['title'] . "') || '%' ";
            }
            
            // 内容
            if( !empty( $postArray['contents'] ) )
            {
                $sql .= " AND sf_translate_case(contents) LIKE '%' || sf_translate_case('" . $postArray['contents'] . "') || '%' ";
            }
            
            // 作成日
            if( !empty( $postArray['sAppStartDate'] ) )
            {
                $sql .= " AND nc.registration_time >= :registration_stime ";
                $searchArray = array_merge($searchArray, array(':registration_stime' => $postArray['sAppStartDate'],));
            }
            if( !empty( $postArray['eAppStartDate'] ) )
            {
                $sql .= " AND nc.registration_time <= :registration_etime ";
                $searchArray = array_merge($searchArray, array(':registration_etime' => $postArray['eAppStartDate'],));
            }
            
            // 既読・未読 両方チェックがはずれている場合はチェックしているのと同じ
            if( $postArray['read'] == 'false' && $postArray['unread'] == 'true')
            {
                // 未読のみチェックされている場合
                $sql .= " AND ncd.state = 0 ";
            }
            
            if( $postArray['read'] == 'true' && $postArray['unread'] == 'false' )
            {
                // 既読のみチェックされている場合
                $sql .= " AND (ncd.state = 1 or ncd.state is null)";
            }
            
            // 自分が作成したものをUNION 管理者の場合は必要なし
            if( !empty( $postArray['securityLebel'] ) && $postArray['securityLebel'] > 1 && empty( $postArray['registration_organization'] ) && empty( $postArray['restriction_user_id'] )
                && !( $postArray['read'] == 'false' && $postArray['unread'] == 'true')    )
            {
                $sql .= " UNION"
                     . " SELECT  nc.notice_contact_id"
                     . "        ,nc.title"
                     . "        ,CASE WHEN LENGTH(nc.contents) > 22 THEN SUBSTR(nc.contents, 0,  22) || ' ...' "
                     . "              ELSE nc.contents "
                     . "         END as contents"
                     . "        ,1 as state"
                     . "        ,CASE WHEN nc.registration_time > current_timestamp + '-3 days' THEN 1"
                     . "              ELSE 0"
                     . "         END as is_new"
                     . "        ,nc.thumbnail"
                     . "        ,nc.file"
                     . "        ,v.organization_name"
                     . "        ,v.user_name"
                     . "        ,to_char(nc.registration_time,'YYYY/MM/DD HH24時MI分SS秒') as registration_time"
                     . "        ,(SELECT count('x') FROM t_notice_contact_details WHERE notice_contact_id = nc.notice_contact_id AND state = 0) as unread_cnt"
                     . "        ,(SELECT count('x') FROM t_notice_contact_details WHERE notice_contact_id = nc.notice_contact_id AND state = 1) as already_read_cnt"
                     . "   FROM t_notice_contact as nc"
                     . "   LEFT JOIN (SELECT * FROM v_user WHERE eff_code = '適用中') v on (cast(nc.registration_user_id as integer) = v.user_id)";

                    // 発信組織・発信者
                    if( empty( $postArray['registration_organization'] ) && empty( $postArray['restriction_user_id'] ) )
                    {
                        // スタッフID
                        $sql .= "  WHERE cast(registration_user_id as integer) = :user_id";
                        
                    }else if( !empty( $postArray['registration_organization'] ) && !empty( $postArray['restriction_user_id'] ) ){
                        
                        // 指定した検索発信組織・発信者
                        $sql .= "  WHERE cast(registration_user_id as integer) = :restriction_user_id"
                             .  "    AND cast(registration_organization as integer) = :registration_organization";
                        
                    }else{
                        // 指定した検索組織
                        $sql .= "  WHERE cast(registration_organization as integer) = :registration_organization";
                        
                    }
                    
                    // タイトル
                    if( !empty( $postArray['title'] ) )
                    {
                        $sql .= " AND sf_translate_case(title) LIKE '%' || sf_translate_case('" . $postArray['title'] . "') || '%' ";
                    }

                    // 内容
                    if( !empty( $postArray['contents'] ) )
                    {
                        $sql .= " AND sf_translate_case(contents) LIKE '%' || sf_translate_case('" . $postArray['contents'] . "') || '%' ";
                    }

                    // 作成日
                    if( !empty( $postArray['sAppStartDate'] ) )
                    {
                        $sql .= " AND registration_time >= :registration_time ";
                    }
                    if( !empty( $postArray['eAppStartDate'] ) )
                    {
                        $sql .= " AND registration_time <= :registration_time ";
                    }
            }
            
            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");

            return $sql;
        }

        /**
         * 通達連絡一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   通達連絡条件追加SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sqlSort = ' ORDER BY registration_time DESC';

            // ソート条件作成
            $sortSqlList = array(
                                3     => ' ORDER BY state DESC, registration_time DESC',
                                4     => ' ORDER BY state, registration_time DESC',
                                5     => ' ORDER BY title DESC, registration_time DESC',
                                6     => ' ORDER BY title, registration_time DESC',
                                7     => ' ORDER BY contents DESC, registration_time DESC',
                                8     => ' ORDER BY contents, registration_time DESC',
                                9     => ' ORDER BY v.organization_name DESC, registration_time DESC',
                                10    => ' ORDER BY v.organization_name, registration_time DESC',
                                11    => ' ORDER BY v.user_name DESC, registration_time DESC',
                                12    => ' ORDER BY v.user_name, registration_time DESC',
                                13    => ' ORDER BY registration_time DESC',
                                14    => ' ORDER BY registration_time',
                                15    => ' ORDER BY unread_cnt DESC, registration_time DESC',
                                16    => ' ORDER BY unread_cnt, registration_time DESC',
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
         * 通達連絡修正画面登録データ検索
         * @param    $notice_contact_id
         * @return   SQLの実行結果
         */
        public function getNoticeContactData( $notice_contact_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserData");

            $sql = ' SELECT * FROM t_notice_contact where notice_contact_id = :notice_contact_id';
            
            $searchArray = array( ':notice_contact_id' => $notice_contact_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $noticeContactDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getUserData");
                return $noticeContactDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $noticeContactDataList = $data;
            }

            $Log->trace("END getUserData");

            return $noticeContactDataList;
        }
        
        /**
         * 通達連絡修正画面詳細登録データ検索
         * @param    $notice_contact_id
         * @return   SQLの実行結果
         */
        public function getNoticeContactDetailesData( $notice_contact_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserData");

            $sql = " SELECT  '3-' || nc.user_id as id"
                 . '         ,un.user_name as name'
                 . '   FROM  t_notice_contact_details as nc'
                 . "         join (SELECT user_id, user_name FROM v_user WHERE eff_code = '適用中') as un on (nc.user_id =  un.user_id)"
                 . '  WHERE notice_contact_id = :notice_contact_id';
            
            $searchArray = array( ':notice_contact_id' => $notice_contact_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $noticeContactDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getUserData");
                return $noticeContactDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $noticeContactDataList, $data);
            }

            $Log->trace("END getUserData");

            return $noticeContactDataList;
        }
        
        /**
         * 通達連絡新規データ登録
         * @param    $postArray(通達連絡テーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function addNewData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            // 一覧表を格納する空の配列宣言
            $noticeContactDataList = array();

            $parameters = array(
                ':title'                        => $postArray['title'],
                ':contents'                     => $postArray['contents'],
                ':thumbnail'                    => $postArray['thumbnail'],
                ':file'                         => $postArray['file'],
                ':registration_user_id'         => $postArray['registration_user_id'],
                ':registration_organization'    => $postArray['registration_organization'],
                ':update_user_id'               => $postArray['update_user_id'],
                ':update_organization'          => $postArray['update_organization'],
            );

            $sql = ' INSERT INTO t_notice_contact( '
                 . '                    title'
                 . '                  , contents'
                 . '                  , thumbnail'
                 . '                  , file'
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
                $errMsg = "通達連絡の登録失敗";
                $Log->warnDetail($errMsg);
                return $noticeContactDataList;
            }

            // 登録した通達連絡IDを取得
            $sql = ' SELECT notice_contact_id FROM t_notice_contact'
                 . '  WHERE title = :title'
                 . '    AND contents = :contents'
                 . '    AND thumbnail = :thumbnail'
                 . '    AND file = :file'
                 . '    AND registration_user_id = :registration_user_id'
                 . '    AND registration_organization = :registration_organization'
                 . '    AND update_user_id = :update_user_id'
                 . '    AND update_organization = :update_organization';
            
            // SQLの実行
            $result = $DBA->executeSQL($sql, $parameters);

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3080");
                $errMsg = "通達連絡の取得失敗";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewData");
                return $noticeContactDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $noticeContactDataList = $data;
            }

            $Log->trace("END addNewData");

            return $noticeContactDataList;
        }

        /**
         * 通達連絡詳細の新規データ登録
         * @param    $postArray(通達連絡詳細テーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function addNewDetailsData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewDetailsData");

            $parameters = array(
                ':notice_contact_id'            => $postArray['notice_contact_id'],
                ':user_id'                      => $postArray['user_id'],
                ':state'                        => $postArray['state'],
                ':reply'                        => $postArray['reply'],
                ':thumbnail'                    => $postArray['thumbnail'],
                ':file'                         => $postArray['file'],
                ':registration_user_id'         => $postArray['registration_user_id'],
                ':registration_organization'    => $postArray['registration_organization'],
                ':update_user_id'               => $postArray['update_user_id'],
                ':update_organization'          => $postArray['update_organization'],
            );

            $sql = ' INSERT INTO t_notice_contact_details( '
                 . '                    notice_contact_id'
                 . '                  , user_id'
                 . '                  , state'
                 . '                  , reply'
                 . '                  , thumbnail'
                 . '                  , file'
                 . '                  , registration_time'
                 . '                  , registration_user_id'
                 . '                  , registration_organization'
                 . '                  , update_time'
                 . '                  , update_user_id'
                 . '                  , update_organization'
                 . '                  ) VALUES ('
                 . '                    :notice_contact_id'
                 . '                  , :user_id'
                 . '                  , :state'
                 . '                  , :reply'
                 . '                  , :thumbnail'
                 . '                  , :file'
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
                $errMsg = "通達連絡詳細の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3080";
            }

            $Log->trace("END addNewData");
            return "MSG_BASE_0000";
            
        }

        
        /**
         * 通達連絡データを更新
         * @param    $postArray(通達連絡テーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function updateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $parameters = array(
                ':notice_contact_id'            => $postArray['notice_contact_id'],
                ':title'                        => $postArray['title'],
                ':contents'                     => $postArray['contents'],
                ':thumbnail'                    => $postArray['thumbnail'],
                ':update_user_id'               => $postArray['update_user_id'],
                ':update_organization'          => $postArray['update_organization'],
            );

            // 通達連絡テーブル更新
            $sql = 'UPDATE t_notice_contact SET'
                . '   title = :title'
                . ' , contents = :contents'
                . ' , thumbnail = :thumbnail'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE notice_contact_id = :notice_contact_id';
//                . ' WHERE notice_contact_id = :notice_contact_id AND update_user_id = :update_user_id AND update_time = :update_time '; 排他制御用

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // 通達連絡テーブルの登録結果がfalseの場合、エラーメッセージを出力
                $Log->warn("MSG_ERR_3085");
                $errMsg = "通達連絡の登録処理にエラーが生じました。";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END addNewData");
            return "MSG_BASE_0000";
        }

        /**
         * 通達連絡のデータ削除
         * @param    $postArray(通達連絡ID)
         * @return   SQLの実行結果
         */
        public function delData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delData");

            $sql = ' DELETE FROM t_notice_contact WHERE notice_contact_id = :notice_contact_id  ';
            $parameters = array(
                ':notice_contact_id' => $postArray['notice_contact_id'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();
                // SQL実行エラー
                $Log->warn("通達連絡の削除に失敗しました。");
                $errMsg = "通達連絡の削除に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END delData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END delData");

            return "MSG_BASE_0000";
        }

        /**
         * 通達連絡詳細のデータ削除
         * @param    $postArray(通達連絡ID)
         * @return   SQLの実行結果
         */
        public function delDataDetails($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delData");

            // 対象者リストを削除する
            $sql = ' DELETE FROM t_notice_contact_details WHERE notice_contact_id = :notice_contact_id  ';
            $parameters = array(
                ':notice_contact_id' => $postArray['notice_contact_id'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();
                // SQL実行エラー
                $Log->warn("通達連絡の詳細削除に失敗しました。");
                $errMsg = "通達連絡の詳細削除に失敗しました。";
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
            $Log->trace("START getPositionUser");

            $sql = ' SELECT * FROM v_user where position_id = :position_id';
            
            $searchArray = array( ':position_id' => $position_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $noticeContactDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result == false )
            {
                $Log->trace("END getPositionUser");
                return $noticeContactDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $noticeContactDataList = $data;
            }

            $Log->trace("END getPositionUser");

            return $noticeContactDataList;
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
            $noticeContactDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getAllUser");
                return $noticeContactDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $noticeContactDataList, $data);
            }
            
            $Log->trace("END getAllUser");

            return $noticeContactDataList;
        }
        
        /**
         * グループのリストを取得する
         * @param    $position_id
         * @return   SQLの実行結果
         */
        public function getGroupUser( $group_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getGroupUser");

            $sql = ' SELECT * FROM m_group where group_id = :group_id';
            
            $searchArray = array( ':group_id' => $group_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $noticeContactDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result == false )
            {
                $Log->trace("END getGroupUser");
                return $noticeContactDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $noticeContactDataList = $data;
            }

            $Log->trace("END getGroupUser");

            return $noticeContactDataList;
        }

    }
?>