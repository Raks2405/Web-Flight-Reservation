<?
include "utility_functions.php";

$sessionid =$_GET["sessionid"];
verify_session($sessionid);


ini_set( "display_errors", 0);  


$username = $_GET["username"];

$sql = "Delete from Users where username = '$username'";

$result_array = execute_sql_in_oracle ($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false){
  // Error handling interface.
  echo "<B>Deletion Failed.</B> <BR />";

  display_oracle_error_message($cursor);

  die("<i> 

  <form method=\"post\" action=\"users.php?sessionid=$sessionid&username=$username\">
  Read the error message, and then try again:
  <input type=\"submit\" value=\"Go Back\">
  </form>

  </i>
  ");
}

// Record deleted.  Go back.
Header("Location:users.php?sessionid=$sessionid");
?>