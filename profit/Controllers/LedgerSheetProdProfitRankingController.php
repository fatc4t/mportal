<?php
    /**
     * @file      商品粗利ベスト10一覧表 [M]
     * @author    川橋
     * @date      2019/06/06
     * @version   1.00
     * @note      帳票 - 商品粗利ベスト10一覧表の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetProdProfitRanking.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetProdProfitRankingController extends BaseController
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

            $LedgerSheetProdProfitRanking = new LedgerSheetProdProfitRanking();

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-P');

            //テスト用
            //$html = file_get_contents("./View/LedgerSheetProdProfitRankingPDFPanel.html");

            $saledYear = parent::escStr( $_POST['saled_year'] );
            $startCode  = parent::escStr( $_POST['start_code'] );
            $endCode  = parent::escStr( $_POST['end_code'] );
            $searchArray = array(
                'saled_year' => $saledYear,
                'start_code' => $startCode,
                'end_code'   => $endCode,
            );

            $list = $LedgerSheetProdProfitRanking->getFormListData($searchArray);

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

            $LedgerSheetProdProfitRanking = new LedgerSheetProdProfitRanking();

            $abbreviatedNameList = $LedgerSheetProdProfitRanking->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト
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
            //$startDate = parent::escStr( $_POST['start_date'] );
            $startDate = parent::escStr( $_POST['start_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDate) OR $startDate == ""){
                //現在日付の１日を設定
                $startDate =date('Y/m/d', strtotime('first day of ' . $month));
            }
            $tmpDate = explode('/', $startDate);
            $startDate = $tmpDate[0].'/'.$tmpDate[1].'/01';
            
            // 日付リストを取得
            //$endDate = parent::escStr( $_POST['end_date'] );

            // 画面フォームに日付を返す
            //if(!isset($endDate) OR $endDate == ""){
            //    //現在日付の１日を設定
            //    $endDate = date('Y/m/d', strtotime('last day of ' . $month));
            //}
            $endDate = date('Y/m/d', strtotime('last day of ' . $startDate));
            
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
                //$ledgerSheetDetailList = $LedgerSheetProdProfitRanking->getFormListData($searchArray);
                //print_r($ledgerSheetDetailList);
                $data = $LedgerSheetProdProfitRanking->getFormListData($searchArray);
                $ledgerSheetDetailList = array();
                $org_bef = '';
                $composition_ratio = 0.0;
                $cumulative_ratio = 0.0;
                $sum_amount = 0.0;
                $sum_total = 0.0;
                $sum_profit = 0.0;
                $cnt = 0;
                $profit_limit = '';
                foreach ($data as $row){
                    if ($searchArray['organization_id'] === 'compile'){
                        // SQLで取得しないのでここで補完する
                        $row['organization_id'] = '';
                        $row['abbreviated_name'] = '';
                    }
                    $org = intval($row['organization_id']);
                    if ($org !== $org_bef){
                        /*
                         * 不要とのことですが一応残しておきます
                        if ($org_bef !== ''){
                            // 集計レコード
                            $aryAddRow = array(
                                'no'                    => '集計',
                                'abbreviated_name'      => '',
                                'sect_nm'               => '',
                                'prod_cd'               => '',
                                'prod_nm'               => '',
                                'prod_sale_amount'      => number_format($sum_amount, 0),
                                'saleprice'             => '',
                                'costprice'             => '',
                                'prod_sale_total'       => number_format($sum_total, 0),
                                'prod_profit'           => number_format($sum_profit, 0),
                                'composition_ratio'     => number_format($composition_ratio, 2).'%',
                                'cumulative_ratio'      => number_format($cumulative_ratio, 2).'%',
                                'gross_profit_margin'   => '',
                            );
                            array_push($ledgerSheetDetailList, $aryAddRow);
                        }
                        */
                        $composition_ratio = 0.0;
                        $cumulative_ratio = 0.0;
                        $sum_amount = 0.0;
                        $sum_total = 0.0;
                        $sum_profit = 0.0;
                        $cnt = 0;
                        $profit_limit = '';
                    }

                    if ($profit_limit !== '') {
                        // 粗利の値が11行目以降の粗利が10行目と同じなら続行
                        if ($profit_limit !== floatval($row['prod_profit'])){
                            continue;
                        }
                    }

                    $composition_ratio += floatval($row['composition_ratio']);
                    $cumulative_ratio += floatval($row['composition_ratio']);
                    $sum_amount += floatval($row['prod_sale_amount']);
                    $sum_total += floatval($row['prod_sale_total']);
                    $sum_profit += floatval($row['prod_profit']);
                    
                    $aryAddRow = array(
                        'no'                    => strval($cnt + 1),
                        'abbreviated_name'      => $row['abbreviated_name'],
                        'sect_nm'               => $row['sect_nm'],
                        'prod_cd'               => $row['prod_cd'],
                        'prod_nm'               => $row['prod_nm'],
                        'prod_sale_amount'      => number_format(floatval($row['prod_sale_amount'])),
                        'saleprice'             => number_format(floatval($row['saleprice'])),
                        'costprice'             => number_format(floatval($row['costprice']), 2),
                        'prod_sale_total'       => number_format(floatval($row['prod_sale_total'])),
                        'prod_profit'           => number_format(floatval($row['prod_profit'])),
                        'composition_ratio'     => number_format(floatval($row['composition_ratio']), 2).'%',
                        'cumulative_ratio'      => number_format($cumulative_ratio, 2).'%',
                        'gross_profit_margin'   => number_format(floatval($row['gross_profit_margin']), 2).'%',
                    );
                    array_push($ledgerSheetDetailList, $aryAddRow);
                    $org_bef = $org;
                    $cnt ++;
                    if ($cnt === 10){
                        $profit_limit = floatval($row['prod_profit']);
                    }
                }
                /*
                 * 不要とのことですが一応残しておきます
                // ループ脱出後、集計レコード
                $aryAddRow = array(
                    'no'                    => '集計',
                    'abbreviated_name'      => '',
                    'sect_nm'               => '',
                    'prod_cd'               => '',
                    'prod_nm'               => '',
                    'prod_sale_amount'      => number_format($sum_amount, 0),
                    'saleprice'             => '',
                    'costprice'             => '',
                    'prod_sale_total'       => number_format($sum_total, 0),
                    'prod_profit'           => number_format($sum_profit, 0),
                    'composition_ratio'     => number_format($composition_ratio, 2).'%',
                    'cumulative_ratio'      => number_format($cumulative_ratio, 2).'%',
                    'gross_profit_margin'   => '',
                );
                array_push($ledgerSheetDetailList, $aryAddRow);
                 */
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
                require_once './View/LedgerSheetProdProfitRankingPanel.html';
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

            $LedgerSheetProdProfitRanking = new LedgerSheetProdProfitRanking();

            // 日付リストを取得
            //$startDate = parent::escStr( $_POST['start_date'] );
            $startDate = parent::escStr( $_POST['start_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDate) OR $startDate == ""){
                //現在日付の１日を設定
                $startDate =date('Y/m/d', strtotime('first day of ' . $month));
            }
            $tmpDate = explode('/', $startDate);
            $startDate = $tmpDate[0].'/'.$tmpDate[1].'/01';
            
            // 日付リストを取得
            //$endDate = parent::escStr( $_POST['end_date'] );

            // 画面フォームに日付を返す
            //if(!isset($endDate) OR $endDate == ""){
            //    //現在日付の１日を設定
            //    $endDate = date('Y/m/d', strtotime('last day of ' . $month));
            //}
            $endDate = date('Y/m/d', strtotime('last day of ' . $startDate));
            
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
            //$ledgerSheetDetailList = $LedgerSheetProdProfitRanking->getFormListData($searchArray);
            $data = $LedgerSheetProdProfitRanking->getFormListData($searchArray);
            $ledgerSheetDetailList = array();
            $org_bef = '';
            $composition_ratio = 0.0;
            $cumulative_ratio = 0.0;
            $sum_amount = 0.0;
            $sum_total = 0.0;
            $sum_profit = 0.0;
            $cnt = 0;
            $profit_limit = '';
            foreach ($data as $row){
                if ($searchArray['organization_id'] === 'compile'){
                    // SQLで取得しないのでここで補完する
                    $row['organization_id'] = '';
                    $row['abbreviated_name'] = '';
                }
                $org = intval($row['organization_id']);
                if ($org !== $org_bef){
                    /*
                     * 不要とのことですが一応残しておきます
                    if ($org_bef !== ''){
                        // 集計レコード
                        $aryAddRow = array(
                            'no'                    => '集計',
                            'abbreviated_name'      => '',
                            'sect_nm'               => '',
                            'prod_cd'               => '',
                            'prod_nm'               => '',
                            'prod_sale_amount'      => number_format($sum_amount, 0),
                            'saleprice'             => '',
                            'costprice'             => '',
                            'prod_sale_total'       => number_format($sum_total, 0),
                            'prod_profit'           => number_format($sum_profit, 0),
                            'composition_ratio'     => number_format($composition_ratio, 2).'%',
                            'cumulative_ratio'      => number_format($cumulative_ratio, 2).'%',
                            'gross_profit_margin'   => '',
                        );
                        array_push($ledgerSheetDetailList, $aryAddRow);
                    }
                    */
                    $composition_ratio = 0.0;
                    $cumulative_ratio = 0.0;
                    $sum_amount = 0.0;
                    $sum_total = 0.0;
                    $sum_profit = 0.0;
                    $cnt = 0;
                    $profit_limit = '';
                }

                if ($profit_limit !== '') {
                    // 粗利の値が11行目以降の粗利が10行目と同じなら続行
                    if ($profit_limit !== floatval($row['prod_profit'])){
                        continue;
                    }
                }

                $composition_ratio += floatval($row['composition_ratio']);
                $cumulative_ratio += floatval($row['composition_ratio']);
                $sum_amount += floatval($row['prod_sale_amount']);
                $sum_total += floatval($row['prod_sale_total']);
                $sum_profit += floatval($row['prod_profit']);

                $aryAddRow = array(
                    'no'                    => strval($cnt + 1),
                    'abbreviated_name'      => $row['abbreviated_name'],
                    'sect_nm'               => $row['sect_nm'],
                    'prod_cd'               => $row['prod_cd'],
                    'prod_nm'               => $row['prod_nm'],
                    'prod_sale_amount'      => number_format(floatval($row['prod_sale_amount']), 0, ".", ""),
                    'saleprice'             => number_format(floatval($row['saleprice']), 0, ".", ""),
                    'costprice'             => number_format(floatval($row['costprice']), 2, ".", ""),
                    'prod_sale_total'       => number_format(floatval($row['prod_sale_total']), 0, ".", ""),
                    'prod_profit'           => number_format(floatval($row['prod_profit']), 0, ".", ""),
                    'composition_ratio'     => number_format(floatval($row['composition_ratio']), 2, ".", "").'%',
                    'cumulative_ratio'      => number_format($cumulative_ratio, 2, ".", "").'%',
                    'gross_profit_margin'   => number_format(floatval($row['gross_profit_margin']), 2, ".", "").'%',
                );
                array_push($ledgerSheetDetailList, $aryAddRow);
                $org_bef = $org;
                $cnt ++;
                if ($cnt === 10){
                    $profit_limit = floatval($row['prod_profit']);
                }
            }
            /*
             * 不要とのことですが一応残しておきます
            // ループ脱出後、集計レコード
            $aryAddRow = array(
                'no'                    => '集計',
                'abbreviated_name'      => '',
                'sect_nm'               => '',
                'prod_cd'               => '',
                'prod_nm'               => '',
                'prod_sale_amount'      => number_format($sum_amount, 0),
                'saleprice'             => '',
                'costprice'             => '',
                'prod_sale_total'       => number_format($sum_total, 0),
                'prod_profit'           => number_format($sum_profit, 0),
                'composition_ratio'     => number_format($composition_ratio, 2).'%',
                'cumulative_ratio'      => number_format($cumulative_ratio, 2).'%',
                'gross_profit_margin'   => '',
            );
            array_push($ledgerSheetDetailList, $aryAddRow);
             */
            
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
            array_push($export_csv_title, "No");
            //array_push($export_csv_title, "店舗CD");
            array_push($export_csv_title, "店舗");
            //array_push($export_csv_title, "部門コード");
            array_push($export_csv_title, "部門名");
            array_push($export_csv_title, "商品コード");
            array_push($export_csv_title, "商品名");            
            array_push($export_csv_title, "数量");            
            array_push($export_csv_title, "売単価");
            array_push($export_csv_title, "原単価");
            array_push($export_csv_title, "売上金額");
            array_push($export_csv_title, "粗利金額");
            array_push($export_csv_title, "構成比");
            array_push($export_csv_title, "累積比");
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
                foreach( $ledgerSheetDetailList as $rows ){
                    
                    // 垂直ヘッダ
                    $list_no += 1;

                    //$str = '"'.$list_no;// 順位
                    $str = '"'.$rows['no'];// 順位
                    //$str = $str.'","'.$rows['department_code'];
                    $str = $str.'","'.$rows['abbreviated_name'];
                    //$str = $str.'","'.$rows['sect_cd'];
                    $str = $str.'","'.$rows['sect_nm'];
                    $str = $str.'","'.$rows['prod_cd'];
                    $str = $str.'","'.$rows['prod_nm'];                    
                    $str = $str.'","'.$rows['prod_sale_amount'];                    
                    $str = $str.'","'.$rows['saleprice'];
                    $str = $str.'","'.$rows['costprice'];
                    $str = $str.'","'.$rows['prod_sale_total'];
                    $str = $str.'","'.$rows['prod_profit'];
                    $str = $str.'","'.$rows['composition_ratio'];
                    $str = $str.'","'.$rows['cumulative_ratio'];
                    $str = $str.'","'.$rows['gross_profit_margin'];
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
            header("Content-Disposition: attachment; filename=粗利ベスト１０".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
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
                'prod_cdSortMark'               => '',
                'prod_nmSortMark'               => '',
                'prod_sale_amountSortMark'      => '',
                'salepriceSortMark'             => '',
                'costpriceSortMark'             => '',
                'prod_sale_totalSortMark'       => '',
                'prod_profitSortMark'           => '',
                'composition_ratioSortMark'     => '',
                'cumulative_ratioSortMark'      => '',
                'gross_profit_marginSortMark'   => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]    = "tenpoSortMark";
                $sortList[2]    = "tenpoSortMark";
                $sortList[3]    = "sect_nmSortMark";
                $sortList[4]    = "sect_nmSortMark";
                $sortList[5]    = "prod_cdSortMark";
                $sortList[6]    = "prod_cdSortMark";
                $sortList[7]    = "prod_nmSortMark";
                $sortList[8]    = "prod_nmSortMark";
                $sortList[9]    = "prod_sale_amountSortMark";
                $sortList[10]   = "prod_sale_amountSortMark";
                $sortList[11]   = "salepriceSortMark";
                $sortList[12]   = "salepriceSortMark";
                $sortList[13]   = "costpriceSortMark";
                $sortList[14]   = "costpriceSortMark";
                $sortList[15]   = "prod_sale_totalSortMark";
                $sortList[16]   = "prod_sale_totalSortMark";
                $sortList[17]   = "prod_profitSortMark";
                $sortList[18]   = "prod_profitSortMark";
                $sortList[19]   = "composition_ratioSortMark";
                $sortList[20]   = "composition_ratioSortMark";
                $sortList[21]   = "cumulative_ratioSortMark";
                $sortList[22]   = "cumulative_ratioSortMark";
                $sortList[23]   = "gross_profit_marginSortMark";
                $sortList[24]   = "gross_profit_marginSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START changeHeaderItemMark");

            return $headerArray;
        }
    }
?>
