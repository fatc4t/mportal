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


$home = (isset($_GET['home'])) ? $_GET['home'] : 0;

$login_id = $login_staff_id;	//f_staff_id_get($login_staff);

$apply_no = (isset($_POST['apply_no'])) ? $_POST['apply_no'] : "";
$start_d = (isset($_POST['start_d'])) ? $_POST['start_d'] : "";
$end_d = (isset($_POST['end_d'])) ? $_POST['end_d'] : "";
$k_stat = (isset($_POST['k_stat'])) ? $_POST['k_stat'] : 0;
$apply_org = (isset($_POST['apply_org'])) ? $_POST['apply_org'] : 0;
$flow_doc = (isset($_POST['flow_doc'])) ? $_POST['flow_doc'] : 0;
$apply_title = (isset($_POST['apply_title'])) ? $_POST['apply_title'] : "";
$apply_body = (isset($_POST['apply_body'])) ? $_POST['apply_body'] : "";

if ($k_stat == 0){
	$k_stat = (isset($_GET['k_stat'])) ? $_GET['k_stat'] : 0;
}
if ($apply_no == ""){
	$apply_no = (isset($_GET['apply_no'])) ? $_GET['apply_no'] : "";
}
if ($apply_org == 0){
	$apply_org = (isset($_GET['apply_org'])) ? $_GET['apply_org'] : "";
}
if ($flow_doc == 0){
	$flow_doc = (isset($_GET['flow_doc'])) ? $_GET['flow_doc'] : 0;
}
if ($apply_title == ""){
	$apply_title = (isset($_GET['apply_title'])) ? $_GET['apply_title'] : "";
}
if ($apply_body == ""){
	$apply_body = (isset($_GET['apply_body'])) ? $_GET['apply_body'] : "";
}
if ($start_d == ""){
	$start_d = (isset($_GET['start_d'])) ? $_GET['start_d'] : "";
}
if ($end_d == ""){
	$end_d = (isset($_GET['end_d'])) ? $_GET['end_d'] : "";
}




	if ( isset($_GET['cd']) ) {
		$cd = $_GET['cd'];
	}else{
		$cd = 0;
	}

	if ( isset($_GET['workflow_id']) ) {
		$workflow_id = $_GET['workflow_id'];
	}else{
		$workflow_id = 0;
	}

	if ($workflow_id == 0){
		if ( isset($_POST['workflow_id']) ) {
			$workflow_id = $_POST['workflow_id'];
		}
	}

	if ( isset($_GET['mode']) ) {
		$mode = $_GET['mode'];
	}else{
		if ( isset($_POST['mode']) ) {
			$mode = $_POST['mode'];
		}else{
			$mode = 0;
		}
	}
	if ( isset($_GET['page']) ) {
		$page = $_GET['page'];
	}else{
		$page = 1;
	}

	$staff_code = $login_staff_id;	//f_staff_id_get($login_staff);
	$workflow_html = "";

	if ($mode == 1){	//取り戻された
		$sql_up = "UPDATE ".$schema.".t_workflow SET stat = 4 WHERE workflow_id = ".$workflow_id;
		sqlExec($sql_up);
	}

	$count = 0;
	$workflow_html .= "<table align=center class=\"list_table\" width=100%>\n";

//	$page_max = f_staff_page_max_get($login_staff);
	$page_max = 10;
	$offset = (($page-1) * $page_max);
	if ($offset < 0){
		$offset = 0;
	}


if ($cd == 9){
	$page = 1;
	$offset = 0;
}

$sec_id = f_staff_super_get($login_staff_id);
	if ($sec_id == 1 || $sec_id == 2){
		$fg = 0;
		$sql = "SELECT * FROM ".$schema.".t_workflow ";
		if ($k_stat != 0){
			$sql .= "WHERE stat = ".$k_stat." ";
			$fg = 1;
		}
		if ($apply_org != 0){
			if ($fg == 0){
				$sql .= "WHERE busho_code = ".detail_org_id_get($apply_org)." ";
				$fg = 1;
			}else{
				$sql .= "and busho_code = ".detail_org_id_get($apply_org)." ";
			}
		}
		if ($apply_no != ""){
			if ($fg == 0){
				$sql .= "WHERE workflow_no = '".$apply_no."' ";
				$fg = 1;
			}else{
				$sql .= "and workflow_no = '".$apply_no."' ";
			}
		}
		if ($flow_doc != 0){
			if ($fg == 0){
				$sql .= "WHERE flow_doc_code = ".$flow_doc." ";
				$fg = 1;
			}else{
				$sql .= "and flow_doc_code = ".$flow_doc." ";
			}
		}
		if ($apply_title != ""){
			if ($fg == 0){
				$sql .= "WHERE title like '%".$apply_title."%' ";
				$fg = 1;
			}else{
				$sql .= "and title like '%".$apply_title."%' ";
			}
		}
		if ($apply_body != ""){
			if ($fg == 0){
				$sql .= "WHERE comment like '%".$apply_body."%' ";
				$fg = 1;
			}else{
				$sql .= "and comment like '%".$apply_body."%' ";
			}
		}
		if ($start_d != "" && $end_d != ""){
			if ($fg == 0){
				$sql .= "WHERE (create_date >= '".$start_d." 00:00:00' and create_date <= '".$end_d." 23:59:59') ";
				$fg = 1;
			}else{
				$sql .= "and (create_date >= '".$start_d." 00:00:00' and create_date <= '".$end_d." 23:59:59') ";
			}
		}else if ($start_d != ""){
			if ($fg == 0){
				$sql .= "WHERE create_date >= '".$start_d." 00:00:00' ";
				$fg = 1;
			}else{
				$sql .= "and create_date >= '".$start_d." 00:00:00' ";
			}
		}else if ($end_d != ""){
			if ($fg == 0){
				$sql .= "WHERE create_date <= '".$end_d." 23:59:59' ";
				$fg = 1;
			}else{
				$sql .= "and create_date <= '".$end_d." 23:59:59' ";
			}
		}

		$sql .= "ORDER BY workflow_id DESC";
		$sql_2 = " limit ".$page_max." offset ".$offset;

	}else{

		$sql = "SELECT * FROM ".$schema.".t_workflow WHERE (user_id = ".$staff_code." or ";
		$sql .= "next1_data LIKE '*".$staff_code."-%' or next2_data LIKE '*".$staff_code."-%' or next3_data LIKE '*".$staff_code."-%' or next4_data LIKE '*".$staff_code."-%' or next5_data LIKE '*".$staff_code."-%' or next6_data LIKE '*".$staff_code."-%' or next7_data LIKE '*".$staff_code."-%' or next8_data LIKE '*".$staff_code."-%' or next9_data LIKE '*".$staff_code."-%' or next10_data LIKE '*".$staff_code."-%' or ";
		$sql .= "cc1 LIKE '".$staff_code."-%' or cc2 LIKE '".$staff_code."-%' or cc3 LIKE '".$staff_code."-%' or cc4 LIKE '".$staff_code."-%' or cc5 LIKE '".$staff_code."-%' or ";
		$sql .= "next1_cc1_data LIKE '*".$staff_code."-%' or next1_cc2_data LIKE '*".$staff_code."-%' or next1_cc3_data LIKE '*".$staff_code."-%' or next1_cc4_data LIKE '*".$staff_code."-%' or next1_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql .= "next2_cc1_data LIKE '*".$staff_code."-%' or next2_cc2_data LIKE '*".$staff_code."-%' or next2_cc3_data LIKE '*".$staff_code."-%' or next2_cc4_data LIKE '*".$staff_code."-%' or next2_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql .= "next3_cc1_data LIKE '*".$staff_code."-%' or next3_cc2_data LIKE '*".$staff_code."-%' or next3_cc3_data LIKE '*".$staff_code."-%' or next3_cc4_data LIKE '*".$staff_code."-%' or next3_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql .= "next4_cc1_data LIKE '*".$staff_code."-%' or next4_cc2_data LIKE '*".$staff_code."-%' or next4_cc3_data LIKE '*".$staff_code."-%' or next4_cc4_data LIKE '*".$staff_code."-%' or next4_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql .= "next5_cc1_data LIKE '*".$staff_code."-%' or next5_cc2_data LIKE '*".$staff_code."-%' or next5_cc3_data LIKE '*".$staff_code."-%' or next5_cc4_data LIKE '*".$staff_code."-%' or next5_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql .= "next6_cc1_data LIKE '*".$staff_code."-%' or next6_cc2_data LIKE '*".$staff_code."-%' or next6_cc3_data LIKE '*".$staff_code."-%' or next6_cc4_data LIKE '*".$staff_code."-%' or next6_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql .= "next7_cc1_data LIKE '*".$staff_code."-%' or next7_cc2_data LIKE '*".$staff_code."-%' or next7_cc3_data LIKE '*".$staff_code."-%' or next7_cc4_data LIKE '*".$staff_code."-%' or next7_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql .= "next8_cc1_data LIKE '*".$staff_code."-%' or next8_cc2_data LIKE '*".$staff_code."-%' or next8_cc3_data LIKE '*".$staff_code."-%' or next8_cc4_data LIKE '*".$staff_code."-%' or next8_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql .= "next9_cc1_data LIKE '*".$staff_code."-%' or next9_cc2_data LIKE '*".$staff_code."-%' or next9_cc3_data LIKE '*".$staff_code."-%' or next9_cc4_data LIKE '*".$staff_code."-%' or next9_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql .= "next10_cc1_data LIKE '*".$staff_code."-%' or next10_cc2_data LIKE '*".$staff_code."-%' or next10_cc3_data LIKE '*".$staff_code."-%' or next10_cc4_data LIKE '*".$staff_code."-%' or next10_cc5_data LIKE '*".$staff_code."-%') ";

			if ($k_stat != 0){
				$sql .= "and stat = ".$k_stat." ";
			}
			if ($apply_org != 0){
				$sql .= "and busho_code = ".$apply_org." ";
			}
			if ($apply_no != ""){
				$sql .= "and workflow_no = '".$apply_no."' ";
			}
			if ($flow_doc != 0){
				$sql .= "and flow_doc_code = ".$flow_doc." ";
			}
			if ($apply_title != ""){
				$sql .= "and title like '%".$apply_title."%' ";
			}
			if ($apply_body != ""){
				$sql .= "and comment like '%".$apply_body."%' ";
			}
		if ($start_d != "" && $end_d != ""){
			if ($fg == 0){
				$sql .= "WHERE (create_date >= '".$start_d." 00:00:00' and create_date <= '".$end_d." 23:59:59') ";
				$fg = 1;
			}else{
				$sql .= "and (create_date >= '".$start_d." 00:00:00' and create_date <= '".$end_d." 23:59:59') ";
			}
		}else if ($start_d != ""){
			if ($fg == 0){
				$sql .= "WHERE create_date >= '".$start_d." 00:00:00' ";
				$fg = 1;
			}else{
				$sql .= "and create_date >= '".$start_d." 00:00:00' ";
			}
		}else if ($end_d != ""){
			if ($fg == 0){
				$sql .= "WHERE create_date <= '".$end_d." 23:59:59' ";
				$fg = 1;
			}else{
				$sql .= "and create_date <= '".$end_d." 23:59:59' ";
			}
		}

		$sql .= "ORDER BY workflow_id DESC";
		$sql_2 = " limit ".$page_max." offset ".$offset;

	}

$file_path = "";

if ($cd == 10 && $flow_doc != 0 && ($start_d != "" || $end_d != "")){	//CSV出力
	$csv_data_cnt = 0;

	$cnt = 0;
        $f_rows = getList($sql);
	if($f_rows){
		while($row = $f_rows[$cnt]) {
			$create_date = $row["create_date"];
			$create_date = date('Y-m-d', strtotime($create_date));
			$where = "wdoc_no = '".$flow_doc."'";
			$target_date = target_tekiyou_where("t_workflow_format", $create_date, $where);
			if ($target_date == ""){
				$rows_format = 0;
			}else{
				$sql_format = "select * from ".$_SESSION["SCHEMA"].".t_workflow_format where ".$where." and target_date = '".$target_date."' order by num asc";

				$post_name_save = "";
				$cnt_format = 0;
			        $rows_format = getList($sql_format);
		        }
		        if($rows_format){


				$csv_data[$csv_data_cnt] .= "\"".$row["workflow_no"]."\",";
				$csv_data[$csv_data_cnt] .= f_code_dsp(99, $row["flow_doc_code"]).",";

				if ($row["stat"] == 1){	//申請中
				      	$csv_data[$csv_data_cnt] .= "申請中,";
				}else if ($row["stat"] == 2){	//否決
				      	$csv_data[$csv_data_cnt] .= "否決,";
				}else if ($row["stat"] == 3){	//可決
				      	$csv_data[$csv_data_cnt] .= "可決,";
				}else if ($row["stat"] == 4){	//取戻
				      	$csv_data[$csv_data_cnt] .= "取り戻し,";
				}else if ($row["stat"] == 5){	//差し戻し
					if ($row["remand_f"] == 1){
					      	$csv_data[$csv_data_cnt] .= "再申請済み,";
					}else{
					      	$csv_data[$csv_data_cnt] .= "差し戻し,";
					}
				}else{	//エラー
				      	$csv_data[$csv_data_cnt] .= ",";
				}


				$csv_data[$csv_data_cnt] .= "\"".date('Y-m-d', strtotime($row["create_date"]))."\",";
				$csv_data[$csv_data_cnt] .= f_staff_name_get($row["user_id"]).",";

				while($row_format = $rows_format[$cnt_format]) {

					list($data1, $data2, $data3, $data4, $data5, $data6, $data7, $data8, $data9, $data10, $data11, $data12, $data13, $data14, $data15, $data16) = explode("-", $row_format["data"]);

					switch($data1)
					{
						case 1:	//th
							$tmp_str = str_replace("<br />", "", $data2);
							$tmp_str = str_replace("&nbsp;", "", $tmp_str);
							if ($tmp_str != ""){
								$csv_data[$csv_data_cnt] .= "[項目]".$tmp_str.",";
							}
							break;
						case 2:	//tr
							break;
						case 3:	//td
							break;
						case 10:	//input
							switch($data12)
							{
								case 2:	//type = text
									if ($data16 != "none"){
										$csv_data[$csv_data_cnt] .= f_post_name_data_get($row["workflow_id"], $data10).",".$data16.",";
									}else{
										$csv_data[$csv_data_cnt] .= f_post_name_data_get($row["workflow_id"], $data10).",".",";
									}
									break;
								case 4:	//type = dsp 表示のみ
				//					if ($data16 != "none"){
				//						$csv_data[$csv_data_cnt] .= "[項目]".$data2.",".$data16.",";
				//					}else{
				//						$csv_data[$csv_data_cnt] .= "[項目]".$data2.",".",";
				//					}
									$tmp_str = str_replace("<br />", "", $data2);
									$tmp_str = str_replace("&nbsp;", "", $tmp_str);
									if ($tmp_str != ""){
										$csv_data[$csv_data_cnt] .= "[項目]".$tmp_str.",";
									}
									break;
								case 1:	//type = radio
									if ($post_name_save != $data10){
										$csv_data[$csv_data_cnt] .= f_post_name_data_get($row["workflow_id"], $data10).",";
									}
									$post_name_save = $data10;
									break;
								case 3:	//type = checkbox
									if ($post_name_save != $data10){
										$csv_data[$csv_data_cnt] .= f_post_name_data_get($row["workflow_id"], $data10).",";
									}
									$post_name_save = $data10;
									break;
							}
							break;
						case 20:	//textarea
							$tmp_str = str_replace("<br />", "", f_post_name_data_get($row["workflow_id"], $data10));
							if ($tmp_str != ""){
								$csv_data[$csv_data_cnt] .= "\"".$tmp_str."\",";
							}
//							$csv_data[$csv_data_cnt] .= "\"".f_post_name_data_get($row["workflow_id"], $data10)."\",";
							break;
						case 30:	//date
							$tmp_str = f_post_name_data_get($row["workflow_id"], $data10);
							switch($data12)
							{
								case 1:	//年
									$tmp_str = date('Y', strtotime($tmp_str));
									break;
								case 2:	//年月
									$tmp_str = date('Y-m', strtotime($tmp_str));
									break;
								case 3:	//年月日
									$tmp_str = date('Y-m-d', strtotime($tmp_str));
									break;
							}
							$csv_data[$csv_data_cnt] .= "'".$tmp_str.",";
//							$csv_data[$csv_data_cnt] .= "\"".f_post_name_data_get($row["workflow_id"], $data10)."\",";
							break;
						case 40:	//time
							$csv_data[$csv_data_cnt] .= f_post_name_data_get($row["workflow_id"], $data10).",";
							break;
						case 60:	//select
							$csv_data[$csv_data_cnt] .= f_code_dsp($data13, f_post_name_data_get($row["workflow_id"], $data10)).",";
							break;
						case 4:	// /td
							break;
						case 5:	// /tr
							break;
					}					

					$cnt_format ++;
				}
				$csv_data_cnt ++;
			}
			$cnt ++;
		}

//CSV出力


/*
		$fp = fopen('php://output', 'w');
		if ($fp !== false) {
			ob_start();
			fwrite($fp, $csv_data[0]."\n");
			for($i=0; $i < $csv_data_cnt; $i++) {
				fwrite($fp, $csv_data[$i]."\n");
			}
			$str = ob_get_contents();
			fclose($fp);
			ob_end_clean();
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=apply-".date("Y-m-d").".csv");
			header("Content-Length: ".strlen($str));
			echo $str;
		}
*/

		$file_path = "./Temp/".date(Ymd).$login_staff_id.".csv";

		$fp = fopen($file_path, 'w');
		for($i=0; $i < $csv_data_cnt; $i++) {
			fwrite($fp, mb_convert_encoding($csv_data[$i], 'SJIS-win', 'UTF-8')."\n");
		}
		fclose($fp);


	}


}

$count = $offset;

		$sql .= $sql_2;

		$cnt = 0;
	        $f_rows = getList($sql);

		if($f_rows){
			$workflow_html .= "<tr>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=50>NO</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=150>申請番号</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=200>申請日付</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=250>申請文書</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=150>申請者</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\">タイトル</th>\n";
//			$workflow_html .= "<th align=center class=\"title_td\" width=400>内容</th>\n";
//			$workflow_html .= "<th align=center class=\"title_td\" width=80>結果報告</th>\n";
			$workflow_html .= "<th align=center class=\"title_td\" width=80>状況</th>\n";
			$workflow_html .= "</tr>\n";
			while($row = $f_rows[$cnt]) {
				$count ++;
				$workflow_html .= "<tr>\n";
			      	$workflow_html .= "<td align=center class=\"item_td\">".$count."</td>\n";

			      	$workflow_html .= "<td align=center class=\"item_td\"><a href=\"./apply_view.php?home=".$home."&cd=1&page=".$page."&workflow_id=".$row["workflow_id"]."&k_stat=".$k_stat."&apply_no=".$apply_no."&apply_org=".$apply_org."&flow_doc=".$flow_doc."&apply_title=".$apply_title."&apply_body=".$apply_body."&start_d=".$start_d."&end_d=".$end_d."\">".$row["workflow_no"]."</a></td>\n";

//			      	$workflow_html .= "<td align=center class=\"item_td\"><a href=\"./apply_view.php?home=".$home."&cd=1&page=".$page."&workflow_id=".$row["workflow_id"]."\">".$row["workflow_id"]."</a></td>\n";
			      	$workflow_html .= "<td align=center class=\"item_td\">".$row["create_date"]."</td>\n";
			      	$workflow_html .= "<td align=left class=\"item_td\">".f_code_dsp(99, $row["flow_doc_code"])."</td>\n";
			      	$workflow_html .= "<td align=center class=\"item_td\">".f_staff_name_get($row["user_id"])."</td>\n";
			      	$workflow_html .= "<td align=left class=\"item_td\">".htmlspecialchars($row["title"])."</td>\n";

//				$str = mb_strimwidth($row["comment"], 0, 200, "...");
//			      	$workflow_html .= "<td align=left class=\"item_td\" style='word-break:break-all;'>".$str."</td>\n";
/*
if ($row["flow_doc_code"] == 1 && $row["stat"] == 3){
	if ($row["result_id"] == 0){
			      	$workflow_html .= "<td align=center class=\"item_td\"><font color=red>未</font></td>\n";
	}else{
			      	$workflow_html .= "<td align=center class=\"item_td\"><font color=blue>済</font></td>\n";
	}
}else{
			      	$workflow_html .= "<td align=center class=\"item_td\"> </td>\n";
}
*/
				if ($row["stat"] == 1){	//申請中
				      	$workflow_html .= "<td align=center class=\"item_td\"><font color=black>申請中</font></td>\n";
				}else if ($row["stat"] == 2){	//否決
				      	$workflow_html .= "<td align=center class=\"item_td\"><font color=red>否決</font></td>\n";
				}else if ($row["stat"] == 3){	//可決
				      	$workflow_html .= "<td align=center class=\"item_td\"><font color=blue>可決</font></td>\n";
				}else if ($row["stat"] == 4){	//取戻
				      	$workflow_html .= "<td align=center class=\"item_td\"><font color=black>取り戻し</font></td>\n";
				}else if ($row["stat"] == 5){	//差し戻し
					if ($row["remand_f"] == 1){
					      	$workflow_html .= "<td align=center class=\"item_td\"><font color=black>再申請済み</font></td>\n";
					}else{
					      	$workflow_html .= "<td align=center class=\"item_td\"><font color=black>差し戻し</font></td>\n";
					}
				}else{	//エラー
				}
				$workflow_html .= "</tr>\n";
				$cnt += 1;
			}
		}

	$workflow_html .= "</table>\n";

	$workflow_doc_html = "";
	$workflow_stat_html = "";
	
	if ($cd == 1){	//申請書番号がクリックされた
		$workflow_doc_html = workflow_doc_inf(3, $workflow_id, $staff_code);
		$workflow_stat_html = workflow_stat(3, $workflow_id, $staff_code, $home, $cd, $page);
	}

//各画面のPHP

	if ($sec_id == 1 || $sec_id == 2){
		$fg = 0;
		$sql_page = "SELECT * FROM ".$schema.".t_workflow ";
		if ($k_stat != 0){
			$sql_page .= "WHERE stat = ".$k_stat." ";
			$fg = 1;
		}
		if ($apply_org != 0){
			if ($fg == 0){
				$sql_page .= "WHERE busho_code = ".detail_org_id_get($apply_org)." ";
				$fg = 1;
			}else{
				$sql_page .= "and busho_code = ".detail_org_id_get($apply_org)." ";
			}
		}
		if ($apply_no != ""){
			if ($fg == 0){
				$sql_page .= "WHERE workflow_no = '".$apply_no."' ";
				$fg = 1;
			}else{
				$sql_page .= "and workflow_no = '".$apply_no."' ";
			}
		}
		if ($flow_doc != 0){
			if ($fg == 0){
				$sql_page .= "WHERE flow_doc_code = ".$flow_doc." ";
				$fg = 1;
			}else{
				$sql_page .= "and flow_doc_code = ".$flow_doc." ";
			}
		}
		if ($apply_title != ""){
			if ($fg == 0){
				$sql_page .= "WHERE title like '%".$apply_title."%' ";
				$fg = 1;
			}else{
				$sql_page .= "and title like '%".$apply_title."%' ";
			}
		}
		if ($apply_body != ""){
			if ($fg == 0){
				$sql_page .= "WHERE comment like '%".$apply_body."%' ";
				$fg = 1;
			}else{
				$sql_page .= "and comment like '%".$apply_body."%' ";
			}
		}
		if ($start_d != "" && $end_d != ""){
			if ($fg == 0){
				$sql_page .= "WHERE (create_date >= '".$start_d." 00:00:00' and create_date <= '".$end_d." 23:59:59') ";
				$fg = 1;
			}else{
				$sql_page .= "and (create_date >= '".$start_d." 00:00:00' and create_date <= '".$end_d." 23:59:59') ";
			}
		}else if ($start_d != ""){
			if ($fg == 0){
				$sql_page .= "WHERE create_date >= '".$start_d." 00:00:00' ";
				$fg = 1;
			}else{
				$sql_page .= "and create_date >= '".$start_d." 00:00:00' ";
			}
		}else if ($end_d != ""){
			if ($fg == 0){
				$sql_page .= "WHERE create_date <= '".$end_d." 23:59:59' ";
				$fg = 1;
			}else{
				$sql_page .= "and create_date <= '".$end_d." 23:59:59' ";
			}
		}

		$sql_page .= "ORDER BY workflow_id DESC ";
	}else{
		$sql_page = "SELECT * FROM ".$schema.".t_workflow WHERE (user_id = ".$staff_code." or ";
		$sql_page .= "next1_data LIKE '*".$staff_code."-%' or next2_data LIKE '*".$staff_code."-%' or next3_data LIKE '*".$staff_code."-%' or next4_data LIKE '*".$staff_code."-%' or next5_data LIKE '*".$staff_code."-%' or next6_data LIKE '*".$staff_code."-%' or next7_data LIKE '*".$staff_code."-%' or next8_data LIKE '*".$staff_code."-%' or next9_data LIKE '*".$staff_code."-%' or next10_data LIKE '*".$staff_code."-%' or ";
		$sql_page .= "cc1 LIKE '".$staff_code."-%' or cc2 LIKE '".$staff_code."-%' or cc3 LIKE '".$staff_code."-%' or cc4 LIKE '".$staff_code."-%' or cc5 LIKE '".$staff_code."-%' or ";
		$sql_page .= "next1_cc1_data LIKE '*".$staff_code."-%' or next1_cc2_data LIKE '*".$staff_code."-%' or next1_cc3_data LIKE '*".$staff_code."-%' or next1_cc4_data LIKE '*".$staff_code."-%' or next1_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql_page .= "next2_cc1_data LIKE '*".$staff_code."-%' or next2_cc2_data LIKE '*".$staff_code."-%' or next2_cc3_data LIKE '*".$staff_code."-%' or next2_cc4_data LIKE '*".$staff_code."-%' or next2_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql_page .= "next3_cc1_data LIKE '*".$staff_code."-%' or next3_cc2_data LIKE '*".$staff_code."-%' or next3_cc3_data LIKE '*".$staff_code."-%' or next3_cc4_data LIKE '*".$staff_code."-%' or next3_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql_page .= "next4_cc1_data LIKE '*".$staff_code."-%' or next4_cc2_data LIKE '*".$staff_code."-%' or next4_cc3_data LIKE '*".$staff_code."-%' or next4_cc4_data LIKE '*".$staff_code."-%' or next4_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql_page .= "next5_cc1_data LIKE '*".$staff_code."-%' or next5_cc2_data LIKE '*".$staff_code."-%' or next5_cc3_data LIKE '*".$staff_code."-%' or next5_cc4_data LIKE '*".$staff_code."-%' or next5_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql_page .= "next6_cc1_data LIKE '*".$staff_code."-%' or next6_cc2_data LIKE '*".$staff_code."-%' or next6_cc3_data LIKE '*".$staff_code."-%' or next6_cc4_data LIKE '*".$staff_code."-%' or next6_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql_page .= "next7_cc1_data LIKE '*".$staff_code."-%' or next7_cc2_data LIKE '*".$staff_code."-%' or next7_cc3_data LIKE '*".$staff_code."-%' or next7_cc4_data LIKE '*".$staff_code."-%' or next7_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql_page .= "next8_cc1_data LIKE '*".$staff_code."-%' or next8_cc2_data LIKE '*".$staff_code."-%' or next8_cc3_data LIKE '*".$staff_code."-%' or next8_cc4_data LIKE '*".$staff_code."-%' or next8_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql_page .= "next9_cc1_data LIKE '*".$staff_code."-%' or next9_cc2_data LIKE '*".$staff_code."-%' or next9_cc3_data LIKE '*".$staff_code."-%' or next9_cc4_data LIKE '*".$staff_code."-%' or next9_cc5_data LIKE '*".$staff_code."-%' or ";
		$sql_page .= "next10_cc1_data LIKE '*".$staff_code."-%' or next10_cc2_data LIKE '*".$staff_code."-%' or next10_cc3_data LIKE '*".$staff_code."-%' or next10_cc4_data LIKE '*".$staff_code."-%' or next10_cc5_data LIKE '*".$staff_code."-%') ";

			if ($k_stat != 0){
				$sql_page .= "and stat = ".$k_stat." ";
			}
			if ($apply_org != 0){
				$sql_page .= "and busho_code = ".$apply_org." ";
			}
			if ($apply_no != ""){
				$sql_page .= "and workflow_no = '".$apply_no."' ";
			}
			if ($flow_doc != 0){
				$sql_page .= "and flow_doc_code = ".$flow_doc." ";
			}
			if ($apply_title != ""){
				$sql_page .= "and title like '%".$apply_title."%' ";
			}
			if ($apply_body != ""){
				$sql_page .= "and comment like '%".$apply_body."%' ";
			}
		if ($start_d != "" && $end_d != ""){
			if ($fg == 0){
				$sql_page .= "WHERE (create_date >= '".$start_d." 00:00:00' and create_date <= '".$end_d." 23:59:59') ";
				$fg = 1;
			}else{
				$sql_page .= "and (create_date >= '".$start_d." 00:00:00' and create_date <= '".$end_d." 23:59:59') ";
			}
		}else if ($start_d != ""){
			if ($fg == 0){
				$sql_page .= "WHERE create_date >= '".$start_d." 00:00:00' ";
				$fg = 1;
			}else{
				$sql_page .= "and create_date >= '".$start_d." 00:00:00' ";
			}
		}else if ($end_d != ""){
			if ($fg == 0){
				$sql_page .= "WHERE create_date <= '".$end_d." 23:59:59' ";
				$fg = 1;
			}else{
				$sql_page .= "and create_date <= '".$end_d." 23:59:59' ";
			}
		}

		$sql_page .= "ORDER BY workflow_id DESC ";
	}

	$pageList = pageListCreate($page_max, "page", $sql_page, $page, "apply_view.php?home=".$home."&cd=0&k_stat=".$k_stat."&apply_no=".$apply_no."&apply_org=".$apply_org."&flow_doc=".$flow_doc."&apply_title=".$apply_title."&apply_body=".$apply_body."&start_d=".$start_d."&end_d=".$end_d);





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
<script language="javascript" type="text/javascript">
function stop(){
	document.getElementById("mode").value = 1;
	var target = document.getElementById("apply_view");
	target.submit();

}
</script>
<script type="text/javascript" src="./js/sales/jquery/jquery.min.js"></script>
<script type="text/javascript" src="./js/sales/printThis.js"></script>
<script type="text/javascript" src="./js/sales/print.js"></script> 

<script type="text/javascript" src="./js/sales/jquery/jquery-ui.min.js"></script>
<script type="text/javascript" src="./js/sales/jquery.ui.datepicker-ja.min.js"></script>
<link type="text/css" href="./css/sales/sunny/jquery-ui-1.9.2.custom.css" rel="stylesheet" />
<script src="./js/sales/jquery/jquery.maskedinput.js" type="text/javascript"></script>

<script type='text/javascript'>
$(function(){
$('#start_d').datepicker({dateFormat:'yy-mm-dd'});
$('#start_d').mask('9999-99-99');
$('#end_d').datepicker({dateFormat:'yy-mm-dd'});
$('#end_d').mask('9999-99-99');
});
</script>

	</head>

    <body id="top">
<?php
include("../home/View/Common/PageHeader.php");
?>
		<div id="sb-site">
<?php
include("./View/Common/Breadcrumb_apply_view.php");
?>
<?php
			echo "<form action='apply_view.php?cd=9&home=".$home."&page=".$page."' name='serchForm' method='post'>\n";
			echo "<div class='serchBoardArea'>\n";
			echo "<table>\n";
			echo "<tbody>\n";
			echo "<tr>\n";
			echo "<th>申請番号</th><td><input type='text' id='apply_no' name='apply_no' size=20 value=".$apply_no."></td>\n";
			echo "<th>組織</th><td>".f_master_org_select($apply_org, "apply_org")."</td>\n";
//			echo "<th> </th><td> </td>\n";
			echo "<th>申請書</th><td>".f_code_flow_doc_select($flow_doc)."</td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<th>状況</th><td>".f_stat_select($k_stat)."</td>\n";
			echo "<th>タイトル</th><td><input type='text' id='apply_title' name='apply_title' size=40 value='".$apply_title."'></td>\n";
			echo "<th>申請期間</th>\n";
			echo "<td><input type='text' id='start_d' name='start_d' size='10' value='".$start_d."'>&nbsp;～&nbsp;<input type='text' id='end_d' name='end_d' size='10' value='".$end_d."'></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<th>内容</th><td colspan=3><input type='text' id='apply_body' name='apply_body' size=80 value='".$apply_body."'></td>\n";
if ($file_path == ""){
			echo "<th>CSV出力</th><td><input type='button' onclick=\"location.href='apply_view.php?cd=10&home=".$home."&page=".$page."&k_stat=".$k_stat."&apply_no=".$apply_no."&apply_org=".$apply_org."&flow_doc=".$flow_doc."&apply_title=".$apply_title."&apply_body=".$apply_body."&start_d=".$start_d."&end_d=".$end_d."'\" value='CSV出力'>&nbsp;&nbsp;<font color=red>(期間・申請書による検索が必要です)</font></td>\n";
}else{
			echo "<th>CSV出力</th><td><input type='button' onclick=\"location.href='apply_view.php?cd=10&home=".$home."&page=".$page."&k_stat=".$k_stat."&apply_no=".$apply_no."&apply_org=".$apply_org."&flow_doc=".$flow_doc."&apply_title=".$apply_title."&apply_body=".$apply_body."&start_d=".$start_d."&end_d=".$end_d."'\" value='CSV出力'>&nbsp;&nbsp;<a href='".$file_path."'>ダウンロード</a></td>\n";
}
			echo "</tr>\n";
			echo "</tbody>\n";
			echo "</table>\n";
			echo "</div>\n";
			echo "<div class='serchButtonArea'>\n";
			echo "<p align=center><input type='submit' value='検索' class='serch' /></p>\n";
			echo "</div>\n";
			echo "</form>\n";
?>

			<form id="apply_view" action="apply_view.php?home=".$home."&cd=0&workflow_id=<?= $workflow_id ?>" method="post">

			<?= $pageList ?>

			<!-- serchListArea -->
			<div class="serchListArea">
				<?= $workflow_html ?>
			</div><!-- /.serchListArea -->

			<!-- serchButtonArea -->
			<div class="serchButtonArea">
				<p align=center>
<?php
			if ($cd == 1){
				echo "<input type=\"button\" id=\"btn_print\" value=\"印刷\" class=\"print\" />\n";
//				echo "<a href=\"#\" class=\"offprint\" id=\"btn_print\"><span class=\"print\" /></span>印刷</a>\n";
			}
?>

				</p>
			</div><!-- /.serchButtonArea -->

			<div id="printarea">
			<!-- serchEditArea -->
			<div class="serchEditArea">
				<?= $workflow_doc_html ?>
			</div><!-- /.serchEditArea -->

			<!-- serchEditArea -->
			<div class="serchEditArea">
				<?= $workflow_stat_html ?>
			</div><!-- /.serchEditArea -->
			</div><!-- /.printarea -->

		<input type="hidden" id="workflow_id" name="workflow_id" value=<?= $workflow_id ?> />
		<input type="hidden" id="mode" name="mode" value="0" />
		</form>
		</div><!-- /#sb-site -->

    <script src="../js/home/slidebars/slidebars.min.js"></script>
    <script src="../js/home/slidebars/mp_silde.js"></script>
    </body>
</html>
