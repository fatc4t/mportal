<?php
    /**
     * @file      HTML共通ヘッダ(View)
     * @author    USE Y.Sakata
     * @date      2016/04/27
     * @version   1.00
     * @note      HTML共通ヘッダ
     */
?>
<?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="description" content="Slidebars is a jQuery plugin for quickly and easily implementing app style off-canvas menus and sidebars into your website.">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">

<!-- Web App -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<title>M-PORTAL</title>

<!-- Stylesheets -->
<?php
        if( $_SESSION["SCHEMA"] == "honbu" ) 
        {
?>
<link rel="stylesheet" href="../css/home/bootstrap.min_h.css">
<link rel="stylesheet" href="../css/home/slidebars/slidebars.min_h.css">
<link rel="stylesheet" href="../css/home/mp_style_h.css">
<link rel="stylesheet" href="../css/mp_button_h.css">
<?php  }
        else
        {
?>
<link rel="stylesheet" href="../css/home/bootstrap.min.css">
<link rel="stylesheet" href="../css/home/slidebars/slidebars.min.css">
<link rel="stylesheet" href="../css/home/mp_style.css">
<link rel="stylesheet" href="../css/mp_button.css">
<?php
        }
?>

<!-- Favicons -->
<link rel="icon" type="image/png" href="../icons/16.png">
<link rel="icon" type="image/png" href="../icons/32.png" sizes="32x32">
<link rel="icon" type="image/png" href="../icons/48.png" sizes="48x48">
<link rel="icon" type="image/png" href="../icons/64.png" sizes="64x64">

<!-- Apple Touch Icons -->
<link rel="apple-touch-icon" href="../icons/152.png" sizes="120x120">
<link rel="apple-touch-icon" href="../icons/120.png" sizes="152x152">
<link rel="apple-touch-icon" href="../icons/76.png" sizes="76x76">
<link rel="apple-touch-icon" href="../icons/114.png" sizes="114x114">
<link rel="apple-touch-icon" href="../icons/57.png" sizes="57x57">
<link rel="apple-touch-icon" href="../icons/144.png" sizes="144x144">
<link rel="apple-touch-icon" href="../icons/72.png" sizes="72x72">

<!-- JS -->
<script src="../js/drop_menu.js"></script>
<script src="../js/attendance/jquery/jquery.min.js"></script>
<script src="../js/attendance/slidebars/slidebars.min.js"></script>
<script src="../js/attendance/slidebars/mp_silde.js"></script>

<?php if( empty($isNoHeader) ) { ?>
    <!-- ゆっくりTOPに上げる -->
    <script src="../js/mp_slow_up.js"></script>
<?php } ?>

<?php // 配列$fileNameの拡張子を見てｃｓｓかｊｓか判別して読み込み
    foreach ($fileNames as $fileName) 
    {
        if(strstr($fileName,'.css'))
        {
            echo('<link rel="stylesheet" type="text/css" href="../css/attendance/'.$fileName .'">'.PHP_EOL);
        }
        elseif(strstr($fileName,'.js'))
        {
            echo('<script type="text/javascript" src="../js/attendance/'.$fileName .'"></script>'.PHP_EOL);
        }
    }
?>
<!--[if lt IE 9]>
    <script src="js/html5.js"></script>
<![endif]-->
