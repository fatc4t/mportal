<?php
    /**
     * @file      帳票 - 商品別在庫数詳細コントローラ
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 商品別在庫数詳細の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetStockTransition.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetStockTransitionController extends BaseController
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

            $ledgerSheetStockTransition = new ledgerSheetStockTransition();

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-P');

            //テスト用
            //$html = file_get_contents("./View/LedgerSheetBySalesStaffSpreadSheetPDFPanel.html");

            $saledYear = parent::escStr( $_POST['saled_year'] );
            $startCode  = parent::escStr( $_POST['start_code'] );
            $endCode  = parent::escStr( $_POST['end_code'] );
            $searchArray = array(
                'saled_year' => $saledYear,
                'start_code' => $startCode,
                'end_code'   => $endCode,
            );

            $list = $ledgerSheetBySalesStaffSpreadSheet->getFormListData($searchArray);

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

            $tantou = parent::escStr($_POST['staff_nm']);
            $ledgerSheetStockTransition = new ledgerSheetStockTransition();

            $abbreviatedNameList = $ledgerSheetStockTransition->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

            $modal = new Modal();
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            $prod_detail    = [];
            $prod_detail    = $modal->getprod_detail();
            $sect_detail    = [];
            $sect_detail    = $modal->getsect_detail();
            $param          = [];
            if($_POST){
                $param = $_POST;
            }
            $param["prod_cd"]   = "";
            $param["sect_cd"]   = "";
            $param["org_id"]    = "";
            
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
            $prod_cd = 'false';
            if($_POST['prod_r'] === ""){
                if($_POST['prod_select'] && $_POST['prod_select'] === 'empty'){
                    $prod_cd = $_POST['prod_cd'];
                }else{
                    $prod_cd = "'".$_POST['prod_select']."'";
                }
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
            
            // 日付リストを取得
            $startDateM = parent::escStr( $_POST['start_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDateM) OR $startDateM == ""){
                //現在日付の１日を設定
                $month = date('Y-m');
                $startDateM = date('Y/m', strtotime('first day of ' . $month));
            }
            // 月初を設定
            $startDate        = parent::escStr( $_POST['start_dateM'] );
            $startDate        = $startDate.'/01';
            
            // 日付リストを取得
            $endDateM = parent::escStr( $_POST['end_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($endDateM) OR $endDateM == ""){
                //現在日付の１日を設定
                $month = date('Y-m');
                $endDateM = date('Y/m', strtotime('last day of ' . $month));
            }
            // 月末を設定
            $endDate          = parent::escStr( $_POST['end_dateM'] );
            $endDate          = $endDate.'/'.date('t', strtotime($endDate.'/01'));
/*            
            $organizationId   = parent::escStr( $_POST['organizationName'] );
            $prodK = parent::escStr( $_POST['prod_k']);
            $prodS = parent::escStr( $_POST['prod_s']);
            $sectK = parent::escStr( $_POST['sect_k']);
            $sectS = parent::escStr( $_POST['sect_s']);
*/
            $prtType = parent::escStr( $_POST['prt_type']);
/*
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'prod_k'            => $prodK,
                'prod_s'            => $prodS,
                'sect_k'            => $sectK,
                'sect_s'            => $sectS,
            );
            
*/
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'prod_cd'           => $prod_cd,
                'sect_cd'           => $sect_cd,     
            );
            
            
            // テーブルヘッダ
            $ledgerSheetDateColumns = array();
            $dtStart = strtotime($startDate);
            $dtEnd   = strtotime($endDate);
            while ($dtStart < $dtEnd){
                array_push($ledgerSheetDateColumns, date('Ym', $dtStart));
                $dtStart = strtotime(date('Y-m-d', $dtStart).' +1 month');
            }
            
            $ledgerSheetDetailList = array();
        
            if($mode != "initial") {
                // 帳票フォーム一覧データ取得
                //$ledgerSheetDetailList = $ledgerSheetStockTransition->getFormListData($searchArray);
                $data = $ledgerSheetStockTransition->getFormListData($searchArray);

                // 画面に合わせて調整
                $aryAddRow = array();
                $intOrgnId_Bef = -1;
                $strProdCd_Bef = '';

                foreach ($data as $row){
                    $intOrgnId = intval($row['organization_id']);
                    $strProdCd = strval($row['prod_cd']);
                    if ($intOrgnId !== $intOrgnId_Bef || $strProdCd !== $strProdCd_Bef){
                        // 配列追加
                        if (count($aryAddRow) > 0){
                            array_push($ledgerSheetDetailList, $aryAddRow);
                        }

                        $aryAddRow = array();
                        $aryAddRow['organization_id']   = strval($intOrgnId);
                        $aryAddRow['abbreviated_name']  = $row['abbreviated_name'];
                        $aryAddRow['prod_cd']           = $strProdCd;
                        $aryAddRow['prod_nm']           = $row['prod_nm'];
                        $aryAddRow['delete_kbn']        = '0';
                        for ($intL = 0; $intL < count($ledgerSheetDateColumns); $intL ++){
                            $aryAddRow['supp_amount'.strval($intL)]     = '0';
                            $aryAddRow['sale_amount'.strval($intL)]     = '0';
                            $aryAddRow['out_amount'.strval($intL)]      = '0';
                            $aryAddRow['in_amount'.strval($intL)]       = '0';
                            $aryAddRow['disposal_amount'.strval($intL)] = '0';
                            $aryAddRow['stock_amount'.strval($intL)]    = '0';
                        }
                    }
                    // amount値を格納するカラム位置を検索
                    //$needle = $row['proc_date'];
                    $needle = substr($row['proc_date'], 0, 6);
                    $key = array_search($needle, $ledgerSheetDateColumns, true);
                    switch ($row['kbn']){
                        case '1':       // 仕入数
                            $field = 'supp_amount'.strval($key);
                            break;
                        case '2':       // 販売数
                            $field = 'sale_amount'.strval($key);
                            break;
                        case '3':       // 出庫数
                            $field = 'out_amount'.strval($key);
                            break;
                        case '4':       // 入庫数
                            $field = 'in_amount'.strval($key);
                            break;
                        case '5':       // 廃棄数
                            $field = 'disposal_amount'.strval($key);
                            break;
                        case '6':       // 在庫数
                            $field = 'stock_amount'.strval($key);
                            break;
                        default:
                            continue;
                            break;
                    }
                    $aryAddRow[$field] = number_format($row['amount']);
                    
                    // キー保持
                    $intOrgnId_Bef = $intOrgnId;
                    $strProdCd_Bef = $strProdCd;
                }
                // ループ脱出後　配列追加
                if (count($aryAddRow) > 0){
                    array_push($ledgerSheetDetailList, $aryAddRow);
                }
            }
            
            // 指定期間内で在庫数に変化がない商品は除く
            if ($prtType === '1'){
                for ($intL = 0; $intL < count($ledgerSheetDetailList); $intL ++){
                    $dmlStock_Bef = 0;
                    $ledgerSheetDetailList[$intL]['delete_kbn'] = '1';              // 非表示で初期化しておく
                    for ($intJ = 0; $intJ < count($ledgerSheetDateColumns); $intJ ++){
                        $dmlStock = floatval($ledgerSheetDetailList[$intL]['stock_amount'.strval($intJ)]);
                        if ($intJ > 0) {
                            if ($dmlStock !== $dmlStock_Bef){
                                $ledgerSheetDetailList[$intL]['delete_kbn'] = '0';  // 表示
                                break;
                            }
                        }
                        $dmlStock_Bef = $dmlStock;
                    }
                }
            }

            $searchArray = array(
                'org_id'            => str_replace("'","",$_POST['org_id']),
                'prod_cd'           => str_replace("'","",$_POST['prod_cd']),
                'sect_cd'           => str_replace("'","",$_POST['sect_cd']),
                'start_date'        => $startDateM,
                'end_date'          => $endDateM,
                'prt_type'          => $prtType,
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
                require_once './View/LedgerSheetStockTransitionPanel.html';
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
            
            $ledgerSheetStockTransition = new ledgerSheetStockTransition();

            $abbreviatedNameList = $ledgerSheetStockTransition->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

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
            $prod_cd = 'false';
            if($_POST['prod_r'] === ""){
                if($_POST['prod_select'] && $_POST['prod_select'] === 'empty'){
                    $prod_cd = $_POST['prod_cd'];
                }else{
                    $prod_cd = "'".$_POST['prod_select']."'";
                }
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
            
            // 日付リストを取得
            //$startDate = parent::escStr( $_POST['start_date'] );

            // 画面フォームに日付を返す
            //if(!isset($startDate) OR $startDate == ""){
            //    //現在日付の１日を設定
            //    $startDate =date('Y/m/d', strtotime('first day of ' . $month));
            //}
            
            // 日付リストを取得
            $startDateM = parent::escStr( $_POST['start_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDateM) OR $startDateM == ""){
                //現在日付の１日を設定
                $month = date('Y-m');
                $startDateM = date('Y/m', strtotime('first day of ' . $month));
            }
            // 月初を設定
            $startDate        = $startDateM.'/01';
            
            // 日付リストを取得
            //$endDate = parent::escStr( $_POST['end_date'] );

            // 画面フォームに日付を返す
            //if(!isset($endDate) OR $endDate == ""){
            //    //現在日付の１日を設定
            //    $endDate = date('Y/m/d', strtotime('last day of ' . $endDate));
            //}

            // 日付リストを取得
            $endDateM = parent::escStr( $_POST['end_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($endDateM) OR $endDateM == ""){
                //現在日付の１日を設定
                $month = date('Y-m');
                $endDateM = date('Y/m', strtotime('last day of ' . $month));
            }
            // 月末を設定
            $endDate          = $endDateM.'/'.date('t', strtotime($endDateM.'/01'));
/*
            $organizationId   = parent::escStr( $_POST['organizationName'] );
            $prodK = parent::escStr( $_POST['prod_k']);
            $prodS = parent::escStr( $_POST['prod_s']);
            $sectK = parent::escStr( $_POST['sect_k']);
            $sectS = parent::escStr( $_POST['sect_s']);
 */
            $prtType = parent::escStr( $_POST['prt_type']);
/*
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'prod_k'            => $prodK,
                'prod_s'            => $prodS,
                'sect_k'            => $sectK,
                'sect_s'            => $sectS,
            );
*/
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'prod_cd'           => $prod_cd,
                'sect_cd'           => $sect_cd,     
            );

            // テーブルヘッダ
            $ledgerSheetDateColumns = array();
            $dtStart = strtotime($startDate);
            $dtEnd   = strtotime($endDate);
            while ($dtStart < $dtEnd){
                array_push($ledgerSheetDateColumns, date('Ym', $dtStart));
                $dtStart = strtotime(date('Y-m-d', $dtStart).' +1 month');
            }
            
            
            $ledgerSheetDetailList = array();
        
            // 帳票フォーム一覧データ取得
            //$ledgerSheetDetailList = $ledgerSheetStockTransition->getFormListData($searchArray);
            $data = $ledgerSheetStockTransition->getFormListData($searchArray);

            // 画面に合わせて調整
            $aryAddRow = array();
            $intOrgnId_Bef = -1;
            $strProdCd_Bef = '';

            foreach ($data as $row){
                $intOrgnId = intval($row['organization_id']);
                $strProdCd = strval($row['prod_cd']);
                if ($intOrgnId !== $intOrgnId_Bef || $strProdCd !== $strProdCd_Bef){
                    // 配列追加
                    if (count($aryAddRow) > 0){
                        array_push($ledgerSheetDetailList, $aryAddRow);
                    }

                    $aryAddRow = array();
                    $aryAddRow['organization_id']   = strval($intOrgnId);
                    $aryAddRow['abbreviated_name']  = $row['abbreviated_name'];
                    $aryAddRow['prod_cd']           = $strProdCd;
                    $aryAddRow['prod_nm']           = $row['prod_nm'];
                    $aryAddRow['delete_kbn']        = '0';
                    for ($intL = 0; $intL < count($ledgerSheetDateColumns); $intL ++){
                        $aryAddRow['supp_amount'.strval($intL)]     = '0';
                        $aryAddRow['sale_amount'.strval($intL)]     = '0';
                        $aryAddRow['out_amount'.strval($intL)]      = '0';
                        $aryAddRow['in_amount'.strval($intL)]       = '0';
                        $aryAddRow['disposal_amount'.strval($intL)] = '0';
                        $aryAddRow['stock_amount'.strval($intL)]    = '0';
                    }
                }
                // amount値を格納するカラム位置を検索
                //$needle = $row['proc_date'];
                $needle = substr($row['proc_date'], 0, 6);
                $key = array_search($needle, $ledgerSheetDateColumns, true);
                switch ($row['kbn']){
                    case '1':       // 仕入数
                        $field = 'supp_amount'.strval($key);
                        break;
                    case '2':       // 販売数
                        $field = 'sale_amount'.strval($key);
                        break;
                    case '3':       // 出庫数
                        $field = 'out_amount'.strval($key);
                        break;
                    case '4':       // 入庫数
                        $field = 'in_amount'.strval($key);
                        break;
                    case '5':       // 廃棄数
                        $field = 'disposal_amount'.strval($key);
                        break;
                    case '6':       // 在庫数
                        $field = 'stock_amount'.strval($key);
                        break;
                    default:
                        continue;
                        break;
                }
                //$aryAddRow[$field] = number_format($row['amount']);
                $aryAddRow[$field] = number_format($row['amount'], 0, "", "");

                // キー保持
                $intOrgnId_Bef = $intOrgnId;
                $strProdCd_Bef = $strProdCd;
            }
            // ループ脱出後　配列追加
            if (count($aryAddRow) > 0){
                array_push($ledgerSheetDetailList, $aryAddRow);
            }
            
            // 指定期間内で在庫数に変化がない商品は除く
            if ($prtType === '1'){
                for ($intL = 0; $intL < count($ledgerSheetDetailList); $intL ++){
                    $dmlStock_Bef = 0;
                    $ledgerSheetDetailList[$intL]['delete_kbn'] = '1';              // 非表示で初期化しておく
                    for ($intJ = 0; $intJ < count($ledgerSheetDateColumns); $intJ ++){
                        $dmlStock = floatval($ledgerSheetDetailList[$intL]['stock_amount'.strval($intJ)]);
                        if ($intJ > 0) {
                            if ($dmlStock !== $dmlStock_Bef){
                                $ledgerSheetDetailList[$intL]['delete_kbn'] = '0';  // 表示
                                break;
                            }
                        }
                        $dmlStock_Bef = $dmlStock;
                    }
                }
            }
            
            // ファイル名
            //$file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');
            $file_path = mb_convert_encoding(date(YmdHis).'.csv', 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // ヘッダ追加
            //array_push($export_csv_title, "No");
            array_push($export_csv_title, "店舗");
            array_push($export_csv_title, "商品コード");
            array_push($export_csv_title, "商品");
            array_push($export_csv_title, "項目");
            for ($intL = 0; $intL < count($ledgerSheetDateColumns); $intL ++){
                $strDate = substr($ledgerSheetDateColumns[$intL], 0, 4).'年'.intval(substr($ledgerSheetDateColumns[$intL], 4, 2)).'月';
                array_push($export_csv_title, $strDate);
            }
            
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

                foreach( $ledgerSheetDetailList as $rows ){
                    
                    // 指定期間内で在庫数に変化がない商品は除く
                    if ($rows['delete_kbn'] === '1'){
                        continue;
                    }

                    for ($row_cnt = 1; $row_cnt <= 6; $row_cnt ++){

                        // 垂直ヘッダ
                        //$list_no += 1;
                        //$str = '"'.$list_no;// No
                        if ($row_cnt === 1){
                            $list_no ++;
                        }
                        //$str = $list_no;
                        $str = "";

                        $str = $str.'"'.$rows['abbreviated_name'].'"';
                        $str = $str.',"'.$rows['prod_cd'].'"';
                        $str = $str.',"'.$rows['prod_nm'].'"';

                        switch ($row_cnt){
                            case 1:
                                $strTitle = '仕入数';
                                $strField = 'supp_amount';
                                break;
                            case 2:
                                $strTitle = '販売数';
                                $strField = 'sale_amount';
                                break;
                            case 3:
                                $strTitle = '出庫数';
                                $strField = 'out_amount';
                                break;
                            case 4:
                                $strTitle = '入庫数';
                                $strField = 'in_amount';
                                break;
                            case 5:
                                $strTitle = '廃棄数';
                                $strField = 'disposal_amount';
                                break;
                            case 6:
                                $strTitle = '在庫数';
                                $strField = 'stock_amount';
                                break;
                            default:
                                $strTitle = '';
                                $strField = '';
                                break;
                        }
                        $str = $str.',"'.$strTitle.'"';
                        for ($intL = 0; $intL < count($ledgerSheetDateColumns); $intL ++){
                            $str = $str.','.$rows[$strField.strval($intL)];
                        }

                        // 配列に変換
                        $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
                        //$export_arr = explode(",", $str);

                        // 内容行を1行ごとにCSVデータへ
                        //$file->fputcsv($export_arr);
                        $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");
                    }
                }
           }
 
            // ダウンロード用
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers 
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=在庫推移表".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
    }
?>
