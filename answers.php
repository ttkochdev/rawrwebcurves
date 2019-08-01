<?php
require('include/dbconnect.php');
require('include/constants.php');
require('include/session.php');
require('include/service.php');
require('include/answer.php');
require('include/barplot.php');

// Local debug variable which can be enabled without turning on all of them
define( 'DBUG', DEBUG );
//define( 'DBUG', TRUE );

// Generate random string for saving file
  $length = 10;
    $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
    $string = "";    

    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }

//    echo ($string);


// Ensure this is a teacher
if( $session->logged_in && $session->isProf() || TRUE ){
   ?><head><title><?php echo SYSTEM;?> Task Responses</title></head><?php

   // Read class and task sysid's from URL params
   $class = isset($_GET['class']) ? $_GET['class'] : NULL;
   $task  = isset($_GET['task'])  ? $_GET['task']  : NULL;

   // Check if numbers were entered for class and task
   if( is_numeric( $class ) && is_numeric($task) ){

      // Get course name and section number
      $query = "SELECT Course.Name, Course.Course_ID AS CID, Class.ClassNumber AS CN
                FROM Course JOIN Class
                ON Class.Course_SysID=Course.SysID
                WHERE Class.SysID=$class";
      $result = mysql_query( $query );
      $data = mysql_fetch_array( $result );

      // Print heading detailing the class selected
      echo "<h3>$data[Name] : &nbsp;$data[CID]-$data[CN] :&nbsp; &nbsp; ";
      

      // Get tasks info
      $query = "SELECT `Name` 
                FROM `Task`
                WHERE `SysID`=$task";
      $result = mysql_query( $query );
      $data = mysql_fetch_array( $result );

      // Add task to heading and add a home and back button
      echo "$data[Name]</h3>";
      echo "<div style='padding-bottom:15px'>[<a href='index.php'>Home</a>] &nbsp;
            [<a href='responses.php?class=$class'>Back</a>]</div>";

      // Get Question SysIDs for this task
      $qids = array(); // Array of question SysIDs
      $query = "SELECT `Question`.`SysID` AS QID, `Question`.`Name`
                FROM `Task` JOIN `TaskMap` JOIN `Question`
                ON `TaskMap`.`Task_SysID` = `Task`.`SysID`
                 AND `TaskMap`.`Question_SysID` = `Question`.`SysID`
                WHERE `Task`.`SysID` = $task
                 AND `Question`.`Setup` = 0
                ORDER BY `TaskMap`.`Order`";
      $result = mysql_query( $query );

      // Save results to $qids array as [QuestionName] => QuestionSysID
      while( ( $data = mysql_fetch_array( $result ) ) != NULL ){
         $qids[$data['Name']] = $data['QID'];
      }

         $num = -1;
      // Parse Each Question
      foreach( $qids as $name => $qid ){

         // Get all responses for this question
         $query = "SELECT `Answer`.`Answer`, `GroupTask`.`Start_Date` AS SD
                   FROM `Answer` JOIN `GroupTask` JOIN `Group`
                   ON `Answer`.`GroupTask_SysID` = `GroupTask`.`SysID`
                    AND `GroupTask`.`Group_SysID` = `Group`.`SysID`
                   WHERE `GroupTask`.`Task_SysID` = $task
                    AND `Group`.`Class_SysId` = $class
                    AND `Answer`.`Question_SysID` = $qid
                   ORDER BY `GroupTask`.`Start_Date`";
         $result = mysql_query( $query );
         
         $dates = array(); // Tracks seen start dates
         $reps = array();  // Stores resposnes and count
         $weeks = 0;       // Week count

         // Loop through each response
         while( ( $data = mysql_fetch_array( $result ) ) != NULL ){
            
            // Get the answer
            $ans = getAnswer( $data['Answer'] ); // [0] = Name, [1] = Answer

            // Check if a new start date has been found, update weeks if it has
            if( !isset( $dates[$data['SD']] ) ){
               $dates[$data['SD']] = array();
               $weeks++;
            }

            // Check if this Name has been encountered before
            if( !isset( $reps[$ans[1]] ) ){
               // Create an entry in $reps if it hasn't
               $reps[$ans[1]] = array();

               // If this isn't the first week, fill previous weeks with blanks
               if( $weeks != 1 )
                  $reps[$ans[1]] = array_fill(0,$weeks-1,0);
            }

            // Check if this week has been encountered for this Name yet
            if( !isset( $reps[$ans[1]][$weeks-1] ) ){
               // Create entry in $reps for this week, start count at 1
               $reps[$ans[1]][$weeks-1] = 1;

               // Specifically create uncreated prior weeks and set count to 0
               foreach( $reps as &$week ){
                  if( !isset( $week[$weeks-1] ) ){
                     $week[$weeks-1] = 0;
                  }
               }
            }else{
               // This Name and week already existed, update its count
               $reps[$ans[1]][$weeks-1]++;
            }
         }

         // DEBUG junk, displays contents of $reps and $dates
         if( DBUG ){
            echo "<PRE>" . htmlentities(print_r($reps,TRUE)) . "</PRE>";
            echo "<PRE>";
            echo htmlentities(print_r($dates,TRUE));
            echo "</PRE>";
         }

         // Only try to make a graph if $resps has data
         
         if( count($reps) > 0 ){
            $num++;
            barPlot( $name, $reps, $string );
            echo "<img id='pic$num' name='$class$qid' src=\"images/loading.gif\" style='padding:222;border:1px solid black'/>";
         }else{
            echo "No responses for $name yet.<br>";
         }
      }
?>
<script type='text/javascript'>
var ids = [<?php 
$pics = '';
for( $i = 0; $i <= $num; $i++ )
   $pics .= "\"pic$i\",";
$pics = substr($pics, 0, -1 );
echo $pics;?>];

function picsu(){
   for( i = 0; i <= ids.length; i++ ){
      document.getElementById( ids[i] ).src = "R/pic" + document.getElementById( ids[i] ).name;
      document.getElementById( ids[i] ).style.padding = "";
      document.getElementById( ids[i] ).style.border = "";
   }
}
setTimeout( "picsu()", ids.length * 1000);
</script>
<?php
   }else{
      // Didn't specify a class, send back to home
      Home();
   }

}else{
   // Wasn't a teacher, send them back to home
   Home();
}

?>
