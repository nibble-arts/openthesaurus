<?PHP

include_once("hyrarchy.php");
// global


// intelligent display
function echoall($string)
{
  echo "<span class='normal'>";
    if (is_array($string) or is_object($string))
    {
      echo count($string) . " elements";
      echoarray($string);
    }
      
    switch ($string)
    {
      case NULL:
        echo "*NULL";
        break;
  
      case "":
        echo "*empty";
        break;
  
      case FALSE:
        echo "*FALSE";
        break;
  
      default:
        echo $string;
        break;
    }
    
    echo "<br></span>";
}


// displays array in code-style
function echoarray($entry)
{
  echo "<pre>";
  print_r($entry);
  echo "</pre>";
}


// display debug info
function echodebug($text)
{
  if (session::get("debug"))
  {
    echo "<div id='debug'>";
      echo "<b>Debug information in ";
      echo $_SERVER['PHP_SELF'] . "</b><br>";

      echoall($text);
    echo "</div>";
  }
}


function echojavascript($text)
{
?>
  <script language="JavaScript" type="text/javascript" >alert(
  <?PHP
  echo "'$text'";
  ?>
  )</script>
<?PHP
}


function echoalert($text)
{
  echo "<div id='alert'>";
    echoall($text);
  echo "</div>";
}


function highlight($needle,$string,$isDescriptor = "",$format = "")
{
// write descriptor in versales
  if ($isDescriptor) $string = strtoupper($string);

  if ($needle)
  {
    $start = 0;
    $pos = 0;
    $x = 0;

// split string to array
    while (($pos = strpos(strtoupper($string),strtoupper($needle),$start)) or (strtoupper(substr($string,0,strlen($needle)))) == strtoupper($needle) and $x == 0) // 
    {
      $tempArray[$x] = substr($string,$start,$pos);
      $needleArray[$x++] = substr($string,$start+$pos,strlen($needle));
      $start = $pos + strlen($needle);
    }
    $tempArray[$x] = substr($string,$start);

// recreate string from array
    $string = "";
    if ($tempArray)
    {
      for ($y = 0;$y <= $x;$y++)
      {
        $string .= $tempArray[$y];
        if ($x != $y) $string .= "<span class='red'>" . $needleArray[$y] . "</span>";
      }
    }

    return("<span class='$format'>$string</span>");
  }
// write descriptor in versales
  else return("<span class='$format'>$string</span>");
}


// display selection
// $itemArray ... items to select
// $name ... name of selection
// $default ... ID od selected item
// $size ... number of displayed lines
function echo_selection($itemArray,$name,$default,$size,$help)
{
  if (!$default) $default = 1; // set standard to hyrarchic

  echo "<select class='selection' name='$name' width='20' size='$size'";
    echo help::show($help,"");
    echo ">";

    if ($prefix) $prefix = "$prefix.";
    foreach($itemArray as $entry)
    {
      echo "<option value='$prefix" . $entry[ID] . "'";
      if ($entry[ID] == $default) echo " selected>";
      else echo ">";
        echo $entry[name];
      echo "</option>";
    }
  echo "</select>";
}







//-----------------------------------------------------------------------
// class for output of formatted line

class line {
  var $pos = 0;
  var $lineString = "";
  
// inserts cell into line
// returns new position in line
  function insert($data,$length = 0)
  {
  // div start with length
    $this->lineString .= "<div id='cell' style='";
    if ($length) $this->lineString .= "width:" . $length . "px;"; // set lenght if defined
      $this->lineString .= "left:" . $this->pos . ";";
    $this->lineString .= "'>";

    $this->pos += $length;

  // data
    $this->lineString .= $data;

  // div end
    $this->lineString .= "</div>";

    return($this->pos);
  }
  
  function reset()
  {
    $this->pos = 0;
    $this->lineString = "";
  }
}




//-----------------------------------------------------------------------
// class for output of action symbols
// $linktype ... 1 = OB
//               2 = BS/BF
//               3 = VB

class action
{

  function display($id,$linktype)
  {
    echo "<table width='100%'>";
      echo "<tr>";
        echo "<td>";

  // open tree to selected parent
          action::open_tree();

  // open deskriptor
          action::show();

          if (right::link())
          {
      // swap direktion for equivalent links
              if ($linktype == 2 and right::write())
              action::swap($id);
              
      // change OB
              if ($linktype == 1 and right::write())
              action::change($id);
          }
        echo "</td>";


        if (right::link())
        {
          echo "<td align='right'>";
          	if(right::write())
          	{
    // make new parent link
              action::add_link($id,$linktype);
              
              if ($linktype == 1) $minimum = 1;
              else $linktype == 0;
    // unlink selected parent
          		if (thesaurus::parent_num($id) > $minimum) action::unlink();
          	}
          echo "</td>";
        }
      echo "</tr>";
    echo "</table>";
  }


// create new link to linktype
  function add_link($id,$linktype)
  {
    if ($linktype > 0)
    {
      $typename = thesaurus::get_type_name($linktype);

// make new parent link
      echo form::link($typename,"new","index.php?id=$id&amp;linkaction=link&amp;link=" . $id . "&amp;linktype=$linktype","newlink_$linktype");
    }
    else
      echo form::link("add","+","index.php?action=add&amp;parent=$id","add");
  }
  

// unlink
  function unlink()
  {
// unlink selected parent
    echo form::field("image","linkaction.unlink","","width='20px'","src='images/delete.gif'","","","unlink");
  }



// open tree in formular
  function open_tree()
  {
    echo form::field("image","action.showhyrarchy","","width='20px'","src='images/opentree.gif'","","","opentree");
  }


// close all trees
  function closeall()
  {
    echo form::link("closeall","[-]","index.php?action=closeall","closeall");
  }


// show entry in formular
  function show()
  {
    echo form::field("image","action.show","","width='20px'","src='images/up.gif'","","","name");
  }



// change direction of equivalent link BF <-> BS
  function swap($id)
  {
    echo form::field("image","action.swap.$id","","width='20px'","src='images/swap.gif'","","","swap");
  }

// change OB
  function change($id)
  {
    echo form::field("image","action.change.$id","","width='20px'","src='images/change.gif'","","","change");
    
    echo form::field("hidden","linkparent","$_ID");
    echo form::field("hidden","linkaction","change");
    echo form::field("hidden","linktype","1");
  }

// show - hide nondescriptor
  function descriptor()
  {
    if (session::get("descriptor"))
      echo form::link("descriptor","D","index.php?action=toggleND","show-all");
    else
      echo form::link("nondescriptor","ND","index.php?action=toggleND","show-desc");
  }

// show - hide ordered
  function visible()
  {
    if (session::get("visible"))
      echo form::link("visible","V","index.php?action=toggleVI","show-order");
    else
      echo form::link("nonvisible","NV","index.php?action=toggleVI","hide-order");
  }


// if write permission
// display edit icon
  function edit()
  {
    if (right::write())
    {
      if (session::get("edit"))
        echo form::link("endedit","edit","index.php?action=noedit","noedit");
      else
        echo form::link("edit","edit","index.php?action=edit","edit");
    }
  }
  
  
// display - hide hyrarchy
  function hyrarchy()
  {
    if (session::get("hyrarchy"))
      echo form::link("opentree","Hirarchie aus","index.php?action=hyrarchyoff","hyrarchyoff");
    else
      echo form::link("notree","Hirarchie ein","index.php?action=hyrarchyon","hyrarchyon");
  }


// show - hide tooltips
  function tooltips()
  {
    if (!session::get("tooltips"))
      echo form::link("help-on","help","index.php?action=off","tooltipsoff");
    else
      echo form::link("help-off","nohelp","index.php?action=on","tooltipson");
  }


// print list
  function listit($type = "all",$filter = "")
  {
    if ($filter)
    {
      $filterArray = array();
      $filterString = "";

      foreach($filter as $entry)
      {
        array_push($filterArray,$entry[ID]);
      }
      
      $filterString = implode($filterArray,",");
    }

    echo form::link("print","P","print.php?action=$type&amp;filter=$filterString","print");
    
    echo form::link("","csv","csv.php?id=0&type=csv") . " ";
    echo form::link("","txt","csv.php?id=0&type=txt") . " ";
  }


// debugmode
  function debug()
  {
    if (!system::getval(debug))
      echo form::link("nodebug","Wartung ein","index.php?action=debugon","debugon");
    else
      echo form::link("debug","Wartung aus","index.php?action=debugoff","debugoff");
  }
}




// class for display icons
class grafik {

  function disp($name,$alt,$size)
  {
    return("<img src='images/$name.gif' alt='$alt' height='$size'>");
  }


  function arrow($direction,$color,$size)
  { return("<img src='images/$direction-$color.gif' alt='->' height='$size' valign='top'>"); }

}





class export
{
// print all descriptors / non descriptors
// descriptor = 0 ... print all
// descriptor = 1 ... only descriptors
  function print_all($descriptor)
  {
    echo "<h1>Liste aller Deskriptoren</h1>";
    echo "<p><i>Stand vom " . date("d. m Y, H:i:s") . "</i></p>";
    
    if (!$descriptor) $descString = " WHERE entry.status=statustype.ID and statustype.descriptor='1'";
    
    $descriptorArray = fetch_to_array(database::query("SELECT * FROM entry,statustype $descString ORDER BY name"),"");
    
    foreach($descriptorArray as $entry)
    {
      export::descriptor($entry[ID],"");
      echo "<br>";
    }
  }


// print search result
  function print_search($searchString)
  {
    echo "<h1>Liste der Deskriptoren aus der Suche nach <i>'" . session::get("search") . "'</i></h1>";
    
    echo "<p><b>Suchparameter:</b><br>";
    echo "<i>Stand vom " . date("d. M Y, H:i:s") . "</i></p>";
    
    echo "<ul>";
      if (session::get("searchexact")) echo "<li>Exakte Suche</li>";
      elseif (!session::get("searchstart")) echo "<li>Freie Suche</li>";
      else echo "<li>Suche am Wortanfang</li>";
  
      if (session::get("searchcom")) echo "<li>Suche auch in Erläuterungen</li>";

      if (session::get("searchentrytype"))
        $tempArray = thesaurus::get_entrytype_name(session::get("searchentrytype"));
        echo "<li>Suche nach Begriffstyp '" . $tempArray[type] . "'</li>";

      if (session::get("searchstatus"))
        echo "<li>Suche nach Status '" . thesaurus::get_status_name(session::get("searchstatus")) . "'</li>";

      if (session::get("searchstatus"))
        echo "<li>Suche nach Benutzer '" . user::name(session::get("searchowner")) . "'</li>";
    echo "</ul>";

    echo "<br>";
        

    $entryArray = explode(",",$searchString);
    foreach($entryArray as $element)
    {
      $descriptorArray = fetch_to_array(database::query("SELECT * FROM entry WHERE ID='$element'"),"");
      
      foreach($descriptorArray as $entry)
      {
        export::descriptor($entry[ID],$searchString);
        echo "<hr>";
      }
    }
  }
  


// print tree starting at id
  function print_tree($descId,$type="",$depth="")
  {
    switch($type)
    {
      case txt:
        $delimitor = "*";
        break;
      case csv:
        $delimitor = ";";
        break;

      case adlib:
        $adlibString = adlib($descId,$type);
        return $adlibString;
        break;
    }

// display id entry
    $comment = thesaurus::get_comment($descId);
    
// remove \r\n
    $comment = str_replace("\n",'',$comment);
    $comment = str_replace("\r",'',$comment);

    $paramArray = explode(";",$comment);

    $idArray = explode("=",$paramArray[0]);
    $rootArray = explode("=",$paramArray[1]);
    $commentArray = explode("=",$paramArray[2]);

    $levelCnt = 7;

// insert title
    if (!$depth)
    {
      
      $treeString .= $idArray[0] . $delimitor;
      for ($i=0;$i<$levelCnt;$i++)
      {
        $treeString .= "Level $i" . $delimitor;
      }
      $treeString .= $delimitor . $rootArray[0] . $delimitor . $commentArray[0] . "\r\n";
    }

// insert id at beginning
    $treeString .= $idArray[1] . $delimitor;
    
// insert blank tree elements on beginn
    $i = 0;
    while($i++ < $depth-1) { $treeString .=  $delimitor; }
    if ($type == "txt") $treeString .=  " "; // whitespace in textfile

// display entry
    $treeString .= thesaurus::get_name($descId);

// insert blank tree elements at end
    while($i++ < $levelCnt) { $treeString .=  $delimitor; }
    if ($type == "txt") $treeString .=  " "; // whitespace in textfile

// insert root entry
    $treeString .= $delimitor . "$rootArray[1]";

// insert comment entry
    $treeString .= $delimitor . "$commentArray[1]";

// insert crlf
    $treeString .= "\r\n";

    $childArray = thesaurus::get_child($descId);

// loop all childs recursive
    if (is_array($childArray))
    {
      foreach($childArray as $entry)
      {
        $treeString .= export::print_tree($entry,$type,$depth+1);
      }
    }
    return($treeString);
  }
  
  
// print descriptor information

// highlight ... string to be highlighted
// view ... display mode: EDIT => view edit icons
  function descriptor($id,$highlight = "",$view = "")
  {
  	echo "<div id='export'>";

      $dataArray = thesaurus::get_descriptor($id);
      $linkArray = thesaurus::get_link($id,session::get(descriptor));
      $separator = FALSE;
      $isDescriptor = thesaurus::is_descriptor($id);

//------------------------------------------------------------------------------  
// icons for actions
      echo "<p>";
        if ($view)
        {
          echo "<div id='box'>";
            echo "<table width='100%'><tr>";
              echo "<td>";
                echo form::link("opentree","[+]","index.php?action=showhyrarchy&amp;id=$id","opentree");
    
    // open hyrarchy tree
                echo " " . form::link("edit","edit","index.php?action=edit&amp;id=$id","edit");
    
    // link icons
                echo " " . form::link("link","OB","index.php?linkaction=link&amp;link=$id&amp;id=$id&amp;linktype=1","newlink_1");
                echo " " . form::link("equiv","BS","index.php?linkaction=link&amp;link=$id&amp;id=$id&amp;linktype=2","newlink_2");
                echo " " . form::link("assoc","VB","index.php?linkaction=link&amp;link=$id&amp;id=$id&amp;linktype=3","newlink_3");

                echo " " . form::link("add","add","index.php?action=add&amp;parent=$id","add");
              echo "</td>";

    // print tree
              echo "<td>";
                echo " " . form::link("","txt","csv.php?id=$id&amp;type=txt","csv");
              echo "</td>";

    // print csv
              echo "<td>";
                echo " " . form::link("","csv","csv.php?id=$id&amp;type=csv","csv");
              echo "</td>";
              
              echo "<td align='right'>";
    // export Adlib
              echo "<td>";
                echo " " . form::link("","adlib","csv.php?id=$id&amp;type=adlib","adlib");
              echo "</td>";

    // set link
                $linkType = session::get(linktype);
                $linkIcon = array("","dolink","doequiv","doassoc");
    
                if (session::get(link) != $id and session::get(link))
                {
                  echo "mit <b>'" . thesaurus::name(session::get(link)) . "'</b>' verknüpfen";
                  echo " " . form::link($linkIcon[$linkType],$linkType,"index.php?linkaction=linkdo&amp;id=$id",$linkIcon[$linkType]);
                  echo " " . form::link("delete","end","index.php?linkaction=linkend","end-linking");
                }
              echo "</td>";
            echo "</tr></table>";
          echo "</div>";
        }
      echo "</p>";



//------------------------------------------------------------------------------  
// display name
      echo "<p>";
        echo "<span class='exporthead'><b>";

          $tempString = highlight($highlight,$dataArray[name],$isDescriptor);
          echo "<a href='index.php?action=show&amp;id=$id'>";
            echo $tempString;
            
// if javascript -> direkt edit
            if (session::get(JS));
            echo grafik::disp("edit","edit",15);

          echo "</a>";
        echo "</b>";
  
// display status of descriptor
          echo " <i>(" . thesaurus::get_status_name(thesaurus::get_status($id)) . ")</i>";

// mark if kandidate            
          if (!thesaurus::is_visible($id)) echo "<br><span class='red'>versteckt</span>";
        echo "</span>";


// display type of descriptor
        echo "<br><i>" . thesaurus::get_name(thesaurus::get_thesaurus($id)) . "</i>";
      echo "</p>";



//------------------------------------------------------------------------------  
// display comment
      echo "<p>";
        if ($dataArray[comment])
        {
          echo "<tr><td></td>";
	
          echo "<td class='export'>";
            echo nl2br(highlight($highlight,$dataArray[comment]));
          echo "</td></tr>";
        }
      echo "</p>";
 


//------------------------------------------------------------------------------  
// get link data for descriptor
      $parentArray = $linkArray[parent];
      $childArray = $linkArray[child];
      $assocArray = $linkArray[assoc];
      $equivArray = $linkArray[equiv];

//------------------------------------------------------------------------------  
// list synonyms
      echo "<table>";
        if ($equivArray) // parents
        {
// sort for use and used for
          $x = $y = 0;
          foreach($equivArray as $entry)
          {
            if ($entry > 0) $forArray[$x++] = $entry;
            else $retArray[$x++] = abs($entry);
          }
  
          if ($forArray)
          {
            echo "<tr><td valign='top'>BS</td>";
            echo "<td class='export'>";
              export::list_names($forArray,$highlight);
            echo "</td></tr>";
          }
          
          if ($retArray)
          {
            echo "<tr><td valign='top'>BF</td>";
            echo "<td class='export'>";
              export::list_names($retArray,$highlight);
            echo "</td></tr>";
          }
        }
      echo "</p>";


//------------------------------------------------------------------------------  
// list parents
      if ($parentArray) // parents
      {
        echo "<tr><td valign='top'>";
          echo "<a href='javascript:void();'";
          echo help::show("OB","");
          echo ">OB</a>";
        echo "</td>";
        echo "<td class='export'>";
          export::list_names($parentArray,$highlight);
        echo "</td></tr>";
      }
      
//------------------------------------------------------------------------------  
// list childs
      if ($childArray)
      {
        echo "<tr><td valign='top'>";
          echo "<a href='javascript:void();'";
          echo help::show("UB","");
          echo ">UB</a>";
        echo "</td>";
        echo "<td class='export'>";
          export::list_names($childArray,$highlight);
        echo "</td></tr>";
      }

//------------------------------------------------------------------------------  
// list associative links        
      if ($assocArray)
      {
        echo "<tr><td valign='top'>";
          echo "<a href='javascript:void();'";
          echo help::show("VB","");
          echo ">VB</a>";
        echo "</td>";
        echo "<td class='export'>";
          export::list_names($assocArray,$highlight);
        echo "</td></tr>";
      }

      echo "</table>";
    echo "</div>";
  }




//------------------------------------------------------------------------------  
  function list_names($listArray,$highlight,$format = "")
  {
    $name = "";
    
    foreach($listArray as $entry)
    {
      if (!($isDescriptor = thesaurus::is_descriptor($entry)) and $format) $formString = "bright";
      else $formString = "";
      
      $name .= "<a href='index.php?action=show&amp;id=$entry'>";
      $name .= highlight($highlight,thesaurus::get_name($entry),$isDescriptor,$formString);
      $name .= "</a><br>";
    }
    echo substr($name,0,strlen($name)-4);
  }
}

?>