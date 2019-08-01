#!/usr/bin/php
<?php
require '/var/www/webcurves/include/dbconnect.php';

$user = exec('whoami');
if( strcmp( $user, 'www-data' ) != 0 ){
   $stdin = fopen( 'php://stdin', 'r' );
   $types = array( 1 => 'Teacher', 2 => 'Student' );
   echo "What would you like to add?";
   foreach( $types as $num => $type ){
      echo "\n    $num - $type";
   }
   do{
      echo "\n? ";
      $inp = trim(fgets( $stdin ));
      if( $inp == "q" || $inp == "quit" )
         die();
   }while( $types[$inp] == NULL );

   echo "\nAdd $types[$inp]\n";
   echo "First Name: ";
   $fname = trim(fgets( $stdin ));
   echo "Last Name: ";
   $lname = trim(fgets( $stdin ));
   echo "Email: ";
   $email = trim(fgets( $stdin ));
   $shortname = substr( $email, 0, strpos( $email, '@' ) );
   connect();
   switch( $inp ){
      case 1:
         $query = "INSERT INTO Teacher (`FirstName`, `LastName`, `Email`, `University_SysID`)
                   VALUES ('$fname', '$lname', '$email', 1)";
         mysql_query( $query );
         echo "\n$query\n";
         $id = mysql_insert_id();
         $query = "INSERT INTO Login (`username`, `password`, `email`, `timestamp`, `userlevel`, `Student_SysID`) VALUES
                   ('$shortname', '', '$email', 0, 5, $id )";
         mysql_query( $query );
         echo "\n$query\n";
         break;
      case 2:
         $query = "INSERT INTO Student (`FirstName`, `LastName`, `University_SysID`, `Email` )
                   VALUES ('$fname', '$lname', 1, '$email' )";
         mysql_query( $query );
         echo "\n$query\n";
         $id = mysql_insert_id();
         $query = "INSERT INTO Login (`username`, `password`, `email`, `timestamp`, `userlevel`, `Student_SysID`) VALUES
                   ('$shortname', '', '$email', 0, 1, $id )";
         mysql_query( $query );
         echo "\n$query\n";
         
         break;
   }
}
?>
