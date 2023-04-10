<?php
    /**
     * @file      indexクラス
     * @author    millionetoota
     * @date      2016/08/18
     * @version   1.00
     * @note      ログイン画面にリダイレクト
     */

    // ログイン画面にリダイレクト
    $redirect_url = "../../login/index.php?param=Login/show&CompanyID=mandr";

    header('Location:../../login/index.php?param=Login/show&CompanyID=mandr');

?>

