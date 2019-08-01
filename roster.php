<?php

require( "include/dbconnect.php" );
require('include/session.php');
// First upload file.


$post = $_POST;
$allow_insert = TRUE; // unused
$uploaded = $_POST['data-upload'];
$course = $_POST['course'];   // Course number
$section = $_POST['section']; // Section number
$sdate = $_POST['start'];     // Start date
$edate = $_POST['end'];       // End date


if( !$session->isProf() ){
   echo "You can't do that. [<a href='index.php'>Home</a>]";
}else{

   if( $uploaded && $course && $section && $sdate && $edate ){
      // A file was uploaded
      $valid = TRUE;

      // Check if section value was actually a number
      $section = trim($section);
      if( is_numeric($section) ){
         $section = (int)$section;
      }else{
         $valid = FALSE;
         echo "Section is not numeric<br>";
      }

      // Split start date object into its parts
      $sdate = trim($sdate);
      $sdate = preg_split( '/\-|\/|\\\\\\\/', $sdate ); // Split date object on - / and \
      if( count($sdate) == 3 && is_numeric($sdate[0]) && is_numeric( $sdate[1] ) && is_numeric($sdate[2]) ){
         $sdate[0] = (int)trim($sdate[0]);
         $sdate[1] = (int)trim($sdate[1]);
         $sdate[2] = (int)trim($sdate[2]);
      }else{
         $valid = FALSE;
         echo "SDate couldn't be split properly<br>";
      }

      // Split end date object into its parts
      $edate = trim($edate);
      $edate = preg_split( '/\-|\/|\\\\\\\/', $edate ); // Split date object on - / and \
      if( count($edate) == 3 && is_numeric($edate[0]) && is_numeric( $edate[1] ) && is_numeric($edate[2]) ){
         $edate[0] = (int)trim($edate[0]);
         $edate[1] = (int)trim($edate[1]);
         $edate[2] = (int)trim($edate[2]);
      }else{
         $valid = FALSE;
         echo "EDate couldn't be split properly<br>";
      }

      // Check if course number was really a number
      $course = trim( $course );
      if( is_numeric( $course ) ){
         $course = (int)$course;
      }else{
         $valid = FALSE;
         echo "Course is not numeric<br>";
      }

      // Check if uploaded file is within limitations
      $tmp = explode('.',$_FILES['file']['name']);
      if( strcmp( $tmp[1], 'csv' ) == 0 && $_FILES['file']['size'] < 1000000 ){

      }else{
         $valid = FALSE;
         echo "Filetype: ".$_FILES['file']['name']."<br>Filesize: ".$_FILES['file']['size']."<br>";
      }


      // Check file type and limit size to 1mb
      if( $valid ){
         if( $_FILES["file"]["error"] > 0 ){
            // Print any error messages
            echo "Return Code: " . $_FILES["file"]["error"] . "<br>";

         }else{
            // File was uploaded successfully and was valid
            // Do stuff:

            $cid = $course;
            $tid = $session->id;

            // Save file on server
            move_uploaded_file( $_FILES["file"]["tmp_name"], "Rosters/" . $_FILES["file"]["name"] );

            // Connect to MySQL DB
            connect();

            // Check if section exists, add if doesn't
            $query = "SELECT SysID
                      FROM Class
                      WHERE Course_SysID = $cid
                       AND ClassNumber='$section'
                       AND Start_Date='$sdate[2]-$sdate[0]-$sdate[1]'
                       AND End_Date='$edate[2]-$edate[0]-$edate[1]'
                       AND Teacher_SysID=$tid";
            $result = mysql_query( $query );
            $data = mysql_fetch_array( $result );

            $clid;
            if( $data != NULL ){
               $clid = $data['SysID'];
            }else{
               $query = "INSERT INTO Class
                         (`SysID`, `Course_SysID`, `Teacher_SysID`, `ClassNumber`, `Start_Date`, `End_Date`)
                         VALUES
                         (NULL, $cid, $tid, $section, '$sdate[2]-$sdate[0]-$sdate[1]',
                          '$edate[2]-$edate[0]-$edate[1]')";
               mysql_query( $query );
               $clid = mysql_insert_id();
            }

            //
            // Parse roster here
            //
            $fieldseparator = ",";
            $lineseparator = "\n";

            $csvfile = ("Rosters/" . $_FILES["file"]["name"]);
            /********************************/
            /* Would you like to add an ampty field at the beginning of these records?
            /* This is useful if you have a table with the first field being an auto_increment integer
            /* and the csv file does not have such as empty field before the records.
            /* Set 1 for yes and 0 for no. ATTENTION: don't set to 1 if you are not sure.
            /* This can dump data in the wrong fields if this extra field does not exist in the table
            /********************************/
            $addauto = 1;
            /********************************/
            /* Would you like to save the mysql queries in a file? If yes set $save to 1.
            /* Permission on the file should be set to 777. Either upload a sample file through ftp and
            /* change the permissions, or execute at the prompt: touch output.sql && chmod 777 output.sql
            /********************************/
            $save = 1;
            $outputfile = "output.sql";
            /********************************/


            if(!file_exists($csvfile)) {
               echo "File not found. Make sure you specified the correct path.\n";
               exit;
            }

            $file = fopen($csvfile,"r");

            if(!$file) {
               echo "Error opening data file.\n";
               exit;
            }

            $size = filesize($csvfile);

            if(!$size) {
               echo "File is empty.\n";
               exit;
            }

            $csvcontent = fread($file,$size);

            fclose($file);

            $lines = 0;
            $queries = "";
            $linearray = array();
            $datas = split( $lineseparator, $csvcontent );
            array_shift( $datas );

            foreach( $datas as $line) {

               $lines++;

               $line = trim($line);

               // Don't parse blank lines
               if( strcmp( $line, "" ) != 0 ){

                  $line = str_replace("\r","",$line); // remove newlines
                  $line = substr( $line, 1, -1 ); // remove first and last quote

                  /************************************
                    This line escapes the special character. remove it if entries are already escaped in the csv file
                   ************************************/
                  $line = str_replace("'","\'",$line);
                  /*************************************/

                  $line = explode('","',$line); // split line on quote comma quote

                  foreach( $line as &$entry ){
                     $entry = trim( $entry );
                  }

                  if( $allow_insert ){
                     //
                     // Insert to Student Table
                     //
                     $query = "SELECT SysID FROM Student WHERE FirstName='$line[0]' AND LastName='$line[1]' AND Email='$line[2]'";
                     $result = mysql_query( $query );
                     $stuid;
                     if( mysql_num_rows( $result ) == 0 ){
                        // Student doesn't exist yet
                        $query = "INSERT INTO Student (FirstName, LastName, Email, University_SysID) VALUES
                           ('$line[0]', '$line[1]', '$line[2]', 1)";
                        $result = mysql_query( $query );
                        $stuid = mysql_insert_id();
                     }else{
                        // Student already exists
                        $data = mysql_fetch_array( $result );
                        $stuid = $data['SysID'];
                     }

                     //
                     // Add to schedule
                     //
                     $query = "SELECT * FROM Schedule WHERE Student_SysID=$stuid AND Class_SysID=$clid";
                     $result = mysql_query( $query );
                     if( mysql_num_rows( $result ) == 0 ){
                        $query = "INSERT INTO Schedule (Student_SysID, Class_SysID) VALUES ($stuid, $clid)";
                        mysql_query( $query );
                     }

                     //
                     // Add to Login
                     //
                     $name = substr( $line[2], 0, strpos( $line[2], '@' ) );
                     $pass = md5( $line[1] );

                     $query = "SELECT * FROM Login WHERE Student_SysID=$stuid";
                     $result = mysql_query( $query );
                     if( mysql_num_rows( $result ) == 0 ){
                        $query = "INSERT INTO Login (username, password, email, userlevel, Student_SysID)
                           VALUES ('$name', '$pass', '$line[2]', 1, $stuid)";
                        mysql_query( $query );
                     }
                  }
               }
            }

            @mysql_close($con);
            header( 'Location: index.php' );
            die();
            echo "Found a total of $lines records in this csv file.\n";



            /* 

            // Example stuff
            // Will read through and print out every line of the file
            echo "<PRE>";
            while( $file != null && !feof( $file ) ){
            $line = fgets( $file );
            echo $line;
            }
            echo "</PRE>";
            // End Example stuff
             */
         }
      }else{
         // redirect back to self?
         header( 'Location: roster.php' );
      }

   }else{
      // No file uploaded yet
      connect();
      $query = "SELECT SysID, Name, Course_ID as CID
         FROM Course
         WHERE University_SysID=1
         ORDER BY Course_ID";
      $result = mysql_query( $query );

      ?>
         <head>
         <title>RAWR Upload Roster</title>
         <script language="javascript" type="text/javascript" src="include/timepicker.js">
         //Date Time Picker script- by TengYong Ng of http://www.rainforestnet.com
         //Script featured on JavaScript Kit (http://www.javascriptkit.com)
         //For this script, visit http://www.javascriptkit.com
         </script>
         </head>
         <h3>Upload Roster:</h3>
         <form enctype="multipart/form-data" action="roster.php" method="post">
         <table>
            <tr>
               <td>Select Course:</td>
               <td><select name="course">
               <?php while( ($data = mysql_fetch_array( $result ) ) != NULL ){
                  echo "<option value='$data[SysID]'>$data[CID] &nbsp; $data[Name]</option>";
               }?>
            </select></td>
            </tr>
            <tr>
               <td>Section Number:</td>
               <td><input name="section" type="text" size=2></td>
            </tr>
            <tr>
               <td>Start Date (mm-dd-yyyy):</td>
               <td><input name="start" id="cal1" type="text" size=10><a href="javascript:NewCal('cal1','mmddyyyy')"><img src="images/cal.gif" style='border:none' alt="Pick a date"></a></td>
            </tr>
            <tr>
               <td>End Date (mm-dd-yyyy):</td>
               <td><input name="end" id="cal2" type="text" size=10><a href="javascript:NewCal('cal2','mmddyyyy')"><img src="images/cal.gif" style='border:none' alt="Pick a date"></a></td>
            </tr>
            <tr>
               <td>CSV File:</td>
               <td><input type="file" value="" name="file"></td>
            </tr>
            <tr>
               <td align="right">[<a href='index.php'>Cancel</a>]</td>
               <td><input type="submit" value="Upload"></td>
            </tr>
         </table>
         <input type="hidden" value="data-upload" name="data-upload">
         <br>
         </form>
      <?php
   }
}
?>
