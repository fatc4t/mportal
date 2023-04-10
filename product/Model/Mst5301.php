<?php
    /**
     * @file      商品バンドル構成情報
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      商品バンドル構成情報テーブルの管理を行う
     */

    // BaseProducts.phpを読み込む
    require './Model/BaseProduct.php';

    /**
     * 商品バンドル構成情報クラス
     * @note   商品バンドル構成情報テーブルの管理を行う。
     */
    class Mst5301 extends BaseProduct
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
         * 商品バンドル構成情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        public function searchMst5301Data()
        {
            global $DBA, $Log; // searchMst5301Data変数宣言
            $Log->trace("START searchMst5301Data");
            $sql = "SELECT "
                    . "  mst5301.organization_id "
                    . ", mst5301.bundle_cd "
                    . ", mst5301.expira_str "
                    . ", mst5301.expira_end "
                    . ", TO_CHAR(mst5301.sale_price, 'FM999,999,990')               AS sale_price "
                    . ", string_agg(mst5302.prod_cd, ',' ORDER BY mst5302.prod_cd)  AS prod_cd "
                    . " FROM mst5301 "
                    . " INNER JOIN mst5302 "
                    . " ON (mst5302.organization_id = mst5301.organization_id AND mst5302.bundle_cd = mst5301.bundle_cd) "
                    . " INNER JOIN mst0201 "
                    . " ON (mst0201.organization_id = mst5302.organization_id AND mst0201.prod_cd = mst5302.prod_cd) "
                    . " WHERE 1 = :bundle_cd "
                    . " GROUP BY "
                    . "  mst5301.organization_id "
                    . ", mst5301.bundle_cd "
                    . ", expira_str "
                    . ", expira_end "
                    . ", sale_price ";
            $searchArray = array(':bundle_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst5301DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END searchMst5301Data");
                return $mst5301DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst5301DataList, $data);
            }

            $Log->trace("END searchMst5301Data");

            return $mst5301DataList;
        }

        /**
         * 商品バンドル構成情報データ取得
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
            public function getMst5301List()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst5301List");

//            $sql = " SELECT string_agg(type_cd,',') as list from mst5301 where 1 = :type_cd ";            
            $sql = " SELECT string_agg(organization_id||':'||bundle_cd,',') as list from mst5301 where 1 = :bundle_cd ";            
            $searchArray = array(':bundle_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst5301DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst5301List");
                return $mst5301DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst5301DataList = $data;
            }

            $Log->trace("END getMst5301List");

            return $mst5301DataList;
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
         * 商品バンドル構成情報データ取得
         * @return   SQLの実行結果
         */
        //public function get_mst5301_data()
        public function get_mst5301_data( $aryKeys )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst5301_data");
            
            if (count($aryKeys) === 0) {
                $Log->trace("END get_mst5301_data");
                return array();
            }
            $sql  = " SELECT";
            $sql .= "   mst5301.organization_id                                 AS organization_id";
            $sql .= "  ,mst5301.bundle_cd                                       AS bundle_cd";
            $sql .= "  ,mst5301.expira_str                                      AS expira_str";
            $sql .= "  ,mst5301.expira_end                                      AS expira_end";
            $sql .= "  ,TO_CHAR(mst5301.sale_price,         'FM999,999,990')    AS sale_price";
            $sql .= "  ,TO_CHAR(mst5301.sale_point,         'FM999,999,990')    AS sale_point";
            $sql .= "  ,mst5302.prod_cd                                         AS prod_cd";
            $sql .= "  ,TO_CHAR(mst5302.constitu_amount,    'FM999,999,990')    AS constitu_amount";
            $sql .= "  ,mst0201.prod_nm                                         AS prod_nm";
            $sql .= "  ,mst0201.prod_capa_nm                                    AS prod_capa_nm";
            $sql .= "  ,TO_CHAR(mst0201.saleprice,          'FM999,999,990')    AS saleprice";
            $sql .= " FROM mst5301";
            $sql .= " INNER JOIN mst5302";
            $sql .= " ON (mst5302.organization_id = mst5301.organization_id AND mst5302.bundle_cd = mst5301.bundle_cd)";
            $sql .= " INNER JOIN mst0201";
            $sql .= " ON (mst0201.organization_id = mst5302.organization_id AND mst0201.prod_cd = mst5302.prod_cd)";
//            $sql .= '  WHERE 1 = :type  ';
            $sql .= '  WHERE mst5301.organization_id = :organization_id  ';
            $sql .= '  AND   mst5301.bundle_cd       = :bundle_cd';

//            $searchArray = array( ':type' => 1 );                
            $searchArray = array(
                ':organization_id'  => $aryKeys['organization_id'],
                ':bundle_cd'        => $aryKeys["bundle_cd"]
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst5301DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst5301_data");
                return $mst5301DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst5301DataList, $data);
            }

            $Log->trace("END get_mst5301_data");

            return $mst5301DataList;
        }
        
        /**
         * 商品バンドル構成マスタデータ登録
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
                //$mst5301->Delete($del_data);
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
                //$mst5301->Insert($new_data);
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
                //$mst5301->Update($upd_data);
                if ($this->Update($aryKeys, $upd_data, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
                }
            }
            if (isset($del_data) === false && isset($new_data) === false && isset($upd_data) === false){
                // ヘッダ情報のみ更新
                if ($this->UpdateMst5301($aryKeys, $aryErrMsg) === false){
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
         * 商品バンドル構成マスタ新規データ登録
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
            $query   ="";
            $query  .= "select  ";
            $query  .= "organization_id  ";
            $query  .= "from ". $_SESSION["SCHEMA"] .". m_organization_detail ";
            $query  .= "where organization_id <> 1  ";
            $query  .= "order by organization_id  ";
            //print_r($sql);
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
                foreach ($dataList as $orglist){
//ADDEND kanderu   
            //if($this->get_bundle_cd($aryKeys) === "0"){
            $query  = "";
            //$query .= "insert into mst5301 (";
            $query .= "insert into ". $_SESSION["SCHEMA"].".mst5301 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,BUNDLE_CD         ";
            $query .= "    ,EXPIRA_STR        ";
            $query .= "    ,EXPIRA_END        ";
            $query .= "    ,SALE_PRICE        ";
            $query .= "    ,SALE_POINT        ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:BUNDLE_CD        ";
            $query .= "    ,:EXPIRA_STR       ";
            $query .= "    ,:EXPIRA_END       ";
            $query .= "    ,:SALE_PRICE       ";
            $query .= "    ,:SALE_POINT       ";
            $query .= "  )                    ";

            $param = array();
            $param[":INSUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":INSDATETIME"]      = "now()";
            $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":UPDDATETIME"]      = "now()";
            $param[":DISABLED"]         = '0';
            $param[":LAN_KBN"]          = '0';
            $param[":CONNECT_KBN"]      = '0';
            $param[":ORGANIZATION_ID"]  = $orglist["organization_id"];
            $param[":BUNDLE_CD"]        = $aryKeys["bundle_cd"];
            $param[":EXPIRA_STR"]       = str_replace('/', '', $aryKeys["expira_str"]);
            $param[":EXPIRA_END"]       = str_replace('/', '', $aryKeys["expira_end"]);
            $param[":SALE_PRICE"]       = str_replace(',', '', $aryKeys["sale_price"]);
            $param[":SALE_POINT"]       = '0';

            //$result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query,$param);
            if ($result === false){
                $aryErrMsg[] = '追加処理中にエラーが発生しました1。(商品バンドルコード：'.$aryKeys["bundle_cd"].')';
                $Log->trace("END   Insert");
                return false;
            }
            $query  = "";
            //$query .= "insert into mst5302 (";
            $query .= "insert into ". $_SESSION["SCHEMA"].".mst5302 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,BUNDLE_CD         ";
            $query .= "    ,PROD_CD           ";
            $query .= "    ,CONSTITU_AMOUNT   ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:BUNDLE_CD        ";
            $query .= "    ,:PROD_CD          ";
            $query .= "    ,:CONSTITU_AMOUNT  ";
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
                $param[":ORGANIZATION_ID"]  = $orglist["organization_id"];
                $param[":BUNDLE_CD"]        = $aryKeys["bundle_cd"];
                $param[":PROD_CD"]          = $data[$sind]["prod_cd"];
                $param[":CONSTITU_AMOUNT"]  = $data[$sind]["constitu_amount"];

                //$result = $DBA->executeSQL($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query,$param);
                if ($result === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました2。(商品バンドルコード：'.$aryKeys["bundle_cd"].'/商品コード'.$data[$sind]["prod_cd"].')';
                    $Log->trace("END   Insert");
                    return false;
                }
              }
            }
            $Log->trace("END   Insert");
            return true;
        }
        
        /**
         * 商品バンドル構成マスタ既存データ更新
         * @param
         * @return   SQLの実行結果
         */
        //public function Update($data)
        public function Update($aryKeys, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Update");

            //
            $query  = "";
            //$query .= " update mst5301 set  ";
            $query .=  " update ". $_SESSION["SCHEMA"].".mst5301 set  "; 
            $query .= "  UPDUSER_CD             = :UPDUSER_CD        , ";
            $query .= "  UPDDATETIME            = :UPDDATETIME       , ";
            $query .= "  DISABLED               = :DISABLED          , ";
            $query .= "  LAN_KBN                = :LAN_KBN           , ";
            $query .= "  CONNECT_KBN            = :CONNECT_KBN       , ";
            $query .= "  EXPIRA_STR             = :EXPIRA_STR        , ";
            $query .= "  EXPIRA_END             = :EXPIRA_END        , ";
            $query .= "  SALE_PRICE             = :SALE_PRICE        , ";
            $query .= "  SALE_POINT             = :SALE_POINT          ";
            //$query .= " where ORGANIZATION_ID   = :organization_id     ";
            $query .= " where   BUNDLE_CD         = :bundle_cd           ";

            $param = array();
            $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":UPDDATETIME"]      = "now()";
            $param[":DISABLED"]         = '0';
            $param[":LAN_KBN"]          = '0';
            $param[":CONNECT_KBN"]      = '0';
            $param[":EXPIRA_STR"]       = str_replace('/', '', $aryKeys["expira_str"]);
            $param[":EXPIRA_END"]       = str_replace('/', '', $aryKeys["expira_end"]);
            $param[":SALE_PRICE"]       = str_replace(',', '', $aryKeys["sale_price"]);
            $param[":SALE_POINT"]       = '0';
            //$param[":organization_id"]  = $aryKeys["organization_id"];
            $param[":bundle_cd"]        = $aryKeys["bundle_cd"];

           // $result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品バンドルコード：'.$aryKeys["bundle_cd"].')';
                $Log->trace("END   Update");
                return false;
            }

            // $data配列には主キーと変更項目の2要素が1行ごとに時系列で格納されている
            // 同じ変更項目が格納されることもあるが後優先である
            for ($intL = 0; $intL < count($data); $intL ++){
                //
                $new_prod_cd = '';
                $query  = "";
                //$query .= " update mst5302 set  ";
                $query .=  " update ". $_SESSION["SCHEMA"].".mst5302 set  "; 
                $query .= "  UPDUSER_CD         = :UPDUSER_CD        , ";
                $query .= "  UPDDATETIME        = :UPDDATETIME       , ";
                $query .= "  DISABLED           = :DISABLED          , ";
                $query .= "  LAN_KBN            = :LAN_KBN           , ";
                $query .= "  CONNECT_KBN        = :CONNECT_KBN       , ";

                $param = array();
                $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
                $param[":UPDDATETIME"]      = "now()";
                $param[":DISABLED"]         = '0';
                $param[":LAN_KBN"]          = '0';
                $param[":CONNECT_KBN"]      = '0';
                //$param[":organization_id"]  = $aryKeys["organization_id"];
                $param[":bundle_cd"]        = $aryKeys["bundle_cd"];

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
                                // 他テーブル使用状況チェック
                                if ($this->UsedTableCheck($aryKeys["bundle_cd"]) === false){
                                    $aryErrMsg[] = '他のマスタで使用されているためコード変更できません。(商品バンドルコード：'.$aryKeys["bundle_cd"].')';
                                    continue;
                                }
                                $new_prod_cd = $value;
                            }
                            $do_query = 1;
                            $query .= "  $key =    :$key  ";
                            $param[':'.$key] = $value;
                        }
                    }
                    if ($do_query === 0){
                        continue;
                    }
                    //$query .= " where ORGANIZATION_ID   = :organization_id      ";
                    $query .= " where   bundle_cd         = :bundle_cd            ";
                    $query .= " and   prod_cd           = :updkey_prod_cd       ";

                    //$result = $DBA->executeSQL($query, $param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    if ($result === false){
                        $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品バンドルコード：'.$aryKeys["bundle_cd"].'/商品コード'.$updkey_cd.')';
                        $Log->trace("END   Update");
                        return false;
                    }

                    // 商品マスタ　セット商品区分更新
                    if ($new_prod_cd !== ''){
                        // 商品バンドル構成マスタ存在チェック
                        if ($this->BundleIsExist($aryKeys["organization_id"], $updkey_cd, "") === false){
                            // 商品マスタ　セット商品区分更新
                            if ($this->BundleKbnUpd( $updkey_cd, "0") === false){
                                $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品バンドルコード：'.$aryKeys["bundle_cd"].'/商品コード'.$updkey_cd.')';
                                $Log->trace("END   Update");
                                return false;
                            }
                        }
                        // 商品マスタ　バンドル区分更新
                        if ($this->BundleKbnUpd($new_prod_cd, "1") === false){
                            $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品バンドルコード：'.$aryKeys["bundle_cd"].'/商品コード'.$updkey_cd.')';
                            $Log->trace("END   Update");
                            return false;
                        }

                        // 商品バンドル更新記録(TRN3050)
                        //※prod_cd = $updkey_cd, upd_type = '3'(削除) / prod_cd = $new_prod_cd, upd_type = '1'(新規)
                        
                    }
                    else{
                        // 商品バンドル更新記録(TRN3050)
                        //※prod_cd = $updkey_cd, upd_type = '2'(更新)
                        
                    }
                }
            }

            $Log->trace("END   Update");
            return true;
        }

        /**
         * 商品バンドル構成マスタ既存データ削除
         */
        //public function Delete($data){
        public function Delete($aryKeys, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START AllDelete");
            
            // トランザクション開始
            $DBA->beginTransaction();

            // 商品バンドル構成マスタ取得
            $mst5301data = $this->get_mst5301_data($aryKeys);
            
            $param = array();
            //$param[":organization_id"]  = $aryKeys["organization_id"];
            $param[":bundle_cd"]        = $aryKeys["bundle_cd"];

            // 他テーブル使用状況チェック
            if ($this->UsedTableCheck($aryKeys["bundle_cd"]) === false){
                $aryErrMsg[] = '他のマスタで使用されているため削除できません。(商品バンドルコード：'.$aryKeys["bundle_cd"].')';
                return false;
            }

            // 商品バンドル構成マスタ削除
            $query  = "";
            //$query .= " delete from mst5301 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].". mst5301 ";
            //$query .= " where organization_id = :organization_id";
            $query .= " where   bundle_cd       = :bundle_cd";

            //$result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品バンドルコード：'.$aryKeys["bundle_cd"].')';
                $Log->trace("END   AllDelete");
                return false;
            }
            $query  = "";
            //$query .= " delete from mst5302 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].". mst5302 ";
           // $query .= " where organization_id = :organization_id";
            $query .= " where   bundle_cd       = :bundle_cd";
            $query .= " and   prod_cd         = :prod_cd";

            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['prod_cd']) === false || is_numeric($aryKeys['bundle_cd']) === false){
                    continue;
                }
                $param = array();
                //$param[":organization_id"]  = $aryKeys["organization_id"];
                $param[":bundle_cd"]        = $aryKeys["bundle_cd"];
                $param[":prod_cd"]          = $data[$sind]['prod_cd'];

                //$result = $DBA->executeSQL($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                if ($result === false){
                    $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品バンドルコード：'.$aryKeys["bundle_cd"].'/商品コード'.$data[$sind]["prod_cd"].')';
                    $Log->trace("END   Delete");
                    return false;
                }

                // 商品バンドル構成マスタ存在チェック
                if ($this->BundleIsExist($aryKeys["bundle_cd"], $data[$sind]['prod_cd'], "") === false){
                    // 商品マスタ　バンドル区分更新
                    if ($this->BundleKbnUpd($data[$sind]['prod_cd'], "0") === false){
                        $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品バンドルコード：'.$aryKeys["bundle_cd"].'/'.$data[$sind]["prod_cd"].')';
                        $Log->trace("END   Delete");
                        return false;
                    }
                }
                
                // 商品バンドル更新記録(TRN3050)
                
                
            }
            $Log->trace("END   Delete");
            return true;
        }
        
        /**
         * バンドルマスタ更新処理
         */
        public function UpdateMst5301($aryKeys, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START UpdateMst5301");

            $query  = "";
            //$query .= " update mst5301 set  ";
            $query .=  " update ". $_SESSION["SCHEMA"].".mst5301 set  "; 
            $query .= "  UPDUSER_CD             = :UPDUSER_CD        , ";
            $query .= "  UPDDATETIME            = :UPDDATETIME       , ";
            $query .= "  DISABLED               = :DISABLED          , ";
            $query .= "  LAN_KBN                = :LAN_KBN           , ";
            $query .= "  CONNECT_KBN            = :CONNECT_KBN       , ";
            $query .= "  EXPIRA_STR             = :EXPIRA_STR        , ";
            $query .= "  EXPIRA_END             = :EXPIRA_END        , ";
            $query .= "  SALE_PRICE             = :SALE_PRICE        , ";
            $query .= "  SALE_POINT             = :SALE_POINT          ";
            //$query .= " where ORGANIZATION_ID   = :organization_id     ";
            $query .= " where   BUNDLE_CD         = :bundle_cd           ";

            $param = array();
            $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":UPDDATETIME"]      = "now()";
            $param[":DISABLED"]         = '0';
            $param[":LAN_KBN"]          = '0';
            $param[":CONNECT_KBN"]      = '0';
            $param[":EXPIRA_STR"]       = str_replace('/', '', $aryKeys["expira_str"]);
            $param[":EXPIRA_END"]       = str_replace('/', '', $aryKeys["expira_end"]);
            $param[":SALE_PRICE"]       = str_replace(',', '', $aryKeys["sale_price"]);
            $param[":SALE_POINT"]       = '0';
           // $param[":organization_id"]  = $aryKeys["organization_id"];
            $param[":bundle_cd"]        = $aryKeys["bundle_cd"];

            //$result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品バンドルコード：'.$aryKeys["bundle_cd"].')';
                $Log->trace("END   UpdateMst5301");
                return false;
            }

            $Log->trace("END   UpdateMst5301");
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
        public function searchMst0201Data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START searchMst0201Data");

            $sql  = " SELECT organization_id, prod_cd,prod_nm,prod_kn";
            $sql .= ", prod_kn_rk, prod_capa_nm";
            $sql .= ", TO_CHAR(saleprice,       'FM999,999,990')    AS saleprice";
            $sql .= ", TO_CHAR(head_costprice,  'FM999,999,990.00') AS costprice";
            $sql .= " from mst0201 where 1 = :prod_cd order by prod_cd desc";
            $searchArray = array(':prod_cd' => 1);

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
        
        /**
         * 商品バンドル構成マスタ存在チェック
         */
        function BundleIsExist($organization_id, $bundle_cd, $prod_cd)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START BundleIsExist");

            $sql  = " SELECT count(*) AS cnt";
            $sql .= " from mst5302";
            $sql .= " WHERE organization_id = :organization_id";
            $sql .= " AND   bundle_cd       = :bundle_cd";
            $sql .= " AND   prod_cd         = :prod_cd";

            $searchArray = array(
                ':organization_id'  => $organization_id,
                ':bundle_cd'        => $bundle_cd,
                ':prod_cd'          => $prod_cd);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $aryResult = array();

            // データ取得ができなかった場合、falseを返す
            if( $result === false )
            {
                $Log->trace("END BundleIsExist");
                return false;
            }

            $numRet = 0;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                $numRet = intval($data['cnt']);
            }
            if ($numRet > 0) {
                $Log->trace("END BundleIsExist");
                return true;
            }

            $Log->trace("END BundleIsExist");

            return false;
        }
        
        function AllDelete($aryKeys, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START AllDelete");
            
            // トランザクション開始
            $DBA->beginTransaction();

            // 商品バンドル構成マスタ取得
            $mst5301data = $this->get_mst5301_data($aryKeys);
            
            $param = array();
            //$param[":organization_id"]  = $aryKeys["organization_id"];
            $param[":bundle_cd"]        = $aryKeys["bundle_cd"];

            // 他テーブル使用状況チェック
            if ($this->UsedTableCheck($aryKeys["bundle_cd"]) === false){
                $aryErrMsg[] = '他のマスタで使用されているため削除できません。(商品バンドルコード：'.$aryKeys["bundle_cd"].')';
                return false;
            }

            // 商品バンドル構成マスタ削除
            $query  = "";
            //$query .= " delete from mst5301 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].". mst5301 ";
            //$query .= " where organization_id = :organization_id";
            $query .= " where   bundle_cd       = :bundle_cd";

            //$result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品バンドルコード：'.$aryKeys["bundle_cd"].')';
                $Log->trace("END   AllDelete");
                return false;
            }

            $query  = "";
            //$query .= " delete from mst5302 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].". mst5302 ";
           // $query .= " where organization_id = :organization_id";
            $query .= " where   bundle_cd       = :bundle_cd";
            //$result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品バンドルコード：'.$aryKeys["bundle_cd"].')';
                $Log->trace("END   AllDelete");
                return false;
            }
            
            for ($i = 0; $i < count($mst5301data); $i ++)
            {
                // 商品バンドル構成マスタ存在チェック
                if ($this->BundleIsExist( $mst5301data[$i]["bundle_cd"], $mst5301data[$i]["prod_cd"]) === false){
                    // 商品マスタ　セット商品区分更新
                    if ($this->BundleKbnUpd( $mst5301data[$i]["prod_cd"], "0") === false){
                        // ロールバック
                        $DBA->rollBack();
                        $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品バンドルコード：'.$aryKeys["bundle_cd"].')';
                        $Log->trace("END   AllDelete");
                        return false;
                    }
                }
            }
            
            // 商品バンドル更新記録(TRN3031)
            // コミット
            $DBA->commit();
            $Log->trace("END   AllDelete");
            return true;
        }
        
        function BundleKbnUpd($organization_id, $prod_cd, $bundle_kbn)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START BundleKbnUpd");
            
            // 商品マスタ　セット商品区分取得
            $sql  = "";
            $sql .= " SELECT set_prod_kbn";
            $sql .= "    FROM mst0201";
            $sql .= "  WHERE organization_id = :organization_id";
            $sql .= "  AND   prod_cd         = :prod_cd";
            $searchArray = array(
                ':organization_id'  => $organization_id,
                ':prod_cd'          => $prod_cd
            );
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0201data = array();

            // データ取得ができなかった場合、falseを返す
            if( $result === false )
            {
                $Log->trace("END BundleKbnUpd");
                return;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                $mst0201data = $data;
            }
            if ($mst0201data["set_prod_kbn"] !== $bundle_kbn){
                // 商品マスタ　セット商品区分更新
                $query  = "";
                $query .= " UPDATE mst0201 SET";
                $query .= "   UPDUSER_CD          = :UPDUSER_CD";
                $query .= "  ,UPDDATETIME         = :UPDDATETIME";
                $query .= "  ,DISABLED            = :DISABLED";
                $query .= "  ,LAN_KBN             = :LAN_KBN";
                $query .= "  ,CONNECT_KBN         = :CONNECT_KBN";
                $query .= "  ,BUNDLE_KBN          = :BUNDLE_KBN";
                $query .= " WHERE ORGANIZATION_ID = :ORGANIZATION_ID";
                $query .= " AND   PROD_CD         = :PROD_CD";
                $param = array();
                $param[":UPDUSER_CD"]           = $_SESSION["USER_ID"];
                $param[":UPDDATETIME"]          = "now()";
                $param[":DISABLED"]             = '0';
                $param[":LAN_KBN"]              = '0';
                $param[":CONNECT_KBN"]          = '0';
                $param[":ORGANIZATION_ID"]      = $organization_id;
                $param[":PROD_CD"]              = $prod_cd;
                $param[":BUNDLE_KBN"]           = $bundle_kbn;

                $result = $DBA->executeSQL($query, $param);
                if ($result === false){
                    $Log->trace("END   BundleKbnUpd");
                    return false;
                }
                
                
                // 商品更新記録(TRN3020)
                
                
            }
            
            $Log->trace("END BundleKbnUpd");
            return true;
        }  
        function InsTrn3050($organization_id, $bundle_kbn, $prod_cd, $upd_type, $IN_drwRow = array())
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START InsTrn3050");

            // 商品バンドル情報を取得する
            if (count($IN_drwRow) ===0){
                $sql  = " SELECT";
                $sql .= "   mst5301.organization_id     AS organization_id";
                $sql .= "  ,mst5301.bundle_cd           AS bundle_cd";
                $sql .= "  ,mst5301.expira_str          AS expira_str";
                $sql .= "  ,mst5301.expira_end          AS expira_end";
                $sql .= "  ,mst5301.sale_price          AS sale_price";
                $sql .= "  ,mst5301.sale_point          AS sale_point";
                $sql .= "  ,mst5302.prod_cd             AS prod_cd";
                $sql .= "  ,mst5302.constitu_amount     AS constitu_amount";
                $sql .= " FROM mst5301";
                $sql .= " INNER JOIN mst5302";
                $sql .= " ON (mst5302.organization_id = mst5301.organization_id AND mst5302.bundle_cd = mst5301.bundle_cd)";
                $sql .= '  WHERE mst5301.organization_id = :organization_id  ';
                $sql .= '  AND   mst5301.bundle_cd       = :bundle_cd';
                $sql .= '  AND   mst5302.prod_cd         = :prod_cd';
                $searchArray = array(
                    ':organization_id'  => $organization_id,
                    ':bundle_cd'        => $bundle_kbn,
                    ':prod_cd'          => $prod_cd,
                );
                // SQLの実行
                //$result = $DBA->executeSQL($sql, $searchArray);
                $result = $DBA->executeSQL_no_searchpath($sql,$searchArray);
                // 一覧表を格納する空の配列宣言
                $mst5301data = array();

                // データ取得ができなかった場合、falseを返す
                if( $result === false )
                {
                    $Log->trace("END InsTrn3050");
                    return;
                }

                while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                    $mst5301data = $data;
                }
            }
            else{
                $mst5301data = $IN_drwRow;
            }
            if (count($mst5301data) ===0){
                $Log->trace("END InsTrn3050");
                return;
            }

            // 商品バンドル更新記録(TRN3050)
            $query  = "";
            //$query .= "insert into trn3050 (";
             $query .= "insert into ". $_SESSION["SCHEMA"].".trn3050 (";
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
            $param[":ORGANIZATION_ID"]  = $mst5301data["organization_id"];
            $param[":HIDESEQ"]          = 0;
            $param[":PROC_DATE"]        = date('Ymd');
            $param[":UPD_TYPE"]         = $upd_type;
            $param[":BUNDLE_CD"]        = $mst5301data["bundle_cd"];
            $param[":PROD_CD"]          = $mst5301data["prod_cd"];
            $param[":EXPIRA_STR"]       = $mst5301data["expira_str"];
            $param[":EXPIRA_END"]       = $mst5301data["expira_end"];
            $param[":SALE_PRICE"]       = str_replace(',', '', $mst5301data["sale_price"]);
            $param[":SALE_POINT"]       = $mst5301data["sale_point"];
            $param[":CONSTITU_AMOUNT"]  = $mst5301data["constitu_amount"];
            $param[":BUNDLE_UPDTIME"]   = date('His');
            $param[":VAN_DATA_KBN"]     = '0';
            //$param[":VAN_DATA_DATE"]    = date('YmdHis');
            $param[":PROD_SYNC_KBN"]    = '0';
            
            //$result = $DBA->executeSQL($query, $param);
             $result = $DBA->executeSQL_no_searchpath($query,$param);
            if ($result === false){
                $Log->trace("END   InsTrn3050");
                return false;
            }
            return true;
        }
        
        /**
         * 他テーブル使用状況チェック処理
         * @param   $organization_id    組織ID
         * @param   $bundle_cd          バンドルコード
         * @return boolean
         */
        function UsedTableCheck($organization_id, $bundle_cd) {

            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START UsedTableCheck");

            $searchArray = array(
                ':organization_id'      => $organization_id,
                ':bundle_cd'            => $bundle_cd
            );

            $sqlArray = array(
                'SELECT COUNT(hideseq) AS CNT FROM trn0102 WHERE organization_id = :organization_id AND bundle_cd = :bundle_cd',    // 売上明細
                'SELECT COUNT(hideseq) AS CNT FROM jsk1160 WHERE organization_id = :organization_id AND bundle_cd = :bundle_cd',    // バンドル実績日別
            );

            for ($intL = 0; $intL < Count($sqlArray); $intL ++) {
                // SQLの実行
                $result = $DBA->executeSQL($sqlArray[$intL], $searchArray);
                if ($result === false) {
                    $Log->trace("END UsedTableCheck");
                    return false;
                }
                $numRet = 0;
                while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                    $numRet = intval($data['cnt']);
                }
                if ($numRet > 0) {
                    $Log->trace("END UsedTableCheck");
                    return false;
                }
            }

            $Log->trace("END UsedTableCheck");
            return true;
        }

        /**
         * 商品ミックスマッチ構成情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        public function searchMst5201Data()
        {
            global $DBA, $Log; // searchMst5301Data変数宣言
            $Log->trace("START searchMst5201Data");

//            $sql = ' SELECT type_cd,type_nm,type_kn from mst5301 where 1 = :type_cd order by type_cd desc';
            $sql  =  "";
            $sql = "SELECT "
                    . "  mst5201.organization_id"
                    . ", mst5201.mixmatch_cd"
                    . ", mst5201.expira_str"
                    . ", mst5201.expira_end"
                    . ", mst5202.prod_cd"
                    . " from mst5201"
                    . " inner join mst5202"
                    . " on (mst5202.organization_id = mst5201.organization_id and mst5202.mixmatch_cd = mst5201.mixmatch_cd)"
                    . " where 1 = :mixmatch_cd";
            $searchArray = array(':mixmatch_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst5201DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END searchMst5201Data");
                return $mst5201DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst5201DataList, $data);
            }

            $Log->trace("END searchMst5201Data");

            return $mst5201DataList;
        }
        
        public function get_bundle_cd($aryKeys)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_bundle_cd");
            $cnt = "0";
            // バンドルコード取得
            $sql = "";
            $sql .= " SELECT count(*) cnt FROM mst5301 ";
            $sql .= " WHERE organization_id = :organization_id AND bundle_cd = :bundle_cd";
            $searchArray = array();
            $searchArray[":organization_id"] = $aryKeys["organization_id"];
            $searchArray[":bundle_cd"] = $aryKeys["bundle_cd"];
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            // データ取得ができなかった場合、エラーを返す
            if( $result === false )
            {
                // SQL実行エラー
                $Log->trace("END   get_bundle_cd");
                return $cnt;
            }
            
            // 取得したデータを格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $cnt = $data['cnt'];
            }

            $Log->trace("END   get_bundle_cd");
            return $cnt;
        }
    }
?>