<?php
require_once ("../common/lib/function.php");
require_once ("../common/lib/DBAccess_Function.php");

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();

$target_date = (isset($_GET['target_date'])) ? $_GET['target_date'] : "";

$sql  = "SELECT  dr.daily_report_id";
$sql .= "       ,dr.data";
$sql .= "       ,to_char(dr.target_date,'YYYY/MM/DD') as target_date";
$sql .= "       ,dr.form_id";
$sql .= "       ,dr.user_id";
$sql .= "       ,v.user_name";
$sql .= "       ,v.organization_name";
$sql .= "  FROM ".$schema.".t_daily_report as dr";
$sql .= "  LEFT JOIN (SELECT * FROM ".$schema.".v_user WHERE eff_code = '適用中') v on (dr.user_id = v.user_id)";
$sql .= " WHERE daily_report_id is not null";
$sql .= "   AND target_date = '".$target_date."'";

$f_rows = getList($sql);

$cnt = 0;
$content = "";

if($f_rows){
        while($f_row = $f_rows[$cnt]) {
            // 先頭部分
            $content .= "<li><a href=\"report_inf.php?daily_report_id=".$f_row["daily_report_id"]."&user_name=".$f_row["user_name"]."&target_date=".$f_row["target_date"]."\"> ";

            // 内容追加
            $content .= "　　店舗名：".$f_row["organization_name"]."<BR>スタッフ名：".$f_row["user_name"];

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
