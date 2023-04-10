<?php
//アドレス指定配信
function send_mail($address, $title, $body)
{
	mb_language("japanese");
	mb_internal_encoding("UTF-8");
	mb_send_mail($address, $title, $body, "From:info@mportal.jp");

	return 0;
}
?>
