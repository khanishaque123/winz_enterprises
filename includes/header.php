<?php
session_start();
// Check if user is logged in
$currentUser = null;
if (isset($_SESSION['user'])) {
    $currentUser = $_SESSION['user'];
}
?>
<!-- Header Section -->
<header class="main-header" id="mainHeader">
    <div class="nav-container">
        <a href="index.php" class="logo"> 
            <img src="uploads/logo.jpeg" alt="WInz Enterprises Logo" class="logo-img">
        </a>
        
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="product_page.php">Products</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
        
        <div class="nav-actions">
            <a href="cart.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count" id="cartCount">0</span>
            </a>
            
            <!-- User Authentication Section -->
            <div id="userAuthSection">
                <!-- Show when user is not logged in -->
                <div id="guestUser">
                    <button class="btn-login" id="openAuthModal">Login / Sign Up</button>
                </div>
                
                <!-- Show when user is logged in -->
                <div id="loggedInUser" style="display: none;">
                    <div class="user-dropdown">
                        <button class="user-dropdown-btn">
                            <i class="fas fa-user"></i> 
                            <span id="userNameDisplay">User</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown-content">
                            <a href="#" id="userProfileLink"><i class="fas fa-user-circle"></i> My Profile</a>
                            <a href="#" id="userOrdersLink"><i class="fas fa-shopping-bag"></i> My Orders</a>
                            <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                            <a href="admin/index.php" class="admin-dashboard-link"><i class="fas fa-cog"></i> Admin Dashboard</a>
                            <?php endif; ?>
                            <a href="#" id="userLogoutLink"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
    <button class="mobile-menu-close" id="mobileMenuClose">
        <i class="fas fa-times"></i>
    </button>
    <ul class="mobile-nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="product_page.php">Products</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
    </ul>
    <div class="mobile-nav-actions">
        <a href="cart.php" class="cart-icon">
            <i class="fas fa-shopping-cart"></i> Cart
            <span class="cart-count" id="mobileCartCount">0</span>
        </a>
        
        <!-- Mobile User Authentication -->
        <div id="mobileUserAuth">
            <div id="mobileGuestUser">
                <button class="btn-login" id="mobileOpenAuthModal" style="width: 100%;">Login / Sign Up</button>
            </div>
            <div id="mobileLoggedInUser" style="display: none;">
                <a href="#" class="btn-login" id="mobileUserProfileLink" style="width: 100%;">
                    <i class="fas fa-user"></i> <span id="mobileUserNameDisplay">User</span>
                </a>
                <a href="#" class="btn-login" id="mobileUserOrdersLink" style="width: 100%; margin-top: 10px;">
                    <i class="fas fa-shopping-bag"></i> My Orders
                </a>
                <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                <a href="admin/index.php" class="btn-login mobile-admin-dashboard-link" style="width: 100%; margin-top: 10px;">
                    <i class="fas fa-cog"></i> Admin Dashboard
                </a>
                <?php endif; ?>
                <a href="#" class="btn-login" id="mobileUserLogoutLink" style="width: 100%; margin-top: 10px;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Authentication Modal -->
<div id="authModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="authModalTitle">Login to Your Account</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="form-tabs">
                <div class="form-tab active" id="loginTab">Login</div>
                <div class="form-tab" id="signupTab">Sign Up</div>
            </div>
            
            <!-- Login Form -->
            <div id="loginForm" class="form-content active">
                <div class="user-role-selector">
                    <div class="role-option active" data-role="customer">
                        <i class="fas fa-user"></i>
                        <span>Customer</span>
                    </div>
                    <div class="role-option" data-role="admin">
                        <i class="fas fa-cog"></i>
                        <span>Admin</span>
                    </div>
                </div>
                
                <!-- Customer Login Form -->
                <div id="customerLoginForm">
                    <form id="customerLoginFormElement">
                        <div class="form-group">
                            <label for="loginEmail">Email Address</label>
                            <input type="email" id="loginEmail" class="form-control" placeholder="Enter your email" required>
                        </div>
                        <button type="button" id="sendOtpBtn" class="btn-primary">Send OTP</button>
                        
                        <div id="otpSection" style="display: none;">
                            <div class="form-group">
                                <label for="loginOtp">Enter OTP</label>
                                <div class="otp-container">
                                    <input type="text" maxlength="1" class="otp-input" id="otp1">
                                    <input type="text" maxlength="1" class="otp-input" id="otp2">
                                    <input type="text" maxlength="1" class="otp-input" id="otp3">
                                    <input type="text" maxlength="1" class="otp-input" id="otp4">
                                    <input type="text" maxlength="1" class="otp-input" id="otp5">
                                    <input type="text" maxlength="1" class="otp-input" id="otp6">
                                </div>
                                <input type="hidden" id="loginOtp">
                            </div>
                            <div class="resend-otp">
                                <a id="resendOtpLink">Resend OTP</a>
                                <div class="otp-timer" id="otpTimer">02:00</div>
                            </div>
                            <button type="submit" class="btn-primary">Verify & Login</button>
                        </div>
                    </form>
                </div>
                
                <!-- Admin Login Form -->
                <div id="adminLoginForm" style="display: none;">
                    <form id="adminLoginFormElement">
                        <div class="form-group">
                            <label for="adminUsername">Username</label>
                            <input type="text" id="adminUsername" class="form-control" placeholder="Enter admin username" required>
                        </div>
                        <div class="form-group">
                            <label for="adminPassword">Password</label>
                            <input type="password" id="adminPassword" class="form-control" placeholder="Enter admin password" required>
                        </div>
                        <button type="submit" class="btn-primary">Login as Admin</button>
                    </form>
                </div>
                
                <div class="form-footer">
                    <p>Don't have an account? <a href="#" id="switchToSignup">Sign up here</a></p>
                    <p><small>Demo Admin: admin / admin123</small></p>
                </div>
            </div>
            
            <!-- Signup Form -->
            <div id="signupForm" class="form-content">
                <form id="signupFormElement">
                    <div class="form-group">
                        <label for="signupName">Full Name</label>
                        <input type="text" id="signupName" class="form-control" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label for="signupEmail">Email Address</label>
                        <input type="email" id="signupEmail" class="form-control" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="signupMobile">Mobile Number</label>
                        <input type="tel" id="signupMobile" class="form-control" placeholder="Enter your mobile number" required>
                    </div>
                    
                    <div class="address-fields" id="addressFields">
                        <div class="form-group">
                            <label for="signupAddress">Complete Address</label>
                            <textarea id="signupAddress" class="form-control" placeholder="Enter your complete address" rows="3" required></textarea>
                            <p class="form-note">Please provide your complete address including street, city, state, and zip code</p>
                        </div>
                    </div>
                    
                    <button type="button" id="sendSignupOtpBtn" class="btn-primary">Send OTP</button>
                    
                    <div id="signupOtpSection" style="display: none;">
                        <div class="form-group">
                            <label for="signupOtp">Enter OTP</label>
                            <div class="otp-container">
                                <input type="text" maxlength="1" class="otp-input" id="signupOtp1">
                                <input type="text" maxlength="1" class="otp-input" id="signupOtp2">
                                <input type="text" maxlength="1" class="otp-input" id="signupOtp3">
                                <input type="text" maxlength="1" class="otp-input" id="signupOtp4">
                                <input type="text" maxlength="1" class="otp-input" id="signupOtp5">
                                <input type="text" maxlength="1" class="otp-input" id="signupOtp6">
                            </div>
                            <input type="hidden" id="signupOtp">
                        </div>
                        <div class="resend-otp">
                            <a id="resendSignupOtpLink">Resend OTP</a>
                            <div class="otp-timer" id="signupOtpTimer">02:00</div>
                        </div>
                        <button type="submit" class="btn-primary">Verify & Create Account</button>
                    </div>
                </form>
                <div class="form-footer">
                    <p>Already have an account? <a href="#" id="switchToLogin">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>