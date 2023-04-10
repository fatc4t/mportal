<?php

require_once("../sales/public/f_function.php");

require_once("../sales/public/f_map.php");
require_once("../sales/public/f_mail.php");
require_once("../sales/public/f_customer.php");
require_once("../sales/public/f_staff.php");

require_once("../sales/public/f_session.php");

//---------------------
//毎日5:00に起動
//---------------------

//---------------------
//ToDoメール通知
//---------------------

	$today = date("Y-m-d");
	$m = 0;

	$sql = "SELECT * FROM t_customer_memo WHERE check_befor = 1 and check_after = 0 and todo != '0000-00-00' and todo != \"\" and todo >= '".$today."' ORDER BY todo";
	$result = executeQuery($sql);

	$rows = mysql_num_rows($result);

	if($rows){
		while($row = mysql_fetch_array($result)) {
			$check_list[$m][0] = $row["todo"];
			$check_list[$m][1] = f_customer_name_get($row["no"]);
			$check_list[$m][2] = $row["tantou"];
			$check_list[$m][3] = $row["memo"];
			$check_list[$m][4] = remain_check2($row["todo"]);
			$check_list[$m][5] = $row["todo_title"];
			$m ++;
		}
	}

	mysql_free_result($result);

	$sql = "SELECT * FROM t_project_memo WHERE check_befor = 1 and check_after = 0 and todo != '0000-00-00' and todo != \"\" and todo >= '".$today."' ORDER BY todo";
  	$result = executeQuery($sql);

	$rows = mysql_num_rows($result);

	if($rows){
   		while($row = mysql_fetch_array($result)) {
			$check_list[$m][0] = $row["todo"];
			$check_list[$m][1] = f_project_customername_get($row["no"]);
			$check_list[$m][2] = $row["tantou"];
			$check_list[$m][3] = $row["memo"];
			$check_list[$m][4] = remain_check2($row["todo"]);
			$check_list[$m][5] = $row["todo_title"];
			$m ++;
		}
	}

  	mysql_free_result($result);

	if ($m > 0){
		$sql = "SELECT * FROM t_staff WHERE todo_on = 1 and todo_mail > 0 and mail_address != \"\"";
	  	$result = executeQuery($sql);

		$rows = mysql_num_rows($result);

		if($rows){
	   		while($row = mysql_fetch_array($result)) {
				for ($ii = 0; $ii < $m; $ii++){
					if ($row["name"] == $check_list[$ii][2]){
						$mail_send_day = $check_list[$ii][4] - $row["todo_mail"];
						if ($mail_send_day == 0){
						// メールを送る

							$body = "M-PORTAL からのお知らせです。\n";
							$body .= "以下の期日となりました。\n";
							$body .= "\n";
							$body .= "期日：".$check_list[$ii][0]."\n";
							$body .= "\n";
							$body .= "顧客名：". $check_list[$ii][1]."\n";
							$body .= "担当者：".char_chg("to_dsp", $check_list[$ii][2])."\n";
							$body .= "\n";
							$body .= "タイトル：".char_chg("to_dsp", $check_list[$ii][5])."\n";
							$body .= "内容：\n";
							$body .= char_chg("to_dsp", $check_list[$ii][3])."\n";
							$body .= "\n";
							$body .= "このメールには返信しないでください。\n";

							$title = "ToDo 通知メール";

							send_mail2($row["mail_address"], $title, $body);

							echo $check_list[$ii][2]." mail sender successfully!<br />\n";

						}
					}
				}
			}
		}

	  	mysql_free_result($result);
	}


?>
