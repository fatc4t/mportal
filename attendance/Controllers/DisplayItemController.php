<?php
    /**
     * @file      表示項目設定コントローラ
     * @author    USE K.Narita
     * @date      2016/06/21
     * @version   1.00
     * @note      表示項目設定の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // DisplayItem処理モデル
    require './Model/DisplayItem.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class DisplayItemController extends BaseController
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
         * 表示項目設定一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1140" );
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }

        /**
         * 表示項目設定一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            $Log->info( "MSG_INFO_1141" );

            if(isset($_POST['displayPageNo']))
            {
                $_SESSION['PAGE_NO'] = parent::escStr( $_POST['displayPageNo'] );
            }

            if(isset($_POST['displayRecordCnt']))
            {
                $_SESSION['DISPLAY_RECORD_CNT'] = parent::escStr( $_POST['displayRecordCnt'] );
            }

            $this->initialList();
            $Log->trace("END   searchAction");
        }
        
        /**
         * 表示項目設定一覧画面遷移(更新/削除)
         * @return    なし
         */
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addInputAction" );
            $Log->info( "MSG_INFO_1142" );

            $this->displayItemInputPanelDisplay();

            $Log->trace("END addInputAction");
        }

        /**
         * 表示項目設定マスタ新規登録処理
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1143" );

            $displayItem = new DisplayItem();

            $postArray = array(
                'displayItemId'                    => parent::escStr( $_POST['displayItemId'] ),
                'organization_id'                  => parent::escStr( $_POST['organizationName'] ),
                'optionName'                       => parent::escStr( $_POST['optionName'] ),
                'displayFormat'                    => parent::escStr( $_POST['displayFormat'] ) === 'true' ? 1 : 2,
                'noDataFormat'                     => parent::escStr( $_POST['noDataFormat'] ) === 'true' ? 1 : 2,
                'comment'                          => parent::escStr( $_POST['comment'] ),
                'dispOrder'                        => parent::escStr( $_POST['dispOrder'] ),
                'user_id'                          => $_SESSION["USER_ID"],
                'organization'                     => $_SESSION["ORGANIZATION_ID"],
            );
            
            $outputItemList = array();
            $itemNameList = array();
            $isDisplayList = array();
            $outputTypeIdList = array();
            $outItemBranchList = array();
            $outputItemViewList = array();
            
            $arrayCount = count($_POST['outputItemNameList']);
            for($i = 0 ; $i < $arrayCount ; $i++ )
            {
                array_push( $outputItemList ,         parent::escStr( $_POST['outputItemNameList'][$i] ) );
                array_push( $itemNameList ,           parent::escStr( $_POST['itemNameArrayList'][$i] ) );
                array_push( $isDisplayList ,          parent::escStr( $_POST['isDisplayArrayList'][$i] ) );
                array_push( $outputTypeIdList ,       parent::escStr( $_POST['outputTypeIdArrayList'][$i] ) );
                array_push( $outItemBranchList ,      parent::escStr( $_POST['outputItemBranchArrayList'][$i] ) );
                array_push( $outputItemViewList ,     parent::escStr( $_POST['outputItemViewArrayList'][$i] ) );
            }

            // 新規登録フラグ
            $addFlag = true;
            
            $messege = $displayItem->addNewData($postArray,$outputItemList,$itemNameList,$isDisplayList,$outputTypeIdList,$addFlag, $outItemBranchList, $outputItemViewList);
            
            
            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                $this->initialDisplay();
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
            }
            
            $Log->trace("END addAction");
        }
        
        /**
         * 表示項目設定一覧画面修正処理
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1144" );

            $displayItem = new DisplayItem();

            $postArray = array(
                'displayItemId'                    => parent::escStr( $_POST['displayItemId'] ),
                'comment'                          => parent::escStr( $_POST['comment'] ),
                'organization_id'                  => parent::escStr( $_POST['organizationName'] ),
                'optionName'                       => parent::escStr( $_POST['optionName'] ),
                'displayFormat'                    => parent::escStr( $_POST['displayFormat'] ) === 'true' ? 1 : 2,
                'noDataFormat'                     => parent::escStr( $_POST['noDataFormat'] ) === 'true' ? 1 : 2,
                'dispOrder'                        => parent::escStr( $_POST['dispOrder'] ),
                'updateTime'                       => parent::escStr( $_POST['updateTime'] ),
                'user_id'                          => $_SESSION["USER_ID"],
                'organization'                     => $_SESSION["ORGANIZATION_ID"],
            );
            
            $outputItemList = array();
            $itemNameList = array();
            $isDisplayList = array();
            $outputTypeIdList = array();
            $outItemBranchList = array();
            $outputItemViewList = array();
            
            $arrayCount = count($_POST['outputItemNameList']);
            for($i = 0 ; $i < $arrayCount ; $i++ )
            {
                array_push( $outputItemList ,         parent::escStr( $_POST['outputItemNameList'][$i] ) );
                array_push( $itemNameList ,           parent::escStr( $_POST['itemNameArrayList'][$i] ) );
                array_push( $isDisplayList ,          parent::escStr( $_POST['isDisplayArrayList'][$i] ) );
                array_push( $outputTypeIdList ,       parent::escStr( $_POST['outputTypeIdArrayList'][$i] ) );
                array_push( $outItemBranchList ,      parent::escStr( $_POST['outputItemBranchArrayList'][$i] ) );
                array_push( $outputItemViewList ,     parent::escStr( $_POST['outputItemViewArrayList'][$i] ) );
            }

            // 新規登録フラグ
            $addFlag = false;
            $messege = $displayItem->addNewData($postArray,$outputItemList,$itemNameList,$isDisplayList,$outputTypeIdList,$addFlag, $outItemBranchList, $outputItemViewList);
            
            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                $this->initialDisplay();
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
            }
            $Log->trace("END   modAction");
        }

        /**
         * 表示項目設定一覧画面削除処理
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1145" );

            $displayItem = new DisplayItem();

            $postArray = array(
                'displayItemId'                    => parent::escStr( $_POST['displayItemId'] ),
                'comment'                          => parent::escStr( $_POST['comment'] ),
                'optionName'                       => parent::escStr( $_POST['optionName'] ),
                'displayFormat'                    => parent::escStr( $_POST['displayFormat'] ),
                'noDataFormat'                     => parent::escStr( $_POST['noDataFormat'] ),
                'dispOrder'                        => parent::escStr( $_POST['dispOrder'] ),
                'updateTime'                       => parent::escStr( $_POST['updateTime'] ),
                'is_del'                           => 1,
                'user_id'                          => $_SESSION["USER_ID"],
                'organization'                     => $_SESSION["ORGANIZATION_ID"], 
                );

            $messege = $displayItem->delUpdateData($postArray);

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                $this->initialDisplay();
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
            }
            $Log->trace("END   delAction");
        }
        
        /**
         * 表示項目設定一覧画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $displayItem = new DisplayItem();

            // 検索プルダウン
            $abbreviatedNameList = $displayItem->setPulldown->getSearchOrganizationList( 'reference', true );    // 組織略称名リスト
            $optionNameList      = $displayItem->setPulldown->getSearchOptionNameList( 'reference' );            // 設定名リスト
            $displayFormatList   = $displayItem->getSearchDisplayFormatList($_SESSION["REFERENCE"]);             // 時間の表示形式リスト
            $noDataFormatList    = $displayItem->getSearchNoDataFormatList($_SESSION["REFERENCE"]);              // 時間データなしリスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;
            
            $searchArray = array(
                                    'organizationID'       => "",
                                    'optionName'           => "",
                                    'displayFormat'        => "",
                                    'noDataFormat'         => "",
                                    'minCount'             => "",
                                    'maxCount'             => "",
                                    'comment'              => "",
                                    'isDel'                => 0,
                                    'sort'                 => 0,
                                );
            if( !empty($_POST['searchOrganizationID'] ) )
            {
                $searchArray = array(
                    'organizationID'       => parent::escStr( $_POST['searchOrganizatioID'] ),
                    'optionName'           => parent::escStr( $_POST['searchOptionName'] ),
                    'displayFormat'        => parent::escStr( $_POST['searchDisplayFormat'] ),
                    'noDataFormat'         => parent::escStr( $_POST['searchNoDataFormat'] ),
                    'minCount'             => parent::escStr( $_POST['searchMinCount'] ),
                    'maxCount'             => parent::escStr( $_POST['searchMaxCount'] ),
                    'comment'              => parent::escStr( $_POST['searchComment'] ),
                    'isDel'                => parent::escStr( $_POST['searchDelCheck'] ) == 'true' ? 1 : 0,
                    'sort'                 => parent::escStr( $_POST['sortConditions'] ),
                );
            }

            // 表示項目設定一覧データ取得
            $displayItemAllList = $displayItem->getListData($searchArray);

            // 表示項目設定一覧レコード数
            $displayItemRecordCnt = count($displayItemAllList);
            
            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($displayItemRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "display_item_id";

            $displayItemList = $this->refineListDisplayNoSpecifiedPage($displayItemAllList, $idName, $pagedRecordCnt, $pageNo);

            $displayItemList = $this->modBtnDelBtnDisabledCheck($displayItemList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 12 && count($displayItemList) >= 12)
            {
                $isScrollBar = true;
            }

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($displayItemRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            $headerArray = $this->changeHeaderItemMark(0);

            $displayNoSortFlag = false;
            
            $headerArray = $this->changeHeaderItemMark(0);

            $displayNoSortFlag = false;

            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/DisplayItemPanel.html';
        }

        /**
         * 表示項目設定一覧更新
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function initialList()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialList");

            $searchArray = array(
                'organizationID'       => parent::escStr( $_POST['searchOrganizatioID'] ),
                'optionName'           => parent::escStr( $_POST['searchOptionName'] ),
                'displayFormat'        => parent::escStr( $_POST['searchDisplayFormat'] ),
                'noDataFormat'         => parent::escStr( $_POST['searchNoDataFormat'] ),
                'minCount'             => parent::escStr( $_POST['searchMinCount'] ),
                'maxCount'             => parent::escStr( $_POST['searchMaxCount'] ),
                'comment'              => parent::escStr( $_POST['searchComment'] ),
                'isDel'                => parent::escStr( $_POST['searchDelCheck'] ) === 'true' ? 1 : 0,
                'sort'                 => parent::escStr( $_POST['sortConditions'] ),
            );
            
            $displayItem = new DisplayItem();
            
            // シーケンスIDName
            $idName = "display_item_id";

            // 表示項目設定一覧データ取得
            $displaySearchList = $displayItem->getListData($searchArray);
            
            // 表示項目設定一覧検索後のレコード数
            $emploSearchListCnt = count($displaySearchList);
            // 表示項目設定一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
            // 検索結果後の表示項目設定レコードに対するページ数
            $pagedCnt = ceil($emploSearchListCnt /  $pagedRecordCnt);

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $displayItemList = $this->refineListDisplayNoSpecifiedPage($displaySearchList, $idName, $pagedRecordCnt, $pageNo);
            
            $displayItemList = $this->modBtnDelBtnDisabledCheck($displayItemList);
            
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 12 && count($displayItemList) >= 12)
            {
                $isScrollBar = true;
            }
            
            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);


            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $displayNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $display_no = $this->getDisplayNo( $emploSearchListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );
            
            require_once './View/DisplayItemTablePanel.html';

            $Log->trace("END   initialList");
        }

        /**
         * ヘッダー部分ソート時のマーク変更
         * @note     パラメータは、View側で使用
         * @param    ソート条件番号(「No」押下時：1/「組織名」押下時：3/「設定名」押下時：5/「時間の表示形式」押下時：7/「時間データなし」押下時：9/「表示項目数」押下時：11/「コメント」押下時：13/「表示順」押下時：15/「状態」押下時：17)
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        private function changeHeaderItemMark($sortNo)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeHeaderItemMark");
            
            // 初期化
            $headerArray = array(
                    'displayItemNoSortMark'                 => '',
                    'displayItemOrganizationSortMark'       => '',
                    'displayItemOptionSortMark'             => '',
                    'displayItemDisplayFormatSortMark'      => '',
                    'displayItemNoDataFormatSortMark'       => '',
                    'displayItemCountSortMark'              => '',
                    'displayItemCommentSortMark'            => '',
                    'displayItemDispOrderSortMark'          => '',
                    'displayItemStateSortMark'              => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1] = "displayItemNoSortMark";
                $sortList[2] = "displayItemNoSortMark";
                $sortList[3] = "displayItemOrganizationSortMark";
                $sortList[4] = "displayItemOrganizationSortMark";
                $sortList[5] = "displayItemOptionSortMark";
                $sortList[6] = "displayItemOptionSortMark";
                $sortList[7] = "displayItemDisplayFormatSortMark";
                $sortList[8] = "displayItemDisplayFormatSortMark";
                $sortList[9] = "displayItemNoDataFormatSortMark";
                $sortList[10] = "displayItemNoDataFormatSortMark";
                $sortList[11] = "displayItemCountSortMark";
                $sortList[12] = "displayItemCountSortMark";
                $sortList[13] = "displayItemCommentSortMark";
                $sortList[14] = "displayItemCommentSortMark";
                $sortList[15] = "displayItemDispOrderSortMark";
                $sortList[16] = "displayItemDispOrderSortMark";
                $sortList[17] = "displayItemStateSortMark";
                $sortList[18] = "displayItemStateSortMark";
                $temp = "";
                if(array_key_exists($sortNo,$sortList))
                {
                    $temp = $sortList[$sortNo];
                }
                $mark = SystemParameters::$SORT_ASC_MARK;
                if( $sortNo % 2 != 0 )
                {
                    $mark = SystemParameters::$SORT_DESC_MARK;
                }
                $headerArray[$temp] = $mark;
            }
            
            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }
        
        /**
         * 表示項目設定マスタ入力画面(新規登録)
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function displayItemInputPanelDisplay()
        {
            global $Log, $TokenID; // グローバル変数宣言
            $Log->trace( "START displayItemInputPanelDisplay" );

            $searchArray = array(
                'organizationId'       => parent::escStr( $_POST['searchOrganizatioID'] ),
                'optionName'           => parent::escStr( $_POST['searchOptionName'] ),
                'displayFormat'        => parent::escStr( $_POST['searchDisplayFormat'] ),
                'noDataFormat'         => parent::escStr( $_POST['searchNoDataFormat'] ),
                'minCount'             => parent::escStr( $_POST['searchMinCount'] ),
                'maxCount'             => parent::escStr( $_POST['searchMaxCount'] ),
                'comment'              => parent::escStr( $_POST['searchComment'] ),
                'isDel'                => parent::escStr( $_POST['searchDelCheck'] ) === 'true' ? 1 : 0,
            );

            $postArray = array(
                    'displayItemId'   => parent::escStr( $_POST['displayItemId'] ),
                    'organization_id' => $_SESSION["ORGANIZATION_ID"],
                    'isDel'           => 0,
                    'sort'            => 0,
            );
            
            $displayItem = new DisplayItem();

            // 登録用組織略称名プルダウン
            $abbreviatedNameList = $displayItem->setPulldown->getSearchOrganizationList( 'registration', true );

            // ヘッダ部分の入力値の取得
            if( $_POST['displayItemId'] != 0 )
            {
                $displayItemList = $displayItem->getListData($postArray);
            }
            else
            {
                $displayItemList = array( array(
                        'display_item_id'   => '',
                        'abbreviated_name'  => '',
                        'name'              => '',
                        'display_format'    => '',
                        'no_data_format'    => '',
                        'comment'           => '',
                        'disp_order'        => '',
                        'count'             => '',
                        'organization_id'   => '',
                        'is_del'            => '',
                        'update_time'       => '',
                       
                ));
            }
            
            // 詳細部分の入力値の取得
            $outputItemList = $displayItem->getOutputItemDeteilList($postArray);
            $outputItemDetailList = array();
            foreach( $outputItemList as $outputItem )
            {
                // 出力データを設定
                array_push( $outputItemDetailList, $outputItem );
                // 出力種別が、1(表示項目マスタデータ以外)
                if( $outputItem['output_type_id'] != 1 )
                {
                    // サブ項目が存在するか
                    if( !$displayItem->isSubItem( $postArray['displayItemId'], $outputItem['output_type_id'], $outputItem['output_item_id'] ) )
                    {
                        // サブ項目を設定
                        if( $outputItem['output_type_id'] == 3 || $outputItem['output_type_id'] == 4 )
                        {
                            $this->setHolidaySubItems( $outputItem, $outputItemDetailList );
                        }
                        else if( $outputItem['output_type_id'] == 2 )
                        {
                            $this->setAllowanceSubItems( $outputItem, $outputItemDetailList );
                        }
                    }
                }
            }


            $display_no =0;
            
            $Log->trace( "END displayItemInputPanelDisplay" );
            require_once './View/DisplayItemInputPanel.html';
        }
        
        /**
         * 休日(名称)マスタのサブ項目追加
         * @note     休日(名称)マスタで、1つでもIDがある場合、複数のサブ項目を設定する
         * @return   無
         */
        private function setHolidaySubItems( $outputItem, &$outputItemDetailList )
        {
            // 追加するサブ項目
            $inputNewDataArray = array(
                                        '1'     =>  $outputItem['disp_name'] . '　休日取得日数',
                                        '2'     =>  $outputItem['disp_name'] . '　休日出勤日数',
                                        '3'     =>  $outputItem['disp_name'] . '　休日労働時間',
                                        '4'     =>  $outputItem['disp_name'] . '　休日所定内労働時間',
                                        '5'     =>  $outputItem['disp_name'] . '　休日残業時間',
                                        '6'     =>  $outputItem['disp_name'] . '　休日深夜時間',
                                        '7'     =>  $outputItem['disp_name'] . '　休日深夜残業時間',
                                    );
            
            // データの初期値
            $inputArray = array( 
                                'organization_id'        => $outputItem['organization_id'],
                                'display_item_id'        => $outputItem['display_item_id'],
                                'output_type_id'         => $outputItem['output_type_id'],
                                'output_item_id'         => $outputItem['output_item_id'],
                                'output_item_branch'     => 0,
                                'disp_name'              => '',
                                'item_name'              => '',
                                'is_display'             => 0,
                                'disp_order'             => $outputItem['disp_order'], 
                          );
            
            foreach( $inputNewDataArray as $key => $val )
            {
                $inputArray['output_item_branch'] = $key;
                $inputArray['disp_name'] = $val;
                $inputArray['item_name'] = $val;
                
                array_push( $outputItemDetailList, $inputArray );
            }
        }
        
        /**
         * 手当マスタのサブ項目追加
         * @note     手当マスタで、1つでもIDがある場合、複数のサブ項目を設定する
         * @return   無
         */
        private function setAllowanceSubItems( $outputItem, &$outputItemDetailList )
        {
            // 追加するサブ項目
            $inputNewDataArray = array(
                                        '1'     =>  $outputItem['disp_name'] . '　手当取得回数',
                                        '2'     =>  $outputItem['disp_name'] . '　手当金額',
                                    );
            
            // データの初期値
            $inputArray = array( 
                                'organization_id'        => $outputItem['organization_id'],
                                'display_item_id'        => $outputItem['display_item_id'],
                                'output_type_id'         => $outputItem['output_type_id'],
                                'output_item_id'         => $outputItem['output_item_id'],
                                'output_item_branch'     => 0,
                                'disp_name'              => '',
                                'item_name'              => '',
                                'is_display'             => 0,
                                'disp_order'             => $outputItem['disp_order'], 
                          );
            
            foreach( $inputNewDataArray as $key => $val )
            {
                $inputArray['output_item_branch'] = $key;
                $inputArray['disp_name'] = $val;
                $inputArray['item_name'] = $val;
                
                array_push( $outputItemDetailList, $inputArray );
            }
        }
    }
?>
