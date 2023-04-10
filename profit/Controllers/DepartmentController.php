<?php
    /**
     * @file      POS種別マスタコントローラ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      POS種別の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/PosBrand.php';

    /**
     * POS種別マスタコントローラクラス
     * @note   POS種別マスタの新規登録/修正/削除を行う
     */
    class DepartmentController extends BaseController
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
         * POS種別一覧画面初期表示
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
         * POS種別一覧画面検索
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
         * POS種別一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1042" );

            $posBrand = new posBrand();

            $postArray = array(
                'posBrandCode'       => parent::escStr( $_POST['posBrandCode'] ),
                'posBrandName'       => parent::escStr( $_POST['posBrandName'] ),
                'posBrandNameKana'   => parent::escStr( $_POST['posBrandNameKana'] ),
                'is_del'             => 0,
                'dispOrder'          => parent::escStr( $_POST['dispOrder'] ),
                'user_id'            => $_SESSION["USER_ID"],
                'organization'       => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $posBrand->addNewData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }

        /**
         * POS種別一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1043" );

            $posBrand = new posBrand();

            $postArray = array(
                'posBrandId'         => parent::escStr( $_POST['posBrandID'] ),
                'dispOrder'          => parent::escStr( $_POST['dispOrder'] ),
                'posBrandCode'       => parent::escStr( $_POST['posBrandCode'] ),
                'posBrandName'       => parent::escStr( $_POST['posBrandName'] ),
                'posBrandNameKana'   => parent::escStr( $_POST['posBrandNameKana'] ),
                'updateTime'         => parent::escStr( $_POST['updateTime'] ),
                'user_id'            => $_SESSION["USER_ID"],
                'organization'       => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $posBrand->modUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * POS種別一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1044" );

            $posBrand = new posBrand();

            $postArray = array(
                'posBrandId'     => parent::escStr( $_POST['posBrandID'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'is_del'         => 1,
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $posBrand->delUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   delAction");
        }

        /**
         * 新規登録用エリアの更新
         * @note     POS種別マスタを新規作成した場合、新規登録用エリアを更新する
         * @return   新規登録用エリア
         */
        public function inputAreaAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1045" );
            $Log->trace("END inputAreaAction");
            
            require_once './View/DepartmentInputPanel.html';
        }

        /**
         * POS種別一覧画面
         * @note     POS種別画面全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $posBrand = new posBrand();

            $searchArray = array(
                'pos_brand_code' => '',
                'pos_brand_name' => '',
                'is_del'         => 0,
                'sort'           => '',
            );

            // POS種別一覧データ取得
            $posBrandAllList = $posBrand->getListData($searchArray);

            // POS種別一覧レコード数
            $posBrandRecordCnt = count($posBrandAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($posBrandRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "pos_brand_id";

            $posBrandList = $this->refineListDisplayNoSpecifiedPage($posBrandAllList, $idName, $pagedRecordCnt, $pageNo);
            $posBrandList = $this->modBtnDelBtnDisabledCheck($posBrandList);

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($posBrandRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            $headerArray = $this->changeHeaderItemMark(0);

            $posBrandNoSortFlag = false;

            $posBrand_no = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/DepartmentPanel.html';
        }

        /**
         * POS種別一覧更新
         * @note     POS種別画面の一覧部分のみの更新
         * @param    $addFlag           新規登録フラグ true：新規登録  false：新規登録以外
         * @param    $messege           DBの更新結果(データ指定がない場合、デフォルト値[MSG_BASE_0000]を設定)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $searchArray = array(
                'posBrandCode'   => parent::escStr( $_POST['searchPosBrandCode'] ),
                'posBrandName'   => parent::escStr( $_POST['searchPosBrandName'] ),
                'is_del'         => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'sort'           => parent::escStr( $_POST['sortConditions'] ),
            );

            $posBrand = new posBrand();

            // シーケンスIDName
            $idName = "pos_brand_id";

            // POS種別一覧データ取得
            $posBrandSearchList = $posBrand->getListData($searchArray);
            
            // POS種別一覧検索後のレコード数
            $posBrandSearchListCnt = count($posBrandSearchList);
            // POS種別一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
            // 検索結果後のPOS種別レコードに対するページ数
            $pagedCnt = ceil($posBrandSearchListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データPOS種別IDを取得
                $posBrandLastId = $posBrand->getLastid();
                $this->pageNoWhenUpdating($posBrandSearchList, $idName, $posBrandLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $posBrandList = $this->refineListDisplayNoSpecifiedPage($posBrandSearchList, $idName, $pagedRecordCnt, $pageNo);
            $posBrandList = $this->modBtnDelBtnDisabledCheck($posBrandList);

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $posBrandNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $posBrand_no = 0;
            
            $display_no = $this->getDisplayNo( $posBrandSearchListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/DepartmentTablePanel.html';
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
                    'posBrandNoSortMark'        => '',
                    'posBrandDispOrderSortMark' => '',
                    'posBrandCodeSortMark'      => '',
                    'posBrandNameSortMark'      => '',
                    'posBrandNameKanaSortMark'  => '',
                    'registrationTimeSortMark'  => '',
                    'updateTimeSortMark'        => '',
                    'posBrandStateSortMark'     => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]  = "posBrandNoSortMark";
                $sortList[2]  = "posBrandNoSortMark";
                $sortList[3]  = "posBrandDispOrderSortMark";
                $sortList[4]  = "posBrandDispOrderSortMark";
                $sortList[5]  = "posBrandCodeSortMark";
                $sortList[6]  = "posBrandCodeSortMark";
                $sortList[7]  = "posBrandNameSortMark";
                $sortList[8]  = "posBrandNameSortMark";
                $sortList[9]  = "posBrandNameKanaSortMark";
                $sortList[10] = "posBrandNameKanaSortMark";
                $sortList[11] = "registrationTimeSortMark";
                $sortList[12] = "registrationTimeSortMark";
                $sortList[13] = "updateTimeSortMark";
                $sortList[14] = "updateTimeSortMark";
                $sortList[15] = "posBrandStateSortMark";
                $sortList[16] = "posBrandStateSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
