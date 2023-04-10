<?php
    /**
     * @file      売上管理振り分けクラス
     * @author    oota
     * @date      2016/06/23
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    // システムパラメータファイル
    require_once '../../local_security/mportal/profit/SystemParameters.php';
    // メインコントローラ(index.phpからのみ参照)
    require_once SystemParameters::$FW_COMMON_PATH . 'Dispatcher.php';

    // 勤怠システムの振分け処理クラス
    class ProfitDispatcher extends Dispatcher
    {

    }
?>
