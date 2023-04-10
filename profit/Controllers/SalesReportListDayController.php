<?php
    /**
     * @file      日次報告書コントローラ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      日次報告書の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/SalesReportListDay.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class SalesReportListDayController extends BaseController
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
         * 報告書登録画面初期表示
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
         * 報告書登録画面 登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START insertAction");
            $Log->info( "MSG_INFO_1040" );
            
            $salesReportListDay = new SalesReportListDay();

            $searchArray = array(
                'report_form_id'            => parent::escStr( $_POST['report_form_id'] ),
            );
            
            // 報告書フォーム一覧データ取得
            $salesFormDetailList = $salesReportListDay->getFormDetailList($searchArray);

            // 詳細ごとに登録
            foreach($salesFormDetailList as $data){
                
                $inputArray = array(
                    'target_date'            => parent::escStr( $_POST['target_date'] ),
                    'report_form_id'         => parent::escStr( $_POST['report_form_id'] ),
                    'report_type'            => parent::escStr( $_POST['report_type'] ),
                    'organization_id'        => parent::escStr( $_POST['organizationName'] ),
                    'report_form_detail_id'  => $data["report_form_detail_id"],
                    'data'                   => parent::escStr( $_POST[$data["report_form_detail_id"]] ),
                    'user_id'                => $_SESSION["USER_ID"],
                    'organization'           => $_SESSION["ORGANIZATION_ID"],
                );

                $messege = $salesReportListDay->addNewData($inputArray);
            }

            $this->initialDisplay();
            
            $Log->trace("END   insertAction");
        }

        /**
         * 報告書登録画面 更新
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START updateAction");
            $Log->info( "MSG_INFO_1040" );
            
            $salesReportListDay = new SalesReportListDay();

            $searchArray = array(
                'report_form_id'            => parent::escStr( $_POST['report_form_id'] ),
            );

            // 報告書フォーム一覧データ取得
            $salesFormDetailList = $salesReportListDay->getFormDetailList($searchArray);

            // 詳細ごとに登録
            foreach($salesFormDetailList as $data){
                
                $updateArray = array(
                    'report_form_id'         => parent::escStr( $_POST['report_form_id'] ),
                    'report_form_detail_id'  => $data["report_form_detail_id"],
                    'target_date'            => parent::escStr( $_POST['target_date'] ),
                    'organization_id'        => parent::escStr( $_POST['organizationName'] ),
                    'data'                   => parent::escStr( $_POST[$data["report_form_detail_id"]] ),
                    'user_id'                => $_SESSION["USER_ID"],
                    'organization'           => $_SESSION["ORGANIZATION_ID"],
                );

                $messege = $salesReportListDay->modUpdateData($updateArray);
            }


            $this->initialDisplay();
            
            $Log->trace("END   updateAction");
        }

        /**
         * 報告書登録画面 削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START deleteAction");
            $Log->info( "MSG_INFO_1040" );
            
            $salesReportListDay = new SalesReportListDay();

            $deleteArray = array(
                'report_form_id'         => parent::escStr( $_POST['report_form_id'] ),
                'target_date'            => parent::escStr( $_POST['target_date'] ),
                'organization_id'        => parent::escStr( $_POST['organizationName'] ),
            );

            $messege = $salesReportListDay->del($deleteArray);

            $this->initialDisplay();
            
            $Log->trace("END   deleteAction");
        }

        /**
         * 報告書画面
         * @note     報告書画面の表示
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $home = parent::escStr( $_POST['home'] );
            
            $salesReportListDay = new SalesReportListDay();
            $abbreviatedNameList = $salesReportListDay->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

            $searchArray = array(
                'report_form_id'  => '1',
            );

            // 報告書フォーム一覧データ取得
            $salesReportDayList = $salesReportListDay->getFormListData($searchArray);

            // ヘッダ情報、Ｍコード情報取り出し 
            foreach($salesReportDayList as $data){
                $reportFormId = $data["report_form_id"];
                $reportType = $data["report_type"];
            }
            
            // 日付リストを取得
            $targetDate = parent::escStr( $_POST['target_date'] );

            // 画面フォームに日付を返す
            if(!isset($targetDate) OR $targetDate == ""){
                //現在日付を設定
                $targetDate =date("Y/m/d");
            }

            $organizationName = parent::escStr( $_POST['organizationName'] );
            
            if(!isset($organizationName) OR $organizationName == ""){
                $organizationName = $_SESSION["ORGANIZATION_ID"];
            }
            
            // 検索組織
            $searchArray = array(
                'organizationID' => parent::escStr( $_POST['organizationName'] ),
            );

            // 登録済みデータがあるか取得
            $searchMArray = array(
                'organization_id'   => $organizationName,   // 検索組織
                'target_date'       => $targetDate,         // 検索日付
                'report_form_id'    => '1',                 // 報告書設定ID
            );

            // 詳細情報と登録済みデータを取得
            $salesReportDayDetailList = $salesReportListDay->getFormDetailListData($searchMArray);
            
            // 詳細情報から計算や累計を実施
            foreach($salesReportDayDetailList as &$dData){
                // 集計タイプが累計の場合
                if($dData["aggregate_type"] == 1){
                    
                    // 月初から対象日までのデータを取得 + 入力値
                    $searchTArray = array(
                        'organization_id'          => $organizationName, // 検索組織
                        'target_date'              => $targetDate,       // 検索日付
                        'report_form_detail_id'    => $dData[logic],     // 報告書設定ID
                    );
                    
                    // 累計データを取得
                    $totalDeta = $salesReportListDay->getTotalDeta($searchTArray);
                    
                    // 画面のフォーム内容もプラスする
                    $dData["data"] = $totalDeta[0]["data"] + parent::escStr( $_POST[$dData[logic]] );
                }
                
                // 計算ロジックが設定されていたら
                if(!empty($dData["logic"])){
                    // 計算処理を実施
                       
                }
                       
                // 新規か編集かフラグ生成 
                if($dData["data"] != ""){
                    $dataFlg = "true";
                }
            }
            // 値を参照渡しで設定する
            unset($dData);
            
            $Log->trace("END   initialDisplay");
            require_once './View/SalesReportListDayPanel.html';
        }

    }?>
