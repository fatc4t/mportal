<?php
require_once ("../common/lib/function.php");
require_once ("../common/lib/DBAccess_Function.php");

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();

$daily_report_id = (isset($_GET['daily_report_id'])) ? $_GET['daily_report_id'] : "";
$user_name = (isset($_GET['user_name'])) ? $_GET['user_name'] : "";
$target_date = (isset($_GET['target_date'])) ? $_GET['target_date'] : "";

$sql  = "SELECT mrd.*,tr.data,tr.user_id FROM ".$schema.".m_daily_report_form as mr";
$sql .= "       JOIN ".$schema.".m_daily_report_form_details as mrd on (mr.form_id = mrd.form_id)";
$sql .= "       LEFT JOIN (SELECT *";
$sql .= "                   FROM ".$schema.".t_daily_report_details";
$sql .= "                  WHERE daily_report_id = ".$daily_report_id;
$sql .= "              ) as tr on (mrd.form_details_id = tr.form_details_id)";
$sql .= " WHERE mr.form_id = 1";
$sql .= " ORDER BY mrd.disp_sort";

$f_rows = getList($sql);

$cnt = 0;
$content = "";

// 先頭部分
$content .= "<div class=\"inner\">\n";
$content .= "	<p class=\"lead\">&nbsp;&nbsp;".$user_name."&nbsp;&nbsp;".$target_date."</p>\n";

if($f_rows){
        while($f_row = $f_rows[$cnt]) {

            // タイプに合わせて作成
            if( $f_row['form_type'] == 0){
                // 改行
                $content .= "		</div>\n";
            }else if( $f_row['form_type'] == 1){
                // ヘッダ
                $content .= "		<div class=\"section\">\n";
                $content .= "			<h2>".$f_row["form_details_name"]."</h2>\n";
                $content .= "			<ul class=\"voice\">\n";
            }else if( $f_row['form_type'] == 2){
                // テキスト
            }else if( $f_row['form_type'] == 3){
                // ラベル
            }else if( $f_row['form_type'] == 4){
                // リスト
            }else if( $f_row['form_type'] == 5){
                // ラジオ
            }else if( $f_row['form_type'] == 6){
                // 日付
            }else if( $f_row['form_type'] == 7){
                // エリア
                $content .= "			<li>".nl2br($f_row["data"])."</li>\n";
//                $content .= "			<li>"."<TEXTAREA cols=45 rows=5 >".$f_row["data"]."</TEXTAREA>"."</li>\n";
                $content .= "			</ul>\n";
            }else if( $f_row['form_type'] == 8){
                // チェックボックス
            }else if( $f_row['form_type'] == 9){
                // 表示専用 計算項目
            }
            
            $cnt += 1;
        }
}

// 最終部分
$content .= "</div>\n";

// コメント情報
$sqlCC  = " SELECT  drc.daily_report_comment_id";
$sqlCC .= "        ,drc.contents";
$sqlCC .= "        ,to_char(drc.registration_time,'YYYY/MM/DD HH24時MI分SS秒') as registration_time";
$sqlCC .= "        ,drc.user_id";
$sqlCC .= "        ,v.user_name";
$sqlCC .= "        ,v.organization_name";
$sqlCC .= "   FROM ".$schema.".t_daily_report_comment as drc";
$sqlCC .= "   LEFT JOIN (SELECT * FROM ".$schema.".v_user WHERE eff_code = '適用中') v on (drc.user_id = v.user_id)";
$sqlCC .= "  WHERE drc.daily_report_id = ".$daily_report_id;
$sqlCC .= "  ORDER BY drc.registration_time";

$fCC_rows = getList($sqlCC);

$cntCC = 0;
$contentCC = "";

if($fCC_rows){
        while($fCC_row = $fCC_rows[$cntCC]) {

            $contentCC .= "<div class=\"inner\">\n";
            $contentCC .= "	<p class=\"lead\">コメント</p>\n";
            $contentCC .= "		<div class=\"section\">\n";
            $contentCC .= "			<h2>".$fCC_row["user_name"]."&nbsp;&nbsp;".$fCC_row["registration_time"]."</h2>\n";
            $contentCC .= "			<ul class=\"voice\">\n";
            $contentCC .= "			<li>".$fCC_row["contents"]."</li>\n";
            $contentCC .= "			</ul>\n";
            $contentCC .= "		</div>\n";
            
            $cntCC += 1;
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
<?= $contentCC ?>
</div>
<?php h_footer(); ?>
</body>
</html>
