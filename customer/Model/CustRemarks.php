<?php
    /**
     * @file      顧客情報
     * @author    K.Sakamoto
     * @date      2017/08/21
     * @version   1.00
     * @note      顧客情報テーブルの管理を行う
     */

    // CustomerInputData.phpを読み込む
    require './Model/BaseCustomer.php';

    /**
     * 顧客情報クラス
     * @note   顧客情報テーブルの管理を行う。
     */
    class CustRemarks extends BaseCustomer
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
            $query .= " select a.organization_id, a.abbreviated_name as organization_name ";
            $query .= " from m_organization_detail a ";
            $query .= " where 1 = :val and a.department_code <> '0000'";
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
        /**
         * 地区データを獲得します
         * @param    
         * @return   SQLの実行結果
         */
        public function get_mst0701_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst0701_data");

            $sql = ' select cust_b_type,cust_b_cd,cust_b_nm from mst0701';
            $sql .= '  WHERE 1 = :custremarks  order by cust_b_cd ';

            $searchArray = array( ':custremarks' => 1 );                
            
            //print_r('custremarks: '.$sql);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst0701_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas[$data["cust_b_type"]][] = $data;
            }

            $Log->trace("END get_mst0701_data");

            return $Datas;
        }
        public function get_mst0010_data($org_id){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst0010_data");
            $sql    = "";
            $sql   .= " select ";
            $sql   .= "  c_biko1 ";
            $sql   .= " ,c_biko2 ";
            $sql   .= " ,c_biko3 ";
            $sql   .= " ,c_biko4 ";
            $sql   .= " ,c_biko5 ";
            $sql   .= " ,c_biko6 ";
            $sql   .= " ,c_biko7 ";
            $sql   .= " ,c_biko8 ";
            $sql   .= " ,c_biko9 ";
            $sql   .= " ,c_biko10 ";
            $sql   .= " from mst0010 ";
            $sql   .= "  where organization_id = :org_id limit 1";

            $searchArray[":org_id"] = $org_id; 
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $Datas = [];

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst0010_data");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas = $data;
            }

            $Log->trace("END get_mst0010_data");

            return $Datas;            
        }        
        
        public function add_mst0701($org_id,$cust_b_type,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START add_mst0701");             
            
            $query = "";
            $query .= " insert into ". $_SESSION["SCHEMA"] .".mst0701 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,cust_b_type        ";
            $query .= "    ,cust_b_cd          ";
            $query .= "    ,cust_b_nm          ";
            $query .= "    ,sender             ";            
            $query .= " ) values (             ";
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CONNECT_KBN       ";          
            $query .= "    ,:cust_b_type        ";
            $query .= "    ,:cust_b_cd          ";
            $query .= "    ,:cust_b_nm          ";
            $query .= "    ,:sender             ";
            $query .= "     )                  ";
//echo "cust_b_type = $cust_b_type";
//print_r($cust_b_type);
//echo 'data:';            
//print_r($data);
//print_r("new:".$query);
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['cust_b_cd'])){
                    $param[":INSUSER_CD"]	=	$_SESSION["LOGIN"];
                    $param[":INSDATETIME"]	=	"now()";
                    $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
                    $param[":UPDDATETIME"]	=	"now()";
                    $param[":DISABLED"]		=	'0';
                    $param[":LAN_KBN"]		=	'0';
                    $param[":CONNECT_KBN"]	=       '0';
                    $param[":cust_b_type"]	=	$cust_b_type;
                    $param[":cust_b_cd"]	=	$data[$sind]['cust_b_cd'];
                    $param[":cust_b_nm"]	=	$data[$sind]['cust_b_nm'];
                    $param[":sender"]           =       '0';
//echo 'param:';                    
//print_r($param);                
                    $result = $DBA->executeSQL_no_searchpath($query, $param); 
                    $param                      = [];
                    $param[":cust_b_type"]	=	$cust_b_type;
                    $param[":cust_b_cd"]	=	$data[$sind]['cust_b_cd'];
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where cust_b_type = :cust_b_type";
                    $query_del .= "   and cust_b_cd   = :cust_b_cd";
//echo "query_del = $query_del";
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                }
   
            }
            $Log->trace("END add_mst0701"); 
        }
        public function del_mst0701($org_id,$cust_b_type,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START del_mst0701");  
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0701 ";
            $query .= " set UPDUSER_CD='delete'  ";
            $query .= " where cust_b_type = :cust_b_type ";
            $query .= "   and cust_b_cd   = :cust_b_cd ";
            
            $query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST0701_CHANGED ";
            $query_del .= " where cust_b_type = :cust_b_type ";
            $query_del .= "   and cust_b_cd   = :cust_b_cd ";
            
            $query_del_1  = "";
            $query_del_1 .= " delete from ". $_SESSION["SCHEMA"].".MST0701";
            $query_del_1 .= " where cust_b_type = :cust_b_type ";
            $query_del_1 .= "   and cust_b_cd   = :cust_b_cd ";
            
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
                $param[":cust_b_type"]	=	$cust_b_type;
                $param[":cust_b_cd"]	=	$data[$sind]['cust_b_cd'];
//print_r("del upd:".$query);
//print_r("delchange:".$query_del);
//print_r("delete:".$query_del_1);
//print_r($param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                $result = $DBA->executeSQL_no_searchpath($query_del_1, $param);
            }
            $Log->trace("END del_mst0701"); 
        }
        public function upd_mst0701($org_id,$cust_b_type,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd_mst0401");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0701 set  ";            
            $query .= "  UPDUSER_CD        = 	:UPDUSER_CD        , ";
            $query .= "  UPDDATETIME       = 	:UPDDATETIME       , ";
            $query .= "  DISABLED          = 	:DISABLED          , ";
            $query .= "  LAN_KBN           = 	:LAN_KBN           , ";
            $query .= "  CONNECT_KBN       =    :CONNECT_KBN       , ";            
            $query .= "  sender            =    :sender              "; 
            $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":DISABLED"]		=	'0';
            $param[":LAN_KBN"]		=	'0';
            $param[":CONNECT_KBN"]	=       '0';
            $param[":sender"]           =       '0';
            $param[":cust_b_type"]	=	$cust_b_type;
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';

                if(is_numeric ($data[$sind]['cust_b_cd'])){
                    foreach(  $data[$sind] as $key => $value){
                        $do_query = 0;
                        if($key === "cust_b_cd"){
                            $param[':'.$key] = $value;
                            continue;
                        }
                        if($value === ""|| isset($value)){
                            //echo "value1 = $value";
                            $do_query = 1;
                            $query .= "  ,$key =    :$key ";
                            $param[':'.$key] = $value;                        
                        }
                    }
                    
                    if($do_query === 0){
                        continue;
                    }                  
                    $query .= " where cust_b_type = :cust_b_type";
                    $query .= "   and cust_b_cd   = :cust_b_cd";                   
//print_r("update:".$query);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    
                    $param = [];
                    $param[":cust_b_type"]	=	$cust_b_type;
                    $param[":cust_b_cd"]	=	$data[$sind]['cust_b_cd'];                  
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where cust_b_type = :cust_b_type ";
                    $query_del .= "   and cust_b_cd   = :cust_b_cd ";                    
                    
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);                    
//print_r($result);                    
                }
            }          
            $Log->trace("END upd_mst0701");
        }
        public function upd_mst0010($org_id,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd_mst0010");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0010 set  ";            
            $query .= "  UPDUSER_CD        = 	:UPDUSER_CD        , ";
            $query .= "  UPDDATETIME       = 	:UPDDATETIME       , ";
            $query .= "  DISABLED          = 	:DISABLED          , ";
            $query .= "  LAN_KBN           = 	:LAN_KBN           , ";
            $query .= "  CONNECT_KBN       =    :CONNECT_KBN       , ";            
            $query .= "  ORGANIZATION_ID   =    :ORGANIZATION_ID     "; 
            $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":DISABLED"]		=	'0';
            $param[":LAN_KBN"]		=	'0';
            $param[":CONNECT_KBN"]	=       '0';
            $param[":ORGANIZATION_ID"]  =       $org_id;
            
            $do_query = 0;
            foreach(  $data as $key => $value){
                if($value === "" || isset($value)){
                    //echo "value1 = $value";
                    $do_query = 1;
                    $query .= "  ,$key =    :$key ";
                    $param[':'.$key] = $value;                        
                }
            }

            $query .= " where ";
            $query .= "   ORGANIZATION_ID = :ORGANIZATION_ID";                    
//print_r("update:".$query);
//print_r($param);
            if($do_query === 1){
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                    
            }   
                      
            $Log->trace("END upd_mst0701");
        }        
        
    }
?>