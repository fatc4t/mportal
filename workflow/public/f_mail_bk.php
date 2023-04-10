<?php

function send_mail($title, $body)
{

//mb_language("ja");
//mb_internal_encoding("utf-8");

/*
require 'PHPMailer/PHPMailerAutoload.php';

$mailer = new PHPMailer();
$mailer->IsSMTP();
$mailer->Encoding = "7bit";
$mailer->CharSet = '"iso-2022-jp"';

$mailer->Host = 'smtp.gmail.com';
$mailer->Port = 587;
$mailer->SMTPAuth = TRUE;
$mailer->SMTPSecure = "tls";
$mailer->Username = 'saleswars.millionet@gmail.com'; // Gmailログインアドレス
$mailer->Password = 'kimi5869'; // Gmailログインパスワード

$mailer->From     = 'saleswars.millionet@gmail.com'; // Fromアドレス
$mailer->FromName = "M-PORTAL";

$mailer->Subject  = mb_encode_mimeheader($title);
$mailer->Body  = mb_convert_encoding($body, "ISO-2022-JP", "auto");
$mailer->AddAddress('allstaff2@millionet.co.jp'); // Toアドレス

if($mailer->Send()){
	return 0;
}
else{
	return 1;
}
*/
	return 0;

}


function send_mail2($address, $title, $body)
{
/*
require_once 'PHPMailer/PHPMailerAutoload.php';

$mailer = new PHPMailer();
$mailer->IsSMTP();
$mailer->Encoding = "7bit";
$mailer->CharSet = '"iso-2022-jp"';

$mailer->Host = 'smtp.gmail.com';
$mailer->Port = 587;
$mailer->SMTPAuth = TRUE;
$mailer->SMTPSecure = "tls";
$mailer->Username = 'saleswars.millionet@gmail.com'; // Gmailログインアドレス
$mailer->Password = 'kimi5869'; // Gmailログインパスワード

$mailer->From     = 'saleswars.millionet@gmail.com'; // Fromアドレス
$mailer->FromName = "M-PORTAL";

$mailer->Subject  = mb_encode_mimeheader($title);
$mailer->Body  = mb_convert_encoding($body, "ISO-2022-JP", "auto");
$mailer->AddAddress($address); // Toアドレス

if($mailer->Send()){
	return 0;
}
else{
	return 1;
}
*/
	return 0;

}

function koko_mail_send( $user_name, $strLat, $strLng )
{
	$sql = "SELECT mail_address FROM t_staff WHERE koko_alert = 1";
	$result = executeQuery($sql);

	$f_rows = mysql_num_rows($result);

	$mail_address = "";

	if($f_rows){

		$addr = strLatLngToAddr( $strLat, $strLng );
		$body = $user_name." さんは、いまココにいます\n";
		$body .= "\n";
		$body .= date("Y-m-d H:i:s")."\n";
		$body .= $addr."\n";
		$body .= "\n";
		$body .= "このメールには返信しないでください。\n";

		while($f_rows = mysql_fetch_array($result)) {
			$mail_address .= $f_rows["mail_address"].",";
    		}

		send_mail2($mail_address, "[M-PORTAL]いまココ！通知", $body);

	}

    return 0;
}

?>
