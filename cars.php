<?php
require_once 'db.php';

// Get search parameters
$location = $_GET['location'] ?? '';
$pickup_date = $_GET['pickup_date'] ?? '';
$return_date = $_GET['return_date'] ?? '';
$car_type = $_GET['car_type'] ?? '';
$fuel_type = $_GET['fuel_type'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'price_asc';

// Build query
$query = "SELECT * FROM cars WHERE availability_status = 'Available'";
$params = [];

if ($location) {
    $query .= " AND location = ?";
    $params[] = $location;
}

if ($car_type) {
    $query .= " AND car_type = ?";
    $params[] = $car_type;
}

if ($fuel_type) {
    $query .= " AND fuel_type = ?";
    $params[] = $fuel_type;
}

if ($min_price) {
    $query .= " AND price_per_day >= ?";
    $params[] = $min_price;
}

if ($max_price) {
    $query .= " AND price_per_day <= ?";
    $params[] = $max_price;
}

// Add sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY price_per_day ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY price_per_day DESC";
        break;
    case 'rating':
        $query .= " ORDER BY rating DESC";
        break;
    case 'newest':
        $query .= " ORDER BY year DESC";
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$cars = $stmt->fetchAll();

// Get filter options
$locations = $pdo->query("SELECT DISTINCT city FROM locations WHERE is_active = TRUE ORDER BY city")->fetchAll();
$car_types = $pdo->query("SELECT DISTINCT car_type FROM cars ORDER BY car_type")->fetchAll();
$fuel_types = $pdo->query("SELECT DISTINCT fuel_type FROM cars ORDER BY fuel_type")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Cars - RentACar</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            padding: 2rem 0;
        }

        /* Filters Sidebar */
        .filters-sidebar {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .filters-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .filter-group {
            margin-bottom: 1.5rem;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
        }

        .price-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }

        .filter-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
        }

        /* Cars Section */
        .cars-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .cars-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .results-count {
            font-size: 1.2rem;
            color: #666;
        }

        .sort-dropdown {
            padding: 10px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
        }

        /* Car Cards */
        .cars-grid {
            display: grid;
            gap: 2rem;
        }

        .car-card {
            display: grid;
            grid-template-columns: 300px 1fr auto;
            gap: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .car-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .car-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .car-details {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .car-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .car-specs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: #666;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .car-features {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .car-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stars {
            color: #ffc107;
        }

        .car-pricing {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: white;
            border-left: 1px solid #e1e5e9;
        }

        .price {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .price-label {
            color: #666;
            margin-bottom: 1rem;
        }

        .book-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
            text-decoration: none;
            text-align: center;
        }

        .book-btn:hover {
            transform: translateY(-2px);
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .no-results i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #ccc;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .filters-sidebar {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .car-card {
                grid-template-columns: 1fr;
            }

            .car-pricing {
                border-left: none;
                border-top: 1px solid #e1e5e9;
            }

            .cars-header {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-car"></i> RentACar
                </a>
                <nav>
                    <ul class="nav-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="cars.php">Cars</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="main-content">
            <!-- Filters Sidebar -->
            <aside class="filters-sidebar">
                <h3 class="filters-title">Filter Cars</h3>
                <form id="filterForm">
                    <div class="filter-group">
                        <label for="filter_location">Location</label>
                        <select id="filter_location" name="location">
                            <option value="">All Locations</option>
                            <?php foreach($locations as $loc): ?>
                                <option value="<?= htmlspecialchars($loc['city']) ?>" 
                                        <?= $location === $loc['city'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loc['city']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter_car_type">Car Type</label>
                        <select id="filter_car_type" name="car_type">
                            <option value="">All Types</option>
                            <?php foreach($car_types as $type): ?>
                                <option value="<?= htmlspecialchars($type['car_type']) ?>" 
                                        <?= $car_type === $type['car_type'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['car_type']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter_fuel_type">Fuel Type</label>
                        <select id="filter_fuel_type" name="fuel_type">
                            <option value="">All Fuel Types</option>
                            <?php foreach($fuel_types as $fuel): ?>
                                <option value="<?= htmlspecialchars($fuel['fuel_type']) ?>" 
                                        <?= $fuel_type === $fuel['fuel_type'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($fuel['fuel_type']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Price Range (per day)</label>
                        <div class="price-range">
                            <input type="number" name="min_price" placeholder="Min $" 
                                   value="<?= htmlspecialchars($min_price) ?>">
                            <input type="number" name="max_price" placeholder="Max $" 
                                   value="<?= htmlspecialchars($max_price) ?>">
                        </div>
                    </div>

                    <input type="hidden" name="pickup_date" value="<?= htmlspecialchars($pickup_date) ?>">
                    <input type="hidden" name="return_date" value="<?= htmlspecialchars($return_date) ?>">

                    <button type="submit" class="filter-btn">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </form>
            </aside>

            <!-- Cars Section -->
            <main class="cars-section">
                <div class="cars-header">
                    <div class="results-count">
                        <?= count($cars) ?> cars available
                        <?php if ($pickup_date && $return_date): ?>
                            from <?= date('M j', strtotime($pickup_date)) ?> to <?= date('M j, Y', strtotime($return_date)) ?>
                        <?php endif; ?>
                    </div>
                    <select class="sort-dropdown" id="sortSelect">
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    </select>
                </div>

                <div class="cars-grid">
                    <?php if (empty($cars)): ?>
                        <div class="no-results">
                            <i class="fas fa-car"></i>
                            <h3>No cars found</h3>
                            <p>Try adjusting your search criteria or filters</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($cars as $car): ?>
                        <div class="car-card">
                            <img src="<?= htmlspecialchars($car['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>" 
                                 class="car-image">
                            
                            <div class="car-details">
                                <h3 class="car-title"><?= htmlspecialchars($car['brand'] . ' ' . $car['model'] . ' ' . $car['year']) ?></h3>
                                
                                <div class="car-specs">
                                    <div class="spec-item">
                                        <i class="fas fa-car"></i>
                                        <span><?= htmlspecialchars($car['car_type']) ?></span>
                                    </div>
                                    <div class="spec-item">
                                        <i class="fas fa-gas-pump"></i>
                                        <span><?= htmlspecialchars($car['fuel_type']) ?></span>
                                    </div>
                                    <div class="spec-item">
                                        <i class="fas fa-cog"></i>
                                        <span><?= htmlspecialchars($car['transmission']) ?></span>
                                    </div>
                                    <div class="spec-item">
                                        <i class="fas fa-users"></i>
                                        <span><?= htmlspecialchars($car['seats']) ?> seats</span>
                                    </div>
                                </div>

                                <div class="car-features">
                                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($car['features']) ?>
                                </div>

                                <div class="car-rating">
                                    <div class="stars">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?= $i <= $car['rating'] ? '' : '-o' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span><?= $car['rating'] ?> (<?= $car['total_reviews'] ?> reviews)</span>
                                </div>
                            </div>

                            <div class="car-pricing">
                                <div class="price">$<?= number_format($car['price_per_day'], 2) ?></div>
                                <div class="price-label">per day</div>
                                <a href="#" class="book-btn" onclick="bookCar(<?= $car['id'] ?>)">
                                    <i class="fas fa-calendar-check"></i> Book Now
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Handle filter form submission
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });

        // Handle sort change
        document.getElementById('sortSelect').addEventListener('change', function() {
            applyFilters();
        });

        function applyFilters() {
            const formData = new FormData(document.getElementById('filterForm'));
            const params = new URLSearchParams();
            
            // Add form data to params
            for (let [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }
            
            // Add sort parameter
            const sort = document.getElementById('sortSelect').value;
            if (sort) params.append('sort', sort);
            
            // Redirect with new parameters
            window.location.href = 'cars.php?' + params.toString();
        }

        function bookCar(carId) {
            const urlParams = new URLSearchParams(window.location.search);
            const pickupDate = urlParams.get('pickup_date');
            const returnDate = urlParams.get('return_date');
            const location = urlParams.get('location');
            
            if (!pickupDate || !returnDate) {
                alert('Please select pickup and return dates first');
                window.location.href = 'index.php';
                return;
            }
            
            const params = new URLSearchParams({
                car_id: carId,
                pickup_date: pickupDate,
                return_date: returnDate,
                location: location || ''
            });
            
            window.location.href = 'booking.php?' + params.toString();
        }
    </script>
</body>
</html>
