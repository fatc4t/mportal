<?php
    /**
     * @file      帳票設定コントローラ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票設定の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetForm.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetFormController extends BaseController
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
         * 帳票設定一覧画面初期表示
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
         * 帳票設定一覧画面検索
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
         * 帳票設定一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1042" );

            $ledgerSheetForm = new ledgerSheetForm();

            $postArray = array(
                'dispOrder'                 => parent::escStr( $_POST['dispOrder'] ),
                'ledgerSheetFormCode'       => parent::escStr( $_POST['ledgerSheetFormCode'] ),
                'ledgerSheetFormName'       => parent::escStr( $_POST['ledgerSheetFormName'] ),
                'ledgerSheetFormNameKana'   => parent::escStr( $_POST['ledgerSheetFormNameKana'] ),
                'header'                    => parent::escStr( $_POST['header'] ),
                'logic'                     => parent::escStr( $_POST['logic'] ),
                'cYear'                     => parent::escStr( $_POST['cYear'] ),
                'cMonth'                    => parent::escStr( $_POST['cMonth'] ),
                'cDay'                      => parent::escStr( $_POST['cDay'] ),
                'cKikan'                    => parent::escStr( $_POST['cKikan'] ),
                'cStaff'                    => parent::escStr( $_POST['cStaff'] ),
                'cStaffBumon'               => parent::escStr( $_POST['cStaffBumon'] ),
                'cMenu'                     => parent::escStr( $_POST['cMenu'] ),
                'cMenuBumon'                => parent::escStr( $_POST['cMenuBumon'] ),
                'cJikan'                    => parent::escStr( $_POST['cJikan'] ),
                'cJikantai'                 => parent::escStr( $_POST['cJikantai'] ),
                'cTime1'                    => parent::escStr( $_POST['cTime1'] ),
                'cTime2'                    => parent::escStr( $_POST['cTime2'] ),
                'cTime3'                    => parent::escStr( $_POST['cTime3'] ),
                'cTime4'                    => parent::escStr( $_POST['cTime4'] ),
                'cTime5'                    => parent::escStr( $_POST['cTime5'] ),
                'cTime6'                    => parent::escStr( $_POST['cTime6'] ),
                'cTime7'                    => parent::escStr( $_POST['cTime7'] ),
                'cTime8'                    => parent::escStr( $_POST['cTime8'] ),
                'cTime9'                    => parent::escStr( $_POST['cTime9'] ),
                'cTime10'                   => parent::escStr( $_POST['cTime10'] ),
                'cTime11'                   => parent::escStr( $_POST['cTime11'] ),
                'cTime12'                   => parent::escStr( $_POST['cTime12'] ),
                'cTime13'                   => parent::escStr( $_POST['cTime13'] ),
                'cTime14'                   => parent::escStr( $_POST['cTime14'] ),
                'cTime15'                   => parent::escStr( $_POST['cTime15'] ),
                'cTime16'                   => parent::escStr( $_POST['cTime16'] ),
                'cTime17'                   => parent::escStr( $_POST['cTime17'] ),
                'cTime18'                   => parent::escStr( $_POST['cTime18'] ),
                'cTime18'                   => parent::escStr( $_POST['cTime19'] ),
                'cTime19'                   => parent::escStr( $_POST['cTime20'] ),
                'cTime20'                   => parent::escStr( $_POST['cTime21'] ),
                'cTime21'                   => parent::escStr( $_POST['cTime22'] ),
                'cTime22'                   => parent::escStr( $_POST['cTime23'] ),
                'cTime23'                   => parent::escStr( $_POST['cTime24'] ),
                'cTime24'                   => parent::escStr( $_POST['cTime25'] ),
                'is_del'                    => 0,
                'user_id'                   => $_SESSION["USER_ID"],
                'organization'              => $_SESSION["ORGANIZATION_ID"],
                );

            $messege = $ledgerSheetForm->addNewData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }

        /**
         * 帳票設定一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1043" );

            $ledgerSheetForm = new ledgerSheetForm();

            $postArray = array(
                'ledgerSheetFormId'        => parent::escStr( $_POST['ledgerSheetFormId'] ),
                'dispOrder'                => parent::escStr( $_POST['dispOrder'] ),
                'ledgerSheetFormCode'      => parent::escStr( $_POST['ledgerSheetFormCode'] ),
                'ledgerSheetFormName'      => parent::escStr( $_POST['ledgerSheetFormName'] ),
                'ledgerSheetFormNameKana'  => parent::escStr( $_POST['ledgerSheetFormNameKana'] ),
                'header'                   => parent::escStr( $_POST['header'] ),
                'logic'                    => parent::escStr( $_POST['logic'] ),
                'cMonth'                   => parent::escStr( $_POST['cMonth'] ),
                'cDay'                     => parent::escStr( $_POST['cDay'] ),
                'cKikan'                   => parent::escStr( $_POST['cKikan'] ),
                'cStaff'                   => parent::escStr( $_POST['cStaff'] ),
                'cStaffBumon'              => parent::escStr( $_POST['cStaffBumon'] ),
                'cMenu'                    => parent::escStr( $_POST['cMenu'] ),
                'cMenuBumon'               => parent::escStr( $_POST['cMenuBumon'] ),
                'cJikan'                   => parent::escStr( $_POST['cJikan'] ),
                'cJikantai'                => parent::escStr( $_POST['cJikantai'] ),
                'cTime1'                   => parent::escStr( $_POST['cTime1'] ),
                'cTime2'                   => parent::escStr( $_POST['cTime2'] ),
                'cTime3'                   => parent::escStr( $_POST['cTime3'] ),
                'cTime4'                   => parent::escStr( $_POST['cTime4'] ),
                'cTime5'                   => parent::escStr( $_POST['cTime5'] ),
                'cTime6'                   => parent::escStr( $_POST['cTime6'] ),
                'cTime7'                   => parent::escStr( $_POST['cTime7'] ),
                'cTime8'                   => parent::escStr( $_POST['cTime8'] ),
                'cTime9'                   => parent::escStr( $_POST['cTime9'] ),
                'cTime10'                  => parent::escStr( $_POST['cTime10'] ),
                'cTime11'                  => parent::escStr( $_POST['cTime11'] ),
                'cTime12'                  => parent::escStr( $_POST['cTime12'] ),
                'cTime13'                  => parent::escStr( $_POST['cTime13'] ),
                'cTime14'                  => parent::escStr( $_POST['cTime14'] ),
                'cTime15'                  => parent::escStr( $_POST['cTime15'] ),
                'cTime16'                  => parent::escStr( $_POST['cTime16'] ),
                'cTime17'                  => parent::escStr( $_POST['cTime17'] ),
                'cTime18'                  => parent::escStr( $_POST['cTime18'] ),
                'cTime19'                  => parent::escStr( $_POST['cTime19'] ),
                'cTime20'                  => parent::escStr( $_POST['cTime20'] ),
                'cTime21'                  => parent::escStr( $_POST['cTime21'] ),
                'cTime22'                  => parent::escStr( $_POST['cTime22'] ),
                'cTime23'                  => parent::escStr( $_POST['cTime23'] ),
                'cTime24'                  => parent::escStr( $_POST['cTime24'] ),
                'updateTime'               => parent::escStr( $_POST['updateTime'] ),
                'user_id'                  => $_SESSION["USER_ID"],
                'organization'             => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $ledgerSheetForm->modUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * 帳票設定一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1044" );

            $ledgerSheetForm = new ledgerSheetForm();

            $postArray = array(
                'ledgerSheetFormId'   => parent::escStr( $_POST['ledgerSheetFormId'] ),
                'updateTime'          => parent::escStr( $_POST['updateTime'] ),
                'is_del'              => 1,
                'user_id'             => $_SESSION["USER_ID"],
                'organization'        => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $ledgerSheetForm->delUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   delAction");
        }

        /**
         * 新規登録用エリアの更新
         * @note     帳票設定マスタを新規作成した場合、新規登録用エリアを更新する
         * @return   新規登録用エリア
         */
        public function inputAreaAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1045" );

            $ledgerSheetForm = new ledgerSheetForm();

            $Log->trace("END inputAreaAction");
            
            require_once './View/LedgerSheetFormInputPanel.html';
        }

        /**
         * 帳票設定一覧画面
         * @note     帳票設定画面全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $ledgerSheetForm = new ledgerSheetForm();

            $searchArray = array(
                'ledger_sheet_form_code'    => '',
                'ledger_sheet_form_name'    => '',
                'is_del'                    => 0,
                'sort'                      => '',
            );

            // 帳票設定一覧データ取得
            $ledgerSheetFormAllList = $ledgerSheetForm->getListData($searchArray);

            // 帳票設定一覧レコード数
            $ledgerSheetFormRecordCnt = count($ledgerSheetFormAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($ledgerSheetFormRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "ledger_sheet_form_id";

            $ledgerSheetFormList = $this->refineListDisplayNoSpecifiedPage($ledgerSheetFormAllList, $idName, $pagedRecordCnt, $pageNo);
            $ledgerSheetFormList = $this->modBtnDelBtnDisabledCheck($ledgerSheetFormList);

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
                             
            $pagedCnt = ceil($ledgerSheetFormRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            $headerArray = $this->changeHeaderItemMark(0);

            $ledgerSheetFormNoSortFlag = false;

            $ledgerSheetForm_no  = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/LedgerSheetFormPanel.html';
        }

        /**
         * 帳票設定一覧更新
         * @note     帳票設定画面の一覧部分のみの更新
         * @param    $addFlag           新規登録フラグ true：新規登録  false：新規登録以外
         * @param    $messege           DBの更新結果(データ指定がない場合、デフォルト値[MSG_BASE_0000]を設定)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $searchArray = array(
                'ledgerSheetFormCode'     => parent::escStr( $_POST['searchLedgerSheetFormCode'] ),
                'ledgerSheetFormName'     => parent::escStr( $_POST['searchLedgerSheetFormName'] ),
                'is_del'                  => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'sort'                    => parent::escStr( $_POST['sortConditions'] ),
            );

            $ledgerSheetForm = new ledgerSheetForm();

            // シーケンスIDName
            $idName = "ledger_sheet_form_id";

            // 帳票設定一覧データ取得
            $ledgerSheetFormSearchList = $ledgerSheetForm->getListData($searchArray);
            
            // 帳票設定一覧検索後のレコード数
            $ledgerSheetFormSearchListCnt = count($ledgerSheetFormSearchList);
            // 帳票設定一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
            // 検索結果後の帳票設定レコードに対するページ数
            $pagedCnt = ceil($ledgerSheetFormSearchListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データ帳票設定IDを取得
                $ledgerSheetFormLastId = $ledgerSheetForm->getLastid();
                $this->pageNoWhenUpdating($ledgerSheetFormSearchList, $idName, $ledgerSheetFormLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $ledgerSheetFormList = $this->refineListDisplayNoSpecifiedPage($ledgerSheetFormSearchList, $idName, $pagedRecordCnt, $pageNo);
            $ledgerSheetFormList = $this->modBtnDelBtnDisabledCheck($ledgerSheetFormList);

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $ledgerSheetFormNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $ledgerSheetForm_no = 0;
            
            $display_no = $this->getDisplayNo( $ledgerSheetFormSearchListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/LedgerSheetFormTablePanel.html';
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
                    'ledgerSheetFormNoSortMark'         => '',
                    'ledgerSheetFormDispOrderSortMark'  => '',
                    'ledgerSheetFormCodeSortMark'       => '',
                    'ledgerSheetFormNameSortMark'       => '',
                    'ledgerSheetFormNameKanaSortMark'   => '',
                    'headerSortMark'                    => '',
                    'logicSortMark'                     => '',
                    'cYearSortMark'                     => '',
                    'cMonthSortMark'                    => '',
                    'cDaySortMark'                      => '',
                    'cKikanSortMark'                    => '',
                    'cStaffSortMark'                    => '',
                    'cStaffBumonSortMark'               => '',
                    'cMenuSortMark'                     => '',
                    'cMenuBumonSortMark'                => '',
                    'cJikanSortMark'                    => '',
                    'cJikantaiSortMark'                 => '',
                    'cTime1SortMark'                    => '',
                    'cTime2SortMark'                    => '',
                    'cTime3SortMark'                    => '',
                    'cTime4SortMark'                    => '',
                    'cTime5SortMark'                    => '',
                    'cTime6SortMark'                    => '',
                    'cTime7SortMark'                    => '',
                    'cTime8SortMark'                    => '',
                    'cTime9SortMark'                    => '',
                    'cTime10SortMark'                   => '',
                    'cTime11SortMark'                   => '',
                    'cTime12SortMark'                   => '',
                    'cTime13SortMark'                   => '',
                    'cTime14SortMark'                   => '',
                    'cTime15SortMark'                   => '',
                    'cTime16SortMark'                   => '',
                    'cTime17SortMark'                   => '',
                    'cTime18SortMark'                   => '',
                    'cTime19SortMark'                   => '',
                    'cTime20SortMark'                   => '',
                    'cTime21SortMark'                   => '',
                    'cTime22SortMark'                   => '',
                    'cTime23SortMark'                   => '',
                    'cTime24SortMark'                   => '',
                    'registrationTimeSortMark'          => '',
                    'updateTimeSortMark'                => '',
                    'mappingNameStateSortMark'          => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]  = "ledgerSheetFormNoSortMark";
                $sortList[2]  = "ledgerSheetFormNoSortMark";
                $sortList[3]  = "ledgerSheetFormDispOrderSortMark";
                $sortList[4]  = "ledgerSheetFormDispOrderSortMark";
                $sortList[5]  = "ledgerSheetFormCodeSortMark";
                $sortList[6]  = "ledgerSheetFormCodeSortMark";
                $sortList[7]  = "ledgerSheetFormNameSortMark";
                $sortList[8]  = "ledgerSheetFormNameSortMark";
                $sortList[9]  = "ledgerSheetFormNameKanaSortMark";
                $sortList[10] = "ledgerSheetFormNameKanaSortMark";
                $sortList[11] = "headerSortMark";
                $sortList[12] = "headerSortMark";
                $sortList[13] = "logicSortMark";
                $sortList[14] = "logicSortMark";
                $sortList[15] = "cYearSortMark";
                $sortList[16] = "cYearSortMark";
                $sortList[17] = "cMonthSortMark";
                $sortList[18] = "cMonthSortMark";
                $sortList[19] = "cDaySortMark";
                $sortList[20] = "cDaySortMark";
                $sortList[21] = "cKikanSortMark";
                $sortList[22] = "cKikanSortMark";
                $sortList[23] = "cStaffSortMark";
                $sortList[24] = "cStaffSortMark";
                $sortList[25] = "cStaffBumonSortMark";
                $sortList[26] = "cStaffBumonSortMark";
                $sortList[27] = "cMenuSortMark";
                $sortList[28] = "cMenuSortMark";
                $sortList[29] = "cMenuBumonSortMark";
                $sortList[30] = "cMenuBumonSortMark";
                $sortList[31] = "cJikanSortMark";
                $sortList[32] = "cJikanSortMark";
                $sortList[33] = "cJikantaiSortMark";
                $sortList[34] = "cJikantaiSortMark";
                $sortList[35] = "cTime1SortMark";
                $sortList[36] = "cTime1SortMark";
                $sortList[37] = "cTime2SortMark";
                $sortList[38] = "cTime2SortMark";
                $sortList[39] = "cTime3SortMark";
                $sortList[40] = "cTime3SortMark";
                $sortList[41] = "cTime4SortMark";
                $sortList[42] = "cTime4SortMark";
                $sortList[43] = "cTime5SortMark";
                $sortList[44] = "cTime5SortMark";
                $sortList[45] = "cTime6SortMark";
                $sortList[46] = "cTime6SortMark";
                $sortList[47] = "cTime7SortMark";
                $sortList[48] = "cTime7SortMark";
                $sortList[49] = "cTime8SortMark";
                $sortList[50] = "cTime8SortMark";
                $sortList[51] = "cTime9SortMark";
                $sortList[52] = "cTime9SortMark";
                $sortList[53] = "cTime10SortMark";
                $sortList[54] = "cTime10SortMark";
                $sortList[55] = "cTime11SortMark";
                $sortList[56] = "cTime11SortMark";
                $sortList[57] = "cTime12SortMark";
                $sortList[58] = "cTime12SortMark";
                $sortList[59] = "cTime13SortMark";
                $sortList[60] = "cTime13SortMark";
                $sortList[61] = "cTime14SortMark";
                $sortList[62] = "cTime14SortMark";
                $sortList[63] = "cTime15SortMark";
                $sortList[64] = "cTime15SortMark";
                $sortList[65] = "cTime16SortMark";
                $sortList[66] = "cTime16SortMark";
                $sortList[67] = "cTime17SortMark";
                $sortList[68] = "cTime17SortMark";
                $sortList[69] = "cTime18SortMark";
                $sortList[70] = "cTime18SortMark";
                $sortList[71] = "cTime19SortMark";
                $sortList[72] = "cTime19SortMark";
                $sortList[73] = "cTime20SortMark";
                $sortList[74] = "cTime20SortMark";
                $sortList[75] = "cTime21SortMark";
                $sortList[76] = "cTime21SortMark";
                $sortList[77] = "cTime22SortMark";
                $sortList[78] = "cTime22SortMark";
                $sortList[79] = "cTime23SortMark";
                $sortList[80] = "cTime23SortMark";
                $sortList[81] = "cTime24SortMark";
                $sortList[82] = "cTime24SortMark";
                $sortList[83] = "registrationTimeSortMark";
                $sortList[84] = "registrationTimeSortMark";
                $sortList[85] = "updateTimeSortMark";
                $sortList[86] = "updateTimeSortMark";
                $sortList[87] = "mappingNameStateSortMark";
                $sortList[88] = "mappingNameStateSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
