<?php
    /**
     * @file      帳票 - 日次
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note     帳票 - 日次 -の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // コスト帳票処理モデル
    require './Model/LedgerSheetCost.php';

    require '../attendance/Model/AggregateLaborCosts.php';

    // Excel読み込み用ファイル
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel.php';
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel/IOFactory.php';
    
    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetCostController extends BaseController
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
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            
            $this->initialDisplay("show");
            
            $Log->trace("END   showAction");
        }

        /**
         * CSV出力
         * @return    なし
         */
        public function csvoutputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START csvoutputAction");
            
            $ledgerSheetCost = new LedgerSheetCost();
            $laborCosts = new AggregateLaborCosts();

            $searchArray = array(
                'ledger_sheet_form_id'  => '11',
            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetCostList = $ledgerSheetCost->getFormListData($searchArray);

            // ファイル名
            $file_path = mb_convert_encoding("./Temp/".$ledgerSheetCostList[0]["ledger_sheet_form_name"].".csv", 'SJIS-win', 'UTF-8');
 
            // ヘッダ情報、Ｍコード情報取り出し 
            foreach($ledgerSheetCostList as $data){
                $headr = explode(",", $data["header"]);
                $logic = explode(",", $data["logic"]);
            }

            // ヘッダリスト構築
            $headrList = array();
            $maxColspan = 0;
            $maxRowspan = 0;
            $colFlg = true;
            for ($hc = 0; $hc < count($headr); $hc++){

                // 4要素ずつでローテーション
                $head = array(
                   'colspan'   => $headr[$hc],      // １つ目 水平方向（colspan）の値
                   'rowspan'   => $headr[$hc + 1],  // ２つ目 垂直方向（rowspan）の値
                   'headrName' => $headr[$hc + 2],  // ３つ目 ヘッダ名称
                   'width'     => $headr[$hc + 3],  // ４つ目 幅（width）の値
                );
                // 横幅を計測
                if($headr[$hc] != 0 && $colFlg){
                    $maxColspan = $maxColspan + $headr[$hc];
                }else{
                    $colFlg = false;
                }
                
                // 先頭の空枠用に最大rowspanを保持
                if($maxRowspan <= $headr[$hc + 1]){
                    $maxRowspan = $headr[$hc + 1];
                }

                array_push($headrList, $head);
                $hc+= 3;
                
            }
            
            // 日付リストを取得
            $startDate = parent::escStr( $_POST['start_date'] );
            $endDate = parent::escStr( $_POST['end_date'] );
            $startDateM = parent::escStr( $_POST['start_dateM'] );
            $endDateM = parent::escStr( $_POST['end_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDate) OR $startDate == ""){
                //現在日付の１日を設定
                $startDate =date('Y/m/d', strtotime('first day of ' . $month));
            }

            if(!isset($endDate) OR $endDate == ""){
                //現在日付の末日を設定
                $endDate = date('Y/m/d', strtotime('last day of ' . $month));
            }
            
            if(!isset($startDateM) OR $startDateM == ""){
                //現在日付の１日を設定
                $startDateM =date('Y/m', strtotime('first day of ' . $month));
            }

            if(!isset($endDateM) OR $endDateM == ""){
                //現在日付の末日を設定
                $endDateM = date('Y/m', strtotime('last day of ' . $month));
            }

            // 組織リスト取得
            $orgList = $ledgerSheetCost->getAllOrganizationList();
            
            // Ｍコードリスト構築
            $mcodeList = array();

            // 組織数分実行
            foreach($orgList as $dateListOne){
                
                $dayList = array();
                
                for ($hc = 0; $hc < count($logic); $hc++){

                    $value = 0;

                    $searchMCArray = array(
                        'is_del'            => 0,                         // 削除フラグ
                        'mcode'             => $logic[$hc + 1],           // 対象Ｍコード
                    );

                    // 対象Mコード情報を取得
                    $mdeta = array();
                    $mdeta = $ledgerSheetCost->getMListData( $searchMCArray );

                    // Mコードのタイプを判別
                    if($mdeta[0]["mapping_type"] == "17"){
                        // Mコード種別が人件費だったら
                        $orgIDList = array();
                        array_push($orgIDList, $dateListOne["organization_id"]);

                        // 人件費データ取得
                        $ret = $laborCosts->getOrgLaborAggregateData( $orgIDList, $startDate, $endDate, 1, 3, false );
                        
                        if(count($ret) == 0){
                            $value = 0;
                        }else{
                            foreach($ret as $datam){
                                if($logic[$hc + 1] == "172001"){
                                // 正社員
                                    if($datam["code"] == "1100"){
                                        $value = $value + $datam["rough_estimate"];
                                    }
                                }else if($logic[$hc + 1] == "172002"){
                                // パートアルバイト
                                    if($datam["code"] == "0005" or $datam["code"] == "0006" ){
                                        $value = $value + $datam["rough_estimate"];
                                    }
                                }else{
                                // 総人件費
                                    $value = $value + $datam["rough_estimate"];
                               }
                            }
                        }
                        
                    }else{

                        $searchMArray = array(
                            'organization_id'   => $dateListOne["organization_id"],  // 検索組織
                            'startDate'         => $startDate,                       // 検索日付開始
                            'endDate'           => $endDate,                         // 検索日付開始
                            'mcode'             => $logic[$hc + 1],                  // 対象Ｍコード
                        );
                        
                        // Mコード種別が連携データだったら → 取込みデータを取得
                        $mCodeDataCostList = $ledgerSheetCost->getMcodeListData($searchMArray);
                        
                        if(count($mCodeDataCostList) == 0){
                            $value = 0;
                        }else{
                            foreach($mCodeDataCostList as $datam){
                                $value = $datam["data"];
                            }
                        }
                    }
                    
                    // Mコード種別が報告書取得だったら

                    // Mコード種別が計算式だったら

                    // 3要素ずつでローテーション
                    $mcode = array(
                        'type'   => $logic[$hc],      // １つ目 種別コード(0:当年、1:前年)
                        'mcode'  => $logic[$hc + 1],  // ２つ目 Ｍコード
                        'width'  => $logic[$hc + 2],  // ３つ目 幅（width）の値
                        'value'  => $value,           // データ
                    );
                    // 1セル分搭載
                    array_push($dayList, $mcode);
                    $hc+= 2;
                
                }
                //１行分搭載
                array_push($mcodeList, $dayList);
            }

            $arrayStr = array( "項目名" );
            
            // CSVに出力するヘッダ行
            foreach( $orgList as $oList ){
                array_push($arrayStr,$oList["organization_name"]);
            }

            $export_csv_title = $arrayStr;
 
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
                $headrList_no = 0;
                foreach( $headrList as $val ){
                    $str =  mb_convert_encoding($val["headrName"], 'SJIS-win', 'UTF-8');  // 項目名

                    foreach( $mcodeList as $mcode ){
                        $str =  $str.",".$mcode[$headrList_no]['value'];                  // 値
                    }

                    $headrList_no += 1;

                    $export_arr = explode(",",$str);
                    // 内容行を配列ごとCSVデータ化
                    $file->fputcsv($export_arr);
                }
            }
 
            // ダウンロード用
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers 
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=コスト帳票".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
            header("Content-Disposition: attachment; filename=".basename($file_path).";" );
            header("Content-Transfer-Encoding: binary");
            readfile("$file_path");
            $Log->trace("END   csvoutputAction");

        }

        /**
         * Excel出力
         * @note     Excel出力
         * @return   無
         */
        public function exoutputAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START exoutputAction");

            $this->initialDisplay("exoutput");
            
            $Log->trace("END   exoutputAction");
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

            $ledgerSheetCost = new LedgerSheetCost();
            $laborCosts = new AggregateLaborCosts();

            $searchArray = array(
                'ledger_sheet_form_id'  => '11',
            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetCostList = $ledgerSheetCost->getFormListData($searchArray);

            // ヘッダ情報、Ｍコード情報取り出し 
            foreach($ledgerSheetCostList as $data){
                $headr = explode(",", $data["header"]);
                $logic = explode(",", $data["logic"]);
            }
            
            // ヘッダリスト構築
            $headrList = array();
            $maxColspan = 0;
            $maxRowspan = 0;
            $colFlg = true;
            
            for ($hc = 0; $hc < count($headr); $hc++){

                // 4要素ずつでローテーション
                $head = array(
                   'colspan'   => $headr[$hc],      // １つ目 水平方向（colspan）の値
                   'rowspan'   => $headr[$hc + 1],  // ２つ目 垂直方向（rowspan）の値
                   'headrName' => $headr[$hc + 2],  // ３つ目 ヘッダ名称
                   'width'     => $headr[$hc + 3],  // ４つ目 幅（width）の値
                );
                // 横幅を計測
                if($headr[$hc] != 0 && $colFlg){
                    $maxColspan = $maxColspan + $headr[$hc];
                }else{
                    $colFlg = false;
                }
                
                // 先頭の空枠用に最大rowspanを保持
                if($maxRowspan <= $headr[$hc + 1]){
                    $maxRowspan = $headr[$hc + 1];
                }

                array_push($headrList, $head);
                $hc+= 3;
                
            }
            
            // 日付リストを取得
            $startDate = parent::escStr( $_POST['start_date'] );
            $endDate = parent::escStr( $_POST['end_date'] );
            $startDateM = parent::escStr( $_POST['start_dateM'] );
            $endDateM = parent::escStr( $_POST['end_dateM'] );

            // 画面フォームに日付を返す
            if(!isset($startDate) OR $startDate == ""){
                //現在日付の１日を設定
                $startDate =date('Y/m/d', strtotime('first day of ' . $month));
            }

            if(!isset($endDate) OR $endDate == ""){
                //現在日付の末日を設定
                $endDate = date('Y/m/d', strtotime('last day of ' . $month));
            }
            
            if(!isset($startDateM) OR $startDateM == ""){
                //現在日付の１日を設定
                $startDateM =date('Y/m', strtotime('first day of ' . $month));
            }

            if(!isset($endDateM) OR $endDateM == ""){
                //現在日付の末日を設定
                $endDateM = date('Y/m', strtotime('last day of ' . $month));
            }

            // 組織リスト取得
            $orgList = $ledgerSheetCost->getAllOrganizationList();
            
            // Ｍコードリスト構築
            $mcodeList = array();
            
            // 組織数分実行
            foreach($orgList as $dateListOne){
                
                $dayList = array();
                
                for ($hc = 0; $hc < count($logic); $hc++){

                    $value = 0;

                    $searchMCArray = array(
                        'is_del'            => 0,                         // 削除フラグ
                        'mcode'             => $logic[$hc + 1],           // 対象Ｍコード
                    );

                    // 対象Mコード情報を取得
                    $mdeta = array();
                    $mdeta = $ledgerSheetCost->getMListData( $searchMCArray );

                    // Mコードのタイプを判別
                    if($mdeta[0]["mapping_type"] == "17"){
                        // Mコード種別が人件費だったら
                        $orgIDList = array();
                        array_push($orgIDList, $dateListOne["organization_id"]);

                        // 人件費データ取得
                        $ret = $laborCosts->getOrgLaborAggregateData( $orgIDList, $startDate, $endDate, 1, 3, false );
                        
                        if(count($ret) == 0){
                            $value = 0;
                        }else{
                            foreach($ret as $datam){
                                if($logic[$hc + 1] == "172001"){
                                // 正社員
                                    if($datam["code"] == "1100"){
                                        $value = $value + $datam["rough_estimate"];
                                    }
                                }else if($logic[$hc + 1] == "172002"){
                                // パートアルバイト
                                    if($datam["code"] == "0005" or $datam["code"] == "0006" ){
                                        $value = $value + $datam["rough_estimate"];
                                    }
                                }else{
                                // 総人件費
                                    $value = $value + $datam["rough_estimate"];
                               }
                            }
                        }
                        
                    }else{

                        $searchMArray = array(
                            'organization_id'   => $dateListOne["organization_id"],  // 検索組織
                            'startDate'         => $startDate,                       // 検索日付開始
                            'endDate'           => $endDate,                         // 検索日付開始
                            'mcode'             => $logic[$hc + 1],                  // 対象Ｍコード
                        );
                        
                        // Mコード種別が連携データだったら → 取込みデータを取得
                        $mCodeDataCostList = $ledgerSheetCost->getMcodeListData($searchMArray);
                        
                        if(count($mCodeDataCostList) == 0){
                            $value = 0;
                        }else{
                            foreach($mCodeDataCostList as $datam){
                                $value = $datam["data"];
                            }
                        }
                    }
                    
                    // Mコード種別が報告書取得だったら

                    // Mコード種別が計算式だったら

                    // 3要素ずつでローテーション
                    $mcode = array(
                        'type'   => $logic[$hc],      // １つ目 種別コード(0:当年、1:前年)
                        'mcode'  => $logic[$hc + 1],  // ２つ目 Ｍコード
                        'width'  => $logic[$hc + 2],  // ３つ目 幅（width）の値
                        'value'  => $value,           // データ
                    );
                    // 1セル分搭載
                    array_push($dayList, $mcode);
                    $hc+= 2;
                
                }
                //１行分搭載
                array_push($mcodeList, $dayList);
            }

            $mcodeList_no = 0;
            
            $Log->trace("END   initialDisplay");

            if($mode == "show"){
                require_once './View/LedgerSheetCostPanel.html';
            }else{
                require_once './View/LedgerSheetCostEXPanel.html';
            }
        }

        
    }
?>
