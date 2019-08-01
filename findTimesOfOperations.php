<html><head><title>findTimesOfOperations</title></head><body>
<?

require('include/dbconnect.php');
require('include/constants.php');
require('include/session.php');
require('include/service.php');
connect();
/*
$sql  = "SELECT `FirstName`, `LastName` FROM `Student`";
$result = mysql_query($sql) or die(mysql_error());
//$row=mysql_fetch_row($result);

echo "<table align=center width=300>";
echo "<tr><th>FirstName</th><th>LastName</th></tr>\n";
if ($result=mysql_query($sql)) {
  while ($row=mysql_fetch_row($result)) {
    echo "<tr><td>".$row[0]."</td>";
    echo "<td>".$row[1]."</td></tr>\n";
  }
} else {
  echo "<!-- SQL Error ".mysql_error()." -->";
}
echo "</table>";

//$sql2 = "SELECT COUNT(FirstName) AS FirstName FROM Student";
//$result = mysql_query($sql) or die(mysql_error());
*/




$sql = "SELECT `GroupTask`.Start_Date, `Answer`.Answer, COUNT( * ) 
  FROM `Answer` 
    JOIN `GroupTask` ON `GroupTask`.SysID = `Answer`.GroupTask_SysID
      WHERE Question_SysID =42
        AND `GroupTask`.Start_Date > '2010-09-05'
          GROUP BY `GroupTask`.Start_Date, `Answer`.Answer
            ORDER BY `GroupTask`.Start_Date";


$result = mysql_query($sql) or die(mysql_error());




$fields_num = mysql_num_fields($result);

echo "<h1>Table: {$table}</h1>";
echo "<table border='1'><tr>";
// printing table headers
for($i=0; $i<$fields_num; $i++)
{
    $field = mysql_fetch_field($result);
    echo "<td>{$field->name}</td>";
}
echo "</tr>\n";
// printing table rows
while($row = mysql_fetch_row($result))
{
    echo "<tr>";

    // $row is array... foreach( .. ) puts every element
    // of $row to $cell variable
    foreach($row as $cell)
        echo "<td>$cell</td>";

    echo "</tr>\n";

}
mysql_free_result($result);


?>
</body></html>
