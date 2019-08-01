<?php
require('include/session.php');

$okbutton = "Yes, you may use my responses";
$nobutton = "No, you may not use my responses";

// Check if user is logged in
if( $session->logged_in ){

   // Check if user has submitted, or just loaded the page
   if( !empty( $_POST ) ){
      // User submitted something

      // Check if the proper post variable was set.
      if( isset( $_POST['ok'] ) ){
         // Update Student's consent to what they selected
         $query = "UPDATE Student SET Consented=";
         if( strcmp( $_POST['ok'], $okbutton ) == 0 )
            $query .= '1';
         else
            $query .= '0';
         $query .= ' WHERE SysID = ' . $session->userinfo['Student_SysID'];

         mysql_query( $query );

         if( strcmp( mysql_error(), '' ) == 0 ){
            // Consent was updated successfully, mark that student has updated their consent
            $query = "UPDATE Student SET UpdateConsent=0 WHERE SysID = " . $session->userinfo['Student_SysID'];
            mysql_query( $query );

            if( strcmp( mysql_error(), '' ) == 0 ){
               // Complete success, forward to home page
               $query = "SELECT UpdateInfo AS UI FROM Student WHERE SysID=".$session->userinfo['Student_SysID'];
               $query = mysql_query( $query );
               $query = mysql_fetch_array( $query );

               // If user needs to complete demographics, forward there, else forward to home
               if( strcmp( $query['UI'], "1" ) == 0 ){
                  header( "Location: demographics.php" );

               }else{
                  header( "Location: index.php" );
               }

            }else{
               // Couldn't set consent flag, have user re-submit
               $_POST = array();
            }
         }else{
            // Couldn't set consent status, have user re-submit
            $_POST = array();
         }
      }else{
         // Wrong post data, send user proper consent page
         $_POST = array();
      }
   }

   // If no post data is present (page loaded, or cleared above)
   if( empty( $_POST ) ){
      ?><h2>Informed Consent Form</h2>
Project:  Student Learning in Introductory Calculus-based Physics<br>
PI: Scott V. Franklin, Dept. of Physics<br>
<br>
In the Physics Education Research Laboratory at RIT, we're curious about how students learn physics.  We invite you to participate in our research, which will help us improve instruction in subsequent years. Your participation will assist us in this task, and we appreciate you giving your time to help us.<br>
<br>
What we will ask you to do:<br>
As part of your coursework, you are taking short quizzes using the RAWR system.  We would like to use your responses as part of a research study on how students learn.  Your participation in this study allows us to analyze these quizzes and look for various correlating factors, such as math preparation or course grade.<br>
<br>
In addition to the RAWR quizzes, we would like to collect information about your performance in the course from your instructor or the Institute.<br>
<br>
Risks to participation<br>
There are no foreseeable risks to you participating in this study. Your participation in the study will not have an effect (positive or negative) on your grade.  Whether or not you choose to allow your data to be used in the study, you'll still have to take the quizzes to get course credit.<br>
<br>
Benefits to participation<br>
Your participation in this study will help us understand your thinking, and will be of great benefit to future students.<br>
<br>
Confidentiality:<br>
We keep our data in a secure location, and your identity as a participant will be kept confidential. A number will be randomly generated for each student and data will be associated with that number. Data will be averaged over the entire section, or over all students with similar grades.  We will never refer to you in a way that could identify you (such as your name), and we don't tell your instructor whether you participate. Only researchers in the Physics Education Research Laboratory (and their collaborators on this project) will have access to the data. We plan to keep the data indefinitely, but never to use it without full confidentiality.<br>
<br>
Voluntary<br>
Participation is voluntary. If you choose to take part, you may also stop at any time. All students will complete tasks for course credit, however only consenting student data will be included in the data set.<br>
<br>
Contact Information<br>
If you have questions about this study, please contact the PI, Scott V. Franklin. He is available at 475-2536 or svfsps@rit.edu. You can also contact the head of the Human Subjects Review Board, Dr. Heather Foti at hmfsrs@rit.edu.<br>
<br>
Do you consent to participating in the study of student learning?<br>
<table><tr><td>
      <form action="consent.php" method="post">
      <button name='ok' value='<?php echo $okbutton; ?>' type='submit'><?php echo $okbutton; ?></button>
      </form>
</td><td>
      <form action="consent.php" method="post">
      <button name='ok' value='<?php echo $nobutton; ?>' type='submit'><?php echo $nobutton; ?></button>
      </form>
</td></tr></table>
      <?php
   }
}
?>
