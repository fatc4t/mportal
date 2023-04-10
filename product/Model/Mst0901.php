<?php
    /**
     * @file      商品区分情報
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      商品区分情報テーブルの管理を行う
     */

    // BaseProducts.phpを読み込む
    require './Model/BaseProduct.php';

    /**
     * 商品区分情報クラス
     * @note   商品区分情報テーブルの管理を行う。
     */
    class Mst0901 extends BaseProduct
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

//      /**
//       * 顧客情報新規データ登録
//       * @param    $postArray(顧客情報、または従業員詳細マスタへの登録情報)
//       * @param    $addFlag(新規登録時にはtrue)
//       * @return   SQLの実行結果
//       */
//        public function addNewData($postArray, $addFlag)
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START addNewData");
//
//            // 操作対象テーブルが1個のためトランザクションは使用しない
//
//            $ret = "";
//            
//            // 追加前チェック
//            if($addFlag)
//            {
//                $ret = $this->customerPreErrorCheck($postArray);
//                if($ret !== "MSG_BASE_0000")
//                {
//                    $errMsg = "顧客情報への登録更新処理にエラーが生じました。";
//                    $Log->warnDetail($errMsg);
//                    $Log->trace("END addNewData");
//                    return $ret;
//                }
//            }
//            
//            // 顧客情報追加
//            $ret = $this->customerProcessingDistribution($postArray, $addFlag);
//            // 顧客情報の追加/更新の結果がfalseの場合ロールバック
//            if($ret !== "MSG_BASE_0000")
//            {
//                $errMsg = "顧客情報への登録更新処理にエラーが生じました。";
//                $Log->warnDetail($errMsg);
//                $Log->trace("END addNewData");
//                return $ret;
//            }
//
//            $Log->trace("END addNewData");
//            return $ret;
//        }
//
//        /**
//         * 顧客情報登録前チェック
//         * @param    $postArray(従業員マスタ、または従業員詳細マスタへの登録情報)
//         * @return   SQL実行結果（定型文）
//         */
//        private function customerPreErrorCheck($postArray)
//        {
//            global $Log; // グローバル変数宣言
//            $Log->trace("START customerPreErrorCheck");
//
//            // 顧客コードの重複チェック
//            $custCdCount = 0;
//            $custCdCount = $this->getCustCdCount($postArray['cust_cd']);
//            if($custCdCount > 0)
//            {
//                $Log->trace("END customerPreErrorCheck");
//                return "MSG_ERR_3081";
//            } else if ($custCdCount == -1)
//            {
//                $Log->trace("END customerPreErrorCheck");
//                return "MSG_ERR_3010";
//            }
//            if($postArray['birth'] !== '')
//            {
//                if(!$this->dateCheck($postArray['birth'], true))
//                {
//                    return "MSG_ERR_3085";
//                }
//                
//            }
//            if($postArray['memorial'] !== '')
//            {
//                if(!$this->dateCheck($postArray['memorial'], true))
//                {
//                    return "MSG_ERR_3086";
//                }
//                
//            }
//            if($postArray['publish'] !== '')
//            {
//                if(!$this->dateCheck($postArray['publish'], false))
//                {
//                    return "MSG_ERR_3087";
//                }
//                
//            }
//            if($postArray['limitdate'] !== '')
//            {
//                if(!$this->dateCheck($postArray['limitdate'], false))
//                {
//                    return "MSG_ERR_3088";
//                }
//                
//            }
//            if($postArray['renew'] !== '')
//            {
//                if(!$this->dateCheck($postArray['renew'], false))
//                {
//                    return "MSG_ERR_3089";
//                }
//                
//            }
//            return "MSG_BASE_0000";
//        }
//        
//        /**
//         * 日付正当性確認
//         * @param    $dateString
//         * @return   判定結果
//         */
//        private function dateCheck($dateString, $isFeature)
//        {
//            list($y, $m, $d) = explode('/', $dateString);
//            if(checkdate($m, $d, $y) !== true)
//            {
//                return false;
//            }
//            if($isFeature)
//            {
//                $now = new DateTime();
//                $now->setTimezone(new DateTimeZone('Asia/Tokyo'));
//                $today = $now->format('Y/m/d');
//                if(strtotime($dateString) > strtotime($today))
//                {
//                    return false;
//                }
//            }
//            return true;
//        }
//
//        /**
//         * 顧客情報追加/更新処理振り分け
//         * @param    $postArray(顧客情報、または従業員詳細マスタへの登録情報)
//         * @param    $addFlag
//         * @param    $customerId(参照して引き渡すため空文字)
//         * @return   SQL実行結果（定型文）
//         */
//        private function customerProcessingDistribution($postArray, $addFlag)
//        {
//            global $Log; // グローバル変数宣言
//            $Log->trace("START customerProcessingDistribution");
//
//            if($addFlag)
//            {
//                // 新規登録
//                $ret = $this->addCustomerNewData($postArray);
//            }
//            else
//            {
//                // 更新
//                $ret = $this->modCustomerUpdateData($postArray);
//            }
//
//            $Log->trace("END customerProcessingDistribution");
//            return $ret;
//        }
        
        
//        /**
//         * 商品部門情報データ取得
//         * @param    $sect_cd
//         * @return   SQLの実行結果
//         */
//        public function getMst0901Data( $type_cd )
//        public function getMst0901Data( $organization_id, $type_cd )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getMst0901Data");
//
//            $sql  = " SELECT ";
//            $sql .= "     INSDATETIME";
//            $sql .= "    ,UPDDATETIME";
//            $sql .= "    ,ORGANIZATION_ID";
//            $sql .= "    ,TYPE_CD";
//            $sql .= "    ,TYPE_NM";
//            $sql .= "    ,TYPE_KN";
//            $sql .= "  from mst0901";
//            $sql .= "  WHERE 1 = 1";
//            $sql .= "  WHERE ORGANIZATION_ID = :organization_id  ";
//            $sql .= "  AND   TYPE_CD = :type_cd  ";
//
//            $searchArray = array(
//                ':organization_id'  => $organization_id,
//                ':type_cd'          => $type_cd );
//
//            // SQLの実行
//            $result = $DBA->executeSQL($sql, $searchArray);
//
//            // 一覧表を格納する空の配列宣言
//            $mst0901DataList = array();
//
//            // データ取得ができなかった場合、空の配列を返す
//            if( $result === false )
//            {
//                $Log->trace("END getMst0901Data");
//                return $mst0901DataList;
//            }
//
//            // 取得したデータ群を配列に格納
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                $mst0901DataList = $data;
//            }
//
//            $Log->trace("END getMst0901Data");
//
//            return $mst0901DataList;
//        }

        /**
         * 商品区分情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        public function searchMst0901Data()
        {
            global $DBA, $Log; // searchMst0901Data変数宣言
            $Log->trace("START searchMst0901Data");

//            $sql = ' SELECT type_cd,type_nm,type_kn from mst0901 where 1 = :type_cd order by type_cd desc';
            $sql = ' SELECT organization_id, type_cd,type_nm,type_kn from mst0901 where 1 = :type_cd order by type_cd desc';
            $searchArray = array(':type_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0901DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END searchMst0901Data");
                return $mst0901DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst0901DataList, $data);
            }

            $Log->trace("END searchMst0901Data");

            return $mst0901DataList;
        }

//        /**
//         * 商品区分情報データ取得
//         * @param    $user_detail_id
//         * @return   SQLの実行結果
//         */
//            public function getMst0901List()
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getMst0901List");
//
//            $sql = " SELECT string_agg(type_cd,',') as list from mst0901 where 1 = :type_cd ";            
//            $sql = " SELECT string_agg(organization_id||':'||type_cd,',') as list from mst0901 where 1 = :type_cd ";            
//            $searchArray = array(':type_cd' => 1);
//
//            // SQLの実行
//            $result = $DBA->executeSQL($sql, $searchArray);
//
//            // 一覧表を格納する空の配列宣言
//            $mst0901DataList = array();
//
//            // データ取得ができなかった場合、空の配列を返す
//            if( $result === false )
//            {
//                $Log->trace("END getMst0901List");
//                return $mst0901DataList;
//            }
//
//            // 取得したデータ群を配列に格納
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                $mst0901DataList = $data;
//            }
//
//            $Log->trace("END getMst0901List");
//
//            return $mst0901DataList;
//        }   
        

        /**
         * 商品区分情報データ取得
         * @return   SQLの実行結果
         */
        //public function get_mst0901_data()
        public function get_mst0901_data( $cmb_prod_k )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst0901_data");
            
            if (count($cmb_prod_k) === 0) {
                $Log->trace("END get_mst0901_data");
                return array();
            }
            $sql  = ' SELECT';
            $sql .= '   prod_k_cd';
            $sql .= '  ,prod_k_nm';
            $sql .= ' FROM mst0901';
//            $sql .= '  WHERE 1 = :type  ';
            $sql .= '  WHERE organization_id = :organization_id  ';
            $sql .= '  AND   prod_k_type     = :prod_k_type';

//            $searchArray = array( ':type' => 1 );                
            $searchArray = array(
                ':organization_id'  => $cmb_prod_k['organization_id'],
                ':prod_k_type'      => str_pad($cmb_prod_k["prod_k_type"], 2, '0', STR_PAD_LEFT)
            );
            $sql .= '  ORDER BY prod_k_cd';

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst0901DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst0901_data");
                return $mst0901DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst0901DataList, $data);
            }

            $Log->trace("END get_mst0901_data");

            return $mst0901DataList;
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
                //$mst0901->Delete($del_data);
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
                //$mst0901->Insert($new_data);
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
                //$mst0901->Update($upd_data);
                if ($this->Update($cmb_prod_k, $upd_data, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
                }
            }
            
            // システムマスタ商品区分名称更新
            if ($this->Update_Mst0010_P_KUBUN($cmb_prod_k, $aryErrMsg) === false){
                    // ロールバック
                    $DBA->rollBack();
                    $Log->trace("END   BatchRegist");
                    return;
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
////ADDSTR 2020/08/26 kanderu
//           //select org id 
            $query   = "";
            $query  .= "select  ";
            $query  .= "organization_id  ";
            $query  .= "from ". $_SESSION["SCHEMA"] .". m_organization_detail ";
            $query  .= "where organization_id <> 1  ";
            $query  .= "order by organization_id  ";
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
             //loop
            if(count($dataList1) != 0){
                foreach ($dataList1 as $orglist1){
////ADDEND kanderu
            $query  = "";
            //$query .= "insert into mst0901 (";
            $query .= "insert into ". $_SESSION["SCHEMA"] .".mst0901 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,PROD_K_TYPE       ";
            $query .= "    ,PROD_K_CD         ";
            $query .= "    ,PROD_K_NM         ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:PROD_K_TYPE      ";
            $query .= "    ,:PROD_K_CD        ";
            $query .= "    ,:PROD_K_NM        ";
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
                $param[":CONNECT_KBN"]      = '0';
//EDITSTR 20201028 kanderu 
                $param[":ORGANIZATION_ID"]  = $orglist1["organization_id"];
 //EDITSTR 20201028 kanderu 
                $param[":PROD_K_TYPE"]      = str_pad($cmb_prod_k["prod_k_type"], 2, '0', STR_PAD_LEFT);
                $param[":PROD_K_CD"]        = $data[$sind]["prod_k_cd"];
                $param[":PROD_K_NM"]        = $data[$sind]["prod_k_nm"];
//EDITSTR 20201028 kanderu
                //$result = $DBA->executeSQL($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
//EDITEND 20201028 kanderu
                if ($result === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました。(商品区分コード：'.$data[$sind]["prod_k_cd"].')';
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
         * 商品区分マスタ既存データ更新
         * @param
         * @return   SQLの実行結果
         */
        public function Update($cmb_prod_k, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Update");

            // $data配列には主キーと変更項目の2要素が1行ごとに時系列で格納されている
            // 同じ変更項目が格納されることもあるが後優先である
            for ($intL = 0; $intL < count($data); $intL ++){
                $query  = "";
                //$query .= " update mst0901 set  ";
                 $query  = "update  ". $_SESSION["SCHEMA"].".mst0901 set  ";
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
              //  $param[":organization_id"]  = $cmb_prod_k["organization_id"];
                $param[":prod_k_type"]      = str_pad($cmb_prod_k["prod_k_type"], 2, '0', STR_PAD_LEFT);

                $updkey_cd = '';
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['updkey_prod_k_cd'])){
                    foreach ($data[$sind] as $key => $value){
                        $do_query = 0;
                        if ($key === 'updkey_prod_k_cd'){
                            $updkey_cd = $value;
                            $param[":updkey_prod_k_cd"] = $updkey_cd;
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
                            $query .= "  $key =    :$key  ";
                            $param[':'.$key] = $value;
                        }
                    }
                    if ($do_query === 0){
                        continue;
                    }
                   // $query .= " where ORGANIZATION_ID   = :organization_id      ";
                    $query .= " where   prod_k_type       = :prod_k_type          ";
                    $query .= " and   prod_k_cd         = :updkey_prod_k_cd     ";

                    //$result = $DBA->executeSQL($query, $param);
                    $result = $DBA->executeSQL_no_searchpath($query, $param);
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

            $query  = "";
            //$query .= " delete from mst0901 ";
            $query .= " delete from ". $_SESSION["SCHEMA"].".mst0901 ";
            //$query .= " where organization_id = :organization_id";
            $query .= " where   prod_k_type     = :prod_k_type";
            $query .= " and   prod_k_cd       = :prod_k_cd";

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
                $param = array();
                //$param[":organization_id"]  = $cmb_prod_k["organization_id"];
                $param[":prod_k_type"]      = str_pad($cmb_prod_k["prod_k_type"], 2, '0', STR_PAD_LEFT);
                $param[":prod_k_cd"]        = $data[$sind]['prod_k_cd'];

                //$result = $DBA->executeSQL($query, $param);
                $result = $DBA->executeSQL_no_searchpath($query, $param);
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
         * システムマスタ　商品区分名称更新処理
         * @global type $DBA
         * @global type $Log
         * @param type $cmb_prod_k
         * @param string $aryErrMsg
         * @return boolean
         */
        function Update_Mst0010_P_KUBUN($cmb_prod_k, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Update_Mst0010_P_KUBUN");

            $query  = "";
//EDITSTR 20201028 kanderu
           // $query .= " update mst0010 set  ";
            $query  = "update  ". $_SESSION["SCHEMA"].".mst0010 set  ";
//EDITEND 20201028 kanderu
            $query .= "  UPDUSER_CD             = :UPDUSER_CD             , ";
            $query .= "  UPDDATETIME            = :UPDDATETIME            , ";
            $query .= "  DISABLED               = :DISABLED               , ";
            $query .= "  LAN_KBN                = :LAN_KBN                , ";
            $query .= "  CONNECT_KBN            = :CONNECT_KBN            , ";
            $query .= "  P_KUBUN".$cmb_prod_k["prod_k_type"]. " = :p_kubun  ";
            //$query .= "  where ORGANIZATION_ID  = :organization_id          ";
            $query .= "  where   SYS_CD           = :sys_cd                   ";
            
            $param = array();
            $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":UPDDATETIME"]      = "now()";
            $param[":DISABLED"]         = '0';
            $param[":LAN_KBN"]          = '0';
            $param[":CONNECT_KBN"]      = '0';
            //$param[":organization_id"]  = $cmb_prod_k["organization_id"];
            $param[":sys_cd"]           = '1';
            $param[":p_kubun"]          = $cmb_prod_k["prod_k_type_nm"];
//EDITSTR 20201028 kanderu 
            //$result = $DBA->executeSQL($query, $param);
             $result = $DBA->executeSQL_no_searchpath($query, $param);
//EDITEND 20201028 kanderu
            if ($result === false){
                $aryErrMsg[] = '更新処理中にエラーが発生しました。(システムマスタ商品区分名称)';
                $Log->trace("END   Update_Mst0010_P_KUBUN");
                return false;
            }
            $Log->trace("END   Update_Mst0010_P_KUBUN");
            return true;
        }
        
        /**
         * 他テーブル使用状況チェック処理
         * @param   $organization_id    組織ID
         * @param   $type_cd            分類コード
         * @return boolean
         */
        function UsedTableCheck($cmb_prod_k, $prod_k_cd) {

            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START UsedTableCheck");

            $sql_where  = "";
            $sql_where .= ' WHERE organization_id = :organization_id';
            $sql_where .= ' AND   prod_k_cd'.$cmb_prod_k["prod_k_type"].' = :prod_k_cd';

            $sqlArray = array(
                'SELECT COUNT(prod_cd) AS CNT FROM mst0201 '.$sql_where,
                'SELECT COUNT(prod_cd) AS CNT FROM mst0211 '.$sql_where,
            );
            
            $searchArray = array(
                ':organization_id'      => $cmb_prod_t["organization_id"],
                ':prod_k_cd'            => $prod_k_cd
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
    }
?>