<?php
    /**
     * @file      祝日リスト更新
     * @author    USE Y.Sakata
     * @date      2016/08/03
     * @version   1.00
     * @note      googleから取得した祝日リストをデータベースへ登録
     */

    // パラメータファイルの設定(バッチ[クーロン]からの起動する為、絶対パスで指定)
    require_once '/var/www/local_security/mportal/attendance/SystemParameters.php';
    // パラメータファイルの設定(バッチ[クーロン]からの起動する為、絶対パスで指定)
    require_once '/var/www/mportal/FwCommon/FwBaseClass.php';

    // 日本の祝日を設定するクラス
    require_once '/var/www/mportal/attendance/Model/JapanHoliday.php';

    // 日本の祝日クラスをインスタンス化
    $japanHoliday = new JapanHoliday();
    
    // googleから祝日情報を取得し、DBへ格納
    $japanHoliday->modJapanHoliday();
?>

