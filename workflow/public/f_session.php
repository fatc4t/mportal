<?php

//�󋵁@���X�g�{�b�N�X�\���@�e���A�|
function f_session_start($session, $self, $server, $returnpass, $ipaddress)
{
	if (!isset($session)) {
		log_insert(char_insert("�s��"), char_insert("�s���A�N�Z�X"), "", "", $server, $ipaddress);
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
