<?php

function f_security_check($self, $name)
{
require_once("../public/f_staff.php");

	$err_cd = 0;
	$admin = 0;
	$busho = f_staff_busho_id_get($name);

	if ($self === "/mportal/sales/budget/tantou_hiritsu_graph.php" ||	//�c�ƃ��j���[�@�S������
	    $self === "/mportal/sales/budget/kojin_graph.php" ||		//              �S���ҕʎ���
	    $self === "/mportal/sales/budget/sales.php" ||			//              �e��\�Z
	    $self === "/mportal/sales/budget/sales_budget.php" ||			//              �e��\�Z
	    $self === "/mportal/sales/budget/sales_budget2.php" ||			//              �e��\�Z
	    $self === "/mportal/sales/budget/menu.php" )			//              �\���Ǘ�
	{
//		if ($busho != 1 && $busho != 4){		//�c�ƕ��ƊǗ����̂�
//			$err_cd = 1;
//		}
	}
	else if($self === "/mportal/sales/master/menu.php")			//�}�X�^�[���j���[
	{
		$admin = f_staff_admin_get($name);		//�Ǘ��҂̂�
		if ($admin == 0){
			$err_cd = 1;
		}
	}
	else if($self === "/mportal/sales/project/running_list.php")			//�}�X�^�[���j���[
	{
		if ($busho != 400){		//�Ǘ����̂�
			$err_cd = 1;
		}
	}
	else
	{
	}

	return($err_cd);
}

?>
