<?php
//指定comp_idの組織全部の自動Ｍコードを再計算
//引数
//yyyy:年
//mm:月
//dd:日 →月次の時は01
//type:
//11:売上報告書
//12:コスト報告書（日次）
//13:コスト報告書（月次）
//14:目標設定（日次）
//15:目標設定（月次）
//16:仕入報告書
//17:勤怠報告書
//20:その他

require_once("../public/f_function.php");
require_once("../public/f_db.php");
require_once("../public/core/sales/f_sales_form.php");
require_once("../public/f_html.php");
require_once("../public/f_session.php");
require_once("../public/f_get.php");
require_once("../public/core/sales/f_logic.php");

	$time_start = microtime(true);

	$cd = 0;
	$yyyy = "";
	$mm = "";
	$dd = "";
	$type = "";

	if (isset($_GET["type"])) {
		$type = $_GET["type"];
	}

	if (isset($_GET["yyyy"])) {
		$yyyy = $_GET["yyyy"];
	}
	if (isset($_GET["mm"])) {
		$mm = $_GET["mm"];
	}
	if (isset($_GET["dd"])) {
		$dd = $_GET["dd"];
	}
	if (isset($_GET["comp_id"])) {
		$comp_id = $_GET["comp_id"];
	}

	

	if ($yyyy == "" || $mm == "" || $dd == ""){
		echo "年月日の指定が不正です<br />\n";
		log_insert("", "", "", "年月日の指定が不正です", "engine type calc", "", "");
		goto skip;
	}
	if ($type == "" || ($type < 11 && $type > 17)){
		echo "日次・月次の指定がありません<br />\n";
		log_insert("", "", "", "日次・月次の指定がありません", "engine type calc", "", "");
		goto skip;
	}

	if ($type == 13 || $type == 15){
		$from = $yyyy."-".$mm."-01";
		$to = $yyyy."-".$mm."-31";
	}else{
		$from = $yyyy."-".$mm."-".$dd;
		$to = $yyyy."-".$mm."-".$dd;
	}


	$sql = "select * from t_mapping_name where df = 0 and link = 0 and type = ".$type;

	$mapping_name[0][0] = "";
	$mapping_name[0][1] = "";
	$mapping_name_cnt = 0;
	list( $rows, $result ) = executeQuery(0, $sql);
	if($rows){
		while($row = mysql_fetch_array($result)) {
			$mapping_name[$mapping_name_cnt][0] = $row["code"];			//Mコード
			$mapping_name[$mapping_name_cnt][1] = $row["mapping_name_id"];
			$mapping_name_cnt += 1;
		}
		mysql_free_result($result);
	}


	$table_mcode_name = "t_".$yyyy."_mcode_data";

	$get_ok_logic = 0;

//Ｍコードのデータ設定（計算式タイプのみ）
		$mapping_data[0][0] = "";
		$mapping_data[0][1] = "";
		$mapping_data[0][2] = "";
		$mapping_data[0][3] = 0;
		$mapping_data_cnt = 0;
		for ($jj = 0; $jj < $mapping_name_cnt; $jj++){
			//計算式タイプのマッピングデータのみ
			$sql = "select * from t_pos_mapping where mapping_name_id = '".$mapping_name[$jj][1]."' and df = 0 and logic_type = 1";
			list( $rows, $result ) = executeQuery(0, $sql);
			if($rows){
				$row = mysql_fetch_array($result);

				$mapping_data[$mapping_data_cnt][0] = $mapping_name[$jj][0];	//Ｍコード
				$mapping_data[$mapping_data_cnt][1] = $row["logic"];		//ロジック
				$mapping_data[$mapping_data_cnt][2] = $row["keta"];		//小数点以下の桁数
				$mapping_data[$mapping_data_cnt][3] = $row["round_type"];		//端数処理

				$mapping_data_cnt += 1;
				mysql_free_result($result);
			}
		}

	$sql_org = "SELECT * FROM t_org where comp_id = '".$comp_id."' and pos_code != ''";
	list( $rows_org, $result_org ) = executeQuery(0, $sql_org);

	if($rows_org){
		while($row_org = mysql_fetch_array($result_org)) {
			//ロジックは2列目　Ｍコードを数値に変換
			for ($jj = 0; $jj < $mapping_data_cnt; $jj++){
				$err_f = 0;
				//ロジックを解析し値を算出
				list($err_f, $price) = f_mcode_logic($table_mcode_name, $comp_id, $row_org["org_id"], $mapping_data[$jj], $from, $to);

				if ($err_f == 1){
					echo $row_org["org_id"].":(".$mapping_data[$jj][0].")(".$mapping_data[$jj][1].")NG !!! → zero除算<br />\n";
				}else if ($err_f == 2){
					echo $row_org["org_id"].":(".$mapping_data[$jj][0].")(".$mapping_data[$jj][1].")NG !!! → 指定されたＭコードの値が取得できません<br />\n";
				}else{

					if ($type == 13 || $type == 15){
						$sql = "DELETE FROM ".$table_mcode_name." WHERE comp_id = '".$comp_id."' and org_id = '".$row_org["org_id"]."' and yyyymmdd = '".$yyyy.$mm."01' and type = ".$type." and mcode = '".$mapping_data[$jj][0]."'";
					}else{
						$sql = "DELETE FROM ".$table_mcode_name." WHERE comp_id = '".$comp_id."' and org_id = '".$row_org["org_id"]."' and yyyymmdd = '".$yyyy.$mm.$dd."' and type = ".$type." and mcode = '".$mapping_data[$jj][0]."'";
					}
					list( $rows, $result ) = executeQuery(1, $sql);


					$sql = "INSERT INTO ".$table_mcode_name." VALUES(";
							$sql .= "NULL, ";
							$sql .= "'".$comp_id."', ";
							$sql .= "'".$row_org["org_id"]."', ";
							$sql .= "'0', ";
							$sql .= "'".$type."', ";
							$sql .= "'".$mapping_data[$jj][0]."', ";
					if ($type == 13 || $type == 15){
							$sql .= "'".$yyyy.$mm."01', ";
					}else{
							$sql .= "'".$yyyy.$mm.$dd."', ";
					}
							$sql .= "'".$price."', ";
							$sql .= "NULL, ";
							$sql .= "CURRENT_TIMESTAMP)";
					list( $rows, $result ) = executeQuery(1, $sql);
					$get_ok_logic += 1;



				}
			}
		}
		echo $get_ok_logic." 件の式マッピングデータを算出しました<br />\n";
		log_insert("", "", "", $get_ok_logic." 件の式マッピングデータを算出しました", "engine type calc", "", "");

		mysql_free_result($result_org);

	}

skip:

	$timelimit = microtime(true) - $time_start;
	echo $timelimit." seconds<br />\n";
	echo "-----<br />\n";
	@ob_flush();
	@flush();

?>
