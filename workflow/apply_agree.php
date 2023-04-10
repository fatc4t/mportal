<?php


   require_once './WorkFlowDispatcher.php';

    $dispatcher = new WorkFlowDispatcher();
    $dispatcher->dispatch();

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();


//各画面のPHP
require_once("DBAccess_Function.php");

require_once("./public/f_function.php");
require_once("./public/f_mail.php");
require_once("./public/f_status.php");
require_once("./public/f_customer.php");
require_once("./public/f_staff.php");
require_once("./public/workflow.php");

//flag:0(無視)1(承認対象)2(承認待ち)3(承認済み)
//cc_flag:0(無視)1(参照対象)2(参照待ち)3(参照済み)
//stat:0(無視)1(申請中)2(否決)3(可決)4(取戻)5(差戻)

//workflow_id
//user_id
//busho_code
//yyyy
//mm
//dd
//flow_doc_code
//next1_user_id
//next1_flag
//next1_cc1_user_id
//next1_cc1_flag
//next1_cc2_user_id
//next1_cc2_flag
//next1_cc3_user_id
//next1_cc3_flag

	if ( isset($_GET['cd']) ) {
		$cd = $_GET['cd'];
	}else{
		$cd = 0;
	}

	if ( isset($_GET['workflow_id']) ) {
		$workflow_id = $_GET['workflow_id'];
	}else{
		$workflow_id = 0;
	}

	if ( isset($_POST['mode']) ) {
		$mode = $_POST['mode'];
	}else{
		$mode = 0;
	}

	if ( isset($_POST['d_comment']) ) {
		$d_comment = $_POST['d_comment'];
	}else{
		$d_comment = "";
	}

	$staff_code = $login_staff_id; //f_staff_id_get($login_staff);
	$workflow_html = "";

	if ($cd == 2){
//承認・参照・否決
		workflow_agree($mode, $staff_code, $workflow_id, $d_comment, $login_staff);
	}
	$count = 0;
	$workflow_html .= "<table align=center class=\"list_table\" width=100%>\n";
			$workflow_html .= "<tr>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=50>NO</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=150>申請番号</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=250>申請日付</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=150>申請文書</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=150>申請者</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\">タイトル</th>\n";
//			$workflow_html .= "<th align=center class=\"title_td\" width=400>内容</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=100>種別</th>\n";
			$workflow_html .= "</tr>\n";

		$sql = "SELECT * FROM ".$schema.".t_workflow WHERE (next1_data LIKE '%*".$staff_code."-2%' or next2_data LIKE '%*".$staff_code."-2%' or next3_data LIKE '%*".$staff_code."-2%' or next4_data LIKE '%*".$staff_code."-2%' or next5_data LIKE '%*".$staff_code."-2%' or next6_data LIKE '%*".$staff_code."-2%' or next7_data LIKE '%*".$staff_code."-2%' or next8_data LIKE '%*".$staff_code."-2%' or next9_data LIKE '%*".$staff_code."-2%' or next10_data LIKE '%*".$staff_code."-2%') and stat = '1'";

		$cnt = 0;
	        $f_rows = getList($sql);
               
		if($f_rows){
			while($f_row = $f_rows[$cnt]) {
				$count ++;
				$workflow_html .= "<tr>\n";
			      	$workflow_html .= "<td align=center class=\"item_td\">".$count."</td>\n";
			      	$workflow_html .= "<td align=center class=\"item_td\"><a href=\"./apply_agree.php?home=".$home."&cd=1&workflow_id=".$f_row["workflow_id"]."\">".$f_row["workflow_no"]."</a></td>\n";
			      	$workflow_html .= "<td align=center class=\"item_td\">".$f_row["create_date"]."</td>\n";
			      	$workflow_html .= "<td align=left class=\"item_td\">".f_code_dsp(99, $f_row["flow_doc_code"])."</td>\n";
			      	$workflow_html .= "<td align=center class=\"item_td\">".f_staff_name_get($f_row["user_id"])."</td>\n";
			      	$workflow_html .= "<td align=left class=\"item_td\">".htmlspecialchars($f_row["title"])."</td>\n";

			      	$workflow_html .= "<td align=center class=\"item_td\">承認</td>\n";
				$workflow_html .= "</tr>\n";
				$cnt += 1;
			}
		}

	$workflow_html .= "</table>\n";

	if ($count == 0){	//データがない
		$workflow_html .= "承認・参照対象の申請書はありません。\n";
	}
	
	$workflow_doc_html = "";
	$workflow_stat_html = "";
	if ($cd == 1){	//申請書番号がクリックされた
		$workflow_doc_html .= workflow_doc_inf(1, $workflow_id, $staff_code);
		$workflow_stat_html .= workflow_stat(1, $workflow_id, $staff_code, 0, 0, 0);
	}



?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>M-PORTAL</title>

        <?php
            include("View/Common/HtmlHeader.php"); 
        ?>


<meta http-equiv="Content-Type" content="text/html; charset=SHIFT-JIS">
<meta name="description" content="Slidebars is a jQuery plugin for quickly and easily implementing app style off-canvas menus and sidebars into your website.">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">

<!-- 各画面のスクリプト -->

<link rel="stylesheet" href="./css/sales/sales.css" type="text/css" />
<script language="javascript" type="text/javascript">
function agree(){

	document.getElementById("mode").value = 1;
	var target = document.getElementById("apply_agree");
	target.submit();

}
function agree_view(){

	document.getElementById("mode").value = 2;
	var target = document.getElementById("apply_agree");
	target.submit();

}
function against(){

	document.getElementById("mode").value = 3;
	var target = document.getElementById("apply_agree");
	target.submit();

}
function remand(){

	document.getElementById("mode").value = 5;
	var target = document.getElementById("apply_agree");
	target.submit();

}

</script>

	</head>

    <body id="top">
<?php
include("../home/View/Common/PageHeader.php");
?>
		<div id="sb-site">
<?php
include("./View/Common/Breadcrumb_apply_agree.php");
?>

<form id="apply_agree" action="apply_agree.php?home=<?= $home ?>&cd=2&workflow_id=<?= $workflow_id ?>" method="post">
			<!-- serchListArea -->
			<div class="serchListArea">
				<?= $workflow_html ?>
			</div><!-- /.serchListArea -->

			<div class="serchEditArea">
				<?= $workflow_doc_html ?>
			</div><!-- /.serchEditArea -->

			<!-- serchEditArea -->
			<div class="serchEditArea">
				<?= $workflow_stat_html ?>
			</div><!-- /.serchEditArea -->

		<input type="hidden" id="mode" name="mode" value="0" />
		</form>

		</div><!-- /#sb-site -->
    <script src="../js/home/slidebars/slidebars.min.js"></script>
    <script src="../js/home/slidebars/mp_silde.js"></script>
    </body>
</html>
