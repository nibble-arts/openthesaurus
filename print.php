<?PHP session_start(); ?>


<html>
<head>
	<TITLE>OpenThesaurus</TITLE>

	<link rel='stylesheet' type='text/css' href='default.css'>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
</head>

<body onload="self.focus(); document.insert.name.focus();">


<script language="JavaScript" src="libjava.js"></script>

<?PHP
include("libstd.php");


// connect database
$daba = new database();
$daba->connect("localhost","iggmp","1s87J37r0");
$daba->select("thesaurus");


switch($action)
{
  case all:
    export::print_all(session::get("descriptor"));
    break;
  
  case search:
    export::print_search($filter);
    break;
  
  default:
    break;
}



?>
</body>
</html>