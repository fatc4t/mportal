<?php
    /**
     * @file      入出金記録 [C]
     * @author    vergara miguel
     * @date      2019/03/05
     * @version   1.00
     * @note      帳票
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetDepositsAndWithdrawals.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetDepositsAndWithdrawalsController extends BaseController
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
        public function initialAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");

            $this->initialDisplay("initial");

            $Log->trace("END   showAction");
        }

        /**
         * 表示項目設定一覧画面検索表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");

            $this->initialDisplay("show");

            $Log->trace("END   showAction");
        }

        /**
         * 帳票一覧画面
         * @note     POS種別画面全てを更新
         * @return   無
         */
        private function initialDisplay($mode)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");
            $ledgerSheetDepositsAndWithdrawals = new ledgerSheetDepositsAndWithdrawals();

            $abbreviatedNameList = $ledgerSheetDepositsAndWithdrawals->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

            // 日付リストを取得
            $startDate = parent::escStr( $_POST['start_date'] );
            $endDate = parent::escStr( $_POST['end_date'] );

            // 画面フォームに日付を返す
            if(!isset($startDate) OR $startDate == ""){
                //現在日付の１日を設定
                $startDate =date('Y/m/d', strtotime('first day of ' . $month));
            }
            
            if(!isset($endDate) OR $endDate == ""){
                //現在日付の１日を設定
                $endDate =date('Y/m/d', strtotime('last day of ' . $month));
            }

            $organizationId   = parent::escStr( $_POST['organizationName'] );
            
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'organizationID'    => $organizationId,

            );
            
            if($mode != "initial")
            {
                // 帳票フォーム一覧データ取得
                $ledgerSheetDepositsAndWithdrawals = $ledgerSheetDepositsAndWithdrawals->getFormListData($searchArray);
            }

            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetDepositsAndWithdrawalsPanel.html';
            }
        }
    }
?>
