<?
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);
$username = $_GET['username'];
echo ("This is Personal Information page
    <br/>
    <br/>
    ");

$sql = "
    SELECT *
    FROM Customers WHERE username = '$username'
  ";

$result_array = execute_sql_in_oracle($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false) {
    display_oracle_error_message($cursor);
    die("Query Failed.");
}

echo "<table border=1>";
echo "<tr> <th>Username</th><th>Password</th> <th>First Name</th> <th>Last Name</th> <th>Phone Number</th><th>Customer Type</th><th>Diamond Status</th> <th>State</th> <th>Country</th></tr>";

while ($row = oci_fetch_array($cursor)) {
    $username = htmlspecialchars($row[0]);
    $password = htmlspecialchars($row[1]);
    $first_name = htmlspecialchars($row[2]);
    $last_name = htmlspecialchars($row[3]);
    $phone = htmlspecialchars($row[4]);
    $customer_type = htmlspecialchars($row[5]);
    $diamond_status = htmlspecialchars($row[6]);
    $state = htmlspecialchars($row[7]);
    $country = htmlspecialchars($row[8]);

    echo "<tr>
            <td>$username</td>
            <td>$password</td>
            <td>$first_name</td>
            <td>$last_name</td>
            <td>$phone</td>
            <td>$customer_type</td>
            <td>$diamond_status</td>
            <td>$state</td>
            <td>$country</td>
          </tr>";
}

oci_free_statement($cursor);
echo "</table>";
// Optional: back button
echo "<form method=\"post\" action=\"regularuser.php?sessionid=$sessionid&username=$username\">
        <input type=\"submit\" value=\"Go Back\">
    </form>";
?>

