<?PHP


class help {


// adds mouse-commands to html
  function show($id,$text="")
  {
    if (!session::get("tooltips"))
    {

// display text from database
      if ($id)
      {
        $helpArray = fetch_to_array(database::query("SELECT * FROM system WHERE name='$id'"),"");
        if ($helpArray) $entry = current($helpArray);
        
        $right = right::get_field($id);
        $user = right::get("rights");

        $temp = $entry[text];

// admin informations
        if (right::superuser())
        {
          $temp .= "<hr><table>";
            $temp .= "<tr><td>fieldname</td><td>$id</td></tr>"; // fieldname
            $temp .= "<tr><td>edit</td><td>" . right::int2string($right[edit]) . "</td></tr>"; // edit rights
            $temp .= "<tr><td>view</td><td>" . right::int2string($right[view]) . "</td></tr>"; // view rights
          $temp .= "</table>";
          
          $clickEvent = " onmousedown = edit(&#34;$id&#34;)";
        }

        if ($text)
        $temp .= "<hr>$text";
        
        return("onmouseover='return overlib(&#34;" . $temp . "&#34;);' onmouseout='return nd()' $clickEvent");
      }

// display special text
    }
  }
}

?>