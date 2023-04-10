<?php
/******************************************************************************-*
  '*
  '*  ユーザー名  :
  '*
  '*  システム名  :MPORTAL
  '*
  '*  処理名      :送信処理-発注データ（KICS版）
  '*
  '*  処理概要    :
  '*
  '*  開発言語    :PHP
  '*
  '*  作成日      :2021/02/20
  '*  作成者      :バッタライ, K(2022/12)
  '*
  '*------------------------------------------------------------------------------
  '*  修正履歴
  '*  修正日        修正者            修正内容
  '*  
  '*
  '******************************************************************************/



try {
    
    require_once("nsdban_Functions.php");
	

    //日本の時間を設定
    date_default_timezone_set('Asia/Tokyo');
    // 現在の年月日時分秒を取得(スラシュあり)
    $currentdatetime = date('Y/m/d h:i:s');
    // 現在の年月日時分秒を取得(スラシュ無し)
    $currentdate = date('ymdHis');

    //--------KARL Month and Day
    $currentMonth   =date('m');
    $currentDay     =date('d');

    // 発注データ集計フォーマット
    $format = "KICS";
    // ログファイル名
    $logname = $format . "_" . $currentdate;
    // ログファイルパス
    $log_path     = "/var/www/nsdban/log/".$logname.'.log';
    // 伝票番号ログパス
    $den_logname = '/var/www/nsdban/log/'.$currentdate.'_den_no.log';

    // 空白
    $space = str_pad('',5);

    // 処理開始ログ出力
    error_log($currentdatetime . $space .
            $format . $space .
            "開始" . "\n", 3, $log_path);

    //**************************************************
    //
    // 機能     :発注データレコード生成
    //
    // 機能説明 :
    //
    // 備考     :as_ダウンサイジング
    //
    //**************************************************

    function CreateOrderRec_std($IN_aryDataBuf, &$OUT_strOutBuf) {
        //print_r("------inside CREATEORDERREC");
        $strMe         = 'CreateOrderRec_std';
        $OUT_strOutBuf = '';
        $strRecLine    = '';
        $intPos        = 0;
        foreach ($IN_aryDataBuf as $strDataRow) {
            $strRecLine .= $strDataRow;
            $OUT_strOutBuf .= $strRecLine;
            $strRecLine = '';
            if (strlen($strRecLine) === 30) {
                    $OUT_strOutBuf .= $strRecLine;
                    $strRecLine = '';
                }
            $intPos ++; 

        }
        //明細数が1または3～5の場合は256桁に満たないのでスペース埋め
        if (strlen($strRecLine) > 1 && strlen($strRecLine) < 256) {
            $strRecLine = str_pad($strRecLine, 256);
            $OUT_strOutBuf .= $strRecLine;

        }

        //------------------------HINT-------------------------
        // use str__pad(input, number of spaces)

    }

    //**************************************************
    //
    // 機能     :EOS発注使用しているスキーマのみ取得 
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************
    

    $sql_schema = "";
    $sql_schema .= "SELECT                          ";
    $sql_schema .= "     COMPANY_CODE               ";
    $sql_schema .= "FROM PUBLIC.M_COMPANY_CONTRACT  ";
    $sql_schema .= "WHERE NSD_SHOP_CD = '102'       ";//-----------------------------NSDVAN
    //$sql_schema .= "WHERE EOS_KBN = '1'             ";
    
    //LOCAL TEST ONLY------------------------------
    //$sql_schema .= "AND COMPANY_CODE = 'ikkou'      ";
    //LOCAL TEST ONLY------------------------------

    $rows_schemas = getList($sql_schema);

   print_r($rows_schemas);exit;
    
    // 企業名初期化
    $schema = "";

    // 企業ループ開始
   foreach ($rows_schemas as $schemas) {
        // DBスキーマ名
        $schema = $schemas['company_code'];
        

        $juSQL = "";
        //-------------------------------受注のデータ-----------------------------------------DONE
        $juSQL .="select a.hideseq as hideseq,";  
        $juSQL .="a.organization_id as organization_id,";  
        $juSQL .="b.line_no as line_no,";
        $juSQL .="'102' as tenpo_cd,";                      //default to 102 from email
        $juSQL .="'A' as data_kbn,";                        //default to "A"
        $juSQL .="0 as shori_kbn,";                         //ord_amount <= 0 '5=返品'になる　else '0'----DONE
        $juSQL .="nextval('public.shori_ban_seq')  as shori_cd,";               //setup in Database MPORTAL PUBLIC (public.shori_ban_seq)
        $juSQL .="b.prod_cd as prod_cd,";                   //商品コード
        $juSQL .="round(b.ord_amount) as ord_amount,";      //数量-----DECIMAL 消す(round)
        $juSQL .=$currentMonth." as month, ";               //今の月
        $juSQL .=$currentDay." as day ";                    //今の曜日
        $juSQL .="from ".$schema.".TRN1601 a inner join ".$schema.".TRN1602 b on ";
        $juSQL .="a.organization_id=b.organization_id and a.hideseq=b.hideseq ";
        $juSQL .="where a.order_kbn='1' ";                   // EOS発注
        $juSQL .="and a.van_data_kbn='1' ";                  // 送信済 ///<-------0 ならダメ
        $juSQL .="and a.connect_kbn='0' ";                   // 未送信 ///<-------change to 1(after running)
        $juSQL .="order by b.prod_cd ";


        // $sql .= "where TRN1601.ORDER_KBN             = '1' ";// EOS発注
        // $sql .= "   and   TRN1601.VAN_DATA_KBN       = '1' ";// 送信済 ///<-------0 ならダメ
        // $sql .= "   and   TRN1601.CONNECT_KBN        = '0' ";// 未送信 ///<-------change to 1(after)

        // 発注データ取得SQL実行
        $rows_import = getList($juSQL); //受注のデータ
        
        
        
        // -------------------------対象のデータがない場合、ログ出力、-----------------------------------------
        if (count($rows_import) <= 0) {
            error_log($currentdatetime . $space .
                    $format . $space . $schema .
                    $space . "受注データが存在しません" . "\n", 3, $log_path);
            continue;    
        }

        //データ設定-------------------------------------------------------
        if ($rows_import) {
            
            //VAR READY----------------------------------------------------------
            // データ格納配列初期化
            $aryDataBuf               = array();
            // 伝票番号初期化
            $strSeq                   = 0;
            // レコード番号 CLEAR
            $hideseq                  = "";
            // MPORTAL店舗ID
            $org_id                   = "";
            // 行番号
            $line_no                  = "";
            $ordAMT                   = "";
            

            
            /* ループ開始 */
            foreach ($rows_import as $row_import) {
                

                // 出力バッファ初期化
                $strOutBuf = '';
                $tenpoCD =db2str($row_import, 'tenpo_cd', 3);
                $ordAMT =$row_import['ord_amount'];
                $mm =$row_import['month'];
                $dd =$row_import['day'];
                
                // レコード番号
                $hideseq                = $row_import['hideseq'];
                // MPORTAL店舗ID
                $org_id                 = $row_import['organization_id']; 

                // 行番号
                $line_no                = $row_import['line_no'];

           
                $strOutBuf      .=str_pad($tenpoCD,3);                                   //----------3桁     
                $strOutBuf      .=db2str($row_import, 'data_kbn', 1);                    //----------1桁 'A'

                //----------1桁 if ord_amount<0 : 5になる
                if($ordAMT<0){
                    $strOutBuf  .=str_pad('5', 1);
                }else{
                    $strOutBuf  .=$row_import['shori_kbn'];                           
                }
                
                $strOutBuf      .=db2str($row_import, 'shori_cd', 4);                    //----------4桁  shori_ban_seq (SEQUENCE)
                $strOutBuf      .=$row_import['prod_cd'];                                //----------13桁 
                //$strOutBuf      .=str_pad($ordAMT,4);
                $strOutBuf      .=sprintf("%04d", $ordAMT);                                    //----------4桁 
                //$strOutBuf      .=str_pad($mm,2);                                        //----------2桁 
                //$strOutBuf      .=str_pad($dd,2);                                        //----------2桁 
                $strOutBuf      .=sprintf("%02d",$mm);                                        //----------2桁 
                $strOutBuf      .=sprintf("%02d",$dd);                                        //----------2桁 
                //$strOutBuf    .=str_pad('', 6);

                //print_r($strOutBuf."<br>");
                //$hideseq                = $row_import['hideseq'];
                
                // 明細バッファ設定
                $aryDataBuf[] = $strOutBuf;

                writeJUCHUfile($strOutBuf);   //----------------------------------DONE

                update_nsdban_trn1602($schema, $hideseq, $org_id, $line_no, $ordAMT);
                update_nsdban_trn1601($schema, $hideseq, $org_id); 




                /*------------------------------------------------OLD DATA------------------------------------------
                // 発注明細を更新
                //update_trn1602($schema, $hideseq, $org_id, $line_no, $intProcDetailCnt, $strSeq, $ord_amount)                
                // 発注伝票を更新
                //update_trn1601($schema, $hideseq, $org_id, $strSeq); */

            }    // ループ終了       

        }


        print_r("--END");

    }   // ループ終了


  



} catch (Exception $ex) {
    // 処理中にエラーが発生した場合、ログファイル出力
    error_log($currentdatetime . $space . 
              $format . $space . 
              "処理中にエラーが発生しました" . "\n"."エラー内容：".$ex, 3, $log_path);
}
    // 処理完了ログ出力
    error_log($currentdatetime . $space .
            $format . $space .
            "完了" . "\n", 3, $log_path);

    // error_log('END', 3, '/var/www/as_400/order/KICS.dat');
    error_log('END', 3, '/var/www/nsdban/log/KICS.dat');

    @ob_flush();
    @flush();
?>
