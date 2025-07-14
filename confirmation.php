<?php
require_once 'db.php';

$booking_reference = $_GET['ref'] ?? '';

if (!$booking_reference) {
    header('Location: index.php');
    exit;
}

// Get booking details
$stmt = $pdo->prepare("
    SELECT b.*, c.brand, c.model, c.year, c.image_url, c.car_type, c.fuel_type, c.transmission, c.seats
    FROM bookings b 
    JOIN cars c ON b.car_id = c.id 
    WHERE b.booking_reference = ?
");
$stmt->execute([$booking_reference]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - RentACar</title>
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
            max-width: 800px;
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
        .confirmation-container {
            padding: 3rem 0;
        }

        .success-card {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 2rem;
        }

        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }

        .success-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .success-message {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
        }

        .booking-ref {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.3rem;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 2rem;
        }

        /* Booking Details */
        .booking-details {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .details-title {
            font-size: 1.8rem;
            margin-bottom: 2rem;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        .car-info {
            text-align: center;
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
            margin-bottom: 1rem;
            color: #333;
        }

        .car-specs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            color: #666;
            font-size: 0.9rem;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }

        .booking-info {
            display: grid;
            gap: 1rem;
        }

        .info-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
        }

        .info-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #333;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            color: #666;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-row.highlight {
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
            border-top: 1px solid #ddd;
            padding-top: 0.5rem;
            margin-top: 1rem;
        }

        .price-highlight {
            color: #667eea;
            font-weight: bold;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: transform 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        /* Important Notice */
        .notice-card {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .notice-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 0.5rem;
        }

        .notice-text {
            color: #856404;
            margin: 0;
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

            .details-grid {
                grid-template-columns: 1fr;
            }

            .car-specs {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .success-title {
                font-size: 2rem;
            }

            .booking-ref {
                font-size: 1.1rem;
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
        <div class="confirmation-container">
            <!-- Success Message -->
            <div class="success-card">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="success-title">Booking Confirmed!</h1>
                <p class="success-message">
                    Thank you for choosing RentACar. Your reservation has been successfully confirmed.
                </p>
                <div class="booking-ref">
                    Booking Reference: <?= htmlspecialchars($booking['booking_reference']) ?>
                </div>
                <p style="color: #666; font-size: 0.9rem;">
                    Please save this reference number for your records
                </p>
            </div>

            <!-- Important Notice -->
            <div class="notice-card">
                <div class="notice-title">
                    <i class="fas fa-exclamation-triangle"></i> Important Information
                </div>
                <p class="notice-text">
                    Please bring a valid driver's license and credit card for pickup. 
                    Arrive 15 minutes early for vehicle inspection and paperwork.
                </p>
            </div>

            <!-- Booking Details -->
            <div class="booking-details">
                <h2 class="details-title">Booking Details</h2>
                
                <div class="details-grid">
                    <div class="car-info">
                        <img src="<?= htmlspecialchars($booking['image_url']) ?>" 
                             alt="<?= htmlspecialchars($booking['brand'] . ' ' . $booking['model']) ?>" 
                             class="car-image">
                        
                        <h3 class="car-title">
                            <?= htmlspecialchars($booking['brand'] . ' ' . $booking['model'] . ' ' . $booking['year']) ?>
                        </h3>
                        
                        <div class="car-specs">
                            <div class="spec-item">
                                <i class="fas fa-car"></i>
                                <span><?= htmlspecialchars($booking['car_type']) ?></span>
                            </div>
                            <div class="spec-item">
                                <i class="fas fa-gas-pump"></i>
                                <span><?= htmlspecialchars($booking['fuel_type']) ?></span>
                            </div>
                            <div class="spec-item">
                                <i class="fas fa-cog"></i>
                                <span><?= htmlspecialchars($booking['transmission']) ?></span>
                            </div>
                            <div class="spec-item">
                                <i class="fas fa-users"></i>
                                <span><?= htmlspecialchars($booking['seats']) ?> seats</span>
                            </div>
                        </div>
                    </div>

                    <div class="booking-info">
                        <div class="info-section">
                            <h4 class="info-title">Customer Information</h4>
                            <div class="info-row">
                                <span><i class="fas fa-user"></i> Name:</span>
                                <span><?= htmlspecialchars($booking['customer_name']) ?></span>
                            </div>
                            <div class="info-row">
                                <span><i class="fas fa-envelope"></i> Email:</span>
                                <span><?= htmlspecialchars($booking['customer_email']) ?></span>
                            </div>
                            <div class="info-row">
                                <span><i class="fas fa-phone"></i> Phone:</span>
                                <span><?= htmlspecialchars($booking['customer_phone']) ?></span>
                            </div>
                        </div>

                        <div class="info-section">
                            <h4 class="info-title">Rental Information</h4>
                            <div class="info-row">
                                <span><i class="fas fa-map-marker-alt"></i> Pickup Location:</span>
                                <span><?= htmlspecialchars($booking['pickup_location']) ?></span>
                            </div>
                            <div class="info-row">
                                <span><i class="fas fa-calendar-alt"></i> Pickup Date:</span>
                                <span><?= date('M j, Y', strtotime($booking['pickup_date'])) ?></span>
                            </div>
                            <div class="info-row">
                                <span><i class="fas fa-calendar-alt"></i> Return Date:</span>
                                <span><?= date('M j, Y', strtotime($booking['return_date'])) ?></span>
                            </div>
                            <div class="info-row">
                                <span><i class="fas fa-clock"></i> Duration:</span>
                                <span><?= $booking['total_days'] ?> day<?= $booking['total_days'] > 1 ? 's' : '' ?></span>
                            </div>
                        </div>

                        <div class="info-section">
                            <h4 class="info-title">Payment Summary</h4>
                            <div class="info-row">
                                <span>Daily Rate:</span>
                                <span class="price-highlight">$<?= number_format($booking['total_amount'] / $booking['total_days'], 2) ?></span>
                            </div>
                            <div class="info-row">
                                <span>Number of Days:</span>
                                <span><?= $booking['total_days'] ?></span>
                            </div>
                            <div class="info-row highlight">
                                <span>Total Amount:</span>
                                <span class="price-highlight">$<?= number_format($booking['total_amount'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <a href="cars.php" class="btn btn-secondary">
                    <i class="fas fa-car"></i> Browse More Cars
                </a>
                <button onclick="window.print()" class="btn btn-success">
                    <i class="fas fa-print"></i> Print Confirmation
                </button>
            </div>
        </div>
    </div>

    <script>
        // Auto-scroll to top on page load
        window.addEventListener('load', function() {
            window.scrollTo(0, 0);
        });

        // Add some celebration animation
        document.addEventListener('DOMContentLoaded', function() {
            const successIcon = document.querySelector('.success-icon');
            successIcon.style.animation = 'bounce 1s ease-in-out';
            
            // Add CSS animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes bounce {
                    0%, 20%, 60%, 100% {
                        transform: translateY(0);
                    }
                    40% {
                        transform: translateY(-20px);
                    }
                    80% {
                        transform: translateY(-10px);
                    }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>
