<?php
    /**
     * @file      得点倍率マスタ
     * @author    K.Sakamoto
     * @date      2017/08/21
     * @version   1.00
     * @note      得点倍率テーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     *得点倍率クラス
     * @note   得点倍率テーブルの管理を行う。
     */
    class ScaledScore extends BaseModel
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
 
        public function getmst1601($org_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getmst1601");

            $sql = " select ";
            $sql.= " prate_cd, "; 
            $sql.= " prate_kbn, "; 
            $sql.= " effect_str, "; 
            $sql.= " effect_end, "; 
            $sql.= " effect_kbn, "; 
            $sql.= " prate, "; 
            $sql.= " mon_kbn, "; 
            $sql.= " tue_kbn, ";             
            $sql.= " wed_kbn, "; 
            $sql.= " thu_kbn, "; 
            $sql.= " fri_kbn, "; 
            $sql.= " sat_kbn, "; 
            $sql.= " sun_kbn, "; 
            $sql.= " set_day1, "; 
            $sql.= " set_day2, "; 
            $sql.= " set_day3, "; 
            $sql.= " set_day4, "; 
            $sql.= " set_day5, "; 
            $sql.= " set_day6, "; 
            $sql.= " set_day7 "; 
            $sql.= " from mst1601 where ";
            $sql.= " organization_id = :org_id ";
            $sql.= " order by prate_cd ";
            $searchArray = [];

            $searchArray[":org_id"]   = $org_id;
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            //print_r($sql);
            //print_r($searchArray); 
            // 一覧表を格納する空の配列宣言
            $allData = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getmst1601");
                return $allData;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $allData[] = $data;
            }

            $Log->trace("END getmst1601");

            return $allData;
        }
        
        public function getmst1602list($org_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getmst1602list");

            $sql = " select prate_cd,string_agg(sect_cd,',') as list from mst1602 ";
            $sql.= "  where organization_id = :org_id ";
            $sql.= " group by prate_cd order by prate_cd ";
            $searchArray = array(':org_id' => $org_id);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $allData = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getmst1602list");
                return $allData;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $allData[$data["prate_cd"]]["list"] = $data["list"];
            }

            $Log->trace("END getmst1602list");

            return $allData;
        }        
 
        public function getmst1201($org_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getmst1201");
            $searchArray = [];
            $sql = " select sect_cd,sect_nm ";
            $sql.= " from MST1201 ";
            $sql.= "  where organization_id = :org_id ";            
            $sql.= " order by sect_cd";
            
            $searchArray[":org_id"] = $org_id;
            //print_r($_SESSION);
            //print_r($sql);
            //print_r($searchArray);         
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

             // 一覧表を格納する空の配列宣言
            $allData = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getmst1201");
                return $allData;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $allData[] = $data;
                //print_r($data);
            }

            $Log->trace("END getmst1201");

            return $allData;
        } 
        
        public function addData_mst1601($org_id,$prate_cd,$val){
            global $DBA, $Log; // グローバル変数宣言
            $p_cd = str_replace('"',"",$prate_cd);
            $Log->trace("START addData_mst1601");
            $query  = "";
            $query .= " select count(*) from mst1601 ";
            $query .= "  where organization_id = :org_id ";
            $query .= "    and prate_cd = :prate_cd ";
            $param[":org_id"]   = $org_id;
            $param[":prate_cd"] = $p_cd;
            //echo'param:';
            //print_r($param);
            // SQLの実行
            $result = $DBA->executeSQL($query, $param);

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END addData_mst1601");
                return ;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $count = $data["count"];
            } 
            if($count > 0){
                $this->update_mst1601($org_id,$p_cd,$val);
            }else{
                $this->insert_mst1601($org_id,$p_cd,$val);
            }
            $Log->trace("END addData_mst1601");
        }
        public function update_mst1601($org_id,$prate_cd,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START update_mst1601");            
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst1601 set ";
            $query .= "  UPDUSER_CD        = 	:UPDUSER_CD         ";
            $query .= " ,UPDDATETIME       = 	:UPDDATETIME        ";            
            foreach($data as $key => $value){
                $query .= " ,$key = :$key ";
                $param[":$key"] = $value;
            }
            $query .= "  where organization_id = :org_id ";
            $query .= "    and prate_cd = :prate_cd ";
            $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
            $param[":UPDDATETIME"]	=	"now()";            
            $param[":org_id"]   = $org_id;
            $param[":prate_cd"] = $prate_cd;
            //print_r($query);
            //print_r($param);
            
            $result = $DBA->executeSQL_no_searchpath($query, $param);
        }
        public function insert_mst1601($org_id,$prate_cd,$data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START insert_mst1601");
            //echo "data:";
            //print_r($data);
            $query  = "";
            $query  = " insert into ". $_SESSION["SCHEMA"].".mst1601 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,ORGANIZATION_ID    ";
            $query .= "    ,prate_cd           ";
            
            $val .= "     :INSUSER_CD         ";
            $val .= "    ,:INSDATETIME        ";
            $val .= "    ,:UPDUSER_CD         ";
            $val .= "    ,:UPDDATETIME        ";
            $val .= "    ,:DISABLED           ";
            $val .= "    ,:LAN_KBN            ";
            $val .= "    ,:CONNECT_KBN        ";
            $val .= "    ,:ORGANIZATION_ID    ";
            $val .= "    ,:prate_cd           ";   
            
            $param[":INSUSER_CD"]	=	$_SESSION["USER_ID"];
            $param[":INSDATETIME"]	=	"now()";
            $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":DISABLED"]		=	'0';
            $param[":LAN_KBN"]		=	'0';
            $param[":CONNECT_KBN"]	=       '0';
            $param[":ORGANIZATION_ID"]	=       $org_id;
            $param[":prate_cd"]         =       $prate_cd; 
            foreach($data as $key => $value){
                $query .= " ,$key  ";
                $val .= " ,:$key  ";
                $param[":$key"] = $value;
            }
            $query .= ") values ( $val )";
            //print_r($query);
            //print_r($param);
            
            $result = $DBA->executeSQL_no_searchpath($query, $param);            
        }
        
        public function addData_mst1602($org_id,$prate_cd,$val){
            global $DBA, $Log; // グローバル変数宣言
            
            //print_r($val);
            //print_r($prate_cd);
            $p_cd = str_replace('"',"",$prate_cd);
            $Log->trace("START addData_mst1602");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst1602 set ";
            $query .= "  UPDUSER_CD        = 	:UPDUSER_CD         ";
            $query .= "  where organization_id = :org_id ";
            $query .= "    and prate_cd = :prate_cd ";            
           
            $param = [];
            $param[":UPDUSER_CD"]   = "Delete";            
            $param[":org_id"]       = $org_id;
            $param[":prate_cd"]     = $p_cd;
            //echo'param:';
            //print_r($param);
            //print_r($query);
            // SQLの実行
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".mst1602 ";
            $query .= "  where organization_id = :org_id ";
            $query .= "    and prate_cd = :prate_cd ";            
            
            $param = [];
            $param[":org_id"]       = $org_id;
            $param[":prate_cd"]     = $p_cd;
            //echo'param:';
            //print_r($param);
            // SQLの実行
            $result = $DBA->executeSQL_no_searchpath($query, $param); 
            
            
            $query  = "";
            $query  = " insert into ". $_SESSION["SCHEMA"].".mst1602 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,ORGANIZATION_ID    ";
            $query .= "    ,prate_cd           ";
            $query .= "    ,sect_cd            ";
            $query .= " )values (              ";
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CONNECT_KBN       ";
            $query .= "    ,:ORGANIZATION_ID   ";
            $query .= "    ,:prate_cd          ";
            $query .= "    ,:sect_cd           ";
            $query .= " )                      ";
            
            $param = []; 
            $param[":INSUSER_CD"]	=	$_SESSION["USER_ID"];
            $param[":INSDATETIME"]	=	"now()";
            $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":DISABLED"]		=	'0';
            $param[":LAN_KBN"]		=	'0';
            $param[":CONNECT_KBN"]	=       '0';
            $param[":ORGANIZATION_ID"]	=       $org_id;
            $param[":prate_cd"]         =       $p_cd; 
            
            //echo "query: $query";
            $sect_cd = explode(',',$val['list']);
            //print_r($sect_cd);
            for($i=0;$i<count($sect_cd);$i++){
                $param[":sect_cd"] = $sect_cd[$i];
                //print_r($param);
                if($param[":sect_cd"]){
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                }
            }
            $Log->trace("END addData_mst1602");
        }        
        
    }
?>