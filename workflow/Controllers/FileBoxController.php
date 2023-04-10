<?php
    /**
     * @file      トップメッセージコントローラ
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      トップメッセージの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // トップメッセージモデル
    require './Model/FileBox.php';
    
    /**
     * トップメッセージコントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class FileBoxController extends BaseController
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
         * トップメッセージ一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START filebox showAction");

            $startFlag = true;

            $this->initialDisplay($startFlag);
            $Log->trace("END filebox showAction");
        }
        
        /**
         * トップメッセージ一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START filebox searchAction" );
            
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
            
            $Log->trace("END filebox searchAction");
            
        }

        /**
         * トップメッセージ入力画面遷移
         * @return    なし
         */
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START filebox addInputAction" );

            $this->FileBoxPanelDisplay();

            $Log->trace("END filebox addInputAction");
            
        }

        /**
         * トップメッセージ新規登録処理
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START filebox addAction" );

            $filebox = new FileBox();

            $startFlag = true;

            $postArray = array(
                'title'                     => parent::escStr( $_POST['title'] ),
                'contents'                  => parent::escStr( $_POST['contents'] ),
                'thumbnail'                 => parent::escStr( $_POST['thumbnail'] ),
                'file'                      => parent::escStr( $_POST['file'] ),
                'viewer'                    => "",
                'viewer_count'              => "0",
                'registration_user_id'      => $_SESSION["USER_ID"],
                'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                'update_user_id'            => $_SESSION["USER_ID"],
                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
            );
            
            // サムネイルファイルを登録
            
            // 添付ファイルを登録

            $message = $FileBox->addNewData($postArray);
            if($message === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }
            $Log->trace("END filebox addAction");
        }

        /**
         * トップメッセージ更新処理
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START filebox modAction" );

            $filebox = new FileBox();

            $startFlag = true;

            $postArray = array(
                'top_message_id'            => parent::escStr( $_POST['topMessageID'] ),
                'title'                     => parent::escStr( $_POST['title'] ),
                'contents'                  => parent::escStr( $_POST['contents'] ),
                'thumbnail'                 => parent::escStr( $_POST['thumbnail'] ),
                'file'                      => parent::escStr( $_POST['file'] ),
                'viewer'                    => parent::escStr( $_POST['viewer'] ),
                'viewer_count'              => parent::escStr( $_POST['viewer_count'] ),
                'update_user_id'            => $_SESSION["USER_ID"],
                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                'update_time'               => parent::escStr( $_POST['update_time'] ),
            );

            // サムネイルファイルの構成が変わっていたら変わった分だけ再登録
            
            // 添付ファイルの構成が変わっていたら変わった分だけ再登録

            // 新規登録フラグ
            $addFlag = false;

            $message = $topMessage->updateData($postArray);

            if($message === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END topMessage modAction");
        }

        /**
         * トップメッセージ削除処理
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START topMessage delAction" );

            $topMessage = new TopMessage();

            $startFlag = true;

            $a = $_POST['topMessageID'];
            
            $postArray = array(
                'top_message_id'    => parent::escStr( $_POST['topMessageID'] ),
                'thumbnail'         => parent::escStr( $_POST['thumbnail'] ),
                'file'              => parent::escStr( $_POST['file'] ),
            );

            $message = $topMessage->delData($postArray);

            if($message === "MSG_BASE_0000")
            {
                // 関連のサムネイルファイルを削除
                // 関連の添付ファイルを削除

                // 削除成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END topMessage delAction");
        }

        /**
         * トップメッセージ一覧検索
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        private function initialDisplay($startFlag)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START topMessage initialDisplay");

            // トップメッセージモデルインスタンス化
            $topMessage = new TopMessage();

            // 一覧表用検索項目
            $searchArray = array(
                'title'                 => parent::escStr( $_POST['searchTitle'] ),
                'contents'              => parent::escStr( $_POST['searchContents'] ),
                'thumbnail'             => parent::escStr( $_POST['searchThumbnail'] ),
                'file'                  => parent::escStr( $_POST['searchFile'] ),
                'viewer_count'          => parent::escStr( $_POST['searchViewer_count'] ),
                'sAppStartDate'         => parent::escStr( $_POST['searchSAppStartDate'] ),
                'eAppStartDate'         => parent::escStr( $_POST['searchEAppStartDate'] ),
            );

            // トップメッセージ一覧データ取得
            $topMessageAllList = $topMessage->getListData($searchArray);
            // トップメッセージレコード数
            $topMessageRecordCnt = count($topMessageAllList);
            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            if($startFlag){
                // 初期表示の場合
                $pageNo = $this->getDuringTransitionPageNo($topMessageRecordCnt, $pagedRecordCnt);
            }else{
                // その他の場合
                $pageNo = $_SESSION["PAGE_NO"];
            }

            // シーケンスIDName
            $idName = "top_message_id";
            // 一覧表
            $topMessageList = $this->refineListDisplayNoSpecifiedPage($topMessageAllList, $idName, $pagedRecordCnt, $pageNo);
            $topMessageList = $this->modBtnDelBtnDisabledCheck($topMessageList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($topMessageList) >= 11)
            {
                $isScrollBar = true;
            }

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
            // ページ数
            $pagedCnt = ceil($topMessageRecordCnt /  $pagedRecordCnt);
            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
            // ソート時のマーク変更メソッド
            if($startFlag){
                // ソートマーク初期化
                $headerArray = $this->changeHeaderItemMark(0);
                // NO表示用
                $display_no = 0;
                // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
                $topMessageNoSortFlag = false;
            }else{
                // 対象項目へマークを付与
                $headerArray = $this->changeHeaderItemMark(parent::escStr( $_POST['sortConditions'] ));
                // NO表示用
                $display_no = $this->getDisplayNo( $topMessageRecordCnt, $pagedRecordCnt, $pageNo, parent::escStr( $_POST['sortConditions'] ) );
                // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
                $topMessageNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;
            }

            if($startFlag){
                require_once './View/TopMessagePanel.html';
            }else{
                require_once './View/TopMessageTablePanel.html';
            }

            $Log->trace("END topMessage initialDisplay");
        }

        /**
         * トップメッセージ入力画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function topMessageInputPanelDisplay()
        {
            global $Log, $TokenID; // グローバル変数宣言
            $Log->trace( "START topMessageInputPanelDisplay" );

            $topMessage = new TopMessage();

            $topMessageDataList = array();
            
            // 修正時フラグ（false）
            $CorrectionFlag = false;
            // 登録データ更新時
            if(!empty($_POST['topMessageID']))
            {
                // トップメッセージデータを取得
                $topMessageDataList = $topMessage->getTopMessageData( parent::escStr( $_POST['topMessageID'] ) );
                // セキュリティに合わせて削除可能か設定←いずれ
                $del_disabled = true;
                // 更新フラグ
                $CorrectionFlag = true;
            }

            require_once './View/TopMessageInputPanel.html';
            
            $Log->trace("END topMessageInputPanelDisplay");
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
            $Log->trace("START topMessage changeHeaderItemMark");

            // 初期化
            $headerArray = array(
                    'topMessageNoSortMark'      => '',
                    'titleSortMark'             => '',
                    'contentsSortMark'          => '',
                    'thumbnailSortMark'         => '',
                    'fileMark'                  => '',
                    'viewerCountSortMark'       => '',
                    'registrationTimeSortMark'  => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]  = "topMessageNoSortMark";
                $sortList[2]  = "topMessageNoSortMark";
                $sortList[3]  = "titleSortMark";
                $sortList[4]  = "titleSortMark";
                $sortList[5]  = "contentsSortMark";
                $sortList[6]  = "contentsSortMark";
                $sortList[7]  = "thumbnailSortMark";
                $sortList[8]  = "thumbnailSortMark";
                $sortList[9]  = "fileMark";
                $sortList[10] = "fileMark";
                $sortList[12] = "viewerCountSortMark";
                $sortList[13] = "viewerCountSortMark";
                $sortList[14] = "registrationTimeSortMark";
                $sortList[15] = "registrationTimeSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START topMessage changeHeaderItemMark");

            return $headerArray;
        }

    }
?>