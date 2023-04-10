<?php
    /**
     * @file      日報マスタ
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      日報マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    
    /**
     * 日報クラス
     * @note   日報テーブルの管理を行う。
     */
    class DailyReport extends BaseModel
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
         * 日報一覧画面一覧表
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
            $dailyReportDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $dailyReportDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $dailyReportDataList, $data);
            }

            $Log->trace("END getListData");

            // 一覧表を返す
            return $dailyReportDataList;
        }

        /**
         * 日報一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   日報一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $searchArray = array();
            
            $sql = '   SELECT  dr.daily_report_id'
                    . '       ,dr.data'
                    . "       ,to_char(dr.target_date,'YYYY/MM/DD') as target_date"
                    . '       ,dr.form_id'
                    . '       ,dr.user_id'
                    . '       ,v.user_name'
                    . '       ,v.organization_name'
                    . '  FROM t_daily_report as dr'
                    . "  LEFT JOIN (SELECT * FROM v_user WHERE eff_code = '適用中') v on (dr.user_id = v.user_id)"
                    . ' WHERE daily_report_id is not null';
            
            // 検索条件追加
            // 対象日
            if( !empty( $postArray['sAppStartDate'] ) )
            {
                $sql .= " AND dr.target_date >= :target_sdate ";
                $searchArray = array_merge($searchArray, array(':target_sdate' => $postArray['sAppStartDate'],));
            }
            if( !empty( $postArray['eAppStartDate'] ) )
            {
                $sql .= " AND dr.target_date <= :target_edate ";
                $searchArray = array_merge($searchArray, array(':target_edate' => $postArray['eAppStartDate'],));
            }

            // 組織
            if( !empty( $postArray['organization_id'] ) )
            {
                $sql .= " AND dr.organization_id = :organization_id ";
                $searchArray = array_merge($searchArray, array(':organization_id' => $postArray['organization_id'],));
            }
            
            // スタッフ
            if( !empty( $postArray['user_id'] ) )
            {
                $sql .= " AND dr.user_id = :user_id ";
                $searchArray = array_merge($searchArray, array(':user_id' => $postArray['user_id'],));
            }

            // キーワード
            if( !empty( $postArray['keyword'] ) )
            {
                $sql .= ' AND dr.data LIKE :keyword ';
                $keyword = "%" . $postArray['keyword'] . "%";
                $searchArray = array_merge($searchArray, array(':keyword' => $keyword,));
            }
            
            // 返信内容
            if( !empty( $postArray['reply'] ) )
            {
                $sql .= ' AND reply LIKE :reply ';
                $file = "%" . $postArray['reply'] . "%";
                $searchArray = array_merge($searchArray, array(':reply' => reply,));
            }

            // 承認状況
            if( !empty( $postArray['approval'] ) )
            {
                $sql .= ' AND approval >= :approval ';
                $searchArray = array_merge($searchArray, array(':approval' => $postArray['approval'],));
            }

            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");

            return $sql;
        }

        /**
         * 日報一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   日報条件追加SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sqlSort = ' ORDER BY dr.target_date DESC';

            // ソート条件作成
            $sortSqlList = array(
                                3    => ' ORDER BY dr.target_date DESC',
                                4    => ' ORDER BY dr.target_date',
                                5    => ' ORDER BY v.organization_name DESC',
                                6    => ' ORDER BY v.organization_name',
                                7    => ' ORDER BY v.user_name DESC',
                                8    => ' ORDER BY v.user_name',
                                9    => " ORDER BY string_agg(dr.data,' ') DESC",
                                10   => " ORDER BY string_agg(dr.data,' ')",
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
         * 日報フォーム取得
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getFormListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getFormListData");
           
            $searchArray = array();
            
            $sql = ' SELECT *'
                 . '   FROM m_daily_report_form'
                 . '  WHERE true';
                
            // AND条件
            if( !empty( $postArray['form_id'] ) )
            {
                $sql .= ' AND form_id = :form_id ';
                $formIdArray = array(':form_id' => $postArray['form_id'],);
                $searchArray = array_merge($searchArray, $formIdArray);
            }

            $result = $DBA->executeSQL($sql, $searchArray);
            
            $displayItemDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getFormListData");
                return $displayItemDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }
            
            $Log->trace("END getFormListData");
            return $displayItemDataList;
        }

        /**
         * 詳細設定情報と登録済みデータを取得
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getFormDetailListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getFormDetailListData");
           
            $searchArray = array();
            
            $sql = " SELECT mrd.*,to_char(tr.target_date,'YYYY/MM/DD') as target_date,tr.data,tr.user_id FROM m_daily_report_form as mr"
                 . '   JOIN m_daily_report_form_details as mrd on (mr.form_id = mrd.form_id)'
                 . '   LEFT JOIN (SELECT *'
                 . '                FROM t_daily_report_details'
                 . '               WHERE daily_report_id = :daily_report_id'
                 . '              ) as tr on (mrd.form_details_id = tr.form_details_id)'
                 . '  WHERE mr.form_id = :form_id'
                 . '  ORDER BY mrd.disp_sort;';
                
            // AND条件
            if( !empty( $postArray['form_id'] ) )
            {
                $formIdArray = array(':form_id' => $postArray['form_id'],);
                $searchArray = array_merge($searchArray, $formIdArray);
            }

            if( !empty( $postArray['daily_report_id'] ) )
            {
                $formIdArray = array(':daily_report_id' => $postArray['daily_report_id']);
                $searchArray = array_merge($searchArray, $formIdArray);
            }else{
                $formIdArray = array(':daily_report_id' => 0);
                $searchArray = array_merge($searchArray, $formIdArray);
            }

            $result = $DBA->executeSQL($sql, $searchArray);
            
            $displayItemDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getFormDetailListData");
                return $displayItemDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }
            
            $Log->trace("END getFormDetailListData");
            return $displayItemDataList;
        }
        
        /**
         * 登録・更新用に詳細設定情報取得
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getFormDetailList( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getFormDetailList");
           
            $searchArray = array();
            $sql = ' SELECT mrd.* FROM m_daily_report_form as mr'
                 . '   JOIN m_daily_report_form_details as mrd on (mr.form_id = mrd.form_id)'
                 . '  WHERE mr.form_id = :form_id'
                 . '    AND mrd.form_type IN (2,3,4,5,6,7,8,9)'
                 . '  ORDER BY disp_sort;';
                
            // AND条件
            if( !empty( $postArray['form_id'] ) )
            {
                $formId = array(':form_id' => $postArray['form_id'],);
                $searchArray = array_merge($searchArray, $formId);
            }

            $result = $DBA->executeSQL($sql, $searchArray);
            
            $displayItemDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getFormDetailList");
                return $displayItemDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }
            
            $Log->trace("END getFormDetailList");
            return $displayItemDataList;
        }

        /**
         * 日報新規データ登録
         * @param    $postArray(日報テーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function addNewData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            // 一覧表を格納する空の配列宣言
            $dailyReportDataList = array();
            
            $parameters = array(
                ':target_date'                  => $postArray['target_date'],
                ':user_id'                      => $postArray['user_id'],
                ':organization_id'              => $postArray['organization_id'],
                ':form_id'                      => $postArray['form_id'],
                ':data'                         => $postArray['data'],
                ':registration_user_id'         => $postArray['registration_user_id'],
                ':registration_organization'    => $postArray['registration_organization'],
                ':update_user_id'               => $postArray['update_user_id'],
                ':update_organization'          => $postArray['update_organization'],
            );

            $sql = ' INSERT INTO t_daily_report( '
                 . '                    target_date'
                 . '                  , user_id'
                 . '                  , organization_id'
                 . '                  , form_id'
                 . '                  , data'
                 . '                  , registration_time'
                 . '                  , registration_user_id'
                 . '                  , registration_organization'
                 . '                  , update_time'
                 . '                  , update_user_id'
                 . '                  , update_organization'
                 . '                  ) VALUES ('
                 . '                    :target_date'
                 . '                  , :user_id'
                 . '                  , :organization_id'
                 . '                  , :form_id'
                 . '                  , :data'
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
                $errMsg = "日報の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3080";
            }

                        // 登録した通達連絡IDを取得
            $sql = ' SELECT daily_report_id FROM t_daily_report'
                 . '  WHERE target_date = :target_date'
                 . '    AND user_id = :user_id'
                 . '    AND organization_id = :organization_id'
                 . '    AND form_id = :form_id'
                 . '    AND data = :data'
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
                $errMsg = "登録した日報の取得失敗";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewData");
                return $dailyReportDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $dailyReportDataList = $data;
            }
            
            $Log->trace("END addNewData");
            
            return $dailyReportDataList;
        }

        /**
         * 日報詳細新規データ登録
         * @param    $postArray(日報テーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function addNewDetailsData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewDetailsData");

            $parameters = array(
                ':daily_report_id'              => $postArray['daily_report_id'],
                ':target_date'                  => $postArray['target_date'],
                ':user_id'                      => $postArray['user_id'],
                ':organization_id'              => $postArray['organization_id'],
                ':form_id'                      => $postArray['form_id'],
                ':form_details_id'              => $postArray['form_details_id'],
                ':form_type'                    => $postArray['form_type'],
                ':data'                         => $postArray['data'],
                ':disp_sort'                    => $postArray['disp_sort'],
                ':registration_user_id'         => $postArray['registration_user_id'],
                ':registration_organization'    => $postArray['registration_organization'],
                ':update_user_id'               => $postArray['update_user_id'],
                ':update_organization'          => $postArray['update_organization'],
            );

            $sql = ' INSERT INTO t_daily_report_details( '
                 . '                    daily_report_id'
                 . '                  , target_date'
                 . '                  , user_id'
                 . '                  , organization_id'
                 . '                  , form_id'
                 . '                  , form_details_id'
                 . '                  , form_type'
                 . '                  , data'
                 . '                  , disp_sort'
                 . '                  , registration_time'
                 . '                  , registration_user_id'
                 . '                  , registration_organization'
                 . '                  , update_time'
                 . '                  , update_user_id'
                 . '                  , update_organization'
                 . '                  ) VALUES ('
                 . '                    :daily_report_id'
                 . '                  , :target_date'
                 . '                  , :user_id'
                 . '                  , :organization_id'
                 . '                  , :form_id'
                 . '                  , :form_details_id'
                 . '                  , :form_type'
                 . '                  , :data'
                 . '                  , :disp_sort'
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
                $errMsg = "日報詳細の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3080";
            }

            $Log->trace("END addNewDetailsData");
            return "MSG_BASE_0000";
        }

        /**
         * 日報新規データ更新
         * @param    $postArray(日報テーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function updateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START updateData");

            $parameters = array(
                ':daily_report_id'              => $postArray['daily_report_id'],
                ':data'                         => $postArray['data'],
                ':update_user_id'               => $postArray['update_user_id'],
                ':update_organization'          => $postArray['update_organization'],
            );

            // 日報テーブル更新
            $sql = 'UPDATE t_daily_report SET'
                . '   data = :data'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE daily_report_id = :daily_report_id';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // 日報テーブルの登録結果がfalseの場合、エラーメッセージを出力
                $Log->warn("MSG_ERR_3085");
                $errMsg = "日報の更新処理にエラーが生じました。";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END updateData");
            return "MSG_BASE_0000";
        }

        /**
         * 日報新規データ登録
         * @param    $postArray(日報テーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function updateDetailsData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START updateData");

            $parameters = array(
                ':daily_report_id'              => $postArray['daily_report_id'],
                ':form_details_id'              => $postArray['form_details_id'],
                ':data'                         => $postArray['data'],
                ':update_user_id'               => $postArray['update_user_id'],
                ':update_organization'          => $postArray['update_organization'],
            );

            // 日報テーブル更新
            $sql = 'UPDATE t_daily_report_details SET'
                . '   data = :data'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE daily_report_id = :daily_report_id'
                . '   AND form_details_id = :form_details_id';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // 日報テーブルの登録結果がfalseの場合、エラーメッセージを出力
                $Log->warn("MSG_ERR_3085");
                $errMsg = "日報の更新処理にエラーが生じました。";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END updateData");
            return "MSG_BASE_0000";
        }

        /**
         * 日報のデータ削除
         * @param    $postArray(日報ID/サムネイルファイル名/添付ファイル名)
         * @return   SQLの実行結果
         */
        public function delData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delData");

            $parameters = array(
                ':daily_report_id'                  => $postArray['daily_report_id'],
            );
            
            $sql = ' DELETE FROM t_daily_report'
                 . ' WHERE daily_report_id = :daily_report_id  ';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();
                // SQL実行エラー
                $Log->warn("日報の削除に失敗しました。");
                $errMsg = "日報の削除に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END delData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END delData");

            return "MSG_BASE_0000";
        }
        
        /**
         * 日報詳細のデータ削除
         * @param    $postArray(日報ID/サムネイルファイル名/添付ファイル名)
         * @return   SQLの実行結果
         */
        public function delDetailsData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delDetailsData");

            $parameters = array(
                ':daily_report_id'                  => $postArray['daily_report_id'],
            );
            
            $sql = ' DELETE FROM t_daily_report_details'
                 . ' WHERE daily_report_id = :daily_report_id  ';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();
                // SQL実行エラー
                $Log->warn("日報の削除に失敗しました。");
                $errMsg = "日報の削除に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END delDetailsData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END delDetailsData");

            return "MSG_BASE_0000";
        }
        
        /**
         * 詳細設定情報と登録済みデータを取得
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getDailyReportComentListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getFormDetailListData");
           
            $searchArray = array();
            
            $sql = ' SELECT  drc.daily_report_comment_id '
                 . '        ,drc.contents'
                 . "        ,to_char(drc.registration_time,'YYYY/MM/DD HH24時MI分SS秒') as registration_time"
                 . '        ,drc.user_id'
                 . '        ,v.user_name'
                 . '        ,v.organization_name'
                 . '   FROM t_daily_report_comment as drc'
                 . "   LEFT JOIN (SELECT * FROM v_user WHERE eff_code = '適用中') v on (drc.user_id = v.user_id)"
                 . '  WHERE drc.daily_report_id = :daily_report_id'
                 . '  ORDER BY drc.registration_time;';
                
            if( !empty($postArray['daily_report_id']) )
            {
                $targetDateArray = array(':daily_report_id' => $postArray['daily_report_id'],);
                $searchArray = array_merge($searchArray, $targetDateArray);
            }

            $result = $DBA->executeSQL($sql, $searchArray);
            
            $displayItemDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getFormDetailListData");
                return $displayItemDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }
            
            $Log->trace("END getFormDetailListData");
            return $displayItemDataList;
        }
        
        /**
         * 日報コメントデータ登録
         * @param    $postArray(日報テーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function addNewCommentData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewCommentData");

            $parameters = array(
                ':daily_report_id'              => $postArray['daily_report_id'],
                ':target_date'                  => $postArray['target_date'],
                ':user_id'                      => $postArray['user_id'],
                ':form_id'                      => $postArray['form_id'],
                ':contents'                     => $postArray['contents'],
                ':registration_user_id'         => $postArray['registration_user_id'],
                ':registration_organization'    => $postArray['registration_organization'],
                ':update_user_id'               => $postArray['update_user_id'],
                ':update_organization'          => $postArray['update_organization'],
            );

            $sql = ' INSERT INTO t_daily_report_comment( '
                 . '                    daily_report_id'
                 . '                  , target_date'
                 . '                  , user_id'
                 . '                  , form_id'
                 . '                  , contents'
                 . '                  , registration_time'
                 . '                  , registration_user_id'
                 . '                  , registration_organization'
                 . '                  , update_time'
                 . '                  , update_user_id'
                 . '                  , update_organization'
                 . '                  ) VALUES ('
                 . '                    :daily_report_id'
                 . '                  , :target_date'
                 . '                  , :user_id'
                 . '                  , :form_id'
                 . '                  , :contents'
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
                $errMsg = "日報のコメント登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3080";
            }

            $Log->trace("END addNewCommentData");
            return "MSG_BASE_0000";
        }
        
        /**
         * 日報コメントのデータ削除
         * @param    $postArray(日報コメントID)
         * @return   SQLの実行結果
         */
        public function delCommentData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delCommentData");

            $parameters = array(
                ':daily_report_comment_id'  => $postArray['daily_report_comment_id'],
            );
            
            $sql = ' DELETE FROM t_daily_report_comment'
                 . ' WHERE daily_report_comment_id = :daily_report_comment_id  ';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();
                // SQL実行エラー
                $Log->warn("日報コメントの削除に失敗しました。");
                $errMsg = "日報コメントの削除に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END delCommentData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END delCommentData");

            return "MSG_BASE_0000";
        }
        
    }

?>