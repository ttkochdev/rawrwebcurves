<?php
require_once('include/constants.php');
require_once('include/session.php');

function Home(){
   header('Location: index.php');
   exit();
}

function getName($id){
   $query = "SELECT FirstName, LastName FROM Student WHERE SysID = $id";
   $query = mysql_query($query);
   $query = mysql_fetch_array($query);
   return $query['FirstName'] . " " . $query['LastName'];
}

function getPName($id){
   $query = "SELECT FirstName, LastName FROM Teacher WHERE SysID = $id";
   $query = mysql_query($query);
   $query = mysql_fetch_array($query);
   return $query['FirstName'] . " " . $query['LastName'];
}

function getName2(){
   $query;
   if( $session->isStudent() ){
      $query = "SELECT FirstName, LastName FROM Student WHERE SysID = " .
               $session->userinfo['Student_SysID'];
   }
   $query = mysql_query( $query );
   $query = mysql_fetch_array( $query );
   return $query['FirstName'] . " " . $query['LastName'];
}

function getGroup($id, $num){
   $gtid = 'SELECT GroupTask.SysID AS ID
            FROM Student JOIN Schedule JOIN Class JOIN `Group` JOIN GroupMap JOIN GroupTask JOIN Task
            ON Student.SysID = Schedule.Student_SysID
             AND Schedule.Class_SysID = Class.SysID
             AND Class.SysID = Group.Class_SysID
             AND GroupMap.Group_SysID = Group.SysID
             AND GroupMap.Student_SysID = Student.SysID
             AND GroupTask.Group_SysID = Group.SysID
             AND GroupTask.Task_SysID = Task.SysID
            WHERE Student.SysID = ' . $id . ' AND Task.SysID = ' . $num;
   $gtid = mysql_query( $gtid );
   $gtid = mysql_fetch_array( $gtid );
   return $gtid['ID'];
}

// Gets tasks by student id
function getCurrentTasks($id){
   $query="SELECT Task.SysID AS ID, Course.Name AS Name, DATE_FORMAT(GroupTask.End_Date, '%W, %b %e') AS ED, ".
          " GroupTask.SysID AS GTID ".
          "FROM Student JOIN Schedule JOIN Class JOIN `Group` JOIN GroupTask JOIN Task JOIN GroupMap JOIN Course ".
//          " JOIN TakeTask ".
          "ON Student.SysID = Schedule.Student_SysID AND Schedule.Class_SysID = Class.SysID".
          " AND Class.SysID = `Group`.Class_SysID AND `Group`.SysID = GroupTask.Group_SysID".
          " AND GroupTask.Task_SysID = Task.SysID AND GroupMap.Student_SysID = Student.SysID".
          " AND GroupMap.Group_SysID = Group.SysID AND Class.Course_SysID = Course.SysID ".
//          " AND Student.SysID = TakeTask.Student_SysID AND GroupTask.SysID = TakeTask.GroupTask_SysID ".
          "WHERE Student.SysID = $id ".
          " AND (DATEDIFF(NOW(), GroupTask.End_Date) < 1) AND (DATEDIFF(NOW(), GroupTask.Start_Date) > -1) ".
//          " AND (TIMEDIFF(NOW(), TakeTask.Start) < '00:30:00') ".
          "ORDER BY GroupTask.End_Date";

   // New query, it doesn't matter what class the student is in as long as their in the task group
   $query = "SELECT GroupTask.Task_SysID AS ID, DATE_FORMAT(GroupTask.End_Date, '%W, %b %e') AS ED,
              GroupTask.SysID AS GTID, Course.Name AS Name
             FROM GroupTask JOIN GroupMap JOIN Class JOIN Course
             ON GroupTask.Group_SysID = GroupMap.Group_SysID
              AND GroupMap.Class_SysID = Class.SysID
              AND Class.Course_SysID = Course.SysID
             WHERE GroupMap.Student_SysID = $id
              AND (DATEDIFF(NOW(), GroupTask.End_Date) < 1) AND (DATEDIFF(NOW(), GroupTask.Start_Date) > -1)
             ORDER BY GroupTask.End_Date";
   $query = mysql_query($query);
   $tasks = array();
   $count = 0;
   while( ($data = mysql_fetch_array($query)) != NULL ){
      if( !taskAnswered( $id, $data['ID'], $data['GTID'] ) && !timeExpired( $id, $data['GTID'] ) ){
         $remaining = "SELECT TIMEDIFF( '" . MAX_TIME . "', TIMEDIFF( NOW(), `Start` ) ) AS TR FROM TakeTask
                       WHERE Student_SysID=$id AND GroupTask_SysID=$data[GTID]";
         $remaining = mysql_query( $remaining );
         $remaining = mysql_fetch_array( $remaining );
         $tr = NULL;
         if( $remaining != NULL )
            $tr = $remaining['TR'];
         $tasks[$count++] = array($data['ID'], $data['Name'], $data['ED'], $data['GTID'], $tr);
      }
   }

   return $tasks;
}

function timeExpired( $s_id, $gt_id ){
   $query = "SELECT * FROM TakeTask
             WHERE Student_SysID=$s_id
              AND GroupTask_SysID=$gt_id
              AND TIMEDIFF( NOW(), `Start` ) > '" . MAX_TIME . "'";
   $result = mysql_query( $query );
   return mysql_num_rows( $result ) > 0;
}

function taskAnswered( $s_id, $t_id, $g_id ){
   $query = "SELECT Question_SysID AS QID FROM TaskMap WHERE TaskMap.Task_SysID = $t_id";
   $result = mysql_query( $query );

   $submitted = FALSE;
   while( ($data = mysql_fetch_array( $result )) != NULL ){
      $query = "SELECT * FROM Answer 
                WHERE Student_SysID = $s_id AND Task_SysID = $t_id AND Question_SysID = $data[QID]
                AND GroupTask_SysID=$g_id";
      $query = mysql_query( $query );
      $answered = mysql_num_rows( $query );
      if( $answered > 0 ){
         $submitted = TRUE;
         break;
      }
   }
   
   return $submitted;
}

function getCurrentCourses( $id ){
   $query = "SELECT Course.Name AS Name, Class.SysID AS ID, Course.Course_ID AS CID, Class.ClassNumber AS CNUM
             FROM Course JOIN Class
             WHERE Course.SysID = Class.Course_SysID
              AND Class.Teacher_SysID = $id
              ";
   return fetchCourses( $query );
}
function fetchCourses( $query ){
   $result = mysql_query( $query );

   $results = array();
   while( ($data = mysql_fetch_array( $result )) != NULL ){
      $results[] = array( 'Name' => $data['Name'], 'SysID' => $data['ID'], 'ID' => $data['CID'],
                          'CNUM' => $data['CNUM'] );
   }
   return $results;
}

function getActiveCourses( $id ){
   $query = "SELECT Course.Name AS Name, Class.SysID AS ID, Course.Course_ID AS CID, Class.ClassNumber AS CNUM
             FROM Course JOIN Class
             WHERE Class.Course_SysID = Course.SysID
              AND (DATEDIFF(NOW(), Class.End_Date) < 1)
              AND (DATEDIFF(NOW(), Class.Start_Date) > -1)
              AND Class.Teacher_SysID != $id
             ";
   return fetchCourses( $query );
}

function getPastCourses( $id ){
   $query = "SELECT Course.Name AS Name, Class.SysID AS ID, Course.Course_ID AS CID, Class.ClassNumber AS CNUM
             FROM Course JOIN Class
             WHERE Class.Course_SysID = Course.SysID
              AND (DATEDIFF(NOW(), Class.End_Date) > 1)
              AND Class.Teacher_SysID != $id
             ";
   return fetchCourses( $query );
}

/* Replaces special commands in a string
 * ex: '[System]' => RAWR, [URL] => http://rawr.rit.edu
 */
function parseString( $str ){
   $str = str_replace( array( '[System]', '[URL]' ), array( SYSNAME, 'http://rawr.rit.edu' ), $str );
   return $str;
}

/* Replaces the first and last name of a student in a string
 */
function parseString2( $str, $fname, $lname ){
   $str = str_replace( array( '[FirstName]', '[LastName]' ), array( $fname, $lname ), $str );
   return $str;
}

?>
