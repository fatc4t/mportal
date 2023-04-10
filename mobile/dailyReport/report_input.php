<?php
require_once ("../common/lib/function.php");
require_once ("../common/lib/DBAccess_Function.php");

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$organization_id = $_SESSION["ORGANIZATION_ID"];
$_SESSION["SESSION_TIME"] = time();

// 対象日
$targetDate = (isset($_POST['target_date'])) ? $_POST['target_date'] : date("Y/m/d");

// 登録フラグ
$insertFlg = (isset($_POST['insert_flg'])) ? $_POST['insert_flg'] : 0;

if($insertFlg == 1){
// 登録処理

    // 日報フォーム情報を取得
    $sql_form  = "SELECT mrd.* FROM ".$schema.".m_daily_report_form as mr";
    $sql_form .= "       JOIN ".$schema.".m_daily_report_form_details as mrd on (mr.form_id = mrd.form_id)";
    $sql_form .= " WHERE mr.form_id = 1";
    $sql_form .= "   AND mrd.form_type IN (2,3,4,5,6,7,8,9)";
    $sql_form .= " ORDER BY mrd.disp_sort";

    $input_rows = getList($sql_form);
    
    $dataStr = "";
            
    $cnt_report = 0;
    if($input_rows){
            while($i_row = $input_rows[$cnt_report]) {
                $dataStr .= (isset($_POST[$i_row["form_details_id"]])) ? $_POST[$i_row["form_details_id"]] : "";
                $cnt_report += 1;
            }
    }
    
    $form_id = (isset($_POST['form_id'])) ? $_POST['form_id'] : 1;

    // 日報ヘッダ情報を登録
    $sql_daily_report = "INSERT INTO ".$schema.".t_daily_report (";
    $sql_daily_report .= "            target_date";
    $sql_daily_report .= "           ,user_id";
    $sql_daily_report .= "           ,organization_id";
    $sql_daily_report .= "           ,form_id";
    $sql_daily_report .= "           ,data";
    $sql_daily_report .= "           ,registration_time";
    $sql_daily_report .= "           ,registration_user_id";
    $sql_daily_report .= "           ,registration_organization";
    $sql_daily_report .= "           ,update_time";
    $sql_daily_report .= "           ,update_user_id";
    $sql_daily_report .= "           ,update_organization";
    $sql_daily_report .= " ) VALUES( ";
    $sql_daily_report .= "            '".$targetDate."'";
    $sql_daily_report .= "           ,'".$login_staff_id."'";
    $sql_daily_report .= "           ,'".$organization_id."'";
    $sql_daily_report .= "           ,'".$form_id."'";
    $sql_daily_report .= "           ,'".$dataStr."'";
    $sql_daily_report .= "           ,current_timestamp";
    $sql_daily_report .= "           ,'".$login_staff_id."'";
    $sql_daily_report .= "           ,'".$organization_id."'";
    $sql_daily_report .= "           ,current_timestamp";
    $sql_daily_report .= "           ,'".$login_staff_id."'";
    $sql_daily_report .= "           ,'".$organization_id."'";
    $sql_daily_report .= "           );";
    
    // 日報を登録
    sqlExec($sql_daily_report);

    // daily_report_idを取得するため、登録した日報を再取得
    $sql_daily_report  = "SELECT *";
    $sql_daily_report .= "  FROM ".$schema.".t_daily_report";
    $sql_daily_report .= " WHERE form_id = 1";
    $sql_daily_report .= "   AND target_date = '".$targetDate."'";
    $sql_daily_report .= "   AND user_id = '".$login_staff_id."'";
    $sql_daily_report .= "   AND organization_id = '".$organization_id."'";

    $dr_rows = getList($sql_daily_report);
    $input_daily_report_id = $dr_rows[0]["daily_report_id"];
    
    // 詳細ごとに登録
    $cnt_details = 0;
    if($input_rows){
        while($i_row = $input_rows[$cnt_details]) {

            $dataStr = (isset($_POST[$i_row["form_details_id"]])) ? $_POST[$i_row["form_details_id"]] : "";

            // 日報詳細情報を登録
            $sql_daily_report_details = "INSERT INTO ".$schema.".t_daily_report_details (";
            $sql_daily_report_details .= "            daily_report_id";
            $sql_daily_report_details .= "           ,target_date";
            $sql_daily_report_details .= "           ,user_id";
            $sql_daily_report_details .= "           ,organization_id";
            $sql_daily_report_details .= "           ,form_id";
            $sql_daily_report_details .= "           ,form_details_id";
            $sql_daily_report_details .= "           ,form_type";
            $sql_daily_report_details .= "           ,data";
            $sql_daily_report_details .= "           ,disp_sort";
            $sql_daily_report_details .= "           ,registration_time";
            $sql_daily_report_details .= "           ,registration_user_id";
            $sql_daily_report_details .= "           ,registration_organization";
            $sql_daily_report_details .= "           ,update_time";
            $sql_daily_report_details .= "           ,update_user_id";
            $sql_daily_report_details .= "           ,update_organization";
            $sql_daily_report_details .= " ) VALUES( ";
            $sql_daily_report_details .= "            '".$input_daily_report_id."'";
            $sql_daily_report_details .= "           ,'".$targetDate."'";
            $sql_daily_report_details .= "           ,'".$login_staff_id."'";
            $sql_daily_report_details .= "           ,'".$organization_id."'";
            $sql_daily_report_details .= "           ,'".$form_id."'";
            $sql_daily_report_details .= "           ,'".$i_row["form_details_id"]."'";
            $sql_daily_report_details .= "           ,'".$i_row["form_type"]."'";
            $sql_daily_report_details .= "           ,'".$dataStr."'";
            $sql_daily_report_details .= "           ,'".$i_row["disp_sort"]."'";
            $sql_daily_report_details .= "           ,current_timestamp";
            $sql_daily_report_details .= "           ,'".$login_staff_id."'";
            $sql_daily_report_details .= "           ,'".$organization_id."'";
            $sql_daily_report_details .= "           ,current_timestamp";
            $sql_daily_report_details .= "           ,'".$login_staff_id."'";
            $sql_daily_report_details .= "           ,'".$organization_id."'";
            $sql_daily_report_details .= "           );";

            // 日報を登録
            sqlExec($sql_daily_report_details);

            $cnt_details += 1;
        }
    }
    
}else if($insertFlg == 2){
// 更新処理

    // 日報フォーム情報を取得
    $sql_form  = "SELECT mrd.* FROM ".$schema.".m_daily_report_form as mr";
    $sql_form .= "       JOIN ".$schema.".m_daily_report_form_details as mrd on (mr.form_id = mrd.form_id)";
    $sql_form .= " WHERE mr.form_id = 1";
    $sql_form .= "   AND mrd.form_type IN (2,3,4,5,6,7,8,9)";
    $sql_form .= " ORDER BY mrd.disp_sort";

    $update_rows = getList($sql_form);
    
    $dataStr = "";
            
    $cnt_report = 0;
    if($update_rows){
            while($u_row = $update_rows[$cnt_report]) {
                $dataStr .= (isset($_POST[$u_row["form_details_id"]])) ? $_POST[$u_row["form_details_id"]] : "";
                $cnt_report += 1;
            }
    }
    
    $update_daily_report_id = (isset($_POST['daily_report_id'])) ? $_POST['daily_report_id'] : "";
    
    $form_id = (isset($_POST['form_id'])) ? $_POST['form_id'] : 1;

    // 日報ヘッダ情報を更新
    $sql_daily_report = "UPDATE ".$schema.".t_daily_report SET";
    $sql_daily_report .= "            data = '".$dataStr."'";
    $sql_daily_report .= "           ,update_time = current_timestamp";
    $sql_daily_report .= "           ,update_user_id = ".$login_staff_id;
    $sql_daily_report .= "           ,update_organization = ".$organization_id;
    $sql_daily_report .= " WHERE daily_report_id = ".$update_daily_report_id;
    
    // 日報を更新
    sqlExec($sql_daily_report);

    // 詳細ごとに更新
    $cnt_details = 0;
    if($update_rows){
        while($u_row = $update_rows[$cnt_details]) {

            $dataStr = (isset($_POST[$u_row["form_details_id"]])) ? $_POST[$u_row["form_details_id"]] : "";

            // 日報詳細情報を登録
            $sql_daily_report_details  = " UPDATE ".$schema.".t_daily_report_details SET";
            $sql_daily_report_details .= "        data = '".$dataStr."'";
            $sql_daily_report_details .= "       ,update_time = current_timestamp";
            $sql_daily_report_details .= "       ,update_user_id = ".$login_staff_id;
            $sql_daily_report_details .= "       ,update_organization = ".$organization_id;
            $sql_daily_report_details .= "  WHERE daily_report_id = ".$update_daily_report_id;
            $sql_daily_report_details .= "    AND form_details_id = ".$u_row["form_details_id"];

            // 日報を登録
            sqlExec($sql_daily_report_details);

            $cnt_details += 1;
        }
    }    
    
}else if($insertFlg == 3){
// 削除処理
    $del_daily_report_id = (isset($_POST['daily_report_id'])) ? $_POST['daily_report_id'] : "";

    // 日報ヘッダ情報を削除
    $sql_del_daily_report  = "DELETE FROM ".$schema.".t_daily_report ";
    $sql_del_daily_report .= " WHERE daily_report_id = ".$del_daily_report_id.";";
    
    // 日報を削除
    sqlExec($sql_del_daily_report);
    
    // 日報詳細情報を削除
    $sql_del_daily_report  = "DELETE FROM ".$schema.".t_daily_report_details ";
    $sql_del_daily_report .= " WHERE daily_report_id = ".$del_daily_report_id.";";
    
    // 日報を削除
    sqlExec($sql_del_daily_report);
    
    // 日報コメントを削除
    $sql_del_daily_report  = "DELETE FROM ".$schema.".t_daily_report_comment ";
    $sql_del_daily_report .= " WHERE daily_report_id = ".$del_daily_report_id.";";
    
    // 日報を削除
    sqlExec($sql_del_daily_report);
    
}

// 日報フォーム情報を取得
$sql  = "SELECT mrd.*, tr.data, tr.user_id, tr.daily_report_id FROM ".$schema.".m_daily_report_form as mr";
$sql .= "       JOIN ".$schema.".m_daily_report_form_details as mrd on (mr.form_id = mrd.form_id)";
$sql .= "       LEFT JOIN (SELECT *";
$sql .= "                   FROM ".$schema.".t_daily_report_details";
$sql .= "                  WHERE target_date = '".$targetDate."'";
$sql .= "                    AND user_id = ".$login_staff_id;
$sql .= "              ) as tr on (mrd.form_details_id = tr.form_details_id)";
$sql .= " WHERE mr.form_id = 1";
$sql .= " ORDER BY mrd.disp_sort";

$f_rows = getList($sql);

$cnt = 0;
$content = "";

$content .= "<form id=\"reportInput\" method=\"post\" enctype=\"multipart/form-data\" action=\"report_input.php?target_date=".$targetDate."\">\n";
                
// 先頭部分
$content .= "<div class=\"inner\">\n";
$content .= "	<p class=\"lead\">&nbsp;&nbsp;".$login_staff."&nbsp;&nbsp;";
$content .= "   <input type=\"text\" pattern=\"\d{4}/\d{2}/\d{2}\" title=\"西暦/月/日の形式で入力してください。\" id=\"target_date\" name=\"target_date\" size=\"10\" value=\"".$targetDate."\">";
$content .= "   <input id='btn' name='btn' type=\"button\" onclick=\"serch()\" value=\"検索\" class=\"request\"/>";
$content .= "   </p>\n";

if($f_rows){
    
        while($f_row = $f_rows[$cnt]) {

            // タイプに合わせて作成
            if( $f_row['form_type'] == 0){
                // 改行
                $content .= "		</div>\n";
            }else if( $f_row['form_type'] == 1){
                // ヘッダ
                $content .= "		<div class=\"section\">\n";
                $content .= "			<h2>".$f_row["form_details_name"]."</h2>\n";
                $content .= "			<ul class=\"voice\">\n";
            }else if( $f_row['form_type'] == 2){
                // テキスト
                $content .= "			<li>"."<input type=\"text\"";
                $content .= "			"."id=\"".$f_row["form_details_id"]."\"";
                $content .= "			"."name=\"".$f_row["form_details_id"]."\"";
                $content .= "			"."size=\""."43"."\"";
                $content .= "			"."style=\""."ime-mode:disabled;text-align:right;"."\"";
                $content .= "			>".$f_row["data"]."</li>\n";
                $content .= "			</ul>\n";
            }else if( $f_row['form_type'] == 3){
                // ラベル
                $content .= "			<li>".nl2br($f_row["data"])."</li>\n";
            }else if( $f_row['form_type'] == 4){
                // リスト
            }else if( $f_row['form_type'] == 5){
                // ラジオ
            }else if( $f_row['form_type'] == 6){
                // 日付
            }else if( $f_row['form_type'] == 7){
                // エリア
                $content .= "			<li>"."<TEXTAREA cols=45 rows=5";
                $content .= "			"."id=\"".$f_row["form_details_id"]."\"";
                $content .= "			"."name=\"".$f_row["form_details_id"]."\"";
                $content .= "			".">".$f_row["data"]."</TEXTAREA>"."</li>\n";
                $content .= "			</ul>\n";
            }else if( $f_row['form_type'] == 8){
                // チェックボックス
            }else if( $f_row['form_type'] == 9){
                // 表示専用 計算項目
            }
            
            $cnt += 1;
        }
}

// 日報IDを取得
$drSql  = "SELECT * FROM ".$schema.".t_daily_report";
$drSql .= " WHERE target_date = '".$targetDate."'";
$drSql .= "   AND user_id = ".$login_staff_id;

$dr_rows = getList($drSql);

$daily_report_id = $dr_rows[0]['daily_report_id'];
$content .= "<input type=\"hidden\" id=\"daily_report_id\" name=\"daily_report_id\" value=\"".$daily_report_id."\" />\n";

$content .= "<input type=\"hidden\" id=\"insert_flg\" name=\"insert_flg\" value=\"0\" />\n";

if($daily_report_id == ""){
    // 登録ボタン
    $content .= "<center><input id='iBtn' name='iBtn' type=\"button\" onclick=\"insert(1)\" value=\"登録\" class=\"request\"/></center>\n";
}else{
    // 更新ボタン
    $content .= "<center><input id='uBtn' name='uBtn' type=\"button\" onclick=\"insert(2)\" value=\"更新\" class=\"request\"/>　　";
    // 削除ボタン
    $content .= "<input id='dBtn' name='dBtn' type=\"button\" onclick=\"insert(3)\" value=\"削除\" class=\"request\"/></center>\n";
    
}


// 最終部分
$content .= "</div>\n";

// コメント情報
$sqlCC  = " SELECT  drc.daily_report_comment_id";
$sqlCC .= "        ,drc.contents";
$sqlCC .= "        ,to_char(drc.registration_time,'YYYY/MM/DD HH24時MI分SS秒') as registration_time";
$sqlCC .= "        ,drc.user_id";
$sqlCC .= "        ,v.user_name";
$sqlCC .= "        ,v.organization_name";
$sqlCC .= "   FROM ".$schema.".t_daily_report_comment as drc";
$sqlCC .= "   LEFT JOIN (SELECT * FROM ".$schema.".v_user WHERE eff_code = '適用中') v on (drc.user_id = v.user_id)";
$sqlCC .= "  WHERE drc.target_date = '".$targetDate."'";
$sqlCC .= "    AND drc.user_id = ".$login_staff_id;
$sqlCC .= "  ORDER BY drc.registration_time";

$fCC_rows = getList($sqlCC);

$cntCC = 0;
$contentCC = "";

if($fCC_rows){
        while($fCC_row = $fCC_rows[$cntCC]) {

            $contentCC .= "<div class=\"inner\">\n";
            $contentCC .= "	<p class=\"lead\">コメント</p>\n";
            $contentCC .= "		<div class=\"section\">\n";
            $contentCC .= "			<h2>".$fCC_row["user_name"]."&nbsp;&nbsp;".$fCC_row["registration_time"]."</h2>\n";
            $contentCC .= "			<ul class=\"voice\">\n";
            $contentCC .= "			<li>".$fCC_row["contents"]."</li>\n";
            $contentCC .= "			</ul>\n";
            $contentCC .= "		</div>\n";
            
            $cntCC += 1;
        }
}

$contentCC .= "</form>\n";

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

<script language="javascript" type="text/javascript">
function serch(){

	var target = document.getElementById("reportInput");
	target.submit();

}

function insert( flg ){

	document.getElementById("btn").disabled  = true;
	document.getElementById("insert_flg").value  = flg;
        
        if(flg == 1){
            document.getElementById("iBtn").disabled  = true;
        }else{
            document.getElementById("uBtn").disabled  = true;
            document.getElementById("dBtn").disabled  = true;
        }

        var target = document.getElementById("reportInput");
	target.submit();

}
/**
 *  DatePickerを設定
 */
$(function()
{
    $( "#target_date" ).datepicker({
        showButtonPanel: true,
        dateFormat: 'yy/mm/dd',
    });

});

</script>

</head>

<body id="winTop" class="infTop">
<?php h_header(); ?>
<?php h_menu(); ?>
<div class="overlay"></div>
<div id="container">
<?= $content ?>
<?= $contentCC ?>
</div>
<?php h_footer(); ?>
</body>
</html>
