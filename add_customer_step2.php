<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Get data from step 1
$fname = $_POST["fname"];
$lname = $_POST["lname"];
$reg_date = $_POST["reg_date"];

// Step 1: Generate next username
do {
    // Step 1: Get next sequence number
    $sql = "SELECT customer_seq.NEXTVAL FROM dual";
    $result_array = execute_sql_in_oracle($sql);
    $cursor = $result_array["cursor"];
    $row = oci_fetch_array($cursor);
    $next_num = intval($row[0]);
    oci_free_statement($cursor);

    // Step 2: Form username
    $initials = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));
    $username = $initials . str_pad($next_num, 4, '0', STR_PAD_LEFT);

    // Step 3: Check if username exists
    $check_sql = "SELECT COUNT(*) FROM Customers WHERE username = '$username'";
    $check_array = execute_sql_in_oracle($check_sql);
    $check_cursor = $check_array["cursor"];
    $check_row = oci_fetch_array($check_cursor);
    $exists = $check_row[0] > 0;
    oci_free_statement($check_cursor);

} while ($exists);


// Step 2: Show final form
echo("<h2>Step 2: Complete Customer Details</h2>");
echo("<form method='post' action='add_customer_action.php?sessionid=$sessionid'>");
echo("<input type='hidden' name='fname' value='" . htmlspecialchars($fname) . "'>");
echo("<input type='hidden' name='lname' value='" . htmlspecialchars($lname) . "'>");
echo("<input type='hidden' name='reg_date' value='" . htmlspecialchars($reg_date) . "'>");
echo("Username: <b>$username</b><br />");
echo("<input type='hidden' name='username' value='$username'>");
echo("<input type='hidden' name='fname' value='$fname'>");
echo("<input type='hidden' name='lname' value='$lname'>");
echo("<input type='hidden' name='reg_date' value='$reg_date'>");

// Additional required fields
echo("Password: <input type='text' name='password' required><br />");
echo("Phone: <input type='text' name='phone' required><br />");
echo("Customer Type:
    <select name='type' required>
        <option value=''>Select...</option>
        <option value='D'>Domestic</option>
        <option value='F'>Foreign</option>
    </select><br />");
echo("State (if Domestic): <input type='text' name='state' minlength='2' maxlength ='2'><br />");
echo("Country (if Foreign): <input type='text' name='country' minlength='2' maxlength ='2'><br />");
echo("<input type='submit' value='Add Customer'>");
echo("</form>");
echo("<form method='post' action='users.php?sessionid=$sessionid'>
        <input type='submit' value='Go Back'>
      </form>");
?>
