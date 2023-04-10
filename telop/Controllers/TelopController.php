<?php
    /**
     * @file      テロップマスタコントローラ
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      テロップの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // テロップモデル
    require './Model/Telop.php';
    
    /**
     * テロップコントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class TelopController extends BaseController
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
         * テロップ一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START telop showAction");

            $startFlag = true;

            $this->initialDisplay($startFlag);
            $Log->trace("END telop showAction");
        }
        
        /**
         * テロップ一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START telop searchAction" );
            
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
            
            $Log->trace("END telop searchAction");
            
        }

        /**
         * テロップ入力画面遷移
         * @return    なし
         */
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START telop addInputAction" );

            $this->TelopInputPanelDisplay();

            $Log->trace("END telop addInputAction");
            
        }

        /**
         * テロップ新規登録処理
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START telop addAction" );

            $telop = new Telop();

            $startFlag = true;

            $postArray = array(
                'contents'                  => parent::escStr( $_POST['contents'] ),
                'link_url'                  => parent::escStr( $_POST['link_url'] ),
                'link_underline'            => parent::escStr( $_POST['link_underline'] ),
                'color'                     => parent::escStr( $_POST['picker'] ),
                'size'                      => parent::escStr( $_POST['size'] ),
                'scroll_check'              => parent::escStr( $_POST['scroll_check'] ),
                'scroll_direction'          => parent::escStr( $_POST['scroll_direction'] ),
                'scroll_behavior'           => parent::escStr( $_POST['scroll_behavior'] ),
                'scroll_loop'               => parent::escStr( $_POST['scroll_loop'] ),
                'scroll_amount'             => parent::escStr( $_POST['scroll_amount'] ),
                'flashing'                  => parent::escStr( $_POST['flashing'] ),
                'bold'                      => parent::escStr( $_POST['bold'] ),
                'font'                      => parent::escStr( $_POST['font'] ),
                'centering'                 => parent::escStr( $_POST['centering'] ),
                'background_color'          => parent::escStr( $_POST['picker2'] ),
                'thumbnail'                 => parent::escStr( $_POST['thumbnailId'] ),
                'application_start_date'    => parent::escStr( $_POST['applicationStartDate'] ),
                'application_end_date'      => parent::escStr( $_POST['applicationEndDate'] ),
                'registration_user_id'      => $_SESSION["USER_ID"],
                'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                'update_user_id'            => $_SESSION["USER_ID"],
                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
            );
            
            $message = $telop->addNewData($postArray);
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
            $Log->trace("END telop addAction");
        }

        /**
         * テロップ更新処理
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START telop modAction" );

            $telop = new Telop();

            $startFlag = true;

            $postArray = array(
                'telop_id'                  => parent::escStr( $_POST['telopID'] ),
                'contents'                  => parent::escStr( $_POST['contents'] ),
                'link_url'                  => parent::escStr( $_POST['link_url'] ),
                'link_underline'            => parent::escStr( $_POST['link_underline'] ),
                'color'                     => parent::escStr( $_POST['picker'] ),
                'size'                      => parent::escStr( $_POST['size'] ),
                'scroll_check'              => parent::escStr( $_POST['scroll_check'] ),
                'scroll_direction'          => parent::escStr( $_POST['scroll_direction'] ),
                'scroll_behavior'           => parent::escStr( $_POST['scroll_behavior'] ),
                'scroll_loop'               => parent::escStr( $_POST['scroll_loop'] ),
                'scroll_amount'             => parent::escStr( $_POST['scroll_amount'] ),
                'flashing'                  => parent::escStr( $_POST['flashing'] ),
                'bold'                      => parent::escStr( $_POST['bold'] ),
                'font'                      => parent::escStr( $_POST['font'] ),
                'centering'                 => parent::escStr( $_POST['centering'] ),
                'background_color'          => parent::escStr( $_POST['picker2'] ),
                'application_start_date'    => parent::escStr( $_POST['applicationStartDate'] ),
                'application_end_date'      => parent::escStr( $_POST['applicationEndDate'] ),
                'thumbnail'                 => parent::escStr( $_POST['thumbnailId'] ),
                'registration_user_id'      => $_SESSION["USER_ID"],
                'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                'update_user_id'            => $_SESSION["USER_ID"],
                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                'update_time'               => parent::escStr( $_POST['update_time'] ),
            );

            // 新規登録フラグ
            $addFlag = false;

            $message = $telop->updateData($postArray);

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

            $Log->trace("END telop modAction");
        }

        /**
         * テロップ削除処理
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START telop delAction" );

            $telop = new Telop();

            $startFlag = true;

            $a = $_POST['telopID'];
            
            $postArray = array(
                'telop_id'    => parent::escStr( $_POST['telopID'] ),
            );

            $message = $telop->delData($postArray);

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

            $Log->trace("END telop delAction");
        }

        /**
         * テロップ一覧検索
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        private function initialDisplay($startFlag)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START telop initialDisplay");

            // テロップモデルインスタンス化
            $telop = new Telop();

            // 一覧表用検索項目
            $searchArray = array(
                'searchContents'        => parent::escStr( $_POST['searchContents'] ),
                'sAppStartDate'         => parent::escStr( $_POST['searchSAppStartDate'] ),
                'eAppStartDate'         => parent::escStr( $_POST['searchEAppStartDate'] ),
            );

            // テロップ一覧データ取得
            $telopAllList = $telop->getListData($searchArray);
            // テロップレコード数
            $telopRecordCnt = count($telopAllList);
            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            if($startFlag){
                // 初期表示の場合
                $pageNo = $this->getDuringTransitionPageNo($telopRecordCnt, $pagedRecordCnt);
            }else{
                // その他の場合
                $pageNo = $_SESSION["PAGE_NO"];
            }

            // シーケンスIDName
            $idName = "telop_id";
            // 一覧表
            $telopList = $this->refineListDisplayNoSpecifiedPage($telopAllList, $idName, $pagedRecordCnt, $pageNo);
            $telopList = $this->modBtnDelBtnDisabledCheck($telopList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 16 && count($telopList) >= 16)
            {
                $isScrollBar = true;
            }

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
            // ページ数
            $pagedCnt = ceil($telopRecordCnt /  $pagedRecordCnt);
            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
            // ソート時のマーク変更メソッド
            if($startFlag){
                // ソートマーク初期化
                $headerArray = $this->changeHeaderItemMark(0);
                // NO表示用
                $display_no = 0;
                // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
                $telopNoSortFlag = false;
            }else{
                // 対象項目へマークを付与
                $headerArray = $this->changeHeaderItemMark(parent::escStr( $_POST['sortConditions'] ));
                // NO表示用
                $display_no = $this->getDisplayNo( $telopRecordCnt, $pagedRecordCnt, $pageNo, parent::escStr( $_POST['sortConditions'] ) );
                // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
                $telopNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;
            }

            if($startFlag){
                require_once './View/TelopPanel.html';
            }else{
                require_once './View/TelopTablePanel.html';
            }

            $Log->trace("END telop initialDisplay");
        }

        /**
         * テロップ入力画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function telopInputPanelDisplay()
        {
            global $Log, $TokenID; // グローバル変数宣言
            $Log->trace( "START vInputPanelDisplay" );

            $telop = new Telop();

            $telopDataList = array();
            
            // 添付ファイル用Cookieの初期化
            setcookie('telopSCHEMAName','', time()-3600);
            setcookie('telopDirName','', time()-3600);
                
            // 修正時フラグ（false）
            $CorrectionFlag = false;
            // 登録データ更新時
            if(!empty($_POST['telopID']))
            {
                // テロップデータを取得
                $telopDataList = $telop->getTelopData( parent::escStr( $_POST['telopID'] ) );
                // セキュリティに合わせて削除可能か設定←いずれ
                $del_disabled = true;
                // 更新フラグ
                $CorrectionFlag = true;
                
                $oldThumbnailId = $telopDataList['thumbnail'];
                $thumbnailId = uniqid();
                // 添付ファイル用にCookie設定→JQueryに渡す
                setcookie('telopSCHEMAName',$_SESSION['SCHEMA'], time()+3600);
                //setcookie('noticeContactDirName',$noticeContactDataList['thumbnail'], time()+3600);
                setcookie('telopDirName',$thumbnailId, time()+3600);

                // 一時保存フォルダを作成
                mkdir("./server/php/".$_SESSION['SCHEMA']."/".$thumbnailId);
                
                //ファイルを移動させたいディレクトリの指定
                $new_directory = "./server/php/".$_SESSION['SCHEMA']."/".$thumbnailId; 
                $move_directory = "./server/php/".$_SESSION['SCHEMA']. "/".$telopDataList['thumbnail'];
                
                if(!empty($telopDataList['thumbnail'])){
                    /** 既存のフォルダを丸ごとコピー **/
                    $this-> dir_copy($move_directory, $new_directory);
                }
                
            }else{
                $oldThumbnailId = "";
                $thumbnailId = uniqid();
                // 添付ファイル用にCookie設定→JQueryに渡す
                setcookie('telopSCHEMAName',$_SESSION['SCHEMA'], time()+3600);
                setcookie('telopDirName',$thumbnailId, time()+3600);
            }

            require_once './View/TelopInputPanel.html';
            
            $Log->trace("END telopInputPanelDisplay");
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
            $Log->trace("START telop changeHeaderItemMark");

            // 初期化
            $headerArray = array(
                    'telopNoSortMark'              => '',
                    'contentsSortMark'             => '',
                    'linkUrlSortMark'              => '',
                    'applicatioStartDateSortMark'  => '',
                    'applicatioEndDateSortMark'    => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]  = "telopNoSortMark";
                $sortList[2]  = "telopNoSortMark";
                $sortList[3]  = "contentsSortMark";
                $sortList[4]  = "contentsSortMark";
                $sortList[5]  = "linkUrlSortMark";
                $sortList[6]  = "linkUrlSortMark";
                $sortList[7]  = "applicatioStartDateSortMark";
                $sortList[8]  = "applicatioStartDateSortMark";
                $sortList[9]  = "applicatioEndDateSortMark";
                $sortList[10] = "applicatioEndDateSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START telop changeHeaderItemMark");

            return $headerArray;
        }

    }
?>