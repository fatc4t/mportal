<?php

function iine_dame_cnt($cd, $serch_date, $name, $login_staff)
{

	$num_iine= 0;
	$iine_cnt= 0;
	$num_dame= 0;
	$dame_cnt= 0;

	if ($cd == 1){
		$sql_iine= "SELECT count(*) FROM t_iine WHERE terget_date = '".$serch_date."' and terget_name = '".char_chg("to_db", $name)."' and rating = '".char_chg("to_db", $login_staff)."' and iine = 1";
		$result_iine = executeQuery($sql_iine);
		list($num_iine) = mysql_fetch_row($result_iine);
		mysql_free_result($result_iine);

		$sql_dame= "SELECT count(*) FROM t_iine WHERE terget_date = '".$serch_date."' and terget_name = '".char_chg("to_db", $name)."' and rating = '".char_chg("to_db", $login_staff)."' and dame = 1";
		$result_dame = executeQuery($sql_dame);
		list($num_dame) = mysql_fetch_row($result_dame);
		mysql_free_result($result_dame);

	}

	if ($cd == 2){
		$sql_iine_cnt = "SELECT sum(iine), sum(dame) FROM t_iine WHERE terget_date > '".$serch_date."' and terget_name = '".char_chg("to_db", $name)."'";
	}else{
		$sql_iine_cnt = "SELECT sum(iine), sum(dame) FROM t_iine WHERE terget_date = '".$serch_date."' and terget_name = '".char_chg("to_db", $name)."'";
	}

	$result_iine_cnt = executeQuery($sql_iine_cnt);
	$rows_iine_cnt = mysql_num_rows($result_iine_cnt);
	if($rows_iine_cnt){
		$row_iine_cnt = mysql_fetch_array($result_iine_cnt);
		$iine_cnt = intval($row_iine_cnt["sum(iine)"]);
		$dame_cnt = intval($row_iine_cnt["sum(dame)"]);
	}
	mysql_free_result($result_iine_cnt);
	if ($cd == 2){
		$sql_iine_cnt = "SELECT sum(iine), sum(dame) FROM t_iine WHERE terget_date > '".$serch_date."' and terget_name = '".$name."' and rating = 'ˆä–ìŒöl'";
		$result_iine_cnt = executeQuery($sql_iine_cnt);
		$rows_iine_cnt = mysql_num_rows($result_iine_cnt);
		if($rows_iine_cnt){
			$row_iine_cnt = mysql_fetch_array($result_iine_cnt);
			$iine_cnt += (intval($row_iine_cnt["sum(iine)"]) * 9);
			$dame_cnt += (intval($row_iine_cnt["sum(dame)"]) * 9);
		}
		mysql_free_result($result_iine_cnt);
	}

	return array( $num_iine, $iine_cnt, $num_dame, $dame_cnt );


}


?>
