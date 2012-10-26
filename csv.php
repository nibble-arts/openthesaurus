<?PHP
session_start();
include("libstd.php");
$_JS=1;
?>



<html>
<head>
	<TITLE>OpenThesaurus CSV Export</TITLE>

	<link rel='stylesheet' type='text/css' href='default.css'>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">

  <script language="JavaScript" src="libjava.js"></script>
  <script type="text/javascript" src="javascript/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>

  <noscript><?PHP $_JS=0; ?></noscript>
</head>


<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<?PHP
  foreach($_GET as $key=>$value)
  {
    $$key = $value;
  }
  foreach($_POST as $key=>$value)
  {
    $$key = $value;
  }
//echoalert($_JS);

  // connect database
  $daba = new database();
  $daba->connect("localhost","iggmp","1s87J37r0");
  $daba->select("thesaurus");

  $exportString = export::print_tree($id,$type);

  if ($type == "adlib")
    $extension = ".xml";
  else
    $extension = ".csv";

  $path = "import/export" . user::id() . $extension;
  $fHandle = fopen($path,"w");
  fputs($fHandle,utf8_decode($exportString));
  fclose($fHandle);

  echo "<a href='$path'>$type herunterladen</a>";
?>
</body>