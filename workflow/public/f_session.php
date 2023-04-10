<?php

//状況　リストボックス表示　テレアポ
function f_session_start($session, $self, $server, $returnpass, $ipaddress)
{
	if (!isset($session)) {
		log_insert(char_insert("不明"), char_insert("不正アクセス"), "", "", $server, $ipaddress);
		$no_login_url = $returnpass;
		header("Location: {$no_login_url}");
		exit;
	}else{
//INO 20160816		$login_staff = $session;
$login_staff = f_staff_name_get($session);
	}

	return $login_staff;
}

?>
