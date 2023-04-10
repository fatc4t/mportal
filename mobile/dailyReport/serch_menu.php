<?php
require_once ("../common/lib/function.php");
require_once ("../common/lib/DBAccess_Function.php");

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();

$sql  = "SELECT * ";
$sql .= "  FROM (SELECT to_char( current_date + arr.i, 'YYYY/MM/DD' ) as date,";
$sql .= "               '(' || to_char( current_date + arr.i, 'TMDy' ) || ')' as week";
$sql .= "          FROM generate_series( -7, 0 ) AS arr( i )";
$sql .= "         ORDER BY date desc) as dd";
$sql .= "  LEFT JOIN (SELECT  to_char(dr.target_date,'YYYY/MM/DD') as target_date";
$sql .= "                    ,count('x') as cnt";
$sql .= "               FROM ".$schema.".t_daily_report as dr";
$sql .= "              WHERE daily_report_id is not null";
$sql .= "                AND dr.target_date >= current_timestamp + '-1 weeks'";
$sql .= "                AND dr.target_date <= current_timestamp";
$sql .= "              GROUP BY target_date) as cn";
$sql .= "         ON (dd.date = cn.target_date)";

$f_rows = getList($sql);

$cnt = 0;
$content = "";

if($f_rows){
        while($f_row = $f_rows[$cnt]) {
            // 先頭部分
            $content .= "<li><a href=\"serch_report.php?target_date=".$f_row["date"]."\"> ";

            // 曜日によって文字色変更
            if($f_row["week"] == "(土)" ){
                $content .= "<font color=blue> ";
            }else if($f_row["week"] == "(日)"){
                $content .= "<font color=red> ";
            }

            // 当日文言
            if($cnt == 0 ){
                $content .= "今日 ";
            }

            // 内容追加
            $content .= $f_row["date"]." ".$f_row["week"]." ";

            // 件数
            if($f_row["cnt"] > 0){
                $content .= $f_row["cnt"]." 件 ";
            }else{
                $content .= " 0件 ";
            }

            // 曜日によって文字色変更
            if($f_row["week"] == "(土)" ){
                $content .= "</font> ";
            }else if($f_row["week"] == "(日)"){
                $content .= "</font> ";
            }

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
