<?php
    /**
     * @file      帳票 - 商品別実績
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 商品別実績の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';
    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetProductResultstest extends BaseModel
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
        
        public function get_data_1($searchparam){
        global $DBA, $Log; // グローバル変数宣言
        $Log->trace("START get_data_1");
        $sql  = "";
        $sql  .= " select  ";
//        if($searchparam['org_r'] === 'compile'){
//            $sql  .= " 	0 as org_id, ";
//            $sql  .= " 	'企業計' as org_nm, ";
//        }else{
//            $sql  .= " 	t_data.org_id, ";
//            $sql  .= " 	t_data.org_nm, ";            
//        }
     //   $sql  .= " select                                                                                                                                                                                                                                                                                        ";
        $sql  .= " b.prod_pure_total,                                                                                                                                                                                                                                                                            ";
        $sql  .= " total.prod_pure_total_total , ";
        $sql  .= " b.sect_cd,                                                                                                                                                                                                                                                                                    ";
        $sql  .= " b.sect_nm,                                                                                                                                                                                                                                                                                    ";
        $sql  .= " round (case when b.prod_pure_total = 0 then 0 else round(a.prod_pure_total/b.prod_pure_total * 100 ,2) end ,2) as r1,                                                                                                                                                             ";
        $sql  .= " a.* ,                                                                                                                                                                                                                                                                                       ";
     //   $sql  .= " round (case when c.prod_pure_total_c = 0 then 0 else round(b.prod_pure_total/c.prod_pure_total_c * 100 ,2) end ,2) as r3 ,                                                                                                                                                    ";
         $sql  .= " round (case when b.prod_pure_total = 0 then 0 else round(a.prod_pure_total/d.prod_pure_total * 100 ,2) end ,2) as r2 , ";
          $sql  .= " round (case when total.prod_pure_total_total = 0 then 0 else round(d.prod_pure_total/total.prod_pure_total_total * 100 ,2) end ,2) as r3  ";
        $sql  .= "  from                                                                                                                                                                                                                                                                                         ";
        $sql  .= " (select                                                                                                                                                                                                                                                                                       ";
        if($searchparam['org_r'] === 'compile'){
            $sql  .= " 	0 as org_id, ";
            $sql  .= " 	'企業計' as org_nm, ";
        }else{
            $sql  .= " 	t_data.org_id, ";
            $sql  .= " 	t_data.org_nm, ";            
        }
//        $sql  .= " t_data.org_id,                                                                                                                                                                                                                                                                                ";
        $sql  .= " t_data.prod_t_cd1,                                                                                                                                                                                                                                                                            ";
        $sql  .= " t_data.prod_t_nm1,                                                                                                                                                                                                                                                                            ";
        $sql  .= " t_data.prod_t_cd2,                                                                                                                                                                                                                                                                            ";
        $sql  .= " t_data.prod_t_nm2,                                                                                                                                                                                                                                                                            ";
        $sql  .= " t_data.maker_cd,                                                                                                                                                                                                                                                                              ";
        $sql  .= " t_data.maker_nm  ,                                                                                                                                                                                                                                                                              ";
        $sql  .= " t_data.supp_cd,                                                                                                                                                                                                                                                                               ";
        $sql  .= " t_data.supp_nm,                                                                                                                                                                                                                                                                               ";
        $sql  .= " t_data.sect_cd,                                                                                                                                                                                                                                                                               ";
        $sql  .= " t_data.sect_nm,                                                                                                                                                                                                                                                                               ";
        $sql  .= " t_data.prod_cd,                                                                                                                                                                                                                                                                               ";
        $sql  .= " t_data.prod_nm,                                                                                                                                                                                                                                                                               ";
        $sql  .= " t_data.prod_tax,                                                                                                                                                                                                                                                                              ";
        $sql  .= " t_data.prod_capa_nm,                                                                                                                                                                                                                                                                          ";
        $sql  .= " t_data.special,                                                                                                                                                                                                                                                                               ";
        $sql  .= " sum(t_data.prod_sale_amount)  as prod_sale_amount,                                                                                                                                                                                                                                            ";
        $sql  .= " sum(t_data.prod_pure_total)   as prod_pure_total,                                                                                                                                                                                                                                             ";
        $sql  .= " sum(t_data.prod_pure_total_c) as prod_pure_total_c,                                                                                                                                                                                                                                           ";
        $sql  .= " sum(t_data.pure_total)	      as pure_total,                                                                                                                                                                                                                                                 ";
        $sql  .= " sum(t_data.prod_profit)       as prod_profit,                                                                                                                                                                                                                                                 ";
        $sql  .= " case when sum(t_data.prod_sale_amount) = 0 then 0 else (sum(t_data.pure_total)-sum(t_data.prod_profit))/sum(t_data.prod_sale_amount) end as res1,                                                                                                                                             ";
        $sql  .= " case when sum(t_data.prod_sale_amount) = 0 then 0 else (sum(t_data.pure_total))/sum(t_data.prod_sale_amount) end as res2,                                                                                                                                                                     ";
        $sql  .= " case when sum(t_data.pure_total) = 0 then 0 else (sum(t_data.prod_profit))/sum(t_data.pure_total)*100 end as res3                                                                                                                                                                             ";
        $sql  .= " from (                                                                                                                                                                                                                                                                                        ";
        $sql  .= " select                                                                                                                                                                                                                                                                                        ";
        $sql  .= " j.proc_date ,                                                                                                                                                                                                                                                                                 ";
        $sql  .= " j.organization_id as org_id ,                                                                                                                                                                                                                                                                 ";
        $sql  .= " coalesce(o.abbreviated_name,'') as org_nm ,                                                                                                                                                                                                                                                   ";
        $sql  .= " j.prod_cd ,                                                                                                                                                                                                                                                                                   ";
        $sql  .= " coalesce(replace(m.prod_nm, '\"', '\\\"'),'') as prod_nm,                                                                                                                                                                                                                                     ";
        $sql  .= " coalesce(m.prod_tax,0) as  prod_tax,                                                                                                                                                                                                                                                          ";
        $sql  .= " coalesce(m.prod_capa_kn,'') as prod_capa_nm ,                                                                                                                                                                                                                                                 ";
        $sql  .= " coalesce(sp.sale_amount , j.prod_sale_amount) as prod_sale_amount,                                                                                                                                                                                                                            ";
        $sql  .= " coalesce(sp.pure_total, j.prod_pure_total) as prod_pure_total,                                                                                                                                                                                                                                ";
        $sql  .= " j.prod_pure_total as prod_pure_total_c,                                                                                                                                                                                                                                                       ";
        $sql  .= " sp.pure_total,                                                                                                                                                                                                                                                                                ";
        $sql  .= " coalesce(sp.sale_profit , j.prod_profit) as prod_profit,                                                                                                                                                                                                                                      ";
        $sql  .= " m.head_supp_cd as supp_cd ,                                                                                                                                                                                                                                                                   ";
        $sql  .= " m.sect_cd ,                                                                                                                                                                                                                                                                                   ";
        $sql  .= " m.maker_cd ,                                                                                                                                                                                                                                                                                  ";
        $sql  .= " m.prod_t_cd1,                                                                                                                                                                                                                                                                                 ";
        $sql  .= " m.prod_t_cd2,                                                                                                                                                                                                                                                                                 ";
        $sql  .= " m1.sect_nm,                                                                                                                                                                                                                                                                                   ";
        $sql  .= " m2.supp_nm,                                                                                                                                                                                                                                                                                   ";
        $sql  .= " m3.maker_nm,                                                                                                                                                                                                                                                                                  ";
        $sql  .= " MST0801_1.prod_t_nm as prod_t_nm1,                                                                                                                                                                                                                                                            ";
        $sql  .= " MST0801_2.prod_t_nm as prod_t_nm2,                                                                                                                                                                                                                                                            ";
        $sql  .= " MST0801_3.prod_t_nm as prod_t_nm3,                                                                                                                                                                                                                                                            ";
        $sql  .= " MST0801_4.prod_t_nm as prod_t_nm4,                                                                                                                                                                                                                                                            ";
        $sql  .= " coalesce(sp.special, 0) as special                                                                                                                                                                                                                                                            ";
        $sql  .= " from jsk5110 j                                                                                                                                                                                                                                                                                ";
        $sql  .= " left join m_organization_detail o on (o.organization_id = j.organization_id)                                                                                                                                                                                                                  ";
        $sql  .= " left join mst0201 m on (m.organization_id = j.organization_id and m.prod_cd = j.prod_cd)                                                                                                                                                                                                      ";
        $sql  .= " left join mst1201 m1 on (m.organization_id = m1.organization_id and m.sect_cd = m1.sect_cd)                                                                                                                                                                                                   ";
        $sql  .= " left join mst1101 m2 on (m.organization_id = m2.organization_id and m.head_supp_cd = m2.supp_cd)                                                                                                                                                                                              ";
        $sql  .= " left join mst1001 m3 on (m.organization_id = m3.organization_id and m.maker_cd = m3.maker_cd)                                                                                                                                                                                                 ";
        $sql  .= " left join (select * from MST0801 where prod_t_cd2 = '' and prod_t_cd3 = '' and prod_t_cd4 = '') as MST0801_1 on (MST0801_1.prod_t_cd1 = m.prod_t_cd1 and MST0801_1.organization_id = m.organization_id)                                                                                       ";
        $sql  .= " left join (select * from MST0801 where prod_t_cd3 = '' and prod_t_cd4 = '') as MST0801_2 on (MST0801_2.prod_t_cd1 = m.prod_t_cd1 and MST0801_2.prod_t_cd2 = m.prod_t_cd2 and MST0801_2.organization_id = m.organization_id)                                                                   ";
        $sql  .= " left join (select * from MST0801 where prod_t_cd4 = '' ) as MST0801_3 on (MST0801_3.prod_t_cd1 = m.prod_t_cd1 and MST0801_3.prod_t_cd2 = m.prod_t_cd2 and MST0801_3.prod_t_cd3 = m.prod_t_cd3 and MST0801_3.organization_id = m.organization_id)                                              ";
        $sql  .= " left join (select * from MST0801 where prod_t_cd4 != '' ) as MST0801_4 on (MST0801_4.prod_t_cd1 = m.prod_t_cd1 and MST0801_4.prod_t_cd2 = m.prod_t_cd2 and MST0801_4.prod_t_cd3 = m.prod_t_cd3 and MST0801_4.prod_t_cd4 = m.prod_t_cd4 and MST0801_4.organization_id = m.organization_id)     ";
        $sql  .= " left join (                                                                                                                                                                                                                                                                                      ";  
        $sql  .= " select                                                                                                                                                                                                                                                                                           ";
        $sql  .= " distinct(organization_id),                                                                                                                                                                                                                                                                       ";
        $sql  .= " proc_date,                                                                                                                                                                                                                                                                                       ";
        $sql  .= " prod_cd,                                                                                                                                                                                                                                                                                         ";
        $sql  .= " (case sale_plan_cd when '' then 0 else 1 end ) as special,                                                                                                                                                                                                                                       ";
        $sql  .= " sum(pure_total) as pure_total,                                                                                                                                                                                                                                                                   ";
        $sql  .= " sum(sale_amount) as sale_amount,                                                                                                                                                                                                                                                                 ";
        $sql  .= " sum(sale_profit) as sale_profit                                                                                                                                                                                                                                                                  ";
        $sql  .= " from                                                                                                                                                                                                                                                                                             ";
        $sql  .= " jsk1140                                                                                                                                                                                                                                                                                          ";
        $sql  .= " where                                                                                                                                                                                                                                                                                            ";
        $sql  .= " sale_plan_cd ~ '^[0-9]*$'                                                                                                                                                                                                                                                                        ";
        $sql  .= " and proc_date between :startdate and :enddate                                                                                                                                                                                                                                            ";
       //$sql  .= " 			and proc_date between '20200701' and '20200702'";
        if($searchparam['org_r'] === ''){
            if( $searchparam['org_select'] === 'empty'){
                $sql .= " and organization_id in (".$searchparam['org_id'].")";
            }else{
                $sql .= " and organization_id in (".$searchparam['org_select'].")";
            }
        }
        if($searchparam['prod_r'] === ''){
            if( $searchparam['prod_select'] === 'empty'){
                $sql .= " and prod_cd in (".$searchparam['prod_cd'].")";
            }else{
                $sql .= " and prod_cd in ('".$searchparam['prod_select']."')";
            }
        }         
        $sql  .= " group by                                                                                                                                                                                                                                                                                         ";
        $sql  .= " organization_id,                                                                                                                                                                                                                                                                                 ";
        $sql  .= " proc_date,                                                                                                                                                                                                                                                                                       ";
        $sql  .= " prod_cd,                                                                                                                                                                                                                                                                                         ";
        $sql  .= " special) sp on (sp.organization_id = j.organization_id and sp.prod_cd = j.prod_cd and sp.proc_date = j.proc_date )                                                                                                                                                                               ";
        $sql  .= " where                                                                                                                                                                                                                                                                                            ";
        $sql  .= " 1 = :val1                                                                                                                                                                                                                                                                                        ";
        $sql  .= " and( j.prod_pure_total <> 0                                                                                                                                                                                                                                                                      ";
        $sql  .= " or j.prod_sale_total <> 0                                                                                                                                                                                                                                                                        ";
        $sql  .= " or j.prod_profit <> 0)                                                                                                                                                                                                                                                                           ";
       // $sql  .= " and j. proc_date between: and :enddate                                                                                                                                                                                                                                              ";
//        $sql  .= "                                                                                                                                                                                                                                                                                                  ";
       $sql  .= " and j.proc_date between :startdate and :enddate ";
        if($searchparam['org_r'] === ''){
            if( $searchparam['org_select'] === 'empty'){
                $sql .= " and j.organization_id in (".$searchparam['org_id'].")";
            }else{
                $sql .= " and j.organization_id in (".$searchparam['org_select'].")";
            }
        }
        if($searchparam['prod_r'] === ''){
            if( $searchparam['prod_select'] === 'empty'){
                $sql .= " and j.prod_cd in (".$searchparam['prod_cd'].")";
            }else{
                $sql .= " and j.prod_cd in ('".$searchparam['prod_select']."')";
            }
        }
        if($searchparam['appoprod_r'] === ''){
            if( $searchparam['appoprod_select'] === 'empty'){
                $sql .= " and m.appo_prod_kbn in (".$searchparam['appoprod_cd'].")";
            }else{
                $sql .= " and m.appo_prod_kbn in ('".$searchparam['appoprod_select']."')";
            }
        }            
        if($searchparam['sect_r'] === ''){
            if( $searchparam['sect_select'] === 'empty'){
                $sql .= " and m.sect_cd in (".$searchparam['sect_cd'].")";
            }else{
                $sql .= " and m.sect_cd in ('".$searchparam['sect_select']."')";
            }
        }
        if($searchparam['supp_r'] === ''){
            if( $searchparam['supp_select'] === 'empty'){
                $sql .= " and m.head_supp_cd in (".$searchparam['supp_cd'].")";
            }else{
                $sql .= " and m.head_supp_cd in ('".$searchparam['supp_select']."')";
            }
        }     
        $sql  .= " order by                                                                                                                                                                                                                                                                                         ";
        $sql  .= " proc_date,                                                                                                                                                                                                                                                                                       ";
        $sql  .= " prod_cd                                                                                                                                                                                                                                                                                          ";
        $sql  .= " ) t_data                                                                                                                                                                                                                                                                                         ";
            if($searchparam['special_sale']){
                $sql  .= " where t_data.special = 1";
            }
            $sql  .= " group by  ";
            if($searchparam['org_r'] !== 'compile'){
                $sql  .= " 	t_data.org_id, ";
                $sql  .= " 	t_data.org_nm, ";
            }
      //  $sql  .= " group by                                                                                                                                                                                                                                                                                         ";
        $sql  .= " t_data.org_id,                                                                                                                                                                                                                                                                                   ";
        $sql  .= " t_data.org_nm,";
        $sql  .= " t_data.prod_cd,                                                                                                                                                                                                                                                                                  ";
        $sql  .= " t_data.prod_nm,                                                                                                                                                                                                                                                                                  ";
        $sql  .= " t_data.prod_tax,                                                                                                                                                                                                                                                                                 ";
        $sql  .= " t_data.prod_capa_nm,                                                                                                                                                                                                                                                                             ";
        $sql  .= " t_data.special ,                                                                                                                                                                                                                                                                                 ";
        $sql  .= " t_data.sect_cd,                                                                                                                                                                                                                                                                                  ";
        $sql  .= " t_data.maker_cd,                                                                                                                                                                                                                                                                                 ";
        $sql  .= " t_data.supp_cd,                                                                                                                                                                                                                                                                                  ";
        $sql  .= " t_data.prod_t_cd1,                                                                                                                                                                                                                                                                               ";
        $sql  .= " t_data.prod_t_cd2,                                                                                                                                                                                                                                                                               ";
        $sql  .= " t_data.sect_nm,                                                                                                                                                                                                                                                                                  ";
        $sql  .= " t_data.supp_nm,                                                                                                                                                                                                                                                                                  ";
        $sql  .= " t_data.maker_nm,                                                                                                                                                                                                                                                                                 ";
        $sql  .= " t_data.prod_t_nm1,                                                                                                                                                                                                                                                                               ";
        $sql  .= " t_data.prod_t_nm2                                                                                                                                                                                                                                                                              ";
       // $sql  .= " t_data.prod_pure_total,                                                                                                                                                                                                                                                                          ";
     //  $sql  .= " t_data.pure_total                                                                                                                                                                                                                                                                                ";
        $sql  .= " order by	                                                                                                                                                                                                                                                                                        ";
        $sql  .= " org_id,prod_pure_total desc,prod_cd,special                                                                                                                                                                                                                                                      ";
        $sql  .= " ) as a                                                                                                                                                                                                                                                                                           ";
       
     //   $sql  .= "                                                                                                                                                                                                                                                                                                  ";
        $sql  .= " left join                                                                                                                                                                                                                                                                                        ";
       // $sql  .= "                                                                                                                                                                                                                                                                                                  ";
        $sql  .= " (select                                                                                                                                                                                                                                                                                          ";
        $sql  .= " t_data.org_id,                                                                                                                                                                                                                                                                                   ";
        $sql  .= " t_data.sect_cd,                                                                                                                                                                                                                                                                                  ";
        $sql  .= " t_data.sect_nm,                                                                                                                                                                                                                                                                                  ";
        $sql  .= " sum(t_data.prod_sale_amount)  as prod_sale_amount,                                                                                                                                                                                                                                               ";
        $sql  .= " sum(t_data.prod_pure_total)   as prod_pure_total,                                                                                                                                                                                                                                                ";
        $sql  .= " sum(t_data.prod_pure_total_c) as prod_pure_total_c,                                                                                                                                                                                                                                              ";
        $sql  .= " sum(t_data.pure_total)	      as pure_total,                                                                                                                                                                                                                                                    ";
        $sql  .= " sum(t_data.prod_profit)       as prod_profit,                                                                                                                                                                                                                                                    ";
        $sql  .= " case when sum(t_data.prod_sale_amount) = 0 then 0 else (sum(t_data.pure_total)-sum(t_data.prod_profit))/sum(t_data.prod_sale_amount) end as res1,                                                                                                                                                ";
        $sql  .= " case when sum(t_data.prod_sale_amount) = 0 then 0 else (sum(t_data.pure_total))/sum(t_data.prod_sale_amount) end as res2,                                                                                                                                                                        ";
        $sql  .= " case when sum(t_data.pure_total) = 0 then 0 else (sum(t_data.prod_profit))/sum(t_data.pure_total)*100 end as res3                                                                                                                                                                                ";
        $sql  .= " from (                                                                                                                                                                                                                                                                                           ";
        $sql  .= " select                                                                                                                                                                                                                                                                                           ";
        $sql  .= " j.proc_date ,                                                                                                                                                                                                                                                                                    ";
        $sql  .= " j.organization_id as org_id ,                                                                                                                                                                                                                                                                    ";
        $sql  .= " coalesce(o.abbreviated_name,'') as org_nm ,                                                                                                                                                                                                                                                      ";
        $sql  .= " j.prod_cd ,                                                                                                                                                                                                                                                                                      ";
        $sql  .= " coalesce(replace(m.prod_nm, '\"', '\\\"'),'') as prod_nm,                                                                                                                                                                                                                                        ";
        $sql  .= " coalesce(m.prod_tax,0) as  prod_tax,                                                                                                                                                                                                                                                             ";
        $sql  .= " coalesce(m.prod_capa_kn,'') as prod_capa_nm ,                                                                                                                                                                                                                                                    ";
        $sql  .= " coalesce(sp.sale_amount , j.prod_sale_amount) as prod_sale_amount,                                                                                                                                                                                                                               ";
        $sql  .= " coalesce(sp.pure_total, j.prod_pure_total) as prod_pure_total,                                                                                                                                                                                                                                   ";
        $sql  .= " j.prod_pure_total as prod_pure_total_c,                                                                                                                                                                                                                                                          ";
        $sql  .= " sp.pure_total,                                                                                                                                                                                                                                                                                   ";
        $sql  .= " coalesce(sp.sale_profit , j.prod_profit) as prod_profit,                                                                                                                                                                                                                                         ";
        $sql  .= " m.head_supp_cd as supp_cd ,                                                                                                                                                                                                                                                                      ";
        $sql  .= " m.sect_cd ,                                                                                                                                                                                                                                                                                      ";
        $sql  .= " m.maker_cd ,                                                                                                                                                                                                                                                                                     ";
        $sql  .= " m.prod_t_cd1,                                                                                                                                                                                                                                                                                    ";
        $sql  .= " m.prod_t_cd2,                                                                                                                                                                                                                                                                                    ";
        $sql  .= " m1.sect_nm,                                                                                                                                                                                                                                                                                      ";
        $sql  .= " m2.supp_nm,                                                                                                                                                                                                                                                                                      ";
        $sql  .= " m3.maker_nm,                                                                                                                                                                                                                                                                                     ";
        $sql  .= " MST0801_1.prod_t_nm as prod_t_nm1,                                                                                                                                                                                                                                                               ";
        $sql  .= " MST0801_2.prod_t_nm as prod_t_nm2,                                                                                                                                                                                                                                                               ";
        $sql  .= " MST0801_3.prod_t_nm as prod_t_nm3,                                                                                                                                                                                                                                                               ";
        $sql  .= " MST0801_4.prod_t_nm as prod_t_nm4,                                                                                                                                                                                                                                                               ";
        $sql  .= " coalesce(sp.special, 0) as special                                                                                                                                                                                                                                                               ";
        $sql  .= " from jsk5110 j                                                                                                                                                                                                                                                                                   ";
        $sql  .= " left join m_organization_detail o on (o.organization_id = j.organization_id)                                                                                                                                                                                                                     ";
        $sql  .= " left join mst0201 m on (m.organization_id = j.organization_id and m.prod_cd = j.prod_cd)                                                                                                                                                                                                         ";
        $sql  .= " left join mst1201 m1 on (m.organization_id = m1.organization_id and m.sect_cd = m1.sect_cd)                                                                                                                                                                                                      ";
        $sql  .= " left join mst1101 m2 on (m.organization_id = m2.organization_id and m.head_supp_cd = m2.supp_cd)                                                                                                                                                                                                 ";
        $sql  .= " left join mst1001 m3 on (m.organization_id = m3.organization_id and m.maker_cd = m3.maker_cd)                                                                                                                                                                                                    ";
        $sql  .= " left join (select * from MST0801 where prod_t_cd2 = '' and prod_t_cd3 = '' and prod_t_cd4 = '') as MST0801_1 on (MST0801_1.prod_t_cd1 = m.prod_t_cd1 and MST0801_1.organization_id = m.organization_id)                                                                                          ";
        $sql  .= " left join (select * from MST0801 where prod_t_cd3 = '' and prod_t_cd4 = '') as MST0801_2 on (MST0801_2.prod_t_cd1 = m.prod_t_cd1 and MST0801_2.prod_t_cd2 = m.prod_t_cd2 and MST0801_2.organization_id = m.organization_id)                                                                      ";
        $sql  .= " left join (select * from MST0801 where prod_t_cd4 = '' ) as MST0801_3 on (MST0801_3.prod_t_cd1 = m.prod_t_cd1 and MST0801_3.prod_t_cd2 = m.prod_t_cd2 and MST0801_3.prod_t_cd3 = m.prod_t_cd3 and MST0801_3.organization_id = m.organization_id)                                                 ";
        $sql  .= " left join (select * from MST0801 where prod_t_cd4 != '' ) as MST0801_4 on (MST0801_4.prod_t_cd1 = m.prod_t_cd1 and MST0801_4.prod_t_cd2 = m.prod_t_cd2 and MST0801_4.prod_t_cd3 = m.prod_t_cd3 and MST0801_4.prod_t_cd4 = m.prod_t_cd4 and MST0801_4.organization_id = m.organization_id)        ";
        $sql  .= " left join (                                                                                                                                                                                                                                                                                      ";
        $sql  .= " select                                                                                                                                                                                                                                                                                           ";
        $sql  .= " distinct(organization_id),                                                                                                                                                                                                                                                                       ";
        $sql  .= " proc_date,                                                                                                                                                                                                                                                                                       ";
        $sql  .= " prod_cd,                                                                                                                                                                                                                                                                                         ";
        $sql  .= " (case sale_plan_cd when '' then 0 else 1 end ) as special,                                                                                                                                                                                                                                       ";
        $sql  .= " sum(pure_total) as pure_total,                                                                                                                                                                                                                                                                   ";
        $sql  .= " sum(sale_amount) as sale_amount,                                                                                                                                                                                                                                                                 ";
        $sql  .= " sum(sale_profit) as sale_profit                                                                                                                                                                                                                                                                  ";
        $sql  .= " from                                                                                                                                                                                                                                                                                             ";
        $sql  .= " jsk1140                                                                                                                                                                                                                                                                                          ";
        $sql  .= " where                                                                                                                                                                                                                                                                                            ";
        $sql  .= " sale_plan_cd ~ '^[0-9]*$'                                                                                                                                                                                                                                                                        ";
        $sql  .= " and proc_date between :startdate and :enddate                                                                                                                                                                                                                                        ";
      //  $sql  .= " 			and proc_date between '20200701' and '20200702'";
        if($searchparam['org_r'] === ''){
            if( $searchparam['org_select'] === 'empty'){
                $sql .= " and organization_id in (".$searchparam['org_id'].")";
            }else{
                $sql .= " and organization_id in (".$searchparam['org_select'].")";
            }
        }
        if($searchparam['prod_r'] === ''){
            if( $searchparam['prod_select'] === 'empty'){
                $sql .= " and prod_cd in (".$searchparam['prod_cd'].")";
            }else{
                $sql .= " and prod_cd in ('".$searchparam['prod_select']."')";
            }
        }  
        $sql  .= " group by                                                                                                                                                                                                                                                                                         ";
        $sql  .= " organization_id,                                                                                                                                                                                                                                                                                 ";
        $sql  .= " proc_date,                                                                                                                                                                                                                                                                                       ";
        $sql  .= " prod_cd,                                                                                                                                                                                                                                                                                         ";
        $sql  .= " special) sp on (sp.organization_id = j.organization_id and sp.prod_cd = j.prod_cd and sp.proc_date = j.proc_date )                                                                                                                                                                               ";
        $sql  .= " where                                                                                                                                                                                                                                                                                            ";
        $sql  .= " 1 = :val1                                                                                                                                                                                                                                                                                         ";
        $sql  .= " and( j.prod_pure_total <> 0                                                                                                                                                                                                                                                                      ";
        $sql  .= " or j.prod_sale_total <> 0                                                                                                                                                                                                                                                                        ";
        $sql  .= " or j.prod_profit <> 0)                                                                                                                                                                                                                                                                           ";
       // $sql  .= " 		and j.proc_date between :startdate and :enddate ";
        $sql  .= " and j.proc_date between :startdate and :enddate ";   
        if($searchparam['org_r'] === ''){
            if( $searchparam['org_select'] === 'empty'){
                $sql .= " and j.organization_id in (".$searchparam['org_id'].")";
            }else{
                $sql .= " and j.organization_id in (".$searchparam['org_select'].")";
            }
        }
        if($searchparam['prod_r'] === ''){
            if( $searchparam['prod_select'] === 'empty'){
                $sql .= " and j.prod_cd in (".$searchparam['prod_cd'].")";
            }else{
                $sql .= " and j.prod_cd in ('".$searchparam['prod_select']."')";
            }
        }
        if($searchparam['appoprod_r'] === ''){
            if( $searchparam['appoprod_select'] === 'empty'){
                $sql .= " and m.appo_prod_kbn in (".$searchparam['appoprod_cd'].")";
            }else{
                $sql .= " and m.appo_prod_kbn in ('".$searchparam['appoprod_select']."')";
            }
        }            
        if($searchparam['sect_r'] === ''){
            if( $searchparam['sect_select'] === 'empty'){
                $sql .= " and m.sect_cd in (".$searchparam['sect_cd'].")";
            }else{
                $sql .= " and m.sect_cd in ('".$searchparam['sect_select']."')";
            }
        }
        if($searchparam['supp_r'] === ''){
            if( $searchparam['supp_select'] === 'empty'){
                $sql .= " and m.head_supp_cd in (".$searchparam['supp_cd'].")";
            }else{
                $sql .= " and m.head_supp_cd in ('".$searchparam['supp_select']."')";
            }
        } 
        //   $sql  .= " and j. proc_date between '20200701' and '20200702'                                                                                                                                                                                                                                               ";
       // $sql  .= "                                                                                                                                                                                                                                                                                                  ";
        $sql  .= " order by                                                                                                                                                                                                                                                                                         ";
        $sql  .= " proc_date,                                                                                                                                                                                                                                                                                       ";
        $sql  .= " prod_cd                                                                                                                                                                                                                                                                                          ";
        $sql  .= " ) t_data                                                                                                                                                                                                                                                                                         ";
            if($searchparam['special_sale']){
                $sql  .= " where t_data.special = 1";
            }
            $sql  .= " group by  ";
            if($searchparam['org_r'] !== 'compile'){
                $sql  .= " 	t_data.org_id, ";
                $sql  .= " 	t_data.org_nm, ";
            }
        $sql  .= " t_data.org_id,                                                                                                                                                                                                                                                                                   ";
        $sql  .= " t_data.sect_cd,                                                                                                                                                                                                                                                                                  ";
        $sql  .= " t_data.sect_nm                                                                                                                                                                                                                                                                                   ";
        $sql  .= " order by	                                                                                                                                                                                                                                                                                        ";
        $sql  .= " org_id                                                                                                                                                                                                                                                                                           ";
        $sql  .= " ) as b on (a.org_id = b.org_id and a.sect_cd = b.sect_cd )                                                                                                                                                                                                                                       ";
        $sql  .= "left join (                                                                                                                                                                                                                                                                                            ";
        $sql  .= "select                                                                                                                                                                                                                                                                                                 ";
        $sql  .= "t_data.org_id,                                                                                                                                                                                                                                                                                         ";
        $sql  .= "t_data.prod_t_cd2,                                                                                                                                                                                                                                                                                     ";
        $sql  .= "t_data.prod_t_nm2,                                                                                                                                                                                                                                                                                     ";
        $sql  .= "sum(t_data.prod_pure_total)   as prod_pure_total,                                                                                                                                                                                                                                                      ";
        $sql  .= "sum(t_data.prod_pure_total_c) as prod_pure_total_c                                                                                                                                                                                                                                                     ";
        $sql  .= "from (                                                                                                                                                                                                                                                                                                 ";
        $sql  .= "select                                                                                                                                                                                                                                                                                                 ";
        $sql  .= "j.proc_date ,                                                                                                                                                                                                                                                                                          ";
        $sql  .= "j.organization_id as org_id ,                                                                                                                                                                                                                                                                          ";
        $sql  .= "coalesce(o.abbreviated_name,'') as org_nm ,                                                                                                                                                                                                                                                            ";
        $sql  .= "j.prod_cd ,                                                                                                                                                                                                                                                                                            ";
        $sql  .= "coalesce(replace(m.prod_nm, '\"', '\\\"'),'') as prod_nm,                                                                                                                                                                                                                                              ";
        $sql  .= "coalesce(m.prod_tax,0) as  prod_tax,                                                                                                                                                                                                                                                                   ";
        $sql  .= "coalesce(m.prod_capa_kn,'') as prod_capa_nm ,                                                                                                                                                                                                                                                          ";
        $sql  .= "coalesce(sp.sale_amount , j.prod_sale_amount) as prod_sale_amount,                                                                                                                                                                                                                                     ";
        $sql  .= "coalesce(sp.pure_total, j.prod_pure_total) as prod_pure_total,                                                                                                                                                                                                                                         ";
        $sql  .= "j.prod_pure_total as prod_pure_total_c,                                                                                                                                                                                                                                                                ";
        $sql  .= "sp.pure_total,                                                                                                                                                                                                                                                                                         ";
        $sql  .= "coalesce(sp.sale_profit , j.prod_profit) as prod_profit,                                                                                                                                                                                                                                               ";
        $sql  .= "m.prod_t_cd1,                                                                                                                                                                                                                                                                                          ";
        $sql  .= "m.prod_t_cd2,                                                                                                                                                                                                                                                                                          ";
        $sql  .= "m.prod_t_cd3,  "; 
        $sql  .= "m.prod_t_cd4,  ";
        $sql  .= "MST0801_1.prod_t_nm as prod_t_nm1,                                                                                                                                                                                                                                                                     ";
        $sql  .= "MST0801_2.prod_t_nm as prod_t_nm2,                                                                                                                                                                                                                                                                     ";
        $sql  .= "MST0801_3.prod_t_nm as prod_t_nm3,                                                                                                                                                                                                                                                                     ";
        $sql  .= "MST0801_4.prod_t_nm as prod_t_nm4,                                                                                                                                                                                                                                                                     ";
        $sql  .= "coalesce(sp.special, 0) as special                                                                                                                                                                                                                                                                     ";
        $sql  .= "from jsk5110 j                                                                                                                                                                                                                                                                                         ";
        $sql  .= "left join m_organization_detail o on (o.organization_id = j.organization_id)                                                                                                                                                                                                                           ";
        $sql  .= "left join mst0201 m on (m.organization_id = j.organization_id and m.prod_cd = j.prod_cd)                                                                                                                                                                                                               ";
        $sql  .= "left join mst1201 m1 on (m.organization_id = m1.organization_id and m.sect_cd = m1.sect_cd)                                                                                                                                                                                                            ";
        $sql  .= "left join mst1101 m2 on (m.organization_id = m2.organization_id and m.head_supp_cd = m2.supp_cd)                                                                                                                                                                                                       ";
        $sql  .= "left join mst1001 m3 on (m.organization_id = m3.organization_id and m.maker_cd = m3.maker_cd)                                                                                                                                                                                                          ";
        $sql  .= "left join (select * from MST0801 where prod_t_cd2 = '' and prod_t_cd3 = '' and prod_t_cd4 = '') as MST0801_1 on (MST0801_1.prod_t_cd1 = m.prod_t_cd1 and MST0801_1.organization_id = m.organization_id)                                                                                                ";
        $sql  .= "left join (select * from MST0801 where prod_t_cd3 = '' and prod_t_cd4 = '') as MST0801_2 on (MST0801_2.prod_t_cd1 = m.prod_t_cd1 and MST0801_2.prod_t_cd2 = m.prod_t_cd2 and MST0801_2.organization_id = m.organization_id)                                                                            ";
        $sql  .= "left join (select * from MST0801 where prod_t_cd4 = '' ) as MST0801_3 on (MST0801_3.prod_t_cd1 = m.prod_t_cd1 and MST0801_3.prod_t_cd2 = m.prod_t_cd2 and MST0801_3.prod_t_cd3 = m.prod_t_cd3 and MST0801_3.organization_id = m.organization_id)                                                       ";
        $sql  .= "left join (select * from MST0801 where prod_t_cd4 != '' ) as MST0801_4 on (MST0801_4.prod_t_cd1 = m.prod_t_cd1 and MST0801_4.prod_t_cd2 = m.prod_t_cd2 and MST0801_4.prod_t_cd3 = m.prod_t_cd3 and MST0801_4.prod_t_cd4 = m.prod_t_cd4 and MST0801_4.organization_id = m.organization_id)              ";
        $sql  .= "left join (                                                                                                                                                                                                                                                                                            ";
        $sql  .= "select                                                                                                                                                                                                                                                                                                 ";
        $sql  .= "distinct(organization_id),                                                                                                                                                                                                                                                                             ";
        $sql  .= "proc_date,                                                                                                                                                                                                                                                                                             ";
        $sql  .= "prod_cd,                                                                                                                                                                                                                                                                                               ";
        $sql  .= "(case sale_plan_cd when '' then 0 else 1 end ) as special,                                                                                                                                                                                                                                             ";
        $sql  .= "sum(pure_total) as pure_total,                                                                                                                                                                                                                                                                         ";
        $sql  .= "sum(sale_amount) as sale_amount,                                                                                                                                                                                                                                                                       ";
        $sql  .= "sum(sale_profit) as sale_profit                                                                                                                                                                                                                                                                        ";
        $sql  .= "from                                                                                                                                                                                                                                                                                                   ";
        $sql  .= "jsk1140                                                                                                                                                                                                                                                                                                ";
        $sql  .= "where                                                                                                                                                                                                                                                                                                  ";
        $sql  .= "sale_plan_cd ~ '^[0-9]*$'                                                                                                                                                                                                                                                                              ";
        $sql  .= " and proc_date between :startdate and :enddate                                                                                                                                                                                                                                                     ";
                  if($searchparam['org_r'] === ''){
                if( $searchparam['org_select'] === 'empty'){
                    $sql .= " and organization_id in (".$searchparam['org_id'].")";
                }else{
                    $sql .= " and organization_id in (".$searchparam['org_select'].")";
                }
            }
            if($searchparam['prod_r'] === ''){
                if( $searchparam['prod_select'] === 'empty'){
                    $sql .= " and prod_cd in (".$searchparam['prod_cd'].")";
                }else{
                    $sql .= " and prod_cd in ('".$searchparam['prod_select']."')";
                }
            }  
        $sql  .= "group by                                                                                                                                                                                                                                                                                               ";
        $sql  .= "organization_id,                                                                                                                                                                                                                                                                                       ";
        $sql  .= "proc_date,                                                                                                                                                                                                                                                                                             ";
        $sql  .= "prod_cd,                                                                                                                                                                                                                                                                                               ";
        $sql  .= "special) sp on (sp.organization_id = j.organization_id and sp.prod_cd = j.prod_cd and sp.proc_date = j.proc_date )                                                                                                                                                                                     ";
        $sql  .= "where                                                                                                                                                                                                                                                                                                  ";
        $sql  .= "1 = :val1                                                                                                                                                                                                                                                                                                  ";
        $sql  .= "and( j.prod_pure_total <> 0                                                                                                                                                                                                                                                                            ";
        $sql  .= "or j.prod_sale_total <> 0                                                                                                                                                                                                                                                                              ";
        $sql  .= "or j.prod_profit <> 0)                                                                                                                                                                                                                                                                                 ";
        $sql  .= "and j.proc_date between :startdate and :enddate                                                                                                                                                                                                                                                 ";
                 if($searchparam['org_r'] === ''){
                if( $searchparam['org_select'] === 'empty'){
                    $sql .= " and j.organization_id in (".$searchparam['org_id'].")";
                }else{
                    $sql .= " and j.organization_id in (".$searchparam['org_select'].")";
                }
            }
            if($searchparam['prod_r'] === ''){
                if( $searchparam['prod_select'] === 'empty'){
                    $sql .= " and j.prod_cd in (".$searchparam['prod_cd'].")";
                }else{
                    $sql .= " and j.prod_cd in ('".$searchparam['prod_select']."')";
                }
            }
            if($searchparam['appoprod_r'] === ''){
                if( $searchparam['appoprod_select'] === 'empty'){
                    $sql .= " and m.appo_prod_kbn in (".$searchparam['appoprod_cd'].")";
                }else{
                    $sql .= " and m.appo_prod_kbn in ('".$searchparam['appoprod_select']."')";
                }
            }            
            if($searchparam['sect_r'] === ''){
                if( $searchparam['sect_select'] === 'empty'){
                    $sql .= " and m.sect_cd in (".$searchparam['sect_cd'].")";
                }else{
                    $sql .= " and m.sect_cd in ('".$searchparam['sect_select']."')";
                }
            }
            if($searchparam['supp_r'] === ''){
                if( $searchparam['supp_select'] === 'empty'){
                    $sql .= " and m.head_supp_cd in (".$searchparam['supp_cd'].")";
                }else{
                    $sql .= " and m.head_supp_cd in ('".$searchparam['supp_select']."')";
                }
            }
        //  $sql  .= "                                                                                                                                                                                                                                                                                                       ";
        $sql  .= "order by                                                                                                                                                                                                                                                                                               ";
        $sql  .= "proc_date,                                                                                                                                                                                                                                                                                             ";
        $sql  .= "prod_cd                                                                                                                                                                                                                                                                                                ";
        $sql  .= ") t_data                                                                                                                                                                                                                                                                                               ";
        $sql  .= " where t_data.prod_t_cd3='' and t_data.prod_t_cd4=''   ";
            if($searchparam['special_sale']){
                $sql  .= " where t_data.special = 1";
            }
            $sql  .= " group by  ";
            if($searchparam['org_r'] !== 'compile'){
                $sql  .= " 	t_data.org_id, ";
                $sql  .= " 	t_data.org_nm, ";
            }
        $sql  .= "t_data.org_id,                                                                                                                                                                                                                                                                                         ";
        $sql  .= "                                                                                                                                                                                                                                                                                                       ";
        $sql  .= "t_data.prod_t_cd2,                                                                                                                                                                                                                                                                                     ";
        $sql  .= "                                                                                                                                                                                                                                                                                                       ";
        $sql  .= "t_data.prod_t_nm2                                                                                                                                                                                                                                                                                      ";
        $sql  .= "order by	                                                                                                                                                                                                                                                                                             ";
        $sql  .= "org_id ) as d on (a.org_id = d.org_id and a.prod_t_cd2 = d.prod_t_cd2)                                                                                                                                                                                                                                 ";
        $sql  .= "left join (                                                                                                                                                                                                                                                                                                            ";
        $sql  .= "select                                                                                                                                                                                                                                                                                                                 ";
        $sql  .= "sum(t_data.prod_sale_amount)  as prod_sale_amount,                                                                                                                                                                                                                                                                     ";
        $sql  .= "sum(t_data.prod_pure_total)   as prod_pure_total_total,                                                                                                                                                                                                                                                                ";
        $sql  .= "sum(t_data.prod_pure_total_c) as prod_pure_total_c,                                                                                                                                                                                                                                                                    ";
        $sql  .= "sum(t_data.pure_total)	      as pure_total,                                                                                                                                                                                                                                                                         ";
        $sql  .= "sum(t_data.prod_profit)       as prod_profit,                                                                                                                                                                                                                                                                          ";
        $sql  .= "case when sum(t_data.prod_sale_amount) = 0 then 0 else (sum(t_data.pure_total)-sum(t_data.prod_profit))/sum(t_data.prod_sale_amount) end as res1,                                                                                                                                                                      ";
        $sql  .= "case when sum(t_data.prod_sale_amount) = 0 then 0 else (sum(t_data.pure_total))/sum(t_data.prod_sale_amount) end as res2,                                                                                                                                                                                              ";
        $sql  .= "case when sum(t_data.pure_total) = 0 then 0 else (sum(t_data.prod_profit))/sum(t_data.pure_total)*100 end as res3                                                                                                                                                                                                      ";
        $sql  .= "from (                                                                                                                                                                                                                                                                                                                 ";
        $sql  .= "select                                                                                                                                                                                                                                                                                                                 ";
        $sql  .= "j.proc_date ,                                                                                                                                                                                                                                                                                                          ";
        $sql  .= "j.organization_id as org_id ,                                                                                                                                                                                                                                                                                          ";
        $sql  .= "coalesce(o.abbreviated_name,'') as org_nm ,                                                                                                                                                                                                                                                                            ";
        $sql  .= "j.prod_cd ,                                                                                                                                                                                                                                                                                                            ";
        $sql  .= "coalesce(replace(m.prod_nm, '\"', '\\\"'),'') as prod_nm,                                                                                                                                                                                                                                                              ";
        $sql  .= "coalesce(m.prod_tax,0) as  prod_tax,                                                                                                                                                                                                                                                                                   ";
        $sql  .= "coalesce(m.prod_capa_kn,'') as prod_capa_nm ,                                                                                                                                                                                                                                                                          ";
        $sql  .= "coalesce(sp.sale_amount , j.prod_sale_amount) as prod_sale_amount,                                                                                                                                                                                                                                                     ";
        $sql  .= "coalesce(sp.pure_total, j.prod_pure_total) as prod_pure_total,                                                                                                                                                                                                                                                         ";
        $sql  .= "j.prod_pure_total as prod_pure_total_c,                                                                                                                                                                                                                                                                                ";
        $sql  .= "sp.pure_total,                                                                                                                                                                                                                                                                                                         ";
        $sql  .= "coalesce(sp.sale_profit , j.prod_profit) as prod_profit,                                                                                                                                                                                                                                                               ";
        $sql  .= "m.head_supp_cd as supp_cd ,                                                                                                                                                                                                                                                                                            ";
        $sql  .= "m.sect_cd ,                                                                                                                                                                                                                                                                                                            ";
        $sql  .= "m.maker_cd ,                                                                                                                                                                                                                                                                                                           ";
        $sql  .= "m.prod_t_cd1,                                                                                                                                                                                                                                                                                                          ";
        $sql  .= "m.prod_t_cd2,                                                                                                                                                                                                                                                                                                          ";
        $sql  .= "m1.sect_nm,                                                                                                                                                                                                                                                                                                            ";
        $sql  .= "m2.supp_nm,                                                                                                                                                                                                                                                                                                            ";
        $sql  .= "m3.maker_nm,                                                                                                                                                                                                                                                                                                           ";
        $sql  .= "MST0801_1.prod_t_nm as prod_t_nm1,                                                                                                                                                                                                                                                                                     ";
        $sql  .= "MST0801_2.prod_t_nm as prod_t_nm2,                                                                                                                                                                                                                                                                                     ";
        $sql  .= "MST0801_3.prod_t_nm as prod_t_nm3,                                                                                                                                                                                                                                                                                     ";
        $sql  .= "MST0801_4.prod_t_nm as prod_t_nm4,                                                                                                                                                                                                                                                                                     ";
        $sql  .= "coalesce(sp.special, 0) as special                                                                                                                                                                                                                                                                                     ";
        $sql  .= "from jsk5110 j                                                                                                                                                                                                                                                                                                         ";
        $sql  .= "left join m_organization_detail o on (o.organization_id = j.organization_id)                                                                                                                                                                                                                                           ";
        $sql  .= "left join mst0201 m on (m.organization_id = j.organization_id and m.prod_cd = j.prod_cd)                                                                                                                                                                                                                               ";
        $sql  .= "left join mst1201 m1 on (m.organization_id = m1.organization_id and m.sect_cd = m1.sect_cd)                                                                                                                                                                                                                            ";
        $sql  .= "left join mst1101 m2 on (m.organization_id = m2.organization_id and m.head_supp_cd = m2.supp_cd)                                                                                                                                                                                                                       ";
        $sql  .= "left join mst1001 m3 on (m.organization_id = m3.organization_id and m.maker_cd = m3.maker_cd)                                                                                                                                                                                                                          ";
        $sql  .= "left join (select * from MST0801 where prod_t_cd2 = '' and prod_t_cd3 = '' and prod_t_cd4 = '') as MST0801_1 on (MST0801_1.prod_t_cd1 = m.prod_t_cd1 and MST0801_1.organization_id = m.organization_id)                                                                                                                ";
        $sql  .= "left join (select * from MST0801 where prod_t_cd3 = '' and prod_t_cd4 = '') as MST0801_2 on (MST0801_2.prod_t_cd1 = m.prod_t_cd1 and MST0801_2.prod_t_cd2 = m.prod_t_cd2 and MST0801_2.organization_id = m.organization_id)                                                                                            ";
        $sql  .= "left join (select * from MST0801 where prod_t_cd4 = '' ) as MST0801_3 on (MST0801_3.prod_t_cd1 = m.prod_t_cd1 and MST0801_3.prod_t_cd2 = m.prod_t_cd2 and MST0801_3.prod_t_cd3 = m.prod_t_cd3 and MST0801_3.organization_id = m.organization_id)                                                                       ";
        $sql  .= "left join (select * from MST0801 where prod_t_cd4 != '' ) as MST0801_4 on (MST0801_4.prod_t_cd1 = m.prod_t_cd1 and MST0801_4.prod_t_cd2 = m.prod_t_cd2 and MST0801_4.prod_t_cd3 = m.prod_t_cd3 and MST0801_4.prod_t_cd4 = m.prod_t_cd4 and MST0801_4.organization_id = m.organization_id)                              ";
        $sql  .= "left join (                                                                                                                                                                                                                                                                                                            ";
        $sql  .= "select                                                                                                                                                                                                                                                                                                                 ";
        $sql  .= "distinct(organization_id),                                                                                                                                                                                                                                                                                             ";
        $sql  .= "proc_date,                                                                                                                                                                                                                                                                                                             ";
        $sql  .= "prod_cd,                                                                                                                                                                                                                                                                                                               ";
        $sql  .= "(case sale_plan_cd when '' then 0 else 1 end ) as special,                                                                                                                                                                                                                                                             ";
        $sql  .= "sum(pure_total) as pure_total,                                                                                                                                                                                                                                                                                         ";
        $sql  .= "sum(sale_amount) as sale_amount,                                                                                                                                                                                                                                                                                       ";
        $sql  .= "sum(sale_profit) as sale_profit                                                                                                                                                                                                                                                                                        ";
        $sql  .= "from                                                                                                                                                                                                                                                                                                                   ";
        $sql  .= "jsk1140                                                                                                                                                                                                                                                                                                                ";
        $sql  .= "where                                                                                                                                                                                                                                                                                                                  ";
        $sql  .= "sale_plan_cd ~ '^[0-9]*$'                                                                                                                                                                                                                                                                                              ";
        $sql  .= " and proc_date between :startdate and :enddate                                                                                                                                                                                                                                                            ";
                if($searchparam['org_r'] === ''){
              if( $searchparam['org_select'] === 'empty'){
                  $sql .= " and organization_id in (".$searchparam['org_id'].")";
              }else{
                  $sql .= " and organization_id in (".$searchparam['org_select'].")";
              }
          }
          if($searchparam['prod_r'] === ''){
              if( $searchparam['prod_select'] === 'empty'){
                  $sql .= " and prod_cd in (".$searchparam['prod_cd'].")";
              }else{
                  $sql .= " and prod_cd in ('".$searchparam['prod_select']."')";
              }
          }  
        $sql  .= "group by                                                                                                                                                                                                                                                                                                               ";
        $sql  .= "organization_id,                                                                                                                                                                                                                                                                                                       ";
        $sql  .= "proc_date,                                                                                                                                                                                                                                                                                                             ";
        $sql  .= "prod_cd,                                                                                                                                                                                                                                                                                                               ";
        $sql  .= "special) sp on (sp.organization_id = j.organization_id and sp.prod_cd = j.prod_cd and sp.proc_date = j.proc_date )                                                                                                                                                                                                     ";
        $sql  .= "where                                                                                                                                                                                                                                                                                                                  ";
        $sql  .= "1 = :val1                                                                                                                                                                                                                                                                                                                 ";
        $sql  .= "and( j.prod_pure_total <> 0                                                                                                                                                                                                                                                                                            ";
        $sql  .= "or j.prod_sale_total <> 0                                                                                                                                                                                                                                                                                              ";
        $sql  .= "or j.prod_profit <> 0)                                                                                                                                                                                                                                                                                                 ";
        $sql  .= " and j.proc_date between :startdate and :enddate                                                                                                                                                                                                                                                          ";
      //  $sql  .= "                                                                                                                                                                                                                                                                                                                       ";
                 if($searchparam['org_r'] === ''){
                if( $searchparam['org_select'] === 'empty'){
                    $sql .= " and j.organization_id in (".$searchparam['org_id'].")";
                }else{
                    $sql .= " and j.organization_id in (".$searchparam['org_select'].")";
                }
            }
            if($searchparam['prod_r'] === ''){
                if( $searchparam['prod_select'] === 'empty'){
                    $sql .= " and j.prod_cd in (".$searchparam['prod_cd'].")";
                }else{
                    $sql .= " and j.prod_cd in ('".$searchparam['prod_select']."')";
                }
            }
            if($searchparam['appoprod_r'] === ''){
                if( $searchparam['appoprod_select'] === 'empty'){
                    $sql .= " and m.appo_prod_kbn in (".$searchparam['appoprod_cd'].")";
                }else{
                    $sql .= " and m.appo_prod_kbn in ('".$searchparam['appoprod_select']."')";
                }
            }            
            if($searchparam['sect_r'] === ''){
                if( $searchparam['sect_select'] === 'empty'){
                    $sql .= " and m.sect_cd in (".$searchparam['sect_cd'].")";
                }else{
                    $sql .= " and m.sect_cd in ('".$searchparam['sect_select']."')";
                }
            }
            if($searchparam['supp_r'] === ''){
                if( $searchparam['supp_select'] === 'empty'){
                    $sql .= " and m.head_supp_cd in (".$searchparam['supp_cd'].")";
                }else{
                    $sql .= " and m.head_supp_cd in ('".$searchparam['supp_select']."')";
                }
            }
        $sql  .= "order by                                                                                                                                                                                                                                                                                                               ";
        $sql  .= "proc_date,                                                                                                                                                                                                                                                                                                             ";
        $sql  .= "prod_cd                                                                                                                                                                                                                                                                                                                ";
        $sql  .= ") t_data                                                                                                                                                                                                                                                                                                               ";
        $sql  .= "group by true )  as total on (a.org_id = b.org_id)                                                                                                                                                                                                                                                                     ";																																																																										

            $searchArray = [];
            $searchArray[':val1'] = 1 ;
            if($searchparam['period'] === 'day' ){
                $searchArray[':startdate']  = substr(str_replace('/','',$searchparam['start_date']).'0000',0,8);
                $searchArray[':enddate']    = substr(str_replace('/','',$searchparam['end_date']).'9999',0,8); 
            }else if($searchparam['period'] === 'month' ){
                $searchArray[':startdate']  = substr(str_replace('/','',$searchparam['start_dateM']).'0000',0,8);
                $searchArray[':enddate']    = substr(str_replace('/','',$searchparam['end_dateM']).'9999',0,8);                 
            }else{
                $searchArray[':startdate']  = substr(str_replace('/','',$searchparam['start_year']).'0000',0,8);
                $searchArray[':enddate']    = substr(str_replace('/','',$searchparam['end_year']).'9999',0,8);                 
            }
            //echo '$sql';
        
           // print_r($searchArray);
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
            $prev_org_id = -1;
            $sum         = [];
            $sum['prod_sale_amount']    = 0;
            $sum['prod_pure_total']     = 0;
            $sum['prod_pure_total_c']   = 0;
            $sum['pure_total']          = 0;
            $sum['prod_profit']         = 0;
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if($prev_org_id !== $data['org_id'] ){
                    $rank = 1;
                }else{
                    $rank++;
                }
                $sum['sale_amount']    += $data['prod_sale_amount'];
                $sum['pure_total']     += $data['prod_pure_total'];
                //$sum['prod_pure_total_c']   += $data['prod_pure_total_c'];
                //$sum['pure_total']          += $data['pure_total'];
                $sum['profit']         += $data['prod_profit'];                
                $prev_org_id = $data['org_id'];
                    //print_r($data);
                    //$Datas[$data['org_id']][$data['prod_cd']][$data['special']] = $data;
                $data['rank'] = $rank;
                $Datas[$data['org_id']][] = $data;
            }
            $Datas['9999'] = $sum;
            //print_r($sql);
            $Log->trace("END get_data");

            return $Datas;            
        }
        public function get_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_data");
            
            $sql  = "";
            $sql .= " select ";
            $sql .= " 	j.proc_date , ";
            $sql .= " 	j.organization_id as org_id , ";
            $sql .= " 	o.abbreviated_name as org_nm , ";
            $sql .= " 	j.prod_cd , ";
            $sql .= " 	replace(m.prod_nm,'\"','\\\"') as prod_nm, ";
            $sql .= " 	m.prod_tax , ";
            $sql .= " 	m.prod_capa_kn as prod_capa_nm , ";
            $sql .= " 	coalesce(sp.sale_amount ,j.prod_sale_amount) as  prod_sale_amount, ";
            $sql .= " 	coalesce(sp.pure_total,j.prod_pure_total) as prod_pure_total, ";
            $sql .= " 	j.prod_pure_total as prod_pure_total_c, ";
            $sql .= " 	sp.pure_total, ";
            $sql .= " 	coalesce(sp.sale_profit ,j.prod_profit) 	 as  prod_profit, ";
            $sql .= " 	m.head_supp_cd as supp_cd , ";
            $sql .= " 	m.sect_cd , ";
            $sql .= " 	coalesce(sp.special, 0) as special ";
            $sql .= " from ";
            $sql .= " 	jsk5110 j ";
            $sql .= " left join m_organization_detail o on ";
            $sql .= " 	(o.organization_id = j.organization_id) ";
            $sql .= " left join mst0201 m on ";
            $sql .= " 	(m.organization_id = j.organization_id ";
            $sql .= " 	and m.prod_cd = j.prod_cd) ";
            $sql .= " left join ( ";
            $sql .= " 		select distinct(organization_id), ";
            $sql .= " 		proc_date, ";
            $sql .= " 		prod_cd, ";
            $sql .= " 		(case ";
            $sql .= " 			sale_plan_cd ";
            $sql .= " 			when '' then 0 ";
            $sql .= " 			else 1 ";
            $sql .= " 		end ) as special, ";
            $sql .= " 		sum(pure_total)  as pure_total, ";
            $sql .= " 		sum(sale_amount) as sale_amount, ";
            $sql .= " 		sum(sale_profit) as sale_profit ";
            $sql .= " 	from ";
            $sql .= " 		jsk1140 ";
            $sql .= " 	where ";
            $sql .= " 		sale_plan_cd ~ '^[0-9]*$' ";
            $sql .= " 	group by  ";
            $sql .= " 		organization_id, ";
            $sql .= " 		proc_date, ";
            $sql .= " 		prod_cd, ";
            $sql .= " 		special) sp on ";
            $sql .= " 	( sp.organization_id = j.organization_id ";
            $sql .= " 	and sp.prod_cd = j.prod_cd ";
            $sql .= " 	and sp.proc_date = j.proc_date ) ";
            $sql .= " where ";
            $sql .= " 	1 = :val1 ";
            $sql .= " 	and( j.prod_pure_total <> 0 ";
            $sql .= " 	or j.prod_sale_total <> 0 ";
            $sql .= " 	or j.prod_profit <> 0) ";
            $sql .= " order by ";
            $sql .= " 	proc_date, ";
            $sql .= " 	org_id, ";
            $sql .= " 	prod_cd, ";
            $sql .= " 	sect_cd, ";
            $sql .= " 	supp_cd ";

            $searchArray[':val1'] = 1 ;
         //   print_r($sql);
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

        public function getspe_sales_list(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getspe_sales_list");
            $query  = "";
            $query .= " select organization_id, string_agg(prod_cd,',' order by prod_cd) prod_lst ";
            $query .= " from mst1303 where 1 = :val group by organization_id ";
            $param[":val"] = 1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getspe_sales_list");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $Datas[$data["organization_id"]][] = $data["prod_lst"];
            }            
                   
            $Log->trace("END getspe_sales_list");
            return $Datas;
        }
        
        
        
        
        public function getspe_sales_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getspe_sales_detail");
            $query  = "";
            $query .= " select organization_id,prod_cd,sale_str_dt1,sale_end_dt1,sale_str_dt2,sale_end_dt2 ";         
            $query .= " from mst1303 a ";
            $query .= " where 1 = :val ";
            $query .= " order by organization_id, prod_cd ";
            
            $param[":val"] = 1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getspe_sales_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $Datas[$data["organization_id"]][$data["prod_cd"]] = $data;
            }            
                   
            $Log->trace("END getspe_sales_detail");
            return $Datas;
        }         
    }
?>
