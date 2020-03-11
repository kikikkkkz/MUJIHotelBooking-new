<?php
require_once('initialize.php');
$page_title = 'Profile';
include('header.php');

$id = $_SESSION['admin_id'];
$_SESSION['callback_url']=url_for('profile.php');
$msg = ''; //message for delete
$msg_update=''; //message for update

//call delete function when delete button is clicked
if(isset($_POST['delete'])){
	delete_comment($_POST['hidden']);
	$msg='Delete successfully!';
}

//display message when information is updated
if(isset($_POST['update'])){
	$msg_update='Update successfully!';
}

//select all properties from the member 
$query_str = "SELECT lastName,firstName,email,phoneNumber,country, imagePath FROM members WHERE memberNumber = ?";
			  
$stmt = $db->prepare($query_str);
$stmt->bind_param('i',$id);
$stmt->execute();
$stmt->bind_result($last1,$first1,$email1,$phone1,$country1,$imagePath1);

echo "<div class=\"login\">";

//display profile 
if($stmt->fetch()) {
	echo "<h4>Hello, $first1 $last1!</h4>\n";
	echo "<img src=".$imagePath1." width=\"200\" alt=\"$id\" />";
	echo "<table cellspacing=5><p>";
	echo "<tr><td>Email</td> <td>|</td> <td>$email1</td></tr>
	<tr><td>Phone Number</td> <td>|</td> <td>$phone1</td></tr> 
	<tr><td>Country</td> <td>|</td> <td>$country1</td></tr>";
	echo "</p></table><br />";
}
$stmt->free_result();

$_SESSION['imagePath'] = $imagePath1;
$_SESSION['firstName'] = $first1; 

//updating the info
echo "\t<button type=\"button\"><a href=\"update.php\">Edit</a></button>";
echo "\t<button type=\"button\"><a href=\"change.php\">Change Password</a></button><br>";
echo $msg_update;
echo "<br>";

$query_str = "SELECT phoneNumber FROM members WHERE memberNumber=".$id."";
$res = $db->query($query_str);
while ($row = $res->fetch_assoc()) {
	$number = $row['phoneNumber'];
}

$res->free_result(); 

//display reservation
$query_str = "SELECT bookingNumber, checkInDate, checkOutDate, priceEach, roomType FROM reservation WHERE memberNumber=".$id."";
			  
$res = $db->query($query_str);

echo "<p>";
echo "<b>Your Reservation</b><br>";
if($res->num_rows > 0) {
	while ($row = $res->fetch_assoc()) {
		echo "<table cellspacing=5>";
		echo "<tr><td>Booking Number <b>".$row['bookingNumber']."</b></td> <td>|</td> <td>".$row['checkInDate']." ~ ".$row['checkOutDate']."</td></tr>";
		echo "<tr><td></td> <td>|</td> <td><b><a href=";
		echo url_for('roomdetails.php?room='.$row['roomType']);
		echo ">Type ".$row['roomType']."</b></a></td></tr>";
		echo "<tr><td></td> <td>|</td> <td>RMB ".$row['priceEach']." /night</td></tr>";
		echo "</p></table>";
	}
}else{
	echo "Haven't made any reservations yet."; //show 0 result it there is nothing matched 
}
echo "</p>";
$res->free_result();

echo "<br>";

//display comments posted by the member
$query_str = "SELECT id, content, timePosted, roomType FROM comment WHERE memberNumber = ".$id."";
			  
$res = $db->query($query_str);

echo "<p>";
echo "<b>Your Comment</b><br>";
echo "<table cellspacing=5>";

if($res->num_rows > 0) {
	while ($row = $res->fetch_assoc()) {
		$url=url_for('profile.php');
		echo "<form action=$url method=POST>";
		echo "<tr><td>".$row['timePosted']."</td> <td>|</td>"; 
		echo "<td><b><a href=";
		echo url_for('roomdetails.php?room='.$row['roomType']);
		echo ">Type ".$row['roomType']."</a></b></td></tr>";
		echo "<tr><td> <td>|</td> </td><td>".$row['content']."</td>";
		//button for deleting comment
		echo "<td><input class=del type=submit name=delete value=delete></td></tr>";
		echo "<tr><td><input type=hidden name=hidden value=".$row['id']."></td></tr>";
		echo "</form>";
	}
}else{
	echo "Haven't posted any comments yet."; //show 0 result it there is nothing matched 
}

echo "</p></table>";
echo $msg;

$res->free_result();

include('footer.php');

$db->close();
?>
