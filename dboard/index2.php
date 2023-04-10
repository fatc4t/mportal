<?php
date_default_timezone_set('Asia/Tokyo');
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
// $html .= "    <div >";
// $html .= "    <h1>RECEIVE</h1>";
// $html .="        <table id='table_head'>";
// $html .= "           <tr>";
// $html .= "               <th style='width:200px'>RECEIVE 日付</th>";
// $html .= "               <th style='width:400px'>RECEIVE ファイル</th>";
// $html .= "           </tr>";
// $html .= "       </table>";
// $html .= "    </div>";
$html .= "       <table id='tbody' >";
$html .= "           <div>";
$html .= "           </div>";

//20221109
$timestamp = time() ;
$today = date( "Ymd" , $timestamp ) ;
//20221109

//schema
$schema = $_GET['Key1'];
//orgid
$orgid  = $_GET['Key2'];
//receive
// $dir = '/mportal/'.$schema.'/'.$orgid.'/receive/';
// //server
// if (file_exists($dir) && is_dir($dir) ) {
// 	$scan_arr = scandir($dir);
// 	$files_arr = array_diff($scan_arr, array('.','..') );
// 	rsort($files_arr);
// 	//var_dump($files_arr.length);
// foreach ($files_arr as $file) {
// $file_date = substr($file, 8, 15);
// $a = 0;
// $html .= "           <tr> ";
// $html .= "              <td  style='width:200px' align='left' >".$file_date."</td>";
// $html .= "              <td  style='width:400px' align='left'>".$file."</td>";
// $html .= "           </tr>";
// }
// }
//  else{
// 	echo "RECEIVE:そのようなディレクトリはありません。";
//  }
// //$html .= "   </table>";
// //$html .= "  </div>";
// $html .= "       </table>";
// $html .= "    <h1>SENDING</h1>";
// $html .= "   <table style = 'border: 1px solid black;'>";
// $html .= "       <table id='tbody1' >";
// $html .= "    <div id = 'aaaa'>";
// $html .= "  <tr>";
// $html .= "   <th style='width:200px;background-color: lightblue;border: 1px solid black;'>SENDING 日付</th>";
// $html .= "    <th style='width:410px;background-color: lightblue;border: 1px solid black;'>SENDING ファイル</th>";


// //sending
// $dir1 = '/mportal/'.$schema.'/'.$orgid.'/sending/';
// //server
// if (file_exists($dir1) && is_dir($dir1) ) {
// 	$scan_arr = scandir($dir1);
// 	$files_arr = array_diff($scan_arr, array('.','..') );
// 	rsort($files_arr);
// foreach ($files_arr as $file) {
// $file_date = substr($file, 16, 15);
// $html .= "           <tr> ";
// $html .= "              <td  style='width:200px;border: 1px solid black;' align='left' >".$file_date."</td>";
// $html .= "              <td  style='width:410px;border: 1px solid black;' align='left'>".$file."</td>";
// $html .= "           </tr>";
// }
// }
//  else{
// 		echo "SENDING:そのようなディレクトリはありません。";
//  }

// $html .= "   </table>";

 //arcive
$html .= "    <h3>ERROR_RECEIVE</h3>";
// ////////////////////
$html .= "   <table style = 'border: 1px solid black;'>";
$html .= "       <table id='tbody1'>";
$html .= "    <div id = 'aaaa'>";
$html .= "  <tr>";
$html .= "   <th style='width:200px;background-color: lightblue;border: 1px solid black;'>ERROR_RCV 日付</th>";
$html .= "    <th style='width:400px;background-color: lightblue;border: 1px solid black;'>ERROR_RCV ファイル</th>";

$dir2 = '/mportal/'.$schema.'/'.$orgid.'/error/receive/';
// //server
if (file_exists($dir2) && is_dir($dir2) ) {
	$scan_arr = scandir($dir2);
	$files_arr = array_diff($scan_arr, array('.','..') );
	rsort($files_arr);
$count2   = 0;
foreach ($files_arr as $file) {
	
//err
	$err = substr($file, 0, 3);
if($err == 'err'){
$file_dt = substr($file, 12, 8);	
	
	//20221109
$file_dt = substr($file, 8, 8);
if ($today == $file_dt){
//20221109
		$count2   += 1;
if ($count2 <= 50) {  
$file_date = substr($file, 12, 8);
$html .= "           <tr> ";
$html .= "              <td  style='width:200px;border: 1px solid black;' align='left' >".$file_date."</td>";
$html .= "              <td  style='width:400px;border: 1px solid black;' align='left'>".$file."</td>";
$html .= "           </tr>";
}
}
}
}
}
 else{
		echo "ERROR_RCV:そのようなディレクトリはありません。";
 }
$html .= "   </table>";

$html .= "    <h3>ERROR_SENDING</h3>";
////////////////////
$html .= "   <table style = 'border: 1px solid black;'>";
$html .= "       <table id='tbody1'>";
$html .= "    <div id = 'aaaa'>";
$html .= "  <tr>";
$html .= "   <th style='width:200px;background-color: lightblue;border: 1px solid black;'>ERROR_SND 日付</th>";
$html .= "    <th style='width:400px;background-color: lightblue;border: 1px solid black;'>ERROR_SND ファイル</th>";
$dir3 = '/mportal/'.$schema.'/'.$orgid.'/error/sending/';
// //server
if (file_exists($dir3) && is_dir($dir3) ) {
	$scan_arr = scandir($dir3);
	$files_arr = array_diff($scan_arr, array('.','..') );
	rsort($files_arr);
	$count3 = 0;
foreach ($files_arr as $file) {
//err
	$err = substr($file, 0, 3);
if($err == 'err'){

$file_dt = substr($file, 20, 8);	
	//20221109
$file_dt = substr($file, 16, 8);
if ($today == $file_dt){
//20221109	
			$count3   += 1;
if ($count3 <= 50) { 
$file_date = substr($file, 20, 8);
$html .= "           <tr> ";
$html .= "              <td  style='width:200px;border: 1px solid black;' align='left' >".$file_date."</td>";
$html .= "              <td  style='width:400px;border: 1px solid black;' align='left'>".$file."</td>";
$html .= "           </tr>";
}
}
}
}
}
 else{
		echo "ERROR_SND:そのようなディレクトリはありません。";
 }
$html .= "   </table>";
$html .= "</head>";
$html .= "</html>";
echo $html;
?>