<?php
    //**************************************************
    //
    // 機能   :発注データから旧本部システムの店舗発注CSVで
    //         取込み可能なCSVファイルを作成して指定先にメールで送信する
    //
    // 作成者 :millionet oota
    // 作成日 :2019/0514
    //
    // 備考   :シグマFC店対応用
    //
    //**************************************************

// DB設定を読み込み
require_once("DBAccess_Function.php");

        // 実行開始時間を取得
	$time_start = microtime(true);

        // 日付の設定 0:前日 1:指定した日付
	$flag = 0;

        // パラメーターの年を取得
	if (isset($_GET["yyyy"])) {
		$yyyy = $_GET["yyyy"];
		$flag = 1;
	}else if($yyyy != ""){
            $flag = 1;
	}else{
            $yyyy = date("Y");
        }

        // パラメータの月を取得
	if (isset($_GET["mm"])) {
		$mm = $_GET["mm"];
		$flag = 1;
	}else if($mm != ""){
            $flag = 1;
	}else{
            $mm = date("m");
	}

        // パラメーターの日を取得
	if (isset($_GET["dd"])) {
		$dd = $_GET["dd"];
		$flag = 1;
	}else if($dd != ""){
            $flag = 1;
	}else{
            $dd = date("d");
	}

        // 日付のチェックフラグ
	if ($flag == 0){
                //
		list($yyyy, $mm, $dd) = computeDate($yyyy, $mm, $dd, -1);
	}

        // 日付の不正チェック
	if ($yyyy == "" || $mm == "" || $dd == ""){
		echo "年月日の指定が不正です<br />\n";
		//log_insert("", "", "", "年月日の指定が不正です", "engine type import", "", "");
		goto skip;
	}

        // スキーマ名
        $schema = "acrossring";

        //
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

	$org_data[0] = "";
	$org_data_cnt = 0;
	$tenpo_code_tmp = "";

        // 取込みデータを取得
	$sql_import = "select * from ".$table_name." where target_date = '".$yyyy."/".$mm."/".$dd."'";
        $rows_import = getList($sql_import);

	if($rows_import){

                $row_cnt = 0;
                
                // 対象データ分実行
		while($row_import = $rows_import[$row_cnt]) {

			$pos_key_file_id = $row_import["pos_key_file_id"];
			$tenpo_code = $row_import["pos_code"];
                        
                        // 対象組織のチェック
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
				$org_data[$org_data_cnt] = $org_id;
				$org_data_cnt ++;
			}

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
                                                  ."   AND organization_id = '".$org_id."'"
                                                  ."   AND type = 1"
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
