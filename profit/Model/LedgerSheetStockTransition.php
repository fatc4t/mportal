<?php
    /**
     * @file      帳票 - 商品別在庫数詳細
     * @author    川橋
     * @date      2019/03/08
     * @version   1.00
     * @note      帳票 - 商品別在庫数詳細の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetStockTransition extends BaseModel
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
            $Log->trace("START getFormListData");

            $searchArray = array();
            //$sql = $this->creatSQL( $postArray, $searchArray );

            $sql_where  = "";
/*
            // 画面で入力した条件
            if ($postArray['organization_id'] !== ''){
                $sql_where .= " and   mst0201.organization_id = ".$postArray['organization_id'];
            }
            if ($postArray['prod_k'] !== ''){
                $sql_where .= " and   mst0201.prod_cd >= '".$postArray['prod_k']."'";
            }
            if ($postArray['prod_s'] !== ''){
                $sql_where .= " and   mst0201.prod_cd <= '".$postArray['prod_s']."'";
            }
            if ($postArray['sect_k'] !== ''){
                $sql_where .= " and   mst0201.sect_cd >= '".$postArray['sect_k']."'";
            }
            if ($postArray['sect_s'] !== ''){
                $sql_where .= " and   mst0201.sect_cd <= '".$postArray['sect_s']."'";
            }
*/
            if( $postArray['organization_id'] !== 'compile' && $postArray['organization_id'] !== 'all')
            {
                $sql_where .= " AND mst0201.organization_id in (".$postArray['organization_id'].") ";
            }
            if( $postArray['prod_cd'] !== 'false' )
            {
                $sql_where .= " AND mst0201.prod_cd in (".$postArray['prod_cd'].") ";
            }
            if( $postArray['sect_cd'] !== 'false' )
            {
                $sql_where .= " AND mst0201.sect_cd in (".$postArray['sect_cd'].") ";
            }

            // 仕入数
            $sql  = "";
            $sql .= "select";
            $sql .= "    jsk5110.organization_id                as organization_id";
            $sql .= "   ,v.abbreviated_name                     as abbreviated_name";
            $sql .= "   ,v.disp_order                           as disp_order";
            $sql .= "   ,jsk5110.prod_cd                        as prod_cd";
            $sql .= "   ,replace(mst0201.prod_nm, $$ $$, $$ $$) as prod_nm";
            $sql .= "   ,'1'                                    as kbn";
            $sql .="    ,substr(jsk5110.proc_date, 1, 6)        as proc_date";
            $sql .= "   ,sum(jsk5110.prod_supp_amount)          as amount";
            $sql .= "   from jsk5110";
            $sql .= "   inner join mst0201";
            $sql .= "   on (mst0201.organization_id = jsk5110.organization_id and mst0201.prod_cd = jsk5110.prod_cd)";
            $sql .= "   left join v_organization v on jsk5110.organization_id = v.organization_id";
            // 期間は必須とする
            $sql .= "   where jsk5110.proc_date >= '".str_replace('/', '', $postArray['start_date'])."'";
            $sql .= "   and   jsk5110.proc_date <= '".str_replace('/', '', $postArray['end_date'])."'";
            $sql .= $sql_where;
            $sql .= "   group by jsk5110.organization_id, v.abbreviated_name, v.disp_order, jsk5110.prod_cd, mst0201.prod_nm, substr(jsk5110.proc_date, 1, 6)";
            // 販売数
            $sql .= " union ";
            $sql .= "select";
            $sql .= "    jsk5110.organization_id                as organization_id";
            $sql .= "   ,v.abbreviated_name                     as abbreviated_name";
            $sql .= "   ,v.disp_order                           as disp_order";
            $sql .= "   ,jsk5110.prod_cd                        as prod_cd";
            $sql .= "   ,replace(mst0201.prod_nm, $$ $$, $$ $$) as prod_nm";
            $sql .= "   ,'2'                                    as kbn";
            $sql .="    ,substr(jsk5110.proc_date, 1, 6)        as proc_date";
            $sql .= "   ,sum(jsk5110.prod_sale_amount)          as amount";
            $sql .= "   from jsk5110";
            $sql .= "   inner join mst0201";
            $sql .= "   on (mst0201.organization_id = jsk5110.organization_id and mst0201.prod_cd = jsk5110.prod_cd)";
            $sql .= "   left join v_organization v on jsk5110.organization_id = v.organization_id";
            // 期間は必須とする
            $sql .= "   where jsk5110.proc_date >= '".str_replace('/', '', $postArray['start_date'])."'";
            $sql .= "   and   jsk5110.proc_date <= '".str_replace('/', '', $postArray['end_date'])."'";
            $sql .= $sql_where;
            $sql .= "   group by jsk5110.organization_id, v.abbreviated_name, v.disp_order, jsk5110.prod_cd, mst0201.prod_nm, substr(jsk5110.proc_date, 1, 6)";
            // 出庫数
            $sql .= " union ";
            $sql .= "select";
            $sql .= "    trn1401.organization_id                as organization_id";
            $sql .= "   ,v.abbreviated_name                     as abbreviated_name";
            $sql .= "   ,v.disp_order                           as disp_order";
            $sql .= "   ,trn1402.prod_cd                        as prod_cd";
            $sql .= "   ,replace(mst0201.prod_nm, $$ $$, $$ $$) as prod_nm";
            $sql .= "   ,'3'                                    as kbn";
            $sql .="    ,substr(trn1401.proc_date, 1, 6)        as proc_date";
            $sql .= "   ,sum(trn1402.amount)                    as amount";
            $sql .= "   from trn1401";
            $sql .= "   inner join trn1402";
            $sql .= "   on (trn1402.organization_id = trn1401.organization_id and trn1402.hideseq = trn1401.hideseq)";
            $sql .= "   inner join mst0201";
            $sql .= "   on (mst0201.organization_id = trn1402.organization_id and mst0201.prod_cd = trn1402.prod_cd)";
            $sql .= "   left join v_organization v on mst0201.organization_id = v.organization_id";
            // 期間は必須とする
            $sql .= "   where trn1401.proc_date >= '".str_replace('/', '', $postArray['start_date'])."'";
            $sql .= "   and   trn1401.proc_date <= '".str_replace('/', '', $postArray['end_date'])."'";
            $sql .= "   and   trn1401.proc_kbn in ('61', '62', '63', '64', '65')";
            $sql .= $sql_where;
            $sql .= "   group by trn1401.organization_id, v.abbreviated_name, v.disp_order, trn1402.prod_cd, mst0201.prod_nm, substr(trn1401.proc_date, 1, 6)";
            // 入庫数
            $sql .= " union ";
            $sql .= "select";
            $sql .= "    trn1401.organization_id                as organization_id";
            $sql .= "   ,v.abbreviated_name                     as abbreviated_name";
            $sql .= "   ,v.disp_order                           as disp_order";
            $sql .= "   ,trn1402.prod_cd                        as prod_cd";
            $sql .= "   ,replace(mst0201.prod_nm, $$ $$, $$ $$) as prod_nm";
            $sql .= "   ,'4'                                    as kbn";
            $sql .="    ,substr(trn1401.proc_date, 1, 6)        as proc_date";
            $sql .= "   ,sum(trn1402.amount)                    as amount";
            $sql .= "   from trn1401";
            $sql .= "   inner join trn1402";
            $sql .= "   on (trn1402.organization_id = trn1401.organization_id and trn1402.hideseq = trn1401.hideseq)";
            $sql .= "   inner join mst0201";
            $sql .= "   on (mst0201.organization_id = trn1402.organization_id and mst0201.prod_cd = trn1402.prod_cd)";
            $sql .= "   left join v_organization v on mst0201.organization_id = v.organization_id";
            // 期間は必須とする
            $sql .= "   where trn1401.proc_date >= '".str_replace('/', '', $postArray['start_date'])."'";
            $sql .= "   and   trn1401.proc_date <= '".str_replace('/', '', $postArray['end_date'])."'";
            $sql .= "   and   trn1401.proc_kbn in ('11', '12', '13', '14', '16')";
            $sql .= $sql_where;
            $sql .= "   group by trn1401.organization_id, v.abbreviated_name, v.disp_order, trn1402.prod_cd, mst0201.prod_nm, substr(trn1401.proc_date, 1, 6)";
            // 廃棄数
            $sql .= " union ";
            $sql .= "select";
            $sql .= "    trn1401.organization_id                as organization_id";
            $sql .= "   ,v.abbreviated_name                     as abbreviated_name";
            $sql .= "   ,v.disp_order                           as disp_order";
            $sql .= "   ,trn1402.prod_cd                        as prod_cd";
            $sql .= "   ,replace(mst0201.prod_nm, $$ $$, $$ $$) as prod_nm";
            $sql .= "   ,'5'                                    as kbn";
            $sql .="    ,substr(trn1401.proc_date, 1, 6)        as proc_date";
            $sql .= "   ,sum(trn1402.amount)                    as amount";
            $sql .= "   from trn1401";
            $sql .= "   inner join trn1402";
            $sql .= "   on (trn1402.organization_id = trn1401.organization_id and trn1402.hideseq = trn1401.hideseq)";
            $sql .= "   inner join mst0201";
            $sql .= "   on (mst0201.organization_id = trn1402.organization_id and mst0201.prod_cd = trn1402.prod_cd)";
            $sql .= "   left join v_organization v on mst0201.organization_id = v.organization_id";
            // 期間は必須とする
            $sql .= "   where trn1401.proc_date >= '".str_replace('/', '', $postArray['start_date'])."'";
            $sql .= "   and   trn1401.proc_date <= '".str_replace('/', '', $postArray['end_date'])."'";
            $sql .= "   and   trn1401.proc_kbn in ('66', '67', '68')";
            $sql .= $sql_where;
            $sql .= "   group by trn1401.organization_id, v.abbreviated_name, v.disp_order, trn1402.prod_cd, mst0201.prod_nm, substr(trn1401.proc_date, 1, 6)";

            // 在庫数
            // stkfin_dateの昇順でselect
            // 同一年月で複数行存在してもControllers側で最終行(最新)の在庫数が採用される
            $sql .= " union ";
            $sql .= "select";
            $sql .= "    trn1701.organization_id                as organization_id";
            $sql .= "   ,v.abbreviated_name                     as abbreviated_name";
            $sql .= "   ,v.disp_order                           as disp_order";
            $sql .= "   ,trn1702.prod_cd                        as prod_cd";
            $sql .= "   ,replace(mst0201.prod_nm, $$ $$, $$ $$) as prod_nm";
            $sql .= "   ,'6'                                    as kbn";
            //$sql .="    ,substr(trn1701.stkfin_date, 1, 6)      as proc_date";
            //$sql .= "   ,sum(trn1702.fin_amount)                as amount";
            $sql .="    ,trn1701.stkfin_date                    as proc_date";
            $sql .= "   ,trn1702.fin_amount                     as amount";
            $sql .= "   from trn1701";
            $sql .= "   inner join trn1702";
            $sql .= "   on (trn1702.organization_id = trn1701.organization_id and trn1702.hideseq = trn1701.hideseq)";
            $sql .= "   inner join mst0201";
            $sql .= "   on (mst0201.organization_id = trn1702.organization_id and mst0201.prod_cd = trn1702.prod_cd)";
            $sql .= "   left join v_organization v on mst0201.organization_id = v.organization_id";
            // 期間は必須とする
            $sql .= "   where trn1701.stkfin_date >= '".str_replace('/', '', $postArray['start_date'])."'";
            $sql .= "   and   trn1701.stkfin_date <= '".str_replace('/', '', $postArray['end_date'])."'";
            $sql .= $sql_where;
            //$sql .= "   group by trn1701.organization_id, v.abbreviated_name, v.disp_order, trn1702.prod_cd, mst0201.prod_nm, substr(trn1701.stkfin_date, 1, 6)";
            $sql .= "   order by disp_order, organization_id, prod_cd, kbn, proc_date";

            $searchArray = array();

            $displayItemDataList = array();

            $result = $DBA->executeSQL($sql, $searchArray);
            if( $result === false )
            {
                $Log->trace("END getFormListData");
                return $displayItemDataList;
            }
            $item_data = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }

            $Log->trace("END getFormListData");
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
            
            if( !empty( $postArray['organization_id'] ) )
            {
                $where .= ' AND o.organization_id = :organization_id ';
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
            if( !empty( $postArray['sect_k'] ) )
            {
                $where .= ' AND a.sect_cd >= :sect_k ';
                $ledgerSheetFormIdArray = array(':sect_k' => $postArray['sect_k'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            if( !empty( $postArray['sect_s'] ) )
            {
                $where .= ' AND a.sect_cd <= :sect_s ';
                $ledgerSheetFormIdArray = array(':sect_s' => $postArray['sect_s'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            $sql  = ' SELECT 
                    o.department_code, 
                    REPLACE(o.organization_name, $$　$$, $$ $$) AS organization_name,
                    a.prod_cd AS prod_cd,
                    REPLACE(a.prod_nm, $$　$$, $$ $$) AS prod_nm,
                    b.sect_cd AS sect_cd,
                    c.supp_cd AS supp_cd,
                    d.staff_cd AS staff_cd,
                    REPLACE(d.staff_nm, $$　$$, $$ $$) AS staff_nm,
                    t2.proc_date AS proc_date,
                    SUM(t.amount) AS amount,
                    SUM(t.pure_total) AS pure_total,
                    SUM(t.subtotal - t.day_costprice) AS subtotal,
                    SUM(t.pure_total_i) AS pure_total_i
                    FROM  trn0102 t
                    LEFT JOIN trn0101 t2 ON (t.hideseq = t2.hideseq AND t.organization_id = t2.organization_id) 
                    LEFT JOIN mst0201 a ON (t.prod_cd = a.prod_cd AND t.organization_id = a.organization_id) 
                    LEFT JOIN mst1201 b ON (a.sect_cd = b.sect_cd AND a.organization_id = b.organization_id) 
                    LEFT JOIN mst1101 c ON (a.head_supp_cd = c.supp_cd AND a.organization_id = c.organization_id) 
                    LEFT JOIN mst0601 d ON (t2.staff_cd = d.staff_cd AND t2.organization_id = d.organization_id) 
                    LEFT JOIN v_organization o ON (t.organization_id = o.organization_id) 
                    WHERE t.sect_sale_kbn = $$0$$ ';
            $sql .= $where;
            $sql .= " 	GROUP BY o.department_code, a.prod_cd, a.prod_nm, b.sect_cd, c.supp_cd, d.staff_cd, t2.proc_date, o.organization_name,
                        d.staff_nm ";
            $sql .= " 	ORDER BY o.department_code, d.staff_cd, t2.proc_date ";
            
            $Log->trace("END creatSQL");
            return $sql;
        }
    }
?>
