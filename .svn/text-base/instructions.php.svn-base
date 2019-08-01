<?php
require('include/session.php');

if( $session->logged_in ){

   // Check for POST data
   if( !empty( $_POST ) ){

      // Check if user clicked ok
      if( strcmp( $_POST['ok'], 'Ok' ) == 0 ){

         // Remove flag to display this page
         $query = "UPDATE Student SET ShowInstructions=0 WHERE SysID = " . $session->userinfo['Student_SysID'];
         mysql_query( $query );

         // If thehre was no errors while updating
         if( strcmp( mysql_error(), '' ) == 0 ){

            // Check if IRB or Demographics needs to be done
            $query = "SELECT UpdateConsent AS UC, UpdateInfo AS UI FROM Student WHERE SysID=".$session->userinfo['Student_SysID'];
            $query = mysql_query( $query );
            $query = mysql_fetch_array( $query );

            // Redirect to IRB, Demographics, or Home
            if( strcmp( $query['UC'], "1" ) == 0 ){
               header( "Location: consent.php" );
            }else if( strcmp( $query['UI'], "1" ) == 0 ){
               header( "Location: demographics.php" );
            }else{
               header( "Location: index.php" );
            }
            
         }else{
            // If an mysql error occured, clear post data to redisplay the message
            $_POST = array();
         }
      }
   }

   // If no POST data, display message
   if( empty( $_POST ) ){
      ?>
Thank you for logging in to RAWR: the Rapid Assessment and Web Reports system. You are being asked to answer short assessments; each should take no longer than 5-10 minutes.  Some of these may be pre-tests, occurring before classroom instruction has taken place.  Your grade on these quizzes is determined by your good-faith participation, not  by the correctness of your answer.<br>
<br>
Because tasks are short, we expect that you can take them in one sitting, without break.  The system will reset if 30 minutes passes without activity, although this should not cause any difficulty.<br>
<br>
Results from these assessments are used by your instructor to develop better classroom activities/lecture notes and by the Physics Department to assess the efficacy of instruction.<br>
<br>
      <form action="instructions.php" method="post">
      <button name='ok' value='Ok' type='submit'>Ok</button>
      </form>
      <?php
   }

}else{
   // If not logged in, redirect back to index
   header( "Location: index.php" );
}
?>
