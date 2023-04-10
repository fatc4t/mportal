<?php
    /**
     * @file      帳票 - 販売員別売上集計表コントローラ
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 販売員別売上集計表の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetBySalesStaffSpreadSheet.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetBySalesStaffSpreadSheetController extends BaseController
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

            $ledgerSheetBySalesStaffSpreadSheet = new ledgerSheetBySalesStaffSpreadSheet();

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

// modal            
            $modal = new Modal();
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            $sect_detail    = [];
            $sect_detail    = $modal->getsect_detail();
            $staff_detail   = [];
            $staff_detail    = $modal->getstaff_detail();            
            $param          = [];
            if($_POST){
                $param = $_POST;
            }
            $param["sect_cd"]   = "";
            $param["org_id"]    = "";
            $param["staff_cd"]  = "";            
            
            $ledgerSheetBySalesStaffSpreadSheet = new ledgerSheetBySalesStaffSpreadSheet();

            $abbreviatedNameList = $ledgerSheetBySalesStaffSpreadSheet->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

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
                $endDate = date('Y/m/d', strtotime('last day of ' . $endDate));
            }

            $prodK = parent::escStr( $_POST['prod_k']);
            $prodS = parent::escStr( $_POST['prod_s']);
            $sort = ($mode === "initial") ? '0' : parent::escStr( $_POST['sort'] );            
// modal            
            $organizationId = 'false';
            if($_POST['org_r'] === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
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
            $staff_cd = 'false';
            if($_POST['staff_r'] === ""){
                if($_POST['staff_select'] && $_POST['staff_select'] === 'empty'){
                    $staff_cd = $_POST['staff_cd'];
                }else{
                    $staff_cd = "'".$_POST['staff_select']."'";
                }
            }
            
            $sort = ($mode === "initial") ? '0' : parent::escStr( $_POST['sort'] );
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'prod_k'            => $prodK,
                'prod_s'            => $prodS,
                'organization_id'   => $organizationId,
                'sect_cd'           => $sect_cd,
                'staff_cd'          => $staff_cd,
                'orgn_sort'         => $orgn_sort,
                'sect_sort'         => $sect_sort,
                'sort'              => $sort,
            );
            
            if($mode != "initial") {
                // 帳票フォーム一覧データ取得
                $ledgerSheetDetailList = $ledgerSheetBySalesStaffSpreadSheet->getFormListData($searchArray);

                // 合計計算用変数

                $sumAmount = 0;
                $sumPureTotal = 0;
                $sumProfitI = 0;
                $sumPureTotalI = 0;

                if(count($ledgerSheetDetailList) > 0){
                    // 合計行を取得
                    foreach($ledgerSheetDetailList as $data){
                        $sumAmount = $sumAmount + $data["amount"];
                        $sumPureTotal  = $sumPureTotal + $data["pure_total"];
                        $sumProfitI    = $sumProfitI + $data["profit_i"];
                        $sumPureTotalI  = $sumPureTotalI + $data["pure_total_i"];
                    }

                    $sumLine = array(
                        'organization_name'   => '',                // 店番
                        'prod_cd'           => '',                // JANコード
                        'prod_nm'           => '',            // 商品名
                        'sect_cd'           => '',                // 部門コード
                        'supp_cd'           => '',                // 仕入先コード
                        'staff_nm'          => '合計',                // 担当者コード
                        'amount'            => $sumAmount,        // 売上数
                        'pure_total'        => $sumPureTotal,     // 売上金額
                        'profit_i'          => $sumProfitI,      // 粗利額
                        'pure_total_i'      => $sumPureTotalI,    // 税抜売上金額
                        );
                    
                    /**　修正日  　:　2019/11/27
                     * 　修正者　　:　バッタライ
                     * 　修正内容　:  合計行を追加しないようにしました。
                     */　

//                    // 合計行を追加
//                    array_push($ledgerSheetDetailList, $sumLine);
                }
            }
            $headerArray = $this->changeHeaderItemMark($sort);

            // 検索組織
            $searchArray = array(
                'org_id'            => str_replace("'","",$_POST['org_id']),
                'sect_cd'           => str_replace("'","",$_POST['sect_cd']),
                'staff_cd'          => str_replace("'","",$_POST['staff_cd'])
            );
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();            

            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetBySalesStaffSpreadSheetPanel.html';
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
            
            $ledgerSheetBySalesStaffSpreadSheet = new ledgerSheetBySalesStaffSpreadSheet();

            // 日付リストを取得
            $startDate = parent::escStr( $_POST['start_date'] );

            // 日付リストを取得
            $endDate = parent::escStr( $_POST['end_date'] );

            $prodK = parent::escStr( $_POST['prod_k']);
            $prodS = parent::escStr( $_POST['prod_s']);
            $sort = ($mode === "initial") ? '0' : parent::escStr( $_POST['sort'] );            

// modal            
            $organizationId = 'false';
            if($_POST['org_r'] === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
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
            $staff_cd = 'false';
            if($_POST['staff_r'] === ""){
                if($_POST['staff_select'] && $_POST['staff_select'] === 'empty'){
                    $staff_cd = $_POST['staff_cd'];
                }else{
                    $staff_cd = "'".$_POST['staff_select']."'";
                }
            }
            
            $sort = ($mode === "initial") ? '0' : parent::escStr( $_POST['sort'] );
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'prod_k'            => $prodK,
                'prod_s'            => $prodS,
                'organization_id'   => $organizationId,
                'sect_cd'           => $sect_cd,
                'staff_cd'          => $staff_cd,
                'orgn_sort'         => $orgn_sort,
                'sect_sort'         => $sect_sort,
                'sort'              => $sort,
            );
            
            // 帳票フォーム一覧データ取得
            $ledgerSheetDetailList = $ledgerSheetBySalesStaffSpreadSheet->getFormListData($searchArray);
            
            // ファイル名
            //$file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');
            $file_path = date('YmdHis').'.csv';

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "No");
            array_push($export_csv_title, "店舗");
            array_push($export_csv_title, "担当者コード");
            array_push($export_csv_title, "担当者名");
            array_push($export_csv_title, "商品コード");
            array_push($export_csv_title, "商品名");
            array_push($export_csv_title, "数量");
            array_push($export_csv_title, "売上金額");
            array_push($export_csv_title, "粗利額");
            array_push($export_csv_title, "税抜売上げ金額");
            array_push($export_csv_title, "部門コード");
            array_push($export_csv_title, "部門名");
            array_push($export_csv_title, "仕入先コード");
            array_push($export_csv_title, "仕入先名");

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
                    $str = '"'.$list_no;// No

                    $str = $str.'","'.$rows['organization_name'];   // 店舗
                    $str = $str.'","'.$rows['staff_cd'];            // 担当者コード
                    $str = $str.'","'.$rows['staff_nm'];            // 担当者名
                    $str = $str.'","'.$rows['prod_cd'];             // 商品コード
                    $str = $str.'","'.$rows['prod_nm'];             // 商品名
                    $str = $str.'","'.$rows['amount'];              // 数量
                    $str = $str.'","'.$rows['pure_total'];          // 売上金額
                    $str = $str.'","'.$rows['profit_i'];            // 粗利額
                    $str = $str.'","'.$rows['pure_total_i'];        // 税抜売上金額
                    $str = $str.'","'.$rows['sect_cd'];             // 部門コード
                    $str = $str.'","'.$rows['sect_nm'];             // 部門名
                    $str = $str.'","'.$rows['supp_cd'];             // 仕入先コード
                    $str = $str.'","'.$rows['supp_nm'];             // 仕入先コード
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
            header("Content-Disposition: attachment; filename=担当者別売上集計表".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
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
                'tenpoSortMark'         => '',
                'prod_cdSortMark'       => '',
                'prod_nmSortMark'       => '',
                'sect_cdSortMark'       => '',
                'sect_nmSortMark'       => '',
                'supp_cdSortMark'       => '',
                'supp_nmSortMark'       => '',
                'staff_cdSortMark'      => '',
                'staff_nmSortMark'      => '',
                'amountSortMark'        => '',
                'pure_totalSortMark'    => '',
                'profit_iSortMark'      => '',
                'pure_total_iSortMark'  => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]    = "tenpoSortMark";
                $sortList[2]    = "tenpoSortMark";
                $sortList[3]    = "prod_cdSortMark";
                $sortList[4]    = "prod_cdSortMark";
                $sortList[5]    = "prod_nmSortMark";
                $sortList[6]    = "prod_nmSortMark";
                $sortList[7]    = "sect_cdSortMark";
                $sortList[8]    = "sect_cdSortMark";
                $sortList[9]    = "sect_nmSortMark";
                $sortList[10]   = "sect_nmSortMark";
                $sortList[11]   = "supp_cdSortMark";
                $sortList[12]   = "supp_cdSortMark";
                $sortList[13]   = "supp_nmSortMark";
                $sortList[14]   = "supp_nmSortMark";
                $sortList[15]   = "staff_cdSortMark";
                $sortList[16]   = "staff_cdSortMark";
                $sortList[17]   = "staff_nmSortMark";
                $sortList[18]   = "staff_nmSortMark";
                $sortList[19]   = "amountSortMark";
                $sortList[20]   = "amountSortMark";
                $sortList[21]   = "pure_totalSortMark";
                $sortList[22]   = "pure_totalSortMark";
                $sortList[23]   = "profit_iSortMark";
                $sortList[24]   = "profit_iSortMark";
                $sortList[25]   = "pure_total_iSortMark";
                $sortList[26]   = "pure_total_iSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START changeHeaderItemMark");

            return $headerArray;
        }
    }
?>
