<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$address_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch address
$stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $address_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$address = $result->fetch_assoc();

if (!$address) {
    header("Location: manage_addresses.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);
    $is_primary = isset($_POST['is_primary']) ? 1 : 0;

    // If this is being set as primary, unset any existing primary address
    if ($is_primary) {
        $conn->query("UPDATE addresses SET is_primary = 0 WHERE user_id = $user_id");
    }

    // Update address
    $stmt = $conn->prepare("UPDATE addresses SET street = ?, city = ?, state = ?, postal_code = ?, country = ?, is_primary = ? WHERE id = ?");
    $stmt->bind_param("sssssii", $street, $city, $state, $postal_code, $country, $is_primary, $address_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Address updated successfully!";
        header("Location: manage_addresses.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Failed to update address. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Address</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-header">
        <h2>✏️ Edit Address</h2>
        <div class="user-info">
            <a href="manage_addresses.php" class="button">Back to Addresses</a>
        </div>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message">
            <p><?= $_SESSION['error_message'] ?></p>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="table-container">
        <form method="POST" action="edit_address.php?id=<?= $address_id ?>">
            <div class="form-group">
                <label for="street">Street Address:</label>
                <input type="text" id="street" name="street" value="<?= htmlspecialchars($address['street']) ?>">
            </div>
            <div class="form-group">
                <label for="city">City:</label>
                <input type="text" id="city" name="city" value="<?= htmlspecialchars($address['city']) ?>">
            </div>
            <div class="form-group">
                <label for="state">State/Province:</label>
                <input type="text" id="state" name="state" value="<?= htmlspecialchars($address['state']) ?>">
            </div>
            <div class="form-group">
                <label for="postal_code">Postal Code:</label>
                <input type="text" id="postal_code" name="postal_code" value="<?= htmlspecialchars($address['postal_code']) ?>">
            </div>
            <div class="form-group">
                <label for="country">Country:</label>
                <input type="text" id="country" name="country" value="<?= htmlspecialchars($address['country']) ?>">
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_primary" value="1" <?= $address['is_primary'] ? 'checked' : '' ?>> Set as primary address
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Update Address</button>
            </div>
        </form>
    </div>
</body>
</html>