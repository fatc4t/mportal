<?php
    /**
     * @file      薬剤発注管理振り分けクラス
     * @author    USE K.Kazuya (media-craft.co.jp)
     * @date      2018/01/22
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    // システムパラメータファイル
    require_once '../../local_security/mportal/order/SystemParameters.php';
    // メインコントローラ(index.phpからのみ参照)
    require_once SystemParameters::$FW_COMMON_PATH . 'Dispatcher.php';

    // 薬剤発注管理システムの振分け処理クラス
    class OrderDispatcher extends Dispatcher
    {

    }
?>
