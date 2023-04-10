<?php
    /**
     * @file      マッピング名マスタコントローラ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      マッピング名の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/MappingName.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class MappingNameController extends BaseController
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
         * マッピング名一覧画面初期表示
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
         * マッピング名一覧画面検索
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
         * マッピング名一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1042" );

            $mappingName = new mappingName();

            $postArray = array(
                'dispOrder'        => parent::escStr( $_POST['dispOrder'] ),
                'mappingType'      => parent::escStr( $_POST['mappingType'] ),
                'link'             => parent::escStr( $_POST['link'] ),
                'listF'            => parent::escStr( $_POST['listF'] ),
                'mappingCode'      => parent::escStr( $_POST['mappingCode'] ),
                'mappingName'      => parent::escStr( $_POST['mappingName'] ),
                'mappingNameKana'  => parent::escStr( $_POST['mappingNameKana'] ),
                'is_del'           => 0,
                'user_id'          => $_SESSION["USER_ID"],
                'organization'     => $_SESSION["ORGANIZATION_ID"],
                );

            $messege = $mappingName->addNewData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }

        /**
         * マッピング名一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1043" );

            $mappingName = new mappingName();

            $postArray = array(
                'dispOrder'        => parent::escStr( $_POST['dispOrder'] ),
                'mappingNameId'    => parent::escStr( $_POST['mappingNameId'] ),
                'mappingType'      => parent::escStr( $_POST['mappingType'] ),
                'link'             => parent::escStr( $_POST['link'] ),
                'listF'            => parent::escStr( $_POST['listF'] ),
                'mappingCode'      => parent::escStr( $_POST['mappingCode'] ),
                'mappingName'      => parent::escStr( $_POST['mappingName'] ),
                'mappingNameKana'  => parent::escStr( $_POST['mappingNameKana'] ),
                'updateTime'       => parent::escStr( $_POST['updateTime'] ),
                'user_id'          => $_SESSION["USER_ID"],
                'organization'     => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $mappingName->modUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * マッピング名一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1044" );

            $mappingName = new mappingName();

            $postArray = array(
                'mappingNameId'  => parent::escStr( $_POST['mappingNameId'] ),
                'updateTime'     => parent::escStr( $_POST['updateTime'] ),
                'is_del'         => 1,
                'user_id'        => $_SESSION["USER_ID"],
                'organization'   => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $mappingName->delUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   delAction");
        }

        /**
         * 新規登録用エリアの更新
         * @note     マッピング名マスタを新規作成した場合、新規登録用エリアを更新する
         * @return   新規登録用エリア
         */
        public function inputAreaAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1045" );

            $mappingName = new mappingName();

            // タイププルダウン
            $abbMpappingNameTypeList = $mappingName->getMappingTypeList( 'registration' );
            
            // 連携プルダウン
            $abbLinkList = $mappingName->getLinkList( 'registration' );

            // 報告書一覧プルダウン
            $abbListFList = $mappingName->getListFList( 'registration' );

            $Log->trace("END inputAreaAction");
            
            require_once './View/MappingNameInputPanel.html';
        }

        /**
         * マッピング名一覧画面
         * @note     マッピング名画面全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $mappingName = new mappingName();

            $searchArray = array(
                'mapping_name'   => '',
                'mapping_type'   => '',
                'is_del'         => 0,
                'link'           => '',
                'sort'           => '',
                'mcode'          => '',
            );

            // マッピング名一覧データ取得
            $mappingNameAllList = $mappingName->getListData($searchArray);

            // マッピング名一覧レコード数
            $mappingNameRecordCnt = count($mappingNameAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($mappingNameRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "mapping_name_id";

            $mappingNameList = $this->refineListDisplayNoSpecifiedPage($mappingNameAllList, $idName, $pagedRecordCnt, $pageNo);
            $mappingNameList = $this->modBtnDelBtnDisabledCheck($mappingNameList);

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($mappingNameRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // タイププルダウン
            $abbMpappingNameTypeList = $mappingName->getMappingTypeList( 'registration' );
            
            // 連携プルダウン
            $abbLinkList = $mappingName->getLinkList( 'registration' );

            // 報告書一覧プルダウン
            $abbListFList = $mappingName->getListFList( 'registration' );

            $headerArray = $this->changeHeaderItemMark(0);

            $mappingNameNoSortFlag = false;

            $mappingName_no  = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/MappingNamePanel.html';
        }

        /**
         * マッピング名一覧更新
         * @note     マッピング名画面の一覧部分のみの更新
         * @param    $addFlag           新規登録フラグ true：新規登録  false：新規登録以外
         * @param    $messege           DBの更新結果(データ指定がない場合、デフォルト値[MSG_BASE_0000]を設定)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $searchArray = array(
                'mappingType'    => parent::escStr( $_POST['searchMappingType'] ),
                'mappingName'    => parent::escStr( $_POST['searchMappingName'] ),
                'link'           => parent::escStr( $_POST['searchLink'] ),
                'is_del'         => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'sort'           => parent::escStr( $_POST['sortConditions'] ),
            );

            $mappingName = new mappingName();

            // シーケンスIDName
            $idName = "mapping_name_id";

            // マッピング名一覧データ取得
            $mappingNameSearchList = $mappingName->getListData($searchArray);
            
            // マッピング名一覧検索後のレコード数
            $mappingNameSearchListCnt = count($mappingNameSearchList);
            // マッピング名一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
            // 検索結果後のマッピング名レコードに対するページ数
            $pagedCnt = ceil($mappingNameSearchListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データマッピング名IDを取得
                $mappingNameLastId = $mappingName->getLastid();
                $this->pageNoWhenUpdating($mappingNameSearchList, $idName, $mappingNameLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $mappingNameList = $this->refineListDisplayNoSpecifiedPage($mappingNameSearchList, $idName, $pagedRecordCnt, $pageNo);
            $mappingNameList = $this->modBtnDelBtnDisabledCheck($mappingNameList);

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // タイププルダウン
            $abbMpappingNameTypeList = $mappingName->getMappingTypeList( 'registration' );
            
            // 連携プルダウン
            $abbLinkList = $mappingName->getLinkList( 'registration' );

            // 報告書一覧プルダウン
            $abbListFList = $mappingName->getListFList( 'registration' );

            // キー種別プルダウン
            
            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $mappingNameNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $mappingName_no = 0;
            
            $display_no = $this->getDisplayNo( $mappingNameSearchListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/MappingNameTablePanel.html';
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
                    'mappingNameNoSortMark'         => '',
                    'mappingNameDispOrderSortMark'  => '',
                    'mappingTypeSortMark'           => '',
                    'linkSortMark'                  => '',
                    'listFSortMark'                 => '',
                    'mappingCodeSortMark'           => '',
                    'mappingNameSortMark'           => '',
                    'mappingNameKanaSortMark'       => '',
                    'registrationTimeSortMark'      => '',
                    'updateTimeSortMark'            => '',
                    'mappingNameStateSortMark'      => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]  = "mappingNameNoSortMark";
                $sortList[2]  = "mappingNameNoSortMark";
                $sortList[3]  = "mappingNameDispOrderSortMark";
                $sortList[4]  = "mappingNameDispOrderSortMark";
                $sortList[5]  = "mappingTypeSortMark";
                $sortList[6]  = "mappingTypeSortMark";
                $sortList[7]  = "linkSortMark";
                $sortList[8]  = "linkSortMark";
                $sortList[9]  = "listFSortMark";
                $sortList[10] = "listFSortMark";
                $sortList[11] = "mappingCodeSortMark";
                $sortList[12] = "mappingCodeSortMark";
                $sortList[13] = "mappingNameSortMark";
                $sortList[14] = "mappingNameSortMark";
                $sortList[15] = "mappingNameKanaSortMark";
                $sortList[16] = "mappingNameKanaSortMark";
                $sortList[17] = "registrationTimeSortMark";
                $sortList[18] = "registrationTimeSortMark";
                $sortList[19] = "updateTimeSortMark";
                $sortList[20] = "updateTimeSortMark";
                $sortList[21] = "mappingNameStateSortMark";
                $sortList[22] = "mappingNameStateSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
