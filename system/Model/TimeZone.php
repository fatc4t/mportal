<?php
    /**
     * @file      顧客情報
     * @author    K.Sakamoto
     * @date      2017/08/21
     * @version   1.00
     * @note      顧客情報テーブルの管理を行う
     */

    // CustomerInputData.phpを読み込む
    require './Model/BaseSystem.php';

    /**
     * 顧客情報クラス
     * @note   顧客情報テーブルの管理を行う。
     */
    class TimeZone extends BaseSystem
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
         * 地区データを獲得します
         * @param    
         * @return   SQLの実行結果
         */
        public function get_timezone_data( $org_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");

            $sql = " select tmzone_cd,to_char(tmzone_str::time,'HH24:MI') as tmzone_str ,to_char(tmzone_end::time,'HH24:MI') as tmzone_end from mst8101 ";
            $sql .= '  WHERE organization_id = :organization_id order by tmzone_cd ';

            $searchArray = array( ':organization_id' => $org_id );                
            
           // //print_r('timezone: '.$sql);
            //print_r($searchArray);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

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
                    $customerDataList[] = $data;
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        }
        
        public function add_timezone($org_id,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START add_timezone");             
            
            $query = "";
            $query .= " insert into ". $_SESSION["SCHEMA"] .".mst8101 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,ORGANIZATION_ID    ";
            $query .= "    ,tmzone_cd            ";
            $query .= "    ,tmzone_str            ";
            $query .= "    ,tmzone_end            ";
            $query .= " ) values (             ";
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CONNECT_KBN       ";
            $query .= "    ,:ORGANIZATION_ID   ";              
            $query .= "    ,:tmzone_cd           ";
            $query .= "    ,:tmzone_str           ";
            $query .= "    ,:tmzone_end           ";
            $query .= "     )                  ";
//print_r($data); 
//print_r("new:".$query);
            for($i=0;$i<count($data);$i++){

                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['tmzone_cd'])){
                    $param[":INSUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":INSDATETIME"]	=	"now()";
                    $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":UPDDATETIME"]	=	"now()";
                    $param[":DISABLED"]		=	'0';
                    $param[":LAN_KBN"]		=	'0';
                    $param[":CONNECT_KBN"]	=       '0';
                    $param[":ORGANIZATION_ID"]  =       $org_id;                    
                    $param[":tmzone_cd"]		=	$data[$sind]['tmzone_cd'];
                    $param[":tmzone_str"]		=	$data[$sind]['tmzone_str'];
                    $param[":tmzone_end"]		=	$data[$sind]['tmzone_end'];                  
                    
//print_r($param);                
                    $result = $DBA->executeSQL_no_searchpath($query, $param); 
                    $param = [];
                    $param[":tmzone_cd"] = $data[$sind]['tmzone_cd'];
                    $param[":ORGANIZATION_ID"]  = $org_id;                    
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where tmzone_cd = :tmzone_cd";
                    $query_del .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";                    
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                }
   
            }
            $Log->trace("END add_timezone"); 
        }
        public function del_timezone($org_id,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START del_timezone");  
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst8101 ";
            $query .= " set UPDUSER_CD='delete' where tmzone_cd = :tmzone_cd";
            $query .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";             
            
            $query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST8101_CHANGED ";
            $query_del .= " where tmzone_cd = :tmzone_cd";
            $query_del .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";             
            
            $query_del_1  = "";
            $query_del_1 .= " delete from ". $_SESSION["SCHEMA"].".MST8101";
            $query_del_1 .= " where tmzone_cd = :tmzone_cd";
            $query_del_1 .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";             
            
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['tmzone_cd'])){
                    $param[":ORGANIZATION_ID"]  = $org_id;
                    $param["tmzone_cd"] = $data[$sind]['tmzone_cd'];
//print_r("del upd:".$query);
//print_r("delchange:".$query_del);
//print_r("delete:".$query_del_1);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                    $result = $DBA->executeSQL_no_searchpath($query_del_1, $param);
                }
            }
            $Log->trace("END del_timezone"); 
        }
        
        public function upd_timezone($org_id,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd_timezone");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst8101 set  ";            
            $query .= "  UPDUSER_CD        = 	:UPDUSER_CD        , ";
            $query .= "  UPDDATETIME       = 	:UPDDATETIME       , ";
            $query .= "  DISABLED          = 	:DISABLED          , ";
            $query .= "  LAN_KBN           = 	:LAN_KBN           , ";
            $query .= "  CONNECT_KBN       =    :CONNECT_KBN       , ";            
            
            $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":DISABLED"]		=	'0';
            $param[":LAN_KBN"]		=	'0';
            $param[":CONNECT_KBN"]	=       '0';
            $param[":ORGANIZATION_ID"]  =       $org_id;
            
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
//print_r($data);
                if(is_numeric ($data[$sind]['tmzone_cd'])){
                    foreach(  $data[$sind] as $key => $value){
                        $do_query = 0;
                        if($key === "tmzone_cd"){
                            $param[':'.$key] = $value;
                            continue;
                        }
                        if($value === ""||$value){
                            $do_query = 1;
                            $query .= "  $key =    :$key , ";
                            $param[':'.$key] = $value;                        
                        }
                    }
                    
                    if($do_query === 0){
                        continue;
                    }                   
                    $query .= " where tmzone_cd = :tmzone_cd";
                    $query .= "  and ORGANIZATION_ID = :ORGANIZATION_ID"; 
//print_r("update:".$query);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    $param = [];
                    $param[":tmzone_cd"] = $data[$sind]['tmzone_cd'];
                    $param[":ORGANIZATION_ID"]  = $org_id;
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where tmzone_cd = :tmzone_cd";
                    
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);                    
//print_r($result);                    
                }
            }          
            $Log->trace("END upd_timezone");
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
        
    }
?>