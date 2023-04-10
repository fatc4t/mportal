<?php
    /**
     * @file      ゼロ商品一覧表 [M]
     * @author    川橋
     * @date      2019/06/04
     * @version   1.00
     * @note      帳票 - ゼロ商品一覧表の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetNoSalesProdList extends BaseModel
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
            $where = "  WHERE a.last_sale_date <> '' ";
            //if( !empty( $postArray['start_date'] ) )
            //{
                //$where .= " AND SUBSTR(a.last_sale_date, 1, 8) <= TO_CHAR(date(:base_date) - interval '1 months', 'YYYYMMDD') ";
                $where .= " AND SUBSTR(a.last_sale_date, 1, 8) <= TO_CHAR(date(:base_date) - interval '31 days', 'YYYYMMDD') ";
                $ledgerSheetFormIdArray = array(':base_date' => $postArray['start_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            //}
            if( $postArray['sect_cd'] !== 'false' )
            {
                $where .= " AND m.sect_cd in (".$postArray['sect_cd'].") ";
            }
            if( $postArray['organization_id'] !== 'false' )
            {
                $where .= " AND a.organization_id in (".$postArray['organization_id'].") ";
            }

            $sql  = " SELECT
                        a.organization_id,
                        v.abbreviated_name,
                        a.prod_cd,
                        m.prod_nm,
                        m.prod_kn,
                        m.sect_cd,
                        n.sect_nm,
                        date(:base_date) - TO_DATE(SUBSTR(a.last_sale_date, 1, 8), 'YYYYMMDD') AS zero_duration,
                        TO_CHAR(TO_DATE(SUBSTR(a.last_sale_date, 1, 8), 'YYYYMMDD'), 'YYYY/MM/DD') AS last_sale_date
                    FROM jsk5100 a
                    LEFT JOIN mst0201 m ON ( m.prod_cd = a.prod_cd and m.organization_id = a.organization_id )
                    LEFT JOIN mst1201 n ON ( n.sect_cd = m.sect_cd and n.organization_id = m.organization_id )
                    LEFT JOIN v_organization v ON v.organization_id = a.organization_id  ";
            $sql .= $where;

            // 並び順
            $sql .= '   ORDER BY  ';
            if( !empty( $postArray['sort'] )){
                // 店舗(降順)
                if($postArray['sort'] == '1'){
                    $sql .= ' abbreviated_name DESC ';
                }
                // 店舗(昇順)
                else if($postArray['sort'] == '2'){
                    $sql .= ' abbreviated_name ';
                }
                // 部門名(降順)
                else if($postArray['sort'] == '3'){
                    $sql .= ' sect_nm DESC ';
                }
                // 部門名(昇順)
                else if($postArray['sort'] == '4'){
                    $sql .= ' sect_nm ';
                }
                // 商品コード(降順)
                else if($postArray['sort'] == '5'){
                    $sql .= ' a.prod_cd DESC ';
                }
                // 商品コード(昇順)
                else if($postArray['sort'] == '6'){
                    $sql .= ' a.prod_cd ';
                }
                // 商品名(降順)
                else if($postArray['sort'] == '7'){
                    $sql .= ' prod_nm DESC ';
                }
                // 商品名(昇順)
                else if($postArray['sort'] == '8'){
                    $sql .= ' prod_nm ';
                }
                // 商品名カナ(降順)
                else if($postArray['sort'] == '9'){
                    $sql .= ' prod_kn DESC ';
                }
                // 商品名カナ(昇順)
                else if($postArray['sort'] == '10'){
                    $sql .= ' prod_kn ';
                }
                // ゼロ期間(降順)
                else if($postArray['sort'] == '11'){
                    $sql .= " date(:base_date) - TO_DATE(SUBSTR(a.last_sale_date, 1, 8), 'YYYYMMDD') DESC ";
                }
                // ゼロ期間(昇順)
                else if($postArray['sort'] == '12'){
                    $sql .= " date(:base_date) - TO_DATE(SUBSTR(a.last_sale_date, 1, 8), 'YYYYMMDD') ";
                }
                // 最終販売日(降順)
                else if($postArray['sort'] == '13'){
                    $sql .= " TO_CHAR(TO_DATE(SUBSTR(a.last_sale_date, 1, 8), 'YYYYMMDD'), 'YYYY/MM/DD') DESC ";
                }
                // 最終販売日(昇順)
                else if($postArray['sort'] == '14'){
                    $sql .= " TO_CHAR(TO_DATE(SUBSTR(a.last_sale_date, 1, 8), 'YYYYMMDD'), 'YYYY/MM/DD') ";
                }
                $sql .= ', a.organization_id, a.prod_cd ';
            }
            else{
                $sql .= '  a.organization_id, a.prod_cd ';
            }
            
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
