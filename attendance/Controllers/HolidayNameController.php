<?php
    /**
     * @file      休日名称コントローラ
     * @author    USE S.Nakamura
     * @date      2016/06/14
     * @version   1.00
     * @note      休日名称の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/HolidayName.php';

    /**
     * 休日名称コントローラクラス
     * @note   ログイン時のリクエストを振り分けるf
     */
    class HolidayNameController extends BaseController
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
         * 休日名称一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1050" );
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }

        /**
         * 休日名称一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START searchAction");
            $Log->info( "MSG_INFO_1051" );
            
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
         * 休日名称一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1052" );

            $holidayName = new holidayName();

            $postArray = array(
                'holidayNameCode'    => parent::escStr( $_POST['holidayNameCode'] ),
                'organizationID' => parent::escStr( $_POST['organizationId'] ),
                'holidayName'    => parent::escStr( $_POST['holidayName'] ),
                'is_del'         => 0,
                'dispOrder'      => parent::escStr( $_POST['dispOrder'] ),
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $holidayName->addNewData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }
        /**
         * 休日名称一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1053" );

            $holidayName = new holidayName();

            $postArray = array(
                'holidayNameID'      => parent::escStr( $_POST['holidayNameID'] ),
                'holidayNameCode'    => parent::escStr( $_POST['holidayNameCode'] ),
                'organizationID' => parent::escStr( $_POST['organizationId'] ),
                'holidayName'    => parent::escStr( $_POST['holidayName'] ),
                'dispOrder'      => parent::escStr( $_POST['dispOrder'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $holidayName->modUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   modAction");

        }

        /**
         *休日名称 一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1054" );

            $holidayName = new holidayName();

            $postArray = array(
                'holidayNameID'      => parent::escStr( $_POST['holidayNameID'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'is_del'         => 1,
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $holidayName->delUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   delAction");
        }
        /**
         * 新規登録用エリアの更新
         * @note     休日名称マスタを新規作成した場合、新規登録用エリアを更新する
         * @return   新規登録用エリア
         */
        public function inputAreaAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1055" );

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            $searchArray = array(
                'is_del'         => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'codeID'         => parent::escStr( $_POST['searchCodeName'] ),
                'organizationID' => parent::escStr( $_POST['searchOrganizatioID'] ),
                'holidayNameID'    => parent::escStr( $_POST['searchHolidayName'] ),
                'sort'           => parent::escStr( $_POST['sortConditions'] ),
            );
            //--------------------------------------------------------------->

            $holidayName = new holidayName();

            // 登録用組織プルダウン
            $abbreviatedList = $holidayName->setPulldown->getSearchOrganizationList( 'registration', true  );
            $regFlag = count( $abbreviatedList ) -1;

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // シーケンスIDName
            $idName = "holiday_name_id";

            // 休日名称一覧データ取得
            $holidayNNSearchList = $holidayName->getListData($searchArray);
            
            // 休日名称一覧検索後のレコード数
            $hdNSearchListCnt = count($holidayNNSearchList);
            // 休日名称一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後の休日名称レコードに対するページ数
            $pagedCnt = ceil($hdNSearchListCnt /  $pagedRecordCnt);
            
            // 指定ページ
            $pageNo = parent::escStr( $_POST['displayPageNo'] );

            // ページ部分に対応させたリスト
            $holidayList = $this->refineListDisplayNoSpecifiedPage($holidayNNSearchList, $idName, $pagedRecordCnt, $pageNo);
            $holidayList = $this->modBtnDelBtnDisabledCheck($holidayList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($holidayList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->

            $Log->trace("END   inputAreaAction");
            
            require_once './View/HolidayNameInputPanel.html';
        }

        /**
         * 検索用コードリストの更新
         * @note     休日名称マスタを更新した場合、検索用のリストも更新する
         * @return   検索用コードリスト
         */
        public function searchCodeNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchCodeNameAction");
            $Log->info( "MSG_INFO_1056" );

            $holidayName = new holidayName();

            $codeList = $holidayName->getSearchCodeList();                                               // コードリスト

            $searchArray['codeID'] = parent::escStr( $_POST['searchCodeName'] );

            $Log->trace("END   searchCodeNameAction");
            
            require_once '../FwCommon/View/SearchCodeName.php';
        }

        /**
         * 検索用休日名称リストの更新
         * @note     休日名称マスタを更新した場合、検索用のリストも更新する
         * @return   検索用休日名称リスト
         */
        public function searchHolidayNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchHolidayNameAction");
            $Log->info( "MSG_INFO_1057" );

            $holidayName = new holidayName();

            $holidayNameNameList = $holidayName->setPulldown->getSearchHolidayNameNameList( 'reference' );               // 休日名称リスト

            $searchArray['holidayNameID'] = parent::escStr( $_POST['searchHolidayName'] );

            $Log->trace("END   searchHolidayNameAction");
            
            require_once './View/Common/SearchHolidayNameName.php';
        }

        /**
         * 休日名称一覧画面
         * @note     休日名称画面全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $holidayName = new holidayName ();

            // 検索プルダウン
            $codeList            = $holidayName->getSearchCodeList();                                           // コードリスト
            $abbreviatedNameList = $holidayName->setPulldown->getSearchOrganizationList( 'reference', true );   // 組織略称名リスト
            $holidayNameNameList = $holidayName->setPulldown->getSearchHolidayNameNameList('reference' );       // 休日名称リスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;

            $searchArray = array(
                'is_del'         => 0,
                'codeID'         => '',
                'organizationID' => '',
                'holidayNameID'    => '',
                'sort'           => '',
            );

            // 休日名称一覧データ取得
            $holidayNameAllList = $holidayName->getListData($searchArray);


            // 休日名称一覧レコード数
            $holidayNameRecordCnt = count($holidayNameAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($holidayNameRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "holiday_name_id";

            $holidayList = $this->refineListDisplayNoSpecifiedPage($holidayNameAllList, $idName, $pagedRecordCnt, $pageNo);
            $holidayList = $this->modBtnDelBtnDisabledCheck($holidayList);
            
            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($holidayList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->
            
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($holidayNameRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 登録用組織プルダウン
            $abbreviatedList = $holidayName->setPulldown->getSearchOrganizationList( 'registration', true  );
            $regFlag = count( $abbreviatedList ) -1;

            $headerArray = $this->changeHeaderItemMark(0);

            $holidayNoSortFlag = false;

            $holidayName_no = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/HolidayNamePanel.html';
        }

        /**
         * 休日名称一覧更新
         * @note     パラメータは、View側で使用
         * @param    休日名称リスト(組織ID/休日名称ID)
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
                'holidayNameID'    => parent::escStr( $_POST['searchHolidayName'] ),
                'sort'           => parent::escStr( $_POST['sortConditions'] ),
            );

            $holidayName = new holidayName();

            // シーケンスIDName
            $idName = "holiday_name_id";

            // 休日名称一覧データ取得
            $holidayNNSearchList = $holidayName->getListData($searchArray);
            
            // 休日名称一覧検索後のレコード数
            $hdNSearchListCnt = count($holidayNNSearchList);
            // 休日名称一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後の休日名称レコードに対するページ数
            $pagedCnt = ceil($hdNSearchListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データ休日名称IDを取得
                $holidayNameLastId = $holidayName->getLastid();
                $this->pageNoWhenUpdating($holidayNNSearchList, $idName, $holidayNameLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $holidayList = $this->refineListDisplayNoSpecifiedPage($holidayNNSearchList, $idName, $pagedRecordCnt, $pageNo);
            $holidayList = $this->modBtnDelBtnDisabledCheck($holidayList);
            
            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($holidayList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->
            
            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // 登録用組織プルダウン
            $abbreviatedList = $holidayName->setPulldown->getSearchOrganizationList( 'registration', true  );
            $regFlag = count( $abbreviatedList ) -1;

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $holidayNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $holidayName_no = 0;
            
            $display_no = $this->getDisplayNo( $hdNSearchListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/HolidayNameTablePanel.html';
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
         * @note     パラメータは、View側で使用
         * @param    ソート条件番号(「No」押下時：1/「状態」押下時：3/「コード」押下時：5/「組織名」押下時：7/「休日名称名」押下時：9/「表示名」押下時：11)
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        private function changeHeaderItemMark($sortNo)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeHeaderItemMark");
            
            // 初期化
            $headerArray = array(
                    'holidayNameNoSortMark'           => '',
                    'holidayNameStateSortMark'        => '',
                    'holidayNameCodeSortMark'         => '',
                    'holidayNameOrganizationSortMark' => '',
                    'holidayNameSortMark'         => '',
                    'holidayNameDispOrderSortMark'    => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1] = "holidayNameNoSortMark";
                $sortList[2] = "holidayNameNoSortMark";
                $sortList[3] = "holidayNameStateSortMark";
                $sortList[4] = "holidayNameStateSortMark";
                $sortList[5] = "holidayNameCodeSortMark";
                $sortList[6] = "holidayNameCodeSortMark";
                $sortList[7] = "holidayNameOrganizationSortMark";
                $sortList[8] = "holidayNameOrganizationSortMark";
                $sortList[9] = "holidayNameSortMark";
                $sortList[10] = "holidayNameSortMark";
                $sortList[11] = "holidayNameDispOrderSortMark";
                $sortList[12] = "holidayNameDispOrderSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }
            
            
            
            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
