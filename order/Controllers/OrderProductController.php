<?php
    /**
     * @file      仕入発注品コントローラ
     * @author    尉
     * @date      2019/07/03
     * @version   1.00
     * @note      仕入発注品の新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // アクセス権限処理モデル
    require './Model/OrderProduct.php';

    /**
     * アクセス権限コントローラクラス
     * @note   アクセス権限の新規登録/修正/削除を行う
     */
    class OrderProductController extends BaseController
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
         * 仕入発注品マスタ一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_11011" );
            $startFlag = true;
            $this->initialDisplay($startFlag);
            $Log->trace("END   showAction");
        }
        
	/**
         * 仕入発注品マスタ検索画面
         * @return   HTMLソース
         */
        public function searchAction()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START searchAction");
            
            // 一覧表用検索項目
            $searchArray = array(
                'prod_name'           => parent::escStr( $_POST['prod_name'] ),
                'sort'                => parent::escStr( $_POST['sortConditions'] ),
                'prod_list'           => parent::escStr( $_POST['prod_list'] ),
                'ma_cd'               => parent::escStr( $_POST['ma_cd'] ),
            );
            
            $startFlag = false;
            
            if(isset($_POST['displayPageNo']))
            {
                $_SESSION['PAGE_NO'] = $_POST['displayPageNo'];
            }
            if(isset($_POST['displayRecordCnt']))
            {
                $_SESSION['DISPLAY_RECORD_CNT'] = $_POST['displayRecordCnt'];
            }
            $OrderProduct = new OrderProduct();

            $this->initialDisplay($startFlag);

            $Log->trace("END   searchAction");

        }
        
	/**
        * 仕入発注品マスタ削除処理
        * @return    なし
        */
        public function deleteAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START deleteAction");
            $startFlag = true;
            
            $OrderProduct = new OrderProduct();
            $ret = $OrderProduct->deleteOrderProductData($_POST['prod_cd'], $_POST['ma_cd']);
             $searchArray = array(
              
                'ma_cd'               => parent::escStr( $_POST['ma_cd'] )
            );
            $orderProductList = $OrderProduct->getOrderProductList($searchArray);

            $Log->trace("END   deleteAction");
            require_once './View/OrderProductList.html';
        }
        
	/**
        * 仕入発注品マスタ登録・更新処理
        * @return 無し
        */
        public function saveAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START saveAction");
            $OrderProduct = new OrderProduct();
            $startFlag = true;
            $Form = $_POST;
            $prod_cd = $Form['prod_cd'];
            $ma_cd = (empty($Form['ma_cd'])  ? $_SESSION['MA_CD']: $Form['ma_cd']);
            
            $imgFld  = "../img/order/product/".$ma_cd."/";
            if(!is_dir($imgFld)){
                mkdir($imgFld, 0755);
            }

            $Form['imgs'] = "";
            for($x=0; $x<5; $x++){
                $output = $imgFld.$prod_cd."_".$x.".jpg";
                if(substr($Form['img_'.($x + 1)], -4) != '.jpg'){ 
                    if(!empty($Form['img_'.($x + 1)])){
                        $Form['imgs'] .= ",".$output;
                    }else{
                        $Form['imgs'] .= ",";
                    }
                }else{
                    $Form['imgs'] .= ",".$Form['img_'.($x + 1)];
                }
                if(!empty($Form['img_'.($x + 1)]) && substr($Form['img_'.($x + 1)], -4) != '.jpg'){  
                    file_put_contents($output, file_get_contents($Form['img_'.($x + 1)]));
                }
            }

            $ret = $OrderProduct->saveOrderProductData($Form);
            if($ret === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }
            $Log->trace("END   saveAction");
        }

	/**
         * 仕入発注品マスタ登録・編集画面表示
         * @return    なし
         */
        public function inputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_11011" );
      	    $mode = "";
      	    $Form = $_POST;
            if(empty($Form['prod_cd']))
            {
		$mode = "新規登録";
            } else {
      		$mode = "編集";
      		//データベースから発注品データを取得
      		$OrderProduct = new OrderProduct();
      		$Form = $OrderProduct->getOrderProductData($Form['prod_cd'], $Form['cmb_ma_cd']);
            }
            $OrderProduct = new OrderProduct();
            $modal = new Modal();
            
            //JICFS一覧データ取得
            $jic_id_list    = $OrderProduct->getJicfsList();
            //商品区分一覧データ取得
            $prod_k_cd1_list    = $OrderProduct->getProdKbnCdList($Form['ma_cd'], "1");
            
            $data           = [];
            $data           = $OrderProduct->getOrderProductData();
            
            $imgFld  = "/img/order/product/";

            require_once './View/OrderProduct.html';                        

            $Log->trace("END   showAction");
        }
        
        /**
        * 仕入発注品マスタ一覧画面
        * @return   HTMLソース
        */
        private function initialDisplay($startFlag)
        {
              global $Log, $TokenID;  // グローバル変数宣言
              $Log->trace("START initialDisplay");

              $OrderProduct = new OrderProduct();
              
              $modal = new Modal();
              $sect_detail    = [];
              $sect_detail    = $modal->getsect_detail();
              
              $Sizelist       = [];
              $base           = new BaseModel();
              $Sizelist       = $base->getSizeList();     

               // 一覧表用検索項目
              $searchArray = array(
                  'prod_name'           => parent::escStr( $_POST['prod_name'] ),
                  'sort'                => parent::escStr( $_POST['sortConditions'] ),
                  'prod_list'           => parent::escStr( $_POST['prod_list'] ),
                  'ma_cd'           => parent::escStr( $_POST['ma_cd'] ),
              );
              
              $ma_cd = $_SESSION['MA_CD'];
              $ma_name = $_SESSION['USER_NAME'];
              // 業者コードプルダウン
              $makerList = $OrderProduct->getMakerList();
              
              $prod_detail    = [];
              $prod_detail    = $OrderProduct->getOrderProductList($searchArray);
              
              $orderProductALLList = $OrderProduct->getOrderProductList($searchArray);
              // グループマスタレコード数
              $orderProductRecordCnt = count($orderProductALLList);
              // 表示レコード数
              $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

              // 指定ページ
              if($startFlag){
                  // 初期表示の場合
                  $pageNo = $this->getDuringTransitionPageNo($orderProductRecordCnt, $pagedRecordCnt);
              }else{
                  // その他の場合
                  $pageNo = $_SESSION["PAGE_NO"];
              }

              // シーケンスIDName
              $idName = "pro_cd";
              // 一覧表
              $orderProductList = $this->refineListDisplayNoSpecifiedPage($orderProductALLList, $idName, $pagedRecordCnt, $pageNo);
              $orderProductList = $this->modBtnDelBtnDisabledCheck($orderProductList);

              // 表示レコード数が11以上の場合、スクロールバーを表示
              $isScrollBar = false;
              if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($orderProductList) >= 15)
              {
                  $isScrollBar = true;
              }

              // 表示数リンクのロック処理
              $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
              // ページ数
              $pagedCnt = ceil($orderProductRecordCnt /  $pagedRecordCnt);
              // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
              $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

              // ソート時のマーク変更メソッド
              if($startFlag){
                  // ソートマーク初期化
                  $headerArray = $this->changeHeaderItemMark(0);
                  // NO表示用
                  $display_no = 0;
                  // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
                  $orderProductNoSortFlag = false;
              }else{
                  // 対象項目へマークを付与
                  $headerArray = $this->changeHeaderItemMark(parent::escStr( $_POST['sortConditions'] ));
                  // NO表示用
                  $display_no = $this->getDisplayNo( $orderProductRecordCnt, $pagedRecordCnt, $pageNo, parent::escStr( $_POST['sortConditions'] ) );
                  // ソートがを選択されたかどうかのチェックフラグ（userTablePanel.htmlにて使用）
                  $orderProductNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;
              }

              if($startFlag){
                  require_once './View/OrderProductSearch.html';
              }else{
                  require_once './View/OrderProductList.html';
              }

              $Log->trace("END   initialDisplay");

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
            $Log->trace("START orderProduct changeHeaderItemMark");

            // 初期化
            $headerArray = array(
                    'prodCdSortMark'   => '',
                    'prodNmSortMark'   => ''
            );
            if(!empty($sortNo))
            {
                $sortList[1]  = "prodCdSortMark";
                $sortList[2]  = "prodCdSortMark";
                $sortList[3]  = "prodNmSortMark";
                $sortList[4]  = "prodNmSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START orderProduct changeHeaderItemMark");

            return $headerArray;
        }
      
        /**
        * 自動採番
        * @return    商品番号
        */
         public function autoNumAction()
        {
           global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START autoNumAction");
            
            $OrderProduct = new OrderProduct();
            $prod_cd = $OrderProduct->getLastProdCd($_SESSION['MA_CD']);
            $country_cd = $OrderProduct->getCountryCd($_SESSION['MA_CD']);
            $business_cd = $OrderProduct->getBusinessCd($_SESSION['MA_CD']);
            $ret = "20".str_pad($country_cd, 2, "0", STR_PAD_LEFT).
                str_pad($business_cd, 4, "0", STR_PAD_LEFT).
                str_pad($prod_cd + 1, 4, "0", STR_PAD_LEFT);

            $Log->trace("END autoNumAction");
            echo $ret;
        }
        
        /**
        * 商品区分設定
        * @return    商品区分
        */
         public function setProdKbnAction()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START setProdKbnAction");
            
            $OrderProduct = new OrderProduct();
            $ret = $OrderProduct->getProdKbnCdList("",$_POST["prod_type"],$_POST["prod_kbn_list"]);

            $Log->trace("END setProdKbnAction");
            echo json_encode($ret);
        }
        
        /**
         * CSV出力
         * @return    なし
         */
        public function csvoutputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START csvoutputAction");
            if(!$_POST["csv_data"]){
                return;
            }
            $startDateM = date("Ymd");
            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            if( touch($file_path) ){
                // オブジェクト生成
                $file = new SplFileObject( $file_path, "w" ); 
                $strHead = "JANコード,GS1コード,商品コード,容量,入数,ケース,最低ロット,単価,メーカー希望価格,商品区分1,商品区分2,商品区分3,商品区分4,商品区分5";
                $csv_data = json_decode($_POST['csv_data'],true);
                $strHead = mb_convert_encoding($strHead, 'SJIS-win', 'UTF-8');
                $file = fopen($file_path, 'a');fwrite($file,$strHead."\r");
                $OrderProduct = new OrderProduct();
                foreach( $csv_data as $str ){               
                    $data           = [];
                    $data           = $OrderProduct->getOrderProductData($str, $_POST["ma_cd"]);
                    
                    $str = $data["jan_cd"].",".$data["gs1_code"].",".$data["pro_cd"].",".
                            $data["youryo"].",".$data["irisuu"].",".$data["case_num"].",".
                            $data["min_lot"].",".$data["tanka"].",".$data["teika"].",".
                            $data["prod_k_cd1"].",".$data["prod_k_cd2"].",".$data["prod_k_cd3"].",".
                            $data["prod_k_cd4"].",".$data["prod_k_cd5"];
                    
                    // 配列に変換
                    $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
                    
                    // 内容行を1行ごとにCSVデータへ
                    if($str[0] === '"'){
                        $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");
                    }else{
                        $file = fopen($file_path, 'a');fwrite($file,$str."\r");
                    }
                    
                }
            }
 
            // ダウンロード用
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers 
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=商品一覧".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");
        }
    }
      
?>
