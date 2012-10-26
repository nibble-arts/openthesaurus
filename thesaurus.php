<?PHP
include_once("database.php");


class thesaurus {


// check if subtree on parent is opened
  function is_open($id)
  {
    if (session::get('open' . $id)) return(TRUE);
    else return(FALSE);
  }
  
  
// fetch name of entry $id
  function name($id)
  {
    $queryString = "SELECT name FROM entry WHERE ID='$id'";
    $name_res = database::query($queryString);
    if (mysql_num_rows($name_res) != 0) return(mysql_result($name_res,0));
    else return(FALSE);
  }
  


// returns a array of the hyrarchy objekts from $id to the top
  function get_hyrarchy($id)
  {

    $temp = database::parent_fetch_parents($id);

    if (is_array($temp)) $tempArray = current($temp);
    else return(FALSE);

    $x = 0;
    $retArray[$x++] = $id; // insert uppest entry

    while ($tempArray[parent] != 0)
    {
      $id = $retArray[$x++] = $tempArray[parent];
      $temp = database::parent_fetch_parents($id);

      if (is_array($temp)) $tempArray = current($temp);
      else break;
    }

    if (is_array($retArray)) $retArray = array_reverse($retArray);
    return($retArray);
  }


// get list of thesauri
  function get_thesaurus_list()
  {
    return(fetch_to_array(database::query("SELECT entry.ID FROM entry,parent WHERE entry.ID=parent.child and parent.parent='0' ORDER BY entry.name"),"ID"));
  }


// get thesaurus name
  function get_thesaurus($id)
  {
    $tempArray = fetch_to_array(database::query("SELECT * FROM entry WHERE ID='$id'"),"thesaurus");
    if (is_array($tempArray)) return($tempArray[0]);
  }



// ------------------------------------------------------------------------
// get all links for deskriptor
  function get_link($deskriptorID,$descType="")
  {
    $returnArray[parent] = thesaurus::get_parent($deskriptorID);
    $returnArray[child] = thesaurus::get_child($deskriptorID);
    $returnArray[assoc] = thesaurus::get_assoc($deskriptorID);
    $returnArray[equiv] = thesaurus::get_equiv($deskriptorID);

    return($returnArray);
  }


// ------------------------------------------------------------------------
// get parents for desktriptor
  function get_parent($deskriptorID)
  {
    $tempArray = fetch_to_array(database::query("SELECT parent.parent FROM parent,linktype WHERE parent.child='$deskriptorID' and parent.parent<>'0' and parent.type=linktype.ID and linktype.hyrarchic<>'0'"),"parent");

    if ($tempArray) return($tempArray);
    else return(FALSE);
  }  



// get children for desktriptor
  function get_child($deskriptorID)
  {
    $visible = session::get(visible);
    $descType = session::get(descriptor);

//    if ($visible) $visibleString = " and entry.status=statustype.ID and statustype.visible='1'";
    
    $queryString = "SELECT parent.child 
    	FROM entry,parent,linktype,statustype 
    	WHERE entry.ID=$deskriptorID 
    		and parent.parent=entry.ID 
    		and parent.type=linktype.ID and linktype.hyrarchic='1'
    		$visibleString
        ";

//echoalert($queryString);

    $tempArray = fetch_to_array(
    	database::query($queryString),"child");

// order by entry names
    if ($tempArray)
    {
      foreach($tempArray as $entry)
      {
// is descriptor
// or is non-descriptor with children
        $cntChild = mysql_num_rows(database::query("SELECT * FROM entry,parent,linktype WHERE entry.ID='$entry' and entry.ID=parent.parent and parent.type=linktype.ID and linktype.hyrarchic='1'"));

        if (thesaurus::is_descriptor($entry)
          or ($visible and !thesaurus::is_visible($entry))
          or ($descType and thesaurus::is_equivalent($entry))
          or $cntChild)
        {
          $orderArray[$entry] = strtolower(thesaurus::get_name($entry));// array lowercase for sorting
          
          asort($orderArray);
      	}
      }

// rewrite sorted array
    if (is_array($orderArray)) return(array_keys($orderArray));
      else return(FALSE);
    }
    else return(FALSE);
  }  

// get equivalent links for desktriptor
  function get_equiv($deskriptorID,$direction="")
  {
// only synonyms
    if (strtoupper($direction) == "BS") $tempArray = fetch_to_array(database::query("SELECT parent.child,parent.parent FROM parent,linktype WHERE parent.child='$deskriptorID' and parent.type=linktype.ID and linktype.ID='2'"),"");

// only used for
    elseif (strtoupper($direction) == "BF")  $tempArray = fetch_to_array(database::query("SELECT parent.child,parent.parent FROM parent,linktype WHERE parent.parent='$deskriptorID' and parent.type=linktype.ID and linktype.ID='2'"),"");

// both directions
    else $tempArray = fetch_to_array(database::query("SELECT parent.child,parent.parent FROM parent,linktype WHERE (parent.child='$deskriptorID' or parent.parent='$deskriptorID') and parent.type=linktype.ID and linktype.ID='2'"),"");

    $x = 0;
    if ($tempArray) // entries found
    {
      foreach($tempArray as $entry)
      {
        if ($entry[child] == $deskriptorID) $retArray[$x++] = $entry[parent];
        else $retArray[$x++] = -$entry[child];
      }
      return($retArray);
    }
    else return(FALSE);
  }


// swap equivalent link direction
  function swap_link($childID,$parentID)
  {
    $swapArray = fetch_to_array(database::query("SELECT * FROM parent WHERE child='$childID' and parent='$parentID'"),"");
    if (!$swapArray) $swapArray = fetch_to_array(database::query("SELECT * FROM parent WHERE parent='$childID' and child='$parentID'"),"");

    $swapArray = current($swapArray);
    $id = $swapArray[ID];
    $child = $swapArray[child];
    $parent = $swapArray[parent];

    database::query("UPDATE parent SET child='$parent',parent='$child' WHERE ID='$id'");
  }


// change OB
  function change_link($arg)
  {
//    echodebug($arg);
  }


// get associative links for desktriptor
  function get_assoc($deskriptorID)
  {
    $tempArray = fetch_to_array(database::query("SELECT parent.child,parent.parent FROM parent,linktype WHERE (parent.child='$deskriptorID' or parent.parent='$deskriptorID') and parent.type=linktype.ID and linktype.ID='3'"),"");

    $x = 0;
    if ($tempArray) // entries found
    {
      foreach($tempArray as $entry)
      {
        if ($entry[child] == $deskriptorID) $retArray[$x++] = $entry[parent];
        else $retArray[$x++] = $entry[child];
      }
      return($retArray);
    }
    else return(FALSE);
  }





// ------------------------------------------------------------------------
// checks if deskriptor in thesaurus level
  function is_thesaurus($deskriptorID)
  {
//    $tempArray = thesaurus::get_parent($desktriptorID);
    
//    echoall($tempArray);
    return(TRUE);
  }


// checks if link is hyrarchic
	function is_hyrarchic($id)
	{
		return(mysql_num_rows(database::query("SELECT * FROM linktype WHERE ID=$id and hyrarchic<>'0'")));
	}

// checks if is equivalent
  function is_equivalent($id)
  {
    return(mysql_num_rows(database::query("SELECT * FROM entry,parent,linktype WHERE entry.ID=$id and entry.ID=parent.parent and parent.type=linktype.ID and linktype.equiv='1'")));
  }

// checks if desktriptor
  function is_descriptor($id)
  {
    return(mysql_num_rows(database::query("SELECT * FROM entry,statustype WHERE entry.ID=$id and entry.status=statustype.ID and statustype.descriptor='1'")));
  }

// checks if visible
  function is_visible($id)
  {
		return(mysql_num_rows(database::query("SELECT * FROM entry,statustype WHERE entry.ID=$id and entry.status=statustype.ID and statustype.visible='1'")));
  }

// checks if rejected
  function is_deleted($id)
  {
		return(mysql_num_rows(database::query("SELECT * FROM entry,statustype WHERE entry.ID=$id and entry.status=statustype.ID and statustype.deleted='1'")));
  }

// gets name of linktype
  function get_type_name($id)
  {
		$tempArray = fetch_to_array(database::query("SELECT * FROM linktype WHERE ID=$id"),"name");
		if ($tempArray) return(current($tempArray));
		else return(FALSE);
  }


// gets short of linktype
  function get_type_short($id)
  {
		$tempArray = fetch_to_array(database::query("SELECT * FROM linktype WHERE ID=$id"),"short");
		if ($tempArray) return(current($tempArray));
		else return(FALSE);
  }


// gets array of available stati
  function get_status_list()
  {
    $statusArray = fetch_to_array(database::query("SELECT * FROM statustype ORDER BY ID"),"");
    $retArray = array();
    foreach($statusArray as $entry)
    {
      if (!$entry[visible]) $visibleString = " (versteckt)";
      else $visibleString = "";

      array_push($retArray,array("value" => $entry[ID],"text" => $entry[status] . $visibleString));
    }
    return($retArray);
  }


// gets list of descriptor types
  function get_type_list()
  {
    $typeArray = fetch_to_array(database::query("SELECT * FROM entrytype ORDER BY ID"),"");
    $retArray = array();
    foreach($typeArray as $entry)
    {
      array_push($retArray,array("value" => $entry[ID],"text" => $entry[type]));
    }
    return($retArray);
  }



// ------------------------------------------------------------------------
// checks if descriptor is parent of
  function is_parent_of($descriptor,$child)
  {
    return(mysql_num_rows(database::query("SELECT parent.ID FROM parent,linktype WHERE parent.parent='$descriptor' and parent.child='$child' and parent.type=linktype.ID and linktype.hyrarchic='1'")));
  }  


// checks if descriptor is child of
  function is_child_of($descriptor,$parent)
  {
    return(mysql_num_rows(database::query("SELECT parent.ID FROM entry,parent,linktype WHERE entry.descriptor='1' and entry.ID='$descriptor' and parent.child='$descriptor' and parent.parent='$parent' and parent.type=linktype.ID and linktype.hyrarchic='1'")));
  }

// checks if descriptor is associated to
	function is_assoc_of($descriptor,$parent)
	{
		$tempArray = thesaurus::get_assoc($descriptor);
		if($tempArray)
		{
			if (in_array($parent,$tempArray)) return(TRUE);
			else return(FALSE);
		}
	}




// ------------------------------------------------------------------------
// count descriptors
// number of parents
  function parent_num($descriptorID)
  {
    $tempArray = thesaurus::get_parent($descriptorID);
    if ($tempArray) return(count($tempArray));
    else return(FALSE);
  }

// number of childs
  function child_num($descriptorID,$descType="")
  {
    $tempArray = thesaurus::get_child($descriptorID,$descType);
    if ($tempArray) return(count($tempArray));
    else return(FALSE);
  }

// number of synonyms
  function equiv_num($descriptorID)
  {
    $tempArray = thesaurus::get_assoc($descriptorID);
    if ($tempArray) return(count($tempArray));
    else return(FALSE);
  }

// number of assoziated links
  function assoc_num($descriptorID)
  {
    $tempArray = thesaurus::get_assoc($descriptorID);
    if ($tempArray) return(count($tempArray));
    else return(FALSE);
  }




// ------------------------------------------------------------------------
// get informations about descriptors
// fetchs information about desktriptor
  function get_descriptor($descriptorID)
  {
    $sqlRef = database::query("SELECT * FROM entry WHERE ID='" . abs(intval($descriptorID)) . "'");
    if (mysql_num_rows($sqlRef))
    {
      $tempArray = current(fetch_to_array($sqlRef,""));
      return($tempArray);
    }
    
    else
    {
//      echo "<p class='red'>Fehlerhafte Verknüpfung zu $descriptorID</p>";
      return(false);
    }
  }
  
// gets name of descriptor
  function get_name($descriptorID)
  {
    $descriptorArray = thesaurus::get_descriptor($descriptorID);
    return($descriptorArray[name]);
  }

// gets owner of descriptor
  function get_owner($descriptorID)
  {
    $descriptorArray = thesaurus::get_descriptor($descriptorID);
    return($descriptorArray[owner]);
  }

// get status of descriptor
  function get_status($descriptorID)
  {
    $descriptorArray = thesaurus::get_descriptor($descriptorID);
    return($descriptorArray[status]);
  }

// get type of descriptor
  function get_entrytype($descriptorID)
  {
    $descriptorArray = thesaurus::get_descriptor($descriptorID);
    return($descriptorArray[entrytype]);
  }

// get comment of descriptor
  function get_comment($descriptorID)
  {
    $descriptorArray = thesaurus::get_descriptor($descriptorID);
    return($descriptorArray[comment]);
  }
  
// get typedescription of descriptor
  function get_entrytype_name($typeID)
  {
    $ref = database::query("SELECT * FROM entrytype WHERE ID=$typeID");
    $temp = fetch_to_array($ref,"type");
    return($temp[0]);
  }

// get statusname of descriptor
  function get_status_name($statusID)
  {
    $ref = database::query("SELECT * FROM statustype WHERE ID=$statusID");
    $temp = fetch_to_array($ref,"status");
    return($temp[0]);
  }

// get status entry for new entries
  function newstatus()
  {
    $ref = database::query("SELECT * FROM statustype WHERE new=1");
    $temp = fetch_to_array($ref,"ID");
    return($temp[0]);
  }



//-----------------------------------------------------------------------------
// validate thesaurus
  function validate($action = "")
  {
// check entries with undefined links
    $sqlRef = database::query("SELECT * FROM parent");

    $tempString = "";
// loop over all link entries
    while ($entry = mysql_fetch_array($sqlRef))
    {
      if (!$action)
      {
        $parentName = thesaurus::get_name($entry[parent]);
        $childName = thesaurus::get_name($entry[child]);

        if (!thesaurus::get_descriptor($entry[parent]) and $entry[parent] != 0) $tempString .= "Oberbegriff $entry[parent] von <b>'$childName'</b> <i>($entry[child])</i> existiert nicht<br>";
        if (!thesaurus::get_descriptor($entry[child])) $tempString .= "Unterbegriff $entry[child] von <b>'$parentName'</b> <i>($entry[parent])</i> existiert nicht<br>";
      }
      else
      {
        if ((!thesaurus::get_descriptor($entry[parent]) or !thesaurus::get_descriptor($entry[child])) and $entry[parent] != 0)
          database::query("DELETE FROM parent WHERE ID=$entry[ID]");
      }

// check crosslinks between thesauri
      if (thesaurus::get_thesaurus($entry[parent]) and (thesaurus::get_thesaurus($entry[parent]) != thesaurus::get_thesaurus($entry[child])))
        $crossString .= form::link("",$entry[parent],"index.php?action=show&amp;id=$entry[parent]") . " <=> " . form::link("",$entry[child],"index.php?action=show&amp;id=$entry[child]") . " link in different thesauri<br>";



    }

// display error messages
    if (right::link() and ($tempString or $crossString))
    {
      echo "<div id='alert' class='red'>";
        if ($tempString)
        {
          echo "<b>OB / UB Fehler</b><br>$tempString<br>";
          
          echo "Datenbank bereinigen ";
          echo form::link("delete","Datenbank bereinigen","index.php?action=correct","correct","");
        }
  
  // display error message
        if ($crossString)
        {
  
            echo "<b>Crosslink Fehler</b><br>$crossString<br>";
            
    //        echo "Datenbank bereinigen ";
    //        echo form::link("delete","Datenbank bereinigen","index.php?action=correct","correct","");
        }
      echo "</div>";
    }
    


// rewrite thesaurus links
    $sqlRef = database::query("SELECT * FROM entry");
    while ($entry = mysql_fetch_array($sqlRef))
    {
      $tempArray = thesaurus::get_hyrarchy($entry[ID]);

      if (is_array($tempArray)) $thesaurus = $tempArray[0];
      else $thesaurus = $tempArray;
      
      database::query("UPDATE entry SET thesaurus='$thesaurus' WHERE ID='$entry[ID]'");
    }
  }
}

function tempfunc($value)
{
  echoall($value);
//  return (strtolower($value));
}


?>