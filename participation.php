<?php
require('include/dbconnect.php');
require('include/constants.php');
require('include/session.php');
require('include/service.php');

// Ensure this is a teacher
if( $session->logged_in && $session->isProf() ){
   ?><head><title>RAWR Task Completion</title></head><?php

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
      echo "<div style='padding-bottom:15px'>[<a href='index.php'>Home</a>] &nbsp; [<a href='export.php?class=$class'>Save as Csv</a>]</div>";

      // Get groups and tasks
      $query = "SELECT GroupMap.Group_SysID, GroupTask.SysID
                FROM GroupMap JOIN GroupTask
                ON GroupTask.Group_SysID = GroupMap.Group_SysID
                WHERE Class_SysID = $class
                GROUP BY GroupMap.Group_SysID, GroupTask.Start_Date, GroupTask.Task_SysID
                ";
      $result = mysql_query( $query );
      $grouptasks = array();
      while( ( $data = mysql_fetch_array( $result ) ) != NULL ){
         $grouptasks[$data['Group_SysID']][] = $data['SysID'];
      }

      // Get students
      $query = "SELECT Student.SysID, Student.FirstName, Student.LastName, GroupMap.Group_SysID, 
                 GroupTask.SysID AS GID
                FROM Student JOIN GroupMap JOIN TakeTask JOIN GroupTask
                ON GroupMap.Student_SysID = Student.SysID
                 AND TakeTask.GroupTask_SysID = GroupTask.SysID
                 AND TakeTask.Student_SysID = Student.SysID
                 AND GroupTask.Group_SysID = GroupMap.Group_SysID
                WHERE GroupMap.Class_SysID = $class
                 AND TakeTask.Finish IS NOT NULL
                ORDER BY Student.SysID, GroupTask.Start_Date
                ";
      $groups = ""; // Get list of groups in this class
      foreach( $grouptasks as $a => $b ){
         $groups .= " OR GroupMap.Group_SysID = $a";
      }
      $groups = substr( $groups, 3 );
//      echo "<PRE>" . htmlentities( print_r( $grouptasks, true ) ) . "</PRE>";
//      echo $query;
   
      $result = mysql_query( $query );
      $stu = -1;
      $gid = -1;
      $index = 10;

      echo "<table><tr><td>Last</td><td>First</td><td>Week 1</td><td>Week 2</td><td>Week 3</td><td>Week 4</td><td>Week 5</td><td>Week 6</td><td>Week 7</td><td>Week 8</td><td>Week 9</td><td>Week 10</td>";
      while( ($data = mysql_fetch_array( $result ) ) != NULL ){
         if( $stu != $data['SysID'] ){
            while( $index < 10 ){
               echo "<td></td>";
               $index++;
            }
            echo "</tr><tr><td>$data[LastName]</td><td>$data[FirstName]</td>";
            $stu = $data['SysID'];
            $gid = $data['Group_SysID'];
            $index = 0;
         }

         while( $grouptasks[$gid][$index] != $data['GID'] && $index < 10){
            echo "<td></td>";
            $index++;
         }
         if( $index != 10 ){
            $index++;
            echo "<td>Complete</td>";
         }
      }
            while( $index < 10 ){
               echo "<td></td>";
               $index++;
            }
      echo "</tr></table>";
      die();

   }else{
      // Didn't specify a class, send back to home
      Home();
   }

}else{
   // Wasn't a teacher, send them back to home
   Home();
}

?>
