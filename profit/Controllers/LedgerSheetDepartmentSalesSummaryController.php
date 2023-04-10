<?php
    /**
     * @file      部門別売上集計表 [M]
     * @author    川橋
     * @date      2019/06/11
     * @version   1.00
     * @note      帳票 - 部門別売上集計表の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetDepartmentSalesSummary.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetDepartmentSalesSummaryController extends BaseController
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

            $LedgerSheetDepartmentSalesSummary = new LedgerSheetDepartmentSalesSummary();

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-P');

            //テスト用
            //$html = file_get_contents("./View/LedgerSheetDepartmentSalesSummaryPDFPanel.html");

            $saledYear = parent::escStr( $_POST['saled_year'] );
            $startCode  = parent::escStr( $_POST['start_code'] );
            $endCode  = parent::escStr( $_POST['end_code'] );
            $searchArray = array(
                'saled_year' => $saledYear,
                'start_code' => $startCode,
                'end_code'   => $endCode,
            );

            $list = $LedgerSheetDepartmentSalesSummary->getFormListData($searchArray);

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
            //print_r($_POST);
            
            //開始年
            $startYear = "2010";

            $LedgerSheetDepartmentSalesSummary = new LedgerSheetDepartmentSalesSummary();

            $abbreviatedNameList = $LedgerSheetDepartmentSalesSummary->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト
            $modal = new Modal();
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            $sect_detail    = [];
            $sect_detail    = $modal->getsect_detail();
            $param          = [];
            if($_POST){
                $param = $_POST;
            }
            $param["sect_cd"]   = "";
            $param["org_id"]    = "";

            $searchArray = [];            

            // 日付リストを取得
            $startDate = parent::escStr( $_POST['start_date'] );
            //$startDate = parent::escStr( $_POST['start_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDate) OR $startDate == ""){
                //現在日付の１日を設定
                $startDate =date('Y/m/d', strtotime('first day of ' . $month));
            }
            
            // 日付リストを取得
            $endDate = parent::escStr( $_POST['end_date'] );

            // 画面フォームに日付を返す
            if(!isset($endDate) OR $endDate == ""){
                //現在日付の１日を設定
                $endDate = date('Y/m/d', strtotime('last day of ' . $month));
            }
            $organizationId = 'false';
            //if($_POST['org_r'] === ""){
            //    if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
            //        $organizationId = $_POST['org_id'];
            //    }else{
            //        $organizationId = "'".$_POST['org_select']."'";
            //    }
            //}
            $org_r = isset($_POST['org_r']) ? parent::escStr($_POST['org_r']) : '';
            if($org_r === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
                }
            }
            else{
                $organizationId = $org_r;
            }
            $sect_cd = 'false';
            if($_POST['sect_r'] === ""){
                if($_POST['sect_select'] && $_POST['sect_select'] === 'empty'){
                    $sect_cd = $_POST['sect_cd'];
                }else{
                    $sect_cd = "'".$_POST['sect_select']."'";
                }
            }
            //$sectCdS = parent::escStr( $_POST['sectCdS'] );
            $sort = ($mode === "initial") ? '0' : parent::escStr( $_POST['sort'] );
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'sect_cd'           => $sect_cd,
                'sort'              => $sort,
            );

            if($mode != "initial") {

                // 帳票フォーム一覧データ取得
                //$ledgerSheetDetailList = $LedgerSheetDepartmentSalesSummary->getFormListData($searchArray);
                //print_r($ledgerSheetDetailList);
                $data = $LedgerSheetDepartmentSalesSummary->getFormListData($searchArray);
                $ledgerSheetDetailList = array();
                $abbreviated_name_bef = '';
                
                // 総合計
                $sum_sale_total = 0.0;
                //$sum_sale_total_percent = 0.0;
                $sum_sale_profit = 0.0;
                //$sum_sale_profit_percent = 0.0;
                $sum_sale_amount = 0.0;
                $sum_sale_cust_cnt_sum = 0.0;                
                
                // 構成比の計算用に、一度総合計を算出する
                for ($intL = 0; $intL < count($data); $intL ++){
                    // 純売上額
                    $sum_sale_total += floatval($data[$intL]['sale_total']);
                    // 粗利金額
                    $sum_sale_profit += floatval($data[$intL]['sale_profit']);
                    // 売上点数
                    $sum_sale_amount += floatval($data[$intL]['sale_amount']);
                    // 来店客数
                    $sum_sale_cust_cnt_sum += floatval($data[$intL]['sale_cust_cnt_sum']);
                }
                
                for ($intL = 0; $intL < count($data); $intL ++){
                    
                    $aryAddRow = $data[$intL];

                    // 企業計の場合
                    if (isset($aryAddRow['organization_id']) === false){
                        $aryAddRow['abbreviated_name'] = '企業計';
                    }
                    // 店舗名(表示用)
                    $aryAddRow['abbreviated_name_dsp'] = $aryAddRow['abbreviated_name'];

                    // 企業計 or ソートなし or 店舗名でソートの場合
                    if (isset($aryAddRow['organization_id']) === false || (isset($aryAddRow['organization_id']) === true && $sort <= 2)){
                        // 直前行と同じ店舗名は表示しない
                        if ($aryAddRow['abbreviated_name'] === $abbreviated_name_bef){
                            $aryAddRow['abbreviated_name_dsp'] = '';
                        }
                        // 店舗名を保持
                        $abbreviated_name_bef = $aryAddRow['abbreviated_name'];
                    }
                    // 売上構成比(%)　※ここで四捨五入する
//                    $aryAddRow['sale_total_percent'] = number_format(floatval($aryAddRow['sale_total_percent']), 1); 
                    $aryAddRow['sale_total_percent'] = number_format(floatval($aryAddRow['sale_total'] / $sum_sale_total * 100), 2); 
                    // 粗利構成比(%)　※ここで四捨五入する
//                    $aryAddRow['sale_profit_percent'] = number_format(floatval($aryAddRow['sale_profit_percent']), 1); 
                    $aryAddRow['sale_profit_percent'] = number_format(floatval($aryAddRow['sale_profit'] / $sum_sale_profit * 100), 2); 
                    // 粗利率
                    $aryAddRow['gross_profit_margin'] = number_format(floatval($aryAddRow['gross_profit_margin']), 2);
                    // 行追加
                    array_push($ledgerSheetDetailList, $aryAddRow);                    
                }
                if (count($data) > 0){
                    // 総合計
                    $aryAddRow = array(
                        'sect_nm'                   => '総合計',
                        'sale_total'                => $sum_sale_total,
                        'sale_total_percent'        => '100.0',
                        'sale_profit'               => $sum_sale_profit,
                        'sale_profit_percent'       => '100.0',
                        'gross_profit_margin'       => number_format(round(($sum_sale_profit / $sum_sale_total) * 100, 2), 2),
                        'sale_amount'               => $sum_sale_amount,
                        'sale_cust_cnt_sum'         => $sum_sale_cust_cnt_sum,
                    );
                    // 行追加
                    array_push($ledgerSheetDetailList, $aryAddRow);
                }
                
/*
                // 合計計算用変数
                $sumProdSaleAmount = 0;
                $sumProdSaleTotal = 0;
                $sumProdProfit = 0;

                if(count($ledgerSheetDetailList) > 0){
                    // 合計行を取得
                    foreach($ledgerSheetDetailList as $data){
                        $sumProdSaleAmount  = $sumProdSaleAmount + $data["sale_amount"];
                        $sumProdSaleTotal = $sumProdSaleTotal + $data["sale_total"];
                        $sumProdProfit    = $sumProdProfit + $data["sale_profit"];
                    }
                    if($sumProdSaleTotal !== 0){
                        $arariritsu = $sumProdProfit / $sumProdSaleTotal *100;
                    }
                    $sumLine = array(
                        'department_code'  => '',                 // 店舗CD
                        'abbreviated_name' => '',                 // 店舗名
                        'sect_cd'          => '',                 // 部門CD
                        'sect_nm'          => '',                 // 部門名
                        'type_cd'          => '',                 // 分類CD
                        'type_nm'          => '合計',             // 分類名
                        'sale_amount'      => $sumProdSaleAmount, // 売上数量
                        'sale_total'       => $sumProdSaleTotal,  // 売上金額
                        'sale_profit'      => $sumProdProfit,     // 粗利金額
                        'arariritsu'       => $arariritsu         // 粗利率
                        );

                    // 合計行を追加
                    array_push($ledgerSheetDetailList, $sumLine);
                }
 */
            }
            $headerArray = $this->changeHeaderItemMark($sort);

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
                'sect_cd'           => str_replace("'","",$_POST['sect_cd']),
                'sort'              => $sort,
            );
            $checked = "";
            if($_POST['tenpo']){
                $searchArray['tenpo'] = true;
            }
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetDepartmentSalesSummaryPanel.html';
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
            
            //開始年
            $startYear = "2010";

            $LedgerSheetDepartmentSalesSummary = new LedgerSheetDepartmentSalesSummary();

            // 日付リストを取得
            $startDate = parent::escStr( $_POST['start_date'] );
            //$startDate = parent::escStr( $_POST['start_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDate) OR $startDate == ""){
                //現在日付の１日を設定
                $startDate =date('Y/m/d', strtotime('first day of ' . $month));
            }
            
            // 日付リストを取得
            $endDate = parent::escStr( $_POST['end_date'] );

            // 画面フォームに日付を返す
            if(!isset($endDate) OR $endDate == ""){
                //現在日付の１日を設定
                $endDate = date('Y/m/d', strtotime('last day of ' . $month));
            }
            $organizationId = 'false';
            //if($_POST['org_r'] === ""){
            //    if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
            //        $organizationId = $_POST['org_id'];
            //    }else{
            //        $organizationId = "'".$_POST['org_select']."'";
            //    }
            //}
            $org_r = isset($_POST['org_r']) ? parent::escStr($_POST['org_r']) : '';
            if($org_r === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
                }
            }
            else{
                $organizationId = $org_r;
            }
            $sect_cd = 'false';
            if($_POST['sect_r'] === ""){
                if($_POST['sect_select'] && $_POST['sect_select'] === 'empty'){
                    $sect_cd = $_POST['sect_cd'];
                }else{
                    $sect_cd = "'".$_POST['sect_select']."'";
                }
            }
            //$sectCdS = parent::escStr( $_POST['sectCdS'] );
            $sort = ($mode === "initial") ? '0' : parent::escStr( $_POST['sort'] );
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'sect_cd'           => $sect_cd,
                'sort'              => $sort,
            );

            // 帳票フォーム一覧データ取得
            //$ledgerSheetDetailList = $LedgerSheetDepartmentSalesSummary->getFormListData($searchArray);
            //print_r($ledgerSheetDetailList);
            $data = $LedgerSheetDepartmentSalesSummary->getFormListData($searchArray);
            $ledgerSheetDetailList = array();
            $abbreviated_name_bef = '';
            // 総合計
            $sum_sale_total = 0.0;
            //$sum_sale_total_percent = 0.0;
            $sum_sale_profit = 0.0;
            //$sum_sale_profit_percent = 0.0;
            $sum_sale_amount = 0.0;
            $sum_sale_cust_cnt_sum = 0.0;
            
            // 構成比の計算用に、一度総合計を算出する
            for ($intL = 0; $intL < count($data); $intL ++){
                // 純売上額
                $sum_sale_total += floatval($data[$intL]['sale_total']);
                // 粗利金額
                $sum_sale_profit += floatval($data[$intL]['sale_profit']);
                // 売上点数
                $sum_sale_amount += floatval($data[$intL]['sale_amount']);
                // 来店客数
                $sum_sale_cust_cnt_sum += floatval($data[$intL]['sale_cust_cnt_sum']);
            }

            for ($intL = 0; $intL < count($data); $intL ++){

                $aryAddRow = $data[$intL];

                // 企業計の場合
                if (isset($aryAddRow['organization_id']) === false){
                    $aryAddRow['abbreviated_name'] = '企業計';
                }
                // 店舗名(表示用)
                $aryAddRow['abbreviated_name_dsp'] = $aryAddRow['abbreviated_name'];

                // 企業計 or ソートなし or 店舗名でソートの場合
                if (isset($aryAddRow['organization_id']) === false || (isset($aryAddRow['organization_id']) === true && $sort <= 2)){
                    // 直前行と同じ店舗名は表示しない
                    if ($aryAddRow['abbreviated_name'] === $abbreviated_name_bef){
                        $aryAddRow['abbreviated_name_dsp'] = '';
                    }
                    // 店舗名を保持
                    $abbreviated_name_bef = $aryAddRow['abbreviated_name'];
                }
                // 売上構成比(%)
//                $aryAddRow['sale_total_percent'] = number_format(floatval($aryAddRow['sale_total_percent']), 1, ".", "");
                $aryAddRow['sale_total_percent'] = number_format(floatval($aryAddRow['sale_total'] / $sum_sale_total * 100), 2, ".", "");
                // 粗利構成比(%)
//                $aryAddRow['sale_profit_percent'] = number_format(floatval($aryAddRow['sale_profit_percent']), 1, ".", "");
                $aryAddRow['sale_profit_percent'] = number_format(floatval($aryAddRow['sale_profit'] / $sum_sale_profit * 100), 2, ".", "");
                // 粗利率
                $aryAddRow['gross_profit_margin'] = number_format(floatval($aryAddRow['gross_profit_margin']), 2, ".", "");
                // 行追加
                array_push($ledgerSheetDetailList, $aryAddRow);
            }
            // 総合計
            $aryAddRow = array(
                'sect_nm'                   => '総合計',
                'sale_total'                => $sum_sale_total,
                'sale_total_percent'        => '100.0',
                'sale_profit'               => $sum_sale_profit,
                'sale_profit_percent'       => '100.0',
                'gross_profit_margin'       => number_format(round(($sum_sale_profit / $sum_sale_total) * 100, 2), 2, ".", ""),
                'sale_amount'               => $sum_sale_amount,
                'sale_cust_cnt_sum'         => $sum_sale_cust_cnt_sum,
            );
            // 行追加
            array_push($ledgerSheetDetailList, $aryAddRow);

            // 画面フォームに日付を返す
            if(!isset($saledYear) OR $saledYear == "")
            {
                //現在日付の１日を設定
                $saledYear =date('Y');
            }

            // ファイル名
            //$file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');
            $file_path = date('YmdHis').'.csv';

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "店舗");
            //array_push($export_csv_title, "部門コード");
            array_push($export_csv_title, "部門名");
            array_push($export_csv_title, "純売上額");
            array_push($export_csv_title, "売上構成比(%)");            
            array_push($export_csv_title, "粗利金額");            
            array_push($export_csv_title, "粗利構成比(%)");
            array_push($export_csv_title, "粗利率(%)");
            array_push($export_csv_title, "売上点数");
            array_push($export_csv_title, "来店客数");

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
                foreach( $ledgerSheetDetailList as $rows ){
                    
                    // 垂直ヘッダ
                    $list_no += 1;

                    $str = '"'.$rows['abbreviated_name'];
                    // ↓↓↓画面表示と同等(企業計検索または店舗でソートした場合に前行と同じ店舗を表示しない)出力するならこちらを有効にする
                    //$str = $str.'","'.$rows['abbreviated_name_dsp'];
                    //$str = $str.'","'.$rows['sect_cd'];
                    $str = $str.'","'.$rows['sect_nm'];
                    $str = $str.'","'.number_format(floatval($rows['sale_total']), 0, ".", "");
                    $str = $str.'","'.$rows['sale_total_percent'];
                    $str = $str.'","'.number_format(floatval($rows['sale_profit']), 0, ".", "");
                    $str = $str.'","'.$rows['sale_profit_percent'];
                    $str = $str.'","'.$rows['gross_profit_margin'];
                    $str = $str.'","'.number_format(floatval($rows['sale_amount']), 0, ".", "");
                    $str = $str.'","'.number_format(floatval($rows['sale_cust_cnt_sum']), 0, ".", "");
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
            header("Content-Disposition: attachment; filename=部門別売上集計表".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
        /**
         * ヘッダー部分ソート時のマーク変更
         * @note     ソート番号により、ソートマークを設定する
         * @param    $sortNo ソート条件番号
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        private function changeHeaderItemMark( $sortNo )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeHeaderItemMark");

            // 初期化
            $headerArray = array(
                'tenpoSortMark'                 => '',
                'sect_nmSortMark'               => '',
                'sale_totalSortMark'            => '',
                'sale_total_percentSortMark'    => '',
                'sale_profitSortMark'           => '',
                'sale_profit_percentSortMark'   => '',
                'gross_profit_marginSortMark'   => '',
                'sale_amountSortMark'           => '',
                'sale_cust_cnt_sumSortMark'     => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]    = "tenpoSortMark";
                $sortList[2]    = "tenpoSortMark";
                $sortList[3]    = "sect_nmSortMark";
                $sortList[4]    = "sect_nmSortMark";
                $sortList[5]    = "sale_totalSortMark";
                $sortList[6]    = "sale_totalSortMark";
                $sortList[7]    = "sale_total_percentSortMark";
                $sortList[8]    = "sale_total_percentSortMark";
                $sortList[9]    = "sale_profitSortMark";
                $sortList[10]   = "sale_profitSortMark";
                $sortList[11]   = "sale_profit_percentSortMark";
                $sortList[12]   = "sale_profit_percentSortMark";
                $sortList[13]   = "gross_profit_marginSortMark";
                $sortList[14]   = "gross_profit_marginSortMark";
                $sortList[15]   = "sale_amountSortMark";
                $sortList[16]   = "sale_amountSortMark";
                $sortList[17]   = "sale_cust_cnt_sumSortMark";
                $sortList[18]   = "sale_cust_cnt_sumSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START changeHeaderItemMark");

            return $headerArray;
        }
    }
?>
