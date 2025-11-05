<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); 
define('DB_PASSWORD', '');     
define('DB_NAME', 'know_your_leader_db');



$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("ERROR: Database connection failed. " . $conn->connect_error);
}

function start_secure_session() {
    session_name('KYL_SESSION');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
        ini_set('session.cookie_secure', 1);
    }

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Checks if the current user is an Admin.
 * @return bool
 */
function is_admin() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $_SESSION["user_type"] === 'admin';
}

/**
 * Checks if the current user is a Citizen.
 * @return bool
 */
function is_citizen() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $_SESSION["user_type"] === 'citizen';
}
?>