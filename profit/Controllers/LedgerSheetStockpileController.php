<?php
    /**
     * @file      帳票 - 在庫調査表コントローラ
     * @author    media craft
     * @date      2018/03/33
     * @version   1.00
     * @note      帳票 - 在庫調査表の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetStockpile.php';
    require '../attendance/Model/AggregateLaborCosts.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetStockpileController extends BaseController
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

            $ledgerSheetStockpile = new ledgerSheetStockpile();

            $searchArray = array(

            );
            // 検索データリスト
            $list = array();

            // 帳票フォーム一覧データ取得
            $ledgerSheetStockpileList = $ledgerSheetStockpile->getFormListData($searchArray);

            // ヘッダ情報、Ｍコード情報取り出し
            // foreach($ledgerSheetStockpileList as $data){
            //
            // }

            if($ledgerSheetStockpileList !== $list){
                $list = $ledgerSheetStockpileList;
            }

            $html = "";
            //include("./View/LedgerSheetStockpilePDFPanel.html");

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-P');

            $header = '<!DOCTYPE html>'
                    .'<html>'
                    .'<head>'
                    .'    <title>在庫調査表</title>'
                    .'    <STYLE type="text/css">'
                    .'        html,body{width:100%;height:100%;}'
                    .'        header{width:100%;height:45px;}'
                    .'        main{width:100%;height:910px;}'
                    .'        footer{width:100%;height:35px;position:absolute;bottom:0;}'
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
                    .'<div>'
                    .'    <header>'
                    .'        <div align="left" style="font-style:oblique; line-height: 5px;">'
                    .'            <h1><span>在庫調査表</span></h1>'
                    .'        </div>'
                    .'        <table style="border-bottom:solid 3px #000000;line-height:5px;font-size:28px;">'
                    .'            <tr>'
                    .'                <th align="left" style="width:260px;font-style:oblique;">　商品コード</th>'
                    .'                <th align="left" style="width:260px;font-style:oblique;">商品名</th>'
                    .'                <th align="left" style="width:120px;font-style:oblique;">容量</th>'
                    .'                <th align="left" style="width:100px;font-style:oblique;">売価</th>'
                    .'                <th align="left" style="width:120px;font-style:oblique;">分類CD</th>'
                    .'                <th align="left" style="width:150px;font-style:oblique;">分類名</th>'
                    .'                <th align="left" style="width:120px;font-style:oblique;">現在庫</th>'
                    .'                <th align="left" style="width:120px;font-style:oblique;">死に筋</th>'
                    .'                <th align="left" style="width:140px;font-style:oblique;">実在庫数</th>'
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

                $html .= '<table style="border-bottom:solid 1px #000000;line-height:10px;font-size:24px;">'
                        .'            <tr >'
                        .'                <td align="left" style="width:260px;">'.$data['prod_cd'].'</td>'
                        .'                <td align="left" style="width:280px;">'.$data['prod_kn'].'</td>'
                        .'                <td align="left" style="width:120px;">'.$data['prod_capa_kn'].'</td>'
                        .'                <td align="left" style="width:100px;">￥'.number_format($data['saleprice']).'</td>'
                        .'                <td align="left" style="width:120px;">'.$data['jicfs_class_cd'].'</td>'
                        .'                <td align="left" style="width:150px;">'.$data['jicfs_class_nm'].'</td>'
                        .'                <td align="center" style="width:120px;">'.$data['total_stock_amout'].'</td>'
                        .'                <td align="center" style="width:120px;">'.$data['dead_months'].'</td>'
                        .'                <td align="left" style="width:140px;"></td>'
                        .'            </tr>'
                        .'            <tr>'
                        .'                <td colspan="9">'
                        .'                    <barcode code="'.$data['jan_cd'].'" type="EAN13" size="2" height="0.3">'
                        .'                </td>'
                        .'            </tr>'
                        .'        </table>';

                if($count > 13 || end($list) == $data){
                    $count = 0;
                    $html .= '    </main>'
                             .'    <footer>'
                             .'        <table style="border-top: solid 3px #434359;width:100%; font-size: 20px;">'
                             .'            <tr>'
                             .'                <td style="width:800px; font-style:oblique;">'.date(Y年m月d日).'</td>'
                             .'                <td style="width:300px; font-style:oblique;">'.$page.'/'.$sum.'ページ</td>'
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

            $ledgerSheetStockpile = new ledgerSheetStockpile();

            $abbreviatedNameList = $ledgerSheetStockpile->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

            // 検索データリスト
            $list = array();

            // 初期表示以外だったら
            if($mode != "initial"){

                $organizationId   = parent::escStr( $_POST['organizationName'] );

                $searchArray = array(
                    'organization_id'   => $organizationId,
                );

                // 帳票フォーム一覧データ取得
                $ledgerSheetStockpileList = $ledgerSheetStockpile->getFormListData($searchArray);

                $list = $ledgerSheetStockpileList;

            }
            
            // 検索組織
            $searchArray = array(
                'organizationID' => parent::escStr( $_POST['organizationName'] ),
            );
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial"){
                require_once './View/LedgerSheetStockpilePanel.html';
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
            
            $ledgerSheetStockpile = new ledgerSheetStockpile();

            // 検索データリスト
            $list = array();

            $organizationId   = parent::escStr( $_POST['organizationName'] );

            $searchArray = array(
                'organization_id'   => $organizationId,
            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetStockpileList = $ledgerSheetStockpile->getFormListData($searchArray);


            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "No");
            array_push($export_csv_title, "商品コード");
            array_push($export_csv_title, "商品名");
            array_push($export_csv_title, "容量");
            array_push($export_csv_title, "売価");
            array_push($export_csv_title, "分類コード");
            array_push($export_csv_title, "分類名");
            array_push($export_csv_title, "現在庫");
            array_push($export_csv_title, "死に筋月");
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
                foreach( $ledgerSheetStockpileList as $rows ){
                    
                    // 垂直ヘッダ
                    $list_no += 1;

                    $str = '"'.$list_no;// 順位
                    
                    $str = $str.'","'.$rows['prod_cd'];
                    $str = $str.'","'.$rows['prod_kn'];
                    $str = $str.'","'.$rows['prod_capa_kn'];
                    $str = $str.'","'.round($rows['saleprice'],0);
                    $str = $str.'","'.$rows['jicfs_class_cd'];
                    $str = $str.'","'.$rows['jicfs_class_nm'];
                    $str = $str.'","'.round($rows['total_stock_amout'],0);
                    $str = $str.'","'.$rows['dead_months'];
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
            header("Content-Disposition: attachment; filename=在庫調査表".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
