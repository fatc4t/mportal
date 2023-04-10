<?php
require_once ("../../workflow/public/f_function.php");
require_once ("../../workflow/public/f_staff.php");
require_once ("../../workflow/public/workflow.php");
require_once ("../common/lib/function.php");
require_once ("../common/lib/DBAccess_Function.php");
require_once("../../workflow/public/f_mail.php");

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();

$cd = (isset($_GET['cd'])) ? $_GET['cd'] : 0;
$workflow_id = (isset($_GET['workflow_id'])) ? $_GET['workflow_id'] : "";
$mode = (isset($_GET['mode'])) ? $_GET['mode'] : 0;
$d_comment = (isset($_POST['d_comment'])) ? $_POST['d_comment'] : "";


$name = "";
$content = "";


if ($cd == 1){	//mode 承認１参照２否決３差し戻し５

	$mode_f = 0;
  	if (isset($_POST['b_1'])) {
		$mode_f = 1;
	}
  	if (isset($_POST['b_2'])) {
		$mode_f = 2;
	}
  	if (isset($_POST['b_3'])) {
		$mode_f = 3;
	}
  	if (isset($_POST['b_4'])) {
		$mode_f = 5;
	}

	$staff_code = $login_staff_id;

	workflow_agree($mode_f, $staff_code, $workflow_id, $d_comment, $login_staff);

header( "HTTP/1.1 301 Moved Permanently" ); 
header( "Location: ./serch_apply.php"); 


}else{

	if ($workflow_id == ""){
		$content = "エラー(E001)です。戻ってください<br />\n";
	}else{
		$sql = "SELECT * FROM ".$schema.".t_workflow WHERE workflow_id = ".$workflow_id;
        	$result = getList($sql);
		if($result){
			$row = $result[0];
//			$name = mb_convert_encoding(f_staff_name_get($row["user_id"]), "UTF-8", "auto");
//			$flow_doc = mb_convert_encoding(f_code_dsp(99, $row["flow_doc_code"]), "UTF-8", "auto");
			$name = f_staff_name_get($row["user_id"]);
			$flow_doc = f_code_dsp(99, $row["flow_doc_code"]);
			$title = $row["title"];
			$comment = $row["comment"];
			$create_date = $row["create_date"];
			$next_comment[1] = $row["next1_data_comment"];
			$next_comment[2] = $row["next2_data_comment"];
			$next_comment[3] = $row["next3_data_comment"];
			$next_comment[4] = $row["next4_data_comment"];
			$next_comment[5] = $row["next5_data_comment"];
			$next_comment[6] = $row["next6_data_comment"];
			$next_comment[7] = $row["next7_data_comment"];
			$next_comment[8] = $row["next8_data_comment"];
			$next_comment[9] = $row["next9_data_comment"];
			$next_comment[10] = $row["next10_data_comment"];
			$tempfile[1][1] = $row["tmp_name1"];
			$tempfile[1][2] = $row["file_name1"];
			$tempfile[1][3] = $row["kakuchousi1"];
			$tempfile[2][1] = $row["tmp_name2"];
			$tempfile[2][2] = $row["file_name2"];
			$tempfile[2][3] = $row["kakuchousi2"];
			$tempfile[3][1] = $row["tmp_name3"];
			$tempfile[3][2] = $row["file_name3"];
			$tempfile[3][3] = $row["kakuchousi3"];
		}

		$content .= "<div class=\"inner\">\n";
		$content .= "	<p class=\"lead\">".$name."</p>\n";
		$content .= "		<div class=\"section\">\n";
		$content .= "			<h2>".$title."</h2>\n";
		$content .= "			<ul class=\"board\">\n";
		$content .= "			<li>".$create_date."</li>\n";
		$content .= "			<li>".$flow_doc."</li>\n";
//		$content .= "			<li>".nl2br($comment)."</li>\n";

//内容
		$content .= "			<li>\n";
		$content .= workflow_format_read_mobile($row["flow_doc_code"], $workflow_id);
		$content .= "			</li>\n";
		$content .= "			</ul>\n";
		$content .= "		</div>\n";

		$content .= "		<div class=\"section\">\n";
		$content .= "			<h2>添付ファイル</h2>\n";
		$content .= "			<ul class=\"voice\">\n";
		$tempfile_f = 0;
		if ($tempfile[1][1] != ""){
			$content .= "			<li><a href=\"../../workflow/file/".$schema."/".$tempfile[1][1]."\" download=\"".$tempfile[1][2].$tempfile[1][3]."\">".$tempfile[1][2].$tempfile[1][3]."</a></li>\n";
			$tempfile_f = 1;
		}
		if ($tempfile[2][1] != ""){
			$content .= "			<li><a href=\"../../workflow/file/".$schema."/".$tempfile[2][1]."\" download=\"".$tempfile[2][2].$tempfile[2][3]."\">".$tempfile[2][2].$tempfile[2][3]."</a></li>\n";
			$tempfile_f = 1;
		}
		if ($tempfile[3][1] != ""){
			$content .= "			<li><a href=\"../../workflow/file/".$schema."/".$tempfile[3][1]."\" download=\"".$tempfile[3][2].$tempfile[3][3]."\">".$tempfile[3][2].$tempfile[3][3]."</a></li>\n";
			$tempfile_f = 1;
		}
		if ($tempfile_f == 0){
			$content .= "			<li>添付ファイルはありません</li>\n";
		}

		$content .= "			</ul>\n";
		$content .= "		</div>\n";

		$content .= "		<div class=\"section\">\n";
		$content .= "			<h2>コメント</h2>\n";
		$content .= "			<ul class=\"voice\">\n";
		$content .= "			<li><textarea name=\"d_comment\" rows=5></textarea></li>\n";
		$content .= "			</ul>\n";
		$content .= "		</div>\n";
		$content .= "</div>\n";

		if ($mode==2){	//参照
			$content .= "<p align=center><input type=\"submit\" value=\"参照\" name=\"b_2\" style=\"WIDTH: 70px; HEIGHT: 30px\"></p>\n";
		}else{
			$content .= "<p align=center><input type=\"submit\" value=\"承認\" name=\"b_1\" style=\"WIDTH: 70px; HEIGHT: 30px\">&nbsp;&nbsp;";
			$content .= "<input type=\"submit\" value=\"否決\" name=\"b_3\" style=\"WIDTH: 70px; HEIGHT: 30px\">&nbsp;&nbsp;\n";
			$content .= "<input type=\"submit\" value=\"差し戻し\" name=\"b_4\" style=\"WIDTH: 70px; HEIGHT: 30px\"></p>\n";
		}
		$content .= "<br />\n";

	}
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

<body id="winTop" class="infTop">
<?php h_header(); ?>
<?php h_menu(); ?>
<div class="overlay"></div>
<div id="container">
<form method="post" action="workflow_inf.php?cd=1&workflow_id=<?= $workflow_id ?>">
<?= $content ?>
</form>
</div>
<?php h_footer(); ?>
</body>
</html>
