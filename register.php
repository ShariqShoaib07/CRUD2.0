<?php
session_start();
include 'db.php';

$errors = [];
$username = '';
$email = '';
$name = ''; // Full name

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']); // Full name
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Address fields
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);

    // Validation for full name
    if (empty($name)) {
        $errors[] = "Full name is required";
    }

    // Validation for username
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters";
    }

    // Validation for email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Validation for password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    // Confirm password
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Basic address validation
    if (empty($street) || empty($city) || empty($state) || empty($postal_code) || empty($country)) {
        $errors[] = "All address fields are required";
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM userrs WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Username or email already exists";
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user (now including full name)
        $stmt = $conn->prepare("INSERT INTO userrs (username, name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $name, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id; // Get newly inserted user ID

            // Insert address
            $stmt_address = $conn->prepare("INSERT INTO addresses (user_id, street, city, state, postal_code, country) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_address->bind_param("isssss", $user_id, $street, $city, $state, $postal_code, $country);

            if ($stmt_address->execute()) {
                $_SESSION['registration_success'] = true;
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "User created, but address insertion failed: " . $stmt_address->error;
            }

        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>📝 Register</h2>
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['registration_success'])): ?>
            <div class="success-message">
                <p>Registration successful! Please login.</p>
            </div>
            <?php unset($_SESSION['registration_success']); ?>
        <?php endif; ?>
        
        <form method="POST" action="register.php" enctype="multipart/form-data">
            <label>Profile Image:</label><br>
            <input type="file" name="image" accept="image/*"><br><br>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
            </div>

            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <!-- Address Fields -->
            <div class="form-group">
                <label for="street">Street Address:</label>
                <input type="text" id="street" name="street" required>
            </div>
            <div class="form-group">
                <label for="city">City:</label>
                <input type="text" id="city" name="city" required>
            </div>
            <div class="form-group">
                <label for="state">State/Province:</label>
                <input type="text" id="state" name="state" required>
            </div>
            <div class="form-group">
                <label for="postal_code">Postal Code:</label>
                <input type="text" id="postal_code" name="postal_code" required>
            </div>
            <div class="form-group">
                <label for="country">Country:</label>
                <input type="text" id="country" name="country" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="button">Register</button>
                <a href="login.php" class="button">Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>
