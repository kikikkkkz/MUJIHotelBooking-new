<?php
$db = $_SESSION['connection'] =  connectToDB('localhost', 'root', '', 'hotel');

define("DB_SERVER", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "hotel");

  function connectToDB($dbhost, $dbuser, $dbpass, $dbname) {
      $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
      if (mysqli_connect_errno()) {
          //quit and display error and error number
          die("Database connection failed:" .
              mysqli_connect_error() .
              " (" . mysqli_connect_errno() . ")"
          );
      }
      return $connection;
  }

  function db_connect() {
    $connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    confirm_db_connect();
    return $connection;
  }

  function db_disconnect($connection) {
    if(isset($connection)) {
      mysqli_close($connection);
    }
  }

  function db_escape($connection, $string) {
    return mysqli_real_escape_string($connection, $string);
  }

  function confirm_db_connect() {
    if(mysqli_connect_errno()) {
      $msg = "Database connection failed: ";
      $msg .= mysqli_connect_error();
      $msg .= " (" . mysqli_connect_errno() . ")";
      exit($msg);
    }
  }

  function confirm_result_set($result_set) {
    if (!$result_set) {
    	exit("Database query failed.");
    }
  }

  function url_for($script_path) {
  // add the leading '/' if not present
  if($script_path[0] != '/') {
    $script_path = "/" . $script_path;
  }
  return WWW_ROOT . $script_path;
  }

  function redirect_to($location) {
    header("Location: http://" . $_SERVER["HTTP_HOST"]. $location);
    exit;
  }

  //check if form is post
  function is_post_request() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

  //Performs all actions necessary to log in an admin
  function log_in_admin($admin) {
  //Renerating the ID protects the admin from session fixation.
    session_regenerate_id();
    $_SESSION['admin_id'] = $admin['memberNumber'];
    $_SESSION['last_login'] = time();
    $_SESSION['email'] = $admin['email'];
    return true;
  }

  //Performs all actions necessary to log out an admin
  function log_out_admin() {
    unset($_SESSION['admin_id']);
    unset($_SESSION['last_login']);
    unset($_SESSION['email']);
    return true;
  }

  //check session id to see if logged in
  function is_logged_in() {
    return isset($_SESSION['admin_id']);
  }

  //require user login
  function require_login() {
    if(!is_logged_in()) {
      redirect_to(url_for('login.php'));
    } else {
    }
  }

  //find the user account using id
  function find_admin_by_id($id) {
    global $db;

    $sql = "SELECT * FROM members ";
    $sql .= "WHERE memberNumber='" . db_escape($db, $id) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $admin = mysqli_fetch_assoc($result); // find first
    mysqli_free_result($result);
    return $admin; // returns an assoc. array
  }

  //find the user account using email
  function find_admin_by_email($email) {
    global $db;

    $sql = "SELECT * FROM members ";
    $sql .= "WHERE email='" . db_escape($db, $email) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $admin = mysqli_fetch_assoc($result); // find first
    mysqli_free_result($result);
    return $admin; // returns an assoc. array
  }

  //display errors on the page
  function display_errors($errors=array()) {
  $output = '';
  if(!empty($errors)) {
    $output .= "<div class=\"errors\">";
    $output .= "<h3>Please fix the following errors:</h3>";
    $output .= "<ul>";
    foreach($errors as $error) {
      $output .= h($error) . "<br />";
    }
    $output .= "</ul><br />";
    $output .= "</div>";
  }
  return $output;
  }

  //for casting special characters
  function h($string="") {
    return htmlspecialchars($string);
  }

  //generate link for detail page
  function format_name_as_link($id,$name,$page){
    echo "<b><a href=\"$page?room=$id\">TYPE $name</a></b>";
  }

  //validate information input for creating an account
  function validate_admin($admin, $options=[]) {

    $password_required = $options['password_required'] ?? true;

    if(is_blank($admin['first_name'])) {
      $errors[] = "First name cannot be blank.";
    } elseif (!has_length($admin['first_name'], array('min' => 2, 'max' => 50))) {
      $errors[] = "First name must be between 2 and 50 characters.";
    }

    if(is_blank($admin['last_name'])) {
      $errors[] = "Last name cannot be blank.";
    } elseif (!has_length($admin['last_name'], array('min' => 2, 'max' => 50))) {
      $errors[] = "Last name must be between 2 and 50 characters.";
    }

    if(is_blank($admin['email'])) {
      $errors[] = "Email cannot be blank.";
    } elseif (!has_length($admin['email'], array('max' => 50))) {
      $errors[] = "Email must be less than 50 characters.";
    } elseif (!has_valid_email_format($admin['email'])) {
      $errors[] = "Email must be a valid format.";
    } elseif (!has_unique_email($admin['email'], $admin['id'] ?? 0)) {
      $errors[] = "Email not allowed. Try another.";
    }

    if(is_blank($admin['avatar'])) {
      $errors[] = "Avatar cannot be blank.";
    }

    if(is_blank($admin['phone_number'])) {
      $errors[] = "Phone number cannot be blank.";
    } elseif (!has_length($admin['phone_number'], array('min' => 10, 'max' => 11))) {
      $errors[] = "Phone number must be valid.";
    }

    if(is_blank($admin['country'])) {
      $errors[] = "Country cannot be blank.";
    } elseif (!has_length($admin['country'], array('min' => 2, 'max' => 50))) {
      $errors[] = "Country must be between 2 and 50 characters.";
    }

    if($password_required) {
      if(is_blank($admin['password'])) {
        $errors[] = "Password cannot be blank.";
      }

      if(is_blank($admin['confirm_password'])) {
        $errors[] = "Confirm password cannot be blank.";
      } elseif ($admin['password'] !== $admin['confirm_password']) {
        $errors[] = "Password and confirm password must match.";
      }
    }
    
    return $errors;
  }

  //validate update inputs
  function validate_update($admin, $options=[]) {

    $password_required = $options['password_required'] ?? true;

    if(is_blank($admin['avatar'])) {
      $errors[] = "Avatar cannot be blank.";
    }

    if(is_blank($admin['first_name'])) {
      $errors[] = "First name cannot be blank.";
    } elseif (!has_length($admin['first_name'], array('min' => 2, 'max' => 50))) {
      $errors[] = "First name must be between 2 and 50 characters.";
    }

    if(is_blank($admin['last_name'])) {
      $errors[] = "Last name cannot be blank.";
    } elseif (!has_length($admin['last_name'], array('min' => 2, 'max' => 50))) {
      $errors[] = "Last name must be between 2 and 50 characters.";
    }


    if(is_blank($admin['phone_number'])) {
      $errors[] = "Phone number cannot be blank.";
    } elseif (!has_length($admin['phone_number'], array('min' => 10, 'max' => 11))) {
      $errors[] = "Phone number must be valid.";
    }

    if(is_blank($admin['country'])) {
      $errors[] = "Country cannot be blank.";
    } elseif (!has_length($admin['country'], array('min' => 2, 'max' => 50))) {
      $errors[] = "Country must be between 2 and 50 characters.";
    }
    
    return $errors;
  }

  //insert new account to database
  function insert_admin($admin) {
    global $db;

    $errors = validate_admin($admin);
    if (!empty($errors)) {
      return $errors;
    }

    $hashed_password = password_hash($admin['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO members ";
    $sql .= "(firstName, lastName, email, phoneNumber, country, hashed_password, image, imagePath) ";
    $sql .= "VALUES (";
    $sql .= "'" . db_escape($db, $admin['first_name']) . "',";
    $sql .= "'" . db_escape($db, $admin['last_name']) . "',";
    $sql .= "'" . db_escape($db, $admin['email']) . "',";
    $sql .= "'" . db_escape($db, $admin['phone_number']) . "',";
    $sql .= "'" . db_escape($db, $admin['country']) . "',";
    $sql .= "'" . db_escape($db, $hashed_password) . "',";
    $sql .= "LOAD_FILE('" . $_SERVER['DOCUMENT_ROOT']."/MUJIHotelBooking/".db_escape($db, $admin['avatar'])."'),";
    $sql .= "'" . db_escape($db, $admin['avatar']) . "'";
    $sql .= ")";
    $result = mysqli_query($db, $sql);

    //For INSERT statements, $result is true/false
    if($result) {
      return true;
    } else {
      // INSERT failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }

  //update account to database
  function update_admin($admin) {
    global $db;

    $errors = validate_update($admin);
    if (!empty($errors)) {
      return $errors;
    }

    $sql = "UPDATE members SET ";
    $sql .= "firstName='" . db_escape($db, $admin['first_name']) . "', ";
    $sql .= "lastName='" . db_escape($db, $admin['last_name']) . "', ";
    $sql .= "phoneNumber='" . db_escape($db, $admin['phone_number']) . "', ";
    $sql .= "country='" . db_escape($db, $admin['country']) . "', ";
    $sql .= "image=LOAD_FILE('" . $_SERVER['DOCUMENT_ROOT']."/MUJIHotelBooking/".db_escape($db, $admin['avatar'])."'),";
    $sql .= "imagePath='" . db_escape($db, $admin['avatar']) . "'";
    $sql .= "WHERE memberNumber ='" . db_escape($db, $admin['id']) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);

    // For UPDATE statements, $result is true/false
    if($result) {
      return true;
    } else {
      // UPDATE failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }

  //valiadation functions
  function is_blank($value) {
    return !isset($value) || trim($value) === '';
  }
  function has_length($value, $options) {
    if(isset($options['min']) && !has_length_greater_than($value, $options['min'] - 1)) {
      return false;
    } elseif(isset($options['max']) && !has_length_less_than($value, $options['max'] + 1)) {
      return false;
    } elseif(isset($options['exact']) && !has_length_exactly($value, $options['exact'])) {
      return false;
    } else {
      return true;
    }
  }
  function has_length_greater_than($value, $min) {
    $length = strlen($value);
    return $length > $min;
  }
  function has_length_less_than($value, $max) {
    $length = strlen($value);
    return $length < $max;
  }
  function has_length_exactly($value, $exact) {
    $length = strlen($value);
    return $length == $exact;
  }
  function has_valid_email_format($value) {
    $email_regex = '/\A[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\Z/i';
    return preg_match($email_regex, $value) === 1;
  }
  function has_unique_email($email, $current_id="0") {
    global $db;

    $sql = "SELECT * FROM members ";
    $sql .= "WHERE email='" . db_escape($db, $email) . "' ";
    $sql .= "AND memberNumber != '" . db_escape($db, $current_id) . "'";

    $result = mysqli_query($db, $sql);
    $admin_count = mysqli_num_rows($result);
    mysqli_free_result($result);

    return $admin_count === 0;
  }

  //switch to https
  function require_SSL(){
    if($_SERVER["HTTPS"] != "on"){
      header("Location: https://" . $_SERVER['SERVER_NAME'] . ":8443". $_SERVER['REQUEST_URI']);
      // header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
      exit();
    }
  }

  //insert new reservation to database
  function insert_reserve($reserve) {
    global $db;

    $sql = "INSERT INTO reservation ";
    $sql .= "(memberNumber, bookingDate, checkInDate, checkOutDate, numberOfGuests, numberOfRoomBooked, bedType, roomType, roomNumber, priceEach, bookingComments) ";
    $sql .= "VALUES (";
    $sql .= "'" . db_escape($db, $reserve['memberNumber']) . "',";
    $sql .= "'" . db_escape($db, $reserve['bookingDate']) . "',";
    $sql .= "'" . db_escape($db, $reserve['checkInDate']) . "',";
    $sql .= "'" . db_escape($db, $reserve['checkOutDate']) . "',";
    $sql .= "'" . db_escape($db, $reserve['numberOfGuests']) . "',";
    $sql .= "'" . db_escape($db, $reserve['numberOfRoomBooked']) . "',";
    $sql .= "'" . db_escape($db, $reserve['bedType']) . "',";
    $sql .= "'" . db_escape($db, $reserve['roomType']) . "',";
    $sql .= "'" . db_escape($db, $reserve['roomNumber']) . "',";
    $sql .= "'" . db_escape($db, $reserve['priceEach']) . "',";
    $sql .= "'" . db_escape($db, $reserve['bookingComments']) . "'";
    $sql .= ")";
    $result = mysqli_query($db, $sql);

    // For INSERT statements, $result is true/false
    if($result) {
      return true;
    } else {
      // INSERT failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }

  //insert new comment to comment database
  function insert_comment($comment) {
    global $db;

    if(is_blank($comment['content'])) {
      $errors = "Comment cannot be blank.";
    } elseif (!has_length($comment['content'], array('min' => 10, 'max' => 200))) {
      $errors = "Comment must be between 10 and 200 characters.";
    }
    if (!empty($errors)) {
      return $errors;
    }

    $sql = "INSERT INTO comment ";
    $sql .= "(content, memberNumber, roomType, timePosted) ";
    $sql .= "VALUES (";
    $sql .= "'" . db_escape($db, $comment['content']) . "',";
    $sql .= "'" . db_escape($db, $_SESSION['admin_id']) . "',";
    $sql .= "'" . db_escape($db, $comment['room']) . "',";
    $sql .= "'" . db_escape($db, date("Y-m-d")) . "'";
    $sql .= ")";
    echo $sql;
    $result = mysqli_query($db, $sql);

    // For INSERT statements, $result is true/false
    if($result) {
      return true;
    } else {
      // INSERT failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }

  //update password
  function change_password($admin) {
    global $db;

    $hashed_password = password_hash($admin['new_password'], PASSWORD_BCRYPT);

    $errors = validate_change($admin);
    if (!empty($errors)) {
      return $errors;
    }

    $sql = "UPDATE members SET ";
    $sql .= "hashed_password='" . db_escape($db, $hashed_password) . "' ";

    $result = mysqli_query($db, $sql);

    // For UPDATE statements, $result is true/false
    if($result) {
      return true;
    } else {
      // UPDATE failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }

  }

  //validate password change
  function validate_change($admin, $options=[]) {

    if(is_blank($admin['cur_password'])) {
      $errors[] = "Current password cannot be blank.";
    }else{
      $user = find_admin_by_id($admin['id']);
      if(password_verify($admin['cur_password'], $user['hashed_password'])) {
      }else{
        $errors[] = "Incorrect current password.";
      }
    }

    if(is_blank($admin['new_password'])) {
      $errors[] = "New password cannot be blank.";
    }

    if(is_blank($admin['confirm_password'])) {
      $errors[] = "Confirm password cannot be blank.";
    } elseif ($admin['new_password'] !== $admin['confirm_password']) {
      $errors[] = "New password and confirm password must match.";
    }

    return $errors;
  }

  //delete comment from database
  function delete_comment($id) {
    global $db;

    $sql = "DELETE FROM comment WHERE ";
    $sql .= "id='" . db_escape($db, $id) . "' ";
    
    $result = mysqli_query($db, $sql);
    
    // For DELETE statements, $result is true/false
    if($result) {
      return true;
    } else {
      // DELETE failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }
?>