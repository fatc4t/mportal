<?php
    /**
     * @file      従業員コードのみ、ログインコントローラクラス
     * @author    USE Y.Sakata
     * @date      2016/09/28
     * @version   1.00
     * @note      ログイン時のリクエストを振り分ける
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/SimpleLogin.php';

    /**
     * 従業員コードのみ、ログインコントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class SimpleLoginController extends BaseController
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseControllerのコンストラクタ
            parent::__construct();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // BaseControllerのデストラクタ
            parent::__destruct();
        }

        /**
         * ログイン画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1010" );
            session_regenerate_id(true);
            
            $errMsg = '';
            $companyID = '';
            $code = '';
            if( isset($_GET['errMsg']) )
            {
                $errMsg = $Log->getMsgLog( parent::escStr( $_GET['errMsg'] ) );
            }
            
            if( isset($_GET['CompanyID']) )
            {
                $companyID = parent::escStr( $_GET['CompanyID'] );
            }
            
            if( isset($_GET['code']) )
            {
                $code = parent::escStr( $_GET['code'] );
            }
            
            $retArray = array(
                    "code"        => $code,
                    "userNo"      => '',
                    "companyID"   => $companyID,
                    "errMes"      => $errMsg,
            );
            $this->initialDisplay($retArray);
            $Log->trace("END   showAction");
        }
        
        /**
         * 簡易ログイン画面からの遷移
         * @return    なし
         */
        public function loginAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START loginAction");
            $Log->info( "MSG_INFO_1011" );
            // ログイン押下
            if (isset($_POST["login"]))
            {
                // 認証
                if( $this->authentication( $retArray ) )
                {
                    $Log->trace("END   loginAction");
                    $_SESSION["SIMPLE_LOGIN_CODE"] = $retArray["code"];
                    // 認証成功 (勤怠表示画面)
                    header('Location:../attendance/index.php?param=SimpleAttendance/show&home=1');
                }
                else
                {
                    // 認証エラー (ログイン画面へ)
                    $this->initialDisplay( $retArray );
                }
            }
            $Log->trace("END   loginAction");
        }

        /**
         * ログアウト処理
         * @return    なし
         */
        public function logoutAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START logoutAction");
            $Log->info( "MSG_INFO_1012" );

            $code = "";
            $companyID = "";
            if( isset($_GET['CompanyID']) )
            {
                $companyID = parent::escStr( $_GET['CompanyID'] );
            }
            
            if( isset($_GET['code']) )
            {
                $code = parent::escStr( $_GET['code'] );
            }

            $errMes = 'ログアウトしました';
            
            $retArray = array(
                    "code"        => $code,
                    "userNo"      => '',
                    "companyID"   => $companyID,
                    "errMes"      => $errMes,
            );

            // セッション変数のクリア
            $_SESSION = array();
            // セッションクリア
            @session_destroy();

            // ログイン画面へ
            $this->initialDisplay( $retArray );
            
            $Log->trace("END   logoutAction");
        }

        /**
         * 初期表示設定
         * @note     [SESSION_TIME]をクリアする / パラメータは、View側で使用
         * @param    $retArray   パラメータ(login_id/password/errMes)
         * @return   なし
         */
        private function initialDisplay( $retArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            unset($_SESSION["SESSION_TIME"]);
            
            require_once './View/SimpleLoginPanel.html';
            $Log->trace("END   initialDisplay");
        }

        /**
         * ユーザID/パスワード認証
         * @param     &$retArray   上位へ返却するパラメータ
         * @return    成功時：true  失敗：false
         */
        private function authentication(&$retArray)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START authentication");
            
            $login = new SimpleLogin();
            
            // POSTデータをエスケープ処理
            $postArray = array(
                "userNo"    => parent::escStr( $_POST['userNo'] ),
                "code"      => parent::escStr( $_POST['code'] ),
                "companyID" => parent::escStr( $_POST['companyID'] ),
            );

            $Log->trace("END   authentication");
            // 認証処理実行
            return $login->authentication( $postArray, $retArray );
        }
        
    }
?>
