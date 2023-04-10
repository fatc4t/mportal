<?php
    /**
     * @file      セクションコントローラ
     * @author    USE M.Higashihara
     * @date      2016/05/24
     * @version   1.00
     * @note      セクションの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // セクション処理モデル
    require './Model/Section.php';

    /**
     * セクションコントローラクラス
     * @note   セクションの新規登録/修正/削除を行う
     */
    class SectionController extends BaseController
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
         * セクション一覧画面初期表示
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
         * セクション一覧画面検索
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
         * セクション一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1042" );

            $section = new section();

            $postArray = array(
                'sectionCode'    => parent::escStr( $_POST['sectionCode'] ),
                'organizationID' => parent::escStr( $_POST['organizationId'] ),
                'sectionName'    => parent::escStr( $_POST['sectionName'] ),
                'is_del'         => 0,
                'dispOrder'      => parent::escStr( $_POST['dispOrder'] ),
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $section->addNewData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }

        /**
         * セクション一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1043" );

            $section = new section();

            $postArray = array(
                'sectionID'      => parent::escStr( $_POST['sectionID'] ),
                'sectionCode'    => parent::escStr( $_POST['sectionCode'] ),
                'organizationID' => parent::escStr( $_POST['organizationId'] ),
                'sectionName'    => parent::escStr( $_POST['sectionName'] ),
                'dispOrder'      => parent::escStr( $_POST['dispOrder'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $section->modUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * セクション一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1044" );

            $section = new section();

            $postArray = array(
                'sectionID'      => parent::escStr( $_POST['sectionID'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'is_del'         => 1,
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $section->delUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   delAction");
        }

        /**
         * 新規登録用エリアの更新
         * @note     セクションマスタを新規作成した場合、新規登録用エリアを更新する
         * @return   新規登録用エリア
         */
        public function inputAreaAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1045" );

            $searchArray = array(
                'is_del'         => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'codeID'         => parent::escStr( $_POST['searchCodeName'] ),
                'organizationID' => parent::escStr( $_POST['searchOrganizatioID'] ),
                'sectionName'    => parent::escStr( $_POST['searchSectionName'] ),
                'sort'           => parent::escStr( $_POST['sortConditions'] ),
            );

            $section = new section();

            // 登録用組織プルダウン
            $abbreviatedList = $section->setPulldown->getSearchOrganizationList( 'registration', true  );
            $regFlag = count( $abbreviatedList ) -1;

            // シーケンスIDName
            $idName = "section_id";

            // セクション一覧データ取得
            $sectionSearchList = $section->getListData($searchArray);

            // セクション一覧検索後のレコード数
            $sectionSearchListCnt = count($sectionSearchList);

            // セクション一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後のセクションレコードに対するページ数
            $pagedCnt = ceil($sectionSearchListCnt /  $pagedRecordCnt);
            
            // 指定ページ
            $pageNo = parent::escStr( $_POST['displayPageNo'] );

            // ページ部分に対応させたリスト
            $sectionList = $this->refineListDisplayNoSpecifiedPage($sectionSearchList, $idName, $pagedRecordCnt, $pageNo);
            $sectionList = $this->modBtnDelBtnDisabledCheck($sectionList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($sectionList) >= 11 )
            {
                $isScrollBar = true;
            }

            $Log->trace("END   inputAreaAction");
            
            require_once './View/SectionInputPanel.html';
        }

        /**
         * 検索用コードリストの更新
         * @note     セクションマスタを更新した場合、検索用のリストも更新する
         * @return   検索用コードリスト
         */
        public function searchCodeNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchCodeNameAction");
            $Log->info( "MSG_INFO_1046" );

            $section = new section();

            $codeList = $section->getSearchCodeList();                                               // コードリスト

            $searchArray['codeID'] = parent::escStr( $_POST['searchCodeName'] );

            $Log->trace("END   searchCodeNameAction");
            
            require_once '../FwCommon/View/SearchCodeName.php';
        }

        /**
         * 検索用セクションリストの更新
         * @note     セクションマスタを更新した場合、検索用のリストも更新する
         * @return   検索用セクションリスト
         */
        public function searchSectionNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchSectionNameAction");
            $Log->info( "MSG_INFO_1047" );

            $section = new section();

            $sectionNameList = $section->setPulldown->getSearchSectionList( 'reference' );               // セクションリスト

            $searchArray['sectionName'] = parent::escStr( $_POST['searchSectionName'] );

            $Log->trace("END   searchSectionNameAction");
            
            require_once '../FwCommon/View/SearchSectionName.php';
        }

        /**
         * セクション一覧画面
         * @note     セクション画面全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $section = new section();

            // 検索プルダウン
            $codeList            = $section->getSearchCodeList();                                           // コードリスト
            $abbreviatedNameList = $section->setPulldown->getSearchOrganizationList( 'reference', true );   // 組織略称名リスト
            $sectionNameList     = $section->setPulldown->getSearchSectionList( 'reference' );              // セクションリスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;

            $searchArray = array(
                'is_del'         => 0,
                'codeID'         => '',
                'organizationID' => '',
                'sectionName'    => '',
                'sort'           => '',
            );

            // セクション一覧データ取得
            $sectionAllList = $section->getListData($searchArray);

            // セクション一覧レコード数
            $sectionRecordCnt = count($sectionAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($sectionRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "section_id";

            $sectionList = $this->refineListDisplayNoSpecifiedPage($sectionAllList, $idName, $pagedRecordCnt, $pageNo);
            $sectionList = $this->modBtnDelBtnDisabledCheck($sectionList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($sectionList) >= 11 )
            {
                $isScrollBar = true;
            }

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($sectionRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 登録用組織プルダウン
            $abbreviatedList = $section->setPulldown->getSearchOrganizationList( 'registration', true  );
            $regFlag = count( $abbreviatedList ) -1;

            $headerArray = $this->changeHeaderItemMark(0);

            $sectionNoSortFlag = false;

            $section_no = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/SectionPanel.html';
        }

        /**
         * セクション一覧更新
         * @note     セクション画面の一覧部分のみの更新
         * @param    $addFlag           新規登録フラグ true：新規登録  false：新規登録以外
         * @param    $messege           DBの更新結果(データ指定がない場合、デフォルト値[MSG_BASE_0000]を設定)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $searchArray = array(
                'is_del'         => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'codeID'         => parent::escStr( $_POST['searchCodeName'] ),
                'organizationID' => parent::escStr( $_POST['searchOrganizatioID'] ),
                'sectionName'    => parent::escStr( $_POST['searchSectionName'] ),
                'sort'           => parent::escStr( $_POST['sortConditions'] ),
            );

            $section = new section();

            // シーケンスIDName
            $idName = "section_id";

            // セクション一覧データ取得
            $sectionSearchList = $section->getListData($searchArray);

            // セクション一覧検索後のレコード数
            $sectionSearchListCnt = count($sectionSearchList);

            // セクション一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後のセクションレコードに対するページ数
            $pagedCnt = ceil($sectionSearchListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データセクションIDを取得
                $sectionLastId = $section->getLastid();
                $this->pageNoWhenUpdating($sectionSearchList, $idName, $sectionLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $sectionList = $this->refineListDisplayNoSpecifiedPage($sectionSearchList, $idName, $pagedRecordCnt, $pageNo);
            $sectionList = $this->modBtnDelBtnDisabledCheck($sectionList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($sectionList) >= 11 )
            {
                $isScrollBar = true;
            }

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // 登録用組織プルダウン
            $abbreviatedList = $section->setPulldown->getSearchOrganizationList( 'registration', true  );
            $regFlag = count( $abbreviatedList ) -1;

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $sectionNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $section_no = 0;
            
            $display_no = $this->getDisplayNo( $sectionSearchListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/SectionTablePanel.html';
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
                    'sectionNoSortMark'           => '',
                    'sectionStateSortMark'        => '',
                    'sectionCodeSortMark'         => '',
                    'sectionOrganizationSortMark' => '',
                    'sectionNameSortMark'         => '',
                    'sectionDispOrderSortMark'    => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1] = "sectionNoSortMark";
                $sortList[2] = "sectionNoSortMark";
                $sortList[3] = "sectionStateSortMark";
                $sortList[4] = "sectionStateSortMark";
                $sortList[5] = "sectionCodeSortMark";
                $sortList[6] = "sectionCodeSortMark";
                $sortList[7] = "sectionOrganizationSortMark";
                $sortList[8] = "sectionOrganizationSortMark";
                $sortList[9] = "sectionNameSortMark";
                $sortList[10] = "sectionNameSortMark";
                $sortList[11] = "sectionDispOrderSortMark";
                $sortList[12] = "sectionDispOrderSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
