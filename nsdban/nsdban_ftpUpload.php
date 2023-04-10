<?php

// FTP host settings
$ftp_host = "192.168.100.15";
$ftp_user = "posftp@192.168.100.15";
$ftp_pass = "Pc4EeF2fN6"; 

$server_destination = ".\pos_recv"; //FTP Server   //Save here
$local_source=  "";                 //send from here


// (B) CONNECT TO FTP SERVER
$ftp = ftp_connect($ftp_host) or exit("Failed to connect to $ftp_host");
 
// (C) LOGIN & UPLOAD
if (ftp_login($ftp, $ftp_user, $ftp_pass)) {
  echo ftp_put($ftp, $server_destination, $local_source, FTP_BINARY)
    ? "Uploaded to $server_destination"        //if
    : "Error uploading $local_source" ;   //else
} else { echo "Invalid user/password"; }
 
// (D) CLOSE FTP CONNECTION
ftp_close($ftp);