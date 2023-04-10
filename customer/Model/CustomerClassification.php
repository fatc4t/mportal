<?php
    /**
     * @file      顧客分類マスタ
     * @author    K.Sakamoto
     * @date      2017/07/25
     * @note      顧客分類マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 顧客分類マスタクラス
     * @note   顧客分類マスタテーブルの管理を行う
     */
    class CustomerClassification extends BaseModel
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
         * 顧客分類マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(disabled/codeID/custTypeNm/sort)
         * @return   成功時：$customerClassificationList(cust_type_code/cust_type_nm/disabled)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $customerClassificationDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $customerClassificationDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $customerClassificationDataList, $data);
            }

            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $customerClassificationList = $customerClassificationDataList;
            }
            else

            $Log->trace("END getListData");

            return $customerClassificationList;
        }

        /**
         * 顧客分類マスタ新規データ登録
         * @param    $postArray   入力パラメータ(顧客分類コード/顧客分類名/削除フラグ0/ユーザID/更新組織ID)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            // 追加前チェック
            $ret = $this->customerClassificationPreErrorCheck($postArray);
            if($ret !== "MSG_BASE_0000")
            {
                $errMsg = "顧客分類への登録更新処理にエラーが生じました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewData");
                return $ret;
            }

            $sql = 'INSERT INTO m_customer_classification('
                . '                      cust_type_cd'
                . '                      , cust_type_nm'
                . '                      , disabled'
                . '                      , registration_time'
                . '                      , registration_user_id'
                . '                      , registration_organization'
                . '                      , update_time'
                . '                      , update_user_id'
                . '                      , update_organization'
                . '                      ) VALUES ('
                . '                      :cust_type_cd'
                . '                      , :cust_type_nm'
                . '                      , :disabled'
                . '                      , current_timestamp'
                . '                      , :registration_user_id'
                . '                      , :registration_organization'
                . '                      , current_timestamp'
                . '                      , :update_user_id'
                . '                      , :update_organization)';

            $parameters = array(
                ':cust_type_cd'              => $postArray['custTypeCode'],
                ':cust_type_nm'              => $postArray['custTypeNm'],
                ':disabled'                  => $postArray['disabled'],
                ':registration_user_id'      => $postArray['userID'],
                ':registration_organization' => $postArray['organizationID'],
                ':update_user_id'            => $postArray['userID'],
                ':update_organization'       => $postArray['organizationID'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "m_customer_classification" );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * 登録前チェック
         * @param    $postArray()
         * @return   SQL実行結果（定型文）
         */
        private function customerClassificationPreErrorCheck($postArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START customerPreErrorCheck");

            // 顧客コードの重複チェック
            $custCdCount = 0;
            $custCdCount = $this->getCustCdCount($postArray['custTypeCode']);
            if($custCdCount > 0)
            {
                $Log->trace("END customerClassificationPreErrorCheck");
                return "MSG_ERR_3083";
            } else if ($custCdCount == -1)
            {
                $Log->trace("END customerClassificationPreErrorCheck");
                return "MSG_ERR_3010";
            }
            return "MSG_BASE_0000";
        }

        /* 顧客コード登録数取得
         * @param    $organization_id (登録予定の組織ID)
         * @return   $employeesNoList
         */
        private function getCustCdCount($custCd)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustCdCount");

            $sql = ' SELECT COUNT(cust_type_cd) as cust_type_cd_cnt FROM m_customer_classification '
                 . " WHERE cust_type_cd = :cust_type_cd AND disabled = '0'";   
            $parametersArray = array( ':cust_type_cd' => $custCd, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $custCdCount = 0;
            if( $result === false )
            {
                $Log->trace("END getCustCdCount");
                return -1;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $custCdCount = $data['cust_type_cd_cnt'];
            }

            $Log->trace("END getCustCdCount");

            return $custCdCount;
        }

        /**
         * 顧客分類マスタ登録データ修正
         * @param    $postArray   入力パラメータ(顧客分類ID/顧客分類コード/顧客分類名/ユーザID/更新組織ID)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $id_name = "cust_type_id";
            $inUseArray = $this->getInUseCheckList($id_name);
            // POSTで来た顧客分類IDを数値化する
            $intCustomerClassificationId = intval($postArray['custTypeId']);

            $sql = 'UPDATE m_customer_classification SET'
                . ' cust_type_cd = :cust_type_cd'
                . ' , cust_type_nm = :cust_type_nm'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE cust_type_id = :cust_type_id AND update_time = :update_time ';

            $parameters = array(
                ':cust_type_cd'              => $postArray['custTypeCode'],
                ':cust_type_nm'              => $postArray['custTypeNm'],
                ':update_user_id'            => $postArray['userID'],
                ':update_organization'       => $postArray['organizationID'],
                ':cust_type_id'              => $postArray['custTypeId'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * 顧客分類マスタ登録データ削除
         * @param    $postArray   入力パラメータ(顧客分類ID/削除フラグ1/ユーザID/更新組織ID)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            $id_name = "cust_type_id";
            $inUseArray = $this->getInUseCheckList($id_name);
            $intCustomerClassificationId = intval($postArray['custTypeId']);
            if(in_array($intCustomerClassificationId, $inUseArray))
            {
                return "MSG_WAR_2101";
            }

            $sql = 'UPDATE m_customer_classification SET'
                . ' disabled = :disabled'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE cust_type_id = :cust_type_id AND update_time = :update_time ';

            $parameters = array(
                ':disabled'                  => $postArray['disabled'],
                ':update_user_id'            => $postArray['userID'],
                ':update_organization'       => $postArray['organizationID'],
                ':cust_type_id'          => $postArray['custTypeId'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * 顧客分類マスタ検索用コードのプルダウン
         * @return   コードリスト(コード) 
         */
        public function getSearchCodeList()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchCodeList");

            $sql = ' SELECT DISTINCT cust_type_cd as code FROM m_customer_classification '
                 . ' WHERE disabled = :disabled ORDER BY cust_type_cd ';
            $parametersArray = array( ':disabled' => 0, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $customerClassificationCodeList = array('code' => '');
            
            if( $result === false )
            {
                $Log->trace("END getSearchCodeList");
                return $customerClassificationCodeList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($customerClassificationCodeList, $data);
            }

            $Log->trace("END getSearchCodeList");
            
            return $customerClassificationCodeList;
        }

        /**
         * 検索(顧客分類)プルダウン
         * @param    $authority     アクセス権限名
         * @return   セクションリスト(セクションID/セクション名) 
         */
        public function getSearchCustomerClassificationList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSearchCustomerClassificationList");

            $sql = 'SELECT cust_type_id, cust_type_nm FROM m_customer_classification '
                . ' WHERE disabled = :disabled ORDER BY cust_type_cd';
            $parametersArray = array( ':disabled' => 0, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $custTypeNmList = array(array('cust_type_id' => '', 'cust_type_nm' => ''));
            
            if( $result === false )
            {
                $Log->trace("END getSearchCustomerClassificationList");
                return $custTypeNmList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($custTypeNmList, $data);
            }

            $Log->trace("END getSearchCustomerClassificationList");

            return $custTypeNmList;
        }

        /**
         * 顧客分類マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(disabled/organizationID/custTypeNm/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   顧客分類マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT s.cust_type_id, s.cust_type_cd, s.cust_type_nm, s.disabled, s.update_time '
                 . ' FROM m_customer_classification s ';

            $sqlWhere = '';
            
            if( !empty( $postArray['custTypeNm'] ) )
            {
                if(strlen($sqlWhere) == 0)
                {
                    $sqlWhere .= ' s.cust_type_nm = :custTypeNm ';
                }
                else
                {
                    $sqlWhere .= ' AND s.cust_type_nm = :custTypeNm ';
                }
                $custTypeNmArray = array(':custTypeNm' => $postArray['custTypeNm'],);
                $searchArray = array_merge($searchArray, $custTypeNmArray);
            }
            if( !empty( $postArray['codeID'] ) )
            {
                if(strlen($sqlWhere) == 0)
                {
                    $sqlWhere .= ' s.cust_type_cd = :cust_type_codeID ';
                }
                else
                {
                    $sqlWhere .= ' AND s.cust_type_cd = :cust_type_codeID ';
                }
                $cust_type_codeIDArray = array(':cust_type_codeID' => $postArray['codeID'],);
                $searchArray = array_merge($searchArray, $cust_type_codeIDArray);
            }
            
            if( $postArray['disabled'] == 0 )
            {
                if(strlen($sqlWhere) == 0)
                {
                    $sqlWhere .= ' s.disabled = :disabled ';
                }
                else
                {
                    $sqlWhere .= ' AND s.disabled = :disabled ';
                }
                $disabledArray = array(':disabled' => $postArray['disabled'],);
                $searchArray = array_merge($searchArray, $disabledArray);
            }

            if(strlen($sqlWhere) > 0)
            {
                $sql .= ' WHERE ' . $sqlWhere;
            }

            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");
            
            return $sql;
        }

        /**
         * 顧客分類マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   顧客分類マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = ' ORDER BY s.cust_type_cd';

            // ソート条件作成
            $sortSqlList = array(
                                3       =>  ' ORDER BY s.disabled DESC, s.cust_type_cd',              // 状態の降順
                                4       =>  ' ORDER BY s.disabled, s.cust_type_cd',                   // 状態の昇順
                                5       =>  ' ORDER BY s.cust_type_cd DESC',                          // コードの降順
                                6       =>  ' ORDER BY s.cust_type_cd',                               // コードの昇順
                                9       =>  ' ORDER BY s.cust_type_nm DESC, s.cust_type_cd',          // 顧客分類名の降順
                               10       =>  ' ORDER BY s.cust_type_nm, s.cust_type_cd',               // 顧客分類名の昇順
                            );
            // ソート条件
            if( array_key_exists( $sortNo, $sortSqlList ) )
            {
                $sql = $sortSqlList[$sortNo];
            }
            
            $Log->trace("END creatSortSQL");
            
            return $sql;
        }

        /**
         * 顧客分類マスタ更新時使用済み顧客分類データ組織ID取得
         * @param    $customerClassificationID   顧客分類ID
         * @return   組織ID
         */
        private function getUseDataOrganizationId( $customerClassificationID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUseDataOrganizationId");

            $sql = 'SELECT s.organization_id FROM m_customer_classification s WHERE s.cust_type_id = :cust_type_id';
            $searchArray = array(':cust_type_id' => $customerClassificationID,);
            // sql実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            if( $result === false )
            {
                $Log->trace("END getUseDataOrganizationId");
                return -1;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $organization_id = $data['organization_id'];
            }

            $Log->trace("END getUseDataOrganizationId");

            return $organization_id;
        }
    }
?>
