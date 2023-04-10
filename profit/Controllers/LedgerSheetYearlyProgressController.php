<?php
    /**
     * @file      推移実績 [C]
     * @author    vergara miguel
     * @date      2019/03/01
     * @version   1.00
     * @note      帳票
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetYearlyProgress.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetYearlyProgressController extends BaseController
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
            $ledgerSheetYearlyProgress = new ledgerSheetYearlyProgress();

            $abbreviatedNameList = $ledgerSheetYearlyProgress->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

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
                $ledgerSheetYearlyProgressList = $ledgerSheetYearlyProgress->getFormListData($searchArray);
            }
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetYearlyProgressPanel.html';
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
            
            $ledgerSheetYearlyProgress = new ledgerSheetYearlyProgress();

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

            $ledgerSheetYearlyProgressList = $ledgerSheetYearlyProgress->getFormListData($searchArray);

            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "No");
            array_push($export_csv_title, "店舗");
            array_push($export_csv_title, "日付");
            array_push($export_csv_title, "税込売上金額");
            array_push($export_csv_title, "純売上金額");
            array_push($export_csv_title, "消費税");//pme 2019.03.13
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
                //print_r($ledgerSheetYearlyProgressList);
                foreach( $ledgerSheetYearlyProgressList as $rows ){

                    // 垂直ヘッダ
                    $list_no += 1;
                    $str = '"'.$list_no;// No

                    $str = $str.'","'.$rows['abbreviated_name'];
                    $str = $str.'","'.$rows['years'];

                    if($rows['months'] == '1'){$str = $str.'/1月';}//pme 2019.03.13
                    elseif($rows['months'] == '2'){$str = $str.'/2月';}
                    elseif($rows['months'] == '3'){$str = $str.'/3月';}
                    elseif($rows['months'] == '4'){$str = $str.'/4月';}
                    elseif($rows['months'] == '5'){$str = $str.'/5月';}
                    elseif($rows['months'] == '6'){$str = $str.'/6月';}
                    elseif($rows['months'] == '7'){$str = $str.'/7月';}
                    elseif($rows['months'] == '8'){$str = $str.'/8月';}
                    elseif($rows['months'] == '9'){$str = $str.'/9月';}
                    elseif($rows['months'] == '10'){$str = $str.'/10月';}
                    elseif($rows['months'] == '11'){$str = $str.'/11月';}
                    elseif($rows['months'] == '12'){$str = $str.'/12月';}

                    $str = $str.'","'.round($rows['pure_total']+$rows['tax_total'],0);

                    $str = $str.'","'.round($rows['pure_total'],0);
                    $str = $str.'","'.round($rows['tax_total'],0);// pme 2019.03.13
                    $str = $str.'","'.round($rows['total_amount'],0);
                    //pme 2019.03.13
                    if($rows['pure_total']==0 ||$rows['total_amount'] ==0){
                        $str = $str.'","0';
                    }else{
                        $heikintanka = $rows['pure_total'] / $rows['total_amount'];
                        $str = $str.'","'.round($heikintanka,2);
                    }
                    //pme end 2019.03.13
                    $str = $str.'","'.round($rows['return_amount'],0);
                    $str = $str.'","'.round($rows['return_total'],0);
                    $str = $str.'","'.round($rows['disc_total'],0);
                    $str = $str.'","'.round($rows['total_profit'],0);

                    $total = $rows['pure_total'];
                    $arari = $rows['total_profit'];
                    //pme 2019.03.13
                    if($total==0 || $arari ==0){
                        $str = $str.'","0';
                    }else{
                        $arariritsu = number_format($arari / $total * 100,2);
                        $str = $str.'","'.$arariritsu;
                    }
                    //pme end 2019.03.13
                    $str = $str.'"';

                    // 配列に変換
                    $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
                    $export_arr = explode(",", $str);
                    
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
            header("Content-Disposition: attachment; filename=推移実績".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
