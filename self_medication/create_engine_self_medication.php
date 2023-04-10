<?php
    //**************************************************************************
    //
    // 機能     :セルフメディケション機能
    //
    // 機能説明 :
    //
    // 備考     :
    //
    // 作成日　：2021/12/17   　　　　　作成者　：バッタライ
    //
    // 修正日　：　　　　　　　　　　　　修正者　：
    //
    //**************************************************************************

try {
    require_once("DBAccess_Function.php"); 
    
    date_default_timezone_set('Asia/Tokyo');
    error_log('セルフメディケーション　更新開始　　　'.date('Y/m/d h:m:s'). "\n", 3, '/var/www/log/selfmedi'.date('Ym').'.log');
    
    $get_schema  = get_schema();
    
    $del_prod_cd = get_del_prod_cd();
    $add_prod_cd = get_add_prod_cd(); 

    // スキーマでループまわす
    foreach($get_schema as $array_schema){
        $schema = $array_schema['schemaname'];
        if($schema == 'shiguma' && 'fuji' && 'victory'){
            continue;
        }
        
            // セルフメディケション削除スクリプト
            $sql_delete  = "";
            $sql_delete .= " update  ".$schema.".mst0201 set ";
            $sql_delete .= " upduser_cd      = 'SELFMEDI' ";
            $sql_delete .= " ,upddatetime    =  now() ";
            $sql_delete .= " ,switch_otc_kbn = '0'  ";
            $sql_delete .= " where  ";
            $sql_delete .= " switch_otc_kbn = '1' ";
            $sql_delete .= " and prod_cd in (".$del_prod_cd.")"; 
            sqlExec($sql_delete);
            
            //　セルフメディケション更新
            $sql_update = " ";
            $sql_update .= " update ".$schema.".mst0201 set ";
            $sql_update .= " upduser_cd     = 'SELFMEDI' ";
            $sql_update .= " ,upddatetime    =  now() ";
            $sql_update .= " ,switch_otc_kbn = '1' ";
            $sql_update .= " where ";
            $sql_update .= " switch_otc_kbn = '0' ";
            $sql_update .= " and prod_cd in (".$add_prod_cd.")";
            
            sqlExec($sql_update);
            error_log(str_pad($schema, 20).'　完了'. "\n", 3, '/var/www/log/selfmedi'.date('Ym').'.log');
    }
    error_log('セルフメディケーション　更新完了　　　'.date('Y/m/d h:m:s').  "\n", 3, '/var/www/log/selfmedi'.date('Ym').'.log');
} catch (Exception $ex) {
    error_log($ex. "\n", 3, '/var/www/log/selfmedi'.date('Ym').'.log');
}
?>
