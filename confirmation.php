<?php
session_start();
require_once 'db_connect.php'; 

if (!isset($_SESSION['order'])) {
    die('Error: No order found.');
}

$order = $_SESSION['order'];

// Fetch car details from JSON
$carsJson = file_get_contents('cars.json');
$carsArray = json_decode($carsJson, true);

$carDetails = null;
foreach ($carsArray['cars'] as $car) {
    if ($car['carModel'] === $order['carModel']) {
        $carDetails = $car;
        break;
    }
}

if (!$carDetails) {
    die('Error: Car details not found.');
}

// Update JSON file and MySQL database
if (isset($_GET['confirm'])) {
    foreach ($carsArray['cars'] as &$car) {
        if ($car['carModel'] === $order['carModel']) {
            $car['quantity'] -= $order['quantity'];
            break;
        }
    }
    file_put_contents('cars.json', json_encode($carsArray, JSON_PRETTY_PRINT));

    // Update MySQL database
    $stmt = $conn->prepare("UPDATE orders SET status = 'confirmed' WHERE user_name = ? AND user_mobile_number = ? AND user_email = ? AND user_driver_license = ?");
    $stmt->bind_param("ssss", $order['name'], $order['mobile'], $order['email'], $order['license']);
    $stmt->execute();
    $stmt->close();

    // Clear session order
    unset($_SESSION['order']);
    
    // Set a flag to show confirmation message
    $showConfirmation = true;
    } else {
        $showConfirmation = false;
    }
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Confirmation</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/aos.css">
    <link rel="stylesheet" href="css/ionicons.min.css">
    <link rel="stylesheet" href="css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="css/jquery.timepicker.css">
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/icomoon.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
        }

        #ftco-navbar {
            background-color: black !important;
            margin-bottom: 0;
        }

        .ftco-section {
            padding-top: 20px;
        }

        .confirmation-details {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .confirmation-message {
            font-size: 1.2em;
            color: green;
            margin-bottom: 20px;
        }

        .btn-back-to-cars {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
        <div class="container">
            <a class="navbar-brand" href="car.php">UTS<span> Car Rental</span></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="oi oi-menu"></span> Menu
            </button>
            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a href="order.php" class="nav-link">Orders</a></li>
                    <li class="nav-item"><a href="reservation.php" class="nav-link">Reservation</a></li>
                    <li class="nav-item"><a href="car.php" class="nav-link">Cars</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Navbar -->

    
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="heading-section text center ftco-animate">
                        <h2 class="mb-4">Order Confirmation</h2>
                    </div>
                    <div class="confirmation-details text-center">
                        <?php if ($showConfirmation): ?>
                            <div class="confirmation-message">
                                Your order has been successfully confirmed!
                            </div>
                            <p>Thank you for confirming your order. You can now return to the cars page to view more options or make another reservation.</p>
                            <a href="car.php" class="btn btn-primary btn-back-to-cars">Back to Cars</a>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($carDetails['image']); ?>" alt="Car Image" style="width: 100%; max-width: 300px;">
                            <p>Car Model: <?= htmlspecialchars($order['carModel']); ?></p>
                            <p>Price per Day: $<?= number_format($carDetails['pricePerDay'], 2); ?>/day</p>
                            <p>Total Price: $<?= number_format($order['totalPrice'], 2); ?></p>
                            <p>Click the link below to confirm your order:</p>
                            <a href="confirmation.php?confirm=true" class="btn btn-primary">Confirm Order</a>
                            <p>or</p>
                            <p><a href="confirmation.php?confirm=true">confirmation.php?confirm=true</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="ftco-footer ftco-bg-dark ftco-section">
    </footer>
    <!-- Footer -->

    <!-- Loader -->
    <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>

    <script src="js/jquery.min.js"></script>
    <script src="js/jquery-migrate-3.0.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.easing.1.3.js"></script>
    <script src="js/jquery.waypoints.min.js"></script>
    <script src="js/jquery.stellar.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/aos.js"></script>
    <script src="js/jquery.animateNumber.min.js"></script>
    <script src="js/bootstrap-datepicker.js"></script>
    <script src="js/jquery.timepicker.min.js"></script>
    <script src="js/scrollax.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
