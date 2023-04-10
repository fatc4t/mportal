<?php
function all_buget($d_yyyy)
{


	$sql = "SELECT total_m, total_k, total_j FROM t_tmp WHERE cd = 'calc_budget'";
	$result = executeQuery($sql);
	$f_rows = mysql_fetch_array($result);


	return array($f_rows["total_m"], $f_rows["total_k"], $f_rows["total_j"]);

}

?>
