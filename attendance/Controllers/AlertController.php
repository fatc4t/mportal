<?php
    /**
     * @file      アラートコントローラ
     * @author    USE R.dendo
     * @date      2016/06/24
     * @version   1.00
     * @note      アラートの内容表示/修正を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // アラート処理モデル
    require './Model/Alert.php';

    /**
     * アラートコントローラクラス
     * @note    アラートの内容表示/修正を行う
     */
    class AlertController extends BaseController
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
         * アラート一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1130" );
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }

        /**
         * アラート一覧画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1131" );

            $alert = new alert();

            $postArray = array(
                'alertID'                            => parent::escStr( $_POST['alertID'] ),
                'is_labor_standards_act'             => parent::escStr( $_POST['is_labor_standards_act'] ),
                'is_labor_standards_act_warning'     => parent::escStr( $_POST['is_labor_standards_act_warning'] ),
                'warning_value'                      => parent::escStr( $_POST['warning_value'] ),
                'updateTime'                         => parent::escStr( $_POST['updateTime'] ),
                'user_id'                            => $_SESSION["USER_ID"],
                'organization'                       => $_SESSION["ORGANIZATION_ID"],
            );

            $messege = $alert->modUpdateData($postArray);
            $this->initialList($messege);
            $Log->trace("END   modAction");
        }

        /**
         * アラート一覧画面
         * @note     アラート画面全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $alert = new alert();

            // アラート一覧データ取得
            $alertAllList = $alert->getListData();

           // アラート一覧レコード数
            $alertRecordCnt = count($alertAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($alertRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "alert_id";

            $alertList = $this->refineListDisplayNoSpecifiedPage($alertAllList, $idName, $pagedRecordCnt, $pageNo);
            $alertList = $this->modBtnDelBtnDisabledCheck($alertList);

            $alert_no = 0;

            $Log->trace("END   initialDisplay");
            require_once './View/AlertPanel.html';
        }
         /**
         * アラート一覧更新
         * @note     アラート画面の一覧部分のみの更新
         * @param    $messege        DBの更新結果(データ指定がない場合、デフォルト値[MSG_BASE_0000]を設定)
         * @return   無
         */
        private function initialList($messege = "MSG_BASE_0000")
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");

            $alert = new alert();

            // アラート一覧データ取得
            $alertAllList = $alert->getListData();

            // アラート一覧レコード数
            $alertRecordCnt = count($alertAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($alertRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "alert_id";

            $alertList = $this->refineListDisplayNoSpecifiedPage($alertAllList, $idName, $pagedRecordCnt, $pageNo);
            $alertList = $this->modBtnDelBtnDisabledCheck($alertList);

            $alert_no = 0;

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                require_once './View/AlertTablePanel.html';
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
            }
            $Log->trace("END   initialList");
        }

    }
?>
