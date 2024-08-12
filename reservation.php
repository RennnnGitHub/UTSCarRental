<?php
session_start();
require_once 'db_connect.php'; 

$carsJson = file_get_contents('cars.json');
$carsArray = json_decode($carsJson, true);

if (!isset($carsArray['cars'])) {
    die('Error: Cars data not found.');
}

$cars = $carsArray['cars'];

// Initialize variables
$selectedCar = '';
$availability = 'Not Available';
$carDetails = [];

// Check if a car was selected to rent
if (isset($_SESSION['selectedCar'])) {
    $selectedCar = $_SESSION['selectedCar'];
    foreach ($cars as $car) {
        if ($car['carModel'] === $selectedCar) {
            $availability = $car['quantity'] > 0 ? 'Available' : 'Not Available';
            $carDetails = $car;
            break;
        }
    }
}

// Cancel reservation
if (isset($_POST['cancel'])) {
    unset($_SESSION['selectedCar']);
    header("Location: reservation.php");
    exit();
}

// Place order
if (isset($_POST['placeOrder'])) {
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $license = $_POST['license'];
    $quantity = $_POST['quantity'];
    $startDate = $_POST['start-date'];
    $endDate = $_POST['end-date'];
    $carModel = $selectedCar;

    if ($name && $mobile && $email && $license && $quantity && $startDate && $endDate) {
        if (strtotime($endDate) >= strtotime($startDate)) {
            // Save order details
            $_SESSION['order'] = [
                'carModel' => $carModel,
                'name' => $name,
                'mobile' => $mobile,
                'email' => $email,
                'license' => $license,
                'quantity' => $quantity,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalPrice' => $_POST['totalPrice']
            ];

            // Insert to database
            $stmt = $conn->prepare("INSERT INTO orders (user_name, user_mobile_number, user_email, user_driver_license, rent_start_date, rent_end_date, price, status, car_model) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssiss", $name, $mobile, $email, $license, $startDate, $endDate, $_POST['totalPrice'], $status, $carModel);
            $status = 'unconfirmed';
            $stmt->execute();
            $stmt->close();

            // Clear session selectedCar
            unset($_SESSION['selectedCar']);

            // Redirect to confirmation pg
            header("Location: confirmation.php");
            exit();
        } else {
            $error = "End date cannot be before start date.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reservation</title>
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

        .reservation-details {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
        }

        .car-image {
            max-width: 100%;
            height: auto;
        }

        .form-control {
            margin-bottom: 10px;
        }

        .btn-disabled {
            background-color: gray;
            cursor: not-allowed;
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
                    <li class="nav-item active"><a href="reservation.php" class="nav-link">Reservation</a></li>
                    <li class="nav-item"><a href="car.php" class="nav-link">Cars</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Navbar -->

    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="heading-section text-center ftco-animate">
                        <h2 class="mb-4">Reservation Details</h2>
                    </div>
                    <div class="reservation-details">
                        <?php if ($selectedCar): ?>
                            <div class="car-info text-center">
                                <img src="<?php echo htmlspecialchars($carDetails['image']); ?>" alt="<?php echo htmlspecialchars($selectedCar); ?>" class="car-image">
                                <p><strong>Car Model:</strong> <?php echo htmlspecialchars($selectedCar); ?></p>
                                <p><strong>Price per Day:</strong> $<?php echo number_format($carDetails['pricePerDay'], 2); ?></p>
                                <p><strong>Availability:</strong> <?php echo htmlspecialchars($availability); ?></p>
                            </div>
                            <form id="reservation-form" method="post" action="reservation.php">
                                <div class="form-group">
                                    <label for="quantity">Quantity:</label>
                                    <input type="number" id="quantity" name="quantity" class="form-control" min="1" max="<?php echo $carDetails['quantity']; ?>" value="1" required>
                                </div>
                                <div class="form-group">
                                    <label for="start-date">Start Date:</label>
                                    <input type="date" id="start-date" name="start-date" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="end-date">End Date:</label>
                                    <input type="date" id="end-date" name="end-date" class="form-control" required>
                                </div>
                                <p><strong>Total Price:</strong> $<span id="total-price">0.00</span></p>

                                <div class="form-group">
                                    <label for="name">Name:</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="mobile">Mobile Number:</label>
                                    <input type="text" id="mobile" name="mobile" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address:</label>
                                    <input type="email" id="email" name="email" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="license">Driver's License:</label>
                                    <input type="text" id="license" name="license" class="form-control" required>
                                </div>

                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="valid-license">
                                    <label class="form-check-label" for="valid-license">I have a valid driver's license</label>
                                </div>

                                <input type="hidden" id="totalPrice" name="totalPrice">
                                
                                <?php if (isset($error)): ?>
                                    <p class="text-danger"><?php echo htmlspecialchars($error); ?></p>
                                <?php endif; ?>

                                <button type="submit" name="placeOrder" class="btn btn-primary btn-disabled" disabled>Submit Order</button>
                            </form>
                            <form method="post" action="reservation.php" style="margin-top: 10px;">
                                <button type="submit" name="cancel" class="btn btn-secondary">Cancel Reservation</button>
                            </form>
                        <?php else: ?>
                            <p>No car selected. Please choose a car from the <a href="car.php">Cars</a> page.</p>
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
    <script>
        $(document).ready(function() {
            function calculateTotalPrice() {
                var pricePerDay = <?php echo $carDetails['pricePerDay']; ?>;
                var quantity = $('#quantity').val();
                var startDate = new Date($('#start-date').val());
                var endDate = new Date($('#end-date').val());

                // Calculate days between dates
                var timeDifference = endDate.getTime() - startDate.getTime();
                var days = Math.ceil(timeDifference / (1000 * 3600 * 24)) + 1;

                if (!isNaN(days) && days > 0 && quantity > 0) {
                    var totalPrice = pricePerDay * days * quantity;
                    $('#total-price').text(totalPrice.toFixed(2));
                    $('#totalPrice').val(totalPrice.toFixed(2));
                } else {
                    $('#total-price').text('0.00');
                    $('#totalPrice').val('0.00');
                }
            }

            function validateDates() {
                var startDate = new Date($('#start-date').val());
                var endDate = new Date($('#end-date').val());

                if (endDate < startDate) {
                    $('#end-date').val('');
                    alert('End date cannot be before start date.');
                }
            }

            function togglePlaceOrderButton() {
                var isValidLicense = $('#valid-license').is(':checked');
                var isFormFilled = $('#name').val() && $('#mobile').val() && $('#email').val() && $('#license').val() && $('#start-date').val() && $('#end-date').val();
                
                if (isValidLicense && isFormFilled) {
                    $('button[name="placeOrder"]').removeClass('btn-disabled').prop('disabled', false);
                } else {
                    $('button[name="placeOrder"]').addClass('btn-disabled').prop('disabled', true);
                }
            }

            $('#quantity, #start-date, #end-date, #valid-license, #name, #mobile, #email, #license').on('change keyup', function() {
                calculateTotalPrice();
                validateDates();
                togglePlaceOrderButton();
            });

            $('#reservation-form').on('submit', function(event) {
                var name = $('#name').val();
                var mobile = $('#mobile').val();
                var email = $('#email').val();
                var license = $('#license').val();

                if (!name || !mobile || !email || !license) {
                    event.preventDefault();
                    alert('All fields are required.');
                } else {
                    alert('Reservation confirmed!');
                }
            });
        });
    </script>
</body>
</html>
