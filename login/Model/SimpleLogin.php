<?php
    /**
     * @file      従業員コードのみのログイン処理を行う
     * @author    USE Y.Sakata
     * @date      2016/09/28
     * @version   1.00
     * @note      ログイン時の認証処理及び、セッションの初期設定を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    // M-PORTAL管理 初期化クラスファイル読み込み
    require './Model/Common/SetMasterInitialization.php';
    // 勤怠システム用初期化クラスファイル読み込み
    require './Model/Common/SetAttendanceInitialization.php';

    /**
     * 簡易ログインクラス
     * @note   簡易ログイン時の認証処理及び、セッションの初期設定を行う
     */
    class SimpleLogin extends BaseModel
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
         * ユーザID/パスワード認証
         * @param    $postArray   入力パラメータ(login_id/password/companyID)
         * @param    &$retArray   上位へ返却するパラメータ(login_id/password/companyID/errMes)
         * @return   成功時：true  失敗：false
         */
        public function authentication( $postArray, &$retArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START authentication");

            // 入力チェックエラー
            if( $this->isInputError( $postArray, $retArray ) )
            {
                $Log->trace("END   authentication");
                // ログインエラー
                return false;
            }

            // スキーマ取得
            $schema = $this->getSchema( $postArray['companyID'] );
            if( "" === $schema )
            {
                // URL又は、契約情報に誤りがある
                $errorMessage = $Log->getMsgLog("MSG_BASE_0103");
                $retArray = array(
                    "userNo"    => $postArray['userNo'],
                    "code"      => $postArray['code'],
                    "companyID" => $postArray['companyID'],
                    "errMes"    => $errorMessage,
                );
                
                // エラーログ出力
                $Log->warn( "MSG_BASE_0103" );
                $Log->trace("END   authentication");
                return false;
            }

            // 会社IDをセッションIDに設定
            $_SESSION["SCHEMA"] = $schema;
            $_SESSION["COMPANY_ID"] = $postArray['companyID'];
            $_SESSION["CODE"] = $postArray['code'];
            // IDとパスワードを取得
            $sql = " SELECT user_id FROM v_user "
                 . " WHERE is_del = 0 AND eff_code = '適用中' AND status = '在籍' AND  "
                 . " employees_no = :employees_no AND department_code = :department_code ";
            $parametersArray = array( 
                                        ':employees_no'    => $postArray['userNo'],
                                        ':department_code' => $postArray['code'], 
                                    );
            $result = $DBA->executeSQL($sql, $parametersArray);

            $errMes = '';
            // DBアクセス時のエラーがある
            if( false !== $result )
            {
                $user_id = "";
                while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
                {
                    $user_id = $data['user_id'];
                }

                // 画面から入力されたID/パスワードが一致
                if ( "" !== $user_id )
                {
                    // 認証成功。セッションIDを新規に発行する
                    session_regenerate_id(true);
                    // 初期設定をセッション変数へ設定
                    $this->setSession( $user_id );
                    $retArray = array(
                                        "userNo"    => $postArray['userNo'],
                                        "code"      => $postArray['code'],
                                        "companyID" => $postArray['companyID'],
                                        "errMes"    => '',
                                     );
                    
                    // ログイン成功
                    $Log->trace("END   authentication");
                    return true;
                }
               
                // 認証エラーのメッセージ設定
                $errMes = $Log->getMsgLog("MSG_BASE_0105");
            }
            else
            {
                // 障害のメッセージ設定
                $errMes = $Log->getMsgLog("MSG_FW_DB_FAILURE") . "<br>" . $Log->getMsgLog("MSG_FW_FAILURE");
            }

            // ログインエラー
            $retArray = array(
                                "userNo"    => $postArray['userNo'],
                                "code"      => $postArray['code'],
                                "companyID" => $postArray['companyID'],
                                "errMes"     => $errMes,
                             );

            // ログインエラー
            $Log->trace("END   authentication");
            return false;
        }

        /**
         * 入力チェック
         * @param    $postArray   入力パラメータ(login_id/password/companyID)
         * @param    &$retArray   上位へ返却するパラメータ(login_id/password/companyID/errMes)
         * @return   成功時：true  失敗：false
         */
        private function isInputError( $postArray, &$retArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START isInputError");
            
            $errorMessage = "";

            // 従業員Noの入力チェック
            if ( empty( $postArray['userNo'] ) ) 
            {
                $errorMessage = $Log->getMsgLog("MSG_BASE_0104");
                // エラーログ出力
                $Log->warn( "MSG_BASE_0104" );
            }

            // URLに誤りがある(会社ID)
            if( $postArray['companyID'] === '' )
            {
                $errorMessage = $errorMessage . $Log->getMsgLog("MSG_BASE_0103");
                // エラーログ出力
                $Log->warn( "MSG_BASE_0103" );
            }

            // URLに誤りがある(部門コード)
            if( $postArray['code'] === '' && $postArray['companyID'] !== '' )
            {
                $errorMessage = $errorMessage . $Log->getMsgLog("MSG_BASE_0103");
                // エラーログ出力
                $Log->warn( "MSG_BASE_0103" );
            }

            // 上記の処理にエラー
            if($errorMessage != "")
            {
                $retArray = array(
                    "userNo"    => $postArray['userNo'],
                    "code"      => $postArray['code'],
                    "companyID" => $postArray['companyID'],
                    "errMes"    => $errorMessage,
                );
                
                $Log->trace("END   isInputError");
                return true;
            }

            $Log->trace("END   isInputError");
            return false;
        }

        /**
         * セッションへ初期設定
         * @note      [SECURITY_LOGOUT]をクリアする。システムで使用するセッション変数を設定
         * @param     $user_id   ユーザID
         * @return    なし
         */
        private function setSession( $user_id )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setSession");

            // セッション開始時間を設定する
            $_SESSION["SESSION_TIME"] = time();

            unset( $_SESSION["SECURITY_LOGOUT"] );
            
            // M-PORTAL全体の初期設定
            $setMaster = new SetMasterInitialization();
            $setMaster->setMasterInit( $user_id );
            
            // 勤怠管理システムの初期設定
            $setAttendance = new SetAttendanceInitialization();
            $setAttendance->setAttendanceInit();
            
            $Log->trace("END   setSession");
        }

    }
?>
