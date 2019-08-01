<?
/**
 * Main.php
 *
 * This is an example of the main page of a website. Here
 * users will be able to login. However, like on most sites
 * the login form doesn't just have to be on the main page,
 * but re-appear on subsequent pages, depending on whether
 * the user has logged in or not.
 *
 * Written by: Jpmaster77 a.k.a. The Grandmaster of C++ (GMC)
 * Last Updated: August 26, 2004
 */
include("include/session.php");
include("include/service.php");
require('include/httpsonly.php');
?>

<html>
<title>RAWR Home</title>
<body>

<table>
<tr><td>


<?
/**
 * User has already logged in, so display relavent links, including
 * a link to the admin center if the user is an administrator.
 */
if($session->logged_in){
   if( $session->isStudent() )
      echo "<h1>Welcome, " . getName($session->id) . "</h1>";
   if( $session->isProf() )
      echo "<h1>Welcome, " . getPName($session->id) . "</h1>";

   echo ""
       ."[<a href=\"userinfo.php?user=$session->username\">My Account</a>] &nbsp;&nbsp;"
       ."[<a href=\"useredit.php\">Change Password</a>] &nbsp;&nbsp;";
   if($session->isAdmin()){
      echo "[<a href=\"admin/admin.php\">Admin Center</a>] &nbsp;&nbsp;";
   }
   echo "[<a href=\"process.php\">Logout</a>]";
   if( $session->isDbug() ){
      echo " &nbsp;&nbsp;[<a title='Reset survey tasks' href=\"reset.php\">Reset</a>]";
      echo " &nbsp;&nbsp;[<a title='Clear all responses' href=\"clear.php\">Clear</a>]";
   }

   echo "<br><br>";

   if( $session->isProf() ){
      // List instructor's classes
      echo '<div>';
      echo "<b>Your Classes:</b><br>";
      echo '<table border=0 cellspacing=0 cellpadding=0>';
      $query = "SELECT FROM Class JOIN Course";
      $classes = getCurrentCourses( $session->id );
      foreach( $classes as $class ){
         echo "<tr><td>$class[Name]</td>".
              "<td style='padding-left:10px'>$class[ID]</td>".
              "<td style='padding-left:10px'>$class[CNUM]</td>".
              "<td style='padding-left:10px'><a href='participation.php?class=$class[SysID]'>Completion</a></td>".
              "<td style='padding-left:10px'><a href='responses.php?class=$class[SysID]'>Responses</a></td>".
              "<td style='padding-left:10px'><a href='edit.php?class=$class[SysID]'>Edit</a></td></tr>";
      }
      echo "<tr><td><span style='font-size:small;'>[<a href='roster.php'>Add</a>]</span></td></tr>";
      echo '</table>';
   }

   if( $session->isResearch() ){
      // List other current classes
      $classes = getActiveCourses( $session->id );
      if( count( $classes ) > 0 ){
         echo "<br><div>";
         echo '<b>Other Active Classes:</b><br>';
         echo '<table border=0 cellspacing=0 cellpadding=0>';
         foreach( $classes as $class ){
            echo "<tr><td>$class[Name]</td>".
                 "<td style='padding-left:10px'>$class[ID]</td>".
                 "<td style='padding-left:10px'>$class[CNUM]</td>".
                 "<td style='padding-left:10px'><a href='participation.php?class=$class[SysID]'>Completion</a></td>".
                 "<td style='padding-left:10px'><a href='responses.php?class=$class[SysID]'>Responses</a></td>".
//                 "<td style='padding-left:10px'><a href='edit.php?class=$class[SysID]'>Edit</a></td>".
                 "</tr>";
         }
         echo '</table>';
      }

      // List other past classes
      $classes = getPastCourses( $session->id );
      if( count( $classes ) > 0 ){
         echo "<br><div>";
         echo '<b>Other Past Classes:</b><br>';
         echo '<table border=0 cellspacing=0 cellpadding=0>';
         foreach( $classes as $class ){
            echo "<tr><td>$class[Name]</td>".
                 "<td style='padding-left:10px'>$class[ID]</td>".
                 "<td style='padding-left:10px'>$class[CNUM]</td>".
                 "<td style='padding-left:10px'><a href='participation.php?class=$class[SysID]'>Completion</a></td>".
                 "<td style='padding-left:10px'><a href='responses.php?class=$class[SysID]'>Responses</a></td>".
//                 "<td style='padding-left:10px'><a href='edit.php?class=$class[SysID]'>Edit</a></td>".
                 "</tr>";
         }
         echo '</table>';
      }
   }
  
   if( $session->isStudent() ){
      // Check if this student needs to take any of the pre-tests
      $query = "SELECT UpdateInfo, UpdateConsent, ShowInstructions ".
               "FROM Student Where SysID = " . $session->userinfo['Student_SysID'];
      $result = mysql_query($query);
      $data = mysql_fetch_array($result);
      
      // If any of them are needed, display a link to the first one
      if( strcmp( $data['ShowInstructions'], "1" ) == 0 ){
         echo "<b>Attention Needed:</b><br>";
         echo "<a href='instructions.php'>Please read before continuing</a><br>";
      }else if( strcmp( $data['UpdateConsent'], "1" ) == 0 ){
         echo "<b>Attention Needed:</b><br>";
         echo "<a href='consent.php'>Allow / Deny Consent</a><br>";
      }else if( strcmp( $data['UpdateInfo'], "1" ) == 0 ){
      	echo("<b>You need to take the demographics survey.</b><br>".
              "<a href=\"demographics.php\" target=\"_self\">Begin Survey</a><br>");
      }else{
         // All pre-tests are taken, display regular tasks
         $tasks = getCurrentTasks( $session->userinfo['Student_SysID'] );
         if( count($tasks) == 1 )
            echo("<b>You have 1 Task to complete:</b><br>");
         else{
            if( count($tasks) != 0 )
               echo ":";
            echo "</b><br>";
         }
   
         // Display each task
         foreach( $tasks as $id ){
            if( $id[4] == NULL ){
               echo "Begin <a href='quizme.php?task=$id[3]'>Task</a> for $id[1] &nbsp (due $id[2])<br>";
            }else{
               echo "Begin <a href='quizme.php?task=$id[3]'>Task</a> for $id[1] ".
                    "&nbsp (<span style='color:red'>time remaining: $id[4]</span>)<br>";
            }
         }
      }
   }
}else{
   ?>

   <h1>Login</h1>
   <?
   /**
    * User not logged in, display the login form.
    * If user has already tried to login, but errors were
    * found, display the total number of errors.
    * If errors occurred, they will be displayed.
    */
   if($form->num_errors > 0){
      echo "<font size=\"2\" color=\"#ff0000\">".$form->num_errors." error(s) found</font>";
   }
   ?>
   <form action="process.php" method="POST">
   <table align="left" border="0" cellspacing="0" cellpadding="3">
   <tr><td>Username:</td><td><input type="text" name="user" maxlength="30" value="<? echo $form->value("user"); ?>"></td><td><? echo $form->error("user"); ?></td></tr>
   <tr><td>Password:</td><td><input type="password" name="pass" maxlength="30" value="<? echo $form->value("pass"); ?>"></td><td><? echo $form->error("pass"); ?></td></tr>
   <tr><td colspan="2" align="left"><input type="checkbox" name="remember" <? if($form->value("remember") != ""){ echo "checked"; } ?>>
   <font size="2">Remember me next time &nbsp;&nbsp;&nbsp;&nbsp;
   <input type="hidden" name="sublogin" value="1">
   <input type="submit" value="Login"></td></tr>
   <tr><td colspan="2" align="left"><br><font size="2">[<a href="forgotpass.php">Forgot Password?</a>]</font></td><td align="right"></td></tr>
   <?php //<tr><td colspan="2" align="left"><br>Not registered? <a href="register.php">Sign-Up!</a></td></tr> ?>
   </table>
   </form>

   <?
}

if( false ){
   // Don't want this right now
   /**
    * Just a little page footer, tells how many registered members
    * there are, how many users currently logged in and viewing site,
    * and how many guests viewing site. Active users are displayed,
    * with link to their user information.
    */
   echo "</td></tr><tr><td align=\"center\"><br><br>";
   echo "<b>Member Total:</b> ".$database->getNumMembers()."<br>";
   echo "There are $database->num_active_users registered members and ";
   echo "$database->num_active_guests guests viewing the site.<br><br>";

   include("include/view_active.php");
}

?>


</td></tr>
</table>


</body>
</html>
