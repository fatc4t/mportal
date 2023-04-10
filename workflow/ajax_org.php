<?php
require_once("DBAccess_Function.php");

require_once("./public/f_function.php");

require_once("./public/f_status.php");
require_once("./public/f_customer.php");
require_once("./public/f_staff.php");
require_once("./public/workflow.php");
session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();

	$org_id=$_POST['org_id'];

	if ($org_id == 0){
	}else{
		$o_id = detail_org_id_get($org_id);
		$sql = "SELECT user_name, user_id FROM ".$schema.".m_user_detail WHERE organization_id = " .$o_id." order by employees_no asc";
		$cnt = 0;
	        $rows = getList($sql);
	               
	        if($rows){
			while($row = $rows[$cnt]) {
				echo '<option value="'.$row['user_id'].'">'.$row['user_name'].'</option>';
				$cnt += 1;
			}
		}
	}
?>
