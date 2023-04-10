<?php
    /**
     * @file      発注先コントローラ
     * @author    USE K.Kanda(media-craft.co.jp)
     * @date      2016/01/23
     * @version   1.00
     * @note      発注先の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // アクセス権限処理モデル
    require './Model/Maker.php';

    /**
     * アクセス権限コントローラクラス
     * @note   アクセス権限の新規登録/修正/削除を行う
     */
    class MakerController extends BaseController
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
         * 発注先マスタ一覧画面初期表示
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
         * 発注先マスタ検索画面
         * @return   HTMLソース
         */
        public function searchAction()
        {
          global $Log, $TokenID;  // グローバル変数宣言
          $Log->trace("START searchAction");

    			$Maker = new Maker();
    			$makerList = $Maker->getMakerList($_POST['ma_name']);

    			$Log->trace("END   searchAction");
          require_once './View/MakerList.html';
        }
		/**
        * 発注先マスタ削除処理
        * @return    なし
        */
        public function deleteAction()
        {
          global $Log; // グローバル変数宣言
          $Log->trace("START deleteAction");
		      $Maker = new Maker();
			    $ret = $Maker->deleteMakerData($_POST['ma_cd']);
			    $makerList = $Maker->getMakerList();
          $Log->trace("END   deleteAction");
			    require_once './View/MakerList.html';
        }
		/**
        * 発注先マスタ登録・更新処理
        * @return 無し
        */
        public function saveAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START saveAction");

		    $Maker = new Maker();

			$ret = $Maker->saveMakerData($_POST);

            $Log->trace("END   saveAction");
        }

		/**
         * 発注先マスタ登録・編集画面表示
         * @return    なし
         */
        public function inputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_11011" );

      			$mode = "";
      			$Form = $_POST;
            if(empty($Form['ma_cd']))
			      {
				      $mode = "新規登録";
    		    } else {
      				$mode = "編集";
      				//データベースから薬局データを取得
      				$Maker = new Maker();
      				$Form = $Maker->getMakerData($Form['ma_cd']);
      				//$Form['start_ymd'] = str_replace("-","/",$Form['start_ymd']);
            }

      			//カンマ区切りのデータを配列に格納
      			$arr = explode(",",$Form['get_day']);

      			//曜日のチェック
      			for($i=0;$i<=6;$i++) {
      				$var = "checked".$i;
      				${$var} = "";
      				for($n=0;$n<count($arr);$n++) {
      					if($arr[$n]==$i && $arr[$n]!="") {
      						${$var} = "checked";
      					}
      				}
      			}

      			$weekArr[0] = array("label"=>"日","checked"=>$checked0);
      			$weekArr[1] = array("label"=>"月","checked"=>$checked1);
      			$weekArr[2] = array("label"=>"火","checked"=>$checked2);
      			$weekArr[3] = array("label"=>"水","checked"=>$checked3);
      			$weekArr[4] = array("label"=>"木","checked"=>$checked4);
      			$weekArr[5] = array("label"=>"金","checked"=>$checked5);
      			$weekArr[6] = array("label"=>"土","checked"=>$checked6);

      			require_once './View/Maker.html';

            $Log->trace("END   showAction");
        }
      /**
       * 発注先マスタ一覧画面
       * @return   HTMLソース
       */
      private function initialDisplay()
      {
          global $Log, $TokenID;  // グローバル変数宣言
          $Log->trace("START initialDisplay");

    			$Maker = new Maker();
    			$makerList = $Maker->getMakerList();
    			$Log->trace("END   initialDisplay");
          require_once './View/MakerSearch.html';
      }
      /**
      * 薬剤メーカー画面(受注一覧)
      * @return    なし
      */
      private function  adminController()
      {
        //受注一覧の取得

        require_once './View/MakerAdmin.html';
      }
    }
?>
