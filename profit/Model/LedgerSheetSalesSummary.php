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
    class LedgerSheetSalesSummary extends BaseModel
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
            $sql .= " 	substring(j.proc_date,0,7) as proc_date , ";
            $sql .= " 	j.organization_id as org_id , ";
            $sql .= " 	o.abbreviated_name as org_nm , ";
            $sql .= " 	coalesce(m.sect_cd,'なし') as sect_cd , ";
            $sql .= " 	ms.sect_nm , ";
            $sql .= " 	j.prod_cd , ";
            $sql .= " 	replace(m.prod_nm,'\"','\\\"') as prod_nm, ";
            $sql .= " 	m.prod_tax , ";
            $sql .= " 	sum(j.prod_sale_amount) as prod_sale_amount ,  ";
            $sql .= " 	sum(j.prod_sale_total) as  prod_sale_total, ";
            $sql .= " 	sum(j.prod_profit) as prod_profit, ";
            $sql .= " 	coalesce(sp.special,0) as special ";
            $sql .= " from ";
            $sql .= " 	jsk5110 j ";
            $sql .= " left join m_organization_detail o on ";
            $sql .= " 	(o.organization_id = j.organization_id) ";
            $sql .= " left join mst0201 m on ";
            $sql .= " 	(m.organization_id = j.organization_id ";
            $sql .= " 	and m.prod_cd = j.prod_cd) ";
            $sql .= " left join mst1201 ms on (ms.organization_id = m.organization_id ";
            $sql .= " 	and ms.sect_cd = m.sect_cd) ";
            $sql .= " left join (select distinct(organization_id), proc_date, prod_cd, ";
            $sql .= " 			(case sale_plan_cd when '' then 0 else 1 end ) as special "; 
            $sql .= " 			from jsk1140 where sale_plan_cd ~ '^[0-9]*$') sp ";
            $sql .= " 	on (	sp.organization_id = j.organization_id ";
            $sql .= " 		and sp.prod_cd = j.prod_cd ";
            $sql .= " 		and sp.proc_date = j.proc_date ";
            $sql .= " 	)  ";
            $sql .= " where ";
            $sql .= " 	1 = :val1 ";
            $sql .= " 	and (j.prod_sale_amount <> 0 ";
            $sql .= " 	or j.prod_sale_total  <> 0 ";
            $sql .= " 	or j.prod_profit 	   <> 0) ";
            $sql .= " group by 	 ";
            $sql .= " 	substring(j.proc_date,0,7), ";
            $sql .= " 	org_id, ";
            $sql .= " 	j.prod_cd, ";
            $sql .= " 	m.sect_cd, ";
            $sql .= " 	sect_nm, ";
            $sql .= " 	prod_nm, ";
            $sql .= " 	prod_tax, ";
            $sql .= " 	org_nm, ";
            $sql .= " 	coalesce(sp.special,0) ";
            $sql .= " order by ";
            $sql .= " 	proc_date, ";
            $sql .= " 	org_id,	 ";
            $sql .= " 	sect_cd, ";
			$sql .= " 	prod_cd, ";
            $sql .= " 	special ";

            $searchArray[':val1'] = 1 ;
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
