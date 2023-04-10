<?php
	require_once './FileBoxDispatcher.php';

	$dispatcher = new FileBoxDispatcher();
	$dispatcher->dispatch();

	session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>M-PORTAL</title>

        <?php
            include("../home/View/Common/HtmlHeader.php"); 
        ?>


		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2" />
		<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" type="text/css" href="css/elfinder.min.css">
		<link rel="stylesheet" type="text/css" href="css/theme.css">
		<!-- Section JavaScript -->
		<!-- jQuery and jQuery UI (REQUIRED) -->
		<!--[if lt IE 9]>
		<script src="js/jquery-1.12.4.min.js"></script>
		<![endif]-->
		<!--[if gte IE 9]><!-->
		<script src="js/jquery-3.1.1.min.js"></script>
		<!--<![endif]-->
		<script src="js/1.12.1/jquery-ui.min.js"></script>

		<!-- elFinder JS (REQUIRED) -->
		<script src="js/elfinder.min.js"></script>

		<!-- GoogleDocs Quicklook plugin for GoogleDrive Volume (OPTIONAL) -->
		<!--<script src="js/extras/quicklook.googledocs.js"></script>-->

		<!-- elFinder translation (OPTIONAL) -->
		<script src="js/i18n/elfinder.jp.js"></script>

		<!-- elFinder initialization (REQUIRED) -->
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
				$('#elfinder').elfinder({
					url : 'php/connector.minimal.php'  // connector URL (REQUIRED)
					, lang: 'jp'                    // language (OPTIONAL)
				});
			});
		</script>
	</head>

    <body id="top">
<?php
include("../home/View/Common/PageHeader.php");
?>
    <div id="sb-site">
<?php
include("./View/Common/Breadcrumb.php");
?>
		<div id="elfinder"></div>
    </div><!-- /#sb-site -->
    <script type="text/javascript" src="jquery/js/scrolltopcontrol.js"></script> <!--スクロールしながらページのトップに戻る-->
    <script src="../js/home/slidebars/slidebars.min.js"></script>
    <script src="../js/home/slidebars/mp_silde.js"></script>
    </body>
</html>
