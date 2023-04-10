<?php
    /**
     * @file      通達連絡閲覧コントローラ
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      通達連絡の閲覧/未読/既読を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 通達連絡閲覧モデル
    require './Model/NoticeContactBrowsing.php';
    
    /**
     * 通達連絡閲覧コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class NoticeContactBrowsingController extends BaseController
    {

        protected $noticeContactBrowsing = null;     ///< 通達連絡閲覧モデル

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseCheckAlertControllerのコンストラクタ
            parent::__construct();
            global $noticeContactBrowsing;
            $noticeContactBrowsing = new noticeContactBrowsing();
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
         * 通達連絡閲覧画面表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START noticeContactBrowsing addInputAction" );

            $this->initialDisplay();

            $Log->trace("END noticeContactBrowsing addInputAction");

        }
        
        /**
         * 通達連絡閲 未読・既読処理
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START noticeContacBrowsing modAction" );

            $noticeContactBrowsing = new NoticeContactBrowsing();

            $startFlag = true;

            $postArray = array(
                'notice_contact_id'         => parent::escStr( $_POST['noticeContactID'] ),
                'user_id'                   => $_SESSION["USER_ID"],
                'state'                     => parent::escStr( $_POST['state'] ),
                'update_user_id'            => $_SESSION["USER_ID"],
                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                'update_time'               => parent::escStr( $_POST['update_time'] ),
            );

            // 対象者リストを更新する
            $message = $noticeContactBrowsing->updateData($postArray);
            
            if($message === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay();
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END noticeContactBrowsing modAction");
        }

        /**
         * 通達連絡 閲覧入力画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID; // グローバル変数宣言
            $Log->trace( "START noticeContactBrowsingInputPanelDisplay" );

            $noticeContactBrowsing = new NoticeContactBrowsing();

            $mode = $_GET['mode'];
            
            if($mode == ""){
                $mode = $_POST['mode'];
            }
            
            $noticeContactID = $_GET['noticeContactID'];
            
            if($noticeContactID == ""){
                $noticeContactID = $_POST['noticeContactID'];
            }
            
            // 通達連絡閲覧データを取得
            $noticeContactBrowsingDataList = $noticeContactBrowsing->getNoticeContactBrowsingData( $noticeContactID );
            
            // 通達連絡詳細データを取得
            $noticeContactBrowsingDetailsDataList = $noticeContactBrowsing->getNoticeContactBrowsingDetailsData( $noticeContactID, $_SESSION["USER_ID"]);
                
            // 管理権限を持っている場合は、修正ＯＫ
            $CorrectionFlag = false;

            $security = $_SESSION["WRITE_MENU_LIST"];
            $securityLebel = $security['/noticeContact/index.php?param=NoticeContactBrowsing/show'];
            
            // 簡易アクセス権制御
            if ( $securityLebel == 1 ){
                // 編集フラグを設定
                $CorrectionFlag = true;
            }else{
                // 記事の作成者または編集者だった場合は、修正ＯＫ
                if($noticeContactBrowsingDataList["registration_user_id"] == $_SESSION["USER_ID"] || $noticeContactBrowsingDataList["update_user_id"] == $_SESSION["USER_ID"] ) {
                    // 編集可能に設定
                    $CorrectionFlag = true;
                }
            }
                
            // 未読者一覧
            $unreadList = $noticeContactBrowsing->getBrowsingList( $noticeContactID, 0 );
                
            // 既読者一覧
            $alreadyReadList = $noticeContactBrowsing->getBrowsingList( $noticeContactID, 1 );

            require_once './View/NoticeContactBrowsingPanel.html';
            
            $Log->trace("END noticeContactBrowsingInputPanelDisplay");
        }
        
    }
?>