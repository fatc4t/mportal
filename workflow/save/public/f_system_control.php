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
		$t_max_m = $f_rows["t_max_m"];			//�ړI�n�Ƒō��ʒu�̃A���[�g�����i���j 500
		$t_max_minute = $f_rows["t_max_minute"];	//�\��Ƒō����Ԃ̃A���[�g���ԁi���j 10
		$p_keika_max = $f_rows["p_keika_max"];		//�v���W�F�N�g�̌o�ߓ����A���[�g�i���j 90
		$s_log = $f_rows["s_log"];		//���O�ő�\������ 300
		$settlement = $f_rows["settlement"];		//���Z�� 4
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
