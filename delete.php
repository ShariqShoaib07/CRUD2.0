<?php
include 'db.php';
$id = $_GET['id'];

$result = $conn->query("SELECT image FROM userrs WHERE id = $id");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (!empty($row['image'])) {
        $imagePath = UPLOAD_DIR . $row['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}

$sql = "DELETE FROM userrs WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    header("Location: admin_dashboard.php");
} else {
    echo "Error deleting record: " . $conn->error;
}
?>
