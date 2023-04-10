<?php
    /**
     * @file      役職コントローラ
     * @author    USE S.Kasai
     * @date      2016/06/09
     * @version   1.00
     * @note      役職の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/Position.php';

    /**
     * 役職コントローラクラス
     * @note   役職の新規登録/修正/削除を行う
     */
    class PositionController extends BaseController
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
         * 役職一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1020" );
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }

        /**
         * 役職一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            $Log->info( "MSG_INFO_1021" );

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
         * 役職一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1022" );

            $position = new position();

            $laborRegulationsID = parent::escStr( $_POST['laborRegulationsId'] );
            if( !is_numeric($laborRegulationsID) )
            {
                $laborRegulationsID = 0;
            }

            $postArray = array(
                'positionCode'        => parent::escStr( $_POST['positionCode'] ),
                'organizationID'      => parent::escStr( $_POST['organizationId'] ),
                'positionName'        => parent::escStr( $_POST['positionName'] ),
                'laborRegulationsID'  => $laborRegulationsID,
                'is_del'              => 0,
                'dispOrder'           => parent::escStr( $_POST['dispOrder'] ),
                'user_id'             => $_SESSION["USER_ID"],
                'organization'        => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $position->addNewData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }

        /**
         * 役職一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1023" );

            $position = new position();

            $laborRegulationsID = parent::escStr( $_POST['laborRegulationsId'] );
            if( !is_numeric($laborRegulationsID) )
            {
                $laborRegulationsID = 0;
            }

            $postArray = array(
                'positionID'          => parent::escStr( $_POST['positionID'] ),
                'positionCode'        => parent::escStr( $_POST['positionCode'] ),
                'organizationID'      => parent::escStr( $_POST['organizationId'] ),
                'positionName'        => parent::escStr( $_POST['positionName'] ),
                'laborRegulationsID'  => $laborRegulationsID,
                'dispOrder'           => parent::escStr( $_POST['dispOrder'] ),
                'updateTime'          => parent::escStr( $_POST['updateTime'] ),
                'user_id'             => $_SESSION["USER_ID"],
                'organization'        => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $position->modUpdateData($postArray);
            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * 役職一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1024" );

            $position = new position();

            $postArray = array(
                'positionID'      => parent::escStr( $_POST['positionID'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'is_del'         => 1,
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $position->delUpdateData($postArray);

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
            $Log->info( "MSG_INFO_1025" );

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            $searchArray = array(
                'is_del'               => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'codeID'               => parent::escStr( $_POST['searchCodeName'] ),
                'organizationID'       => parent::escStr( $_POST['searchOrganizatioID'] ),
                'positionName'         => parent::escStr( $_POST['searchPositionName'] ),
                'laborRegulationsID'   => parent::escStr( $_POST['searchLaborRegulationsID'] ),
                'sort'                 => parent::escStr( $_POST['sortConditions'] ),
            );
            //--------------------------------------------------------------->
            $position = new position();

            // 登録用組織プルダウン
            $abbreviatedList = $position->setPulldown->getSearchOrganizationList( 'registration', true, true );
            // 登録用就業規則プルダウン
            $laborRegulationsList = $position->setPulldown->getSearchLaborRegulationsList( 'registration' );

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 役職一覧データ取得
            $positionAllList = $position->getListData($searchArray);

            // 役職一覧レコード数
            $positionRecordCnt = count($positionAllList);

            // 表示レコード数
            $pagedRecordCnt = parent::escStr( $_POST['displayRecordCnt'] );

            // 指定ページ
            $pageNo = parent::escStr( $_POST['displayPageNo'] );

            // シーケンスIDName
            $idName = "position_id";

            $positionList = $this->refineListDisplayNoSpecifiedPage($positionAllList, $idName, $pagedRecordCnt, $pageNo);
            $positionList = $this->modBtnDelBtnDisabledCheck($positionList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($positionList) >= 15)
            {
                $isScrollBar = true;
            }

            $regFlag = count( $abbreviatedList ) -1;
            //--------------------------------------------------------------->

            $Log->trace("END   inputAreaAction");
            
            require_once './View/PositionInputPanel.html';
        }
        
        /**
         * 検索用役職リストの更新
         * @note     役職リストを更新した場合、検索用のリストも更新する
         * @return   検索用役職リスト
         */
        public function searchPositionNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchPositionNameAction");
            $Log->info( "MSG_INFO_1026" );

            $position = new position();

            $positionNameList = $position->setPulldown->getSearchPositionList( 'reference' );               // 役職リスト

            $searchArray['positionName'] = parent::escStr( $_POST['searchPositionName'] );

            $Log->trace("END   searchPositionNameAction");
        
            require_once './View/Common/SearchPositionName.php';
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
            $Log->info( "MSG_INFO_1027" );

            $position = new position();

            $codeList = $position->getSearchCodeList();                                               // コードリスト

            $searchArray['codeID'] = parent::escStr( $_POST['searchCodeName'] );

            $Log->trace("END   searchCodeNameAction");
            
            require_once '../FwCommon/View/SearchCodeName.php';
        }


        /**
         * 役職一覧画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $position = new position();

            // 検索プルダウン
            $codeList                 = $position->getSearchCodeList();                                               // コードリスト
            $abbreviatedNameList      = $position->setPulldown->getSearchOrganizationList( 'reference', true, true ); // 組織略称名リスト
            $positionNameList         = $position->setPulldown->getSearchPositionList( 'reference' );                 // 役職名リスト
            $laborRegNameList         = $position->setPulldown->getSearchLaborRegulationsList( 'reference' );         // 就業規則リスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;

            $searchArray = array(
                'is_del'              => 0,
                'codeID'              => '',
                'organizationID'      => '',
                'positionName'        => '',
                'laborRegulationsID'  => '',
                'sort'                => '',
            );

            // 役職一覧データ取得
            $positionAllList = $position->getListData($searchArray);

            // 役職一覧レコード数
            $positionRecordCnt = count($positionAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($positionRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "position_id";

            $positionList = $this->refineListDisplayNoSpecifiedPage($positionAllList, $idName, $pagedRecordCnt, $pageNo);
            $positionList = $this->modBtnDelBtnDisabledCheck($positionList);

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($positionList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($positionRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 登録用組織プルダウン
            $abbreviatedList = $position->setPulldown->getSearchOrganizationList( 'registration', true, true );
            $regFlag = count( $abbreviatedList ) -1;

            // 登録用就業規則プルダウン
            $laborRegulationsList = $position->setPulldown->getSearchLaborRegulationsList( 'registration' );

            $headerArray = $this->changeHeaderItemMark(0);

            $positionNoSortFlag = false;

            $position_no = 0;
            $display_no =0;

            $headerArray = $this->changeHeaderItemMark(0);

            $positionNoSortFlag = false;

            $position_no = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/PositionPanel.html';
        }

        /**
         * 役職一覧更新
         * @note     パラメータは、View側で使用
         * @param    役職リスト(組織ID/役職ID)
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
                'positionName'         => parent::escStr( $_POST['searchPositionName'] ),
                'laborRegulationsID'   => parent::escStr( $_POST['searchLaborRegulationsID'] ),
                'sort'                 => parent::escStr( $_POST['sortConditions'] ),
            );
            $position = new position();

            // シーケンスIDName
            $idName = "position_id";

            // 役職一覧データ取得
            $positionSearchList = $position->getListData($searchArray);
            
            // 役職一覧検索後のレコード数
            $positionSrcListCnt = count($positionSearchList);
            // 役職一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後の役職レコードに対するページ数
            $pagedCnt = ceil($positionSrcListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データ役職IDを取得
                $positionLastId = $position->getLastid();
                $this->pageNoWhenUpdating($positionSearchList, $idName, $positionLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $positionList = $this->refineListDisplayNoSpecifiedPage($positionSearchList, $idName, $pagedRecordCnt, $pageNo);
            $positionList = $this->modBtnDelBtnDisabledCheck($positionList);
            
            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($positionList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->
            
            // 表示レコード数
            $positionListCount = count($positionList);

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // 登録用組織プルダウン
            $abbreviatedList = $position->setPulldown->getSearchOrganizationList( 'registration', true, true );
            $regFlag = count( $abbreviatedList ) -1;
            
            // 登録用就業規則プルダウン
            $laborRegulationsList = $position->setPulldown->getSearchLaborRegulationsList( 'registration' );

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $positionNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $position_no = 0;

            $display_no = $this->getDisplayNo( $positionSrcListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/PositionTablePanel.html';
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
         * @param    ソート条件番号(「No」押下時：1/「状態」押下時：3/「コード」押下時：5/「組織名」押下時：7/「役職名」押下時：9/「就業規則」押下時：11/「表示順」押下時：13)
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        private function changeHeaderItemMark($sortNo)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeHeaderItemMark");
            
            // 初期化
            $headerArray = array(
                    'positionNoSortMark'               => '',
                    'positionStateSortMark'            => '',
                    'positionCodeSortMark'             => '',
                    'positionOrganizationSortMark'     => '',
                    'positionNameSortMark'             => '',
                    'positionLaborRegulationsSortMark' => '',
                    'positionDispOrderSortMark'        => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1] = "positionNoSortMark";
                $sortList[2] = "positionNoSortMark";
                $sortList[3] = "positionStateSortMark";
                $sortList[4] = "positionStateSortMark";
                $sortList[5] = "positionCodeSortMark";
                $sortList[6] = "positionCodeSortMark";
                $sortList[7] = "positionOrganizationSortMark";
                $sortList[8] = "positionOrganizationSortMark";
                $sortList[9] = "positionNameSortMark";
                $sortList[10] = "positionNameSortMark";
                $sortList[11] = "positionLaborRegulationsSortMark";
                $sortList[12] = "positionLaborRegulationsSortMark";
                $sortList[13] = "positionDispOrderSortMark";
                $sortList[14] = "positionDispOrderSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }
            
            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
            
        }

    }
?>
