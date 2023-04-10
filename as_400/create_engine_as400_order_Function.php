<?php
/* * *****************************************************************************
  '*
  '*  ユーザー名  :
  '*
  '*  システム名  :MPORTAL
  '*
  '*  処理名      :送信処理-発注データ（共通関数）
  '*
  '*  処理概要    :
  '*
  '*  開発言語    :PHP
  '*
  '*  作成日      :2021/02/20
  '*  作成者      :バッタライ
  '*
  '*----------------------------------------------------------------------------
  '*  修正履歴
  '*  修正日         修正者          修正内容
  '*　2021/11/12   バッタライ     原価売価0にならないよに設定変更
  '*  2021/11/12   バッタライ     特売時の原価売価は特売マスタではなく商品マスタを参照する様に変更
  '*
  '*  
  '***************************************************************************** */

require_once("DBAccess_Function.php");

function db2str($IN_dbRow, $IN_strField, $IN_intLength) {

    return str_pad(trim(substr(mb_convert_encoding(strval($IN_dbRow[$IN_strField]), "SJIS", "UTF-8"), 0, $IN_intLength)), $IN_intLength);
}

//**************************************************************************
// 機能　　　：帳合先マスタ取得
//
// 引数　　　：発注データ形式（1：シグマ版　2：AJD版　3：シグマ版）
//
// 備考　　　：現在未使用
//
//**************************************************************************

function get_M_choall($order_format_type) {

    $sql = "";
    $sql .= "select  ";
    $sql .= "      BASE_FIRM_CUST_CD ";
    $sql .= "     ,regexp_replace (replace(replace(coalesce(M_CHOALL.BASE_FIRM_CUST_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as BASE_FIRM_CUST_KN ";
    $sql .= "     ,ONLINE_CD          as ONLINE_CD ";
    $sql .= "     ,regexp_replace (replace(replace(coalesce(M_CHOALL.ONLINE_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as ONLINE_KN ";
    $sql .= "     ,SUPP_CD       ";
    $sql .= "     ,DISTRICT_CODE ";
    $sql .= "     ,SCHEMA_NM     ";
    $sql .= "from public.m_choall             ";
    $sql .= "where                            ";
    $sql .= "     order_kbn = '1'             ";
    $sql .= "     and order_format_type = '3' ";
    $sql .= "     and schema_nm         <> '' ";
    $sql .= "order by ";
    $sql .= "       online_cd         ";
    //  $sql .= "      ,base_firm_cust_cd ";
    // 一覧表を格納する空の配列宣言
    $result = [];
    //　SQL　実行
    $result = getList($sql);

    if (($result === false)) {
        return false;
    }

    return $result;
}

//**************************************************************************
//
// 機能     :発注データファイル出力
//
// 引数　　 :オンラインコード、発注データ
//
// 備考     :
//
//**************************************************************************

function PutFileOrderDat($IN_strOnline_Cd, $strOutBuf) {

    $strMe = 'PutFileOrderDat';

    if ($IN_strOnline_Cd === '') {
        return false;
    }
    // VAN発注ファイル保存パス
    $dir = "/var/www/as_400/order/".$IN_strOnline_Cd;
    //フォルダーが存在しない場合、ベンダー毎のフォルダーを作成
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    if ($strOutBuf === '') {
        return false;
    }

    $path = $dir;

    $strDatCnvFile = $path . '/' . $IN_strOnline_Cd . '.TXT';

    //ファイルオープン(追加)
    $fp = fopen($strDatCnvFile, "a");

    if ($fp) {
        if (flock($fp, LOCK_EX)) {
            if (fwrite($fp, $strOutBuf) === false) {
                // NOP
            }
            //ロック解除
            flock($fp, LOCK_UN);
        } else {
            
        }
        //ファイルクローズ
        fclose($fp);
    }
    if (file_exists($strDatCnvFile) === true) {
        chown($strDatCnvFile, 'apache');
        chgrp($strDatCnvFile, 'apache');
    }

    return true;
}

//**************************************************************************
//
// 機能     :発注データファイル出力
//
// 引数　　 :KICS店舗コード
//
// 備考     :
//
//**************************************************************************

function get_m_youbi($strBase_Firm_Cust_Cd) {

    $rows_youbi = [];

    $sql = "";
    $sql .= "select ";
    $sql .= " * ";
    $sql .= "from public.m_youbi ";
    $sql .= "where ";
    $sql .= " base_firm_cust_cd = " . "'" . $strBase_Firm_Cust_Cd . "'";

    //　SQL　実行
    $rows_youbi = getList($sql);

    if ($rows_youbi) {
        return $rows_youbi;
    }
}

//**************************************************************************
//
// 機能     :曜日を取得
//
// 引数　　 :発注日
//
// 備考     :
//
//**************************************************************************   

function get_weekday($arr_date) {

    $week = array("sun", "mon", "tue", "wed", "thu", "fri", "sat");
    $datetime = new DateTime($arr_date);
    $weekday = $week[$datetime->format("w")];

    return $weekday;
}

//**************************************************************************
//
// 機能     :伝票番号取得
//
// 引数　　　:KICS店舗コード
//
// 備考     :
//
//**************************************************************************

function get_m_denno($strBase_Firm_Cust_Cd) {
    $rows_denno = [];
    $sql = "";
    $sql .= "select ";
    $sql .= " denno +1 as denno   ";
    $sql .= " from public.m_denno ";
    $sql .= " where ";
    $sql .= " base_firm_cust_cd = " . "'" . $strBase_Firm_Cust_Cd . "'";
    //　SQL　実行
    $rows_denno = getList($sql);

    foreach ($rows_denno as $row_denno => $val) {
        return $val['denno'];
    }
}

//**************************************************************************
//
// 機能     　:システムマスタ詳細データ設定を取得
//
// 引数　　　 :スキーマ名、店舗コード
//
// 備考     　:
//
//**************************************************************************    

function strdata_set_kbn($schema, $org_id) {

    $rows_sys = [];
    $sql = " ";
    $sql .= " select ";
    $sql .= "     detail_cd ";
    $sql .= "     ,strvalue ";
    $sql .= " from " . $schema . ".mst0011 ";
    $sql .= " where         ";
    $sql .= "    organization_id = " . $org_id;
    $sql .= " and detail_cd in ('107100040S')";
    $sql .= " and reji_no = '01' ";

    $rows_sys = getList($sql);
    
    foreach($rows_sys as $rows_sy){
        $strdata_set_kbn = $rows_sy['strvalue'];
    }
    
    if ($strdata_set_kbn) {
        return $strdata_set_kbn;
    }
    
}

//**************************************************************************
//
// 機能     　:システムマスタ詳細原価送信対象区分を取得
//
// 引数　　　 :スキーマ名、店舗コード
//
// 備考     　:
//
//**************************************************************************    

function strcost_send_kbn($schema, $org_id) {

    $rows_sys = [];
    $sql = " ";
    $sql .= " select ";
    $sql .= "     detail_cd ";
    $sql .= "     ,strvalue ";
    $sql .= " from " . $schema . ".mst0011 ";
    $sql .= " where         ";
    $sql .= "    organization_id = " . $org_id;
    $sql .= " and detail_cd in ('107100010S')";//,'107100010S','107100020S') ";
    $sql .= " and reji_no = '01' ";

    $rows_sys = getList($sql);
    
    foreach($rows_sys as $rows_sy){
        $strcost_send_kbn = $rows_sy['strvalue'];
    }
    
    if ($strcost_send_kbn) {
        return $strcost_send_kbn;
    }
    
}        
//**************************************************************************
//
// 機能     　:システムマスタ詳細原価送信対象区分を取得
//
// 引数　　　 :スキーマ名、店舗コード
//
// 備考     　:
//
//**************************************************************************      
function strsale_send_kbn($schema, $org_id) {

    $rows_sys = [];
    $sql = " ";
    $sql .= " select ";
    $sql .= "     detail_cd ";
    $sql .= "     ,strvalue ";
    $sql .= " from " . $schema . ".mst0011 ";
    $sql .= " where         ";
    $sql .= "    organization_id = " . $org_id;
    $sql .= " and detail_cd in ('107100020S')";
    $sql .= " and reji_no = '01' ";

    $rows_sys = getList($sql);
    
    foreach($rows_sys as $rows_sy){
        $strsale_send_kbn = $rows_sy['strvalue'];
    }
    
    if ($strsale_send_kbn) {
        return $strsale_send_kbn;
    }
    
}               

//**************************************************************************
//
// 機能     :伝票番号を更新
//
// 引数　　 :KICS店舗コード、シーケンス番号
//
// 備考     :
//
//**************************************************************************
function update_m_denno($strBase_Firm_Cust_Cd, $strSeq) {

    $sql = "";
    $sql .= " update public.m_denno set ";
    $sql .= " denno =" . $strSeq;
    $sql .= " where base_firm_cust_cd =" . "'" . $strBase_Firm_Cust_Cd . "'";

    $result = sqlExec($sql);
}

//**************************************************************************
//
// 機能     　:発注明細を更新
//
// 引数　　　 :スキーマ名、店舗コード、レコード番号、伝票番号
//
// 備考    　 :
//
//**************************************************************************

function update_trn1602($schema, $hideseq, $org_id, $line_no, $intProcDetailCnt, 
                        $strSeq, $ord_amount) {


    $sql = "";
    $sql .= " update " . $schema . ".TRN1602 set                          ";
    $sql .= "       UPDUSER_CD              = 'MPORTAL'                   "; // 更新者コード
    $sql .= "      ,UPDDATETIME             =  now()                      "; // 更新日時
    $sql .= "      ,SUPP_STATE_KBN          = '2'                         "; // 仕入状態区分を発注済みにする
    $sql .= "      ,EOS_DENNO               = " . "'" . $strSeq . "'      "; // EOS伝票番号
    $sql .= "      ,EOS_LINE_NO             = " . $intProcDetailCnt        ; // EOS伝票行番号
    $sql .= "      ,EOS_RCV_DATE            = " . date('YmdHis')           ; // EOS受信日時
    $sql .= "      ,EOS_RCV_ERR_CD          = ''                          "; // EOS受信エラーコード
    $sql .= "      ,EOS_RCV_BEF_AMOUNT      = " . "'" . $ord_amount . "'  "; // EOS受信前発注数量
    $sql .= " where TRN1602.ORGANIZATION_ID = " . $org_id                  ; // 店舗コード        
    $sql .= " and   TRN1602.HIDESEQ         = " . $hideseq                 ; // レコード番号
    $sql .= " and   TRN1602.LINE_NO         = " . $line_no                 ; // 行番号

    
    $result = sqlExec($sql);
    
}

//**************************************************************************
//
// 機能     　: 発注伝票を更新
//
// 引数　　　 : スキーマ名、レコード番号、店舗コード
//
// 備考    　 :
//
//**************************************************************************

function update_trn1601($schema, $hideseq, $org_id) {

    $query = "";
    $query .= " update " . $schema . ".TRN1601 set                           ";
    $query .= "        UPDUSER_CD             = 'MPORTAL'                    "; // 更新者コード
    $query .= "       ,UPDDATETIME            = now()                        "; // 更新日時
    $query .= "       ,CONNECT_KBN            = '1'                          "; // VANデータ作成区分
    $query .= "       ,VAN_DATA_KBN           = '1'                          "; // VANデータ作成区分
    $query .= "       ,VAN_DATA_DATE          = " . "'" . date('YmdHis') . "'"; // VANデータ作成日時
    $query .= " where  TRN1601.ORGANIZATION_ID = " . $org_id; // 店舗コード        
    $query .= " and    TRN1601.HIDESEQ         = " . $hideseq; // レコード番号

    $result = sqlExec($query);
}

//**************************************************************************
//
// 機能     :豊興用のベンダーを取得
//
// 引数     :KICS店舗コード
//
// 備考     :(@)パラメータは豊興様のコードを固定設定(131491)
//
//**************************************************************************

function get_houkou_list($base_firm_cust_cd) {

    $rows_houkou = [];

    $sql = "";
    $sql .= " select                  ";
    $sql .= " online_cd               ";
    $sql .= " from public.m_choall    ";
    $sql .= " where                   ";
    $sql .= " base_firm_cust_cd = " . "'" . $base_firm_cust_cd . "'";
    $sql .= " and order_format_type = '1' ";
    $sql .= " group by online_cd      ";
    $sql .= " order by online_cd      ";
    //　SQL　実行
    $rows_houkou = getList($sql);

    return $rows_houkou;
}

//**************************************************************************
//
// 機能     :特売マスタ
//
// 引数　　　:スキーマ名、商品コード、発注日
//
// 備考     :
//
//**************************************************************************
// DLTSTR 2021/11/11 bhattarai 特売マスタをみない
/*function get_m_tokubai($schema, $prod_cd, $ord_date) {

    $rows_tokubai = [];

    $C_ord_date = str_replace("/", "", $ord_date);

    $sql = "";
    $sql .= " select                           ";
    $sql .= "       prod_cd                    ";
    $sql .= "      ,sale_str_dt1               ";
    $sql .= "      ,sale_end_dt1               ";
    $sql .= "      ,costprice1                 ";
    $sql .= "      ,saleprice1                 ";
    $sql .= " from " . $schema . ".mst1303     ";
    $sql .= " where                            ";
    $sql .= " prod_cd = " . "'" . $prod_cd . "'";
    $sql .= " and sale_str_dt1 <= " . "'" . $C_ord_date . "'";
    $sql .= " and sale_end_dt1 >= " . "'" . $C_ord_date . "'";

    //　SQL実行
    $rows_tokubai = getList($sql);

    foreach ($rows_tokubai as $row_tokubai) {

        return $row_tokubai;
    }
}*/
// DLTSTR 2021/11/11 bhattarai 特売マスタをみない
function get_m_tokubai($schema, $prod_cd, $org_id) {

    $rows_tokubai = [];

    $C_ord_date = str_replace("/", "", $ord_date);

    $sql = "";
    $sql .= " select                          ";
    $sql .= "       prod_cd                   ";
    $sql .= "      ,smp1_str_dt               ";
    $sql .= "      ,smp1_end_dt               ";
    $sql .= "      ,smp1_costprice            ";
    $sql .= "      ,smp1_saleprice            ";
    $sql .= "      ,smp2_str_dt               ";
    $sql .= "      ,smp2_end_dt               ";
    $sql .= "      ,smp2_costprice            ";
    $sql .= "      ,smp2_saleprice            ";
    $sql .= " from " . $schema . ".mst0201    ";
    $sql .= " where                           ";
    $sql .= " prod_cd = " . "'" . $prod_cd . "'";
    $sql .= " and organization_id = " . $org_id;
    
    //　SQL実行
    $rows_tokubai    = getList($sql);
    
    // 初期化する
    $smp1_str_dt     = '';
    $smp1_end_dt     = '';
    $smp1_costprice  = 0 ;
    $smp1_saleprice  = 0 ;
    $smp2_str_dt     = '';
    $smp2_end_dt     = '';
    $smp2_costprice  = 0 ;
    $smp2_saleprice  = 0 ;
    $tokubai         = [];
    // ループ開始
    foreach ($rows_tokubai as $row_tokubai) {
        // 簡易特売1-開始日を取得
        $smp1_str_dt     = $row_tokubai['smp1_str_dt'];
        // 簡易特売1-終了日を取得
        $smp1_end_dt     = $row_tokubai['smp1_end_dt'];
        // 簡易特売2-開始日を取得
        $smp2_str_dt     = $row_tokubai['smp2_str_dt'];
        // 簡易特売2-終了日を取得
        $smp2_end_dt     = $row_tokubai['smp2_end_dt'];
        // 発注日が簡易特売1-開始日より大きい場合
        // 発注日が簡易特売2-終了日より小さい場合
        // 特売簡易特売1-商品原価と簡易特売1-商品売価を取得する
        if ($smp1_str_dt <= $C_ord_date && $smp1_end_dt >= $C_ord_date){
            // 簡易特売1-商品原価
            $smp1_costprice  = $row_tokubai['smp1_costprice'] ;
            // 簡易特売1-商品売価
            $smp1_saleprice  = $row_tokubai['smp1_saleprice'];
        }else{
            // 簡易特売1-商品原価
            $smp1_costprice  = 0 ;
            // 簡易特売1-商品売価
            $smp1_saleprice  = 0;
        }
        // 発注日が簡易特売2-開始日より大きい場合
        // 発注日が簡易特売2-終了日より小さい場合
        // 特売簡易特売2-商品原価と簡易特売2-商品売価を取得する
        if ($smp2_str_dt <= $C_ord_date && $smp2_end_dt >= $C_ord_date){
            // 簡易特売2-商品原価
            $smp2_costprice  = $row_tokubai['smp2_costprice'] ;
            // 簡易特売2-商品売価
            $smp2_saleprice  = $row_tokubai['smp2_saleprice'];
        }else{
            // 簡易特売2-商品原価
            $smp2_costprice  = 0;
            // 簡易特売2-商品売価
            $smp2_saleprice  = 0;   
        }
        // 簡易特売1-商品原価より簡易特売1-商品売価が小さい場合
        if($smp1_costprice < $smp2_costprice){
            $tokubai['costprice1'] = $smp1_costprice;
        }else{
            $tokubai['costprice1'] = $smp2_costprice;
        }
        // 簡易特売2-商品原価より簡易特売2-商品売価が小さい場合
        if($smp1_saleprice < $smp2_saleprice){
            $tokubai['saleprice1'] = $smp1_saleprice;
        }else{
            $tokubai['saleprice1'] = $smp2_saleprice;
        }
        return $tokubai;
    }
}
 
// 
//**************************************************************************
//
// 機能     :発注日マスタ
//
// 引数　　　:本日＝1
//
// 備考     :当日発注可能な店舗を取得
//
//**************************************************************************
function get_mht_youbi($today) {

    $rows_mht_youbi = [];

    $sql = "";
    $sql .= " select ";
    $sql .= "     base_firm_cust_cd ";
    $sql .= " from public.mht_youbi ";
    $sql .= " where                 ";
    $sql .= "".($today) ."= '1'     ";
    //　SQL実行
    $rows_mht_youbi = getList($sql);
    // 発注日を返す
    return $rows_mht_youbi;
    
}

?>
