<?php
    /**
     * @file      リクエスト振り分けクラス
     * @author    oota
     * @date      2016/06/23
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    require_once './LoginDispatcher.php';

    $dispatcher = new LoginDispatcher();
    $dispatcher->dispatch();

?>

