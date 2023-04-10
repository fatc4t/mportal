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
$target_date2 = (isset($_GET['target_date2'])) ? $_GET['target_date2'] : "";
$target_date = (isset($_GET['target_date'])) ? $_GET['target_date'] : "";
$target_flow = (isset($_GET['target_flow'])) ? $_GET['target_flow'] : 0;
$target_org = (isset($_GET['target_org'])) ? $_GET['target_org'] : 0;
$chg_org = (isset($_GET['chg_org'])) ? $_GET['chg_org'] : 0;
$up = (isset($_GET['up'])) ? $_GET['up'] : 0;
$target_staff = (isset($_GET['target_staff'])) ? $_GET['target_staff'] : 0;
$chg_flow = (isset($_GET['chg_flow'])) ? $_GET['chg_flow'] : 0;
$wid = (isset($_GET['wid'])) ? $_GET['wid'] : 0;
$kengen = (isset($_GET['kengen'])) ? $_GET['kengen'] : 0;
$kengen_no = (isset($_GET['kengen_no'])) ? $_GET['kengen_no'] : 0;
$cc_no = (isset($_GET['cc_no'])) ? $_GET['cc_no'] : 0;
$ok_f = (isset($_GET['ok_f'])) ? $_GET['ok_f'] : 0;
$home = (isset($_GET['home'])) ? $_GET['home'] : 0;
$hani = (isset($_POST['hani'])) ? $_POST['hani'] : 0;
$org_id = (isset($_POST['org_id'])) ? $_POST['org_id'] : 0;
$user_list = (isset($_POST['user_list'])) ? $_POST['user_list'] : 0;
$chg_date = (isset($_POST['chg_date'])) ? $_POST['chg_date'] : "";

$chg_f = (isset($_GET['chg_f'])) ? $_GET['chg_f'] : 0;

if ($ok_f == 1 && $user_list != 0){	//入れ替える
//範囲 1:申請文書指定 2:同じ権限全部 3:全部
//echo "範囲:".$hani."<br />\n";

	if ($hani == 1){	//1変更元(組織・権限・申請文書・対象者指定)
//echo "1変更元(申請文書指定)<br />\n";
//echo "&nbsp;適用日：".$target_date2."<br />\n";
//echo "&nbsp;申請文書：".$chg_flow."<br />\n";
//echo "&nbsp;組織：".$chg_org."<br />\n";
//echo "&nbsp;社員：".$target_staff."<br />\n";
//echo "&nbsp;権限：".$kengen."<br />\n";
//echo "&nbsp;番目：".$kengen_no."<br />\n";
//echo "&nbsp;CC番目：".$cc_no."<br />\n";

		if ($chg_date == ""){	//上書き
			if ($kengen == 1){		//承認者
				$sql = "SELECT next".$kengen_no."_data FROM ".$schema.".m_workflow WHERE target_date = '".$target_date2."' and busho_code = ".$chg_org." and flow_doc_code = ".$chg_flow;
				$result = getList($sql);
				$f_rows = $result[0];
				$data = $f_rows["next".$kengen_no."_data"];
				$data_c = workflow_root_change($data, $target_staff, $user_list);
				$sql = "update ".$schema.".m_workflow set next".$kengen_no."_data = '".$data_c."' where target_date = '".$target_date2."' and busho_code = ".$chg_org." and flow_doc_code = ".$chg_flow;
				sqlExec($sql);
			}else if ($kengen == 2){	//CC
				$sql = "update ".$schema.".m_workflow set next".$kengen_no."_cc".$cc_no."_data = '*".$user_list."-1' where target_date = '".$target_date2."' and busho_code = ".$chg_org." and flow_doc_code = ".$chg_flow;
				sqlExec($sql);
			}else{				//起案者CC
				$sql = "update ".$schema.".m_workflow set cc".$cc_no." = ".$user_list." where target_date = '".$target_date2."' and busho_code = ".$chg_org." and flow_doc_code = ".$chg_flow;
				sqlExec($sql);
			}
		}else{	//新規追加
			if ($kengen == 1){		//承認者
				$sql = "SELECT m_workflow_id, next".$kengen_no."_data FROM ".$schema.".m_workflow WHERE target_date = '".$target_date2."' and busho_code = ".$chg_org." and flow_doc_code = ".$chg_flow;
				$result = getList($sql);
				$f_rows = $result[0];
				$id = $f_rows["m_workflow_id"];
				$data = $f_rows["next".$kengen_no."_data"];
				$data_c = workflow_root_change($data, $target_staff, $user_list);
				$where = "where m_workflow_id = '".$id."'";
				$last_id = m_workflow_copy_insert($schema, $where);

				$sql = "update ".$schema.".m_workflow set next".$kengen_no."_data = '".$data_c."', target_date = '".$chg_date."' where m_workflow_id = '".$last_id."'";
				sqlExec($sql);
			}else if ($kengen == 2){	//CC
				$where = "where target_date = '".$target_date2."' and busho_code = ".$chg_org." and flow_doc_code = ".$chg_flow;
				$last_id = m_workflow_copy_insert($schema, $where);

				$sql = "update ".$schema.".m_workflow set next".$kengen_no."_cc".$cc_no."_data = '*".$user_list."-1', target_date = '".$chg_date."' where m_workflow_id = '".$last_id."'";
				sqlExec($sql);
			}else{				//起案者CC
				$where = "where target_date = '".$target_date2."' and busho_code = ".$chg_org." and flow_doc_code = ".$chg_flow;
				$last_id = m_workflow_copy_insert($schema, $where);

				$sql = "update ".$schema.".m_workflow set cc".$cc_no." = '".$user_list."', target_date = '".$chg_date."' where m_workflow_id = '".$last_id."'";
				sqlExec($sql);
			}
		}
	}else if ($hani == 2){	//2変更元(表示されているルートの同じ権限の対象者全て)
//echo "2変更元(申請文書指定)<br />\n";
//echo "&nbsp;適用日：".$target_date2."<br />\n";
//echo "&nbsp;申請文書：".$target_flow."<br />\n";
//echo "&nbsp;組織：".$target_org."<br />\n";
//echo "&nbsp;社員：".$target_staff."<br />\n";
//echo "&nbsp;権限：".$kengen."<br />\n";
		if ($chg_date == ""){	//上書き
			$where = "target_date = '".$target_date2."'";
			if ($target_org != 0){
				$where .= " and busho_code = '".$target_org."'";
			}
			if ($target_flow != 0){
				$where .= " and flow_doc_code = '".$target_flow."'";
			}

			if ($kengen == 1){		//承認者
				shouninsha_update($schema, $target_staff, $user_list, $where);
			}else if ($kengen == 2){	//CC
				cc_update($schema, $target_staff, $user_list, $where);
			}else{	//起案者CC
				kian_cc_update($schema, $target_staff, $user_list, $where);
			}
		}else{	//新規追加
			$where = "target_date = '".$target_date2."'";
			if ($target_org != 0){
				$where .= " and busho_code = '".$target_org."'";
			}
			if ($target_flow != 0){
				$where .= " and flow_doc_code = '".$target_flow."'";
			}

			if ($kengen == 1){		//承認者
				shouninsha_insert_update($schema, $target_staff, $user_list, $where);
			}else if ($kengen == 2){	//CC
				cc_insert_update($schema, $target_staff, $user_list, $where);
			}else{	//起案者CC
				kian_cc_insert_update($schema, $target_staff, $user_list, $where);
			}
		}
	}else if ($hani == 3){	//3変更元(表示されているルートの対象者全部)
//echo "3変更元(全部)<br />\n";
//echo "&nbsp;適用日：".$target_date2."<br />\n";
//echo "&nbsp;申請文書：".$target_flow."<br />\n";
//echo "&nbsp;組織：".$target_org."<br />\n";
//echo "&nbsp;社員：".$target_staff."<br />\n";
		if ($chg_date == ""){	//上書き
			$where = "target_date = '".$target_date2."'";
			if ($target_org != 0){
				$where .= " and busho_code = '".$target_org."'";
			}
			if ($target_flow != 0){
				$where .= " and flow_doc_code = '".$target_flow."'";
			}

			shouninsha_update($schema, $target_staff, $user_list, $where);
			cc_update($schema, $target_staff, $user_list, $where);
			kian_cc_update($schema, $target_staff, $user_list, $where);
		}else{	//新規追加
			$where = "target_date = '".$target_date2."'";
			if ($target_org != 0){
				$where .= " and busho_code = '".$target_org."'";
			}
			if ($target_flow != 0){
				$where .= " and flow_doc_code = '".$target_flow."'";
			}

			shouninsha_insert_update($schema, $target_staff, $user_list, $where);
			cc_insert_update($schema, $target_staff, $user_list, $where);
			kian_cc_insert_update($schema, $target_staff, $user_list, $where);
		}
	}

//echo "変更後<br />\n";
//echo "&nbsp;組織：".$org_id."<br />\n";
//echo "&nbsp;社員：".$user_list."<br />\n";
//echo "&nbsp;適用日：".$chg_date."<br />\n";
}else if ($ok_f == 2){	//削除
	if ($hani == 1){	//1対象ルート指定削除
		$sql = "delete from ".$schema.".m_workflow where m_workflow_id = '".$wid."'";
		sqlExec($sql);
	}else if ($hani == 2){	//2表示されているルートの同じ適用年月日全て)
		$where = "target_date = '".$target_date2."'";
		if ($target_org != 0){
			$where .= " and busho_code = '".$target_org."'";
		}
		if ($target_flow != 0){
			$where .= " and flow_doc_code = '".$target_flow."'";
		}
		$sql = "delete from ".$schema.".m_workflow where ".$where;
		sqlExec($sql);
	}
}else if ($ok_f == 3 && $target_staff != 0){	//対象者を削除する

			if ($kengen == 1){		//承認者
				workflow_root_target_delete_shounin($schema, $target_date2, $chg_flow, $target_org, $target_staff, $kengen_no);
			}else if ($kengen == 2){	//CC
				$sql = "SELECT * FROM ".$schema.".m_workflow WHERE busho_code = ".$target_org." and flow_doc_code = ".$chg_flow." and target_date = '".$target_date2."'";
			       	$rows = getList($sql);
			        if($rows){
					$row = $rows[0];
					$sql_up = "update ".$schema.".m_workflow set ";
					for ($j = $cc_no; $j < 5; $j++){
						$sql_up .= "next".$kengen_no."_cc".$j."_data = '".$row["next".$kengen_no."_cc".($j+1)."_data"]."', ";
					}
					$sql_up .= "next".$kengen_no."_cc".$j."_data = '*0-0' ";
					$sql_up .= "WHERE busho_code = ".$target_org." and flow_doc_code = ".$chg_flow." and target_date = '".$target_date2."'";
					sqlExec($sql_up);
				}
			}else{	//起案者CC
				$sql = "SELECT * FROM ".$schema.".m_workflow WHERE busho_code = ".$target_org." and flow_doc_code = ".$chg_flow." and target_date = '".$target_date2."'";
			       	$rows = getList($sql);
			        if($rows){
					$row = $rows[0];
					$sql_up = "update ".$schema.".m_workflow set ";
					for ($j = $cc_no; $j < 5; $j++){
						$sql_up .= "cc".$j." = '".$row["cc".($j+1)]."', ";
					}
					$sql_up .= "cc".$j." = '0' ";
					$sql_up .= "WHERE busho_code = ".$target_org." and flow_doc_code = ".$chg_flow." and target_date = '".$target_date2."'";
					sqlExec($sql_up);
				}
			}


//echo "適用日：".$target_date2."<br />\n";
//echo "申請文書：".$chg_flow."<br />\n";
//echo "組織：".$chg_org."<br />\n";
//echo "社員：".$target_staff."<br />\n";
//echo "権限：".$kengen."<br />\n";
//echo "順位：".$kengen_no."<br />\n";
}


$kyoten = 1;


if ($target_date == ""){
	$target_date = date("Y-m-d");
}

//$target_date2 = target_tekiyou("m_workflow", $target_date);

$sql_where = "";
if ($target_flow != 0){
	$sql_where .= " and flow_doc_code = ".$target_flow;
}
if ($target_org != 0){
	$sql_where .= " and busho_code = ".$target_org;
}

$b_id = "";
$b_name = "";
$s_name = "";
$kengen_name = "";

switch($kengen)
{
	case 1:
		$kengen_name = "承認者";
		break;
	case 2:
		$kengen_name = "参照者";
		break;
	case 3:
		$kengen_name = "参照者";
		break;
	default:
		$kengen_name = "";
		break;
}

$workflow_change_html = "";

if ($up == 1 || $up == 2){

	$workflow_change_html .= "<tr>\n";
	$workflow_change_html .= "<th colspan=4>選択したルート</td>\n";
	$workflow_change_html .= "</tr>\n";

	$workflow_change_html .= "<tr>\n";
	$workflow_change_html .= "<th width=150>適用年月日</th>\n";
	$workflow_change_html .= "<td colspan=3 align=left>".$target_date2."</td>\n";
	$workflow_change_html .= "</tr>\n";

	$workflow_change_html .= "<tr>\n";
	$workflow_change_html .= "<th width=150>申請書</th>\n";
	$workflow_change_html .= "<td colspan=3 align=left>".f_code_dsp(99, $chg_flow)."</td>\n";
	$workflow_change_html .= "</tr>\n";

	if($up == 1){
		$b_id = f_staff_busho_get($target_staff);
		$b_name = org_name_dsp($b_id);
		$s_name = f_staff_name_get($target_staff);

		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<th width=150>権限</th>\n";
		$workflow_change_html .= "<td colspan=3 align=left>".$kengen_name."</td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<th width=150>組織名</th>\n";
		$workflow_change_html .= "<td width=350 align=left>".$b_name."</td>\n";
		$workflow_change_html .= "<th width=150>対象者</th>\n";
		$workflow_change_html .= "<td width=150 align=left>".$s_name."</td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<td colspan=4 align=center><button type='button' onclick=\"location.href='workflow_root_dsp.php?ok_f=3&kengen=".$kengen."&kengen_no=".$kengen_no."&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$target_staff."&chg_flow=".$chg_flow."&chg_org=".$chg_org."&target_date2=".$target_date2."&cc_no=".$cc_no."'\">対象者を削除する</button></td>\n";
		$workflow_change_html .= "</tr>\n";


	}

	$workflow_change_html .= "<tr>\n";
	$workflow_change_html .= "<td colspan=4 align=center>↓</td>\n";
	$workflow_change_html .= "</tr>\n";
	if ($up == 1){	//入れ替える
		$workflow_change_html .= "<form method='POST' action='workflow_root_dsp.php?ok_f=1&kengen=".$kengen."&kengen_no=".$kengen_no."&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&chg_org=".$chg_org."&target_staff=".$target_staff."&chg_flow=".$chg_flow."&target_date2=".$target_date2."&cc_no=".$cc_no."'>";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<th colspan=4>人を入れ替える</td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<th>対象適用年月日</th>\n";
		$workflow_change_html .= "<td colspan=3 align=left>".$target_date2."</td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<th rowspan=3>変更の範囲</th>\n";
		$workflow_change_html .= "<td colspan=3><input type='radio' name=hani value=1 checked>&nbsp;<font color=red>".f_code_dsp(99, $chg_flow)."</font>&nbsp;の指定した対象のみ</td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<td colspan=3><input type='radio' name=hani value=2>&nbsp;表示されているルートの<font color=red>".$kengen_name."の対象者全て</font></td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<td colspan=3><input type='radio' name=hani value=3>&nbsp;表示されているルートの<font color=red>対象者全て</font></td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<th width=150>組織名</th>\n";
		$workflow_change_html .= "<td width=350>".f_master_org_select(0, "org_id")."</td>\n";
		$workflow_change_html .= "<th width=150>氏名</th>\n";
		$workflow_change_html .= "<td width=150>".f_user_select()."</td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<th>適用開始</th>\n";
		$workflow_change_html .= "<td colspan=3><input type='text' id='chg_date' name='chg_date' size='10'>&nbsp;<font color=red>(入力しない場合は同じ適用年月日で上書きします)</font></td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<td colspan=4 align=center><input type='submit' value='人を入れ替える'></td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "</form>\n";
	}else if ($up == 2){	//適用年月日を選択して削除する
		$workflow_change_html .= "<form method='POST' action='workflow_root_dsp.php?ok_f=2&kengen=".$kengen."&kengen_no=".$kengen_no."&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$target_staff."&chg_flow=".$chg_flow."&target_date2=".$target_date2."&cc_no=".$cc_no."&wid=".$wid."'>";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<th colspan=4>選択したルートを削除する</td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<th width=150>対象適用年月日</th>\n";
		$workflow_change_html .= "<td colspan=3 align=left width=650>".$target_date2."</td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<th rowspan=2>削除の範囲</th>\n";
		$workflow_change_html .= "<td colspan=3><input type='radio' name=hani value=1 checked>&nbsp;<font color=red>".f_code_dsp(99, $chg_flow)."</font>&nbsp;の指定した適用年月日&nbsp;".$target_date2."&nbsp;のみ</td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<td colspan=3><input type='radio' name=hani value=2>&nbsp;表示されているルートの<font color=red>適用年月日&nbsp;".$target_date2."&nbsp;全て</font></td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "<tr>\n";
		$workflow_change_html .= "<td colspan=4 align=center><input type='submit' value='削除する'>&nbsp;&nbsp;<button type='button' onclick=\"location.href='workflow_root_dsp.php?up=4&chg_f=1&kengen=".$kengen."&kengen_no=".$kengen_no."&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$target_staff."&chg_flow=".$chg_flow."&chg_org=".$chg_org."&target_date2=".$target_date2."&cc_no=".$cc_no."'\">ルートを変更する</button></td>\n";
		$workflow_change_html .= "</tr>\n";
		$workflow_change_html .= "</form>\n";

	$sql_del = "select * from ".$schema.".m_workflow_tmp where user_id = '".$login_staff_id."'";
       	$rows_del = getList($sql_del);
	$f_rows_del = $rows_del[0];
	if ($f_rows_del){
		$sql_del = "delete from ".$schema.".m_workflow_tmp where user_id = ".$login_staff_id;
		sqlExec($sql_del);
	}

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

			m_workflow_insert(1, $schema, $f_rows["busho_code"], $f_rows["flow_doc_code"], $flow, $f_rows["cc1"], $f_rows["cc2"], $f_rows["cc3"], $f_rows["cc4"], $f_rows["cc5"], $f_rows["target_date"], $login_staff_id);

		}

	}
}else if ($up == 3){	//ルートを作る
//操作者は１レコードのm_workflow_tmpしか使えない
	$sql_del = "select * from ".$schema.".m_workflow_tmp where user_id = '".$login_staff_id."'";
       	$rows_del = getList($sql_del);
	$f_rows_del = $rows_del[0];
	if ($f_rows_del){
		$sql_del = "delete from ".$schema.".m_workflow_tmp where user_id = ".$login_staff_id;
		sqlExec($sql_del);
	}

	$workflow_change_html .= "<tr>\n";
	$workflow_change_html .= "<th colspan=4>ルートを作成する</td>\n";
	$workflow_change_html .= "</tr>\n";

	$workflow_change_html .= "<tr>\n";
	$workflow_change_html .= "<th width=150>申請書</th>\n";
	$workflow_change_html .= "<td colspan=3 align=left>".f_code_dsp(99, $chg_flow)."</td>\n";
	$workflow_change_html .= "</tr>\n";

	$workflow_change_html .= "<tr>\n";
	$workflow_change_html .= "<th width=150>組織名</th>\n";
	$workflow_change_html .= "<td align=left colspan=3>".org_name_dsp($chg_org)."</td>\n";
	$workflow_change_html .= "</tr>\n";

	$workflow_change_html .= "<tr>\n";
	$workflow_change_html .= "<td align=left colspan=4>\n";

	$workflow_change_html .= "<iframe src='workflow_create.php?chg_f=".$chg_f."&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&chg_flow=".$chg_flow."&chg_org=".$chg_org."&target_date2=".$target_date2."' marginwidth=0 marginheight=0 style=\"border: 0px; width: 790px; height: 450px; margin:0; padding: 0;\" scrolling=\"yes\" frameborder=\"no\"></iframe>\n";

	$workflow_change_html .= "</td>\n";
	$workflow_change_html .= "</tr>\n";
}else if ($up == 4){	//ルートを変更する
	$sql_del = "select * from ".$schema.".m_workflow_tmp where user_id = '".$login_staff_id."'";
       	$rows_del = getList($sql_del);
	$f_rows_del = $rows_del[0];
	if ($f_rows_del){
	$workflow_change_html .= "<tr>\n";
	$workflow_change_html .= "<th colspan=4>ルートを作成する</td>\n";
	$workflow_change_html .= "</tr>\n";

	$workflow_change_html .= "<tr>\n";
	$workflow_change_html .= "<th width=150>申請書</th>\n";
	$workflow_change_html .= "<td colspan=3 align=left>".f_code_dsp(99, $chg_flow)."</td>\n";
	$workflow_change_html .= "</tr>\n";

	$workflow_change_html .= "<tr>\n";
	$workflow_change_html .= "<th width=150>組織名</th>\n";
	$workflow_change_html .= "<td align=left colspan=3>".org_name_dsp($chg_org)."</td>\n";
	$workflow_change_html .= "</tr>\n";

	$workflow_change_html .= "<tr>\n";
	$workflow_change_html .= "<td align=left colspan=4>\n";

	$workflow_change_html .= "<iframe src='workflow_create.php?chg_f=".$chg_f."&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&chg_flow=".$chg_flow."&chg_org=".$chg_org."&target_date2=".$target_date2."' marginwidth=0 marginheight=0 style=\"border: 0px; width: 790px; height: 450px; margin:0; padding: 0;\" scrolling=\"yes\" frameborder=\"no\"></iframe>\n";

	$workflow_change_html .= "</td>\n";
	$workflow_change_html .= "</tr>\n";
	}
}

	$workflow_html = "<tr>\n";
	$workflow_html .= "<th colspan=4>".$target_date."&nbsp;に有効なルート</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<th width=50>NO</th>\n";
	$workflow_html .= "<th width=150>承認者</th>\n";
	$workflow_html .= "<th width=80>多数決</th>\n";
	$workflow_html .= "<th width=150>参照者</th>\n";
	$workflow_html .= "</tr>\n";


$sql_mcode = "SELECT no, name FROM ".$schema.".m_code WHERE cd = 99 and no != 0 ORDER BY disp_order";
$cnt_mcode = 0;
$rows_mcode = getList($sql_mcode);

	if($rows_mcode){
		while($row_mcode = $rows_mcode[$cnt_mcode]) {

			if ($target_flow != 0){
				if ($row_mcode["no"] == $target_flow){
				}else{
					goto skip3;
				}
			}

			$sql_org = "SELECT * FROM ".$schema.".m_organization_detail left join ".$schema.".m_organization on ".$schema.".m_organization_detail.organization_id = ".$schema.".m_organization.organization_id where ".$schema.".m_organization.is_del = 0 ORDER BY ".$schema.".m_organization.disp_order DESC";
			$cnt_org = 0;
		        $rows_org = getList($sql_org);
			if($rows_org){
				while($row_org = $rows_org[$cnt_org]) {

					if ($target_org != 0){
						if ($row_org["organization_detail_id"] == $target_org){
						}else{
							goto skip2;
						}
					}
			//組織・申請文書ごとに適用年月日を求める
					$target_date2 = target_tekiyou_where("m_workflow", $target_date, "flow_doc_code = ".$row_mcode["no"]." and busho_code = ".$row_org["organization_detail_id"]);

if ($target_date2 == ""){
//ルートがない
					$workflow_html .= "<tr>\n";
//				      	$workflow_html .= "<th colspan=4>".code_name_dsp(0, org_id_code_get($row_org["organization_detail_id"]))."&nbsp;&nbsp;&nbsp;".f_code_dsp(99, $row_mcode["no"])."&nbsp;&nbsp;(<a href='workflow_root_dsp.php?up=3&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&chg_flow=".$row_mcode["no"]."&target_date2=".$target_date2."&chg_org=".$row_org["organization_detail_id"]."'>ルートを作る</a>)</th>\n";
				      	$workflow_html .= "<th colspan=4>".code_name_dsp(0, org_id_code_get($row_org["organization_detail_id"]))."&nbsp;&nbsp;&nbsp;".f_code_dsp(99, $row_mcode["no"])."&nbsp;&nbsp;(<a href='workflow_root_dsp.php?up=3&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&chg_flow=".$row_mcode["no"]."&target_date2=".date("Y-m-d")."&chg_org=".$row_org["organization_detail_id"]."'>ルートを作る</a>)</th>\n";

					$workflow_html .= "</tr>\n";
	goto skip2;
}
					$sql = "SELECT * FROM ".$schema.".m_workflow where target_date = '".$target_date2."' and flow_doc_code = ".$row_mcode["no"]." and busho_code = ".$row_org["organization_detail_id"]." ORDER BY busho_code, flow_doc_code asc";

					$cnt = 0;
				       	$rows = getList($sql);

					while($f_rows = $rows[$cnt]) {

						$cc1 = $f_rows["cc1"];
						$cc2 = $f_rows["cc2"];
						$cc3 = $f_rows["cc3"];
						$cc4 = $f_rows["cc4"];
						$cc5 = $f_rows["cc5"];
						for ($i = 1; $i <= 10; $i++){
							$workflow_cc_data[$i] = 0;

							for ($j=0; $j<6; $j++){
								$workflow_data[$i][$j][0] = "";
								$workflow_data[$i][$j][1] = 0;
								$workflow_data[$i][$j][2] = 0;
								$workflow_data[$i][$j][3] = 0;
							}
						}
						$next_count = 0;


						for ($i=1; $i <= 10; $i++){
							if ($f_rows["next".$i."_data"] != "*0-0"){
								$next_count ++;

			//複数承認を分解する
								list($split_staff_cnt, $split_staff_name, $split_apply_cnt) = workflow_root_dsp_split($f_rows["next".$i."_data"]);

								for ($nn=0; $nn < $split_staff_cnt; $nn++){
									if ($nn != 0){
										$workflow_data[$next_count][0][0] .= "<br />";
									}
									$s_id = str_replace("*", "", $split_staff_name[$nn]);
			//						$workflow_data[$next_count][0][0] .= f_staff_name_get($s_id);



									$workflow_data[$next_count][0][0] .= "<a href='workflow_root_dsp.php?up=1&kengen=1&kengen_no=".$next_count."&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$s_id."&chg_org=".$f_rows["busho_code"]."&chg_flow=".$f_rows["flow_doc_code"]."&target_date2=".$target_date2."'>".f_staff_name_get($s_id)."</a>";




								}
								$workflow_data[$next_count][0][2] = $split_staff_cnt;
								$workflow_data[$next_count][0][3] = $split_apply_cnt;
			//ここまで

			//					list($terget_id, $terget_flag) = explode("-", $f_rows["next".$i."_data"]);
			//					$workflow_data[$next_count][0][0] = $terget_id;

								$workflow_data[$next_count][0][1] = $terget_flag;
								$next_cc_count = 0;
								for ($j=1; $j <= 5; $j++){
									if ($f_rows["next".$i."_cc".$j."_data"] != "*0-0"){
										$next_cc_count ++;
										list($terget_id, $terget_flag) = explode("-", $f_rows["next".$i."_cc".$j."_data"]);
										$workflow_data[$next_count][$next_cc_count][0] = $terget_id;
										$workflow_data[$next_count][$next_cc_count][1] = $terget_flag;
									}
								}
								$workflow_cc_data[$next_count] = $next_cc_count;
							}
						}

						$workflow_html .= "<tr>\n";
			//		      	$workflow_html .= "<th colspan=4>".code_name_dsp(1, $f_rows["busho_code"])."&nbsp;&nbsp;&nbsp;".code_name_dsp(0, org_id_code_get($f_rows["busho_code"]))."&nbsp;&nbsp;&nbsp;".f_code_dsp(99, $f_rows["flow_doc_code"])."&nbsp;&nbsp;(ランク：".$f_rows["rank"].")</th>\n";
//					      	$workflow_html .= "<th colspan=4>".code_name_dsp(1, $f_rows["busho_code"])."&nbsp;&nbsp;&nbsp;".code_name_dsp(0, org_id_code_get($f_rows["busho_code"]))."&nbsp;&nbsp;&nbsp;".f_code_dsp(99, $f_rows["flow_doc_code"])."&nbsp;&nbsp;(適用年月日：".$target_date2.")</th>\n";
					      	$workflow_html .= "<th colspan=4>".code_name_dsp(0, org_id_code_get($f_rows["busho_code"]))."&nbsp;&nbsp;&nbsp;".f_code_dsp(99, $f_rows["flow_doc_code"])."&nbsp;&nbsp;(適用年月日："."<a href='workflow_root_dsp.php?up=2&kengen=3&cc_no=1&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$cc1."&chg_org=".$f_rows["busho_code"]."&chg_flow=".$f_rows["flow_doc_code"]."&target_date2=".$target_date2."&wid=".$f_rows["m_workflow_id"]."'>".$target_date2."</a>)</th>\n";
						$workflow_html .= "</tr>\n";
						$workflow_html .= "<tr>\n";
					      	$workflow_html .= "<td align=center>1</td>\n";
					      	$workflow_html .= "<td align=left colspan=2>起案者</td>\n";
					      	$workflow_html .= "<td align=left>";

						$cc = "";
						if ($cc1 != "0"){
							if ($cc == ""){
								$cc .= "<a href='workflow_root_dsp.php?up=1&kengen=3&cc_no=1&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$cc1."&chg_org=".$f_rows["busho_code"]."&chg_flow=".$f_rows["flow_doc_code"]."&target_date2=".$target_date2."'>".f_staff_name_get($cc1)."</a>";
							}else{
								$cc .= "<br />"."<a href='workflow_root_dsp.php?up=1&kengen=3&cc_no=1&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$cc1."&chg_org=".$f_rows["busho_code"]."&chg_flow=".$f_rows["flow_doc_code"]."&target_date2=".$target_date2."'>".f_staff_name_get($cc1)."</a>";
							}
						}
						if ($cc2 != "0"){
							if ($cc == ""){
								$cc .= "<a href='workflow_root_dsp.php?up=1&kengen=3&cc_no=2&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$cc2."&chg_org=".$f_rows["busho_code"]."&chg_flow=".$f_rows["flow_doc_code"]."&target_date2=".$target_date2."'>".f_staff_name_get($cc2)."</a>";
							}else{
								$cc .= "<br />"."<a href='workflow_root_dsp.php?up=1&kengen=3&cc_no=2&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$cc2."&chg_org=".$f_rows["busho_code"]."&chg_flow=".$f_rows["flow_doc_code"]."&target_date2=".$target_date2."'>".f_staff_name_get($cc2)."</a>";
							}
						}
						if ($cc3 != "0"){
							if ($cc == ""){
								$cc .= "<a href='workflow_root_dsp.php?up=1&kengen=3&cc_no=3&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$cc3."&chg_org=".$f_rows["busho_code"]."&chg_flow=".$f_rows["flow_doc_code"]."&target_date2=".$target_date2."'>".f_staff_name_get($cc3)."</a>";
							}else{
								$cc .= "<br />"."<a href='workflow_root_dsp.php?up=1&kengen=3&cc_no=3&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$cc3."&chg_org=".$f_rows["busho_code"]."&chg_flow=".$f_rows["flow_doc_code"]."&target_date2=".$target_date2."'>".f_staff_name_get($cc3)."</a>";
							}
						}
						if ($cc4 != "0"){
							if ($cc == ""){
								$cc .= "<a href='workflow_root_dsp.php?up=1&kengen=3&cc_no=4&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$cc4."&chg_org=".$f_rows["busho_code"]."&chg_flow=".$f_rows["flow_doc_code"]."&target_date2=".$target_date2."'>".f_staff_name_get($cc4)."</a>";
							}else{
								$cc .= "<br />"."<a href='workflow_root_dsp.php?up=1&kengen=3&cc_no=4&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$cc4."&chg_org=".$f_rows["busho_code"]."&chg_flow=".$f_rows["flow_doc_code"]."&target_date2=".$target_date2."'>".f_staff_name_get($cc4)."</a>";
							}
						}
						if ($cc5 != "0"){
							if ($cc == ""){
								$cc .= "<a href='workflow_root_dsp.php?up=1&kengen=3&cc_no=5&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$cc5."&chg_org=".$f_rows["busho_code"]."&chg_flow=".$f_rows["flow_doc_code"]."&target_date2=".$target_date2."'>".f_staff_name_get($cc5)."</a>";
							}else{
								$cc .= "<br />"."<a href='workflow_root_dsp.php?up=1&kengen=3&cc_no=5&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&target_staff=".$cc5."&chg_org=".$f_rows["busho_code"]."&chg_flow=".$f_rows["flow_doc_code"]."&target_date2=".$target_date2."'>".f_staff_name_get($cc5)."</a>";
							}
						}

						$workflow_html .= $cc;
						$workflow_html .= "</td>\n";
						$workflow_html .= "</tr>\n";

						for ($i=1; $i <= $next_count; $i++){
							$workflow_html .= "<tr>\n";
						      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i].">".($i+1)."</td>\n";
			//複数承認者の名前を列記する
						      	$workflow_html .= "<td align=left rowspan=".$workflow_cc_data[$i].">".$workflow_data[$i][0][0]."</td>\n";
			//			      	$workflow_html .= "<td align=left rowspan=".$workflow_cc_data[$i].">".f_staff_name_get($workflow_data[$i][0][0])."</td>\n";
			//承認人数を表示
						      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i].">".$workflow_data[$i][0][3]." / ".$workflow_data[$i][0][2]."</td>\n";


							if ($workflow_cc_data[$i] == 0){
							      	$workflow_html .= "<td align=left></td>\n";
							}else{
								for ($j=1; $j <= $workflow_cc_data[$i]; $j++){
									if ($j > 1){
										$workflow_html .= "<tr>\n";
									}
									$s_id = str_replace("*", "", $workflow_data[$i][$j][0]);
								      	$workflow_html .= "<td align=left><a href='workflow_root_dsp.php?up=1&kengen=2&kengen_no=".$i."&cc_no=".$j."&target_date=".$target_date."&target_flow=".$target_flow."&target_org=".$target_org."&chg_org=".$f_rows["busho_code"]."&target_staff=".$s_id."&chg_flow=".$f_rows["flow_doc_code"]."&target_date2=".$target_date2."'>".f_staff_name_get($s_id)."</a></td>\n";
									$workflow_html .= "</tr>\n";
								}
							}
						}
skip:
						$cnt += 1;
					}
skip2:
					$cnt_org += 1;
				}
			}
skip3:
			$cnt_mcode += 1;
		}
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


<meta http-equiv="Content-Type" content="text/html; charset=SHIFT-JIS">
<meta name="description" content="Slidebars is a jQuery plugin for quickly and easily implementing app style off-canvas menus and sidebars into your website.">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">

<script type="text/javascript" src="./js/sales/jquery/jquery.min.js"></script>
<script type="text/javascript" src="./js/sales/jquery/jquery-ui.min.js"></script>
<script type="text/javascript" src="./js/sales/jquery/jquery.ui.datepicker-ja.min.js"></script>
<link rel="stylesheet" type="text/css" href="./css/sales/sunny/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="./js/sales/jquery/jquery.maskedinput.js"></script>

<!-- 各画面のスクリプト -->
<script type="text/javascript">
function org_sel(){
		$.ajax({
			type: "POST",
			url: "ajax_org.php",
    			data: {
        			org_id: $(".org_id").val()
    			},
			cache: false,
			async: false,
			success: function(html){
				$(".user_list").html(html);
			}
		});
}
$(function(){
	$("#chg_date").datepicker({dateFormat:'yy-mm-dd'});
	$("#chg_date").mask("9999-99-99");
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
			<div class="serchEditArea">
<table width=100% style="border-style: none;">
<tbody>
<tr>
<td width=500 style="border-style: none;vertical-align: top;">
				<table>
					<tbody>
<?= $workflow_html ?>
					</tbody>
				</table>
</td>
<td style="border-style: none;vertical-align: top;">
				<table>
					<tbody>
<?= $workflow_change_html ?>
					</tbody>
				</table>
</td>
</tr>
</table>
			</div><!-- /.serchListAreaFree -->

<p align="center"><button type="button" onclick="location.href='inport_workflow.php?cd=100&target_flow=<?= $target_flow ?>&target_org=<?= $target_org ?>&target_date=<?= $target_date ?>'">戻る</button><p>

		</div><!-- /#sb-site -->

    <script type="text/javascript" src="jquery/js/scrolltopcontrol.js"></script> <!--スクロールしながらページのトップに戻る-->
    <script src="../js/home/slidebars/slidebars.min.js"></script>
    <script src="../js/home/slidebars/mp_silde.js"></script>
    </body>
</html>
