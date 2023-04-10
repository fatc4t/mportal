<?php
//取り込んだインポートデータから関係する日次のＭデータを作成する
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
		//log_insert("", "", "", "年月日の指定が不正です", "engine type import", "", "");
		goto skip;
	}

        // スキーマ名
        $schema = "miyagawa";

        $table_name = $schema.".t_import_data_day";
	$table_mcode_name = $schema.".t_mcode_data_day";

	$get_ok = 0;

        //マッピング名の取得
	$sql = "select * from ".$schema.".m_mapping_name where is_del = 0 and link = 0 and mapping_type != 13 and mapping_type != 15";
	$mapping_name[0][0] = "";
	$mapping_name[0][1] = "";
	$mapping_name_cnt = 0;

        $rows = getList($sql);

        if($rows){
		while($row = $rows[$mapping_name_cnt]) {
			$mapping_name[$mapping_name_cnt][0] = $row["mapping_code"];
			$mapping_name[$mapping_name_cnt][1] = $row["mapping_name_id"];
			$mapping_name_cnt += 1;
		}
	}
        
        //Ｍコードのデータ設定（取得タイプのみ）
        //マッピングデータの取得・設定
	$mapping_data[0][0] = "";
	$mapping_data[0][1] = "";
	$mapping_data[0][2] = "";
	$mapping_data[0][3] = 0;
	$mapping_data_cnt = 0;
        $loop_cnt = 0;
	for ($jj = 0; $jj < $mapping_name_cnt; $jj++){
		//取得タイプのマッピングデータのみ
		$sql = "select m.*, p.pos_key_file_name from ".$schema.".m_pos_mapping as m left join ".$schema.".m_pos_key_file as p on(m.pos_key_file_id = p.pos_key_file_id)"
                     . " where mapping_name_id = '".$mapping_name[$jj][1]."' and m.is_del = 0 and m.logic_type = 0";

                $rows = getList($sql);
                $key_file_cnt = 0;
                
                if($rows){
                    while($row = $rows[$key_file_cnt]) {
                        $mapping_data[$mapping_data_cnt][0] = $mapping_name[$jj][0];	 //Ｍコード
			$mapping_data[$mapping_data_cnt][1] = $row["logic"];		 //ロジック
			$mapping_data[$mapping_data_cnt][2] = $row["pos_key_file_name"]; //キーファイル名
			$mapping_data[$mapping_data_cnt][3] = $row["logic_type"];	 //ロジックタイプ
                        
                        $key_file_cnt++;
                        $mapping_data_cnt += 1;
                    }

		}
	}

        // 組織IDを取得
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

                // 取込み日のデータを上書きするので削除
	  	$sql = "DELETE FROM ".$table_mcode_name." WHERE target_date = '".$yyyy.$mm.$dd."' and organization_id = '".$org_id."' and pos_key_file_id = '".$key_file_name."' and type = 1";
                sqlExec($sql);

                $row_cnt = 0;
                
                // 対象データ分実行
		while($row_import = $rows_import[$row_cnt]) {

			$pos_key_file_id = $row_import["pos_key_file_id"];

			$data = explode(",", $row_import["data_line"]);
                        
                        // Mコードごとにデータと比較して登録
			for ($jj = 0; $jj < $mapping_data_cnt; $jj++){
                            
                            // ポスファイル名でチェック
                            if($pos_key_file_id == $mapping_data[$jj][2]){
                                
				$flag = 0;
				$mapping = explode(",", $mapping_data[$jj][1]);

				if ($mapping[2] != ""){	//and か or 条件あり
					if ($mapping[2] == "and"){
						if ($data[$mapping[0]-1] == $mapping[1] && $data[$mapping[3]-1] == $mapping[4]){	//条件に一致
							$price = $data[$mapping[5]-1];
							$flag = 1;
						}
					}else if ($mapping[2] == "or"){
						if ($data[$mapping[0]-1] == $mapping[1] || $data[$mapping[3]-1] == $mapping[4]){	//条件に一致
							$price = $data[$mapping[5]-1];
							$flag = 1;
						}
					}
				}else{
                                        // 最初の指定が0だったら
                                        if($mapping[0] == "0"){
                                        //キーチェックなしのモード
                                            $price = $data[$mapping[5]-1];
                                            $flag = 1;
                                        }else if ($data[$mapping[0]-1] == $mapping[1]){	//条件に一致
                                        // キーチェックモード
                                            $price = $data[$mapping[5]-1];
                                            $flag = 1;
					}
				}

                                // Mコードと一致する内容があれば書き込み
				if ($flag == 1){
                                    
                                        // 重複データがあるかチェック
                                        $sql = "SELECT data FROM ".$table_mcode_name
                                              ." WHERE target_date = '".$yyyy.$mm.$dd."'"
                                              ."   AND organization_id = '".$org_id."'"
                                              ."   AND pos_key_file_id = '".$key_file_name."'"
                                              ."   AND type = 1"
                                              ."   AND mcode = '".$mapping_data[$jj][0]."'";
                                        $datecheck = getList($sql);
                                        $sdata = floatval($price);
                                        
                                        if($datecheck){
                                            $datecheckOne = $datecheck[0];
                                            // データがある場合は、削除して値を加算
                                            $sdata = floatval($datecheckOne["data"]) + floatval($price);

                                            $sql = "DELETE FROM ".$table_mcode_name
                                                  ." WHERE target_date = '".$yyyy.$mm.$dd."'"
                                                  ."   AND type = 1"
                                                  ."   AND organization_id = '".$org_id."'"
                                                  ."   AND pos_key_file_id = '".$key_file_name."'"
                                                  ."   AND mcode = '".$mapping_data[$jj][0]."'";
                                            sqlExec($sql);

                                        $get_ok -= 1;
                                            
                                        }

                                        // データがない場合は、新規登録
                                        $sql = "INSERT INTO ".$table_mcode_name." (";
                                            $sql .= "organization_id, ";
                                            $sql .= "recipient_id, ";
                                            $sql .= "type, ";
                                            $sql .= "mcode, ";
                                            $sql .= "target_date, ";
                                            $sql .= "data, ";
                                            $sql .= "pos_key_file_id, ";
                                            $sql .= "registration_time ";
                                            $sql .= ") ";
                                            $sql .= "VALUES(";
                                            $sql .= $org_id.", ";
                                            $sql .= "'0', ";
                                            $sql .= "'1', ";
                                            $sql .= "'".$mapping_data[$jj][0]."', ";
                                            $sql .= "'".$yyyy.$mm.$dd."', ";
                                            $sql .= "'".$sdata."', ";	//intval 浮動小数点なし
                                            $sql .= "'".$pos_key_file_id."', ";
                                            $sql .= "CURRENT_TIMESTAMP)";

                                        sqlExec($sql);
                                            
                                        $get_ok += 1;
				}

                                
                            }
			}

                        $row_cnt ++;
		}

		echo $get_ok." 件のマッピングデータを取得しました<br />\n";
		//log_insert("", "", "", $get_ok." 件のマッピングデータを取得しました", "engine type import", "", "");
	}

skip:

	$timelimit = microtime(true) - $time_start;
	echo $timelimit." seconds<br />\n";
	echo "-----<br />\n";
	@ob_flush();
	@flush();

?>
