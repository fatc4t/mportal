<?php
    /**
     * @file      商品部門情報
     * @author    川橋
     * @date      2019/01/15
     * @version   1.00
     * @note      商品部門情報テーブルの管理を行う
     */

    // BaseProducts.phpを読み込む
    require './Model/BaseProduct.php';

    /**
     * 商品部門情報クラス
     * @note   商品部門情報テーブルの管理を行う。
     */
    class Mst1201 extends BaseProduct
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
         * 商品部門情報データ取得
         * @param    $sect_cd
         * @return   SQLの実行結果
         */
//        public function getMst1201Data( $sect_cd )
        public function getMst1201Data( $organization_id, $sect_cd )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst1201Data");
            $sql  = " SELECT ";
            $sql .= "     INSDATETIME";
            $sql .= "    ,UPDDATETIME";
            $sql .= "    ,ORGANIZATION_ID";
            $sql .= "    ,SECT_CD";
            $sql .= "    ,SECT_NM";
            $sql .= "    ,SECT_KN";
            $sql .= "    ,PROD_CD";
            $sql .= "    ,TYPE_CD";
            $sql .= "    ,TO_CHAR(SECT_PROFIT,      'FM990.00') AS SECT_PROFIT";
            $sql .= "    ,TAX_TYPE";
            $sql .= "    ,SECT_TAX";
            $sql .= "    ,TO_CHAR(SECT_TAX,         'FM990.00') AS SECT_TAX";
            $sql .= "    ,POINT_KBN";
            $sql .= "    ,TO_CHAR(SECT_PRATE,       'FM990.0') AS SECT_PRATE";
            $sql .= "    ,TO_CHAR(SECT_DISC_RATE,   'FM990.00') AS SECT_DISC_RATE";
            $sql .= "    ,TO_CHAR(CUST_DISC_RATE,   'FM990.00') AS CUST_DISC_RATE";
            $sql .= "    ,EMPL_KBN";
            $sql .= "    ,DISC_NOT_KBN";
            $sql .= "    ,TODAY_SALE_PRT";
            $sql .= "  from mst1201";
            $sql .= "  WHERE ORGANIZATION_ID = :organization_id  ";
            $sql .= "  AND   SECT_CD = :sect_cd  ";

//            $searchArray = array( ':sect_cd' => $sect_cd );
            $searchArray = array(
                ':organization_id'  => $organization_id,
                ':sect_cd'          => $sect_cd );

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
         * 商品部門情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        public function searchMst1201Data()
        {
            global $DBA, $Log; // searchMst1201Data変数宣言
            $Log->trace("START searchMst1201Data");

//            $sql = ' SELECT sect_cd,sect_nm,sect_kn from mst1201 where 1 = :sect_cd order by sect_cd desc';
            $sql = ' SELECT organization_id, sect_cd,sect_nm,sect_kn from mst1201 where 1 = :sect_cd order by sect_cd desc';
            $searchArray = array(':sect_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1201DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END searchMst1201Data");
                return $mst1201DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst1201DataList, $data);
            }

            $Log->trace("END searchMst1201Data");

            return $mst1201DataList;
        }

        /**
         * 商品部門情報データ取得
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
            public function getMst1201List()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst1201List");

//            $sql = " SELECT string_agg(sect_cd,',') as list from mst1201 where 1 = :sect_cd ";            
            $sql = " SELECT string_agg(organization_id||':'||sect_cd,',') as list from mst1201 where 1 = :sect_cd ";            
            $searchArray = array(':sect_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1201DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst1201List");
                return $mst1201DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst1201DataList = $data;
            }

            $Log->trace("END getMst1201List");

            return $mst1201DataList;
        }   
        
        /**
         * 商品部門マスタ新規データ登録
         * @param
         * @return   SQLの実行結果
         */
        public function insertdata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START insertdata");

            // トランザクション開始
          //  $DBA->beginTransaction();
////ADDSTR 2020/08/26 kanderu
//           //select org id 
            $query   = "";
            $query  .= "select  ";
            $query  .= "organization_id  ";
            $query  .= "from ". $_SESSION["SCHEMA"] .". m_organization_detail ";
            $query  .= "where organization_id <> 1  ";
            $query  .= "order by organization_id  ";
            print_r($query);
           //  $result=  $DBA->sqlExec($query);
            $result1 = $DBA->executeSQL_no_searchpath($query);

            // 一覧表を格納する空の配列宣言
            $dataList1 = array();
             
            // 取得したデータ群を配列に格納
            while ( $data1 = $result1->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $dataList1, $data1);
            }
             //loop
            if(count($dataList1) != 0){
                foreach ($dataList1 as $orglist1){
////ADDEND kanderu   
            // 操作対象テーブルが1個のためトランザクションは使用しない
            $query  = "";
            $query .= "insert into  ". $_SESSION["SCHEMA"].".mst1201 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,SECT_CD           ";
            $query .= "    ,SECT_NM           ";
            $query .= "    ,SECT_KN           ";
            $query .= "    ,PROD_CD           ";
            $query .= "    ,TYPE_CD           ";
            $query .= "    ,SECT_PROFIT       ";
            $query .= "    ,TAX_TYPE          ";
            $query .= "    ,SECT_TAX          ";
            $query .= "    ,POINT_KBN         ";
            $query .= "    ,SECT_PRATE        ";
            $query .= "    ,SECT_DISC_RATE    ";
            $query .= "    ,CUST_DISC_RATE    ";
            $query .= "    ,EMPL_KBN          ";
            $query .= "    ,DISC_NOT_KBN      ";
            $query .= "    ,TODAY_SALE_PRT    ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:SECT_CD          ";
            $query .= "    ,:SECT_NM          ";
            $query .= "    ,:SECT_KN          ";
            $query .= "    ,:PROD_CD          ";
            $query .= "    ,:TYPE_CD          ";
            $query .= "    ,:SECT_PROFIT      ";
            $query .= "    ,:TAX_TYPE         ";
            $query .= "    ,:SECT_TAX         ";
            $query .= "    ,:POINT_KBN        ";
            $query .= "    ,:SECT_PRATE       ";
            $query .= "    ,:SECT_DISC_RATE   ";
            $query .= "    ,:CUST_DISC_RATE   ";
            $query .= "    ,:EMPL_KBN         ";
            $query .= "    ,:DISC_NOT_KBN     ";
            $query .= "    ,:TODAY_SALE_PRT   ";
            $query .= "  )                     ";
            // 部門打用商品コード
            $strPROD_CD = substr($_POST["PROD_CD"],0,13);
            if (!preg_match('/^[0-9]+$/', $strPROD_CD)) {
                $strPROD_CD = '';
            }
            $param[":INSUSER_CD"]       = $_SESSION["LOGIN"];
            $param[":INSDATETIME"]      = "now()";
            $param[":UPDUSER_CD"]       = $_SESSION["LOGIN"];
            $param[":UPDDATETIME"]      = "now()";
            $param[":DISABLED"]         = '0';
            $param[":LAN_KBN"]          = '0';
            $param[":CONNECT_KBN"]      = '0';
            $param[":ORGANIZATION_ID"]  = $orglist1["organization_id"];
            $param[":SECT_CD"]          = $_POST["SECT_CD"];
            $param[":SECT_NM"]          = $_POST["SECT_NM"];
            $param[":SECT_KN"]          = $_POST["SECT_KN"];
            //$param[":PROD_CD"]          = $_POST["PROD_CD"];
            $param[":PROD_CD"]          = $strPROD_CD;
            $param[":TYPE_CD"]          = $_POST["TYPE_CD"];
            $param[":SECT_PROFIT"]      = $_POST["SECT_PROFIT"];
            $param[":TAX_TYPE"]         = $_POST["TAX_TYPE"];
            $param[":SECT_TAX"]         = $_POST["SECT_TAX"];
            $param[":POINT_KBN"]        = $_POST["POINT_KBN"];
            $param[":SECT_PRATE"]       = $_POST["SECT_PRATE"];
            $param[":SECT_DISC_RATE"]   = $_POST["SECT_DISC_RATE"];
            $param[":CUST_DISC_RATE"]   = $_POST["CUST_DISC_RATE"];
            $param[":EMPL_KBN"]         = $_POST["EMPL_KBN"];
            $param[":DISC_NOT_KBN"]     = $_POST["DISC_NOT_KBN"];
            $param[":TODAY_SALE_PRT"]   = $_POST["TODAY_SALE_PRT"];
            //print_r($param);
            //echo $query;
            // SQLの実行
            $result1 = $DBA->executeSQL_no_searchpath($query, $param);
            //console.log($result);
        }
     }
   }
        /**
         * 商品部門マスタ既存データ更新
         * @param
         * @return   SQLの実行結果
         */
        public function updatedata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START insertdata");        
            $strsect_cd = $_POST['SECT_CD'];
            $query  = "";
            $query  = "update  ". $_SESSION["SCHEMA"].".mst1201 set  ";
            $query .= "UPDUSER_CD               = :UPDUSER_CD        , ";
            $query .= "UPDDATETIME              = :UPDDATETIME       , ";
            $query .= "DISABLED                 = :DISABLED          , ";
            $query .= "LAN_KBN                  = :LAN_KBN           , ";
            $query .= "CONNECT_KBN              = :CONNECT_KBN       , ";
            $query .= "SECT_NM                  = :SECT_NM           , ";
            $query .= "SECT_KN                  = :SECT_KN           , ";
            $query .= "PROD_CD                  = :PROD_CD           , ";
            $query .= "TYPE_CD                  = :TYPE_CD           , ";
            $query .= "SECT_PROFIT              = :SECT_PROFIT       , ";
            $query .= "TAX_TYPE                 = :TAX_TYPE          , ";
            $query .= "SECT_TAX                 = :SECT_TAX          , ";
            $query .= "POINT_KBN                = :POINT_KBN         , ";
            $query .= "SECT_PRATE               = :SECT_PRATE        , ";
            $query .= "SECT_DISC_RATE           = :SECT_DISC_RATE    , ";
            $query .= "CUST_DISC_RATE           = :CUST_DISC_RATE    , ";
            $query .= "EMPL_KBN                 = :EMPL_KBN          , ";
            $query .= "DISC_NOT_KBN             = :DISC_NOT_KBN      , ";
            $query .= "TODAY_SALE_PRT           = :TODAY_SALE_PRT      ";
          //  $query .= " where ORGANIZATION_ID   = :ORGANIZATION_ID     ";
            $query .= " where   SECT_CD           = :SECT_CD             ";

            // 部門打用商品コード
            $strPROD_CD = substr($_POST["PROD_CD"],0,13);
            if (!preg_match('/^[0-9]+$/', $strPROD_CD)) {
                $strPROD_CD = '';
            }
            
            $param[":UPDUSER_CD"]               = $_SESSION["LOGIN"];
            $param[":UPDDATETIME"]              = "now()";
            $param[":DISABLED"]                 = '0';
            $param[":LAN_KBN"]                  = '0';
            $param[":CONNECT_KBN"]              = '0';
           // $param[":ORGANIZATION_ID"]          = $_POST["ORGANIZATION_ID"];
            $param[":SECT_CD"]                  = $_POST["SECT_CD"];
            $param[":SECT_NM"]                  = $_POST["SECT_NM"];
            $param[":SECT_KN"]                  = $_POST["SECT_KN"];
            //$param[":PROD_CD"]                  = $_POST["PROD_CD"];
            $param[":PROD_CD"]                  = $strPROD_CD;
            $param[":TYPE_CD"]                  = $_POST["TYPE_CD"];
            $param[":SECT_PROFIT"]              = $_POST["SECT_PROFIT"];
            $param[":TAX_TYPE"]                 = $_POST["TAX_TYPE"];
            $param[":SECT_TAX"]                 = $_POST["SECT_TAX"];
            $param[":POINT_KBN"]                = $_POST["POINT_KBN"];
            $param[":SECT_PRATE"]               = $_POST["SECT_PRATE"];
            $param[":SECT_DISC_RATE"]           = $_POST["SECT_DISC_RATE"];
            $param[":CUST_DISC_RATE"]           = $_POST["CUST_DISC_RATE"];
            $param[":EMPL_KBN"]                 = $_POST["EMPL_KBN"];
            $param[":DISC_NOT_KBN"]             = $_POST["DISC_NOT_KBN"];
            $param[":TODAY_SALE_PRT"]           = $_POST["TODAY_SALE_PRT"];

           // print_r($_POST);
            //echo $query;
            //print_r($param);
            // SQLの実行
            $result = $DBA->executeSQL_no_searchpath($query, $param); 
            //print_r($result);
        }
        
        /**
         * 商品部門マスタ既存データ削除
         * @param    $organization_id：組織ID
         * @param    $sect_cd：部門コード
         * @return   SQLの実行結果
         */
        public function deldata($organization_id, $sect_cd){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START deldata");
            
//change update user's name to let trigger add row to mst9999 
            $query1  = "";
            $query1 .= " update ". $_SESSION["SCHEMA"].".mst1201 set UPDUSER_CD='delete' ";
            //$query1 .= " where organization_id = :organization_id";
            $query .= " where   sect_cd = :sect_cd";
// delete data from changed
            $query2  = "";
            $query2 .= " delete from ". $_SESSION["SCHEMA"].".mst1201_changed ";
            //$query2 .= " where organization_id = :organization_id";
            $query .= " where   sect_cd = :sect_cd";  
            $query  = "";
            $query .= " delete from  ". $_SESSION["SCHEMA"].".mst1201 ";
           // $query .= " where organization_id = :organization_id";
            $query .= " where   sect_cd = :sect_cd";

           // $param[":organization_id"]  = $organization_id;
            $param[":sect_cd"]          = $sect_cd;
            $result = $DBA->executeSQL_no_searchpath($query1, $param);
            $result = $DBA->executeSQL_no_searchpath($query2, $param);            
            $result = $DBA->executeSQL_no_searchpath($query, $param);

            $Log->trace("START deldata");
        }

        /**
         * システムマスタデータを取得します
         * @param    $organization_id：組織ID
         * @return   SQLの実行結果
         */
        public function getMst0010Data( $organization_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst0010Data");

            // 一覧表を格納する空の配列宣言
            $mst0010DataList = array();

            if ($organization_id === "") {
                $Log->trace("END getMst0010Data");
                return $mst0010DataList;                
            }

            $sql = ' select *  from mst0010';
            $sql .= "  WHERE disabled = :disabled  ";
            $sql .= "  AND   organization_id = :organization_id  ";
            $sql .= "  AND   sys_cd = :sys_cd limit 1 ";

            $searchArray = array(
                ':disabled'         => '0',
                ':organization_id'  => $organization_id,
                ':sys_cd'           => '1'
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst0010Data");
                return $mst0010DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //array_push( $mst0010DataList, $data);
                $mst0010DataList = $data;
            }

            $Log->trace("END getMst00105Data");

            return $mst0010DataList;
        } 

        /**
         * 商品部門分類データを取得します
         * @param    $organization_id：組織ID
         * @return   SQLの実行結果
         */
        public function getMst1205Data( $organization_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst1205Data");

            // 一覧表を格納する空の配列宣言
            $mst1205DataList = array();

            if ($organization_id === "") {
                $Log->trace("END getMst1205Data");
                return $mst1205DataList;                
            }

            $sql = ' select organization_id, type_cd, type_nm  from mst1205';
            $sql .= "  WHERE disabled = :disabled  ";
            $sql .= "  AND   organization_id = :organization_id  ";
            $sql .= "  ORDER BY type_cd  ";

            $searchArray = array(
                ':disabled'         => '0',
                ':organization_id'  => $organization_id
            );
print_r($sql);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst1205Data");
                return $mst1205DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst1205DataList, $data);
            }

            $Log->trace("END getMst1205Data");

            return $mst1205DataList;
        } 

        /**
         * 商品データを取得します
         * @param    $organization_id：組織ID
         * @return   SQLの実行結果
         */
        public function getMst0201Data( $organization_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst0201Data");

            // 一覧表を格納する空の配列宣言
            $mst0201DataList = array();

            if ($organization_id === "") {
                $Log->trace("END getMst0201Data");
                return $mst0201DataList;                
            }

            $sql = ' select organization_id, prod_cd, prod_nm  from mst0201';
            $sql .= "  WHERE disabled = :disabled  ";
            $sql .= "  AND   organization_id = :organization_id  ";
            $sql .= "  ORDER BY prod_cd  ";
           // $sql .= "  limit 20  ";     // for DEBUG

            $searchArray = array(
                ':disabled'         => '0',
                ':organization_id'  => $organization_id
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst0201Data");
                return $mst0201DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst0201DataList, $data);
            }

            $Log->trace("END getMst0201Data");

            return $mst0201DataList;
        } 
        
    }
?>