<?php
    //**************************************************
    //
    // 機能     :POS発注データファイルDB取込
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************

    // キーファイル名の設定があるファイルを全て読み込む
    //key_type が 2のみ
    require_once("DBAccess_Function.php");

    // POS発注ファイル保存パス
    $path = "../profit/import/as400/read/";     // 仮

    // DBスキーマ名
    $schema = "as400";

    // DBテーブル名
    $table_name = $schema.".t_import_pos_order";

    // 対象パスにあるファイルを読み込んでリスト化する
    function serchDir($comp_code_in, $path)
    {
        $comp_code = $comp_code_in;
        if ($handle = opendir($path))
        {

            // ファイルリスト用変数
            $queue = array();

            // フォルダ内のチェック
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

    }

    // サブディレクトリを再帰的に呼び出す
    function serchSubDir($comp_code, $dir, $path)
    {
        serchDir($comp_code, $path.$dir."/");
    }

    // ファイル数、読み込みを実施
    function setQueue($comp_code, $queue, $path)
    {
        foreach ($queue as $file)
        {
            read_file($comp_code, $file, $path.$file);
        }
    }

    //ファイルを読み込んで書き込み
    function read_file($comp_code, $filename, $filefull)
    {
        // DBスキーマ名
        global $schema;

        // DBテーブル名
        global $table_name;

        $time_start = microtime(true);

//        //企業マスタを読込み
//        $sql = 'SELECT * FROM public.m_company_contract WHERE is_del = 0';
//        $companyContractList = getList($sql);
//
//        $key_file_name = "";
//
//        // スキーマ名
//        $schema = "acrossring";
//
//        // $filename からPOS種別を判断して、日付を取り出す yyyymmdd
//        $sql = "select * from ".$schema.".m_pos_key_file where is_del = 0 and pos_key_type = 2";
//
//        $key_file = array();
//
//        $rows = getList($sql);
//        $key_file_cnt = count($key_file);
//
//        if($rows){
//            while($row = $rows[$key_file_cnt]) {
//                $key_file[$key_file_cnt] = $row["pos_key_file_name"];
//                $key_file_cnt += 1;
//            }
//        }
//
//        //ファイル名の中にキーファイル名があるかチェック
//        $key_file_ok = 0;
//        for ($jj = 0; $jj < $key_file_cnt; $jj++){
//            if (strstr($filename, $key_file[$jj].'.csv')){
//
//                //ファイル名から日付取り出し
//                $tenpo_code = substr($filename, 1, 4);  //店舗コード
//                $yyyy = substr($filename, 6, 4);    //yyyy
//                $mm = substr($filename, 10, 2);     //mm
//                $dd = substr($filename, 12, 2);     //dd
//                $key_file_name = $key_file[$jj];    //キーファイル名
//                $key_file_ok = 1;
//                break;
//            }
//        }
//
//        if ($key_file_ok == 0){
//            //キーファイルの指定なし　処理スキップ
//            //          echo "(".$filename.")POSキーファイルの指定が無いファイルを検出しました<br />\n";
//            //          log_insert("", "", "", "POSキーファイルの指定が無いファイルを検出しました", "POSインポート", "", "");
//            goto skip;
//        }
//
//        $table_name = $schema.".t_import_data_item";
//        $num = 1;
//        $get_ok = 0;

        $fp = fopen($filefull, 'r');

        if ($fp){
            if (flock($fp, LOCK_SH)){
//                // 取込み前に対象日の同じキーファイルタイプで該当組織分を上書きにするため削除
//                $sql = "DELETE FROM ".$table_name." WHERE target_date = '".$yyyy.$mm.$dd."' and pos_key_file_id = '".$key_file_name."' and pos_code = '".$tenpo_code."'";
//
//                sqlExec($sql);

                // レコード件数チェック
                $num = 0;
                while (!feof($fp)) {
                    $buffer = fgets($fp);
                    // ファイル区分
                    $strKBN = substr($buffer, 6, 3);
                    if ($strKBN === 'STA') {
                        //ヘッダレコード
                        $intRecordCnt_STA = intval(substr($buffer, 9, 6));
                        $strCreateDateTime = trim(substr($buffer, 15, 14));
                        $num ++;
                    }
                    else if ($strKBN === 'END') {
                        // フッタレコード
                        $intRecordCnt_END = intval(substr($buffer, 9, 6));
                        $num ++;
                    }
                    else if ($strKBN === '021') {
                        // 発注明細レコード
                        $num ++;
                    }
                }
                // 件数チェック(ヘッダとフッタの設定値および実際の行数を比較)
                if ($intRecordCnt_STA !== $intRecordCnt_END || $intRecordCnt_STA !== $num) {

                    goto skip;
                }

                // ファイルポインタを先頭に戻す
                if (rewind($fp) === false) {
                    //NOP
                }

                $num = 0;
                while (!feof($fp)) {
                    $buffer = fgets($fp);

                    // ファイル区分
                    $strKBN = substr($buffer, 6, 3);
                    if ($strKBN !== '021') {
                        // 明細レコード以外はスキップ
                        continue;
                    }

//                    //デリミタを除外
//                    $str =  str_replace('"','',char_chg("to_db", $buffer));
//
//                    $sql = "INSERT INTO ".$table_name." (";
//                    $sql .= "num, ";
//                    $sql .= "pos_code, ";
//                    $sql .= "target_date, ";
//                    $sql .= "file_name, ";
//                    $sql .= "data_line, ";
//                    $sql .= "pos_key_file_id, ";
//                    $sql .= "registration_time ";
//                    $sql .= ") ";
//                    $sql .= "VALUES(";
//                    $sql .= $num.", ";
//                    $sql .= "'".$tenpo_code."', ";
//                    $sql .= "'".$yyyy.$mm.$dd."', ";
//                    $sql .= "'".$filename."', ";
//                    $sql .= "'".$str."', ";
//                    $sql .= "'".$key_file_name."', ";
//                    $sql .= "CURRENT_TIMESTAMP)";


                    $intPos = 13;   // SUPP_CDの位置

                    $sql  = "";
                    $sql .= "INSERT INTO ".$table_name." (";
                    $sql .= " CREATEDATETIME";
                    $sql .= ",SUPP_CD";
                    $sql .= ",ORD_DATE";
                    $sql .= ",ARR_DATE";
                    $sql .= ",CONTRACT_MONTH";
                    $sql .= ",GYOMU_DATA_KBN";
                    $sql .= ",INP_DATE";
                    $sql .= ",INP_TIME";
                    $sql .= ",ORDER_KBN";
                    $sql .= ",PROD_CD";
                    $sql .= ",ORD_AMOUNT_S";
                    $sql .= ",ORD_AMOUNT";
                    $sql .= ",COSTPRICE";
                    $sql .= ",SALEPRICE";
                    $sql .= ",AUTO_ORDER_KBN";
                    $sql .= ",SECT_CD";
                    $sql .= ",PRIV_CLASS_CD";
                    $sql .= ",JICFS_CLASS_CD";
                    $sql .= ",KICS_SUPP_CD";
                    $sql .= ",PROD_KN";
                    $sql .= ",PROD_CAPA_KN";
                    $sql .= ",STAFF_CD";
                    $sql .= ",SHOP_CD";
                    $sql .= ",EOS_SEQ";
                    $sql .= ",BASE_SHOP_CD";
                    $sql .= ",BASE_FIRM_CUST_CD";
                    $sql .= ") ";
                    $sql .= "VALUES(";
                    $sql .= " '".$strCreateDateTime."'";                                                                                    // CREATEDATETIME
                    $sql .= ",'".trim(substr($buffer, $intPos,  4))."'";                                                $intPos +=  4;      // SUPP_CD
                    $sql .= ",'".trim(substr($buffer, $intPos,  8))."'";                                                $intPos +=  8;      // ORD_DATE
                    $sql .= ",'".trim(substr($buffer, $intPos,  8))."'";                                                $intPos +=  8;      // ARR_DATE
                    $sql .= ",'".trim(substr($buffer, $intPos,  2))."'";                                                $intPos +=  2;      // CONTRACT_MONTH
                    $sql .= ",'".trim(substr($buffer, $intPos,  1))."'";                                                $intPos +=  1;      // GYOMU_DATA_KBN
                    $sql .= ",'".trim(substr($buffer, $intPos,  8))."'";                                                $intPos +=  8;      // INP_DATE
                    $sql .= ",'".trim(substr($buffer, $intPos,  4))."'";                                                $intPos +=  4;      // INP_TIME
                    $sql .= ",'".trim(substr($buffer, $intPos,  1))."'";                                                $intPos +=  1;      // ORDER_KBN
                    $sql .= ",'".trim(substr($buffer, $intPos, 13))."'";                                                $intPos += 13;      // PROD_CD
                    $sql .= ",'".trim(substr($buffer, $intPos,  1))."'";                                                $intPos +=  1;      // ORD_AMOUNT_S
                    $sql .= ",".strval(floatval(substr($buffer, $intPos,  4)));                                         $intPos +=  4;      // ORD_AMOUNT
                    $strTmp  = '';
                    $strTmp .= substr($buffer, $intPos,  7);                                                            $intPos +=  7;      // COSTPRICE(INT)
                    $strTmp .= '.';
                    $strTmp .= substr($buffer, $intPos,  2);                                                            $intPos +=  2;      // COSTPRICE(DEC)
                    $sql .= ",".strval(floatval($strTmp));                                                                                  // COSTPRICE
                    $sql .= ",".strval(floatval(substr($buffer, $intPos,  7)));                                         $intPos +=  7;      // SALEPRICE
                    $sql .= ",'".trim(substr($buffer, $intPos,  1))."'";                                                $intPos +=  1;      // AUTO_ORDER_KBN
                    $sql .= ",'".trim(substr($buffer, $intPos,  2))."'";                                                $intPos +=  2;      // SECT_CD
                    $sql .= ",'".trim(substr($buffer, $intPos,  4))."'";                                                $intPos +=  4;      // PRIV_CLASS_CD
                    $sql .= ",'".trim(substr($buffer, $intPos,  6))."'";                                                $intPos +=  6;      // JICFS_CLASS_CD
                    $sql .= ",'".trim(substr($buffer, $intPos,  6))."'";                                                $intPos +=  6;      // KICS_SUPP_CD
                    $sql .= ",'".trim(mb_convert_encoding(substr($buffer, $intPos, 25),"UTF-8","SJIS"))."'";            $intPos += 25;      // PROD_KN
                    $sql .= ",'".trim(mb_convert_encoding(substr($buffer, $intPos,  6),"UTF-8","SJIS"))."'";            $intPos +=  6;      // PROD_CAPA_KN
                    $sql .= ",'".trim(substr($buffer, $intPos,  6))."'";                                                $intPos +=  6;      // STAFF_CD
                    $sql .= ",'".trim(substr($buffer, $intPos,  3))."'";                                                $intPos +=  3;      // SHOP_CD
                    $sql .= ",'".trim(substr($buffer, $intPos, 10))."'";                                                $intPos += 10;      // EOS_SEQ
                    $intPos += 95;                                                                                                          // FILLER
                    $sql .= ",'".trim(substr($buffer, $intPos,  3))."'";                                                $intPos +=  3;      // BASE_SHOP_CD
                    $sql .= ",'".trim(substr($buffer, $intPos,  6))."'";                                                                    // BASE_FIRM_CUST_CD
                    $sql .= ") ";

                    sqlExec($sql);
                    $num ++;
                }

                // ファイルのロックを解除
                flock($fp, LOCK_UN);

//                // mcode取込みを呼び出し
//                require("create_engine_aitem.php");


            }else{
                echo "(".$filename.") ファイルのロックに失敗しました<br />\n";
                //log_insert("", "", "", "(".$filename.") ファイルのロックに失敗しました", "POSインポート", "", "");
            }
        }




        echo "(".$filename.") ファイルの取り込みに成功しました<br />\n";
        //log_insert("", "", "", "(".$filename.") ファイルの取り込みに成功しました", "POSインポート", "", "");

        $timelimit = microtime(true) - $time_start;
        echo $timelimit." seconds<br />\n";

        echo "-----<br />\n";

        @ob_flush();
        @flush();

        skip:

    }

    //**************************************************
    //
    // 機能     :POS発注データファイルDB取込メイン
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************

    // テーブルを空にする
    $sql  = "";
    $sql .= "TRUNCATE TABLE ".$table_name;
    $sql .= " RESTART IDENTITY";
    sqlExec($sql);


    serchDir("", $path);

    // VAN発注データ変換ファイル出力
    require('create_engine_as400_order_std.php');
    require('create_engine_as400_order_sgm.php');
    require('create_engine_as400_order_ajd.php');


    // テーブルを空にする
    $sql  = "";
    $sql .= "TRUNCATE TABLE ".$table_name;
    $sql .= " RESTART IDENTITY";
//    sqlExec($sql);
?>
