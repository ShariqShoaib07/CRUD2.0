<?php
$errors = json_decode($_GET['errors'] ?? '[]', true);
$redirect = $_GET['redirect'] ?? 'read.php';
$error_type = $_GET['type'] ?? 'Validation Error';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($error_type) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="table-container">
        <h2>⚠️ <?= htmlspecialchars($error_type) ?></h2>
        <div class="error-container">
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p>• <?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
                <p class="text-muted">Please correct these issues and try again.</p>
            </div>
            <div class="actions">
                <a href="<?= htmlspecialchars($redirect) ?>" class="button">Go Back</a>
                <a href="read.php" class="button" style="background: linear-gradient(135deg, #00c6ff, #0072ff)">Return to List</a>
            </div>
        </div>
    </div>
</body>
</html>