<?PHP

class system
{
  function setval($name,$value)
  {
// value name exists
    if (mysql_num_rows(database::query("SELECT * FROM system WHERE name='$name'")))
    {
      $queryString = "UPDATE system SET text='$value' WHERE name='$name'";
      database::query($queryString);
    }
    else
    {
      $queryString = "INSERT INTO system SET text='$value',name='$name'";
      database::query($queryString);
    }

  }
  
  function getval($name)
  {
    $res = database::query("SELECT text FROM system WHERE name='$name'");
    if (mysql_num_rows($res)) return(mysql_result($res,0,"text"));
    else return (FALSE);
  }
}

?>