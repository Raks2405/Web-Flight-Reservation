<?php
session_start();
include "utility_functions.php";


if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access Denied: Admins only.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['manage_users'])) {
    $action = $_POST['action'];
    $username = $_POST['target_user'];

    if ($action == 'delete') {
        $sql = "DELETE FROM Users WHERE username = '$username'";
    } elseif ($action == 'update' && isset($_POST['new_data'])) {
        $new_data = $_POST['new_data'];
        $sql = "UPDATE Users SET $new_data WHERE username = '$username'";
    } elseif ($action == 'add') {
        $password = $_POST['password'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $role = $_POST['role'];
        $sql = "INSERT INTO Users (username, password, first_name, last_name, role) VALUES ('$username', '$password', '$first_name', '$last_name', '$role')";
    }

    $result = execute_sql_in_oracle($sql);
    if ($result['flag'] === false) {
        die("User management action failed.");
    }
    echo "User management action successful.";
}
?>
