<?php
    /**
     * @file      帳票 - 時間帯別コントローラ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 時間帯別の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetTimeZone.php';
    // Excel読み込み用ファイル
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel.php';
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel/IOFactory.php';

    /**
     * 時間帯別帳票コントローラクラス
     * @note   時間帯別帳票の表示を行う
     */
    class LedgerSheetTimeZoneController extends BaseController
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
            
            $ledgerSheetTimeZone = new LedgerSheetTimeZone();
            $abbreviatedNameList = $ledgerSheetTimeZone->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

            $searchArray = array(
                'ledger_sheet_form_id'  => '5',
            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetDayList = $ledgerSheetTimeZone->getFormListData($searchArray);

            // ファイル名
            $file_path = mb_convert_encoding("./Temp/".$ledgerSheetDayList[0]["ledger_sheet_form_name"].".csv", 'SJIS-win', 'UTF-8');
 
            $dateList = array();

            // 検索組織
            $searchArray = array(
                'organizationID' => parent::escStr( $_POST['organizationName'] ),
            );

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

            // 表示データリスト構築
            $timeDataList = array();

            // 時間帯マスタ取得
            $ledgerSheetTimeZoneList = $ledgerSheetTimeZone->getTimeZoneListData($searchArray);

            foreach($ledgerSheetTimeZoneList as $timeZone){
                
                // 時間が逆転している場合は、日を跨いでいるので2回に分ける
                if($timeZone['time_zone_start'] > $timeZone['time_zone_end'] ){
                    $timeData1 = array();
                    $timeData2 = array();
                    
                    $searchMArray1 = array(
                        'organization_id'   => $searchArray['organizationID'],  // 検索組織
                        'target_date_start' => $startDate,                      // 検索日付開始
                        'target_date_end'   => $endDate,                        // 検索日付終了
                        'start_time'        => $timeZone['time_zone_start'],    // 集計開始時間
                        'end_time'          => '24',                             // 集計終了時間
                    );

                    $searchMArray2 = array(
                        'organization_id'   => $searchArray['organizationID'],  // 検索組織
                        'target_date_start' => $startDate,                      // 検索日付開始
                        'target_date_end'   => $endDate,                        // 検索日付終了
                        'start_time'        => '00',                            // 集計開始時間
                        'end_time'          => $timeZone['time_zone_end'],      // 集計終了時間
                    );

                    // 時間帯別一覧データ取得
                    $timeData1 = $ledgerSheetTimeZone->getTimeData($searchMArray1);
                    $timeData2 = $ledgerSheetTimeZone->getTimeData($searchMArray2);

                    $listArray = array(
                        'key'           => $timeZone['time_zone_name'],                                   // 時間帯キー
                        'count'         => $timeData1[0]["count"] + $timeData2[0]["count"],               // 数量
                        'sales'         => $timeData1[0]["sales"] + $timeData2[0]["sales"],               // 売上(税抜)
                        'sales_tax'     => $timeData1[0]["sales_tax"] + $timeData2[0]["sales_tax"],       // 売上(税込)
                        'guest_count'   => $timeData1[0]["guest_count"] + $timeData2[0]["guest_count"],   // 客数;
                        'group_count'   => $timeData1[0]["group_count"] + $timeData2[0]["group_count"],   // 組数
                        'coupon'        => $timeData1[0]["coupon"] + $timeData2[0]["coupon"],             // クーポン売上(税抜)
                        'coupon_tax'    => $timeData1[0]["coupon_tax"] + $timeData2[0]["coupon_tax"],     // クーポン売上(税込)
                        'coupon_count'  => $timeData1[0]["coupon_count"] + $timeData2[0]["coupon_count"], // クーポン枚数
                    );
                }else{
                    $timeData = array();
                    $searchMArray = array(
                        'organization_id'   => $searchArray['organizationID'],  // 検索組織
                        'target_date_start' => $startDate,                      // 検索日付開始
                        'target_date_end'   => $endDate,                        // 検索日付終了
                        'start_time'        => $timeZone['time_zone_start'],    // 集計開始時間
                        'end_time'          => $timeZone['time_zone_end'],      // 集計終了時間
                    );

                    // 時間帯別一覧データ取得
                    $timeData = $ledgerSheetTimeZone->getTimeData($searchMArray);

                        foreach($timeData as $time){
                            if($time["key"] != null){
                                $listArray = array(
                                    'key'           => $timeZone['time_zone_name'],     // 時間帯キー
                                    'count'         => $time["count"],                  // 数量
                                    'sales'         => $time["sales"],                  // 売上(税抜)
                                    'sales_tax'     => $time["sales_tax"],              // 売上(税込)
                                    'guest_count'   => $time["guest_count"],            // 客数
                                    'group_count'   => $time["group_count"],            // 組数
                                    'coupon'        => $time["coupon"],                 // クーポン売上(税抜)
                                    'coupon_tax'    => $time["coupon_tax"],             // クーポン売上(税込)
                                    'coupon_count'  => $time["coupon_count"],           // クーポン枚数
                                );
                            }else{
                                $listArray = array(
                                    'key'           => $timeZone['time_zone_name'], // 時間キー
                                    'count'         => 0,                           // 数量
                                    'sales'         => 0,                           // 売上(税抜)
                                    'sales_tax'     => 0,                           // 売上(税込)
                                    'guest_count'   => 0,                           // 客数
                                    'group_count'   => 0,                           // 組数
                                    'coupon'        => 0,                           // クーポン売上(税抜)
                                    'coupon_tax'    => 0,                           // クーポン売上(税込)
                                    'coupon_count'  => 0,                           // クーポン枚数
                                );
                            }
                        }
                }
                
                //１行分搭載
                array_push($timeDataList, $listArray);
            }

            // 合計計算用
            $sumDataList = array();

            $sumCount       = 0;
            $sumSales       = 0;
            $sumSalesTax    = 0;
            $sumGuestCount  = 0;
            $sumGroupCount  = 0;
            $sumCoupon      = 0;
            $sumCouponTax   = 0;
            $sumCouponCount = 0;
 
            foreach($timeDataList as $date){
                // 合計計算
                $sumCount       += $date["count"];
                $sumSales       += $date["sales"];
                $sumSalesTax    += $date["sales_tax"];
                $sumGuestCount  += $date["guest_count"];
                $sumGroupCount  += $date["group_count"];
                $sumCoupon      += $date["coupon"];
                $sumCouponTax   += $date["coupon_tax"];
                $sumCouponCount += $date["coupon_count"];
            }
            
            $sumArray = array(
                'key'           => "合計",              // 時間キー
                'count'         => $sumCount,       // 数量
                'sales'         => $sumSales,       // 売上(税抜)
                'sales_tax'     => $sumSalesTax,    // 売上(税込)
                'guest_count'   => $sumGuestCount,  // 客数
                'group_count'   => $sumGroupCount,  // 組数
                'coupon'        => $sumCoupon,      // クーポン売上(税抜)
                'coupon_tax'    => $sumCouponTax,   // クーポン売上(税込)
                'coupon_count'  => $sumCouponCount, // クーポン枚数
            );
                        
            // 合計行を搭載
            array_push($sumDataList, $sumArray);

            // CSVに出力するヘッダ行
            $export_csv_title = array( "時間帯名","売上(税抜)", "売上(税込)", "客数","組数","販売点数","クーポン(税込)","クーポン(内税)","クーポン枚数" );
 
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
                foreach( $timeDataList as $val ){

                    $str =  mb_convert_encoding($val["key"], 'SJIS-win', 'UTF-8')  // 時間帯名
                            .",".$val["sales"]          // 売上(税抜)
                            .",".$val["sales_tax"]      // 売上(税込)
                            .",".$val["guest_count"]    // 客数
                            .",".$val["group_count"]    // 組数
                            .",".$val["count"]          // 数量
                            .",".$val["coupon"]         // クーポン売上(税抜)
                            .",".$val["coupon_tax"]     // クーポン売上(税込)
                            .",".$val["coupon_count"];  // クーポン枚数

                    $export_arr = explode(",",$str);
                    // 内容行を配列ごとCSVデータ化
                    $file->fputcsv($export_arr);
                }
            }
 
            // 合計行をCSVデータ化
            foreach( $sumDataList as $val ){

                $str =  mb_convert_encoding($val["key"], 'SJIS-win', 'UTF-8')  // 時間帯名
                        .",".$val["sales"]          // 売上(税抜)
                        .",".$val["sales_tax"]      // 売上(税込)
                        .",".$val["guest_count"]    // 客数
                        .",".$val["group_count"]    // 組数
                        .",".$val["count"]          // 数量
                        .",".$val["coupon"]         // クーポン売上(税抜)
                        .",".$val["coupon_tax"]     // クーポン売上(税込)
                        .",".$val["coupon_count"];  // クーポン枚数

                $export_arr = explode(",",$str);
                // 内容行を配列ごとCSVデータ化
                $file->fputcsv($export_arr);
            }
            
            // ダウンロード用
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers 
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=時間帯別帳票".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
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

            $ledgerSheetTimeZone = new LedgerSheetTimeZone();
            $abbreviatedNameList = $ledgerSheetTimeZone->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

            $searchArray = array(
                'ledger_sheet_form_id'  => '5',
            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetDayList = $ledgerSheetTimeZone->getFormListData($searchArray);

            // 検索組織
            $searchArray = array(
                'organizationID' => parent::escStr( $_POST['organizationName'] ),
            );

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

            // 表示データリスト構築
            $timeDataList = array();
            
            // 時間帯マスタ取得
            $ledgerSheetTimeZoneList = $ledgerSheetTimeZone->getTimeZoneListData($searchArray);

            foreach($ledgerSheetTimeZoneList as $timeZone){
                
                // 時間が逆転している場合は、日を跨いでいるので2回に分ける
                if($timeZone['time_zone_start'] > $timeZone['time_zone_end'] ){
                    $timeData1 = array();
                    $timeData2 = array();
                    
                    $searchMArray1 = array(
                        'organization_id'   => $searchArray['organizationID'],  // 検索組織
                        'target_date_start' => $startDate,                      // 検索日付開始
                        'target_date_end'   => $endDate,                        // 検索日付終了
                        'start_time'        => $timeZone['time_zone_start'],    // 集計開始時間
                        'end_time'          => '24',                             // 集計終了時間
                    );

                    $searchMArray2 = array(
                        'organization_id'   => $searchArray['organizationID'],  // 検索組織
                        'target_date_start' => $startDate,                      // 検索日付開始
                        'target_date_end'   => $endDate,                        // 検索日付終了
                        'start_time'        => '00',                            // 集計開始時間
                        'end_time'          => $timeZone['time_zone_end'],      // 集計終了時間
                    );

                    // 時間帯別一覧データ取得
                    $timeData1 = $ledgerSheetTimeZone->getTimeData($searchMArray1);
                    $timeData2 = $ledgerSheetTimeZone->getTimeData($searchMArray2);

                    $listArray = array(
                        'key'           => $timeZone['time_zone_name'],                                   // 時間帯キー
                        'count'         => $timeData1[0]["count"] + $timeData2[0]["count"],               // 数量
                        'sales'         => $timeData1[0]["sales"] + $timeData2[0]["sales"],               // 売上(税抜)
                        'sales_tax'     => $timeData1[0]["sales_tax"] + $timeData2[0]["sales_tax"],       // 売上(税込)
                        'guest_count'   => $timeData1[0]["guest_count"] + $timeData2[0]["guest_count"],   // 客数;
                        'group_count'   => $timeData1[0]["group_count"] + $timeData2[0]["group_count"],   // 組数
                        'coupon'        => $timeData1[0]["coupon"] + $timeData2[0]["coupon"],             // クーポン売上(税抜)
                        'coupon_tax'    => $timeData1[0]["coupon_tax"] + $timeData2[0]["coupon_tax"],     // クーポン売上(税込)
                        'coupon_count'  => $timeData1[0]["coupon_count"] + $timeData2[0]["coupon_count"], // クーポン枚数
                    );
                }else{
                    $timeData = array();
                    $searchMArray = array(
                        'organization_id'   => $searchArray['organizationID'],  // 検索組織
                        'target_date_start' => $startDate,                      // 検索日付開始
                        'target_date_end'   => $endDate,                        // 検索日付終了
                        'start_time'        => $timeZone['time_zone_start'],    // 集計開始時間
                        'end_time'          => $timeZone['time_zone_end'],      // 集計終了時間
                    );

                    // 時間帯別一覧データ取得
                    $timeData = $ledgerSheetTimeZone->getTimeData($searchMArray);

                        foreach($timeData as $time){
                            if($time["key"] != null){
                                $listArray = array(
                                    'key'           => $timeZone['time_zone_name'],     // 時間帯キー
                                    'count'         => $time["count"],                  // 数量
                                    'sales'         => $time["sales"],                  // 売上(税抜)
                                    'sales_tax'     => $time["sales_tax"],              // 売上(税込)
                                    'guest_count'   => $time["guest_count"],            // 客数
                                    'group_count'   => $time["group_count"],            // 組数
                                    'coupon'        => $time["coupon"],                 // クーポン売上(税抜)
                                    'coupon_tax'    => $time["coupon_tax"],             // クーポン売上(税込)
                                    'coupon_count'  => $time["coupon_count"],           // クーポン枚数
                                );
                            }else{
                                $listArray = array(
                                    'key'           => $timeZone['time_zone_name'], // 時間キー
                                    'count'         => 0,                           // 数量
                                    'sales'         => 0,                           // 売上(税抜)
                                    'sales_tax'     => 0,                           // 売上(税込)
                                    'guest_count'   => 0,                           // 客数
                                    'group_count'   => 0,                           // 組数
                                    'coupon'        => 0,                           // クーポン売上(税抜)
                                    'coupon_tax'    => 0,                           // クーポン売上(税込)
                                    'coupon_count'  => 0,                           // クーポン枚数
                                );
                            }
                        }
                }
                
                //１行分搭載
                array_push($timeDataList, $listArray);
            }

            // 合計計算用
            $sumDataList = array();

            $sumCount       = 0;
            $sumSales       = 0;
            $sumSalesTax    = 0;
            $sumGuestCount  = 0;
            $sumGroupCount  = 0;
            $sumCoupon      = 0;
            $sumCouponTax   = 0;
            $sumCouponCount = 0;
 
            foreach($timeDataList as $date){
                // 合計計算
                $sumCount       += $date["count"];
                $sumSales       += $date["sales"];
                $sumSalesTax    += $date["sales_tax"];
                $sumGuestCount  += $date["guest_count"];
                $sumGroupCount  += $date["group_count"];
                $sumCoupon      += $date["coupon"];
                $sumCouponTax   += $date["coupon_tax"];
                $sumCouponCount += $date["coupon_count"];
            }
            
            $sumArray = array(
                'key'           => "",              // 時間キー
                'count'         => $sumCount,       // 数量
                'sales'         => $sumSales,       // 売上(税抜)
                'sales_tax'     => $sumSalesTax,    // 売上(税込)
                'guest_count'   => $sumGuestCount,  // 客数
                'group_count'   => $sumGroupCount,  // 組数
                'coupon'        => $sumCoupon,      // クーポン売上(税抜)
                'coupon_tax'    => $sumCouponTax,   // クーポン売上(税込)
                'coupon_count'  => $sumCouponCount, // クーポン枚数
            );
                        
            // 合計行を搭載
            array_push($sumDataList, $sumArray);

            $Log->trace("END   initialDisplay");
            if($mode == "show"){
                require_once './View/LedgerSheetTimeZonePanel.html';
            }else{
                require_once './View/LedgerSheetTimeZoneEXPanel.html';
            }
        }

    }
?>
