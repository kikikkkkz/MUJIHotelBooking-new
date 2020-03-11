<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<style>
.mySlides {display:none}
.w3-left, .w3-right, .w3-badge {cursor:pointer}
.w3-badge {height:13px;width:13px;padding:0}
</style>

<?php
require_once('initialize.php');
$page_title = 'Room Details';
include('header.php');

$room = trim($_GET['room']); //get room type from url
$_SESSION['room']=$room;
$content = '';
$result = '';

//select all properties from roomtype 
$query_str = "SELECT roomType, roomTypeDescription, bedType, area, numberOfOccupants, price, image FROM roomtype WHERE roomType = ?";
			  
$stmt = $db->prepare($query_str);
$stmt->bind_param('s',$room);
$stmt->execute();
$stmt->bind_result($room1,$descroption1,$bedtype1,$area1,$occupant1,$price1,$image1);

echo "<div class=\"details\">";
echo "<button type=\"button\"><a href=";
echo url_for('room.php');
echo ">&laquo; Back to room list</a></button>";

//display results
if($stmt->fetch()) {
	echo "<h3>TYPE $room</h3>\n";
	//display images in gallery
	echo "<div class=\"w3-content w3-display-container\" style=\"max-width:800px\">";
	$img = explode(";",$image1);
	echo "<img class=\"mySlides\" src=$img[0] style=\"width:100%\">";
	echo "<img class=\"mySlides\" src=$img[1] style=\"width:100%\">";
	echo "<img class=\"mySlides\" src=$img[2] style=\"width:100%\">";
	echo "<img class=\"mySlides\" src=$img[3] style=\"width:100%\">";
?>
	<div class="w3-center w3-container w3-section w3-large w3-text-white w3-display-bottommiddle" style="width:100%">
    <div class="w3-left w3-hover-text-khaki" onclick="plusDivs(-1)">&#10094;</div>
    <div class="w3-right w3-hover-text-khaki" onclick="plusDivs(1)">&#10095;</div>
    <span class="w3-badge demo w3-border w3-transparent w3-hover-white" onclick="currentDiv(1)"></span>
    <span class="w3-badge demo w3-border w3-transparent w3-hover-white" onclick="currentDiv(2)"></span>
    <span class="w3-badge demo w3-border w3-transparent w3-hover-white" onclick="currentDiv(3)"></span>
    <span class="w3-badge demo w3-border w3-transparent w3-hover-white" onclick="currentDiv(4)"></span>
    </div>
	</div>
<?php
	echo "<p>";
	echo "Area $area1 m<sup>2</sup><br>
	Bed Type | $bedtype1 <br>
	1-$occupant1 Occupants<br>
	Check-in｜ 14:00<br>Check-out｜ 12:00<br><br>
	Room Rate <b>RMB $price1</b> /night<br>
	<em>Breakfast, Tax & Service Charge Included</em><br>
	<strong>Standard complimentary items and fixtures</strong><br>$descroption1<br>";
	echo "</p>";
}
$stmt->free_result();

//display comments for the room type
echo "<div id=\"display_comment\"></div>";

//only members can see the option of writing comments
if(isset($_SESSION['admin_id'])) {
?>
<html>
<body>
<a href=# class="comment_link"><b>Write Comments</b></a>
	<form method="POST" id="comment_form" class="dno">
	    <div class="form-group">
	     <input type="hidden" name="comment_type" id="comment_type" value="<?php echo $room;?>" />
	     <textarea rows="4" cols="50" id="comment_name" name="comment_name" placeholder="How you feel about our hotel..."></textarea>
	    </div>
	    <div class="form-group">
	     <br /><input type="submit" name="submit" id="submit" class="btn btn-info" value="Submit" />
	    </div>
   	</form>
   <span id="comment_message"></span>
   <br />
</body>
</html>

<?php
} 

?>

<style>
.dno {
	display: none;
	text-align: center;
}
</style>
<script>
$(document).ready(function(){
	//show comment area when link is clicked
	$(".comment_link").on("click",function(event) {
		event.preventDefault();
		$(this).hide();
		$("#comment_form").show();
	});

	//do things in addcomment.php when submit 
	$("#comment_form").on("submit", function(event) {
		event.preventDefault();
		var form_data = $(this).serialize();
		$.ajax({
			url: "add_comment.php",
			method: "POST",
			data: form_data,
			dataType: "JSON",
			success: function(data) {
				if(data.errors != '') {
					$('#comment_form')[0].reset();
					$('#comment_message').fadeIn().html(data.errors);
					setTimeout(function(){
						$('#comment_message').fadeOut("slow");
					},2000);
					load_comment();
				}
			}
		})
	});

	load_comment();

	//load comment from database with fetch_comment.php
	function load_comment()
	{
	  $.ajax({
	   url:"fetch_comment.php",
	   method:"POST",
	   success:function(data)
	   {
	    $('#display_comment').html(data);
	   }
	  })
	}

});

</script>

<script>
var slideIndex = 1;
showDivs(slideIndex);

function plusDivs(n) {
  showDivs(slideIndex += n);
}

function currentDiv(n) {
  showDivs(slideIndex = n);
}

function showDivs(n) {
  var i;
  var x = document.getElementsByClassName("mySlides");
  var dots = document.getElementsByClassName("demo");
  if (n > x.length) {slideIndex = 1}    
  if (n < 1) {slideIndex = x.length}
  for (i = 0; i < x.length; i++) {
     x[i].style.display = "none";  
  }
  for (i = 0; i < dots.length; i++) {
     dots[i].className = dots[i].className.replace(" w3-white", "");
  }
  x[slideIndex-1].style.display = "block";  
  dots[slideIndex-1].className += " w3-white";
}
</script>

<?php

//start booking button
echo "<button id=\"book\" class=\"btn\"><a href=\"search.php\">Start Booking</a></button>"; 
if($_SESSION['callback_url']!=url_for('reservation.php')){
	$_SESSION['room']=$room; //room viewed on reservation will not be added again 
}

echo "</div>";

include('footer.php');

$db->close();
?>

