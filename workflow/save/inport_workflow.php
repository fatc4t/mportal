<?php


   require_once './WorkFlowDispatcher.php';

    $dispatcher = new WorkFlowDispatcher();
    $dispatcher->dispatch();

//各画面のPHP
require_once("DBAccess_Function.php");

require_once("./public/f_function.php");

require_once("./public/f_status.php");
require_once("./public/f_customer.php");
require_once("./public/f_staff.php");
require_once("./public/workflow.php");

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();

$cd = (isset($_GET['cd'])) ? $_GET['cd'] : 0;

if ($cd == 100){	//workflow_root_dspからの戻り
	$target_date = (isset($_GET['target_date'])) ? $_GET['target_date'] : "";
	$target_flow = (isset($_GET['target_flow'])) ? $_GET['target_flow'] : 0;
	$target_org = (isset($_GET['target_org'])) ? $_GET['target_org'] : 0;
}else{
	$target_date = (isset($_POST['target_date'])) ? $_POST['target_date'] : "";
	$target_flow = (isset($_POST['flow_doc'])) ? $_POST['flow_doc'] : 0;
	$target_org = (isset($_POST['target_org'])) ? $_POST['target_org'] : 0;
}


if ($cd == 99){	//確認する
	$ret = "./workflow_root_dsp.php?target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org;
	header( "HTTP/1.1 301 Moved Permanently" ); 
	header( "Location: ".$ret); 
}

		$kyoten_cd =  0;
		$busho_cd =  0;
		$workflow_doc =  0;
		$staff_rank =  0;
		for ($i = 1; $i <= 10; $i++){
			$flow[$i][0] =  "";	//承認者
			$flow[$i][1] =  "";	//CC 1
			$flow[$i][2] =  "";	//CC 2
			$flow[$i][3] =  "";	//CC 3
			$flow[$i][4] =  "";	//CC 4
			$flow[$i][5] =  "";	//CC 5
		}

$err_code = 0;
$err_msg = "";

if( isset( $_FILES["userfile"] ) ) {

	$handle = fopen( $_FILES['userfile']['tmp_name'], "r");
	$c_cnt = 0;

//コメント行を空読み
$data = fgetcsv($handle, 100000, ",");
//適用年月日読み込み
$data = fgetcsv($handle, 100000, ",");
$tekiyou_date = $data[0];

if ($tekiyou_date == ""){
	$err_code = 1;
	goto skip;
}else{
//書式：2012-01-01
	if(preg_match('/^([1-9][0-9]{3})\-(0[1-9]{1}|1[0-2]{1})\-(0[1-9]{1}|[1-2]{1}[0-9]{1}|3[0-1]{1})$/', $tekiyou_date))	{
	}
	else{
		$err_code = 1;
		goto skip;
	}
}

	$sql_delete = "DELETE FROM ".$schema.".m_workflow where target_date = '".$tekiyou_date."'";
	sqlExec($sql_delete);


	while( $data = fgetcsv($handle, 100000, ",") ) {

	$kyoten_cd = $data[0];
	$busho_cd = $data[1];
	$workflow_doc = $data[2];
	$staff_rank = $data[3];
	$cc1 = $data[4];
	$cc2 = $data[5];
	$cc3 = $data[6];
	$cc4 = $data[7];
	$cc5 = $data[8];
	$num = 0;
	$j = 9;
	for ($i = 1; $i <= 10; $i++){
		$num ++;
		$flow[$num][0] = workflow_root_split($data[$j]);	//承認者
		$j ++;
		$flow[$num][1] = $data[$j];	//CC 1
		$j ++;
		$flow[$num][2] = $data[$j];	//CC 2
		$j ++;
		$flow[$num][3] = $data[$j];	//CC 3
		$j ++;
		$flow[$num][4] = $data[$j];	//CC 3
		$j ++;
		$flow[$num][5] = $data[$j];	//CC 3
		$j ++;
	}

	$c_cnt ++;
/*
	echo $kyoten_cd." ".$busho_cd." ".$workflow_doc." ".$staff_rank." ".
			$flow[1][0]." ".$flow[1][1]." ".$flow[1][2]." ".$flow[1][3]." ".$flow[1][4]." ".$flow[1][5]." ".
			$flow[2][0]." ".$flow[2][1]." ".$flow[2][2]." ".$flow[2][3]." ".$flow[2][4]." ".$flow[2][5]." ".
			$flow[3][0]." ".$flow[3][1]." ".$flow[3][2]." ".$flow[3][3]." ".$flow[3][4]." ".$flow[3][5]." ".
			$flow[4][0]." ".$flow[4][1]." ".$flow[4][2]." ".$flow[4][3]." ".$flow[4][4]." ".$flow[4][5]." ".
			$flow[5][0]." ".$flow[5][1]." ".$flow[5][2]." ".$flow[5][3]." ".$flow[5][4]." ".$flow[5][5]." ".
			$flow[6][0]." ".$flow[6][1]." ".$flow[6][2]." ".$flow[6][3]." ".$flow[6][4]." ".$flow[6][5]." ".
			$flow[7][0]." ".$flow[7][1]." ".$flow[7][2]." ".$flow[7][3]." ".$flow[7][4]." ".$flow[7][5]." ".
			$flow[8][0]." ".$flow[8][1]." ".$flow[8][2]." ".$flow[8][3]." ".$flow[8][4]." ".$flow[8][5]." ".
			$flow[9][0]." ".$flow[9][1]." ".$flow[9][2]." ".$flow[9][3]." ".$flow[9][4]." ".$flow[9][5]." ".
			$flow[10][0]." ".$flow[10][1]." ".$flow[10][2]." ".$flow[10][3]." ".$flow[10][4]." ".$flow[10][5]." ".$cc1." ".$cc2." ".$cc3." ".$cc4." ".$cc5."<br />\n";
*/
	$busho_cd = code_org_id_get($busho_cd);

	$sql = "INSERT INTO ".$schema.".m_workflow ";

	$sql .= "(kyoten,busho_code,flow_doc_code,rank,";
	$sql .= "next1_data,next1_cc1_data,next1_cc2_data,next1_cc3_data,next1_cc4_data,next1_cc5_data,";
	$sql .= "next2_data,next2_cc1_data,next2_cc2_data,next2_cc3_data,next2_cc4_data,next2_cc5_data,";
	$sql .= "next3_data,next3_cc1_data,next3_cc2_data,next3_cc3_data,next3_cc4_data,next3_cc5_data,";
	$sql .= "next4_data,next4_cc1_data,next4_cc2_data,next4_cc3_data,next4_cc4_data,next4_cc5_data,";
	$sql .= "next5_data,next5_cc1_data,next5_cc2_data,next5_cc3_data,next5_cc4_data,next5_cc5_data,";
	$sql .= "next6_data,next6_cc1_data,next6_cc2_data,next6_cc3_data,next6_cc4_data,next6_cc5_data,";
	$sql .= "next7_data,next7_cc1_data,next7_cc2_data,next7_cc3_data,next7_cc4_data,next7_cc5_data,";
	$sql .= "next8_data,next8_cc1_data,next8_cc2_data,next8_cc3_data,next8_cc4_data,next8_cc5_data,";
	$sql .= "next9_data,next9_cc1_data,next9_cc2_data,next9_cc3_data,next9_cc4_data,next9_cc5_data,";
	$sql .= "next10_data,next10_cc1_data,next10_cc2_data,next10_cc3_data,next10_cc4_data,next10_cc5_data,";
	$sql .= "create_date,target_date, cc1, cc2, cc3, cc4, cc5, user_id) ";

	$sql .= "VALUES(".$kyoten_cd.", ".$busho_cd.", ".$workflow_doc.", ".$staff_rank.", '".
				$flow[1][0]."', '".$flow[1][1]."', '".$flow[1][2]."', '".$flow[1][3]."', '".$flow[1][4]."', '".$flow[1][5]."', '".
				$flow[2][0]."', '".$flow[2][1]."', '".$flow[2][2]."', '".$flow[2][3]."', '".$flow[2][4]."', '".$flow[2][5]."', '".
				$flow[3][0]."', '".$flow[3][1]."', '".$flow[3][2]."', '".$flow[3][3]."', '".$flow[3][4]."', '".$flow[3][5]."', '".
				$flow[4][0]."', '".$flow[4][1]."', '".$flow[4][2]."', '".$flow[4][3]."', '".$flow[4][4]."', '".$flow[4][5]."', '".
				$flow[5][0]."', '".$flow[5][1]."', '".$flow[5][2]."', '".$flow[5][3]."', '".$flow[5][4]."', '".$flow[5][5]."', '".
				$flow[6][0]."', '".$flow[6][1]."', '".$flow[6][2]."', '".$flow[6][3]."', '".$flow[6][4]."', '".$flow[6][5]."', '".
				$flow[7][0]."', '".$flow[7][1]."', '".$flow[7][2]."', '".$flow[7][3]."', '".$flow[7][4]."', '".$flow[7][5]."', '".
				$flow[8][0]."', '".$flow[8][1]."', '".$flow[8][2]."', '".$flow[8][3]."', '".$flow[8][4]."', '".$flow[8][5]."', '".
				$flow[9][0]."', '".$flow[9][1]."', '".$flow[9][2]."', '".$flow[9][3]."', '".$flow[9][4]."', '".$flow[9][5]."', '".
				$flow[10][0]."', '".$flow[10][1]."', '".$flow[10][2]."', '".$flow[10][3]."', '".$flow[10][4]."', '".$flow[10][5]."', CURRENT_TIMESTAMP, '".$tekiyou_date."', ".
				"'".$cc1."', '".$cc2."', '".$cc3."', '".$cc4."', '".$cc5."', ".$login_staff_id.")";

	sqlExec($sql);

	}

	fclose($handle);
	$err_msg = "正しくインポートされました。";
}



skip:
if ($err_code != 0){
	$err_msg = "インポートファイル中の適用年月日が不正です。";
}



?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>M-PORTAL</title>

<?php
            include("../home/View/Common/HtmlHeader.php"); 
?>


<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="description" content="Slidebars is a jQuery plugin for quickly and easily implementing app style off-canvas menus and sidebars into your website.">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">

<!-- 各画面のスクリプト -->

<link rel="stylesheet" type="text/css" href="./css/sales/sales.css" />
<script language="javascript" type="text/javascript">
function stop(){

	document.getElementById("mode").value = 1;
	var target = document.getElementById("apply_view");
	target.submit();

}
</script>
<script type="text/javascript" src="./js/sales/jquery/jquery.min.js"></script>
<script type="text/javascript" src="./js/sales/jquery/jquery-ui.min.js"></script>
<script type="text/javascript" src="./js/sales/jquery/jquery.jPrintArea.js"></script> 
<script type="text/javascript" src="./js/sales/print.js"></script> 
<script type="text/javascript" src="./js/sales/jquery/jquery.ui.datepicker-ja.min.js"></script>
<link rel="stylesheet" type="text/css" href="./css/sales/sunny/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="./js/sales/jquery/jquery.maskedinput.js"></script>
<script type="text/javascript">
$(function(){
	$("#target_date").datepicker({dateFormat:'yy-mm-dd'});
	$("#target_date").mask("9999-99-99");
});
</script>

	</head>

    <body id="top">
<?php
include("../home/View/Common/PageHeader.php");
?>
		<div id="sb-site">
<?php
include("./View/Common/Breadcrumb_workflow_root_dsp.php");
?>


			<!-- 検索結果リストエリア width指定なし serchListAreaFree -->
			<div class="serchListAreaFree">
				<table>
					<tbody>
<tr>
<form method="POST" action="inport_workflow.php?cd=99">
<p align=center><?= $err_msg ?></p>
<th width=150 rowspan=3>現在の申請ルート</th>
<td align=left colspan=2>組織：&nbsp;<?= f_master_org_select($target_org, "target_org") ?></td>
</tr>
<tr>
<td align=left colspan=2>申請書：&nbsp;<?= f_code_flow_doc_select($target_flow) ?></td> <!-- flow_doc -->
</tr>
<tr>
<td align=left colspan=2>対象日：&nbsp;<input type="text" id="target_date" name="target_date" size="10">&nbsp;に有効なルートを&nbsp;&nbsp;<input type="submit" value="確認する" name="up"></td>
</tr>
</form>
</tr>
<tr>
<form method="POST" enctype="multipart/form-data" action="inport_workflow.php">
<th width=100>送信ファイル</th>
<td align=left width=400><input type="file" name="userfile" size="40"></td>
<td align=center width=80><input type="submit" value="送信" name="up"></td>
</form>
</tr>


					</tbody>
				</table>
			</div><!-- /.serchListAreaFree -->

		</div><!-- /#sb-site -->

    <script type="text/javascript" src="jquery/js/scrolltopcontrol.js"></script> <!--スクロールしながらページのトップに戻る-->
    <script src="../js/home/slidebars/slidebars.min.js"></script>
    <script src="../js/home/slidebars/mp_silde.js"></script>
    </body>
</html>
