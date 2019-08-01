<?
# -------- Non Working Code For SQL generation
/*
require('include/dbconnect.php');
require('include/constants.php');
require('include/session.php');
require('include/service.php');
connect();

$resultChart = mysql_query("SELECT `GroupTask`.Start_Date, `Answer`.Answer, COUNT( * ) 
  FROM `Answer` 
    JOIN `GroupTask` ON `GroupTask`.SysID = `Answer`.GroupTask_SysID
      WHERE Question_SysID =42
        AND `GroupTask`.Start_Date > '2010-09-05'
          GROUP BY `GroupTask`.Start_Date, `Answer`.Answer
            ORDER BY `GroupTask`.Start_Date");
 
$values=array();
 
while($row = mysql_fetch_array($resultChart))
{
  $key=$row['Start_Date'];
  $values[$key] = (number_format($row['COUNT( * )'],1));
}

*/
require('include/dbconnect.php');
require('include/constants.php');
require('include/session.php');
require('include/service.php');
connect();

$question = 42;
if( isset($_GET['q']) && $_GET['q'] != "" ){
   $ques = $_GET['q'];
   if( is_numeric( $ques ) )
      $question = $ques;
}

$resultChart = mysql_query(
 "SELECT `GroupTask`.Start_Date, `Answer`.Answer, COUNT( * ) 
  FROM `Answer`, `GroupTask`
  WHERE Question_SysID =$question
   AND `GroupTask`.SysID = `Answer`.GroupTask_SysID
   AND `GroupTask`.Start_Date > '2010-09-05'
   AND `Answer`.IRB_OK=1
  GROUP BY `GroupTask`.Start_Date, `Answer`.Answer
  ORDER BY `GroupTask`.Start_Date, `Answer`.Answer");
 
$values=array();
$vals = array();
$anss = array();
$dates= array();

while( ( $row = mysql_fetch_array( $resultChart ) ) != NULL ){
   $key = $row['Start_Date'];
   $values[$key] = $row["COUNT( * )"];
   $val = trim( $row['Answer'] );
   if( strcmp( $val, "" ) == 0 )
      $val = "---";

   if( !isset( $vals[$val] ) ){
      $vals[$val] = array();
      $anss[] = $val;
   }
   $vals[$val]["".$key] = $row['COUNT( * )'];

   if( !isset( $dates[$key] ) )
      $dates[$key] = $key;
   //echo "$key. $values[$key]";
}

// Padding
foreach( $vals as &$answer ){
   $temp = array();
   foreach( $dates as $date ){
      if( !isset( $answer[$date] ) )
         $temp[$date] = 0;
      else
         $temp[$date] = $answer[$date];
   }
   $answer = $temp;
}
/*
echo "Answers Per Week:<PRE>" . htmlentities( print_r( $vals, true ) ) . "</PRE>";
$values = end($vals);
echo "Answers:<PRE>" . htmlentities( print_r( $anss, true ) ) . "</PRE>";
echo "Dates:<PRE>" . htmlentities( print_r( $dates, true ) ) . "</PRE>";
echo "[2]:<PRE>" . htmlentities( print_r( $vals[$anss[2]], true ) ) . "</PRE>";
die();
/**/

/*
	# ------- The graph values in the form of associative array
	$values=array(
		"Jan" => 110,
		"Feb" => 130,
		"Mar" => 215,
		"Apr" => 81,
		"May" => 310,
		"Jun" => 110,
		"Jul" => 190,
		"Aug" => 175,
		"Sep" => 390,
		"Oct" => 286,
		"Nov" => 150,
		"Dec" => 196
	);
*/
 
	$bar_width=20;
   $bar_space=15;
	$margins=20;

	$img_width=$margins*2 + ($bar_width*count($anss)*count($dates)) + ($bar_space*count($dates)) + $bar_space;
	$img_height=300 + 2*$margins; 

 
	# ---- Find the size of graph by substracting the size of borders
	$graph_width=$img_width - $margins * 2;
	$graph_height=$img_height - $margins * 2; 
	$img=imagecreate($img_width,$img_height + 100);

 
	$total_bars=count($values) * count($anss);
   $gap=$bar_space;
 
	# -------  Define Colors ----------------
   $text_color=imagecolorallocate($img,0,0,0);
	$background_color=imagecolorallocate($img,240,240,255);
	$border_color=imagecolorallocate($img,200,200,200);
	$line_color=imagecolorallocate($img,220,220,220);
   $bar_colors = array();
   for($i = 0; $i < count($anss); $i++ ){
      $bar_colors[] = imagecolorallocate($img,rand(0,255),rand(0,255),rand(0,255));
   }
 
	# ------ Create the border around the graph ------

	imagefilledrectangle($img,1,1,$img_width-2,$img_height-2+100,$border_color);
	imagefilledrectangle($img,$margins,$margins,$img_width-1-$margins,$img_height-1-$margins,$background_color);

 
	# ------- Max value is required to adjust the scale	-------
   $max = 0;
   foreach( $vals as &$answer ){
      foreach( $answer as $count ){
         if( $count > $max ){
            $max = $count;
         }
      }
   }
	$max_value=$max;
	$ratio= $graph_height/$max_value;

 
	# -------- Create scale and draw horizontal lines  --------
	$horizontal_lines=20;
	$horizontal_gap=$graph_height/$horizontal_lines;

	for($i=1;$i<=$horizontal_lines;$i++){
		$y=$img_height - $margins - $horizontal_gap * $i ;
		imageline($img,$margins,$y,$img_width-$margins,$y,$line_color);
		$v=intval($horizontal_gap * $i /$ratio);
		imagestring($img,0,5,$y-5,$v,$text_color);

	}

	# ----------- Draw the bars here ------
   $i = 0;
   foreach( $dates as $date ){
      $x1 = $margins + $gap + ($i * $gap) + (($i*count($anss))*$bar_width);
      $x2 = $x1 + $bar_width - 1;
      $y2 = $img_height - $margins;
		imagestring($img,0,$x1+3,$img_height-15,$date,$text_color);		// Display Date
      $j = 0;
      foreach( $vals as &$answer ){
         $y1 = $margins + $graph_height - intval($answer[$date] * $ratio);
         imagestring($img,0,$x1+3,$y1-10,$answer[$date],$text_color);
         imagefilledrectangle($img,$x1,$y1,$x2,$y2,$bar_colors[$j]);
         $x1 += $bar_width;
         $x2 += $bar_width;
         $j++;
      }
      $i++;
   }

   # ------ Draw Key ------
   $i = 0;
   $y1 = $img_height + 10;
   $x1 = 1 + $margins;
   foreach( $anss as $ans ){
      imagefilledrectangle( $img, $x1, $y1, $x1 + 20, $y1 + 20, $bar_colors[$i] );
      imagestring( $img, 0, $x1 + 25, $y1 + 7, $ans, $text_color );
      $y1 += 30;
      $i++;
      if( $i % 3 == 0 ){
         $y1 = $img_height + 10;
         $x1 += 200;
      }
   }

	header("Content-type:image/png");
	imagepng($img);
   
?>


