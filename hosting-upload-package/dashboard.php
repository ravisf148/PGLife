<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require "includes/database_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user profile
$sql_user = "SELECT full_name, email, phone, college_name FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

if (!$user) {
    echo "User not found!";
    exit;
}

// Fetch interested properties
$sql_interested = "SELECT p.* FROM properties p INNER JOIN interested_users_properties iup ON p.id = iup.property_id WHERE iup.user_id = ?";
$stmt_int = $conn->prepare($sql_interested);
$stmt_int->bind_param("i", $user_id);
$stmt_int->execute();
$res_int = $stmt_int->get_result();
$interested_properties = [];
while ($row_int = $res_int->fetch_assoc()) {
    $interested_properties[] = $row_int;
}
$stmt_int->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | PG Life</title>

    <?php include "includes/head_links.php"; ?>
    <link href="css/dashboard.css" rel="stylesheet" />
</head>

<body>
    <?php include "includes/header.php"; ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item">
                <a href="index.php">Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Dashboard
            </li>
        </ol>
    </nav>

    <div class="my-profile page-container">
        <h1>My Profile</h1>
        <div class="row">
            <div class="col-md-3 profile-img-container">
                <i class="fas fa-user profile-img"></i>
            </div>
            <div class="col-md-9">
                <div class="row no-gutters justify-content-between align-items-end">
                    <div class="profile">
                        <div class="name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                        <div class="email"><?php echo htmlspecialchars($user['email']); ?></div>
                        <div class="phone"><?php echo htmlspecialchars($user['phone']); ?></div>
                        <div class="college"><?php echo htmlspecialchars($user['college_name']); ?></div>
                    </div>
                    <div class="edit">
                        <div class="edit-profile">Edit Profile</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="my-interested-properties">
        <div class="page-container">
            <h1>My Interested Properties</h1>

            <?php
            if (count($interested_properties) == 0) {
            ?>
                <div class="no-property-container text-center py-4">
                    <p class="text-muted">No interested properties yet.</p>
                </div>
            <?php
            } else {
                foreach ($interested_properties as $property) {
                    $property_images = glob("img/properties/" . $property['id'] . "/*");
                    $image_path = !empty($property_images) ? $property_images[0] : "img/properties/1/1d4f0757fdb86d5f.jpg";

                    $total_rating = round(($property['rating_clean'] + $property['rating_food'] + $property['rating_safety']) / 3, 1);
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
                                    <i class="is-interested-image fas fa-heart" property_id="<?php echo $property['id']; ?>"></i>
                                </div>
                            </div>
                            <div class="detail-container">
                                <div class="property-name"><?php echo htmlspecialchars($property['name']); ?></div>
                                <div class="property-address"><?php echo htmlspecialchars($property['address']); ?></div>
                                <div class="property-gender">
                                    <?php
                                    if ($property['gender'] == "male") {
                                        echo '<img src="img/male.png">';
                                    } elseif ($property['gender'] == "female") {
                                        echo '<img src="img/female.png">';
                                    } else {
                                        echo '<img src="img/unisex.png">';
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

    <?php include "includes/footer.php"; ?>
</body>

</html>
