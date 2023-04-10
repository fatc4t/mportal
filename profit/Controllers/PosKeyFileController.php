<?php
    /**
     * @file      POSキーファイルマスタコントローラ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      POSキーファイルの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/PosKeyFile.php';

    /**
     * POSキーファイルマスタコントローラクラス
     * @note   POSキーファイルマスタの新規登録/修正/削除を行う
     */
    class PosKeyFileController extends BaseController
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
         * POSキーファイル一覧初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1040" );
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }

        /**
         * POSキーファイル一覧検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            $Log->info( "MSG_INFO_1041" );

            if(isset($_POST['displayPageNo']))
            {
                $_SESSION['PAGE_NO'] = parent::escStr( $_POST['displayPageNo'] );
            }

            if(isset($_POST['displayRecordCnt']))
            {
                $_SESSION['DISPLAY_RECORD_CNT'] = parent::escStr( $_POST['displayRecordCnt'] );
            }

            $this->initialList(false);
            
            $Log->trace("END   searchAction");
        }

        /**
         * POSキーファイル一覧登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1042" );

            $posKeyFile = new posKeyFile();

            $postArray = array(
                'posBrandId'         => parent::escStr( $_POST['posBrandName'] ),
                'posKeyFileName'     => parent::escStr( $_POST['posKeyFileName'] ),
                'comment'            => parent::escStr( $_POST['comment'] ),
                'posKeyType'         => parent::escStr( $_POST['posKeyType'] ),
                'cooperationCode'    => parent::escStr( $_POST['cooperationCode'] ),
                'is_del'             => 0,
                'dispOrder'          => parent::escStr( $_POST['dispOrder'] ),
                'user_id'            => $_SESSION["USER_ID"],
                'organization'       => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $posKeyFile->addNewData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }

        /**
         * POSキーファイル一覧修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1043" );

            $posKeyFile = new posKeyFile();

            $postArray = array(
                'posKeyFileId'     => parent::escStr( $_POST['posKeyFileId'] ),
                'posBrandId'       => parent::escStr( $_POST['posBrandName'] ),
                'dispOrder'        => parent::escStr( $_POST['dispOrder'] ),
                'posKeyFileName'   => parent::escStr( $_POST['posKeyFileName'] ),
                'posKeyType'       => parent::escStr( $_POST['posKeyType'] ),
                'comment'          => parent::escStr( $_POST['comment'] ),
                'cooperationCode'  => parent::escStr( $_POST['cooperationCode'] ),
                'updateTime'       => parent::escStr( $_POST['updateTime'] ),
                'user_id'          => $_SESSION["USER_ID"],
                'organization'     => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $posKeyFile->modUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * POSキーファイル一覧削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1044" );

            $posKeyFile = new posKeyFile();

            $postArray = array(
                'posKeyFileId'   => parent::escStr( $_POST['posKeyFileId'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'is_del'         => 1,
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $posKeyFile->delUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   delAction");
        }

        /**
         * 新規登録用エリアの更新
         * @note     POSキーファイルマスタを新規作成した場合、新規登録用エリアを更新する
         * @return   新規登録用エリア
         */
        public function inputAreaAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1045" );

            $posKeyFile = new posKeyFile();

            // 登録用POS種別プルダウン
            $abbPosBrandList = $posKeyFile->getPosBrandList( 'registration' );

            $Log->trace("END inputAreaAction");
            
            require_once './View/PosKeyFileInputPanel.html';
        }

        /**
         * POSキーファイル一覧
         * @note     POSキーファイル全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $posKeyFile = new posKeyFile();

            $searchArray = array(
                'pos_brand_id'   => '',
                'is_del'         => 0,
                'sort'           => '',
            );

            // POSキーファイル一覧データ取得
            $posKeyFileAllList = $posKeyFile->getListData($searchArray);

            // POSキーファイル一覧レコード数
            $posKeyFileRecordCnt = count($posKeyFileAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($posKeyFileRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "pos_key_file_id";

            $posKeyFileList = $this->refineListDisplayNoSpecifiedPage($posKeyFileAllList, $idName, $pagedRecordCnt, $pageNo);
            $posKeyFileList = $this->modBtnDelBtnDisabledCheck($posKeyFileList);

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($posKeyFileRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 登録用POS種別プルダウン
            $abbPosBrandList = $posKeyFile->getPosBrandList( 'registration' );
            
            // キー種別プルダウン

            $headerArray = $this->changeHeaderItemMark(0);

            $posKeyFileNoSortFlag = false;

            $posKeyFile_no = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/PosKeyFilePanel.html';
        }

        /**
         * POSキーファイル一覧更新
         * @note     POSキーファイルの一覧部分のみの更新
         * @param    $addFlag           新規登録フラグ true：新規登録  false：新規登録以外
         * @param    $messege           DBの更新結果(データ指定がない場合、デフォルト値[MSG_BASE_0000]を設定)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $searchArray = array(
                'posBrandId'     => parent::escStr( $_POST['searchPosBrandName'] ),
                'is_del'         => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'sort'           => parent::escStr( $_POST['sortConditions'] ),
            );

            $posKeyFile = new posKeyFile();

            // シーケンスIDName
            $idName = "pos_key_file_id";

            // POSキーファイル一覧データ取得
            $posKeyFileSearchList = $posKeyFile->getListData($searchArray);
            
            // POSキーファイル一覧検索後のレコード数
            $posKeyFileSearchListCnt = count($posKeyFileSearchList);
            // POSキーファイル一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
            // 検索結果後のPOSキーファイルレコードに対するページ数
            $pagedCnt = ceil($posKeyFileSearchListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データPOSキーファイルIDを取得
                $posKeyFileLastId = $posKeyFile->getLastid();
                $this->pageNoWhenUpdating($posKeyFileSearchList, $idName, $posKeyFileLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $posKeyFileList = $this->refineListDisplayNoSpecifiedPage($posKeyFileSearchList, $idName, $pagedRecordCnt, $pageNo);
            $posKeyFileList = $this->modBtnDelBtnDisabledCheck($posKeyFileList);

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // 登録用キー種別プルダウン
            $abbPosBrandList = $posKeyFile->getPosBrandList( 'registration' );

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $posKeyFileNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $posKeyFile_no = 0;
            
            $display_no = $this->getDisplayNo( $posKeyFileSearchListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/PosKeyFileTablePanel.html';
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
            }
            $Log->trace("END   initialList");
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
            $Log->trace("START changeHeaderItemMark");
            
            // 初期化
            $headerArray = array(
                    'posKeyFileNoSortMark'        => '',
                    'posKeyFileDispOrderSortMark' => '',
                    'posBrandNameSortMark'        => '',
                    'posKeyFileNameSortMark'      => '',
                    'posKeyFileTypeSortMark'      => '',
                    'cooperationCodeSortMark'     => '',
                    'commentSortMark'             => '',
                    'registrationTimeSortMark'    => '',
                    'updateTimeSortMark'          => '',
                    'posKeyFileStateSortMark'     => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]  = "posKeyFileNoSortMark";
                $sortList[2]  = "posKeyFileNoSortMark";
                $sortList[3]  = "posKeyFileDispOrderSortMark";
                $sortList[4]  = "posKeyFileDispOrderSortMark";
                $sortList[5]  = "posBrandNameSortMark";
                $sortList[6]  = "posBrandNameSortMark";
                $sortList[7]  = "posKeyFileNameSortMark";
                $sortList[8]  = "posKeyFileNameSortMark";
                $sortList[9]  = "posKeyFileTypeSortMark";
                $sortList[10] = "posKeyFileTypeSortMark";
                $sortList[11] = "cooperationCodeSortMark";
                $sortList[12] = "cooperationCodeSortMark";
                $sortList[13] = "commentSortMark";
                $sortList[14] = "commentSortMark";
                $sortList[15] = "registrationTimeSortMark";
                $sortList[16] = "registrationTimeSortMark";
                $sortList[17] = "updateTimeSortMark";
                $sortList[18] = "updateTimeSortMark";
                $sortList[19] = "posKeyFileStateSortMark";
                $sortList[20] = "posKeyFileStateSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
