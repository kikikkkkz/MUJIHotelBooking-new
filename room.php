<?php
require_once('initialize.php');
$page_title = 'Room Choice';
include('header.php');

$_SESSION['callback_url'] = url_for('room.php'); //session the url address

echo "<div class=\"room\"><h2>Rooms</h2>";

//set sql query string and get the room info from database
$query_str = "SELECT roomType, area, bedType, price, image FROM roomtype";
$res = $db->query($query_str);

//list all items
echo "<div class=\"container\">";
while($row = $res->fetch_row()){
	echo "<div class=\"frame\">";
	echo "<div class=\"box1\">";
	$img=explode(";",$row[4]);
	echo "<div id=\"cover-image\"><a href=";	
	echo url_for('roomdetails.php?room='.$row[0]);
	echo "><img src=".$img[0]." width=\"100%\" alt=\"\" /></a></div>";
	echo "</div>";

	echo "<div class=\"box1\"><br /><br /><br />";
	format_name_as_link($row[0], $row[0], "roomdetails.php"); //each item links to specific model
	echo "<p>";
	echo "Area ".$row['1']." m<sup>2</sup><br>
	Bed Type | ".$row['2']." <br>
	Room Rate <b>RMB ".$row['3']."</b> /night<br>";
	echo "</p>";
	echo "</div>";
	echo "</div";
};
echo "</div>";
echo "</div>";
echo "</div>";

//unset session for room
if(isset($_SESSION['room'])){
	unset($_SESSION['room']);
}

//release object
$res->free_result();
$db->close();

include('footer.php');
?>