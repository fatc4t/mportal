<?php
    /**
     * @file      手当コントローラ
     * @author    USE S.Kasai
     * @date      2016/06/15
     * @version   1.00
     * @note      手当の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 手当処理モデル
    require './Model/Allowance.php';

    /**
     * 手当コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class AllowanceController extends BaseController
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
         * 手当一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1070" );
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }

        /**
         * 手当一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            $Log->info( "MSG_INFO_1071" );

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
         * 手当一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1072" );

            $allowance = new allowance();

            $postArray = array(
                'allowanceCode'             => parent::escStr( $_POST['allowanceCode'] ),
                'organizationID'            => parent::escStr( $_POST['organizationId'] ),
                'allowanceName'             => parent::escStr( $_POST['allowanceName'] ),
                'wageFormTypeID'            => parent::escStr( $_POST['wageFormTypeId'] ),
                'paymentConditionsID'       => parent::escStr( $_POST['paymentConditionsId'] ),
                'paymentConditionsHours'    => $this->changeMinuteFromTime( parent::escStr( $_POST['paymentConditionsDetailTime'] ) ),
                'paymentConditionsDays'     => parent::escStr( $_POST['paymentConditionsDetail'] ),
                'comment'                   => parent::escStr( $_POST['comment'] ),
                'is_del'                    => 0,
                'dispOrder'                 => parent::escStr( $_POST['dispOrder'] ),
                'user_id'                   => $_SESSION["USER_ID"],
                'organization'              => $_SESSION["ORGANIZATION_ID"],
            );
            
            if( $postArray['paymentConditionsID'] === '2' )
            {
                $postArray['paymentConditionsDays'] = 0 ;
            }
            else if( $postArray['paymentConditionsID'] === '3' )
            {
                $postArray['paymentConditionsHours'] = 0 ;
            }
            else
            {
                $postArray['paymentConditionsHours'] = 0 ;
                $postArray['paymentConditionsDays'] = 0 ;
            }

            $messege = $allowance->addNewData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }

        /**
         * 手当一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1073" );

            $allowance = new allowance();

            $postArray = array(
                'allowanceID'               => parent::escStr( $_POST['allowanceID'] ),
                'allowanceCode'             => parent::escStr( $_POST['allowanceCode'] ),
                'organizationID'            => parent::escStr( $_POST['organizationId'] ),
                'allowanceName'             => parent::escStr( $_POST['allowanceName'] ),
                'wageFormTypeID'            => parent::escStr( $_POST['wageFormTypeId'] ),
                'paymentConditionsID'       => parent::escStr( $_POST['paymentConditionsId'] ),
                'paymentConditionsHours'    => $this->changeMinuteFromTime( parent::escStr( $_POST['paymentConditionsDetailTime'] ) ),
                'paymentConditionsDays'     => parent::escStr( $_POST['paymentConditionsDetail'] ),
                'comment'                   => parent::escStr( $_POST['comment'] ),
                'dispOrder'                 => parent::escStr( $_POST['dispOrder'] ),
                'updateTime'                => parent::escStr( $_POST['updateTime'] ),
                'user_id'                   => $_SESSION["USER_ID"],
                'organization'              => $_SESSION["ORGANIZATION_ID"],
            );
            
            if( $postArray['paymentConditionsID'] === '2' )
            {
                $postArray['paymentConditionsDays'] = 0 ;
            }
            else if( $postArray['paymentConditionsID'] === '3' )
            {
                $postArray['paymentConditionsHours'] = 0 ;
            }
            else
            {
                $postArray['paymentConditionsHours'] = 0 ;
                $postArray['paymentConditionsDays'] = 0 ;
            }
            
            $messege = $allowance->modUpdateData($postArray);
            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * 手当一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1074" );

            $allowance = new allowance();

            $postArray = array(
                'allowanceID'    => parent::escStr( $_POST['allowanceID'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'is_del'         => 1,
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $allowance->delUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   delAction");
        }
        
        /**
         * 新規登録用エリアの更新
         * @note     手当マスタを新規作成した場合、新規登録用エリアを更新する
         * @return   新規登録用エリア
         */
        public function inputAreaAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1075" );

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            $searchArray = array(
                'is_del'                    => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'codeID'                    => parent::escStr( $_POST['searchCodeName'] ),
                'organizationID'            => parent::escStr( $_POST['searchOrganizatioID'] ),
                'allowanceName'             => parent::escStr( $_POST['searchAllowanceName'] ),
                'wageFormTypeID'            => parent::escStr( $_POST['searchWageFormTypeID'] ),
                'paymentConditionsID'       => parent::escStr( $_POST['searchPayCndID'] ),
                'comment'                   => parent::escStr( $_POST['searchComment'] ),
                'sort'                      => parent::escStr( $_POST['sortConditions'] ),
            );
            //--------------------------------------------------------------->
            $allowance = new allowance();

            // 登録用組織プルダウン
            $abbreviatedList = $allowance->setPulldown->getSearchOrganizationList( 'registration', true );
            // 登録用支給単位プルダウン
            $wageTypeList = $allowance->setPulldown->getSearchWageTypeList();
            // 登録用支給条件プルダウン
            $payCndList = $allowance->setPulldown->getSearchPaymentConditionsList();
            $regFlag = count( $abbreviatedList ) -1;

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // シーケンスIDName
            $idName = "allowance_id";

            // 手当一覧データ取得
            $allowanceSearchList = $allowance->getListData($searchArray);
            
            // 手当一覧検索後のレコード数
            $allowanceSrcListCnt = count($allowanceSearchList);
            // 手当一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後の手当レコードに対するページ数
            $pagedCnt = ceil($allowanceSrcListCnt /  $pagedRecordCnt);
            
            // 指定ページ
            $pageNo = parent::escStr( $_POST['displayPageNo'] );

            // ページ部分に対応させたリスト
            $allowanceList = $this->refineListDisplayNoSpecifiedPage($allowanceSearchList, $idName, $pagedRecordCnt, $pageNo);
            $allowanceList = $this->modBtnDelBtnDisabledCheck($allowanceList);
            // 表示レコード数
            $allowanceListCount = count($allowanceList);

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($allowanceList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->

            $Log->trace("END   inputAreaAction");
            
            require_once './View/AllowanceInputPanel.html';
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
            $Log->info( "MSG_INFO_1076" );

            $allowance = new allowance();

            $codeList = $allowance->getSearchCodeList();                                               // コードリスト

            $searchArray['codeID'] = parent::escStr( $_POST['searchCodeName'] );

            $Log->trace("END   searchCodeNameAction");
            
            require_once '../FwCommon/View/SearchCodeName.php';
        }
        
        /**
         * 検索用手当リストの更新
         * @note     手当リストを更新した場合、検索用のリストも更新する
         * @return   検索用手当リスト
         */
        public function searchAllowanceNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchAllowanceNameAction");
            $Log->info( "MSG_INFO_1077" );

            $allowance = new allowance();

            $allowanceNameList = $allowance->setPulldown->getSearchAllowanceList( 'reference' );             // 手当リスト

            $searchArray['allowanceName'] = parent::escStr( $_POST['searchAllowanceName'] );

            $Log->trace("END   searchAllowanceNameAction");
        
            require_once './View/Common/SearchAllowanceName.php';
        }
        
        /**
         * 新規登録時検索用支給条件リストの制限
         * @note     支給単位リストを設定した場合、紐付く支給条件リストに制限する
         * @return   検索用支給条件リスト
         */
        public function searchPaymentConditionsNameEditAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchPaymentConditionsNameEditAction");
            $Log->info( "MSG_INFO_1078" );

            $allowance = new allowance();
            $searchPayCndID = parent::escStr( $_POST['searchPayCndID'] );
            $payCndList = $allowance->getSearchPaymentConditionsEditList( $searchPayCndID );               // 支給条件リスト

            $searchArray['paymentConditionsID'] = $searchPayCndID;

            $Log->trace("END   searchPaymentConditionsNameEditAction");
        
            require_once './View/Common/SearchPaymentConditionsNameEdit.php';
        }
        
        /**
         * 編集登録時検索用支給条件リストの制限
         * @note     支給単位リストを設定した場合、紐付く支給条件リストに制限する
         * @return   検索用支給条件リスト
         */
        public function searchPaymentConditionsModNameEditAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchPaymentConditionsModNameEditAction");
            $Log->info( "MSG_INFO_1079" );

            $allowanceModel = new allowance();
            
            $searchPayCndID = parent::escStr( $_POST['searchPayCndID'] );
            $payCndList = $allowanceModel->getSearchPaymentConditionsEditList( $searchPayCndID );               // 支給条件リスト
            
            $allowance['payment_conditions_id'] = parent::escStr( $_POST['searchPaymentConditionsModID'] );

            $allowance_no = parent::escStr( $_POST['rowId'] );
            $Log->trace("END   searchPaymentConditionsModNameEditAction");

            require_once './View/Common/SearchPaymentConditionsModNameEdit.php';
        }


        /**
         * 手当一覧画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $allowance = new allowance();

            // 検索プルダウン
            $codeList                 = $allowance->getSearchCodeList();                                            // コードリスト
            $abbreviatedNameList      = $allowance->setPulldown->getSearchOrganizationList( 'reference', true );    // 組織略称名リスト
            $allowanceNameList        = $allowance->setPulldown->getSearchAllowanceList( 'reference' );             // 手当リスト
            $wageTypeNameList         = $allowance->setPulldown->getSearchWageTypeList();                           // 支給単位リスト
            $payCndNameList           = $allowance->setPulldown->getSearchPaymentConditionsList();                  // 支給条件リスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;

            $searchArray = array(
                'is_del'              => 0,
                'codeID'              => '',
                'organizationID'      => '',
                'allowanceName'       => '',
                'wageFormTypeID'      => '',
                'paymentConditionsID' => '',
                'workingHours'        => '',
                'workingDays'         => '',
                'comment'             => '',
                'sort'                => '',
            );

            // 手当一覧データ取得
            $allowanceAllList = $allowance->getListData($searchArray);

            // 手当一覧レコード数
            $allowanceRecordCnt = count($allowanceAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($allowanceRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "allowance_id";

            $allowanceList = $this->refineListDisplayNoSpecifiedPage($allowanceAllList, $idName, $pagedRecordCnt, $pageNo);
            $allowanceList = $this->modBtnDelBtnDisabledCheck($allowanceList);

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($allowanceList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($allowanceRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 登録用組織プルダウン
            $abbreviatedList = $allowance->setPulldown->getSearchOrganizationList( 'registration', true );
            $regFlag = count( $abbreviatedList ) -1;

            $headerArray = $this->changeHeaderItemMark(0);

            $allowanceNoSortFlag = false;

            $allowance_no = 0;
            $display_no =0;
            
            // 登録用支給単位プルダウン
            $wageTypeList = $allowance->setPulldown->getSearchWageTypeList();
            $regFlag = count( $wageTypeList ) -1;

            $headerArray = $this->changeHeaderItemMark(0);

            $allowanceNoSortFlag = false;

            $allowance_no = 0;
            $display_no =0;
            
            // 登録用支給条件プルダウン
            $payCndList = $allowance->setPulldown->getSearchPaymentConditionsList();
            $regFlag = count( $payCndList ) -1;
            $headerArray = $this->changeHeaderItemMark(0);

            $allowanceNoSortFlag = false;

            $allowance_no = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/AllowancePanel.html';
        }

        /**
         * 手当一覧更新
         * @note     パラメータは、View側で使用
         * @param    手当リスト(組織ID/手当ID)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $searchArray = array(
                'is_del'                    => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'codeID'                    => parent::escStr( $_POST['searchCodeName'] ),
                'organizationID'            => parent::escStr( $_POST['searchOrganizatioID'] ),
                'allowanceName'             => parent::escStr( $_POST['searchAllowanceName'] ),
                'wageFormTypeID'            => parent::escStr( $_POST['searchWageFormTypeID'] ),
                'paymentConditionsID'       => parent::escStr( $_POST['searchPayCndID'] ),
                'comment'                   => parent::escStr( $_POST['searchComment'] ),
                'sort'                      => parent::escStr( $_POST['sortConditions'] ),
            );
            $allowance = new allowance();

            // シーケンスIDName
            $idName = "allowance_id";

            // 手当一覧データ取得
            $allowanceSearchList = $allowance->getListData($searchArray);
            
            // 手当一覧検索後のレコード数
            $allowanceSrcListCnt = count($allowanceSearchList);
            // 手当一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後の手当レコードに対するページ数
            $pagedCnt = ceil($allowanceSrcListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データ手当IDを取得
                $allowanceLastId = $allowance->getLastid();
                $this->pageNoWhenUpdating($allowanceSearchList, $idName, $allowanceLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $allowanceList = $this->refineListDisplayNoSpecifiedPage($allowanceSearchList, $idName, $pagedRecordCnt, $pageNo);
            $allowanceList = $this->modBtnDelBtnDisabledCheck($allowanceList);
            // 表示レコード数
            $allowanceListCount = count($allowanceList);

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($allowanceList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // 登録用組織プルダウン
            $abbreviatedList = $allowance->setPulldown->getSearchOrganizationList( 'registration', true );
            $regFlag = count( $abbreviatedList ) -1;
            
            // 登録用支給単位プルダウン
            $wageTypeList = $allowance->setPulldown->getSearchWageTypeList();
            $regFlag = count( $wageTypeList ) -1;
            
            // 登録用支給条件プルダウン
            $payCndList = $allowance->setPulldown->getSearchPaymentConditionsList();
            $regFlag = count( $payCndList ) -1;

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $allowanceNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $allowance_no = 0;

            $display_no = $this->getDisplayNo( $allowanceSrcListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/AllowanceTablePanel.html';
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
         * @param    ソート条件番号(「No」押下時：1/「状態」押下時：3/「コード」押下時：5/「組織名」押下時：7/「手当名」押下時：9/「就業規則」押下時：11/「表示順」押下時：13)
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        private function changeHeaderItemMark($sortNo)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeHeaderItemMark");
            
            // 初期化
            $headerArray = array(
                    'allowanceNoSortMark'                        => '',
                    'allowanceStateSortMark'                     => '',
                    'allowanceCodeSortMark'                      => '',
                    'allowanceOrganizationSortMark'              => '',
                    'allowanceNameSortMark'                      => '',
                    'allowanceWageFormTypeSortMark'              => '',
                    'allowancePaymentConditionsSortMark'         => '',
                    'allowancePaymentConditionsDetailSortMark'   => '',
                    'allowanceCommentSortMark'                   => '',
                    'allowanceDispOrderSortMark'                 => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1] = "allowanceNoSortMark";
                $sortList[2] = "allowanceNoSortMark";
                $sortList[3] = "allowanceStateSortMark";
                $sortList[4] = "allowanceStateSortMark";
                $sortList[5] = "allowanceCodeSortMark";
                $sortList[6] = "allowanceCodeSortMark";
                $sortList[7] = "allowanceOrganizationSortMark";
                $sortList[8] = "allowanceOrganizationSortMark";
                $sortList[9] = "allowanceNameSortMark";
                $sortList[10] = "allowanceNameSortMark";
                $sortList[11] = "allowanceWageFormTypeSortMark";
                $sortList[12] = "allowanceWageFormTypeSortMark";
                $sortList[13] = "allowancePaymentConditionsSortMark";
                $sortList[14] = "allowancePaymentConditionsSortMark";
                $sortList[15] = "allowancePaymentConditionsDetailSortMark";
                $sortList[16] = "allowancePaymentConditionsDetailSortMark";
                $sortList[17] = "allowanceCommentSortMark";
                $sortList[18] = "allowanceCommentSortMark";
                $sortList[19] = "allowanceDispOrderSortMark";
                $sortList[20] = "allowanceDispOrderSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }
            
            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
