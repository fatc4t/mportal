<?php
date_default_timezone_set('Asia/Tokyo');
$vender_dir  = glob('/var/www/as_400/order/*');
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
$html .= "    <title>発注データファイル確認</title>";
$html .= "    <STYLE type='text/css'> ";

$html .= "       #table_head th{background-color:lightblue;width:200px;border: 1px solid black;}";
$html .= "       #tbody td{width:200px;border: 1px solid black;}";
$html .= "    </STYLE>";
$html .= "    </div>";
$html .= "    <div align='center' style='font-style:oblique; line-height:50px;'>";
$html .= "         <h1><span>発注データファイル確認</span></h1>";
$html .= "    </div>";
$html .= "    <div align='center' style='width:100%'>";
$html .="        <table id='table_head'>";
$html .= "           <tr>";
$html .= "               <th>ベンダーコード</th>";
$html .= "               <th>ファイル状態</th>";
$html .= "               <th>ファイルサイズ</th>";
$html .= "               <th>ファイル出力時間</th>";
$html .= "           </tr>";
$html .= "       </table>";
$html .= "    </div>";
$html .= "    <div align='center' style='height: 500px; overflow: auto; width:calc(100% + 16px)'>";
$html .= "       <table id='tbody' >";
foreach ($vender_dir as $path) {
    $vender_cd = substr($path, 22, 6);
    $file      = glob('/var/www/as_400/order/'.$vender_cd.'/*');
    if($file){
        $file_exit   = 'あり';
        $file_time   = filemtime('/var/www/as_400/order/'.$vender_cd.'/'.$vender_cd.'.TXT');
        $file_size   = filesize('/var/www/as_400/order/'.$vender_cd.'/'.$vender_cd.'.TXT').'　bytes';
    }else{
        $file_exit = 'なし';
        $file_time = '';
        $file_size = ''; 
    }
$html .= "           <tr> ";
$html .= "              <td align='left'>".$vender_cd."</td>";
$html .= "              <td align='left'>".$file_exit."</td>";
$html .= "              <td align='left'>".$file_size."</td>";
$html .= "              <td align='left'>".date('Y/m/d H:i:s', $file_time)."</td>";
$html .= "           </tr>";
}
$html .= "       </table>";
$html .= "    </div>";
$html .= "    </div>";
$html .= "    <div align='center' style='font-style:oblique; line-height:50px;'>";
$html .= "         <h1><span>※確認時間に気を付けてください!!</span></h1>";
$html .= "    </div>";
$html .= "</head>";
$html .= "</html>";
echo $html;
?>