<?php
require_once('initialize.php');

//logout the user
log_out_admin();

//return to login page
redirect_to(url_for('login.php'));

?>
