<?php
require_once('initialize.php');
date_default_timezone_set('America/Los_Angeles'); //set time zone

//connect to database
$connect = new PDO('mysql:host=localhost;dbname=hotel', 'root', '');
$errors = '';
$comment_name = '';

//validate comment
if (isset($_POST['comment_name'])) {
  if(is_blank($_POST['comment_name'])) {
    $errors = "<p>Comment cannot be blank.</p>";
  } elseif (!has_length($_POST['comment_name'], array('min' => 10, 'max' => 200))) {
    $errors = "<p>Comment must be between 10 and 200 characters.</p>";
  } else 
    $comment_name=$_POST['comment_name'];
}

//add comment to database
if($errors == '')
{
 $query = "
 INSERT INTO comment 
 (content, memberNumber, roomType, timePosted) 
 VALUES (:content, :id, :roomtype, :timeposted)
 ";
 $statement = $connect->prepare($query);
 $statement->execute(
  array(
   ':content' => $comment_name,
   ':id' => $_SESSION['admin_id'],
   ':roomtype' => $_POST['comment_type'],
   ':timeposted' => date('Y-m-d')
  )
 );
 $errors = '<label class="text-success">Comment Added</label>';
}

$data = array(
 'errors'  => $errors
);

echo json_encode($data);

?>