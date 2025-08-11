<?php
include 'db.php';

// Define constants for file uploads
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('UPLOAD_DIR', 'uploads/');
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

$id = (int)$_POST['id'];
$name = $conn->real_escape_string($_POST['name']);
$email = $conn->real_escape_string($_POST['email']);
$phone = $conn->real_escape_string($_POST['phone']);
$imageName = null;
$removeImage = isset($_POST['remove_image']);

// Start session for form value preservation
session_start();
$_SESSION['form_values'] = [
    'name' => htmlspecialchars($_POST['name']),
    'email' => htmlspecialchars($_POST['email']),
    'phone' => htmlspecialchars($_POST['phone']),
    'street' => htmlspecialchars($_POST['street']),
    'city' => htmlspecialchars($_POST['city']),
    'state' => htmlspecialchars($_POST['state']),
    'postal_code' => htmlspecialchars($_POST['postal_code']),
    'country' => htmlspecialchars($_POST['country'])
];

if ($removeImage) {
    // Delete old image if it exists
    $oldImage = $conn->query("SELECT image FROM userrs WHERE id = $id")->fetch_assoc()['image'];
    if ($oldImage && file_exists(UPLOAD_DIR . $oldImage)) {
        unlink(UPLOAD_DIR . $oldImage);
    }
    $imageUpdate = "image = NULL";
} elseif (!empty($_FILES['image']['name'])) {
    // Handle new image upload
    $file = $_FILES['image'];
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        header("Location: error-display.php?error=File upload error&redirect=edit.php?id=$id");
        exit();
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        header("Location: error-display.php?error=File is too large (max 2MB)&redirect=edit.php?id=$id");
        exit();
    }
    
    if (!in_array($file['type'], ALLOWED_TYPES)) {
        header("Location: error-display.php?error=Only JPG, PNG, and GIF files are allowed&redirect=edit.php?id=$id");
        exit();
    }
    
    // Delete old image if it exists
    $oldImage = $conn->query("SELECT image FROM userrs WHERE id = $id")->fetch_assoc()['image'];
    if ($oldImage && file_exists(UPLOAD_DIR . $oldImage)) {
        unlink(UPLOAD_DIR . $oldImage);
    }
    
    // Save new image
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $imageName = uniqid() . '.' . $ext;
    $destination = UPLOAD_DIR . $imageName;
    
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        header("Location: error-display.php?error=Failed to save uploaded file&redirect=edit.php?id=$id");
        exit();
    }
    $imageUpdate = "image = '$imageName'";
} else {
    // Keep existing image
    $imageUpdate = "image = image";
}

// Check if email already exists for another user
$check_email = $conn->query("SELECT id FROM userrs WHERE email = '$email' AND id != $id");
if ($check_email->num_rows > 0) {
    header("Location: error-display.php?error=Email already exists&redirect=edit.php?id=$id");
    exit();
}

// Check if phone already exists for another user (if phone is provided)
if (!empty($phone)) {
    $check_phone = $conn->query("SELECT id FROM userrs WHERE phone = '$phone' AND id != $id");
    if ($check_phone->num_rows > 0) {
        header("Location: error-display.php?error=Phone number already exists&redirect=edit.php?id=$id");
        exit();
    }
}

// Update user table
$sql = "UPDATE userrs SET name='$name', email='$email', phone='$phone', $imageUpdate WHERE id=$id";
if ($conn->query($sql) === TRUE) {

    // ✅ After updating user info, update address table
    $street = $conn->real_escape_string($_POST['street']);
    $city = $conn->real_escape_string($_POST['city']);
    $state = $conn->real_escape_string($_POST['state']);
    $postal_code = $conn->real_escape_string($_POST['postal_code']);
    $country = $conn->real_escape_string($_POST['country']);

    // Check if address exists
    $addressExists = $conn->query("SELECT id FROM addresses WHERE user_id = $id")->num_rows > 0;

    if ($addressExists) {
        $conn->query("UPDATE addresses SET 
            street = '$street', 
            city = '$city', 
            state = '$state', 
            postal_code = '$postal_code', 
            country = '$country' 
            WHERE user_id = $id");
    } else {
        $conn->query("INSERT INTO addresses (user_id, street, city, state, postal_code, country) 
            VALUES ($id, '$street', '$city', '$state', '$postal_code', '$country')");
    }

    // Clear stored form values on success
    unset($_SESSION['form_values']);
    header("Location: admin_dashboard.php");
    exit();
} else {
    header("Location: error-display.php?error=" . urlencode($conn->error) . "&redirect=edit.php?id=$id");
    exit();
}
?>
