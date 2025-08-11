<?php 
session_start();
include 'db.php';

// Get saved form values from session (if redirected back after error)
$form_values = $_SESSION['form_values'] ?? [
    'name' => '',
    'email' => '',
    'phone' => '',
    'street' => '',
    'city' => '',
    'state' => '',
    'postal_code' => '',
    'country' => ''
];

// Get any error messages
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>➕ Add New User</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="table-container">
        <form method="POST" action="store.php" enctype="multipart/form-data">
            <label>Profile Image:</label><br>
            <input type="file" name="image" accept="image/*"><br><br>

            <label>Name:</label><br>
            <input type="text" name="name" value="<?= htmlspecialchars($form_values['name']) ?>" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($form_values['email']) ?>" required><br><br>

            <label>Phone:</label><br>
            <input type="text" name="phone" value="<?= htmlspecialchars($form_values['phone']) ?>"><br><br>

            <!-- Address Fields -->
            <label>Street Address:</label><br>
            <input type="text" name="street" value="<?= htmlspecialchars($form_values['street']) ?>" required><br><br>

            <label>City:</label><br>
            <input type="text" name="city" value="<?= htmlspecialchars($form_values['city']) ?>" required><br><br>

            <label>State/Province:</label><br>
            <input type="text" name="state" value="<?= htmlspecialchars($form_values['state']) ?>" required><br><br>

            <label>Postal Code:</label><br>
            <input type="text" name="postal_code" value="<?= htmlspecialchars($form_values['postal_code']) ?>" required><br><br>

            <label>Country:</label><br>
            <input type="text" name="country" value="<?= htmlspecialchars($form_values['country']) ?>" required><br><br>

            <div class="form-actions">
                <button onclick="window.location.href='admin_dashboard.php'" class="button" style="background: linear-gradient(135deg, #00c6ff, #0072ff)" type="button">Go Back</button>
                <button class="button" type="submit">Save</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php 
// Clear form values after displaying them
if (isset($_SESSION['form_values'])) {
    unset($_SESSION['form_values']);
}
?>
