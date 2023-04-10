<?php
    /**
     * @file      indexクラス
     * @author    millionetoota
     * @date      2016/08/18
     * @version   1.00
     * @note      ログイン画面にリダイレクト
     */

    // ログイン画面にリダイレクト
    $ua=$_SERVER["HTTP_USER_AGENT"];

    if((strpos($ua,'iPhone')!==false) || (strpos($ua,'iPod') !== false) || (strpos($ua,'WindowsPhone') !== false)) {
            header("Location:../../login/index.php?param=Login/show&CompanyID=millionet&mobile=true");
            exit();
    }else if(strpos($ua,'Android') !== false){
            if ((strpos($ua,'Mobile')!==false)){
                    header("Location:../../login/index.php?param=Login/show&CompanyID=millionet&mobile=true");
                    exit();
            }
    }

    $redirect_url = "../../login/index.php?param=Login/show&CompanyID=millionet";

    header('Location:../../login/index.php?param=Login/show&CompanyID=millionet');

?>

