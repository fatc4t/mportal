<?php
    /**
     * @file      予約画面振り分けクラス
     * @author    oota
     * @date      2019/06/05
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    // システムパラメータファイル
    require_once '../../local_security/mportal/reserve/SystemParameters.php';

    // メインコントローラ(index.phpからのみ参照)
    require_once SystemParameters::$FW_COMMON_PATH . 'Dispatcher.php';

    // 予約画面の振分け処理クラス
    class ReserveDispatcher extends Dispatcher
    {

    }
?>
