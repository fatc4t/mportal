<?php
    /**
     * @file      ログイン振り分けクラス
     * @author    oota
     * @date      2016/06/23
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    // システムパラメータファイル
    require_once '../../local_security/mportal/login/SystemParameters.php';
    // メインコントローラ(index.phpからのみ参照)
    require_once SystemParameters::$FW_COMMON_PATH . 'Dispatcher.php';

    // ログインの振分け処理クラス
    class LoginDispatcher extends Dispatcher
    {

    }
?>
