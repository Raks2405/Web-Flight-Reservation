<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
$username = $_GET["username"];
verify_session($sessionid);

$sql = "DELETE FROM Customers WHERE username = '$username'";
$result_array = execute_sql_in_oracle($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if (!$result) {
    display_oracle_error_message($cursor);
    die("<b>Error:</b> Failed to delete customer.");
}
oci_free_statement($cursor);

// Optional: Clean up related user data if ON DELETE CASCADE isn't set
$sql2 = "DELETE FROM UserRole WHERE username = '$username'";
$sql3 = "DELETE FROM Users WHERE username = '$username'";
execute_sql_in_oracle($sql2);
execute_sql_in_oracle($sql3);

// Redirect
header("Location: update_delete_customer.php?sessionid=$sessionid");
exit();
?>
