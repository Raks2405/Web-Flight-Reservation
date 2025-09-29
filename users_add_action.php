<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

ini_set("display_errors", 1);
error_reporting(E_ALL); // Enable error reporting for debugging

// Get form inputs and trim spaces
$username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
$password = isset($_POST["password"]) ? trim($_POST["password"]) : "";
$fname = isset($_POST["fname"]) ? trim($_POST["fname"]) : "";
$lname = isset($_POST["lname"]) ? trim($_POST["lname"]) : "";
$role_id = isset($_POST["role_id"]) ? trim($_POST["role_id"]) : "";
$start_date = isset($_POST["start_date"]) ? trim($_POST["start_date"]) : "";
$reg_date = isset($_POST["reg_date"]) ? trim($_POST["reg_date"]) : "";

// Debugging - Print Role ID and Post Data

// **Step 1: Check for Mandatory Fields**
if (empty($username) || empty($password) || empty($fname) || empty($lname) || empty($role_id)) {
    die("<b>Error:</b> Username, Password, First Name, Last Name, and Role are required!<br><a href='users_add.php?sessionid=$sessionid'>Go Back</a>");
}

// **Step 2: Role-Based Validations**
if ($role_id === "2") {
    if (empty($start_date)) {
        die("<b>Error:</b> Start Date is required for Admin users!<br><a href='users_add.php?sessionid=$sessionid'>Go Back</a>");
    }
    if (!empty($reg_date)) {
        die("<b>Error:</b> Admin users should NOT have a Registration Date!<br><a href='users_add.php?sessionid=$sessionid'>Go Back</a>");
    }
} elseif ($role_id === "1") {
    if (empty($reg_date)) {
        die("<b>Error:</b> Registration Date is required for Regular users!<br><a href='users_add.php?sessionid=$sessionid'>Go Back</a>");
    }
    if (!empty($start_date)) {
        die("<b>Error:</b> Regular users should NOT have a Start Date!<br><a href='users_add.php?sessionid=$sessionid'>Go Back</a>");
    }
} elseif ($role_id === "3") {
    if (empty($start_date) || empty($reg_date)) {
        die("<b>Error:</b> Both Start Date and Registration Date are required for Hybrid users!<br><a href='users_add.php?sessionid=$sessionid'>Go Back</a>");
    }
} else {
    die("<b>Error:</b> Invalid Role selected!<br><a href='users_add.php?sessionid=$sessionid'>Go Back</a>");
}

// **Step 3: Prepare SQL Statements**
$sql1 = "INSERT INTO Users (username, password, first_name, last_name, start_date, registration_date) 
         VALUES (
            '$username', 
            '$password',
            '$fname',
            '$lname', 
            " . (!empty($start_date) ? "TO_DATE('$start_date', 'MM-DD-YYYY')" : "NULL") . ", 
            " . (!empty($reg_date) ? "TO_DATE('$reg_date', 'MM-DD-YYYY')" : "NULL") . ")";


// Execute first SQL query (Insert User)
$result_array1 = execute_sql_in_oracle($sql1);
$result1 = $result_array1["flag"];
$cursor1 = $result_array1["cursor"];

if (!$result1) {
    display_oracle_error_message($cursor1);
    die("<b>Error:</b> User Insertion Failed!<br><a href='users_add.php?sessionid=$sessionid'>Go Back</a>");
}
oci_free_statement($cursor1);

// **Step 4: Insert Role into UserRole Table**
$sql2 = "INSERT INTO UserRole (username, id_role) VALUES ('$username','$role_id')";
$result_array2 = execute_sql_in_oracle($sql2);
$result2 = $result_array2["flag"];
$cursor2 = $result_array2["cursor"];

if (!$result2) {
    display_oracle_error_message($cursor2);
    die("<b>Error:</b> User Role Insertion Failed!<br><a href='users_add.php?sessionid=$sessionid'>Go Back</a>");
}
oci_free_statement($cursor2);

// **Step 5: Success - Redirect**
header("Location: users.php?sessionid=$sessionid");
exit();
?>
