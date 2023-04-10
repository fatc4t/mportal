<?php
    /**
     * @file      顧客備考マスタ
     * @author    K.Sakamoto
     * @date      2017/07/25
     * @note      顧客備考マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 顧客備考マスタクラス
     * @note   顧客備考マスタテーブルの管理を行う
     */
    class CustomerRemarks extends BaseModel
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
         * 顧客備考マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ()
         * @return   成功時：$customerRemarksList(cust_b_code/cust_b_nm/disabled)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $customerRemarksDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $customerRemarksDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $customerRemarksDataList, $data);
            }
            $customerRemarksList = $customerRemarksDataList;

            $Log->trace("END getListData");

            return $customerRemarksList;
        }

        /**
         * 顧客備考マスタ新規データ登録
         * @param    $postArray   入力パラメータ(顧客備考コード/顧客備考名/削除フラグ0/ユーザID/更新組織ID)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            // 追加前チェック
            $ret = $this->customerRemarksPreErrorCheck($postArray, 1);
            if($ret !== "MSG_BASE_0000")
            {
                $errMsg = "顧客備考への登録更新処理にエラーが生じました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewData");
                return $ret;
            }

            $sql = 'INSERT INTO m_customer_remarks('
                . '                      cust_b_type'
                . '                      , cust_b_cd'
                . '                      , cust_b_nm'
                . '                      , disabled'
                . '                      , registration_time'
                . '                      , registration_user_id'
                . '                      , registration_organization'
                . '                      , update_time'
                . '                      , update_user_id'
                . '                      , update_organization'
                . '                      ) VALUES ('
                . '                      :cust_b_type'
                . '                      , :cust_b_cd'
                . '                      , :cust_b_nm'
                . '                      , :disabled'
                . '                      , current_timestamp'
                . '                      , :registration_user_id'
                . '                      , :registration_organization'
                . '                      , current_timestamp'
                . '                      , :update_user_id'
                . '                      , :update_organization)';

            $parameters = array(
                ':cust_b_type'               => $postArray['custBType'],
                ':cust_b_cd'                 => $postArray['custBCd'],
                ':cust_b_nm'                 => $postArray['custBNm'],
                ':disabled'                  => $postArray['disabled'],
                ':registration_user_id'      => $postArray['userID'],
                ':registration_organization' => $postArray['organizationID'],
                ':update_user_id'            => $postArray['userID'],
                ':update_organization'       => $postArray['organizationID'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "m_customer_remarks" );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * 登録前チェック
         * @param    $postArray()
         * @return   SQL実行結果（定型文）
         */
        private function customerRemarksPreErrorCheck($postArray, $proctype)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START customerPreErrorCheck");

            // 顧客分類コードの重複チェック
            $custCdCount = 0;
            $custCdCount = $this->getCustBCdCount($postArray);
            if($custCdCount > 0)
            {
                $Log->trace("END customerRemarksPreErrorCheck");
                $message = "MSG_ERR_3084";
                if($proctype == 2)
                {
                    $message = "MSG_ERR_3090";
                }
                return $message;
            } else if ($custCdCount == -1)
            {
                $Log->trace("END customerRemarksPreErrorCheck");
                return "MSG_ERR_3080";
            }
            return "MSG_BASE_0000";
        }

        /* 顧客分類コード登録数取得
         * @param    $organization_id (登録予定の組織ID)
         * @return   $employeesNoList
         */
        private function getCustBCdCount($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustBCdCount");

            $sql = ' SELECT COUNT(cust_b_cd) as cust_b_cd_cnt FROM m_customer_remarks '
                 . " WHERE cust_b_type = :cust_b_type AND cust_b_cd = :cust_b_cd AND disabled = '0'";   
            $parametersArray = array( ':cust_b_type' => $postArray['custBType'], ':cust_b_cd' => $postArray['custBCd']);
            $result = $DBA->executeSQL($sql, $parametersArray);

            $custBCdCount = 0;
            if( $result === false )
            {
                $Log->trace("END getCustBCdCount");
                return -1;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $custBCdCount = $data['cust_b_cd_cnt'];
            }

            $Log->trace("END getCustBCdCount");

            return $custBCdCount;
        }
        
        /**
         * 顧客備考マスタ登録データ修正
         * @param    $postArray   入力パラメータ
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $sql = 'UPDATE m_customer_remarks SET'
                . ' cust_b_nm = :cust_b_nm'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE cust_b_id = :cust_b_id AND update_time = :update_time ';

            $parameters = array(
                ':cust_b_nm'                 => $postArray['custBNm'],
                ':update_user_id'            => $postArray['userID'],
                ':update_organization'       => $postArray['organizationID'],
                ':cust_b_id'                 => $postArray['custBId'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * 顧客備考マスタ登録データ削除
         * @param    $postArray   入力パラメータ
         * @return   SQLの実行結果
         */
        public function delUpdateData($postArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            $message = $this->getInUseCustomerCheck($postArray);
            if($message !== "MSG_BASE_0000")
            {
                return $message;
            }

            $sql = 'UPDATE m_customer_remarks SET'
                . ' disabled = :disabled'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE cust_b_id = :cust_b_id AND update_time = :update_time ';

            $parameters = array(
                ':disabled'                  => $postArray['disabled'],
                ':update_user_id'            => $postArray['userID'],
                ':update_organization'       => $postArray['organizationID'],
                ':cust_b_id'                 => $postArray['custBId'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * 顧客備考マスタ登録データ復活
         * @param    $postArray   入力パラメータ
         * @return   SQLの実行結果
         */
        public function resUpdateData($postArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START resUpdateData");

            $ret = $this->customerRemarksPreErrorCheck($postArray, 2);
            if($ret !== "MSG_BASE_0000")
            {
                $errMsg = "顧客備考への登録更新処理にエラーが生じました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewData");
                return $ret;
            }

            $sql = 'UPDATE m_customer_remarks SET'
                . ' disabled = :disabled'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE cust_b_id = :cust_b_id AND update_time = :update_time ';

            $parameters = array(
                ':disabled'                  => $postArray['disabled'],
                ':update_user_id'            => $postArray['userID'],
                ':update_organization'       => $postArray['organizationID'],
                ':cust_b_id'                 => $postArray['custBId'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 復活SQLの実行
            $ret = $this->executeOneTableSQL($sql, $parameters);

            $Log->trace("END resUpdateData");

            return $ret;
        }

        /**
         * 顧客備考マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   顧客備考マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT s.cust_b_id, s.cust_b_type, s.cust_b_cd, s.cust_b_nm, s.disabled, s.update_time '
                 . ' FROM m_customer_remarks s ';

            $sqlWhere = '';
            
            if( !empty( $postArray['cust_b_type'] ) )
            {
                if(strlen($sqlWhere) == 0)
                {
                    $sqlWhere .= ' s.cust_b_type = :cust_b_type ';
                }
                else
                {
                    $sqlWhere .= ' AND s.cust_b_type = :cust_b_type ';
                }
                $custBNmArray = array(':cust_b_type' => $postArray['cust_b_type'],);
                $searchArray = array_merge($searchArray, $custBNmArray);
            }

            $sql .= ' WHERE ' . $sqlWhere . ' ORDER BY s.cust_b_cd';

            $Log->trace("END creatSQL");
            
            return $sql;
        }

        /* 顧客備考使用状況確認
         * @param    $postArray                 入力パラメータ
         * @return   SQLの実行結果
         */
        private function getInUseCustomerCheck($postArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getInUseCustomerCheck");
            
            $custCdCount = 0;
            $custCdCount = $this->getCustCdCount($postArray);
            if($custCdCount > 0)
            {
                $Log->trace("END getInUseCustomerCheck");
                return "MSG_WAR_2100";
            } else if ($custCdCount == -1)
            {
                $Log->trace("END getInUseCustomerCheck");
                return "MSG_ERR_3010";
            }

            $Log->trace("END getInUseCustomerCheck");
            
            return "MSG_BASE_0000";
        }
        
        /* 顧客備考コード登録数取得
         * @param    $postArray                 入力パラメータ
         * @return   登録数
         */
        private function getCustCdCount($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustCdCount");

            $sql = ' SELECT COUNT(cust_cd) as cust_cd_cnt FROM m_customer '
                 . " WHERE disabled = '0' AND ";  
            $parametersArray = "";
            if($postArray['custBType'] === '01')
            {
                $sql .= " cust_b_cd1 = :cust_b_cd";
                $parametersArray = array( ':cust_b_cd' => $postArray['custBCd'] , );
            }
            else if($postArray['custBType'] === '02')
            {
                $sql .= " cust_b_cd2 = :cust_b_cd";
                $parametersArray = array( ':cust_b_cd' => $postArray['custBCd'], );
            }
            else if($postArray['custBType'] === '03')
            {
                $sql .= " cust_b_cd3 = :cust_b_cd";
                $parametersArray = array( ':cust_b_cd' => $postArray['custBCd'], );
            }
            else if($postArray['custBType'] === '04')
            {
                $sql .= " cust_b_cd4 = :cust_b_cd";
                $parametersArray = array( ':cust_b_cd' => $postArray['custBCd'], );
            }
            else if($postArray['custBType'] === '05')
            {
                $sql .= " cust_b_cd5 = :cust_b_cd";
                $parametersArray = array( ':cust_b_cd' => $postArray['custBCd'], );
            }
            else if($postArray['custBType'] === '06')
            {
                $sql .= " cust_b_cd6 = :cust_b_cd";
                $parametersArray = array( ':cust_b_cd' => $postArray['custBCd'], );
            }
            else if($postArray['custBType'] === '07')
            {
                $sql .= " cust_b_cd7 = :cust_b_cd";
                $parametersArray = array( ':cust_b_cd' => $postArray['custBCd'], );
            }
            else if($postArray['custBType'] === '08')
            {
                $sql .= " cust_b_cd8 = :cust_b_cd";
                $parametersArray = array( ':cust_b_cd' => $postArray['custBCd'], );
            }
            else if($postArray['custBType'] === '09')
            {
                $sql .= " cust_b_cd9 = :cust_b_cd";
                $parametersArray = array( ':cust_b_cd' => $postArray['custBCd'], );
            }
            else if($postArray['custBType'] === '10')
            {
                $sql .= " cust_b_cd10 = :cust_b_cd";
                $parametersArray = array( ':cust_b_cd' => $postArray['custBCd'], );
            }
            $result = $DBA->executeSQL($sql, $parametersArray);

            $custCdCount = 0;
            if( $result === false )
            {
                $Log->trace("END getCustCdCount");
                return -1;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $custCdCount = $data['cust_cd_cnt'];
            }

            $Log->trace("END getCustCdCount");

            return $custCdCount;
        }
    }
?>
