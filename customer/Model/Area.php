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
    class Area extends BaseCustomer
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
        public function get_area_data( $area="" )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");

            $sql = ' select area_cd,area_nm,area_kn from mst0501';
            $sql .= '  WHERE 1 = :area  ';

            $searchArray = array( ':area' => 1 );                
            
            //print_r('area: '.$sql);
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
        
        public function add_area($data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START add_area");             
            
            $query = "";
            $query .= " insert into ". $_SESSION["SCHEMA"] .".mst0501 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,area_cd            ";
            $query .= "    ,area_nm            ";
            $query .= "    ,area_kn            ";
            $query .= "    ,sender             ";
            $query .= " ) values (             ";
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CONNECT_KBN       ";
            $query .= "    ,:area_cd           ";
            $query .= "    ,:area_nm           ";
            $query .= "    ,:area_kn           ";
            $query .= "    ,:sender            ";
            $query .= "     )                  ";
//print_r($data); 
//print_r("new:".$query);
            for($i=0;$i<count($data);$i++){

                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['area_cd'])){
                    $param[":INSUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":INSDATETIME"]	=	"now()";
                    $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":UPDDATETIME"]	=	"now()";
                    $param[":DISABLED"]		=	'0';
                    $param[":LAN_KBN"]		=	'0';
                    $param[":CONNECT_KBN"]	=       '0';
                    $param[":area_cd"]		=	$data[$sind]['area_cd'];
                    $param[":area_nm"]		=	$data[$sind]['area_nm'];
                    $param[":area_kn"]		=	$data[$sind]['area_kn'];
                    $param[":sender"]		=	'0';                    
                    
//print_r($param);                
                    $result = $DBA->executeSQL_no_searchpath($query, $param); 
                    $param = [];
                    $param[":area_cd"] = $data[$sind]['area_cd'];                    
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where area_cd = :area_cd";
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                }
   
            }
            $Log->trace("END add_area"); 
        }
        public function del_area($data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START del_area");  
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0501 ";
            $query .= " set UPDUSER_CD='delete' where area_cd = :area_cd";
            
            $query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST0501_CHANGED ";
            $query_del .= " where area_cd = :area_cd";
            
            $query_del_1  = "";
            $query_del_1 .= " delete from ". $_SESSION["SCHEMA"].".MST0501";
            $query_del_1 .= " where area_cd = :area_cd";
            
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['area_cd'])){            
                    $param["area_cd"] = $data[$sind]['area_cd'];
//print_r("del upd:".$query);
//print_r("delchange:".$query_del);
//print_r("delete:".$query_del_1);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                    $result = $DBA->executeSQL_no_searchpath($query_del_1, $param);
                }
            }
            $Log->trace("END del_area"); 
        }
        public function upd_area($data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd_area");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0501 set  ";            
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
            $param[":SENDER"]   	=       '0';
            
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
//print_r($data);
                if(is_numeric ($data[$sind]['area_cd'])){
                    foreach(  $data[$sind] as $key => $value){
                        $do_query = 0;
                        if($key === "area_cd"){
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
                    $query .= "  SENDER = :SENDER ";                    
                    $query .= " where area_cd = :area_cd";
//print_r("update:".$query);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    $param = [];
                    $param[":area_cd"] = $data[$sind]['area_cd'];
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where area_cd = :area_cd";
                    
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);                    
//print_r($result);                    
                }
            }          
            $Log->trace("END upd_area");
        }
        
    }
?>