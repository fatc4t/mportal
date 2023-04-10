<?php

function f_system()
{
	$t_max_m = 0;
	$t_max_minute = 0;
	$p_keika_max = 0;
	$s_log = 0;

	$sql = "SELECT * FROM t_control";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$t_max_m = $f_rows["t_max_m"];			//目的地と打刻位置のアラート距離（ｍ） 500
		$t_max_minute = $f_rows["t_max_minute"];	//予定と打刻時間のアラート時間（分） 10
		$p_keika_max = $f_rows["p_keika_max"];		//プロジェクトの経過日数アラート（日） 90
		$s_log = $f_rows["s_log"];		//ログ最大表示件数 300
		$settlement = $f_rows["settlement"];		//決算期 4
	}

	mysql_free_result($result);

	return array(
		"t_max_m" => $t_max_m,
		"t_max_minute" => $t_max_minute,
		"p_keika_max" => $p_keika_max,
		"s_log" => $s_log,
		"settlement" => $settlement
		);

}

?>
