<?php
function linePlot( $title, $data, $file ){

   $rcmd = "";
   $list = "";
   $label = "";
   $name = "a";
   foreach( $data as $ans => $vals ){
      $rcmd .= "$name <- c(";
      $list .= "$name, ";
      $label .= "\"$ans\", ";
      $name .= 'a';
      foreach( $vals as $num ){
         $rcmd .= "$num,";
      }
      $rcmd = substr( $rcmd, 0, -1 ) . '); ';
   }
   $list = substr( $list, 0, -2 );
   $label = substr( $label, 0, -2 );

   // Colors
   $rcmd .= 'cl = colors(); colr <- c(cl[6], cl[11], cl[16], cl[24], cl[26], cl[32], cl[33], cl[42], cl[44], cl[47], cl[51], cl[62], cl[59], cl[73], cl[84], cl[81], cl[77], cl[83], cl[86],cl[90], cl[95], cl[100], cl[94], cl[36], cl[41] ); ';


   // Min, Max value to display
   // rang <- c(0,30);
   $rcmd .= "rang <- range(0, $list ); ";

   // Save to pic.png
   $rcmd .= "png( file=\"R/pic$file.png\"); ";

   // Make a line plot.
   // Annotations (axis labels) are off
   // Axis labels are off
   // Range limited to what we defined above
   // Color set to fix color index
   // Set dot marker to type 0
   // plot( cars, type="o", ann=FALSE, axes=FALSE, ylim=rang, col=colr[1], pch=0 );
//   echo "'".key(array_slice($data, 0, 1 , TRUE ) )."'";
   $rcmd .= "plot( a, type=\"o\", ann=FALSE, ylim=rang, col=colr[1], pch=0, xlab=\"Week\", ylab=\"Times Answered\", cex.lab=1.4 ); ";

   // Set main title for graph
   $rcmd .= "title(main=\"Responses For $title\", col.main=\"red\", font.main=2); ";

   // X-Axis label
   $rcmd .= "title(xlab=\"Week\", cex.lab=1.4); ";

   // Y-Axis label
   $rcmd .= "title(ylab=\"Times Answered\", cex.lab=1.4 ); ";

   // X-Axis values
//   $rcmd .= "axis(1, at=1:10, lab=1:10)); ";

   // Y-Axis values
   // axis(2, at=c(1,2,3,6));

   // Add a box
   // box();

//   $i = 1;
//   foreach( $data as $ans => $vals ){
   $name = "aa";
   for( $i = 2; $i <= count($data); $i++ ){
      $rcmd .= "lines( $name, type=\"o\", pch=".($i-1).", col=colr[$i] ); ";
      $name .= 'a';
//      $i++;
   }

   // Plot multiples of two
//   lines( twos, type="o", pch=1, col=colr[2] );

   // Plot mult. of three
//   lines( thres, type="o", pch=2, col=colr[3] );

   // 
   $rcmd .= "legend( 1, rang[2], c($label), cex=1.0, pch=0:25, col=colr, lty=1 ); ";
   exec( "echo '$rcmd' | /usr/bin/R --vanilla --slave" );
//   echo $rcmd . "<br>";
//   echo str_replace( "; ", ";<br>", htmlentities($rcmd) );
}
?>
