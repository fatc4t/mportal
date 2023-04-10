<?php
/******************************************************************************-*
  '*
  '*  ユーザー名  :
  '*
  '*  システム名  :MPORTAL - NSDVAN
  '*
  '*  処理名      :単価データを読む　MPROTALに更新
  '*
  '*  処理概要    :
  '*
  '*  開発言語    :PHP
  '*
  '*  
  '*  作成者      :K(2022/12)
  '*
  '*------------------------------------------------------------------------------
  '*  修正履歴
  '*  修正日        修正者            修正内容
  '*  
  '*
  '******************************************************************************/


try {
    
    require_once("nsdban_Functions.php");
	
	readTANKADatafile(); //タイミングの為にファイルを分ける


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
