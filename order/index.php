<?php
    /**
     * @file      薬剤発注リクエスト振り分けクラス
     * @author    USE K.Kanda(media-craft.co.jp)
     * @date      2018/01/22
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    require_once './OrderDispatcher.php';

    $dispatcher = new OrderDispatcher();
    $dispatcher->dispatch();

?>

