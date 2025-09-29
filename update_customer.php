<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
$username = $_GET["username"];
verify_session($sessionid);

// Fetch customer info
$sql = "SELECT * FROM Customers WHERE username = '$username'";
$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];
$row = oci_fetch_array($cursor, OCI_ASSOC);
oci_free_statement($cursor);

if (!$row) {
    echo "<b>Error:</b> Customer not found.";
    exit();
}

// Pre-fill values
$fname = htmlspecialchars($row['FIRST_NAME']);
$lname = htmlspecialchars($row['LAST_NAME']);
$phone = htmlspecialchars($row['PHONE']);
$type = htmlspecialchars($row['CUSTOMER_TYPE']);
$state = htmlspecialchars($row['STATE']);
$country = htmlspecialchars($row['COUNTRY']);

// Render form
echo "<h2>Update Customer</h2>";
echo "<form method='post' action='update_customer_action.php?sessionid=$sessionid'>";
echo "<input type='hidden' name='username' value='$username'>";
echo "First Name: <input type='text' name='fname' value='$fname' required><br />";
echo "Last Name: <input type='text' name='lname' value='$lname' required><br />";
echo "Phone: <input type='text' name='phone' value='$phone' required><br />";
echo "Customer Type:
    <select name='type' required>
        <option value='D'" . ($type == 'D' ? " selected" : "") . ">Domestic</option>
        <option value='F'" . ($type == 'F' ? " selected" : "") . ">Foreign</option>
    </select><br />";
echo "State (if Domestic): <input type='text' name='state' value='$state' minlength='2' maxlength='2'><br />";
echo "Country (if Foreign): <input type='text' name='country' value='$country'minlength='2' maxlength='2'><br />";
echo "<input type='submit' value='Update Customer'>";
echo "</form>";
echo "<form method='post' action='update_delete_customer.php?sessionid=$sessionid'>
        <input type='submit' value='Go Back'>
      </form>";
?>
