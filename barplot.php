
<?php
function barplot( $fields, $data, $x, $y, $title, $file ){
   $labels = "";
   foreach( $fields as $v ){
      $labels .= "\"" . $v . "\",";
   }
   $labels = substr( $labels, 0, -1 );
   $values = "";
   foreach( $data as $v ){
      $values .= "" . $v . ",";
   }
   $values = substr( $values, 0, -1 );
//   $fh = fopen("temp.r",'w');
//   fwrite( $fh, "png( file=\"temp.png\" )\nnums <- c($values)\nbarplot(nums)\ndev.off()\ninvisible()\n" );
//   fclose( $fh );
//   $cmd = "echo 'source(\"temp.r\")' | /usr/bin/R --vanilla --slave";
//   passthru( $cmd );
   $cmd = "echo 'ans <- c($values); labs <- c($labels); png( file=\"R/temp$file.png\" ); " .
          "barplot(ans, names.arg=labs, xlab=\"$x\", ylab=\"$y\", main=\"$title\" );' " .
          "| /usr/bin/R --vanilla --slave";
   system($cmd);
    
//   exec($cmd . ' > /dev/null 2>/dev/null &');

//   echo "<img src='temp.png'/>";
//$cmd = "echo 'source(\"ex_2.r\")' | /usr/bin/R --vanilla --slave";
/*
png( file="temp.png" )
pie( rep( 1, 24 ), col = rainbow( 24 ) )
cars <- c(1,3,6,4,9)
barplot( cars )
dev.off()
invisible()



dev.off.wrap <- function(){
   dev.off()
   invisible()
}

png( file="temp.png" )
pie( rep( 1, 24 ), col = rainbow( 24 ) )
dev.off.wrap()
*/

}
$a[0] = "Cabot";
$a[1] = "Kraft";
$a[2] = "Helluva Good";
$b[0] = 7;
$b[1] = 1;
$b[2] = 3;
//barplot( $a, $b, "Companies", "Responses", "Test 1" );
?>
