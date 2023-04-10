<?php
    /**
     * @file      期間原価構成情報
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      期間原価構成情報テーブルの管理を行う
     */

    // BaseProducts.phpを読み込む
    require './Model/BaseProduct.php';

    /**
     * 期間原価構成情報クラス
     * @note   期間原価構成情報テーブルの管理を行う。
     */
    class Mst1401 extends BaseProduct
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
         * 期間原価構成情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        //public function searchMst1401Data()
        public function searchMst1401Data($organization_id)
        {
            global $DBA, $Log; // searchMst1401Data変数宣言
            $Log->trace("START searchMst1401Data");
            $sql = "SELECT "
                    . "  mst1401.peri_cost_cd "
                    . ", mst1401.prod_cd "
                    . ", mst1401.supp_cd "
                    . ", mst1401.order_date_str "
                    . ", mst1401.order_date_end "
                    . ", TO_CHAR(mst1401.peri_costprice,    'FM999,999,990.00') AS peri_costprice "
                    . ", TO_CHAR(mst1401.order_lot,         'FM999,999,990')    AS order_lot "
                    . ", mst1401.prod_cd                                        AS prod_cd_bak "
                    . " FROM mst1401 "
                    . " WHERE ORGANIZATION_ID = :organization_id "
                    . " ORDER BY "
                    . "  mst1401.supp_cd "
                    . " ,mst1401.prod_cd "
                    . " ,mst1401.order_date_str "
                    . " ,mst1401.order_date_end "
                    . " ,mst1401.peri_costprice";
            $searchArray = array(':organization_id' => $organization_id);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1401DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END searchMst1401Data");
                return $mst1401DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst1401DataList, $data);
            }

            $Log->trace("END searchMst1401Data");

            return $mst1401DataList;
        }

        /**
         * 期間原価構成情報データ取得
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
            public function getMst1401List()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst1401List");        
            $sql = " SELECT string_agg(organization_id||':'||bundle_cd,',') as list from mst1401 where 1 = :bundle_cd ";            
            $searchArray = array(':bundle_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1401DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst1401List");
                return $mst1401DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst1401DataList = $data;
            }

            $Log->trace("END getMst1401List");

            return $mst1401DataList;
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
            $sql .= '  WHERE organization_id = :organization_id  ';
            $sql .= '  AND   prod_cd         = :prod_cd';             
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
         * 期間原価構成情報データ取得
         * @return   SQLの実行結果
         */
        //public function get_mst1401_data()
        public function get_mst1401_data( $aryKeys )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst1401_data");
            
            if (count($aryKeys) === 0) {
                $Log->trace("END get_mst1401_data");
                return array();
            }
            $sql  = " SELECT";
            $sql .= "   mst1401.peri_cost_cd                                    AS peri_cost_cd";
            $sql .= "  ,mst1401.prod_cd                                         AS prod_cd";
            $sql .= "  ,mst1401.supp_cd                                         AS supp_cd";
            $sql .= "  ,mst1401.order_date_str                                  AS order_date_str";
            $sql .= "  ,mst1401.order_date_end                                  AS order_date_end";
            $sql .= "  ,TO_CHAR(mst1401.peri_costprice,     'FM999,999,990.00') AS peri_costprice";
            $sql .= "  ,TO_CHAR(mst1401.order_lot,          'FM999,999,990')    AS order_lot";
            $sql .= "  ,mst1401.prod_cd                                         AS prod_cd_bak";
            $sql .= "  ,mst0201.prod_nm                                         AS prod_nm";
            $sql .= " FROM mst1401";
            $sql .= " LEFT JOIN mst0201";
            $sql .= " ON (mst0201.organization_id = mst1401.organization_id AND mst0201.prod_cd = mst1401.prod_cd)";
            $sql .= '  WHERE mst1401.organization_id = :organization_id  ';
            $sql .= '  AND   mst1401.supp_cd         = :supp_cd';
            $sql .= "  ORDER BY";
            $sql .= "   mst1401.prod_cd";
            $sql .= "  ,mst1401.order_date_str";
            $sql .= "  ,mst1401.order_date_end";
            $sql .= "  ,mst1401.peri_costprice";              
            $searchArray = array(
                ':organization_id'  => $aryKeys['organization_id'],
                ':supp_cd'          => $aryKeys["supp_cd"]
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            //$result = $DBA->executeSQL_no_searchpath($sql, $searchArray);
            // 一覧表を格納する空の配列宣言
            $mst1401DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst1401_data");
                return $mst1401DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst1401DataList, $data);
            }

            $Log->trace("END get_mst1401_data");

            return $mst1401DataList;
        }
        
        /**
         * 期間原価マスタデータ登録
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
                //$mst1401->Delete($del_data);
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
                //$mst1401->Insert($new_data);
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
                //$mst1401->Update($upd_data);
                if ($this->Update($aryKeys, $upd_data, $aryErrMsg) === false){
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
         * 期間原価マスタ新規データ登録
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
            $query  = "";
            //$query .= "insert into mst1401 (";
            $query .= "insert into ". $_SESSION["SCHEMA"].".mst1401 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,PERI_COST_CD      ";
            $query .= "    ,PROD_CD           ";
            $query .= "    ,SUPP_CD           ";
            $query .= "    ,ORDER_DATE_STR    ";
            $query .= "    ,ORDER_DATE_END    ";
            $query .= "    ,PERI_COSTPRICE    ";
            $query .= "    ,ORDER_LOT         ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:PERI_COST_CD     ";
            $query .= "    ,:PROD_CD          ";
            $query .= "    ,:SUPP_CD          ";
            $query .= "    ,:ORDER_DATE_STR   ";
            $query .= "    ,:ORDER_DATE_END   ";
            $query .= "    ,:PERI_COSTPRICE   ";
            $query .= "    ,:ORDER_LOT        ";
            $query .= "  )                    ";
            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['prod_cd']) === false){
                    continue;
                }

                // 期間売価コード
                if (is_numeric($data[$sind]['peri_cost_cd']) === false){
                    continue;
                }
                $peri_cost_cd = $data[$sind]['peri_cost_cd'];
                if (intval($peri_cost_cd) > 9999){
                    $peri_cost_cd = $this->get_peri_cost_cd();
                    if ($peri_cost_cd === false){
                        $aryErrMsg[] = '追加処理中にエラーが発生しました。';
                        $Log->trace("END   Insert");
                        return false;
                    }
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
                $param[":PERI_COST_CD"]     = $peri_cost_cd;
                $param[":SUPP_CD"]          = $aryKeys["supp_cd"];
                $param[":PROD_CD"]          = $data[$sind]["prod_cd"];
                $param[":ORDER_DATE_STR"]   = str_replace('/', '', $data[$sind]["order_date_str"]);
                $param[":ORDER_DATE_END"]   = str_replace('/', '', $data[$sind]["order_date_end"]);
                $param[":PERI_COSTPRICE"]   = str_replace(',', '', $data[$sind]["peri_costprice"]);
                $param[":ORDER_LOT"]        = str_replace(',', '', $data[$sind]["order_lot"]);

                //$result = $DBA->executeSQL($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                if ($result === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました123。';
                    $Log->trace("END   Insert");
                    return false;
                }
            }
        }
            $Log->trace("END   Insert");
            return true;
        } 
        /**
         * 期間原価マスタ既存データ更新
         * @param
         * @return   SQLの実行結果
         */
        public function Update($aryKeys, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Update");
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
            // $data配列には主キーと変更項目の2要素が1行ごとに時系列で格納されている
            // 同じ変更項目が格納されることもあるが後優先である
            for ($intL = 0; $intL < count($data); $intL ++){
                //
                $query  = "";
                //$query .= " update mst1401 set  ";
                $query .=  " update ". $_SESSION["SCHEMA"].".mst1401 set  "; 
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
                $param[":organization_id"]  = $orglist["organization_id"];

                $updkey_cd = '';
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['updkey_peri_cost_cd'])){
                    foreach ($data[$sind] as $key => $value){
                        // updkey_peri_cost_cd is enough to update record.
                        if ($key === 'updkey_prod_cd'){
                            continue;
                        }
                        
                        $do_query = 0;
                        if ($key === 'updkey_peri_cost_cd'){
                            $updkey_cd = $value;
                            $param[":updkey_peri_cost_cd"] = $updkey_cd;
                            continue;
                        }
                        if (is_null($value) === false){
                            if ($key === 'prod_cd'){
                                if (is_numeric($value) === false){
                                    continue;
                                }
                            }
                            $do_query = 1;
                            $query .= "  $key =    :$key  ";
                            //$param[':'.$key] = $value;
                            $param[':'.$key] = str_replace(',', '', str_replace('/', '', $value));
                        }
                    }
                    if ($do_query === 0){
                        continue;
                    }
                    $query .= " where ORGANIZATION_ID   = :organization_id      ";
                    $query .= " and   peri_cost_cd      = :updkey_peri_cost_cd  ";
//print_r($value);
                    //$result = $DBA->executeSQL($query, $param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    if ($result === false){
                        $aryErrMsg[] = '更新処理中にエラーが発生しました。';
                        $Log->trace("END   Update");
                        return false;
                    }
                }
            }
        }
            $Log->trace("END   Update");
            return true;
        }        

        /**
         * 期間原価マスタ既存データ削除
         */
        //public function Delete($data){
        public function Delete($aryKeys, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Delete");
            $param = array();
            $param[":supp_cd"]          = $aryKeys["supp_cd"];
            $query  = "";
            //$query .= " delete from mst1401 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].". mst1401 ";
            //$query .= " where organization_id = :organization_id";
            $query .= " where   supp_cd         = :supp_cd";
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                if ($result === false){
                    $aryErrMsg[] = '削除処理中にエラーが発生しました。';
                    $Log->trace("END   Delete");
                    return false;
                }
            $Log->trace("END   Delete");
            return true;
        }

        public function get_peri_cost_cd()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_peri_cost_cd");

            // 期間売価コード取得
            $sql = "SELECT TO_CHAR(nextval('peri_cost_cd_mst1401'),'FM0000') as peri_cost_cd_mst1401";
            $searchArray = array();
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            // データ取得ができなかった場合、エラーを返す
            if( $result === false )
            {
                // SQL実行エラー
                $Log->trace("END   get_peri_cost_cd");
                return false;
            }
            $peri_cost_cd = '';
            // 取得したデータを格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $peri_cost_cd = $data['peri_cost_cd_mst1401'];
            }
            if ($peri_cost_cd === '') {
                // SQL実行エラー
                $Log->trace("END   get_peri_cost_cd");
                return false;
            }

            $Log->trace("END   get_peri_cost_cd");
            return $peri_cost_cd;
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
            //$sql .= ", prod_kn_rk, prod_capa_nm";
            //$sql .= ", TO_CHAR(saleprice,       'FM999,999,990')    AS saleprice";
            //$sql .= ", TO_CHAR(head_costprice,  'FM999,999,990.00') AS costprice";
            //$sql .= " from mst0201 where 1 = :prod_cd order by prod_cd desc";
            $sql .= " from mst0201 where 1 = :prod_cd";
            if ($organization_id !== null){
                $sql .= " and organization_id = :organization_id";
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

            $param = array();
            //$param[":organization_id"]  = $aryKeys["organization_id"];
            $param[":supp_cd"]          = $aryKeys["supp_cd"];

            // 期間原価マスタ削除
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"].". mst1401 ";
            //$query .= " where organization_id = :organization_id";
            $query .= " where   supp_cd         = :supp_cd";
            if ($aryKeys['order_date_str'] !== ''){
                $query .= " and   order_date_str >= :order_date_str";
                $param[':order_date_str'] = $aryKeys['order_date_str'];
            }
            if ($aryKeys['order_date_end'] !== ''){
                $query .= " and   order_date_end >= :order_date_end";
                $param[':order_date_end'] = $aryKeys['order_date_end'];
            }

           // $result = $DBA->executeSQL($query, $param);
            //$result = $DBA->executeSQL_no_searchpath($query, $param);
            $result = $DBA->executeSQL_no_searchpath($query, $param);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                $aryErrMsg[] = '削除処理中にエラーが発生しました。';
                $Log->trace("END   AllDelete");
                return false;
            }

            // コミット
            $DBA->commit();
            $Log->trace("END   AllDelete");
            return true;
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

            $Log->trace("END searchmst1101Data");

            return $mst1101DataList;
        }
        
        /**
         * 商品部門情報データ取得
         * @param    $supp_cd
         * @return   SQLの実行結果
         */
        public function get_mst1101_data( $aryKeys )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst1101_data");
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
            $searchArray = array(
                ':organization_id'  => $aryKeys['organization_id'],
                ':supp_cd'          => $aryKeys['supp_cd'] );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1101DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst1101_data");
                return $mst1101DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst1101DataList = $data;
            }

            $Log->trace("END get_mst1101_data");

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
        
    }
?>