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
    class Rank extends BaseCustomer
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
        public function get_mst8401_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst8401_data");
            
            $sql  = "";
            $sql .= " select cust_rank_cd ";
            $sql .= " ,cust_rank_nm ";
            $sql .= " ,cust_rank_prate ";
            $sql .= " ,cust_rank_p_kbn ";
            $sql .= " ,case  ";
            $sql .= " 	when cust_rank_p_kbn = '0' then '得点除外しない' ";
            $sql .= " 	when cust_rank_p_kbn = '1' then '得点除外する' ";
            $sql .= " 	else '' ";
            $sql .= " END  AS cust_rank_p_nm ";
            $sql .= " from mst8401 ";
            $sql .= " where 1 = :val ";

            $searchArray = array( ':val' => 1 );

            //print_r('area: '.$sql);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst8401_data");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas[] = $data;
            }

            $Log->trace("END get_mst8401_data");

            return $Datas;
        }
        
        public function add_mst8401($data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START add_mst8401");             
            
            $query = "";
            $query .= " insert into ". $_SESSION["SCHEMA"] .".mst8401 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,cust_rank_cd       ";
            $query .= "    ,cust_rank_nm       ";
            $query .= "    ,cust_rank_prate    ";
            $query .= "    ,cust_rank_p_kbn    ";
            $query .= "    ,sender             ";
            $query .= " ) values (             ";
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CONNECT_KBN       ";
            $query .= "    ,:cust_rank_cd      ";
            $query .= "    ,:cust_rank_nm      ";
            $query .= "    ,:cust_rank_prate   ";
            $query .= "    ,:cust_rank_p_kbn   ";
            $query .= "    ,:sender            ";
            $query .= "     )                  ";
//print_r($data); 
//print_r("new:".$query);
            for($i=0;$i<count($data);$i++){

                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['cust_rank_cd'])){
                    $param[":INSUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":INSDATETIME"]	=	"now()";
                    $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":UPDDATETIME"]	=	"now()";
                    $param[":DISABLED"]		=	'0';
                    $param[":LAN_KBN"]		=	'0';
                    $param[":CONNECT_KBN"]	=       '0';
                    $param[":cust_rank_cd"]	=	$data[$sind]['cust_rank_cd'];
                    $param[":cust_rank_nm"]	=	$data[$sind]['cust_rank_nm'];
                    $param[":cust_rank_prate"]	=	$data[$sind]['cust_rank_prate'];
                    $param[":cust_rank_p_kbn"]	=	$data[$sind]['cust_rank_p_kbn'];                    
                    $param[":sender"]		=	'0';                    
                    
//print_r($param);                
                    $result = $DBA->executeSQL_no_searchpath($query, $param); 
                    $param = [];
                    $param[":cust_rank_cd"] = $data[$sind]['cust_rank_cd'];                    
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where cust_rank_cd = :cust_rank_cd";
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                }
   
            }
            $Log->trace("END add_mst8401"); 
        }
        public function del_mst8401($data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START del_mst8401");  
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst8401 ";
            $query .= " set UPDUSER_CD='delete' where cust_rank_cd = :cust_rank_cd";
            
            $query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST8401_CHANGED ";
            $query_del .= " where cust_rank_cd = :cust_rank_cd";
            
            $query_del_1  = "";
            $query_del_1 .= " delete from ". $_SESSION["SCHEMA"].".MST8401";
            $query_del_1 .= " where cust_rank_cd = :cust_rank_cd";
            
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['cust_rank_cd'])){            
                    $param["cust_rank_cd"] = $data[$sind]['cust_rank_cd'];
//print_r("del upd:".$query);
//print_r("delchange:".$query_del);
//print_r("delete:".$query_del_1);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                    $result = $DBA->executeSQL_no_searchpath($query_del_1, $param);
                }
            }
            $Log->trace("END del_mst8401"); 
        }
        public function upd_mst8401($data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd_mst0401");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst8401 set  ";            
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

                if(is_numeric ($data[$sind]['cust_rank_cd'])){
                    foreach(  $data[$sind] as $key => $value){
                        $do_query = 0;
                        if($key === "cust_rank_cd"){
                            $param[':'.$key] = $value;
                            continue;
                        }

                        if($value === ""|| isset($value)){
                            //echo "value1 = $value";
                            $do_query = 1;
                            $query .= "  $key =    :$key , ";
                            $param[':'.$key] = $value;                        
                        }
                    }
                    
                    if($do_query === 0){
                        continue;
                    }
                    $query .= "  SENDER = :SENDER ";                    
                    $query .= " where cust_rank_cd = :cust_rank_cd";
//print_r("update:".$query);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    
                    $param = [];
                    $param[":cust_rank_cd"] = $data[$sind]['cust_rank_cd'];
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where cust_rank_cd = :cust_rank_cd";
                    
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);                    
//print_r($result);                    
                }
            }          
            $Log->trace("END upd_mst8401");
        }
        
    }
?>