<?php
function budget_calc($yyyy)
{
require_once("f_function.php");
require_once("f_system_control.php");

$d_yyyy = $yyyy;
$staff_max = 30;


$system_control = f_system();
$settlement = $system_control["settlement"];
$m_dsp = month_dsp($settlement);

//������
$rank_tx[0][0] = "-";
$rank_tx[0][1] = 0;
$rank_tx[1][0] = "S";
$rank_tx[1][1] = 0;
$rank_tx[2][0] = "A";
$rank_tx[2][1] = 0;
$rank_tx[3][0] = "B";
$rank_tx[3][1] = 0;
$rank_tx[4][0] = "C";
$rank_tx[4][1] = 0;
$rank_tx[5][0] = "D";
$rank_tx[5][1] = 0;


//�\�Z
for ($m = 1; $m <= 11; $m ++){
	for ($v = 0; $v <= 2; $v++){
		for ($i = 1; $i <= 7; $i ++){
			for ($j = 1; $j <= 12; $j ++){
				$budget[$m][$v][$i][1][$j] = 0;	//����
				$budget[$m][$v][$i][2][$j] = 0;	//�����j���O����
				$budget[$m][$v][$i][3][$j] = 0; //�����j���O�݌v
			}
			$budget[$m][$v][$i][1][13] = "";
			$budget[$m][$v][$i][2][13] = "";
			$budget[$m][$v][$i][3][13] = "";
		}
		for ($j = 1; $j <= 12; $j ++){
			$all_total[$m][$v][$j] = 0;	//������ڕW
			$all_total[$m][$v][$j] = 0;	//���������
			$all_total[$m][$v][$j] = 0;	//�����㌩��
		}
	}
}

for ($kk = 1; $kk <= 12; $kk ++){
	$siire_shoki[$kk] = 0;	//�����̎d����
	$siire_kakin[$kk] = 0;	//�ۋ��̎d����
	$all_total_m[$kk] = 0;	//������ڕW
	$all_total_j[$kk] = 0;	//���������
	$all_total_k[$kk] = 0;	//�����㌩��
	$nikutama[1][$kk] = 0;
	$nikutama[2][$kk] = 0;
	$budget_cost[1][$kk] = 0;	//�ɂ����� �ƒ�
	$budget_cost[2][$kk] = 0;	//�ɂ����� ���M��
	$budget_cost[3][$kk] = 0;	//�ɂ����� ���[�X
	$budget_cost[4][$kk] = 0;	//�ɂ����� �l����
	$budget_cost[5][$kk] = 0;	//WEB �l����
	$budget_cost[6][$kk] = 0;	//WEB �O���ێ��
}




//�ɂ����܂̔���Ǝd������擾
  // �N�G���𑗐M����
  $sql = "SELECT * FROM t_nikutama WHERE terget = '".$d_yyyy."' ORDER BY mm ASC";
  $result = executeQuery($sql);

  // �N�G���𑗐M����
  $result = executeQuery($sql);

  //���ʃZ�b�g�̍s�����擾����
  $rows = mysql_num_rows($result);

  //�\������f�[�^���쐬
  if($rows){
    while($row = mysql_fetch_array($result)) {
	$mm = $row["mm"];

	$nikutama[1][$mm] = $row["uriage"];
	$nikutama[2][$mm] = $row["siire"];
    }
  }


  //���ʕێ��p���������J������
  mysql_free_result($result);


//�R�X�g�\�Z���擾
$sql = "SELECT * FROM t_budget WHERE terget = '".$d_yyyy."' and cd > 30 ORDER BY budget_id ASC";
$result = executeQuery($sql);
$rows = mysql_num_rows($result);
if($rows){
	while($row = mysql_fetch_array($result)) {
		$i = $row["cd"]-30;
		$budget_cost[$i][1] = $row["m1"];
		$budget_cost[$i][2] = $row["m2"];
		$budget_cost[$i][3] = $row["m3"];
		$budget_cost[$i][4] = $row["m4"];
		$budget_cost[$i][5] = $row["m5"];
		$budget_cost[$i][6] = $row["m6"];
		$budget_cost[$i][7] = $row["m7"];
		$budget_cost[$i][8] = $row["m8"];
		$budget_cost[$i][9] = $row["m9"];
		$budget_cost[$i][10] = $row["m10"];
		$budget_cost[$i][11] = $row["m11"];
		$budget_cost[$i][12] = $row["m12"];
	}
}
mysql_free_result($result);

//����\�Z���擾
$sql = "SELECT * FROM t_budget WHERE terget = '".$d_yyyy."' and cd < 8 ORDER BY budget_id ASC";
$result = executeQuery($sql);
$rows = mysql_num_rows($result);

$staff_count = 0;
$name_sv = "";

//�\������f�[�^���쐬
if($rows){
	while($row = mysql_fetch_array($result)) {
		$i = $row["cd"];
		$sub = $row["sub"];

		if ($row["name"] != $name_sv || $name_sv == ""){
			$name_sv = $row["name"];
			$staff_count ++;
		}

		$budget[$staff_count][0][$i][$sub][1] = $row["m1"];
		$budget[$staff_count][0][$i][$sub][2] = $row["m2"];
		$budget[$staff_count][0][$i][$sub][3] = $row["m3"];
		$budget[$staff_count][0][$i][$sub][4] = $row["m4"];
		$budget[$staff_count][0][$i][$sub][5] = $row["m5"];
		$budget[$staff_count][0][$i][$sub][6] = $row["m6"];
		$budget[$staff_count][0][$i][$sub][7] = $row["m7"];
		$budget[$staff_count][0][$i][$sub][8] = $row["m8"];
		$budget[$staff_count][0][$i][$sub][9] = $row["m9"];
		$budget[$staff_count][0][$i][$sub][10] = $row["m10"];
		$budget[$staff_count][0][$i][$sub][11] = $row["m11"];
		$budget[$staff_count][0][$i][$sub][12] = $row["m12"];
		$budget[$staff_count][0][$i][$sub][13] = char_chg("to_dsp", $row["name"]);

		if (char_chg("to_dsp", $row["name"]) == "WEB" && $i == 5 && $sub == 1){
			$budget[$staff_count][0][$i][$sub][1] += ($budget_cost[5][1] + $budget_cost[6][1]);
			$budget[$staff_count][0][$i][$sub][2] += ($budget_cost[5][2] + $budget_cost[6][2]);
			$budget[$staff_count][0][$i][$sub][3] += ($budget_cost[5][3] + $budget_cost[6][3]);
			$budget[$staff_count][0][$i][$sub][4] += ($budget_cost[5][4] + $budget_cost[6][4]);
			$budget[$staff_count][0][$i][$sub][5] += ($budget_cost[5][5] + $budget_cost[6][5]);
			$budget[$staff_count][0][$i][$sub][6] += ($budget_cost[5][6] + $budget_cost[6][6]);
			$budget[$staff_count][0][$i][$sub][7] += ($budget_cost[5][7] + $budget_cost[6][7]);
			$budget[$staff_count][0][$i][$sub][8] += ($budget_cost[5][8] + $budget_cost[6][8]);
			$budget[$staff_count][0][$i][$sub][9] += ($budget_cost[5][9] + $budget_cost[6][9]);
			$budget[$staff_count][0][$i][$sub][10] += ($budget_cost[5][10] + $budget_cost[6][10]);
			$budget[$staff_count][0][$i][$sub][11] += ($budget_cost[5][11] + $budget_cost[6][11]);
			$budget[$staff_count][0][$i][$sub][12] += ($budget_cost[5][12] + $budget_cost[6][12]);
		}else if (char_chg("to_dsp", $row["name"]) == "�ɂ�����" && $i == 6 && $sub == 1){
			$budget[$staff_count][0][$i][$sub][1] += ($budget_cost[1][1] + $budget_cost[2][1] + $budget_cost[3][1]);
			$budget[$staff_count][0][$i][$sub][2] += ($budget_cost[1][2] + $budget_cost[2][2] + $budget_cost[3][2]);
			$budget[$staff_count][0][$i][$sub][3] += ($budget_cost[1][3] + $budget_cost[2][3] + $budget_cost[3][3]);
			$budget[$staff_count][0][$i][$sub][4] += ($budget_cost[1][4] + $budget_cost[2][4] + $budget_cost[3][4]);
			$budget[$staff_count][0][$i][$sub][5] += ($budget_cost[1][5] + $budget_cost[2][5] + $budget_cost[3][5]);
			$budget[$staff_count][0][$i][$sub][6] += ($budget_cost[1][6] + $budget_cost[2][6] + $budget_cost[3][6]);
			$budget[$staff_count][0][$i][$sub][7] += ($budget_cost[1][7] + $budget_cost[2][7] + $budget_cost[3][7]);
			$budget[$staff_count][0][$i][$sub][8] += ($budget_cost[1][8] + $budget_cost[2][8] + $budget_cost[3][8]);
			$budget[$staff_count][0][$i][$sub][9] += ($budget_cost[1][9] + $budget_cost[2][9] + $budget_cost[3][9]);
			$budget[$staff_count][0][$i][$sub][10] += ($budget_cost[1][10] + $budget_cost[2][10] + $budget_cost[3][10]);
			$budget[$staff_count][0][$i][$sub][11] += ($budget_cost[1][11] + $budget_cost[2][11] + $budget_cost[3][11]);
			$budget[$staff_count][0][$i][$sub][12] += ($budget_cost[1][12] + $budget_cost[2][12] + $budget_cost[3][12]);
		}
	}
}

//�����j���O�̖ڕW��������݌v�����߂�
for ($tt = 1; $tt <= $staff_count+1; $tt++){
	for ($ll=1; $ll <= 7; $ll++){
		$ruikei = 0;
		for ($j = 1; $j <= 12; $j ++){
			$ruikei += $budget[$tt][0][$ll][2][$j];
			$budget[$tt][0][$ll][3][$j] = $ruikei;
		}
	}
}

mysql_free_result($result);

$sql_mcode = "SELECT * FROM m_code WHERE cd = 7 or cd = 11 ORDER BY no";
$result_mcode = executeQuery($sql_mcode);
$rows_mcode = mysql_num_rows($result_mcode);

$factor_cnt = 0;
$flow_cnt = 0;
if($rows_mcode){
	while($rows_mcode = mysql_fetch_array($result_mcode)) {
		if ($rows_mcode["cd"] == 7){
			$rank_tx[$rows_mcode["no"]][1] = intval($rows_mcode["name"]);
			$factor_cnt += 1;
		}else{
			if (intval($rows_mcode["no"]) != 0){
				$sales_flow[$flow_cnt][1] = intval($rows_mcode["name"]);
				$flow_cnt += 1;
			}
		}
	}
}

if ($m_dsp["0"] == 1){
	$start_date = $d_yyyy."-01-01";
	$end_date = $d_yyyy."-12-31";
}else{
	$start_date = $d_yyyy."-".sprintf("%02d",$m_dsp["0"])."-01";
	$end_date = ($d_yyyy+1)."-".sprintf("%02d",$m_dsp["11"])."-31";
}

$terget = 0;
$d_cnt = 0;

$sql = "SELECT * FROM t_project WHERE s_prj_status = 1 or s_prj_status = 2";

$result = executeQuery($sql);
$rows = mysql_num_rows($result);


//�\������f�[�^���쐬
if($rows){
	while($row = mysql_fetch_array($result)) {

		if ($row["s_prj_status"] == 2){	//�󒍂��Ă���
			$shoki_m = get_month($d_yyyy, $row["s_prj_jyuchu_shoki_date"]);
		}else{
			$shoki_m = get_month($d_yyyy, $row["s_prj_mikomi_jyuchu_date"]);
		}

		if ($shoki_m != 0){
			if ($shoki_m < $m_dsp["0"]){
				$shoki_m = $shoki_m + ($m_dsp["0"]-1);
			}else{
				$shoki_m = $shoki_m - ($m_dsp["0"]-1);
			}
		}

		if ($row["s_prj_status"] == 2){	//�󒍂��Ă���
			$kakin_m = get_month($d_yyyy, $row["s_prj_jyuchu_kakin_date"]);
		}else{
			$kakin_m = get_month($d_yyyy, $row["s_prj_mikomi_kakin_date"]);
		}

		if ($kakin_m != 0){
			if ($kakin_m < $m_dsp["0"]){
				$kakin_m = $kakin_m + ($m_dsp["0"]-1);
			}else{
				$kakin_m = $kakin_m - ($m_dsp["0"]-1);
			}
		}

		$shoki_siire_m = intval(substr($row["k_siire_shoki_siharai_date"], 5, 2));

		if ($shoki_siire_m != 0){
			if ($shoki_siire_m < $m_dsp["0"]){
				$shoki_siire_m = $shoki_siire_m + ($m_dsp["0"]-1);
			}else{
				$shoki_siire_m = $shoki_siire_m - ($m_dsp["0"]-1);
			}
		}

		$flag = 0;
		if ($row["s_prj_status"] == 2){
			if (strtotime($row["s_prj_jyuchu_shoki_date"]) >= strtotime($start_date) && strtotime($end_date) >= strtotime($row["s_prj_jyuchu_shoki_date"]) && $row["s_prj_jyuchu_shoki_date"] != "0000-00-00"){
				$flag = 1;
			}
			if (strtotime($row["s_prj_jyuchu_kakin_date"]) >= strtotime($start_date) && strtotime($end_date) >= strtotime($row["s_prj_jyuchu_kakin_date"]) && $row["s_prj_jyuchu_kakin_date"] != "0000-00-00"){
				$flag = 1;
			}
		}else{
			if (strtotime($row["s_prj_mikomi_jyuchu_date"]) >= strtotime($start_date) && strtotime($end_date) >= strtotime($row["s_prj_mikomi_jyuchu_date"]) && $row["s_prj_mikomi_jyuchu_date"] != "0000-00-00"){
				$flag = 1;
			}
			if (strtotime($row["s_prj_mikomi_kakin_date"]) >= strtotime($start_date) && strtotime($end_date) >= strtotime($row["s_prj_mikomi_kakin_date"]) && $row["s_prj_mikomi_kakin_date"] != "0000-00-00"){
				$flag = 1;
			}
		}

		if ($flag == 0){
			continue;
		}


//�c�ƒS���Ɨ\�Z�̂���Ј����}�b�`���O
		$terget = 0;
		for ($v=1; $v <= $staff_count; $v++){
			if ($budget[$v][0][1][1][13] == char_chg("to_dsp", $row["s_prj_tantou"])){
				$terget = $v;
				break;
			}
		}

		//�\�Z�̂Ȃ��Ј��͑S���܂�߂ĕ\��
		//�X�^�b�t���͂��̑� 11
		if ($terget == 0){
			$terget = 11;
		}

//�敪
		//�敪���Â����̂͑S�����̑�7
		if ($row["s_prj_kubun"] > 70){
			if ($terget == 11){	//�\�Z�̖����l�̔���͂��̑�
				$terget_kubun = 7;
			}else{
				$terget_kubun = $row["s_prj_kubun"]-70;
			}
		}else{
			$terget_kubun = 7;
		}

		if ($shoki_m != 0){
			if ($row["s_prj_status"] == 2){
				$budget[$terget][1][$terget_kubun][1][$shoki_m] += $row["s_prj_jyuchu_shoki_price"];

				if ($shoki_siire_m != 0){
					$siire_shoki[$shoki_siire_m] += $row["s_prj_siire_shoki_price"];	//�J���󒍕��̐�������
				}

			}else{
				if ($row["s_prj_rank"] != 0){
					$budget[$terget][2][$terget_kubun][1][$shoki_m] += ceil($row["s_prj_mikomi_shoki_price"] * $rank_tx[$row["s_prj_rank"]][1] / 100);

					if ($shoki_siire_m != 0){
						$siire_shoki[$shoki_siire_m] += $row["s_prj_siire_shoki_price"];	//�J���󒍕��̐�������
					}
				}
			}
		}

		if ($kakin_m != 0){

			if ($row["s_prj_status"] == 2){
				$budget[$terget][1][$terget_kubun][2][$kakin_m] += $row["s_prj_jyuchu_kakin_price"];
			}else{
				if($row["s_prj_rank"] != 0){
					$budget[$terget][2][$terget_kubun][2][$kakin_m] += ceil($row["s_prj_mikomi_kakin_price"] * $rank_tx[$row["s_prj_rank"]][1] / 100);
				}
			}
		}
	}
}


//�����j���O�̎��сE������������݌v�����߂�
for ($tt = 1; $tt <= $staff_count+1; $tt++){
	for ($ll=1; $ll <= 7; $ll++){
		$ruikei1 = 0;
		$ruikei2 = 0;
		for ($j = 1; $j <= 12; $j ++){
			$ruikei1 += $budget[$tt][1][$ll][2][$j];
			$budget[$tt][1][$ll][3][$j] = $ruikei1;
			$ruikei2 += $budget[$tt][2][$ll][2][$j];
			$budget[$tt][2][$ll][3][$j] = $ruikei2;

			$all_total[$tt][0][$j] += ($budget[$tt][0][$ll][1][$j] + $budget[$tt][0][$ll][3][$j]);	//�ڕW
			$all_total[$tt][1][$j] += ($budget[$tt][1][$ll][1][$j] + $budget[$tt][1][$ll][3][$j]);	//����
			$all_total[$tt][2][$j] += ($budget[$tt][2][$ll][1][$j] + $budget[$tt][2][$ll][3][$j]);	//����
		}
	}
}

//�ڕW�̑���������߂�
for ($tt = 1; $tt <= $staff_count+1; $tt++){
	for ($j = 1; $j <= 12; $j ++){
		$all_total_m[$j] += $all_total[$tt][0][$j];
		$all_total_j[$j] += $all_total[$tt][1][$j];
		$all_total_k[$j] += $all_total[$tt][2][$j];
	}
}

$total_m = 0;
$total_k = 0;
$total_j = 0;
for ($kk = 1; $kk <= 12; $kk ++)
{
	$total_m += $all_total_m[$kk];
	$total_k += $all_total_k[$kk];
	$total_j += $all_total_j[$kk];
}


	$sql = "UPDATE t_tmp SET total_m = '".$total_m."', total_k = '".$total_k."', total_j = '".$total_j."' WHERE cd = 'calc_budget'";
	$result = executeQuery($sql);

}
return;

?>
