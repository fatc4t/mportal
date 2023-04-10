<?php
    /**
     * @file      帳票 - 日次コントローラ
     * @author    media craft
     * @date      2018/03/22
     * @version   1.00
     * @note      帳票 - 日次の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetClient.php';
    require '../attendance/Model/AggregateLaborCosts.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetClientController extends BaseController
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

            $ledgerSheetClient = new ledgerSheetClient();

            $startCode = parent::escStr( $_POST['start_code'] );
            $endCode = parent::escStr( $_POST['end_code'] );
            $startDate = parent::escStr( $_POST['start_date'] );
            $endDate = parent::escStr( $_POST['end_date'] );
            $dmCode = parent::escStr( $_POST['dm_code'] );
            $custName = parent::escStr( $_POST['cust_nm'] );
            $addr = parent::escStr( $_POST['addr'] );


            $searchArray = array(
                'start_code'  => $startCode,
                'end_code'    => $endCode,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'dm_code'     => $dmCode,
                'cust_name'   => $custName,
                'addr'        => $addr,
                'birth'       => $birth
            );


            $list = $ledgerSheetClient->getFormListData($searchArray);

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');
            $mpdf=new mPDF('ja+aCJK', 'A4-L');

            $header = '<!DOCTYPE html>'
                     .'<html>'
                     .'<head>'
                     .'    <meta charset="utf-8" />'
                     .'    <title>顧客検索一覧</title>'
                     .'    <STYLE type="text/css">'
                     .'        html,body{width:100%;height:100%;}'
                     .'        header{width:100%;height:40px;}'
                     .'        main{width:100%;height:580px;}'
                     .'        footer{width:100%;height:30px;position:absolute;bottom:0;}'
                     .'    </STYLE>'
                     .'</head>'
                     .'<body>'
                     .'    <div>'
                     .'    <header>'
                     .'        <div align="left" style="line-height: 5px;">'
                     .'            <h1><span>顧客検索一覧</span></h1>'
                     .'        </div>'
                     .'        <table style="border-bottom:solid 3px #000000;line-height:10px;font-size:28px;">'
                     .'            <tr>'
                     .'                <th align="left" style="width:270px;">顧客コード</th>'
                     .'                <th align="center" style="width:260px;">顧客名</th>'
                     .'                <th align="center" style="width:370px;">住所１</th>'
                     .'                <th align="right" style="width:200px;">住所２</th>'
                     .'                <th align="right" style="width:230x;">DM不要区分</th>'
                     .'                <th align="right" style="width:150px;">生年月日</th>'
                     .'                <th align="right" style="width:200px;">登録年月日</th>'
                     .'            </tr>'
                     .'        </table>'
                     .'    </header>'
                     .'    <main>';

            $count = 1;
            $page = 1;
            $maxRow = 22;
            $sum = ceil(count($list)/$maxRow);

            foreach($list as $data){
                if($count == 1){
                    $html .= $header;
                }

                $html .= '        <table style="border-bottom: solid 1px #000000;line-height:20px;font-size:24px;">'
                        .'            <tr>'
                        .'                <td align="left" style="width:115;">'.$data['cust_cd'].'</td>'
                        .'                <td align="left" style="width:285px;">'.$data['cust_nm'].'</td>'
                        .'                <td align="left" style="width:295px;">'.$data['addr1'].'</td>'
                        .'                <td align="left" style="width:210px;">'.$data['addr2'].'</td>'
                        .'                <td align="left" style="width:40px;">'.$data['dissenddm'].'</td>'
                        .'                <td align="left" style="width:130px;">'.$data['birth'].'</td>'
                        .'                <td align="left" style="width:120px;">'.$data['insdatetime'].'</td>'
                        .'            </tr>'
                        .'        </table>';

                if($count > $maxRow || end($list) == $data){
                    $count = 0;
                    $html .= '</main>'
                            .'    <footer>'
                            .'        <table style="border-top: solid 3px #434359;line-height:10px;width:100%;font-size:20px;">'
                            .'            <tr>'
                            .'                <td style="width:800px; font-style:oblique;">'.date(Y年m月d日).'</td>'
                            .'                <td style="width:300px; font-style:oblique;">'.$page.'/'.$sum.'ページ</td>'
                            .'            <tr>'
                            .'        </table>'
                            .'    </footer>'
                            .'</div>'
                            .'</body>'
                            .'</html>';
                    $mpdf->WriteHTML("$html");
                    if(end($list) != $data){
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
         * @note     POS種別画面全てを更新
         * @return   無
         */
        private function initialDisplay($mode)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $ledgerSheetClient = new ledgerSheetClient();

            $startCode = parent::escStr( $_POST['start_code'] );
            $endCode = parent::escStr( $_POST['end_code'] );
            $startDate = parent::escStr( $_POST['start_date'] );
            $endDate = parent::escStr( $_POST['end_date'] );
            $dmCode = parent::escStr( $_POST['dm_code'] );
            $custName = parent::escStr( $_POST['cust_nm'] );
            $addr = parent::escStr( $_POST['addr'] );
            $birth = parent::escStr( $_POST['birth'] );

            // 初期表示以外だったら
            if($mode != "initial"){
                $searchArray = array(
                    'start_code'  => $startCode,
                    'end_code'    => $endCode,
                    'start_date'  => $startDate,
                    'end_date'    => $endDate,
                    'dm_code'     => $dmCode,
                    'cust_name'   => $custName,
                    'addr'        => $addr,
                    'birth'       => $birth
                );

                // 帳票フォーム一覧データ取得
                $ledgerSheetClientList = $ledgerSheetClient->getFormListData($searchArray);

                $list = $ledgerSheetClientList;
            }

            // 画面フォームに日付を返す
            if(!isset($startDate) OR $startDate == ""){
                //現在日付の１日を設定
                $startDate =date('Y/m/d', strtotime('first day of ' . $month));
            }

            if(!isset($endDate) OR $endDate == ""){
                //現在日付の末日を設定
                $endDate = date('Y/m/d', strtotime('last day of ' . $month));
            }

            if(!isset($dmCode) OR $dmCode == ""){
                $dmCode = "0";
            }
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial"){
                require_once './View/LedgerSheetClientPanel.html';
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
            
            $ledgerSheetClient = new ledgerSheetClient();

            $startCode = parent::escStr( $_POST['start_code'] );
            $endCode = parent::escStr( $_POST['end_code'] );
            $startDate = parent::escStr( $_POST['start_date'] );
            $endDate = parent::escStr( $_POST['end_date'] );
            $dmCode = parent::escStr( $_POST['dm_code'] );
            $custName = parent::escStr( $_POST['cust_nm'] );
            $addr = parent::escStr( $_POST['addr'] );
            $birth = parent::escStr( $_POST['birth'] );

                $searchArray = array(
                    'start_code'  => $startCode,
                    'end_code'    => $endCode,
                    'start_date'  => $startDate,
                    'end_date'    => $endDate,
                    'dm_code'     => $dmCode,
                    'cust_name'   => $custName,
                    'addr'        => $addr,
                    'birth'       => $birth
                );

                // 帳票フォーム一覧データ取得
                $ledgerSheetClientList = $ledgerSheetClient->getFormListData($searchArray);

            // 画面フォームに日付を返す
            if(!isset($startDate) OR $startDate == ""){
                //現在日付の１日を設定
                $startDate =date('Y/m/d', strtotime('first day of ' . $month));
            }

            if(!isset($endDate) OR $endDate == ""){
                //現在日付の末日を設定
                $endDate = date('Y/m/d', strtotime('last day of ' . $month));
            }

            if(!isset($dmCode) OR $dmCode == ""){
                $dmCode = "0";
            }

            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "No");
            array_push($export_csv_title, "顧客コード");
            array_push($export_csv_title, "顧客名");
            array_push($export_csv_title, "住所１ ・ 住所２ ・ 住所3");
            array_push($export_csv_title, "電話番号");
            array_push($export_csv_title, "携帯電話");
            array_push($export_csv_title, "DM区分");
            array_push($export_csv_title, "生年月日");
            array_push($export_csv_title, "更新日");
            array_push($export_csv_title, "登録年月日");

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
                foreach( $ledgerSheetClientList as $rows ){
                    
                    // 垂直ヘッダ
                    $list_no += 1;

                    $str = '"'.$list_no;// 順位

                    $str = $str.'","'.$rows['cust_cd'];
                    $str = $str.'","'.$rows['cust_nm'];
                    $str = $str.'","'.$rows['addr1'].$rows['addr2'].$rows['addr3'];
                    $str = $str.'","'.$rows['tel'];
                    $str = $str.'","'.$rows['hphone'];
                    $str = $str.'","'.$rows['dissenddm'];
                    $str = $str.'","'.$rows['birth'];
                    $str = $str.'","'.$rows['updatetime'];
                    $str = $str.'","'.$rows['insdatetime'];
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
            header("Content-Disposition: attachment; filename=顧客検索一覧".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
