<?php

function f_zero_ten($no)
{
	$html = "";
	$html .= "<select id='add_no' name='add_no'>\n";

	for ($i = 0; $i <= 9; $i++){
		if ($no == $i){
			$html .= "<option value='".$i."' selected>".$i."</option>\n";
		}else{
			$html .= "<option value='".$i."'>".$i."</option>\n";
		}
	}
	$html .= "</select>\n";
	return $html;
}

function f_operation($no)
{
	$html = "";
	$html .= "<select id='operation' name='operation'>\n";

	switch ($no)
	{
		case 0:
		default:
			$html .= "<option value='0' selected>の下に追加</option>\n";
			$html .= "<option value='1'>に追加</option>\n";
			break;
		case 1:
			$html .= "<option value='0'>の下に追加</option>\n";
			$html .= "<option value='1' selected>に追加</option>\n";
			break;
	}

	$html .= "</select>\n";
	return $html;
}

//スタッフ名でスーパー管理者チェック
function f_staff_super_get($user_id)
{
	$c_id = 0;

	$sql = "SELECT security_id FROM ".$_SESSION["SCHEMA"].".m_user_detail WHERE user_id = ".$user_id;
        $result = getList($sql);
	$f_rows = $result[0];
	$c_id = $f_rows["security_id"];

return $c_id;

}

//$cd:0:承認依頼メール 1:可決メール 2:参照依頼メール 3:否決メール 5:差し戻しメール
function workflow_send_mail($cd, $mail_address, $target_staff, $workflow_id, $flow_doc_id)
{
	$schema = $_SESSION["SCHEMA"];

	$ret = 0;
	$body = "M-PORTALからのお知らせです。\n";
	$staff_name = f_staff_name_get($target_staff);
	if ($mail_address != ""){
		switch($cd)
		{
			case 0://承認依頼メール
				$body .= "以下の申請書の承認依頼が届いています。M-PORTALから承認してください！\n";
				$title = "[M-PORTAL] 申請書 (".f_code_dsp(99, $flow_doc_id).") の承認をお願いします";
				break;
			case 1://可決メール
				$body .= "以下の申請書が可決されました。M-PORTALから確認してください！\n";
				$title = "[M-PORTAL] 申請書 (".f_code_dsp(99, $flow_doc_id).") が可決されました";
				break;
			case 2://参照依頼メール
				$body .= "以下の申請書の参照依頼が届いています。M-PORTALから参照してください！\n";
				$title = "[M-PORTAL] 申請書 (".f_code_dsp(99, $flow_doc_id).") の確認をお願いします";
				break;
			case 3://否決メールを送る
				$body .= "以下の申請書が否決されました。M-PORTALから確認してください！\n";
				$title = "[M-PORTAL] 申請書 (".f_code_dsp(99, $flow_doc_id).") が否決されました";
				break;
			case 5://差し戻しメールを送る
				$body .= "以下の申請書が差し戻しされました。M-PORTALから確認してください！\n";
				$title = "[M-PORTAL] 申請書 (".f_code_dsp(99, $flow_doc_id).") が差し戻しされました";
				break;
		}

		$body .= "\n";
		$body .= "申請番号：".$workflow_id."\n";
		$body .= "申請者：".$staff_name."\n";
		$body .= "\n";
		$body .= "申請文書：".f_code_dsp(99, $flow_doc_id)."\n";
		$body .= "\n";
		$body .= "https://www.mportal.jp/top/".$schema."/index.php\n";
		$body .= "\n";
		$body .= "このメールには返信しないでください。\n";

		$ret = send_mail($mail_address, $title, $body);
	}

	return $ret;
}


function workflow_cc_ok($f_row, $staff_code)
{
	$ok_f = 0;
	$ok_j = 0;

	for ($j = 1; $j <= 10; $j++){	//承認者
		for ($n = 1; $n <= 5; $n++){	//CC
			if($f_row["next".$j."_cc".$n."_data"] == $staff_code."-2")
			{
				$ok_j = $j;
				goto jump;
			}
		}
	}

jump:
	if ($ok_j != 0){
		$str = $f_row["next".$ok_j."_data"];
		if ($str != "*0-0"){
			$apply_cnt = 0;
			$staff_cnt = 0;

			$key1 = explode("=", $str);
			$apply_cnt = $key1[1];	//承認人数


			$key2 = explode("/", $key1[0]);

			$staff_cnt = count($key2);
			
			$apply_staff = 0;
			for ($i = 0; $i < $staff_cnt; $i++){
				$key3 = explode("-", $key2[$i]);
				if ($key3[1] == 3){
					$apply_staff ++;
				}
			}

			//承認人数に達したか？
			if ($apply_staff >= $apply_cnt){
				$ok_f = 1;
			}
		}
	}

	return $ok_f;
}

function workflow_agree_all($str)
{
	$apply_cnt = 0;
	$staff_cnt = 0;
	$f = 0;

	$key1 = explode("=", $str);
	$apply_cnt = $key1[1];	//承認人数


	$key2 = explode("/", $key1[0]);

	$staff_cnt = count($key2);
	$apply_staff = 0;
	$staff_all = 0;
	$remand_f = 0;

	for ($i = 0; $i < $staff_cnt; $i++){
		$key3 = explode("-", $key2[$i]);
		if ($key3[1] == 3){
			$apply_staff ++;
		}
		if ($key3[1] == 3 || $key3[1] == 4 || $key3[1] == 5){
			$staff_all ++;
		}
		if ($key3[1] == 5){
			$remand_f = 1;
		}
	}

	//承認人数に達したか？
	if ($apply_staff >= $apply_cnt){
		$f = 1;	//達したから終了
	}else{
		if ($staff_cnt == $staff_all){
			if ($remand_f == 0){
				$f = 2;	//達しなかったので終了　否決
			}else{
				$f = 3;	//達しなかったので終了　差し戻し
			}
		}
	}

	return $f;
}

function workflow_next_agree_update($str, $f)
{
	$apply_cnt = 0;
	$staff_cnt = 0;
	$up_str = "";

	$key1 = explode("=", $str);
	$apply_cnt = $key1[1];	//承認人数


	$key2 = explode("/", $key1[0]);

	$staff_cnt = count($key2);

	for ($i = 0; $i < $staff_cnt; $i++){
		$key3 = explode("-", $key2[$i]);
		if ($i != 0){
			$up_str .= "/";
		}
		$up_str .= $key3[0]."-".$f;
	}

	$up_str .= "=".$apply_cnt;

	return $up_str;
}
//指定ルートの指定した人を入れ替える
function workflow_root_change($str, $staff_code1, $staff_code2)
{
	$apply_cnt = 0;
	$staff_cnt = 0;
	$staff_no = 0;
	$up_str = "";

	$key1 = explode("=", $str);
	$apply_cnt = $key1[1];	//承認人数


	$key2 = explode("/", $key1[0]);

	$staff_cnt = count($key2);

	for ($i = 0; $i < $staff_cnt; $i++){
		$key3 = explode("-", $key2[$i]);
		if ($i != 0){
			$up_str .= "/";
		}
		if ($staff_no == 0 && "*".$staff_code1 == $key3[0]){
			$staff_no = $i+1;
			$up_str .= "*".$staff_code2."-".$key3[1];
		}else{
			$up_str .= $key3[0]."-".$key3[1];
		}
	}

	$up_str .= "=".$apply_cnt;

	return $up_str;
}


//ルート・人全員のステータスを変更
function workflow_agree_update_all($str, $f)
{
	$apply_cnt = 0;
	$staff_cnt = 0;
	$up_str = "";

	$key1 = explode("=", $str);
	$apply_cnt = $key1[1];	//承認人数


	$key2 = explode("/", $key1[0]);

	$staff_cnt = count($key2);

	for ($i = 0; $i < $staff_cnt; $i++){
		$key3 = explode("-", $key2[$i]);
		if ($i != 0){
			$up_str .= "/";
		}
		$up_str .= $key3[0]."-".$f;
	}

	$up_str .= "=".$apply_cnt;

	return $up_str;
}

//指定のルート・人のステータスのみを変更
function workflow_agree_update($str, $staff_code, $f)
{
	$apply_cnt = 0;
	$staff_cnt = 0;
	$staff_no = 0;
	$up_str = "";

	$key1 = explode("=", $str);
	$apply_cnt = $key1[1];	//承認人数


	$key2 = explode("/", $key1[0]);

	$staff_cnt = count($key2);

	for ($i = 0; $i < $staff_cnt; $i++){
		$key3 = explode("-", $key2[$i]);
		if ($i != 0){
			$up_str .= "/";
		}
		if ($staff_no == 0 && $staff_code == $key3[0]){
			$staff_no = $i+1;
			$up_str .= $key3[0]."-".$f;
		}else{
			$up_str .= $key3[0]."-".$key3[1];
		}
	}

	$up_str .= "=".$apply_cnt;

	return array($staff_no, $up_str);
}

//起案者CCの状況を判定
function workflow_kian_cc($str)
{

	$key1 = explode("-", $str);
	$stat = $key1[1];
	return $stat;
}
//起案者CCの状況を判定
function workflow_kian_cc_staff($str)
{

	$key1 = explode("-", $str);
	$stat = $key1[1];

	return $key1[0];
}

//ルート表示用に人数と名前の配列、承認人数を返す
function workflow_root_dsp_split2($str)
{
	$apply_cnt = 0;
	$staff_cnt = 0;

	$key1 = explode("=", $str);
	$apply_cnt = $key1[1];	//承認人数


	$key2 = explode("/", $key1[0]);

	$staff_cnt = count($key2);

	for ($i = 0; $i < $staff_cnt; $i++){
		$key3 = explode("-", $key2[$i]);
		$name[] = $key3[0];
		$target[] = $key3[1];
	}

	return array($staff_cnt, $name, $target, $apply_cnt);
}

function workflow_root_dsp_split($str)
{
	$apply_cnt = 0;
	$staff_cnt = 0;

	$key1 = explode("=", $str);
	$apply_cnt = $key1[1];	//承認人数


	$key2 = explode("/", $key1[0]);

	$staff_cnt = count($key2);

	for ($i = 0; $i < $staff_cnt; $i++){
		$key3 = explode("-", $key2[$i]);
		$name[] = $key3[0];
	}

	return array($staff_cnt, $name, $apply_cnt);
}

function workflow_root_split($str)
{

	$str_root = "";

	if ($str != "*0-0"){
		$key1 = explode("-", $str);

	//$key1[1];	-1の部分

		$key2 = explode("=", $key1[0]);	//$key2[0]スタッフコード $key2[1]多数決数
		//$key2[1]	//多数決数

		$key3 = explode("/", $key2[0]);	//スタッフを分割

		$staff_cnt = 0;
		$staff_cnt = count($key3);

		for ($i = 0; $i < $staff_cnt; $i++){
			if ($i != 0){
				$str_root .= "/";
			}
			$str_root .= $key3[$i]."-".$key1[1];
		}

		$str_root .= "=".$key2[1];
	}else{
		$str_root = $str;
	}

	return $str_root;
}

//指定のルートに加える
function workflow_add_route($add_no, $operation, $str, $staff_code, $tasuuketsu)
{
	$apply_cnt = 0;
	$staff_cnt = 0;
	$up_str = "";

	if ($add_no == 1){
		$f = 2;
	}else{
		$f = 1;
	}

	if ($str == "*0-0"){
		$up_str = "*".$staff_code."-".$f."=1";
	}else{

		$key1 = explode("=", $str);
		$apply_cnt = $key1[1];	//承認人数
		if ($tasuuketsu != 0){
			$apply_cnt = $tasuuketsu;
		}

		$key2 = explode("/", $key1[0]);

		$staff_cnt = count($key2);

		for ($i = 0; $i < $staff_cnt; $i++){
			$key3 = explode("-", $key2[$i]);
			if ($i != 0){
				$up_str .= "/";
			}
			$up_str .= $key3[0]."-".$f;
		}
		$up_str .= "/"."*".$staff_code."-".$f;
		$up_str .= "=".$apply_cnt;
	}

	return $up_str;
}

function mb_pathinfo($str){

	$res = substr($str, strrpos($str, '.') + 1);

	return $res;
}

function mb_basename($str, $suffix=null){
    $tmp = preg_split('/[\/\\\\]/', $str);
    $res = end($tmp);
    if(strlen($suffix)){
        $suffix = preg_quote($suffix);
        $res = preg_replace("/({$suffix})$/u", "", $res);
    }
    return $res;
}
//文字列変換
//$chg:"to_db":DBへ書き込む時, "to_dsp":画面に表示する時
//$cr:変換する文字列
function char_chg($chg, $cr)
{

return $cr;
	$cr_after = "";

//	mb_language("Japanese");

	switch ($chg)
	{
		case "to_db":
$cr_after = mb_convert_encoding($cr,"UTF-8",mb_detect_encoding($cr, "ASCII,SJIS,UTF-8,CP51932,SJIS-win"));
			$cr_after = mb_convert_encoding($cr,"UTF-8","ASCII"); 
			break;
		case "to_dsp":
$cr_after = $cr;
//			$cr_after = mb_convert_encoding($cr,"ASCII","UTF-8"); 
			break;
		case "to_utf":
			$cr_after = mb_convert_encoding($cr,"UTF-8","UTF-8"); 
			break;
		default:
			$cr_after = "no change"; 
			break;
	}

/*
	$cr_after = "";

	switch ($chg)
	{
		case "to_db":
			$cr_after = mb_convert_encoding($cr,"UTF-8","SJIS"); 
			break;
		case "to_dsp":
			$cr_after = mb_convert_encoding($cr,"SJIS","UTF-8"); 
			break;
		case "to_utf":
			$cr_after = mb_convert_encoding($cr,"UTF-8","UTF-8"); 
			break;
		default:
			$cr_after = "no change"; 
			break;
	}
*/
	return $cr_after;
}


//DB操作

function db_select($item, $table, $where, $other){

	$sql = "SELECT ".$item." FROM ".$table." WHERE ".$where.$other;

	return $sql;
}






//

function makeRandStrA9($length) {
    $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
    $r_str = null;
    for ($i = 0; $i < $length; $i++) {
        $r_str .= $str[rand(0, count($str) - 1)];
    }
    return $r_str;
}

function makeRandStr9($length) {
    $str = array_merge(range('0', '9'));
    $r_str = null;
    for ($i = 0; $i < $length; $i++) {
        $r_str .= $str[rand(0, count($str) - 1)];
    }
    return $r_str;
}

function running_shop_id_num($yyyy, $mm, $s_prj_code){

	$shop_num = 0;
	$id_num = 0;

	$sql = "SELECT shop_num, id_num FROM t_running_log WHERE s_prj_code = '".$s_prj_code."' and yyyy = '".$yyyy."' and mm = '".$mm."'";
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);
	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$shop_num = $f_rows["shop_num"];
		$id_num = $f_rows["id_num"];
	}

	return array($shop_num, $id_num);
}


function menu_bar($refer, $self, $title, $bookmark_title, $arg, $code_mst){

$menu = "none";
$my_menu = "";
$refer_menu = "";
$bookmark_name = "";
$bookmark_url = "";

	$sql = "SELECT * FROM t_function WHERE self = '".$self."'";
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);
	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$my_menu = char_chg("to_dsp", $f_rows["menu_name"]);
		$bookmark_name = char_chg("to_dsp", $f_rows["bookmark_name"]);
		$bookmark_url = char_chg("to_dsp", $f_rows["bookmark_url"]);
		if ($arg != ""){
			$bookmark_url .= $arg;
		}
	}
	mysql_free_result($result);

if ($title != ""){
	$my_menu = $title;
}

if ($bookmark_title != ""){
	$bookmark_name = $bookmark_title;
}

//コードマスタ
if ($code_mst != 0){

	switch ($code_mst)
	{
		case 1:
			$my_menu = "アクションマスタ";
			break;
		case 2:
			$my_menu = "ルートマスタ";
			break;
		case 3:
			$my_menu = "区分マスタ";
			break;
		case 4:
			$my_menu = "問合せマスタ";
			break;
		case 5:
			$my_menu = "業種マスタ";
			break;
		case 6:
			$my_menu = "ランクマスタ";
			break;
		case 7:
			$my_menu = "ランク係数";
			break;
		case 8:
			$my_menu = "プロジェクトタイプ(開発)";
			break;
		case 9:
			$my_menu = "プロジェクトタイプ(営業)";
			break;
		case 10:
			$my_menu = "開発部セクション";
			break;
		case 11:
			$my_menu = "営業フロー";
			break;
		case 12:
			$my_menu = "拠点";
			break;
		case 13:
			$my_menu = "組織マスタ";
			break;
		case 99:
			$my_menu = "申請文書名";
			break;
		case 101:
			$my_menu = "機種マスタ";
			break;
		case 300:
			$my_menu = "履歴";
			break;
		case 301:
			$my_menu = "本部受付";
			break;
		case 302:
			$my_menu = "発送処理";
			break;
		case 303:
			$my_menu = "検品待ち";
			break;
		case 401:
			$my_menu = "請求処理";
			break;
		case 402:
			$my_menu = "入金処理";
			break;
		case 403:
			$my_menu = "インセンティブ";
			break;
		case 404:
			$my_menu = "コミッション";
			break;
		default:
			$my_menu = "none";
			break;
	}

}

/*
	$sql = "SELECT menu_name FROM t_function WHERE self = '".$refer."'";
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);
	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$refer_menu = char_chg("to_dsp", $f_rows["menu_name"]);
	}
	mysql_free_result($result);

	$menu = "<div class=\"menuNameArea\"><h2><a href=\"../menu.php\"><span class=\"menu\" /></span></a><a href=\"#\">".$refer_menu."</a> > <a href=\"#\">".$my_menu."</a></h2></div>\n";
*/
	$menu = "<div class=\"menuNameArea\"><h2><a href=\"../menu.php\"><span class=\"menu\" /></span></a><a href=\"#\">".$my_menu."</a></h2></div>\n";

return array($menu, $bookmark_name, $bookmark_url);
}

function pageListCreate($lineNum, $name, $sql_page, $page, $url){

        $rows = getList($sql_page);

	if ($rows != 0){
		$page_cnt = ceil(count($rows)/$lineNum);
	}else{
		$page_cnt = 0;
	}
	if ($page_cnt > 5){
		$max = 5;
	}else{
		$max = $page_cnt;
	}

	if (($page-1) > 1){
		if (($page+5) <= $page_cnt){
			$start_page = $page-1;
		}else{
			$start_page = $page_cnt-5;
		}
		if ($start_page < 0){
			$start_page = 1;
		}
	}else{
		$start_page = 1;
	}

	if ($start_page == 0){
		$start_page = 1;
	}

	$pageList = "<!-- PageMoveArea -->\n";
	$pageList .= "<div class=\"PageMoveArea\">\n";
	$pageList .= "<table>\n";
	$pageList .= "<tr>\n";
	$pageList .= "<td><a href=\"".$url."&".$name."=1\"><<</a></td>\n";
	if ($page > 1){
		$pageList .= "<td><a href=\"".$url."&".$name."=".($page-1)."\"><</a></td>\n";
	}else{
		$pageList .= "<td><a href=\"#\"><</a></td>\n";
	}


	for ($i = $start_page; $i <= ($max+$start_page-1); $i++){
		if ($i <= $page_cnt){
			if ($page == $i){
				$pageList .= "<td><a href=\"".$url."&".$name."=".$i."\" class=\"current\">".$i."</a></td>\n";
			}else{
				$pageList .= "<td><a href=\"".$url."&".$name."=".$i."\">".$i."</a></td>\n";
			}
		}
	}
	if ($page_cnt > 5 && ($i-1) < $page_cnt){
		$pageList .= "<td>...</td>\n";
		if ($page_cnt == $page){
			$pageList .= "<td><a href=\"".$url."&".$name."=".$page_cnt."\" class=\"current\">".$page_cnt."</a></td>\n";
		}else{
			$pageList .= "<td><a href=\"".$url."&".$name."=".$page_cnt."\">".$page_cnt."</a></td>\n";
		}
	}
	if ($page < $page_cnt){
		$pageList .= "<td><a href=\"".$url."&".$name."=".($page+1)."\">></a></td>\n";
	}else{
		$pageList .= "<td><a href=\"#\">></a></td>\n";
	}
	$pageList .= "<td><a href=\"".$url."&".$name."=".$page_cnt."\">>></a></td>\n";
	$pageList .= "</tr>\n";
	$pageList .= "</table>\n";
	$pageList .= "</div><!-- /.PageMoveArea -->\n";


	return($pageList); 

}

function tran_begin()
{

	$url = "127.0.0.1";
	$user = "root";
	$pass = "kimi5869";
	$db = "sales_mng";

	// MySQLへ接続する
	$link = mysql_connect($url,$user,$pass) or die("MySQLへの接続に失敗しました。");

	// データベースを選択する
	$sdb = mysql_select_db($db,$link) or die("データベースの選択に失敗しました。");

	//トランザクションをはじめる準備
	$sql = "set autocommit = 0";

	mysql_set_charset('utf8');

	mysql_query( $sql, $link ) or die("クエリの送信に失敗しました。<br />SQL:".$sql);

	//トランザクション開始
	$sql = "begin";
	mysql_query( $sql, $link ) or die("クエリの送信に失敗しました。<br />SQL:".$sql);

	return($link);

}
function executeQuery($sql)
{
	$url = "127.0.0.1";
	$user = "root";
	$pass = "kimi5869";
	$db = "sales_mng";

	// MySQLへ接続する
	$link = mysql_connect($url,$user,$pass) or die("MySQLへの接続に失敗しました。");

	// データベースを選択する
	$sdb = mysql_select_db($db,$link) or die("データベースの選択に失敗しました。");

	// クエリを送信する
	$result = mysql_query($sql, $link) or die("クエリの送信に失敗しました。<br />SQL:".$sql."<br />ERR:".mysql_error());

	// MySQLへの接続を閉じる
//	mysql_close($link) or die("MySQL切断に失敗しました。");

	//戻り値
	return($result);
}

function executeQuery_insert($sql)
{
	$url = "127.0.0.1";
	$user = "root";
	$pass = "kimi5869";
	$db = "sales_mng";

	// MySQLへ接続する
	$link = mysql_connect($url,$user,$pass) or die("MySQLへの接続に失敗しました。");

	// データベースを選択する
	$sdb = mysql_select_db($db,$link) or die("データベースの選択に失敗しました。");

	// クエリを送信する
	$result = mysql_query($sql, $link) or die("クエリの送信に失敗しました。<br />SQL:".$sql."<br />ERR:".mysql_error());

	$last_id = mysql_insert_id();

	//戻り値
	return($last_id);
}

function last_day_get($yyyymm)
{
$lastDate = date('d', strtotime("last day of ".$yyyymm));
return($lastDate);
}

function menu_partner_top($home, $login_staff, $bookmark_title, $bookmark_url, $bookmark_f, $bookmark_disp)
{
	list($org_name, $partner_name) = f_order_partner_name_get($login_staff);

	$menu_top = "<div class=\"container\">\n";
	$menu_top .= "<!-- Logo -->\n";
	$menu_top .= "<div id=\"logo\" class=\"navbar-left\">\n";
	$menu_top .= "<a href=\"/mportal/home/top.php\"><img src=\"/mportal/img/top-menu-logo.png\" alt=\"HOME\" height=\"40\"></a>\n";
	$menu_top .= "</div><!-- /#logo -->\n";
				
	$menu_top .= "<!-- Menu -->\n";
	$menu_top .= "<ul class=\"nav navbar-nav navbar-right\">\n";

	$menu_top .= "<li><a href=\"https://www.mportal.jp/home/index.php?param=Home/show&home=1\">ホーム</a></li>\n";
	$menu_top .= "<li><a>".$partner_name."</a></li>\n";
	$menu_top .= "<li><a href=\"/mportal/ordering/partner/login.php\">ログアウト</a></li>\n";


	$menu_top .= "<li><a id=\"top-arrow\" href=\"#top\">＾</a></li>\n";

	$menu_top .= "</ul><!-- Menu -->\n";
	$menu_top .= "</div>\n";

	return $menu_top;
}

function menu_top($home, $login_staff, $bookmark_title, $bookmark_url, $bookmark_f, $bookmark_disp)
{

	$menu_top = "<div class=\"container\">\n";
	$menu_top .= "<!-- Logo -->\n";
	$menu_top .= "<div id=\"logo\" class=\"navbar-left\">\n";
	$menu_top .= "<a href=\"/mportal/home/top.php\"><img src=\"/mportal/img/top-menu-logo.png\" alt=\"HOME\" height=\"40\"></a>\n";
	$menu_top .= "</div><!-- /#logo -->\n";
				
	$menu_top .= "<!-- Menu -->\n";
	$menu_top .= "<ul class=\"nav navbar-nav navbar-right\">\n";

	if ($home == 0){
		if ($bookmark_f == 1){
			if ($bookmark_disp == 1){
				$menu_top .= "<li><a style='cursor:pointer' id=\"button_bookmark\" onclick=\"bookmark();\">ﾌﾞｯｸﾏｰｸ削除</a></li>\n";
			}else{
				$menu_top .= "<li><a style='cursor:pointer' id=\"button_bookmark\" onclick=\"bookmark();\">ﾌﾞｯｸﾏｰｸ登録</a></li>\n";
			}
		}
		$menu_top .= "<input id=\"login_staff\" type=\"hidden\" value=\"".$login_staff."\">";
		$menu_top .= "<input id=\"bookmark_title\" type=\"hidden\" value=\"".$bookmark_title."\">";
		$menu_top .= "<input id=\"bookmark_url\" type=\"hidden\" value=\"".$bookmark_url."\">";
	}
	$menu_top .= "<li><a href=\"https://www.mportal.jp/home/index.php?param=Home/show&home=1\">ホーム</a></li>\n";
	$admin = f_staff_admin_get($login_staff);		//管理者のみ
	if ($admin == 1){
		$menu_top .= "<li><a href=\"/mportal/sales/master/control.php?home=".$home."\">管理者</a></li>\n";
	}
	$menu_top .= "<li><a href=\"/mportal/sales/kojin/staff.php?no=0000&home=".$home."\">".$login_staff."</a></li>\n";
	$menu_top .= "<li><a href=\"/mportal/sales/index.php\">ログアウト</a></li>\n";
	$menu_top .= "<li><a id=\"top-arrow\" href=\"#top\">＾</a></li>\n";
	$menu_top .= "</ul><!-- Menu -->\n";
	$menu_top .= "</div>\n";

	return $menu_top;
}

function menu_side_left($home, $login_staff)
{

$user_id = f_staff_id_get($login_staff);

if ($home == 0){
	$menu_side_left = "<!-- sb-left -->\n";
	$menu_side_left .= "<div class=\"sb-slidebar sb-left\">\n";
	$menu_side_left .= "<nav>\n";
	$menu_side_left .= "<ul class=\"sb-menu\">\n";
	$menu_side_left .= "<li><img src=\"/mportal/img/logo-sales.png\" alt=\"M-PORTAL\" height=\"40\"></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/menu.php\">トップページ</a></li>\n";

	$menu_side_left .= "<div onclick=\"drop_menu4();\">\n";
	$menu_side_left .= "<li class=\"sb-close_menu\"><a>営業</a></li>\n";
	$menu_side_left .= "</div>\n";
	$menu_side_left .= "<div id=\"drop_menu4\" style=\"display:none\">\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/customer/customer.php?no=0000\">&nbsp;&nbsp;&nbsp;顧客リスト</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/project/project_list.php?no=0000\">&nbsp;&nbsp;&nbsp;プロジェクトリスト</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/management/all_buget_month.php?cd=0\">&nbsp;&nbsp;&nbsp;予実管理</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/budget/sales.php?no=0000\">&nbsp;&nbsp;&nbsp;予算作成</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/report/scl_sales_report.php?no=0000\">&nbsp;&nbsp;&nbsp;営業活動報告</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/time/log-sales.php\">&nbsp;&nbsp;&nbsp;GPS行動管理</a></li>\n";
	$menu_side_left .= "</div>\n";


	$menu_side_left .= "<div onclick=\"drop_menu8();\">\n";
	$menu_side_left .= "<li class=\"sb-close_menu\"><a>管理部</a></li>\n";
	$menu_side_left .= "</div>\n";
	$menu_side_left .= "<div id=\"drop_menu8\" style=\"display:none\">\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/project/running_list.php?cd=0\">&nbsp;&nbsp;&nbsp;請求処理</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/project/running_list.php?cd=2\">&nbsp;&nbsp;&nbsp;入金処理</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/project/running_list.php?cd=5\">&nbsp;&nbsp;&nbsp;インセンティブ</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/project/running_list.php?cd=7\">&nbsp;&nbsp;&nbsp;コミッション</a></li>\n";
	$menu_side_left .= "</div>\n";

	$menu_side_left .= "<div onclick=\"drop_menu1();\">\n";
	$menu_side_left .= "<li class=\"sb-close_menu\"><a>業務報告</a></li>\n";
	$menu_side_left .= "</div>\n";
	$menu_side_left .= "<div id=\"drop_menu1\" style=\"display:none\">\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/report/report.php?report_id=0&user_id=".$user_id."\">&nbsp;&nbsp;&nbsp;入力</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/report/report-view.php\">&nbsp;&nbsp;&nbsp;閲覧</a></li>\n";
	$menu_side_left .= "</div>\n";

	$menu_side_left .= "<div onclick=\"drop_menu3();\">\n";
	$menu_side_left .= "<li class=\"sb-close_menu\"><a>日報・月報</a></li>\n";
	$menu_side_left .= "</div>\n";
	$menu_side_left .= "<div id=\"drop_menu3\" style=\"display:none\">\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/report/sales_report.php?no=0000\">&nbsp;&nbsp;&nbsp;日報</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/report/sales_report_mon.php?no=0000\">&nbsp;&nbsp;&nbsp;月報</a></li>\n";
	$menu_side_left .= "</div>\n";

	$menu_side_left .= "<div onclick=\"drop_menu2();\">\n";
	$menu_side_left .= "<li class=\"sb-close_menu\"><a>ワークフロー【簡易版】</a></li>\n";
	$menu_side_left .= "</div>\n";
	$menu_side_left .= "<div id=\"drop_menu2\" style=\"display:none\">\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/workflow/apply.php\">&nbsp;&nbsp;&nbsp;申請</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/workflow/apply_agree.php\">&nbsp;&nbsp;&nbsp;承認・参照</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/workflow/apply_view.php\">&nbsp;&nbsp;&nbsp;一覧</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/workflow/apply_check.php\">&nbsp;&nbsp;&nbsp;確認</a></li>\n";
	$menu_side_left .= "</div>\n";

	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/budget/nikutama_report.php?no=0000\">にくたま</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/kojin/staff.php?no=0000\">個人設定</a></li>\n";


	$menu_side_left .= "<div onclick=\"drop_menu5();\">\n";
	$menu_side_left .= "<li class=\"sb-close_menu\"><a>開発</a></li>\n";
	$menu_side_left .= "</div>\n";
	$menu_side_left .= "<div id=\"drop_menu5\" style=\"display:none\">\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/dev/partner.php\">&nbsp;&nbsp;&nbsp;パートナー管理</a></li>\n";
	$menu_side_left .= "</div>\n";



	$menu_side_left .= "<div onclick=\"drop_menu6();\">\n";
	$menu_side_left .= "<li class=\"sb-close_menu\"><a>グループウェア</a></li>\n";
	$menu_side_left .= "</div>\n";
	$menu_side_left .= "<div id=\"drop_menu6\" style=\"display:none\">\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/checklist/check_list.php\">&nbsp;&nbsp;&nbsp;ToDo</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/calendar/calendar.php?no=0000\">&nbsp;&nbsp;&nbsp;個人スケジュール</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/kojin/schedule.php#today\">&nbsp;&nbsp;&nbsp;営業部スケジュール</a></li>\n";

	$menu_side_left .= "	<div onclick=\"drop_menu9();\">\n";
	$menu_side_left .= "		<li class=\"sb-close_menu\"><a>&nbsp;&nbsp;&nbsp;資料室</a></li>\n";
	$menu_side_left .= "	</div>\n";
	$menu_side_left .= "	<div id=\"drop_menu9\" style=\"display:none\">\n";
	$menu_side_left .= "		<li class=\"sb-close\"><a href=\"/mportal/sales/filebox/library.php?no=0000\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;閲覧</a></li>\n";
	$menu_side_left .= "		<li class=\"sb-close\"><a href=\"/mportal/file_manage/\" target=\"_blank\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;編集</a></li>\n";
	$menu_side_left .= "	</div>\n";
/*
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/templete/library.php?no=0000\">&nbsp;&nbsp;&nbsp;資料室</a></li>\n";
*/

	$menu_side_left .= "</div>\n";

	$admin = f_staff_admin_get($login_staff);		//管理者のみ
	if ($admin == 1)
	{
		$menu_side_left .= "<div onclick=\"drop_menu7();\">\n";
		$menu_side_left .= "<li class=\"sb-close_menu\"><a>メンテナンス</a></li>\n";
		$menu_side_left .= "</div>\n";
		$menu_side_left .= "<div id=\"drop_menu7\" style=\"display:none\">\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/staff.php\">&nbsp;&nbsp;&nbsp;アカウントマスタ</a></li>\n";



		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/code.php?cd=3\">&nbsp;&nbsp;&nbsp;区分マスタ</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/report_kubun.php\">&nbsp;&nbsp;&nbsp;業務区分マスタ</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/code.php?cd=8\">&nbsp;&nbsp;&nbsp;プロジェクトタイプ(開発)</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/log.php\">&nbsp;&nbsp;&nbsp;アクセスログ</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/code.php?cd=1\">&nbsp;&nbsp;&nbsp;アクションマスタ</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/code.php?cd=9\">&nbsp;&nbsp;&nbsp;プロジェクトタイプ(営業)</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/code.php?cd=4\">&nbsp;&nbsp;&nbsp;問合せマスタ</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/code.php?cd=10\">&nbsp;&nbsp;&nbsp;開発部セクション</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/code.php?cd=5\">&nbsp;&nbsp;&nbsp;業種マスタ</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/code.php?cd=12\">&nbsp;&nbsp;&nbsp;拠点</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/org.php?cd=1\">&nbsp;&nbsp;&nbsp;組織マスタ</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/code.php?cd=11\">&nbsp;&nbsp;&nbsp;営業フロー</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/code.php?cd=6\">&nbsp;&nbsp;&nbsp;ランクマスタ</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/function.php\">&nbsp;&nbsp;&nbsp;機能マスタ</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/customer_export.php\">&nbsp;&nbsp;&nbsp;顧客情報出力</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/code.php?cd=2\">&nbsp;&nbsp;&nbsp;ルートマスタ</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/inport/inport_workflow.php\">&nbsp;&nbsp;&nbsp;申請ルート取込</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/inport/inport_workflow_doc_format.php\">&nbsp;&nbsp;&nbsp;申請書レイアウト取込</a></li>\n";

		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/code.php?cd=99\">&nbsp;&nbsp;&nbsp;申請文書名</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/master/code.php?cd=7\">&nbsp;&nbsp;&nbsp;ランク係数</a></li>\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/sales/inport/upload_customer.php\">&nbsp;&nbsp;&nbsp;顧客情報の取込</a></li>\n";
	}



	$menu_side_left .= "</div>\n";



	$menu_side_left .= "</ul>\n";
	$menu_side_left .= "</nav>\n";
	$menu_side_left .= "</div><!-- sb-left -->\n";
}else{
	$menu_side_left = "<!-- sb-left -->\n";
	$menu_side_left .= "<div class=\"sb-slidebar sb-left\">\n";
	$menu_side_left .= "<nav>\n";
	$menu_side_left .= "<ul class=\"sb-menu\">\n";
	$menu_side_left .= "<li><img src=\"/mportal/img/right-menu-logo.png\" alt=\"M-PORTAL\" height=\"40\"></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/home/top.php\">トップページ</a></li>\n";


	$sql_bookmark = "SELECT * FROM t_bookmark WHERE user_name = '".char_chg("to_db", $login_staff)."' and system_type = 'sales' ORDER BY bookmark_id ASC";
	$result_bookmark = executeQuery($sql_bookmark);
	$rows_bookmark = mysql_num_rows($result_bookmark);
	$count=1;
	if($rows_bookmark){
		$menu_side_left .= "<div onclick=\"drop_menu".$count."();\">\n";
		$menu_side_left .= "<li class=\"sb-close_menu\"><a>営業管理</a></li>\n";
		$menu_side_left .= "</div>\n";
		$menu_side_left .= "<div id=\"drop_menu".$count."\" style=\"display:none\">\n";

		while($row_bookmark = mysql_fetch_array($result_bookmark)) {
			$last_char = mb_substr(char_chg("to_dsp", $row_bookmark["url"]), -4);
			if ($last_char == ".php"){	//GET引数なし
				$menu_side_left .= "<li class=\"sb-close\"><a href=\"".char_chg("to_dsp", $row_bookmark["url"])."?home=1\">&nbsp;&nbsp;&nbsp;".char_chg("to_dsp", $row_bookmark["title"])."</a></li>\n";
			}else{
				$menu_side_left .= "<li class=\"sb-close\"><a href=\"".char_chg("to_dsp", $row_bookmark["url"])."&home=1\">&nbsp;&nbsp;&nbsp;".char_chg("to_dsp", $row_bookmark["title"])."</a></li>\n";
			}
		}
		$menu_side_left .= "</div>\n";
	}

	$menu_side_left .= "</ul>\n";
	$menu_side_left .= "</nav>\n";
	$menu_side_left .= "</div><!-- sb-left -->\n";
}

	return $menu_side_left;
}



//発注用ID 顧客番号から顧客名
function f_order_partner_name_get($order_login)
{
	$c_name = "";
	$sql = "SELECT s_prj_name, s_prj_code FROM t_project WHERE order_login = '" .$order_login."'";
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_name = char_chg("to_dsp", $f_rows["s_prj_name"]);
	}

	$org_name = f_project_customername_get($f_rows["s_prj_code"]);

	mysql_free_result($result);


return array($org_name, $c_name);

}
//発注用ID 顧客番号から顧客ID
function f_order_partner_id_get($order_login)
{
	$no = "";
	$sql = "SELECT s_customer_no FROM t_project WHERE order_login = '" .$order_login."'";
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$no = $f_rows["s_customer_no"];
	}

	mysql_free_result($result);


return $no;

}

//発注用ID 顧客番号から顧客名
function f_order_partner_name_get2($order_login)
{
	$c_name = "";
	$sql = "SELECT s_prj_name FROM t_project WHERE order_login = '" .$order_login."'";
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_name = char_chg("to_dsp", $f_rows["s_prj_name"]);
	}

	mysql_free_result($result);


return $c_name;

}

function order_non_send_item_get($login_staff, $ordering_id)
{
	$num = 0;

	$sql = "SELECT order_count FROM t_ordering_order WHERE order_login = '".$login_staff."' and ordering_id = '".$ordering_id."' and send_flag = 0";

	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$num = $f_rows["order_count"];
	}

	mysql_free_result($result);

	return $num;
}

function order_non_send_item_num($login_staff, $ordering_id)
{
	$num = 0;

	$sql = "SELECT count(*) FROM t_ordering_order WHERE order_login = '".$login_staff."' and ordering_id = '".$ordering_id."' and send_flag = 0";
	$rnum1 = executeQuery($sql);
	list($num) = mysql_fetch_row($rnum1);

	mysql_free_result($rnum1);



	return $num;
}

function menu_side_left_partner($home, $login_staff)
{

	$menu_side_left = "<!-- sb-left -->\n";
	$menu_side_left .= "<div class=\"sb-slidebar sb-left\">\n";
	$menu_side_left .= "<nav>\n";
	$menu_side_left .= "<ul class=\"sb-menu\">\n";
	$menu_side_left .= "<li><img src=\"/mportal/img/logo-ordering.png\" alt=\"M-PORTAL\" height=\"40\"></li>\n";

	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/ordering/partner/top.php\">トップページ</a></li>\n";

	$menu_side_left .= "<div onclick=\"drop_menu1();\">\n";
	$menu_side_left .= "<li class=\"sb-close_menu\"><a>注文するパーツを選ぶ</a></li>\n";
	$menu_side_left .= "</div>\n";
	$menu_side_left .= "<div id=\"drop_menu1\" style=\"display:none\">\n";

	$sql = "SELECT * FROM m_code WHERE cd = '101'";
	$result = executeQuery($sql);
	$rows = mysql_num_rows($result);
	if($rows){
		while($row = mysql_fetch_array($result)) {
			if ($row["no"] != 0){
				$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/ordering/partner/order.php?model=".$row["no"]."\">&nbsp;&nbsp;&nbsp;".char_chg("to_dsp", $row["name"])."</a></li>\n";
			}
		}
	}
	mysql_free_result($result);

	$menu_side_left .= "</div>\n";

	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/ordering/partner/send.php\">注文確定</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/ordering/partner/checking.php\">検品</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/ordering/partner/send_log.php\">注文履歴</a></li>\n";

	$menu_side_left .= "</ul>\n";
	$menu_side_left .= "</nav>\n";
	$menu_side_left .= "</div><!-- sb-left -->\n";

	return $menu_side_left;
}



function menu_side_right($login_staff)
{

$menu_side_right = "<!-- sb-right -->\n";
$menu_side_right .= "<div class=\"sb-slidebar sb-right sb-style-overlay\">\n";
$menu_side_right .= "<aside id=\"about-me\">\n";
$menu_side_right .= "<ul class=\"sb-menu\">\n";
$menu_side_right .= "<li><img src=\"/mportal/img/right-menu-logo.png\" alt=\"M-PORTAL\" height=\"40\"></li>\n";
$menu_side_right .= "<li class=\"sb-close\"><a href=\"https://www.mportal.jp/profit/index.php?param=Top/show\">売上管理</a></li>\n";
$menu_side_right .= "<li class=\"sb-close\"><a href=\"https://www.mportal.jp/attendance/index.php?param=Top/show\">勤怠管理</a></li>\n";
$menu_side_right .= "<li class=\"sb-close\"><a href=\"/mportal/sales/menu.php\">営業管理</a></li>\n";
$menu_side_right .= "<li class=\"sb-close\"><a href=\"/mportal/customer/index.html\">顧客管理</a></li>\n";
$menu_side_right .= "<li class=\"sb-close\"><a href=\"/mportal/ordering/top.php\">受発注管理</a></li>\n";
$menu_side_right .= "<li class=\"sb-close\"><a href=\"/mportal/workflow/apply.php\">ワークフロー</a></li>\n";
$menu_side_right .= "<li class=\"sb-close\"><a href=\"/mportal/group/top.php\">グループウェア</a></li>\n";
$menu_side_right .= "</ul>\n";
$menu_side_right .= "</aside>\n";
$menu_side_right .= "</div><!-- /.sb-right -->\n";

	return $menu_side_right;
}




function month_dsp($settlement)
{
$mm = $settlement;
for ($ii=0; $ii<12; $ii++){
	if ($mm >12){
		$mm = 1;
	}
	$m_dsp[$ii] = $mm;
	$mm ++;
}

	return array(
		"0" => $m_dsp[0],
		"1" => $m_dsp[1],
		"2" => $m_dsp[2],
		"3" => $m_dsp[3],
		"4" => $m_dsp[4],
		"5" => $m_dsp[5],
		"6" => $m_dsp[6],
		"7" => $m_dsp[7],
		"8" => $m_dsp[8],
		"9" => $m_dsp[9],
		"10" => $m_dsp[10],
		"11" => $m_dsp[11]
		);

}


function char_chg2($chg, $cr)
{
	$cr_after = "";

	switch ($chg)
	{
		case "to_db":
			$cr_after = mb_convert_encoding($cr,"UTF-8","ASCII"); 
			break;
		case "to_dsp":
			$cr_after = mb_convert_encoding($cr,"SJIS","UTF-8"); 
			break;
		default:
			$cr_after = "no change"; 
			break;
	}

	return $cr;
}

function kakin_notice()
{
	$notice = "";
	$notice .= "<br />";
	$notice .= "<table>\n";
	$notice .= "<tr>\n";
	$notice .= "<td align=center>種別</td>\n";
	$notice .= "<td align=center>集計方法</td>\n";
	$notice .= "</tr>\n";
	$notice .= "<tr>\n";
	$notice .= "<td align=center>1</td>\n";
	$notice .= "<td align=left>社内winboard用（１つしか登録したら絶対ダメ）</td>\n";
	$notice .= "</tr>\n";
	$notice .= "<tr>\n";
	$notice .= "<td align=center>2</td>\n";
	$notice .= "<td align=left>本部課金＋ＩＤ課金＋店舗課金</td>\n";
	$notice .= "</tr>\n";
	$notice .= "<tr>\n";
	$notice .= "<td align=center>3</td>\n";
	$notice .= "<td align=left>本部課金＋スタッフ課金</td>\n";
	$notice .= "</tr>\n";
	$notice .= "<tr>\n";
	$notice .= "<td align=center>4</td>\n";
	$notice .= "<td align=left>本部課金＋ＩＤ課金</td>\n";
	$notice .= "</tr>\n";
	$notice .= "<tr>\n";
	$notice .= "<td align=center>5</td>\n";
	$notice .= "<td align=left>打刻実績課金</td>\n";
	$notice .= "</tr>\n";
	$notice .= "<tr>\n";
	$notice .= "<td align=center>6</td>\n";
	$notice .= "<td align=left>クエリー指定</td>\n";
	$notice .= "</tr>\n";
	$notice .= "</table>\n";

	return($notice);

}


function tran_commit($link)
{

	$sql = "commit";
	mysql_query( $sql, $link );
	print "コミットしました";
	//MySQL切断
	mysql_close($link) or die("MySQL切断に失敗しました。");

	return($link);

}


function f_m_code_caption($cd, $mode)
{
	switch ($cd)
	{
		case 1:	//action
			$caption = "アクションマスタ";
			$item = "アクション名";
			$name = "action";
			break;
		case 2:	//route
			$caption = "ルートマスタ";
			$item = "ルート名";
			$name = "route";
			break;
		case 3:	//kubun
			$caption = "区分マスタ";
			$item = "区分名";
			$name = "kubun";
			break;
		case 4:	//toiawase
			$caption = "問合せマスタ";
			$item = "問合せ名";
			$name = "toiawase";
			break;
		case 5:	//toiawase
			$caption = "業種マスタ";
			$item = "業種名";
			$name = "c_type";
			break;
		case 6:	//rank
			$caption = "ランクマスタ";
			$item = "ランク名";
			$name = "s_prj_rank";
			break;
		case 7:	//rank 係数
			$caption = "ランク係数マスタ（触るな危険！）";
			$item = "ランク係数";
			$name = "factor";
			break;
		case 8:	//dev_type プロジェクトタイプ(開発)
			$caption = "プロジェクトタイプマスタ(開発)（触るな危険！）";
			$item = "プロジェクトタイプ(開発)";
			$name = "dev_type";
			break;
		case 9:	//sales_type プロジェクトタイプ(営業)
			$caption = "プロジェクトタイプマスタ(営業)（触るな危険！）";
			$item = "プロジェクトタイプ(営業)";
			$name = "sales_type";
			break;
		case 10:	//dev_section 開発部セクション
			$caption = "開発部セクションマスタ(開発)（触るな危険！）";
			$item = "開発部セクション";
			$name = "dev_section";
			break;
		case 11:	//sales_flown 営業フロー
			$caption = "営業フローマスタ(営業)（触るな危険！）";
			$item = "営業フロー";
			$name = "sales_flow";
			break;
		case 12:	//kyoten 拠点
			$caption = "拠点マスタ(営業)（触るな危険！）";
			$item = "拠点";
			$name = "kyoten";
			break;
		case 99:	//flow_doc 申請文書
			$caption = "申請文書マスタ";
			$item = "申請文書";
			$name = "flow_doc";
			break;
		case 101:	//ordering 機種マスタ
			$caption = "機種マスタ";
			$item = "機種名";
			$name = "model";
			break;
	}

	if ($mode == 1){
		$d = $caption;
	}else if ($mode == 2){
		$d = $item;
	}else{
		$d = $name;
	}

return $d;

}

//マスタ　リストボックス表示
function f_code_select($cd, $f_select)
{

	$sql = "SELECT no, name FROM m_code WHERE cd = ".$cd." ORDER BY disp_order";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "";
	$html .= "<select id=\"".f_m_code_caption($cd, 3)."\" name=\"".f_m_code_caption($cd, 3)."\">\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if ($cd != 8 && $cd != 9 && $cd != 10 && $cd != 11){
				if ($f_rows["no"] == $f_select){
					$html .= "<option value=\"".$f_rows["no"]."\" selected>".char_chg("to_dsp", $f_rows["name"])."</option>\n";
				}else{
					$html .= "<option value=\"".$f_rows["no"]."\">".char_chg("to_dsp", $f_rows["name"])."</option>\n";
	    			}

			}else{
				if ($f_rows["no"] == $f_select){
					if ($f_rows["no"] == 0){
						$html .= "<option value=\"\" selected>".char_chg("to_dsp", $f_rows["name"])."</option>\n";
					}else{
						$html .= "<option value=\"".$f_rows["no"]."\" selected>".char_chg("to_dsp", $f_rows["name"])."</option>\n";
					}
				}else{
					if ($f_rows["no"] == 0){
						$html .= "<option value=\"\">".char_chg("to_dsp", $f_rows["name"])."</option>\n";
					}else{
						$html .= "<option value=\"".$f_rows["no"]."\">".char_chg("to_dsp", $f_rows["name"])."</option>\n";
					}
	    			}
			}

		}
	}

	$html .= "</select>\n";

	mysql_free_result($result);

return $html;

}
//マスタ　リストボックス表示
function f_code_free_select($cd, $f_select, $id, $name)
{

	$sql = "SELECT no, name FROM ".$_SESSION["SCHEMA"].".m_code WHERE cd = ".$cd." ORDER BY disp_order";
	$cnt = 0;
        $rows = getList($sql);
               

	$html = "";
	$html .= "<select id=\"".$id."\" name=\"".$name."\">\n";

        if($rows){
		while($row = $rows[$cnt]) {
			if ($cd != 8 && $cd != 9 && $cd != 10 && $cd != 11){
				if ($row["no"] == $f_select){
					$html .= "<option value=\"".$row["no"]."\" selected>".$row["name"]."</option>\n";
				}else{
					$html .= "<option value=\"".$row["no"]."\">".$row["name"]."</option>\n";
	    			}

			}else{
				if ($row["no"] == $f_select){
					if ($row["no"] == 0){
						$html .= "<option value=\"\" selected>".$row["name"]."</option>\n";
					}else{
						$html .= "<option value=\"".$row["no"]."\" selected>".$row["name"]."</option>\n";
					}
				}else{
					if ($row["no"] == 0){
						$html .= "<option value=\"\">".$row["name"]."</option>\n";
					}else{
						$html .= "<option value=\"".$row["no"]."\">".$row["name"]."</option>\n";
					}
	    			}
			}
			$cnt += 1;
		}
	}

	$html .= "</select>\n";


return $html;

}

//マスタ　リストボックス表示
function f_code_flow_doc_select($f_select)
{
	$cd = 99;
	$sql = "SELECT no, name FROM ".$_SESSION["SCHEMA"].".m_code WHERE cd = ".$cd." ORDER BY disp_order";

	$html = "";
	$html .= "<select id=\"".f_m_code_caption($cd, 3)."\" name=\"".f_m_code_caption($cd, 3)."\" onchange=\"flow_sel()\">\n";

	$cnt = 0;
        $rows = getList($sql);
               
        if($rows){
		while($row = $rows[$cnt]) {

			if ($cd != 8 && $cd != 9 && $cd != 10 && $cd != 11){
				if ($row["no"] == $f_select){
					$html .= "<option value=\"".$row["no"]."\" selected>".$row["name"]."</option>\n";
				}else{
					$html .= "<option value=\"".$row["no"]."\">".$row["name"]."</option>\n";
	    			}

			}else{
				if ($row["no"] == $f_select){
					if ($f_rows["no"] == 0){
						$html .= "<option value=\"\" selected>".$row["name"]."</option>\n";
					}else{
						$html .= "<option value=\"".$row["no"]."\" selected>".$row["name"]."</option>\n";
					}
				}else{
					if ($row["no"] == 0){
						$html .= "<option value=\"\">".$row["name"]."</option>\n";
					}else{
						$html .= "<option value=\"".$row["no"]."\">".$row["name"]."</option>\n";
					}
	    			}
			}
			$cnt += 1;
		}
	}

	$html .= "</select>\n";

return $html;

}


//顧客番号からルート表示
function f_customer_route_dsp($f_no)
{
	$sql = "SELECT name FROM m_code WHERE no IN ( SELECT route FROM t_customer WHERE no = ".$f_no." ) and cd = 2";
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		return char_chg("to_dsp", $f_rows["name"]);
	}

	mysql_free_result($result);

return "none";

}

//ルート　ラベル表示
function f_code_dsp($cd, $f_code)
{
	if ($f_code != ""){
		$sql = "SELECT name FROM ".$_SESSION["SCHEMA"].".m_code WHERE cd = ".$cd." and no = ".$f_code;
        $result = getList($sql);
	$f_rows = $result[0];

		if($f_rows){
			return $f_rows["name"];
		}

	}

return "none";

}
function f_code_dsp_utf($cd, $f_code)
{
	if ($f_code != ""){
		$sql = "SELECT name FROM m_code WHERE cd = ".$cd." and no = ".$f_code;
		$result = executeQuery($sql);
		$f_rows = mysql_num_rows($result);

		if($f_rows){
			$f_rows = mysql_fetch_array($result);
			return $f_rows["name"];
		}

		mysql_free_result($result);
	}

return "none";

}

function val_check($val)
{

	if ($val == 0){
		$val2=str_replace("0", "", $val);
	}else{
		$val2=number_format($val);
	}

return $val2;
}

//パートナー名　リストボックス表示
function f_partner_select($f_partner_no)
{

	$sql = "SELECT name, no FROM t_partner ORDER BY no";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "<select name=\"partner_no[]\">\n";
	$html .= "<option value=\"0000\">----</option>\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if ($f_rows["no"] == $f_partner_no){
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

function f_eigyou_tantou_get($no)
{
	$eigyo_name = "";

	$sql = "SELECT user_id FROM t_customer_info WHERE no = " .$no;
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$eigyo_name = f_staff_name_get($f_rows["user_id"]);
	}

return $eigyo_name;

}

//パートナー情報取得 パートナー番号からパートナー名
function f_partner_name_get($no)
{
	$c_name = "";

	if ($no != ""){
		$sql = "SELECT name FROM t_partner WHERE no = ".$no;

		$result = executeQuery($sql);
		$f_rows = mysql_num_rows($result);

		if($f_rows){
			$f_rows = mysql_fetch_array($result);
			$c_name = $f_rows["name"];
		}

		mysql_free_result($result);
	}

return $c_name;

}

//プロジェクト情報取得 プロジェクト番号からプロジェクト名
function f_project_name_get($no)
{
	$c_name = "";
	$sql = "SELECT s_prj_name FROM t_project WHERE s_prj_code = " .$no;

	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_name = char_chg("to_dsp", $f_rows["s_prj_name"]);
	}

	mysql_free_result($result);

return $c_name;

}


//プロジェクト情報取得 プロジェクト番号から初回課金請求日取得
function f_project_seikyu_get($no)
{
	$seikyu_year = 0;
	$seikyu_month = 0;
	$sql = "SELECT s_prj_jyuchu_kakin_date FROM t_project WHERE s_prj_code = " .$no;

	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$seikyu_year = intval(substr($f_rows["s_prj_jyuchu_kakin_date"], 0, 4));
		$seikyu_year = intval(substr($f_rows["s_prj_jyuchu_kakin_date"], 5, 2));
	}

	mysql_free_result($result);

return array( $seikyu_year, $seikyu_month );

}

//プロジェクト 顧客番号から課金データ取得
function f_project_kakin_get($c_no)
{
	$code = "";
	$honbu = 0;
	$tanka = 0;

	$sql = "SELECT s_prj_code, p_i1, p_i4 FROM t_project WHERE s_prj_status = 2 and s_customer_no = '" .$c_no."' and (s_prj_kubun = 1 or s_prj_kubun = 2)";

	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$code = $f_rows["s_prj_code"];	//プロジェクトコード
		$honbu = $f_rows["p_i1"];	//本部課金
		$tanka = $f_rows["p_i4"];	//単価
	}

	mysql_free_result($result);

	return array(
		"code" => $code,
		"honbu" => $honbu,
		"tanka" => $tanka
		);

}

//プロジェクト番号から顧客名取得
function f_project_customername_get($no)
{

	$customer_no = intval(substr($no, 4, 4));

	$c_name = "";
	$sql = "SELECT name FROM t_customer WHERE no = " .$customer_no;
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_name = char_chg("to_dsp", $f_rows["name"]);
	}

	mysql_free_result($result);

return $c_name;
}

//ブックマークボタン制御
function f_bookmark_control($login_staff, $bookmark_title, $bookmark_url)
{
	$num = 0;
	$bookmark_button_disp = 0;
	$sql = "SELECT count(*) FROM t_bookmark WHERE user_name = '".char_chg("to_db", $login_staff)."' and title = '".char_chg("to_db", $bookmark_title)."' and url = '".char_chg("to_db", $bookmark_url)."'";
	$rnum1 = executeQuery($sql);
	list($num) = mysql_fetch_row($rnum1);

	mysql_free_result($rnum1);

	if ($num >0){
		$bookmark_button_disp = 1;
	}

return array( $bookmark_button_disp, $bookmark_title );
}


//プロジェクト情報取得　メモ数
function f_project_memo_cnt_get($no)
{
	$num = 0;
	$sql = "SELECT count(*) FROM t_project_memo WHERE no = ".$no;
	$rnum1 = executeQuery($sql);
	list($num) = mysql_fetch_row($rnum1);

	mysql_free_result($rnum1);

return $num;
}
//プロジェクト情報取得　指定状況(1/2/3)の数
function f_project_stat_cnt_get($cd, $no)
{
	$num = 0;
	$sql = "SELECT count(*) FROM t_project WHERE s_customer_no = ".$no." and s_prj_status = '".$cd."'";
	$rnum1 = executeQuery($sql);
	list($num) = mysql_fetch_row($rnum1);

	mysql_free_result($rnum1);

return $num;
}

function get_inport_max_month($d_yyyy, $code)
{
	if ($code == 0){
		$sql = "SELECT * FROM t_pl2 WHERE yyyy = ".$d_yyyy." order by mm desc limit 1";
	}else{
		$sql = "SELECT * FROM t_pl2 WHERE yyyy = ".$d_yyyy." and fix = 1 order by mm desc limit 1";
	}

	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$inport_max_month = $f_rows["mm"];
	}else{
		$inport_max_month = 0;
	}

return $inport_max_month;
}

//プロジェクト情報取得　最新のメモ取得
function f_project_memo_new_get($p_no)
{
//	$sql = "SELECT memo, max(create_date) as create_date, tantou FROM t_project_memo WHERE no = ".$p_no;
//	$sql = "SELECT max(create_date) as create_date, tantou, memo FROM t_project_memo WHERE no = ".$p_no;
	$sql = "SELECT * FROM t_project_memo WHERE no = ".$p_no." order by create_date desc limit 1";

		$d_memo = "";
		$d_date = "";
		$d_tantou = "";
		$d_memo_all = "";

	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$d_memo = substr($f_rows["memo"],0,20);
		if (strlen($f_rows["memo"]) > 20){
			$d_memo = $d_memo."....";
		}
		$d_date = $f_rows["create_date"];
		$d_tantou = $f_rows["tantou"];
		$d_memo_all = $f_rows["memo"];
	}

//	mysql_free_result($result);

return array( $d_date, $d_tantou, $d_memo, $d_memo_all );

}

//プロジェクト情報取得　メモ最終更新日
function f_project_memo_date_get($no)
{
	$c_date = "";
	$sql = "SELECT create_date FROM t_project_memo WHERE no = " .$no. " ORDER BY create_date DESC LIMIT 1";
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_date = $f_rows["create_date"];
	}

	mysql_free_result($result);

return $c_date;
}

//解約　リストボックス表示
function f_kaiyaku_select($f_kaiyaku)
{
	$kaiyaku_html = "";
	$kaiyaku_html .= "<select name=\"kaiyaku\">";
	switch ($f_kaiyaku)
	{
		case 0:
			$kaiyaku_html .= "<option value=\"0\" selected>全て</option>\n";
			$kaiyaku_html .= "<option value=\"1\">未解約</option>\n";
			$kaiyaku_html .= "<option value=\"2\">解約</option>\n";
			break;
		case 1:
			$kaiyaku_html .= "<option value=\"0\">全て</option>\n";
			$kaiyaku_html .= "<option value=\"1\" selected>未解約</option>\n";
			$kaiyaku_html .= "<option value=\"2\">解約</option>\n";
			break;
		case 2:
			$kaiyaku_html .= "<option value=\"0\">全て</option>\n";
			$kaiyaku_html .= "<option value=\"1\" >未解約</option>\n";
			$kaiyaku_html .= "<option value=\"2\" selected>解約</option>\n";
			break;
	}
	$kaiyaku_html .= "</select>";

return $kaiyaku_html;

}

//ワークフロー　状況　リストボックス表示
function f_stat_select($stat)
{
	$stat_html = "";
	$stat_html .= "<select name=\"k_stat\">";
	switch ($stat)
	{
		case 0:
			$stat_html .= "<option value=\"0\" selected>全て</option>\n";
			$stat_html .= "<option value=\"1\">申請中</option>\n";
			$stat_html .= "<option value=\"2\">否決</option>\n";
			$stat_html .= "<option value=\"3\">可決</option>\n";
			$stat_html .= "<option value=\"4\">中止</option>\n";
			break;
		case 1:
			$stat_html .= "<option value=\"0\">全て</option>\n";
			$stat_html .= "<option value=\"1\" selected>申請中</option>\n";
			$stat_html .= "<option value=\"2\">否決</option>\n";
			$stat_html .= "<option value=\"3\">可決</option>\n";
			$stat_html .= "<option value=\"4\">中止</option>\n";
			break;
		case 2:
			$stat_html .= "<option value=\"0\">全て</option>\n";
			$stat_html .= "<option value=\"1\">申請中</option>\n";
			$stat_html .= "<option value=\"2\" selected>否決</option>\n";
			$stat_html .= "<option value=\"3\">可決</option>\n";
			$stat_html .= "<option value=\"4\">中止</option>\n";
			break;
		case 3:
			$stat_html .= "<option value=\"0\">全て</option>\n";
			$stat_html .= "<option value=\"1\">申請中</option>\n";
			$stat_html .= "<option value=\"2\">否決</option>\n";
			$stat_html .= "<option value=\"3\" selected>可決</option>\n";
			$stat_html .= "<option value=\"4\">中止</option>\n";
			break;
		case 4:
			$stat_html .= "<option value=\"0\">全て</option>\n";
			$stat_html .= "<option value=\"1\">申請中</option>\n";
			$stat_html .= "<option value=\"2\">否決</option>\n";
			$stat_html .= "<option value=\"3\">可決</option>\n";
			$stat_html .= "<option value=\"4\" selected>中止</option>\n";
			break;
	}
	$stat_html .= "</select>";

return $stat_html;

}

//受注者側の状態　リストボックス表示
function fordering_recv_stat($f_stat)
{

	for ($i = 0; $i<= 6; $i++){
		$data[$i] = "";
	}
	$data[$f_stat] = " selected";

	$kaiyaku_html = "";
	$kaiyaku_html .= "<select name=\"recv_stat\">";
	$kaiyaku_html .= "<option value=\"0\"".$data[0].">全て</option>\n";
	$kaiyaku_html .= "<option value=\"1\"".$data[1].">注文</option>\n";
	$kaiyaku_html .= "<option value=\"2\"".$data[2].">発送待ち</option>\n";
	$kaiyaku_html .= "<option value=\"3\"".$data[3].">検品待ち</option>\n";
	$kaiyaku_html .= "<option value=\"4\"".$data[4].">検品済み</option>\n";
	$kaiyaku_html .= "<option value=\"5\"".$data[5].">請求締め</option>\n";
	$kaiyaku_html .= "<option value=\"6\"".$data[6].">請求出力</option>\n";

	$kaiyaku_html .= "</select>";

return $kaiyaku_html;

}

//機能　リストボックス表示
function f_kinou_select($default_dsp)
{
	$kinou_html = "";
	$kinou_html .= "<select name=\"default_dsp\">";
	switch ($default_dsp)
	{
		case 0:
			$kinou_html .= "<option value=\"0\" selected>---</option>\n";
			$kinou_html .= "<option value=\"1\">ホーム</option>\n";
			$kinou_html .= "<option value=\"2\">売上管理</option>\n";
			$kinou_html .= "<option value=\"3\">勤怠管理</option>\n";
			$kinou_html .= "<option value=\"4\">営業管理</option>\n";
			$kinou_html .= "<option value=\"5\">顧客管理</option>\n";
			$kinou_html .= "<option value=\"8\">受発注管理</option>\n";
			$kinou_html .= "<option value=\"6\">ワークフロー</option>\n";
			$kinou_html .= "<option value=\"7\">グループウェア</option>\n";
			break;
		case 1:
			$kinou_html .= "<option value=\"0\">---</option>\n";
			$kinou_html .= "<option value=\"1\" selected>ホーム</option>\n";
			$kinou_html .= "<option value=\"2\">売上管理</option>\n";
			$kinou_html .= "<option value=\"3\">勤怠管理</option>\n";
			$kinou_html .= "<option value=\"4\">営業管理</option>\n";
			$kinou_html .= "<option value=\"5\">顧客管理</option>\n";
			$kinou_html .= "<option value=\"8\">受発注管理</option>\n";
			$kinou_html .= "<option value=\"6\">ワークフロー</option>\n";
			$kinou_html .= "<option value=\"7\">グループウェア</option>\n";
			break;
		case 2:
			$kinou_html .= "<option value=\"0\">---</option>\n";
			$kinou_html .= "<option value=\"1\">ホーム</option>\n";
			$kinou_html .= "<option value=\"2\" selected>売上管理</option>\n";
			$kinou_html .= "<option value=\"3\">勤怠管理</option>\n";
			$kinou_html .= "<option value=\"4\">営業管理</option>\n";
			$kinou_html .= "<option value=\"5\">顧客管理</option>\n";
			$kinou_html .= "<option value=\"8\">受発注管理</option>\n";
			$kinou_html .= "<option value=\"6\">ワークフロー</option>\n";
			$kinou_html .= "<option value=\"7\">グループウェア</option>\n";
			break;
		case 3:
			$kinou_html .= "<option value=\"0\">---</option>\n";
			$kinou_html .= "<option value=\"1\">ホーム</option>\n";
			$kinou_html .= "<option value=\"2\">売上管理</option>\n";
			$kinou_html .= "<option value=\"3\" selected>勤怠管理</option>\n";
			$kinou_html .= "<option value=\"4\">営業管理</option>\n";
			$kinou_html .= "<option value=\"5\">顧客管理</option>\n";
			$kinou_html .= "<option value=\"8\">受発注管理</option>\n";
			$kinou_html .= "<option value=\"6\">ワークフロー</option>\n";
			$kinou_html .= "<option value=\"7\">グループウェア</option>\n";
			break;
		case 4:
			$kinou_html .= "<option value=\"0\">---</option>\n";
			$kinou_html .= "<option value=\"1\">ホーム</option>\n";
			$kinou_html .= "<option value=\"2\">売上管理</option>\n";
			$kinou_html .= "<option value=\"3\">勤怠管理</option>\n";
			$kinou_html .= "<option value=\"4\" selected>営業管理</option>\n";
			$kinou_html .= "<option value=\"5\">顧客管理</option>\n";
			$kinou_html .= "<option value=\"8\">受発注管理</option>\n";
			$kinou_html .= "<option value=\"6\">ワークフロー</option>\n";
			$kinou_html .= "<option value=\"7\">グループウェア</option>\n";
			break;
		case 5:
			$kinou_html .= "<option value=\"0\">---</option>\n";
			$kinou_html .= "<option value=\"1\">ホーム</option>\n";
			$kinou_html .= "<option value=\"2\">売上管理</option>\n";
			$kinou_html .= "<option value=\"3\">勤怠管理</option>\n";
			$kinou_html .= "<option value=\"4\">営業管理</option>\n";
			$kinou_html .= "<option value=\"5\" selected>顧客管理</option>\n";
			$kinou_html .= "<option value=\"8\">受発注管理</option>\n";
			$kinou_html .= "<option value=\"6\">ワークフロー</option>\n";
			$kinou_html .= "<option value=\"7\">グループウェア</option>\n";
			break;
		case 6:
			$kinou_html .= "<option value=\"0\">---</option>\n";
			$kinou_html .= "<option value=\"1\">ホーム</option>\n";
			$kinou_html .= "<option value=\"2\">売上管理</option>\n";
			$kinou_html .= "<option value=\"3\">勤怠管理</option>\n";
			$kinou_html .= "<option value=\"4\">営業管理</option>\n";
			$kinou_html .= "<option value=\"5\">顧客管理</option>\n";
			$kinou_html .= "<option value=\"8\">受発注管理</option>\n";
			$kinou_html .= "<option value=\"6\" selected>ワークフロー</option>\n";
			$kinou_html .= "<option value=\"7\">グループウェア</option>\n";
			break;
		case 7:
			$kinou_html .= "<option value=\"0\">---</option>\n";
			$kinou_html .= "<option value=\"1\">ホーム</option>\n";
			$kinou_html .= "<option value=\"2\">売上管理</option>\n";
			$kinou_html .= "<option value=\"3\">勤怠管理</option>\n";
			$kinou_html .= "<option value=\"4\">営業管理</option>\n";
			$kinou_html .= "<option value=\"5\">顧客管理</option>\n";
			$kinou_html .= "<option value=\"8\">受発注管理</option>\n";
			$kinou_html .= "<option value=\"6\">ワークフロー</option>\n";
			$kinou_html .= "<option value=\"7\" selected>グループウェア</option>\n";
			break;
		case 8:
			$kinou_html .= "<option value=\"0\">---</option>\n";
			$kinou_html .= "<option value=\"1\">ホーム</option>\n";
			$kinou_html .= "<option value=\"2\">売上管理</option>\n";
			$kinou_html .= "<option value=\"3\">勤怠管理</option>\n";
			$kinou_html .= "<option value=\"4\">営業管理</option>\n";
			$kinou_html .= "<option value=\"5\">顧客管理</option>\n";
			$kinou_html .= "<option value=\"8\" selected>受発注管理</option>\n";
			$kinou_html .= "<option value=\"6\">ワークフロー</option>\n";
			$kinou_html .= "<option value=\"7\">グループウェア</option>\n";
			break;
	}
	$kinou_html .= "</select>";

return $kinou_html;

}

//機能　リスト表示
function f_kinou_dsp($default_dsp)
{

	$dsp_kinou[0] = "---";
	$dsp_kinou[1] = "ホーム";
	$dsp_kinou[2] = "売上管理";
	$dsp_kinou[3] = "勤怠管理";
	$dsp_kinou[4] = "営業管理";
	$dsp_kinou[5] = "顧客管理";
	$dsp_kinou[6] = "ワークフロー";
	$dsp_kinou[7] = "グループウェア";
	$dsp_kinou[8] = "受発注管理";

return $dsp_kinou[$default_dsp];

}

//地域　リストボックス表示
function f_chiiki_select($f_chiiki)
{
	$chiiki_html = "";
	$chiiki_html .= "<select name=\"chiiki\">";
	switch ($f_chiiki)
	{
		case 0:
			$chiiki_html .= "<option value=\"0\" selected>全て</option>\n";
			$chiiki_html .= "<option value=\"1\">北海道</option>\n";
			$chiiki_html .= "<option value=\"2\">東京都</option>\n";
			$chiiki_html .= "<option value=\"3\">関西</option>\n";
			break;
		case 1:
			$chiiki_html .= "<option value=\"0\">全て</option>\n";
			$chiiki_html .= "<option value=\"1\" selected>北海道</option>\n";
			$chiiki_html .= "<option value=\"2\">東京都</option>\n";
			$chiiki_html .= "<option value=\"3\">関西</option>\n";
			break;
		case 2:
			$chiiki_html .= "<option value=\"0\">全て</option>\n";
			$chiiki_html .= "<option value=\"1\" >北海道</option>\n";
			$chiiki_html .= "<option value=\"2\" selected>東京都</option>\n";
			$chiiki_html .= "<option value=\"3\">関西</option>\n";
			break;
		case 3:
			$chiiki_html .= "<option value=\"0\">全て</option>\n";
			$chiiki_html .= "<option value=\"1\" >北海道</option>\n";
			$chiiki_html .= "<option value=\"2\">東京都</option>\n";
			$chiiki_html .= "<option value=\"3\" selected>関西</option>\n";
			break;
	}
	$chiiki_html .= "</select>";

return $chiiki_html;

}

//案件ありなし　リストボックス表示
function f_anken_select($f_anken)
{
	$anken_html = "";
	$anken_html .= "<select name=\"anken\">";
	switch ($f_anken)
	{
		case 0:
			$anken_html .= "<option value=\"0\" selected>全て</option>\n";
			$anken_html .= "<option value=\"1\">有のみ</option>\n";
			break;
		case 1:
			$anken_html .= "<option value=\"0\">全て</option>\n";
			$anken_html .= "<option value=\"1\" selected>有のみ</option>\n";
			break;
	}
	$anken_html .= "</select>";

return $anken_html;

}
//メモありなし　リストボックス表示
function f_memo_select($f_memo)
{
	$memo_html = "";
	$memo_html .= "<select name=\"memo_ari\">";
	switch ($f_memo)
	{
		case 0:
			$memo_html .= "<option value=\"0\" selected>全て</option>\n";
			$memo_html .= "<option value=\"1\">有のみ</option>\n";
			break;
		case 1:
			$memo_html .= "<option value=\"0\">全て</option>\n";
			$memo_html .= "<option value=\"1\" selected>有のみ</option>\n";
			break;
	}
	$memo_html .= "</select>";

return $memo_html;

}

//休暇申請の理由　リストボックス表示
function f_wake_select($wake)
{
	$wake_html = "";
	$wake_html .= "<select name=\"w_wake\">";
	switch ($wake)
	{
		case "none":
			$wake_html .= "<option value=\"none\" selected>---</option>\n";
			$wake_html .= "<option value=\"私用\">私用</option>\n";
			$wake_html .= "<option value=\"その他\">その他</option>\n";
			break;
		case "私用":
			$wake_html .= "<option value=\"none\">---</option>\n";
			$wake_html .= "<option value=\"私用\" selected>私用</option>\n";
			$wake_html .= "<option value=\"その他\">その他</option>\n";
			break;
		case "その他":
			$wake_html .= "<option value=\"none\">---</option>\n";
			$wake_html .= "<option value=\"私用\">私用</option>\n";
			$wake_html .= "<option value=\"その他\" selected>その他</option>\n";
			break;
	}
	$wake_html .= "</select>";

return $wake_html;

}
//休暇申請の区分　リストボックス表示
function f_holiday_select($holiday)
{
	$holiday_html = "";
	$holiday_html .= "<select name=\"w_holiday\">";
	switch ($holiday)
	{
		case "none":
			$holiday_html .= "<option value=\"none\" selected>---</option>\n";
			$holiday_html .= "<option value=\"有給休暇\">有給休暇</option>\n";
			$holiday_html .= "<option value=\"慶弔休暇\">慶弔休暇</option>\n";
			$holiday_html .= "<option value=\"特別休暇\">特別休暇</option>\n";
			$holiday_html .= "<option value=\"欠勤\">欠勤</option>\n";
			$holiday_html .= "<option value=\"その他\">その他</option>\n";
			break;
		case "有給休暇":
			$holiday_html .= "<option value=\"none\">---</option>\n";
			$holiday_html .= "<option value=\"有給休暇\" selected>有給休暇</option>\n";
			$holiday_html .= "<option value=\"慶弔休暇\">慶弔休暇</option>\n";
			$holiday_html .= "<option value=\"特別休暇\">特別休暇</option>\n";
			$holiday_html .= "<option value=\"欠勤\">欠勤</option>\n";
			$holiday_html .= "<option value=\"その他\">その他</option>\n";
			break;
		case "慶弔休暇":
			$holiday_html .= "<option value=\"none\">---</option>\n";
			$holiday_html .= "<option value=\"有給休暇\">有給休暇</option>\n";
			$holiday_html .= "<option value=\"慶弔休暇\" selected>慶弔休暇</option>\n";
			$holiday_html .= "<option value=\"特別休暇\">特別休暇</option>\n";
			$holiday_html .= "<option value=\"欠勤\">欠勤</option>\n";
			$holiday_html .= "<option value=\"その他\">その他</option>\n";
			break;
		case "特別休暇":
			$holiday_html .= "<option value=\"none\">---</option>\n";
			$holiday_html .= "<option value=\"有給休暇\">有給休暇</option>\n";
			$holiday_html .= "<option value=\"慶弔休暇\">慶弔休暇</option>\n";
			$holiday_html .= "<option value=\"特別休暇\" selected>特別休暇</option>\n";
			$holiday_html .= "<option value=\"欠勤\">欠勤</option>\n";
			$holiday_html .= "<option value=\"その他\">その他</option>\n";
			break;
		case "欠勤":
			$holiday_html .= "<option value=\"none\">---</option>\n";
			$holiday_html .= "<option value=\"有給休暇\">有給休暇</option>\n";
			$holiday_html .= "<option value=\"慶弔休暇\">慶弔休暇</option>\n";
			$holiday_html .= "<option value=\"特別休暇\">特別休暇</option>\n";
			$holiday_html .= "<option value=\"欠勤\" selected>欠勤</option>\n";
			$holiday_html .= "<option value=\"その他\">その他</option>\n";
			break;
		case "その他":
			$holiday_html .= "<option value=\"none\">---</option>\n";
			$holiday_html .= "<option value=\"有給休暇\">有給休暇</option>\n";
			$holiday_html .= "<option value=\"慶弔休暇\">慶弔休暇</option>\n";
			$holiday_html .= "<option value=\"特別休暇\">特別休暇</option>\n";
			$holiday_html .= "<option value=\"欠勤\">欠勤</option>\n";
			$holiday_html .= "<option value=\"その他\" selected>その他</option>\n";
			break;
	}
	$holiday_html .= "</select>";

return $holiday_html;

}

//解約　ラベル表示
function f_kaiyaku_dsp($f_kaiyaku)
{
	$kaiyaku_html = "";
	switch ($f_kaiyaku)
	{
		case 0:
			$kaiyaku_html .= " ";
			break;
		case 1:
			$kaiyaku_html .= "解約";
			break;
	}

return $kaiyaku_html;

}

function f_kikan_sitei($start, $end)
{

	$html = "<input type=\"text\" id=\"start_date\" name=\"start_date\" size=\"10\" value=$start>";
	$html .= "～";
	$html .= "<input type=\"text\" id=\"end_date\" name=\"end_date\" size=\"10\" value=$end>";

return $html;
}

//年月指定リスト
function f_yyyymm_sitei($yyyy, $mm)
{

	$y_i = 2011;
	$m_i = 1;
	$i = 0;
	$now_date = date("Y")+1;

	$html_yyyy = "";
	$html_yyyy .= "<select name=\"yyyy\">\n";
	for ($i = $y_i; $i <= $now_date; $i++)
	{
		if ($i == $yyyy){
			$html_yyyy .= "<option value=\"".$i."\" selected>".$i."</option>\n";
		}else{
			$html_yyyy .= "<option value=\"".$i."\">".$i."</option>\n";
		}
	}
	$html_yyyy .= "</select>年\n";

	$html_mm = "";
	$html_mm .= "<select name=\"mm\">\n";
	for ($i = $m_i; $i <= 12; $i++)
	{
		if ($i == $mm){
			$html_mm .= "<option value=\"".$i."\" selected>".$i."</option>\n";
		}else{
			$html_mm .= "<option value=\"".$i."\">".$i."</option>\n";
		}
	}
	$html_mm .= "</select>月\n";

	$html = $html_yyyy.$html_mm;

return $html;
}


//年指定リスト
function f_yyyy_sitei($yyyy)
{

	$y_i = 2011;
	$i = 0;
	$now_date = date("Y")+1;

	$html_yyyy = "";
	$html_yyyy .= "<select name=\"yyyy\">\n";
	for ($i = $y_i; $i <= $now_date; $i++)
	{
		if ($i == $yyyy){
			$html_yyyy .= "<option value=\"".$i."\" selected>".$i."</option>\n";
		}else{
			$html_yyyy .= "<option value=\"".$i."\">".$i."</option>\n";
		}
	}
	$html_yyyy .= "</select>年\n";

return $html_yyyy;
}

//グラフ指定リスト
function f_graph_sitei($code)
{

	$html_graph = "<select name=\"code\">\n";
		if ($code == 0){
			$html_graph .= "<option value=\"0\" selected>初期込み</option>\n";
			$html_graph .= "<option value=\"1\">課金のみ</option>\n";
		}else{
			$html_graph .= "<option value=\"0\">初期込み</option>\n";
			$html_graph .= "<option value=\"1\" selected>課金のみ</option>\n";
		}
	$html_graph .= "</select>\n";

return $html_graph;
}

//グラフ指定リスト
function f_graph_sitei2($code2)
{

	$html_graph2 = "受注済みグラフを表示<select name=\"code2\">\n";
		if ($code2 == 0){
			$html_graph2 .= "<option value=\"0\" selected>する</option>\n";
			$html_graph2 .= "<option value=\"1\">しない</option>\n";
		}else{
			$html_graph2 .= "<option value=\"0\">する</option>\n";
			$html_graph2 .= "<option value=\"1\" selected>しない</option>\n";
		}
	$html_graph2 .= "</select>\n";

return $html_graph2;
}

//グラフ指定リスト
function f_graph_sitei3($code3)
{

	$html_graph3 = "<select name=\"code3\">\n";
		if ($code3 == 0){
			$html_graph3 .= "<option value=\"0\" selected>未請求込み</option>\n";
			$html_graph3 .= "<option value=\"1\">請求済のみ</option>\n";
		}else{
			$html_graph3 .= "<option value=\"0\">未請求込み</option>\n";
			$html_graph3 .= "<option value=\"1\" selected>請求済のみ</option>\n";
		}
	$html_graph3 .= "</select>\n";

return $html_graph3;
}

//グラフ指定リスト
function f_graph_sitei4($code4)
{

	$html_graph4 = "<select name=\"code4\">\n";
		if ($code4 == 0){
			$html_graph4 .= "<option value=\"0\" selected>全て</option>\n";
			$html_graph4 .= "<option value=\"1\">活動中のみ</option>\n";
		}else{
			$html_graph4 .= "<option value=\"0\">全て</option>\n";
			$html_graph4 .= "<option value=\"1\" selected>活動中のみ</option>\n";
		}
	$html_graph4 .= "</select>\n";

return $html_graph4;
}

//チェックリスト
function f_check_list($code)
{

	$html = "<select name=\"code\">\n";
		if ($code == 0){
			$html .= "<option value=\"0\" selected>全て</option>\n";
			$html .= "<option value=\"1\">未完了のみ</option>\n";
		}else{
			$html .= "<option value=\"0\">全て</option>\n";
			$html .= "<option value=\"1\" selected>未完了のみ</option>\n";
		}
	$html .= "</select>\n";

return $html;
}

// --- 業務名の表示

//ユーザ名から対象となる業務リストボックス表示 AJAX
function f_ajax_gyoum($user_name,$d_gyoumu_kubun)
{
	$busho_code = 0;
//	$sql = "SELECT busho_code FROM t_staff WHERE name = '" .$user_name."'";
//	$result = executeQuery($sql);
//	$f_rows = mysql_num_rows($result);
//	if($f_rows){
//		$f_rows = mysql_fetch_array($result);
//		$busho_code = $f_rows["busho_code"];
//	}
//	mysql_free_result($result);


//	$busho_code = f_staff_busho_id_get(char_chg("to_dsp", $user_name));
	$busho_code = f_staff_busho_id_get($user_name);

	$sql = "SELECT gyoumu_kubun, gyoumu_name FROM m_report_kubun WHERE busho = '" .$busho_code. "' GROUP BY gyoumu_name ORDER BY report_kubun_id";
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);
	$html = "<select name=\"gyoumu\" class=\"gyoumu\">\n";
	$html .= "<option value=\"\">選択して下さい</option>\n";
	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if ($d_gyoumu_kubun == $f_rows["gyoumu_kubun"]){
				$html .= "<option value=\"".$f_rows["gyoumu_kubun"]."\" selected>".char_chg("to_dsp", $f_rows["gyoumu_name"])."</option>\n";
			}else{
				$html .= "<option value=\"".$f_rows["gyoumu_kubun"]."\">".char_chg("to_dsp", $f_rows["gyoumu_name"])."</option>\n";
			}
		}
	}
	mysql_free_result($result);
	$html .= "</select>\n";

return $html;
}

//顧客名リストボックス表示 AJAX
function f_ajax_customer($f_customer_no, $user_name)
{

	$busho_code = f_staff_busho_id_get($user_name);

	if ($busho_code == 2){
		$sql = "SELECT tc.name, tc.no FROM t_customer tc, t_project tp WHERE tc.cnt > 0 and tc.no = tp.s_customer_no and ((tp.s_prj_status = '2' and tp.d_f = '1') or (tp.s_prj_status = '1' and tp.d_f = '1')) and tc.kaiyaku = 0 GROUP BY tc.no ORDER BY tc.no";
	}else{
		$sql = "SELECT name, no FROM t_customer WHERE kaiyaku = 0 ORDER BY no";
	}
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html = "<select name=\"customer\" class=\"customer\">\n";
	$html .= "<option value=\"\">選択して下さい</option>\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if ($f_customer_no == $f_rows["no"]){
				$html .= "<option value=\"".$f_rows["no"]."\" selected>".char_chg("to_dsp", $f_rows["name"])."</option>\n";
			}else{
				$html .= "<option value=\"".$f_rows["no"]."\">".char_chg("to_dsp", $f_rows["name"])."</option>\n";
			}
		}
	}
	mysql_free_result($result);
	$html .= "</select>\n";

return $html;
}

//顧客名リストボックス表示 AJAX
function f_ajax_customer2($cd, $d_yyyy, $d_mm, $user_id, $f_customer_no, $user_name)
{

	$html_sel = "";
	$html = "";
	
	if (($d_mm-1) <= 0){
		$d_mm_s = 12;
		$d_yyyy_s = $d_yyyy-1;
	}else{
		$d_mm_s = $d_mm - 1;
		$d_yyyy_s = $d_yyyy;
	}
	$busho_code = f_staff_busho_id_get($user_name);
	
	if ($busho_code == 2){
		$sql = "SELECT tc.name, tc.no FROM t_customer tc, t_project tp WHERE tc.cnt > 0 and tc.no = tp.s_customer_no and ((tp.s_prj_status = '2' and tp.d_f = '1') or (tp.s_prj_status = '1' and tp.d_f = '1')) and tc.kaiyaku = 0 GROUP BY tc.no ORDER BY tc.no";
	}else{
		if ($cd == 1){
			$sql = "SELECT name, no FROM t_customer WHERE kaiyaku = 0 ORDER BY no";
		}else{
//			$sql = "SELECT name, no FROM t_customer WHERE kaiyaku = 0 ORDER BY create_date desc limit 200";
			$sql = "SELECT name, no, route FROM t_customer WHERE kaiyaku = 0 and route != 17 and route != 18";
		}
	}
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$html_sel .= "<select name=\"customer2\" class=\"customer2\">\n";
	$html_sel .= "<option value=\"\">最近１ヶ月間に報告した顧客</option>\n";
	$html .= "<select name=\"customer\" class=\"customer\">\n";
	$html .= "<option value=\"\">選択して下さい</option>\n";

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
		
			$sql_rr = "SELECT count(*) FROM t_report WHERE user_id = ".$user_id." and yyyy >= ".$d_yyyy_s." and mm >= ".$d_mm_s." and customer_no = ".$f_rows["no"];
			$rnum = executeQuery($sql_rr);
			list($num) = mysql_fetch_row($rnum);
			
			if ($f_customer_no == $f_rows["no"]){
				$html .= "<option value=\"".$f_rows["no"]."\" selected>".char_chg("to_dsp", $f_rows["name"])."</option>\n";
				if ($num != 0){
					$html_sel .= "<option value=\"".$f_rows["no"]."\" selected>".char_chg("to_dsp", $f_rows["name"])."</option>\n";
				}
			}else{
				$html .= "<option value=\"".$f_rows["no"]."\">".char_chg("to_dsp", $f_rows["name"])."</option>\n";
				if ($num != 0){
					$html_sel .= "<option value=\"".$f_rows["no"]."\">".char_chg("to_dsp", $f_rows["name"])."</option>\n";
				}
			}
			mysql_free_result($rnum);
		}
	}
	mysql_free_result($result);

	$html .= "</select>\n";
	$html_sel .= "</select>\n";
	
	$html = $html_sel."<br />".$html;
	
return $html;
}

//業務情報取得 業務番号から業務名
function f_gyoumu_name_get($busho_code,$gyoumu_no)
{
	$c_name = "";
	$sql = "SELECT gyoumu_name FROM m_report_kubun WHERE busho = ".$busho_code." and gyoumu_kubun = ".$gyoumu_no." GROUP BY gyoumu_name ORDER BY report_kubun_id";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_name = char_chg("to_dsp", $f_rows["gyoumu_name"]);
	}

	mysql_free_result($result);

return $c_name;

}

//業務情報取得 業務番号から業務名
function f_post_name_data_get($workflow_id, $postname)
{
	$data = "";
	$sql = "SELECT data FROM ".$_SESSION["SCHEMA"].".t_workflow_doc_new WHERE workflow_id = '".$workflow_id."' and post_name = '".$postname."'";
        $result = getList($sql);
	$f_rows = $result[0];

	if($f_rows){
		$data = $f_rows["data"];
	}

return $data;

}

//作業情報取得 作業番号から作業名
function f_sagyou_name_get($busho_code,$sagyou_no)
{
	$c_name = "";
	$sql = "SELECT sagyou_name FROM m_report_kubun WHERE busho = ".$busho_code." and sagyou_kubun = ".$sagyou_no." GROUP BY gyoumu_name ORDER BY report_kubun_id";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$c_name = char_chg("to_dsp", $f_rows["sagyou_name"]);
	}

	mysql_free_result($result);

return $c_name;

}

function f_report_info_get($report_id)
{
	$d_yyyy ="";
	$d_mm = "";
	$d_dd = "";
	$d_customer_no = "";
	$d_s_prj_code = "";
	$d_gyoumu_kubun = "";
	$d_sagyou_kubun = "";
	$d_kousu = "";
	$d_keihi = "";

	$sql = "SELECT * FROM t_report WHERE report_id = " .$report_id;
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$d_yyyy = $f_rows["yyyy"];
		$d_mm = $f_rows["mm"];
		$d_dd = $f_rows["dd"];
		$d_customer_no = $f_rows["customer_no"];
		$d_s_prj_code = $f_rows["s_prj_code"];
		$d_gyoumu_kubun= $f_rows["gyoumu_kubun"];
		$d_sagyou_kubun = $f_rows["sagyou_kubun"];
		$d_kousu = $f_rows["kousu"];
		$d_keihi = $f_rows["keihi"];
	}

	mysql_free_result($result);

return array( $d_yyyy, $d_mm, $d_dd, $d_customer_no, $d_s_prj_code, $d_gyoumu_kubun, $d_sagyou_kubun, $d_kousu, $d_keihi );

}

function f_sagyou_name_select($sagyou_id, $gyoumu_id, $busho_code)
{
	$sql = "select sagyou_kubun, sagyou_name from m_report_kubun where gyoumu_kubun = '".$gyoumu_id."' and busho = '".$busho_code."'";
	$result = executeQuery($sql);

	$html = "";


	if ($sagyou_id != 0){
		$html .= "<select name=\"sagyou\" class=\"sagyou\">";
		while($row=mysql_fetch_array($result))
		{
			$id=$row['sagyou_kubun'];
			$data=char_chg("to_dsp", $row['sagyou_name']);
			if ($id == $sagyou_id){
				$html .= "<option value=\"".$id."\" selected>".$data."</option>";
			}else{
				$html .= "<option value=\"".$id."\">".$data."</option>";
			}
		}
		$html .= "</select>";
	}else{
		$html .= "<select name=\"sagyou\" class=\"sagyou\">";
		$html .= "<option value=\"0\">業務名を選択して下さい</option>";
		$html .= "</select>";
	}

return $html;
}

function f_prj_name_select($d_customer_no,$s_prj_code)
{
	$sql = "select s_prj_code, s_prj_name from t_project where s_customer_no = '".$d_customer_no."'";
	$result = executeQuery($sql);

	$html = "";


	if ($d_customer_no != 0){
		$html .= "<select name=\"prj\" class=\"prj\">";
		while($row=mysql_fetch_array($result))
		{
			$id=$row['s_prj_code'];
			$data=char_chg("to_dsp", $row['s_prj_name']);
			if ($id == $s_prj_code){
				$html .= "<option value=\"".$id."\" selected>".$data."</option>";
			}else{
				$html .= "<option value=\"".$id."\">".$data."</option>";
			}
		}
		$html .= "</select>";
	}else{
		$html .= "<select name=\"prj\" class=\"prj\">";
		$html .= "<option value=\"0\">顧客名を選択して下さい</option>";
		$html .= "</select>";
	}

return $html;
}

function computeDate($val) {
    return date("Y-m-d H:i:s", strtotime($val." day"));
}

function computeDate2($sitei, $addDays) {

	$year = intval(substr($sitei, 0, 4));
	$month = intval(substr($sitei, 5, 2));
	$day = intval(substr($sitei, 8, 2));

    $baseSec = mktime(0, 0, 0, $month, $day, $year);//基準日を秒で取得
    $addSec = $addDays * 86400;//日数×１日の秒数
    $targetSec = $baseSec + $addSec;
    return date("Y-m-d", $targetSec);
}

//プロジェクト　最新プロジェクトの登録日を取得
function f_project_max_createdate($d_no)
{
	$sql = "SELECT max(create_date) as create_date FROM t_project WHERE s_customer_no = " .$d_no;

	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$d_date = $f_rows["create_date"];
	}

	mysql_free_result($result);

return $d_date;

}
//プロジェクト　最新プロジェクトの更新日を取得
function f_project_max_update($d_no)
{

	$sql = "SELECT max(up_date) as up_date FROM t_project WHERE s_customer_no = " .$d_no;

	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$d_date = $f_rows["up_date"];
	}

	mysql_free_result($result);

return $d_date;

}

function f_youbi_get($y, $m, $d)
{

$weekjp_array = array('日', '月', '火', '水', '木', '金', '土');

//日付を指定
$pyear = $y;
$pmonth = $m;
$pday = $d;

//タイムスタンプを取得
$ptimestamp = mktime(0, 0, 0, $pmonth, $pday, $pyear);
//曜日番号を取得
$weekno = date('w', $ptimestamp);
//日本語の曜日を出力
$weekjp = $weekjp_array[$weekno];

return $weekjp;

}

function manage_check_invoice ($c1,$c2,$c3,$c4,$c5,$c6,$c7,$c8,$c9,$c10,$all)
{

$html = "<td style=\"font-size: 11px;\">";
if ($all != 0){
	$html .= "<input type=\"checkbox\" name=\"all\" value=\"11\" onClick=\"allChange();\" checked>全て ";
}else{
	$html .= "<input type=\"checkbox\" name=\"all\" value=\"11\" onClick=\"allChange();\">全て ";
}
$html .= "</td><td class=\"title_td_p\" style=\"font-size: 11px;\">";
if ($c1 != 0){
	$html .= "<input type=\"checkbox\" name=\"c1\" value=\"1\" checked>検収　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c1\" value=\"1\">検収　";
}
if ($c2 != 0){
	$html .= "<input type=\"checkbox\" name=\"c2\" value=\"2\" checked>支払 ";
}else{
	$html .= "<input type=\"checkbox\" name=\"c2\" value=\"2\">支払 ";
}
$html .= "</td><td class=\"title_td_c\" style=\"font-size: 11px;\">";
if ($c3 != 0){
	$html .= "<input type=\"checkbox\" name=\"c3\" value=\"3\" checked>検収　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c3\" value=\"3\">検収　";
}
if ($c4 != 0){
	$html .= "<input type=\"checkbox\" name=\"c4\" value=\"4\" checked>検収書送付　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c4\" value=\"4\">検収書送付　";
}
if ($c5 != 0){
	$html .= "<input type=\"checkbox\" name=\"c5\" value=\"5\" checked>検収書受領　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c5\" value=\"5\">検収書受領　";
}
if ($c6 != 0){
	$html .= "<input type=\"checkbox\" name=\"c6\" value=\"6\" checked>初期請求　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c6\" value=\"6\">初期請求　";
}
if ($c7 != 0){
	$html .= "<input type=\"checkbox\" name=\"c7\" value=\"7\" checked>課金請求　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c7\" value=\"7\">課金請求　";
}
if ($c8 != 0){
	$html .= "<input type=\"checkbox\" name=\"c8\" value=\"8\" checked>入金";
}else{
	$html .= "<input type=\"checkbox\" name=\"c8\" value=\"8\">入金";
}
$html .= "</td><td class=\"title_td_s\" style=\"font-size: 11px;\">";
if ($c9 != 0){
	$html .= "<input type=\"checkbox\" name=\"c9\" value=\"9\" checked>初期支払 ";
}else{
	$html .= "<input type=\"checkbox\" name=\"c9\" value=\"9\">初期支払 ";
}
if ($c10 != 0){
	$html .= "<input type=\"checkbox\" name=\"c10\" value=\"10\" checked>課金支払 ";
}else{
	$html .= "<input type=\"checkbox\" name=\"c10\" value=\"10\">課金支払 ";
}
$html .= "</td>";

return $html;
}


function manage_check_project($c1,$c2,$c3,$c4,$c5,$c6,$c7,$c8,$c9,$c10,$all)
{

$html = "<td style=\"font-size: 11px;\">";
if ($all != 0){
	$html .= "<input type=\"checkbox\" name=\"all\" value=\"11\" onClick=\"allChange();\" checked>全て ";
}else{
	$html .= "<input type=\"checkbox\" name=\"all\" value=\"11\" onClick=\"allChange();\">全て ";
}
$html .= "</td><td class=\"title_td_p\" style=\"font-size: 11px;\">";
if ($c1 != 0){
	$html .= "<input type=\"checkbox\" name=\"c1\" value=\"1\" checked>検収　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c1\" value=\"1\">検収　";
}
//if ($c2 != 0){
//	$html .= "<input type=\"checkbox\" name=\"c2\" value=\"2\" checked>支払 ";
//}else{
//	$html .= "<input type=\"checkbox\" name=\"c2\" value=\"2\">支払 ";
//}
$html .= "</td><td class=\"title_td_c\" style=\"font-size: 11px;\">";
if ($c3 != 0){
	$html .= "<input type=\"checkbox\" name=\"c3\" value=\"3\" checked>検収　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c3\" value=\"3\">検収　";
}
if ($c4 != 0){
	$html .= "<input type=\"checkbox\" name=\"c4\" value=\"4\" checked>検収書送付　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c4\" value=\"4\">検収書送付　";
}
if ($c5 != 0){
	$html .= "<input type=\"checkbox\" name=\"c5\" value=\"5\" checked>検収書受領　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c5\" value=\"5\">検収書受領　";
}
//if ($c6 != 0){
//	$html .= "<input type=\"checkbox\" name=\"c6\" value=\"6\" checked>初期請求　";
//}else{
//	$html .= "<input type=\"checkbox\" name=\"c6\" value=\"6\">初期請求　";
//}
//if ($c7 != 0){
//	$html .= "<input type=\"checkbox\" name=\"c7\" value=\"7\" checked>課金請求　";
//}else{
//	$html .= "<input type=\"checkbox\" name=\"c7\" value=\"7\">課金請求　";
//}
//if ($c8 != 0){
//	$html .= "<input type=\"checkbox\" name=\"c8\" value=\"8\" checked>入金";
//}else{
//	$html .= "<input type=\"checkbox\" name=\"c8\" value=\"8\">入金";
//}
//$html .= "</td><td class=\"title_td_s\" style=\"font-size: 11px;\">";
//if ($c9 != 0){
//	$html .= "<input type=\"checkbox\" name=\"c9\" value=\"9\" checked>初期支払 ";
//}else{
//	$html .= "<input type=\"checkbox\" name=\"c9\" value=\"9\">初期支払 ";
//}
//if ($c10 != 0){
//	$html .= "<input type=\"checkbox\" name=\"c10\" value=\"10\" checked>課金支払 ";
//}else{
//	$html .= "<input type=\"checkbox\" name=\"c10\" value=\"10\">課金支払 ";
//}
$html .= "</td>";

return $html;
}

function f_finish_f ($cd, $fin_f)
{

	$html = "";

	if ($cd == 0){
		$html .= "<select name=\"p_ok[]\">";
	}else{
		$html .= "<select name=\"p_ok[]\" disabled>";
	}
	switch ($fin_f)
	{
		case 0:
			$html .= "<option value=\"0\" selected>--</option>\n";
			$html .= "<option value=\"1\">未</option>\n";
			$html .= "<option value=\"2\">完</option>\n";
			break;
		case 1:
			$html .= "<option value=\"0\">--</option>\n";
			$html .= "<option value=\"1\" selected>未</option>\n";
			$html .= "<option value=\"2\">完</option>\n";
			break;
		case 2:
			$html .= "<option value=\"0\">--</option>\n";
			$html .= "<option value=\"1\">未</option>\n";
			$html .= "<option value=\"2\" selected>完</option>\n";
			break;
	}
	$html .= "</select>";

return $html;
}

function f_finish_dsp ($fin_f)
{

	$html = "";
	switch ($fin_f)
	{
		case 0:
			$html .= "--\n";
			break;
		case 1:
			$html .= "未\n";
			break;
		case 2:
			$html .= "完\n";
			break;
	}

return $html;
}

function f_finish_f_s ($fin_f)
{

	$html = "";
	$html .= "<select name=\"d_ok\">";
	switch ($fin_f)
	{
		case 0:
			$html .= "<option value=\"0\" selected>--</option>\n";
			$html .= "<option value=\"1\">未</option>\n";
			$html .= "<option value=\"2\">完</option>\n";
			break;
		case 1:
			$html .= "<option value=\"0\">--</option>\n";
			$html .= "<option value=\"1\" selected>未</option>\n";
			$html .= "<option value=\"2\">完</option>\n";
			break;
		case 2:
			$html .= "<option value=\"0\">--</option>\n";
			$html .= "<option value=\"1\">未</option>\n";
			$html .= "<option value=\"2\" selected>完</option>\n";
			break;
	}
	$html .= "</select>";

return $html;
}



function manage_check ($c1,$c2,$c3,$c4,$c5,$c6,$c7,$c8,$c9,$c10,$all)
{

$html = "<td style=\"font-size: 11px;\">";
if ($all != 0){
	$html .= "<input type=\"checkbox\" name=\"all\" value=\"11\" onClick=\"allChange();\" checked>全て ";
}else{
	$html .= "<input type=\"checkbox\" name=\"all\" value=\"11\" onClick=\"allChange();\">全て ";
}
$html .= "</td><td class=\"title_td_p\" style=\"font-size: 11px;\">";
if ($c1 != 0){
	$html .= "<input type=\"checkbox\" name=\"c1\" value=\"1\" checked>検収　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c1\" value=\"1\">検収　";
}
if ($c2 != 0){
	$html .= "<input type=\"checkbox\" name=\"c2\" value=\"2\" checked>支払 ";
}else{
	$html .= "<input type=\"checkbox\" name=\"c2\" value=\"2\">支払 ";
}
$html .= "</td><td class=\"title_td_c\" style=\"font-size: 11px;\">";
if ($c3 != 0){
	$html .= "<input type=\"checkbox\" name=\"c3\" value=\"3\" checked>検収　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c3\" value=\"3\">検収　";
}
if ($c4 != 0){
	$html .= "<input type=\"checkbox\" name=\"c4\" value=\"4\" checked>検収書送付　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c4\" value=\"4\">検収書送付　";
}
if ($c5 != 0){
	$html .= "<input type=\"checkbox\" name=\"c5\" value=\"5\" checked>検収書受領　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c5\" value=\"5\">検収書受領　";
}
if ($c6 != 0){
	$html .= "<input type=\"checkbox\" name=\"c6\" value=\"6\" checked>初期請求　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c6\" value=\"6\">初期請求　";
}
if ($c7 != 0){
	$html .= "<input type=\"checkbox\" name=\"c7\" value=\"7\" checked>課金請求　";
}else{
	$html .= "<input type=\"checkbox\" name=\"c7\" value=\"7\">課金請求　";
}
if ($c8 != 0){
	$html .= "<input type=\"checkbox\" name=\"c8\" value=\"8\" checked>入金";
}else{
	$html .= "<input type=\"checkbox\" name=\"c8\" value=\"8\">入金";
}
$html .= "</td><td class=\"title_td_s\" style=\"font-size: 11px;\">";
if ($c9 != 0){
	$html .= "<input type=\"checkbox\" name=\"c9\" value=\"9\" checked>初期支払 ";
}else{
	$html .= "<input type=\"checkbox\" name=\"c9\" value=\"9\">初期支払 ";
}
if ($c10 != 0){
	$html .= "<input type=\"checkbox\" name=\"c10\" value=\"10\" checked>課金支払 ";
}else{
	$html .= "<input type=\"checkbox\" name=\"c10\" value=\"10\">課金支払 ";
}
$html .= "</td>";

return $html;
}

//指定日付から今日までの経過日数を返す
function remain_check ($d)
{
	$yyyy = substr($d, 0, 4);
	$mm = substr($d, 5, 2);
	$dd = substr($d, 8, 2);

	$one_day = 60 * 60 * 24; //1日の秒数

	$t1 = mktime(0, 0, 0, $mm, $dd, $yyyy); //現在のタイムスタンプ
	$t2 = time(); //目標日のタイムスタンプ

	if ($t1 < $t2) { //目標日の月日が今日より後の場合
		//タイムスタンプの差を日付に直し、切り上げて残日数を計算
		$remain_day = ceil (($t2 - $t1) / $one_day);
	} else { //目標日の月日が今日より前の場合（当日含む）
		$next_y = ++$yyyy ; //来年の年
		$t3 = mktime(0, 0, 0, $mm, $dd, $next_y); //来年の目標日のタイムスタンプ

		//タイムスタンプの差を日付に直し、切り上げて残日数を計算
		$remain_day = ceil (($t3 - $t1) / $one_day);

		//当日に365と表示されるのを0にする処理
		//当日に365と表示されてもよいなら下３行は不要
		$tukihi1 = date ('Ymd', $t1) ; //現在の月日
		$tukihi2 = date ('Ymd', $t2) ; //目標日の月日
		//カウントダウン当日：現在の月日と目標日の月日が同じなら0を代入
		$remain_day = ($tukihi1 != $tukihi2) ? $remain_day : 0;
	}

	return($remain_day);
}

//指定日から指定日までの経過日数を返す
function remain_check3 ($d1, $d2)
{
	$yyyy = substr($d1, 0, 4);
	$mm = substr($d1, 5, 2);
	$dd = substr($d1, 8, 2);
	$yyyy2 = substr($d2, 0, 4);
	$mm2 = substr($d2, 5, 2);
	$dd2 = substr($d2, 8, 2);

	$one_day = 60 * 60 * 24; //1日の秒数

	$t1 = mktime(0, 0, 0, $mm, $dd, $yyyy); //タイムスタンプ
	$t2 = mktime(0, 0, 0, $mm2, $dd2, $yyyy2); //タイムスタンプ
//	$t2 = time(); //目標日のタイムスタンプ

	if ($t1 < $t2) { //目標日の月日が今日より後の場合
		//タイムスタンプの差を日付に直し、切り上げて残日数を計算
		$remain_day = ceil (($t2 - $t1) / $one_day);
	} else { //目標日の月日が今日より前の場合（当日含む）
		$next_y = ++$yyyy ; //来年の年
		$t3 = mktime(0, 0, 0, $mm, $dd, $next_y); //来年の目標日のタイムスタンプ

		//タイムスタンプの差を日付に直し、切り上げて残日数を計算
		$remain_day = ceil (($t3 - $t1) / $one_day);

		//当日に365と表示されるのを0にする処理
		//当日に365と表示されてもよいなら下３行は不要
		$tukihi1 = date ('Ymd', $t1) ; //現在の月日
		$tukihi2 = date ('Ymd', $t2) ; //目標日の月日
		//カウントダウン当日：現在の月日と目標日の月日が同じなら0を代入
		$remain_day = ($tukihi1 != $tukihi2) ? $remain_day : 0;
	}

	return($remain_day);
}


//指定日から指定日までの経過月数と指定月・終了月を返す
function remain_check_month ($d1, $d2)
{
	$yyyy = substr($d1, 0, 4);
	$mm = substr($d1, 5, 2);

	$yyyy2 = substr($d2, 0, 4);
	$mm2 = substr($d2, 5, 2);

	if ($yyyy == $yyyy2){
		$mm_cnt = $mm2 - $mm + 1;
	}else{
		$mm_cnt = ($yyyy2 - $yyyy - 1) * 12;
		$mm_cnt += (12 - $mm + 1);
		$mm_cnt += $mm2;
	}

	$s_yyyy = $yyyy;
	$s_mm = $mm;
	$e_yyyy = $yyyy2;
	$e_mm = $mm2;

	return array($mm_cnt, $s_yyyy, $s_mm, $e_yyyy, $e_mm);
}

//今日から指定日付までの経過日数を返す
function remain_check2 ($d)
{
	$yyyy = substr($d, 0, 4);
	$mm = substr($d, 5, 2);
	$dd = substr($d, 8, 2);

	$one_day = 60 * 60 * 24; //1日の秒数

	$t2 = mktime(0, 0, 0, $mm, $dd, $yyyy); //現在のタイムスタンプ
	$t1 = time(); //目標日のタイムスタンプ

	if ($t1 < $t2) { //目標日の月日が今日より後の場合
		//タイムスタンプの差を日付に直し、切り上げて残日数を計算
		$remain_day = ceil (($t2 - $t1) / $one_day);
	} else { //目標日の月日が今日より前の場合（当日含む）
		$next_y = ++$yyyy ; //来年の年
		$t3 = mktime(0, 0, 0, $mm, $dd, $next_y); //来年の目標日のタイムスタンプ

		//タイムスタンプの差を日付に直し、切り上げて残日数を計算
		$remain_day = ceil (($t3 - $t1) / $one_day);

		//当日に365と表示されるのを0にする処理
		//当日に365と表示されてもよいなら下３行は不要
		$tukihi1 = date ('Ymd', $t1) ; //現在の月日
		$tukihi2 = date ('Ymd', $t2) ; //目標日の月日
		//カウントダウン当日：現在の月日と目標日の月日が同じなら0を代入
		$remain_day = ($tukihi1 != $tukihi2) ? $remain_day : 0;
	}

	return($remain_day);
}

function f_project_status_get($s_prj_code)
{
	$sql = "SELECT s_prj_status FROM t_project WHERE s_prj_code = '".$s_prj_code."'";
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		return $f_rows["s_prj_status"];
	}

	mysql_free_result($result);

return "";

}

function excel_put($l, $r)
{
	$tx[1] = "E";
	$tx[2] = "F";
	$tx[3] = "G";
//H 1Q
	$tx[4] = "I";
	$tx[5] = "J";
	$tx[6] = "K";
//L 1Q
	$tx[7] = "M";
	$tx[8] = "N";
	$tx[9] = "O";
//P 3Q
	$tx[10] = "Q";
	$tx[11] = "R";
	$tx[12] = "S";
//T 4Q
	$tx[13] = "U";

	$cel = $tx[$r].$l;
	return ($cel);
}

function log_insert($user_name, $access, $latitude, $longitude, $ua, $ipaddress)
{

	$stat = 0;

// 端末
	if((strpos($ua,'iPhone')!==false)){
		$terminal = "iphone";
	}else if ((strpos($ua,'iPod')!==false)){
		$terminal = "ipod";
	}else if ((strpos($ua,'Windows Phone')!==false)){
		$terminal = "windows phone";
	}else if ((strpos($ua,'Android')!==false)) {
		if ((strpos($ua,'Mobile')!==false)){
			$terminal = "android phone";
		}else{
			$terminal = "android tablet";
		}
	}else if((strpos($ua,'iPad')!==false)){
		$terminal = "ipad";
	}else{
		$terminal = "pc";
	}

// キャリア
	if (preg_match("/^DoCoMo\//", $ua)) {
		$career = "docomo";
	} else if (preg_match("/^(J\-PHONE|Vodafone|MOT\-[CV]980|SoftBank)\//", $ua)) {
		$career = "softbank";
	} else if (preg_match("/^KDDI\-|UP\.Browser/", $ua)) {
		$career = "au";
	} else if (preg_match("/^PDXGW\/|DDIPOCKET;|WILLCOM;/", $ua)) {
		$career = "willcom";
	} else if (preg_match("/^emobile\//", $ua)) {
		$career = "emobile";
	} else {
		$career = "pc";
	}

//	$sql = "INSERT INTO t_log VALUES(NULL, '".char_chg("to_db", $user_name)."', '".char_chg("to_db", $access)."', '".$latitude."', '".$longitude."', '".$career."', '".$terminal."', '".$ua."', '".$ipaddress."', NULL, NULL, 0)";
	$sql = "INSERT INTO t_log VALUES(NULL, CURRENT_TIMESTAMP, '".char_chg("to_db", $user_name)."', '".char_chg("to_db", $access)."', '".$latitude."', '".$longitude."', '".$career."', '".$terminal."', '".$ua."', NULL, NULL, 0, '".$ipaddress."')";
  	$result = executeQuery($sql);
	if( $result === true ){
	}else{
		$stat = 1;
	}

	return $stat;
}


function on_off($cd)
{

if ($cd == 0){
	$stat = "<font color=\"#ff0000\">OFF</font>";
}else{
	$stat = "<font color=\"#0000ff\">ON</font>";
}

return $stat;
}

function sumi_mi($cd)
{

if ($cd == 1){
	$stat = "<font color=\"#ff0000\">未</font>";
}else if ($cd == 2){
	$stat = "<font color=\"#0000ff\">済</font>";
}else{
	$stat = "-";
}

return $stat;
}

function char_insert($str)
{
	$insert = "";
	//mb_internal_encoding('UTF-8'); // 環境に合わせて
	$len = mb_strlen($str);
	for ($i = 0; $i <$len; $i++) {
		if(preg_match("/^[a-zA-Z0-9]+$/", mb_substr($str, $i, 1))){
    			$insert .= mb_substr($str, $i, 1);
		} else {
    			$insert .= "\\".mb_substr($str, $i, 1);
		}
	}

return($insert);
}

//いけるぞ！　リストボックス表示
function f_ikeru_select($f_kaiyaku)
{
	$ikeru_html = "";
	$ikeru_html .= "<select name=\"ikeru\">";
	switch ($f_kaiyaku)
	{
		case 0:
			$ikeru_html .= "<option value=\"0\" selected>わかんな～い</option>\n";
			$ikeru_html .= "<option value=\"1\">ムリっぽいわ</option>\n";
			$ikeru_html .= "<option value=\"2\">イケるわよ☆</option>\n";
			$ikeru_html .= "<option value=\"3\">アポとれたわよ</option>\n";
			break;
		case 1:
			$ikeru_html .= "<option value=\"0\">わかんな～い</option>\n";
			$ikeru_html .= "<option value=\"1\" selected>ムリっぽいわ</option>\n";
			$ikeru_html .= "<option value=\"2\">イケるわよ☆</option>\n";
			$ikeru_html .= "<option value=\"3\">アポとれたわよ</option>\n";
			break;
		case 2:
			$ikeru_html .= "<option value=\"0\">わかんな～い</option>\n";
			$ikeru_html .= "<option value=\"1\">ムリっぽいわ</option>\n";
			$ikeru_html .= "<option value=\"2\" selected>イケるわよ☆</option>\n";
			$ikeru_html .= "<option value=\"3\">アポとれたわよ</option>\n";
			break;
		case 3:
			$ikeru_html .= "<option value=\"0\">わかんな～い</option>\n";
			$ikeru_html .= "<option value=\"1\">ムリっぽいわ</option>\n";
			$ikeru_html .= "<option value=\"2\">イケるわよ☆</option>\n";
			$ikeru_html .= "<option value=\"3\" selected>アポとれたわよ</option>\n";
			break;
	}
	$ikeru_html .= "</select>";

return $ikeru_html;

}
//いけるぞ！　リストボックス表示
function f_ikeru_dsp($f_kaiyaku)
{
	switch ($f_kaiyaku)
	{
		case 0:
			$ikeru_html = "わかんな～い";
			break;
		case 1:
			$ikeru_html = "ムリっぽいわ";
			break;
		case 2:
			$ikeru_html = "イケるわよ☆";
			break;
		case 3:
			$ikeru_html = "アポとれたわよ";
			break;
	}

return $ikeru_html;

}

function get_month($y, $date)
{
	$m = 0;

	$year = intval(substr($date, 0, 4));

//	if ($y == $year)
//	{
		$m = intval(substr($date, 5, 2));
//	}


return $m;
	
}

function message_create($loginstaff, $windows8)
{

require_once 'HTTP.php';

$f_date = computeDate(-7);
$t_date = computeDate(0);
$m_date = computeDate(-30);
$message_html = "";

	$alert_cnt = 0;
	$err_cnt = 0;
	$war_cnt = 0;
	$rank_cnt = 0;
	$winboard_cnt = 0;
	for ($ii = 0; $ii < 100; $ii ++)
	{
		$alert_inf[$ii][0] = "";
		$alert_inf[$ii][1] = 0;
		$err_inf[$ii][0] = "";
		$err_inf[$ii][1] = 0;
		$war_inf[$ii][0] = "";
		$war_inf[$ii][1] = 0;
		$rank_inf[$ii][1] = 0;
		$winboard_inf[$ii] = "";
	}

	$sql = "SELECT * FROM t_project WHERE s_prj_status = '1' or s_prj_status = '2'";

	$result = executeQuery($sql);
	$rows = mysql_num_rows($result);

	//表示するデータを作成
	if($rows){
		while($row = mysql_fetch_array($result)) {

			$tmp_tantou = char_chg("to_dsp", $row["s_prj_tantou"]);

			// 活動中のプロジェクトをチェック
			if ($row["s_prj_status"] == 1){
				$memo_d = f_project_memo_date_get($row["s_prj_code"]);
				$memo_d2 = f_customer_memo_date_get($row["s_customer_no"]);

				$busho = f_staff_busho_id_get($tmp_tantou);

				if ($memo_d > $memo_d2)
				{
					if ($memo_d <= $m_date){
						$flag = 0;
						for ($ii = 0; $ii < $alert_cnt; $ii ++)
						{
							if ($alert_inf[$ii][0] === $tmp_tantou)
							{
								$alert_inf[$ii][1] ++;
								$flag = 1;
								break;
							}
						}
						if ($flag == 0)
						{
							$alert_inf[$alert_cnt][0] = $tmp_tantou;
							$alert_inf[$alert_cnt][1] ++;
							$alert_cnt ++;
						}
					}
				}else{
					if ($memo_d2 <= $m_date)
					{
						$flag = 0;
						for ($ii = 0; $ii < $alert_cnt; $ii ++)
						{
							if ($alert_inf[$ii][0] === $tmp_tantou)
							{
								$alert_inf[$ii][1] ++;
								$flag = 1;
								break;
							}
						}
						if ($flag == 0)
						{
							$alert_inf[$alert_cnt][0] = $tmp_tantou;
							$alert_inf[$alert_cnt][1] ++;
							$alert_cnt ++;
						}
					}
				}

				if (($row["s_prj_mikomi_jyuchu_date"] <= $t_date && $row["s_prj_mikomi_jyuchu_date"] != "0000-00-00") || ($row["s_prj_mikomi_kakin_date"] <= $t_date && $row["s_prj_mikomi_kakin_date"] != "0000-00-00") || ($row["s_prj_jyuchu_shoki_date"] <= $t_date && $row["s_prj_jyuchu_shoki_date"] != "0000-00-00") || ($row["s_prj_jyuchu_kakin_date"] <= $t_date && $row["s_prj_jyuchu_kakin_date"] != "0000-00-00"))
				{
						$flag = 0;
						for ($ii = 0; $ii < $err_cnt; $ii ++)
						{
							if ($err_inf[$ii][0] === $tmp_tantou)
							{
								$err_inf[$ii][1] ++;
								$flag = 1;
								break;
							}
						}
						if ($flag == 0)
						{
							$err_inf[$err_cnt][0] = $tmp_tantou;
							$err_inf[$err_cnt][1] ++;
							$err_cnt ++;
						}
				}

				//ランク未入力
				if ($row["s_prj_rank"] == 0)
				{
						$flag = 0;
						for ($ii = 0; $ii < $rank_cnt; $ii ++)
						{
							if ($rank_inf[$ii][0] === $tmp_tantou)
							{
								$rank_inf[$ii][1] ++;
								$flag = 1;
								break;
							}
						}
						if ($flag == 0)
						{
							$rank_inf[$rank_cnt][0] = $tmp_tantou;
							$rank_inf[$rank_cnt][1] ++;
							$rank_cnt ++;
						}
				}
				//区分未入力
				if ($row["s_prj_kubun"] == 0)
				{
						$flag = 0;
						for ($ii = 0; $ii < $war_cnt; $ii ++)
						{
							if ($war_inf[$ii][0] === $tmp_tantou)
							{
								$war_inf[$ii][1] ++;
								$flag = 1;
								break;
							}
						}
						if ($flag == 0)
						{
							$war_inf[$war_cnt][0] = $tmp_tantou;
							$war_inf[$war_cnt][1] ++;
							$war_cnt ++;
						}
				}
			}
		}
	}

$skip_f = 0;

		//アラートメッセージ
		if ($war_cnt != 0)
		{
			for ($ii = 0; $ii < $war_cnt; $ii ++)
			{
				if ($war_inf[$ii][0] === $loginstaff){
					$skip_f = 3;
					goto skip_redirect;
				}
			}
		}

		//エラーメッセージ
		if ($err_cnt != 0)
		{
			for ($ii = 0; $ii < $err_cnt; $ii ++)
			{
						if ($err_inf[$ii][0] === $loginstaff){
							$skip_f = 1;
							goto skip_redirect;
						}
			}
		}

		//アラートメッセージ
		if ($alert_cnt != 0)
		{
			for ($ii = 0; $ii < $alert_cnt; $ii ++)
			{
				if ($alert_inf[$ii][0] === $loginstaff){
					$skip_f = 2;
					goto skip_redirect;
				}
			}
		}

		//ランク未入力メッセージ
		if ($rank_cnt != 0)
		{
			for ($ii = 0; $ii < $rank_cnt; $ii ++)
			{
				if ($rank_inf[$ii][0] === $loginstaff){
					$skip_f = 4;
					goto skip_redirect;
				}
			}
		}

skip_redirect:

	$staff_id = f_staff_id_get($loginstaff);

	if ($skip_f == 1){
HTTP::redirect("../sales/project/alert.php?id=".$staff_id);
	}

if ($skip_f == 2){
HTTP::redirect("../sales/project/alert2.php?id=".$staff_id);
}

if ($skip_f == 3){
HTTP::redirect("../sales/project/alert3.php?id=".$staff_id);
}

if ($skip_f == 4){
HTTP::redirect("../sales/project/alert4.php?id=".$staff_id);
}

	return $message_html;

}

function new_insert($user_name, $menu, $function_d)
{

	$stat = 0;

	$sql = "INSERT INTO t_new VALUES(NULL, NULL, '".char_chg("to_db", $user_name)."', '".char_chg("to_db", $menu)."', '".$function_d."')";
  	$result = executeQuery($sql);
	if( $result === true ){
	}else{
		$stat = 1;
	}

	return $stat;
}

//tmp情報GET
function tmp_get()
{
	$sales_total_senshu = 0;
	$chakuchi_zenjitsu = 0;
	$chakuchi_date = "";
	$sales_total_senshu_jitsu = 0;
	
	$sql = "SELECT * FROM t_tmp where cd = 1";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$chakuchi_date = $f_rows["chakuchi_date"];
		$chakuchi_zenjitsu = $f_rows["chakuchi_zenjitsu"];

		$sales_total_senshu = $f_rows["sales_total_senshu"];
		$sales_total_senshu_jitsu = $f_rows["sales_total_senshu_jitsu"];
	}

	mysql_free_result($result);

return array( $sales_total_senshu, $chakuchi_zenjitsu, $chakuchi_date, $sales_total_senshu_jitsu );

}

//WBバッチからしか呼ばれない
function tmp_put($tmp1, $tmp2, $tmp3)
{
	$stat = 0;

	if (date('w') == 5){	//金曜日にだけ記録

		if ($tmp1 != 0){
			$sql = "UPDATE t_tmp SET sales_total_senshu = '".$tmp1."' where cd = 1";
			$result = executeQuery($sql);
		}
		
		if ($tmp2 != 0){
			$chakuchi_date = date("Y-m-d");
			$sql = "UPDATE t_tmp SET chakuchi_zenjitsu = '".$tmp2."', chakuchi_date = '".$chakuchi_date."' where cd = 1";
			$result = executeQuery($sql);
		}

		if ($tmp3!= 0){
			$sql = "UPDATE t_tmp SET sales_total_senshu_jitsu = '".$tmp3."' where cd = 1";
			$result = executeQuery($sql);
		}

	}

return $stat;

}

//状況　リストボックス表示　テレアポ
function f_status_select2($f_status)
{
	$status_html = "";
	$status_html .= "<select id=\"s_prj_status\" name=\"s_prj_status\">";
	switch ($f_status)
	{
		case 0:
			$status_html .= "<option value=\"0\" selected>----</option>\n";
			$status_html .= "<option value=\"4\">TEL追跡中</option>\n";
			$status_html .= "<option value=\"5\">TEL断念</option>\n";
			break;
		case 4:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"4\" selected>TEL追跡中</option>\n";
			$status_html .= "<option value=\"5\">TEL断念</option>\n";
			break;
		case 5:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"4\">TEL追跡中</option>\n";
			$status_html .= "<option value=\"5\" selected>TEL断念</option>\n";
			break;
	}
	$status_html .= "</select>";

return $status_html;

}

//指定したyyyy年mm月の祝日を取得
function getHolidays($year, $m, $country) {

	$holidays = array();
/*
	//Googleカレンダーから、指定年の祝日情報をJSON形式で取得するためのURL
	$query = 'alt=json&start-min=' . $year . '-01-01' . '&start-max=' . $year . '-12-31';
	if ($country == 'ja') { //日本
		$url = 'http://www.google.com/calendar/feeds/japanese__ja%40holiday.calendar.google.com/public/full?';
	} elseif ($country == 'us') { //アメリカ
		$url = 'http://www.google.com/calendar/feeds/usa__ja@holiday.calendar.google.com/public/full?';
	} elseif ($country == 'sg') { //シンガポール
		$url = 'http://www.google.com/calendar/feeds/singapore__ja@holiday.calendar.google.com/public/full?';
	} elseif ($country == 'tw') { //台湾
		$url = 'http://www.google.com/calendar/feeds/taiwan__ja@holiday.calendar.google.com/public/full?';
	} elseif ($country == 'uk') { //イギリス
		$url = 'http://www.google.com/calendar/feeds/uk__ja@holiday.calendar.google.com/public/full?';
	}
	//JSON形式で取得した情報を配列に変換
	$results = json_decode(file_get_contents($url . $query), true);

 	$i = 0;
	foreach($results['feed']['entry'] as $val) {
		$date = $val['gd$when'][0]['startTime']; // 日付を取得
		if (substr($date, 5, 2) == $m){
			$holidays[$i++] = $date; // 番号をキーに日付を値に格納
		}
	}
*/
	//配列として祝日を返す
	return $holidays;
}


function f_kind_check($name, $value) {

	if ($value == 1){
		$html = "<input type=\"checkbox\" name=\"".$name."\" value=\"1\" checked>";
	}else{
		$html = "<input type=\"checkbox\" name=\"".$name."\" value=\"1\">";
	}
	return $html;
}

function f_keiyaku_checkbox($val, $name, $caption)
{
	if ($val == 1){
		$keiyaku_html = "<input type=\"checkbox\" name=\"".$name."\" value=\"1\" checked> ".$caption;
	}else{
		$keiyaku_html = "<input type=\"checkbox\" name=\"".$name."\" value=\"1\"> ".$caption;
	}
return $keiyaku_html;
}

function f_keiyaku_checkbox_dsp($val, $name, $caption)
{
	if ($val == 1){
		$keiyaku_html = "<input type=\"checkbox\" name=\"".$name."\" value=\"1\" checked=\"checked\" disabled=\"disabled\"> ".$caption;
	}else{
		$keiyaku_html = "<input type=\"checkbox\" name=\"".$name."\" value=\"1\" disabled=\"disabled\"> ".$caption;
	}
return $keiyaku_html;
}
//契約情報の取得
function f_keiyaku_info_get( $no )
{

		$k1 =  0;
		$k2 =  100;
		$k3 =  100;
		$k4 =  10000;
		$k5 =  0;
		$k6 =  0;
		$k7 =  0;
		$k8 =  0;
		$k9 = 0;
		$k10 = 0;
		$k11 =  "";
		$k12 =  "";
		$k13 =  0;
		$k14 = "";
		$k15 = "";
		$k16 =  0;
		$k17 =  0;
		$k18 =  "";
		$k19 =  "";
		$k20 =  "";


  $sql = "SELECT * FROM t_customer_info WHERE no = ".$no;
  $result = executeQuery($sql);

  $rows = mysql_num_rows($result);

  if($rows){
    $row = mysql_fetch_array($result);

    $k1 = $row["keiyaku1"];
    $k2 = $row["keiyaku2"];
    $k3 = $row["keiyaku3"];
    $k4 = $row["keiyaku4"];
    $k5 = $row["keiyaku5"];
    $k6 = $row["keiyaku6"];
    $k7 = $row["keiyaku7"];
    $k8 = $row["keiyaku8"];
    $k9 = $row["keiyaku9"];
    $k10 = $row["keiyaku10"];
    $k11 = $row["keiyaku11"];
    $k12 = $row["keiyaku12"];
    $k13 = $row["keiyaku13"];
    $k14 = $row["keiyaku14"];
    $k15 = $row["keiyaku15"];
    $k16 = $row["keiyaku16"];
    $k17 = $row["keiyaku17"];
    $k18 = $row["keiyaku18"];
    $k19 = $row["keiyaku19"];
    $k20 = $row["keiyaku20"];
  }

  return array("keiyaku1" => $k1, "keiyaku2" => $k2, "keiyaku3" => $k3, "keiyaku4" => $k4, "keiyaku5" => $k5, "keiyaku6" => $k6, "keiyaku7" => $k7, "keiyaku8" => $k8, "keiyaku9" => $k9, "keiyaku10" => $k10, "keiyaku11" => $k11, "keiyaku12" => $k12, "keiyaku13" => $k13, "keiyaku14" => $k14, "keiyaku15" => $k15, "keiyaku16" => $k16, "keiyaku17" => $k17, "keiyaku18" => $k18, "keiyaku19" => $k19, "keiyaku20" => $k20);
}

//未対応残件数
function f_no_check_cnt_get()
{
	$num = 0;


	$sql = "SELECT count(*) FROM t_customer_memo WHERE check_befor = 1 and check_after = 0";
	$rnum1 = executeQuery($sql);
	list($num1) = mysql_fetch_row($rnum1);
	mysql_free_result($rnum1);

	$sql = "SELECT count(*) FROM t_project_memo WHERE check_befor = 1 and check_after = 0";
	$rnum2 = executeQuery($sql);
	list($num2) = mysql_fetch_row($rnum2);
	mysql_free_result($rnum2);

return $num1+$num2;
}

function f_date_memo_cnt($serch_date)
{

	$sql_customer = "SELECT count(*) FROM t_customer_memo WHERE create_date LIKE '%".$serch_date."%'";
	$result_customer = executeQuery($sql_customer);
	list($num_customer) = mysql_fetch_row($result_customer);

	$sql_project = "SELECT count(*) FROM t_project_memo WHERE create_date LIKE '%".$serch_date."%'";
	$result_project = executeQuery($sql_project);
	list($num_project) = mysql_fetch_row($result_project);

	$num = $num_customer + $num_project;

	return $num;
}

//スタッフ名から拠点コード取得
function f_staff_kyoten_get($name)
{
	$kyoten_no = 0;
	$sql = "SELECT kyoten FROM m_user_detail WHERE name = '".char_chg("to_db", $name)."'";

	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);

	if($f_rows){
		$f_rows = mysql_fetch_array($result);
		$kyoten_no = $f_rows["kyoten"];
	}

	mysql_free_result($result);

return $kyoten_no;

}

$h = "";
// スタッフマスタ　セレクトボックス　作成 ajax対応 スタッフ名
function f_master_org_staff_select_name($hit, $id)
{

	global $h;
	$data = array();

//	$sql = "SELECT * FROM m_organization_detail ORDER BY dsp DESC";
	$sql = "SELECT * FROM m_organization_detail left join m_organization on m_organization_detail.organization_id = m_organization.organization_id ORDER BY m_organization.disp_order DESC";

	$result = executeQuery($sql);
	$rows = mysql_num_rows($result);
	$ii = 0;
	if($rows){
		while($row = mysql_fetch_array($result)) {
			$data[$ii][0] = $row['organization_detail_id'];
			$data[$ii][1] = $row['upper_level_organization'];
			$data[$ii][2] = $row['organization_name'];
			$ii ++;
		}
	}

	$h = "<select name=\"".$id."\" class=\"".$id."\">\n";
	$h .= "<option value=\"0\">---</option>\n";

	$roots  = array();
	$broths = array();
	$childs = array();
	$texts  = array();

	foreach ($data as $log) {
		list($no, $pno, $text) = $log;

		if ($pno == 0) {
			$roots[] = $no;
		}else{
			$broths[$no]  = isset($childs[$pno]) ? $childs[$pno] : 0;
			$childs[$pno] = $no;
		}
		$texts[$no] = $text;
	}
	sort($roots);

	foreach ($roots as $root) {
		put_tree_staff_name($root, '', $broths, $childs, $texts, $hit);
	}

	$h .= "</select>\n";

return $h;

}

function put_tree_staff_name($no, $line, $broths, $childs, $texts, $hit) {
	global $h;

	$h .= "<option value=\"".$no."\">".$line." ".char_chg("to_dsp", $texts[$no])."</option>\n";

	$sql = "SELECT name, user_id FROM m_user_detail WHERE organization_id = '".org_id_code_get($no)."' ORDER BY organization_id, rank DESC";
	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);
	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			$line2 = preg_replace("/├$/", "&nbsp;|&nbsp;", $line);
			$line2 = preg_replace("/└$/", "&nbsp;&nbsp;", $line2);
			if ($f_rows["name"] == $hit){
				$h .= "<option value=\"".char_chg("to_dsp", $f_rows["name"])."\" selected>".$line2."&nbsp;&nbsp;└".char_chg("to_dsp", $f_rows["name"])."</option>\n";
			}else{
				$h .= "<option value=\"".char_chg("to_dsp", $f_rows["name"])."\">".$line2."&nbsp;&nbsp;└".char_chg("to_dsp", $f_rows["name"])."</option>\n";
    			}
		
		}
	}
	mysql_free_result($result);


	$line = preg_replace("/├$/", "&nbsp;|&nbsp;", $line);
	$line = preg_replace("/└$/", "&nbsp;&nbsp;", $line);

	$no = isset($childs[$no]) ? $childs[$no] : 0;

	while ($no > 0) {
		$tail = $broths[$no] ? "&nbsp;&nbsp;&nbsp;├" : "&nbsp;&nbsp;&nbsp;└";
		put_tree_staff_name($no, $line . $tail, $broths, $childs, $texts, $hit);
		$no = $broths[$no];
	}
}

// スタッフマスタ　セレクトボックス　作成 ajax対応 スタッフコード

function f_master_org_staff_select($hit, $id)
{

	global $h;
	$data = array();

//	$sql = "SELECT * FROM m_organization_detail ORDER BY dsp DESC";
	$sql = "SELECT * FROM m_organization_detail left join m_organization on m_organization_detail.organization_id = m_organization.organization_id ORDER BY m_organization.disp_order DESC";

	$result = executeQuery($sql);
	$rows = mysql_num_rows($result);
	$ii = 0;
	if($rows){
		while($row = mysql_fetch_array($result)) {
			$data[$ii][0] = $row['organization_detail_id'];
			$data[$ii][1] = $row['upper_level_organization'];
			$data[$ii][2] = $row['organization_name'];
			$ii ++;
		}
	}

	$h = "<select name=\"".$id."\" class=\"".$id."\">\n";
	$h .= "<option value=\"0\">---</option>\n";

	$roots  = array();
	$broths = array();
	$childs = array();
	$texts  = array();

	foreach ($data as $log) {
		list($no, $pno, $text) = $log;

		if ($pno == 0) {
			$roots[] = $no;
		}else{
			$broths[$no]  = isset($childs[$pno]) ? $childs[$pno] : 0;
			$childs[$pno] = $no;
		}
		$texts[$no] = $text;
	}
	sort($roots);

	foreach ($roots as $root) {
		put_tree_staff($root, '', $broths, $childs, $texts, $hit);
	}

	$h .= "</select>\n";

return $h;

}

function put_tree_staff($no, $line, $broths, $childs, $texts, $hit) {
	global $h;

	$h .= "<option value=\"".$no."\">".$line." ".char_chg("to_dsp", $texts[$no])."</option>\n";

	$sql = "SELECT name, user_id FROM m_user_detail WHERE organization_id = '".org_id_code_get($no)."' ORDER BY organization_id, rank DESC";

	$result = executeQuery($sql);
	$f_rows = mysql_num_rows($result);
	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
	$line2 = preg_replace("/├$/", "&nbsp;|&nbsp;", $line);
	$line2 = preg_replace("/└$/", "&nbsp;&nbsp;", $line2);
			if ($f_rows["user_id"] == $hit){
				$h .= "<option value=\"".$f_rows["user_id"]."\" selected>".$line2."&nbsp;&nbsp;└".char_chg("to_dsp", $f_rows["name"])."</option>\n";
			}else{
				$h .= "<option value=\"".$f_rows["user_id"]."\">".$line2."&nbsp;&nbsp;└".char_chg("to_dsp", $f_rows["name"])."</option>\n";
    			}
		
		}
	}
	mysql_free_result($result);


	$line = preg_replace("/├$/", "&nbsp;|&nbsp;", $line);
	$line = preg_replace("/└$/", "&nbsp;&nbsp;", $line);

	$no = isset($childs[$no]) ? $childs[$no] : 0;

	while ($no > 0) {
		$tail = $broths[$no] ? "&nbsp;&nbsp;&nbsp;├" : "&nbsp;&nbsp;&nbsp;└";
		put_tree_staff($no, $line . $tail, $broths, $childs, $texts, $hit);
		$no = $broths[$no];
	}
}
// ajax組織→社員名　初期表示
function f_user_select()
{
	$html = "<select name=\"user_list\" class=\"user_list\">\n";
	$html .= "</select>\n";

return $html;

}
// ajax組織→社員名　初期表示
function f_user_select_name($name)
{
	$html = "<select name=\"".$name."\" class=\"".$name."\">\n";
	$html .= "</select>\n";

return $html;

}

// 組織マスタ　セレクトボックス　作成 ajax対応
function f_master_org_select_name($name, $hit, $id)
{
//        $schema = "use";
	$schema = $_SESSION["SCHEMA"];

	global $h;
	$data = array();

//	$sql = "SELECT * FROM m_organization_detail ORDER BY dsp DESC";
//	$sql = "SELECT * FROM ".$schema.".m_organization_detail left join ".$schema.".m_organization on ".$schema.".m_organization_detail.organization_id = ".$schema.".m_organization.organization_id ORDER BY ".$schema.".m_organization.disp_order DESC";
	$sql = "SELECT * FROM ".$schema.".m_organization_detail left join ".$schema.".m_organization on ".$schema.".m_organization_detail.organization_id = ".$schema.".m_organization.organization_id where ".$schema.".m_organization.is_del = 0 ORDER BY ".$schema.".m_organization.disp_order DESC";
	$cnt = 0;
        $rows = getList($sql);
	$ii = 0;
	if($rows){
		while($row = $rows[$cnt]) {
			$data[$ii][0] = $row['organization_detail_id'];
			$data[$ii][1] = $row['upper_level_organization'];
			$data[$ii][2] = $row['organization_name'];
			$ii ++;
			$cnt += 1;
		}
	}

	$h = "<select name=\"".$id."\" class=\"".$id."\" onchange=\"".$name."\">\n";
	$h .= "<option value=\"0\">---</option>\n";

	$roots  = array();
	$broths = array();
	$childs = array();
	$texts  = array();

	foreach ($data as $log) {
		list($no, $pno, $text) = $log;

		if ($pno == 0) {
			$roots[] = $no;
		}else{
			$broths[$no]  = isset($childs[$pno]) ? $childs[$pno] : 0;
			$childs[$pno] = $no;
		}
		$texts[$no] = $text;
	}
	sort($roots);

	foreach ($roots as $root) {
		put_tree($root, '', $broths, $childs, $texts, $hit);
	}

	$h .= "</select>\n";

return $h;

}

// 組織マスタ　セレクトボックス　作成 ajax対応
function f_master_org_select($hit, $id)
{
//        $schema = "use";
	$schema = $_SESSION["SCHEMA"];

	global $h;
	$data = array();

//	$sql = "SELECT * FROM m_organization_detail ORDER BY dsp DESC";
//	$sql = "SELECT * FROM ".$schema.".m_organization_detail left join ".$schema.".m_organization on ".$schema.".m_organization_detail.organization_id = ".$schema.".m_organization.organization_id ORDER BY ".$schema.".m_organization.disp_order DESC";
	$sql = "SELECT * FROM ".$schema.".m_organization_detail left join ".$schema.".m_organization on ".$schema.".m_organization_detail.organization_id = ".$schema.".m_organization.organization_id where ".$schema.".m_organization.is_del = 0 ORDER BY ".$schema.".m_organization.disp_order DESC";
	$cnt = 0;
        $rows = getList($sql);
	$ii = 0;
	if($rows){
		while($row = $rows[$cnt]) {
			$data[$ii][0] = $row['organization_detail_id'];
			$data[$ii][1] = $row['upper_level_organization'];
			$data[$ii][2] = $row['organization_name'];
			$ii ++;
			$cnt += 1;
		}
	}

	$h = "<select name=\"".$id."\" class=\"".$id."\" onchange=\"org_sel()\">\n";
	$h .= "<option value=\"0\">---</option>\n";

	$roots  = array();
	$broths = array();
	$childs = array();
	$texts  = array();

	foreach ($data as $log) {
		list($no, $pno, $text) = $log;

		if ($pno == 0) {
			$roots[] = $no;
		}else{
			$broths[$no]  = isset($childs[$pno]) ? $childs[$pno] : 0;
			$childs[$pno] = $no;
		}
		$texts[$no] = $text;
	}
	sort($roots);

	foreach ($roots as $root) {
		put_tree($root, '', $broths, $childs, $texts, $hit);
	}

	$h .= "</select>\n";

return $h;

}

function put_tree($no, $line, $broths, $childs, $texts, $hit) {
	global $h;

	if ($no == $hit){
		$h .= "<option value=\"".$no."\" selected>".$line." ".char_chg("to_dsp", $texts[$no])."</option>\n";
//		$h .= "<option value=\"".$no."\" selected>".$line." ".$texts[$no]."</option>\n";
	}else{
		$h .= "<option value=\"".$no."\">".$line." ".char_chg("to_dsp", $texts[$no])."</option>\n";
//		$h .= "<option value=\"".$no."\">".$line." ".$texts[$no]."</option>\n";
	}

	$line = preg_replace("/├$/", "&nbsp;|&nbsp;", $line);
	$line = preg_replace("/└$/", "&nbsp;&nbsp;", $line);

	$no = isset($childs[$no]) ? $childs[$no] : 0;

	while ($no > 0) {
		$tail = $broths[$no] ? "&nbsp;&nbsp;&nbsp;├" : "&nbsp;&nbsp;&nbsp;└";
		put_tree($no, $line . $tail, $broths, $childs, $texts, $hit);
		$no = $broths[$no];
	}
}

//org_idから部署名取得
function org_name_dsp($id)
{
	$name = "";
	$sql = "SELECT organization_name FROM ".$_SESSION["SCHEMA"].".m_organization_detail WHERE organization_id = '".$id."'";
        $result = getList($sql);
	$f_rows = $result[0];
	if($f_rows){
		$name = $f_rows["organization_name"];
	}


return $name;

}

//organization_detail_idからorganization_id取得
function detail_org_id_get($id)
{
	$org_id = 0;
	$sql = "SELECT organization_id FROM ".$_SESSION["SCHEMA"].".m_organization_detail WHERE organization_detail_id = '".$id."'";

        $result = getList($sql);
	$f_rows = $result[0];

	if($f_rows){
		$org_id = $f_rows["organization_id"];
	}

return $org_id;

}

//department_codeからorganization_detail_id取得
function code_org_id_get2($id)
{
	$org_id = 0;
	$sql = "SELECT organization_detail_id FROM ".$_SESSION["SCHEMA"].".m_organization_detail WHERE organization_id = '".$id."'";

        $result = getList($sql);
	$f_rows = $result[0];

	if($f_rows){
		$org_id = $f_rows["organization_detail_id"];
	}

return $org_id;

}

//department_codeからorganization_detail_id取得
function code_org_id_get($id)
{
	$org_id = 0;
	$sql = "SELECT organization_detail_id FROM ".$_SESSION["SCHEMA"].".m_organization_detail WHERE department_code = '".$id."'";

        $result = getList($sql);
	$f_rows = $result[0];

	if($f_rows){
		$org_id = $f_rows["organization_detail_id"];
	}

return $org_id;

}

//org_idからcode取得
function org_id_code_get($id)
{
	$code = "";
	$sql = "SELECT department_code FROM ".$_SESSION["SCHEMA"].".m_organization_detail WHERE organization_detail_id = ".$id;
        $result = getList($sql);
	$f_rows = $result[0];
	if($f_rows){
		$code = $f_rows["department_code"];
	}

return $code;

}

//code(組織コード)から親か自部署名取得
//$parent 0 自部署
//        1 親部署
function code_name_dsp($parent, $id)
{
	$name = "";
	$parent_id = "";
	$sql = "SELECT organization_name, upper_level_organization FROM ".$_SESSION["SCHEMA"].".m_organization_detail WHERE department_code = '".$id."'";
        $result = getList($sql);
	$f_rows = $result[0];
	if($f_rows){
		$name = $f_rows["organization_name"];
		$parent_id = $f_rows["upper_level_organization"];
	}

	if ($parent == 1 && $parent_id != 0){	//親を取得
		$sql = "SELECT organization_name FROM ".$_SESSION["SCHEMA"]."m_organization_detail WHERE organization_detail_id = ".$parent_id;
	        $result = getList($sql);
		$f_rows = $result[0];
		if($f_rows){
			$name = $f_rows["organization_name"];
		}
	}

return $name;

}

//スタッフ名から部署コード取得
function f_staff_busho_id_get($login_staff_id)
{
	$c_id = "";
	$sql = "SELECT organization_id FROM ".$_SESSION["SCHEMA"].".m_user_detail WHERE user_id = '" .$login_staff_id."'";
        $result = getList($sql);
	$f_rows = $result[0];
	if($f_rows){
		$c_id = $f_rows["organization_id"];
	}
return $c_id;

}
//スタッフ名からランクコード取得
function f_staff_rank_id_get($login_staff_id)
{
	$rank = 0;
	$sql = "SELECT rank FROM ".$_SESSION["SCHEMA"].".m_user_detail WHERE user_id = '" .$login_staff_id."'";
        $result = getList($sql);
	$f_rows = $result[0];
	if($f_rows){
		$rank = $f_rows["rank"];
	}
return $rank;

}

?>
