<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require "includes/database_connect.php";

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : 0;

$sql_prop = "SELECT * FROM properties WHERE id = ?";
$stmt_prop = $conn->prepare($sql_prop);
$stmt_prop->bind_param("i", $property_id);
$stmt_prop->execute();
$result_prop = $stmt_prop->get_result();
$property = $result_prop->fetch_assoc();
$stmt_prop->close();

if (!$property) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Property Not Found | PG Life</title>
        <?php include "includes/head_links.php"; ?>
        <link href="css/property_detail.css" rel="stylesheet" />
    </head>
    <body>
        <?php include "includes/header.php"; ?>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb py-2">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Not Found</li>
            </ol>
        </nav>
        <div class="page-container text-center py-5">
            <h1 class="mb-3">Property Not Found</h1>
            <p class="text-muted mb-4">The property you are looking for does not exist or has been removed.</p>
            <a href="index.php" class="btn btn-primary px-4 py-2">Back to Home</a>
        </div>
        <?php include "includes/signup_modal.php"; ?>
        <?php include "includes/login_modal.php"; ?>
        <?php include "includes/footer.php"; ?>
    </body>
    </html>
    <?php
    exit;
}

// Fetch City
$sql_city = "SELECT * FROM cities WHERE id = ?";
$stmt_city = $conn->prepare($sql_city);
$stmt_city->bind_param("i", $property['city_id']);
$stmt_city->execute();
$city = $stmt_city->get_result()->fetch_assoc();
$stmt_city->close();

// Fetch Images
$property_images = glob("img/properties/" . $property['id'] . "/*");
if (empty($property_images)) {
    $property_images = ["img/properties/1/1d4f0757fdb86d5f.jpg"];
}

// Total Rating
$total_rating = round(($property['rating_clean'] + $property['rating_food'] + $property['rating_safety']) / 3, 1);

// Interested Count
$sql_count = "SELECT COUNT(*) as count FROM interested_users_properties WHERE property_id = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $property['id']);
$stmt_count->execute();
$interested_count = $stmt_count->get_result()->fetch_assoc()['count'];
$stmt_count->close();

// Check if current user is interested
$is_interested = false;
if ($user_id) {
    $sql_ui = "SELECT id FROM interested_users_properties WHERE user_id = ? AND property_id = ?";
    $stmt_ui = $conn->prepare($sql_ui);
    $stmt_ui->bind_param("ii", $user_id, $property['id']);
    $stmt_ui->execute();
    if ($stmt_ui->get_result()->num_rows > 0) {
        $is_interested = true;
    }
    $stmt_ui->close();
}

// Fetch Amenities grouped by type
$amenities = [];
$sql_amenities = "SELECT a.name, a.type, a.icon FROM amenities a INNER JOIN properties_amenities pa ON a.id = pa.amenity_id WHERE pa.property_id = ?";
$stmt_am = $conn->prepare($sql_amenities);
$stmt_am->bind_param("i", $property['id']);
$stmt_am->execute();
$res_am = $stmt_am->get_result();
while ($row_am = $res_am->fetch_assoc()) {
    $amenities[$row_am['type']][] = $row_am;
}
$stmt_am->close();

// Fetch Testimonials
$testimonials = [];
$sql_testi = "SELECT * FROM testimonials WHERE property_id = ?";
$stmt_testi = $conn->prepare($sql_testi);
$stmt_testi->bind_param("i", $property['id']);
$stmt_testi->execute();
$res_testi = $stmt_testi->get_result();
while ($row_testi = $res_testi->fetch_assoc()) {
    $testimonials[] = $row_testi;
}
$stmt_testi->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($property['name']); ?> | PG Life</title>

    <?php include "includes/head_links.php"; ?>
    <link href="css/property_detail.css" rel="stylesheet" />
</head>

<body>
    <?php include "includes/header.php"; ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item">
                <a href="index.php">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="property_list.php?city=<?php echo urlencode($city['name']); ?>"><?php echo htmlspecialchars($city['name']); ?></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo htmlspecialchars($property['name']); ?>
            </li>
        </ol>
    </nav>

    <div id="property-images" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php
            foreach ($property_images as $index => $img_path) {
            ?>
                <li data-target="#property-images" data-slide-to="<?php echo $index; ?>" class="<?php echo $index == 0 ? 'active' : ''; ?>"></li>
            <?php
            }
            ?>
        </ol>
        <div class="carousel-inner">
            <?php
            foreach ($property_images as $index => $img_path) {
            ?>
                <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                    <img class="d-block w-100" src="<?php echo htmlspecialchars($img_path); ?>" alt="slide">
                </div>
            <?php
            }
            ?>
        </div>
        <a class="carousel-control-prev" href="#property-images" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#property-images" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <div class="property-summary page-container">
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
                <a href="#" class="btn btn-primary">Book Now</a>
            </div>
        </div>
    </div>

    <div class="property-amenities">
        <div class="page-container">
            <h1>Amenities</h1>
            <div class="row justify-content-between">
                <?php
                $amenity_types = ['Building', 'Common Area', 'Bedroom', 'Washroom'];
                foreach ($amenity_types as $type) {
                    if (isset($amenities[$type])) {
                ?>
                        <div class="col-md-auto">
                            <h5><?php echo htmlspecialchars($type); ?></h5>
                            <?php
                            foreach ($amenities[$type] as $amenity) {
                            ?>
                                <div class="amenity-container">
                                    <img src="img/amenities/<?php echo htmlspecialchars($amenity['icon']); ?>.svg">
                                    <span><?php echo htmlspecialchars($amenity['name']); ?></span>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <div class="property-about page-container">
        <h1>About the Property</h1>
        <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
    </div>

    <div class="property-rating">
        <div class="page-container">
            <h1>Property Rating</h1>
            <div class="row align-items-center justify-content-between">
                <div class="col-md-6">
                    <div class="rating-criteria row">
                        <div class="col-6">
                            <i class="rating-criteria-icon fas fa-broom"></i>
                            <span class="rating-criteria-text">Cleanliness</span>
                        </div>
                        <div class="rating-criteria-star-container col-6" title="<?php echo $property['rating_clean']; ?>">
                            <?php
                            for ($i = 0; $i < 5; $i++) {
                                if ($property['rating_clean'] >= $i + 0.8) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($property['rating_clean'] >= $i + 0.3) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <div class="rating-criteria row">
                        <div class="col-6">
                            <i class="rating-criteria-icon fas fa-utensils"></i>
                            <span class="rating-criteria-text">Food Quality</span>
                        </div>
                        <div class="rating-criteria-star-container col-6" title="<?php echo $property['rating_food']; ?>">
                            <?php
                            for ($i = 0; $i < 5; $i++) {
                                if ($property['rating_food'] >= $i + 0.8) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($property['rating_food'] >= $i + 0.3) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <div class="rating-criteria row">
                        <div class="col-6">
                            <i class="rating-criteria-icon fa fa-lock"></i>
                            <span class="rating-criteria-text">Safety</span>
                        </div>
                        <div class="rating-criteria-star-container col-6" title="<?php echo $property['rating_safety']; ?>">
                            <?php
                            for ($i = 0; $i < 5; $i++) {
                                if ($property['rating_safety'] >= $i + 0.8) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($property['rating_safety'] >= $i + 0.3) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="rating-circle">
                        <div class="total-rating"><?php echo $total_rating; ?></div>
                        <div class="rating-circle-star-container">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="property-testimonials page-container">
        <h1>What people say</h1>
        <?php
        if (count($testimonials) == 0) {
        ?>
            <p class="text-muted">No testimonials yet for this property.</p>
        <?php
        } else {
            foreach ($testimonials as $testimonial) {
        ?>
                <div class="testimonial-block">
                    <div class="testimonial-image-container">
                        <img class="testimonial-img" src="img/man.png">
                    </div>
                    <div class="testimonial-text">
                        <i class="fa fa-quote-left" aria-hidden="true"></i>
                        <p><?php echo htmlspecialchars($testimonial['content']); ?></p>
                    </div>
                    <div class="testimonial-name">- <?php echo htmlspecialchars($testimonial['user_name']); ?></div>
                </div>
        <?php
            }
        }
        ?>
    </div>

    <?php include "includes/signup_modal.php"; ?>
    <?php include "includes/login_modal.php"; ?>
    <?php include "includes/footer.php"; ?>
</body>

</html>
