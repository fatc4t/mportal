<?php
    /**
     * @file      リクエスト振り分けクラス
     * @author    川橋
     * @date      2019/01/15
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    require_once './ProductDispatcher.php';

    $dispatcher = new ProductDispatcher();
    
    $dispatcher->dispatch();
?>
