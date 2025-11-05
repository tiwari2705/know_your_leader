<?php
require_once 'config/db.php';
start_secure_session();

$_SESSION = array();
session_destroy();

header("location: login.php");
exit;
?>