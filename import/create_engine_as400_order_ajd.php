<?php
    //**************************************************
    //
    // 機能     :VAN発注データ変換ファイル出力
    //
    // 機能説明 :
    //
    // 備考     :AS400ダウンサイジング
    //
    //**************************************************
    require_once("create_engine_as400_order_Function.php");

    //**************************************************
    //
    // 機能     :発注データレコード生成
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************
    function CreateOrderRec_ajd($IN_strOnline_Cd,$IN_aryDataBuf,&$OUT_strOutBuf,$IN_blnSysFileHeader) {

        $strMe = 'CreateOrderRec_ajd';

        $OUT_strOutBuf = '';
        $strRecLine = '';

        //発注データ出力していない
        if ($IN_blnSysFileHeader === false) {
            //システム・ファイル・ヘッダー
            //レコード区分(1)
            $strRecLine .= '%';
            //データ種別(2)
            $strRecLine .= '01';
            //データ処理日付(6)
            $strRecLine .= date('ymdHis');          //データ作成時のマシン日付、マシン時刻
            //予備(6)
            $strRecLine .= str_pad('', 6);
            //データ送信元(8)
            $strRecLine .= '80630000';              //ミリオセンターコード「80630000」固定
            //最終送信先(8)
            $strRecLine .= $IN_strOnline_Cd;        //取引先コード
            //予備(91)
            $strRecLine .= str_pad('', 91);
        }

        $intPos = 0;
        foreach ($IN_aryDataBuf as $strDataRow) {
            if (substr($strDataRow, 0, 1) === 'B') {
                if ($intPos > 0 && strlen($strRecLine) > 0 && strlen($strRecLine) < 256) {
                    $OUT_strOutBuf .= str_pad($strRecLine, 256).PHP_EOL;
                    $strRecLine = '';
                }
                //伝票ヘッダーレコード
                $strRecLine .= $strDataRow;
            }
            else if (substr($strDataRow, 0, 1) === 'D') {
                //伝票明細レコード
                $strRecLine .= $strDataRow;
            }
            else {
                //NOP
            }

            if (strlen($strRecLine) === 256) {
                $OUT_strOutBuf .= $strRecLine;
                $OUT_strOutBuf .= PHP_EOL;
                $strRecLine = '';
            }
            $intPos ++;
        }
        if (strlen($strRecLine) > 0 && strlen($strRecLine) < 256) {
            $OUT_strOutBuf .= str_pad($strRecLine, 256).PHP_EOL;
        }
        return true;
    }

    //**************************************************
    //
    // 機能     :VAN発注データ変換ファイル出力メイン
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************

    $arySupp_Cd = array();

    // DBスキーマ名
    $schema = "as400";
    // DBテーブル名
    $table_name_t = $schema.".t_import_pos_order";
    $table_name_c = $schema.".m_choall";                //仕入先帳合マスタ
    $table_name_s = $schema.".m_supp";                  //取引先マスタ
    $table_name_m = $schema.".m_ajd_store";             //ＡＪＤ小売店マスタ(仮)

    $sql_import  = "";
    $sql_import .= "select ";
    $sql_import .=     $table_name_t.".*";
    $sql_import .= ",".$table_name_c.".ONLINE_CD";              //帳合オンラインコード
    $sql_import .= ",".$table_name_s.".SUPP_NM";                //取引先名
    $sql_import .= ",".$table_name_m.".STORE_CD";               //ＡＪＤ小売店コード(仮)
    $sql_import .= ",".$table_name_m.".STORE_NM";               //ＡＪＤ小売店名(仮)
    $sql_import .= ",".$table_name_m.".COMP_NM";                //ＡＪＤ企業名(仮)
    $sql_import .= " from ".$table_name_t;
    $sql_import .= " left outer join ".$table_name_c;
    $sql_import .= " on (".$table_name_c.".BASE_FIRM_CUST_CD = ".$table_name_t.".BASE_FIRM_CUST_CD and ".$table_name_c.".SUPP_CD = ".$table_name_t.".SUPP_CD)";
    $sql_import .= " left outer join ".$table_name_s;
    $sql_import .= " on (".$table_name_s.".SUPP_CD = ".$table_name_c.".ONLINE_CD)";
    $sql_import .= " left outer join ".$table_name_m;
    $sql_import .= " on (".$table_name_m.".BASE_FIRM_CUST_CD = ".$table_name_t.".BASE_FIRM_CUST_CD)";
    $sql_import .= " where ".$table_name_s.".ORDER_FORMAT_TYPE = '2'";      //発注データ形式 = 2:AJD
    $sql_import .= " order by ";
    $sql_import .=     $table_name_t.".BASE_FIRM_CUST_CD";
    $sql_import .= ",".$table_name_t.".SUPP_CD";
    $sql_import .= ",".$table_name_t.".ORD_DATE";
    $sql_import .= ",".$table_name_t.".ARR_DATE";
    $sql_import .= ",".$table_name_t.".SECT_CD";
    $sql_import .= ",".$table_name_t.".PROD_CD";
//echo $sql_import."\r\n"; exit;
    $rows_import = getList($sql_import);

    if($rows_import){

        //オンラインコード初期化
        $strOnline_Cd = '';

        //オンライン名称初期化
        $strOnline_Nm = '';

        //前処理オンラインコード初期化
        $strBef_Online_Cd       = '';

        //前処理部門コード初期化
        $strBef_Sect_Cd = '';

        //データ格納配列初期化
        $aryDataBuf = array();

        $strOutBuf = '';

        //処理明細数
        $intProcDetailCnt = 0;

        foreach($rows_import as $row_import) {

            //取引先コード(6)
            $strBase_Firm_Cust_Cd = db2str($row_import, 'base_firm_cust_cd', 6);
            //仕入先コード(4)
            $strSupp_Cd = db2str($row_import, 'supp_cd', 4);
            //部門コード(部門ごとに伝票ヘッダを分けている模様)
            $strSect_Cd = db2str($row_import, 'sect_cd', 2);

//            //帳合マスタ参照
//            $aryMst_ChoAll = array();
//            $aryMst_ChoAll['base_firm_cust_cd'] = $strBase_Firm_Cust_Cd;
//            $aryMst_ChoAll['supp_cd'     ]      = $strSupp_Cd;
//            if (GetMstChoAll($aryMst_ChoAll) === false) {
//                continue;
//            }
//            $strOnline_Cd  = $aryMst_ChoAll['online_cd'];
//            $strOnline_Nm  = $aryMst_ChoAll['online_nm'];
//            $strOrderFormatType = $aryMst_ChoAll['order_format_type'];

            $strOnline_Cd  = trim(db2str($row_import, 'online_cd', 8));
            $strOnline_Nm  = trim(db2str($row_import, 'supp_nm',  20));
            $strOrderFormatType = trim(db2str($row_import, 'order_format_type', 1));

//            //ＡＪＤでなければスキップ
//            if ($strOrderFormatType !== '2') {
//                continue;
//            }

            //オンラインコードが変わった時、またはループ初回処理時
            if ($strOnline_Cd !== $strBef_Online_Cd) {

//echo "bef:[".$strBef_Online_Cd."] now[".$strOnline_Cd."]\r\n";
                //前処理オンラインコードあり＝出力データあり
                if ($strBef_Online_Cd !== '') {
                    //発注データ出力済み取引先コードをチェック
                    $blnExists = in_array($strBef_Online_Cd, $arySupp_Cd, true);
                    //発注データレコード生成
                    CreateOrderRec_ajd($strBef_Online_Cd, $aryDataBuf, $strOutBuf, $blnExists);
                    //発注データファイル出力
                    PutFileOrderDat($strBef_Online_Cd, $strOutBuf);

                    //発注データ変換した取引先コードを保持(メール送信するため)
                    if (in_array($strBef_Online_Cd, $arySupp_Cd, true) === false) {
                        $arySupp_Cd[] = $strBef_Online_Cd;
                    }
                }

                //データ格納配列初期化
                $aryDataBuf = array();
            }

            //部門コードが変わった時、または6明細に達した時
            if ($strSect_Cd !== $strBef_Sect_Cd || $intProcDetailCnt >= 6) {

                //出力バッファ初期化
                $strOutBuf = '';

                //ヘッダレコード
                //レコード区分(1)
                $strOutBuf .= 'B';
                //データ区分(2)
                $strOutBuf .= '01';
                //伝票番号(9)
                $strSeq = GetNextSeq();
                if ($strSeq === '') {
                    $strOutBuf .= str_pad('', 9);
                }
                else {
                    $strOutBuf .= sprintf('%09s', $strSeq);
                }
                //小売店コード(8)
//                $strOutBuf .= sprintf('%08s', $strBase_Firm_Cust_Cd);
                $strOutBuf .= db2str($row_import, 'store_cd', 8);
                //予備(6)
                $strOutBuf .= str_pad('', 6);
                //伝票区分(2)※01:通常発注/11:特売発注←判定条件は？
                $strOutBuf .= '01';     //仮
                //発注日(6)
                $strOutBuf .= substr(db2str($row_import, 'ord_date', 8),2,6);
                //納品日(6)
                $strOutBuf .= substr(db2str($row_import, 'arr_date', 8),2,6);
                //取引先(8)
                $strOutBuf .= str_pad($strOnline_Cd, 8);
                //社名
                $strOutBuf .= db2str($row_import, 'comp_nm', 20);
                //店名
                $strOutBuf .= db2str($row_import, 'store_nm', 20);
                //取引先名
                $strOutBuf .= str_pad($strOnline_Nm, 20);
                //E欄(17)
                $strOutBuf .= str_pad('EOS',17);
                //便区分(1)
                $strOutBuf .= ' ';                  //仮
                //予備(2)
                $strOutBuf .= str_pad('',2);

                //ヘッダバッファ設定
                $aryDataBuf[] = $strOutBuf;

                //処理明細数クリア
                $intProcDetailCnt = 0;
            }

            //出力バッファ初期化
            $strOutBuf = '';

            //明細レコード
            //レコード区分(1)
            $strOutBuf .= 'D';
            //データ区分(2)
            $strOutBuf .= '01';
            //伝票行番号(2)
            $intProcDetailCnt ++;
            $strOutBuf .= sprintf('%02d', $intProcDetailCnt);
            //商品コード(13)
            $strOutBuf .= db2str($row_import, 'prod_cd', 13);
            //入数(4)
            $strOutBuf .= sprintf('%04d', 1);       //仮
            //発注単位数(4)
            $strOutBuf .= sprintf("%04d", intval($row_import['ord_amount']));
            //予備(2)
            $strOutBuf .= str_pad('',2);
            //数量(N5V1)
            $dblAmount = doubleval($row_import['ord_amount']);
//echo $dblAmount."\r\n";
            try {
                $strTmp = sprintf("%07.1f", $dblAmount);
//echo $strTmp."\r\n";
                $strTmp = str_replace('.', '', $strTmp);
            }
            catch (Exception $e) {
                $strTmp = str_pad('', 6);
            }
//echo $strTmp."\r\n";
            $strOutBuf .= $strTmp;
            //原単価(N7V2)
            $dblCostPrice = doubleval($row_import['costprice']);
//echo $dblCostPrice."\r\n";
            try {
                $strTmp = sprintf('%010.2f', intval($dblCostPrice));
//echo $strTmp."\r\n";
                $strTmp = str_replace('.', '', $strTmp);
            }
            catch (Exception $e) {
                $strTmp = str_pad('', 9);
            }
            $strOutBuf .= $strTmp;
//echo $strTmp."\r\n";
            //売単価(7)
            $dblSalePrice = doubleval($row_import['saleprice']);
            try {
                $strTmp = sprintf('%07d', floor($dblSalePrice));
            }
            catch (Exception $e) {
                $strTmp = str_pad('', 7);
            }
            $strOutBuf .= $strTmp;
            //原価金額(10)※数量×原単価
            $strOutBuf .= sprintf('%010d', floor($dblAmount * $dblCostPrice));
            //売価金額(10)※数量×売単価
            $strOutBuf .= sprintf('%010d', floor($dblAmount * $dblSalePrice));
            //予備１(9)
            $strOutBuf .= str_pad('',9);
            //商品名(25)
            $strOutBuf .= db2str($row_import, 'prod_kn', 25);
            //予備２(6)
            $strOutBuf .= str_pad('',6);
            //引合(2)※部門コード
            $strOutBuf .= $strSect_Cd;
            //予備３(2)
            $strOutBuf .= str_pad('',2);
            //予備４(7)
            $strOutBuf .= str_pad('',7);
            //予備５(5)
            $strOutBuf .= str_pad('',5);
            //予備(2)
            $strOutBuf .= str_pad('',2);

            //バッファ設定
            $aryDataBuf[] = $strOutBuf;

            //オンラインコードを保持
            $strBef_Online_Cd  = $strOnline_Cd;
            //部門コードを保持
            $strBef_Sect_Cd = $strSect_Cd;
        }

        //ループ脱出後
//echo "bef:[".$strBef_Online_Cd."] now[".$strOnline_Cd."]\r\n";
        //--------------------------------------
        //前処理KICS仕入先コードあり＝出力データあり
        if ($strBef_Online_Cd !== '') {
            //発注データ変換出力取引先コードをチェック
            $blnExists = in_array($strBef_Online_Cd, $arySupp_Cd, true);
            //発注データレコード生成
            CreateOrderRec_ajd($strBef_Online_Cd, $aryDataBuf, $strOutBuf, $blnExists);
            //発注データファイル出力
            PutFileOrderDat($strBef_Online_Cd, $strOutBuf);
        }
        //--------------------------------------

        //発注データ変換した仕入先コードを保持(メール送信するため)
        if (in_array($strBef_Online_Cd, $arySupp_Cd, true) === false) {
            $arySupp_Cd[] = $strBef_Online_Cd;
        }
    }
//echo Count($arySupp_Cd)."\r\n";

    //変換処理あり
//    if (Count($arySupp_Cd) > 0) {
//        //仕入先宛てにメール送信
//        if (MailSend($arySupp_Cd) === false) {
//            //NOP
//        }
//    }

//    // テーブルを空にする
//    $sql  = "";
//    $sql .= "TRUNCATE TABLE ".$table_name_t;
//    $sql .= " RESTART IDENTITY";
//    sqlExec($sql);

skip:

    $timelimit = microtime(true) - $time_start;
    echo $timelimit." seconds<br />\n";
    echo "-----<br />\n";
    @ob_flush();
    @flush();

?>
