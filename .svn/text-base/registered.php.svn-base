<?php
require( 'resources/httpsonly.php' );
session_name( 'webcurvesLogin' );
session_set_cookie_params( 2*7*24*60*60 );
session_start();
?>

<head>
<title>Registered users only</title>
</head>

<body>
<?php
if($_SESSION['id']){
   ?>
   Hello, <?php echo $_SESSION['usr'];?>!<br>
   You are among the elite. Only people like you have the magnificent privilege of viewing this page.
   Congratulations!<br><br>
   <a href="members.php">Go Back</a><br>
   <?php
}else{
   ?>
   We don't like people like you. Please go away.
   <?php
}
?>
</div>


</body>
