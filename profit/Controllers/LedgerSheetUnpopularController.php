<?php
    /**
     * @file      帳票 - 死に筋商品リストコントローラ
     * @author    media craft
     * @date      2018/03/22
     * @version   1.00
     * @note      帳票 - 死に筋商品リストを行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetUnpopular.php';
    require '../attendance/Model/AggregateLaborCosts.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetUnpopularController extends BaseController
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

            $ledgerSheetUnpopular = new ledgerSheetUnpopular();

            $suppCd = parent::escStr( $_POST['supp_cd'] );
            $months = parent::escStr( $_POST['months'] );
            $counts = parent::escStr( $_POST['counts'] );

            $searchArray = array(
                'supp_cd'  => $suppCd,
                'months'   => $months,
                'counts'   => $counts,
            );

            $ledgerSheetUnpopularList = $ledgerSheetUnpopular->getFormListData($searchArray);

            foreach($ledgerSheetUnpopularList as $data){
                $list[] = array(
                    'prod_cd' => $data['prod_cd'],
                    'prod_kn' => $data['prod_kn'],
                    'sect_cd' => $data['sect_cd'],
                    'dead_months' => $data['dead_months'],
                    'supp_cd' => $data['supp_cd'],
                    'supp_nm' => $data['supp_nm'],
                    'total_stock_amount' => intval($data['total_stock_amount']),
                );
            }

            //$list = $ledgerSheetUnpopularList;

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-P');

            $header = '<!DOCTYPE html>'
                     .'<html>'
                     .'<head>'
                     .'    <meta charset="utf-8" />'
                     .'    <title>死に筋商品リスト</title>'
                     .'    <STYLE type="text/css">'
                     .'        html,body{width:100%;height:100%;}'
                     .'        header{width:100%;height:75px;}'
                     .'        main{width:100%;height:850px;}'
                     .'        footer{width:100%;height:15px;position:absolute;bottom:0;}'
                     .'    </STYLE>'
                     .'</head>'
                     .'<body>'
                     .'    <div>'
                     .'    <header>'
                     .'        <div align="left" style="font-style:oblique; line-height: 5px;">'
                     .'            <h1><span>死に筋商品リスト</span></h1>'
                     .'        </div>'
                     .'        <table style="border-bottom:solid 3px #000000;line-height:25px;font-size:28px;">'
                     .'            <tr>'
                     .'                <td style="line-height:40px;font-style:oblique;" colspan="5">仕入先：'.$suppCd.'</td>'
                     .'            </tr>'
                     .'            <tr>'
                     .'                <th align="left" style="width:320px;font-style:oblique;">　　　商品CD</th>'
                     .'                <th align="left" style="width:350px;font-style:oblique;">商品名</th>'
                     .'                <th align="left" style="width:150px;font-style:oblique;">部門</th>'
                     .'                <th align="left" style="width:100px;font-style:oblique;">死に筋月数</th>'
                     .'                <th align="left" style="width:200px;font-style:oblique;">　在庫数量</th>'
                     .'            </tr>'
                     .'        </table>'
                     .'    </header>'
                     .'    <main>'
                     .'        <table align="top" style="line-height:40px;font-size:24px;">';

            $count = 1;
            $page = 1;
            $sum = ceil(count($list)/13);

            foreach($list as $data){
                if($count == 1){
                    $html .= $header;
                }

                $html .= '            <tr>'
                        .'                <td align="center" style="width:320px;">'.$data['prod_cd'].'</td>'
                        .'                <td align="left" style="width:350px;">'.$data['prod_kn'].'</td>'
                        .'                <td align="left" style="width:150px;">'.$data['sect_cd'].'</td>'
                        .'                <td align="center" style="width:100px;">'.$data['dead_months'].'</td>'
                        .'                <td align="center" style="width:200px;">'.intval($data['total_stock_amount']).'</td>'
                        .'            </tr>';

                if($count > 29 || end($list) == $data){
                    $count = 0;
                    $html .= '        </table>'
                            .'</main>'
                            .'    <footer>'
                            .'        <table style="font-style:oblique;border-top: solid 3px #434359;width:100%;font-size: 20px;">'
                            .'            <tr>'
                            .'                <td style="width:900px; font-style:oblique;">'.date(Y年m月d日).'</td>'
                            .'                <td style="width:200px; font-style:oblique;">'.$page.'/'.$sum.'ページ</td>'
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

            $ledgerSheetUnpopular = new ledgerSheetUnpopular();

            $suppCd = parent::escStr( $_POST['supp_cd'] );
            $months = parent::escStr( $_POST['months'] );
            $counts = parent::escStr( $_POST['counts'] );

            // 初期表示以外だったら
            if($mode != "initial"){
                $searchArray = array(
                    'supp_cd'  => $suppCd,
                    'months'   => $months,
                    'counts'   => $counts,
                );

                // 帳票フォーム一覧データ取得
                $ledgerSheetUnpopularList = $ledgerSheetUnpopular->getFormListData($searchArray);

                foreach($ledgerSheetUnpopularList as $data){
                    $list[] = array(
                        'prod_cd' => $data['prod_cd'],
                        'prod_kn' => $data['prod_kn'],
                        'sect_cd' => $data['sect_cd'],
                        'sect_nm' => $data['sect_nm'],
                        'supp_cd' => $data['supp_cd'],
                        'supp_nm' => $data['supp_nm'],
                        'dead_months' => $data['dead_months'],
                        'total_stock_amount' => intval($data['total_stock_amount']),
                        'organization_name' => $data['organization_name'],
                    );
                }
            }
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial"){
                require_once './View/LedgerSheetUnpopularPanel.html';
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
            
            $ledgerSheetUnpopular = new ledgerSheetUnpopular();

            $suppCd = parent::escStr( $_POST['supp_cd'] );
            $months = parent::escStr( $_POST['months'] );
            $counts = parent::escStr( $_POST['counts'] );

                $searchArray = array(
                    'supp_cd'  => $suppCd,
                    'months'   => $months,
                    'counts'   => $counts,
                );

                // 帳票フォーム一覧データ取得
                $ledgerSheetUnpopularList = $ledgerSheetUnpopular->getFormListData($searchArray);

                foreach($ledgerSheetUnpopularList as $data){
                    $list[] = array(
                        'prod_cd' => $data['prod_cd'],
                        'prod_kn' => $data['prod_kn'],
                        'sect_cd' => $data['sect_cd'],
                        'sect_nm' => $data['sect_nm'],
                        'supp_cd' => $data['supp_cd'],
                        'supp_nm' => $data['supp_nm'],
                        'dead_months' => $data['dead_months'],
                        'total_stock_amount' => intval($data['total_stock_amount']),
                        'organization_name' => $data['organization_name'],
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
            array_push($export_csv_title, "部門コード");
            array_push($export_csv_title, "部門");
            array_push($export_csv_title, "仕入先コード");
            array_push($export_csv_title, "仕入先");
            array_push($export_csv_title, "死に筋月数");
            array_push($export_csv_title, "現在在庫");
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

                    $str = $str.'","'.$rows['prod_cd'];
                    $str = $str.'","'.$rows['prod_kn'];
                    $str = $str.'","'.$rows['sect_cd'];
                    $str = $str.'","'.$rows['sect_nm'];
                    $str = $str.'","'.$rows['supp_cd'];
                    $str = $str.'","'.$rows['supp_nm'];
                    $str = $str.'","'.$rows['dead_months'];
                    $str = $str.'","'.round($rows['total_stock_amount'],0);
                    $str = $str.'","'.$rows['organization_name'];
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
            header("Content-Disposition: attachment; filename=死に筋商品リスト".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
