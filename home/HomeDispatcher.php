<?php
    /**
     * @file      HOME画面振り分けクラス
     * @author    oota
     * @date      2016/06/24
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    // システムパラメータファイル
    require_once '../../local_security/mportal/home/SystemParameters.php';

    // メインコントローラ(index.phpからのみ参照)
    require_once SystemParameters::$FW_COMMON_PATH . 'Dispatcher.php';

    // HOME画面の振分け処理クラス
    class HomeDispatcher extends Dispatcher
    {

    }
?>
