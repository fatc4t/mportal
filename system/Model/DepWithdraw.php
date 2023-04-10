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
    class DepWithdraw extends BaseModel
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
        public function get_mst6401_data($org_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst6401_data");

            $sql = ' select acct_in_out_kbn,acct_in_out_cd,acct_in_out_name from mst6401';
            $sql .= '  WHERE ORGANIZATION_ID   =    :ORGANIZATION_ID  order by acct_in_out_kbn,acct_in_out_cd';

            $searchArray[":ORGANIZATION_ID"]  =       $org_id;;                
            
            //print_r('depwithdraw: '.$sql);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst6401_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas[$data["acct_in_out_kbn"]][] = $data;
            }

            $Log->trace("END get_mst6401_data");

            return $Datas;
        }        
        
        public function add_mst6401($org_id,$acct_in_out_kbn,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START add_mst6401");             
            
            $query = "";
            $query .= " insert into ". $_SESSION["SCHEMA"] .".mst6401 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,ORGANIZATION_ID    "; 
            $query .= "    ,acct_in_out_kbn    ";
            $query .= "    ,acct_in_out_cd     ";
            $query .= "    ,acct_in_out_name   ";           
            $query .= " ) values (             ";
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CONNECT_KBN       ";
            $query .= "    ,:ORGANIZATION_ID   "; 
            $query .= "    ,:acct_in_out_kbn   ";
            $query .= "    ,:acct_in_out_cd    ";
            $query .= "    ,:acct_in_out_name   ";
            $query .= "     )                  ";
//echo "acct_in_out_kbn = $acct_in_out_kbn";
//print_r($acct_in_out_kbn);
//echo 'data:';            
//print_r($data);
//print_r("new:".$query);
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['acct_in_out_cd'])){
                    $param[":INSUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":INSDATETIME"]	=	"now()";
                    $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":UPDDATETIME"]	=	"now()";
                    $param[":DISABLED"]		=	'0';
                    $param[":LAN_KBN"]		=	'0';
                    $param[":CONNECT_KBN"]	=       '0';
                    $param[":acct_in_out_kbn"]	=	$acct_in_out_kbn;
                    $param[":acct_in_out_cd"]	=	$data[$sind]['acct_in_out_cd'];
                    $param[":acct_in_out_name"]	=	$data[$sind]['acct_in_out_name'];
                    $param[":ORGANIZATION_ID"]  =       $org_id;
//echo 'param:';                    
//print_r($param);                
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    $param                      = [];
                    $param[":acct_in_out_kbn"]	=	$acct_in_out_kbn;
                    $param[":acct_in_out_cd"]	=	$data[$sind]['acct_in_out_cd'];
                    $param[":ORGANIZATION_ID"]  =       $org_id;
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where acct_in_out_kbn = :acct_in_out_kbn";
                    $query_del .= "   and acct_in_out_cd   = :acct_in_out_cd";
                    $query_del .= "   and ORGANIZATION_ID   =    :ORGANIZATION_ID ";                     
//echo "query_del = $query_del";
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                }
   
            }
            $Log->trace("END add_mst6401"); 
        }
        public function del_mst6401($org_id,$acct_in_out_kbn,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START del_mst6401");  
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst6401 ";
            $query .= " set UPDUSER_CD='delete'  ";
            $query .= " where acct_in_out_kbn = :acct_in_out_kbn ";
            $query .= "   and acct_in_out_cd   = :acct_in_out_cd ";
            $query .= "   and ORGANIZATION_ID   =    :ORGANIZATION_ID ";
            
            $query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST6401_CHANGED ";
            $query_del .= " where acct_in_out_kbn = :acct_in_out_kbn ";
            $query_del .= "   and acct_in_out_cd   = :acct_in_out_cd ";
            $query_del .= "   and ORGANIZATION_ID   =    :ORGANIZATION_ID ";
            
            $query_del_1  = "";
            $query_del_1 .= " delete from ". $_SESSION["SCHEMA"].".MST6401";
            $query_del_1 .= " where acct_in_out_kbn = :acct_in_out_kbn ";
            $query_del_1 .= "   and acct_in_out_cd   = :acct_in_out_cd ";
            $query_del_1 .= "   and ORGANIZATION_ID   =    :ORGANIZATION_ID ";
            
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
                $param[":ORGANIZATION_ID"]      =       $org_id;
                $param[":acct_in_out_kbn"]	=	$acct_in_out_kbn;
                $param[":acct_in_out_cd"]	=	$data[$sind]['acct_in_out_cd'];
//print_r("del upd:".$query);
//print_r("delchange:".$query_del);
//print_r("delete:".$query_del_1);
//print_r($param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                $result = $DBA->executeSQL_no_searchpath($query_del_1, $param);
            }
            $Log->trace("END del_mst6401"); 
        }
        public function upd_mst6401($org_id,$acct_in_out_kbn,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd_mst0401");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst6401 set  ";            
            $query .= "  UPDUSER_CD        = 	:UPDUSER_CD        , ";
            $query .= "  UPDDATETIME       = 	:UPDDATETIME       , ";
            $query .= "  DISABLED          = 	:DISABLED          , ";
            $query .= "  LAN_KBN           = 	:LAN_KBN           , ";
            $query .= "  CONNECT_KBN       =    :CONNECT_KBN       , ";            
            $query .= "  ORGANIZATION_ID   =    :ORGANIZATION_ID     "; 
            $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":DISABLED"]		=	'0';
            $param[":LAN_KBN"]		=	'0';
            $param[":CONNECT_KBN"]	=       '0';
            $param[":ORGANIZATION_ID"]  =       $org_id;
            $param[":acct_in_out_kbn"]	=	$acct_in_out_kbn;
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';

                if(is_numeric ($data[$sind]['acct_in_out_cd'])){
                    foreach(  $data[$sind] as $key => $value){
                        $do_query = 0;
                        if($key === "acct_in_out_cd"){
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
                    $query .= " where acct_in_out_kbn = :acct_in_out_kbn";
                    $query .= "   and acct_in_out_cd   = :acct_in_out_cd";                   
//print_r("update:".$query);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    
                    $param = [];
                    $param[":acct_in_out_kbn"]	=	$acct_in_out_kbn;
                    $param[":acct_in_out_cd"]	=	$data[$sind]['acct_in_out_cd'];
                    $param[":ORGANIZATION_ID"]  =       $org_id;
                    
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where acct_in_out_kbn  = :acct_in_out_kbn ";
                    $query_del .= "   and acct_in_out_cd   = :acct_in_out_cd ";
                    $query_del .= "   and ORGANIZATION_ID  = :ORGANIZATION_ID ";                    
                    
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);                    
//print_r($result);                    
                }
            }          
            $Log->trace("END upd_mst6401");
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
            $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
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
                      
            $Log->trace("END upd_mst6401");
        }        
        
    }
?>