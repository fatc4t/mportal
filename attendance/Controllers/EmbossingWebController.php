<?php
    /**
     * @file      打刻管理コントローラ
     * @author    Millionet oota
     * @date      2016/08/19
     * @version   1.00
     * @note      打刻管理の新規登録/修正/削除を行う
     */

    // BaseClassの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwDirectAccessController.php';
    // 打刻管理モデル
    require_once './Model/EmbossingWeb.php';

    /**
     * 打刻管理コントローラクラス
     * @note   打刻用機器からのアクセスも対応する
     */
    class EmbossingWebController extends FwDirectAccessController
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
         * 初期表示 必要情報の取得
         * @return   なし
         */
        public function testAction(){

            global $Log; // グローバル変数宣言
            $Log->trace("START embossingWeb");
            $Log->info( "打刻画面表示" );

            // 契約IDを判別
            $companyID = isset($_GET['CompanyID']) ? parent::escStr($_GET['CompanyID']) : null;
            
            // モード通常
            $mode = 0;

            // 認証キーを取得
            $key = $_COOKIE["tenpo_tokutei"];
            
            // 打刻組織認証キーのチェック
            if(!isset($key)){
                // なければ認証キーモードへ
                $mode = 1;
                $errMes = "店舗認証コードを入力して下さい";
            }
            
            $mmbossingWeb = new EmbossingWeb( $companyID );

            // 打刻組織認証キーで打刻履歴を取得
            $embossingList = $mmbossingWeb->getEmbossingList( $key );
            
            $embossingType = 2;

            $Log->trace("END   embossingWeb");
            
            require './View/Timerecorder.html';
        }
                
        /**
         * 店舗特定を設定
         * @return   なし
         */
        public function setTenpoTokuteiAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setTenpoTokuteiAction");
            $Log->info( "MSG_INFO_1803" );

            // 契約IDを判別
            $companyID = isset($_GET['CompanyID']) ? parent::escStr($_GET['CompanyID']) : null;

            $mmbossingWeb = new EmbossingWeb( $companyID );

            // 認証キー(打刻ID)
            $password = parent::escStr( $_POST['password'] );

            // 店舗コードから店舗を特定
            $ret = $mmbossingWeb->getTenpoTokutei(  $password );

            // 認証結果に合わせて対応
            if($ret){
                // 認証成功
                $errMes = "店舗認証コードが設定されました";
                // cookieに認証コードを設定
                setcookie("tenpo_tokutei", $password, time()+(10 * 12 * 30 * (24*60*60))); //100年
                // モード通常
                $mode = 0;
                
                // 打刻組織認証キーで打刻履歴を取得
                $embossingList = $mmbossingWeb->getEmbossingList( $password );
                
            }else{
                
                // 認証失敗
                $errMes = "店舗認証コードが不正です";
                // モード変更なし
                $mode = 1;

            }
            
            $Log->trace("END   setTenpoTokuteiAction");

            require './View/Timerecorder.html';
        }

        /**
         * 打刻情報を保存
         * @return   なし
         */
        public function setEmbossingAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START setEmbossingAction");
            $Log->info( "MSG_INFO_1803" );

            // 契約IDを判別
            $companyID = isset($_GET['CompanyID']) ? parent::escStr($_GET['CompanyID']) : null;

            $mmbossingWeb = new EmbossingWeb( $companyID );

            // 打刻種別
            $embossingType = parent::escStr( $_POST['embossingType'] );
            // ローカル時間
            $dateTime = parent::escStr( $_POST['dateTime'] );
            // 違反フラグ (0を指定)
            $isViolation = 0;
            
            // 打刻ID
            $password = parent::escStr( $_POST['password'] );
            
            // 認証キーを取得
            $key = $_COOKIE["tenpo_tokutei"];
            
            // 打刻IDから従業員を特定
            $list = $mmbossingWeb->getUserInfoEmPass(  $password );

            if($password == ""){
                // エラーメッセージを作成
                $messege = "パスワードが未入力です";
            }else if($list['organization_id'] == 0){
                // エラーメッセージを作成
                $messege = "打刻パスワードが間違っています";
            }else{
                // 従業員所属組織ID
                $organID = $list['organization_id'];
                // 従業員コード
                $employeesNo = $list['employees_no'];
                
                $ret = $mmbossingWeb->setEmbossing( $organID, $employeesNo, $key, $embossingType, $dateTime, $isViolation, 1 );
            
                // エラーチェック
                if($ret == "MSG_FW_NO_SET_USER_NO"){
                    $messege = "打刻パスワードが間違っています";
                }else if($ret == "MSG_BASE_0004"){
                        $messege = "退勤の打刻がされていません";
                }else if($ret == "MSG_BASE_0006"){
                        $messege = "出勤又は、休憩終了の打刻がされていません";
                }else if($ret == "MSG_BASE_0005"){
                        $messege = "休憩開始の打刻がされていません";
                }else if($ret == "MSG_BASE_0007"){
                        $messege = "出勤又は、休憩開始の打刻がされていません";
                }else if($ret == "MSG_BASE_0008"){
                        $messege = "他店舗のシフトが登録されていない為、打刻できません";
                }else if($ret == "MSG_BASE_0009"){
                        $messege = "認証キーの設定が間違っている為、打刻できません";
                }else{
                    // メッセージを作成
                    $messege = "";
                    $organizationName =$list['organization_name'];
                    $userCode = $list['employees_no'];
                    $userName = $list['user_name'];

                    switch ($embossingType){
                        case '2':
                          // 出勤
                            $messege = "出勤しました";
                          break;
                        case '3':
                          // 休憩IN
                            $messege = "休憩に入りました";
                          break;
                        case '4':
                          // 休憩OUT
                            $messege = "休憩から戻りました";
                          break;
                        case '5':
                          // 退勤
                            $messege = "退勤しました";
                          break;
                      default:
                          // 処理
                    }
                }
            }
            
            $Log->trace("END   setEmbossingAction");

            // 打刻組織認証キーで打刻履歴を取得
            $embossingList = $mmbossingWeb->getEmbossingList( $key );

            // モード通常
            $mode = 0;

            require './View/Timerecorder.html';
        }
    }
?>
