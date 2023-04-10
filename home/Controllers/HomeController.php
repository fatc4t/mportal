<?php
    /**
     * @file    トップコントローラ
     * @author  スクリプト作者
     * @date    日付
     * @version バージョン
     * @note    注釈を記述
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';

    // Login処理モデル
    require './Model/Home.php';

    /**
     * トップコントローラ
     * @note   注釈を記述
     */
    class HomeController extends BaseController
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
         * 初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->info( "MSG_INFO_1000" );
            
            $this->initialDisplay();
        }
        /**
         * 初期表示
         * @return    なし
         */
        public function show2Action()
        {
            global $Log; // グローバル変数宣言
            $Log->info( "MSG_INFO_1000" );

            $this->initialDisplay2();
        }

        /**
         * HOGE検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            
            $this->initialDisplay();
            $Log->trace("END   searchAction");
        }

        /**
         * HOGE登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            
            $this->initialDisplay();
            $Log->trace("END   addAction");
        }

        /**
         * HOGE修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            
            $this->initialDisplay();
            $Log->trace("END   modAction");
        }

        /**
         * HOGE削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            
            $this->initialDisplay();
            $Log->trace("END   delAction");
        }

        /**
         * 初期表示設定
         * @return    なし
         */
        private function initialDisplay()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START initialDisplay" );
            
            $home = new Home();
            
            // セキュリティ情報を取得
            $security = $_SESSION["ACCESS_MENU_LIST"];
           
            // トップメッセージ最新を取得
            $topMessageList = $home->getTopMessage();

            // 売上履歴取得
            $searchArray = array(
                'organization_id'  => $_SESSION["ORGANIZATION_ID"],
                'mcode'            => '112001', // [税込]総売上
            );

            $profitHistoryList = $home->getProfitHistoryList($searchArray);
            
            // 売上履歴取得2
            $profitHistoryList2 = $home->getProfitHistoryList2($searchArray);

            // 全店の売上サマリ
            $searchArrayS = array(
                
                'mcode1'            => "111002",    // [税抜]現金売上
                'mcode2'            => "111003",    // [税抜]カード売上
                'mcode3'            => "110002",    // 組数
                'mcode4'            => "110001",    // 客数
                'mcode5'            => "111007",    // [税抜]クーポン
                'mcode6'            => "110008",    // 現金過不足  
            );

            $profitSummaryList = $home->getProfitSummaryList($searchArrayS);

            // 全店の売上サマリ2
            $profitSummaryList2 = $home->getProfitSummaryList2($searchArray);
            
            // 全店の売上推移
            $profitList = $home->getProfitList($searchArray);
            
            // 全店の売上推移2
            $profitList2 = $home->getProfitList2($searchArray);
            
            // 本日の出勤中
            $embossingNowList = $home->getEmbossingNowList($_SESSION["ORGANIZATION_ID"]);
            
            // 商品粗利のTOP10
            $productList = $home->getProductList($_SESSION["ORGANIZATION_ID"]);

            // 発注状況
            $orderList = $home->getOrderList($_SESSION["ORGANIZATION_ID"]);

            // 仕入状況
            $purchaseList = $home->getPurchaseList($_SESSION["ORGANIZATION_ID"]);

            // 打刻忘れの可能性アリ
            $embossingMissList = $home->getEmbossingMissList($_SESSION["ORGANIZATION_ID"]);
            
            // 新規顧客リスト
            $customerList = $home->getCustomerList($_SESSION["ORGANIZATION_ID"]);
            
            // 打刻履歴
            $embossingList = $home->getEmbossingList($_SESSION["ORGANIZATION_ID"]);
            
            // ワークフロー承認待ち件数
            $approvalList = $home->getApprovalList($_SESSION["USER_ID"]);
            
            // ワークフロー参照待ち件数
            $referenceList = $home->getReferenceList($_SESSION["USER_ID"]);
            
            // ワークフロー申請中件数
            $applyingList = $home->getApplyingList($_SESSION["USER_ID"]);
            
            // 日報セキュリティ
            $securityLebel = $security['/dailyReport/index.php?param=DailyReport/show'];
            
            $organization_id = 0;
            $user_id = 0;
            // 簡易アクセス権制御
            if ( $securityLebel == 1 or $securityLebel == 2 ){
                // 管理者、全店以上なら選択した組織or全店(組織ID指定なし)
            }else if($securityLebel == 3){
                // 組織配下なら選択した組織はOK
                $organization_id = $_SESSION["ORGANIZATION_ID"];
            }else if($securityLebel == 4){
                // 自組織
                $organization_id = $_SESSION["ORGANIZATION_ID"];
            }else if($securityLebel == 5){
                // 自分
                $organization_id = $_SESSION["ORGANIZATION_ID"];
                $user_id = $_SESSION["USER_ID"];
            }else{
                $organization_id = 999;
            }
            
            $searchArray = array(
                'organization_id'  => $organization_id,
                '$user_id'         => $user_id, 
            );

            // 日報一覧取得
            $dailyReportList = $home->getDailyReportList($searchArray);

            // 通達連絡
            $securityLebel = $security['/noticeContact/index.php?param=TOP/show'];

            if ( $securityLebel <= 5 ){
                $noticeContactList = $home->getNoticeContactList($_SESSION["USER_ID"]);
            }else{
                $noticeContactList = array();
            }
            
            // 未登録商品情報取得
            $unregprodlist = $home->unregprodlist($searchArray);

            // テロップ情報取得
            $telopContactList = $home->getTelopList($_SESSION["USER_ID"]);
            
            if( $_SESSION['SCHEMA'] == 'use'){
                require_once '../home/View/HomePanelFujiya.html';
            }else if( $_SESSION['SCHEMA'] == 'akinai'){
                require_once '../home/View/HomePanelAkinai.html';
            }else if( $_SESSION['SCHEMA'] == 'millionet'){
                require_once '../home/View/HomePanelFujiya.html';
            }else if( $_SESSION['SCHEMA'] == 'honbu'){
                require_once '../home/View/HomePanelFujiya.html';
            }else if( $_SESSION['SCHEMA'] == 'juso' or $_SESSION["SCHEMA"] == "honbu_test" or $_SESSION["SCHEMA"] == "jinseido" or $_SESSION["SCHEMA"] == "kubo" or $_SESSION["SCHEMA"] == "sugimotoen" or $_SESSION["SCHEMA"] == "okinawa_pharmacy" or $_SESSION["SCHEMA"] == "taihei" or $_SESSION["SCHEMA"] == "takiya" or $_SESSION["SCHEMA"] == "withs" or $_SESSION["SCHEMA"] == "office_sol" or $_SESSION["SCHEMA"] == "ikkou" or $_SESSION["SCHEMA"] == "saikou" or $_SESSION["SCHEMA"] == "denshiti" or $_SESSION["SCHEMA"] == "kameya" or $_SESSION["SCHEMA"] == "fuji" or $_SESSION["SCHEMA"] == "shiguma" or $_SESSION["SCHEMA"] == "okazawa" or $_SESSION["SCHEMA"] == "nishimotohiro" or $_SESSION["SCHEMA"] == "jrkusuri" or $_SESSION["SCHEMA"] == "shiosaka"){
                require_once '../home/View/HomePanelHonbu.html';
            }else{
                require_once '../home/View/HomePanelOld.html';
            }

            $Log->trace("END   initialDisplay");
        }
        /**
         * 初期表示設定
         * @return    なし
         */
        private function initialDisplay2()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START initialDisplay2" );

            $pha_id = $_SESSION['PHA_ID'];
            $home = new Home();
            $makerArr = $home->getMakerList($pha_id);
            $proArr   = $home->getProductsList($pha_id);

            $imgFld  = "/img/order/maker/";
            $imgFld2 = "/var/www/mportal/img/order/maker/";

            require_once './View/HomePanel2.html';

            $Log->trace("END   initialDisplay2");
        }

    }

?>
