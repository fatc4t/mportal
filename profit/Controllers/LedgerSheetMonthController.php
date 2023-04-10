<?php
    /**
     * @file      帳票 - 月次コントローラ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 月次の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetMonth.php';
    require '../attendance/Model/AggregateLaborCosts.php';
    
    // Excel読み込み用ファイル
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel.php';
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel/IOFactory.php';

    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetMonthController extends BaseController
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
            
            $ledgerSheetMonth = new ledgerSheetMonth();
            $abbreviatedNameList = $ledgerSheetMonth->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト
            $laborCosts = new AggregateLaborCosts();

            $searchArray = array(
                'ledger_sheet_form_id'  => '9',
            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetMonthList = $ledgerSheetMonth->getFormListData($searchArray);

            // ファイル名
            $file_path = mb_convert_encoding("./Temp/".$ledgerSheetMonthList[0]["ledger_sheet_form_name"].".csv", 'SJIS-win', 'UTF-8');

            // ヘッダ情報、Ｍコード情報取り出し 
            foreach($ledgerSheetMonthList as $data){
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
            
            // 検索組織
            $searchArray = array(
                'organizationID' => parent::escStr( $_POST['organizationName'] ),
            );
            
            // 日付リストを取得
            $startDateM = parent::escStr( $_POST['start_dateM'] );
            $endDateM = parent::escStr( $_POST['end_dateM'] );
            $weekday = array( '日', '月', '火', '水', '木', '金', '土' );

            // 画面フォームに日付を返す
            if(!isset($startDateM) OR $startDateM == ""){
                //現在日付の１日を設定
                $startDateM =date('Y/m', strtotime('first day of ' . $month));
            }

            if(!isset($endDateM) OR $endDateM == ""){
                //現在日付の末日を設定
                $endDateM = date('Y/m', strtotime('last day of ' . $month));
            }
            
            $date1=strtotime($endDateM.'/01');
            $date2=strtotime($startDateM.'/01');
            $month1=date("Y",$date1)*12+date("m",$date1);
            $month2=date("Y",$date2)*12+date("m",$date2);

            $diff = $month1 - $month2;

            $dateList = array();
            $dispDateList = array();
            
            for($i = 0; $i <= $diff; $i++) {
                $dPeriod = array('ddays' => date('Y年m月', strtotime($startDateM.'/01' . '+' . $i . 'month')));
                array_push($dispDateList, $dPeriod);

                $period = array('days' => date('Y/m/d', strtotime($startDateM.'/01' . '+' . $i . 'month')));
                array_push($dateList, $period);
            }            

            // 合計ヘッダを追加
//            $dPeriod = array('ddays' => '合計');
//            array_push($dispDateList, $dPeriod);

            // Ｍコードリスト構築
            $mcodeList = array();
            
            // 合計用の変数を動的に作成
            $sumValue[] = 0;
            for ($hh = 0; $hh < count($logic); $hh++){
                $sumValue[$hh] = 0;
                $hh+= 2;
            }

            // 日付範囲分実行
            foreach($dateList as $dateListOne){
                
                $dayList = array();
                
                for ($hc = 0; $hc < count($logic); $hc++){

                    // 条件に合わせて日付を構築
                    if($logic[$hc] == 1){
                        // 前年だったら日付を操作
                        $targetTime = strtotime($dateListOne["days"]);
                        $searchDay = date('Y/m/d', strtotime('-1 year', $targetTime));
                    }else{
                        // 当年だったらそのまま設定
                        $searchDay = $dateListOne["days"];
                    }
                    
                    $value = 0;
                    // Ｍコード情報を取得
                    $searchMArray = array(
                        'organization_id'   => $searchArray['organizationID'],             // 検索組織
                        'target_date'       => $searchDay,      // 検索日付開始
                        'mcode'             => $logic[$hc + 1], // 対象Ｍコード
                    );

                    // Ｍコード一覧データ取得
                    $mCodeDataList = $ledgerSheetMonth->getMcodeData($searchMArray);

                    // ロジック情報を分解
                    $mCodeLogic = explode(",", $mCodeDataList[0]["logic"]);
                    
                    // Ｍコードのタイプに合わせて取得先を変更
                    if($mCodeLogic[0] == '#'){
                        // 取得先が報告書の場合
                        
                        $searchrdArray = array(
                            'organization_id'          => $searchArray['organizationID'],  // 検索組織
                            'target_date'              => $searchDay,                      // 検索日付開始
                            'report_form_id'           => $mCodeLogic[1],                  // 報告書設定ID
                            'report_form_detail_id'    => $mCodeLogic[2],                  // 報告書詳細設定ID
                        );

                        // Ｍコード一覧データ取得
                        $mCodeDataDayList = $ledgerSheetMonth->getReportDataDay($searchrdArray);

                        if(count($mCodeDataDayList) == 0){
                            $value = 0;
                        }else{
                            foreach($mCodeDataDayList as $datam){
                                $value += $datam["data"];
                            }
                        }
                        
                    }else if($mCodeLogic[0] == '*'){
                        // 計算式の場合
                        // 符号以外を取り出す
                        $calStr = preg_split("/[\+\-\*\/()]/",$mCodeLogic[1]);

                        // 置換用リスト
                        $msList = array();
                        
                        // 数値の数だけ実施
                        for ($cs = 0; $cs < count($calStr); $cs++){
                            
                            $mEntity = array('mcode' => $calStr[$cs]);
                            
                            $searchrdArray = array(
                                'mapping_code'    => $calStr[$cs], // Mコード
                            );

                            // Mコードかどうかチェック
                            $checkMcode = $ledgerSheetMonth->checkMcode($searchrdArray);

                            if(!empty($checkMcode)){
                                if($calStr[$cs] == "172001" || $calStr[$cs] == "172002" || $calStr[$cs] == "172999"){
                                    // Mコード種別が人件費だったら
                                    $orgIDList = array();

                                    if($searchArray['organizationID'] != ""){
                                        array_push($orgIDList, $searchArray['organizationID']);
                                    }

                                    // 条件に合わせて日付を構築
                                    if($logic[$hc] == 1){
                                        // 前年だったら日付を操作
                                        $sDate = date('Y/m/d', strtotime('-1 year', strtotime($dateListOne["days"])));
                                        $eDate = date('Y/m/d', strtotime('+1 month', $sDate));
                                    }else{
                                        // 当年だったらそのまま設定
                                        $sDate = strtotime($dateListOne["days"]);
                                        $eDate = date('Y/m/d', strtotime('+1 month', $sDate));
                                    }

                                    // 人件費データ取得
                                    $entity = $this->getLabor($laborCosts, $logic[$hc + 1],$orgIDList,$dateListOne["days"],$eDate);
                                    $mEntity = $mEntity + array('val' => $entity);
                                }else{
                                    // データテーブルがどこかを判定するためMコードを再取得
                                    $searchMArraySub = array(
                                        'organization_id'   => $searchArray['organizationID'],             // 検索組織
                                        'target_date'       => $searchDay,      // 検索日付開始
                                        'mcode'             => $calStr[$cs],    // 対象Ｍコード
                                    );

                                    // Ｍコード一覧データ取得
                                    $mCodeDataListSub = $ledgerSheetMonth->getMcodeData($searchMArraySub);

                                    // ロジック情報を分解
                                    $mCodeLogicSub = explode(",", $mCodeDataListSub[0]["logic"]);

                                    if($mCodeLogicSub[0] == '#'){
                                        // 取得先が報告書の場合

                                        $searchMArraySub = array(
                                            'organization_id'          => $searchArray['organizationID'],  // 検索組織
                                            'target_date'              => $searchDay,                      // 検索日付開始
                                            'report_form_id'           => $mCodeLogicSub[1],                  // 報告書設定ID
                                            'report_form_detail_id'    => $mCodeLogicSub[2],                  // 報告書詳細設定ID
                                        );

                                        // Ｍコード一覧データ取得
                                        $mCodeDataDayListSub = $ledgerSheetMonth->getReportDataDay($searchMArraySub);

                                        if(count($mCodeDataDayListSub) == 0){
                                            $mEntity = $mEntity + array('val' => 0);
                                        }else{
                                            foreach($mCodeDataDayListSub as $datam){
                                                if($datam["data"] != "" && $datam["data"] != null){
                                                    $mEntity = $mEntity + array('val' => $datam["data"]);
                                                }else{
                                                    $mEntity = $mEntity + array('val' => 0);
                                                }
                                            }
                                        }
                                    }else{

                                        // 条件に合わせて日付を構築
                                        if($mCodeDataListSub[0]["logic_type"] == 2){
                                            // 前年だったら日付を操作
                                            $targetTime = strtotime($dateListOne["days"]);
                                            $searchDay2 = date('Y/m/d', strtotime('-1 year', $targetTime));

                                            // 報告書経由じゃない場合はPOSデータ、データを取得
                                            $searchMlArray = array(
                                                'organization_id'   => $searchArray['organizationID'], // 検索組織
                                                'target_date'       => $searchDay2,      // 検索日付開始
                                                'mcode'             => $mCodeDataListSub[0]["logic"], // 対象Ｍコード
                                            );

                                        }else{

                                            // 報告書経由じゃない場合はPOSデータ、データを取得
                                            $searchMlArray = array(
                                                'organization_id'   => $searchArray['organizationID'], // 検索組織
                                                'target_date'       => $searchDay,      // 検索日付開始
                                                'mcode'             => $calStr[$cs], // 対象Ｍコード
                                            );

                                        }

                                       $mCodeDataDayList = $ledgerSheetMonth->getMcodeListData($searchMlArray);

                                        if(count($mCodeDataDayList) == 0){
                                            $mEntity = $mEntity + array('val' => 0);
                                        }else{
                                            foreach($mCodeDataDayList as $datam){
                                                $mEntity = $mEntity + array('val' => $datam["data"]);
                                            }
                                        }
                                    }
                                }
                            }else{
                                // Mコードじゃない場合は、数値として文字列に変換
                                $mEntity = $mEntity + array('val' => $calStr[$cs]);
                            }
                            // 変換リストに搭載
                             array_push($msList, $mEntity);
                        }
                        
                        // データにしたものをキーを使って置換する
                        for ($chl = 0; $chl < count($msList); $chl++){
                            $mCodeLogic[1] = str_replace($msList[$chl]["mcode"],$msList[$chl]["val"],$mCodeLogic[1]);
                        }
                        
                        //完成した文字列を計算式として実行 
                        $value = round(eval("return ".$mCodeLogic[1].";"),2);
                        
                    }else{
                        // 取得先がPOSデータの場合
                        $searchMlArray = array(
                            'organization_id'   => $searchArray['organizationID'],             // 検索組織
                            'target_date'       => $searchDay,      // 検索日付開始
                            'mcode'             => $logic[$hc + 1], // 対象Ｍコード
                        );

                        // Ｍコード一覧データ取得
                        $mCodeDataDayList = $ledgerSheetMonth->getMcodeListData($searchMlArray);

                        if(count($mCodeDataDayList) == 0){
                            $value = 0;
                        }else{
                            foreach($mCodeDataDayList as $datam){
                                $value += $datam["data"];
                            }
                        }
                    }

                    // 3要素ずつでローテーション
                    $mcode = array(
                        'type'   => $logic[$hc],      // １つ目 種別コード(0:当年、1:前年)
                        'mcode'  => $logic[$hc + 1],  // ２つ目 Ｍコード
                        'width'  => $logic[$hc + 2],  // ３つ目 幅（width）の値
                        'value'  => $value,           // データ
                    );
                    // 1セル分搭載
                    array_push($dayList, $mcode);
                    // 合計用に加算
//                    $sumValue[$hc] += $value;
                    // ローテーションように要素を進める
                    $hc+= 2;
                
                }
                //１行分搭載
                array_push($mcodeList, $dayList);
            }

            // 合計行を搭載
            $sumList = array();
            
            for ($hs = 0; $hs < count($logic); $hs++){

                // 3要素ずつでローテーション
                    $sumMcode = array(
                        'type'   => 0,                // １つ目 種別コード(0:当年、1:前年)
                        'mcode'  => $logic[$hs + 1],  // ２つ目 Ｍコード
                        'width'  => $logic[$hs + 2],  // ３つ目 幅（width）の値
                        'value'  => $sumValue[$hs],   // データ
                    );
                    // 1セル分搭載
                    array_push($sumList, $sumMcode);
                    $hs+= 2;
            }

//            array_push($mcodeList, $sumList);

            $mcodeList_no = 0;

            // CSVに出力するヘッダ行
            $export_csv_title = array();

            // 先頭固定ヘッダ追加
            array_push($export_csv_title, "対象月");

            // 格納添字
            $inCnt = 1;
            
            // 一時保管用リスト
            $tmpList = array();

            // 階層附番用リスト
            $hList = array();
            
            // 一旦階層ごとに順番を附番する
            foreach($headrList as $head){
                // 改行かどうかチェック
                if($head["colspan"] == 0){
                    // 格納添字をクリア
                    $inCnt = 1;
                    // 1階層分搭載
                    array_push($tmpList, $hList);
                    // 階層附番用リストをクリア
                    $hList = array();
                }else{
                // 階層ごとに並べ替え
                    // colが1のものだけに集約
                    if($head["colspan"] == 1){
                        // 格納添字を付与して、カウントアップ
                        $head = array_merge($head,array('inCnt'=>$inCnt));
                        array_push($hList, $head);
                        $inCnt ++;
                    }else{
                        $inCnt += $head["colspan"];
                    }
                }
            }
            
            // 最後の階層を追加(最後に改行がないので直接のせるしかない)
            array_push($tmpList, $hList);
 
            // 一時保管最終リスト
            $lastHeadList = array();
            
            // 一時保管用リストを使って、番号の重複チェックと階層優先
            foreach($tmpList as $thead){
                // 順番が重複したら、上位階層を優先
                foreach($thead as $row){
                     foreach($lastHeadList as $tmpRow){
                         if($tmpRow["inCnt"] == $row["inCnt"]){
                             $row["inCnt"] ++;
                         }
                     }
                     // チェック後の附番で格納
                     array_push($lastHeadList, $row);

                    // 格納したら並び替え
                    foreach ($lastHeadList as $key => $value){
                        $key_id[$key] = $value['inCnt'];
                    }
                    array_multisort ( $key_id , SORT_ASC , $lastHeadList);
                }
            }
            
            // 整理し終わったヘッダリストを格納
            foreach($lastHeadList as $lhead){
                // 下位階層は重複分後の番号になる
                array_push($export_csv_title, $lhead["headrName"]);
            }
                
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
                $mcodeList_no = 0;
                foreach( $mcodeList as $rows ){
                    
                    // 垂直ヘッダ
                    $str = mb_convert_encoding($dispDateList[$mcodeList_no]['ddays'], 'SJIS-win', 'UTF-8');   // 日付
                    $mcodeList_no += 1;
                     
                    // 1行分追記
                    foreach( $rows as $mcode ){
                        $str = $str.",".$mcode['value'];    // 数量
                    }

                    // 配列に変換
                    $export_arr = explode(",",$str);
                    
                    // 内容行を1行ごとにCSVデータへ
                    $file->fputcsv($export_arr);
                }
            }
 
            // ダウンロード用
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers 
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=月次帳票".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
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

            $ledgerSheetMonth = new ledgerSheetMonth();
            $abbreviatedNameList = $ledgerSheetMonth->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト
            $laborCosts = new AggregateLaborCosts();

            $searchArray = array(
                'ledger_sheet_form_id'  => '9',
            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetMonthList = $ledgerSheetMonth->getFormListData($searchArray);

            // ヘッダ情報、Ｍコード情報取り出し 
            foreach($ledgerSheetMonthList as $data){
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
            
            // 検索組織
            $searchArray = array(
                'organizationID' => parent::escStr( $_POST['organizationName'] ),
            );

            // 日付リストを取得
            $startDate = parent::escStr( $_POST['start_date'] );
            $endDate = parent::escStr( $_POST['end_date'] );
            $startDateM = parent::escStr( $_POST['start_dateM'] );
            $endDateM = parent::escStr( $_POST['end_dateM'] );

            $weekday = array( '日', '月', '火', '水', '木', '金', '土' );

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
            
            // startDate,endDateに強制的に日付を付与
            $date1=strtotime($endDateM.'/01');
            $date2=strtotime($startDateM.'/01');
            $month1=date("Y",$date1)*12+date("m",$date1);
            $month2=date("Y",$date2)*12+date("m",$date2);

            $diff = $month1 - $month2;

            $dateList = array();
            $dispDateList = array();
            
            for($i = 0; $i <= $diff; $i++) {
                $dPeriod = array('ddays' => date('Y年m月', strtotime($startDateM.'/01' . '+' . $i . 'month')));
                array_push($dispDateList, $dPeriod);

                $period = array('days' => date('Y/m/d', strtotime($startDateM.'/01' . '+' . $i . 'month')));
                array_push($dateList, $period);
            }

            // 合計ヘッダを追加
//            $dPeriod = array('ddays' => '合計');
//            array_push($dispDateList, $dPeriod);

            // Ｍコードリスト構築
            $mcodeList = array();
            
            // 合計用の変数を動的に作成
            $sumValue[] = 0;
            for ($hh = 0; $hh < count($logic); $hh++){
                $sumValue[$hh] = 0;
                $hh+= 2;
            }
            
            // 日付範囲分実行
            foreach($dateList as $dateListOne){
                
                $dayList = array();
                
                for ($hc = 0; $hc < count($logic); $hc++){

                    // 条件に合わせて日付を構築
                    if($logic[$hc] == 1){
                        // 前年だったら日付を操作
                        $targetTime = strtotime($dateListOne["days"]);
                        $searchDay = date('Y/m/d', strtotime('-1 year', $targetTime));
                    }else{
                        // 当年だったらそのまま設定
                        $searchDay = $dateListOne["days"];
                    }
                    
                    $value = 0;
                    // Ｍコード情報を取得
                    $searchMArray = array(
                        'organization_id'   => $searchArray['organizationID'],             // 検索組織
                        'target_date'       => $searchDay,      // 検索日付開始
                        'mcode'             => $logic[$hc + 1], // 対象Ｍコード
                    );

                    // Ｍコード一覧データ取得
                    $mCodeDataList = $ledgerSheetMonth->getMcodeData($searchMArray);

                    // ロジック情報を分解
                    $mCodeLogic = explode(",", $mCodeDataList[0]["logic"]);
                    
                    // Ｍコードのタイプに合わせて取得先を変更
                    if($mCodeLogic[0] == '#'){
                        // 取得先が報告書の場合
                        
                        $searchrdArray = array(
                            'organization_id'          => $searchArray['organizationID'],  // 検索組織
                            'target_date'              => $searchDay,                      // 検索日付開始
                            'report_form_id'           => $mCodeLogic[1],                  // 報告書設定ID
                            'report_form_detail_id'    => $mCodeLogic[2],                  // 報告書詳細設定ID
                        );

                        // Ｍコード一覧データ取得
                        $mCodeDataDayList = $ledgerSheetMonth->getReportDataDay($searchrdArray);

                        if(count($mCodeDataDayList) == 0){
                            $value = 0;
                        }else{
                            foreach($mCodeDataDayList as $datam){
                                $value += $datam["data"];
                            }
                        }
                        
                    }else if($mCodeLogic[0] == '*'){
                        // 計算式の場合

                        // 符号以外を取り出す
                        $calStr = preg_split("/[\+\-\*\/()]/",$mCodeLogic[1]);

                        // 置換用リスト
                        $msList = array();
                        
                        // 数値の数だけ実施
                        for ($cs = 0; $cs < count($calStr); $cs++){
                            
                            $mEntity = array('mcode' => $calStr[$cs]);
                            
                            $searchrdArray = array(
                                'mapping_code'    => $calStr[$cs], // Mコード
                            );

                            // Mコードかどうかチェック
                            $checkMcode = $ledgerSheetMonth->checkMcode($searchrdArray);

                            if(!empty($checkMcode)){
                                if($calStr[$cs] == "172001" || $calStr[$cs] == "172002" || $calStr[$cs] == "172999"){
                                    // Mコード種別が人件費だったら
                                    $orgIDList = array();

                                    if($searchArray['organizationID'] != ""){
                                        array_push($orgIDList, $searchArray['organizationID']);
                                    }

                                    // 条件に合わせて日付を構築
                                    if($logic[$hc] == 1){
                                        // 前年だったら日付を操作
                                        $sDate = date('Y/m/d', strtotime('-1 year', strtotime($dateListOne["days"])));
                                        $eDate = date('Y/m/d', strtotime('+1 month', $sDate));
                                    }else{
                                        // 当年だったらそのまま設定
                                        $sDate = strtotime($dateListOne["days"]);
                                        $eDate = date('Y/m/d', strtotime('+1 month', $sDate));
                                    }

                                    // 人件費データ取得
                                    $entity = $this->getLabor($laborCosts, $logic[$hc + 1],$orgIDList,$dateListOne["days"],$eDate);
                                    $mEntity = $mEntity + array('val' => $entity);
                                }else{
                                    // データテーブルがどこかを判定するためMコードを再取得
                                    $searchMArraySub = array(
                                        'organization_id'   => $searchArray['organizationID'],             // 検索組織
                                        'target_date'       => $searchDay,      // 検索日付開始
                                        'mcode'             => $calStr[$cs],    // 対象Ｍコード
                                    );

                                    // Ｍコード一覧データ取得
                                    $mCodeDataListSub = $ledgerSheetMonth->getMcodeData($searchMArraySub);

                                    // ロジック情報を分解
                                    $mCodeLogicSub = explode(",", $mCodeDataListSub[0]["logic"]);

                                    if($mCodeLogicSub[0] == '#'){
                                        // 取得先が報告書の場合

                                        $searchMArraySub = array(
                                            'organization_id'          => $searchArray['organizationID'],  // 検索組織
                                            'target_date'              => $searchDay,                      // 検索日付開始
                                            'report_form_id'           => $mCodeLogicSub[1],                  // 報告書設定ID
                                            'report_form_detail_id'    => $mCodeLogicSub[2],                  // 報告書詳細設定ID
                                        );

                                        // Ｍコード一覧データ取得
                                        $mCodeDataDayListSub = $ledgerSheetMonth->getReportDataDay($searchMArraySub);

                                        if(count($mCodeDataDayListSub) == 0){
                                            $mEntity = $mEntity + array('val' => 0);
                                        }else{
                                            foreach($mCodeDataDayListSub as $datam){
                                                if($datam["data"] != "" && $datam["data"] != null){
                                                    $mEntity = $mEntity + array('val' => $datam["data"]);
                                                }else{
                                                    $mEntity = $mEntity + array('val' => 0);
                                                }
                                            }
                                        }
                                    }else{

                                        // 条件に合わせて日付を構築
                                        if($mCodeDataListSub[0]["logic_type"] == 2){
                                            // 前年だったら日付を操作
                                            $targetTime = strtotime($dateListOne["days"]);
                                            $searchDay2 = date('Y/m/d', strtotime('-1 year', $targetTime));

                                            // 報告書経由じゃない場合はPOSデータ、データを取得
                                            $searchMlArray = array(
                                                'organization_id'   => $searchArray['organizationID'], // 検索組織
                                                'target_date'       => $searchDay2,      // 検索日付開始
                                                'mcode'             => $mCodeDataListSub[0]["logic"], // 対象Ｍコード
                                            );

                                        }else{

                                            // 報告書経由じゃない場合はPOSデータ、データを取得
                                            $searchMlArray = array(
                                                'organization_id'   => $searchArray['organizationID'], // 検索組織
                                                'target_date'       => $searchDay,      // 検索日付開始
                                                'mcode'             => $calStr[$cs], // 対象Ｍコード
                                            );

                                        }

                                       $mCodeDataDayList = $ledgerSheetMonth->getMcodeListData($searchMlArray);

                                        if(count($mCodeDataDayList) == 0){
                                            $mEntity = $mEntity + array('val' => 0);
                                        }else{
                                            foreach($mCodeDataDayList as $datam){
                                                $mEntity = $mEntity + array('val' => $datam["data"]);
                                            }
                                        }
                                    }
                                }
                            }else{
                                // Mコードじゃない場合は、数値として文字列に変換
                                $mEntity = $mEntity + array('val' => $calStr[$cs]);
                            }
                            // 変換リストに搭載
                             array_push($msList, $mEntity);
                        }
                        
                        // データにしたものをキーを使って置換する
                        for ($chl = 0; $chl < count($msList); $chl++){
                            $mCodeLogic[1] = str_replace($msList[$chl]["mcode"],$msList[$chl]["val"],$mCodeLogic[1]);
                        }
                        
                        //完成した文字列を計算式として実行 
                        $value = round(eval("return ".$mCodeLogic[1].";"),2);
                        
                    }else if($mCodeLogic[0] == 'k'){
                        // Mコード種別が人件費だったら
                        $orgIDList = array();
                                    
                        if($searchArray['organizationID'] != ""){
                            array_push($orgIDList, $searchArray['organizationID']);
                        }

                            // 条件に合わせて日付を構築
                            if($logic[$hc] == 1){
                                // 前年だったら日付を操作
                                $sDate = date('Y/m/d', strtotime('-1 year', strtotime($dateListOne["days"])));
                                $eDate = date('Y/m/d', strtotime('+1 month', $sDate));
                            }else{
                                // 当年だったらそのまま設定
                                $sDate = strtotime($dateListOne["days"]);
                                $eDate = date('Y/m/d', strtotime('+1 month', $sDate));
                            }
                        
                        // 人件費データ取得
                        $value = $this->getLabor($laborCosts, $logic[$hc + 1],$orgIDList,$dateListOne["days"],$eDate);
                        
                    }else{
                        // 取得先がPOSデータの場合
                        $searchMlArray = array(
                            'organization_id'   => $searchArray['organizationID'],             // 検索組織
                            'target_date'       => $searchDay,      // 検索日付開始
                            'mcode'             => $logic[$hc + 1], // 対象Ｍコード
                        );

                        // Ｍコード一覧データ取得
                        $mCodeDataDayList = $ledgerSheetMonth->getMcodeListData($searchMlArray);

                        if(count($mCodeDataDayList) == 0){
                            $value = 0;
                        }else{
                            foreach($mCodeDataDayList as $datam){
                                $value += $datam["data"];
                            }
                        }
                    }

                    // 3要素ずつでローテーション
                    $mcode = array(
                        'type'   => $logic[$hc],      // １つ目 種別コード(0:当年、1:前年)
                        'mcode'  => $logic[$hc + 1],  // ２つ目 Ｍコード
                        'width'  => $logic[$hc + 2],  // ３つ目 幅（width）の値
                        'value'  => $value,           // データ
                    );
                    // 1セル分搭載
                    array_push($dayList, $mcode);
                    // 合計用に加算
//                    $sumValue[$hc] += $value;
                    // ローテーションように要素を進める
                    $hc+= 2;
                    
                }
                //１行分搭載
                array_push($mcodeList, $dayList);
            }
            
            // 合計行を搭載
            
            $sumList = array();
            
            for ($hs = 0; $hs < count($logic); $hs++){

                // 3要素ずつでローテーション
                    $sumMcode = array(
                        'type'   => 0,                // １つ目 種別コード(0:当年、1:前年)
                        'mcode'  => $logic[$hs + 1],  // ２つ目 Ｍコード
                        'width'  => $logic[$hs + 2],  // ３つ目 幅（width）の値
                        'value'  => $sumValue[$hs],   // データ
                    );
                    // 1セル分搭載
                    array_push($sumList, $sumMcode);
                    $hs+= 2;
            }

//            array_push($mcodeList, $sumList);
            
            $mcodeList_no = 0;
            
            $Log->trace("END   initialDisplay");

            if($mode == "show"){
                require_once './View/LedgerSheetMonthPanel.html';
            }else{
                require_once './View/LedgerSheetMonthEXPanel.html';
            }
        }

        /**
         * 人件費取得処理
         * @note     Excel出力
         * @return   無
         */
        private function getLabor($laborCosts, $logic, $list, $s, $e)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getLabor");

            // 人件費データ取得
            $ret = $laborCosts->getOrgLaborAggregateData( $list, $s, $e, 1, 3, false );

            if(count($ret) == 0){
                $value = 0;
            }else{
                foreach($ret as $datam){
                    if($logic == "172001"){
                        // 正社員
                        if($datam["code"] == "1100"){
                            $value = $value + $datam["rough_estimate"];
                        }
                    }else if($logic == "172002"){
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
            $Log->trace("END   getLabor");
            return $value;
        }
        
    }?>
