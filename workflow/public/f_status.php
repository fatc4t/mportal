<?php

//�󋵁@���x���\��
function f_status_dsp($f_status)
{
	$status_d = "";

	switch ($f_status)
	{
		case 0:
			$status_d = "----";
			break;
		case 1:
			$status_d = "������";
			break;
		case 2:
			$status_d = "��";
			break;
		case 3:
			$status_d = "����";
			break;
/*
		case 4:
			$status_d = "TEL�ǐՒ�";
			break;
		case 5:
			$status_d = "TEL�f�O";
			break;
*/
	}

return $status_d;

}

//�󋵁@���X�g�{�b�N�X�\��
function f_status_select($f_status)
{
	$status_html = "";
	$status_html .= "<select id=\"s_prj_status\" name=\"s_prj_status\">\n";
	switch ($f_status)
	{
		case 0:
			$status_html .= "<option value=\"0\" selected>----</option>\n";
			$status_html .= "<option value=\"1\">������</option>\n";
			$status_html .= "<option value=\"2\">��</option>\n";
			$status_html .= "<option value=\"3\">����</option>\n";
//			$status_html .= "<option value=\"4\">TEL�ǐՒ�</option>\n";
//			$status_html .= "<option value=\"5\">TEL�f�O</option>\n";
			break;
		case 1:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\" selected>������</option>\n";
			$status_html .= "<option value=\"2\">��</option>\n";
			$status_html .= "<option value=\"3\">����</option>\n";
//			$status_html .= "<option value=\"4\">TEL�ǐՒ�</option>\n";
//			$status_html .= "<option value=\"5\">TEL�f�O</option>\n";
			break;
		case 2:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\">������</option>\n";
			$status_html .= "<option value=\"2\" selected>��</option>\n";
			$status_html .= "<option value=\"3\">����</option>\n";
//			$status_html .= "<option value=\"4\">TEL�ǐՒ�</option>\n";
//			$status_html .= "<option value=\"5\">TEL�f�O</option>\n";
			break;
		case 3:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\">������</option>\n";
			$status_html .= "<option value=\"2\">��</option>\n";
			$status_html .= "<option value=\"3\" selected>����</option>\n";
//			$status_html .= "<option value=\"4\">TEL�ǐՒ�</option>\n";
//			$status_html .= "<option value=\"5\">TEL�f�O</option>\n";
			break;
/*
		case 4:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\">������</option>\n";
			$status_html .= "<option value=\"2\">��</option>\n";
			$status_html .= "<option value=\"3\">����</option>\n";
			$status_html .= "<option value=\"4\" selected>TEL�ǐՒ�</option>\n";
			$status_html .= "<option value=\"5\">TEL�f�O</option>\n";
			break;
		case 5:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\">������</option>\n";
			$status_html .= "<option value=\"2\">��</option>\n";
			$status_html .= "<option value=\"3\">����</option>\n";
			$status_html .= "<option value=\"4\">TEL�ǐՒ�</option>\n";
			$status_html .= "<option value=\"5\" selected>TEL�f�O</option>\n";
			break;
*/
	}
	$status_html .= "</select>\n";

return $status_html;

}

?>
