<?php
    /**
     * @file      indexクラス
     * @author    oota
     * @date      2016/06/20
     * @version   1.00
     * @note      ログイン画面にリダイレクト
     */

    // ログイン画面にリダイレクト
    $redirect_url = "./login/index.php?param=Login/show/login&CompanyID=use";

    header('Location:./login/index.php?param=Login/show/login&CompanyID=use');

?>

