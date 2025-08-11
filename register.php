<?php
session_start();
include 'db.php';

$errors = [];
$username = '';
$email = '';
$name = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phone = trim($_POST['phone']);

    // Validation
    if (empty($name)) $errors[] = "Full name is required";
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM userrs WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Username or email already exists";
    }

    // Image upload handling
    $image_name = null;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_name = time() . "_" . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_name;

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF images are allowed.";
        } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $errors[] = "Failed to upload image.";
        }
    }

    // If no errors, insert user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO userrs (username, name, email, password, phone, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $name, $email, $hashed_password, $phone, $image_name);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;

            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['is_admin'] = false;

            $_SESSION['registration_success'] = true;
            $_SESSION['success_message'] = "Registration successful! You can now add addresses to your profile.";
            header("Location: manage_addresses.php");
            exit();
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
                <label for="username">Username: <span class="required">*</span></label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
            </div>

            <div class="form-group">
                <label for="name">Full Name: <span class="required">*</span></label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email: <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password: <span class="required">*</span></label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password: <span class="required">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone">
            </div>

            <div class="form-actions">
                <button type="submit" class="button">Register</button>
                <a href="login.php" class="button">Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>