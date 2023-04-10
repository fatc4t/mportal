<?php
    /**
     * @file      アクセス権限コントローラ
     * @author    USE Y.Sakata
     * @date      2016/07/01
     * @version   1.00
     * @note      アクセス権限の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // アクセス権限処理モデル
    require './Model/AccessAuthority.php';

    /**
     * アクセス権限コントローラクラス
     * @note   アクセス権限の新規登録/修正/削除を行う
     */
    class AccessAuthorityController extends BaseController
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
         * アクセス権限一覧画面初期表示
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
         * アクセス権限一覧画面検索
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
         * アクセス権限一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1042" );
            
            $postArray = array(
                'accessAuthorityID' => parent::escStr( $_POST['accessAuthorityID'] ),
                'functionID'        => parent::escStr( $_POST['functionID'] ),
                'screenName'        => parent::escStr( $_POST['screenName'] ),
                'url'               => parent::escStr( $_POST['url'] ),
                'comment'           => parent::escStr( $_POST['comment'] ),
                'reference'         => $this->isBoolToInt( parent::escStr( $_POST['reference'] ) ),
                'registration'      => $this->isBoolToInt( parent::escStr( $_POST['registration'] ) ),
                'delete'            => $this->isBoolToInt( parent::escStr( $_POST['delete'] ) ),
                'approval'          => $this->isBoolToInt( parent::escStr( $_POST['approval'] ) ),
                'printing'          => $this->isBoolToInt( parent::escStr( $_POST['printing'] ) ),
                'output'            => $this->isBoolToInt( parent::escStr( $_POST['output'] ) ),
                'dispOrder'         => parent::escStr( $_POST['dispOrder'] ),
                'user_id'           => $_SESSION["USER_ID"],
            );

            $accessAuthority = new AccessAuthority();
            $messege = $accessAuthority->addNewData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }

        /**
         * アクセス権限一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1043" );

            $postArray = array(
                'accessAuthorityID' => parent::escStr( $_POST['accessAuthorityID'] ),
                'functionID'        => parent::escStr( $_POST['functionID'] ),
                'screenName'        => parent::escStr( $_POST['screenName'] ),
                'url'               => parent::escStr( $_POST['url'] ),
                'comment'           => parent::escStr( $_POST['comment'] ),
                'reference'         => $this->isBoolToInt( parent::escStr( $_POST['reference'] ) ),
                'registration'      => $this->isBoolToInt( parent::escStr( $_POST['registration'] ) ),
                'delete'            => $this->isBoolToInt( parent::escStr( $_POST['delete'] ) ),
                'approval'          => $this->isBoolToInt( parent::escStr( $_POST['approval'] ) ),
                'printing'          => $this->isBoolToInt( parent::escStr( $_POST['printing'] ) ),
                'output'            => $this->isBoolToInt( parent::escStr( $_POST['output'] ) ),
                'dispOrder'         => parent::escStr( $_POST['dispOrder'] ),
                'updateTime'        => parent::escStr( $_POST['updateTime'] ),
                'user_id'           => $_SESSION["USER_ID"],
            );

            $accessAuthority = new AccessAuthority();
            $messege = $accessAuthority->modUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * アクセス権限一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1044" );

            $accessAuthority = new AccessAuthority();

            $postArray = array(
                'accessAuthorityID' => parent::escStr( $_POST['accessAuthorityID'] ),
                'updateTime'        => parent::escStr( $_POST['updateTime'] ),
                'isDel'             => 1,
                'user_id'           => $_SESSION["USER_ID"],
            );

            $messege = $accessAuthority->delUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   delAction");
        }

        /**
         * 新規登録用エリアの更新
         * @note     アクセス権限マスタを新規作成した場合、新規登録用エリアを更新する
         * @return   新規登録用エリア
         */
        public function inputAreaAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1045" );

            $accessAuthority = new AccessAuthority();
            $functionIdList  = $accessAuthority->getFunctionList();             // 機能IDプルダウン

            $searchArray = array(
                'isDel'                 => $this->isBoolToInt( parent::escStr( $_POST['searchDelF'] ) ),
                'accessAuthorityId'     => parent::escStr( $_POST['searchAccessAuthorityID'] ),
                'functionId'            => parent::escStr( $_POST['searchFunctionID'] ),
                'screenName'            => parent::escStr( $_POST['searchScreenName'] ),
                'url'                   => parent::escStr( $_POST['searchUrl'] ),
                'comment'               => parent::escStr( $_POST['searchComment'] ),
                'reference'             => $this->isBoolToInt( parent::escStr( $_POST['searchReferenceF'] ) ),
                'registration'          => $this->isBoolToInt( parent::escStr( $_POST['searchRegistrationF'] ) ),
                'delete'                => $this->isBoolToInt( parent::escStr( $_POST['searchDeleteF'] ) ),
                'approval'              => $this->isBoolToInt( parent::escStr( $_POST['searchApprovalF'] ) ),
                'printing'              => $this->isBoolToInt( parent::escStr( $_POST['searchPrintingF'] ) ),
                'output'                => $this->isBoolToInt( parent::escStr( $_POST['searchOutputF'] ) ),
                'sort'                  => parent::escStr( $_POST['sortConditions'] ),
            );

            // アクセス権限一覧データ取得
            $accessAllList = $accessAuthority->getListData($searchArray);

            // アクセス権限一覧レコード数
            $sectionRecordCnt = count($accessAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = parent::escStr( $_POST['displayPageNo'] );

            // シーケンスIDName
            $idName = "access_authority_id";

            $accessAuthorityList = $this->refineListDisplayNoSpecifiedPage($accessAllList, $idName, $pagedRecordCnt, $pageNo);
            $accessAuthorityList = $this->modBtnDelBtnDisabledCheck($accessAuthorityList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($accessAuthorityList) >= 15)
            {
                $isScrollBar = true;
            }

            $regFlag = $registration['registration'] > 5 ? 0 : 1;

            $Log->trace("END   inputAreaAction");
            
            require_once './View/AccessAuthorityInputPanel.html';
        }

        /**
         * 検索用アクセス権限リストの更新
         * @note     アクセス権限マスタを更新した場合、検索用のリストも更新する
         * @return   検索用アクセス権限リスト
         */
        public function searchAccessAuthorityIDAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchAccessAuthorityIDAction");
            $Log->info( "MSG_INFO_1046" );

            $accessAuthority = new AccessAuthority();
            $accessIdList    = $accessAuthority->getAccessAuthorityList();      // アクセス権限IDプルダウン

            $searchArray = array(
                'accessAuthorityId' => parent::escStr( $_POST['searchAccessAuthorityID'] ),
            );

            $Log->trace("END   searchAccessAuthorityIDAction");
            
            require_once './View/Common/SearchAccessAuthorityID.php';
        }

        /**
         * 検索用アクセス権限リストの更新
         * @note     アクセス権限マスタを更新した場合、検索用のリストも更新する
         * @return   検索用アクセス権限リスト
         */
        private function isBoolToInt( $bool )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START isBoolToInt");

            $ret = $bool === 'true' ? 1 : 0;

            $Log->trace("END   isBoolToInt");
            
            return $ret;
        }

        /**
         * アクセス権限一覧画面
         * @note     アクセス権限画面全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $accessAuthority = new AccessAuthority();

            // 検索プルダウン
            $accessIdList          = $accessAuthority->getAccessAuthorityList();      // アクセス権限IDプルダウン
            $functionIdList        = $accessAuthority->getFunctionList();             // 機能IDプルダウン

            $searchArray = array(
                'isDel'                 => 0,
                'accessAuthorityId'     => '',
                'functionId'            => '',
                'screenName'            => '',
                'url'                   => '',
                'comment'               => '',
                'reference'             => '',
                'registration'          => '',
                'delete'                => '',
                'approval'              => '',
                'printing'              => '',
                'output'                => '',
                'sort'                  => '',
            );

            // アクセス権限一覧データ取得
            $accessAllList = $accessAuthority->getListData($searchArray);

            // アクセス権限一覧レコード数
            $sectionRecordCnt = count($accessAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($sectionRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "access_authority_id";

            $accessAuthorityList = $this->refineListDisplayNoSpecifiedPage($accessAllList, $idName, $pagedRecordCnt, $pageNo);
            $accessAuthorityList = $this->modBtnDelBtnDisabledCheck($accessAuthorityList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($accessAuthorityList) >= 11)
            {
                $isScrollBar = true;
            }

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($sectionRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 登録権限の確認
            $securityProcess = new SecurityProcess();
            $registration = $securityProcess->getAccessAuthorityList();
            $regFlag = $registration['registration'] > 5 ? 0 : 1;

            $headerArray = $this->changeHeaderItemMark(0);

            $sectionNoSortFlag = false;

            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/AccessAuthorityPanel.html';
        }

        /**
         * アクセス権限一覧更新
         * @note     アクセス権限画面の一覧部分のみの更新
         * @param    $addFlag           新規登録フラグ true：新規登録  false：新規登録以外
         * @param    $messege           DBの更新結果(データ指定がない場合、デフォルト値[MSG_BASE_0000]を設定)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $searchArray = array(
                'isDel'                 => $this->isBoolToInt( parent::escStr( $_POST['searchDelF'] ) ),
                'accessAuthorityId'     => parent::escStr( $_POST['searchAccessAuthorityID'] ),
                'functionId'            => parent::escStr( $_POST['searchFunctionID'] ),
                'screenName'            => parent::escStr( $_POST['searchScreenName'] ),
                'url'                   => parent::escStr( $_POST['searchUrl'] ),
                'comment'               => parent::escStr( $_POST['searchComment'] ),
                'reference'             => $this->isBoolToInt( parent::escStr( $_POST['searchReferenceF'] ) ),
                'registration'          => $this->isBoolToInt( parent::escStr( $_POST['searchRegistrationF'] ) ),
                'delete'                => $this->isBoolToInt( parent::escStr( $_POST['searchDeleteF'] ) ),
                'approval'              => $this->isBoolToInt( parent::escStr( $_POST['searchApprovalF'] ) ),
                'printing'              => $this->isBoolToInt( parent::escStr( $_POST['searchPrintingF'] ) ),
                'output'                => $this->isBoolToInt( parent::escStr( $_POST['searchOutputF'] ) ),
                'sort'                  => parent::escStr( $_POST['sortConditions'] ),
            );

            $accessAuthority = new AccessAuthority();

            // アクセス権限一覧データ取得
            $searchList = $accessAuthority->getListData($searchArray);
            $functionIdList        = $accessAuthority->getFunctionList();             // 機能IDプルダウン
            
            // アクセス権限一覧検索後のレコード数
            $searchCnt = count($searchList);
            // アクセス権限一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
            // 検索結果後のアクセス権限レコードに対するページ数
            $pagedCnt = ceil($searchCnt /  $pagedRecordCnt);

            // シーケンスIDName
            $idName = "access_authority_id";
            if($addFlag)
            {
                // 新規追加後の最新データアクセス権限IDを取得
                $lastId = $accessAuthority->getLastid();
                $this->pageNoWhenUpdating($searchList, $idName, $lastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $accessAuthorityList = $this->refineListDisplayNoSpecifiedPage($searchList, $idName, $pagedRecordCnt, $pageNo);
            $accessAuthorityList = $this->modBtnDelBtnDisabledCheck($accessAuthorityList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($accessAuthorityList) >= 11)
            {
                $isScrollBar = true;
            }


            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $sectionNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;
            
            $display_no = $this->getDisplayNo( $searchCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/AccessAuthorityTablePanel.html';
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
                                    'noSortMark'                => '',
                                    'stateSortMark'             => '',
                                    'accessAuthoritySortMark'   => '',
                                    'functionSortMark'          => '',
                                    'screenNameSortMark'        => '',
                                    'urlSortMark'               => '',
                                    'commentSortMark'           => '',
                                    'referenceSortMark'         => '',
                                    'registrationSortMark'      => '',
                                    'deleteSortMark'            => '',
                                    'approvalSortMark'          => '',
                                    'printingSortMark'          => '',
                                    'outputSortMark'            => '',
                                    'dispOrderSortMark'         => '',
                            );

            if(!empty($sortNo))
            {
                $sortList[1] = "noSortMark";
                $sortList[2] = "noSortMark";
                $sortList[3] = "stateSortMark";
                $sortList[4] = "stateSortMark";
                $sortList[5] = "accessAuthoritySortMark";
                $sortList[6] = "accessAuthoritySortMark";
                $sortList[7] = "functionSortMark";
                $sortList[8] = "functionSortMark";
                $sortList[9] = "screenNameSortMark";
                $sortList[10] = "screenNameSortMark";
                $sortList[11] = "urlSortMark";
                $sortList[12] = "urlSortMark";
                $sortList[13] = "commentSortMark";
                $sortList[14] = "commentSortMark";
                $sortList[15] = "referenceSortMark";
                $sortList[16] = "referenceSortMark";
                $sortList[17] = "registrationSortMark";
                $sortList[18] = "registrationSortMark";
                $sortList[19] = "deleteSortMark";
                $sortList[20] = "deleteSortMark";
                $sortList[21] = "approvalSortMark";
                $sortList[22] = "approvalSortMark";
                $sortList[23] = "printingSortMark";
                $sortList[24] = "printingSortMark";
                $sortList[25] = "outputSortMark";
                $sortList[26] = "outputSortMark";
                $sortList[27] = "dispOrderSortMark";
                $sortList[28] = "dispOrderSortMark";
                
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
