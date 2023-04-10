<?php
try{
    require_once("fuji_import_fnc.php");

    //固定長ファイル取得する
    $rows_import = getFileDat($filename);

    $count = strlen($rows_import);
    $width = 80;
 
    //ループ開始
    for ($i=0; $i<=$count; $i+=$width) {

    $prod_cd1        = str_replace("'",'' ,mb_substr($rows_import,$i+1,13));
    $prod_cd = "'".$prod_cd1."'";
    $amount         = str_replace("'",'' ,mb_substr($rows_import,$i+26,3));
    $upddatetime  =  'now()';
    //sql
    $sql  = "";
    $sql .= "update fuji.trn1702 set upddatetime = $upddatetime, rea_amount =$amount where prod_cd = $prod_cd  and to_char(upddatetime, 'yyyy/mm/dd') between '2021/10/23' and '2021/11/23' and organization_id = 7 ";

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
