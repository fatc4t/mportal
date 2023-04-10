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
    class System extends BaseSystem
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
        public function getorgidlist(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getorgidlist");
            $query  = "";
            $query .= " select organization_id ";
            $query .= " from mst0010 ";
            $query .= " where 1 = :val order by organization_id";
            
            $param[":val"] = 1;
            
            //print_r($query);
            //print_r($param);            
            $result = $DBA->executeSQL($query, $param);
            
            $systemDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getorgidlist");
                return $systemDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $systemDataList[] = $data;
            }            
                   
            $Log->trace("END getorgidlist");
            return $systemDataList;
        }
 
        
        /**
         * 
         * @param    
         * @return   SQLの実行結果
         */
        public function getmst0010($org_id){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getmst0010");
            $query  = "";
            $query .= " select a.*, b.abbreviated_name as organization_name ";
            $query .= " from mst0010 a ";
            $query .= " left join m_organization_detail b on a.organization_id = b.organization_id";
            $query .= " where a.organization_id = :org_id order by organization_id";

            $param[":org_id"] = $org_id;
            //print_r($org_id);
            //print_r($param);
            $result = $DBA->executeSQL($query, $param);
            //echo "getmst0010(".$org_id.")";
            //print_r($result);
            $systemDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getmst0010");
                return $systemDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $systemDataList = $data;
            }            
                   
            $Log->trace("END getmst0010");
            return $systemDataList;
        }
        
        /**
         * 
         * @param    
         * @return   SQLの実行結果
         */        
        public function getrankdata(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getrankdata"); 
            $query  = "";
            $query .= " select cust_rank_nm,cust_rank_cd ";
            $query .= " from mst8401 ";
            $query .= " where 1 = :val order by cust_rank_cd ";
            $param[":val"] = 1;
            
            $result = $DBA->executeSQL($query, $param); 
            
            $systemDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getrankdata");
                return $systemDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $systemDataList[] = $data;
            }            
                   
            $Log->trace("END getrankdata");    
            return $systemDataList;
        }
        
         public function updatedata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START updatedata");        
            $query  = "";
            $query .= "update ". $_SESSION["SCHEMA"].".mst0010 set  ";
           foreach($_POST as $key=>$value) {
               if($key !== "organization_id"){
                    $query .= $key." = :".$key." ,";
                    
                    $param[":".$key] = $value;
               }
            }
            $query .= "UPDUSER_CD        = 	:UPDUSER_CD        , ";
            $query .= "UPDDATETIME       = 	:UPDDATETIME         ";
            $query .= " where organization_id = :organization_id";

            $param[":UPDUSER_CD"]	=	$_SESSION["USER_ID"];
            $param[":UPDDATETIME"]	=	"now()";
            $param[":organization_id"]	=	$_POST["organization_id"];
            //print_r($query);
            //print_r($param); 
            $result = $DBA->executeSQL_no_searchpath($query, $param);                    
//print_r($result);  
            $Log->trace("END updatedata"); 
        }
        public function deldata($org_id){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START deldata");  
            $query  = "";
            $query .= " update ". $_SESSION["SCHEMA"].".mst0010 ";
            $query .= " set UPDUSER_CD='delete' where organization_id = :org_id";
            $param[":org_id"] = $org_id;
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".mst0010 ";
            $query .= " where organization_id = :org_id";
            $result = $DBA->executeSQL_no_searchpath($query, $param);            
            
        }
        
        
    }
?>