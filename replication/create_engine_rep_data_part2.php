<?php

//******************************************************************************
//
// 機能     :データレプリケーション(パート2)
//
// 機能説明 :
//
// 備考     :
//
// 作成日　：2021/12/08   　　　　　作成者　：バッタライ
// 
// 修正日　：　　　　　　　　　　　　修正者　：
//
//******************************************************************************

try {
    require_once("DBAccess_Function.php");

    //file_put_contents($csv, mb_convert_encoding(file_get_contents($csv), 'UTF-8', 'SJIS'));
    // iniファイル設定参照
    $ini = file_get_contents('/mportal/script/rep_schema.ini');
    // 
    $schemas = explode("\r\n", $ini);
    //
    $currentdate = date('YmdHis');
    //
    $toda = date("Ymd");
    $today =date('Ymd', strtotime($toda . '-1 day'));
    // 企業一覧ループ開始
    foreach ($schemas as $schema) {
        // 
        $shop_dir = '/mportal/' . $schema;
        // 店舗一覧を取得
        $shop_cd_list = glob($shop_dir . '/*');
        // 店舗一覧ループ開始
        foreach ($shop_cd_list as $shop_cd_dir) {
            // 店舗コードを取得
            $result_dir = glob($shop_cd_dir . '/archive/backup/*');
            // CSVファイル名でループし、MST0101を探す
            foreach ($result_dir as $path) {
                // csvファイル名を取得
                $file_name = substr($path, -27, 27);
                // 本日上がって来たファイルじゃない場合はスキップする
               // if(substr($file_name, 8, 8) !== $today){
               //     continue;
               // }
                // TRN1601
                if (substr($file_name, 0, 7) === 'trn1601') {

                    if (($fp = fopen($path, "r")) === false) {
                        //　ファイルが存在しない場合はエラ
                        return true;
                    }
                    // CSVの中身がダブルクオーテーションで囲われていない場合に一文字目が化けるのを回避
                    setlocale(LC_ALL, 'ja_JP');
                    $i = 0;
                    while (($line = fgetcsv($fp)) !== FALSE) {
                    //    mb_convert_variables('UTF-8', 'sjis-win', $line);
                        if ($i == 0) {
                            // タイトル行は外す
                            $header = $line;
                            $i++;
                            continue;
                        }

                        $array_line = str_replace('"', '', $line[0]);

                        $line1 = explode("#", $array_line);

                        $sql_update = "";
                        $sql_update .= " update " . $schema . ".trn1601 set ";
                        $sql_update .= " upduser_cd            = 'MPORTAL' ";
                        $sql_update .= " ,upddatetime          = now() ";
                        // 集約処理区分が0の場合
                        if ($line1[19] === '0') {
                            $sql_update .= " ,consproc_kbn         = '1' ";
                            $sql_update .= " ,consproc_date        = " . "'" . $currentdate . "'";
                        }
                        // VANデータ作成区分
                        if ($line1[21] === '0') {
                            $sql_update .= " ,van_data_kbn         = '1' ";
                            $sql_update .= " ,van_data_date        = " . "'" . $currentdate . "'";
                        }
                        $sql_update .= " where  ";
                        $sql_update .= " order_kbn           = '1'";
                        $sql_update .= " and organization_id = " . $line1[7];
                        $sql_update .= " and hideseq         = " . $line1[8];
                        // SQLを実行
             //           sqlExec($sql_update);
                        $i++;
                    }
                }
                // TRN1602
                if (substr($file_name, 0, 7) === 'trn1602') {

                    if (($fp = fopen($path, "r")) === false) {
                        //　ファイルが存在しない場合はエラ
                        return true;
                    }
                    // CSVの中身がダブルクオーテーションで囲われていない場合に一文字目が化けるのを回避
                    setlocale(LC_ALL, 'ja_JP');
                    $i = 0;
                    while (($line = fgetcsv($fp)) !== FALSE) {
                    //    mb_convert_variables('UTF-8', 'sjis-win', $line);
                        if ($i == 0) {
                            // タイトル行は外す
                            $header = $line;
                            $i++;
                            continue;
                        }

                        $array_line = str_replace('"', '', $line[0]);

                        $line1 = explode("#", $array_line);

                        $sql_update = "";
                        $sql_update .= " update " . $schema . ".trn1602 set ";
                        $sql_update .= " upduser_cd            = 'MPORTAL' ";
                        $sql_update .= " ,upddatetime          = now() ";
                        // 集約処理区分が0の場合
                        if ($line1[16] === '0') {
                            $sql_update .= " ,supp_state_kbn         = '2' ";
                        }
                        $sql_update .= " where  ";
                        $sql_update .= " organization_id = " . $line1[7];
                        $sql_update .= " and hideseq     = " . $line1[8];
                        $sql_update .= " and line_no     = " . $line1[9];
                        // SQLを実行
           //             sqlExec($sql_update);
                        $i++;
                    }
                }
                //MST0101
                if (substr($file_name, 0, 7) === 'mst0101') {
                    if (($fp = fopen($path, "r")) === false) {
                        //　ファイルが存在しない場合はエラ
                        return true;
                    }
                    // CSVの中身がダブルクオーテーションで囲われていない場合に一文字目が化けるのを回避
                    setlocale(LC_ALL, 'ja_JP');
                    // 
                    $k = 0;
                    while (($line = fgetcsv($fp)) !== FALSE) {
                      //  mb_convert_variables('UTF-8', 'sjis-win', $line);
                        if ($k == 0) {
                            // タイトル行は外す
                            $header = $line;
                            $k++;
                            continue;
                        }

                        $array_line3 = str_replace('"', '', $line[0]);

                        $line3 = explode("#", $array_line3);

                        $sql_update = "";
                        $sql_update .= "update " . $schema . ".mst0101 set ";
                        $sql_update .= "  upduser_cd  = 'RESTORE' ";
                        $sql_update .= " ,upddatetime = now()     ";
                        if ($line3[8] !== '' &&  $line3[8] !== $line3[7]) {
                            $sql_update .= " ,cust_nm = " . "'" . $line3[8] . "'";
                        }
                        if ($line3[9] !== '') {
                            $sql_update .= " ,cust_kn = " . "'" . $line3[9] . "'";
                        }
                        if ($line3[10] !== '') {
                            $sql_update .= " ,zip = " . "'" . $line3[10] . "'";
                        }
                        if ($line3[11] !== '') {
                            $sql_update .= " ,addr1 = " . "'" . $line3[11] . "'";
                        }
                        if ($line3[12] !== '') {
                            $sql_update .= " ,addr2 = " . "'" . $line3[12] . "'";
                        }
                        if ($line3[13] !== '') {
                            $sql_update .= " ,addr3 = " . "'" . $line3[13] . "'";
                        }
                        if ($line3[14] !== '') {
                            $sql_update .= " ,tel = " . "'" . $line3[14] . "'";
                        }
                        if ($line3[15] !== '') {
                            $sql_update .= " ,tel4 = " . "'" . $line3[15] . "'";
                        }
                        if ($line3[16] !== '') {
                            $sql_update .= " ,fax = " . "'" . $line3[16] . "'";
                        }
                        if ($line3[17] !== '') {
                            $sql_update .= " ,hphone = " . "'" . $line3[17] . "'";
                        }
                        if ($line3[18] !== '') {
                            $sql_update .= " ,email = " . "'" . $line3[18] . "'";
                        }
                        if ($line3[20] !== '') {
                            $sql_update .= " ,birth = " . "'" . $line3[20] . "'";
                        }
                       /* if ($line3[26] !== '') {
                            $sql_update .= " ,area_cd01 = " . "'" . $line3[26] . "'";
                        }
                        if ($line3[28] !== '') {
                            $sql_update .= " ,staff_cd = " . "'" . $line3[28] . "'";
                        }
                        if ($line3[30] !== '') {
                            $sql_update .= " ,dm_type = " . "'" . $line3[30] . "'";
                        }
                        if ($line3[34] !== '') {
                            $sql_update .= " ,person_kbn = " . "'" . $line3[34] . "'";
                        }
                        if ($line3[38] !== '') {
                            $sql_update .= " ,cust_sumday = " . "'" . $line3[38] . "'";
                        }
                        if ($line3[40] !== '') {
                            $sql_update .= " ,point_magni = " . "'" . $line3[40] . "'";
                        }*/
                        $sql_update .= " where ";
                        $sql_update .= " cust_cd         = " . "'" . $line3[7] . "'";
                        //SQLを実行
                        sqlExec($sql_update);
                        $k++;
                    }
                }
            }
        }
    }
error_log('完了',3,'/mportal/mifuku/2/archive/backup/mifuku.log');
} catch (Exception $ex) {
    error_log($ex,3,'/mportal/mifuku/2/archive/backup/mifuku.log');
    return true;
}
?>
