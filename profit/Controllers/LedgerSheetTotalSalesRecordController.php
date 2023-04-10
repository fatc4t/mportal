<?php
    /**
     * @file      売上実績 [C]
     * @author    vergara miguel
     * @date      2019/02/27
     * @version   1.00
     * @note      帳票
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetTotalSalesRecord.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetTotalSalesRecordController extends BaseController
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
            $ledgerSheetTotalSalesRecord = new ledgerSheetTotalSalesRecord();

            $abbreviatedNameList = $ledgerSheetTotalSalesRecord->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

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

            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'organizationID'    => $organizationId,

            );
            if($mode != "initial")
            {
                // 帳票フォーム一覧データ取得
                $ledgerSheetTotalSalesRecordList = $ledgerSheetTotalSalesRecord->getFormListData($searchArray);

    
                    // 合計計算用変数
    
                    $sumAmount = 0;
                    $retAmount  = 0;
                    $totalMoney = 0;
                    $totalTax = 0;
                    $totalMoneyI = 0;
                    $totalDisc = 0;
                    $totalProfit = 0;
                    $totalAmount = 0;
                    $retTotal = 0;
                    $arari = 0;
                    $avgPrice = 0;
                        // 合計行を取得
                        foreach($ledgerSheetTotalSalesRecordList as $data){
                           
                            $retAmount  += $data['return_amount'];
                            $retTotal   += $data['return_total'];
                            $totalMoney += ($data['pure_total_i']+$data['total_tax']);
                            $totalTax   += $data['total_tax'];
                            $totalMoneyI += $data['pure_total_i'];
                            $totalDisc   += $data['disc_total'];
                            $totalProfit += $data['total_profit_i'];
                            $totalAmount += $data['total_amount'];
                            $arari       += ($data['pure_total_i'] / $data['total_profit_i']);
                            $avgPrice   += ($totalMoneyI / $data['total_amount']);
                    

                        $sumLine = array(
                            'No' => '',   
                            'hi' => '合計',
                            'pure_total' => $totalMoney,
                            'pure_total_i' =>  $totalMoneyI,
                            'total_tax'    => $totalTax,
                            'total_amount'   =>   $totalAmount,
                            'total_total'       => $avgPrice,
                            'return_amount' => $retAmount,
                            'return_total'  => $retTotal,
                            'disc_total'  =>  $totalDisc,
                            'total_profit_i' => $totalProfit,
                            'total_profit' => $arari,


                            
                            );
                }
                // 合計行を追加
                array_push($ledgerSheetTotalSalesRecordList, $sumLine);




            }
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetTotalSalesRecordPanel.html';
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
            
            $ledgerSheetTotalSalesRecord = new ledgerSheetTotalSalesRecord();

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

            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'organizationID'    => $organizationId,

            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetTotalSalesRecordList = $ledgerSheetTotalSalesRecord->getFormListData($searchArray);

            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "No");
            if(isset($_POST['tenpo'])){
                array_push($export_csv_title, "店舗");
            }
            array_push($export_csv_title, "日付");
            array_push($export_csv_title, "税込売上金額");
            array_push($export_csv_title, "純売上金額");
            array_push($export_csv_title, "売上数量");
            array_push($export_csv_title, "平均単価");
            array_push($export_csv_title, "返品数量");
            array_push($export_csv_title, "返品金額");
            array_push($export_csv_title, "引金額");
            array_push($export_csv_title, "粗利金額");
            array_push($export_csv_title, "粗利率");

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

                foreach( $ledgerSheetTotalSalesRecordList as $rows ){

                    // 垂直ヘッダ
                    $list_no += 1;
                    $str = '"'.$list_no;// No

                    if(isset($_POST['tenpo'])){
                        $str = $str.'","'.$rows['abbreviated_name'];
                    }
                    $str = $str.'","'.$rows['proc_date'];
                    $str = $str.'","'.round($rows['total_total'],0);
                    $str = $str.'","'.round($rows['pure_total'],0);
                    $str = $str.'","'.round($rows['total_amount'],0);

		    $heikintanka = $rows['total_total'] / $rows['total_amount'];

		    if(is_nan($heikintanka)){
	                    $str = $str.'","'.'0';
		    }else{
	                    $str = $str.'","'.round($heikintanka,2);
		    }

                    $str = $str.'","'.round($rows['return_amount'],0);
                    $str = $str.'","'.round($rows['return_total'],0);
                    $str = $str.'","'.round($rows['disc_total'],0);
                    $str = $str.'","'.round($rows['total_profit'],0);

                    $total = $rows['total_total'];
                    $arari = $rows['total_profit'];
                    $arariritsu = number_format($arari / $total * 100,2);
                   if(!is_numeric($arariritsu)){
	                    $str = $str.'","'.'0';
		    }else{
	                    $str = $str.'","'.$arariritsu;
		    }

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
            header("Content-Disposition: attachment; filename=売上実績".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
