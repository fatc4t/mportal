<?php
//取り込んだインポートデータから時間別のデータを作成
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
		//log_insert("", "", "", "年月日の指定が不正です", "engine type time", "", "");
		goto skip;
	}

        // スキーマ名
        $schema = "langrise";

        $table_name = $schema.".t_import_data_time";
	$table_mcode_name = $schema.".t_mcode_data_time";


	$get_ok = 0;

        //時間別別マッピングデータの取得
	$sql = "select * from ".$schema.".m_pos_mapping_time where is_del = 0";
	$t_key_file_name[0] = "";
	$start_time[0] = "";
	$end_time[0] = "";
	$count[0] = "";
	$sales[0] = "";
	$sales_tax[0] = "";
	$guest_count[0] = "";
	$group_count[0] = "";
	$coupon[0] = "";
	$coupon_tax[0] = "";
	$coupon_count[0] = "";
	$col_limit[0] = 0;
	$time_mapping_cnt = 0;

        $rows = getList($sql);

        if($rows){
		while($row = $rows[$time_mapping_cnt]) {
			$t_key_file_name[$time_mapping_cnt] = $row["key_file_name"];
			$start_time[$time_mapping_cnt] = $row["start_time"];
			$end_time[$time_mapping_cnt] = $row["end_time"];
			$count[$time_mapping_cnt] = $row["count"];
			$sales[$time_mapping_cnt] = $row["sales"];
			$sales_tax[$time_mapping_cnt] = $row["sales_tax"];
			$guest_count[$time_mapping_cnt] = $row["guest_count"];
			$group_count[$time_mapping_cnt] = $row["group_count"];
			$coupon[$time_mapping_cnt] = $row["coupon"];
			$coupon_tax[$time_mapping_cnt] = $row["coupon_tax"];
			$coupon_count[$time_mapping_cnt] = $row["coupon_count"];
			$col_limit[$time_mapping_cnt] = $row["col_limit"];
			$time_mapping_cnt += 1;
		}
	}

        $org_id = "";
        $sql = "SELECT organization_id FROM ".$schema.".m_organization_detail WHERE department_code = '".$tenpo_code."'";

        $rows = getList($sql);

        if($rows){
            $row = $rows[0];
            $org_id = $row["organization_id"]; 
        }
                
        // 取込みデータを取得
	$sql_import = "select * from ".$table_name." where target_date = '".$yyyy."/".$mm."/".$dd."'"." and pos_key_file_id = '".$key_file_name."' and pos_code = '".$tenpo_code."'";
        $rows_import = getList($sql_import);

	if($rows_import){

                $row_cnt = 0;
		$get_ok = 0;
                
                // 取込み日のデータを上書きするので削除
                $sql = "DELETE FROM ".$table_mcode_name." WHERE target_date = '".$yyyy.$mm.$dd."' and organization_id = '".$org_id."'";
                sqlExec($sql);

                while($row_import = $rows_import[$row_cnt]) {

			$data = explode(",", $row_import["data_line"]);
			for ($jj = 0; $jj < $time_mapping_cnt; $jj++){
				if($t_key_file_name[$jj] == $row_import["pos_key_file_id"]){
						if (count($data) >= $col_limit[$jj] && $start_time[$jj] != ""){
                                                        $count_set = 0;
                                                        $coupon_count_set = "0";
                                                        $group_count_set = "0";
                                                        
                                                        // 開始時間設定
							if ($start_time[$jj] != ""){
								$start_time_set = $data[$start_time[$jj]-1];
                                                        }
                                                        
                                                        // 終了時間設定
							if ($end_time[$jj] != ""){
								$end_time_set = $data[$end_time[$jj]-1];
                                                        }
                                                        
                                                        // 商品数設定
							if ($count[$jj] != ""){
                                                            $count_set = $data[$count[$jj]-1];
                                                        }

                                                        // クーポン枚数設定
							if ($coupon_count[$jj] != ""){
                                                            // 複数種類ある場合は、全て合計する
                                                            $cc = explode(",", $coupon_count[$jj]-1);
                                                            $coupon_count_set = floatval($data[$cc[0]]) + floatval($data[$cc[1]]);
                                                        }

                                                        // 売上(税抜)を設定
							if ($sales[$jj] != ""){
								$sales_set = floatval($data[$sales_tax[$jj]-1]) - floatval($data[$sales[$jj]-1]);
                                                        }

                                                        // 売上(税込)を設定
                                                        if ($sales_tax[$jj] != ""){
								$sales_tax_set = $data[$sales_tax[$jj]-1];
                                                        }

                                                        // 客数を設定
                                                        if ($guest_count[$jj] != ""){
								$guest_count_set = $data[$guest_count[$jj]-1];
                                                        }

                                                        // 組数を設定
							if ($data[$group_count[$jj]-1] == 1){
                                                                // 通常伝票であれば組数1とする
								$group_count_set = "1";
                                                        }

                                                        // クーポン金額(税抜)を設定
                                                        if ($coupon[$jj] != ""){
								$coupon_set = $data[$coupon[$jj]-1];
                                                        }

                                                        // クーポン金額(税込)を設定
							if ($coupon_tax[$jj] != ""){
								$coupon_tax_set = $data[$coupon_tax[$jj]-1];
                                                        }
                                                        
                                                        $sql = "INSERT INTO ".$table_mcode_name." (";
                                                            $sql .= "organization_id, ";
                                                            $sql .= "target_date, ";
                                                            $sql .= "start_time, ";
                                                            $sql .= "end_time, ";
                                                            $sql .= "count, ";
                                                            $sql .= "sales, ";
                                                            $sql .= "sales_tax, ";
                                                            $sql .= "guest_count, ";
                                                            $sql .= "group_count, ";
                                                            $sql .= "coupon, ";
                                                            $sql .= "coupon_tax, ";
                                                            $sql .= "coupon_count, ";
                                                            $sql .= "registration_time ";
                                                            $sql .= ") ";
                                                            $sql .= "VALUES(";
                                                            $sql .= $org_id.", ";
                                                            $sql .= "'".$yyyy.$mm.$dd."', ";
							    $sql .= "'".$start_time_set."', ";
                                                            $sql .= "'".$end_time_set."', ";
                                                            $sql .= "'".floatval($count_set)."', ";
                                                            $sql .= "'".floatval($sales_set)."', ";
                                                            $sql .= "'".floatval($sales_tax_set)."', ";
                                                            $sql .= "'".floatval($guest_count_set)."', ";
                                                            $sql .= "'".floatval($group_count_set)."', ";
                                                            $sql .= "'".floatval($coupon_set)."', ";
                                                            $sql .= "'".floatval($coupon_tax_set)."', ";
                                                            $sql .= "'".floatval($coupon_count_set)."', ";
                                                            $sql .= "CURRENT_TIMESTAMP)";

                                                        sqlExec($sql);
							$get_ok += 1;
						}
				}
			}
                        $row_cnt ++;
		}
		echo $get_ok." 件の時間別マッピングデータを取得しました<br />\n";
		//log_insert("", "", "", $get_ok." 件の時間別マッピングデータを取得しました", "engine type time", "", "");
	}

skip:

	$timelimit = microtime(true) - $time_start;
	echo $timelimit." seconds<br />\n";
	echo "-----<br />\n";
	@ob_flush();
	@flush();

?>
