<?php
require('include/session.php');
require('include/dbconnect.php');
require('include/constants.php');

if( $session->logged_in ){

   // Check for POST data on the two fields we want
   if( isset( $_POST['feedid'] ) && isset( $_POST['feedback'] ) ){

      // Check if their feedid is empty in the database
      $query = 'SELECT ID FROM Feedback WHERE ID='.$_POST['feedid'].' AND Feed IS NULL';
      $result = mysql_query( $query );

      // If its unavilable, reload page (which will give them a new one)
      if( mysql_num_rows($result) == 0 ){
         $_POST = array();
         header('Location: feedback.php');

      // If it was available insert their reply
      }else{
         $query = 'Update Feedback SET `Feed`=\'' . 
            mysql_real_escape_string(stripslashes($_POST['feedback'])) . '\' WHERE ID=' . $_POST['feedid'];
         mysql_query( $query );
         echo "Thank you. [<a href='index.php'>Home</a>]";
      }

   // No POST, display web page
   }else{

      // Generate a new feedback id for them
      $query = 'INSERT INTO Feedback (`ID`, `Feed`) VALUES (NULL, NULL)';
      mysql_query( $query );
      $id = mysql_insert_id();
      ?>
<h3><?php echo SYSTEM; ?> Feedback</h3>
As the RAWR system is still under development, we are interested in hearing your thoughts. If you have any thoughts or comments about the current system, what you like and don't like, or general suggestions for improvment, we would appreciate you letting us know. Thanks!<br>
<form method='post' action='feedback.php'>
<input type='hidden' name='feedid' value='<?php echo $id; ?>' />
<textarea name='feedback' style='width:45em;height:20em'></textarea><br>
<button type='submit'>Send Feedback</button>
</form><br><br>
I don't want to provide feedback, [<a href='index.php'>Home</a>]
      <?php
   }
}else{
   echo "You're not logged in<br>[<a href='index.php'>Home</a>]";
}
