<?php
    /**
     * @file      リクエスト振り分けクラス
     * @author    oota
     * @date      2019/06/05
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    require_once '../reserve/ReserveDispatcher.php';

    $dispatcher = new ReserveDispatcher();
    $dispatcher->dispatch();

?>

