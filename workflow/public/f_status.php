<?php

//状況　ラベル表示
function f_status_dsp($f_status)
{
	$status_d = "";

	switch ($f_status)
	{
		case 0:
			$status_d = "----";
			break;
		case 1:
			$status_d = "活動中";
			break;
		case 2:
			$status_d = "受注";
			break;
		case 3:
			$status_d = "失注";
			break;
/*
		case 4:
			$status_d = "TEL追跡中";
			break;
		case 5:
			$status_d = "TEL断念";
			break;
*/
	}

return $status_d;

}

//状況　リストボックス表示
function f_status_select($f_status)
{
	$status_html = "";
	$status_html .= "<select id=\"s_prj_status\" name=\"s_prj_status\">\n";
	switch ($f_status)
	{
		case 0:
			$status_html .= "<option value=\"0\" selected>----</option>\n";
			$status_html .= "<option value=\"1\">活動中</option>\n";
			$status_html .= "<option value=\"2\">受注</option>\n";
			$status_html .= "<option value=\"3\">失注</option>\n";
//			$status_html .= "<option value=\"4\">TEL追跡中</option>\n";
//			$status_html .= "<option value=\"5\">TEL断念</option>\n";
			break;
		case 1:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\" selected>活動中</option>\n";
			$status_html .= "<option value=\"2\">受注</option>\n";
			$status_html .= "<option value=\"3\">失注</option>\n";
//			$status_html .= "<option value=\"4\">TEL追跡中</option>\n";
//			$status_html .= "<option value=\"5\">TEL断念</option>\n";
			break;
		case 2:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\">活動中</option>\n";
			$status_html .= "<option value=\"2\" selected>受注</option>\n";
			$status_html .= "<option value=\"3\">失注</option>\n";
//			$status_html .= "<option value=\"4\">TEL追跡中</option>\n";
//			$status_html .= "<option value=\"5\">TEL断念</option>\n";
			break;
		case 3:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\">活動中</option>\n";
			$status_html .= "<option value=\"2\">受注</option>\n";
			$status_html .= "<option value=\"3\" selected>失注</option>\n";
//			$status_html .= "<option value=\"4\">TEL追跡中</option>\n";
//			$status_html .= "<option value=\"5\">TEL断念</option>\n";
			break;
/*
		case 4:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\">活動中</option>\n";
			$status_html .= "<option value=\"2\">受注</option>\n";
			$status_html .= "<option value=\"3\">失注</option>\n";
			$status_html .= "<option value=\"4\" selected>TEL追跡中</option>\n";
			$status_html .= "<option value=\"5\">TEL断念</option>\n";
			break;
		case 5:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\">活動中</option>\n";
			$status_html .= "<option value=\"2\">受注</option>\n";
			$status_html .= "<option value=\"3\">失注</option>\n";
			$status_html .= "<option value=\"4\">TEL追跡中</option>\n";
			$status_html .= "<option value=\"5\" selected>TEL断念</option>\n";
			break;
*/
	}
	$status_html .= "</select>\n";

return $status_html;

}

?>
