<?php

function f_security_check($self, $name)
{
require_once("../public/f_staff.php");

	$err_cd = 0;
	$admin = 0;
	$busho = f_staff_busho_id_get($name);

	if ($self === "/mportal/sales/budget/tantou_hiritsu_graph.php" ||	//営業メニュー　担当件数
	    $self === "/mportal/sales/budget/kojin_graph.php" ||		//              担当者別実績
	    $self === "/mportal/sales/budget/sales.php" ||			//              各種予算
	    $self === "/mportal/sales/budget/sales_budget.php" ||			//              各種予算
	    $self === "/mportal/sales/budget/sales_budget2.php" ||			//              各種予算
	    $self === "/mportal/sales/budget/menu.php" )			//              予実管理
	{
//		if ($busho != 1 && $busho != 4){		//営業部と管理部のみ
//			$err_cd = 1;
//		}
	}
	else if($self === "/mportal/sales/master/menu.php")			//マスターメニュー
	{
		$admin = f_staff_admin_get($name);		//管理者のみ
		if ($admin == 0){
			$err_cd = 1;
		}
	}
	else if($self === "/mportal/sales/project/running_list.php")			//マスターメニュー
	{
		if ($busho != 400){		//管理部のみ
			$err_cd = 1;
		}
	}
	else
	{
	}

	return($err_cd);
}

?>
