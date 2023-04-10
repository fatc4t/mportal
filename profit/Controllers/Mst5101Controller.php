<?php
    /**
     * @file      POS商品セット構成マスタコントローラ
     * @author    川橋
     * @date      2018/12/27
     * @version   1.00
     * @note      POS商品セット構成マスタの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/Mst5101.php';

    /**
     * POS商品セット構成マスタコントローラクラス
     * @note   POS商品セット構成マスタの新規登録/修正/削除を行う
     */
    class Mst5101Controller extends BaseController
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
         * POS商品セット構成マスタ一覧画面初期表示
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
         * POS商品セット構成マスタ一覧画面検索
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
         * POS商品セット構成マスタ一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1022" );

            $mst5101 = new Mst5101();

            $postArray = array(
                'organization_id'     => parent::escStr( $_POST['organizationId'] ),
                'prod_cd'             => parent::escStr( $_POST['prodCode'] ),
                'setprod_cd'          => parent::escStr( $_POST['setprodCode'] ),
                'setprod_amount'      => parent::escStr( $_POST['setprodAmount'] ),
            );

            $messege = $mst5101->addNewData($postArray);

            $this->initialList( true, $messege);

            $Log->trace("END   addAction");
        }

        /**
         * POS商品セット構成マスタ一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1023" );

            $mst5101 = new Mst5101();

            $postArray = array(
                'organization_id'       => parent::escStr( $_POST['organizationId'] ),
                'prod_cd'               => parent::escStr( $_POST['prodCode'] ),
                'setprod_cd_bef'        => parent::escStr( $_POST['setProdCodeBef']),
                'setprod_cd'            => parent::escStr( $_POST['setprodCode'] ),
                'setprod_amount'        => parent::escStr( $_POST['setprodAmount'] ),
            );

            $messege = $mst5101->modUpdateData($postArray);
            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * POS商品セット構成マスタ一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1024" );

            $mst5101 = new Mst5101();

            $postArray = array(
                'organization_id'       => parent::escStr( $_POST['organizationId'] ),
                'prod_cd'               => parent::escStr( $_POST['prodCode'] ),
                'setprod_cd_bef'        => parent::escStr( $_POST['setProdCodeBef']),
            );

            $messege = $mst5101->delUpdateData($postArray);

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
                'prodCode'             => parent::escStr( $_POST['searchProdCode'] ),
                'prodName'             => parent::escStr( $_POST['searchProdName'] ),
                'setprodCode'          => parent::escStr( $_POST['searchSetProdCode'] ),
                'setprodName'          => parent::escStr( $_POST['searchSetProdName'] ),
                'organizationID'       => parent::escStr( $_POST['searchOrganizationID'] ),
                'sort'                 => parent::escStr( $_POST['sortConditions'] ),
            );
            //--------------------------------------------------------------->
            $mst5101 = new Mst5101();

            // 登録用組織プルダウン
            $abbreviatedList = $mst5101->setPulldown->getSearchOrganizationList( 'registration', true, true );

            // POS商品セット構成マスタ一覧データ取得
            $mst5101AllList = $mst5101->getListData($searchArray);

            // POS商品セット構成マスタ一覧一覧レコード数
            $mst5101RecordCnt = count($mst5101AllList);

            // 表示レコード数
            $pagedRecordCnt = parent::escStr( $_POST['displayRecordCnt'] );

            // 指定ページ
            $pageNo = parent::escStr( $_POST['displayPageNo'] );

            // シーケンスIDName
            $idName = "prod_cd";

            $mst5101List = $this->refineListDisplayNoSpecifiedPage($mst5101AllList, $idName, $pagedRecordCnt, $pageNo);
            $mst5101List = $this->modBtnDelBtnDisabledCheck($mst5101List);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($mst5101List) >= 15)
            {
                $isScrollBar = true;
            }

            $regFlag = count( $abbreviatedList ) -1;
            //--------------------------------------------------------------->

            $Log->trace("END   inputAreaAction");
            
            require_once './View/Mst5101InputPanel.html';
        }

        /**
         * POS商品セット構成マスタ一覧画面初期表示
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $mst5101 = new Mst5101();

            // 検索プルダウン
            $abbreviatedNameList = $mst5101->setPulldown->getSearchOrganizationList( 'reference', true, true ); // 組織略称名リスト

            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;

            $searchArray = array(
                'codeID'              => '',
                'organizationID'      => '',
                'sort'                => '',
            );

            // POS商品セット構成マスタ一覧データ取得
            $mst5101AllList = $mst5101->getListData($searchArray);

            // POS商品セット構成マスタ一覧レコード数
            $mst5101RecordCnt = count($mst5101AllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($mst5101RecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "prod_cd";

            $mst5101List = $this->refineListDisplayNoSpecifiedPage($mst5101AllList, $idName, $pagedRecordCnt, $pageNo);
            $mst5101List = $this->modBtnDelBtnDisabledCheck($mst5101List);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($mst5101List) >= 11)
            {
                $isScrollBar = true;
            }

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($mst5101RecordCnt /  $pagedRecordCnt);

            $mst5101Array = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 登録用組織プルダウン
            $abbreviatedList = $mst5101->setPulldown->getSearchOrganizationList( 'registration', true, true );
            $regFlag = count( $abbreviatedList ) -1;
            
            // 修正登録用商品プルダウン
            $mst0201DataList = $mst5101->setPulldown->getSearchMst0201List();
            // 追加登録用商品プルダウン（JSON）
            $json_mst0201DataList = json_encode($mst0201DataList, JSON_UNESCAPED_UNICODE);
            
            $headerArray = $this->changeHeaderItemMark(0);

            $mst5101NoSortFlag = false;

            $mst5101_no = 0;
            $display_no =0;

            $headerArray = $this->changeHeaderItemMark(0);

            $mst5101NoSortFlag = false;

            $mst5101_no = 0;
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/Mst5101Panel.html';
        }

        /**
         * POS商品セット構成マスタ一覧更新
         * @note     パラメータは、View側で使用
         * @param    役職リスト(組織ID/役職ID)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $searchArray = array(
                'prodCode'             => parent::escStr( $_POST['searchProdCode'] ),
                'prodName'             => parent::escStr( $_POST['searchProdName'] ),
                'setprodCode'          => parent::escStr( $_POST['searchSetProdCode'] ),
                'setprodName'          => parent::escStr( $_POST['searchSetProdName'] ),
                'organizationID'       => parent::escStr( $_POST['searchOrganizationID'] ),
                'sort'                 => parent::escStr( $_POST['sortConditions'] ),
            );
            $mst5101 = new Mst5101();

            // シーケンスIDName
            $idName = "prod_cd";

            // POS商品セット構成マスタ一覧データ取得
            $mst5101SearchList = $mst5101->getListData($searchArray);
            
            // POS商品セット構成マスタ一覧検索後のレコード数
            $mst5101SrcListCnt = count($mst5101SearchList);
            // POS商品セット構成マスタ一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後のPOS商品セット構成マスタレコードに対するページ数
            $pagedCnt = ceil($mst5101SrcListCnt /  $pagedRecordCnt);

//            if($addFlag)
//            {
//                // 新規追加後の最新データ役職IDを取得
//                $positionLastId = $position->getLastid();
//                $this->pageNoWhenUpdating($positionSearchList, $idName, $positionLastId, $pagedRecordCnt, $pagedCnt);
//            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $mst5101List = $this->refineListDisplayNoSpecifiedPage($mst5101SearchList, $idName, $pagedRecordCnt, $pageNo);
            $mst5101List = $this->modBtnDelBtnDisabledCheck($mst5101List);
            
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($mst5101List) >= 11)
            {
                $isScrollBar = true;
            }
            
            // 表示レコード数
            $mst5101ListCount = count($mst5101List);

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $mst5101Array = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // 登録用組織プルダウン
            $abbreviatedList = $mst5101->setPulldown->getSearchOrganizationList( 'registration', true, true );
            $regFlag = count( $abbreviatedList ) -1;
            
             // 修正登録用商品プルダウン
            $mst0201DataList = $mst5101->setPulldown->getSearchMst0201List();
            // 追加登録用商品プルダウン（JSON）
            $json_mst0201DataList = json_encode($mst0201DataList, JSON_UNESCAPED_UNICODE);
           
            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $mst5101NoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $mst5101_no = 0;

            $display_no = $this->getDisplayNo( $mst5101SrcListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/Mst5101TablePanel.html';
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
         * @param    ソート条件番号(「組織」押下時：1,2/「商品コード」押下時：3,4/「商品名」押下時：5,6/「セット商品コード」押下時：7,8/「セット商品名」押下時：9,10)
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        private function changeHeaderItemMark($sortNo)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeHeaderItemMark");
            
            // 初期化
            $headerArray = array(
                    'mst5101OrganizationSortMark'      => '',
                    'mst5101ProdCodeSortMark'          => '',
                    'mst5101ProdNameSortMark'          => '',
                    'mst5101SetProdCodeSortMark'       => '',
                    'mst5101SetProdNameSortMark'       => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1] = "mst5101OrganizationSortMark";
                $sortList[2] = "mst5101OrganizationSortMark";
                $sortList[3] = "mst5101ProdCodeSortMark";
                $sortList[4] = "mst5101ProdCodeSortMark";
                $sortList[5] = "mst5101ProdNameSortMark";
                $sortList[6] = "mst5101ProdNameSortMark";
                $sortList[7] = "mst5101SetProdCodeSortMark";
                $sortList[8] = "mst5101SetProdCodeSortMark";
                $sortList[9] = "mst5101SetProdNameSortMark";
                $sortList[10] = "mst5101SetProdNameSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }
            
            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
            
        }

        /**
         * 登録用POS商品リスト情報の更新
         * @note     入力画面で組織プルダウンの値を変更した時にPOS商品情報を更新する
         * @return   POS商品情報
         */
        public function searchMst0201OptionAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchMst0201OptionAction");
            $Log->info( "MSG_INFO_1086_1" );
            
            $mst0201 = new Mst0201();

            $organization_id = parent::escStr( $_POST['organization_id'] );
            
            // 登録用POS商品部門プルダウン
            $mst1201DataList        = $mst0201->setPulldown->getSearchMst1201List( $organization_id );
            // JSONに変換
            $json_mst1201DataList = json_encode($mst1201DataList);

            require_once './View/Common/SearchMst1201Option.php';
            $Log->trace("END searchMst0201OptionAction");
        }
    }
?>
