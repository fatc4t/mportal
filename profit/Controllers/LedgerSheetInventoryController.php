<?php
    /**
     * @file      棚卸一覧表
     * @author    millionet oota
     * @date      2020/05/18
     * @version   1.00
     * @note      帳票 - 棚卸一覧表を表示
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    //require './Model/LedgerSheetItem.php';
    require './Model/LedgerSheetInventory.php';
    // Excel読み込み用ファイル
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel.php';
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel/IOFactory.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetInventoryController extends BaseController
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
////ADDSTR 2020/06 kanderu*************************************************************************
        public function datasearchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");            
            $LedgerSheetInventory = new LedgerSheetInventory();

            $searchparam = json_decode($_POST['param'],true);
           //print_r($searchparam);
            
            $result = $LedgerSheetInventory->get_data_1($searchparam);
            
            
            print_r(json_encode($result));
            //print_r($_POST);            
        }  
       
        public function searchAction()       {
            global $Log; // グローバル変数宣言/            $Log->trace( "START searchAction" );
           // $Log->info( "MSG_INFO_1041" );
               
            if(isset($_POST['displayPageNo']))
            {
                $_SESSION['PAGE_NO'] = parent::escStr( $_POST['displayPageNo'] );
            }
                
            if(isset($_POST['displayRecordCnt']))
            {
                $_SESSION['DISPLAY_RECORD_CNT'] = parent::escStr( $_POST['displayRecordCnt'] );
            }
                
            $this->initialList(false);
                
            $Log->trace("END   searchAction");
        }
////        
//////ADDEND 2020/06 kanderu*************************************************************************
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
            $LedgerSheetInventory = new LedgerSheetInventory();
            $list = $LedgerSheetInventory -> getListData($searchArray);

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

            // Model生成
            $LedgerSheetInventory = new LedgerSheetInventory();
            // システムマスタ：賞味期限利用区分の取得
            $bb_date_kbn = $LedgerSheetInventory->chk_BB_DATE_KBN();
            
           
            // 日付リストを取得
            $startDateM = parent::escStr( $_POST['start_dateM'] );
            $startDateM = parent::escStr( $_POST['start_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDateM) OR $startDateM == ""){
                //現在日付の１日を設定
                $startDateM = date('Y/m', strtotime('first day of ' . $month));
            }

            $modal = new Modal();
            // 店舗リスト取得
            
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            
            // 商品リスト取得
            $prod_detail    = [];
//            $prod_detail    = $modal->getprod_detail();
            
            // 部門リスト取得
            $sect_detail    = [];
            $sect_detail    = $modal->getsect_detail();

            // 分類リスト取得
            $prodclass_detail    = [];
            $prodclass_detail    = $modal->getprod_class_detail();
            if(!isset($prodclass_detail['ooki'])){
                    $prodclass_detail['ooki']    = [];
            }
            if(!isset($prodclass_detail['naka'])){
                    $prodclass_detail['naka']    = [];
            }			
            
            // 仕入先入リスト取得
            $supp_detail    = [];
            $supp_detail    = $modal->getsupp_detail();
            
            // パラメータ変数定義
            $param          = [];
            
            // POSTパラメーター
            if($_POST){
                $param = $_POST;
            }
            
            // 各種変数定義
            $param["org_id"]    = "";
            $param["prod_cd"]   = "";
            $param["sect_cd"]   = "";
            $param["prodclass_cd"]   = "";
            $param["supp_cd"]   = "";
            
            $searchArray = array();
            
            $organizationId = 'false';
            
            // 店舗リスト
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
            
            // 店舗選択
            if($_POST['tenpo']){
                $organizationId = 'compile';
            }

            // 商品コード
            $prod_cd = 'false';
            if($_POST['prod_r'] === ""){
                if($_POST['prod_select'] && $_POST['prod_select'] === 'empty'){
                    $prod_cd = $_POST['prod_cd'];
                }else{
                    $prod_cd = "'".$_POST['prod_select']."'";
                }
            }

            //部門
            $sect_cd = 'false';
            if($_POST['sect_r'] === ""){
                if($_POST['sect_select'] && $_POST['sect_select'] === 'empty'){
                    $sect_cd = $_POST['sect_cd'];
                }else{
                    $sect_cd = "'".$_POST['sect_select']."'";
                }
            }
//ADDSTR 2020/06 kanderu*************************************************************************
            // アクセス権限一覧レコード数
//            $sectionRecordCnt = count($accessAllList);

            // 表示レコード数
//            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
            // $_POST['pageNo'] = '0';
            //  $_POST['pageNo'] = 0;
            // $pageMaxNo='0';
                $pageNo = $_POST['pageNo'];  	
             if($pageNo === 0 || $pageNo === ''){
                $pageNo =  $this->getDuringTransitionPageNo($pagedRecordCnt);
             }
             
             $pagedRecordCnt = 0;
            
             if( $pagedRecordCnt = $_POST['limit_r']){
                 $btn_next=$_POST['limit_r'];   
             }
            
            if($pagedRecordCnt === 0 || $pagedRecordCnt === ''){
              //  $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
                 $pagedRecordCnt = 5000;

            }
           // $pageNo=0;


            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($List) >= 11)
            {
                $isScrollBar = true;
            }
            $isScrollBar = false;
//ADDSEND 2020/06 kanderu*************************************************************************
            // 分類
            $prodclass_cd = 'false';
            if($_POST['prodclass_r'] === ""){
                if($_POST['prodclass_select'] && $_POST['prodclass_select'] === 'empty'){
                    $prodclass_cd = $_POST['prodclass_cd'];
                }else{
                    $prodclass_cd = "'".$_POST['prodclass_select']."'";
                }
            }
            // 仕入
            $supp_cd = 'false';
            if($_POST['supp_r'] === ""){
                if($_POST['supp_select'] && $_POST['supp_select'] === 'empty'){
                    $supp_cd = $_POST['supp_cd'];
                }else{
                    $supp_cd = "'".$_POST['supp_select']."'";
                }
            }
            // 賞味期限表示区分
            if ($bb_date_kbn === '1'){
                // 賞味期限は利用しない設定故、表示もしない
                $bb_date_dspkbn = '1';
            }
            else{
                // 既定値は0(表示する)としておく
                $bb_date_dspkbn = isset($_POST['bb_date_dspkbn']) ? parent::escStr($_POST['bb_date_dspkbn']) : '0';
            }
            
            // 在庫数範囲指定
            $amount_str = isset($_POST['amount_str']) ? parent::escStr($_POST['amount_str']) : '';
            $amount_end = isset($_POST['amount_end']) ? parent::escStr($_POST['amount_end']) : '';

            // 在庫差異あり商品(0:全て / 1:差異あり / 2:差異なし)既定値は全てとしておく
            $amount_kbn = isset($_POST['amount_kbn']) ? parent::escStr($_POST['amount_kbn']) : '0';

            // 在庫ゼロ商品(0:全て / 1:在庫ありのみ / 2:在庫0のみ)既定値は全てとしておく
            $zero_kbn = isset($_POST['zero_kbn']) ? parent::escStr($_POST['zero_kbn']) : '0';

            // 売単価表示(0:税込 / 1:税抜)　既定値は0(税込)としておく
            $saleprice_kbn = isset($_POST['saleprice_kbn']) ? parent::escStr($_POST['saleprice_kbn']) : '0';
            
            //MAXページを計算する
           // $pageMaxNo=0;
                $searchArray0 = array(
                'organization_id'   => $organizationId,
                'start_date_m'      => $startDateM,
                'prod_cd'           => $prod_cd,
                'sect_cd'           => $sect_cd,
                'bb_date_kbn'       => $bb_date_kbn,
                'bb_date_dspkbn'    => $bb_date_dspkbn,
                'amount_kbn'        => $amount_kbn,
                'zero_kbn'          => $zero_kbn,
                'amount_str'        => str_replace(',', '', $amount_str),
                'amount_end'        => str_replace(',', '', $amount_end),
                'saleprice_kbn'     => $saleprice_kbn,
                'prodclass_cd'      => $prodclass_cd,
                'supp_cd'           => $supp_cd, );
             
            $pageMaxNo = $LedgerSheetInventory->pageMaxNo($searchArray0,$searchArray);
            
//            $pageMaxNo=$pageMaxNo[0]["rownos"] / $pagedRecordCnt;
            
            if($pageMaxNo[0]["rownos"] > 0){
                $round_up = $pageMaxNo[0]["rownos"] / $pagedRecordCnt;
                $pageMaxNo = ceil(round($round_up,2,PHP_ROUND_HALF_UP));
                //$pageMaxNo+1;
            }
           // $ledgerSheetDetailList
//            if ($pageMaxNo= "") { 


            //$pageMaxNo=0;
            // 検索条件設定 
            $searchArray = array(
                'organization_id'   => $organizationId,
                'start_date_m'      => $startDateM,
                'prod_cd'           => $prod_cd,
                'sect_cd'           => $sect_cd,
                'bb_date_kbn'       => $bb_date_kbn,
                'bb_date_dspkbn'    => $bb_date_dspkbn,
                'amount_kbn'        => $amount_kbn,
                'zero_kbn'          => $zero_kbn,
                'amount_str'        => str_replace(',', '', $amount_str),
                'amount_end'        => str_replace(',', '', $amount_end),
                'saleprice_kbn'     => $saleprice_kbn,
                'prodclass_cd'      => $prodclass_cd,
                'supp_cd'           => $supp_cd,
                'sort'              => $sort,
////ADDSTR 2020/06 kanderu*************************************************************************
                'limit'                     => $pagedRecordCnt,
               'offset'                    => ($pageNo - 1  )* $pagedRecordCnt,
////ADDEND 2020/06 kanderu*************************************************************************
            );

            // 初期表示かどうかの確認
            if($mode != "initial")
            {
                //データの取得
              $list = $LedgerSheetInventory -> getListData($searchArray);
             // $pageMaxNo = $LedgerSheetInventory -> pageMaxNo($searchArray); 
                // 各種変数定義
                $organization_id_bef = '';
                $prod_cd_bef = '';
              
                // 店舗合計
                $sum_rea_amount              = 0; // 実棚数量(A)
                $sum_difference              = 0; // 実績差異
                $sum_costtotal_honbu         = 0.0; // 本部実棚金額(D=A×B)
                $sum_costtotal               = 0.0; // 店舗実棚金額(E=A×C)
                $sum_saletotal               = 0; // 実棚売価金額
                $sum_cost_difference         = 0.0; // 原価差額(B-C)	
                $sum_actual_shelf_difference = 0.0; // 実棚差額(D-E)		

                // 総合計
                $sumall_rea_amount              = 0; // 実棚数量(A)
                $sumall_difference              = 0; // 実績差異
                $sumall_costtotal_honbu         = 0.0; // 本部実棚金額(D=A×B)
                $sumall_costtotal               = 0.0; // 店舗実棚金額(E=A×C)
                $sumall_saletotal               = 0; // 実棚売価金額
                $sumall_cost_difference         = 0.0; // 原価差額(B-C)	
                $sumall_actual_shelf_difference = 0.0; // 実棚差額(D-E)		

                $ledgerSheetDetailList = array();
                
                // 首都データで合計計算 +α
                for ($intL = 0; $intL < count($list); $intL ++){
                    
                    if (isset($list[$intL]['organization_id']) === true){
                        $organization_id = intval($list[$intL]['organization_id']);
                    }
                    else{
                        $organization_id = '';
                    }
                    
                    $prod_cd = $list[$intL]['prod_cd'];

                    // 店舗が変わったら実施
                    if ($organization_id !== $organization_id_bef){
                        
                        // 合計行の追加処理
                        if ($intL > 0){
                            // 店舗合計行
                            $aryAddRow = array();
                            $aryAddRow['abbreviated_name']          = '合計';
                            $aryAddRow['rea_amount']                = $sum_rea_amount;               // 実棚数量(A)
                            $aryAddRow['difference']                = $sum_difference;               // 実績差異
                            $aryAddRow['costtotal_honbu']           = $sum_costtotal_honbu;          // 本部実棚金額(D=A×B)
                            $aryAddRow['costtotal']                 = $sum_costtotal;                // 店舗実棚金額(E=A×C)
                            $aryAddRow['saletotal']                 = $sum_saletotal;                // 実棚売価金額
                            $aryAddRow['cost_difference']           = $sum_cost_difference;          // 原価差額(B-C)
                            $aryAddRow['actual_shelf_difference']   = $sum_actual_shelf_difference;  // 実棚差額(D-E)
                            $aryAddRow['record_type'] = 'S';    // Summary
                            // 行追加
                            if(!$_POST['tenpo']){
                                array_push($ledgerSheetDetailList, $aryAddRow);
                            }
                        }
                        // 店舗合計のクリア
                        $sum_rea_amount              = 0; // 実棚数量(A)
                        $sum_difference              = 0; // 実績差異
                        $sum_costtotal_honbu         = 0.0; // 本部実棚金額(D=A×B)
                        $sum_costtotal               = 0.0; // 店舗実棚金額(E=A×C)
                        $sum_saletotal               = 0; // 実棚売価金額
                        $sum_cost_difference         = 0.0; // 原価差額(B-C)	
                        $sum_actual_shelf_difference = 0.0; // 実棚差額(D-E)		

                        // 次店舗の行が同一商品で始まったらコードと名称が表示されないので初期化
                        $prod_cd_bef = '';
                    }

                    // 取得したリストを初期セット
                    $aryAddRow = $list[$intL];
                    $organization_id = intval($list[$intL]['organization_id']);
                    
                    // 店舗が変わったときは小計行になるので店舗名を空白にする
                    if ($intL > 0 && $organization_id === $organization_id_bef){
                        $aryAddRow['abbreviated_name'] = '';
                    }

                    // 企業計モードの時は店舗名部分に企業計を設定
                    if($_POST['tenpo'] && $intL === 0){
                        $aryAddRow['abbreviated_name'] = '企業計';
                    }

                    // 店舗が変わったときは小計行になるので各内容のを空白にする
                    if ($prod_cd === $prod_cd_bef){
                        $aryAddRow['shelf_no_fst'] = '';
                        $aryAddRow['shelf_no_lst'] = '';
                        $aryAddRow['shelf_no_otr'] = '';
                        $aryAddRow['bb_date'] = '';
                        $aryAddRow['prod_cd'] = '';
                        $aryAddRow['prod_nm'] = '';
                        $aryAddRow['prod_tax'] = '';
                        $aryAddRow['supp_cd'] = '';
                        $aryAddRow['supp_nm'] = '';
                        $aryAddRow['sect_cd'] = '';
                        $aryAddRow['sect_nm'] = '';
                        $aryAddRow['prod_t_cd1'] = '';
                        $aryAddRow['prod_t_nm1'] = '';
                        $aryAddRow['prod_t_cd2'] = '';
                        $aryAddRow['prod_t_nm2'] = '';
                        $aryAddRow['prod_t_cd3'] = '';
                        $aryAddRow['prod_t_nm3'] = '';
                        $aryAddRow['prod_t_cd4'] = '';
                        $aryAddRow['prod_t_nm4'] = '';
                        $aryAddRow['priv_class_cd'] = '';
                        $aryAddRow['priv_class_nm'] = '';
                        $aryAddRow['jicfs_class_cd'] = '';
                        $aryAddRow['jicfs_class_nm'] = '';
                    }
                    
                    // リストの生成
                    $aryAddRow['record_type'] = 'D';    // Detail

                    // 行追加
                    array_push($ledgerSheetDetailList, $aryAddRow);
                    
                    // 店舗合計に加算
                    $sum_rea_amount              += $list[$intL]['rea_amount'];
                    $sum_difference              += $list[$intL]['difference'];
                    $sum_costtotal_honbu         += $list[$intL]['costtotal_honbu'];
                    $sum_costtotal               += $list[$intL]['costtotal'];
                    $sum_saletotal               += $list[$intL]['saletotal'];
                    $sum_cost_difference         += $list[$intL]['cost_difference'];	
                    $sum_actual_shelf_difference += $list[$intL]['actual_shelf_difference'];
                
                    // 企業計に加算
                    $sumall_rea_amount              += $list[$intL]['rea_amount'];
                    $sumall_difference              += $list[$intL]['difference'];
                    $sumall_costtotal_honbu         += $list[$intL]['costtotal_honbu'];
                    $sumall_costtotal               += $list[$intL]['costtotal'];
                    $sumall_saletotal               += $list[$intL]['saletotal'];
                    $sumall_cost_difference         += $list[$intL]['cost_difference'];	
                    $sumall_actual_shelf_difference += $list[$intL]['actual_shelf_difference'];

                    // 値保持
                    $organization_id_bef = $organization_id;
                    $prod_cd_bef = $prod_cd;
                }
                // ループ脱出後
                if (count($list) > 0){
                    // 店舗合計行
                    $aryAddRow = array();
                    $aryAddRow['abbreviated_name']           = '合計';
                    $aryAddRow['rea_amount']                 = $sum_rea_amount;
                    $aryAddRow['difference']                 = $sum_difference;
                    $aryAddRow['costtotal_honbu']            = $sum_costtotal_honbu;
                    $aryAddRow['costtotal']                  = $sum_costtotal;
                    $aryAddRow['saletotal']                  = $sum_saletotal;
                    $aryAddRow['cost_difference']            = $sum_cost_difference;
                    $aryAddRow['actual_shelf_difference']    = $sum_actual_shelf_difference;
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
                    $aryAddRow['abbreviated_name']           = '合計';
                    $aryAddRow['rea_amount']                 = $sumall_rea_amount;
                    $aryAddRow['difference']                 = $sumall_difference;
                    $aryAddRow['costtotal_honbu']            = $sumall_costtotal_honbu;
                    $aryAddRow['costtotal']                  = $sumall_costtotal;
                    $aryAddRow['saletotal']                  = $sumall_saletotal;
                    $aryAddRow['cost_difference']            = $sumall_cost_difference;
                    $aryAddRow['actual_shelf_difference']    = $sumall_actual_shelf_difference;
                    $aryAddRow['record_type'] = 'F';    // Footer
                    // 行追加
                    array_push($ledgerSheetDetailList, $aryAddRow);
                }
            }

            // 検索組織
            $searchArray = array(
                'org_id'            => str_replace("'","",$_POST['org_id']),
                'prod_cd'           => str_replace("'","",$_POST['prod_cd']),
                'sect_cd'           => str_replace("'","",$_POST['sect_cd']),
                'bb_date_kbn'       => $bb_date_kbn,
                'bb_date_dspkbn'    => $bb_date_dspkbn,
                'zero_kbn'          => $zero_kbn,
                'amount_kbn'        => $amount_kbn,
                'amount_str'        => $amount_str,
                'amount_end'        => $amount_end,
                'saleprice_kbn'     => $saleprice_kbn,
                'prodclass_cd'      => str_replace("'","",$_POST['prodclass_cd']),
                'supp_cd'           => str_replace("'","",$_POST['supp_cd']),
                'sort'              => $sort,
            );
////ADDSTR 2020/06 kanderu*************************************************************************            
             // 表示レコード数
//           $pagedRecordCnt = 200;



       

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 15 && count($userList) >= 15)
            {
                $isScrollBar = true;
            }
//            //--------------------------------------------------------------->


//            // 表示数リンクのロック処理
//            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
////            // ページ数
//            $pagedCnt = ceil($userRecordCnt /  $pagedRecordCnt);
//            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
//            // ソートマーク初期化
////ADDEND 2020/06 kanderu*************************************************************************
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
                require_once './View/LedgerSheetInventoryPanel.html';
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
            header("Content-Disposition: attachment; filename=棚卸一覧表".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");
//DELSTR 2020/06  kanderu*************************************************************************            
            // レスポンス対策のため、画面をリロードする
           // require_once './View/LedgerSheetInventoryPanel.html';
//DELEND 2020/06  kanderu*************************************************************************       
        }

        public function getproddataAction(){
            global $Log;  // グローバル変数宣言
            $Log->trace("START getproddataAction");  
            $modal = new Modal();
            $prod_detail    = $modal->getprod_detail(); 
            print_r(json_encode($prod_detail));
            $Log->trace("END   getproddataAction");
        }
    
    }    
    
?>
