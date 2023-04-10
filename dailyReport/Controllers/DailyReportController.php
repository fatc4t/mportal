<?php
    /**
     * @file      日報コントローラ
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      日報の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 日報ジモデル
    require './Model/DailyReport.php';
    
    /**
     * 日報コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class DailyReportController extends BaseController
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
         * 日報一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START dailyReport showAction");

            $startFlag = true;

            $this->initialDisplay($startFlag);
            $Log->trace("END dailyReport showAction");
        }
        
        /**
         * 日報一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START dailyReport searchAction" );
            
            $startFlag = false;
            
            if(isset($_POST['displayPageNo']))
            {
                $_SESSION['PAGE_NO'] = $_POST['displayPageNo'];
            }
            if(isset($_POST['displayRecordCnt']))
            {
                $_SESSION['DISPLAY_RECORD_CNT'] = $_POST['displayRecordCnt'];
            }
            
            $this->initialDisplay($startFlag);
            
            $Log->trace("END dailyReport searchAction");
            
        }

        /**
         * 日報入力画面遷移
         * @return    なし
         */
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START dailyReport addInputAction" );

            $this->DailyReportPanelDisplay();

            $Log->trace("END dailyReport addInputAction");
            
        }

        /**
         * 日報新規登録処理
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START dailyReport addAction" );

            $dailyReport = new DailyReport();

            $message = "MSG_BASE_0000";
            $startFlag = true;
            $dataStr = "";

            $searchArray = array(
                'form_id'            => parent::escStr( $_POST['form_id'] ),
            );
            
            // 日報フォーム一覧データ取得
            $formDetailList = $dailyReport->getFormDetailList($searchArray);
            
            // 登録内容を検索用に1つにまとめる
            foreach($formDetailList as $data){
                $dataStr = $dataStr.parent::escStr( $_POST[$data["form_details_id"]] );
            }

            // 日報ヘッダ情報登録
            $hedaArray = array(
                'target_date'               => parent::escStr( $_POST['target_date'] ),
                'user_id'                   => $_SESSION["USER_ID"],
                'organization_id'           => $_SESSION["ORGANIZATION_ID"],
                'form_id'                   => parent::escStr( $_POST['form_id'] ),
                'data'                      => $dataStr,
                'registration_user_id'      => $_SESSION["USER_ID"],
                'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                'update_user_id'            => $_SESSION["USER_ID"],
                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
            );

            $dailyReportDataList = $dailyReport->addNewData($hedaArray);
            
            // 詳細ごとに登録
            foreach($formDetailList as $dataDetails){
                
                $inputArray = array(
                    'daily_report_id'           => $dailyReportDataList["daily_report_id"],
                    'target_date'               => parent::escStr( $_POST['target_date'] ),
                    'user_id'                   => $_SESSION["USER_ID"],
                    'organization_id'           => $_SESSION["ORGANIZATION_ID"],
                    'form_id'                   => parent::escStr( $_POST['form_id'] ),
                    'form_details_id'           => $dataDetails["form_details_id"],
                    'form_type'                 => $dataDetails["form_type"],
                    'data'                      => parent::escStr( $_POST[$dataDetails["form_details_id"]] ),
                    'disp_sort'                 => $dataDetails["disp_sort"],
                    'registration_user_id'      => $_SESSION["USER_ID"],
                    'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                    'update_user_id'            => $_SESSION["USER_ID"],
                    'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                );

                $message = $dailyReport->addNewDetailsData($inputArray);
            }
            
            if($message == "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }
        }

        /**
         * 日報更新処理
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START dailyReport modAction" );

            $dailyReport = new DailyReport();

            $message = "";
            $startFlag = true;

            $searchArray = array(
                'form_id'            => parent::escStr( $_POST['form_id'] ),
            );

            // 日報フォーム一覧データ取得
            $formDetailList = $dailyReport->getFormDetailList($searchArray);

            // 登録内容を検索用に1つにまとめる
            foreach($formDetailList as $data){
                $dataStr = $dataStr.parent::escStr( $_POST[$data["form_details_id"]] );
            }
            
            // 日報ヘッダ情報を更新
            $hedaArray = array(
                'daily_report_id'           => parent::escStr( $_POST['daily_report_id'] ),
                'data'                      => $dataStr,
                'update_user_id'            => $_SESSION["USER_ID"],
                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
            );

            $message = $dailyReport->updateData($hedaArray);

            // 詳細ごとに更新
            foreach($formDetailList as $dataDetails){
                
                $postArray = array(
                    'form_details_id'           => $dataDetails["form_details_id"],
                    'daily_report_id'           => parent::escStr( $_POST['daily_report_id'] ),
                    'data'                      => parent::escStr( $_POST[$dataDetails["form_details_id"]] ),
                    'update_user_id'            => $_SESSION["USER_ID"],
                    'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                );

                $message = $dailyReport->updateDetailsData($postArray);
            }
            
            if($message == "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

        }

        /**
         * 日報削除処理
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START dailyReport delAction" );

            $dailyReport = new DailyReport();

            $message = "";
            $startFlag = true;

            $postArray = array(
                'daily_report_id'   => parent::escStr( $_POST['daily_report_id'] ), // 日報ID
            );

            // 日報ヘッダ情報を削除
            $message = $dailyReport->delData($postArray);

            // 日報詳細情報を削除
            $message = $dailyReport->delDetailsData($postArray);

            if($message === "MSG_BASE_0000")
            {
                // 削除成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END dailyReport delAction");
        }

        /**
         * 日報一覧検索
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        private function initialDisplay($startFlag)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START dailyReport initialDisplay");

            // 日報モデルインスタンス化
            $dailyReport = new DailyReport();

            // 検索用組織プルダウン
            $abbreviatedNameList = $dailyReport->setPulldown->getSearchOrganizationList( 'reference', true );      // 組織略称名リスト

            $security = $_SESSION["ACCESS_MENU_LIST"];
            $securityLebel = $security['/dailyReport/index.php?param=DailyReport/show'];
            
            // 簡易アクセス権制御
            if ( $securityLebel == 1 or $securityLebel == 2 ){
                // 管理者、全店以上なら選択した組織or全店(組織ID指定なし)
                $organization_id = parent::escStr( $_POST['searchOrgID'] );
            }else if($securityLebel == 3){
                // 組織配下なら選択した組織はOK
                $organization_id = parent::escStr( $_POST['searchOrgID'] );
                if($organization_id == ""){
                    //組織未選択の場合自組織に変換
                    $organization_id = $_SESSION["ORGANIZATION_ID"];
                }
            }else if($securityLebel == 4 or $securityLebel == 5){
                // 自組織、自分なら選択している自組織はOK
                $organization_id = parent::escStr( $_POST['searchOrgID'] );
                if($organization_id == ""){
                    //組織未選択の場合は自組織に変換
                    $organization_id = $_SESSION["ORGANIZATION_ID"];
                }
            }else{
                $organization_id = 999;
            }
            
            // 一覧表用検索項目
            $searchArray = array(
                'sAppStartDate'         => parent::escStr( $_POST['searchSAppStartDate'] ),
                'eAppStartDate'         => parent::escStr( $_POST['searchEAppStartDate'] ),
                'organization_id'       => $organization_id,
                'user_id'               => parent::escStr( $_POST['searchUser'] ),
                'keyword'               => parent::escStr( $_POST['searchKeyword'] ),
                'reply'                 => parent::escStr( $_POST['searchReply'] ),
                'approval'              => parent::escStr( $_POST['searchApproval'] ),
                'sort'                  => parent::escStr( $_POST['sortConditions'] ),
            );

            // 日報一覧データ取得
            $dailyReportAllList = $dailyReport->getListData($searchArray);
            // 日報レコード数
            $dailyReportRecordCnt = count($dailyReportAllList);
            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            if($startFlag){
                // 初期表示の場合
                $pageNo = $this->getDuringTransitionPageNo($dailyReportRecordCnt, $pagedRecordCnt);
            }else{
                // その他の場合
                $pageNo = $_SESSION["PAGE_NO"];
            }

            // シーケンスIDName
            $idName = "daily_report_id";
            // 一覧表
            $dailyReportList = $this->refineListDisplayNoSpecifiedPage($dailyReportAllList, $idName, $pagedRecordCnt, $pageNo);
            $dailyReportList = $this->modBtnDelBtnDisabledCheck($dailyReportList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($dailyReportList) >= 15)
            {
                $isScrollBar = true;
            }

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
            // ページ数
            $pagedCnt = ceil($dailyReportRecordCnt /  $pagedRecordCnt);
            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
            // ソート時のマーク変更メソッド
            if($startFlag){
                // ソートマーク初期化
                $headerArray = $this->changeHeaderItemMark(0);
                // NO表示用
                $display_no = 0;
                // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
                $dailyReportNoSortFlag = false;
            }else{
                // 対象項目へマークを付与
                $headerArray = $this->changeHeaderItemMark(parent::escStr( $_POST['sortConditions'] ));
                // NO表示用
                $display_no = $this->getDisplayNo( $dailyReportRecordCnt, $pagedRecordCnt, $pageNo, parent::escStr( $_POST['sortConditions'] ) );
                // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
                $dailyReportNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;
            }

            if($startFlag){
                require_once './View/DailyReportPanel.html';
            }else{
                require_once './View/DailyReportTablePanel.html';
            }

            $Log->trace("END dailyReport initialDisplay");
        }

        /**
         * 日報入力画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function DailyReportPanelDisplay()
        {
            global $Log, $TokenID; // グローバル変数宣言
            $Log->trace( "START dailyReportInputPanelDisplay" );

            $dailyReport = new DailyReport();

            $dailyReportDataList = array();
            
            $readMode = 0;

            // 遷移元情報
            $mode = $_GET['mode'];
            
            if($mode == ""){
                $mode = $_POST['mode'];
            }

            $searchArray = array(
                'form_id'  => '1',
            );

            // 日報フォーム一覧データ取得
            $dailyReportDataList = $dailyReport->getFormListData($searchArray);

            // ヘッダ情報取り出し 
            foreach($dailyReportDataList as $data){
                $formId = $data["form_id"];
                $application_date = $data["application_date"];
            }
            
            $userID = parent::escStr( $_POST['user_id'] );
            if($userID == "") {
                $userID = $_GET['user_id'];
            }

            $dailyReportId = parent::escStr( $_POST['daily_report_id'] );
            if($dailyReportId == "") {
                $dailyReportId = $_GET['daily_report_id'];
            }
            
            // 画面フォームに日付を返す
            if(!isset($targetDate) OR $targetDate == ""){
                //現在日付を設定
                $targetDate =date("Y/m/d");
            }
            
            // 登録済みデータがあるか取得
            $searchMArray = array(
                'daily_report_id'   => $dailyReportId, // 日報ID
                'form_id'           => '1',                                         // 日報フォームID
            );

            // 詳細情報と登録済みデータを取得
            $dailyReportDetailList = $dailyReport->getFormDetailListData($searchMArray);

            // 修正時フラグ（false）
            $CorrectionFlag = false;
            
            // 詳細情報から画面制御を設定
            foreach($dailyReportDetailList as &$dData){
                // 新規か編集かフラグ生成 
                if($dData["data"] != ""){
                    // セキュリティに合わせて削除可能か設定←いずれ
                    $del_disabled = true;
                    // 更新フラグ
                    $CorrectionFlag = true;
                    
                    $targetDate = $dData["target_date"];
                }
                
                if(isset($dData["user_id"]) OR $dData["user_id"] != ""){
                    // 閲覧モード判別
                    if($dData["user_id"] != $_SESSION["USER_ID"]){
                        $readMode = 1;
                    }
                }
                
            }
            
            // コメント情報取得
            $dailyReportComentList = $dailyReport->getDailyReportComentListData($searchMArray);

            require_once './View/DailyReportInputPanel.html';
            
            $Log->trace("END dailyReportInputPanelDisplay");
        }

        /**
         * ヘッダー部分ソート時のマーク変更
         * @note     ソート番号により、ソートマークを設定する
         * @param    $sortNo ソート条件番号
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        private function changeHeaderItemMark( $sortNo )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START dailyReport changeHeaderItemMark");

            // 初期化
            $headerArray = array(
                    'dailyReportNoSortMark' => '',
                    'targetDateSortMark'    => '',
                    'orgSortMark'           => '',
                    'userSortMark'          => '',
                    'dataMark'              => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]  = "dailyReportNoSortMark";
                $sortList[2]  = "dailyReportNoSortMark";
                $sortList[3]  = "targetDateSortMark";
                $sortList[4]  = "targetDateSortMark";
                $sortList[5]  = "orgSortMark";
                $sortList[6]  = "orgSortMark";
                $sortList[7]  = "userSortMark";
                $sortList[8]  = "userSortMark";
                $sortList[9]  = "dataMark";
                $sortList[10] = "dataMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START dailyReport changeHeaderItemMark");

            return $headerArray;
        }

        /**
         * 従業員リスト更新処理
         * @return    なし
         */
        public function searchUserListAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START userListUpAction");
            $Log->info( "MSG_INFO_1426" );

            $dailyReport = new DailyReport();
            
            // 検索用ユーザリスト
            $searchUserNameList = $dailyReport->setPulldown->getSearchUserList( parent::escStr( $_POST['searchOrgID']) );      // ユーザリスト

            // 空のリストを1つ目に
            // 1行分作成
            $selectUserList = array(
                    'user_id'      => "",
                    'user_name'    => "",
            );
            //１行分搭載
            array_unshift($searchUserNameList, $selectUserList);
            
            require_once '../FwCommon/View/SearchUserName.php';

            $Log->trace("END   userListUpAction");
        }
        
        /**
         * 日報コメント登録処理
         * @return    なし
         */
        public function addCommentAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START dailyReport addCommentAction" );

            $dailyReport = new DailyReport();

            $message = "";

            $inputArray = array(
                'daily_report_id'           => parent::escStr( $_POST['daily_report_id'] ),
                'target_date'               => parent::escStr( $_POST['target_date'] ),
                'user_id'                   => $_SESSION["USER_ID"],
                'form_id'                   => parent::escStr( $_POST['form_id'] ),
                'contents'                  => parent::escStr( $_POST['comment'] ),
                'registration_user_id'      => $_SESSION["USER_ID"],
                'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                'update_user_id'            => $_SESSION["USER_ID"],
                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
            );
                
            $message = $dailyReport->addNewCommentData($inputArray);
            
            if($message == "MSG_BASE_0000")
            {
                // 登録成功→入力画面へ遷移
                $this->DailyReportPanelDisplay();
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }
            $Log->trace("END dailyReport addCommentAction");
        }

        /**
         * 日報コメント削除処理
         * @return    なし
         */
        public function delCommentAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START dailyReport delCommentAction" );

            $dailyReport = new DailyReport();

            $message = "";
            $startFlag = true;

            $postArray = array(
                'daily_report_comment_id'   => parent::escStr( $_POST['daily_report_comment_id'] ), // 日報ID
            );

            // 日報ヘッダ情報を削除
            $message = $dailyReport->delCommentData($postArray);

            if($message === "MSG_BASE_0000")
            {
                // 削除成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END dailyReport delCommentAction");
        }
        
    }
?>