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
    require './Model/LedgerSheetStockTotal.php';
    // Excel読み込み用ファイル
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel.php';
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel/IOFactory.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetStockTotalController extends BaseController
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

            //$html = file_get_contents("./View/LedgerSheetStockTotalPDFPanel.html");
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
            $LedgerSheetStockTotal = new LedgerSheetStockTotal();
            $list = $LedgerSheetStockTotal -> getListData($searchArray);

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
            $maxRow = 14;
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
                  $header .= '                <td align="left" width="300px">分類</td> ';
                  $header .= '                <td align="right" width="130px">分類計</td> ';
                  $header .= '                <td align="right" width="480px">在庫数　</td> ';
                  $header .= '                <td align="right" width="130px">原価金額　</td>';
                  $header .= '                <td align="right" width="130px">売価金額　</td> ';
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
                  $html .= '  <td align="left" valign="top" width="300px">'.$data['type_nm'].'</td>';
                  $html .= '  <td align="right" valign="bottom" width="130px"><br>'.number_format($data['saleprice']).'</td>';
                  $html .= '  <td align="right" valign="bottom" width="480px">'.number_format($data['total_stock_amout']).'</td>';
                  $html .= '  <td align="right" valign="bottom" width="130px">'.number_format($data['total1']).'</td>';
                  $html .= '  <td align="right" valign="bottom" width="130px">'.number_format($data['total2']).'</td>';
                  $html .= '</tr>';
                  $html .= '<tr>';
                  $html .= '  <td colspan="10" style="border-top:1px solid #000000;"></td>';
                  $html .= '</tr>';
                } else if($data['gyokbn']=='999'){

                  $html .= '<tr style="padding-top:5px;padding-bottom:5px;">';
                  $html .= '  <td align="right" colspan="3">合計</td>';
                  $html .= '  <td align="right" width="130px">'.number_format($data['total1']).'</td>';
                  $html .= '  <td align="right" width="130px">'.number_format($data['total2']).'</td>';
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

            $LedgerSheetStockTotal = new LedgerSheetStockTotal();

            $abbreviatedNameList = $LedgerSheetStockTotal->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

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
            $amount_str = isset($_POST['amount_str']) ? str_replace(',', '', parent::escStr($_POST['amount_str'])) : '';
            $amount_end = isset($_POST['amount_end']) ? str_replace(',', '', parent::escStr($_POST['amount_end'])) : '';
            // 対象在庫(0:現在在庫 / 1:前月末在庫)　既定値は0(現在在庫)としておく
            $amount_kbn = isset($_POST['amount_kbn']) ? parent::escStr($_POST['amount_kbn']) : '0';
            // 売単価表示(0:税込 / 1:税抜)　既定値は0(税込)としておく
            $saleprice_kbn = isset($_POST['saleprice_kbn']) ? parent::escStr($_POST['saleprice_kbn']) : '0';
            
            $searchArray = array(
                'organization_id'   => $organizationId,
                'prod_cd'           => $prod_cd,
                'sect_cd'           => $sect_cd,
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
                $list = $LedgerSheetStockTotal -> getListData($searchArray);
                $ledgerSheetDetailList = array();
                $t_cumul_data = array();
                $t_cumul_data['total'] = array();   // 総合計
//                $t_cumul_data['total']['saleprice'] = 0;
                $t_cumul_data['total']['amount']    = 0;
                $t_cumul_data['total']['total1']    = 0;
                $t_cumul_data['total']['total2']    = 0;
              
                $org_id_bef = '';
                foreach ($list as $row){
                    $org_id = $row['org_id'];
                    // 店舗
                    if ($searchArray['organization_id'] === 'compile'){
                        if (count($ledgerSheetDetailList) === 0){
                            $row['org_nm'] = '企業計';
                        }
                    }
                    else{
                        if ($org_id !== $org_id_bef){
                            if ($org_id_bef !== ''){
                                // 店舗合計行
                                $aryAddRow = array();
                                $aryAddRow['record_type'] = 'S';    // Summary
                                $aryAddRow['org_nm']    = '';
                                $aryAddRow['type_nm']   = '合計';
//                                $aryAddRow['saleprice'] = $t_cumul_data[$org_id_bef]['saleprice'];
                                $aryAddRow['amount']    = $t_cumul_data[$org_id_bef]['amount'];
                                $aryAddRow['total1']    = $t_cumul_data[$org_id_bef]['total1'];
                                $aryAddRow['total2']    = $t_cumul_data[$org_id_bef]['total2'];
                                // 追加
                                array_push($ledgerSheetDetailList, $aryAddRow);
                            }
                            $t_cumul_data[$org_id] = array();
//                            $t_cumul_data[$org_id]['saleprice'] = 0;
                            $t_cumul_data[$org_id]['amount']    = 0;
                            $t_cumul_data[$org_id]['total1']    = 0;
                            $t_cumul_data[$org_id]['total2']    = 0;
                        }
                        else{
                            // 店舗名表示省略
                            $row['org_nm'] = '';
                        }
                    }
                    $row['record_type'] = 'D';  // Detail
                    array_push($ledgerSheetDetailList, $row);
                    
                    // 店舗合計加算
//                    $t_cumul_data[$org_id]['saleprice'] += floatval($row['saleprice']);
                    $t_cumul_data[$org_id]['amount']    += floatval($row['amount']);
                    $t_cumul_data[$org_id]['total1']    += floatval($row['total1']);
                    $t_cumul_data[$org_id]['total2']    += floatval($row['total2']);
                    // 総合計加算
//                    $t_cumul_data['total']['saleprice'] += floatval($row['saleprice']);
                    $t_cumul_data['total']['amount']    += floatval($row['amount']);
                    $t_cumul_data['total']['total1']    += floatval($row['total1']);
                    $t_cumul_data['total']['total2']    += floatval($row['total2']);
                    
                    $org_id_bef = $org_id;
                }
                if ($searchArray['organization_id'] !== 'compile'){
                    if (count($list) > 0){
                        // ループ脱出後　店舗合計行
                        $aryAddRow = array();
                        $aryAddRow['record_type'] = 'S';    // Summary
                        $aryAddRow['org_nm']    = '';
                        $aryAddRow['type_nm']   = '合計';
//                        $aryAddRow['saleprice'] = $t_cumul_data[$org_id_bef]['saleprice'];
                        $aryAddRow['amount']    = $t_cumul_data[$org_id_bef]['amount'];
                        $aryAddRow['total1']    = $t_cumul_data[$org_id_bef]['total1'];
                        $aryAddRow['total2']    = $t_cumul_data[$org_id_bef]['total2'];
                        // 行追加
                        array_push($ledgerSheetDetailList, $aryAddRow);

                        //// 仕切りの空行(background-color: blue;
                        //$aryAddRow = array();
                        //$aryAddRow['record_type'] = 'P';    // Partition
                        //// 行追加
                        //array_push($ledgerSheetDetailList, $aryAddRow);

                        //// 総合計
                        //$aryAddRow = array();
                        //$aryAddRow['org_nm']    = '';
                        //$aryAddRow['type_nm']   = '総合計';
                        //$aryAddRow['saleprice'] = $t_cumul_data['total']['saleprice'];
                        //$aryAddRow['amount']    = $t_cumul_data['total']['amount'];
                        //$aryAddRow['total1']    = $t_cumul_data['total']['total1'];
                        //$aryAddRow['total2']    = $t_cumul_data['total']['total2'];
                        //$aryAddRow['record_type'] = 'F';    // Footer
                        //// 行追加
                        //array_push($ledgerSheetDetailList, $aryAddRow);
                    }
                }
                else{
                    // 企業計の場合は総合計行は不要
                    unset($t_cumul_data['total']);
                }
            }

            $searchArray = array(
                'org_id'            => str_replace("'","",$_POST['org_id']),
                'prod_cd'           => str_replace("'","",$_POST['prod_cd']),
                'sect_cd'           => str_replace("'","",$_POST['sect_cd']),
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
                require_once './View/LedgerSheetStockTotalPanel.html';
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
            
//            $LedgerSheetStockTotal = new LedgerSheetStockTotal();
///*
//            $searchArray = array();
//
//            $prodCd1   = parent::escStr( $_POST['prod_cd1'] );  //商品コード(開始)
//            $prodCd2   = parent::escStr( $_POST['prod_cd2'] );  //商品コード(終了)
//            $prodNm    = parent::escStr( $_POST['prod_nm'] );   //商品
//            $bbDate1   = parent::escStr( $_POST['start_date'] );   //賞味期限(開始)
//            $bbDate2   = parent::escStr( $_POST['end_date'] );     //賞味期限(終了)
//
//            $organizationId   = parent::escStr( $_POST['organizationName'] );
//
//            $searchArray = array(
//                 'prod_cd1'       => $prodCd1
//                ,'prod_cd2'       => $prodCd2
//                ,'prod_nm'        => $prodNm
//                ,'bb_date1'       => str_replace("/","",$bbDate1)
//                ,'bb_date2'       => str_replace("/","",$bbDate2)
//                ,'organization_id'   => $organizationId
//            );
//*/
//            $organizationId = 'false';
//            //if($_POST['org_r'] === ""){
//            //    if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
//            //        $organizationId = $_POST['org_id'];
//            //    }else{
//            //        $organizationId = "'".$_POST['org_select']."'";
//            //    }
//            //}
//            $org_r = isset($_POST['org_r']) ? parent::escStr($_POST['org_r']) : '';
//            if($org_r === ""){
//                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
//                    $organizationId = $_POST['org_id'];
//                }else{
//                    $organizationId = "'".$_POST['org_select']."'";
//                }
//            }
//            else{
//                $organizationId = $org_r;
//            }
//            $prod_cd = 'false';
//            if($_POST['prod_r'] === ""){
//                if($_POST['prod_select'] && $_POST['prod_select'] === 'empty'){
//                    $prod_cd = $_POST['prod_cd'];
//                }else{
//                    $prod_cd = "'".$_POST['prod_select']."'";
//                }
//            }
//            $sect_cd = 'false';
//            if($_POST['sect_r'] === ""){
//                if($_POST['sect_select'] && $_POST['sect_select'] === 'empty'){
//                    $sect_cd = $_POST['sect_cd'];
//                }else{
//                    $sect_cd = "'".$_POST['sect_select']."'";
//                }
//            }
//            //$sectCdS = parent::escStr( $_POST['sectCdS'] );            
//
//            
//            // 賞味期限表示区分
//            if ($bb_date_kbn === '1'){
//                // 賞味期限は利用しない設定故、表示もしない
//                $bb_date_dspkbn = '1';
//            }
//            else{
//                // 既定値は0(表示する)としておく
//                $bb_date_dspkbn = isset($_POST['bb_date_dspkbn']) ? parent::escStr($_POST['bb_date_dspkbn']) : '0';
//            }
//            // ゼロ在庫表示(0:表示 / 1:非表示)
//            $zero_kbn = isset($_POST['zero']) ? '0' : '1';
//            // 在庫数範囲指定
//            $amount_str = isset($_POST['amount_str']) ? str_replace(',', '', parent::escStr($_POST['amount_str'])) : '';
//            $amount_end = isset($_POST['amount_end']) ? str_replace(',', '', parent::escStr($_POST['amount_end'])) : '';
//            // 対象在庫(0:現在在庫 / 1:前月末在庫)　既定値は0(現在在庫)としておく
//            $amount_kbn = isset($_POST['amount_kbn']) ? parent::escStr($_POST['amount_kbn']) : '0';
//            // 売単価表示(0:税込 / 1:税抜)　既定値は0(税込)としておく
//            $saleprice_kbn = isset($_POST['saleprice_kbn']) ? parent::escStr($_POST['saleprice_kbn']) : '0';
//
//            
//            $searchArray = array(
//                'organization_id'   => $organizationId,
//                'prod_cd'           => $prod_cd,
//                'sect_cd'           => $sect_cd,
//                'zero_kbn'          => $zero_kbn,
//                'amount_str'        => str_replace(',', '', $amount_str),
//                'amount_end'        => str_replace(',', '', $amount_end),
//                'amount_kbn'        => $amount_kbn,
//                'saleprice_kbn'     => $saleprice_kbn,
//                'sort'              => $sort,
//            );
//
//            // 帳票フォーム一覧データ取得
//            $list = $LedgerSheetStockTotal -> getListData($searchArray);
//            $ledgerSheetDetailList = array();
//            $t_cumul_data = array();
//            $t_cumul_data['total'] = array();   // 総合計
////            $t_cumul_data['total']['saleprice'] = 0;
//            $t_cumul_data['total']['amount']    = 0;
//            $t_cumul_data['total']['total1']    = 0;
//            $t_cumul_data['total']['total2']    = 0;
//
//            $org_id_bef = '';
//            foreach ($list as $row){
//                $org_id = $row['org_id'];
//                // 店舗
//                if ($searchArray['organization_id'] === 'compile'){
//                    //if (count($ledgerSheetDetailList) === 0){
//                        $row['org_nm'] = '企業計';
//                    //}
//                }
//                else{
//                    if ($org_id !== $org_id_bef){
//                        if ($org_id_bef !== ''){
//                            // 店舗合計行
//                            $aryAddRow = array();
//                            $aryAddRow['record_type'] = 'S';    // Summary
//                            $aryAddRow['org_nm']    = '';
//                            $aryAddRow['type_nm']   = '合計';
////                            $aryAddRow['saleprice'] = $t_cumul_data[$org_id_bef]['saleprice'];
//                            $aryAddRow['amount']    = $t_cumul_data[$org_id_bef]['amount'];
//                            $aryAddRow['total1']    = $t_cumul_data[$org_id_bef]['total1'];
//                            $aryAddRow['total2']    = $t_cumul_data[$org_id_bef]['total2'];
//                            // 追加
//                            array_push($ledgerSheetDetailList, $aryAddRow);
//                        }
//                        $t_cumul_data[$org_id] = array();
////                        $t_cumul_data[$org_id]['saleprice'] = 0;
//                        $t_cumul_data[$org_id]['amount']    = 0;
//                        $t_cumul_data[$org_id]['total1']    = 0;
//                        $t_cumul_data[$org_id]['total2']    = 0;
//                    }
//                    //else{
//                    //    // 店舗名表示省略
//                    //    $row['org_nm'] = '';
//                    //}
//                }
//                $row['record_type'] = 'D';  // Detail
//                array_push($ledgerSheetDetailList, $row);
//
//                // 店舗合計加算
////                $t_cumul_data[$org_id]['saleprice'] += floatval($row['saleprice']);
//                $t_cumul_data[$org_id]['amount']    += floatval($row['amount']);
//                $t_cumul_data[$org_id]['total1']    += floatval($row['total1']);
//                $t_cumul_data[$org_id]['total2']    += floatval($row['total2']);
//                // 総合計加算
////                $t_cumul_data['total']['saleprice'] += floatval($row['saleprice']);
//                $t_cumul_data['total']['amount']    += floatval($row['amount']);
//                $t_cumul_data['total']['total1']    += floatval($row['total1']);
//                $t_cumul_data['total']['total2']    += floatval($row['total2']);
//
//                $org_id_bef = $org_id;
//            }
//            if ($searchArray['organization_id'] !== 'compile'){
//                if (count($list) > 0){
//                    // ループ脱出後　店舗合計行
//                    $aryAddRow = array();
//                    $aryAddRow['record_type'] = 'S';    // Summary
//                    $aryAddRow['org_nm']    = '';
//                    $aryAddRow['type_nm']   = '合計';
////                    $aryAddRow['saleprice'] = $t_cumul_data[$org_id_bef]['saleprice'];
//                    $aryAddRow['amount']    = $t_cumul_data[$org_id_bef]['amount'];
//                    $aryAddRow['total1']    = $t_cumul_data[$org_id_bef]['total1'];
//                    $aryAddRow['total2']    = $t_cumul_data[$org_id_bef]['total2'];
//                    // 行追加
//                    array_push($ledgerSheetDetailList, $aryAddRow);
//
//                    // 総合計
//                    $aryAddRow = array();
//                    $aryAddRow['record_type'] = 'F';    // Footer
//                    $aryAddRow['org_nm']    = '';
//                    $aryAddRow['type_nm']   = '総合計';
////                    $aryAddRow['saleprice'] = $t_cumul_data['total']['saleprice'];
//                    $aryAddRow['amount']    = $t_cumul_data['total']['amount'];
//                    $aryAddRow['total1']    = $t_cumul_data['total']['total1'];
//                    $aryAddRow['total2']    = $t_cumul_data['total']['total2'];
//                    // 行追加
//                    array_push($ledgerSheetDetailList, $aryAddRow);
//                }
//            }
//
//            // ファイル名
//            //$file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');
//            $file_path = date('YmdHis').".csv";
//
//            // CSVに出力するヘッダ行
//            $export_csv_title = array();
//
//            // 先頭固定ヘッダ追加
//            //array_push($export_csv_title, "No");
//            array_push($export_csv_title, "店舗");
//            array_push($export_csv_title, "分類");
//            array_push($export_csv_title, "分類計");
//            array_push($export_csv_title, "在庫数");
//            array_push($export_csv_title, "原価金額");
//            array_push($export_csv_title, "売価金額");
//
//            if( touch($file_path) ){
//                // オブジェクト生成
//                $file = new SplFileObject( $file_path, "w" ); 
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
//                    $list_no += 1;
//
//                    //$str = '"'.$list_no;// 順位
//
//                    $str = '"'.$row['org_nm'];
//                    $str = $str.'","'.$rows['type_cd'];
//                    $str = $str.'","'.$rows['type_nm'];
////                    $str = $str.'","'.round($rows['saleprice'],0);
//                    $str = $str.'","'.round($rows['amount'],0);
//                    $str = $str.'","'.round($rows['total1'],0);
//                    $str = $str.'","'.round($rows['total2'],0);
//                    $str = $str.'"';
//                    
//                    // 配列に変換
//                    $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
//                    //$export_arr = explode(",", $str);
//                    
//                    // 内容行を1行ごとにCSVデータへ
//                    $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");
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
            // ダウンロード用
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers 
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=在庫一覧表(合計表)".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }

    }
?>
