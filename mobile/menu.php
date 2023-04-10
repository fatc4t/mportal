<?php
require_once ("../workflow/public/f_function.php");
require_once ("../workflow/public/f_staff.php");
require_once ("../workflow/public/workflow.php");
require_once ("./common/lib/function.php");
require_once ("./common/lib/DBAccess_Function.php");

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();

list($agree_num, $cc_num) = workflow_alert($schema, $login_staff_id)

?>
<html>
<head>
<!-- ヘッダ -->
<?php include("common/include/include_header.php"); ?>

<!-- スタイルシート -->
<?php include("common/include/include_stylesheets.php"); ?>

<!-- アイコン -->
<?php include("common/include/include_icons.php"); ?>

<!-- スクリプト -->
<?php include("common/include/include_js.php"); ?>
</head>

<body id="winTop">
<?php h_header(); ?>
<?php h_menu(); ?>
<div class="overlay"></div>
<div id="container">
	<div class="inner">
		<ul>
                    <?php if($schema == 'first'){ ?>
                        <!-- ファーストさん専用メニュー -->
			<li class="osirase"><a href="profit/serch_stocklist.php">在庫管理</a></li>
                    <?php } else { ?>
                        <!-- グループウェア系(アキナイなど) -->
			<li class="osirase"><a href="topMessage/serch_message.php">トップメッセージ</a></li>
			<li class="report"><a href="noticeContact/serch_noticeContact.php">通達・連絡</a></li>
			<li class="kojin"><a href="dailyReport/serch_menu.php">日報</a></li>
			<li class="kojin"><a href="dailyReport/report_input.php">日報登録</a></li>
			<li class="workflow"><a href="workflow/apply.php">申請</a></li>
			<li class="workflow"><a href="workflow/serch_apply.php">承認&nbsp;&nbsp;<font color=red>(<?= $agree_num ?>件)</font>&nbsp;&nbsp;参照&nbsp;&nbsp;<font color=red>(<?= $cc_num ?>件)</font></a></li>
                    <?php } ?>
		</ul>
	</div>
</div>
<?php h_footer(); ?>
</body>
</html>
