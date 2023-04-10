<?php
   /**
     * @file      薬局コントローラ
     * @author    USE K.Kanda(media-craft.co.jp)
     * @date      2016/01/23
     * @version   1.00
     * @note      薬局の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // アクセス権限処理モデル
    require './Model/Pharmacy.php';

    /**
     * アクセス権限コントローラクラス
     * @note   アクセス権限の新規登録/修正/削除を行う
     */
    class PharmacyController extends BaseController
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
         * 薬局マスタ一覧画面初期表示
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
         * 薬局マスタ検索画面
         * @return   HTMLソース
         */
        public function searchAction()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START searchAction");

      			$pharmacy = new Pharmacy();
      			$pharmacyList = $pharmacy->getPharmacyList($_POST['pha_name']);
      			$Log->trace("END   searchAction");
            require_once './View/PharmacyList.html';
        }
		/**
        * 薬局マスタ削除処理
        * @return    なし
        */
        public function deleteAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START deleteAction");
		        $pharmacy = new Pharmacy();
      			$ret = $pharmacy->deletePharmacyData($_POST['pha_id']);
      			$pharmacyList = $pharmacy->getPharmacyList();
            $Log->trace("END   deleteAction");
			      require_once './View/PharmacyList.html';

        }
		/**
    * 薬局マスタ登録・更新処理
    * @return 無し
    */
    public function saveAction()
    {
        global $Log; // グローバル変数宣言
        $Log->trace("START saveAction");

        $pharmacy = new Pharmacy();
	      $ret = $pharmacy->savePharmacyData($_POST);

        $Log->trace("END   saveAction");
    }

		/**
         * 薬局マスタ登録・編集画面表示
         * @return    なし
         */
        public function inputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_11011" );

      			$mode = "";
      			$Form = $_POST;
            if(empty($Form['pha_id']))
      			{
      				$mode = "新規登録";
      		    } else {
      				$mode = "編集";
      				//データベースから薬局データを取得
      				$pharmacy = new Pharmacy();
      				$Form = $pharmacy->getPharmacyData($Form['pha_id']);
      				$Form['start_ymd'] = str_replace("-","/",$Form['start_ymd']);
            }
			      require_once './View/Pharmacy.html';

            $Log->trace("END   showAction");
        }
        /**
         * 薬局マスタ一覧画面
         * @return   HTMLソース
         */
        private function initialDisplay()
        {
          global $Log, $TokenID;  // グローバル変数宣言
          $Log->trace("START initialDisplay");

    			$pharmacy      = new Pharmacy();
    			$pharmacyList = $pharmacy->getPharmacyList();
    			$Log->trace("END   initialDisplay");
          require_once './View/PharmacySearch.html';
        }

    }
?>
