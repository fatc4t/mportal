<?php
    /**
     * @file      帳票 - 販売員別選定品(税込)コントローラ
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 販売員別選定品(税込)の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetBySalesStaffSelectedItem.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetBySalesStaffSelectedItemController extends BaseController
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

            $ledgerSheetBySalesStaffSelectedItem = new ledgerSheetBySalesStaffSelectedItem();

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-P');

            //テスト用
            //$html = file_get_contents("./View/LedgerSheetBySalesStaffSelectedItemPDFPanel.html");

            $saledYear = parent::escStr( $_POST['saled_year'] );
            $startCode  = parent::escStr( $_POST['start_code'] );
            $endCode  = parent::escStr( $_POST['end_code'] );
            $searchArray = array(
                'saled_year' => $saledYear,
                'start_code' => $startCode,
                'end_code'   => $endCode,
            );

            $list = $ledgerSheetBySalesStaffSelectedItem->getFormListData($searchArray);

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
            $modal = new Modal();
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            $sect_detail    = [];
            $sect_detail    = $modal->getsect_detail();            
            $prod_detail    = [];
            $prod_detail    = $modal->getprod_detail();
            $staff_detail   = [];
            $staff_detail    = $modal->getstaff_detail();            
            $param          = [];
            if($_POST){
                $param = $_POST;
            }
            $param["sect_cd"]   = "";
            $param["org_id"]    = "";
            $param["prod_cd"]   = "";
            $param["staff_cd"]  = "";

            $searchArray = [];
            $ledgerSheetBySalesStaffSelectedItem = new ledgerSheetBySalesStaffSelectedItem();

            $abbreviatedNameList = $ledgerSheetBySalesStaffSelectedItem->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

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


            // 一覧表示用データ配列初期化
            $ledgerSheetDetailList = array();

            $orgn_sort  = ($mode === "initial") ? '2' : parent::escStr( $_POST['orgn_sort'] );  // 既定値 = 2:店舗昇順
            $sect_sort  = ($mode === "initial") ? '4' : parent::escStr( $_POST['sect_sort'] );  // 既定値 = 4:部門昇順
            $sort       = ($mode === "initial") ? '0' : parent::escStr( $_POST['sort'] );       // 既定値 = 0:定義範囲外(既定のソート条件)
            $bef_sort   = ($mode === "initial") ? '2' : parent::escStr( $_POST['bef_sort'] );   // 商品で並べ替えする直前に店舗と部門とどちらでソートされていたか(既定値 = 2:店舗昇順)
            // 店舗で並べ替えする場合は部門並べ替え条件をリセット(昇順)する
            if ($sort === '1' || $sort === '2'){
                $sect_sort = '4';
            }
            // 部門で並べ替えする場合は店舗並べ替え条件をリセット(昇順)する
            else if ($sort === '3' || $sort === '4'){
                $orgn_sort = '2';
            }
            // その他の場合は店舗と部門の並べ替え条件を保持する
            else{
                // NOP
            }

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
            $prod_cd = 'false';
            if($_POST['prod_r'] === ""){
                if($_POST['prod_select'] && $_POST['prod_select'] === 'empty'){
                    $prod_cd = $_POST['prod_cd'];
                }else{
                    $prod_cd = "'".$_POST['prod_select']."'";
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
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'organizationID'    => $organizationId,
                'prod_cd'           => $prod_cd,
                'sect_cd'           => $sect_cd,
                'staff_cd'          => $staff_cd,
                'orgn_sort'         => $orgn_sort,
                'sect_sort'         => $sect_sort,
                'sort'              => $sort,
                'bef_sort'          => $bef_sort,
            );            
            if($mode != 'initial'){


                // 担当者データ取得
                $ledgerSheetStaffList = $ledgerSheetBySalesStaffSelectedItem->getStaffListData($searchArray);

                // 帳票フォーム一覧データ取得
                //$ledgerSheetDetailList = $ledgerSheetBySalesStaffSelectedItem->getFormListData($searchArray);
                $data = $ledgerSheetBySalesStaffSelectedItem->getFormListData($searchArray);

                $aryAddRow = array();
                $intOrgnId_Bef = -1;
                $strOrgnNm_Bef = '';
                $strSectCd_Bef = '';
                $strProdCd_Bef = '';

                $arySum_Staff = array();
                for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
                    $field = 'pure_total'.strval($intL);
                    $aryRow = array($field => '');
                    array_push($arySum_Staff, $aryRow);
                }
                // 集計ループ処理開始位置
                $intPos = 0;

                foreach ($data as $rows){
                    $intOrgnId = intval($rows['organization_id']);
                    $strOrgnNm = strval($rows['abbreviated_name']);
                    $strSectCd = strval($rows['sect_cd']);
                    $strProdCd = strval($rows['prod_cd']);
                    if ($intOrgnId !== $intOrgnId_Bef || $strSectCd !== $strSectCd_Bef || $strProdCd !== $strProdCd_Bef){
                        if (count($aryAddRow) > 0){                        
                            // 商品別総計
                            $dml_pure_total_prod_sum = '';
                            for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
                                $field = 'pure_total'.strval($intL);
                                if ($aryAddRow[$field] !== ''){
                                    $dml_pure_total_prod_sum += floatval($aryAddRow[$field]);
                                }
                            }
                            $aryAddRow['sum_pure_total'] = $dml_pure_total_prod_sum;
                            // 配列追加
                            array_push($ledgerSheetDetailList, $aryAddRow);

                            if ($intOrgnId !== $intOrgnId_Bef || $strSectCd !== $strSectCd_Bef){
                                // 部門集計行追加
                                $aryAddRow = array();
                                $aryAddRow['data_kbn']          = 'S';  // Summary
                                $aryAddRow['organization_id']   = $intOrgnId_Bef;
                                $aryAddRow['abbreviated_name']  = $strOrgnNm_Bef;
                                $aryAddRow['sect_cd']           = '合計';
                                $aryAddRow['sect_nm']           = '合計';
                                //$aryAddRow['sect_nm']           = '';
                                $aryAddRow['prod_cd']           = '';
                                $aryAddRow['prod_nm']           = '';
                                //$aryAddRow['prod_nm']           = '合計';
                                // 担当者列ループ処理
                                $dml_pure_total_sum = '';
                                for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
                                    // 追加済データ配列ループ処理
                                    $dml_pure_total_sum_staff = '';
                                    for ($intJ = $intPos; $intJ < count($ledgerSheetDetailList); $intJ ++){
                                        if (intval($ledgerSheetDetailList[$intJ]['organization_id']) !== $intOrgnId_Bef ||
                                            strval($ledgerSheetDetailList[$intJ]['sect_cd']) !== $strSectCd_Bef){
                                            continue;
                                        }
                                        $field = 'pure_total'.strval($intL);
                                        if ($ledgerSheetDetailList[$intJ][$field] !== ''){
                                            $dml_pure_total_sum_staff += floatval($ledgerSheetDetailList[$intJ][$field]);
                                        }
                                    }
                                    // 担当者別部門別集計格納
                                    $field = 'pure_total'.strval($intL);
                                    $aryAddRow[$field] = $dml_pure_total_sum_staff;
                                    if ($dml_pure_total_sum_staff !== ''){
                                        $dml_pure_total_sum     += $dml_pure_total_sum_staff;
                                        $arySum_Staff[$field]   += $dml_pure_total_sum_staff;   // 総計で使用する
                                    }
                                }
                                // 部門別集計
                                if ($dml_pure_total_sum !== ''){
                                    $aryAddRow['sum_pure_total'] = $dml_pure_total_sum;
                                }
                                // 配列追加
                                array_push($ledgerSheetDetailList, $aryAddRow);
                                // 次回集計ループ処理開始位置
                                $intPos = count($ledgerSheetDetailList);
                            }
                        }
                        $aryAddRow = array();
                        $aryAddRow['data_kbn']          = 'D';  // Detail
                        $aryAddRow['organization_id']   = strval($intOrgnId);
                        $aryAddRow['abbreviated_name']  = $rows['abbreviated_name'];
                        $aryAddRow['sect_cd']           = $strSectCd;
                        $aryAddRow['sect_nm']           = $rows['sect_nm'];
                        $aryAddRow['prod_cd']           = $rows['prod_cd'];
                        $aryAddRow['prod_nm']           = $rows['prod_nm'];
                        for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
                            $aryAddRow['pure_total'.strval($intL)] = '';
                        }
                        $aryAddRow['sum_pure_total'] = '';
                    }
                    // pure_total値を格納するカラム位置を検索
                    $needle = strval($rows['staff_cd']);
                    $key = array_search($needle, $ledgerSheetStaffList, true);
                    $field = 'pure_total'.strval($key);
                    $aryAddRow[$field] = $rows['pure_total'];

                    // キー保持
                    $intOrgnId_Bef = $intOrgnId;
                    $strOrgnNm_Bef = $strOrgnNm;
                    $strSectCd_Bef = $strSectCd;
                    $strProdCd_Bef = $strProdCd;
                }
                // ループ脱出後　配列追加
                if (count($aryAddRow) > 0){
                    // 商品別総計
                    $dml_pure_total_prod_sum = '';
                    for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
                        $field = 'pure_total'.strval($intL);
                        if ($aryAddRow[$field] !== ''){
                            $dml_pure_total_prod_sum += floatval($aryAddRow[$field]);
                        }
                    }
                    $aryAddRow['sum_pure_total'] = $dml_pure_total_prod_sum;
                    // 配列追加
                    array_push($ledgerSheetDetailList, $aryAddRow);

                    // 部門集計行追加
                    $aryAddRow = array();
                    $aryAddRow['data_kbn']          = 'S';  // Summary
                    $aryAddRow['organization_id']   = $intOrgnId_Bef;
                    $aryAddRow['abbreviated_name']  = $strOrgnNm_Bef;
                    $aryAddRow['sect_cd']           = '合計';
                    $aryAddRow['sect_nm']           = '合計';
                    //$aryAddRow['sect_nm']           = '';
                    $aryAddRow['prod_cd']           = '';
                    $aryAddRow['prod_nm']           = '';
                    //$aryAddRow['prod_nm']           = '合計';
                    // 担当者列ループ処理
                    $dml_pure_total_sum = '';
                    for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
                        // 追加済データ配列ループ処理
                        $dml_pure_total_sum_staff = '';
                        for ($intJ = $intPos; $intJ < count($ledgerSheetDetailList); $intJ ++){
                            if (intval($ledgerSheetDetailList[$intJ]['organization_id']) !== $intOrgnId_Bef ||
                                strval($ledgerSheetDetailList[$intJ]['sect_cd']) !== $strSectCd_Bef){
                                continue;
                            }
                            $field = 'pure_total'.strval($intL);
                            if ($ledgerSheetDetailList[$intJ][$field] !== ''){
                                $dml_pure_total_sum_staff += floatval($ledgerSheetDetailList[$intJ][$field]);
                            }
                        }
                        // 担当者別部門別集計格納
                        $field = 'pure_total'.strval($intL);
                        $aryAddRow[$field] = $dml_pure_total_sum_staff;
                        if ($dml_pure_total_sum_staff !== ''){
                            $dml_pure_total_sum     += $dml_pure_total_sum_staff;
                            $arySum_Staff[$field]   += $dml_pure_total_sum_staff;   // 総計で使用する
                        }
                    }
                    // 部門別集計
                    $aryAddRow['sum_pure_total'] = $dml_pure_total_sum;
                    // 配列追加
                    array_push($ledgerSheetDetailList, $aryAddRow);
                }
                // 総計行追加
                $aryAddRow = array();
                $aryAddRow['data_kbn']          = 'F';  // Footer
                $aryAddRow['organization_id']   = '';
                $aryAddRow['abbreviated_name']  = '総合計';//表示変更（総計から総合計　2019/11/30 柴田
                $aryAddRow['sect_cd']           = '';
                $aryAddRow['sect_nm']           = '';
                $aryAddRow['prod_cd']           = '';
                $aryAddRow['prod_nm']           = '';
                // 担当者列ループ処理
                $dml_pure_total_sum = '';
                for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
    //                $dml_pure_total_sum_staff = '';
    //                // 追加済データ配列ループ処理
    //                for ($intJ = 0; $intJ < count($ledgerSheetDetailList); $intJ ++){
    //                    if ($ledgerSheetDetailList[$intJ]['data_kbn'] === 'S'){
    //                        $field = 'pure_total'.strval($intL);
    //                        if ($ledgerSheetDetailList[$intJ][$field] !== ''){
    //                            $dml_pure_total_sum_staff += floatval($ledgerSheetDetailList[$intJ][$field]);
    //                        }
    //                    }
    //                    // 担当者別部門別集計格納
    //                    $field = 'pure_total'.strval($intL);
    //                    $aryAddRow[$field] = $dml_pure_total_sum_staff;
    //                    if ($dml_pure_total_sum_staff !== ''){
    //                        $dml_pure_total_sum += $dml_pure_total_sum_staff;
    //                    }
    //                }


                    $field = 'pure_total'.strval($intL);
                    $aryAddRow[$field] = $arySum_Staff[$field];
                    if ($aryAddRow[$field] !== ''){
                        $dml_pure_total_sum += $aryAddRow[$field];
                    }                
                }
                // 総計
                $aryAddRow['sum_pure_total'] = $dml_pure_total_sum;
                // 配列追加
                array_push($ledgerSheetDetailList, $aryAddRow);

                $Log->trace("END   initialDisplay");

            }

            $headerArray = $this->changeHeaderItemMark($sort);

            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'org_id'            => str_replace("'","",$_POST['org_id']),
                'organizationID'    => str_replace("'","",$organizationId),
                'prod_cd'           => str_replace("'","",$_POST['prod_cd']),
                'sect_cd'           => str_replace("'","",$_POST['sect_cd']),
                'staff_cd'          => str_replace("'","",$_POST['staff_cd']),
                'orgn_sort'         => $orgn_sort,
                'sect_sort'         => $sect_sort,
                'sort'              => $sort,
                'bef_sort'          => $bef_sort,
            );              

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetBySalesStaffSelectedItemPanel.html';
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
            
            $ledgerSheetBySalesStaffSelectedItem = new ledgerSheetBySalesStaffSelectedItem();

            $abbreviatedNameList = $ledgerSheetBySalesStaffSelectedItem->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

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

            // 一覧表示用データ配列初期化
            $ledgerSheetDetailList = array();

            $orgn_sort  = parent::escStr( $_POST['orgn_sort'] );
            $sect_sort  = parent::escStr( $_POST['sect_sort'] );
            $sort       = parent::escStr( $_POST['sort'] );
            $bef_sort   = parent::escStr( $_POST['bef_sort'] );
            // 店舗で並べ替えする場合は部門並べ替え条件をリセット(昇順)する
            if ($sort === '1' || $sort === '2'){
                $sect_sort = '4';
            }
            // 部門で並べ替えする場合は店舗並べ替え条件をリセット(昇順)する
            else if ($sort === '3' || $sort === '4'){
                $orgn_sort = '2';
            }
            // その他の場合は店舗と部門の並べ替え条件を保持する
            else{
                // NOP
            }
            
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
            $prod_cd = 'false';
            if($_POST['prod_r'] === ""){
                if($_POST['prod_select'] && $_POST['prod_select'] === 'empty'){
                    $prod_cd = $_POST['prod_cd'];
                }else{
                    $prod_cd = "'".$_POST['prod_select']."'";
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
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
                'organizationID'    => $organizationId,
                'prod_cd'           => $prod_cd,
                'sect_cd'           => $sect_cd,
                'staff_cd'          => $staff_cd,
                'orgn_sort'         => $orgn_sort,
                'sect_sort'         => $sect_sort,
                'sort'              => $sort,
                'bef_sort'          => $bef_sort,
            ); 

            // 担当者データ取得
            $ledgerSheetStaffList = $ledgerSheetBySalesStaffSelectedItem->getStaffListData($searchArray);
            
            
            // 帳票フォーム一覧データ取得
            //$ledgerSheetDetailList = $ledgerSheetBySalesStaffSelectedItem->getFormListData($searchArray);
            $data = $ledgerSheetBySalesStaffSelectedItem->getFormListData($searchArray);

//            $sumLedgerSheetDetailList = array();
//            $tmpgerSheetStaffList = array();
//            $tmpprod_cd = '';
//            $tmpdata = array();
//            $rsubtotal = 0;
//            
//        /*    // 担当者に合わせてリストを再生成
//            foreach($ledgerSheetDetailList as $data){
//                // 商品が変わるごとにリストを復旧
//                if($tmpprod_cd == '' || $tmpprod_cd != $data["prod_cd"]){
//
//                    if(count($tmpgerSheetStaffList) > 0){
//                        // 残った列を埋める処理
//                        foreach($tmpgerSheetStaffList as $staff){
//
//                            $sumLine = array(
//                                'no'              => '',  
//                                'sect_cd'         => $tmpdata["sect_cd"],         // 部門コード
//                                'sect_nm'         => $tmpdata["sect_nm"],         // 部門名
//                                'prod_cd'         => $tmpdata["prod_cd"],         // 商品コード
//                                'prod_nm'         => $tmpdata["prod_nm"],         // 商品名
//                                'staff_cd'        => $tmpdata["staff_cd"],        // スタッフコード
//                            //  'staff_nm'        => $tmpdata["staff_nm"],          
//                                'subtotal'        => $rsubtotal,                          // 金額
//                            );
//                            // 行を追加
//                            array_push($sumLedgerSheetDetailList, $sumLine);                
//                        }
//                        
//                    // 右総計列を追加
//                            $sumLine = array(
//                                'no'              => '', 
//                                'sect_cd'         => $tmpdata["sect_cd"],           // 部門コード
//                                'sect_nm'         => $tmpdata["sect_nm"],           // 部門名
//                                'prod_cd'         => $tmpdata["prod_cd"],           // 商品コード
//                                'prod_nm'         => $tmpdata["prod_nm"],           // 商品名
//                                'staff_cd'        => $tmpdata["staff_cd"],          // スタッフコード
//                          //      'staff_nm'        => $tmpdata["staff_nm"],
//                                'subtotal'        => $rsubtotal,                     // 金額
//                            );
//                            // 小計行を追加
//                            array_push($sumLedgerSheetDetailList, $sumLine); 
//                            $rsubtotal = 0;
//                       
//                    }
//                    
//                        // 判定用に商品コードを保存
//                        $tmpprod_cd = $data["prod_cd"];
//                        $tmpgerSheetStaffList = $ledgerSheetStaffList;
//                }
//                
//                // 担当者分回しながら値のなかった分は要素を減らしていく
//             foreach($tmpgerSheetStaffList as $staff){
//
//                    // 第1リストのスタッフと合致するまで空行を生成
//                    if($data["staff_cd"] != $staff["staff_cd"]){
//                        $sumLine = array(
//                            'no'              => '', 
//                            'sect_cd'         => $data["sect_cd"],            // 部門コード
//                            'sect_nm'         => $data["sect_nm"],            // 部門名
//                            'prod_cd'         => $data["prod_cd"],            // 商品コード
//                            'prod_nm'         => $data["prod_nm"],            // 商品名
//                            'staff_cd'        => $staff["staff_cd"],          // スタッフコード
//                  //          'staff_nm'        => $tmpdata["staff_nm"],
//                            'subtotal'        => '',                          // 金額
//                        );
//                        //格納したらスタッフ情報を削除していくことでループ数と重複データをなくす
//                        array_splice($tmpgerSheetStaffList, 0, 1);
//                        // 行を追加
//                        array_push($sumLedgerSheetDetailList, $sumLine);                
//
//                    }else{
//                        $sumLine = array(
//                            'no'              => '', 
//                            'sect_cd'         => $data["sect_cd"],            // 部門コード
//                            'sect_nm'         => $data["sect_nm"],            // 部門名
//                            'prod_cd'         => $data["prod_cd"],            // 商品コード
//                            'prod_nm'         => $data["prod_nm"],            // 商品名
//                            'staff_cd'        => $staff["staff_cd"],          // スタッフコード
//               //             'staff_nm'        => $tmpdata["staff_nm"],
//                            'subtotal'        => $data["subtotal"],           // 金額
//                        );
//                        //格納したらスタッフ情報を削除していくことでループ数と重複データをなくす
//                        array_splice($tmpgerSheetStaffList, 0, 1);
//                        // 行を追加
//                        array_push($sumLedgerSheetDetailList, $sumLine);    
//                        // 残りの列を対処するためいったん退避する
//                        $tmpdata = $data;
//                        $rsubtotal = $rsubtotal + $data["subtotal"];
//                        break;
//
//                    }
//                }
//            }
//            
//           // 残った列を埋める処理
//            foreach($tmpgerSheetStaffList as $staff){
//
//                $sumLine = array(
//                    'no'              => '', 
//                    'sect_cd'         => $tmpdata["sect_cd"],            // 部門コード
//                    'sect_nm'         => $tmpdata["sect_nm"],            // 部門名
//                    'prod_cd'         => $tmpdata["prod_cd"],            // 商品コード
//                    'prod_nm'         => $tmpdata["prod_nm"],            // 商品名
//                    'staff_cd'        => $tmpdata["staff_cd"],          // スタッフコード
//          //          'staff_nm'        => $tmpdata["staff_nm"],
//                    'subtotal'        => '',                          // 金額
//                );
//                // 合計行を追加
//                array_push($sumLedgerSheetDetailList, $sumLine);                
//            }*/

            
            $aryAddRow = array();
            $intOrgnId_Bef = -1;
            $strOrgnNm_Bef = '';
            $strSectCd_Bef = '';
            $strProdCd_Bef = '';

//            $arySum_Staff = array();
//            for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
//                $field = 'pure_total'.strval($intL);
//                $aryRow = array($field => '');
//                array_push($arySum_Staff, $aryRow);
//            }
//            // 集計ループ処理開始位置
//            $intPos = 0;
            
            foreach ($data as $rows){
                $intOrgnId = intval($rows['organization_id']);
                $strOrgnNm = strval($rows['abbreviated_name']);
                $strSectCd = strval($rows['sect_cd']);
                $strProdCd = strval($rows['prod_cd']);
                if ($intOrgnId !== $intOrgnId_Bef || $strSectCd !== $strSectCd_Bef || $strProdCd !== $strProdCd_Bef){
                    if (count($aryAddRow) > 0){                        
                        // 商品別総計
                        $dml_pure_total_prod_sum = '';
                        for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
                            $field = 'pure_total'.strval($intL);
                            if ($aryAddRow[$field] !== ''){
                                $dml_pure_total_prod_sum += floatval($aryAddRow[$field]);
                            }
                        }
                        $aryAddRow['sum_pure_total'] = $dml_pure_total_prod_sum;
                        // 配列追加
                        array_push($ledgerSheetDetailList, $aryAddRow);

//                        if ($intOrgnId !== $intOrgnId_Bef || $strSectCd !== $strSectCd_Bef){
//                            // 部門集計行追加
//                            $aryAddRow = array();
//                            $aryAddRow['data_kbn']          = 'S';  // Summary
//                            $aryAddRow['organization_id']   = $intOrgnId_Bef;
//                            $aryAddRow['abbreviated_name']  = $strOrgnNm_Bef;
//                            $aryAddRow['sect_cd']           = $strSectCd_Bef;
//                            //$aryAddRow['sect_nm']           = '集計';
//                            $aryAddRow['sect_nm']           = '';
//                            $aryAddRow['prod_cd']           = '';
//                            //$aryAddRow['prod_nm']           = '';
//                            $aryAddRow['prod_nm']           = '集計';
//                            // 担当者列ループ処理
//                            $dml_pure_total_sum = '';
//                            for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
//                                // 追加済データ配列ループ処理
//                                $dml_pure_total_sum_staff = '';
//                                for ($intJ = $intPos; $intJ < count($ledgerSheetDetailList); $intJ ++){
//                                    if (intval($ledgerSheetDetailList[$intJ]['organization_id']) !== $intOrgnId_Bef ||
//                                        strval($ledgerSheetDetailList[$intJ]['sect_cd']) !== $strSectCd_Bef){
//                                        continue;
//                                    }
//                                    $field = 'pure_total'.strval($intL);
//                                    if ($ledgerSheetDetailList[$intJ][$field] !== ''){
//                                        $dml_pure_total_sum_staff += floatval($ledgerSheetDetailList[$intJ][$field]);
//                                    }
//                                }
//                                // 担当者別部門別集計格納
//                                $field = 'pure_total'.strval($intL);
//                                $aryAddRow[$field] = $dml_pure_total_sum_staff;
//                                if ($dml_pure_total_sum_staff !== ''){
//                                    $dml_pure_total_sum     += $dml_pure_total_sum_staff;
//                                    $arySum_Staff[$field]   += $dml_pure_total_sum_staff;   // 総計で使用する
//                                }
//                            }
//                            // 部門別集計
//                            if ($dml_pure_total_sum !== ''){
//                                $aryAddRow['sum_pure_total'] = $dml_pure_total_sum;
//                            }
//                            // 配列追加
//                            array_push($ledgerSheetDetailList, $aryAddRow);
//                            // 次回集計ループ処理開始位置
//                            $intPos = count($ledgerSheetDetailList);
//                        }
                    }
                    $aryAddRow = array();
                    $aryAddRow['data_kbn']          = 'D';  // Detail
                    $aryAddRow['organization_id']   = strval($intOrgnId);
                    $aryAddRow['abbreviated_name']  = $rows['abbreviated_name'];
                    $aryAddRow['sect_cd']           = $strSectCd;
                    $aryAddRow['sect_nm']           = $rows['sect_nm'];
                    $aryAddRow['prod_cd']           = $rows['prod_cd'];
                    $aryAddRow['prod_nm']           = $rows['prod_nm'];
                    for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
                        $aryAddRow['pure_total'.strval($intL)] = '';
                    }
                    $aryAddRow['sum_pure_total'] = '';
                }
                // pure_total値を格納するカラム位置を検索
                $needle = strval($rows['staff_cd']);
                $key = array_search($needle, $ledgerSheetStaffList, true);
                $field = 'pure_total'.strval($key);
                $aryAddRow[$field] = $rows['pure_total'];

                // キー保持
                $intOrgnId_Bef = $intOrgnId;
                $strOrgnNm_Bef = $strOrgnNm;
                $strSectCd_Bef = $strSectCd;
                $strProdCd_Bef = $strProdCd;
            }
            // ループ脱出後　配列追加
            if (count($aryAddRow) > 0){
                // 商品別総計
                $dml_pure_total_prod_sum = '';
                for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
                    $field = 'pure_total'.strval($intL);
                    if ($aryAddRow[$field] !== ''){
                        $dml_pure_total_prod_sum += floatval($aryAddRow[$field]);
                    }
                }
                $aryAddRow['sum_pure_total'] = $dml_pure_total_prod_sum;
                // 配列追加
                array_push($ledgerSheetDetailList, $aryAddRow);

//                // 部門集計行追加
//                $aryAddRow = array();
//                $aryAddRow['data_kbn']          = 'S';  // Summary
//                $aryAddRow['organization_id']   = $intOrgnId_Bef;
//                $aryAddRow['abbreviated_name']  = $strOrgnNm_Bef;
//                $aryAddRow['sect_cd']           = $strSectCd_Bef;
//                //$aryAddRow['sect_nm']           = '集計';
//                $aryAddRow['sect_nm']           = '';
//                $aryAddRow['prod_cd']           = '';
//                //$aryAddRow['prod_nm']           = '';
//                $aryAddRow['prod_nm']           = '集計';
//                // 担当者列ループ処理
//                $dml_pure_total_sum = '';
//                for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
//                    // 追加済データ配列ループ処理
//                    $dml_pure_total_sum_staff = '';
//                    for ($intJ = $intPos; $intJ < count($ledgerSheetDetailList); $intJ ++){
//                        if (intval($ledgerSheetDetailList[$intJ]['organization_id']) !== $intOrgnId_Bef ||
//                            strval($ledgerSheetDetailList[$intJ]['sect_cd']) !== $strSectCd_Bef){
//                            continue;
//                        }
//                        $field = 'pure_total'.strval($intL);
//                        if ($ledgerSheetDetailList[$intJ][$field] !== ''){
//                            $dml_pure_total_sum_staff += floatval($ledgerSheetDetailList[$intJ][$field]);
//                        }
//                    }
//                    // 担当者別部門別集計格納
//                    $field = 'pure_total'.strval($intL);
//                    $aryAddRow[$field] = $dml_pure_total_sum_staff;
//                    if ($dml_pure_total_sum_staff !== ''){
//                        $dml_pure_total_sum     += $dml_pure_total_sum_staff;
//                        $arySum_Staff[$field]   += $dml_pure_total_sum_staff;   // 総計で使用する
//                    }
//                }
//                // 部門別集計
//                $aryAddRow['sum_pure_total'] = $dml_pure_total_sum;
//                // 配列追加
//                array_push($ledgerSheetDetailList, $aryAddRow);
            }
//            // 総計行追加
//            $aryAddRow = array();
//            $aryAddRow['data_kbn']          = 'F';  // Footer
//            $aryAddRow['organization_id']   = '';
//            $aryAddRow['abbreviated_name']  = '総計';
//            $aryAddRow['sect_cd']           = '';
//            $aryAddRow['sect_nm']           = '';
//            $aryAddRow['prod_cd']           = '';
//            $aryAddRow['prod_nm']           = '';
//            // 担当者列ループ処理
//            $dml_pure_total_sum = '';
//            for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
////                $dml_pure_total_sum_staff = '';
////                // 追加済データ配列ループ処理
////                for ($intJ = 0; $intJ < count($ledgerSheetDetailList); $intJ ++){
////                    if ($ledgerSheetDetailList[$intJ]['data_kbn'] === 'S'){
////                        $field = 'pure_total'.strval($intL);
////                        if ($ledgerSheetDetailList[$intJ][$field] !== ''){
////                            $dml_pure_total_sum_staff += floatval($ledgerSheetDetailList[$intJ][$field]);
////                        }
////                    }
////                    // 担当者別部門別集計格納
////                    $field = 'pure_total'.strval($intL);
////                    $aryAddRow[$field] = $dml_pure_total_sum_staff;
////                    if ($dml_pure_total_sum_staff !== ''){
////                        $dml_pure_total_sum += $dml_pure_total_sum_staff;
////                    }
////                }
//
//                
//                $field = 'pure_total'.strval($intL);
//                $aryAddRow[$field] = $arySum_Staff[$field];
//                if ($aryAddRow[$field] !== ''){
//                    $dml_pure_total_sum += $aryAddRow[$field];
//                }                
//            }
//            // 総計
//            $aryAddRow['sum_pure_total'] = $dml_pure_total_sum;
//            // 配列追加
//            array_push($ledgerSheetDetailList, $aryAddRow);
            
            
            // 画面フォームに日付を返す
            if(!isset($saledYear) OR $saledYear == "")
            {
                //現在日付の１日を設定
                $saledYear =date('Y');
            }

            // ファイル名
            //$file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');
            $file_path = date('YmsHis').'.csv';

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            //array_push($export_csv_title, "No");
            array_push($export_csv_title, "店舗");
            array_push($export_csv_title, "部門コード");
            //array_push($export_csv_title, "部門");
            //array_push($export_csv_title, "商品コード");
            array_push($export_csv_title, "商品名");
	    foreach ($ledgerSheetStaffList as $rows ) {
//                if($rows['staff_cd']){
//	            array_push($export_csv_title, $rows['staff_cd']);
//                }
            
                if ($rows === ''){
                    array_push($export_csv_title, '"担当なし"');
                }
                else{
                    array_push($export_csv_title, $rows);
                }
	    }
            //array_push($export_csv_title, "");
            array_push($export_csv_title, "合計");

            if( touch($file_path) ){
                // オブジェクト生成
                $file = new SplFileObject( $file_path, "w" ); 
                // タイトル行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
                foreach( $export_csv_title as $key => $val ){
                    $export_header[] = mb_convert_encoding($val, 'SJIS-win', 'UTF-8');
                }
 
                // エンコードしたタイトル行を配列ごとCSVデータ化
                $file = fopen($file_path, 'a');fwrite($file,implode(',',$export_header)."\r");
                
//                // 取得結果を画面と同じように再構築して行として搭載
//                // 内容行のエンコードをSJIS-winに変換（一部環境依存文字に対応用）
//                $list_no = 0;
//                $prod_cd_back = '';
//                $staff_line=0;
//                $i=0;
//                foreach( $ledgerSheetDetailList as $rows ){
//                    
//                    if($prod_cd_back !== $rows['prod_cd'] ){
//                        if($prod_cd_back !== ""){
//                            if($i < count($ledgerSheetStaffList)){
//                                for($i=$staff_line;$i<count($ledgerSheetStaffList);$i++){ 
//                                    $str = $str.'","';
//                                }                               
//                            }
//                            $str = $str.'","'.$totsubtotal.'"';
//                            // 配列に変換
//                            $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');                            
//                            // 内容行を1行ごとにCSVデータへ
//                            $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");                            
//                        }
//                        $prod_cd_back = $rows['prod_cd'];
//                        $staff_line=0;
//                        $list_no += 1;
//                        $totsubtotal=0;
//                        $str = '"'.$list_no;// No
//                        $str = $str.'","'.$rows['sect_cd'];
//                        $str = $str.'","'.$rows['sect_nm'];
//                        $str = $str.'","'.$rows['prod_cd'];
//                        $str = $str.'","'.$rows['prod_nm'];                        
//                        for($i=$staff_line;$i<count($ledgerSheetStaffList);$i++){
//                            if($rows['staff_cd'] == $ledgerSheetStaffList[$i]['staff_cd']){
//                                $str = $str.'","'.round($rows['subtotal'],0);
//                                $totsubtotal += $rows['subtotal'];
//                                $staff_line=$i+1;
//                                break;
//                            }else{
//                                $str = $str.'","';
//                            }
//                        }
//                    }else{
//                        for($i=$staff_line;$i<count($ledgerSheetStaffList);$i++){
//                            if($rows['staff_cd'] == $ledgerSheetStaffList[$i]['staff_cd']){
//                                $str = $str.'","'.round($rows['subtotal'],0);
//                                $totsubtotal += $rows['subtotal'];
//                                $staff_line=$i+1;
//                                break;
//                            }else{
//                                $str = $str.'","';
//                            }
//                        }                        
//                    }
//                    
//                    
//                    
//                    
////                    if($prod_nm_back !== $rows['prod_cd'] && $prod_nm_back !== ""){
////                            $str = $str.'"';
////                            // 配列に変換
////                            $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
////                            //$export_arr = explode(",", $str);
////
////                            // 内容行を1行ごとにCSVデータへ
////                            $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");
////                    }                    
////
////                    if($prod_nm_back == '' || $prod_nm_back !== $rows['prod_cd']){
////                        // 垂直ヘッダ
////                        $list_no += 1;
////                        $str = '"'.$list_no;// No
////
////                        $prod_nm_back = $rows['prod_cd'];
////
////                        $str = $str.'","'.$rows['sect_cd'];
////                        $str = $str.'","'.$rows['sect_nm'];
////                        $str = $str.'","'.$rows['prod_cd'];
////                        $str = $str.'","'.$rows['prod_nm'];
////                        if (is_null($rows['subtotal']) || empty($rows['subtotal'])){
////                                $str = $str.'","'.'';
////                        }else{
////                                $str = $str.'","'.$rows['subtotal'];
////                        }
////                    }else{
////                        if (is_null($rows['subtotal']) || empty($rows['subtotal'])){
////                                $str = $str.'","'.'';
////                        }else{
////                                $str = $str.'","'.$rows['subtotal'];
////                        }
////                    } 
//                }
//                if($i < count($ledgerSheetStaffList)){
//                    for($i=$staff_line;$i<count($ledgerSheetStaffList);$i++){ 
//                        $str = $str.'","';
//                    }                               
//                }
//                $str = $str.'","'.$totsubtotal.'"';
//                // 配列に変換
//                $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');                            
//                // 内容行を1行ごとにCSVデータへ
//                $file = fopen($file_path, 'a');fwrite($file,"\n".$str."\r");                 
                $int=0;
                foreach( $ledgerSheetDetailList as $rows ){
                    $str  = '';
                    $str .= '"'.$rows['abbreviated_name'].'"';
                    $str .= ',"'.$rows['sect_cd'].'"';
                    $str .= ',"'.$rows['prod_nm'].'"';
                    for ($intL = 0; $intL < count($ledgerSheetStaffList); $intL ++){
                        $field = 'pure_total'.strval($intL);
                        $str .= ',"';
                        $str .= $rows[$field] === '' ? '' : number_format($rows[$field], 0, '.', '');
                        $str .= '"';
                    }
                    $str .= ',"'.number_format($rows['sum_pure_total'], 0, '.', '');
                    $str .= '"';
                    // 配列に変換
                    $str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');                            
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
            header("Content-Disposition: attachment; filename=販売員別選定品(税込)".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
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
                'sect_cdSortMark'       => '',
                'sect_nmSortMark'       => '',
                'prod_cdSortMark'       => '',
                'prod_nmSortMark'       => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1]    = "tenpoSortMark";
                $sortList[2]    = "tenpoSortMark";
                $sortList[3]    = "sect_cdSortMark";
                $sortList[4]    = "sect_cdSortMark";
                $sortList[5]    = "sect_nmSortMark";
                $sortList[6]    = "sect_nmSortMark";
                $sortList[7]    = "prod_cdSortMark";
                $sortList[8]    = "prod_cdSortMark";
                $sortList[9]    = "prod_nmSortMark";
                $sortList[10]   = "prod_nmSortMark";

                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("START changeHeaderItemMark");

            return $headerArray;
        }
    }
?>
