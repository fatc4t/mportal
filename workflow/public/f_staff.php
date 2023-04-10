<?php

//スタッフ　リストボックス表示
function f_staff_select4($f_user_id)
{
	$flag = 0;

	$sql = "SELECT name, user_id, organization_id FROM m_user_detail ORDER BY organization_id, user_name";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "";
	$html .= "<select name=\"a_user_id\">\n";
	$html .= "<option value=\"\">全員</option>\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if ($f_rows["organization_id"] == 1 && $flag == 0){
				$html .= "<option value=\"0\">営業部</option>\n";
				$flag ++;
			}else if ($f_rows["organization_id"] == 2 && $flag == 1){
				$html .= "<option value=\"0\">開発部</option>\n";
				$flag ++;
			}else if ($f_rows["organization_id"] != 1 && $f_rows["organization_id"] != 2 && $flag == 2){
				$html .= "<option value=\"0\">その他</option>\n";
				$flag ++;
			}

			if ($f_rows["user_id"] == $f_user_id){
				$html .= "<option value=\"".$f_rows["user_id"]."\" selected>　└ ".char_chg("to_dsp", $f_rows["name"])."</option>\n";
			}else{
				$html .= "<option value=\"".$f_rows["user_id"]."\">　└ ".char_chg("to_dsp", $f_rows["name"])."</option>\n";
    			}
		
		}
		$html .= "</select>\n";

		return $html;
	}

	mysql_free_result($result);

return $html;

}

//スタッフ　リストボックス表示
function f_staff_select($f_user_id)
{
	$flag = 0;

	$sql = "SELECT name, user_id, organization_id FROM m_user_detail ORDER BY organization_id, user_name";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "";
	$html .= "<select name=\"user_id\">\n";
	$html .= "<option value=\"\">全員</option>\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if ($f_rows["organization_id"] == 1 && $flag == 0){
				$html .= "<option value=\"0\">営業部</option>\n";
				$flag ++;
			}else if ($f_rows["organization_id"] == 2 && $flag == 1){
				$html .= "<option value=\"0\">開発部</option>\n";
				$flag ++;
			}else if ($f_rows["organization_id"] != 1 && $f_rows["organization_id"] != 2 && $flag == 2){
				$html .= "<option value=\"0\">その他</option>\n";
				$flag ++;
			}

			if ($f_rows["user_id"] == $f_user_id){
				$html .= "<option value=\"".$f_rows["user_id"]."\" selected>　└ ".char_chg("to_dsp", $f_rows["name"])."</option>\n";
			}else{
				$html .= "<option value=\"".$f_rows["user_id"]."\">　└ ".char_chg("to_dsp", $f_rows["name"])."</option>\n";
    			}
		
		}
		$html .= "</select>\n";

		return $html;
	}

	mysql_free_result($result);

return $html;

}
//スタッフ　リストボックス表示
function f_staff_select2($f_user_id)
{

	$sql = "SELECT name, user_id, organization_id FROM m_user_detail ORDER BY user_name";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "";
	$html .= "<select name=\"s_pl_id[]\">\n";
	$html .= "<option value=\"\">----</option>\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if ($f_rows["organization_id"] == 2){
				if ($f_rows["user_id"] == $f_user_id){
					$html .= "<option value=\"".$f_rows["user_id"]."\" selected>".char_chg("to_dsp", $f_rows["name"])."</option>\n";
				}else{
					$html .= "<option value=\"".$f_rows["user_id"]."\">".char_chg("to_dsp", $f_rows["name"])."</option>\n";
	    			}
			}
		}
		$html .= "</select>\n";

		return $html;
	}

	mysql_free_result($result);

return $html;

}

//スタッフ　リストボックス表示
function f_staff_select3($f_user_id)
{

	$sql = "SELECT name, user_id, organization_id FROM m_user_detail ORDER BY user_name";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "";
	$html .= "<select name=\"pl_id\">\n";
	$html .= "<option value=\"\">----</option>\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if ($f_rows["organization_id"] == 2){
				if ($f_rows["user_id"] == $f_user_id){
					$html .= "<option value=\"".$f_rows["user_id"]."\" selected>".char_chg("to_dsp", $f_rows["name"])."</option>\n";
				}else{
					$html .= "<option value=\"".$f_rows["user_id"]."\">".char_chg("to_dsp", $f_rows["name"])."</option>\n";
	    			}
			}
		}
		$html .= "</select>\n";

		return $html;
	}

	mysql_free_result($result);

return $html;

}

//スタッフ（担当者）　リストボックス表示
function f_staff_select_name2($f_user_name)
{

	$sql = "SELECT name, user_id FROM m_user_detail ORDER BY user_name";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "";
	$html .= "<select name=\"houmonsha\">\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			$c_name = char_chg("to_dsp", $f_rows["name"]);
			if ($c_name == char_chg("to_dsp", $f_user_name)){
				$html .= "<option value=\"".$c_name."\" selected>".$c_name."</option>\n";
			}else{
				$html .= "<option value=\"".$c_name."\">".$c_name."</option>\n";
    			}
		}
		$html .= "</select>\n";

		return $html;
	}

	mysql_free_result($result);

return $html;

}

//営業スタッフ（担当者）　リストボックス表示
function f_staff_select_eigyou($f_user_name)
{
	$flag = 0;

	$sql = "SELECT name, user_id, organization_id FROM m_user_detail ORDER BY organization_id, user_name";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "";
	$html .= "<select name=\"houmonsha\">\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			$c_name = char_chg("to_dsp", $f_rows["name"]);
			if ($f_rows["organization_id"] == 1 && $flag == 0){
				$html .= "<option value=\"0\">営業部</option>\n";
				$flag ++;
			}else if ($f_rows["organization_id"] == 2 && $flag == 1){
				$html .= "<option value=\"0\">開発部</option>\n";
				$flag ++;
			}else if ($f_rows["organization_id"] != 1 && $f_rows["organization_id"] != 2 && $flag == 2){
				$html .= "<option value=\"0\">その他</option>\n";
				$flag ++;
			}

			if ($c_name == $f_user_name){
				$html .= "<option value=\"".$c_name."\" selected>　└ ".$c_name."</option>\n";
			}else{
				$html .= "<option value=\"".$c_name."\">　└ ".$c_name."</option>\n";
    			}
		
		}
		$html .= "</select>\n";

		return $html;
	}

	mysql_free_result($result);

return $html;




}

//スタッフ（担当者）　リストボックス表示
function f_staff_select_name3($f_user_name)
{

	$sql = "SELECT name, user_id FROM m_user_detail ORDER BY user_name";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "";
	$html .= "<select name=\"houmonsha2\">\n";
	$html .= "<option value=\"".$f_rows["name"]."\"> </option>\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			$c_name = char_chg("to_dsp", $f_rows["name"]);
			if ($c_name == char_chg("to_dsp", $f_user_name)){
				$html .= "<option value=\"".$c_name."\" selected>".$c_name."</option>\n";
			}else{
				$html .= "<option value=\"".$c_name."\">".$c_name."</option>\n";
    			}
		}
		$html .= "</select>\n";

		return $html;
	}

	mysql_free_result($result);

return $html;

}

//スタッフ（担当者）　リストボックス表示
function f_staff_select_name($f_user_name)
{

	$sql = "SELECT name, user_id FROM m_user_detail ORDER BY user_name";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "";
	$html .= "<select name=\"s_prj_tantou\">\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			$c_name = char_chg("to_dsp", $f_rows["name"]);
			if ($c_name == char_chg("to_dsp", $f_user_name)){
				$html .= "<option value=\"".$c_name."\" selected>".$c_name."</option>\n";
			}else{
				$html .= "<option value=\"".$c_name."\">".$c_name."</option>\n";
    			}
		}
		$html .= "</select>\n";

		return $html;
	}

	mysql_free_result($result);

return $html;

}
//スタッフ（担当者）　リストボックス表示
function f_staff_select_name_ff($name, $uid)
{

	$sql = "SELECT name, user_id FROM m_user_detail ORDER BY user_name";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "";
	$html .= "<select name=\"".char_chg("to_dsp", $name)."\">\n";
	$html .= "<option value=\"\">----</option>\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if ($f_rows["user_id"] == $uid){
				$html .= "<option value=\"".$f_rows["user_id"]."\" selected>".char_chg("to_dsp", $f_rows["name"])."</option>\n";
			}else{
				$html .= "<option value=\"".$f_rows["user_id"]."\">".char_chg("to_dsp", $f_rows["name"])."</option>\n";
    			}
		
		}
		$html .= "</select>\n";

		return $html;
	}

	mysql_free_result($result);

return $html;

}

//スタッフコードからログイン情報取得
function f_staff_login_get($f_user_id)
{
	$login_id = "";
	$password = "";
	$sql = "SELECT login_id, password FROM m_login WHERE user_id = '" .$f_user_id."'";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$login_id = $f_rows["login_id"];
		$password = $f_rows["password"];
	}

	mysql_free_result($result);

return array($login_id, $password);

}


//スタッフコードからスタッフ名取得
function f_staff_name_get($f_user_id)
{
	$c_name = "";
	$sql = "SELECT user_name FROM ".$_SESSION["SCHEMA"].".m_user_detail WHERE user_id = ".$f_user_id;
        $result = getList($sql);
	$f_rows = $result[0];
	if($f_rows){
		$c_name = $f_rows["user_name"];
		$sql_u = "SELECT is_del, leaving_date FROM ".$_SESSION["SCHEMA"].".m_user WHERE user_id = ".$f_user_id;
	        $result_u = getList($sql_u);
		$f_rows_u = $result_u[0];
		if($f_rows_u){
			if ($f_rows_u["is_del"] != 0){
				$c_name .= "&nbsp;<font color=red>[削除]</font>";
			}else{
				if ($f_rows_u["leaving_date"] != "" && $f_rows_u["leaving_date"] < date("Y/m/d")){
					$c_name .= "&nbsp;<font color=red>[退職]</font>";
				}
			}
		}
	}




return $c_name;

}

//スタッフコードから部署コード取得
function f_staff_busho_get($f_user_id)
{
	$c_busho = "";
	$sql = "SELECT organization_id FROM ".$_SESSION["SCHEMA"].".m_user_detail WHERE user_id = '" .$f_user_id."'";
        $result = getList($sql);
	$f_rows = $result[0];
	if($f_rows){
		$c_busho = $f_rows["organization_id"];
	}

return $c_busho;

}

//スタッフコードからメールアドレス取得
function f_staff_mail_get($f_user_id)
{
	$c_address = "";
	$sql = "SELECT mail_address FROM ".$_SESSION["SCHEMA"].".m_user_detail WHERE user_id = '" .$f_user_id."'";
        $result = getList($sql);
	$f_rows = $result[0];

	if($f_rows){
		$c_address = $f_rows["mail_address"];
	}

return $c_address;

}

//スタッフ名から売上集計対象フラグ取得
function f_staff_f1_get($f_user_name)
{
	$name = char_chg("to_db", $f_user_name);

	$c_id = "";
	$sql = "SELECT f1 FROM m_user_detail WHERE name = '" .$name."'";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_id = $f_rows["f1"];
	}

	mysql_free_result($result);

return $c_id;

}


//スタッフ名からランクコード取得
function f_staff_page_max_get($f_user_name)
{
	$name = char_chg("to_db", $f_user_name);

	$page_max = 10;
	$sql = "SELECT page_max FROM m_user_detail WHERE name = '" .$name."'";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$page_max = $f_rows["page_max"];
	}

	mysql_free_result($result);

return $page_max;

}
//スタッフ名からai機能のON/OFF取得
function f_staff_ai_get($f_user_name)
{
	$name = char_chg("to_db", $f_user_name);

	$ai = 0;
	$sql = "SELECT ai FROM m_user_detail WHERE name = '" .$name."'";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$ai = $f_rows["ai"];
	}

	mysql_free_result($result);

return $ai;

}
//スタッフ名から初期表示機能を取得
function f_staff_default_dsp_get($f_user_name)
{
	$name = char_chg("to_db", $f_user_name);

	$default_dsp = 0;
	$sql = "SELECT default_dsp FROM m_user_detail WHERE name = '" .$name."'";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$default_dsp = $f_rows["default_dsp"];
	}

	mysql_free_result($result);

return $default_dsp;

}

//スタッフ名からスタッフコード取得
function f_staff_id_get($f_user_name)
{
	$c_id = "";
	$sql = "SELECT user_id FROM ".$_SESSION["SCHEMA"].".m_user_detail WHERE user_name = '" .$f_user_name. "'";

        $result = getList($sql);
	$f_rows = $result[0];

	if($f_rows){
		$c_id = $f_rows["user_id"];
	}

return $c_id;

}

//スタッフ名で管理者チェック
function f_staff_admin_get($f_user_name)
{
	$name = char_chg("to_db", $f_user_name);
	$c_id = 0;
	$sql = "SELECT admin FROM m_user_detail WHERE name = '" .$name. "'";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_id = $f_rows["admin"];
	}

	mysql_free_result($result);

return $c_id;

}

//スタッフ名からgoogle連携情報取得
function f_staff_google_get($f_user_name)
{
	$c_google = 0;
	$c_google_id = "";
	$c_google_password = "";
	$c_mail_alert = 0;
	$c_mail_address = "";
	
	$sql = "SELECT google, google_id, google_password, mail_alert, mail_address FROM m_user_detail WHERE name = '" .char_chg("to_db", $f_user_name). "'";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_google = $f_rows["google"];
		$c_google_id = $f_rows["google_id"];
		$c_google_password = $f_rows["google_password"];
		$c_mail_alert = $f_rows["mail_alert"];
		$c_mail_address = $f_rows["mail_address"];
	}

	mysql_free_result($result);

return array( $c_google, $c_google_id, $c_google_password, $c_mail_alert, $c_mail_address );

}

//スタッフ名から緯度経度取得
function f_staff_latlng_get($f_user_name)
{
	$lat = "";
	$lng = "";
	$adr = "";

	$sql = "SELECT lat, lng, kinmuchi FROM m_user_detail WHERE name = '".char_chg("to_db", $f_user_name)."'";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$lat = $f_rows["lat"];
		$lng = $f_rows["lng"];
		$adr = char_chg("to_dsp", $f_rows["kinmuchi"]);
	}

	mysql_free_result($result);

	return array("lat" => $lat, "lng" => $lng, "adr" => $adr);

}

?>
