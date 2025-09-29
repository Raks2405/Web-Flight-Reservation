<?php
include "utility_functions.php";
session_start();  // Ensure session is started

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

$username = $_GET['username'];  // Get the logged-in user
$newpassword = trim($_POST["newpassword"]);  // Trim spaces

// Suppress PHP auto warnings.
ini_set("display_errors", 0);

// Check if the new password is empty
if (empty($newpassword)) {
    die("Password cannot be empty. Click <A href='change_password.php?sessionid=$sessionid'>here</A> to try again.");
}

// SQL to update the user's password
$sql = "Update Users set password = '$newpassword' WHERE username = '$username'";

$connection = oci_connect("gq036", "iccwku", "gqiannew3:1521/orc.uco.local");
if (!$connection) {
    display_oracle_error_message(null);
    die("Failed to connect to the database.");
}

$statement = oci_parse($connection, $sql);
oci_bind_by_name($statement, ":newpassword", $newpassword);
oci_bind_by_name($statement, ":username", $username);

// Execute SQL with commit
$result = oci_execute($statement, OCI_COMMIT_ON_SUCCESS);

if ($result) {
    echo "Password change successful. Click <A href='welcomepage.php?sessionid=$sessionid'>here</A> to go back.";
} else {
    display_oracle_error_message($statement);
    die("<i>
    <form method='post' action='change_password.php?sessionid=$sessionid'>
    Read the error message, and then try again:
    <input type='submit' value='Go Back'>
    </form>
    </i>");
}

oci_free_statement($statement);
oci_close($connection);
?>
