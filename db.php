<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'crud_app';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper encoding
$conn->set_charset("utf8mb4");

// Create addresses table if it doesn't exist
$conn->query("
    CREATE TABLE IF NOT EXISTS addresses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        street VARCHAR(255) NOT NULL,
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100) NOT NULL,
        postal_code VARCHAR(20) NOT NULL,
        country VARCHAR(100) NOT NULL,
        FOREIGN KEY (user_id) REFERENCES userrs(id) ON DELETE CASCADE
    )
");

// Image upload configuration
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
?>