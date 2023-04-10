<?php
    /**
     * @file      休日コントローラ
     * @author    USE S.Kasai
     * @date      2016/06/10
     * @version   1.00
     * @note      休日の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/Holiday.php';

    /**
     * 休日コントローラクラス
     * @note   休日の新規登録/修正/削除を行う
     */
    class HolidayController extends BaseController
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
         * 休日一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1060" );
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }

        /**
         * 休日一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            $Log->info( "MSG_INFO_1061" );

            if(isset($_POST['displayPageNo']))
            {
                $_SESSION['PAGE_NO'] = parent::escStr($_POST['displayPageNo']);
            }

            if(isset($_POST['displayRecordCnt']))
            {
                $_SESSION['DISPLAY_RECORD_CNT'] = parent::escStr($_POST['displayRecordCnt']);
            }

            $this->initialList(false);
            $Log->trace("END   searchAction");
        }

        /**
         * 休日一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1062" );

            $holiday = new holiday();

            $postArray = array(
                'holidayCode'    => parent::escStr( $_POST['holidayCode'] ),
                'organizationID' => parent::escStr( $_POST['organizationId'] ),
                'holidayName'    => parent::escStr( $_POST['holidayName'] ),
                'holidayNameID'  => parent::escStr( $_POST['holidayNameId'] ),
                'workingHours'   => $this->changeMinuteFromTime( parent::escStr( $_POST['workingHours'] ) ),
                'workingDay'     => parent::escStr( $_POST['workingDay'] ),
                'comment'        => parent::escStr( $_POST['comment'] ),
                'is_del'         => 0,
                'dispOrder'      => parent::escStr( $_POST['dispOrder'] ),
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            if( "" === $postArray['workingHours'] )
            {
                $postArray['workingHours'] = 0 ;
            }
            if( "" === $postArray['workingDay'] )
            {
                $postArray['workingDay'] = 0 ;
            }

            $messege = $holiday->addNewData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }

        /**
         * 休日一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1063" );

            $holiday = new holiday();

            $postArray = array(
                'holidayID'      => parent::escStr( $_POST['holidayID'] ),
                'holidayCode'    => parent::escStr( $_POST['holidayCode'] ),
                'organizationID' => parent::escStr( $_POST['organizationId'] ),
                'holidayName'    => parent::escStr( $_POST['holidayName'] ),
                'holidayNameID'  => parent::escStr( $_POST['holidayNameId'] ),
                'workingHours'   => $this->changeMinuteFromTime( parent::escStr( $_POST['workingHours'] ) ),
                'workingDay'     => parent::escStr( $_POST['workingDay'] ),
                'comment'        => parent::escStr( $_POST['comment'] ),
                'dispOrder'      => parent::escStr( $_POST['dispOrder'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );
            
            if( "" === $postArray['workingHours'] )
            {
                $postArray['workingHours'] = 0 ;
            }
            if( "" === $postArray['workingDay'] )
            {
                $postArray['workingDay'] = 0 ;
            }
            
            $messege = $holiday->modUpdateData($postArray);
            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * 休日一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1064" );

            $holiday = new holiday();

            $postArray = array(
                'holidayID'      => parent::escStr( $_POST['holidayID'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'is_del'         => 1,
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $holiday->delUpdateData($postArray);

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
            $Log->info( "MSG_INFO_1065" );

            $workingHoursStart = "";
            $workingHoursEnd   = "";

            if( parent::escStr( $_POST['searchWorkingHoursStart'] ) != "" )
            {
                $workingHoursStart = $this->changeMinuteFromTime( parent::escStr( $_POST['searchWorkingHoursStart'] ) );
            }

            if( parent::escStr( $_POST['searchWorkingHoursEnd'] ) != "" )
            {
                $workingHoursEnd = $this->changeMinuteFromTime( parent::escStr( $_POST['searchWorkingHoursEnd'] ) );
            }

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            $searchArray = array(
                'is_del'               => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'codeID'               => parent::escStr( $_POST['searchCodeName'] ),
                'organizationID'       => parent::escStr( $_POST['searchOrganizatioID'] ),
                'holidayName'          => parent::escStr( $_POST['searchHolidayName'] ),
                'holidayNameID'        => parent::escStr( $_POST['searchHolidayNameID'] ),
                'workingHoursStart'    => $workingHoursStart,
                'workingHoursEnd'      => $workingHoursEnd,
                'workingDayStart'      => parent::escStr( $_POST['searchWorkingDayStart'] ),
                'workingDayEnd'        => parent::escStr( $_POST['searchWorkingDayEnd'] ),
                'comment'              => parent::escStr( $_POST['searchComment'] ),
                'sort'                 => parent::escStr( $_POST['sortConditions'] ),
            );
            //--------------------------------------------------------------->

            $holiday = new holiday();

            // 登録用組織プルダウン
            $abbreviatedList = $holiday->setPulldown->getSearchOrganizationList( 'registration', true );
            // 登録用休日名称プルダウン
            $holidayNameAddList = $holiday->setPulldown->getSearchHolidayNameNameList( 'registration' );

            $regFlag = count( $abbreviatedList ) -1;

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // シーケンスIDName
            $idName = "holiday_id";

            // 休日一覧データ取得
            $holidaySearchList = $holiday->getListData($searchArray);
            
            // 休日一覧検索後のレコード数
            $holidaySearchListCnt = count($holidaySearchList);
            // 休日一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後の休日レコードに対するページ数
            $pagedCnt = ceil($holidaySearchListCnt /  $pagedRecordCnt);
            
            // 指定ページ
            $pageNo = parent::escStr( $_POST['displayPageNo'] );

            // ページ部分に対応させたリスト
            $holidayList = $this->refineListDisplayNoSpecifiedPage($holidaySearchList, $idName, $pagedRecordCnt, $pageNo);
            $holidayList = $this->modBtnDelBtnDisabledCheck($holidayList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($holidayList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->

            $Log->trace("END   inputAreaAction");
            
            require_once './View/HolidayInputPanel.html';
        }
        
        /**
         * 検索用休日リストの更新
         * @note     休日リストを更新した場合、検索用のリストも更新する
         * @return   検索用休日リスト
         */
        public function searchHolidayNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchHolidayNameAction");
            $Log->info( "MSG_INFO_1066" );

            $holiday = new holiday();

            $holidayNameList = $holiday->setPulldown->getSearchHolidayNameList( 'reference' );               // 休日リスト

            $searchArray['holidayName'] = parent::escStr( $_POST['searchHolidayName'] );

            $Log->trace("END   searchHolidayNameAction");
        
            require_once './View/Common/SearchHolidayName.php';
        }

        /**
         * 検索用コードリストの更新
         * @note     コードリストを更新した場合、検索用のリストも更新する
         * @return   検索用コードリスト
         */
        public function searchCodeNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchCodeNameAction");
            $Log->info( "MSG_INFO_1067" );

            $holiday = new holiday();

            $codeList = $holiday->getSearchCodeList();                                               // コードリスト

            $searchArray['codeID'] = parent::escStr( $_POST['searchCodeName'] );

            $Log->trace("END   searchCodeNameAction");
            
            require_once '../FwCommon/View/SearchCodeName.php';
        }


        /**
         * 休日一覧画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $holiday = new holiday();

            // 検索プルダウン
            $codeList                 = $holiday->getSearchCodeList();                                          // コードリスト
            $abbreviatedNameList      = $holiday->setPulldown->getSearchOrganizationList( 'reference', true );  // 組織略称名リスト
            $holidayNameNameList      = $holiday->setPulldown->getSearchHolidayNameNameList( 'reference' );     // 休日名称名リスト
            $holidayNameList          = $holiday->setPulldown->getSearchHolidayNameList( 'reference' );         // 休日リスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;

            $searchArray = array(
                'is_del'              => 0,
                'codeID'              => '',
                'organizationID'      => '',
                'holidayName'         => '',
                'holidayNameID'       => '',
                'workingHoursStart'   => '',
                'workingHoursEnd'     => '',
                'workingDayStart'     => '',
                'workingDayEnd'       => '',
                'comment'             => '',
                'sort'                => '',
            );

            // 休日一覧データ取得
            $holidayAllList = $holiday->getListData($searchArray);

            // 休日一覧レコード数
            $holidayRecordCnt = count($holidayAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($holidayRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "holiday_id";

            $holidayList = $this->refineListDisplayNoSpecifiedPage($holidayAllList, $idName, $pagedRecordCnt, $pageNo);
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

            $pagedCnt = ceil($holidayRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 登録用組織プルダウン
            $abbreviatedList = $holiday->setPulldown->getSearchOrganizationList( 'registration', true );
            $regFlag = count( $abbreviatedList ) -1;

            $headerArray = $this->changeHeaderItemMark(0);

            $holidayNoSortFlag = false;

            $holiday_no = 0;
            $display_no =0;
            
            // 登録用休日名称プルダウン
            $holidayNameAddList = $holiday->setPulldown->getSearchHolidayNameNameList( 'registration' );
            $regFlag = count( $holidayNameAddList ) -1;

            $headerArray = $this->changeHeaderItemMark(0);

            $holidayNoSortFlag = false;

            $holiday_no = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/HolidayPanel.html';
        }

        /**
         * 休日一覧更新
         * @note     パラメータは、View側で使用
         * @param    休日リスト(組織ID/休日ID)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $workingHoursStart = "";
            $workingHoursEnd   = "";

            if( parent::escStr( $_POST['searchWorkingHoursStart'] ) != "" )
            {
                $workingHoursStart = $this->changeMinuteFromTime( parent::escStr( $_POST['searchWorkingHoursStart'] ) );
            }

            if( parent::escStr( $_POST['searchWorkingHoursEnd'] ) != "" )
            {
                $workingHoursEnd = $this->changeMinuteFromTime( parent::escStr( $_POST['searchWorkingHoursEnd'] ) );
            }

            $searchArray = array(
                'is_del'               => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'codeID'               => parent::escStr( $_POST['searchCodeName'] ),
                'organizationID'       => parent::escStr( $_POST['searchOrganizatioID'] ),
                'holidayName'          => parent::escStr( $_POST['searchHolidayName'] ),
                'holidayNameID'        => parent::escStr( $_POST['searchHolidayNameID'] ),
                'workingHoursStart'    => $workingHoursStart,
                'workingHoursEnd'      => $workingHoursEnd,
                'workingDayStart'      => parent::escStr( $_POST['searchWorkingDayStart'] ),
                'workingDayEnd'        => parent::escStr( $_POST['searchWorkingDayEnd'] ),
                'comment'              => parent::escStr( $_POST['searchComment'] ),
                'sort'                 => parent::escStr( $_POST['sortConditions'] ),
            );
            $holiday = new holiday();

            // シーケンスIDName
            $idName = "holiday_id";

            // 休日一覧データ取得
            $holidaySearchList = $holiday->getListData($searchArray);
            
            // 休日一覧検索後のレコード数
            $holidaySearchListCnt = count($holidaySearchList);
            // 休日一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後の休日レコードに対するページ数
            $pagedCnt = ceil($holidaySearchListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データ休日IDを取得
                $holidayLastId = $holiday->getLastid();
                $this->pageNoWhenUpdating($holidaySearchList, $idName, $holidayLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $holidayList = $this->refineListDisplayNoSpecifiedPage($holidaySearchList, $idName, $pagedRecordCnt, $pageNo);
            $holidayList = $this->modBtnDelBtnDisabledCheck($holidayList);
            // 表示レコード数
            $holidayListCount = count($holidayList);

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
            $abbreviatedList = $holiday->setPulldown->getSearchOrganizationList( 'registration', true );
            $regFlag = count( $abbreviatedList ) -1;
            
            // 登録用休日名称プルダウン
            $holidayNameAddList = $holiday->setPulldown->getSearchHolidayNameNameList( 'registration' );
            $regFlag = count( $holidayNameAddList ) -1;


            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $holidayNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $holiday_no = 0;

            $display_no = $this->getDisplayNo( $holidaySearchListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/HolidayTablePanel.html';
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
         * @param    ソート条件番号(「No」押下時：1/「状態」押下時：3/「コード」押下時：5/「組織名」押下時：7/「休日名」押下時：9/「就業規則」押下時：11/「表示順」押下時：13)
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        private function changeHeaderItemMark($sortNo)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeHeaderItemMark");
            
            // 初期化
            $headerArray = array(
                    'holidayNoSortMark'               => '',
                    'holidayStateSortMark'            => '',
                    'holidayCodeSortMark'             => '',
                    'holidayOrganizationSortMark'     => '',
                    'holidayNameNameSortMark'         => '',
                    'holidayNameSortMark'             => '',
                    'holidayWorkingHoursSortMark'     => '',
                    'holidayWorkingDaySortMark'       => '',
                    'holidayCommentSortMark'          => '',
                    'holidayDispOrderSortMark'        => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1] = "holidayNoSortMark";
                $sortList[2] = "holidayNoSortMark";
                $sortList[3] = "holidayStateSortMark";
                $sortList[4] = "holidayStateSortMark";
                $sortList[5] = "holidayCodeSortMark";
                $sortList[6] = "holidayCodeSortMark";
                $sortList[7] = "holidayOrganizationSortMark";
                $sortList[8] = "holidayOrganizationSortMark";
                $sortList[9] = "holidayNameNameSortMark";
                $sortList[10] = "holidayNameNameSortMark";
                $sortList[11] = "holidayNameSortMark";
                $sortList[12] = "holidayNameSortMark";
                $sortList[13] = "holidayWorkingHoursSortMark";
                $sortList[14] = "holidayWorkingHoursSortMark";
                $sortList[15] = "holidayWorkingDaySortMark";
                $sortList[16] = "holidayWorkingDaySortMark";
                $sortList[17] = "holidayCommentSortMark";
                $sortList[18] = "holidayCommentSortMark";
                $sortList[19] = "holidayDispOrderSortMark";
                $sortList[20] = "holidayDispOrderSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }
            
            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
