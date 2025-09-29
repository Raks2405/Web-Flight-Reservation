<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

ini_set("display_errors", 1);
error_reporting(E_ALL);

// Step 1: Get and trim inputs
$fname = isset($_POST["fname"]) ? trim($_POST["fname"]) : "";
$lname = isset($_POST["lname"]) ? trim($_POST["lname"]) : "";
$reg_date = isset($_POST["reg_date"]) ? trim($_POST["reg_date"]) : "";

// Step 2: Validate required fields
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($fname) || empty($lname) || empty($reg_date)) {
        die("<b>Error:</b> All fields are required!<br><a href='add_customer_error.php?sessionid=$sessionid'>Go Back</a>");
    }

    // Redirect to Step 2 with POST data
    echo "<form method='post' action='add_customer_step2.php?sessionid=$sessionid'>";
    echo "<input type='hidden' name='fname' value='" . htmlspecialchars($fname) . "'>";
    echo "<input type='hidden' name='lname' value='" . htmlspecialchars($lname) . "'>";
    echo "<input type='hidden' name='reg_date' value='" . htmlspecialchars($reg_date) . "'>";
    echo "<script>document.forms[0].submit();</script>";
    echo "</form>";
    exit();
}

// Display Step 1 form
echo "<h2>Step 1: Enter Basic Customer Info</h2>";
echo "<form method='post' action='add_customer.php?sessionid=$sessionid'>";
echo "First Name: <input type='text' name='fname' value='$fname'><br /><br />";
echo "Last Name: <input type='text' name='lname' value='$lname'><br /><br />";
echo "Registration Date (MM/DD/YYYY): <input type='text' name='reg_date' value='$reg_date'><br /><br />";
echo "<input type='submit' value='Next'>";
echo "</form>";
echo("<form method='post' action='users.php?sessionid=$sessionid'>
        <input type='submit' value='Go Back'>
      </form>")
?>
