<?php

    require_once("DBAccess_Function.php");
    
    function db2str($IN_dbRow, $IN_strField, $IN_intLength) {

        return str_pad(trim(substr(mb_convert_encoding(strval($IN_dbRow[$IN_strField]),"SJIS","UTF-8"), 0, $IN_intLength)), $IN_intLength);
    }

    function getFileDat($file_name) {

        $strMe = 'getFileDat';

        //ファイルパスとファイル名
        //$strDatCnvFile ='D:/test/RCVHTDT.txt';
		$path = '/var/www/mportal/import_txt/textfile/RCVHTDT.txt';
        //ファイルオープン(追加）       
        $fp = fopen($strDatCnvFile, "r");
        if($fp){
            if(flock($fp, LOCK_SH)){
                while((!feof($fp))){
                    //ファイルを取得
                    $buffer = fgets($fp);
                    $buffer1 = mb_convert_encoding($buffer,"UTF8","SJIS");
                    return $buffer1;
                }
            }
        }        
        fclose($fp);      
    }

?>
