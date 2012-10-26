<?PHP



function action($_arg)
{

//------------------------------------------------------------------------------
// extrace action from coordinate system
  while($entry = each($_arg))
  {
    $argArray = explode("_",$entry[key]);

    if (count($argArray) > 1)
    {
      
      $indexString = $argArray[0];
      $valueString = $argArray[1];
  
      $_arg[$indexString] = $valueString;

      if (isset($argArray[2])) $_arg['_ID'] = $argArray[2];
    }    
  }


//echoalert($_arg);
//echoalert($_SESSION);

//------------------------------------------------------------------------------
// parse reset value
  if ($_arg[reset])
  {
    $_arg = array();

    session::destroy(searchshow);    

    session::destroy(show);    
    session::destroy(search);
    session::destroy(searchtype);
    session::destroy(searchcom);
    session::destroy(searchorder);
    session::destroy(searchString);
    session::destroy(searchexact);
    session::destroy(searchstart);
    session::destroy(searchowner);

    session::destroy(searchentrytype);
    session::destroy(searchstatus);
  }


//------------------------------------------------------------------------------
// parse action parameter
  switch($_arg[action])
  {

//------------------------------------------------------------------------------
// login / out
    case login: // login user
      user::login($_arg[user],$_arg[password]);

// restore program status if new session
      restore_status();

// reset linking
      session::destroy("linkaction");
      session::destroy("link");
      break;
      
    case logout: // logout user
      user::logout();
      break;
      
    case changedo: // change password
      if ($password = $_GET[password])
      {
        database::query("UPDATE user SET password='" . md5($password) . "' WHERE ID='" . session::get("user") . "'");
        echojavascript("Passwort erfolgreich geändert");
      }
      break;



//------------------------------------------------------------------------------
// inherit entrytype to children
    case inherit:
      $childArray = thesaurus::get_child($_arg[id]);
      foreach($childArray as $entry)
      {
        database::query("UPDATE entry SET entrytype=$_arg[entrytype] WHERE ID=$entry");
      }
      break;


//------------------------------------------------------------------------------
    case update:
      if ($_arg[orderdefault]) session::set(orderdefault,$_arg[id]);
      elseif (isset($_arg[orderdefault])) session::destroy(orderdefault);
      break;


//------------------------------------------------------------------------------
    case edit:
      session::set("edit",TRUE);
      session::set("show",$_arg[id]);

      session::destroy("searchshow");
      break;
      
    case noedit:
      session::destroy("edit");
      break;
      


//------------------------------------------------------------------------------
    case open:
      session::open($_arg[id]);
      break;

    case close:
      session::close($_arg[id]);
      break;

    case closeall:
      session::close_all();
      break;



//------------------------------------------------------------------------------
    case deleteid:
      end_link();
      hide();
      database::delete($_arg[id]);
      break;



//------------------------------------------------------------------------------
    case suchen:
      if (!$_arg[searchString] and ($_arg[searchowner] or $_arg[searchtype] or $_arg[searchstatus]))
        $_arg[searchString] = "%";
        
      if ($_arg[searchString]) session::set("searchshow",true); // show search result
      
      session::set("search",$_arg[searchString]);
      session::set("searchcom",$_arg[searchcom]);
      session::set("searchorder",$_arg[searchorder]);
      session::set("searchentrytype",$_arg[searchentrytype]);
      session::set("searchstatus",$_arg[searchstatus]);
      
      if ($_arg[searchowner]) session::set("searchowner",$_arg[searchowner]);
      else session::destroy("searchowner");

    	switch ($_arg[searchtype])
    	{
    		case 0:
    			session::destroy("searchexact");
    			session::destroy("searchstart");
    			break;
    		case 1:
    			session::destroy("searchexact");
    			session::set("searchstart",TRUE);
    			break;
    		case 2:
    			session::destroy("searchstart");
    			session::set("searchexact",TRUE);
    			break;
    	}
    	break;


    case hidesearch:
      session::destroy(searchshow);
      break;

//------------------------------------------------------------------------------
    case show:
      session::destroy("searchshow");
      
      $_arg[linkaction] = "";
      
      if ($_arg[id] == NULL)
      {
        break;
      }
      elseif ($_arg[id] > 0)
      {
        session::set("show",$_arg[id]);
        break;
      }
      else
      {
        session::delete("show");
        break;
      }
      break;




//------------------------------------------------------------------------------
    case swap:
      if ($_arg[id])
      {
        thesaurus::swap_link($_arg[id],$_arg[_ID]);
      }
      break;

    case change:
      if ($_arg[id])
      {
//        thesaurus::change_link($_arg);
      }
      break;
      
      
    case add: // add new descriptor
      session::destroy("show");
      session::destroy("searchshow");
//      session::set("",1);
      break;
      


// clean database
    case correct:
      thesaurus::validate(true);
      echoalert("Datenbank bereinigt");
      break;


//------------------------------------------------------------------------------
// open hyrarchy down to selected entry
    case showhyrarchy:
      if ($_arg[id])
      {
        $hyrarchyArray = thesaurus::get_hyrarchy($_arg[id]);

// don't open selected entry
//        array_pop($hyrarchyArray);


        foreach($hyrarchyArray as $entry)
        {
//        echo $entry . " ";
          echo session::open($entry);     
        }

        session::set("hyrarchy",TRUE);

// hide search window
        session::destroy("searchshow");

// if nothing selected for display, show ID
        if (!session::get(show)) session::set("show",$_arg[id]);
        break;
      }




//------------------------------------------------------------------------------
// debug on/off
    case debugon:
      system::setval(debug,TRUE);
      break;
      
    case debugoff:
      system::setval(debug,FALSE);

// legend on/off
    case legendon:
      session::set("legend",TRUE);
      break;

    case legendoff:
      session::destroy("legend");
      break;

// display / hide non descriptors
    case toggleND:
      if (session::get("descriptor")) session::destroy("descriptor");
      else session::set("descriptor",TRUE);
      break;

// display / hide orders
    case toggleVI:
      if (session::get("visible")) session::destroy("visible");
      else session::set("visible",TRUE);
      break;


// toggle tooltips on/off
    case off:
      session::set("tooltips",TRUE);
      break;
    case on:
      session::destroy("tooltips");
      break;

// toggle hyrarchy
	case hyrarchyon:
		session::set("hyrarchy",TRUE);
		break;
	case hyrarchyoff:
		session::set("hyrarchy",FALSE);
		break;
  }  





//------------------------------------------------------------------------------
// parse linkaction parameter
  switch ($_arg[linkaction])
  {
// link
    case link:
      session::set("link",$_arg[id]);
      session::set("linkaction",$_arg[linkaction]);
      session::set("linktype",$_arg[linktype]);
      break;

// execute linking
    case linkdo:
      switch (session::get('linkaction'))
      {
        case link:
          database::parent_insert(session::get("link"),$_arg[id],session::get("linktype"));
          session::set("show",session::get("link")); // set display to linked objects

// with BS set linked descriptor to "no descriptor"
          if(session::get("linktype") == 2)
            database::set_desc($_arg[id],0);

//          session::destroy("link"); // end linking
          break;

        case change:
          database::link_change(session::get('linkparent'),session::get('link'),$_arg['id']); // parent,oldlink,newlink
          break;
      }
      break;

    case linkend:
      end_link();
      break;

// unlink
    case unlink:
      if ($_arg[id])
      {
        database::parent_delete(session::get("show"),$_arg[id]);
      }
      break;
      
// change OB
    case change:
      if ($_arg[id])
      {
        session::set("link",$_arg[id]);
        session::set("linkaction",$_arg[linkaction]);
        session::set("linkparent",$_arg[_ID]);
        session::set("linktype",$_arg[linktype]);
      }
      break;
  }


// TEMP SETTINGS
// if not link rights, set descriptor and visible to true
if (!right::link()) session::set(descriptor,FALSE);
//if (!right::link()) session::set(visible,TRUE);


// save program status
  save_status($_SESSION);
}



// end link action
function end_link()
{
  session::destroy("link");
  session::destroy("linktype");
  session::destroy("linkaction");
  session::destroy("linkparent");
}


// hide descriptor window
function hide()
{
  session::destroy("show");
}

?>