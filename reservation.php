<?php
require_once('initialize.php');
$page_title = 'Reservation';
include('header.php');
date_default_timezone_set('America/Los_Angeles'); //set time zone

$_SESSION['callback_url'] = url_for('reservation.php');

echo "<div class=\"reservation\">";

//when form is submitted, insert reservation to database
if(is_post_request()){

	$reserve['memberNumber']=$_SESSION['admin_id'];
	$reserve['bookingDate']=date("Y-m-d");
	$reserve['checkInDate']=$_SESSION['check_in'];
	$reserve['checkOutDate']=$_SESSION['check_out'];
	$reserve['numberOfGuests']=$_SESSION['occupants'];
	$reserve['numberOfRoomBooked']="1";
	$reserve['bedType']=$_SESSION['bed'];
	$reserve['roomType']=$_SESSION['room'];
	$reserve['priceEach']=$_SESSION['priceEach'];
	$reserve['roomNumber']=$_SESSION['roomNumber'];
	if(isset($_POST['bookingComments'])) {
		$reserve['bookingComments']=$_POST['bookingComments'];
	}
	
	$result = insert_reserve($reserve);
    if($result === true) {
      $new_id = mysqli_insert_id($db); //insert id to database
      $_SESSION['booking_id']=$new_id; //set session booking number
      
      echo "<h2>Thank you for your reservation!</h2>";
      echo "Your booking number is <b>".$_SESSION['booking_id'].".</b><br>";
      echo "Have a nice stay in MUJI!<br>";

      //unset sessions of booking info
      unset($_SESSION['check_in']);
      unset($_SESSION['check_out']);
      unset($_SESSION['occupants']);
      unset($_SESSION['bed']);
      unset($_SESSION['room']);
      unset($_SESSION['priceEach']);
      unset($_SESSION['roomNumber']);
      unset($_SESSION['bookingComment']);
    } else {
      $errors = $result; //error message
      echo $errors;
    }

}else{
	$reserve='';
}

echo "<br>";

//button for directing to user profile
echo "<button type=\"button\" value=\"Go to profile\"><a href=";
echo url_for('profile.php');
echo ">Go to profile</a></button>";

echo "</div>";

include('footer.php');
?>