<?php
function barPlot( $title, $data, $file ){
   $rcmd = "";
   $label = "";
   $bars = "a <- c(";
   foreach( $data as $ans => $vals ){
      $label .= "\"$ans\", ";
      foreach( $vals as $num ){
         $bars .= "$num,";
      }
   }
   $bars = substr( $bars, 0, -1 ) . '); ';
   $list = substr( $list, 0, -2 );
   $label = substr( $label, 0, -2 );

   $rcmd = $bars;

   // Colors
   $rcmd .= 'cl = colors(); colr <- c(cl[6], cl[11], cl[16], cl[24], cl[26], cl[32], cl[33], cl[42], cl[44], cl[47], cl[51], cl[62], cl[59], cl[73], cl[84], cl[81], cl[77], cl[83], cl[86],cl[90], cl[95], cl[100], cl[94], cl[36], cl[41] ); ';


   // Min, Max value to display
   $rcmd .= "rang <- range(0, a ); ";


   // Save to pic.png
   $rcmd .= "png( file=\"R/pic$file.png\"); ";

   // Resize to make room for legend
//   $rcmd .= 'par(xpd=T, mar=par()$mar+c(0,0,0,4)); ';

   // Graph
   $rcmd .= "barplot( a, ann=FALSE, col=colr, cex.lab=1.4, names.arg=c($label) ); ";

   // Set main title for graph
   $rcmd .= "title(main=\"Responses For $title\", col.main=\"red\", font.main=2); ";

   // X-Axis label
   $rcmd .= "title(xlab=\"Answer\", cex.lab=1.4); ";

   // Y-Axis label
   $rcmd .= "title(ylab=\"Occurances\", cex.lab=1.4 ); ";

   // Legend
//   $rcmd .= "legend( ". count($data) .".5, rang[2], c($label), cex=1.0, fill=colr ); ";
   exec( "rm R/pic$file.png > /dev/null 2>&1" );
   exec( "echo '$rcmd' | /usr/bin/R --vanilla --slave > /dev/null 2>&1 &" );
}
?>
