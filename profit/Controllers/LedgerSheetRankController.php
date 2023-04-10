<?php
    /**
     * @file      帳票 - 商品実績順位表
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 商品を売上順に表示
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    //require './Model/LedgerSheetItem.php';
    require './Model/LedgerSheetRank.php';
    // Excel読み込み用ファイル
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel.php';
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel/IOFactory.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetRankController extends BaseController
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

            //$html = file_get_contents("./View/LedgerSheetRankPDFPanel.html");
            //$mpdf->WriteHTML("$html");
            //検索条件
            $searchArray = array();

            $prodCd1      = parent::escStr( $_POST['prod_cd1'] );  //商品コード(開始)
            $prodCd2      = parent::escStr( $_POST['prod_cd2'] );  //商品コード(終了)
            $prodKn       = parent::escStr( $_POST['prod_kn'] );   //品名
            $sectCd       = parent::escStr( $_POST['sect_cd'] );   //部門名
            $suppCd       = parent::escStr( $_POST['supp_cd'] );   //仕入先コード
            $jicfsClassCd = parent::escStr( $_POST['jicfs_class_cd'] );   //JICFS分類
            $privClassCd  = parent::escStr( $_POST['priv_class_cd'] );   //JICFS分類
            $prodDate1    = parent::escStr( $_POST['start_date'] );   //売上期間(開始)
            $prodDate2    = parent::escStr( $_POST['end_date'] );     //売上期間(終了)

            $searchArray = array(
                 'prod_cd1'       => $prodCd1
                ,'prod_cd2'       => $prodCd2
                ,'prod_kn'        => $prodKn
                ,'sect_cd'        => $sectCd
                ,'supp_cd'        => $suppCd
                ,'jicfs_class_cd' => $jicfsClassCd
                ,'priv_class_cd'  => $privClassCd
                ,'prod_date1'     => str_replace("/","",$prodDate1)
                ,'prod_date2'     => str_replace("/","",$prodDate2)
            );

            //一覧データ取得
            $ledgerSheetRank = new LedgerSheetRank();
            $list = $ledgerSheetRank -> getListData($searchArray);

            $html   = '';
            $header = '';
            //商品コード
            $prodCd = '';
            if(!empty($prodCd1))
            {
              $prodCd .= $prodCd1;
            }
            if(!empty($prodCd1) || !empty($prodCd1))
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
            if(empty($prodKn))
            {
              $prodKn = '未指定';
            }
            //部門
            if(empty($sectCd))
            {
              $sectNm = '未指定';
            } else {
              $sectNm = $ledgerSheetRank -> getSectData($sectCd);
            }
            //仕入先
            if(empty($suppCd))
            {
              $suppCd = '未指定';
            }
            //JICFS分類
            if(empty($jicfsClassCd))
            {
              $jicfsClassNm = '未指定';
            } else {
              $jicfsClassNm = $ledgerSheetRank -> getJicfsData($jicfsClassCd);
            }
            //自社コード
            if(empty($privClassCd))
            {
              $privClassNm = '未指定';
            } else {
              $privClassNm = $ledgerSheetRank -> getPrivData($privClassCd);
            }
            //売上期間
            $prodDate = '';
            if(!empty($prodDate1))
            {
              $prodDate .= $prodDate1;
            }
            if(!empty($prodDate1) || !empty($prodDate2))
            {
              $prodDate .= " ～ ";
            }
            if(!empty($prodDate2))
            {
              $prodDate .= $prodDate2;
            }
            if(empty($prodDate))
            {
              $prodDate = '未指定';
            }



            $count = 1;
            $page = 1;
            $maxRow = 17;
            $sum = ceil(count($list)/$maxRow);

            foreach($list as $data)
            {
                if($count == 1)
                {
                    $header  = '<!DOCTYPE html>';
                    $header .= '<html>';
                    $header .= '<head>';
                    $header .= '    <meta charset="utf-8" />';
                    $header .= '    <title>商品実績順位表</title>';
                    $header .= '    <STYLE type="text/css">';
                    $header .= '        html,body{width:100%;height:100%;}';
                    $header .= '        header{width:100%;height:95px;}';
                    $header .= '        main{width:100%;height:850px;}';
                    $header .= '        footer{width:100%;height:5px;position:absolute;bottom:5;}';
                    $header .= '        .barcode {';
                    $header .= '            padding: 1.5mm;';
                    $header .= '            margin: 0;';
                    $header .= '            vertical-align: top;';
                    $header .= '            color: #000044;';
                    $header .= '        }';
                    $header .= '        .barcodecell {';
                    $header .= '            text-align: center;';
                    $header .= '            vertical-align: middle;';
                    $header .= '        }';
                    $header .= '    </STYLE>';
                    $header .= '</head>';
                    $header .= '<body>';
                    $header .= '<!--<div style="page-break-before: always;">-->';
                    $header .= '    <div>';
                    $header .= '    <header>';
                    $header .= '        <div align="right">印刷日時：'.date("Y/m/d H:i:s").'</div>';
                    $header .= '        <div align="right">ページ：'.$page.' / '.$sum.'</div>';
                    $header .= '        <div align="center" style="font-style:oblique; line-height: 5px;">';
                    $header .= '            <h1 style="text-align:center;font-size:24px;"><span>商品実績順位表</span></h1>';
                    $header .= '        </div>';
                    $header .= '        <table style="width:100%;">';
                    $header .= '            <tr>';
                    $header .= '                <td align="left">商品コード： '.$prodCd.'</td>';
                    $header .= '            </tr>';
                    $header .= '            <tr>';
                    $header .= '                <td align="left">品名(カナ)： '.$prodKn.'</td>';
                    $header .= '            </tr>';
                    $header .= '            <tr>';
                    $header .= '                <td align="left">部門名： '.$sectNm.'</td>';
                    $header .= '            </tr>';
                    $header .= '            <tr>';
                    $header .= '                <td align="left">仕入先コード： '.$suppCd.'</td>';
                    $header .= '            </tr>';
                    $header .= '            <tr>';
                    $header .= '                <td align="left">大分類： 未指定</td>';
                    $header .= '            </tr>';
                    $header .= '            <tr>';
                    $header .= '                <td align="left">中分類： 未指定</td>';
                    $header .= '            </tr>';
                    $header .= '            <tr>';
                    $header .= '                <td align="left">小分類： 未指定</td>';
                    $header .= '            </tr>';
                    $header .= '            <tr>';
                    $header .= '                <td align="left">クラス： 未指定</td>';
                    $header .= '            </tr>';
                    $header .= '            <tr>';
                    $header .= '                <td align="left">JICFS分類： '.$jicfsClassNm.'</td>';
                    $header .= '            </tr>';
                    $header .= '            <tr>';
                    $header .= '                <td align="left">自社分類： '.$privClassNm.'</td>';
                    $header .= '            </tr>';
                    $header .= '            <tr>';
                    $header .= '                <td align="left">売上期間： '.$prodDate.'</td>';
                    $header .= '            </tr>';
                    $header .= '        </table>';
                    $header .= '        <table style="border-bottom:solid 1px #000000;width:100%;margin-top:10px;">';
                    $header .= '            <tr>';
                    $header .= '                <td align="right" width="45px">順位</td>';
                    $header .= '                <td align="center" width="130px">JANコード</td>';
                    $header .= '                <td align="left" width="220px">商品名</td>';
                    $header .= '                <td align="left" width="80">規格/容量</td>';
                    $header .= '                <td align="left" width="100">部門名</td>';
                    $header .= '                <td align="left">仕入先</td>';
                    $header .= '                <td align="right" width="60">数量</td>';
                    $header .= '                <td align="right" width="80">金額</td>';
                    $header .= '                <td align="right" width="60">構成比</td>';
                    $header .= '                <td align="right" width="80">粗利額</td>';
                    $header .= '                <td align="right" width="60">構成比</td>';
                    $header .= '                <td align="right" width="60">粗利率</td>';
                    $header .= '        </table>';
                    $header .= '    </header>';
                    $header .= '    <main>';
                    $header .= '      <table style="width:100%;">';

                    $html .= $header;
                }

                $html .= '			<tr style="padding-top:5px;padding-bottom:5px;">';
                $html .= '          <td align="right" width="45px">'.($count + ($page -1) * $maxRow).'</td>';
                $html .= '          <td align="center" width="130px">'.$data['jan_cd'].'</td>';
                $html .= '          <td align="left" width="220px">'.$data['prod_kn'].'</td>';
                $html .= '          <td align="left" width="80">'.$data['prod_capa_nm'].'</td>';
                $html .= '          <td align="left" width="100">'.$data['sect_nm'].'</td>';
                $html .= '          <td align="left">'.$data['supp_nm'].'</td>';
                $html .= '          <td align="right" width="60">'.number_format($data['prod_sale_amount']).'</td>';
                $html .= '          <td align="right" width="80">'.number_format($data['prod_sale_total']).'</td>';
                $html .= '          <td align="right" width="60">'.$data['per1'].'%</td>';
                $html .= '          <td align="right" width="80">'.number_format($data['prod_profit']).'</td>';
                $html .= '          <td align="right" width="60">'.$data['per2'].'%</td>';
                $html .= '          <td align="right" width="60">'.number_format($data['cost_rate'],2).'%</td>';
                $html .= '      </tr>';

                if($count > $maxRow || end($list) == $data)
                {
                    $count = 0;
                    $html .= '    </table>'
                             .'    <div style="border-top:1px solid #000000;"></div>'
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

            $ledgerSheetRank = new LedgerSheetRank();

            //検索条件の取得
            $sectionList = $ledgerSheetRank -> getSectionList();  //部門
            $jicfsList   = $ledgerSheetRank -> getJicfsList();  //jicfs分類
            $privList    = $ledgerSheetRank -> getPrivList();  //自社分類

            $searchArray = array();

            $prodCd1      = parent::escStr( $_POST['prod_cd1'] );  //商品コード(開始)
            $prodCd2      = parent::escStr( $_POST['prod_cd2'] );  //商品コード(終了)
            $prodKn       = parent::escStr( $_POST['prod_kn'] );   //品名
            $sectCd       = parent::escStr( $_POST['sect_cd'] );   //部門名
            $suppCd       = parent::escStr( $_POST['supp_cd'] );   //仕入先コード
            $jicfsClassCd = parent::escStr( $_POST['jicfs_class_cd'] );   //JICFS分類
            $privClassCd  = parent::escStr( $_POST['priv_class_cd'] );   //JICFS分類
            $prodDate1    = parent::escStr( $_POST['start_date'] );   //売上期間(開始)
            $prodDate2    = parent::escStr( $_POST['end_date'] );     //売上期間(終了)



            $searchArray = array(
                 'prod_cd1'       => $prodCd1
                ,'prod_cd2'       => $prodCd2
                ,'prod_kn'        => $prodKn
                ,'sect_cd'        => $sectCd
                ,'supp_cd'        => $suppCd
                ,'jicfs_class_cd' => $jicfsClassCd
                ,'priv_class_cd'  => $privClassCd
                ,'prod_date1'     => str_replace("/","",$prodDate1)
                ,'prod_date2'     => str_replace("/","",$prodDate2)
            );

            //データの取得
            if($mode != "initial")
            {
              $list = $ledgerSheetRank -> getListData($searchArray);
            }
            $Sizelist = [];
            $base = new BaseModel();
            $Sizelist = $base->getSizeList();
            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/LedgerSheetRankPanel.html';
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
            
            $ledgerSheetRank = new LedgerSheetRank();

            $searchArray = array();

            $prodCd1      = parent::escStr( $_POST['prod_cd1'] );  //商品コード(開始)
            $prodCd2      = parent::escStr( $_POST['prod_cd2'] );  //商品コード(終了)
            $prodKn       = parent::escStr( $_POST['prod_kn'] );   //品名
            $sectCd       = parent::escStr( $_POST['sect_cd'] );   //部門名
            $suppCd       = parent::escStr( $_POST['supp_cd'] );   //仕入先コード
            $jicfsClassCd = parent::escStr( $_POST['jicfs_class_cd'] );   //JICFS分類
            $privClassCd  = parent::escStr( $_POST['priv_class_cd'] );   //JICFS分類
            $prodDate1    = parent::escStr( $_POST['start_date'] );   //売上期間(開始)
            $prodDate2    = parent::escStr( $_POST['end_date'] );     //売上期間(終了)



            $searchArray = array(
                 'prod_cd1'       => $prodCd1
                ,'prod_cd2'       => $prodCd2
                ,'prod_kn'        => $prodKn
                ,'sect_cd'        => $sectCd
                ,'supp_cd'        => $suppCd
                ,'jicfs_class_cd' => $jicfsClassCd
                ,'priv_class_cd'  => $privClassCd
                ,'prod_date1'     => str_replace("/","",$prodDate1)
                ,'prod_date2'     => str_replace("/","",$prodDate2)
            );


            // 帳票フォーム一覧データ取得
            $list = $ledgerSheetRank -> getListData($searchArray);

            // ファイル名
            $file_path = mb_convert_encoding(str_replace("/", "",parent::escStr( $startDateM )).".csv", 'SJIS-win', 'UTF-8');

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "順位");
            array_push($export_csv_title, "商品コード");
            array_push($export_csv_title, "商品名");
            array_push($export_csv_title, "容量");
            array_push($export_csv_title, "部門コード");
            array_push($export_csv_title, "部門");
            array_push($export_csv_title, "仕入先コード");
            array_push($export_csv_title, "仕入先");
            array_push($export_csv_title, "数量");
            array_push($export_csv_title, "金額");
            array_push($export_csv_title, "構成比");
            array_push($export_csv_title, "粗利額");
            array_push($export_csv_title, "構成比");
            array_push($export_csv_title, "粗利率");

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
                foreach( $list as $rows ){
                    
                    // 垂直ヘッダ
                    $list_no += 1;

                    $str = '"'.$list_no;// 順位

                    $str = $str.'","'.$rows['prod_cd'];
                    $str = $str.'","'.$rows['prod_kn'];
                    $str = $str.'","'.$rows['prod_capa_nm'];
                    $str = $str.'","'.$rows['sect_cd'];
                    $str = $str.'","'.$rows['sect_nm'];
                    $str = $str.'","'.$rows['supp_cd'];
                    $str = $str.'","'.$rows['supp_nm'];
                    $str = $str.'","'.round($rows['prod_sale_amount'],0);
                    $str = $str.'","'.round($rows['prod_sale_total'],0);
                    $str = $str.'","'.round($rows['per1'],2);
                    $str = $str.'","'.round($rows['prod_profit'],0);
                    $str = $str.'","'.round($rows['per2'],2);
                    $str = $str.'","'.round($rows['cost_rate'],2);
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
            header("Content-Disposition: attachment; filename=商品実績順位表".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }

    }
?>
