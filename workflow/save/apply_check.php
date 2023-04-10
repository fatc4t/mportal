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

require_once("./public/f_status.php");
require_once("./public/f_customer.php");
require_once("./public/f_staff.php");
require_once("./public/workflow.php");


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

	if ( isset($_GET['mode']) ) {
		$mode = $_GET['mode'];
	}else{
		$mode = 0;
	}
	if ( isset($_GET['page']) ) {
		$page = $_GET['page'];
	}else{
		$page = 1;
	}

	$staff_code = $login_staff_id;	//f_staff_id_get($login_staff);
	$workflow_html = "";

	if ($mode == 1){	//済みにする
		$sql_up = "UPDATE ".$schema.".t_workflow SET after_check = 2 WHERE workflow_id = ".$workflow_id;
		sqlExec($sql_up);
	}else if ($mode == 2){	//未にする
		$sql_up = "UPDATE ".$schema.".t_workflow SET after_check = 1 WHERE workflow_id = ".$workflow_id;
		sqlExec($sql_up);
	}

	$count = 0;
	$workflow_html .= "<table align=center class=\"list_table\" width=100%>\n";

	$page_max = 10;
	$offset = (($page-1) * $page_max);
	if ($offset < 0){
		$offset = 0;
	}
	$sql = "SELECT * FROM ".$schema.".t_workflow WHERE user_id = '".$staff_code."' and stat = 3 and after_check > 0 ORDER BY workflow_id DESC limit ".$page_max." offset ".$offset;

$count = $offset;

		$cnt = 0;
        	$f_rows = getList($sql);
               
		if($f_rows){
			$workflow_html .= "<tr>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=30>NO</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=80>申請番号</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=100>申請日付</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=150>申請文書</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=100>申請者</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=250>タイトル</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=400>内容</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=80>確認</th>\n";
			$workflow_html .= "</tr>\n";
			while($row = $f_rows[$cnt]) {
				$count ++;
				$workflow_html .= "<tr>\n";
			      	$workflow_html .= "<td align=center class=\"item_td\">".$count."</td>\n";
			      	$workflow_html .= "<td align=center class=\"item_td\"><a href=\"./apply_check.php?home=".$home."&cd=1&page=".$page."&workflow_id=".$row["workflow_id"]."\">".$row["workflow_no"]."</a></td>\n";
			      	$workflow_html .= "<td align=center class=\"item_td\">".$row["create_date"]."</td>\n";
			      	$workflow_html .= "<td align=left class=\"item_td\">".f_code_dsp(99, $row["flow_doc_code"])."</td>\n";
			      	$workflow_html .= "<td align=center class=\"item_td\">".f_staff_name_get($row["user_id"])."</td>\n";
			      	$workflow_html .= "<td align=left class=\"item_td\">".htmlspecialchars($row["title"])."</td>\n";

$str = mb_strimwidth($row["comment"], 0, 200, "...");

			      	$workflow_html .= "<td align=left class=\"item_td\">".$str."</td>\n";
				if ($row["after_check"] == 1){	//未→済にするリンク
				      	$workflow_html .= "<td align=center class=\"item_td\"><a href=\"./apply_check.php?home=".$home."&cd=0&page=".$page."&mode=1&workflow_id=".$row["workflow_id"]."\">".sumi_mi($row["after_check"])."</a></td>\n";
				}else{	//済→未にするリンク
				      	$workflow_html .= "<td align=center class=\"item_td\"><a href=\"./apply_check.php?home=".$home."&cd=0&page=".$page."&mode=2&workflow_id=".$row["workflow_id"]."\">".sumi_mi($row["after_check"])."</a></td>\n";
				}
				$workflow_html .= "</tr>\n";
				$cnt += 1;
			}
		}

	$workflow_html .= "</table>\n";

	$workflow_doc_html = "";
	$workflow_stat_html = "";
	
	if ($cd == 1){	//申請書番号がクリックされた
		$workflow_doc_html = workflow_doc_inf(3, $workflow_id, $staff_code);
		$workflow_stat_html = workflow_stat(3, $workflow_id, $staff_code, $home, $cd, $page);
	}

//各画面のPHP

	$sql_page = "SELECT * FROM ".$schema.".t_workflow WHERE user_id = '".$staff_code."' and stat = 3 and after_check > 0 ORDER BY workflow_id DESC ";

	$pageList = pageListCreate($page_max, "page", $sql_page, $page, "apply_check.php?home=".$home."&cd=0");



?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>M-PORTAL</title>

        <?php
            include("../home/View/Common/HtmlHeader.php"); 
        ?>


<meta http-equiv="Content-Type" content="text/html; charset=SHIFT-JIS">
<meta name="description" content="Slidebars is a jQuery plugin for quickly and easily implementing app style off-canvas menus and sidebars into your website.">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">

<!-- 各画面のスクリプト -->

<script language="javascript" type="text/javascript">
function stop(){

	document.getElementById("mode").value = 1;
	var target = document.getElementById("apply_view");
	target.submit();

}
</script>
<script type="text/javascript" src="./js/sales/jquery/jquery.min.js"></script>
<script type="text/javascript" src="./js/sales/jquery/jquery.jPrintArea.js"></script> 
<script type="text/javascript" src="./js/sales/print.js"></script> 


	</head>

    <body id="top">
<?php
include("../home/View/Common/PageHeader.php");
?>
		<div id="sb-site">
<?php
include("./View/Common/Breadcrumb_apply_check.php");
?>

			<form id="apply_check" action="apply_check.php?home=".$home."&cd=0&workflow_id=<?= $workflow_id ?>" method="post">

			<?= $pageList ?>

			<!-- serchListArea -->
			<div class="serchListArea">
				<?= $workflow_html ?>
			</div><!-- /.serchListArea -->

			<!-- serchButtonArea -->
			<div class="serchButtonArea">
				<p align=center>
<?php
			if ($cd == 1){
				echo "<input type=\"button\" id=\"btn_print\" value=\"印刷\" class=\"print\" />\n";
//				echo "<a href=\"#\" class=\"offprint\" id=\"btn_print\"><span class=\"print\" /></span>印刷</a>\n";
			}
?>

				</p>
			</div><!-- /.serchButtonArea -->

			<div id="printarea">
			<!-- serchEditArea -->
			<div class="serchEditArea">
				<?= $workflow_doc_html ?>
			</div><!-- /.serchEditArea -->

			<!-- serchEditArea -->
			<div class="serchEditArea">
				<?= $workflow_stat_html ?>
			</div><!-- /.serchEditArea -->
			</div>

		</form>
		</div><!-- /#sb-site -->

    <script src="../js/home/slidebars/slidebars.min.js"></script>
    <script src="../js/home/slidebars/mp_silde.js"></script>
    </body>
</html>
