<?php

function h_header()
{

echo "<header id=\"header\" class=\"clearfix\">\n";
echo "	<h1 class=\"logo\"><a href=\"/mobile/menu.php\"><img src=\"/mobile/common/images/logo.png\" alt=\"M-PORTAL\"></a></h1>\n";
//echo "	<div class=\"util clearfix\"><span class=\"toggleBtn\"><a href=\"javascript:void(0);\"></a></span> </div>\n";
echo "	<div class=\"util clearfix\"><span style=\"height:33px;width:52px;float:right;margin-left:5px;\"></span></div>\n";
echo "</header>\n";

return;
}

function h_footer()
{

echo "<div id=\"pagetop\"> <a href=\"#winTop\">TOP<span></span></a> </div>\n";
echo "<footer id=\"footer\" class=\"clearfix\">\n";
echo "	<p id=\"cr\">Copyright(C) millionet Co.,Ltd.<br> All Rights Reserved.</p>\n";
echo "</footer>\n";

return;
}


function h_menu()
{

echo "<nav>\n";
echo "	<div class=\"menu\">\n";
echo "		<div class=\"osirase\">\n";
echo "			<p>トップメッセージ</p>\n";
echo "			<ul>\n";
echo "				<li><a href=\"#\">一覧</a></li>\n";
echo "			</ul>\n";
echo "		</div>\n";
echo "		<div class=\"report\">\n";
echo "			<p>通達・連絡</p>\n";
echo "			<ul>\n";
echo "				<li><a href=\"#\">一覧</a></li>\n";
echo "			</ul>\n";
echo "		</div>\n";
echo "		<div class=\"report\">\n";
echo "			<p>日報</p>\n";
echo "			<ul>\n";
echo "				<li><a href=\"#\">一覧</a></li>\n";
echo "			</ul>\n";
echo "		</div>\n";
echo "		<div class=\"workflow\">\n";
echo "			<p>ワークフロー</p>\n";
echo "			<ul>\n";
echo "				<li><a href=\"./workflow/serch_apply.php\">承認・参照&nbsp;&nbsp;<font color=red>(1件)</font></a></li>\n";
echo "			</ul>\n";
echo "		</div>\n";
echo "	</div>\n";
echo "</nav>\n";

return;
}


?>
