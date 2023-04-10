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
    class LedgerSheetBySalesStaffManagement extends BaseModel
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
//print_r($postArray);    
//print_r($sql);
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
           
// modal
            if($postArray['org_id'] !== 'false'){
                if( $postArray['org_select'] === 'empty'){
                    $where .= " and t.organization_id in (".$postArray['org_id'].")";
                }else{
                    $where .= " and t.organization_id in (".$postArray['org_select'].")";
                }
            }            
            
            if(  $postArray['sect_cd'] !== 'false' ){
                if( $postArray['sect_select'] === 'empty'){
                    $where .= ' AND t.sect_cd in ( '.$postArray['sect_cd'].' )';
                }else{
                    $where .= " AND t.sect_cd in ( '".$postArray['sect_select']."' )";
                }
            }
            if(  $postArray['prod_cd'] !== 'false' ){
                if( $postArray['prod_select'] === 'empty'){
                    $where .= ' AND t.prod_cd in ( '.$postArray['prod_cd'].' )';
                }else{
                    $where .= " AND t.prod_cd in ( '".$postArray['prod_select']."' )";
                }
            }            
            if( $postArray['staff_cd'] !== 'false' ){
                if( $postArray['staff_select'] === 'empty'){
                    $where .= ' AND t2.staff_cd in ( '.$postArray['staff_cd'].' )';
                }else{
                    $where .= " AND t2.staff_cd in ( '".$postArray['staff_select']."' )";
                }
            }
            if($postArray['sort'] && $postArray['sort'] !== 'false'){
                $array_sort = explode('#',$postArray['sort']);
                $order =$array_sort[0];
                if($array_sort[1] < 0 ){
                    $order .= " desc ";
                }
            }else{
                $order = "staff_cd ";               
            } 
            if($order !== 'staff_cd'){
                $order .= ',staff_cd';
            }
            if(!$postArray['mode_chk']){
                $order  .= " ,prod_cd ";
            } 
            $order = 'organization_id,'.$order;
            
            $sql  = ' SELECT 
                        t.organization_id as organization_id,
                        v.abbreviated_name AS organization_name,
                        v.department_code AS department_code,';
            if(!$postArray['mode_chk']){
                $sql .= '   t.prod_cd AS prod_cd,
                            REPLACE(a.prod_nm, $$　$$, $$ $$) AS prod_nm,
                            t.sect_cd AS sect_cd,
                            b.sect_nm AS sect_nm,
                            c.supp_cd AS supp_cd,
                            c.supp_nm AS supp_nm,';
            }else{
                $sql .= "   '' AS prod_cd,
                            '' AS prod_nm,
                            '' AS sect_cd,
                            '' AS sect_nm,
                            '' AS supp_cd,
                            '' AS supp_nm,";                
            }
            $sql .= '   CASE WHEN d.staff_cd != $$$$ OR d.staff_cd IS NOT NULL
                        THEN (SELECT d.staff_cd)
                        END AS staff_cd,
                        REPLACE(d.staff_nm, $$　$$, $$ $$) AS staff_nm,
                        COUNT(distinct t2.cust_cd) AS cust_amount,
                        SUM(t.amount) AS amount,
                        SUM(t.pure_total) AS pure_total,
                        SUM(t.profit_i) AS profit_i,
                        SUM(t.pure_total_i) AS pure_total_i,
                        sum(t.day_costprice) as day_costprice,
                        case when sum(t.amount) = 0 then 0 else sum(t.pure_total)/sum(t.amount) end as sale_average,
                        case when sum(t.amount) = 0 then 0 else sum(t.profit_i)/sum(t.amount) end as profit_average,
                        case when sum(t.pure_total) = 0 then 0 else sum(t.profit_i)/sum(t.pure_total)*100 end as profit_margin
                        FROM  trn0102 t
                        LEFT JOIN trn0101 t2 ON (t.hideseq = t2.hideseq AND t.organization_id = t2.organization_id)';
            if(!$postArray['mode_chk']){
                $sql .= "   LEFT JOIN mst0201 a ON (t.prod_cd = a.prod_cd AND t.organization_id = a.organization_id) 
                        LEFT JOIN mst1201 b ON (t.sect_cd = b.sect_cd AND t.organization_id = b.organization_id) 
                        LEFT JOIN mst1101 c ON (a.head_supp_cd = c.supp_cd AND a.organization_id = c.organization_id)"; 
            }
            $sql .= "   LEFT JOIN mst0601 d ON (t2.staff_cd = d.staff_cd AND t2.organization_id = d.organization_id) 
                        LEFT JOIN v_organization v ON (t.organization_id = v.organization_id) ";
            $sql .= $where;
            $sql .= " 	GROUP BY  t.organization_id,d.staff_cd, v.abbreviated_name, v.department_code,d.staff_nm ";
            if(!$postArray['mode_chk']){
                $sql .= "  , t.prod_cd, a.prod_nm, t.sect_cd, c.supp_cd, b.sect_nm,c.supp_nm ";
            }
            
            //$sql .= " 	ORDER BY d.staff_cd ";
            
            // 並び順
            $sql .= '   ORDER BY  '.$order;


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
