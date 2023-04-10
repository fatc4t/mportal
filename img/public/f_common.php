<?php
/*
function group_bookmark_html($login_name)
{
	$bookmark_html = "";
	$bookmark_html .= "<table width=100%>\n";
	$bookmark_html .= "<tr>\n";
	$bookmark_html .= "<td class=\"caption\"><img src=\"./img/icon/bookmark.png\" width=20 height=20 align=top>　ブックマーク</td>\n";
	$bookmark_html .= "</tr>\n";
	$bookmark_html .= "<tr>\n";
	$bookmark_html .= "<td class=\"telop\">\n";
  	$bookmark_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

  	$sql_bookmark = "SELECT * FROM t_bookmark WHERE system_type = 'group' and user_name = '".char_chg("to_db", $login_name)."' ORDER BY bookmark_id ASC";
	$result_bookmark = executeQuery($sql_bookmark);
	$rows_bookmark = mysql_num_rows($result_bookmark);
	$button_count=0;
	if($rows_bookmark){
		$bookmark_html .= "<tr>\n";
		while($row_bookmark = mysql_fetch_array($result_bookmark)) {
				if ($button_count == 5){
					$bookmark_html .= "</tr>\n";
					$bookmark_html .= "<tr>\n";
					$button_count = 0;
				}
				$button_count ++;
				$bookmark_html .= "<td align=center><input type=\"button\" style=\"width: 110px;\" value=\"".char_chg("to_dsp", $row_bookmark["title"])."\" onclick=window.open(\"".char_chg("to_dsp", $row_bookmark["url"])."\"); /></td>\n";
		}
		for ($iii=$button_count; $iii < 3; $iii++){
				$bookmark_html .= "<td align=center></td>\n";
		}
		$bookmark_html .= "</tr>\n";
	}
  	$bookmark_html .= "</table>\n";
	$bookmark_html .= "</td>\n";
	$bookmark_html .= "</tr>\n";
	$bookmark_html .= "</table>\n";
	
	return $bookmark_html;
}
*/
function group_bookmark_html($login_name)
{
	$bookmark_html = "";
	$bookmark_html .= "<table width=100%>\n";
	$bookmark_html .= "<tr>\n";
	$bookmark_html .= "<td class=\"caption\"><img src=\"./img/icon/bookmark.png\" width=20 height=20 align=top>　ブックマーク</td>\n";
	$bookmark_html .= "</tr>\n";
	$bookmark_html .= "<tr>\n";
	$bookmark_html .= "<td class=\"telop\">\n";
  	$bookmark_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

		$bookmark_html .= "<tr>\n";

				$bookmark_html .= "<td align=center><input type=\"button\" style=\"width: 110px;\" value=\"カレンダー\" /></td>\n";
				$bookmark_html .= "<td align=center><input type=\"button\" style=\"width: 110px;\" value=\"掲示板\" /></td>\n";
				$bookmark_html .= "<td align=center><input type=\"button\" style=\"width: 110px;\" value=\"メッセージ\" /></td>\n";

		$bookmark_html .= "</tr>\n";
		$bookmark_html .= "<tr>\n";

				$bookmark_html .= "<td align=center><input type=\"button\" style=\"width: 110px;\" value=\"ファイル共有\" /></td>\n";
				$bookmark_html .= "<td align=center></td>\n";
				$bookmark_html .= "<td align=center></td>\n";

		$bookmark_html .= "</tr>\n";

  	$bookmark_html .= "</table>\n";
	$bookmark_html .= "</td>\n";
	$bookmark_html .= "</tr>\n";
	$bookmark_html .= "</table>\n";
	
	return $bookmark_html;
}

function menu_side_left_group($home, $login_staff)
{

$user_id = f_staff_id_get($login_staff);


	$menu_side_left = "<!-- sb-left -->\n";
	$menu_side_left .= "<div class=\"sb-slidebar sb-left\">\n";
	$menu_side_left .= "<nav>\n";
	$menu_side_left .= "<ul class=\"sb-menu\">\n";
	$menu_side_left .= "<li><img src=\"/mportal/img/logo-group.png\" alt=\"M-PORTAL\" width=\"118\" height=\"40\"></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/top.php\">トップページ</a></li>\n";



	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/notice/notice_list.php\">通知</a></li>\n";

	$menu_side_left .= "<div onclick=\"drop_menu1();\">\n";
	$menu_side_left .= "<li class=\"sb-close_menu\"><a>スケジュール</a></li>\n";
	$menu_side_left .= "</div>\n";
	$menu_side_left .= "<div id=\"drop_menu1\" style=\"display:none\">\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/schedule/mon.php\">&nbsp;&nbsp;&nbsp;月</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/schedule/week.php\">&nbsp;&nbsp;&nbsp;週</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/schedule/day.php\">&nbsp;&nbsp;&nbsp;日</a></li>\n";
	$menu_side_left .= "</div>\n";

	$menu_side_left .= "<li class=\"sb-close\"><a href=\"#\">ココにいます！</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"#\">掲示板</a></li>\n";

	$menu_side_left .= "<div onclick=\"drop_menu2();\">\n";
	$menu_side_left .= "<li class=\"sb-close_menu\"><a>メッセージ</a></li>\n";
	$menu_side_left .= "</div>\n";
	$menu_side_left .= "<div id=\"drop_menu2\" style=\"display:none\">\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/message/group.php\">&nbsp;&nbsp;&nbsp;グループ登録</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/message/index.php\">&nbsp;&nbsp;&nbsp;チャット</a></li>\n";
	$menu_side_left .= "</div>\n";

	$menu_side_left .= "<li class=\"sb-close\"><a href=\"#\">ファイル共有</a></li>\n";

	$admin = f_staff_admin_get($login_staff);		//管理者のみ
	if ($admin == 1)
	{
		$menu_side_left .= "<div onclick=\"drop_menu7();\">\n";
		$menu_side_left .= "<li class=\"sb-close_menu\"><a>メンテナンス</a></li>\n";
		$menu_side_left .= "</div>\n";
		$menu_side_left .= "<div id=\"drop_menu7\" style=\"display:none\">\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"#\">&nbsp;&nbsp;&nbsp;アカウント一覧</a></li>\n";
		$menu_side_left .= "</div>\n";
	}






	$menu_side_left .= "</ul>\n";
	$menu_side_left .= "</nav>\n";
	$menu_side_left .= "</div><!-- sb-left -->\n";

	return $menu_side_left;
}

function notice_top($login_staff)
{
	$new_html = "";
	$new_html .= "<table width=100%>\n";
	$new_html .= "<tr height=25>\n";
	$new_html .= "<td class=\"caption\"><img src=\"./img/icon/todo.png\" width=20 height=20 align=top>　通知</td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "<tr>\n";
	$new_html .= "<td class=\"telop\">\n";
  	$new_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

	$new_html .= "<tr height=20>\n";
	$new_html .= "<td align=left width=100>2016-05-05 17:30</td>\n";
	$new_html .= "<td align=left width=70>本部</td>\n";
	$new_html .= "<td align=left><a href='#'>iphone9の仕様</a></td>\n";
	$new_html .= "<td align=left width=70><font color=red>未読</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td colspan=4>\n";

	$new_html .= "<table width=\"100%\">\n";
	$new_html .= "<tr height=50>\n";
	$new_html .= "<td valign=top><img src='./public/sample.png' height=50></td>\n";
	$new_html .= "<td style='word-break: break-all;' valign=top><font size=2>iphone9を購入すると外科手術が必要になります。とても痛い命懸けの手術ですが、ガマンしなければいけません。</font></td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "</table>\n";

	$new_html .= "</td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr height=20>\n";
	$new_html .= "<td align=left width=100>2016-05-04 12:00</td>\n";
	$new_html .= "<td align=left width=70>本部</td>\n";
	$new_html .= "<td align=left><a href='#'>全社会議</a></td>\n";
	$new_html .= "<td align=left width=70><font color=red>未読</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td colspan=4>\n";

	$new_html .= "<table width=\"100%\">\n";
	$new_html .= "<tr height=50>\n";
	$new_html .= "<td valign=top><img src='./public/sample2.jpg' height=50></td>\n";
	$new_html .= "<td style='word-break: break-all;' valign=top><font size=2>全社会議では、みなさん積極的に意見を述べて頂きました。議事録を添付していますので、皆さん参照ください。</font></td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "</table>\n";

	$new_html .= "</td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr height=20>\n";
	$new_html .= "<td align=left width=100>2016-05-04 12:00</td>\n";
	$new_html .= "<td align=left width=70>店舗管理部</td>\n";
	$new_html .= "<td align=left><a href='#'>新店のお知らせ</a></td>\n";
	$new_html .= "<td align=left width=70><font color=blue>既読</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td colspan=4>\n";

	$new_html .= "<table width=\"100%\">\n";
	$new_html .= "<tr height=50>\n";
	$new_html .= "<td valign=top><img src='./public/shop.jpg' height=50></td>\n";
	$new_html .= "<td style='word-break: break-all;' valign=top><font size=2>横浜の桜木町に新店がオープンします！新しい仲間が頑張っていますので、応援よろしくお願いします！</font></td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "</table>\n";

	$new_html .= "</td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left>2016-06-11 10:30</td>\n";
	$new_html .= "<td align=left>管理部</td>\n";
	$new_html .= "<td align=left><a href='#'>勤怠締め</a></td>\n";
	$new_html .= "<td align=left><font color=blue>既読</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left>2016-06-10 09:00</td>\n";
	$new_html .= "<td align=left>本部</td>\n";
	$new_html .= "<td align=left><a href='#'>アルバイト時給の件</a></td>\n";
	$new_html .= "<td align=left><font color=blue>既読</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left>2016-04-02 11:20</td>\n";
	$new_html .= "<td align=left>営業部</td>\n";
	$new_html .= "<td align=left><a href='#'>店長会議の件</a></td>\n";
	$new_html .= "<td align=left><font color=blue>既読</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left>2016-02-28 18:00</td>\n";
	$new_html .= "<td align=left>本部</td>\n";
	$new_html .= "<td align=left><a href='#'>小口現金の件</a></td>\n";
	$new_html .= "<td align=left><font color=blue>既読</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left>2016-01-12 17:00</td>\n";
	$new_html .= "<td align=left>管理部</td>\n";
	$new_html .= "<td align=left><a href='#'>システムの入れ替え</a></td>\n";
	$new_html .= "<td align=left><font color=blue>既読</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left>2015-12-20 17:20</td>\n";
	$new_html .= "<td align=left>管理部</td>\n";
	$new_html .= "<td align=left><a href='#'>年末年始の件</a></td>\n";
	$new_html .= "<td align=left><font color=blue>既読</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr>\n";
	$new_html .= "<td align=left>2015-12-10 12:13</td>\n";
	$new_html .= "<td align=left>管理部</td>\n";
	$new_html .= "<td align=left><a href='#'>年末調整の件</a></td>\n";
	$new_html .= "<td align=left><font color=blue>既読</font></td>\n";
	$new_html .= "</tr>\n";

  	$new_html .= "</table>\n";
	$new_html .= "</td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "</table>\n";
	
	return $new_html;
}


function cal_top($login_staff)
{
	$serch_date_base = date("Y-m-d");

	$new_html = "";
	$new_html .= "<table width=100%>\n";
	$new_html .= "<tr height=25>\n";
	$new_html .= "<td class=\"caption\"><img src=\"./img/icon/calendar.png\" width=20 height=20 align=top>　スケジュール</td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "<tr>\n";
	$new_html .= "<td class=\"telop\">\n";
  	$new_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

	$new_html .= "<tr height=20 style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left width=85>日付</td>\n";
	for ($i = 8; $i <= 20; $i++){
		$new_html .= "<td align=left width=55>".$i."</td>\n";
	}
	$new_html .= "</tr>\n";

		$serch_date = computeDate2($serch_date_base, 0);
		$y = substr($serch_date, 0, 4);
		$m = substr($serch_date, 5, 2);
		$d = substr($serch_date, 8, 2);
		$youbi = f_youbi_get($y, $m, $d);
	if ($youbi == "土"){
		$fcol = "(<font color=blue>".$youbi."</font>)";
	}else if ($youbi == "日"){
		$fcol = "(<font color=red>".$youbi."</font>)";
	}else{
		$fcol = "(".$youbi.")";
	}

	$new_html .= "<tr height=20 style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left width=85>".$m."-".$d." ".$fcol."</td>\n";
	$new_html .= "<td align=left width=55>　</td>\n";
	$new_html .= "<td class=\"tdb\" align=left width=55 colspan=5>(株)ミリオネア</td>\n";
	for ($i = 8; $i <= 20-6; $i++){
		$new_html .= "<td align=left width=55> </td>\n";
	}
	$new_html .= "</tr>\n";

		$serch_date = computeDate2($serch_date_base, 1);
		$y = substr($serch_date, 0, 4);
		$m = substr($serch_date, 5, 2);
		$d = substr($serch_date, 8, 2);
		$youbi = f_youbi_get($y, $m, $d);
	if ($youbi == "土"){
		$fcol = "(<font color=blue>".$youbi."</font>)";
	}else if ($youbi == "日"){
		$fcol = "(<font color=red>".$youbi."</font>)";
	}else{
		$fcol = "(".$youbi.")";
	}

	$new_html .= "<tr height=20 style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left width=85>".$m."-".$d." ".$fcol."</td>\n";
	for ($i = 1; $i <= 5; $i++){
		$new_html .= "<td align=left width=55> </td>\n";
	}
	$new_html .= "<td class=\"tdb\" align=left width=55 colspan=3>営業会議</td>\n";
	$new_html .= "<td align=left width=55> </td>\n";
	$new_html .= "<td class=\"tdb\" align=left width=55 colspan=2>日本銀行(有)</td>\n";
	for ($i = 1; $i <= 2; $i++){
		$new_html .= "<td align=left width=55> </td>\n";
	}
	$new_html .= "</tr>\n";

	for ($j = 1; $j <= 5; $j++){
		$serch_date = computeDate2($serch_date_base, $j+1);
		$y = substr($serch_date, 0, 4);
		$m = substr($serch_date, 5, 2);
		$d = substr($serch_date, 8, 2);
		$youbi = f_youbi_get($y, $m, $d);
	if ($youbi == "土"){
		$fcol = "(<font color=blue>".$youbi."</font>)";
	}else if ($youbi == "日"){
		$fcol = "(<font color=red>".$youbi."</font>)";
	}else{
		$fcol = "(".$youbi.")";
	}

		if ($j == 5){
			$new_html .= "<tr height=20>\n";
		}else{
			$new_html .= "<tr height=20 style='border-bottom:1px solid #ccc;'>\n";
		}
		$new_html .= "<td align=left width=85>".$m."-".$d." ".$fcol."</td>\n";
		if ($j == 3){
			$new_html .= "<td class=\"tdy\" align=left width=55 colspan=13>有給</td>\n";
		}else if ($j == 4){
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td class=\"tdb\" align=left colspan=2>(株)井野商事</td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td class=\"tdb\" align=left colspan=2>(株)ラングライズ</td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
		}else if ($j == 1){
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td class=\"tdo\" align=left width=55>外出</td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td class=\"tdb\" align=left colspan=2>(株)末田家</td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
		}else if ($j == 5){
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td class=\"tdm\" align=left colspan=2>パンフレビュー</td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td class=\"tdb\" align=left colspan=3>(株)藤原組</td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td class=\"tdm\" align=left width=55>TV会議</td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
		}else{
			for ($i = 8; $i <= 20; $i++){
				$new_html .= "<td align=left width=55> </td>\n";
			}
		}
		$new_html .= "</tr>\n";
	}

  	$new_html .= "</table>\n";
	$new_html .= "</td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "</table>\n";
	
	return $new_html;
}

function f_workflow_html($login_staff)
{
	$workflow_html = "";
	$workflow_html .= "<table width=100%>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<td class=\"caption\"><img src=\"./img/icon/workflow.png\" width=20 height=20 align=top>　ワークフロー</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<td class=\"telop\">\n";
  	$workflow_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

	$staff_code = f_staff_id_get($login_staff);

	$next_num = 0;
//	$sql = "SELECT count(*) FROM t_workflow WHERE (next1_data LIKE '".$staff_code."%-2' or next2_data LIKE '".$staff_code."%-2' or next3_data LIKE '".$staff_code."%-2' or next4_data LIKE '".$staff_code."%-2' or next5_data LIKE '".$staff_code."%-2' or next6_data LIKE '".$staff_code."%-2' or next7_data LIKE '".$staff_code."%-2' or next8_data LIKE '".$staff_code."%-2' or next9_data LIKE '".$staff_code."%-2' or next10_data LIKE '".$staff_code."%-2') and stat = '1'";
	$sql = "SELECT count(*) FROM t_workflow WHERE (next1_data = '".$staff_code."-2' or next2_data = '".$staff_code."-2' or next3_data = '".$staff_code."-2' or next4_data = '".$staff_code."-2' or next5_data = '".$staff_code."-2' or next6_data = '".$staff_code."-2' or next7_data = '".$staff_code."-2' or next8_data = '".$staff_code."-2' or next9_data = '".$staff_code."-2' or next10_data = '".$staff_code."-2') and stat = '1'";
	$num = executeQuery($sql);
	list($next_num) = mysql_fetch_row($num);
	mysql_free_result($num);

	$cc_num = 0;
//	$sql = "SELECT count(*) FROM t_workflow WHERE next1_cc1_data LIKE '".$staff_code."%%-2' or next1_cc2_data LIKE '".$staff_code."%-2' or next1_cc3_data LIKE '".$staff_code."%-2' or next2_cc1_data LIKE '".$staff_code."%-2' or next2_cc2_data LIKE '".$staff_code."%-2' or next2_cc3_data LIKE '".$staff_code."%-2' or next3_cc1_data LIKE '".$staff_code."%-2' or next3_cc2_data LIKE '".$staff_code."%-2' or next3_cc3_data LIKE '".$staff_code."%-2' or next4_cc1_data LIKE '".$staff_code."%-2' or next4_cc2_data LIKE '".$staff_code."%-2' or next4_cc3_data LIKE '".$staff_code."%-2' or next5_cc1_data LIKE '".$staff_code."%-2' or next5_cc2_data LIKE '".$staff_code."%-2' or next5_cc3_data LIKE '".$staff_code."%-2' or next6_cc1_data LIKE '".$staff_code."%-2' or next6_cc2_data LIKE '".$staff_code."%-2' or next6_cc3_data LIKE '".$staff_code."%-2' or next7_cc1_data LIKE '".$staff_code."%-2' or next7_cc2_data LIKE '".$staff_code."%-2' or next7_cc3_data LIKE '".$staff_code."%-2' or next8_cc1_data LIKE '".$staff_code."%-2' or next8_cc2_data LIKE '".$staff_code."%-2' or next8_cc3_data LIKE '".$staff_code."%-2' or next9_cc1_data LIKE '".$staff_code."%-2' or next9_cc2_data LIKE '".$staff_code."%-2' or next9_cc3_data LIKE '".$staff_code."%-2' or next10_cc1_data LIKE '".$staff_code."%-2' or next10_cc2_data LIKE '".$staff_code."%-2' or next10_cc3_data LIKE '".$staff_code."%-2'";
	$sql = "SELECT count(*) FROM t_workflow WHERE next1_cc1_data = '".$staff_code."-2' or next1_cc2_data = '".$staff_code."-2' or next1_cc3_data = '".$staff_code."-2' or next1_cc4_data = '".$staff_code."-2' or next1_cc5_data = '".$staff_code."-2' or";
	$sql .= " next2_cc1_data = '".$staff_code."-2' or next2_cc2_data = '".$staff_code."-2' or next2_cc3_data = '".$staff_code."-2' or next2_cc4_data = '".$staff_code."-2' or next2_cc5_data = '".$staff_code."-2' or";
	$sql .= " next3_cc1_data = '".$staff_code."-2' or next3_cc2_data = '".$staff_code."-2' or next3_cc3_data = '".$staff_code."-2' or next3_cc4_data = '".$staff_code."-2' or next3_cc5_data = '".$staff_code."-2' or";
	$sql .= " next4_cc1_data = '".$staff_code."-2' or next4_cc2_data = '".$staff_code."-2' or next4_cc3_data = '".$staff_code."-2' or next4_cc4_data = '".$staff_code."-2' or next4_cc5_data = '".$staff_code."-2' or";
	$sql .= " next5_cc1_data = '".$staff_code."-2' or next5_cc2_data = '".$staff_code."-2' or next5_cc3_data = '".$staff_code."-2' or next5_cc4_data = '".$staff_code."-2' or next5_cc5_data = '".$staff_code."-2' or";
	$sql .= " next6_cc1_data = '".$staff_code."-2' or next6_cc2_data = '".$staff_code."-2' or next6_cc3_data = '".$staff_code."-2' or next6_cc4_data = '".$staff_code."-2' or next6_cc5_data = '".$staff_code."-2' or";
	$sql .= " next7_cc1_data = '".$staff_code."-2' or next7_cc2_data = '".$staff_code."-2' or next7_cc3_data = '".$staff_code."-2' or next7_cc4_data = '".$staff_code."-2' or next7_cc5_data = '".$staff_code."-2' or";
	$sql .= " next8_cc1_data = '".$staff_code."-2' or next8_cc2_data = '".$staff_code."-2' or next8_cc3_data = '".$staff_code."-2' or next8_cc4_data = '".$staff_code."-2' or next8_cc5_data = '".$staff_code."-2' or";
	$sql .= " next9_cc1_data = '".$staff_code."-2' or next9_cc2_data = '".$staff_code."-2' or next9_cc3_data = '".$staff_code."-2' or next9_cc4_data = '".$staff_code."-2' or next9_cc5_data = '".$staff_code."-2' or";
	$sql .= " next10_cc1_data = '".$staff_code."-2' or next10_cc2_data = '".$staff_code."-2' or next10_cc3_data = '".$staff_code."-2' or next10_cc4_data = '".$staff_code."-2' or next10_cc5_data = '".$staff_code."-2'";
	$num = executeQuery($sql);
	list($cc_num) = mysql_fetch_row($num);
	mysql_free_result($num);

	$in_num = 0;
	$sql = "SELECT count(*) FROM t_workflow WHERE user_id = '".$staff_code."' and stat = '1'";
	$num = executeQuery($sql);
	list($in_num) = mysql_fetch_row($num);
	mysql_free_result($num);


				$workflow_html .= "<tr>\n";
if ($next_num == 0){
				$workflow_html .= "<td align=left><font color=black>あなたの承認待ちは</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>".$next_num."件</font></td>\n";
}else{
				$workflow_html .= "<td align=left><a href=\"./workflow/apply_agree.php\" target=\"_blank\"><font color=black>あなたの承認待ちは</font></a></td>\n";
				$workflow_html .= "<td align=left><font color=red>".$next_num."件</font></td>\n";
}
				$workflow_html .= "<td align=left><font color=black>です</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr>\n";
if ($cc_num == 0){
				$workflow_html .= "<td align=left><font color=black>あなたの参照待ちは</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>".$cc_num."件</font></td>\n";
}else{
				$workflow_html .= "<td align=left><a href=\"./workflow/apply_agree.php\" target=\"_blank\"><font color=black>あなたの参照待ちは</font></a></td>\n";
				$workflow_html .= "<td align=left><font color=red>".$cc_num."件</font></td>\n";
}
				$workflow_html .= "<td align=left><font color=black>です</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr>\n";
if ($in_num == 0){
				$workflow_html .= "<td align=left><font color=black>あなたの申請中は</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>".$in_num."件</font></td>\n";
}else{
				$workflow_html .= "<td align=left><a href=\"./workflow/apply_view.php\" target=\"_blank\"><font color=black>あなたの申請中は</font></a></td>\n";
				$workflow_html .= "<td align=left><font color=red>".$in_num."件</font></td>\n";
}
				$workflow_html .= "<td align=left><font color=black>です</font></td>\n";
				$workflow_html .= "</tr>\n";


  	$workflow_html .= "</table>\n";
	$workflow_html .= "</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "</table>\n";
	
	return $workflow_html;
}

function bbs_top($login_staff)
{
	$workflow_html = "";
	$workflow_html .= "<table width=100%>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<td class=\"caption\"><img src=\"./img/icon/bbs.png\" width=20 height=20 align=top>　掲示板</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<td class=\"telop\">\n";
  	$workflow_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>スキー部 BBS</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>10件</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>三浦雄一郎</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2016-06-20</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>社員旅行アンケート</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>1件</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>久米明</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2016-06-19</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>M-PORTAL会議室</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>25件</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>ビル・ケイツ</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2016-05-10</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>通信販売のお知らせ</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>5件</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>高田総統</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2016-04-24</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>15歳でバイクを･･･</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>1件</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>尾崎豊</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2016-01-14</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>今年の抱負</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>43件</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>武田豊</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2016-01-01</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>忘年会について</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>56件</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>沿海部町</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2015-12-03</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>芋煮会</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>12件</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>河合奈保子</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2015-11-13</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>お勧めスマホ</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>32件</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>日立タロウ</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2015-10-22</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr>\n";
				$workflow_html .= "<td align=left><font color=black>1980年代を語ろう</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>32件</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>ニューヨークニューヨーク</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2015-05-27</font></td>\n";
				$workflow_html .= "</tr>\n";


  	$workflow_html .= "</table>\n";
	$workflow_html .= "</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "</table>\n";
	
	return $workflow_html;
}

function message_top($login_staff)
{
	$workflow_html = "";
	$workflow_html .= "<table width=100%>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<td class=\"caption\"><img src=\"./img/icon/message.png\" width=20 height=20 align=top>　メッセージ</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<td class=\"telop\">\n";
  	$workflow_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/mail16.png\" width=15 height=15 align=top>&nbsp;&nbsp;<font color=black>松田聖子</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>さんから<a href='#'>メッセージ</a>が届いています</font></td>\n";
				$workflow_html .= "</tr>\n";

				$workflow_html .= "<tr>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/mail16.png\" width=15 height=15 align=top>&nbsp;&nbsp;<font color=black>郷ひろみ</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>さんから<a href='#'>メッセージ</a>が届いています</font></td>\n";
				$workflow_html .= "</tr>\n";

  	$workflow_html .= "</table>\n";
	$workflow_html .= "</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "</table>\n";
	
	return $workflow_html;
}
function coco_top($login_staff)
{
	$workflow_html = "";
	$workflow_html .= "<table width=100%>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<td class=\"caption\"><img src=\"./img/icon/coco.png\" width=20 height=20 align=top>　ココにいます！</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<td class=\"telop\">\n";
  	$workflow_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;' height=20>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/coco16.png\" width=15 height=15 align=top>&nbsp;&nbsp;2016-06-20 12:13</td>\n";
				$workflow_html .= "<td align=left><font color=black>伊藤つかさ</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>さんは<a href='https://www.google.co.jp/maps/place/%E6%97%A5%E6%9C%AC%E3%83%86%E3%83%AC%E3%83%93%E6%94%BE%E9%80%81%E7%B6%B2%E6%A0%AA%E5%BC%8F%E4%BC%9A%E7%A4%BE/@35.6644505,139.7587345,18z/data=!4m12!1m6!3m5!1s0x60188bc263ed5eed:0xc20773bb2245a02a!2z5pel5pys44OG44Os44OT5pS-6YCB57ay5qCq5byP5Lya56S-!8m2!3d35.6643132!4d139.7598181!3m4!1s0x60188bc263ed5eed:0xc20773bb2245a02a!8m2!3d35.6643132!4d139.7598181' target='_blank'>ココ</a>にいます</font></td>\n";
				$workflow_html .= "</tr>\n";

				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;' height=20>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/coco16.png\" width=15 height=15 align=top>&nbsp;&nbsp;2016-06-10 09:13</td>\n";
				$workflow_html .= "<td align=left><font color=black>堀ちえみ</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>さんは<a href='https://www.google.co.jp/maps/place/%E6%97%A5%E6%9C%AC%E3%83%86%E3%83%AC%E3%83%93%E6%94%BE%E9%80%81%E7%B6%B2%E6%A0%AA%E5%BC%8F%E4%BC%9A%E7%A4%BE/@35.6644505,139.7587345,18z/data=!4m12!1m6!3m5!1s0x60188bc263ed5eed:0xc20773bb2245a02a!2z5pel5pys44OG44Os44OT5pS-6YCB57ay5qCq5byP5Lya56S-!8m2!3d35.6643132!4d139.7598181!3m4!1s0x60188bc263ed5eed:0xc20773bb2245a02a!8m2!3d35.6643132!4d139.7598181' target='_blank'>ココ</a>にいます</font></td>\n";
				$workflow_html .= "</tr>\n";


				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;' height=20>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/coco16.png\" width=15 height=15 align=top>&nbsp;&nbsp;2016-06-09 19:00</td>\n";
				$workflow_html .= "<td align=left><font color=black>柏原芳恵</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>さんは<a href='https://www.google.co.jp/maps/place/%E6%97%A5%E6%9C%AC%E3%83%86%E3%83%AC%E3%83%93%E6%94%BE%E9%80%81%E7%B6%B2%E6%A0%AA%E5%BC%8F%E4%BC%9A%E7%A4%BE/@35.6644505,139.7587345,18z/data=!4m12!1m6!3m5!1s0x60188bc263ed5eed:0xc20773bb2245a02a!2z5pel5pys44OG44Os44OT5pS-6YCB57ay5qCq5byP5Lya56S-!8m2!3d35.6643132!4d139.7598181!3m4!1s0x60188bc263ed5eed:0xc20773bb2245a02a!8m2!3d35.6643132!4d139.7598181' target='_blank'>ココ</a>にいます</font></td>\n";
				$workflow_html .= "</tr>\n";

				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;' height=20>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/coco16.png\" width=15 height=15 align=top>&nbsp;&nbsp;2016-06-08 08:03</td>\n";
				$workflow_html .= "<td align=left><font color=black>早見優</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>さんは<a href='https://www.google.co.jp/maps/place/%E6%97%A5%E6%9C%AC%E3%83%86%E3%83%AC%E3%83%93%E6%94%BE%E9%80%81%E7%B6%B2%E6%A0%AA%E5%BC%8F%E4%BC%9A%E7%A4%BE/@35.6644505,139.7587345,18z/data=!4m12!1m6!3m5!1s0x60188bc263ed5eed:0xc20773bb2245a02a!2z5pel5pys44OG44Os44OT5pS-6YCB57ay5qCq5byP5Lya56S-!8m2!3d35.6643132!4d139.7598181!3m4!1s0x60188bc263ed5eed:0xc20773bb2245a02a!8m2!3d35.6643132!4d139.7598181' target='_blank'>ココ</a>にいます</font></td>\n";
				$workflow_html .= "</tr>\n";

				$workflow_html .= "<tr height=20>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/coco16.png\" width=15 height=15 align=top>&nbsp;&nbsp;2016-06-08 08:03</td>\n";
				$workflow_html .= "<td align=left><font color=black>ビルゲイツ</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>さんは<a href='https://www.google.co.jp/maps?espv=2&biw=1366&bih=705&q=%E3%83%9E%E3%82%A4%E3%82%AF%E3%83%AD%E3%82%BD%E3%83%95%E3%83%88%E3%80%80%E6%9C%AC%E7%A4%BE%E3%80%80%E4%BD%8F%E6%89%80%E3%80%80MAP&bav=on.2,or.&bvm=bv.125801520,d.dGo&ion=1&um=1&ie=UTF-8&sa=X&ved=0ahUKEwjwn8vZoMzNAhVBkZQKHTrADLAQ_AUIBigB' target='_blank'>ココ</a>にいます</font></td>\n";
				$workflow_html .= "</tr>\n";

  	$workflow_html .= "</table>\n";
	$workflow_html .= "</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "</table>\n";
	
	return $workflow_html;
}

$h = "";
	function createDir($path)
	{
	global $h;
		if ($handle = opendir($path))
		{
			$h .= "\n<ul>\n";
			$queue = array();
			while (false !== ($file = readdir($handle)))
			{
				if (is_dir($path.$file) && $file != '.' && $file !='..') {
					printSubDir($file, $path, $queue);
				} else if ($file != '.' && $file !='..') {
					$queue[] = $file;
				}
			}
			printQueue($queue, $path);
			$h .= "</ul>\n";
		}
	}

	function printQueue($queue, $path)
	{
	global $h;
		foreach ($queue as $file)
		{
			printFile($file, $path);
		}
	}

	function img_select($file)
	{
		$img_h = "";

$pos = strpos($file, ".csv");
if ($pos != 0) {
	$img_h = "<img src=\"./img/icon/file16_csv.png\" width=15 height=15 align=top>\n";
}else{
	$pos = strpos($file, ".docx");
	if ($pos != 0) {
		$img_h = "<img src=\"./img/icon/file16_doc.png\" width=15 height=15 align=top>\n";
	}else{
		$pos = strpos($file, ".pptx");
		if ($pos != 0) {
			$img_h = "<img src=\"./img/icon/file16_ppt.png\" width=15 height=15 align=top>\n";
		}else{
			$pos = strpos($file, ".xlsx");
			if ($pos != 0) {
				$img_h = "<img src=\"./img/icon/file16_xls.png\" width=15 height=15 align=top>\n";
			}else{
				$pos = strpos($file, ".xls");
				if ($pos != 0) {
					$img_h = "<img src=\"./img/icon/file16_xls.png\" width=15 height=15 align=top>\n";
				}else{
					$pos = strpos($file, ".pdf");
					if ($pos != 0) {
						$img_h = "<img src=\"./img/icon/file16_pdf.png\" width=15 height=15 align=top>\n";
					}else{
						$img_h = "<img src=\"./img/icon/file16_txt.png\" width=15 height=15 align=top>\n";
					}
				}
			}
		}
	}
}

		return $img_h;
	}

	function printFile($file, $path)
	{
	global $h;
		$img_h = img_select($file);
		if ($file != "library.php" && $file != "Thumbs.db" && $file != "index.html")
			$h .= "<li>".$img_h."<a href=\"".$path.$file."\">".$file."</a></li>\n";
	}

	function printSubDir($dir, $path)
	{
	global $h;
		$h .= "<li><span class=\"dir\">$dir</span>";
		createDir($path.$dir."/");
		$h .= "</li>\n";
	}

function file_top($login_staff)
{
	global $h;

	$h .= "<table width=100%>\n";
	$h .= "<tr>\n";
	$h .= "<td class=\"caption\"><img src=\"./img/icon/file.png\" width=20 height=20 align=top>　ファイル共有</td>\n";
	$h .= "</tr>\n";
	$h .= "<tr>\n";
	$h .= "<td class=\"telop\">\n";

$h .= "<div id=\"dir_tree\" style='border-style: none;'>\n";

	$path = "./templete/";

	$h .= "<table width=\"400\" align=center bgcolor=white  border=0 cellspacing=0 cellpadding=0 bordercolor=gray><tr><td align=left>\n";
	$h .= "<br />\n";


	createDir($path);

$h .= "<br />\n";
$h .= "</table>\n";

$h .= "</div>\n";

	$h .= "</td>\n";
	$h .= "</tr>\n";
	$h .= "</table>\n";


return $h;
}

