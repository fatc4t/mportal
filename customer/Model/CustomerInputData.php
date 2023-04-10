<?php
    /**
     * @file      顧客情報
     * @author    K.Sakamoto
     * @date      2017/08/21
     * @version   1.00
     * @note      顧客情報入力画面に表示するデータの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/BaseCustomer.php';

    /**
     * 顧客情報入力画面クラス
     * @note   顧客情報テーブルの管理を行う。
     */
    class CustomerInputData extends BaseCustomer
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
         * 顧客情報データ取得
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
        public function getCustomerData( $cust_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerData");

            $sql = ' SELECT distinct '
                    . " cust_id,
                        cust_cd,
                        cust_nm,
                        cust_kn,
                        zip,
                        addr1,
                        addr2,
                        addr3,
                        tel,
                        fax,
                        hphone,
                        email,
                        imode,
                        to_char(CAST(birth AS DATE), 'yyyy/mm/dd') as birth,
                        sex,
                        to_char(CAST(memorial AS DATE), 'yyyy/mm/dd') as memorial,
                        shop_cd,
                        cust_type_cd,
                        area_cd,
                        staff_cd,
                        introducter,
                        dm_type,
                        option_flag,
                        dissenddm,
                        dissendemail,
                        dissendimode,
                        to_char(CAST(publish AS DATE), 'yyyy/mm/dd') as publish,
                        to_char(CAST(limitdate AS DATE), 'yyyy/mm/dd') as limitdate,
                        to_char(CAST(renew AS DATE), 'yyyy/mm/dd') as renew,
                        fees_rece_kbn,
                        cred_impo_kbn,
                        cust_sumday,
                        tax_calc_kbn,
                        tax_frac_kbn,
                        note1,
                        note2,
                        note3,
                        note4,
                        note5,
                        cust_b_cd1,
                        cust_b_cd2,
                        cust_b_cd3,
                        cust_b_cd4,
                        cust_b_cd5,
                        cust_b_cd6,
                        cust_b_cd7,
                        cust_b_cd8,
                        cust_b_cd9,
                        cust_b_cd10,
                        to_char(CAST(fst_visit_dt AS DATE), 'yyyy/mm/dd') as fst_visit_dt,
                        to_char(CAST(lst_visit_dt AS DATE), 'yyyy/mm/dd') as lst_visit_dt,
                        lst_shop_cd,
                        visit_cnt,
                        all_total,
                        all_profit,
                        all_point,
                        ave_visit_cnt,
                        disabled,
                        registration_time,
                        registration_user_id,
                        registration_organization,
                        update_time,
                        update_user_id,
                        update_organization,
                        shop_nm,
                        cust_type_nm,
                        area_nm,
                        staff_nm,
                        cust_b_nm1,
                        cust_b_nm2,
                        cust_b_nm3,
                        cust_b_nm4,
                        cust_b_nm5,
                        cust_b_nm6,
                        cust_b_nm7,
                        cust_b_nm8,
                        cust_b_nm9,
                        cust_b_nm10,
                        lst_shop_nm"
                    . ' FROM v_customer ';
            $sql .= '  WHERE cust_id = :cust_id  ';
            
            $searchArray = array( ':cust_id' => $cust_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $customerDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getCustomerData");
                return $customerDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $customerDataList = $data;
            }

            $Log->trace("END getCustomerData");

            return $customerDataList;
        }

        /**
         * 顧客情報データ削除
         * @param    $postArray(顧客情報ID/削除フラグ（1）/ユーザID/更新組織ID)
         * @return   SQLの実行結果
         */
        public function delUpdateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

                    $sql = 'UPDATE m_customer SET'
                        . '   disabled = :is_del'
                        . ' , update_time = current_timestamp'
                        . ' , update_user_id = :update_user_id'
                        . ' , update_organization = :update_organization'
                        . ' WHERE cust_id = :cust_id AND update_time = :update_time ';

                    $parameters = array(
                        ':is_del'                    => $postArray['is_del'],
                        ':update_user_id'            => $postArray['user_id'],
                        ':update_organization'       => $postArray['organization'],
                        ':cust_id'                   => $postArray['del_cust_id'],
                        ':update_time'               => $postArray['update_time'],
                    );

                    // SQL実行
                    if( !$DBA->executeSQL($sql, $parameters, true) )
                    {
                        // SQL実行エラー
                        $Log->warn("MSG_ERR_3090");
                        $errMsg = "顧客情報削除処理に失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END delUpdateData");
                        return "MSG_FW_DB_EXCLUSION_NG";
                    }
            
            $Log->trace("END delUpdateData");

            return "MSG_BASE_0000";
        }

        /* 顧客コード登録数取得
         * @param    $organization_id (登録予定の組織ID)
         * @return   $employeesNoList
         */
        protected function getCustCdCount($custCd)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustCdCount");

            $sql = ' SELECT COUNT(cust_cd) as cust_cd_cnt FROM m_customer '
                 . " WHERE cust_cd = :cust_cd AND disabled = '0'";   
            $parametersArray = array( ':cust_cd' => $custCd, );
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

        /**
         * 顧客情報データ更新
         * @param    $postArray
         * @return   SQLの実行結果
         */
        protected function modCustomerUpdateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modCustomerUpdateData");

            // 顧客情報マスタのカラムリスト
            $columnFlag = true;
            $columnList = $this->getColumnList($columnFlag);

            $parameters = array(
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':cust_id'                   => $postArray['up_cust_id'],
                ':update_time'               => $postArray['updateTime'],
            );

            $sqlUpdate = $this->creatUpdateSQL($postArray, $parameters, $columnList);

            $sql = 'UPDATE m_customer SET'
                . '   update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization';

            $sql .= $sqlUpdate;

            $sql .= ' WHERE cust_id = :cust_id AND update_time = :update_time ';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3082");
                $errMsg = "顧客情報ID：" . $postArray['cust_id']. "の更新失敗しました。";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            $Log->trace("END modCustomerUpdateData");

            return "MSG_BASE_0000";
        }

        /**
         * アップデート文補完メソッド
         * @param    $postArray
         * @param    $parameters(呼び元の引数の値を変更する)
         * @return   SQL文とパラメータ配列
         */
        private function creatUpdateSQL($postArray, &$parameters, $columnList)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatUpdateSQL");

            $sqlUpdate = "";
            foreach($postArray as $postKey => $postValue)
            {
                foreach($columnList as $columnKey => $columnvalue)
                {
                    if($postKey === $columnKey && $postValue !== "")
                    {
                        if($columnvalue === 'date')
                        {
                            $postValue = str_replace("/", "", $postValue);
                        }
                        $keyName = ":" . $postKey;
                        $sqlUpdate .= ' , ' . $postKey . ' = ' . $keyName;
                        $parameters[$keyName] = $postValue;
                    }
                    if($postKey === $columnKey && $postValue === "")
                    {
                        $keyName = ":" . $postKey;
                        $sqlUpdate .= ' , ' . $postKey . ' = ' . $keyName;
                        $parameters[$keyName] = null;
                    }
                }
            }

            $Log->trace("END creatUpdateSQL");

            return $sqlUpdate;
        }

        /* 購買履歴取得
         * @param    $organization_id (登録予定の組織ID)
         * @return   $employeesNoList
         */
        public function getSalesData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSalesData");
                   
            $searchArray = array();
            $sql = ' SELECT '
                    . " hideseq, to_char(CAST(trndate AS DATE), 'yyyy/mm/dd') as trndate, to_char(CAST(trntime AS TIME), 'HH24:MI:SS') as trntime, "
                    . "denno, shop_cd, staff_cd, total, profit, abbreviated_name, user_name "
                    . ' FROM t_sales_data '
                    . ' LEFT JOIN v_organization_name ON shop_cd::text = organization_id::text '
                    . ' LEFT JOIN '
                    . ' ( SELECT v_user.user_id, v_user.employees_no, v_user.user_name '
                    . " FROM v_user WHERE v_user.eff_code = '適用中'::text) usr ON staff_cd::text = employees_no::text "
                    . " WHERE cust_cd = :cust_cd AND disabled = '0' ";
            $searchArray = array_merge($searchArray, array(':cust_cd' => $postArray['custCd'],));
            if( $postArray['minTrndate'] !== "" )
            {
                $sql .= " AND CAST(trndate AS DATE) >= CAST(:minTrndate AS DATE) ";
                $searchArray = array_merge($searchArray, array(':minTrndate' => $postArray['minTrndate'],));
            }
            if( $postArray['maxTrndate'] !== "" )
            {
                $sql .= " AND CAST(trndate AS DATE) <= CAST(:maxTrndate AS DATE) ";
                $searchArray = array_merge($searchArray, array(':maxTrndate' => $postArray['maxTrndate'],));
            }
            $sql .= " ORDER BY trndate, trntime, denno";
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $salesDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getSalesData");
                return $salesDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $salesDataList, $data);
            }

            $Log->trace("END getSalesData");

            return $salesDataList;
        }
        /* 購買明細履歴取得
         * @param    $organization_id (登録予定の組織ID)
         * @return   $employeesNoList
         */
        public function getSalesDetailsData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSalesDetailsData");
                   
            $searchArray = array();

            $sql = ' SELECT sal.lineno, pro.prod_nm, sal.amount, sal.unitprice, sal.total, sal.profit '
                    . ' FROM t_sales_data_details as sal '
                    . ' LEFT OUTER JOIN m_profit_menu as pro ON sal.prod_cd = pro.prod_cd '
                    . " WHERE sal.denno = :denno AND sal.disabled = '0' "
                    . " ORDER BY sal.lineno";
            $searchArray = array_merge($searchArray, array(':denno' => $postArray['denno'],));
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $salesDetailsDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getSalesDetailsData");
                return $salesDetailsDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $salesDetailsDataList, $data);
            }

            $Log->trace("END getSalesData");

            return $salesDetailsDataList;
        }
    }

?>