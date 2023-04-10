<?php
    /**
     * @file      帳票 - 在庫一覧表(明細一覧表)
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 在庫明細一覧を表示
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    //require './Model/LedgerSheetItem.php';
    require './Model/LedgerSheetStocklist.php';
    // Excel読み込み用ファイル
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel.php';
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel/IOFactory.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetStocklistController extends BaseController
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
         * 表示項目設定一覧画面検索
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

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-L',0,'',5,5,5,5,0,0,'');

            //$html = file_get_contents("./View/LedgerSheetStocklistPDFPanel.html");
            //$mpdf->WriteHTML("$html");
            //検索条件
            $searchArray = array();

            $prodCd1   = parent::escStr( $_POST['prod_cd1'] );  //商品コード(開始)
            $prodCd2   = parent::escStr( $_POST['prod_cd2'] );  //商品コード(終了)
            $prodNm    = parent::escStr( $_POST['prod_nm'] );   //商品
            $bbDate1   = parent::escStr( $_POST['start_date'] );   //賞味期限(開始)
            $bbDate2   = parent::escStr( $_POST['end_date'] );     //賞味期限(終了)

            $searchArray = array(
                 'prod_cd1'       => $prodCd1
                ,'prod_cd2'       => $prodCd2
                ,'prod_nm'        => $prodNm
                ,'bb_date1'     => str_replace("/","",$bbDate1)
                ,'bb_date2'     => str_replace("/","",$bbDate2)
            );

            //一覧データ取得
            $LedgerSheetStocklist = new LedgerSheetStocklist();
            $list = $LedgerSheetStocklist -> getListData($searchArray);

            $html   = '';
            $header = '';
            //商品コード
            $prodCd = '';
            if(!empty($prodCd1))
            {
              $prodCd .= $prodCd1;
            }
            if(!empty($prodCd1) || !empty($prodCd2))
            {
              $prodCd .= " ～ ";
            }
            if(!empty($prodCd2))
            {
              $prodCd .= $prodCd2;
            }
            if(empty($prodCd))
            {
              $prodCd = '未指定';
            }
            //品名
            if(empty($prodNm))
            {
              $prodNm = '未指定';
            }
            //賞味期限
            $bbDate = '';
            if(!empty($bbDate1))
            {
              $bbDate .= $bbDate1;
            }
            if(!empty($bbDate1) || !empty($bbDate2))
            {
              $bbDate .= " ～ ";
            }
            if(!empty($bbDate2))
            {
              $bbDate .= $bbDate2;
            }
            if(empty($bbDate))
            {
              $bbDate = '未指定';
            }



            $count = 1;
            $page = 1;
            $maxRow = 13;
            $sum = ceil(count($list)/$maxRow);

            foreach($list as $data)
            {
                if($count == 1)
                {
                  $header  = '<!DOCTYPE html> ';
                  $header .= '<html> ';
                  $header .= '<head> ';
                  $header .= '    <meta charset="utf-8" /> ';
                  $header .= '    <title>在庫一覧表(明細一覧表)</title> ';
                  $header .= '    <STYLE type="text/css"> ';
                  $header .= '        html,body{width:100%;height:100%;} ';
                  $header .= '        header{width:100%;height:95px;} ';
                  $header .= '        main{width:100%;height:850px;} ';
                  $header .= '        footer{width:100%;height:25px;position:absolute;bottom:0;} ';
                  $header .= '        .barcode { ';
                  $header .= '            padding: 1.5mm; ';
                  $header .= '            margin: 0; ';
                  $header .= '            vertical-align: top; ';
                  $header .= '            color: #000044; ';
                  $header .= '        } ';
                  $header .= '        .barcodecell { ';
                  $header .= '            text-align: center; ';
                  $header .= '            vertical-align: middle; ';
                  $header .= '        } ';
                  $header .= '    </STYLE> ';
                  $header .= '</head> ';
                  $header .= '<body> ';
                  $header .= '<!--<div style="page-break-before: always;">--> ';
                  $header .= '    <div> ';
                  $header .= '    <header> ';
                  $header .= '        <div align="right">印刷日時：'.date("Y/m/d H:i:s").'</div> ';
                  $header .= '        <div align="right">ページ：'.$page.' / '.$sum.'</div> ';
                  $header .= '        <div align="center" style="font-style:oblique; line-height: 5px;"> ';
                  $header .= '            <h1 style="text-align:center;font-size:24px;"><span>在庫一覧表(明細一覧表)</span></h1> ';
                  $header .= '        </div> ';
                  $header .= '        <table style="width:100%;"> ';
                  $header .= '            <tr> ';
                  $header .= '                <td align="left">対象在庫：現在在庫</td> ';
                  $header .= '            </tr> ';
                  $header .= '            <tr> ';
                  $header .= '                <td align="left">商品コード： '.$prodCd.'</td> ';
                  $header .= '            </tr> ';
                  $header .= '            <tr> ';
                  $header .= '                <td align="left">商品名： '.$prodNm.'</td> ';
                  $header .= '            </tr> ';
                  $header .= '            <tr> ';
                  $header .= '                <td align="left">売上期間： '.$bbDate.'</td> ';
                  $header .= '            </tr> ';
                  $header .= '        </table> ';
                  $header .= '        <table style="border-bottom:solid 1px #000000;width:100%;margin-top:10px;"> ';
                  $header .= '            <tr> ';
                  $header .= '                <td align="left" width="130px">商品</td> ';
                  $header .= '                <td align="left">商品名</td> ';
                  $header .= '                <td align="left" width="80">賞味期限</td> ';
                  $header .= '                <td align="left" width="60">容量</td> ';
                  $header .= '                <td align="right" width="90">原単価</td> ';
                  $header .= '                <td align="right" width="90">売単価</td> ';
                  $header .= '                <td align="right" width="60">在庫数</td> ';
                  $header .= '                <td align="right" width="90">原価金額</td> ';
                  $header .= '                <td align="right" width="90">売価金額</td> ';
                  $header .= '                <td align="left" width="120">棚番</td> ';
                  $header .= '            </tr> ';
                  $header .= '        </table> ';
                  $header .= '    </header> ';
                  $header .= '    <main> ';
                  $header .= '        <table style="width:100%;"> ';

                    $html .= $header;
                }
                if($data['gyokbn']=='001')
                {
                  $html .= '<tr style="padding-top:5px;padding-bottom:5px;';
                  if($count % 2 ===0)
                  {
                    $html .= 'background:#efefef;';
                  }
                  $html .= '">';
                  $html .= '  <td align="left" valign="top" width="130px">'.$data['prod_cd'].'<br><br><br></td>';
                  $html .= '  <td align="left" valign="top">'.$data['prod_nm'].'</td>';
                  $html .= '  <td align="left" valign="top" width="80">'.$data['bb_date'].'</td>';
                  $html .= '  <td align="left" valign="top" width="60">'.$data['prod_capa_nm'].'</td>';
                  $html .= '  <td align="right" valign="top" width="90">'.number_format($data['head_costprice'],2).'</td>';
                  $html .= '  <td align="right" valign="top" width="90">'.number_format($data['saleprice']).'</td>';
                  $html .= '  <td align="right" valign="top" width="60">'.number_format($data['total_stock_amout']).'</td>';
                  $html .= '  <td align="right" valign="top" width="90">'.number_format($data['total1']).'</td>';
                  $html .= '  <td align="right" valign="top" width="90">'.number_format($data['total2']).'</td>';
                  $html .= '  <td align="left" valign="top" width="120">'.$data['location_no'].'</td>';
                  $html .= '</tr>';
                } else if($data['gyokbn']=='002'){
                  $html .= '<tr>';
                  $html .= '  <td colspan="10" style="border-top:1px solid #000000;"></td>';
                  $html .= '</tr>';
                  $html .= '<tr style="padding-top:5px;padding-bottom:5px;">';
                  $html .= '  <td align="left" valign="top" width="130px" colspan="7">分類計<br><br><br></td>';
                  $html .= '  <td align="right" valign="top" width="90">'.number_format($data['total1']).'</td>';
                  $html .= '  <td align="right" valign="top" width="90">'.number_format($data['total2']).'</td>';
                  $html .= '  <td align="left" valign="top" width="120"></td>';
                  $html .= '</tr>';
                } else if($data['gyokbn']=='999'){
                  $html .= '<tr>';
                  $html .= '  <td colspan="10" style="border-top:1px solid #000000;"></td>';
                  $html .= '</tr>';
                  $html .= '<tr style="padding-top:5px;padding-bottom:5px;background:#efefef;">';
                  $html .= '  <td align="left" valign="top" width="130px" colspan="7">総合計<br><br><br></td>';
                  $html .= '  <td align="right" valign="top" width="90">'.number_format($data['total1']).'</td>';
                  $html .= '  <td align="right" valign="top" width="90">'.number_format($data['total2']).'</td>';
                  $html .= '  <td align="left" valign="top" width="120"></td>';
                  $html .= '</tr>';
                }

                if($count > $maxRow || end($list) == $data)
                {
                    $count = 0;
                    $html .= '    </table>'
                             .'    </main>'
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

            $Log->trace("END   pdfoutputAction");
        }

        /**
         * 帳票一覧画面
         * @note     商品別データ取得
         * @return   無
         */
        private function initialDisplay($mode)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $LedgerSheetStocklist = new LedgerSheetStocklist();

            // システムマスタ：賞味期限利用区分
            $bb_date_kbn = $LedgerSheetStocklist->chk_BB_DATE_KBN();
            
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
            
            $searchArray = array();
/*
            $prodCd1   = parent::escStr( $_POST['prod_cd1'] );  //商品コード(開始)
            $prodCd2   = parent::escStr( $_POST['prod_cd2'] );  //商品コード(終了)
            $prodNm    = parent::escStr( $_POST['prod_nm'] );   //商品
            $bbDate1   = parent::escStr( $_POST['start_date'] );   //賞味期限(開始)
            $bbDate2   = parent::escStr( $_POST['end_date'] );     //賞味期限(終了)

            $organizationId   = parent::escStr( $_POST['organizationName'] );

            $searchArray = array(
                 'prod_cd1'       => $prodCd1
                ,'prod_cd2'       => $prodCd2
                ,'prod_nm'        => $prodNm
                ,'bb_date1'       => str_replace("/","",$bbDate1)
                ,'bb_date2'       => str_replace("/","",$bbDate2)
                ,'organization_id'   => $organizationId
            );
*/
            
            
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
            if($_POST['tenpo']){
                $organizationId = 'compile';
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

            
            // 賞味期限表示区分
            if ($bb_date_kbn === '1'){
                // 賞味期限は利用しない設定故、表示もしない
                $bb_date_dspkbn = '1';
            }
            else{
                // 既定値は0(表示する)としておく
                $bb_date_dspkbn = isset($_POST['bb_date_dspkbn']) ? parent::escStr($_POST['bb_date_dspkbn']) : '0';
            }
            // ゼロ在庫表示(0:表示 / 1:非表示)
            $zero_kbn = isset($_POST['zero']) ? '0' : '1';
            // 在庫数範囲指定
            $amount_str = isset($_POST['amount_str']) ? parent::escStr($_POST['amount_str']) : '';
            $amount_end = isset($_POST['amount_end']) ? parent::escStr($_POST['amount_end']) : '';
            // 対象在庫(0:現在在庫 / 1:前月末在庫)　既定値は0(現在在庫)としておく
            $amount_kbn = isset($_POST['amount_kbn']) ? parent::escStr($_POST['amount_kbn']) : '0';
            // 売単価表示(0:税込 / 1:税抜)　既定値は0(税込)としておく
            $saleprice_kbn = isset($_POST['saleprice_kbn']) ? parent::escStr($_POST['saleprice_kbn']) : '0';

            
            $searchArray = array(
                'organization_id'   => $organizationId,
                'prod_cd'           => $prod_cd,
                'sect_cd'           => $sect_cd,
                'bb_date_kbn'       => $bb_date_kbn,
                'bb_date_dspkbn'    => $bb_date_dspkbn,
                'zero_kbn'          => $zero_kbn,
                'amount_str'        => str_replace(',', '', $amount_str),
                'amount_end'        => str_replace(',', '', $amount_end),
                'amount_kbn'        => $amount_kbn,
                'saleprice_kbn'     => $saleprice_kbn,
                'sort'              => $sort,
            );


            //データの取得
            if($mode != "initial")
            {
              $list = $LedgerSheetStocklist -> getListData($searchArray);
              
                //
                $organization_id_bef = '';
                $prod_cd_bef = '';
              
                // 店舗合計
                $sum_stock_amount           = 0.0;
                $sum_stock_cost_total       = 0.0;
                $sum_stock_sale_total       = 0.0;                
                // 総合計
                $sumall_stock_amount        = 0.0;
                $sumall_stock_cost_total    = 0.0;
                $sumall_stock_sale_total    = 0.0;                
                
                $ledgerSheetDetailList = array();
                for ($intL = 0; $intL < count($list); $intL ++){
                    
                    if (isset($list[$intL]['organization_id']) === true){
                        $organization_id = intval($list[$intL]['organization_id']);
                    }
                    else{
                        $organization_id = '';
                    }
                    $prod_cd = $list[$intL]['prod_cd'];

                    if ($organization_id !== $organization_id_bef){
                        if ($intL > 0){
                            // 店舗合計行
                            $aryAddRow = array();
                            $aryAddRow['abbreviated_name']   = '合計';
                            $aryAddRow['amount']    = number_format($sum_stock_amount);
                            $aryAddRow['stock_cost_total']  = number_format($sum_stock_cost_total);
                            $aryAddRow['stock_sale_total']  = number_format($sum_stock_sale_total);
                            $aryAddRow['record_type'] = 'S';    // Summary
                            // 行追加
                            if(!$_POST['tenpo']){
                                array_push($ledgerSheetDetailList, $aryAddRow);
                            }
                        }
                        // 店舗合計
                        $sum_stock_amount           = 0.0;
                        $sum_stock_cost_total       = 0.0;
                        $sum_stock_sale_total       = 0.0;

                        // 次店舗の行が同一商品で始まったらコードと名称が表示されないので初期化
                        $prod_cd_bef = '';
                    }

                    // 原単価
                    $costprice = floatval($list[$intL]['head_costprice']);
                    
                    // 売単価
                    if ($saleprice_kbn === '0'){
                        // 税込
                        $saleprice = floatval($list[$intL]['saleprice']);
                    }
                    else if ($saleprice_kbn === '1'){
                        // 税抜
                        $saleprice = floatval($list[$intL]['saleprice_ex']);
                    }
                    else{
                        $saleprice = 0;
                    }
                    // 在庫数
                    if ($amount_kbn === '0'){
                        // 現在在庫
                        $amount = floatval($list[$intL]['total_stock_amout']);
                    }
                    else if ($amount_kbn === '1'){
                        // 現在在庫
                        $amount = floatval($list[$intL]['endmon_amount']);
                    }
                    else{
                        $amount = 0;
                    }
                    // 原価金額
                    $stock_cost_total = $costprice * $amount;
                    // 売価金額
                    $stock_sale_total = $saleprice * $amount;

                    $aryAddRow = $list[$intL];
                    $organization_id = intval($list[$intL]['organization_id']);
                    if ($intL > 0 && $organization_id === $organization_id_bef){
                        $aryAddRow['abbreviated_name'] = '';
                    }
                    if($_POST['tenpo'] && $intL === 0){
                        $aryAddRow['abbreviated_name'] = '企業計';
                    }                    
                    if ($prod_cd === $prod_cd_bef){
                        $aryAddRow['prod_cd'] = '';
                        $aryAddRow['prod_nm'] = '';
                    }
                    $aryAddRow['costprice']         = number_format($costprice, 2);
                    $aryAddRow['saleprice']         = number_format($saleprice);
                    $aryAddRow['amount']            = number_format($amount);
                    $aryAddRow['stock_cost_total']  = number_format($stock_cost_total);
                    $aryAddRow['stock_sale_total']  = number_format($stock_sale_total);
                    $aryAddRow['record_type'] = 'D';    // Detail

                    // 行追加
                    array_push($ledgerSheetDetailList, $aryAddRow);
                    
                    // 加算
                    $sum_stock_amount += $amount;
                    $sum_stock_cost_total += $stock_cost_total;
                    $sum_stock_sale_total += $stock_sale_total;
                    // 加算
                    $sumall_stock_amount += $amount;
                    $sumall_stock_cost_total += $stock_cost_total;
                    $sumall_stock_sale_total += $stock_sale_total;
                    
                    // 値保持
                    $organization_id_bef = $organization_id;
                    $prod_cd_bef = $prod_cd;
                }
                // ループ脱出後
                if (count($list) > 0){
                    // 店舗合計行
                    $aryAddRow = array();
                    $aryAddRow['abbreviated_name']   = '合計';
                    $aryAddRow['amount']    = number_format($sum_stock_amount);
                    $aryAddRow['stock_cost_total']  = number_format($sum_stock_cost_total);
                    $aryAddRow['stock_sale_total']  = number_format($sum_stock_sale_total);
                    $aryAddRow['record_type'] = 'S';    // Summary
                    // 行追加
                    if(!$_POST['tenpo']){
                        array_push($ledgerSheetDetailList, $aryAddRow);
                    }
                    
                    // 仕切りの空行(background-color: blue;
                    $aryAddRow = array();
                    $aryAddRow['record_type'] = 'P';    // Partition
                    // 行追加
                    array_push($ledgerSheetDetailList, $aryAddRow);
                    
                    // 総合計
                    $aryAddRow = array();
                    $aryAddRow['prod_cd']   = '総合計';
                    $aryAddRow['amount']    = number_format($sumall_stock_amount);
                    $aryAddRow['stock_cost_total']  = number_format($sumall_stock_cost_total);
                    $aryAddRow['stock_sale_total']  = number_format($sumall_stock_sale_total);
                    $aryAddRow['record_type'] = 'F';    // Footer
                    // 行追加
                    array_push($ledgerSheetDetailList, $aryAddRow);
                }
            }

            // 検索組織
            //$searchArray = array(
            //    'organizationID' => parent::escStr( $_POST['organizationName'] ),
            //);
            $searchArray = array(
                'org_id'            => str_replace("'","",$_POST['org_id']),
                'prod_cd'           => str_replace("'","",$_POST['prod_cd']),
                'sect_cd'           => str_replace("'","",$_POST['sect_cd']),
                'bb_date_dspkbn'    => $bb_date_dspkbn,
                'zero_kbn'          => $zero_kbn,
                'amount_str'        => $amount_str,
                'amount_end'        => $amount_end,
                'amount_kbn'        => $amount_kbn,
                'saleprice_kbn'     => $saleprice_kbn,
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
                require_once './View/LedgerSheetStocklistPanel.html';
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
/*            
            $LedgerSheetStocklist = new LedgerSheetStocklist();
            
            // システムマスタ：賞味期限利用区分
            $bb_date_kbn = $LedgerSheetStocklist->chk_BB_DATE_KBN();
            
/*
            $prodCd1   = parent::escStr( $_POST['prod_cd1'] );  //商品コード(開始)
            $prodCd2   = parent::escStr( $_POST['prod_cd2'] );  //商品コード(終了)
            $prodNm    = parent::escStr( $_POST['prod_nm'] );   //商品
            $bbDate1   = parent::escStr( $_POST['start_date'] );   //賞味期限(開始)
            $bbDate2   = parent::escStr( $_POST['end_date'] );     //賞味期限(終了)

            $organizationId   = parent::escStr( $_POST['organizationName'] );

            $searchArray = array(
                 'prod_cd1'       => $prodCd1
                ,'prod_cd2'       => $prodCd2
                ,'prod_nm'        => $prodNm
                ,'bb_date1'       => str_replace("/","",$bbDate1)
                ,'bb_date2'       => str_replace("/","",$bbDate2)
                ,'organization_id'   => $organizationId
            );

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
            if($_POST['tenpo']){
                $organizationId = 'compile';
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

            
            // 賞味期限表示区分
            if ($bb_date_kbn === '1'){
                // 賞味期限は利用しない設定故、表示もしない
                $bb_date_dspkbn = '1';
            }
            else{
                // 既定値は0(表示する)としておく
                $bb_date_dspkbn = isset($_POST['bb_date_dspkbn']) ? parent::escStr($_POST['bb_date_dspkbn']) : '0';
            }
            // ゼロ在庫表示(0:表示 / 1:非表示)
            $zero_kbn = isset($_POST['zero']) ? '0' : '1';
            // 在庫数範囲指定
            $amount_str = isset($_POST['amount_str']) ? parent::escStr($_POST['amount_str']) : '';
            $amount_end = isset($_POST['amount_end']) ? parent::escStr($_POST['amount_end']) : '';
            // 対象在庫(0:現在在庫 / 1:前月末在庫)　既定値は0(現在在庫)としておく
            $amount_kbn = isset($_POST['amount_kbn']) ? parent::escStr($_POST['amount_kbn']) : '0';
            // 売単価表示(0:税込 / 1:税抜)　既定値は0(税込)としておく
            $saleprice_kbn = isset($_POST['saleprice_kbn']) ? parent::escStr($_POST['saleprice_kbn']) : '0';

            
            $searchArray = array(
                'organization_id'   => $organizationId,
                'prod_cd'           => $prod_cd,
                'sect_cd'           => $sect_cd,
                'bb_date_kbn'       => $bb_date_kbn,
                'bb_date_dspkbn'    => $bb_date_dspkbn,
                'zero_kbn'          => $zero_kbn,
                'amount_str'        => str_replace(',', '', $amount_str),
                'amount_end'        => str_replace(',', '', $amount_end),
                'amount_kbn'        => $amount_kbn,
                'saleprice_kbn'     => $saleprice_kbn,
                'sort'              => $sort,
            );


            // 帳票フォーム一覧データ取得
            $list = $LedgerSheetStocklist -> getListData($searchArray);
              
            //
            $organization_id_bef = '';
            $prod_cd_bef = '';

            // 店舗合計
            $sum_stock_amount           = 0.0;
            $sum_stock_cost_total       = 0.0;
            $sum_stock_sale_total       = 0.0;                
            // 総合計
            $sumall_stock_amount        = 0.0;
            $sumall_stock_cost_total    = 0.0;
            $sumall_stock_sale_total    = 0.0;                

            $ledgerSheetDetailList = array();
            for ($intL = 0; $intL < count($list); $intL ++){
                if (isset($list[$intL]['organization_id']) === true){
                    $organization_id = intval($list[$intL]['organization_id']);
                }
                else{
                    $organization_id = '';
                }
                $prod_cd = $list[$intL]['prod_cd'];

                if ($organization_id !== $organization_id_bef){
                    if ($intL > 0){
                        // 店舗合計行
                        $aryAddRow = array();
                        $aryAddRow['prod_cd']   = '合計';
                        $aryAddRow['amount']    = number_format($sum_stock_amount, 0, ".", "");
                        $aryAddRow['stock_cost_total']  = number_format($sum_stock_cost_total, 0, ".", "");
                        $aryAddRow['stock_sale_total']  = number_format($sum_stock_sale_total, 0, ".", "");
                        $aryAddRow['record_type'] = 'S';    // Summary
                        // 行追加
                        if(!$_POST['tenpo']){
                            array_push($ledgerSheetDetailList, $aryAddRow);
                        }
                    }
                    // 店舗合計
                    $sum_stock_amount           = 0.0;
                    $sum_stock_cost_total       = 0.0;
                    $sum_stock_sale_total       = 0.0;

                    // 次店舗の行が同一商品で始まったらコードと名称が表示されないので初期化
                    $prod_cd_bef = '';
                }

                // 原単価
                $costprice = floatval($list[$intL]['head_costprice']);

                // 売単価
                if ($saleprice_kbn === '0'){
                    // 税込
                    $saleprice = floatval($list[$intL]['saleprice']);
                }
                else if ($saleprice_kbn === '1'){
                    // 税抜
                    $saleprice = floatval($list[$intL]['saleprice_ex']);
                }
                else{
                    $saleprice = 0;
                }
                // 在庫数
                if ($amount_kbn === '0'){
                    // 現在在庫
                    $amount = floatval($list[$intL]['total_stock_amout']);
                }
                else if ($amount_kbn === '1'){
                    // 現在在庫
                    $amount = floatval($list[$intL]['endmon_amount']);
                }
                else{
                    $amount = 0;
                }
                // 原価金額
                $stock_cost_total = $costprice * $amount;
                // 売価金額
                $stock_sale_total = $saleprice * $amount;

                $aryAddRow = $list[$intL];
                $organization_id = intval($list[$intL]['organization_id']);
                //if ($intL > 0 && $organization_id === $organization_id_bef){
                //    $aryAddRow['abbreviated_name'] = '';
                //}
                //if ($prod_cd === $prod_cd_bef){
                //    $aryAddRow['prod_cd'] = '';
                //    $aryAddRow['prod_nm'] = '';
                //}
                $aryAddRow['costprice']         = number_format($costprice, 2, ".", "");
                $aryAddRow['saleprice']         = number_format($saleprice, 0, ".", "");
                $aryAddRow['amount']            = number_format($amount, 0, ".", "");
                $aryAddRow['stock_cost_total']  = number_format($stock_cost_total, 0, ".", "");
                $aryAddRow['stock_sale_total']  = number_format($stock_sale_total, 0, ".", "");
                $aryAddRow['record_type'] = 'D';    // Detail
                if($_POST['tenpo'] && $intL === 0){
                    $aryAddRow['abbreviated_name'] = '企業計';
                }                

                // 行追加
                array_push($ledgerSheetDetailList, $aryAddRow);

                // 加算
                $sum_stock_amount += $amount;
                $sum_stock_cost_total += $stock_cost_total;
                $sum_stock_sale_total += $stock_sale_total;
                // 加算
                $sumall_stock_amount += $amount;
                $sumall_stock_cost_total += $stock_cost_total;
                $sumall_stock_sale_total += $stock_sale_total;

                // 値保持
                $organization_id_bef = $organization_id;
                $prod_cd_bef = $prod_cd;
            }
            // ループ脱出後
            if (count($list) > 0){
                // 店舗合計行
                $aryAddRow = array();
                $aryAddRow['prod_cd']   = '合計';
                $aryAddRow['amount']    = number_format($sum_stock_amount, 0, ".", "");
                $aryAddRow['stock_cost_total']  = number_format($sum_stock_cost_total, 0, ".", "");
                $aryAddRow['stock_sale_total']  = number_format($sum_stock_sale_total, 0, ".", "");
                $aryAddRow['record_type'] = 'S';    // Summary
                // 行追加
                if(!$_POST['tenpo']){
                    array_push($ledgerSheetDetailList, $aryAddRow);
                }

                //// 仕切りの空行(background-color: blue;
                //$aryAddRow = array();
                //$aryAddRow['record_type'] = 'P';    // Partition
                //// 行追加
                //array_push($ledgerSheetDetailList, $aryAddRow);

                // 総合計
                $aryAddRow = array();
                $aryAddRow['prod_cd']   = '総合計';
                $aryAddRow['amount']    = number_format($sumall_stock_amount, 0, ".", "");
                $aryAddRow['stock_cost_total']  = number_format($sumall_stock_cost_total, 0, ".", "");
                $aryAddRow['stock_sale_total']  = number_format($sumall_stock_sale_total, 0, ".", "");
                $aryAddRow['record_type'] = 'F';    // Footer
                // 行追加
                array_push($ledgerSheetDetailList, $aryAddRow);
            }

            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            //array_push($export_csv_title, "No");
            array_push($export_csv_title, "店舗名");
            array_push($export_csv_title, "商品コード");
            array_push($export_csv_title, "商品名");
            array_push($export_csv_title, "税率");
            array_push($export_csv_title, "賞味期限");
            array_push($export_csv_title, "容量");
            array_push($export_csv_title, "原単価");
            array_push($export_csv_title, "売単価");
            array_push($export_csv_title, "在庫数");
            array_push($export_csv_title, "原価金額");
            array_push($export_csv_title, "売価金額");

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
                    
                    //if($rows['prod_cd'] != '合計' && $rows['prod_cd'] != '総合計' ){
                        
                        // 垂直ヘッダ
                        $list_no += 1;

                        //$str = '"'.$list_no;// 順位
                        $str = "";

                        $str = $str.'"'.$rows['abbreviated_name'];
                        $str = $str.'","'.$rows['prod_cd'];
                        $str = $str.'","'.$rows['prod_nm'];
                        $str = $str.'","'.$rows['prod_tax'];
                        $str = $str.'","'.$rows['bb_date'];
                        $str = $str.'","'.$rows['prod_capa_nm'];
                        $str = $str.'","'.$rows['costprice'];
                        $str = $str.'","'.$rows['saleprice'];
                        $str = $str.'","'.$rows['amount'];
                        $str = $str.'","'.$rows['stock_cost_total'];
                        $str = $str.'","'.$rows['stock_sale_total'];
                        $str = $str.'"';

                        // 配列に変換
                        $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
                        //$export_arr = explode(",", $str);

                        // 内容行を1行ごとにCSVデータへ
                        $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");
                    //}
                }
           }
 */
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
            // ダウンロード用
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers 
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=在庫一覧表(明細一覧表)".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }

    }
?>
