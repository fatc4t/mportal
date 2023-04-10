<?php
    /**
     * @file      画面PLU設定マスタ(MST6701)
     * @author    柴田
     * @date      
     * @version   1.00
     * @note     
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';    
    /**
     * POS予約商品マスタ(MST0211)クラス
     * @note   POS予約商品マスタテーブルの管理を行う。
     */
    class Mst6701 extends BaseModel
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
         * POSシステムマスタ取得
         * @param    $organization_id
         * @return   SQLの実行結果
         */
        public function getMst0010Data( $organization_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst0010Data");

            $sql = "SELECT * FROM mst0010 WHERE organization_id = :organization_id";

            $searchArray = array(
                ':organization_id'      => $organization_id,
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0010DataList = array();

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst0010DataList = $data;
            }

            $Log->trace("END getMst0010Data");

            return $mst0010DataList;
        }
        
        /**
         * POS商品部門マスタデータ検索
         * @param    $organization_id, $sect_cd
         * @return   SQLの実行結果
         */
        public function getMst1201Data( $organization_id, $sect_cd )
        {
            global $DBA, $Log; // getMst1201Data変数宣言
            $Log->trace("START getMst1201Data");

            $sql  = "";
            $sql .= " SELECT";
            $sql .= "   mst1201.insuser_cd";
            $sql .= " , mst1201.insdatetime";
            $sql .= " , mst1201.upduser_cd";
            $sql .= " , mst1201.upddatetime";
            $sql .= " , mst1201.disabled";
            $sql .= " , mst1201.lan_kbn";
            $sql .= " , mst1201.organization_id";
            $sql .= " , mst1201.sect_cd";
            $sql .= " , mst1201.sect_nm";
            $sql .= " , mst1201.sect_kn";
            $sql .= " , mst1201.prod_cd";
            $sql .= " , mst1201.type_cd";
            $sql .= " , mst1201.sect_profit";
            $sql .= " , mst1201.tax_type";
            $sql .= " , mst1201.sect_tax";
            $sql .= " , mst1201.point_kbn";
            $sql .= " , mst1201.sect_prate";
            $sql .= " , mst1201.sect_disc_rate";
            $sql .= " , mst1201.cust_disc_rate";
            $sql .= " , mst1201.empl_kbn";
            $sql .= " , mst1201.disc_not_kbn";
            $sql .= " , mst1201.today_sale_prt";
            $sql .= " FROM mst1201";
            $sql .= " where mst1201.organization_id = :organization_id and mst1201.sect_cd = :sect_cd";

            $searchArray = array(
                ':organization_id'      => $organization_id,
                ':sect_cd'              => $sect_cd,
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1201DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst1201Data");
                return $mst1201DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst1201DataList = $data;
            }

            $Log->trace("END getMst1201Data");

            return $mst1201DataList;
        }
        /**
         * 画面PLU設定マスタデータ検索
         * @param    $organization_id
         * @return   SQLの実行結果
         */
        public function getMst6701Data( $organization_id)
        {
            global $DBA, $Log; // getMst6701Data変数宣言
            $Log->trace("START getMst6701Data");
            $sql  = "select a.*,";
            $sql .= " regexp_replace (replace(coalesce(replace(b.disp_prod_nm1,'\"','\\\"'),''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as disp_prod_nm1, ";
            $sql .= " regexp_replace (replace(coalesce(replace(b.disp_prod_nm2,'\"','\\\"'),''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as disp_prod_nm2, ";
            $sql .= " regexp_replace (replace(coalesce(replace(b.prod_nm,'\"','\\\"'),''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as prod_nm ";
            $sql .= " from mst6701 a  left join mst0201 b on a.organization_id = b.organization_id and a.plu_cd = b.prod_cd and a.plu_kbn = '0' WHERE a.organization_id = :organization_id";

            $searchArray = array(
                ':organization_id'      => $organization_id,
            );
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0010DataList = array();

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
            $mst0010DataList[$data['reji_no']][$data['func_kbn']][$data['func_no']] = $data;
            }

            $Log->trace("END getMst6701Data");

            return $mst0010DataList;            
        }

      
        //商品
        public function getprod_detail($organization_id){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getprod_detail");
            $query  = "";
            $query .= " select ";
            $query .= "    distinct(a.prod_cd) ";
            $query .= "   ,regexp_replace (replace(coalesce(replace(a.prod_nm,'\"','\\\"'),''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as prod_nm  ";
            $query .= "   ,regexp_replace (replace(replace(coalesce(a.prod_kn,''),'''',' '),'\"','\\\"'),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as prod_kn ";
            $query .= "   ,a.head_supp_cd as supp_cd";
            $query .= "   ,a.sect_cd ";
            $query .= "   ,a.appo_prod_kbn ";
            $query .= "   ,a.disp_prod_nm1 ";
            $query .= "   ,a.disp_prod_nm2 ";
            $query .= " from mst0201 a ";
            $query .= " where  organization_id = :organization_id";
            $query .= " order by prod_cd ";
            $param = array(
                ':organization_id'      => $organization_id,
            );
            
            //$query .= " offset 70000 limit 10000 ";
            //$param[":val"] = 1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            //print_r($result);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getprod_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $Datas['sorted'][$data['prod_cd']] = $data;
                //$Datas['modal'][] = $data;
            }            
                   
            $Log->trace("END getprod_detail");
            return $Datas;
        }
        //部門
        public function getsect_detail($organization_id){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getsect_detail");
            $query  = "";
            $query .= " select ";
            $query .= "    a.sect_cd ";
            $query .= "   ,regexp_replace (replace(replace(coalesce(a.sect_nm,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as sect_nm ";           
            $query .= " from mst1201 a ";
            $query .= " where organization_id = :organization_id ";
            $query .= " group by a.sect_cd ,coalesce(a.sect_nm,'') ";
            $query .= " order by sect_cd ";
            $param = array(
                ':organization_id'      => $organization_id,
            );
                       
            $result = $DBA->executeSQL($query, $param);
            //print_r($query);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getsect_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $Datas['sorted'][$data['sect_cd']] = $data;
                $Datas['modal'][] = $data;
            }            
                   
            $Log->trace("END getsect_detail");
            return $Datas;
        } 
        public function add_mst6701($data){
            global $DBA, $Log; // グローバル変数宣言
            
            $Log->trace("START add_mst6701");             
            if($data['delete']){
                $param                      = [];
                $param[":ORGANIZATION_ID"]  =       $data['org_id'];
                $param[":reji_no"]          =	$data['reji_no'];
                
                $query .= " update ". $_SESSION["SCHEMA"].".MST6701 set upduser_cd = 'delete' ";
                $query .= " where ( (reji_no = :reji_no";
                $query .= "   and ORGANIZATION_ID = :ORGANIZATION_ID "." and (func_kbn,func_no) in (".$data['delete'].") )";
//                if($data['org_select1'] === 'empty' && $data['org_list'] !== ''){
//                     $query .= "   or ( ORGANIZATION_ID in (".$data['org_list'].") and reji_no ='".$data['reji_no']."' and (ORGANIZATION_ID,reji_no) <> (".$data['org_id'].",'".$data['reji_no']."') ) ";
//                }else if($data['org_select1'] !== '' && $data['org_select1'] !== 'empty' && $data['reji_no_1']){
//                    $query .= "   or ( ORGANIZATION_ID = ".$data['org_select1'];
//                    $query .= "   and reji_no = '".$data['reji_no_1']."' and (ORGANIZATION_ID,reji_no) <> (".$data['org_id'].",'".$data['reji_no']."')) ";
//                }
                 $query .= " ) ";                
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                $param                      = [];
                $param[":ORGANIZATION_ID"]  =       $data['org_id'];
                $param[":reji_no"]          =	$data['reji_no'];
                //$param[":func_kbn"]         =	$data['func_kbn'];
                //$param[":func_no"]          =	$data['func_no'];
                $query_del  = "";
                $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST6701 ";
                $query_del .= " where ( (reji_no = :reji_no";
                $query_del .= "   and ORGANIZATION_ID = :ORGANIZATION_ID "." and (func_kbn,func_no) in (".$data['delete'].") )";
//                if($data['org_select1'] === 'empty' && $data['org_list'] !== ''){
//                     $query_del .= "   or ( ORGANIZATION_ID in (".$data['org_list'].") and reji_no ='".$data['reji_no']."' and (ORGANIZATION_ID,reji_no) <> (".$data['org_id'].",'".$data['reji_no']."') ) ";
//                }else if($data['org_select1'] !== '' && $data['org_select1'] !== 'empty' && $data['reji_no_1']){
//                    $query_del .= "   or ( ORGANIZATION_ID = ".$data['org_select1'];
//                    $query_del .= "   and reji_no = '".$data['reji_no_1']."' and (ORGANIZATION_ID,reji_no) <> (".$data['org_id'].",'".$data['reji_no']."')) ";
//                }
                 $query_del .= " ) ";
                //$query_del .= " and (func_kbn,func_no) in (".$data['delete'].")";
                $result = $DBA->executeSQL_no_searchpath($query_del, $param);
              
            }
             

            $param                      = [];
            $param[":ORGANIZATION_ID"]  =       $data['org_id'];
            $param[":reji_no"]          =	$data['reji_no'];
            
            $query = "";
            $query .= " insert into ". $_SESSION["SCHEMA"] .".mst6701 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,ORGANIZATION_ID    ";
            $query .= "    ,reji_no          ";
            $query .= "    ,func_kbn          ";
            $query .= "    ,func_no          ";
            $query .= "    ,func_name         ";
            $query .= "    ,plu_kbn          ";
            $query .= "    ,plu_cd         ";
            $query .= " ) values (             ";
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CONNECT_KBN       ";
            $query .= "    ,:ORGANIZATION_ID   ";            
            $query .= "    ,:reji_no         ";
            $query .= "    ,:func_kbn         ";
            $query .= "    ,:func_no         ";
            $query .= "    ,:func_name        ";
            $query .= "    ,:plu_kbn         ";
            $query .= "    ,:plu_cd        ";
            $query .= "     )                  ";
//print_r($data); 
//print_r("new:".$query);
            $param[":INSUSER_CD"]	=	$_SESSION["LOGIN"];
            $param[":INSDATETIME"]	=	"now()";
            $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":DISABLED"]		=	'0';
            $param[":LAN_KBN"]		=	'0';
            $param[":CONNECT_KBN"]	=       '0';
            $param[":ORGANIZATION_ID"]  =       $data['org_id'];
            $param[":reji_no"]          =	$data['reji_no'];
            //print_r($data['working_data']['ins']);
            for( $i=0;$i<count($data['working_data']['ins']);$i++){

                if(!$data['working_data']['ins'][$i]){
                    //print_r($data['working_data'][$i]);
                    continue;
                }
                //print_r($data['working_data'][$i]);
                $param[":func_kbn"]         =	$data['working_data']['ins'][$i]['func_kbn'];
                $param[":func_no"]          =	$data['working_data']['ins'][$i]['func_no'];
                $param[":func_name"]	=	'PG'.str_pad($data['working_data']['ins'][$i]['func_kbn'],2,0,STR_PAD_LEFT).str_pad($data['working_data']['ins'][$i]['func_no'],2,0,STR_PAD_LEFT);
                $param[":plu_kbn"]          =	$data['working_data']['ins'][$i]['plu_kbn'];
                $param[":plu_cd"]           =	$data['working_data']['ins'][$i]['plu_cd'];       
  
               $result = $DBA->executeSQL_no_searchpath($query, $param); 
            }

            if($data['update']){
                $query  = "";
                $query .= " update ". $_SESSION["SCHEMA"].".mst6701 set  ";            
                $query .= "  UPDUSER_CD        = 	:UPDUSER_CD        , ";
                $query .= "  UPDDATETIME       = 	:UPDDATETIME       , ";
                $query .= "  DISABLED          = 	:DISABLED          , ";
                $query .= "  CONNECT_KBN       =    :CONNECT_KBN       , "; 
                $query .= "  plu_kbn           =    :plu_kbn           ,  ";
                $query .= "  plu_cd            =    :plu_cd              ";
                $query .= " where ( ORGANIZATION_ID = :ORGANIZATION_ID  and reji_no =:reji_no ";
/*                if($data['org_list'] !== ''){
                    $query .= " or  ORGANIZATION_ID in (".$data['org_list'].")";
                }*/
                $query .= " ) and (func_kbn = :func_kbn and func_no = :func_no) ";
                $param = [];
                $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
                $param[":UPDDATETIME"]	=	"now()";
                $param[":DISABLED"]		=	'0';
                $param[":CONNECT_KBN"]	=       '0';
                $param[":ORGANIZATION_ID"]   =   $data['org_id'];  
                $param[":reji_no"]           =   $data['reji_no'];
                for($i=0;$i<count($data[working_data]['upd']);$i++){ 
                    $param[":func_kbn"]         =	$data['working_data']['upd'][$i]['func_kbn'];
                    $param[":func_no"]          =	$data['working_data']['upd'][$i]['func_no'];
                    $param[":plu_kbn"]          =	$data['working_data']['upd'][$i]['plu_kbn'];
                    $param[":plu_cd"]           =	$data['working_data']['upd'][$i]['plu_cd']; 
                    //print_r($param);
                    //print_r($query);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                }
            }
            
            if($data['insert'] || $data['update']){               
                $param                      = [];
                $param[":ORGANIZATION_ID"]  =       $data['org_id'];
                $param[":reji_no"]          =	$data['reji_no'];
                //$param[":func_kbn"]         =	$data['func_kbn'];
                //$param[":func_no"]          =	$data['func_no'];
                $query_del  = "";
                $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_mportal ";
                $query_del .= " where ( (reji_no = :reji_no";
                //$query_del .= "   and func_kbn = :func_kbn";
                //$query_del .= "   and func_no = :func_no";
                $query_del .= "   and ORGANIZATION_ID = :ORGANIZATION_ID )";
                if($data['org_select1'] === 'empty' && $data['org_list'] !== ''){
                     $query_del .= "   or ( ORGANIZATION_ID in (".$data['org_list'].") and reji_no ='".$data['reji_no']."' ) ";
                }else if($data['org_select1'] !== '' && $data['org_select1'] !== 'empty' && $data['reji_no_1']){
                    $query_del .= "   or ( ORGANIZATION_ID = ".$data['org_select1'];
                    $query_del .= "   and reji_no = '".$data['reji_no_1']."') ";
                }
                $query_del .= " ) ";
                if($data['insert'] && $data['update']){
                    $query_del .= " and ((func_kbn,func_no) in (".$data['insert'].") or (func_kbn,func_no) in (".$data['update'].")) ";
                }else if ($data['insert']){
                    $query_del .= " and (func_kbn,func_no) in (".$data['insert'].") ";
                }else if ($data['update']){
                    $query_del .= " and (func_kbn,func_no) in (".$data['update'].") ";
                }
                $query_del .= " and table_name = 'mst6701' ";
                //print_r('$data[insert]: '.$data['insert']);
                //print_r('$data[update]: '.$data['update']);
                //print_r($query_del);
                $result = $DBA->executeSQL_no_searchpath($query_del, $param); 
                
                
            }
            $Log->trace("END add_mst6701"); 
        }
     
         public function copy_mst6701($data){
            global $DBA, $Log; // グローバル変数宣言
            
            $Log->trace("START add_mst6701");        
        
            //print_r($data['org_select1'].' / '.$data['org_list']);
            if(!($data['org_select1'] === 'empty' && $data['org_list'] === '')){
                

                $param=[];
                $query = "DROP TRIGGER trg_mst6701_del ON ". $_SESSION["SCHEMA"].".mst6701; ";
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                
                $param                      = [];
                $param[':val'] = 1;
                //$param[":ORGANIZATION_ID"]  = $data['org_id'];
                //$param[":reji_no"]          = $data['reji_no'];                    
                //$param[":func_no"]          =	$data['func_no'];
                $query_del  = "";
                $query_del .= " delete from ".$_SESSION["SCHEMA"].".MST6701 ";
                $query_del .= " where ";
                if($data['org_select1'] === 'empty' && $data['org_list'] !== ''){
                     $query_del .= "  ( ORGANIZATION_ID in (".$data['org_list'].") and reji_no ='".$data['reji_no']."' ) ";
                }else{
                    $query_del .= "  ( ORGANIZATION_ID = ".$data['org_select1'];
                    $query_del .= "   and reji_no = '".$data['reji_no_1']."') ";
                }
                $query_del .= "    and (ORGANIZATION_ID,reji_no) <> (".$data['org_id'].",'".$data['reji_no']."') and 1 = :val ";
                //$query_del .= " and (func_kbn,func_no) in (".$data['delete'].")";
                $result = $DBA->executeSQL_no_searchpath($query_del, $param);       
                //print_r('koko1');

                $param=[];
                $query = "create trigger trg_mst6701_del before delete on  ".$_SESSION["SCHEMA"].".mst6701 for each row execute procedure fnc_mst9999_deleted_row();";
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                //print_r('koko2');
                $param                      = [];
                $param[":ORGANIZATION_ID"]  = $data['org_id'];
                $param[":reji_no"]          = $data['reji_no'];                

                $query1  = " select ";
                $query1 .= "     m.INSUSER_CD         ";
                $query1 .= "    ,m.INSDATETIME        ";
                $query1 .= "    ,m.UPDUSER_CD         ";
                $query1 .= "    ,m.UPDDATETIME        ";
                $query1 .= "    ,m.DISABLED           ";
                $query1 .= "    ,m.LAN_KBN            ";
                $query1 .= "    ,m.CONNECT_KBN        ";
                $query1 .= "    ,a.org_id             ";
                $query1 .= "    ,a.reji_no            ";
                $query1 .= "    ,m.func_kbn           ";
                $query1 .= "    ,m.func_no            ";
                $query1 .= "    ,m.func_name          ";
                $query1 .= "    ,m.plu_kbn            ";
                $query1 .= "    ,m.plu_cd             ";   
                $query1 .= " from  ". $_SESSION["SCHEMA"].".mst6701 m, (select unnest(array[";
                if($data['org_select1'] === 'empty' && $data['org_list'] !== ''){
                    $query1 .= $data['org_list'];
                }else{
                    $query1 .= $data['org_select1'];
                }
                $query1 .= "])::int as org_id,'".$data['reji_no_1']."' ::text as reji_no) a ";
                $query1 .= "  where ";
                $query1 .= "      m.organization_id = :ORGANIZATION_ID ";
                $query1 .= "    and m.reji_no = :reji_no ";
                $query1 .= "    and (a.org_id, a.reji_no) <> (m.organization_id, m.reji_no) ";
                $query1 .= " order by a.org_id,func_no  ";   
/*
$result = $DBA->executeSQL($query1, $param);
//print_r($query1);
$Datas = array();

// データ取得ができなかった場合、空の配列を返す
if( $result === false )
{
$Log->trace("END getsect_detail");
return $Datas;
}

// 取得したデータ群を配列に格納
while ( $data1 = $result->fetch(PDO::FETCH_ASSOC) )
{
$Datas[] = $data1;

}   
 */


//print_r($Datas);               
//print_r('koko1');
                $query  = "";   
                $query .= " insert into ". $_SESSION["SCHEMA"].".MST6701 (";
                $query .= "     INSUSER_CD         ";
                $query .= "    ,INSDATETIME        ";
                $query .= "    ,UPDUSER_CD         ";
                $query .= "    ,UPDDATETIME        ";
                $query .= "    ,DISABLED           ";
                $query .= "    ,LAN_KBN            ";
                $query .= "    ,CONNECT_KBN        ";
                $query .= "    ,ORGANIZATION_ID    ";
                $query .= "    ,reji_no            ";
                $query .= "    ,func_kbn           ";
                $query .= "    ,func_no            ";
                $query .= "    ,func_name          ";
                $query .= "    ,plu_kbn            ";
                $query .= "    ,plu_cd             )";  
                $query .= $query1;
                
                //print_r($query);
                $result = $DBA->executeSQL_no_searchpath($query, $param); 
                $param                      = [];
                $param[':val'] = 1;
               // $param[":ORGANIZATION_ID"]  =       $data['org_id'];
                //$param[":reji_no"]          =	$data['reji_no'];
                //$param[":func_kbn"]         =	$data['func_kbn'];
                //$param[":func_no"]          =	$data['func_no'];
                $query_del  = "";
                $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_mportal ";
                $query_del .= " where table_name = 'mst6701' and (organization_id,reji_no) in ";
                $query_del .= " ( ";
                $query_del .= "     select a.org_id,a.reji_no ";
                $query_del .= "     from (select unnest(array[";
                if($data['org_select1'] === 'empty' && $data['org_list'] !== ''){
                        $query_del .= $data['org_list'];
                }else{
                        $query_del .= $data['org_select1'];
                }
                        $query_del .= "])::int as org_id,'".$data['reji_no_1']."' ::text as reji_no) a ";
                $query_del .= "     where 1 = :val and (a.org_id, a.reji_no) <> (".$data['org_id'].",'".$data['reji_no']."') ";                    
                $query_del .= " ) ";                   
                $result = $DBA->executeSQL_no_searchpath($query_del, $param); 
               // //print_r($query_del);

//                for($i=1;$i<11;$i++){
//                    for($j=1;$j<57;$j++){
//                        if(strpos($data['insert'].','.$data['update'],'('.$i.','.$j.')') !== false){
//                            continue;
//                        }
                        $query = "";
                        $query .= " insert into ". $_SESSION["SCHEMA"].".mst9999_mportal (";
                        $query .= "      table_name ";
                        $query .= "     ,organization_id ";
                        $query .= "     ,func_kbn ";
                        $query .= "     ,func_no ";
                        $query .= "     ,reji_no ";
                        $query .= " ) select ";
                        $query .= "      'mst6701' as table_name ";
                        $query .= "     ,a.org_id ";
                        $query .= "     ,b.func_kbn";
                        $query .= "     ,c.func_no ";             
                        $query .= "     ,a.reji_no ";
                        $query .= " from (select unnest(array[";
                        if($data['org_select1'] === 'empty' && $data['org_list'] !== ''){
                            $query .= $data['org_list'];
                        }else{
                            $query .= $data['org_select1'];
                        }
                        $query .= "])::int as org_id,'".$data['reji_no_1']."' ::text as reji_no) a ";
                        $query .= "  left join (select generate_series(1, 10) as func_kbn) b on (true) ";
                        $query .= "  left join (select generate_series(1, 56) as func_no) c on (true) ";
                        $query .= "  left join (select organization_id,reji_no,func_kbn,func_no from ". $_SESSION["SCHEMA"].".mst6701 where organization_id = :ORGANIZATION_ID and reji_no = :reji_no) m using (func_kbn,func_no) ";                         
                        $query .= " where (a.org_id, a.reji_no) <> (".$data['org_id'].",'".$data['reji_no']."') and m.reji_no is null ";
                        $param = [];
                        $param[":ORGANIZATION_ID"]  = $data['org_id'];
                        $param[":reji_no"]          = $data['reji_no'];
                        //print_r($query);
                        $result = $DBA->executeSQL_no_searchpath($query, $param);                 

            }        
         }
         public function upd_mst0010($data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd_mst0010");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0010 set  ";            
            $query .= "  UPDUSER_CD        = 	:UPDUSER_CD        , ";
            $query .= "  UPDDATETIME       = 	:UPDDATETIME       , ";
            $query .= "  DISABLED          = 	:DISABLED          , ";
            $query .= "  CONNECT_KBN       =    :CONNECT_KBN       , "; 
            $query .= "  disp_plu1   =    :disp_plu1   ,  ";
            $query .= "  disp_plu2   =    :disp_plu2   ,  ";
            $query .= "  disp_plu3   =    :disp_plu3   ,  ";
            $query .= "  disp_plu4   =    :disp_plu4   ,  ";
            $query .= "  disp_plu5   =    :disp_plu5   ,  ";
            $query .= "  disp_plu6   =    :disp_plu6   ,  ";
            $query .= "  disp_plu7   =    :disp_plu7   ,  ";
            $query .= "  disp_plu8   =    :disp_plu8   ,  ";
            $query .= "  disp_plu9   =    :disp_plu9   ,  ";
            $query .= "  disp_plu10  =    :disp_plu10     ";
            $query .= " where  ORGANIZATION_ID = :ORGANIZATION_ID   ";
            if($data['org_list'] !== ''){
                $query .= " or  ORGANIZATION_ID in (".$data['org_list'].")";
            }
            $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":DISABLED"]		=	'0';
            $param[":CONNECT_KBN"]	=       '0';
            $param[":ORGANIZATION_ID"]  =       $data['org_id'];
            $param[":disp_plu1"]  =       $data['disp_plu1'];
            $param[":disp_plu2"]  =       $data['disp_plu2'];
            $param[":disp_plu3"]  =       $data['disp_plu3'];
            $param[":disp_plu4"]  =       $data['disp_plu4'];
            $param[":disp_plu5"]  =       $data['disp_plu5'];
            $param[":disp_plu6"]  =       $data['disp_plu6'];
            $param[":disp_plu7"]  =       $data['disp_plu7'];
            $param[":disp_plu8"]  =       $data['disp_plu8'];
            $param[":disp_plu9"]  =       $data['disp_plu9'];
            $param[":disp_plu10"]  =      $data['disp_plu10'];                 
//print_r("update:".$query);
//print_r($param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);

            $param = [];
            $param[":ORGANIZATION_ID"]  =       $data['org_id'];
            $query_del .= " 　and ORGANIZATION_ID = :ORGANIZATION_ID";                   
            $query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
            $query_del .= " where ORGANIZATION_ID = :ORGANIZATION_ID";
            $query_del .= "   and table_name = 'mst0010'";                    
            $result = $DBA->executeSQL_no_searchpath($query_del, $param);                    
//print_r($result);                    
            $Log->trace("END upd_mst0010");
        }
        
//        public function upd_mst0201($data){
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START upd_mst0201");
//            $query  = "";
//            $query .= " update ". $_SESSION["SCHEMA"].".mst0201 set  ";            
//            $query .= "  UPDUSER_CD        = 	:UPDUSER_CD        , ";
//            $query .= "  UPDDATETIME       = 	:UPDDATETIME       , ";
//            $query .= "  DISABLED          = 	:DISABLED          , ";
//            $query .= "  LAN_KBN           = 	:LAN_KBN           , ";
//            $query .= "  CONNECT_KBN       =    :CONNECT_KBN       , ";
//            $query .= "  disp_prod_nm1   =    :disp_prod_nm1   ,  ";
//            $query .= "  disp_prod_nm2   =    :disp_prod_nm2     ";
//            $query .= " where prod_cd = :prod_cd";
//            $query .= "   and ( ORGANIZATION_ID = :ORGANIZATION_ID "; 
// /*           if($data['org_list'] !== ''){
//                $query .= " or  ORGANIZATION_ID in (".$data['org_list'].") ";
//            }   */  
//            $query .= " ) " ;
//            $param = [];
//            $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
//            $param[":UPDDATETIME"]	=	"now()";
//            $param[":DISABLED"]		=	'0';
//            $param[":LAN_KBN"]		=	'0';
//            $param[":CONNECT_KBN"]	=       '0';            
//            $param[":ORGANIZATION_ID"]  =       $data['org_id'];
//            for($i=0;$i<count($data['prod_data']);$i++){
//                $param[":prod_cd"]                =	$data['prod_data'][$i]['prod_cd'];
//                $param[":disp_prod_nm1"]         =	$data['prod_data'][$i]['disp_prod_nm1'];
//                $param[":disp_prod_nm2"]         =	$data['prod_data'][$i]['disp_prod_nm2'];
//print_r($param);
//print_r($query);
//print_r('koko888');                
//                $result = $DBA->executeSQL_no_searchpath($query, $param); 
//            }
            
        public function prod_mst0201($data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd_mst0201");
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0201 set  ";            
            $query .= "  UPDUSER_CD        = 	:UPDUSER_CD        , ";
            $query .= "  UPDDATETIME       = 	:UPDDATETIME       , ";
            $query .= "  DISABLED          = 	:DISABLED          , ";
            $query .= "  LAN_KBN           = 	:LAN_KBN           , ";
            $query .= "  CONNECT_KBN       =    :CONNECT_KBN       , ";
            $query .= "  disp_prod_nm1   =    :disp_prod_nm1   ,  ";
            $query .= "  disp_prod_nm2   =    :disp_prod_nm2     ";
            $query .= " where prod_cd = :prod_cd";
            $query .= "   and ( ORGANIZATION_ID = :ORGANIZATION_ID "; 
 /*           if($data['org_list'] !== ''){
                $query .= " or  ORGANIZATION_ID in (".$data['org_list'].") ";
            }   */  
            $query .= " ) " ;
            $param = [];
            $param[":UPDUSER_CD"]	=	$_SESSION["LOGIN"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":DISABLED"]		=	'0';
            $param[":LAN_KBN"]		=	'0';
            $param[":CONNECT_KBN"]	=       '0';            
            $param[":ORGANIZATION_ID"]  =       $data['org_id'];
            for($i=0;$i<count($data['prod_data']);$i++){
                $param[":prod_cd"]                =	$data['prod_data'][$i]['prod_cd'];
                $param[":disp_prod_nm1"]         =	$data['prod_data'][$i]['disp_prod_nm1'];
                $param[":disp_prod_nm2"]         =	$data['prod_data'][$i]['disp_prod_nm2'];
//print_r($param);
print_r(json_encode($query));
//print_r('koko888');                
                $result = $DBA->executeSQL_no_searchpath($query, $param); 
            }
        }
                        
        public function prodsecond_mst0201($data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START upd1_mst0201"); 
//print_r($data);
//print_r('koko');
//print_r($data['prod_list']);
            $query  = "update ". $_SESSION["SCHEMA"].".mst0201 m1 set  ";
            $query .= "        upduser_cd = m2.upduser_cd  ";
            $query .= "       ,upddatetime = m2.upddatetime  ";
            $query .= "       ,disabled = m2.disabled  ";
            $query .= "       ,lan_kbn = m2.lan_kbn  ";
            $query .= "       ,connect_kbn = m2.connect_kbn  ";
            $query .= "       ,disp_prod_nm1 = m2.disp_prod_nm1  ";
            $query .= "       ,disp_prod_nm2  = m2.disp_prod_nm2  ";
            $query .= " from (select '".$_SESSION["LOGIN"]."' as upduser_cd, now() as upddatetime,disabled,lan_kbn,connect_kbn,prod_cd,disp_prod_nm1,disp_prod_nm2 from  ". $_SESSION["SCHEMA"].".mst0201 where organization_id = :ORGANIZATION_ID and prod_cd in (".$data['prod_list'].")) m2  ";
            $query .= " where  ";
            $query .= "     m1.prod_cd = m2.prod_cd  ";
            $query .= "     and m1.organization_id in (".$data['org_list'].")  and m1.organization_id <> :ORGANIZATION_ID; ";                
            $param = [];
            $param[":ORGANIZATION_ID"]  =       $data['org_id'];   
//print_r('koko1');         
//print_r($query);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
//print_r($param);
                   
//print_r('koko999');
            
         }
//         
//        public function clean_mst0201($data){
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START upd_mst0201"); 
//            $param = [];
//            $param[":prod_cd"]   = $data['plu_cd'];
//            $param[":ORGANIZATION_ID"]  =  $data['org_id'];                  
//            $query_del  = "";
//            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST9999_MPORTAL ";
//            $query_del .= " where prod_cd = :prod_cd";
//            $query_del .= "   and ORGANIZATION_ID = :ORGANIZATION_ID";
//            $query_del .= "   and table_name = 'mst0201'";            
//
//            $result = $DBA->executeSQL_no_searchpath($query_del, $param);                    
//print_r($result);                    
//            $Log->trace("END upd_mst0201");
//        }
//        
        
        
        
        public function delete_mst6701($data){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delete_mst6701");             
            $param                      = [];
            //print_r($data);
            
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".MST6701 set upduser_cd = 'delete' ";
            $query .= " where reji_no = :reji_no";
            $query .= "   and func_kbn = :func_kbn";
            $query .= "   and func_no = :func_no";
            $query .= "   and ORGANIZATION_ID = :ORGANIZATION_ID";
            
            $query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST6701 ";
            $query_del .= " where reji_no = :reji_no";
            $query_del .= "   and func_kbn = :func_kbn";
            $query_del .= "   and func_no = :func_no";
            $query_del .= "   and ORGANIZATION_ID = :ORGANIZATION_ID";
            for( $i=0;$i<count($data);$i++){                
                $param[":ORGANIZATION_ID"]  =       $data[$i]['org_id'];
                $param[":reji_no"]          =	$data[$i]['reji_no'];
                $param[":func_kbn"]         =	$data[$i]['func_kbn'];
                $param[":func_no"]          =	$data[$i]['func_no'];
//                    //print_r($param);
//                    //print_r($query_del);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query_del, $param);  
                }
            $param                      = [];
            
            /*$query_del  = "";
            $query_del .= " delete from ". $_SESSION["SCHEMA"].".MST6701_changed ";
            $query_del .= " where reji_no = :reji_no";
            $query_del .= "   and func_kbn = :func_kbn";
            $query_del .= "   and func_no = :func_no";
            $query_del .= "   and ORGANIZATION_ID = :ORGANIZATION_ID";
            for( $i=0;$i<count($data);$i++){
                    $param[":ORGANIZATION_ID"]  =       $data[$i]['org_id'];
                    $param[":reji_no"]          =	$data[$i]['reji_no'];
                    $param[":func_kbn"]         =	$data[$i]['func_kbn'];
                    $param[":func_no"]          =	$data[$i]['func_no'];
                    $result = $DBA->executeSQL_no_searchpath($query_del, $param);  
                }*/
            $Log->trace("END delete_mst6701");             
            }
        
        //レジ番号
        public function getreji_no_detail(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getreji_no_detail");
            $query  = "";
            $query .= " select ";
            $query .= "    distinct(organization_id),reji_no ";
            $query .= " from mst6801 a ";
            $query .= " where reji_no <> :reji_no ";
            $query .= " order by organization_id,reji_no ";
            $param = array(
                ':reji_no'      => '01',
            );          
            $result = $DBA->executeSQL($query, $param);
            $Datas = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getreji_no_detail");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $Datas[$data['organization_id']][$data['reji_no']] = $data;
            }            
                   
            $Log->trace("END getreji_no_detail");
            return $Datas;
        }
        
    }
    
?>