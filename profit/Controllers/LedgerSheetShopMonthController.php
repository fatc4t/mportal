<?php
    /**
     * @file      帳票 - 店舗別売上月報コントローラ
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 店舗別売上月報の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetShopMonth.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetShopMonthController extends BaseController
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

            $ledgerSheetShopMonth = new ledgerSheetShopMonth();

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-P');

            //テスト用
            //$html = file_get_contents("./View/LedgerSheetShopMonthPDFPanel.html");

            $saledYear = parent::escStr( $_POST['saled_year'] );
            $startCode  = parent::escStr( $_POST['start_code'] );
            $endCode  = parent::escStr( $_POST['end_code'] );
            $searchArray = array(
                'saled_year' => $saledYear,
                'start_code' => $startCode,
                'end_code'   => $endCode,
            );

            $list = $ledgerSheetShopMonth->getFormListData($searchArray);

            $html   = '';
            $header = '';

            $header  = '<!DOCTYPE html>';
            $header .= '<html>';
            $header .= '<head>';
            $header .= '    <meta charset="utf-8" />';
            $header .= '    <title>顧客別売上明細</title>';
            $header .= '    <STYLE type="text/css">';
            $header .= '        html,body{width:100%;height:100%;}';
            $header .= '        header{width:100%;height:75px;}';
            $header .= '        main{width:100%;height:850px;}';
            $header .= '        footer{width:100%;height:25px;position:absolute;bottom:0;}';
            $header .= '    </STYLE>';
            $header .= '</head>';
            $header .= '<body>';
            $header .= '<!--<div style="page-break-before: always;">-->';
            $header .= '    <div>';
            $header .= '    <header>';
            $header .= '        <div align="left" style="font-style:oblique; line-height:25px;">';
            $header .= '            <h1><span>顧客別売上明細</span></h1>';
            $header .= '        </div>';
            $header .= '        <table style="border-bottom:solid 3px #000000;line-height:50px;font-size:28px;">';
            $header .= '            <tr>';
            $header .= '                <th align="left" style="width:100px;font-style:oblique;">顧客CD</th>';
            $header .= '                <th align="left" style="width:250px;font-style:oblique;">　顧客名_漢字</th>';
            $header .= '                <th align="right" style="width:300px;font-style:oblique;">売上日：'.$saledYear.'年</th>';
            $header .= '                <th align="left" style="width:300px;font-style:oblique;"></th>';
            $header .= '                <th align="left" style="width:150px;font-style:oblique;"></th>';
            $header .= '                <th align="left" style="width:150px;font-style:oblique;"></th>';
            $header .= '            </tr>';
            $header .= '            <tr>';
            $header .= '                <th align="left" style="width:100px;font-style:oblique;"></th>';
            $header .= '                <th align="left" style="width:300px;font-style:oblique;">履歴商品CD</th>';
            $header .= '                <th align="left" style="width:300px;font-style:oblique;">商品名_カナ</th>';
            $header .= '                <th align="left" style="width:300px;font-style:oblique;">商品容量_カナ</th>';
            $header .= '                <th align="right" style="width:150px;font-style:oblique;">履歴売価</th>';
            $header .= '                <th align="right" style="width:150px;font-style:oblique;">履歴数量</th>';
            $header .= '            </tr>';
            $header .= '        </table>';
            $header .= '    </header>';
            $header .= '    <main>';
            $header .= '        <table style="font-size:22px;">';
            $count = 1;
            $page = 1;
            $maxRow = 42;


            $sum = ceil(count($list)/$maxRow);

            foreach($list as $data)
            {
                if($count == 1)
                {
                    $html .= $header;
                }


                if($data['gyokbn']==999)
                {
                  $html .= '<tr>';
                  $html .= '    <td align="right" colspan="4" style="font-style:oblique;">計　</td>';
                  $html .= '    <td align="right" colspan="1">￥'.$data['saleprice'].'</td>';
                  $html .= '    <td align="right" colspan="1">'.$data['amount'].'</td>';
                  $html .= '</tr>';
                  //下線がページの最終行の場合は表示しない
                  if(count < $maxRow -1)
                  {
                    $html .= '<tr>';
                    $html .= '    <td colspan="6"  style="border-bottom: solid 1px #000000;"></td>';
                    $html .= '</tr>';
                  }
                } else {
                  $html .= '<tr>';
                  $html .= '    <td align="left" style="width:100px;">'.$data['cust_cd'].'</td>';
                  $html .= '    <td align="center" style="width:250px;">'.$data['cust_nm'].'</td>';
                  $html .= '    <td align="left" style="width:300px;">'.$data['prod_kn'].'</td>';
                  $html .= '    <td align="left" style="width:300px;">'.$data['prod_capa_kn'].'</td>';
                  $html .= '    <td align="right" style="width:150px;">';
                  if($data['gyokbn']=='002') {
                    $html .='￥';
                  }
                  $html .= $data['saleprice'].'</td>';
                  $html .= '    <td align="right" style="width:150px;">'.$data['amount'].'</td>';
                  $html .= '</tr>';
                }


                if($count > $maxRow || end($list) == $data)
                {
                    $count = 0;
                    $html .= '        </table>';
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

            $ledgerSheetShopMonth = new ledgerSheetShopMonth();

            //$abbreviatedNameList = $ledgerSheetShopMonth->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト
            // modal
            $modal = new Modal();
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            $supp_detail    = [];
            $supp_detail    = $modal->getsupp_detail();
            $param          = [];
            if($_POST){
                $param = $_POST;
            }
            $param["supp_cd"]   = "";
            $param["org_id"]    = "";             

            // 日付リストを取得
            $startDateM = parent::escStr( $_POST['start_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDateM) OR $startDateM == ""){
                //現在日付の１日を設定
                $startDateM = date('Y/m', strtotime('first day of ' . $month));
            }
            
            // 月初を設定
            $startDate        = parent::escStr( $_POST['start_dateM'] );
            $startDate        = $startDate.'/01';
            // 月末を設定
            $endDate = date('Y/m/d', strtotime('last day of ' . $startDate));
            //modal
            $organizationId = 'false';
            if($_POST['org_r'] === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
                }
            }
            
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
            );
            
            if($mode != "initial"){
                
                // 帳票フォーム一覧データ取得
                $ledgerSheetDetailList = $ledgerSheetShopMonth->getFormListData($searchArray);

                // 合計計算用変数

                $sumPureTotalI = 0;
                $sumTotalAmount = 0;
                $sumTotalCnt = 0;
                $sumTotalHiki = 0;
                $daySum = 0;
                $sumTax10 = 0;
                $sumTax08 = 0;
                if(count($ledgerSheetDetailList) > 0){
                    // 合計行を取得
                    foreach($ledgerSheetDetailList as $data){
                        $sumPureTotalI  = $sumPureTotalI + $data["pure_total_i"];
                        $sumTotalAmount = $sumTotalAmount + $data["total_amount"];
                        $sumTotalCnt    = $sumTotalCnt + $data["total_cnt"];
                        $sumTotalHiki   = $sumTotalHiki + $data["hiki_total"];
                        $daySum         = $daySum + $data['days'];
                        $sumTax08         = $sumTax08 + $data['tax_total_08'];
                        $sumTax10         = $sumTax10 + $data['tax_total_10'];
                    }

                    $sumLine = array(
                        'abbreviated_name'   => '総合計',            // 合計名称
                        'organization_id'    => '',                // 空白
                        'days'               => '',
                        'tax_total_08'          => $sumTax08,     // 空白
                        'tax_total_10'          => $sumTax10,     // 空白
                        'pure_total_i'       => $sumPureTotalI,    // 現金売価金額
                        'composition_ratio'  => '',                // 構成比
                        'total_amount'       => $sumTotalAmount,   // 数量
                        'avg_amount'         => '',                // 平均数量
                        'total_cnt'          => $sumTotalCnt,      // 客数
                        'avarege_price'      => round($sumPureTotalI / $sumTotalAmount),  // 一品単価
                        'per_customer_price' => round($sumPureTotalI / $sumTotalCnt),     // 客単価
                        'hiki_total'         => $sumTotalHiki,
                        );

                    // 合計行を追加
                    array_push($ledgerSheetDetailList, $sumLine);
                }
            }

            // 画面フォームに日付を返す
            if(!isset($saledYear) OR $saledYear == "")
            {
                //現在日付の１日を設定
                $saledYear =date('Y');
            }

            // 検索組織
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'org_id'            => str_replace("'","",$_POST['org_id']),
            );

            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();

            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetShopMonthPanel.html';
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
            
            $ledgerSheetShopMonth = new ledgerSheetShopMonth();

            // 日付リストを取得
            $startDateM = parent::escStr( $_POST['start_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDateM) OR $startDateM == ""){
                //現在日付の１日を設定
                $startDateM = date('Y/m', strtotime('first day of ' . $month));
            }
            
            // 月初を設定
            $startDate        = parent::escStr( $_POST['start_dateM'] );
            $startDate        = $startDate.'/01';
            // 月末を設定
            $endDate = date('Y/m/d', strtotime('last day of ' . $startDate));
            //modal
            $organizationId = 'false';
            if($_POST['org_r'] === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
                }
            }
            
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
            );
            // 帳票フォーム一覧データ取得
            $ledgerSheetDetailList = $ledgerSheetShopMonth->getFormListData($searchArray);
            
            // 画面フォームに日付を返す
            if(!isset($saledYear) OR $saledYear == "")
            {
                //現在日付の１日を設定
                $saledYear =date('Y');
            }

            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "店舗");
            array_push($export_csv_title, "営業日数");
            array_push($export_csv_title, "税込売上額");
            array_push($export_csv_title, "税抜売上額");
            array_push($export_csv_title, "消費税8%");
            array_push($export_csv_title, "消費税10%");
            array_push($export_csv_title, "構成比");
            array_push($export_csv_title, "数量");
            array_push($export_csv_title, "平均数量");
            array_push($export_csv_title, "来店数");
            array_push($export_csv_title, "平均単価");
            array_push($export_csv_title, "客平均単価");
            array_push($export_csv_title, "引額");

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
                foreach( $ledgerSheetDetailList as $rows ){
                    
                    // 垂直ヘッダ
                    $str = '"'.$rows['abbreviated_name'];
                    
                    $str = $str.'","'.$rows['days'];
                    $str = $str.'","'.round($rows['pure_total_i']+$rows['tax_total_08']+$rows['tax_total_10'],0);
                    $str = $str.'","'.round($rows['pure_total_i'],0);
                    $str = $str.'","'.round($rows['tax_total_08'],0);
                    $str = $str.'","'.round($rows['tax_total_10'],0);
                    $str = $str.'","'.str_replace('%','',$rows['composition_ratio']);
                    $str = $str.'","'.round($rows['total_amount'],0);
                    $str = $str.'","'.round($rows['avg_amount'],0);
                    $str = $str.'","'.$rows['total_cnt'];
                    if($rows['total_amount'] == 0){
                        $str = $str.'","'."0";
                    }else{        
                        $avgAmntShouhin = $rows['pure_total_i'] / $rows['total_amount'];
                            if (is_nan($avgAmntShouhin)){
                                $str = $str.'","'."0";
                            }else{
                                $str = $str.'","'.round($avgAmntShouhin,0);
                            }
                    }
                    if($rows['total_cnt'] == 0){
                        $str = $str.'","'."0";
                    }else{
                        $avgAmntKokyaku =  $rows['pure_total_i'] / $rows['total_cnt']; 
                            if (is_nan($avgAmntKokyaku)){
                                $str = $str.'","'."0";
                            }else{
                                $str = $str.'","'.round($avgAmntKokyaku,0);
                            }
                    }
                    $str = $str.'","'.round($rows['hiki_total'],0);
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
            header("Content-Disposition: attachment; filename=店舗別売上月報".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
