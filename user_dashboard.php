<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<body>
    <div class="dashboard-header">
        <h2>👤 User Dashboard</h2>
        <div class="user-info">
            
            <a href="logout.php" class="button">Logout</a>
        </div>
    </div>
    
    <div class="table-container">
        <table id="usersTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch users with addresses
                $sql = "SELECT u.id, u.name, u.email, u.phone, u.image, 
                               a.street, a.city, a.state, a.country, a.postal_code
                        FROM userrs u
                        LEFT JOIN addresses a ON u.id = a.user_id";
                $result = $conn->query($sql);
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td>
                            <?php if (!empty($row['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" width="50" style="border-radius: 5px;">
                            <?php else: ?>
                                <div style="width:50px; height:50px; background:#555; border-radius:5px;"></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td>
                            <?php
                            if (!empty($row['street'])) {
                                echo htmlspecialchars($row['street']) . ', ' .
                                     htmlspecialchars($row['city']) . ', ' .
                                     htmlspecialchars($row['state']) . ', ' .
                                     htmlspecialchars($row['country']) . ', ' .
                                     htmlspecialchars($row['postal_code']);
                            } else {
                                echo 'No address';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            responsive: true,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search users..."
            }
        });
    });
    </script>
</body>
</html>
