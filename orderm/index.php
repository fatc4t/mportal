<?php
    /**
     * @file      リクエスト振り分けクラス
     * @author    K.Sakamoto
     * @date      2017/07/25
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    require_once '../system/SystemDispatcher.php';

    $dispatcher = new SystemDispatcher();
    
    $dispatcher->dispatch();



