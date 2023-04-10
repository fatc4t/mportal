<?php
//取り込んだインポートデータから商品別のデータを作成
//引数
//yyyy:年
//mm:月
//dd:日

require_once("DBAccess_Function.php");

	$time_start = microtime(true);

	$cd = 0;
	$flag = 0;

	if (isset($_GET["yyyy"])) {
		$yyyy = $_GET["yyyy"];
		$flag = 1;
	}else if($yyyy != ""){
            $flag = 1;
	}else{
            $yyyy = date("Y");
        }

	if (isset($_GET["mm"])) {
		$mm = $_GET["mm"];
		$flag = 1;
	}else if($mm != ""){
            $flag = 1;
	}else{
            $mm = date("m");
	}

	if (isset($_GET["dd"])) {
		$dd = $_GET["dd"];
		$flag = 1;
	}else if($dd != ""){
            $flag = 1;
	}else{
            $dd = date("d");
	}

	if ($flag == 0){
		list($yyyy, $mm, $dd) = computeDate($yyyy, $mm, $dd, -1);
	}
	if ($yyyy == "" || $mm == "" || $dd == ""){
		echo "年月日の指定が不正です<br />\n";
		//log_insert("", "", "", "年月日の指定が不正です", "engine type item", "", "");
		goto skip;
	}


        // スキーマ名
        $schema = "ozaki_shoji";

        $table_name = $schema.".t_import_data_item";
	$table_mcode_name = $schema.".t_mcode_data_item";

	$get_ok = 0;

        //商品別マッピングデータの取得
	$sql = "select * from ".$schema.".m_pos_mapping_item where is_del = 0";
	$i_key_file_name[0] = "";
	$plu_code[0] = "";
	$plu_name[0] = "";
	$price[0] = "";
	$cost[0] = "";
	$large_class_code[0] = "";
	$large_class_name[0] = "";
	$middle_class_code[0] = "";
	$middle_class_name[0] = "";
	$small_class_code[0] = "";
	$small_class_name[0] = "";
	$count[0] = "";
	$sales[0] = "";
	$col_limit[0] = 0;
	$item_mapping_cnt = 0;

        $rows = getList($sql);

        if($rows){
		while($row = $rows[$item_mapping_cnt]) {
			$i_key_file_name[$item_mapping_cnt] = $row["key_file_name"];
			$plu_code[$item_mapping_cnt] = $row["plu_code"];
			$plu_name[$item_mapping_cnt] = $row["plu_name"];
			$price[$item_mapping_cnt] = $row["price"];
			$cost[$item_mapping_cnt] = $row["cost"];
			$large_class_code[$item_mapping_cnt] = $row["large_class_code"];
			$large_class_name[$item_mapping_cnt] = $row["large_class_name"];
			$middle_class_code[$item_mapping_cnt] = $row["middle_class_code"];
			$middle_class_name[$item_mapping_cnt] = $row["middle_class_name"];
			$small_class_code[$item_mapping_cnt] = $row["small_class_code"];
			$small_class_name[$item_mapping_cnt] = $row["small_class_name"];
			$count[$item_mapping_cnt] = $row["count"];
			$sales[$item_mapping_cnt] = $row["sales"];
			$col_limit[$item_mapping_cnt] = $row["col_limit"];
			$item_mapping_cnt += 1;
		}
	}

	$org_data[0] = "";
	$org_data_cnt = 0;
	$tenpo_code_tmp = "";

	$sql_import = "select * from ".$table_name." where target_date = '".$yyyy."/".$mm."/".$dd."'";
        $rows_import = getList($sql_import);

	if($rows_import){

                $row_cnt = 0;
		$get_ok = 0;

                while($row_import = $rows_import[$row_cnt]) {
			$tenpo_code = $row_import["pos_code"];
			if ($tenpo_code_tmp != $tenpo_code){
                            $org_id = "";
                            $sql = "SELECT organization_id FROM ".$schema.".m_organization_detail WHERE department_code = '".$tenpo_code."'";

                            $rows = getList($sql);

                            if($rows){
                                $row = $rows[0];
                                $org_id = $row["organization_id"]; 
                            }
			}
                        
			$tenpo_code_tmp = $tenpo_code;

			$f = 0;
			for ($kk = 0; $kk < $org_data_cnt; $kk++){
				if ($org_data[$kk] == $org_id){
					$f = 1;
					break;
				}
			}
			if ($f == 0){
			  	$sql = "DELETE FROM ".$table_mcode_name." WHERE target_date = '".$yyyy.$mm.$dd."' and organization_id = '".$org_id."'";
                                sqlExec($sql);

                                $org_data[$org_data_cnt] = $org_id;
				$org_data_cnt ++;
			}

			$data = explode(",", $row_import["data_line"]);
			for ($jj = 0; $jj < $item_mapping_cnt; $jj++){
				if($i_key_file_name[$jj] == $row_import["pos_key_file_id"]){
						if (count($data) >= $col_limit[$jj] && $plu_code[$jj] != ""){
							if ($plu_code[$jj] != "")
								$plu_code_set = $data[$plu_code[$jj]-1];
							if ($plu_name[$jj] != "")
								$plu_name_set = $data[$plu_name[$jj]-1];
							if ($price[$jj] != "")
								$price_set = $data[$price[$jj]-1];
							if ($cost[$jj] != "")
								$cost_set = $data[$cost[$jj]-1];
							if ($large_class_code[$jj] != "")
								$large_class_code_set = $data[$large_class_code[$jj]-1];
							if ($large_class_name[$jj] != "")
								$large_class_name_set = $data[$large_class_name[$jj]-1];
							if ($middle_class_code[$jj] != "")
								$middle_class_code_set = $data[$middle_class_code[$jj]-1];
							if ($middle_class_name[$jj] != "")
								$middle_class_name_set = $data[$middle_class_name[$jj]-1];
							if ($small_class_code[$jj] != "")
								$small_class_code_set = $data[$small_class_code[$jj]-1];
							if ($small_class_name[$jj] != "")
								$small_class_name_set = $data[$small_class_name[$jj]-1];
				//点数
							if ($count[$jj] != ""){
								$count_data = explode(",", $count[$jj]);
								$count_data_max = count($count_data);
								$count_set = 0;
								for ($kk=0; $kk < $count_data_max; $kk++){
									$count_set += $data[$count_data[$kk]-1];
								}
							}else{
								$count_set = 0;
							}
				//金額
							if ($sales[$jj] != ""){
								$sales_data = explode(",", $sales[$jj]);
								$sales_data_max = count($sales_data);
								$sales_set = 0;
								for ($kk=0; $kk < $sales_data_max; $kk++){
									$sales_set += $data[$sales_data[$kk]-1];
								}
							}else{
								$sales_set = 0;
							}

                                                        $sql = "INSERT INTO ".$table_mcode_name." (";
                                                            $sql .= "organization_id, ";
                                                            $sql .= "target_date, ";
                                                            $sql .= "plu_code, ";
                                                            $sql .= "plu_name, ";
                                                            $sql .= "price, ";
                                                            $sql .= "cost, ";
                                                            $sql .= "large_class_code, ";
                                                            $sql .= "large_class_name, ";
                                                            $sql .= "medium_class_code, ";
                                                            $sql .= "medium_class_name, ";
                                                            $sql .= "small_class_code, ";
                                                            $sql .= "small_class_name, ";
                                                            $sql .= "count, ";
                                                            $sql .= "sales, ";
                                                            $sql .= "registration_time ";
                                                            $sql .= ") ";
                                                            $sql .= "VALUES(";
                                                            $sql .= $org_id.", ";
                                                            $sql .= "'".$yyyy.$mm.$dd."', ";
                                                            $sql .= "'".$plu_code_set."', ";
                                                            $sql .= "'".$plu_name_set."', ";
                                                            $sql .= "'".floatval($price_set)."', ";
                                                            $sql .= "'".floatval($cost_set)."', ";
                                                            $sql .= "'".$large_class_code_set."', ";
                                                            $sql .= "'".$large_class_name_set."', ";
                                                            $sql .= "'".$middle_class_code_set."', ";
                                                            $sql .= "'".$middle_class_name_set."', ";
                                                            $sql .= "'".$small_class_code_set."', ";
                                                            $sql .= "'".$small_class_name_set."', ";
                                                            $sql .= "'".floatval($count_set)."', ";
                                                            $sql .= "'".floatval($sales_set)."', ";
                                                            $sql .= "CURRENT_TIMESTAMP)";

                                                sqlExec($sql);
                                                $get_ok += 1;

                                                }
				}
			}
                        $row_cnt ++;
		}
		echo $get_ok." 件の商品別マッピングデータを取得しました<br />\n";
		//log_insert("", "", "", $get_ok." 件の商品別マッピングデータを取得しました", "engine type item", "", "");
	}

skip:

	$timelimit = microtime(true) - $time_start;
	echo $timelimit." seconds<br />\n";
	echo "-----<br />\n";
	@ob_flush();
	@flush();

?>
