<?php

function m_workflow_insert($cd, $schema, $chg_org, $chg_flow, $flow, $cc1, $cc2, $cc3, $cc4, $cc5, $chg_date, $login_staff_id)
{

	if ($cd == 0){
			$sql = "INSERT INTO ".$schema.".m_workflow ";
	}else{
			$sql = "INSERT INTO ".$schema.".m_workflow_tmp ";
	}
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
						$cc1.", ".$cc2.", ".$cc3.", ".$cc4.", ".$cc5.", ".$login_staff_id.")";
			sqlExec($sql);
}

function workflow_no($schema, $busho_code, $staff_code, $yyyy, $mm, $dd)
{
	$sirial_no = 0;
	$sql = "select * from ".$schema.".t_workflow_no where busho_code = '".$busho_code."' and yyyy = '".$yyyy."' and mm = '".$mm."'";

        $f_rows = getList($sql);
	$num = count($f_rows);
	if ($num == 0){
			$sql = "INSERT INTO ".$schema.".t_workflow_no (busho_code, yyyy, mm, dd, no) VALUES('".$busho_code."', '".$yyyy."', '".$mm."', '".$dd."', 1);";
			sqlExec($sql);
			$sirial_no = 1;
	}else{
			$rows = $f_rows[0];
			$sirial_no = $rows["no"] + 1;
			$sql = "UPDATE ".$schema.".t_workflow_no SET no = ".$sirial_no." where t_workflow_no_id = ".$rows["t_workflow_no_id"];
			sqlExec($sql);
	}

	$workflow_no = sprintf("%s-%04d%02d-%05d",$busho_code, $yyyy, $mm, $sirial_no);

	return $workflow_no;
}

function workflow_alert($schema, $staff_code)
{
	$agree_num = 0;
	$cc_num = 0;

	$sql = "SELECT * FROM ".$schema.".t_workflow WHERE (next1_data LIKE '%*".$staff_code."-2%' or next2_data LIKE '%*".$staff_code."-2%' or next3_data LIKE '%*".$staff_code."-2%' or next4_data LIKE '%*".$staff_code."-2%' or next5_data LIKE '%*".$staff_code."-2%' or next6_data LIKE '%*".$staff_code."-2%' or next7_data LIKE '%*".$staff_code."-2%' or next8_data LIKE '%*".$staff_code."-2%' or next9_data LIKE '%*".$staff_code."-2%' or next10_data LIKE '%*".$staff_code."-2%') and stat = '1'";
        $f_rows = getList($sql);
	$agree_num = count($f_rows);

	$sql = "SELECT * FROM ".$schema.".t_workflow WHERE ";
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
        $f_rows = getList($sql);
	$cc_num = count($f_rows);

	return array($agree_num, $cc_num);
}


function workflow_doc_inf($cd, $id, $staff_code)
{
	$workflow_html = "";
	$workflow2_html = "";
	$workflow3_html = "";



	for ($i = 1; $i <= 10; $i++){
		$workflow_cc_data[$i] = 0;

		for ($j=0; $j<6; $j++){
			$workflow_data[$i][$j][0] = 0;
			$workflow_data[$i][$j][1] = 0;
			$workflow_data[$i][$j][2] = "";
			$workflow_data[$i][$j][3] = "";
		}
	}
	$next_count = 0;

	$sql = "SELECT * FROM ".$_SESSION["SCHEMA"].".t_workflow WHERE workflow_id = ".$id;

        $result = getList($sql);
	$f_rows = $result[0];


	$status = 0;
	$input_user = 0;
	if($f_rows){
		$workflow_no = $f_rows["workflow_no"];
		$status = $f_rows["stat"];
		$input_user = $f_rows["user_id"];
			for ($i=1; $i <= 10; $i++){
				if ($f_rows["next".$i."_data"] != "*0-0"){
					$next_count ++;
					list($terget_id, $terget_flag) = explode("-", $f_rows["next".$i."_data"]);
					$workflow_data[$next_count][0][0] = $terget_id;
					$workflow_data[$next_count][0][1] = $terget_flag;
					$workflow_data[$next_count][0][2] = $f_rows["next".$i."_data_date"];
					$workflow_data[$next_count][0][3] = $f_rows["next".$i."_data_comment"];
					$next_cc_count = 0;
					for ($j=1; $j <= 5; $j++){
						if ($f_rows["next".$i."_cc".$j."_data"] != "*0-0" && $f_rows["next".$i."_cc".$j."_data"] != ""){
							$next_cc_count ++;
							list($terget_id, $terget_flag) = explode("-", $f_rows["next".$i."_cc".$j."_data"]);
							$workflow_data[$next_count][$next_cc_count][0] = $terget_id;
							$workflow_data[$next_count][$next_cc_count][1] = $terget_flag;
							$workflow_data[$next_count][$next_cc_count][2] = $f_rows["next".$i."_cc".$j."_data_date"];
						}
					}
					$workflow_cc_data[$next_count] = $next_cc_count;
				}
			}

		$workflow_html .= "<table>\n";
		$workflow_html .= "<tbody>\n";
		$workflow_html .= "<tr>\n";
		$workflow_html .= "<th colspan=2 width=250>申請番号</th>\n";
	      	$workflow_html .= "<td align=left width=650>".$workflow_no."</td>\n";
		$workflow_html .= "</tr>\n";
		$workflow_html .= "<tr>\n";
		$workflow_html .= "<th colspan=2>申請者</th>\n";
	      	$workflow_html .= "<td align=left>".f_staff_name_get($f_rows["user_id"])."</td>\n";
		$workflow_html .= "</tr>\n";
		$workflow_html .= "<tr>\n";
		$workflow_html .= "<th colspan=2>申請日付</th>\n";
	      	$workflow_html .= "<td align=left>".$f_rows["create_date"]."</td>\n";
		$workflow_html .= "</tr>\n";
		$workflow_html .= "<tr>\n";
		$workflow_html .= "<th colspan=2>申請文書</th>\n";
	      	$workflow_html .= "<td align=left>".f_code_dsp(99, $f_rows["flow_doc_code"])."</td>\n";
		$workflow_html .= "</tr>\n";
		$workflow_html .= "<tr>\n";
		$workflow_html .= "<th colspan=2>タイトル</th>\n";
	      	$workflow_html .= "<td align=left>".htmlspecialchars($f_rows["title"])."</td>\n";
		$workflow_html .= "</tr>\n";
/*
		$workflow_html .= "<tr>\n";
		$workflow_html .= "<th colspan=2>内容</th>\n";
	      	$workflow_html .= "<td width=650 align=left style='word-break:break-all;'>".nl2br($f_rows["comment"])."</td>\n";
		$workflow_html .= "</tr>\n";
*/
			$workflow_html .= "<tr>\n";
			$workflow_html .= "<td colspan=3>\n";
			$workflow_html .= "<table>\n";
			list($html, $script_html) = workflow_format_read(1, $f_rows["flow_doc_code"], $id);
			$workflow_html .= $html;
			$workflow_html .= "</table>\n";
			$workflow_html .= "</td>\n";
			$workflow_html .= "</tr>\n";


		$workflow_html .= "<tr>\n";
		$workflow_html .= "<th width=150 rowspan=3>添付ファイル</th>\n";
		$workflow_html .= "<th width=100>1</th>\n";
	      	$workflow_html .= "<td align=left><a href=\"./file/".$_SESSION["SCHEMA"]."/".$f_rows["tmp_name1"]."\" download=\"".$f_rows["file_name1"].$f_rows["kakuchousi1"]."\">".$f_rows["file_name1"].$f_rows["kakuchousi1"]."</a></td>\n";
		$workflow_html .= "</tr>\n";
		$workflow_html .= "<tr>\n";
		$workflow_html .= "<th>2</th>\n";
	      	$workflow_html .= "<td align=left><a href=\"./file/".$_SESSION["SCHEMA"]."/".$f_rows["tmp_name2"]."\" download=\"".$f_rows["file_name2"].$f_rows["kakuchousi2"]."\">".$f_rows["file_name2"].$f_rows["kakuchousi2"]."</a></td>\n";
		$workflow_html .= "</tr>\n";
		$workflow_html .= "<tr>\n";
		$workflow_html .= "<th>3</th>\n";
	      	$workflow_html .= "<td align=left><a href=\"./file/".$_SESSION["SCHEMA"]."/".$f_rows["tmp_name3"]."\" download=\"".$f_rows["file_name3"].$f_rows["kakuchousi3"]."\">".$f_rows["file_name3"].$f_rows["kakuchousi3"]."</a></td>\n";
		$workflow_html .= "</tr>\n";

//再申請履歴
		if ($cd != 9){
			$workflow_html .= "<tr>\n";
			$workflow_html .= "<th colspan=3>再申請履歴</th>\n";
			$workflow_html .= "</tr>\n";
			$workflow_html .= "<tr>\n";
			$workflow_html .= "<td colspan=3>\n";

			$workflow_html .= "<table width=900>\n";
			$workflow_html .= "<tbody>\n";

			$workflow_html .= "<tr>\n";
			$workflow_html .= "<th width=50>NO</th>\n";
			$workflow_html .= "<th width=220>申請日付</th>\n";
			$workflow_html .= "<th width=650>タイトル</th>\n";
			$workflow_html .= "</tr>\n";

	//履歴を取り出しリスト表示
			$sql_remand = "select * from ".$_SESSION["SCHEMA"].".t_workflow where workflow_no in (select workflow_no from ".$_SESSION["SCHEMA"].".t_workflow where workflow_id = ".$id.") and remand_f = 1 order by workflow_id asc";
			$cnt_remand = 0;
		        $rows_remand = getList($sql_remand);
		        if($rows_remand){
				while($row_remand = $rows_remand[$cnt_remand]) {
					if ($row_remand["workflow_id"] != $id){
						$workflow_html .= "<tr>\n";
						$workflow_html .= "<td align=center>".($cnt_remand+1)."</td>\n";
						$workflow_html .= "<td>".$row_remand["create_date"]."</td>\n";
						$workflow_html .= "<td><a href='workflow_dsp.php?workflow_id=".$row_remand["workflow_id"]."' target=_blank>".htmlspecialchars($row_remand["title"])."</a></td>\n";
						$workflow_html .= "</tr>\n";
					}
					$cnt_remand += 1;
				}
			}

			$workflow_html .= "</tbody>\n";
			$workflow_html .= "</table>\n";

			$workflow_html .= "</td>\n";
			$workflow_html .= "</tr>\n";
		}
		$workflow_html .= "</tbody>\n";
		$workflow_html .= "</table>\n";
	}

	return $workflow_html;
}
/*
function workflow_route($kyoten, $busho_code, $workflow_doc, $rank)
{

	$workflow_html = "";

	for ($i = 1; $i <= 10; $i++){
		$workflow_cc_data[$i] = 0;

		for ($j=0; $j<6; $j++){
			$workflow_data[$i][$j][0] = 0;
			$workflow_data[$i][$j][1] = 0;
			$workflow_data[$i][$j][2] = "";
			$workflow_data[$i][$j][3] = "";
		}
	}
	$next_count = 0;

	$sql = "SELECT * FROM ".$_SESSION["SCHEMA"].".m_workflow WHERE kyoten = ".$kyoten." and busho_code = ".code_org_id_get2($busho_code)." and flow_doc_code = ".$workflow_doc." and rank = ".$rank;
        $result = getList($sql);
	$f_rows = $result[0];

	$status = 0;
	$input_user = 0;
	if($f_rows){
			for ($i=1; $i <= 10; $i++){
				if ($f_rows["next".$i."_data"] != "0-0"){
					$next_count ++;
					list($terget_id, $terget_flag) = explode("-", $f_rows["next".$i."_data"]);
					$workflow_data[$next_count][0][0] = $terget_id;
					$workflow_data[$next_count][0][1] = $terget_flag;
					$next_cc_count = 0;
					for ($j=1; $j <= 5; $j++){
						if ($f_rows["next".$i."_cc".$j."_data"] != "0-0" && $f_rows["next".$i."_cc".$j."_data"] != ""){
							$next_cc_count ++;
							list($terget_id, $terget_flag) = explode("-", $f_rows["next".$i."_cc".$j."_data"]);
							$workflow_data[$next_count][$next_cc_count][0] = $terget_id;
							$workflow_data[$next_count][$next_cc_count][1] = $terget_flag;
						}
					}
					$workflow_cc_data[$next_count] = $next_cc_count;
				}
			}
	}

	$workflow_html .= "<table width=900>\n";
	$workflow_html .= "<tbody>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<th width=30>NO</td>\n";
	$workflow_html .= "<th>承認者</th>\n";
	$workflow_html .= "<th width=80>承認人数</th>\n";
	$workflow_html .= "<th>参照者</th>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "<tr>\n";
      	$workflow_html .= "<td align=center>0</td>\n";
      	$workflow_html .= "<td align=center>起案者</td>\n";
      	$workflow_html .= "<td align=left></td>\n";
	$workflow_html .= "</tr>\n";

	$comment_f = 0;
	$comment_data = "";
	for ($i=1; $i <= $next_count; $i++){
		$workflow_html .= "<tr>\n";
	      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i].">".$i."</td>\n";
	      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i].">".f_staff_name_get($workflow_data[$i][0][0])."</td>\n";
	      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i].">1 / 1</td>\n";

		if ($workflow_cc_data[$i] == 0){
		      	$workflow_html .= "<td align=left></td>\n";
		}else{
			for ($j=1; $j <= $workflow_cc_data[$i]; $j++){
				if ($j > 1){
					$workflow_html .= "<tr>\n";
				}
			      	$workflow_html .= "<td align=center>".f_staff_name_get($workflow_data[$i][$j][0])."</td>\n";
				$workflow_html .= "</tr>\n";
			}
		}
	}

		$workflow_html .= "</tbody>\n";
		$workflow_html .= "</table>\n";




	return $workflow_html;
}
*/
function workflow_route_check($kyoten, $busho_code, $workflow_doc, $rank)
{
	$next_count = 0;

//	$target_date = target_tekiyou("m_workflow", date("Y-m-d"));
	$where = "kyoten = ".$kyoten." and busho_code = ".code_org_id_get2($busho_code)." and flow_doc_code = ".$workflow_doc." and rank = ".$rank;
	$target_date = target_tekiyou_where("m_workflow", date("Y-m-d"), $where);
	if ($target_date == ""){
		$f_rows = 0;
	}else{
		$sql = "SELECT * FROM ".$_SESSION["SCHEMA"].".m_workflow WHERE target_date = '".$target_date."' and ".$where;
	        $result = getList($sql);
		$f_rows = $result[0];
	}
	if($f_rows){
			for ($i=1; $i <= 10; $i++){
				if ($f_rows["next".$i."_data"] != "*0-0"){
					$next_count ++;
				}
			}
	}

	return $next_count;
}

function workflow_route($kyoten, $busho_code, $workflow_doc, $rank)
{
	$workflow_html = "";
	$agree_f = 0;
	$agree_c = 0;

	for ($i = 1; $i <= 10; $i++){
		$workflow_cc_data[$i] = 0;

		for ($j=0; $j<6; $j++){
			for ($nn = 0; $nn < 10; $nn++){
				$workflow_data[$i][$j][0][$nn] = "";
				$workflow_data[$i][$j][1][$nn] = 0;
				$workflow_data[$i][$j][2][$nn] = "";
				$workflow_data[$i][$j][3][$nn] = "";
			}
			$workflow_data[$i][$j][4][0] = 0;
			$workflow_data[$i][$j][5][0] = 0;
		}
	}
	$next_count = 0;

	$where = "kyoten = ".$kyoten." and busho_code = ".code_org_id_get2($busho_code)." and flow_doc_code = ".$workflow_doc." and rank = ".$rank;
	$target_date = target_tekiyou_where("m_workflow", date("Y-m-d"), $where);

	if ($target_date == ""){
		$f_rows = 0;
	}else{
		$sql = "SELECT * FROM ".$_SESSION["SCHEMA"].".m_workflow WHERE target_date = '".$target_date."' and ".$where;
	        $result = getList($sql);
		$f_rows = $result[0];
	}

	$status = 0;
	$doc_cd = 0;
	$input_user = 0;
	if($f_rows){
		$cc1 = $f_rows["cc1"];
		$cc2 = $f_rows["cc2"];
		$cc3 = $f_rows["cc3"];
		$cc4 = $f_rows["cc4"];
		$cc5 = $f_rows["cc5"];
//		$status = $f_rows["stat"];
//		$input_user = $f_rows["user_id"];
		$doc_cd = $f_rows["flow_doc_code"];
		$result_id = $f_rows["result_id"];
			for ($i=1; $i <= 10; $i++){
				if ($f_rows["next".$i."_data"] != "*0-0"){
					$next_count ++;
//複数承認を分解する
					list($split_staff_cnt, $split_staff_name, $split_staff_target, $split_apply_cnt) = workflow_root_dsp_split2($f_rows["next".$i."_data"]);
					for ($nn=0; $nn < $split_staff_cnt; $nn++){
						$workflow_data[$next_count][0][0][$nn] .= $split_staff_name[$nn];
						$workflow_data[$next_count][0][1][$nn] .= $split_staff_target[$nn];
						$workflow_data[$next_count][0][2][$nn] = $f_rows["next".$i."_data_date".($nn+1)];
						$workflow_data[$next_count][0][3][$nn] = $f_rows["next".$i."_data_comment".($nn+1)];
					}
					$workflow_data[$next_count][0][4][0] = $split_staff_cnt;
					$workflow_data[$next_count][0][5][0] = $split_apply_cnt;

/*
					list($terget_id, $terget_flag) = explode("-", $f_rows["next".$i."_data"]);
					$workflow_data[$next_count][0][0] = $terget_id;
					$workflow_data[$next_count][0][1] = $terget_flag;
*/


//ここまで
					$next_cc_count = 0;
					for ($j=1; $j <= 5; $j++){
						if ($f_rows["next".$i."_cc".$j."_data"] != "*0-0" && $f_rows["next".$i."_cc".$j."_data"] != ""){
							$next_cc_count ++;
							list($terget_id, $terget_flag) = explode("-", $f_rows["next".$i."_cc".$j."_data"]);
							$workflow_data[$next_count][$next_cc_count][0][0] = $terget_id;
							$workflow_data[$next_count][$next_cc_count][1][0] = $terget_flag;
							$workflow_data[$next_count][$next_cc_count][2][0] = $f_rows["next".$i."_cc".$j."_data_date"];
						}
					}
					$workflow_cc_data[$next_count] = $next_cc_count;
				}
			}
	}


	$workflow_html .= "<table width=920>\n";
	$workflow_html .= "<tbody>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<th width=50>NO</td>\n";
	$workflow_html .= "<th width=350>承認者</th>\n";
	$workflow_html .= "<th width=170>多数決</th>\n";
	$workflow_html .= "<th width=350>参照者</th>\n";
	$workflow_html .= "</tr>\n";
	if ($next_count == 0){
		$workflow_html .= "<tr>\n";
	      	$workflow_html .= "<td align=center colspan=4>申請ルートが設定されていません</td>\n";
		$workflow_html .= "</tr>\n";
	}else{
			$cc = "";
			if ($cc1 != "0"){
				$cc .= f_staff_name_get($cc1)." ";
			}
			if ($cc2 != "0"){
				$cc .= f_staff_name_get($cc2)." ";
			}
			if ($cc3 != "0"){
				$cc .= f_staff_name_get($cc3)." ";
			}
			if ($cc4 != "0"){
				$cc .= f_staff_name_get($cc4)." ";
			}
			if ($cc5 != "0"){
				$cc .= f_staff_name_get($cc5);
			}

		$workflow_html .= "<tr>\n";
	      	$workflow_html .= "<td align=center>0</td>\n";
	      	$workflow_html .= "<td align=center>起案者</td>\n";
	      	$workflow_html .= "<td align=left> </td>\n";

	      	$workflow_html .= "<td align=left>";
		$workflow_html .= $cc;
		$workflow_html .= "</td>\n";

		$workflow_html .= "</tr>\n";
	}

	for ($i=1; $i <= $next_count; $i++){
		$workflow_html .= "<tr>\n";
	      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i].">".$i."</td>\n";
	      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i]." cellspacing=0 cellpadding=0>\n";
	      	$workflow_html .= "<table cellspacing=0 cellpadding=0 width=350>\n";
		for ($kk=0; $kk < $workflow_data[$i][0][4][0]; $kk++){
			$workflow_html .= "<tr>\n";
			if ($kk+1 < $workflow_data[$i][0][4][0]){
				$workflow_html .= "<td width='100%' style='border: 1px dotted gray; border-top-style:none; border-right-style:none;border-left-style:none;' align=center>";
			}else{
				$workflow_html .= "<td width='100%' style='border:none;' align=center>";
			}
			$workflow_html .= f_staff_name_get(str_replace("*", "", $workflow_data[$i][0][0][$kk]));
			$workflow_html .= "</td>\n";
			$workflow_html .= "</tr>\n";
		}
	      	$workflow_html .= "</table>\n";
	      	$workflow_html .= "</td>\n";

	      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i]." width=170>".$workflow_data[$i][0][5][0]." / ".$workflow_data[$i][0][4][0]."</td>\n";

		if ($workflow_cc_data[$i] == 0){
		      	$workflow_html .= "<td align=left colspan=3></td>\n";
		}else{
			for ($j=1; $j <= $workflow_cc_data[$i]; $j++){
				if ($j > 1){
					$workflow_html .= "<tr>\n";
				}
			      	$workflow_html .= "<td align=center width=350>".f_staff_name_get(str_replace("*", "", $workflow_data[$i][$j][0][0]))."</td>\n";
				$workflow_html .= "</tr>\n";
			}
		}
	}


		$workflow_html .= "</tbody>\n";
		$workflow_html .= "</table>\n";

	return $workflow_html;
}

function workflow_route_tmp($user_id)
{
	$workflow_html = "";
	$agree_f = 0;
	$agree_c = 0;

	for ($i = 1; $i <= 10; $i++){
		$workflow_cc_data[$i] = 0;

		for ($j=0; $j<6; $j++){
			for ($nn = 0; $nn < 10; $nn++){
				$workflow_data[$i][$j][0][$nn] = "";
				$workflow_data[$i][$j][1][$nn] = 0;
				$workflow_data[$i][$j][2][$nn] = "";
				$workflow_data[$i][$j][3][$nn] = "";
			}
			$workflow_data[$i][$j][4][0] = 0;
			$workflow_data[$i][$j][5][0] = 0;
		}
	}
	$next_count = 0;

	$sql = "SELECT * FROM ".$_SESSION["SCHEMA"].".m_workflow_tmp WHERE user_id = ".$user_id;
        $result = getList($sql);
	$f_rows = $result[0];


	$status = 0;
	$doc_cd = 0;
	$input_user = 0;
	if($f_rows){
		$cc1 = $f_rows["cc1"];
		$cc2 = $f_rows["cc2"];
		$cc3 = $f_rows["cc3"];
		$cc4 = $f_rows["cc4"];
		$cc5 = $f_rows["cc5"];
		$doc_cd = $f_rows["flow_doc_code"];
		$result_id = $f_rows["result_id"];
			for ($i=1; $i <= 10; $i++){
				if ($f_rows["next".$i."_data"] != "*0-0"){
					$next_count ++;
//複数承認を分解する
					list($split_staff_cnt, $split_staff_name, $split_staff_target, $split_apply_cnt) = workflow_root_dsp_split2($f_rows["next".$i."_data"]);
					for ($nn=0; $nn < $split_staff_cnt; $nn++){
						$workflow_data[$next_count][0][0][$nn] .= $split_staff_name[$nn];
						$workflow_data[$next_count][0][1][$nn] .= $split_staff_target[$nn];
						$workflow_data[$next_count][0][2][$nn] = $f_rows["next".$i."_data_date".($nn+1)];
						$workflow_data[$next_count][0][3][$nn] = $f_rows["next".$i."_data_comment".($nn+1)];
					}
					$workflow_data[$next_count][0][4][0] = $split_staff_cnt;
					$workflow_data[$next_count][0][5][0] = $split_apply_cnt;

/*
					list($terget_id, $terget_flag) = explode("-", $f_rows["next".$i."_data"]);
					$workflow_data[$next_count][0][0] = $terget_id;
					$workflow_data[$next_count][0][1] = $terget_flag;
*/


//ここまで
					$next_cc_count = 0;
					for ($j=1; $j <= 5; $j++){
						if ($f_rows["next".$i."_cc".$j."_data"] != "*0-0" && $f_rows["next".$i."_cc".$j."_data"] != ""){
							$next_cc_count ++;
							list($terget_id, $terget_flag) = explode("-", $f_rows["next".$i."_cc".$j."_data"]);
							$workflow_data[$next_count][$next_cc_count][0][0] = $terget_id;
							$workflow_data[$next_count][$next_cc_count][1][0] = $terget_flag;
							$workflow_data[$next_count][$next_cc_count][2][0] = $f_rows["next".$i."_cc".$j."_data_date"];
						}
					}
					$workflow_cc_data[$next_count] = $next_cc_count;
				}
			}
	}


	$workflow_html .= "<tr>\n";
	$workflow_html .= "<th width=170>NO</th>\n";
	$workflow_html .= "<th width=260>承認者</th>\n";
	$workflow_html .= "<th width=70>多数決</th>\n";
	$workflow_html .= "<th width=260>参照者</th>\n";
	$workflow_html .= "</tr>\n";
	if ($next_count == 0){
	}else{
			$cc = "";
			if ($cc1 != "0"){
				$cc .= f_staff_name_get(workflow_kian_cc_staff($cc1));
			}
			if ($cc2 != "0"){
				$cc .= "<br/>".f_staff_name_get(workflow_kian_cc_staff($cc2));
			}
			if ($cc3 != "0"){
				$cc .= "<br/>".f_staff_name_get(workflow_kian_cc_staff($cc3));
			}
			if ($cc4 != "0"){
				$cc .= "<br/>".f_staff_name_get(workflow_kian_cc_staff($cc4));
			}
			if ($cc5 != "0"){
				$cc .= "<br/>".f_staff_name_get(workflow_kian_cc_staff($cc5));
			}

		$workflow_html .= "<tr>\n";
	      	$workflow_html .= "<td align=center>0</td>\n";
	      	$workflow_html .= "<td align=left>起案者</td>\n";
	      	$workflow_html .= "<td align=left> </td>\n";

	      	$workflow_html .= "<td align=left>";
		$workflow_html .= $cc;
		$workflow_html .= "</td>\n";

		$workflow_html .= "</tr>\n";
	}

	for ($i=1; $i <= $next_count; $i++){
		$workflow_html .= "<tr>\n";
	      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i].">".$i."</td>\n";
	      	$workflow_html .= "<td align=left rowspan=".$workflow_cc_data[$i]." cellspacing=0 cellpadding=0>\n";
	      	$workflow_html .= "<table cellspacing=0 cellpadding=0 width=100%>\n";
		for ($kk=0; $kk < $workflow_data[$i][0][4][0]; $kk++){
			$workflow_html .= "<tr>\n";
			if ($kk+1 < $workflow_data[$i][0][4][0]){
				$workflow_html .= "<td width='100%' style='border: 1px dotted gray; border-top-style:none; border-right-style:none;border-left-style:none;' align=left>";
			}else{
				$workflow_html .= "<td width='100%' style='border:none;' align=left>";
			}
			$workflow_html .= f_staff_name_get(str_replace("*", "", $workflow_data[$i][0][0][$kk]));
			$workflow_html .= "</td>\n";
			$workflow_html .= "</tr>\n";
		}
	      	$workflow_html .= "</table>\n";
	      	$workflow_html .= "</td>\n";

	      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i].">".$workflow_data[$i][0][5][0]." / ".$workflow_data[$i][0][4][0]."</td>\n";

		if ($workflow_cc_data[$i] == 0){
		      	$workflow_html .= "<td align=left colspan=3></td>\n";
		}else{
			for ($j=1; $j <= $workflow_cc_data[$i]; $j++){
				if ($j > 1){
					$workflow_html .= "<tr>\n";
				}
			      	$workflow_html .= "<td align=left>".f_staff_name_get(str_replace("*", "", $workflow_data[$i][$j][0][0]))."</td>\n";
				$workflow_html .= "</tr>\n";
			}
		}
	}


	return $workflow_html;
}

function workflow_stat($cd, $id, $staff_code, $home, $code, $page)
{
	$staff_code_tmp = $staff_code;
	$staff_code = "*".$staff_code;

	$workflow_html = "";
	$agree_f = 0;
	$agree_c = 0;

	$stat[0] = "<font color=black>-</font>";
	$stat[1] = "<font color=black>承認待ち</font>";
	$stat[2] = "<font color=red>承認中</font>";
	$stat[3] = "<font color=blue>承認済み</font>";
	$stat[4] = "<font color=red>否決済み</font>";
//	$stat[5] = "<font color=black>取り戻し</font>";
	$stat[5] = "<font color=green>差し戻し</font>";
	$stat_cc[0] = "<font color=black>-</font>";
	$stat_cc[1] = "<font color=black>参照待ち</font>";
	$stat_cc[2] = "<font color=red>未参照</font>";
	$stat_cc[3] = "<font color=blue>参照済み</font>";

	for ($j = 1; $j <= 5; $j ++){
		$workflow_cc_kian[$j][0] = "";
		$workflow_cc_kian[$j][1] = "";
		$workflow_cc_kian[$j][2] = "";
	}
	$workflow_cc_kian_cnt = 0;

	for ($i = 1; $i <= 10; $i++){
		$workflow_cc_data[$i] = 0;

		for ($j=0; $j<6; $j++){
			for ($nn = 0; $nn < 10; $nn++){
				$workflow_data[$i][$j][0][$nn] = "";
				$workflow_data[$i][$j][1][$nn] = 0;
				$workflow_data[$i][$j][2][$nn] = "";
				$workflow_data[$i][$j][3][$nn] = "";
			}
			$workflow_data[$i][$j][4][0] = 0;
			$workflow_data[$i][$j][5][0] = 0;
		}
	}
	$next_count = 0;

	$sql = "SELECT * FROM ".$_SESSION["SCHEMA"].".t_workflow WHERE workflow_id = ".$id;
        $result = getList($sql);
	$f_rows = $result[0];

	$status = 0;
	$doc_cd = 0;
	$input_user = 0;
	$remand_f = 0;
	if($f_rows){
		$status = $f_rows["stat"];
		$input_user = $f_rows["user_id"];
		$remand_f = $f_rows["remand_f"];
		$doc_cd = $f_rows["flow_doc_code"];
		$result_id = $f_rows["result_id"];

			for ($j = 1; $j <= 5; $j ++){
				if ($f_rows["cc".$j] != "0"){
					$workflow_cc_kian_cnt ++;
					$workflow_cc_kian[$workflow_cc_kian_cnt][0] = workflow_kian_cc_staff($f_rows["cc".$j]);
					$workflow_cc_kian[$workflow_cc_kian_cnt][1] = $f_rows["cc".$j."_date"];
					$workflow_cc_kian[$workflow_cc_kian_cnt][2] = $f_rows["cc".$j];
				}
			}

			for ($i=1; $i <= 10; $i++){
				if ($f_rows["next".$i."_data"] != "*0-0"){
					$next_count ++;
//複数承認を分解する
					list($split_staff_cnt, $split_staff_name, $split_staff_target, $split_apply_cnt) = workflow_root_dsp_split2($f_rows["next".$i."_data"]);
					for ($nn=0; $nn < $split_staff_cnt; $nn++){
						$workflow_data[$next_count][0][0][$nn] .= $split_staff_name[$nn];
						$workflow_data[$next_count][0][1][$nn] .= $split_staff_target[$nn];
						$workflow_data[$next_count][0][2][$nn] = $f_rows["next".$i."_data_date".($nn+1)];
						$workflow_data[$next_count][0][3][$nn] = $f_rows["next".$i."_data_comment".($nn+1)];
					}
					$workflow_data[$next_count][0][4][0] = $split_staff_cnt;
					$workflow_data[$next_count][0][5][0] = $split_apply_cnt;

/*
					list($terget_id, $terget_flag) = explode("-", $f_rows["next".$i."_data"]);
					$workflow_data[$next_count][0][0] = $terget_id;
					$workflow_data[$next_count][0][1] = $terget_flag;
*/


//ここまで
					$next_cc_count = 0;
					for ($j=1; $j <= 5; $j++){
						if ($f_rows["next".$i."_cc".$j."_data"] != "*0-0" && $f_rows["next".$i."_cc".$j."_data"] != ""){
							$next_cc_count ++;
							list($terget_id, $terget_flag) = explode("-", $f_rows["next".$i."_cc".$j."_data"]);
							$workflow_data[$next_count][$next_cc_count][0][0] = $terget_id;
							$workflow_data[$next_count][$next_cc_count][1][0] = $terget_flag;
							$workflow_data[$next_count][$next_cc_count][2][0] = $f_rows["next".$i."_cc".$j."_data_date"];
						}
					}
					$workflow_cc_data[$next_count] = $next_cc_count;
				}
			}
	}


	$workflow_html .= "<table>\n";
	$workflow_html .= "<tbody>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<th width=30 rowspan=2>NO</td>\n";
	$workflow_html .= "<th colspan=5>承認者</th>\n";
	$workflow_html .= "<th colspan=3>参照者</th>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<th width=100>名前</th>\n";
	$workflow_html .= "<th width=180>状況</th>\n";
	$workflow_html .= "<th width=200>日付</th>\n";
	$workflow_html .= "<th width=60>多数決</th>\n";
	$workflow_html .= "<th width=320>コメント</th>\n";
	$workflow_html .= "<th width=100>名前</th>\n";
	$workflow_html .= "<th width=70>状況</th>\n";
	$workflow_html .= "<th width=200>日付</th>\n";
	$workflow_html .= "</tr>\n";

	$comment_f = 0;
	$comment_data = "";

	$workflow_html .= "<tr>\n";
      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_kian_cnt.">0</td>\n";
      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_kian_cnt." colspan=5>起案者</td>\n";
	if ($workflow_cc_kian_cnt != 0){
		for ($i=1; $i <= $workflow_cc_kian_cnt; $i++){
					if ($i > 1){
						$workflow_html .= "<tr>\n";
					}
				      	$workflow_html .= "<td align=center>".f_staff_name_get($workflow_cc_kian[$i][0])."</td>\n";
					$kian_cc_stat = workflow_kian_cc($workflow_cc_kian[$i][2]);
					if ($cd == 1 && $staff_code == "*".$workflow_cc_kian[$i][0]){	//参照ボタン表示
						$workflow_html .= "<td align=center><input type=\"button\" onclick=\"agree_view()\" value=\"参照\"></td>\n";
					}else{		//参照のみ
					      	$workflow_html .= "<td align=center>".$stat_cc[$kian_cc_stat]."</td>\n";
					}
				      	$workflow_html .= "<td align=center>".$workflow_cc_kian[$i][1]."</td>\n";
					$workflow_html .= "</tr>\n";
		}
	}else{
	      	$workflow_html .= "<td colspan=3></td>\n";
		$workflow_html .= "</tr>\n";
	}

	for ($i=1; $i <= $next_count; $i++){
		$workflow_html .= "<tr>\n";
	      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i].">".$i."</td>\n";
//ここから修正

//	      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i].">".f_staff_name_get($workflow_data[$i][0][0])."</td>\n";
//承認人数分作成
	      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i]." cellspacing=0 cellpadding=0>\n";
	      	$workflow_html .= "<table cellspacing=0 cellpadding=0 width=100>\n";
		for ($kk=0; $kk < $workflow_data[$i][0][4][0]; $kk++){
			$workflow_html .= "<tr>\n";
			if ($kk+1 < $workflow_data[$i][0][4][0]){
				$workflow_html .= "<td width='100%' style='border: 1px dotted gray; border-top-style:none; border-right-style:none;border-left-style:none;' align=center>";
			}else{
				$workflow_html .= "<td width='100%' style='border:none;' align=center>";
			}
			$workflow_html .= f_staff_name_get(str_replace("*", "", $workflow_data[$i][0][0][$kk]));
			$workflow_html .= "</td>\n";
			$workflow_html .= "</tr>\n";
		}
	      	$workflow_html .= "</table>\n";
	      	$workflow_html .= "</td>\n";

		$target_staff = -1;
		for ($kk=0; $kk < $workflow_data[$i][0][4][0]; $kk++){
			if($staff_code == $workflow_data[$i][0][0][$kk] && $workflow_data[$i][0][1][$kk] == 2){
				$target_staff = $kk;
				break;
			}
		}

		$comment_f = 0;
		$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i].">\n";
	      	$workflow_html .= "<table cellspacing=0 cellpadding=0 width=180>\n";
		for ($kk=0; $kk < $workflow_data[$i][0][4][0]; $kk++){
			$workflow_html .= "<tr>\n";
			if ($kk+1 < $workflow_data[$i][0][4][0]){
				$workflow_html .= "<td width='100%' style='border: 1px dotted gray; border-top-style:none; border-right-style:none;border-left-style:none;' align=center>";
			}else{
				$workflow_html .= "<td width='100%' style='border:none;' align=center>";
			}
			if ($cd == 1 && $kk == $target_staff && $agree_f == 0){	//承認ボタン表示
				$comment_f = 1;
				$agree_f = 1;
				$workflow_html .= "<input type=\"button\" onclick=\"agree()\" value=\"承認\">&nbsp;<input type=\"button\" onclick=\"against()\" value=\"否決\">&nbsp;<input type=\"button\" onclick=\"remand()\" value=\"差し戻し\">";
			}else{		//参照のみ
			      	$workflow_html .= $stat[intval($workflow_data[$i][0][1][$kk])];
			}
			$workflow_html .= "</td>\n";
			$workflow_html .= "</tr>\n";
		}
	      	$workflow_html .= "</table>\n";
		$workflow_html .= "</td>\n";

		$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i].">\n";
	      	$workflow_html .= "<table cellspacing=0 cellpadding=0 width=200>\n";
		$ok_staff = 0;
		for ($kk=0; $kk < $workflow_data[$i][0][4][0]; $kk++){
			$workflow_html .= "<tr>\n";
			if ($kk+1 < $workflow_data[$i][0][4][0]){
				$workflow_html .= "<td width='100%' style='border: 1px dotted gray; border-top-style:none; border-right-style:none;border-left-style:none;' align=center>";
			}else{
				$workflow_html .= "<td width='100%' style='border:none;' align=center>";
			}
			if ($status == 4 && $workflow_data[$i][0][1][$kk] == 2){
			      	$workflow_html .= "<font color=red>[取り戻し]</font>";
			}else{
				$workflow_html .= $workflow_data[$i][0][2][$kk];
			}
			if ($workflow_data[$i][0][1][$kk] == 3){
				$ok_staff ++;
			}
			$workflow_html .= "</td>\n";
			$workflow_html .= "</tr>\n";
		}
	      	$workflow_html .= "</table>\n";
		$workflow_html .= "</td>\n";



	      	$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i].">".$workflow_data[$i][0][5][0]." / ".$workflow_data[$i][0][4][0]."</td>\n";


		$workflow_html .= "<td align=center rowspan=".$workflow_cc_data[$i]." align=left>\n";
	      	$workflow_html .= "<table align=left cellspacing=0 cellpadding=0 width=320>\n";
		for ($kk=0; $kk < $workflow_data[$i][0][4][0]; $kk++){
			$workflow_html .= "<tr>\n";
			if ($kk+1 < $workflow_data[$i][0][4][0]){
				$workflow_html .= "<td align=left width='100%' style='border: 1px dotted gray; border-top-style:none; border-right-style:none;border-left-style:none;' align=center>";
			}else{
				$workflow_html .= "<td align=left width='100%' style='border:none;' align=center>";
			}

			if ($cd == 1 && $kk == $target_staff && $agree_c == 0){	//コメント
				$agree_c = 1;
			      	$workflow_html .= "<textarea name=\"d_comment\" cols=40 rows=3></textarea>";
			}else{
			      	$workflow_html .= nl2br($workflow_data[$i][0][3][$kk]);
			}

			$workflow_html .= "</td>\n";
			$workflow_html .= "</tr>\n";
		}
	      	$workflow_html .= "</table>\n";
		$workflow_html .= "</td>\n";




		if ($workflow_cc_data[$i] == 0){
		      	$workflow_html .= "<td align=left colspan=3></td>\n";
		}else{
			for ($j=1; $j <= $workflow_cc_data[$i]; $j++){
				if ($j > 1){
					$workflow_html .= "<tr>\n";
				}
			      	$workflow_html .= "<td align=center>".f_staff_name_get(str_replace("*", "", $workflow_data[$i][$j][0][0]))."</td>\n";
				if ($cd == 1 && $staff_code == $workflow_data[$i][$j][0][0] && $agree_f == 0){	//参照ボタン表示
					$workflow_html .= "<td align=center><input type=\"button\" onclick=\"agree_view()\" value=\"参照\"></td>\n";
				}else{		//参照のみ
				      	$workflow_html .= "<td align=center>".$stat_cc[$workflow_data[$i][$j][1][0]]."</td>\n";
				}
			      	$workflow_html .= "<td align=center>".$workflow_data[$i][$j][2][0]."</td>\n";
				$workflow_html .= "</tr>\n";
			}
		}
	}
//ここまで

		$workflow_html .= "</tbody>\n";
		$workflow_html .= "</table>\n";

$workflow2_html = "";

/*
if ($comment_f ==1){
		$workflow2_html .= "<table>\n";
		$workflow2_html .= "<tbody>\n";
		$workflow2_html .= "<tr>\n";
		$workflow2_html .= "<td align=center class=\"title_td\">コメント</td>\n";
		$workflow2_html .= "</tr>\n";
		$workflow2_html .= "<tr>\n";
	  	$workflow2_html .= "<td class=\"item_td\" align=\"center\"><textarea name=\"d_comment\" cols=80 rows=5></textarea></td>\n";
		$workflow2_html .= "</tr>\n";
		$workflow2_html .= "</tbody>\n";
		$workflow2_html .= "</table>\n";
}
*/

$workflow2_html .= $workflow_html;
$f = 0;

	if ($cd == 3 && $status == 1 && $input_user == $staff_code_tmp){	//取り戻しボタンを表示
		$workflow2_html .= "<br /><p align=center><input type=\"button\" onclick=\"stop()\" value=\"取り戻す\" class=\"return\" />\n";
		$f = 1;

//		$workflow2_html .= "<a href=\"#\" onclick=\"stop()\"><span class=\"undo\" /></span>取り戻す</a>\n";
	}

	if (($status == 2 || $status == 3 || $status == 4) && $input_user == $staff_code_tmp){	//再利用ボタンを表示
		$workflow2_html .= "<br /><p align=center><input type=\"button\" onclick=\"location.href='apply.php?workflow_id=".$id."&remand=1'\" value=\"再利用\" class=\"confirm\" />\n";
		$f = 1;
	}else if ($status == 5 && $input_user == $staff_code_tmp){	//再申請ボタンを表示
		if ($remand_f == 0){
			$workflow2_html .= "<br /><p align=center><input type=\"button\" onclick=\"location.href='apply.php?workflow_id=".$id."&remand=1'\" value=\"再申請\" class=\"confirm\" />\n";
			$f = 1;
		}
	}
/*
	if ($cd == 3 && $doc_cd == 1 && $status == 3 && $result_id == 0 &&  $input_user == $staff_code){	//結果報告対象
		if ($f == 0){
			$workflow2_html .= "<br /><p align=center>";
		}else{
			$workflow2_html .= "&nbsp;&nbsp;";
		}
		$workflow2_html .= "<input type=\"button\" onclick=\"location.href='result_sel.php?result_id=".$id."&home=".$home."&cd=".$cd."&page=".$page."'\" value=\"結果報告\" class=\"confirm\" />";
		$f = 1;
	}
*/
	if ($f == 1){
		$workflow2_html .= "</p>\n";
	}

	return $workflow2_html;
}

function target_tekiyou($table, $serch_date){

	$sql = "select * from ".$_SESSION["SCHEMA"].".".$table." where target_date <= '".$serch_date."' order by target_date desc limit 1";
        $result = getList($sql);
	$f_rows = $result[0];
	$target_date = "";
	if($f_rows){
		$target_date = $f_rows["target_date"];
	}

return $target_date;
}

function target_tekiyou_where($table, $serch_date, $where){

	$sql = "select * from ".$_SESSION["SCHEMA"].".".$table." where target_date <= '".$serch_date."' and ".$where." order by target_date desc limit 1";
        $result = getList($sql);
	$f_rows = $result[0];
	$target_date = "";
	if($f_rows){
		$target_date = $f_rows["target_date"];
	}

return $target_date;
}


function workflow_format_read($cd, $wdoc_no, $workflow_id)
{

$html = "";
$script_html = "";



	if ($cd == 0){	//入力画面
		$script_html .= "<script type='text/javascript'>\n";
		$script_html .= "$(function(){\n";

//		$target_date = target_tekiyou("t_workflow_format", date("Y-m-d"));
		$where = "wdoc_no = '".$wdoc_no."'";
		$target_date = target_tekiyou_where("t_workflow_format", date("Y-m-d"), $where);
		if ($target_date == ""){
			$rows = 0;
		}else{
			$sql = "select * from ".$_SESSION["SCHEMA"].".t_workflow_format where ".$where." and target_date = '".$target_date."' order by num asc";
			$cnt = 0;
		        $rows = getList($sql);
	        }

	        if($rows){
			while($row = $rows[$cnt]) {
				list($data1, $data2, $data3, $data4, $data5, $data6, $data7, $data8, $data9, $data10, $data11, $data12, $data13, $data14, $data15, $data16) = explode("-", $row["data"]);

				switch($data1)
				{
					case 1:	//th
						$html .= "<th width='".$data6."' height='".$data7."' rowspan='".$data4."' colspan='".$data5."'>".$data2."</th>\n";
						break;
					case 2:	//tr
						$html .= "<tr>\n";
						break;
					case 3:	//td
						if ($data3 != "none"){
							$html .= "<td align='".$data3."' width='".$data6."' height='".$data7."' rowspan='".$data4."' colspan='".$data5."'>\n";
						}else{
							$html .= "<td width='".$data6."' height='".$data7."' rowspan='".$data4."' colspan='".$data5."'>\n";
						}
						break;
					case 10:	//input
						switch($data12)
						{
							case 2:	//type = text
								if ($data16 != "none"){
									if ($data11 == "number"){
										$html .= "<input type='text' id='".$data8."' name='".$data10."' size='".$data14."' value='".f_post_name_data_get($workflow_id, $data10)."' style='text-align: right;'>&nbsp;".$data16."\n";
									}else{
										$html .= "<input type='text' id='".$data8."' name='".$data10."' size='".$data14."' value='".f_post_name_data_get($workflow_id, $data10)."'>&nbsp;".$data16."\n";
									}
								}else{
									if ($data11 == "number"){
										$html .= "<input type='text' id='".$data8."' name='".$data10."' size='".$data14."' value='".f_post_name_data_get($workflow_id, $data10)."' style='text-align: right;'>\n";
									}else{
										$html .= "<input type='text' id='".$data8."' name='".$data10."' size='".$data14."' value='".f_post_name_data_get($workflow_id, $data10)."'>\n";
									}
								}
								break;
							case 4:	//type = dsp 表示のみ
								if ($data16 != "none"){
									$html .= $data2." ".$data16."\n";
								}else{
									$html .= $data2."\n";
								}
								break;
							case 1:	//type = radio
								$radio_data = f_post_name_data_get($workflow_id, $data10);
								if ($radio_data == $data2){
									$html .= "<input type='radio' id='".$data8."' name='".$data10."' size='".$data14."' value='".$data2."' checked> ".$data16."\n";
								}else{
									$html .= "<input type='radio' id='".$data8."' name='".$data10."' size='".$data14."' value='".$data2."'> ".$data16."\n";
								}
								break;
							case 3:	//type = checkbox
								$check_data = f_post_name_data_get($workflow_id, $data10);
								if ($check_data == $data2){
									$html .= "<input type='checkbox' id='".$data8."' name='".$data10."' size='".$data14."' value='".$data2."' checked> ".$data16."\n";
								}else{
									$html .= "<input type='checkbox' id='".$data8."' name='".$data10."' size='".$data14."' value='".$data2."'> ".$data16."\n";
								}
								break;
						}
						break;
					case 20:	//textarea
//						$html .= "<textarea id='".$data8."' name='".$data10."' rows='".$data4."' cols='".$data5."' value='".nl2br(f_post_name_data_get($workflow_id, $data10))."'></textarea>\n";
//						if ($data2 != "0"){
							$html .= "<textarea id='".$data8."' name='".$data10."' rows='".$data4."' cols='".$data5."'>".nl2br(f_post_name_data_get($workflow_id, $data10))."</textarea>\n";
//						}else{
//							$html .= "<textarea id='".$data8."' name='".$data10."' rows='".$data4."' cols='".$data5."'></textarea>\n";
//						}
						break;
					case 30:	//date
						$html .= "<input type='text' id='".$data8."' name='".$data10."' size='".$data14."' value='".f_post_name_data_get($workflow_id, $data10)."'>\n";
						switch($data12)
						{
							case 1:	//年
								$script_html .= "$('#".$data8."').datepicker({dateFormat:'yy'});\n";
								$script_html .= "$('#".$data8."').mask('9999');\n";
								break;
							case 2:	//年月
								$script_html .= "$('#".$data8."').ympicker({dateFormat:'yy-mm'});\n";
								$script_html .= "$('#".$data8."').mask('9999-99');\n";
								break;
							case 3:	//年月日
								$script_html .= "$('#".$data8."').datepicker({dateFormat:'yy-mm-dd'});\n";
								$script_html .= "$('#".$data8."').mask('9999-99-99');\n";
								break;
						}
						break;
					case 40:	//time
						$html .= "<input type='text' id='".$data8."' name='".$data10."' size='".$data14."' value='".f_post_name_data_get($workflow_id, $data10)."'>\n";
						switch($data12)
						{
							case 1:	//時
								$script_html .= "$('#".$data8."').mask('99');\n";
								break;
							case 2:	//時分
								$script_html .= "$('#".$data8."').mask('99:99');\n";
								break;
							case 3:	//分
								$script_html .= "$('#".$data8."').mask('99');\n";
								break;
						}
						break;
					case 60:	//select
						$html .= f_code_free_select($data13, $data2, $data8, $data10);
//						$html .= f_code_free_select($data13, 0, $data8, $data10);
						break;
					case 4:	// /td
						$html .= "</td>\n";
						break;
					case 5:	// /tr
						$html .= "</tr>\n";
						break;
				}

				$cnt += 1;
			}


		}else{
			$html = "<font color=red>申請書マスタに異常があります。システム管理者にご連絡ください</font>\n";
		}

		$script_html .= "});\n";
		$script_html .= "</script>\n";

	}else{	//表示画面

		$sql = "SELECT * FROM ".$_SESSION["SCHEMA"].".t_workflow WHERE workflow_id = ".$workflow_id;
	        $result = getList($sql);
		$f_rows = $result[0];
		$create_date = "";
		if($f_rows){
			$create_date = $f_rows["create_date"];
			$create_date = date('Y-m-d', strtotime($create_date));

//			$target_date = target_tekiyou("t_workflow_format", $create_date);
			$where = "wdoc_no = '".$wdoc_no."'";
			$target_date = target_tekiyou_where("t_workflow_format", $create_date, $where);
			if ($target_date == ""){
				$rows = 0;
			}else{
				$sql = "select * from ".$_SESSION["SCHEMA"].".t_workflow_format where ".$where." and target_date = '".$target_date."' order by num asc";

				$post_name_save = "";
				$cnt = 0;
			        $rows = getList($sql);
		        }
		        if($rows){
				while($row = $rows[$cnt]) {

					list($data1, $data2, $data3, $data4, $data5, $data6, $data7, $data8, $data9, $data10, $data11, $data12, $data13, $data14, $data15, $data16) = explode("-", $row["data"]);

					switch($data1)
					{
						case 1:	//th
							$html .= "<th width='".$data6."' height='".$data7."' rowspan='".$data4."' colspan='".$data5."' style='word-break:break-all;'>".$data2."</th>\n";
							break;
						case 2:	//tr
							$html .= "<tr>\n";
							break;
						case 3:	//td
							if ($data3 != "none"){
								$html .= "<td align='".$data3."' width='".$data6."' height='".$data7."' rowspan='".$data4."' colspan='".$data5."' style='word-break:break-all;'>\n";
							}else{
								$html .= "<td width='".$data6."' height='".$data7."' rowspan='".$data4."' colspan='".$data5."' style='word-break:break-all;'>\n";
							}
							break;
						case 10:	//input
							switch($data12)
							{
								case 2:	//type = text
									if ($data11 == "number"){
										if ($data16 != "none"){
											$html .= number_format(f_post_name_data_get($workflow_id, $data10))."&nbsp;".$data16;
										}else{
											$html .= number_format(f_post_name_data_get($workflow_id, $data10));
										}
									}else{
										if ($data16 != "none"){
											$html .= htmlspecialchars(f_post_name_data_get($workflow_id, $data10))."&nbsp;".$data16;
										}else{
											$html .= htmlspecialchars(f_post_name_data_get($workflow_id, $data10));
										}
									}
									break;
								case 4:	//type = dsp 表示のみ
									if ($data16 != "none"){
										$html .= $data2." ".$data16."\n";
									}else{
										$html .= $data2."\n";
									}
									break;
								case 1:	//type = radio
									if ($post_name_save != $data10){
										$html .= f_post_name_data_get($workflow_id, $data10);
									}
									$post_name_save = $data10;
									break;
								case 3:	//type = checkbox
									if ($post_name_save != $data10){
										$html .= f_post_name_data_get($workflow_id, $data10);
									}
									$post_name_save = $data10;
									break;
							}
							break;
						case 20:	//textarea
							$html .= nl2br(htmlspecialchars(f_post_name_data_get($workflow_id, $data10)));
							break;
						case 30:	//date
							$html .= f_post_name_data_get($workflow_id, $data10);
							break;
						case 40:	//time
							$html .= f_post_name_data_get($workflow_id, $data10);
							break;
						case 60:	//select
							$html .= f_code_dsp($data13, f_post_name_data_get($workflow_id, $data10));
							break;
						case 4:	// /td
							$html .= "</td>\n";
							break;
						case 5:	// /tr
							$html .= "</tr>\n";
							break;
					}
					$cnt += 1;
				}


			}else{
				$html = "<font color=red>申請書マスタに異常があります。システム管理者にご連絡ください</font>\n";
			}
		}else{
			$html = "<font color=red>申請書マスタに異常があります。システム管理者にご連絡ください</font>\n";
		}
	}

	return array($html, $script_html);
}

function workflow_format_read_mobile($wdoc_no, $workflow_id)
{
	$content = "";

		$sql = "SELECT * FROM ".$_SESSION["SCHEMA"].".t_workflow WHERE workflow_id = ".$workflow_id;
	        $result = getList($sql);
		$f_rows = $result[0];
		$create_date = "";
		if($f_rows){
			$create_date = $f_rows["create_date"];
			$create_date = date('Y-m-d', strtotime($create_date));

//			$target_date = target_tekiyou("t_workflow_format", $create_date);
			$where = "wdoc_no = '".$wdoc_no."'";
			$target_date = target_tekiyou_where("t_workflow_format", $create_date, $where);
			if ($target_date == ""){
				$rows_doc = 0;
			}else{
				$sql_doc = "select * from ".$_SESSION["SCHEMA"].".t_workflow_format where ".$where." and target_date = '".$target_date."' order by num asc";

				$post_name_save = "";
				$cnt = 0;
			        $rows_doc = getList($sql_doc);
		        }
		        if($rows_doc){
				while($row_doc = $rows_doc[$cnt]) {

					list($data1, $data2, $data3, $data4, $data5, $data6, $data7, $data8, $data9, $data10, $data11, $data12, $data13, $data14, $data15, $data16) = explode("-", char_chg("to_dsp", $row_doc["data"]));

					switch($data1)
					{
						case 1:	//th
							$content .= $data2.":";
							$flag = 1;
//							$content .= $data2.":\n";
							break;
						case 2:	//tr
							break;
						case 3:	//td
							break;
						case 10:	//input
							switch($data12)
							{
								case 2:	//type = text
									$tmp = f_post_name_data_get($workflow_id, $data10);
									if($tmp != ""){
										if ($data11 == "number"){
											if ($data16 != "none"){
												$content .= number_format($tmp)."&nbsp;".$data16."&nbsp;";
											}else{
												$content .= number_format($tmp)."&nbsp;";
											}
										}else{
											if ($data16 != "none"){
												$content .= $tmp."&nbsp;".$data16."&nbsp;";
											}else{
												$content .= $tmp."&nbsp;";
											}
										}
										$flag = 1;
									}
									break;
								case 4:	//type = dsp 表示のみ
/*
									if ($data16 != "none"){
										$content .= $data2." ".$data16."\n";
									}else{
										$content .= $data2."\n";
									}
*/
									break;
								case 1:	//type = radio
									if ($post_name_save != $data10){
										$content .= f_post_name_data_get($workflow_id, $data10);
									}
									$post_name_save = $data10;
									break;
								case 3:	//type = checkbox
									if ($post_name_save != $data10){
										$content .= f_post_name_data_get($workflow_id, $data10);
									}
									$post_name_save = $data10;
									break;
							}
							break;
						case 20:	//textarea
							$tmp = f_post_name_data_get($workflow_id, $data10);
							if($tmp != ""){
								$content .= nl2br($tmp)."&nbsp;";
								$flag = 1;
							}
							break;
						case 30:	//date
							$tmp = f_post_name_data_get($workflow_id, $data10);
							if($tmp != ""){
								$content .= $tmp."&nbsp;";
								$flag = 1;
							}
							break;
						case 40:	//time
							$tmp = f_post_name_data_get($workflow_id, $data10);
							if($tmp != ""){
								$content .= $tmp."&nbsp;";
								$flag = 1;
							}
							break;
						case 60:	//select
							$tmp = f_post_name_data_get($workflow_id, $data10);
							if($tmp != 0){
								$content .= f_code_dsp($data13, $tmp)."&nbsp;";
								$flag = 1;
							}
							break;
						case 4:	// /td
							break;
						case 5:	// /tr
							if ($flag == 1){
								$content .= "<br />\n";
							}
							$flag = 0;
							break;
					}
					$cnt += 1;
				}


			}else{
				$content = "<font color=red>申請書マスタに異常があります。システム管理者にご連絡ください</font>\n";
			}
		}else{
			$content = "<font color=red>申請書マスタに異常があります。システム管理者にご連絡ください</font>\n";
		}

	return ($content);
}

function workflow_agree($mode, $staff_code, $workflow_id, $d_comment, $login_staff)
{
	$ret_f = 0;
	$staff_code_kian = $staff_code;
	$staff_code = "*".$staff_code;

		if ($mode == 1 || $mode == 3 || $mode == 5){
			$sql = "SELECT * FROM ".$_SESSION["SCHEMA"].".t_workflow WHERE (next1_data LIKE '%".$staff_code."-2%' or next2_data LIKE '%".$staff_code."-2%' or next3_data LIKE '%".$staff_code."-2%' or next4_data LIKE '%".$staff_code."-2%' or next5_data LIKE '%".$staff_code."-2%' or next6_data LIKE '%".$staff_code."-2%' or next7_data LIKE '%".$staff_code."-2%' or next8_data LIKE '%".$staff_code."-2%' or next9_data LIKE '%".$staff_code."-2%' or next10_data LIKE '%".$staff_code."-2%') and stat = '1' and  workflow_id = ".$workflow_id;
		}else{
			$sql = "SELECT * FROM ".$_SESSION["SCHEMA"].".t_workflow WHERE (";
			$sql .= "cc1 = '".$staff_code_kian."-2' or cc2 = '".$staff_code_kian."-2' or cc3 = '".$staff_code_kian."-2' or cc4 = '".$staff_code_kian."-2' or cc5 = '".$staff_code_kian."-2' or ";
			$sql .= "next1_cc1_data = '".$staff_code."-2' or next1_cc2_data = '".$staff_code."-2' or next1_cc3_data = '".$staff_code."-2' or next1_cc4_data = '".$staff_code."-2' or next1_cc5_data = '".$staff_code."-2' or ";
			$sql .= "next2_cc1_data = '".$staff_code."-2' or next2_cc2_data = '".$staff_code."-2' or next2_cc3_data = '".$staff_code."-2' or next2_cc4_data = '".$staff_code."-2' or next2_cc5_data = '".$staff_code."-2' or ";
			$sql .= "next3_cc1_data = '".$staff_code."-2' or next3_cc2_data = '".$staff_code."-2' or next3_cc3_data = '".$staff_code."-2' or next3_cc4_data = '".$staff_code."-2' or next3_cc5_data = '".$staff_code."-2' or ";
			$sql .= "next4_cc1_data = '".$staff_code."-2' or next4_cc2_data = '".$staff_code."-2' or next4_cc3_data = '".$staff_code."-2' or next4_cc4_data = '".$staff_code."-2' or next4_cc5_data = '".$staff_code."-2' or ";
			$sql .= "next5_cc1_data = '".$staff_code."-2' or next5_cc2_data = '".$staff_code."-2' or next5_cc3_data = '".$staff_code."-2' or next5_cc4_data = '".$staff_code."-2' or next5_cc5_data = '".$staff_code."-2' or ";
			$sql .= "next6_cc1_data = '".$staff_code."-2' or next6_cc2_data = '".$staff_code."-2' or next6_cc3_data = '".$staff_code."-2' or next6_cc4_data = '".$staff_code."-2' or next6_cc5_data = '".$staff_code."-2' or ";
			$sql .= "next7_cc1_data = '".$staff_code."-2' or next7_cc2_data = '".$staff_code."-2' or next7_cc3_data = '".$staff_code."-2' or next7_cc4_data = '".$staff_code."-2' or next7_cc5_data = '".$staff_code."-2' or ";
			$sql .= "next8_cc1_data = '".$staff_code."-2' or next8_cc2_data = '".$staff_code."-2' or next8_cc3_data = '".$staff_code."-2' or next8_cc4_data = '".$staff_code."-2' or next8_cc5_data = '".$staff_code."-2' or ";
			$sql .= "next9_cc1_data = '".$staff_code."-2' or next9_cc2_data = '".$staff_code."-2' or next9_cc3_data = '".$staff_code."-2' or next9_cc4_data = '".$staff_code."-2' or next9_cc5_data = '".$staff_code."-2' or ";
			$sql .= "next10_cc1_data = '".$staff_code."-2' or next10_cc2_data = '".$staff_code."-2' or next10_cc3_data = '".$staff_code."-2' or next10_cc4_data = '".$staff_code."-2' or next10_cc5_data = '".$staff_code."-2') ";
			$sql .= "and  workflow_id = ".$workflow_id;
		}

	        $rows = getList($sql);
		$f_rows = $rows[0];
	               
	        if($f_rows){
			$input_staff = $f_rows["user_id"];
			$input_flow_doc_id = $f_rows["flow_doc_code"];
			$workflow_no = $f_rows["workflow_no"];
			if ($mode == 1){	//承認された場合
				for ($i=1; $i <= 10; $i++){
					if (strpos($f_rows["next".$i."_data"],$staff_code."-2") !== false){


//複数承認書を特定してupdate
						list($staff_no, $up_str) = workflow_agree_update($f_rows["next".$i."_data"], $staff_code, 3);

						$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET next".$i."_data = '".$up_str."', next".$i."_data_date".$staff_no." = CURRENT_TIMESTAMP, next".$i."_data_comment".$staff_no." = '".$d_comment."' WHERE workflow_id = ".$workflow_id;
						sqlExec($sql_up);

//重要　全員の承認が終わって、かつ、必要人数に達したら、CCへ
						$stat_result = workflow_agree_all($up_str);
						if ($stat_result == 1)
						{
							$mail_address = "";
							for ($k=1; $k <= 5; $k++){
								$sql_next = "SELECT * FROM ".$_SESSION["SCHEMA"].".t_workflow WHERE next".$i."_cc".$k."_data LIKE '%-1' and workflow_id = '".$workflow_id."'";
							        $result_next = getList($sql_next);
								$f_rows_next = $result_next[0];
							               
							        if($f_rows_next){
									$cc_id = $f_rows_next["user_id"];
									$flow_doc_id = $f_rows_next["flow_doc_code"];
									$workflow_no = $f_rows_next["workflow_no"];
				//承認後の参照者がいるので見つけて2に変えてメールを送る
									if ($f_rows_next["next".$i."_cc".$k."_data"] != ""){
										list($terget_id, $terget_flag) = explode("-", $f_rows_next["next".$i."_cc".$k."_data"]);
										$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET next".$i."_cc".$k."_data = '".$terget_id."-2' WHERE workflow_id = ".$workflow_id;
										sqlExec($sql_up);

										//参照依頼メールを送る
										$mail_address = f_staff_mail_get(str_replace("*", "", $terget_id));
										workflow_send_mail(2, $mail_address, $cc_id, $workflow_no, $flow_doc_id);
									}
								}
							}

							$sql_next = "SELECT * FROM ".$_SESSION["SCHEMA"].".t_workflow WHERE next".($i+1)."_data LIKE '%-1%' and workflow_id = '".$workflow_id."'";
						        $result_next = getList($sql_next);
							$f_rows_next = $result_next[0];
							if($f_rows_next){
				//次の承認者がいるので見つけて2に変えてメールを送る
								$cc_id = $f_rows_next["user_id"];
								$flow_doc_id = $f_rows_next["flow_doc_code"];
								$workflow_no = $f_rows_next["workflow_no"];
								if ($f_rows_next["next".($i+1)."_data"] != ""){


									list($staff_cnt, $name, $apply_cnt) = workflow_root_dsp_split($f_rows_next["next".($i+1)."_data"]);

	//								list($terget_id, $terget_flag) = explode("-", $f_rows_next["next".($i+1)."_data"]);

	//次の複数承認書を特定してupdate
									$up_str = workflow_next_agree_update($f_rows_next["next".($i+1)."_data"], 2);

	//								$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET next".($i+1)."_data = '".$terget_id."-2' WHERE workflow_id = ".$workflow_id;
									$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET next".($i+1)."_data = '".$up_str."' WHERE workflow_id = ".$workflow_id;



									sqlExec($sql_up);
												//$terget_idにメールを送る
									$mail_address = "";
									for ($kk = 0; $kk < $staff_cnt; $kk++){
										$mail_tmp = f_staff_mail_get(str_replace("*", "", $name[$kk]));
										if ($mail_address != "" && $mail_tmp != ""){
											$mail_address .= ",";
										}
										if ($mail_tmp != ""){
											$mail_address .= $mail_tmp;	//f_staff_mail_get($terget_id);
										}
									}
									//承認依頼メールを送る
									workflow_send_mail(0, $mail_address, $cc_id, $workflow_no, $flow_doc_id);
								}
							}else{
				//次の承認者がいなければstatを3可決に変えて申請者にメールを送る
								$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET stat = '3' WHERE workflow_id = ".$workflow_id;
								sqlExec($sql_up);
								//可決メールを送る
								$mail_address = f_staff_mail_get($input_staff);
								workflow_send_mail(1, $mail_address, $input_staff, $workflow_no, $input_flow_doc_id);
							}
							break;
						}else if ($stat_result == 2){	//必要人数に達しなかったので否決にする
							$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET stat = 2 WHERE workflow_id = ".$workflow_id;
							sqlExec($sql_up);

							//否決メールを送る
							$mail_address = f_staff_mail_get($input_staff);
							workflow_send_mail(3, $mail_address, $input_staff, $workflow_no, $input_flow_doc_id);

							break;
						}else if ($stat_result == 3){	//必要人数に達しなかったので差し戻しにする
							$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET stat = 5 WHERE workflow_id = ".$workflow_id;
							sqlExec($sql_up);

							//否決メールを送る
							$mail_address = f_staff_mail_get($input_staff);
							workflow_send_mail(5, $mail_address, $input_staff, $workflow_no, $input_flow_doc_id);

							break;
						}
						break;
					}
				}
			}else if ($mode == 2){	//参照された場合
				for ($i=1; $i <= 5; $i++){
					if (($staff_code_kian."-2") == $f_rows["cc".$i]){
						$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET cc".$i." = '".$staff_code_kian."-3', cc".$i."_date = CURRENT_TIMESTAMP WHERE workflow_id = ".$workflow_id;
						sqlExec($sql_up);
						goto skip_cc;
					}
				}
				for ($i=1; $i <= 10; $i++){
					for ($k=1; $k <= 5; $k++){
						if (($staff_code."-2") == $f_rows["next".$i."_cc".$k."_data"]){
							$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET next".$i."_cc".$k."_data = '".$staff_code."-3', next".$i."_cc".$k."_data_date = CURRENT_TIMESTAMP WHERE workflow_id = ".$workflow_id;
							sqlExec($sql_up);
							break;
						}
					}
				}
skip_cc:
			}else if ($mode == 3){	//否決された場合
				for ($i=1; $i <= 10; $i++){
					if(strpos($f_rows["next".$i."_data"],($staff_code."-2")) !== false){

						list($staff_no, $up_str) = workflow_agree_update($f_rows["next".$i."_data"], $staff_code, 4);
						$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET next".$i."_data = '".$up_str."', next".$i."_data_date".$staff_no." = CURRENT_TIMESTAMP, next".$i."_data_comment".$staff_no." = '".$d_comment."' WHERE workflow_id = ".$workflow_id;
						sqlExec($sql_up);

//重要　全員の承認が終わって、かつ、必要人数に達したら、CCへ
						$stat_result = workflow_agree_all($up_str);
						if ($stat_result == 1)
						{
							$mail_address = "";
							for ($k=1; $k <= 5; $k++){
								$sql_next = "SELECT * FROM ".$_SESSION["SCHEMA"].".t_workflow WHERE next".$i."_cc".$k."_data LIKE '%-1' and workflow_id = '".$workflow_id."'";
							        $result_next = getList($sql_next);
								$f_rows_next = $result_next[0];
							               
							        if($f_rows_next){
									$cc_id = $f_rows_next["user_id"];
									$flow_doc_id = $f_rows_next["flow_doc_code"];
									$workflow_no = $f_rows_next["workflow_no"];
				//承認後の参照者がいるので見つけて2に変えてメールを送る
									if ($f_rows_next["next".$i."_cc".$k."_data"] != ""){
										list($terget_id, $terget_flag) = explode("-", $f_rows_next["next".$i."_cc".$k."_data"]);
										$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET next".$i."_cc".$k."_data = '".$terget_id."-2' WHERE workflow_id = ".$workflow_id;
										sqlExec($sql_up);

										//参照依頼メールを送る
										$mail_address = f_staff_mail_get(str_replace("*", "", $terget_id));
										workflow_send_mail(2, $mail_address, $cc_id, $workflow_no, $flow_doc_id);

									}
								}
							}

							$sql_next = "SELECT * FROM ".$_SESSION["SCHEMA"].".t_workflow WHERE next".($i+1)."_data LIKE '%-1%' and workflow_id = '".$workflow_id."'";
						        $result_next = getList($sql_next);
							$f_rows_next = $result_next[0];
							if($f_rows_next){
				//次の承認者がいるので見つけて2に変えてメールを送る
								$cc_id = $f_rows_next["user_id"];
								$flow_doc_id = $f_rows_next["flow_doc_code"];
								$workflow_no = $f_rows_next["workflow_no"];
								if ($f_rows_next["next".($i+1)."_data"] != ""){


									list($staff_cnt, $name, $apply_cnt) = workflow_root_dsp_split($f_rows_next["next".($i+1)."_data"]);

	//								list($terget_id, $terget_flag) = explode("-", $f_rows_next["next".($i+1)."_data"]);

	//次の複数承認書を特定してupdate
									$up_str = workflow_next_agree_update($f_rows_next["next".($i+1)."_data"], 2);

	//								$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET next".($i+1)."_data = '".$terget_id."-2' WHERE workflow_id = ".$workflow_id;
									$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET next".($i+1)."_data = '".$up_str."' WHERE workflow_id = ".$workflow_id;



									sqlExec($sql_up);
												//$terget_idにメールを送る
									$mail_address = "";
									for ($kk = 0; $kk < $staff_cnt; $kk++){
										$mail_tmp = f_staff_mail_get(str_replace("*", "", $name[$kk]));
										if ($mail_address != "" && $mail_tmp != ""){
											$mail_address .= ",";
										}
										if ($mail_tmp != ""){
											$mail_address .= $mail_tmp;	//f_staff_mail_get($terget_id);
										}
									}
									//承認依頼メールを送る
									workflow_send_mail(0, $mail_address, $cc_id, $workflow_no, $flow_doc_id);
								}
							}else{
				//次の承認者がいなければstatを3可決に変えて申請者にメールを送る
								$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET stat = '3' WHERE workflow_id = ".$workflow_id;
								sqlExec($sql_up);
								//可決メールを送る
								$mail_address = f_staff_mail_get($input_staff);
								workflow_send_mail(1, $mail_address, $input_staff, $workflow_no, $input_flow_doc_id);
							}
							break;
						}else if ($stat_result == 2){	//必要人数に達しなかったので否決にする
							$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET stat = 2 WHERE workflow_id = ".$workflow_id;
							sqlExec($sql_up);

							//否決メールを送る
							$mail_address = f_staff_mail_get($input_staff);
							workflow_send_mail(3, $mail_address, $input_staff, $workflow_no, $input_flow_doc_id);

							break;
						}else if ($stat_result == 3){	//必要人数に達しなかったので差し戻しにする
							$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET stat = 5 WHERE workflow_id = ".$workflow_id;
							sqlExec($sql_up);

							//否決メールを送る
							$mail_address = f_staff_mail_get($input_staff);
							workflow_send_mail(5, $mail_address, $input_staff, $workflow_no, $input_flow_doc_id);

							break;
						}
						break;
					}
				}
			}else{		//差し戻しされた場合
				for ($i=1; $i <= 10; $i++){
					if(strpos($f_rows["next".$i."_data"],($staff_code."-2")) !== false){

						list($staff_no, $up_str) = workflow_agree_update($f_rows["next".$i."_data"], $staff_code, 5);
						$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET next".$i."_data = '".$up_str."', next".$i."_data_date".$staff_no." = CURRENT_TIMESTAMP, next".$i."_data_comment".$staff_no." = '".$d_comment."' WHERE workflow_id = ".$workflow_id;
						sqlExec($sql_up);

//重要　全員の承認が終わって、かつ、必要人数に達したら、CCへ
						$stat_result = workflow_agree_all($up_str);
						if ($stat_result == 1)
						{
							$mail_address = "";
							for ($k=1; $k <= 5; $k++){
								$sql_next = "SELECT * FROM ".$_SESSION["SCHEMA"].".t_workflow WHERE next".$i."_cc".$k."_data LIKE '%-1' and workflow_id = '".$workflow_id."'";
							        $result_next = getList($sql_next);
								$f_rows_next = $result_next[0];
							               
							        if($f_rows_next){
									$cc_id = $f_rows_next["user_id"];
									$flow_doc_id = $f_rows_next["flow_doc_code"];
									$workflow_no = $f_rows_next["workflow_no"];
				//承認後の参照者がいるので見つけて2に変えてメールを送る
									if ($f_rows_next["next".$i."_cc".$k."_data"] != ""){
										list($terget_id, $terget_flag) = explode("-", $f_rows_next["next".$i."_cc".$k."_data"]);
										$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET next".$i."_cc".$k."_data = '".$terget_id."-2' WHERE workflow_id = ".$workflow_id;
										sqlExec($sql_up);

										//参照依頼メールを送る
										$mail_address = f_staff_mail_get(str_replace("*", "", $terget_id));
										workflow_send_mail(2, $mail_address, $cc_id, $workflow_no, $flow_doc_id);

									}
								}
							}

							$sql_next = "SELECT * FROM ".$_SESSION["SCHEMA"].".t_workflow WHERE next".($i+1)."_data LIKE '%-1%' and workflow_id = '".$workflow_id."'";
						        $result_next = getList($sql_next);
							$f_rows_next = $result_next[0];
							if($f_rows_next){
				//次の承認者がいるので見つけて2に変えてメールを送る
								$cc_id = $f_rows_next["user_id"];
								$flow_doc_id = $f_rows_next["flow_doc_code"];
								$workflow_no = $f_rows_next["workflow_no"];
								if ($f_rows_next["next".($i+1)."_data"] != ""){


									list($staff_cnt, $name, $apply_cnt) = workflow_root_dsp_split($f_rows_next["next".($i+1)."_data"]);

	//								list($terget_id, $terget_flag) = explode("-", $f_rows_next["next".($i+1)."_data"]);

	//次の複数承認書を特定してupdate
									$up_str = workflow_next_agree_update($f_rows_next["next".($i+1)."_data"], 2);

	//								$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET next".($i+1)."_data = '".$terget_id."-2' WHERE workflow_id = ".$workflow_id;
									$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET next".($i+1)."_data = '".$up_str."' WHERE workflow_id = ".$workflow_id;



									sqlExec($sql_up);
												//$terget_idにメールを送る
									$mail_address = "";
									for ($kk = 0; $kk < $staff_cnt; $kk++){
										$mail_tmp = f_staff_mail_get(str_replace("*", "", $name[$kk]));
										if ($mail_address != "" && $mail_tmp != ""){
											$mail_address .= ",";
										}
										if ($mail_tmp != ""){
											$mail_address .= $mail_tmp;	//f_staff_mail_get($terget_id);
										}
									}
									//承認依頼メールを送る
									workflow_send_mail(0, $mail_address, $cc_id, $workflow_no, $flow_doc_id);
								}
							}else{
				//次の承認者がいなければstatを3可決に変えて申請者にメールを送る
								$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET stat = '3' WHERE workflow_id = ".$workflow_id;
								sqlExec($sql_up);
								//可決メールを送る
								$mail_address = f_staff_mail_get($input_staff);
								workflow_send_mail(1, $mail_address, $input_staff, $workflow_no, $input_flow_doc_id);
							}
							break;
						}else if ($stat_result == 2){	//必要人数に達しなかったので否決にする
							$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET stat = 2 WHERE workflow_id = ".$workflow_id;
							sqlExec($sql_up);

							//否決メールを送る
							$mail_address = f_staff_mail_get($input_staff);
							workflow_send_mail(3, $mail_address, $input_staff, $workflow_no, $input_flow_doc_id);

							break;
						}else if ($stat_result == 3){	//必要人数に達しなかったので差し戻しにする
							$sql_up = "UPDATE ".$_SESSION["SCHEMA"].".t_workflow SET stat = 5 WHERE workflow_id = ".$workflow_id;
							sqlExec($sql_up);

							//否決メールを送る
							$mail_address = f_staff_mail_get($input_staff);
							workflow_send_mail(5, $mail_address, $input_staff, $workflow_no, $input_flow_doc_id);

							break;
						}
						break;
					}
				}			}
		}

	return($ret_f);
}

//申請ルートを取り出す
function f_workflow_route($kyoten, $busho_code, $workflow_doc, $rank)
{
//	$target_date = target_tekiyou("m_workflow", date("Y-m-d"));
	$where = "kyoten = '".$kyoten."' and busho_code = '".code_org_id_get2($busho_code)."' and flow_doc_code = '".$workflow_doc."' and rank = ".$rank;
	$target_date = target_tekiyou_where("m_workflow", date("Y-m-d"), $where);
	if ($target_date == ""){
		$f_rows = 0;
	}else{
		$sql = "SELECT * FROM ".$_SESSION["SCHEMA"].".m_workflow WHERE target_date = '".$target_date."' and ".$where;
	        $result = getList($sql);
		$f_rows = $result[0];
	}
	if($f_rows){

		for ($i=1; $i <= 10; $i++){
			$flow_data[$i][0] = $f_rows["next".$i."_data"];
			$flow_data[$i][1] = $f_rows["next".$i."_cc1_data"];
			$flow_data[$i][2] = $f_rows["next".$i."_cc2_data"];
			$flow_data[$i][3] = $f_rows["next".$i."_cc3_data"];
			$flow_data[$i][4] = $f_rows["next".$i."_cc4_data"];
			$flow_data[$i][5] = $f_rows["next".$i."_cc5_data"];
		}
	}

return array(
$flow_data[1][0],$flow_data[1][1],$flow_data[1][2],$flow_data[1][3],$flow_data[1][4],$flow_data[1][5],
$flow_data[2][0],$flow_data[2][1],$flow_data[2][2],$flow_data[2][3],$flow_data[2][4],$flow_data[2][5],
$flow_data[3][0],$flow_data[3][1],$flow_data[3][2],$flow_data[3][3],$flow_data[3][4],$flow_data[3][5],
$flow_data[4][0],$flow_data[4][1],$flow_data[4][2],$flow_data[4][3],$flow_data[4][4],$flow_data[4][5],
$flow_data[5][0],$flow_data[5][1],$flow_data[5][2],$flow_data[5][3],$flow_data[5][4],$flow_data[5][5],
$flow_data[6][0],$flow_data[6][1],$flow_data[6][2],$flow_data[6][3],$flow_data[6][4],$flow_data[6][5],
$flow_data[7][0],$flow_data[7][1],$flow_data[7][2],$flow_data[7][3],$flow_data[7][4],$flow_data[7][5],
$flow_data[8][0],$flow_data[8][1],$flow_data[8][2],$flow_data[8][3],$flow_data[8][4],$flow_data[8][5],
$flow_data[9][0],$flow_data[9][1],$flow_data[9][2],$flow_data[9][3],$flow_data[9][4],$flow_data[9][5],
$flow_data[10][0],$flow_data[10][1],$flow_data[10][2],$flow_data[10][3],$flow_data[10][4],$flow_data[10][5]);



}

//起案時CCを取り出す
function f_workflow_route_cc($kyoten, $busho_code, $workflow_doc, $rank)
{
	$cc1 = "";
	$cc2 = "";
	$cc3 = "";
	$cc4 = "";
	$cc5 = "";

//	$target_date = target_tekiyou("m_workflow", date("Y-m-d"));
	$where = "kyoten = '".$kyoten."' and busho_code = '".code_org_id_get2($busho_code)."' and flow_doc_code = '".$workflow_doc."' and rank = ".$rank;
	$target_date = target_tekiyou_where("m_workflow", date("Y-m-d"), $where);
	if ($target_date == ""){
		$f_rows = 0;
	}else{
		$sql = "SELECT * FROM ".$_SESSION["SCHEMA"].".m_workflow WHERE target_date = '".$target_date."' and ".$where;
	        $result = getList($sql);
		$f_rows = $result[0];
	}
	if($f_rows){
		$cc1 = $f_rows["cc1"];
		$cc2 = $f_rows["cc2"];
		$cc3 = $f_rows["cc3"];
		$cc4 = $f_rows["cc4"];
		$cc5 = $f_rows["cc5"];
	}

return array($cc1, $cc2, $cc3, $cc4, $cc5);

}

?>