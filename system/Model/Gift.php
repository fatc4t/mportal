<?php
    /**
     * @file      商品券マスタ情報
     * @author    K.Sakamoto
     * @date      2017/08/21
     * @version   1.00
     * @note      商品券マスタ情報テーブルの管理を行う
     */

    // CustomerInputData.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 商品券マスタ情報クラス
     * @note   商品券マスタ情報テーブルの管理を行う。
     */
    class Gift extends BaseModel
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
         * 商品券マスタデータを獲得します
         * @param    
         * @return   SQLの実行結果
         */
        public function get_mst5901_data($org_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst5901_data");
            
            $sql  = "";
            $sql .= " select gift_certi_cd ";
            $sql .= " ,gift_certi_nm ";
            $sql .= " ,gift_certi_kn ";
            $sql .= " ,change_kbn ";
            $sql .= " ,disc_money ";
            $sql .= " ,point_kbn ";
            $sql .= " ,case  ";
            $sql .= " 	when change_kbn = '0' then 'お釣無し' ";
            $sql .= " 	when change_kbn = '1' then 'お釣有り' ";
            $sql .= " 	else '' ";
            $sql .= " END  AS change_nm ";
            $sql .= " ,case  ";
            $sql .= " 	when point_kbn = '0' then '得点除外しない' ";
            $sql .= " 	when point_kbn = '1' then '得点除外する'	 ";
            $sql .= " 	else '' ";
            $sql .= " END  AS point_nm ";           
            $sql .= " from mst5901 ";
            $sql .= " where organization_id = :org_id order by gift_certi_cd ";

            $searchArray = array( ':org_id' => $org_id );

            //print_r('area: '.$sql);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst5901_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas[] = $data;
            }

            $Log->trace("END get_mst5901_data");

            return $Datas;
        }
        
        public function add_mst5901($org_id,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START add_mst5901");             
            
            $query = "";
            $query .= " insert into ". $_SESSION["SCHEMA"] .".mst5901 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,ORGANIZATION_ID    ";
            $query .= "    ,gift_certi_cd          ";
            $query .= "    ,gift_certi_nm          ";
            $query .= "    ,gift_certi_kn          ";
            $query .= "    ,change_kbn         ";
            $query .= "    ,disc_money          ";
            $query .= "    ,point_kbn         ";
            $query .= "    ,disc_kbn         ";
            $query .= " ) values (             ";
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CONNECT_KBN       ";
            $query .= "    ,:ORGANIZATION_ID   ";            
            $query .= "    ,:gift_certi_cd         ";
            $query .= "    ,:gift_certi_nm         ";
            $query .= "    ,:gift_certi_kn         ";
            $query .= "    ,:change_kbn        ";
            $query .= "    ,:disc_money         ";
            $query .= "    ,:point_kbn        ";
            $query .= "    ,:disc_kbn         ";
            $query .= "     )                  ";
//print_r($data); 
//print_r("new:".$query);
            for($i=0;$i<count($data);$i++){

                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['gift_certi_cd'])){
                    $param                      = [];
                    $param[":INSUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":INSDATETIME"]	=	"now()";
                    $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
                    $param[":UPDDATETIME"]	=	"now()";
                    $param[":DISABLED"]		=	'0';
                    $param[":LAN_KBN"]		=	'0';
                    $param[":CONNECT_KBN"]	=       '0';
                    $param[":ORGANIZATION_ID"]  =       $org_id;
                    $param[":gift_certi_cd"]	=	$data[$sind]['gift_certi_cd'];
                    $param[":gift_certi_nm"]	=	$data[$sind]['gift_certi_nm'];
                    $param[":gift_certi_kn"]	=	$data[$sind]['gift_certi_kn'];
                    $param[":change_kbn"]	=	$data[$sind]['change_kbn'];
                    $param[":disc_money"]	=	empty($data[$sind]['disc_money']) ? 0 : str_replace(',', '', $data[$sind]['disc_money']);
                    $param[":point_kbn"]	=	$data[$sind]['point_kbn'];
                    $param[":disc_kbn"]         =       '0';
                    
//print_r($param);                
                    $result = $DBA->executeSQL_no_searchpath($query, $param); 
                    $param1                      = [];
                    $param1[":gift_certi_cd"]        = $data[$sind]['gift_certi_cd'];
                    $param1[":ORGANIZATION_ID"]  = $org_id;
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where gift_certi_cd = :gift_certi_cd";
                    $query_del .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param1);
                }
   
            }
            $Log->trace("END add_mst5901"); 
        }
        public function del_mst5901($org_id,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START del_mst5901");  
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst5901 ";
            $query .= " set UPDUSER_CD='delete' where gift_certi_cd = :gift_certi_cd";
            $query .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";
            
            $query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST5901_CHANGED ";
            $query_del .= " where gift_certi_cd = :gift_certi_cd";
            $query_del .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";
            
            $query_del_1  = "";
            $query_del_1 .= " delete from ". $_SESSION["SCHEMA"].".MST5901";
            $query_del_1 .= " where gift_certi_cd = :gift_certi_cd";
            $query_del_1 .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";
            
            for($i=0;$i<count($data);$i++){
                $sind='"'.$i.'"';
                if(is_numeric ($data[$sind]['gift_certi_cd'])){            
                    $param["gift_certi_cd"] = $data[$sind]['gift_certi_cd'];
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
            $Log->trace("END del_mst5901"); 
        }
        public function upd_mst5901($org_id,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd_mst0401");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst5901 set  ";            
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
                $query1 = "";
                $param1 = [];
                if(is_numeric ($data[$sind]['gift_certi_cd'])){
                    foreach(  $data[$sind] as $key => $value){
                        $do_query = 0;
                        if($key === "gift_certi_cd"){
                            $param1[':'.$key] = $value;
                            continue;
                        }else if($key === "refund_nm" || $key === "change_kbn_nm" ){
                            continue;
                        }
                        
                        if($value === ""|| isset($value)){
                            //echo "value1 = $value";
                            $do_query = 1;
                            $query1 .= "  ,$key =    :$key ";
                            if($key === "disc_money"){
                                $param1[':'.$key] = empty($value) ? 0 : str_replace(',', '', $value);
                            }else{
                                $param1[':'.$key] = $value;   
                            }
                        }
                    }
                    
                    if($do_query === 0){
                        continue;
                    }                  
                    $query1 .= " where gift_certi_cd = :gift_certi_cd";
                    $query1 .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";                    
//print_r("update:".$query);
//print_r($param);
                    $result = $DBA->executeSQL_no_searchpath($query.$query1, array_merge($param, $param1));
                    
                    $param2 = [];
                    $param2[":gift_certi_cd"]   = $data[$sind]['gift_certi_cd'];
                    $param2[":ORGANIZATION_ID"]  = $org_id;                    
                    $query_del  = "";
                    $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
                    $query_del .= " where gift_certi_cd = :gift_certi_cd";
                    $query_del .= "  and ORGANIZATION_ID = :ORGANIZATION_ID";                    
                    
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param2);                    
//print_r($result);                    
                }
            }          
            $Log->trace("END upd_mst5901");
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
        public function gift_size($org_id){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START gift_size");
            $Log->trace("END gift_size");
            $sql    = "";
            $sql   .= " select gift_certisize from mst0010 ";
            $sql   .= "  where organization_id = :org_id limit 1";

            $searchArray[":org_id"] = $org_id; 
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $Datas = '';

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst5901_data");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $Datas = $data["gift_certisize"];
            }

            $Log->trace("END get_mst5901_data");

            return $Datas;            
        }
        
    }
?>