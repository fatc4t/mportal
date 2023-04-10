<?php
    /**
     * @file      ログイン処理を行う
     * @author    USE Y.Sakata
     * @date      2016/04/27
     * @version   1.00
     * @note      ログイン時の認証処理及び、セッションの初期設定を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    require './Model/Common/SetSystemInitialization.php';
    
    // M-PORTAL管理 初期化クラスファイル読み込み
    require './Model/Common/SetMasterInitialization.php';
    // 勤怠システム用初期化クラスファイル読み込み
    require './Model/Common/SetAttendanceInitialization.php';
    // 売上管理用初期化クラスファイル読み込み
    require './Model/Common/SetProfitInitialization.php';
    // トップメッセージ初期化クラスファイル読み込み
    require './Model/Common/SetTopMessageInitialization.php';
    // トップメッセージ初期化クラスファイル読み込み
    require './Model/Common/SetTelopInitialization.php';
    // 日報初期化クラスファイル読み込み
    require './Model/Common/SetDailyReportInitialization.php';
    // 通達連絡初期化クラスファイル読み込み
    require './Model/Common/SetNoticeContactInitialization.php';
    // 顧客管理初期化クラスファイル読み込み
    require './Model/Common/SetCustomerInitialization.php';
    // 薬剤発注管理初期化クラスファイル読み込み
    require './Model/Common/SetOrderInitialization.php';
    
    require './Model/Common/SetOrdermInitialization.php';
    

    /**
     * ログインクラス
     * @note   ログイン時の認証処理及び、セッションの初期設定を行う
     */
    class Login extends BaseModel
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
                    "login_id"   => $postArray['login_id'],
                    "password"   => '',
                    "companyID"  => $postArray['companyID'],
                    "errMes"     => $errorMessage,
                );

                // エラーログ出力
                $Log->warn( "MSG_BASE_0103" );
                $Log->trace("END   authentication");
                return false;
            }

            // 会社IDをセッションIDに設定
            $_SESSION["SCHEMA"] = $schema;
            $_SESSION["COMPANY_ID"] = $postArray['companyID'];
            $_SESSION["LOGIN"]      = $postArray['login_id'];  
            // IDとパスワードを取得
            $sql = ' SELECT u.user_id FROM m_login l INNER JOIN m_user u ON u.user_id = l.user_id '
                 . ' WHERE login_id = :login_id AND password = :password AND u.is_del = 0 ';
            $parametersArray = array( ':login_id' => $postArray['login_id'],
                            ':password' => $postArray['password'], );
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
                        "login_id"   => $postArray['login_id'],
                        "password"   => $postArray['password'],
                        "companyID"  => $postArray['companyID'],
                        "errMes"     => "",
                    );

                    // ログイン成功
                    $Log->trace("END   authentication");
                    return true;
                }

                // 認証エラーのメッセージ設定
                $errMes = $Log->getMsgLog("MSG_BASE_0102");

            }
            else
            {
                // 障害のメッセージ設定
                $errMes = $Log->getMsgLog("MSG_FW_DB_FAILURE") . "<br>" . $Log->getMsgLog("MSG_FW_FAILURE");
            }

            // ログインエラー
            $retArray = array(
                "login_id"     => $postArray['login_id'],
                "password"     => '',
                "companyID"  => $postArray['companyID'],
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

            // ログインIDの入力チェック
            if ( empty( $postArray['login_id'] ) )
            {
                $errorMessage = $Log->getMsgLog("MSG_BASE_0100");
                // エラーログ出力
                $Log->warn( "MSG_BASE_0100" );
            }

            // パスワードの入力チェック
            if ( empty( $postArray['password'] ) )
            {
                $errorMessage = $errorMessage . $Log->getMsgLog("MSG_BASE_0101");
                // エラーログ出力
                $Log->warn( "MSG_BASE_0101" );
            }

            // URLに誤りがある
            if( $postArray['companyID'] === '' )
            {
                $errorMessage = $errorMessage . $Log->getMsgLog("MSG_BASE_0103");
                // エラーログ出力
                $Log->warn( "MSG_BASE_0103" );
            }

            // 上記の処理にエラー
            if($errorMessage != "")
            {
                $retArray = array(
                    "login_id"  => $postArray['login_id'],
                    "password"  => '',
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

			$Log->debug($user_id);

            // M-PORTAL全体の初期設定
            $setMaster = new SetMasterInitialization();
            $setMaster->setMasterInit( $user_id );

            // 勤怠管理システムの初期設定
            $setAttendance = new SetAttendanceInitialization();
            $setAttendance->setAttendanceInit();

            // 売上管理システムの初期設定
            $setProfit = new SetProfitInitialization();
            $setProfit->setProfitInit();

            // トップメッセージの初期設定
            $setTopMessage = new SetTopMessageInitialization();
            $setTopMessage->setTopMessageInit();

            // テロップの初期設定
            $setTelop = new SetTelopInitialization();
            $setTelop->setTelopInit();

            // 日報の初期設定
            $setDailyReport = new SetDailyReportInitialization();
            $setDailyReport->setDailyReportInit();

            // 通達連絡の初期設定
            $setNoticeContact = new SetNoticeContactInitialization();
            $setNoticeContact->setNoticeContactInit();

            // 顧客管理システムの初期設定
            $setCustomer = new SetCustomerInitialization();
            $setCustomer->setCustomerInit();
            
            // システムの初期設定
            $setSystem = new SetSystemInitialization();
            $setSystem->setSystemInit();            

            // 薬剤発注管理システムの初期設定
            $setOrder = new SetOrderInitialization();
            $setOrder->setOrderInit();

            $setOrderm = new SetOrdermInitialization();
            $setOrderm->setOrdermInit();            
            // Js script version
            $_SESSION["AUTOFIL_VER"] = '1.2';

          
            
            $Log->trace("END   setSession");
        }

    }
?>
