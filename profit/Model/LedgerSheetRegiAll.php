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
    class LedgerSheetRegiAll extends BaseModel
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
        public function get_jsk1010_data()
        {
            global $DBA,$Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql   = "";
            $sql  .= "select 'select '||list_col||' from jsk1010' as query from ";
            $sql  .= "	(select string_agg('sum('||column_name||') as '||column_name,',') as list_col  ";
            $sql  .= "	from information_schema.columns  ";
            $sql  .= "	where  ";
            $sql  .= "			table_schema = '".$_SESSION["SCHEMA"]."'  ";
            $sql  .= "		and table_name = 'jsk1010'  ";
            $sql  .= "		and character_maximum_length is null  ";
            $sql  .= "		and datetime_precision is null  ";
            $sql  .= "		and ordinal_position > 18 ";	            
            $sql  .= "	) quer  where 1 = :val ";
            $sql  .= "limit 1 ";
            $searchArray[':val']=1;

            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_data");
                return $Datas;
            }
            $query='';
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $query = $data['query'];

            }            
            
            $sql = $query.' where proc_date between :start_date and :end_date';
            if($_POST['org_id'] !== 'false'){
                if( $_POST['org_select'] === 'empty'){
                    $sql .= " and organization_id in (".$_POST['org_id'].")";
                }else{
                    $sql .= " and organization_id in (".$_POST['org_select'].")";
                }
            }

            $searchArray=[];
            $searchArray[':start_date'] = '';
            $searchArray[':end_date']   = '';
            if($_POST['start_date']){
                $searchArray[':start_date'] = str_replace('/','',$_POST['start_date']);
            }
            if($_POST['end_date']){
                $searchArray[':end_date']   = str_replace('/','',$_POST['end_date']);
            }    

            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas = $data;
            }

            $Log->trace("END get_data");
            return $Datas;
        }
        /**
         * クレジットデータ (売上)
         * @param    $postArray   入力パラメータ(start_date/end_date)
         * @return  
         */        
        public function get_credit_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_data");
			$sql   = "";
            $sql  .= " 		select ";	
            $sql  .= " 			t.credit_nm as credit_nm_f , ";	
            $sql  .= " 			0 as sale_cust_cnt_f, ";	
            $sql  .= " 			0 as sale_total_f, ";	
            $sql  .= " 			sum(t.credit_money) as kake_total_f, ";	
            $sql  .= " 			sum(t.count) as kake_cust_cnt_f, ";	
            $sql  .= " 			sum(t.credit_money) as total_total_f, ";	
            $sql  .= " 			sum(t.count) as total_cust_cnt_f, ";	
            $sql  .= " 			0 as sale_cust_cnt_cumul, ";	
            $sql  .= " 			0 as sale_total_cumul, ";	
            $sql  .= " 			sum(sum(t.count)) over (order by sorder,t.credit_nm asc rows between unbounded preceding and current row) as kake_cust_cnt_cumul, ";	
            $sql  .= " 			sum(sum(t.credit_money)) over (order by sorder,t.credit_nm asc rows between unbounded preceding and current row) as kake_total_cumul ";	
            $sql  .= " 		from ( ";	
            $sql  .= " 			select  ";	
            $sql  .= " 				1 as sorder, ";	
            $sql  .= " 				proc_date, ";	
            $sql  .= " 				organization_id, ";	
            $sql  .= " 				credit_nm , ";	
            $sql  .= " 				sum(credit_money) as credit_money, ";	
            $sql  .= " 				sum(case when credit_money < 0 then -1 else 1 end) as count  ";	
            $sql  .= " 			from trn0201 ";	
            $sql  .= " 			left join trn0101 using (organization_id,hideseq) ";	
            $sql  .= " 			where 	stop_kbn = '0' ";	
			$sql  .= " 				and proc_date between :start_date and :end_date ";				
            $sql  .= " 			group by proc_date,organization_id,credit_nm  ";	
            $sql  .= " 			union  ";	
            $sql  .= " 			select  ";	
            $sql  .= " 				2 as sorder, ";	
            $sql  .= " 				proc_date, ";	
            $sql  .= " 				organization_id, ";	
            $sql  .= " 				gift_certi_nm , ";	
            $sql  .= " 				sum(gift_money ) as credit_money, ";	
            $sql  .= " 				sum(case when gift_money < 0 then -1 else 1 end) as count  ";	
            $sql  .= " 			from trn0204 ";	
            $sql  .= " 			left join trn0101 using (organization_id,hideseq) ";	
            $sql  .= " 			where 	stop_kbn = '0' ";
			$sql  .= " 				and proc_date between :start_date and :end_date ";	
            $sql  .= " 			group by proc_date,organization_id,gift_certi_nm   ";	
            $sql  .= " 		)t ";	
            $sql  .= " 		where ";
            $sql  .= " 			proc_date between :start_date and :end_date ";			
            if($_POST['org_id'] !== 'false'){
                if( $_POST['org_select'] === 'empty'){
                    $sql .= " and organization_id in (".$_POST['org_id'].")";
                }else{
                    $sql .= " and organization_id in (".$_POST['org_select'].")";
                }
            }	
            $sql  .= " 		group by  sorder,t.credit_nm ";	
            $sql  .= " 		order by sorder,credit_nm ";
            
            $searchArray[':start_date'] = '';
            $searchArray[':end_date']   = '';
            if($_POST['start_date']){
                $searchArray[':start_date'] = str_replace('/','',$_POST['start_date']);
            }
            if($_POST['end_date']){
                $searchArray[':end_date']   = str_replace('/','',$_POST['end_date']);
            }
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas[] = $data;
            }

            $Log->trace("END get_data");

            return $Datas;
        }
        
        /**
         * クレジットデータ
         * @param    $postArray   入力パラメータ(start_date/end_date)
         * @return  
         */        
        public function get_kake_tax_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_data");
            
            $sql  = "";
            $sql  .= " select  ";
            $sql  .= " 	 sum( ";
            $sql  .= " 		case  ";
            $sql  .= " 			when tax_type = '0' then den_pure_total ";
            $sql  .= " 			else 0 ";
            $sql  .= " 		end ";
            $sql  .= " 	) as den_pure_total_0 ";
            $sql  .= " 	,sum( ";
            $sql  .= " 		case  ";
            $sql  .= " 			when tax_type = '0' then den_tax ";
            $sql  .= " 			else 0 ";
            $sql  .= " 		end ";
            $sql  .= " 	) as den_tax_0	 ";
            $sql  .= " 	,sum( ";
            $sql  .= " 		case  ";
            $sql  .= " 			when tax_type = '1' then den_pure_total ";
            $sql  .= " 			else 0 ";
            $sql  .= " 		end ";
            $sql  .= " 	) as den_pure_total_1 ";
            $sql  .= " 	,sum( ";
            $sql  .= " 		case  ";
            $sql  .= " 			when tax_type = '1' then den_tax ";
            $sql  .= " 			else 0 ";
            $sql  .= " 		end ";
            $sql  .= " 	) as den_tax_1 ";
            $sql  .= " 	,sum( ";
            $sql  .= " 		case  ";
            $sql  .= " 			when tax_type = '9' then den_pure_total ";
            $sql  .= " 			else 0 ";
            $sql  .= " 		end ";
            $sql  .= " 	) as den_pure_total_9 ";
            $sql  .= " 	,sum( ";
            $sql  .= " 		case  ";
            $sql  .= " 			when tax_type = '9' then den_tax ";
            $sql  .= " 			else 0 ";
            $sql  .= " 		end ";
            $sql  .= " 	) as den_tax_9	 ";
            $sql  .= " from jsk4160 ";
            $sql  .= " where ";
            $sql  .= " 	proc_date between :start_date and :end_date ";
            // 店舗条件
            if($_POST['org_id'] !== 'false'){
                if( $_POST['org_select'] === 'empty'){
                    $sql .= " and organization_id in (".$_POST['org_id'].")";
                }else{
                    $sql .= " and organization_id in (".$_POST['org_select'].")";
                }
            }
            
            $searchArray[':start_date'] = '';
            $searchArray[':end_date']   = '';
            if($_POST['start_date']){
                $searchArray[':start_date'] = str_replace('/','',$_POST['start_date']);
            }
            if($_POST['end_date']){
                $searchArray[':end_date']   = str_replace('/','',$_POST['end_date']);
            }
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas = $data;
            }

            $Log->trace("END get_data");

            return $Datas;
        } 
        
        public function get_tax_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_tax_data");
            $sql  = "";        

            $sql  .= " 		select  ";
            $sql  .= " 			 sum(genkin_cnt) as genkin_cnt  ";
                                         //計算式 売掛外税 対象金額 = (クレジット + ギフト)[売掛外税対象金額] - [売掛外税 税額] 
            $sql  .= " 			,sum( case when (t_data.uitem <> 0 and (t_data.iitem <> 0 or t_data.hitem <> 0) and (t_data.credit_money+t_data.gift_money) <> 0) then (t_data.credit_money+t_data.gift_money - t_data.iitem - t_data.itax - t_data.hitem - t_data.htax) - round(t_data.utax*(t_data.credit_money+t_data.gift_money - t_data.iitem - t_data.itax - t_data.hitem - t_data.htax ) / NULLIF(t_data.uitem + t_data.utax,0))  ";	
            $sql  .= " 			 else (t_data.credit_money+t_data.gift_money)  - round(t_data.utax*(t_data.credit_money+t_data.gift_money)/ NULLIF(t_data.uitem + t_data.utax,0))  ";	
            $sql  .= " 			 end ) as kake_uitem_calc ";	
                                         //計算式 四捨五入 [取引外税額 * (クレジット + ギフト[売掛外税対象金額]) / 取引外税商品代金 ] = 売掛外税 税額
            $sql  .= " 			,sum( case when (t_data.uitem <> 0 and(t_data.iitem <> 0 or t_data.hitem <> 0) and (t_data.credit_money+t_data.gift_money) <> 0) then round(t_data.utax*(t_data.credit_money+t_data.gift_money - t_data.iitem - t_data.itax - t_data.hitem - t_data.htax ) / NULLIF(t_data.uitem + t_data.utax,0)) ";	
            $sql  .= " 			 else round(t_data.utax*(t_data.credit_money+t_data.gift_money)/ NULLIF(t_data.uitem + t_data.utax,0)) ";	
            $sql  .= " 			 end ) as kake_utax_calc ";	
                                         //計算式 売掛内税 対象金額 = (クレジット + ギフト)[売掛内税対象金額] - [売掛内税 税額] 
            $sql  .= " 			,sum( case when (t_data.iitem <> 0 and (t_data.uitem <> 0 or t_data.hitem <> 0) and (t_data.credit_money+t_data.gift_money) <> 0) then (t_data.credit_money+t_data.gift_money - t_data.uitem - t_data.utax - t_data.hitem - t_data.htax) - round(t_data.itax*(t_data.credit_money+t_data.gift_money - t_data.uitem - t_data.utax - t_data.hitem - t_data.htax ) / NULLIF(t_data.iitem + t_data.itax,0))  ";	
            $sql  .= " 			 else (t_data.credit_money+t_data.gift_money)  - round(t_data.itax*(t_data.credit_money+t_data.gift_money)/ NULLIF(t_data.iitem + t_data.itax,0))  ";	
            $sql  .= " 			 end ) as kake_iitem_calc";	
                                         //計算式 四捨五入 [取引内税額 * (クレジット + ギフト[売掛内税対象金額]) / 取引内税商品代金 ] = 売掛内税 税額
            $sql  .= " 			,sum( case when (t_data.iitem <> 0 and(t_data.uitem <> 0 or t_data.hitem <> 0) and (t_data.credit_money+t_data.gift_money) <> 0) then round(t_data.itax*(t_data.credit_money+t_data.gift_money - t_data.uitem - t_data.utax - t_data.hitem - t_data.htax ) / NULLIF(t_data.iitem + t_data.itax,0)) ";	
            $sql  .= " 			 else round(t_data.itax*(t_data.credit_money+t_data.gift_money) / NULLIF(t_data.iitem + t_data.itax,0)) ";	
            $sql  .= " 			 end ) as kake_itax_calc ";	
                                         //計算式 売掛非課税 対象金額 = (クレジット + ギフト)[売掛非課税対象金額] - [売掛非課税 税額] 
            $sql  .= " 			,sum( case when (t_data.hitem <> 0 and (t_data.uitem <> 0 or t_data.iitem <> 0) and (t_data.credit_money+t_data.gift_money) <> 0) then (t_data.credit_money+t_data.gift_money - t_data.uitem - t_data.utax - t_data.iitem - t_data.itax) - round(t_data.htax*(t_data.credit_money+t_data.gift_money - t_data.uitem - t_data.utax - t_data.iitem - t_data.itax ) / NULLIF(t_data.hitem + t_data.htax,0))  ";	
            $sql  .= " 			 else (t_data.credit_money+t_data.gift_money)  - round(t_data.htax*(t_data.credit_money+t_data.gift_money) / NULLIF(t_data.hitem + t_data.htax,0)) ";	
            $sql  .= " 			 end ) as kake_hitem_calc ";	
                                         //計算式 四捨五入 [取引非課税 * (クレジット + ギフト[売掛非課税対象金額]) / 取引非課税商品代金 ] = 売掛非課税 税額
            $sql  .= " 			,sum( case when (t_data.hitem <> 0 and(t_data.uitem <> 0 or t_data.iitem <> 0) and (t_data.credit_money+t_data.gift_money) <> 0	) then round(t_data.htax*(t_data.credit_money+t_data.gift_money - t_data.uitem - t_data.utax - t_data.iitem - t_data.itax ) / NULLIF(t_data.hitem + t_data.htax,0)) ";	
            $sql  .= " 			 else round(t_data.htax*(t_data.credit_money+t_data.gift_money) / NULLIF(t_data.hitem + t_data.htax,0)) ";	
            $sql  .= " 			 end ) as kake_htax_calc ";	

                                         //計算式 (現金外税対象金額 - 現金の外税 税額 = 現金の外税 対象金額
            $sql  .= " 			,sum( case when (t_data.uitem <> 0 and (t_data.iitem <> 0 or t_data.hitem <> 0) and (t_data.genkin + t_data.point_money) <> 0) then (t_data.genkin + t_data.point_money - t_data.iitem - t_data.itax - t_data.hitem - t_data.htax ) - round(t_data.utax*(t_data.genkin + t_data.point_money  - t_data.iitem - t_data.itax  - t_data.hitem - t_data.htax ) / NULLIF(t_data.uitem + t_data.utax,0))  ";	
            $sql  .= " 			 else t_data.genkin + t_data.point_money- round(t_data.utax * (t_data.genkin + t_data.point_money ) / NULLIF(t_data.uitem + t_data.utax,0))  ";	
            $sql  .= " 			 end ) as genkin_uitem_calc ";	
                                         //計算式 四捨五入 [取引外税 * (現金 + ポイント[現金外税対象金額]) / 取引外税商品代金 ] = 現金外税 税額
            $sql  .= " 			,sum( case when (t_data.uitem <> 0 and(t_data.iitem <> 0 or t_data.hitem <> 0) and (t_data.genkin + t_data.point_money) <> 0) then round(t_data.utax*(t_data.genkin + t_data.point_money - t_data.iitem - t_data.itax - t_data.hitem - t_data.htax ) / NULLIF(t_data.uitem + t_data.utax,0))";	
            $sql  .= " 			 else round(t_data.utax*(t_data.genkin + t_data.point_money) / NULLIF(t_data.uitem + t_data.utax,0)) ";	
            $sql  .= " 			 end ) as genkin_utax_calc ";	
                                         //計算式 (現金内税対象金額 - 現金の内税 税額 = 現金の内税 対象金額
            $sql  .= " 			,sum( case when (t_data.iitem <> 0 and(t_data.uitem <> 0 or t_data.hitem <> 0) and (t_data.genkin + t_data.point_money) <> 0) then (t_data.genkin + t_data.point_money - t_data.uitem - t_data.utax - t_data.hitem - t_data.htax) - round(t_data.itax*(t_data.genkin + t_data.point_money - t_data.uitem - t_data.utax - t_data.hitem - t_data.htax) / NULLIF(t_data.iitem + t_data.itax,0))  ";	
            $sql  .= " 			 else t_data.genkin + t_data.point_money- round(t_data.itax * (t_data.genkin + t_data.point_money ) / NULLIF(t_data.iitem + t_data.itax,0))  ";	
            $sql  .= " 			 end ) as genkin_iitem_calc ";	
                                         //計算式 四捨五入 [取引内税 * (現金 + ポイント[現金内税対象金額]) / 取引内税商品代金 ] = 現金内税 税額
            $sql  .= " 			,sum( case when (t_data.iitem <> 0 and (t_data.uitem <> 0 or t_data.hitem <> 0) and (t_data.genkin + t_data.point_money) <> 0) then round(t_data.itax*(t_data.genkin + t_data.point_money - t_data.uitem  - t_data.utax - t_data.hitem  - t_data.htax) / NULLIF(t_data.iitem + t_data.itax,0))";	
            $sql  .= " 			 else round(t_data.itax*(t_data.genkin + t_data.point_money) / NULLIF(t_data.iitem + t_data.itax,0))";	
            $sql  .= " 			 end ) as genkin_itax_calc ";	
                                         //計算式 (現金非課税対象金額 - 現金の非課税 税額 = 現金の非課税 対象金額
            $sql  .= " 			,sum( case when (t_data.hitem <> 0 and(t_data.uitem <> 0 or t_data.iitem <> 0) and (t_data.genkin + t_data.point_money) <> 0) then (t_data.genkin + t_data.point_money - t_data.uitem - t_data.utax - t_data.iitem - t_data.itax) - round(t_data.htax*(t_data.genkin + t_data.point_money - t_data.uitem - t_data.utax - t_data.iitem - t_data.itax) / NULLIF(t_data.hitem + t_data.htax,0))  ";	
            $sql  .= " 			 else t_data.genkin + t_data.point_money - round(t_data.htax*(t_data.genkin + t_data.point_money) / NULLIF(t_data.hitem + t_data.htax,0))  ";	
            $sql  .= " 			 end ) as genkin_hitem_calc ";	
                                         //計算式 四捨五入 [取引非課税 * (現金 + ポイント[現金非課税対象金額]) / 取引非課税商品代金 ] = 現金非課税 税額
            $sql  .= " 			,sum( case when (t_data.hitem <> 0 and (t_data.uitem <> 0 or t_data.iitem <> 0) and (t_data.genkin + t_data.point_money) <> 0) then round(t_data.htax*(t_data.genkin + t_data.point_money - t_data.uitem  - t_data.utax - t_data.iitem  - t_data.itax) / NULLIF(t_data.hitem + t_data.htax,0)) ";	
            $sql  .= " 			 else round(t_data.htax*(t_data.genkin + t_data.point_money) / NULLIF(t_data.hitem + t_data.htax,0)) ";	
            $sql  .= " 			 end ) as genkin_htax_calc ";	
			$sql  .= " 	,sum(t_data.point_cnt) as point_cnt ";
            $sql  .= " 		from ( ";	
            $sql  .= " 			select  ";	
            $sql  .= " 				   ";	
            $sql  .= " 				  case 	when (azukari <> 0   and turisen >= 0)  or ( azukari<0 and turisen <0) then genkin ";	
            $sql  .= " 				  else 0 ";	
            $sql  .= " 				  end as genkin ";
			$sql  .= " 				, case 	when (azukari <> 0   and turisen >= 0)  or ( azukari<0 and turisen <0) then case when genkin < 0 then -1 else 1 end" ;	
            $sql  .= " 				  else 0 ";	
            $sql  .= " 				  end as genkin_cnt ";
            $sql  .= " 				, case when point_money < 0 then -1 when point_money > 0 then 1 else 0 end as point_cnt ";				
            $sql  .= " 				, credit_money ";	
            $sql  .= " 				, gift_money ";	
            $sql  .= " 				, waribiki ";	
            $sql  .= " 				, point_money ";	
            $sql  .= " 				, t2.pure_total_i as tot_pure_total_i ";	
            $sql  .= " 				, t1.utax + t1.itax as tot_itax ";	
            $sql  .= " 				, t_uitem as uitem ";	
            $sql  .= " 				, t1.utax as utax ";	
            $sql  .= " 				, t_iitem as iitem ";	
            $sql  .= " 				, t1.itax as itax ";	
            $sql  .= " 				, t_hitem as hitem ";	
            $sql  .= " 				, t_htax as htax ";	
// 20200211 oota END
            $sql  .= " 			from trn0203 ";	
            $sql  .= " 			left join trn0101 t1 using (organization_id,hideseq) ";	
            $sql  .= " 			left join ( ";	
            $sql  .= " 					   select 			    ";	
            $sql  .= " 							 t2.organization_id ";	
            $sql  .= " 							,t2.hideseq ";	
            $sql  .= " 							,sum(t2.pure_total_i) as pure_total_i ";	
            $sql  .= " 							,sum(t2.itax) as itax ";	
            $sql  .= " 					   		,sum(case  ";	
            $sql  .= " 					   			when t2.tax_type = '1' then t2.pure_total_i ";	
            $sql  .= " 					   			else 0 ";	
            $sql  .= " 					   		 end) as t_uitem ";	
            $sql  .= " 					   		,sum(case  ";	
            $sql  .= " 					   			when t2.tax_type = '1' then t2.itax ";	
            $sql  .= " 					   			else 0 ";	
            $sql  .= " 					   		 end) as t_utax ";	
            $sql  .= " 					   		,sum(case  ";	
            $sql  .= " 					   			when t2.tax_type = '2' then t2.pure_total_i ";	
            $sql  .= " 					   			else 0 ";	
            $sql  .= " 					   		 end) as t_iitem ";	
            $sql  .= " 					   		,sum(case  ";	
            $sql  .= " 					   			when t2.tax_type = '2' then t2.itax ";	
            $sql  .= " 					   			else 0 ";	
            $sql  .= " 					   		 end) as t_itax ";	
            $sql  .= " 					   		,sum(case  ";	
            $sql  .= " 					   			when t2.tax_type = '9' then t2.pure_total_i ";	
            $sql  .= " 					   			else 0 ";	
            $sql  .= " 					   		 end) as t_hitem ";	
            $sql  .= " 					   		,sum(case  ";	
            $sql  .= " 					   			when t2.tax_type = '9' then t2.itax ";	
            $sql  .= " 					   			else 0 ";	
            $sql  .= " 					   		 end) as t_htax		   		  ";	
            $sql  .= " 					   from trn0102 t2 ";	
            $sql  .= " 					   left join trn0101 t1 using (organization_id,hideseq) ";	
            $sql  .= " 					   where cancel_kbn = '0' and stop_kbn = '0' ";	
            $sql  .= " 					   group by organization_id,hideseq		    ";	
            $sql  .= " 					  ) t2 using (organization_id,hideseq) ";	
            $sql  .= " 			where ";	
            $sql  .= " 					proc_date between :start_date and :end_date ";	
            // 店舗条件
            if($_POST['org_id'] !== 'false'){
                if( $_POST['org_select'] === 'empty'){
                    $sql .= " and organization_id in (".$_POST['org_id'].")";
                }else{
                    $sql .= " and organization_id in (".$_POST['org_select'].")";
                }
            }
			
            $sql  .= " 		) T_data ";	


// 20200207 montagne end

            
            $searchArray[':start_date'] = '';
            $searchArray[':end_date']   = '';
            if($_POST['start_date']){
                $searchArray[':start_date'] = str_replace('/','',$_POST['start_date']);
            }
            if($_POST['end_date']){
                $searchArray[':end_date']   = str_replace('/','',$_POST['end_date']);
            }
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_tax_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
// 20200127 montage  START					
                    //$Datas[$data['types']] = $data;
					$Datas = $data;
// 20200127 montage  END					
            }

            $Log->trace("END get_tax_data");
            return $Datas;
        }         
        
        /**
         * クレジットデータ (売上)
         * @param    $postArray   入力パラメータ(start_date/end_date)
         * @return  
         */        
        public function get_gift_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_credit_all_data");
            
            $sql  = "";
            $sql  .= " select  ";
            $sql  .= " 	coalesce(sum(sale_cust_cnt+sale_not_cust_cnt),0) as sale_cnt, ";
            $sql  .= " 	coalesce(sum(gift_money),0) as gift_money ";
            $sql  .= " from jsk1180 ";
            $sql  .= " where  ";
            $sql  .= " 		gift_certi_cd <> 'ZZ' ";
            $sql  .= " 	and proc_date between :start_date and :end_date ";
            // 店舗条件
            if($_POST['org_id'] !== 'false'){
                if( $_POST['org_select'] === 'empty'){
                    $sql .= " and organization_id in (".$_POST['org_id'].")";
                }else{
                    $sql .= " and organization_id in (".$_POST['org_select'].")";
                }
            }
            
            $searchArray[':start_date'] = '';
            $searchArray[':end_date']   = '';
            if($_POST['start_date']){
                $searchArray[':start_date'] = str_replace('/','',$_POST['start_date']);
            }
            if($_POST['end_date']){
                $searchArray[':end_date']   = str_replace('/','',$_POST['end_date']);
            }
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_credit_all_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas = $data;
            }

            $Log->trace("END get_credit_all_data");

            return $Datas;
        }
        /**
         * 商品券件数　　（モンターニュ）
         */        
        public function get_gift_cnt_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_gift_cnt_data");
            
            $sql  = "";
			//EDTSTR montagne 2020/02/04
            //$sql  .= " select count(*) from trn0101 where return_amount < 0 and stop_kbn = '0' ";
			$sql  .= " select count(*),sum(-1*abs(return_total)) as return_total from trn0101 where return_amount <> 0 and stop_kbn = '0' ";
			//EDTSTR montagne 2020/02/04
            $sql  .= " 	and proc_date between :start_date and :end_date ";
            // 店舗条件
            if($_POST['org_id'] !== 'false'){
                if( $_POST['org_select'] === 'empty'){
                    $sql .= " and organization_id in (".$_POST['org_id'].")";
                }else{
                    $sql .= " and organization_id in (".$_POST['org_select'].")";
                }
            }
            
            $searchArray[':start_date'] = '';
            $searchArray[':end_date']   = '';
            if($_POST['start_date']){
                $searchArray[':start_date'] = str_replace('/','',$_POST['start_date']);
            }
            if($_POST['end_date']){
                $searchArray[':end_date']   = str_replace('/','',$_POST['end_date']);
            }
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_gift_cnt_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas = $data;
            }

            $Log->trace("END get_gift_cnt_data");

            return $Datas;
        }
        /**
         * クレジットデータ (売上)
         * @param    $postArray   入力パラメータ(start_date/end_date)
         * @return  
         */        
        public function get_trn0221_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_trn0221_data");
            
            $sql  = "";
            $sql  .= " select ";
            $sql  .= " 	coalesce(sum( case when acct_in_out_cd = '01'  and acct_in_out_kbn  = '1' then case when acct_in_out_money < 0 then -1 else 1 end else 0 end ),0) as in_cnt_0 , ";
            $sql  .= " 	coalesce(sum( case when acct_in_out_cd = '01'  and acct_in_out_kbn  = '1' then acct_in_out_money else 0 end ),0) as in_tot_0 , ";
            $sql  .= " 	coalesce(sum( case when acct_in_out_cd <> '01' and acct_in_out_kbn  = '1' then case when acct_in_out_money < 0 then -1 else 1 end  else 0 end ),0) as in_cnt_1 , ";
            $sql  .= " 	coalesce(sum( case when acct_in_out_cd <> '01' and acct_in_out_kbn  = '1' then acct_in_out_money else 0 end ),0) as in_tot_1, ";
            $sql  .= " 	coalesce(sum( case when acct_in_out_cd = '01'  and acct_in_out_kbn <> '1' then case when acct_in_out_money < 0 then -1 else 1 end  else 0 end ),0) as out_cnt_0 , ";
            $sql  .= " 	coalesce(sum( case when acct_in_out_cd = '01'  and acct_in_out_kbn <> '1' then acct_in_out_money else 0 end ),0) as out_tot_0 , ";
            $sql  .= " 	coalesce(sum( case when acct_in_out_cd <> '01' and acct_in_out_kbn <> '1' then case when acct_in_out_money < 0 then -1 else 1 end  else 0 end ),0) as out_cnt_1 , ";
            $sql  .= " 	coalesce(sum( case when acct_in_out_cd <> '01' and acct_in_out_kbn <> '1' then acct_in_out_money else 0 end ),0) as out_tot_1	 ";
            $sql  .= " from ";
            $sql  .= " 	trn0221 left join trn0101 using (organization_id,hideseq) ";
            $sql  .= " where ";
            $sql  .= " 		proc_date between :start_date and :end_date ";
			$sql  .= "  and stop_kbn = '0' ";
            // 店舗条件
            if($_POST['org_id'] !== 'false'){
                if( $_POST['org_select'] === 'empty'){
                    $sql .= " and trn0221.organization_id in (".$_POST['org_id'].")";
                }else{
                    $sql .= " and trn0221.organization_id in (".$_POST['org_select'].")";
                }
            }
            
            $searchArray[':start_date'] = '';
            $searchArray[':end_date']   = '';
            if($_POST['start_date']){
                $searchArray[':start_date'] = str_replace('/','',$_POST['start_date']);
            }
            if($_POST['end_date']){
                $searchArray[':end_date']   = str_replace('/','',$_POST['end_date']);
            }
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_trn0221_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas = $data;
            }

            $Log->trace("END get_trn0221_data");

            return $Datas;
        } 

        /**
         * クレジットデータ (売上)
         * @param    $postArray   入力パラメータ(start_date/end_date)
         * @return  
         */        
        public function get_trn0301_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_trn0301_data");
            
            $sql   = "";
            $sql  .= " 		select ";	
            $sql  .= " 			rece_kbn, ";	
            $sql  .= " 			sum(rece_total) as rece_total, ";	
            $sql  .= " 			sum(case when rece_total < 0 then -1 else 1 end) as rece_cnt ";
            $sql  .= " 		from ";	
            $sql  .= " 			trn0301 t ";	
            $sql  .= " 		left join trn0101 ";	
            $sql  .= " 				using(organization_id, ";	
            $sql  .= " 			hideseq) ";	
            $sql  .= " 		where ";
//EDITSTR 2020/04/20 kanderu
           // $sql  .= " 			proc_date between :start_date and :end_date ";
            $sql  .= " 			date(t.insdatetime) between :start_date and :end_date ";
//EDITEND 2020/04/20 kanderu            
            $sql  .= " 			and stop_kbn = '0' ";	
            $sql  .= " 			and rece_total <> 0 ";	
            // 店舗条件
            if($_POST['org_id'] !== 'false'){
                if( $_POST['org_select'] === 'empty'){
                    $sql .= " and organization_id in (".$_POST['org_id'].")";
                }else{
                    $sql .= " and organization_id in (".$_POST['org_select'].")";
                }
            }			
            $sql  .= " 		group by ";	
            $sql  .= " 			rece_kbn ";
            $sql  .= " 		order by ";	
            $sql  .= " 			rece_kbn ";	
            $searchArray[':start_date'] = '';
            $searchArray[':end_date']   = '';
            if($_POST['start_date']){
                $searchArray[':start_date'] = str_replace('/','',$_POST['start_date']);
            }
            if($_POST['end_date']){
                $searchArray[':end_date']   = str_replace('/','',$_POST['end_date']);
            }
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_trn0301_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas[$data['rece_kbn']] = $data;
            }

            $Log->trace("END get_trn0301_data");
            return $Datas;
        } 
	        /**
         * 値引/割引件数
         * @param    $postArray   入力パラメータ(start_date/end_date)
         * @return  
         */        
        public function get_count_discount_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_count_discount_data");
            
            $sql   = "";	
            $sql  .= " 		select  ";	
            $sql  .= " 			coalesce (sum(case  ";	
            $sql  .= " 				when waribiki = 0 then 0 ";																					//割引。
            $sql  .= " 				when waribiki < 0 then -1 ";	
            $sql  .= " 				else 1 ";	
            $sql  .= " 			end) + ";	
            $sql  .= " 			sum(case  ";	
            $sql  .= " 				when mod_nebiki = 0 then 0 ";																				//修正値引。
            $sql  .= " 				when mod_nebiki < 0 then -1 ";	
            $sql  .= " 				else 1 ";	
            $sql  .= " 			end) + ";	
            $sql  .= " 			sum(case  ";	
            $sql  .= " 				when nebiki = 0 then 0 ";																					//値引。
            $sql  .= " 				when nebiki < 0 then -1 ";	
            $sql  .= " 				else 1 ";	
            $sql  .= " 			end) + ";	
            $sql  .= " 			sum(case  ";	
            $sql  .= " 				when (prev_org_id = t2.organization_id and prev_hideseq = t2.hideseq) or s_mixmatch_nebiki = 0 then 0 ";	//按分ミックスマッチ値引。前店舗コードと前レコード番号が今店舗コードと今レコード番号と同じ時、件数は0。
            $sql  .= " 				when s_mixmatch_nebiki < 0 then -1 ";	
            $sql  .= " 				else 1 ";	
            $sql  .= " 			end) + ";	
            $sql  .= " 			sum(case  ";	
            $sql  .= " 				when (prev_org_id = t2.organization_id and prev_hideseq = t2.hideseq) or s_nebiki = 0 then 0 ";				//按分取引値引。前店舗コードと前レコード番号が今店舗コードと今レコード番号と同じ時、件数は0。
            $sql  .= " 				when s_nebiki < 0 then -1 ";	
            $sql  .= " 				else 1 ";	
            $sql  .= " 			end) + ";	
            $sql  .= " 			sum(case  ";	
            $sql  .= " 				when (prev_org_id = t2.organization_id and prev_hideseq = t2.hideseq) or s_waribiki = 0 then 0 ";			//按分取引割引。前店舗コードと前レコード番号が今店舗コードと今レコード番号と同じ時、件数は0。
            $sql  .= " 				when s_waribiki < 0 then -1 ";	
            $sql  .= " 				else 1 ";	
            $sql  .= " 			end) + ";	
            $sql  .= " 			sum(case  ";	
            $sql  .= " 				when (prev_org_id = t2.organization_id and prev_hideseq = t2.hideseq) or s_tyoseibiki = 0 then 0 ";			//按分調整引。前店舗コードと前レコード番号が今店舗コードと今レコード番号と同じ時、件数は0。
            $sql  .= " 				when s_tyoseibiki < 0 then -1 ";	
            $sql  .= " 				else 1 ";	
            $sql  .= " 			end),0) 	 ";	
            $sql  .= " 			as count_discount ";	
            $sql  .= " 		from (select 		 ";	
            $sql  .= " 				lag(organization_id ) OVER(ORDER BY organization_id,hideseq,line_no) prev_org_id, ";						//前店舗コード。
            $sql  .= " 				lag(hideseq ) OVER(ORDER BY organization_id,hideseq,line_no) prev_hideseq, ";								//前レコード番号。
            $sql  .= " 				trn0102.* from trn0102 where cancel_kbn = '0')t2 ";	
            $sql  .= " 		left join trn0101 t1 using(organization_id,hideseq) ";	
            $sql  .= " 		where  ";	
            $sql  .= " 				proc_date between :start_date and :end_date ";	
            $sql  .= " 			and stop_kbn  = '0'		 ";	
            // 店舗条件
            if($_POST['org_id'] !== 'false'){
                if( $_POST['org_select'] === 'empty'){
                    $sql .= " and organization_id in (".$_POST['org_id'].")";
                }else{
                    $sql .= " and organization_id in (".$_POST['org_select'].")";
                }
            }				
           $searchArray[':start_date'] = '';
            $searchArray[':end_date']   = '';
            if($_POST['start_date']){
                $searchArray[':start_date'] = str_replace('/','',$_POST['start_date']);
            }
            if($_POST['end_date']){
                $searchArray[':end_date']   = str_replace('/','',$_POST['end_date']);
            }
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_count_discount_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas = $data;
            }

            $Log->trace("END get_count_discount_data");
            return $Datas;
        } 			
    }
?>
