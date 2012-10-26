<?PHP
$recordArray = array();


// create adlib xml
function adlib($id,$type)
{
// create adlib xml basic structure
  $XML = new simpleXmlElement("<?xml version='1.0' encoding='utf-8'?><adlibXML xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:noNamespaceSchemaLocation='http://www.adlibsoft.com/adlibXML.xsd' />");

?>
<?PHP

  echoall("Start Adlib XML export");
  echoall("Connect database");

  $daba = new database();
  $daba->connect("localhost","iggmp","1s87J37r0");
  if ($daba->select("thesaurus"))
    echoall("Database connected");
  else
    die("***ERROR - Database connection failed");


  $termType = thesaurus::get_name($id);

  echoall("Starte bei <b>'" . thesaurus::get_name($id) . "'</b>");

//------------------------------------------------------------------------------
// create recordList
  xml_insert($XML,new simpleXmlElement("<recordList />"));

// insert records in recordList
  $subXml = _subtree($id,$termType);
  if ($subXml)
    xml_insert($XML->recordList,$subXml);

  echoall("XML export completed<hr>");

echoall($XML);
  return ($XML->asXML());
}


//------------------------------------------------------------------------------
// loop over hyrarchy recursive
function _subtree($id,$termType)
{
  global $recordArray;
  
  if (!$retXml)
    $retXml = new simpleXmlElement("<temp/>");


  $term = thesaurus::get_name($id);


// check if entry allready exists

  if (!$recordArray[$term])
  {
//------------------------------------------------------------------------------
// extract (BS) from term
// and create new terms from BS clauses

      $uses = extract_pattern($term,"/\(BS[: ](?<use>.*)\)/");
    
    
      if ($uses)
      {
// create common parameters
      $newArray['input.name'] = "Thesaurus Redaktion";
      $newArray['input.date'] = date("Y-m-d",time());
      $newArray['input.time'] = date("h:i:s",time());
      $newArray['term.type'] = $termType;
      $newArray['term.status'] = "Verweisungsform";
      $newArray['use'] = $term;

//    $newArray['broader_term'] = thesaurus::get_parent($id);

// loop over uses
      foreach($uses as $entry)
      {
        $newArray['term'] = $entry;
        extract_pattern($newArray['term'],"/\(BS[: ](?<use>.*)\)/");
//  echoall(create_record($newArray,$termType));
        xml_insert($retXml->record,create_record($newArray,$termType));
      }
    }


//------------------------------------------------------------------------------
// insert entry
    xml_insert($retXml,record($id,$termType));

// register new record
    $recordArray[$term] = $term;


//------------------------------------------------------------------------------
// recursion of children
    $children = thesaurus::get_child($id);
  
    if ($children)
    {
      foreach ($children as $entry)
      {
  //echoall($entry);
  // subchildren -> recursion
        if (count(thesaurus::get_child($entry)))
        {
          $subXml = _subtree($entry,$termType);
          if ($subXml)
            xml_insert($retXml,$subXml);
        }
      }
    }
    return ($retXml);
  }
}


//------------------------------------------------------------------------------
// create record entry from id
function record($id,$termType)
{
  $uses = "";
  $term = thesaurus::get_name($id);

  $uses = extract_pattern($term,"/\(BS[: ](?<use>.*)\)/");

  $recordArray = array();
  $record = new simpleXmlElement("<record />");


// check if term already exists


// define array for record creation
  $recordArray['input.name'] = "Thesaurus Redaktion";
  $recordArray['input.date'] = date("Y-m-d",time());
  $recordArray['input.time'] = date("h:i:s",time());
  $recordArray['term.type'] = $termType;
  $recordArray['term.status'] = thesaurus::get_status_name(thesaurus::get_status($id));
  $recordArray['term'] = $term;
  $recordArray['notes'] = thesaurus::get_comment($id);
  $recordArray['broader_term'] = thesaurus::get_parent($id);

//  $recordArray['narrower_term'] = thesaurus::get_child($id);
  $recordArray['related_term'] = thesaurus::get_assoc($id);
  $recordArray['used_for'] = $uses;


  xml_insert($record->record,create_record($recordArray,$termType));

  return ($record);
}


//------------------------------------------------------------------------------
// create record xml
function create_record($recordArray,$termType)
{
  $retXml = new simpleXmlElement("<record />");

  if (is_array($recordArray))
  {
    foreach($recordArray as $key=>$value)
    {
// create only when value is defined
      if ($value)
      {
// if array -> create list
        if (is_array($value))
        {
          if (intval($value))
            xml_insert($retXml,list_id($key,$value));
          else
            xml_insert($retXml,list_name($key,$value));
        }
        else
        {
// if value -> create tag
          $retXml->addChild($key,trim($value));
        }
      }
    }
  }

  return($retXml);
}


//------------------------------------------------------------------------------
// extract (BS)
function extract_pattern(&$term,$pattern)
{
//------------------------------------------------------------------------------
// parse term for use / used_for
//  $pattern = "/\(BS[: ](?<use>.*)\)/";
  preg_match($pattern,$term,&$matches,PREG_OFFSET_CAPTURE);

  $pos = $matches[0][1];
  $use = $matches['use'][0];
  if ($use)
    $uses = explode(",",$use);


// remove use string from term
  if ($use)
  {
    $term = substr($term,0,$pos);
  }

  return ($uses);
}


//------------------------------------------------------------------------------
function list_id($type,$idArray)
{
  $retXml = new simpleXmlElement("<$type />");
  
  if (is_array($idArray))
  {
    foreach($idArray as $entry)
    {
      if (is_numeric($entry))
      {
        $xmlString = thesaurus::get_name($entry);
        extract_pattern($xmlString,"/\(BS[: ](?<use>.*)\)/");
      }
      else
      {
        $xmlString = $entry;
      }
      xml_insert($retXml,new simpleXmlElement("<$type>" . trim($xmlString) . "</$type>"));
    }
  }

  return $retXml;
}


//------------------------------------------------------------------------------
// insert complex xml in xml
function xml_insert(&$xml_to,$xml_from)
{
// insert complex structure
	if (count($xml_from->children()))
	{
    foreach ($xml_from->children() as $xml_child)
    {
      $xml_temp = $xml_to->addChild($xml_child->getName(), (string) $xml_child);
      foreach ($xml_child->attributes() as $attr_key => $attr_value)
      {
          $xml_temp->addAttribute($attr_key, $attr_value);
      }

// add recursive
			if (count($xml_child->children()))
	      xml_insert($xml_temp, $xml_child);
    }
	}
  else
// insert single entry
  {
    $xml_temp = $xml_to->addChild($xml_from->getName(), (string) $xml_from);
    foreach ($xml_from->attributes() as $attr_key => $attr_value)
    {
        $xml_temp->addAttribute($attr_key, $attr_value);
    }
  }
}
?>