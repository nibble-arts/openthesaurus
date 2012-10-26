<?PHP

class search {

  var $elements = array();

// get search result
// name ... string of search
// exact ... false: search string with global settings
//       ... exact: search string
//       ... start: search string%
//       ... like: search %string%

  function get($name,$exact="")
  {
    if ($name != "")
    {
// use global parameters
      if (!$exact)
      {
      	if (session::get("searchexact")) $nameString = "name='$name'";
      	elseif (session::get("searchstart")) $nameString = "name LIKE '$name%'";
      	else $nameString = "name LIKE '%$name%'";
      }
// use argument for search parameters
      else
      {
        switch($exact)
        {
          case exact:
            $nameString = "name='$name'";
            break;
            
          case start:
            $nameString = "name LIKE '$name%'";
            break;
            
          case like:
            $nameString = "name LIKE '%$name%'";
            break;
        }
      }
      
      if (session::get("searchcom")) $comString = " or comment LIKE '%$name%'";
      else $comString = "";

      if (session::get("searchorder")) $visibleString = " and visible='0'";
      else $visibleString = "";
      
      if (session::get("searchowner")) $ownerString = " and owner='" . session::get("searchowner") . "'";
      else $ownerString = "";

      if (session::get("searchentrytype")) $entrytypeString = " and entrytype='" . session::get("searchentrytype") . "'";
      else $entrytypeString = "";

      if (session::get("searchstatus")) $statusString = " and status='" . session::get("searchstatus") . "'";
      else $statusString = "";


      $search_res = mysql_query("SELECT ID FROM entry WHERE ($nameString $comString) $desString $visibleString $ownerString $entrytypeString $statusString ORDER BY name");
      return(fetch_to_array($search_res,""));
    }
    else return(FALSE); // no search string
  }


// display form for search input
  function form($searchString)
  {
  	if (session::get("hyrarchy")) echo "<div id='search' class='search'>";
  	else echo "<div id='searchbig' class='search'>";


      echo "<form method='GET' action='index.php' name='search'>";
        echo "<b>Suche in</b><br>";
  
// Freie Suche
        if (!session::get("searchstart") and !session::get("searchexact")) $checkString = " checked='checked'";
        else $checkString = "";
        
        echo form::field("radio","searchtype",0,"",$checkString,"","frei","search-free");
        
// Suche am Wortanfang
        if (session::get("searchstart")) $checkString = " checked='checked'";
        else $checkString = "";

        echo form::field("radio","searchtype",1,"",$checkString,"","Wortanfang","search-start");

// exakte Suche
        if (session::get("searchexact")) $checkString = " checked='checked'";
        else $checkString = "";

        echo form::field("radio","searchtype",2,"",$checkString,"","exact","search-exact");


// search in comment field
        if (session::get("searchcom")) $checkString = " checked='checked'";
        else $checkString = "";
        echo form::field("checkbox","searchcom",1,"",$checkString,"","Erl&auml;uterungen","search-comment");


  // search only for ordered entries
    // create array for selector
        $typeArray = thesaurus::get_type_list();
        $statusArray = thesaurus::get_status_list();
        $ownerArray = user::get_users("entry");

        if (count($statusArray))
        {
          echo "<br>";
          echo form::selector("searchentrytype",$typeArray,1,"",session::get("searchentrytype"),""," Begriffstype ","searchtype","");
          echo form::selector("searchstatus",$statusArray,1,"",session::get("searchstatus"),""," Status ","searchstatus","");
          echo form::selector("searchowner",$ownerArray,1,"",session::get("searchowner"),""," Eigentümer mit Einträgen ","searchowner","");
        }
        else
          echo form::link("","<span class='small'>Keine beantragten Einträge</span>","","no-ordered");
        

// search field
        echo "<p><input type='text' size='35' name='searchString' value='" . session::get("search") . "' ";
        echo help::show("search-field","");
        echo ">";

                
        echo form::field("submit","action","suchen","",""," ","","search");
        echo form::field("submit","reset","zurücksetzen","",""," ","","newsearch");
      echo "</form></p>";
    echo "</div>";
  }


// display search result
  function display($searchResult)
  {
    if ($searchResult)
    {
      echo "<div id='result' class='search'>";
        echo "<b>Suchergebnis - </b>";
        echo " <i class='normal'>" . count($searchResult) . " Treffer</i>";

        echo form::link("delete","x","index.php?action=hidesearch","close-search");

        action::listit("search",$searchResult);

        foreach($searchResult as $entry)
        {
          $nameArray = thesaurus::get_descriptor($entry[ID]);
          export::descriptor($nameArray[ID],session::get("search"),"SHOW");
          echo "<hr>";
        }

    // set parent
        if (session::get(show) != 0) $parent = session::get(show);
        else $parent = system::getval("val_orderdefault");
        
    // sugest new entry if orderdefault is set or OB is displayed
    
        if ($parent)
          form::insert_entry(array('parent'=>$parent,'action'=>'add',""),array(),session::get(search));
        else echo "<p>Kein Oberbegriff für neuen Begriff gesetzt.</p>";

      echo "</div>";
    }
    else
    {
      echo "<div id='result' class='search'>";
        echo "<span class='red'>Kein Eintrag gefunden</span>";

        echo form::link("delete","x","index.php?action=hidesearch","close-search");

    // set parent
        if (session::get(show) != 0) $parent = session::get(show);
        else $parent = system::getval("val_orderdefault");
        
    // sugest new entry if orderdefault is set or OB is displayed
    
        if ($parent)
          form::insert_entry(array('parent'=>$parent,'action'=>'add',""),array(),session::get(search));
        else echo "<p>Kein Oberbegriff für neuen Begriff gesetzt.</p>";

      echo "</div>";
    }
  }
}

?>