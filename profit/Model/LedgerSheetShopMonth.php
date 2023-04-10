<?php
    /**
     * @file      帳票 - 店舗別売上月報
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 店舗別売上月報の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetShopMonth extends BaseModel
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
                $order .= $_POST['sort_table']; 
            }else{
                $order .= 'organization_id';
            }            

 /*           $sql = "  SELECT 
                a.organization_id AS organization_id,
                count(distinct proc_date) || '日' AS days,
                o.abbreviated_name AS abbreviated_name,
                SUM(total_utax+total_itax) AS tax_total,
                ROUND(SUM(a.pure_total_i) / NULLIF((SELECT SUM(pure_total_i)
                FROM jsk1010 ". $where ."),0) * 100 ,2) || $$%$$ AS composition_ratio,
                AVG(a.total_amount) AS avg_amount,
                SUM(a.pure_total_i) AS pure_total_i, 
                SUM(a.total_amount) AS total_amount,
                SUM(a.total_cnt) AS total_cnt,
                SUM(a.perc_total + a.disc_total + a.adjust_total 
                + perc_item_total + disc_item_total + disc_item_total_e + bundle_total + mixmatch_total) AS hiki_total
                FROM jsk1010 a
                LEFT JOIN v_organization o ON a.organization_id = o.organization_id ";
                $sql .= $where;
                $sql .= ' AND pure_total_i IS NOT NULL
                GROUP BY a.organization_id, o.abbreviated_name ';
                $sql .= $order;
*/
                $sql .= "select "; //select formated data
                $sql .= "	organization_id,";
                $sql .= "	days,";
                $sql .= "	abbreviated_name,";
//stadd montagne 20200131
                //$sql .= "	pure_total_i,";
                $sql .= "	pure_total,";
				$sql .= "	pure_total - total_itax1 as pure_total_i,";
//endadd montagne 20200131			

                $sql .= "	total_amount,";
                $sql .= "	total_cnt,";
                $sql .= "	hiki_total,";
                $sql .= "	composition_ratio as composition_ratio,";
                $sql .= "	avg_amount,";
                $sql .= "	tax_total_08,";
                $sql .= "	tax_total_jsk - tax_total_08 as tax_total_10 ";
                $sql .= "from (select"; // case when 2 or more 営業日　in one day
                $sql .= "		a.organization_id as organization_id,";
                $sql .= "		count(distinct a.proc_date) || '日' AS days,";
                $sql .= "		o.abbreviated_name as abbreviated_name,";
                $sql .= "		round(sum(a.pure_total_i) / nullif((select sum(pure_total_i) from jsk1010 ".str_replace("a.","",$where)." ), 0) * 100 , 2) || $$ % $$ as composition_ratio,";
                $sql .= "		avg(a.total_amount) as avg_amount,";
//stadd montagne 20200131			
                $sql .= "		sum(a.pure_total) as pure_total,";	
				$sql .= " 		sum(a.total_utax) as total_utax1, ";
				$sql .= " 		sum(a.total_itax) as total_itax1, ";
//endadd montagne 20200131			
                $sql .= "		sum(a.pure_total_i) as pure_total_i,";
                $sql .= "		sum(t2_pure_total_i) as t2_pure_total_i_trn,";
                $sql .= "		sum(a.pure_total_i) - sum(t2_pure_total_i) as diff_pure_total,";
                $sql .= "		sum(a.total_amount) as total_amount,";
                $sql .= "		sum(a.total_cnt) as total_cnt,";
                $sql .= "		sum(a.perc_total + a.disc_total + a.adjust_total + perc_item_total + disc_item_total + disc_item_total_e + bundle_total + mixmatch_total) as hiki_total";
                $sql .= "		,sum(total_utax + total_itax) as tax_total_jsk";
                $sql .= "	    ,sum(coalesce(t.tax_08,0)) as tax_08_trn";
                $sql .= "	    ,sum(coalesce(t.tax_10,0)) as tax_10_trn ";
                $sql .= "	    ,sum(total_utax + total_itax) - sum(coalesce(t.tax_08,0))-sum(coalesce(t.tax_10,0)) as tax_diff";
                $sql .= "	    ,case ";
                $sql .= "	    	when sum(coalesce(t.tax_08,0))+sum(coalesce(t.tax_10,0)) = 0 then 	sum(total_utax + total_itax)";
                $sql .= "	    	else cast(sum(total_utax + total_itax)*sum(coalesce(t.tax_08,0))/(sum(coalesce(t.tax_08,0))+sum(coalesce(t.tax_10,0))) as int)";
                $sql .= "		end as tax_total_08";
                $sql .= "	from";
                $sql .= "		(select ";
                $sql .= "			organization_id,";
                $sql .= "			reji_no,";
                $sql .= "			proc_date,";
//stadd montagne 20200131				
                $sql .= "			sum(pure_total) as pure_total,";
				$sql .= " 			sum(total_utax) as total_utax1, ";
				$sql .= " 			sum(total_itax) as total_itax1, ";				
//endadd montagne 20200131				
                $sql .= "			sum(pure_total_i) as pure_total_i,";
                $sql .= "			sum(total_amount) as total_amount,		";
                $sql .= "			sum(total_cnt) as total_cnt,";
                $sql .= "			sum(perc_total) as perc_total,";
                $sql .= "			sum(disc_total) as disc_total,";
                $sql .= "			sum(adjust_total) as adjust_total,";
                $sql .= "			sum(perc_item_total) as perc_item_total,";
                $sql .= "			sum(disc_item_total) as disc_item_total,";
                $sql .= "			sum(disc_item_total_e) as disc_item_total_e,";
                $sql .= "			sum(bundle_total) as bundle_total,";
                $sql .= "			sum(mixmatch_total) as mixmatch_total,";
                $sql .= "			sum(total_utax) as total_utax,";
                $sql .= "			sum(total_itax) as total_itax";
                $sql .= "		from jsk1010";
                $sql .= "		group by organization_id,reji_no,proc_date) a";
                $sql .= "	left join v_organization o on"; // name of shop
                $sql .= "		a.organization_id = o.organization_id";
                $sql .= "	left join (";
                $sql .= "				select"; // percentage of tax
                $sql .= "				    t2.organization_id,t1.proc_date,t2.reji_no,";
                $sql .= "					count(distinct (t1.trndate)) as t_day,";
                $sql .= "				    sum(t2.pure_total) as t2_pure_total_i,";
                $sql .= "				    sum(";
                $sql .= "				    case when t2.tax_rate = 8 then t2.tax_total ";
                $sql .= "				         else 0";
                $sql .= "				    end) as tax_08,";
                $sql .= "				    sum(";
                $sql .= "				    case when t2.tax_rate = 10 then t2.tax_total";
                $sql .= "				         else 0";
                $sql .= "				    end) as tax_10";
                $sql .= "				from trn0103 t2";
                $sql .= "				left join trn0101 t1 on t1.hideseq = t2.hideseq and t1.organization_id = t2.organization_id";
                $sql .= "				where";
                $sql .= "						t1.stop_kbn = '0'";
                $sql .= "				group by t2.organization_id,t1.proc_date,t2.reji_no";
                $sql .= "				order by organization_id,proc_date,reji_no";
                $sql .= "		) t on  ";
                $sql .= "			t.proc_date = a.proc_date ";
                $sql .= "		and a.reji_no = t.reji_no ";
                $sql .= "		and t.organization_id = a.organization_id	";
                $sql .= 	$where; // condition
                $sql .= "	group by";
                $sql .= "		a.organization_id,";
                $sql .= "		o.abbreviated_name";
                $sql .= "	order by ";
                $sql .= "		organization_id";
                $sql .= ") full_data";
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
