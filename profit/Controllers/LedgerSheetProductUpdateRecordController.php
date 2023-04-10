<?php
    /**
     * @file      商品更新記録 [C]
     * @author    vergara miguel
     * @date      2019/02/20
     * @version   1.00
     * @note      帳票
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetProductUpdateRecord.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetProductUpdateRecordController extends BaseController
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
            $ledgerSheetProductUpdateRecord = new ledgerSheetProductUpdateRecord();

            $abbreviatedNameList = $ledgerSheetProductUpdateRecord->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

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
            $prodK   = parent::escStr( $_POST['prod_k'] );
            $prodS   = parent::escStr( $_POST['prod_s'] );
            $prodNm  = parent::escStr( $_POST['prod_nm'] );
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'organizationID'    => $organizationId,
                'prod_k'            => $prodK,
                'prod_s'            => $prodS,
                'prod_nm'           => $prodNm,
            );

            if($mode != "initial")
            {
                // 帳票フォーム一覧データ取得
                $ledgerSheetProductUpdateRecordList = $ledgerSheetProductUpdateRecord->getFormListData($searchArray);
            }
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetProductUpdateRecordPanel.html';
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
            
            $ledgerSheetProductUpdateRecord = new ledgerSheetProductUpdateRecord();

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
            $prodK   = parent::escStr( $_POST['prod_k'] );
            $prodS   = parent::escStr( $_POST['prod_s'] );
            $prodNm  = parent::escStr( $_POST['prod_nm'] );
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'organizationID'    => $organizationId,
                'prod_k'            => $prodK,
                'prod_s'            => $prodS,
                'prod_nm'           => $prodNm,
            );

            // 帳票フォーム一覧データ取得

            $ledgerSheetProductUpdateRecordList = $ledgerSheetProductUpdateRecord->getFormListData($searchArray);

            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "No");
            array_push($export_csv_title, "商品コード");
            array_push($export_csv_title, "商品");
            array_push($export_csv_title, "商品カナ");
            array_push($export_csv_title, "容量");
            array_push($export_csv_title, "部門コード");
            array_push($export_csv_title, "メーカーコード");
            array_push($export_csv_title, "仕入先コード");
            array_push($export_csv_title, "原価");
            array_push($export_csv_title, "定価");
            array_push($export_csv_title, "売価");
            array_push($export_csv_title, "発注ロット");
            array_push($export_csv_title, "返品ロット");
            array_push($export_csv_title, "返品不可");
            array_push($export_csv_title, "店舗");

            if( touch($file_path) ){
                // オブジェクト生成
                $file = new SplFileObject( $file_path, "w" ); 
                // タイトル行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
                foreach( $export_csv_title as $key => $val ){
                    $export_header[] = mb_convert_encoding($val, 'SJIS-win', 'UTF-8');
                }
 
                // エンコードしたタイトル行を配列ごとCSVデータ化
                $file = fopen($file_path, 'a');fwrite($file,implode(',',$export_header)."\r");
                
                // 取得結果を画面と同じように再構築して行として搭載
                // 内容行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
                $list_no = 0;

                foreach( $ledgerSheetProductUpdateRecordList as $rows ){

                    // 垂直ヘッダ
                    $list_no += 1;
                    $str = '"'.$list_no;// No

                    $str = $str.'","'.$rows['prod_cd'];
                    $str = $str.'","'.$rows['prod_nm'];
                    $str = $str.'","'.$rows['prod_kn'];
                    $str = $str.'","'.$rows['prod_capa'];
                    $str = $str.'","'.$rows['sect_cd'];
                    $str = $str.'","'.$rows['maker_cd'];
                    $str = $str.'","'.$rows['head_supp_cd'];
                    $str = $str.'","'.$rows['head_costprice'];
                    $str = $str.'","'.round($rows['fixeprice'],2);
                    $str = $str.'","'.round($rows['saleprice'],0);
                    $str = $str.'","'.round($rows['return_lot'],0);
                    $str = $str.'","'.round($rows['order_lot'],0);
		    if ($rows['noreturn_kbn'] == '0') {
	                    $str = $str.'","'.'可能';
		    }else{
	                    $str = $str.'","'.'不可';
                    }
                    $str = $str.'","'.$rows['abbreviated_name'];
                    $str = $str.'"';

                    // 配列に変換
                    $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
                    //$export_arr = explode(",", $str);
                    
                    // 内容行を1行ごとにCSVデータへ
                    $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");
		    }
           }
 
            // ダウンロード用
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers 
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=商品更新記録".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
