<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

$username = $_GET["username"];
$fid = $_GET["fid"];
$grade = isset($_POST["grade"]) ? trim($_POST["grade"]) : "";
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update"])) {
    if ($grade === "") {
        $error = "Seating grade is required.";
    } elseif (!in_array($grade, ['0', '1', '2'])) {
        $error = "Seating grade must be 0, 1, or 2.";
    } else {
        $sql = "UPDATE Reservations 
                SET seating_grade = $grade 
                WHERE username = '$username' AND flight_id = $fid";

        $result_array = execute_sql_in_oracle($sql);
        $cursor = $result_array["cursor"];

        if ($result_array["flag"]) {
            $success = "Seating grade updated successfully.";
        } else {
            display_oracle_error_message($cursor);
            $error = "Failed to update the grade.";
        }

        oci_free_statement($cursor);
    }
}

echo "<h3>Step 2: Enter New Seating Grade</h3>";
echo "<b>Username:</b> $username<br>";
echo "<b>Flight ID:</b> $fid<br><br>";

if (!empty($error)) {
    echo "<b>Error:</b> $error<br><br>";
}

if (!empty($success)) {
    echo "<b>$success</b><br><br>";
}

echo "<form method='post' action='update_grade_form.php?sessionid=$sessionid'>";
echo "<input type='hidden' name='username' value='" . htmlspecialchars($username) . "'>";
echo "<input type='hidden' name='fid' value='" . htmlspecialchars($fid) . "'>";
echo "Seating Grade (0, 1, 2): <input type='number' name='grade' min='0' max='2' required><br><br>";
echo "<input type='submit' name='update' value='Update Grade'>";
echo "</form>";

echo "<br><form method='post' action='seat_grades.php?sessionid=$sessionid'>";
echo "<input type='submit' value='Back'>";
echo "</form>";
?>
