<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

$username = $_POST["username"];
$fname = $_POST["fname"];
$lname = $_POST["lname"];
$phone = $_POST["phone"];
$type = $_POST["type"];
$state = strtoupper(trim($_POST["state"]));
$country = strtoupper(trim($_POST["country"]));

if (empty($fname) || empty($lname) || empty($phone) || empty($type)) {
    die("<b>Error:</b> All required fields must be filled.<br>
    <form method='post' action='update_customer.php?sessionid=$sessionid&username=$username'>
        <input type='submit' value='Go Back'>
    </form>");
}

if ($type === 'D' && (empty($state) || !empty($country))) {
    die("<b>Error:</b> Domestic customers should only have state and no country.<br>
    <form method='post' action='update_customer.php?sessionid=$sessionid&username=$username'>
        <input type='submit' value='Go Back'>
    </form>");
}

if ($type === 'F' && (empty($country) || !empty($state))) {
    die("<b>Error:</b> Foreign customers should only have country and no state.<br>
    <form method='post' action='update_customer.php?sessionid=$sessionid&username=$username'>
        <input type='submit' value='Go Back'>
    </form>");
}

$sql = "UPDATE Customers SET
            first_name = '$fname',
            last_name = '$lname',
            phone = '$phone',
            customer_type = '$type',
            state = " . ($type === 'D' ? "'$state'" : "NULL") . ",
            country = " . ($type === 'F' ? "'$country'" : "NULL") . "
        WHERE username = '$username'";

$result_array = execute_sql_in_oracle($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if (!$result) {
    display_oracle_error_message($cursor);
    die("<b>Error:</b> Failed to update customer.");
}
oci_free_statement($cursor);

header("Location: update_delete_customer.php?sessionid=$sessionid");
exit();
?>
