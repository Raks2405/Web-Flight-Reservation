<?
include "utility_functions.php";

$sessionid =$_GET["sessionid"];
verify_session($sessionid);

// Verify where we are from, employee.php or  emp_update_action.php.
if (!isset($_POST["update_fail"])) { // from employee.php
  // Fetch the record to be updated.
  $username = $_GET["username"];

  // the sql string
  $sql = "select username, first_name, last_name, TO_CHAR(start_date, 'MM/DD/YYYY'), 
         TO_CHAR(registration_date, 'MM/DD/YYYY') FROM Users where username = '$username'";
  //echo($sql);

  $result_array = execute_sql_in_oracle ($sql);
  $result = $result_array["flag"];
  $cursor = $result_array["cursor"];

  if ($result == false){
    display_oracle_error_message($cursor);
    die("Query Failed.");
  }

  $values = oci_fetch_array ($cursor);
  oci_free_statement($cursor);

  $username = $values[0];
  $fname = $values[1];
  $lname = $values[2];
  $start_date = $values[3];
  $reg_date = $values[4];
}
else { 
  $username = $values["username"];
  $fname = $values["fname"];
  $lname = $values["lname"];
  $start_date = $values["start_date"];
  $reg_date = $values["reg_date"];
}

echo("
  <form method=\"post\" action=\"users_update_action.php?sessionid=$sessionid\">
  Username (Read-only): <input type=\"text\" readonly value = \"$username\" size=\"10\" maxlength=\"10\" name=\"username\"> <br /> 
  Firstname : <input type=\"text\" value = \"$fname\" size=\"20\" maxlength=\"30\" name=\"fname\">  <br />
  Lastname : <input type=\"text\" value = \"$lname\" size=\"20\" maxlength=\"30\" name=\"lname\">  <br />
  Start Date (mm/dd/yyyy): <input type=\"text\" value = \"$start_date\" size=\"10\" maxlength=\"10\" name=\"start_date\">  <br />
  Registration Date (mm/dd/yyyy): <input type=\"text\" value = \"$reg_date\" size=\"10\" maxlength=\"10\" name=\"reg_date\">  <br />
  ");
$sql = "select role_id, role_name from Role order by role_id";

$result_array = execute_sql_in_oracle ($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false){
  display_oracle_error_message($cursor);
  die("Query Failed.");
}

echo("
  Role (Required):
  <select name=\"role_id\">
  <option value=\"\">Choose One:</option>
  ");

// Fetch the departments from the cursor one by one into the dropdown list.
while ($values = oci_fetch_array ($cursor)){
  $r_role_id = $values[0];
  $r_role_name = $values[1];
  if (!isset($role_id) or $role_id == "" or $r_role_id != $role_id) {
    echo("
      <option value=\"$r_role_id\">$r_role_id, $r_role_name</option>
      ");
  }
  else {
    echo("
      <option selected value=\"$r_role_id\">$r_role_id, $r_role_name</option>
      ");
  }
}
oci_free_statement($cursor);

echo("
  </select>  <input type=\"submit\" value=\"Update\">
  <input type=\"reset\" value=\"Reset to Original Value\">
  </form>

  <form method=\"post\" action=\"users.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
  ");



?>
