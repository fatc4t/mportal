<?php
    /**
     * @file      帳票 - 仕入先・店舗別仕入元帳コントローラ
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 仕入先・店舗別仕入元帳の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetPurchaseLedger.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetPurchaseLedgerController extends BaseController
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

            $ledgerSheetPurchaseLedger = new ledgerSheetPurchaseLedger();

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-P');

            //テスト用
            //$html = file_get_contents("./View/LedgerSheetPurchaseLedgerPDFPanel.html");

            $saledYear = parent::escStr( $_POST['saled_year'] );
            $startCode  = parent::escStr( $_POST['start_code'] );
            $endCode  = parent::escStr( $_POST['end_code'] );
            $searchArray = array(
                'saled_year' => $saledYear,
                'start_code' => $startCode,
                'end_code'   => $endCode,
            );

            $list = $ledgerSheetPurchaseDay->getFormListData($searchArray);

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

            $ledgerSheetPurchaseLedger = new ledgerSheetPurchaseLedger();

            //$abbreviatedNameList = $ledgerSheetPurchaseLedger->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト
            // modal
            $modal = new Modal();
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            $supp_detail    = [];
            $supp_detail    = $modal->getsupp_detail();
            $prod_detail    = [];
            $prod_detail    = $modal->getprod_detail();            
            $param          = [];
            if($_POST){
                $param = $_POST;
            }
            $param["supp_cd"]   = "";
            $param["org_id"]    = "";
            $param["prod_cd"]    = "";             

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

            //modal
            $organizationId = 'false';
            if($_POST['org_r'] === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
                }
            }            
            $supp_cd = 'false';
            if($_POST['supp_r'] === ""){
                if($_POST['supp_select'] && $_POST['supp_select'] === 'empty'){
                    $supp_cd = $_POST['supp_cd'];
                }else{
                    $supp_cd = "'".$_POST['supp_select']."'";
                }
            }
            $prod_cd = 'false';
            if($_POST['prod_r'] === ""){
                if($_POST['prod_select'] && $_POST['prod_select'] === 'empty'){
                    $prod_cd = $_POST['prod_cd'];
                }else{
                    $prod_cd = "'".$_POST['prod_select']."'";
                }
            }            
            
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'supp_cd'           => $supp_cd,
                'prod_cd'           => $prod_cd,
            );
            
            if($mode != "initial") {
                // 帳票フォーム一覧データ取得
                $ledgerSheetDetailListOri = $ledgerSheetPurchaseLedger->getFormListData($searchArray);

                // 合計計算用変数

                $sumAmount = 0;
                $sumCostprice = 0;
                $sumCosttotal = 0;
                $sumTax = 0;
                $cnt = 0;
                $denno = "";

                $ledgerSheetDetailList = array();

                if(count($ledgerSheetDetailListOri) > 0){
                    // 合計行を取得
                    foreach($ledgerSheetDetailListOri as $data){
                        if($cnt == 0){
                            $denno = $data["denno"];
                        }

                        // 伝票番号をチェック
                        if($cnt == 0 && $denno != $data["denno"]){
                            $sumLine = array(
                                'tax'             => $data["tax"], // 消費税
                                );

                            // 伝票消費税行をを追加
                            array_push($ledgerSheetDetailList, $data);
                          array_push($ledgerSheetDetailList, $sumLine);
                           $denno = $data["denno"];

                      } else {
                            array_push($ledgerSheetDetailList, $data);

                        }

                        // 総合計
                        $sumAmount    = $sumAmount + $data["amount"];
                        $sumCostprice = $sumCostprice + $data["costprice"];
                        $sumCosttotal = $sumCosttotal + $data["costtotal"];
                        //$sumTax = $sumTax + $data["tax"] 
                        //$sumTax += $data['tax'];
                    }
                    $sumLine = array(
                        'stock_date'      => '',        // 合計名称
                        'denno'           => '合計',            // 伝票番号
                        'prod_nm'         => '',            // 商品名
                        //'pay_type_kbn'  => '',          // 仕入区分
                        'amount'          => $sumAmount,    // 仕入数量
                        'costprice'       => $sumCostprice, // 仕入単価
                        'costtotal'       => $sumCosttotal, // 仕入金額
                        //'tax'             => $sumTax,       // 消費税
                        'tax'             => '',       // 消費税
                        );

                    // 合計行を追加
                    array_push($ledgerSheetDetailList, $sumLine);
                }
            }
            
            // 検索組織
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'org_id'            => str_replace("'","",$_POST['org_id']),
                'supp_cd'           => str_replace("'","",$_POST['supp_cd']),
                'prod_cd'           => str_replace("'","",$_POST['prod_cd']),
                //'sort'              => $sort,
            ); 
            // 画面フォームに日付を返す
            if(!isset($saledYear) OR $saledYear == "")
            {
                //現在日付の１日を設定
                $saledYear =date('Y');
            }
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetPurchaseLedgerPanel.html';
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
 
// pme 2019/12/02            
//            $ledgerSheetPurchaseLedger = new ledgerSheetPurchaseLedger();
//
//            // 日付リストを取得
//            $startDate = parent::escStr( $_POST['start_date'] );
//            $endDate = parent::escStr( $_POST['end_date'] );
//
//            // 画面フォームに日付を返す
//            if(!isset($startDate) OR $startDate == ""){
//                //現在日付の１日を設定
//                $startDate =date('Y/m/d', strtotime('first day of ' . $month));
//            }
//            
//            if(!isset($endDate) OR $endDate == ""){
//                //現在日付の１日を設定
//                $endDate =date('Y/m/d', strtotime('last day of ' . $month));
//            }
//            //modal
//            $organizationId = 'false';
//            if($_POST['org_r'] === ""){
//                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
//                    $organizationId = $_POST['org_id'];
//                }else{
//                    $organizationId = "'".$_POST['org_select']."'";
//                }
//            }            
//            $supp_cd = 'false';
//            if($_POST['supp_r'] === ""){
//                if($_POST['supp_select'] && $_POST['supp_select'] === 'empty'){
//                    $supp_cd = $_POST['supp_cd'];
//                }else{
//                    $supp_cd = "'".$_POST['supp_select']."'";
//                }
//            }
//            $prod_cd = 'false';
//            if($_POST['prod_r'] === ""){
//                if($_POST['prod_select'] && $_POST['prod_select'] === 'empty'){
//                    $prod_cd = $_POST['prod_cd'];
//                }else{
//                    $prod_cd = "'".$_POST['prod_select']."'";
//                }
//            }            
//            
//            $searchArray = array(
//                'start_date'        => $startDate,
//                'end_date'          => $endDate,
//                'organization_id'   => $organizationId,
//                'supp_cd'           => $supp_cd,
//                'prod_cd'           => $prod_cd,
//            );
//            $prev_denno = "";
//            // 帳票フォーム一覧データ取得
//            $ledgerSheetDetailList = $ledgerSheetPurchaseLedger->getFormListData($searchArray);
//           
//            // 画面フォームに日付を返す
//            if(!isset($saledYear) OR $saledYear == "")
//            {
//                //現在日付の１日を設定
//                $saledYear =date('Y');
//            }
//
//            // ファイル名
//            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDate )).".csv", 'SJIS-win', 'UTF-8');
//
//            // CSVに出力するヘッダ行
//            $export_csv_title = array();
//
//            // 先頭固定ヘッダ追加
//            
//            array_push($export_csv_title, "仕入日付");
//            array_push($export_csv_title, "店舗");
//            array_push($export_csv_title, "仕入先コード");
//            array_push($export_csv_title, "仕入先");
//            array_push($export_csv_title, "伝票番号");
//            array_push($export_csv_title, "商品コード");
//            array_push($export_csv_title, "商品");
//            array_push($export_csv_title, "仕入数量");
//            array_push($export_csv_title, "仕入単価");
//            array_push($export_csv_title, "仕入金額");
//            array_push($export_csv_title, "伝票金額");
//            array_push($export_csv_title, "伝票消費税額");
//            array_push($export_csv_title, "合計金額");
//            if( touch($file_path) ){
//                // オブジェクト生成
//                $file = new SplFileObject( $file_path, "w" ); 
// 
//                // タイトル行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
//                foreach( $export_csv_title as $key => $val ){
//                    $export_header[] = mb_convert_encoding($val, 'SJIS-win', 'UTF-8');
//                }
//
//                // エンコードしたタイトル行を配列ごとCSVデータ化
//                $file = fopen($file_path, 'a');fwrite($file,implode(',',$export_header)."\r");
//                
//                // 取得結果を画面と同じように再構築して行として搭載
//                // 内容行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
//                $list_no = 0;
//                foreach( $ledgerSheetDetailList as $rows ){
//                    
//                    // 垂直ヘッダ
//                    $str = "";
//                    $str = $str.'"'.$rows['stock_date'];
//                    $str = $str.'","'.$rows['org_nm'];
//                    $str = $str.'","'.$rows['supp_cd'];
//                    $str = $str.'","'.$rows['supp_nm'];
//                    $str = $str.'","'.$rows['denno'];
//                    $str = $str.'","'.$rows['prod_cd'];
//                    $str = $str.'","'.$rows['prod_nm'];
//                    $str = $str.'","'.round($rows['amount'],0);
//                    $str = $str.'","'.round($rows['costprice'],0);
//                    $str = $str.'","'.round($rows['costtotal'],0);
//                    if($prev_denno !== $rows['denno']){
//                        $str = $str.'","'.round($rows['subtot'],0);
//                        $str = $str.'","'.round($rows['tax'],0);
//                        $str = $str.'","'.round($rows['cost_total'],0);
//                    }else{
//                        $str = $str.'","","","';
//                    }   
//                    $str = $str.'"';
//                    
//                    // 配列に変換
//                    $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
//                    //$export_arr = explode(",", $str);
//                    
//                    // 内容行を1行ごとにCSVデータへ
//                    $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");
//                    $prev_denno = $rows['denno'];
//                }
//           }
             if(!$_POST["csv_data"]){
                return;
            }
            $startDateM = date("Ymd");
            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');

            if( touch($file_path) ){
                // オブジェクト生成
                $file = new SplFileObject( $file_path, "w" ); 

 
                // エンコードしたタイトル行を配列ごとCSVデータ化
                //$file = fopen($file_path, 'a');
                
                // 取得結果を画面と同じように再構築して行として搭載
                // 内容行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
                $csv_data = json_decode($_POST['csv_data'],true);
                foreach( $csv_data as $str ){

                    // 配列に変換
                    $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
                    //$export_arr = explode(",", $str);
                    
                    // 内容行を1行ごとにCSVデータへ
                    if($str[0] === '"'){
                        $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");
                    }else{
                        $file = fopen($file_path, 'a');fwrite($file,$str."\r");
                    }
                    
                }
            }
            $_POST["csv_data"] = '';
 // pme 2012/12/02 end
            // ダウンロード用
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers 
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=仕入先・店舗別仕入元帳".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
