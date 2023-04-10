<?php
    /**
     * @file      テロップマスタ
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      テロップマスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    
    /**
     * テロップクラス
     * @note   テロップテーブルの管理を行う。
     */
    class Telop extends BaseModel
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
         * テロップ一覧画面一覧表
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
            $telopDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $telopDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $telopDataList, $data);
            }

            $Log->trace("END getListData");

            // 一覧表を返す
            return $telopDataList;
        }

        /**
         * テロップ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   テロップ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $searchArray = array();
            
            $sql = ' SELECT  telop_id'
                    . '     ,contents'
                    . '     ,link_url'
                    . "     ,to_char(application_start_date,'YYYY/MM/DD') as application_start_date "
                    . "     ,to_char(application_end_date,'YYYY/MM/DD') as application_end_date "
                    . 'FROM m_telop WHERE telop_id is not null';
            
            // 検索条件追加
            // 内容
            if( !empty( $postArray['searchContents'] ) )
            {
                $sql .= ' AND contents LIKE :searchContents ';
                $contents = "%" . $postArray['searchContents'] . "%";
                $searchArray = array_merge($searchArray, array(':searchContents' => $contents,));
            }
            
            // 適用開始日
            if( !empty( $postArray['sAppStartDate'] ) )
            {
                $sql .= " AND application_start_date >= :sAppStartDate ";
                $searchArray = array_merge($searchArray, array(':sAppStartDate' => $postArray['sAppStartDate'],));
            }
            if( !empty( $postArray['eAppEndDate'] ) )
            {
                $sql .= " AND application_end_date <= :eAppEndDate ";
                $searchArray = array_merge($searchArray, array(':eAppEndDate' => $postArray['eAppEndDate'],));
            }
            
            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");

            return $sql;
        }

        /**
         * テロップ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   テロップ条件追加SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sqlSort = ' ORDER BY registration_time DESC';

            // ソート条件作成
            $sortSqlList = array(
                                3    => ' ORDER BY contents DESC, registration_time DESC',
                                4    => ' ORDER BY contents, registration_time',
                                5    => ' ORDER BY linkUrl DESC, registration_time DESC',
                                6    => ' ORDER BY linkUrl, registration_time',
                                7    => ' ORDER BY application_start_date DESC, registration_time DESC',
                                8    => ' ORDER BY application_start_date, registration_time',
                                9    => ' ORDER BY application_end_date DESC, registration_time DESC',
                                10   => ' ORDER BY application_end_date, registration_time',
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
         * テロップ修正画面登録データ検索
         * @param    $top_message_id
         * @return   SQLの実行結果
         */
        public function getTelopData( $telop_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getTelopData");

            $sql = '   SELECT  telop_id'
                    . '       ,contents'
                    . '       ,link_url'
                    . '       ,link_underline'
                    . '       ,color'
                    . '       ,size'
                    . '       ,scroll_check'
                    . '       ,scroll_direction'
                    . '       ,scroll_behavior'
                    . '       ,scroll_loop'
                    . '       ,scroll_amount'
                    . '       ,flashing'
                    . '       ,bold'
                    . '       ,font'
                    . '       ,centering'
                    . '       ,background_color'
                    . "       ,to_char(application_start_date,'YYYY/MM/DD') as application_start_date"
                    . "       ,to_char(application_end_date,'YYYY/MM/DD') as application_end_date"
                    . '       ,thumbnail'
                    . '       ,registration_time'
                    . '       ,registration_user_id'
                    . '       ,registration_organization'
                    . '       ,update_time'
                    . '       ,update_user_id'
                    . '       ,update_organization'
                    . '  FROM m_telop'
                    . ' where telop_id = :telop_id';
            
            $searchArray = array( ':telop_id' => $telop_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $telopDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result == false )
            {
                $Log->trace("END getTelopData");
                return $telopDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $telopDataList = $data;
            }

            $Log->trace("END getTelopData");

            return $telopDataList;
        }
        
        /**
         * テロップ新規データ登録
         * @param    $postArray(テロップテーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function addNewData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $parameters = array(
                ':contents'                     => $postArray['contents'],
                ':link_url'                     => $postArray['link_url'],
                ':link_underline'               => $postArray['link_underline'],
                ':color'                        => $postArray['color'],
                ':size'                         => $postArray['size'],
                ':scroll_check'                 => $postArray['scroll_check'],
                ':scroll_direction'             => $postArray['scroll_direction'],
                ':scroll_behavior'              => $postArray['scroll_behavior'],
                ':scroll_loop'                  => $postArray['scroll_loop'],
                ':scroll_amount'                => $postArray['scroll_amount'],
                ':flashing'                     => $postArray['flashing'],
                ':bold'                         => $postArray['bold'],
                ':font'                         => $postArray['font'],
                ':centering'                    => $postArray['centering'],
                ':background_color'             => $postArray['background_color'],
                ':application_start_date'       => $postArray['application_start_date'],
                ':application_end_date'         => $postArray['application_end_date'],
                ':thumbnail'                    => $postArray['thumbnail'],
                ':registration_user_id'         => $postArray['registration_user_id'],
                ':registration_organization'    => $postArray['registration_organization'],
                ':update_user_id'               => $postArray['update_user_id'],
                ':update_organization'          => $postArray['update_organization'],
            );

            $sql = ' INSERT INTO m_telop( '
                 . '                    contents'
                 . '                  , link_url'
                 . '                  , link_underline'
                 . '                  , color'
                 . '                  , size'
                 . '                  , scroll_check'
                 . '                  , scroll_direction'
                 . '                  , scroll_behavior'
                 . '                  , scroll_loop'
                 . '                  , scroll_amount'
                 . '                  , flashing'
                 . '                  , bold'
                 . '                  , font'
                 . '                  , centering'
                 . '                  , background_color'
                 . '                  , application_start_date'
                 . '                  , application_end_date'
                 . '                  , thumbnail'
                 . '                  , registration_time'
                 . '                  , registration_user_id'
                 . '                  , registration_organization'
                 . '                  , update_time'
                 . '                  , update_user_id'
                 . '                  , update_organization'
                 . '                  ) VALUES ('
                 . '                    :contents'
                 . '                  , :link_url'
                 . '                  , :link_underline'
                 . '                  , :color'
                 . '                  , :size'
                 . '                  , :scroll_check'
                 . '                  , :scroll_direction'
                 . '                  , :scroll_behavior'
                 . '                  , :scroll_loop'
                 . '                  , :scroll_amount'
                 . '                  , :flashing'
                 . '                  , :bold'
                 . '                  , :font'
                 . '                  , :centering'
                 . '                  , :background_color'
                 . '                  , :application_start_date'
                 . '                  , :application_end_date'
                 . '                  , :thumbnail'
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
                $errMsg = "テロップの登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3080";
            }

            $Log->trace("END addNewData");
            return "MSG_BASE_0000";
        }

        /**
         * テロップ新規データ登録
         * @param    $postArray(テロップテーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function updateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $parameters = array(
                ':telop_id'                     => $postArray['telop_id'],
                ':contents'                     => $postArray['contents'],
                ':link_url'                     => $postArray['link_url'],
                ':link_underline'               => $postArray['link_underline'],
                ':color'                        => $postArray['color'],
                ':size'                         => $postArray['size'],
                ':scroll_check'                 => $postArray['scroll_check'],
                ':scroll_direction'             => $postArray['scroll_direction'],
                ':scroll_behavior'              => $postArray['scroll_behavior'],
                ':scroll_loop'                  => $postArray['scroll_loop'],
                ':scroll_amount'                => $postArray['scroll_amount'],
                ':flashing'                     => $postArray['flashing'],
                ':bold'                         => $postArray['bold'],
                ':font'                         => $postArray['font'],
                ':centering'                    => $postArray['centering'],
                ':background_color'             => $postArray['background_color'],
                ':application_start_date'       => $postArray['application_start_date'],
                ':application_end_date'         => $postArray['application_end_date'],
                ':thumbnail'                    => $postArray['thumbnail'],
                ':update_user_id'               => $postArray['update_user_id'],
                ':update_organization'          => $postArray['update_organization'],
            );
            
            // テロップテーブル更新
            $sql = 'UPDATE m_telop SET'
                . '   contents = :contents'
                . ' , link_url = :link_url'
                . ' , link_underline = :link_underline'
                . ' , color = :color'
                . ' , size = :size'
                . ' , scroll_check = :scroll_check'
                . ' , scroll_direction = :scroll_direction'
                . ' , scroll_behavior = :scroll_behavior'
                . ' , scroll_loop = :scroll_loop'
                . ' , scroll_amount = :scroll_amount'
                . ' , flashing = :flashing'
                . ' , bold = :bold'
                . ' , font = :font'
                . ' , centering = :centering'
                . ' , background_color = :background_color'
                . ' , application_start_date = :application_start_date'
                . ' , application_end_date = :application_end_date'
                . ' , thumbnail = :thumbnail'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE telop_id = :telop_id';
//                . ' WHERE telop_id = :telop_id AND update_user_id = :update_user_id AND update_time = :update_time '; 排他制御用

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // テロップテーブルの登録結果がfalseの場合、エラーメッセージを出力
                $Log->warn("MSG_ERR_3085");
                $errMsg = "テロップの更新処理にエラーが生じました。";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END addNewData");
            return "MSG_BASE_0000";
        }

        /**
         * テロップのデータ削除
         * @param    $postArray(テロップID/サムネイルファイル名/添付ファイル名)
         * @return   SQLの実行結果
         */
        public function delData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delData");

            $sql = ' DELETE FROM m_telop WHERE telop_id = :telop_id  ';
            $parameters = array(
                ':telop_id' => $postArray['telop_id'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters ) )
            {
                // SQL実行エラー　ロールバック対応
                $DBA->rollBack();
                // SQL実行エラー
                $Log->warn("テロップの削除に失敗しました。");
                $errMsg = "テロップの削除に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END delData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END delData");

            return "MSG_BASE_0000";
        }
        
    }

?>