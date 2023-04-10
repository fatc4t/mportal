<?php
require_once("./f_function.php");

$user_name = $_GET['user_name'];
$title = $_GET['title'];
$url = $_GET['url'];
$bookmark_f = $_GET['bookmark_f'];

if ($bookmark_f == "ÌÞ¯¸Ï°¸“o˜^"){

	$key = "sales";
	if (strpos($url,'/mportal/sales/') !== false){
		$key = "sales";
	}else{
		$key = "ordering";
	}

	$sql = "INSERT INTO t_bookmark VALUES(NULL, '".$key."', '0', '".char_chg("to_db", $user_name)."', '".char_chg("to_db", $title)."', '".char_chg("to_db", $url)."', CURRENT_TIMESTAMP)";
	$result = executeQuery($sql);
}else{
	$sql = "DELETE FROM t_bookmark WHERE user_name = '".char_chg("to_db", $user_name)."' and title = '".char_chg("to_db", $title)."' and url = '".char_chg("to_db", $url)."'";
	$result = executeQuery($sql);
}

$return_flag = 0;

echo $return_flag;

?>
