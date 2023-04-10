<?php
    /**
     * @file      帳票 - 顧客台帳コントローラ
     * @author    millionet oota
     * @date      2018/12/11
     * @version   1.00
     * @note      帳票 - 顧客台帳の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetCustomer.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetCustomerController extends BaseController
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

            $ledgerSheetCustomer = new ledgerSheetCustomer();

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-P');

            //テスト用
            //$html = file_get_contents("./View/LedgerSheetCustomerLedgerPDFPanel.html");

            $saledYear = parent::escStr( $_POST['saled_year'] );
            $startCode  = parent::escStr( $_POST['start_code'] );
            $endCode  = parent::escStr( $_POST['end_code'] );
            $searchArray = array(
                'saled_year' => $saledYear,
                'start_code' => $startCode,
                'end_code'   => $endCode,
            );

            $list = $ledgerSheetCustomer->getFormListData($searchArray);

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

            $conditions = '';
            $client_no = '';
            $addr_no = '';
            $dm_no = '';
// modal            
            $modal = new Modal();
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();           
            $prod_detail    = [];
            $prod_detail    = $modal->getprod_detail();            
            $param          = [];
            if($_POST){
                $param = $_POST;
            }
            $param["org_id"]    = "";
            $param["prod_cd"]   = "";
            
            // フラグ初期設定
            if($mode == 'initial'){
                $conditions  = '1';
                $client_no  = '1';
                $addr_no  = '1';
                $dm_no  = '1';
                
                // ソートマーク初期化
                $sort = "2";
                
            }else{
                $conditions  = parent::escStr( $_POST['conditions'] );
                $client_no  = parent::escStr( $_POST['client_no'] );
                $addr_no  = parent::escStr( $_POST['addr_no'] );
                $dm_no  = parent::escStr( $_POST['dm_no'] );
                $sort  = parent::escStr( $_POST['sort'] );
            }
            
            $headerArray = $this->changeHeaderItemMark($sort);

            //開始年
            $startYear = "2010";

            $ledgerSheetCustomer = new ledgerSheetCustomer();

            $abbreviatedNameList = $ledgerSheetCustomer->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

            // 日付リストを取得
            $startDateM = parent::escStr( $_POST['start_dateM'] );
            $endDateM = parent::escStr( $_POST['end_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDateM) OR $startDateM == ""){
                //現在日付の１日を設定
                $startDateM =date('Y/m', strtotime('first day of ' . $month));
            }
            
            if(!isset($endDateM) OR $endDateM == ""){
                //現在日付の１日を設定
                $endDateM =date('Y/m', strtotime('last day of ' . $month));
            }
            
            // 月初を設定
            $startDate        = parent::escStr( $startDateM ).'/01';
            // 月末を設定
            $endDate = date('Y/m/d', strtotime('last day of ' . $endDateM));
            //性別
            $sex = "";
            for($i=1;$i<6;$i++){
                if($_POST["sex_".$i]){                    
                    $sex .= ",'".$_POST["sex_".$i]."'";
                }
            }
            $sex = preg_replace('/^,/', '', $sex);
            //誕生月
            $birth_month = "";
            for($i=1;$i<13;$i++){
                if($_POST["birth_month_".$i]){                    
                    $birth_month .= ",'".$_POST["birth_month_".$i]."'";
                }
            }
            $birth_month = preg_replace('/^,/', '', $birth_month);
// modal
            $organizationId = 'false';
            if($_POST['org_r'] === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
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
                'start_date'            => str_replace("/", "",parent::escStr( $startDateM )),
                'end_date'              => str_replace("/", "",parent::escStr( $endDateM )),
                'line_cnt'              => parent::escStr( $_POST['line_cnt'] ),
//                'line_cnt'    => parent::escStr( "50" ),
                'conditions'            => $conditions,
                'client_no'             => $client_no,
                'addr_no'               => $addr_no,
                'dm_no'                 => $dm_no,
                'total_st'              => parent::escStr( $_POST['total_st'] ),
                'total_end'             => parent::escStr( $_POST['total_end'] ),
                'profit_st'             => parent::escStr( $_POST['profit_st'] ),
                'profit_end'            => parent::escStr( $_POST['profit_end'] ),
                'cust_point_st'         => parent::escStr( $_POST['cust_point_st'] ),
                'cust_point_end'        => parent::escStr( $_POST['cust_point_end'] ),
                'visit_cnt_st'          => parent::escStr( $_POST['visit_cnt_st'] ),
                'visit_cnt_end'         => parent::escStr( $_POST['visit_cnt_end'] ),
                'start_last_date'       => parent::escStr( $_POST['start_last_date'] ),
                'end_last_date'         => parent::escStr( $_POST['end_last_date'] ),
                'start_last_dateM'      => parent::escStr( $_POST['start_last_dateM'] ),
                'end_last_dateM'        => parent::escStr( $_POST['end_last_dateM'] ),
                'start_lbirth'          => parent::escStr( $_POST['start_lbirth'] ),
                'end_lbirth'            => parent::escStr( $_POST['end_lbirth'] ),
                'birth_month'           => $birth_month,
                'start_age'             => parent::escStr( $_POST['start_age'] ),
                'end_age'               => parent::escStr( $_POST['end_age'] ),
                'sex'                   => $sex,
                'start_cust_cd'         => parent::escStr( $_POST['start_cust_cd'] ),
                'end_cust_cd'           => parent::escStr( $_POST['end_cust_cd'] ),
                'cust_nm'               => parent::escStr( $_POST['cust_nm'] ),
                'addr1'                 => parent::escStr( $_POST['addr1'] ),
                'sort'                  => $sort,
                'organization_id'       => $organizationId,
                'prod_cd'               => $prod_cd,                
            );
            
            if($mode != "initial")
            {
                // 帳票フォーム一覧データ取得
                $ledgerSheetDetailList = $ledgerSheetCustomer->getFormListData($searchArray);
            }
            
            // 画面フォームに日付を返す
            if(!isset($saledYear) OR $saledYear == "")
            {
                //現在日付の１日を設定
                $saledYear =date('Y');
            }
            $searchArray = array(
                'start_date'  => str_replace("/", "",parent::escStr( $startDateM )),
                'end_date'    => str_replace("/", "",parent::escStr( $endDateM )),
                'line_cnt'    => parent::escStr( $_POST['line_cnt'] ),
//                'line_cnt'    => parent::escStr( "50" ),
                'conditions'  => $conditions,
                'client_no'  => $client_no,
                'addr_no'   => $addr_no,
                'dm_no'     => $dm_no,
                'total_st'     => parent::escStr( $_POST['total_st'] ),
                'total_end'     => parent::escStr( $_POST['total_end'] ),
                'profit_st'     => parent::escStr( $_POST['profit_st'] ),
                'profit_end'     => parent::escStr( $_POST['profit_end'] ),
                'cust_point_st'     => parent::escStr( $_POST['cust_point_st'] ),
                'cust_point_end'     => parent::escStr( $_POST['cust_point_end'] ),
                'visit_cnt_st'     => parent::escStr( $_POST['visit_cnt_st'] ),
                'visit_cnt_end'     => parent::escStr( $_POST['visit_cnt_end'] ),
                'start_last_date'     => parent::escStr( $_POST['start_last_date'] ),
                'end_last_date'     => parent::escStr( $_POST['end_last_date'] ),
                'start_last_dateM'     => parent::escStr( $_POST['start_last_dateM'] ),
                'end_last_dateM'     => parent::escStr( $_POST['end_last_dateM'] ),
                'start_lbirth'     => parent::escStr( $_POST['start_lbirth'] ),
                'end_lbirth'     => parent::escStr( $_POST['end_lbirth'] ),
                'start_age'     => parent::escStr( $_POST['start_age'] ),
                'end_age'     => parent::escStr( $_POST['end_age'] ),
                'start_cust_cd'     => parent::escStr( $_POST['start_cust_cd'] ),
                'end_cust_cd'     => parent::escStr( $_POST['end_cust_cd'] ),
                'cust_nm'     => parent::escStr( $_POST['cust_nm'] ),
                'addr1'     => parent::escStr( $_POST['addr1'] ),
                'sort'                  => $sort,
                'org_id'            => str_replace("'","",$_POST['org_id']),
                'prod_cd'           => str_replace("'","",$_POST['prod_cd']),               
            );            
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetCustomerPanel.html';
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
            //print_r($_POST);
            //開始年
            $startYear = "2010";

            $ledgerSheetCustomer = new ledgerSheetCustomer();
            // フラグ初期設定
            $conditions  = parent::escStr( $_POST['conditions'] );
            $client_no  = parent::escStr( $_POST['client_no'] );
            $addr_no  = parent::escStr( $_POST['addr_no'] );
            $dm_no  = parent::escStr( $_POST['dm_no'] );
            $sort  = parent::escStr( $_POST['sort'] );    
            
            // 日付リストを取得
            $startDateM = parent::escStr( $_POST['start_dateM'] );
            $endDateM = parent::escStr( $_POST['end_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDateM) OR $startDateM == ""){
                //現在日付の１日を設定
                $startDateM =date('Y/m', strtotime('first day of ' . $month));
            }
            
            if(!isset($endDateM) OR $endDateM == ""){
                //現在日付の１日を設定
                $endDateM =date('Y/m', strtotime('last day of ' . $month));
            }
            
            // 月初を設定
            $startDate        = parent::escStr( $startDateM ).'/01';
            // 月末を設定
            $endDate = date('Y/m/d', strtotime('last day of ' . $startDate));
            //性別
            $sex = "";
            for($i=1;$i<6;$i++){
                if($_POST["sex_".$i]){                    
                    $sex .= ",'".$_POST["sex_".$i]."'";
                }
            }
            $sex = preg_replace('/^,/', '', $sex);
            //誕生月
            $birth_month = "";
            for($i=1;$i<13;$i++){
                if($_POST["birth_month_".$i]){                    
                    $birth_month .= ",'".$_POST["birth_month_".$i]."'";
                }
            }
            $birth_month = preg_replace('/^,/', '', $birth_month);           

// modal
            $organizationId = 'false';
            if($_POST['org_r'] === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
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
                'start_date'            => str_replace("/", "",parent::escStr( $startDateM )),
                'end_date'              => str_replace("/", "",parent::escStr( $endDateM )),
                'line_cnt'              => parent::escStr( $_POST['line_cnt'] ),
//                'line_cnt'    => parent::escStr( "50" ),
                'conditions'            => $conditions,
                'client_no'             => $client_no,
                'addr_no'               => $addr_no,
                'dm_no'                 => $dm_no,
                'total_st'              => parent::escStr( $_POST['total_st'] ),
                'total_end'             => parent::escStr( $_POST['total_end'] ),
                'profit_st'             => parent::escStr( $_POST['profit_st'] ),
                'profit_end'            => parent::escStr( $_POST['profit_end'] ),
                'cust_point_st'         => parent::escStr( $_POST['cust_point_st'] ),
                'cust_point_end'        => parent::escStr( $_POST['cust_point_end'] ),
                'visit_cnt_st'          => parent::escStr( $_POST['visit_cnt_st'] ),
                'visit_cnt_end'         => parent::escStr( $_POST['visit_cnt_end'] ),
                'start_last_date'       => parent::escStr( $_POST['start_last_date'] ),
                'end_last_date'         => parent::escStr( $_POST['end_last_date'] ),
                'start_last_dateM'      => parent::escStr( $_POST['start_last_dateM'] ),
                'end_last_dateM'        => parent::escStr( $_POST['end_last_dateM'] ),
                'start_lbirth'          => parent::escStr( $_POST['start_lbirth'] ),
                'end_lbirth'            => parent::escStr( $_POST['end_lbirth'] ),
                'birth_month'           => $birth_month,
                'start_age'             => parent::escStr( $_POST['start_age'] ),
                'end_age'               => parent::escStr( $_POST['end_age'] ),
                'sex'                   => $sex,
                'start_cust_cd'         => parent::escStr( $_POST['start_cust_cd'] ),
                'end_cust_cd'           => parent::escStr( $_POST['end_cust_cd'] ),
                'cust_nm'               => parent::escStr( $_POST['cust_nm'] ),
                'addr1'                 => parent::escStr( $_POST['addr1'] ),
                'sort'                  => $sort,
                'organization_id'       => $organizationId,
                'prod_cd'               => $prod_cd, 
            );
            
            // 帳票フォーム一覧データ取得
            $ledgerSheetDetailList = $ledgerSheetCustomer->getFormListData($searchArray);
            
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

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "順位");
            array_push($export_csv_title, "顧客ｺｰﾄﾞ");
            array_push($export_csv_title, "顧客名");
            array_push($export_csv_title, "郵便番号");
            array_push($export_csv_title, "住所1");
            array_push($export_csv_title, "住所2");
            array_push($export_csv_title, "電話番号");
            array_push($export_csv_title, "登録日");
            array_push($export_csv_title, "お買上金額");
            array_push($export_csv_title, "粗利金額");
            array_push($export_csv_title, "得点");
            array_push($export_csv_title, "来店回数");
            array_push($export_csv_title, "最新来店日");

            if( touch($file_path) ){
                // オブジェクト生成
               $file = new SplFileObject( $file_path, "w" );$str = chr(239).chr(187).chr(191).$str;$file = fopen($file_path, 'a');fwrite($file,$str); // add モンターニュ 2020/02/03
                // タイトル行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
                foreach( $export_csv_title as $key => $val ){
                    $export_header[] = mb_convert_encoding($val, 'SJIS-win', 'UTF-8');
                }
 
                // エンコードしたタイトル行を配列ごとCSVデータ化
                $file = fopen($file_path, 'a');fwrite($file,implode(',',$export_csv_title)."\r");// add モンターニュ 2020/02/03
                
                // 取得結果を画面と同じように再構築して行として搭載
                // 内容行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
                $list_no = 0;
                foreach( $ledgerSheetDetailList as $rows ){
                    
                    // 垂直ヘッダ
                    $list_no += 1;

                    $str = '"'.$list_no;// 順位
                     
                    $str = $str.'","'.$rows['cust_cd'];
                    $str = $str.'","'.$rows['cust_nm'];
                    $str = $str.'","'.$rows['zip'];
                    $str = $str.'","'.$rows['addr1'];
                    $str = $str.'","'.$rows['addr2'];
                    $str = $str.'","'.$rows['tel'];
                    $str = $str.'","'.$rows['insdatetime'];
                    $str = $str.'","'.round($rows['total'],0);
                    $str = $str.'","'.round($rows['profit'],0);
                    $str = $str.'","'.round($rows['cust_point'],0);
                    $str = $str.'","'.$rows['visit_cnt'];
                    $str = $str.'","'.date("Y/m/d", strtotime($rows['last_date'])); //edt montagne 2020/04/07 「/」を追加します 
                    $str = $str.'"';
                    // 配列に変換
                    //$str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8'); // add モンターニュ 2020/02/03
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
            header("Content-Disposition: attachment; filename=顧客台帳".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
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
                    'custcdSortMark'          => '',
                    'custNmSortMark'       => '',
                    'zipSortMark'  => '',
                    'addr1SortMark' => '',
                    'addr2SortMark'        => '',
                    'telSortMark'         => '',
                    'insdatetimeSortMark' => '',
                    'totalSortMark'    => '',
                    'profitSortMark'  => '',
                    'custPointSortMark'     => '',
                    'visitCntSortMark'    => '',
                    'lastDateSortMark'  => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1] = "custcdSortMark";
                $sortList[2] = "custcdSortMark";
                $sortList[3] = "custNmSortMark";
                $sortList[4] = "custNmSortMark";
                $sortList[5] = "zipSortMark";
                $sortList[6] = "zipSortMark";
                $sortList[7] = "addr1SortMark";
                $sortList[8] = "addr1SortMark";
                $sortList[9] = "addr2SortMark";
                $sortList[10] = "addr2SortMark";
                $sortList[11] = "telSortMark";
                $sortList[12] = "telSortMark";
                $sortList[13] = "insdatetimeSortMark";
                $sortList[14] = "insdatetimeSortMark";
                $sortList[15] = "totalSortMark";
                $sortList[16] = "totalSortMark";
                $sortList[17] = "profitSortMark";
                $sortList[18] = "profitSortMark";
                $sortList[19] = "custPointSortMark";
                $sortList[20] = "custPointSortMark";
                $sortList[21] = "visitCntSortMark";
                $sortList[22] = "visitCntSortMark";
                $sortList[23] = "lastDateSortMark";
                $sortList[24] = "lastDateSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START changeHeaderItemMark");

            return $headerArray;
        }

    }
?>
