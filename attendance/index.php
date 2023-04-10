<?php
    /**
     * @file      リクエスト振り分けクラス
     * @author    USE Y.Sakata
     * @date      2016/06/07
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    require_once './AttendanceDispatcher.php';

    $dispatcher = new AttendanceDispatcher();
    
    // システムからの直接アクセスである
    $key = isset($_GET['key']) ? htmlspecialchars($_GET['key']) : null;
    if( is_null( $key ) )
    {
        // 一般アクセス
        $dispatcher->dispatch();
    }
    else
    {
        // システムからのアクセス
        $dispatcher->directAccessDispatch();
    }
?>

