<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Input fields from search form
$username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
$fname = isset($_POST["fname"]) ? trim($_POST["fname"]) : "";
$lname = isset($_POST["lname"]) ? trim($_POST["lname"]) : "";
$type = isset($_POST["type"]) ? trim($_POST["type"]) : "";
$diamond = isset($_POST["diamond"]) ? trim($_POST["diamond"]) : "";

// Build WHERE clause
$whereClause = "1=1";
if (!empty($username)) $whereClause .= " AND username LIKE '%$username%'";
if (!empty($fname)) $whereClause .= " AND first_name LIKE '%$fname%'";
if (!empty($lname)) $whereClause .= " AND last_name LIKE '%$lname%'";
if (!empty($type)) $whereClause .= " AND customer_type = '$type'";
if ($diamond !== "") $whereClause .= " AND diamond_status = $diamond";

// Form for search filters
echo("<form method='post' action='update_delete_customer.php?sessionid=$sessionid'>");
echo("Username: <input type='text' name='username' value='$username'> ");
echo("First Name: <input type='text' name='fname' value='$fname'> ");
echo("Last Name: <input type='text' name='lname' value='$lname'> ");
echo("Customer Type: <select name='type'>");
echo("<option value=''>--Select--</option>");
echo("<option value='D'" . ($type == 'D' ? " selected" : "") . ">Domestic</option>");
echo("<option value='F'" . ($type == 'F' ? " selected" : "") . ">Foreign</option>");
echo("</select> ");
echo("Diamond Status: <select name='diamond'>");
echo("<option value=''>--Any--</option>");
echo("<option value='1'" . ($diamond == '1' ? " selected" : "") . ">Yes</option>");
echo("<option value='0'" . ($diamond == '0' ? " selected" : "") . ">No</option>");
echo("</select> ");
echo("<input type='submit' value='Search'>");
echo("</form><br>");

// Fetch filtered results
$sql = "SELECT * FROM Customers WHERE $whereClause ORDER BY username";
$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];

// Render table
echo("<table border='1'>");
echo("<tr><th>Username</th><th>First Name</th><th>Last Name</th><th>Phone</th><th>Type</th><th>Diamond</th><th>State</th><th>Country</th><th>Update</th><th>Delete</th></tr>");

while ($row = oci_fetch_array($cursor)) {
  $uname = htmlspecialchars($row["USERNAME"]);
  $fname = htmlspecialchars($row["FIRST_NAME"]);
  $lname = htmlspecialchars($row["LAST_NAME"]);
  $phone = htmlspecialchars($row["PHONE"]);
  $ctype = htmlspecialchars($row["CUSTOMER_TYPE"]);
  $diamond = htmlspecialchars($row["DIAMOND_STATUS"]);
  $state = htmlspecialchars($row["STATE"]);
  $country = htmlspecialchars($row["COUNTRY"]);

  echo("<tr>");
  echo("<td>$uname</td><td>$fname</td><td>$lname</td><td>$phone</td><td>$ctype</td><td>$diamond</td><td>$state</td><td>$country</td>");
  echo("<td><a href='update_customer.php?sessionid=$sessionid&username=$uname'>Update</a></td>");
  echo("<td><a href='delete_customer.php?sessionid=$sessionid&username=$uname' onclick=\"return confirm('Are you sure you want to delete $uname?');\">Delete</a></td>");
  echo("</tr>");
}

oci_free_statement($cursor);
echo("</table>");

// Back button
echo("<br><form method='post' action='users.php?sessionid=$sessionid'>");
echo("<input type='submit' value='Go Back'>");
echo("</form>");
?>
