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
    class LedgerSheetProductHistory extends BaseModel
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
 
//           Edit 2019/11/27 kanderu データを取り込む
             if( $_POST['org_id'] !== 'false')
            {
                if( $_POST['org_select'] === 'empty'){
                    $where .= " AND mst0201.organization_id in (".$_POST['org_id'].") ";
                }else{
                    $where .= " AND mst0201.organization_id in ('".$_POST['org_select']."') ";
                }
            }
           
            
            if( $_POST['prod_cd'] !== 'false' )
            {
                if( $_POST['prod_select'] === 'empty'){
                    $where .= " AND mst0201.prod_cd in (".$_POST['prod_cd'].") ";
                }else{
                    $where .= " AND mst0201.prod_cd in ('".$_POST['prod_select']."') ";
                }
            }
            if( $_POST['sect_cd'] !== 'false' )
            {
                if( $_POST['sect_select'] === 'empty'){
                    $where .= " AND mst0201.sect_cd in (".$_POST['sect_cd'].") ";
                }else{
                    $where .= " AND mst0201.sect_cd in ('".$_POST['sect_select']."') ";
                }
            }
//EDITSTR kanderu 2020/09/04            
//            if($_POST['sort'] && $_POST['sort'] !== 'false'){
//                $array_sort = explode('#',$_POST['sort']);
//                $order = ','.$array_sort[0];
//                if($array_sort[1] < 0 ){
//                    $order .= " desc ";
//                }
//            }else{
//                $order = "";
//            }    
//EDITEND kanderu 2020/09/04                
             $order = ' order by ';
            if($_POST["sort_table"]){
                $order .= $_POST['sort_table']; 
            }else{
                $order .= 'organization_id';
            }            
            $sql  .= "select ";
            $sql  .= "	TRN1750.organization_id, ";
            $sql  .= "	v.abbreviated_name, ";
            $sql  .= "	to_char(date(substr(TRN1750.PROC_DATE, 1, 8)), 'YYYY/MM/DD') as proc_date , ";
            $sql  .= "	TRN1750.HIDESEQ , ";
            $sql  .= "	mst0201.prod_cd, ";
            $sql  .= "	coalesce(mst0201.prod_nm,mst0201.prod_kn,'') as prod_nm, ";
// 税率項目追加 oota 20200414 START
            $sql  .= "	coalesce(trn0102.tax_rate, mst0201.prod_tax, 0) as tax_rate, ";
// 税率項目追加 oota 20200414 END
            $sql  .= "	mst0201.maker_cd, ";
            $sql  .= "	TRN1750.DATA_TYPE , ";
            $sql  .= "	TRN1401.PROC_KBN , ";
//            $sql  .= "	TRN1750.DEN_HIDESEQ  ";
            $sql  .= "	 case  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '0101' then TRN0101.reji_no || ' - ' || TRN0101.account_no  ";       //売上、売上返品
            $sql  .= "		else '' || TRN1750.DEN_HIDESEQ ";
            $sql  .= "	 end as DEN_HIDESEQ ";
            $sql  .= "	,case  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '0101' then coalesce(mst0101.CUST_NM,mst0101.cust_kn,'')  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1101' then coalesce(MST1001.maker_nm,MST1001.maker_kn,'')  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1401' then coalesce(mst1101_1.supp_nm,mst1101_1.supp_kn,'') ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1501' then '' ";
            $sql  .= "		else '' ";
            $sql  .= "	 end as to_name ";
            $sql  .= "	,case  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '0101' and TRN1750.amount <= 0 then '売上'  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '0101' and TRN1750.amount >  0 then '売上返品' ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1101' and TRN1750.amount >= 0 then '仕入'  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1101' and TRN1750.amount <  0 then '仕入返品' ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1401' and TRN1401.PROC_KBN = '11' then '入庫'  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1401' and TRN1401.PROC_KBN = '61' then '出庫'  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1401' and TRN1750.amount >= 0 and TRN1401.PROC_KBN = '66' then '廃棄'  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1401' and TRN1750.amount <  0 and TRN1401.PROC_KBN = '66'then '廃棄返品' ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1401' and TRN1750.amount >= 0 and TRN1401.PROC_KBN = '67' then '自家消費'  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1401' and TRN1750.amount <  0 and TRN1401.PROC_KBN = '67'then '自家消費返品' ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1401' and TRN1750.amount >= 0 and TRN1401.PROC_KBN = '68' then 'サンプル'  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1401' and TRN1750.amount <  0 and TRN1401.PROC_KBN = '68'then 'サンプル返品' ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1401' and TRN1750.amount <  0 then '' ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1501' and TRN1750.amount >= 0 then '店間移動'  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '1501' and TRN1750.amount <  0 then '店間移動返品' ";
            $sql  .= "		when TRN1750.DEN_HIDESEQ = '0' then '棚卸' ";
            $sql  .= "	end as type_nm, ";
// 2020/04/20 oota START
            $sql  .= "	case  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '0101' then TRN0102.AMOUNT ";
            $sql  .= "		else TRN1750.AMOUNT ";
            $sql  .= "	end	as AMOUNT "; // 数量
            $sql  .= "	,case  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '0101' then abs(trn0102.pure_total_i / NULLIF(TRN0102.AMOUNT,0)) ";      //売上関連の場合 明細純売上金額_内税抜き / 数量 → 単価
            $sql  .= "		when TRN1750.DATA_TYPE = '1101' then trn1102.saleprice / NULLIF(trn1102.amount,0) ";    // 仕入関連の場合 → 単価
            $sql  .= "		else trn1402.saleprice ";                                  // その他の場合 売単価  → 単価
            $sql  .= "	end	as unit_price "; // 単価
            $sql  .= "	,case  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '0101' then trn0102.pure_total_i ";    // 売上関連の場合 明細純売上金額_内税抜き → 売価
            $sql  .= "		when TRN1750.DATA_TYPE = '1101' then trn1102.saleprice ";       // 仕入関連の場合 商品売価 → 売価
            $sql  .= "		else trn1402.saleprice * trn1402.AMOUNT ";                      // その他の場合   売単価 * 数量 → 売価
            $sql  .= "	end	as saleprice "; // 売価
            $sql  .= "	,case  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '0101' then trn0102.day_costprice ";   // 売上関連の場合 当日原価 → 原価
            $sql  .= "		when TRN1750.DATA_TYPE = '1101' then trn1102.costprice ";       // 仕入関連の場合 仕入単価 → 原価
            $sql  .= "		else trn1402.unitprice ";                                       // その他の場合   原単価  → 原価
            $sql  .= "	end as costprice "; // 原価
            $sql  .= "	,case  ";
            $sql  .= "		when TRN1750.DATA_TYPE = '0101' then trn0102.day_costprice * TRN0102.AMOUNT "; // 売上関連の場合 当日原価 * 数量 → 原価金額
            $sql  .= "		when TRN1750.DATA_TYPE = '1101' then trn1102.costtotal ";                      // 仕入関連の場合 仕入金額       → 原価金額
            $sql  .= "		else trn1402.unitprice * trn1402.AMOUNT ";                                     // その他の場合   原単価 * 数量  → 原価金額
            $sql  .= "	end	as costprice_total	 "; // 原価金額
// 2020/04/20 oota END
            $sql  .= "from ";
            $sql  .= "	TRN1750 ";
            $sql  .= "left join TRN0101 on ";
            $sql  .= "	(TRN1750.DEN_HIDESEQ = TRN0101.HIDESEQ and TRN1750.organization_id = TRN0101.organization_id) ";
            $sql  .= "left join TRN0102 on ";
            $sql  .= "	(TRN0102.HIDESEQ = TRN0101.HIDESEQ and TRN1750.organization_id = TRN0102.organization_id and TRN1750.prod_cd = TRN0102.prod_cd and TRN1750.den_line_no = TRN0102.line_no)	 ";
            $sql  .= "left join MST0101 on ";
            $sql  .= "	(TRN0101.CUST_CD = MST0101.CUST_CD) ";
            $sql  .= "left join TRN1101 on ";
            $sql  .= "	(TRN1750.DEN_HIDESEQ = TRN1101.HIDESEQ and TRN1750.organization_id = TRN1101.organization_id) ";
            $sql  .= "left join TRN1102 on ";
            $sql  .= "	(TRN1102.HIDESEQ = TRN1101.HIDESEQ and TRN1750.organization_id = TRN1102.organization_id and TRN1750.prod_cd = TRN1102.prod_cd and TRN1750.den_line_no = TRN1102.line_no)		 ";
            $sql  .= "left join MST1101 on ";
            $sql  .= "	(TRN1101.SUPP_CD = MST1101.SUPP_CD and TRN1750.organization_id = MST1101.organization_id) ";
            $sql  .= "left join TRN1401 on ";
            $sql  .= "	(TRN1750.DEN_HIDESEQ = TRN1401.HIDESEQ and TRN1750.organization_id = TRN1401.organization_id and substr(TRN1750.PROC_DATE, 1, 8) = TRN1401.proc_date ) ";
            $sql  .= "left join TRN1402 on ";
            $sql  .= "	(TRN1402.HIDESEQ = TRN1401.HIDESEQ and TRN1750.den_line_no = TRN1402.line_no and TRN1750.organization_id = TRN1402.organization_id and TRN1750.prod_cd = TRN1402.prod_cd) ";
            $sql  .= "left join MST1101 mst1101_1 on ";
            $sql  .= "	(TRN1401.SUPP_CD = mst1101_1.SUPP_CD and TRN1750.organization_id = mst1101_1.organization_id) ";
            $sql  .= "left join MST0201 on ";
            $sql  .= "	(TRN1750.PROD_CD = MST0201.PROD_CD and TRN1750.organization_id = MST0201.organization_id) ";
            $sql  .= "left join mst1001 on (mst1001.maker_cd = mst0201.maker_cd and TRN1750.organization_id = mst1001.organization_id) ";
            $sql  .= "left join m_organization_detail v on TRN1750.organization_id = v.organization_id ";
            $sql  .= "where ";
            $sql  .= "		TRN1750.PROD_CD <> '' ";
            $sql  .= "	and substr(TRN1750.PROC_DATE, 1, 8) between :start_date and :end_date ";
            $sql  .= $where;
            $sql  .= "union all ";
            $sql  .= "select ";
            $sql  .= "	TRN1701.organization_id, ";
            $sql  .= "	v.abbreviated_name, ";
            $sql  .= "	to_char(date(substr(TRN1701.STKFIN_DATE, 1, 8)), 'YYYY/MM/DD') as PROC_DATE, ";
            $sql  .= "	TRN1701.HIDESEQ, ";
            $sql  .= "	mst0201.prod_cd, ";
            $sql  .= "	coalesce(mst0201.prod_nm,mst0201.prod_kn,'') as prod_nm,	 ";
// 税率項目追加 oota 20200414 START
            $sql  .= "	coalesce( mst0201.prod_tax, 0) as tax_rate, ";
// 税率項目追加 oota 20200414 END
            $sql  .= "	supp_cd as maker_cd, ";
            $sql  .= "	'0' as data_type, ";
            $sql  .= "	'0' as PROC_KBN, ";
            $sql  .= "	case when TRN1702.bb_date <> '' then to_char(date(substr(TRN1702.bb_date, 1, 8)), 'YYYY/MM/DD') else '' end as DEN_HIDESEQ, ";
            $sql  .= "	TRN1702.supp_kn as to_name, ";
            $sql  .= "	'棚卸入力' as type_nm, ";
            $sql  .= "	TRN1702.FIN_AMOUNT , "; // 確定在庫数量　→ 数量
// 2020/04/20 oota START
            $sql  .= "	trn1702.saleprice_ex as unit_price, ";                        // 商品売価(税抜) → 単価
            $sql  .= "	trn1702.saleprice_ex * TRN1702.FIN_AMOUNT as saleprice, ";    // 商品売価(税抜) * 確定在庫数量 → 売価
            $sql  .= "	trn1702.costprice as costprice, ";                            // 商品原価 → 原価
            $sql  .= "	trn1702.costprice * TRN1702.FIN_AMOUNT as costprice_total ";  // 商品原価 * 確定在庫数量 → 原価金額
// 2020/04/20 oota END
            $sql  .= "from ";
            $sql  .= "	TRN1702 ";
            $sql  .= "left join TRN1701 on ";
            $sql  .= "	(TRN1702.HIDESEQ = TRN1701.HIDESEQ and TRN1702.organization_id = TRN1701.organization_id) ";
            $sql  .= "left join MST0201 on ";
            $sql  .= "	(TRN1702.PROD_CD = MST0201.PROD_CD and TRN1702.organization_id = MST0201.organization_id) ";
            $sql  .= "left join m_organization_detail v on TRN1702.organization_id = v.organization_id ";
            $sql  .= "where ";
            $sql  .= "	substr(TRN1701.STKFIN_DATE, 1, 8) between :start_date and :end_date ";
            $sql  .= $where;
 //EDITSTR kanderu 2020/09/04    
           // $sql  .= "order by ";            
        //    $sql  .= "	organization_id  ";
            $sql .= $order; 
//            $sql  .= "	,PROC_DATE  ";
//            $sql  .= "	,prod_cd ";
//            $sql  .= "	,type_nm ";
//EDITEND kanderu 2020/09/04     
            $searchArray[':start_date'] = str_replace('/','',$postArray['start_date']);
            $searchArray[':end_date']  = str_replace('/','',$postArray['end_date']);
//print_r($sql);
            
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
           // print_r($sql);
//print_r($displayItemDataList);
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
            $sql .= " 	ORDER BY.department_code, d.staff_cd, t2.proc_date,DEN_HIDESEQ ";
              print_r($sql);
            $Log->trace("END creatSQL");
            return $sql;            
        }
    }
?>
