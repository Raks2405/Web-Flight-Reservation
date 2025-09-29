<?
include "utility_functions.php";

$sessionid =$_GET["sessionid"];
verify_session($sessionid);

// Suppress PHP auto warning.
ini_set( "display_errors", 0);  

// Obtain information for the record to be updated.
$username = $_POST["username"];
$fname = $_POST["fname"];
$lname = $_POST["lname"];
$start_date = $_POST["start_date"];
$reg_date = $_POST["reg_date"];
$role_id = $_POST["role_id"];

$username = trim( $_POST['username']);

if ($username == "") $username = "NULL";

$sql = "Update Users set first_name = '$fname', last_name = '$lname', start_date = to_date('$start_date', 'MM/DD/YYYY'), registration_date = to_date('$reg_date', 'MM/DD/YYYY'), username = '$username' where username = '$username'";

$result_array = execute_sql_in_oracle ($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false){
  // Error handling interface.
  echo "<B>Update Failed.</B> <BR />";

  display_oracle_error_message($cursor);

  die("<i> 

  <form method=\"post\" action=\"users_update?sessionid=$sessionid&username=$username\">

  <input type=\"hidden\" value = \"1\" name=\"update_fail\">
  <input type=\"hidden\" value = \"$username\" name=\"username\">
  <input type=\"hidden\" value = \"$fname\" name=\"fname\">
  <input type=\"hidden\" value = \"$lname\" name=\"lname\">
  <input type=\"hidden\" value = \"$start_date\" name=\"start_date\">
  <input type=\"hidden\" value = \"$reg_date\" name=\"reg_date\">
  
  Read the error message, and then try again:
  <input type=\"submit\" value=\"Go Back\">
  </form>
  </i>
  ");
}

oci_free_statement($cursor);

if ($role_id == "") $role_id = "NULL";
if ($role_id === "2") {
  if (empty($start_date)) {
      die("<b>Error:</b> Start Date is required for Admin users!<br><a href='users_update.php?sessionid=$sessionid&username=$username'>Go Back</a>");
  }
  if (!empty($reg_date)) {
      die("<b>Error:</b> Admin users should NOT have a Registration Date!<br><a href='users_update.php?sessionid=$sessionid&username=$username'>Go Back</a>");
  }
} elseif ($role_id === "1") {
  if (empty($reg_date)) {
      die("<b>Error:</b> Registration Date is required for Regular users!<br><a href='users_update.php?sessionid=$sessionid&username=$username'>Go Back</a>");
  }
  if (!empty($start_date)) {
      die("<b>Error:</b> Regular users should NOT have a Start Date!<br><a href='users_update.php?sessionid=$sessionid&username=$username'>Go Back</a>");
  }
} elseif ($role_id === "3") {
  if (empty($start_date) || empty($reg_date)) {
      die("<b>Error:</b> Both Start Date and Registration Date are required for Hybrid users!<br><a href='users_update.php?sessionid=$sessionid&username=$username'>Go Back</a>");
  }
} else {
  die("<b>Error:</b> Invalid Role selected!<br><a href='users_update.php?sessionid=$sessionid&username=$username'>Go Back</a>");
}

$sql2 = "Update UserRole set id_role = '$role_id' where username = '$username'";


$result_array1 = execute_sql_in_oracle ($sql2);
$result1 = $result_array1["flag"];
$cursor1 = $result_array1["cursor"];

if ($result1 == false){
  // Error handling interface.
  echo "<B>Update Failed.</B> <BR />";

  display_oracle_error_message($cursor1);

  die("<i> 

  <form method=\"post\" action=\"users_update?sessionid=$sessionid&username=$username\">

  <input type=\"hidden\" value = \"1\" name=\"update_fail\">
  <input type=\"hidden\" value = \"$username\" name=\"username\">
  <input type=\"hidden\" value = \"$fname\" name=\"fname\">
  <input type=\"hidden\" value = \"$lname\" name=\"lname\">
  <input type=\"hidden\" value = \"$start_date\" name=\"start_date\">
  <input type=\"hidden\" value = \"$reg_date\" name=\"reg_date\">
  
  Read the error message, and then try again:
  <input type=\"submit\" value=\"Go Back\">
  </form>
  </i>
  ");
}
oci_free_statement($cursor1);


Header("Location:users.php?sessionid=$sessionid&username=$username");
?>