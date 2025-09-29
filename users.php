<?
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);
$username = $_GET['username'];

echo("<LI><A HREF=\"add_customer.php?sessionid=$sessionid&username=$username\">Add Customer</A></LI>");
echo("<LI><A HREF=\"update_delete_customer.php?sessionid=$sessionid&username=$username\">Update/Delete Customer</A></LI>");
echo("<LI><A HREF=\"seat_grades.php?sessionid=$sessionid&username=$username\">Enter seat grades</A></LI>")


?>