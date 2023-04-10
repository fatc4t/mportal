<?php
require_once ("../common/lib/function.php");
require_once ("../common/lib/DBAccess_Function.php");

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();

$top_message_id = (isset($_GET['top_message_id'])) ? $_GET['top_message_id'] : "";

$sql  = " SELECT * ";
$sql .= "   FROM ".$schema.".t_top_message";
$sql .= "  WHERE top_message_id =".$top_message_id;

$f_rows = getList($sql);

$cnt = 0;
$content = "";

if($f_rows){
        while($f_row = $f_rows[$cnt]) {
            $content .= "<div class=\"inner\">\n";
            $content .= "	<p class=\"lead\">&nbsp;&nbsp;".$user_name."&nbsp;&nbsp;".$target_date."</p>\n";
            $content .= "		<div class=\"section\">\n";
            $content .= "			<h2>".$f_row["title"]."</h2>\n";
            $content .= "			<ul class=\"voice\">\n";
            $content .= "			<li>".nl2br($f_row["contents"])."</li>\n";
            $content .= "			</ul>\n";
            $content .= "		</div>\n";
            $content .= "</div>\n";

            // 添付画像
            // ディレクトリのパスを記述
            $dir = "../../topMessage/server/php/".$schema.'/'.$f_row['thumbnail'].'/'  ;
            if( is_dir( $dir ) && $handle = opendir( $dir ) ) {
                // ループ処理
                $content .= "<div class=\"inner\" align=\"center\">\n";
                while( ($file = readdir($handle)) !== false ) {
                        // ファイルのみ取得
                        if( filetype( $path = $dir . $file ) == "file" ) {
                            if (strstr($file, '.jpg') || strstr($file, '.jpeg') || strstr($file, '.gif') || strstr($file, '.png') || strstr($file, '.tiff') || strstr($file, '.bmp')) {
                                $content .= "<a href=\"".$dir.$file."\" download=\"".$dir.$file."\">\n";
                                $content .= "<img src=\"".$dir.$file."\" border=\"1\" width=\"180\" height=\"180\"/>\n";
                                $content .= "</a>\n";
                            } else {
                                $content .= "<a href=\"".$dir.$file."\" download=\"".$dir.$file."\">\n";
                                $content .= $file."\n";
                                $content .= "</a>\n";
                            }
                        }
                }
                $content .= "</div>\n";
            }
            
            $cnt += 1;
        }
}

?>
<!DOCTYPE HTML>
<html>
<head>
<!-- ヘッダ -->
<?php include("../common/include/include_header.php"); ?>

<!-- スタイルシート -->
<?php include("../common/include/include_stylesheets.php"); ?>

<!-- アイコン -->
<?php include("../common/include/include_icons.php"); ?>

<!-- スクリプト -->
<?php include("../common/include/include_js.php"); ?>
</head>

<body id="winTop" class="infTop">
<?php h_header(); ?>
<?php h_menu(); ?>
<div class="overlay"></div>
<div id="container">
<?= $content ?>
</div>
<?php h_footer(); ?>
</body>
</html>
