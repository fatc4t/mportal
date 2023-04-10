<?php

    //**************************************************
    //
    // 機能     :JICFS/IFDBデータインポート
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************
    require_once("DBAccess_Function.php");

    $currentdate = date('ymdHis');
    $format = "jsfic_data";
    // ログファイル名
    $logname = $format . "_" . $currentdate;
    //$log_path = "D:/htdocs/mportal/backup/" . $logname . '.log';  
    $log_path = "/var/www/mportal/importfile/log/" . $logname . $filename.'.log';	
    function createSQL($aryData)
    {
        global $strDATA_RCV_DATE;
        global $intDATA_RCV_SEQ_JAN_BASIC;
        global $intDATA_RCV_SEQ_ITF;
        global $intDATA_RCV_SEQ_JAN_DETAIL;
        global $intDATA_RCV_SEQ_MAKER_STD;
        global $intDATA_RCV_SEQ_MAKER_SHORT;
        $strSQL = '';
        // データ種別
                $maker_cd  = trim($aryData[8]);
                $maker_cd1 =  substr($maker_cd,0, 6); 
                $strSQL = "INSERT INTO m_jicfs_jan_basic ("
                    . "  jan_cd"
                    . " ,maker_cd"
                    . " ,prod_kn"
                    . " ,prod_nm"
                    . " ,prod_capa_kn"
                    . " ,prod_capa_nm"
                    . " ,prod_kn_rk"
                    . " ,saleprice"
                    . " )"
                    . " VALUES ("
                    . "  '" . trim($aryData[3]) . "'"                                   // jan_cd
                    . " ,'" . $maker_cd1 . "'"                                   // maker_cd
                    . " ,'" . char_chg("to_db", trim($aryData[12])) . "'"               // prod_kn
                    . " ,'" . char_chg("to_db", trim($aryData[13])) . "'"               // prod_nm
                    . " ,'" . char_chg("to_db", trim($aryData[14])) . "'"               // prod_capa_kn
                    . " ,'" . char_chg("to_db", trim($aryData[15])) . "'"               // prod_capa_nm
                    . " ,'" . char_chg("to_db", trim($aryData[18])) . "'"               // prod_kn_rk
                    . " , " . trim($aryData[38])                                        // saleprice
                    . " )";       
        // 結果を返却
        return $strSQL;
    }
    //**************************************************
    //
    // 機能     :JICFS/IFDBデータインポートメイン
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************    
    $strDATA_RCV_DATE = date('Ymd');
    $intDATA_RCV_SEQ_JAN_BASIC      = 0;
    $intDATA_RCV_SEQ_ITF            = 0;
    $intDATA_RCV_SEQ_JAN_DETAIL     = 0;
    $intDATA_RCV_SEQ_MAKER_STD      = 0;
    $intDATA_RCV_SEQ_MAKER_SHORT    = 0;
    // JICFS/IFDB データファイル格納場所
   // $path ='C:/xampp/htdocs/mportal/importfile/';
    $path ='/var/www/mportal/importfile/';
    // 当時の提供データ
    $fname = 'RJDP';      
    if (!file_exists($path)){
          echo char_chg("to_dsp", "JICFS FILES CANNOT FOUND !!!")."\n";
     
        exit();
    }      
    // サブディレクトリを再帰的に呼び出す
    $comp_code = $comp_code_in;
    if ($handle = opendir($path))
    {
        // ファイルリスト用変数
        $queue = array();
            //　フォルダ内のチェック
            while (false !== ($file = readdir($handle)))
            {
                    // サブディレクトリのチェック
                    if (is_dir($path.$file) && $file != '.' && $file !='..') {
                        // ディレクトリの場合は下層から実施する
                            $comp_code = $file;
                            serchSubDir($comp_code, $file, $path);
                    } else if ($file != '.' && $file !='..') {
                        // ファイルリストに入れる
                            $queue[] = $file;
                    }
            }
            // 読み込みを実行
            setQueue($comp_code, $queue, $path);
    }
        // ファイル数、読み込みを実施
    function setQueue($comp_code, $queue, $path)
    {
            foreach ($queue as $file)
            {
                    read_file($comp_code, $file, $path.$file);

            }
    }   	     
    //EDITEND 20210108 kanderu      
    function read_file($comp_code, $filename, $filefull){
    // ファイルをロック
    $fp = fopen($filefull, 'r');
    // ファイル行数カウント
    for ($count = 0; fgets($fp); $count ++);
    rewind($fp);
  // $file = fopen('D:/importfile/', 'r'); 
   while ($data = fgetcsv($fp, 1024, "\t")){
        // ファイルヘッダレコード
        if ($data[0] === 'J'){
            // データ送信元
            if ($data[4] !== '4912345000001'){
                // エラーメッセージ(データ送信元違い)
                echo char_chg("to_dsp", "[データ異常]データ送信元が違います。")."\n";
                exit();
            }
            // レコード件数
            if (intval($data[6]) !== $count){
                // エラーメッセージ(件数違い)
                echo char_chg("to_dsp", "[1データ異常]レコード件数がファイルヘッダ情報と違います。")."\n";
                exit();
            }
        }
        // データレコード
        else if ($data[0] === 'E'){
           
            
        if ($data[1]==='A1'){
              $sql = '';
              $sql = "DELETE FROM m_jicfs_jan_basic WHERE jan_cd = '".$data[3]."'"; 
              //SQL実行
              sqlExec($sql);

            $sql = createSQL($data);
            // SQL実行
            //print_r($sql);
            
            sqlExec($sql);
            if ($sql === false){
                // エラーメッセージ
               // echo char_chg("to_dsp", "[データ異常]データ種別が未定義のレコードです。")."\n";
                echo char_chg("to_dsp", "error1")."\n";
                echo join("\t", $data)."\n";
                continue;
            }

              }
        }

    }
    fclose($fp);
    // ファイルのロックを解除
    flock($fp, LOCK_UN);
    }
  // 処理完了ログ出力
  error_log("完了" . "\n", 3, $log_path);
    // 処理中にエラーが発生した場合はログファイル出力し、終了
   // error_log("処理中にエラーが発生しました" . "\n" . "エラー内容：". "\n".$x . $ex, 3, $log_path);
    
 
?>
