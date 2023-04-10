<?php
    /**
     * @file      部門別売上集計表 [M]
     * @author    川橋
     * @date      2019/06/11
     * @version   1.00
     * @note      帳票 - 部門別売上集計表の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetProductClassification_zeinuki.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetProductClassification_zeinukiController extends BaseController
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
            //print_r($_POST);
            
            //開始年
            $startYear = "2010";

            $LedgerSheetProductClassification_zeinuki = new LedgerSheetProductClassification_zeinuki();
            $modal = new Modal();
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            $prodclass_detail    = [];
            $prodclass_detail    = $modal->getprod_class_detail();
			if(!isset($prodclass_detail['ooki'])){
				$prodclass_detail['ooki']    = [];
			}
			if(!isset($prodclass_detail['naka'])){
				$prodclass_detail['naka']    = [];
			}			
			//print_r($prodclass_detail);
            $param          = [];
            //print_r($_POST);
            if($_POST){
				$_POST['csv_data'] = '';
                $param = $_POST;
            }
            $param["prodclass_cd"]   = "";
            $param["org_id"]    = "";

            $searchArray = [];            

            // 日付リストを取得
            $startDate = parent::escStr( $_POST['start_date'] );
            //$startDate = parent::escStr( $_POST['start_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDate) OR $startDate == ""){
                //現在日付の１日を設定
                $startDate =date('Y/m/d', strtotime('first day of ' . $month));
            }
            
            // 日付リストを取得
            $endDate = parent::escStr( $_POST['end_date'] );

            // 画面フォームに日付を返す
            if(!isset($endDate) OR $endDate == ""){
                //現在日付の１日を設定
                $endDate = date('Y/m/d', strtotime('last day of ' . $month));
            }
            
            $organizationId = 'false';
            if($_POST['org_r'] === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
                }
            }
            
            
            $prodclass_cd = 'false';
            if($_POST['prodclass_r'] === ""){
                if($_POST['prodclass_select'] && $_POST['prodclass_select'] === 'empty'){
                    $prodclass_cd = $_POST['prodclass_cd'];
                }else{
                    $prodclass_cd = "'".$_POST['prodclass_select']."'";
                }
            }
            //$sectCdS = parent::escStr( $_POST['sectCdS'] );
			if($_POST['sort']){
				$sort = $_POST['sort'] ;
			}else{
				$sort = 'prod_t_cd1#1';
			}
            //$sort = ($mode === "initial") ? 'prod_t_cd1#1' : parent::escStr( $_POST['sort'] );
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'prodclass_cd'      => $prodclass_cd,
                'sort'              => $sort,
            );
            $data = '';
            if($_POST){
                $data = $LedgerSheetProductClassification_zeinuki->getFormListData($_POST);
            }

            // 検索組織
            $searchArray = $_POST;
            $searchArray['start_date'] = $startDate;
            $searchArray['end_date']   = $endDate;
            $searchArray['org_id']     = str_replace("'","",$_POST['org_id']);
            $searchArray['prodclass_cd'] = str_replace("'","",$_POST['prodclass_cd']);
            $searchArray['sort']       = $sort;
            
            $checked = "";
            if($_POST['tenpo']){
                $searchArray['tenpo'] = true;
            }
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
//print_r(searchArray);
                require_once './View/LedgerSheetProductClassification_zeinukiPanel.html';
            }
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

 
                // エンコードしたタイトル行を配列ごとCSVデータ化
                //$file = fopen($file_path, 'a');
                
                // 取得結果を画面と同じように再構築して行として搭載
                // 内容行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
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
            header("Content-Disposition: attachment; filename=分類別売上集計表".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");
 
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
            $Log->trace("START changeHeaderItemMark");

            // 初期化
            $headerArray = array(
                'tenpoSortMark'                 => '',
                'prodclass_nmSortMark'               => '',
                'sale_totalSortMark'            => '',
                'sale_total_percentSortMark'    => '',
                'sale_profitSortMark'           => '',
                'sale_profit_percentSortMark'   => '',
                'gross_profit_marginSortMark'   => '',
                'sale_amountSortMark'           => '',
                'sale_cust_cnt_sumSortMark'     => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]    = "tenpoSortMark";
                $sortList[2]    = "tenpoSortMark";
                $sortList[3]    = "prodclass_nmSortMark";
                $sortList[4]    = "prodclass_nmSortMark";
                $sortList[5]    = "sale_totalSortMark";
                $sortList[6]    = "sale_totalSortMark";
                $sortList[7]    = "sale_total_percentSortMark";
                $sortList[8]    = "sale_total_percentSortMark";
                $sortList[9]    = "sale_profitSortMark";
                $sortList[10]   = "sale_profitSortMark";
                $sortList[11]   = "sale_profit_percentSortMark";
                $sortList[12]   = "sale_profit_percentSortMark";
                $sortList[13]   = "gross_profit_marginSortMark";
                $sortList[14]   = "gross_profit_marginSortMark";
                $sortList[15]   = "sale_amountSortMark";
                $sortList[16]   = "sale_amountSortMark";
                $sortList[17]   = "sale_cust_cnt_sumSortMark";
                $sortList[18]   = "sale_cust_cnt_sumSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START changeHeaderItemMark");

            return $headerArray;
        }
    }
?>
