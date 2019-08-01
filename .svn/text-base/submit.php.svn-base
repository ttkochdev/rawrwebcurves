<?php
require("include/dbconnect.php");
require("include/session.php");
require("include/constants.php");
require('include/service.php');

$dbug = "";
connect();

if( $session->logged_in ){
   $dbug = ""; // debug text

   // Get all needed information:
   $gtid; // GroupTask SysID
   $sid;  // Student SysID
   $tid;  // Task SysID
   $did;  // Demographics SysID
   $cons; // T/F if user has consented to participate
   $qids; // Question SysID (multiple)
   $file; // Question file
   $ans;  // Answer file

   $gtid = (int)$_POST["test-number"];
   $sid = $session->userinfo['Student_SysID'];
   $result = mysql_query( "SELECT Task_SysID AS TID FROM GroupTask WHERE SysID=$gtid" );
   $data = mysql_fetch_array( $result );
   $tid = $data['TID'];
   $result = mysql_query( "SELECT Demographics_SysID AS DID, Consented FROM Student WHERE SysID=$sid" );
   $data = mysql_fetch_array( $result );
   $did = $data['DID'];
   $cons = $data['Consented'];
   $query = "SELECT File, Question.Setup, Question.SysID AS QID
             FROM Task JOIN TaskMap JOIN Question
             ON TaskMap.Task_SysID = Task.SysID AND TaskMap.Question_SysID = Question.SysID
             WHERE Task.SysID = $tid ORDER BY TaskMap.Order";
   $result = mysql_query($query) or die ('Query failure 1');
   $qids = array();
   $index = -1;
   while( ($data = mysql_fetch_array( $result )) != NULL ){
      if( !$data['Setup'] )
         $qids[++$index] = $data['QID'];
   }

   // Check if time expired
   if( timeExpired($sid, $gtid ) ){
      echo "The time to take this task expired. [<a href='index.php'>Home</a>]";
   }else{
   
      // Set finished in TakeTask
      $query = "SELECT * FROM TakeTask WHERE Student_SysID=$sid AND GroupTask_SysID=$gtid AND Finish IS NULL";
      if( DEBUG )
         echo "Set Finished in TakeTask:<br>$query<br>";
      $result = mysql_query( $query );
      if( mysql_num_rows($result) > 0 ){
         $query = "UPDATE TakeTask SET Finish=NOW() WHERE Student_SysID=$sid AND GroupTask_SysID=$gtid";
         if( DEBUG )
            echo "$query<br>";
         mysql_query( $query );
      }
   
      // Store Answers
      $dbug .= "Dump\n";
   
      if( DEBUG ){
         // Print out POST data
         print("<PRE>");
         print_r($_POST);
         print("</PRE>");
      }
   
      $answers = array(); // Answer data
      $index = -1;        // Answer index
   
      foreach( $_POST as $q => $a ){
         // Loop through all POST data
   
         $dbug .= "$q = $a\n"; // debug crap
   
         if( strcmp($q, "test-number") == 0 ){
            // Ignore line if it defines the Task number
         }else{
            if( strpos($q, "q-number_") !== 0 ){
               // This is a response
               // Store response to answer array
               $answers[$index] .= "$q=$a\n";
            }else{
               // This is the start of a new question
               // Increment index, initialize string
               $index++;
               $answers[$index] = "";
            }
         }
      }
   
      //foreach($answers as $count){ 
      for( $i = 0; $i < count( $answers ); ++$i ){
         $ans = $answers[$i];
         $question = $qids[$i];
   
         //Checks to see if already submitted for this task
         $save = "SELECT * FROM Answer 
                  WHERE (Student_SysID = $sid AND Task_SysID = $tid AND Question_SysID = $question
                   AND GroupTask_SysID = $gtid)";
         $dbug .= "\n$save\n";
         $save = mysql_query($save);
         if( mysql_num_rows( $save ) == 0 ){
            // Save New Answer
   
            $save = "INSERT INTO Answer
                      (Student_SysID, Task_SysID, Question_SysID, Answer, GroupTask_SysID, Demographics_SysID, IRB_OK)
                     VALUES ( $sid, $tid, $question, '$ans', $gtid, $did, $cons )";
            $saved = mysql_query( $save );
            $dbug .= "$save : $saved\n";
   
         }else{
            // Answer Exists
            echo "<i><b>You cannot change your answers</b></i><br>";
         }
      }
   
      if( DEBUG ){
         echo "<PRE>$dbug</PRE>";
         echo "Return <a href='index.php'>Home<br>";
      }else{
         header( "Location: index.php" );
         die();
         if( count( getCurrentTasks( $session->userinfo[Student_SysID] ) ) == 0 ){
            header( "Location: feedback.php" );
         }else{
            header( "Location: index.php" );
         }
      }
   }

}else{
   echo "You are not logged in, please <a href='index.php'>log in</a>.<br>";
}
?>
