<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);
$username = $_GET['username'];

// Search filters
$airline = isset($_POST['airline']) ? trim($_POST['airline']) : '';
$flight_number = isset($_POST['flight_number']) ? trim($_POST['flight_number']) : '';
$flight_date = isset($_POST['flight_date']) ? trim($_POST['flight_date']) : '';

$conditions = ["f.flight_date >= SYSDATE"];
if ($airline != '') $conditions[] = "f.airline_name = '$airline'";
if ($flight_number != '') $conditions[] = "f.flight_number = $flight_number";
if ($flight_date != '') $conditions[] = "TRUNC(f.flight_date) = TO_DATE('$flight_date', 'YYYY-MM-DD')";
$whereClause = implode(" AND ", $conditions);

$sql = "
    SELECT f.flight_id, f.airline_name, f.flight_number, f.flight_date, f.capacity,
           f.capacity - NVL(res.count_reserved, 0) AS available_seats
    FROM flights f
    LEFT JOIN (
        SELECT flight_id, COUNT(*) AS count_reserved
        FROM reservations
        GROUP BY flight_id
    ) res ON f.flight_id = res.flight_id
    WHERE $whereClause
    ORDER BY f.flight_date ASC
";
$result_array = execute_sql_in_oracle($sql);
$cursor = $result_array["cursor"];
?>

<h2>Search Available Flights</h2>
<form method="post" action="flight_reservation.php?sessionid=<?= $sessionid ?>&username=<?= $username ?>">
    Airline: <input type="text" name="airline" value="<?= $airline ?>" maxlength="2">
    Flight Number: <input type="number" name="flight_number" value="<?= $flight_number ?>">
    Date (YYYY-MM-DD): <input type="text" name="flight_date" value="<?= $flight_date ?>">
    <input type="submit" value="Search">
</form>

<br/>
<h3>Upcoming Flights</h3>
<table border="1">
<tr>
    <th>Flight ID</th>
    <th>Airline</th>
    <th>Flight Number</th>
    <th>Flight Date</th>
    <th>Capacity</th>
    <th>Available Seats</th>
</tr>
<?php
while ($row = oci_fetch_array($cursor, OCI_ASSOC)) {
    echo "<tr>";
    echo "<td>{$row['FLIGHT_ID']}</td>";
    echo "<td>{$row['AIRLINE_NAME']}</td>";
    echo "<td>{$row['FLIGHT_NUMBER']}</td>";
    echo "<td>{$row['FLIGHT_DATE']}</td>";
    echo "<td>{$row['CAPACITY']}</td>";
    echo "<td>{$row['AVAILABLE_SEATS']}</td>";
    echo "</tr>";
}
oci_free_statement($cursor);
?>
</table>

<hr>
<h3>Reserve a Single Flight</h3>
<form method="post" action="flight_reservation.php?sessionid=<?= $sessionid ?>&username=<?= $username ?>">
    Enter Flight ID: <input type="number" name="reserve_flight_id" required>
    <input type="submit" name="reserve_single" value="Reserve Flight">
</form>

<hr>
<h3>Reserve a Multi-Flight Sequence (Up to 3)</h3>
<form method="post" action="flight_reservation.php?sessionid=<?= $sessionid ?>&username=<?= $username ?>">
    Flight ID 1: <input type="number" name="flight1"> &nbsp;
    Flight ID 2: <input type="number" name="flight2"> &nbsp;
    Flight ID 3: <input type="number" name="flight3">
    <br/><br/>
    <input type="submit" name="reserve_multi" value="Reserve Sequence">
</form>

<br/>
<form method="post" action="regularuser.php?sessionid=<?= $sessionid ?>&username=<?= $username ?>">
    <input type="submit" value="Go Back">
</form>

<?php
// === SINGLE FLIGHT RESERVATION ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reserve_single'])) {
    $flight_id = $_POST['reserve_flight_id'];
    $sql = "SELECT flight_date, capacity FROM flights WHERE flight_id = $flight_id";
    $res = execute_sql_in_oracle($sql);
    $flight_row = oci_fetch_array($res["cursor"], OCI_ASSOC);

    if (!$flight_row) {
        echo "<p style='color:red'><b>Flight ID not found.</b></p>";
    } else {
        $flight_date = $flight_row['FLIGHT_DATE'];
        $capacity = $flight_row['CAPACITY'];
        $today_result = execute_sql_in_oracle("SELECT SYSDATE FROM dual");
        $today = oci_fetch_array($today_result["cursor"], OCI_ASSOC)['SYSDATE'];

        if ($flight_date < $today) {
            echo "<p style='color:red'><b>Reservation failed: Flight is in the past.</b></p>";
        } else {
            $booked = oci_fetch_array(execute_sql_in_oracle(
                "SELECT COUNT(*) AS booked FROM reservations WHERE flight_id = $flight_id"
            )["cursor"], OCI_ASSOC)['BOOKED'];

            if ($booked >= $capacity) {
                echo "<p style='color:red'><b>No available seats.</b></p>";
            } else {
                $exists = oci_fetch_array(execute_sql_in_oracle(
                    "SELECT * FROM reservations WHERE username = '$username' AND flight_id = $flight_id"
                )["cursor"]);

                if ($exists) {
                    echo "<p style='color:red'><b>Already reserved.</b></p>";
                } else {
                    $insert_sql = "INSERT INTO reservations (username, flight_id, seating_grade) VALUES ('$username', $flight_id, 0)";
                    if (execute_sql_in_oracle($insert_sql)['flag']) {
                        echo "<p style='color:green'><b>Reservation successful!</b></p>";
                    } else {
                        echo "<p style='color:red'><b>Reservation failed due to database error.</b></p>";
                    }
                }
            }
        }
    }
}

// === MULTI-FLIGHT RESERVATION ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reserve_multi'])) {
    $flights = array_filter([$_POST['flight1'], $_POST['flight2'], $_POST['flight3']]);
    if (count($flights) < 2) {
        echo "<p style='color:red'><b>Enter at least two flights.</b></p>";
    } else {
        $flight_info = [];
        $flight_dates = [];
        foreach ($flights as $fid) {
            $sql = "SELECT flight_id, airline_name, flight_number, flight_date, capacity FROM flights WHERE flight_id = $fid";
            $res = execute_sql_in_oracle($sql);
            $row = oci_fetch_array($res["cursor"], OCI_ASSOC);
            if (!$row) {
                echo "<p style='color:red'><b>Flight ID $fid not found.</b></p>";
                return;
            }
            $flight_info[$fid] = $row;
            $flight_dates[] = $row['FLIGHT_DATE'];
        }

        $first_date = $flight_dates[0];
        $same_date = array_reduce($flight_dates, fn($carry, $d) => $carry && ($d == $first_date), true);
        $today = oci_fetch_array(execute_sql_in_oracle("SELECT SYSDATE FROM dual")["cursor"], OCI_ASSOC)['SYSDATE'];

        if (!$same_date) {
            echo "<p style='color:red'><b>All flights must be on the same date.</b></p>";
            return;
        }
        if ($first_date < $today) {
            echo "<p style='color:red'><b>Cannot reserve flights in the past.</b></p>";
            return;
        }

        foreach ($flights as $fid) {
            $booked = oci_fetch_array(execute_sql_in_oracle(
                "SELECT COUNT(*) AS booked FROM reservations WHERE flight_id = $fid"
            )["cursor"], OCI_ASSOC)['BOOKED'];
            if ($booked >= $flight_info[$fid]['CAPACITY']) {
                echo "<p style='color:red'><b>Flight $fid is fully booked.</b></p>";
                return;
            }
        }

        $ids = implode(",", $flights);
        $res_check = execute_sql_in_oracle("SELECT flight_id FROM reservations WHERE username = '$username' AND flight_id IN ($ids)");
        while ($row = oci_fetch_array($res_check["cursor"], OCI_ASSOC)) {
            echo "<p style='color:red'><b>Already reserved Flight ID {$row['FLIGHT_ID']}.</b></p>";
            return;
        }

        for ($i = 0; $i < count($flights) - 1; $i++) {
            $f1 = $flight_info[$flights[$i]];
            $f2 = $flight_info[$flights[$i + 1]];
            $check_sql = "
                SELECT * FROM preceding_routes
                WHERE airline_name = '{$f2['AIRLINE_NAME']}'
                  AND flight_number = {$f2['FLIGHT_NUMBER']}
                  AND preceeding_airline = '{$f1['AIRLINE_NAME']}'
                  AND preceeding_flight_number = {$f1['FLIGHT_NUMBER']}";
            if (!oci_fetch_array(execute_sql_in_oracle($check_sql)["cursor"])) {
                echo "<p style='color:red'><b>Invalid sequence: {$flights[$i]} → {$flights[$i + 1]}</b></p>";
                return;
            }
        }

        foreach ($flights as $fid) {
            $insert_sql = "INSERT INTO reservations (username, flight_id, seating_grade) VALUES ('$username', $fid, 0)";
            if (!execute_sql_in_oracle($insert_sql)['flag']) {
                echo "<p style='color:red'><b>Failed to reserve Flight ID $fid due to DB error.</b></p>";
                return;
            }
        }

        echo "<p style='color:green'><b>Multi-flight reservation successful!</b></p>";
    }
}
?>