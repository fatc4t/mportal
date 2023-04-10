<?php
    /**
     * @file      システム管理 - 電子ジャーナル検索コントローラ
     * @author    millionet oota
     * @date      2020/03/25
     * @version   1.00
     * @note      システム管理 - 電子ジャーナル検索の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/Ejournal.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   電子ジャーナルの検索・閲覧を行う
     */
    class EjournalController extends BaseController
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
         * 電子ジャーナル一覧画面初期表示
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
         * 電子ジャーナル一覧画面検索表示
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
         * 電子ジャーナル一覧画面
         * @note     
         * @return   無
         */
        private function initialDisplay($mode)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            // モデル読み込み
            $ejournal = new ejournal();

            // モーダル各種取得
            $modal = new Modal();
            // 店舗リスト
            $org_id_list    = [];
            $org_id_list    = $modal->getorg_detail();
            
            // 担当者リスト
            $staff_detail   = [];
            $staff_detail   = $modal->getstaff_detail();  
            
            // 商品リスト
            $prod_detail    = [];
            $prod_detail    = $modal->getprod_detail();
            
            // パラメーター用変数
            $param          = [];
            
            // 画面フォームを取得
            if($_POST){
                $param["org_r"]           = parent::escStr( $_POST['org_r'] );
                $param["org_nm_lst"]      = parent::escStr( $_POST['org_nm_lst'] );             
                $param["org_select"]      = parent::escStr( $_POST['org_select'] );             
                $param["prod_r"]          = parent::escStr( $_POST['prod_r'] );
                $param["prod_nm_lst"]     = parent::escStr( $_POST['prod_nm_lst'] );             
                $param["prod_select"]     = parent::escStr( $_POST['prod_select'] );             
                $param["staff_r"]         = parent::escStr( $_POST['staff_r'] );
                $param["staff_nm_lst"]    = parent::escStr( $_POST['staff_nm_lst'] );             
                $param["staff_select"]    = parent::escStr( $_POST['staff_select'] );             
            }

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
            
            // 条件の初期設定
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

            $staff_cd = 'false';
            $staff_nm_lst = '';
            if($_POST['staff_r'] === ""){
                if($_POST['staff_select'] && $_POST['staff_select'] === 'empty'){
                    $staff_cd = $_POST['staff_cd'];
                    $staff_nm_lst = $_POST['staff_nm_lst'];
                }else{
                    $staff_cd = "'".$_POST['staff_select']."'";
                    $staff_nm_lst = "'".$_POST['staff_nm_lst']."'";
                }
            }
            
            $rn1 = parent::escStr( $_POST['rn1'] );
            $rn2 = parent::escStr( $_POST['rn2'] );
            $mode_chk = parent::escStr( $_POST['mode_chk'] );
            $t_time_h = parent::escStr( $_POST['t_time_h'] );
            $t_time_m = parent::escStr( $_POST['t_time_m'] );
            $keyword = parent::escStr( $_POST['keyword'] );

            // 初期表示かどうかのチェック
            if($mode != "initial"){

                // 集計条件を取得
                $sqlSearchArray = array(
                    'start_date'        => $startDate,
                    'end_date'          => $endDate,
                    'organization_id'   => $organizationId,
                    'rn1'               => $rn1,
                    'rn2'               => $rn2,
                    'mode_chk'          => $mode_chk,
                    't_time_h'          => $t_time_h,
                    't_time_m'          => $t_time_m,
                    'keyword'           => $keyword,
                    'prod_cd'           => $prod_cd,
                    'staff_cd'          => $staff_cd,
                    '$staff_nm_lst'     => $staff_nm_lst,
                );
                
                // jジャーナルテーブルからデータを取得
               $ejournalList = $ejournal->getFormListData($sqlSearchArray);

                // ページ系情報の整理
                $nowcnt = 0;  // 現在の件数を変数化
                $maxcnt = 1;  // 最大件数を変数化

                // 編集用array
                $oneArray = array();

                // 一覧用データ用array
                $line_date = array();

                // KEY_ON
                $KEY_NO = 1;

                // 変数初期化
                $TYPE = "";

                // 1行ごとにチェックして、閲覧用データと一覧用データを生成
                foreach($ejournalList as $data){
                    $nowcnt = 1;  // 現在の件数を1に設定

                    // ヘッダの場合は各種情報を判定して生成
                    // 伝票ヘッダの判定
                    if ("*S" == substr($data["receipt_info"],0,2) && "-TEST" <> substr($data["receipt_data"],6,5)){

                         // ヘッダ行から情報取得
                         $RECEIPT_CD = substr($data["receipt_data"],4,7);// レシートNoを取得
                        
                         // 担当者名を取得
                         $str = mb_strpos($data["receipt_data"], '[担:', 0, SJIS);
                         
                         if($str != false && $str > 0){
                            $strtemp = substr($data["receipt_data"],mb_strpos($data["receipt_data"], '[担:', 0, SJIS),14);
                            $strtemp = trim($strtemp);
                            $strtemp = str_replace('[担:','',$strtemp);
                            $strtemp = str_replace(']','',$strtemp);
                            
                             // 担当者名を設定
                             $STAFF_NM = $strtemp;
                            
                         }

                         // 店舗名を設定
                         $TEMPO_NM = $data["abbreviated_name"];
                         
                         // 表示用のデータを詰める
                         $DETAIL = $DETAIL.$data["receipt_data"]."\n";
                         // 次の要素へ
                         continue;
                         
                     }else if ("*D" == substr($data["receipt_info"],0,2)){
                         // 日付のヘッダの判定
                         // 日付を取得
                         $DAY = substr($data["receipt_data"],2,14);

                         // 時間を取得
                         $TIME = substr($data["receipt_data"],26,12);

                         // 表示用のデータを詰める
                         $DETAIL = $DETAIL.$data["receipt_data"]."\n";
                         // 次の要素へ
                         continue;
                         
                     }else{
                         
                        // タイプ判別
                        $str = mb_strpos($data["receipt_data"], '【一括返品】', 0, SJIS);
                        if($str > 0){
                            $TYPE = '一括返品';
                        }

                        $str = mb_strpos($data["receipt_data"], '【取引中止】', 0, SJIS);
                        if($str > 0){
                            $TYPE = '取引中止';
                        }
                        
                        $str = mb_strpos($data["receipt_data"], '【得点増減】', 0, SJIS);
                        if($str > 0){
                            $TYPE = '得点増減';
                        }
                        
                        $str = mb_strpos($data["receipt_data"], '入金', 0, SJIS);
                        if($str > 0){
                            $TYPE = '入出金';
                        }
                        
                        $str = mb_strpos($data["receipt_data"], '掛入金', 0, SJIS);
                        if($str > 0){
                            $TYPE = '掛入金';
                        }
                        
                        $str = mb_strpos($data["receipt_data"], '出金', 0, SJIS);
                        if($str > 0){
                            $TYPE = '入出金';
                        }
                        
                        if($TYPE === ''){
                            $TYPE = '売上';
                        }
                        
                        // 金額取得
                        $str = mb_strpos($data["receipt_data"], '合計', 0, SJIS);
                         
                         // 金額の取得
                        if($str === 0){
                            $total_tmp = substr($data["receipt_data"],28,32);
                            $total_tmp = str_replace('合計','',$total_tmp);
                            $total_tmp = str_replace('\\','',$total_tmp);
                            $total_tmp = trim($total_tmp);

                            $TOTAL = $total_tmp;
                        }
                         
                         // 表示用のデータを詰める
                         $DETAIL = $DETAIL.$data["receipt_data"]."\n";
                     }

                     // ブロック終了時にリスト計上＆$KEY_ONのカウントアップ
                     if("*E" == substr($data["receipt_info"],0,2) ){
 
                         // line計上
                         $oneArray = array(
                            'KEY_NO'        => $KEY_NO,
                            'TEMPO_NM'      => $TEMPO_NM,
                            'RECEIPT_CD'    => $RECEIPT_CD,
                            'DAY'           => $DAY,
                            'TIME'          => $TIME,
                            'STAFF_NM'      => $STAFF_NM,
                            'TYPE'          => $TYPE,
                            'TOTAL'         => $TOTAL,
                            'DETAIL'        => $DETAIL,
                             );

                         // 行データに搭載
                         array_push($line_date, $oneArray);

                         // KEY_NOをカウントアップ
                         $KEY_NO++;
                         
                         // 表示用データのクリア
                         $TEMPO_NM = "";
                         $RECEIPT_CD = "";
                         $DAY = "";
                         $TIME = "";
                         $STAFF_NM = "";
                         $TYPE = "";
                         $TOTAL = "";
                         $DETAIL = "";

                    }
                }
            }
            // 最大数を設定
            $maxcnt = $KEY_NO - 1;

            // 検索条件を画面に戻す
            $searchArray = array(
                'org_id'            => str_replace("'","",$_POST['org_id']),
                'staff_cd'          => str_replace("'","",$_POST['staff_cd']),
                'prod_cd'           => str_replace("'","",$_POST['prod_cd']),
            );

            $Log->trace("END   initialDisplay");

            if($mode == "show" || $mode == "initial")
            {
                require_once './View/EjournalPanel.html';
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
            
            $ejournal = new ejournal();

            // 日付リストを取得
            $startDateM = parent::escStr( $_POST['start_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDateM) OR $startDateM == ""){
                //現在日付の１日を設定
                $startDateM = date('Y/m', strtotime('first day of ' . $month));
            }
            
            // 月初を設定
            $startDate        = parent::escStr( $_POST['start_dateM'] );
            $startDate        = $startDate.'/01';
            // 月末を設定
            $endDate = date('Y/m/d', strtotime('last day of ' . $startDate));
            //modal
            $organizationId = 'false';
            if($_POST['org_r'] === ""){
                if($_POST['org_select'] && $_POST['org_select'] === 'empty'){
                    $organizationId = $_POST['org_id'];
                }else{
                    $organizationId = "'".$_POST['org_select']."'";
                }
            }
            
            $searchArray = array(
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'organization_id'   => $organizationId,
            );
            // 帳票フォーム一覧データ取得
            $ledgerSheetDetailList = $ejournal->getFormListData($searchArray);
            
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
            array_push($export_csv_title, "店舗");
            array_push($export_csv_title, "営業日数");
            array_push($export_csv_title, "税込売上額");
            array_push($export_csv_title, "税抜売上額");
            array_push($export_csv_title, "消費税8%");
            array_push($export_csv_title, "消費税10%");
            array_push($export_csv_title, "構成比");
            array_push($export_csv_title, "数量");
            array_push($export_csv_title, "平均数量");
            array_push($export_csv_title, "来店数");
            array_push($export_csv_title, "平均単価");
            array_push($export_csv_title, "客平均単価");
            array_push($export_csv_title, "引額");

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
                foreach( $ledgerSheetDetailList as $rows ){
                    
                    // 垂直ヘッダ
                    $str = '"'.$rows['abbreviated_name'];
                    
                    $str = $str.'","'.$rows['days'];
                    $str = $str.'","'.round($rows['pure_total_i']+$rows['tax_total_08']+$rows['tax_total_10'],0);
                    $str = $str.'","'.round($rows['pure_total_i'],0);
                    $str = $str.'","'.round($rows['tax_total_08'],0);
                    $str = $str.'","'.round($rows['tax_total_10'],0);
                    $str = $str.'","'.str_replace('%','',$rows['composition_ratio']);
                    $str = $str.'","'.round($rows['total_amount'],0);
                    $str = $str.'","'.round($rows['avg_amount'],0);
                    $str = $str.'","'.$rows['total_cnt'];
                    if($rows['total_amount'] == 0){
                        $str = $str.'","'."0";
                    }else{        
                        $avgAmntShouhin = $rows['pure_total_i'] / $rows['total_amount'];
                            if (is_nan($avgAmntShouhin)){
                                $str = $str.'","'."0";
                            }else{
                                $str = $str.'","'.round($avgAmntShouhin,0);
                            }
                    }
                    if($rows['total_cnt'] == 0){
                        $str = $str.'","'."0";
                    }else{
                        $avgAmntKokyaku =  $rows['pure_total_i'] / $rows['total_cnt']; 
                            if (is_nan($avgAmntKokyaku)){
                                $str = $str.'","'."0";
                            }else{
                                $str = $str.'","'.round($avgAmntKokyaku,0);
                            }
                    }
                    $str = $str.'","'.round($rows['hiki_total'],0);
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
            header("Content-Disposition: attachment; filename=店舗別売上月報".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }
        
    }
?>
