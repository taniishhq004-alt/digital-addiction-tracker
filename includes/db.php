<?php
// includes/db.php
// -------------------------------------------------------
// Database connection — change password if needed
// -------------------------------------------------------
$host   = "localhost";
$dbname = "digital_addiction_db";
$user   = "root";
$pass   = "";          // default XAMPP password is empty

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("<div style='color:red;font-family:sans-serif;padding:20px'>
         ❌ Database connection failed: " . $conn->connect_error . "
         <br>Make sure XAMPP is running and you have imported database.sql
         </div>");
}
$conn->set_charset("utf8");
?>
