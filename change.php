<?php
require_once('initialize.php');
$id = $_SESSION['admin_id'];

$page_title = 'Edit profile';
include('header.php');

$errors = [];
$email = '';
$password = '';

//updating the profile 
if (isset($_POST['confirm'])) {

  $admin['id'] = $id;
  $admin['cur_password'] = $_POST['cur_password'] ?? '';
  $admin['new_password'] = $_POST['new_password'] ?? '';
  $admin['confirm_password'] = $_POST['confirm_password'] ?? '';
  
  //validate and insert to database
  $result = change_password($admin);
  if($result === true) {
      redirect_to($_SESSION['callback_url']); //return to previous page
  } else {
    $errors = $result; //error message
  }
}

?>

<div class="register">

    <h1>Change Password</h1>

    <button type="button"><a href="<?php echo url_for('profile.php'); ?>">&laquo;Back to profile</a></button>

    <?php echo display_errors($errors); ?>

    <form method="post">

      <table cellspacing="10">

      <tr>
        <td>Current Password</td>
        <td><input type="password" name="cur_password" value="" /></td>
      </tr>

      <tr>
        <td>New Password</td>
        <td><input type="password" name="new_password" value="" /></td>
      </tr>

      <tr>
        <td>Confirm New Password</td>
        <td><input type="password" name="confirm_password" value="" /></td>
      </tr>

      <br />
      <tr>
        <td></td>
        <td><input class="btn" type="submit" name="confirm" value="Confirm" /></td>
      </tr>
       
      </table>

    </form>
</div>

<?php 
include('footer.php');

$db->close();
?>