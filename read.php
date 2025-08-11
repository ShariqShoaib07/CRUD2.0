<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Users List</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .default-avatar {
            width: 50px;
            height: 50px;
            background: #555;
            border-radius: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
<h2>📋 User List</h2>    
<div class="table-container">
    <table id="usersTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
    </table>
</div>
<br>
<div style="text-align: center;">
    <a class="button add-new" href="create.php">+ Add New User</a>
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