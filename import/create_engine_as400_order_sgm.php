<?php
    //**************************************************
    //
    // 機能     :VAN発注データ変換ファイル出力
    //
    // 機能説明 :
    //
    // 備考     :シグマ一括フォーマット
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
    function CreateOrderRec_sgm($IN_aryDataBuf,&$OUT_strOutBuf) {

        $strMe = 'CreateOrderRec_sgm';

        $OUT_strOutBuf = '';
        $strRecLine = '';
//print_r($IN_aryDataBuf);
        $intPos = 0;
        foreach ($IN_aryDataBuf as $strDataRow) {
//echo $strDataRow."\r\n";
            if (substr($strDataRow, 0, 1) === 'B') {
                //伝票ヘッダーレコード
                $strRecLine .= $strDataRow;
            }
            else if (substr($strDataRow, 0, 1) === 'D') {
                //伝票明細レコード

                if (($intPos % 2) === 0) {
                    $strRecLine .= substr($strDataRow,3);       // 偶数行の明細はレコード頭の"D11"を省く
                    $strRecLine .= str_pad('', 3);              // ("D11"を省いた分)末尾に予備(3)を付加する
                }
                else {
                    $strRecLine .= $strDataRow;
                }
            }
            else {
                //NOP
            }
//echo $strRecLine."\r\n";

            if (strlen($strRecLine) === 256) {
                $OUT_strOutBuf .= $strRecLine;
                $OUT_strOutBuf .= PHP_EOL;
                $strRecLine = '';
            }

            $intPos ++;
        }

        //明細数が1または3～5の場合は256桁に満たないのでスペース埋め
        if (strlen($strRecLine) > 1 && strlen($strRecLine) < 256) {
            $strRecLine = str_pad($strRecLine, 256);
            $OUT_strOutBuf .= $strRecLine;
            $OUT_strOutBuf .= PHP_EOL;
        }

//echo $OUT_strOutBuf."\r\n";
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
    $table_name_t = $schema.".t_import_pos_order";      //POS発注データ
    $table_name_c = $schema.".m_choall";                //仕入先帳合マスタ
    $table_name_s = $schema.".m_supp";                  //取引先マスタ
    $table_name_m = $schema.".m_sgm_prod";              //シグマ商品マスタ(仮)

    $sql_import  = "";
    $sql_import .= "select ";
    $sql_import .=     $table_name_t.".*";
    $sql_import .= ",".$table_name_c.".ONLINE_CD";              //帳合オンラインコード
    $sql_import .= ",".$table_name_m.".CLASS_CD";               //シグマ商品分類コード(仮)
    $sql_import .= ",".$table_name_m.".DIRECT_DELIVERY_KBN";    //直送区分(仮)
    $sql_import .= ",".$table_name_m.".SPEC_SALEPRICE_KBN";     //特売区分(仮)
    $sql_import .= " from ".$table_name_t;
    $sql_import .= " left outer join ".$table_name_c;
    $sql_import .= " on (".$table_name_c.".BASE_FIRM_CUST_CD = ".$table_name_t.".BASE_FIRM_CUST_CD and ".$table_name_c.".SUPP_CD = ".$table_name_t.".SUPP_CD)";
    $sql_import .= " left outer join ".$table_name_s;
    $sql_import .= " on (".$table_name_s.".SUPP_CD = ".$table_name_c.".ONLINE_CD)";
    $sql_import .= " left outer join ".$table_name_m;
    $sql_import .= " on (".$table_name_t.".PROD_CD = ".$table_name_m.".PROD_CD)";
    $sql_import .= " where ".$table_name_s.".ORDER_FORMAT_TYPE = '1'";      //発注データ形式 = 1:シグマ一括
    $sql_import .= " order by ";
    $sql_import .=     $table_name_t.".BASE_FIRM_CUST_CD";
    $sql_import .= ",".$table_name_t.".SUPP_CD";
    $sql_import .= ",".$table_name_t.".ORD_DATE";
    $sql_import .= ",".$table_name_t.".ARR_DATE";
    $sql_import .= ",".$table_name_m.".CLASS_CD";
    $sql_import .= ",".$table_name_m.".DIRECT_DELIVERY_KBN";
    $sql_import .= ",".$table_name_m.".SPEC_SALEPRICE_KBN";
    $sql_import .= ",".$table_name_t.".PROD_CD";
//echo $sql_import."\r\n"; exit;
    $rows_import = getList($sql_import);

    if($rows_import){

        //オンラインコード初期化
        $strOnline_Cd               = '';
        //前処理オンラインコード初期化
        $strBef_Online_Cd           = '';
        //商品分類コード初期化
        $strProd_Class_Cd           = '';
        //前処理商品分類コード初期化
        $strBef_Prod_Class_Cd       = '';
        //直送区分初期化
        $strDIRECT_DELIVERY_KBN     = '';
        //前処理直送区分初期化
        $strBef_DIRECT_DELIVERY_KBN = '';
        //特売区分初期化
        $strSPEC_SALEPRICE_KBN      = '';
        //前処理特売区分初期化
        $strBef_SPEC_SALEPRICE_KBN  = '';

//        //ヘッダ格納変数初期化
//        $strHeaderBuf = '';

//        //明細格納配列初期化
//        $aryDetailBuf = array();

        //データ格納配列初期化
        $aryDataBuf = array();

        $strOutBuf = "";

        //処理明細数
        $intProcDetailCnt = 0;

        foreach($rows_import as $row_import) {

            //取引先コード(6)
            $strBase_Firm_Cust_Cd = db2str($row_import, 'base_firm_cust_cd', 6);
            //仕入先コード(4)
            $strSupp_Cd = db2str($row_import, 'supp_cd', 4);
            //商品分類コード(02)
            $strProd_Class_Cd = db2str($row_import, 'class_cd', 2);
            //直送区分(1)
            $strDIRECT_DELIVERY_KBN = db2str($row_import, 'direct_delivery_kbn', 1);
            //特売区分(1)
            $strSPEC_SALEPRICE_KBN = db2str($row_import, 'spec_saleprice_kbn', 1);

//            //帳合マスタ参照
//            $aryMst_ChoAll = array();
//            $aryMst_ChoAll['base_firm_cust_cd'] = $strBase_Firm_Cust_Cd;
//            $aryMst_ChoAll['supp_cd'     ]      = $strSupp_Cd;
//            if (GetMstChoAll($aryMst_ChoAll) === false) {
//                continue;
//            }
//            $strOnline_Cd  = $aryMst_ChoAll['online_cd'];
            $strOnline_Cd  = trim(db2str($row_import, 'online_cd', 8));
//            //データフォーマットがシグマ一括の取引先でなければスキップ
//            if ($aryMst_ChoAll['order_format_type'] !== '1') {
//                continue;
//            }

            //オンラインコードが変わった時、またはループ初回処理時
            //または商品分類コードが変わった時
            //または特売区分が変わった時
            //または6明細に達した時（どうやら1伝票につき最大6明細の模様）
            if ($strOnline_Cd          !== $strBef_Online_Cd          ||
                $strProd_Class_Cd      !== $strBef_Prod_Class_Cd      ||
                $strSPEC_SALEPRICE_KBN !== $strBef_SPEC_SALEPRICE_KBN ||
                $intProcDetailCnt >= 6) {
                //--------------------------------------
                //前処理オンラインコードあり＝出力データあり
                if ($strBef_Online_Cd !== '') {
                    //発注データレコード生成
                    CreateOrderRec_sgm($aryDataBuf,$strOutBuf);
                    //発注データファイル出力
                    PutFileOrderDat($strBef_Online_Cd, $strOutBuf);

                    //発注データ変換した仕入先コードを保持(メール送信するため)
                    if (in_array($strBef_Online_Cd, $arySupp_Cd, true) === false) {
                        $arySupp_Cd[] = $strBef_Online_Cd;
                    }
                    //明細行番号初期化
                    $intLineno = 0;
                }
                //--------------------------------------

//                //明細格納配列初期化
//                $aryDetailBuf = array();

                //データ格納配列初期化
                $aryDataBuf = array();

                //出力バッファ初期化
                $strOutBuf = '';

                //ヘッダレコード
                //レコード区分(1)
                $strOutBuf .= 'B';
                //データ区分(2)
                $strOutBuf .= '11';
                //伝票番号(9)
                $strSeq = GetNextSeq();
                if ($strSeq === '') {
                    $strOutBuf .= str_pad('', 9);
                }
                else {
                    $strOutBuf .= sprintf('%09s', $strSeq);
                }
                //小売店コード(6)
                $strOutBuf .= $strBase_Firm_Cust_Cd;
                //地区コード(2)※'03','04','05'のいずれかの値が入る(KICS任意項目として使用)
                $strOutBuf .= str_pad('', 2);       //仮
                //分類コード(2)※構成01～09
//                $strOutBuf .= str_pad('', 2);       //仮
                $strOutBuf .= $strProd_Class_Cd;
                //直送区分(1)※直送時「1」、直送でない場合は空白←判定条件は？
                $strOutBuf .= ' ';                  //仮
                //特売区分(1)※特売時「1」、特売でない場合は空白←判定条件は？
                //$strOutBuf .= ' ';                  //仮
                $strOutBuf .= $strSPEC_SALEPRICE_KBN;
                //限(2)
                $strOutBuf .= '00';
                //発注日(6)
                $strOutBuf .= substr(db2str($row_import, 'ord_date', 8), 2, 6);
                //納品日(6)
                $strOutBuf .= substr(db2str($row_import, 'arr_date', 8), 2, 6);
                //仕入先コード(8)
                $strOutBuf .= sprintf('%-8s', $strOnline_Cd);
                //便区分(1)
                $strOutBuf .= ' ';
                //予備(81)
                $strOutBuf .= str_pad('', 81);

                //ヘッダバッファ設定
//                $strHeaderBuf = $strOutBuf;
                $aryDataBuf[] = $strOutBuf;

                //処理明細数クリア
                $intProcDetailCnt = 0;
            }

            //出力バッファ初期化
            $strOutBuf = '';
            //レコード区分(1)
            $strOutBuf .= 'D';
            //データ区分(2)
            $strOutBuf .= '11';
            //商品コード(13)
            $strOutBuf .= db2str($row_import, 'prod_cd', 13);
            //発注数(4)
            $strOutBuf .= sprintf("%04d", intval($row_import['ord_amount']));
            //商品名カナ(15)
            $strOutBuf .= db2str($row_import, 'prod_kn', 15);
            //予備(4)
            $strOutBuf .= str_pad('', 4);
            //規格容量(6)
            $strOutBuf .= db2str($row_import, 'prod_capa_kn', 6);
            //仕入単価(6)
            try {
                $strTmp = sprintf('%06d', floor(floatval($row_import['costprice'])));
                if ($strTmp === '000000') {
                    $strTmp = str_pad('', 6);
                }
            }
            catch (Exception $e) {
                $strTmp = str_pad('', 6);
            }
            $strOutBuf .= $strTmp;
            //予備(1)
            $strOutBuf .= ' ';      //仮
            //売価(6)
            try {
                $intTmp = intval($row_import['saleprice']);
                $strTmp = sprintf('%06d', $intTmp);
                if ($strTmp === '000000') {
                    $strTmp = str_pad('', 6);
                }
            }
            catch (Exception $e) {
                $strTmp = str_pad('', 6);
            }
            $strOutBuf .= $strTmp;
            //行番号(2)
            $intLineno = ((++ $intLineno) % 100);
            if ($intLineno === 0) {
                $intLineno ++;
            }
            $strOutBuf .= sprintf('%02d', $intLineno);
            //予備(4)
            $strOutBuf .= str_pad('', 4);

            //明細バッファ設定
//            $aryDetailBuf[] = $strOutBuf;
            $aryDataBuf[] = $strOutBuf;

            //処理明細数インクリメント
            $intProcDetailCnt ++;

            //オンラインコードを保持
            $strBef_Online_Cd  = $strOnline_Cd;

            //商品分類コードを保持
            $strBef_Prod_Class_Cd = $strProd_Class_Cd;

            //直送区分を保持
            $strBef_DIRECT_DELIVERY_KBN = $strDIRECT_DELIVERY_KBN;

            //特売区分を保持
            $strBef_SPEC_SALEPRICE_KBN = $strSPEC_SALEPRICE_KBN;
        }

        //ループ脱出後
        //--------------------------------------
        //前処理KICS仕入先コードあり＝出力データあり
        if ($strBef_Online_Cd !== '') {
            //発注データレコード生成
            CreateOrderRec_sgm($aryDataBuf,$strOutBuf);
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
