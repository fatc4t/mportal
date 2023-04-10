<?php
    /**
     * @file      商品ミックスマッチ構成情報
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      商品ミックスマッチ構成情報テーブルの管理を行う
     */

    // BaseProducts.phpを読み込む
    require './Model/BaseProduct.php';

    /**
     * 商品ミックスマッチ構成情報クラス
     * @note   商品ミックスマッチ構成情報テーブルの管理を行う。
     */
    class Mst5201 extends BaseProduct
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
         * 商品ミックスマッチ構成情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        public function searchMst5201Data()
        {
            global $DBA, $Log; // searchMst5201Data変数宣言
            $Log->trace("START searchMst5201Data");
            $sql = "SELECT "
                    . "  mst5201.organization_id "
                    . ", mst5201.mixmatch_cd "
                    . ", mst5201.expira_str "
                    . ", mst5201.expira_end "
                    . ", TO_CHAR(mst5201.unit_amoun1, 'FM999,999,990')  AS unit_amoun1 "
                    . ", TO_CHAR(mst5201.unit_money1, 'FM999,999,990')  AS unit_money1 "
                    . ", TO_CHAR(mst5201.unit_amoun2, 'FM999,999,990')  AS unit_amoun2 "
                    . ", TO_CHAR(mst5201.unit_money2, 'FM999,999,990')  AS unit_money2 "
                    . ", TO_CHAR(mst5201.unit_amoun3, 'FM999,999,990')  AS unit_amoun3 "
                    . ", TO_CHAR(mst5201.unit_money3, 'FM999,999,990')  AS unit_money3 "
                    . ", string_agg(mst5202.prod_cd, ',' ORDER BY mst5202.prod_cd)  AS prod_cd "
                    . " from mst5201 "
                    . " INNER JOIN mst5202 "
                    . " ON (mst5202.organization_id = mst5201.organization_id AND mst5202.mixmatch_cd = mst5201.mixmatch_cd) "
                    . " INNER JOIN mst0201 "
                    . " ON (mst0201.organization_id = mst5202.organization_id AND mst0201.prod_cd = mst5202.prod_cd) "
                    . " where 1 = :mixmatch_cd "
                    . " GROUP BY "
                    . "  mst5201.organization_id "
                    . ", mst5201.mixmatch_cd "
                    . ", expira_str "
                    . ", expira_end "
                    . ", unit_amoun1 "
                    . ", unit_money1 "
                    . ", unit_amoun2 "
                    . ", unit_money2 "
                    . ", unit_amoun3 "
                    . ", unit_money3 ";
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

        /**
         * 商品ミックスマッチ構成情報データ取得
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
            public function getMst5201List()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst5201List");

//            $sql = " SELECT string_agg(type_cd,',') as list from mst5201 where 1 = :type_cd ";            
            $sql = " SELECT string_agg(organization_id||':'||mixmatch_cd,',') as list from mst5201 where 1 = :mixmatch_cd ";            
            $searchArray = array(':mixmatch_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst5201DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst5201List");
                return $mst5201DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst5201DataList = $data;
            }

            $Log->trace("END getMst5201List");

            return $mst5201DataList;
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
         * 商品ミックスマッチ構成情報データ取得
         * @return   SQLの実行結果
         */
        //public function get_mst5201_data()
        public function get_mst5201_data( $aryKeys )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst5201_data");
            
            if (count($aryKeys) === 0) {
                $Log->trace("END get_mst5201_data");
                return array();
            }
            $sql  = " SELECT";
            $sql .= "   mst5201.organization_id                                 AS organization_id";
            $sql .= "  ,mst5201.mixmatch_cd                                     AS mixmatch_cd";
            $sql .= "  ,mst5201.expira_str                                      AS expira_str";
            $sql .= "  ,mst5201.expira_end                                      AS expira_end";
            $sql .= "  ,TO_CHAR(mst5201.unit_amoun1, 'FM999,999,990')           AS unit_amoun1";
            $sql .= "  ,TO_CHAR(mst5201.unit_money1, 'FM999,999,990')           AS unit_money1";
            $sql .= "  ,TO_CHAR(mst5201.unit_amoun2, 'FM999,999,990')           AS unit_amoun2";
            $sql .= "  ,TO_CHAR(mst5201.unit_money2, 'FM999,999,990')           AS unit_money2";
            $sql .= "  ,TO_CHAR(mst5201.unit_amoun3, 'FM999,999,990')           AS unit_amoun3";
            $sql .= "  ,TO_CHAR(mst5201.unit_money3, 'FM999,999,990')           AS unit_money3";
            $sql .= "  ,mst5202.prod_cd                                         AS prod_cd";
            $sql .= "  ,mst0201.prod_nm                                         AS prod_nm";
            $sql .= " FROM mst5201";
            $sql .= " INNER JOIN mst5202";
            $sql .= " ON (mst5202.organization_id = mst5201.organization_id AND mst5202.mixmatch_cd = mst5201.mixmatch_cd)";
            $sql .= " INNER JOIN mst0201";
            $sql .= " ON (mst0201.organization_id = mst5202.organization_id AND mst0201.prod_cd = mst5202.prod_cd)";
//            $sql .= '  WHERE 1 = :type  ';
            $sql .= '  WHERE mst5201.organization_id = :organization_id  ';
            $sql .= '  AND   mst5201.mixmatch_cd     = :mixmatch_cd';

//            $searchArray = array( ':type' => 1 );                
            $searchArray = array(
                ':organization_id'  => $aryKeys['organization_id'],
                ':mixmatch_cd'      => $aryKeys["mixmatch_cd"]
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst5201DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst5201_data");
                return $mst5201DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst5201DataList, $data);
            }

            $Log->trace("END get_mst5201_data");

            return $mst5201DataList;
        }
        
        /**
         * 商品ミックスマッチ構成マスタデータ登録
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
                //$mst5201->Delete($del_data);
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
                //$mst5201->Insert($new_data);
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
                //$mst5201->Update($upd_data);
                if ($this->Update($aryKeys, $upd_data, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
                }
            }

            if (isset($del_data) === false && isset($new_data) === false && isset($upd_data) === false){
                // ヘッダ情報のみ更新
                if ($this->UpdateMst5201($aryKeys, $aryErrMsg) === false){
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
         * 商品ミックスマッチ構成マスタ新規データ登録
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
            //if(count($dataList) != 0){
                foreach ($dataList as $orglist){
//ADDEND kanderu              
           // if($this->IsExistMst5201($aryKeys) === false){
            $query  = "";
            //$query .= "insert into mst5201 (";
            $query .= "insert into ". $_SESSION["SCHEMA"].".mst5201 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,MIXMATCH_CD       ";
            $query .= "    ,EXPIRA_STR        ";
            $query .= "    ,EXPIRA_END        ";
            $query .= "    ,UNIT_AMOUN1       ";
            $query .= "    ,UNIT_MONEY1       ";
            $query .= "    ,UNIT_AMOUN2       ";
            $query .= "    ,UNIT_MONEY2       ";
            $query .= "    ,UNIT_AMOUN3       ";
            $query .= "    ,UNIT_MONEY3       ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:MIXMATCH_CD      ";
            $query .= "    ,:EXPIRA_STR       ";
            $query .= "    ,:EXPIRA_END       ";
            $query .= "    ,:UNIT_AMOUN1      ";
            $query .= "    ,:UNIT_MONEY1      ";
            $query .= "    ,:UNIT_AMOUN2      ";
            $query .= "    ,:UNIT_MONEY2      ";
            $query .= "    ,:UNIT_AMOUN3      ";
            $query .= "    ,:UNIT_MONEY3      ";
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
            $param[":MIXMATCH_CD"]      = $aryKeys["mixmatch_cd"];
            $param[":EXPIRA_STR"]       = str_replace('/', '', $aryKeys["expira_str"]);
            $param[":EXPIRA_END"]       = str_replace('/', '', $aryKeys["expira_end"]);
            $param[":UNIT_AMOUN1"]      = str_replace(',', '', $aryKeys["unit_amoun1"]);
            $param[":UNIT_MONEY1"]      = str_replace(',', '', $aryKeys["unit_money1"]);
            $param[":UNIT_AMOUN2"]      = str_replace(',', '', $aryKeys["unit_amoun2"]);
            $param[":UNIT_MONEY2"]      = str_replace(',', '', $aryKeys["unit_money2"]);
            $param[":UNIT_AMOUN3"]      = str_replace(',', '', $aryKeys["unit_amoun3"]);
            $param[":UNIT_MONEY3"]      = str_replace(',', '', $aryKeys["unit_money3"]);

           // $result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                $aryErrMsg[] = '追加処理中にエラーが発生しました。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].')';
                $Log->trace("END   Insert");
                return false;
            }
            $query  = "";
            //$query .= "insert into mst5202 (";
            $query .= "insert into ". $_SESSION["SCHEMA"].".mst5202 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,MIXMATCH_CD        ";
            $query .= "    ,PROD_CD           ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:MIXMATCH_CD      ";
            $query .= "    ,:PROD_CD          ";
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
                $param[":MIXMATCH_CD"]      = $aryKeys["mixmatch_cd"];
                $param[":PROD_CD"]          = $data[$sind]["prod_cd"];

                //$result = $DBA->executeSQL($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                if ($result === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].'/商品コード'.$data[$sind]["prod_cd"].')';
                    $Log->trace("END   Insert");
                    return false;
                }   
            }
         }
            $Log->trace("END   Insert");
            return true;
        }
        
        /**
         * 商品ミックスマッチ構成マスタ既存データ更新
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
            //$query .= " update mst5201 set  ";
            $query .=  " update ". $_SESSION["SCHEMA"].".mst5201 set  "; 
            $query .= "  UPDUSER_CD             = :UPDUSER_CD        , ";
            $query .= "  UPDDATETIME            = :UPDDATETIME       , ";
            $query .= "  DISABLED               = :DISABLED          , ";
            $query .= "  LAN_KBN                = :LAN_KBN           , ";
            $query .= "  CONNECT_KBN            = :CONNECT_KBN       , ";
            $query .= "  EXPIRA_STR             = :EXPIRA_STR        , ";
            $query .= "  EXPIRA_END             = :EXPIRA_END        , ";
            $query .= "  UNIT_AMOUN1            = :UNIT_AMOUN1       , ";
            $query .= "  UNIT_MONEY1            = :UNIT_MONEY1       , ";
            $query .= "  UNIT_AMOUN2            = :UNIT_AMOUN2       , ";
            $query .= "  UNIT_MONEY2            = :UNIT_MONEY2       , ";
            $query .= "  UNIT_AMOUN3            = :UNIT_AMOUN3       , ";
            $query .= "  UNIT_MONEY3            = :UNIT_MONEY3         ";
            $query .= " where ORGANIZATION_ID   = :organization_id     ";
            $query .= " and   MIXMATCH_CD       = :mixmatch_cd         ";

            $param = array();
            $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":UPDDATETIME"]      = "now()";
            $param[":DISABLED"]         = '0';
            $param[":LAN_KBN"]          = '0';
            $param[":CONNECT_KBN"]      = '0';
            $param[":EXPIRA_STR"]       = $aryKeys["expira_str"];
            $param[":EXPIRA_END"]       = $aryKeys["expira_end"];
            $param[":UNIT_AMOUN1"]      = $aryKeys["unit_amoun1"];
            $param[":UNIT_MONEY1"]      = $aryKeys["unit_money1"];
            $param[":UNIT_AMOUN2"]      = $aryKeys["unit_amoun2"];
            $param[":UNIT_MONEY2"]      = $aryKeys["unit_money2"];
            $param[":UNIT_AMOUN3"]      = $aryKeys["unit_amoun3"];
            $param[":UNIT_MONEY3"]      = $aryKeys["unit_money3"];
            $param[":organization_id"]  = $aryKeys["organization_id"];
            $param[":mixmatch_cd"]      = $aryKeys["mixmatch_cd"];

            //$result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].')';
                $Log->trace("END   Update");
                return false;
            }

            // $data配列には主キーと変更項目の2要素が1行ごとに時系列で格納されている
            // 同じ変更項目が格納されることもあるが後優先である
            for ($intL = 0; $intL < count($data); $intL ++){
                //
                $new_prod_cd = '';
                $query  = "";
                //$query .= " upte mst5202 set  ";
                $query .=  " update ". $_SESSION["SCHEMA"].".mst5202 set  "; 
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
                $param[":organization_id"]  = $aryKeys["organization_id"];
                $param[":mixmatch_cd"]      = $aryKeys["mixmatch_cd"];

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
                                if ($this->UsedTableCheck($aryKeys["organization_id"], $aryKeys["mixmatch_cd"]) === false){
                                    $aryErrMsg[] = '他のマスタで使用されているためコード変更できません。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].')';
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
                    $query .= " where ORGANIZATION_ID   = :organization_id      ";
                    $query .= " and   mixmatch_cd       = :mixmatch_cd          ";
                    $query .= " and   prod_cd           = :updkey_prod_cd       ";

                    $result = $DBA->executeSQL($query, $param);
                    if ($result === false){
                        $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].'/商品コード'.$updkey_cd.')';
                        $Log->trace("END   Update");
                        return false;
                    }

                    // 商品マスタ　セット商品区分更新
                    if ($new_prod_cd !== ''){
                        // 商品ミックスマッチ構成マスタ存在チェック
                        if ($this->MixtureIsExist($aryKeys["organization_id"], $updkey_cd, "") === false){
                            // 商品マスタ　ミックスマッチ区分更新
                            if ($this->MixtureKbnUpd($aryKeys["organization_id"], $updkey_cd, "0") === false){
                                $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].'/商品コード'.$updkey_cd.')';
                                $Log->trace("END   Update");
                                return false;
                            }
                        }
                        // 商品マスタ　ミックスマッチ区分更新
                        if ($this->MixtureKbnUpd($aryKeys["organization_id"], $new_prod_cd, "1") === false){
                            $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].'/商品コード'.$updkey_cd.')';
                            $Log->trace("END   Update");
                            return false;
                        }
                        
                    }
                    else{ 
                    }
                }
            }

            $Log->trace("END   Update");
            return true;
        }

        
        /**
         * 商品ミックスマッチ構成マスタ既存データ削除
         */
        //public function Delete($data){
        public function Delete($aryKeys, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Delete");
            
            // トランザクション開始
            $DBA->beginTransaction();

            // 商品ミックスマッチ構成マスタ取得
            $mst5201data = $this->get_mst5201_data($aryKeys);
            
            $param = array();
           // $param[":organization_id"]  = $aryKeys["organization_id"];
            $param[":mixmatch_cd"]      = $aryKeys["mixmatch_cd"];

            // 他テーブル使用状況チェック
            if ($this->UsedTableCheck($aryKeys["mixmatch_cd"]) === false){
                $aryErrMsg[] = '他のマスタで使用されているため削除できません。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].')';
                return false;
            }

            // 商品ミックスマッチ構成マスタ削除
            $query  = "";
           // $query .= " delete from mst5201 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].". mst5201 ";
            //$query .= " where organization_id = :organization_id";
            $query .= " where   mixmatch_cd     = :mixmatch_cd";

            //$result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].')';
                $Log->trace("END   Delete");
                return false;
            }

            $query  = "";
            //$query .= " delete from mst5202 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].". mst5202 ";
            //$query .= " where organization_id = :organization_id";
            $query .= " where   mixmatch_cd     = :mixmatch_cd";
            $query .= " and   prod_cd         = :prod_cd";

            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['prod_cd']) === false || is_numeric($aryKeys['mixmatch_cd']) === false){
                    continue;
                }
                $param = array();
                //$param[":organization_id"]  = $aryKeys["organization_id"];
                $param[":mixmatch_cd"]      = $aryKeys["mixmatch_cd"];
                $param[":prod_cd"]          = $data[$sind]['prod_cd'];

                //$result = $DBA->executeSQL($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                if ($result === false){
                    $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].'/商品コード'.$data[$sind]["prod_cd"].')';
                    $Log->trace("END   Delete");
                    return false;
                }   
            }
            $Log->trace("END   Delete");
            return true;
        }        
        /**
         * ミックスマッチマスタ更新処理
         */
        public function UpdateMst5201($aryKeys, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START UpdateMst5201");

            //
            $query  = "";
            //$query .= " update mst5201 set  ";
            $query .=  " update ". $_SESSION["SCHEMA"].".mst5201 set  "; 
            $query .= "  UPDUSER_CD             = :UPDUSER_CD        , ";
            $query .= "  UPDDATETIME            = :UPDDATETIME       , ";
            $query .= "  DISABLED               = :DISABLED          , ";
            $query .= "  LAN_KBN                = :LAN_KBN           , ";
            $query .= "  CONNECT_KBN            = :CONNECT_KBN       , ";
            $query .= "  EXPIRA_STR             = :EXPIRA_STR        , ";
            $query .= "  EXPIRA_END             = :EXPIRA_END        , ";
            $query .= "  UNIT_AMOUN1            = :UNIT_AMOUN1       , ";
            $query .= "  UNIT_MONEY1            = :UNIT_MONEY1       , ";
            $query .= "  UNIT_AMOUN2            = :UNIT_AMOUN2       , ";
            $query .= "  UNIT_MONEY2            = :UNIT_MONEY2       , ";
            $query .= "  UNIT_AMOUN3            = :UNIT_AMOUN3       , ";
            $query .= "  UNIT_MONEY3            = :UNIT_MONEY3         ";
            $query .= " where ORGANIZATION_ID   = :organization_id     ";
            $query .= " and   MIXMATCH_CD       = :mixmatch_cd         ";

            $param = array();
            $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":UPDDATETIME"]      = "now()";
            $param[":DISABLED"]         = '0';
            $param[":LAN_KBN"]          = '0';
            $param[":CONNECT_KBN"]      = '0';
            $param[":EXPIRA_STR"]       = $aryKeys["expira_str"];
            $param[":EXPIRA_END"]       = $aryKeys["expira_end"];
            $param[":UNIT_AMOUN1"]      = $aryKeys["unit_amoun1"];
            $param[":UNIT_MONEY1"]      = $aryKeys["unit_money1"];
            $param[":UNIT_AMOUN2"]      = $aryKeys["unit_amoun2"];
            $param[":UNIT_MONEY2"]      = $aryKeys["unit_money2"];
            $param[":UNIT_AMOUN3"]      = $aryKeys["unit_amoun3"];
            $param[":UNIT_MONEY3"]      = $aryKeys["unit_money3"];
            $param[":organization_id"]  = $aryKeys["organization_id"];
            $param[":mixmatch_cd"]      = $aryKeys["mixmatch_cd"];

            //$result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].')';
                $Log->trace("END   UpdateMst5201");
                return false;
            }

            $Log->trace("END   UpdateMst5201");
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
            //$sql .= ", TO_CHAR(saleprice,       'FM999,999,990')    AS saleprice";
            //$sql .= ", TO_CHAR(head_costprice,  'FM999,999,990.00') AS costprice";
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
         * 商品ミックスマッチ構成マスタ存在チェック
         */
        function MixtureIsExist($organization_id, $mixmatch_cd, $prod_cd)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START MixtureIsExist");

            $sql  = " SELECT count(*) AS cnt";
            $sql .= " from mst5202";
            $sql .= " WHERE organization_id = :organization_id";
            $sql .= " AND   mixmatch_cd     = :mixmatch_cd";
            $sql .= " AND   prod_cd         = :prod_cd";

            $searchArray = array(
                ':organization_id'  => $organization_id,
                ':mixmatch_cd'      => $mixmatch_cd,
                ':prod_cd'          => $prod_cd);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $aryResult = array();

            // データ取得ができなかった場合、falseを返す
            if( $result === false )
            {
                $Log->trace("END MixtureIsExist");
                return false;
            }

            $numRet = 0;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                $numRet = intval($data['cnt']);
            }
            if ($numRet > 0) {
                $Log->trace("END MixtureIsExist");
                return true;
            }

            $Log->trace("END MixtureIsExist");

            return false;
        }
        
        function AllDelete($aryKeys, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START AllDelete");
            
            // トランザクション開始
            $DBA->beginTransaction();

            // 商品ミックスマッチ構成マスタ取得
            $mst5201data = $this->get_mst5201_data($aryKeys);
            
            $param = array();
           // $param[":organization_id"]  = $aryKeys["organization_id"];
            $param[":mixmatch_cd"]      = $aryKeys["mixmatch_cd"];

            // 他テーブル使用状況チェック
            if ($this->UsedTableCheck($aryKeys["mixmatch_cd"]) === false){
                $aryErrMsg[] = '他のマスタで使用されているため削除できません。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].')';
                return false;
            }

            // 商品ミックスマッチ構成マスタ削除
            $query  = "";
           // $query .= " delete from mst5201 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].". mst5201 ";
            //$query .= " where organization_id = :organization_id";
            $query .= " where   mixmatch_cd     = :mixmatch_cd";

            //$result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].')';
                $Log->trace("END   AllDelete");
                return false;
            }

            $query  = "";
            //$query .= " delete from mst5202 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].". mst5202 ";
           // $query .= " where organization_id = :organization_id";
            $query .= " where   mixmatch_cd     = :mixmatch_cd";
            //$result = $DBA->executeSQL($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].')';
                $Log->trace("END   AllDelete");
                return false;
            }
            

            for ($i = 0; $i < count($mst5201data); $i ++)
            {
                // 商品ミックスマッチ構成マスタ存在チェック
                if ($this->MixtureIsExist($mst5201data[$i]["mixmatch_cd"], $mst5201data[$i]["prod_cd"]) === false){
                    // 商品マスタ　セット商品区分更新
                    if ($this->MixtureKbnUpd($mst5201data[$i]["prod_cd"], "0") === false){
                        // ロールバック
                        $DBA->rollBack();
                        $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品ミックスマッチコード：'.$aryKeys["mixmatch_cd"].')';
                        $Log->trace("END   AllDelete");
                        return false;
                    }
                }
            }
            // コミット
            $DBA->commit();
            $Log->trace("END   AllDelete");
            return true;
        }
        
        function MixtureKbnUpd($organization_id, $prod_cd, $mixture_kbn)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START MixtureKbnUpd");
            
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
           // $result = $DBA->executeSQL($sql, $searchArray);
           $result = $DBA->executeSQL_no_searchpath($sql, $searchArray);
            // 一覧表を格納する空の配列宣言
            $mst0201data = array();

            // データ取得ができなかった場合、falseを返す
            if( $result === false )
            {
                $Log->trace("END MixtureKbnUpd");
                return;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                $mst0201data = $data;
            }
            if ($mst0201data["set_prod_kbn"] !== $mixture_kbn){
                // 商品マスタ　セット商品区分更新
                $query  = "";
                //$query .= " UPDATE mst0201 SET";
                $query .=  " update ". $_SESSION["SCHEMA"].".mst0201 set  "; 
                $query .= "   UPDUSER_CD          = :UPDUSER_CD";
                $query .= "  ,UPDDATETIME         = :UPDDATETIME";
                $query .= "  ,DISABLED            = :DISABLED";
                $query .= "  ,LAN_KBN             = :LAN_KBN";
                $query .= "  ,CONNECT_KBN         = :CONNECT_KBN";
                $query .= "  ,MIXTURE_KBN         = :MIXTURE_KBN";
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
                $param[":MIXTURE_KBN"]          = $mixture_kbn;

               // $result = $DBA->executeSQL($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                if ($result === false){
                    $Log->trace("END   MixtureKbnUpd");
                    return false;
                } 
            }
            
            $Log->trace("END MixtureKbnUpd");
            return true;
        }
        function InsTrn3040($organization_id, $mixture_kbn, $prod_cd, $upd_type, $IN_drwRow = array())
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START InsTrn3050");

            // 商品ミックスマッチ情報を取得する
            if (count($IN_drwRow) ===0){
                $sql  = " SELECT";
                $sql .= "   mst5201.organization_id     AS organization_id";
                $sql .= "  ,mst5201.mixmatch_cd         AS mixmatch_cd";
                $sql .= "  ,mst5201.expira_str          AS expira_str";
                $sql .= "  ,mst5201.expira_end          AS expira_end";
                $sql .= "  ,mst5201.unit_amoun1         AS unit_amoun1";
                $sql .= "  ,mst5201.unit_money1         AS unit_money1";
                $sql .= "  ,mst5201.unit_amoun2         AS unit_amoun2";
                $sql .= "  ,mst5201.unit_money2         AS unit_money2";
                $sql .= "  ,mst5201.unit_amoun3         AS unit_amoun3";
                $sql .= "  ,mst5201.unit_money3         AS unit_money3";
                $sql .= "  ,mst5202.prod_cd             AS prod_cd";
                $sql .= " FROM mst5201";
                $sql .= " INNER JOIN mst5202";
                $sql .= " ON (mst5202.organization_id = mst5201.organization_id AND mst5202.mixmatch_cd = mst5201.mixmatch_cd)";
                $sql .= '  WHERE mst5201.organization_id = :organization_id  ';
                $sql .= '  AND   mst5201.mixmatch_cd     = :mixmatch_cd';
                $sql .= '  AND   mst5202.prod_cd         = :prod_cd';
                $searchArray = array(
                    ':organization_id'  => $organization_id,
                    ':mixmatch_cd'      => $mixmatch_cd,
                    ':prod_cd'          => $prod_cd,
                );
                // SQLの実行
                $result = $DBA->executeSQL($sql, $searchArray);

                // 一覧表を格納する空の配列宣言
                $mst5201data = array();

                // データ取得ができなかった場合、falseを返す
                if( $result === false )
                {
                    $Log->trace("END InsTrn3050");
                    return;
                }

                while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                    $mst5201data = $data;
                }
            }
            else{
                $mst5201data = $IN_drwRow;
            }
            if (count($mst5201data) ===0){
                $Log->trace("END InsTrn3050");
                return;
            }
            // 商品ミックスマッチ更新記録(TRN3040)
            $query  = "";
            //$query .= "insert into trn3040 (";
            $query .= "insert into ". $_SESSION["SCHEMA"].".trn3040 (";
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
            $query .= "    ,MIXMATCH_CD       ";
            $query .= "    ,PROD_CD           ";
            $query .= "    ,EXPIRA_STR        ";
            $query .= "    ,EXPIRA_END        ";
            $query .= "    ,UNIT_AMOUN1       ";
            $query .= "    ,UNIT_MONEY1       ";
            $query .= "    ,UNIT_AMOUN2       ";
            $query .= "    ,UNIT_MONEY2       ";
            $query .= "    ,UNIT_AMOUN3       ";
            $query .= "    ,UNIT_MONEY3       ";
            $query .= "    ,MIXMATCH_UPDTIME  ";
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
            $query .= "    ,:MIXMATCH_CD      ";
            $query .= "    ,:PROD_CD          ";
            $query .= "    ,:EXPIRA_STR       ";
            $query .= "    ,:EXPIRA_END       ";
            $query .= "    ,:UNIT_AMOUN1      ";
            $query .= "    ,:UNIT_MONEY1      ";
            $query .= "    ,:UNIT_AMOUN2      ";
            $query .= "    ,:UNIT_MONEY2      ";
            $query .= "    ,:UNIT_AMOUN3      ";
            $query .= "    ,:UNIT_MONEY3      ";
            $query .= "    ,:MIXMATCH_UPDTIME ";
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
            $param[":ORGANIZATION_ID"]  = $mst5201data["organization_id"];
            $param[":HIDESEQ"]          = 0;
            $param[":PROC_DATE"]        = date('Ymd');
            $param[":UPD_TYPE"]         = $upd_type;
            $param[":MIXMATCH_CD"]      = $mst5201data["mixmatch_cd"];
            $param[":PROD_CD"]          = $mst5201data["prod_cd"];
            $param[":EXPIRA_STR"]       = $mst5201data["expira_str"];
            $param[":EXPIRA_END"]       = $mst5201data["expira_end"];
            $param[":UNIT_AMOUN1"]      = str_replace(',', '', $mst5201data["unit_amoun1"]);
            $param[":UNIT_MONEY1"]      = str_replace(',', '', $mst5201data["unit_money1"]);
            $param[":UNIT_AMOUN2"]      = str_replace(',', '', $mst5201data["unit_amoun2"]);
            $param[":UNIT_MONEY2"]      = str_replace(',', '', $mst5201data["unit_money2"]);
            $param[":UNIT_AMOUN3"]      = str_replace(',', '', $mst5201data["unit_amoun3"]);
            $param[":UNIT_MONEY3"]      = str_replace(',', '', $mst5201data["unit_money3"]);
            $param[":MIXMATCH_UPDTIME"] = date('His');
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
        
        /**
         * 他テーブル使用状況チェック処理
         * @param   $organization_id    組織ID
         * @param   $mixmatch_cd        ミックスマッチコード
         * @return boolean
         */
        function UsedTableCheck($organization_id, $mixmatch_cd) {

            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START UsedTableCheck");

            $searchArray = array(
                ':organization_id'      => $organization_id,
                ':mixmatch_cd'          => $mixmatch_cd
            );

            $sqlArray = array(
                'SELECT COUNT(hideseq) AS CNT FROM trn0102 WHERE organization_id = :organization_id AND mixmatch_cd = :mixmatch_cd',    // 売上明細
                'SELECT COUNT(hideseq) AS CNT FROM jsk1150 WHERE organization_id = :organization_id AND mixmatch_cd = :mixmatch_cd',    // ミックスマッチ実績日別
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
         * 商品バンドル構成情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        public function searchMst5301Data()
        {
            global $DBA, $Log; // searchMst5201Data変数宣言
            $Log->trace("START searchMst5301Data");

//            $sql = ' SELECT type_cd,type_nm,type_kn from mst5201 where 1 = :type_cd order by type_cd desc';
            $sql  =  "";
            $sql = "SELECT "
                    . "  mst5301.organization_id"
                    . ", mst5301.bundle_cd"
                    . ", mst5301.expira_str"
                    . ", mst5301.expira_end"
                    . ", mst5302.prod_cd"
                    . " from mst5301"
                    . " inner join mst5302"
                    . " on (mst5302.organization_id = mst5301.organization_id and mst5302.bundle_cd = mst5301.bundle_cd)"
                    . " where 1 = :bundle_cd";
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
         * 商品ミックスマッチ構成情報データ取得
         * @return   SQLの実行結果
         */
        //public function get_mst5201_data()
        public function IsExistMst5201( $aryKeys )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START IsExistMst5201");
            
            if (count($aryKeys) === 0) {
                $Log->trace("END IsExistMst5201");
                return false;
            }
            $sql  = " SELECT count(mixmatch_cd) AS cnt";
            $sql .= " FROM mst5201";
            $sql .= '  WHERE organization_id = :organization_id  ';
            $sql .= '  AND   mixmatch_cd     = :mixmatch_cd';

//            $searchArray = array( ':type' => 1 );                
            $searchArray = array(
                ':organization_id'  => $aryKeys['organization_id'],
                ':mixmatch_cd'      => $aryKeys["mixmatch_cd"]
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            if ($result === false) {
                $Log->trace("END IsExistMst5201");
                return false;
            }
            $numRet = 0;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                $numRet = intval($data['cnt']);
            }
            if ($numRet === 0) {
                $Log->trace("END IsExistMst5201");
                return false;
            }

            $Log->trace("END IsExistMst5201");
            return true;
        }
    }
?>