<?php
require_once ("../common/lib/function.php");
require_once ("../common/lib/DBAccess_Function.php");

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();

$sql  = "SELECT * ";
$sql .= "  FROM  ".$schema.".t_top_message";
$sql .= " WHERE top_message_id is not null";
$sql .= " ORDER BY registration_time DESC";
$sql .= " LIMIT 10";

$f_rows = getList($sql);

$cnt = 0;
$content = "";

if($f_rows){
        while($f_row = $f_rows[$cnt]) {
            // 先頭部分
            $content .= "<li><a href=\"message_inf.php?top_message_id=".$f_row["top_message_id"]."\"> ";

            // 内容追加
            $content .= $f_row["title"]." ";

            // 最終部分
            $content .= "</a></li>\n";

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

<body id="winTop" class="serchTop">
<?php h_header(); ?>
<?php h_menu(); ?>
<div class="overlay"></div>
<div id="container">
	<div class="inner">
		<ul>
<?= $content ?>
		</ul>
	</div>
</div>
<?php h_footer(); ?>
</body>
</html>
