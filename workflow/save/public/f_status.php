<?php

//ó‹µ@ƒ‰ƒxƒ‹•\¦
function f_status_dsp($f_status)
{
	$status_d = "";

	switch ($f_status)
	{
		case 0:
			$status_d = "----";
			break;
		case 1:
			$status_d = "Šˆ“®’†";
			break;
		case 2:
			$status_d = "ó’";
			break;
		case 3:
			$status_d = "¸’";
			break;
/*
		case 4:
			$status_d = "TEL’ÇÕ’†";
			break;
		case 5:
			$status_d = "TEL’f”O";
			break;
*/
	}

return $status_d;

}

//ó‹µ@ƒŠƒXƒgƒ{ƒbƒNƒX•\¦
function f_status_select($f_status)
{
	$status_html = "";
	$status_html .= "<select id=\"s_prj_status\" name=\"s_prj_status\">\n";
	switch ($f_status)
	{
		case 0:
			$status_html .= "<option value=\"0\" selected>----</option>\n";
			$status_html .= "<option value=\"1\">Šˆ“®’†</option>\n";
			$status_html .= "<option value=\"2\">ó’</option>\n";
			$status_html .= "<option value=\"3\">¸’</option>\n";
//			$status_html .= "<option value=\"4\">TEL’ÇÕ’†</option>\n";
//			$status_html .= "<option value=\"5\">TEL’f”O</option>\n";
			break;
		case 1:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\" selected>Šˆ“®’†</option>\n";
			$status_html .= "<option value=\"2\">ó’</option>\n";
			$status_html .= "<option value=\"3\">¸’</option>\n";
//			$status_html .= "<option value=\"4\">TEL’ÇÕ’†</option>\n";
//			$status_html .= "<option value=\"5\">TEL’f”O</option>\n";
			break;
		case 2:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\">Šˆ“®’†</option>\n";
			$status_html .= "<option value=\"2\" selected>ó’</option>\n";
			$status_html .= "<option value=\"3\">¸’</option>\n";
//			$status_html .= "<option value=\"4\">TEL’ÇÕ’†</option>\n";
//			$status_html .= "<option value=\"5\">TEL’f”O</option>\n";
			break;
		case 3:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\">Šˆ“®’†</option>\n";
			$status_html .= "<option value=\"2\">ó’</option>\n";
			$status_html .= "<option value=\"3\" selected>¸’</option>\n";
//			$status_html .= "<option value=\"4\">TEL’ÇÕ’†</option>\n";
//			$status_html .= "<option value=\"5\">TEL’f”O</option>\n";
			break;
/*
		case 4:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\">Šˆ“®’†</option>\n";
			$status_html .= "<option value=\"2\">ó’</option>\n";
			$status_html .= "<option value=\"3\">¸’</option>\n";
			$status_html .= "<option value=\"4\" selected>TEL’ÇÕ’†</option>\n";
			$status_html .= "<option value=\"5\">TEL’f”O</option>\n";
			break;
		case 5:
			$status_html .= "<option value=\"0\">----</option>\n";
			$status_html .= "<option value=\"1\">Šˆ“®’†</option>\n";
			$status_html .= "<option value=\"2\">ó’</option>\n";
			$status_html .= "<option value=\"3\">¸’</option>\n";
			$status_html .= "<option value=\"4\">TEL’ÇÕ’†</option>\n";
			$status_html .= "<option value=\"5\" selected>TEL’f”O</option>\n";
			break;
*/
	}
	$status_html .= "</select>\n";

return $status_html;

}

?>
