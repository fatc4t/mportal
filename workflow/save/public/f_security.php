<?php

function f_security_check($self, $name)
{
require_once("../public/f_staff.php");

	$err_cd = 0;
	$admin = 0;
	$busho = f_staff_busho_id_get($name);

	if ($self === "/mportal/sales/budget/tantou_hiritsu_graph.php" ||	//‰c‹Æƒƒjƒ…[@’S“–Œ”
	    $self === "/mportal/sales/budget/kojin_graph.php" ||		//              ’S“–ŽÒ•ÊŽÀÑ
	    $self === "/mportal/sales/budget/sales.php" ||			//              ŠeŽí—\ŽZ
	    $self === "/mportal/sales/budget/sales_budget.php" ||			//              ŠeŽí—\ŽZ
	    $self === "/mportal/sales/budget/sales_budget2.php" ||			//              ŠeŽí—\ŽZ
	    $self === "/mportal/sales/budget/menu.php" )			//              —\ŽÀŠÇ—
	{
//		if ($busho != 1 && $busho != 4){		//‰c‹Æ•”‚ÆŠÇ—•”‚Ì‚Ý
//			$err_cd = 1;
//		}
	}
	else if($self === "/mportal/sales/master/menu.php")			//ƒ}ƒXƒ^[ƒƒjƒ…[
	{
		$admin = f_staff_admin_get($name);		//ŠÇ—ŽÒ‚Ì‚Ý
		if ($admin == 0){
			$err_cd = 1;
		}
	}
	else if($self === "/mportal/sales/project/running_list.php")			//ƒ}ƒXƒ^[ƒƒjƒ…[
	{
		if ($busho != 400){		//ŠÇ—•”‚Ì‚Ý
			$err_cd = 1;
		}
	}
	else
	{
	}

	return($err_cd);
}

?>
