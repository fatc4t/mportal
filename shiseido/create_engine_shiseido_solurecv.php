<?php
/*******************************************************************************
  '*
  '*  ユーザー名  :
  '*
  '*  システム名  :MPORTAL
  '*
  '*  処理名      :資生堂データ変換ファイル出力
  '*
  '*  処理概要    :顧客ID付き売上データ出力
  '*
  '*  開発言語    :PHP
  '*
  '*  作成日      :2021/08/20
  '*  作成者      :バッタライ
  '*
  '*----------------------------------------------------------------------------
  '*  修正履歴
  '*  修正日      修正者  修正内容
  '*
  '*
  '****************************************************************************/
try{
    require_once("create_engine_shiseido_Function.php");
    //日本の時間を設定
    date_default_timezone_set('Asia/Tokyo');
    // 現在の年月日時分秒を取得(スラシュあり)
    $currentdatetime = date('Y/m/d h:i:s');
    // 現在の年月日時分秒を取得(スラシュ無し)
    $currentdate = date('ymdHis');
    // 発注データ集計フォーマット
    $format = "SOLURECV";
    // ログファイル名
    $logname = $format . "_" . $currentdate;
    // ログファイルパス
    $log_path = "/var/www/shiseido/log/" . $logname . '.log';
    // 空白
    $space = str_pad('', 5);
    // 件数カウント
    $lineCnt = 0;

    // 処理開始ログ出力
    error_log($currentdatetime . $space .
            $format . $space .
            "開始" . "\n", 3, $log_path);
    //**************************************************************************
    //
    // 機能     :売上データレコード生成
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************************************
    function CreateOrderRec($IN_aryDataBuf,&$OUT_strOutBuf) {

        $strMe = 'CreateOrderRec';

        //$OUT_strOutBuf = '';
        $strRecLine = '';
        $intPos = 0;
        foreach ($IN_aryDataBuf as $strDataRow) {
            if (substr($strDataRow, 0, 1) === 'F') {
                //伝票ヘッダーレコード
                $strRecLine .= $strDataRow;
            }
            else if (substr($strDataRow, 0, 1) === 'S') {
                //伝票明細レコード

                if (($intPos % 2) === 0) {
                    $strRecLine .= substr($strDataRow,3);
                    $strRecLine .= str_pad('', 3);
                }
                else {
                    $strRecLine .= $strDataRow;
                }
            }
            else {
                //NOP
            }
        }
        return true;
    }
    // 資生堂連携されてる全スキーマを取得する
    $schemas     = getschema();
    $rows_import = [];
    $schema      = '';
    $org_id      = '';
    $shop_cd     = '';
    // ループ開始
    foreach ($schemas as $company){
        $schema      = $company['schema_nm'];
        $org_id      = $company['org_id'];
        $shop_cd     = $company['shop_cd'];   
        $rows_import = getdata($schema,$org_id);
        // 対象のデータがない場合、ログ出力し終了
        if (count($rows_import) <= 0) {

            error_log($currentdatetime . $space .
                    $format . $space .
                    $order_shop . $space .
                    "VAN発注データが存在しません" . "\n", 3, $log_path);
            continue;
        }
        //対象のデータがない場合、データを出力する
        if ($rows_import) {
            //データ格納配列初期化
            $aryDataBuf = array();
            // ループ開始
            foreach($rows_import as $row_import){
                $header = 'header';
                //　前回ヘッダーレコードが存在した場合作成しない
                if($Bef_header !== $header){
                    $strOutBuf = "";
                    //区分(1)固定
                    $strOutBuf .= 'F';  
                    //会社名（8）固定
                    $strOutBuf .= 'KICS    ';
                    //作成年月日(8)
                    $strOutBuf .= date('Ymd', strtotime('today'));
                    //ファイル種別
                    $strOutBuf .= 'SOLURECV';
                    //備考（125）空白
                    $strOutBuf .= str_pad('', 125);   
                    //スタートレコードバッファ設定
                    $aryDataBuf[] = $strOutBuf;
                }
                //データレコード（データ部）
               //$strOutBuf = "";
                //データ区分（2）固定
                $strOutBuf .= 'S2';
                //会社コード（13） global location number
                $strOutBuf .= '1882120000004';
                //会社名（8）
                $strOutBuf .= 'KICS    ';
                //POSオペレーション 年月日時分秒(14)        
//                $strOutBuf .= date('Ymdhis', strtotime('today'));
                $strOutBuf .= db2str($row_import, 'proc_date',8).db2str($row_import, 'trn_time',6);
                //予備(8)空白
                $strOutBuf .= str_pad('', 8);
                //御社コード
                $strOutBuf .= str_pad($shop_cd, 13);
                //レジNO（2）
                $strOutBuf .= db2strL($row_import, 'reji_no', 2);
                //売上日()年月日（8）
                $strOutBuf .= db2str($row_import, 'proc_date',8);
                //売上時間（6）時分秒
                $strOutBuf .= db2str($row_import, 'trn_time',6);
                //レシートNO
                $strOutBuf .= db2strL($row_import, 'account_no',4);
                //レシート内行番号（3）
                try {
                    $lineTmp = sprintf('%03d', floor(floatval($row_import['line_no'])));
                    if ($lineTmp === '000') {
                        $lineTmp = str_pad('', 3);
                    }
                }
                catch (Exception $e) {
                    $lineTmp = str_pad('', 3);
                }
                $strOutBuf .= $lineTmp;
                //JANコード（13）
                $strOutBuf .= db2strL($row_import, 'prod_cd',13);
                //売上数量（3）
                try {
                    $intTmp = sprintf('%03d', floor(floatval($row_import['amount'])));
                    if ($intTmp === '000') {
                        $intTmp = str_pad('', 3);
                    }
                }
                catch (Exception $e) {
                    $intTmp = str_pad('', 3);
                }
                $minus = mb_substr($intTmp,0,1);
                if($minus ==='-'){
                    $intTmp = str_replace('-',0, $intTmp);
                }
                $strOutBuf .= $intTmp;
                //実売価格（合計）（7）
                try {
                    $strTmp = sprintf('%07d', floor(floatval($row_import['pure_total'])));
                    if ($strTmp === '0000000') {
                        $strTmp = str_pad('', 7);
                    }
                }
                catch (Exception $e) {
                    $strTmp = str_pad('', 7);
                }
                $minus_strtmp = mb_substr($strTmp,0,1);
                if($minus_strtmp ==='-'){
                    $strTmp = str_replace('-',0, $strTmp);
                }
                $strOutBuf .= $strTmp;
                //返品区分（1）
                $strOutBuf .= db2str($row_import, 'return_kbn',1);
                //マイナス区分（1）
//                if($minus === '-'){
//                    $minus_kbn = 1;
//                }else{
//                    $minus_kbn = 0;
//                }
                // 一旦固定 millionet oota 20210830
                $minus_kbn = 0;
                $strOutBuf .= str_pad($minus_kbn, 1);
                //会員区分（1）
                $strOutBuf .= db2str($row_import, 'member_kbn',1);
                //資生堂IDコード（13）
                $strOutBuf .= db2str($row_import, 'cust_res_cd1',13);
                //備考（30）
                $strOutBuf .= db2str($row_import, '',30);
                //明細バッファ設定
                $aryDataBuf[] = $strOutBuf;
                //処理明細数インクリメント
                $intProcDetailCnt ++;
                //前回のヘッダーを保持
                $Bef_header = $header;

                // レコード件数を追加
                $lineCnt++;
            }
        }
    }
    //エンドレコード
    //区分(1)固定
    $strOutBuf .= 'E';  
    //会社名（8）固定
    $strOutBuf .= 'KICS    ';
    //レコード件数（10）データ数
    $strOutBuf .= str_pad($lineCnt, 10);
    //備考（20）空白
    $strOutBuf .= str_pad('', 131);
    //ヘッダバッファ設定    
    $aryDataBuf[] = $strOutBuf;
    //ファイル名を指定
    $file_name = 'KICS.RV.SOLURECV';
    //売上データレコード生成
    CreateOrderRec($aryDataBuf,$strOutBuf);
    //売上データファイル出力
    PutFileDat($file_name, $strOutBuf);
    
} catch (Exception $ex) {
    // 処理中にエラーが発生した場合はログファイル出力し、終了
    error_log($currentdatetime . $space .
            $format . $space .
            "処理中にエラーが発生しました" . "\n" . "エラー内容：" . $ex, 3, $log_path);
}    
// 処理完了ログ出力
error_log($currentdatetime . $space .
        $format . $space .
        "完了" . "\n", 3, $log_path);
skip:
@ob_flush();
@flush();
?>
