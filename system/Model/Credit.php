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
    class Credit extends BaseModel
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
        public function get_mst5601_data($org_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst5601_data");
            
            $sql  = "";
            $sql .= " select credit_cd ";
            $sql .= " ,credit_nm ";
            $sql .= " ,credit_kn ";
            $sql .= " ,credit_kbn ";
            $sql .= " ,add_prate ";
            $sql .= " ,refund_kbn ";
            $sql .= " ,case  ";
            $sql .= " 	when credit_kbn = '0' then 'クレジットカード' ";
            $sql .= " 	when credit_kbn = '1' then 'ハウスカード' ";
            $sql .= " 	when credit_kbn = '2' then '電子マネー' ";
            $sql .= " 	when credit_kbn = '3' then '掛売'	 ";
            $sql .= " 	when credit_kbn = '4' then '交通系IC' ";
            $sql .= " 	when credit_kbn = '5' then 'iD' ";
            $sql .= " 	when credit_kbn = '6' then 'Edy' ";
            $sql .= " 	when credit_kbn = '7' then 'nanaco'	 ";
            $sql .= " 	when credit_kbn = '8' then 'WAON' ";
            $sql .= " 	when credit_kbn = '9' then 'QUIC Pay' ";
            $sql .= " 	else '' ";
            $sql .= " END  AS credit_kbn_nm ";
            $sql .= " ,case  ";
            $sql .= " 	when refund_kbn = '0' then '返金不可' ";
            $sql .= " 	when refund_kbn = '1' then '返金可能'	 ";
            $sql .= " 	else '' ";
            $sql .= " END  AS refund_nm ";
            $sql .= " from mst5601 ";
            $sql .= " where organization_id = :org_id order by credit_cd ";

            $searchArray = array( ':org_id' => $org_id );

            //print_r('area: '.$sql);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst5601_data");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas[] = $data;
            }

            $Log->trace("END get_mst5601_data");

            return $Datas;
        }
        
        public function add_mst5601($org_id,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START add_mst5601");             
            
            $query = "";
            $query .= " insert into ". $_SESSION["SCHEMA"] .".mst5601 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,ORGANIZATION_ID    ";
            $query .= "    ,credit_cd          ";
            $query .= "    ,credit_nm          ";
            $query .= "    ,credit_kn          ";
            $query .= "    ,credit_kbn         ";
            $query .= "    ,add_prate          ";
            $query .= "    ,refund_kbn         ";
            $query .= " ) values (             ";
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CONNECT_KBN       ";
            $query .= "    ,:ORGANIZATION_ID   ";            
            $query .= "    ,:credit_cd         ";
            $query .= "    ,:credit_nm         ";
            $query .= "    ,:credit_kn         ";
            $query .= "    ,:credit_kbn        ";
            $query .= "    ,:add_prate         ";
            $query .= "    ,:refund_kbn        ";
            $query .= "     )                  ";
//print_r($data); 
//print_r("new:".$query);
            for($i=0;$i<count($data);$i++){

                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['credit_cd'])){
                    $param                      = [];
                    $param[":INSUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":INSDATETIME"]	=	"now()";
                    $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":UPDDATETIME"]	=	"now()";
                    $param[":DISABLED"]		=	'0';
                    $param[":LAN_KBN"]		=	'0';
                    $param[":CONNECT_KBN"]	=       '0';
                    $param[":ORGANIZATION_ID"]  =       $org_id;
                    $param[":credit_cd"]	=	$data[$sind]['credit_cd'];
                    $param[":credit_nm"]	=	$data[$sind]['credit_nm'];
                    $param[":credit_kn"]	=	$data[$sind]['credit_kn'];
                    $param[":credit_kbn"]	=	$data[$sind]['credit_kbn'];
                    $param[":add_prate"]	=	empty($data[$sind]['add_prate']) ? 0 : $data[$sind]['add_prate'];
                    $param[":refund_kbn"]	=	$data[$sind]['refund_kbn'];                                     
                    
//print_r($param);                
                    $result = $DBA->executeSQL_no_searchpath($query, $param); 
                    $param                      = [];
                    $param[":credit_cd"]        = $data[$sind]['credit_cd'];
                    $param[":ORGANIZATION_ID"]  = $org_id;
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where credit_cd = :credit_cd";
                    $query_del .= " 　and ORGANIZATION_ID = :ORGANIZATION_ID";
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);
                }
   
            }
            $Log->trace("END add_mst5601"); 
        }
        public function del_mst5601($org_id,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START del_mst5601");  
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst5601 ";
            $query .= " set UPDUSER_CD='delete' where credit_cd = :credit_cd";
            $query .= "   and ORGANIZATION_ID = :ORGANIZATION_ID";
            
            $query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST5601_CHANGED ";
            $query_del .= " where credit_cd = :credit_cd";
            $query_del .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";
            
            $query_del_1  = "";
            $query_del_1 .= " delete from ". $_SESSION["SCHEMA"].".MST5601";
            $query_del_1 .= " where credit_cd = :credit_cd";
            $query_del_1 .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";
            
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['credit_cd'])){            
                    $param["credit_cd"] = $data[$sind]['credit_cd'];
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
            $Log->trace("END del_mst5601"); 
        }
        public function upd_mst5601($org_id,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd_mst0401");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst5601 set  ";            
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

                if(is_numeric ($data[$sind]['credit_cd'])){
                    foreach(  $data[$sind] as $key => $value){
                        $do_query = 0;
                        if($key === "credit_cd"){
                            $param[':'.$key] = $value;
                            continue;
                        }else if($key === "refund_nm" || $key === "credit_kbn_nm" ){
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
                    $query .= " where credit_cd = :credit_cd";
                    $query .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";                    
//print_r("update:".$query);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    
                    $param = [];
                    $param[":credit_cd"]   = $data[$sind]['credit_cd'];
                    $param[":ORGANIZATION_ID"]  = $org_id;                    
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where credit_cd = :credit_cd";
                    $query_del .= " 　and ORGANIZATION_ID = :ORGANIZATION_ID";                    
                    
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);                    
//print_r($result);                    
                }
            }          
            $Log->trace("END upd_mst5601");
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