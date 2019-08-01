<?php
require('include/dbconnect.php');
require('include/constants.php');
require('include/session.php');
require('include/service.php');
require('include/answer.php');
require('barplot.php');

define( 'DBUG', DEBUG );
//define( 'DBUG', TRUE );

// Ensure this is a teacher
if( $session->logged_in && $session->isProf() ){
   ?><head><title>RAWR Course Tasks</title></head><?php

   $class = NULL;
   if( isset($_GET['class'] ) )
      $class = $_GET['class'];

   if( is_numeric( $class ) ){
      // Get course name and section number
      $query = "SELECT Course.Name, Course.Course_ID AS CID, Class.ClassNumber AS CN
                FROM Course JOIN Class
                ON Class.Course_SysID=Course.SysID
                WHERE Class.SysID=$class";
      $result = mysql_query( $query );
      $data = mysql_fetch_array( $result );
      echo "<h3>$data[Name] : &nbsp;$data[CID]-$data[CN]</h3>";
      echo "<div style='padding-bottom:15px'>[<a href='index.php'>Home</a>]</div>";
/*
Plan of attack:
Use class ID to get a list of tasks (order by sysid)
For each task, get the groups which took it (order by start date)
For each group, get the student responses

*/

      // Get tasks for the class
      $query = "SELECT Task.SysID, Task.Name
                FROM `Group` JOIN GroupTask JOIN Task
                ON GroupTask.Group_SysID=Group.SysID
                 AND GroupTask.Task_SysID=Task.SysID
                WHERE Group.Class_SysID=$class
                GROUP BY Task.SysID
                ORDER BY Task.SysID";
      $result = mysql_query( $query );

      debug( $query );
      echo "<div style='text-decoration:underline;margin-bottom:10px'>Tasks Assigned to this Class:</div>";
      echo "<div style='padding-left:15px'>";
      echo '<table cellspacing=0 cellpadding=0>';
      while( ( $tasks = mysql_fetch_array( $result ) ) != NULL ){
//         echo "$tasks[Name] <span style='font-size:x-small'>[<a href='viewquiz.php?task=$tasks[SysID]'>view</a>]</span> &nbsp; [<a href='answers.php?class=$class&task=$tasks[SysID]'>View Responses</a>]<br>";
         echo "<tr><td>$tasks[Name]</td>".
              "<td style='padding-left:10px'><span style='font-size:x-small'>[<a href='viewquiz.php?task=$tasks[SysID]'>view</a>]</span></td>".
              "<td style='padding-left:10px'>[<a href='answers.php?class=$class&task=$tasks[SysID]'>View Responses</a>]</td></tr>";
      }
      echo "</div>";
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
