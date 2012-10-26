<?PHP
include_once("user.php");
include_once("group.php");


class right
{


// get rights from database
// FALSE, if nobody logged in
  function get($field)
  {
    $userID = session::get("user");
    
    if ($userID)
    {
      $tempArray = fetch_to_array(database::query("SELECT * FROM user,`group` WHERE user.group=group.ID and user.ID='$userID'"),"");
      if (is_array($tempArray)) $tempArray = current($tempArray);

      return($tempArray[$field]);
    }
    else return(FALSE);
  }
  


// check if read permission
  function read()
  {
    $right = right::get("rights");
    if ($right & 1) return(TRUE);
    else return(FALSE);
  }


// check if read permission
  function link()
  {
    $right = right::get("rights");
    if ($right & 4) return(TRUE);
    else return(FALSE);
  }


// check if write permission
  function write()
  {
    $right = right::get("rights");
    if ($right & 2) return(TRUE);
    else return(FALSE);
  }
  

// check if superuser
  function superuser()
  {
    if(right::get("superuser")) return(TRUE);
    else return(FALSE);
  }
  
  

//-----------------------------------------------------------------------------
// get field rights
// check if right for field editing
  function get_field($name)
  {
    if ($name)
    {
      $tempArray = fetch_to_array(database::query("SELECT * FROM system WHERE name='$name'"),"");
      if (is_array($tempArray)) return(current($tempArray));
    }
    else return (false);
  }


// set right of field
  function set_field($field,$edit="",$view="")
  {
    $userID = session::get("user");
    
    if ($userID)
    {
      if (!right::get_field($field))
        database::query("INSERT INTO system SET name='$field',edit='$edit',view='$view'");
      else
        database::query("UPDATE system SET edit='$edit',view='$view' WHERE name='$field'");
    }    
  }


// set new tooltip text
  function set_tooltip($field,$text)
  {
    if ($field and $text)
    {
      database::query("UPDATE system SET text='$text' WHERE name='$field'");
    }
  }

// check if right for field editing
  function field_edit($name)
  {
    if (right::superuser()) return (true);
    
    $rightArray = right::get_field($name);
    $userRight = right::get("rights");

    if ($rightArray[edit]) return($userRight & $rightArray[edit]);
    else return(false); // default no edit
  }


// check if right for field editing
  function field_view($name)
  {
    if (right::superuser()) return (true);
    
    $fieldRight = right::get_field($name);
    $view = $fieldRight[view];

    $userRight = right::get("rights");

    if ($view != 0) return($userRight & $view);
    else return(false); // default no display
  }

  

// display rights
  function int2string($right,$su=false)
  {
    $text = "";
    
    if ($right & 1) $text .= "R";
    if ($right & 2) $text .= "W";
    if ($right & 4) $text .= "L";
    
    if (right::superuser() and $su) $text .= " su";

    return($text);
  }
}

?>