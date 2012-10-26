<?PHP




//-----------------------------------------------------------------------
// class for input / edit window
class form {

//-----------------------------------------------------------------------
// method for displaying field with parameters

// $type ... <input> type
// $name ... name of field
// [$value] ... preset value
// [$size] ... size of field
// [format] ... string with format
// [$prefix] ... text before text
// [$postfix] ... text behind field
// [$help] ... name of helptext

// field(type,name,value,size,format,prefix,postfix,help)

  function field($type,$name,$value="",$size="",$format="",$prefix="",$postfix="",$help="",$helpfree="")
  {
    if (right::field_view($help) or !$help or $type == "hidden")
    {
      if ($prefix) echo "<span class='normal'>$prefix</span> ";

      $retString = "";

// parse type
      switch ($type)
      {
        case image:
          $sizeString = "$size";
          break;
        
        default:
          $sizeString = "size='$size'";
      }
      
  		$retString .= "<input $format $sizeString type='$type' name='$name' value='$value' ";
  
    	if ($help or $helpfree) $retString .= help::show($help,$helpfree);
  		
      if (right::field_edit($help) or !$help or $type == "hidden") // editable: enable edit
      {
        $retString .= ">";
      }
      else // display only
      {
        $retString .= " disabled>";
      }
      
      if ($postfix) $retString .= " <span class='normal'>$postfix</span>";
    }
    return ($retString);
  }



//-----------------------------------------------------------------------
// method for displaying textarea with parameters

// $type ... <input> type
// $name ... name of field
// [$value] ... preset value
// [$cols] ... coloms of textarea
// [$rows] ... rows of textarea
// [format] ... string with format
// [$prefix] ... text before text
// [$postfix] ... text behind field
// [$help] ... name of helptext

// textfield(type,name,value,editable,size,format,prefix,postfix,help)

  function textfield($name,$value="",$cols="",$rows="",$format="",$prefix="",$postfix="",$help="",$helpfree="")
  {
    if (right::field_view($help))
    {
      $retString = "";

      if ($prefix) $retString .= "<span class='normal'>$prefix</span> ";
  		$retString .= "<textarea $format name='$name' cols='$cols' rows='$rows' ";
    
    		if ($help or $helpfree) $retString .= help::show($help,$helpfree);
    		
        if (right::field_edit($help)) // editable: enable edit
        {
          $retString .= ">";
        }
        else // display only
        {
          $retString .= " readonly>";
        }
        
        $retString .= $value;
      $retString .= "</textarea>";
      
      if ($postfix) $retString .= " <span class='normal'>$postfix</span>";
    }
    return ($retString);
  }


//-----------------------------------------------------------------------
// method for displaying selector
// selector(name,valueArray,size,format,selected,prefix,postfix,help,helpfree)

// valueArray ... array("value" => value for items,"text" => names for option text)

  function selector($name,$valueArray,$size="",$format="",$selected="",$prefix="",$postfix="",$help="",$helpfree="")
  {
    if (right::field_view($help) and is_array($valueArray))
    {
      if ($prefix) echo "<span class='normal'>$prefix</span> ";
      $retString = "";

  		$retString .= "<select $format size='$size' name='$name' ";
  
      	if ($help or $helpfree) $retString .= help::show($help,$helpfree);
    		
        if (right::field_edit($help) or $type == "hidden") // editable: enable edit
        {
          $retString .= ">";
        }
        else // display only
        {
          $retString .= " disabled>";
        }
        
        $retString .= "<option value=''></option>";
        foreach($valueArray as $entry)
        {
          $retString .= "<option value='" . $entry[value] . "'";
            if ($selected == $entry[value]) $retString .= " selected";
          $retString .= ">" . $entry[text] . "</option>";
        }  
      $retString .= "</select>";
      
      if ($postfix) $retString .= " <span class='normal'>$postfix</span>";
    }
    return ($retString);
  }

//-----------------------------------------------------------------------
// method for displaying a hyperlink <a> tag

// $image ... name of $image
// $text ... displayed linktext
// $uri ... adress of link
// 

  function link($image,$text="",$uri,$help="",$helpfree="",$name="")
  {
    if (right::field_view($help))
    {
      $retString = "";
      if ($name) $retString .= "<a name='anchor$name'></a>"; // insert anchor
      
      if (right::field_edit($help)) // editable: enable edit
      {
        $retString .= "<a href='$uri'";
    		  if ($help or $helpfree) $retString .= help::show($help,$helpfree);
          $retString .= ">";
    
          if ($image) $retString .= grafik::disp($image,$text,20);
          else $retString .= $text;
        $retString .= "</a>";
      }
      else
      {
        $retString .= $text;
      }
    }
    return ($retString);
  }





//-----------------------------------------------------------------------
// display insert formular
  function insert_entry($_arg,$errorArray,$value="")
  {
    if ($_arg[action] == 'add' or ($_arg[action] == 'insert' and $errorArray))
    {
// set default value
      if ($value) $_arg[name] = $value;

        echo "<form name='descriptor' method='get' action='index.php'>";

          $name = thesaurus::name($_arg[parent]);
          if ($name)
          {
            echo "<p><b>Neuen Unterbegriff in '<span class='red'>";
              echo "<a href='index.php?action=show&amp;id=$_arg[parent]'>$name</a>";
            echo "</span>' ";
          
            if (right::link()) echo "anlegen";
            else echo "vorschlagen";
            
            echo "</b></p>";
          }
          else echo "<p><b>Neuen Thesaurus anlegen</b></p>";


          $error = $errorArray[name];

// name of new descriptor
          echo form::field("text","name",$_arg[name],30,"class='$error'","<span class='small'>Name</span><br>","","name-field");

          switch($error)
          {
            case exist:
              echo "<br><b class='exist'>Eintrag existiert schon</b>";
              echo form::link(""," Eintrag ansehen","index.php?action=suchen&amp;searchString=$_arg[name]&amp;searchtype=2","showexist");
              break;
              
            case alike:
/*              echo "<br><b class='exist'>Ähnliche Einträge existiert schon</b>";
              echo form::link(""," Einträge ansehen","index.php?action=suchen&amp;searchString=$_arg[name]&amp;searchtype=1","showalike");
*/              break;

            default:
              break;
          }

          echo "<br>";
          
// type of descriptor
/*            $typeArray = thesaurus::get_type_list();
            $entryType = thesaurus::get_entrytype($_arg[parent]);
            echo form::selector("entrytype",$typeArray,1,"",$entryType[entrytype],"<span class='small'>Begriffstype</span><br>","","entrytype");
*/


            echo "<br>";
          

// status of descriptor
          if (right::write())
          {
            $statusArray = thesaurus::get_status_list();
            echo form::selector("status",$statusArray,1,"",thesaurus::get_status($id),"<span class='small'>Status</span><br>","","statustype");
          }
          else
          {
            echo "<span class='small'>Status</span><br><span class='normal'>" . thesaurus::get_status_name(thesaurus::newstatus()) . "</span>";
            echo form::field("hidden","status",thesaurus::newstatus());
          }
          echo "</p>";

          $error = $errorArray[comment];

// comment field
          echo form::textfield("comment",$_arg[comment],45,3,"class='$error'","Bemerkungen<br>","","comment-field");

          
          echo form::field("submit","","anlegen","","","","","new");

          echo form::field("hidden","parent",$_arg[parent]);
          echo form::field("hidden","id",$_arg[id]);
          echo form::field("hidden","action","insert");
          echo form::field("hidden","type",1);

      echo "</form>";
    }
  }




//-----------------------------------------------------------------------
// display and edit entry
  function update_entry($_arg,$errorArray)
  {
    if ($id = session::get("show") or hyrarchy::errors($errorArray))
    {
      $parentArray = thesaurus::get_descriptor($id);

        if (right::write() and session::get("edit"))
        {
        	echo "<form method='get' name='descriptor' action='index.php'>";
      // basic data
            form::descriptor($id,$parentArray[name],$errorArray[name]);
            form::comment($id,$parentArray[comment],$errorArray[comment]);
    
// field(type,name,value,editable,size,format,prefix,postfix,help)

            echo form::field("submit","","speichern","","","","","save");

        		echo form::field("hidden","id",$id);
        		echo form::field("hidden","action","update");
          echo "</form>";
      
      // links
          form::equivalent_links($id);
          form::hyrarchic_links($id);
          form::sub_links($id);
          form::assoc_links($id);
        }
      
// display preview
        echo "<div id='preview'>";
          export::descriptor($id,"","SHOW");
        echo "</div>";

    }
  }



//-----------------------------------------------------------------------
// Form for Deskriptor input
  function descriptor($id,$name,$error)
  {
    echo "<fieldset><legend>";
//-----------------
        if (!thesaurus::is_descriptor($id))
        {
          $checkString = "";
          echo "kein ";
        }
        else $checkString = " checked";
        echo "Begriff";
        
        if (!thesaurus::is_visible($id))
        echo " <span class='red'>(versteckt)</span>";

      echo "</legend>";


// descriptor value
      echo form::field("text","name",$name,30,"class='$error'","","",$help="name-field");

// display ID
      echo "<span class='small'>ID = $id</span>";

// open tree
      echo form::link("opentree","[+]","index.php?action=showhyrarchy&amp;id=$id","opentree");

// exit edit mode
      if (right::write())
      {
        action::edit();
      }

// delete entry
      if (thesaurus::child_num($id,1) == 0) // no links to entry -> make delete possible
      {
        $javaText = "Wollen Sie " . $name . " wirklich l&ouml;schen? Es werden auch alle Links zu diesem Eintrag gel&ouml;scht";
        echo form::link("delete","x","javascript:get_confirm(&#34;$javaText&#34;,&#34;index.php?action=deleteid&id=$id&#34;);","delete");
      }


      echo "<br>";

// type of descriptor
/*      $typeArray = thesaurus::get_type_list();
      $entryType = thesaurus::get_entrytype($id);
      $entryTypeName = thesaurus::get_entrytype_name($entryType);

      $javaText = "Wollen Sie wirklich allen Unterbegriffen von " . $name . " den Begriffstyp " . $entryTypeName . " zuweisen?";
      echo form::selector("entrytype",$typeArray,1,"",$entryType,"<span class='small'>Begriffstype</span><br>","","entrytype");

  // herite to all childs
      echo form::link("inheritance"," Begriffstyp vererben","javascript:get_confirm(&#34;$javaText&#34;,&#34;index.php?id=$id&amp;entrytype=$entryType&amp;action=inherit&#34;);","inheritance");
*/

      echo "<span class='normal'><i>" . thesaurus::get_name(thesaurus::get_thesaurus($id)) . "</i></span>";

      echo "<br>";

// status of descriptor
      $statusArray = thesaurus::get_status_list();
      echo form::selector("status",$statusArray,1,"",thesaurus::get_status($id),"<span class='small'>Status</span><br>","","statustype");

      echo "<br>";

// descriptor is default value for ordered entries

  // default place for kandidates defined
      $orderDefault = system::getval("val_orderdefault");
      if ($orderDefault)
      {
        if (intval($orderDefault) == $id) $checkedString = "checked='checked'"; // selected entry for kandidate parent
        else
        {
          $defaultString = "<br>(derzeit: <i>'";
            $defaultString .= "<a href='index.php?id=$orderDefault&amp;action=show'>";
              $defaultString .= thesaurus::get_name($orderDefault);
            $defaultString .= "</a>";
          $defaultString .= "'</i>)";
        }
      }
      else
        $defaultString = "<br>(Kein Ort für Kandidaten festgelegt)";
      
      echo form::field("checkbox","orderdefault",$id,"",$checkedString,"","als Standard für Kandidaten festlegen $defaultString","orderdefault");
    echo "</fieldset>";
  }



//-----------------------------------------------------------------------
// form for equivalent link
  function equivalent_links($id)
  {
    $tempArray = thesaurus::get_equiv($id);
    echo "<form method='get' action='index.php' name='eq'>";
      echo "<fieldset>";
        echo "<legend>Synonyme</legend>";

        $x = 0;
        $listArray = array();
        if ($tempArray)
        {
          foreach($tempArray as $entry)
          {
            $synonym = thesaurus::get_descriptor($entry);

            if ($entry > 0) $synonym[name] = "BS " . $synonym[name];
            else $synonym[name] = "BF " . $synonym[name];

            $listArray[$x++] = $synonym;
          }
        }
        echo_selection($listArray,"id","",3,"equal-field");

  // display action line
        action::display($id,2);

      echo "</fieldset>";
    echo "</form>";    
  }



//-----------------------------------------------------------------------
// get list of hyrarchic links
  function hyrarchic_links($id)
  {
    $tempArray = thesaurus::get_parent($id);
    if ($tempArray)
    {
      echo "<form method='get' action='index.php' name='ob'>";
        echo "<fieldset>";
          echo "<legend>Oberbegriffe</legend>";
  
          $x = 0;
          $listArray = array();
          foreach($tempArray as $entry)
          {
            $listArray[$x++] = thesaurus::get_descriptor($entry);
          }

          echo_selection($listArray,"id","",3,"upper-field");

  // display action line
          action::display($id,1);

        echo "</fieldset>";
      echo "</form>";
    }
  }


//-----------------------------------------------------------------------
// get list of hyrarchic links
  function sub_links($id)
  {
    $tempArray = thesaurus::get_child($id,1);
    echo "<form method='get' action='index.php' name='ub'>";
      echo "<fieldset>";
        echo "<legend>Unterbegriffe</legend>";

        $x = 0;
        $listArray = array();
        if ($tempArray)
        {
          foreach($tempArray as $entry)
          {
            $listArray[$x++] = thesaurus::get_descriptor($entry);
          }
        }
        echo_selection($listArray,"id","",3,"lower-field");

  // display action line
        action::display($id,"");

      echo "</fieldset>";
    echo "</form>";
  }


//-----------------------------------------------------------------------
// get array of associative links
  function assoc_links($id)
  {
    $tempArray = thesaurus::get_assoc($id);
    echo "<form method='get' action='index.php' name='vb'>";
      echo "<fieldset>";
        echo "<legend>Verwandte Begriffe</legend>";

        $x = 0;
        $listArray = array();
        if ($tempArray)
        {
          foreach($tempArray as $entry)
          {
            $listArray[$x++] = thesaurus::get_descriptor($entry);
          }
        }

        echo_selection($listArray,"id","",3,"assoc-field");

  // display action line
        action::display($id,3);

      echo "</fieldset>";
    echo "</form>";
  }


//-----------------------------------------------------------------------
// Comments
  function comment($id,$name,$error)
  {
    echo "<fieldset><legend>Erl&auml;uterungen</legend>";

			echo "<textarea class='$error' name='comment' cols='45' rows='5' ";
      echo help::show("comment-field","");
      if (right::write()) // write permission: enable edit
      {
        echo ">";
      }
      else // display only
      {
        echo " readonly>";
      }
  			echo $name;
			echo "</textarea>";

  // display legend for comment area
      if (session::get("legend"))
      {
        echo form::link("help-off","no help","index.php?action=legendoff","nolegend");

        form::legend();
      }
      else
      {
        echo form::link("help-on","help","index.php?action=legendon","nolegend");
      }

    echo "</fieldset>";
  }







//-----------------------------------------------------------------------
// list legend for info field
  function legend()
  {
    echo "<table>";
      echo "<tr><th>K&uuml;rzel</th><th>Bedeutung</th></tr>";
      echo "<tr><td align='center'><b>W:</b></td><td>Wortformen und Schreibweisen in Deutsch</td></tr>";
      echo "<tr><td align='center'><b>E:</b></td><td>Bezeichnung in Englisch</td></tr>";
      echo "<tr><td align='center'><b>D:</b></td><td>Begriffsdefinition</td></tr>";
      echo "<tr><td align='center'><b>H:</b></td><td>Erl&auml;uterung</td></tr>";
      echo "<tr><td align='center'><b>Q:</b></td><td>Quellenangabe</td></tr>";
    echo "</table>";
  }




// show standard info page
	function standard()
	{
?>
		<div id='main'>

			<p><b>OpenThesaurus</b></p>

			<p>
      Thesauri sind meist hirarchisch geordnete Sammlungen exakter Ausdrücke.
      Zu jedem Begriff können Synonyme abgelegt, die alternative Beschreibungen des Ausdrucks sind, sowie hirarchische oder assoziative Verknüpfungen definiert werden.
      </p>

      <p>
      Der OpenThesaurus stellt ein Werkzeug zur Verfügung solche Strukturen anzulegen und zu verwalten.
      Eine Suche ermölicht alle Felder zu durchsuchen, um schliesslich den passenden, exakten Ausdruck zu erhalten.
      </p>
      
      <p><b>
      Zur Nutzung muss man sich anmelden.
      </b></p>

		</div>
<?PHP
	}
}




?>