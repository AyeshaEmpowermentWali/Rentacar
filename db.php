<?php
// Database configuration
$host = 'localhost';
$dbname = 'dbd1t8vne7scqb';
$username = 'ugrj543f7lree';
$password = 'cgmq43woifko';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set PDO attributes
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to generate booking reference
function generateBookingReference() {
    return 'RC' . strtoupper(substr(uniqid(), -8));
}

// Function to calculate days between dates
function calculateDays($pickup_date, $return_date) {
    $pickup = new DateTime($pickup_date);
    $return = new DateTime($return_date);
    $interval = $pickup->diff($return);
    return $interval->days;
}
?>
