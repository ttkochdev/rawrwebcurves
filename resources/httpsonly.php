<?php
// Force HTTPS
if( !isset( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] == off ){
   $site = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
   header( "Location: $site" );
   exit;
}
?>
