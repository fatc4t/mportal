<?php
    /**
     * @file      顧客分類マスタコントローラ
     * @author    K.Sakamoto
     * @date      2017/07/25
     * @version   1.00
     * @note      顧客分類マスタの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 顧客分類マスタ処理モデル
    require './Model/CustomerClassification.php';

    /**
     * 顧客分類マスタコントローラクラス
     * @note   顧客分類の新規登録/修正/削除を行う
     */
    class CustomerClassificationController extends BaseController
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
         * 顧客分類一覧画面初期表示
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
         * 顧客分類一覧画面検索
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
         * 顧客分類一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1032" );

            $customerClassification = new CustomerClassification();

            $postArray = array(
                'custTypeCode'                  => parent::escStr( $_POST['custTypeCode'] ),
                'custTypeNm'                    => parent::escStr( $_POST['custTypeNm'] ),
                'disabled'                      => 0,
                'userID'                        => $_SESSION["USER_ID"],
                'organizationID'                => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $customerClassification->addNewData($postArray);
            $msg = $messege;

            if($msg === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialList( true, $msg);
           }
            else
            {
                // エラーメッセージを表示 "MSG_ERR_3083"
                $msg = $messege;
                echo( $Log->getMsgLog($msg) );
            }

            $Log->trace("END   addAction");
        }

        /**
         * 顧客分類一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1033" );

            $customerClassification = new CustomerClassification();

            $postArray = array(
                'custTypeId'                    => parent::escStr( $_POST['custTypeId'] ),
                'custTypeCode'                  => parent::escStr( $_POST['custTypeCode'] ),
                'custTypeNm'                    => parent::escStr( $_POST['custTypeNm'] ),
                'updateTime'                    => parent::escStr( $_POST['updateTime'] ),
                'userID'                        => $_SESSION["USER_ID"],
                'organizationID'                => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $customerClassification->modUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * 顧客分類一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1034" );

            $customerClassification = new CustomerClassification();

            $postArray = array(
                'custTypeId'                => parent::escStr( $_POST['custTypeId'] ),
                'updateTime'                => parent::escStr( $_POST['updateTime'] ),
                'disabled'                  => 9,
                'userID'                    => $_SESSION["USER_ID"],
                'organizationID'            => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $customerClassification->delUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   delAction");
        }

        /**
         * 新規登録用エリアの更新
         * @note     顧客分類マスタを新規作成した場合、新規登録用エリアを更新する
         * @return   新規登録用エリア
         */
        public function inputAreaAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1035" );

            $searchArray = array(
                'disabled'                          => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'codeID'                            => parent::escStr( $_POST['searchCodeName'] ),
                'custTypeNm'                        => parent::escStr( $_POST['searchCustomerClassificationName'] ),
                'sort'                              => parent::escStr( $_POST['sortConditions'] ),
            );

            $customerClassification = new CustomerClassification();

            // シーケンスIDName
            $idName = "cust_type_id";

            // 顧客分類一覧データ取得
            $customerClassificationSearchList = $customerClassification->getListData($searchArray);

            // 顧客分類一覧検索後のレコード数
            $customerClassificationSearchListCnt = count($customerClassificationSearchList);

            // 顧客分類一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後の顧客分類レコードに対するページ数
            $pagedCnt = ceil($customerClassificationSearchListCnt /  $pagedRecordCnt);
            
            // 指定ページ
            $pageNo = parent::escStr( $_POST['displayPageNo'] );

            // ページ部分に対応させたリスト
            $customerClassificationList = $this->refineListDisplayNoSpecifiedPage($customerClassificationSearchList, $idName, $pagedRecordCnt, $pageNo);
            $customerClassificationList = $this->modBtnDelBtnDisabledCheck($customerClassificationList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($customerClassificationList) >= 11 )
            {
                $isScrollBar = true;
            }

            $Log->trace("END   inputAreaAction");
            
            require_once './View/CustomerClassificationInputPanel.html';
        }

        /**
         * 検索用コードリストの更新
         * @note     顧客分類マスタを更新した場合、検索用のリストも更新する
         * @return   検索用コードリスト
         */
        public function searchCodeNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchCodeNameAction");
            $Log->info( "MSG_INFO_1036" );

            $customerClassification = new CustomerClassification();

            $codeList = $customerClassification->getSearchCodeList();

            $searchArray['codeID'] = parent::escStr( $_POST['searchCodeName'] );

            $Log->trace("END   searchCodeNameAction");
            
            require_once '../FwCommon/View/SearchCodeName.php';
        }

        /**
         * 検索用顧客分類リストの更新
         * @note     顧客分類マスタを更新した場合、検索用のリストも更新する
         * @return   検索用顧客分類リスト
         */
        public function searchCustomerClassificationNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchCustomerClassificationNameAction");
            $Log->info( "MSG_INFO_1037" );

            $customerClassification = new CustomerClassification();

            $custTypeNmList = $customerClassification->getSearchCustomerClassificationList();

            $searchArray['custTypeNm'] = parent::escStr( $_POST['searchCustomerClassificationName'] );

            $Log->trace("END   searchCustomerClassificationNameAction");
            
            require_once '../FwCommon/View/SearchCustomerClassificationName.php';
        }

        /**
         * 顧客分類一覧画面
         * @note     顧客分類画面全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $customerClassification = new CustomerClassification();

            // 検索プルダウン
            $codeList                           = $customerClassification->getSearchCodeList();
            $custTypeNmList                     = $customerClassification->getSearchCustomerClassificationList();
            $selectOrgSize                      = 200;
            $Log->info( "MSG_INFO_1038" );

            $searchArray = array(
                'disabled'                      => 0,
                'codeID'                        => '',
                'custTypeNm'                    => '',
                'sort'                          => '4',
            );

           // 顧客分類一覧データ取得
            $customerClassificationAllList = $customerClassification->getListData($searchArray);

            // 顧客分類一覧レコード数
            $customerClassificationRecordCnt = count($customerClassificationAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($customerClassificationRecordCnt, $pagedRecordCnt);

            // シーケンスID
            $idName = "cust_type_id";

            $customerClassificationList = $this->refineListDisplayNoSpecifiedPage($customerClassificationAllList, $idName, $pagedRecordCnt, $pageNo);
            $customerClassificationList = $this->modBtnDelBtnDisabledCheck($customerClassificationList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($customerClassificationList) >= 11 )
            {
                $isScrollBar = true;
            }

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($customerClassificationRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            $headerArray = $this->changeHeaderItemMark(0);

            $customerClassificationNoSortFlag = false;

            $customerClassification_no = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/CustomerClassificationPanel.html';
        }

        /**
         * 顧客分類一覧更新
         * @note     顧客分類画面の一覧部分のみの更新
         * @param    $addFlag           新規登録フラグ true：新規登録  false：新規登録以外
         * @param    $messege           DBの更新結果(データ指定がない場合、デフォルト値[MSG_BASE_0000]を設定)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $searchArray = array(
                'disabled'          => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'codeID'            => parent::escStr( $_POST['searchCodeName'] ),
                'custTypeNm'        => parent::escStr( $_POST['searchCustomerClassificationName'] ),
                'sort'              => parent::escStr( $_POST['sortConditions'] ),
            );

            $customerClassification = new CustomerClassification();

            // シーケンスID
            $idName = "cust_type_id";

            // 顧客分類一覧データ取得
            $customerClassificationSearchList = $customerClassification->getListData($searchArray);

            // 顧客分類一覧検索後のレコード数
            $customerClassificationSearchListCnt = count($customerClassificationSearchList);

            // 顧客分類一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後の顧客分類レコードに対するページ数
            $pagedCnt = ceil($customerClassificationSearchListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データ顧客分類IDを取得
                $customerClassificationLastId = $customerClassification->getLastid();
                $this->pageNoWhenUpdating($customerClassificationSearchList, $idName, $customerClassificationLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $customerClassificationList = $this->refineListDisplayNoSpecifiedPage($customerClassificationSearchList, $idName, $pagedRecordCnt, $pageNo);
            $customerClassificationList = $this->modBtnDelBtnDisabledCheck($customerClassificationList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($customerClassificationList) >= 11 )
            {
                $isScrollBar = true;
            }

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $customerClassificationNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $customerClassification_no = 0;
            
            $display_no = $this->getDisplayNo( $customerClassificationSearchListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/CustomerClassificationTablePanel.html';
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
                    'customerClassificationNoSortMark'           => '',
                    'customerClassificationStateSortMark'        => '',
                    'custTypeCodeSortMark'         => '',
                    'customerClassificationOrganizationSortMark' => '',
                    'custTypeNmSortMark'         => '',
                    'customerClassificationDispOrderSortMark'    => '',
            );
            if(!empty($sortNo))
            {
                $sortList[3] = "customerClassificationStateSortMark";
                $sortList[4] = "customerClassificationStateSortMark";
                $sortList[5] = "custTypeCodeSortMark";
                $sortList[6] = "custTypeCodeSortMark";
                $sortList[9] = "custTypeNmSortMark";
                $sortList[10] = "custTypeNmSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
