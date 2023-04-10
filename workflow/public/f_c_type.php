<?php

//モバイル用　customerタイプのメニュー
function mobile_c_type_menu()
{
	$type_html = "";

	$sql = "SELECT no, name FROM m_code WHERE cd = 5 ORDER BY no";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	if($f_rows){
		while($f_rows = mysql_fetch_array($result)) {
			if ($f_rows["no"] == 0){
				$type_html .= "<li><a href=\"mobile-customer.php?c_type=".$f_rows["no"]."\" target=\"_self\">".mb_convert_encoding("検索・五十音順", "UTF-8", "auto")."</a></li>\n";
			}else{
				$type_html .= "<li><a href=\"mobile-customer.php?c_type=".$f_rows["no"]."\" target=\"_self\">".mb_convert_encoding($f_rows["name"], "UTF-8", "auto")."</a></li>\n";
			}
		}
	}



return $type_html;

}

?>
