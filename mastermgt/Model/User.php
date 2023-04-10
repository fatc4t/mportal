<?php
    /**
     * @file      従業員マスタ
     * @author    USE M.Higashihara
     * @date      2016/06/09
     * @version   1.00
     * @note      従業員マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/UserInputData.php';

    /**
     * 従業員クラス
     * @note   従業員マスタテーブルの管理を行う。
     */
    class User extends UserInputData
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
         * 従業員マスタ新規データ登録
         * @param    $postArray(従業員マスタ、または従業員詳細マスタへの登録情報)
         * @param    $approvalArray(承認組織情報)
         * @param    $allowanceArray(手当情報)
         * @param    $addFlag(新規登録時にはtrue)
         * @return   SQLの実行結果
         */
        public function addNewData($postArray, $approvalArray, $allowanceArray, $addFlag)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            if($DBA->beginTransaction())
            {
                $ret = "";
                // ユーザマスタ追加/更新振り分け後、対象の従業員ID取得
                $userId = "";
                $ret = $this->userProcessingDistribution($postArray, $addFlag, $userId);
                // ユーザマスタの追加/更新の結果がfalseの場合ロールバック
                if($ret !== "MSG_BASE_0000")
                {
                    $DBA->rollBack();
                    $errMsg = "ユーザマスタへの登録更新処理にエラーが生じました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return $ret;
                }
                // ユーザ詳細マスタ追加/更新振り分け後、対象の従業員詳細ID取得
                $userDetailId = "";
                $ret = $this->userDetailPreErrorCheck($postArray, $userId, $addFlag, $userDetailId);
                // ユーザマスタの追加/更新の結果がfalseの場合ロールバック
                if($ret !== "MSG_BASE_0000")
                {
                    $DBA->rollBack();
                    $errMsg = "ユーザ詳細マスタへの登録更新処理にエラーが生じました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return $ret;
                }
                // ログインアカウントの追加/更新/削除処理
                $ret = $this->loginProcessingDistribution($postArray, $userId, $addFlag);
                // ログインアカウントの追加/更新/削除に失敗した場合ロールバック
                if($ret !== "MSG_BASE_0000")
                {
                    $DBA->rollBack();
                    $errMsg = "ログインマスタへの登録更新処理にエラーが生じました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return $ret;
                }
                // 承認組織の追加/更新/削除処理
                $approvalFlag = true;
                $ret = $this->roopUpdateProcessingDistribution($approvalArray, $userDetailId, $addFlag, $approvalFlag);
                // 承認組織の追加/更新/削除に失敗した場合ロールバック
                if($ret !== "MSG_BASE_0000")
                {
                    $DBA->rollBack();
                    $errMsg = "承認組織マスタへの登録更新処理にエラーが生じました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return $ret;
                }
                // 従業員手当マスタの追加/更新/削除処理
                $allowanceFlag = false;
                $ret = $this->roopUpdateProcessingDistribution($allowanceArray, $userDetailId, $addFlag, $allowanceFlag);
                // 従業員手当マスタの追加/更新/削除に失敗した場合ロールバック
                if($ret !== "MSG_BASE_0000")
                {
                    $DBA->rollBack();
                    $errMsg = "承認組織マスタの処理に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return $ret;
                }
                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "従業員マスタ登録処理のコミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
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

            $Log->trace("END addNewData");
            return $ret;
        }

        /**
         * 従業員マスタ追加/更新処理振り分け
         * @param    $postArray(従業員マスタ、または従業員詳細マスタへの登録情報)
         * @param    $addFlag
         * @param    $userId(参照して引き渡すため空文字)
         * @return   SQL実行結果（定型文）
         */
        private function userProcessingDistribution($postArray, $addFlag, &$userId)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START userProcessingDistribution");

            // 新規登録か既存組織の適用予定作成かの判定
            if($addFlag)
            {
                // 新規登録の場合従業員マスタへ登録
                $ret = $this->addUserNewData($postArray, $userId);
            }
            else
            {
                // 従業員マスタ更新
                $ret = $this->modUserUpdateData($postArray);
                $userId = $postArray['up_user_id'];
            }

            $Log->trace("END userProcessingDistribution");
            return $ret;
        }

        /**
         * 従業員詳細マスタ追加/更新処理振り分け
         * @param    $postArray(従業員マスタ、または従業員詳細マスタへの登録情報)
         * @param    $userId
         * @param    $addFlag
         * @param    $userDetailId (参照して引き渡すため空文字)
         * @return   SQL実行結果（定型文）
         */
        private function userDetailPreErrorCheck($postArray, $userId, $addFlag, &$userDetailId)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START userDetailPreErrorCheck");

            // 登録済みの従業員NOに対して、修正がかかった場合に登録しようとしている組織が使っている給与フォーマット内で従業員Noが重複しないよう判定する
            if($postArray['up_employees_no'] != $postArray['employees_no'])
            {
                $employeesNoList = $this->getEmployeesNoListInnerSalaryFormat($postArray['organization_id']);
                if(in_array($postArray['employees_no'], $employeesNoList))
                {
                    $Log->trace("START userDetailPreErrorCheck");
                    return "MSG_ERR_3092";
                }
            }
            // 更新予定適用開始日をUnixtimestamp形式に変換
            $regApplicationUNIX = strtotime($postArray['application_date_start']);
            // 既存登録適用開始日をUnixtimestamp形式に変換
            $comApplicationUNIX = strtotime($postArray['up_application_date_start']);
            // 現在日時をUnixtimestamp形式にて取得
            $timestamp = time();
            // 新規登録時以外、変更した適用開始時が現在日時より過去だった場合エラーとする
            if(empty($addFlag))
            {
                if($regApplicationUNIX != $comApplicationUNIX && $timestamp > $regApplicationUNIX)
                {
                    $Log->trace("START userDetailPreErrorCheck");
                    return "MSG_ERR_3093";
                }
            }
            // POSTで送られてきた配列に登録済みの従業員IDを入れ込む
            $postArray['regUserId'] = $userId;
            // 登録更新の振り分け処理
            $ret = $this->userDetailProcessingDistribution($postArray, $userDetailId, $regApplicationUNIX, $comApplicationUNIX, $timestamp);
            // 返り値
            $Log->trace("END userDetailPreErrorCheck");
            return $ret;
        }

        /**
         * 従業員詳細マスタ追加/更新処理振り分け
         * @param    $postArray(従業員詳細マスタへの登録情報に登録済みの従業員IDを加えたもの)
         * @param    $userDetailId (参照して引き渡すため空文字)
         * @param    $regApplicationUNIX
         * @param    $comApplicationUNIX
         * @param    $timestamp
         * @return   SQL実行結果（定型文）
         */
        private function userDetailProcessingDistribution($postArray, &$userDetailId, $regApplicationUNIX, $comApplicationUNIX, $timestamp)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START userDetailProcessingDistribution");

            if($regApplicationUNIX == $comApplicationUNIX)
            {
                // 更新予定の適用開始日と既存登録の適用開始日が同じの場合、UPDATE処理
                $ret = $this->modUserDetailUpdateData($postArray);
                // 成功の場合更新対象の従業員詳細IDを参照渡しさせる
                $userDetailId = $postArray['up_user_detail_id'];
            }
            else
            {
                if($timestamp < $comApplicationUNIX)
                {
                    // 従業員詳細マスタへの更新
                    $ret = $this->modUserDetailUpdateData($postArray);
                    // 成功の場合更新対象の従業員詳細IDを参照渡しさせる
                    $userDetailId = $postArray['up_user_detail_id'];
                }
                else
                {
                    // 既存の適用予定情報の存在有無を判定し、なければ新規で適用予定情報を作成する。
                    $applicationCnt = $this->generationSQL->getRegistrationCount($postArray['regUserId'], "m_user_detail", "user_id", true);
                    if(empty($applicationCnt))
                    {
                        // 従業員詳細マスタへの登録
                        $ret = $this->addUserDetailNewData($postArray, $userDetailId);
                    }
                    else
                    {
                        $Log->trace("END userDetailProcessingDistribution");
                        return "MSG_ERR_3094";
                    }
                }
            }

            $Log->trace("END userDetailProcessingDistribution");
            return $ret;
        }

        /**
         * ログインマスタ追加/更新/削除処理振り分け
         * @param    $postArray(login_id/password)
         * @param    $userId
         * @param    $addFlag
         * @return   SQLの実行結果
         */
        private function loginProcessingDistribution($postArray, $userId, $addFlag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START loginProcessingDistribution");

            $loginAccountFlag = $this->getTargetDataPresence($userId, $addFlag, "m_login", "user_id");

            // ログイン/パスワードの両方入力がある場合には新規登録または修正処理、どちらも未入力の場合には登録データを確認後削除処理
            if(!empty($postArray['login_id']) && !empty($postArray['password']))
            {
                // 入力されたID・パスワードが、登録対象の従業員以外にすでに登録されていないか判定
                $loginOverlapFlag = $this->checkLoginOverlap($postArray, $userId);
                if(empty($loginOverlapFlag))
                {
                    $Log->trace("END loginProcessingDistribution");
                    return "MSG_ERR_3063";
                }

                // 新規登録時またはログインアカウントが存在しない場合には新規登録、それ以外は既存のアカウントの変更処理
                if(!empty($addFlag) || empty($loginAccountFlag))
                {
                    $ret = $this->addLoginAccountNewData($postArray, $userId);
                    return $ret;
                }
                else
                {
                    $ret = $this->modLoginAccountUpdateData($postArray, $userId);
                    return $ret;
                }
            }
            else if(empty($postArray['login_id']) && empty($postArray['password']))
            {
                // 登録データが存在する場合、削除処理
                $ret = $this->getTargetDataPresenceDelete($userId, $loginAccountFlag, "m_login", "user_id");
                return $ret;
            }

            $ret = $this->loginInputDetermination($postArray);

            $Log->trace("END loginProcessingDistribution");

            return $ret;
        }

        /**
         * ログインマスタ入力チェック
         * @param    $postArray(login_id/password)
         * @return   SQLの実行結果
         */
        private function loginInputDetermination($postArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START loginInputDetermination");

            $ret = "MSG_BASE_0000";
            if(!empty($postArray['login_id']) && empty($postArray['password']))
            {
                $ret = "MSG_ERR_3064";
            }
            else if(empty($postArray['login_id']) && !empty($postArray['password']))
            {
                $ret = "MSG_ERR_3065";
            }

            $Log->trace("END loginInputDetermination");

            return $ret;
        }

        /**
         * 承認組織マスタ/従業員手当マスタの新規追加/更新/削除処理振り分け
         * @param    $array
         * @param    $userDetailId(従業員詳細ID)
         * @param    $addFlag(新規登録判定フラグ)
         * @param    $flag(承認組織での処理時はtrue/手当での処理時はfalse)
         * @return   SQLの実行結果(true/false)
         */
        private function roopUpdateProcessingDistribution($array, $userDetailId, $addFlag, $flag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START roopUpdateProcessingDistribution");

            // 承認組織/手当の各テーブル名を取得
            $tableName = $this->getRoopUpdateTableName($flag);
            // パラメータ名
            $parameterName = "user_detail_id";
            // 新規登録ではない場合,対象従業員の別テーブルの登録情報が存在するか確認
            $registrationCnt = $this->getTargetDataPresence($userDetailId, $addFlag, $tableName, $parameterName);

            // 承認組織/従業員手当其々の処理振り分け
            if(!empty($flag))
            {
                // 入力データの有無判定
                if(!empty($array[0]['organization_id']))
                {
                    $ret = $this->addApprovalNumberOfTimes($array, $userDetailId, $registrationCnt, $tableName, $parameterName);
                }
                else
                {
                    // 登録データがある場合、データを削除する。
                    $ret = $this->getTargetDataPresenceDelete($userDetailId, $registrationCnt, $tableName, $parameterName);
                }
            }
            else
            {
                // 入力データの有無判定
                if(!empty($array[0]['allowance_id']))
                {
                    $ret = $this->addAllowanceNumberOfTimes($array, $userDetailId, $registrationCnt, $tableName, $parameterName);
                }
                else
                {
                    // 登録データがある場合、データを削除する。
                    $ret = $this->getTargetDataPresenceDelete($userDetailId, $registrationCnt, $tableName, $parameterName);
                }
            }

            $Log->trace("END roopUpdateProcessingDistribution");

            return $ret;
        }

        /**
         * 承認組織マスタ/従業員手当マスタ各テーブル名振り分け
         * @param    $flag(承認組織での処理時はtrue/手当での処理時はfalse)
         * @return   $tableName
         */
        private function getRoopUpdateTableName($flag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getRoopUpdateTableName");

            $tableName = "";
            if(!empty($flag))
            {
                $tableName = "m_approval_organization";
            }
            else
            {
                $tableName = "m_user_allowance";
            }

            $Log->trace("END getRoopUpdateTableName");
            return $tableName;
        }

        /**
         * 設定数分の承認組織新規登録処理
         * @param    $array
         * @param    $userDetailId(従業員詳細ID)
         * @param    $registrationCnt
         * @param    $tableName
         * @param    $parameterName
         * @return   SQLの実行結果(true/false)
         */
        private function addApprovalNumberOfTimes($array, $userDetailId, $registrationCnt, $tableName, $parameterName)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addApprovalNumberOfTimes");

            // 登録データがある場合、データを削除する。
            $ret = $this->getTargetDataPresenceDelete($userDetailId, $registrationCnt, $tableName, $parameterName);
            if($ret !== "MSG_BASE_0000")
            {
                $Log->trace("END addApprovalNumberOfTimes");
                return $ret;
            }
            // 入力データの数分登録処理を行う
            foreach($array as $data)
            {
                $ret = $this->addApprovalOrganizitionNewData($userDetailId, $data['organization_id']);
                if($ret !== "MSG_BASE_0000")
                {
                    $Log->trace("END addApprovalNumberOfTimes");
                    return $ret;
                }
            }

            $Log->trace("END addApprovalNumberOfTimes");
            return $ret;
        }

        /**
         * 設定数分の承認組織新規登録処理
         * @param    $array
         * @param    $userDetailId(従業員詳細ID)
         * @param    $registrationCnt
         * @param    $tableName
         * @param    $parameterName
         * @return   SQLの実行結果(true/false)
         */
        private function addAllowanceNumberOfTimes($array, $userDetailId, $registrationCnt, $tableName, $parameterName)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addAllowanceNumberOfTimes");

            // 登録データがある場合、データを削除する。
            $ret = $this->getTargetDataPresenceDelete($userDetailId, $registrationCnt, $tableName, $parameterName);
            if($ret !== "MSG_BASE_0000")
            {
                $Log->trace("END addApprovalNumberOfTimes");
                return $ret;
            }
            // 入力データの数分登録処理を行う
            foreach($array as $data)
            {
                $ret = $this->addMUserAllowanceNewData($data, $userDetailId);
                if($ret !== "MSG_BASE_0000")
                {
                    $Log->trace("END addApprovalNumberOfTimes");
                    return $ret;
                }
            }

            $Log->trace("END addAllowanceNumberOfTimes");
            return $ret;
        }

    }

?>