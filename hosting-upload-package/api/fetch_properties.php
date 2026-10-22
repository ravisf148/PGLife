<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require "../includes/database_connect.php";

header('Content-Type: application/json');

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$city_param = isset($_GET['city']) ? trim($_GET['city']) : '';
$gender_param = isset($_GET['gender']) ? strtolower(trim($_GET['gender'])) : 'all';
$budget_param = isset($_GET['budget']) ? strtolower(trim($_GET['budget'])) : 'all';
$sort_param = isset($_GET['sort']) ? strtolower(trim($_GET['sort'])) : 'none';

// Fetch all cities for metadata / dropdowns
$cities_map = [];
$res_cities = $conn->query("SELECT id, name FROM cities");
if ($res_cities) {
    while ($c = $res_cities->fetch_assoc()) {
        $cities_map[$c['id']] = $c['name'];
    }
}

// Resolve city_id if specified
$city_id = null;
if (!empty($city_param) && $city_param !== 'all') {
    if (is_numeric($city_param)) {
        $city_id = intval($city_param);
    } else {
        $sql_city = "SELECT id, name FROM cities WHERE LOWER(name) = LOWER(?) OR (LOWER(name) = 'bengaluru' AND LOWER(?) = 'bangalore')";
        $stmt_c = $conn->prepare($sql_city);
        $stmt_c->bind_param("ss", $city_param, $city_param);
        $stmt_c->execute();
        $city_row = $stmt_c->get_result()->fetch_assoc();
        if ($city_row) {
            $city_id = $city_row['id'];
        }
        $stmt_c->close();
    }
}

// Build query
$where_clauses = ["1=1"];
$params = [];
$types = "";

if ($city_id !== null) {
    $where_clauses[] = "p.city_id = ?";
    $params[] = $city_id;
    $types .= "i";
}

if (!empty($gender_param) && $gender_param !== 'all' && $gender_param !== 'no filter') {
    $where_clauses[] = "LOWER(p.gender) = ?";
    $params[] = $gender_param;
    $types .= "s";
}

if (!empty($budget_param) && $budget_param !== 'all' && $budget_param !== 'no filter') {
    if ($budget_param === 'max_6000' || $budget_param === 'under_6000') {
        $where_clauses[] = "p.rent <= 6000";
    } elseif ($budget_param === '6000_8500' || $budget_param === '6000-8500') {
        $where_clauses[] = "p.rent > 6000 AND p.rent <= 8500";
    } elseif ($budget_param === 'above_8500') {
        $where_clauses[] = "p.rent > 8500";
    }
}

$order_by = "p.id ASC";
if ($sort_param === 'highest' || $sort_param === 'desc') {
    $order_by = "p.rent DESC";
} elseif ($sort_param === 'lowest' || $sort_param === 'asc') {
    $order_by = "p.rent ASC";
}

$sql = "SELECT p.*, c.name as city_name FROM properties p INNER JOIN cities c ON p.city_id = c.id WHERE " . implode(" AND ", $where_clauses) . " ORDER BY " . $order_by;

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$properties = [];
while ($row = $result->fetch_assoc()) {
    $prop_id = $row['id'];

    // Resolve Image
    $images = glob("../img/properties/" . $prop_id . "/*");
    if (!empty($images)) {
        // Strip leading ../ for client usage
        $first_img = preg_replace('/^\.\.\//', '', $images[0]);
    } else {
        $first_img = "img/properties/1/1d4f0757fdb86d5f.jpg";
    }

    // Rating
    $total_rating = round(($row['rating_clean'] + $row['rating_food'] + $row['rating_safety']) / 3, 1);

    // Interested count
    $stmt_cnt = $conn->prepare("SELECT COUNT(*) as count FROM interested_users_properties WHERE property_id = ?");
    $stmt_cnt->bind_param("i", $prop_id);
    $stmt_cnt->execute();
    $cnt_row = $stmt_cnt->get_result()->fetch_assoc();
    $interested_count = $cnt_row ? intval($cnt_row['count']) : 0;
    $stmt_cnt->close();

    // Check if user is interested
    $is_interested = false;
    if ($user_id) {
        $stmt_ui = $conn->prepare("SELECT id FROM interested_users_properties WHERE user_id = ? AND property_id = ?");
        $stmt_ui->bind_param("ii", $user_id, $prop_id);
        $stmt_ui->execute();
        if ($stmt_ui->get_result()->num_rows > 0) {
            $is_interested = true;
        }
        $stmt_ui->close();
    }

    $properties[] = [
        "id" => intval($row['id']),
        "city_id" => intval($row['city_id']),
        "city_name" => $row['city_name'],
        "name" => $row['name'],
        "address" => $row['address'],
        "description" => $row['description'],
        "gender" => $row['gender'],
        "rent" => intval($row['rent']),
        "total_rating" => $total_rating,
        "image" => $first_img,
        "interested_count" => $interested_count,
        "is_interested" => $is_interested
    ];
}

$stmt->close();
$conn->close();

echo json_encode([
    "success" => true,
    "city" => $city_param,
    "gender" => $gender_param,
    "budget" => $budget_param,
    "sort" => $sort_param,
    "count" => count($properties),
    "properties" => $properties
]);
?>
