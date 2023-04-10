<?php
    /**
     * @file      セキュリティコントローラ
     * @author    USE S.Nakamura
     * @date      2016/07/14
     * @version   1.00
     * @note      セキュリティ情報の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // セキュリティモデル
    require './Model/Security.php';
    
    /**
     * セキュリティコントローラクラス
     * @note   セキュリティの新規登録/修正/削除を行う
     */
    class SecurityController extends BaseController
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
         * セキュリティ一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1090" );

            $startFlag = true;
            if(isset($_POST['back']))
            {
                $startFlag = false;
            }

            $this->initialDisplay();
            $Log->trace("END   showAction");
        }
        
        /**
         * セキュリティ一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            $Log->info( "MSG_INFO_1091" );

            if(isset($_POST['displayPageNo']))
            {
                $_SESSION['PAGE_NO'] = parent::escStr( $_POST['displayPageNo'] );

            }
            if(isset($_POST['displayRecordCnt']))
            {
                $_SESSION['DISPLAY_RECORD_CNT'] = parent::escStr( $_POST['displayRecordCnt'] );

            }
            $this->initialList();
  
            $Log->trace("END searchAction");
        }

        /**
         * セキュリティ  入力画面表示処理
         * @return    なし
         */
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addInputAction" );
            $Log->info( "MSG_INFO_1092" );

            $securityID = parent::escStr( $_POST['securityID']);
            $this->securityEditPanelDisplay( $securityID );

            $Log->trace("END addInputAction");
        }


        /**
         * セキュリティマスタ新規登録処理
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1093" );

            // セキュリティマスタヘッダ部設定
            $postArray = $this->creatParameter();
            
            // セキュリティマスタ詳細の初期化
            $referenceList = array();
            $registrationList = array();
            $deleteList = array();
            $approvalList = array();
            $printingList = array();
            $outputList = array();
            $accessIDList = array();

            // セキュリティマスタ詳細部設定
            $this->creatParameterDetail( $referenceList, $registrationList, $deleteList, 
                                         $approvalList, $printingList, $outputList, $accessIDList );
            
            $security = new security();

            // 新規登録
            $messege = $security->addNewData($postArray,$referenceList, $registrationList, $deleteList, 
                                             $approvalList, $printingList, $outputList, $accessIDList );

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
         * セキュリティマスタ画面修正処理
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1094" );

            // セキュリティマスタヘッダ部設定
            $postArray = $this->creatParameter();
            
            // セキュリティマスタ詳細の初期化
            $referenceList = array();
            $registrationList = array();
            $deleteList = array();
            $approvalList = array();
            $printingList = array();
            $outputList = array();
            $accessIDList = array();

            // セキュリティマスタ詳細部設定
            $this->creatParameterDetail( $referenceList, $registrationList, $deleteList, 
                                         $approvalList, $printingList, $outputList, $accessIDList );
            
            $security = new security();
            
            // 更新処理
            $messege = $security->modUpdateData( $postArray,$referenceList, $registrationList, $deleteList, 
                                                 $approvalList, $printingList, $outputList, $accessIDList );

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
         * セキュリティマスタ画面削除処理
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1095" );

            $security = new security();

            // セキュリティマスタヘッダ部設定
            $postArray = $this->creatParameter();

            // 削除処理
            $messege = $security->delUpdateData( $postArray );

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
         * セキュリティマスタ一覧画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $security = new Security();

            $searchArray = array(
                'is_del'                       => 0,
                'securityID'                   => parent::escStr( $_POST['searchSecurityID'] ),
                'searchClassID'                => parent::escStr( $_POST['searchClassID'] ),
                'organizationID'               => parent::escStr( $_POST['searchOrganizationID'] ),
                'security'                     => parent::escStr( $_POST['searchSecurityName'] ),
                'optionName'                   => parent::escStr( $_POST['searchoptionName'] ),
                'comment'                      => parent::escStr( $_POST['searchComment'] ),
                'reference'                    => parent::escStr( $_POST['searchReference'] ),
                'registration'                 => parent::escStr( $_POST['searchRegistration'] ),
                'delete'                       => parent::escStr( $_POST['searchDelete'] ),
                'approval'                     => parent::escStr( $_POST['searchApproval'] ),
                'printing'                     => parent::escStr( $_POST['searchPrinting'] ),
                'output'                       => parent::escStr( $_POST['searchOutput'] ),
            );

            $is_del = parent::escStr( $_POST['is_del'] );

            // セキュリティ一覧データ取得
            $securityDataList = $security->getListData( $searchArray );

            // セキュリティ一覧レコード数
            $recordCnt = count($securityDataList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo( $recordCnt, $pagedRecordCnt );

            // シーケンスIDName
            $idName = "security_id";

            $securityDataList = $this->refineListDisplayNoSpecifiedPage( $securityDataList, $idName, $pagedRecordCnt, $pageNo );
            $securityDataList = $this->modBtnDelBtnDisabledCheck($securityDataList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($securityDataList) >= 15)
            {
                $isScrollBar = true;
            }

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            $pagedCnt = ceil($recordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            $headerArray = $this->changeHeaderItemMark(0);
            
            $securityNoSortFlag = false;

            // NO表示用
            $display_no =0;

            $securityProcess = new SecurityProcess();
            $accessAuthorityList = $securityProcess->getAccessAuthorityList();

            // 検索用プルダウンデータ生成
            $searchClassList          = $security->getSearchClassification( );
            $abbreviatedNameList      = $security->setPulldown->getSearchOrganizationList( 'reference', true, true );    // 組織略称名リスト
            $securityList             = $security->setPulldown->getSearchSecurityList( 'reference' );              // セキュリティリスト
            $optionNameList           = $security->setPulldown->getSearchOptionNameList( 'reference' );            // 表示項目リスト
            $referenceList            = $security->getSearchList( $accessAuthorityList['reference'] );             // 参照リスト
            $registrationList         = $security->getSearchList( $accessAuthorityList['registration'] );          // 登録リスト
            $deleteList               = $security->getSearchList( $accessAuthorityList['delete'] );                // 削除リスト
            $approvalList             = $security->getSearchList( $accessAuthorityList['approval'] );              // 承認リスト
            $printingList             = $security->getSearchList( $accessAuthorityList['printing'] );              // 印刷リスト
            $outputList               = $security->getSearchList( $accessAuthorityList['output'] );                // 出力リスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;
            
            $Log->trace("END   initialDisplay");
            
            require_once './View/SecurityPanel.html';
        }

        /**
         * セキュリティテーブル画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function initialList( $messege = "MSG_BASE_0000")
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialList");

            $security = new security();

            // シーケンスIDName
            $idName = "security_id";

            $searchArray = array(
                'is_del'                       => parent::escStr( $_POST['searchDelF'] ) === 'true' ? 1 : 0,
                'securityID'                   => parent::escStr( $_POST['searchSecurityID'] ),
                'searchClassID'                => parent::escStr( $_POST['searchClassID'] ),
                'organizationName'             => parent::escStr( $_POST['searchOrganizationID'] ),
                'security'                     => parent::escStr( $_POST['searchSecurityName'] ),
                'optionName'                   => parent::escStr( $_POST['searchoptionName'] ),
                'comment'                      => parent::escStr( $_POST['searchComment'] ),
                'reference'                    => parent::escStr( $_POST['searchReference'] ),
                'registration'                 => parent::escStr( $_POST['searchRegistration'] ),
                'delete'                       => parent::escStr( $_POST['searchDelete'] ),
                'approval'                     => parent::escStr( $_POST['searchApproval'] ),
                'printing'                     => parent::escStr( $_POST['searchPrinting'] ),
                'output'                       => parent::escStr( $_POST['searchOutput'] ),
                'sort'                         => parent::escStr( $_POST['sortConditions'] ),
            );

            // セキュリティ一覧データ取得
            $securityAllList = $security->getListData($searchArray);

            // セキュリティ一覧レコード数
            $securityRecordCnt = count($securityAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 検索結果後のセキュリティレコードに対するページ数
            $pagedCnt = ceil($securityRecordCnt /  $pagedRecordCnt);

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);

            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $securityDataList = $this->refineListDisplayNoSpecifiedPage($securityAllList, $idName, $pagedRecordCnt, $pageNo);
            $securityDataList = $this->modBtnDelBtnDisabledCheck($securityDataList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($securityDataList) >= 11)
            {
                $isScrollBar = true;
            }

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);

            // ソート実行判定フラグ
            $securityNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );
            
            $security_no = 0;

            $display_no = $this->getDisplayNo( $securityRecordCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/SecurityTablePanel.html';
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
            }

            $Log->trace("END   initialList");
        }

        /**
         * セキュリティマスタ入力画面(更新/削除)
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function securityEditPanelDisplay( $securityID )
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START securityEditPanelDisplay" );
            
            $security = new security();
            
            // ログインユーザのセキュリティマスタ画面のアクセス権限を取得
            $loginSecurityID = isset( $_SESSION["SECURITY_ID"] ) ? $_SESSION["SECURITY_ID"] : 0; 
            $authority  = $security->getAuthority( $loginSecurityID );
            
            // 登録用組織略称名プルダウン
            $abbreviatedNameList    = $security->setPulldown->getSearchOrganizationList( 'registration', true, true );
            // 表示項目リスト
            $optionNameList         = $security->setPulldown->getSearchOptionNameList( 'registration' );
            // 参照リスト
            $referenceList          = $security->getSearchList( $authority['reference']);
            // 登録リスト
            $registrationList       = $security->getSearchList( $authority['registration'] );
            // 削除リスト
            $deleteList             = $security->getSearchList( $authority['delete'] );
            // 承認リスト
            $approvalList           = $security->getSearchList( $authority['approval'] );
            // 印刷リスト
            $printingList           = $security->getSearchList( $authority['printing'] );
            // 出力リスト
            $outputList             = $security->getSearchList( $authority['output'] );

            // 編集項目の一覧取得
            $authorityNameList = $security->getAccessList( $securityID );
            $nameList   = $security->getNameList( $securityID );

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( count($authorityNameList) >= 11)
            {
                $isScrollBar = true;
            }


            $add_disabled = $this->securitydisable($nameList['organization_id'], $_SESSION["REGISTRATION"] );
            $del_disabled = $this->securitydisable($nameList['organization_id'], $_SESSION["DELETE"] );
            
            // 新規登録で、登録可能な組織がある場合、登録ボタンの押下を許可する
            if( 0 < count( $abbreviatedNameList ) && ( $securityID == 0 ) )
            {
                $add_disabled = "";
            }

            if ($nameList['is_del'] == 1) 
            {
                $add_disabled = "disabled";
                $del_disabled = "disabled";
            }


            $Log->trace( "END securityEditPanelDisplay" );
            
            require_once './View/SecurityInputPanel.html';
        }

        /**
         * セキュリティマスタ入力画面(更新/削除)
         * @note     パラメータは、View側で使用
         * @param    $disabled
         * @return   無
         */
        private function securitydisable($disabled, $accessList)
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START securitydisable" );
            
            // アクセス権限によるボタン制御
            $disabledMsg = "disabled";
            foreach( $accessList as $reg )
            {
                if($disabled == $reg['organization_id' ] )
                {
                    $disabledMsg = "";
                }
            }

            $Log->trace( "END securitydisable" );
            return $disabledMsg;
        }


        /**
         * ヘッダー部分ソート時のマーク変更
         * @note     パラメータは、View側で使用
         * @param    ソート条件番号(「No」押下時：1/「区分」押下時：3/「組織名」押下時：5/「セキュリティ名」押下時：7/「表示項目」押下時：9/「参照」押下時：11 「登録」押下時：13 「削除」押下時：15 「承認」押下時：17 「印刷」押下時：19 「出力」押下時：21 「コメント」押下時：23 「表示順」押下時：25 「状態」押下時：27)
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        private function changeHeaderItemMark($sortNo)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeHeaderItemMark");

            // 初期化
            $headerArray = array(
                    'securityNoSortMark'           => '',
                    'securityClassSortMark'        => '',
                    'securityOrganizationSortMark' => '',
                    'securityNameSortMark'         => '',
                    'securityOptionNameSortMark'   => '',
                    'securityReferenceSortMark'    => '',
                    'securityRegistrationSortMark' => '',
                    'securityDeleteSortMark'       => '',
                    'securityApprovalSortMark'     => '',
                    'securityPrintingSortMark'     => '',
                    'securityOutputSortMark'       => '',
                    'securityCommentSortMark'      => '',
                    'securityDispOrderSortMark'    => '',
                    'securityStateSortMark'        => '',
            );

            if(!empty($sortNo))
            {
                $sortList[1] = "securityNoSortMark";
                $sortList[2] = "securityNoSortMark";
                $sortList[3] = "securityClassSortMark";
                $sortList[4] = "securityClassSortMark";
                $sortList[5] = "securityOrganizationSortMark";
                $sortList[6] = "securityOrganizationSortMark";
                $sortList[7] = "securityNameSortMark";
                $sortList[8] = "securityNameSortMark";
                $sortList[9] = "securityOptionNameSortMark";
                $sortList[10] = "securityOptionNameSortMark";
                $sortList[11] = "securityReferenceSortMark";
                $sortList[12] = "securityReferenceSortMark";
                $sortList[13] = "securityRegistrationSortMark";
                $sortList[14] = "securityRegistrationSortMark";
                $sortList[15] = "securityDeleteSortMark";
                $sortList[16] = "securityDeleteSortMark";
                $sortList[17] = "securityApprovalSortMark";
                $sortList[18] = "securityApprovalSortMark";
                $sortList[19] = "securityPrintingSortMark";
                $sortList[20] = "securityPrintingSortMark";
                $sortList[21] = "securityOutputSortMark";
                $sortList[22] = "securityOutputSortMark";
                $sortList[23] = "securityCommentSortMark";
                $sortList[24] = "securityCommentSortMark";
                $sortList[25] = "securityDispOrderSortMark";
                $sortList[26] = "securityDispOrderSortMark";
                $sortList[27] = "securityStateSortMark";
                $sortList[28] = "securityStateSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("END changeHeaderItemMark");
            return $headerArray;
        }
        
        /**
         * パラメータリスト作成
         * @return    $postArray
         */
        private function creatParameter()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START creatParameter" );

            $postArray = array(
                'securityID'        => parent::escStr( $_POST['securityID'] ),
                'organizationName'  => parent::escStr( $_POST['organization_id'] ),
                'security_name'     => parent::escStr( $_POST['security_name'] ),
                'optionName'        => parent::escStr( $_POST['optionName'] ),
                'comment'           => parent::escStr( $_POST['comment'] ),
                'dispOrder'         => parent::escStr( $_POST['dispOrder'] ),
                'updateTime'        => parent::escStr( $_POST['updateTime'] ),
                'isDel'             => 0,
                'user_id'           => $_SESSION["USER_ID"],
                'organization'      => $_SESSION["ORGANIZATION_ID"],
            );

            $Log->trace("END creatParameter");
            
            return $postArray;
        }
        
        /**
         * 詳細パラメータリスト作成
         * @return    なし
         */
        private function creatParameterDetail( &$referenceList, &$registrationList, &$deleteList, 
                                               &$approvalList, &$printingList, &$outputList, &$accessIDList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START creatParameterDetail" );

            $arrayCount = count($_POST['referenceList']);
            for($i = 0 ; $i < $arrayCount ; $i++ )
            {
                array_push( $referenceList ,            parent::escStr( $_POST['referenceList'][$i] ) );
                array_push( $registrationList ,         parent::escStr( $_POST['registrationList'][$i] ) );
                array_push( $deleteList ,               parent::escStr( $_POST['deleteList'][$i] ) );
                array_push( $approvalList ,             parent::escStr( $_POST['approvalList'][$i] ) );
                array_push( $printingList ,             parent::escStr( $_POST['printingList'][$i] ) );
                array_push( $outputList ,               parent::escStr( $_POST['outputList'][$i] ) );
                array_push( $accessIDList ,             parent::escStr( $_POST['accessIDList'][$i] ) );
            }

            $Log->trace("END creatParameterDetail");
        }
        
    }
?>