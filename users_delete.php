<?
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);
$username = $_GET["username"];

$sql = "select username, first_name, last_name, TO_CHAR(start_date, 'MM/DD/YYYY'), 
         TO_CHAR(registration_date, 'MM/DD/YYYY') FROM Users where username = '$username'";

$result_array = execute_sql_in_oracle($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false) {
    display_oracle_error_message($cursor);
    die("Client Query Failed.");
}

if (!($values = oci_fetch_array($cursor))) {
    // Record already deleted by a separate session.  Go back.
    Header("Location:users.php?sessionid=$sessionid");
}
oci_free_statement($cursor);

$username = $values[0];
  $fname = $values[1];
  $lname = $values[2];
  $start_date = $values[3];
  $reg_date = $values[4];

  echo("
  <form method=\"post\" action=\"users_delete_action.php?sessionid=$sessionid&username=$username\">
  Username (Read-only): <input type=\"text\" disabled readonly value = \"$username\" size=\"10\" maxlength=\"10\" name=\"username\"> <br /> 
  Firstname : <input type=\"text\" value = \"$fname\" disabled size=\"20\" maxlength=\"30\" name=\"fname\">  <br />
  Lastname : <input type=\"text\" value = \"$lname\" disabled size=\"20\" maxlength=\"30\" name=\"lname\">  <br />
  Start Date (mm/dd/yyyy): <input type=\"text\" value = \"$start_date\" disabled size=\"10\" maxlength=\"10\" name=\"start_date\">  <br />
  Registration Date (mm/dd/yyyy): <input type=\"text\" value = \"$reg_date\" disabled size=\"10\" maxlength=\"10\" name=\"reg_date\">  <br />
  ");

  echo("
  </select> <input type=\"submit\" value=\"Delete\">
  </form>

  <form method=\"post\" action=\"users.php?sessionid=$sessionid&username=$username\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
  ");
?>