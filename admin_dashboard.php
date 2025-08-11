<?php
session_start();
include 'db.php';

if (isset($_SESSION['login_success'])) {
    echo '<div class="success-message"><p>' . $_SESSION['login_success'] . '</p></div>';
    unset($_SESSION['login_success']);
}
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    
</head>
<body>
    <div class="dashboard-header">
        <h2>👑 Admin Dashboard</h2>
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
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
    <br>
    <div style="text-align: center;">
        <a class="button add-new" href="register.php">+ Add New User</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            serverSide: true,
            ajax: {
                url: 'server_processing.php',
                type: 'POST'
            },
            columns: [
                { data: 'id' },
                { 
                    data: 'image',
                    render: function(data) {
                        return data ? 
                            `<img src="uploads/${data}" width="50" style="border-radius:5px">` : 
                            '<div class="default-avatar"></div>';
                    },
                    orderable: false
                },
                { data: 'name' },
                { data: 'email' },
                { data: 'phone' },
                { 
                    data: 'address', 
                    render: function(data) {
                        return data ? 
                            `${data.street}, ${data.city}, ${data.state}, ${data.country}, ${data.postal_code}` : 
                            'No address';
                    }
                },
                { 
                    data: 'id',
                    render: function(data) {
                        return `<div class="actions">
                            <a class="button" href="edit.php?id=${data}">Edit</a>
                            <a class="button" href="delete.php?id=${data}" onclick="return confirm('Are you sure?')">Delete</a>
                        </div>`;
                    },
                    orderable: false
                }
            ]
        });
    });
    </script>
</body>
</html>
