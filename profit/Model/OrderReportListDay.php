<?php
    /**
     * @file      仕入報告書
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      仕入れ報告書の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 仕入報告書クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class OrderReportListDay extends BaseModel
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
         * 売上報告書フォーム取得
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getFormListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");
           
            $searchArray = array();
            
            $sql = ' SELECT *'
                 . '   FROM m_report_form'
                 . '  WHERE true';
                
            // AND条件
            if( !empty($postArray['is_del']) )
            {
                $sql .= ' AND is_del = :is_del ';
                $isDelArray = array(':is_del' => $postArray['is_del'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty( $postArray['report_form_id'] ) )
            {
                $sql .= ' AND report_form_id = :report_form_id ';
                $ledgerSheetFormIdArray = array(':report_form_id' => $postArray['report_form_id'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            $result = $DBA->executeSQL($sql, $searchArray);
            
            $displayItemDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $displayItemDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }
            
            $Log->trace("END getListData");
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
            
            $sql = ' SELECT mrd.*,tr.data FROM m_report_form as mr'
                 . '   JOIN m_report_form_detail as mrd on (mr.report_form_id = mrd.report_form_id)'
                 . '   LEFT JOIN (SELECT *'
                 . '                FROM t_report_data_day'
                 . '               WHERE target_date = :target_date'
                 . '                 AND report_form_id = :report_form_id'
                 . '                 AND organization_id = :organization_id'
                 . '              ) as tr on (mrd.report_form_detail_id = tr.report_form_detail_id)'
                 . '  WHERE mr.report_form_id = :report_form_id'
                 . '  ORDER BY disp_order;';
                
            // AND条件
            if( !empty( $postArray['report_form_id'] ) )
            {
                $reportFormIdArray = array(':report_form_id' => $postArray['report_form_id'],);
                $searchArray = array_merge($searchArray, $reportFormIdArray);
            }

            if( !empty($postArray['organization_id']) )
            {
                $organizationIdArray = array(':organization_id' => $postArray['organization_id'],);
                $searchArray = array_merge($searchArray, $organizationIdArray);
            }

            if( !empty($postArray['target_date']) )
            {
                $targetDateArray = array(':target_date' => $postArray['target_date'],);
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
            
            $Log->trace("END getListData");
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
            $sql = ' SELECT mrd.* FROM m_report_form as mr'
                 . '   JOIN m_report_form_detail as mrd on (mr.report_form_id = mrd.report_form_id)'
                 . '  WHERE mr.report_form_id = :report_form_id'
                 . '    AND mrd.report_form_detail_type IN (2,3,4,5,6,7)';
                
            // AND条件
            if( !empty( $postArray['report_form_id'] ) )
            {
                $reportFormId = array(':report_form_id' => $postArray['report_form_id'],);
                $searchArray = array_merge($searchArray, $reportFormId);
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
         * 月次コスト 新規データ登録
         * @param    $postArray   入力パラメータ(コード/POS種別名/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO t_report_data_day( '
                . '                        target_date'
                . '                      , report_form_id'
                . '                      , report_type'
                . '                      , organization_id'
                . '                      , report_form_detail_id'
                . '                      , data'
                . '                      , registration_time'
                . '                      , registration_user_id'
                . '                      , registration_organization'
                . '                      , update_time'
                . '                      , update_user_id'
                . '                      , update_organization'
                . '                      ) VALUES ('
                . '                        :target_date'
                . '                      , :report_form_id'
                . '                      , :report_type'
                . '                      , :organization_id'
                . '                      , :report_form_detail_id'
                . '                      , :data'
                . '                      , current_timestamp'
                . '                      , :registration_user_id'
                . '                      , :registration_organization'
                . '                      , current_timestamp'
                . '                      , :update_user_id'
                . '                      , :update_organization)';

            $parameters = array(
                ':target_date'               => $postArray['target_date'],
                ':report_form_id'            => $postArray['report_form_id'],
                ':report_type'               => $postArray['report_type'],
                ':organization_id'           => $postArray['organization_id'],
                ':report_form_detail_id'     => $postArray['report_form_detail_id'],
                ':data'                      => $postArray['data'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "t_report_data_day" );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * 月次コスト 登録データ修正
         * @param    $postArray   入力パラメータ(POS種別ID/コード/組織ID/POS種別名/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $sql = 'UPDATE t_report_data_day '
                . '    SET  data                 = :data'
                . '       , update_time          = current_timestamp'
                . '       , update_user_id       = :update_user_id'
                . '       , update_organization  = :update_organization'
                . '  WHERE report_form_id        = :report_form_id'
                . '    AND report_form_detail_id = :report_form_detail_id'
                . '    AND target_date           = :target_date'
                . '    AND organization_id       = :organization_id';

            $parameters = array(
                ':report_form_id'            => $postArray['report_form_id'],
                ':report_form_detail_id'     => $postArray['report_form_detail_id'],
                ':target_date'               => $postArray['target_date'],
                ':organization_id'           => $postArray['organization_id'],
                ':data'                      => $postArray['data'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * 月次コスト データ削除
         * @param    $postArray   入力パラメータ(POS種別ID/コード/組織ID/POS種別名/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function del( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $sql = 'DELETE FROM t_report_data_day '
                . '  WHERE report_form_id        = :report_form_id'
                . '    AND target_date           = :target_date'
                . '    AND organization_id       = :organization_id';

            $parameters = array(
                ':report_form_id'        => $postArray['report_form_id'],
                ':target_date'           => $postArray['target_date'],
                ':organization_id'       => $postArray['organization_id'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }
    }

?>
