<?php
require_once ("../common/lib/function.php");
require_once ("../common/lib/DBAccess_Function.php");

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();

// 部門一覧の取得
$sqlD  = " SELECT ";
$sqlD .= "    sect_cd ";
$sqlD .= "   ,sect_nm ";
$sqlD .= "   FROM ".$schema.".mst1201 ";

$fD_rows = getList($sqlD);

$cntD = 0;
$cnt = 0;
$content = "";

// 部門条件の取得
$sect = $_POST['sect'];

// 商品名の取得
$prodnm = $_POST['name'];

// 検索条件部
$content .= "<div class=\"section\" >\n";
$content .= "<form id=\"stockListForm\" name=\"stockListForm\" method=\"POST\" action=\"./serch_stocklist.php\">\n";
$content .= "<h2 style=\"background-color: #00008b;color:#ffffff;\">部門　　\n";
$content .= "<select name=\"sect\" id=\"sect\">\n";
$content .= "<option value=\"\"></option>\n";
if($fD_rows){
    while($fD_row = $fD_rows[$cntD]) {
        if($sect == $fD_row["sect_cd"]){
            $content .= "<option value=\"".$fD_row["sect_cd"]."\" selected>".$fD_row["sect_nm"]."</option>\n";
        }else{
            $content .= "<option value=\"".$fD_row["sect_cd"]."\">".$fD_row["sect_nm"]."</option>\n";
        }
        $cntD += 1;
    }
}
$content .= "</select>\n";
$content .= "</h2>\n";
$content .= "<h2 style=\"background-color: #00008b;color:#ffffff;\">商品名　\n";
$content .= "<input type=\"text\" id=\"name\" name=\"name\" maxlength=\"8\" size=\"20\" value=\"".$prodnm."\"> \n";
$content .= "<input type=\"submit\" name=\"serch\" id=\"serch\" style=\"height:30;width:100;\" value=\"検索\"/>\n";
$content .= "</h2>\n";

// 商品一覧の取得
$sql  = " SELECT ";
$sql .= "    mst0201.prod_cd ";
$sql .= "   ,mst0201.prod_nm ";
$sql .= "   ,SUM(mst0204.total_stock_amout)::int     AS total_stock_amout ";
$sql .= "   FROM ".$schema.".mst0201 ";
$sql .= "   LEFT JOIN ".$schema.".mst0204 ON ( mst0201.prod_cd = mst0204.prod_cd and mst0201.organization_id = mst0204.organization_id ) ";
$sql .= "   INNER JOIN ".$schema.".mst1201 ON ( mst1201.sect_cd = mst0201.sect_cd and mst1201.organization_id = mst0201.organization_id ) ";
$sql .= "   LEFT JOIN ".$schema.".v_organization v ON v.organization_id = mst0204.organization_id  ";
$sql .= " where 1 = 1";

if($sect !== "" && $sect !== null){
    $sql .= " and mst1201.sect_cd = '".$sect."'";
}

if($prodnm !== "" && $prodnm !== null){
    $sql .= " and mst0201.prod_nm like '%".$prodnm."%'";
}

$sql .= "   GROUP BY ";
$sql .= "    mst0201.prod_cd ";
$sql .= "   ,prod_nm ";
$sql .= "   ORDER BY ";
$sql .= "    mst0201.prod_cd ";

$f_rows = getList($sql);

if($f_rows){
    // 一覧
    while($f_row = $f_rows[$cnt]) {
            $content .= "		<div class=\"section\">\n";
            $content .= "			<h2>".$f_row["prod_nm"]."</h2>\n";
            $content .= "			<ul class=\"voice\">\n";
            $content .= "			<li>在庫数 ".nl2br($f_row["total_stock_amout"])." 個</li>\n";
            $content .= "			</ul>\n";
            $content .= "		</div>\n";

        $cnt += 1;
    }
}
$content .= "</div>\n";

?>
<!DOCTYPE HTML>
<html>
<head>
<!-- ヘッダ -->
<?php include("../common/include/include_header.php"); ?>

<!-- スタイルシート -->
<?php include("../common/include/include_stylesheets.php"); ?>

<!-- アイコン -->
<?php include("../common/include/include_icons.php"); ?>

<!-- スクリプト -->
<?php include("../common/include/include_js.php"); ?>
</head>

<body id="winTop" class="serchTop">
<?php h_header(); ?>
<?php h_menu(); ?>
<div class="overlay"></div>
<div id="container">
	<div class="inner">
		<ul>
<?= $content ?>
		</ul>
	</div>
</div>
<?php h_footer(); ?>
</body>
</html>
