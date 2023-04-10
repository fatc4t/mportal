<?php
    /**
     * @file      従業員ベースマスタ
     * @author    USE M.Higashihara
     * @date      2016/07/05
     * @version   1.00
     * @note      従業員マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 従業員ベースクラス
     * @note   従業員マスタテーブルの管理を行う。
     */
    class BaseUser extends BaseModel
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
         * 従業員マスタ一覧画面一覧表
         * @param    $postArray    入力パラメータ
         * @param    $effFlag      状態フラグ
         * @param    $statusFlag   在籍状況フラグ
         * @return   成功時：$userDataList 失敗：無
         */
        public function getListData( $postArray, $effFlag, $statusFlag )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            // 一覧検索用のSQL文と検索条件が入った配列の生成
            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray, $effFlag, $statusFlag );

            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);

            // 一覧表を格納する空の配列宣言
            $userDataList = array();

            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $userDataList;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $userDataList, $data);
            }

            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $userList = $userDataList;
            }
            else
            {
                // 組織の表示順に並びかえる
                $userList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $userDataList);
                if( $postArray['sort'] == 1 )
                {
                    $userList = array_reverse($userList);
                }
            }

            // 一覧画面で閲覧ができるユーザが所属する組織に編集と削除権限があるか判定
            $userList = $this->updateControl($userList);

            $Log->trace("END getListData");

            // 一覧表を返す
            return $userList;
        }

        /**
         * 従業員マスタ新規データ登録
         * @param    $postArray(生年月日/性別/入社日/退社日/試用期間種別ID/試用期間の値/試用期間中の賃金/試用期間賃金値/削除フラグ/登録ユーザID/登録組織ID)
         * @return   SQLの実行結果($LastUserId/false)
         */
        protected function addUserNewData($postArray, &$userId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addUserNewData");

            $sqlParamenter = "";

            $parameters = array(
                ':is_del'                           => $postArray['is_del'],
                ':registration_user_id'             => $postArray['user_id'],
                ':registration_organization'        => $postArray['organization'],
                ':update_user_id'                   => $postArray['user_id'],
                ':update_organization'              => $postArray['organization'],
            );

            // 従業員マスタのカラムリスト
            $columnFlag = true;
            $columnList = $this->getColumnList($columnFlag);

            $sqlColumn = $this->generationSQL->creatInsertSQL($postArray, $sqlParamenter, $parameters, $columnList);

            $sql = ' INSERT INTO m_user(is_del'
                 . '                  , registration_time'
                 . '                  , registration_user_id'
                 . '                  , registration_organization'
                 . '                  , update_time'
                 . '                  , update_user_id'
                 . '                  , update_organization';
            $sql .= $sqlColumn;
            $sql .= '                  ) VALUES ('
                 . '                    :is_del'
                 . '                  , current_timestamp'
                 . '                  , :registration_user_id'
                 . '                  , :registration_organization'
                 . '                  , current_timestamp'
                 . '                  , :update_user_id'
                 . '                  , :update_organization';
            $sql .= $sqlParamenter;
            $sql .= ')';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3080");
                $errMsg = "従業員No：" . $postarray['employees_no']. "従業員姓名：" . $postarray['user_name'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3080";
            }

            // Lastidの更新
            $userId = $DBA->lastInsertId( "m_user" );

            $Log->trace("END addUserNewData");

            return "MSG_BASE_0000";
        }

        /**
         * 従業員詳細マスタ新規データ登録
         * @param    $postArray(適用開始日時/従業員NO/組織ID/役職ID/雇用形態ID/セクションID/賃金形態ID/セキュリティID/従業員姓名/住所/電話番号/携帯電話/メールアドレス/時給/基本給/コメント)
         * @param    $userDetailId 
         * @return   SQLの実行結果($LastUserDetailId/false)
         */
        protected function addUserDetailNewData($postArray, &$userDetailId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addUserDetailNewData");

            $sqlDetailParamenter = "";

            $parameters = array(
                ':user_id'                => $postArray['regUserId'],
            );

            // 従業員詳細マスタのカラムリスト
            $columnFlag = false;
            $columnList = $this->getColumnList($columnFlag);

            $sqlDetailColumn = $this->generationSQL->creatInsertSQL($postArray, $sqlDetailParamenter, $parameters, $columnList);

            $sql = 'INSERT INTO m_user_detail( user_id';
            $sql .= $sqlDetailColumn;
            $sql .= '                         ) VALUES ('
                 . '                           :user_id';
            $sql .= $sqlDetailParamenter;
            $sql .= ')';

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3081");
                $errMsg = "従業員No：" . $postarray['employees_no']. "従業員姓名：" . $postarray['user_name'] . "の登に失敗しました。";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3081";
            }

            // Lastidの更新
            $userDetailId = $DBA->lastInsertId( "m_user_detail" );

            $Log->trace("END addUserDetailNewData");

            return "MSG_BASE_0000";
        }

        /**
         * 新規ログインアカウント登録
         * @param    $postArray(ログインID/パスワード)
         * @param    $userId
         * @return   SQLの実行結果(true/false)
         */
        protected function addLoginAccountNewData($postArray, $userId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addLoginAccountNewData");

            $sql = 'INSERT INTO m_login(login_id'
                 . '                  , password'
                 . '                  , user_id'
                 . '                  ) VALUES ('
                 . '                    :login_id'
                 . '                  , :password'
                 . '                  , :user_id)';
            $parameters = array(
                ':login_id'            => $postArray['login_id'],
                ':password'            => $postArray['password'],
                ':user_id'             => $userId,
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3060");
                $errMsg = "ログインID：" . $postarray['login_id']. "パスワード：" . $postarray['password'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3060";
            }

            $Log->trace("END addLoginAccountNewData");

            return "MSG_BASE_0000";
        }

        /**
         * 承認組織マスタ新規データ登録
         * @param    $userDetailId(ユーザ詳細ID)
         * @param    $organization_id(組織ID)
         * @return   SQLの実行結果(true/false)
         */
        protected function addApprovalOrganizitionNewData($userDetailId, $organization_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addApprovalOrganizitionNewData");

            $sql = 'INSERT INTO m_approval_organization( user_detail_id'
                 . '                                   , organization_id'
                 . '                                   ) VALUES ('
                 . '                                     :user_detail_id'
                 . '                                   , :organization_id)';
            $parameters = array(
                ':user_detail_id'  => $userDetailId,
                ':organization_id' => $organization_id,
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3083");
                $errMsg = "従業員ID：" . $userDetailId. "組織ID：" . $organization_id . "の承認組織登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3083";
            }

            $Log->trace("END addApprovalOrganizitionNewData");

            return "MSG_BASE_0000";
        }

        /**
         * 従業員手当マスタ新規データ登録
         * @param    $postArray(手当ID/手当金額)
         * @param    $userDetailId(ユーザ詳細ID)
         * @return   SQLの実行結果(true/false)
         */
        protected function addMUserAllowanceNewData($postArray, $userDetailId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addMUserAllowanceNewData");

            $sql = 'INSERT INTO m_user_allowance( user_detail_id'
                 . '                            , allowance_id'
                 . '                            , allowance_amount'
                 . '                            ) VALUES ('
                 . '                              :user_detail_id'
                 . '                            , :allowance_id'
                 . '                            , :allowance_amount)';

            $parameters = array(
                ':user_detail_id'   => $userDetailId,
                ':allowance_id'     => $postArray['allowance_id'],
                ':allowance_amount' => $postArray['allowance_amount'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3084");
                $errMsg = "従業員詳細ID：" . $userDetailId. "手当ID：" . $postArray['allowance_id'] . "の登録失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3084";
            }

            $Log->trace("END addMUserAllowanceNewData");

            return "MSG_BASE_0000";
        }

        /**
         * 従業員マスタと従業員詳細マスタのカラムリスト
         * @param    $flag(従業員マスタからの呼び出し時はtrue)
         * @return   カラムのリスト
         */
        protected function getColumnList($flag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getColumnList");

            $columnList = array();
            if($flag)
            {
                $columnList = array(
                    'birthday'                         => 'date',
                    'sex'                              => '',
                    'hire_date'                        => 'date',
                    'leaving_date'                     => 'date',
                    'trial_period_type_id'             => '',
                    'trial_period_criteria_value'      => '',
                    'trial_period_write_down_criteria' => '',
                    'trial_period_wages_value'         => '',
                );
            }
            else
            {
                // 従業員マスタのカラムリスト
                $columnList = array(
                    'application_date_start'           => 'date',
                    'employees_no'                     => '',
                    'organization_id'                  => '',
                    'position_id'                      => '',
                    'employment_id'                    => '',
                    'section_id'                       => '',
                    'is_embossing'                     => '',
                    'wage_form_id'                     => '',
                    'security_id'                      => '',
                    'user_name'                        => '',
                    'address'                          => '',
                    'tel'                              => '',
                    'cellphone'                        => '',
                    'mail_address'                     => '',
                    'hourly_wage'                      => '',
                    'base_salary'                      => '',
                    'comment'                          => '',
                );
            }

            $Log->trace("END getColumnList");

            return $columnList;
        }

        /**
         * 従業員マスタ一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @param    $effFlag                   検索条件用パラメータ
         * @param    $statusFlag                検索条件用パラメータ
         * @return   従業員マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray, $effFlag, $statusFlag )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            // 参照権限による検索条件追加
            $searchedColumn = ' WHERE ';

            $sql = ' SELECT user_id, user_detail_id, eff_code, status '
                 . ' , hire_date, leaving_date, employees_no, user_name '
                 . ' , tel, cellphone, base_salary, hourly_wage '
                 . ' , application_date_start, user_comment, position_name '
                 . ' , employment_name, section_name, wage_form_name '
                 . ' , security_name, abbreviated_name, approvallist '
                 . ' , organization_id FROM vm_user ';
            $sql .= $this->creatSqlWhereInConditions($searchedColumn);
            $sql .= $this->creatAndSQL($postArray, $searchArray);
            $sql .= $this->creatAndLikeSQL($postArray, $searchArray);
            $sql .= $this->creatAndAnySQL($postArray, $searchArray);
            $sql .= $this->creatSalaryRangeSQL($postArray, $searchArray);
            $sql .= $this->creatPeriodRangeSQL($postArray, $searchArray);
            $sql .= $this->generationSQL->creatAndEffCheckSQL($effFlag);
            $sql .= $this->generationSQL->creatAndStatusCheckSQL($statusFlag);
            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");

            return $sql;
        }

        /**
         * 一覧検索条件追加SQL作成(完全一致検索)
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ（参照渡しで返す）
         * @return   従業員マスタ一覧取得SQL文
         */
        private function creatAndSQL($postArray, &$searchArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAndSQL");

            $sqlAnd = '';
            if( !empty( $postArray['userNo'] ) )
            {
                $sqlAnd .= ' AND employees_no = :userNo ';
                $searchArray = array_merge($searchArray, array(':userNo' => $postArray['userNo'],));
            }
            if( !empty( $postArray['organizationID'] ) )
            {
                $sqlAnd .= ' AND organization_id = :organizationID ';
                $searchArray = array_merge($searchArray, array(':organizationID' => $postArray['organizationID'],));
            }
            if( !empty( $postArray['wageForm'] ) )
            {
                $sqlAnd .= ' AND wage_form_id = :wage_form_id ';
                $searchArray = array_merge($searchArray, array(':wage_form_id' => $postArray['wageForm'],));
            }

            $Log->trace("END creatAndSQL");

            return $sqlAnd;
        }

        /**
         * 一覧検索条件追加SQL作成(部分検索)
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ（参照渡しで返す）
         * @return   従業員マスタ一覧取得SQL文
         */
        private function creatAndLikeSQL($postArray, &$searchArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAndLikeSQL");

            $sqlAndLike = '';
            if( !empty( $postArray['userName'] ) )
            {
                $sqlAndLike .= ' AND user_name LIKE :user_name ';
                $userName = "%" . $postArray['userName'] . "%";
                $searchArray = array_merge($searchArray, array(':user_name' => $userName,));
            }
            if( !empty( $postArray['positionName'] ) )
            {
                $sqlAndLike .= ' AND position_name LIKE :position_name ';
                $positionName = "%" . $postArray['positionName'] . "%";
                $searchArray = array_merge($searchArray, array(':position_name' => $positionName,));
            }
            if( !empty( $postArray['employmentName'] ) )
            {
                $sqlAndLike .= ' AND employment_name LIKE :employment_name ';
                $employmentName = "%" . $postArray['employmentName'] . "%";
                $searchArray = array_merge($searchArray, array(':employment_name' => $employmentName,));
            }
            if( !empty( $postArray['sectionName'] ) )
            {
                $sqlAndLike .= ' AND section_name LIKE :section_name ';
                $sectionName = "%" . $postArray['sectionName'] . "%";
                $searchArray = array_merge($searchArray, array(':section_name' => $sectionName,));
            }
            if( !empty( $postArray['security'] ) )
            {
                $sqlAndLike .= ' AND security_name LIKE :security_name ';
                $securityName = "%" . $postArray['security'] . "%";
                $searchArray = array_merge($searchArray, array(':security_name' => $securityName,));
            }
            if( !empty( $postArray['comment'] ) )
            {
                $sqlAndLike .= ' AND comment LIKE :comment ';
                $comment = "%" . $postArray['comment'] . "%";
                $searchArray = array_merge($searchArray, array(':comment' => $comment,));
            }

            $Log->trace("END creatAndLikeSQL");

            return $sqlAndLike;
        }

        /**
         * 一覧検索条件追加SQL作成(配列検索)
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ（参照渡しで返す）
         * @return   従業員マスタ一覧取得SQL文
         */
        private function creatAndAnySQL($postArray, &$searchArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatAndAnySQL");

            $sqlAndAny = '';
            if( !empty( $postArray['approval'] ) )
            {
                $sqlAndAny .= ' AND :approval =  ANY(approvallist) ';
                $searchArray = array_merge($searchArray, array(':approval' => $postArray['approval'],));
            }

            $Log->trace("END creatAndAnySQL");

            return $sqlAndAny;
        }

        /**
         * 一覧検索条件追加SQL作成（給料範囲指定）
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ（参照渡しで返す）
         * @return   従業員マスタ一覧取得SQL文
         */
        private function creatSalaryRangeSQL($postArray, &$searchArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSalaryRangeSQL");

            $sqlSalaryRange = '';

            if( !empty( $postArray['minBaseSalaey'] ) )
            {
                $sqlSalaryRange .= " AND base_salary >= :min_base_salary ";
                $searchArray = array_merge($searchArray, array(':min_base_salary' => $postArray['minBaseSalaey'],));
            }
            if( !empty( $postArray['maxBaseSalaey'] ) )
            {
                $sqlSalaryRange .= " AND base_salary <= :max_base_salary ";
                $searchArray = array_merge($searchArray, array(':max_base_salary' => $postArray['maxBaseSalaey'],));
            }
            if( !empty( $postArray['minHourlyWage'] ) )
            {
                $sqlSalaryRange .= " AND hourly_wage >= :min_hourly_wage ";
                $searchArray = array_merge($searchArray, array(':min_hourly_wage' => $postArray['minHourlyWage'],));
            }
            if( !empty( $postArray['maxHourlyWage'] ) )
            {
                $sqlSalaryRange .= " AND hourly_wage <= :max_hourly_wage ";
                $searchArray = array_merge($searchArray, array(':max_hourly_wage' => $postArray['maxHourlyWage'],));
            }

            $Log->trace("END creatSalaryRangeSQL");

            return $sqlSalaryRange;
        }

        /**
         * 一覧検索条件追加SQL作成（期間範囲指定）
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ（参照渡しで返す）
         * @return   従業員マスタ一覧取得SQL文
         */
        private function creatPeriodRangeSQL($postArray, &$searchArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatPeriodRangeSQL");

            $sqlPeriodRange = '';

            if( !empty( $postArray['sAppStartDate'] ) )
            {
                $sqlPeriodRange .= " AND application_date_start >= :s_app_start_date ";
                $searchArray = array_merge($searchArray, array(':s_app_start_date' => $postArray['sAppStartDate'],));
            }
            if( !empty( $postArray['eAppStartDate'] ) )
            {
                $sqlPeriodRange .= " AND application_date_start <= :e_app_start_date ";
                $searchArray = array_merge($searchArray, array(':e_app_start_date' => $postArray['eAppStartDate'],));
            }
            if( !empty( $postArray['startHireDate'] ) )
            {
                $sqlPeriodRange .= " AND hire_date >= :start_hire_date ";
                $searchArray = array_merge($searchArray, array(':start_hire_date' => $postArray['startHireDate'],));
            }
            if( !empty( $postArray['endHireDate'] ) )
            {
                $sqlPeriodRange .= " AND hire_date <= :end_hire_date ";
                $searchArray = array_merge($searchArray, array(':end_hire_date' => $postArray['endHireDate'],));
            }
            if( !empty( $postArray['startLeavingDate'] ) )
            {
                $sqlPeriodRange .= " AND leaving_date >= :start_leaving_date ";
                $searchArray = array_merge($searchArray, array(':start_leaving_date' => $postArray['startLeavingDate'],));
            }
            if( !empty( $postArray['endLeavingDate'] ) )
            {
                $sqlPeriodRange .= " AND leaving_date <= :end_leaving_date ";
                $searchArray = array_merge($searchArray, array(':end_leaving_date' => $postArray['endLeavingDate'],));
            }

            $Log->trace("END creatPeriodRangeSQL");

            return $sqlPeriodRange;
        }

        /**
         * 従業員マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   従業員マスタソート条件追加SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sqlSort = ' ORDER BY p_disp_order, employees_no';

            // ソート条件作成
            $sortSqlList = array(
                                3    => ' ORDER BY eff_code DESC, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                4    => ' ORDER BY eff_code, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                5    => ' ORDER BY status DESC, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                6    => ' ORDER BY status, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                7    => ' ORDER BY employees_no DESC, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                8    => ' ORDER BY employees_no, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                9    => ' ORDER BY user_name DESC, p_disp_order, p_code, e_disp_order, e_code ',
                                10   => ' ORDER BY user_name, p_disp_order, p_code, e_disp_order, e_code ',
                                11   => ' ORDER BY tel DESC NULLS LAST, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                12   => ' ORDER BY tel NULLS FIRST, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                13   => ' ORDER BY o_disp_order DESC, department_code DESC, abbreviated_name DESC, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                14   => ' ORDER BY o_disp_order, department_code, abbreviated_name, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                15   => ' ORDER BY p_disp_order DESC, p_code DESC, e_disp_order, e_code, user_name ',
                                16   => ' ORDER BY p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                17   => ' ORDER BY e_disp_order DESC, e_code DESC, p_disp_order, p_code, user_name ',
                                18   => ' ORDER BY e_disp_order, e_code, p_disp_order, p_code, user_name ',
                                19   => ' ORDER BY s_disp_order DESC NULLS LAST, s_code DESC NULLS LAST, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                20   => ' ORDER BY s_disp_order NULLS FIRST, s_code NULLS FIRST, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                21   => ' ORDER BY w_disp_order DESC, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                22   => ' ORDER BY w_disp_order, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                23   => ' ORDER BY base_salary DESC NULLS LAST, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                24   => ' ORDER BY base_salary NULLS FIRST, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                25   => ' ORDER BY hourly_wage DESC NULLS LAST, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                26   => ' ORDER BY hourly_wage NULLS FIRST, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                27   => ' ORDER BY hire_date DESC, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                28   => ' ORDER BY hire_date, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                29   => ' ORDER BY leaving_date DESC NULLS LAST, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                30   => ' ORDER BY leaving_date NULLS FIRST, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                31   => ' ORDER BY application_date_start DESC, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                32   => ' ORDER BY application_date_start, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                33   => ' ORDER BY approvallist DESC, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                34   => ' ORDER BY approvallist, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                35   => ' ORDER BY se_disp_order DESC, p_code, e_disp_order, e_code, user_name ',
                                36   => ' ORDER BY se_disp_order, p_code, e_disp_order, e_code, user_name ',
                                37   => ' ORDER BY user_comment DESC NULLS LAST, p_disp_order, p_code, e_disp_order, e_code, user_name ',
                                38   => ' ORDER BY user_comment NULLS FIRST, p_disp_order, p_code, e_disp_order, e_code, user_name ',
            );

            // ソート条件
            if( array_key_exists( $sortNo, $sortSqlList ) )
            {
                $sqlSort = $sortSqlList[$sortNo];
            }

            $Log->trace("END creatSortSQL");

            return $sqlSort;
        }

    }

?>