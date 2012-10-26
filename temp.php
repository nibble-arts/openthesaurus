<html>
<head><TITLE></TITLE></head>

<body>

<h1>Transfer Parents to new table</h1>
<?PHP
include "liboutput.php";
include "libdatabase.php";

// connect database
$daba = new database();
$daba->connect("localhost","iggmp","1s87J37r0");
$daba->select("thesaurus");

$res = mysql_query("SELECT * FROM entry where descriptor='0'");
$tempArray = fetch_to_array($res,"");

echo "Anzahl: " . count($tempArray) . "<hr>";

foreach($tempArray as $entry)
{
  echoall($entry);
  database::query("UPDATE entry SET status='1' WHERE ID='" . $entry[ID] . "'");
  echo "<hr>";
}

?>


</body></html>