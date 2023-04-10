<?php
    /**
     * @file      ログインコントローラ
     * @author    USE Y.Sakata
     * @date      2016/04/27
     * @version   1.00
     * @note      ログイン時のリクエストを振り分ける
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/Login.php';

    /**
     * ログインコントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class LoginController extends BaseController
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
            $Log->info( "MSG_INFO_1000" );
            session_regenerate_id(true);
           
            $errMsg = '';
            $companyID = '';
            $mobile = '';
            if( isset($_GET['errMsg']) )
            {
                $errMsg = $Log->getMsgLog( parent::escStr( $_GET['errMsg'] ) );
            }
            
            if( isset($_GET['CompanyID']) )
            {
                $companyID = parent::escStr( $_GET['CompanyID'] );
            }
            
            if( isset($_GET['mobile']) )
            {
                $mobile = parent::escStr( $_GET['mobile'] );
            }
            
            $retArray = array(
                    "login_id"    => '',
                    "password"    => '',
                    "companyID"   => $companyID,
                    "errMes"      => $errMsg,
                    "mobile"      => $mobile,
            );
            $this->initialDisplay($retArray);
            $Log->trace("END   showAction");
        }
        
        /**
         * ログイン画面からの遷移
         * @return    なし
         */
        public function loginAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START loginAction");
            $Log->info( "MSG_INFO_1001" );
            
            $mobile = '';
            
            if( isset($_POST["mobile"]) )
            {
                $mobile = parent::escStr( $_POST['mobile'] );
            }

            // ログイン押下
            if (isset($_POST["login"]))
            {
                // 認証
                if( $this->authentication( $retArray ) )
                {
                    $Log->debug( "認証成功" );
                    $Log->trace("END   loginAction");
                    // 認証成功 (Home or mobileメニューページ)
                    
                    if($mobile){
                        header('Location:../mobile/menu.php');
                    }else if($_SESSION["COMPANY_ID"] == 'honbu'){
                        header('Location:../home/index.php?param=Home/show2');
                    }else{
                        header('Location:../home/index.php?param=Home/show');
                    }
                }
                else
                {
                    // モバイルかどうかを設定
                    $retArray["mobile"] = $mobile;
                    
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
            $Log->info( "MSG_INFO_1002" );
            
            $errMes = 'ログアウトしました';
            if( isset( $_SESSION["SECURITY_LOGOUT"] ) )
            {
                if( '' !== $_SESSION["SECURITY_LOGOUT"] )
                {
                    $errMes = $Log->getMsgLog( $_SESSION["SECURITY_LOGOUT"] );
                }
            }
            
            $retArray = array(
                    "login_id"    => '',
                    "password"    => '',
                    "companyID"   => $_SESSION["COMPANY_ID"],
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
            
            if($retArray['mobile']){
                require_once './View/LoginPanelMobile.html';
            }else{
                require_once './View/LoginPanel.html';
            }
            $Log->trace("END   initialDisplay");
        }

        /**
         * ユーザID/パスワード認証
         * @param     &$retArray   上位へ返却するパラメータ(login_id/password/companyID/errMes)
         * @return    成功時：true  失敗：false
         */
        private function authentication(&$retArray)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START authentication");
            
            $login = new Login();
            
            // POSTデータをエスケープ処理
            $postArray = array(
                "login_id"  => parent::escStr( $_POST['login_id'] ),
                "password"  => parent::escStr( $_POST['password'] ),
                "companyID" => parent::escStr( $_POST['companyID'] ),
            );

            $Log->trace("END   authentication");
            // 認証処理実行
            return $login->authentication( $postArray, $retArray );
        }
        
    }
?>
