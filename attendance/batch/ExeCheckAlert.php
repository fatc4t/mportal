<?php
    /**
     * @file      日々の打刻漏れ、アラートチェック
     * @author    USE Y.Sakata
     * @date      2016/10/25
     * @version   1.00
     * @note      日々の打刻漏れ、アラートチェック(組織単位)
     */

    // パラメータファイルの設定(バッチ[クーロン]からの起動する為、絶対パスで指定)
    require_once '/var/www/local_security/mportal/attendance/SystemParameters.php';
    // パラメータファイルの設定(バッチ[クーロン]からの起動する為、絶対パスで指定)
    require_once '/var/www/mportal/FwCommon/FwBaseClass.php';

    // 日々アラートチェック処理クラス
    require_once '/var/www/mportal/attendance/Controllers/ExeCheckAlertController.php';

    // 日々アラートチェック処理クラスをインスタンス化
    $checkAlert = new ExeCheckAlertController();

    // 日々アラートチェックを実行
    $checkAlert->sendCheckAlertMail();
?>

