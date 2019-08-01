<?php
require('include/session.php');
require('include/dbconnect.php');
require('include/constants.php');

connect();

// Perform checks to see if this teacher owns the class
$owner = FALSE;
// Read class from URL params
$class = isset($_GET['class'] ) ? $class = $_GET['class'] : NULL;
if( is_numeric( $class ) ){
   // If class was a number, check if current teacher owns that class
   $query = "SELECT * FROM Class
             WHERE SysID = $class
              AND Teacher_SysID = " . $session->id;
   $result = mysql_query( $query );
   $owner = @mysql_num_rows( $result ) > 0;
}

// Check if this teacher owns the class
if( $session->isProf() && $owner ){

   // Read Course Name and Number
   $query = "SELECT Course.Name, Course.Course_ID AS CID, Class.ClassNumber AS CN, Course.SysID
      FROM Course JOIN Class
      ON Class.Course_SysID=Course.SysID
      WHERE Class.SysID=$class";
   $result = mysql_query( $query );
   $data = mysql_fetch_array( $result );

   $cid = $data['SysID']; // Course SysID

   // Dump POST vars
   if( DEBUG )
      echo "<PRE>" . htmlentities( print_r( $_POST, TRUE ) ) . "</PRE>";

   // Parse any _POST comands
   // Check for remove tasks
   if( strcmp( $_POST['remove'], 'remove' ) == 0 && !isset( $_POST['add'] ) ){

      // Loop through each task in POST variables
      foreach( $_POST as $id => $rm ){

         // If POST var was a number, remove it from the classes tasks
         if( is_numeric( $id ) && strcmp( $rm, 'on' ) == 0 ){
            $query = "DELETE FROM ClassTask
                      WHERE Class_SysID = $class
                       AND Task_SysID = $id";
            mysql_query( $query );
         }
      }
      // Send through bounce.php so refreshing doesn't resend data
      header( 'Location: bounce.php' );
   }

   // Check for add tasks
   if( strcmp( $_POST['add'], 'add' ) == 0 && !isset( $_POST['remove'] ) ){
      $query = "INSERT INTO ClassTask (Class_SysID, Task_SysID)
                VALUES"; // Query to add all classes

      // Loop through each task in POST variables
      foreach( $_POST as $id => $add ){
         // If POST var was a number, add it to the list to add
         if( is_numeric( $id ) && strcmp ( $add, 'on' ) == 0 ){
            $query .= " ($class, $id),";
         }
      }

      // Run query to add courses
      $query = substr( $query, 0 , -1 );
      mysql_query( $query );
      
      // Send through bounce.php so refreshing doesn't resend data
      header( 'Location: bounce.php' );
   }

   if( strcmp( $_POST['roster'], 'roster' ) == 0 ){
      header( 'Location: bounce.php' );
      echo "<PRE>" . htmlentities(print_r($_POST,true) ) . "</PRE>";
      die();
   }

   $tsks = array(); // Keeps track of already displayed tasks

   // Print page header and title
   echo "<head>".
        "<title>". SYSNAME . " Edit $data[Name]</title></head>".
        "<link href='css/edit.css' type='text/css' rel='stylesheet'/>".
        "</head>";
   echo "<h3>$data[Name] : &nbsp;$data[CID]-$data[CN]</h3>";
   echo "<div style='padding-bottom:15px'>[<a href='index.php'>Home</a>]</div>";


   //
   // List Tasks Assigned to this Class
   //

   // Select Tasks assigned to class
   $query = "SELECT Task.SysID, Task.Name
      FROM ClassTask JOIN Task
      ON ClassTask.Task_SysID = Task.SysID
      WHERE ClassTask.Class_SysID=$class
      ORDER BY Task.SysID";
   $result = mysql_query( $query );

   // Check if there are any currently registered tasks
   if( mysql_num_rows( $result ) > 0 ){
      // Create form for removing classes
      echo "<form action='".$_SERVER['REQUEST_URI']."' method='post'>";
      // Print headers of display table
      ?>
      <div style='font-weight:bold;margin-bottom:10px'>Remove Tasks:</div>
      <div style='padding-left:15px'>
      <table cellspacing=0 cellpadding=0 style="border:1px solid black;padding:2px">
      <tr><td></td><td class="displaytd">Name</td><td></td><td></td></tr>
      <?php
      // Print each task to the table
      while( ( $tasks = mysql_fetch_array( $result ) ) != NULL ){
         // Display a row for the task with a checkbox and link to view the task
         echo "<tr><td><input type='checkbox' name='$tasks[SysID]' /></td>";
         echo "<td class='displaytd'>$tasks[Name]</td>";
         echo "<td class='displaytd' style='font-size:x-small'>[<a href='javascript:void(0);'".
              "onclick='javascript:window.open(\"viewquiz.php?task=$tasks[SysID]\",".
              "\"View\",\"toolbar=0,status=0,menubar=0,fullscreen=no,scrollbars=yes,".
              "width=755,height=500\");'>view</a>]</td></tr>";
         $tsks[$tasks['SysID']] = TRUE;
      }
      // End table and form as well as add the button to remove things with
      ?>
      </table>
      <input type='hidden' value='remove' name='remove' />
      <input type='submit' value='Remove Selected' />
      </div></form>
      <?php
   }else{
      // No registered tasks, display messaged indicating so
      echo "<div style='font-weight:bold'>Class currently has no assigned tasks.</div>";
   }

   // Setup form for adding courses
   echo "<form action='".$_SERVER['REQUEST_URI']."' method='post'>";
   echo "<div style='padding-top:20px;padding-bottom:10px;font-weight:bold'>Add Tasks</div>";
   echo "<div style='padding-left:20px;'>";

   //
   // List Tasks Recommended for this Course
   //

   // Read reccomended tasks for this course
   $query = "SELECT Task.SysID, Task.Name, Task.Description
             FROM CourseTask JOIN Task
             ON CourseTask.Task_SysID = Task.SysID
             WHERE CourseTask.Course_SysID = $cid";
   $result = mysql_query( $query );

   // Check if there were any reccomended tasks
   if( mysql_num_rows( $result ) > 0 ){
      // Print table header for tasks
      ?>
      <div style='text-decoration:underline;margin-bottom:10px'>Recommended Tasks for this Course:</div>
      <table style='border:1px solid black'>
      <tr><td></td><td class='displaytd'>Name</td><td></td><td class='displaytd'>Description</td></tr>
      <?php
      // Print each task
      while( ( $tasks = mysql_fetch_array( $result ) ) != NULL ){
         // Make sure task hasn't been displayed before
         if( !isset( $tsks[$tasks['SysID']] ) ){
            // Print a row with a checkbox, task name, description, and link to view it
            echo "<tr><td><input type='checkbox' name='$tasks[SysID]' /></td>";
            echo "<td class='displaytd'>$tasks[Name]</td>";
            echo "<td style='font-size:x-small'>[<a href='javascript:void(0);'".
                 "onclick='javascript:window.open(\"viewquiz.php?task=$tasks[SysID]\",".
                 "\"View\",\"toolbar=0,status=0,menubar=0,fullscreen=no,scrollbars=yes,".
                 "width=755,height=500\");'>view</a>]</td>";
            echo "<td class='displaytd'>$tasks[Description]</td></tr>";
            $tsks[$tasks['SysID']] = TRUE;
         }
      }
      // End reccomended list of tasks
      echo "</table>";
      echo "<div style='text-decoration:underline;margin:10px 0'>Other Tasks:</div>";
   }


   //
   // List All Other Tasks
   //

   // Print table header for displaying tasks
   echo "<div style='height:252px;padding-bottom:10px'>";
   echo "<div style='height:250px;overflow:auto;float:left;border:1px solid black'>";
   echo "<table><tr><td></td><td class='displaytd'>Name</td><td></td><td class='displaytd'>Description</td></tr>";

   // Read all tasks from database
   $query = "SELECT Task.SysID, Task.Name, Task.Description
             FROM CourseTask JOIN Task";
   $result = mysql_query( $query );

   // Print each task
   while( ( $tasks = mysql_fetch_array( $result ) ) != NULL ){
      // Make sure the task hasn't been displayed before
      if( !isset( $tsks[$tasks['SysID']] ) ){
         // Print a row with a checkbox, task name, description, and a link to view it
         echo "<tr><td><input type='checkbox' name='$tasks[SysID]' /></td>";
         echo "<td class='displaytd'>$tasks[Name]</td>";
         echo "<td style='font-size:x-small'>[<a href='javascript:void(0);'".
              "onclick='javascript:window.open(\"viewquiz.php?task=$tasks[SysID]\",".
              "\"View\",\"toolbar=0,status=0,menubar=0,fullscreen=no,scrollbars=yes,".
              "width=755,height=500\");'>view</a>]</td>";
         echo "<td class='displaytd'>$tasks[Description]</td>";
         echo "<td style='width:30px'></td></tr>";
         $tsks[$tasks['SysID']] = TRUE;
      }
   }
   // End table of tasks and end form for adding tasks
   echo "</table></div></div>";

   echo "<input type='hidden' value='add' name='add' />";
   echo "</div>";
   echo "<input type='submit' value='Add Selected' />";
   echo "</form>";

   // 
   // Upload new roster
   //

   // Pull class data
   $query = "SELECT Course_SysID, ClassNumber, DATE_FORMAT( Start_Date, '%c-%d-%Y') AS SDate, DATE_FORMAT( END_DATE, '%c-%d-%Y') AS EDate FROM Class WHERE SysID=$class";
   $result = mysql_query( $query );
   $data = mysql_fetch_array( $result );
   echo $data['Start_Date'];
   echo "<div>";
   echo "<div style='padding-top:20px;padding-bottom:10px;font-weight:bold'>Add New Students from Roster</div>";
   echo '<form enctype="multipart/form-data" action="roster.php" method="post">';
   echo 'CSV File: <input type="file" name="file">';
   echo "<input type='hidden' name='course' value='$data[Course_SysID]'";
   echo "<input type='hidden' name='section' value='$data[ClassNumber]'";
   echo "<input type='hidden' name='start' value='$data[SDate]'";
   echo "<input type='hidden' name='end' value='$data[EDate]'";
   echo '<input type="hidden" name="data-upload" value="data-upload">';
   echo '<br><input type="submit" value="Upload">';
   echo '</form></div>';
}else{
   // User not logged in, redirect to home
   header( 'Location: index.php' );
}
?>
