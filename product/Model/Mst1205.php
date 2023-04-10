<?php
    /**
     * @file      商品部門分類情報
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      商品部門分類情報テーブルの管理を行う
     */

    // BaseProducts.phpを読み込む
    require './Model/BaseProduct.php';

    /**
     * 商品部門分類情報クラス
     * @note   商品部門分類情報テーブルの管理を行う。
     */
    class Mst1205 extends BaseProduct
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
         * 商品部門分類情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        public function searchMst1205Data()
        {
            global $DBA, $Log; // searchMst1205Data変数宣言
            $Log->trace("START searchMst1205Data");

//            $sql = ' SELECT type_cd,type_nm,type_kn from mst1205 where 1 = :type_cd order by type_cd desc';
            $sql = ' SELECT organization_id, type_cd,type_nm,type_kn from mst1205 where 1 = :type_cd order by type_cd desc';
            $searchArray = array(':type_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1205DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END searchMst1205Data");
                return $mst1205DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst1205DataList, $data);
            }

            $Log->trace("END searchMst1205Data");

            return $mst1205DataList;
        }

        /**
         * 商品部門分類情報データ取得
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
            public function getMst1205List()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst1205List");

//            $sql = " SELECT string_agg(type_cd,',') as list from mst1205 where 1 = :type_cd ";            
            $sql = " SELECT string_agg(organization_id||':'||type_cd,',') as list from mst1205 where 1 = :type_cd ";            
            $searchArray = array(':type_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1205DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst1205List");
                return $mst1205DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst1205DataList = $data;
            }

            $Log->trace("END getMst1205List");

            return $mst1205DataList;
        }   
        

        /**
         * 商品部門分類情報データ取得
         * @return   SQLの実行結果
         */
        //public function get_mst1205_data()
        public function get_mst1205_data( $organization_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst1205_data");

            $sql = ' select type_cd,type_nm,type_kn from mst1205';
//            $sql .= '  WHERE 1 = :type  ';
            $sql .= '  WHERE organization_id = :organization_id  ';
            $sql .= '  ORDER BY type_cd';

//            $searchArray = array( ':type' => 1 );                
            $searchArray = array( ':organization_id' => $organization_id );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst1205DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst1205_data");
                return $mst1205DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst1205DataList, $data);
            }

            $Log->trace("END get_mst1205_data");

            return $mst1205DataList;
        }

        
        /**
         * 商品部門分類マスタデータ登録
         * @param   $post           POSTデータ
         * @param   &$aryErrMsg     エラーメッセージ配列
         * @return
         */
        public function BatchRegist($post, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START BatchRegist");
            //print_r($post);
            // 組織ID
            $organization_id = $post['organization_id'];
            
            // トランザクション開始
            $DBA->beginTransaction();

            // 削除
            if (isset($post['del_data']) === true){
                $del_data = json_decode($post['del_data'], true);
                //$mst1205->Delete($del_data);
                if ($this->Delete($organization_id, $del_data, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
                }
            }
            // 新規
            if (isset($post['new_data']) === true){
                $new_data = json_decode($post['new_data'], true);
                //$mst1205->Insert($new_data);
                if ($this->Insert($organization_id, $new_data, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
                }
            }
            // 更新
            if (isset($post['upd_data']) === true){
                $upd_data = json_decode($post['upd_data'], true);
                //$mst1205->Update($upd_data);
                if ($this->Update($organization_id, $upd_data, $aryErrMsg) === false){
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
         * 商品部門分類マスタ新規データ登録
         * @param
         * @return   SQLの実行結果
         */
        //public function Insert($data)
        public function Insert($organization_id, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Insert");
////ADDSTR 2020/08/26 kanderu
//           //select org id 
            $query   = "";
            $query  .= "select  ";
            $query  .= "organization_id  ";
            $query  .= "from ". $_SESSION["SCHEMA"] .". m_organization_detail ";
            $query  .= "where organization_id <> 1  ";
            $query  .= "order by organization_id  ";
          //  print_r($query);
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
            $query  = "";
            $query .= "insert into ". $_SESSION["SCHEMA"] .".mst1205 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,TYPE_CD           ";
            $query .= "    ,TYPE_NM           ";
            $query .= "    ,TYPE_KN           ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:TYPE_CD          ";
            $query .= "    ,:TYPE_NM          ";
            $query .= "    ,:TYPE_KN          ";
            $query .= "  )                    ";

            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['type_cd']) === false){
                    continue;
                }
                $param = array();
                $param[":INSUSER_CD"]       = $_SESSION["LOGIN"];
                $param[":INSDATETIME"]      = "now()";
                $param[":UPDUSER_CD"]       = $_SESSION["LOGIN"];
                $param[":UPDDATETIME"]      = "now()";
                $param[":DISABLED"]         = '0';
                $param[":LAN_KBN"]          = '0';
                $param[":CONNECT_KBN"]      = '0';
                $param[":ORGANIZATION_ID"]  = $orglist1["organization_id"];
                $param[":TYPE_CD"]          = $data[$sind]["type_cd"];
                $param[":TYPE_NM"]          = $data[$sind]["type_nm"];
                $param[":TYPE_KN"]          = $data[$sind]["type_kn"];

                $result = $DBA->executeSQL_no_searchpath($query, $param);
            }
        }
                if ($result === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました。(分類コード：'.$data[$sind]["type_cd"].')';
                    $Log->trace("END   Insert");
                    return false;
                }
            }
            $Log->trace("END   Insert");
            return true;

    }
        /**
         * 商品部門分類マスタ既存データ更新
         * @param
         * @return   SQLの実行結果
         */
        //public function Update($data)
        public function Update($organization_id, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Update");

            // $data配列には主キーと変更項目の2要素が1行ごとに時系列で格納されている
            // 同じ変更項目が格納されることもあるが後優先である
            for ($intL = 0; $intL < count($data); $intL ++){
                $query  = "";
                $query .= " update ". $_SESSION["SCHEMA"] .".mst1205 set  ";
                $query .= "  UPDUSER_CD         = :UPDUSER_CD        , ";
                $query .= "  UPDDATETIME        = :UPDDATETIME       , ";
                $query .= "  DISABLED           = :DISABLED          , ";
                $query .= "  LAN_KBN            = :LAN_KBN           , ";
                $query .= "  CONNECT_KBN        = :CONNECT_KBN       , ";

                $param = array();
                $param[":UPDUSER_CD"]       = $_SESSION["LOGIN"];
                $param[":UPDDATETIME"]      = "now()";
                $param[":DISABLED"]         = '0';
                $param[":LAN_KBN"]          = '0';
                $param[":CONNECT_KBN"]      = '0';
               // $param[":organization_id"]  = $organization_id;

                $updkey_cd = '';
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['updkey_type_cd'])){
                    foreach ($data[$sind] as $key => $value){
                        $do_query = 0;
                        if ($key === 'updkey_type_cd'){
                            $updkey_cd = $value;
                            $param[":updkey_type_cd"] = $updkey_cd;
                            continue;
                        }
                        if (is_null($value) === false){
                            if ($key === 'type_cd'){
                                if (is_numeric($value) === false){
                                    continue;
                                }
                                // 他テーブル使用状況チェック
                                if ($this->UsedTableCheck($organization_id, $updkey_cd) === false){
                                    $aryErrMsg[] = '他のマスタで使用されているためコード変更できません。(分類コード：'.$updkey_cd.')';
                                    continue;
                                }
                            }
                            $do_query = 1;
                            $query .= "  $key =    :$key  ";
                            $param[':'.$key] = $value;
                        }
                    }
                    if ($do_query === 0){
                        continue;
                    }
                  //  $query .= " where ORGANIZATION_ID   = :organization_id      ";
                    $query .= " where   type_cd           = :updkey_type_cd       ";

                    $result = $DBA->executeSQL_no_searchpath($query, $param);
                    if ($result === false){
                        $aryErrMsg[] = '更新処理中にエラーが発生しました。(分類コード：'.$updkey_cd.')';
                        $Log->trace("END   Update");
                        return false;
                    }
                }
            }
            $Log->trace("END   Update");
            return true;
        }

        /**
         * 商品部門マスタ既存データ削除
         * @param    $organization_id：組織ID
         * @param    $sect_cd：部門コード
         * @return   SQLの実行結果
         */
        //public function Delete($data){
        public function Delete($organization_id, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Delete");
            
            $query1  = "";
            $query1 .= " update ". $_SESSION["SCHEMA"].".mst1205 ";
            $query1 .= " set UPDUSER_CD='delete'  ";
            $query1 .= " where cust_b_type = :cust_b_type ";
            $query1 .= " and   type_cd = :type_cd";
            
            $query2  = "";
            $query2 .= " delete from ". $_SESSION["SCHEMA"].".mst1205_CHANGED ";
            $query2 .= " where cust_b_type = :cust_b_type ";
            $query2 .= " and   type_cd = :type_cd";
            
            $query  = "";
            $query .= " delete from ". $_SESSION["SCHEMA"] .".mst1205 ";
           // $query .= " where organization_id = :organization_id";
            $query .= " where   type_cd = :type_cd";

            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['type_cd']) === false){
                    continue;
                }
                // 他テーブル使用状況チェック
                if ($this->UsedTableCheck($organization_id, $data[$sind]['type_cd']) === false){
                    $aryErrMsg[] = '他のマスタで使用されているため削除できません。(分類コード：'.$data[$sind]["type_cd"].')';
                    continue;
                }
                $param = array();
              //  $param[":organization_id"]  = $organization_id;
                $param[":type_cd"]          = $data[$sind]['type_cd'];

                $result = $DBA->executeSQL_no_searchpath($query1, $param);
                $result = $DBA->executeSQL_no_searchpath($query2, $param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
                if ($result === false){
                    $aryErrMsg[] = '削除処理中にエラーが発生しました。(分類コード：'.$data[$sind]["type_cd"].')';
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
        function UsedTableCheck($organization_id, $type_cd) {

            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START UsedTableCheck");

            $searchArray = array(
                ':organization_id'      => $organization_id,
                ':type_cd'              => $type_cd
            );

            $sqlArray = array(
                // 商品部門マスタ
                'SELECT COUNT(sect_cd) AS CNT FROM mst1201 WHERE organization_id = :organization_id AND type_cd = :type_cd',
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
    }
?>