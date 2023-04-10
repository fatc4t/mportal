<?php
    /**
     * @file      帳票 - 日次コントローラ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 日次の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetOrder.php';
    require '../attendance/Model/AggregateLaborCosts.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetOrderController extends BaseController
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
         * PDF出力
         * @note     PDF出力
         * @return   無
         */
        public function pdfoutputAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START pdfoutputAction");

            $ledgerSheetOrder = new ledgerSheetOrder();



            //テスト時
            //$html = file_get_contents("./View/LedgerSheetOrderPDFPanel.html");

            //検索条件
            $startDate = parent::escStr( $_POST['start_date'] );
            $endDate = parent::escStr( $_POST['end_date'] );
            $suppCd  = parent::escStr( $_POST['supplierName'] );
            $searchArray = array(
                'supp_cd' => $suppCd,
                'start_date' => $startDate,
                'end_date' => $endDate
            );

            //仕入先名の取得
            $suppNm = '指定なし';
            if($suppCd!==''){
                $suppNm = $ledgerSheetOrder->getSupplierName(array_unique($suppCd));
            }
            //注文データを取得
            $list = $ledgerSheetOrder->getFormListData($searchArray);

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-P');

            $html   = '';
            $header = '';

            $header  = '<!DOCTYPE html>';
            $header .= '<html>';
            $header .= '<head>';
            $header .= '    <meta charset="utf-8" />';
            $header .= '    <title>注文書</title>';
            $header .= '    <STYLE type="text/css">';
            $header .= '        html,body{width:100%;height:100%;}';
            $header .= '        header{width:100%;height:95px;}';
            $header .= '        main{width:100%;height:850px;}';
            $header .= '        footer{width:100%;height:25px;position:absolute;bottom:0;}';
            $header .= '        .barcode {';
            $header .= '            padding: 1.5mm;';
            $header .= '            margin: 0;';
            $header .= '            vertical-align: top;';
            $header .= '            color: #000044;';
            $header .= '        }';
            $header .= '        .barcodecell {';
            $header .= '            text-align: center;';
            $header .= '            vertical-align: middle;';
            $header .= '        }';
            $header .= '    </STYLE>';
            $header .= '</head>';
            $header .= '<body>';
            $header .= '<!--<div style="page-break-before: always;">-->';
            $header .= '    <div>';
            $header .= '    <header>';
            $header .= '        <div align="center" style="font-style:oblique; line-height: 5px;">';
            $header .= '            <h1 style="text-align:center;"><span>注文書</span></h1>';
            $header .= '        </div>';
            $header .= '        <table style="border-bottom:solid 3px #000000;line-height:25px;font-size:28px;">';
            $header .= '            <tr>';
            $header .= '                <td align="left" colspan="3">仕入先： '.$suppNm.'</td>';
            $header .= '                <td align="right" colspan="2"></td>';
            $header .= '            </tr>';
            $header .= '            <tr>';
            $header .= '                <td align="left" colspan="4"></td>';
            $header .= '                <td align="left" colspan="1">発注日：'.$startDate.'</td>';
            $header .= '            </tr>';
            $header .= '            <tr>';
            $header .= '                <td align="left" colspan="4"></td>';
            $header .= '                <td align="left" colspan="1">納品日：'.$endDate.'</td>';
            $header .= '            </tr>';
            $header .= '            <tr>';
            $header .= '                <th align="left" style="width:100px;font-style:oblique;">SEQ</th>';
            $header .= '                <th align="left" style="width:200px;font-style:oblique;">商品CD</th>';
            $header .= '                <th align="left" style="width:300px;font-style:oblique;">商品名</th>';
            $header .= '                <th align="left" style="width:150px;font-style:oblique;">数量</th>';
            $header .= '                <th align="left" style="width:300px;font-style:oblique;"></th>';
            $header .= '            </tr>';
            $header .= '        </table>';
            $header .= '    </header>';
            $header .= '    <main>';

            $count = 1;
            $page = 1;
            $sum = ceil(count($list)/18);

            foreach($list as $data)
            {
                if($count == 1)
                {
                    $html .= $header;
                }

                $html .= '<table style="border-bottom:solid 1px #000000;line-height:30px;font-size:22px;">'
                        .'            <tr>'
                        .'                <td align="left" style="width:100px;">'.$data['hideseq'].'</td>'
                        .'                <td align="left" style="width:200px;">'.$data['prod_cd'].'</td>'
                        .'                <td align="left" style="width:300px;">'.$data['prod_kn'].'</td>'
                        .'                <td align="left" style="width:150px;text-align:right">'.$data['ord_amount'].'</td>'
                        .'                <td align="right" style="width:300px;">'
                        .'                    <barcode code="'.$data['jan_cd'].'" type="EAN13" size="2" height="0.3"/>'
                        .'                </td>'
                        .'            </tr>'
                        .'        </table>';

                if($count > 18 || end($list) == $data)
                {
                    $count = 0;
                    $html .= '    </main>'
                             .'    <footer>'
                             .'        <table style="border-top: solid 3px #434359;width:100%; font-size: 20px;">'
                             .'            <tr>'
                             .'                <td style="width:800px; font-style:oblique;">'.date(Y年m月d日).'</td>'
                             .'                <td style="width:300px; font-style:oblique;" align="rigth">'.$page.'/'.$sum.'ページ</td>'
                             .'            <tr>'
                             .'        </table>'
                             .'    </footer>'
                             .'</div>'
                             .'</body>'
                             .'</html>';
                    $mpdf->WriteHTML("$html");
                    if(end($list) != $data)
                    {
                        $mpdf->AddPage();
                    }
                    $html = "";
                    $page++;
                }
                $count++;
            }
            $mpdf->Output();

            exit;

            $Log->trace("END   pdfoutputAction");
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

            $ledgerSheetOrder = new ledgerSheetOrder();

            $abbreviatedNameList = $ledgerSheetOrder->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

            $supplierList = $ledgerSheetOrder->getSuppliersList();  //仕入先一覧の取得

            // 日付リストを取得
            $startDate = parent::escStr( $_POST['start_date'] );
            $endDate = parent::escStr( $_POST['end_date'] );

            // 画面フォームに日付を返す
            if($mode === "initial"){
                if(!isset($startDate) OR $startDate == "")
                {
                    //現在日付の１日を設定
                    $startDate =date('Y/m/d', strtotime('first day of ' . $month));
                }

                if(!isset($endDate) OR $endDate == "")
                {
                    //現在日付の末日を設定
                    $endDate = date('Y/m/d', strtotime('last day of ' . $month));
                }
                $searchArray = array(
                    'supp_cd' => parent::escStr( $_POST['supplierName'] ),
                    'start_date' => $startDate,
                    'end_date' => $endDate
                );
            }

            if($mode != "initial")
            {
                // 帳票フォーム一覧データ取得
                $ledgerSheetOrderList = $ledgerSheetOrder->getFormListData($searchArray);
    
                    // 合計計算用変数
    
                    $sumAmount = 0;
                        // 合計行を取得
                        foreach($ledgerSheetOrderList as $data){
                            $sumAmount  += $data["ord_amount"];
                        
                        $sumLine = array(
                            'No'          		=> '',       
                            'denno'   			=> '',
                            'prod_cd'       	=> '',    
                            'prod_nm'  			=> '',   
                            'prod_kn'       	=> '合計',   
                            'prod_capa'  		=> '',    
                            'ord_amount'      	=> $sumAmount,  
                            'ord_date'          => '',      
                            'arr_date' 			=> '',     
                            'abbreviated_name'  => '',                 
                            );
                }
                // 合計行を追加
                array_push($ledgerSheetOrderList, $sumLine);
            
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END initialDisplay");
            }
            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetOrderPanel.html';
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
            
            $ledgerSheetOrder = new ledgerSheetOrder();

            // 日付リストを取得
            $startDate = parent::escStr( $_POST['start_date'] );
            $endDate = parent::escStr( $_POST['end_date'] );

            // 画面フォームに日付を返す
            if(!isset($startDate) OR $startDate == "")
            {
                //現在日付の１日を設定
                $startDate =date('Y/m/d');
            }

            if(!isset($endDate) OR $endDate == "")
            {
                //現在日付の末日を設定
                //$endDate = date('Y/m/d', strtotime('last day of ' . $month));
            }
            $searchArray = array(
                'supp_cd' => parent::escStr( $_POST['supplierName'] ),
                'start_date' => $startDate,
                'end_date' => $endDate
            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetOrderList = $ledgerSheetOrder->getFormListData($searchArray);

            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "No");
            array_push($export_csv_title, "伝票");
            array_push($export_csv_title, "商品コード");
            array_push($export_csv_title, "商品名");
            array_push($export_csv_title, "商品名カナ");
            array_push($export_csv_title, "容量");
            array_push($export_csv_title, "数量");
            array_push($export_csv_title, "発注日");
            array_push($export_csv_title, "納品日");
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
                foreach( $list as $rows ){
                    
                    // 垂直ヘッダ
                    $list_no += 1;

                    $str = '"'.$list_no;// 順位

                    $str = $str.'","'.$rows['denno'];
                    $str = $str.'","'.$rows['prod_cd'];
                    $str = $str.'","'.$rows['prod_nm'];
                    $str = $str.'","'.$rows['prod_kn'];
                    $str = $str.'","'.$rows['prod_capa'];
                    $str = $str.'","'.$rows['ord_amount'];
                    $str = $str.'","'.$rows['start_date'];
                    $str = $str.'","'.$rows['end_date'];
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
            header("Content-Disposition: attachment; filename=注文書".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
