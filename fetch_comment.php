<?php
require_once('initialize.php');
date_default_timezone_set('America/Los_Angeles'); //set time zone

//get comment and its member info from database
$query = "
SELECT members.firstName, members.imagePath, comment.content, comment.memberNumber, comment.timePosted FROM members LEFT JOIN comment ON members.memberNumber = comment.memberNumber  
WHERE comment.roomType = '".$_SESSION['room']."'";

$res = $db->query($query);

//show number of comments
echo "<b>".$res->num_rows." Comments </b><br />";
$output = '<br />';
if($res->num_rows > 0) {
	while($row=$res->fetch_assoc()) {
	  $output .= " 
   		<div class=\"panel panel-default\"> 
    	<div class=\"panel-heading\"> 
   		 <img src=".$row["imagePath"]." width=\"50\"  />  "; 
 
  	  $output .= "<b>".$row["firstName"]." </b>| ".$row["timePosted"]."</i></div> 
   		 <div class=\"panel-body\">".$row["content"]."</div><br />"; 
	}

} else {
	$output .= "No one has visited yet."; //0 result
}

echo $output;
echo "<br>";

?>