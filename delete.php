<?php
include 'db.php';
$id = (int)$_GET['id']; // typecast to int for safety

// Remove image if it exists
$result = $conn->query("SELECT image FROM userrs WHERE id = $id");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (!empty($row['image'])) {
        $imagePath = UPLOAD_DIR . $row['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}

// Trigger will handle deleting related addresses
$sql = "DELETE FROM userrs WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    header("Location: admin_dashboard.php");
    exit();
} else {
    echo "Error deleting record: " . $conn->error;
}
?>
