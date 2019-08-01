<?php
require("include/dbconnect.php");
require("include/constants.php");
require("include/session.php");
require("include/service.php");
echo("<head><link href='css/quizme.css' type='text/css' rel='stylesheet'/></head>");
// Make sure user is logged in
if( $session->logged_in && $session->isStudent() ){

   // Check if URL param task was set
   if( isset($_GET['task']) && $_GET['task'] != "" ){
      connect();
   
      // Confirm user is allowed to take this task
      $gtid = $_GET['task'];
      $query = "SELECT Task_SysID AS TID, Class_SysID AS CID
                FROM GroupTask JOIN GroupMap
                ON GroupTask.Group_SysID = GroupMap.Group_SysID
                 AND GroupMap.Student_SysID = " . $session->userinfo['Student_SysID'] . "
                WHERE GroupTask.SysID = $gtid";
      $result = mysql_query( $query );
      $data = mysql_fetch_array( $result );


      $tid; // Task SysID
      $cid; // Class SysID
      if( $data == NULL ){
         $tid = NULL;
      }else{
         $tid = $data['TID'];
         $cid = $data['CID'];
      }

      // Check if testing period expired
      if( timeExpired( $session->userinfo['Student_SysID'], $gtid ) ){
         $tid = NULL;
      }


      if( $tid != NULL ){
         // Mark task as started
         $started = 'INSERT INTO TakeTask (Student_SysID, GroupTask_SysID, Start, Finish)
                     VALUES ('.$session->userinfo['Student_SysID'].', '.$gtid.', NOW(), NULL)';
         mysql_query( $started );
         $val = mysql_insert_id();
         $time;

         if( $val != '' ){
            // Insert succeeded, set remaining time to max time
            $time = MAX_TIME;
         }else{
            // Insert failed, read remaining time from DB
            $time = mysql_query( "SELECT TIMEDIFF( '" . MAX_TIME . "', TIMEDIFF( NOW(), `Start` ) ) AS TR 
                                  FROM TakeTask 
                                  WHERE Student_SysID=" . $session->userinfo['Student_SysID'] . " 
                                  AND GroupTask_SysID=$gtid" );
            $time = mysql_fetch_array( $time );
            $time = $time['TR'];
         }

         // Read course name to display at top
         $tasknfo = "SELECT Course.Name
                     FROM Class JOIN Course
                     ON Class.Course_SysID = Course.SysID
                     WHERE Class.SysID=$cid";
         $tasknfo = mysql_query( $tasknfo );
         $tasknfo = mysql_fetch_array( $tasknfo );
         echo '<div class="quizheader">';
         echo '<div class="taskheader">Task for '.$tasknfo['Name'].'</div>';
         echo '<div class="timeremaining">You have <span id="timer">' . $time . '</span>
               to complete this task <span style="font-size:small">(time is approximate)</span></div>';
         echo '<div class="wrapper" ></div></div>';
         echo '<form method="post" action="submit.php"><div class="task">';
   

         // Select questions related to this task from database
         $query = "SELECT Task.SysID, File, Answer, Setup ".
                  "FROM   Task JOIN Question JOIN TaskMap ".
                  "ON    (TaskMap.Task_SysID = Task.SysID AND TaskMap.Question_SysID = Question.SysID) ".
                  "WHERE  Task.SysID = $tid ".
                  "ORDER BY `Order`";
         $result = mysql_query($query) or die ('Query failure 1');
         $data;

         $count = 0;
         $hasSetup = FALSE;
         // Put all questions on webpage
         echo "<input type=\"hidden\" name=\"test-number\" value=\"$gtid\">";
         while( ($data = mysql_fetch_array($result)) != null ){
            $file = $data[File];
            // Set div class based on if setup question or not

            if( $data['Setup'] ){
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
               echo "<input type='hidden' name='q-number_" . ++$count . "' value='1'>";
               echo file_get_contents("questions/$file");
               echo "</div>";
            }
         }
         
         // End last setup div, if there was one
         if( $hasSetup ){
            echo '</div>';
         }
         ?>
            <input type="submit" value="Submit">
            </div>
            </form>
            <script type='text/javascript'>
var hour = <?php echo substr( $time, 0, 2 ); ?>;
var min = <?php echo substr( $time, 3, 2 ); ?>;
var sec = <?php echo substr( $time, 6, 2 ); ?>;
var interval;

function UpdateTime(){
   sec -= 1;
   if( sec == -1 ){
      sec = 59;
      min -= 1;
      if( min == -1 ){
         min = 59;
         hour -= 1;
      }
   }
   document.getElementById( "timer" ).innerHTML = Pad(hour) + ":" + Pad(min) + ":" + Pad(sec);
   if( sec == 0 && min == 0 && hour == 0 )
      window.clearInterval( interval );
}
function Pad(n){
   if( n < 10 )
      return "0" + n;
   else
      return "" + n;
}
interval = window.setInterval("UpdateTime()",1000);
            </script>
         <?php
      }else{
         echo "You're not allowed to take this Task<br>[<a href='index.php'>Home</a>]";
      }
   }else{
      echo "No test was specified.<br>[<a href='index.php'>Home</a>]";
   }
}else{
   echo "You're not logged in<br>[<a href='index.php'>Home</a>]";
}
?>
