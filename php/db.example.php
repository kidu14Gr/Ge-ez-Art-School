<?php
// Database connection template/example
// Copy this file to php/db.php and fill in your local credentials.

// Hostname for the database server
define('DB_HOST', '');

// Database user
define('DB_USER', '');

// Database password
define('DB_PASS', '');

// Database name
define('DB_NAME', '');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('Database connection error: ' . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}
?>
