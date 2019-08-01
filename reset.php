<?php
require('include/session.php');
require('include/dbconnect.php');

if( $session->logged_in && $session->isDbug() ){
   $query = "UPDATE Student SET UpdateInfo=1, UpdateConsent=1, ShowInstructions=1 WHERE SysID=" . $session->userinfo[Student_SysID];
   $results = mysql_query( $query );
}

header( "Location: index.php");

