<?php
    /**
     * @file      商品入数構成情報
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      商品入数構成情報テーブルの管理を行う
     */

    // BaseProducts.phpを読み込む
    require './Model/BaseProduct.php';

    /**
     * 商品入数構成情報クラス
     * @note   商品入数構成情報テーブルの管理を行う。
     */
    class Mst5102 extends BaseProduct
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
//        public function getMst5102Data( $type_cd )
//        public function getMst5102Data( $organization_id, $type_cd )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getMst5102Data");
//
//            $sql  = " SELECT ";
//            $sql .= "     INSDATETIME";
//            $sql .= "    ,UPDDATETIME";
//            $sql .= "    ,ORGANIZATION_ID";
//            $sql .= "    ,TYPE_CD";
//            $sql .= "    ,TYPE_NM";
//            $sql .= "    ,TYPE_KN";
//            $sql .= "  from mst5102";
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
//            $mst5102DataList = array();
//
//            // データ取得ができなかった場合、空の配列を返す
//            if( $result === false )
//            {
//                $Log->trace("END getMst5102Data");
//                return $mst5102DataList;
//            }
//
//            // 取得したデータ群を配列に格納
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                $mst5102DataList = $data;
//            }
//
//            $Log->trace("END getMst5102Data");
//
//            return $mst5102DataList;
//        }

        /**
         * 商品入数構成情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        public function searchMst5102Data()
        {
            global $DBA, $Log; // searchMst5102Data変数宣言
            $Log->trace("START searchMst5102Data");

//            $sql = ' SELECT type_cd,type_nm,type_kn from mst5102 where 1 = :type_cd order by type_cd desc';
            $sql = ' SELECT organization_id, type_cd,type_nm,type_kn from mst5102 where 1 = :type_cd order by type_cd desc';
            $searchArray = array(':type_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst5102DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END searchMst5102Data");
                return $mst5102DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst5102DataList, $data);
            }

            $Log->trace("END searchMst5102Data");

            return $mst5102DataList;
        }

//        /**
//         * 商品入数構成情報データ取得
//         * @param    $user_detail_id
//         * @return   SQLの実行結果
//         */
//            public function getMst5102List()
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getMst5102List");
//
//            $sql = " SELECT string_agg(type_cd,',') as list from mst5102 where 1 = :type_cd ";            
//            $sql = " SELECT string_agg(organization_id||':'||type_cd,',') as list from mst5102 where 1 = :type_cd ";            
//            $searchArray = array(':type_cd' => 1);
//
//            // SQLの実行
//            $result = $DBA->executeSQL($sql, $searchArray);
//
//            // 一覧表を格納する空の配列宣言
//            $mst5102DataList = array();
//
//            // データ取得ができなかった場合、空の配列を返す
//            if( $result === false )
//            {
//                $Log->trace("END getMst5102List");
//                return $mst5102DataList;
//            }
//
//            // 取得したデータ群を配列に格納
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                $mst5102DataList = $data;
//            }
//
//            $Log->trace("END getMst5102List");
//
//            return $mst5102DataList;
//        }   
        
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
         * 商品入数構成情報データ取得
         * @return   SQLの実行結果
         */
        //public function get_mst5102_data()
        public function get_mst5102_data( $aryKeys )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst5102_data");
            
            if (count($aryKeys) === 0) {
                $Log->trace("END get_mst5102_data");
                return array();
            }
            $sql  = " SELECT";
            $sql .= "   mst5102.organization_id                             AS organization_id";
            $sql .= "  ,mst5102.prod_cd                                     AS prod_cd";
            $sql .= "  ,TO_CHAR(mst5102.setprod_amount, 'FM999,999,990')    AS setprod_amount";
            $sql .= "  ,mst5102.order_object                                AS order_object";
            $sql .= "  ,mst0201.prod_kn_rk                                  AS prod_kn_rk";
            $sql .= "  ,mst0201.prod_capa_nm                                AS prod_capa_nm";
            $sql .= "  ,TO_CHAR(mst0201.saleprice,      'FM999,999,990')    AS saleprice";
            $sql .= "  ,TO_CHAR(mst0201.head_costprice, 'FM999,999,990.00') AS costprice";
            $sql .= " FROM mst5102";
            $sql .= " INNER JOIN mst0201";
            $sql .= " ON (mst0201.organization_id = mst5102.organization_id AND mst0201.prod_cd = mst5102.prod_cd)";
//            $sql .= '  WHERE 1 = :type  ';
            $sql .= '  WHERE mst5102.organization_id = :organization_id  ';
            $sql .= '  AND   mst5102.setprod_cd      = :setprod_cd';

//            $searchArray = array( ':type' => 1 );                
            $searchArray = array(
                ':organization_id'  => $aryKeys['organization_id'],
                ':setprod_cd'       => $aryKeys["setprod_cd"]
            );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst5102DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst5102_data");
                return $mst5102DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst5102DataList, $data);
            }

            $Log->trace("END get_mst5102_data");

            return $mst5102DataList;
        }
        
        /**
         * 商品入数構成マスタデータ登録
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
                //$mst5102->Delete($del_data);
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
                //$mst5102->Insert($new_data);
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
                //$mst5102->Update($upd_data);
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
         * 商品入数構成マスタ新規データ登録
         * @param
         * @return   SQLの実行結果
         */
        //public function Insert($data)
        public function Insert($aryKeys, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Insert");

            $query  = "";
            $query .= "insert into mst5102 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DATA_MAKE_KBN     ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,PROD_CD           ";
            $query .= "    ,SETPROD_CD        ";
            $query .= "    ,SETPROD_AMOUNT    ";
            $query .= "    ,ORDER_OBJECT      ";
            $query .= "    ,DATA_UPD_KBN      ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DATA_MAKE_KBN    ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:PROD_CD          ";
            $query .= "    ,:SETPROD_CD       ";
            $query .= "    ,:SETPROD_AMOUNT   ";
            $query .= "    ,:ORDER_OBJECT     ";
            $query .= "    ,:DATA_UPD_KBN     ";
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
                $param[":DATA_MAKE_KBN"]    = '0';
                $param[":LAN_KBN"]          = '0';
                $param[":CONNECT_KBN"]      = '0';
                $param[":ORGANIZATION_ID"]  = $aryKeys["organization_id"];
                $param[":PROD_CD"]          = $data[$sind]["prod_cd"];
                $param[":SETPROD_CD"]       = $aryKeys["setprod_cd"];
                $param[":SETPROD_AMOUNT"]   = $data[$sind]["setprod_amount"];
                $param[":ORDER_OBJECT"]     = $data[$sind]["order_object"] === "" ? "0" : "1";
                $param[":DATA_UPD_KBN"]     = "0";

                $result = $DBA->executeSQL($query, $param);
                if ($result === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました。(商品入数構成コード：'.$aryKeys["setprod_cd"].'/'.$data[$sind]["prod_cd"].')';
                    $Log->trace("END   Insert");
                    return false;
                }
                
                // 商品入数更新記録(TRN3031)
                if ($this->InsTrn3031($aryKeys["organization_id"], $data[$sind]["prod_cd"], $aryKeys["setprod_cd"], '1') === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました。(商品入数構成コード：'.$aryKeys["setprod_cd"].'/'.$data[$sind]["prod_cd"].')';
                    $Log->trace("END   Insert");
                    return false;
                }
                
                // 商品マスタ　セット商品区分更新(親:2)
                if ($this->SetProdKbnUpd($aryKeys["organization_id"], $data[$sind]['prod_cd'], "2") === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました。(商品入数構成コード：'.$aryKeys["setprod_cd"].'/'.$data[$sind]["prod_cd"].')';
                    $Log->trace("END   Insert");
                    return false;
                }
            }

            // 商品マスタ　セット商品区分更新(子:1)
            if ($this->SetProdKbnUpd($aryKeys["organization_id"], $aryKeys["setprod_cd"], "1") === false){
                $aryErrMsg[] = '追加処理中にエラーが発生しました。(商品入数構成コード：'.$aryKeys["setprod_cd"].'/'.$data[$sind]["prod_cd"].')';
                $Log->trace("END   Insert");
                return false;
            }

            $Log->trace("END   Insert");
            return true;
        }
        
        /**
         * 商品入数構成マスタ既存データ更新
         * @param
         * @return   SQLの実行結果
         */
        //public function Update($data)
        public function Update($aryKeys, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Update");

            // $data配列には主キーと変更項目の2要素が1行ごとに時系列で格納されている
            // 同じ変更項目が格納されることもあるが後優先である
            for ($intL = 0; $intL < count($data); $intL ++){
                $new_prod_cd = '';
                //
                $query  = "";
                $query .= " update mst5102 set  ";
                $query .= "  UPDUSER_CD         = :UPDUSER_CD        , ";
                $query .= "  UPDDATETIME        = :UPDDATETIME       , ";
                $query .= "  DATA_MAKE_KBN      = :DATA_MAKE_KBN     , ";
                $query .= "  LAN_KBN            = :LAN_KBN           , ";
                $query .= "  CONNECT_KBN        = :CONNECT_KBN       , ";

                $param = array();
                $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
                $param[":UPDDATETIME"]      = "now()";
                $param[":DATA_MAKE_KBN"]    = '0';
                $param[":LAN_KBN"]          = '0';
                $param[":CONNECT_KBN"]      = '0';
                $param[":organization_id"]  = $aryKeys["organization_id"];
                $param[":setprod_cd"]       = $aryKeys["setprod_cd"];

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
                            $param[':'.$key] = $value;
                        }
                    }
                    if ($do_query === 0){
                        continue;
                    }
                    $query .= " where ORGANIZATION_ID   = :organization_id      ";
                    $query .= " and   setprod_cd        = :setprod_cd           ";
                    $query .= " and   prod_cd           = :updkey_prod_cd       ";

                    $result = $DBA->executeSQL($query, $param);
                    if ($result === false){
                        $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品入数構成コード：'.$aryKeys["setprod_cd"].'/'.$updkey_cd.')';
                        $Log->trace("END   Update");
                        return false;
                    }

                    // 商品マスタ　セット商品区分更新
                    if ($new_prod_cd !== ''){
                        // 商品入数構成マスタ存在チェック
                        if ($this->SetProdIsExist($aryKeys["organization_id"], $updkey_cd, "") === false){
                            // 商品マスタ　セット商品区分更新
                            if ($this->SetProdKbnUpd($aryKeys["organization_id"], $updkey_cd, "0") === false){
                                $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品入数構成コード：'.$aryKeys["setprod_cd"].'/'.$updkey_cd.')';
                                $Log->trace("END   Update");
                                return false;
                            }
                        }
                        // 商品マスタ　セット商品区分更新(親:2)
                        if ($this->SetProdKbnUpd($aryKeys["organization_id"], $new_prod_cd, "2") === false){
                            $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品入数構成コード：'.$aryKeys["setprod_cd"].'/'.$updkey_cd.')';
                            $Log->trace("END   Update");
                            return false;
                        }

                        // 商品入数更新記録(TRN3031)
                        //※prod_cd = $updkey_cd, upd_type = '3'(削除) / prod_cd = $new_prod_cd, upd_type = '1'(新規)
                        
                    }
                    else{
                        // 商品入数更新記録(TRN3031)
                        //※prod_cd = $updkey_cd, upd_type = '2'(更新)
                        
                    }
                }
            }

            // 商品マスタ　セット商品区分更新(子:1)
            if ($this->SetProdKbnUpd($aryKeys["organization_id"], $aryKeys["setprod_cd"], "1") === false){
                $aryErrMsg[] = '更新処理中にエラーが発生しました。(商品入数構成コード：'.$aryKeys["setprod_cd"].'/'.$updkey_cd.')';
                $Log->trace("END   Insert");
                return false;
            }

            $Log->trace("END   Update");
            return true;
        }

        /**
         * 商品入数構成マスタ既存データ削除
         * @param    $organization_id：組織ID
         * @param    $sect_cd：部門コード
         * @return   SQLの実行結果
         */
        //public function Delete($data){
        public function Delete($aryKeys, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Delete");

            $query  = "";
            $query .= " delete from mst5102 ";
            $query .= " where organization_id = :organization_id";
            $query .= " and   prod_cd         = :prod_cd";
            $query .= " and   setprod_cd      = :setprod_cd";

            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['prod_cd']) === false || is_numeric($aryKeys['setprod_cd']) === false){
                    continue;
                }
                $param = array();
                $param[":organization_id"]  = $aryKeys["organization_id"];
                $param[":setprod_cd"]       = $aryKeys["setprod_cd"];
                $param[":prod_cd"]          = $data[$sind]['prod_cd'];

                $result = $DBA->executeSQL($query, $param);
                if ($result === false){
                    $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品入数構成コード：'.$aryKeys["setprod_cd"].'/'.$data[$sind]["prod_cd"].')';
                    $Log->trace("END   Delete");
                    return false;
                }

                // 商品入数構成マスタ存在チェック
                if ($this->SetProdIsExist($aryKeys["organization_id"], $data[$sind]['prod_cd'], "") === false){
                    // 商品マスタ　セット商品区分更新
                    if ($this->SetProdKbnUpd($aryKeys["organization_id"], $data[$sind]['prod_cd'], "0") === false){
                        $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品入数構成コード：'.$aryKeys["setprod_cd"].'/'.$data[$sind]["prod_cd"].')';
                        $Log->trace("END   Delete");
                        return false;
                    }
                }
                
                // 商品入数更新記録(TRN3031)
                if ($this->InsTrn3031($aryKeys["organization_id"], $data[$sind]["prod_cd"], $aryKeys["setprod_cd"], '3') === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました。(商品入数構成コード：'.$aryKeys["setprod_cd"].'/'.$data[$sind]["prod_cd"].')';
                    $Log->trace("END   Insert");
                    return false;
                }
                
            }
            $Log->trace("END   Delete");
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
        
//        /**
//         * システムマスタデータを取得します
//         * @param    $organization_id：組織ID
//         * @return   SQLの実行結果
//         */
//        public function getMst0010Data( $organization_id )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getMst0010Data");
//
//            // 一覧表を格納する空の配列宣言
//            $mst0010DataList = array();
//
//            if ($organization_id === "") {
//                $Log->trace("END getMst0010Data");
//                return $mst0010DataList;                
//            }
//
//            $sql = ' select *  from mst0010';
//            $sql .= "  WHERE disabled = :disabled  ";
//            $sql .= "  AND   organization_id = :organization_id  ";
//            $sql .= "  AND   sys_cd = :sys_cd  ";
//
//            $searchArray = array(
//                ':disabled'         => '0',
//                ':organization_id'  => $organization_id,
//                ':sys_cd'           => '1'
//            );
//
//            // SQLの実行
//            $result = $DBA->executeSQL($sql, $searchArray);
//
//            // データ取得ができなかった場合、空の配列を返す
//            if( $result === false )
//            {
//                $Log->trace("END getMst0010Data");
//                return $mst0010DataList;
//            }
//
//            // 取得したデータ群を配列に格納
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push( $mst0010DataList, $data);
//            }
//
//            $Log->trace("END getMst00105Data");
//
//            return $mst0010DataList;
//        } 
//
//        /**
//         * 商品入数構成データを取得します
//         * @param    $organization_id：組織ID
//         * @return   SQLの実行結果
//         */
//        public function getMst5102Data( $organization_id )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getMst5102Data");
//
//            // 一覧表を格納する空の配列宣言
//            $mst5102DataList = array();
//
//            if ($organization_id === "") {
//                $Log->trace("END getMst5102Data");
//                return $mst5102DataList;                
//            }
//
//            $sql = ' select organization_id, type_cd, type_nm  from mst5102';
//            $sql .= "  WHERE disabled = :disabled  ";
//            $sql .= "  AND   organization_id = :organization_id  ";
//            $sql .= "  ORDER BY type_cd  ";
//
//            $searchArray = array(
//                ':disabled'         => '0',
//                ':organization_id'  => $organization_id
//            );
//
//            // SQLの実行
//            $result = $DBA->executeSQL($sql, $searchArray);
//
//            // データ取得ができなかった場合、空の配列を返す
//            if( $result === false )
//            {
//                $Log->trace("END getMst5102Data");
//                return $mst5102DataList;
//            }
//
//            // 取得したデータ群を配列に格納
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push( $mst5102DataList, $data);
//            }
//
//            $Log->trace("END getMst5102Data");
//
//            return $mst5102DataList;
//        } 
//
//        /**
//         * 商品データを取得します
//         * @param    $organization_id：組織ID
//         * @return   SQLの実行結果
//         */
//        public function getMst0201Data( $organization_id )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getMst0201Data");
//
//            // 一覧表を格納する空の配列宣言
//            $mst0201DataList = array();
//
//            if ($organization_id === "") {
//                $Log->trace("END getMst0201Data");
//                return $mst0201DataList;                
//            }
//
//            $sql = ' select organization_id, prod_cd, prod_nm  from mst0201';
//            $sql .= "  WHERE disabled = :disabled  ";
//            $sql .= "  AND   organization_id = :organization_id  ";
//            $sql .= "  ORDER BY prod_cd  ";
//            $sql .= "  limit 20  ";     // for DEBUG
//
//            $searchArray = array(
//                ':disabled'         => '0',
//                ':organization_id'  => $organization_id
//            );
//
//            // SQLの実行
//            $result = $DBA->executeSQL($sql, $searchArray);
//
//            // データ取得ができなかった場合、空の配列を返す
//            if( $result === false )
//            {
//                $Log->trace("END getMst0201Data");
//                return $mst0201DataList;
//            }
//
//            // 取得したデータ群を配列に格納
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push( $mst0201DataList, $data);
//            }
//
//            $Log->trace("END getMst0201Data");
//
//            return $mst0201DataList;
//        } 
        
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
            //return array_slice($mst0201DataList, 0, 10000);
        }
        
        /**
         * 商品入数構成マスタ存在チェック
         */
        function SetProdIsExist($organization_id, $prod_cd, $setprod_cd)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START SetProdIsExist");

            $sql  = " SELECT count(*) AS cnt";
            $sql .= " from mst5102";
            $sql .= " WHERE organization_id = :organization_id";
            $searchArray = array(':organization_id' => $organization_id);
            if ($prod_cd !== ""){
                $sql .= " AND   prod_cd = :prod_cd";
                $searchArray = array_merge($searchArray, array(':prod_cd' => $prod_cd));
            }
            if ($setprod_cd !== ""){
                $sql .= " AND   setprod_cd = :setprod_cd";
                $searchArray = array_merge($searchArray, array(':setprod_cd' => $setprod_cd));
            }

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $aryResult = array();

            // データ取得ができなかった場合、falseを返す
            if( $result === false )
            {
                $Log->trace("END SetProdIsExist");
                return false;
            }

            $numRet = 0;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                $numRet = intval($data['cnt']);
            }
            if ($numRet > 0) {
                $Log->trace("END SetProdIsExist");
                return true;
            }

            $Log->trace("END SetProdIsExist");

            return false;
        }
        
        function AllDelete($aryKeys, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START AllDelete");
            
            // トランザクション開始
            $DBA->beginTransaction();

            // 商品入数構成マスタ取得
            $mst5102data = $this->get_mst5102_data($aryKeys);
            
            // 商品入数構成マスタ削除
            $query  = "";
            $query .= " delete from mst5102 ";
            $query .= " where organization_id = :organization_id";
            $query .= " and   setprod_cd      = :setprod_cd";
            $param = array();
            $param[":organization_id"]  = $aryKeys["organization_id"];
            $param[":setprod_cd"]       = $aryKeys["setprod_cd"];
            $result = $DBA->executeSQL($query, $param);
            if ($result === false){
                // ロールバック
                $DBA->rollBack();
                $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品入数構成コード：'.$aryKeys["setprod_cd"].')';
                $Log->trace("END   AllDelete");
                return false;
            }

            for ($i = 0; $i < count($mst5102data); $i ++)
            {
                // 商品入数構成マスタ存在チェック
                if ($this->SetProdIsExist($mst5102data[$i]["organization_id"], $mst5102data[$i]["prod_cd"], "") === false){
                    // 商品マスタ　セット商品区分更新
                    if ($this->SetProdKbnUpd($mst5102data[$i]["organization_id"], $mst5102data[$i]["prod_cd"], "0") === false){
                        // ロールバック
                        $DBA->rollBack();
                        $aryErrMsg[] = '削除処理中にエラーが発生しました。(商品入数構成コード：'.$aryKeys["setprod_cd"].')';
                        $Log->trace("END   AllDelete");
                        return false;
                    }
                }
            }
            
            // 商品入数更新記録(TRN3031)
            
            
            // コミット
            $DBA->commit();
            $Log->trace("END   AllDelete");
            return true;
        }
        
        function SetProdKbnUpd($organization_id, $prod_cd, $set_prod_kbn)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START SetProdKbnUpd");
            
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
                $Log->trace("END SetProdKbnUpd");
                return;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                $mst0201data = $data;
            }
            if ($mst0201data["set_prod_kbn"] !== $set_prod_kbn){
                // 商品マスタ　セット商品区分更新
                $query  = "";
                $query .= " UPDATE mst0201 SET";
                $query .= "   UPDUSER_CD          = :UPDUSER_CD";
                $query .= "  ,UPDDATETIME         = :UPDDATETIME";
                $query .= "  ,DISABLED            = :DISABLED";
                $query .= "  ,LAN_KBN             = :LAN_KBN";
                $query .= "  ,CONNECT_KBN         = :CONNECT_KBN";
                $query .= "  ,SET_PROD_KBN        = :SET_PROD_KBN";
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
                $param[":SET_PROD_KBN"]         = $set_prod_kbn;

                $result = $DBA->executeSQL($query, $param);
                if ($result === false){
                    $Log->trace("END   SetProdKbnUpd");
                    return false;
                }
                
                
                // 商品更新記録(TRN3020)
                
                
            }
            
            $Log->trace("END SetProdKbnUpd");
            return true;
        }
        
        function InsTrn3031($organization_id, $prod_cd, $setprod_cd, $upd_type, $IN_drwRow = array())
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START InsTrn3031");

            // 商品入数情報を取得する
            if (count($IN_drwRow) ===0){
                $sql  = "";
                $sql .= " SELECT *";
                $sql .= "    FROM mst5102";
                $sql .= "  WHERE organization_id = :organization_id";
                $sql .= "  AND   prod_cd         = :prod_cd";
                $sql .= "  AND   setprod_cd      = :setprod_cd";
                $searchArray = array(
                    ':organization_id'  => $organization_id,
                    ':prod_cd'          => $prod_cd,
                    ':setprod_cd'       => $setprod_cd
                );
                // SQLの実行
                $result = $DBA->executeSQL($sql, $searchArray);

                // 一覧表を格納する空の配列宣言
                $mst5102data = array();

                // データ取得ができなかった場合、falseを返す
                if( $result === false )
                {
                    $Log->trace("END InsTrn3031");
                    return;
                }

                while ( $data = $result->fetch(PDO::FETCH_ASSOC) ) {
                    $mst5102data = $data;
                }
            }
            else{
                $mst5102data = $IN_drwRow;
            }
            if (count($mst5102data) ===0){
                $Log->trace("END InsTrn3031");
                return;
            }

            // 商品入数更新記録(TRN3031)
            $query  = "";
            $query .= "insert into trn3031 (";
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
            $query .= "    ,PROD_CD           ";
            $query .= "    ,SETPROD_CD        ";
            $query .= "    ,SETPROD_AMOUNT    ";
            $query .= "    ,ORDER_OBJECT      ";
            $query .= "    ,DATA_UPD_KBN      ";
            $query .= "    ,SETPROD_UPDTIME   ";
            $query .= "    ,VAN_DATA_KBN      ";
            //$query .= "    ,VAN_DATA_DATE     ";
            $query .= "    ,PROD_SYNC_KBN     ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DATA_MAKE_KBN    ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:HIDESEQ          ";
            $query .= "    ,:PROC_DATE        ";
            $query .= "    ,:UPD_TYPE         ";
            $query .= "    ,:PROD_CD          ";
            $query .= "    ,:SETPROD_CD       ";
            $query .= "    ,:SETPROD_AMOUNT   ";
            $query .= "    ,:ORDER_OBJECT     ";
            $query .= "    ,:DATA_UPD_KBN     ";
            $query .= "    ,:SETPROD_UPDTIME  ";
            $query .= "    ,:VAN_DATA_KBN     ";
            //$query .= "    ,:VAN_DATA_DATE    ";
            $query .= "    ,:PROD_SYNC_KBN    ";
            $query .= "  )                    ";
            
            $param = array();
            $param[":INSUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":INSDATETIME"]      = "now()";
            $param[":UPDUSER_CD"]       = $_SESSION["USER_ID"];
            $param[":UPDDATETIME"]      = "now()";
            $param[":DATA_MAKE_KBN"]    = '0';
            $param[":LAN_KBN"]          = '0';
            $param[":CONNECT_KBN"]      = '0';
            $param[":ORGANIZATION_ID"]  = $mst5102data["organization_id"];
            $param[":HIDESEQ"]          = $this->getHideSeq($mst5102data["organization_id"]) + 1;
            $param[":PROC_DATE"]        = date('Ymd');
            $param[":UPD_TYPE"]         = $upd_type;
            $param[":PROD_CD"]          = $mst5102data["prod_cd"];
            $param[":SETPROD_CD"]       = $mst5102data["setprod_cd"];
            $param[":SETPROD_AMOUNT"]   = $mst5102data["setprod_amount"];
            $param[":ORDER_OBJECT"]     = $mst5102data["order_object"];
            $param[":DATA_UPD_KBN"]     = '0';
            $param[":SETPROD_UPDTIME"]  = date('His');
            $param[":VAN_DATA_KBN"]     = '0';
            //$param[":VAN_DATA_DATE"]    = date('YmdHis');
            $param[":PROD_SYNC_KBN"]    = '0';
            
            $result = $DBA->executeSQL($query, $param);
            if ($result === false){
                $Log->trace("END   InsTrn3031");
                return false;
            }
            return true;
        }
        
        function getHideSeq($organization_id){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START InsTrn3031");
            
            $data = "";
            $sql  = "";
            $sql .= " SELECT max(hideseq) hideseq";
            $sql .= "    FROM trn3031";
            $sql .= "  WHERE organization_id = :organization_id";
            $searchArray = array(
                ':organization_id'  => $organization_id
                );
            
            $result = $DBA->executeSQL($sql, $searchArray);
            if ($result === false){
                $Log->trace("END   InsTrn3031");
                return false;
            }
            
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $data = $row['hideseq'];
           
            return $data;
        }
    }
?>