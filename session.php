<?PHP

class session
{
// set variable
  function set($name,$value)
  {
    $_SESSION[$name] = $value;
  }
  
// read variable
  function get($name)
  {
    return($_SESSION[$name]);
  }
  
// set entry-ID open
  function open($id)
  {
    session::set('open' . $id,TRUE);
  }

// set entry-ID closed
  function close($id)
  {
    session::destroy('open' . $id);
  }

// set entry-ID closed
  function destroy($id)
  {
    unset($_SESSION[$id]);
  }

// clase all opened treeelements
  function close_all()
  {
    while ($element = each($_SESSION))
    {
      $keyVal = substr($element[key],0,4); 
      $numVal =substr($element[key],4);
      
      if ($keyVal == "open") unset($_SESSION["$keyVal$numVal"]);
    }
  }
}




// save status of program to database
function save_status($statusArray)
{
  if (user::name() != "gast" and ($userID = user::id()))
    {
    $tempArray = array();
    
    while($entry = each($statusArray))
    {
      array_push($tempArray,$entry[key] . "=" . $entry[value]);
    }
    $statusString = implode($tempArray,";");
    if (database::query("UPDATE user SET status='$statusString' WHERE ID='$userID'"))
    {
      session::set("stored",time());
    }
  }
}


// restore status information if new session started
function restore_status()
{
  if (user::name() != "gast")
  {
    $statusString = user::status();
  
    $_SESSION = array();
  
    $statusArray = explode(";",$statusString);
  
    if (is_array($statusArray))
    {
      foreach($statusArray as $entry)
      {
        $keyvalArray = explode("=",$entry);
        session::set($keyvalArray[0],$keyvalArray[1]);
      }
    }
  }
}

?>