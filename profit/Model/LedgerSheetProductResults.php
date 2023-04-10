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
    class LedgerSheetProductResults extends BaseModel
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
        
//        public function getorg_detail(){
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getorgidlist");
//            $query  = "";
//            $query .= " select a.organization_id as org_id";
//            $query .= "   ,b.abbreviated_name as org_nm";
//            $query .= " from mst0010 a ";
//            $query .= " left join m_organization_detail b on b.organization_id = a.organization_id ";
//            $query .= " where 1 = :val ";
//            $query .= " group by a.organization_id, b.abbreviated_name ";
//            $query .= " order by org_id ";
//            
//            $param[":val"] = 1;
//            
//            //print_r($query);
//            //print_r($param);            
//            $result = $DBA->executeSQL($query, $param);
//            //print_r($result);
//            $Datas = array();
//
//            // データ取得ができなかった場合、空の配列を返す
//            if( $result === false )
//            {
//                $Log->trace("END getorgidlist");
//                return $Datas;
//            }
//
//            // 取得したデータ群を配列に格納
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                $Datas[] = $data;
//            }            
//                   
//            $Log->trace("END getorgidlist");
//            return $Datas;
//        }
//        public function getprod_detail(){
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getprod_detail");
//            $query  = "";
//            $query .= " select ";
//            $query .= "    distinct(a.prod_cd) ";
//            $query .= "   ,regexp_replace (a.prod_nm,'/  |\r\n|\n|\r/gm','') as prod_nm ";
//            $query .= "   ,a.prod_kn ";
//            $query .= "   ,a.head_supp_cd ";
//            $query .= "   ,a.sect_cd ";
//            $query .= "   ,a.appo_prod_kbn ";            
//            $query .= " from mst0201 a ";
//            $query .= " where 1 = :val ";
//            $query .= " order by prod_cd ";
//            
//            $param[":val"] = 1;
//            
//            //print_r($query);
//            //print_r($param);            
//            $result = $DBA->executeSQL($query, $param);
//            //print_r($result);
//            $Datas = array();
//
//            // データ取得ができなかった場合、空の配列を返す
//            if( $result === false )
//            {
//                $Log->trace("END getprod_detail");
//                return $Datas;
//            }
//
//            // 取得したデータ群を配列に格納
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                $Datas[] = $data;
//            }            
//                   
//            $Log->trace("END getprod_detail");
//            return $Datas;
//        }         
//        public function getsupp_detail(){
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getsupp_detail");
//            $query  = "";
//            $query .= " select ";
//            $query .= "    a.supp_cd ";
//            $query .= "   ,a.supp_nm ";           
//            $query .= " from mst1101 a ";
//            $query .= " where 1 = :val ";
//            $query .= " group by a.supp_cd ,a.supp_nm ";
//            $query .= " order by supp_cd ";
//            
//            $param[":val"] = 1;
//            
//            //print_r($query);
//            //print_r($param);            
//            $result = $DBA->executeSQL($query, $param);
//            //print_r($result);
//            $Datas = array();
//
//            // データ取得ができなかった場合、空の配列を返す
//            if( $result === false )
//            {
//                $Log->trace("END getsupp_detail");
//                return $Datas;
//            }
//
//            // 取得したデータ群を配列に格納
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                $Datas[] = $data;
//            }            
//                   
//            $Log->trace("END getsupp_detail");
//            return $Datas;
//        }
//        public function getsect_detail(){
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getsect_detail");
//            $query  = "";
//            $query .= " select ";
//            $query .= "    a.sect_cd ";
//            $query .= "   ,a.sect_nm ";           
//            $query .= " from mst1201 a ";
//            $query .= " where 1 = :val ";
//            $query .= " group by a.sect_cd ,a.sect_nm ";
//            $query .= " order by sect_cd ";
//            
//            $param[":val"] = 1;
//            
//            //print_r($query);
//            //print_r($param);            
//            $result = $DBA->executeSQL($query, $param);
//            //print_r($result);
//            $Datas = array();
//
//            // データ取得ができなかった場合、空の配列を返す
//            if( $result === false )
//            {
//                $Log->trace("END getsect_detail");
//                return $Datas;
//            }
//
//            // 取得したデータ群を配列に格納
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                $Datas[] = $data;
//            }            
//                   
//            $Log->trace("END getsect_detail");
//            return $Datas;
//        } 
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
