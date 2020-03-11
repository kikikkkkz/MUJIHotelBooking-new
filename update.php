<?php
require_once('initialize.php');
$page_title = 'Edit profile';
include('header.php');

$id = $_SESSION['admin_id'];

$errors = [];
$email = '';
$password = '';

//select all properties from the member 
$query_str = "SELECT * FROM members WHERE memberNumber = " .$id. "";
			  
$res=$db->query($query_str);
if ($res->num_rows > 0) {
	$rows = $res->fetch_assoc();
}

//updating the profile when button clicked
if (isset($_POST['update'])) {

  $admin['id'] = $id;
  $admin['first_name'] = $_POST['first_name'] ?? '';
  $admin['last_name'] = $_POST['last_name'] ?? '';
  $admin['avatar'] = $_POST['avatar'] ?? '';
  $admin['phone_number'] = $_POST['phone_number'] ?? '';
  $admin['country'] = $_POST['country'] ?? '';

  $result = update_admin($admin);
  if($result === true) {
      $_SESSION['imagePath'] = $admin['avatar']; //set session image path
    	redirect_to($_SESSION['callback_url']); //return to previous page
    } else {
		$errors = $result; //error message
    }
}

?>

<div class="register">

    <h1>Edit Profile</h1>

    <?php echo display_errors($errors); ?>

    <button type="button"><a href="<?php echo url_for('profile.php'); ?>">&laquo;Back to profile</a></button>

    <form method="post">
      <div class="create">
      <br />Avatar<br />
      <ul>
      <li><input type="radio" id="avatar1" name="avatar" value="images/avatar1.jpg" <?php if($rows['imagePath'] == "images/avatar1.jpg") echo "checked" ;  ?> >
        <label for="avatar1"><img src="images/avatar1.jpg" style="width:200px"/></label>
      </li>
      
      <li>
       <input type="radio" id="avatar2" name="avatar" value="images/avatar2.jpg" <?php if($rows['imagePath'] == "images/avatar2.jpg") echo "checked" ;  ?>>
      <label for="avatar2"><img src="images/avatar2.jpg" style="width:200px"/></label>
      </li>

      <li>
        <input type="radio" id="avatar3" name="avatar" value="images/avatar3.jpg" <?php if($rows['imagePath'] == "images/avatar3.jpg") echo "checked" ;  ?>>
        <label for="avatar3"><img src="images/avatar3.jpg" style="width:200px"/></label>   
      </li>
      </ul>
      </div>

      <table cellspacing="10">
      	<tr>
        <td >Email</td>
        <td><input type="text" name="email" id="email" value="<?php echo $rows['email']; ?>" readonly/><br /></td>
        </tr>

        <tr>
          <td>First name</td>
          <td><input type="text" name="first_name" value="<?php echo $rows['firstName']; ?>" /></td>
        </tr>

        <tr>
          <td>Last name</td>
          <td><input type="text" name="last_name" value="<?php echo $rows['lastName']; ?>" /></td>
        </tr>

        <tr>
          <td>Phone Number</td>
          <td><input type="text" name="phone_number" value="<?php echo $rows['phoneNumber']; ?>" /><br /></td>
        </tr>

        <tr>
          <td>Country</td>
          <td><input type="text" name="country" value="<?php echo $rows['country']; ?>" /><br /></td>
        </tr>

        <br />
        <tr>
          <td></td>
          <td><input type="submit" name="update" value="Update" /></td>
        </tr>
		   
      </table>

    </form>
</div>

<?php 
include('footer.php');

$db->close();
?>