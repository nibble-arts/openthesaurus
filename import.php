<?PHP
session_start();
include("libstd.php");
$_JS=1;
?>



<html>
<head>
	<TITLE>OpenThesaurus CSV Import</TITLE>

	<link rel='stylesheet' type='text/css' href='default.css'>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">

  <script language="JavaScript" src="libjava.js"></script>
  <script type="text/javascript" src="javascript/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>

  <noscript><?PHP $_JS=0; ?></noscript>
</head>


<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<?PHP
//echoalert($_JS);


// connect database
$daba = new database();
$daba->connect("localhost","iggmp","1s87J37r0");
$daba->select("thesaurus");


if (right::link()) thesaurus::validate();

// show login or logged user
user::show();


// headline
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

<?PHP
  foreach($_GET as $key=>$value)
  {
    $$key = $value;
  }
  foreach($_POST as $key=>$value)
  {
    $$key = $value;
  }

    // Root für Daten
    $temp = "import";
//    $b1 = $_POST[b1];

//------------------------------------------------------------------------------
    // data transmitted
    switch ($b1)
    {
      case laden:
        $source = $_FILES['csv']['tmp_name'];
        $filename = $_FILES['csv']['name'];
        $fileSize = $_FILES['csv']['size'];
        $destination = "$temp/$filename";
       
        /* gesendete Daten verarbeiten */
      	/* Daten übermittelt -> kopieren */
      	if ($source != "") {
          
      		if ($fileSize > 2000000)
      		{
            echo "<p class='error'>Datei zu groß (max 2MB)</p>";
      		}
      		else
      		{
      			/* Daten speichern */
      			if (move_uploaded_file($source,$destination))
    				{
              echo "<p>Daten wurden erfolgreich überspielt</p>";
     			}
      			else
    				{
              echo "<p class='error'>Fehler beim Laden</p>";
      			}
      		}
      	}
    
        clearstatcache();
        break;
      default:
        $destination = "$filename";
        break;
    }

?>
  </div>

  <div id='search'>
    <form enctype='multipart/form-data' method='post' action='import.php'>
      <p><input type='file' name='csv' value=' <?PHP echo $source; ?>'>
      <input type='submit' name='b1' value='laden'>
      <input type='hidden' name='id' value='<?PHP echo $id; ?>'>

      Daten stammen von <input type='text' name='root' value='<?PHP echo $root; ?>'>

<?PHP
      if ($filename)
      {
        echo "<input type='hidden' name='filename' value='$destination'>";
        echo "<input type='submit' name='b1' value='update'>";
        echo "<input type='submit' name='action' value='insert'>";
      }
      
      if (!isset($levelCnt)) $levelCnt = 7;
?>
      </p>

      <p><input type='checkbox' name='title' value='1' <?PHP if ($title) { echo "checked='checked'"; } ?>> Titelzeile
      <input type='checkbox' name='parseId' value='1' <?PHP if ($parseId) { echo "checked='checked'"; } ?>> ID
      <input type='text' name='levelCnt' value='<?PHP echo $levelCnt; ?>'> # of Levels</p>

<?PHP
      echo "<p>Import in Thesaurus <b>'" . thesaurus::get_name($id) . "'</b></p>";
?>     
    </form>
  </div>
<?PHP

//------------------------------------------------------------------------------
// display analysed data
  echo "<div id='infobig'>";
    import::analyse($destination,$id,$title,$root,$parseId,$action,$levelCnt);
  echo "</div>";
?>

</body>
</html>
