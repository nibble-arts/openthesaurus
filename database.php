<?PHP



// global

// fetch a table to array
// if $field == "" ... fetch all data
// if $field == Fieldname ... fetch only this field
// returns count of elements

function fetch_to_array($resource,$field)
{
  $num = 0;
  while ($element = mysql_fetch_assoc($resource))
  {
    if ($field) $tempArray[$num] = $element[$field]; // store selected field
    else $tempArray[$num] = $element; // store only $field element
    
    $num++;
  }
  if (count($tempArray)) return($tempArray);
  else return(FALSE);
}





//------------------------------------------------------------------------------
//------------------------------------------------------------------------------
// class for database access and data input and output
class database {

var $resource;

// Establish connection to the database
// Servername, Username, Password
	function connect($server,$user,$password)
	{
		$this->resource = mysql_connect ($server,$user,$password);
	}

// select database
//
  function select($name)
  {
    return mysql_select_db($name,$this->resource);
  }


// fetch number of entries in entry
  function count()
  {
//    if (session::get("descriptor")) $descString = "";
//    else $descString = " WHERE descriptor='1'";
    
    return(mysql_num_rows(database::query("SELECT entry.ID FROM entry,statustype WHERE entry.status=statustype.ID and statustype.descriptor='1'")));

//    return(mysql_num_rows(database::query("SELECT ID FROM entry $descString")));
  }


// fetch number of links in entry
  function linkcount()
  {
    return(mysql_num_rows(database::query("SELECT ID FROM parent")));
  }



// send query string
  function query($queryString)
  {
// parse query string
/*    if (stristr(strtolower($queryString),"insert")) return(true);
    if (stristr(strtolower($queryString),"delete")) return(true);
    if (stristr(strtolower($queryString),"update")) return(true);
*/
    return(mysql_query($queryString));
  }  



// delete ID from database
  function delete($id)
  {
// delete parent entries
    database::query("DELETE FROM parent WHERE child='$id'");
    database::query("DELETE FROM parent WHERE parent='$id'");

// delete entry
    database::query("DELETE FROM entry WHERE ID='$id'");
  }





//------------------------------------------------------------------------------
// inserts data in the database
  function insert($_arg,$mandatory)
  {
    $errorArray = array();

    if ($_arg[action] == "insert" or $_arg[action] == "update")
    {
      session::destroy(searchshow);
      
	    $action = $_arg[action];
      $ok = TRUE; // set error default ok


//-----------------------------------------------------------------------------
// insert
      if($_arg[action] == "insert")
      {

//-----------------------------------------------------------------------------
  // check for existing name entry
        $tempArray = search::get("$_arg[name]","exact");
        if (is_array($tempArray)) // set error to exist
        {
          $ok = FALSE;
          $errorArray[name] = "exist";
        }
        else
        {
  // check for alike name entry
          $tempArray = search::get("$_arg[name]%","start");
          if (is_array($tempArray)) // set error to exist
          {
//            $ok = FALSE;
            $errorArray[name] = "alike";
          }
        }
      }    



// check mandatory fields
      foreach($mandatory as $check)
      {
        if (!$_arg[$check] != "")
        {
          $ok = FALSE;
          $errorArray[$check] = "error";
        }
      }      

//echoalert($errorArray);
  // mandatory fields ok -> create sql-string out of data
      if ($ok)
      {

//echoalert($_GET);
//-----------------------------------------------------------------------------
// reset parameters for next entry
        unset($_GET[name]); // delete name, sysnonym and comment
        unset($_GET[comment]);
        unset($_arg[action]); // remove action comment from $_arg

        $errorArray = false;
        
  // combine parameters
        $queryArray = array();

        while($entry = each($_arg))
        {
          switch ($entry[key])
          {
            case parent:
              $parent = $entry[value]; // filter parent
              break;
            case id:
              $child = $entry[value]; // filter ID
              break;
            case type:
              $type = $entry[value]; // filter type
              break;
            case PHPSESSID: // filter SSID
              break;
            case orderdefault:
              system::setval("val_orderdefault",$entry[value]);
              break;

            default:
              array_push($queryArray,$entry[key] . "='" . $entry[value] . "'");
              break;
          }
        }
        $insertString = implode(",",$queryArray);


// add sql-clauses
      	switch ($action)
      	{
// query for entry insert
      		case insert:
            $_GET[action] = "add"; // set for next entry
            $insertString .= ",owner='" . user::id() . "'";
      			$queryString = "INSERT INTO entry SET $insertString"; // = "INSERT entry
//echoalert($queryString);

            if (database::query($queryString))
            {
        // query for parent insert
              $child = mysql_insert_id();
              $parentString = "INSERT INTO parent SET child='$child',parent='$parent',type='$type',owner='$user'";
              database::query($parentString);
            }
      			break;
      
// query for entry update
      		case update:
            $queryString = "UPDATE entry SET $insertString WHERE ID='" . $_arg[id] . "';"; // = "UPDATE entry
            database::query($queryString);
      			break;
      	}

      }
      return($errorArray);
    }
  }
  




// gets ID of parent/child entry
  function parent_get($child,$parent)
  {
    $queryString = "SELECT * FROM parent WHERE (child='$child' and parent='$parent') or (child='$parent' and parent='$child')";
    if (mysql_num_rows($temp = database::query($queryString))) // entry found
    {
      return(mysql_result($temp,0,"ID"));
    }
    else return(FALSE); // entry exists; nothing done
  }


// checks if thesaurus
	function is_thesaurus($id)
	{
		$tempArray = database::parent_fetch_parents($id);
		foreach($tempArray as $entry)
		{
			if (array_search(0,$entry) == "parent") return(TRUE);
		}
		return(FALSE);
	}

// insert parent / child
  function parent_insert($child,$parent,$type)
  {
// check if dataset exists
    if (!database::parent_get($child,$parent)) // no entry
    {
      $user = user::id();
      $queryString = "INSERT INTO parent SET child='$child',parent='$parent',type='$type',owner='$user'";
      return(database::query($queryString));
    }
    else return(FALSE); // entry exists; nothing done
  }


// delete parent / child entry
  function parent_delete($child,$parent)
  {
// check if dataset exists
    if ($linkID = database::parent_get($child,$parent)) // entry exists
    {
      $queryString = "DELETE FROM parent WHERE ID='$linkID'";
      return(database::query($queryString));
    }
    else return(FALSE); // entry exists; nothing done
  }


// change OB
  function link_change($child,$oldlink,$newlink)
  {
    database::parent_delete($child,$oldlink);
    database::parent_insert($child,$newlink,1);
    end_link();
  }
  
// gets count of childs
  function parent_childs($id)
  {
    return(mysql_num_rows(mysql_query("SELECT * FROM parent WHERE child='$id'")));
  }

// gets count of parents
  function parent_parents($id)
  {
    return(mysql_num_rows(mysql_query("SELECT * FROM parent WHERE parent='$id'")));
  }

// get parent ids
  function parent_fetch_parents($id)
  {
    $queryString = "SELECT parent.ID,parent.parent,parent.child,parent.type,parent.owner FROM parent,linktype WHERE parent.child='$id' and parent.type=linktype.ID and linktype.hyrarchic='1'";

    return(fetch_to_array(database::query($queryString),""));
  }

// get child ids
  function parent_fetch_childs($id)
  {
    $queryString = "SELECT parent.ID,parent.parent,parent.child,parent.type,parent.owner FROM parent,linktype WHERE parent.parent='$id' and parent.type=linktype.ID and linktype.hyrarchic='1'";

    return(fetch_to_array(database::query($queryString),""));
  }





// get list of link types
// $hyrarchic ... 1->only hyrarchic types
  function get_linktypes($hyrarchic)
  {
    if ($hyrarchic) $whereString = "WHERE hyrarchic='1'";
    else $whereString = "";
    return(fetch_to_array(database::query("SELECT * FROM linktype $whereString"),""));
  }


// get hyrarchic status of linktype
  function get_linkstatus($typeID)
  {
    $tempArray = fetch_to_array(database::query("SELECT * FROM linktype WHERE ID='$typeID'"),"");

    if (is_array($tempArray)) $tempArray = current($tempArray);
    return($tempArray[hyrarchic]);
  }



// set descriptor type
  function set_desc($id,$value)
  {
    if ($id)
    {
      if ($value) $value = 1;
      else $value = 0;

      database::query("UPDATE entry SET descriptor='$value' WHERE ID='$id'");
    }
  }


}
?>