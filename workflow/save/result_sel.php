<?php

//�e��ʂ�PHP


require_once("../public/f_function.php");

require_once("../public/f_customer.php");
require_once("../public/f_staff.php");


require_once("../public/f_session.php");
session_start();
$login_staff = f_session_start($_SESSION["user_name"], $_SERVER["PHP_SELF"], $_SERVER["HTTP_USER_AGENT"], "../index.php", $_SERVER["REMOTE_ADDR"]);
$home = (isset($_GET['home'])) ? $_GET['home'] : 0;
$cd = (isset($_GET['cd'])) ? $_GET['cd'] : 0;
$page = (isset($_GET['page'])) ? $_GET['page'] : 0;



$result_id = $_GET['result_id'];


$report_dsp = "";

	$report_dsp .= "<tr>\n";
  	$report_dsp .= "<th width=\"50\">�I��</th>\n";
  	$report_dsp .= "<th width=\"100\">�o�^��</th>\n";
  	$report_dsp .= "<th width=\"200\">�ڋq��</th>\n";
  	$report_dsp .= "<th width=\"280\">�v���W�F�N�g��</th>\n";
  	$report_dsp .= "<th width=\"100\">�A�N�V����</th>\n";
  	$report_dsp .= "<th width=\"500\">�񍐓��e</th>\n";
	$report_dsp .= "</tr>\n";


$p_flag = 0;

	$sql_customer = "SELECT * FROM t_customer_memo WHERE action_no = '100' and result_id = '00000000000' and tantou = '".char_chg("to_db", $login_staff)."' ORDER BY create_date";
	$result_customer = executeQuery($sql_customer);
	$rows_customer = mysql_num_rows($result_customer);
	if($rows_customer){
		$p_flag = 1;
		while($row_customer = mysql_fetch_array($result_customer)) {

			$report_dsp .= "<tr>\n";
			$report_dsp .= "<td align=center><input type='checkbox' name='id[]' value='1-".$row_customer["id"]."'></td>\n";
$dd = substr($row_customer["create_date"], 0, 10);
			$report_dsp .= "<td align=center>".$dd."</td>\n";
			$report_dsp .= "<td align=left>".f_customer_name_get($row_customer["no"])."</td>\n";
			$report_dsp .= "<td align=center>�@</td>\n";
			$report_dsp .= "<td align=left>".f_code_dsp(1, $row_customer["action_no"])."</td>\n";
			$report_dsp .= "<td align=left>".char_chg("to_dsp", nl2br($row_customer["memo"]))."</td>\n";
			$report_dsp .= "</tr>\n";
		}
		mysql_free_result($result_customer);

	}

	$sql_project = "SELECT * FROM t_project_memo WHERE action_no = '100' and result_id = '00000000000' and tantou = '".char_chg("to_db", $login_staff)."' ORDER BY create_date";
	$result_project = executeQuery($sql_project);
	$rows_project = mysql_num_rows($result_project);

	if($rows_project){
		$p_flag = 1;
		while($row_project = mysql_fetch_array($result_project)) {

			$report_dsp .= "<tr>\n";
			$report_dsp .= "<td align=center><input type='checkbox' name='id[]' value='2-".$row_project["id"]."'></td>\n";
$dd = substr($row_project["create_date"], 0, 10);
			$report_dsp .= "<td align=center>".$dd."</td>\n";
			$report_dsp .= "<td align=left>".f_project_customername_get($row_project["no"])."</td>\n";
			$report_dsp .= "<td align=left>".f_project_name_get($row_project["no"])."</td>\n";
			$report_dsp .= "<td align=left>".f_code_dsp(1, $row_project["action_no"])."</td>\n";
			$report_dsp .= "<td align=left>".char_chg("to_dsp", nl2br($row_project["memo"]))."</td>\n";

			$report_dsp .= "</tr>\n";
		}
		mysql_free_result($result_project);


	}

	if ($p_flag == 0){
		$report_dsp .= "<tr>\n";
		$report_dsp .= "<td align=center colspan=6>�o���񍐂̃������L�^����Ă��܂���B�܂��͋L�^�����[���ʂ�c���Ă��������I</td>\n";
		$report_dsp .= "</tr>\n";
	}


//�e��ʂ�PHP


	//�@�\���G���A�̍쐬
	list($my_menu, $bookmark_name, $bookmark_url) = menu_bar("", $_SERVER["PHP_SELF"], "", "", "", 0);

	//�u�b�N�}�[�N�̍쐬
//	list($bookmark_button_disp, $bookmark_title) = f_bookmark_control($login_staff, $bookmark_name, $bookmark_url);
	
	//�g�b�v���j���[�̍쐬
	$menu_top = menu_top($home, $login_staff, "", "", 0, "");
	//�E�X���C�h���j���[�̍쐬
	$menu_side_right = menu_side_right($login_staff);
	//���X���C�h���j���[�̍쐬
	$menu_side_left = menu_side_left($home, $login_staff);



?>
<html lang="ja">
	<head>
		<!-- Meta -->
		<meta charset="sjis">
		<title>M-PORTAL</title>
		<meta name="description" content="Slidebars is a jQuery plugin for quickly and easily implementing app style off-canvas menus and sidebars into your website.">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">

<!-- �e��ʂ�SCRIPT -->
<script type="text/javascript" src="../../js/sales/jquery/jquery.min.js"></script>
<script type="text/javascript" src="../../js/sales/jquery/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../js/sales/jquery/jquery.jPrintArea.js"></script> 
<script type="text/javascript" src="../../js/sales/print.js"></script> 
<script src="../../js/bookmark.js" type="text/javascript"></script>

<!-- �e��ʂ�SCRIPT -->

		<!-- Stylesheets -->
<?php
	if ($home == 1){
		echo "<link rel=\"stylesheet\" href=\"../../css/home/bootstrap.min.css\">\n";
		echo "<link rel=\"stylesheet\" href=\"../../css/home/slidebars/slidebars.min.css\">\n";
		echo "<link rel=\"stylesheet\" href=\"../../css/home/mp_style.css\">\n";
	}else{
		echo "<link rel=\"stylesheet\" href=\"../../css/sales/bootstrap.min.css\">\n";
		echo "<link rel=\"stylesheet\" href=\"../../css/sales/slidebars/slidebars.min.css\">\n";
		echo "<link rel=\"stylesheet\" href=\"../../css/sales/mp_style.css\">\n";
	}
?>
		<link rel="stylesheet" href="../../css/mp_button.css">
		
		<!-- Web App -->
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		
		<!-- Favicons -->
		<link rel="icon" type="image/png" href="../../icons/16.png" sizes="16x16">
		<link rel="icon" type="image/png" href="../../icons/32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="../../icons/48.png" sizes="48x48">
		<link rel="icon" type="image/png" href="../../icons/64.png" sizes="64x64">
		
		<!-- Apple Touch Icons -->
		<link rel="apple-touch-icon" href="../../icons/152.png" sizes="120x120">
		<link rel="apple-touch-icon" href="../../icons/120.png" sizes="152x152">
		<link rel="apple-touch-icon" href="../../icons/76.png" sizes="76x76">
		<link rel="apple-touch-icon" href="../../icons/114.png" sizes="114x114">
		<link rel="apple-touch-icon" href="../../icons/57.png" sizes="57x57">
		<link rel="apple-touch-icon" href="../../icons/144.png" sizes="144x144">
		<link rel="apple-touch-icon" href="../../icons/72.png" sizes="72x72">

		<script src="../../js/drop_menu.js"></script>
	</head>
	
	<body id="top">
		<!-- Navbar -->
		<nav class="navbar navbar-default navbar-fixed-top sb-slide" role="navigation">
			<!-- Left Control -->
			<div class="sb-toggle-left navbar-left">
				<div class="navicon-line"></div>
				<div class="navicon-line"></div>
				<div class="navicon-line"></div>
			</div><!-- /.sb-control-left -->
			
			<!-- Right Control -->
			<div class="sb-toggle-right navbar-right">
				<div class="navicon-line"></div>
				<div class="navicon-line"></div>
				<div class="navicon-line"></div>
			</div><!-- /.sb-control-right -->

			<?= $menu_top ?>	<!-- Top Menu -->
		</nav>

		<!-- Site -->
		<div id="sb-site">
<form id="apply" method="post" action="apply.php?result_id=<?= $result_id ?>">
			<!-- �@�\���\���G���A menuNameArea --><?= $my_menu ?><!-- /.menuNameArea -->

			<!-- �������ʃ��X�g�G���A width�w��Ȃ� serchListAreaFree -->
			<div class="serchListAreaFree">
				<table>
					<tbody>

		<?= $report_dsp ?>


					</tbody>
				</table>
			</div><!-- /.serchListAreaFree -->

			<!-- �o�^�{�^���G���A logicButtonArea -->
			<div class="logicButtonArea">
				<p align=center>
<?php
		if ($p_flag != 0){
				echo "<input type='submit' value='�\��' class='confirm' />&nbsp;&nbsp;\n";
		}
?>
				<input type="button" onclick="location.href='apply_view.php?home=<?= $home ?>&cd=<?= $cd ?>&page=<?= $page ?>'" value="�߂�" class="return" />
				</p>
			</div><!-- /.logicButtonArea -->
</form>
		</div><!-- /#sb-site -->

		<!-- Slidebars -->
		<?= $menu_side_left ?>	<!-- Left Side Menu -->

		<?= $menu_side_right ?>	<!-- Right Side Menu -->

		<script src="../../js/sales/slidebars/slidebars.min.js"></script>
		<script src="../../js/sales/slidebars/mp_silde.js"></script>
		
		<!-- �������TOP�ɏグ�� -->
		<script src="../../js/mp_slow_up.js"></script>


	</body>
</html>
