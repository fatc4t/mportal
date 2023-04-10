<?php

//******************************************************************************
//
// 機能     :ジャーナルファイルを移動する
//
// 機能説明 :
//
// 備考     :
//
// 作成日　：2021/12/10   　　　　　作成者　：バッタライ
// 
// 修正日　：　　　　　　　　　　　　修正者　：
//
//******************************************************************************

try {
    require_once("DBAccess_Function.php");
    // trn9101存在するスキーマのみを取得
    $schema     = "";
    $sql_schema  = " SELECT ";
    $sql_schema .= " schemaname ";
    $sql_schema .= " FROM pg_tables  ";
    $sql_schema .= " where tablename = 'trn9101' ";
    $sql_schema .= " order by schemaname  ";
    $rows_schemas = getList($sql_schema);
    // 企業一覧ループ開始
    foreach($rows_schemas as $schemas){
        
        $schema = $schemas['schemaname'];
        
        $shop_dir = '/mportal/' . $schema;
        // 店舗一覧を取得
        $shop_cd_list = glob($shop_dir . '/*');
        // 店舗一覧ループ開始
        foreach ($shop_cd_list as $shop_cd_dir) {
            // 店舗コードを取得
            $result_dir = glob($shop_cd_dir . '/receive/*');
            // CSVファイル名でループし、MST0101を探す
            foreach ($result_dir as $path) {
                // csvファイル名を取得
                $file_name = substr($path, -27, 27);
                // TRN9101
                if (substr($file_name, 0, 7) === 'trn9101') {
                    $des_path = $shop_cd_dir.'/archive/receive/'.$file_name;
                    // archiveへ移動する
                    rename($path, $des_path);
                }
            }
        }
    }     
} catch (Exception $ex) {
    print_r($ex);
    return true;
}
?>
