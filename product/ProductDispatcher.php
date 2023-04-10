<?php
    /**
     * @file      商品管理振り分けクラス
     * @author    川橋
     * @date      2019/01/15
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    // システムパラメータファイル
    require_once '../../local_security/mportal/product/SystemParameters.php';
    // メインコントローラ(index.phpからのみ参照)
    require_once SystemParameters::$FW_COMMON_PATH . 'Dispatcher.php';
    // 勤怠システムの振分け処理クラス
    class ProductDispatcher extends Dispatcher
    {
    }
?>
