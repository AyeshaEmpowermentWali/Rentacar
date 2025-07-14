<?php
require_once 'db.php';

// Get parameters
$car_id = $_GET['car_id'] ?? '';
$pickup_date = $_GET['pickup_date'] ?? '';
$return_date = $_GET['return_date'] ?? '';
$location = $_GET['location'] ?? '';

if (!$car_id || !$pickup_date || !$return_date) {
    header('Location: index.php');
    exit;
}

// Get car details
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND availability_status = 'Available'");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    header('Location: cars.php');
    exit;
}

// Calculate rental details
$total_days = calculateDays($pickup_date, $return_date);
$total_amount = $total_days * $car['price_per_day'];

// Handle form submission
if ($_POST) {
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    $pickup_location = trim($_POST['pickup_location']);
    
    $errors = [];
    
    // Validation
    if (empty($customer_name)) $errors[] = "Name is required";
    if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($customer_phone)) $errors[] = "Phone number is required";
    if (empty($pickup_location)) $errors[] = "Pickup location is required";
    
    if (empty($errors)) {
        try {
            $booking_reference = generateBookingReference();
            
            $stmt = $pdo->prepare("
                INSERT INTO bookings (car_id, customer_name, customer_email, customer_phone, 
                                    pickup_location, pickup_date, return_date, total_days, 
                                    total_amount, booking_reference) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $car_id, $customer_name, $customer_email, $customer_phone,
                $pickup_location, $pickup_date, $return_date, $total_days,
                $total_amount, $booking_reference
            ]);
            
            // Redirect to confirmation
            header("Location: confirmation.php?ref=$booking_reference");
            exit;
            
        } catch (Exception $e) {
            $errors[] = "Booking failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?> - RentACar</title>
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
        .booking-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
            padding: 2rem 0;
        }

        /* Booking Form */
        .booking-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .form-title {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .error-messages {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid #f5c6cb;
        }

        .error-messages ul {
            margin: 0;
            padding-left: 1.5rem;
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        /* Booking Summary */
        .booking-summary {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .summary-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .car-summary {
            margin-bottom: 2rem;
        }

        .car-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .car-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .car-specs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: #666;
            font-size: 0.9rem;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .rental-details {
            border-top: 1px solid #e1e5e9;
            padding-top: 1rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            color: #666;
        }

        .detail-row.total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            border-top: 1px solid #e1e5e9;
            padding-top: 0.5rem;
            margin-top: 1rem;
        }

        .price-highlight {
            color: #667eea;
            font-weight: bold;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .booking-container {
                grid-template-columns: 1fr;
            }

            .booking-summary {
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

            .form-row {
                grid-template-columns: 1fr;
            }

            .car-specs {
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

    <div class="container">
        <div class="booking-container">
            <!-- Booking Form -->
            <div class="booking-form">
                <h2 class="form-title">Complete Your Booking</h2>

                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <ul>
                            <?php foreach($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_name">Full Name *</label>
                            <input type="text" id="customer_name" name="customer_name" 
                                   value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="customer_email">Email Address *</label>
                            <input type="email" id="customer_email" name="customer_email" 
                                   value="<?= htmlspecialchars($_POST['customer_email'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="customer_phone">Phone Number *</label>
                        <input type="tel" id="customer_phone" name="customer_phone" 
                               value="<?= htmlspecialchars($_POST['customer_phone'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="pickup_location">Pickup Location *</label>
                        <input type="text" id="pickup_location" name="pickup_location" 
                               value="<?= htmlspecialchars($_POST['pickup_location'] ?? $location) ?>" 
                               placeholder="Enter specific pickup address" required>
                    </div>

                    <div class="form-group">
                        <label for="special_requests">Special Requests (Optional)</label>
                        <textarea id="special_requests" name="special_requests" rows="3" 
                                  placeholder="Any special requirements or requests..."><?= htmlspecialchars($_POST['special_requests'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-credit-card"></i> Confirm Booking
                    </button>
                </form>
            </div>

            <!-- Booking Summary -->
            <div class="booking-summary">
                <h3 class="summary-title">Booking Summary</h3>
                
                <div class="car-summary">
                    <img src="<?= htmlspecialchars($car['image_url']) ?>" 
                         alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>" 
                         class="car-image">
                    
                    <h4 class="car-title"><?= htmlspecialchars($car['brand'] . ' ' . $car['model'] . ' ' . $car['year']) ?></h4>
                    
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
                </div>

                <div class="rental-details">
                    <div class="detail-row">
                        <span><i class="fas fa-calendar-alt"></i> Pickup Date:</span>
                        <span><?= date('M j, Y', strtotime($pickup_date)) ?></span>
                    </div>
                    <div class="detail-row">
                        <span><i class="fas fa-calendar-alt"></i> Return Date:</span>
                        <span><?= date('M j, Y', strtotime($return_date)) ?></span>
                    </div>
                    <div class="detail-row">
                        <span><i class="fas fa-clock"></i> Rental Duration:</span>
                        <span><?= $total_days ?> day<?= $total_days > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="detail-row">
                        <span><i class="fas fa-dollar-sign"></i> Daily Rate:</span>
                        <span class="price-highlight">$<?= number_format($car['price_per_day'], 2) ?></span>
                    </div>
                    <div class="detail-row total">
                        <span>Total Amount:</span>
                        <span class="price-highlight">$<?= number_format($total_amount, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('customer_name').value.trim();
            const email = document.getElementById('customer_email').value.trim();
            const phone = document.getElementById('customer_phone').value.trim();
            const location = document.getElementById('pickup_location').value.trim();
            
            if (!name || !email || !phone || !location) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }
            
            // Phone validation (basic)
            const phoneRegex = /^[\d\s\-\+$$$$]+$/;
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                alert('Please enter a valid phone number');
                return;
            }
        });
    </script>
</body>
</html>
