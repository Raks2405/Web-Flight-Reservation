<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

ini_set("display_errors", 1);
error_reporting(E_ALL);

// Get all inputs
$username = $_POST["username"];
$password = $_POST["password"];
$fname = $_POST["fname"];
$lname = $_POST["lname"];
$reg_date = $_POST["reg_date"];
$phone = $_POST["phone"];
$type = $_POST["type"];
$state = strtoupper(trim($_POST["state"]));
$country = strtoupper(trim($_POST["country"]));

// Step 1: Validate required fields
if (empty($username) || empty($password) || empty($fname) || empty($lname) || empty($reg_date) || empty($phone) || empty($type)) {
    echo "<b>Error:</b> All required fields must be filled.<br>
    <form method='post' action='add_customer_step2.php?sessionid=$sessionid'>
      <input type='hidden' name='fname' value='" . htmlspecialchars($fname) . "'>
      <input type='hidden' name='lname' value='" . htmlspecialchars($lname) . "'>
      <input type='hidden' name='reg_date' value='" . htmlspecialchars($reg_date) . "'>
      <input type='submit' value='Go Back'>
    </form>";
    exit();
}

if ($type === 'D' && (empty($state) || !empty($country))) {
    echo "<b>Error:</b> Domestic customers should only have state.<br>
    <form method='post' action='add_customer_step2.php?sessionid=$sessionid'>
      <input type='hidden' name='fname' value='" . htmlspecialchars($fname) . "'>
      <input type='hidden' name='lname' value='" . htmlspecialchars($lname) . "'>
      <input type='hidden' name='reg_date' value='" . htmlspecialchars($reg_date) . "'>
      <input type='submit' value='Go Back'>
    </form>";
    exit();
}

if ($type === 'F' && (empty($country) || !empty($state))) {
    echo "<b>Error:</b> Foreign customers must have a country and no state..<br>
    <form method='post' action='add_customer_step2.php?sessionid=$sessionid'>
      <input type='hidden' name='fname' value='" . htmlspecialchars($fname) . "'>
      <input type='hidden' name='lname' value='" . htmlspecialchars($lname) . "'>
      <input type='hidden' name='reg_date' value='" . htmlspecialchars($reg_date) . "'>
      <input type='submit' value='Go Back'>
    </form>";
    exit();
}

$sql3 = "INSERT INTO Customers (username, password, first_name, last_name, phone, customer_type, diamond_status, state, country) VALUES (
        '$username',
        '$password',
        '$fname',
        '$lname',
        '$phone',
        '$type',
        0,
        " . ($type === 'D' ? "'" . strtoupper($state) . "', NULL" : "NULL, '" . strtoupper($country) . "'") . ")";

$result_array3 = execute_sql_in_oracle($sql3);
$result3 = $result_array3["flag"];
$cursor3 = $result_array3["cursor"];
if (!$result3) {
    display_oracle_error_message($cursor3);
    echo "<b>Error:</b> Failed to insert into Customers.<br>";

    echo "<form method='post' action='add_customer_step2.php?sessionid=$sessionid'>
            <input type='hidden' name='fname' value='" . htmlspecialchars($fname) . "'>
            <input type='hidden' name='lname' value='" . htmlspecialchars($lname) . "'>
            <input type='hidden' name='reg_date' value='" . htmlspecialchars($reg_date) . "'>
            <input type='submit' value='Go Back'>
          </form>";
    exit();

}
oci_free_statement($cursor3);

// Step 2: Insert into Users table
$sql1 = "INSERT INTO Users (username, password, first_name, last_name, registration_date) VALUES ('$username', '$password', '$fname', '$lname', TO_DATE('$reg_date', 'MM/DD/YYYY'))";
$result_array1 = execute_sql_in_oracle($sql1);
$result1 = $result_array1["flag"];
$cursor1 = $result_array1["cursor"];
if (!$result1) {
    display_oracle_error_message($cursor1);
    echo "<b>Error:</b> Description.<br>
    <form method='post' action='add_customer_step2.php?sessionid=$sessionid'>
      <input type='hidden' name='fname' value='" . htmlspecialchars($fname) . "'>
      <input type='hidden' name='lname' value='" . htmlspecialchars($lname) . "'>
      <input type='hidden' name='reg_date' value='" . htmlspecialchars($reg_date) . "'>
      <input type='submit' value='Go Back'>
    </form>";
    exit();
}
oci_free_statement($cursor1);

// Step 3: Insert into UserRole
$sql2 = "INSERT INTO UserRole (username, id_role) VALUES ('$username', 1)";
$result_array2 = execute_sql_in_oracle($sql2);
$result2 = $result_array2["flag"];
$cursor2 = $result_array2["cursor"];
if (!$result2) {
    display_oracle_error_message($cursor2);
    echo "<b>Error:</b> Description.<br>
<form method='post' action='add_customer_step2.php?sessionid=$sessionid'>
  <input type='hidden' name='fname' value='" . htmlspecialchars($fname) . "'>
  <input type='hidden' name='lname' value='" . htmlspecialchars($lname) . "'>
  <input type='hidden' name='reg_date' value='" . htmlspecialchars($reg_date) . "'>
  <input type='submit' value='Go Back'>
</form>";
    exit();

}
oci_free_statement($cursor2);

// Final Step: Success
header("Location: users.php?sessionid=$sessionid");
exit();
?>
