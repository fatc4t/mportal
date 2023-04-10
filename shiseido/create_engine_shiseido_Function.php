<?php
    //**************************************************
    //
    // 機能     :資生堂ファイル出力共通関数
    //
    // 機能説明 :共通関数
    //
    // 備考     :AS400ダウンサイジング
    //
    // 作成日　：2021/03/24　　　　　　 作成者　：バッタライ
    // 
    // 修正日　：　　　　　　　　　　　　修正者　：
    // 　　　　　　　　
    //**************************************************
    require_once("DBAccess_Function.php");
    
    function db2str($IN_dbRow, $IN_strField, $IN_intLength) {

//        return str_pad(trim(substr(mb_convert_encoding(strval($IN_dbRow[$IN_strField]),"SJIS","UTF-8"), 0, $IN_intLength)), $IN_intLength);
        return str_pad(trim(mb_strcut(mb_convert_encoding(strval($IN_dbRow[$IN_strField]),"SJIS","UTF-8"), 0, $IN_intLength)), $IN_intLength);

    }

    function db2strL($IN_dbRow, $IN_strField, $IN_intLength) {

//        return str_pad(trim(substr(mb_convert_encoding(strval($IN_dbRow[$IN_strField]),"SJIS","UTF-8"), 0, $IN_intLength)), $IN_intLength, 0 ,STR_PAD_LEFT);
        return str_pad(trim(mb_strcut(mb_convert_encoding(strval($IN_dbRow[$IN_strField]),"SJIS","UTF-8"), 0, $IN_intLength)), $IN_intLength, 0 ,STR_PAD_LEFT);
    }

    //**************************************************
    //
    // 機能     :資生堂データファイル出力
    //
    // 機能説明 :資生堂連携されてる全スキーマを取得する
    //
    // 備考     :AS400ダウンサイジング
    //
    //**************************************************
    function getschema(){
        
        $result = [];
        $sql  = "";
        $sql .= "select ";
        $sql .= "      schema_nm       as schema_nm ";
        $sql .= "     ,ssd_code        as shop_cd   ";
        $sql .= "     ,organization_id as org_id    ";
        $sql .= "from public.m_shiseido_shop        ";
        $sql .= "order by shop_cd                   ";
        //sql実行
        $result = getList($sql);
        return $result;
    }
    //**************************************************
    //
    // 機能     :資生堂データファイル出力
    //
    // 機能説明 :
    //
    // 備考     :AS400ダウンサイジング
    //
    //**************************************************
    function PutFileDat($file_name,$IN_strBuf) {

        $strMe = 'PutFileDat';

        //資生堂ファイル保存パス
        $path = "/var/www/shiseido/send/";
//        $path = "C:\\var\www\shiseido\send/";
       
        //ファイル名
        $strDatCnvFile = $path.$file_name;
        //ファイルオープン(追加）       
        $fp = fopen($strDatCnvFile, "a");
        if ($fp) {
            if (flock($fp, LOCK_EX)) {
                if (fwrite($fp, $IN_strBuf) === false) {
                }
                //ロック解除
                flock($fp, LOCK_UN);
            }else{
                //nop
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
    // 機能     :資生堂データファイル読み込み
    //
    // 機能説明 :
    //
    // 備考     :AS400ダウンサイジング
    //
    //************************************************************************** 
    function getFileDat($file_name) {

        $strMe = 'getFileDat';

        //資生堂ファイル保存パス
        $path = "/var/www/batch/import/0009_shiseido/SYOHINMT/";
        //ファイルパスとファイル名
        $strDatCnvFile = $path.$file_name;
        //ファイルオープン(追加）       
        $fp = fopen($strDatCnvFile, "r");
        if($fp){
            if(flock($fp, LOCK_SH)){
                while((!feof($fp))){
                    //ファイルを取得
                    $buffer = fgets($fp);
                    $buffer1 = mb_convert_encoding($buffer,"UTF8","SJIS");
                }
            }
        }        
        fclose($fp); 

        $des_path = '/var/www/batch/import/0009_shiseido/SYOHINMT_BAK/KICS_'.date('ymd').'.SYOHINMT';
        rename($path.$file_name, $des_path);
        
        return $buffer1;
     
    }
    
    //**************************************************
    //
    // 機能     :資生堂顧客ID付き売上象データ取得
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //************************************************** 
        function getdata($schema,$org_id){
            
            // 本日を取得（ｙｙｙｙ-ｍｍ-ｄｄ）
            $Dt_today   = date('Y-m-d');
            // 前日（ｙｙｙｙ-ｍｍ-ｄｄ）
            $Dt_yesterday = date('Y-m-d', strtotime($Dt_today .'-1 day'));
            // 前日（ｙｙｙｙｍｍｄｄ）
            $proc_date = str_replace("-", "", $Dt_yesterday);
            
            //$proc_date ='20211004';
            //　sqlを開始        
            $sql  = "";
            $sql .= " select ";
            $sql .= "       t1.reji_no         as reji_no ";
            $sql .= "      ,t1.proc_date       as proc_date ";
            $sql .= "      ,t1.trntime         as trn_time ";
            $sql .= "      ,t1.account_no      as account_no ";
            $sql .= "      ,t1.lump_return_kbn as return_kbn ";
            $sql .= "      ,t2.line_no         as line_no ";
            $sql .= "      ,t2.prod_cd         as prod_cd ";
            $sql .= "      ,t2.amount          as amount ";
            $sql .= "      ,t2.pure_total      as pure_total ";
            $sql .= "      ,case  ";
            $sql .= "         when m1.cust_res_cd1 <> '' then 1";
            $sql .= "         else 0 end      as member_kbn ";
            $sql .= "      ,m1.cust_cd        as cust_cd    ";
            $sql .= "      ,m1.cust_res_cd1   as cust_res_cd1 ";
            $sql .= " from ".$schema.".trn0101 t1 ";
            $sql .= " inner join ".$schema.".trn0102 t2 on (t1.organization_id = t2.organization_id and t1.hideseq = t2.hideseq) ";
            $sql .= " left join ".$schema.".mst0101 m1 on (t1.cust_cd = m1.cust_cd) ";
            $sql .= " inner join public.m_shiseido_prod m2 on (t2.prod_cd = m2.prod_cd) ";
//            $sql .= "where m1.cust_res_cd1  <> ''  ";// 資生堂商品じゃないデータは取得しない
//            $sql .= "and t1.return_hideseq  =   0  ";// 返品分は取得しない
            $sql .= " where t1.stop_kbn        <> '1' ";// 取引中止は取得しない
            $sql .= " and t2.cancel_kbn = '0'"; // 直前取消は取得しない
            $sql .= " and t1.proc_date       = "."'".$proc_date."'";// 前日営業日分のみ取得
            $sql .= " and t1.organization_id = ".$org_id;// 対象店舗のみ取得
            $sql .= " order by proc_date, trn_time ";
           //sql実行
            $result = getList($sql);

            return $result;
        }
        
    //**************************************************
    //
    // 機能     :資生堂顧客ID付き顧客属性データ取得
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //************************************************** 
        function getcustdata($schema){
            
            // 本日を取得（ｙｙｙｙ-ｍｍ-ｄｄ）
            $Dt_today   = date('Y-m-d');
            // 前日（ｙｙｙｙ-ｍｍ-ｄｄ）
            $Dt_yesterday = date('Y-m-d', strtotime($Dt_today .'-1 day'));
            // 前日（ｙｙｙｙｍｍｄｄ）
            $proc_date = str_replace("-", "", $Dt_yesterday);
            
            //$proc_date ='20211007';

            $sql  = "";
            $sql .= "select ";
            $sql .= "       m1.cust_res_cd1  as cust_res_cd1 ";
            $sql .= "       ,m1.birth        as birth ";
            $sql .= "       ,m1.cust_kn      as cust_kn ";
            $sql .= "       ,m1.cust_nm      as cust_nm ";
            $sql .= "       ,m1.sex          as sex ";
            $sql .= "       ,m1.addr1        as addr1 ";
            $sql .= "       ,m1.addr2        as addr2 ";
            $sql .= "       ,m1.addr3        as addr3 ";
            $sql .= "       ,replace(m1.zip,'-','')  as zip";
            $sql .= "       ,m1.tel          as tel ";
            $sql .= "       ,m1.hphone       as hphone ";
            $sql .= "       ,m1.dissenddm    as dissenddm";
            $sql .= "       ,m1.email        as email ";
            $sql .= "       ,m1.dissendemail as dissendemail ";
            $sql .= "       ,m1.upddatetime as upddatetime ";
            $sql .= "from ".$schema.".mst0101 m1 ";
            $sql .= "where SUBSTRING(m1.cust_res_cd1,1,3) = '200' ";
            $sql .= "  and date(m1.upddatetime) = '".$proc_date."'";// 前日営業日分のみ取得
            $sql .= " order by m1.cust_res_cd1";

            //sql実行
            $result = getList($sql);

            return $result;
        }    
        
?>
