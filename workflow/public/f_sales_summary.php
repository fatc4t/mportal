<?php

function f_sales_katsudou_html($login_name)
{
	// ����
	$serch_date_base = date("Y-m-d");
	$serch_date_month = date("Y-m");
	$y = substr($serch_date_base, 0, 4);
	$m = substr($serch_date_base, 5, 2);
	$d = substr($serch_date_base, 8, 2);
	$holiday = getHolidays($y, $m, 'ja');

	$cnt_i = 0;
	$cnt_no = 1;
	$sales_html = "";
	$sales_html .= "<table width=100%>\n";
	$sales_html .= "<tr>\n";
	$sales_html .= "<td class=\"caption\"><img src=\"./img/icon/report.png\" width=20 height=20 align=top>�@������</td>\n";
	$sales_html .= "</tr>\n";
	$sales_html .= "<tr>\n";
	$sales_html .= "<td class=\"telop\" height=140>\n";
	$sales_html .= "<table class=\"top-menu-list_table\" width=100%>\n";
	$sales_html .= "<tr>\n";
	$sales_html .= "<td align=center>����</td>\n";
	$sales_html .= "<td align=center>���O</td>\n";
	$date_cnt = 0;
	for ($date_i = 0; $date_i <= 4; $date_i ++)
	{
		$date_cnt = $date_i * -1;
		$serch_date = computeDate2($serch_date_base, $date_cnt);
		$y = substr($serch_date, 0, 4);
		$m = substr($serch_date, 5, 2);
		$d = substr($serch_date, 8, 2);
		$youbi = f_youbi_get($y, $m, $d);
		if ($youbi == "�y"){
			if ($date_i == 0){
				$sales_html .= "<td align=left><font color=blue>����</font></td>\n";
			}else{
				$sales_html .= "<td align=left><font color=blue>".$d."(".$youbi.")</font></td>\n";
			}
		}else if($youbi == "��"){
			if ($date_i == 0){
				$sales_html .= "<td align=left><font color=red>����</font></td>\n";
			}else{
				$sales_html .= "<td align=left><font color=red>".$d."(".$youbi.")</font></td>\n";
			}
		}else{
			$today = $y."-".$m."-".$d;
			$y_flag = 0;
			for($m_i = 0; $m_i < count($holiday); $m_i ++) {
				if($today == $holiday[$m_i]){
					$y_flag = 1;
					break;
				}
			}
			if ($date_i == 0){
				if ($y_flag == 1){
					$sales_html .= "<td align=left><font color=red>����</font></td>\n";
				}else{
					$sales_html .= "<td align=left>����</td>\n";
				}
			}else{
				if ($y_flag == 1){
					$sales_html .= "<td align=left><font color=red>".$d."(".$youbi.")</font></td>\n";
				}else{
					$sales_html .= "<td align=left>".$d."(".$youbi.")</td>\n";
				}
			}
		}
	}
	$sales_html .= "</tr>\n";

  	$sql_staff = "SELECT * FROM m_user_detail WHERE f1 = 1 ORDER BY organization_id, rank DESC";

	$result_staff = executeQuery($sql_staff);
	$rows_staff = mysql_num_rows($result_staff);
	$flag = 0;
	$busho_save = 0;
	if($rows_staff){
		while($row_staff = mysql_fetch_array($result_staff)) {
			$date_cnt = 0;

			if ($busho_save != $row_staff["organization_id"]){

				$busho_save = $row_staff["organization_id"];

				$sales_html .= "<tr height=10>\n";
				$sales_html .= "<td align=center colspan=7>".char_chg("to_dsp", code_name_dsp(1, $row_staff["organization_id"]))." ".char_chg("to_dsp", code_name_dsp(0, $row_staff["organization_id"]))."</td>\n";
				$sales_html .= "</tr>\n";

			}
			$sales_html .= "<tr>\n";
			for ($date_i = 0; $date_i <= 4; $date_i ++)
			{
				$date_cnt = $date_i * -1;
				$serch_date = computeDate2($serch_date_base, $date_cnt);

				$sql_customer = "SELECT count(*) FROM t_customer_memo WHERE create_date LIKE '%".$serch_date."%' and tantou = '".$row_staff["name"]."'";
				$result_customer = executeQuery($sql_customer);
				list($num_customer) = mysql_fetch_row($result_customer);

				$sql_project = "SELECT count(*) FROM t_project_memo WHERE create_date LIKE '%".$serch_date."%' and tantou = '".$row_staff["name"]."'";
				$result_project = executeQuery($sql_project);
				list($num_project) = mysql_fetch_row($result_project);

				$num_comment = 0;
				$sql_comment = "SELECT count(*) FROM t_memo_comment WHERE terget LIKE '%".$serch_date."%' and tantou = '".$row_staff["name"]."'";
				$result_comment = executeQuery($sql_comment);
				list($num_comment) = mysql_fetch_row($result_comment);

				$num = $num_customer + $num_project;

				if ($date_i == 0)
				{
					$sales_html .= "<td align=center>".$cnt_no.".</td>\n";
					$sales_html .= "<td align=left>".char_chg("to_dsp", $row_staff["name"])."</td>\n";
				}
				if ($num == 0){
					$sales_html .= "<td align=left><font color=gray>0 ��</font></td>\n";
				}else{
					if ($num_comment == 0){
						$sales_html .= "<td align=left><a class=\"black\" href=./report/sales_report_staff.php?serch_date=".$serch_date."&name=".char_chg("to_dsp", $row_staff["name"])."&ret=1 target=\"_self\">".$num." ��</a></td>\n";
					}else{
						$sql_me = "SELECT create_date FROM t_memo_comment WHERE terget LIKE '%".$serch_date."%' and touroku_name = '".$login_name."' and tantou = '".$row_staff["name"]."' ORDER BY create_date DESC LIMIT 1";
						$result_me = executeQuery($sql_me);
						$rows_me = mysql_fetch_array($result_me);
						$num_other = 0;
						if($rows_me){
							$sql_other = "SELECT count(*) FROM t_memo_comment WHERE terget LIKE '%".$serch_date."%' and create_date > '".$rows_me["create_date"]."' and tantou = '".char_chg("to_dsp", $row_staff["name"])."' and touroku_name != '".$login_name."'";
							$result_other = executeQuery($sql_other);
							list($num_other) = mysql_fetch_row($result_other);
						}
						if ($num_other == 0){
							$sales_html .= "<td align=left><a class=\"black\" href=./report/sales_report_staff.php?serch_date=".$serch_date."&name=".char_chg("to_dsp", $row_staff["name"])."&ret=1 target=\"_self\">".$num." ��</a> <font color=red>(".$num_comment.")</font></td>\n";
						}else{
							$sales_html .= "<td align=left><a class=\"black\" href=./report/sales_report_staff.php?serch_date=".$serch_date."&name=".char_chg("to_dsp", $row_staff["name"])."&ret=1 target=\"_self\"><font color=red>".$num." ��</font></a> <font color=red>(".$num_comment.")</font></td>\n";
						}
					}
				}
			}
			$sales_html .= "</tr>\n";
			$cnt_no ++;
		}
	}

	$sales_html .= "</table>\n";
	$sales_html .= "</td>\n";
	$sales_html .= "</tr>\n";
	$sales_html .= "</table>\n";

	return $sales_html;
}

function f_report_ranking()
{
	$dsp_max = 10;

	$report_ranking_html = "";
	$report_ranking_html .= "<table width=100%>\n";
	$report_ranking_html .= "<tr>\n";
	$report_ranking_html .= "<td class=\"caption\"><img src=\"./img/icon/ranking.png\" width=20 height=20 align=top>�@�����ˁI�����L���O ".$dsp_max."</td>\n";
	$report_ranking_html .= "</tr>\n";
	$report_ranking_html .= "<tr>\n";
	$report_ranking_html .= "<td class=\"telop\">\n";
  	$report_ranking_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

	for ($i=0; $i<30; $i++){
		$ranking[$i][0] = "";	//���O
		$ranking[$i][1] = 0;	//�����ː�
		$ranking[$i][2] = 0;	//���߂��ː�
		$ranking[$i][3] = 0;	//�����ː�-���߂��ː�
	}

  	$sql_staff = "SELECT * FROM m_user_detail WHERE f1 = 1 ORDER BY organization_id";
	$result_staff = executeQuery($sql_staff);
	$rows_staff = mysql_num_rows($result_staff);
	if($rows_staff){
		while($row_staff = mysql_fetch_array($result_staff)) {

			$num_iine = 0;
			$iine_cnt = 0;
			$num_dame = 0;
			$dame_cnt = 0;

			list( $num_iine, $iine_cnt, $num_dame, $dame_cnt ) = iine_dame_cnt(2, "2016-01-01", char_chg("to_dsp", $row_staff["name"]), "");

			for ($i=0; $i<30; $i++){

				if($ranking[$i][3] < ($iine_cnt-$dame_cnt)){
					for ($j=30; $j>$i; $j--){
						$ranking[$j][0] = $ranking[$j-1][0];
						$ranking[$j][1] = $ranking[$j-1][1];
						$ranking[$j][2] = $ranking[$j-1][2];
						$ranking[$j][3] = $ranking[$j-1][3];
					}
					$ranking[$i][0] = char_chg("to_dsp", $row_staff["name"]);
					$ranking[$i][1] = $iine_cnt;
					$ranking[$i][2] = $dame_cnt;
					$ranking[$i][3] = ($iine_cnt-$dame_cnt);
					break;
				}

			}
		}
	}


	$same_data = $ranking[14][3];
	$same_cnt = 0;
	for ($i=$dsp_max; $i<30; $i++){
					if ($same_data != $ranking[$i][3]){
						break;
					}else{
						$same_cnt ++;
					}
	}

	$iine_sv = 0;
	$iine_ranking = 0;
	for ($i=0; $i<$dsp_max; $i++){
		if ($iine_sv != $ranking[$i][3]){
			$iine_ranking ++;
		}
				$report_ranking_html .= "<tr>\n";
				$report_ranking_html .= "<td align=center width=40>".$iine_ranking."��</td>\n";

				if ($i+1 == $dsp_max){
					if ($same_cnt != 0){
						$report_ranking_html .= "<td align=left>".$ranking[$i][0]." (�� ".$same_cnt."��)</td>\n";
					}else{
						$report_ranking_html .= "<td align=left>".$ranking[$i][0]."</td>\n";
					}
				}else{
					$report_ranking_html .= "<td align=left>".$ranking[$i][0]."</td>\n";
				}

				$report_ranking_html .= "<td align=right width=40>".$ranking[$i][3]."</td>\n";
				$report_ranking_html .= "<td align=right width=40><font color=blue>+ ".$ranking[$i][1]."</font></td>\n";
				$report_ranking_html .= "<td align=right width=40><font color=red>- ".$ranking[$i][2]."</font></td>\n";
				$report_ranking_html .= "</tr>\n";
		$iine_sv = $ranking[$i][3];
	}

  	$report_ranking_html .= "</table>\n";
	$report_ranking_html .= "</td>\n";
	$report_ranking_html .= "</tr>\n";
	$report_ranking_html .= "</table>\n";

	return $report_ranking_html;

}


function f_houmon_html()
{
	$cnt_i = 1;
	$houmon_html = "";
	$houmon_html .= "<table width=100%>\n";
	$houmon_html .= "<tr>\n";
	$houmon_html .= "<td class=\"caption\"><img src=\"./img/icon/calendar.png\" width=20 height=20 align=top>�@�K��\��</td>\n";
	$houmon_html .= "</tr>\n";
	$houmon_html .= "<tr>\n";
	$houmon_html .= "<td class=\"telop\">\n";
	$houmon_html .= "<table class=\"top-menu-list_table\" width=100%>\n";

	$serch_date = date("Y-m-d");
  	$sql_houmonbi = "SELECT * FROM t_customer_memo WHERE houmonbi >= '".$serch_date."' ORDER BY houmonbi, houmon_s1";
	$result_houmonbi = executeQuery($sql_houmonbi);
	$rows_houmonbi = mysql_num_rows($result_houmonbi);
	if($rows_houmonbi){
		while($row_houmonbi = mysql_fetch_array($result_houmonbi)) {

			if ($row_houmonbi["houmonbi"] != "0000-00-00"){
				if ($cnt_i > 12)
					break;
				
				if ($row_houmonbi["houmonbi"] === $serch_date){
					$houmon_html .= "<tr bgcolor=\"#CEF6F5\">\n";
				}else{
					$houmon_html .= "<tr>\n";
				}
				$youbi = "(".f_youbi_get(substr($row_houmonbi["houmonbi"],0,4), substr($row_houmonbi["houmonbi"],5,2), substr($row_houmonbi["houmonbi"],8,2)).")";
				$houmon_html .= "<td align=left>".$row_houmonbi["houmonbi"].$youbi."</td>\n";
				$houmon_html .= "<td align=left><a class=\"black\" href=\"./customer/customer_info_view.php?no=".$row_houmonbi["no"]."\" target=\"_blank\">".f_customer_name_get($row_houmonbi["no"])."</a></td>\n";
				if ($row_houmonbi["houmonsha2"] === "")
					$houmon_html .= "<td align=left>".char_chg("to_dsp", $row_houmonbi["houmonsha"])."</td>\n";
				else
					$houmon_html .= "<td align=left>".char_chg("to_dsp", $row_houmonbi["houmonsha"])."�E".char_chg("to_dsp", $row_houmonbi["houmonsha2"])."</td>\n";
				$houmon_html .= "</tr>\n";
				
				$cnt_i ++;
			}

		}
	}
  	$houmon_html .= "</table>\n";
	$houmon_html .= "</td>\n";
	$houmon_html .= "</tr>\n";
	$houmon_html .= "</table>\n";
	
	return $houmon_html;
}
	
function f_new_html()
{
	$new_html = "";
	$new_html .= "<table width=100%>\n";
	$new_html .= "<tr>\n";
	$new_html .= "<td class=\"caption\"><img src=\"./img/icon/new.png\" width=20 height=20 align=top>�@�X�V����</td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "<tr>\n";
	$new_html .= "<td class=\"telop\">\n";
  	$new_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";
	$cnt_no = 1;

  	$sql_new = "SELECT * FROM t_new  ORDER BY create_date desc limit 10";
	$result_new = executeQuery($sql_new);
	$rows_new = mysql_num_rows($result_new);
	if($rows_new){
		while($row_new = mysql_fetch_array($result_new)) {
				$new_html .= "<tr>\n";
				if (strpos($row_new["function"], "�C���|�[�g") !== false || strpos($row_new["function"], "�W�v") !== false){
							$new_html .= "<td align=left><font color=red>".$row_new["create_date"]."</font></td>\n";
							$new_html .= "<td align=left><font color=red>".char_chg("to_dsp", $row_new["tantou"])."</font></td>\n";
				//			$new_html .= "<td align=left><font color=red>".char_chg("to_dsp", $row_new["menu"])."</font></td>\n";
							$new_html .= "<td align=left><font color=red>".char_chg("to_dsp", $row_new["function"])."</font></td>\n";
				}else if (strpos($row_new["function"], "�L�^") !== false){
							$new_html .= "<td align=left><font color=deeppink>".$row_new["create_date"]."</font></td>\n";
							$new_html .= "<td align=left><font color=deeppink>".char_chg("to_dsp", $row_new["tantou"])."</font></td>\n";
				//			$new_html .= "<td align=left><font color=deeppink>".char_chg("to_dsp", $row_new["menu"])."</font></td>\n";
							$new_html .= "<td align=left><font color=deeppink>".char_chg("to_dsp", $row_new["function"])."</font></td>\n";
				}else{
							$new_html .= "<td align=left>".$row_new["create_date"]."</td>\n";
							$new_html .= "<td align=left>".char_chg("to_dsp", $row_new["tantou"])."</td>\n";
				//			$new_html .= "<td align=left>".char_chg("to_dsp", $row_new["menu"])."</td>\n";
							$new_html .= "<td align=left>".char_chg("to_dsp", $row_new["function"])."</td>\n";
				}
				$new_html .= "</tr>\n";
				$cnt_no ++;
		}
	}
  	$new_html .= "</table>\n";
	$new_html .= "</td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "</table>\n";
	
	return $new_html;
}

function f_dev_html()
{
	$dev_html = "";
	$dev_html .= "<table width=100%>\n";
	$dev_html .= "<tr>\n";
	$dev_html .= "<td class=\"caption\" width=10%><img src=\"./img/icon/setting.png\" width=20 height=20 align=top>�@�J���Ҍ���</td>\n";
	$dev_html .= "<tr>\n";
	$dev_html .= "<td class=\"telop\">\n";

  	$dev_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";
	$dev_html .= "<tr>\n";
	$dev_html .= "<td align=left style=\"border-bottom:solid 1px #E6E6E6;\"><a href=\"./mobile/menu.php\"><font color=blue>�X�}�z�ł̓R�R</font></a></td>\n";
	$dev_html .= "</tr>\n";
	$dev_html .= "<tr>\n";
	$dev_html .= "<td align=left style=\"border-bottom:solid 1px #E6E6E6;\"><a href=\"../ordering/partner/login.php\"><font color=blue>�������O�C�����</font></a></td>\n";
	$dev_html .= "</tr>\n";
	$dev_html .= "</table>\n";

	$dev_html .= "</td>\n";
	$dev_html .= "</tr>\n";
	$dev_html .= "</table>\n";

	return $dev_html;
}

function f_workflow_html($login_staff)
{
	$workflow_html = "";
	$workflow_html .= "<table width=100%>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<td class=\"caption\"><img src=\"./img/icon/workflow.png\" width=20 height=20 align=top>�@���[�N�t���[</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<td class=\"telop\">\n";
  	$workflow_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

	$staff_code = f_staff_id_get($login_staff);

	$next_num = 0;
//	$sql = "SELECT count(*) FROM t_workflow WHERE (next1_data LIKE '".$staff_code."%-2' or next2_data LIKE '".$staff_code."%-2' or next3_data LIKE '".$staff_code."%-2' or next4_data LIKE '".$staff_code."%-2' or next5_data LIKE '".$staff_code."%-2' or next6_data LIKE '".$staff_code."%-2' or next7_data LIKE '".$staff_code."%-2' or next8_data LIKE '".$staff_code."%-2' or next9_data LIKE '".$staff_code."%-2' or next10_data LIKE '".$staff_code."%-2') and stat = '1'";
	$sql = "SELECT count(*) FROM t_workflow WHERE (next1_data = '".$staff_code."-2' or next2_data = '".$staff_code."-2' or next3_data = '".$staff_code."-2' or next4_data = '".$staff_code."-2' or next5_data = '".$staff_code."-2' or next6_data = '".$staff_code."-2' or next7_data = '".$staff_code."-2' or next8_data = '".$staff_code."-2' or next9_data = '".$staff_code."-2' or next10_data = '".$staff_code."-2') and stat = '1'";
	$num = executeQuery($sql);
	list($next_num) = mysql_fetch_row($num);
	mysql_free_result($num);

	$cc_num = 0;
//	$sql = "SELECT count(*) FROM t_workflow WHERE next1_cc1_data LIKE '".$staff_code."%%-2' or next1_cc2_data LIKE '".$staff_code."%-2' or next1_cc3_data LIKE '".$staff_code."%-2' or next2_cc1_data LIKE '".$staff_code."%-2' or next2_cc2_data LIKE '".$staff_code."%-2' or next2_cc3_data LIKE '".$staff_code."%-2' or next3_cc1_data LIKE '".$staff_code."%-2' or next3_cc2_data LIKE '".$staff_code."%-2' or next3_cc3_data LIKE '".$staff_code."%-2' or next4_cc1_data LIKE '".$staff_code."%-2' or next4_cc2_data LIKE '".$staff_code."%-2' or next4_cc3_data LIKE '".$staff_code."%-2' or next5_cc1_data LIKE '".$staff_code."%-2' or next5_cc2_data LIKE '".$staff_code."%-2' or next5_cc3_data LIKE '".$staff_code."%-2' or next6_cc1_data LIKE '".$staff_code."%-2' or next6_cc2_data LIKE '".$staff_code."%-2' or next6_cc3_data LIKE '".$staff_code."%-2' or next7_cc1_data LIKE '".$staff_code."%-2' or next7_cc2_data LIKE '".$staff_code."%-2' or next7_cc3_data LIKE '".$staff_code."%-2' or next8_cc1_data LIKE '".$staff_code."%-2' or next8_cc2_data LIKE '".$staff_code."%-2' or next8_cc3_data LIKE '".$staff_code."%-2' or next9_cc1_data LIKE '".$staff_code."%-2' or next9_cc2_data LIKE '".$staff_code."%-2' or next9_cc3_data LIKE '".$staff_code."%-2' or next10_cc1_data LIKE '".$staff_code."%-2' or next10_cc2_data LIKE '".$staff_code."%-2' or next10_cc3_data LIKE '".$staff_code."%-2'";
	$sql = "SELECT count(*) FROM t_workflow WHERE next1_cc1_data = '".$staff_code."-2' or next1_cc2_data = '".$staff_code."-2' or next1_cc3_data = '".$staff_code."-2' or next1_cc4_data = '".$staff_code."-2' or next1_cc5_data = '".$staff_code."-2' or";
	$sql .= " next2_cc1_data = '".$staff_code."-2' or next2_cc2_data = '".$staff_code."-2' or next2_cc3_data = '".$staff_code."-2' or next2_cc4_data = '".$staff_code."-2' or next2_cc5_data = '".$staff_code."-2' or";
	$sql .= " next3_cc1_data = '".$staff_code."-2' or next3_cc2_data = '".$staff_code."-2' or next3_cc3_data = '".$staff_code."-2' or next3_cc4_data = '".$staff_code."-2' or next3_cc5_data = '".$staff_code."-2' or";
	$sql .= " next4_cc1_data = '".$staff_code."-2' or next4_cc2_data = '".$staff_code."-2' or next4_cc3_data = '".$staff_code."-2' or next4_cc4_data = '".$staff_code."-2' or next4_cc5_data = '".$staff_code."-2' or";
	$sql .= " next5_cc1_data = '".$staff_code."-2' or next5_cc2_data = '".$staff_code."-2' or next5_cc3_data = '".$staff_code."-2' or next5_cc4_data = '".$staff_code."-2' or next5_cc5_data = '".$staff_code."-2' or";
	$sql .= " next6_cc1_data = '".$staff_code."-2' or next6_cc2_data = '".$staff_code."-2' or next6_cc3_data = '".$staff_code."-2' or next6_cc4_data = '".$staff_code."-2' or next6_cc5_data = '".$staff_code."-2' or";
	$sql .= " next7_cc1_data = '".$staff_code."-2' or next7_cc2_data = '".$staff_code."-2' or next7_cc3_data = '".$staff_code."-2' or next7_cc4_data = '".$staff_code."-2' or next7_cc5_data = '".$staff_code."-2' or";
	$sql .= " next8_cc1_data = '".$staff_code."-2' or next8_cc2_data = '".$staff_code."-2' or next8_cc3_data = '".$staff_code."-2' or next8_cc4_data = '".$staff_code."-2' or next8_cc5_data = '".$staff_code."-2' or";
	$sql .= " next9_cc1_data = '".$staff_code."-2' or next9_cc2_data = '".$staff_code."-2' or next9_cc3_data = '".$staff_code."-2' or next9_cc4_data = '".$staff_code."-2' or next9_cc5_data = '".$staff_code."-2' or";
	$sql .= " next10_cc1_data = '".$staff_code."-2' or next10_cc2_data = '".$staff_code."-2' or next10_cc3_data = '".$staff_code."-2' or next10_cc4_data = '".$staff_code."-2' or next10_cc5_data = '".$staff_code."-2'";
	$num = executeQuery($sql);
	list($cc_num) = mysql_fetch_row($num);
	mysql_free_result($num);

	$in_num = 0;
	$sql = "SELECT count(*) FROM t_workflow WHERE user_id = '".$staff_code."' and stat = '1'";
	$num = executeQuery($sql);
	list($in_num) = mysql_fetch_row($num);
	mysql_free_result($num);


				$workflow_html .= "<tr>\n";
if ($next_num == 0){
				$workflow_html .= "<td align=left><font color=black>���Ȃ��̏��F�҂���</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>".$next_num."��</font></td>\n";
}else{
				$workflow_html .= "<td align=left><a href=\"./workflow/apply_agree.php\" target=\"_blank\"><font color=black>���Ȃ��̏��F�҂���</font></a></td>\n";
				$workflow_html .= "<td align=left><font color=red>".$next_num."��</font></td>\n";
}
				$workflow_html .= "<td align=left><font color=black>�ł�</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr>\n";
if ($cc_num == 0){
				$workflow_html .= "<td align=left><font color=black>���Ȃ��̎Q�Ƒ҂���</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>".$cc_num."��</font></td>\n";
}else{
				$workflow_html .= "<td align=left><a href=\"./workflow/apply_agree.php\" target=\"_blank\"><font color=black>���Ȃ��̎Q�Ƒ҂���</font></a></td>\n";
				$workflow_html .= "<td align=left><font color=red>".$cc_num."��</font></td>\n";
}
				$workflow_html .= "<td align=left><font color=black>�ł�</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr>\n";
if ($in_num == 0){
				$workflow_html .= "<td align=left><font color=black>���Ȃ��̐\������</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>".$in_num."��</font></td>\n";
}else{
				$workflow_html .= "<td align=left><a href=\"./workflow/apply_view.php\" target=\"_blank\"><font color=black>���Ȃ��̐\������</font></a></td>\n";
				$workflow_html .= "<td align=left><font color=red>".$in_num."��</font></td>\n";
}
				$workflow_html .= "<td align=left><font color=black>�ł�</font></td>\n";
				$workflow_html .= "</tr>\n";


  	$workflow_html .= "</table>\n";
	$workflow_html .= "</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "</table>\n";
	
	return $workflow_html;
}

function f_bookmark_html($login_name)
{
	$bookmark_html = "";
	$bookmark_html .= "<table width=100%>\n";
	$bookmark_html .= "<tr>\n";
	$bookmark_html .= "<td class=\"caption\"><img src=\"./img/icon/bookmark.png\" width=20 height=20 align=top>�@�u�b�N�}�[�N</td>\n";
	$bookmark_html .= "</tr>\n";
	$bookmark_html .= "<tr>\n";
	$bookmark_html .= "<td class=\"telop\">\n";
  	$bookmark_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

  	$sql_bookmark = "SELECT * FROM t_bookmark WHERE system_type = 'sales' and user_name = '".char_chg("to_db", $login_name)."' ORDER BY bookmark_id ASC";
	$result_bookmark = executeQuery($sql_bookmark);
	$rows_bookmark = mysql_num_rows($result_bookmark);
	$button_count=0;
	if($rows_bookmark){
		$bookmark_html .= "<tr>\n";
		while($row_bookmark = mysql_fetch_array($result_bookmark)) {
				if ($button_count == 3){
					$bookmark_html .= "</tr>\n";
					$bookmark_html .= "<tr>\n";
					$button_count = 0;
				}
				$button_count ++;
				$bookmark_html .= "<td align=center><input type=\"button\" style=\"width: 110px;\" value=\"".char_chg("to_dsp", $row_bookmark["title"])."\" onclick=window.open(\"".char_chg("to_dsp", $row_bookmark["url"])."\"); /></td>\n";
		}
		for ($iii=$button_count; $iii < 3; $iii++){
				$bookmark_html .= "<td align=center></td>\n";
		}
		$bookmark_html .= "</tr>\n";
	}
  	$bookmark_html .= "</table>\n";
	$bookmark_html .= "</td>\n";
	$bookmark_html .= "</tr>\n";
	$bookmark_html .= "</table>\n";
	
	return $bookmark_html;
}

function f_mitaiou_html()
{
	$mitaiou = "";
	$mitaiou .= "<table width=100%>\n";
	$mitaiou .= "<tr>\n";
	$mitaiou .= "<td class=\"caption\">�`�F�b�N���X�g</td>\n";
	$mitaiou .= "</tr>\n";
	$mitaiou .= "<tr>\n";
	$mitaiou .= "<td>\n";
	$mitaiou .= "<table width=100%>\n";
	$mitaiou .= "<tr>\n";
	$ct = f_no_check_cnt_get();
	if ($ct == 0){
		$mitaiou .= "<td align=center height=50><a href=./checklist/check_list.php><font color=red size=5>���Ή������F".number_format($ct)."��</font></a></td>";
	}else{
		$mitaiou .= "<td align=center height=50><a href=./checklist/check_list.php><font color=red size=5>���Ή������F".number_format($ct)."��</font></a></td>";
	}
	$mitaiou .= "</tr>\n";
	$mitaiou .= "</table>\n";
	$mitaiou .= "</td>\n";
	$mitaiou .= "</tr>\n";
	$mitaiou .= "</table>\n";

	return $mitaiou;
}

function f_get_html()
{
	$cnt_no = 1;
	$get_html = "";
	$get_html .= "<table width=100%>\n";
	$get_html .= "<tr>\n";
	$get_html .= "<td class=\"caption\"><img src=\"./img/icon/jyuchu.png\" width=20 height=20 align=top>�@�󒍏�</td>\n";
	$get_html .= "</tr>\n";
	$get_html .= "<tr>\n";
	$get_html .= "<td class=\"telop\">\n";
  	$get_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

	$serch_date = date("Y-m-d");
	$serch_date_month = date("Y-m");
	$serch_date2 = computeDate2($serch_date,-30);
  	$sql_get = "SELECT * FROM t_project WHERE s_prj_status = 2 ORDER BY s_prj_chumon_date  desc limit 10";
	$result_get = executeQuery($sql_get);
	$rows_get = mysql_num_rows($result_get);
	if($rows_get){
		while($row_get = mysql_fetch_array($result_get)) {
				$get_html .= "<tr>\n";
				$get_html .= "<td align=left width=60>".date('m��d��', strtotime($row_get["s_prj_chumon_date"]))."</td>\n";
				$get_html .= "<td align=left width=20>".f_code_dsp(6, $row_get["s_prj_rank"])."</td>\n";
				$get_html .= "<td align=left><a class=\"black\" href=\"./customer/customer_info_view.php?no=".$row_get["s_customer_no"]."\" target=\"_blank\">".char_chg("to_dsp", $row_get["s_customer_name"])."</a></td>\n";
				$get_html .= "<td align=left><a class=\"black\" href=\"./project/project_view.php?code=".$row_get["s_prj_code"]."\" target=\"_blank\">".char_chg("to_dsp", $row_get["s_prj_tantou"])."</a></td>\n";
				$get_html .= "</tr>\n";
				$cnt_no ++;
		}
	}
  	$get_html .= "</table>\n";
	$get_html .= "</td>\n";
	$get_html .= "</tr>\n";
	$get_html .= "</table>\n";
	
	return $get_html;
}
	
function f_log_html()
{

	$logo_html = "";
	$logo_html .= "<table width=100%>\n";
	$logo_html .= "<tr>\n";
	$logo_html .= "<td align=center>\n";
	$logo_html .= "<a href=\"index.php\"><img src=\"./img/sw-big.png\" border=0 height=75></a>\n";
	$logo_html .= "</td>\n";
	$logo_html .= "</tr>\n";
	$logo_html .= "</table>\n";

	return $logo_html;
}


function bubblesort($arr,$staff) {
    $count = count($arr);
    for ($n = $count - 1; $n > 0; $n--) {
        for($i = 0; $i < $n; $i++) {
            if($arr[$i] < $arr[$i + 1]) {
                $tmp = $arr[$i];
                $tmp2 = $staff[$i];
                $arr[$i] = $arr[$i + 1]; 
                $arr[$i + 1] = $tmp;
                $staff[$i] = $staff[$i + 1]; 
                $staff[$i + 1] = $tmp2;
            }   
        }   
    }   
    return array( $arr, $staff );
}

function f_check_alert($windows8)
{

	$today = date("Y-m-d");
	$check_html = "";
	$check_html .= "<table width=100%>\n";
	$check_html .= "<tr>\n";
	$check_html .= "<td class=\"caption\"><img src=\"./img/icon/todo.png\" width=20 height=20 align=top>�@ToDo</td>\n";
	$check_html .= "</tr>\n";
	$check_html .= "<tr>\n";
	$check_html .= "<td class=\"telop\">\n";
  	$check_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

	// �N�G���𑗐M����
	$sql = "SELECT * FROM t_project_memo WHERE todo != '0000-00-00' and check_after = 0 and todo > '".$today."' ORDER BY todo";

	$result = executeQuery($sql);

  	$rows = mysql_num_rows($result);

	if($rows){
		while($row = mysql_fetch_array($result)) {
			$check_html .= "<tr>\n";
			$check_html .= "<td>".f_project_customername_get($row["no"])."</td>\n";
			$check_html .= "<td>".$row["todo"]."</td>\n";
			if ($row["todo"] < $today){
				$ato = remain_check3($row["todo"], $today);
				if ($ato > 5){
					$check_html .= "<td align=right rowspan=2 style=\"border-bottom:solid 1px #E6E6E6;\"><font color=red size=10>- ".$ato."</font> ���o��</td>\n";
				}else{
					$check_html .= "<td align=right rowspan=2 style=\"border-bottom:solid 1px #E6E6E6;\"><font color=red size=5>- ".$ato."</font> ���o��</td>\n";
				}
			}else{
				$ato = remain_check2($row["todo"]);
				if ($ato == 0){
					$check_html .= "<td align=left rowspan=2 style=\"border-bottom:solid 1px #E6E6E6;\"><font color=blue size=6>�����ł�!!</font></td>\n";
				}else{
					if ($ato > 7){
						$check_html .= "<td align=left rowspan=2 style=\"border-bottom:solid 1px #E6E6E6;\">���� ".$ato." ��</td>\n";
					}else{
						$check_html .= "<td align=left rowspan=2 style=\"border-bottom:solid 1px #E6E6E6;\">���� <font color=blue size=5>".$ato."</font> ��</td>\n";
					}
				}
			}
			$check_html .= "</tr>\n";
			$check_html .= "<tr>\n";
			$check_html .= "<td style=\"border-bottom:solid 1px #E6E6E6;\">�@�w".char_chg("to_dsp", $row["todo_title"])."�x</td>\n";
			$check_html .= "<td style=\"border-bottom:solid 1px #E6E6E6;\">".char_chg("to_dsp", $row["tantou"])."</td>\n";
			$check_html .= "</tr>\n";
		}
	}

	mysql_free_result($result);


	// �N�G���𑗐M����
//	$sql = "SELECT * FROM t_customer_memo WHERE todo != '0000-00-00' and check_after = 0 and todo >= '".$today."' ORDER BY todo";
	$sql = "SELECT * FROM t_customer_memo WHERE todo != '0000-00-00' and check_after = 0 ORDER BY todo";

	$result = executeQuery($sql);

  	//���ʃZ�b�g�̍s�����擾����
  	$rows = mysql_num_rows($result);

  	//�\������f�[�^���쐬
	if($rows){
		while($row = mysql_fetch_array($result)) {
			$check_html .= "<tr>\n";
			$check_html .= "<td>".f_customer_name_get($row["no"])."</td>\n";
			$check_html .= "<td>".$row["todo"]."</td>\n";
			if ($row["todo"] < $today){
				$ato = remain_check3($row["todo"], $today);
				if ($ato > 5){
					$check_html .= "<td align=right rowspan=2 style=\"border-bottom:solid 1px #E6E6E6;\"><font color=red size=10>- ".$ato."</font> ���o��</td>\n";
				}else{
					$check_html .= "<td align=right rowspan=2 style=\"border-bottom:solid 1px #E6E6E6;\"><font color=red size=5>- ".$ato."</font> ���o��</td>\n";
				}
			}else{
				$ato = remain_check2($row["todo"]);
				if ($ato == 0){
					$check_html .= "<td align=left rowspan=2 style=\"border-bottom:solid 1px #E6E6E6;\"><font color=blue size=6>�����ł�!!</font></td>\n";
				}else{
					if ($ato > 7){
						$check_html .= "<td align=left rowspan=2 style=\"border-bottom:solid 1px #E6E6E6;\">���� ".$ato." ��</td>\n";
					}else{
						$check_html .= "<td align=left rowspan=2 style=\"border-bottom:solid 1px #E6E6E6;\">���� <font color=blue size=5>".$ato."</font> ��</td>\n";
					}
				}
			}
			$check_html .= "</tr>\n";
			$check_html .= "<tr>\n";
			$check_html .= "<td style=\"border-bottom:solid 1px #E6E6E6;\">�@�w".char_chg("to_dsp", $row["todo_title"])."�x</td>\n";
			$check_html .= "<td style=\"border-bottom:solid 1px #E6E6E6;\">".char_chg("to_dsp", $row["tantou"])."</td>\n";
			$check_html .= "</tr>\n";
		}
	}

	mysql_free_result($result);
	$check_html .= "</table>\n";
	$check_html .= "</td>\n";
	$check_html .= "</tr>\n";
	$check_html .= "</table>\n";

	return($check_html);
}

?>
