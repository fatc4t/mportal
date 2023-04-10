<?php
date_default_timezone_set('Asia/Tokyo');
require_once("DBAccess_Function.php");   
$vender_cd   = [];
$file        = [];
$file_exit   = '';
$file_time   = '';
$file_size   = '';
$html   = "";
$html  = "<!DOCTYPE html>";
$html .= "<html>";
$html .= "<head>";
$html .= "    <meta charset='utf-8' />";
$html .= "    <title>データファイル確認</title>";
$html .= "    <STYLE type='text/css'> ";
$html .= "       #table_head th{background-color:lightblue;width:200px;border: 1px solid black;}";
$html .= "       #tbody td{width:200px;border: 1px solid black;}";
$html .= "    </STYLE>";
$html .= "    <div  style='font-style:oblique; line-height:50px;'>";
$html .= "    </div>";
$html .= "    <div >";
$html .= "    <h3>発注全店</h3>";
$html .="        <table id='table_head'>";
$html .= "           <tr>";
$html .= "               <th style='width:60px'>店舗ID</th>";
$html .= "               <th style='width:100px'>店舗名</th>";
$html .= "               <th style='width:80px'>発注日</th>";
$html .= "               <th style='width:95px'>入荷予定日</th>";
//$html .= "               <th style='width:100px'>伝票番号</th>";
$html .= "               <th style='width:110px'>商品コード</th>";
$html .= "               <th style='width:80px'>発注数量</th>";
//$html .= "               <th style='width:80px'>商品原価</th>";
//$html .= "               <th style='width:80px'>商品売価</th>";
$html .= "           </tr>";
$html .= "       </table>";
$html .= "    </div>";
$html .= "       <table id='tbody' >";
$html .= "           <div>";
$html .= "           </div>";

//20221109
$timestamp = time() ;
$today = date( "Ymd" , $timestamp ) ;
//20221109

//schema
$schema = $_GET['Key1'];
//$schema = 'millionet_test';
//orgid
//$orgid  = $_GET['Key2'];
   //update
  //  $sql  = " select * from ".$schema.".trn1601 where ord_date ='20221102'";

//print_r($today );
$sql  = " select  TRN1601.organization_id as orgid
   ,m_organization_detail.organization_name as orgnm
   ,TRN1601.ord_date as ord_date
   ,TRN1601.arr_date as arr_date
   ,TRN1602.prod_cd as prod_cd
   ,TRN1602.ord_amount as ord_amount ";
$sql  .= " from ".$schema.".trn1601";
$sql  .= " inner join ".$schema.".TRN1602 on (TRN1601.hideseq = TRN1602.hideseq and TRN1601.organization_id = TRN1602.organization_id) ";
$sql  .= " inner join ".$schema.".m_organization_detail on ( TRN1601.organization_id = m_organization_detail.organization_id) ";
$sql  .= " where TRN1601.ord_date = '".$today."'  ";
$sql  .= " order by TRN1601.organization_id";
//print_r($sql);
    //条件
     //sqlExec($sql);
$result = getList($sql);

foreach ($result as $value){
	$orgid = $value['orgid'];
	$orgnm = $value['orgnm'];
	//$denno = $value['denno'];
	$ord_date = $value['ord_date'];
	$arr_date = $value['arr_date'];
	$prod_cd = $value['prod_cd'];
	$ord_amount = $value['ord_amount'];
	//$costprice = $value['costprice'];
	//$saleprice = $value['saleprice'];

	//table
$html .= "           <tr> ";
$html .= "              <td  style='width:75px' align='left' >".$orgid."</td>";
$html .= "              <td  style='width:115px' align='left'>".$orgnm."</td>";
$html .= "              <td  style='width:75px' align='left'>".$ord_date."</td>";
$html .= "              <td  style='width:95px' align='left' >".$arr_date."</td>";
//$html .= "              <td  style='width:100px' align='left' >".$denno."</td>";
$html .= "              <td  style='width:100px' align='left'>".$prod_cd."</td>";
$html .= "              <td  style='width:80px' align='left' >".$ord_amount."</td>";
//$html .= "              <td  style='width:80px' align='left'>".$costprice."</td>";
//$html .= "              <td  style='width:80px' align='left' >".$saleprice."</td>";
$html .= "           </tr>";
}



 //arcive
// $html .= "    <h1>ARCHIVE_RECEIVE</h1>";
// // ////////////////////
// $html .= "   <table style = 'border: 1px solid black;'>";
// $html .= "       <table id='tbody1'>";
// $html .= "    <div id = 'aaaa'>";
// $html .= "  <tr>";
// $html .= "   <th style='width:200px;background-color: lightblue;border: 1px solid black;'>ARCHIVE_RCV 日付</th>";
// $html .= "    <th style='width:400px;background-color: lightblue;border: 1px solid black;'>ARCHIVE_RCV ファイル</th>";

// $dir2 = '/mportal/'.$schema.'/'.$orgid.'/archive/receive/';
// // //server
// if (file_exists($dir2) && is_dir($dir2) ) {
// 	$scan_arr = scandir($dir2);
// 	$files_arr = array_diff($scan_arr, array('.','..') );
// 	rsort($files_arr);
// $count2   = 0;
// foreach ($files_arr as $file) {
// 	//20221109
// $file_dt = substr($file, 8, 8);
// if ($today == $file_dt){
// //20221109
// 		$count2   += 1;
// if ($count2 <= 50) {  
// $file_date = substr($file, 8, 15);
// $html .= "           <tr> ";
// $html .= "              <td  style='width:200px;border: 1px solid black;' align='left' >".$file_date."</td>";
// $html .= "              <td  style='width:400px;border: 1px solid black;' align='left'>".$file."</td>";
// $html .= "           </tr>";
// }
// }
// }
// }
//  else{
// 		echo "ARCHIVE_RCV:そのようなディレクトリはありません。";
//  }
// $html .= "   </table>";

// $html .= "    <h1>ARCHIVE_SENDING</h1>";
// ////////////////////
// $html .= "   <table style = 'border: 1px solid black;'>";
// $html .= "       <table id='tbody1'>";
// $html .= "    <div id = 'aaaa'>";
// $html .= "  <tr>";
// $html .= "   <th style='width:200px;background-color: lightblue;border: 1px solid black;'>ARCHIVE_SND 日付</th>";
// $html .= "    <th style='width:400px;background-color: lightblue;border: 1px solid black;'>ARCHIVE_SND ファイル</th>";
// $dir3 = '/mportal/'.$schema.'/'.$orgid.'/archive/sending/';
// // //server
// if (file_exists($dir3) && is_dir($dir3) ) {
// 	$scan_arr = scandir($dir3);
// 	$files_arr = array_diff($scan_arr, array('.','..') );
// 	rsort($files_arr);
// 	$count3 = 0;
// foreach ($files_arr as $file) {
// 	//20221109
// $file_dt = substr($file, 16, 8);
// if ($today == $file_dt){
// //20221109	
// 			$count3   += 1;
// if ($count3 <= 50) { 
// $file_date = substr($file, 16, 15);
// $html .= "           <tr> ";
// $html .= "              <td  style='width:200px;border: 1px solid black;' align='left' >".$file_date."</td>";
// $html .= "              <td  style='width:400px;border: 1px solid black;' align='left'>".$file."</td>";
// $html .= "           </tr>";
// }
// }
// }
// }
//  else{
// 		echo "ARCHIVE_SND:そのようなディレクトリはありません。";
//  }
// $html .= "   </table>";
$html .= "</head>";
$html .= "</html>";
echo $html;
?>