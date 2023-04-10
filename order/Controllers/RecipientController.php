<?php
    /**
     * @file      発注先設定コントローラ
     * @author    USE K.Kanda(media-craft.co.jp)
     * @date      2016/01/23
     * @version   1.00
     * @note      発注先設定の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // アクセス権限処理モデル
    require './Model/Recipient.php';

    /**
     * アクセス権限コントローラクラス
     * @note   アクセス権限の新規登録/修正/削除を行う
     */
    class RecipientController extends BaseController
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
         * 発注先設定画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_11011" );

            $this->initialDisplay();
            $Log->trace("END   showAction");
        }
    		/**
         * 発注先設定登録処理
         * @return    なし
         */
        public function saveAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START saveAction");
    	      $recipient = new Recipient();
    	      $recipient->saveRecipientData($_POST);

            $Log->trace("END   saveAction");
        }

    		/**
       * 未設定発注先一覧
       * @return    なし
       */
        public function search1Action()
        {
          global $Log; // グローバル変数宣言
          $Log->trace("START search1Action");

    			$recipient = new Recipient();
    			$makerArr1 = $recipient->getMakerList1($_POST['pha_id']);

          $Log->trace("END   search1Action");
          require_once './View/RecipientList1.html';
    		}
		    /**
         * 設定済み発注先一覧
         * @return    なし
         */
        public function search2Action()
        {
          global $Log; // グローバル変数宣言
          $Log->trace("START search2Action");

    			$recipient = new Recipient();
    			$makerArr2 = $recipient->getMakerList2($_POST['pha_id']);

          $Log->trace("END   search2Action");
          require_once './View/RecipientList2.html';
		    }

        /**
         * 発注先設定画面
         * @return   HTMLソース
         */
        private function initialDisplay()
        {
          global $Log, $TokenID;  // グローバル変数宣言
          $Log->trace("START initialDisplay");

    			$pha_id = "";
    			if(!empty($_POST['pha_id'])) {
    				$pha_id = $_POST['pha_id'];
    			}

    			//薬局の一覧を取得
    			$recipient   = new Recipient();
    			$pharmacyArr = $recipient->getPharmacyList();
                //未設定の発注先一覧を取得
    			$makerArr1   = $recipient->getMakerList1($pha_id);


    			$Log->trace("END   initialDisplay");
          require_once './View/Recipient.html';
        }
    }
?>
