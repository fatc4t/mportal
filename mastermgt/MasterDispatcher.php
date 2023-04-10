<?php
    /**
     * @file      M-PORTAL管理振り分けクラス
     * @author    USE Y.Sakata
     * @date      2016/07/01
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    // システムパラメータファイル
    require_once '../../local_security/mportal/master/SystemParameters.php';
    // メインコントローラ(index.phpからのみ参照)
    require_once SystemParameters::$FW_COMMON_PATH . 'Dispatcher.php';

    // M-PORTAL管理システムの振分け処理クラス
    class MasterDispatcher extends Dispatcher
    {

    }
?>
