<?php
session_start();
require_once 'db_connect.php'; 

$query = "SELECT * FROM orders WHERE status = 'unconfirmed'";
$result = $conn->query($query);

if (!$result) {
    die('Error fetching unconfirmed orders: ' . $conn->error);
}

// Load and decode JSON data
$carsJson = file_get_contents('cars.json');
$carsArray = json_decode($carsJson, true);

if (!isset($carsArray['cars'])) {
    die('Error: Cars data not found.');
}

$cars = $carsArray['cars'];

// Function to find car details by model
function findCarDetails($cars, $carModel) {
    foreach ($cars as $car) {
        if ($car['carModel'] === $carModel) {
            return $car;
        }
    }
    return null;
}

// Handle order cancellation
if (isset($_GET['cancel_id'])) {
    $cancelId = $_GET['cancel_id'];
    $cancelStmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
    if ($cancelStmt) {
        $cancelStmt->bind_param("i", $cancelId);
        $cancelStmt->execute();
        $cancelStmt->close();
    } else {
        die('Error preparing cancel statement: ' . $conn->error);
    }
    header("Location: order.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Orders</title>
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
        .navbar-dark {
            background-color: #000 !important;
        }
        .car-image {
            width: 100px;
            height: auto;
        }
        .table {
            margin-top: 20px;
        }
        .table th, .table td {
            padding: 15px;
            text-align: center;
        }
        .btn {
            width: 100%;
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
                    <li class="nav-item active"><a href="order.php" class="nav-link">Orders</a></li>
                    <li class="nav-item"><a href="reservation.php" class="nav-link">Reservation</a></li>
                    <li class="nav-item"><a href="car.php" class="nav-link">Cars</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Navbar -->

    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="heading-section text-center ftco-animate">
                        <h2 class="mb-4">Unconfirmed Orders</h2>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Car Image</th>
                                <th>Price per Day</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>License</th>
                                <th>Car Model</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Total Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php
                                    $carDetails = findCarDetails($cars, $row['car_model'] ?? '');
                                    $carImage = $carDetails['image'] ?? 'images/no-image.png';
                                    $carPricePerDay = $carDetails['pricePerDay'] ?? '0.00';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['order_id'] ?? 'N/A'); ?></td>
                                    <td><img src="<?php echo htmlspecialchars($carImage); ?>" alt="<?php echo htmlspecialchars($row['car_model'] ?? 'N/A'); ?>" class="car-image"></td>
                                    <td>$<?php echo number_format((float)$carPricePerDay, 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_mobile_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_driver_license'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['car_model'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['rent_start_date'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['rent_end_date'] ?? 'N/A'); ?></td>
                                    <td>$<?php echo number_format((float)($row['price'] ?? 0), 2); ?></td>
                                    <td>
                                        <a href="confirmation.php?order_id=<?php echo $row['order_id'] ?? ''; ?>" class="btn btn-success mb-2">Confirm Order</a>
                                        <a href="order.php?cancel_id=<?php echo $row['order_id'] ?? ''; ?>" class="btn btn-danger">Cancel Order</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php $result->free(); ?>
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

<?php
$conn->close();
?>
