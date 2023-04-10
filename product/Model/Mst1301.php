<?php
    /**
     * @file      商品特売構成情報
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      特売構成情報テーブルの管理を行う
     */

    // BaseProducts.phpを読み込む
    require './Model/BaseProduct.php';

    /**
     * 商品特売構成情報クラス
     * @note   特売構成情報テーブルの管理を行う。
     */
    class Mst1301 extends BaseProduct
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
         * 商品特売構成情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        public function searchMst1301Data()
        {
            global $DBA, $Log; // searchMst1301Data変数宣言
            $Log->trace("START searchMst1301Data");
            $sql = "SELECT "
                    . "  mst1301.organization_id "
                    . ", mst1301.sale_plan_cd "
                    . ", mst1301.sale_plan_nm "
                    . ", mst1301.sale_plan_str_dt "
                    . ", mst1301.sale_plan_end_dt "
                    . ", count(mst1303.prod_cd) AS detail_cnt "
                    . " FROM mst1301 "
                    . " INNER JOIN mst1303 "
                    . " ON (mst1303.organization_id = mst1301.organization_id AND mst1303.sale_plan_cd = mst1301.sale_plan_cd) "
                    . " WHERE 1 = :sale_plan_cd "
                    . " GROUP BY "
                    . "  mst1301.organization_id "
                    . ", mst1301.sale_plan_cd "
                    . ", sale_plan_nm "
                    . ", sale_plan_str_dt "
                    . ", sale_plan_end_dt ";
            $searchArray = array(':sale_plan_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1301DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END searchMst1301Data");
                return $mst1301DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst1301DataList, $data);
            }

            $Log->trace("END searchMst1301Data");

            return $mst1301DataList;
        }

        /**
         * 商品特売構成情報データ取得
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
            public function getMst1301List()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst1301List");

//            $sql = " SELECT string_agg(type_cd,',') as list from mst1301 where 1 = :type_cd ";            
            $sql = " SELECT string_agg(organization_id||':'||sale_plan_cd,',') as list from mst1301 where 1 = :sale_plan_cd ";            
            $searchArray = array(':sale_plan_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1301DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst1301List");
                return $mst1301DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst1301DataList = $data;
            }

            $Log->trace("END getMst1301List");

            return $mst1301DataList;
        }   
        
        /**
         * 商品情報データ取得
         * @return   SQLの実行結果
         */
        public function get_mst0201_data( $aryKeys )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst0201_data");
            
            if (count($aryKeys) === 0) {
                $Log->trace("END get_mst0201_data");
                return array();
            }
            $sql  = ' SELECT';
            $sql .= '   prod_cd';
            $sql .= '  ,prod_nm';
            //$sql .= '  ,prod_kn';
            //$sql .= '  ,prod_kn_rk';
            //$sql .= '  ,prod_capa_nm';
            //$sql .= '  ,prod_capa_kn';
            $sql .= ' FROM mst0201';
//            $sql .= '  WHERE 1 = :type  ';
            $sql .= '  WHERE organization_id = :organization_id  ';
            $sql .= '  AND   prod_cd         = :prod_cd';

//            $searchArray = array( ':type' => 1 );                
            $searchArray = array(
                ':organization_id'  => $aryKeys['organization_id'],
                ':prod_cd'          => $aryKeys["setprod_cd"]
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0201DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst0201_data");
                return $mst0201DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst0201DataList, $data);
            }

            $Log->trace("END get_mst0201_data");

            return $mst0201DataList;
        }
        
        /**
         * 商品特売構成情報データ取得
         * @return   SQLの実行結果
         */
        //public function get_mst1301_data()
        public function get_mst1301_data( $aryKeys )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst1301_data");
            
            if (count($aryKeys) === 0) {
                $Log->trace("END get_mst1301_data");
                return array();
            }
            $sql  = " SELECT";
            $sql .= "   mst1301.organization_id                                     AS organization_id";
            $sql .= "  ,mst1301.sale_plan_cd                                        AS sale_plan_cd";
            $sql .= "  ,mst1301.sale_plan_nm                                        AS sale_plan_nm";
            $sql .= "  ,mst1301.sale_plan_str_dt                                    AS sale_plan_str_dt";
            $sql .= "  ,mst1301.sale_plan_end_dt                                    AS sale_plan_end_dt";
            $sql .= "  ,mst1303.prod_cd                                             AS prod_cd";
            $sql .= "  ,mst1303.sale_str_dt1                                        AS sale_str_dt1";
            $sql .= "  ,mst1303.sale_end_dt1                                        AS sale_end_dt1";
            $sql .= "  ,TO_CHAR(mst1303.costprice1,        'FM999,999,990.00')		AS costprice1 ";
            $sql .= "  ,TO_CHAR(mst1303.saleprice1,        'FM999,999,990')         AS saleprice1 ";
            $sql .= "  ,TO_CHAR(mst1303.cust_saleprice1,   'FM999,999,990')         AS cust_saleprice1 ";
            $sql .= "  ,mst1303.point_kbn1                                          AS point_kbn1";
            $sql .= "  ,mst1303.sale_str_dt2                                        AS sale_str_dt2";
            $sql .= "  ,mst1303.sale_end_dt2                                        AS sale_end_dt2";
            $sql .= "  ,TO_CHAR(mst1303.costprice2,        'FM999,999,990.00')      AS costprice2 ";
            $sql .= "  ,TO_CHAR(mst1303.saleprice2,        'FM999,999,990')         AS saleprice2 ";
            $sql .= "  ,TO_CHAR(mst1303.cust_saleprice2,   'FM999,999,990')         AS cust_saleprice2 ";
            $sql .= "  ,mst1303.point_kbn2                                          AS point_kbn2";
            $sql .= "  ,mst0201.prod_nm                                             AS prod_nm";
            $sql .= "  ,mst0201.prod_kn                                             AS prod_kn";
            $sql .= "  ,mst0201.prod_capa_nm                                        AS prod_capa_nm";
            $sql .= "  ,mst0201.prod_capa_kn                                        AS prod_capa_kn";
            $sql .= "  ,TO_CHAR(mst0201.head_costprice,    'FM999,999,990.00')      AS costprice ";
            $sql .= "  ,TO_CHAR(mst0201.saleprice,         'FM999,999,990')         AS saleprice ";
            $sql .= " FROM mst1301 ";
            $sql .= " INNER JOIN mst1303 ";
            $sql .= " ON (mst1303.organization_id = mst1301.organization_id AND mst1303.sale_plan_cd = mst1301.sale_plan_cd) ";
            $sql .= " INNER JOIN mst0201 ";
            $sql .= " ON (mst0201.organization_id = mst1303.organization_id AND mst0201.prod_cd = mst1303.prod_cd) ";
//            $sql .= '  WHERE 1 = :type  ';
            $sql .= '  WHERE mst1301.organization_id = :organization_id  ';
            $sql .= '  AND   mst1301.sale_plan_cd    = :sale_plan_cd';
            if (isset($aryKeys['prod_cd']) === true){
                $sql .= '  AND   mst1303.prod_cd    = :prod_cd';
            }
            $sql .= " ORDER BY prod_cd ";

//            $searchArray = array( ':type' => 1 );                
            $searchArray = array(
                ':organization_id'  => $aryKeys['organization_id'],
                ':sale_plan_cd'     => $aryKeys["sale_plan_cd"]
            );
            if (isset($aryKeys['prod_cd']) === true){
                $searchArray = array_merge($searchArray, array(':prod_cd' => $aryKeys['prod_cd']));
            }

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1301DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst1301_data");
                return $mst1301DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst1301DataList, $data);
            }

            $Log->trace("END get_mst1301_data");

            return $mst1301DataList;
        }
        
        /**
         * 商品特売構成マスタデータ登録
         * @param   $post           POSTデータ
         * @param   &$aryErrMsg     エラーメッセージ配列
         * @return
         */
        public function BatchRegist($aryKeys, $post, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START BatchRegist");

            // トランザクション開始
            $DBA->beginTransaction();

            // 削除
            if (isset($post['del_data']) === true){
                $del_data = json_decode($post['del_data'], true);
                //$mst1301->Delete($del_data);
                if ($this->Delete($aryKeys, $del_data, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
                }
            }
            // 新規
            if (isset($post['new_data']) === true){
                $new_data = json_decode($post['new_data'], true);
                //$mst1301->Insert($new_data);
                if ($this->Insert($aryKeys, $new_data, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
                }
            }
            // 更新
            if (isset($post['upd_data']) === true){
                $upd_data = json_decode($post['upd_data'], true);
                //$mst1301->Update($upd_data);
                if ($this->Update($aryKeys, $upd_data, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
                }
            }
            
            if (isset($del_data) === false && isset($new_data) === false && isset($upd_data) === false){
                // ヘッダ情報のみ更新
                if ($this->UpdateMst1301($aryKeys, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
                }
            }
                        
            // コミット
            $DBA->commit();
            $Log->trace("END   BatchRegist");
        }
        
        /**
         * 特売マスタ新規データ登録
         * @param
         * @return   SQLの実行結果
         */
        //public function Insert($data)
        public function Insert($aryKeys, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Insert");
//ADDSTR 2020/08/26 kanderu
           //select org id 
            $query   .= "";
            $query  .= "select  ";
            $query  .= "organization_id  ";
            $query  .= "from ". $_SESSION["SCHEMA"] .". m_organization_detail ";
            $query  .= "where organization_id <> 1  ";
            $query  .= "order by organization_id asc  ";
           // print_r($query);
           //  $result=  $DBA->sqlExec($query);
            $result1 = $DBA->executeSQL_no_searchpath($query);

            // 一覧表を格納する空の配列宣言
            $dataList1 = array();
             
            // 取得したデータ群を配列に格納
            while ( $data1 = $result1->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $dataList1, $data1);
            }
             if(count($dataList1) != 0){
                foreach ($dataList1 as $orglist1){
//ADDEND kanderu                 
       // if($this->get_sale_plan_cd($aryKeys) === "0"){
            // 特売マスタ
            $query  = "";
            //$query .= "insert into mst1301 (";
            $query .= "insert into  ". $_SESSION["SCHEMA"].".mst1301 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,SALE_PLAN_CD      ";
            $query .= "    ,SALE_PLAN_NM      ";
            $query .= "    ,SALE_PLAN_STR_DT  ";
            $query .= "    ,SALE_PLAN_END_DT  ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:SALE_PLAN_CD     ";
            $query .= "    ,:SALE_PLAN_NM     ";
            $query .= "    ,:SALE_PLAN_STR_DT ";
            $query .= "    ,:SALE_PLAN_END_DT ";
            $query .= "  )                    ";

            $param = array();
            $param[":INSUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":INSDATETIME"]      = "now()";
            $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":UPDDATETIME"]      = "now()";
            $param[":DISABLED"]         = '0';
            $param[":LAN_KBN"]          = '0';
            $param[":CONNECT_KBN"]      = '0';
            $param[":ORGANIZATION_ID"]  = $orglist1["organization_id"];
            $param[":SALE_PLAN_CD"]     = $aryKeys["sale_plan_cd"];
            $param[":SALE_PLAN_NM"]     = $aryKeys["sale_plan_nm"];
            $param[":SALE_PLAN_STR_DT"] = str_replace('/', '', $aryKeys["sale_plan_str_dt"]);
            $param[":SALE_PLAN_END_DT"] = str_replace('/', '', $aryKeys["sale_plan_end_dt"]);

            //$result = $DBA->executeSQL($query, $param);
             $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                $aryErrMsg[] = '追加処理中にエラーが発生しました。(特売企画コード：'.$aryKeys["sale_plan_cd"].')';
                $Log->trace("END   Insert");
                return false;
            }
            // 特売マスタ内訳
            $query  = "";
            //$query .= "insert into mst1302 (";
            $query .= "insert into  ". $_SESSION["SCHEMA"].".mst1302 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,SALE_PLAN_CD      ";
            $query .= "    ,BRAN_NO           ";
            $query .= "    ,SALE_PLAN_NM      ";
            $query .= "    ,SALE_PLAN_STR_DT  ";
            $query .= "    ,SALE_PLAN_END_DT  ";
            $query .= "    ,SEND_KBN          ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:SALE_PLAN_CD     ";
            $query .= "    ,:BRAN_NO          ";
            $query .= "    ,:SALE_PLAN_NM     ";
            $query .= "    ,:SALE_PLAN_STR_DT ";
            $query .= "    ,:SALE_PLAN_END_DT ";
            $query .= "    ,:SEND_KBN         ";
            $query .= "  )                    ";

            $param = array();
            $param[":INSUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":INSDATETIME"]      = "now()";
            $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":UPDDATETIME"]      = "now()";
            $param[":DISABLED"]         = '0';
            $param[":LAN_KBN"]          = '0';
            $param[":CONNECT_KBN"]      = '0';
            $param[":ORGANIZATION_ID"]  = $orglist1["organization_id"];
            $param[":SALE_PLAN_CD"]     = $aryKeys["sale_plan_cd"];
            $param[":BRAN_NO"]          = '01';
            $param[":SALE_PLAN_NM"]     = $aryKeys["sale_plan_nm"];
            $param[":SALE_PLAN_STR_DT"] = str_replace('/', '', $aryKeys["sale_plan_str_dt"]);
            $param[":SALE_PLAN_END_DT"] = str_replace('/', '', $aryKeys["sale_plan_end_dt"]);
            $param[":SEND_KBN"]         = '0';

           // $result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                $aryErrMsg[] = '追加処理中にエラーが発生しました。(特売企画コード：'.$aryKeys["sale_plan_cd"].')';
                $Log->trace("END   Insert");
                return false;
            }
   

            // 特売マスタ明細
            $query  = "";
            //$query .= "insert into mst1303 (";
            $query .= "insert into  ". $_SESSION["SCHEMA"].".mst1303 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,SALE_PLAN_CD      ";
            $query .= "    ,BRAN_NO           ";
            $query .= "    ,PROD_CD           ";
            $query .= "    ,SALE_STR_DT1      ";
            $query .= "    ,SALE_STR_TM1      ";
            $query .= "    ,SALE_END_DT1      ";
            $query .= "    ,SALE_END_TM1      ";
            $query .= "    ,COSTPRICE1        ";
            $query .= "    ,SALEPRICE1        ";
            $query .= "    ,CUST_SALEPRICE1   ";
            $query .= "    ,POINT_KBN1        ";
            $query .= "    ,SALE_STR_DT2      ";
            $query .= "    ,SALE_STR_TM2      ";
            $query .= "    ,SALE_END_DT2      ";
            $query .= "    ,SALE_END_TM2      ";
            $query .= "    ,COSTPRICE2        ";
            $query .= "    ,SALEPRICE2        ";
            $query .= "    ,CUST_SALEPRICE2   ";
            $query .= "    ,POINT_KBN2        ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:SALE_PLAN_CD     ";
            $query .= "    ,:BRAN_NO          ";
            $query .= "    ,:PROD_CD          ";
            $query .= "    ,:SALE_STR_DT1     ";
            $query .= "    ,:SALE_STR_TM1     ";
            $query .= "    ,:SALE_END_DT1     ";
            $query .= "    ,:SALE_END_TM1     ";
            $query .= "    ,:COSTPRICE1       ";
            $query .= "    ,:SALEPRICE1       ";
            $query .= "    ,:CUST_SALEPRICE1  ";
            $query .= "    ,:POINT_KBN1       ";
            $query .= "    ,:SALE_STR_DT2     ";
            $query .= "    ,:SALE_STR_TM2     ";
            $query .= "    ,:SALE_END_DT2     ";
            $query .= "    ,:SALE_END_TM2     ";
            $query .= "    ,:COSTPRICE2       ";
            $query .= "    ,:SALEPRICE2       ";
            $query .= "    ,:CUST_SALEPRICE2  ";
            $query .= "    ,:POINT_KBN2       ";
            $query .= "  )                    ";

            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['prod_cd']) === false){
                    continue;
                }
                $param = array();
                $param[":INSUSER_CD"]       = $_SESSION["USER_ID"];
                $param[":INSDATETIME"]      = "now()";
                $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
                $param[":UPDDATETIME"]      = "now()";
                $param[":DISABLED"]         = '0';
                $param[":LAN_KBN"]          = '0';
                $param[":CONNECT_KBN"]      = '0';
                $param[":ORGANIZATION_ID"]  = $orglist1["organization_id"];
                $param[":SALE_PLAN_CD"]     = $aryKeys["sale_plan_cd"];
                $param[":BRAN_NO"]          = '01';
                $param[":PROD_CD"]          = $data[$sind]["prod_cd"];
                $param[":SALE_STR_DT1"]     = str_replace('/','',$data[$sind]["sale_str_dt1"]);
                $param[":SALE_STR_TM1"]     = "0000";
                $param[":SALE_END_DT1"]     = str_replace('/','',$data[$sind]["sale_end_dt1"]);
                $param[":SALE_END_TM1"]     = "2359";
                $param[":COSTPRICE1"]       = str_replace(',','',$data[$sind]["costprice1"]);
                $param[":SALEPRICE1"]       = str_replace(',','',$data[$sind]["saleprice1"]);
                $param[":CUST_SALEPRICE1"]  = str_replace(',','',$data[$sind]["cust_saleprice1"]);
                $param[":POINT_KBN1"]       = $data[$sind]["point_kbn1"];
                $param[":SALE_STR_DT2"]     = str_replace('/','',$data[$sind]["sale_str_dt2"]);
                $param[":SALE_STR_TM2"]     = ($data[$sind]["sale_str_dt2"] === "") ? "" : "0000";
                $param[":SALE_END_DT2"]     = str_replace('/','',$data[$sind]["sale_end_dt2"]);
                $param[":SALE_END_TM2"]     = ($data[$sind]["sale_end_dt2"] === "") ? "" : "2359";
                $param[":COSTPRICE2"]       = str_replace(',','',$data[$sind]["costprice2"]);
                $param[":SALEPRICE2"]       = str_replace(',','',$data[$sind]["saleprice2"]);
                $param[":CUST_SALEPRICE2"]  = str_replace(',','',$data[$sind]["cust_saleprice2"]);
                $param[":POINT_KBN2"]       = $data[$sind]["point_kbn2"];

                //$result = $DBA->executeSQL($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                if ($result === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました。(特売企画コード：'.$aryKeys["sale_plan_cd"].'/商品コード'.$data[$sind]["prod_cd"].')';
                    $Log->trace("END   Insert");
                    return false;
                }
        }    
                }
             }
            $Log->trace("END   Insert");
            return true;
  
        }
      
        /**
         * 特売マスタ既存データ更新
         * @param
         * @return   SQLの実行結果
         */
        //public function Update($data)
        public function Update($aryKeys, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Update");

            if ($this->UpdateMst1301($aryKeys, $aryErrMsg) === false){
                $Log->trace("END   Update");
                return false;
            }

            // $data配列には主キーと変更項目の2要素が1行ごとに時系列で格納されている
            // 同じ変更項目が格納されることもあるが後優先である
            for ($intL = 0; $intL < count($data); $intL ++){
                $new_prod_cd = '';
                //
                $query  = "";
                //$query .= " update mst1303 set  ";
                $query  = "update  ". $_SESSION["SCHEMA"].".mst1303 set  ";
                $query .= "  UPDUSER_CD             = :UPDUSER_CD        , ";
                $query .= "  UPDDATETIME            = :UPDDATETIME       , ";
                $query .= "  DISABLED               = :DISABLED          , ";
                $query .= "  LAN_KBN                = :LAN_KBN           , ";
                $query .= "  CONNECT_KBN            = :CONNECT_KBN       , ";

                $param = array();
                $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
                $param[":UPDDATETIME"]      = "now()";
                $param[":DISABLED"]         = '0';
                $param[":LAN_KBN"]          = '0';
                $param[":CONNECT_KBN"]      = '0';
                //$param[":organization_id"]  = $aryKeys["organization_id"];
                $param[":sale_plan_cd"]     = $aryKeys["sale_plan_cd"];
                
                $updkey_cd = '';
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['updkey_prod_cd'])){
                    foreach ($data[$sind] as $key => $value){
                        $do_query = 0;
                        if ($key === 'updkey_prod_cd'){
                            $updkey_cd = $value;
                            $param[":updkey_prod_cd"] = $updkey_cd;
                            continue;
                        }
                        if (is_null($value) === false){
                            if ($key === 'prod_cd'){
                                if (is_numeric($value) === false){
                                    continue;
                                }
                                $new_prod_cd = $value;
                            }
                            $do_query = 1;
                            $query .= "  $key =    :$key  ";
                            //$param[':'.$key] = $value;
                            if (strpos($key, 'sale_str_dt') === 0 || strpos($key, 'sale_end_dt') === 0){
                                $param[':'.$key] = str_replace('/','',$value);
                            }
                            else if (strrpos($key, 'price') !== false){
                                $param[':'.$key] = str_replace(',','',$value);
                            }
                            else{
                                $param[':'.$key] = $value;
                            }
                        }
                    }
                    if ($do_query === 0){
                        continue;
                    }
                   // $query .= " where ORGANIZATION_ID   = :organization_id      ";
                    $query .= " where   sale_plan_cd      = :sale_plan_cd         ";
                    $query .= " and   prod_cd           = :updkey_prod_cd       ";

                    //$result = $DBA->executeSQL($query, $param);
                     $result = $DBA->executeSQL_no_searchpath($query, $param);
                    if ($result === false){
                        $aryErrMsg[] = '更新処理中にエラーが発生しました。(特売企画コード：'.$aryKeys["sale_plan_cd"].'/商品コード'.$updkey_cd.')';
                        $Log->trace("END   Update");
                        return false;
                    }

                    if ($new_prod_cd !== ''){
                        // 特売品更新記録(TRN3090)
                        //※prod_cd = $updkey_cd, upd_type = '3'(削除) / prod_cd = $new_prod_cd, upd_type = '1'(新規)
                        
                        
                    }
                    else{
                        // 特売品更新記録(TRN3090)
                        //※prod_cd = $updkey_cd, upd_type = '2'(更新)
                        
                    }
                }
            }

            $Log->trace("END   Update");
            return true;
        }

        /**
         * 特売マスタ既存データ削除
         */
        //public function Delete($data){
        public function Delete($aryKeys, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Delete");

            $query  = "";
            //$query .= " delete from mst1303 ";
             $query .= " delete from ". $_SESSION["SCHEMA"].".mst1303 ";
            //$query .= " where organization_id   = :organization_id";
            $query .= " where   sale_plan_cd      = :sale_plan_cd";
            $query .= " and   prod_cd           = :prod_cd";

            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['prod_cd']) === false || is_numeric($aryKeys['sale_plan_cd']) === false){
                    continue;
                }
                $param = array();
               // $param[":organization_id"]  = $aryKeys["organization_id"];
                $param[":sale_plan_cd"]     = $aryKeys["sale_plan_cd"];
                $param[":prod_cd"]          = $data[$sind]['prod_cd'];

                //$result = $DBA->executeSQL($query, $param);
                 $result = $DBA->executeSQL_no_searchpath($query, $param);
                if ($result === false){
                    $aryErrMsg[] = '削除処理中にエラーが発生しました。(特売企画コード：'.$aryKeys["sale_plan_cd"].'/商品コード'.$data[$sind]["prod_cd"].')';
                    $Log->trace("END   Delete");
                    return false;
                }

                // 特売品更新記録(TRN3090)
                
                
            }
            $Log->trace("END   Delete");
            return true;
        }
        
        /**
         * バンドルマスタ更新処理
         */
        public function UpdateMst1301($aryKeys, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START UpdateMst1301");

            //
            $query  = "";
            //$query .= " update mst1301 set  ";
            $query  = "update  ". $_SESSION["SCHEMA"].".mst1301 set  ";
            $query .= "  UPDUSER_CD             = :UPDUSER_CD        , ";
            $query .= "  UPDDATETIME            = :UPDDATETIME       , ";
            $query .= "  DISABLED               = :DISABLED          , ";
            $query .= "  LAN_KBN                = :LAN_KBN           , ";
            $query .= "  CONNECT_KBN            = :CONNECT_KBN       , ";
            $query .= "  SALE_PLAN_NM           = :SALE_PLAN_NM      , ";
            $query .= "  SALE_PLAN_STR_DT       = :SALE_PLAN_STR_DT  , ";
            $query .= "  SALE_PLAN_END_DT       = :SALE_PLAN_END_DT    ";
            //$query .= " where ORGANIZATION_ID   = :organization_id     ";
            $query .= " where   SALE_PLAN_CD      = :sale_plan_cd        ";

            $param = array();
            $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":UPDDATETIME"]      = "now()";
            $param[":DISABLED"]         = '0';
            $param[":LAN_KBN"]          = '0';
            $param[":CONNECT_KBN"]      = '0';
            $param[":SALE_PLAN_NM"]     = $aryKeys["sale_plan_nm"];
            $param[":SALE_PLAN_STR_DT"] = str_replace('/', '', $aryKeys["sale_plan_str_dt"]);
            $param[":SALE_PLAN_END_DT"] = str_replace('/', '', $aryKeys["sale_plan_end_dt"]);
            //$param[":organization_id"]  = $aryKeys["organization_id"];
            $param[":sale_plan_cd"]     = $aryKeys["sale_plan_cd"];

            //$result = $DBA->executeSQL($query, $param);
             $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                $aryErrMsg[] = '更新処理中にエラーが発生しました。(特売企画コード：'.$aryKeys["sale_plan_cd"].')';
                $Log->trace("END   UpdateMst1301");
                return false;
            }

            $query  .= "";
            //$query .= " update mst1302 set  ";
            $query  = "update  ". $_SESSION["SCHEMA"].".mst1302 set  ";
            $query .= "  UPDUSER_CD             = :UPDUSER_CD        , ";
            $query .= "  UPDDATETIME            = :UPDDATETIME       , ";
            $query .= "  DISABLED               = :DISABLED          , ";
            $query .= "  LAN_KBN                = :LAN_KBN           , ";
            $query .= "  CONNECT_KBN            = :CONNECT_KBN       , ";
            $query .= "  SALE_PLAN_NM           = :SALE_PLAN_NM      , ";
            $query .= "  SALE_PLAN_STR_DT       = :SALE_PLAN_STR_DT  , ";
            $query .= "  SALE_PLAN_END_DT       = :SALE_PLAN_END_DT  , ";
            $query .= "  SEND_KBN               = :SEND_KBN            ";
           // $query .= " where ORGANIZATION_ID   = :organization_id     ";
            $query .= " where   SALE_PLAN_CD      = :sale_plan_cd        ";
            $query .= " and   BRAN_NO           = :bran_no             ";

            $param = array();
            $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":UPDDATETIME"]      = "now()";
            $param[":DISABLED"]         = '0';
            $param[":LAN_KBN"]          = '0';
            $param[":CONNECT_KBN"]      = '0';
            $param[":SALE_PLAN_NM"]     = $aryKeys["sale_plan_nm"];
            $param[":SALE_PLAN_STR_DT"] = str_replace('/', '', $aryKeys["sale_plan_str_dt"]);
            $param[":SALE_PLAN_END_DT"] = str_replace('/', '', $aryKeys["sale_plan_end_dt"]);
            $param[":SEND_KBN"]         = "0";
            //$param[":organization_id"]  = $aryKeys["organization_id"];
            $param[":sale_plan_cd"]     = $aryKeys["sale_plan_cd"];
            $param[":bran_no"]          = "01";

            //$result = $DBA->executeSQL($query, $param);
             $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                $aryErrMsg[] = '更新処理中にエラーが発生しました。(特売企画コード：'.$aryKeys["sale_plan_cd"].')';
                $Log->trace("END   UpdateMst1301");
                return false;
            }

            $Log->trace("END   UpdateMst1301");
            return true;
        }

        /**
         * システムマスタデータ取得
         * @return   SQLの実行結果
         */
        public function get_mst0010_alldata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst0010_alldata");
            
            $sql  = ' SELECT *'
                        . '  FROM mst0010'
                        . '  WHERE sys_cd = :sys_cd  '
                        . '  ORDER BY'
                        . '  organization_id';

            $searchArray = array( ':sys_cd' => '1' );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0010AllDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst0010_alldata");
                return $mst0010AllDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst0010AllDataList, $data);
            }

            $Log->trace("END get_mst0010_alldata");

            return $mst0010AllDataList;
        }

        /**
         * 商品情報データ取得
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
            public function getMst0201List()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst0201List");

            $sql = " SELECT string_agg(organization_id||':'||prod_cd,',') as list from mst0201 where 1 = :prod_cd ";            
            $searchArray = array(':prod_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0201DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst0201List");
                return $mst0201DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst0201DataList = $data;
            }

            $Log->trace("END getMst0201List");

            return $mst0201DataList;
        }
        
        /**
         * 商品情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        //public function searchMst0201Data()
        public function searchMst0201Data( $organization_id = null )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START searchMst0201Data");

            $sql  = " SELECT organization_id, prod_cd,prod_nm,prod_kn";
            $sql .= ", prod_kn_rk, prod_capa_nm";
            $sql .= ", TO_CHAR(saleprice,       'FM999,999,990')    AS saleprice";
            $sql .= ", TO_CHAR(head_costprice,  'FM999,999,990.00') AS costprice";
            //$sql .= " from mst0201 where 1 = :prod_cd order by prod_cd desc";
            $sql .= " from mst0201 where 1 = :prod_cd ";
            if ($organization_id !== null){
                $sql .= " and organization_id = :organization_id ";
            }
            $sql .= " order by prod_cd desc";
            $searchArray = array(':prod_cd' => 1);
            if ($organization_id !== null){
                $searchArray = array_merge($searchArray, array(':organization_id' => $organization_id));
            }

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0201DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END searchMst0201Data");
                return $mst0201DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst0201DataList, $data);
            }

            $Log->trace("END searchMst0201Data");

            return $mst0201DataList;
        }
        
        function AllDelete($aryKeys, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START AllDelete");
            
            // トランザクション開始
            $DBA->beginTransaction();

            // 商品バンドル構成マスタ取得
            $mst1301data = $this->get_mst1301_data($aryKeys);
            
            $param = array();
           // $param[":organization_id"]  = $aryKeys["organization_id"];
            $param[":sale_plan_cd"]     = $aryKeys["sale_plan_cd"];

            // 商品バンドル構成マスタ削除
            $query  = "";
            //$query .= " delete from mst1301 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].".mst1301 ";
            //$query .= " where organization_id = :organization_id";
            $query .= " where   sale_plan_cd    = :sale_plan_cd";

            //$result = $DBA->executeSQL($query, $param);
             $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                $aryErrMsg[] = '削除処理中にエラーが発生しました。(特売企画コード：'.$aryKeys["sale_plan_cd"].')';
                $Log->trace("END   AllDelete");
                return false;
            }

            $query  = "";
            //$query .= " delete from mst1302 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].".mst1302 ";
           // $query .= " where organization_id = :organization_id";
            $query .= " where   sale_plan_cd    = :sale_plan_cd";
            //$result = $DBA->executeSQL($query, $param);
             $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                $aryErrMsg[] = '削除処理中にエラーが発生しました。(特売企画コード：'.$aryKeys["sale_plan_cd"].')';
                $Log->trace("END   AllDelete");
                return false;
            }
            
            $query  = "";
            //$query .= " delete from mst1303 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].".mst1303 ";
           // $query .= " where organization_id = :organization_id";
            $query .= " where   sale_plan_cd    = :sale_plan_cd";
           // $result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                $aryErrMsg[] = '削除処理中にエラーが発生しました。(特売企画コード：'.$aryKeys["sale_plan_cd"].')';
                $Log->trace("END   AllDelete");
                return false;
            }

            // 特売品更新記録(TRN3090)
            
            
            // コミット
            $DBA->commit();
            $Log->trace("END   AllDelete");
            return true;
        }
        
        function InsTrn3090($organization_id, $bundle_kbn, $prod_cd, $upd_type, $IN_drwRow = array())
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START InsTrn3090");

            // 商品バンドル情報を取得する
            if (count($IN_drwRow) ===0){
                $sql  = " SELECT";
                $sql .= "   mst1301.organization_id     AS organization_id";
                $sql .= "  ,mst1301.bundle_cd           AS bundle_cd";
                $sql .= "  ,mst1301.expira_str          AS expira_str";
                $sql .= "  ,mst1301.expira_end          AS expira_end";
                $sql .= "  ,mst1301.sale_price          AS sale_price";
                $sql .= "  ,mst1301.sale_point          AS sale_point";
                $sql .= "  ,mst5302.prod_cd             AS prod_cd";
                $sql .= "  ,mst5302.constitu_amount     AS constitu_amount";
                $sql .= " FROM mst1301";
                $sql .= " INNER JOIN mst5302";
                $sql .= " ON (mst5302.organization_id = mst1301.organization_id AND mst5302.bundle_cd = mst1301.bundle_cd)";
                $sql .= '  WHERE mst1301.organization_id = :organization_id  ';
                $sql .= '  AND   mst1301.bundle_cd       = :bundle_cd';
                $sql .= '  AND   mst5302.prod_cd         = :prod_cd';
                $searchArray = array(
                    ':organization_id'  => $organization_id,
                    ':bundle_cd'        => $bundle_kbn,
                    ':prod_cd'          => $prod_cd,
                );
                // SQLの実行
                $result = $DBA->executeSQL($sql, $searchArray);

                // 一覧表を格納する空の配列宣言
                $mst1301data = array();

                // データ取得ができなかった場合、falseを返す
                if( $result === false )
                {
                    $Log->trace("END InsTrn3050");
                    return;
                }

                while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                    $mst1301data = $data;
                }
            }
            else{
                $mst1301data = $IN_drwRow;
            }
            if (count($mst1301data) ===0){
                $Log->trace("END InsTrn3050");
                return;
            }

            // 商品バンドル更新記録(TRN3050)
            $query  = "";
            //$query .= "insert into trn3050 (";
            $query .= "insert into  ". $_SESSION["SCHEMA"].".mst1303 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,HIDESEQ           ";
            $query .= "    ,PROC_DATE         ";
            $query .= "    ,UPD_TYPE          ";
            $query .= "    ,BUNDLE_CD         ";
            $query .= "    ,PROD_CD           ";
            $query .= "    ,EXPIRA_STR        ";
            $query .= "    ,EXPIRA_END        ";
            $query .= "    ,SALE_PRICE        ";
            $query .= "    ,SALE_POINT        ";
            $query .= "    ,CONSTITU_AMOUNT   ";
            $query .= "    ,BUNDLE_UPDTIME    ";
            $query .= "    ,VAN_DATA_KBN      ";
            //$query .= "    ,VAN_DATA_DATE     ";
            $query .= "    ,PROD_SYNC_KBN     ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:HIDESEQ          ";
            $query .= "    ,:PROC_DATE        ";
            $query .= "    ,:UPD_TYPE         ";
            $query .= "    ,:BUNDLE_CD        ";
            $query .= "    ,:PROD_CD          ";
            $query .= "    ,:EXPIRA_STR       ";
            $query .= "    ,:EXPIRA_END       ";
            $query .= "    ,:SALE_PRICE       ";
            $query .= "    ,:SALE_POINT       ";
            $query .= "    ,:CONSTITU_AMOUNT  ";
            $query .= "    ,:BUNDLE_UPDTIME   ";
            $query .= "    ,:VAN_DATA_KBN     ";
            //$query .= "    ,:VAN_DATA_DATE    ";
            $query .= "    ,:PROD_SYNC_KBN    ";
            $query .= "  )                    ";
            
            $param = array();
            $param[":INSUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":INSDATETIME"]      = "now()";
            $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":UPDDATETIME"]      = "now()";
            $param[":DISABLED"]         = '0';
            $param[":LAN_KBN"]          = '0';
            $param[":CONNECT_KBN"]      = '0';
            $param[":ORGANIZATION_ID"]  = $mst1301data["organization_id"];
            $param[":HIDESEQ"]          = 0;
            $param[":PROC_DATE"]        = date('Ymd');
            $param[":UPD_TYPE"]         = $upd_type;
            $param[":BUNDLE_CD"]        = $mst1301data["bundle_cd"];
            $param[":PROD_CD"]          = $mst1301data["prod_cd"];
            $param[":EXPIRA_STR"]       = $mst1301data["expira_str"];
            $param[":EXPIRA_END"]       = $mst1301data["expira_end"];
            $param[":SALE_PRICE"]       = str_replace(',', '', $mst1301data["sale_price"]);
            $param[":SALE_POINT"]       = $mst1301data["sale_point"];
            $param[":CONSTITU_AMOUNT"]  = $mst1301data["constitu_amount"];
            $param[":BUNDLE_UPDTIME"]   = date('His');
            $param[":VAN_DATA_KBN"]     = '0';
            //$param[":VAN_DATA_DATE"]    = date('YmdHis');
            $param[":PROD_SYNC_KBN"]    = '0';
            
            //$result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                $Log->trace("END   InsTrn3031");
                return false;
            }
            return true;
        }
        
        public function get_sale_plan_cd($aryKeys)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_sale_plan_cd");
            $cnt = "0";
            // 期間売価コード取得
            $sql = "";
            $sql .= " SELECT count(*) cnt FROM mst1301 ";
            $sql .= " WHERE organization_id = :organization_id AND sale_plan_cd = :sale_plan_cd";
            $searchArray = array();
            $searchArray[":organization_id"] = $aryKeys["organization_id"];
            $searchArray[":sale_plan_cd"] = $aryKeys["sale_plan_cd"];
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            // データ取得ができなかった場合、エラーを返す
            if( $result === false )
            {
                // SQL実行エラー
                $Log->trace("END   get_sale_plan_cd");
                return $cnt;
            }
            
            // 取得したデータを格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $cnt = $data['cnt'];
            }

            $Log->trace("END   get_sale_plan_cd");
            return $cnt;
        }
    }
?>