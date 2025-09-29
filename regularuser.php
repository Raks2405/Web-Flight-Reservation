<?
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);
$username = $_GET['username'];

// Generate the query section
echo("
 Welcome to Regular User Page
  <br/>
  <br/>

  <LI><A HREF=\"personal_info.php?sessionid=$sessionid&username=$username\">My Personal Information</A></LI>
  <LI><A HREF=\"reservation_info.php?sessionid=$sessionid&username=$username\">My Reservation Informations</A></LI>
  <LI><A HREF=\"flight_reservation.php?sessionid=$sessionid&username=$username\">Flight Reservations</A></LI>
  <br/>
  <br/>
  <form method=\"post\" action=\"change_password.php?sessionid=$sessionid&username=$username\">
  <input type=\"submit\" value=\"Change Password\">
  </form>
  <form method=\"post\" action=\"welcomepage.php?sessionid=$sessionid&username=$username\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
  ");
?>