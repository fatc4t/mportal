<?php
    /**
     * @file      帳票 - 売上モニターリストコントローラ
     * @author    millionet bhattarai
     * @date      2020/02/05
     * @version   1.00
     * @note      帳票 - 売上モニターリストの閲覧を行う
     */
    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetSalesMonitorList.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetSalesMonitorListController extends BaseController
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
            $LedgerSheetSalesMonitorList = new LedgerSheetSalesMonitorList();
            //モーダル
            $modal = new Modal();
            //店舗指定モーダル取得 
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            //店舗指定モーダル取得 
            $cust_detail    = [];
            $cust_detail    = $modal->getcust_detail();    
            $credit_detail    = [];
            $credit_detail    = $modal->getcredit_detail();
            
            // 画面フォームに日付を返す
            if(!isset($startDate) OR $startDate == ""){
                //現在日付の１日を設定
                $startDate =date('Y/m/d', strtotime('first day of ' . $month));
            }
            if(!isset($endDate) OR $endDate == ""){
                //現在日付の設定
                $endDate =date('Y/m/d', strtotime('Today' . $month));
            }  
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();   
            require_once './View/LedgerSheetSalesMonitorList.html';

        }
        public function getdataAction($mode)
        {
            $payload = file_get_contents('php://input');
            $joken = json_decode($payload,true);               
            global $Log;  // グローバル変数宣言
            $Log->trace("START getdataAction");  
            $LedgerSheetSalesMonitorList = new LedgerSheetSalesMonitorList();
            $data['dataget'] = $LedgerSheetSalesMonitorList->get_data($joken);
            print_r(json_encode($data));
            $Log->trace("END   getdataAction");
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
                $csv_data = json_decode($_POST['csv_data'],true);
                foreach( $csv_data as $str ){

                    // 配列に変換
                    $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
                    //$export_arr = explode(",", $str);
                    
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
            header("Content-Disposition: attachment; filename=売上モニターリスト".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }        
   
    }
?>
