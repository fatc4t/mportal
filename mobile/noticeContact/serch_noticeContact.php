<?php
require_once ("../common/lib/function.php");
require_once ("../common/lib/DBAccess_Function.php");

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();

$sql  = " SELECT nc.notice_contact_id";
$sql .= "       ,CASE WHEN LENGTH(nc.title) > 10 THEN SUBSTR(nc.title, 0,  10) || ' ...'";
$sql .= "             ELSE nc.title";
$sql .= "        END as title";
$sql .= "       ,CASE WHEN LENGTH(nc.contents) > 10 THEN SUBSTR(nc.contents, 0,  35) || ' ...'";
$sql .= "             ELSE nc.title";
$sql .= "        END as contents";
$sql .= "       ,ncd.state";
$sql .= "       ,to_char(nc.registration_time,'YYYY/MM/DD HH24時MI分SS秒') as registration_time";
$sql .= "       ,v.organization_name";
$sql .= "       ,v.user_name";
$sql .= "   FROM ".$schema.".t_notice_contact as nc";
$sql .= "        JOIN (SELECT notice_contact_id,state";
$sql .= "                FROM ".$schema.".t_notice_contact_details";
$sql .= "               WHERE user_id =".$login_staff_id;
$sql .= "             ) as ncd on (nc.notice_contact_id =  ncd.notice_contact_id)";
$sql .= "        LEFT JOIN (SELECT * FROM ".$schema.".v_user WHERE eff_code = '適用中') v on (nc.registration_user_id::integer = v.user_id)";
$sql .= "   ORDER BY nc.update_time DESC";
$sql .= "   limit 50";

$f_rows = getList($sql);

$cnt = 0;
$content = "";

if($f_rows){
        while($f_row = $f_rows[$cnt]) {
            // 先頭部分
            $content .= "<li><a href=\"noticeContact_inf.php?notice_contact_id=".$f_row["notice_contact_id"]."\"> ";

            // 内容追加
            $content .= "タイトル：".$f_row["title"]."<BR>　　発信：".$f_row["organization_name"]." - ".$f_row["user_name"]."<BR>発信日時：".$f_row["registration_time"];

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
