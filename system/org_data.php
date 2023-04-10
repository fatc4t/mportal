<?php
//各のPHP
require_once("./DBAccess_Function.php");

$password = (isset($_GET['p'])) ? $_GET['p'] : "";

if ($password == "dfhDKenf748op590sFmaGFNop98KPFODTSKJ5905hud"){
	$schema_cnt = 0;
        $cnt = 0;
	$sql = "SELECT * FROM public.m_company_contract WHERE is_del = 0";
        $rows = getList($sql);
        if($rows){
		while($row = $rows[$cnt]) {
		        $cnt_org = 0;
			$schema[$schema_cnt] = $row['company_name'];
			$schema_cnt ++;
			$cnt += 1;
		}
	}
	for ($j = 0; $j < $schema_cnt; $j++){
	        $cnt = 0;
		$sql = "SELECT * FROM ".$schema[$j].".m_organization_detail left join ".$schema[$j].".m_organization on ".$schema[$j].".m_organization_detail.organization_id = ".$schema[$j].".m_organization.organization_id where ".$schema[$j].".m_organization.is_del = 0 and ";
		$sql .= "(";
		$sql .= $schema[$j].".m_organization_detail.profit = 1 or ";
		$sql .= $schema[$j].".m_organization_detail.attendance = 1 or ";
		$sql .= $schema[$j].".m_organization_detail.workflow = 1 or ";
		$sql .= $schema[$j].".m_organization_detail.groupware = 1 ";
		$sql .= ") ";
		$sql .= "ORDER BY ".$schema[$j].".m_organization.disp_order ASC";
	        $rows = getList($sql);
	        if($rows){
			while($row = $rows[$cnt]) {
				$orgData[]=array(
						'company_code'=>$schema[$j],
						'department_code'=>$row['department_code'],
						'organization_name'=>$row['organization_name']
						);
				$cnt += 1;
			}
		}
	}

	header('Content-type: application/json');
	echo json_encode($orgData);
}
?>
