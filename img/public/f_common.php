<?php
/*
function group_bookmark_html($login_name)
{
	$bookmark_html = "";
	$bookmark_html .= "<table width=100%>\n";
	$bookmark_html .= "<tr>\n";
	$bookmark_html .= "<td class=\"caption\"><img src=\"./img/icon/bookmark.png\" width=20 height=20 align=top>�@�u�b�N�}�[�N</td>\n";
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
	$bookmark_html .= "<td class=\"caption\"><img src=\"./img/icon/bookmark.png\" width=20 height=20 align=top>�@�u�b�N�}�[�N</td>\n";
	$bookmark_html .= "</tr>\n";
	$bookmark_html .= "<tr>\n";
	$bookmark_html .= "<td class=\"telop\">\n";
  	$bookmark_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

		$bookmark_html .= "<tr>\n";

				$bookmark_html .= "<td align=center><input type=\"button\" style=\"width: 110px;\" value=\"�J�����_�[\" /></td>\n";
				$bookmark_html .= "<td align=center><input type=\"button\" style=\"width: 110px;\" value=\"�f����\" /></td>\n";
				$bookmark_html .= "<td align=center><input type=\"button\" style=\"width: 110px;\" value=\"���b�Z�[�W\" /></td>\n";

		$bookmark_html .= "</tr>\n";
		$bookmark_html .= "<tr>\n";

				$bookmark_html .= "<td align=center><input type=\"button\" style=\"width: 110px;\" value=\"�t�@�C�����L\" /></td>\n";
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
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/top.php\">�g�b�v�y�[�W</a></li>\n";



	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/notice/notice_list.php\">�ʒm</a></li>\n";

	$menu_side_left .= "<div onclick=\"drop_menu1();\">\n";
	$menu_side_left .= "<li class=\"sb-close_menu\"><a>�X�P�W���[��</a></li>\n";
	$menu_side_left .= "</div>\n";
	$menu_side_left .= "<div id=\"drop_menu1\" style=\"display:none\">\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/schedule/mon.php\">&nbsp;&nbsp;&nbsp;��</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/schedule/week.php\">&nbsp;&nbsp;&nbsp;�T</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/schedule/day.php\">&nbsp;&nbsp;&nbsp;��</a></li>\n";
	$menu_side_left .= "</div>\n";

	$menu_side_left .= "<li class=\"sb-close\"><a href=\"#\">�R�R�ɂ��܂��I</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"#\">�f����</a></li>\n";

	$menu_side_left .= "<div onclick=\"drop_menu2();\">\n";
	$menu_side_left .= "<li class=\"sb-close_menu\"><a>���b�Z�[�W</a></li>\n";
	$menu_side_left .= "</div>\n";
	$menu_side_left .= "<div id=\"drop_menu2\" style=\"display:none\">\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/message/group.php\">&nbsp;&nbsp;&nbsp;�O���[�v�o�^</a></li>\n";
	$menu_side_left .= "<li class=\"sb-close\"><a href=\"/mportal/group/message/index.php\">&nbsp;&nbsp;&nbsp;�`���b�g</a></li>\n";
	$menu_side_left .= "</div>\n";

	$menu_side_left .= "<li class=\"sb-close\"><a href=\"#\">�t�@�C�����L</a></li>\n";

	$admin = f_staff_admin_get($login_staff);		//�Ǘ��҂̂�
	if ($admin == 1)
	{
		$menu_side_left .= "<div onclick=\"drop_menu7();\">\n";
		$menu_side_left .= "<li class=\"sb-close_menu\"><a>�����e�i���X</a></li>\n";
		$menu_side_left .= "</div>\n";
		$menu_side_left .= "<div id=\"drop_menu7\" style=\"display:none\">\n";
		$menu_side_left .= "<li class=\"sb-close\"><a href=\"#\">&nbsp;&nbsp;&nbsp;�A�J�E���g�ꗗ</a></li>\n";
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
	$new_html .= "<td class=\"caption\"><img src=\"./img/icon/todo.png\" width=20 height=20 align=top>�@�ʒm</td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "<tr>\n";
	$new_html .= "<td class=\"telop\">\n";
  	$new_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

	$new_html .= "<tr height=20>\n";
	$new_html .= "<td align=left width=100>2016-05-05 17:30</td>\n";
	$new_html .= "<td align=left width=70>�{��</td>\n";
	$new_html .= "<td align=left><a href='#'>iphone9�̎d�l</a></td>\n";
	$new_html .= "<td align=left width=70><font color=red>����</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td colspan=4>\n";

	$new_html .= "<table width=\"100%\">\n";
	$new_html .= "<tr height=50>\n";
	$new_html .= "<td valign=top><img src='./public/sample.png' height=50></td>\n";
	$new_html .= "<td style='word-break: break-all;' valign=top><font size=2>iphone9���w������ƊO�Ȏ�p���K�v�ɂȂ�܂��B�ƂĂ��ɂ��������̎�p�ł����A�K�}�����Ȃ���΂����܂���B</font></td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "</table>\n";

	$new_html .= "</td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr height=20>\n";
	$new_html .= "<td align=left width=100>2016-05-04 12:00</td>\n";
	$new_html .= "<td align=left width=70>�{��</td>\n";
	$new_html .= "<td align=left><a href='#'>�S�Љ�c</a></td>\n";
	$new_html .= "<td align=left width=70><font color=red>����</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td colspan=4>\n";

	$new_html .= "<table width=\"100%\">\n";
	$new_html .= "<tr height=50>\n";
	$new_html .= "<td valign=top><img src='./public/sample2.jpg' height=50></td>\n";
	$new_html .= "<td style='word-break: break-all;' valign=top><font size=2>�S�Љ�c�ł́A�݂Ȃ���ϋɓI�Ɉӌ����q�ׂĒ����܂����B�c���^��Y�t���Ă��܂��̂ŁA�F����Q�Ƃ��������B</font></td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "</table>\n";

	$new_html .= "</td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr height=20>\n";
	$new_html .= "<td align=left width=100>2016-05-04 12:00</td>\n";
	$new_html .= "<td align=left width=70>�X�܊Ǘ���</td>\n";
	$new_html .= "<td align=left><a href='#'>�V�X�̂��m�点</a></td>\n";
	$new_html .= "<td align=left width=70><font color=blue>����</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td colspan=4>\n";

	$new_html .= "<table width=\"100%\">\n";
	$new_html .= "<tr height=50>\n";
	$new_html .= "<td valign=top><img src='./public/shop.jpg' height=50></td>\n";
	$new_html .= "<td style='word-break: break-all;' valign=top><font size=2>���l�̍��ؒ��ɐV�X���I�[�v�����܂��I�V�������Ԃ��撣���Ă��܂��̂ŁA������낵�����肢���܂��I</font></td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "</table>\n";

	$new_html .= "</td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left>2016-06-11 10:30</td>\n";
	$new_html .= "<td align=left>�Ǘ���</td>\n";
	$new_html .= "<td align=left><a href='#'>�ΑӒ���</a></td>\n";
	$new_html .= "<td align=left><font color=blue>����</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left>2016-06-10 09:00</td>\n";
	$new_html .= "<td align=left>�{��</td>\n";
	$new_html .= "<td align=left><a href='#'>�A���o�C�g�����̌�</a></td>\n";
	$new_html .= "<td align=left><font color=blue>����</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left>2016-04-02 11:20</td>\n";
	$new_html .= "<td align=left>�c�ƕ�</td>\n";
	$new_html .= "<td align=left><a href='#'>�X����c�̌�</a></td>\n";
	$new_html .= "<td align=left><font color=blue>����</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left>2016-02-28 18:00</td>\n";
	$new_html .= "<td align=left>�{��</td>\n";
	$new_html .= "<td align=left><a href='#'>���������̌�</a></td>\n";
	$new_html .= "<td align=left><font color=blue>����</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left>2016-01-12 17:00</td>\n";
	$new_html .= "<td align=left>�Ǘ���</td>\n";
	$new_html .= "<td align=left><a href='#'>�V�X�e���̓���ւ�</a></td>\n";
	$new_html .= "<td align=left><font color=blue>����</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left>2015-12-20 17:20</td>\n";
	$new_html .= "<td align=left>�Ǘ���</td>\n";
	$new_html .= "<td align=left><a href='#'>�N���N�n�̌�</a></td>\n";
	$new_html .= "<td align=left><font color=blue>����</font></td>\n";
	$new_html .= "</tr>\n";

	$new_html .= "<tr>\n";
	$new_html .= "<td align=left>2015-12-10 12:13</td>\n";
	$new_html .= "<td align=left>�Ǘ���</td>\n";
	$new_html .= "<td align=left><a href='#'>�N�������̌�</a></td>\n";
	$new_html .= "<td align=left><font color=blue>����</font></td>\n";
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
	$new_html .= "<td class=\"caption\"><img src=\"./img/icon/calendar.png\" width=20 height=20 align=top>�@�X�P�W���[��</td>\n";
	$new_html .= "</tr>\n";
	$new_html .= "<tr>\n";
	$new_html .= "<td class=\"telop\">\n";
  	$new_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

	$new_html .= "<tr height=20 style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left width=85>���t</td>\n";
	for ($i = 8; $i <= 20; $i++){
		$new_html .= "<td align=left width=55>".$i."</td>\n";
	}
	$new_html .= "</tr>\n";

		$serch_date = computeDate2($serch_date_base, 0);
		$y = substr($serch_date, 0, 4);
		$m = substr($serch_date, 5, 2);
		$d = substr($serch_date, 8, 2);
		$youbi = f_youbi_get($y, $m, $d);
	if ($youbi == "�y"){
		$fcol = "(<font color=blue>".$youbi."</font>)";
	}else if ($youbi == "��"){
		$fcol = "(<font color=red>".$youbi."</font>)";
	}else{
		$fcol = "(".$youbi.")";
	}

	$new_html .= "<tr height=20 style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left width=85>".$m."-".$d." ".$fcol."</td>\n";
	$new_html .= "<td align=left width=55>�@</td>\n";
	$new_html .= "<td class=\"tdb\" align=left width=55 colspan=5>(��)�~���I�l�A</td>\n";
	for ($i = 8; $i <= 20-6; $i++){
		$new_html .= "<td align=left width=55> </td>\n";
	}
	$new_html .= "</tr>\n";

		$serch_date = computeDate2($serch_date_base, 1);
		$y = substr($serch_date, 0, 4);
		$m = substr($serch_date, 5, 2);
		$d = substr($serch_date, 8, 2);
		$youbi = f_youbi_get($y, $m, $d);
	if ($youbi == "�y"){
		$fcol = "(<font color=blue>".$youbi."</font>)";
	}else if ($youbi == "��"){
		$fcol = "(<font color=red>".$youbi."</font>)";
	}else{
		$fcol = "(".$youbi.")";
	}

	$new_html .= "<tr height=20 style='border-bottom:1px solid #ccc;'>\n";
	$new_html .= "<td align=left width=85>".$m."-".$d." ".$fcol."</td>\n";
	for ($i = 1; $i <= 5; $i++){
		$new_html .= "<td align=left width=55> </td>\n";
	}
	$new_html .= "<td class=\"tdb\" align=left width=55 colspan=3>�c�Ɖ�c</td>\n";
	$new_html .= "<td align=left width=55> </td>\n";
	$new_html .= "<td class=\"tdb\" align=left width=55 colspan=2>���{��s(�L)</td>\n";
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
	if ($youbi == "�y"){
		$fcol = "(<font color=blue>".$youbi."</font>)";
	}else if ($youbi == "��"){
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
			$new_html .= "<td class=\"tdy\" align=left width=55 colspan=13>�L��</td>\n";
		}else if ($j == 4){
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td class=\"tdb\" align=left colspan=2>(��)��쏤��</td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td class=\"tdb\" align=left colspan=2>(��)�����O���C�Y</td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
		}else if ($j == 1){
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td class=\"tdo\" align=left width=55>�O�o</td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td class=\"tdb\" align=left colspan=2>(��)���c��</td>\n";
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
			$new_html .= "<td class=\"tdm\" align=left colspan=2>�p���t���r���[</td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td class=\"tdb\" align=left colspan=3>(��)�����g</td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td align=left width=55> </td>\n";
			$new_html .= "<td class=\"tdm\" align=left width=55>TV��c</td>\n";
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
	$workflow_html .= "<td class=\"caption\"><img src=\"./img/icon/workflow.png\" width=20 height=20 align=top>�@���[�N�t���[</td>\n";
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
				$workflow_html .= "<td align=left><font color=black>���Ȃ��̏��F�҂���</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>".$next_num."��</font></td>\n";
}else{
				$workflow_html .= "<td align=left><a href=\"./workflow/apply_agree.php\" target=\"_blank\"><font color=black>���Ȃ��̏��F�҂���</font></a></td>\n";
				$workflow_html .= "<td align=left><font color=red>".$next_num."��</font></td>\n";
}
				$workflow_html .= "<td align=left><font color=black>�ł�</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr>\n";
if ($cc_num == 0){
				$workflow_html .= "<td align=left><font color=black>���Ȃ��̎Q�Ƒ҂���</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>".$cc_num."��</font></td>\n";
}else{
				$workflow_html .= "<td align=left><a href=\"./workflow/apply_agree.php\" target=\"_blank\"><font color=black>���Ȃ��̎Q�Ƒ҂���</font></a></td>\n";
				$workflow_html .= "<td align=left><font color=red>".$cc_num."��</font></td>\n";
}
				$workflow_html .= "<td align=left><font color=black>�ł�</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr>\n";
if ($in_num == 0){
				$workflow_html .= "<td align=left><font color=black>���Ȃ��̐\������</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>".$in_num."��</font></td>\n";
}else{
				$workflow_html .= "<td align=left><a href=\"./workflow/apply_view.php\" target=\"_blank\"><font color=black>���Ȃ��̐\������</font></a></td>\n";
				$workflow_html .= "<td align=left><font color=red>".$in_num."��</font></td>\n";
}
				$workflow_html .= "<td align=left><font color=black>�ł�</font></td>\n";
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
	$workflow_html .= "<td class=\"caption\"><img src=\"./img/icon/bbs.png\" width=20 height=20 align=top>�@�f����</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<td class=\"telop\">\n";
  	$workflow_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>�X�L�[�� BBS</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>10��</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>�O�Y�Y��Y</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2016-06-20</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>�Ј����s�A���P�[�g</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>1��</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>�v�Ė�</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2016-06-19</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>M-PORTAL��c��</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>25��</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>�r���E�P�C�c</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2016-05-10</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>�ʐM�̔��̂��m�点</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>5��</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>���c����</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2016-04-24</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>15�΂Ńo�C�N�𥥥</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>1��</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>����L</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2016-01-14</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>���N�̕���</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>43��</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>���c�L</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2016-01-01</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>�Y�N��ɂ���</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>56��</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>���C����</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2015-12-03</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>���ω�</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>12��</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>�͍��ޕێq</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2015-11-13</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><font color=black>�����߃X�}�z</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>32��</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>�����^���E</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>2015-10-22</font></td>\n";
				$workflow_html .= "</tr>\n";
				$workflow_html .= "<tr>\n";
				$workflow_html .= "<td align=left><font color=black>1980�N�����낤</font></td>\n";
				$workflow_html .= "<td align=left><font color=blue>32��</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>�j���[���[�N�j���[���[�N</font></td>\n";
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
	$workflow_html .= "<td class=\"caption\"><img src=\"./img/icon/message.png\" width=20 height=20 align=top>�@���b�Z�[�W</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<td class=\"telop\">\n";
  	$workflow_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;'>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/mail16.png\" width=15 height=15 align=top>&nbsp;&nbsp;<font color=black>���c���q</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>���񂩂�<a href='#'>���b�Z�[�W</a>���͂��Ă��܂�</font></td>\n";
				$workflow_html .= "</tr>\n";

				$workflow_html .= "<tr>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/mail16.png\" width=15 height=15 align=top>&nbsp;&nbsp;<font color=black>���Ђ��</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>���񂩂�<a href='#'>���b�Z�[�W</a>���͂��Ă��܂�</font></td>\n";
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
	$workflow_html .= "<td class=\"caption\"><img src=\"./img/icon/coco.png\" width=20 height=20 align=top>�@�R�R�ɂ��܂��I</td>\n";
	$workflow_html .= "</tr>\n";
	$workflow_html .= "<tr>\n";
	$workflow_html .= "<td class=\"telop\">\n";
  	$workflow_html .= "<table class=\"top-menu-list_table\" width=\"100%\">\n";

				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;' height=20>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/coco16.png\" width=15 height=15 align=top>&nbsp;&nbsp;2016-06-20 12:13</td>\n";
				$workflow_html .= "<td align=left><font color=black>�ɓ�����</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>�����<a href='https://www.google.co.jp/maps/place/%E6%97%A5%E6%9C%AC%E3%83%86%E3%83%AC%E3%83%93%E6%94%BE%E9%80%81%E7%B6%B2%E6%A0%AA%E5%BC%8F%E4%BC%9A%E7%A4%BE/@35.6644505,139.7587345,18z/data=!4m12!1m6!3m5!1s0x60188bc263ed5eed:0xc20773bb2245a02a!2z5pel5pys44OG44Os44OT5pS-6YCB57ay5qCq5byP5Lya56S-!8m2!3d35.6643132!4d139.7598181!3m4!1s0x60188bc263ed5eed:0xc20773bb2245a02a!8m2!3d35.6643132!4d139.7598181' target='_blank'>�R�R</a>�ɂ��܂�</font></td>\n";
				$workflow_html .= "</tr>\n";

				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;' height=20>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/coco16.png\" width=15 height=15 align=top>&nbsp;&nbsp;2016-06-10 09:13</td>\n";
				$workflow_html .= "<td align=left><font color=black>�x������</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>�����<a href='https://www.google.co.jp/maps/place/%E6%97%A5%E6%9C%AC%E3%83%86%E3%83%AC%E3%83%93%E6%94%BE%E9%80%81%E7%B6%B2%E6%A0%AA%E5%BC%8F%E4%BC%9A%E7%A4%BE/@35.6644505,139.7587345,18z/data=!4m12!1m6!3m5!1s0x60188bc263ed5eed:0xc20773bb2245a02a!2z5pel5pys44OG44Os44OT5pS-6YCB57ay5qCq5byP5Lya56S-!8m2!3d35.6643132!4d139.7598181!3m4!1s0x60188bc263ed5eed:0xc20773bb2245a02a!8m2!3d35.6643132!4d139.7598181' target='_blank'>�R�R</a>�ɂ��܂�</font></td>\n";
				$workflow_html .= "</tr>\n";


				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;' height=20>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/coco16.png\" width=15 height=15 align=top>&nbsp;&nbsp;2016-06-09 19:00</td>\n";
				$workflow_html .= "<td align=left><font color=black>�����F�b</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>�����<a href='https://www.google.co.jp/maps/place/%E6%97%A5%E6%9C%AC%E3%83%86%E3%83%AC%E3%83%93%E6%94%BE%E9%80%81%E7%B6%B2%E6%A0%AA%E5%BC%8F%E4%BC%9A%E7%A4%BE/@35.6644505,139.7587345,18z/data=!4m12!1m6!3m5!1s0x60188bc263ed5eed:0xc20773bb2245a02a!2z5pel5pys44OG44Os44OT5pS-6YCB57ay5qCq5byP5Lya56S-!8m2!3d35.6643132!4d139.7598181!3m4!1s0x60188bc263ed5eed:0xc20773bb2245a02a!8m2!3d35.6643132!4d139.7598181' target='_blank'>�R�R</a>�ɂ��܂�</font></td>\n";
				$workflow_html .= "</tr>\n";

				$workflow_html .= "<tr style='border-bottom:1px solid #ccc;' height=20>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/coco16.png\" width=15 height=15 align=top>&nbsp;&nbsp;2016-06-08 08:03</td>\n";
				$workflow_html .= "<td align=left><font color=black>�����D</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>�����<a href='https://www.google.co.jp/maps/place/%E6%97%A5%E6%9C%AC%E3%83%86%E3%83%AC%E3%83%93%E6%94%BE%E9%80%81%E7%B6%B2%E6%A0%AA%E5%BC%8F%E4%BC%9A%E7%A4%BE/@35.6644505,139.7587345,18z/data=!4m12!1m6!3m5!1s0x60188bc263ed5eed:0xc20773bb2245a02a!2z5pel5pys44OG44Os44OT5pS-6YCB57ay5qCq5byP5Lya56S-!8m2!3d35.6643132!4d139.7598181!3m4!1s0x60188bc263ed5eed:0xc20773bb2245a02a!8m2!3d35.6643132!4d139.7598181' target='_blank'>�R�R</a>�ɂ��܂�</font></td>\n";
				$workflow_html .= "</tr>\n";

				$workflow_html .= "<tr height=20>\n";
				$workflow_html .= "<td align=left><img src=\"./img/icon/coco16.png\" width=15 height=15 align=top>&nbsp;&nbsp;2016-06-08 08:03</td>\n";
				$workflow_html .= "<td align=left><font color=black>�r���Q�C�c</font></td>\n";
				$workflow_html .= "<td align=left><font color=black>�����<a href='https://www.google.co.jp/maps?espv=2&biw=1366&bih=705&q=%E3%83%9E%E3%82%A4%E3%82%AF%E3%83%AD%E3%82%BD%E3%83%95%E3%83%88%E3%80%80%E6%9C%AC%E7%A4%BE%E3%80%80%E4%BD%8F%E6%89%80%E3%80%80MAP&bav=on.2,or.&bvm=bv.125801520,d.dGo&ion=1&um=1&ie=UTF-8&sa=X&ved=0ahUKEwjwn8vZoMzNAhVBkZQKHTrADLAQ_AUIBigB' target='_blank'>�R�R</a>�ɂ��܂�</font></td>\n";
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
	$h .= "<td class=\"caption\"><img src=\"./img/icon/file.png\" width=20 height=20 align=top>�@�t�@�C�����L</td>\n";
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

