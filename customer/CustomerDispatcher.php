<?php
    /**
     * @file      顧客管理振り分けクラス
     * @author    K.Sakamoto
     * @date      2017/07/25
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    // システムパラメータファイル
    require_once '../../local_security/mportal/customer/SystemParameters.php';
    // メインコントローラ(index.phpからのみ参照)
    require_once SystemParameters::$FW_COMMON_PATH . 'Dispatcher.php';
    // 勤怠システムの振分け処理クラス
    class CustomerDispatcher extends Dispatcher
    {
    }
?>
