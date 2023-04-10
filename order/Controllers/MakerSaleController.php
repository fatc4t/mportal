<?php
    /**
     * @file      メーカー側発注処理コントローラ
     * @author    USE K.Kanda(media-craft.co.jp)
     * @date      2018/12/14
     * @version   1.00
     * @note      発注処理を行う。
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // アクセス権限処理モデル
    require './Model/MakerSale.php';
    //require './Model/MakerAdmin.php';

    /**
     * アクセス権限コントローラクラス
     * @note   アクセス権限の新規登録/修正/削除を行う
     */
    class MakerSaleController extends BaseController
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

            $this->initialDisplay();
            $Log->trace("END   showAction");
        }
        /**
         * 受注一覧データ表示
         * @return    なし
         */
        public function listAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START listAction");

            $ordersList = array();
            //受注一覧の取得
            $makerSale = new MakerSale();
            $ordersList = $makerSale->getOrdersList($_POST);

            require_once './View/MakerSaleList.html';

            $Log->trace("END   listAction");
        }
        /**
         * 受注一覧CSV出力
         * @return    なし
         */
        public function csvAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START listAction");

            $ordersList = array();
            //受注一覧の取得
            $makerSale = new MakerSale();

            $pha_id = $_POST['pha_id2'];
            $ord_no = $_POST['ord_no'];

            $ordersData = $makerSale->getOrdersData($pha_id,$ord_no);
            $csv = "取引先名,受注日時,JANコード,商品名,容量,入数,単価,数量,ケース,金額\n";
            foreach($ordersData as $key => $value)
            {
              $csv .= $value['pha_name'];
              $csv .= ",".$value['order_time'];
              $csv .= ",".$value['jan_cd'];
              $csv .= ",".$value['pro_name'];
              $csv .= ",".$value['youryo'];
              $csv .= ",".$value['irisuu'];
              $csv .= ",".$value['tanka'];
              $csv .= ",".$value['suu'];
              $csv .= ",".$value['hakosuu'];
              $csv .= ",".$value['kin'];
              $csv .= "\n";
            }
            $Log->trace("END   listAction");

            //CSV出力
            $filename = $pha_id."_".$ord_no.".csv";
        		header("Content-Type: application/octet-stream");
        		header("Content-Disposition: attachment; filename=$filename");
        		echo mb_convert_encoding($csv,"SJIS-win", "UTF-8");
        }
        /**
         * 受注処理(単一)
         * @return    なし
         */
        public function updateAction()
        {
          global $Log, $TokenID;  // グローバル変数宣言
          $Log->trace("START initialDisplay");
          $makerSale = new MakerSale();

          $pha_id = $_POST['pha_id'];
          $ord_no = $_POST['ord_no'];

          if($makerSale->updateOrderSales($pha_id,$ord_no)) {
            echo 'OK';
          } else {
            echo 'NG';
          }
        }
        /**
         * 受注処理(一括)
         * @return    なし
         */
        public function allAction()
        {
          global $Log, $TokenID;  // グローバル変数宣言
          $Log->trace("START initialDisplay");
          $makerSale = new MakerSale();


          $request  = json_decode(file_get_contents('php://input'));
          $arr = array();
          for($i=0;$i<count($request);$i++) {
            $arr[] = array("pha_id"=>$request[$i]->pha_id,"ord_no"=>$request[$i]->ord_no);
          }
          if($makerSale->allOrderSales($arr)) {
            echo 'OK';
          } else {
            echo 'NG';
          }
        }

        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            //取引先一覧の取得
            $makerSale = new MakerSale();
            $phaList = array();
            $phaList = $makerSale->getPhaList();

            $ma_cd = $_SESSION['MA_CD'];

            $kbnList = array();
            $kbnList[1] = '注文';
            $kbnList[2] = '受注受付';
            $kbnList[3] = '出荷済';
            $kbnList[9] = 'キャンセル';

      			$Log->trace("END   initialDisplay");
            require_once './View/MakerSale.html';


        }
    }
?>
