<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require "includes/database_connect.php";

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$city_name = isset($_GET['city']) ? trim($_GET['city']) : '';

// Resolve city from database
$city = null;
if (!empty($city_name)) {
    $sql_city = "SELECT * FROM cities WHERE LOWER(name) = LOWER(?) OR (LOWER(name) = 'bengaluru' AND LOWER(?) = 'bangalore')";
    $stmt_city = $conn->prepare($sql_city);
    $stmt_city->bind_param("ss", $city_name, $city_name);
    $stmt_city->execute();
    $result_city = $stmt_city->get_result();
    $city = $result_city->fetch_assoc();
    $stmt_city->close();
}

$properties = [];
$where = ["1=1"];
$params = [];
$types = "";

if ($city) {
    $where[] = "city_id = ?";
    $params[] = $city['id'];
    $types .= "i";
}

$gender = isset($_GET['gender']) ? strtolower(trim($_GET['gender'])) : 'all';
if (!empty($gender) && $gender !== 'all') {
    $where[] = "LOWER(gender) = ?";
    $params[] = $gender;
    $types .= "s";
}

$budget = isset($_GET['budget']) ? strtolower(trim($_GET['budget'])) : 'all';
if ($budget === 'max_6000') {
    $where[] = "rent <= 6000";
} elseif ($budget === '6000_8500') {
    $where[] = "rent > 6000 AND rent <= 8500";
} elseif ($budget === 'above_8500') {
    $where[] = "rent > 8500";
}

$sort = isset($_GET['sort']) ? strtolower(trim($_GET['sort'])) : 'none';
$order = "id ASC";
if ($sort === 'highest') {
    $order = "rent DESC";
} elseif ($sort === 'lowest') {
    $order = "rent ASC";
}

$sql_prop = "SELECT * FROM properties WHERE " . implode(" AND ", $where) . " ORDER BY " . $order;
$stmt_prop = $conn->prepare($sql_prop);
if (!empty($params)) {
    $stmt_prop->bind_param($types, ...$params);
}
$stmt_prop->execute();
$result_prop = $stmt_prop->get_result();
while ($row = $result_prop->fetch_assoc()) {
    $properties[] = $row;
}
$stmt_prop->close();

// Fetch interested properties for current user if logged in
$interested_properties_by_user = [];
if ($user_id) {
    $sql_user_interested = "SELECT property_id FROM interested_users_properties WHERE user_id = ?";
    $stmt_user_interested = $conn->prepare($sql_user_interested);
    $stmt_user_interested->bind_param("i", $user_id);
    $stmt_user_interested->execute();
    $res_ui = $stmt_user_interested->get_result();
    while ($row_ui = $res_ui->fetch_assoc()) {
        $interested_properties_by_user[] = $row_ui['property_id'];
    }
    $stmt_user_interested->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Best PG's in <?php echo htmlspecialchars($city_name ?: 'City'); ?> | PG Life</title>

    <?php include "includes/head_links.php"; ?>
    <link href="css/property_list.css" rel="stylesheet" />
</head>

<body>
    <?php include "includes/header.php"; ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item">
                <a href="index.php">Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo htmlspecialchars($city ? $city['name'] : $city_name); ?>
            </li>
        </ol>
    </nav>

    <div class="page-container">
        <div id="react-property-list-root" data-city="<?php echo htmlspecialchars($city ? $city['name'] : $city_name); ?>">
            <div class="filter-bar row justify-content-around">
                <div class="col-auto" data-toggle="modal" data-target="#filter-modal">
                    <img src="img/filter.png" alt="filter" />
                    <span>Filter</span>
                </div>
                <div class="col-auto">
                    <img src="img/desc.png" alt="sort-desc" />
                    <span>Highest rent first</span>
                </div>
                <div class="col-auto">
                    <img src="img/asc.png" alt="sort-asc" />
                    <span>Lowest rent first</span>
                </div>
            </div>

            <?php
            if (count($properties) == 0) {
            ?>
                <div class="no-property-container">
                    <p>No PG to list</p>
                </div>
            <?php
            } else {
                foreach ($properties as $property) {
                    $property_images = glob("img/properties/" . $property['id'] . "/*");
                    $image_path = !empty($property_images) ? $property_images[0] : "img/properties/1/1d4f0757fdb86d5f.jpg";

                    $total_rating = round(($property['rating_clean'] + $property['rating_food'] + $property['rating_safety']) / 3, 1);

                    // Count interested users for this property
                    $sql_count = "SELECT COUNT(*) as count FROM interested_users_properties WHERE property_id = ?";
                    $stmt_count = $conn->prepare($sql_count);
                    $stmt_count->bind_param("i", $property['id']);
                    $stmt_count->execute();
                    $interested_count = $stmt_count->get_result()->fetch_assoc()['count'];
                    $stmt_count->close();

                    $is_interested = in_array($property['id'], $interested_properties_by_user);
            ?>
                    <div class="property-card property-id-<?php echo $property['id']; ?> row">
                        <div class="image-container col-md-4">
                            <img src="<?php echo htmlspecialchars($image_path); ?>" />
                        </div>
                        <div class="content-container col-md-8">
                            <div class="row no-gutters justify-content-between">
                                <div class="star-container" title="<?php echo $total_rating; ?>">
                                    <?php
                                    for ($i = 0; $i < 5; $i++) {
                                        if ($total_rating >= $i + 0.8) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($total_rating >= $i + 0.3) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="interested-container">
                                    <i class="is-interested-image <?php echo $is_interested ? 'fas' : 'far'; ?> fa-heart" property_id="<?php echo $property['id']; ?>"></i>
                                    <div class="interested-text">
                                        <span class="interested-user-count"><?php echo $interested_count; ?></span> interested
                                    </div>
                                </div>
                            </div>
                            <div class="detail-container">
                                <div class="property-name"><?php echo htmlspecialchars($property['name']); ?></div>
                                <div class="property-address"><?php echo htmlspecialchars($property['address']); ?></div>
                                <div class="property-gender">
                                    <?php
                                    if ($property['gender'] == "male") {
                                        echo '<img src="img/male.png" />';
                                    } elseif ($property['gender'] == "female") {
                                        echo '<img src="img/female.png" />';
                                    } else {
                                        echo '<img src="img/unisex.png" />';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row no-gutters">
                                <div class="rent-container col-6">
                                    <div class="rent">Rs <?php echo number_format($property['rent']); ?>/-</div>
                                    <div class="rent-unit">per month</div>
                                </div>
                                <div class="button-container col-6">
                                    <a href="property_detail.php?property_id=<?php echo $property['id']; ?>" class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php
                }
            }
            ?>
        </div>
    </div>

    <?php include "includes/signup_modal.php"; ?>
    <?php include "includes/login_modal.php"; ?>
    <?php include "includes/footer.php"; ?>

    <!-- React Property Listing & Filtering Integration -->
    <script type="text/javascript" src="js/react-components/property_list.bundle.js"></script>
</body>

</html>
