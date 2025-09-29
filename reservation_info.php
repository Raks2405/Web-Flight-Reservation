<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);
$username = $_GET["username"];

echo("<h2>My Reservation Information</h2>");

// Total Reservations
$sql = "SELECT COUNT(*) FROM Reservations WHERE username = '$username'";
$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];
$row = oci_fetch_array($cursor);
$total_reservations = $row[0];
oci_free_statement($cursor);

// Upcoming Reservations
$sql = "SELECT COUNT(*) 
        FROM Reservations r JOIN Flights f ON r.flight_id = f.flight_id 
        WHERE r.username = '$username' AND f.flight_date >= TRUNC(SYSDATE)";

$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];
$row = oci_fetch_array($cursor);
$upcoming = $row[0];
oci_free_statement($cursor);

// Average Monthly Flights (last 12 months)
$sql = "SELECT COUNT(*) / 12 
        FROM Reservations r JOIN Flights f ON r.flight_id = f.flight_id 
        WHERE r.username = '$username' AND f.flight_date >= ADD_MONTHS(TRUNC(SYSDATE, 'MM'), -11)";
$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];
$row = oci_fetch_array($cursor);
$avg_monthly = number_format($row[0], 2);
oci_free_statement($cursor);

// Diamond Customer Score
$sql = "SELECT NVL(SUM(seating_grade), 0) / 
               CASE WHEN COUNT(*) = 0 THEN 1 ELSE COUNT(*) END 
        FROM Reservations WHERE username = '$username'";
$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];
$row = oci_fetch_array($cursor);
$diamond_score = number_format($row[0], 2);
oci_free_statement($cursor);

// Display Summary
echo("<b>Total Reservations:</b> $total_reservations <br/>");
echo("<b>Upcoming Reservations:</b> $upcoming <br/>");
echo("<b>Average Monthly Flights (last 12 months):</b> $avg_monthly <br/>");
echo("<b>Diamond Customer Score:</b> $diamond_score <br/><br/>");

// List All Reservations
$sql = "SELECT r.flight_id, fr.airline_name, fr.flight_number, f.flight_date, r.seating_grade
        FROM Reservations r
        JOIN Flights f ON r.flight_id = f.flight_id
        JOIN Flight_Routes fr ON f.airline_name = fr.airline_name AND f.flight_number = fr.flight_number
        WHERE r.username = '$username'
        ORDER BY f.flight_date DESC";

$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];

echo("<table border=1>");
echo("<tr> <th>Flight ID</th> <th>Airline</th> <th>Flight Number</th> <th>Flight Date</th> <th>Seating Grade</th> </tr>");

while ($values = oci_fetch_array($cursor)) {
    $fid = $values[0];
    $airline = $values[1];
    $fnum = $values[2];
    $fdate = $values[3];
    $grade = $values[4];

    echo("<tr><td>$fid</td><td>$airline</td><td>$fnum</td><td>$fdate</td><td>$grade</td></tr>");
}
echo("</table>");
oci_free_statement($cursor);
echo("<form method='post' action='regularuser.php?sessionid=$sessionid&username=$username'>
        <input type='submit' value='Go Back'>
      </form>");
oci_close($conn);

// Back button

?>
