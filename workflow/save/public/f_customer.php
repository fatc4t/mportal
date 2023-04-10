<?php

//顧客名のリストボックス表示
function f_customer_select($f_customer_no)
{

	$sql = "SELECT name, no FROM t_customer ORDER BY no";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "<select name=\"customer_no\">\n";
	$html .= "<option value=\"0000\">全て</option>\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if ($f_rows["no"] == $f_customer_no){
				$html .= "<option value=\"".$f_rows["no"]."\" selected>".char_chg("to_dsp", $f_rows["name"])."</option>\n";
			}else{
				$html .= "<option value=\"".$f_rows["no"]."\">".char_chg("to_dsp", $f_rows["name"])."</option>\n";
    			}
		}
	}

	$html .= "</select>\n";

	mysql_free_result($result);

return $html;

}

//顧客名のリストボックス表示
function f_customer_select2($f_customer_no)
{

	$sql = "SELECT name, no FROM t_customer WHERE kaiyaku = 0 ORDER BY no";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "<select name=\"customer_no\">\n";
	$html .= "<option value=\"0000\">------</option>\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if (f_customer_project_cnt_get3($f_rows["no"], 2) == 0){
				continue;
			}

			if ($f_rows["no"] == $f_customer_no){
				$html .= "<option value=\"".$f_rows["no"]."\" selected>".char_chg("to_dsp", $f_rows["name"])."</option>\n";
			}else{
				$html .= "<option value=\"".$f_rows["no"]."\">".char_chg("to_dsp", $f_rows["name"])."</option>\n";
    			}
		}
	}

	$html .= "</select>\n";

	mysql_free_result($result);

return $html;

}
//顧客名のリストボックス表示
function f_customer_select2_all($f_customer_no)
{

	$sql = "SELECT name, no FROM t_customer ORDER BY no";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "<select name=\"customer_no\">\n";
	$html .= "<option value=\"0000\">------</option>\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if ($f_rows["no"] == $f_customer_no){
				$html .= "<option value=\"".$f_rows["no"]."\" selected>".char_chg("to_dsp", $f_rows["name"])."</option>\n";
			}else{
				$html .= "<option value=\"".$f_rows["no"]."\">".char_chg("to_dsp", $f_rows["name"])."</option>\n";
    			}
		}
	}

	$html .= "</select>\n";

	mysql_free_result($result);

return $html;

}

//顧客名の重複チェック
function f_customer_name_serch($f_customer_name)
{
	$name = $f_customer_name;

	if (strlen($f_customer_name) != 0)
	{
//		$name = mb_convert_kana($f_customer_name,"as","SJIS"); 
		$name = str_replace("　", "", $name);
		$name = str_replace(" ", "", $name);
		$name = str_replace("(株)", "", $name);
		$name = str_replace("（株）", "", $name);
		$name = str_replace("㈱", "", $name);
		$name = str_replace("株式会社", "", $name);
		$name = str_replace("・", "", $name);
		$name = str_replace("･", "", $name);

//		$sql = "SELECT count(*) FROM t_customer WHERE name like \"".$name."\"";
//		$sql = "SELECT count(*) FROM t_customer WHERE name = '".$name."'";
		$sql = "SELECT count(*) FROM t_customer WHERE name like '%".char_chg("to_db", $name)."%'";
		$rnum1 = executeQuery($sql);
		list($num) = mysql_fetch_row($rnum1);

		mysql_free_result($rnum1);
	}else{
		$num = 0;
	}

return $num;
}

//顧客名（プロジェクトのある）　リストボックス表示
function f_customer_prj_select($f_customer_no)
{

	$sql = "SELECT name, no FROM t_customer ORDER BY no";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "<select name=\"customer_no\">\n";
	$html .= "<option value=\"0000\">全て</option>\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {

			$p_c = f_customer_project_cnt_get($f_rows["no"]);
			if ($p_c != 0){
				if ($f_rows["no"] == $f_customer_no){
					$html .= "<option value=\"".$f_rows["no"]."\" selected>".char_chg("to_dsp", $f_rows["name"])."</option>\n";
				}else{
					$html .= "<option value=\"".$f_rows["no"]."\">".char_chg("to_dsp", $f_rows["name"])."</option>\n";
	    			}
			}
		}
	}

	$html .= "</select>\n";

	mysql_free_result($result);

return $html;

}

//顧客情報取得 顧客番号　no→noyyyy
function f_customer_no_get($no)
{
	$c_no = "";
	$sql = "SELECT no, yyyy FROM t_customer WHERE no = " .$no;
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_no = $f_rows["yyyy"].$f_rows["no"];
	}

	mysql_free_result($result);

return $c_no;

}

//顧客情報取得 顧客番号から顧客名
function f_customer_name_get($no)
{
	$c_name = "";
	$sql = "SELECT name FROM t_customer WHERE no = " .$no;
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_name = char_chg("to_dsp", $f_rows["name"]);
	}

	mysql_free_result($result);

return $c_name;

}

//顧客情報取得 顧客番号から顧客名と解約フラグ
function f_customer_name2_get($no)
{
	$c_name = "";
	$c_kaiyaku = 0;
	$sql = "SELECT name, kaiyaku FROM t_customer WHERE no = " .$no;
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_name = char_chg("to_dsp", $f_rows["name"]);
		$c_kaiyaku = $f_rows["kaiyaku"];
	}

	mysql_free_result($result);

return array( $c_name, $c_kaiyaku );

}
//顧客情報取得 顧客番号からルート名
function f_customer_route($no)
{
	$c_route = "";
	$c_route_name = "";
	$sql = "SELECT route FROM t_customer WHERE no = " .$no;
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_route = $f_rows["route"];
	}

	mysql_free_result($result);

	$c_route_name = f_code_dsp(2, $c_route);

return $c_route_name;

}

//顧客情報取得 顧客番号から顧客住所
function f_customer_address_get($no)
{
	$c_address = "";
	$sql = "SELECT address FROM t_customer_info WHERE no = " .$no;
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_address = char_chg("to_dsp", $f_rows["address"]);
	}

	mysql_free_result($result);

return $c_address;

}

//顧客情報取得 指定住所が含まれるか否か
function f_customer_address_serch($no, $serch_address)
{
	$sql = "SELECT count(*) FROM t_customer_info WHERE no = " .$no." and address like '%".char_chg("to_db", $serch_address)."%'";
	$rnum1 = executeQuery($sql);
	list($num) = mysql_fetch_row($rnum1);

return $num;

}

function f_customer_address_get2($no)
{
	$c_address = "";
	$sql = "SELECT address, bldg FROM t_customer_info WHERE no = " .$no;
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_address = char_chg("to_dsp", $f_rows["address"]);
		if ($c_address != ""){
			$c_address .= " ".char_chg("to_dsp", $f_rows["bldg"]);
		}
	}

	mysql_free_result($result);

return $c_address;

}
//顧客情報取得 顧客番号から位置情報取得
function f_customer_latlng_get($no)
{
	$lat = "";
	$lng = "";
	$sql = "SELECT latitude, longitude FROM t_customer_info WHERE no = " .$no;
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$lat = $f_rows["latitude"];
		$lng = $f_rows["longitude"];
	}

	mysql_free_result($result);

	return array("lat" => $lat, "lng" => $lng);

}

//顧客情報取得　メモ数
function f_customer_memo_cnt_get($no)
{
	$c_cnt = "";
	$sql = "SELECT count(*) FROM t_customer_memo WHERE no = " .$no;
	$rnum1 = executeQuery($sql);
	list($num) = mysql_fetch_row($rnum1);

	mysql_free_result($rnum1);

return $num;
}

//顧客情報取得　メモ最終更新日
function f_customer_memo_date_get($no)
{
	$c_date = "";
	$sql = "SELECT create_date FROM t_customer_memo WHERE no = " .$no. " ORDER BY create_date DESC LIMIT 1";
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_date = $f_rows["create_date"];
	}

	mysql_free_result($result);

return $c_date;
}

//選択された顧客のプロジェクト名　リストボックス表示
function f_customer_prj_name_select($f_customer_no, $f_project_no)
{

	$sql = "SELECT s_customer_no, s_prj_code, s_prj_name FROM t_project WHERE s_customer_no = " .$f_customer_no;
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	$html = "<select name=\"s_prj_code\">\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if ($f_rows["s_prj_code"] == $f_project_no){
				$html .= "<option value=\"".$f_rows["s_prj_code"]."\" selected>".$f_rows["s_prj_name"]."</option>\n";
			}else{
				$html .= "<option value=\"".$f_rows["s_prj_code"]."\">".$f_rows["s_prj_name"]."</option>\n";
    			}
		}
	}

	$html .= "</select>\n";

	mysql_free_result($result);

return $html;

}

//顧客情報取得　登録プロジェクト数
function f_customer_project_cnt_get($no)
{
	$sql = "SELECT count(*) FROM t_project WHERE s_customer_no = '".$no."'";
	$rnum1 = executeQuery($sql);
	list($num) = mysql_fetch_row($rnum1);

	mysql_free_result($rnum1);

return $num;

}

//顧客情報取得　活動中録プロジェクト数
function f_customer_project_cnt_get2($no, $stat)
{
	$sql = "SELECT count(*) FROM t_project WHERE s_customer_no = '".$no."' and s_prj_status = '".$stat."'";
	$rnum1 = executeQuery($sql);
	list($num) = mysql_fetch_row($rnum1);

	mysql_free_result($rnum1);

return $num;

}

//顧客情報取得　winboardプロジェクト数
function f_customer_project_cnt_get3($no, $stat)
{
	$sql = "SELECT count(*) FROM t_project WHERE s_customer_no = '".$no."' and s_prj_status = '".$stat."' and (s_prj_kubun = '1' or s_prj_kubun = '2')";
	$rnum1 = executeQuery($sql);
	list($num) = mysql_fetch_row($rnum1);

	mysql_free_result($rnum1);

return $num;

}

//顧客情報取得 no→インフォメーション取得
function f_customer_info_get($no)
{
	$c_no = "";
	$c_name = "";
	$c_yyyy = "";
	$c_cnt = "";

	$sql = "SELECT no, name, yyyy, cnt FROM t_customer WHERE no = " .$no;
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_no = $f_rows["no"];
		$c_name = char_chg("to_dsp", $f_rows["name"]);
		$c_yyyy = $f_rows["yyyy"];
		$c_cnt = $f_rows["cnt"];
	}

	mysql_free_result($result);

return array( $c_no, $c_name, $c_yyyy, $c_cnt );

}

//顧客情報取得　指定ルート件数取得
function f_customer_route_cnt_get($yyyy, $no)
{
	if ($yyyy == 0){
		$sql = "SELECT count(*) FROM t_customer WHERE c_type > 5 and c_type < 98 and route = '".$no."' and name not like '%見込%'";
	}else{
		$sql = "SELECT count(*) FROM t_customer WHERE c_type > 5 and c_type < 98 and route = '".$no."' and yyyy = '".$yyyy."' and name not like '%見込%'";
	}

	$rnum1 = executeQuery($sql);
	list($num) = mysql_fetch_row($rnum1);

	mysql_free_result($rnum1);

return $num;

}

//プロジェクト情報取得　指定ルート件数取得
function f_project_route_cnt_get($yyyy, $no, $cd)
{
	if ($yyyy == 0){
		if ($cd == 0){
			$sql = "SELECT no FROM t_project left join t_customer on t_project.s_customer_no = t_customer.no WHERE t_customer.c_type > 5 and t_customer.c_type < 98 and t_customer.route = '".$no."' and (t_project.s_prj_kubun = 1 or t_project.s_prj_kubun = 2) and t_project.s_prj_name not like '%見込%' and t_customer.name not like '%見込%'";
		}else{
			$sql = "SELECT no FROM t_project left join t_customer on t_project.s_customer_no = t_customer.no WHERE t_customer.c_type > 5 and t_customer.c_type < 98 and t_customer.route = '".$no."' and t_project.s_prj_status = '".$cd."' and (t_project.s_prj_kubun = 1 or t_project.s_prj_kubun = 2) and t_project.s_prj_name not like '%見込%' and t_customer.name not like '%見込%'";
		}
	}else{
		if ($cd == 0){
			$sql = "SELECT no FROM t_project left join t_customer on t_project.s_customer_no = t_customer.no WHERE t_customer.c_type > 5 and t_customer.c_type < 98 and t_customer.route = '".$no."' and t_project.create_date LIKE '%".$yyyy."%' and (t_project.s_prj_kubun = 1 or t_project.s_prj_kubun = 2) and t_project.s_prj_name not like '%見込%' and t_customer.name not like '%見込%'";
		}else{
			$sql = "SELECT no FROM t_project left join t_customer on t_project.s_customer_no = t_customer.no WHERE t_customer.c_type > 5 and t_customer.c_type < 98 and t_customer.route = '".$no."' and t_project.s_prj_status = '".$cd."' and t_project.create_date LIKE '%".$yyyy."%' and (t_project.s_prj_kubun = 1 or t_project.s_prj_kubun = 2) and t_project.s_prj_name not like '%見込%' and t_customer.name not like '%見込%'";
		}
	}

	$same_no[] = array();

	$max = 0;
	$result = executeQuery($sql);
	$rows = mysql_num_rows($result);
	if($rows){
		while($row = mysql_fetch_array($result)) {
			$flag = 0;
			for ($ii = 0; $ii < $max; $ii++){
				if ($row["no"] == $same_no[$max]){
					$flag = 1;
					break;
				}
			}
			if ($flag == 0){
				$same_no[] = $row["no"];
				$max ++;
			}
		}
	}

	mysql_free_result($result);

return $max;

}

/*
function f_project_route_cnt_get($yyyy, $no, $cd)
{
	if ($yyyy == 0){
		if ($cd == 0){
			$sql = "SELECT count(*) FROM t_project left join t_customer on t_project.s_customer_no = t_customer.no WHERE t_customer.c_type > 5 and t_customer.c_type < 98 and t_customer.route = '".$no."'";
		}else{
			$sql = "SELECT count(*) FROM t_project left join t_customer on t_project.s_customer_no = t_customer.no WHERE t_customer.c_type > 5 and t_customer.c_type < 98 and t_customer.route = '".$no."' and t_project.s_prj_status = '".$cd."'";
		}
	}else{
		if ($cd == 0){
			$sql = "SELECT count(*) FROM t_project left join t_customer on t_project.s_customer_no = t_customer.no WHERE t_customer.c_type > 5 and t_customer.c_type < 98 and t_customer.route = '".$no."' and t_project.create_date LIKE '%".$yyyy."%'";
		}else{
			$sql = "SELECT count(*) FROM t_project left join t_customer on t_project.s_customer_no = t_customer.no WHERE t_customer.c_type > 5 and t_customer.c_type < 98 and t_customer.route = '".$no."' and t_project.s_prj_status = '".$cd."' and t_project.create_date LIKE '%".$yyyy."%'";
		}
	}
	$rnum1 = executeQuery($sql);
	list($num) = mysql_fetch_row($rnum1);

	mysql_free_result($rnum1);

return $num;

}
*/

?>
