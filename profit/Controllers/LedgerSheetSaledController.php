<?php
    /**
     * @file      帳票 - 日次コントローラ
     * @author    media craft
     * @date      2018/03/22
     * @version   1.00
     * @note      帳票 - 日次の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetSaled.php';
    require '../attendance/Model/AggregateLaborCosts.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetSaledController extends BaseController
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

            $ledgerSheetSaled = new ledgerSheetSaled();

            $saledDate = parent::escStr( $_POST['saled_date'] );
            $searchArray = array(
                'saled_date'  => str_replace('/','',$saledDate),
            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetSaledList = $ledgerSheetSaled->getFormListData($searchArray);

            $tmp = "";
            foreach($ledgerSheetSaledList as $data){
                if($data['shop_supp_cd'] != $tmp){
                    $tmp = $data['shop_supp_cd'];
                    $list[] = array(
                        'prod_cd' => $data['shop_supp_cd'].' '.$data['supp_nm'],
                        'prod_nm' => '',
                        'prod_sale_amount' => '',
                        'flg' => '1',
                    );
                }
                $list[] = array(
                    'prod_cd' => $data['prod_cd'],
                    'prod_nm' => $data['prod_nm'],
                    'prod_sale_amount' => intval($data['prod_sale_amount']),
                    'flg' => '0',
                );
            }

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            //$html = file_get_contents("./View/LedgerSheetSaledPDFPanel.html");
            $mpdf = new mPDF('ja+aCJK', 'A4-P');

            $header = '<!DOCTYPE html>'
                     .'<html>'
                     .'<head>'
                     .'    <meta charset="utf-8" />'
                     .'    <title>当日販売商品一覧</title>'
                     .'    <STYLE type="text/css">'
                     .'        html,body{width:100%;height:100%;}'
                     .'        header{width:100%;height:75px;}'
                     .'        main{width:100%;height:880px;}'
                     .'        footer{width:100%;height:25px;position:absolute;bottom:0;}'
                     .'    </STYLE>'
                     .'</head>'
                     .'<body>'
                     .'    <div>'
                     .'    <header>'
                     .'        <div align="left" style="font-style:oblique; line-height:25px;">'
                     .'            <h1><span>当日販売商品一覧</span></h1>'
                     .'        </div>'
                     .'        <table style="border-bottom:solid 3px #000000;line-height:25px;font-size:28px;">'
                     .'            <tr>'
                     .'                <td style="font-style:oblique;line-height:40px;" colspan="4">　　売上日：　'.$saledDate.'</td>'
                     .'            </tr>'
                     .'            <tr>'
                     .'                <th align="left" style="width:380px;font-style:oblique;">商品CD</th>'
                     .'                <th align="left" style="width:400px;font-style:oblique;">商品名</th>'
                     .'                <th align="right" style="width:200px;font-style:oblique;">販売数量</th>'
                     .'                <th align="right" style="width:200px;font-style:oblique;">在庫数量</th>'
                     .'            </tr>'
                     .'        </table>'
                     .'    </header>'
                     .'    <main>'
                     .'        <table style="line-height:30px;font-size:22px;">';

            $count = 1;
            $page = 1;
            $sum = ceil(count($list)/29);
            foreach($list as $data){
                if($count == 1){
                    $html .= $header;
                }

                if($data['flg'] == '1'){
                    $html .= '            <tr>'
                            .'                <td align="left" style="width:380px;">'.$data['prod_cd'].'</td>'
                            .'                <td align="left" style="width:400px;"></td>'
                            .'                <td align="left" style="width:200px;"></td>'
                            .'                <td align="left" style="width:200px;"></td>'
                            .'            </tr>';
                }else{
                    $html .= '            <tr>'
                            .'                <td align="center" style="width:380px;">'.$data['prod_cd'].'</td>'
                            .'                <td align="left" style="width:400px;">'.$data['prod_nm'].'</td>'
                            .'                <td align="right" style="width:200px;">'.$data['prod_sale_amount'].'</td>'
                            .'            </tr>';
                }

                if($count > 29 || end($list) == $data){
                    $count = 0;
                    $html .= '</table>'
                            .'</main>'
                            .'    <footer>'
                            .'        <table style="border-top: solid 3px #434359;width:100%;font-size:20px;">'
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

            $ledgerSheetSaled = new ledgerSheetSaled();

            $abbreviatedNameList = $ledgerSheetSaled->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

            $saledDate = parent::escStr( $_POST['saled_date'] );
            $organizationId   = parent::escStr( $_POST['organizationName'] );

            // 初期表示以外だったら
            if($mode != "initial"){
                $searchArray = array(
                    'saled_date'  => str_replace('/','',$saledDate),
                    'organization_id'   => $organizationId,
                );

                // 帳票フォーム一覧データ取得
                $ledgerSheetSaledList = $ledgerSheetSaled->getFormListData($searchArray);

                    // 合計計算用変数
    
                    $sumAmount = 0;
                        // 合計行を取得
                            $sumProfit = 0;
                            $sumPure = 0;
                            $sumAmount = 0;

                        foreach($ledgerSheetSaledList as $datos){
                            $sumProfit  += $datos["prod_profit"];
                            $sumPure    += $datos["prod_pure_total"];
                            $sumAmount  += $datos['prod_sale_amount'];

                        $sumLine = array(
                            'No'          		=> '',       
                            'prod_cd'   	    => '',
                            'prod_nm'       	=> '',    
                            'prod_capa'       	=> '合計',   
                            'prod_sale_amount'  => $sumAmount,    
                            'prod_pure_total'   => $sumPure,
                            'prod_profit'       => $sumProfit,
                            'arariritsu'     	=> number_format(($sumProfit / $sumPure) *100,2),     
                                
                            );
                }
                // 合計行を追加
                array_push($ledgerSheetSaledList, $sumLine);

                // ヘッダ情報、Ｍコード情報取り出し
                $tmp = "";
                foreach($ledgerSheetSaledList as $data){
                    if($data['shop_supp_cd'] != $tmp){
                        $tmp = $data['shop_supp_cd'];
                        $list[] = array(
                            'prod_cd' => $data['shop_supp_cd'].' '.$data['supp_nm'],
                            'prod_nm' => '',
                            'prod_sale_amount' => '',
                            'prod_capa' => '',
                            'prod_sale_amount' => '',
                            'prod_profit' => '',
                            'prod_pure_total' => '',
                            'arariritsu' => '',
                            'flg' => '1',
                        );
                    }
                    $list[] = array(
                        'prod_cd' => $data['prod_cd'],
                        'prod_nm' => $data['prod_nm'],
                        'prod_sale_amount' => intval($data['prod_sale_amount']),
                        'prod_capa' => $data['prod_capa'],
                        'prod_sale_amount' => $data['prod_sale_amount'],
                        'prod_profit' => $data['prod_profit'],
                        'prod_pure_total' => $data['prod_pure_total'],
                        'arariritsu' => $data['arariritsu'],
                        'flg' => '0',
                    );
                }
            }

            // 画面フォームに日付を返す
            if(!isset($saledDate) OR $saledDate == ""){
                //現在日付の１日を設定
                $saledDate =date('Y/m/d', strtotime('first day of ' . $month));
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
                require_once './View/LedgerSheetSaledPanel.html';
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
            
            $ledgerSheetSaled = new ledgerSheetSaled();

            $saledDate = parent::escStr( $_POST['saled_date'] );
            $organizationId   = parent::escStr( $_POST['organizationName'] );

                $searchArray = array(
                    'saled_date'  => str_replace('/','',$saledDate),
                    'organization_id'   => $organizationId,
                );

                // 帳票フォーム一覧データ取得
                $ledgerSheetSaledList = $ledgerSheetSaled->getFormListData($searchArray);

                // ヘッダ情報、Ｍコード情報取り出し
                $tmp = "";
                foreach($ledgerSheetSaledList as $data){
                    if($data['shop_supp_cd'] != $tmp){
                        $tmp = $data['shop_supp_cd'];
                        $list[] = array(
                            'prod_cd' => $data['shop_supp_cd'].' '.$data['supp_nm'],
                            'prod_nm' => '',
                            'prod_sale_amount' => '',
                            'prod_capa' => '',
                            'prod_sale_amount' => '',
                            'prod_profit' => '',
                            'prod_pure_total' => '',
                            'arariritsu' => '',
                            'flg' => '1',
                        );
                    }
                    $list[] = array(
                        'prod_cd' => $data['prod_cd'],
                        'prod_nm' => $data['prod_nm'],
                        'prod_sale_amount' => intval($data['prod_sale_amount']),
                        'prod_capa' => $data['prod_capa'],
                        'prod_sale_amount' => $data['prod_sale_amount'],
                        'prod_profit' => $data['prod_profit'],
                        'prod_pure_total' => $data['prod_pure_total'],
                        'arariritsu' => $data['arariritsu'],
                        'flg' => '0',
                    );
                }


            // 画面フォームに日付を返す
            if(!isset($saledDate) OR $saledDate == ""){
                //現在日付の１日を設定
                $saledDate =date('Y/m/d', strtotime('first day of ' . $month));
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
            array_push($export_csv_title, "販売数量");
            array_push($export_csv_title, "売上金額");
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
                foreach( $list as $rows ){
                    
                    // 垂直ヘッダ
                    $list_no += 1;

                    $str = '"'.$list_no;// 順位

                    $str = $str.'","'.$rows['prod_cd'];
                    $str = $str.'","'.$rows['prod_nm'];
                    $str = $str.'","'.$rows['prod_capa'];
                    $str = $str.'","'.round($rows['prod_sale_amount'],0);
                    $str = $str.'","'.round($rows['prod_pure_total'],0);
                    $str = $str.'","'.round($rows['prod_profit'],0);
                    $str = $str.'","'.round($rows['arariritsu'],0);
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
            header("Content-Disposition: attachment; filename=当日販売商品一覧".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
