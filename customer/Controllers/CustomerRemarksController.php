<?php
    /**
     * @file      顧客備考マスタコントローラ
     * @author    K.Sakamoto
     * @date      2017/07/25
     * @version   1.00
     * @note      顧客備考マスタの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 顧客備考マスタ処理モデル
    require './Model/CustomerRemarks.php';

    /**
     * 顧客備考マスタコントローラクラス
     * @note   顧客備考の新規登録/修正/削除を行う
     */
    class CustomerRemarksController extends BaseController
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
         * 顧客備考一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1060" );
           
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }

        /**
         * 顧客備考一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            $Log->info( "MSG_INFO_1061" );

            $this->initialList(false);
            
            $Log->trace("END   searchAction");
        }

        /**
         * 顧客備考一覧画面登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1062" );

            $customerRemarks = new CustomerRemarks();

            $postArray = array(
                'custBType'                     => parent::escStr( $_POST['custBType'] ),
                'custBCd'                       => parent::escStr( $_POST['custBCd'] ),
                'custBNm'                       => parent::escStr( $_POST['custBNm'] ),
                'disabled'                      => 0,
                'userID'                        => $_SESSION["USER_ID"],
                'organizationID'                => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $customerRemarks->addNewData($postArray);
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
         * 顧客備考一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1063" );

            $customerRemarks = new CustomerRemarks();

            $postArray = array(
                'custBId'                       => parent::escStr( $_POST['custBId'] ),
                'custBType'                     => parent::escStr( $_POST['custBType'] ),
                'custBCd'                       => parent::escStr( $_POST['custBCd'] ),
                'custBNm'                       => parent::escStr( $_POST['custBNm'] ),
                'updateTime'                    => parent::escStr( $_POST['updateTime'] ),
                'userID'                        => $_SESSION["USER_ID"],
                'organizationID'                => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $customerRemarks->modUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   modAction");
        }

        /**
         * 顧客備考一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1064" );

            $customerRemarks = new CustomerRemarks();

            $postArray = array(
                'custBId'                   => parent::escStr( $_POST['custBId'] ),
                'custBType'                 => parent::escStr( $_POST['custBType'] ),
                'custBCd'                   => parent::escStr( $_POST['custBCd'] ),
                'updateTime'                => parent::escStr( $_POST['updateTime'] ),
                'disabled'                  => 9,
                'userID'                    => $_SESSION["USER_ID"],
                'organizationID'            => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $customerRemarks->delUpdateData($postArray);

            $this->initialList( false, $messege);

            $Log->trace("END   delAction");
        }

        /**
         * 顧客備考一覧画面復活
         * @return    なし
         */
        public function resAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START resAction" );
            $Log->info( "MSG_INFO_1064" );

            $customerRemarks = new CustomerRemarks();

            $postArray = array(
                'custBId'                   => parent::escStr( $_POST['custBId'] ),
                'custBType'                 => parent::escStr( $_POST['custBType'] ),
                'custBCd'                   => parent::escStr( $_POST['custBCd'] ),
                'updateTime'                => parent::escStr( $_POST['updateTime'] ),
                'disabled'                  => 0,
                'userID'                    => $_SESSION["USER_ID"],
                'organizationID'            => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $customerRemarks->resUpdateData($postArray);
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

//            $this->initialList( false, $messege);

            $Log->trace("END   resAction");
        }

        /**
         * 新規登録用エリアの更新
         * @note     顧客備考マスタを新規作成した場合、新規登録用エリアを更新する
         * @return   新規登録用エリア
         */
        public function inputAreaAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1065" );

            $customerRemarks = new CustomerRemarks();

            $searchArray = array(
                'cust_b_type'         => parent::escStr( $_POST['custBType'] ),
            );

           // 顧客備考一覧データ取得
            $customerRemarksList = $customerRemarks->getListData($searchArray);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($customerRemarksList) >= 11 )
            {
                $isScrollBar = true;
            }
            $isScrollBar = true;

            $Log->trace("END   inputAreaAction");
            
            require_once './View/CustomerRemarksInputPanel.html';
        }

        /**
         * 顧客備考一覧画面
         * @note     顧客備考画面全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $customerRemarks = new CustomerRemarks();

            $searchArray = array(
                'cust_b_type'                     => '01',
            );

           // 顧客備考一覧データ取得
            $customerRemarksList = $customerRemarks->getListData($searchArray);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($customerRemarksList) >= 11 )
            {
                $isScrollBar = true;
            }

            $Log->trace("END initialDisplay");
            require_once './View/CustomerRemarksPanel.html';
        }

        /**
         * 顧客備考一覧更新
         * @note     顧客備考画面の一覧部分のみの更新
         * @param    $addFlag           新規登録フラグ true：新規登録  false：新規登録以外
         * @param    $messege           DBの更新結果(データ指定がない場合、デフォルト値[MSG_BASE_0000]を設定)
         * @return   無
         */
        private function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $customerRemarks = new CustomerRemarks();

            $searchArray = array(
                'cust_b_type'         => parent::escStr( $_POST['custBType'] ),
            );

           // 顧客備考一覧データ取得
            $customerRemarksList = $customerRemarks->getListData($searchArray);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($customerRemarksList) >= 11 )
            {
                $isScrollBar = true;
            }

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/CustomerRemarksTablePanel.html';
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
                require_once './View/CustomerRemarksTablePanel.html';
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
                    'custBCdSortMark'         => '',
                    'custBNmSortMark'         => '',
            );
            if(!empty($sortNo))
            {
                $sortList[3] = "customerRemarksStateSortMark";
                $sortList[4] = "customerRemarksStateSortMark";
                $sortList[5] = "custBCdSortMark";
                $sortList[6] = "custBCdSortMark";
                $sortList[9] = "custBNmSortMark";
                $sortList[10] = "custBNmSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
