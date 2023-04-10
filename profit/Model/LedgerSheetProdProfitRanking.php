<?php
    /**
     * @file      商品粗利ベスト10一覧表 [M]
     * @author    川橋
     * @date      2019/06/06
     * @version   1.00
     * @note      帳票 - 商品粗利ベスト10一覧表の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetProdProfitRanking extends BaseModel
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
            
            //print_r( "sql: ".$sql);
            //print_r($searchArray);
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

            //$where = "  WHERE a.sect_cd ~ '^[0-9]*$' ";
            $where = "  WHERE  a.prod_sale_amount <> 0 ";
            if( !empty( $postArray['start_date'] ) )
            {
                $where .= ' AND date(proc_date) >= date(:start_date) ';
                $ledgerSheetFormIdArray = array(':start_date' => $postArray['start_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            if( !empty( $postArray['end_date'] ) )
            {
                $where .= ' AND date(proc_date) <= date(:end_date) ';
                $ledgerSheetFormIdArray = array(':end_date' => $postArray['end_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            if( $postArray['sect_cd'] !== 'false' )
            {
                $where .= " AND m.sect_cd in (".$postArray['sect_cd'].") ";
            }
            //if( $postArray['organization_id'] !== 'false' )
            //{
            //    $where .= " AND a.organization_id in (".$postArray['organization_id'].") ";
            //}
            if( $postArray['organization_id'] !== 'compile' && $postArray['organization_id'] !== 'all')
            {
                $where .= " AND a.organization_id in (".$postArray['organization_id'].") ";
            }

            $sql  = " SELECT ";
            if ($postArray['organization_id'] !== 'compile'){
                $sql .= "    a.organization_id ";
                $sql .= "   ,v.abbreviated_name, ";
            }
            $sql .= "    a.prod_cd ";
            $sql .= "   ,m.prod_nm ";
            $sql .= "   ,m.prod_kn ";
            $sql .= "   ,m.sect_cd ";
            $sql .= "   ,m.saleprice ";
            $sql .= "   ,m.head_costprice               AS costprice ";
            $sql .= "   ,n.sect_nm ";
            $sql .= "   ,SUM(a.prod_sale_amount)        AS prod_sale_amount ";
            $sql .= "   ,SUM(a.prod_sale_total)         AS prod_sale_total ";
            $sql .= "   ,SUM(a.prod_profit)             AS prod_profit ";
            //$sql .= "   ,sumall.prod_sale_total         AS sumall_prod_sale_total";
            // 構成比
            $sql .= "   ,CASE WHEN sumall.prod_sale_total = 0 THEN 0 ";
            $sql .= "        ELSE ROUND((SUM(a.prod_sale_total) / sumall.prod_sale_total) * 100, 2) ";
            $sql .= "    END AS composition_ratio ";
            // 粗利率
            $sql .= "  ,CASE WHEN SUM(a.prod_sale_total) = 0 THEN 0 ";
            $sql .= "        ELSE ROUND((SUM(a.prod_profit) / SUM(a.prod_sale_total)) * 100, 2) ";
            $sql .= "    END AS gross_profit_margin ";
            $sql .= "   FROM jsk5110 a ";
            $sql .= "   LEFT JOIN mst0201 m ON ( m.prod_cd = a.prod_cd and m.organization_id = a.organization_id ) ";
            $sql .= "   LEFT JOIN mst1201 n ON ( n.sect_cd = m.sect_cd and n.organization_id = m.organization_id ) ";
            $sql .= "   LEFT JOIN v_organization v ON v.organization_id = a.organization_id ";
            // 全商品売上金額
            if ($postArray['organization_id'] !== 'compile'){
                $sql .= "   LEFT JOIN ( ";
                $sql .= "       SELECT ";
                $sql .= "            organization_id    AS organization_id, ";
                $sql .= "           SUM(prod_sale_total) AS prod_sale_total ";
                $sql .= "       FROM jsk5110 ";
                $sql .= "       WHERE prod_sale_amount <> 0 ";
                $sql .= "       AND   date(proc_date) >= date(:start_date) ";
                $sql .= "       AND   date(proc_date) <= date(:end_date) ";
                $sql .= "       GROUP BY organization_id ";
                $sql .= "   ) sumall ON (sumall.organization_id = a.organization_id) ";
            }
            else{
                $sql .= "   ,( ";
                $sql .= "       SELECT ";
                $sql .= "           SUM(prod_sale_total) AS prod_sale_total ";
                $sql .= "       FROM jsk5110 ";
                $sql .= "       WHERE prod_sale_amount <> 0 ";
                $sql .= "       AND   date(proc_date) >= date(:start_date) ";
                $sql .= "       AND   date(proc_date) <= date(:end_date) ";
                $sql .= "   ) sumall ";
            }
            $sql .= $where;
            $sql .= "   GROUP BY ";
            if ($postArray['organization_id'] !== 'compile'){
                $sql .= "        a.organization_id, ";
            }
            $sql .= "        abbreviated_name ";
            $sql .= "       ,a.prod_cd ";
            $sql .= "       ,m.prod_nm ";
            $sql .= "       ,m.prod_kn ";
            $sql .= "       ,m.sect_cd ";
            $sql .= "       ,m.saleprice ";
            $sql .= "       ,m.head_costprice ";
            $sql .= "       ,n.sect_nm ";
            $sql .= "       ,sumall.prod_sale_total ";

            // 並び順
            $sql .= '   ORDER BY  ';
            if ($postArray['organization_id'] !== 'compile'){
                $sql .= '   a.organization_id, ';
            }
            $sql .= '   SUM(a.prod_profit) DESC, a.prod_cd ';
            
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
