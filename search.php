<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>🔍 Search Results</h2>    
<div class="table-container">
    <!-- Search form -->
    <form method="GET" action="search.php" class="search-form">
        <input type="text" name="query" value="<?= htmlspecialchars($_GET['query'] ?? '') ?>" placeholder="Search by name or email..." class="search-input">
        <div style="text-align: center; margin-top: 15px;">
            <a href="search.php" class="button search-button">Search</a>
            <a href="read.php" class="button">Clear Search</a>
        </div>
    </form>
    <br>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Image</th><th>Name</th><th>Email</th><th>Phone</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = isset($_GET['query']) ? trim($_GET['query']) : '';
            $sql = "SELECT * FROM userrs";
            
            if (!empty($query)) {
                $searchTerm = $conn->real_escape_string($query);
                $sql .= " WHERE name LIKE '%$searchTerm%' OR email LIKE '%$searchTerm%'";
            }
            
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
                        <div class="actions">
                            <a class="button" href="edit.php?id=<?= $row['id'] ?>">Edit</a>
                            <a class="button" href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="6" class="no-data">No users found<?= !empty($query) ? ' matching your search' : '' ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<br>
<div style="text-align: center;">
    <a class="button add-new" href="create.php">+ Add New User</a>
</div>

<script>
    // Create floating particles (same as read.php)
    document.addEventListener('DOMContentLoaded', function() {
        const particleCount = 30;
        const body = document.body;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');
            
            const size = Math.random() * 4 + 2;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            
            particle.style.left = `${Math.random() * 100}vw`;
            particle.style.top = `${Math.random() * 100}vh`;
            
            const duration = Math.random() * 20 + 10;
            particle.style.animation = `float ${duration}s linear infinite`;
            
            body.appendChild(particle);
        }
    });
</script>
</body>
</html>