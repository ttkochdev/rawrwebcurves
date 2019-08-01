<?php
require '../include/constants.php';
require '../include/dbconnect.php';
require '../include/session.php';
connect();

if( $session->isAdmin() ){
   // Read POST vars
   $subject = trim( $_POST['subject'] );
   $body = trim( $_POST['body'] );
   
   // If POST vars weren't null, update database
   if( $subject != NULL && $body != NULL ){
      // Escape them so they are MySQL safe
      $sub = mysql_real_escape_string( $subject );
      $bod = mysql_real_escape_string( $body );
      $query = "UPDATE Config SET Subject='$sub', Email='$bod' WHERE University_SysID = 1";
      mysql_query( $query );
      // Redirect through bounce to prevent history
     header( 'Location: ../bounce.php' );
   }

   // Page headers
   echo "<title>" . SYSNAME . " Email Settings</title>";
   echo "<h3>Email Settings</h3>";
   echo "[<a href='admin.php'>Admin Center</a>]<br><br>";

   // Read current info from database
   $query = "SELECT Subject, Email
             FROM Config
             WHERE University_SysID = 1
   ";
   $result = mysql_query( $query );
   $data = mysql_fetch_array( $result );
   
   if( FALSE ){
   ?>Current Email:<br><div style='width:600px;border:1px solid black'><PRE style='white-space:pre-wrap'>
   To:
   <hr>
   From: <?php echo htmlentities(EMAIL_FROM_NAME . ' <' . EMAIL_FROM_ADDR . '>'); ?>
   <hr>
   Subject: <?php echo htmlentities($data['Subject']); ?>
   <hr>
   <?php 
   echo htmlentities($data['Email']) . '</PRE></div><br>';
   }
   
   echo "Special Notations:<table><tr><td>[System]</td><td>Name of the system</td><td>" . SYSNAME . "</td></tr>".
        "<tr><td>[URL]</td><td>URL of the site</td><td>http://rawr.rit.edu</td></tr>".
        "<tr><td>[FirstName]</td><td>Recipient's first name</td><td></td></tr>".
        "<tr><td>[LastName]</td><td>Recipient's last name</td><td></td></tr>".
        "</table>";
   
   echo "<h3>Student Email:</h3>";
   echo "<form method='POST' action='emails.php'>";
   echo "Subject:<br><input type='text' value='$data[Subject]' style='width:300px' name='subject'><br><br>";
   echo "Body:<br><textarea name='body' style='width:600px;height:20em'>$data[Email]</textarea><br>";
   echo "<input type='submit' value='Change'></form>";
}
?>
