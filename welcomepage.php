<?
include "utility_functions.php";

$sessionid =$_GET["sessionid"];
verify_session($sessionid);

$sql = "SELECT username FROM Usersession WHERE sessionid = '$sessionid'";
$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];
$values = oci_fetch_array($cursor);
$username = $values["USERNAME"];
oci_free_statement($cursor);

// Retrieve the user's roles
$sql = "SELECT r.role_name FROM Role r
        JOIN UserRole ur ON r.role_id = ur.id_role
        WHERE ur.username = '$username'";

$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];

$roles = [];
while ($values = oci_fetch_array($cursor, OCI_ASSOC)) { 
  $roles[] = $values["ROLE_NAME"];
}
oci_free_statement($cursor);

// Determine the user's role(s)
$is_regular_user = in_array("RegularUser", $roles);
$is_admin = in_array("Admin", $roles);
$is_hybrid = in_array("Hybrid User", $roles);

// Generate the content of the welcome page
echo("Data Management Menu: <br />");
echo("<UL>");


if ($is_regular_user) {
  echo("<LI><A HREF=\"regularuser.php?sessionid=$sessionid&username=$username\">Customer</A></LI>");
}

if($is_admin) {
  echo("<LI><A HREF=\"users.php?sessionid=$sessionid&username=$username\">Customer data</A></LI>");
}

if ($is_hybrid) {
  echo("<LI><A HREF=\"regularuser.php?sessionid=$sessionid&username=$username\">Customer Page</A></LI>");
  echo("<LI><A HREF=\"users.php?sessionid=$sessionid&username=$username\">Customer data</A></LI>");


}
if (empty($roles)) {
  echo "No roles found for this user.";
} 


echo("</UL>");

echo("<br />");
echo("<br />");
echo("Click <A HREF = \"logout_action.php?sessionid=$sessionid\">here</A> to Logout.");
?>