<?PHP
session_start();
include("libstd.php");
$_JS=1;
?>



<html>
<head>
	<TITLE>OpenThesaurus</TITLE>

	<link rel='stylesheet' type='text/css' href='default.css'>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">

  <script language="JavaScript" src="libjava.js"></script>
  <script type="text/javascript" src="javascript/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>

  <noscript><?PHP $_JS=0; ?></noscript>
</head>


<body <?PHP
  if ($_GET[action] == "showhyrarchy"
    or $_GET[action] == "open"
    or $_GET[action] == "close"
  )
  {
    $string = "&#34;#anchor" . $id . "&#34;";
    echo "onload='location.href = $string';";
  }
  ?>>
  
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>


 
<?PHP
//echoalert($_JS);


// connect database
$daba = new database();
$daba->connect("localhost","iggmp","1s87J37r0");
$daba->select("thesaurus");


// open thesaurus
$display = new hyrarchy;


// call actions
action($_GET);





if (right::link()) thesaurus::validate();

// show login or logged user
user::show();


//----------------------------------
// headline
if (system::getval(debug) and !user::superuser()) echo "<div id='alert' class='big'>Die Seite steht derzeit wegen Wartungsarbeiten nicht zur Verfügung.</div>";
else
{
  ?>
    <div id='header'>
      <a href='index.php'>OpenThesaurus</a><br>
  <?PHP
  
  // show count of entries
      echo " <i class='normal'>(";
        echo database::count() . " Einträge mit ";
        echo database::linkcount() . " Verknüpfungen";
      echo ")</i>";
  ?>
    </div>
    
    
  
  
  <div id='status'>V2.3 - Thomas H Winkler - 2010-2011</div>
  
  <?PHP
  
  
  
  if (right::read())
  {
    // Insert values into database
    $mandatory = array("name");
    
    $errorArray = database::insert($_GET,$mandatory);
    $errorArray = $display->set_default($errorArray,$mandatory);
  
  
  
  //------------------------------------------------------------------------------
  // Form fields
    if (session::get("hyrarchy")) echo "<div id='info' class='info'>"; // small info windos
    else echo "<div id='infobig' class='info'>"; // big info window
      form::insert_entry($_GET,$errorArray);
    echo "</div>";
  
  
    if (session::get("show"))
    {
      if (session::get("hyrarchy")) echo "<div id='info' class='info'>";
      else echo "<div id='infobig' class='info'>";
        form::update_entry($_GET,$errorArray);
      echo "</div>";
    }
  
  
//------------------------------------------------------------------------------
// display hyrarchy list
    if (session::get("hyrarchy"))
    {
    	echo "<div id='hyrarchy'>";
      	$display->header();
      	$display->display(0,-1,0,$errorArray); //thesaurus-resource, parent, depth, position
    	echo "</div>";
    }
  
  
  
  //------------------------------------------------------------------------------
  // search field
    $searchResult = search::get(session::get(search));
  
  
  // display search result
    if (!session::get(searchshow)) unset($searchResult); // clear serach string if nothing new
  
    if (count($searchResult) > 0 and session::get(searchshow))
    {
  // display search window
      search::display($searchResult);
    }
  
    // display search formular  
    search::form(session::get(search));
  }
  else
  {
    form::standard();
  }
}
?>


</body>
</html>