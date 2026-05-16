<?php
// logout.php – Destroys user session and redirects to login
session_start();
session_unset();
session_destroy();
header('Location: login.php');
exit;
