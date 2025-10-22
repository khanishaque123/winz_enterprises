<?php 
session_start();
// Check if user is already logged in
$currentUser = null;
if (isset($_SESSION['user'])) {
    $currentUser = $_SESSION['user'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WInz Enterprises - Premium Products Delivered</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <style>
        /* Slider Styles */
        .slider-section {
            position: relative;
            overflow: hidden;
            height: 500px;
        }

        .slider {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .slides {
            display: flex;
            width: 400%; /* 4 slides */
            height: 100%;
            transition: transform 0.8s ease;
        }

        .slide {
            width: 25%; /* Each slide takes 25% of the container (1/4) */
            height: 100%;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
        }

        .slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Dark overlay for better text visibility */
        }

        .slide-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            padding: 0 20px;
        }

        .slide-content h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .slide-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .btn-slider {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            display: inline-block;
        }

        .btn-slider:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .slider-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .slider-dot.active {
            background: white;
        }

        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.5rem;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .slider-arrow:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .slider-arrow.prev {
            left: 20px;
        }

        .slider-arrow.next {
            right: 20px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .slider-section {
                height: 400px;
            }

            .slide-content h1 {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 480px) {
            .slider-section {
                height: 350px;
            }

            .slide-content h1 {
                font-size: 1.8rem;
            }

            .slide-content p {
                font-size: 1rem;
            }

            .slider-arrow {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Slider Section - Replaces Hero Section -->
    <section class="slider-section">
        <div class="slider">
            <div class="slides">
                <div class="slide active" style="background-image: url('https://images.unsplash.com/photo-1607082350899-7e105aa886ae?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
                    <div class="slide-content">
                        <h1>Premium Products, Exceptional Quality</h1>
                        <p>Discover our curated collection of premium products delivered right to your doorstep with exceptional service.</p>
                        <a href="#products" class="btn-slider">
                            <i class="fas fa-shopping-bag"></i> Shop Now
                        </a>
                    </div>
                </div>
                <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
                    <div class="slide-content">
                        <h1>New Arrivals</h1>
                        <p>Check out our latest products with innovative features and designs</p>
                        <a href="#products" class="btn-slider">
                            <i class="fas fa-star"></i> Explore
                        </a>
                    </div>
                </div>
                <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1556742044-3c52d6e88c62?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
                    <div class="slide-content">
                        <h1>Special Offers</h1>
                        <p>Limited time discounts on our best-selling products</p>
                        <a href="#products" class="btn-slider">
                            <i class="fas fa-tag"></i> View Deals
                        </a>
                    </div>
                </div>
                <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1556742111-a301076d9d18?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
                    <div class="slide-content">
                        <h1>Fast & Free Shipping</h1>
                        <p>Get your orders delivered quickly with our premium shipping service</p>
                        <a href="#products" class="btn-slider">
                            <i class="fas fa-shipping-fast"></i> Learn More
                        </a>
                    </div>
                </div>
            </div>
            <button class="slider-arrow prev">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="slider-arrow next">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="slider-controls">
                <div class="slider-dot active" data-slide="0"></div>
                <div class="slider-dot" data-slide="1"></div>
                <div class="slider-dot" data-slide="2"></div>
                <div class="slider-dot" data-slide="3"></div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose WInz Enterprises</h2>
                <p>We're committed to providing the best shopping experience with premium products and exceptional service.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3>Fast Delivery</h3>
                    <p>Get your orders delivered quickly with our reliable shipping partners across the country.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure Payment</h3>
                    <p>Your payment information is protected with industry-leading security measures.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Our customer support team is always ready to assist you with any questions or concerns.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-undo-alt"></i>
                    </div>
                    <h3>Easy Returns</h3>
                    <p>Not satisfied? We offer hassle-free returns within 30 days of purchase.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section" id="products">
        <div class="container">
            <div class="products-header">
                <div class="section-title">
                    <h2>Featured Products</h2>
                    <p>Discover our handpicked selection of premium products</p>
                </div>
                <a href="product_page.php" class="btn-secondary">View All Products</a>
            </div>
            <div class="products-grid" id="productList">
                <div class="loading">
                    <div class="loading-spinner"></div>
                    <p>Loading products...</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter">
        <div class="newsletter-content">
            <h2>Stay Updated</h2>
            <p>Subscribe to our newsletter for the latest products, exclusive offers, and promotions.</p>
            <form class="newsletter-form" id="newsletterForm">
                <input type="email" class="newsletter-input" placeholder="Enter your email address" required>
                <button type="submit" class="newsletter-btn">Subscribe</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>WInz Enterprises</h3>
                    <p>Your trusted partner for premium products and exceptional service.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="product_page.php">Products</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Customer Service</h3>
                    <ul class="footer-links">
                        <li><a href="shipping_policy.php">Shipping Policy</a></li>
                        <li><a href="returns-refunds.php">Returns & Refunds</a></li>
                        <li><a href="privacy_policy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Business Ave, City, State 12345</li>
                        <li><i class="fas fa-phone"></i> (555) 123-4567</li>
                        <li><i class="fas fa-envelope"></i> info@winzenterprises.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 WInz Enterprises. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/header.js"></script>
    <script src="assets/js/home.js"></script>
    
    <script>
        // Slider functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.slider-dot');
        const totalSlides = slides.length;
        const slideInterval = 5000; // 5 seconds

        function nextSlide() {
            // Remove active class from current slide and dot
            slides[currentSlide].classList.remove('active');
            dots[currentSlide].classList.remove('active');

            // Increment current slide
            currentSlide = (currentSlide + 1) % totalSlides;

            // Add active class to new current slide and dot
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');

            // Update the slides container transform
            document.querySelector('.slides').style.transform = `translateX(-${currentSlide * (100 / totalSlides)}%)`;
        }

        function prevSlide() {
            // Remove active class from current slide and dot
            slides[currentSlide].classList.remove('active');
            dots[currentSlide].classList.remove('active');

            // Decrement current slide
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;

            // Add active class to new current slide and dot
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');

            // Update the slides container transform
            document.querySelector('.slides').style.transform = `translateX(-${currentSlide * (100 / totalSlides)}%)`;
        }

        // Set up automatic sliding
        let slideTimer = setInterval(nextSlide, slideInterval);

        // Add event listeners to arrows
        document.querySelector('.slider-arrow.next').addEventListener('click', () => {
            clearInterval(slideTimer);
            nextSlide();
            slideTimer = setInterval(nextSlide, slideInterval);
        });

        document.querySelector('.slider-arrow.prev').addEventListener('click', () => {
            clearInterval(slideTimer);
            prevSlide();
            slideTimer = setInterval(nextSlide, slideInterval);
        });

        // Add event listeners to dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                clearInterval(slideTimer);

                // Remove active class from current slide and dot
                slides[currentSlide].classList.remove('active');
                dots[currentSlide].classList.remove('active');

                // Set current slide to the dot's index
                currentSlide = index;

                // Add active class to new current slide and dot
                slides[currentSlide].classList.add('active');
                dots[currentSlide].classList.add('active');

                // Update the slides container transform
                document.querySelector('.slides').style.transform = `translateX(-${currentSlide * (100 / totalSlides)}%)`;

                // Restart automatic sliding
                slideTimer = setInterval(nextSlide, slideInterval);
            });
        });

        // Pause slider on hover
        const slider = document.querySelector('.slider');
        slider.addEventListener('mouseenter', () => {
            clearInterval(slideTimer);
        });

        slider.addEventListener('mouseleave', () => {
            slideTimer = setInterval(nextSlide, slideInterval);
        });
    </script>
</body>
</html>