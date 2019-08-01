<?php
require('resources/dbconnect.php');
require('httpsonly.php');
require('include/constants.php');
require('include/session.php');

// Temporary, all majors are here in order by department
      $depts = array( "B. Thomas Golisano College of Computing and Information Sciences", "College of Applied Science and Technology", "College of Imaging Arts and Sciences", "College of Liberal Arts", "College of Science", "E. Philip Saunders College of Business", "Interdisciplinary", "Kate Gleason College of Engineering", "National Technical Institute for the Deaf", "Other" );
      $mids = array(
         array(12,1,13,14,15,16,2,17,18,19),
         array(20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35),
         array(36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58),
         array(59,60,61,62,63,64,65,66,67,68,69,70,71,72),
         array(73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,3,90,91,92,93),
         array(94,95,96,97,4,98,99),
         array(100),
         array(101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121),
         array(122,123),
         array(-1,-1)
      );
      $majors_list = array( 
         array( "Applied Networking & Systems Administration (VNSA)", "Computer Science (VCSG)", "Computing Exploration (VGCU)", "Game Design and Development (VIGD)", "Informatics Exploration (VGIU)", 'Information Security and Forensics (VNSF)', 'Information Technology (VKSF)', 'Medical Informatics (VKSM)', 'New Media Interactive Development (VIGN)', 'Software Engineering (VSEN)' ),
            array( 'Civil Engineering Technology (ITFC)', 'Computer Engineering Technology (ITFP)', 'Electrical / Mechanical Engineering Technology (freshmen applicants only) (ITFS)', 'Electrical / Mechanical Engineering Technology (transfer applicants only) (ITFL)', 'Electrical Engineering Technology (ITFE)', 'Manufacturing Engineering Technology (ITFF)', 'Mechanical Engineering Technology (ITFM)', 'Telecommunications Engineering Technology (ITFT)', 'Undeclared Engineering Technology (ITFZ)', 'Environmental Sustainability (IEME)', 'Safety Technology (IEMS)', 'Applied Arts & Sciences (transfer applicants only) (IMDF)', 'Packaging Science (IPKT)', 'Hospitality and Service Management (ISMK)', 'Nutrition Management (ISMD)', 'Undeclared Hospitality & Service Management (ISMU)' ),
            array( 'Ceramics & Ceramic Sculpture (JSCC)', 'Glass & Glass Sculpture (JSCG)', 'Metals & Jewelry Design (JSCM)', 'Undeclared Crafts (JSCU)', 'Woodworking & Furniture Design (JSCW)', 'Woodworking - AOS (JSCA)', 'Fine Arts Studio (JADF)', 'Illustration (JADL)', 'Medical Illustration (JADM)', '3D Digital Graphics (JADQ)', 'Graphic Design (JADC)', 'Industrial Design (JADU)', 'Interior Design (JADI)', 'New Media Design & Imaging (JADW)', 'Digital Cinema (JPHF)', 'Film/Video/Animation (JPHQ)', 'Advertising Photography (JPHD)', 'Biomedical Photographic Communications (JPHB)', 'Fine Art Photography (JPHA)', 'Imaging & Photographic Technology (JPHT)', 'Photojournalism (JPHR)', 'New Media Publishing (JPRW)', 'Undeclared Art / Design (JADA)' ),
            array( 'Advertising & Public Relations (GPTA)', 'Criminal Justice (GCJC)', 'Cultural Resource Studies (GCRS)', 'Economics (GECN)', 'International Studies (GSSI)', 'Journalism (GPTJ)', 'Liberal Arts Exploration (undeclared Liberal Arts) (GLAU)', 'Philosophy (GPIL)', 'Political Science (GPLS)', 'Pre-Law Studies (GPLW)', 'Professional & Technical Communication (GPTC)', 'Psychology (GPSY)', 'Public Policy (GPPD)', 'Urban and Community Studies (GUCS)' ),
            array( 'Applied Mathematics (SMAM)', 'Applied Statistics (SMAS)', 'Biochemistry (SCHB)', 'Bioinformatics (SBIC)', 'Biology (SBIB)', 'Biomedical Sciences (SSBS)', 'Biotechnology (SBIT)', 'Biotechnology - Bioinformatics Option (SBIF)', 'Chemistry (SCHC)', 'Chemistry - Environmental Option (SCHW)', 'Computational Mathematics (SMAC)', 'Diagnostic Medical Sonography (Ultrasound) - BS (SCLS)', 'Diagnostic Medical Sonography (Ultrasound) - Certificate (SCLT)', 'Environmental Science (SBIV)', 'General Science Exploration (undeclared Science) (SSEG)', 'Imaging Science (SIMG)', 'Physician Assistant (SCLP)', 'Physics (SPSP)', 'Polymer Chemistry (SCHM)', 'Predentistry Studies (SPDT)', 'Premedical Studies (SPMD)', 'Preveterinary Studies (SPVT)' ),
            array( 'Accounting (BBUA)', 'Finance (BBUF)', 'International Business (BBUT)', 'Management (BBUG)', 'Management Information Systems (BBUI)', 'Marketing (BBUM)', 'New Media Marketing (BREP)' ),
            array( 'University Studies (WUSP)' ),
            array( 'Biomedical Engineering (EBME)', 'Chemical Engineering (ECME)', 'Computer Engineering (EECC)', 'Computer Engineering - Software Engineering Option (EECS)', 'Electrical Engineering (EEEE)', 'Electrical Engineering - Biomedical Engineering Option (EEEM)', 'Electrical Engineering - Computer Engineering Option (EEEC)', 'Electrical Engineering - Robotics Option (EEER)', 'Engineering Exploration (Undeclared Engineering) (EENG)', 'Industrial & Systems Engineering (EIEI)', 'Industrial & Systems Engineering - Ergonomics Option (EIEE)', 'Industrial & Systems Engineering - Information Systems Option (EIEY)', 'Industrial & Systems Engineering - Manufacturing Option (EIEN)', 'Industrial & Systems Engineering- Information Systems Option (EIEK)', 'Industrial & Systems Engineering- Lean Six Sigma Option (EIEL)', 'Mechanical Engineering (EMEM)', 'Mechanical Engineering - Aerospace Engineering Option (EMEA)', 'Mechanical Engineering - Automotive Engineering Option (EMEV)', 'Mechanical Engineering - Bioengineering Option (EMED)', 'Mechanical Engineering - Energy & the Environment Option (EMEE)', 'Microelectronic Engineering (EMCR)' ),
            array( 'ASL / English Interpretation - BS degree (NITF)', 'Associate Degree Programs for the deaf and hard-of-hearing (NITA)'),
            array( 'Other' )
            );


if( $session->logged_in != 1 ){
  /* ?>You are not logged in. Please log in. <a href="index.php"><?php echo SYSTEM;?></a><?php
  */
  // Not logged in, redirect so they can log in
  header( 'Location: index.php' );

}else{
   // Logged in, connect to MySQL DB
   connect();

   $errors = array(); // Stores any error messages
   $e_index = 0;

   // Check for post data
   if( !empty($_POST) ){

      // List of fields needed
      $fields = array( "gender", "major", "year", "highmath", "curmath", "highphys", "hsphys" );
      $matches = 0;
      $i = 0;
      
      // Check if each field was defined in the POST
      foreach( $_POST as $q => $a ){
         if( strcmp( $q, $fields[$i++] ) == 0 ){
            if( $a != NULL && strcmp( $a, "" ) != 0 )
               ++$matches;
            else
               $errors[$e_index] = "Not all fields were answered";
         }else{
            $errors[$e_index] = "Not all fields were answered";
         }
         if( sizeof($errors) != 0 ){
            break;
         }
      }

      // Print any error messages from parsing through fields
      if( count( $errors ) > 0 ){
         echo "<span style='color:red;font-size:large;font-weight:bold'>";
         foreach( $errors as $err )
            echo "$err<br>";
         echo "</span>";
      }

      // Check that the right number of fields was submitted
      if( $matches == sizeof( $fields ) ){
         // Read in all fields

         $d_id = -1;

         // Get user gender
         $gender = 1;
         if( strcmp( $_POST['gender'], "Female" ) == 0 )
            $gender = 0;
         else if( strcmp( $_POST['gender'], "Male" ) == 0 )
            $gender = 1;

         // Get user major
         $major = $_POST['major'];

         // Get user year level
         $year = $_POST['year'];

         // Get highest math course
         $pmath = $_POST['highmath'];

         // Get prior physics, default to none
         // Priority: college, then highschool, then none
         $pphys = "NULL";
         if( strcmp( $_POST['highphys'], "-1" ) == 0 ){
            if( strcmp( $_POST['hsphys'], "-1" ) != 0 ){
               $pphys = "'$_POST[hsphys]'";
            }
         }else{
            $pphys = "$_POST[highphys]";
         }

         // Get current math course
         $cmath = "NULL";
         if( strcmp( $_POST['curmath'], "-1" ) != 0 ){
            $cmath = "$_POST[curmath]";
         }

         // Get current physics course from student profile
         $cphys = "SELECT CurPhys FROM Student WHERE SysID=" . $session->userinfo['Student_SysID'];
         $cphys = mysql_query($cphys);
         $cphys = mysql_fetch_array($cphys);
         $cphys = $cphys['CurPhys'];
         if( $cphys == NULL )
            $cphys = "NULL";

         // Find if there is already a demographics entry which matches
         $query = "SELECT SysID FROM Demographics WHERE Gender=$gender AND Major=$major".
                  " AND PriorPhys" . (strcmp($pphys,"NULL")==0 ? " IS NULL" : "=$pphys").
                  " AND PriorMath" . (strcmp($pmath,"NULL")==0 ? " IS NULL" : "=$pmath").
                  " AND CurPhys" . (strcmp($cphys,"NULL")==0 ? " IS NULL" : "=$cphys").
                  " AND CurMath" . (strcmp($cmath,"NULL")==0 ? " IS NULL" : "=$cmath").
                  " AND Year=$year";
         if( DEBUG )
            echo "Find Demo SysID:<br>$query<br>";
         $result = mysql_query( $query );

         // If there wasn't one, make one
         if( mysql_num_rows( $result ) == 0 ){
            $insert = "INSERT INTO Demographics (Gender, Major, PriorPhys, PriorMath, CurPhys, CurMath, Year) ".
                      "VALUES ($gender, $major, $pphys, $pmath, $cphys, $cmath, $year)";
            if( DEBUG )
               echo "Insert Demographic:<br>$insert<br>";
            $result = mysql_query( $insert );
            $result = mysql_query( $query );
         }

         // Record demographics sysid
         $data = mysql_fetch_array( $result );
         $d_id = $data['SysID'];

         // Set student profile to have this demographics
         $query = "UPDATE Student SET Demographics_SysID=$d_id, UpdateInfo=0 WHERE SysID=".$session->userinfo['Student_SysID'];
         $result = mysql_query( $query );
         
         if( DEBUG )
            echo "Update: $result<br>";

         /*
         // Initial Error Checking Stuff
         print("<pre>");
         print_r($_POST);
         print("</pre>");

         $gender = $_POST[gender];
         if( strcmp( $gender, "Male" ) != 0 && strcmp( $gender, "Female" ) != 0 ){
         $gender = "Other";
         }
         $query = "SELECT Name FROM Major WHERE SysID = $_POST[major]";
         $result = mysql_query( $query );
         $data = mysql_fetch_array( $result );
         echo "Your major is $data[Name]<br>";

         echo "You are year level $_POST[year]<br>";

         $query = "SELECT Name FROM Course WHERE SysID = $_POST[highmath]";
         $result = mysql_query( $query );
         $data = mysql_fetch_array( $result );
         echo "Your highest level math course was $data[Name]<br>";

         if( strcmp( $_POST[curmath], "-1" ) != 0 ){
         $query = "SELECT Name FROM Course WHERE SysID = $_POST[curmath]";
         $result = mysql_query( $query );
         $data = mysql_fetch_array( $result );
         echo "You are currently taking $data[Name]<br>";
         }else
         echo "You are not enrolled in a math course<br>";
          */
         //
         // Format SQL Update Query
         //
/*         $query = "UPDATE Student SET Gender='";
         if( strcmp( $_POST[gender], "Male" ) || strcmp( $_POST[gender], "Female" ) ){
            $query .= $_POST[gender];
         }else{
            $query .= "Other";
         }
         $query .= "', Year='$_POST[year]', HighMath=$_POST[highmath], CurMath=";
         if( strcmp( $_POST[curmath], "-1" ) == 0 ){
            $query .= "NULL";
         }else{
            $query .= "'$_POST[curmath]'";
         }
         $query .= ", HighPhys=";
         if( strcmp( $_POST[highphys], "-1" ) == 0 ){
            if( strcmp( $_POST[hsphys], "-1" ) == 0 ){
               $query .= "NULL";
            }else{
               $query .= "'$_POST[hsphys]'";
            }
         }else{
            $query .= "'$_POST[highphys]'";
         }
         if( strcmp( $_POST[hsphys], "-1" ) != 0 ){
            $hs_ins = "INSERT INTO Schedule ( Student_SysID, Class_SysID ) VALUES ( ".
               $session->userinfo[Student_SysID] . ", $_POST[hsphys] )";
            if( DEBUG )
               echo "$hs_ins<br>";
            mysql_query( $hs_ins );
         }

         $query .= ", UpdateInfo='0' WHERE Student.SysID = " . $session->userinfo[id];
         if( DEBUG )
            echo "$query<br>";*/

//         echo "Thank You. <a href=\"index.php\">Return to " . SYSTEM . "</a>.";
         mysql_query( $query );
         // Demographics completed, forward to home
         header( 'Location: index.php' );
      }
   }

   // Display demographics fields on first load or if ther were errors on their submit
   if( empty($_POST) || !empty($errors) ){
      $query = "SELECT * FROM Major Where 1";
      $result = mysql_query( $query );
      $majors = array();
      $i = 0;
      while( ($data = mysql_fetch_array( $result ) ) != NULL ){
         if( strcmp( $data['Name'], MISC_MAJOR ) != 0 )
            $majors[$i++] = "<option label='$data[Name]' value='$data[SysID]'>$data[Name]</option>";
      }
      sort( $majors );
      array_unshift( $majors, "<option/>" );

      // Start of viewed web page:
      ?><h3>Please fill out the following information:</h3>
      <form name="demographics" method="post">What is your gender?<br>
      <input type="radio" name="gender" value="Male">Male</input>
      <input type="radio" name="gender" value="Female">Female</input><br>
      What is your major or intended major?<br>
      <table><tr>
      <td>College:</td>
      <td><select id="col_list" onchange="majorlist(document.getElementById('col_list').selectedIndex-1)">
      <?php
         // Add colleges from predefined lists at top of file
         $count = 0;
         echo "<option onclick='clear()'></option>";
         foreach( $depts as $d ){
            echo "<option onclick='majorlist($count)'>$d</option>";
            $count++;
         }
         ?>
         </select></td></tr>
         <tr><td>Major:</td><td><select id='major'  name='major'></select><br>
      <?php
         // Display majors from mysql query
//         foreach( $majors as $i ){
//            echo $i;
//         }
   ?>
      </select></td></tr></table>
      What year level are you?<br>
      <select name="year">
      <option></option>
      <option label=1 value=1>1</option>
      <option label=2 value=2>2</option>
      <option label=3 value=3>3</option>
      <option label=4 value=4>4+</option>
      </select><br>
      What is the highest level math course you have credit for?<br><select name="highmath">
      <option/>
      <option value=31>1016-261 Calculus Foundations I</option>
      <option value=32>1016-262 Calculus Foundations II</option>
      <option value=20>1016-271 Calculus A</option>
      <option value=21>1016-272 Calculus B</option>
      <option value=22>1016-273 Calculus C</option>
      <option value=17>1016-281 Project-based Calculus I</option>
      <option value=18>1016-282 Project-based Calculus II</option>
      <option value=19>1016-283 Project-based Calculus III</option>
      <option value=33>1016-304 Diff Eq For Eng Tech</option>
      <option value=25>1016-305 Multivariable Calculus</option>
      <option value=23>1016-306 Differential Equations I</option>
      <option value=35>1016-307 Differential Equations II</option>
      </select><br>
      What, if any, math course are you currently enrolled in?<br><select name="curmath">
      <option/>
      <option value=-1>None</option>
      <option value=31>1016-261 Calculus Foundations I</option>
      <option value=32>1016-262 Calculus Foundations II</option>
      <option value=20>1016-271 Calculus A</option>
      <option value=21>1016-272 Calculus B</option>
      <option value=22>1016-273 Calculus C</option>
      <option value=17>1016-281 Project-based Calculus I</option>
      <option value=18>1016-282 Project-based Calculus II</option>
      <option value=19>1016-283 Project-based Calculus III</option>
      <option value=33>1016-304 Diff Eq For Eng Tech</option>
      <option value=25>1016-305 Multivariable Calculus</option>
      <option value=23>1016-306 Differential Equations I</option>
      <option value=35>1016-307 Differential Equations II</option>
      <option value=-1>Other</option>
      </select><br>
      What is the highest level physics course you have credit for?<br><select name="highphys">
      <option/>
      <option value=-1>None</option>
      <option value=35>1017-211 College Physics I</option>
      <option value=36>1017-211 College Physics II</option>
      <option value=37>1017-211 College Physics III</option>
      <option value=38>1017-369 University Physics IA</option>
      <option value=1>1017-311 University Physics I</option>
      <option value=39>1017-389 University Physics IIA</option>
      <option value=2>1017-312 University Physics II</option>
      <option value=3>1017-313 University Physics III</option>
      </select><br>
      Which high school level physics course did you take?<br><select name="hsphys">
      <option/>
      <option value=-1>None</option>
      <option value=14>Regular Physics</option>
      <option value=24>Physics Honors</option>
      <option value=16>Physics AP: AB</option>
      <option value=15>Physics AP: BC</option>
      <option value=29>Physics IB</option>
      </select><br><br>
         <button type="submit">Submit</button>
         </form>
<script type='text/javascript'>
var depts = new Array( <?php
$list = "";
foreach( $depts as $d ){
   $list .= "\"$d\",";
}
echo substr( $list, 0, -1 );
?>
);
var majors = new Array( <?php
$list = "";
foreach( $majors_list as $major ){
   $list .= 'new Array( ';
   foreach( $major as $m ){
      $list .= "\"$m\",";
   }
   $list = substr( $list, 0, -1 );
   $list .= '),';
}
echo substr( $list, 0, -1 );
?>);
var mids = new Array( <?php
$list = "";
foreach( $mids as $mi ){
   $list .= 'new Array( ';
   foreach( $mi as $mid ){
      $list .= "$mid,";
   }
   $list = substr( $list, 0, -1 );
   $list .= '),';
}
echo substr( $list, 0, -1 );
?>);

function majorlist( num ){
   if( num === -1 ){
      clear();
   }else{
      var box = document.getElementById( "major" );
      box.options.length = 0;
      if( num != 9 ){
         var option = document.createElement("OPTION");
         option.text = "";
         box.options.add( option );
      }
      for( i = 0; i < majors[num].length; i++ ){
         var option = document.createElement("OPTION");
         option.text = majors[num][i];
         option.value = mids[num][i];
         box.options.add( option );
      }
   }
}

function clear(){
   document.demographics.major.options.length = 0;
}

</script>
         <?php
   }
}
?>
