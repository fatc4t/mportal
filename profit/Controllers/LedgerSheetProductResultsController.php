<?php
    /**
     * @file      商品別実績
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note     商品別実績の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // コスト帳票処理モデル
    require './Model/LedgerSheetProductResults.php';

    require '../attendance/Model/AggregateLaborCosts.php';

    // Excel読み込み用ファイル
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel.php';
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel/IOFactory.php';
    
    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetProductResultsController extends BaseController
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
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            
            $this->initialDisplay("show");
            
            $Log->trace("END   showAction");
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

            if( touch($file_path) ){
                // オブジェクト生成
                $file = new SplFileObject( $file_path, "w" ); 

                // 取得結果を画面と同じように再構築して行として搭載
                $csv_data = json_decode($_POST['csv_data'],true);
                foreach( $csv_data as $str ){

                    // 内容行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
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
            header("Content-Disposition: attachment; filename=商品別実績".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }

        /**
         * Excel出力
         * @note     Excel出力
         * @return   無
         */
        public function exoutputAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START exoutputAction");

            $this->initialDisplay("exoutput");
            
            $Log->trace("END   exoutputAction");
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

            $ledgerSheetProductResults = new LedgerSheetProductResults();
            $modal = new Modal();

            // 帳票フォーム一覧データ取得
            $data           = [];
            $data           = $ledgerSheetProductResults->get_data();
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            $sect_detail    = [];
            $sect_detail    = $modal->getsect_detail();
            $prod_detail    = [];
            $prod_detail    = $modal->getprod_detail();
            $supp_detail    = [];
            $supp_detail    = $modal->getsupp_detail();
            $spe_sales_lst  = [];
            $spe_sales_lst  = $ledgerSheetProductResults->getspe_sales_list();
            $spe_sales_detail = [];
            $spe_sales_detail = $ledgerSheetProductResults->getspe_sales_detail();
            
            $Sizelist       = [];
            $base           = new BaseModel();
            $Sizelist       = $base->getSizeList();            
            $Log->trace("END   initialDisplay");

            
            require_once './View/LedgerSheetProductResultsPanel.html';

        }

        
    }
?>
