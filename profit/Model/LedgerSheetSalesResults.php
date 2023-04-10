<?php
    /**
     * @file      帳票 - コスト
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - コストの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetSalesResults extends BaseModel
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

        public function get_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_data");
/*            
            $sql  = "";
            $sql .= " select j.proc_date " ;
            $sql .= " 	,j.organization_id  as org_id " ;
            $sql .= " 	,o.abbreviated_name as org_nm" ;
            $sql .= " 	,sum(j.pure_total + j.total_utax) as res1 " ;
            $sql .= " 	,sum(j.pure_total_i) as pure_total_i " ;
            $sql .= " 	,sum(j.total_itax + j.total_utax) as res2 " ;
            $sql .= " 	,sum(j.total_profit_i) as total_profit_i " ;
            $sql .= " 	,round(CASE sum(j.pure_total_i) " ;
            $sql .= " 		when 0 then 0 " ;
            $sql .= " 		else round(sum(j.total_profit_i)/sum(j.pure_total_i)*100,2) " ;
            $sql .= " 	end,2) as res3 " ;
            $sql .= " 	,sum(j.total_cnt) as total_cnt " ;
            $sql .= " 	,sum(j.total_amount) as total_amount " ;
            $sql .= " 	,round(CASE sum(j.total_cnt) " ;
            $sql .= " 		when 0 then 0 " ;
            $sql .= " 		else round(sum(j.pure_total_i)/sum(j.total_cnt),2) " ;
            $sql .= " 	end,2) as res4  " ;
            $sql .= " 	,round(CASE sum(j.total_cnt) " ;
            $sql .= " 		when 0 then 0 " ;
            $sql .= " 		else round(sum(j.total_amount)/sum(j.total_cnt),2) " ;
            $sql .= " 	end,2) as res5	 " ;
            $sql .= " 	,round(CASE sum(j.total_amount) " ;
            $sql .= " 		when 0 then 0 " ;
            $sql .= " 		else round(sum(j.pure_total_i)/sum(j.total_amount),2) " ;
            $sql .= " 	end,2) as res6	 " ;
            $sql .= " from jsk1010 j " ;
            $sql .= " left join m_organization_detail o on (o.organization_id = j.organization_id) " ;
            $sql .= " where 1 = :val " ;
            $sql .= " group by j.proc_date, j.organization_id, o.abbreviated_name " ;
            $sql .= " order by proc_date,org_id " ;
 */
            $sql .= " select ";
            $sql .= " 	*, ";
            $sql .= " 	res2 - tax_total_08 as tax_total_10 ";
            $sql .= " from (select ";
            $sql .= " 		a.proc_date , ";
            $sql .= " 		a.organization_id as org_id , ";
            $sql .= " 		o.abbreviated_name as org_nm , ";
            $sql .= " 		sum(a.pure_total + a.total_utax) as res1 , ";
//stadd montagne 20200131			
            //$sql .= " 		sum(a.pure_total_i) as pure_total_i , ";
			$sql .= " 		sum(a.pure_total - a.total_itax) as pure_total_i , ";
//endadd montagne 20200131				
            $sql .= " 		sum(a.total_itax + a.total_utax) as res2 , ";
            $sql .= " 		sum(a.total_profit_i) as total_profit_i , ";
            $sql .= " 		round(case sum(a.pure_total_i) when 0 then 0 else round(sum(a.total_profit_i)/ sum(a.pure_total_i)* 100, 2) end, 2) as res3 , ";
            $sql .= " 		sum(a.total_cnt) as total_cnt , ";
            $sql .= " 		sum(a.total_amount) as total_amount , ";
            $sql .= " 		round(case sum(a.total_cnt) when 0 then 0 else round(sum(a.pure_total_i)/ sum(a.total_cnt), 2) end, 2) as res4 , ";
            $sql .= " 		round(case sum(a.total_cnt) when 0 then 0 else round(sum(a.total_amount)/ sum(a.total_cnt), 2) end, 2) as res5 , ";
            $sql .= " 		round(case sum(a.total_amount) when 0 then 0 else round(sum(a.pure_total_i)/ sum(a.total_amount), 2) end, 2) as res6, ";
            $sql .= " 		sum(coalesce(t.tax_08, 0)) as tax_08_trn , ";
            $sql .= " 		sum(coalesce(t.tax_10, 0)) as tax_10_trn,  ";
            $sql .= " 		case ";
            $sql .= " 			when sum(coalesce(t.tax_08, 0))+ sum(coalesce(t.tax_10, 0)) = 0 then sum(total_utax + total_itax) ";
            $sql .= " 			else cast(sum(total_utax + total_itax)* sum(coalesce(t.tax_08, 0))/(sum(coalesce(t.tax_08, 0))+ sum(coalesce(t.tax_10, 0))) as int) ";
            $sql .= " 		end as tax_total_08		 ";
            $sql .= " 	from ";
            $sql .= " 		(select ";
            $sql .= " 			organization_id, ";
            $sql .= " 			reji_no, ";
            $sql .= " 			proc_date, ";
            $sql .= " 			sum(pure_total) as pure_total, ";
            $sql .= " 			sum(pure_total_i) as pure_total_i, ";
            $sql .= " 			sum(total_profit_i) as total_profit_i, ";
            $sql .= " 			sum(total_amount) as total_amount, ";
            $sql .= " 			sum(total_cnt) as total_cnt, ";
            $sql .= " 			sum(perc_total) as perc_total, ";
            $sql .= " 			sum(disc_total) as disc_total, ";
            $sql .= " 			sum(adjust_total) as adjust_total, ";
            $sql .= " 			sum(perc_item_total) as perc_item_total, ";
            $sql .= " 			sum(disc_item_total) as disc_item_total, ";
            $sql .= " 			sum(disc_item_total_e) as disc_item_total_e, ";
            $sql .= " 			sum(bundle_total) as bundle_total, ";
            $sql .= " 			sum(mixmatch_total) as mixmatch_total, ";
            $sql .= " 			sum(total_utax) as total_utax, ";
            $sql .= " 			sum(total_itax) as total_itax ";
            $sql .= " 		from ";
            $sql .= " 			jsk1010 ";
            $sql .= " 		group by ";
            $sql .= " 			organization_id, ";
            $sql .= " 			reji_no, ";
            $sql .= " 			proc_date) a ";
            $sql .= " 	left join m_organization_detail o on ";
            $sql .= " 		(o.organization_id = a.organization_id) ";
            $sql .= " 	left join ( ";
            $sql .= " 		select ";
            $sql .= " 			t2.organization_id, ";
            $sql .= " 			t1.proc_date, ";
            $sql .= " 			t2.reji_no, ";
            $sql .= " 			count(distinct (t1.trndate)) as t_day, ";
            $sql .= " 			sum(t2.pure_total) as t2_pure_total_i, ";
            $sql .= " 			sum( case when t2.tax_rate = 8 then t2.tax_total else 0 end) as tax_08, ";
            $sql .= " 			sum( case when t2.tax_rate = 10 then t2.tax_total else 0 end) as tax_10 ";
            $sql .= " 		from ";
            $sql .= " 			trn0103 t2 ";
            $sql .= " 		left join trn0101 t1 on ";
            $sql .= " 			t1.hideseq = t2.hideseq ";
            $sql .= " 			and t1.organization_id = t2.organization_id ";
            $sql .= " 		where ";
            $sql .= " 			t1.stop_kbn = '0' ";
            $sql .= " 		group by ";
            $sql .= " 			t2.organization_id, ";
            $sql .= " 			t1.proc_date, ";
            $sql .= " 			t2.reji_no ";
            $sql .= " 		order by ";
            $sql .= " 			organization_id, ";
            $sql .= " 			proc_date, ";
            $sql .= " 			reji_no ) t on ";
            $sql .= " 		t.proc_date = a.proc_date ";
            $sql .= " 		and a.reji_no = t.reji_no ";
            $sql .= " 		and t.organization_id = a.organization_id		 ";
            $sql .= " 	where ";
            $sql .= " 		1 = :val ";
            $sql .= " 	group by ";
            $sql .= " 		a.proc_date, ";
            $sql .= " 		a.organization_id, ";
            $sql .= " 		o.abbreviated_name ";
            $sql .= " 	order by ";
            $sql .= " 		proc_date, ";
            $sql .= " 		org_id ";
            $sql .= " 	)full_data ";
            $searchArray[':val'] = 1 ;
            //print_r($sql);
            //print_r($searchArray);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];
            //print_r($result);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    //print_r($data);
                    $Datas[] = $data;
            }

            $Log->trace("END get_data");

            return $Datas;
        }
        
    }
?>
