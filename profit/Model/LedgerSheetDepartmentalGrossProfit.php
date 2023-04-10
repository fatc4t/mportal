<?php
    /**
     * @file      部門実績 [M]
     * @author    millionet oota
     * @date      2018/08/30
     * @version   1.00
     * @note      帳票 - 部門別粗利速報の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetDepartmentalGrossProfit extends BaseModel
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

            $where = "  WHERE a.sect_cd ~ '^[0-9]*$' ";
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
                $where .= " AND a.sect_cd in (".$postArray['sect_cd'].") ";
            }
            if( $postArray['organization_id'] !== 'false' )
            {
                $where .= " AND a.organization_id in (".$postArray['organization_id'].") ";
            }

            $abrNm = '';
            $groupBy = '';
            //if(isset($_POST['tenpo'])){
            //$abrNm = ' v.abbreviated_name AS abbreviated_name, ';
            //$groupBy = ' GROUP BY a.sect_cd, n.sect_nm, v.abbreviated_name ';}
            //else{
            //$abrNm = '';
            //$groupBy = ' GROUP BY a.sect_cd, n.sect_nm ';} 
            if (!isset($_POST['tenpo'])){
                $abrNm = ' v.abbreviated_name AS abbreviated_name, v.department_code AS department_code,';
                $groupBy = ' GROUP BY a.sect_cd, n.sect_nm, b.type_cd, b.type_nm, b.type_kn, v.abbreviated_name, v.department_code ';
            }
            else{
                $abrNm = "'' AS abbreviated_name, '' AS department_code,";
                $groupBy = ' GROUP BY a.sect_cd, n.sect_nm, b.type_cd, b.type_nm, b.type_kn ';
            }

            $sql  = ' SELECT DISTINCT ';
		    $sql .= $abrNm;   
            $sql .= "   a.sect_cd,
                        n.sect_nm,
                        coalesce( b.type_cd,'' ) as type_cd,
                        CASE WHEN b.type_nm = $$$$ OR b.type_nm IS NULL THEN coalesce( b.type_kn,'')
                             ELSE b.type_nm
                        END AS type_nm,
                        SUM(sale_total) AS sale_total,
                        SUM(pure_total) AS pure_total,
                        SUM(sale_amount) AS sale_amount,
                        SUM(sale_profit) AS sale_profit,
                        (case sum(sale_total) when 0 then 0
                            else sum(sale_profit) / sum(sale_total)* 100 
                        end)  as arariritsu,
                        SUM(sale_cust_cnt) AS sale_cust_cnt
                    FROM jsk1130 a
                    LEFT JOIN mst1201 n ON ( n.sect_cd = a.sect_cd and n.organization_id = a.organization_id )
                    LEFT JOIN mst1205 b ON ( n.type_cd = b.type_cd and a.organization_id = b.organization_id ) 
                    LEFT JOIN v_organization v ON v.organization_id = a.organization_id  ";
            $sql .= $where;
            $sql .= $groupBy;
            //$sql .= '   ORDER BY sect_cd ';

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
                // 分類コード(降順)
                else if($postArray['sort'] == '3'){
                    $sql .= ' type_cd DESC ';
                }
                // 分類コード(昇順)
                else if($postArray['sort'] == '4'){
                    $sql .= ' type_cd ';
                }
                // 分類(降順)
                else if($postArray['sort'] == '5'){
                    $sql .= ' type_nm DESC ';
                }
                // 分類(昇順)
                else if($postArray['sort'] == '6'){
                    $sql .= ' type_nm ';
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
                // 売上数量(降順)
                else if($postArray['sort'] == '11'){
                    $sql .= ' sale_amount DESC ';
                }
                // 売上数量(昇順)
                else if($postArray['sort'] == '12'){
                    $sql .= ' sale_amount ';
                }
                // 売上金額(降順)
                else if($postArray['sort'] == '13'){
                    $sql .= ' sale_total DESC ';
                }
                // 売上金額(昇順)
                else if($postArray['sort'] == '14'){
                    $sql .= ' sale_total ';
                }
                // 粗利金額(降順)
                else if($postArray['sort'] == '15'){
                    $sql .= ' pure_total DESC ';
                }
                // 粗利金額(昇順)
                else if($postArray['sort'] == '16'){
                    $sql .= ' pure_total ';
                }
                // 粗利率(降順)
                else if($postArray['sort'] == '17'){
                    $sql .= ' arariritsu DESC ';
                }
                // 粗利率(昇順)
                else if($postArray['sort'] == '18'){
                    $sql .= ' arariritsu ';
                }
                $sql .= ', sect_cd ';
            }
            else{
                $sql .= '  sect_cd ';
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
