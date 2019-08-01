<?php
require("include/dbconnect.php");
require("include/constants.php");
require("include/session.php");
require("include/service.php");
echo("<head><link href='css/quizme.css' type='text/css' rel='stylesheet'/></head>");
echo '<div class="task" style="margin-left:0px">';
// Make sure user is logged in
// Check if URL param task was set
if( isset($_GET['task']) && $_GET['task'] != "" ){
   connect();

   // Select questions related to this task from database
   $query = "SELECT Task.SysID, File, Answer, Setup ".
      "FROM   Task JOIN Question JOIN TaskMap ".
      "ON    (TaskMap.Task_SysID = Task.SysID AND TaskMap.Question_SysID = Question.SysID) ".
      "WHERE  Task.SysID = $_GET[task] ".
      "ORDER BY `Order`";
   $result = mysql_query($query) or die ('Query failure 1');
   $data;

   $count = 0;
   $hasSetup = FALSE;
   // Put all questions on webpage
   echo "<input type=\"hidden\" name=\"test-number\" value=\"$_GET[task]\">";
   while( ($data = mysql_fetch_array($result)) != null ){
      $file = $data[File];
      // Set div class based on if setup question or not

      if( $data[Setup] ){
         // Setup question
         if( $hasSetup ){
            // If there is already a setup div, end it
            echo '</div>';
         }
         echo "<div class='header'>";
         $hasSetup = TRUE;

         echo file_get_contents("questions/$file");

         echo '</div><div class="questions">';
      }else{
         // Normal question
         echo '<div class="question">';
         if( $data[Answer] != NULL && strcmp( $data[Answer], "" ) != 0 )
            echo "<input type='hidden' name='q-number_" . ++$count . "' value='1'>";
         echo file_get_contents("questions/$file");
         echo "</div>";
      }
   }
}else{
   echo "No test was specified.<br>";
}
?>
</div>
</div>
