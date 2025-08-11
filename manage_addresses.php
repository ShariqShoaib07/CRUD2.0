<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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

    // Insert new address
    $stmt = $conn->prepare("INSERT INTO addresses (user_id, street, city, state, postal_code, country, is_primary) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssi", $user_id, $street, $city, $state, $postal_code, $country, $is_primary);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Address added successfully!";
        header("Location: manage_addresses.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Failed to add address. Please try again.";
    }
}

// Get user's addresses
$addresses = $conn->query("SELECT * FROM addresses WHERE user_id = $user_id ORDER BY is_primary DESC, id ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Addresses</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-header">
        <h2>🏠 Manage Addresses</h2>
        <div class="user-info">
            <a href="user_dashboard.php" class="button">Dashboard</a>
            <a href="logout.php" class="button">Logout</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            <p><?= $_SESSION['success_message'] ?></p>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message">
            <p><?= $_SESSION['error_message'] ?></p>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="table-container">
        <h3>Add New Address</h3>
        <form method="POST" action="manage_addresses.php">
            <div class="form-group">
                <label for="street">Street Address:</label>
                <input type="text" id="street" name="street">
            </div>
            <div class="form-group">
                <label for="city">City:</label>
                <input type="text" id="city" name="city">
            </div>
            <div class="form-group">
                <label for="state">State/Province:</label>
                <input type="text" id="state" name="state">
            </div>
            <div class="form-group">
                <label for="postal_code">Postal Code:</label>
                <input type="text" id="postal_code" name="postal_code">
            </div>
            <div class="form-group">
                <label for="country">Country:</label>
                <input type="text" id="country" name="country">
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_primary" value="1"> Set as primary address
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Add Address</button>
            </div>
        </form>

        <h3>Your Addresses</h3>
        <?php if ($addresses->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Address</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($address = $addresses->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php 
                                echo htmlspecialchars($address['street']) . ', ' . 
                                     htmlspecialchars($address['city']) . ', ' . 
                                     htmlspecialchars($address['state']) . ', ' . 
                                     htmlspecialchars($address['country']) . ', ' . 
                                     htmlspecialchars($address['postal_code']);
                                ?>
                            </td>
                            <td><?= $address['is_primary'] ? 'Primary' : 'Secondary' ?></td>
                            <td>
                                <div class="actions">
                                    <a href="edit_address.php?id=<?= $address['id'] ?>" class="button">Edit</a>
                                    <a href="delete_address.php?id=<?= $address['id'] ?>" class="button" onclick="return confirm('Are you sure?')">Delete</a>
                                    <?php if (!$address['is_primary']): ?>
                                        <a href="set_primary.php?id=<?= $address['id'] ?>" class="button">Set as Primary</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No addresses found. Please add your first address.</p>
        <?php endif; ?>
    </div>
</body>
</html>