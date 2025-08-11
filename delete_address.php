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

// Check if address belongs to user
$stmt = $conn->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $address_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // Delete address
    $conn->query("DELETE FROM addresses WHERE id = $address_id");
    $_SESSION['success_message'] = "Address deleted successfully!";
} else {
    $_SESSION['error_message'] = "Address not found or you don't have permission to delete it.";
}

header("Location: manage_addresses.php");
exit();
?>