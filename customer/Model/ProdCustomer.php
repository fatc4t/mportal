<?php
    /**
     * @file      顧客情報
     * @author    K.Sakamoto
     * @date      2017/08/21
     * @version   1.00
     * @note      顧客情報テーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 顧客情報クラス
     * @note   顧客情報テーブルの管理を行う。
     */
    class ProdCustomer extends BaseModel
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
         * 
         * @param    
         * @return   SQLの実行結果
         */
        public function getorg_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getorgidlist");
            $query  = "";
            $query .= " select a.organization_id ";
            $query .= "   ,b.organization_name";
            $query .= " from mst0010 a ";
            $query .= " left join m_organization_detail b on b.organization_id = a.organization_id ";
            $query .= " where 1 = :val ";
            $query .= " group by a.organization_id, b.organization_name ";
            $query .= " order by organization_id ";
            
            $param[":val"] = 1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $prodcustomerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getorgidlist");
                return $prodcustomerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $prodcustomerDataList[] = $data;
            }            
                   
            $Log->trace("END getorgidlist");
            return $prodcustomerDataList;
        }
 
        public function getCustomerlist()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");

            $sql = " SELECT string_agg(cust_cd,',') as list from mst0101 where 1 = :cust_cd ";            
            $searchArray = array(':cust_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            //print_r($query);
            //print_r($result); 
            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList = $data;
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        }
        
        public function getProdlist($org_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getProdlist");

            $sql = " SELECT string_agg(prod_cd,',') as list from mst0201 where organization_id = :org_id  ";            
            $searchArray = array(':org_id' => $org_id);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getProdlist");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList = $data;
            }

            $Log->trace("END getProdlist");

            return $customerDataList;
        }        
 
        public function getmst1701($cust_cd,$org_id,$prod_cd = "")
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getmst1701");
            $searchArray = [];
            $sql = " select a.organization_id, a.bycust_saleprice, a.prod_cd, a.cust_cd,a.upddatetime, b.prod_nm, b.head_costprice, b.saleprice";
            $sql.= " from MST1701 a ";
            $sql.= " left join MST0201 b on  (a.PROD_CD = b.PROD_CD and b.PROD_NM LIKE '%%' and a.organization_id = b.organization_id) ";
            $sql.= " where a.DISABLED = :DISABLED ";
            $sql.= "    and a.cust_cd = :cust_cd ";
            $sql.= "    and a.organization_id = :org_id ";
            if($prod_cd !== ""){
                 $sql.= "    and a.prod_cd = :prod_cd ";
                 $searchArray[":prod_cd"] = $prod_cd;
            }
            else{
                $sql.= " order by a.prod_cd,a.organization_id ";
            }
            $searchArray [':DISABLED'] = '0';
            $searchArray[":cust_cd"] = $cust_cd;
            $searchArray[":org_id"] = $org_id;
            //print_r($sql);
            //print_r($searchArray);         
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getmst1701");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList[] = $data;
            }

            $Log->trace("END getmst1701");

            return $customerDataList;
        } 

         public function getdata($cust_cd,$org_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getdata");
            $searchArray = [];
            $sql = " select a.bycust_saleprice, a.prod_cd,'old' as state";
            $sql.= " from MST1701 a ";
            $sql.= " where a.DISABLED = :DISABLED ";
            $sql.= "    and a.cust_cd = :cust_cd ";
            $sql.= "    and a.organization_id = :org_id ";
            $sql.= " order by a.prod_cd,a.organization_id ";
            $searchArray [':DISABLED'] = '0';
            $searchArray[":cust_cd"] = $cust_cd;
            $searchArray[":org_id"] = $org_id;
         
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            //print_r($sql);
            //print_r($searchArray);
            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getdata");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList[] = $data;
            }

            $Log->trace("END getdata");

            return $customerDataList;
        } 

        public function getCustomersearchdata($cond)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomersearchdata");

            $query = " SELECT cust_cd,cust_nm,cust_kn,tel,tel4 from mst0101 where 1 = :val ";            
            foreach($cond as $key=>$value) {
               
            $query .= " and ".$key." = :".$key;
            $param[":".$key] = $value;
        
            }
            $param[":val"] = 1;

            // SQLの実行
            $result = $DBA->executeSQL($query, $param);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomersearchdata");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList = $data;
            }

            $Log->trace("END getCustomersearchdata");

            return $customerDataList;
        } 
        
        public function getProdsearchdata($cond)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getProdsearchdata");
            $searchArray = [];
            $sql  = "";
            $sql .= " select distinct MST0201.PROD_CD ,MST0201.PROD_NM ,MST0201.PROD_KN ,MST0201.SECT_CD ,TRN1101.SUPP_CD ";
            $sql .= " ,MST0201.saleprice, MST0201.head_costprice";
            $sql .= " from MST0201 ";
            $sql .= " left outer join trn1102 on (trn1102.prod_cd = MST0201.PROD_CD ) ";
            $sql .= " left outer join trn1101 on (trn1101.hideseq = trn1102.hideseq ) ";
            $sql .= " where 1 = :val and MST0201.PROD_CD is not null ";            
            foreach($cond as $key=>$value) {
                if($key === "SUPP_CD"){
                    $sql .= " and TRN1101.".$key." = '".$value."' ";
                    //$searchArray[":".$key] = $value;                     
                }else{
                    $sql .= " and MST0201.".$key." = '".$value."' ";
                    //$searchArray[":".$key] = $value;        
                }
            }
            $sql .= " order by MST0201.PROD_CD desc";
            $searchArray[":val"] = 1;
            //print_r($sql);
            //print_r($searchArray);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            
            // 一覧表を格納する空の配列宣言
            $customerDataList = array();
            
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getProdsearchdata");
                return $customerDataList;
            }
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList[] = $data;
            }

            $Log->trace("END getProdsearchdata");

            return $customerDataList;
        }         
        
        /**
         * 
         * @param    
         * @return   SQLの実行結果
         */
        public function getmst0010($org_id){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getmst0010");
            $query  = "";
            $query .= " select a.*, b.organization_name ";
            $query .= " from mst0010 a ";
            $query .= " left join m_organization_detail b on a.organization_id = b.organization_id";
            $query .= " where a.organization_id = :org_id order by organization_id";

            $param[":org_id"] = $org_id;
            //print_r($org_id);
            //print_r($param);
            $result = $DBA->executeSQL($query, $param);
            echo "getmst0010(".$org_id.")";
            //print_r($result);
            $prodcustomerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getmst0010");
                return $prodcustomerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $prodcustomerDataList = $data;
            }            
                   
            $Log->trace("END getmst0010");
            return $prodcustomerDataList;
        }
        
        /**
         * 
         * @param    
         * @return   SQLの実行結果
         */        
        public function getrankdata(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getrankdata"); 
            $query  = "";
            $query .= " select cust_rank_nm,cust_rank_cd ";
            $query .= " from mst8401 ";
            $query .= " where 1 = :val order by cust_rank_cd ";
            $param[":val"] = 1;
            
            $result = $DBA->executeSQL($query, $param); 
            
            $prodcustomerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getrankdata");
                return $prodcustomerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $prodcustomerDataList[] = $data;
            }            
                   
            $Log->trace("END getrankdata");    
            return $prodcustomerDataList;
        }
        
         public function updatedata($cust_cd,$org_id,$prod_cd,$bycust)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START updatedata");        
            $query  = "";
            $query .= "update ". $_SESSION["SCHEMA"].".mst1701 set  ";
            $query .= "UPDUSER_CD        = 	:UPDUSER_CD        , ";
            $query .= "UPDDATETIME       = 	:UPDDATETIME       , ";
            $query .= "bycust_saleprice  = 	:bycust_saleprice    ";
            $query .= " where organization_id = :organization_id";
            $query .= " and cust_cd = :cust_cd ";
            $query .= " and prod_cd = :prod_cd ";             

            $param[":UPDUSER_CD"]	= $_SESSION["USER_ID"];
            $param[":UPDDATETIME"]	= "now()";
            $param[":organization_id"]	= $org_id;
            $param[":cust_cd"]          = $cust_cd;
            $param[":prod_cd"]          = $prod_cd;
            $param[":bycust_saleprice"] = $bycust;
            //print_r($query);
            //print_r($param); 
            $result = $DBA->executeSQL_no_searchpath($query, $param);                    
//print_r($result);  
            $Log->trace("END updatedata"); 
        }

         public function insertdata($cust_cd,$org_id,$prod_cd,$bycust)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START insertdata");        
            $query  = "";
            $query .= " insert into ". $_SESSION["SCHEMA"].".mst1701 ";
            $query .= " (insuser_cd,insdatetime,upduser_cd,upddatetime,disabled";
            $query .= "  ,lan_kbn,organization_id,cust_cd,prod_cd,bycust_saleprice) values ";
            $query .= " (:insuser_cd,:insdatetime,:upduser_cd,:upddatetime,:disabled";
            $query .= "  ,:lan_kbn,:organization_id,:cust_cd,:prod_cd,:bycust_saleprice)";                         

            $param[":insuser_cd"]	= $_SESSION["USER_ID"];
            $param[":insdatetime"]	= "now()";
            $param[":upduser_cd"]	= $_SESSION["USER_ID"];
            $param[":upddatetime"]	= "now()";
            $param[":disabled"]         = "0";
            $param[":lan_kbn"]          = "0";
            $param[":organization_id"]	= $org_id;
            $param[":cust_cd"]          = $cust_cd;
            $param[":prod_cd"]          = $prod_cd;
            $param[":bycust_saleprice"] = $bycust;
            //print_r($query);
            //print_r($param); 
            $result = $DBA->executeSQL_no_searchpath($query, $param);                    
//print_r($result);  
            $Log->trace("END insertdata"); 
        }        
        
        public function deletedata($cust_cd,$org_id,$prod_cd){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START deletedata");  
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst1701 ";
            $query .= " set UPDUSER_CD='delete' where organization_id = :org_id ";
            $query .= " and cust_cd = :cust_cd ";
            $query .= " and prod_cd = :prod_cd ";
            $param[":org_id"]  = $org_id;
            $param[":cust_cd"] = $cust_cd;
            $param[":prod_cd"] = $prod_cd;
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".mst1701 ";
            $query .= " where organization_id = :org_id ";
            $query .= " and cust_cd = :cust_cd ";
            $query .= " and prod_cd = :prod_cd ";            
            $result = $DBA->executeSQL_no_searchpath($query, $param);            
            
        }

        public function search_getCustomersearchdata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START search_getCustomersearchdata");

            $sql = ' SELECT cust_cd,cust_nm,cust_kn,tel,tel4 from mst0101 where 1 = :cust_cd order by cust_cd desc';            
            $searchArray = array(':cust_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END search_getCustomersearchdata");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $customerDataList, $data);
            }

            $Log->trace("END search_getCustomersearchdata");

            return $customerDataList;
        }        
        
        
        public function search_getCustomerlist()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START search_getCustomerlist");

            $sql = " SELECT string_agg(cust_cd,',') as list from mst0101 where 1 = :cust_cd ";            
            $searchArray = array(':cust_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END search_getCustomerlist");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList = $data;
            }

            $Log->trace("END search_getCustomerlist");

            return $customerDataList;
        } 
        
        public function getmst1101($org_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getmst1101");

            $sql = ' SELECT supp_cd,supp_nm from mst1101 where organization_id = :org_id order by supp_cd';            
            $searchArray = array(':org_id' => $org_id);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getmst1101");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList[]= $data;
            }

            $Log->trace("END getmst1101");

            return $customerDataList;
        }  
        public function getmst1201($org_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getmst1201");

            $sql = ' SELECT sect_cd,sect_nm from mst1201 where organization_id = :org_id order by sect_cd';            
            $searchArray = array(':org_id' => $org_id);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getmst1201");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList[]= $data;
            }

            $Log->trace("END getmst1201");

            return $customerDataList;
        }         
        
    }
?>