<?php
include "utility_functions.php";

$sessionid =$_GET["sessionid"];
verify_session($sessionid);
$username = $_GET['username'];
$newpassword = $_POST["newpassword"];

$sql = "select username, password from Users where username = '$username'";


$result_array = execute_sql_in_oracle ($sql);
  $result = $result_array["flag"];
  $cursor = $result_array["cursor"];

  if ($result == false){
    display_oracle_error_message($cursor);
    die("Query Failed.");
  }

  else{
    $username = $_GET["username"];
    $newpassword = $_POST["newpassword"];
  }

echo("<form method=\"post\" action=\"change_password_action.php?sessionid=$sessionid&username=$username\">
    Password (Up to 6 digits): <input type=\"text\" value = \"$newpassword\" size=\"6\" maxlength=\"6\" name=\"newpassword\"> <br />
    <input type=\"submit\" value=\"Change\">
    </form>
  <form method=\"post\" action=\"welcomepage.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
    
    ")
?>


