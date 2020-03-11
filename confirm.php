<?php
require_once('initialize.php');
$page_title = 'Confirm';
include('header.php');

$room=trim($_GET['room']); //get room
$_SESSION['room']=$room;
$_SESSION['callback_url']=url_for('confirm.php?room='.$room);

echo "<div class=\"confirm\">";

//when book button is clicked
if(is_post_request()&&isset($_POST['book'])){ 
	$occupants=$_POST['occupants'] ?? '';
	$_SESSION['occupants']=$occupants;
	$bed=$_POST['bed'] ?? '';
	$_SESSION['bed']=$bed;
}
require_login(); //login is required to make a reservation

//get session id
$id=$_SESSION['admin_id'];

//display user info
$query_str = "SELECT lastName,firstName,email,phoneNumber FROM members WHERE memberNumber = ?";

$stmt = $db->prepare($query_str);
$stmt->bind_param('i',$id);
$stmt->execute();
$stmt->bind_result($last1,$first1,$email1,$phone1);;

if($stmt->fetch()) {
	echo "<h2>$first1,</h2>";
	echo "<h3>please confirm your booking</h3>\n";
	echo "<table cellspacing=5><p>";
	echo "<tr><td>Name</td> <td>|</td> <td>$first1 $last1</td></tr>
	<tr><td>Email</td> <td>|</td> <td>$email1</td></tr>
	<tr><td>Phone Number</td> <td>|</td> <td>$phone1</td></tr>";
	echo "</p></table><br />";
}
$stmt->free_result();

echo "<br>";

//get checkin and checkout date from session
$checkIn = $_SESSION['check_in'];
$checkOut = $_SESSION['check_out'];
echo "Check-in | $checkIn 14:00<br>";
echo "Check-out | $checkOut 12:00<br>";

echo "<br>";

//assign the room number, display booking options from session
$query_str = "SELECT DISTINCT room.roomNumber, roomtype.price 
			  FROM room
			  LEFT JOIN roomtype ON room.roomType = roomtype.roomType
			  WHERE room.roomType = '".$room."' AND
			  room.roomNumber NOT IN
			  (	SELECT roomNumber 
				FROM reservation
				WHERE checkInDate <= '".$checkIn."' AND checkOutDate > '".$checkIn."'
			 	OR (checkInDate < '".$checkOut."' AND checkOutDate >= '".$checkOut."')
			 	OR (checkInDate >= '".$checkIn."' AND checkOutDate <= '".$checkOut."')
			  ) 
			  ORDER BY room.roomNumber ASC LIMIT 1;";
$res = $db->query($query_str);

foreach($res as $row) {
	echo "<b>Room type</b> | Type $room<br>";
	echo "<b>Room Number</b> | ".$row['roomNumber']."<br />";
	echo "<b>Number of room</b> | 1<br>";
	echo "<b>Number of guests</b> | ".$_SESSION['occupants']."<br>";
	echo "<b>Type of bed type</b> | ".$_SESSION['bed']."<br>";

	echo "<h3>Price | RMB ".$row['price']." /night</h3><br>";
	$_SESSION['priceEach']=$row['price'];
	$_SESSION['roomNumber']=$row['roomNumber'];
}
$res->free_result();

//confirm button and comment area
echo "<br />";
echo "<form action=\"reservation.php\" method=\"POST\">";
echo "Booking Comments<br />";
echo "<textarea type=\"text\" name=\"bookingComments\" rows=\"5\" cols=\"40\" placeholder=\"Anything you want us to prepare...\" value=\"";
	if(isset($_POST['bookingComments'])) echo $_POST['bookingComments'];
		echo "\" ></textarea><br />";
echo "<br /><input type=\"submit\" name=\"confirm\" value=\"Confirm\">";
echo "</form>";

echo "</div>";

include('footer.php');

$db->close();
?>