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
    class Memo extends BaseModel
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
        public function get_mst6201_data($org_id,$receipt_cd)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst6201_data");
            
            $sql  = "";
            $sql .= " select  ";
            $sql .= "  receipt_kbn ";
            $sql .= " ,receipt_str_dt ";
            $sql .= " ,receipt_end_dt ";
            $sql .= " ,receipt_line_1 ";
            $sql .= " ,receipt_line_2 ";
            $sql .= " ,receipt_line_3 ";
            $sql .= " ,receipt_line_4 ";
            $sql .= " ,receipt_line_5 ";  
            $sql .= " from mst6201 ";
            $sql .= " where organization_id = :org_id ";
            $sql .= "   and receipt_cd = :receipt_cd ";

            $searchArray[':org_id'] = $org_id ;
            $searchArray[':receipt_cd'] = $receipt_cd ;
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
                
                $Log->trace("END get_mst6201_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    //print_r($data);
                    $Datas[] = $data;
            }

            $Log->trace("END get_mst6201_data");

            return $Datas;
        }
        public function get_mst6201_lst($org_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst6201_lst");
            
            $sql  = "";
            $sql .= " select  "; 
            $sql .= " string_agg(receipt_cd,',') as list  "; 
            $sql .= " from mst6201 ";
            $sql .= " where organization_id = :org_id ";

            $searchArray[':org_id'] = $org_id ;
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
                
                $Log->trace("END get_mst6201_lst");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    //print_r($data);
                    $Datas = $data;
            }

            $Log->trace("END get_mst6201_lst");

            return $Datas;
        }        
        public function add_mst6201($org_id,$receipt_cd,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START add_mst6201");             
            
            $query = "";
            $query .= " insert into ". $_SESSION["SCHEMA"] .".mst6201 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,ORGANIZATION_ID    ";
            $query .= "    ,receipt_cd         ";
            $query .= "    ,receipt_kbn        ";
            $query .= "    ,receipt_str_dt     ";
            $query .= "    ,receipt_end_dt     ";
            $query .= "    ,receipt_line_1     ";            
            $query .= "    ,receipt_line_2     ";
            $query .= "    ,receipt_line_3     ";
            $query .= "    ,receipt_line_4     ";
            $query .= "    ,receipt_line_5     ";
            $query .= " ) values (             ";
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CONNECT_KBN       ";
            $query .= "    ,:ORGANIZATION_ID   ";            
            $query .= "    ,:receipt_cd        ";
            $query .= "    ,:receipt_kbn       ";
            $query .= "    ,:receipt_str_dt    ";
            $query .= "    ,:receipt_end_dt    ";
            $query .= "    ,:receipt_line_1    ";            
            $query .= "    ,:receipt_line_2    ";
            $query .= "    ,:receipt_line_3    ";
            $query .= "    ,:receipt_line_4    ";
            $query .= "    ,:receipt_line_5    ";
            $query .= "     )                  ";
//print_r(count($data)); 
//print_r("new:".$query);
                if(is_numeric ($receipt_cd)){
                    $param[":INSUSER_CD"]	=	$_SESSION["LOGIN"];
                    $param[":INSDATETIME"]	=	"now()";
                    $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
                    $param[":UPDDATETIME"]	=	"now()";
                    $param[":DISABLED"]		=	'0';
                    $param[":LAN_KBN"]		=	'0';
                    $param[":CONNECT_KBN"]	=       '0';
                    $param[":ORGANIZATION_ID"]  =       $org_id;
                    $param[":receipt_cd"]       =	$receipt_cd;
                    $param[":receipt_kbn"]      =	$data['receipt_kbn'];
                    $param[":receipt_str_dt"]   =	$data['receipt_str_dt'];
                    $param[":receipt_end_dt"]   =	$data['receipt_end_dt'];
                    $param[":receipt_line_1"]	=	$data['receipt_line_1'];                    
                    $param[":receipt_line_2"]	=	$data['receipt_line_2'];
                    $param[":receipt_line_3"]	=	$data['receipt_line_3'];
                    $param[":receipt_line_4"]	=	$data['receipt_line_4'];
                    $param[":receipt_line_5"]	=	$data['receipt_line_5'];                    
                    
//print_r($param);                
                    $result = $DBA->executeSQL_no_searchpath($query, $param); 
                    $param                      = [];
                    $param[":receipt_cd"]        = $receipt_cd;
                    $param[":ORGANIZATION_ID"]  = $org_id;
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where receipt_cd = :receipt_cd";
                    $query_del .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                }
   
            
            $Log->trace("END add_mst6201"); 
        }
        public function del_mst6201($org_id,$receipt_cd){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START del_mst6201");  
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst6201 ";
            $query .= " set UPDUSER_CD='delete' where receipt_cd = :receipt_cd ";
            $query .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";
            
            $query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST6201_CHANGED ";
            $query_del .= " where receipt_cd = :receipt_cd ";
            $query_del .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";
            
            $query_del_1  = "";
            $query_del_1 .= " delete from ". $_SESSION["SCHEMA"].".MST6201";
            $query_del_1 .= " where receipt_cd = :receipt_cd ";
            $query_del_1 .= " and ORGANIZATION_ID = :ORGANIZATION_ID";
            

            if(is_numeric ($receipt_cd)){            
                $param["receipt_cd"] = $receipt_cd;
                $param[":ORGANIZATION_ID"]  =       $org_id;
//print_r("del upd:".$query);
//print_r("delchange:".$query_del);
//print_r("delete:".$query_del_1);
//print_r($param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                $result = $DBA->executeSQL_no_searchpath($query_del_1, $param);  
            }
            $Log->trace("END del_mst6201"); 
        }
        public function upd_mst6201($org_id,$receipt_cd,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd_mst0401");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst6201 set  ";            
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
            $param["receipt_cd"]        =       $receipt_cd;
            if(is_numeric ($receipt_cd)){
                foreach(  $data as $key => $value){
                    if($key === "upd"){
                        continue;
                    }
                    if($value === ""|| isset($value)){
                        //echo "value1 = $value";
                        $query .= "  ,$key =    :$key ";
                        $param[':'.$key] = $value;                        
                    }
                }
                
                $query .= " where receipt_cd = :receipt_cd";
                $query .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";                    
//print_r("update:".$query);
//print_r($param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);

                $param = [];
                $param[":receipt_cd"]         = $receipt_cd;
                $param[":ORGANIZATION_ID"]  = $org_id;                    
                $query_del  = "";
                $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                $query_del .= " where receipt_cd = :receipt_cd";
                $query_del .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";                    

                $result = $DBA->executeSQL_no_searchpath($query_del, $param);                    
//print_r($result);                    
            }
                     
            $Log->trace("END upd_mst6201");
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
        
    }
?>