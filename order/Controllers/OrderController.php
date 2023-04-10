<?php
    /**
     * @file      発注画面コントローラ
     * @author    USE K.Kanda(media-craft.co.jp)
     * @date      2016/01/23
     * @version   1.00
     * @note      発注画面
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // アクセス権限処理モデル
    require './Model/Order.php';

    /**
     * 発注画面コントローラクラス
     * @note   発注画面の新規登録/修正/削除を行う
     */
    class OrderController extends BaseController
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
         * 発注画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_11011" );

            $disabled = true;

            $this->initialDisplay();
            $Log->trace("END   showAction");
        }
		/**
         * 発注画面商品検索(条件なし)
         * @return    なし
         */
        public function prolistAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START prolistAction");

            $order  = new Order();
            $proArr = $order->getProductsList($_POST);
      		
            //商品区分一覧データ取得
            $prod_k_cd1_list  = $order->getProdKbnCdList($_POST['ma_cd'],"1");
                        
            $imgFld = "/img/order/maker/".$_POST['ma_cd']."/";
            echo json_encode($prod_k_cd1_list);

            require_once("./View/OrderProductsList.html");

            $Log->trace("END   prolistAction");
        }
		/**
         * 発注画面商品検索(条件あり)
         * @return    なし
         */
        public function prosearchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->debug("START prosearchAction");
            $order  = new Order();

            $proArr = $order->getProductsSearch($_POST);
            
            $imgFld = "/img/order/maker/".$_POST['ma_cd']."/";

            require_once("./View/OrderProductsList.html");
            
            $Log->debug("END   prosearchAction");
        }

		/**
         * 発注画面商品データ取得
         * @return    なし
         */
        public function getAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getAction");

            $order  = new Order();
      			$ret = $order->getProductData($_POST['pha_id'],$_POST['ma_cd'],$_POST['code']);

      			$rtnStr = "";
      			if(!empty($ret))
      			{
      				$rtnStr = $ret['pro_cd'].":||:".$ret['pro_name'].":||:".$ret['youryo'].":||:".$ret['irisuu'].":||:".$ret['tanka'].":||:".$ret['min_lot'];
      			}

            $Log->trace("END   getAction");

			      echo $rtnStr;
        }

        /**
         * 発注
         * @return    処理結果
         */
        public function orderAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START orderAction");

            $arr = array();
            $request  = json_decode(file_get_contents('php://input'));

            $arr['tag_no']   = $request[1]->tag_no;
            $arr['postage']  = $request[1]->postage;
            $arr['free_kin'] = $request[1]->free_kin;
            $arr['pha_id']   = $request[1]->pha_id;
            $arr['ma_cd']    = $request[1]->ma_cd;

            for($i=0;$i<count($request[0]->data);$i++)
            {
                $prod_cd = $request[0]->data[$i]->prod_cd;
                $jan_cd  = $request[0]->data[$i]->jan_cd;
                $suu     = $request[0]->data[$i]->suu;
                if($suu=="")
                {
                  $suu = 0;
                }
                $hakosuu = $request[0]->data[$i]->hakosuu;
                if($hakosuu=="")
                {
                  $hakosuu = 0;
                }

                $arr['pro_cd_'.$arr['tag_no'].'_'.($i+1)] = $prod_cd;
                $arr['jan_cd_'.$arr['tag_no'].'_'.($i+1)]  = $jan_cd;
                $arr['suu_'.$arr['tag_no'].'_'.($i+1)]     = $suu;
                $arr['case_'.$arr['tag_no'].'_'.($i+1)]    = $hakosuu;
            }

            //print_r($arr);

            $order  = new Order();
      			if($order->startOrder($arr)) {
              echo "OK";
              exit;
      			}
            $Log->trace("END   orderAction");
        }
        
        /**
         * 発注キャンセル
         * @return    処理結果
         */
        public function cancelAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START cancelAction");

            $arr = array();
            $request  = json_decode(file_get_contents('php://input'));

            $arr['tag_no']   = $request[1]->tag_no;
            $arr['postage']  = $request[1]->postage;
            $arr['free_kin'] = $request[1]->free_kin;
            $arr['pha_id']   = $request[1]->pha_id;
            $arr['ma_cd']    = $request[1]->ma_cd;
            $arr['ord_no']   = $request[1]->ord_no;

            for($i=0;$i<count($request[0]->data);$i++)
            {
                $prod_cd = $request[0]->data[$i]->prod_cd;
                $jan_cd  = $request[0]->data[$i]->jan_cd;
                $suu     = $request[0]->data[$i]->suu;
                if($suu=="")
                {
                  $suu = 0;
                }
                $hakosuu = $request[0]->data[$i]->hakosuu;
                if($hakosuu=="")
                {
                  $hakosuu = 0;
                }

                $arr['pro_cd_'.$arr['tag_no'].'_'.($i+1)] = $prod_cd;
                $arr['jan_cd_'.$arr['tag_no'].'_'.($i+1)]  = $jan_cd;
                $arr['suu_'.$arr['tag_no'].'_'.($i+1)]     = $suu;
                $arr['case_'.$arr['tag_no'].'_'.($i+1)]    = $hakosuu;
            }

            $order  = new Order();
      			if($order->cancelOrder($arr)) {
      				echo "OK";
              exit;
      			}
            $Log->trace("END   cancelAction");

        }
        
		/**
         * 発注データ一時保存
         * @return    なし
         */
        public function saveAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START orderAction");

            $arr = array();
            $request  = json_decode(file_get_contents('php://input'));

            $arr['tag_no']   = $request[1]->tag_no;
            $arr['postage']  = $request[1]->postage;
            $arr['free_kin'] = $request[1]->free_kin;
            $arr['pha_id']   = $request[1]->pha_id;
            $arr['ma_cd']    = $request[1]->ma_cd;

            for($i=0;$i<count($request[0]->data);$i++)
            {
                $prod_cd = $request[0]->data[$i]->prod_cd;
                $jan_cd  = $request[0]->data[$i]->jan_cd;
                $suu     = $request[0]->data[$i]->suu;
                if($suu=="")
                {
                  $suu = 0;
                }
                $hakosuu = $request[0]->data[$i]->hakosuu;
                if($hakosuu=="")
                {
                  $hakosuu = 0;
                }

                $arr['pro_cd_'.$arr['tag_no'].'_'.($i+1)] = $prod_cd;
                $arr['jan_cd_'.$arr['tag_no'].'_'.($i+1)]  = $jan_cd;
                $arr['suu_'.$arr['tag_no'].'_'.($i+1)]     = $suu;
                $arr['case_'.$arr['tag_no'].'_'.($i+1)]    = $hakosuu;
            }
            //print_r($arr);
            //exit;

            $order  = new Order();
      			if($order->startOrder($arr,"0")) {
      				echo "OK";
              exit;
      			}
            $Log->trace("END   orderAction");
        }
		     /**
         * 発注履歴画面からコピーによる新規作成
         * @return    なし
         */
        public function copyAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START copyAction");

        			$copyFlg = false;
        			$ord_no  = "";
        			if(isset($_GET['ord_no'])) {
        				$copyFlg = true;
        				$ord_no  = $_GET['ord_no'];
        			}
              $pha_id = $_SESSION['PHA_ID'];

        			$Log->trace("END   copyAction");

        			require_once("./View/Order.html");
        }
		/**
         * 発注履歴画面から選択
         * @return    なし
         */
        public function selectAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START selectAction");
      			$copyFlg = false;
      			$ord_no  = "";
            $pha_id = $_SESSION['PHA_ID'];

      			if(isset($_GET['ord_no'])) {
      				$copyFlg = false;
      				$ord_no  = $_GET['ord_no'];
  			    }

		      $Log->trace("END   selectAction");

          $order = new Order();
          //発注先一覧の取得
          $makerArr = $order->getMakerList($pha_id);
          //CM一覧の取得
          $cmArr    = $order->getCmList($pha_id);

          $disabled = false;
			    require_once("./View/Order.html");
        }
		/**
         * 発注ヘッダ情報を取得
         * @return    なし
         */
        public function headerAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START headerAction");

      			$order = new Order();
      			$ret   = $order->getOrdHead($_POST);

      			$rtnStr = "";
      			if(!empty($ret))
      			{
      				$rtnStr = $ret['ma_cd'].":||:".$ret['ma_name'].":||:".$ret['postage'].":||:".$ret['free_kin'].":||:".$ret['kbn'];
      			}

      			$Log->trace("END   headerAction");

      			echo $rtnStr;
        }
		/**
         * 発注明細情報を取得
         * @return    なし
         */
        public function detailsAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START detailsAction");


      			$order = new Order();
      			$detailsArr = $order->getOrdDetails($_POST);
            $tag_no  = $_POST['tag_no'];

      			$Log->trace("END   detailsAction");

      			require_once './View/OrderList.html';
        }

		/**
         * 発注先広告データの取得
         * @return    なし
         */
        public function cmAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START cmAction");

      			$order = new Order();
      			$cmArr = $order->getCmList($_POST['pha_id']);

      			$Log->trace("END   cmAction");

      			require_once './View/OrderCmList.html';
        }
        /**
         * 発注BOXの追加
         * @return   HTMLソース
         */
        public function boxAction()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START boxAction");

            $tag_no   = $_POST['tag_no'];
            $postage  = $_POST['postage'];
            $free_kin = $_POST['free_kin'];
            $kin      = $_POST['kin'];
            $ord_no   = $_POST['ord_no'];

            if($ord_no == ""){
                $disabled = true;
            }else{
                $disabled = false;
            }
            
            $Log->debug("KIN=".$kin);

      			$Log->trace("END   boxAction");
            require_once './View/orderBox.html';
        }
        /**
         * 比較
         * @return   HTMLソース
         */
        public function comparisonAction()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START comparisonAction");

            //header('Content-Type: application/json');
            //echo "通信OK";
            $request  = json_decode(file_get_contents('php://input'));
            //print_r($request);
            //exit;

            $tag_no   = $request[1]->tag_no;
            $postage  = $request[1]->postage;
            $free_kin = $request[1]->free_kin;
            $pha_id   = $request[1]->pha_id;
            $ma_cd    = $request[1]->ma_cd;

            $order = new Order();
            $detailsArr = array();
            for($i=0;$i<count($request[0]->data);$i++)
            {
                $code = $request[0]->data[$i]->prod_cd;
                $arr = $order->getProductData($pha_id,$ma_cd,$code);
                if(!empty($arr))
                {
                    $detailsArr[] = $arr;
                }
            }

            //print_r($detailsArr);


      			$Log->trace("END comparisonAction");
            require_once './View/orderBox.html';
        }

        /**
         * 発注画面
         * @return   HTMLソース
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $pha_id = $_SESSION['PHA_ID'];

            $order = new Order();
            //発注先一覧の取得
            $makerArr = $order->getMakerList($pha_id);
            //CM一覧の取得
            $cmArr    = $order->getCmList($pha_id);

            $Log->trace("END   initialDisplay");
            require_once './View/Order.html';
        }
        
         /**
        * 商品区分設定
        * @return    商品区分
        */
         public function setProdKbnAction()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START setProdKbnAction");
            
            $Order = new Order();
            $ret = $Order->getProdKbnCdList($_POST['ma_cd'],$_POST["prod_type"],$_POST["prod_kbn_list"]);

            $Log->trace("END setProdKbnAction");
            echo json_encode($ret);
        }
        
        /**
         * 仕入発注品マスタ画面表示
         * @return    なし
         */
        public function inputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START inputAction");
            $Log->info( "MSG_INFO_11011" );
      	    $mode = "";
      	    $Form = $_POST;
           
            $mode = "編集";
      		//データベースから発注品データを取得
            $Order = new Order();
            $Form = $Order->getOrderProductData($Form['jan_cd'],$Form['ma_cd'],$Form['pha_id']);
           
            $imgFld  = "/img/order/product/";

            require_once './View/OrderProduct.html';                        

            $Log->trace("END   inputAction");
        }
    }
?>
