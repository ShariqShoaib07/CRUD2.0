<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include 'db.php';

try {
    // Get DataTables parameters
    $draw = (int)($_POST['draw'] ?? 1);
    $start = (int)($_POST['start'] ?? 0);
    $length = (int)($_POST['length'] ?? 10);
    $search = $_POST['search']['value'] ?? '';

    // Column mapping (including address for ordering if needed)
    $columns = ['u.id', 'u.image', 'u.name', 'u.email', 'u.phone', 'a.street'];
    $orderColumn = $columns[$_POST['order'][0]['column'] ?? 0] ?? 'u.id';
    $orderDir = ($_POST['order'][0]['dir'] ?? 'asc') === 'asc' ? 'ASC' : 'DESC';

    // Base query with join
    $query = "SELECT u.id, u.image, u.name, u.email, u.phone,
                     a.street, a.city, a.state, a.postal_code, a.country
              FROM userrs u
              LEFT JOIN addresses a ON u.id = a.user_id";
    $where = '';
    $params = [];

    // Search filter
    if (!empty($search)) {
        $where = " WHERE u.name LIKE ? 
                   OR u.email LIKE ? 
                   OR u.phone LIKE ? 
                   OR a.street LIKE ? 
                   OR a.city LIKE ? 
                   OR a.state LIKE ? 
                   OR a.country LIKE ? 
                   OR a.postal_code LIKE ?";
        $params = array_fill(0, 8, "%$search%");
    }

    // Count total records
    $totalRecords = $conn->query("SELECT COUNT(*) FROM userrs")->fetch_row()[0];

    // Count filtered records
    $filteredQuery = "SELECT COUNT(*) 
                      FROM userrs u 
                      LEFT JOIN addresses a ON u.id = a.user_id
                      $where";
    $stmt = $conn->prepare($filteredQuery);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $filteredRecords = $stmt->get_result()->fetch_row()[0];

    // Data query with ordering and limit
    $dataQuery = "$query $where ORDER BY $orderColumn $orderDir LIMIT ?, ?";
    $params[] = $start;
    $params[] = $length;

    $stmt = $conn->prepare($dataQuery);
    $types = (!empty($search) ? str_repeat('s', 8) : '') . 'ii';
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    // Build response data
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => (int)$row['id'],
            'image' => $row['image'],
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'address' => $row['street'] ? [
                'street' => $row['street'],
                'city' => $row['city'],
                'state' => $row['state'],
                'postal_code' => $row['postal_code'],
                'country' => $row['country']
            ] : null
        ];
    }

    // Return JSON
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => (int)$totalRecords,
        "recordsFiltered" => (int)$filteredRecords,
        "data" => $data
    ]);

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode([
        "draw" => $_POST['draw'] ?? 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "An error occurred"
    ]);
}
