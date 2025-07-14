<?php
require_once 'db.php';

// Fetch featured cars
$featured_cars_query = "SELECT * FROM cars WHERE availability_status = 'Available' ORDER BY rating DESC LIMIT 6";
$featured_cars = $pdo->query($featured_cars_query)->fetchAll();

// Fetch locations
$locations_query = "SELECT DISTINCT city FROM locations WHERE is_active = TRUE ORDER BY city";
$locations = $pdo->query($locations_query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentACar - Premium Car Rental Service</title>
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

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=1200') center/cover;
            height: 70vh;
            display: flex;
            align-items: center;
            color: white;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        /* Search Form */
        .search-section {
            background: white;
            padding: 3rem 0;
            margin-top: -100px;
            position: relative;
            z-index: 10;
        }

        .search-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }

        .form-group input, .form-group select {
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .search-btn:hover {
            transform: translateY(-2px);
        }

        /* Featured Cars */
        .featured-section {
            padding: 4rem 0;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #333;
        }

        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .car-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .car-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .car-info {
            padding: 1.5rem;
        }

        .car-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .car-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            color: #666;
            font-size: 0.9rem;
        }

        .car-features {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .car-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stars {
            color: #ffc107;
        }

        /* Footer */
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 4rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .search-form {
                grid-template-columns: 1fr;
            }

            .cars-grid {
                grid-template-columns: 1fr;
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

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Find Your Perfect Ride</h1>
                <p>Premium car rental service with the best deals and top-rated vehicles</p>
            </div>
        </div>
    </section>

    <section class="search-section">
        <div class="container">
            <form class="search-form" id="searchForm">
                <div class="form-group">
                    <label for="location">Pickup Location</label>
                    <select id="location" name="location" required>
                        <option value="">Select Location</option>
                        <?php foreach($locations as $location): ?>
                            <option value="<?= htmlspecialchars($location['city']) ?>">
                                <?= htmlspecialchars($location['city']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pickup_date">Pickup Date</label>
                    <input type="date" id="pickup_date" name="pickup_date" required>
                </div>
                <div class="form-group">
                    <label for="return_date">Return Date</label>
                    <input type="date" id="return_date" name="return_date" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search Cars
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="featured-section">
        <div class="container">
            <h2 class="section-title">Featured Vehicles</h2>
            <div class="cars-grid">
                <?php foreach($featured_cars as $car): ?>
                <div class="car-card">
                    <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>" class="car-image">
                    <div class="car-info">
                        <h3 class="car-title"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h3>
                        <div class="car-details">
                            <span><i class="fas fa-car"></i> <?= htmlspecialchars($car['car_type']) ?></span>
                            <span><i class="fas fa-gas-pump"></i> <?= htmlspecialchars($car['fuel_type']) ?></span>
                            <span><i class="fas fa-users"></i> <?= htmlspecialchars($car['seats']) ?> seats</span>
                        </div>
                        <div class="car-features">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($car['features']) ?>
                        </div>
                        <div class="car-price">
                            <span class="price">$<?= number_format($car['price_per_day'], 2) ?>/day</span>
                            <div class="rating">
                                <div class="stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= $car['rating'] ? '' : '-o' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span>(<?= $car['total_reviews'] ?>)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2024 RentACar. All rights reserved. | Premium Car Rental Service</p>
        </div>
    </footer>

    <script>
        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('pickup_date').min = today;
            document.getElementById('return_date').min = today;
            
            // Update return date minimum when pickup date changes
            document.getElementById('pickup_date').addEventListener('change', function() {
                document.getElementById('return_date').min = this.value;
            });
        });

        // Handle search form submission
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const location = document.getElementById('location').value;
            const pickupDate = document.getElementById('pickup_date').value;
            const returnDate = document.getElementById('return_date').value;
            
            if (!location || !pickupDate || !returnDate) {
                alert('Please fill in all fields');
                return;
            }
            
            if (new Date(returnDate) <= new Date(pickupDate)) {
                alert('Return date must be after pickup date');
                return;
            }
            
            // Redirect to cars page with search parameters
            const params = new URLSearchParams({
                location: location,
                pickup_date: pickupDate,
                return_date: returnDate
            });
            
            window.location.href = 'cars.php?' + params.toString();
        });
    </script>
</body>
</html>
