#!/usr/bin/php
<?php
// This is to be run by cron
// 0 4 * * 3 /var/www/webcurves/remail.php
// Every wednesday at 4am
define( 'EMAIL_FROM_NAME', 'RAWR Admin' );
define( 'EMAIL_FROM_ADDR', 'svfsps@rit.edu' );
define( 'SYSNAME', 'RAWR' );
define( 'CRON_USER', 'mysync' );

function connect(){
   $host = 'localhost';
   $user = 'apachesql';
   $pass = 'apacheadmin';
   $db   = 'webcurves';

   mysql_connect($host, $user, $pass) or die ('Failed to connect with database.' );
   mysql_select_db($db) or die ('Couldn\'t select database');
}

function parseString( $str ){
   $day = ( 1 - date('w') ) * 86400; // 1 - day('w') finds Monday, * 24 * 60 * 60
   $str = str_replace( array( '[System]', '[URL]', '[Start]', '[End]' ), 
      array( SYSNAME, 'http://rawr.rit.edu', date('l n/j/Y', time() + $day),
         date('l n/j/Y', time() + $day + 172800) ), $str );
   return $str;
}

function parseString2( $str, $fname, $lname ){
   $str = str_replace( array( '[FirstName]', '[LastName]' ), array( $fname, $lname ), $str );
   return $str;
}

// Check the user this is running as
$user = exec( 'whoami' );
if( strcmp( $user, CRON_USER ) == 0 ){
   connect();
   $query = "SELECT Reminder_Body AS Email, Reminder_Subject AS Subject
      FROM Config
      WHERE University_SysID = 1
      ";
   $result = mysql_query( $query );
   $data = mysql_fetch_array( $result );
   $body = parseString( $data['Email'] );
   $subject = parseString( $data['Subject'] );

   // Get all who are supposed to take the task
   $query = "SELECT Login.email, Student.SysID
      FROM Login JOIN GroupTask JOIN GroupMap JOIN Student
      ON Login.Student_SysID = Student.SysID
       AND GroupMap.Group_SysID = GroupTask.Group_SysID
       AND GroupMap.Student_SysID = Student.SysID
      WHERE (Login.userlevel = 1 OR Login.userlevel = 8)
       AND (DATEDIFF( NOW(), GroupTask.End_Date ) < 1 )
       AND (DATEDIFF( ADDDATE(NOW(), INTERVAL 3 DAY), GroupTask.End_Date ) > 0)
       AND Student.SpamMail = 1
      GROUP BY Student.SysID
      ORDER BY Student.SysID
      ";
   $result = mysql_query( $query );
   $people = array(); // Store their IDs and Emails
   while( ($data = mysql_fetch_array( $result ) ) != NULL ){
      $people[] = array( $data['SysID'], $data['email'] );
   }
   
   // Get list of people who have started the task
   $query = "SELECT Student.SysID
      FROM GroupTask JOIN GroupMap JOIN Student JOIN TakeTask
      ON GroupMap.Group_SysID = GroupTask.Group_SysID
       AND GroupMap.Student_SysID = Student.SysID
       AND TakeTask.Student_SysID = Student.SysID
       AND TakeTask.GroupTask_SysID = GroupTask.SysID
      WHERE 1
       AND (DATEDIFF( NOW(), GroupTask.End_Date ) < 1 )
       AND (DATEDIFF( ADDDATE(NOW(), INTERVAL 3 DAY), GroupTask.End_Date ) > 0)
       AND Student.SpamMail = 1
      GROUP BY Student.SysID
      ORDER BY Student.SysID
      ";
   $result = mysql_query( $query );
   $data = mysql_fetch_array( $result );

   // Send email to anyone from the first query who isn't in the second
   foreach( $people as $person ){
      if( $data == NULL || $person[0] != $data['SysID'] ){
         spamMail( $person[1], $subject, $body );
      }else{
         $data = mysql_fetch_array( $result );
      }
   }

}

// Sends an email
function spamMail( $to, $subject, $body ){
   // Mark email as from as defind in constants
   $from = "From: ".EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";

   // Echo copy of the email to the page
   echo "<PRE>" . htmlentities("To: $to") . '<hr>' . htmlentities($from) . '<hr>' .
      htmlentities("Subject: $subject") . '<hr>' . htmlentities($body) . "<hr></PRE>";

//return; // Actual sending disabled while developing

   // Send mail
//   $sent = mail( $to, $subject, $body, $from );
   $sent = mail( 'srm2997@rit.edu', $subject, $body, $from );
   echo $sent . "<br>";
   return $sent;
}


?>
