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
require_once("./apply_function.php");

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();

$cd = (isset($_GET['cd'])) ? $_GET['cd'] : 0;
$target_date = (isset($_GET['target_date'])) ? $_GET['target_date'] : "";
$target_date2 = (isset($_GET['target_date2'])) ? $_GET['target_date2'] : "";
$target_flow = (isset($_GET['target_flow'])) ? $_GET['target_flow'] : 0;
$target_org = (isset($_GET['target_org'])) ? $_GET['target_org'] : 0;
$chg_flow = (isset($_GET['chg_flow'])) ? $_GET['chg_flow'] : 0;
$chg_org = (isset($_GET['chg_org'])) ? $_GET['chg_org'] : 0;

$chg_f = (isset($_GET['chg_f'])) ? $_GET['chg_f'] : 0;

if ($cd == 0 && $chg_f == 1){
/*
		$sql = "select * from ".$schema.".m_workflow where target_date = '".$target_date2."' and busho_code = ".$chg_org." and flow_doc_code = ".$chg_flow;
       		$rows = getList($sql);
		$f_rows = $rows[0];
		if ($f_rows){
			for ($i = 1; $i <= 10; $i++){
				$flow[$i][0] = $f_rows["next".$i."_data"];
				for ($j = 1; $j <= 5; $j++){
					$flow[$i][$j] = $f_rows["next".$i."_cc".$j."_data"];
				}
			}
		}

			m_workflow_insert(1, $schema, $f_rows["busho_code"], $f_rows["flow_doc_code"], $flow, $f_rows["cc1"], $f_rows["cc2"], $f_rows["cc3"], $f_rows["cc4"], $f_rows["cc5"], $f_rows["target_date"], $login_staff_id);
*/
/*

			$sql = "INSERT INTO ".$schema.".m_workflow_tmp ";
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

			$sql .= "VALUES(0, ".$f_rows["busho_code"].", ".$f_rows["flow_doc_code"].", 0, '".
						$flow[1][0]."', '".$flow[1][1]."', '".$flow[1][2]."', '".$flow[1][3]."', '".$flow[1][4]."', '".$flow[1][5]."', '".
						$flow[2][0]."', '".$flow[2][1]."', '".$flow[2][2]."', '".$flow[2][3]."', '".$flow[2][4]."', '".$flow[2][5]."', '".
						$flow[3][0]."', '".$flow[3][1]."', '".$flow[3][2]."', '".$flow[3][3]."', '".$flow[3][4]."', '".$flow[3][5]."', '".
						$flow[4][0]."', '".$flow[4][1]."', '".$flow[4][2]."', '".$flow[4][3]."', '".$flow[4][4]."', '".$flow[4][5]."', '".
						$flow[5][0]."', '".$flow[5][1]."', '".$flow[5][2]."', '".$flow[5][3]."', '".$flow[5][4]."', '".$flow[5][5]."', '".
						$flow[6][0]."', '".$flow[6][1]."', '".$flow[6][2]."', '".$flow[6][3]."', '".$flow[6][4]."', '".$flow[6][5]."', '".
						$flow[7][0]."', '".$flow[7][1]."', '".$flow[7][2]."', '".$flow[7][3]."', '".$flow[7][4]."', '".$flow[7][5]."', '".
						$flow[8][0]."', '".$flow[8][1]."', '".$flow[8][2]."', '".$flow[8][3]."', '".$flow[8][4]."', '".$flow[8][5]."', '".
						$flow[9][0]."', '".$flow[9][1]."', '".$flow[9][2]."', '".$flow[9][3]."', '".$flow[9][4]."', '".$flow[9][5]."', '".
						$flow[10][0]."', '".$flow[10][1]."', '".$flow[10][2]."', '".$flow[10][3]."', '".$flow[10][4]."', '".$flow[10][5]."', CURRENT_TIMESTAMP, '".$f_rows["target_date"]."', ".
						"'".$f_rows["cc1"]."', '".$f_rows["cc2"]."', '".$f_rows["cc3"]."', '".$f_rows["cc4"]."', '".$f_rows["cc5"]."', ".$login_staff_id.")";
			sqlExec($sql);
*/
}

//
if ($cd == 1){	//追加
	$add_no = (isset($_POST['add_no'])) ? $_POST['add_no'] : 0;			//追加位置
	$operation = (isset($_POST['operation'])) ? $_POST['operation'] : 0;		//操作0:add_noの下 1:add_noに追加
	$user_id_kian = (isset($_POST['user_id_kian'])) ? $_POST['user_id_kian'] : 0;	//起案者CC
	$user_id_apply = (isset($_POST['user_id_apply'])) ? $_POST['user_id_apply'] : 0;//承認者
	$user_id_cc = (isset($_POST['user_id_cc'])) ? $_POST['user_id_cc'] : 0;		//参照者
	$tasuuketsu = (isset($_POST['tasuuketsu'])) ? $_POST['tasuuketsu'] : 1;		//多数決
	$chg_date = (isset($_POST['chg_date'])) ? $_POST['chg_date'] : "";
	if ($tasuuketsu == 0){
		$tasuuketsu = 1;
	}

	$cc = "";

		$sql_chg = "select * from ".$schema.".m_workflow_tmp where user_id = ".$login_staff_id;
       		$rows_chg = getList($sql_chg);
		$f_rows_chg = $rows_chg[0];
		if ($f_rows_chg){
//チェック

			if ($chg_date == ""){
				$set_date = "";
			}else{
				$set_date = ", target_date = '".$chg_date."'";
			}

			if ($user_id_kian != 0){
				for ($i = 1; $i <= 5; $i++){
					if ($f_rows_chg["cc".$i] == "0"){
						$sql_up = "update ".$schema.".m_workflow_tmp set cc".$i." = '".$user_id_kian."-2'".$set_date." where user_id = ".$login_staff_id;
						sqlExec($sql_up);
						break;
					}
				}
			}

			if ($operation == 0 && $user_id_apply != 0 && $user_id_cc != 0){
				$cc_f = 1;
			}else if($operation == 1 && $add_no != 0 && $user_id_cc != 0){
				if ($f_rows_chg["next".$add_no."_data"] != "*0-0"){
					$cc_f = 0;
					for ($i = 1; $i <= 5; $i++){
						if ($f_rows_chg["next".$add_no."_cc".$i."_data"] == "*0-0"){
							$sql_up = "update ".$schema.".m_workflow_tmp set next".$add_no."_cc".$i."_data = '*".$user_id_cc."-1'".$set_date." where user_id = ".$login_staff_id;
							sqlExec($sql_up);
							break;
						}
					}
				}
			}

			if ($user_id_apply != 0){
				$sql_set = "";
	//1つずらす
				if ($operation == 0){
					if ($f_rows_chg["next".$add_no."_data"] != "*0-0"){
						$target_str = $f_rows_chg["next".($add_no+1)."_data"];
						$sql_set .= "next".($add_no+1)."_data = '*0-0'";
						$sql_set .= ", next".($add_no+1)."_cc1_data = '*0-0'";
						$sql_set .= ", next".($add_no+1)."_cc2_data = '*0-0'";
						$sql_set .= ", next".($add_no+1)."_cc3_data = '*0-0'";
						$sql_set .= ", next".($add_no+1)."_cc4_data = '*0-0'";
						$sql_set .= ", next".($add_no+1)."_cc5_data = '*0-0'";
						for ($i = ($add_no+1); $i < 10; $i++){
							if ($i == 1){
								$sql_set .= ", next".($i+1)."_data = '".workflow_agree_update_all($f_rows_chg["next".$i."_data"], 1)."'";
							}else{
								$sql_set .= ", next".($i+1)."_data = '".$f_rows_chg["next".$i."_data"]."'";
							}
							for ($j = 1; $j <= 5; $j++){
								$sql_set .= ", next".($i+1)."_cc".$j."_data = '".$f_rows_chg["next".$i."_cc".$j."_data"]."'";
							}
						}
						$sql_up = "update ".$schema.".m_workflow_tmp set ".$sql_set."".$set_date." where user_id = ".$login_staff_id;
						sqlExec($sql_up);
						$up_str = workflow_add_route($add_no+1, $operation, "*0-0", $user_id_apply, $tasuuketsu);
						$sql_up = "update ".$schema.".m_workflow_tmp set next".($add_no+1)."_data = '".$up_str."'".$set_date." where user_id = ".$login_staff_id;
						sqlExec($sql_up);
					}

					if ($cc_f == 1){
						$sql_chg2 = "select * from ".$schema.".m_workflow_tmp where user_id = ".$login_staff_id;
				       		$rows_chg2 = getList($sql_chg2);
						$f_rows_chg2 = $rows_chg2[0];
						if ($f_rows_chg2){
							for ($i = 1; $i <= 5; $i++){
								if ($f_rows_chg2["next".($add_no+1)."_cc".$i."_data"] == "*0-0"){
									$sql_up = "update ".$schema.".m_workflow_tmp set next".($add_no+1)."_cc".$i."_data = '*".$user_id_cc."-1'".$set_date." where user_id = ".$login_staff_id;
									sqlExec($sql_up);
									break;
								}
							}
						}
					}
				}else{
					$target_str = $f_rows_chg["next".$add_no."_data"];
					$up_str = workflow_add_route($add_no, $operation, $target_str, $user_id_apply, $tasuuketsu);
					$sql_up = "update ".$schema.".m_workflow_tmp set next".$add_no."_data = '".$up_str."'".$set_date." where user_id = ".$login_staff_id;
					sqlExec($sql_up);
				}
			}

		}else{
			if ($user_id_kian != 0){
				$cc1 = $user_id_kian."-2";
			}else{
				$cc1 = 0;
			}
			if (($add_no+1) == 1){
				$f = 2;
			}else{
				$f = 1;
			}
			for ($i = 1; $i <= 10; $i++){
				$flow[$i][0] = "*0-0";
				if ($user_id_apply != 0){
					if ($i == ($add_no+1)){
						$flow[$i][0] = "*".$user_id_apply."-".$f."=".$tasuuketsu;
					}
				}
				for ($j = 1; $j <= 5; $j++){
					$flow[$i][$j] = "*0-0";
					if ($i == ($add_no+1) && $j == 1){
						if ($user_id_cc != 0){
							$flow[$i][$j] = "*".$user_id_cc."-1";
						}
					}

				}
			}

			m_workflow_insert(1, $schema, $chg_org, $chg_flow, $flow, $cc1, 0, 0, 0, 0, $chg_date, $login_staff_id);
/*
			$sql = "INSERT INTO ".$schema.".m_workflow_tmp ";

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

			$sql .= "VALUES(0, ".$chg_org.", ".$chg_flow.", 0, '".
						$flow[1][0]."', '".$flow[1][1]."', '".$flow[1][2]."', '".$flow[1][3]."', '".$flow[1][4]."', '".$flow[1][5]."', '".
						$flow[2][0]."', '".$flow[2][1]."', '".$flow[2][2]."', '".$flow[2][3]."', '".$flow[2][4]."', '".$flow[2][5]."', '".
						$flow[3][0]."', '".$flow[3][1]."', '".$flow[3][2]."', '".$flow[3][3]."', '".$flow[3][4]."', '".$flow[3][5]."', '".
						$flow[4][0]."', '".$flow[4][1]."', '".$flow[4][2]."', '".$flow[4][3]."', '".$flow[4][4]."', '".$flow[4][5]."', '".
						$flow[5][0]."', '".$flow[5][1]."', '".$flow[5][2]."', '".$flow[5][3]."', '".$flow[5][4]."', '".$flow[5][5]."', '".
						$flow[6][0]."', '".$flow[6][1]."', '".$flow[6][2]."', '".$flow[6][3]."', '".$flow[6][4]."', '".$flow[6][5]."', '".
						$flow[7][0]."', '".$flow[7][1]."', '".$flow[7][2]."', '".$flow[7][3]."', '".$flow[7][4]."', '".$flow[7][5]."', '".
						$flow[8][0]."', '".$flow[8][1]."', '".$flow[8][2]."', '".$flow[8][3]."', '".$flow[8][4]."', '".$flow[8][5]."', '".
						$flow[9][0]."', '".$flow[9][1]."', '".$flow[9][2]."', '".$flow[9][3]."', '".$flow[9][4]."', '".$flow[9][5]."', '".
						$flow[10][0]."', '".$flow[10][1]."', '".$flow[10][2]."', '".$flow[10][3]."', '".$flow[10][4]."', '".$flow[10][5]."', CURRENT_TIMESTAMP, '".$chg_date."', ".
						"'".$cc1."', '0', '0', '0', '0', ".$login_staff_id.")";
			sqlExec($sql);
*/
		}

}else if($cd == 2){	//登録し workflow_root_dsp に戻る

	$sql = "insert into ".$schema.".m_workflow select * from ".$schema.".m_workflow_tmp where user_id = ".$login_staff_id;
	sqlExec($sql);
	$sql = "delete from ".$schema.".m_workflow_tmp where user_id = ".$login_staff_id;
	sqlExec($sql);

//	echo '<script type="text/javascript">parent.location.reload();</script>';
	echo '<script type="text/javascript">parent.location.href=parent.location.href;</script>';



//header( "HTTP/1.1 301 Moved Permanently" ); 
//header( "Location: workflow_root_dsp.php?target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org); 

}else if($cd == 3){	//破棄
	$sql = "delete from ".$schema.".m_workflow_tmp where user_id = ".$login_staff_id;
	$rows = getList($sql);
	echo '<script type="text/javascript">parent.location.reload();</script>';
}else if($cd == 4){	//変更

	$sql = "delete from ".$schema.".m_workflow  where target_date = '".$target_date2."' and busho_code = ".$chg_org." and flow_doc_code = ".$chg_flow;
	sqlExec($sql);
	$sql = "insert into ".$schema.".m_workflow select * from ".$schema.".m_workflow_tmp where user_id = ".$login_staff_id;
	sqlExec($sql);
	$sql = "delete from ".$schema.".m_workflow_tmp where user_id = ".$login_staff_id;
	sqlExec($sql);

	echo '<script type="text/javascript">parent.location.reload();</script>';


}



$workflow_html = "";

//表示 m_workflow_tmp

$workflow_html .= workflow_route_tmp($login_staff_id);

////////////////////////

		$workflow_html .= "<form id=\"dataForm\" name=\"dataFormSend\" action=\"workflow_create.php?chg_f=".$chg_f."&cd=1&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&chg_flow=".$chg_flow."&chg_org=".$chg_org."&target_date2=".$target_date2."\" method=\"post\">\n";

		$workflow_html .= "<tr>\n";
		$workflow_html .= "<th>適用開始</th>\n";

		$sql_chg = "select * from ".$schema.".m_workflow_tmp where user_id = ".$login_staff_id;
     		$rows_chg = getList($sql_chg);//
		$f_rows_chg = $rows_chg[0];
		if ($f_rows_chg){
			$workflow_html .= "<td colspan=3><input type='text' id='chg_date' name='chg_date' size='10' value=".$f_rows_chg["target_date"]."></td>\n";
		}else{
			$workflow_html .= "<td colspan=3><input type='text' id='chg_date' name='chg_date' size='10' value=".$target_date2."></td>\n";
		}

		$workflow_html .= "</tr>\n";

		$workflow_html .= "<tr>\n";
		$workflow_html .= "<th width=170>NO</th>\n";
		$workflow_html .= "<th width=260>承認者</th>\n";
		$workflow_html .= "<th width=70>多数決</th>\n";
		$workflow_html .= "<th width=260>参照者</th>\n";
		$workflow_html .= "</tr>\n";

		$workflow_html .= "<tr>\n";
		$workflow_html .= "<td align=center>0</td>\n";
		$workflow_html .= "<td align=left>起案者</td>\n";
		$workflow_html .= "<td align=center> </td>\n";
		$workflow_html .= "<td>".f_master_org_select_name("org_sel_kian()", 0, "org_id_kian")."<br />\n";
		$workflow_html .= f_user_select_name("user_id_kian")."</td>\n";
		$workflow_html .= "</tr>\n";

		$workflow_html .= "<tr>\n";
		$workflow_html .= "<td>NO&nbsp;".f_zero_ten($add_no)."&nbsp;".f_operation($operation)."</td>\n";
		$workflow_html .= "<td>".f_master_org_select_name("org_sel_apply()", 0, "org_id_apply")."<br />\n";
		$workflow_html .= f_user_select_name("user_id_apply")."</td>\n";
		$workflow_html .= "<td align=center><input type='text' name='tasuuketsu' size=1></td>\n";
		$workflow_html .= "<td>".f_master_org_select_name("org_sel_cc()", 0, "org_id_cc")."<br />\n";
		$workflow_html .= f_user_select_name("user_id_cc")."</td>\n";
		$workflow_html .= "</tr>\n";
		$workflow_html .= "<tr>\n";
		$workflow_html .= "<td colspan=4 align=center><input type='submit' value='追加'></td>\n";
		$workflow_html .= "</tr>\n";
		$workflow_html .= "</form>\n";

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

<script type="text/javascript" src="./js/sales/jquery/jquery.min.js"></script>
<script type="text/javascript" src="./js/sales/jquery/jquery-ui.min.js"></script>
<script type="text/javascript" src="./js/sales/jquery/jquery.ui.datepicker-ja.min.js"></script>
<link rel="stylesheet" type="text/css" href="./css/sales/sunny/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="./js/sales/jquery/jquery.maskedinput.js"></script>
<script type="text/javascript" src="./js/sales/jquery/jquery.validate.min.js"></script>
<script type="text/javascript" src="./js/sales/jquery/jquery.validate.js"></script>
<script type="text/javascript" src="./js/sales/jquery/jquery.maskedinput.js"></script>

<style type="text/css">
label.error {
    color: red;
}
</style>

<script type="text/javascript">
function org_sel_apply(){
		$.ajax({
			type: "POST",
			url: "ajax_org_create.php?cd=1",
    			data: {
        			org_id_apply: $(".org_id_apply").val()
    			},
			cache: false,
			async: false,
			success: function(html){
				$(".user_id_apply").html(html);
			}
		});
}
function org_sel_cc(){
		$.ajax({
			type: "POST",
			url: "ajax_org_create.php?cd=2",
    			data: {
        			org_id_cc: $(".org_id_cc").val()
    			},
			cache: false,
			async: false,
			success: function(html){
				$(".user_id_cc").html(html);
			}
		});
}
function org_sel_kian(){
		$.ajax({
			type: "POST",
			url: "ajax_org_create.php?cd=3",
    			data: {
        			org_id_kian: $(".org_id_kian").val()
    			},
			cache: false,
			async: false,
			success: function(html){
				$(".user_id_kian").html(html);
			}
		});
}
$(function(){
	$("#chg_date").datepicker({dateFormat:'yy-mm-dd'});
	$("#chg_date").mask("9999-99-99");
});
</script>
	</head>

    <body id="top" style="background-color:#FFFFFF;">
    <div id="sb-site2" style="background-color:#FFFFFF;">
			<!-- 検索結果リストエリア width指定なし serchListAreaFree -->
			<div class="serchEditArea">
<table width=100% style="border-style: none;">
<tbody>
<tr>
<td width=50% width=100% style="border-style: none;vertical-align: top;">
				<table>
					<tbody>
<?= $workflow_html ?>
					</tbody>
				</table>
</td>
</tr>
</table>
<br />
<p align=center>
<?php
if ($chg_f == 1){
	echo "<button type=\"button\" onclick=\"location.href='workflow_create.php?cd=4&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&chg_flow=".$chg_flow."&chg_org=".$chg_org."&target_date2=".$target_date2."'\">ワークフローを変更</button>";
}else{
	echo "<button type=\"button\" onclick=\"location.href='workflow_create.php?cd=2&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&chg_flow=".$chg_flow."&chg_org=".$chg_org."&target_date2=".$target_date2."'\">ワークフローに登録</button>";
}
?>
&nbsp;&nbsp;<button type="button" onclick="location.href='workflow_create.php?cd=3&target_date=<?= $target_date ?>&target_flow=<?= $target_flow ?>&target_org=<?= $target_org ?>&chg_flow=<?= $chg_flow ?>&chg_org=<?= $chg_org ?>&target_date2=<?= $target_date2 ?>'">取り消し</button></p>
			</div><!-- /.serchListAreaFree -->
		</div><!-- /#sb-site -->
    </body>
</html>
