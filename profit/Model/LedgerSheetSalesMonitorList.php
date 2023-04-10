<?php
    /**
     * @file      帳票 - 売上モニターリスト
     * @author    millionet bhattarai
     * @date      2020/02/05
     * @version   1.00
     * @note      帳票 - 売上モニターリストの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetSalesMonitorList extends BaseModel
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
        public function get_data($joken)
       {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_data");
            //print_r($joken);
            $searchArray = array();
            
            $where  = "";
            //検索条件店舗コード
            if( $joken['org_id'] !== 'false')
            {
                if( $joken['org_select'] === 'empty'){
                    $where .= " AND t2.organization_id in (".$joken['org_id'].") ";
                }else{
                    $where .= " AND t2.organization_id in ('".$joken['org_select']."') ";
                }
            }
            //検索条件顧客範囲
            if( $joken['cust_cd'] !== 'false' ){
                $where .= " AND t1.cust_cd between ('".$joken['cust_select']."') and ('".$joken['cust_select_1']."') ";
            }    
            /*修正日：20200316　修正者：バッタライ
　　　　　　　修正内容：支払方法検索条件を移動*
            //検索条件支払い方法
            if( $joken['credit_cd'] !== 'false' )
            {
                if( $joken['credit_select'] === 'empty'){
                    $where .= " AND credit_cd in (".$joken['credit_cd'].") ";
                }else{
                    $where .= " AND credit_cd in ('".$joken['credit_select']."') ";
                }
            }*/
            //検索条件取引種別
            if($joken['uriage_kbn']&&!$joken['henpin_kbn']){
                $where .= " AND t2.amount >= 0 ";
            }elseif($joken['henpin_kbn']&&!$joken['uriage_kbn']){
                $where .= " AND t2.amount < 0 ";
            }   
            
            $sql .= "select ";
            $sql .= "	t1.proc_date, ";//日付
            $sql .= "	t1.organization_id as org_id, ";//店舗番号
            $sql .= "	v.abbreviated_name as org_nm, ";//店舗名
            //EDITSTR 2020/04/17 kanderu
            // $sql .= "	t1.hideseq as den_no, ";//伝票番号
            $sql .= "  concat(t1.reji_no||' - '||t1.account_no) as den_no,";//伝票番号
            //EDITEND 2020/04/17 kanderu
            $sql .= "	t1.cust_cd, ";//顧客コード
            $sql .= "	t1.cust_nm, ";//顧客名
            $sql .= "	t2.prod_cd, ";//商品コード
            $sql .= "	t1.staff_nm, ";//担当者名
            $sql .= "	t1.staff_cd, ";//担当者コード
            $sql .= "	case when t4.maker_nm is null then t2.prod_nm_dsp else t4.maker_nm||' ・ '||t2.prod_nm_dsp end as prod_nm, ";//メーカー名+商品名
            $sql .= "	case when t6.genkin <> '0' then '現金・' else '' end as genkin_nm, ";//支払種別で現金を表示
            $sql .= "	case when t6.point_money <> '0' then 'ポイント金額・' else '' end as point_nm, ";//支払種別でポイント金額を表示
            $sql .= "	case when t6.gift_money <> '0' then '商品券金額・' else '' end as gift_nm, ";//支払種別で商品券金額を表示
            $sql .= "	case when t6.credit_money <> '0' then t3.credit_nm else ''   end as credit_nm, ";//支払種別でクレジット名を表示
            //$sql .= "	t3.credit_nm as credit_nm, ";//支払種別でクレジット名を表示
            //$sql .= "	t3.credit_cd, ";//クレジットカード番号
            $sql .= "	t2.saleprice as unit_price, ";//税抜き単価            
            $sql .= "	t2.amount, ";//数量  
            $sql .= "	case ";
            $sql .= "		when (t2.amount < 0) then '返品' ";//数量がゼロより小さい場合は返品表示
            $sql .= "		else '売上' end as torihiki_kbn, ";//数量が一1以上の場合は売上表示
            //$sql .= "	case ";
            //修正日：2020/02/26　修正者：バッタライ　修正内容：税金金額表示
            //$sql .= "		when (t2.waribiki + t2.nebiki + t2.mod_nebiki + t2.s_tyoseibiki + t2.s_mixmatch_nebiki + t2.s_nebiki + t2.s_waribiki) <> '0' ";//単品値引金額や単品割引金額や単品修正値引金額や按分ミックスマッチ値引金額や
            //$sql .= "		   then (t2.waribiki + t2.nebiki + t2.mod_nebiki + t2.s_tyoseibiki+ t2.s_mixmatch_nebiki + t2.s_nebiki + t2.s_waribiki) ";//按分取引値引金額や按分取引割引金額や按分調整引金額等がゼロより少ない場合は全ての値を足して値引金額を表示
            $sql .= "	(t2.waribiki + t2.nebiki + t2.mod_nebiki +  t2.s_tyoseibiki+ t2.s_mixmatch_nebiki + t2.s_nebiki + t2.s_waribiki) as dis_price, ";//按分取引値引金額や按分取引割引金額や按分調整引金額等がゼロより少ない場合は全ての値を足して値引金額を表示          
            /*$sql .= "	case ";
            $sql .= "		when t2.tax_type = '2' then t2.itax ";//tax_type２の場合は内税
            $sql .= "		else null end as itax, ";//内税がヌールの場合はヌールを表示          
            $sql .= "	case ";
            $sql .= "		when t2.tax_type = '1' then t2.itax ";//tax_type1の場合は外税
            $sql .= "		else null end as utax, ";//外税がヌールの場合はヌールを表示*/
            $sql .= "   t1.utax as utax, ";//取引外税額   
            $sql .= "   t1.itax as itax, ";//取引内税額表示          
            /*$sql .= "	case ";            
            $sql .= "		when t2.tax_type = '1' then t2.pure_total + t2.itax ";//外税額ある場合は合計金額に外税額足して合計金額
            $sql .= "		else t2.pure_total end as total_price, ";//外税額ない場合は合計金額のみ取得*/            
            $sql .= "	t2.subtotal as total_price, ";//商品合計（外税抜き内税込み）
            $sql .= "	t1.pure_total + t1.utax as den_total_price, ";//売上取引合計金額と取引外税額足して伝票合計金額を表示  
            $sql .= "	case ";
            //EDITSTR 2020/04/17 kanderu
            // $sql .= "		when t1.return_hideseq <> 0 then t1.return_hideseq::text ";
            $sql .= "	        when t1.return_account_no <> 0 then t1.reji_no||' - '||t1.return_account_no::text ";
            //EDITEND 2020/04/17 kanderu
            $sql .= "	else '' end as pre_den_no , ";//元伝票票日付け
            $sql .= "	t10.proc_date as pre_den_date ";//元伝票番号
            $sql .= "from ";
            $sql .= "	trn0102 t2 ";
            $sql .= "left join trn0101 t1 ";
            $sql .= "		using (organization_id,hideseq) ";
           // $sql .= "	hideseq) ";
            $sql .= "left join (select organization_id ,hideseq ,string_agg(credit_nm ,'・') as credit_nm,sum(credit_money ) as credit_money from trn0201 "; 
            //検索条件支払い方法
            if( $joken['credit_cd'] !== 'false' )
            {
                if( $joken['credit_select'] === 'empty' && is_numeric(explode(",",str_replace("'","",$joken['credit_cd']))[0])!== false){
                    $sql .= " where credit_cd in (".$joken['credit_cd'].") ";
                }else if(is_numeric(explode(",",str_replace("'","",$joken['credit_select']))[0])!== false){
                    $sql .= " where credit_cd in ('".$joken['credit_select']."') ";
                }
            }            
            $sql .= "    group by organization_id ,hideseq) t3  ";
            /*$sql .= "left join trn0201 t3 "; */          
            $sql .= "		using (organization_id, ";
            $sql .= "	hideseq) ";
            $sql .= "left join m_organization_detail v ";
            $sql .= "		using (organization_id) ";
            $sql .= "left join trn0101 t10 on ";
            $sql .= "	t1.return_hideseq = t10.hideseq ";
            $sql .= "	and t1.organization_id = t10.organization_id ";
            $sql .= "left join mst0201 t5 on ";
            $sql .= "   t1.organization_id = t5.organization_id and t2.prod_cd = t5.prod_cd ";
            $sql .= "   and t2.prod_cd = t5.prod_cd ";             
            $sql .= "left join mst1001 t4 on ";
            $sql .= "   t1.organization_id = t4.organization_id ";
            $sql .= "   and t5.maker_cd = t4.maker_cd ";
            //現金やポイント金額や商品券金額等を追加
            $sql .= "left join trn0203 t6 on ";
            $sql .= "   t1.organization_id = t6.organization_id ";
            $sql .= "   and t1.hideseq = t6.hideseq ";
            $sql .= "where ";
            $sql .= "	cancel_kbn = '0' ";
            $sql .= "	and t1.stop_kbn = '0' ";
            $sql .= "	and t1.proc_date between :start_date and :end_date ";
            /*修正日：20200316　修正者：バッタライ
　　　　　　　修正内容：支払方法検索条件を現金や商品券金額やポイント金額にも追加*/
            if( $joken['credit_cd'] !== 'false' ){                 
                $sql .= "and( ";  
                if( $joken['credit_select'] === 'empty'){
                    $var = $joken['credit_cd'];
                }else{
                    $var = $joken['credit_select'];                    
                }  
                $var1 = '';
                if (strpos($var,'G1') !== false){
                   $var1 .= "       or  t6.gift_money <> '0'  ";
                }
                if (strpos($var,'C1') !== false){
                    $var1 .= "       or  t6.genkin <> '0'  ";
                }              
                if (strpos($var,'P1') !== false){
                    $var1 .= "      or   t6.point_money <> '0'  ";
                }
                if(is_numeric(explode(",",str_replace("'","",$var))[0])!== false){
                    $var1 .= "      or   coalesce(credit_nm,'') <> '' ";
                }
                $sql .= preg_replace("/or/","",$var1,1) ;
                $sql .= ") ";       
            }
            /*$sql .= "	and ";*/
            $sql .= $where;         
            $sql .= "order by proc_date, ";
            $sql .= "         org_id, ";
            $sql .= "         den_no  ";
            $sql .= "         ,prod_cd  ";          
            $searchArray[':start_date'] = str_replace('/','',$joken['start_date']);
            $searchArray[':end_date']  = str_replace('/','',$joken['end_date']); 
            //print_r($sql);
            // 一覧表を格納する空の配列宣言            
             $Datas = [];
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
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
                    $Datas[$data['proc_date']][$data['org_id']][$data['cust_cd']][$data['den_no']][] = $data;
            }

            $Log->trace("END get_data");

            return $Datas;
        }
    }   
?>
