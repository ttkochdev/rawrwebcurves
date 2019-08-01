<?php
require('../include/dbconnect.php');
require('../include/session.php');
require('../include/constants.php');

if( $session->logged_in && ( $session->userlevel == DBUG_USER_LEVEL
         || $session->isAdmin() ) ){
   if( !empty($_POST) ){
      // Already submitted
      $sid;
      if( strcmp( $_POST['group'], 'new' ) == 0 ){
         $query = "INSERT INTO `Group` (`SysID`, `Class_SysID`) VALUES (NULL, '16')";
         $result = mysql_query( $query );
         if( $result ){
            $sid = mysql_insert_id();
         }
      }else{
         $sid = $_POST['group'];
      }

      $students = "";

      foreach( $_POST['list'] as $i ){
         $students .= " ('$sid', '$i'),";
      }
      $students = substr($students,0,-1);

      $query = 'INSERT INTO GroupMap (Group_SysID, Student_SysID) VALUES'.$students;
      $result = mysql_query( $query );

      echo "Added<br>[<a href='admin.php'>Home</a>]";
   }else{
      // Display page for editing groups
      // Page headers
      echo "<title>" . SYSNAME . " Group Management</title>";
      echo "<h3>Group Management</h3>";
      echo "[<a href='admin.php'>Admin Center</a>]<br><br>";


      $query = 'SELECT Student.SysID, Student.FirstName, Student.LastName FROM Student ORDER BY LastName';
      $results = mysql_query( $query );
      $students = array();
      while( ($data = mysql_fetch_array( $results )) != NULL ){
         $students[$data[SysID]] = $data[LastName] . ', ' . $data[FirstName];
      }
      echo '<form method="post" action="group.php">';

      echo 'Students:<br><select multiple="yes" size="10" name="list[]">';
      foreach( $students as $i => $s ){
         echo "<option value='$i'>$s</option>";
      }
      echo '</select>';

      $query = 'SELECT Student.SysID AS SID, Student.FirstName, Student.LastName, `Group`.SysID AS GID
         FROM `Group` JOIN Student JOIN GroupMap
         WHERE GroupMap.Group_SysID = Group.SysID AND GroupMap.Student_SysID = Student.SysID
         ORDER BY LastName';
      $results = mysql_query( $query );
      $groups = array();
      while( ($data = mysql_fetch_array( $results ) ) != NULL ){
         $groups[$data['GID']][] = array('' . $data['SID'], $data['LastName'] . ', ' . $data['FirstName'] );
      }

      echo '<br>';

      echo '<br>Add to:<br><br>Groups:<br>
         <table><tr><td style="vertical-align:top">
         <select size="10" name="group">';
      echo "<option onclick='document.getElementById(\"students\").innerHTML = \"\"' value='new'>New</option>";
      $count = 0;
      foreach( $groups as $g => $i ){
         echo "<option onclick='showStu($count)' value='$g'>$g</option>";
         ++$count;
      }
      echo '</select></td><td style="vertical-align:top"><div id="students"></div></td></tr></table>';

      echo '<input type="submit" value="Submit" /></form>';

      // Javascript magic
      // Javascript will update the 'students' div to show students in that group
      echo '<script type="text/javascript">
         var students = new Array(';
      foreach( $groups as $g => $_i ){
         echo 'new Array(';
         foreach( $_i as $n ){
            echo "\"$n[1]\",";
         }
         echo "\"\"),";
      }
      echo 'new Array());
         function showStu(group){
            var list = "";
            for( var i = 0; i < students[group].length; ++i ){
               list += students[group][i] + "<br>";
            }
            document.getElementById("students").innerHTML = list;
         }';
      echo '</script>';
      // End Javascript
   }
}else{
   echo "You can't do that. [<a href='admin.php'>Home</a>]";
}
