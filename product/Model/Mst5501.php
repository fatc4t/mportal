<?php
    /**
     * @file      商品自社分類情報
     * @author    川橋
     * @date      2019/01/22
     * @version   1.00
     * @note      商品自社分類情報テーブルの管理を行う
     */

    // BaseProducts.phpを読み込む
    require './Model/BaseProduct.php';

    /**
     * 商品部門分類情報クラス
     * @note   商品部門分類情報テーブルの管理を行う。
     */
    class Mst5501 extends BaseProduct
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
//        public function getMst5501Data( $type_cd )
//        public function getMst5501Data( $organization_id, $type_cd )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getMst5501Data");
//
//            $sql  = " SELECT ";
//            $sql .= "     INSDATETIME";
//            $sql .= "    ,UPDDATETIME";
//            $sql .= "    ,ORGANIZATION_ID";
//            $sql .= "    ,TYPE_CD";
//            $sql .= "    ,TYPE_NM";
//            $sql .= "    ,TYPE_KN";
//            $sql .= "  from mst5501";
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
//            $mst5501DataList = array();
//
//            // データ取得ができなかった場合、空の配列を返す
//            if( $result === false )
//            {
//                $Log->trace("END getMst5501Data");
//                return $mst5501DataList;
//            }
//
//            // 取得したデータ群を配列に格納
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                $mst5501DataList = $data;
//            }
//
//            $Log->trace("END getMst5501Data");
//
//            return $mst5501DataList;
//        }

        /**
         * 商品自社分類情報データ取得
         * @param    
         * @return   SQLの実行結果
         */
        public function searchMst5501Data()
        {
            global $DBA, $Log; // searchMst5501Data変数宣言
            $Log->trace("START searchMst5501Data");

//            $sql = ' SELECT priv_class_cd,priv_class_nm,priv_class_kn from mst5501 where 1 = :priv_class_cd order by priv_class_cd desc';
            $sql = ' SELECT organization_id, priv_class_cd,priv_class_nm,priv_class_kn from mst5501 where 1 = :priv_class_cd order by priv_class_cd desc';
            $searchArray = array(':type_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst5501DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END searchMst5501Data");
                return $mst5501DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst5501DataList, $data);
            }

            $Log->trace("END searchMst5501Data");

            return $mst5501DataList;
        }

        /**
         * 商品自社分類情報データ取得
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
            public function getMst5501List()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMst5501List");

//            $sql = " SELECT string_agg(priv_class_cd,',') as list from mst5501 where 1 = :priv_class_cd ";            
            $sql = " SELECT string_agg(organization_id||':'||priv_class_cd,',') as list from mst5501 where 1 = :priv_class_cd ";            
            $searchArray = array(':priv_class_cd' => 1);

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst5501DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getMst5501List");
                return $mst5501DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $mst5501DataList = $data;
            }

            $Log->trace("END getMst5501List");

            return $mst5501DataList;
        }   
        

        /**
         * 商品部門分類情報データ取得
         * @return   SQLの実行結果
         */
        //public function get_mst5501_data()
        public function get_mst5501_data( $organization_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst5501_data");

            $sql = ' select priv_class_cd,priv_class_nm,priv_class_kn from mst5501';
            //$sql .= '  WHERE 1 = :priv_class_cd  ';
            $sql .= '  WHERE organization_id = :organization_id  ';
            $sql .= '  ORDER BY priv_class_cd';

            //$searchArray = array( ':priv_class_cd' => 1 );                
            $searchArray = array( ':organization_id' => $organization_id );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $mst5501DataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END get_mst5501_data");
                return $mst5501DataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mst5501DataList, $data);
            }

            $Log->trace("END get_mst5501_data");

            return $mst5501DataList;
        }

        /**
         * 商品自社分類マスタデータ登録
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
                //$mst5501->Delete($del_data);
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
                //$mst5501->Insert($new_data);
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
                //$mst5501->Update($upd_data);
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
         * 商品自社分類マスタ新規データ登録
         * @param
         * @return   SQLの実行結果
         */
        //public function Insert($data)
        public function Insert($organization_id, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Insert");

            // 操作対象テーブルが1個のためトランザクションは使用しない

            $query  = "";
            $query .= "insert into mst5501 (";
            $query .= "     INSUSER_CD        ";
            $query .= "    ,INSDATETIME       ";
            $query .= "    ,UPDUSER_CD        ";
            $query .= "    ,UPDDATETIME       ";
            $query .= "    ,DISABLED          ";
            $query .= "    ,LAN_KBN           ";
            $query .= "    ,CONNECT_KBN       ";
            $query .= "    ,ORGANIZATION_ID   ";
            $query .= "    ,PRIV_CLASS_CD     ";
            $query .= "    ,PRIV_CLASS_NM     ";
            $query .= "    ,PRIV_CLASS_KN     ";
            $query .= "  ) values (           ";
            $query .= "     :INSUSER_CD       ";
            $query .= "    ,:INSDATETIME      ";
            $query .= "    ,:UPDUSER_CD       ";
            $query .= "    ,:UPDDATETIME      ";
            $query .= "    ,:DISABLED         ";
            $query .= "    ,:LAN_KBN          ";
            $query .= "    ,:CONNECT_KBN      ";
            $query .= "    ,:ORGANIZATION_ID  ";
            $query .= "    ,:PRIV_CLASS_CD    ";
            $query .= "    ,:PRIV_CLASS_NM    ";
            $query .= "    ,:PRIV_CLASS_KN    ";
            $query .= "  )                    ";

            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['priv_class_cd']) === false){
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
                $param[":ORGANIZATION_ID"]  = $organization_id;
                $param[":PRIV_CLASS_CD"]    = $data[$sind]["priv_class_cd"];
                $param[":PRIV_CLASS_NM"]    = $data[$sind]["priv_class_nm"];
                $param[":PRIV_CLASS_KN"]    = $data[$sind]["priv_class_kn"];

                $result = $DBA->executeSQL($query, $param);
                if ($result === false){
                    $aryErrMsg[] = '追加処理中にエラーが発生しました。(自社分類コード：'.$data[$sind]["priv_class_cd"].')';
                    $Log->trace("END   Insert");
                    return false;
                }
            }
            $Log->trace("END   Insert");
            return true;
        }
        
        /**
         * 商品自社分類マスタ既存データ更新
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
                $query .= " update mst5501 set  ";
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
                $param[":organization_id"]  = $organization_id;

                $updkey_cd = '';
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['updkey_priv_class_cd'])){
                    foreach ($data[$sind] as $key => $value){
                        $do_query = 0;
                        if ($key === 'updkey_priv_class_cd'){
                            $updkey_cd = $value;
                            $param[":updkey_priv_class_cd"] = $updkey_cd;
                            continue;
                        }
                        if (is_null($value) === false){
                            if ($key === 'priv_class_cd'){
                                if (is_numeric($value) === false){
                                    continue;
                                }
                                // 他テーブル使用状況チェック
                                if ($this->UsedTableCheck($organization_id, $updkey_cd) === false){
                                    $aryErrMsg[] = '他のマスタで使用されているためコード変更できません。(自社分類コード：'.$updkey_cd.')';
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
                    $query .= " where ORGANIZATION_ID   = :organization_id      ";
                    $query .= " and   priv_class_cd     = :updkey_priv_class_cd ";

                    $result = $DBA->executeSQL($query, $param);
                    if ($result === false){
                        $aryErrMsg[] = '更新処理中にエラーが発生しました。(自社分類コード：'.$updkey_cd.')';
                        $Log->trace("END   Update");
                        return false;
                    }
                }
            }
            $Log->trace("END   Update");
            return true;
        }


        /**
         * 商品自社分類マスタ既存データ削除
         * @param    $organization_id：組織ID
         * @param    $sect_cd：部門コード
         * @return   SQLの実行結果
         */
        //public function Delete($data){
        public function Delete($organization_id, $data, &$aryErrMsg)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START Delete");

            $query  = "";
            $query .= " delete from mst5501 ";
            $query .= " where organization_id = :organization_id";
            $query .= " and   priv_class_cd = :priv_class_cd";

            for ($intL = 0; $intL < count($data); $intL ++){
                $sind = '"'.$intL.'"';
                if (is_numeric($data[$sind]['priv_class_cd']) === false){
                    continue;
                }
                // 他テーブル使用状況チェック
                if ($this->UsedTableCheck($organization_id, $data[$sind]['priv_class_cd']) === false){
                    $aryErrMsg[] = '他のマスタで使用されているため削除できません。(自社分類コード：'.$data[$sind]["priv_class_cd"].')';
                    continue;
                }
                $param = array();
                $param[":organization_id"]  = $organization_id;
                $param[":priv_class_cd"]    = $data[$sind]['priv_class_cd'];
                $result = $DBA->executeSQL($query, $param);
                if ($result === false){
                    $aryErrMsg[] = '削除処理中にエラーが発生しました。(自社分類コード：'.$data[$sind]["priv_class_cd"].')';
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
         * @param   $priv_class_cd      自社分類コード
         * @return boolean
         */
        function UsedTableCheck($organization_id, $priv_class_cd) {

            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START UsedTableCheck");

            $searchArray = array(
                ':organization_id'      => $organization_id,
                ':priv_class_cd'        => $priv_class_cd
            );

            $sqlArray = array(
                // 商品部門マスタ
                'SELECT COUNT(prod_cd) AS CNT FROM mst0201 WHERE organization_id = :organization_id AND priv_class_cd = :priv_class_cd',    // 商品マスタ
                'SELECT COUNT(prod_cd) AS CNT FROM mst0211 WHERE organization_id = :organization_id AND priv_class_cd = :priv_class_cd',    // 予約商品マスタ
                'SELECT COUNT(hideseq) AS CNT FROM trn1702 WHERE organization_id = :organization_id AND priv_class_cd = :priv_class_cd',    // 棚卸確定データ明細
                'SELECT COUNT(hideseq) AS CNT FROM jsk4120 WHERE organization_id = :organization_id AND priv_class_cd = :priv_class_cd',    // 顧客購買履歴
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
//         * 商品部門分類データを取得します
//         * @param    $organization_id：組織ID
//         * @return   SQLの実行結果
//         */
//        public function getMst5501Data( $organization_id )
//        {
//            global $DBA, $Log; // グローバル変数宣言
//            $Log->trace("START getMst5501Data");
//
//            // 一覧表を格納する空の配列宣言
//            $mst5501DataList = array();
//
//            if ($organization_id === "") {
//                $Log->trace("END getMst5501Data");
//                return $mst5501DataList;                
//            }
//
//            $sql = ' select organization_id, type_cd, type_nm  from mst5501';
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
//                $Log->trace("END getMst5501Data");
//                return $mst5501DataList;
//            }
//
//            // 取得したデータ群を配列に格納
//            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
//            {
//                array_push( $mst5501DataList, $data);
//            }
//
//            $Log->trace("END getMst5501Data");
//
//            return $mst5501DataList;
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
    }
?>