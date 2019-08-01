<?php
// Checks if webpage is using https, if not it redirects to the same page with https
if( !isset( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] == off ){
   $site = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
   header( "Location: $site" );
   exit;
}
?>
