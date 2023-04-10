<?php
    /**
     * @file      リクエスト振り分けクラス
     * @author    USE Y.Sakata
     * @date      2016/07/01
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    require_once './MasterDispatcher.php';

    $dispatcher = new MasterDispatcher();
    $dispatcher->dispatch();

?>

