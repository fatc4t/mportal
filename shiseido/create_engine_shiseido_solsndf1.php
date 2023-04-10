
<?php
    //**************************************************
    //
    // 機能     :資生堂ID付き顧客属性（更新情報）　I/Fファイル 
    //
    // 機能説明 :
    //
    // 備考     :AS400ダウンサイジング
    //
    // 作成日　：2021/03/24　　　　　　 作成者　：バッタライ
    // 
    // 修正日　：　　　　　　　　　　　　修正者　：
    // 　　　　　　　　
    //**************************************************

    require_once("create_engine_shiseido_Function.php");    
    //資生堂商品マスタファイル名を指定する
    $filename = 'SD.SOLSNDF1';
    
    $rows_import = getFileDat($filename);
    //スタートレコードとエンドレコードを削除し、データレコードのみ取得
    $mst_data = mb_strcut($rows_import,320,-321);
    //128行毎に改行を入れる
    $input_data = mb_wordwrap( $mst_data, 320, "n" );
    //行を配列に変換
    $input_arr = split("[n|r]", $input_data);
    //ループ開始
    foreach($input_arr as $data){
        //メーカー
        $ssd_maker      = mb_substr($data,0,3);
        //商品コード
        $ssd_prod_cd    = mb_substr($data,3,5);
        //商品名
        $prod_kn        = mb_substr($data,8,19);
        //小売価格
        $sugg_price     = mb_substr($data,27,6);
        //発注単位
        $ssd_order_unit = mb_substr($data,33,3);
        //JANコード
        $prod_cd        = mb_substr($data,36,13);
        //分類コード
        $ssd_class_cd   = mb_substr($data,49,6);
        //税区分
        $tax_kbn        = mb_substr($data,55,1);
        
        $param = array(
            ':INSUSER_CD'       => 'MPORTAL',
            ':INSDATETIME'      => "now()",
            ':UPDUSER_CD'       => 'MPORTAL',
            ':UPDDATETIME'      => "now()",
            ':DISABLED'         => '0',
            ':PROD_CD'          => "'".$prod_cd."'",
            ':SSD_PROD_CD'      => "'".$ssd_prod_cd."'",
            ':SSD_PROD_KN'      => "'".$prod_kn."'",
            ':SSD_MAKER'        => "'".$ssd_maker."'",
            ':SSD_CLASS_CD'     => "'".$ssd_class_cd."'",
            ':SSD_ORDER_UNIT'   => "'".$ssd_order_unit."'",
            ':SUGG_PRICE'       => "'".$sugg_price."'",
            ':TAX_KBN'          => "'".$tax_kbn."'",
        );        
        //資生堂商品マスタ参照し、商品コードが存在してる場合更新
        //商品コード存在してない場合は新規作成
        $sql  = "";
        $sql .= "select ";
        $sql .= "      prod_cd ";
        $sql .= "from  PUBLIC.MST03705";
        $sql .= "where prod_cd = "."'".$prod_cd."'";
        
        $result = getList($sql);
        //
        if($result){
            //商品コード存在している場合更新
            $sql   = "";
            $sql  .= "update PUBLIC.MST03705 set";
            $sql  .= "SSD_PROD_CD    ="."'".$ssd_prod_cd."'";
            $sql  .= "SSD_PROD_KN    ="."'".$prod_kbn."'";
            $sql  .= "SSD_MAKER      ="."'".$ssd_maker."'";
            $sql  .= "SSD_CLASS_CD   ="."'".$ssd_class_cd."'";
            $sql  .= "SSD_ORDER_UNIT ="."'".$ssd_order_unit."'";
            $sql  .= "SUGG_PRICE     ="."'".$sugg_price."'";
            $sql  .= "TAX_KBN        ="."'".$tax_kbn."'";
            $sql  .= "where ";
            $sql  .= "PROD_CD =".$prod_cd;
            //SQLを実行
            sqlExec($sql);                
        }else{
            //商品コード存在してない場合は新規作成            
            $sql  = "";
            $sql .= "INSERT INTO PUBLIC.MST03705 ";
            $sql .= "INSUSER_CD      ";
            $sql .= "INSDATETIME     ";
            $sql .= "UPDUSER_CD      ";
            $sql .= "UPDDATETIME     ";
            $sql .= "DISABLED        ";
            $sql .= "PROD_CD         ";
            $sql .= "SSD_PROD_CD     ";
            $sql .= "SSD_PROD_KN     ";
            $sql .= "SSD_MAKER       ";
            $sql .= "SSD_CLASS_CD    ";
            $sql .= "SSD_ORDER_UNIT  ";
            $sql .= "SUGG_PRICE      ";
            $sql .= "TAX_KBN         ";
            $sql .= ")               ";
            $sql .= "VALUES(         ";
            $sql .= ":INSUSER_CD     ";
            $sql .= ":INSDATETIME    ";
            $sql .= ":UPDUSER_CD     ";
            $sql .= ":UPDDATETIME    ";
            $sql .= ":DISABLED       ";
            $sql .= ":PROD_CD        ";
            $sql .= ":SSD_PROD_CD    ";
            $sql .= ":SSD_PROD_KN    ";
            $sql .= ":SSD_MAKER      ";
            $sql .= ":SSD_CLASS_CD   ";
            $sql .= ":SSD_ORDER_UNIT ";
            $sql .= ":SUGG_PRICE     ";
            $sql .= ":TAX_KBN        "; 
            $sql .= ")               ";
            //SQLを実行
            sqlExec($sql,$param);           
        }       
   }
skip:
    $timelimit = microtime(true) - $time_start;
    echo $timelimit." seconds<br />\n";
    echo "-----<br />\n";
    @ob_flush();
    @flush();
?>













