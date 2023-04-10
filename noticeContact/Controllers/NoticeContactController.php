<?php
    /**
     * @file      通達連絡コントローラ
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      通達連絡の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 通達連絡モデル
    require './Model/NoticeContact.php';
    
    /**
     * 通達連絡コントローラクラス
     * @note   ログイン時のリクエストを振り分ける
     */
    class NoticeContactController extends BaseController
    {

        protected $noticeContact = null;     ///< 通達連絡モデル

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseCheckAlertControllerのコンストラクタ
            parent::__construct();
            global $noticeContact;
            $noticeContact = new NoticeContact();
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
         * 通達連絡一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START noticeContact showAction");

            $startFlag = true;
            
            $this->initialDisplay($startFlag);
            $Log->trace("END noticeContact showAction");
        }
        
        /**
         * 通達連絡一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START noticeContact searchAction" );
            
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
            
            $Log->trace("END noticeContact searchAction");
            
        }

        /**
         * 通達連絡入力画面遷移
         * @return    なし
         */
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START noticeContact addInputAction" );

            $this->NoticeContactInputPanelDisplay();

            $Log->trace("END noticeContact addInputAction");
            
        }

        /**
         * 通達連絡新規登録処理
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START noticeContact addAction" );

            $noticeContact = new NoticeContact();

            $startFlag = true;
            $message = "MSG_BASE_0000";
            
            $postArray = array(
                'title'                     => parent::escStr( $_POST['title'] ),
                'contents'                  => parent::escStr( $_POST['contents'] ),
                'thumbnail'                 => parent::escStr( $_POST['thumbnailId'] ),
                'file'                      => parent::escStr( $_POST['file'] ),
                'registration_user_id'      => $_SESSION["USER_ID"],
                'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                'update_user_id'            => $_SESSION["USER_ID"],
                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
            );
            
            // 記事を登録
            $noticeContactDataList = $noticeContact->addNewData($postArray);

            $userList =  explode(',',$_POST['selectUser']);

            if($userList[0] != ""){
                // 対象者情報を登録
                for ($hc = 0; $hc < count($userList); $hc++){
                    if($userList[$hc] != ""){
                        
                        // 種別ごとに判別
                        if(substr($userList[$hc], 0, 2) == '1-' ){
                            // 組織の場合、組織IDでスタッフを検索して登録
                            $sList = $noticeContact->setPulldown->getSearchUserList( substr($userList[$hc], 2, strlen($userList[$hc])));
                            
                            // スタッフ数分登録する
                            foreach($sList as $dateListOne){
                                
                                $state = "0";
                                
                                if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                                    // 発信者と対象者が同じ場合は既読の状態で登録
                                    $state = "1";
                                }
                                
                                // スタッフの場合そのまま登録
                                $postArray = array(
                                    'notice_contact_id'         => $noticeContactDataList["notice_contact_id"],
                                    'user_id'                   => $dateListOne["user_id"],
                                    'state'                     => $state,
                                    'reply'                     => "",
                                    'thumbnail'                 => null,
                                    'file'                      => null,
                                    'registration_user_id'      => $_SESSION["USER_ID"],
                                    'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                                    'update_user_id'            => $_SESSION["USER_ID"],
                                    'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                                );
                                // 登録
                                $message = $noticeContact->addNewDetailsData($postArray);
                            }
                            
                        }else if(substr($userList[$hc], 0, 2) == '2-' ){
                            // 役職の場合、役職IDでスタッフを検索して登録
                            $pList = $noticeContact->getPositionUser(substr($userList[$hc], 2, strlen($userList[$hc])));
                            
                            // スタッフ数分登録する
                            foreach($pList as $dateListOne){
                                
                                $state = "0";
                                
                                if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                                    // 発信者と対象者が同じ場合は既読の状態で登録
                                    $state = "1";
                                }
                                
                                // スタッフの場合そのまま登録
                                $postArray = array(
                                    'notice_contact_id'         => $noticeContactDataList["notice_contact_id"],
                                    'user_id'                   => $dateListOne["user_id"],
                                    'state'                     => $state,
                                    'reply'                     => "",
                                    'thumbnail'                 => null,
                                    'file'                      => null,
                                    'registration_user_id'      => $_SESSION["USER_ID"],
                                    'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                                    'update_user_id'            => $_SESSION["USER_ID"],
                                    'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                                );
                                // 登録
                                $message = $noticeContact->addNewDetailsData($postArray);
                            }
                            
                        }else if(substr($userList[$hc], 0, 2) == '4-' ){
                            // グループの場合、グループIDでスタッフを検索して登録
                            $gList = $noticeContact->getGroupUser(substr($userList[$hc], 2, strlen($userList[$hc])));
                            
                            // グループリスト分、展開しなおす
                            $groupUserList =  explode(',',$gList['menber_id']);

                            if($groupUserList[0] != ""){
                                // 対象者データを再構成
                                for ($hg = 0; $hg < count($groupUserList); $hg++){

                                    if($groupUserList[$hg] != ""){
                                        // 種別ごとに判別
                                        if(substr($groupUserList[$hg], 0, 2) == '1-' ){
                                            // 組織の場合、組織IDでスタッフを検索して登録
                                            $sList = $noticeContact->setPulldown->getSearchUserList( substr($groupUserList[$hg], 2, strlen($groupUserList[$hg])));

                                            // スタッフ数分登録する
                                            foreach($sList as $dateListOne){

                                                $state = "0";

                                                if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                                                    // 発信者と対象者が同じ場合は既読の状態で登録
                                                    $state = "1";
                                                }

                                                // スタッフの場合そのまま登録
                                                $postArray = array(
                                                    'notice_contact_id'         => $noticeContactDataList["notice_contact_id"],
                                                    'user_id'                   => $dateListOne["user_id"],
                                                    'state'                     => $state,
                                                    'reply'                     => "",
                                                    'thumbnail'                 => null,
                                                    'file'                      => null,
                                                    'registration_user_id'      => $_SESSION["USER_ID"],
                                                    'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                                                    'update_user_id'            => $_SESSION["USER_ID"],
                                                    'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                                                );
                                                // 登録
                                                $message = $noticeContact->addNewDetailsData($postArray);
                                            }

                                        }else if(substr($groupUserList[$hg], 0, 2) == '2-' ){
                                            // 役職の場合、役職IDでスタッフを検索して登録
                                            $pList = $noticeContact->getPositionUser(substr($groupUserList[$hg], 2, strlen($groupUserList[$hg])));

                                            // スタッフ数分登録する
                                            foreach($pList as $dateListOne){

                                                $state = "0";

                                                if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                                                    // 発信者と対象者が同じ場合は既読の状態で登録
                                                    $state = "1";
                                                }
                                                
                                                // スタッフの場合そのまま登録
                                                $postArray = array(
                                                    'notice_contact_id'         => $noticeContactDataList["notice_contact_id"],
                                                    'user_id'                   => $dateListOne["user_id"],
                                                    'state'                     => $state,
                                                    'reply'                     => "",
                                                    'thumbnail'                 => null,
                                                    'file'                      => null,
                                                    'registration_user_id'      => $_SESSION["USER_ID"],
                                                    'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                                                    'update_user_id'            => $_SESSION["USER_ID"],
                                                    'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                                                );
                                                // 登録
                                                $message = $noticeContact->addNewDetailsData($postArray);
                                            }

                                        }else{
                                            
                                            $state = "0";

                                            if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                                                // 発信者と対象者が同じ場合は既読の状態で登録
                                                $state = "1";
                                            }
                                            
                                            // スタッフの場合そのまま登録
                                            $postArray = array(
                                                'notice_contact_id'         => $noticeContactDataList["notice_contact_id"],
                                                'user_id'                   => substr($groupUserList[$hg], 2, strlen($groupUserList[$hg])),
                                                'state'                     => $state,
                                                'reply'                     => "",
                                                'thumbnail'                 => null,
                                                'file'                      => null,
                                                'registration_user_id'      => $_SESSION["USER_ID"],
                                                'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                                                'update_user_id'            => $_SESSION["USER_ID"],
                                                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                                            );
                                            // 登録
                                            $message = $noticeContact->addNewDetailsData($postArray);
                                        }
                                    }
                                }
                            }
                            
                        }else{
                            
                            $state = "0";

                            if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                                // 発信者と対象者が同じ場合は既読の状態で登録
                                $state = "1";
                            }
                            
                            // スタッフの場合そのまま登録
                            $postArray = array(
                                'notice_contact_id'         => $noticeContactDataList["notice_contact_id"],
                                'user_id'                   => substr($userList[$hc], 2, strlen($userList[$hc])),
                                'state'                     => $state,
                                'reply'                     => "",
                                'thumbnail'                 => null,
                                'file'                      => null,
                                'registration_user_id'      => $_SESSION["USER_ID"],
                                'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                                'update_user_id'            => $_SESSION["USER_ID"],
                                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                            );
                            // 登録
                            $message = $noticeContact->addNewDetailsData($postArray);
                        }
                    }
                }
            }else{
                // 対象者なしの場合は全員登録
                $sList = $noticeContact->getAllUser();

                // スタッフ数分登録する
                foreach($sList as $dateListOne){

                    $state = "0";

                    if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                        // 発信者と対象者が同じ場合は既読の状態で登録
                        $state = "1";
                    }
                    
                    // スタッフの場合そのまま登録
                    $postArray = array(
                        'notice_contact_id'         => $noticeContactDataList["notice_contact_id"],
                        'user_id'                   => $dateListOne["user_id"],
                        'state'                     => $state,
                        'reply'                     => "",
                        'thumbnail'                 => null,
                        'file'                      => null,
                        'registration_user_id'      => $_SESSION["USER_ID"],
                        'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                        'update_user_id'            => $_SESSION["USER_ID"],
                        'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                    );
                    // 登録
                    $message = $noticeContact->addNewDetailsData($postArray);
                }
            }

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
            $Log->trace("END noticeContact addAction");
        }

        /**
         * 通達連絡更新処理
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START noticeContact modAction" );

            $noticeContact = new NoticeContact();

            $startFlag = true;

            $postArray = array(
                'notice_contact_id'         => parent::escStr( $_POST['noticeContactID'] ),
                'title'                     => parent::escStr( $_POST['title'] ),
                'contents'                  => parent::escStr( $_POST['contents'] ),
                'thumbnail'                 => parent::escStr( $_POST['thumbnailId'] ),
                'update_user_id'            => $_SESSION["USER_ID"],
                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                'update_time'               => parent::escStr( $_POST['update_time'] ),
            );

            // 新規登録フラグ
            $addFlag = false;

            // 記事を更新
            $message = $noticeContact->updateData($postArray);
            
            // 対象者リストを一度削除
            $message = $noticeContact->delDataDetails($postArray);
            
            // 新しく登録する
            $userList =  explode(',',$_POST['selectUser']);

            if($userList[0] != ""){
                // 対象者情報を登録
                for ($hc = 0; $hc < count($userList); $hc++){
                    if($userList[$hc] != ""){
                        
                        // 種別ごとに判別
                        if(substr($userList[$hc], 0, 2) == '1-' ){
                            // 組織の場合、組織IDでスタッフを検索して登録
                            $sList = $noticeContact->setPulldown->getSearchUserList( substr($userList[$hc], 2, strlen($userList[$hc])));
                            
                            // スタッフ数分登録する
                            foreach($sList as $dateListOne){
                                
                                $state = "0";

                                if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                                    // 発信者と対象者が同じ場合は既読の状態で登録
                                    $state = "1";
                                }
                                
                                // スタッフの場合そのまま登録
                                $postArray = array(
                                    'notice_contact_id'         => parent::escStr( $_POST['noticeContactID'] ),
                                    'user_id'                   => $dateListOne["user_id"],
                                    'state'                     => $state,
                                    'reply'                     => "",
                                    'thumbnail'                 => null,
                                    'file'                      => null,
                                    'registration_user_id'      => $_SESSION["USER_ID"],
                                    'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                                    'update_user_id'            => $_SESSION["USER_ID"],
                                    'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                                );
                                // 登録
                                $message = $noticeContact->addNewDetailsData($postArray);
                            }
                            
                        }else if(substr($userList[$hc], 0, 2) == '2-' ){
                            // 役職の場合、役職IDでスタッフを検索して登録
                            $pList = $noticeContact->getPositionUser(substr($userList[$hc], 2, strlen($userList[$hc])));
                            
                            // スタッフ数分登録する
                            foreach($pList as $dateListOne){
                                
                                $state = "0";

                                if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                                    // 発信者と対象者が同じ場合は既読の状態で登録
                                    $state = "1";
                                }
                                
                                // スタッフの場合そのまま登録
                                $postArray = array(
                                    'notice_contact_id'         => parent::escStr( $_POST['noticeContactID'] ),
                                    'user_id'                   => $dateListOne["user_id"],
                                    'state'                     => $state,
                                    'reply'                     => "",
                                    'thumbnail'                 => null,
                                    'file'                      => null,
                                    'registration_user_id'      => $_SESSION["USER_ID"],
                                    'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                                    'update_user_id'            => $_SESSION["USER_ID"],
                                    'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                                );
                                // 登録
                                $message = $noticeContact->addNewDetailsData($postArray);
                            }
                            
                        }else if(substr($userList[$hc], 0, 2) == '4-' ){
                            // グループの場合、グループIDでスタッフを検索して登録
                            $gList = $noticeContact->getGroupUser(substr($userList[$hc], 2, strlen($userList[$hc])));
                            
                            // グループリスト分、展開しなおす
                            $userList =  explode(',',$gList['menber_id']);

                            if($userList[0] != ""){
                                // 対象者データを再構成
                                for ($hc = 0; $hc < count($userList); $hc++){

                                    if($userList[$hc] != ""){
                                        // 種別ごとに判別
                                        if(substr($userList[$hc], 0, 2) == '1-' ){
                                            // 組織の場合、組織IDでスタッフを検索して登録
                                            $sList = $noticeContact->setPulldown->getSearchUserList( substr($userList[$hc], 2, strlen($userList[$hc])));

                                            // スタッフ数分登録する
                                            foreach($sList as $dateListOne){

                                                $state = "0";

                                                if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                                                    // 発信者と対象者が同じ場合は既読の状態で登録
                                                    $state = "1";
                                                }
                                                
                                                // スタッフの場合そのまま登録
                                                $postArray = array(
                                                    'notice_contact_id'         => parent::escStr( $_POST['noticeContactID'] ),
                                                    'user_id'                   => $dateListOne["user_id"],
                                                    'state'                     => $state,
                                                    'reply'                     => "",
                                                    'thumbnail'                 => null,
                                                    'file'                      => null,
                                                    'registration_user_id'      => $_SESSION["USER_ID"],
                                                    'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                                                    'update_user_id'            => $_SESSION["USER_ID"],
                                                    'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                                                );
                                                // 登録
                                                $message = $noticeContact->addNewDetailsData($postArray);
                                            }

                                        }else if(substr($userList[$hc], 0, 2) == '2-' ){
                                            // 役職の場合、役職IDでスタッフを検索して登録
                                            $pList = $noticeContact->getPositionUser(substr($userList[$hc], 2, strlen($userList[$hc])));

                                            // スタッフ数分登録する
                                            foreach($pList as $dateListOne){

                                                $state = "0";

                                                if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                                                    // 発信者と対象者が同じ場合は既読の状態で登録
                                                    $state = "1";
                                                }
                                                
                                                // スタッフの場合そのまま登録
                                                $postArray = array(
                                                    'notice_contact_id'         => parent::escStr( $_POST['noticeContactID'] ),
                                                    'user_id'                   => $dateListOne["user_id"],
                                                    'state'                     => $state,
                                                    'reply'                     => "",
                                                    'thumbnail'                 => null,
                                                    'file'                      => null,
                                                    'registration_user_id'      => $_SESSION["USER_ID"],
                                                    'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                                                    'update_user_id'            => $_SESSION["USER_ID"],
                                                    'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                                                );
                                                // 登録
                                                $message = $noticeContact->addNewDetailsData($postArray);
                                            }

                                        }else{
                                            
                                            $state = "0";

                                            if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                                                // 発信者と対象者が同じ場合は既読の状態で登録
                                                $state = "1";
                                            }
                                                
                                            // スタッフの場合そのまま登録
                                            $postArray = array(
                                                'notice_contact_id'         => parent::escStr( $_POST['noticeContactID'] ),
                                                'user_id'                   => substr($userList[$hc], 2, strlen($userList[$hc])),
                                                'state'                     => $state,
                                                'reply'                     => "",
                                                'thumbnail'                 => null,
                                                'file'                      => null,
                                                'registration_user_id'      => $_SESSION["USER_ID"],
                                                'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                                                'update_user_id'            => $_SESSION["USER_ID"],
                                                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                                            );
                                            // 登録
                                            $message = $noticeContact->addNewDetailsData($postArray);
                                        }
                                    }
                                }
                            }
                            
                        }else{
                            
                            $state = "0";

                            if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                                // 発信者と対象者が同じ場合は既読の状態で登録
                                $state = "1";
                            }
                            
                            // スタッフの場合そのまま登録
                            $postArray = array(
                                'notice_contact_id'         => parent::escStr( $_POST['noticeContactID'] ),
                                'user_id'                   => substr($userList[$hc], 2, strlen($userList[$hc])),
                                'state'                     => $state,
                                'reply'                     => "",
                                'thumbnail'                 => null,
                                'file'                      => null,
                                'registration_user_id'      => $_SESSION["USER_ID"],
                                'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                                'update_user_id'            => $_SESSION["USER_ID"],
                                'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                            );
                            // 登録
                            $message = $noticeContact->addNewDetailsData($postArray);
                        }
                    }
                }
            }else{
                // 対象者なしの場合は全員登録
                $sList = $noticeContact->getAllUser();

                // スタッフ数分登録する
                foreach($sList as $dateListOne){

                    $state = "0";

                    if($dateListOne["user_id"] == $_SESSION["USER_ID"]){
                        // 発信者と対象者が同じ場合は既読の状態で登録
                        $state = "1";
                    }
                    
                    // スタッフの場合そのまま登録
                    $postArray = array(
                        'notice_contact_id'         => parent::escStr( $_POST['noticeContactID'] ),
                        'user_id'                   => $dateListOne["user_id"],
                        'state'                     => $state,
                        'reply'                     => "",
                        'thumbnail'                 => null,
                        'file'                      => null,
                        'registration_user_id'      => $_SESSION["USER_ID"],
                        'registration_organization' => $_SESSION["ORGANIZATION_ID"],
                        'update_user_id'            => $_SESSION["USER_ID"],
                        'update_organization'       => $_SESSION["ORGANIZATION_ID"],
                    );
                    // 登録
                    $message = $noticeContact->addNewDetailsData($postArray);
                }
            }
            
            if($message === "MSG_BASE_0000")
            {
                // 更新が成功したら古いフォルダを削除
                $del_directory = "./server/php/".$_SESSION['SCHEMA']. "/".parent::escStr( $_POST['oldThumbnailId'] );

                if(!empty(parent::escStr( $_POST['oldThumbnailId'] ))){
                    $this-> dir_delete($del_directory);
                }

                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END noticeContact modAction");
        }

        /**
         * 通達連絡削除処理
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START noticeContact delAction" );

            $noticeContact = new NoticeContact();

            $startFlag = true;

            $postArray = array(
                'notice_contact_id' => parent::escStr( $_POST['noticeContactID'] ),
                'thumbnail'         => parent::escStr( $_POST['thumbnail'] ),
                'file'              => parent::escStr( $_POST['file'] ),
            );

            // 記事を削除
            $message = $noticeContact->delData($postArray);

            // 記事詳細を削除
            $message = $noticeContact->delDataDetails($postArray);

            if($message === "MSG_BASE_0000")
            {
                // 一時フォルダを削除
                $del_directory = "./server/php/".$_SESSION['SCHEMA']. "/".parent::escStr( $_POST['oldThumbnailId'] );
                if(!empty(parent::escStr( $_POST['oldThumbnailId'] ))){
                    $this-> dir_delete($del_directory);
                }

                // もともと記事についていたフォルダを削除
                $del_directory = "./server/php/".$_SESSION['SCHEMA']. "/".parent::escStr( $_POST['thumbnailId'] );
                if(!empty(parent::escStr( $_POST['thumbnailId'] ))){
                    $this-> dir_delete($del_directory);
                }
                
                // 削除成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END noticeContact delAction");
        }

        /**
         * 通達連絡一覧検索
         * @note     パラメータは、View側で使用
         * @param    $startFlag (初期表示時フラグ)
         * @return   無
         */
        private function initialDisplay($startFlag)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START noticeContact initialDisplay");

            // 通達連絡モデルインスタンス化
            $noticeContact = new NoticeContact();
            
            // 自分のIDを設定
            $userID = $_SESSION["USER_ID"];
            
            // 新規登録権限
            $securityW = $_SESSION["WRITE_MENU_LIST"];
            $securityLebelW = $securityW['/noticeContact/index.php?param=NoticeContact/show'];

            $newFlg = false;

            if($securityLebelW != null && $securityLebelW <= 5){
                $newFlg = true;
            }
            
            $security = $_SESSION["ACCESS_MENU_LIST"];
            $securityLebel = $security['/noticeContact/index.php?param=NoticeContact/show'];
            
            $read = parent::escStr( $_POST['searchRead'] );
            $unread = parent::escStr( $_POST['searchUnread'] );
            
            // 検索用組織プルダウン
            $abbreviatedNameList = $noticeContact->setPulldown->getSearchOrganizationAllList( 'reference', true );      // 組織略称名リスト
            
            if(!empty(parent::escStr( $_POST['searchOrgID']))){
                // 検索用ユーザリスト
                $searchUserNameList = $noticeContact->setPulldown->getSearchUserList( parent::escStr( $_POST['searchOrgID']) );      // ユーザリスト
            }

            // 既読・未読フラグ
            if($startFlag){
                // 初期表示の場合
                $read = "true";
                $unread = "true";
            }

            // 一覧表用検索項目
            $searchArray = array(
                'title'                     => parent::escStr( $_POST['searchTitle'] ),
                'contents'                  => parent::escStr( $_POST['searchContents'] ),
                'file'                      => parent::escStr( $_POST['searchFile'] ),
                'sAppStartDate'             => parent::escStr( $_POST['searchSAppStartDate'] ),
                'eAppStartDate'             => parent::escStr( $_POST['searchEAppStartDate'] ),
                'sort'                      => parent::escStr( $_POST['sortConditions'] ),
                'user_id'                   => $userID,
                'restriction_user_id'       => parent::escStr( $_POST['searchUser'] ),
                'registration_organization' => parent::escStr( $_POST['searchOrgID'] ),
                'read'                      => $read,
                'unread'                    => $unread,
                'securityLebel'             => $securityLebel,
                'userID'                    => parent::escStr( $_POST['searchUser'] ), // 検索条件のスタッフリスト表示用
            );

            // 通達連絡一覧データ取得
            $noticeContactAllList = $noticeContact->getListData($searchArray);
            // 通達連絡レコード数
            $noticeContactRecordCnt = count($noticeContactAllList);
            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            if($startFlag){
                // 初期表示の場合
                $pageNo = $this->getDuringTransitionPageNo($noticeContactRecordCnt, $pagedRecordCnt);
            }else{
                // その他の場合
                $pageNo = $_SESSION["PAGE_NO"];
            }

            // シーケンスIDName
            $idName = "notice_contact_id";
            // 一覧表
            $noticeContactList = $this->refineListDisplayNoSpecifiedPage($noticeContactAllList, $idName, $pagedRecordCnt, $pageNo);
            $noticeContactList = $this->modBtnDelBtnDisabledCheck($noticeContactList);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($noticeContactList) >= 15)
            {
                $isScrollBar = true;
            }

            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
            // ページ数
            $pagedCnt = ceil($noticeContactRecordCnt /  $pagedRecordCnt);
            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
            // ソート時のマーク変更メソッド
            if($startFlag){
                // ソートマーク初期化
                $headerArray = $this->changeHeaderItemMark(0);
                // NO表示用
                $display_no = 0;
                // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
                $noticeContactNoSortFlag = false;
            }else{
                // 対象項目へマークを付与
                $headerArray = $this->changeHeaderItemMark(parent::escStr( $_POST['sortConditions'] ));
                // NO表示用
                $display_no = $this->getDisplayNo( $noticeContactRecordCnt, $pagedRecordCnt, $pageNo, parent::escStr( $_POST['sortConditions'] ) );
                // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
                $noticeContactNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;
            }

            if($startFlag){
                require_once './View/NoticeContactPanel.html';
            }else{
                require_once './View/NoticeContactTablePanel.html';
            }

            $Log->trace("END noticeContact initialDisplay");
        }

        /**
         * 通達連絡入力画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function noticeContactInputPanelDisplay()
        {
            global $Log, $TokenID; // グローバル変数宣言
            $Log->trace( "START noticeContactInputPanelDisplay" );

            $noticeContact = new NoticeContact();

            // 添付ファイル用Cookieの初期化
            setcookie('noticeContactSCHEMAName','', time()-3600);
            setcookie('noticeContactDirName','', time()-3600);

            $noticeContactDataList = array();
            // 選択用空リスト
            $selectUserNameList = array();
            
            // 検索用組織プルダウン
            $abbreviatedNameList = $noticeContact->setPulldown->getSearchOrganizationList( 'reference', true );      // 組織略称名リスト

            // 検索用グループプルダウン
            $groupList = $noticeContact->setPulldown->getSearchGroupList();

            // 登録セキュリティ
            $securityR = $_SESSION["WRITE_MENU_LIST"];
            $securityLebelR = $securityR['/noticeContact/index.php?param=NoticeContactBrowsing/show'];

            // 削除セキュリティ
            $securityD = $_SESSION["DELETE_MENU_LIST"];
            $securityLebelD = $securityD['/noticeContact/index.php?param=NoticeContactBrowsing/show'];
            
            // 修正時フラグ（false）
            $CorrectionFlag = false;
            // 登録データ更新時
            if(!empty($_POST['noticeContactID']))
            {
                // 通達連絡データを取得
                $noticeContactDataList = $noticeContact->getNoticeContactData( parent::escStr( $_POST['noticeContactID'] ) );
                
                // 通達連絡詳細データを取得(対象者リスト)
                $selectUserNameList = $noticeContact->getNoticeContactDetailesData( parent::escStr( $_POST['noticeContactID'] ) );
                
                // セキュリティに合わせて設定
                $writeFlag = false;
                $delFlag = false;
                
                // 簡易アクセス権制御
                if($securityLebelR != null && $securityLebelR <= 5){
                    // 編集フラグを設定
                    $writeFlag = true;
                }
                
                // 簡易アクセス権制御
                if($securityLebelD != null && $securityLebelD <= 5){
                    // 編集フラグを設定
                    $delFlag = true;
                }
                
                // 更新フラグ
                $CorrectionFlag = true;
                
                $oldThumbnailId = $noticeContactDataList['thumbnail'];
                $thumbnailId = uniqid();
                // 添付ファイル用にCookie設定→JQueryに渡す
                setcookie('noticeContactSCHEMAName',$_SESSION['SCHEMA'], time()+3600);
                //setcookie('noticeContactDirName',$noticeContactDataList['thumbnail'], time()+3600);
                setcookie('noticeContactDirName',$thumbnailId, time()+3600);

                // 一時保存フォルダを作成
                mkdir("./server/php/".$_SESSION['SCHEMA']."/".$thumbnailId);
                
                // ファイルを移動させたいディレクトリの指定
                $new_directory = "./server/php/".$_SESSION['SCHEMA']."/".$thumbnailId; 
                $move_directory = "./server/php/".$_SESSION['SCHEMA']. "/".$noticeContactDataList['thumbnail'];

                if(!empty($noticeContactDataList['thumbnail'])){
                    /** 既存のフォルダを丸ごとコピー **/
                    $this-> dir_copy($move_directory, $new_directory);
                }
                
            }else{
                $oldThumbnailId = "";
                $thumbnailId = uniqid();
                // 添付ファイル用にCookie設定→JQueryに渡す
                setcookie('noticeContactSCHEMAName',$_SESSION['SCHEMA'], time()+3600);
                setcookie('noticeContactDirName',$thumbnailId, time()+3600);
                
            }

            require_once './View/NoticeContactInputPanel.html';
            
            $Log->trace("END noticeContactInputPanelDisplay");
        }

        /**
         * ディレクトリ階層以下のコピー
         * @note     
         * @param    $dir_name コピー元ディレクトリ、$new_dir コピー先ディレクトリ
         * @return   結果
         */
        private function dir_copy($dir_name, $new_dir)
        {
            if (!is_dir($new_dir)) {
                mkdir($new_dir);
            }

            if (is_dir($dir_name)) {
                if ($dh = opendir($dir_name)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file == "." || $file == "..") {
                            continue;
                        }
                        if (is_dir($dir_name . "/" . $file)) {
                            // 再帰的に実行
                            $this-> dir_copy($dir_name . "/" . $file, $new_dir . "/" . $file);
                        } else {
                            copy($dir_name . "/" . $file, $new_dir . "/" . $file);
                        }
                    }
                closedir($dh);
                }
            }
            return true;
        }

        /**
         * ディレクトリ階層以下の削除
         * @note     
         * @param    $dir_name 対象ディレクトリ
         * @return   結果
         */
        private function dir_delete($dir_name)
        {
            if (is_dir($dir_name)) {
                if ($dh = opendir($dir_name)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file == "." || $file == "..") {
                            continue;
                        }
                        if (is_dir($dir_name . "/" . $file)) {
                            $this-> dir_delete($dir_name . "/" . $file);
                        } else {
                            unlink($dir_name . "/" . $file);
                        }
                    }
                closedir($dh);
                }
                rmdir($dir_name);
            }
            return true;
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
            $Log->trace("START noticeContact changeHeaderItemMark");

            // 初期化
            $headerArray = array(
                    'noticeContactNoSortMark'      => '',
                    'unreadSortMark'                => '',
                    'titleSortMark'             => '',
                    'contentsSortMark'     => '',
                    'organizationNameSortMark'     => '',
                    'userNameSortMark'     => '',
                    'registrationTimeSortMark'     => '',
                    'unreadCntSortMark'     => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]  = "noticeContactNoSortMark";
                $sortList[2]  = "noticeContactNoSortMark";
                $sortList[3]  = "unreadSortMark";
                $sortList[4]  = "unreadSortMark";
                $sortList[5]  = "titleSortMark";
                $sortList[6]  = "titleSortMark";
                $sortList[7]  = "contentsSortMark";
                $sortList[8]  = "contentsSortMark";
                $sortList[9]  = "organizationNameSortMark";
                $sortList[10] = "organizationNameSortMark";
                $sortList[11] = "userNameSortMark";
                $sortList[12] = "userNameSortMark";
                $sortList[13] = "registrationTimeSortMark";
                $sortList[14] = "registrationTimeSortMark";
                $sortList[15] = "unreadCntSortMark";
                $sortList[16] = "unreadCntSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START noticeContact changeHeaderItemMark");

            return $headerArray;
        }

        /**
         * 従業員リスト更新処理
         * @return    なし
         */
        public function searchUserListInputAction()
        {
            global $Log, $noticeContact; // グローバル変数宣言
            $Log->trace("START userListUpAction");
            $Log->info( "MSG_INFO_1426" );

            $searchUserNameList = array();
            
            // 選択されている種別でリスト生成
            if($_POST['unit'] == 1){
                // 全員の場合
                
            }else if($_POST['unit'] == 2){
                // 組織の場合 組織略称名リストを取得
                $oList = $noticeContact->setPulldown->getSearchOrganizationList( 'reference', true );

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
                $pList = $noticeContact->setPulldown->getSearchPositionList( 'reference' );
                
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
                $sList = $noticeContact->setPulldown->getSearchUserListALL( parent::escStr( $_POST['searchOrgID']) );
                
                // 共通リストに詰め直す
                foreach($sList as $dateListOne){
                    $dateList = array(
                        'id'    => '3-'.$dateListOne["user_id"],   // ユーザーID
                        'name'  => $dateListOne["user_name"],      // ユーザー名
                    );
                    //１行分搭載
                    array_push($searchUserNameList, $dateList);
                }
                
            }else if($_POST['unit'] == 6){
                // グループの場合 グループリストを取得
                $gList = $noticeContact->setPulldown->getSearchGroupList();
                
                // 共通リストに詰め直す
                foreach($gList as $dateListOne){
                    $dateList = array(
                        'id'    => '4-'.$dateListOne["group_id"],   // グループID
                        'name'  => $dateListOne["group_name"],      // グループ名
                    );
                    //１行分搭載
                    array_push($searchUserNameList, $dateList);
                }
            }else if($_POST['unit'] == 7){
                // グループ個人の場合 グループのユーザーリストを取得
                $guList = $noticeContact->setPulldown->getSearchGroupUserList( parent::escStr( $_POST['searchGroupID']) );
                
                // 共通リストに詰め直す
                foreach($guList as $dateListOne){
                    $dateList = array(
                        'id'    => '3-'.$dateListOne["menber_id"],   // ユーザーID
                        'name'  => $dateListOne["menber_name"],      // ユーザー名
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

            require_once '../noticeContact/View/SearchList.php';

            $Log->trace("END   userListUpAction");
        }
        
        /**
         * 従業員リスト更新処理
         * @return    なし
         */
        public function searchUserListAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START userListAction");

            $noticeContact = new NoticeContact();
            
            // 検索用ユーザリスト
            $searchUserNameList = $noticeContact->setPulldown->getSearchUserList( parent::escStr( $_POST['searchOrgID']) );      // ユーザリスト

            // 空のリストを1つ目に
            // 1行分作成
            $selectUserList = array(
                    'user_id'      => "",
                    'user_name'    => "",
            );
            //１行分搭載
            array_unshift($searchUserNameList, $selectUserList);
            
            require_once '../FwCommon/View/SearchUserName.php';

            $Log->trace("END   userListAction");
        }
        
    }
?>