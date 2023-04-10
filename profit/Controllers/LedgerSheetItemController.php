<?php
    /**
     * @file      帳票 - 商品別コントローラ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 商品別の閲覧を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // Login処理モデル
    require './Model/LedgerSheetItem.php';
    // Excel読み込み用ファイル
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel.php';
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel/IOFactory.php';
    
    /**
     * 表示項目設定コントローラクラス
     * @note   表示項目設定の新規登録/修正/削除を行う
     */
    class LedgerSheetItemController extends BaseController
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
         * 表示項目設定一覧画面初期表示および検索
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
            
            $ledgerSheetItem = new ledgerSheetItem();

            $searchArray = array(
                'ledger_sheet_form_id'  => '7',
            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetItemList = $ledgerSheetItem->getFormListData($searchArray);

            // ファイル名
            $file_path = mb_convert_encoding("./Temp/".$ledgerSheetItemList[0]["ledger_sheet_form_name"].".csv", 'SJIS-win', 'UTF-8');
 
            $dateList = array();

            // 商品データ取り出し 
//            foreach($ledgerSheetItemList as $data){
//                for ($hc = 1; $hc < 24; $hc++){
//                    if(!$data["c_time".$hc] == "" or !isset($data["c_time".$hc])){
//                        array_push($dateList, $data["c_time".$hc]);
//                    }
//                }
//            }
            
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
            
            // 商品マスタが登録されていたら
            if(count($dateList)> 0){

                // 商品コードごとにデータ取得
                foreach($dateList as $dateListOne){
                    $itemData = array();

                    $searchMArray = array(
                        'organization_id'   => $searchArray['organizationID'],             // 検索組織
                        'target_date_start' => $startDate,      // 検索日付開始
                        'target_date_end'   => $endDate,        // 検索日付終了
                        'plu_code'          => $dateListOne,    // 検索商品コード
                    );

                    // 商品データ取得
                    $itemData = $ledgerSheetItem->getItemData($searchMArray);

                    // データの有無確認
                    if(count($itemData) > 0){
                        //対象データがある場合
                        foreach($itemData as $item){
                            $listArray = array(
                                'plu_code'           => $item["plu_code"],               // 商品コード
                                'plu_name'           => $item["plu_name"],               // 商品名
                                'price'              => $item["price"],                  // 単価
                                'cost'               => $item["cost"],                   // 原価
                                'large_class_code'   => $item["large_class_code"],       // 大分類コード
                                'large_class_name'   => $item["large_class_name"],       // 大分類名
                                'medium_class_code'  => $item["medium_class_code"],      // 中分類コード
                                'medium_class_name'  => $item["medium_class_name"],      // 中分類名
                                'small_class_code'   => $item["small_class_code"],       // 小分類コード
                                'small_class_name'   => $item["small_class_name"],       // 小分類名
                                'count'              => $item["count"],                  // 数量
                                'sales'              => $item["sales"],                  // 金額
                            );
                        }
                    }else{
                        // 対象データがない場合、空データを載せる
                        $listArray = array(
                            'plu_code'           => $dateListOne,   // 商品コード
                            'plu_name'           => "",             // 商品名
                            'price'              => 0,              // 単価
                            'cost'               => 0,              // 原価
                            'large_class_code'   => "",             // 大分類コード
                            'large_class_name'   => "",             // 大分類名
                            'medium_class_code'  => "",             // 中分類コード
                            'medium_class_name'  => "",             // 中分類名
                            'small_class_code'   => "",             // 小分類コード
                            'small_class_name'   => "",             // 小分類名
                            'count'              => 0,              // 数量
                            'sales'              => 0,              // 金額
                        );
                    }
                    
                    //１行分搭載
                    array_push($timeDataList, $listArray);
                }

            }else{
                // 商品マスタがない場合は、取込みデータで作成

                $searchMArray = array(
                    'organization_id'   => $searchArray['organizationID'],          // 検索組織
                    'target_date_start' => $startDate,   // 検索日付開始
                    'target_date_end'   => $endDate,     // 検索日付終了
                    'plu_code'          => "",           // 検索商品コード
                );

                // 商品データ取得
                $timeDataList = $ledgerSheetItem->getItemData($searchMArray);
                
            }

            // 最後に商品コードなしデータの追加
            $searchZArray = array(
                'organization_id'   => $searchArray['organizationID'],         // 検索組織
                'target_date_start' => $startDate,  // 検索日付開始
                'target_date_end'   => $endDate,    // 検索日付終了
                'plu_name_flg'      => true,        // 商品コードなしを指定
                );

            // 商品コード未設定データ取得
            $zeroData = $ledgerSheetItem->getItemData($searchZArray);
            if($zeroData){
                array_push($timeDataList, $zeroData);
            }

            // 合計計算用、ランク計算用
            $sumDataList = array();

            $sumPrice = 0;
            $sumCost = 0;
            $sumCostPrice = 0;
            $sumCostPriceRate = 0;
            $sumGrossMargin = 0;
            $sumGrossMarginRat = 0;
            $sumCount = 0;
            $sumSales = 0;
            $sumGrossProfitt = 0;
 
            foreach($timeDataList as $date){
                // 合計計算、ランク計算
                $sumPrice += $date["price"];
                $sumCost  += $date["cost"];
                $sumCostPrice  += ($date["cost"] *  $date["count"]);
                $sumCostPriceRate  += (($date["cost"] * 100 ) / $date["count"]);
                $sumGrossMargin  += (($date["price"] - $date["cost"] ) * $date["count"]);
                $sumGrossMarginRat  += (($date["price"] - $date["cost"] ) * $date["count"] * 100 / $date["sales"]);
                $sumCount += $date["count"];
                $sumSales += $date["sales"];
                $sumGrossProfitt += ($date['sales'] - ($date['cost'] * $date['count']));
            }

            $sumArray = array(
                'plu_code'           => "",         // 商品コード
                'plu_name'           => "合計",     // 商品名
                'price'              => $sumPrice,  // 単価
                'cost'               => $sumCost,   // 原価
                'large_class_code'   => "",         // 大分類コード
                'large_class_name'   => "",         // 大分類名
                'medium_class_code'  => "",         // 中分類コード
                'medium_class_name'  => "",         // 中分類名
                'small_class_code'   => "",         // 小分類コード
                'small_class_name'   => "",         // 小分類名
                'count'              => $sumCount,  // 数量
                'sales'              => $sumSales,  // 金額
            );

            // 合計行を搭載
            array_push($sumDataList, $sumArray);

            // CSVに出力するヘッダ行
            $export_csv_title = array( "メニューコード", "メニュー名", "数量","単価","売上","原価","原価額","原価率","粗利","粗利額","粗利率","構成比数量","構成比金額","構成比粗利","ランク" );
 
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

                    // 原価率計算
                    $cost = "";
                    if($val["sales"] > 0){
                        $cost = round(($val["cost"] * 100) / $val["price"],2);
                    }else{
                        $cost = "0";
                    }
                    
                    // 粗利率計算
                    $arariritu = "";
                    if($val["sales"] > 0){
                        $arariritu = round(($val["price"] - $val["cost"]) * $val["count"] * 100 / $val["sales"],2);
                    }else{
                        $arariritu = "0";
                    }
                    
                    // 構成比 - 数量の計算
                    $suuryo = "";
                    if($sumCount > 0){
                        $suuryo = round($val["count"] * 100 / $sumCount,2);
                    }else{
                        $suuryo = "0";
                    }
                    
                    // 構成比 - 金額の計算
                    $kingaku = "";
                    if($sumSales > 0){
                        $kingaku = round($val["sales"] * 100 / $sumSales,2);
                    }else{
                        $kingaku = "0";
                    }
                    
                    // 構成比 - 粗利の計算
                    $arari = "";
                    if($sumGrossProfitt > 0){
                        $arari = round(($val["price"] - $val["cost"]) * $val["count"] * 100 / $sumGrossProfitt,2);
                    }else{
                        $arari = "0";
                    }
                    
                    // ランク設定
                    $rank = "";
                    if(round(($val["price"] - $val["cost"]) * $val["count"] * 100 / $sumGrossProfitt,2) > 3){
                        $rank = "A";
                    }else if(round(($val["price"] - $val["cost"]) * $val["count"] * 100 / $sumGrossProfitt,2) > 1){
                        $rank = "B";
                    }else{
                        $rank = "C";
                    }
                    
                    $str = $val["plu_code"].","                                             // メニューコード
                            .mb_convert_encoding($val["plu_name"], 'SJIS-win', 'UTF-8')     // メニュー名
                            .",".$val["count"]                                              // 数量
                            .",".$val["price"]                                              // 単価
                            .",".$val["sales"]                                              // 金額
                            .",".$val["cost"]                                               // 原価
                            .",".($val["cost"] * $val["count"])                             // 原価額
                            .",".$cost                                                      // 原価率
                            .",".($val["price"] - $val["cost"])                             // 粗利
                            .",".($val["sales"] - ($val["cost"] * $val["count"]))           // 粗利額
                            .",".$arariritu                                                 // 粗利率
                            .",".$suuryo                                                    // 構成比 - 数量
                            .",".$kingaku                                                   // 構成比 - 金額
                            .",".$arari                                                     // 構成比 - 粗利
                            .",".$rank;                                                     // ランク

                    $export_arr = explode(",",$str);
                    // 内容行を配列ごとCSVデータ化
                    $file->fputcsv($export_arr);
                }
                
                // 合計行をCSVデータ化
                foreach( $sumDataList as $val ){

                    // 原価率計算
                    $cost = "";
                    if($val["sales"] > 0){
                        $cost = round($sumCostPriceRate,2);
                    }else{
                        $cost = "0";
                    }
                    
                    // 粗利率計算
                    $arariritu = "";
                    if($val["sales"] > 0){
                        $arariritu = round($sumGrossMargin * 100 / $val["sales"],2);
                    }else{
                        $arariritu = "0";
                    }
                    
                    // 構成比 - 数量の計算
                    $suuryo = "";
                    if($sumCount > 0){
                        $suuryo = round($val["count"] * 100 / $sumCount,2);
                    }else{
                        $suuryo = "0";
                    }
                    
                    // 構成比 - 金額の計算
                    $kingaku = "";
                    if($sumSales > 0){
                        $kingaku = round($val["sales"] * 100 / $sumSales,2);
                    }else{
                        $kingaku = "0";
                    }
                    
                    // 構成比 - 粗利の計算
                    $arari = "";
                    if($sumGrossProfitt > 0){
                        $arari = round($sumGrossMargin * 100 / $sumGrossProfitt,2);
                    }else{
                        $arari = "0";
                    }
                    
                    // ランク設定
                    $rank = "";
                    if(round(($val["price"] - $val["cost"]) * $val["count"] * 100 / $sumGrossProfitt,2) > 3){
                        $rank = "A";
                    }else if(round(($val["price"] - $val["cost"]) * $val["count"] * 100 / $sumGrossProfitt,2) > 1){
                        $rank = "B";
                    }else{
                        $rank = "C";
                    }
                    
                    $str = $val["plu_code"].","                                             // メニューコード
                            .mb_convert_encoding($val["plu_name"], 'SJIS-win', 'UTF-8')     // メニュー名
                            .",".$val["count"]                                              // 数量
                            .",".$val["price"]                                              // 単価
                            .",".$val["sales"]                                              // 金額
                            .",".$val["cost"]                                               // 原価
                            .",".$sumCostPrice                                              // 原価額
                            .",".$cost                                                      // 原価率
                            .",".($val["price"] - $val["cost"])                             // 粗利
                            .",".sumGrossMargin                                             // 粗利額
                            .",".$arariritu                                                 // 粗利率
                            .",".$suuryo                                                    // 構成比 - 数量
                            .",".$kingaku                                                   // 構成比 - 金額
                            .",".$arari                                                     // 構成比 - 粗利
                            .",".$rank;                                                     // ランク

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
            header("Content-Disposition: attachment; filename=商品別帳票".mb_convert_encoding(basename($file_path), 'SJIS-win', 'UTF-8').";" );
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
         * PDF出力
         * @note     PDF出力
         * @return   無
         */
        public function pdfoutputAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START pdfoutputAction");

            $ledgerSheetItem = new ledgerSheetItem();
            $abbreviatedNameList = $ledgerSheetItem->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

            $searchArray = array(
                'ledger_sheet_form_id'  => '7',
            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetItemList = $ledgerSheetItem->getFormListData($searchArray);

            $dateList = array();

            // 商品データ取り出し 
//            foreach($ledgerSheetItemList as $data){
//                for ($hc = 1; $hc < 24; $hc++){
//                    if(!$data["c_time".$hc] == "" or !isset($data["c_time".$hc])){
//                        array_push($dateList, $data["c_time".$hc]);
//                    }
//                }
//            }
            
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
            
            // 商品マスタが登録されていたら
            if(count($dateList)> 0){

                // 商品コードごとにデータ取得
                foreach($dateList as $dateListOne){
                    $itemData = array();

                    $searchMArray = array(
                        'organization_id'   => $searchArray['organizationID'],             // 検索組織
                        'target_date_start' => $startDate,      // 検索日付開始
                        'target_date_end'   => $endDate,        // 検索日付終了
                        'plu_code'          => $dateListOne,    // 検索商品コード
                    );

                    // 商品データ取得
                    $itemData = $ledgerSheetItem->getItemData($searchMArray);

                    // データの有無確認
                    if(count($itemData) > 0){
                        //対象データがある場合
                        foreach($itemData as $item){
                            $listArray = array(
                                'plu_code'           => $item["plu_code"],               // 商品コード
                                'plu_name'           => $item["plu_name"],               // 商品名
                                'price'              => $item["price"],                  // 単価
                                'cost'               => $item["cost"],                   // 原価
                                'large_class_code'   => $item["large_class_code"],       // 大分類コード
                                'large_class_name'   => $item["large_class_name"],       // 大分類名
                                'medium_class_code'  => $item["medium_class_code"],      // 中分類コード
                                'medium_class_name'  => $item["medium_class_name"],      // 中分類名
                                'small_class_code'   => $item["small_class_code"],       // 小分類コード
                                'small_class_name'   => $item["small_class_name"],       // 小分類名
                                'count'              => $item["count"],                  // 数量
                                'sales'              => $item["sales"],                  // 金額
                            );
                        }
                    }else{
                        // 対象データがない場合、空データを載せる
                        $listArray = array(
                            'plu_code'           => $dateListOne,   // 商品コード
                            'plu_name'           => "",             // 商品名
                            'price'              => 0,              // 単価
                            'cost'               => 0,              // 原価
                            'large_class_code'   => "",             // 大分類コード
                            'large_class_name'   => "",             // 大分類名
                            'medium_class_code'  => "",             // 中分類コード
                            'medium_class_name'  => "",             // 中分類名
                            'small_class_code'   => "",             // 小分類コード
                            'small_class_name'   => "",             // 小分類名
                            'count'              => 0,              // 数量
                            'sales'              => 0,              // 金額
                        );
                    }
                    
                    //１行分搭載
                    array_push($timeDataList, $listArray);
                }

            }else{
                // 商品マスタがない場合は、取込みデータで作成

                $searchMArray = array(
                    'organization_id'   => $searchArray['organizationID'],          // 検索組織
                    'target_date_start' => $startDate,   // 検索日付開始
                    'target_date_end'   => $endDate,     // 検索日付終了
                    'plu_code'          => "",           // 検索商品コード
                );

                // 商品データ取得
                $timeDataList = $ledgerSheetItem->getItemData($searchMArray);
                
            }

            // 最後に商品コードなしデータの追加
            $searchZArray = array(
                'organization_id'   => $searchArray['organizationID'],         // 検索組織
                'target_date_start' => $startDate,  // 検索日付開始
                'target_date_end'   => $endDate,    // 検索日付終了
                'plu_name_flg'      => true,        // 商品コードなしを指定
                );

            // 商品コード未設定データ取得
            $zeroData = $ledgerSheetItem->getItemData($searchZArray);
            if($zeroData){
                array_push($timeDataList, $zeroData);
            }

            // 合計計算用、ランク計算用
            $sumDataList = array();

            $sumPrice = 0;
            $sumCost = 0;
            $sumCostPrice = 0;
            $sumCostPriceRate = 0;
            $sumGrossMargin = 0;
            $sumGrossMarginRat = 0;
            $sumCount = 0;
            $sumSales = 0;
            $sumGrossProfitt = 0;
 
            foreach($timeDataList as $date){
                // 合計計算、ランク計算
                $sumPrice += $date["price"];
                $sumCost  += $date["cost"];
                $sumCostPrice  += ($date["cost"] *  $date["count"]);
                $sumCostPriceRate  += (($date["cost"] * 100 ) / $date["count"]);
                $sumGrossMargin  += (($date["price"] - $date["cost"] ) * $date["count"]);
                $sumGrossMarginRat  += (($date["price"] - $date["cost"] ) * $date["count"] * 100 / $date["sales"]);
                $sumCount += $date["count"];
                $sumSales += $date["sales"];
                $sumGrossProfitt += ($date['sales'] - ($date['cost'] * $date['count']));
            }

            $sumArray = array(
                'plu_code'           => "",         // 商品コード
                'plu_name'           => "",         // 商品名
                'price'              => $sumPrice,  // 単価
                'cost'               => $sumCost,   // 原価
                'large_class_code'   => "",         // 大分類コード
                'large_class_name'   => "",         // 大分類名
                'medium_class_code'  => "",         // 中分類コード
                'medium_class_name'  => "",         // 中分類名
                'small_class_code'   => "",         // 小分類コード
                'small_class_name'   => "",         // 小分類名
                'count'              => $sumCount,  // 数量
                'sales'              => $sumSales,  // 金額
            );
            
            // 合計行を搭載
            array_push($sumDataList, $sumArray);

            // PDF用ライブラリ
            include(SystemParameters::$FW_COMMON_PATH . 'mpdf60/mpdf.php');

            $html = file_get_contents("./View/LedgerSheetItemPDFPanel.html");
            $mpdf=new mPDF('ja+aCJK', 'A4-L');
            $mpdf->WriteHTML("$html");
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

            $ledgerSheetItem = new ledgerSheetItem();
            $abbreviatedNameList = $ledgerSheetItem->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト

            $searchArray = array(
                'ledger_sheet_form_id'  => '7',
            );

            // 帳票フォーム一覧データ取得
            $ledgerSheetItemList = $ledgerSheetItem->getFormListData($searchArray);

            $dateList = array();

            // 商品データ取り出し 
//            foreach($ledgerSheetItemList as $data){
//                for ($hc = 1; $hc < 24; $hc++){
//                    if(!$data["c_time".$hc] == "" or !isset($data["c_time".$hc])){
//                        array_push($dateList, $data["c_time".$hc]);
//                    }
//                }
//            }
            
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
            
            // 商品マスタが登録されていたら
            if(count($dateList)> 0){

                // 商品コードごとにデータ取得
                foreach($dateList as $dateListOne){
                    $itemData = array();

                    $searchMArray = array(
                        'organization_id'   => $searchArray['organizationID'],             // 検索組織
                        'target_date_start' => $startDate,      // 検索日付開始
                        'target_date_end'   => $endDate,        // 検索日付終了
                        'plu_code'          => $dateListOne,    // 検索商品コード
                    );

                    // 商品データ取得
                    $itemData = $ledgerSheetItem->getItemData($searchMArray);

                    // データの有無確認
                    if(count($itemData) > 0){
                        //対象データがある場合
                        foreach($itemData as $item){
                            $listArray = array(
                                'plu_code'           => $item["plu_code"],               // 商品コード
                                'plu_name'           => $item["plu_name"],               // 商品名
                                'price'              => $item["price"],                  // 単価
                                'cost'               => $item["cost"],                   // 原価
                                'large_class_code'   => $item["large_class_code"],       // 大分類コード
                                'large_class_name'   => $item["large_class_name"],       // 大分類名
                                'medium_class_code'  => $item["medium_class_code"],      // 中分類コード
                                'medium_class_name'  => $item["medium_class_name"],      // 中分類名
                                'small_class_code'   => $item["small_class_code"],       // 小分類コード
                                'small_class_name'   => $item["small_class_name"],       // 小分類名
                                'count'              => $item["count"],                  // 数量
                                'sales'              => $item["sales"],                  // 金額
                            );
                        }
                    }else{
                        // 対象データがない場合、空データを載せる
                        $listArray = array(
                            'plu_code'           => $dateListOne,   // 商品コード
                            'plu_name'           => "",             // 商品名
                            'price'              => 0,              // 単価
                            'cost'               => 0,              // 原価
                            'large_class_code'   => "",             // 大分類コード
                            'large_class_name'   => "",             // 大分類名
                            'medium_class_code'  => "",             // 中分類コード
                            'medium_class_name'  => "",             // 中分類名
                            'small_class_code'   => "",             // 小分類コード
                            'small_class_name'   => "",             // 小分類名
                            'count'              => 0,              // 数量
                            'sales'              => 0,              // 金額
                        );
                    }
                    
                    //１行分搭載
                    array_push($timeDataList, $listArray);
                }

            }else{
                // 商品マスタがない場合は、取込みデータで作成

                $searchMArray = array(
                    'organization_id'   => $searchArray['organizationID'],          // 検索組織
                    'target_date_start' => $startDate,   // 検索日付開始
                    'target_date_end'   => $endDate,     // 検索日付終了
                    'plu_code'          => "",           // 検索商品コード
                );

                // 商品データ取得
                $timeDataList = $ledgerSheetItem->getItemData($searchMArray);
                
            }

            // 最後に商品コードなしデータの追加
            $searchZArray = array(
                'organization_id'   => $searchArray['organizationID'],         // 検索組織
                'target_date_start' => $startDate,  // 検索日付開始
                'target_date_end'   => $endDate,    // 検索日付終了
                'plu_name_flg'      => true,        // 商品コードなしを指定
                );

            // 商品コード未設定データ取得
            $zeroData = $ledgerSheetItem->getItemData($searchZArray);
            if($zeroData){
                array_push($timeDataList, $zeroData);
            }

            // 合計計算用、ランク計算用
            $sumDataList = array();

            $sumPrice = 0;
            $sumCost = 0;
            $sumCostPrice = 0;
            $sumCostPriceRate = 0;
            $sumGrossMargin = 0;
            $sumGrossMarginRat = 0;
            $sumCount = 0;
            $sumSales = 0;
            $sumGrossProfitt = 0;
 
            foreach($timeDataList as $date){
                // 合計計算、ランク計算
                $sumPrice += $date["price"];
                $sumCost  += $date["cost"];
                $sumCostPrice  += ($date["cost"] *  $date["count"]);
                $sumCostPriceRate  += (($date["cost"] * 100 ) / $date["count"]);
                $sumGrossMargin  += (($date["price"] - $date["cost"] ) * $date["count"]);
                $sumGrossMarginRat  += (($date["price"] - $date["cost"] ) * $date["count"] * 100 / $date["sales"]);
                $sumCount += $date["count"];
                $sumSales += $date["sales"];
                $sumGrossProfitt += ($date['sales'] - ($date['cost'] * $date['count']));
            }
            
            $sumArray = array(
                'plu_code'           => "",         // 商品コード
                'plu_name'           => "",         // 商品名
                'price'              => $sumPrice,  // 単価
                'cost'               => $sumCost,   // 原価
                'large_class_code'   => "",         // 大分類コード
                'large_class_name'   => "",         // 大分類名
                'medium_class_code'  => "",         // 中分類コード
                'medium_class_name'  => "",         // 中分類名
                'small_class_code'   => "",         // 小分類コード
                'small_class_name'   => "",         // 小分類名
                'count'              => $sumCount,  // 数量
                'sales'              => $sumSales,  // 金額
            );
                        
            // 合計行を搭載
            array_push($sumDataList, $sumArray);
            
            $Log->trace("END   initialDisplay");
            
            if($mode == "show"){
                require_once './View/LedgerSheetItemPanel.html';
            }else{
                require_once './View/LedgerSheetItemEXPanel.html';
            }
        }

    }
?>
