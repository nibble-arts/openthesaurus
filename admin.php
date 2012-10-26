<?PHP session_start(); ?>

<html>
<head>
	<TITLE>OpenThesaurus</TITLE>

	<link rel='stylesheet' type='text/css' href='default.css'>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">

  <script language="JavaScript" src="libjava.js"></script>
  <script type="text/javascript" src="javascript/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>

</head>

<body>


<script language="JavaScript" src="libjava.js"></script>

<?PHP
include("libstd.php");
include("search.php");

// connect database
$daba = new database();
$daba->connect("localhost","iggmp","1s87J37r0");
$daba->select("thesaurus");

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
  </div>

<?PHP


if (right::read())
{
  switch ($action)
  {
    case update:
      group::set($_GET[user],$_GET[group]);
      break;

    case add:
      if ($_GET[user] and $_GET[password]) // user and password
      {
        if (!user::exists($_GET[user]))
        {
          $user = $_GET[user];
          $md5Password = md5($_GET[password]);
          database::query("INSERT INTO user SET user='$user',password='$md5Password',`group`='4'");
        }
        else
        {
          echo "<div id='alert'><b>User schon vergeben</div>";
        }
      }
      break;

    case deleteuser:
      if (user::exists($id))
      {
        echoalert("Benutzer " . user::name($id) . " gelöscht");
  
        database::query("DELETE FROM user WHERE ID='$id'");
      }
      break;
  }


  $userArray = fetch_to_array(database::query("SELECT user.ID,user.user,user.password,user.group,user.status,`group`.name FROM user,`group` WHERE user.group=group.ID ORDER BY user"),"");
  
// list users
  echo "<div id='infobig'>";
    echo "<fieldset><legend>User</legend>";
      echo "<table border='1'>";
        echo "<th>User</th>";
        echo "<th>Group</th>";
        echo "<th></th>";
        echo "<th></th>";
        echo "<th>Last Login</th>";
        echo "<th>Kandidaten</th>";
    
  // edit users and groups
        $bgColor = "lightblue";
      
        foreach($userArray as $entry)
        {
          echo "<tr style='background-color:$bgColor'>";
// toggle backgroud color
            if ($bgColor == "white") $bgColor = "lightblue";
            else $bgColor = "white";
            
            echo "<form action='admin.php' method='get' name='" . $entry[ID] . "'>";
                $userID = $entry[ID];
                $group = $entry[group];

                echo "<td>" . $entry[user] . " <i class='bright'>ID:$userID</i></td>";
                echo "<td>";

                echo form::selector("group",group::get_list(),1,"",$group,"","","groupselect");

                if (user::superuser($userID)) echo " <i class='red'>su</i>";
              echo "</td>";
    
              echo "<td>";
                echo form::field("submit","b1","speichern","","","","","save-group");
              echo "</td>";

              echo "<td>";
                $javaText = "Wollen Sie den Benutzer " . user::name($userID) . " wirklich löschen?";
                echo form::link("deleteuser","löschen","javascript:get_confirm(&#34;$javaText&#34;,&#34;admin.php?action=deleteuser&id=$userID&#34;);");
              echo "</td>";
              
// display last login time
              echo "<td>";
                $statusArray = explode(";",$entry[status]);
                foreach($statusArray as $statusEntry)
                {
                  $last = 0;
                  $entryArray = explode("=",$statusEntry);
                  if ($entryArray[0] == "stored") { $last = $entryArray[1]; break; }
                }
                if ($last) echo date("d.m.Y H:i:s",$last);
              echo "</td>";

// display kandidate entries
              echo "<td>";
                if ($cnt = mysql_num_rows(database::query("SELECT entry.ID,statustype.ID FROM entry,statustype WHERE entry.owner=$userID and entry.status=statustype.ID and statustype.new='1'")))
                  echo "<a href='index.php?action=suchen&amp;searchstatus=3&amp;searchowner=$userID'>$cnt</a>";
              echo "</td>";


// hidden field data
              echo form::field("hidden","action","update");
              echo form::field("hidden","user","$userID");
            echo "</form>";
          echo "</tr>";
        }
      echo "</table>";
    echo "</fieldset>";
    
// add new user
    echo "<p><form method='get' action='admin.php'>";
      echo "<fieldset><legend>Neuer Benutzer</legend>";

        echo form::field("text","user","",30,"","Username ","","new-username");
        echo "<br>";
        echo form::field("password","password","",30,"","Password ","","new-password");

        echo "<br>";
        
        echo form::field("submit","action","anlegen");
        echo form::field("hidden","action","add");
      echo "</fieldset>";
    echo "</form>";
    
    
    
// ----------------------------------------------------------------------------
// list groups

    $groupArray = group::get_list();
    
    echo "<fieldset><legend>Gruppen</legend>";
      echo "<table>";
        
        echo "<th>group</th>";
        echo "<th>rights</th>";
  
        foreach($groupArray as $entry)
        {
          echo "<tr>";
            echo "<td>" . $entry[text] . "</td>";
            echo "<td>" . right::int2string($entry[rights],group::superuser($entry[value])) . "</td>";
          echo "</tr>";
        }
      echo "</table>";
    echo "</fieldset>";

  echo "</div>";
}
else
{
  form::standard();
}
?>



</body>
</html>