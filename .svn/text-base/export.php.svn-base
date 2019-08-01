<?php
require('include/session.php');

if( $session->logged_in && $session->isProf() ){

   $class = NULL;    // Class SysID
   $browser = FALSE; // Bool if list should be displayed in browser
   if( isset($_GET['class'] ) )
      $class = $_GET['class'];

   // Check if it should displayed in browser
   if( isset($_GET['browser']) && strcmp( $_GET['browser'], 't' ) == 0 )
      $browser = TRUE;


$class = NULL;
   if( isset($_GET['class'] ) )
      $class = $_GET['class'];

   if( is_numeric( $class ) ){
      if( $browser ){
         echo "<PRE>";
      }else{
         header('Content-Description: File Transfer');
         header('Content-Type: text/csv');
         header('Expires: 0');
         header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      }

      // Get course name and section number
      $query = "SELECT Course.Name, Course.Course_ID AS CID, Class.ClassNumber AS CN
                FROM Course JOIN Class
                ON Class.Course_SysID=Course.SysID
                WHERE Class.SysID=$class";
      $result = mysql_query( $query );
      $data = mysql_fetch_array( $result );
      if( $browser ){
         echo "Class: $data[Name]\n";
      }else{
         header('Content-Disposition: attachment; filename="'.$data['Name'].'.csv"');
      }

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
   
      $result = mysql_query( $query );
      $stu = -1;
      $gid = -1;
      $index = 10;

      echo '"Last","First","Week 1","Week 2","Week 3","Week 4","Week 5","Week 6","Week 7","Week 8","Week 9","Week 10"';
      while( ($data = mysql_fetch_array( $result ) ) != NULL ){
         if( $stu != $data['SysID'] ){
            while( $index < 10 ){
               echo ',"0"';
               $index++;
            }
            echo "\n\"$data[LastName]\",\"$data[FirstName]\"";
            $stu = $data['SysID'];
            $gid = $data['Group_SysID'];
            $index = 0;
         }

         while( $grouptasks[$gid][$index] != $data['GID'] && $index < 10){
            echo ',"0"';
            $index++;
         }
         if( $index != 10 ){
            $index++;
            echo ',"1"';;
         }
      }
      while( $index < 10 ){
         echo ',"0"';
         $index++;
      }
      echo "\n";
      if( $browser )
         echo "</PRE>";
      die();

   }else{
      // Didn't specify a class, send back to home
      Home();
   }

}else{
   Home();
}
?>
