<?php
    /**
     * @file      期間原価一覧 [M]
     * @author    vergara miguel
     * @date      2019/02/13
     * @version   1.00
     * @note      帳票
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetTemporaryCost extends BaseModel
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
         * 表示項目設定マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getFormListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

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
         * 帳票フォーム一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $where = '';
            if( !empty( $postArray['start_date'] ) )
            {
                $where .= ' where date(a.order_date_str) >= date(:start_date) ';
                $ledgerSheetFormIdArray = array(':start_date' => $postArray['start_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            if( !empty( $postArray['end_date'] ) )
            {
                $where .= ' AND date(a.order_date_str) <= date(:end_date) ';
                $ledgerSheetFormIdArray = array(':end_date' => $postArray['end_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            if( !empty( $postArray['organization_id'] ) )
            {
                $where .= ' AND b.organization_id = :organization_id ';
                $ledgerSheetFormIdArray = array(':organization_id' => $postArray['organization_id'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            if( !empty( $postArray['prod_k'] ) )
            {
                $where .= ' AND a.prod_cd >= :prod_k ';
                $ledgerSheetFormIdArray = array(':prod_k' => $postArray['prod_k'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            if( !empty( $postArray['prod_s'] ) )
            {
                $where .= ' AND a.prod_cd <= :prod_s ';
                $ledgerSheetFormIdArray = array(':prod_s' => $postArray['prod_s'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            if( !empty( $postArray['prod_nm'] ) )
            {
                $where .= ' AND b.prod_nm ~ :prod_nm ';
                $ledgerSheetFormIdArray = array(':prod_nm' => $postArray['prod_nm'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            $sql  = " SELECT distinct
                    a.prod_cd,
                    b.prod_nm,
                    a.supp_cd,
                    date(a.order_date_str) AS order_date_str,
                    date(a.order_date_end) AS order_date_end,
                    a.peri_costprice,
                    a.order_lot,
                    b.head_costprice,
                    v.abbreviated_name

                   FROM mst1401 a
                   JOIN mst0201 b ON a.prod_cd = b.prod_cd and a.organization_id = b.organization_id
                   JOIN v_organization v ON a.organization_id = v.organization_id
                ";
            $sql .= $where;
            $sql .= "   ORDER BY a.prod_cd asc  ";
            
            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 表示項目設定マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getMcodeData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMcodeData");

            $searchArray = array();
            $sql = $this->creatMSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $displayItemDataList = array();

            if( $result === false )
            {
                $Log->trace("END getMcodeData");
                return $displayItemDataList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }

            $Log->trace("END getMcodeData");
            return $displayItemDataList;
        }

        /**
         * マッピング情報取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatMSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT * '
               .   '   FROM m_pos_mapping'
               .   '  WHERE mapping_name_id in'
               .   '        (SELECT mapping_name_id FROM m_mapping_name WHERE mapping_code = :mcode)';

            // AND条件
            if( !empty( $postArray['mcode'] ) )
            {
                $ledgerSheetFormIdArray = array(':mcode' => $postArray['mcode'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 表示項目設定マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getReportDataDay( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getReportDataDay");

            $searchArray = array();
            $sql = $this->creatRdSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $displayItemDataList = array();

            if( $result === false )
            {
                $Log->trace("END getReportDataDay");
                return $displayItemDataList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }

            $Log->trace("END getReportDataDay");
            return $displayItemDataList;
        }

        /**
         * 帳票フォーム一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatRdSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT * FROM t_report_data_day WHERE true ';

            // AND条件
            if( !empty($postArray['organization_id']) )
            {
                $sql .= ' AND organization_id = :organization_id ';
                $organizationIdArray = array(':organization_id' => $postArray['organization_id'],);
                $searchArray = array_merge($searchArray, $organizationIdArray);
            }

            if( !empty($postArray['target_date']) )
            {
                $sql .= ' AND target_date = :target_date ';
                $targetDateArray = array(':target_date' => $postArray['target_date'],);
                $searchArray = array_merge($searchArray, $targetDateArray);
            }

            if( !empty( $postArray['report_form_id'] ) )
            {
                $sql .= ' AND report_form_id = :report_form_id ';
                $reportFormIdArray = array(':report_form_id' => $postArray['report_form_id'],);
                $searchArray = array_merge($searchArray, $reportFormIdArray);
            }

            if( !empty( $postArray['report_form_detail_id'] ) )
            {
                $sql .= ' AND report_form_detail_id = :report_form_detail_id ';
                $reportFormDetailIdArray = array(':report_form_detail_id' => $postArray['report_form_detail_id'],);
                $searchArray = array_merge($searchArray, $reportFormDetailIdArray);
            }

            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 表示項目設定マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getMcodeListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMcodeListData");

            $searchArray = array();
            $sql = $this->creatMLSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $displayItemDataList = array();

            if( $result === false )
            {
                $Log->trace("END getMcodeListData");
                return $displayItemDataList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }

            $Log->trace("END getMcodeListData");
            return $displayItemDataList;
        }

        /**
         * 帳票フォーム一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatMLSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT * FROM t_mcode_data_day WHERE true ';

            // AND条件
            if( !empty($postArray['organization_id']) )
            {
                $sql .= ' AND organization_id = :organization_id ';
                $isDelArray = array(':organization_id' => $postArray['organization_id'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty($postArray['target_date']) )
            {
                $sql .= ' AND target_date = :target_date ';
                $isDelArray = array(':target_date' => $postArray['target_date'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty( $postArray['mcode'] ) )
            {
                $sql .= ' AND mcode = :mcode ';
                $ledgerSheetFormIdArray = array(':mcode' => $postArray['mcode'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * Mコードチェック
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$マッピング情報  失敗：無
         */
        public function checkMcode( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START checkMcode");

            $searchArray = array();
            $sql = $this->creatMCSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $displayItemDataList = array();

            if( $result === false )
            {
                $Log->trace("END checkMcode");
                return $displayItemDataList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }

            $Log->trace("END checkMcode");
            return $displayItemDataList;
        }

        /**
         * Mコードチェック用SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatMCSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT * FROM m_mapping_name WHERE true ';

            // AND条件
            if( !empty( $postArray['mapping_code'] ) )
            {
                $sql .= ' AND mapping_code = :mapping_code ';
                $mappingCodeArray = array(':mapping_code' => $postArray['mapping_code'],);
                $searchArray = array_merge($searchArray, $mappingCodeArray);
            }

            $Log->trace("END creatSQL");
            return $sql;
        }
    }
?>
