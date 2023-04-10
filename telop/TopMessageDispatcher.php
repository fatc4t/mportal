<?php
    /**
     * @file      トップメッセージ振り分けクラス
     * @author    oota
     * @date      2017/01/26
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    // システムパラメータファイル
    require_once '../../local_security/mportal/topMessage/SystemParameters.php';
    // メインコントローラ(index.phpからのみ参照)
    require_once SystemParameters::$FW_COMMON_PATH . 'Dispatcher.php';

    // トップメッセージの振分け処理クラス
    class TopMessageDispatcher extends Dispatcher
    {

    }
?>
