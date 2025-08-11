<?php
session_start();
include 'db.php';

// Constants for file uploads
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    exit("Invalid request method");
}

// Initialize variables and error array
$errors = [];
$name = $email = $phone = '';
$street = $city = $state = $postal_code = $country = '';
$imageName = null;

// Validate and sanitize inputs
try {
    // Validate required fields
    if (empty($_POST['name'])) {
        $errors[] = "Name field is required";
    } else {
        $name = trim($_POST['name']);
    }

    if (empty($_POST['email'])) {
        $errors[] = "Email field is required";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        $email = trim($_POST['email']);
    }

    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : '';

    // Address validation
    if (empty($_POST['street'])) {
        $errors[] = "Street address is required";
    } else {
        $street = trim($_POST['street']);
    }

    if (empty($_POST['city'])) {
        $errors[] = "City is required";
    } else {
        $city = trim($_POST['city']);
    }

    if (empty($_POST['state'])) {
        $errors[] = "State/Province is required";
    } else {
        $state = trim($_POST['state']);
    }

    if (empty($_POST['postal_code'])) {
        $errors[] = "Postal code is required";
    } else {
        $postal_code = trim($_POST['postal_code']);
    }

    if (empty($_POST['country'])) {
        $errors[] = "Country is required";
    } else {
        $country = trim($_POST['country']);
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $errors[] = "Email '$email' is already registered";
    }
    $stmt->close();

    // Check if phone exists (if provided)
    if (!empty($phone)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = "Phone number '$phone' is already in use";
        }
        $stmt->close();
    }

    // Handle image upload if provided
    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error: " . $file['error'];
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = "File is too large. Maximum size is " . (MAX_FILE_SIZE / 1024 / 1024) . "MB";
        } elseif (!in_array($file['type'], ALLOWED_TYPES)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and GIF are allowed";
        } else {
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $imageName = uniqid() . '.' . $ext;
            $destination = UPLOAD_DIR . $imageName;
            
            // Create upload directory if it doesn't exist
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                $errors[] = "Failed to save uploaded file";
            }
        }
    }

    // If any errors, redirect back with error messages
    if (!empty($errors)) {
        $_SESSION['form_values'] = [
            'name' => htmlspecialchars($name),
            'email' => htmlspecialchars($email),
            'phone' => htmlspecialchars($phone),
            'street' => htmlspecialchars($street),
            'city' => htmlspecialchars($city),
            'state' => htmlspecialchars($state),
            'postal_code' => htmlspecialchars($postal_code),
            'country' => htmlspecialchars($country)
        ];
        $_SESSION['errors'] = $errors;
        header("Location: create.php");
        exit();
    }

    // Insert into users table
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, image) VALUES (?, ?, ?, ?)");
    $imageParam = !empty($imageName) ? $imageName : null;
    $stmt->bind_param("ssss", $name, $email, $phone, $imageParam);

    if ($stmt->execute()) {
        // Get the new user ID
        $user_id = $conn->insert_id;

        // Insert into addresses table
        $stmt_addr = $conn->prepare("INSERT INTO addresses (user_id, street, city, state, postal_code, country) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_addr->bind_param("isssss", $user_id, $street, $city, $state, $postal_code, $country);
        $stmt_addr->execute();
        $stmt_addr->close();

        // Clear stored form values
        if (isset($_SESSION['form_values'])) {
            unset($_SESSION['form_values']);
        }
        $_SESSION['success_message'] = "User and address created successfully";
        header("Location: read.php");
        exit();
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }
} catch (Exception $e) {
    // Clean up uploaded file if there was an error after upload
    if (!empty($imageName) && file_exists(UPLOAD_DIR . $imageName)) {
        unlink(UPLOAD_DIR . $imageName);
    }
    
    $errors[] = $e->getMessage();
    $_SESSION['errors'] = $errors;
    header("Location: create.php");
    exit();
}
?>
