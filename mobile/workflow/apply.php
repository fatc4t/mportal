<?php

session_start();
$schema = $_SESSION["SCHEMA"];
$login_staff = $_SESSION["USER_NAME"];
$login_staff_id = $_SESSION["USER_ID"];
$_SESSION["SESSION_TIME"] = time();

//各画面のPHP
require_once ("../common/lib/DBAccess_Function.php");

require_once ("../../workflow/public/f_function.php");
require_once ("../../workflow/public/f_mail.php");
require_once ("../../workflow/public/f_status.php");
require_once ("../../workflow/public/f_customer.php");

require_once ("../../workflow/public/f_staff.php");
require_once ("../../workflow/public/workflow.php");
require_once ("../common/lib/function.php");


//flag:0(無視)1(承認対象)2(承認待ち)3(承認済み)4(否決済み)
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

	$tmp_filename_1 = "";
	$filename_1 = "";
	$kakuchousi_1 = "";
	$tmp_filename_2 = "";
	$filename_2 = "";
	$kakuchousi_2 = "";
	$tmp_filename_3 = "";
	$filename_3 = "";
	$kakuchousi_3 = "";

	$sql_file_1 = " NULL, NULL, NULL";
	$sql_file_2 = " NULL, NULL, NULL";
	$sql_file_3 = " NULL, NULL, NULL";

//再申請
$remand = (isset($_GET['remand'])) ? $_GET['remand'] : 0;
$remand_no = (isset($_GET['remand_no'])) ? $_GET['remand_no'] : 0;
$tenp_del = (isset($_GET['tenp_del'])) ? $_GET['tenp_del'] : 0;
$tenp_del_1 = (isset($_GET['tenp_del_1'])) ? $_GET['tenp_del_1'] : 0;
$tenp_del_2 = (isset($_GET['tenp_del_2'])) ? $_GET['tenp_del_2'] : 0;
$tenp_del_3 = (isset($_GET['tenp_del_3'])) ? $_GET['tenp_del_3'] : 0;
$tenp_del_1_f = 0;
$tenp_del_2_f = 0;
$tenp_del_3_f = 0;


//再利用
$workflow_id = (isset($_GET['workflow_id'])) ? $_GET['workflow_id'] : 0;
$r_flow_doc_code = 0;	//申請文書
$r_title = "";	//タイトル
$r_comment = "";	//内容
$r_tmp_name1 = "";
$r_file_name1 = "";
$r_kakuchousi1 = "";
$r_tmp_name2 = "";
$r_file_name2 = "";
$r_kakuchousi2 = "";
$r_tmp_name3 = "";
$r_file_name3 = "";
$r_kakuchousi3 = "";
$parent_workflow_id = 0;
$remand_f = 0;
$workflow_no = "";
if ($workflow_id != 0){
	$sql = "SELECT * FROM ".$_SESSION["SCHEMA"].".t_workflow WHERE workflow_id = ".$workflow_id;

        $result = getList($sql);
	$f_rows = $result[0];
	if($f_rows){
		$r_flow_doc_code = $f_rows["flow_doc_code"];	//申請文書
		$r_title = $f_rows["title"];	//タイトル
		$r_comment = nl2br($f_rows["comment"]);	//内容
		if ($tenp_del != 1 && $tenp_del_1 == 0 && $f_rows["tmp_name1"] != ""){
			$r_tmp_name1 = $f_rows["tmp_name1"];
			$r_file_name1 = $f_rows["file_name1"];
			$r_kakuchousi1 = $f_rows["kakuchousi1"];
			$tenp_del_1_f = 1;
		}else if($tenp_del == 1){
			$tenp_del_1 = 1;
		}
		if ($tenp_del != 2 && $tenp_del_2 == 0 && $f_rows["tmp_name2"] != ""){
			$r_tmp_name2 = $f_rows["tmp_name2"];
			$r_file_name2 = $f_rows["file_name2"];
			$r_kakuchousi2 = $f_rows["kakuchousi2"];
			$tenp_del_2_f = 1;
		}else if($tenp_del == 2){
			$tenp_del_2 = 1;
		}
		if ($tenp_del != 3 && $tenp_del_3 == 0 && $f_rows["tmp_name3"] != ""){
			$r_tmp_name3 = $f_rows["tmp_name3"];
			$r_file_name3 = $f_rows["file_name3"];
			$r_kakuchousi3 = $f_rows["kakuchousi3"];
			$tenp_del_3_f = 1;
		}else if($tenp_del == 3){
			$tenp_del_3 = 1;
		}
		if ($remand == 2){
			$workflow_no = $f_rows["workflow_no"];
			$parent_workflow_id = $workflow_id;
		}
	}
}


$cd = (isset($_GET['cd'])) ? $_GET['cd'] : 0;
$home = (isset($_GET['home'])) ? $_GET['home'] : 0;
$flow_doc = (isset($_POST['flow_doc'])) ? $_POST['flow_doc'] : 0;
$w_subject = (isset($_POST['w_subject'])) ? $_POST['w_subject'] : "";
//$w_comment = (isset($_POST['w_comment'])) ? $_POST['w_comment'] : "";
$w_check = (isset($_POST['w_check'])) ? $_POST['w_check'] : 0;
$w_pay_3 = (isset($_POST['w_pay_3'])) ? $_POST['w_pay_3'] : "";
$w_pay_price_3 = (isset($_POST['w_pay_price_3'])) ? $_POST['w_pay_price_3'] : "";
$w_pay_num_3 = (isset($_POST['w_pay_num_3'])) ? $_POST['w_pay_num_3'] : "";
$w_pay_method_3 = (isset($_POST['w_pay_method_3'])) ? $_POST['w_pay_method_3'] : "";
$w_pay_date_3 = (isset($_POST['w_pay_date_3'])) ? $_POST['w_pay_date_3'] : "";
$w_pay_4 = (isset($_POST['w_pay_4'])) ? $_POST['w_pay_4'] : "";
$w_pay_price_4 = (isset($_POST['w_pay_price_4'])) ? $_POST['w_pay_price_4'] : "";
$w_pay_num_4 = (isset($_POST['w_pay_num_4'])) ? $_POST['w_pay_num_4'] : "";
$w_pay_method_4 = (isset($_POST['w_pay_method_4'])) ? $_POST['w_pay_method_4'] : "";
$w_pay_date_4 = (isset($_POST['w_pay_date_4'])) ? $_POST['w_pay_date_4'] : "";
$w_pay_5 = (isset($_POST['w_pay_5'])) ? $_POST['w_pay_5'] : "";
$w_pay_price_5 = (isset($_POST['w_pay_price_5'])) ? $_POST['w_pay_price_5'] : "";
$w_pay_num_5 = (isset($_POST['w_pay_num_5'])) ? $_POST['w_pay_num_5'] : "";
$w_pay_method_5 = (isset($_POST['w_pay_method_5'])) ? $_POST['w_pay_method_5'] : "";
$w_pay_date_5 = (isset($_POST['w_pay_date_5'])) ? $_POST['w_pay_date_5'] : "";
$w_start = (isset($_POST['w_start'])) ? $_POST['w_start'] : "";
$w_end = (isset($_POST['w_end'])) ? $_POST['w_end'] : "";
$w_day = (isset($_POST['w_day'])) ? $_POST['w_day'] : "";
$w_holiday = (isset($_POST['w_holiday'])) ? $_POST['w_holiday'] : "";
$w_wake = (isset($_POST['w_wake'])) ? $_POST['w_wake'] : "";
$result_id = (isset($_GET['result_id'])) ? $_GET['result_id'] : 0;

	$set_f = 0;
	$workflow_html = "";
	$button_html = "";
	$form1_html = "";
	$form2_html = "";
	$script_html = "";

//申請ルートを作成
			$kyoten = 0;	//f_staff_kyoten_get($login_staff);
			$busho_code = f_staff_busho_id_get($login_staff_id);
			$staff_code = $login_staff_id;	//f_staff_id_get($login_staff);
			$rank_code = f_staff_rank_id_get($login_staff_id);
			$now_date = date("Y-m-d");
			$yyyy = substr($now_date, 0, 4);
			$mm = substr($now_date, 5, 2);
			$dd = substr($now_date, 8, 2);


	if ($cd == 1){

		$route_f = workflow_route_check($kyoten, $busho_code, $flow_doc, $rank_code);
		if ($flow_doc != 0 && $w_subject != "" && $route_f != 0){

			$set_f = 1;

//検証用　情報取得　print_r(mb_get_info());

//添付ファイルを取得

			if ($remand == 2 && $r_tmp_name1 != ""){
				$tmp_filename_1 = $r_tmp_name1;
				$filename_1 = char_chg("to_dsp", $r_file_name1);
				$kakuchousi_1 = $r_kakuchousi1;
			}else{
				if( isset( $_POST["userfile_name1"] ) ) {
					$tmp_name_pass = $_FILES["userfile1"]["tmp_name"];
					$name = $_POST["userfile_name1"];
					if ($name != ""){
						$kakuchousi_1 = mb_pathinfo($name);
						$filename_1 = mb_basename($name, $kakuchousi_1);
						$tmp_filename_0 = mb_basename($tmp_name_pass);
						$tmp_filename_kakuchousi = mb_pathinfo($tmp_filename_0);
						$tmp_filename_1 = $tmp_filename_0.".".$kakuchousi_1;
						move_uploaded_file($tmp_name_pass, "../../workflow/file/".$schema."/".$tmp_filename_1);
					}
				}
			}
			if ($remand == 2 && $r_tmp_name2 != ""){
				$tmp_filename_2 = $r_tmp_name2;
				$filename_2 = char_chg("to_dsp", $r_file_name2);
				$kakuchousi_2 = $r_kakuchousi2;
			}else{
				if( isset( $_POST["userfile_name2"] ) ) {
					$tmp_name_pass = $_FILES["userfile2"]["tmp_name"];
					$name = $_POST["userfile_name2"];
					if ($name != ""){
						$kakuchousi_2 = mb_pathinfo($name);
						$filename_2 = mb_basename($name, $kakuchousi_2);
						$tmp_filename_0 = mb_basename($tmp_name_pass);
						$tmp_filename_kakuchousi = mb_pathinfo($tmp_filename_0);
						$tmp_filename_2 = $tmp_filename_0.".".$kakuchousi_2;
						move_uploaded_file($tmp_name_pass, "../../workflow/file/".$schema."/".$tmp_filename_2);
					}
				}
			}
			if ($remand == 2 && $r_tmp_name3 != ""){
				$tmp_filename_3 = $r_tmp_name3;
				$filename_3 = char_chg("to_dsp", $r_file_name3);
				$kakuchousi_3 = $r_kakuchousi3;
			}else{
				if( isset( $_POST["userfile_name3"] ) ) {
					$tmp_name_pass = $_FILES["userfile3"]["tmp_name"];
					$name = $_POST["userfile_name3"];
					if ($name != ""){
						$kakuchousi_3 = mb_pathinfo($name);
						$filename_3 = mb_basename($name, $kakuchousi_3);
						$tmp_filename_0 = mb_basename($tmp_name_pass);
						$tmp_filename_kakuchousi = mb_pathinfo($tmp_filename_0);
						$tmp_filename_3 = $tmp_filename_0.".".$kakuchousi_3;
						move_uploaded_file($tmp_name_pass, "../../workflow/file/".$schema."/".$tmp_filename_3);
					}
				}
			}

			if ($tmp_filename_1 != ""){
				$sql_file_1 = " '".$tmp_filename_1."', '".char_chg("to_db", $filename_1)."', '".$kakuchousi_1."'";
				if ($tmp_filename_2 != ""){
					$sql_file_2 = " '".$tmp_filename_2."', '".char_chg("to_db", $filename_2)."', '".$kakuchousi_2."'";
					if ($tmp_filename_3 != ""){
						$sql_file_3 = " '".$tmp_filename_3."', '".char_chg("to_dsp", $filename_3)."', '".$kakuchousi_3."'";
					}
				}else{
					if ($tmp_filename_3 != ""){
						$sql_file_2 = " '".$tmp_filename_3."', '".char_chg2("to_db", $filename_3)."', '".$kakuchousi_3."'";
					}
				}
			}else{
				if ($tmp_filename_2 != ""){
					$sql_file_1 = " '".$tmp_filename_2."', '".char_chg2("to_db", $filename_2)."', '".$kakuchousi_2."'";
					if ($tmp_filename_3 != ""){
						$sql_file_2 = " '".$tmp_filename_3."', '".char_chg2("to_db", $filename_3)."', '".$kakuchousi_3."'";
					}
				}else{
					if ($tmp_filename_3 != ""){
						$sql_file_1 = " '".$tmp_filename_3."', '".char_chg2("to_db", $filename_3)."', '".$kakuchousi_3."'";
					}
				}
			}

			list (  $flow_data[1][0],$flow_data[1][1],$flow_data[1][2],$flow_data[1][3],$flow_data[1][4],$flow_data[1][5],
				$flow_data[2][0],$flow_data[2][1],$flow_data[2][2],$flow_data[2][3],$flow_data[2][4],$flow_data[2][5],
				$flow_data[3][0],$flow_data[3][1],$flow_data[3][2],$flow_data[3][3],$flow_data[3][4],$flow_data[3][5],
				$flow_data[4][0],$flow_data[4][1],$flow_data[4][2],$flow_data[4][3],$flow_data[4][4],$flow_data[4][5],
				$flow_data[5][0],$flow_data[5][1],$flow_data[5][2],$flow_data[5][3],$flow_data[5][4],$flow_data[5][5],
				$flow_data[6][0],$flow_data[6][1],$flow_data[6][2],$flow_data[6][3],$flow_data[6][4],$flow_data[6][5],
				$flow_data[7][0],$flow_data[7][1],$flow_data[7][2],$flow_data[7][3],$flow_data[7][4],$flow_data[7][5],
				$flow_data[8][0],$flow_data[8][1],$flow_data[8][2],$flow_data[8][3],$flow_data[8][4],$flow_data[8][5],
				$flow_data[9][0],$flow_data[9][1],$flow_data[9][2],$flow_data[9][3],$flow_data[9][4],$flow_data[9][5],
				$flow_data[10][0],$flow_data[10][1],$flow_data[10][2],$flow_data[10][3],$flow_data[10][4],$flow_data[10][5]) = f_workflow_route($kyoten, $busho_code, $flow_doc, $rank_code);
			list($cc1, $cc2, $cc3, $cc4, $cc5) = f_workflow_route_cc($kyoten, $busho_code, $flow_doc, $rank_code);
			//申請書の登録

			$w_comment = "";

			if ($remand_no == 0){	//再申請は同じ申請番号を使う
				$workflow_no = workflow_no($schema, $busho_code, $staff_code, $yyyy, $mm, $dd);
			}


			$sql = "INSERT INTO ".$schema.".t_workflow ";
			$sql .= "(user_id,";
			$sql .= "busho_code, yyyy, mm, dd, title, after_check, result_id, comment, flow_doc_code,";
			$sql .= "next1_data, ";
			$sql .= "next1_data_comment1, next1_data_date1, ";
			$sql .= "next1_data_comment2, next1_data_date2, ";
			$sql .= "next1_data_comment3, next1_data_date3, ";
			$sql .= "next1_data_comment4, next1_data_date4, ";
			$sql .= "next1_data_comment5, next1_data_date5, ";
			$sql .= "next1_data_comment6, next1_data_date6, ";
			$sql .= "next1_data_comment7, next1_data_date7, ";
			$sql .= "next1_data_comment8, next1_data_date8, ";
			$sql .= "next1_data_comment9, next1_data_date9, ";
			$sql .= "next1_data_comment10, next1_data_date10, ";
			$sql .= "next1_cc1_data, next1_cc1_data_date, next1_cc2_data, next1_cc2_data_date, next1_cc3_data, next1_cc3_data_date, next1_cc4_data, next1_cc4_data_date, next1_cc5_data, next1_cc5_data_date,";
			$sql .= "next2_data, ";
			$sql .= "next2_data_comment1, next2_data_date1, ";
			$sql .= "next2_data_comment2, next2_data_date2, ";
			$sql .= "next2_data_comment3, next2_data_date3, ";
			$sql .= "next2_data_comment4, next2_data_date4, ";
			$sql .= "next2_data_comment5, next2_data_date5, ";
			$sql .= "next2_data_comment6, next2_data_date6, ";
			$sql .= "next2_data_comment7, next2_data_date7, ";
			$sql .= "next2_data_comment8, next2_data_date8, ";
			$sql .= "next2_data_comment9, next2_data_date9, ";
			$sql .= "next2_data_comment10, next2_data_date10, ";
			$sql .= "next2_cc1_data, next2_cc1_data_date, next2_cc2_data, next2_cc2_data_date, next2_cc3_data, next2_cc3_data_date, next2_cc4_data, next2_cc4_data_date, next2_cc5_data, next2_cc5_data_date,";
			$sql .= "next3_data, ";
			$sql .= "next3_data_comment1, next3_data_date1, ";
			$sql .= "next3_data_comment2, next3_data_date2, ";
			$sql .= "next3_data_comment3, next3_data_date3, ";
			$sql .= "next3_data_comment4, next3_data_date4, ";
			$sql .= "next3_data_comment5, next3_data_date5, ";
			$sql .= "next3_data_comment6, next3_data_date6, ";
			$sql .= "next3_data_comment7, next3_data_date7, ";
			$sql .= "next3_data_comment8, next3_data_date8, ";
			$sql .= "next3_data_comment9, next3_data_date9, ";
			$sql .= "next3_data_comment10, next3_data_date10, ";
			$sql .= "next3_cc1_data, next3_cc1_data_date, next3_cc2_data, next3_cc2_data_date, next3_cc3_data, next3_cc3_data_date, next3_cc4_data, next3_cc4_data_date, next3_cc5_data, next3_cc5_data_date,";
			$sql .= "next4_data, ";
			$sql .= "next4_data_comment1, next4_data_date1, ";
			$sql .= "next4_data_comment2, next4_data_date2, ";
			$sql .= "next4_data_comment3, next4_data_date3, ";
			$sql .= "next4_data_comment4, next4_data_date4, ";
			$sql .= "next4_data_comment5, next4_data_date5, ";
			$sql .= "next4_data_comment6, next4_data_date6, ";
			$sql .= "next4_data_comment7, next4_data_date7, ";
			$sql .= "next4_data_comment8, next4_data_date8, ";
			$sql .= "next4_data_comment9, next4_data_date9, ";
			$sql .= "next4_data_comment10, next4_data_date10, ";
			$sql .= "next4_cc1_data, next4_cc1_data_date, next4_cc2_data, next4_cc2_data_date, next4_cc3_data, next4_cc3_data_date, next4_cc4_data, next4_cc4_data_date, next4_cc5_data, next4_cc5_data_date,";
			$sql .= "next5_data, ";
			$sql .= "next5_data_comment1, next5_data_date1, ";
			$sql .= "next5_data_comment2, next5_data_date2, ";
			$sql .= "next5_data_comment3, next5_data_date3, ";
			$sql .= "next5_data_comment4, next5_data_date4, ";
			$sql .= "next5_data_comment5, next5_data_date5, ";
			$sql .= "next5_data_comment6, next5_data_date6, ";
			$sql .= "next5_data_comment7, next5_data_date7, ";
			$sql .= "next5_data_comment8, next5_data_date8, ";
			$sql .= "next5_data_comment9, next5_data_date9, ";
			$sql .= "next5_data_comment10, next5_data_date10, ";
			$sql .= "next5_cc1_data, next5_cc1_data_date, next5_cc2_data, next5_cc2_data_date, next5_cc3_data, next5_cc3_data_date, next5_cc4_data, next5_cc4_data_date, next5_cc5_data, next5_cc5_data_date,";
			$sql .= "next6_data, ";
			$sql .= "next6_data_comment1, next6_data_date1, ";
			$sql .= "next6_data_comment2, next6_data_date2, ";
			$sql .= "next6_data_comment3, next6_data_date3, ";
			$sql .= "next6_data_comment4, next6_data_date4, ";
			$sql .= "next6_data_comment5, next6_data_date5, ";
			$sql .= "next6_data_comment6, next6_data_date6, ";
			$sql .= "next6_data_comment7, next6_data_date7, ";
			$sql .= "next6_data_comment8, next6_data_date8, ";
			$sql .= "next6_data_comment9, next6_data_date9, ";
			$sql .= "next6_data_comment10, next6_data_date10, ";
			$sql .= "next6_cc1_data, next6_cc1_data_date, next6_cc2_data, next6_cc2_data_date, next6_cc3_data, next6_cc3_data_date, next6_cc4_data, next6_cc4_data_date, next6_cc5_data, next6_cc5_data_date,";
			$sql .= "next7_data, ";
			$sql .= "next7_data_comment1, next7_data_date1, ";
			$sql .= "next7_data_comment2, next7_data_date2, ";
			$sql .= "next7_data_comment3, next7_data_date3, ";
			$sql .= "next7_data_comment4, next7_data_date4, ";
			$sql .= "next7_data_comment5, next7_data_date5, ";
			$sql .= "next7_data_comment6, next7_data_date6, ";
			$sql .= "next7_data_comment7, next7_data_date7, ";
			$sql .= "next7_data_comment8, next7_data_date8, ";
			$sql .= "next7_data_comment9, next7_data_date9, ";
			$sql .= "next7_data_comment10, next7_data_date10, ";
			$sql .= "next7_cc1_data, next7_cc1_data_date, next7_cc2_data, next7_cc2_data_date, next7_cc3_data, next7_cc3_data_date, next7_cc4_data, next7_cc4_data_date, next7_cc5_data, next7_cc5_data_date,";
			$sql .= "next8_data, ";
			$sql .= "next8_data_comment1, next8_data_date1, ";
			$sql .= "next8_data_comment2, next8_data_date2, ";
			$sql .= "next8_data_comment3, next8_data_date3, ";
			$sql .= "next8_data_comment4, next8_data_date4, ";
			$sql .= "next8_data_comment5, next8_data_date5, ";
			$sql .= "next8_data_comment6, next8_data_date6, ";
			$sql .= "next8_data_comment7, next8_data_date7, ";
			$sql .= "next8_data_comment8, next8_data_date8, ";
			$sql .= "next8_data_comment9, next8_data_date9, ";
			$sql .= "next8_data_comment10, next8_data_date10, ";
			$sql .= "next8_cc1_data, next8_cc1_data_date, next8_cc2_data, next8_cc2_data_date, next8_cc3_data, next8_cc3_data_date, next8_cc4_data, next8_cc4_data_date, next8_cc5_data, next8_cc5_data_date,";
			$sql .= "next9_data, ";
			$sql .= "next9_data_comment1, next9_data_date1, ";
			$sql .= "next9_data_comment2, next9_data_date2, ";
			$sql .= "next9_data_comment3, next9_data_date3, ";
			$sql .= "next9_data_comment4, next9_data_date4, ";
			$sql .= "next9_data_comment5, next9_data_date5, ";
			$sql .= "next9_data_comment6, next9_data_date6, ";
			$sql .= "next9_data_comment7, next9_data_date7, ";
			$sql .= "next9_data_comment8, next9_data_date8, ";
			$sql .= "next9_data_comment9, next9_data_date9, ";
			$sql .= "next9_data_comment10, next9_data_date10, ";
			$sql .= "next9_cc1_data, next9_cc1_data_date, next9_cc2_data, next9_cc2_data_date, next9_cc3_data, next9_cc3_data_date, next9_cc4_data, next9_cc4_data_date, next9_cc5_data, next9_cc5_data_date,";
			$sql .= "next10_data, ";
			$sql .= "next10_data_comment1, next10_data_date1, ";
			$sql .= "next10_data_comment2, next10_data_date2, ";
			$sql .= "next10_data_comment3, next10_data_date3, ";
			$sql .= "next10_data_comment4, next10_data_date4, ";
			$sql .= "next10_data_comment5, next10_data_date5, ";
			$sql .= "next10_data_comment6, next10_data_date6, ";
			$sql .= "next10_data_comment7, next10_data_date7, ";
			$sql .= "next10_data_comment8, next10_data_date8, ";
			$sql .= "next10_data_comment9, next10_data_date9, ";
			$sql .= "next10_data_comment10, next10_data_date10, ";
			$sql .= "next10_cc1_data, next10_cc1_data_date, next10_cc2_data, next10_cc2_data_date, next10_cc3_data, next10_cc3_data_date, next10_cc4_data, next10_cc4_data_date, next10_cc5_data, next10_cc5_data_date,";
			$sql .= "stat, tmp_name1, file_name1, kakuchousi1, tmp_name2, file_name2, kakuchousi2, tmp_name3, file_name3, kakuchousi3, against_comment, create_date, workflow_no, parent_workflow_id, remand_f, cc1, cc2, cc3, cc4, cc5, cc1_date, cc2_date, cc3_date, cc4_date, cc5_date) ";
			$sql .= "VALUES('".$staff_code."', '".$busho_code."', '".$yyyy."', '".$mm."', '".$dd."', '".char_chg("to_db", $w_subject)."', '".$w_check."', '0', '".char_chg("to_db", $w_comment)."', '".$flow_doc."', '";
			$sql .= $flow_data[1][0]."', ";
			$sql .= "NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ";
			$sql .= "'".$flow_data[1][1]."', NULL, '".$flow_data[1][2]."', NULL, '".$flow_data[1][3]."', NULL, '".$flow_data[1][4]."', NULL, '".$flow_data[1][5]."', NULL, '";
			$sql .= $flow_data[2][0]."', ";
			$sql .= "NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ";
			$sql .= "'".$flow_data[2][1]."', NULL, '".$flow_data[2][2]."', NULL, '".$flow_data[2][3]."', NULL, '".$flow_data[2][4]."', NULL, '".$flow_data[2][5]."', NULL, '";
			$sql .= $flow_data[3][0]."', ";
			$sql .= "NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ";
			$sql .= "'".$flow_data[3][1]."', NULL, '".$flow_data[3][2]."', NULL, '".$flow_data[3][3]."', NULL, '".$flow_data[3][4]."', NULL, '".$flow_data[3][5]."', NULL, '";
			$sql .= $flow_data[4][0]."', ";
			$sql .= "NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ";
			$sql .= "'".$flow_data[4][1]."', NULL, '".$flow_data[4][2]."', NULL, '".$flow_data[4][3]."', NULL, '".$flow_data[4][4]."', NULL, '".$flow_data[4][5]."', NULL, '";
			$sql .= $flow_data[5][0]."', ";
			$sql .= "NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ";
			$sql .= "'".$flow_data[5][1]."', NULL, '".$flow_data[5][2]."', NULL, '".$flow_data[5][3]."', NULL, '".$flow_data[5][4]."', NULL, '".$flow_data[5][5]."', NULL, '";
			$sql .= $flow_data[6][0]."', ";
			$sql .= "NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ";
			$sql .= "'".$flow_data[6][1]."', NULL, '".$flow_data[6][2]."', NULL, '".$flow_data[6][3]."', NULL, '".$flow_data[6][4]."', NULL, '".$flow_data[6][5]."', NULL, '";
			$sql .= $flow_data[7][0]."', ";
			$sql .= "NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ";
			$sql .= "'".$flow_data[7][1]."', NULL, '".$flow_data[7][2]."', NULL, '".$flow_data[7][3]."', NULL, '".$flow_data[7][4]."', NULL, '".$flow_data[7][5]."', NULL, '";
			$sql .= $flow_data[8][0]."', ";
			$sql .= "NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ";
			$sql .= "'".$flow_data[8][1]."', NULL, '".$flow_data[8][2]."', NULL, '".$flow_data[8][3]."', NULL, '".$flow_data[8][4]."', NULL, '".$flow_data[8][5]."', NULL, '";
			$sql .= $flow_data[9][0]."', ";
			$sql .= "NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ";
			$sql .= "'".$flow_data[9][1]."', NULL, '".$flow_data[9][2]."', NULL, '".$flow_data[9][3]."', NULL, '".$flow_data[9][4]."', NULL, '".$flow_data[9][5]."', NULL, '";
			$sql .= $flow_data[10][0]."', ";
			$sql .= "NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ";
			$sql .= "'".$flow_data[10][1]."', NULL, '".$flow_data[10][2]."', NULL, '".$flow_data[10][3]."', NULL, '".$flow_data[10][4]."', NULL, '".$flow_data[10][5]."', NULL, ";
			$sql .= "'1',".$sql_file_1.",".$sql_file_2.",".$sql_file_3.", NULL, CURRENT_TIMESTAMP, '".$workflow_no."', ".$parent_workflow_id.", ".$remand_f.", '".$cc1."', '".$cc2."', '".$cc3."', '".$cc4."', '".$cc5."', NULL, NULL, NULL, NULL, NULL);";


			$last_id = sqlExecSeq($sql, $schema.".t_workflow_workflow_id_seq");

//再申請の場合再申請元にremand_f=1とする
			if ($remand == 2){
				$sql_doc = "UPDATE ".$schema.".t_workflow SET remand_f = 1 WHERE workflow_id = '".$workflow_id."'";
				sqlExec($sql_doc);
			}

			$where = "wdoc_no = '".$flow_doc."'";
			$target_date = target_tekiyou_where("t_workflow_format", date("Y-m-d"), $where);
                        
//			$target_date = target_tekiyou("t_workflow_format", date("Y-m-d"));
			$sql = "select * from ".$schema.".t_workflow_format where wdoc_no = '".$flow_doc."' and post_name != '' and target_date = '".$target_date."' order by num asc";
		        $cnt = 0;
		        $rows = getList($sql);
		        if($rows){
				while($row = $rows[$cnt]) {
					$data = (isset($_POST[$row["post_name"]])) ? $_POST[$row["post_name"]] : "";

					$sql_doc = "INSERT INTO ".$schema.".t_workflow_doc_new (workflow_id, post_name, data, create_date) VALUES(".$last_id.", '".$row["post_name"]."', '".$data."', CURRENT_TIMESTAMP);";
					$w_comment .= $data;
					sqlExec($sql_doc);
					$cnt += 1;
				}
			}
			$sql_doc = "UPDATE ".$schema.".t_workflow SET comment = '".$w_comment."' WHERE workflow_id = '".$last_id."'";
			sqlExec($sql_doc);

			$workflow_html .= workflow_stat(2, $last_id, $staff_code, 0, 0, 0);

//			$button_html .= "<button type=\"button\" onclick=\"location.href='apply.php'\">完了</button>\n";
			$button_html .= "<input type=\"button\" onclick=\"location.href='apply.php?home=".$home."'\" value=\"確認\" class=\"confirm\" />\n";

			$mail_address = "";
			//承認者の人にメールを送る　$flow_data[1][0]
			list($staff_cnt, $name, $apply_cnt) = workflow_root_dsp_split($flow_data[1][0]);
			for ($jj = 0; $jj < $staff_cnt; $jj++){
				if ($jj != 0){
					$mail_address .= ",";
				}
				$mail_address .= f_staff_mail_get(str_replace("*", "", $name[$jj]));
			}
			//承認依頼メールを送る
			workflow_send_mail(0, $mail_address, $login_staff_id, $workflow_no, $flow_doc);

			//起案者CCメールを送る
			$mail_address = "";
			if ($cc1 != "" || $cc2 != "" || $cc3 != "" || $cc4 != "" || $cc5 != ""){
				if ($cc1 != ""){
					if ($mail_address != ""){
						$mail_address .= ",";
					}
					$mail_address .= f_staff_mail_get(str_replace("-2", "", $cc1));
				}
				if ($cc2 != ""){
					if ($mail_address != ""){
						$mail_address .= ",";
					}
					$mail_address .= f_staff_mail_get(str_replace("-2", "", $cc2));
				}
				if ($cc3 != ""){
					if ($mail_address != ""){
						$mail_address .= ",";
					}
					$mail_address .= f_staff_mail_get(str_replace("-2", "", $cc3));
				}
				if ($cc4 != ""){
					if ($mail_address != ""){
						$mail_address .= ",";
					}
					$mail_address .= f_staff_mail_get(str_replace("-2", "", $cc4));
				}
				if ($cc5 != ""){
					if ($mail_address != ""){
						$mail_address .= ",";
					}
					$mail_address .= f_staff_mail_get(str_replace("-2", "", $cc5));
				}
				workflow_send_mail(2, $mail_address, $login_staff_id, $workflow_no, $flow_doc);
			}

		}else{	//タイトルか文書コードがNULL
			if ($route_f == 0){
				$set_f = 3;
			}else{
				$set_f = 2;
			}
		}
	}

	if (($set_f == 0 || $set_f == 2 || $set_f == 3) && $result_id == 0){
		if ($set_f == 2){
			$workflow_html .= "<font color=red>申請文書とタイトルは必須です。</font><br />\n";
		}else if ($set_f == 3){
			$workflow_html .= "<font color=red>申請ルートが設定されていません。</font><br />\n";
		}
//		$form1_html .= "<form id=\"apply\" method=\"post\" enctype=\"multipart/form-data\" accept-charset=\"ASCII\" action=\"apply.php?home=".$home."&cd=1\">\n";
if ($remand == 0){
		$form1_html .= "<form id=\"apply\" method=\"post\" enctype=\"multipart/form-data\" action=\"apply.php?home=".$home."&cd=1&remand_no=".$remand_no."&tenp_del_1=".$tenp_del_1."&tenp_del_2=".$tenp_del_2."&tenp_del_3=".$tenp_del_3."\">\n";
}else{
		$form1_html .= "<form id=\"apply\" method=\"post\" enctype=\"multipart/form-data\" action=\"apply.php?home=".$home."&cd=1&remand=2&remand_no=".$remand_no."&workflow_id=".$workflow_id."&tenp_del_1=".$tenp_del_1."&tenp_del_2=".$tenp_del_2."&tenp_del_3=".$tenp_del_3."\">\n";
}

		$workflow_html .= "<div class=\"inner\">\n";
		$workflow_html .= "	<p class=\"lead\">".$login_staff."</p>\n";
		$workflow_html .= "		<div class=\"section\">\n";
		$workflow_html .= "			<h2>申請</h2>\n";
		$workflow_html .= "			<ul class=\"board\">\n";
		$workflow_html .= "			<li>申請書：".f_code_flow_doc_select($r_flow_doc_code)."</li>\n";
		$workflow_html .= "			<li>タイトル："."<input type=\"text\" name=\"w_subject\" style='width:80%;' value=\"".$r_title."\"></li>\n";
		$workflow_html .= "			<li>可決後に確認する："."<input type=\"checkbox\" name=\"w_check\" value=\"1\"></li>\n";

		$workflow_html .= "			</ul>\n";
		$workflow_html .= "		</div>\n";

		$workflow_html .= "		<div class=\"section\">\n";
		$workflow_html .= "			<h2>内容</h2>\n";
		$workflow_html .= "			<ul class=\"voice\">\n";

		$sql = "SELECT no FROM ".$schema.".m_code WHERE cd = 99 order by no";
		$cnt = 0;
		$cnt_no = 1;
		$rows = getList($sql);
		if($rows){
			while($row = $rows[$cnt]) {
				if ($row["no"] != 0){
					$workflow_html .= "<li id=\"flow_doc-".(100+$cnt_no)."\" style=\"display:none\">\n";
					$script_html_tmp = "";
					list($html, $script_html_tmp) = workflow_format_write_mobile($row["no"], $workflow_id);
					$script_html .= $script_html_tmp;
					$workflow_html .= $html;
					$workflow_html .= "</li>\n";
					$cnt_no += 1;
				}
				$cnt += 1;
			}
		}

		$workflow_html .= "			</ul>\n";
		$workflow_html .= "		</div>\n";

		$workflow_html .= "		<div class=\"section\">\n";
		$workflow_html .= "			<h2>添付ファイル</h2>\n";
		$content .= "			<ul class=\"voice\">\n";
		$tempfile_f = 0;
		if ($tempfile[1][1] != ""){
			$content .= "			<li><a href=\"../../workflow/file/".$schema."/".$tempfile[1][1]."\" download=\"".$tempfile[1][2].$tempfile[1][3]."\">".$tempfile[1][2].$tempfile[1][3]."</a></li>\n";
			$tempfile_f = 1;
		}
		if ($tempfile[2][1] != ""){
			$content .= "			<li><a href=\"../../workflow/file/".$schema."/".$tempfile[2][1]."\" download=\"".$tempfile[2][2].$tempfile[2][3]."\">".$tempfile[2][2].$tempfile[2][3]."</a></li>\n";
			$tempfile_f = 1;
		}
		if ($tempfile[3][1] != ""){
			$content .= "			<li><a href=\"../../workflow/file/".$schema."/".$tempfile[3][1]."\" download=\"".$tempfile[3][2].$tempfile[3][3]."\">".$tempfile[3][2].$tempfile[3][3]."</a></li>\n";
			$tempfile_f = 1;
		}
		if ($tempfile_f == 0){
			$content .= "			<li>添付ファイルはありません</li>\n";
		}

		$content .= "			</ul>\n";
		$content .= "		</div>\n";




		if ($workflow_id != 0 && $r_file_name1 != ""){	//再申請
			$workflow_html .= "<tr>\n";
			$workflow_html .= "<th align=center rowspan=3 width=100><h2>添付ファイル</h2></th>\n";
			$workflow_html .= "<th align=center width=100><span style=\"font-weight:bold;color:#551c20;background-color:#dcbfa2\">&nbsp;&nbsp;1&nbsp;&nbsp;</span></th>\n";
			$workflow_html .= "<td align=left width=700>".$r_file_name1.$r_kakuchousi1."&nbsp;&nbsp;<button type=\"button\" onclick=\"location.href='apply.php?workflow_id=".$workflow_id."&tenp_del=1&remand=".$remand."&tenp_del_1=".$tenp_del_1."&tenp_del_2=".$tenp_del_2."&tenp_del_3=".$tenp_del_3."'\">削除</button></td>\n";
			$workflow_html .= "</tr>\n";
		}else{
			$workflow_html .= "<tr>\n";
			$workflow_html .= "<th align=center width=100><span style=\"font-weight:bold;color:#551c20;background-color:#dcbfa2\">&nbsp;&nbsp;1&nbsp;&nbsp;</span></th>\n";
			$workflow_html .= "<td align=left width=700><input id=\"userfile1\" name=\"userfile1\" type=\"file\" /></td>\n";
			$workflow_html .= "</tr>\n";
		}
		if ($workflow_id != 0 && $r_file_name2 != ""){	//再申請
			$workflow_html .= "<br>\n";
			$workflow_html .= "<tr>\n";
			$workflow_html .= "<th align=center><span style=\"font-weight:bold;color:#551c20;background-color:#dcbfa2\">&nbsp;&nbsp;2&nbsp;&nbsp;</span></th>\n";
			$workflow_html .= "<td align=left>".$r_file_name2.$r_kakuchousi2."&nbsp;&nbsp;<button type=\"button\" onclick=\"location.href='apply.php?workflow_id=".$workflow_id."&tenp_del=2&remand=".$remand."&tenp_del_1=".$tenp_del_1."&tenp_del_2=".$tenp_del_2."&tenp_del_3=".$tenp_del_3."'\">削除</button></td>\n";
			$workflow_html .= "</tr>\n";
		}else{
			$workflow_html .= "<br>\n";
			$workflow_html .= "<tr>\n";
			$workflow_html .= "<th align=center><span style=\"font-weight:bold;color:#551c20;background-color:#dcbfa2\">&nbsp;&nbsp;2&nbsp;&nbsp;</span></th>\n";
			$workflow_html .= "<td align=left><input id=\"userfile2\" name=\"userfile2\" type=\"file\" /></td>\n";
			$workflow_html .= "</tr>\n";
		}
		if ($workflow_id != 0 && $r_file_name3 != ""){	//再申請
			$workflow_html .= "<br>\n";
			$workflow_html .= "<tr>\n";
			$workflow_html .= "<th align=center><span style=\"font-weight:bold;color:#551c20;background-color:#dcbfa2\">&nbsp;&nbsp;3&nbsp;&nbsp;</span></th>\n";
			$workflow_html .= "<td align=left>".$r_file_name3.$r_kakuchousi3."&nbsp;&nbsp;<button type=\"button\" onclick=\"location.href='apply.php?workflow_id=".$workflow_id."&tenp_del=3&remand=".$remand."&tenp_del_1=".$tenp_del_1."&tenp_del_2=".$tenp_del_2."&tenp_del_3=".$tenp_del_3."'\">削除</button></td>\n";
			$workflow_html .= "</tr>\n";
		}else{
			$workflow_html .= "<br>\n";
			$workflow_html .= "<tr>\n";
			$workflow_html .= "<th align=center><span style=\"font-weight:bold;color:#551c20;background-color:#dcbfa2\">&nbsp;&nbsp;3&nbsp;&nbsp;</span></th>\n";
			$workflow_html .= "<td align=left><input id=\"userfile3\" name=\"userfile3\" type=\"file\" /></td>\n";
			$workflow_html .= "</tr>\n";
		}
//フローを表示
		$workflow_html .= "		<div class=\"section\">\n";
		$workflow_html .= "			<h2>ルート</h2>\n";
		$workflow_html .= "			<ul class=\"voice\">\n";

		$script_h = "";
		$sql = "SELECT no FROM ".$schema.".m_code WHERE cd = 99 order by no";
		$cnt = 0;
		$cnt_no = 1;
		$rows = getList($sql);
		$script_no = count($rows)-1;
		if($rows){
			while($row = $rows[$cnt]) {
				if ($row["no"] != 0){
					$workflow_html .= "<li id=\"flow_doc-".(200+$cnt_no)."\" style=\"display:none\">\n";
					$workflow_html .= workflow_route_mobile($kyoten, $busho_code, $row["no"], $rank_code)."\n";
					$workflow_html .= "</li>\n";

					if ($row["no"] == 1){
						$script_h .= "if(selindex == ".$row["no"].") {\n";
					}else{
						$script_h .= "}else if(selindex == ".$row["no"].") {\n";
					}

					for ($i = 1; $i <= $script_no; $i++){
						if ($i == $row["no"]){
							$script_h .= "document.getElementById('flow_doc-".(100+$i)."').style.display = \"\"\n";
							$script_h .= "document.getElementById('flow_doc-".(200+$i)."').style.display = \"\"\n";
						}else{
							$script_h .= "document.getElementById('flow_doc-".(100+$i)."').style.display = \"none\"\n";
							$script_h .= "document.getElementById('flow_doc-".(200+$i)."').style.display = \"none\"\n";
						}

					}
					$cnt_no += 1;
				}
				$cnt += 1;
			}
			$script_h .= "}\n";
		}
		$workflow_html .= "			</ul>\n";
		$workflow_html .= "		</div>\n";
		$workflow_html .= "</div>\n";

		$workflow_html .= "<input type=\"hidden\" id=\"userfile_name1\" name=\"userfile_name1\" value=\"\" />\n";
		$workflow_html .= "<input type=\"hidden\" id=\"userfile_name2\" name=\"userfile_name2\" value=\"\" />\n";
		$workflow_html .= "<input type=\"hidden\" id=\"userfile_name3\" name=\"userfile_name3\" value=\"\" />\n";

		$button_html .= "<input id='btn' name='btn' type=\"button\" onclick=\"check(".$remand.",".$tenp_del_1_f.",".$tenp_del_2_f.",".$tenp_del_3_f.")\" value=\"申請\" class=\"request\" />\n";
		$form2_html .= "</form>\n";
	}

//各画面のPHP



?>
<html>
<!-- ヘッダ -->
<?php include("../common/include/include_header.php"); ?>

<!-- スタイルシート -->
<?php include("../common/include/include_stylesheets.php"); ?>

<!-- アイコン -->
<?php include("../common/include/include_icons.php"); ?>

<!-- 各画面のスクリプト -->
<script type="text/javascript" src="../../workflow/js/sales/jquery/jquery.min.js"></script>
<script type="text/javascript" src="../../workflow/js/sales/jquery/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../workflow/js/sales/jquery/jquery.ui.ympicker.js"></script>
<link type="text/css" href="../../workflow/css/sales/sunny/jquery-ui-1.9.2.custom.css" rel="stylesheet" />
<script src="../../workflow/js/sales/jquery/jquery.maskedinput.js" type="text/javascript"></script>
<script src="../../workflow/js/bookmark.js" type="text/javascript"></script>
<script type="text/javascript">
function check(remand, t_1, t_2, t_3){

	document.getElementById("btn").disabled  = true;
        
	if (remand == 1 && t_1 == 1){
	}else{
		var fileList1 = document.getElementById("userfile1").files;
		var list1 = "";
		for(var i=0; i<fileList1.length; i++){
			list1 += fileList1[i].name;
		}
		document.getElementById("userfile_name1").value = list1;
	}

	if (remand == 1 && t_2 == 1){
	}else{
		var fileList2 = document.getElementById("userfile2").files;
		var list2 = "";
		for(var i=0; i<fileList2.length; i++){
			list2 += fileList2[i].name;
		}
		document.getElementById("userfile_name2").value = list2;
	}

	if (remand == 1 && t_3 == 1){
	}else{
		var fileList3 = document.getElementById("userfile3").files;
		var list3 = "";
		for(var i=0; i<fileList3.length; i++){
			list3 += fileList3[i].name;
		}
		document.getElementById("userfile_name3").value = list3;
	}

	var target = document.getElementById("apply");
	target.submit();

}

</script>
<script type="text/javascript">
function flow_sel(){
var flow_dat = document.getElementById("apply");

selindex = flow_dat.flow_doc.options[flow_dat.flow_doc.selectedIndex].value;

<?= $script_h ?>

}

// アキナイ向け合計計算用スクリプト対応(一時的)
function goukeiCal1(obj){
    
    if(isNaN(obj.value)){
        alert("金額に半角数値以外が含まれています");
        setTimeout(function(){ obj.focus(); obj.select();}, 1);
        window.onblur = '';
    }else{
        document.getElementsByName("goukei2101")[0].value =
                Number(document.getElementsByName("kingaku2101")[0].value) + Number(document.getElementsByName("kingaku2102")[0].value) + Number(document.getElementsByName("kingaku2103")[0].value) + Number(document.getElementsByName("kingaku2104")[0].value)
                + Number(document.getElementsByName("kingaku2105")[0].value) + Number(document.getElementsByName("kingaku2106")[0].value) + Number(document.getElementsByName("kingaku2107")[0].value) + Number(document.getElementsByName("kingaku2108")[0].value)
                + Number(document.getElementsByName("kingaku2109")[0].value) + Number(document.getElementsByName("kingaku2110")[0].value) + Number(document.getElementsByName("kingaku2111")[0].value) + Number(document.getElementsByName("kingaku2112")[0].value)
                + Number(document.getElementsByName("kingaku2113")[0].value) + Number(document.getElementsByName("kingaku2114")[0].value) + Number(document.getElementsByName("kingaku2115")[0].value) + Number(document.getElementsByName("kingaku2116")[0].value)
                + Number(document.getElementsByName("kingaku2117")[0].value) + Number(document.getElementsByName("kingaku2118")[0].value) + Number(document.getElementsByName("kingaku2119")[0].value) + Number(document.getElementsByName("kingaku2120")[0].value)
                + Number(document.getElementsByName("kingaku2121")[0].value);
    }
}

function goukeiCal2(obj){
    
    if(isNaN(obj.value)){
        alert("金額に半角数値以外が含まれています");
        setTimeout(function(){ obj.focus(); obj.select();}, 1);
        window.onblur = '';
    }else{
        document.getElementsByName("18_h_goukei")[0].value =
                Number(document.getElementsByName("18_h_price_1")[0].value) + Number(document.getElementsByName("18_h_price_2")[0].value) + Number(document.getElementsByName("18_h_price_3")[0].value) + Number(document.getElementsByName("18_h_price_4")[0].value)
                + Number(document.getElementsByName("18_h_price_5")[0].value) + Number(document.getElementsByName("18_h_price_6")[0].value) + Number(document.getElementsByName("18_h_price_7")[0].value) + Number(document.getElementsByName("18_h_price_8")[0].value)
                + Number(document.getElementsByName("18_h_price_9")[0].value) + Number(document.getElementsByName("18_h_price_10")[0].value) + Number(document.getElementsByName("18_h_price_11")[0].value) + Number(document.getElementsByName("18_h_price_12")[0].value)
                + Number(document.getElementsByName("18_h_price_13")[0].value) + Number(document.getElementsByName("18_h_price_14")[0].value) + Number(document.getElementsByName("18_h_price_15")[0].value) + Number(document.getElementsByName("18_h_price_16")[0].value)
                + Number(document.getElementsByName("18_h_price_17")[0].value) + Number(document.getElementsByName("18_h_price_18")[0].value) + Number(document.getElementsByName("18_h_price_19")[0].value) + Number(document.getElementsByName("18_h_price_20")[0].value)
                + Number(document.getElementsByName("18_h_price_21")[0].value) + Number(document.getElementsByName("18_h_price_22")[0].value) + Number(document.getElementsByName("18_h_price_23")[0].value);
    }
}

function goukeiCal3(obj){
    
    if(isNaN(obj.value)){
        alert("金額に半角数値以外が含まれています");
        setTimeout(function(){ obj.focus(); obj.select();}, 1);
        window.onblur = '';
    }else{
        
        // 小計1
        document.getElementsByName("shoukei2501")[0].value =
                Number(document.getElementsByName("nisuu2501")[0].value) * Number(document.getElementsByName("tanka2501")[0].value);
        // 小計2
        document.getElementsByName("shoukei2502")[0].value =
                Number(document.getElementsByName("nisuu2502")[0].value) * Number(document.getElementsByName("tanka2502")[0].value);
        // 小計3
        document.getElementsByName("shoukei2503")[0].value =
                Number(document.getElementsByName("nisuu2503")[0].value) * Number(document.getElementsByName("tanka2503")[0].value);
        // 小計4
        document.getElementsByName("shoukei2504")[0].value =
                Number(document.getElementsByName("nisuu2504")[0].value) * Number(document.getElementsByName("tanka2504")[0].value);
        // 小計5
        document.getElementsByName("shoukei2505")[0].value =
                Number(document.getElementsByName("nisuu2505")[0].value) * Number(document.getElementsByName("tanka2505")[0].value);
        // 合計日数
        document.getElementsByName("gounisuu2501")[0].value =
                Number(document.getElementsByName("nisuu2501")[0].value) + Number(document.getElementsByName("nisuu2502")[0].value) + Number(document.getElementsByName("nisuu2503")[0].value) + Number(document.getElementsByName("nisuu2504")[0].value)
                + Number(document.getElementsByName("nisuu2505")[0].value);
        // 合計単価
        document.getElementsByName("goutanka2501")[0].value =
                Number(document.getElementsByName("tanka2501")[0].value) + Number(document.getElementsByName("tanka2502")[0].value) + Number(document.getElementsByName("tanka2503")[0].value) + Number(document.getElementsByName("tanka2504")[0].value)
                + Number(document.getElementsByName("tanka2505")[0].value);
        // 合計出張計
        document.getElementsByName("goushoukei2501")[0].value =
                Number(document.getElementsByName("shoukei2501")[0].value) + Number(document.getElementsByName("shoukei2502")[0].value) + Number(document.getElementsByName("shoukei2503")[0].value) + Number(document.getElementsByName("shoukei2504")[0].value)
                + Number(document.getElementsByName("shoukei2505")[0].value);
    }
}

</script>

<script type="text/javascript">
$(function(){
	$("#w_start").datepicker({dateFormat:'yy-mm-dd'});
	$("#w_end").datepicker({dateFormat:'yy-mm-dd'});
	$("#w_start").mask("9999-99-99");
	$("#w_end").mask("9999-99-99");
});
</script>
<?= $script_html ?>
</head>

<body id="winTop" class="infTop">
<?php h_header(); ?>
<?php h_menu(); ?>
<div class="overlay"></div>
<div id="container">
<?= $form1_html ?>
<?= $workflow_html ?>
<?= $button_html ?>
<?= $form2_html ?>
</div>
<?php

print "<script language=javascript>flow_sel()</script>";

?>
<?php h_footer(); ?>
</body>
</html>
