<?PHP session_start(); ?>

<html>
<head>
	<TITLE>Edit Rights</TITLE>

	<link rel='stylesheet' type='text/css' href='default.css'>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">

  <script language="JavaScript" src="libjava.js"></script>
  <script type="text/javascript" src="javascript/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>

</head>
<body>

<?PHP

include("libstd.php");

// connect database
$daba = new database();
$daba->connect("localhost","iggmp","1s87J37r0");
$daba->select("thesaurus");

echo "<h1>Rechte bearbeiten</h1>";
echo "<h2>Feld: <i>'$field'</i></h2>";
?>
<hr>

<?PHP

  $right = right::get_field($field);
  $edit = $right[edit];
  $view = $right[view];


// save tooltip
  if ($s1)
  {
    right::set_tooltip($field,$tooltext);
  }

//---------------------------------------------------------
// edit rights
// set bit of edit right
  switch($setedit)
  {
    case 1:
      $edit = $edit | 1;
      break;

    case 2:
      $edit = $edit | 2;
      break;

    case 4:
      $edit = $edit | 4;
      break;
  }

// clear bit of edit right
  switch($deledit)
  {
    case 1:
      $edit = $edit & 6;
      break;

    case 2:
      $edit = $edit & 5;
      break;

    case 4:
      $edit = $edit & 3;
      break;
  }
  

//---------------------------------------------------------
// view rights
// set bit of view right
  switch($setview)
  {
    case 1:
      $view = $view | 1;
      break;

    case 2:
      $view = $view | 2;
      break;

    case 4:
      $view = $view | 4;
      break;
  }

// clear bit of edit right
  switch($delview)
  {
    case 1:
      $view = $view & 6;
      break;

    case 2:
      $view = $view & 5;
      break;

    case 4:
      $view = $view & 3;
      break;
  }

  right::set_field($field,$edit,$view);
  
  $right = right::get_field($field);
  $edit = $right[edit];
  $view = $right[view];


  echo "<form method='get' action=editright.php>";

// display formular
    echo form::field("text","tooltext",$right[text],50,"","Tooltip");
    echo form::field("submit","s1","speichern");
    echo form::field("hidden","field",$field);
  
    echo "<hr>";
  
// right array  
    echo "<table>";
      echo "<tr>";
        echo "<th>&nbsp;</th>";
        echo "<th>Lesen</th>";
        echo "<th>Schreiben</th>";
        echo "<th>Verlinken</th>";
      echo "</tr>";
      
      echo "<tr>";
        echo "<td align='center'>Bearbeiten</td>";
        echo "<td align='center'>";
          if ($edit & 1) echo form::link("X","X","editright.php?deledit=1&amp;field=$field");
          else echo form::link("O","O","editright.php?setedit=1&amp;field=$field");
        echo "</td>";
    
        echo "<td align='center'>";
          if ($edit & 2) echo form::link("X","X","editright.php?deledit=2&amp;field=$field");
          else echo form::link("O","O","editright.php?setedit=2&amp;field=$field");
        echo "</td>";
    
        echo "<td align='center'>";
          if ($edit & 4) echo form::link("X","X","editright.php?deledit=4&amp;field=$field");
          else echo form::link("O","O","editright.php?setedit=4&amp;field=$field");
        echo "</td>";
      echo "</tr>";
    
      echo "<tr>";
        echo "<td align='center'>Anzeigen</td>";
        echo "<td align='center'>";
          if ($view & 1) echo form::link("X","X","editright.php?delview=1&amp;field=$field");
          else echo form::link("O","O","editright.php?setview=1&amp;field=$field");
        echo "</td>";
    
        echo "<td align='center'>";
          if ($view & 2) echo form::link("X","X","editright.php?delview=2&amp;field=$field");
          else echo form::link("O","O","editright.php?setview=2&amp;field=$field");
        echo "</td>";
    
        echo "<td align='center'>";
          if ($view & 4) echo form::link("X","X","editright.php?delview=4&amp;field=$field");
          else echo form::link("O","O","editright.php?setview=4&amp;field=$field");
        echo "</td>";
      echo "</tr>";
    echo "</table>";

    echo "<hr>";
    
//    echo form::field("submit","close","schlieﬂen","","onclick='editWindow.close()'");

  echo "</form>";
  
  
  
?>
</body>
</html>