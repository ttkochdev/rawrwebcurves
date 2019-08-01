<?

require('include/dbconnect.php');
require('include/constants.php');
require('include/session.php');
require('include/service.php');

if( $session->logged_in && ( $session->userlevel == ADMIN_LEVEL || $session->userlevel == DBUG_USER_LEVEL ) ){
   /**
    * sendSpamEmail - Sends a welcome message to students.
    */
   function sendSpamEmail(){
      connect();
      $query  = "SELECT `Email` FROM `Student`";
      $result = mysql_query($query) or die(mysql_error());
      $to = "";
      while( ($data = mysql_fetch_array($result)) != NULL ){
         $to .= $data['Email'] . ', ';
      }
      $to = substr( $to, 0, -2 );
$to = 'srm2997@rit.edu';
      $from = "From: ".EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";
      $subject = "RAWR - Welcome!";
      $body =
"Hello,

The Rapid Assessment and Web Reports (RAWR) group needs your help in developing a new system to measure student learning.  We're asking if you could take 5-10 minutes and
1.  go to https://rawr.rit.edu/
2.  log in using this e-mail for the username (only the part before the \"@\") and your last name for the password (w/capital letters, you can use the \"forgot password\" button to reset)
3.  test our IRB approval form, demographic survey, and a short physics quiz
4.  leave feedback on how easy you found the interface

We really appreciate your help.  This system will be used in the coming year at RIT and in subsequent years at colleges across the country.

Thank you again,

Scott Franklin
Co-PI, RAWR project";
      $headers = "MIME-Version: 1.0rn";
      $headers .= "Content-type: text/html; charset=iso-885n-lrn";
      $headers  .= "From: $from\r\n";


// Do no actually send the emails
//      return mail($to, $subject, $body, $from);
   
      echo "<PRE>";
      echo "To: $to<hr>From: $from<hr>Subject: $subject<hr>Body: $body<hr>Headers: $headers<hr>";
      echo "</PRE>";

   }
//sendSpamEmail();
}else{
   echo "epicfail";
   echo "<br>". $session->userlevel . "<br>";
}



// Sends an email
function spamMail( $to, $subject, $body ){
   // Mark email as from as defind in constants
   $from = "From: ".EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";

   // Echo copy of the email to the page
   echo "<PRE>" . htmlentities("To: $to") . '<hr>' . htmlentities($from) . '<hr>' .
        htmlentities("Subject: $subject") . '<hr>' . htmlentities($body) . "<hr></PRE>";

//   return;

   // Send mail
   $sent = mail( $to, $subject, $body, $from );
   echo $sent . "<br>";
   return $sent;
}

connect();

// Read from DB if this college sends emails and if they need to be sent
$query = "SELECT Email, Subject
          FROM Config
          WHERE Email_Sent = 0
           AND Send_Emails = 1
           AND University_SysID = 1
";
$result = mysql_query( $query );
if( mysql_num_rows( $result ) == 0 ){
   // No emails need to be sent
   die();
}

// Read email body and subject and replace special commands
$data = mysql_fetch_array( $result );
$subject = parseString( $data['Subject'] );
$body = parseString( $data['Email'] );

// Select any student who has a task to complete
$query = "SELECT Student.FirstName, Student.LastName, Login.email
          FROM Student JOIN Login JOIN GroupTask JOIN GroupMap
          ON Login.Student_SysID = Student.SysID
           AND GroupMap.Group_SysID = GroupTask.Group_SysID
           AND GroupMap.Student_SysID = Student.SysID
          WHERE (DATEDIFF(NOW(), GroupTask.End_Date) < 1)
           AND (DATEDIFF(NOW(), GroupTask.Start_Date) > -1)
           AND (Login.userlevel = 1 OR Login.userlevel = 8)
           AND Student.SysID = 1
          GROUP BY Student.SysID
          ORDER BY Student.SysID
";
$result = mysql_query( $query );

// Loop through each student sending an email
while( ($data = mysql_fetch_array( $result ) ) != NULL ){
   $thisbody = parseString2( $body, $data['FirstName'], $data['LastName'] );
   spamMail( $data['email'], $subject, $thisbody );
}

// Update Config to indicate the emails have been sent
$query = "UPDATE Config
          SET Email_Sent=1
          WHERE University_SysID = 1
";
mysql_query( $query );

?>
