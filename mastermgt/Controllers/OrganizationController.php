<?php
    /**
     * @file      組織コントローラ
     * @author    USE R.dendo
     * @date      2016/7/11
     * @version   1.00
     * @note      組織の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 組織処理モデル
    require './Model/Organization.php';

    /**
     * 組織コントローラクラス
     * @note   組織の新規登録/修正/削除を行う
     */
    class OrganizationController extends BaseController
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
         * 組織一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1010" );
            
            $startFlag = true;
            if(isset($_POST['back']))
            {
                $startFlag = false;
            }
            
            $this->initialDisplay($startFlag);
            $Log->trace("END   showAction");
        }

        /**
         * 組織一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            $Log->info( "MSG_INFO_1011" );

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
         * 組織一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1012" );

            $organization = new organization();

            $checkPriority_o = $this->priorityCheck(1);
            $checkPriority_p = $this->priorityCheck(2);
            $checkPriority_e = $this->priorityCheck(3);

            // 上位組織ID
            $upOrganID = parent::escStr( $_POST['upperLevelOrganization'] );
            $upOrganID = $upOrganID  === "" ? 0 : $upOrganID;

            // 給与連携マスタ
            $payrollFormat = parent::escStr( $_POST['payrollFormat'] );
            $payrollFormat = $payrollFormat  === "" ? 0 : $payrollFormat;

            // 就業規則マスタ
            $laborRegulationsName = parent::escStr( $_POST['laborRegulationsName'] );
            $laborRegulationsName = $laborRegulationsName  === "" ? 0 : $laborRegulationsName;

            $postArray = array(
                'departmentCode'                  => parent::escStr( $_POST['departmentCode'] ),
                'organizationId'                  => parent::escStr( $_POST['organizationId'] ),
                'organizationName'                => parent::escStr( $_POST['organizationName'] ),
                'abbreviatedName'                 => parent::escStr( $_POST['abbreviatedName'] ),
                'applicationDateStart'            => parent::escStr( $_POST['applicationDateStart'] ),
                'startTimeDay'                    => parent::escStr( $_POST['startTimeDay'] ),
                'priority_o'                      => $checkPriority_o,
                'priority_p'                      => $checkPriority_p,
                'priority_e'                      => $checkPriority_e,
                'payrollFormat'                   => $payrollFormat,
                'laborRegulationsName'            => $laborRegulationsName,
                'upperLevelOrganization'          => $upOrganID,
                'authenticationKey'               => parent::escStr( $_POST['authenticationKey'] ),
                'comment'                         => parent::escStr( $_POST['comment'] ),
                'dispOrder'                       => parent::escStr( $_POST['dispOrder'] ),
                'is_del'                          => 0,
                'user_id'                         => $_SESSION["USER_ID"],
                'organization'                    => $_SESSION["ORGANIZATION_ID"],
            );
            $messege = $organization->modAddData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }

        /**
         * 組織一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1013" );

            $organization = new organization();

            $checkPriority_o = $this->priorityCheck(1);
            $checkPriority_p = $this->priorityCheck(2);
            $checkPriority_e = $this->priorityCheck(3);

            // 上位組織ID
            $upOrganID = parent::escStr( $_POST['upperLevelOrganization'] );
            $upOrganID = $upOrganID  === "" ? 0 : $upOrganID;

            // 給与連携マスタ
            $payrollFormat = parent::escStr( $_POST['payrollFormat'] );
            $payrollFormat = $payrollFormat  === "" ? 0 : $payrollFormat;

            // 就業規則マスタ
            $laborRegulationsName = parent::escStr( $_POST['laborRegulationsName'] );
            $laborRegulationsName = $laborRegulationsName  === "" ? 0 : $laborRegulationsName;

            $postArray = array(
                'organizationID'                  => parent::escStr( $_POST['organizationID'] ),
                'organizationDetailID'            => parent::escStr( $_POST['organizationDetailID'] ),
                'departmentCode'                  => parent::escStr( $_POST['departmentCode'] ),
                'organizationName'                => parent::escStr( $_POST['organizationName'] ),
                'abbreviatedName'                 => parent::escStr( $_POST['abbreviatedName'] ),
                'applicationDateStart'            => parent::escStr( $_POST['applicationDateStart'] ),
                'startTimeDay'                    => parent::escStr( $_POST['startTimeDay'] ),
                'priority_o'                      => $checkPriority_o,
                'priority_p'                      => $checkPriority_p,
                'priority_e'                      => $checkPriority_e,
                'payrollFormat'                   => $payrollFormat,
                'laborRegulationsName'            => $laborRegulationsName,
                'upperLevelOrganization'          => $upOrganID,
                'authenticationKey'               => parent::escStr( $_POST['authenticationKey'] ),
                'comment'                         => parent::escStr( $_POST['comment'] ),
                'dispOrder'                       => parent::escStr( $_POST['dispOrder'] ),
                'updateTime'                      => parent::escStr( $_POST['updateTime'] ),
                'modUpdateTime'                   => parent::escStr( $_POST['modUpdateTime'] ),
                'user_id'                         => $_SESSION["USER_ID"],
                'organization'                    => $_SESSION["ORGANIZATION_ID"],
                'buttonName'                      => parent::escStr( $_POST['buttonName'] ),
            );

            $this->btnCheck($postArray,$organization);
            $Log->trace("END   modAction");
        }

        /**
         * 組織一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1014" );

            $organization = new organization();

            $postArray = array(
                'organizationID'                 => parent::escStr( $_POST['organizationID'] ),
                'modUpdateTime'                  => parent::escStr( $_POST['modUpdateTime'] ),
                'updateTime'                     => parent::escStr( $_POST['updateTime'] ),
                'applicationDateStart'           => parent::escStr( $_POST['applicationDateStart'] ),
                'is_del'                         => 1,
                'user_id'                        => $_SESSION["USER_ID"],
                'organization'                   => $_SESSION["ORGANIZATION_ID"],
            );
            $messege = $organization->delUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   delAction");
        }

        /**
         * 新規登録用エリアの更新
         * @note     組織マスタを新規作成した場合、新規登録用エリアを更新する
         * @return   新規登録用エリア
         */
        public function inputAreaAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1015" );

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            $searchArray = array(
                'is_del'                      => 0,
                'organizationID'              => parent::escStr( $_POST['searchOrganizationID'] ),
                'laborRegulationsID'          => parent::escStr( $_POST['searchLaborRegulationsName'] ),
                'departmentCode'              => parent::escStr( $_POST['searchDepartmentCode'] ),
                'startTimeDay'                => parent::escStr( $_POST['searchStartTimeDay'] ),
                'commentSearch'               => parent::escStr( $_POST['searchComment'] ),
                'payrollFormat'               => parent::escStr( $_POST['searchPayrollFormat'] ),
                'upperLevelOrganization'      => parent::escStr( $_POST['searchUpperLevelOrganization'] ),
                'sort'                        => parent::escStr( $_POST['sortConditions'] ),
            );
            //--------------------------------------------------------------->
            $organization = new organization();

            // 登録用給与フォーマットプルダウン
            $payrollList = $organization->setPulldown->getSearchPayrollList( 'registration' );
            // 登録用上位組織プルダウン
            $upOrganNameList = $organization->setPulldown->getSearchOrganizationList( 'registration', true );
            // 登録用就業規則優先順位プルダウン
            $laborRegNameAddList = $organization->setPulldown->getSearchLaborRegulationsList( 'registration' );

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 検索バー用チッェクボックスの状況を配列に入れる
            if(!empty($startFlag))
            {
                $effArray = array(1,0,0,0);
            }
            else
            {
                $effArray = array();
                $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchApplyCheck']);
                $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchApplySchCheck']);
                $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchNonApplyCheck']);
                $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchDelCheck']);
            }
            // チェックボックス状況の配列を2進数に直したうえで10進数にして返す
            $effFlag = $this->getStateFlag($effArray);

            // 組織一覧データ取得
            $organizationAllList = $organization->getListData($searchArray, $effFlag);

            // 組織一覧レコード数
            $organizationCnt = count($organizationAllList);

            // 表示レコード数
            $pagedRecordCnt = parent::escStr( $_POST['displayRecordCnt'] );

            // 指定ページ
            $pageNo = parent::escStr( $_POST['displayPageNo'] );

            // シーケンスIDName
            $idName = "organization_id";

            $organizationList = $this->refineListDisplayNoSpecifiedPage($organizationAllList, $idName, $pagedRecordCnt, $pageNo);
            $organizationList = $this->modBtnDelBtnDisabledCheck($organizationList);

            // 表示レコード数が7以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 7 && count($organizationList) >= 7 )
            {
                $isScrollBar = true;
            }
            
            $abbreviatedList = $organization->setPulldown->getSearchOrganizationList( 'registration', true );
            $regFlag = count( $abbreviatedList ) -1;
            //--------------------------------------------------------------->
            
            $Log->trace("END   inputAreaAction");
            
            require_once './View/OrganizationInputPanel.html';
        }

        /**
         * 検索組織名の更新
         * @note     検索組織名を更新する
         * @return   検索組織名エリア
         */
        public function searchOrganizationNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchOrganizationNameAction");
            $Log->info( "MSG_INFO_1016" );

            $organization = new organization();
            $abbreviatedNameList              = $organization->setPulldown->getSearchOrganizationList( 'reference', true );       // 組織略称名リスト

            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;

            $searchArray['organizationID'] = $_POST['searchOrganizationName'];
            $Log->trace("END   searchOrganizationNameAction");

            require_once '../FwCommon/View/SearchOrganizationName.php';
        }

        /**
         * 検索上位組織名の更新
         * @note     検索上位組織名を更新する
         * @return   検索上位組織名エリア
         */
        public function searchUpOrganizationNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchOrganizationNameAction");
            $Log->info( "MSG_INFO_1017" );

            $organization = new organization();
            $upOrganizationList              = $organization->setPulldown->getSearchOrganizationList( 'reference', true );       // 組織略称名リスト

            $searchArray['upOrganizationName'] = $_POST['upOrganizationName'];
            $Log->trace("END   searchOrganizationNameAction");

            require_once './View/Common/SearchUpOrganizationName.php';
        }

        /**
         * 組織一覧画面
         * @note     組織画面全てを更新
         * @return   無
         */
        private function initialDisplay($startFlag)
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $organization = new organization();

            // 検索プルダウン
            $abbreviatedNameList              = $organization->setPulldown->getSearchOrganizationList( 'reference', true );       // 組織略称名リスト
            $payrollFormat                    = $organization->setPulldown->getSearchPayrollList( 'reference' );                  // 給与フォーマットリスト
            $upOrganizationList               = $organization->setPulldown->getSearchOrganizationList( 'reference', true );       // 組織略称名リスト
            $laborRegNameList                 = $organization->setPulldown->getSearchLaborRegulationsList( 'reference' );         // 就業規則リスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;

            // 検索バー用チッェクボックスの状況を配列に入れる
            if(!empty($startFlag))
            {
                $effArray = array(1,0,0,0);
            }
            else
            {
                $effArray = array();
                $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchApplyCheck']);
                $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchApplySchCheck']);
                $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchNonApplyCheck']);
                $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchDelCheck']);
            }
            // チェックボックス状況の配列を2進数に直したうえで10進数にして返す
            $effFlag = $this->getStateFlag($effArray);

            $searchArray = array(
                'is_del'                      => 0,
                'organizationID'              => parent::escStr( $_POST['searchOrganizationID'] ),
                'laborRegulationsID'          => parent::escStr( $_POST['searchLaborRegulationsName'] ),
                'departmentCode'              => parent::escStr( $_POST['searchDepartmentCode'] ),
                'startTimeDay'                => parent::escStr( $_POST['searchStartTimeDay'] ),
                'commentSearch'               => parent::escStr( $_POST['searchComment'] ),
                'payrollFormat'               => parent::escStr( $_POST['searchPayrollFormat'] ),
                'upperLevelOrganization'      => parent::escStr( $_POST['searchUpperLevelOrganization'] ),
                'sort'                        => parent::escStr( $_POST['sortConditions'] ),
            );

            // 組織一覧データ取得
            $organizationAllList = $organization->getListData($searchArray, $effFlag);

            // 組織一覧レコード数
            $organizationCnt = count($organizationAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($organizationCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "organization_id";

            $organizationList = $this->refineListDisplayNoSpecifiedPage($organizationAllList, $idName, $pagedRecordCnt, $pageNo);
            $organizationList = $this->modBtnDelBtnDisabledCheck($organizationList);

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が7以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 7 && count($organizationList) >= 7 )
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($organizationCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 登録用給与フォーマットプルダウン
            $payrollList = $organization->setPulldown->getSearchPayrollList( 'registration' );
            // 登録用上位組織プルダウン
            $upOrganNameList = $organization->setPulldown->getSearchOrganizationList( 'registration', true );
            // 登録用就業規則優先順位プルダウン
            $laborRegNameAddList = $organization->setPulldown->getSearchLaborRegulationsList( 'registration' );

            $abbreviatedList = $organization->setPulldown->getSearchOrganizationList( 'registration', true );
            $regFlag = count( $abbreviatedList ) -1;

            $headerArray = $this->changeHeaderItemMark(0);

            $organizationNoFlag = false;

            $organization_no = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/OrganizationPanel.html';
        }

        /**
         * 組織一覧更新
         * @note     組織画面の一覧部分のみの更新
         * @param    $addFlag           新規登録フラグ true：新規登録  false：新規登録以外
         * @param    $messege           DBの更新結果(データ指定がない場合、デフォルト値[MSG_BASE_0000]を設定)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");
            
            $organization = new organization();

            // シーケンスIDName
            $idName = "organization_id";

            $searchArray = array(
                'is_del'                      => 0,
                'organizationID'              => parent::escStr( $_POST['searchOrganizationID'] ),
                'laborRegulationsName'        => parent::escStr( $_POST['searchLaborRegulationsName'] ),
                'departmentCode'              => parent::escStr( $_POST['searchDepartmentCode'] ),
                'startTimeDay'                => parent::escStr( $_POST['searchStartTimeDay'] ),
                'commentSearch'               => parent::escStr( $_POST['searchComment'] ),
                'sort'                        => parent::escStr( $_POST['sortConditions'] ),
                'payrollFormat'               => parent::escStr( $_POST['searchPayrollFormat'] ),
                'upperLevelOrganization'      => parent::escStr( $_POST['searchUpperLevelOrganization'] ),
            );

            // 適用状態の表示フラグ
            $effArray = array();
            $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchApplyCheck']);
            $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchApplySchCheck']);
            $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchNonApplyCheck']);
            $effArray = $this->booleanTypeChangeIntger($effArray, $_POST['searchDelCheck']);
            $effFlag = $this->getStateFlag($effArray);

            // 組織一覧データ取得
            $organizationList = $organization->getListData($searchArray, $effFlag);
            // 組織一覧検索後のレコード数
            $organizationListCnt = count($organizationList);
            // 組織一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後の組織レコードに対するページ数
            $pagedCnt = ceil($organizationListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データ組織IDを取得
                $organizationLastId = $organization->getLastid();
                $this->pageNoWhenUpdating($organizationList, $idName, $organizationLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $organizationList = $this->refineListDisplayNoSpecifiedPage($organizationList, $idName, $pagedRecordCnt, $pageNo);
            $organizationList = $this->modBtnDelBtnDisabledCheck($organizationList);

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が7以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 7 && count($organizationList) >= 7 )
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // 登録用給与フォーマットプルダウン
            $payrollList = $organization->setPulldown->getSearchPayrollList( 'registration' );
            // 登録用上位組織プルダウン
            $upOrganNameList = $organization->setPulldown->getSearchOrganizationList( 'registration', true );
            // 登録用就業規則優先順位プルダウン
            $laborRegNameAddList = $organization->setPulldown->getSearchLaborRegulationsList( 'registration' );

            $abbreviatedList = $organization->setPulldown->getSearchOrganizationList( 'registration', true );
            $regFlag = count( $abbreviatedList ) -1;

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $organizationNoFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $organization_no = 0;
            
            $display_no = $this->getDisplayNo( $organizationListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/OrganizationTablePanel.html';
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
            }
            $Log->trace("END   initialList");
        }

         /**
         * POSTで帰ってきた検索チェックボックスの値をboolean型から0/1へ変換する
         * @param    $checkArray
         * @param    $checkFlag
         * @return   $checkArray
         */
        private function booleanTypeChangeIntger($checkArray, $checkFlag )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START booleanTypeChangeIntger");

            array_push($checkArray, $checkFlag === 'true' ? 1 : 0);

            $Log->trace("END booleanTypeChangeIntger");

            return $checkArray;
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
                    'organizationNoSortMark'           => '',
                    'organizationStateSortMark'        => '',
                    'departmentCodeSortMark'           => '',
                    'organizationNameSortMark'         => '',
                    'abbreviatedNameSortMark'          => '',
                    'applicationDateStarSortMark'      => '',
                    'StartTimeDaySortMark'             => '',
                    'prioritySortMark'                 => '',
                    'laborRegulationsNameSortMark'     => '',
                    'payrollSystemNameSortMark'        => '',
                    'upOrganizationNameSortMark'       => '',
                    'authenticationKeySortMark'        => '',
                    'commentSortMark'                  => '',
                    'organizationDispOrderSortMark'    => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]  = "organizationNoSortMark";
                $sortList[2]  = "organizationNoSortMark";
                $sortList[3]  = "organizationStateSortMark";
                $sortList[4]  = "organizationStateSortMark";
                $sortList[5]  = "departmentCodeSortMark";
                $sortList[6]  = "departmentCodeSortMark";
                $sortList[7]  = "organizationNameSortMark";
                $sortList[8]  = "organizationNameSortMark";
                $sortList[9]  = "abbreviatedNameSortMark";
                $sortList[10] = "abbreviatedNameSortMark";
                $sortList[11] = "applicationDateStarSortMark";
                $sortList[12] = "applicationDateStarSortMark";
                $sortList[13] = "StartTimeDaySortMark";
                $sortList[14] = "StartTimeDaySortMark";
                $sortList[15] = "prioritySortMark";
                $sortList[16] = "prioritySortMark";
                $sortList[17] = "laborRegulationsNameSortMark";
                $sortList[18] = "laborRegulationsNameSortMark";
                $sortList[19] = "payrollSystemNameSortMark";
                $sortList[20] = "payrollSystemNameSortMark";
                $sortList[21] = "upOrganizationNameSortMark";
                $sortList[22] = "upOrganizationNameSortMark";
                $sortList[23] = "authenticationKeySortMark";
                $sortList[24] = "authenticationKeySortMark";
                $sortList[25] = "commentSortMark";
                $sortList[26] = "commentSortMark";
                $sortList[27] = "organizationDispOrderSortMark";
                $sortList[28] = "organizationDispOrderSortMark";
                $headerArray  = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
                }

            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

        /**
         * 就業規則優先順位をチェック
         * @note     POSTの値から優先順位を判定
         * @param    $priority 優先順位番号
         * @return   $ret 判定結果の番号
         */
        private function priorityCheck( $priority )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START priorityCheck");

            $ret = 0;
            if( parent::escStr( $_POST['priorityNameFirst'] ) == $priority )
            {
                $ret = 1;
            }
            else if( parent::escStr( $_POST['priorityNameSecond'] ) == $priority )
            {
                $ret = 2;
            }
            else if( parent::escStr( $_POST['priorityNameThird'] ) == $priority )
            {
                $ret = 3;
            }

            $Log->trace("END priorityCheck");
            return $ret;
        }

         /**
         * 遷移元ボタンをチェック
         * @note     POSTの値から遷移元ボタンを判定
         * @param    $priority 優先順位番号
         * @return   $ret 判定結果の番号
         */
        private function btnCheck( $postArray,$organization )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START btnCheck");
            
            if($postArray['buttonName']==1)
            {
                $messege = $organization->modUpdateData($postArray);
                $this->initialList( false, $messege);

            }else{
                $messege = $organization->modAddData($postArray);
                $this->initialList( true, $messege);
            }
            $Log->trace("END btnCheck");
        }

    }
?>
