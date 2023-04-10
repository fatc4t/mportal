<?php
    /**
     * @file      Web打刻時の勤怠テーブル更新
     * @author    USE Y.Sakata
     * @date      2016/08/17
     * @version   1.00
     * @note      Web打刻時に、勤怠テーブルを自動更新する
     */

    // パラメータファイルの設定(バッチ[クーロン]からの起動する為、絶対パスで指定)
    require_once '/var/www/local_security/mportal/attendance/SystemParameters.php';
    // パラメータファイルの設定(バッチ[クーロン]からの起動する為、絶対パスで指定)
    require_once '/var/www/mportal/FwCommon/FwBaseClass.php';

    // Web打刻処理クラス
    require_once '/var/www/mportal/attendance/Model/EmbossingWeb.php';

    // Web打刻処理クラスをインスタンス化
    $embossingWeb = new EmbossingWeb( 'testuse' );

    // 勤怠テーブル未格納の打刻データを、勤怠テーブルへ一括更新
    $embossingWeb->massUpdate();
?>

