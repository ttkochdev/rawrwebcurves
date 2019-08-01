<?php
require('include/dbconnect.php');
require('include/constants.php');
require('include/session.php');
require('include/service.php');
require('include/answer.php');
//require('barplot.php');
require('include/lineplot.php');

//define( 'DBUG', DEBUG );
define( 'DBUG', TRUE );

// Ensure this is a teacher
if( $session->logged_in && $session->isProf() || TRUE ){
   $class = NULL;
   $task = NULL;
   if( isset($_GET['class'] ) )
      $class = $_GET['class'];
   if( isset($_GET['task'] ) )
      $task = $_GET['task'];

   if( is_numeric( $class ) && is_numeric($task) ){
      // Get course name and section number
      $query = "SELECT Course.Name, Course.Course_ID AS CID, Class.ClassNumber AS CN
                FROM Course JOIN Class
                ON Class.Course_SysID=Course.SysID
                WHERE Class.SysID=$class";
      $result = mysql_query( $query );
      $data = mysql_fetch_array( $result );
      echo "<h3>$data[Name] : &nbsp;$data[CID]-$data[CN] :&nbsp; &nbsp; ";
      
      // Get tasks info
      $query = "SELECT `Name` 
                FROM `Task`
                WHERE `SysID`=$task";
      $result = mysql_query( $query );
      $data = mysql_fetch_array( $result );
      echo "$data[Name]</h3>";
      echo "<div style='padding-bottom:15px'>[<a href='index.php'>Home</a>] &nbsp;
            [<a href='responses.php?class=$class'>Back</a>]</div>";

      // Get Question SysIDs
      $qids = array();
      $query = "SELECT `Question`.`SysID` AS QID, `Question`.`Name`
                FROM `Task` JOIN `TaskMap` JOIN `Question`
                ON `TaskMap`.`Task_SysID` = `Task`.`SysID`
                 AND `TaskMap`.`Question_SysID` = `Question`.`SysID`
                WHERE `Task`.`SysID` = $task
                 AND `Question`.`Setup` = 0
                ORDER BY `TaskMap`.`Order`";
      $result = mysql_query( $query );
      while( ( $data = mysql_fetch_array( $result ) ) != NULL ){
         $qids[$data['Name']] = $data['QID'];
      }

      // Parse Each Question
      foreach( $qids as $name => $qid ){
         // Get all responses
         $query = "SELECT `Answer`.`Answer`, `GroupTask`.`Start_Date` AS SD
                   FROM `Answer` JOIN `GroupTask` JOIN `Group`
                   ON `Answer`.`GroupTask_SysID` = `GroupTask`.`SysID`
                    AND `GroupTask`.`Group_SysID` = `Group`.`SysID`
                   WHERE `GroupTask`.`Task_SysID` = $task
                    AND `Group`.`Class_SysId` = $class
                    AND `Answer`.`Question_SysID` = $qid
                   ORDER BY `GroupTask`.`Start_Date`";
         $result = mysql_query( $query );
         
         $responses = array();

         $weeks = 0;
         $reps = array();
         while( ( $data = mysql_fetch_array( $result ) ) != NULL ){
            if( !isset( $responses[$data['SD']] ) ){
               $responses[$data['SD']] = array();
//               $responses[$weeks] = array();
               $weeks++;
            }
            $ans = getAnswer( $data['Answer'] );
            if( !isset( $responses[$data['SD']][$ans[1]] ) ){
               $responses[$data['SD']][$ans[1]] = 1;
//               $responses[$weeks - 1][$ans[1]] = 1;
//               $reps[$ans[1]] = array();
//               $reps[$ans[1]][$weeks - 1] = 1;
            }else{
               $responses[$data['SD']][$ans[1]]++;
//               $responses[$weeks - 1][$ans[1]]++;
//               $reps[$ans[1]][$weeks - 1]++;
            }

            if( !isset( $reps[$ans[1]] ) ){
               $reps[$ans[1]] = array();//array_fill(0,10,0);
//               for( $i = 0; $i < $weeks - 1; $i++ ){
//                  $reps[$ans[1]][$i] = 0;
//               }
               if( $weeks != 1 )
                  $reps[$ans[1]] = array_fill(0,$weeks-1,0);
            }
            if( !isset( $reps[$ans[1]][$weeks-1] ) ){
               $reps[$ans[1]][$weeks-1] = 1;

               foreach( $reps as &$week ){
                  if( !isset( $week[$weeks-1] ) ){
                     $week[$weeks-1] = 0;
                  }
               }
            }else{
               $reps[$ans[1]][$weeks-1]++;
               /*foreach( $reps as &$week ){
                  if( !isset( $week[$weeks-1] ) ){
                     $week[$weeks-1] = 0;
                  }
               }*/
            }
         }

//               foreach( $reps as &$week ){
//                  for( $i = 0; $i < $weeks; $i++ ){
//                     if( !isset( $week[$i] ) ){
//                        $week[$i] = 0;
//                     }
//                  }
//               }
         
         if( DEBUG ){
            echo "<PRE>" . htmlentities(print_r($reps,TRUE)) . "</PRE>";
            echo "<PRE>";
            echo htmlentities(print_r($responses,TRUE));
            echo "</PRE>";
         }

         linePlot( $name, $reps, $qid );
         echo "<img src=\"R/pic$qid.png\"/>";
      }
      die();

      // Loop through each class
      while( ( $tasks = mysql_fetch_array( $result ) ) != NULL ){
         echo "<div><h4>$tasks[Name] <span style='font-size:x-small'>[<a href='viewquiz.php?task=$tasks[SysID]'>view</a>]</span></h4>";
         
         // Get groups of this task
         $query = "SELECT GroupTask.SysID
                   FROM GroupTask, `Group`
                   WHERE GroupTask.Group_SysID = Group.SysID
                    AND GroupTask.Task_SysID=$tasks[SysID]
                    AND Group.Class_SysID=$class
                   ORDER BY GroupTask.Start_Date";
         $result2 = mysql_query( $query );

         debug( $query );

         $resps = array( mysql_num_rows($result2) );
         foreach( $resps as &$r ){
            $r = array();
         }

         $pos = 0;
         while( ( $groups = mysql_fetch_array( $result2 ) ) != NULL ){
            // Get student responses
            $query = "SELECT Answer.Answer
                      FROM Answer
                      WHERE Answer.GroupTask_SysID=$groups[SysID]";
            $result3 = mysql_query( $query );

            if( DBUG ){
               echo "<b><i>DEBUG</i></b>:<br>$query";
               echo "<PRE>Answer\n";
               while( ( $ans = mysql_fetch_array( $result3 ) ) != NULL ){
                  $dat = getAnswer( $ans['Answer'] );
                  echo htmlentities($dat[0]) . " => " . htmlentities($dat[1]) . "\n";
               }
               echo "</PRE>";
               $result3 = mysql_query( $query );
            }

            while( ( $ans = mysql_fetch_array( $result3 ) ) != NULL ){
               $dat = getAnswer( $ans['Answer'] );
               if( strcmp( $dat[0], '' ) != 0 && strcmp( $dat[1], '' ) != 0 ){
                  if( $resps[$pos][$dat[0]][$dat[1]] == NULL ){
                     $resps[$pos][$dat[0]][$dat[1]] = 1;
                  }else{
                     $resps[$pos][$dat[0]][$dat[1]]++;
                  }
               }
            }

            ++$pos;
         }

         $count = 0;
         echo "Responses:<br>";
         foreach( $resps as $arr ){
            foreach( $arr as $q => $a ){
               echo htmlentities($q) . ": ";
               foreach( $a as $v => $c ){
                  echo htmlentities($v) . " x$c, &nbsp; ";
               }
               echo "<br>";
               
               $anss = array();
               $valss = array();
               foreach( $a as $v => $c ){
                  $anss[] = $v;
                  $valss[] = $c;
                  
               }
               
               //barplot( $anss, $valss, "Responses", "Occurances", $q, "$pos$count" );
               echo "<img src='R/temp$pos$count.png' width=300px/><br>";
               $count++;
            }
         }
         
         echo "</div>";
         echo "<PRE>";
         echo htmlentities(print_r($resps,TRUE));
         echo "</PRE>";
      }
      die();

      // Get student list
      $query = "SELECT Student.SysID, Student.FirstName, Student.LastName, Group.SysID AS GID
                FROM Class JOIN Schedule JOIN Student JOIN GroupMap JOIN `Group`
                ON Class.SysID=Schedule.Class_SysID
                 AND Student.SysID=Schedule.Student_SysID
                 AND Student.SysID=GroupMap.Student_SysID
                 AND Group.SysID=GroupMap.Group_SysID
                 AND Class.SYsID=Group.Class_SysID
                WHERE Class.SysID=$class
                ORDER BY LastName, FirstName";
      $result = mysql_query( $query );

      // Display table of responses per user
      echo "<table><tr><th>First</th><th>Last</th>";
      for( $i = 1; $i <= $tasks; ++$i )
         echo "<th>Week $i</th>";
      echo "</tr>";
      while( ($data = mysql_fetch_array( $result ) ) != NULL ){
         // Read yes/no for student's response completion
         $query = "SELECT TakeTask.Finish
                   FROM TakeTask JOIN GroupTask
                   ON TakeTask.GroupTask_SysID=GroupTask.SysID
                   WHERE TakeTask.Student_SysID=$data[SysID]
                    AND GroupTask.Group_SysID=$data[GID]
                    AND Finish IS NOT NULL";
         $taken = mysql_query( $query );
         echo "<tr><td>$data[LastName]</td><td>$data[FirstName]</td>";
         for( $i = 0; $i < mysql_num_rows( $taken ); ++$i ){
            echo "<td>Complete</td>";
         }
         echo "</tr>";
      }
      echo "</table>";
      
   }else{
      // Didn't specify a class, send back to home
      Home();
   }

}else{
   // Wasn't a teacher, send them back to home
   Home();
}

function debug($query){
   if( DBUG ){
      $result = mysql_query( $query );
      echo "<b><i>DEBUG</i></b>:<br>$query";
      echo "<PRE>SysID\tName\n";
      while( ( $tasks = mysql_fetch_array( $result ) ) != NULL ){
         echo "$tasks[SysID]\t$tasks[Name]\n";
      }
      echo "</PRE>";
   }
}

?>
