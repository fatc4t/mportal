<?php
    /**
     * @file      帳票 - 商品検索一覧コントローラ
     * @author    media craft
     * @date      2018/03/22
     * @version   1.00
     * @note      帳票 - 商品検索一覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetProduct.php';
    require '../attendance/Model/AggregateLaborCosts.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetProductController extends BaseController
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

            $ledgerSheetProduct = new ledgerSheetProduct();

            $startCd = parent::escStr( $_POST['start_code'] );
            $endCd = parent::escStr( $_POST['end_code'] );
            $prodName = parent::escStr( $_POST['prod_nm'] );
            $sectCd = parent::escStr( $_POST['sect_cd'] );
            $startTsa = parent::escStr( $_POST['start_tsa'] );
            $endTsa = parent::escStr( $_POST['end_tsa'] );
            $startEa = parent::escStr( $_POST['start_ea'] );
            $endEa = parent::escStr( $_POST['end_ea'] );
            $startPsa = parent::escStr( $_POST['start_psa'] );
            $endPsa = parent::escStr( $_POST['end_psa'] );

            $searchArray = array(
                'start_code'  => $startCd,
                'end_code'    => $endCd,
                'prod_kn'     => $prodName,
                'sect_cd'     => $sectCd,
                'start_tsa'   => $startTsa,
                'end_tsa'     => $endTsa,
                'start_ea'    => $startEa,
                'end_ea'      => $endEa,
                'start_psa'   => $startPsa,
                'end_psa'     => $endPsa,
            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetProductList = $ledgerSheetProduct->getFormListData($searchArray);

            // ヘッダ情報、Ｍコード情報取り出し
            foreach($ledgerSheetProductList as $data){
                $list[] = array(
                     'prod_cd' => $data['prod_cd'],
                     'prod_kn' => $data['prod_kn'],
                     'prod_nm' => $data['prod_nm'],
                     'sect_cd' => $data['sect_cd'],
                     'sect_nm' => $data['sect_nm'],
    //               'maker_cd' => $data['maker_cd'],
                     'supp_nm' => $data['supp_nm'],
                     'head_supp_cd' => $data['head_supp_cd'],
                     'jan_cd'  => $data['jan_cd'],
                     'tax_type' => $data['tax_type'],
                     'saleprice' => number_format($data['saleprice']),
                     'cust_saleprice' => $data['cust_saleprice'],
                     'head_costprice' => number_format($data['head_costprice']),
                     'total_stock_amount' => $data['total_stock_amount'],
                     'endmon_amount' => intval($data['endmon_amount']),
                     'abbreviated_name' => $data['abbreviated_name'],
                     'prod_sale_amount' => intval($data['prod_sale_amount']),
                );
            }
            //$list = $ledgerSheetProductList;

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-L');

            $header = '<!DOCTYPE html>'
                     .'<html>'
                     .'<head>'
                     .'    <meta charset="utf-8" />'
                     .'    <title>商品検索一覧</title>'
                     .'    <STYLE type="text/css">'
                     .'        html,body{width:100%;height:100%;}'
                     .'        header{width:100%;height:55px;}'
                     .'        main{width:100%;height:580px;}'
                     .'        footer{width:100%;height:25px;position:absolute;bottom:0;}'
                     .'        .barcode {'
                     .'            padding: 1.5mm;'
                     .'            margin: 0;'
                     .'            vertical-align: top;'
                     .'            color: #000044;'
                     .'        }'
                     .'        .barcodecell {'
                     .'            text-align: center;'
                     .'            vertical-align: middle;'
                     .'        }'
                     .'    </STYLE>'
                     .'</head>'
                     .'<body>'
                     .'    <div>'
                     .'    <header>'
                     .'        <div align="left" style="font-style:oblique; line-height: 5px;">'
                     .'            <h1><span>商品検索一覧</span></h1>'
                     .'        </div>'
                     .'        <table style="border-bottom:solid 3px #000000;line-height:25px;font-size:28px;">'
                     .'            <tr>'
                     .'                <th align="left" style="width:250px;font-style:oblique;">商品コード</th>'
                     .'                <th align="left" style="width:250px;font-style:oblique;">商品名カナ</th>'
                     .'                <th align="left" style="width:120px;font-style:oblique;">部門コード</th>'
                     .'                <th align="left" style="width:120px;font-style:oblique;">部門名</th>'
                     .'                <th align="right" style="width:120px;font-style:oblique;">売価</th>'
                     .'                <th align="right" style="width:120px;font-style:oblique;">原価</th>'
                     .'                <th align="right" style="width:120px;font-style:oblique;">在庫</th>'
                     .'                <th align="right" style="width:160px;font-style:oblique;">前月末在庫</th>'
                     .'                <th align="right" style="width:200px;font-style:oblique;">週別実積</th>'
                     .'            </tr>'
                     .'        </table>'
                     .'    </header>'
                     .'    <main>';

            $count = 1;
            $page = 1;
            $sum = ceil(count($list)/13);

            foreach($list as $data){
                if($count == 1){
                    $html .= $header;
                }

                $html .= '        <table style="border-bottom:solid 1px #000000;line-height:10px;font-size:16px;">'
                        .'            <tr>'
                        .'                <td align="left" style="width:250px;">'.$data['prod_cd'].'</td>'
                        .'                <td align="left" style="width:250px;">'.$data['prod_nm'].'</td>'
                        .'                <td align="left" style="width:120px;">'.$data['sect_cd'].'</td>'
                        .'                <td align="left" style="width:120px;">'.$data['sect_nm'].'</td>'
                        .'                <td align="right" style="width:120px;">'.$data['saleprice'].'</td>'
                        .'                <td align="left" style="width:250px;">'.$data['cust_saleprice'].'</td>'
                        .'                <td align="right" style="width:120px;">'.$data['head_costprice'].'</td>'
                        .'                <td align="right" style="width:120px;">'.$data['total_stock_amount'].'</td>'
                        .'                <td align="right" style="width:160px;">'.$data['endmon_amount'].'</td>'
                        .'                <td align="right" style="width:200px;">'.$data['prod_sale_amount'].'</td>'
                        .'            </tr>'
                        .'            <tr>'
                        .'                <td align="left" colspan="9">'
                        .'                    <barcode code="'.$data['jan_cd'].'" type="EAN13" size="1.8" height="0.3" />'
                        .'                </td>'
                        .'            </tr>'
                        .'        </table>';

                if($count > 13 || end($list) == $data){
                    $count = 0;
                    $html .= '    </main>'
                            .'    <footer>'
                            .'        <table style="border-top:solid 3px #434359;font-size:18px;">'
                            .'            <tr>'
                            .'                <td style="width:900px;font-style:oblique;">'.date(Y年m月d日).'</td>'
                            .'                <td style="width:200px;font-style:oblique;">'.$page.'/'.$sum.'ページ</td>'
                            .'            <tr>'
                            .'        </table>'
                            .'    </footer>'
                            .'</div>'
                            .'</body>'
                            .'</html>';

                    $mpdf->WriteHTML("$html");
                    if(end($list) != $data){
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

            $ledgerSheetProduct = new ledgerSheetProduct();

            $sectList = $ledgerSheetProduct->getSectListData(array());

            $startCd = parent::escStr( $_POST['start_code'] );
            $endCd = parent::escStr( $_POST['end_code'] );
            $prodName = parent::escStr( $_POST['prod_kn'] );
            $sectCd = parent::escStr( $_POST['sect_cd'] );
            $startTsa = parent::escStr( $_POST['start_tsa'] );
            $endTsa = parent::escStr( $_POST['end_tsa'] );
            $startEa = parent::escStr( $_POST['start_ea'] );
            $endEa = parent::escStr( $_POST['end_ea'] );
            $startPsa = parent::escStr( $_POST['start_psa'] );
            $endPsa = parent::escStr( $_POST['end_psa'] );

            // 初期表示以外だったら
            if($mode != "initial"){
                $searchArray = array(
                    'start_code'  => $startCd,
                    'end_code'    => $endCd,
                    'prod_kn'     => $prodName,
                    'sect_cd'     => $sectCd,
                    'start_tsa'   => $startTsa,
                    'end_tsa'     => $endTsa,
                    'start_ea'    => $startEa,
                    'end_ea'      => $endEa,
                    'start_psa'   => $startPsa,
                    'end_psa'     => $endPsa,
                );

                // 帳票フォーム一覧データ取得
                $ledgerSheetProductList = $ledgerSheetProduct->getFormListData($searchArray);

                // ヘッダ情報、Ｍコード情報取り出し
                foreach($ledgerSheetProductList as $data){
                    $list[] = array(
                         'prod_cd' => $data['prod_cd'],
                         'prod_nm' => $data['prod_nm'],
                         'prod_capa' => $data ['prod_capa'],
                         'prod_kn' => $data['prod_kn'],
                         'sect_cd' => $data['sect_cd'],
                         'sect_nm' => $data['sect_nm'],
                         'supp_nm' => $data['supp_nm'],
                         'head_supp_cd' => $data['head_supp_cd'],
                         'saleprice' => number_format($data['saleprice']),
                         'cust_saleprice' => number_format($data['cust_saleprice']),
                         'tax_type' => $data['tax_type'],
                         'head_costprice' => number_format($data['head_costprice']),
                         'total_stock_amount' => $data['total_stock_amount'],
                         'endmon_amount' => intval($data['endmon_amount']),
                         'abbreviated_name' => $data['abbreviated_name'],
                         'prod_sale_amount' => intval($data['prod_sale_amount']),
                    );
                }
            }

            // 画面フォームに日付を返す
            if(!isset($startDate) OR $startDate == ""){
                //現在日付の１日を設定
                $startDate =date('Y/m/d', strtotime('first day of ' . $month));
            }

            if(!isset($endDate) OR $endDate == ""){
                //現在日付の末日を設定
                $endDate = date('Y/m/d', strtotime('last day of ' . $month));
            }

            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial"){
                require_once './View/LedgerSheetProductPanel.html';
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
            
            $ledgerSheetProduct = new ledgerSheetProduct();

            $startCd = parent::escStr( $_POST['start_code'] );
            $endCd = parent::escStr( $_POST['end_code'] );
            $prodName = parent::escStr( $_POST['prod_kn'] );
            $sectCd = parent::escStr( $_POST['sect_cd'] );
            $startTsa = parent::escStr( $_POST['start_tsa'] );
            $endTsa = parent::escStr( $_POST['end_tsa'] );
            $startEa = parent::escStr( $_POST['start_ea'] );
            $endEa = parent::escStr( $_POST['end_ea'] );
            $startPsa = parent::escStr( $_POST['start_psa'] );
            $endPsa = parent::escStr( $_POST['end_psa'] );

                $searchArray = array(
                    'start_code'  => $startCd,
                    'end_code'    => $endCd,
                    'prod_kn'     => $prodName,
                    'sect_cd'     => $sectCd,
                    'start_tsa'   => $startTsa,
                    'end_tsa'     => $endTsa,
                    'start_ea'    => $startEa,
                    'end_ea'      => $endEa,
                    'start_psa'   => $startPsa,
                    'end_psa'     => $endPsa,
                );

                // 帳票フォーム一覧データ取得
                $ledgerSheetProductList = $ledgerSheetProduct->getFormListData($searchArray);

                // ヘッダ情報、Ｍコード情報取り出し
                foreach($ledgerSheetProductList as $data){
                    $list[] = array(
                         'prod_cd' => $data['prod_cd'],
                         'prod_nm' => $data['prod_nm'],
                         'prod_capa' => $data ['prod_capa'],
                         'prod_kn' => $data['prod_kn'],
                         'sect_cd' => $data['sect_cd'],
                         'sect_nm' => $data['sect_nm'],
                         'supp_nm' => $data['supp_nm'],
                         'head_supp_cd' => $data['head_supp_cd'],
                         'saleprice' => number_format($data['saleprice']),
                         'cust_saleprice' => number_format($data['cust_saleprice']),
                         'tax_type' => $data['tax_type'],
                         'head_costprice' => number_format($data['head_costprice']),
                         'total_stock_amount' => $data['total_stock_amount'],
                         'endmon_amount' => intval($data['endmon_amount']),
                         'abbreviated_name' => $data['abbreviated_name'],
                         'prod_sale_amount' => intval($data['prod_sale_amount']),
                    );
                }

            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "No");
            array_push($export_csv_title, "商品コード");
            array_push($export_csv_title, "商品名");
            array_push($export_csv_title, "容量");
            array_push($export_csv_title, "商品名カナ");
            array_push($export_csv_title, "部門コード");
            array_push($export_csv_title, "部門");
            array_push($export_csv_title, "仕入先コード");
            array_push($export_csv_title, "仕入先名");
            array_push($export_csv_title, "売価");
            array_push($export_csv_title, "原価");
            array_push($export_csv_title, "在庫");
            array_push($export_csv_title, "前月末在庫");
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
                foreach( $ledgerSheetProductList as $rows ){
                    
                    // 垂直ヘッダ
                    $list_no += 1;

                    $str = '"'.$list_no;// 順位

                    $str = $str.'","'.$rows['prod_cd'];
                    $str = $str.'","'.$rows['prod_nm'];
                    $str = $str.'","'.$rows['prod_capa'];
                    $str = $str.'","'.$rows['prod_kn'];
                    $str = $str.'","'.$rows['sect_cd'];
                    $str = $str.'","'.$rows['sect_nm'];
                    $str = $str.'","'.$rows['head_supp_cd'];
                    $str = $str.'","'.$rows['supp_nm'];
                    $str = $str.'","'.round($rows['saleprice'],0);
                    $str = $str.'","'.round($rows['cust_saleprice'],0);
                    $str = $str.'","'.round($rows['tax_type'],0);
                    $str = $str.'","'.round($rows['head_costprice'],0);
                    $str = $str.'","'.round($rows['total_stock_amount'],2);
                    $str = $str.'","'.round($rows['endmon_amount'],0);
                    $str = $str.'","'.round($rows['abbreviated_name'],0);
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
            header("Content-Disposition: attachment; filename=商品検索一覧".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
