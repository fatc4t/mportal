<?php
    /**
     * @file      顧客情報
     * @author    K.Sakamoto
     * @date      2017/08/21
     * @version   1.00
     * @note      顧客情報テーブルの管理を行う
     */

    // CustomerInputData.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 顧客情報クラス
     * @note   顧客情報テーブルの管理を行う。
     */
    class Staff extends BaseModel
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
        public function get_mst0601_data($org_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst0601_data");
            
            $sql  = "";
            $sql .= " select staff_cd ";
            $sql .= " ,staff_nm ";
            $sql .= " ,staff_kn ";
            $sql .= " ,supp_cd ";
            $sql .= " ,employee_cd ";
            $sql .= " ,employee_kbn ";
            $sql .= " ,case  ";
            $sql .= " 	when employee_kbn = '00' then '未設定' ";
            $sql .= " 	when employee_kbn = '01' then '責任者' ";
            $sql .= " 	when employee_kbn = '02' then '社員' ";
            $sql .= " 	when employee_kbn = '03' then 'パート・アルバイト' ";            
            $sql .= " 	else '' ";
            $sql .= " END  AS employee_nm ";           
            $sql .= " from mst0601 ";
            $sql .= " where organization_id = :org_id ";
            $sql .= " order by staff_cd ";

            $searchArray = array( ':org_id' => $org_id );

            //print_r('area: '.$sql);
            //print_r($searchArray);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];
            //print_r($result);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_mst0601_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    //print_r($data);
                    $Datas[] = $data;
            }

            $Log->trace("END get_mst0601_data");

            return $Datas;
        }
        
        public function add_mst0601($org_id,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START add_mst0601");             
            
            $query = "";
            $query .= " insert into ". $_SESSION["SCHEMA"] .".mst0601 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,ORGANIZATION_ID    ";
            $query .= "    ,staff_cd           ";
            $query .= "    ,staff_nm           ";
            $query .= "    ,staff_kn           ";
            $query .= "    ,supp_cd            ";
            $query .= "    ,employee_cd         ";
            $query .= "    ,employee_kbn        ";
            $query .= " ) values (             ";
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CONNECT_KBN       ";
            $query .= "    ,:ORGANIZATION_ID   ";            
            $query .= "    ,:staff_cd          ";
            $query .= "    ,:staff_nm          ";
            $query .= "    ,:staff_kn          ";
            $query .= "    ,:supp_cd           ";
            $query .= "    ,:employee_cd        ";
            $query .= "    ,:employee_kbn       ";
            $query .= "     )                  ";
//print_r(count($data)); 
//print_r("new:".$query);
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['staff_cd'])){
                    $param[":INSUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":INSDATETIME"]	=	"now()";
                    $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":UPDDATETIME"]	=	"now()";
                    $param[":DISABLED"]		=	'0';
                    $param[":LAN_KBN"]		=	'0';
                    $param[":CONNECT_KBN"]	=       '0';
                    $param[":ORGANIZATION_ID"]  =       $org_id;
                    $param[":staff_cd"]         =	$data[$sind]['staff_cd'];
                    $param[":staff_nm"]         =	$data[$sind]['staff_nm'];
                    $param[":staff_kn"]         =	$data[$sind]['staff_kn'];
                    $param[":supp_cd"]          =	$data[$sind]['supp_cd'];
                    $param[":employee_cd"]	=	$data[$sind]['employee_cd'];
                    $param[":employee_kbn"]	=	$data[$sind]['employee_kbn'];
                    
//print_r($param);                
                    $result = $DBA->executeSQL_no_searchpath($query, $param); 
                    $param                      = [];
                    $param[":staff_cd"]        = $data[$sind]['staff_cd'];
                    $param[":ORGANIZATION_ID"]  = $org_id;
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where staff_cd = :staff_cd";
                    $query_del .= " 　and ORGANIZATION_ID = :ORGANIZATION_ID";
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                }
   
            }
            $Log->trace("END add_mst0601"); 
        }
        public function del_mst0601($org_id,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START del_mst0601");  
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0601 ";
            $query .= " set UPDUSER_CD='delete' where staff_cd = :staff_cd ";
            $query .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";
            
            $query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST0601_CHANGED ";
            $query_del .= " where staff_cd = :staff_cd ";
            $query_del .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";
            
            $query_del_1  = "";
            $query_del_1 .= " delete from ". $_SESSION["SCHEMA"].".MST0601";
            $query_del_1 .= " where staff_cd = :staff_cd ";
            $query_del_1 .= " and ORGANIZATION_ID = :ORGANIZATION_ID";
            
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['staff_cd'])){            
                    $param["staff_cd"] = $data[$sind]['staff_cd'];
                    $param[":ORGANIZATION_ID"]  =       $org_id;
//print_r("del upd:".$query);
//print_r("delchange:".$query_del);
//print_r("delete:".$query_del_1);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                    $result = $DBA->executeSQL_no_searchpath($query_del_1, $param);
                }
            }
            $Log->trace("END del_mst0601"); 
        }
        public function upd_mst0601($org_id,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd_mst0401");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0601 set  ";            
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
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';

                if(is_numeric ($data[$sind]['staff_cd'])){
                    foreach(  $data[$sind] as $key => $value){
                        $do_query = 0;
                        if($key === "staff_cd"){
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
                    $query .= " where staff_cd = :staff_cd";
                    $query .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";                    
//print_r("update:".$query);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    
                    $param = [];
                    $param[":staff_cd"]         = $data[$sind]['staff_cd'];
                    $param[":ORGANIZATION_ID"]  = $org_id;                    
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where staff_cd = :staff_cd";
                    $query_del .= " 　and ORGANIZATION_ID = :ORGANIZATION_ID";                    
                    
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);                    
//print_r($result);                    
                }
            }          
            $Log->trace("END upd_mst0601");
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
        public function staff_size($org_id){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START staff_size");
            $sql    = "";
            $sql   .= " select staffsize from mst0010 ";
            $sql   .= "  where organization_id = :org_id limit 1";

            $searchArray[":org_id"] = $org_id; 
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $Datas = '';

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END staff_size");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas = $data["staffsize"];
            }

            $Log->trace("END staff_size");

            return $Datas;            
        }
        public function getsupp($org_id){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getsupp");
            $sql    = "";
            $sql   .= " select supp_cd,supp_nm from mst1101 ";
            $sql   .= "  where organization_id = :org_id and supp_kbn = '0' ";
            $sql   .= " order by supp_cd ";
            $searchArray[":org_id"] = $org_id; 
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $Datas = [];

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getsupp");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas[] = $data;
            }

            $Log->trace("END getsupp");

            return $Datas;            
        }        
        
    }
?>