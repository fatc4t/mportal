<?php
    /**
     * @file      商品別マッピング設定コントローラ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      商品別マッピング設定の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/PosMappingItem.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class PosMappingItemController extends BaseController
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
         * 商品別マッピング一覧画面初期表示
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
         * 商品別マッピング一覧画面検索
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
         * 商品別マッピング一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1042" );

            $posMappingItem = new posMappingItem();

            $postArray = array(
                'dispOrder'        => parent::escStr( $_POST['dispOrder'] ),
                'posBrandId'       => parent::escStr( $_POST['posBrandId'] ),
                'mappingNameId'    => parent::escStr( $_POST['mappingNameId'] ),
                'logicType'        => parent::escStr( $_POST['logicType'] ),
                'logic'            => parent::escStr( $_POST['logic'] ),
                'posKeyFileId'     => parent::escStr( $_POST['posKeyFileId'] ),
                'keta'             => parent::escStr( $_POST['keta'] ),
                'roundType'        => parent::escStr( $_POST['roundType'] ),
                'symbol'           => parent::escStr( $_POST['symbol'] ),
                'is_del'           => 0,
                'user_id'          => $_SESSION["USER_ID"],
                'organization'     => $_SESSION["ORGANIZATION_ID"],
                );

            $messege = $posMappingItem->addNewData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }

        /**
         * 商品別マッピング一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1043" );

            $posMappingItem = new posMappingItem();

            $postArray = array(
                'posMappingItemId'   => parent::escStr( $_POST['posMappingItemId'] ),
                'dispOrder'      => parent::escStr( $_POST['dispOrder'] ),
                'posBrandId'     => parent::escStr( $_POST['posBrandId'] ),
                'mappingNameId'  => parent::escStr( $_POST['mappingNameId'] ),
                'logicType'      => parent::escStr( $_POST['logicType'] ),
                'logic'          => parent::escStr( $_POST['logic'] ),
                'posKeyFileId'   => parent::escStr( $_POST['posKeyFileId'] ),
                'keta'           => parent::escStr( $_POST['keta'] ),
                'roundType'      => parent::escStr( $_POST['roundType'] ),
                'symbol'         => parent::escStr( $_POST['symbol'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $posMappingItem->modUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * 商品別マッピング一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1044" );

            $posMappingItem = new posMappingItem();

            $postArray = array(
                'posMappingItemId'   => parent::escStr( $_POST['posMappingItemId'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'is_del'         => 1,
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $posMappingItem->delUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   delAction");
        }

        /**
         * 新規登録用エリアの更新
         * @note     商品別マッピングマスタを新規作成した場合、新規登録用エリアを更新する
         * @return   新規登録用エリア
         */
        public function inputAreaAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1045" );

            $posMappingItem = new posMappingItem();

            // キーファイル名プルダウン
            $abbPosKeyFileList = $posMappingItem->getPosKeyFileList( 'registration' );

            $Log->trace("END inputAreaAction");
            
            require_once './View/PosMappingItemInputPanel.html';
        }

        /**
         * 商品別マッピング一覧画面
         * @note     マッピング名画面全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $posMappingItem = new posMappingItem();

            $searchArray = array(
                'is_del'          => 0,
                'sort'            => '',
            );

            // 商品別マッピング一覧データ取得
            $posMappingItemAllList = $posMappingItem->getListData($searchArray);

            // 商品別マッピング一覧レコード数
            $posMappingItemRecordCnt = count($posMappingItemAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($posMappingItemRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "pos_mapping_item_id";

            $posMappingItemList = $this->refineListDisplayNoSpecifiedPage($posMappingItemAllList, $idName, $pagedRecordCnt, $pageNo);
            $posMappingItemList = $this->modBtnDelBtnDisabledCheck($posMappingItemList);

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
                             
            $pagedCnt = ceil($posMappingItemRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // キーファイル名プルダウン
            $abbPosKeyFileList = $posMappingItem>-getPosKeyFileList( 'registration' );
            
            $headerArray = $this->changeHeaderItemMark(0);

            $posMappingItemNoSortFlag = false;

            $posMappingItem_no  = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/PosMappingItemPanel.html';
        }

        /**
         * 商品別マッピング一覧更新
         * @note     商品別マッピング画面の一覧部分のみの更新
         * @param    $addFlag           新規登録フラグ true：新規登録  false：新規登録以外
         * @param    $messege           DBの更新結果(データ指定がない場合、デフォルト値[MSG_BASE_0000]を設定)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $searchArray = array(
                'posBrandId'     => parent::escStr( $_POST['searchPosBrandId'] ),
                'mappingNameId'  => parent::escStr( $_POST['searchMappingNameId'] ),
                'is_del'         => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'sort'           => parent::escStr( $_POST['sortConditions'] ),
            );

            $posMappingItem= new posMappingItem();

            // シーケンスIDName
            $idName = "pos_mapping_id";

            // 商品別マッピング一覧データ取得
            $posMappingItemSearchList = $posMappingItem->getListData($searchArray);
            
            // 商品別マッピング一覧検索後のレコード数
            $posMappingItemSearchListCnt = count($posMappingItemSearchList);
            // 商品別マッピング一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
            // 検索結果後の商品別マッピングレコードに対するページ数
            $pagedCnt = ceil($posMappingItemSearchListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データ商品別マッピングIDを取得
                $posMappingItemLastId = $posMappingItem->getLastid();
                $this->pageNoWhenUpdating($posMappingItemSearchList, $idName, $posMappingItemLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $posMappingItemList = $this->refineListDisplayNoSpecifiedPage($posMappingItemSearchList, $idName, $pagedRecordCnt, $pageNo);
            $posMappingItemList = $this->modBtnDelBtnDisabledCheck($posMappingItemList);

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // キーファイル名プルダウン
            $abbPosKeyFileList = $posMappingItem->getPosKeyFileList( 'registration' );
            
            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $posMappingItemNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $posMappingItem_no = 0;
            
            $display_no = $this->getDisplayNo( $posMappingItemSearchListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/posMappingItemTablePanel.html';
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
                    'posMappngNoSortMark'         => '',
                    'posMappngDispOrderSortMark'  => '',
                    'posBrandIdSortMark'          => '',
                    'mCodeSortMark'               => '',
                    'mappingNameSortMark'         => '',
                    'logicTypeSortMark'           => '',
                    'logicSortMark'               => '',
                    'posKeyFileIdSortMark'        => '',
                    'ketaSortMark'                => '',
                    'roundTypeSortMark'           => '',
                    'symbolSortMark'              => '',
                    'registrationTimeSortMark'    => '',
                    'updateTimeSortMark'          => '',
                    'mappingNameStateSortMark'    => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]  = "posMappngNoSortMark";
                $sortList[2]  = "posMappngNoSortMark";
                $sortList[3]  = "posMappngDispOrderSortMark";
                $sortList[4]  = "posMappngDispOrderSortMark";
                $sortList[5]  = "posBrandIdSortMark";
                $sortList[6]  = "posBrandIdSortMark";
                $sortList[7]  = "mCodeSortMark";
                $sortList[8]  = "mCodeSortMark";
                $sortList[9]  = "mappingNameSortMark";
                $sortList[10] = "mappingNameSortMark";
                $sortList[11] = "logicTypeSortMark";
                $sortList[12] = "logicTypeSortMark";
                $sortList[13] = "logicSortMark";
                $sortList[14] = "logicSortMark";
                $sortList[15] = "posKeyFileIdSortMark";
                $sortList[16] = "posKeyFileIdSortMark";
                $sortList[17] = "ketaSortMark";
                $sortList[18] = "ketaSortMark";
                $sortList[19] = "roundTypeSortMark";
                $sortList[20] = "roundTypeSortMark";
                $sortList[21] = "symbolSortMark";
                $sortList[22] = "symbolSortMark";
                $sortList[23] = "registrationTimeSortMark";
                $sortList[24] = "registrationTimeSortMark";
                $sortList[25] = "updateTimeSortMark";
                $sortList[26] = "updateTimeSortMark";
                $sortList[27] = "mappingNameStateSortMark";
                $sortList[28] = "mappingNameStateSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
