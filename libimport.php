<?PHP

class import
{
//------------------------------------------------------------------------------
// analyse imported file
// $path ... path to file for analysing
// $title ... if true, don't use first line
// $parseId ... if true, first entry of line is id
  function analyse($path,$parent,$title,$root,$parseId,$action,$levelCnt)
  {
//echoall($path);
    $importArray = import::fetch_file_to_array($path);
    $delimiter = ";";

    
//    echoarray($importArray);

?>
<style>
  table {
    border:1px;
    padding:0px;
    margin:0px;
  }
  
  th {
    color:black;
    border-width:1px;
    font-size:10pt;
  }
  
  td {
    color:black;
    border-width:1px;
    font-size:10pt;
  }
</style>

<?PHP
    if (is_array($importArray))
    {
      echo "<h2>Analyse $path</h2>";

//      $levelCnt = 7;

      $linePos = 0;
      echo "<table border='1' cellpadding='0' cellspacing='0'>";
        if ($parseId) echo "<th>ID</th>";
        
        for ($li=0;$li<$levelCnt;$li++)
        {
          echo "<th>Level $li</th>";
        }

        echo "<th>Herkunft</th>";
        echo "<th>Bemerkung</th>";

        foreach($importArray as $entry)
        {
          if ($linePos != 0 or !$title) // filter title line
          {
            $lineArray = explode($delimiter,$entry);
  
            $colPos = 0;
            if ($parseId) $entryid = $lineArray[0];
    
    // loop for entries
            if ($parseId) $start = 1; else $start = 0; // filter id
    
            for($i = $start;$i<count($lineArray);$i++)
            {
              $lineEntry = str_replace("\n",'',$lineArray[$i]);
              $lineEntry = str_replace("\r",'',$lineEntry);
    
              $source = $lineArray[$li+1];
              $comment = $lineArray[$li+2];
  
              if ($lineEntry) // value found
              {
                $parentArray[$colPos] = $lineEntry; // utf8_encode($lineEntry);
  
  // insert into database
                if ($action == "insert")
                {
                  $parentName = $parentArray[$colPos-1];
                  if (!$parentName) $parentName = thesaurus::get_name($parent); // no parent name -> use thesaurus

                  if ($root) $source = $root;
                  $newId = import::insert($lineEntry,$parentName,"$idName=$entryid;Root=$source;Bemerkung=$comment");
                }
                break;
              }
    
              $colPos++;
            }

  // remove unused array items
            for ($i = $colPos + 1;$i < 7;$i++)
            {
              $parentArray[$i] = "";
            }
            
  // display line
            echo "<tr>";
              if ($parseId) echo "<td>$entryid</td>";
              else echo "<td>&nbsp;</td>";

              foreach ($parentArray as $entry)
                echo "<td>" . $entry . "</td>";
  
              if ($source) echo "<td>$source</td>";
              else echo "<td>&nbsp;</td>";
              if ($comment) echo "<td>$comment</td>";
              else echo "<td>&nbsp;</td>";
              
              if ($newId) echo "<td><i><b>($newId)</b></i></td>";
            echo "</tr>";
          }
          else
          {
    // header
            $lineArray = explode($delimiter,$entry);
            $idName = $lineArray[0];
            $sourceName = $lineArray[7];
            $commentName = $lineArray[8];
          }
          
          $linePos++; // next line of file
        }
      echo "</table>";
    }
  }


//------------------------------------------------------------------------------
// fetch file from path
  function fetch_file_to_array($path)
  {
    if (file_exists($path))
    {
      return(file($path));
    }
  }
  

//------------------------------------------------------------------------------
// insert new entry and create link
  function insert($name,$parent,$comment)
  {
//debug
//echo "$name - $parent - $comment<br>";

    $owner = user::id(); // user id
    $status = 2; // descriptor
    $linktype = 1; // hyrarchic link
    
    $parentArray = search::get($parent,"exact");
    $entryArray = search::get($name,"exact");

    $parentId = $parentArray[0][ID];
    $entryId = $entryArray[0][ID];

// entry already exists - create polyseme
    if ($entryId)
    {
      $name = "$name ($parent)";
    }



// create new entry
    $entryString = "INSERT INTO entry SET
      name='$name',
      owner='$owner',
      status='$status',
      comment='$comment'";

    mysql_query($entryString);
// get new id
    $newId = mysql_insert_id();

//debug
//echo "$parent ($parentId) > $name ($newId)<br>";

// create link to parent
    if ($newId)
    {
      $linkString = "INSERT INTO parent SET
        parent='$parentId',
        child='$newId',
        type='$linktype'";
  
      mysql_query($linkString);
    }

    return ($name);
  }
}

?>