<?php
    /**
     * @file      部門別売上集計表 [M]
     * @author    川橋
     * @date      2019/06/11
     * @version   1.00
     * @note      帳票 - 部門別売上集計表の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetProductClassification_zeinuki extends BaseModel
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
        //kanderu 20211117
        

        /**
         * 帳票フォーム一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        public function getFormListData( $postArray, &$searchArray )
        {
             global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getFormListData");
            // 集計条件の設定 期間指定
            $searchArray['proc_date_start'] = str_replace('/','',$postArray['start_date']);
            $searchArray['proc_date_end'] = str_replace('/','',$postArray['end_date']);

            $where = ""; 

            // 集計条件の設定 分類条件指定
            if($postArray['prodclass_cd'] !== 'false'){
                if($postArray['middle_class_chk']){
                    if( $postArray['prodclass_select'] === 'empty'){
                        $where .= " and t3.prod_t_cd1||'_'||coalesce(t3.prod_t_cd2,'') in (".$postArray['prodclass_cd'].")";
                    }else{
                        $where .= " and t3.prod_t_cd1||'_'||coalesce(t3.prod_t_cd2,'') in ('".$postArray['prodclass_select']."')";
                    }
                }else{
                    if( $postArray['prodclass_select'] === 'empty'){
                        $where .= " and t3.prod_t_cd1 in (".$postArray['prodclass_cd'].")";
                    }else{
                        $where .= " and t3.prod_t_cd1 in ('".$postArray['prodclass_select']."')";
                    }
                }
            }
            
            // 集計条件の設定 店舗指定
            if($postArray['org_id'] !== 'false'){
                if( $postArray['org_select'] === 'empty'){
                    $where .= " and t1.organization_id in (".$postArray['org_id'].")";
                }else{
                    $where .= " and t1.organization_id in (".$postArray['org_select'].")";
                }
            }
            
            // 表示順指定
            if($postArray['sort'] && $postArray['sort'] !== 'false'){
                $array_sort = explode('#',$postArray['sort']);
                $order =$array_sort[0];
                if($array_sort[1] < 0 ){
                    $order .= " desc ";
                }
            }else{
                $order = " prod_t_cd1 ";               
            }

            // 中分類指定条件
            if($postArray['middle_class_chk']){
                $order  .= " ,prod_t_cd2 ";
            } 
            $sql1  .= " ";
            $sql1  .= " select ";
            $sql1  .= "      t.prod_t_cd1 as prod_t_cd1 ";
            if($postArray['middle_class_chk']){
                    $sql1  .= "  ,t.prod_t_cd21 as prod_t_cd2 ";
                    $sql1  .= "  , case when t.prod_t_nm_2 <> '' then t.prod_t_nm_2 else '未登録' end as prod_t_nm_2 ";
            }
            $sql1  .= "  , case when t.prod_t_nm_1 <> '' then t.prod_t_nm_1  else '未登録' end as prod_t_nm_1 ";
            $sql1  .= "  , sum(t.genkin_amount) as genkin_amount ";
            $sql1  .= "  , sum(t.genkin_saleprofit) as genkin_saleprofit ";
            $sql1  .= "  , sum(t.genkin_uitem_calc + t.genkin_iitem_calc + t.genkin_hitem_calc) as genkin_pure_total  ";
            $sql1  .= "  , sum(t.kake_amount) as kake_amount ";
            $sql1  .= "  , sum(t.kake_saleprofit) as kake_saleprofit ";
            $sql1  .= "  , sum(t.kake_uitem_calc + t.kake_iitem_calc + t.kake_hitem_calc) as kake_pure_total ";
            $sql1  .= "  , sum(t.sale_amount) as sale_amount ";
            $sql1  .= "  , sum(t.sale_profit) as sale_profit ";
            $sql1  .= "  , sum(t.genkin_uitem_calc + t.genkin_iitem_calc + t.genkin_hitem_calc + t.kake_uitem_calc + t.kake_iitem_calc+ t.kake_hitem_calc) as sale_total ";
            $sql1  .= "  , coalesce(sum(t.sale_profit)/NULLIF(sum(t.genkin_uitem_calc + t.genkin_iitem_calc + t.genkin_hitem_calc + t.kake_uitem_calc + t.kake_iitem_calc+ t.kake_hitem_calc),0)*100,0) as sale_margin ";
            $sql1  .= " from (  ";
            $sql1  .= "    select ";
            $sql1  .= "      t_data.prod_t_cd1 as prod_t_cd1 ";
            $sql1  .= "      , t_data.prod_t_nm_1 as prod_t_nm_1 ";
            $sql1  .= "      , t_data.prod_t_cd11 as prod_t_cd11 ";
            $sql1  .= "      , t_data.prod_t_cd21 as prod_t_cd21 ";
            $sql1  .= "      , t_data.prod_t_cd31 as prod_t_cd31 ";
            $sql1  .= "      , t_data.prod_t_cd41 as prod_t_cd41 ";
            $sql1  .= "      , t_data.genkin_saleprofit as genkin_saleprofit ";
            $sql1  .= "      , t_data.genkin_amount as genkin_amount ";
            $sql1  .= "      , t_data.kake_amount as kake_amount ";
            $sql1  .= "      , t_data.kake_saleprofit as kake_saleprofit ";
            $sql1  .= "      , (t_data.genkin_amount + t_data.kake_amount) as sale_amount ";
            $sql1  .= "      , (t_data.genkin_saleprofit + t_data.kake_saleprofit) as sale_profit ";
            if($postArray['middle_class_chk']){
                    $sql1  .= " ,prod_t_nm_2 as prod_t_nm_2 ";
            }
            $sql1  .= "      , coalesce(case when (t_data.uitem <> 0 and (t_data.iitem <> 0 or t_data.hitem <> 0) and (t_data.credit_money + t_data.gift_money) <> 0)  ";
            $sql1  .= "             then (t_data.credit_money + t_data.gift_money - t_data.iitem - t_data.itax - t_data.hitem - t_data.htax) - round(t_data.utax * (  ";
            $sql1  .= "             t_data.credit_money + t_data.gift_money - t_data.iitem - t_data.itax - t_data.hitem - t_data.htax ) / NULLIF(t_data.uitem + t_data.utax, 0))  ";
            $sql1  .= "             else (t_data.credit_money + t_data.gift_money) - round(  ";
            $sql1  .= "             t_data.utax * (t_data.credit_money + t_data.gift_money) / NULLIF(t_data.uitem + t_data.utax, 0)) end, 0) as kake_uitem_calc ";
            $sql1  .= "      , coalesce(case when (t_data.iitem <> 0 and (t_data.uitem <> 0 or t_data.hitem <> 0) and (t_data.credit_money + t_data.gift_money) <> 0 )  ";
            $sql1  .= "             then (t_data.credit_money + t_data.gift_money - t_data.uitem - t_data.utax - t_data.hitem - t_data.htax) - round(t_data.itax * ( ";
            $sql1  .= "             t_data.credit_money + t_data.gift_money - t_data.uitem - t_data.utax - t_data.hitem - t_data.htax) / NULLIF(t_data.iitem + t_data.itax, 0) )  ";
            $sql1  .= "             else (t_data.credit_money + t_data.gift_money) - round(  ";
            $sql1  .= "             t_data.itax * (t_data.credit_money + t_data.gift_money) / NULLIF(t_data.iitem + t_data.itax, 0))end, 0 ) as kake_iitem_calc ";
            $sql1  .= "      , coalesce(case when (t_data.hitem <> 0 and (t_data.uitem <> 0 or t_data.iitem <> 0) and (t_data.credit_money + t_data.gift_money) <> 0)  ";
            $sql1  .= "            then (t_data.credit_money + t_data.gift_money - t_data.uitem - t_data.utax - t_data.iitem - t_data.itax) - round(  ";
            $sql1  .= "            t_data.htax * (t_data.credit_money + t_data.gift_money - t_data.uitem - t_data.utax - t_data.iitem - t_data.itax) / NULLIF(t_data.hitem + t_data.htax, 0))  ";
            $sql1  .= "            else (t_data.credit_money + t_data.gift_money) - round(  ";
            $sql1  .= "            t_data.htax * (t_data.credit_money + t_data.gift_money) / NULLIF(t_data.hitem + t_data.htax, 0)) end, 0) as kake_hitem_calc ";
            $sql1  .= "      , coalesce(case when (t_data.uitem <> 0 and (t_data.iitem <> 0 or t_data.hitem <> 0) and (t_data.genkin + t_data.point_money) <> 0)  ";
            $sql1  .= "            then (t_data.genkin + t_data.point_money - t_data.iitem - t_data.itax - t_data.hitem - t_data.htax) - round(  ";
            $sql1  .= "            t_data.utax * (t_data.genkin + t_data.point_money - t_data.iitem - t_data.itax - t_data.hitem - t_data.htax) / NULLIF(t_data.uitem + t_data.utax, 0))  ";
            $sql1  .= "            else t_data.genkin + t_data.point_money - round(t_data.utax * (t_data.genkin + t_data.point_money) / NULLIF(t_data.uitem + t_data.utax, 0)) end , 0 ) as genkin_uitem_calc ";
            $sql1  .= "      , coalesce(case when (t_data.iitem <> 0 and (t_data.uitem <> 0 or t_data.hitem <> 0) and (t_data.genkin + t_data.point_money) <> 0)  ";
            $sql1  .= "            then (t_data.genkin + t_data.point_money - t_data.uitem - t_data.utax - t_data.hitem - t_data.htax) - round(t_data.itax * (  ";
            $sql1  .= "            t_data.genkin + t_data.point_money - t_data.uitem - t_data.utax - t_data.hitem - t_data.htax) / NULLIF(t_data.iitem + t_data.itax, 0))  ";
            $sql1  .= "            else t_data.genkin + t_data.point_money - round(  ";
            $sql1  .= "            t_data.itax * (t_data.genkin + t_data.point_money) / NULLIF(t_data.iitem + t_data.itax, 0))  end  , 0) as genkin_iitem_calc ";
            $sql1  .= "      , coalesce(case when (t_data.hitem <> 0 and (t_data.uitem <> 0 or t_data.iitem <> 0) and (t_data.genkin + t_data.point_money) <> 0) "; 
            $sql1  .= "            then (t_data.genkin + t_data.point_money - t_data.uitem - t_data.utax - t_data.iitem - t_data.itax ) - round(t_data.htax * (  ";
            $sql1  .= "            t_data.genkin + t_data.point_money - t_data.uitem - t_data.utax - t_data.iitem - t_data.itax) / NULLIF(t_data.hitem + t_data.htax, 0))  ";
            $sql1  .= "            else t_data.genkin + t_data.point_money - round( t_data.htax * (t_data.genkin + t_data.point_money) / NULLIF(t_data.hitem + t_data.htax, 0)) end , 0 ) as genkin_hitem_calc  ";
            $sql1  .= "    from (  ";
            $sql1  .= "        select ";
            $sql1  .= "          t3.prod_t_cd1 as prod_t_cd1 ";
            $sql1  .= "          , m1.prod_t_cd1 as prod_t_cd11 ";
            $sql1  .= "          , m1.prod_t_cd2 as prod_t_cd21 ";
            $sql1  .= "          , m1.prod_t_cd3 as prod_t_cd31 ";
            $sql1  .= "          , m1.prod_t_cd4 as prod_t_cd41 ";
            $sql1  .= "          , m1.prod_t_nm as prod_t_nm_1 ";
            if($postArray['middle_class_chk']){
                    $sql1  .= " ,m10.prod_t_nm as prod_t_nm_2 ";
            }
            $sql1  .= "          , coalesce(case when (azukari <> 0 and turisen >= 0) or (azukari < 0 and turisen < 0) then genkin else 0 end , 0) as genkin ";
            $sql1  .= "          , coalesce(case when (azukari <> 0 and turisen >= 0) or (azukari < 0 and turisen < 0) then t2.profit_i else 0 end , 0) as genkin_saleprofit ";
            $sql1  .= "          , coalesce(case when (azukari <> 0 and turisen >= 0) or (azukari < 0 and turisen < 0) then t2.amount  else 0 end , 0) as genkin_amount ";
            $sql1  .= "          , coalesce(case when credit_money + gift_money <> 0 then t2.profit_i else 0  end , 0) as kake_saleprofit ";
            $sql1  .= "          , coalesce(case when credit_money + gift_money <> 0 then t2.amount else 0 end , 0) as kake_amount ";
            $sql1  .= "          , credit_money ";
            $sql1  .= "          , gift_money ";
            $sql1  .= "          , point_money ";
            $sql1  .= "          , t2.pure_total_i as tot_pure_total_i ";
            $sql1  .= "          , t1.utax + t1.itax as tot_itax ";
            $sql1  .= "          , t_uitem as uitem ";
            $sql1  .= "          , t1.utax as utax ";
            $sql1  .= "          , t_iitem as iitem ";
            $sql1  .= "          , t1.itax as itax ";
            $sql1  .= "          , t_hitem as hitem ";
            $sql1  .= "          , t_htax as htax ";
            $sql1  .= "          , t2.amount as amount ";
            $sql1  .= "        from ";
            $sql1  .= "          trn0203  ";
            $sql1  .= "          left join trn0101 t1 using (organization_id, hideseq)  ";
            $sql1  .= "          left join trn0102 t3 using (organization_id, hideseq, line_no)  ";
            $sql1  .= "          left join (  ";
            $sql1  .= "            select ";
            $sql1  .= "              t2.organization_id ";
            $sql1  .= "             , t2.hideseq ";
           // $sql1  .= "              , sum(t2.pure_total_i) as pure_total_i ";
            $sql1  .= "              , sum(case when t2.tax_type = '1' then t2.pure_total_i else 0 end) as pure_total_i ";
            $sql1  .= "              , sum(t2.itax) as itax ";
            $sql1  .= "              , sum(t2.amount) as amount ";
            $sql1  .= "              , sum(t2.profit_i) as profit_i ";
            $sql1  .= "              , sum(case when t2.tax_type = '1' then t2.pure_total_i else 0 end) as t_uitem ";
            $sql1  .= "              , sum(case when t2.tax_type = '1' then t2.itax else 0 end) as t_utax ";
            $sql1  .= "              , sum(case when t2.tax_type = '2' then t2.pure_total_i else 0 end) as t_iitem ";
            $sql1  .= "              , sum(case when t2.tax_type = '2' then t2.itax else 0 end) as t_itax ";
            $sql1  .= "              , sum(case when t2.tax_type = '9' then t2.pure_total_i else 0 end) as t_hitem ";
            $sql1  .= "              , sum(case when t2.tax_type = '9' then t2.itax else 0 end ) as t_htax  ";
            $sql1  .= "            from ";
            $sql1  .= "              trn0102 t2  ";
            $sql1  .= "              left join trn0101 t1 using (organization_id, hideseq)  ";
            $sql1  .= "              where cancel_kbn = '0'  ";
            $sql1  .= "              and stop_kbn = '0'  ";
            $sql1  .= "            group by organization_id, hideseq ";
            $sql1  .= "          ) t2 using (organization_id, hideseq)  ";
            $sql1  .= "           left join mst0801 m1 on (t3.prod_t_cd1 = m1.prod_t_cd1 and t1.organization_id = m1.organization_id and m1.prod_t_cd2 = '')  ";
            if($postArray['middle_class_chk']){	
                    $sql1  .= "         left join mst0801 m10 on t1.organization_id = m10.organization_id and t3.prod_t_cd1 = m10.prod_t_cd1 and t3.prod_t_cd2 = m10.prod_t_cd2  ";
            }  
            $sql1  .= "            left join m_organization_detail v  on (t3.organization_id = v.organization_id) "; 
            $sql1  .= "        where ";
            $sql1  .= "          t1.proc_date between :proc_date_start and :proc_date_end  ";
            $sql1  .= "          and t3.amount is not null  ";
            $sql1  .= "       $where ";
            $sql1  .= "      ) T_data where amount <> 0 ";
            $sql1  .= "  ) t  ";
            $sql1  .= " group by ";
            $sql1  .= "  t.prod_t_cd1, t.prod_t_nm_1  ";
            if($postArray['middle_class_chk']){
                    $sql1  .= "	,t.prod_t_cd21 ";
                    $sql1  .= " ,t.prod_t_nm_2  ";  
            }
            $sql1  .= " order by ";
            $sql1  .= "  t.prod_t_cd1 ";
//          print_r($sql1);
            
            $result1 = $DBA->executeSQL($sql1,$searchArray);
          
           $displayItemDataList = array();

            if( $result1 === false )
            {
              $Log->trace("END getFormListData1");
              return $displayItemDataList;
            }

            while ( $data = $result1->fetch(PDO::FETCH_ASSOC) )
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
        public function getFormListData11( $postArray, &$searchArray )
        {
             global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getFormListData1");
            // 集計条件の設定 期間指定
            $searchArray['proc_date_start'] = str_replace('/','',$postArray['start_date']);
            $searchArray['proc_date_end'] = str_replace('/','',$postArray['end_date']);

            $where = ""; 

            // 集計条件の設定 分類条件指定
            if($postArray['prodclass_cd'] !== 'false'){
                if($postArray['middle_class_chk']){
                    if( $postArray['prodclass_select'] === 'empty'){
                        $where .= " and t2.prod_t_cd1||'_'||coalesce(t2.prod_t_cd2,'') in (".$postArray['prodclass_cd'].")";
                    }else{
                        $where .= " and t2.prod_t_cd1||'_'||coalesce(t2.prod_t_cd2,'') in ('".$postArray['prodclass_select']."')";
                    }
                }else{
                    if( $postArray['prodclass_select'] === 'empty'){
                        $where .= " and t2.prod_t_cd1 in (".$postArray['prodclass_cd'].")";
                    }else{
                        $where .= " and t2.prod_t_cd1 in ('".$postArray['prodclass_select']."')";
                    }
                }
            }
            
            // 集計条件の設定 店舗指定
            if($postArray['org_id'] !== 'false'){
                if( $postArray['org_select'] === 'empty'){
                    $where .= " and t1.organization_id in (".$postArray['org_id'].")";
                }else{
                    $where .= " and t1.organization_id in (".$postArray['org_select'].")";
                }
            }
            
            // 表示順指定
            if($postArray['sort'] && $postArray['sort'] !== 'false'){
                $array_sort = explode('#',$postArray['sort']);
                $order =$array_sort[0];
                if($array_sort[1] < 0 ){
                    $order .= " desc ";
                }
            }else{
                $order = " prod_t_cd1 ";               
            }

            // 中分類指定条件
            if($postArray['middle_class_chk']){
                $order  .= " ,prod_t_cd2 ";
            } 
            
//            $sql1  .= " 	select *";
//            // 中分類指定されていたら
//            if($postArray['middle_class_chk']){
//              $sql1  .= " 		,t.prod_t_cd2, ";
//		      $sql1  .= " 		t.prod_t_nm_2		 ";
//            }
//            $sql1  .= " ,case when e.sale_profit_1 = 0 then 0 else (t.sale_profit/e.sale_profit_1)*100 end as all_comp_1 ";
//            $sql1  .= " 	from  (";
//            $sql1  .= " 	select ";
////            $sql1  .= "     t2.prod_t_cd1 as prod_t_cd1,";
////            $sql1  .= "     t1.organization_id as organization_id,";
//            $sql1  .= "    t2.prod_t_cd1  as prod_t_cd1,";
//            $sql1  .= "    t1.organization_id as organization_id,";
//            $sql1  .= "    m1.prod_t_cd1 as prod_t_cd11,";
//            $sql1  .= "    m1.prod_t_cd2 as prod_t_cd21,";
//            $sql1  .= "    m1.prod_t_cd3 as prod_t_cd31,";
//            $sql1  .= "    m1.prod_t_cd4 as prod_t_cd41,";
//            $sql1  .= "    m1.prod_t_nm as prod_t_nm_1,";
//            $sql1  .= "    (t3.genkin - t2.point_excl_total)  as genkin_pure_total,";
//            // 中分類指定されていたら
//            if($postArray['middle_class_chk']){
//                $sql1  .= " 		t2.prod_t_cd2, ";
//                $sql1  .= " 		m1.prod_t_nm as prod_t_nm_2,		 ";
//            }
//            
////           // $sql1  .= " 	t2.amount as sale_amount,";
////            //$sql1  .= " 	t2.profit_i as sale_profit,";
////            $sql1  .= " 	m1.prod_t_cd1 as prod_t_cd11,";
////            $sql1  .= " 	m1.prod_t_nm as prod_t_nm_1,";
////            //$sql1  .= " 	t2.subtotal as total,";
////            //$sql1  .= " 	case  when t2.tax_type = '1' then  t2.itax else 0 end as tax,";
////            $sql1  .= " 	case  when t3.genkin is null then 0 else (t3.genkin - t2.point_excl_total) end as genkin_pure_total,";
//            $sql1  .= " 	case when t3.genkin > 0 then t2.profit_i else 0 end as genkin_saleprofit,";
//            $sql1  .= " 	case when t3.genkin > 0 then t2.amount else 0 end  as genkin_amount,";
//            $sql1  .= " 	case when t3.genkin = 0 then t2.amount else 0 end  as kake_amount,";
//            $sql1  .= " 	case  when t3.credit_money is null then 0 else  t3.credit_money end  as kake_pure_total, ";
//            $sql1  .= " 	case when t3.genkin = 0 then t2.profit_i else 0 end as kake_saleprofit,";
//           // $sql1  .= " 	t2.subtotal +(case  when t2.tax_type = '1' then  t2.itax else 0 end ) as  sale_total,";
//            //$sql1  .= " 	case  when t2.subtotal = 0 then 0 else t2.profit_i / (t2.subtotal +(case  when t2.tax_type = '1' then  t2.itax else 0 end ))  * 100  end as sale_margin,";
//            $sql1  .= "    case when ((case  when t3.genkin is null then 0 else (t3.genkin - t2.point_excl_total) end )+(case  when t3.credit_money is null then 0 else  t3.credit_money end)) = 0";
//            $sql1  .= "    then 0 else";
//            $sql1  .= "    ((case when t3.genkin > 0 then t2.profit_i else 0 end )+(case when t3.genkin = 0 then t2.profit_i else 0 end))";
//            $sql1  .= "    /((case  when t3.genkin is null then 0 else (t3.genkin - t2.point_excl_total) end )+(case  when t3.credit_money is null then 0 else  t3.credit_money end))* 100 end as sale_margin,";
//            
//            $sql1  .= "     (case when t3.genkin > 0 then t2.amount else 0 end )+(case when t3.genkin = 0 then t2.amount else 0 end)  as sale_amount,";
//            $sql1  .= "     (case  when t3.genkin is null then 0 else (t3.genkin - t2.point_excl_total) end )+(case  when t3.credit_money is null then 0 else  t3.credit_money end)  as sale_total,";
//            $sql1  .= "     (case when t3.genkin > 0 then t2.profit_i else 0 end )+(case when t3.genkin = 0 then t2.profit_i else 0 end)  as sale_profit";
//            $sql1  .= " 	from ";
//            $sql1  .= " 	trn0101 t1 ";
//            $sql1  .= " 	left join trn0102 t2 using (organization_id,hideseq) ";
//            $sql1  .= " 	left join mst0801 m1  on (t2.prod_t_cd2 = m1.prod_t_cd2 and t2.prod_t_cd3 = m1.prod_t_cd3 and t2.prod_t_cd4 = m1.prod_t_cd4 and t2.prod_t_cd1 = m1.prod_t_cd1 and t2.organization_id = m1.organization_id )";
//            $sql1  .= " 	left join m_organization_detail v on (t2.organization_id = v.organization_id ) ";
//            $sql1  .= " 	left join trn0203 t3 on (t3.line_no = t2.line_no and t3.organization_id = t2.organization_id and t3.hideseq = t2.hideseq) ";
//            $sql1  .= " 	where ";
//            $sql1  .= " 	t2.cancel_kbn = '0'";
//            $sql1  .= " 	and t1.stop_kbn = '0' ";
//            $sql1  .= "    and t3.genkin is not null ";
//            $sql1  .= " 	$where";
//            $sql1  .= "     and t1.proc_date between :proc_date_start and :proc_date_end ";
//            // 中分類指定されていたら
//            if($postArray['middle_class_chk']){
//                $sql1  .= "	,t2.prod_t_cd2 ";
//              
//            }	
//            $sql1  .= " 	order by t1.proc_date, t1.organization_id )t  ";
//            
//            
//            $sql1  .= " 	left join (";
//            $sql1  .= " 	select ";
//            $sql1  .= "     t2.prod_t_cd1  as prod_t_cd12,";
//            //$sql1  .= "     m1.prod_t_cd1 as prod_t_cd1_1,";
//            //$sql1  .= "     t1.organization_id as organization_id,";
//            //$sql1  .= " 	t2.amount as sale_amount_1,";
//            //$sql1  .= " 	t2.profit_i as sale_profit_1,";
//            //$sql1  .= " 	case  when t3.genkin is null then 0 else t3.genkin end as genkin_pure_total_1,";
//            $sql1  .= "    t1.organization_id as organization_id,";
//            $sql1  .= "    m1.prod_t_cd1 as prod_t_cd1_1,";
//            $sql1  .= "    m1.prod_t_cd2 as prod_t_cd2_1,";
//            $sql1  .= "    m1.prod_t_cd3 as prod_t_cd3_1,";
//            $sql1  .= "    m1.prod_t_cd4 as prod_t_cd4_1,";
//           // $sql1  .= "    m1.prod_t_nm as prod_t_nm_1,";
//            $sql1  .= "     t3.genkin  as genkin_pure_total_1,";
//            $sql1  .= " 	case when t3.genkin > 0 then t2.profit_i else 0 end as genkin_saleprofit_1,";
//            $sql1  .= " 	case when t3.genkin > 0 then t2.amount else 0 end  as genkin_amount_1,";
//            $sql1  .= " 	case when t3.genkin = 0 then t2.amount else 0 end  as kake_amount_1,";
//            $sql1  .= " 	case  when t3.credit_money is null then 0 else  t3.credit_money end  as kake_pure_total_1,";
//            $sql1  .= " 	case when t3.genkin = 0 then t2.profit_i else 0 end as kake_saleprofit_1,";
//            //$sql1  .= " 	t2.subtotal +(case  when t2.tax_type = '1' then  t2.itax else 0 end ) as  sale_total_1,";
//            //$sql1  .= " 	case  when t2.subtotal = 0 then 0 else t2.profit_i / (t2.subtotal +(case  when t2.tax_type = '1' then  t2.itax else 0 end ))  * 100  end as sale_margin";
//            
//            $sql1  .= "     (case when t3.genkin > 0 then t2.amount else 0 end )+(case when t3.genkin = 0 then t2.amount else 0 end)  as sale_amount_1,";
//            $sql1  .= "     (case  when t3.genkin is null then 0 else (t3.genkin - t2.point_excl_total) end )+(case  when t3.credit_money is null then 0 else  t3.credit_money end)  as sale_total_1,";
//            $sql1  .= "     (case when t3.genkin > 0 then t2.profit_i else 0 end )+(case when t3.genkin = 0 then t2.profit_i else 0 end)  as sale_profit_1,";
//            $sql1  .= "    case when ((case  when t3.genkin is null then 0 else (t3.genkin - t2.point_excl_total) end )+(case  when t3.credit_money is null then 0 else  t3.credit_money end)) = 0";
//            $sql1  .= "    then 0 else";
//            $sql1  .= "    ((case when t3.genkin > 0 then t2.profit_i else 0 end )+(case when t3.genkin = 0 then t2.profit_i else 0 end))";
//            $sql1  .= "    /((case  when t3.genkin is null then 0 else (t3.genkin - t2.point_excl_total) end )+(case  when t3.credit_money is null then 0 else  t3.credit_money end))* 100 end as sale_margin_1";
//            $sql1  .= " 	from ";
//            $sql1  .= " 	trn0101 t1 ";
//            $sql1  .= " 	left join trn0102 t2 using (organization_id,hideseq) ";
//            $sql1  .= " 	left join mst0801 m1  on (t2.prod_t_cd2 = m1.prod_t_cd2 and t2.prod_t_cd3 = m1.prod_t_cd3 and t2.prod_t_cd4 = m1.prod_t_cd4 and t2.prod_t_cd1 = m1.prod_t_cd1 and t2.organization_id = m1.organization_id )";
//            $sql1  .= " 	left join m_organization_detail v on (t2.organization_id = v.organization_id ) ";
//            $sql1  .= " 	left join trn0203 t3 on (t3.line_no = t2.line_no and t3.organization_id = t2.organization_id and t3.hideseq = t2.hideseq) ";
//            $sql1  .= " 	where ";
//            $sql1  .= " 	t2.cancel_kbn = '0'";
//            $sql1  .= " 	and t1.stop_kbn = '0' ";
//            $sql1  .= "    and t3.genkin is not null ";
//            $sql1  .= " 	$where";
//            $sql1  .= "     and t1.proc_date  between (:proc_date_start::int -10000)::text and (:proc_date_end::int -10000)::text ";
//
////            $sql1  .= "     group by  ";
////            $sql1  .= "     t2.prod_t_cd1,  ";
////            $sql1  .= "     t1.organization_id, ";
////            $sql1  .= "     m1.prod_t_cd1,  ";
////            $sql1  .= "     m1.prod_t_nm, ";
////            $sql1  .= "     t3.genkin, ";
////            $sql1  .= "     t2.point_excl_total, ";
////            $sql1  .= "     t2.profit_i,t2.amount, ";
////            $sql1  .= "     t3.credit_money, ";
////            $sql1  .= "     t1.trndate   ";
//            // 中分類指定されていたら
//            if($postArray['middle_class_chk']){
//                $sql1  .= "	,t2.prod_t_cd2 ";
//               
//            }	 
//            $sql1  .= " 	order by t1.trndate, t1.organization_id)e on e.prod_t_cd12 = t.prod_t_cd1 and t.organization_id = e.organization_id and t.prod_t_cd11 = e.prod_t_cd1_1 ";
//            $sql1  .= "   and t.prod_t_cd21= e.prod_t_cd2_1 ";
//            $sql1  .= "  and t.prod_t_cd31= e.prod_t_cd3_1 ";
//            $sql1  .= " and t.prod_t_cd41= e.prod_t_cd4_1 ";
    
            
           $sql1  .= " select * ";
           //$sql1  .= " ,case when e.sale_profit_1 = 0 then 0 else (t.sale_profit/e.sale_profit_1)*100 end as all_comp_1  ";
           $sql1  .= " from  ( ";
           $sql1  .= " select  ";
           $sql1  .= " t2.prod_t_cd1  as prod_t_cd1, ";
           $sql1  .= " t1.organization_id as organization_id, ";
           $sql1  .= " m1.prod_t_cd1 as prod_t_cd11, ";
           $sql1  .= " m1.prod_t_cd2 as prod_t_cd21, ";
           $sql1  .= " m1.prod_t_cd3 as prod_t_cd31, ";
           $sql1  .= " m1.prod_t_cd4 as prod_t_cd41, ";
           $sql1  .= " m1.prod_t_nm as prod_t_nm_1, ";
           $sql1  .= " (t3.genkin +t3.point_money)  as genkin_pure_total_1,  ";
           $sql1  .= " case when t3.genkin > 0 then t2.profit_i else 0 end as genkin_saleprofit_1, ";
           $sql1  .= " case when t3.genkin > 0 then t2.amount else 0 end  as genkin_amount_1, ";
           $sql1  .= " case when t3.genkin = 0 then t2.amount else 0 end  as kake_amount_1, ";
           $sql1  .= " case  when t3.credit_money is null then 0 else  t3.credit_money end  as kake_pure_total_1,  ";
           $sql1  .= " case when t3.genkin = 0 then t2.profit_i else 0 end as kake_saleprofit_1, ";
           $sql1  .= " case when ((case  when t3.genkin is null then 0 else (t3.genkin +t3.point_money) end )+(case  when t3.credit_money is null then 0 else  t3.credit_money end)) = 0 ";
           $sql1  .= " then 0 else ";
           $sql1  .= " ((case when t3.genkin > 0 then t2.profit_i else 0 end )+(case when t3.genkin = 0 then t2.profit_i else 0 end)) ";
           $sql1  .= " /((case  when t3.genkin is null then 0 else (t3.genkin +t3.point_money) end )+(case  when t3.credit_money is null then 0 else  t3.credit_money end))* 100 end as sale_margin_1, ";
           $sql1  .= " (case when t3.genkin > 0 then t2.amount else 0 end )+(case when t3.genkin = 0 then t2.amount else 0 end)  as sale_amount_1, ";
           $sql1  .= " (case  when t3.genkin is null then 0 else (t3.genkin +t3.point_money) end )+(case  when t3.credit_money is null then 0 else  t3.credit_money end)  as sale_total_1, ";
           $sql1  .= " (case when t3.genkin > 0 then t2.profit_i else 0 end )+(case when t3.genkin = 0 then t2.profit_i else 0 end)  as sale_profit_1 ";
           $sql1  .= " from  ";
           $sql1  .= " trn0101 t1  ";
           $sql1  .= " left join trn0102 t2 using (organization_id,hideseq)  ";
           $sql1  .= " left join mst0801 m1  on (t2.prod_t_cd2 = m1.prod_t_cd2 and t2.prod_t_cd3 = m1.prod_t_cd3 and t2.prod_t_cd4 = m1.prod_t_cd4 and t2.prod_t_cd1 = m1.prod_t_cd1 and t2.organization_id = m1.organization_id ) ";
           $sql1  .= " left join m_organization_detail v on (t2.organization_id = v.organization_id )  ";
           $sql1  .= " left join trn0203 t3 on (t3.line_no = t2.line_no and t3.organization_id = t2.organization_id and t3.hideseq = t2.hideseq)  ";
           $sql1  .= " where  ";
          // $sql1  .= " t2.cancel_kbn = '0' ";
           $sql1  .= "  t1.stop_kbn = '0'  ";
          // $sql1  .= " and t1.organization_id ='6' ";
           $sql1  .= " and t3.genkin is not null ";
           $sql1  .= " $where ";
           $sql1  .= "     and t1.proc_date between :proc_date_start and :proc_date_end ";
           $sql1  .= " order by t1.proc_date, t1.organization_id )t  ";
           // print_r($sql1);
            $result1 = $DBA->executeSQL($sql1,$searchArray);
          
           $displayItemDataList = array();

            if( $result1 === false )
            {
              $Log->trace("END getFormListData11");
              return $displayItemDataList;
            }

            while ( $data1 = $result1->fetch(PDO::FETCH_ASSOC) )
            {
              array_push( $displayItemDataList, $data1);              
            }
           
            $Log->trace("END getFormListData1");
            return $displayItemDataList;
        }         
        
        
        
        
    }
    
?>
