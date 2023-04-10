<?php
    /**
     * @file      雇用形態コントローラ
     * @author    USE K.Narita
     * @date      2016/06/09
     * @version   1.00
     * @note      雇用形態の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/Employment.php';

    /**
     * 雇用形態コントローラクラス
     * @note   雇用形態の新規登録/修正/削除を行う
     */
    class EmploymentController extends BaseController
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
         * 雇用形態一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1030" );
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }

        /**
         * 雇用形態一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            $Log->info( "MSG_INFO_1031" );

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
         * 雇用形態一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1032" );

            $employment = new employment();

            $laborRegulationsID = parent::escStr( $_POST['laborRegulationsId'] );
            if( !is_numeric($laborRegulationsID) )
            {
                $laborRegulationsID = 0;
            }

            $postArray = array(
                'employmentCode'    => parent::escStr( $_POST['employmentCode'] ),
                'organizationID' => parent::escStr( $_POST['organizationId'] ),
                'employmentName'    => parent::escStr( $_POST['employmentName'] ),
                'laborRegulationsID'  => $laborRegulationsID,
                'is_del'         => 0,
                'dispOrder'      => parent::escStr( $_POST['dispOrder'] ),
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $employment->addNewData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }

        /**
         * 雇用形態一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1033" );

            $employment = new employment();

            $laborRegulationsID = parent::escStr( $_POST['laborRegulationsId'] );
            if( !is_numeric($laborRegulationsID) )
            {
                $laborRegulationsID = 0;
            }

            $postArray = array(
                'employmentID'      => parent::escStr( $_POST['employmentID'] ),
                'employmentCode'    => parent::escStr( $_POST['employmentCode'] ),
                'organizationID' => parent::escStr( $_POST['organizationId'] ),
                'employmentName'    => parent::escStr( $_POST['employmentName'] ),
                'laborRegulationsID'  => $laborRegulationsID,
                'dispOrder'      => parent::escStr( $_POST['dispOrder'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $employment->modUpdateData($postArray);
            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * 雇用形態一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1034" );

            $employment = new employment();

            $postArray = array(
                'employmentID'      => parent::escStr( $_POST['employmentID'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'is_del'         => 1,
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $employment->delUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   delAction");
        }

        
        /**
         * 新規登録用エリアの更新
         * @note    雇用形態マスタを新規作成した場合、新規登録エリアを更新する
         * @return  新規登録用エリア
         */
        public function inputAreaAction()
        {
            global $Log;    // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1035" );
            
            //-- 2016/09/19 Y.Sugou ------------------------------------->
            $searchArray = array(
                'is_del'               => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'codeID'               => parent::escStr( $_POST['searchCodeName'] ),
                'organizationID'       => parent::escStr( $_POST['searchOrganizatioID'] ),
                'employmentName'       => parent::escStr( $_POST['searchEmploymentName'] ),
                'laborRegulationsID'   => parent::escStr( $_POST['searchLaborRegulationsID'] ),
                'sort'                 => parent::escStr( $_POST['sortConditions'] ),
            );
            //--------------------------------------------------------------->
            $employment = new employment();
            
            $abbreviatedList = $employment->setPulldown->getSearchOrganizationList( 'registration', true, true );
            $laborRegulationsList = $employment->setPulldown->getSearchLaborRegulationsList( 'registration' );
            
            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 雇用形態一覧データ取得
            $employmentSearchList = $employment->getListData($searchArray);
            
            // 雇用形態一覧検索後のレコード数
            $emploSearchListCnt = count($employmentSearchList);
            // 雇用形態一覧表示レコード数
            $pagedRecordCnt = parent::escStr( $_POST['displayRecordCnt'] );

            // 検索結果後の雇用形態レコードに対するページ数
            $pagedCnt = ceil($emploSearchListCnt /  $pagedRecordCnt);
            
            // 指定ページ
            $pageNo = parent::escStr( $_POST['displayPageNo'] );

            // シーケンスIDName
            $idName = "employment_id";

            // ページ部分に対応させたリスト
            $employmentList = $this->refineListDisplayNoSpecifiedPage($employmentSearchList, $idName, $pagedRecordCnt, $pageNo);
            $employmentList = $this->modBtnDelBtnDisabledCheck($employmentList);
            
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($employmentList) >= 15)
            {
                $isScrollBar = true;
            }

            $regFlag = count( $abbreviatedList ) -1;
            //--------------------------------------------------------------->
            $Log->trace("END   inputAreaAction");
            
            require_once './View/EmploymentInputPanel.html';
        }

        /**
         * 検索用コードリストの更新
         * @note     雇用形態マスタを更新した場合、検索用のリストも更新する
         * @return   検索用コードリスト
         */
        public function searchCodeNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchCodeNameAction");
            $Log->info( "MSG_INFO_1036" );

            $employment = new employment();

            $codeList = $employment->getSearchCodeList();                        // コードリスト

            $searchArray['codeID'] = parent::escStr( $_POST['searchCodeName'] );

            $Log->trace("END   searchCodeNameAction");
            
            require_once '../FwCommon/View/SearchCodeName.php';
        }

        /**
         * 検索用雇用形態名リストの更新
         * @note     雇用形態マスタを更新した場合、検索用のリストも更新する
         * @return   検索用雇用形態名リスト
         */
        public function searchEmploymentNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchEmploymentNameAction");
            $Log->info( "MSG_INFO_1037" );

            $employment = new employment();

            $employmentNameList = $employment->setPulldown->getSearchEmploymentList( 'reference' );               // 雇用形態名リスト

            $searchArray['employmentName'] = parent::escStr( $_POST['searchEmploymentName'] );
            
            $Log->trace("END   searchEmploymentNameAction");
            
            require_once './View/Common/SearchEmploymentName.php';
        }

        /**
         * 雇用形態一覧画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $employment = new employment();

            // 検索プルダウン
            $codeList                 = $employment->getSearchCodeList();                                           // コードリスト
            $abbreviatedNameList      = $employment->setPulldown->getSearchOrganizationList( 'reference', true, true );   // 組織略称名リスト
            $employmentNameList       = $employment->setPulldown->getSearchEmploymentList( 'reference' );           // 雇用形態名リスト
            $laborRegNameList         = $employment->setPulldown->getSearchLaborRegulationsList( 'reference' );     // 就業規則リスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;

            $searchArray = array(
                'is_del'              => 0,
                'codeID'              => '',
                'organizationID'      => '',
                'employmentName'        => '',
                'laborRegulationsID'  => '',
                'sort'                => '',
            );

            // 雇用形態一覧データ取得
            $employmentAllList = $employment->getListData($searchArray);

            // 雇用形態一覧レコード数
            $employmentRecordCnt = count($employmentAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($employmentRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "employment_id";

            $employmentList = $this->refineListDisplayNoSpecifiedPage($employmentAllList, $idName, $pagedRecordCnt, $pageNo);
            $employmentList = $this->modBtnDelBtnDisabledCheck($employmentList);

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($employmentList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($employmentRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 登録用組織プルダウン
            $abbreviatedList = $employment->setPulldown->getSearchOrganizationList( 'registration', true, true );
            $regFlag = count( $abbreviatedList ) -1;

            // 登録用就業規則プルダウン
            $laborRegulationsList = $employment->setPulldown->getSearchLaborRegulationsList( 'registration' );

            $headerArray = $this->changeHeaderItemMark(0);

            $employmentNoSortFlag = false;

            $employment_no = 0;
            $display_no =0;

            $headerArray = $this->changeHeaderItemMark(0);

            $employmentNoSortFlag = false;

            $employment_no = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/EmploymentPanel.html';
        }

        /**
         * 雇用形態一覧更新
         * @note     パラメータは、View側で使用
         * @param    雇用形態リスト(組織ID/雇用形態ID)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $searchArray = array(
                'is_del'               => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'codeID'               => parent::escStr( $_POST['searchCodeName'] ),
                'organizationID'       => parent::escStr( $_POST['searchOrganizatioID'] ),
                'employmentName'       => parent::escStr( $_POST['searchEmploymentName'] ),
                'laborRegulationsID'   => parent::escStr( $_POST['searchLaborRegulationsID'] ),
                'sort'                 => parent::escStr( $_POST['sortConditions'] ),
            );

            $employment = new employment();

            // シーケンスIDName
            $idName = "employment_id";

            // 雇用形態一覧データ取得
            $employmentSearchList = $employment->getListData($searchArray);
            
            // 雇用形態一覧検索後のレコード数
            $emploSearchListCnt = count($employmentSearchList);
            // 雇用形態一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後の雇用形態レコードに対するページ数
            $pagedCnt = ceil($emploSearchListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データ雇用形態IDを取得
                $employmentLastId = $employment->getLastid();
                $this->pageNoWhenUpdating($employmentSearchList, $idName, $employmentLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $employmentList = $this->refineListDisplayNoSpecifiedPage($employmentSearchList, $idName, $pagedRecordCnt, $pageNo);
            $employmentList = $this->modBtnDelBtnDisabledCheck($employmentList);
            
            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($employmentList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->
            
            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // 登録用組織プルダウン
            $abbreviatedList = $employment->setPulldown->getSearchOrganizationList( 'registration', true, true );
            $regFlag = count( $abbreviatedList ) -1;
            
            // 登録用就業規則プルダウン
            $laborRegulationsList = $employment->setPulldown->getSearchLaborRegulationsList( 'registration' );

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $employmentNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $employment_no = 0;

            $display_no = $this->getDisplayNo( $emploSearchListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/EmploymentTablePanel.html';
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
         * @param    ソート条件番号(「No」押下時：1/「状態」押下時：3/「コード」押下時：5/「組織名」押下時：7/「雇用形態名」押下時：9/「就業規則」押下時：11/「表示順」押下時：13)
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        private function changeHeaderItemMark($sortNo)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeHeaderItemMark");
            
            // 初期化
            $headerArray = array(
                    'employmentNoSortMark'               => '',
                    'employmentStateSortMark'            => '',
                    'employmentCodeSortMark'             => '',
                    'employmentOrganizationSortMark'     => '',
                    'employmentNameSortMark'             => '',
                    'employmentLaborRegulationsSortMark' => '',
                    'employmentDispOrderSortMark'        => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1] = "employmentNoSortMark";
                $sortList[2] = "employmentNoSortMark";
                $sortList[3] = "employmentStateSortMark";
                $sortList[4] = "employmentStateSortMark";
                $sortList[5] = "employmentCodeSortMark";
                $sortList[6] = "employmentCodeSortMark";
                $sortList[7] = "employmentOrganizationSortMark";
                $sortList[8] = "employmentOrganizationSortMark";
                $sortList[9] = "employmentNameSortMark";
                $sortList[10] = "employmentNameSortMark";
                $sortList[11] = "employmentLaborRegulationsSortMark";
                $sortList[12] = "employmentLaborRegulationsSortMark";
                $sortList[13] = "employmentDispOrderSortMark";
                $sortList[14] = "employmentDispOrderSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }
            
            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
