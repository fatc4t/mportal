<?php
    /**
     * @file      帳票 - コスト
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - コストの管理を行う
     */

    // BaseModel.phpを読み込む


    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class Modal extends BaseModel
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

        
        public function getorg_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getorgidlist");
            $query  = "";
            $query .= " select a.organization_id as org_id";
            $query .= "   ,a.abbreviated_name as org_nm";
            $query .= " from m_organization_detail a ";
            $query .= " where 1 = :val and a.department_code <> '0000'";
            $query .= " order by org_id ";
            
            $param[":val"] = 1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getorgidlist");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //$Datas[] = $data;
                $Datas[$data['org_id']] = $data;
            }            
                   
            $Log->trace("END getorgidlist");
            return $Datas;
        }
        public function getprod_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getprod_detail");
            $query  = "";
            $query .= " select ";
            $query .= "    distinct(a.prod_cd) ";
            $query .= "   ,regexp_replace (replace(coalesce(replace(a.prod_nm,'\"','\\\"'),''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as prod_nm  ";
            $query .= "   ,regexp_replace (replace(replace(coalesce(a.prod_kn,''),'''',' '),'\"','\\\"'),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as prod_kn ";
            $query .= "   ,a.head_supp_cd as supp_cd";
            $query .= "   ,a.sect_cd ";
            $query .= "   ,a.appo_prod_kbn ";            
            $query .= " from mst0201 a ";
			//kanderu 20211129
            $query .= " where a.organization_id = :val ";
            $query .= " order by prod_cd ";
            //$query .= " offset 70000 limit 10000 ";
            $param[":val"] = -1;
            //kanderu 20211129
            //print_r($query);
            //print_r($param); 
			//kanderu 20211129
            $result = $DBA->executeSQL($query,$param);	
            //$result = $DBA->executeSQL($query);
			//kanderu 20211129
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getprod_detail");
                return $Datas;
            }
            $i = 1;
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                
                if(!$Datas[$data['prod_cd']]){
                    $Datas[$data['prod_cd']] = $data;
                    $i = 1;
                }else{
                    $Datas[$data['prod_cd'].'#'.$i] = $data;
                    $i++; 
                }
            }            
            //print_r($Datas);       
            $Log->trace("END getprod_detail");
            return $Datas;
        }         
        public function getsupp_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getsupp_detail");
            $query  = "";
            $query .= " select ";
            $query .= "    a.supp_cd ";
            $query .= "   ,regexp_replace (replace(replace(coalesce(a.supp_nm,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as supp_nm ";           
            $query .= " from mst1101 a ";
            $query .= " where a.organization_id = :val ";
            $query .= " group by a.supp_cd ,coalesce(a.supp_nm,'') ";
            $query .= " order by supp_cd ";
            
            $param[":val"] = -1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getsupp_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //$Datas[] = $data;
                $Datas[$data['supp_cd']] = $data;
            }            
                   
            $Log->trace("END getsupp_detail");
            return $Datas;
        }
        /*ADDSTR 20200707 bhattarai 社内仕入先取得*/
        public function getInSupp_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getInSupp_detail");
            $query  = "";
            $query .= " select ";
            $query .= "    a.supp_cd ";
            $query .= "   ,regexp_replace (replace(replace(coalesce(a.supp_nm,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as supp_nm ";           
            $query .= " from mst1101 a ";
            $query .= " where a.organization_id = :val ";
            $query .= " and supp_kbn = '0' ";
            $query .= " group by a.supp_cd ,coalesce(a.supp_nm,'') ";
            $query .= " order by supp_cd ";
            $param[":val"] = -1;            
            $result = $DBA->executeSQL($query, $param);
            $Datas = array();
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getInSupp_detail");
                return $Datas;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //$Datas[] = $data;
                $Datas[$data['supp_cd']] = $data;
            }      
            $Log->trace("END getInSupp_detail");
            return $Datas;
        }
/*ADDEND*/        
        public function getsect_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getsect_detail");
            $query  = "";
            $query .= " select ";
            $query .= "    a.sect_cd ";
            $query .= "   ,regexp_replace (replace(replace(coalesce(a.sect_nm,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as sect_nm ";           
            $query .= " from mst1201 a ";
            $query .= " where a.organization_id = :val ";
            $query .= " group by a.sect_cd ,coalesce(a.sect_nm,'') ";
            $query .= " order by sect_cd ";
            
            $param[":val"] = -1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getsect_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //$Datas[] = $data;
                $Datas[$data['sect_cd']] = $data;
            }            
                   
            $Log->trace("END getsect_detail");
            return $Datas;
        }
        public function getarea_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getarea_detail");
            $query  = "";
            $query .= " select ";
            $query .= "    a.area_cd ";
            $query .= "   ,regexp_replace (replace(replace(coalesce(a.area_nm,a.area_kn,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as area_nm ";           
            $query .= " from mst0501 a ";
            $query .= " where a.organization_id = :val ";
            $query .= " group by a.area_cd ,coalesce(a.area_nm,a.area_kn,'') ";
            $query .= " order by area_cd ";
            
            $param[":val"] = -1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getarea_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //$Datas[] = $data;
                $Datas[$data['area_cd']] = $data;
            }            
                   
            $Log->trace("END getarea_detail");
            return $Datas;
        }
        public function getcusttype_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getcusttype_detail");
            $query  = "";
            $query .= " select ";
            $query .= "    a.cust_type_cd ";
            $query .= "   ,regexp_replace (replace(replace(coalesce(a.cust_type_nm,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as cust_type_nm ";           
            $query .= " from mst0401 a ";
            $query .= " where a.organization_id = :val ";
            $query .= " group by a.cust_type_cd ,coalesce(a.cust_type_nm,'') ";
            $query .= " order by cust_type_cd ";
            
            $param[":val"] = -1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getcusttype_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //$Datas[] = $data;
                $Datas[$data['cust_type_cd']] = $data;
            }            
                   
            $Log->trace("END getcusttype_detail");
            return $Datas;
        }
        public function getstaff_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getstaff_detail");
            $query  = "";
            $query .= " select ";
            $query .= "    a.staff_cd ";
            $query .= "   ,regexp_replace (replace(replace(coalesce(a.staff_nm,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as staff_nm ";           
            $query .= " from mst0601 a ";
            $query .= " where a.organization_id = :val ";
            $query .= " group by a.staff_cd ,coalesce(a.staff_nm,'') ";
            $query .= " order by staff_cd ";
            
            $param[":val"] = -1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getstaff_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //$Datas[] = $data;
                $Datas[$data['staff_cd']] = $data;
            }            
                   
            $Log->trace("END getstaff_detail");
            return $Datas;
        } 
        public function getsplan_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getsplan_detail");
            $query  = "";
            $query .= " select ";
            $query .= " 	 sale_plan_cd as splan_cd ";
            $query .= " 	,regexp_replace (replace(replace(coalesce(sale_plan_nm,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','')  as splan_nm ";
            $query .= " 	,to_char(sale_plan_str_dt::date,'YYYY/MM/DD') as splan_dt_start ";
            $query .= " 	,to_char(sale_plan_end_dt::date,'YYYY/MM/DD') as splan_dt_end ";
            $query .= " from mst1301  ";
            $query .= " where organization_id = :val ";
            $query .= " order by sale_plan_cd ";
            
            $param[":val"] = -1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getsplan_detail");
                return $Datas;
            }
            $init_row=[];
            $init_row['splan_cd'] = "0000";
            $init_row['splan_nm'] = '簡易特売';
            $init_row['splan_dt_start'] = '';
            $init_row['splan_dt_end'] = '';
            $Datas['0000'] = $init_row;
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //$Datas[] = $data;
                $Datas[$data['splan_cd']] = $data;
            }            
            //print_r($Datas);       
            $Log->trace("END getsplan_detail");
            return $Datas;
        }
        public function getcust_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getcust_detail");
            $query  = "";
            $query .= " select ";
            $query .= "    distinct(a.cust_cd) ";
            $query .= "   ,regexp_replace (replace(replace(coalesce(a.cust_nm,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as cust_nm  ";
            $query .= "   ,regexp_replace (replace(replace(coalesce(a.cust_kn,''),'''',' '),'\"','\\\"'),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as cust_kn ";
            $query .= "   ,regexp_replace (replace(coalesce(a.tel,''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as tel ";
            $query .= "   ,regexp_replace (replace(coalesce(a.tel4,''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as tel4 ";         
            $query .= "   ,regexp_replace (replace(coalesce(a.cust_sumday,''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as cust_sumday ";         
            $query .= " from mst0101 a ";
            $query .= " where 1 = :val ";
            $query .= " order by cust_cd ";
            //$query .= " offset  52075 limit 10 ";
            $param[":val"] = 1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getcust_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //$Datas[] = $data;
                $Datas[$data['cust_cd']] = $data;
            }            
                   
            $Log->trace("END getcust_detail");
            //print_r($Datas);
            return $Datas;
        }       
        public function getprod_class_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getprod_class_detail");
            $query  = "";
            $query  .= "select case when a.prod_t_cd2 = '' then a.prod_t_cd1 else a.prod_t_cd1 || '_' || a.prod_t_cd2 end as prodclass_cd,a.prod_t_nm as prodclass_nm,a.prod_t_cd1,c.prod_t_nm as prod_t_nm1,a.prod_t_cd2, ";
            $query  .= "case when a.prod_t_cd2 = '' then '' ";
            $query  .= "	 else a.prod_t_nm ";
            $query  .= "end as prod_t_nm2 ";
            $query  .= "from mst0801 a ";
            $query  .= "left join mst0801 c on c.organization_id = a.organization_id and c.prod_t_cd1 = a.prod_t_cd1 and c.prod_t_cd2 = '' and c.prod_t_cd3 = '' and c.prod_t_cd4 = '' ";
            $query  .= "where a.organization_id = :val and a.prod_t_cd3 = '' ";
            $query  .= "group by a.prod_t_nm,c.prod_t_nm,a.prod_t_cd1,a.prod_t_cd2 ";
            $query  .= "order by prod_t_cd1,prod_t_cd2 ";
            //$query .= " offset  52075 limit 10 ";
            $param[":val"] = -1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getprod_class_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if($data["prod_t_cd2"] === ''){
                   //$Datas["ooki"][] = $data;  
                    $Datas["ooki"][$data['prodclass_cd']] = $data;
                }else{
                   //$Datas["naka"][] = $data; 
                    $Datas["naka"][$data['prodclass_cd']] = $data;
                }
                
            }            
                   
            $Log->trace("END getprod_class_detail");
            return $Datas;
        }           
    
        public function getappoprod_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getappoprod_detail");
            $query   = "";
            $query  .= " select distinct(appo_prod_kbn) as appoprod_cd,appo_prod_kbn as appoprod_nm ";
            $query  .= " from mst0201 ";
            $query  .= " where coalesce(appo_prod_kbn,'') <> '' and  organization_id = :val ";
            $query  .= " order by appo_prod_kbn ";
            $param[":val"] = -1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getappoprod_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //$Datas[] = $data;
                $Datas[$data['appoprod_cd']] = $data;
            }            
                   
            $Log->trace("END getappoprod_detail");
            return $Datas;
        } 
        /*修正者：バッタライ
　　　　　修正日：2020/02/06
          修正内容：クレジット情報取得　*/
         public function getcredit_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getcredit_detail");
            $query  = "";
            $query .= " select ";
            $query .= "    a.credit_cd ";
            $query .= "   ,regexp_replace (replace(replace(coalesce(a.credit_nm,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as credit_nm ";           
            $query .= " from mst5601 a ";
            $query .= " where a.organization_id = :val ";
            $query .= " group by a.credit_cd ,coalesce(a.credit_nm,'') ";
            $query .= " order by credit_cd ";
            
            $param[":val"] = -1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getcredit_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //$Datas[] = $data;
                $Datas[$data['credit_cd']] = $data;
            }
            /*修正日：20200316　修正者：バッタライ
　　　　　　　修正内容：モーダルに 商品券金額やポイント金額や現金のコードと名前の追加*/
             $Datas['G1']['credit_cd']='G1';
             $Datas['G1']['credit_nm']= '商品券金額';
             $Datas['P1']['credit_cd']='P1';
             $Datas['P1']['credit_nm']= 'ポイント金額';
             $Datas['C1']['credit_cd']='C1';
             $Datas['C1']['credit_nm']= '現金';
            $Log->trace("END getcredit_detail");
            return $Datas;
        }
    }
?>