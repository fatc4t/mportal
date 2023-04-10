<?php
    /**
     * @file      帳票 - 日次コントローラ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 日次の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetDetails.php';
    require '../attendance/Model/AggregateLaborCosts.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetDetailsController extends BaseController
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

            $ledgerSheetDetails = new ledgerSheetDetails();

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-P');

            //テスト用
            //$html = file_get_contents("./View/LedgerSheetDetailsPDFPanel.html");

            $saledYear = parent::escStr( $_POST['saled_year'] );
            $startCode  = parent::escStr( $_POST['start_code'] );
            $endCode  = parent::escStr( $_POST['end_code'] );
            $searchArray = array(
                'saled_year' => $saled_year,
                'start_code' => $startCode,
                'end_code'   => $endCode,
            );

            $list = $ledgerSheetDetails->getFormListData($searchArray);

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

            //開始年
            $startYear = "2010";

            $ledgerSheetDetails = new ledgerSheetDetails();

            //$abbreviatedNameList = $ledgerSheetDetails->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト
            // modal
            $modal = new Modal();
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            $cust_detail    = [];
            $cust_detail    = $modal->getcust_detail();            
            $param          = [];
            if($_POST){
                $param = $_POST;
            }
            $param["cust_cd"]   = "";
            $param["org_id"]    = "";           

            // 日付リストを取得
            $startDate = $_POST['start_date'] ;
            $endDate = $_POST['end_date'] ;

            //modal
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
   
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'cust_cd'           => $cust_cd,
                'organization_id'   => $organizationId,
            );

            if($mode != "initial")
            {
                // 帳票フォーム一覧データ取得
                $ledgerSheetDetailList = $ledgerSheetDetails->getFormListData($searchArray);

                   // 合計計算用変数
    
                   $sumAmount = 0;
                   $totalMoney = 0;
                   $sumPnt1 = 0;       
                   $sumPnt2 = 0;
                   $sumHiki = 0;
                   $sumSale = 0;
                   
                   // 合計行を取得
                   foreach($ledgerSheetDetailList as $data){
                       $sumAmount  += $data["amount"];
                       $totalMoney  += $data["saletotal"];
                       $sumPnt1  += $data["point"];
                       $sumPnt2  += $data["point_r"];
                       $sumHiki += $data['disctotal'];
                       $sumSale += $data['saletotal'];
                       
                   
                   $sumLine = array(
                       'No'                 => '',       
                       'cust_cd'            => '',
                       'cust_nm'            => '',    
                       'trndate'            => '',   
                       'account_no'         => '合計',   
                       'prod_cd'            => '',    
                       'prod_nm'      	    => '',
                       'prod_capa'          => '',      
                       'saleprice'          => '',
                       'point'              => $sumPnt1,               
                       'point_r'            => $sumPnt2,
                       'amount'             => $sumAmount, 
                       'disctotal'          => $sumHiki,
                       'saletotal'          => $sumSale,
                       'abbreviated_name'   => '',
                       );
           }
           // 合計行を追加
           array_push($ledgerSheetDetailList, $sumLine);
        
        }
            // 検索組織
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'org_id'            => str_replace("'","",$_POST['org_id']),
                'cust_cd'           => str_replace("'","",$_POST['cust_cd']),
                //'sort'              => $sort,
            ); 
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetDetailsPanel.html';
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
            $ledgerSheetDetails = new ledgerSheetDetails();
            // 日付リストを取得
            $startDate = parent::escStr( $_POST['start_date'] );
            $endDate = parent::escStr( $_POST['end_date'] );

            //modal
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
   
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'cust_cd'           => $cust_cd,
                'organization_id'   => $organizationId,
            );
            // 帳票フォーム一覧データ取得
            $ledgerSheetDetailList = $ledgerSheetDetails->getFormListData($searchArray);

            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDate )).".csv", 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            
            array_push($export_csv_title, "日付");
            array_push($export_csv_title, "店舗");
            array_push($export_csv_title, "顧客コード");
            array_push($export_csv_title, "顧客名");
            array_push($export_csv_title, "取引番号");
            array_push($export_csv_title, "商品コード");
            array_push($export_csv_title, "商品名");
            array_push($export_csv_title, "容量");
            // test if staff checked
            if($_POST["staff"]){
                array_push($export_csv_title, "担当者名");
            }
            array_push($export_csv_title, "単価");
            array_push($export_csv_title, "今回特点");
            array_push($export_csv_title, "得点利用");
            array_push($export_csv_title, "数量");
            array_push($export_csv_title, "引額");
            array_push($export_csv_title, "取引金額");

            if( touch($file_path) ){
                // オブジェクト生成
               $file = new SplFileObject( $file_path, "w" );$str = chr(239).chr(187).chr(191).$str;$file = fopen($file_path, 'a');fwrite($file,$str); // add モンターニュ 2020/02/03
 
                // エンコードしたタイトル行を配列ごとCSVデータ化
                $file = fopen($file_path, 'a');fwrite($file,implode(',',$export_csv_title)."\r");
                
                // 取得結果を画面と同じように再構築して行として搭載
                // 内容行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
                $list_no = 0;
                foreach( $ledgerSheetDetailList as $rows ){
                    
                    // 垂直ヘッダ

                    $str = '';

                    $str = $str.'"'.$rows['trndate'];
                    $str = $str.'","'.$rows['org_nm'];
                    $str = $str.'","'.$rows['cust_cd'];
                    $str = $str.'","'.$rows['cust_nm'];
                    $str = $str.'","'.$rows['account_no'];
                    $str = $str.'","'.$rows['prod_cd'];
                    $str = $str.'","'.$rows['prod_nm'];
                    $str = $str.'","'.$rows['prod_capa'];
                    // test staff checked
                    if($_POST["staff"]){
                        $str = $str.'","'.$rows['staff_nm'];
                    }
                    $str = $str.'","'.$rows['saleprice'];
                    $str = $str.'","'.$rows['point'];
                    $str = $str.'","'.$rows['point_r'];
                    $str = $str.'","'.$rows['amount'];
                    $str = $str.'","'.$rows['disctotal'];
                    $str = $str.'","'.$rows['saletotal'];
                    $str = $str.'"';
                    
                    // 配列に変換
                    ////$str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8'); // add モンターニュ 2020/02/03
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
            header("Content-Disposition: attachment; filename=顧客別売上明細".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
