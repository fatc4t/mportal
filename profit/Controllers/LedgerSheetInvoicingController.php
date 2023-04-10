<?php
    /**
     * @file      請求書 [M]
     * @author    川橋
     * @date      2020/02/13
     * @version   1.00
     * @note      帳票 - 請求書の閲覧を行う
     */
    set_time_limit(180);

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetInvoicing.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetInvoicingController extends BaseController
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

            $LedgerSheetInvoicing = new LedgerSheetInvoicing();
            $modal = new Modal();
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            $cust_detail    = [];
            $cust_detail    = $modal->getcust_detail();
            $param          = [];
            //print_r($_POST);
            if($_POST){
                $_POST['csv_data'] = '';
                $param = $_POST;
            }
            $param["cust_cd"]   = "";
            $param["org_id"]    = "";

            $searchArray = [];

            // 日付リストを取得
            $startDate = parent::escStr( $_POST['start_date'] );

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
            if($_POST['org_r'] === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
                }
            }

            $cust_cd = 'false';
            if($_POST['cust_r'] === ""){
                if($_POST['cust_select'] && $_POST['cust_select'] === 'empty'){
                    $cust_cd = $_POST['cust_cd'];
                }else{
                    $cust_cd = "'".$_POST['cust_select']."'";
                }
            }
            if($_POST['sort']){
                $sort = $_POST['sort'] ;
            }else{
                $sort = 'prod_t_cd1#1';
            }

            $wellset_date = parent::escStr($_POST['wellset_date']);

            $wellset_date_list = $LedgerSheetInvoicing->getWellsetDateList();
            for ($i = 0; $i < count($wellset_date_list); $i ++){
                $wellset_date_list[$i] = substr($wellset_date_list[$i], 0, 4).'/'.substr($wellset_date_list[$i], 4, 2).'/'.substr($wellset_date_list[$i], 6, 2);
            }

            $data = array();
            if($_POST){
                $data = $LedgerSheetInvoicing->getFormListData($_POST);
            }

            // 検索組織
            $searchArray = $_POST;
            $searchArray['start_date'] = $startDate;
            $searchArray['end_date']   = $endDate;
            $searchArray['org_id']     = str_replace("'","",$_POST['org_id']);
            $searchArray['cust_cd']    = str_replace("'","",$_POST['cust_cd']);
            $searchArray['sort']       = $sort;

            $checked = "";
            if($_POST['tenpo']){
                $searchArray['tenpo'] = true;
            }

            $searchArray['Purchase_payment_checked'] = 'checked';   //買上または入金があるものを表示
            $searchArray['Claimable_checked'] = 'checked';          //請求額があるものを表示
            $searchArray['Copy_checked'] = 'checked';               //控えを印刷する
            $searchArray['Title_checked'] = 'checked';              //タイトルを印字する
            $searchArray['Name_checked'] = 'checked';               //社名・振込先を印刷する
            if ($mode !== 'initial'){
                $searchArray['Purchase_payment_checked'] = isset($_POST['chk_Purchase_payment']) ? 'checked' : '';
                $searchArray['Claimable_checked'] = isset($_POST['chk_Claimable']) ? 'checked' : '';
            }

            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetInvoicingPanel.html';
            }
        }

        /**
         * PDF出力
         * @return    なし
         */
        public function pdfoutputAction()
        {

            $page = 31;
            
            $postArray = $_POST;
            $postArray['action'] = 'pdfoutput';

            // タイトル表示するかどうか
            if (isset($postArray['chk_Title']) === true){
                $page--; //1行分マイナス
            }
            
            // 社名表示するかどうか
            if (isset($postArray['chk_Name']) === true){
                $page--; //1行分マイナス
            }

            define("LINES_PER_PAGE", $page);
            define("REPORT_TITLE", "御　請　求　書");
            define("REPORT_TITLE_COPY", REPORT_TITLE."　(控)");
            global $Log; // グローバル変数宣言
            
            $Log->trace("START pdfoutputAction");

            $LedgerSheetInvoicing = new LedgerSheetInvoicing();
            
            // 店舗選択
            $organizationId = 'false';
            if($_POST['org_r'] === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
                }
            }

            if($_POST['sort']){
                $sort = $_POST['sort'] ;
            }else{
                $sort = 'prod_t_cd1#1';
            }

            // 締処理日
            $wellset_date = parent::escStr($_POST['wellset_date']);

            // システムマスタ(mst0010)取得
            $sysInfo = $LedgerSheetInvoicing->getSysInfo();
            // 備考
            $tmpArray = explode("& vbCrLf &", $sysInfo['invoice_biko']);
            $sysInfo['invoice_biko_up']     = $tmpArray[0];
            $sysInfo['invoice_biko_down']   = $tmpArray[1];

            // 標準消費税率取得
            $dmlStaTaxRate = $this->getTaxRate($sysInfo) * 100;

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf = new mPDF(
                    'ja+aCJK',  //mode
                    'A4',       //paper size
                    0,          //font size
                    '',         //font family
                    5,          //margin-left
                    5,          //margin-right
                    6,          //margin-top
                    6,          //margin-bottom
                    0,          //margin-header
                    0,          //margin-footer
                    'P');       //orientation(Portrait)
            

            // 控html格納配列初期化
            $html_Copy = array();

            // データ取得
            for ($intL = 0; $intL < count($postArray['chk_cust_cd']); $intL ++){
                $postArray['cust_cd'] = $postArray['chk_cust_cd'][$intL];
                
                // ヘッダー情報(顧客情報・会社情報・その他)
                $headList = $LedgerSheetInvoicing->getFormListData($postArray);
                $head = $headList[0];
                //　発行年月日
                $Issue_date = date("Y/m/d ");

                // 締処理日
                $wellset_date_full = substr($head['wellset_date'],0,4).'年'.substr($head['wellset_date'],4,2).'月'.substr($head['wellset_date'],6,2).'日締分';
                $wellset_date = substr($head['wellset_date'],6,2);
                // 請求No
                if (isset($head['bill_no']) === true){
                    // 8桁左ゼロ埋め
                    $head['bill_no'] = '請求No：'.sprintf('%08d', $head['bill_no']);
                }
                
                // ヘッダーワーク用データテーブル
                $tblTaxWork = array();
                $dr = array(
                    'sales_tax_rate'    =>  -1,
                    'sales_tax_nm'      => '',
                    'sales_tax'         => 0
                );
                for ($intJ = 0; $intJ < 5; $intJ ++) {
                    array_push($tblTaxWork, $dr);
                }
                // 初期化(掛売が全くない場合、また非課税品代のみの売上の場合に備え)
                $tblTaxWork[0]['sales_tax_rate']    = 99999;    // ヘッダー用税率(標準税率扱い)
                $tblTaxWork[0]['sales_tax_nm']      = '外税額';
                $tblTaxWork[0]['sales_tax']         = 0;
                
                // 顧客掛売取引税別明細取得
                $dtbTableTax = $LedgerSheetInvoicing->getJSK4170DataList($postArray);
                if (count($dtbTableTax) > 5){
                    // 初期化(1番目:標準税率)
                    $tblTaxWork[0]['sales_tax_rate']    = 99999;    // ヘッダー用税率(標準税率扱い)
                    $tblTaxWork[0]['sales_tax_nm']      = '外税額<br >(標準税率)';
                    $tblTaxWork[0]['sales_tax']         = 0;
                    // 初期化(2番目:軽減税率)
                    $tblTaxWork[1]['sales_tax_rate']    = 999999;   // ヘッダー用税率(軽減税率扱い)
                    $tblTaxWork[1]['sales_tax_nm']      = '外税額<br >(軽減税率)';
                    $tblTaxWork[1]['sales_tax']         = 0;
                    
                    foreach ($dtbTableTax as $drwRowTax){
                        // 税率
                        $dmlTaxRate = floatval($drwRowTax['TAX_RATE']);
                        if ($dmlTaxRate === $dmlStaTaxRate){
                            // 標準税率
                            $tblTaxWork[0]['sales_tax'] += floatval($drwRowTax['sales_tax']);
                        }
                        else{
                            // 軽減税率
                            $tblTaxWork[1]['sales_tax'] += floatval($drwRowTax['sales_tax']);
                        }
                    }
                }
                else if (count($dtbTableTax) > 0){
                    for ($intJ = 0; $intJ < count($dtbTableTax); $intJ ++){
                        // 税率
                        $dmlTaxRate = floatval($dtbTableTax[$intJ]['tax_rate']);
                        $dmlTmp = $dmlTaxRate - floor($dmlTaxRate);
                        $dmlTmp *= 100;
                        
                        $strTmp = '外税額';
                        
                        if ($dmlTmp === 0.0){
                            // 小数点なし
                            $strTmp .= '('.number_format($dmlTaxRate).'%)';
                        }
                        else{
                            if ($dmlTmp % 10 === 0.0){
                                // 小数点あり(1桁)
                                $strTmp .= '('.number_format($dmlTaxRate,1,'.','').'%)';
                            }
                            else{
                                // 小数点あり(2桁)
                                $strTmp .= '('.number_format($dmlTaxRate,2,'.','').'%)';
                            }
                        }
                        $tblTaxWork[$intJ]['sales_tax_rate']    = $dmlTaxRate;
                        $tblTaxWork[$intJ]['sales_tax_nm']      = $strTmp;
                        $tblTaxWork[$intJ]['sales_tax']         = floatval($dtbTableTax[$intJ]['sales_tax']);
                    }
                }
                
                // 明細情報
                $detailList = $LedgerSheetInvoicing->getDetailDataList($postArray);
//ADDSTR 2020/02/28 川橋
                // 消費税額を内・外で分ける
                $detailList_Tmp = array();
                foreach ($detailList as $detail){
                    if ($detail['account_kbn'] === '3'){
                        // 伝票別税区分別税額取得
                        $postArray['organization_id'] = $detail['organization_id'];
                        $postArray['account_no'] = $detail['account_no'];
                        $dtbTableTaxMei = $LedgerSheetInvoicing->getDetailTax($postArray);
                        if (count($dtbTableTaxMei) === 0){
                            array_push($detailList_Tmp, $detail);
                            continue;
                        }
                        foreach ($dtbTableTaxMei as $taxMei){
                            // 税区分上書き
                            $detail['tax_type'] = $taxMei['tax_type'];
                            // 伝票-売上金額上書き
                            $detail['den_pure_total'] = floatval($taxMei['sales_total']);
                            // 外税の場合
                            if ($taxMei['tax_type'] === '1'){
                                $detail['den_pure_total'] += floatval($taxMei['sales_tax']);
                            }
                            // 消費税額上書き
                            $detail['den_tax'] = floatval($taxMei['sales_tax']);
                            array_push($detailList_Tmp, $detail);
                        }
                    }
                    else{
                        array_push($detailList_Tmp, $detail);
                    }
                }
                $detailList = $detailList_Tmp;
//ADDEND 2020/02/28 川橋
                // (顧客毎)総ページ数
//EDTSTR 2020/02/28 川橋
                // 先頭の前回繰越額行と末尾のサマリ３行分を足して計算する※ただし$page_totalは現在未使用
                //$page_total = ceil(count($detailList) / LINES_PER_PAGE);
                $page_total = ceil((count($detailList) + 4) / LINES_PER_PAGE);
//EDTEND 2020/02/28 川橋
                // 明細0件でも明細部に空行を出力するため1件としておく
                if ($page_total === 0.0){
                    $page_total = 1;
                }

                // ヘッダ部html
                $html_hd   = '<!DOCTYPE html>';
                $html_hd  .= '<html>';
                $html_hd  .= '<head>';
                $html_hd  .= '<meta charset="utf-8" />';
                $html_hd  .= '<title>請求書</title>';
                $html_hd  .= '<style type="text/css">';
                $html_hd  .= '    html, body {width:100%; height:100%;}';
                $html_hd  .= '    table {';
                $html_hd  .= '        border-collapse:collapse;';
                $html_hd  .= '        font-size:11px;';
                $html_hd  .= '        table-layout: fixed;';
                $html_hd  .= '    }';
                // 幅コントロールとして追加 20200313 oota start
                $html_hd  .= '    .page-header-title-td {';
                $html_hd  .= '        border:1px solid black;';
                $html_hd  .= '        width: 130px;';
                $html_hd  .= '        height: 20px;';
                $html_hd  .= '    }';
                // 幅コントロールとして追加 20200313 oota end
                $html_hd  .= '    .page-header-summary-td {';
                $html_hd  .= '        border:1px solid black;';
                $html_hd  .= '        width: 130px;';
                $html_hd  .= '        height: 30px;';
                $html_hd  .= '    }';
                $html_hd  .= '    .L {text-align: left;}';
                $html_hd  .= '    .C {text-align: center;}';
                $html_hd  .= '    .R {text-align: right; padding-right: 5px;}';
                $html_hd  .= '    .PadR_5 {padding-right: 5px;}';
                $html_hd  .= '    .PadL_5 {padding-left: 5px;}';
                $html_hd  .= '    .PadL_25 {padding-left: 25px;}';
                $html_hd  .= '    .PadL_40 {padding-left: 40px;}';
                $html_hd  .= '    #detail td {';
                $html_hd  .= '        border-left: 1px solid black;';
                $html_hd  .= '        border-right: 1px solid black;';
                $html_hd  .= '        height: 22px;';
                $html_hd  .= '        font-size: 12px;';
                $html_hd  .= '        padding-top:0px;';
                $html_hd  .= '        padding-bottom:0px;';
                $html_hd  .= '    }';
                $html_hd  .= '</style>';
                $html_hd  .= '</head>';
                $html_hd  .= '<body>';
                $html_hd  .= '    <header>';
                $html_hd  .= '        <table style="width:100%;">';
                $html_hd  .= '            <tr>';
                $html_hd  .= '                <td style="width100px;">&nbsp;</td>';
                $html_hd  .= '                <td style="text-align:center">';
                
                // タイトルを表示するか
                if (isset($postArray['chk_Title']) === true){
                    $html_hd  .= '                    <span style="font-size:28px;">'.REPORT_TITLE.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><br />';
                    $html_hd  .= '                    <span style="font-size:14px;">('.$wellset_date_full.')&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
                }
                $html_hd  .= '                </td>';
                $html_hd  .= '            </tr>';
                $html_hd  .= '        </table>';
                
                //顧客住所ブロック
                $html_hd  .= '        <div id="page-header">';
                $html_hd  .= '            <table style="width: 100%;">';
                $html_hd  .= '                <tr>';
                $html_hd  .= '                    <td style="width:65%; vertical-align:bottom padding-bottom:5px;">';
                $html_hd  .= '                        <table>';
                $html_hd  .= '                            <tr>';
                $html_hd  .= '                                <td>';
                $html_hd  .= '                                    <span style="font-size:18px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;〒'.$head['zip'].'</span>';
                $html_hd  .= '                                </td>';
                $html_hd  .= '                            </tr>';
                $html_hd  .= '                            <tr>';
                $html_hd  .= '                                <td class="PadL_5">';
                $html_hd  .= '                                    <span style="font-size:18px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$head['addr1'].'<br />'.$head['addr2'].'</span>';
                $html_hd  .= '                                </td>';
                $html_hd  .= '                            </tr>';
                $html_hd  .= '                        </table>';
                $html_hd  .= '                    </td>';

                //社名ブロック
                $html_hd  .= '                    <td style="vertical-align:bottom;">';
                $html_hd  .= '                        <table style="width:100%; border-collapse:collapse;">';
                $html_hd  .= '                            <tr>';
                $html_hd  .= '                                <td colspan="3">';
                // 社名を表示するか
                if (isset($postArray['chk_Name']) === true){
                    $html_hd  .= '                                    <font style="font-size:22px;">'.$sysInfo['comp_nm'].'</font>';
                }else{
                    $html_hd  .= '                                    <font style="font-size:22px;">　</font>';
                }
                $html_hd  .= '                                </td>';
                $html_hd  .= '                            </tr>';
                $html_hd  .= '                            <tr>';
                $html_hd  .= '                                <td colspan="3" class="PadL_25">';
                // 社名を表示するか
                if (isset($postArray['chk_Name']) === true){
                    $html_hd  .= '                                    〒'.$sysInfo['zip'].'<br />'.$sysInfo['addr1'].'<br />'.$sysInfo['addr2'];
                }else{
                    $html_hd  .= '                                      　<br />　<br />　';
                }
                $html_hd  .= '                                </td>';
                $html_hd  .= '                            </tr>';
                $html_hd  .= '                            <tr>';
                $html_hd  .= '                                <td class="PadL_40" style="vertical-align:top;">';
                // 社名を表示するか
                if (isset($postArray['chk_Name']) === true){
                    $html_hd  .= '                                    (TEL)&nbsp;'.$sysInfo['tel'].'&nbsp;(FAX)&nbsp;'.$sysInfo['fax'];
                }else{
                    $html_hd  .= '                                    ';
                }
                $html_hd  .= '                                </td>';
                $html_hd  .= '                            </tr>';
                $html_hd  .= '                            <tr>';
                $html_hd  .= '                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>';
                $html_hd  .= '                            </tr>';
                $html_hd  .= '                            <tr>';
                $html_hd  .= '                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>';
                $html_hd  .= '                            </tr>';
                $html_hd  .= '                            <tr>';
                $html_hd  .= '                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>';
                $html_hd  .= '                            </tr>';
                $html_hd  .= '                            <tr>';
                $html_hd  .= '                                <td colspan="3" >';
                // 社名を表示するか
                if (isset($postArray['chk_Name']) === true){
                    $html_hd  .= '                                    '.$sysInfo['invoice_biko_up'];
                }else{
                    $html_hd  .= '                                    ';
                }
                $html_hd  .= '                                </td>';
                $html_hd  .= '                            </tr>';
                $html_hd  .= '                            <tr>';
                $html_hd  .= '                                <td colspan="3" >';
                // 社名を表示するか
                if (isset($postArray['chk_Name']) === true){
                    $html_hd  .= '                                    '.$sysInfo['invoice_biko_down'];
                }else{
                    $html_hd  .= '                                    ';
                }
                $html_hd  .= '                                </td>';
                $html_hd  .= '                            </tr>';
                $html_hd  .= '                        </table>';
                $html_hd  .= '                    </td>';
                $html_hd  .= '                </tr>';
                $html_hd  .= '                <tr>';
  
                // 顧客名
                $html_hd  .= '                    <td class="PadL_5" colspan="2">';
                
                // 顧客名を表示幅35バイトで改行するように対応 20200313 oota
                if(mb_strwidth($head['cust_nm'],"UTF-8") > 35){
                    $str1 = mb_substr($head['cust_nm'],0,13,"UTF-8");
                    $str2 = mb_substr($head['cust_nm'],13,12,"UTF-8");
                    $html_hd  .= '                        <span style="font-size:18px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$str1.'</span><br>';
                    $html_hd  .= '                        <span style="font-size:18px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$str2.'&nbsp;様</span><br />';
                }else{
                    $html_hd  .= '                        <span style="font-size:18px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$head['cust_nm'].'&nbsp;様</span><br />';
                    $html_hd  .= '                        <span style="font-size:18px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><br />';
                }
                
                $html_hd  .= '                    </td>';
                $html_hd  .= '                </tr>';
                $html_hd  .= '                <tr>';
                $html_hd  .= '                    <td>';
                $html_hd  .= '                      &nbsp;';
                $html_hd  .= '                    </td>';
                $html_hd  .= '                </tr>';
                $html_hd  .= '            </table>';
                $html_hd  .= '            <div style="margin-top:10px;">';
                // 発行情報
                $html_hd  .= '            <table id="page-header-summary" style="">';
                $html_hd  .= '                <tr>';
                $html_hd  .= '                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
                $html_hd  .= '                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
                $html_hd  .= '                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
                $html_hd  .= '                    <td class="page-header-title-td C"style="background-color: #C7C7C7">発行年月日</td>';
                $html_hd  .= '                    <td class="page-header-title-td C"style="background-color: #C7C7C7">顧客コード</td>';
                $html_hd  .= '                    <td class="page-header-title-td C"style="background-color: #C7C7C7">締日</td>';
                $html_hd  .= '                    <td class="page-header-title-td C"style="background-color: #C7C7C7">ページNo</td>';
                $html_hd  .= '                </tr>';
                $html_hd  .= '                <tr>';
                $html_hd  .= '                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
                $html_hd  .= '                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
                $html_hd  .= '                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
                $html_hd  .= '                    <td class="page-header-title-td R PadR_5"style="text-align:center">'.$Issue_date.'</td>';
                // 入金額
                $html_hd  .= '                    <td class="page-header-title-td R PadR_5" style="text-align:center">'.$head['cust_cd'].'</td>';
                $html_hd  .= '                    <td class="page-header-title-td R PadR_5" style="text-align:center">'.$wellset_date.'</td>';
                $html_hd  .= '                    <td class="page-header-title-td R PadR_5" style="text-align:center">'.'%PAGE%&nbsp;'.'</td>';
                $html_hd  .= '                </tr>';
                $html_hd  .= '            </table>';
                $html_hd  .= '            </div>';
//                $html_hd  .= '            <table id="page-header-summary" style="margin-top:10px;">';フォントサイズ変更、穴あき用対応 マージン追加 20200313 oota
                $html_hd  .= '            <table id="page-header-summary" style="margin-top:10px;font-size:15px;margin-left: 30px;">';
                $html_hd  .= '                <tr>';
                $html_hd  .= '                    <td class="page-header-summary-td C"style="background-color: #C7C7C7">前回御請求額</td>';
                $html_hd  .= '                    <td class="page-header-summary-td C"style="background-color: #C7C7C7">今回御入金額</td>';
                $html_hd  .= '                    <td class="page-header-summary-td C"style="background-color: #C7C7C7">繰　越　額</td>';
                $html_hd  .= '                    <td class="page-header-summary-td C"style="background-color: #C7C7C7">今回御買上額</td>';
                //外税額</td>';
                for ($intJ = 0; $intJ < 5; $intJ ++){
                    if ($tblTaxWork[$intJ]['sales_tax_rate'] >= 0){
                        $html_hd  .= '                    <td class="page-header-summary-td C"style="background-color: #C7C7C7">'.$tblTaxWork[$intJ]['sales_tax_nm'].'</td>';
                    }
                }
                $html_hd  .= '                    <td class="page-header-summary-td C"style="background-color: #C7C7C7">今回取引額</td>';
                $html_hd  .= '                    <td class="page-header-summary-td C" style="color: #FAFAFA;background-color: #333333">今回御請求額<br>(税込)</td>';
                $html_hd  .= '                </tr>';
                $html_hd  .= '                <tr>';
//EDTSTR 2020/02/25 川橋
//2ページ目以降は金額を***表示

                $html_hd  .= '                    <td class="page-header-summary-td R PadR_5">%BEF_BALANCE%</td>';  // 前回御請求額
                $html_hd  .= '                    <td class="page-header-summary-td R PadR_5">%NYUKIN_TOTAL%</td>'; // 今回御入金額
                $html_hd  .= '                    <td class="page-header-summary-td R PadR_5">%BEF_BALANCE2%</td>'; // 繰　越　額
                $html_hd  .= '                    <td class="page-header-summary-td R PadR_5">%SALE_TOTAL%</td>';   // 今回御買上額
                for ($intJ = 0; $intJ < 5; $intJ ++){
                    if ($tblTaxWork[$intJ]['sales_tax_rate'] >= 0){
                        $html_hd  .= '                    <td class="page-header-summary-td R PadR_5">%SALES_TAX'.strval($intJ + 1).'%</td>'; // 税額
                    }
                }
                $html_hd  .= '                    <td class="page-header-summary-td R PadR_5">%TRANSACTION_AMOUNT%</td>';   // 今回取引額
                $html_hd  .= '                    <td class="page-header-summary-td R PadR_5" style="">%NOW_BALANCE%</td>'; // 今回御請求額(税込)
//EDTEND 2020/02/25 川橋
                $html_hd  .= '                </tr>';
                $html_hd  .= '            </table>';
                $html_hd  .= '        </div>';
                $html_hd  .= '    </header>';
                // 明細タイトル
                $html_hd  .= '    <main>';
//                $html_hd  .= '        <div id="detail" style="margin-top:10px;">'; 穴あき用対応 マージンを追加 20200313 oota
                $html_hd  .= '        <div id="detail" style="margin-top:10px;margin-left: 25px;">';
                $html_hd  .= '        <table style="width: 100%;">';
                $html_hd  .= '            <tr>';
                $html_hd  .= '                <td class="C" style="width:80px; border-top: 1px solid black; border-bottom: 1px solid black;">年月日</td>';
                $html_hd  .= '                <td class="C" style="width:30px; border-top: 1px solid black; border-bottom: 1px solid black;">店</td>';
                $html_hd  .= '                <td class="C" style="width:60px; border-top: 1px solid black; border-bottom: 1px solid black;">伝票No</td>';
                $html_hd  .= '                <td class="C" style="width:200px; border-top: 1px solid black; border-bottom: 1px solid black;">商品名</td>';
                $html_hd  .= '                <td class="C" style="width:50px; border-top: 1px solid black; border-bottom: 1px solid black;">数量</td>';
                $html_hd  .= '                <td class="C" style="width:70px; border-top: 1px solid black; border-bottom: 1px solid black;">単価</td>';
                $html_hd  .= '                <td class="C" style="width:80px; border-top: 1px solid black; border-bottom: 1px solid black;">金額</td>';
                $html_hd  .= '                <td class="C" style="width:80px; border-top: 1px solid black; border-bottom: 1px solid black;">入金額</td>';
                $html_hd  .= '                <td class="C PadL_5" style="border-top: 1px solid black; border-bottom: 1px solid black;">税金／摘要</td>';
                $html_hd  .= '            </tr>';

                // フッタ部html
                $html_ft   = '            <tr>';
                $html_ft  .= '                <td colspan="9" style="border-top: 1px solid black; height:1px;"></td>';
                $html_ft  .= '            </tr>';
                $html_ft  .= '        </table>';
                $html_ft  .= '        </div>';
                $html_ft  .= '    </main>';
                $html_ft  .= '    <footer>';
                $html_ft  .= '    </footer>';
                $html_ft  .= '</body>';
                $html_ft  .= '</html>';

                // ヘッダ部
                $page   = 1;
                $html   = $this->setHeaderValue($page, $html_hd, $head, $tblTaxWork);
                
                // 明細なし
                if (count($detailList) === 0){
                    // 空白行追加
                    for ($i = 0; $i < LINES_PER_PAGE; $i ++){
                        $html  .= '            <tr>';
                        $html  .= '                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>';
                        $html  .= '            </tr>';
                    }
                    // フッタ部
                    $html  .= $html_ft;

                    $mpdf->WriteHTML($html);
                    // 控
                    if (isset($postArray['chk_Copy']) === true){
                        // タイトルを"(控)"付きに置換してpdf出力用配列に格納
                        array_push($html_Copy, str_replace(REPORT_TITLE, REPORT_TITLE_COPY,$html));
                    }
                }
//ADDSTR 2020/02/25 川橋
                else{
                    $html  .= '            <tr>';
                    $html  .= '                <td></td><td></td><td></td><td>＊＊　前回繰越額　＊＊</td><td></td><td></td><td class="R">'.number_format(floatval($head["bef_balance"]) - $dmlReceTotal).'</td><td></td><td></td>';
                    $html  .= '            </tr>';
                }
//ADDEND 2020/02/25 川橋
                
                // 明細あり
//EDTSTR 2020/02/25 川橋
// 前回繰越額行追加に伴う修正
                $count  = 1;        // (ページ毎)出力行数
//EDTEND 2020/02/25 川橋
                $detailRow = 0;             // 明細処理行数
                $trndate_save = '';         // 同日判定用
                $shop_cd_save = '';         // 同ID判定用
                $sale_hideseq_save = '';    // 同伝票No判定用

//ADDSTR 2020/02/25 川橋
                $dmlSUM_DEN_PURE_TOTAL = 0;     // 伝票-売上金額(合計)
                $dmlSUM_DEN_TAX = 0;            // 伝票-外税額(合計)
                $dmlSUM_DISCTOTAL = 0;          // 明細値引金額(総合計)
                $dmlSUM_DENTOTAL = 0;           // 明細値引金額(伝票合計)
//ADDEND 2020/02/25 川橋
                
                foreach ($detailList as $detail){
                    // 変数初期化
                    $strPROD_KN = '';
                    $strAMOUNT = '';
                    $strSALEPRICE = '';
                    $strPURE_TOTAL = '';
                    $strCOMMENT = '';
//ADDSTR 2020/02/25 川橋
                    $dmlSUM_DEN_PURE_TOTAL += floatval($detail['den_pure_total']);
                    $dmlSUM_DISCTOTAL += floatval($detail['disctotal']);
                    $dmlSUM_DENTOTAL += floatval($detail['disctotal']);
//ADDEND 2020/02/25 川橋
                    
                    // 区分 売上
                    if ($detail['account_kbn'] === '0'){
                        // 売上
                        $strPROD_KN = '';
                        // 商品名優先表示区分はひとまず保留
                        if ($detail['prod_cd'] !== ''){
                            $strPROD_KN = $detail['prod_nm'];
                            if ($strPROD_KN === ''){
                                $strPROD_KN = $detail['prod_kn'];
                            }
                            if ($strPROD_KN === ''){
                                $strPROD_KN = $detail['sect_nm'];
                            }
                            if ($strPROD_KN === ''){
                                $strPROD_KN = $detail['sect_kn'];
                            }
                        }
                        else if ($detail['sect_cd'] !== ''){
                            $strPROD_KN = $detail['sect_nm'];
                            if ($strPROD_KN === ''){
                                $strPROD_KN = $detail['sect_kn'];
                            }

                        }

                        $strCOMMENT = '';
                        // 外税･内税であり、標準税率と異なる場合
                        if (($detail['tax_type'] === '1' || $detail['tax_type'] === '2') && floatval($detail['tax_rate']) !== $dmlStaTaxRate){
                            $dmlTaxRate = floatval($detail['tax_rate']);
                            $dmlTmp = $dmlTaxRate - floor($dmlTaxRate);
                            $dmlTmp *= 100;
                            $strTmp = '';
                            if ($dmlTmp === 0.0){
                                // 小数点なし
                                $strTmp .= '軽減税率('.number_format($dmlTaxRate).'%)';
                            }
                            else{
                                if ($dmlTmp % 10 === 0.0){
                                    // 小数点あり(1桁)
                                    $strTmp .= '軽減税率('.number_format($dmlTaxRate,1,'.','').'%)';
                                }
                                else{
                                    // 小数点あり(2桁)
                                    $strTmp .= '軽減税率('.number_format($dmlTaxRate,2,'.','').'%)';
                                }
                            }
                            $strCOMMENT = $strTmp;
                        }

                        $strAMOUNT = number_format($detail['amount']);          //数量
                        $strSALEPRICE = number_format($detail['saleprice']);    //単価
//                        $strPURE_TOTAL = number_format($detail['pure_total'] + $detail['disctotal']);  //金額
                        $strPURE_TOTAL = number_format($detail['pure_total'] + $detail['disctotal']);  //金額 ※値引を引かないように対応 20200313 oota
                    }
                    // 区分 部分入金
                    else if ($detail['account_kbn'] === '1'){
                        // 取引時入金
                        $strPROD_KN = '部分入金';
                        $strAMOUNT = '';
                        $strSALEPRICE = '';
                        $strPURE_TOTAL = '';
                        $strCOMMENT = '';
                    }
                    // 区分 掛入金
                    else if ($detail['account_kbn'] === '2'){
                        // 入金
                        switch ($detail['rece_kbn']){
                            case 0:
                                $strPROD_KN = '現金　入金';
                                break;
                            case 1:
                                $strPROD_KN = '振込　入金';
                                break;
                            case 2:
                                $strPROD_KN = '小切手　入金';
                                break;
                            case 3:
                                $strPROD_KN = '手形　入金';
                                break;
                            case 5:
                                $strPROD_KN = '相殺　入金';
                                break;
                            case 8:
                                $strPROD_KN = '値引　入金';
                                break;
                            case 9:
                                $strPROD_KN = 'その他　入金';
                                break;
                            default:
                                $strPROD_KN = '入金';
                                break;
                        }
                        $strAMOUNT = '';
                        $strSALEPRICE = '';
                        $strPURE_TOTAL = '';
                        $strCOMMENT = '';
                    }
                    // 区分 伝票合計
                    else if ($detail['account_kbn'] === '3'){
                    // 伝票純売上・内消費税(外税+内税)
                    // ※値引がある場合は伝票計の前に値引行を入れる
	            //    if($dmlSUM_DENTOTAL > 0){ 値引きように修正 kanderu 2021/01/05
		        if($dmlSUM_DENTOTAL <> 0){
                            $html  .= '            <tr>';
                            $html  .= '                <td></td><td></td><td></td><td class="L">値引</td><td></td><td></td><td class="R">'.number_format($dmlSUM_DENTOTAL).'</td><td></td><td></td>';
                            $html  .= '            </tr>';
                            $dmlSUM_DENTOTAL = 0; //伝票単位で表示したあとは0クリア
                            // 20200803 合計行数がずれる分に対応 oota START 
                            $count ++;
                            // 20200803 oota END
                        }
//EDTSTR 2020/02/28 川橋
//                        $strPROD_KN = '税　金';
                        if ($detail['tax_type'] === '9'){
                            $strPROD_KN = '税　金　(非課税)';
                        }else if ($detail['tax_type'] === '2'){
                            $strPROD_KN = '税　金　(内税)';
                        }
                        else{
                            $strPROD_KN = '税　金';
                        }
//EDTEND 2020/02/28 川橋
                        $strAMOUNT = '';
                        $strSALEPRICE = '';
                        $strPURE_TOTAL = '';
//税額の括弧表示は内税のみ表示するように修正 20200313 oota start
                        if($detail['tax_type'] === '2'){
                            // 内税の場合
                            $strCOMMENT = '<table style="width:100%; margin:0px;"><tr><td style="width:8px; padding:0px; border:none;">&nbsp;(</td><td style="padding:0px; border:none;">'.number_format($detail['den_tax']).'</td><td style="padding:0px; width:8px; border:none;">)</td></tr></table>';
                            // 内税の合計計算に追加
                            $dmlSUM_DEN_I_TAX += floatval($detail['den_tax']);
                        }else{
                            // それ以外
                            $strCOMMENT = '<table style="width:100%; margin:0px;"><tr><td style="width:8px; padding:0px; border:none;">&nbsp;</td><td style="padding:0px; border:none;">'.number_format($detail['den_tax']).'</td><td style="padding:0px; width:8px; border:none;"></td></tr></table>';
                            // 外税の合計計算に追加
                            $dmlSUM_DEN_U_TAX += floatval($detail['den_tax']);
                        }
                        
//税額の括弧表示は内税のみ表示するように修正 20200313 oota end

                        }
                    // 長さ調整
                    $strPROD_KN = mb_strcut($strPROD_KN,0,50,'UTF-8');
                    
                    // 入金
                    if (floatval($detail['nyukin']) === 0.0){
                        $strNYUKIN = '';
                    }
                    else{
                        $strNYUKIN = number_format($detail['nyukin']);
                    }
                    $html  .= '            <tr>';
                    // 年月日 
                    if ($detail['trndate'] === $trndate_save){
                             $html  .= '                <td class="L"></td>';
                    }else{
	                    $html  .= '                <td class="C">'.substr($detail['trndate'],0,4).'/'.substr($detail['trndate'],4,2).'/'.substr($detail['trndate'],6,2).'</td>';
                                    $trndate_save = $detail['trndate'];
                         }
                    // 店コード
                    if ($detail['shop_cd'] === $shop_cd_save){
                             $html  .= '                <td class="C"></td>';
                    }else{
                             $html  .= '                <td class="C">'.$detail['shop_cd'].'</td>';
                             $shop_cd_save = $detail['shop_cd'];
                         }
                    // 伝票No
                    if ($detail['sale_hideseq'] === $sale_hideseq_save){
                             $html  .= '                <td class="C"></td>';
                    }else{
//                             $html  .= '                <td class="C">'.substr( $detail['sale_hideseq'] , 2 , strlen($detail['sale_hideseq']) -2 ).'</td>';
//                             $sale_hideseq_save = $detail['sale_hideseq'];
                             // hideseq → レジの伝票番号に変更 20200313 oota
                             $html  .= '                <td class="C">'.$detail['sale_hideseq'].'</td>';
                             $sale_hideseq_save = $detail['sale_hideseq'];
                    }
                    // 商品名
                    if ($detail['account_kbn'] === '1' || $detail['account_kbn'] === '2'){
                        $html  .= '                <td class="R">'.$strPROD_KN.'</td>';
                    }
                    else{
                        $html  .= '                <td class="L">'.$strPROD_KN.'</td>';
                    }
                    // 数量
                    $html  .= '                <td class="R">'.$strAMOUNT.'</td>';
                    // 単価
                    $html  .= '                <td class="R">'.$strSALEPRICE.'</td>';
                    // 金額
                    $html  .= '                <td class="R">'.$strPURE_TOTAL.'</td>';
                    // 入金額
                    $html  .= '                <td class="R">'.$strNYUKIN.'</td>';
                    // 税金/摘要
                    if ($detail['account_kbn'] === '3'){
                        $html  .= '                <td class="R">'.$strCOMMENT.'</td>';
                    }
                    else{
                        $html  .= '                <td class="L">'.$strCOMMENT.'</td>';
                    }
                    $html  .= '            </tr>';

                    $count ++;
                    if($count >= LINES_PER_PAGE || $detailRow + 1 >= count($detailList)){
//EDTSTR 2020/02/25 川橋

                        // サマリ行(売上合計・値引合計・税額合計)追加
                        $add_sum_lines = 0;
                        if ($count < LINES_PER_PAGE){
                            for ($i = $count; $i < LINES_PER_PAGE; $i ++){
                                $html  .= '            <tr>';
                                if ($i === $count){
//                                    $html  .= '                <td></td><td></td><td></td><td class="L">売上合計</td><td></td><td></td><td class="R">'.number_format($dmlSUM_DEN_PURE_TOTAL - $dmlSUM_DEN_TAX).'</td><td></td><td></td>'; // 計算式変更 20200313 oota
                                    if(number_format($dmlSUM_DEN_PURE_TOTAL-$dmlSUM_DEN_U_TAX) <> 0){
                                        $html  .= '                <td></td><td></td><td></td><td class="L">売上合計</td><td></td><td></td><td class="R">'.number_format($dmlSUM_DEN_PURE_TOTAL-$dmlSUM_DEN_U_TAX).'</td><td></td><td></td>';
                                    }
                                }
                                else if ($i === $count + 1){
                                    if(number_format($dmlSUM_DISCTOTAL) <> 0){
                                        $html  .= '                <td></td><td></td><td></td><td class="L">値引合計</td><td></td><td></td><td class="R">'.number_format($dmlSUM_DISCTOTAL).'</td><td></td><td></td>';
                                    }
                                }
                                else if ($i === $count + 2){
                                    if(number_format($dmlSUM_DEN_U_TAX) <> 0){
                                        $html  .= '                <td></td><td></td><td></td><td class="L">税額合計</td><td></td><td></td><td class="R">'.number_format($dmlSUM_DEN_U_TAX).'</td><td></td><td></td>';
                                        $dmlSUM_DEN_U_TAX = 0; //表示したあとは0クリア
                                    }
                                }
                                else if ($i === $count + 3){
                                    if(number_format($dmlSUM_DEN_I_TAX) <> 0){
                                        $html  .= '                <td></td><td></td><td></td><td class="L">税額合計（内税）</td><td></td><td></td><td class="R">('.number_format($dmlSUM_DEN_I_TAX).')</td><td></td><td></td>';
                                        $dmlSUM_DEN_I_TAX = 0; //表示したあとは0クリア
                                    }
                                }
                                $html  .= '            </tr>';
                                $add_sum_lines ++;
                                if ($add_sum_lines >= 4){
                                    break;
                                }
                            }
                            // 空白行追加
                            for ($i = $count + $add_sum_lines; $i < LINES_PER_PAGE; $i ++){
                                $html  .= '            <tr>';
                                $html  .= '                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>';
                                $html  .= '            </tr>';
                            }
                        }
//EDTEND 2020/02/25 川橋

                        $html  .= $html_ft;
                        $mpdf->WriteHTML($html);
                        // 控
                        if (isset($postArray['chk_Copy']) === true){
                            // タイトルを"(控)"付きに置換してpdf出力用配列に格納
                            array_push($html_Copy, str_replace(REPORT_TITLE, REPORT_TITLE_COPY,$html));
                        }

                        // 次明細あり
                        if($detailRow + 1 < count($detailList)){
                            $mpdf->AddPage();
                            $page ++;
                            $count = 0;
                            //$html   = str_replace('%PAGE%', number_format($page), $html_hd);
                            $html   = $this->setHeaderValue($page, $html_hd, $head, $tblTaxWork);

                        }
                        else{
//ADDSTR 2020/02/25 川橋
                            // 次明細はないがサマリ行が残っている
                            if ($add_sum_lines < 3){
                                $mpdf->AddPage();
                                $page ++;
                                $count = 0;
                                $html   = $this->setHeaderValue($page, $html_hd, $head, $tblTaxWork);

                                for ($i = $add_sum_lines; $i < 4; $i ++){
                                    $html  .= '            <tr>';
                                    if ($i === 0){
//                                        $html  .= '                <td></td><td></td><td></td><td class="L">売上合計</td><td></td><td></td><td class="R">'.number_format($dmlSUM_DEN_PURE_TOTAL).'</td><td></td><td></td>';// 計算式変更 20200313 oota
                                        if(number_format($dmlSUM_DEN_PURE_TOTAL-$dmlSUM_DEN_U_TAX) <> 0){
                                            $html  .= '                <td></td><td></td><td></td><td class="L">売上合計</td><td></td><td></td><td class="R">'.number_format($dmlSUM_DEN_PURE_TOTAL-$dmlSUM_DEN_U_TAX).'</td><td></td><td></td>';
                                        }
                                    }
                                    else if ($i === 1){
                                        if(number_format($dmlSUM_DISCTOTAL) <> 0){
                                            $html  .= '                <td></td><td></td><td></td><td class="L">値引合計</td><td></td><td></td><td class="R">'.number_format($dmlSUM_DISCTOTAL).'</td><td></td><td></td>';
                                        }
                                    }
                                    else if ($i === 2){
                                        if(number_format($dmlSUM_DEN_U_TAX) <> 0){
                                            $html  .= '                <td></td><td></td><td></td><td class="L">税額合計</td><td></td><td></td><td class="R">'.number_format($dmlSUM_DEN_U_TAX).'</td><td></td><td></td>';
                                            $dmlSUM_DEN_U_TAX = 0; //表示したあとは0クリア
                                        }
                                    }
                                    else if ($i === 3){
                                        if(number_format($dmlSUM_DEN_I_TAX) <> 0){
                                            $html  .= '                <td></td><td></td><td></td><td class="L">税額合計（内税）</td><td></td><td></td><td class="R">('.number_format($dmlSUM_DEN_I_TAX).')</td><td></td><td></td>';
                                            $dmlSUM_DEN_I_TAX = 0; //表示したあとは0クリア
                                        }
                                    }
                                    $html  .= '            </tr>';
                                    $count ++;
                                }
                                // 空白行追加
                                for ($i = $count; $i < LINES_PER_PAGE; $i ++){
                                    $html  .= '            <tr>';
                                    $html  .= '                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>';
                                    $html  .= '            </tr>';
                                }
                                $html  .= $html_ft;
                                $mpdf->WriteHTML($html);
                                // 控
                                if (isset($postArray['chk_Copy']) === true){
                                    // タイトルを"(控)"付きに置換してpdf出力用配列に格納
                                    array_push($html_Copy, str_replace(REPORT_TITLE, REPORT_TITLE_COPY,$html));
                                }
                            }
//ADDEND 2020/02/25 川橋
                            $html   = '';
                            break;
                        }
                    }
                    $detailRow ++;
                }

                // 控出力
                if (isset($postArray['chk_Copy']) === true){
                    $mpdf->AddPage();
                    for ($intJ = 0; $intJ < count($html_Copy); $intJ ++){
                        $mpdf->WriteHTML($html_Copy[$intJ]);
                        if ($intJ + 1 < count($html_Copy)){
                            $mpdf->AddPage();
                        }
                    }
                }
                // 控html格納配列初期化
                $html_Copy = array();
                
                // 次顧客あり
                if ($intL + 1 < count($_POST['chk_cust_cd'])){
                    $mpdf->AddPage();
                    $html   = '';
                }
            }

            $mpdf->Output();

            $Log->trace("END   pdfoutputAction");
        }
        
        /**
         * 消費税率取得
         * @note     消費税率を返す
         * @param    $sysInfo システムマスタ(mst0010)
         * @param    $strDate(YYYYMMDD)
         * @return   $dmlTaxRate("5%"の場合"0.05"を返す)
         */
        private function getTaxRate($sysInfo, $strDate = ""){
            
            global $Log;  // グローバル変数宣言
            $Log->trace("START getTaxRate");
            
            if ($strDate === ""){
                $strDate = date('Ymd');
            }
            
            //税率取得
            $dmlTaxRate = 0;
            if ($sysInfo['tax3s_dt'] <= $strDate && $strDate <= $sysInfo['tax3e_dt']){
                $dmlTaxRate = floatval($sysInfo['tax3']) / 100;
            }
            if ($sysInfo['tax2s_dt'] <= $strDate && $strDate <= $sysInfo['tax2e_dt']){
                $dmlTaxRate = floatval($sysInfo['tax2']) / 100;
            }
            if ($sysInfo['tax1s_dt'] <= $strDate && $strDate <= $sysInfo['tax1e_dt']){
                $dmlTaxRate = floatval($sysInfo['tax1']) / 100;
            }

            $Log->trace("END   getTaxRate");
            return $dmlTaxRate;
        }

        /*
         * ヘッダ部のページ数と集計値を設定する
         * @note     1ページ目は$head値を設定、2ページ目以降は***を設定
         * @return   変換後の$html_hd
         */
        private function setHeaderValue($page, $html_hd, $head, $tblTaxWork){
            
            define("MONEY_MASK", "***&nbsp;***&nbsp;***");
            
            global $Log;  // グローバル変数宣言
            $Log->trace("START setHeaderValue");

            // パラメータ値をローカル変数に格納
            $html_ret = $html_hd;
            
            // ページ数
            $html_ret = str_replace('%PAGE%', number_format($page), $html_ret);
            
            if ($page === 1){
                // 前回御請求額
                $html_ret = str_replace('%BEF_BALANCE%', number_format($head["bef_balance"]), $html_ret);
                // 今回御入金額
                $dmlReceTotal = floatval($head["rece_total"]) + floatval($head["rece_disc"]);
                $html_ret = str_replace('%NYUKIN_TOTAL%', number_format($dmlReceTotal), $html_ret);
                // 繰越額
                $html_ret = str_replace('%BEF_BALANCE2%', number_format(floatval($head["bef_balance"]) - $dmlReceTotal), $html_ret);
                // 今回御買上額 ※お買い上げ額を税抜きにする計算
                $calcsales_tax = 0;
                for ($intJ = 0; $intJ < 5; $intJ ++){
                    if ($tblTaxWork[$intJ]['sales_tax_rate'] >= 0){
                        $calcsales_tax = $calc_sale_total + $tblTaxWork[$intJ]['sales_tax'];
                    }
                }
//                $html_ret = str_replace('%SALE_TOTAL%', number_format($head["sale_total"] - $calcsales_tax), $html_ret);
                $html_ret = str_replace('%SALE_TOTAL%', number_format($head["sale_total"] - $calcsales_tax), $html_ret);
                // 外税額
                for ($intJ = 0; $intJ < 5; $intJ ++){
                    if ($tblTaxWork[$intJ]['sales_tax_rate'] >= 0){
                        $html_ret = str_replace('%SALES_TAX'.strval($intJ + 1).'%', number_format($tblTaxWork[$intJ]['sales_tax']), $html_ret);
                    }
                }
                // 今回取引額
                $html_ret = str_replace('%TRANSACTION_AMOUNT%', number_format($head["sale_total"]), $html_ret);
                // 今回御請求額
                $html_ret = str_replace('%NOW_BALANCE%', number_format($head["now_balance"]), $html_ret);
            }
            else{
                // 前回御請求額
                $html_ret = str_replace('%BEF_BALANCE%', MONEY_MASK, $html_ret);
                // 今回御入金額
                $html_ret = str_replace('%NYUKIN_TOTAL%', MONEY_MASK, $html_ret);
                // 繰越額
                $html_ret = str_replace('%BEF_BALANCE2%', MONEY_MASK, $html_ret);
                // 今回御買上額
                $html_ret = str_replace('%SALE_TOTAL%', MONEY_MASK, $html_ret);
                // 外税額
                for ($intJ = 0; $intJ < 5; $intJ ++){
                    if ($tblTaxWork[$intJ]['sales_tax_rate'] >= 0){
                        $html_ret = str_replace('%SALES_TAX'.strval($intJ + 1).'%', MONEY_MASK, $html_ret);
                    }
                }
                // 今回取引額
                $html_ret = str_replace('%TRANSACTION_AMOUNT%', MONEY_MASK, $html_ret);
                // 今回御請求額
                $html_ret = str_replace('%NOW_BALANCE%', MONEY_MASK, $html_ret);
            }
            
            $Log->trace("END   setHeaderValue");
            return $html_ret;
        }
    }
?>
