<?php
    /**
     * @file      リクエスト振り分けクラス
     * @author    oota
     * @date      2017/01/26
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    require_once './WorkFlowDispatcher.php';

    $dispatcher = new WorkFlowDispatcher();
    $dispatcher->dispatch();

?>
