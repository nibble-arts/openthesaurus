<?PHP
class group
{

// returns a list of all groups in an array
  function get_list()
  {
    return(fetch_to_array(database::query("SELECT ID AS value,name AS text,rights FROM `group` ORDER BY name"),""));
  }
  

// set group of user $userID to group $group
  function set($userID,$group)
  {
    database::query("UPDATE user SET `group`='$group' WHERE ID='$userID'");
  }


// check if superuser
  function superuser($groupID)
  {
    return(mysql_num_rows(database::query("SELECT * FROM `group` WHERE ID='$groupID' and superuser='1' ORDER BY name")));
  }
}
?>