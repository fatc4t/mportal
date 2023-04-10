<?php
    //**************************************************
    //
    // 機能     :資生堂商品マスタデータインプット
    //
    // 機能説明 :
    //
    // 備考     :PUBLIC　スキーマに商品データをインプットする
    //
    // 作成日　：2021/03/24　　　　　　 作成者　：バッタライ
    // 
    // 修正日　：　　　　　　　　　　　　修正者　：
    // 　　　　　　　　
    //**************************************************

try{
    require_once("create_engine_shiseido_Function.php");
    //日本の時間を設定
    date_default_timezone_set('Asia/Tokyo');
    // 現在の年月日時分秒を取得(スラシュあり)
    $currentdatetime = date('Y/m/d h:i:s');
    // 現在の年月日時分秒を取得(スラシュ無し)
    $currentdate = date('ymdHis');
    // 発注データ集計フォーマット
    $format = "SYOHINMT";
    // ログファイル名
    $logname = $format . "_" . $currentdate;
    // ログファイルパス
    $log_path = "/var/www/shiseido/log/" . $logname . '.log';
    // 空白
    $space = str_pad('', 5);

    // 処理開始ログ出力
    error_log($currentdatetime . $space .
            $format . $space .
            "開始" . "\n", 3, $log_path);
    //資生堂商品マスタファイル名を指定する
    $filename = 'KICS.SD.SYOHINMT';
    //固定長ファイル取得する
    $rows_import = getFileDat($filename);
    //ファイルが存在しない場合ログ出力
    if(count($rows_import) <= 0){
        // ログ出力
        error_log($currentdatetime . $space .
                $format . $space .
                "商品マスタのテキストファイルが存在しません。" . "\n". "完了". "\n", 3, $log_path);
        exit();
    }
    //過去のマスタ全て削除する
    $sql  = "";
    $sql .= "delete from public.m_shiseido_prod ";
    sqlExec($sql);
    // ログ出力
    error_log($currentdatetime . $space .
            $format . $space .
            "商品マスタの削除完了". "\n", 3, $log_path);
    //スタートレコードとエンドレコードを削除し、データレコードのみ取得
    $mst_data = mb_strcut($rows_import,256,-256);
    $count = strlen($mst_data);
    $width = 128;

    $ssd_maker      = '';
    $ssd_prod_cd    = '';
    $prod_kn        = '';
    $sugg_price     =  0;
    $ssd_order_unit =  0;
    $prod_cd        = '';
    $ssd_class_cd   = '';
    $tax_kbn        = '';    
    //ループ開始
    for ($i=0; $i<=$count; $i+=$width) {
        //メーカー
        $ssd_maker      = mb_substr($mst_data,$i+1,3);
        //商品コード
        $ssd_prod_cd    = mb_substr($mst_data,$i+4,5);
        //商品名
        $prod_kn        = str_replace("'",'' ,mb_substr($mst_data,$i+9,19));   
        //小売価格
        $sugg_price     = mb_substr($mst_data,$i+28,6);
        //発注単位
        $ssd_order_unit = mb_substr($mst_data,$i+49,3);
        //JANコード
        $prod_cd        = mb_substr($mst_data,$i+52,13);
        //分類コード
        $ssd_class_cd   = mb_substr($mst_data,$i+65,6);
        //税区分
        $tax_kbn        = mb_substr($mst_data,$i+72,1);
        //資生堂商品マスタを登録
        $sql  = "";
        $sql .= "INSERT INTO PUBLIC.m_shiseido_prod ( ";
        $sql .= "INSUSER_CD      ";
        $sql .= ",INSDATETIME     ";
        $sql .= ",UPDUSER_CD      ";
        $sql .= ",UPDDATETIME     ";
        $sql .= ",DISABLED        ";
        $sql .= ",PROD_CD         ";
        $sql .= ",SSD_PROD_CD     ";
        $sql .= ",SSD_PROD_KN     ";
        $sql .= ",SSD_MAKER       ";
        $sql .= ",SSD_CLASS_CD    ";
        $sql .= ",SSD_ORDER_UNIT  ";
        $sql .= ",SUGG_PRICE      ";
        $sql .= ",TAX_KBN         ";
        $sql .= ")                ";
        $sql .= "VALUES(          ";
        $sql .= "'mportal'        ";
        $sql .= ",now()           ";
        $sql .= ",'mportal'       ";
        $sql .= ",now()           ";
        $sql .= ",0               ";
        $sql .= ",'".$prod_cd."'";
        $sql .= ",'".$ssd_prod_cd."'";
        $sql .= ",'".$prod_kn."'";
        $sql .= ",'".$ssd_maker."'";
        $sql .= ",'".$ssd_class_cd."'";
        $sql .= ",'".$ssd_order_unit."'";
        $sql .= ",'".$sugg_price."'";
        $sql .= ",'".$tax_kbn."'";
        $sql .= ") ";
        //SQLを実行
        sqlExec($sql);
   }
   
} catch (Exception $ex) {
    // 処理中にエラーが発生した場合はログファイル出力し、終了
    error_log($currentdatetime . $space .
            $format . $space .
            "処理中にエラーが発生しました" . "\n" . "エラー内容：". "\n" . $ex, 3, $log_path);
}    
// 処理完了ログ出力
error_log($currentdatetime . $space .
        $format . $space .
        "完了" . "\n", 3, $log_path);
skip:
@ob_flush();
@flush();
?>













