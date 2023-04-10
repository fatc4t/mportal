<?php


   require_once './WorkFlowDispatcher.php';

    $dispatcher = new WorkFlowDispatcher();
    $dispatcher->dispatch();
//$_SESSION["SCHEMA"]

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();


//各画面のPHP
require_once("DBAccess_Function.php");

require_once("./public/f_function.php");
require_once("./public/f_mail.php");
require_once("./public/f_status.php");
require_once("./public/f_customer.php");
require_once("./public/f_staff.php");
require_once("./public/workflow.php");


//flag:0(無視)1(承認対象)2(承認待ち)3(承認済み)
//cc_flag:0(無視)1(参照対象)2(参照待ち)3(参照済み)
//stat:0(無視)1(申請中)2(否決)3(可決)4(取戻)5(差戻)

//workflow_id
//user_id
//busho_code
//yyyy
//mm
//dd
//flow_doc_code
//next1_user_id
//next1_flag
//next1_cc1_user_id
//next1_cc1_flag
//next1_cc2_user_id
//next1_cc2_flag
//next1_cc3_user_id
//next1_cc3_flag


	if ( isset($_GET['workflow_id']) ) {
		$workflow_id = $_GET['workflow_id'];
	}else{
		$workflow_id = 0;
	}

	$workflow_doc_html = "";
	$workflow_stat_html = "";
	
	$workflow_doc_html = workflow_doc_inf(9, $workflow_id, $login_staff_id);
	$workflow_stat_html = workflow_stat(0, $workflow_id, $login_staff_id, 0, 0, 0);

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>M-PORTAL</title>

        <?php
            include("../home/View/Common/HtmlHeader.php"); 
        ?>


<meta name="description" content="Slidebars is a jQuery plugin for quickly and easily implementing app style off-canvas menus and sidebars into your website.">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">

<!-- 各画面のスクリプト -->

<link rel="stylesheet" href="./css/sales/sales.css" type="text/css" />
<script type="text/javascript" src="./js/sales/jquery/jquery.min.js"></script>
<script type="text/javascript" src="./js/sales/jquery/jquery.jPrintArea.js"></script> 
<script type="text/javascript" src="./js/sales/print.js"></script> 

	</head>

    <body id="top">
<?php
include("../home/View/Common/PageHeader.php");
?>
		<div id="sb-site">
<?php
include("./View/Common/Breadcrumb_workflow_dsp.php");
?>
			<!-- serchEditArea -->
			<div class="serchEditArea">
				<?= $workflow_doc_html ?>
			</div><!-- /.serchEditArea -->

			<!-- serchEditArea -->
			<div class="serchEditArea">
				<?= $workflow_stat_html ?>
			</div><!-- /.serchEditArea -->
		</div><!-- /#sb-site -->

    <script src="../js/home/slidebars/slidebars.min.js"></script>
    <script src="../js/home/slidebars/mp_silde.js"></script>
    </body>
</html>
