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
    require './Model/TopMessage.php';
    
    /**
     * トップメッセージコントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class TopMessageController extends BaseController
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
            $Log->trace("START topMessage showAction");

            $startFlag = true;

            $this->initialDisplay($startFlag);
            $Log->trace("END topMessage showAction");
        }
        
        /**
         * トップメッセージ一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START topMessage searchAction" );
            
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
            
            $Log->trace("END topMessage searchAction");
            
        }

        /**
         * トップメッセージ入力画面遷移
         * @return    なし
         */
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START topMessage addInputAction" );

            $this->TopMessageInputPanelDisplay();

            $Log->trace("END topMessage addInputAction");
            
        }

        /**
         * トップメッセージ新規登録処理
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START topMessage addAction" );

            $topMessage = new TopMessage();

            $startFlag = true;

            $postArray = array(
                'title'                     => parent::escStr( $_POST['title'] ),
                'contents'                  => parent::escStr( $_POST['contents'] ),
                'thumbnail'                 => parent::escStr( $_POST['thumbnailId'] ),
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

            $message = $topMessage->addNewData($postArray);
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
            $Log->trace("END topMessage addAction");
        }

        /**
         * トップメッセージ更新処理
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START topMessage modAction" );

            $topMessage = new TopMessage();

            $startFlag = true;

            $postArray = array(
                'top_message_id'            => parent::escStr( $_POST['topMessageID'] ),
                'title'                     => parent::escStr( $_POST['title'] ),
                'contents'                  => parent::escStr( $_POST['contents'] ),
                'thumbnail'                 => parent::escStr( $_POST['thumbnailId'] ),
                'update_user_id'            => $_SESSION["USER_ID"],
                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                'update_time'               => parent::escStr( $_POST['update_time'] ),
            );

            // 新規登録フラグ
            $addFlag = false;

            $message = $topMessage->updateData($postArray);

            if($message === "MSG_BASE_0000")
            {
                // 更新が成功したら古いフォルダを削除
                $del_directory = "./server/php/".$_SESSION['SCHEMA']. "/".parent::escStr( $_POST['oldThumbnailId'] );

                if(!empty(parent::escStr( $_POST['oldThumbnailId'] ))){
                    $this-> dir_delete($del_directory);
                }
                
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
                
                // 一時フォルダを削除
                $del_directory = "./server/php/".$_SESSION['SCHEMA']. "/".parent::escStr( $_POST['oldThumbnailId'] );
                if(!empty(parent::escStr( $_POST['oldThumbnailId'] ))){
                    $this-> dir_delete($del_directory);
                }

                // もともと記事についていたフォルダを削除
                $del_directory = "./server/php/".$_SESSION['SCHEMA']. "/".parent::escStr( $_POST['thumbnailId'] );
                if(!empty(parent::escStr( $_POST['thumbnailId'] ))){
                    $this-> dir_delete($del_directory);
                }
                
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
                'file'                  => parent::escStr( $_POST['searchFile'] ),
                'viewer_count'          => parent::escStr( $_POST['searchViewer_count'] ),
                'sAppStartDate'         => parent::escStr( $_POST['searchSAppStartDate'] ),
                'eAppStartDate'         => parent::escStr( $_POST['searchEAppStartDate'] ),
                'sort'                  => parent::escStr( $_POST['sortConditions'] ),
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
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($topMessageList) >= 15)
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
            
            // 添付ファイル用Cookieの初期化
            setcookie('topMessageSCHEMAName','', time()-3600);
            setcookie('topMessageDirName','', time()-3600);
                
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
                
                $oldThumbnailId = $topMessageDataList['thumbnail'];
                $thumbnailId = uniqid();
                
                // 添付ファイル用にCookie設定→JQueryに渡す
                setcookie('topMessageSCHEMAName',$_SESSION['SCHEMA'], time()+3600);
                //setcookie('topMessageDirName',$topMessageDataList['thumbnail'], time()+3600);
                setcookie('topMessageDirName',$thumbnailId, time()+3600);
                
                // 一時保存フォルダを作成
                mkdir("./server/php/".$_SESSION['SCHEMA']."/".$thumbnailId);
                
                // ファイルを移動させたいディレクトリの指定
                $new_directory = "./server/php/".$_SESSION['SCHEMA']."/".$thumbnailId; 
                $move_directory = "./server/php/".$_SESSION['SCHEMA']. "/".$topMessageDataList['thumbnail'];

                if(!empty($topMessageDataList['thumbnail'])){
                    /** 既存のフォルダを丸ごとコピー **/
                    $this-> dir_copy($move_directory, $new_directory);
                }
                
            }else{
                $oldThumbnailId = "";
                $thumbnailId = uniqid();
                // 添付ファイル用にCookie設定→JQueryに渡す
                setcookie('topMessageSCHEMAName',$_SESSION['SCHEMA'], time()+3600);
                setcookie('topMessageDirName',$thumbnailId, time()+3600);
            }

            require_once './View/TopMessageInputPanel.html';
            
            $Log->trace("END topMessageInputPanelDisplay");
        }

        /**
         * ディレクトリ階層以下のコピー
         * @note     
         * @param    $dir_name コピー元ディレクトリ、$new_dir コピー先ディレクトリ
         * @return   結果
         */
        private function dir_copy($dir_name, $new_dir)
        {
            if (!is_dir($new_dir)) {
                mkdir($new_dir);
            }

            if (is_dir($dir_name)) {
                if ($dh = opendir($dir_name)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file == "." || $file == "..") {
                            continue;
                        }
                        if (is_dir($dir_name . "/" . $file)) {
                            // 再帰的に実行
                            $this-> dir_copy($dir_name . "/" . $file, $new_dir . "/" . $file);
                        } else {
                            copy($dir_name . "/" . $file, $new_dir . "/" . $file);
                        }
                    }
                closedir($dh);
                }
            }
            return true;
        }

        /**
         * ディレクトリ階層以下の削除
         * @note     
         * @param    $dir_name 対象ディレクトリ
         * @return   結果
         */
        private function dir_delete($dir_name)
        {
            if (is_dir($dir_name)) {
                if ($dh = opendir($dir_name)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file == "." || $file == "..") {
                            continue;
                        }
                        if (is_dir($dir_name . "/" . $file)) {
                            $this-> dir_delete($dir_name . "/" . $file);
                        } else {
                            unlink($dir_name . "/" . $file);
                        }
                    }
                closedir($dh);
                }
                rmdir($dir_name);
            }
            return true;
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
                $sortList[7] = "registrationTimeSortMark";
                $sortList[8] = "registrationTimeSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START topMessage changeHeaderItemMark");

            return $headerArray;
        }

    }
?>