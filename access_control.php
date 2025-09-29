<?php
session_start();

function check_access($required_role) {
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        header("Location: login.html");
        exit();
    }
    
    if ($required_role == 'admin' && $_SESSION['role'] != 'admin') {
        die("Access Denied: Admins only.");
    }
    if ($required_role == 'regular' && $_SESSION['role'] == 'admin') {
        die("Access Denied: Regular users only.");
    }
    
}
?>
