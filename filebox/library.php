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
<style type="text/css">
body {
	color: #6a6a6a;
}
#dir_tree ul {
	list-style-type: none;
	padding-left: 15px;
	_padding-left: 0px;
}
#dir_tree a, #dir_tree li {
	text-decoration: none;
	margin-bottom: 3px;
}
#dir_tree a {
	font-size:small;
	background-color:#f7f7f7;
	border-bottom:1px solid #f1f1f1;
	margin-left: 15px;
}
</style>
	</head>

    <body id="top">
<?php
include("../home/View/Common/PageHeader.php");
?>
    <div id="sb-site">
<?php
include("./View/Common/Breadcrumb.php");
?>
	<table align=center><tr><td>
		<tr><td align=center>		<!-- コンテンツエリア -->
<div id="dir_tree">
<?php
	$path = "./temp/".$schema."/";
	echo "<table width=\"800\" align=center bgcolor=white  border=1 cellspacing=0 cellpadding=0 bordercolor=gray><tr><td align=left>\n";
	echo "<br />\n";

	function createDir($path)
	{
		if ($handle = opendir($path))
		{
			echo "\n<ul>\n";
			$queue = array();
                        // ファイル名で一旦ソート
                        // ファイル・ディレクトリの一覧を $file_list 配列に
                        while (false !== ($file_list[] = readdir($handle))) ;
                        // ディレクトリハンドルを閉じる
                        closedir( $handle ) ;

                        // ファイル名(降順)にする
                        sort($file_list) ;
                                
                        $maxCnt = count( $file_list );

                        for( $i=0; $i < $maxCnt; $i++ )
                        {
                            if(false !== $file_list[$i] && '0' != $file_list[$i]){
                                
                                $file = $file_list[$i];
				if (is_dir($path.$file) && $file != '.' && $file !='..') {
					printSubDir($file, $path, $queue);
				} else if ($file != '.' && $file !='..') {
					$queue[] = $file;
				}
                            }
			}

                        // ファイル名でソート
                        sort($queue);
                        
			printQueue($queue, $path);
			echo "</ul>\n";
		}

	}

	function printQueue($queue, $path)
	{
		foreach ($queue as $file)
		{
			printFile($file, $path);
		}
	}

	function printFile($file, $path)
	{
		if ($file != "Thumbs.db" && $file != "index.html"){
			echo "<li><a href=\"".$path.$file."\" download=\"".$file."\">$file</a></li>\n";
		}
	}

	function printSubDir($dir, $path)
	{
		if ($dir != ".quarantine" && $dir != ".tmb"){
			echo "<li><span class=\"dir\">$dir</span>";
			createDir($path.$dir."/");
			echo "</li>\n";
		}
	}

	createDir($path);

echo "<br />\n";
echo "</td></tr></table>\n";

?>

</div>

<script type="text/javascript">
$(function() {
	$("span.dir").css("cursor", "pointer").prepend("+ ").click(function() {
		$(this).next().toggle("fast");
		
		var v = $(this).html().substring( 0, 1 );
		if ( v == "+" )
			$(this).html( "-" + $(this).html().substring( 1 ) );
		else if ( v == "-" )
			$(this).html( "+" + $(this).html().substring( 1 ) );
	}).next().hide();
	
    $("#dir_tree a, #dir_tree span.dir").hover(function() {
        $(this).css("font-weight", "bold");
    }, function() {
        $(this).css("font-weight", "normal");
    });
});
</script>
		</td></tr>
	</table>
    </div><!-- /#sb-site -->
    <script type="text/javascript" src="jquery/js/scrolltopcontrol.js"></script> <!--スクロールしながらページのトップに戻る-->
    <script src="../js/home/slidebars/slidebars.min.js"></script>
    <script src="../js/home/slidebars/mp_silde.js"></script>
    </body>
</html>
