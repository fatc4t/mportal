<?php

function shouninsha_update($schema, $target_staff, $user_list, $where)
{
	$sql = "SELECT * FROM ".$schema.".m_workflow WHERE (next1_data LIKE '%*".$target_staff."-%' or next2_data LIKE '%*".$target_staff."-%' or next3_data LIKE '%*".$target_staff."-%' or next4_data LIKE '%*".$target_staff."-%' or next5_data LIKE '%*".$target_staff."-%' or next6_data LIKE '%*".$target_staff."-%' or next7_data LIKE '%*".$target_staff."-%' or next8_data LIKE '%*".$target_staff."-%' or next9_data LIKE '%*".$target_staff."-%' or next10_data LIKE '%*".$target_staff."-%') and ".$where;
        $rows = getList($sql);
	$cnt = 0;
        if($rows){
		while($row = $rows[$cnt]) {
			for ($i = 1; $i <= 10; $i++){
				if(strstr($row["next".$i."_data"], "*".$target_staff."-")){
					$data = $row["next".$i."_data"];
					$data_c = workflow_root_change($data, $target_staff, $user_list);
					$sql_up = "update ".$schema.".m_workflow set next".$i."_data = '".$data_c."' where m_workflow_id = '".$row["m_workflow_id"]."'";
					sqlExec($sql_up);
				}
			}
			$cnt += 1;
		}
	}

}

//指定ルートの指定した人を削除する
function workflow_root_target_delete_shounin($schema, $target_date, $target_flow, $target_org, $target_staff, $kengen_no)
{

	$sql = "SELECT * FROM ".$schema.".m_workflow WHERE busho_code = ".$target_org." and flow_doc_code = ".$target_flow." and target_date = '".$target_date."'";
       	$rows = getList($sql);
        if($rows){
		$row = $rows[0];
		$str = workflow_root_delete($row["next".$kengen_no."_data"], $target_staff);
		if ($str == ""){	//指定順位の承認者が０になったので、つめる
			$ff = 0;
			if ($row["next".($kengen_no+1)."_data"] == "*0-0" && $kengen_no == 1){
				$sql_up = "delete from ".$schema.".m_workflow where m_workflow_id = '".$row["m_workflow_id"]."'";
				sqlExec($sql_up);
			}else{
				$sql_up = "update ".$schema.".m_workflow set ";
				for ($i = $kengen_no; $i < 10; $i++){
					if ($i == 1){
						$sql_up .= "next".$i."_data = '".workflow_agree_update_all($row["next".($i+1)."_data"], 2)."'";
						$ff = 1;
					}else{
						if ($ff == 0){
							$sql_up .= "next".$i."_data = '".$row["next".($i+1)."_data"]."'";
							$ff = 1;
						}else{
							$sql_up .= ", next".$i."_data = '".$row["next".($i+1)."_data"]."'";
						}
					}
					for ($j = 1; $j < 5; $j++){
						$sql_up .= ", next".$i."_cc".$j."_data = '".$row["next".($i+1)."_cc".$j."_data"]."'";
					}
					$sql_up .= ", next".$i."_cc".$j."_data = '*0-0'";
				}
				$sql_up .= ", next".$i."_data = '*0-0' where m_workflow_id = '".$row["m_workflow_id"]."'";
				sqlExec($sql_up);
			}
		}else{
			$sql_up = "update ".$schema.".m_workflow set next".$kengen_no."_data = '".$str."' where m_workflow_id = '".$row["m_workflow_id"]."'";
			sqlExec($sql_up);
		}

	}
	return ;
}

//指定ルートの指定した人を削除する
function workflow_root_delete($str, $staff_code)
{

	$apply_cnt = 0;
	$staff_cnt = 0;
	$up_str = "";

	$key1 = explode("=", $str);
	$apply_cnt = $key1[1];	//承認人数


	$key2 = explode("/", $key1[0]);

	$staff_cnt = count($key2);

	for ($i = 0; $i < $staff_cnt; $i++){
		$key3 = explode("-", $key2[$i]);
		if ("*".$staff_code == $key3[0]){
		}else{
			if ($up_str != ""){
				$up_str .= "/";
			}
			$up_str .= $key3[0]."-".$key3[1];
		}
	}

	if ($up_str != ""){
		$up_str .= "=".$apply_cnt;
	}

	return $up_str;
}

function shouninsha_insert_update($schema, $target_staff, $user_list, $chg_date, $where)
{
	$sql = "SELECT * FROM ".$schema.".m_workflow WHERE (next1_data LIKE '%*".$target_staff."-%' or next2_data LIKE '%*".$target_staff."-%' or next3_data LIKE '%*".$target_staff."-%' or next4_data LIKE '%*".$target_staff."-%' or next5_data LIKE '%*".$target_staff."-%' or next6_data LIKE '%*".$target_staff."-%' or next7_data LIKE '%*".$target_staff."-%' or next8_data LIKE '%*".$target_staff."-%' or next9_data LIKE '%*".$target_staff."-%' or next10_data LIKE '%*".$target_staff."-%') and ".$where;
        $rows = getList($sql);
	$cnt = 0;
        if($rows){
		while($row = $rows[$cnt]) {
			for ($i = 1; $i <= 10; $i++){
				if(strstr($row["next".$i."_data"], "*".$target_staff."-")){
					$data = $row["next".$i."_data"];
					$data_c = workflow_root_change($data, $target_staff, $user_list);

					$where = "where m_workflow_id = '".$row["m_workflow_id"]."'";
					$last_id = m_workflow_copy_insert($schema, $where);

					$sql_up = "update ".$schema.".m_workflow set next".$i."_data = '".$data_c."', target_date = '".$chg_date."' where m_workflow_id = '".$last_id."'";
					sqlExec($sql_up);
				}
			}
			$cnt += 1;
		}
	}

}

function cc_update($schema, $target_staff, $user_list, $where)
{
	$sql = "SELECT * FROM ".$schema.".m_workflow WHERE ";
	$sql .= "(next1_cc1_data LIKE '*".$target_staff."-%' or next1_cc2_data LIKE '*".$target_staff."-%' or next1_cc3_data LIKE '*".$target_staff."-%' or next1_cc4_data LIKE '*".$target_staff."-%' or next1_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next2_cc1_data LIKE '*".$target_staff."-%' or next2_cc2_data LIKE '*".$target_staff."-%' or next2_cc3_data LIKE '*".$target_staff."-%' or next2_cc4_data LIKE '*".$target_staff."-%' or next2_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next3_cc1_data LIKE '*".$target_staff."-%' or next3_cc2_data LIKE '*".$target_staff."-%' or next3_cc3_data LIKE '*".$target_staff."-%' or next3_cc4_data LIKE '*".$target_staff."-%' or next3_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next4_cc1_data LIKE '*".$target_staff."-%' or next4_cc2_data LIKE '*".$target_staff."-%' or next4_cc3_data LIKE '*".$target_staff."-%' or next4_cc4_data LIKE '*".$target_staff."-%' or next4_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next5_cc1_data LIKE '*".$target_staff."-%' or next5_cc2_data LIKE '*".$target_staff."-%' or next5_cc3_data LIKE '*".$target_staff."-%' or next5_cc4_data LIKE '*".$target_staff."-%' or next5_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next6_cc1_data LIKE '*".$target_staff."-%' or next6_cc2_data LIKE '*".$target_staff."-%' or next6_cc3_data LIKE '*".$target_staff."-%' or next6_cc4_data LIKE '*".$target_staff."-%' or next6_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next7_cc1_data LIKE '*".$target_staff."-%' or next7_cc2_data LIKE '*".$target_staff."-%' or next7_cc3_data LIKE '*".$target_staff."-%' or next7_cc4_data LIKE '*".$target_staff."-%' or next7_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next8_cc1_data LIKE '*".$target_staff."-%' or next8_cc2_data LIKE '*".$target_staff."-%' or next8_cc3_data LIKE '*".$target_staff."-%' or next8_cc4_data LIKE '*".$target_staff."-%' or next8_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next9_cc1_data LIKE '*".$target_staff."-%' or next9_cc2_data LIKE '*".$target_staff."-%' or next9_cc3_data LIKE '*".$target_staff."-%' or next9_cc4_data LIKE '*".$target_staff."-%' or next9_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next10_cc1_data LIKE '*".$target_staff."-%' or next10_cc2_data LIKE '*".$target_staff."-%' or next10_cc3_data LIKE '*".$target_staff."-%' or next10_cc4_data LIKE '*".$target_staff."-%' or next10_cc5_data LIKE '*".$target_staff."-%') and ".$where;
        $rows = getList($sql);
	$cnt = 0;
        if($rows){
		while($row = $rows[$cnt]) {
			for ($i = 1; $i <= 10; $i++){
				for ($j = 1; $j <= 5; $j++){
					if(strstr($row["next".$i."_cc".$j."_data"], "*".$target_staff."-")){
						$sql_up = "update ".$schema.".m_workflow set next".$i."_cc".$j."_data = '*".$user_list."-1' where m_workflow_id = '".$row["m_workflow_id"]."'";
						sqlExec($sql_up);
					}
				}
			}
			$cnt += 1;
		}
	}
}

function cc_insert_update($schema, $target_staff, $user_list, $chg_date, $where)
{
	$sql = "SELECT * FROM ".$schema.".m_workflow WHERE ";
	$sql .= "(next1_cc1_data LIKE '*".$target_staff."-%' or next1_cc2_data LIKE '*".$target_staff."-%' or next1_cc3_data LIKE '*".$target_staff."-%' or next1_cc4_data LIKE '*".$target_staff."-%' or next1_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next2_cc1_data LIKE '*".$target_staff."-%' or next2_cc2_data LIKE '*".$target_staff."-%' or next2_cc3_data LIKE '*".$target_staff."-%' or next2_cc4_data LIKE '*".$target_staff."-%' or next2_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next3_cc1_data LIKE '*".$target_staff."-%' or next3_cc2_data LIKE '*".$target_staff."-%' or next3_cc3_data LIKE '*".$target_staff."-%' or next3_cc4_data LIKE '*".$target_staff."-%' or next3_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next4_cc1_data LIKE '*".$target_staff."-%' or next4_cc2_data LIKE '*".$target_staff."-%' or next4_cc3_data LIKE '*".$target_staff."-%' or next4_cc4_data LIKE '*".$target_staff."-%' or next4_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next5_cc1_data LIKE '*".$target_staff."-%' or next5_cc2_data LIKE '*".$target_staff."-%' or next5_cc3_data LIKE '*".$target_staff."-%' or next5_cc4_data LIKE '*".$target_staff."-%' or next5_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next6_cc1_data LIKE '*".$target_staff."-%' or next6_cc2_data LIKE '*".$target_staff."-%' or next6_cc3_data LIKE '*".$target_staff."-%' or next6_cc4_data LIKE '*".$target_staff."-%' or next6_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next7_cc1_data LIKE '*".$target_staff."-%' or next7_cc2_data LIKE '*".$target_staff."-%' or next7_cc3_data LIKE '*".$target_staff."-%' or next7_cc4_data LIKE '*".$target_staff."-%' or next7_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next8_cc1_data LIKE '*".$target_staff."-%' or next8_cc2_data LIKE '*".$target_staff."-%' or next8_cc3_data LIKE '*".$target_staff."-%' or next8_cc4_data LIKE '*".$target_staff."-%' or next8_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next9_cc1_data LIKE '*".$target_staff."-%' or next9_cc2_data LIKE '*".$target_staff."-%' or next9_cc3_data LIKE '*".$target_staff."-%' or next9_cc4_data LIKE '*".$target_staff."-%' or next9_cc5_data LIKE '*".$target_staff."-%' or ";
	$sql .= "next10_cc1_data LIKE '*".$target_staff."-%' or next10_cc2_data LIKE '*".$target_staff."-%' or next10_cc3_data LIKE '*".$target_staff."-%' or next10_cc4_data LIKE '*".$target_staff."-%' or next10_cc5_data LIKE '*".$target_staff."-%') and ".$where;
        $rows = getList($sql);
	$cnt = 0;
        if($rows){
		while($row = $rows[$cnt]) {
			for ($i = 1; $i <= 10; $i++){
				for ($j = 1; $j <= 5; $j++){
					if(strstr($row["next".$i."_cc".$j."_data"], "*".$target_staff."-")){
						$where = "where m_workflow_id = '".$row["m_workflow_id"]."'";
						$last_id = m_workflow_copy_insert($schema, $where);

						$sql_up = "update ".$schema.".m_workflow set next".$i."_cc".$j."_data = '*".$user_list."-1', target_date = '".$chg_date."' where m_workflow_id = '".$last_id."'";
						sqlExec($sql_up);
					}
				}
			}
			$cnt += 1;
		}
	}
}

function kian_cc_update($schema, $target_staff, $user_list, $where)
{
	$sql = "SELECT * FROM ".$schema.".m_workflow WHERE (cc1 = '".$target_staff."' or cc2 = '".$target_staff."' or cc3 = '".$target_staff."' or cc4 = '".$target_staff."' or cc5 = '".$target_staff."') and ".$where;
        $rows = getList($sql);
	$cnt = 0;
        if($rows){
		while($row = $rows[$cnt]) {
			for ($i = 1; $i <= 10; $i++){
				if($row["cc".$i] == $target_staff){
					$sql_up = "update ".$schema.".m_workflow set cc".$i." = '".$user_list."' where m_workflow_id = '".$row["m_workflow_id"]."'";
					sqlExec($sql_up);
				}
			}
			$cnt += 1;
		}
	}
}

function kian_cc_insert_update($schema, $target_staff, $user_list, $chg_date, $where)
{
	$sql = "SELECT * FROM ".$schema.".m_workflow WHERE (cc1 = '".$target_staff."' or cc2 = '".$target_staff."' or cc3 = '".$target_staff."' or cc4 = '".$target_staff."' or cc5 = '".$target_staff."') and ".$where;
        $rows = getList($sql);
	$cnt = 0;
        if($rows){
		while($row = $rows[$cnt]) {
			for ($i = 1; $i <= 10; $i++){
				if($row["cc".$i] == $target_staff){
					$where = "where m_workflow_id = '".$row["m_workflow_id"]."'";
					$last_id = m_workflow_copy_insert($schema, $where);

					$sql_up = "update ".$schema.".m_workflow set cc".$i." = '".$user_list."', target_date = '".$chg_date."' where m_workflow_id = '".$last_id."'";
					sqlExec($sql_up);
				}
			}
			$cnt += 1;
		}
	}
}

function m_workflow_copy_insert($schema, $where)
{
$last_id = 0;
				$sql_insert = "insert into ".$schema.".m_workflow ";
				$sql_insert .= "(kyoten,busho_code,flow_doc_code,rank,";
				$sql_insert .= "next1_data,next1_cc1_data,next1_cc2_data,next1_cc3_data,next1_cc4_data,next1_cc5_data,";
				$sql_insert .= "next2_data,next2_cc1_data,next2_cc2_data,next2_cc3_data,next2_cc4_data,next2_cc5_data,";
				$sql_insert .= "next3_data,next3_cc1_data,next3_cc2_data,next3_cc3_data,next3_cc4_data,next3_cc5_data,";
				$sql_insert .= "next4_data,next4_cc1_data,next4_cc2_data,next4_cc3_data,next4_cc4_data,next4_cc5_data,";
				$sql_insert .= "next5_data,next5_cc1_data,next5_cc2_data,next5_cc3_data,next5_cc4_data,next5_cc5_data,";
				$sql_insert .= "next6_data,next6_cc1_data,next6_cc2_data,next6_cc3_data,next6_cc4_data,next6_cc5_data,";
				$sql_insert .= "next7_data,next7_cc1_data,next7_cc2_data,next7_cc3_data,next7_cc4_data,next7_cc5_data,";
				$sql_insert .= "next8_data,next8_cc1_data,next8_cc2_data,next8_cc3_data,next8_cc4_data,next8_cc5_data,";
				$sql_insert .= "next9_data,next9_cc1_data,next9_cc2_data,next9_cc3_data,next9_cc4_data,next9_cc5_data,";
				$sql_insert .= "next10_data,next10_cc1_data,next10_cc2_data,next10_cc3_data,next10_cc4_data,next10_cc5_data,";
				$sql_insert .= "create_date,target_date, cc1, cc2, cc3, cc4, cc5) ";
				$sql_insert .= "select ";
				$sql_insert .= "kyoten,busho_code,flow_doc_code,rank,";
				$sql_insert .= "next1_data,next1_cc1_data,next1_cc2_data,next1_cc3_data,next1_cc4_data,next1_cc5_data,";
				$sql_insert .= "next2_data,next2_cc1_data,next2_cc2_data,next2_cc3_data,next2_cc4_data,next2_cc5_data,";
				$sql_insert .= "next3_data,next3_cc1_data,next3_cc2_data,next3_cc3_data,next3_cc4_data,next3_cc5_data,";
				$sql_insert .= "next4_data,next4_cc1_data,next4_cc2_data,next4_cc3_data,next4_cc4_data,next4_cc5_data,";
				$sql_insert .= "next5_data,next5_cc1_data,next5_cc2_data,next5_cc3_data,next5_cc4_data,next5_cc5_data,";
				$sql_insert .= "next6_data,next6_cc1_data,next6_cc2_data,next6_cc3_data,next6_cc4_data,next6_cc5_data,";
				$sql_insert .= "next7_data,next7_cc1_data,next7_cc2_data,next7_cc3_data,next7_cc4_data,next7_cc5_data,";
				$sql_insert .= "next8_data,next8_cc1_data,next8_cc2_data,next8_cc3_data,next8_cc4_data,next8_cc5_data,";
				$sql_insert .= "next9_data,next9_cc1_data,next9_cc2_data,next9_cc3_data,next9_cc4_data,next9_cc5_data,";
				$sql_insert .= "next10_data,next10_cc1_data,next10_cc2_data,next10_cc3_data,next10_cc4_data,next10_cc5_data,";
				$sql_insert .= "create_date,target_date, cc1, cc2, cc3, cc4, cc5 ";
				$sql_insert .= "from ".$schema.".m_workflow ";
				$sql_insert .= $where;

				$last_id = sqlExecSeq($sql_insert, $schema.".m_workflow_m_workflow_id_seq");

	return $last_id;
}
?>

