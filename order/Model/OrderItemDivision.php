<?php
    /**
     * @file      商品区分情報
     * @author    尉
     * @date      2019/09/05
     * @version   1.00
     * @note      商品区分情報テーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 商品区分情報クラス
     * @note   商品区分情報テーブルの管理を行う。
     */
    class OrderItemDivision extends BaseModel
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
         * 商品区分情報データ取得
         * @return   SQLの実行結果
         */
        public function get_orderItemDivision_data( $cmb_prod_k )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_OrderItemDivision_data");
            
            if (count($cmb_prod_k) === 0) {
                $Log->trace("END get_OrderItemDivision_data");
                return array();
            }
            $sql  = ' SELECT';
            $sql .= '   prod_k_cd'.$cmb_prod_k['prod_k_type']. ' as prod_k_cd';
            $sql .= '  ,prod_k_nm';
            $sql .= '  ,prod_k_kn';
            $sql .= ' FROM ord_m_prod_kbn';
            $sql .= '  WHERE ma_cd = :ma_cd  ';
            $sql .= '  AND   prod_k_type     = :prod_k_type';
               
            $searchArray = array(
                ':ma_cd'  => $cmb_prod_k['ma_cd'],
                ':prod_k_type'      => $cmb_prod_k['prod_k_type']
            );

            // 種別 = 商品区分2、商品区分3、商品区分4、商品区分5
            if ($cmb_prod_k['prod_k_type'] !== '1'){
                $sql .= '  AND   prod_k_cd1 = :prod_k_cd1';
                $searchArray = array_merge($searchArray, array(':prod_k_cd1' => $cmb_prod_k['prod_k_cd1']));
            }
            // 種別 = 商品区分3、商品区分4、商品区分5
            if ($cmb_prod_k['prod_k_type'] !== '1' && $cmb_prod_k['prod_k_type'] !== '2'){
                $sql .= '  AND   prod_k_cd2 = :prod_k_cd2';
                $searchArray = array_merge($searchArray, array(':prod_k_cd2' => $cmb_prod_k['prod_k_cd2']));
            }
            // 種別 = 商品区分4、商品区分5
            if ($cmb_prod_k['prod_k_type'] !== '1' && $cmb_prod_k['prod_k_type'] !== '2' && $cmb_prod_k['prod_k_type'] !== '3'){
                $sql .= '  AND   prod_k_cd3 = :prod_k_cd3';
                $searchArray = array_merge($searchArray, array(':prod_k_cd3' => $cmb_prod_k['prod_k_cd3']));
            }
            // 種別 = 商品区分5
            if ($cmb_prod_k['prod_k_type'] !== '1' && $cmb_prod_k['prod_k_type'] !== '2' 
                    && $cmb_prod_k['prod_k_type'] !== '3' && $cmb_prod_k['prod_k_type'] !== '4'){
                $sql .= '  AND   prod_k_cd4 = :prod_k_cd4';
                $searchArray = array_merge($searchArray, array(':prod_k_cd4' => $cmb_prod_k['prod_k_cd4']));
            }
            $sql .= '  ORDER BY prod_k_cd';

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0801DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_OrderItemDivision_data");
                return $mst0801DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst0801DataList, $data);
            }

            $Log->trace("END get_OrderItemDivision_data");

            return $mst0801DataList;
        }

        /**
         * 商品区分情報データ取得
         * @return   SQLの実行結果
         */
        public function get_orderitemdivision_alldata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_OrderItemDivision_alldata");
            
            $sql  = ' SELECT'
                        . '  ma_cd'
                        . ', prod_k_type'
                        . ', prod_k_cd1'
                        . ', prod_k_cd2'
                        . ', prod_k_cd3'
                        . ', prod_k_cd4'
                        . ', prod_k_cd5'
                        . ', prod_k_nm'
                        . ', prod_k_kn'
                        . '  FROM ord_m_prod_kbn'
                        //. '  WHERE 1 = :prod_k_type and ma_cd = '.$_SESSION['MA_CD']
                        . '  WHERE 1 = :prod_k_type '
                        . '  ORDER BY'
                        . '  ma_cd'
                        . ', prod_k_type'
                        . ', prod_k_cd1'
                        . ', prod_k_cd2'
                        . ', prod_k_cd3'
                        . ', prod_k_cd4'
                        . ', prod_k_cd5';

            $searchArray = array( ':prod_k_type' => 1 );                

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $prodkbnAllDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_OrderItemDivision_alldata");
                return $prodkbnAllDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $prodkbnAllDataList, $data);
            }

            $Log->trace("END get_OrderItemDivision_alldata");

            return $prodkbnAllDataList;
        }
        
        /**
         * 商品区分マスタデータ登録
         * @param   $post           POSTデータ
         * @param   &$aryErrMsg     エラーメッセージ配列
         * @return
         */
        public function BatchRegist($cmb_prod_k, $post, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START BatchRegist");

            // トランザクション開始
            $DBA->beginTransaction();

            // 削除
            if (isset($post['del_data']) === true){
                $del_data = json_decode($post['del_data'], true);
                if ($this->Delete($cmb_prod_k, $del_data, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
                }
            }
            // 新規
            if (isset($post['new_data']) === true){
                $new_data = json_decode($post['new_data'], true);
                if ($this->Insert($cmb_prod_k, $new_data, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
                }
            }
            // 更新
            if (isset($post['upd_data']) === true){
                $upd_data = json_decode($post['upd_data'], true);
                if ($this->Update($cmb_prod_k, $upd_data, $aryErrMsg) === false){
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
         * 商品区分マスタ新規データ登録
         * @param
         * @return   SQLの実行結果
         */
        //public function Insert($data)
        public function Insert($cmb_prod_k, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Insert");

            $query  = "";
            $query .= "insert into ord_m_prod_kbn (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,MA_CD             ";
            $query .= "    ,PROD_K_TYPE       ";
            $query .= "    ,PROD_K_CD1        ";
            $query .= "    ,PROD_K_CD2        ";
            $query .= "    ,PROD_K_CD3        ";
            $query .= "    ,PROD_K_CD4        ";
            $query .= "    ,PROD_K_CD5        ";
            $query .= "    ,PROD_K_NM         ";
            $query .= "    ,PROD_K_KN         ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:MA_CD            ";
            $query .= "    ,:PROD_K_TYPE      ";
            $query .= "    ,:PROD_K_CD1       ";
            $query .= "    ,:PROD_K_CD2       ";
            $query .= "    ,:PROD_K_CD3       ";
            $query .= "    ,:PROD_K_CD4       ";
            $query .= "    ,:PROD_K_CD5       ";
            $query .= "    ,:PROD_K_NM        ";
            $query .= "    ,:PROD_K_KN        ";
            $query .= "  )                    ";

            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['prod_k_cd']) === false){
                    continue;
                }
                $param = array();
                $param[":INSUSER_CD"]       = $_SESSION["USER_ID"];
                $param[":INSDATETIME"]      = "now()";
                $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
                $param[":UPDDATETIME"]      = "now()";
                $param[":DISABLED"]         = '0';
                $param[":LAN_KBN"]          = '0';
                $param[":MA_CD"]            = $cmb_prod_k["ma_cd"];
                $param[":PROD_K_TYPE"]      = $cmb_prod_k["prod_k_type"];
                $param[":PROD_K_CD1"]       = "";
                $param[":PROD_K_CD2"]       = "";
                $param[":PROD_K_CD3"]       = "";
                $param[":PROD_K_CD4"]       = "";
                $param[":PROD_K_CD5"]       = "";
                if ($cmb_prod_k["prod_k_type"] === "1"){
                    $param[":PROD_K_CD1"] = $data[$sind]["prod_k_cd"];
                }
                else if ($cmb_prod_k["prod_k_type"] === "2"){
                    $param[":PROD_K_CD1"]   = $cmb_prod_k["prod_k_cd1"];
                    $param[":PROD_K_CD2"]   = $data[$sind]["prod_k_cd"];
                }
                else if ($cmb_prod_k["prod_k_type"] === "3"){
                    $param[":PROD_K_CD1"]   = $cmb_prod_k["prod_k_cd1"];
                    $param[":PROD_K_CD2"]   = $cmb_prod_k["prod_k_cd2"];
                    $param[":PROD_K_CD3"]   = $data[$sind]["prod_k_cd"];
                }
                else if ($cmb_prod_k["prod_k_type"] === "4"){
                    $param[":PROD_K_CD1"]   = $cmb_prod_k["prod_k_cd1"];
                    $param[":PROD_K_CD2"]   = $cmb_prod_k["prod_k_cd2"];
                    $param[":PROD_K_CD3"]   = $cmb_prod_k["prod_k_cd3"];
                    $param[":PROD_K_CD4"]   = $data[$sind]["prod_k_cd"];
                }
                else if ($cmb_prod_k["prod_k_type"] === "5"){
                    $param[":PROD_K_CD1"]   = $cmb_prod_k["prod_k_cd1"];
                    $param[":PROD_K_CD2"]   = $cmb_prod_k["prod_k_cd2"];
                    $param[":PROD_K_CD3"]   = $cmb_prod_k["prod_k_cd3"];
                    $param[":PROD_K_CD4"]   = $cmb_prod_k["prod_k_cd4"];
                    $param[":PROD_K_CD5"]   = $data[$sind]["prod_k_cd"];
                }
                $param[":PROD_K_NM"]        = $data[$sind]["prod_k_nm"];
                $param[":PROD_K_KN"]        = $data[$sind]["prod_k_kn"];

                $result = $DBA->executeSQL($query, $param);
                if ($result === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました。(商品区分コード：'.$data[$sind]["prod_k_cd"].')';
                    $Log->trace("END   Insert");
                    return false;
                }
            }
            $Log->trace("END   Insert");
            return true;
        }
        
        /**
         * 商品区分マスタ既存データ更新
         * @param
         * @return   SQLの実行結果
         */
        //public function Update($data)
        public function Update($cmb_prod_k, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Update");

            // $data配列には主キーと変更項目の2要素が1行ごとに時系列で格納されている
            // 同じ変更項目が格納されることもあるが後優先である
            for ($intL = 0; $intL < count($data); $intL ++){
                $query  = "";
                $query .= " update ord_m_prod_kbn set  ";
                $query .= "  UPDUSER_CD         = :UPDUSER_CD        , ";
                $query .= "  UPDDATETIME        = :UPDDATETIME       , ";
                $query .= "  DISABLED           = :DISABLED          , ";
                $query .= "  LAN_KBN            = :LAN_KBN            ";

                $param = array();
                $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
                $param[":UPDDATETIME"]      = "now()";
                $param[":DISABLED"]         = '0';
                $param[":LAN_KBN"]          = '0';
                $param[":ma_cd"]            = $cmb_prod_k["ma_cd"];
                $param[":prod_k_type"]      = $cmb_prod_k["prod_k_type"];
                
                $updkey_cd = '';
                $sind = '"'.$intL.'"';
                $param1 = [];
                $query1 = "";
                if (is_numeric($data[$sind]['updkey_prod_k_cd'])){
                    foreach ($data[$sind] as $key => $value){
                        $do_query = 0;
                        if ($key === 'updkey_prod_k_cd'){
                            $updkey_cd = $value;
                            $param1[":updkey_prod_k_cd"] = $updkey_cd;
                            continue;
                        }
                        if (is_null($value) === false){
                            if ($key === 'prod_k_cd'){
                                if (is_numeric($value) === false){
                                    continue;
                                }
                                // 他テーブル使用状況チェック
                                if ($this->UsedTableCheck($cmb_prod_k, $updkey_cd) === false){
                                    $aryErrMsg[] = '他のマスタで使用されているためコード変更できません。(商品区分コード：'.$updkey_cd.')';
                                    continue;
                                }
                            }
                            $do_query = 1;
                            if ($key === 'prod_k_cd'){
                                $query1 .= "  ,prod_k_cd".$cmb_prod_k["prod_k_type"]." = :$key  ";
                            }
                            else{
                                $query1 .= "  , $key =    :$key  ";
                            }
                            $param1[':'.$key] = $value;
                        }
                    }
                    if ($do_query === 0){
                        continue;
                    }
                    $query1 .= " where ma_cd             = :ma_cd                ";
                    $query1 .= " and   prod_k_type       = :prod_k_type          ";
                    $query1 .= " and   prod_k_cd".$cmb_prod_k["prod_k_type"]." = :updkey_prod_k_cd       ";
                    
                    if ($cmb_prod_k["prod_k_type"] === "2"){
                        $query1 .= ' AND   prod_k_cd1 = :prod_k_cd1';
                        $param1[prod_k_cd1] = $cmb_prod_k["prod_k_cd1"];
                    }
                    else if ($cmb_prod_k["prod_k_type"] === "3"){
                        $query1 .= ' AND   prod_k_cd1 = :prod_k_cd1';
                        $query1 .= ' AND   prod_k_cd2 = :prod_k_cd2';
                        $param1[prod_k_cd1] = $cmb_prod_k["prod_k_cd1"];
                        $param1[prod_k_cd2] = $cmb_prod_k["prod_k_cd2"];
                    }
                    else if ($cmb_prod_k["prod_k_type"] === "4"){
                        $query1 .= ' AND   prod_k_cd1 = :prod_k_cd1';
                        $query1 .= ' AND   prod_k_cd2 = :prod_k_cd2';
                        $query1 .= ' AND   prod_k_cd3 = :prod_k_cd3';
                        $param1[prod_k_cd1] = $cmb_prod_k["prod_k_cd1"];
                        $param1[prod_k_cd2] = $cmb_prod_k["prod_k_cd2"];
                        $param1[prod_k_cd3] = $cmb_prod_k["prod_k_cd3"];

                    }
                    else if ($cmb_prod_k["prod_k_type"] === "5"){
                        $query1 .= ' AND   prod_k_cd1 = :prod_k_cd1';
                        $query1 .= ' AND   prod_k_cd2 = :prod_k_cd2';
                        $query1 .= ' AND   prod_k_cd3 = :prod_k_cd3';
                        $query1 .= ' AND   prod_k_cd4 = :prod_k_cd4';
                        $param1[prod_k_cd1] = $cmb_prod_k["prod_k_cd1"];
                        $param1[prod_k_cd2] = $cmb_prod_k["prod_k_cd2"];
                        $param1[prod_k_cd3] = $cmb_prod_k["prod_k_cd3"];
                        $param1[prod_k_cd4] = $cmb_prod_k["prod_k_cd4"];
                    }

                    $result = $DBA->executeSQL($query.$query1, array_merge($param, $param1));
                    if ($result === false){
                        $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品区分コード：'.$updkey_cd.')';
                        $Log->trace("END   Update");
                        return false;
                    }
                }
            }
            $Log->trace("END   Update");
            return true;
        }

        /**
         * 商品区分マスタ既存データ削除
         * @param    $organization_id：組織ID
         * @param    $sect_cd：部門コード
         * @return   SQLの実行結果
         */
        //public function Delete($data){
        public function Delete($cmb_prod_k, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Delete");
            
            $param = array();
            $query  = "";
            $query .= " delete from ord_m_prod_kbn ";
            $query .= " where ma_cd = :ma_cd";
            $query .= " and   prod_k_type     = :prod_k_type";
            $query .= " and   prod_k_cd".$cmb_prod_k["prod_k_type"]." = :prod_k_cd";
            
            if ($cmb_prod_k["prod_k_type"] === "2"){
                $query .= ' AND   prod_k_cd1 = :prod_k_cd1';
                $param[prod_k_cd1] = $cmb_prod_k["prod_k_cd1"];
            }
            else if ($cmb_prod_k["prod_k_type"] === "3"){
                $query .= ' AND   prod_k_cd1 = :prod_k_cd1';
                $query .= ' AND   prod_k_cd2 = :prod_k_cd2';
                $param[prod_k_cd1] = $cmb_prod_k["prod_k_cd1"];
                $param[prod_k_cd2] = $cmb_prod_k["prod_k_cd2"];
            }
            else if ($cmb_prod_k["prod_k_type"] === "4"){
                $query .= ' AND   prod_k_cd1 = :prod_k_cd1';
                $query .= ' AND   prod_k_cd2 = :prod_k_cd2';
                $query .= ' AND   prod_k_cd3 = :prod_k_cd3';
                $param[prod_k_cd1] = $cmb_prod_k["prod_k_cd1"];
                $param[prod_k_cd2] = $cmb_prod_k["prod_k_cd2"];
                $param[prod_k_cd3] = $cmb_prod_k["prod_k_cd3"];
                
            }
            else if ($cmb_prod_k["prod_k_type"] === "5"){
                $query .= ' AND   prod_k_cd1 = :prod_k_cd1';
                $query .= ' AND   prod_k_cd2 = :prod_k_cd2';
                $query .= ' AND   prod_k_cd3 = :prod_k_cd3';
                $query .= ' AND   prod_k_cd4 = :prod_k_cd4';
                $param[prod_k_cd1] = $cmb_prod_k["prod_k_cd1"];
                $param[prod_k_cd2] = $cmb_prod_k["prod_k_cd2"];
                $param[prod_k_cd3] = $cmb_prod_k["prod_k_cd3"];
                $param[prod_k_cd4] = $cmb_prod_k["prod_k_cd4"];
            }

            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['prod_k_cd']) === false){
                    continue;
                }
                // 他テーブル使用状況チェック
                if ($this->UsedTableCheck($cmb_prod_k, $data[$sind]['prod_k_cd']) === false){
                    $aryErrMsg[] = '他のマスタで使用されているため削除できません。(商品区分コード：'.$data[$sind]["prod_k_cd"].')';
                    continue;
                }
                $param1 = array();
                $param1[":ma_cd"]            = $cmb_prod_k["ma_cd"];
                $param1[":prod_k_type"]      = $cmb_prod_k["prod_k_type"];
                $param1[":prod_k_cd"]        = $data[$sind]['prod_k_cd'];

                $result = $DBA->executeSQL($query, array_merge($param, $param1));
                if ($result === false){
                    $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品区分コード：'.$data[$sind]["prod_k_cd"].')';
                    $Log->trace("END   Delete");
                    return false;
                }
            }
            $Log->trace("END   Delete");
            return true;
        }

        /**
         * 他テーブル使用状況チェック処理
         * @param   $organization_id    組織ID
         * @param   $type_cd            区分コード
         * @return boolean
         */
        function UsedTableCheck($cmb_prod_k, $prod_k_cd) {

            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START UsedTableCheck");

            $sql  = '';
            $sql .= 'SELECT COUNT(pro_cd) AS CNT FROM ord_m_products ';
            $sql .= ' WHERE ma_cd = :ma_cd';

            $searchArray = array(
                ':ma_cd'           => $cmb_prod_k["ma_cd"],
                ':prod_k_cd'       => $prod_k_cd
            );

            if ($cmb_prod_k["prod_k_type"] === "1"){
                $sql .= ' AND   prod_k_cd1 = :prod_k_cd';
            }
            else if ($cmb_prod_k["prod_k_type"] === "2"){
                $sql .= ' AND   prod_k_cd1 = :prod_k_cd1';
                $sql .= ' AND   prod_k_cd2 = :prod_k_cd';
                $searchArray = array_merge($searchArray, array(
                        ':prod_k_cd1' => $cmb_prod_k["prod_k_cd1"]));
            }
            else if ($cmb_prod_k["prod_k_type"] === "3"){
                $sql .= ' AND   prod_k_cd1 = :prod_k_cd1';
                $sql .= ' AND   prod_k_cd2 = :prod_k_cd2';
                $sql .= ' AND   prod_k_cd3 = :prod_k_cd';
                $searchArray = array_merge($searchArray, array(
                        ':prod_k_cd1' => $cmb_prod_k["prod_k_cd1"],
                        ':prod_k_cd2' => $cmb_prod_k["prod_k_cd2"]));
            }
            else if ($cmb_prod_k["prod_k_type"] === "4"){
                $sql .= ' AND   prod_k_cd1 = :prod_k_cd1';
                $sql .= ' AND   prod_k_cd2 = :prod_k_cd2';
                $sql .= ' AND   prod_k_cd3 = :prod_k_cd3';
                $sql .= ' AND   prod_k_cd4 = :prod_k_cd';
                $searchArray = array_merge($searchArray, array(
                        ':prod_k_cd1' => $cmb_prod_k["prod_k_cd1"],
                        ':prod_k_cd2' => $cmb_prod_k["prod_k_cd2"],
                        ':prod_k_cd3' => $cmb_prod_k["prod_k_cd3"]));
            }
            else if ($cmb_prod_k["prod_k_type"] === "5"){
                $sql .= ' AND   prod_k_cd1 = :prod_k_cd1';
                $sql .= ' AND   prod_k_cd2 = :prod_k_cd2';
                $sql .= ' AND   prod_k_cd3 = :prod_k_cd3';
                $sql .= ' AND   prod_k_cd4 = :prod_k_cd4';
                $sql .= ' AND   prod_k_cd5 = :prod_k_cd';
                $searchArray = array_merge($searchArray, array(
                        ':prod_k_cd1' => $cmb_prod_k["prod_k_cd1"],
                        ':prod_k_cd2' => $cmb_prod_k["prod_k_cd2"],
                        ':prod_k_cd3' => $cmb_prod_k["prod_k_cd3"],
                        ':prod_k_cd4' => $cmb_prod_k["prod_k_cd4"]));
            }

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
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

            $Log->trace("END UsedTableCheck");
            return true;
        }

        /**
         * 業者リストデータ取得
         * @return   SQLの実行結果
         */
        public function getMakerList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMakerList");
            
            $sql  = ' SELECT'
                        . '  ord_m_maker.ma_cd ma_cd'
                        . ', ord_m_maker.ma_name ma_name'
                        . '  FROM ord_m_maker'
                        . '  INNER JOIN m_user_detail ON ord_m_maker.user_id = m_user_detail.user_id'
                        . '  WHERE 0 = ord_m_maker.is_del and m_user_detail.security_id > '.$this->getSecurityId()
                        . '  ORDER BY'
                        . '  ord_m_maker.ma_cd';

            $searchArray = array();                

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $makerList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMakerList");
                return $makerList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $makerList, $data);
            }

            $Log->trace("END getMakerList");

            return $makerList;
        }
        
        /**
        * セキュリティコード取得
        * @param     
        * @return   セキュリティコード  (security_id) 
        */
        private function getSecurityId()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSecurityId");

            $params = array();
            //メーカーコード取得
            $sql  = " SELECT security_id security_id From m_user_detail where user_id =  ";
            $sql .= $_SESSION["USER_ID"];
            $result = $DBA->executeSQL($sql,$params);
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $security_id = $row['security_id'];

            $Log->trace("END getSecurityId");
            return $security_id;
        }
    }
?>