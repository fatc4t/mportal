<?php
    /**
     * @file      帳票 - 店舗日別売上動向表
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 店舗日別売上動向表の管理を行う
     */
//*----------------------------------------------------------------------------
//*   修正履歴
//*   修正日      :
//
//  @m1 2019/03/14  モンターニュ　精算純売上金額_内税抜きは精算純売上金額-精算内税額になりました。税込売上金額は精算純売上金額+精算外税額になりました。
//*****************************************************************************

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetShopDay extends BaseModel
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
                $where .= " WHERE a.proc_date >= REPLACE(:start_date,'/','') ";
                $ledgerSheetFormIdArray = array(':start_date' => $postArray['start_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            if( !empty( $postArray['end_date'] ) )
            {
                $where .= " AND a.proc_date <= REPLACE(:end_date,'/','') ";
                $ledgerSheetFormIdArray = array(':end_date' => $postArray['end_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            if( $postArray['organization_id'] !== 'false' )
            {
                $where .= " AND a.organization_id in (".$postArray['organization_id'].") ";
            }
            $order = ' order by ';
            if($_POST["sort_table"]){
                if (strpos($_POST["sort_table"], 'organization_id') !== false) {
                   $order .= $_POST['sort_table']." ,proc_date"; 
                }else{
                   $order .= 'organization_id, '.$_POST["sort_table"]; 
                }
            }else{
                $order .= 'organization_id,proc_date';
            }     
 /*           $sql  = " SELECT ";
            //$sql .=   "      to_char(date(proc_date), 'YYYY/MM/DD') || '(' || to_char(date(proc_date), 'TMDy') || ')' AS proc_date,";
            $sql .=   "     to_char(date(proc_date), 'YYYY/MM/DD') AS proc_date,";
            $sql .=   "     a.organization_id,
                            sum(pure_total + total_utax) as sale_total,
                            sum(pure_total - total_itax)::int as pure_total_i,
                            TRUNC(SUM(pure_total_i::numeric) / NULLIF((select sum(a.pure_total_i)::numeric from jsk1010 as a ". $where ." ),0) * 100,2)  || '%'as composition_ratio_pure_total_i,
                            SUM(total_amount) as total_amount,
                            trunc(SUM(total_amount::numeric) / NULLIF((select sum(a.total_amount)::numeric from jsk1010 as a". $where ." ),0) * 100,2)  || '%'as composition_ratio_total_amount,
                            trunc(SUM(pure_total_i) / NULLIF(sum(total_amount),0),2) as avarege_price,
                            SUM(total_cnt) as total_cnt,
                            trunc(sum(pure_total_i) / NULLIF(sum(total_cnt),0),2) as per_customer_price,
                            0 as progress_rate,
                            SUM(a.perc_total + a.disc_total + a.adjust_total 
                            + perc_item_total + disc_item_total + disc_item_total_e 
                            + bundle_total + mixmatch_total) AS hiki_total,
                            SUM(total_utax + total_itax) AS tax_total,
                            o.abbreviated_name AS abbreviated_name
                            FROM jsk1010 a
                            JOIN v_organization o ON a.organization_id = o.organization_id  ";         
            $sql .= $where;
            $sql .=   "       GROUP BY o.abbreviated_name,a.organization_id,proc_date ";
            $sql .= $order;
*/

            $sql .= " select ";//select formated data
            $sql .= " 	proc_date, ";
            $sql .= " 	organization_id, ";
            $sql .= " 	abbreviated_name, ";
            $sql .= " 	sale_total, ";
            $sql .= " 	pure_total_i, ";
            $sql .= " 	composition_ratio_pure_total_i, ";
            $sql .= " 	total_amount, ";
            $sql .= " 	composition_ratio_total_amount, ";
            $sql .= " 	avarege_price, ";
            $sql .= " 	total_cnt, ";
            $sql .= " 	per_customer_price, ";
            $sql .= " 	progress_rate, ";
            $sql .= " 	hiki_total, ";
            $sql .= " 	tax_total_08, ";
            $sql .= " 	tax_total - tax_total_08 as tax_total_10 ";
            $sql .= " from ( ";
            $sql .= " 	select ";
            $sql .= " 		to_char(date(a.proc_date), 'YYYY/MM/DD') as proc_date, ";
            $sql .= " 		a.organization_id, ";
            $sql .= " 		sum(pure_total + total_utax) as sale_total, ";
            $sql .= " 		sum(pure_total - total_itax)::int as pure_total_i, ";
            //$sql .= " 		sum(pure_total_i + total_itax +total_utax) as sale_total, ";
            //$sql .= " 		sum(pure_total_i)::int as pure_total_i, ";			
            $sql .= " 		trunc(sum(pure_total_i::numeric) / nullif((select sum(a.pure_total_i)::numeric from jsk1010 as a ".str_replace("a.","",$where)." ), 0) * 100, 2) || '%' as composition_ratio_pure_total_i, ";
            $sql .= " 		sum(total_amount) as total_amount, ";
            $sql .= " 		trunc(sum(total_amount::numeric) / nullif((select sum(a.total_amount)::numeric from jsk1010 as a ".str_replace("a.","",$where)." ), 0) * 100, 2) || '%' as composition_ratio_total_amount, ";
            $sql .= " 		trunc(sum(pure_total_i) / nullif(sum(total_amount), 0), 2) as avarege_price, ";
            $sql .= " 		sum(total_cnt) as total_cnt, ";
            $sql .= " 		trunc(sum(pure_total_i) / nullif(sum(total_cnt), 0), 2) as per_customer_price, ";
            $sql .= " 		0 as progress_rate, ";
            $sql .= " 		sum(a.perc_total + a.disc_total + a.adjust_total + perc_item_total + disc_item_total + disc_item_total_e + bundle_total + mixmatch_total) as hiki_total, ";
            $sql .= " 		sum(total_utax + total_itax) as tax_total, ";
            $sql .= " 		o.abbreviated_name as abbreviated_name, ";
            $sql .= " 		sum(coalesce(t.tax_08, 0)) as tax_08_trn , ";
            $sql .= " 		sum(coalesce(t.tax_10, 0)) as tax_10_trn, ";
            $sql .= " 		case ";
            $sql .= " 			when sum(coalesce(t.tax_08, 0))+ sum(coalesce(t.tax_10, 0)) = 0 then sum(total_utax + total_itax) ";
            $sql .= " 			else cast(sum(total_utax + total_itax)* sum(coalesce(t.tax_08, 0))/(sum(coalesce(t.tax_08, 0))+ sum(coalesce(t.tax_10, 0))) as int) ";
            $sql .= " 		end as tax_total_08		 ";
            $sql .= " 	from ";
            $sql .= " 		(select "; // case when 2 or more 営業日　in one day
            $sql .= " 			proc_date, ";
            $sql .= " 			reji_no, ";
            $sql .= " 			organization_id, ";
            $sql .= " 			sum(pure_total) as pure_total, ";
            $sql .= " 			sum(total_utax) as total_utax, ";
            $sql .= " 			sum(total_itax) as total_itax, ";
            $sql .= " 			sum(pure_total_i) as pure_total_i, ";
            $sql .= " 			sum(total_amount) as total_amount, ";
            $sql .= " 			sum(total_cnt) as total_cnt, ";
            $sql .= " 			sum(perc_total) as perc_total, ";
            $sql .= " 			sum(disc_total) as disc_total, ";
            $sql .= " 			sum(adjust_total) as adjust_total, ";
            $sql .= " 			sum(perc_item_total) as perc_item_total, ";
            $sql .= " 			sum(disc_item_total) as disc_item_total, ";
            $sql .= " 			sum(disc_item_total_e) as disc_item_total_e, ";
            $sql .= " 			sum(bundle_total) as bundle_total, ";
            $sql .= " 			sum(mixmatch_total) as mixmatch_total			 ";
            $sql .= " 		 from jsk1010 ";
            $sql .= " 		 group by organization_id,reji_no,proc_date) a ";
            $sql .= " 	join v_organization o on ";
            $sql .= " 		a.organization_id = o.organization_id ";
            $sql .= " 		left join ( ";
            $sql .= " 					select "; // percentage of tax
            $sql .= " 					    t2.organization_id,t1.proc_date,t2.reji_no, ";
            $sql .= " 						count(distinct (t1.trndate)) as t_day, ";
            $sql .= " 					    sum(t2.pure_total) as t2_pure_total_i, ";
            $sql .= " 					    sum( ";
            $sql .= " 					    case when t2.tax_rate = 8 then t2.tax_total  ";
            $sql .= " 					         else 0 ";
            $sql .= " 					    end) as tax_08, ";
            $sql .= " 					    sum( ";
            $sql .= " 					    case when t2.tax_rate = 10 then t2.tax_total ";
            $sql .= " 					         else 0 ";
            $sql .= " 					    end) as tax_10 ";
            $sql .= " 					from trn0103 t2 ";
            $sql .= " 					left join trn0101 t1 on t1.hideseq = t2.hideseq and t1.organization_id = t2.organization_id ";
            $sql .= " 					where ";
            $sql .= " 							t1.stop_kbn = '0' ";
            $sql .= " 					group by t2.organization_id,t1.proc_date,t2.reji_no ";
            $sql .= " 					order by organization_id,proc_date,reji_no ";
            $sql .= " 			) t on   ";
            $sql .= " 				t.proc_date = a.proc_date  ";
            $sql .= " 			and a.reji_no = t.reji_no  ";
            $sql .= " 			and t.organization_id = a.organization_id		 ";
            $sql .= 	$where;// condition
            $sql .= " 	group by ";
            $sql .= " 		o.abbreviated_name, ";
            $sql .= " 		a.organization_id, ";
            $sql .= " 		a.proc_date ";
            $sql .= " 	order by ";
            $sql .= " 		organization_id asc , ";
            $sql .= " 		proc_date ";
            $sql .= " ) full_data ";
            $sql .= $order; // sort (default organization_id)             
            $Log->trace("END creatSQL");
            //print_r($sql);
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
                $sql .= ' AND a.organization_id = :organization_id ';
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
                $sql .= ' AND a.organization_id = :organization_id ';
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
