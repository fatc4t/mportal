<?php
    /**
     * @file      帳票 - 日次コントローラ
     * @author    media craft
     * @date      2018/03/30
     * @version   1.00
     * @note      帳票 - 日次の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetAbnormality.php';
    require '../attendance/Model/AggregateLaborCosts.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetAbnormalityController extends BaseController
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

            $ledgerSheetAbnormality = new ledgerSheetAbnormality();
            $start_date = parent::escStr( $_POST['start_date'] );
            $end_date = parent::escStr( $_POST['end_date'] );
            $searchArray = array(
                'start_date'  => str_replace('/','',$start_date),
                'end_date'  => str_replace('/','',$end_date),
            );

            $list = array();
            $ledgerSheetAbnormalityList = $ledgerSheetAbnormality->getFormListData($searchArray);

            foreach($ledgerSheetAbnormalityList as $data){
                $list[] = array(
                    'prod_cd' => $data['prod_cd'],
                    'prod_kn' => $data['prod_kn'],
                    'prod_capa_kn' => $data['prod_capa_kn'],
                    'saleprice' => number_format($data['saleprice']),
                    'day_costprice' => number_format($data['day_costprice']),
                    'abbreviated_name' => $data['abbreviated_name'],
                );
            }

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');

            $mpdf=new mPDF('ja+aCJK', 'A4-P');

            $header = '<!DOCTYPE html>'
.'<html>'
.'<head>'
.'    <meta charset="utf-8" />'
.'    <title>売上原価異常明細</title>'
.'    <STYLE type="text/css">'
.'        html,body{width:100%;height:100%;}'
.'        header{width:100%;height:75px;}'
.'        main{width:100%;height:860px;}'
.'        footer{width:100%;height:25px;position:absolute;bottom:0;}'
.'    </STYLE>'
.'</head>'
.'<body>'
.'    <div>'
.'    <header>'
.'        <div align="left" style="font-style:oblique; line-height:25px;">'
.'            <h1><span>売上原価異常明細</span></h1>'
.'        </div>'
.'        <table style="border-bottom:solid 3px #000000;line-height:15px;font-size:28px;">'
.'            <tr>'
.'                <td align="top" style="line-height:60px;font-size:26px;font-style:oblique;">売上日:　　'.$start_date.'</td>'
.'            </tr>'
.'            <tr>'
.'                <th align="left" style="width:300px;font-style:oblique;">商品CD</th>'
.'                <th align="left" style="width:300px;font-style:oblique;">商品名</th>'
.'                <th align="left" style="width:150px;font-style:oblique;">商品容量</th>'
.'                <th align="left" style="width:150px;font-style:oblique;">明細売価</th>'
.'                <th align="left" style="width:150px;font-style:oblique;">当日原価</th>'
.'            </tr>'
.'        </table>'
.'    </header>'
.'    <main>';

            $count = 1;
            $page = 1;
            $sum = ceil(count($list)/29);

            foreach($list as $data){
                if($count == 1){
                    $html .= $header;
                }

                $html .= '        <table style="border-bottom:solid 1px #000000;line-height:30px;font-size:24px;">'
.'            <tr>'
.'                <td align="left" style="width:300px;">'.$data['prod_cd'].'</td>'
.'                <td align="left" style="width:300px;">'.$data['prod_kn'].'</td>'
.'                <td align="left" style="width:150px;">'.$data['prod_capa_kn'].'</td>'
.'                <td align="left" style="width:150px;">'.$data['saleprice'].'</td>'
.'                <td align="left" style="width:150px;">'.$data['day_costprice'].'</td>'
.'            </tr>'
.'        </table>';

                if($count > 29 || end($list) == $data){
                    $count = 0;
                    $html .= '    </main>'
.'    <footer>'
.'        <table style="border-top: solid 3px #434359;width:100%; font-size: 20px;">'
.'            <tr>'
.'                <td style="width:900px; font-style:oblique;">2018年3月12日</td>'
.'                <td style="width:200px; font-style:oblique;">1/1ページ</td>'
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

            $ledgerSheetAbnormality = new ledgerSheetAbnormality();

            $start_date = parent::escStr( $_POST['start_date'] );
            $end_date = parent::escStr( $_POST['end_date'] );

            $abbreviatedNameList = $ledgerSheetAbnormality->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

            $list = array();
            // 初期表示以外だったら
            if($mode != "initial"){
                
                $organizationId   = parent::escStr( $_POST['organizationName'] );

                $searchArray = array(
                    'start_date'  => str_replace('/','',$start_date),
                    'end_date'  => str_replace('/','',$end_date),
                    'organization_id'   => $organizationId,
                );

                // 帳票フォーム一覧データ取得
                $ledgerSheetAbnormalityList = $ledgerSheetAbnormality->getFormListData($searchArray);

                foreach($ledgerSheetAbnormalityList as $data){
                    $list[] = array(
                        'trndate' => $data['trndate'],
                        'prod_cd' => $data['prod_cd'],
                        'prod_kn' => $data['prod_kn'],
                        'prod_capa_kn' => $data['prod_capa_kn'],
                        'saleprice' => number_format($data['saleprice']),
                        'day_costprice' => number_format($data['day_costprice']),
                        'abbreviated_name' => $data['abbreviated_name'],
                    );
                }
            }
            // 検索組織
            $searchArray = array(
                'organizationID' => parent::escStr( $_POST['organizationName'] ),
                
            );

            // 画面フォームに日付を返す
            if(!isset($start_date) OR $start_date == ""){
                //現在日付の１日を設定
                $start_date =date('Y/m/d', strtotime('first day of ' . $month));
            }          
            if(!isset($end_date) OR $end_date == ""){
                //現在日付の１日を設定
                $end_date =date('Y/m/d', strtotime('first day of ' . $month));
            }    
            
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();

            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial"){
                require_once './View/LedgerSheetAbnormalityPanel.html';
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
            
            $ledgerSheetAbnormality = new ledgerSheetAbnormality();

            $start_date = parent::escStr( $_POST['start_date'] );
            $end_date = parent::escStr( $_POST['end_date'] );
            $list = array();

                $organizationId   = parent::escStr( $_POST['organizationName'] );

                $searchArray = array(
                    'start_date'  => str_replace('/','',$start_date),
                    'end_date'  => str_replace('/','',$end_date),
                    'organization_id'   => $organizationId,
                );

                // 帳票フォーム一覧データ取得
                $ledgerSheetAbnormalityList = $ledgerSheetAbnormality->getFormListData($searchArray);

                foreach($ledgerSheetAbnormalityList as $data){
                    $list[] = array(
                        'trndate' => $data['trndate'],
                        'prod_cd' => $data['prod_cd'],
                        'prod_kn' => $data['prod_kn'],
                        'prod_capa_kn' => $data['prod_capa_kn'],
                        'saleprice' => number_format($data['saleprice']),
                        'day_costprice' => $data['day_costprice'],
                        'abbreviated_name' => $data['abbreviated_name'],
                    );
                }

            // 画面フォームに日付を返す
            if(!isset($start_date) OR $start_date == ""){
                //現在日付の１日を設定
                $start_date =date('Y/m/d', strtotime('first day of ' . $month));
            }

            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "No");
            array_push($export_csv_title, "日付");
            array_push($export_csv_title, "商品コード");
            array_push($export_csv_title, "商品");
            array_push($export_csv_title, "容量");
            array_push($export_csv_title, "当時売価");
            array_push($export_csv_title, "当日原価");
            array_push($export_csv_title, "店舗");

            if( touch($file_path) ){
                // オブジェクト生成
                $file = new SplFileObject( $file_path, "w" ); 
                // タイトル行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
                foreach( $export_csv_title as $key => $val ){
                    $export_header[] = mb_convert_encoding($val, 'SJIS-win', 'UTF-8');
                }
 
                // エンコードしたタイトル行を配列ごとCSVデータ化
                //$file->fputcsv($export_header);
                 $file = fopen($file_path, 'a');fwrite($file,implode(',',$export_header)."\r");
                
                // 取得結果を画面と同じように再構築して行として搭載
                // 内容行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
                $list_no = 0;

                foreach( $list as $rows ){
                    
                    // 垂直ヘッダ
                    // 垂直ヘッダ
                    $list_no += 1;

                    $str = '"'.$list_no;// 順位

                    $str = $str.'","'.$rows['trndate'];
                    $str = $str.'","'.$rows['prod_cd'];
                    $str = $str.'","'.$rows['prod_kn'];
                    $str = $str.'","'.$rows['prod_capa_kn'];
                    $str = $str.'","'.round($rows['saleprice'],0);
                    $str = $str.'","'.round($rows['day_costprice'],0);
                    $str = $str.'","'.$rows['abbreviated_name'];
                    $str = $str.'"';
                    
                    // 配列に変換
                    $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
                    //$export_arr = explode(",", $str);
                    
                    // 内容行を1行ごとにCSVデータへ

                    $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");

                }
                //$str="fin";$file = fopen($file_path, 'a');fwrite($file,$str."\r");
           }
 
            // ダウンロード用
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers 
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=売上原価異常明細".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
