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

$err_code = 0;
$err_msg = "";

if( isset( $_FILES["userfile"] ) ) {


	$handle = fopen( $_FILES['userfile']['tmp_name'], "r");
	$i = 0;
	$mm = 300;
	$doc_code = 0;
	$doc_name = "";

	while( $data = fgetcsv($handle, 100000, ",") ) {


		if ($i == 0){
			$disp = $data[0];	//0
			$doc_code = $data[1];	//申請文書コード
		}else if ($i == 1){
			$disp = $data[0];	//0
			$doc_name = $data[1];	//申請文書名
		}else if ($i == 2){
			$disp = $data[0];	//0
			$tekiyou_date = $data[1];	//適用年月日読み込み
			if(preg_match('/^([1-9][0-9]{3})\-(0[1-9]{1}|1[0-2]{1})\-(0[1-9]{1}|[1-2]{1}[0-9]{1}|3[0-1]{1})$/', $tekiyou_date))	{
			}
			else{
				$err_code = 1;
				goto skip;
			}

			$sql_delete = "DELETE FROM ".$schema.".t_workflow_format WHERE wdoc_no = '".$doc_code."' and target_date = '".$tekiyou_date."'";
			sqlExec($sql_delete);

			$sql = "SELECT * FROM ".$schema.".m_code WHERE cd = 99 and no = '".$doc_code."'";
			$rows = getList($sql);
			if (count($rows) == 0){
				$sql = "INSERT INTO ".$schema.".m_code ";
				$sql .= "(cd, no, name) ";
				$sql .= "VALUES(99, '".$doc_code."', '".$doc_name."')";
				sqlExec($sql);
			}else{
				$sql = "UPDATE ".$schema.".m_code ";
				$sql .= "set name = '".$doc_name."' ";
				$sql .= "WHERE cd = 99 and no = '".$doc_code."'";
				sqlExec($sql);
			}
		}else{
			list($data1, $data2, $data3, $data4, $data5, $data6, $data7, $data8, $data9, $data10, $data11, $data12, $data13, $data14, $data15, $data16) = explode("-", $data[1]);

			if ($data10 == "0"){
				$sql = "INSERT INTO ".$schema.".t_workflow_format ";
				$sql .= "(wdoc_no, wdoc_name, num, data, post_name, create_date, target_date) ";
				$sql .= "VALUES('".$doc_code."', '".char_chg("to_db", $doc_name)."', '".$data[0]."', '".char_chg("to_db", $data[1])."', NULL, CURRENT_TIMESTAMP, '".$tekiyou_date."')";
			}else{
				$sql = "INSERT INTO ".$schema.".t_workflow_format ";
				$sql .= "(wdoc_no, wdoc_name, num, data, post_name, create_date, target_date) ";
				$sql .= "VALUES('".$doc_code."', '".char_chg("to_db", $doc_name)."', '".$data[0]."', '".char_chg("to_db", $data[1])."', '".$data10."', CURRENT_TIMESTAMP, '".$tekiyou_date."')";
			}
			sqlExec($sql);
			$mm ++;
		}
		$i++;

	}

	fclose($handle);
	$err_msg = "正しくインポートされました。";
}

skip:
if ($err_code != 0){
	$err_msg = "インポートファイル中の適用年月日が不正です。";
}

$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
$page_max = 10;
$offset = (($page-1) * $page_max);
if ($offset < 0){
	$offset = 0;
}

$sql_list = "select DISTINCT wdoc_no, wdoc_name, target_date from ".$schema.".t_workflow_format order by target_date desc, wdoc_no asc limit ".$page_max." offset ".$offset;

$cnt = 0;
$count = $offset;

$rows_list = getList($sql_list);

$format_list = "<table>\n";
$format_list .= "<tr>\n";
$format_list .= "<th align=center class=\"title_td\" width=50>NO</th>\n";
$format_list .= "<th align=center class=\"title_td\" width=150>申請番号</th>\n";
$format_list .= "<th align=center class=\"title_td\" width=400>申請文書名</th>\n";
$format_list .= "<th align=center class=\"title_td\" width=150>適用年月日</th>\n";
$format_list .= "</tr>\n";

if($rows_list){
	while($row_list = $rows_list[$cnt]) {
		$format_list .= "<tr>\n";
	      	$format_list .= "<td align=center class=\"item_td\">".($count+1)."</td>\n";
	      	$format_list .= "<td align=center class=\"item_td\">".$row_list["wdoc_no"]."</td>\n";
	      	$format_list .= "<td align=left class=\"item_td\">".$row_list["wdoc_name"]."</td>\n";
	      	$format_list .= "<td align=center class=\"item_td\">".$row_list["target_date"]."</td>\n";
		$format_list .= "</tr>\n";
		$count ++;
		$cnt ++;
	}
}

$format_list .= "</table>\n";

$sql_page = "select DISTINCT wdoc_no, wdoc_name, target_date from ".$schema.".t_workflow_format order by target_date desc, wdoc_no asc ";

$pageList = pageListCreate($page_max, "page", $sql_page, $page, "inport_workflow_doc_format.php?cd=0");

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

<link rel="stylesheet" href="./css/sales/sales.css" type="text/css" />
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
include("./View/Common/Breadcrumb_inport_workflow_doc_format.php");
?>

			<?= $pageList ?>

			<!-- serchListArea -->
			<div class="serchListAreaFree">
				<?= $format_list ?>
			</div><!-- /.serchListArea -->

			<!-- 検索結果リストエリア width指定なし serchListAreaFree -->
			<div class="serchListAreaFree">
<p align=center><?= $err_msg ?></p>
<form method="POST" enctype="multipart/form-data" action="inport_workflow_doc_format.php?page=<?= $page ?>">
				<table>
					<tbody>
<tr>
<th width=100>送信ファイル</th>
<td align=left width=400><input type="file" name="userfile" size="40"></td>
<td align=center width=80><input type="submit" value="送信" name="up"></td>
</tr>


					</tbody>
				</table>
</form>
			</div><!-- /.serchListAreaFree -->

		</div><!-- /#sb-site -->

    <script type="text/javascript" src="jquery/js/scrolltopcontrol.js"></script> <!--スクロールしながらページのトップに戻る-->
    <script src="../js/home/slidebars/slidebars.min.js"></script>
    <script src="../js/home/slidebars/mp_silde.js"></script>
    </body>
</html>
