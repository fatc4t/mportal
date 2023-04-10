<?php
    /**
     * @file      エリアマスタコントローラ
     * @author    millionet oota
     * @date      2016/07/26
     * @version   1.00
     * @note      エリアマスタの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // エリアマスタモデル
    require './Model/Area.php';
    
    /**
     * エリアマスタコントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class AreaController extends BaseController
    {

        protected $area = null;     ///< エリアマスタモデル

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseCheckAlertControllerのコンストラクタ
            parent::__construct();
            global $area;
            $area = new Area();
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
         * エリアマスタ一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START area showAction");

            $startFlag = true;
            
            $this->initialDisplay($startFlag);
            $Log->trace("END area showAction");
        }
        
        /**
         * エリアマスタ一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START area searchAction" );
            
            $startFlag = false;
            
            if(isset($_POST['displayPageNo']))
            {
                $_SESSION['PAGE_NO'] = $_POST['displayPageNo'];
            }
            if(isset($_POST['displayRecordCnt']))
            {
                $_SESSION['DISPLAY_RECORD_CNT'] = $_POST['displayRecordCnt'];
            }
            
            $this->initialDisplay($startFlag);
            
            $Log->trace("END area searchAction");
            
        }

        /**
         * エリアマスタ入力画面遷移
         * @return    なし
         */
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START area addInputAction" );

            $this->AreaInputPanelDisplay();

            $Log->trace("END area addInputAction");
            
        }

        /**
         * エリアマスタ新規登録処理
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START area addAction" );

            $area = new Area();

            $startFlag = true;
            $message = "MSG_BASE_0000";
            
            $postArray = array(
                'group_id'                  => parent::escStr( $_POST['groupID'] ),
                'group_name'                => parent::escStr( $_POST['groupName'] ),
                'menber_id'                 => parent::escStr( $_POST['selectUser'] ),
                'menber_name'               => parent::escStr( $_POST['selectLabel'] ),
                'disp_order'                => parent::escStr( $_POST['dispOrder'] ),
                'registration_user_id'      => $_SESSION["USER_ID"],
                'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                'update_user_id'            => $_SESSION["USER_ID"],
                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
            );
            
            // 記事を登録
            $message = $area->addNewData($postArray);

            if($message === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }
            $Log->trace("END area addAction");
        }

        /**
         * エリアマスタ更新処理
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START area modAction" );

            $area = new Area();

            $startFlag = true;

            $postArray = array(
                'group_id'                  => parent::escStr( $_POST['groupID'] ),
                'group_name'                => parent::escStr( $_POST['groupName'] ),
                'menber_id'                 => parent::escStr( $_POST['selectUser'] ),
                'menber_name'               => parent::escStr( $_POST['selectLabel'] ),
                'disp_order'                => parent::escStr( $_POST['dispOrder'] ),
                'update_user_id'            => $_SESSION["USER_ID"],
                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                'update_time'               => parent::escStr( $_POST['update_time'] ),
            );

            // 記事を更新
            $message = $area->updateData($postArray);
            
            if($message === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END area modAction");
        }

        /**
         * エリアマスタ削除処理
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START area delAction" );

            $area = new Area();

            $startFlag = true;

            $postArray = array(
                'group_id' => parent::escStr( $_POST['groupID'] ),
            );

            // 記事を削除
            $message = $area->delData($postArray);

            if($message === "MSG_BASE_0000")
            {
                // 削除成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END area delAction");
        }

        /**
         * エリアマスタ一覧検索
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        private function initialDisplay($startFlag)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START area initialDisplay");

            // エリアマスタモデルインスタンス化
            $area = new Area();

            // 一覧表用検索項目
            $searchArray = array(
                'area_name'           => parent::escStr( $_POST['areaName'] ),
                'organization_name'   => parent::escStr( $_POST['organizationName'] ),
                'sort'                 => parent::escStr( $_POST['sortConditions'] ),
            );

            // エリアマスタ一覧データ取得
            $areaAllList = $area->getListData($searchArray);
            // エリアマスタレコード数
            $areaRecordCnt = count($areaAllList);
            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            if($startFlag){
                // 初期表示の場合
                $pageNo = $this->getDuringTransitionPageNo($areaRecordCnt, $pagedRecordCnt);
            }else{
                // その他の場合
                $pageNo = $_SESSION["PAGE_NO"];
            }

            // シーケンスIDName
            $idName = "area_id";
            // 一覧表
            $areaList = $this->refineListDisplayNoSpecifiedPage($areaAllList, $idName, $pagedRecordCnt, $pageNo);
            $areaList = $this->modBtnDelBtnDisabledCheck($areaList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($areaList) >= 15)
            {
                $isScrollBar = true;
            }

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
            // ページ数
            $pagedCnt = ceil($areaRecordCnt /  $pagedRecordCnt);
            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
            // ソート時のマーク変更メソッド
            if($startFlag){
                // ソートマーク初期化
                $headerArray = $this->changeHeaderItemMark(0);
                // NO表示用
                $display_no = 0;
                // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
                $areaNoSortFlag = false;
            }else{
                // 対象項目へマークを付与
                $headerArray = $this->changeHeaderItemMark(parent::escStr( $_POST['sortConditions'] ));
                // NO表示用
                $display_no = $this->getDisplayNo( $areaRecordCnt, $pagedRecordCnt, $pageNo, parent::escStr( $_POST['sortConditions'] ) );
                // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
                $areaNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;
            }

            if($startFlag){
                require_once './View/AreaPanel.html';
            }else{
                require_once './View/AreaTablePanel.html';
            }

            $Log->trace("END area initialDisplay");
        }

        /**
         * エリアマスタ入力画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function areaInputPanelDisplay()
        {
            global $Log, $TokenID; // グローバル変数宣言
            $Log->trace( "START areaInputPanelDisplay" );

            $area = new Area();

            $areaDataList = array();
            // 選択用空リスト
            $selectUserNameList = array();
            
            // 検索用組織プルダウン
            $abbreviatedNameList = $area->setPulldown->getSearchOrganizationList( 'reference', true );      // 組織略称名リスト

            // 修正時フラグ（false）
            $CorrectionFlag = false;
            // 登録データ更新時
            if(!empty($_POST['areaID']))
            {
                // エリアマスタデータを取得
                $areaDataList = $area->getAreaData( parent::escStr( $_POST['areaID'] ) );
                
                // 対象者リストを展開
                $userList =  explode(',',$areaDataList['menber_id']);
                $userLabel =  explode(',',$areaDataList['menber_name']);

                                // 対象者情報を登録
                if($userList[0] != ""){
                    // 対象者データを再構成
                    for ($hc = 0; $hc < count($userList); $hc++){

                        if($userList[$hc] != ""){
                            // 1行分作成
                            $selectUserList = array(
                                    'id'      => $userList[$hc],
                                    'name'    => $userLabel[$hc],
                            );
                            //１行分搭載
                            array_push($selectUserNameList, $selectUserList);
                        }
                    }
                }
                
                // セキュリティに合わせて削除可能か設定←いずれ
                $del_disabled = true;
                // 更新フラグ
                $CorrectionFlag = true;
                
            }

            require_once './View/AreaInputPanel.html';
            
            $Log->trace("END areaInputPanelDisplay");
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
            $Log->trace("START area changeHeaderItemMark");

            // 初期化
            $headerArray = array(
                    'areaNoSortMark'            => '',
                    'titleSortMark'             => '',
                    'contentsSortMark'          => '',
                    'registrationTimeSortMark'  => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]  = "areaNoSortMark";
                $sortList[2]  = "areaNoSortMark";
                $sortList[3]  = "titleSortMark";
                $sortList[4]  = "titleSortMark";
                $sortList[5]  = "contentsSortMark";
                $sortList[6]  = "contentsSortMark";
                $sortList[7] = "registrationTimeSortMark";
                $sortList[8] = "registrationTimeSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START area changeHeaderItemMark");

            return $headerArray;
        }

        /**
         * 従業員リスト更新処理
         * @return    なし
         */
        public function searchUserListAction()
        {
            global $Log, $area; // グローバル変数宣言
            $Log->trace("START userListUpAction");
            $Log->info( "MSG_INFO_1426" );

            $searchUserNameList = array();
            
            // 選択されている種別でリスト生成
            if($_POST['unit'] == 1){
                // 全員の場合
                
            }else if($_POST['unit'] == 2){
                // 組織の場合 組織略称名リストを取得
                $oList = $area->setPulldown->getSearchOrganizationList( 'reference', true );

                // 共通リストに詰め直す
                foreach($oList as $dateListOne){
                    if($dateListOne["organization_id"] != ""){
                        $dateList = array(
                            'id'    => '1-'.$dateListOne["organization_id"],  // 組織ID
                            'name'  => $dateListOne["abbreviated_name"],      // 組織名
                        );
                        //１行分搭載
                        array_push($searchUserNameList, $dateList);
                    }
                }
            
            }else if($_POST['unit'] == 3){
                // 組織配下の場合
            }else if($_POST['unit'] == 4){
                // 役職の場合 役職リストを取得
                $pList = $area->setPulldown->getSearchPositionList( 'reference' );
                
                // 共通リストに詰め直す
                foreach($pList as $dateListOne){
                    if($dateListOne["position_id"] != ""){
                        $dateList = array(
                            'id'    => '2-'.$dateListOne["position_id"],   // 役職ID
                            'name'  => $dateListOne["position_name"],      // 役職名
                        );
                        //１行分搭載
                        array_push($searchUserNameList, $dateList);
                    }
                }
                
            }else if($_POST['unit'] == 5){
                // 個人の場合 スタッフリストを取得
                $sList = $area->setPulldown->getSearchUserListALL( parent::escStr( $_POST['searchOrgID']) );
                
                // 共通リストに詰め直す
                foreach($sList as $dateListOne){
                    $dateList = array(
                        'id'    => '3-'.$dateListOne["user_id"],   // ユーザーID
                        'name'  => $dateListOne["user_name"],      // ユーザー名
                    );
                    //１行分搭載
                    array_push($searchUserNameList, $dateList);
                }
                
            }

            $selectUserNameList = array();
            $userList =  explode(',',$_POST['selectUser']);
            $selectUserLabelList =  explode(',',$_POST['selectUserLabel']);
            
            if($userList[0] != ""){
                // 対象者データを再構成
                for ($hc = 0; $hc < count($userList); $hc++){
                    
                    if($userList[$hc] != ""){
                        // 1行分作成
                        $selectUserList = array(
                                'id'      => $userList[$hc],
                                'name'    => $selectUserLabelList[$hc],
                        );
                        //１行分搭載
                        array_push($selectUserNameList, $selectUserList);
                    }
                }
            }

            require_once '../master/View/Common/SearchList.php';

            $Log->trace("END   userListUpAction");
        }
        
    }
?>