<?php
    /**
     * @file      帳票 - 仕入単品分析
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 仕入単品分析の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetPurchaseDay extends BaseModel
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
                $where .= ' where date(th.stock_date) >= date(:start_date) ';
                $ledgerSheetFormIdArray = array(':start_date' => $postArray['start_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            if( !empty( $postArray['end_date'] ) )
            {
                $where .= ' AND date(th.stock_date) <= date(:end_date) ';
                $ledgerSheetFormIdArray = array(':end_date' => $postArray['end_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //modal
            if( $postArray['supp_cd'] !== 'false' )
            {
                $where .= " AND th.supp_cd in (".$postArray['supp_cd'].") ";
            }
            if( $postArray['organization_id'] !== 'false' )
            {
                $where .= " AND t.organization_id in (".$postArray['organization_id'].") ";
            }
            if( $postArray['prod_cd'] !== 'false' )
            {
                $where .= " AND t.prod_cd in (".$postArray['prod_cd'].") ";
            } 
            //sort
            $order = ' order by ';
            if($_POST["sort_table"]){
                if (strpos($_POST["sort_table"], 'stock_date') !== false) {
                   $order .= $_POST['sort_table'].",org_id,supp_cd,sect_cd,prod_cd "; 
                }else if(strpos($_POST["sort_table"], 'abbreviated_name') !== false){
                   $order .= 'stock_date, '.$_POST["sort_table"].',supp_cd,sect_cd,prod_cd '; 
                }else if(strpos($_POST["sort_table"], 'supp_cd') !== false || strpos($_POST["sort_table"], 'supp_nm') !== false){
                   $order .= 'stock_date,org_id, '.$_POST["sort_table"].',sect_cd,prod_cd '; 
                }else if(strpos($_POST["sort_table"], 'sect_cd') !== false || strpos($_POST["sort_table"], 'sect_nm') !== false){
                   $order .= 'stock_date,org_id,supp_cd,'.$_POST["sort_table"].',prod_cd '; 
                }else if(strpos($_POST["sort_table"], 'prod_tax') !== false){
                   $order .= 'stock_date,org_id, '.$_POST["sort_table"].',prod_tax '; 
                }else{
                   $order .= 'stock_date,org_id,supp_cd,sect_cd, '.$_POST["sort_table"].',prod_cd '; 
                }
            }else{
                $order .= 'stock_date, organization_id,supp_cd,sect_cd,prod_cd ';
            }            

            $sql  = "";
            $sql .= " select ";
            $sql .= " 	to_char(date(th.stock_date), 'YYYY/MM/DD') as stock_date , ";
            if($_POST['tenpo']){
                $sql .= " 	0 as org_id, ";
                $sql .= " 	'企業計' as org_nm, ";
            }else{
                $sql .= " 	t.organization_id as org_id, ";
                $sql .= " 	o.abbreviated_name as org_nm, ";                
            }
            $sql .= " 	m4.supp_cd , ";
            $sql .= " 	m4.supp_nm , ";
            $sql .= " 	m2.sect_cd , ";
            $sql .= " 	m2.sect_nm , ";
            $sql .= " 	m1.prod_cd , ";
            $sql .= " 	m1.prod_nm , ";
            $sql .= " 	m1.prod_tax , ";
            $sql .= " 	sum(t.amount) as amount , ";
            $sql .= " 	sum(t.costprice) as costprice , ";
            $sql .= " 	sum(t.costtotal) as costtotal , ";
            $sql .= " 	sum(t.saleprice) as saleprice , ";
            $sql .= " 	sum(t.saleprice * t.amount) as saletotal , ";
            $sql .= " 	sum(t.saleprice * t.amount - t.costtotal) as gross_profit , ";
            $sql .= " 	case ";
            $sql .= "       when sum(t.saleprice * t.amount) = 0 then '0.00' ";
            $sql .= "       else round( sum( t.saleprice * t.amount - t.costtotal ) / sum( t.saleprice * t.amount ) * 100, 2) ";
            $sql .= " 	end as gross_profit_margin ";
            $sql .= " from ";
            $sql .= " 	trn1102 as t ";
            $sql .= " left join v_organization as o on (t.organization_id = o.organization_id) ";
            $sql .= " left join trn1101 as th on (t.hideseq = th.hideseq and t.organization_id = th.organization_id) ";
            $sql .= " left join mst0201 as m1 on (t.prod_cd = m1.prod_cd	and t.organization_id = m1.organization_id) ";
            $sql .= " left join mst1201 as m2 on (m1.sect_cd = m2.sect_cd	and t.organization_id = m2.organization_id) ";
            $sql .= " left join mst1101 as m4 on(th.supp_cd = m4.supp_cd	and t.organization_id = m4.organization_id) ";        
            $sql .=  $where;
            $sql .= " group by ";
            $sql .= " 	stock_date , ";
            $sql .= " 	org_id, ";
            $sql .= " 	org_nm, ";
            $sql .= " 	m4.supp_cd , ";
            $sql .= " 	m4.supp_nm , ";
            $sql .= " 	m2.sect_cd , ";
            $sql .= " 	m2.sect_nm , ";
            $sql .= " 	m1.prod_cd , ";
            $sql .= " 	m1.prod_nm ,  ";             
            $sql .= " 	m1.prod_tax   ";             
            $sql .=  $order;
            //print_r($sql);
            $Log->trace('END creatSQL');
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
