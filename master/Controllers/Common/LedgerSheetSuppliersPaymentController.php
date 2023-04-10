<?php
    /**
     * @file      帳票 - 仕入先・店舗別支払一覧表コントローラ
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 仕入先・店舗別支払一覧表の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetSuppliersPayment.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetSuppliersPaymentController extends BaseController
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

            $ledgerSheetSuppliersPayment = new ledgerSheetSuppliersPayment();

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-P');

            //テスト用
            //$html = file_get_contents("./View/LedgerSheetSuppliersPaymentPDFPanel.html");

            $saledYear = parent::escStr( $_POST['saled_year'] );
            $startCode  = parent::escStr( $_POST['start_code'] );
            $endCode  = parent::escStr( $_POST['end_code'] );
            $searchArray = array(
                'saled_year' => $saledYear,
                'start_code' => $startCode,
                'end_code'   => $endCode,
            );

            $list = $ledgerSheetSuppliersPayment->getFormListData($searchArray);

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

            $ledgerSheetSuppliersPayment = new ledgerSheetSuppliersPayment();
            //test
            $test = $ledgerSheetSuppliersPayment->test();
            $abbreviatedNameList = $ledgerSheetSuppliersPayment->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

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
//ADDSTR 2019.03.26 モンターニュ　@m4                             
            $aggr=false;
            if($_POST['aggr']){
                $aggr=true;
            }
            $supp_nm_start = '';
            if($_POST['supp_nm_start']){
                $supp_nm_start = $_POST['$supp_nm_start'];
            }
            $supp_nm_end = '';
            if($_POST['supp_nm_end']){
                $supp_nm_end = $_POST['$supp_nm_end'];
            }
//ADDEND 2019.03.26 モンターニュ　@m4                 
            $organizationId   = parent::escStr( $_POST['organizationName'] );
            $prodCdStart   = parent::escStr( $_POST['prod_cd_start'] );
            $prodCdEnd   = parent::escStr( $_POST['prod_cd_end'] );
            $suppCdStart   = parent::escStr( $_POST['supp_cd_start'] );
            $suppCdEnd   = parent::escStr( $_POST['supp_cd_end'] );
//ADDSTR 2019.03.26 モンターニュ　@m4                             
            $supp_data   = $ledgerSheetSuppliersPayment->get_mst1101_data();
//ADDEND 2019.03.26 モンターニュ　@m4                             
            
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'organizationID'    => $organizationId,
                'prod_cd_start'     => $prodCdStart,
                'prod_cd_end'       => $prodCdEnd,
                'supp_cd_start'     => $suppCdStart,
                'supp_cd_end'       => $suppCdEnd,
//ADDSTR 2019.03.26 モンターニュ　@m4                 
                'aggr'              => $aggr,
                'supp_nm_start'     => $supp_nm_start,
                'supp_nm_end'       => $supp_nm_end,
//ADDEND 2019.03.26 モンターニュ　@m4                                 
            );
            
            if($mode != "initial") {
                // 帳票フォーム一覧データ取得
                $ledgerSheetDetailList = $ledgerSheetSuppliersPayment->getFormListData($searchArray);
                //print_r($ledgerSheetDetailList);
                // 合計計算用変数
//pme 2019.03.11
//                $sumPayMoney = 0;
//                $sumPayMoneyRate = 0;
//                $sumPaymentDueDate = 0;
//                $sumLastPaymentBalance = 0;
//                $sumPaymentValueReduction = 0;
//                $sumCarryforwardAmount = 0;
//                $sumSumcostMoney = 0;
//                $sumPayMoneyZan = 0;
                $sumCostMoney = 0;
                $sumDiscMoney = 0;
                $sumCount = 0;                
                $sumReturnMoney = 0;
                $sumTax = 0;
                $sumtotal = 0;
                $sumsubtot1 = 0;
                $sumbef_balance = 0;
                $sumsubtot2 = 0;
                $supp_cd = "";
                

                if(count($ledgerSheetDetailList) > 0){
                    // 合計行を取得
                    foreach($ledgerSheetDetailList as $data){
                        //$sumPayMoney     = $sumPayMoney + $data["pay_money"];
                       // $sumPayMoneyRate = $sumPayMoneyRate + $data["pay_money_rate"];
                        if($supp_cd !==  $data["supp_cd"]){
                            $sumCostMoney    = $sumCostMoney + $data["cost_money"];
                            $sumCount        = $sumCount + $data["count"];
                            $sumTax = $sumTax + $data["tax"];                            
                        }
                        $sumDiscMoney    = $sumDiscMoney + $data["disc_money"];
                        
                        //$sumPaymentDueDate     = $sumPaymentDueDate + $data["payment_due_date"];
                        //$sumLastPaymentBalance = $sumLastPaymentBalance + $data["last_payment_balance"];
                        //$sumPaymentValueReduction = $sumPaymentValueReduction + $data["payment_value_reduction"];
                        //$sumCarryforwardAmount = $sumCarryforwardAmount + $data["carryforward_amount"];
                        $sumReturnMoney = $sumReturnMoney + $data["return_money"];
                        //$sumSumcostMoney = $sumSumcostMoney + $data["sumcost_money"];
                        
                        //$sumPayMoneyZan = $sumPayMoneyZan + $data["pay_money_zan"];
                        $sumtotal = $sumtotal + $data["total"];
                        $sumsubtot1 = $sumsubtot1 + $data["subtot1"];
                        $sumbef_balance = $sumbef_balance + $data["bef_balance"];
                        $sumsubtot2 = $sumsubtot2 + $data["subtot2"];                        
                        $supp_cd =  $data["supp_cd"];
                    }
//                    if (empty($sumAmount)) {
//                        $sumAmount = null;
//                    }
//                    if (empty($sumSaleprice)) {
//                        $sumSaleprice = null;
//                    }
//                    if (empty($sumCosttotal)) {
//                        $sumCosttotal = null;
//                    }

                    $sumLine = array(
                        'supp_cd'                 => '',            // 仕入先cd
                        'supp_nm'                 => '',            // 仕入先名
                        'organization_id'         => '',            // 部門分類cd
                        'abbreviated_name'        => '合計',        // 部門分類名
                        ''                        => '',            // 年月
                        //'pay_money'               => $sumPayMoney,  // 部門cd
                        //'pay_money_rate'          => '',
                        'cost_money'              => $sumCostMoney,  // 商品cd
                        'disc_money'              => $sumDiscMoney,  // 商品名
                        'count'                   => $sumCount,      // 合計名称
                        //'payment_due_date'        => '',   // 売価金額
                        //'last_payment_balance'    => $sumLastPaymentBalance,   // 構成比
                        //'payment_value_reduction' => $sumPaymentValueReduction,  // 数量
                        //'carryforward_amount'     => $sumCarryforwardAmount,  // 構成比
                        'return_money'            => $sumReturnMoney,  // 一品単価
                        //'sumcost_money'           => $sumSumcostMoney,      // 客数
                        'tax'                     => $sumTax,      // 消費税数
                        //'pay_money_zan'           => $sumPayMoneyZan,      // 客数
                        'total'                 =>  $sumtotal,
                        'subtot1'               =>  $sumsubtot1 ,
                        'bef_balance'           =>  $sumbef_balance ,
                        'subtot2'               =>  $sumsubtot2 
                        );
                    //pme end 2019.03.11
//DELSTR 2019.03.26 モンターニュ　@m4 
                    // 合計行を追加
                    //array_push($ledgerSheetDetailList, $sumLine);
//DELEND 2019.03.26 モンターニュ　@m4                     
                }

            }

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
                require_once './View/LedgerSheetSuppliersPaymentPanel.html';
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
            
            $ledgerSheetSuppliersPayment = new ledgerSheetSuppliersPayment();

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

            $organizationId   = parent::escStr( $_POST['organizationName'] );
            $prodCdStart   = parent::escStr( $_POST['prod_cd_start'] );
            $prodCdEnd   = parent::escStr( $_POST['prod_cd_end'] );
            $suppCdStart   = parent::escStr( $_POST['supp_cd_start'] );
            $suppCdEnd   = parent::escStr( $_POST['supp_cd_end'] );
            
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'organizationID'    => $organizationId,
                'prod_cd_start'     => $prodCdStart,
                'prod_cd_end'       => $prodCdEnd,
                'supp_cd_start'     => $suppCdStart,
                'supp_cd_end'       => $suppCdEnd,
            );
            
            // 帳票フォーム一覧データ取得
            $ledgerSheetDetailList = $ledgerSheetSuppliersPayment->getFormListData($searchArray);

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
//EDTSTR 2019.03.26 モンターニュ　@m4                 
            // 先頭固定ヘッダ追加
//            array_push($export_csv_title, "No");
//            array_push($export_csv_title, "仕入先コード");
//            array_push($export_csv_title, "仕入先");
//            array_push($export_csv_title, "店舗");
//            array_push($export_csv_title, "年月");
//            array_push($export_csv_title, "今回支払額");
//            //array_push($export_csv_title, "支払率");
//            array_push($export_csv_title, "今回仕入額");
//            array_push($export_csv_title, "値引額");
//            array_push($export_csv_title, "伝票枚数");
//            //array_push($export_csv_title, "支払予定日");
//            array_push($export_csv_title, "前回支払残高");
//            //array_push($export_csv_title, "支払値引額");
//            //array_push($export_csv_title, "繰越額");
//            array_push($export_csv_title, "返品金額");
//            array_push($export_csv_title, "純仕入額");
//            array_push($export_csv_title, "消費税");
//            array_push($export_csv_title, "今回支払残高");
//            

            array_push($export_csv_title, "仕入先コード");
            array_push($export_csv_title, "仕入");
            array_push($export_csv_title, "店舗");
            array_push($export_csv_title, "前回支払残高");
            array_push($export_csv_title, "今回支払額");
            array_push($export_csv_title, "今回支払値引");
            array_push($export_csv_title, "今回仕入額");
            array_push($export_csv_title, "値引");
            array_push($export_csv_title, "返品");
            array_push($export_csv_title, "伝票消費税");
            array_push($export_csv_title, "伝票枚数");
            array_push($export_csv_title, "今回支払残高");           
//EDTEND 2019.03.26 モンターニュ　@m4                 
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
//EDTSTR 2019.03.26 モンターニュ　@m4                                 
//                $list_no = 0;               
//                foreach( $ledgerSheetDetailList as $rows ){
//                    
//                    // 垂直ヘッダ
//                    $list_no += 1;
//
//                    $str = '"'.$list_no;// 順位
//                    
//                    $str = $str.'","'.$rows['supp_cd'];
//                    $str = $str.'","'.$rows['supp_nm'];
//                    $str = $str.'","'.$rows['abbreviated_name'];
//                    $str = $str.'","'.wordwrap($rows['proc_ym'],4,'/', true);
//                    $str = $str.'","'.round($rows['subtot2'],0);
////                    if($rows['pay_money'] == ""){
////                        $str = $str.'","0';
////                    }else{
////                        $str = $str.'","'.$rows['pay_money'];
////                    }
////                    $str = $str.'","'.$rows['pay_money_rate'];
//                    $str = $str.'","'.round($rows['cost_money'],0);
//                    $str = $str.'","'.round($rows['disc_money'],0);
//                    $str = $str.'","'.$rows['count'];
//                    $str = $str.'","'.round($rows['bef_balance'],0);
//                    //$str = $str.'","'.$rows['payment_due_date'];
//                    //$str = $str.'","'.round($rows['last_payment_balance'],0);
//                    //$str = $str.'","'.round($rows['payment_value_reduction'],0);
//                    //$str = $str.'","'.round($rows['carryforward_amount'],0);
//                    $str = $str.'","'.round($rows['return_money'],0);
//                    $str = $str.'","'.round($rows['subtot1'],0);
//                    //$str = $str.'","'.round($rows['sumcost_money'],0);
//                    $str = $str.'","'.round($rows['tax'],0);
//                    $str = $str.'","'.round($rows['total'],0);
////                    if($rows['pay_money_zan'] == ""){
////                         $str = $str.'","0';
////                    }else{
////                        $str = $str.'","'.round($rows['pay_money_zan'],0);
////                    }
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
                
                $prev_supp_cd               = "";
                $prev_supp_nm               = "";
                $sum_bef_balance_val 	= 0;	
                $sum_subtot2_val            = 0;		
                $sum_disc_money_val 	= 0;		
                $sum_cost_money_val 	= 0;		
                $sum_mon_disc_total_val     = 0;	
                $sum_return_total_val 	= 0;	
                $sum_tax_val 		= 0;			
                $sum_count_val 		= 0;			
                $sum_mon_balance_val        = 0;                  
                if(!$_POST['aggr']){
                    foreach( $ledgerSheetDetailList as $rows ){
                        $str = '"'.$rows['supp_cd'];
                        $str = $str.'","'.$rows['supp_nm'];
                        $str = $str.'","'.$rows['abbreviated_name'];
                        $str = $str.'","'.round($rows['bef_balance'],0);
                        $str = $str.'","'.round($rows['subtot2'],0);
                        $str = $str.'","'.round($rows['disc_money'],0);
                        $str = $str.'","'.round($rows['cost_money'],0);
                        $str = $str.'","'.round($rows['mon_disc_total'],0);
                        $str = $str.'","'.round($rows['return_total'],0);
                        $str = $str.'","'.round($rows['tax'],0);
                        $str = $str.'","'.round($rows['count'],0);
                        $str = $str.'","'.round($rows['mon_balance'],0);
                        $str = $str.'"';

                        // 配列に変換
                        $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
                        //$export_arr = explode(",", $str);

                        // 内容行を1行ごとにCSVデータへ
                        $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");

                    }
                }else{
                    foreach( $ledgerSheetDetailList as $rows ){
                        if($rows['supp_cd'] !== $prev_supp_cd && $prev_supp_cd !== ""){   

                            $supp_cd_val            = $prev_supp_cd;
                            $supp_nm_val            = $prev_supp_nm;
                            $abbreviated_name_val   = '企業計';
                            $bef_balance_val        = $sum_bef_balance_val; 	
                            $subtot2_val            = $sum_subtot2_val;
                            $disc_money_val         = $sum_disc_money_val; 	
                            $cost_money_val         = $sum_cost_money_val;	
                            $mon_disc_total_val     = $sum_mon_disc_total_val; 
                            $return_total_val       = $sum_return_total_val; 	
                            $tax_val                = $sum_tax_val; 			
                            $count_val              = $sum_count_val; 			
                            $mon_balance_val        = $sum_mon_balance_val;	

                            $sum_bef_balance_val 	= 0;	
                            $sum_subtot2_val        = 0;		
                            $sum_disc_money_val 	= 0;		
                            $sum_cost_money_val 	= 0;		
                            $sum_mon_disc_total_val = 0;	
                            $sum_return_total_val 	= 0;	
                            $sum_tax_val 		= 0;			
                            $sum_count_val 		= 0;			
                            $sum_mon_balance_val    = 0; 

                            $str = '"'.$supp_cd_val;
                            $str = $str.'","'.$supp_nm_val;
                            $str = $str.'","'.$abbreviated_name_val;
                            $str = $str.'","'.round($bef_balance_val,0);
                            $str = $str.'","'.round($subtot2_val,0);
                            $str = $str.'","'.round($disc_money_val,0);
                            $str = $str.'","'.round($cost_money_val,0);
                            $str = $str.'","'.round($mon_disc_total_val,0);
                            $str = $str.'","'.round($return_total_val,0);
                            $str = $str.'","'.round($tax_val,0);
                            $str = $str.'","'.round($count_val,0);
                            $str = $str.'","'.round($mon_balance_val,0);
                            $str = $str.'"';
                            // 配列に変換
                            $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
                            //$export_arr = explode(",", $str);

                            // 内容行を1行ごとにCSVデータへ
                            $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");            
                        }
                        $sum_bef_balance_val 	+= $rows['bef_balance'];	
                        $sum_subtot2_val            += $rows['subtot2'];		
                        $sum_disc_money_val 	+= $rows['disc_money'];		
                        $sum_cost_money_val 	+= $rows['cost_money'];		
                        $sum_mon_disc_total_val     += $rows['mon_disc_total'];	
                        $sum_return_total_val 	+= $rows['return_total'];	
                        $sum_tax_val 		+= $rows['tax'];			
                        $sum_count_val 		+= $rows['count'];			
                        $sum_mon_balance_val 	+= $rows['mon_balance'];                            
                        $prev_supp_cd = $rows['supp_cd'];
                        $prev_supp_nm = $rows['supp_nm'];         
                    }
                    $supp_cd_val            = $prev_supp_cd;
                    $supp_nm_val            = $prev_supp_nm;
                    $abbreviated_name_val   = '企業計';
                    $bef_balance_val        = $sum_bef_balance_val; 	
                    $subtot2_val            = $sum_subtot2_val;
                    $disc_money_val         = $sum_disc_money_val; 	
                    $cost_money_val         = $sum_cost_money_val;	
                    $mon_disc_total_val     = $sum_mon_disc_total_val; 
                    $return_total_val       = $sum_return_total_val; 	
                    $tax_val                = $sum_tax_val; 			
                    $count_val              = $sum_count_val; 			
                    $mon_balance_val        = $sum_mon_balance_val;  

                    $str = '"'.$supp_cd_val;
                    $str = $str.'","'.$supp_nm_val;
                    $str = $str.'","'.$abbreviated_name_val;
                    $str = $str.'","'.round($bef_balance_val,0);
                    $str = $str.'","'.round($subtot2_val,0);
                    $str = $str.'","'.round($disc_money_val,0);
                    $str = $str.'","'.round($cost_money_val,0);
                    $str = $str.'","'.round($mon_disc_total_val,0);
                    $str = $str.'","'.round($return_total_val,0);
                    $str = $str.'","'.round($tax_val,0);
                    $str = $str.'","'.round($count_val,0);
                    $str = $str.'","'.round($mon_balance_val,0);
                    $str = $str.'"';
                    // 配列に変換
                    $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
                    //$export_arr = explode(",", $str);

                    // 内容行を1行ごとにCSVデータへ
                    $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");    

                }                

//EDTEND 2019.03.26 モンターニュ　@m4                  
                // ダウンロード用
                header("Pragma: public"); // required
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private",false); // required for certain browsers 
                header("Content-Type: application/force-download");
                header("Content-Disposition: attachment; filename=仕入先・店舗別支払一覧".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
                header("Content-Transfer-Encoding: binary");
                readfile("$file_path");
                $Log->trace("END   csvoutputAction");
            }
        }
    }
?>
