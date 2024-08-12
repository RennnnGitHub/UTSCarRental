<?php
session_start();

// Load and decode JSON data
$carsJson = file_get_contents('cars.json');
$carsArray = json_decode($carsJson, true);

if (!isset($carsArray['cars'])) {
    die('Error: Cars data not found.');
}

$cars = $carsArray['cars'];

// Extract unique type and brand for potential dropdowns/filters
$types = array_unique(array_map(function ($car) { return $car['type']; }, $cars));
$brands = array_unique(array_map(function ($car) { return $car['brand']; }, $cars));
sort($types);
sort($brands);

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$filteredCars = [];

if (!empty($searchQuery)) {
    $lowercaseSearchQuery = strtolower($searchQuery);
    foreach ($cars as $car) {
        // Convert each property to lowercase 
        $carType = strtolower($car['type']);
        $carBrand = strtolower($car['brand']);
        $carModel = strtolower($car['carModel']);

        // Check if the search query matches any part of car properties
        if (strpos($carType, $lowercaseSearchQuery) !== false || 
            strpos($carBrand, $lowercaseSearchQuery) !== false || 
            strpos($carModel, $lowercaseSearchQuery) !== false) {
            $filteredCars[] = $car;
        }
    }
} else {
    $filteredCars = $cars; // Display all cars if no search query is provided
}

// Handle Rent button click
if (isset($_POST['rent'])) {
    $_SESSION['selectedCar'] = $_POST['rent'];
    header('Location: reservation.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>UTS Car Rental</title>
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
</head>

<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    #search-bar-container {
        margin-bottom: 20px;
        position: relative;
    }

    #search-query:focus {
        border-color: #4a90e2;
        box-shadow: 0 0 8px rgba(74, 144, 226, 0.5);
    }

    #search-query {
        transition: border 0.3s, box-shadow 0.3s;
    }

    #recent-searches,
    #suggestions {
        border: 1px solid #ddd;
        border-top: none;
        position: absolute;
        width: calc(100% - 30px);
        max-width: 250px;
        background: white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        z-index: 1000;
        display: none;
    }

    #recent-search-list li,
    #suggestions-list li {
        padding: 8px 12px;
        cursor: pointer;
    }

    #recent-search-list li:hover,
    #suggestions-list li:hover {
        background-color: #f8f8f8;
    }

    #suggestions-list {
        list-style: none;
        margin: 0;
        padding: 0 15px;
        max-height: 200px;
        overflow-y: auto;
    }

    .car-card {
        margin-bottom: 20px;
    }

    .car-card .card {
        margin: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .card-img-top {
        height: 200px;
        object-fit: cover;
    }

    .rent-button:disabled {
    background-color: #ccc;
    color: #666;
    cursor: not-allowed;
    opacity: 0.8;
    }

    .btn-primary:disabled {
        background-color: #ccc !important;
        color: #666 !important;
        cursor: not-allowed !important;
    }

    .card-img-top {
        transition: transform 0.5s ease;
        height: 200px;
        object-fit: cover;
    }

    .card-img-top:hover {
        transform: scale(1.1); 
    }
</style>




<body>
    <!-- Navbar Start -->
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
                    <li class="nav-item active"><a href="car.php" class="nav-link">Cars</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Navbar -->
    
    <section class="hero-wrap hero-wrap-2 js-fullheight" style="background-image: url('images/bg_3.jpg');" data-stellar-background-ratio="0.5">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text js-fullheight align-items-end justify-content-start">
                <div class="col-md-9 ftco-animate pb-5">
                    <p class="breadcrumbs"><span class="mr-2"><a href="index.html">Home <i class="ion-ios-arrow-forward"></i></a></span> <span>Cars <i class="ion-ios-arrow-forward"></i></span></p>
                    <h1 class="mb-3 bread">Choose Your Car</h1>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Car Display Section -->
    <section class="ftco-section bg-light">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-6">
                    <select class="form-control" id="type-filter">
                        <option value="">Select Type</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <select class="form-control" id="brand-filter">
                        <option value="">Select Brand</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo htmlspecialchars($brand); ?>"><?php echo htmlspecialchars($brand); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Search Form Update -->
            <div id="search-bar-container" style="margin-bottom: 20px; position: relative;">
                <form class="d-flex" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET">
                    <input type="text" class="form-control mr-2" id="search-query" name="search" placeholder="Search by model, brand or type" value="<?= htmlspecialchars($searchQuery); ?>" autocomplete="off" style="width: 100%;">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
                <div id="recent-searches">
                    <h6 class="p-2">Recent Searches:</h6>
                    <ul id="recent-search-list"></ul>
                </div>
                <div id="suggestions">
                    <h6 class="p-2">Suggestions:</h6>
                    <ul id="suggestions-list"></ul>
                </div>
            </div>



           <!-- Car Cards -->
            <div class="row">
                <?php foreach ($filteredCars as $car): ?>
                    <div class="col-md-4 car-card" data-brand="<?= htmlspecialchars($car['brand']); ?>" data-type="<?= htmlspecialchars($car['type']); ?>">
                        <div class="card">
                            <img class="card-img-top" src="<?= htmlspecialchars($car['image']); ?>" alt="<?= htmlspecialchars($car['carModel']); ?>">
                            <div class="card-body">
                                <h2><?= htmlspecialchars($car['carModel']); ?></h2>
                                <p>Price per Day: $<?= number_format($car['pricePerDay'], 2); ?>/day</p>
                                <p>Availability: <?= $car['quantity'] > 0 ? 'Available' : 'Not Available'; ?></p>
                                <?php if ($car['quantity'] > 0): ?>
                                    <form method="post" action="">
                                        <button type="submit" name="rent" value="<?= htmlspecialchars($car['carModel']); ?>" class="btn btn-primary rent-button">Rent</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-primary rent-button" disabled>Unavailable</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
                <div id="no-cars-message" class="col-12 text-center" style="display: none;">
                    <p>No cars found.</p>
                </div>
            </div>
    </section>


    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        $(document).ready(function() {
            var recentSearches = JSON.parse(localStorage.getItem('recentSearches')) || [];

            // Display recent searches when the search field is focused and empty
            $('#search-query').on('focus', function() {
                if (!$(this).val()) {
                    displayRecentSearches();
                }
            });

            // Update recent searches on form submission
            $('form').on('submit', function(event) {
                var query = $('#search-query').val().toLowerCase().trim();
                if (query && !recentSearches.includes(query)) {
                    updateRecentSearches(query); 
                }
            });

            // Update recent searches in local storage
            function updateRecentSearches(query) {
                recentSearches.unshift(query);
                recentSearches = [...new Set(recentSearches)]; 
                recentSearches = recentSearches.slice(0, 5); 
                localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
            }

            // Display recent searches
            function displayRecentSearches() {
                var list = $('#recent-search-list');
                list.empty();
                recentSearches.forEach(function(search) {
                    list.append('<li><a href="#" class="recent-search-item">' + search + '</a></li>');
                });
                $('#recent-searches').show();
            }

            // Display suggestions based on input
            $('#search-query').on('input', function() {
                var query = $(this).val().toLowerCase().trim();
                if (query) {
                    $('#recent-searches').hide();
                    displaySuggestions(query);
                    searchCars(query); 
                } else {
                    $('#suggestions').hide();
                    filterCars(); 
                }
            });

            function displaySuggestions(query) {
                var suggestionsList = $('#suggestions-list');
                suggestionsList.empty();

                // Filter suggestions from recent searches and available car models
                var suggestions = recentSearches.filter(function(search) {
                    return search.toLowerCase().startsWith(query);
                });

                // Append car models to suggestions
                $('.car-card').each(function() {
                    var carModel = $(this).find('h2').text().toLowerCase();
                    if (carModel.includes(query) && !suggestions.includes(carModel)) {
                        suggestions.push(carModel);
                    }
                });

                suggestions.forEach(function(suggestion) {
                    var highlighted = highlightText(suggestion, query);
                    suggestionsList.append('<li><a href="#" class="suggestion-item">' + highlighted + '</a></li>');
                });

                if (suggestions.length > 0) {
                    $('#suggestions').show();
                } else {
                    $('#suggestions').hide();
                }
            }

            function highlightText(text, query) {
                var regex = new RegExp('(' + query + ')', 'gi');
                return text.replace(regex, '<strong>$1</strong>');
            }

            // Click handler for items in the recent searches and suggestions lists
            $(document).on('click', '.recent-search-item, .suggestion-item', function(event) {
                event.preventDefault();
                var keyword = $(this).text();
                $('#search-query').val(keyword);
                searchCars(keyword);
                $('#recent-searches, #suggestions').hide();
            });

            // Hide recent searches and suggestions when user clicks outside
            $(document).on('click', function(event) {
                if (!$(event.target).closest('#search-query').length && !$(event.target).closest('#recent-searches, #suggestions').length) {
                    $('#recent-searches, #suggestions').hide();
                }
            });

            // Filter cars based on search query
            function searchCars(query) {
                $('.car-card').hide();
                $('.car-card').filter(function() {
                    var carModel = $(this).find('h2').text().toLowerCase();
                    var carBrand = $(this).data('brand').toLowerCase();
                    var carType = $(this).data('type').toLowerCase();

                    return carModel.includes(query) || carBrand.includes(query) || carType.includes(query);
                }).show();
            }

            // Event listeners for type and brand filters
            $('#type-filter, #brand-filter').on('change', function() {
                filterCars();
            });

            // Filter cars based on type and brand filters
            function filterCars() {
                var selectedType = $('#type-filter').val().toLowerCase();
                var selectedBrand = $('#brand-filter').val().toLowerCase();

                $('.car-card').hide();
                $('.car-card').filter(function() {
                    var carType = $(this).data('type').toLowerCase();
                    var carBrand = $(this).data('brand').toLowerCase();

                    return (selectedType === '' || carType === selectedType) &&
                           (selectedBrand === '' || carBrand === selectedBrand);
                }).show();
            }
            function filterCarsByInput() {
            var searchQuery = $('#search-query').val().toLowerCase().trim();
            
            if (!searchQuery) {
                $('.car-card').show(); // Show all cars if search query is empty
                $('#no-cars-message').hide(); // Hide no cars message
                return;
            }

            var visibleCars = 0;
            $('.car-card').each(function() {
                var carModel = $(this).find('h2').text().toLowerCase();
                var carBrand = $(this).data('brand').toLowerCase();
                var carType = $(this).data('type').toLowerCase();
                
                if (carModel.includes(searchQuery) || carBrand.includes(searchQuery) || carType.includes(searchQuery)) {
                    $(this).show();
                    visibleCars++;
                } else {
                    $(this).hide();
                }
            });

            // Toggle visibility of the no cars message based on number of cars visible
            if (visibleCars > 0) {
                $('#no-cars-message').hide();
            } else {
                $('#no-cars-message').show();
            }
        }

        // Attach filter function to the search input events
        $('#search-query').on('input', filterCarsByInput);

        // Call filter function on page load to handle any pre-filled values like back navigation
        filterCarsByInput();
        });
    </script>
</body>
</html>
