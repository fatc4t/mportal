<?php
    /**
     * @file      通達連絡閲覧
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      通達連絡閲覧の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    
    /**
     * 通達連絡閲覧クラス
     * @note   通達連絡閲覧の管理を行う。
     */
    class NoticeContactBrowsing extends BaseModel
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
         * 通達連絡閲覧画面データ検索
         * @param    $notice_contact_id
         * @return   SQLの実行結果
         */
        public function getNoticeContactBrowsingData( $notice_contact_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserData");

            $sql  = " SELECT *"
                  . "   FROM t_notice_contact"
                  . "  WHERE notice_contact_id = :notice_contact_id";

            
            $searchArray = array( ':notice_contact_id' => $notice_contact_id );

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
         * 通達連絡詳細データ検索
         * @param    $notice_contact_id
         * @return   SQLの実行結果
         */
        public function getNoticeContactBrowsingDetailsData( $notice_contact_id, $user_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserData");

            $sql  = " SELECT notice_contact_id,state"
                  . "   FROM t_notice_contact_details"
                  . "  WHERE notice_contact_id = :notice_contact_id"
                  . "    AND user_id = :user_id ";

            $searchArray = array( ':notice_contact_id' => $notice_contact_id,
                                  ':user_id' => $user_id,
                                );

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
         * 未読者リスト
         * @param    $notice_contact_id
         * @return   SQLの実行結果
         */
        public function getBrowsingList( $notice_contact_id, $state )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserData");

            $sql = ' SELECT   nc.user_id'
                 . '         ,un.user_name'
                 . '   FROM  t_notice_contact_details as nc'
                 . "         join (SELECT user_id, user_name FROM v_user WHERE eff_code = '適用中') as un on (nc.user_id =  un.user_id)"
                 . '  WHERE notice_contact_id = :notice_contact_id'
                 . '    AND state = :state';
            
            $searchArray = array( ':notice_contact_id' => $notice_contact_id,
                                  ':state' => $state,
                                );

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
         * 通達連絡詳細データを更新
         * @param    $postArray(通達連絡テーブルへの登録情報)
         * @return   SQLの実行結果
         */
        public function updateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $parameters = array(
                ':notice_contact_id'          => $postArray['notice_contact_id'],
                ':user_id'                    => $postArray['user_id'],
                ':state'                      => $postArray['state'],
                ':update_user_id'             => $postArray['update_user_id'],
                ':update_organization'        => $postArray['update_organization'],
            );

            // 通達連絡テーブル更新
            $sql = 'UPDATE t_notice_contact_details SET'
                . '   state = :state'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE notice_contact_id = :notice_contact_id'
                . '   AND user_id = :user_id';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // 通達連絡テーブルの登録結果がfalseの場合、エラーメッセージを出力
                $Log->warn("MSG_ERR_3085");
                $errMsg = "既読処理にエラーが生じました。";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            
            $Log->trace("END addNewData");
            return "MSG_BASE_0000";
        }
        
    }

?>