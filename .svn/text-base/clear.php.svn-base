<?php
require('include/session.php');
require('include/dbconnect.php');

// Check if this is a debug user
if( $session->logged_in && $session->isDbug() ){
   // Clear all answers
   $query = "DELETE FROM Answer WHERE Student_SysID=" . $session->userinfo['Student_SysID'];
   $results = mysql_query( $query );

   // Clear all task start data
   $query = "DELETE FROM TakeTask WHERE Student_SysID=" . $session->userinfo['Student_SysID'];
   $results = mysql_query( $query );
}

header( "Location: index.php");

