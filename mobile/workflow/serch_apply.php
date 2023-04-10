<?php
require_once ("../../workflow/public/f_function.php");
require_once ("../../workflow/public/f_staff.php");
require_once ("../common/lib/function.php");
require_once ("../common/lib/DBAccess_Function.php");
require_once("../../workflow/public/f_mail.php");

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();


	$content = "";
	$content_f = 0;
	$staff_code = $login_staff_id;

	for ($jj=0; $jj < 2; $jj ++){
		if ($jj == 0){
			$sql = "SELECT * FROM ".$schema.".t_workflow WHERE (next1_data LIKE '%*".$staff_code."-2%' or next2_data LIKE '%*".$staff_code."-2%' or next3_data LIKE '%*".$staff_code."-2%' or next4_data LIKE '%*".$staff_code."-2%' or next5_data LIKE '%*".$staff_code."-2%' or next6_data LIKE '%*".$staff_code."-2%' or next7_data LIKE '%*".$staff_code."-2%' or next8_data LIKE '%*".$staff_code."-2%' or next9_data LIKE '%*".$staff_code."-2%' or next10_data LIKE '%*".$staff_code."-2%') and stat = '1'";
		}else{
			$sql = "SELECT * FROM ".$schema.".t_workflow WHERE ";
			$sql .= "cc1 = '".$staff_code."-2' or cc2 = '".$staff_code."-2' or cc3 = '".$staff_code."-2' or cc4 = '".$staff_code."-2' or cc5 = '".$staff_code."-2' or ";
			$sql .= "next1_cc1_data = '*".$staff_code."-2' or next1_cc2_data = '*".$staff_code."-2' or next1_cc3_data = '*".$staff_code."-2' or next1_cc4_data = '*".$staff_code."-2' or next1_cc5_data = '*".$staff_code."-2' or ";
			$sql .= "next2_cc1_data = '*".$staff_code."-2' or next2_cc2_data = '*".$staff_code."-2' or next2_cc3_data = '*".$staff_code."-2' or next2_cc4_data = '*".$staff_code."-2' or next2_cc5_data = '*".$staff_code."-2' or ";
			$sql .= "next3_cc1_data = '*".$staff_code."-2' or next3_cc2_data = '*".$staff_code."-2' or next3_cc3_data = '*".$staff_code."-2' or next3_cc4_data = '*".$staff_code."-2' or next3_cc5_data = '*".$staff_code."-2' or ";
			$sql .= "next4_cc1_data = '*".$staff_code."-2' or next4_cc2_data = '*".$staff_code."-2' or next4_cc3_data = '*".$staff_code."-2' or next4_cc4_data = '*".$staff_code."-2' or next4_cc5_data = '*".$staff_code."-2' or ";
			$sql .= "next5_cc1_data = '*".$staff_code."-2' or next5_cc2_data = '*".$staff_code."-2' or next5_cc3_data = '*".$staff_code."-2' or next5_cc4_data = '*".$staff_code."-2' or next5_cc5_data = '*".$staff_code."-2' or ";
			$sql .= "next6_cc1_data = '*".$staff_code."-2' or next6_cc2_data = '*".$staff_code."-2' or next6_cc3_data = '*".$staff_code."-2' or next6_cc4_data = '*".$staff_code."-2' or next6_cc5_data = '*".$staff_code."-2' or ";
			$sql .= "next7_cc1_data = '*".$staff_code."-2' or next7_cc2_data = '*".$staff_code."-2' or next7_cc3_data = '*".$staff_code."-2' or next7_cc4_data = '*".$staff_code."-2' or next7_cc5_data = '*".$staff_code."-2' or ";
			$sql .= "next8_cc1_data = '*".$staff_code."-2' or next8_cc2_data = '*".$staff_code."-2' or next8_cc3_data = '*".$staff_code."-2' or next8_cc4_data = '*".$staff_code."-2' or next8_cc5_data = '*".$staff_code."-2' or ";
			$sql .= "next9_cc1_data = '*".$staff_code."-2' or next9_cc2_data = '*".$staff_code."-2' or next9_cc3_data = '*".$staff_code."-2' or next9_cc4_data = '*".$staff_code."-2' or next9_cc5_data = '*".$staff_code."-2' or ";
			$sql .= "next10_cc1_data = '*".$staff_code."-2' or next10_cc2_data = '*".$staff_code."-2' or next10_cc3_data = '*".$staff_code."-2' or next10_cc4_data = '*".$staff_code."-2' or next10_cc5_data = '*".$staff_code."-2'";
		}
		$cnt = 0;
	        $f_rows = getList($sql);

		if($f_rows){
			while($f_row = $f_rows[$cnt]) {
				if ($jj == 0){
					$content .= "<li><a href=\"workflow_inf.php?cd=0&mode=1&workflow_id=".$f_row["workflow_id"]."\">".$f_row["yyyy"]."-".$f_row["mm"]."-".$f_row["dd"]."&nbsp;&nbsp;<font color=red>承認待ち</font><br />".f_staff_name_get($f_row["user_id"])."&nbsp;&nbsp;<br />".f_code_dsp(99, $f_row["flow_doc_code"])."<br />(".$f_row["title"].")</a></li>\n";
				}else{
					$content .= "<li><a href=\"workflow_inf.php?cd=0&mode=2&workflow_id=".$f_row["workflow_id"]."\">".$f_row["yyyy"]."-".$f_row["mm"]."-".$f_row["dd"]."&nbsp;&nbsp;<font color=red>参照待ち</font><br />".f_staff_name_get($f_row["user_id"])."&nbsp;&nbsp;<br />".f_code_dsp(99, $f_row["flow_doc_code"])."<br />(".$f_row["title"].")</a></li>\n";
				}
				$content_f = 1;
				$cnt += 1;
			}
		}
	}

	if ($content_f == 0){
			$content .= "		<div class=\"section\">\n";
			$content .= "			<h2>メッセージ</h2>\n";
			$content .= "			<ul class=\"voice\">\n";
			$content .= "			<li>対象の申請書はありません。</li>\n";
			$content .= "			</ul>\n";
			$content .= "		</div>\n";
	}



?>
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
