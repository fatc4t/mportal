<?php
    /**
     * @file      商品分類情報
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      商品分類情報テーブルの管理を行う
     */

    // BaseProducts.phpを読み込む
    require './Model/BaseProduct.php';

    /**
     * 商品分類情報クラス
     * @note   商品分類情報テーブルの管理を行う。
     */
    class Mst0801 extends BaseProduct
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
         * 商品分類情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        public function searchMst0801Data()
        {
            global $DBA, $Log; // searchMst0801Data変数宣言
            $Log->trace("START searchMst0801Data");

//            $sql = ' SELECT type_cd,type_nm,type_kn from mst0801 where 1 = :type_cd order by type_cd desc';
            $sql = ' SELECT organization_id, type_cd,type_nm,type_kn from mst0801 where 1 = :type_cd order by type_cd desc';
            $searchArray = array(':type_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0801DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END searchMst0801Data");
                return $mst0801DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst0801DataList, $data);
            }

            $Log->trace("END searchMst0801Data");

            return $mst0801DataList;
        }
        /**
         * 商品分類情報データ取得
         * @return   SQLの実行結果
         */
        //public function get_mst0801_data()
        public function get_mst0801_data( $cmb_prod_t )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst0801_data");
            
            if (count($cmb_prod_t) === 0) {
                $Log->trace("END get_mst0801_data");
                return array();
            }
            $sql  = ' SELECT';
            $sql .= '   prod_t_cd'.$cmb_prod_t['prod_t_type']. ' as prod_t_cd';
            $sql .= '  ,prod_t_nm';
            $sql .= '  ,prod_t_kn';
            $sql .= ' FROM mst0801';
//            $sql .= '  WHERE 1 = :type  ';
            $sql .= '  WHERE organization_id = :organization_id  ';
            $sql .= '  AND   prod_t_type     = :prod_t_type';

//            $searchArray = array( ':type' => 1 );                
            $searchArray = array(
                ':organization_id'  => $cmb_prod_t['organization_id'],
                ':prod_t_type'      => $cmb_prod_t['prod_t_type']
            );

            // 種別 = 中分類、小分類、クラス
            if ($cmb_prod_t['prod_t_type'] !== '1'){
                $sql .= '  AND   prod_t_cd1 = :prod_t_cd1';
                $searchArray = array_merge($searchArray, array(':prod_t_cd1' => $cmb_prod_t['prod_t_cd1']));
            }
            // 種別 = 小分類、クラス
            if ($cmb_prod_t['prod_t_type'] !== '1' && $cmb_prod_t['prod_t_type'] !== '2'){
                $sql .= '  AND   prod_t_cd2 = :prod_t_cd2';
                $searchArray = array_merge($searchArray, array(':prod_t_cd2' => $cmb_prod_t['prod_t_cd2']));
            }
            // 分類種別 = クラス
            if ($cmb_prod_t['prod_t_type'] !== '1' && $cmb_prod_t['prod_t_type'] !== '2' && $cmb_prod_t['prod_t_type'] !== '3'){
                $sql .= '  AND   prod_t_cd3 = :prod_t_cd3';
                $searchArray = array_merge($searchArray, array(':prod_t_cd3' => $cmb_prod_t['prod_t_cd3']));
            }
            $sql .= '  ORDER BY prod_t_cd';

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0801DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst0801_data");
                return $mst0801DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst0801DataList, $data);
            }

            $Log->trace("END get_mst0801_data");

            return $mst0801DataList;
        }

        /**
         * 商品分類情報データ取得
         * @return   SQLの実行結果
         */
        public function get_mst0801_alldata()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst0801_alldata");
            
            $sql  = ' SELECT'
                        . '  organization_id'
                        . ', prod_t_type'
                        . ', prod_t_cd1'
                        . ', prod_t_cd2'
                        . ', prod_t_cd3'
                        . ', prod_t_cd4'
                        . ', prod_t_nm'
                        . ', prod_t_kn'
                        . '  FROM mst0801'
                        . '  WHERE 1 = :prod_t_type  '
                        . '  ORDER BY'
                        . '  organization_id'
                        . ', prod_t_type'
                        . ', prod_t_cd1'
                        . ', prod_t_cd2'
                        . ', prod_t_cd3'
                        . ', prod_t_cd4';

            $searchArray = array( ':prod_t_type' => 1 );                

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0801AllDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst0801_alldata");
                return $mst0801AllDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst0801AllDataList, $data);
            }

            $Log->trace("END get_mst0801_alldata");

            return $mst0801AllDataList;
        }
        
        /**
         * 商品分類マスタデータ登録
         * @param   $post           POSTデータ
         * @param   &$aryErrMsg     エラーメッセージ配列
         * @return
         */
        public function BatchRegist($cmb_prod_t, $post, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START BatchRegist");

            // トランザクション開始
            $DBA->beginTransaction();

            // 削除
            if (isset($post['del_data']) === true){
                $del_data = json_decode($post['del_data'], true);
                //$mst0801->Delete($del_data);
                if ($this->Delete($cmb_prod_t, $del_data, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
                }
            }
            // 新規
            if (isset($post['new_data']) === true){
                $new_data = json_decode($post['new_data'], true);
                //$mst0801->Insert($new_data);
                if ($this->Insert($cmb_prod_t, $new_data, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
                }
            }
            // 更新
            if (isset($post['upd_data']) === true){
                $upd_data = json_decode($post['upd_data'], true);
                //$mst0801->Update($upd_data);
                if ($this->Update($cmb_prod_t, $upd_data, $aryErrMsg) === false){
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
         * 商品分類マスタ新規データ登録
         * @param
         * @return   SQLの実行結果
         */
        //public function Insert($data)
        public function Insert($cmb_prod_t, $data, &$aryErrMsg)
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
            if(count($dataList) != 0){
                foreach ($dataList as $orglist){
//ADDEND kanderu                
            // 操作対象テーブルが1個のためトランザクションは使用しない
                    
            $query  = "";
//EDITSTR 2020/08/28 kanderu
            //$query .= "insert into mst0801 (";
            $query .= "insert into ". $_SESSION["SCHEMA"].". mst0801 (";
//EDITEND 2020/08/28 kanderu            
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,PROD_T_TYPE       ";
            $query .= "    ,PROD_T_CD1        ";
            $query .= "    ,PROD_T_CD2        ";
            $query .= "    ,PROD_T_CD3        ";
            $query .= "    ,PROD_T_CD4        ";
            $query .= "    ,PROD_T_NM         ";
            $query .= "    ,PROD_T_KN         ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:PROD_T_TYPE      ";
            $query .= "    ,:PROD_T_CD1       ";
            $query .= "    ,:PROD_T_CD2       ";
            $query .= "    ,:PROD_T_CD3       ";
            $query .= "    ,:PROD_T_CD4       ";
            $query .= "    ,:PROD_T_NM        ";
            $query .= "    ,:PROD_T_KN        ";
            $query .= "  )                    ";

            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['prod_t_cd']) === false){
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
                $param[":ORGANIZATION_ID"]  =  $orglist["organization_id"];
                $param[":PROD_T_TYPE"]      = $cmb_prod_t["prod_t_type"];
                $param[":PROD_T_CD1"]       = "";
                $param[":PROD_T_CD2"]       = "";
                $param[":PROD_T_CD3"]       = "";
                $param[":PROD_T_CD4"]       = "";
                if ($cmb_prod_t["prod_t_type"] === "1"){
                    $param[":PROD_T_CD1"] = $data[$sind]["prod_t_cd"];
                }
                else if ($cmb_prod_t["prod_t_type"] === "2"){
                    $param[":PROD_T_CD1"]   = $cmb_prod_t["prod_t_cd1"];
                    $param[":PROD_T_CD2"]   = $data[$sind]["prod_t_cd"];
                }
                else if ($cmb_prod_t["prod_t_type"] === "3"){
                    $param[":PROD_T_CD1"]   = $cmb_prod_t["prod_t_cd1"];
                    $param[":PROD_T_CD2"]   = $cmb_prod_t["prod_t_cd2"];
                    $param[":PROD_T_CD3"]   = $data[$sind]["prod_t_cd"];
                }
                else if ($cmb_prod_t["prod_t_type"] === "4"){
                    $param[":PROD_T_CD1"]   = $cmb_prod_t["prod_t_cd1"];
                    $param[":PROD_T_CD2"]   = $cmb_prod_t["prod_t_cd2"];
                    $param[":PROD_T_CD3"]   = $cmb_prod_t["prod_t_cd3"];
                    $param[":PROD_T_CD4"]   = $data[$sind]["prod_t_cd"];
                }
                $param[":PROD_T_NM"]        = $data[$sind]["prod_t_nm"];
                $param[":PROD_T_KN"]        = $data[$sind]["prod_t_kn"];
//EDITSTR 2020/08/28 kanderu
                //$result = $DBA->executeSQL($query, $param);
                 $result = $DBA->executeSQL_no_searchpath($query, $param);
//EDITEND 2020/08/28 kanderu                
                if ($result === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました。(商品分類コード：'.$data[$sind]["prod_t_cd"].')';
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
         * 商品分類マスタ既存データ更新
         * @param
         * @return   SQLの実行結果
         */
        //public function Update($data)
        public function Update($cmb_prod_t, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Update");

            // $data配列には主キーと変更項目の2要素が1行ごとに時系列で格納されている
            // 同じ変更項目が格納されることもあるが後優先である
            for ($intL = 0; $intL < count($data); $intL ++){
                $query  = "";
//EDITSTR kanderu 2020/08/28 
                //$query .= " update mst0801 set  ";
                $query .= " update ". $_SESSION["SCHEMA"].". mst0801 set  ";
//EDITEND kanderu 2020/08/28                
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
//EDITSTR kanderu 2020/08/28 
               // $param[":organization_id"]  = $cmb_prod_t["organization_id"];
//EDITEND kanderu 2020/08/28 
                $param[":prod_t_type"]      = $cmb_prod_t["prod_t_type"];

                $updkey_cd = '';
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['updkey_prod_t_cd'])){
                    foreach ($data[$sind] as $key => $value){
                        $do_query = 0;
                        if ($key === 'updkey_prod_t_cd'){
                            $updkey_cd = $value;
                            $param[":updkey_prod_t_cd"] = $updkey_cd;
                            continue;
                        }
                        if (is_null($value) === false){
                            if ($key === 'prod_t_cd'){
                                if (is_numeric($value) === false){
                                    continue;
                                }
                                // 他テーブル使用状況チェック
                                if ($this->UsedTableCheck($cmb_prod_t, $updkey_cd) === false){
                                    $aryErrMsg[] = '他のマスタで使用されているためコード変更できません。(商品分類コード：'.$updkey_cd.')';
                                    continue;
                                }
                            }
                            $do_query = 1;
//                            $query .= "  $key =    :$key  ";
                            if ($key === 'prod_t_cd'){
                                $query .= "  prod_t_cd".$cmb_prod_t["prod_t_type"]." = :$key  ";
                            }
                            else{
                                $query .= "  $key =    :$key  ";
                            }
                            $param[':'.$key] = $value;
                        }
                    }
                    if ($do_query === 0){
                        continue;
                    }
//EDITSTR 2020/08/28 kanderu
                    //$query .= " where ORGANIZATION_ID   = :organization_id      ";
                    $query .= " where   prod_t_type       = :prod_t_type          ";
                    $query .= " and   prod_t_cd".$cmb_prod_t["prod_t_type"]." = :updkey_prod_t_cd       ";
                //$result = $DBA->executeSQL($query, $param);
                 $result = $DBA->executeSQL_no_searchpath($query, $param);
//EDITEND 2020/08/28 kanderu  
                    if ($result === false){
                        $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品分類コード：'.$updkey_cd.')';
                        $Log->trace("END   Update");
                        return false;
                    }
                }
            }
            $Log->trace("END   Update");
            return true;
        }

        /**
         * 商品分類マスタ既存データ削除
         * @param    $organization_id：組織ID
         * @param    $sect_cd：部門コード
         * @return   SQLの実行結果
         */
        //public function Delete($data){
        public function Delete($cmb_prod_t, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Delete");

            $query  = "";
//EDITSTR 2020/08/28 kanderu
            //$query .= " delete from mst0801 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].". mst0801 ";
           // $query .= " where organization_id = :organization_id";
//EDITEND 2020/08/28 kanderu
            $query .= " where   prod_t_type     = :prod_t_type";
            $query .= " and   prod_t_cd".$cmb_prod_t["prod_t_type"]." = :prod_t_cd";
            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['prod_t_cd']) === false){
                    continue;
                }
                // 他テーブル使用状況チェック
                if ($this->UsedTableCheck($cmb_prod_t, $data[$sind]['prod_t_cd']) === false){
                    $aryErrMsg[] = '他のマスタで使用されているため削除できません。(商品分類コード：'.$data[$sind]["prod_t_cd"].')';
                    continue;
                }
 //EDITstr 2020/08/28 kanderu  
                $param = array();
               // $param[":organization_id"]  = $cmb_prod_t["organization_id"];
                $param[":prod_t_type"]      = $cmb_prod_t["prod_t_type"];
                $param[":prod_t_cd"]        = $data[$sind]['prod_t_cd'];
                //$result = $DBA->executeSQL($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
//EDITEND 2020/08/28 kanderu  
                if ($result === false){
                    $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品分類コード：'.$data[$sind]["prod_t_cd"].')';
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
         * @param   $type_cd            分類コード
         * @return boolean
         */
        function UsedTableCheck($cmb_prod_t, $prod_t_cd) {

            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START UsedTableCheck");

            $sql  = '';
            $sql .= 'SELECT COUNT(prod_cd) AS CNT FROM mst0201 ';
            $sql .= ' WHERE organization_id = :organization_id';

            $searchArray = array(
                ':organization_id'      => $cmb_prod_t["organization_id"],
                ':prod_t_cd'            => $prod_t_cd
            );

            if ($cmb_prod_t["prod_t_type"] === "1"){
                $sql .= ' AND   prod_t_cd1 = :prod_t_cd';
            }
            else if ($cmb_prod_t["prod_t_type"] === "2"){
                $sql .= ' AND   prod_t_cd1 = :prod_t_cd1';
                $sql .= ' AND   prod_t_cd2 = :prod_t_cd';
                $searchArray = array_merge($searchArray, array(
                        ':prod_t_cd1' => $cmb_prod_t["prod_t_cd1"]));
            }
            else if ($cmb_prod_t["prod_t_type"] === "3"){
                $sql .= ' AND   prod_t_cd1 = :prod_t_cd1';
                $sql .= ' AND   prod_t_cd2 = :prod_t_cd2';
                $sql .= ' AND   prod_t_cd3 = :prod_t_cd';
                $searchArray = array_merge($searchArray, array(
                        ':prod_t_cd1' => $cmb_prod_t["prod_t_cd1"],
                        ':prod_t_cd2' => $cmb_prod_t["prod_t_cd2"]));
            }
            else if ($cmb_prod_t["prod_t_type"] === "4"){
                $sql .= ' AND   prod_t_cd1 = :prod_t_cd1';
                $sql .= ' AND   prod_t_cd2 = :prod_t_cd2';
                $sql .= ' AND   prod_t_cd3 = :prod_t_cd3';
                $sql .= ' AND   prod_t_cd4 = :prod_t_cd';
                $searchArray = array_merge($searchArray, array(
                        ':prod_t_cd1' => $cmb_prod_t["prod_t_cd1"],
                        ':prod_t_cd2' => $cmb_prod_t["prod_t_cd2"],
                        ':prod_t_cd3' => $cmb_prod_t["prod_t_cd3"]));
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
    }
?>