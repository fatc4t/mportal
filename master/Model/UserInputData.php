<?php
    /**
     * @file      従業員入力画面マスタ
     * @author    USE M.Higashihara
     * @date      2016/07/06
     * @version   1.00
     * @note      従業員マスタ入力画面に表示するデータの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/BaseUser.php';

    /**
     * 従業員入力画面クラス
     * @note   従業員マスタテーブルの管理を行う。
     */
    class UserInputData extends BaseUser
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
         * 従業員マスタデータ修正画面登録データ検索
         * @param    $user_detail_id
         * @return   SQLの実行結果
         */
        public function getUserData( $user_detail_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserData");

            $sql = ' SELECT ud.user_detail_id, ud.user_id, ud.employees_no, ud.organization_id '
                 . " , to_char(ud.application_date_start, 'yyyy/mm/dd') as application_date_start "
                 . ' , ud.security_id , ud.position_id, ud.employment_id, ud.section_id'
                 . ' , ud.wage_form_id , ud.user_name , ud.address, ud.tel, ud.cellphone'
                 . " , ud.mail_address, to_char(u.birthday, 'yyyy/mm/dd') as birthday "
                 . ' , ud.hourly_wage, ud.base_salary, ud.comment, u.sex, u.trial_period_type_id '
                 . ' , u.trial_period_criteria_value, u.trial_period_write_down_criteria '
                 . ' , u.trial_period_wages_value, u.is_del, l.login_id, l.password '
                 . " , to_char(u.hire_date, 'yyyy/mm/dd') as hire_date "
                 . " , to_char(u.leaving_date, 'yyyy/mm/dd') as leaving_date "
                 . ' , u.update_time, is_embossing '
                 . ' FROM m_user_detail ud INNER JOIN m_user u ON ud.user_id = u.user_id '
                 . ' LEFT OUTER JOIN m_login l ON u.user_id = l.user_id '
                 . ' WHERE ud.user_detail_id = :user_detail_id ';
            
            $searchArray = array( ':user_detail_id' => $user_detail_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $userDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getUserData");
                return $userDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $userDataList = $data;
            }

            $Log->trace("END getUserData");

            return $userDataList;
        }

        /**
         * 従業員に付与されている承認組織一覧を取得
         * @param    $UserDetailId（従業員詳細ID）
         * @return   $approvalList
         */
        public function getApprovalOrganizationList($UserDetailId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getApprovalOrganizationList");

            $sql = ' SELECT organization_id FROM m_approval_organization WHERE user_detail_id = :user_detail_id';
            $parametersArray = array(':user_detail_id' => $UserDetailId);
            $result = $DBA->executeSQL($sql, $parametersArray);
            $approvalList = array();
            if( $result === false )
            {
                $Log->trace("END getApprovalOrganizationList");
                return $approvalList;
            }
            $approvalArray = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $approval = array(
                    'organization_id' => $data['organization_id'],
                );
                array_push($approvalArray, $approval);
            }
            $approvalArray = $this->creatAccessControlledList($_SESSION["REFERENCE"], $approvalArray);
            foreach($approvalArray as $approval)
            {
                $approvalList[] = $approval['organization_id'];
            }
            $Log->trace("END getApprovalOrganizationList");
            return $approvalList;
        }

        /**
         * 従業員に付与されている手当一覧を取得
         * @param    $UserDetailId（従業員詳細ID）
         * @return   $allowanceList（従業員手当ID/手当ID/手当金額）
         */
        public function getUserAllowanceList($UserDetailId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserAllowanceList");

            $sql = ' SELECT allowance_id, allowance_amount '
                 . ' FROM m_user_allowance WHERE user_detail_id = :user_detail_id ';
            $parametersArray = array(':user_detail_id' => $UserDetailId);
            $result = $DBA->executeSQL($sql, $parametersArray);
            $allowanceList = array();
            if( $result === false )
            {
                $Log->trace("END getUserAllowanceList");
                return $allowanceList;
            }

            $allowance = "";
            $amount = "";
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    $allowance .= $data['allowance_id'] . ",";               // 手当ID
                    $amount .= $data['allowance_amount'] . ",";              // 手当金額
            }
            $allowance = substr($allowance, 0, -1);
            $amount = substr($amount, 0, -1);
            $allowanceList = array($allowance, $amount);

            $Log->trace("END getUserAllowanceList");
            return $allowanceList;
        }

        /**
         * 従業員マスタデータ削除
         * @param    $postArray(従業員ID/削除フラグ（1）/ユーザID/更新組織ID)
         * @return   SQLの実行結果
         */
        public function delUpdateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            // 適用予定の場合、組織詳細マスタを物理削除する
            $today = date("Y/m/d");
            $targetDay = $postArray['applicationDateStart'];
            if( strtotime($today) < strtotime($targetDay) )
            {
                // 物理削除
                $ret = $this->executeDelSQL( $postArray );
            }
            else
            {
                // ユーザの論理削除
                if($DBA->beginTransaction())
                {
                    $sql = 'UPDATE m_user SET'
                        . '   is_del = :is_del'
                        . ' , update_time = current_timestamp'
                        . ' , update_user_id = :update_user_id'
                        . ' , update_organization = :update_organization'
                        . ' WHERE user_id = :user_id AND update_time = :update_time ';

                    $parameters = array(
                        ':is_del'                    => $postArray['is_del'],
                        ':update_user_id'            => $postArray['user_id'],
                        ':update_organization'       => $postArray['organization'],
                        ':user_id'                   => $postArray['del_user_id'],
                        ':update_time'               => $postArray['update_time'],
                    );

                    // SQL実行
                    if( !$DBA->executeSQL($sql, $parameters, true) )
                    {
                        // SQL実行エラー　ロールバック対応
                        $DBA->rollBack();
                        // SQL実行エラー
                        $Log->warn("MSG_ERR_3090");
                        $errMsg = "従業員削除処理に失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END delUpdateData");
                        return "MSG_FW_DB_EXCLUSION_NG";
                    }

                    // ログインアカウント削除処理
                    $ret = $this->generationSQL->physicalDeleteData($postArray['del_user_id'], "m_login", "user_id");
                    if($ret !== "MSG_BASE_0000")
                    {
                        $DBA->rollBack();
                        $errMsg = "ログインマスタ削除処理にエラーが生じました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END delUpdateData");
                        return $ret;
                    }

                    // コミット
                    if( !$DBA->commit() )
                    {
                        // コミットエラー　ロールバック対応
                        $DBA->rollBack();
                        $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                        $errMsg = "コミットに失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END delUpdateData");
                        return "MSG_FW_DB_EXCLUSION_NG";
                    }
                }
                else
                {
                    // エラー処理(トランザクション開始エラー)
                    $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                    $errMsg = "従業員No：" . $postarray['employees_no'] . "従業員姓名" . $postarray['user_name'] . "サーバエラー";
                    $Log->fatalDetail($errMsg);
                    return "MSG_FW_DB_TRANSACTION_NG";
                }
            }
            
            $Log->trace("END delUpdateData");

            return "MSG_BASE_0000";
        }

        /**
         * 従業員マスタ物理削除
         * @param    $postArray(従業員ID/削除フラグ（1）/ユーザID/更新組織ID)
         * @return   SQLの実行結果
         */
        public function executeDelSQL($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START executeDelSQL");

            // ユーザ詳細マスタに該当データユーザデータしかが存在しない
            $sql = " SELECT COUNT( user_id ) as count "
                 . " FROM v_user WHERE is_del = 0 AND user_id = :user_id ";

            $parameters = array(
                ':user_id'     => $postArray['del_user_id'],
            );
            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                // SQL実行エラー
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "SQL実行に失敗しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END executeDelSQL");
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            $allDel = false;
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( $data['count'] == 1 )
                {
                    $allDel = true;
                }
            }

            // ユーザの物理削除
            if($DBA->beginTransaction())
            {
                // ユーザ手当マスタ
                $sql = ' DELETE FROM m_user_allowance WHERE user_detail_id = :user_detail_id  ';
                $parameters = array(
                    ':user_detail_id' => $postArray['userDtailID'],
                );

                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters ) )
                {
                    // SQL実行エラー　ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_ERR_3090");
                    $errMsg = "ユーザ手当マスタ削除処理に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeDelSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 承認組織マスタ
                $sql = ' DELETE FROM m_approval_organization WHERE user_detail_id = :user_detail_id ';
                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters ) )
                {
                    // SQL実行エラー　ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_ERR_3090");
                    $errMsg = "承認組織マスタ削除処理に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeDelSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 従業員詳細マスタを削除
                $sql = ' DELETE FROM m_user_detail WHERE user_detail_id = :user_detail_id ';
                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters, true) )
                {
                    // SQL実行エラー　ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_ERR_3090");
                    $errMsg = "従業員詳細削除処理に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeDelSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // ユーザマスタも削除する
                if( $allDel )
                {
                    // ログインアカウント削除処理
                    $ret = $this->generationSQL->physicalDeleteData($postArray['del_user_id'], "m_login", "user_id");
                    if($ret !== "MSG_BASE_0000")
                    {
                        $DBA->rollBack();
                        $errMsg = "ログインマスタ削除処理にエラーが生じました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END executeDelSQL");
                        return $ret;
                    }
                    
                    // 従業員マスタを削除
                    $sql = ' DELETE FROM m_user WHERE user_id = :user_id AND update_time = :update_time ';
                    $parameters = array(
                        ':user_id'                   => $postArray['del_user_id'],
                        ':update_time'               => $postArray['update_time'],
                    );
                    // SQL実行
                    if( !$DBA->executeSQL($sql, $parameters, true) )
                    {
                        // SQL実行エラー　ロールバック対応
                        $DBA->rollBack();
                        // SQL実行エラー
                        $Log->warn("MSG_ERR_3090");
                        $errMsg = "ユーザマスタ削除処理に失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END executeDelSQL");
                        return "MSG_FW_DB_EXCLUSION_NG";
                    }
                }

                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "コミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeDelSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "従業員No：" . $postarray['employees_no'] . "従業員姓名" . $postarray['user_name'] . "サーバエラー";
                $Log->fatalDetail($errMsg);
                return "MSG_FW_DB_TRANSACTION_NG";
            }

            
            $Log->trace("END delUpdateData");

            return "MSG_BASE_0000";
        }

        /**
         * 従業員マスタ入力画面組織選択時に対象組織の就業規則優先順位取得
         * @param    $organization_id
         * @return   SQLの実行結果
         */
        public function getLaborRegulationsPriority( $organization_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getLaborRegulationsPriority");

             $sql = ' SELECT od.priority_o, od.priority_p, od.priority_e '
                  . ' FROM m_organization_detail od '
                  . ' ,( SELECT organization_id, max(application_date_start) as application_date_start '
                  . '    FROM m_organization_detail WHERE organization_id = :organization_id GROUP BY organization_id ) organization '
                  . ' WHERE od.organization_id = organization.organization_id '
                  . ' AND od.application_date_start = organization.application_date_start ';
            $searchArray = array( ':organization_id' => $organization_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $priorityList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getLaborRegulationsPriority");
                return $priorityList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $priorityList = $data;
            }

            $Log->trace("END getLaborRegulationsPriority");

            return $priorityList;
        }

        /**
         * 対象組織の適用中となる詳細ID取得
         * @param    $organization_id
         * @return   $organization_detail_id
         */
        public function getAppliedOrganizationDetailId( $organization_id )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAppliedOrganizationDetailId");

            $sql = ' SELECT organization_detail_id FROM m_organization_detail detail '
                 . '     ,( SELECT organization_id, max(application_date_start) as application_date_start '
                 . '        FROM m_organization_detail WHERE organization_id = :organization_id '
                 . '        AND application_date_start <= current_timestamp GROUP BY organization_id ) maxData '
                 . ' WHERE detail.organization_id = maxData.organization_id '
                 . ' AND detail.application_date_start = maxData.application_date_start ';
            $searchArray = array( ':organization_id' => $organization_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            $organizationDetail = 0;
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getAppliedOrganizationDetailId");
                return $organizationDetail;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $organizationDetail = $data['organization_detail_id'];
            }

            $Log->trace("END getAppliedOrganizationDetailId");

            return $organizationDetail;
        }

        /**
         * 組織、役職、雇用形態に付随している就業規則設定試用期間情報の取得
         * @param    $parameter_id
         * @param    $tableName
         * @param    $columnName
         * @return   $trialArray
         */
        public function laborRegulationsSetProbation($parameter_id, $tableName, $columnName)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

            $keyName = ":" . $columnName;

            $sql = ' SELECT mr.trial_period_type_id, mr.trial_period_criteria_value '
                 . ' FROM m_work_rules_time mr '
                 . '     ,( SELECT application_date_id, applcation.labor_regulations_id '
                 . '        FROM m_application_date ad '
                 . '            ,( SELECT labor_regulations_id '
                 . '               , max(application_date_start) as application_date_start '
                 . '               FROM  m_application_date WHERE labor_regulations_id = '
                 . '                   ( SELECT labor_regulations_id FROM ';
            $sql .= $tableName;
            $sql .= ' WHERE ';
            $sql .= $columnName;
            $sql .= ' = ';
            $sql .= $keyName;
            $sql .= '            ) AND application_date_start <= current_timestamp '
                 . '               GROUP BY labor_regulations_id ) applcation '
                 . '       WHERE ad.labor_regulations_id = applcation.labor_regulations_id '
                 . '       AND ad.application_date_start = applcation.application_date_start ) applicationDate '
                 . ' WHERE mr.application_date_id = applicationDate.application_date_id '
                 . ' AND mr.labor_regulations_id = applicationDate.labor_regulations_id ';
            $searchArray = array( $keyName => $parameter_id, );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $trialArray = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END delUpdateData");
                return $trialArray;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $trialArray = $data;
            }

            $Log->trace("END delUpdateData");

            return $trialArray;
        }

        /**
         * 登録従業員No一覧取得
         * @note     同じ給与フォーマットを使っている組織内で登録されている従業員No一覧を割り出す
         * @param    $organization_id (登録予定の組織ID)
         * @return   $employeesNoList
         */
        protected function getEmployeesNoListInnerSalaryFormat($organization_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getEmployeesNoListInnerSalaryFormat");

            $sql = ' SELECT mud.employees_no  '
                 . ' FROM m_user_detail mud INNER JOIN m_user mu ON mud.user_id = mu.user_id AND mu.is_del = 0 '
                 . ' WHERE organization_id IN ( SELECT organization_id '
                 . '       FROM m_organization_detail WHERE payroll_system_id IN '
                 . '           ( SELECT payroll_system_id '
                 . '             FROM m_organization_detail WHERE organization_detail_id IN '
                 . '                 ( SELECT organization_detail_id FROM m_organization_detail '
                 . '                   WHERE ( application_date_start = '
                 . '                       ( SELECT application_date_start '
                 . '                         FROM m_organization_detail WHERE organization_id = :organization_id '
                 . '                         AND application_date_start > current_timestamp ) '
                 . '                   OR application_date_start = '
                 . '                       ( SELECT max(application_date_start)  '
                 . '                         FROM m_organization_detail WHERE organization_id = :organization_id '
                 . '                         AND application_date_start <= current_timestamp ) ) '
                 . '                   AND organization_id = :organization_id '
                 . '                 ) '
                 . '           ) '
                 . '       GROUP BY organization_id ORDER BY organization_id '
                 . '     ) '
                 . ' ORDER BY employees_no ';
            $parametersArray = array( ':organization_id' => $organization_id, );
            $result = $DBA->executeSQL($sql, $parametersArray);
            $employeesNoList = array();

            if( $result === false )
            {
                $Log->trace("END getEmployeesNoListInnerSalaryFormat");
                return $employeesNoList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $employeesNoList[] = $data['employees_no'];
            }

            $Log->trace("END getEmployeesNoListInnerSalaryFormat");

            return $employeesNoList;
        }

        /**
         * 従業員マスタデータ更新
         * @param    $postArray
         * @return   SQLの実行結果
         */
        protected function modUserUpdateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modUserUpdateData");

            // 従業員マスタのカラムリスト
            $columnFlag = true;
            $columnList = $this->getColumnList($columnFlag);

            $parameters = array(
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':user_id'                   => $postArray['up_user_id'],
                ':update_time'               => $postArray['updateTime'],
            );

            $sqlUpdate = $this->generationSQL->creatUpdateSQL($postArray, $parameters, $columnList);

            // 更新時からできた必須項目以外のものにNULLをセット
            $this->setUserUpdateNullValue($postArray, $parameters, $sqlUpdate);

            $sql = 'UPDATE m_user SET'
                . '   update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization';

            $sql .= $sqlUpdate;

            $sql .= ' WHERE user_id = :user_id AND update_time = :update_time ';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3085");
                $errMsg = "従業員ID：" . $postArray['user_id']. "の更新失敗しました。";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }

            $Log->trace("END modUserUpdateData");

            return "MSG_BASE_0000";
        }

        /**
         * 従業員詳細マスタデータ更新
         * @param    $postArray(従業員詳細ID/登録ユーザID/登録組織ID)
         * @return   SQLの実行結果(true/false)
         */
        protected function modUserDetailUpdateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modUserDetailUpdateData");

            // 従業員詳細マスタのカラムリスト
            $columnFlag = false;
            $columnList = $this->getColumnList($columnFlag);

            $parameters = array(
                ':user_id'                         => $postArray['up_user_id'],
                ':user_detail_id'                  => $postArray['up_user_detail_id'],
            );

            $sqlUpdate = $this->generationSQL->creatUpdateSQL($postArray, $parameters, $columnList);

            // 更新時からできた必須項目以外のものにNULLをセット
            $this->setUserDetailUpdateNullValue($postArray, $parameters, $sqlUpdate);

            $sql = 'UPDATE m_user_detail SET'
                . '   user_id = :user_id';
            $sql .= $sqlUpdate;
            $sql .= ' WHERE user_detail_id = :user_detail_id';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3086");
                $errMsg = "従業員詳細ID：" . $postArray['up_user_detail_id']. "従業員姓名：" . $postArray['user_name']. "の更新失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3086";
            }

            $Log->trace("END modUserDetailUpdateData");

            return "MSG_BASE_0000";
        }

        /**
         * ログインアカウント更新
         * @param    $postArray(ログインID/パスワード)
         * @param    $userId
         * @return   SQLの実行結果(true/false)
         */
        protected function modLoginAccountUpdateData($postArray, $userId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modLoginAccountUpdateData");

            $sql = ' UPDATE m_login SET login_id = :login_id , password = :password WHERE user_id = :user_id ';

            $parameters = array(
                ':login_id'            => $postArray['login_id'],
                ':password'            => $postArray['password'],
                ':user_id'             => $userId,
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3061");
                $errMsg = "ログインID：" . $postarray['login_id']. "パスワード：" . $postarray['password'] . "の更新失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3061";
            }

            $Log->trace("END modLoginAccountUpdateData");

            return "MSG_BASE_0000";
        }

        /**
         * ログインアカウント重複チェック
         * @param    $array
         * @param    $userId
         * @return   $loginOverlapFlag
         */
        protected function checkLoginOverlap($postArray, $userId)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START checkLoginOverlap");

            $loginList = $this->getLoginAccountList();

            $loginOverlapFlag = true;
            foreach($loginList as $login)
            {
                if( ( $login['login_id'] === $postArray['login_id'] ) && ( $login['password'] === $postArray['password'] ) && ( $login['user_id'] != $userId ) )
                {
                    $loginOverlapFlag = false;
                }
            }

            $Log->trace("END checkLoginOverlap");

            return $loginOverlapFlag;
        }

        /**
         * 対象のマスタ内の対象従業員登録データの有無を確認
         * @note     新規登録ではない場合のみ判断
         * @param    $targetId
         * @param    $addFlag
         * @param    $tableName
         * @param    $columnName
         * @return   $loginAccountFlag
         */
        protected function getTargetDataPresence($targetId, $addFlag, $tableName, $columnName)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getTargetDataPresence");

            $targetDataCnt = "";
            if(empty($addFlag))
            {
                $targetDataCnt = $this->generationSQL->getRegistrationCount($targetId, $tableName, $columnName);
            }

            $Log->trace("END getTargetDataPresence");
            return $targetDataCnt;
        }

        /**
         * 対象のマスタ内の対象従業員登録データの削除
         * @note     対象のマスタ内の対象従業員登録データが存在する場合のみ
         * @param    $targetId
         * @param    $dataCnt
         * @param    $tableName
         * @param    $columnName
         * @return   $loginAccountFlag
         */
        protected function getTargetDataPresenceDelete($targetId, $dataCnt, $tableName, $columnName)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getTargetDataPresenceDelete");

            // 登録データがある場合、データを削除する。
            $ret = "MSG_BASE_0000";
            if(!empty($dataCnt))
            {
                $ret = $this->generationSQL->physicalDeleteData($targetId, $tableName, $columnName);
            }

            $Log->trace("END getTargetDataPresenceDelete");
            return $ret;
        }

        /**
         * ログインアカウントリスト取得
         * @return   $loginList
         */
        private function getLoginAccountList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getLoginAccountList");

            $sql = 'SELECT login_id, password, user_id FROM m_login';
            $parametersArray = array();
            $result = $DBA->executeSQL($sql, $parametersArray);

            $loginList = array();
            if( $result === false )
            {
                $Log->trace("END getLoginAccountList");
                return $loginList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($loginList, $data);
            }

            $Log->trace("END getLoginAccountList");

            return $loginList;
        }

    }

?>