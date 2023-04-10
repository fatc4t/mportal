<?php
    /**
     * @file      商品メーカー情報
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      商品メーカー情報テーブルの管理を行う
     */
         
    // BaseProducts.phpを読み込む
    require './Model/BaseProduct.php';
    /**
     * 商品部門分類情報クラス
     * @note   商品部門分類情報テーブルの管理を行う。
     */
    class Mst1001 extends BaseProduct
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
         * 商品メーカー情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        public function searchMst1001Data()
        {
            global $DBA, $Log; // searchMst1001Data変数宣言
            $Log->trace("START searchMst1001Data");
                
//            $sql = ' SELECT maker_cd,maker_nm,maker_nm_rk,maker_kn,maker_kn_rk from mst1001 where 1 = :maker_cd order by maker_cd desc';
            $sql = ' SELECT organization_id, maker_cd,maker_nm,maker_nm_rk,maker_kn,maker_kn_rk from mst1001 where 1 = :maker_cd order by maker_cd desc';
            $searchArray = array(':type_cd' => 1);
                
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
                
            // 一覧表を格納する空の配列宣言
            $mst1001DataList = array();
                
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END searchMst1001Data");
                return $mst1001DataList;
            }
                
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst1001DataList, $data);
            }
                
            $Log->trace("END searchMst1001Data");
                
            return $mst1001DataList;
        }
            
        /**
         * 商品メーカー情報データ取得
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
            public function getMst1001List()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst1001List");
                
//            $sql = " SELECT string_agg(maker_cd,',') as list from mst1001 where 1 = :maker_class_cd ";            
            $sql = " SELECT string_agg(organization_id||':'||maker_cd,',') as list from mst1001 where 1 = :maker_cd ";            
            $searchArray = array(':maker_cd' => 1);
                
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
                
            // 一覧表を格納する空の配列宣言
            $mst1001DataList = array();
                
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst1001List");
                return $mst1001DataList;
            }
                
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst1001DataList = $data;
            }
                
            $Log->trace("END getMst1001List");
                
            return $mst1001DataList;
        }   
            
            
        /**
         * 商品メーカー情報データ取得
         * @return   SQLの実行結果
         */
        //public function get_mst1001_data()
        public function get_mst1001_data( $organization_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst1001_data");
                
            $sql = ' select maker_cd,maker_nm,maker_nm_rk,maker_kn,maker_kn_rk from mst1001';
            //$sql .= '  WHERE 1 = :maker_cd  ';
            $sql .= '  WHERE organization_id = :organization_id  ';
            $sql .= '  ORDER BY maker_cd';
                
            //$searchArray = array( ':maker_cd' => 1 );                
            $searchArray = array( ':organization_id' => $organization_id );
                
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
                
            // 一覧表を格納する空の配列宣言
            $mst1001DataList = array();
                
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst1001_data");
                return $mst1001DataList;
            }
                
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst1001DataList, $data);
            }
                
            $Log->trace("END get_mst1001_data");
                
            return $mst1001DataList;
        }
            
        /**
         * 商品メーカーマスタデータ登録
         * @param   $post           POSTデータ
         * @param   &$aryErrMsg     エラーメッセージ配列
         * @return
         */
        public function BatchRegist($post, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START BatchRegist");
                
            // 組織ID
            $organization_id = $post['organization_id'];
                
            // トランザクション開始
            $DBA->beginTransaction();
                
            // 削除
            if (isset($post['del_data']) === true){
                $del_data = json_decode($post['del_data'], true);
                //$mst1001->Delete($del_data);
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
                //$mst1001->Insert($new_data);
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
                //$mst1001->Update($upd_data);
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
        //org_list kanderu 2020/08/26
        public function org_list( $postArray, &$searchArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START org_list"); 
        $sql   ="";
        $sql  .= "select  ";
        $sql  .= "organization_id  ";
        $sql  .= "from m_organization_detail ";
        $sql  .= "where organization_id <> 1  ";
        $sql  .= "order by organization_id  ";
        $result = $DBA->executeSQL($sql); 
            // 一覧表を格納する空の配列宣言
            $Datas = [];
           print_r($sql);
           echo '';
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END org_list");
                return $Datas;
            }
                
            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    //print_r($data);
                    $Datas[] = $data;
            }
                
            $Log->trace("END org_list");
                
            return $Datas;
                
        }
            
        //kanderu
            
        /**
         * 商品メーカーマスタ新規データ登録
         * @param
         * @return   SQLの実行結果
         */
        public function Insert($organization_id, $data, &$aryErrMsg)
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
//EDITSTR 2020/08/26 kanderu
           //$query .= "insert into mst1001 (";       
            $query .= "insert into ". $_SESSION["SCHEMA"].".mst1001 (";
//EDITEND 2020/08/26 kanderu            
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,MAKER_CD          ";
            $query .= "    ,MAKER_NM          ";
            $query .= "    ,MAKER_NM_RK       ";
            $query .= "    ,MAKER_KN          ";
            $query .= "    ,MAKER_KN_RK       ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:MAKER_CD         ";
            $query .= "    ,:MAKER_NM         ";
            $query .= "    ,:MAKER_NM_RK      ";
            $query .= "    ,:MAKER_KN         ";
            $query .= "    ,:MAKER_KN_RK      ";
            $query .= "  )                    ";    
            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['maker_cd']) === false){
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
                $param[":MAKER_CD"]         = $data[$sind]["maker_cd"];
                $param[":MAKER_NM"]         = $data[$sind]["maker_nm"];
                $param[":MAKER_NM_RK"]      = $data[$sind]["maker_nm_rk"];
                $param[":MAKER_KN"]         = $data[$sind]["maker_kn"];
                $param[":MAKER_KN_RK"]      = $data[$sind]["maker_kn_rk"];
                    
//EDITSTR 2020/08/26 kanderu
           // $result = $DBA->executeSQL($query, $param); 
            $result = $DBA->executeSQL_no_searchpath($query,$param);       
//EDITEND 2020/08/26 kanderu  
          
                if ($result === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました。(メーカーコード：'.$data[$sind]["maker_cd"].')';
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
         * 商品メーカーマスタ既存データ更新
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
//EDITSTR 2020/08/25 kanderu
                // $query .=  " update mst1001 set  "; 
                $query .=  " update ". $_SESSION["SCHEMA"].".mst1001 set  "; 
//EDITEND 2020/08/25 kanderu
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
              //  $param[":organization_id"]  = $organization_id;
                  
                $updkey_cd = '';
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['updkey_maker_cd'])){
                    foreach ($data[$sind] as $key => $value){
                        $do_query = 0;
                        if ($key === 'updkey_maker_cd'){
                            $updkey_cd = $value;
                            $param[":updkey_maker_cd"] = $updkey_cd;
                            continue;
                        }
                        if (is_null($value) === false){
                            if ($key === 'maker_cd'){
                                if (is_numeric($value) === false){
                                    continue;
                                }
                                // 他テーブル使用状況チェック
                                if ($this->UsedTableCheck($organization_id, $updkey_cd) === false){
                                    $aryErrMsg[] = '他のマスタで使用されているためコード変更できません。(メーカーコード：'.$updkey_cd.')';
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
                    $query .= " where    maker_cd          = :updkey_maker_cd      ";
//EDITSTR 2020/08/25 kanderu
                    //$result = $DBA->executeSQL($query, $param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
//EDITSTR 2020/08/25 kanderu
                    if ($result === false){
                        $aryErrMsg[] = '更新処理中にエラーが発生しました。(メーカーコード：'.$updkey_cd.')';
                        $Log->trace("END   Update");
                        return false;
                    }
                }
            }
            $Log->trace("END   Update");
            return true;
        }
            
        /**
         * 商品メーカーマスタ既存データ削除
         * @param    $organization_id：組織ID
         * @param    $sect_cd：部門コード
         * @return   SQLの実行結果
         */
        //public function Delete($data){
        public function Delete($organization_id, $data, &$aryErrMsg){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Delete");
                
            $query  = "";
//EDITSTR 2020/08/25 kanderu
            //$query .= " delete from mst1001 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].". mst1001 ";
//EDITEND 2020/08/25 kanderu
          // $query .= " where organization_id = :organization_id";
            $query .= " where   maker_cd = :maker_cd";
                
            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['maker_cd']) === false){
                    continue;
                }
                // 他テーブル使用状況チェック
                if ($this->UsedTableCheck($organization_id, $data[$sind]['maker_cd']) === false){
                    $aryErrMsg[] = '他のマスタで使用されているため削除できません。(メーカーコード：'.$data[$sind]["maker_cd"].')';
                    continue;
                }
                $param = array();
              //  $param[":organization_id"]  = $organization_id;
                $param[":maker_cd"]         = $data[$sind]['maker_cd'];
//EDITSTR 2020/08/25 kanderu
                //$result = $DBA->executeSQL($query, $param);
                 $result = $DBA->executeSQL_no_searchpath($query, $param);
 //EDITEND 2020/08/25 kanderu
                if ($result === false){
                    $aryErrMsg[] = '削除処理中にエラーが発生しました。(メーカーコード：'.$data[$sind]["maker_cd"].')';
                    $Log->trace("END   Update");
                    return false;
                }
            }
            $Log->trace("END   Update");
            return true;
        }
            
        /**
         * 他テーブル使用状況チェック処理
         * @param   $organization_id    組織ID
         * @param   maker_cd            メーカーコード
         * @return boolean
         */
        function UsedTableCheck($organization_id, $maker_cd) {
            
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START UsedTableCheck");
                
            $searchArray = array(
                ':organization_id'      => $organization_id,
                ':maker_cd'             => $maker_cd
            );
                
            $sqlArray = array(
                // 商品部門マスタ
                'SELECT COUNT(prod_cd) AS CNT FROM mst0201 WHERE organization_id = :organization_id AND maker_cd = :maker_cd',      // 商品マスタ
                'SELECT COUNT(prod_cd) AS CNT FROM mst0211 WHERE organization_id = :organization_id AND maker_cd = :maker_cd',      // 予約商品マスタ
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