<?php
//try{
    require_once("DBAccess_Function.php");   
    // ログファイル名
    $currentdate = date('ymdHis');
    $logname =  $currentdate;
	$filename = 'prod_nm';
    $log_path = "D:/test/" . $logname . $filename.'.log';
    // $log_path = "D:/test/" . $logname . '.log';
   
    //schema name
	$sql  =""; 
	$sql .= "select schemaname from pg_tables";
    $sql .= " where tablename = 'mst0201'  order by schemaname ";

    // SQLの実行
    $schema1 =  getList($sql);
    
    //店舗ループ
    foreach ($schema1 as $key => $schma) {
       $schema = $schma['schemaname'];    
//update
    $sql  = "update ".$schema.".mst0201 SET  ";
    //$sql .= " upddatetime       =  'now()' ";
    $sql .= " prod_nm = prod_kn || prod_capa_kn  "; 
    //条件
    $sql .= " where upduser_cd = 'CSVCONV1' and prod_nm <> '' ";           
    sqlExec($sql);
    }
    error_log("※更新完了。". "\n".$x . $ex, 3, $log_path);
    $msg = "※完了";
    echo   $msg; 
?>
