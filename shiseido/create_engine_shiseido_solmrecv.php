<?php
/* * *****************************************************************************
  '*
  '*  ユーザー名  :
  '*
  '*  システム名  :MPORTAL
  '*
  '*  処理名      :資生堂データ変換ファイル出力
  '*
  '*  処理概要    :資生堂顧客ID付き顧客属性
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
  '******************************************************************************/
try{
    require_once("create_engine_shiseido_Function.php");
    //日本の時間を設定
    date_default_timezone_set('Asia/Tokyo');
    // 現在の年月日時分秒を取得(スラシュあり)
    $currentdatetime = date('Y/m/d h:i:s');
    // 現在の年月日時分秒を取得(スラシュ無し)
    $currentdate = date('ymdHis');
    // 発注データ集計フォーマット
    $format = "SOLMRECV";
    // ログファイル名
    $logname = $format . "_" . $currentdate;
    // ログファイルパス
    $log_path = "/var/www/shiseido/log/" . $logname . '.log';
    // 空白
    $space = str_pad('', 5);

    // 処理開始ログ出力
    error_log($currentdatetime . $space .
            $format . $space .
            "開始" . "\n", 3, $log_path);
    //**************************************************
    //
    // 機能     :顧客属性データレコード生成
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************
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
            else if (substr($strDataRow, 0, 1) === 'M') {
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
            $intPos ++;
        }
        return true;
    } 
    // 資生堂連携されてる全スキーマを取得する
    $schemas = getschema();
    // ループ開始
    foreach ($schemas as $company){
        $schema  = $company['schema_nm'];
        $org_id  = $company['org_id'];
        $shop_cd = $company['shop_cd'];

        // 資生堂顧客ID付き顧客属性データ取得
        $rows_import = getcustdata($schema);
        //データ格納配列初期化
        $aryDataBuf = array();

        //データレコード（データ部）
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
                $strOutBuf .= 'SOLMRECV';
                //備考（125）空白
                $strOutBuf .= str_pad('', 295);   
                //スタートレコードバッファ設定
                $aryDataBuf[] = $strOutBuf;
            }

            //データ区分（2）固定
            $strOutBuf .= 'M1';
            //会社コード（13） global location number
            $strOutBuf .= '1882120000004';
            //会社名（8）
            $strOutBuf .= 'KICS    ';
            //（システム日付）年月日時分秒(14)        
//            $strOutBuf .= date('Ymdhis', strtotime('today'));
            // 更新日を加工
            $strOutBuf .=  substr(str_replace(' ','', str_replace(':','', str_replace('-','',str_replace('/','', $row_import['upddatetime'])))),0,14);
            //御社コード(13)
            //$strOutBuf .= db2str($row_import, 'shop_cd', 13);
            $strOutBuf .= str_pad($shop_cd, 13);
            //資生堂IDコード（13）
            $strOutBuf .= db2str($row_import, 'cust_res_cd1',13);
            //備考（30）
            $strOutBuf .= str_pad('', 20);
            //誕生日年月日（8）
            $birth = db2str($row_import, 'birth',8);
            if($birth === '        '){
                $strOutBuf .= '99999999';
            }else{
                $strOutBuf .= $birth;
            }
            //フリガナ（16）
            $strOutBuf .= db2str($row_import, 'cust_kn',16);
            //氏名(16)
            $strOutBuf .= db2str($row_import, 'cust_nm',16);
            //性別（1）
            $sex = db2str($row_import, 'sex',1);
            switch ($sex) {
                case 1:
                    $strOutBuf .= '1';//男
                    break;
                case 2:
                    $strOutBuf .= '2';//女
                    break;
                default:
                    $strOutBuf .= '9';//不明
                    break;
            }
            //住所1（24）
            $strOutBuf .= db2str($row_import, 'addr1',24);
            //住所2（24）
            $strOutBuf .= db2str($row_import, 'addr2',24); 
            //住所3（24）
            $strOutBuf .= db2str($row_import, 'addr3',24);   
            //郵便番号（7）
            $strOutBuf .= db2str($row_import, 'zip',7); 
            //電話番号自宅（20）
            $strOutBuf .= db2str($row_import, 'tel',20); 
            //携帯電話番号（20）
            $strOutBuf .= db2str($row_import, 'hphone',20); 
            //電話要不要(1)
            $strOutBuf .= db2str($row_import, 'dissenddm',1); 
            //メールアドレス(50)
            $strOutBuf .= db2str($row_import, 'email',50); 
            //店舗発メール要不要(1)
            $strOutBuf .= str_pad('', 1);
            //資生堂発メール要不要(1)
            $strOutBuf .= str_pad('', 1);
            //職業(1)
            $strOutBuf .= str_pad('', 1);
            //未既婚(1)
            $strOutBuf .= str_pad('', 1);
            //子供有無(1)
            $strOutBuf .= str_pad('', 1);
            //店ＤＭ要不要(1)
            $strOutBuf .= db2str($row_import, 'dissenddm',1); 
            //備考2（20）
            $strOutBuf .= str_pad('', 20);
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
    
    //エンドレコード
    //区分(E)固定
    $strOutBuf .= 'E';  
    //会社名（8）固定
    $strOutBuf .= 'KICS    ';
    //レコード件数（10）データ数
    $strOutBuf .= str_pad($lineCnt, 10);
    $strOutBuf .= $cntTmp;
    //備考（20）空白
    $strOutBuf .= str_pad('', 301);
    //ヘッダバッファ設定    
    $aryDataBuf[] = $strOutBuf;
    //ファイル名を指定
     $file_name = 'KICS.RV.SOLMRECV';
    //ループ脱出後
    //資生堂顧客ID付き顧客属性レコード生成
    CreateOrderRec($aryDataBuf,$strOutBuf);
    //資生堂顧客ID付き顧客属性データファイル出力
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
