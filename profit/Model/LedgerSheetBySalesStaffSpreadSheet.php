<?php
    /**
     * @file      帳票 - 販売員別売上集計表
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 販売員別売上集計表の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetBySalesStaffSpreadSheet extends BaseModel
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

            $where = " WHERE  (t.cancel_kbn != '1' AND t2.stop_kbn != '1'AND t2.proc_date is not null AND t2.proc_date != '') ";
          
            if( !empty( $postArray['start_date'] ) )
            {
                $where .= ' AND date(t2.proc_date) >= date(:start_date) ';
                $ledgerSheetFormIdArray = array(':start_date' => $postArray['start_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            if( !empty( $postArray['end_date'] ) )
            {
                $where .= ' AND date(t2.proc_date) <= date(:end_date) ';
                $ledgerSheetFormIdArray = array(':end_date' => $postArray['end_date'],);
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

// modal
            if(  $postArray['organization_id'] !== 'false' )
            {
                $where .= ' AND t.organization_id in ( '.$postArray['organization_id'].' )';
            }
            
            if(  $postArray['sect_cd'] !== 'false' )
            {
                $where .= ' AND t.sect_cd in ( '.$postArray['sect_cd'].' )';
            }
            
            if( $postArray['staff_cd'] !== 'false' )
            {
                $where .= ' AND t2.staff_cd in ( '.$postArray['staff_cd'].' )';
            }
            
            $sql  = ' SELECT 
                    t.organization_id,
                    v.abbreviated_name AS organization_name,
                    v.department_code AS department_code,
                    t.prod_cd AS prod_cd,
                    REPLACE(a.prod_nm, $$　$$, $$ $$) AS prod_nm,
                    t.sect_cd AS sect_cd,
                    b.sect_nm AS sect_nm,
                    c.supp_cd AS supp_cd,
                    c.supp_nm AS supp_nm,
                    CASE WHEN d.staff_cd != $$$$ OR d.staff_cd IS NOT NULL
                    THEN (SELECT d.staff_cd)
                    END AS staff_cd,
                    REPLACE(d.staff_nm, $$　$$, $$ $$) AS staff_nm,
                    SUM(t.amount) AS amount,
                    SUM(t.pure_total) AS pure_total,
                    SUM(t.profit_i) AS profit_i,
                    SUM(t.pure_total_i) AS pure_total_i

                    FROM  trn0102 t
                    LEFT JOIN trn0101 t2 ON (t.hideseq = t2.hideseq AND t.organization_id = t2.organization_id) 
                    LEFT JOIN mst0201 a ON (t.prod_cd = a.prod_cd AND t.organization_id = a.organization_id) 
                    LEFT JOIN mst1201 b ON (t.sect_cd = b.sect_cd AND t.organization_id = b.organization_id) 
                    LEFT JOIN mst1101 c ON (a.head_supp_cd = c.supp_cd AND a.organization_id = c.organization_id) 
                    LEFT JOIN mst0601 d ON (t2.staff_cd = d.staff_cd AND t2.organization_id = d.organization_id) 
                    LEFT JOIN v_organization v ON (t.organization_id = v.organization_id) ';
            $sql .= $where;
            $sql .= " 	GROUP BY  t.prod_cd, a.prod_nm, t.sect_cd, c.supp_cd, d.staff_cd, v.abbreviated_name, v.department_code,
                        d.staff_nm, b.sect_nm,t.organization_id ";
            $sql .= "  ,c.supp_nm ";
            //$sql .= " 	ORDER BY d.staff_cd ";
            
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
                // 商品コード(降順)
                else if($postArray['sort'] == '3'){
                    $sql .= ' prod_cd DESC ';
                }
                // 商品コード(昇順)
                else if($postArray['sort'] == '4'){
                    $sql .= ' prod_cd ';
                }
                // 商品(降順)
                else if($postArray['sort'] == '5'){
                    $sql .= ' prod_nm DESC ';
                }
                // 商品(昇順)
                else if($postArray['sort'] == '6'){
                    $sql .= ' prod_nm ';
                }
                // 部門コード(降順)
                else if($postArray['sort'] == '7'){
                    $sql .= ' sect_cd DESC ';
                }
                // 部門コード(昇順)
                else if($postArray['sort'] == '8'){
                    $sql .= ' sect_cd ';
                }
                // 部門(降順)
                else if($postArray['sort'] == '9'){
                    $sql .= ' sect_nm DESC ';
                }
                // 部門(昇順)
                else if($postArray['sort'] == '10'){
                    $sql .= ' sect_nm ';
                }
                // 仕入先コード(降順)
                else if($postArray['sort'] == '11'){
                    $sql .= ' supp_cd DESC ';
                }
                // 仕入先コード(昇順)
                else if($postArray['sort'] == '12'){
                    $sql .= ' supp_cd ';
                }
                // 仕入先(降順)
                else if($postArray['sort'] == '13'){
                    $sql .= ' supp_nm DESC ';
                }
                // 仕入先(昇順)
                else if($postArray['sort'] == '14'){
                    $sql .= ' supp_nm ';
                }
                // 担当者コード(降順)
                else if($postArray['sort'] == '15'){
                    $sql .= ' staff_cd DESC ';
                }
                // 担当者コード(昇順)
                else if($postArray['sort'] == '16'){
                    $sql .= ' staff_cd ';
                }
                // 担当者名(降順)
                else if($postArray['sort'] == '17'){
                    $sql .= ' staff_nm DESC ';
                }
                // 担当者名(昇順)
                else if($postArray['sort'] == '18'){
                    $sql .= ' staff_nm ';
                }
                // 数量(降順)
                else if($postArray['sort'] == '19'){
                    $sql .= ' amount DESC ';
                }
                // 数量(昇順)
                else if($postArray['sort'] == '20'){
                    $sql .= ' amount ';
                }
                // 売上金額(降順)
                else if($postArray['sort'] == '21'){
                    $sql .= ' pure_total DESC ';
                }
                // 売上金額(昇順)
                else if($postArray['sort'] == '22'){
                    $sql .= ' pure_total ';
                }
                // 粗利額(降順)
                else if($postArray['sort'] == '23'){
                    $sql .= ' profit_i DESC ';
                }
                // 粗利額(昇順)
                else if($postArray['sort'] == '24'){
                    $sql .= ' profit_i ';
                }
                // 税抜売上金額(降順)
                else if($postArray['sort'] == '25'){
                    $sql .= ' pure_total_i DESC ';
                }
                // 税抜売上金額(昇順)
                else if($postArray['sort'] == '26'){
                    $sql .= ' pure_total_i ';
                }
                $sql .= ', staff_cd ';
            }
            else{
                $sql .= '  organization_id,staff_cd ';
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
