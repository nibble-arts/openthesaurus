<?PHP
include_once("action.php");

class user
{
// returns id of current user
  function id()
  {
    return(session::get("user"));
  }


// fetch user information
// $id ... information of this user
// no par ... current user
  function get($id="")
  {
    if (!$id and user::id()) $id = user::id();
    if ($id)
    {
      $userArray = fetch_to_array(database::query("SELECT * FROM user WHERE ID='$id'"),"");
      if (is_array($userArray)) $tempArray = current($userArray);
      return($tempArray);
    }
    else return(FALSE);
  }
  
  
// get user name
// if no id then get current user
// FALSE, if nobody logged in
  function name($id="")
  {
    $userArray = user::get($id);
    if ($userArray) return($userArray[user]);
  }


// get program status information
  function status()
  {
    if ($userID = user::id())
    {
      $tempArray = current(fetch_to_array(database::query("SELECT status FROM user WHERE ID='$userID'"),""));
      return($tempArray[status]);
    }
  }
  
  
// get user name
// FALSE, if nobody logged in
  function password()
  {
    $userArray = user::get($id);
    if ($userArray) return($userArray[password]);
  }


// returns groupID of active user
  function group($id)
  {
    $userArray = user::get($id);
    if ($userArray) return($userArray[group]);
  }


// get superuser status
  function superuser($id="")
  {
    if (!$id and ($id = user::id())) $id = user::id();
    $userArray = user::get($id);

    if (group::superuser($userArray[group])) return(TRUE);
    else return(FALSE);
  }

// checks if user exists
  function exists($user)
  {
      if (mysql_num_rows(database::query("SELECT * FROM user WHERE user='$user'"))) return(TRUE);
      return(FALSE);
  }


//------------------------------------------------------------------------------
// get list of users with ordered entries
  function get_users($status="")
  {
    switch(strtolower($status))
    {
      case entry:
        {
          $queryString = "SELECT user.ID,user.user
            FROM user,entry
            WHERE entry.owner=user.ID";
        }
        break;
      
      case visible:
        {
          $queryString = "SELECT user.ID,user.user
            FROM user,entry,statustype
            WHERE entry.owner=user.ID and entry.status=statustype.ID and statustype.visible='0'";
        }
        break;

      default;
        $queryString = "SELECT ID,user FROM user ORDER BY user";
        break;
    }

//echoalert($queryString);
        
    $resource = database::query($queryString);

    $retArray = array();
    
    while ($entry = mysql_fetch_object($resource))
    {
      $tempArray = array("value" => $entry->ID,"text" => $entry->user);
      if (!in_array($tempArray,$retArray)) // check if already exists
      array_push($retArray,$tempArray);
    }
    return($retArray);
  }


//------------------------------------------------------------------------------
// checks login data and loggs in
  function login($user,$password)
  {
    $tempArray = fetch_to_array(database::query("SELECT * FROM user WHERE user='$user' and password='" . md5($password) . "'"),"");
    if (is_array($tempArray)) // log in
    {
      $tempArray = current($tempArray);
      session::set("user",$tempArray[ID]);
      save_status($_SESSION);
    }
    else
      echojavascript("Login oder Passwort falsch");
  }
  
  
// logout active user
  function logout()
  {
    session::destroy("user");
  }
  
  




// shows login / logout field
  function show()
  {
    if (system::getval(debug)) echo "<div id='login_debug'>";
    else echo "<div id='login'>";

      if ($userID = session::get("user")) // show user and logout
      {
      
  // change password
        if ($_GET[action] == "change")
        {
          echo "<form method='get' action='index.php'>";
            echo form::field("password","password","","","","<i>Neues Passort</i>","","newpassword");

            echo form::field("submit","b1","&auml;ndern");
            echo form::field("hidden","action","changedo");
          echo "</form>";
        }
  // logged in
        else 
        {
          echo "<span class='normal'>Angemeldet: </span><span class='red'>" . user::name($userID) . " </span>";

          echo form::link(""," - abmelden ","index.php?action=logout","logout");
          echo form::link(""," - Passwort &auml;ndern ","index.php?action=change","change-password");

  // if su display admin
          echo form::link(""," - Administration","admin.php","admin");
          if ($_SESSION['show'])
            echo form::link(""," - Import into <b>" . thesaurus::name($_SESSION['show']) . "</b>","import.php?id=" . $_SESSION['show'],"import");

// menu switches
          action::hyrarchy(); // toggle hyrarchy off / on
          action::descriptor();
          action::visible();

          action::tooltips(); // toggle tooltips off / on

          if (right::write()) action::edit(); // show edit function

          action::listit();
          
          action::debug();
        }
      }

// show login
      else
      {
        echo "<span class='normal'>";
          echo "<form method='get' action='index.php'>";
            echo "<i>Benutzername </i><input type='text' name='user' value='$user'>";
            echo " <i>Passwort </i><input type='password' name='password'>";
            
            echo " <input type='submit' name='action' value='anmelden'>";
            echo "<input type='hidden' name='action' value='login'>";
          echo "</form>";
        echo "</span>";
      }
// display javascript status
/*      echo "<span class='small'>";
        if (session::get("JS")) echo "JavaScript aktiv";
        else echo "kein Javascript";
      echo "</span>";*/
    echo "</div>";
  }
}
?>