<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

ini_set("display_errors", 1);
error_reporting(E_ALL);

$username = "";
$fid = "";
$error = "";


echo "<h2>Update Seat Grade - Step 1</h2>";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    $username = trim($_POST["username"]);
    $fid = trim($_POST["fid"]);

    if (empty($username) || empty($fid)) {
        $error = "Both Username and Flight ID are required.";
    } else {
        $sql = "SELECT COUNT(*) FROM Reservations WHERE username = '$username' AND flight_id = $fid";
        $result_array = execute_sql_in_oracle($sql);
        $cursor = $result_array["cursor"];
        $row = oci_fetch_array($cursor);
        oci_free_statement($cursor);

        if ($row[0] > 0) {
            header("Location: update_grade_form.php?sessionid=$sessionid&username=" . urlencode($username) . "&fid=" . urlencode($fid));
            exit();
        } else {
            $error = "Invalid Username or Flight ID.";
        }
    }
}

echo "<h3>Step 1: Verify Reservation</h3>";

if (!empty($error)) {
    echo "<b>Error:</b> $error<br><br>";
}

echo "<form method='post' action='seat_grades.php?sessionid=$sessionid'>
        Username: <input type='text' name='username' value='" . htmlspecialchars($username) . "'><br><br>
        Flight ID: <input type='text' name='fid' value='" . htmlspecialchars($fid) . "'><br><br>
        <input type='submit' name='submit' value='Next'>
      </form>";

echo "<br><form method='post' action='users.php?sessionid=$sessionid'>
        <input type='submit' value='Go Back'>
      </form>";
?>
