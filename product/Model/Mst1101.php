<?php
    /**
     * @file      仕入先情報
     * @author    川橋
     * @date      2019/01/17
     * @version   1.00
     * @note      仕入先情報テーブルの管理を行う
     */

    // BaseProducts.phpを読み込む
    require './Model/BaseProduct.php';

    /**
     * 商品部門情報クラス
     * @note   商品部門情報テーブルの管理を行う。
     */
    class mst1101 extends BaseProduct
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
         * @param    $supp_cd
         * @return   SQLの実行結果
         */
//ADDSTR 2020/08/28 kanderu
       //count function
//        public function pageMaxNo($postArray, $searchArray)
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START pageMaxNo");
//        $where    = "";    
//        $where    .= " where   1 = :val  ";
//        $sql      .= " SELECT  count(*) as rownos ";
//        $sql      .= "from mst1101 ";
//      //  $sql  .= " WHERE 1 = :val ";
//        $sql      .= " $where ";
//        $searchArray[':val'] = 1 ;
//        // SQLの実行
//        $Datas = [];
//        // SQLの実行
//        $result = $DBA->executeSQL($sql,$searchArray);
//
//        // 一覧表を格納する空の配列宣言
//
//        //print_r($result);
//        // データ取得ができなかった場合、空の配列を返す
//        if( $result === false )
//        {
//
//            $Log->trace("END pageMaxNo");
//            return $Datas;
//        }
//
//        // 取得したデータ群を配列に格納
//        while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//        {
//                //print_r($data);
//                $Datas[] = $data;
//        }
//print_r($sql);
//
//        $Log->trace("END pageMaxNo");
//
//        return $Datas;
//    }

//ADDEND 2020/08/28 kanderu        
        
        
        
        
        /**
         * 商品部門情報データ取得
         * @param    $supp_cd
         * @return   SQLの実行結果
         */
//        public function getmst1101Data( $supp_cd )
        public function getmst1101Data( $organization_id, $supp_cd )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getmst1101Data");
//print_r('$_POST');
//            $sql = ' SELECT * from mst1101';
//            $sql .= '  WHERE supp_cd = :supp_cd  ';
//            $sql .= '  WHERE organization_id = :organization_id  ';
//            $sql .= '  AND   supp_cd = :supp_cd  ';

            $sql  = " SELECT ";
            $sql .= "     INSDATETIME";
            $sql .= "    ,UPDDATETIME";
            $sql .= "    ,ORGANIZATION_ID";
            $sql .= "    ,SUPP_CD";
            $sql .= "    ,SUPP_KBN";
            $sql .= "    ,SUPP_NM";
            $sql .= "    ,SUPP_KN";
            $sql .= "    ,ZIP";
            $sql .= "    ,ADDR1";
            $sql .= "    ,ADDR2";
            $sql .= "    ,ADDR3";
            $sql .= "    ,TEL";
            $sql .= "    ,FAX";
            $sql .= "    ,PAY_CLOSE_DAY1";
            $sql .= "    ,ARR_NEED_DAY";
            $sql .= "    ,ORD_FORM_KBN";
            $sql .= "    ,SUPP_DETA_KBN";
            $sql .= "    ,KICS_SUPP_CD";
            $sql .= "    ,NOWORDER_NO";
            $sql .= "    ,ORDER_LINE";
            $sql .= "    ,BIKO";
            $sql .= "    ,TO_CHAR(ORDER_UNDER_PRICE, 'FM999,999,990') AS ORDER_UNDER_PRICE";
            $sql .= "    ,FAX_DETAIL_CNT";
            $sql .= "    ,FAX_DENNO";
            $sql .= "    ,TAX_CALC_KBN";
            $sql .= "    ,TAX_FRAC_KBN";
            $sql .= "    ,ORDER_PROD_KBN";
            $sql .= "    ,ORDER_SP_CD";
            $sql .= "  from mst1101";
            $sql .= "  WHERE ORGANIZATION_ID = :organization_id  ";
            $sql .= "  AND   SUPP_CD = :supp_cd  ";
            
//            $searchArray = array( ':supp_cd' => $supp_cd );
            $searchArray = array(
                ':organization_id'  => $organization_id,
                ':supp_cd'          => $supp_cd );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1101DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getmst1101Data");
                return $mst1101DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst1101DataList = $data;
            }

            $Log->trace("END getmst1101Data");

            return $mst1101DataList;
        }

        /**
         * 仕入先情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        public function searchmst1101Data()
        {
            global $DBA, $Log; // searchmst1101Data変数宣言
            $Log->trace("START searchmst1101Data");

//            $sql = ' SELECT supp_cd,supp_nm,supp_kn from mst1101 where 1 = :supp_cd order by supp_cd desc';
            $sql = ' SELECT organization_id, supp_cd,supp_nm,supp_kn from mst1101 where 1 = :supp_cd order by supp_cd desc';
            $searchArray = array(':supp_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1101DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END searchmst1101Data");
                return $mst1101DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst1101DataList, $data);
            }
         //   echo[$result];
//print_r($sql);
            $Log->trace("END searchmst1101Data");

            return $mst1101DataList;
        }

        /**
         * 仕入先情報データ取得
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
            public function getmst1101List()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getmst1101List");

//            $sql = " SELECT string_agg(supp_cd,',') as list from mst1101 where 1 = :supp_cd ";            
            $sql = " SELECT string_agg(organization_id||':'||supp_cd,',') as list from mst1101 where 1 = :supp_cd ";            
            $searchArray = array(':supp_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1101DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getmst1101List");
                return $mst1101DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst1101DataList = $data;
            }

            $Log->trace("END getmst1101List");

            return $mst1101DataList;
        }   
        
        /**
         * 仕入先マスタ新規データ登録
         * @param
         * @return   SQLの実行結果
         */
        public function insertdata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START insertdata");

            // トランザクション開始
            $DBA->beginTransaction();
//ADDSTR 2020/08/26 kanderu
           //select org id 
            $query   ="";
            $query  .= "select  ";
            $query  .= "organization_id  ";
            $query  .= "from ". $_SESSION["SCHEMA"] .". m_organization_detail ";
            $query  .= "where organization_id <> 1  ";
            $query  .= "order by organization_id  ";
            print_r($query);
           //  $result=  $DBA->sqlExec($query);
             $result1 = $DBA->executeSQL_no_searchpath($query);

            // 一覧表を格納する空の配列宣言
            $dataList = array();
             
            // 取得したデータ群を配列に格納
            while ( $data1 = $result1->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $dataList, $data1);
            }
             //loop
            if(count($dataList) != 0){
                foreach ($dataList as $orglist){
//ADDEND kanderu   
            
            $query  = "";
            $query .= "insert into ". $_SESSION["SCHEMA"].".mst1101 (";
            $query .= "     INSUSER_CD         ";
            $query .= "    ,INSDATETIME        ";
            $query .= "    ,UPDUSER_CD         ";
            $query .= "    ,UPDDATETIME        ";
            $query .= "    ,DISABLED           ";
            $query .= "    ,LAN_KBN            ";
            $query .= "    ,CONNECT_KBN        ";
            $query .= "    ,ORGANIZATION_ID    ";
            $query .= "    ,SUPP_CD            ";
            $query .= "    ,SUPP_KBN           ";
            $query .= "    ,SUPP_NM            ";
            $query .= "    ,SUPP_KN            ";
            $query .= "    ,ZIP                ";
            $query .= "    ,ADDR1              ";
            $query .= "    ,ADDR2              ";
            $query .= "    ,ADDR3              ";
            $query .= "    ,TEL                ";
            $query .= "    ,FAX                ";
            $query .= "    ,PAY_CLOSE_DAY1     ";
            $query .= "    ,ARR_NEED_DAY       ";
            $query .= "    ,ORD_FORM_KBN       ";
            $query .= "    ,SUPP_DETA_KBN      ";
            $query .= "    ,KICS_SUPP_CD       ";
            $query .= "    ,NOWORDER_NO        ";
            $query .= "    ,ORDER_LINE         ";
            $query .= "    ,BIKO               ";
            $query .= "    ,ORDER_UNDER_PRICE  ";
            $query .= "    ,FAX_DETAIL_CNT     ";
            $query .= "    ,FAX_DENNO          ";
            $query .= "    ,TAX_CALC_KBN       ";
            $query .= "    ,TAX_FRAC_KBN       ";
            $query .= "    ,CSV_DENNO          ";
            $query .= "    ,ORDER_PROD_KBN     ";
            $query .= "    ,ORDER_SP_CD        ";
            $query .= "  ) values (            ";
            $query .= "     :INSUSER_CD        ";
            $query .= "    ,:INSDATETIME       ";
            $query .= "    ,:UPDUSER_CD        ";
            $query .= "    ,:UPDDATETIME       ";
            $query .= "    ,:DISABLED          ";
            $query .= "    ,:LAN_KBN           ";
            $query .= "    ,:CONNECT_KBN       ";
            $query .= "    ,:ORGANIZATION_ID   ";
            $query .= "    ,:SUPP_CD           ";
            $query .= "    ,:SUPP_KBN          ";
            $query .= "    ,:SUPP_NM           ";
            $query .= "    ,:SUPP_KN           ";
            $query .= "    ,:ZIP               ";
            $query .= "    ,:ADDR1             ";
            $query .= "    ,:ADDR2             ";
            $query .= "    ,:ADDR3             ";
            $query .= "    ,:TEL               ";
            $query .= "    ,:FAX               ";
            $query .= "    ,:PAY_CLOSE_DAY1    ";
            $query .= "    ,:ARR_NEED_DAY      ";
            $query .= "    ,:ORD_FORM_KBN      ";
            $query .= "    ,:SUPP_DETA_KBN     ";
            $query .= "    ,:KICS_SUPP_CD      ";
            $query .= "    ,:NOWORDER_NO       ";
            $query .= "    ,:ORDER_LINE        ";
            $query .= "    ,:BIKO              ";
            $query .= "    ,:ORDER_UNDER_PRICE ";
            $query .= "    ,:FAX_DETAIL_CNT    ";
            $query .= "    ,:FAX_DENNO         ";
            $query .= "    ,:TAX_CALC_KBN      ";
            $query .= "    ,:TAX_FRAC_KBN      ";
            $query .= "    ,:CSV_DENNO         ";
            $query .= "    ,:ORDER_PROD_KBN    ";
            $query .= "    ,:ORDER_SP_CD       ";
            $query .= "  )                     ";

            $param[":INSUSER_CD"]           = $_SESSION["LOGIN"];
            $param[":INSDATETIME"]          = "now()";
            $param[":UPDUSER_CD"]           = $_SESSION["LOGIN"];
            $param[":UPDDATETIME"]          = "now()";
            $param[":DISABLED"]             = '0';
            $param[":LAN_KBN"]              = '0';
            $param[":CONNECT_KBN"]          = '0';
            $param[":ORGANIZATION_ID"]      =$orglist["organization_id"];
            $param[":SUPP_CD"]              = $_POST["SUPP_CD"];
            $param[":SUPP_KBN"]             = $_POST["SUPP_KBN"];
            $param[":SUPP_NM"]              = $_POST["SUPP_NM"];
            $param[":SUPP_KN"]              = $_POST["SUPP_KN"];
            $param[":ZIP"]                  = $_POST["ZIP"];
            $param[":ADDR1"]                = $_POST["ADDR1"];
            $param[":ADDR2"]                = $_POST["ADDR2"];
            $param[":ADDR3"]                = $_POST["ADDR3"];
            $param[":TEL"]                  = $_POST["TEL"];
            $param[":FAX"]                  = $_POST["FAX"];
            $param[":PAY_CLOSE_DAY1"]       = $_POST["PAY_CLOSE_DAY"];
            $param[":ARR_NEED_DAY"]         = $_POST["ARR_NEED_DAY"];
            $param[":ORD_FORM_KBN"]         = $_POST["ORD_FORM_KBN"];
            $param[":SUPP_DETA_KBN"]        = $_POST["SUPP_DETA_KBN"];
            $param[":KICS_SUPP_CD"]         = $_POST["KICS_SUPP_CD"];
            $param[":NOWORDER_NO"]          = $_POST["NOWORDER_NO"];
            $param[":ORDER_LINE"]           = $_POST["ORDER_LINE"];
            $param[":BIKO"]                 = $_POST["BIKO"];
            $param[":ORDER_UNDER_PRICE"]    = $_POST["UNDER_PRICE"];
            $param[":FAX_DETAIL_CNT"]       = $_POST["DETAIL_CNT"];
            $param[":FAX_DENNO"]            = $_POST["FAX_DENNO"];
            $param[":TAX_CALC_KBN"]         = $_POST["TAX_CALC_KBN"];
            $param[":TAX_FRAC_KBN"]         = $_POST["TAX_FRAC_KBN"];
            $param[":CSV_DENNO"]            = $_POST["CSV_DENNO"];
            $param[":ORDER_PROD_KBN"]       = $_POST["ORDER_PROD_KBN"];
            $param[":ORDER_SP_CD"]          = $_POST["ORDER_SP_CD"];
            
            //print_r($param);
            //echo $query;
            // SQLの実行
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            //console.log($result);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                return;
            }
            
            // 曜日別仕入先発注マスタ登録
            if ($this->insMst6301data() === false){
                // ロールバック
                $DBA->rollBack();
                return;
            }

            // 仕入先別実績データ登録
            if ($this->updJsk5200data() === false){
                // ロールバック
                $DBA->rollBack();
            }
            else {
                // コミット
                $DBA->commit();
            }
        }
     }
  }
        /**
         * 仕入先マスタ既存データ更新
         * @param
         * @return   SQLの実行結果
         */
        public function updatedata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START insertdata");        

            // トランザクション開始
            $DBA->beginTransaction();

            $strsupp_cd = $_POST['SUPP_CD'];
            $query  = "";
            $query  = "update ". $_SESSION["SCHEMA"].".mst1101 set  ";
            $query .= "UPDUSER_CD               = :UPDUSER_CD        , ";
            $query .= "UPDDATETIME              = :UPDDATETIME       , ";
            $query .= "DISABLED                 = :DISABLED          , ";
            $query .= "LAN_KBN                  = :LAN_KBN           , ";
            $query .= "CONNECT_KBN              = :CONNECT_KBN       , ";
            $query .= "SUPP_KBN                 = :SUPP_KBN          , ";
            $query .= "SUPP_NM                  = :SUPP_NM           , ";
            $query .= "SUPP_KN                  = :SUPP_KN           , ";
            $query .= "ZIP                      = :ZIP               , ";
            $query .= "ADDR1                    = :ADDR1             , ";
            $query .= "ADDR2                    = :ADDR2             , ";
            $query .= "ADDR3                    = :ADDR3             , ";
            $query .= "TEL                      = :TEL               , ";
            $query .= "FAX                      = :FAX               , ";
            $query .= "PAY_CLOSE_DAY1           = :PAY_CLOSE_DAY1    , ";
            $query .= "ARR_NEED_DAY             = :ARR_NEED_DAY      , ";
            $query .= "ORD_FORM_KBN             = :ORD_FORM_KBN      , ";
            $query .= "SUPP_DETA_KBN            = :SUPP_DETA_KBN     , ";
            $query .= "KICS_SUPP_CD             = :KICS_SUPP_CD      , ";
            $query .= "NOWORDER_NO              = :NOWORDER_NO       , ";
            $query .= "ORDER_LINE               = :ORDER_LINE        , ";
            $query .= "BIKO                     = :BIKO              , ";
            $query .= "ORDER_UNDER_PRICE        = :ORDER_UNDER_PRICE , ";
            $query .= "FAX_DETAIL_CNT           = :FAX_DETAIL_CNT    , ";
            $query .= "FAX_DENNO                = :FAX_DENNO         , ";
            $query .= "TAX_CALC_KBN             = :TAX_CALC_KBN      , ";
            $query .= "TAX_FRAC_KBN             = :TAX_FRAC_KBN      , ";
            $query .= "CSV_DENNO                = :CSV_DENNO         , ";
            $query .= "ORDER_PROD_KBN           = :ORDER_PROD_KBN    , ";
            $query .= "ORDER_SP_CD              = :ORDER_SP_CD         ";
           // $query .= " where ORGANIZATION_ID   = :ORGANIZATION_ID     ";
            $query .= " where   SUPP_CD           = :SUPP_CD             ";

            $param[":UPDUSER_CD"]               = $_SESSION["LOGIN"];
            $param[":UPDDATETIME"]              = "now()";
            $param[":DISABLED"]                 = '0';
            $param[":LAN_KBN"]                  = '0';
            $param[":CONNECT_KBN"]              = '0';
          //  $param[":ORGANIZATION_ID"]          = $_POST["ORGANIZATION_ID"];
            $param[":SUPP_CD"]                  = $_POST["SUPP_CD"];
            $param[":SUPP_KBN"]                 = $_POST["SUPP_KBN"];
            $param[":SUPP_NM"]                  = $_POST["SUPP_NM"];
            $param[":SUPP_KN"]                  = $_POST["SUPP_KN"];
            $param[":ZIP"]                      = $_POST["ZIP"];
            $param[":ADDR1"]                    = $_POST["ADDR1"];
            $param[":ADDR2"]                    = $_POST["ADDR2"];
            $param[":ADDR3"]                    = $_POST["ADDR3"];
            $param[":TEL"]                      = $_POST["TEL"];
            $param[":FAX"]                      = $_POST["FAX"];
            $param[":PAY_CLOSE_DAY1"]           = $_POST["PAY_CLOSE_DAY"];
            $param[":ARR_NEED_DAY"]             = $_POST["ARR_NEED_DAY"];
            $param[":ORD_FORM_KBN"]             = $_POST["ORD_FORM_KBN"];
            $param[":SUPP_DETA_KBN"]            = $_POST["SUPP_DETA_KBN"];
            $param[":KICS_SUPP_CD"]             = $_POST["KICS_SUPP_CD"];
            $param[":NOWORDER_NO"]              = $_POST["NOWORDER_NO"];
            $param[":ORDER_LINE"]               = $_POST["ORDER_LINE"];
            $param[":BIKO"]                     = $_POST["BIKO"];
            $param[":ORDER_UNDER_PRICE"]        = $_POST["UNDER_PRICE"];
            $param[":FAX_DETAIL_CNT"]           = $_POST["DETAIL_CNT"];
            $param[":FAX_DENNO"]                = $_POST["FAX_DENNO"];
            $param[":TAX_CALC_KBN"]             = $_POST["TAX_CALC_KBN"];
            $param[":TAX_FRAC_KBN"]             = $_POST["TAX_FRAC_KBN"];
            $param[":CSV_DENNO"]                = $_POST["CSV_DENNO"];
            $param[":ORDER_PROD_KBN"]           = $_POST["ORDER_PROD_KBN"];
            $param[":ORDER_SP_CD"]              = $_POST["ORDER_SP_CD"];

           //print_r($_POST);
            //echo $query;
            //print_r($param);
            // SQLの実行
            $result = $DBA->executeSQL_no_searchpath($query, $param); 
            //print_r($result);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                return;
            }
            
            // 曜日別仕入先発注マスタ登録
            if ($this->insMst6301data() === false){
                // ロールバック
                $DBA->rollBack();
                return;
            }

            // 仕入先別実績データ登録
            if ($this->updJsk5200data() === false){
                // ロールバック
                $DBA->rollBack();
            }
            else {
                // コミット
                $DBA->commit();
            }
        }
        
        /**
         * 仕入先マスタ既存データ削除
         * @param    $organization_id：組織ID
         * @param    $supp_cd：仕入先コード
         * @return   SQLの実行結果
         */
        public function deldata($organization_id, $supp_cd){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START deldata");

            // トランザクション開始
            $DBA->beginTransaction();
            //change update user's name to let trigger add row to mst9999 
            $query1  = "";
            $query1 .= " update ". $_SESSION["SCHEMA"].".mst1101 set UPDUSER_CD='delete' ";
            //$query1 .= " where organization_id = :organization_id";
            $query1 .= " where   supp_cd = :supp_cd";
            // delete data from changed
            $query2  = "";
            $query2 .= " delete from ". $_SESSION["SCHEMA"].".mst1101_changed ";
          //  $query2 .= " where organization_id = :organization_id";
            $query2 .= " where   supp_cd = :supp_cd";
            
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".mst1101 ";
            //$query .= " where organization_id = :organization_id";
            $query .= " where   supp_cd = :supp_cd";

            $param = array();
            //$param[":organization_id"]  = $organization_id;
            $param[":supp_cd"]          = $supp_cd;
            
            $result = $DBA->executeSQL_no_searchpath($query1, $param);
            $result = $DBA->executeSQL_no_searchpath($query2, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                $Log->trace("END deldata");
                return;
            }
//change update user's name to let trigger add row to mst9999 
            $query1  = "";
            $query1 .= " update ". $_SESSION["SCHEMA"].".mst6301 set UPDUSER_CD='delete' ";
           // $query1 .= " where organization_id = :organization_id";
            $query1 .= " where   supp_cd = :supp_cd";
// delete data from changed
            $query2  = "";
            $query2 .= " delete from ". $_SESSION["SCHEMA"].".mst6301_changed ";
            //$query2 .= " where organization_id = :organization_id";
            $query2 .= " where   supp_cd = :supp_cd";
            
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".mst6301 ";
           // $query .= " where organization_id = :organization_id";
            $query .= " where   supp_cd = :supp_cd";

            $param = array();
           // $param["organization_id"] = $organization_id;
            $param["supp_cd"] = $supp_cd;
            
            $result = $DBA->executeSQL_no_searchpath($query1, $param);
            $result = $DBA->executeSQL_no_searchpath($query2, $param);  
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
            }
            else {
                // コミット
                $DBA->commit();
            }

            $Log->trace("END deldata");
        }
        
        /**
         * 曜日別仕入先発注マスタデータ登録
         * @param
         * @return   SQLの実行結果
         */
        public function insMst6301data(){
            
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START insMst6301data");
//change update user's name to let trigger add row to mst9999 
            $query1  = "";
            $query1 .= " update ". $_SESSION["SCHEMA"].".mst6301 set UPDUSER_CD='delete' ";
            //$query1 .= " where organization_id = :organization_id";
            $query1 .= " where   supp_cd = :supp_cd";
// delete data from changed
            $query2  = "";
            $query2 .= " delete from ". $_SESSION["SCHEMA"].".mst6301_changed ";
            $query2 .= " where organization_id = :organization_id";
            $query2 .= " and   supp_cd = :supp_cd";            
            
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].".mst6301 ";
           // $query .= " where organization_id = :ORGANIZATION_ID";
            $query .= " where   supp_cd = :SUPP_CD";

            $param = array();
            //$param[":ORGANIZATION_ID"]      = $_POST["ORGANIZATION_ID"];
            $param[":SUPP_CD"]              = $_POST["SUPP_CD"];
            
            //print_r($query1);
            //print_r($param);
            $result = $DBA->executeSQL_no_searchpath($query1, $param);
            //print_r($query2);
            //print_r($param);            
            $result = $DBA->executeSQL_no_searchpath($query2, $param);             
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                $Log->trace("END insMst6301data");
                return false;
            }

            for ($intL = 0; $intL < 7; $intL ++){
                // 曜日
                $day_kbn = isset($_POST['DAY_KBN_'.strval($intL + 1)]) ? $_POST['DAY_KBN_'.strval($intL + 1)] : '0';
                if ($day_kbn == '1'){
                    // 必要日数
                    $need_day = intval($_POST['NEED_DAY_'.strval($intL + 1)]);

                    $query  = "";
                    $query .= "insert into ". $_SESSION["SCHEMA"].".mst6301 (   ";
                    $query .= "     INSUSER_CD         ";
                    $query .= "    ,INSDATETIME        ";
                    $query .= "    ,UPDUSER_CD         ";
                    $query .= "    ,UPDDATETIME        ";
                    $query .= "    ,DISABLED           ";
                    $query .= "    ,LAN_KBN            ";
                    $query .= "    ,CONNECT_KBN        ";
                    $query .= "    ,ORGANIZATION_ID    ";
                    $query .= "    ,SUPP_CD            ";
                    $query .= "    ,DAY_KBN            ";
                    $query .= "    ,ARR_NEED_DAY       ";
                    $query .= "  ) values (            ";
                    $query .= "     :INSUSER_CD        ";
                    $query .= "    ,:INSDATETIME       ";
                    $query .= "    ,:UPDUSER_CD        ";
                    $query .= "    ,:UPDDATETIME       ";
                    $query .= "    ,:DISABLED          ";
                    $query .= "    ,:LAN_KBN           ";
                    $query .= "    ,:CONNECT_KBN       ";
                    $query .= "    ,:ORGANIZATION_ID   ";
                    $query .= "    ,:SUPP_CD           ";
                    $query .= "    ,:DAY_KBN           ";
                    $query .= "    ,:ARR_NEED_DAY      ";
                    $query .= "  )                     ";

                    $param = array();
                    $param[":INSUSER_CD"]           = $_SESSION["LOGIN"];
                    $param[":INSDATETIME"]          = "now()";
                    $param[":UPDUSER_CD"]           = $_SESSION["LOGIN"];
                    $param[":UPDDATETIME"]          = "now()";
                    $param[":DISABLED"]             = '0';
                    $param[":LAN_KBN"]              = '0';
                    $param[":CONNECT_KBN"]          = '0';
                    $param[":ORGANIZATION_ID"]      = $_POST["ORGANIZATION_ID"];
                    $param[":SUPP_CD"]              = $_POST["SUPP_CD"];
                    $param[":DAY_KBN"]              = strval($intL);
                    $param[":ARR_NEED_DAY"]         = strval($need_day);

                    //print_r($param);
                    //echo $query;
                    // SQLの実行
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    //console.log($result);
                    if ($result === false){
                        $Log->trace("END insMst6301data");
                        return false;
                    }
                }
            }
            
            $Log->trace("END insMst6301data");
            return true;
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
            $sql .= "  AND   sys_cd = :sys_cd  ";

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
                array_push( $mst0010DataList, $data);
            }

            $Log->trace("END getMst00105Data");

            return $mst0010DataList;
        }
        
        /**
         * 曜日別仕入先発注マスタデータを取得します
         * @param    $organization_id：組織ID
         * @param    $supp_cd：仕入先コード
         * @return   SQLの実行結果
         */
        public function getMst6301Data( $organization_id, $supp_cd )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst6301Data");

            // 一覧表を格納する空の配列宣言
            $mst6301DataList = array();

            if ($organization_id === "" || $supp_cd === "") {
                $Log->trace("END getMst6301Data");
                return $mst6301DataList;                
            }

            $sql = ' select DAY_KBN, ARR_NEED_DAY, UPDDATETIME from mst6301';
            $sql .= "  WHERE disabled = :disabled  ";
            $sql .= "  AND   organization_id = :organization_id  ";
            $sql .= "  AND   supp_cd = :supp_cd  ";

            $searchArray = array(
                ':disabled'         => '0',
                ':organization_id'  => $organization_id,
                ':supp_cd'          => $supp_cd
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst6301Data");
                return $mst6301DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst6301DataList, $data);
            }

            $Log->trace("END getMst6301Data");

            return $mst6301DataList;
        }
        
        /**
         * 仕入先月別実績データを取得します
         * @param    $organization_id：組織ID
         * @param    $supp_cd：仕入先コード
         * @return   SQLの実行結果
         */
        public function getJsk5200Data( $organization_id, $supp_cd )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getJsk5200Data");

            // 一覧表を格納する空の配列宣言
            $jsk5200DataList = array();

            if ($organization_id === "" || $supp_cd === "") {
                $Log->trace("END getJsk5200Data");
                return $jsk5200DataList;                
            }

            $sql  = " SELECT ";
            $sql .= "     organization_id";
            $sql .= "    ,supp_cd";
            $sql .= "    ,trnym";
            $sql .= "    ,TO_CHAR(supp_budget, 'FM999,999,990') AS supp_budget";
            $sql .= "    ,TO_CHAR(sale_budget, 'FM999,999,990') AS sale_budget";
            $sql .= "  FROM jsk5200";
            $sql .= "  WHERE disabled = :disabled  ";
            $sql .= "  AND   organization_id = :organization_id  ";
            $sql .= "  AND   supp_cd = :supp_cd  ";

            $searchArray = array(
                ':disabled'         => '0',
                ':organization_id'  => $organization_id,
                ':supp_cd'          => $supp_cd
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getJsk5200Data");
                return $jsk5200DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $jsk5200DataList, $data);
            }

            $Log->trace("END getJsk5200Data");

            return $jsk5200DataList;
        }
        
        
        /**
         * 仕入先別実績データ登録
         * @param
         * @return   SQLの実行結果
         */
        public function updJsk5200data(){
            
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START updJsk5200data");

            $strTrnYM = str_replace('/', '', $_POST["TRNYM"]);
            
            // 存在チェック
            $sql  = "";
            $sql .= " SELECT * FROM jsk5200 ";
            $sql .= " WHERE disabled = :disabled  ";
            $sql .= " AND   organization_id = :organization_id  ";
            $sql .= " AND   supp_cd = :supp_cd  ";
            $sql .= " AND   trnym = :trnym";

            $searchArray = array(
                ':disabled'         => '0',
                ':organization_id'  => $_POST["ORGANIZATION_ID"],
                ':supp_cd'          => $_POST["SUPP_CD"],
                ':trnym'            => $strTrnYM
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END updJsk5200data");
                return false;
            }

            // 一覧表を格納する空の配列宣言
            $jsk5200DataList = array();

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $jsk5200DataList = $data;
            }
            
            if (Count($jsk5200DataList) === 0){
                $query  = "";
                $query .= "insert into ". $_SESSION["SCHEMA"].".jsk5200 (   ";
                $query .= "     INSUSER_CD         ";
                $query .= "    ,INSDATETIME        ";
                $query .= "    ,UPDUSER_CD         ";
                $query .= "    ,UPDDATETIME        ";
                $query .= "    ,DISABLED           ";
                $query .= "    ,LAN_KBN            ";
                $query .= "    ,CONNECT_KBN        ";
                $query .= "    ,ORGANIZATION_ID    ";
                $query .= "    ,SUPP_CD            ";
                $query .= "    ,TRNYM              ";
                $query .= "    ,SUPP_BUDGET        ";
                $query .= "    ,SALE_BUDGET        ";
                $query .= "    ,SALE_TOTAL         ";
                $query .= "    ,M_SUPP_TOTAL       ";
                $query .= "    ,M_SUPP_TAX         ";
                $query .= "    ,M_RETURN_TOTAL     ";
                $query .= "    ,M_DISC_TOTAL       ";
                $query .= "    ,M_PURI_SUPP_TOTAL  ";
                $query .= "    ,M_PAYM_TOTAL       ";
                $query .= "  ) values (            ";
                $query .= "     :INSUSER_CD        ";
                $query .= "    ,:INSDATETIME       ";
                $query .= "    ,:UPDUSER_CD        ";
                $query .= "    ,:UPDDATETIME       ";
                $query .= "    ,:DISABLED          ";
                $query .= "    ,:LAN_KBN           ";
                $query .= "    ,:CONNECT_KBN       ";
                $query .= "    ,:ORGANIZATION_ID   ";
                $query .= "    ,:SUPP_CD           ";
                $query .= "    ,:TRNYM             ";
                $query .= "    ,:SUPP_BUDGET       ";
                $query .= "    ,:SALE_BUDGET       ";
                $query .= "    ,:SALE_TOTAL        ";
                $query .= "    ,:M_SUPP_TOTAL      ";
                $query .= "    ,:M_SUPP_TAX        ";
                $query .= "    ,:M_RETURN_TOTAL    ";
                $query .= "    ,:M_DISC_TOTAL      ";
                $query .= "    ,:M_PURI_SUPP_TOTAL ";
                $query .= "    ,:M_PAYM_TOTAL      ";
                $query .= "  )                     ";

                $param = array();
                $param[":INSUSER_CD"]               = $_SESSION["LOGIN"];
                $param[":INSDATETIME"]              = "now()";
                $param[":UPDUSER_CD"]               = $_SESSION["LOGIN"];
                $param[":UPDDATETIME"]              = "now()";
                $param[":DISABLED"]                 = '0';
                $param[":LAN_KBN"]                  = '0';
                $param[":CONNECT_KBN"]              = '0';
                $param[":ORGANIZATION_ID"]          = $_POST["ORGANIZATION_ID"];
                $param[":SUPP_CD"]                  = $_POST["SUPP_CD"];
                $param[":TRNYM"]                    = $strTrnYM;
                $param[":SUPP_BUDGET"]              = $_POST["SUPP_BUDGET"];
                $param[":SALE_BUDGET"]              = $_POST["SALE_BUDGET"];
                $param[":SALE_TOTAL"]               = "0";
                $param[":M_SUPP_TOTAL"]             = "0";
                $param[":M_SUPP_TAX"]               = "0";
                $param[":M_RETURN_TOTAL"]           = "0";
                $param[":M_DISC_TOTAL"]             = "0";
                $param[":M_PURI_SUPP_TOTAL"]        = "0";
                $param[":M_PAYM_TOTAL"]             = "0";
            }
            else{
                $query  = "";
                $query  = "update ". $_SESSION["SCHEMA"].".jsk5200 set  ";
                $query .= "UPDUSER_CD               = :UPDUSER_CD        , ";
                $query .= "UPDDATETIME              = :UPDDATETIME       , ";
                $query .= "DISABLED                 = :DISABLED          , ";
                $query .= "LAN_KBN                  = :LAN_KBN           , ";
                $query .= "CONNECT_KBN              = :CONNECT_KBN       , ";
                $query .= "SUPP_BUDGET              = :SUPP_BUDGET       , ";
                $query .= "SALE_BUDGET              = :SALE_BUDGET         ";
                $query .= " where ORGANIZATION_ID   = :ORGANIZATION_ID     ";
                $query .= " and   SUPP_CD           = :SUPP_CD             ";
                $query .= " and   TRNYM             = :TRNYM               ";

                $param = array();
                $param[":UPDUSER_CD"]               = $_SESSION["LOGIN"];
                $param[":UPDDATETIME"]              = "now()";
                $param[":DISABLED"]                 = '0';
                $param[":LAN_KBN"]                  = '0';
                $param[":CONNECT_KBN"]              = '0';
                $param[":ORGANIZATION_ID"]          = $_POST["ORGANIZATION_ID"];
                $param[":SUPP_CD"]                  = $_POST["SUPP_CD"];
                $param[":TRNYM"]                    = $strTrnYM;
                $param[":SUPP_BUDGET"]              = $_POST["SUPP_BUDGET"];
                $param[":SALE_BUDGET"]              = $_POST["SALE_BUDGET"];
            }

            //print_r($param);
            //echo $query;
            // SQLの実行
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            //console.log($result);
            if ($result === false){
                $Log->trace("END updJsk5200data");
                return false;
            }
            
            $Log->trace("END updJsk5200data");
            return true;
        }
    }
?>