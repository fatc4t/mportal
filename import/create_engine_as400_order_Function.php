<?php
    //**************************************************
    //
    // 機能     :VAN発注データ変換ファイル出力共通関数
    //
    // 機能説明 :
    //
    // 備考     :AS400ダウンサイジング
    //
    //**************************************************
    require_once("DBAccess_Function.php");

    function db2str($IN_dbRow, $IN_strField, $IN_intLength) {

        return str_pad(trim(substr(mb_convert_encoding(strval($IN_dbRow[$IN_strField]),"SJIS","UTF-8"), 0, $IN_intLength)), $IN_intLength);
    }

    //**************************************************
    //
    // 機能     :伝票番号取得
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************
    function GetNextSeq() {

        $strMe = 'GetNextSeq';

        // DBスキーマ名
        global $schema;

        $intSeq = 0;
        $sql = "select nextval('".$schema.".VAN_ORDER_SEQ') as ORDER_SEQ";
        $row = getList($sql);
        if ($row) {
            $intSeq = intval($row[0]['order_seq']);
        }
        return sprintf("%09d", $intSeq);
    }

    //**************************************************
    //
    // 機能     :仕入先帳合マスタ取得
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************
    function GetMstChoAll(&$OUT_aryMst_ChoAll) {

        $strMe = 'GetMstChoAll';

        // DBスキーマ名
        global $schema;

        $sql  = "";
        $sql .= "select ";
        $sql .= "     C.ONLINE_CD           as ONLINE_CD";
        $sql .= "    ,S.SUPP_NM             as ONLINE_NM";
        $sql .= "    ,S.ORDER_FORMAT_TYPE   as ORDER_FORMAT_TYPE";
        $sql .= "   from "           .$schema.".M_CHOALL C";
        $sql .= "   left outer join ".$schema.".M_SUPP S";
        $sql .= "   on (C.ONLINE_CD = S.SUPP_CD)";
        $sql .= " where C.BASE_FIRM_CUST_CD = '".$OUT_aryMst_ChoAll['base_firm_cust_cd']."'";
        $sql .= " and   C.SUPP_CD           = '".$OUT_aryMst_ChoAll['supp_cd']."'";
        $sql .= " and   C.ORDER_KBN         = '1'";
        $row = getList($sql);
        if ($row) {
            $OUT_aryMst_ChoAll['online_cd']         = trim(db2str($row[0], 'online_cd',          8));
            $OUT_aryMst_ChoAll['online_nm']         = trim(db2str($row[0], 'online_nm',         20));
            $OUT_aryMst_ChoAll['order_format_type'] = trim(db2str($row[0], 'order_format_type',  1));
            return true;
        }

        return false;
    }

    //**************************************************
    //
    // 機能     :発注データファイル出力
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************
    function PutFileOrderDat($IN_strOnline_Cd,$IN_strBuf) {

        $strMe = 'PutFileOrderDat';

        // VAN発注ファイル保存パス
        $path = "../profit/import/as400/send/";     // 仮

//echo "[".$IN_strOnline_Cd."]\r\n";
//echo "[".$IN_strBuf."]\r\n";

//        PutLog($strMe,'発注データファイル出力(オンラインコード:'.$IN_strOnline_Cd.')');
//        putLog($strMe,'****************************************');

        if ($IN_strOnline_Cd === '') {
//            PutLog($strMe,'オンラインコードが未設定です。');
            return false;
        }
        if ($IN_strBuf === '') {
//            PutLog($strMe,'出力データがありません。');
            return false;
        }

//        //変換ファイル格納ディレクトリ
//        $strDatCnvDir = $IN_strDir.$IN_strOnline_Cd;
//        //ディレクトリが存在しなければ作成
//        if (file_exists($strDatCnvDir) === false) {
//            if (mkdir($strDatCnvDir) === true) {
//                chown($strDatCnvDir, 'apache');
//                chgrp($strDatCnvDir, 'apache');
//            }
//        }
//        $strDatCnvFile = $strDatCnvDir.'/'.$IN_strOnline_Cd.'.TXT';
//        $strDatCnvFile = $IN_strDir.'/'.$IN_strOnline_Cd.'.TXT';
        $strDatCnvFile = $path.'/'.$IN_strOnline_Cd.'.TXT';

        //ファイルオープン(追加)
        $fp = fopen($strDatCnvFile, "a");
        if ($fp) {
            if (flock($fp, LOCK_EX)) {
                if (fwrite($fp, $IN_strBuf) === false) {
                    //ファイルライト失敗エラーログ
//                    PutLog($strMe,'ファイルライトに失敗しました。');
                }
                //ロック解除
                flock($fp, LOCK_UN);
            }
            else {
                //ファイルロック失敗エラーログ
//                PutLog($strMe,'ファイルロックに失敗しました。');
            }
            //ファイルクローズ
            fclose($fp);
        }
        if (file_exists($strDatCnvFile) === true) {
            chown($strDatCnvFile, 'apache');
            chgrp($strDatCnvFile, 'apache');
        }

//        //ダウンロード処理済み発注データファイル格納ディレクトリ作成
//        $strDatCnvDir .= '/dl/';
//        if (file_exists($strDatCnvDir) === false) {
//            if (mkdir($strDatCnvDir) === true) {
//                chown($strDatCnvDir, 'apache');
//                chgrp($strDatCnvDir, 'apache');
//            }
//        }

        return true;
    }
?>
