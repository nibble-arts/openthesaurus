<?PHP

// ****************************************************************
// displays hyrarchy-tree
class hyrarchy {

// displays a list starting from $parent with a recursive $depth
// $parent ... parent to start from
// $depth ... depth to be displayed
  function display($parent,$depth,$pos,$errorArray)
  {

// V2.0
// starting on thesaurus level
    if ($parent == 0)
    {
      $elementArray = thesaurus::get_thesaurus_list(); // get thesauri
    }
// get list of children
    else $elementArray = thesaurus::get_child($parent); // get children

    // entries found
    if (is_array($elementArray))
    {
      $cntElements = count($elementArray);
// start new line
      if (!isset($pos)) $pos = 0; // set position to 0
      $line = new line;


// ****************************************************************
// start array
      $lineCnt = 0;
      foreach($elementArray as $id)
      {

// don't display if
  // non descriptor
  // or not visible
  
//        if ((thesaurus::is_descriptor($id) or session::get("descriptor"))
//          and (thesaurus::is_visible($id) or session::get("visible"))
//          )
          if (thesaurus::is_visible($id) or session::get("visible"))
          {
            $cntParents = thesaurus::parent_num($id);
            
            $equivArray = thesaurus::get_equiv($id,"BS");
            if (is_array($equivArray)) $cntEquiv = count ($equivArray);
              else $cntEquiv = 0;

    // get count and list of subentries
            $childrenArray = thesaurus::get_child($id);
            $cntChildren = thesaurus::child_num($id,session::get(descriptor));

    // get entry details
            $entry = thesaurus::get_descriptor($id);

  
    // ECHO ENTRY
      // space in front
            $x=0;
            while($x < $pos - 1)
            {
              $line->insert(grafik::disp("line","|",20),20); // insert line-grafik
              $x++;
            }
            
            
      // search elements in next level enabled
            if (($depth > $pos or $depth == -1))
            {
    
      // display tree grafik
              if ($pos > 0)
              {
                if ($lineCnt++ < $cntElements-1) $line->insert(grafik::disp("subtree","|",20),20); // insert subtree icon
                else $line->insert(grafik::disp("subend","|",20),20); // insert sub end icon
              }
              
      // display open / close icon if subobjects
              if ($cntChildren != 0)
              {
      // subobjects found -> display icon to open / close
                if (thesaurus::is_open($id))
                {
                  if ($pos > 0)
                    $tempString = form::link("minus","[+]","index.php?id=$id&amp;action=close","close");
                  else
                    $tempString = form::link("thesaurus-open","[+]","index.php?id=$id&amp;action=close","close");
                }
                else
                {
                  if ($pos > 0)
                    $tempString = form::link("plus","[+]","index.php?id=$id&amp;action=open","open");
                  else
                    $tempString = form::link("thesaurus","[T]","index.php?id=$id&amp;action=open","open");
                }
              }
              else
              { $tempString = grafik::disp("space"," ",20); }
  
              $line->insert($tempString,20); // insert navigation symbols
              $tempString = "";
    
  
  	// Display parent and child link icon
              if (session::get("show")) // element selected -> show parents and childs
              {
  
  	// display parent arrow
                if (thesaurus::is_child_of(session::get("show"),$id) and session::get("show")) // supres loop links
                {
                  $line->insert(grafik::arrow("right","blue",15),20); // display parent arrow
                }
  
  	// display link-arrow
                if (thesaurus::is_parent_of(session::get("show"),$id)) // supres loop links
                {
                  $line->insert(grafik::arrow("right","orange",15),20);
                }
  
  	// display associate links
            		if (thesaurus::is_assoc_of(session::get("show"),$id))
            		{
            			$line->insert(grafik::disp("assoc","=",20),20);
            		}
  
              }
  
  
  
// entry name
              $textLength = strlen($entry[name]) * 15;
              $textString = $entry[name];
              
          // set style for selection
              $styleString = "";

              if (thesaurus::is_equivalent($id)) $styleString = "class='bright'";

              if (thesaurus::is_descriptor($id)) $textString = strtoupper($textString);

              if ($id == session::get("show")) $styleString = "class='select'";
              elseif ($cntParents > 1) $styleString = "class='multiple'";

              if (!thesaurus::is_visible($id)) $styleString = "class='red'";
              if (thesaurus::is_deleted($id)) $styleString = "class='through'";
  
    // draw name with link
              if ($pos == 0)
                $tempString .= form::link("","<b><span $styleString>" . $textString . "</span></b>","index.php?id=$id&amp;action=show","name","",$id);
              else
                $tempString .= form::link("","<span $styleString>" . $textString . "</span>","index.php?id=$id&amp;action=show","name","",$id);
  
    // number of sub-objects
              if ($cntChildren)
              {
                $subText = "";
                $listCnt = 0;
                foreach($childrenArray as $entry) // create list of subentries
                {
                  $subText .= thesaurus::get_name($entry) . "<br>";
                }
                
                $tempString .= "<span ";
                $tempString .= help::show("elementcount",$subText) . ">";
                $tempString .= "<i class='small'>";
                if ($cntChildren) $tempString .= " ($cntChildren UB)</i>";
                $tempString .= "</span>";
              }


    // number of equiv-objects
              if ($cntEquiv)
              {
                $equivText = "";
                $listCnt = 0;
                foreach($equivArray as $entry) // create list of subentries
                {
                  $equivText .= thesaurus::get_name($entry) . "<br>";
                }

                $tempString .= "<span ";
                $tempString .= help::show("equivcount",$equivText) . ">";
                $tempString .= "<i class='small'>";
                if ($cntEquiv) $tempString .= " ($cntEquiv BS)</i>";
                $tempString .= "</span>";
              } // Count of containing elements
    
    // show owner
              $ownerID = thesaurus::get_owner($entry[ID]);

              $infoText = "ID: " . $id;
              $infoText .= "<br>Status: " . thesaurus::get_status_name(thesaurus::get_status($id));
//              $infoText .= "<br>Begriffstyp: " . thesaurus::get_entrytype_name(thesaurus::get_entrytype($id));
              $infoText .= "<br>Ersteller: " . user::name($ownerID);
              
              $tempString .= " " . form::link("","?","","owner",$infoText);
  
      // Edit Link
              $tempString .= form::link("add","add","index.php?parent=$id&amp;action=add","add");
  
      // Delete Link
              if ($cntChildren == 0) // no links to entry -> make delete possible
              {
                $javaText = "Wollen Sie " . $entry[name] . " wirklich l&ouml;schen? Es werden auch alle Links zu diesem Eintrag gelöscht";
                $tempString .= form::link("delete","x","javascript:get_confirm(&#34;$javaText&#34;,&#34;index.php?action=deleteid&id=$id&#34;);","delete");
              }
              
      // Link Link
              if (session::get("link") and $id != session::get("link")) // supress link to itself
              {
                if (!thesaurus::is_parent_of(session::get("link"),$id) or session::get(linkaction) == "change") // supres loop links
                {
                  $linkType = "do" . thesaurus::get_type_name(session::get("linktype"));
                  
                  $tempString .= form::link($linkType,"L","index.php?id=$id&amp;linkaction=linkdo","$linkType");
                }
              }

              $line->insert($tempString); // Insert text in line
              $tempString = "";
  
    // recursive display subtree
            if (thesaurus::is_open($id))
            {
    // recursive call of level
              if (thesaurus::child_num($id,1))
              {
                hyrarchy::display_line($line);
                hyrarchy::display($id,$depth,$pos+1,$errorArray);
              }
            }
          }
  
  // display line
          hyrarchy::display_line($line);
        }
      }
    }
  }
  
  
  function display_line($line)
  {
    if ($line->lineString != "")
    {
      echo "<div id='line'>" . $line->lineString . "</div>";
    }    
    $line->reset();
  }


// displays hyrarchy header
  function header()
  {
    	echo "<b class='big'>";

    	if (!thesaurus::is_thesaurus(session::get("link")))
        {
          echo form::link("","Hierarchie","index.php?action=show&amp;id=0&amp;linkaction=linkdo");
        }
    	else echo "Hierarchie"; // title line
      echo "</b>";
    	
   
    
  // insert thesaurus
        if (right::link()) // new only if write permission
          echo form::link("add","add","index.php?action=add&amp;parent=0","add-thesaurus");

  
  
    // close complete tree
        action::closeall();  

  // Show Link activity
        if ($linkID = session::get("link"))
        {
          echo "<br><i><span class='red'>";

          $linktype = thesaurus::get_type_short(session::get("linktype"));

          if (session::get('linkaction') == "change")
          {
            $linkArray = thesaurus::get_descriptor($linkID);
            $parentArray = thesaurus::get_descriptor(session::get("linkparent"));

            echo "<b>" . $linktype . " '" . $linkArray[name] . "'</b> von <b>'" . $parentArray[name] . "'</b> ändern";
          }
          else
          {
            $linkArray = thesaurus::get_descriptor($linkID);

            echo "Neuen <b>" . $linktype . "</b> f&uuml;r <b>'$linkArray[name]'</b> verlinken";
          }
                    
          echo "</span><span class='normal'><br>Aktion beenden ";
          echo form::link("delete","end","index.php?linkaction=linkend","end-linking");
        
          echo "</i>";
        }
        
    echo "<hr align='left' width='400px'>";
  }


  
// set default fields in errorArray
// return array
  function set_default($errorArray,$mandatoryArray)
  {
    foreach($mandatoryArray as $entry)
    {
      if (!isset($errorArray[$entry])) $errorArray[$entry] = "default";
    }
    return($errorArray);
  }
  
  
// check if errorArray has error message
  function errors($errorArray)
  {
    if (is_array($errorArray)) return(in_array("error",$errorArray));
    else return(FALSE);
  }

}

?>