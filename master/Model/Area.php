<?php
    /**
     * @file      組織マスタ
     * @author    USE R.dendo
     * @date      2016/7/11
     * @version   1.00
     * @note      組織マスタテーブルの管理を行う
     */

    // CheckOrganization.phpを読み込む
    require './Model/CheckOrganization.php';

    /**
     * 組織マスタクラス
     * @note   組織マスタテーブルの管理を行う
     */
    class Area extends CheckOrganization
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
         * 組織マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(organization_id/is_del/organizationID/laborRegulationsID/departmentCode/startTimeDay/commentSearch/payrollFormat/upperLevelOrganization/sort)
         * @return   成功時：$organizationList(organization_id/department_code/eff_code/organization_name/abbreviated_name/application_date_start/start_time_day/priority_o/priority_p/priority_e
         *                                     payroll_system_name/up_organization_name/upper_level_organization/payroll_system_id/authentication_key/labor_regulations_id/labor_regulations_name
         *                                     is_apply_plan/comment/disp_order/is_del/mo_update_time/mod_update_time)
         *           失敗：無
         */
        public function getListData( $postArray, $effFlag )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray, $effFlag );

            $result = $DBA->executeSQL($sql, $searchArray);

            $organizationDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $organizationDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $organizationDataList, $data);
            }

            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $organizationList = $organizationDataList;
            }
            else
            {
                $organizationList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $organizationDataList);
                if( $postArray['sort'] == 1 )
                {
                    $organizationList = array_reverse($organizationList);
                }
            }

            $Log->trace("END getListData");

            return $organizationList;
        }

        /**
         * 新規データ登録
         * @param    $postArray   入力パラメータ(組織ID/部門コード/組織ID/組織名/組織略名/適用開始日/1日の開始時間/就業規則優先順位/給与フォーマット
         *                                       就業規則名/上位組織/認証キー/コメント/表示順/更新時間/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modAddData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modAddData");

            // 重複チェック
            $ret = $this->isInputError($postArray, true);
            

            if( "MSG_BASE_0000" != $ret )
            {
                // 入力エラー
                $Log->trace("END modAddData");
                return $ret;
            }

            $ret = $this->executeAddSQL( $postArray );

            $Log->trace("END modAddData");

            return $ret;
        }

        /**
         * 登録データ修正
         * @param    $postArray   入力パラメータ(組織ID/部門コード/組織ID/組織名/組織略名/適用開始日/1日の開始時間/就業規則優先順位/給与フォーマット
         *                                       就業規則名/上位組織/認証キー/コメント/表示順/更新時間/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            // 関連チェック
            $ret = $this->isInputError($postArray, false);
            if( "MSG_BASE_0000" != $ret )
            {
                // 入力エラー
                $Log->warn($ret);
                $Log->trace("END modUpdateData");
                return $ret;
            }

            $ret = $this->executeSQL( $postArray );
            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * 組織マスタ登録データ削除
         * @param    $postArray   入力パラメータ(組織ID/削除フラグ/更新時間/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
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
                // 組織使用の有無
                if( !$this->isDel( $postArray['organizationID'] ) )
                {
                    $Log->trace("END delUpdateData");
                    return "MSG_WAR_2101";
                }
            
                $sql = 'UPDATE m_organization SET'
                    . '   is_del = :is_del'
                    . ' , update_time = current_timestamp'
                    . ' , update_user_id = :update_user_id'
                    . ' , update_organization = :update_organization'
                    . ' WHERE organization_id = :organization_id AND update_time = :update_time ';

                $parameters = array(
                    ':is_del'                    => $postArray['is_del'],
                    ':update_user_id'            => $postArray['user_id'],
                    ':update_organization'       => $postArray['organization'],
                    ':organization_id'           => $postArray['organizationID'],
                    ':update_time'               => $postArray['updateTime'],
                );
                
                // 削除SQLの実行
                $ret = $this->executeOneTableSQL( $sql, $parameters );
            }
            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * 組織マスタの検索用コードのプルダウン
         * @return   コードリスト(コード) 
         */
        public function getSearchCodeList()
        {
            global $DBA, $Log;  // グローバル変数宣言
            $Log->trace("START getSearchCodeList");

            $sql = '  SELECT organization_id FROM m_organization '
                 . ' WHERE is_del = :is_del ORDER BY organization_id ';
            $parametersArray = array( ':is_del' => 0, );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $codeList = array();
            
            if( $result === false )
            {
                $Log->trace("END getSearchCodeList");
                return $codeList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($codeList, $data);
            }

            $outList = array();
            $outList = $this->creatAccessControlledList( $_SESSION["REFERENCE"], $codeList );

            $initial = array('code' => '',);
            array_unshift($outList, $initial);

            $column = "code";
            $outList = $this->getUniqueArray($outList, $column);

            $Log->trace("END getSearchCodeList");
            return $outList;
        }

        /**
         * 2テーブル更新
         * @param    $sql           実行するSQL文
         * @param    $parameters    実行するSQL文のパラメータ
         * @param    $tableName     INSERTしたテーブル名を指定する(その他は指定なし)
         * @return   実行結果
         */
        protected function executeAddSQL( $postArray )
        {
            global $DBA, $Log, $Lastid; // グローバル変数宣言
            $Log->trace("START executeOneTableSQL");

            if( $DBA->beginTransaction() )
            {
                if( $postArray['buttonName'] == 2 )
                {
                    // 予定の場合、既存の組織IDを設定(詳細のみ追加する)
                    $postArray['organizationId'] = $postArray['organizationID'];
                }
                else
                {
                    // 組織マスタの登録
                    $sql = '';
                    $parameters = array();
                    $this->addNewData( $postArray, $sql, $parameters );
                    // SQL実行
                    if( !$DBA->executeSQL($sql, $parameters, true) )
                    {
                        // SQL実行エラー ロールバック対応
                        $DBA->rollBack();
                        // SQL実行エラー
                        $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                        $errMsg = "SQL実行に失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END executeOneTableSQL");
                        return "MSG_FW_DB_EXCLUSION_NG";
                    }

                    // Lastidの更新
                    $postArray['organizationId'] = $DBA->lastInsertId( "m_organization" );
                    $Lastid = $postArray['organizationId'];
                }
                
                // 組織詳細マスタの登録
                $sql = '';
                $parameters = array();
                $this->organizationDetailNewData( $postArray, $sql, $parameters );
                
                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters, true) )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "SQL実行に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeOneTableSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
   
                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "コミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeOneTableSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "トランザクション開始エラー";
                $Log->fatalDetail($errMsg);
                $Log->trace("END executeOneTableSQL");
                return "MSG_FW_DB_TRANSACTION_NG";
            }

            $Log->trace("END executeOneTableSQL");

            return "MSG_BASE_0000";
        }

        /**
         * 2テーブル更新
         * @param    $sql           実行するSQL文
         * @param    $parameters    実行するSQL文のパラメータ
         * @param    $tableName     INSERTしたテーブル名を指定する(その他は指定なし)
         * @return   実行結果
         */
        protected function executeSQL( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START executeOneTableSQL");

            if( $DBA->beginTransaction() )
            {
                // 組織マスタの更新
                $sql = '';
                $parameters = array();
                $this->modOrganizationSQL( $postArray, $sql, $parameters );

                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters, true) )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "SQL実行に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeOneTableSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 組織詳細マスタの更新
                $sql = '';
                $parameters = array();
                $this->modOrganizationDetail( $postArray, $sql, $parameters );
                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters, true) )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "SQL実行に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeOneTableSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラーロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "コミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeOneTableSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "トランザクション開始エラー";
                $Log->fatalDetail($errMsg);
                $Log->trace("END executeOneTableSQL");
                return "MSG_FW_DB_TRANSACTION_NG";
            }

            $Log->trace("END executeOneTableSQL");

            return "MSG_BASE_0000";
        }

        /**
         * データの物理削除
         * @param    $postArray     入力パラメータ
         * @return   実行結果
         */
        private function executeDelSQL( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START executeOneTableSQL");

            // 組織詳細マスタに該当データ組織データが存在しない
            $sql = " SELECT COUNT( od.labor_regulations_id ) as count "
                 . " FROM m_organization_detail od INNER JOIN m_organization o ON o.organization_id = od.organization_id WHERE o.is_del = 0 AND od.organization_id = :organization_id ";

            $parameters = array(
                ':organization_id'           => $postArray['organizationID'],
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

            if( $allDel )
            {
                // 組織マスタも物理削除する場合のみ、関連チェックを実行する
                // 組織使用の有無
                if( !$this->isDel( $postArray['organizationID'] ) )
                {
                    $Log->trace("END delUpdateData");
                    return "MSG_WAR_2101";
                }
            }

            if( $DBA->beginTransaction() )
            {
                // 組織詳細マスタの物理削除
                $sql = ' DELETE FROM m_organization_detail '
                     . ' WHERE organization_id = :organization_id AND update_time = :update_time ';

                $parameters = array(
                    ':organization_id'           => $postArray['organizationID'],
                    ':update_time'               => $postArray['modUpdateTime'],
                );

                // SQL実行
                if( !$DBA->executeSQL($sql, $parameters, true) )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    // SQL実行エラー
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "SQL実行に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeDelSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 組織マスタも削除する
                if( $allDel )
                {
                    // 組織マスタの物理削除
                    $sql = ' DELETE FROM m_organization WHERE  organization_id = :organization_id ';

                    $parameters = array(
                        ':organization_id'           => $postArray['organizationID'],
                    );

                    // SQL実行
                    if( !$DBA->executeSQL($sql, $parameters, true) )
                    {
                        // SQL実行エラー ロールバック対応
                        $DBA->rollBack();
                        // SQL実行エラー
                        $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                        $errMsg = "SQL実行に失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END executeDelSQL");
                        return "MSG_FW_DB_EXCLUSION_NG";
                    }
                }

                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラーロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "コミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END executeOneTableSQL");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "トランザクション開始エラー";
                $Log->fatalDetail($errMsg);
                $Log->trace("END executeOneTableSQL");
                return "MSG_FW_DB_TRANSACTION_NG";
            }

            $Log->trace("END executeOneTableSQL");

            return "MSG_BASE_0000";
        }

        /**
         * 組織マスタ新規データ登録
         * @param    $postArray   入力パラメータ(認証キー/表示順/更新時間/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        private function addNewData( $postArray, &$sql, &$parameters )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO m_organization( authentication_key'
                . '                    , disp_order'
                . '                    , registration_time'
                . '                    , registration_user_id'
                . '                    , registration_organization'
                . '                    , update_time'
                . '                    , update_user_id'
                . '                    , update_organization'
                . '                    ) VALUES ('
                . '                    :authentication_key'
                . '                    , :disp_order'
                . '                    , current_timestamp'
                . '                    , :registration_user_id'
                . '                    , :registration_organization'
                . '                    , current_timestamp'
                . '                    , :update_user_id'
                . '                    , :update_organization)';
               

            $parameters = array(

                ':authentication_key'            => $postArray['authenticationKey'],
                ':disp_order'                    => $postArray['dispOrder'],
                ':registration_user_id'          => $postArray['user_id'],
                ':registration_organization'     => $postArray['organization'],
                ':update_user_id'                => $postArray['user_id'],
                ':update_organization'           => $postArray['organization'],
            );

            $Log->trace("END addNewData");

        }

        /**
         * 組織詳細マスタ新規データ登録
         * @param    $postArray   入力パラメータ(部門コード/組織ID/組織名/組織略称/上位組織/適用開始日/1日の開始時間/就業規則優先順位/就業規則名/給与フォーマット/
         *                                       コメント/更新時間/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        private function organizationDetailNewData( $postArray, &$sql, &$parameters )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START organizationDetailNewData");

            $sql = 'INSERT INTO m_organization_detail( department_code'
                . '                    , organization_id'
                . '                    , organization_name'
                . '                    , abbreviated_name'
                . '                    , application_date_start'
                . '                    , start_time_day'
                . '                    , priority_o'
                . '                    , priority_p'
                . '                    , priority_e'
                . '                    , labor_regulations_id'
                . '                    , payroll_system_id'
                . '                    , upper_level_organization'
                . '                    , comment'
                . '                    , registration_time'
                . '                    , registration_user_id'
                . '                    , registration_organization'
                . '                    , update_time'
                . '                    , update_user_id'
                . '                    , update_organization'
                . '                    ) VALUES ('
                . '                    :department_code'
                . '                    , :organization_id'
                . '                    , :organization_name'
                . '                    , :abbreviated_name'
                . '                    , :application_date_start'
                . '                    , :start_time_day'
                . '                    , :priority_o'
                . '                    , :priority_p'
                . '                    , :priority_e'
                . '                    , :labor_regulations_id'
                . '                    , :payroll_system_id'
                . '                    , :upper_level_organization'
                . '                    , :comment'
                . '                    , current_timestamp'
                . '                    , :registration_user_id'
                . '                    , :registration_organization'
                . '                    , current_timestamp'
                . '                    , :update_user_id'
                . '                    , :update_organization)';

            $parameters = array(
                ':department_code'            => $postArray['departmentCode'],
                ':organization_id'            => $postArray['organizationId'],
                ':organization_name'          => $postArray['organizationName'],
                ':abbreviated_name'           => $postArray['abbreviatedName'],
                ':application_date_start'     => $postArray['applicationDateStart'],
                ':start_time_day'             => $postArray['startTimeDay'],
                ':priority_o'                 => $postArray['priority_o'],
                ':priority_p'                 => $postArray['priority_p'],
                ':priority_e'                 => $postArray['priority_e'],
                ':labor_regulations_id'       => $postArray['laborRegulationsName'],
                ':payroll_system_id'          => $postArray['payrollFormat'],
                ':upper_level_organization'   => $postArray['upperLevelOrganization'],
                ':comment'                    => $postArray['comment'],
                ':registration_user_id'       => $postArray['user_id'],
                ':registration_organization'  => $postArray['organization'],
                ':update_user_id'             => $postArray['user_id'],
                ':update_organization'        => $postArray['organization'],
            );
            $Log->trace("END organizationDetailNewData");
        }

        /**
         * 組織マスタ登録データ修正
         * @param    $postArray   入力パラメータ(認証キー/表示順/更新時間/更新順/ユーザID/更新組織)
         */
        private function modOrganizationSQL( $postArray, &$sql, &$parameters )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modOrganizationSQL");

            $sql = 'UPDATE m_organization SET'
                . ' organization_id = :organization_id'
                . ' , authentication_key = :authentication_key'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE organization_id = :organization_id AND update_time = :update_time ';

            $parameters = array(
                ':organization_id'           => $postArray['organizationID'],
                ':authentication_key'        => $postArray['authenticationKey'],
                ':disp_order'                => $postArray['dispOrder'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':update_time'               => $postArray['updateTime'],
            );

            $Log->trace("END modOrganizationSQL");
        }

       /**
         * 組織詳細マスタ登録データ修正
         * @param    $postArray   入力パラメータ(部門コード/組織ID/組織名/組織略称/上位組織/適用開始日/1日の開始時間/就業規則優先順位/就業規則名/給与フォーマット/
         *                                       コメント/更新時間/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        private function modOrganizationDetail( $postArray, &$sql, &$parameters )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modOrganizationDetail");

            $sql = 'UPDATE m_organization_detail SET'
                . ' organization_id = :organization_id'
                . ' , department_code = :department_code'
                . ' , organization_name = :organization_name'
                . ' , payroll_system_id = :payroll_system_id'
                . ' , upper_level_organization = :upper_level_organization'
                . ' , priority_o = :priority_o'
                . ' , priority_p = :priority_p'
                . ' , priority_e = :priority_e'
                . ' , labor_regulations_id = :labor_regulations_id'
                . ' , abbreviated_name = :abbreviated_name'
                . ' , comment = :comment'
                . ' , application_date_start = :application_date_start'
                . ' , start_time_day = :start_time_day'
                . ' , update_time = current_timestamp'
                . ' WHERE organization_id = :organization_id AND organization_detail_id = :organization_detail_id AND update_time = :update_time ';

            $parameters = array(
                ':organization_id'             => $postArray['organizationID'],
                ':organization_detail_id'      => $postArray['organizationDetailID'],
                ':department_code'             => $postArray['departmentCode'],
                ':organization_name'           => $postArray['organizationName'],
                ':payroll_system_id'           => $postArray['payrollFormat'],
                ':upper_level_organization'    => $postArray['upperLevelOrganization'],
                ':priority_o'                  => $postArray['priority_o'],
                ':priority_p'                  => $postArray['priority_p'],
                ':priority_e'                  => $postArray['priority_e'],
                ':labor_regulations_id'        => $postArray['laborRegulationsName'],
                ':abbreviated_name'            => $postArray['abbreviatedName'],
                ':comment'                     => $postArray['comment'],
                ':application_date_start'      => $postArray['applicationDateStart'],
                ':start_time_day'              => $postArray['startTimeDay'],
                ':update_time'                 => $postArray['modUpdateTime'],
            );
            
            $Log->trace("END modOrganizationDetail");
        }

        /**
         * 組織マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/organizationID/laborRegulationsID/departmentCode/startTimeDay/commentSearch/payrollFormat/
                                                               upperLevelOrganization/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   組織マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray, $effFlag )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");
            $searchedColumn = ' WHERE ';
            $sqlWhereIn = $this->creatSqlWhereInConditions($searchedColumn);

            $sql = " SELECT organization_id,department_code,eff_code,organization_name,abbreviated_name,to_char(application_date_start, 'yyyy/mm/dd') as application_date_start,start_time_day,"
                 . "       organization_detail_id, priority_o,priority_p,priority_e,payroll_system_name,up_organization_name,upper_level_organization,payroll_system_id,authentication_key,"
                 . "       labor_regulations_id,labor_regulations_name,is_apply_plan,comment,disp_order,is_del,mo_update_time,mod_update_time"
                 . " FROM  v_organization";
            $sql .= $sqlWhereIn;
                 
            if( !empty( $postArray['payrollFormat'] ) )
            {
                $sql .= ' AND payroll_system_id = :payrollFormat ';
                $payrollFormatArray = array(':payrollFormat' => $postArray['payrollFormat'],);
                $searchArray = array_merge($searchArray, $payrollFormatArray);
            }
            
             if( !empty( $postArray['laborRegulationsName'] ) )
            {
                $sql .= ' AND labor_regulations_id = :laborRegulationsName ';
                $lbRegulationsArray = array(':laborRegulationsName' => $postArray['laborRegulationsName'],);
                $searchArray = array_merge($searchArray, $lbRegulationsArray);
            }

            if( !empty( $postArray['organizationID'] ) )
            {
                $sql .= ' AND organization_id = :organizationID ';
                $organizationIDArray = array(':organizationID' => $postArray['organizationID'],);
                $searchArray = array_merge($searchArray, $organizationIDArray);
            }

            if( !empty( $postArray['upperLevelOrganization'] ) )
            {
                $sql .= ' AND upper_level_organization = :upperLevelOrganization ';
                $upOrganizationArray = array(':upperLevelOrganization' => $postArray['upperLevelOrganization'],);
                $searchArray = array_merge($searchArray, $upOrganizationArray);
            }

            if( !empty( $postArray['departmentCode'] ) )
            {
                $sql .= ' AND department_code LIKE :department_code ';
                $departmentCode = "%" . $postArray['departmentCode'] . "%";
                $departmentCodeArray = array(':department_code' => $departmentCode,);
                $searchArray = array_merge($searchArray, $departmentCodeArray);
            }

            if( !empty( $postArray['startTimeDay'] ) )
            {
                $sql .= " AND to_char(start_time_day, 'HH24:MI') = :startTimeDay ";
                $startTimeDayArray = array(':startTimeDay' => $postArray['startTimeDay'],);
                $searchArray = array_merge($searchArray, $startTimeDayArray);
            }

           if( !empty( $postArray['commentSearch'] ) )
            {
                $sql .= ' AND comment LIKE :comment ';
                $commentSearch = "%" . $postArray['commentSearch'] . "%";
                $commentSearchArray = array(':comment' => $commentSearch,);
                $searchArray = array_merge($searchArray, $commentSearchArray);
            }

            $sql .= $this->generationSQL->creatAndEffCheckSQL($effFlag);
            
            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");

            return $sql;
        }

        /**
         * 組織マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   組織マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = ' ORDER BY disp_order';

            // ソート条件作成
            $sortSqlList = array(
                                 3      =>  ' ORDER BY eff_code DESC, organization_id, disp_order,department_code',                   // 状態の降順
                                 4      =>  ' ORDER BY eff_code, organization_id, disp_order,department_code',                        // 状態の昇順
                                 5      =>  ' ORDER BY department_code DESC, organization_id, disp_order,department_code',            // 部門コードの降順
                                 6      =>  ' ORDER BY department_code, organization_id, disp_order,department_code',                 // 部門コードの昇順
                                 7      =>  ' ORDER BY organization_name DESC, organization_id, disp_order,department_code',          // 組織名の降順
                                 8      =>  ' ORDER BY organization_name, organization_id, disp_order,department_code',               // 組織名の昇順
                                 9      =>  ' ORDER BY abbreviated_name DESC, organization_id, disp_order,department_code',           // 組織略称の降順
                                10      =>  ' ORDER BY abbreviated_name, organization_id, disp_order,department_code',                // 組織略称の昇順
                                11      =>  ' ORDER BY application_date_start DESC, organization_id, disp_order,department_code',     // 適用開始日の降順
                                12      =>  ' ORDER BY application_date_start, organization_id, disp_order,department_code',          // 適用開始日の昇順
                                13      =>  ' ORDER BY start_time_day DESC, organization_id, disp_order,department_code',             // 1日の開始時間の降順
                                14      =>  ' ORDER BY start_time_day, organization_id, disp_order,department_code',                  // 1日の開始時間の昇順
                                15      =>  ' ORDER BY priority_o DESC, organization_id, disp_order,department_code',                 // 就業規則優先順位の降順
                                16      =>  ' ORDER BY priority_o, organization_id, disp_order,department_code',                      // 就業規則優先順位の昇順
                                17      =>  ' ORDER BY labor_regulations_name DESC, organization_id, disp_order,department_code',     // 就業規則名の降順
                                18      =>  ' ORDER BY labor_regulations_name, organization_id, disp_order,department_code',          // 就業規則名の昇順
                                19      =>  ' ORDER BY payroll_system_name DESC, organization_id, disp_order,department_code',        // 給与フォーマットの降順
                                20      =>  ' ORDER BY payroll_system_name, organization_id, disp_order,department_code',             // 給与フォーマットの昇順
                                21      =>  ' ORDER BY upper_level_organization DESC, organization_id, disp_order,department_code',   // 上位組織の降順
                                22      =>  ' ORDER BY upper_level_organization, organization_id, disp_order,department_code',        // 上位組織の昇順
                                23      =>  ' ORDER BY authentication_key DESC, organization_id, disp_order,department_code',         // 認証キーの降順
                                24      =>  ' ORDER BY authentication_key, organization_id, disp_order,department_code',              // 認証キーの昇順
                                25      =>  ' ORDER BY comment DESC, organization_id, disp_order,department_code',                    // コメントの降順
                                26      =>  ' ORDER BY comment, organization_id, disp_order,department_code',                         // コメントの昇順
                                27      =>  ' ORDER BY disp_order DESC, organization_id, disp_order,department_code',                 // 表示順の降順
                                28      =>  ' ORDER BY disp_order, organization_id, disp_order,department_code',                      // 表示順の昇順
                            );
            // ソート条件
            if( array_key_exists( $sortNo, $sortSqlList ) )
            {
                $sql = $sortSqlList[$sortNo];
            }
            
            $Log->trace("END creatSortSQL");
            
            return $sql;
        }
    }
?>
