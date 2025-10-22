// assets/js/header.js - Complete User Authentication with OTP
class UserAuth {
    constructor() {
        this.currentUser = null;
        this.apiBase = 'includes';
        this.init();
    }
    
    async init() {
        await this.checkAuthStatus();
        this.updateUI();
        this.setupEventListeners();
        this.setupOtpInputs();
    }
    
    async checkAuthStatus() {
        try {
            const response = await fetch(`${this.apiBase}/check_auth.php`);
            const data = await response.json();
            
            if (data.success) {
                this.currentUser = data.data.user;
                localStorage.setItem('winzUser', JSON.stringify(data.data.user));
                
                if (data.data.user.role === 'admin') {
                    this.addAdminLink();
                }
            } else {
                this.currentUser = null;
                localStorage.removeItem('winzUser');
            }
        } catch (error) {
            console.error('Error checking auth status:', error);
            this.loadFromLocalStorage();
        }
    }
    
    loadFromLocalStorage() {
        const userData = localStorage.getItem('winzUser');
        if (userData) {
            this.currentUser = JSON.parse(userData);
        }
    }
    
    addAdminLink() {
        const userDropdownContent = document.querySelector('.user-dropdown-content');
        const mobileUserActions = document.getElementById('mobileLoggedInUser');
        
        if (userDropdownContent && !userDropdownContent.querySelector('.admin-dashboard-link')) {
            const adminLink = document.createElement('a');
            adminLink.href = 'admin/index.php';
            adminLink.className = 'admin-dashboard-link';
            adminLink.innerHTML = '<i class="fas fa-cog"></i> Admin Dashboard';
            userDropdownContent.insertBefore(adminLink, userDropdownContent.firstChild);
        }
        
        if (mobileUserActions && !mobileUserActions.querySelector('.mobile-admin-dashboard-link')) {
            const mobileAdminLink = document.createElement('a');
            mobileAdminLink.href = 'admin/index.php';
            mobileAdminLink.className = 'btn-login mobile-admin-dashboard-link';
            mobileAdminLink.style.width = '100%';
            mobileAdminLink.style.marginTop = '10px';
            mobileAdminLink.innerHTML = '<i class="fas fa-cog"></i> Admin Dashboard';
            mobileUserActions.insertBefore(mobileAdminLink, mobileUserActions.firstChild);
        }
    }
    
    updateUI() {
        const guestUser = document.getElementById('guestUser');
        const loggedInUser = document.getElementById('loggedInUser');
        const userNameDisplay = document.getElementById('userNameDisplay');
        const mobileGuestUser = document.getElementById('mobileGuestUser');
        const mobileLoggedInUser = document.getElementById('mobileLoggedInUser');
        const mobileUserNameDisplay = document.getElementById('mobileUserNameDisplay');
        
        if (this.currentUser) {
            if (guestUser) guestUser.style.display = 'none';
            if (loggedInUser) loggedInUser.style.display = 'block';
            if (mobileGuestUser) mobileGuestUser.style.display = 'none';
            if (mobileLoggedInUser) mobileLoggedInUser.style.display = 'block';
            
            const displayName = this.currentUser.name.split(' ')[0];
            if (userNameDisplay) userNameDisplay.textContent = displayName;
            if (mobileUserNameDisplay) mobileUserNameDisplay.textContent = displayName;
            
            if (this.currentUser.role === 'admin') {
                this.addAdminLink();
            }
        } else {
            if (guestUser) guestUser.style.display = 'block';
            if (loggedInUser) loggedInUser.style.display = 'none';
            if (mobileGuestUser) mobileGuestUser.style.display = 'block';
            if (mobileLoggedInUser) mobileLoggedInUser.style.display = 'none';
        }
    }
    
    setupOtpInputs() {
        const setupOtpGroup = (prefix) => {
            const inputs = [];
            for (let i = 1; i <= 6; i++) {
                const input = document.getElementById(`${prefix}${i}`);
                if (input) inputs.push(input);
            }
            
            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    // Auto-focus next input
                    if (e.target.value && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                    
                    // Update hidden OTP field
                    this.updateOtpValue(prefix);
                });
                
                input.addEventListener('keydown', (e) => {
                    // Handle backspace
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                    
                    // Handle paste
                    if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
                        setTimeout(() => {
                            this.handlePaste(e.target, inputs, prefix);
                        }, 0);
                    }
                });
                
                // Prevent non-numeric input
                input.addEventListener('keypress', (e) => {
                    if (!/^\d$/.test(e.key)) {
                        e.preventDefault();
                    }
                });
            });
        };
        
        setupOtpGroup('otp'); // For login
        setupOtpGroup('signupOtp'); // For signup
    }
    
    handlePaste(pastedElement, inputs, prefix) {
        const pasteData = pastedElement.value;
        if (pasteData.length === 6 && /^\d+$/.test(pasteData)) {
            // Split pasted data into individual inputs
            for (let i = 0; i < 6; i++) {
                if (inputs[i]) {
                    inputs[i].value = pasteData[i];
                }
            }
            // Update the hidden field
            this.updateOtpValue(prefix);
            // Focus the last input
            if (inputs[5]) inputs[5].focus();
        }
    }

    updateOtpValue(prefix) {
        let otpValue = '';
        for (let i = 1; i <= 6; i++) {
            const input = document.getElementById(`${prefix}${i}`);
            if (input) otpValue += input.value;
        }
        
        const hiddenOtp = document.getElementById(prefix === 'signupOtp' ? 'signupOtp' : 'loginOtp');
        if (hiddenOtp) hiddenOtp.value = otpValue;
    }
    
    setupEventListeners() {
        // Modal open/close
        const openAuthModal = document.getElementById('openAuthModal');
        const mobileOpenAuthModal = document.getElementById('mobileOpenAuthModal');
        const closeModal = document.querySelector('.close-modal');
        const authModal = document.getElementById('authModal');
        
        if (openAuthModal) {
            openAuthModal.addEventListener('click', () => {
                authModal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            });
        }
        
        if (mobileOpenAuthModal) {
            mobileOpenAuthModal.addEventListener('click', () => {
                authModal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                const mobileMenu = document.getElementById('mobileMenu');
                if (mobileMenu) mobileMenu.classList.remove('active');
            });
        }
        
        if (closeModal) {
            closeModal.addEventListener('click', () => {
                authModal.style.display = 'none';
                document.body.style.overflow = '';
                this.resetForms();
            });
        }
        
        window.addEventListener('click', (e) => {
            if (e.target === authModal) {
                authModal.style.display = 'none';
                document.body.style.overflow = '';
                this.resetForms();
            }
        });
        
        // Tab switching
        const loginTab = document.getElementById('loginTab');
        const signupTab = document.getElementById('signupTab');
        const loginForm = document.getElementById('loginForm');
        const signupForm = document.getElementById('signupForm');
        const switchToSignup = document.getElementById('switchToSignup');
        const switchToLogin = document.getElementById('switchToLogin');
        const authModalTitle = document.getElementById('authModalTitle');
        
        if (loginTab && signupTab && loginForm && signupForm) {
            loginTab.addEventListener('click', () => {
                loginTab.classList.add('active');
                signupTab.classList.remove('active');
                loginForm.classList.add('active');
                signupForm.classList.remove('active');
                if (authModalTitle) authModalTitle.textContent = 'Login to Your Account';
                this.resetForms();
            });
            
            signupTab.addEventListener('click', () => {
                signupTab.classList.add('active');
                loginTab.classList.remove('active');
                signupForm.classList.add('active');
                loginForm.classList.remove('active');
                if (authModalTitle) authModalTitle.textContent = 'Create Your Account';
                this.resetForms();
            });
        }
        
        if (switchToSignup) {
            switchToSignup.addEventListener('click', (e) => {
                e.preventDefault();
                if (signupTab) signupTab.click();
            });
        }
        
        if (switchToLogin) {
            switchToLogin.addEventListener('click', (e) => {
                e.preventDefault();
                if (loginTab) loginTab.click();
            });
        }
        
        // User role selection
        const roleOptions = document.querySelectorAll('.role-option');
        roleOptions.forEach(option => {
            option.addEventListener('click', () => {
                roleOptions.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
                
                const role = option.getAttribute('data-role');
                const customerLoginForm = document.getElementById('customerLoginForm');
                const adminLoginForm = document.getElementById('adminLoginForm');
                
                if (role === 'admin') {
                    if (customerLoginForm) customerLoginForm.style.display = 'none';
                    if (adminLoginForm) adminLoginForm.style.display = 'block';
                } else {
                    if (customerLoginForm) customerLoginForm.style.display = 'block';
                    if (adminLoginForm) adminLoginForm.style.display = 'none';
                }
            });
        });
        
        // OTP sending
        const sendOtpBtn = document.getElementById('sendOtpBtn');
        const sendSignupOtpBtn = document.getElementById('sendSignupOtpBtn');
        
        if (sendOtpBtn) {
            sendOtpBtn.addEventListener('click', () => {
                this.handleSendOtp('login');
            });
        }
        
        if (sendSignupOtpBtn) {
            sendSignupOtpBtn.addEventListener('click', () => {
                this.handleSendOtp('signup');
            });
        }
        
        // OTP resend
        const resendOtpLink = document.getElementById('resendOtpLink');
        const resendSignupOtpLink = document.getElementById('resendSignupOtpLink');
        
        if (resendOtpLink) {
            resendOtpLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSendOtp('login');
            });
        }
        
        if (resendSignupOtpLink) {
            resendSignupOtpLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSendOtp('signup');
            });
        }
        
        // Form submissions
        const customerLoginFormElement = document.getElementById('customerLoginFormElement');
        const adminLoginFormElement = document.getElementById('adminLoginFormElement');
        const signupFormElement = document.getElementById('signupFormElement');
        
        if (customerLoginFormElement) {
            customerLoginFormElement.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleCustomerLogin();
            });
        }
        
        if (adminLoginFormElement) {
            adminLoginFormElement.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleAdminLogin();
            });
        }
        
        if (signupFormElement) {
            signupFormElement.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleSignup();
            });
        }
        
        // Logout
        const userLogoutLink = document.getElementById('userLogoutLink');
        const mobileUserLogoutLink = document.getElementById('mobileUserLogoutLink');
        
        if (userLogoutLink) {
            userLogoutLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        }
        
        if (mobileUserLogoutLink) {
            mobileUserLogoutLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        }
    }
    
    resetForms() {
        const loginEmail = document.getElementById('loginEmail');
        const adminUsername = document.getElementById('adminUsername');
        const adminPassword = document.getElementById('adminPassword');
        const signupName = document.getElementById('signupName');
        const signupEmail = document.getElementById('signupEmail');
        const signupMobile = document.getElementById('signupMobile');
        const signupAddress = document.getElementById('signupAddress');
        
        if (loginEmail) loginEmail.value = '';
        if (adminUsername) adminUsername.value = '';
        if (adminPassword) adminPassword.value = '';
        if (signupName) signupName.value = '';
        if (signupEmail) signupEmail.value = '';
        if (signupMobile) signupMobile.value = '';
        if (signupAddress) signupAddress.value = '';
        
        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach(input => input.value = '');
        
        const otpSection = document.getElementById('otpSection');
        const signupOtpSection = document.getElementById('signupOtpSection');
        const addressFields = document.getElementById('addressFields');
        
        if (otpSection) otpSection.style.display = 'none';
        if (signupOtpSection) signupOtpSection.style.display = 'none';
        if (addressFields) addressFields.style.display = 'block';
        
        document.querySelectorAll('.role-option').forEach(opt => {
            if (opt.getAttribute('data-role') === 'customer') {
                opt.classList.add('active');
            } else {
                opt.classList.remove('active');
            }
        });
        
        const customerLoginForm = document.getElementById('customerLoginForm');
        const adminLoginForm = document.getElementById('adminLoginForm');
        
        if (customerLoginForm) customerLoginForm.style.display = 'block';
        if (adminLoginForm) adminLoginForm.style.display = 'none';
    }
    
    async handleSendOtp(type) {
        let email, name, mobile, address;
        
        if (type === 'login') {
            email = document.getElementById('loginEmail').value;
            
            if (!email) {
                this.showNotification('Please enter your email address', 'error');
                return;
            }
            
            if (!this.validateEmail(email)) {
                this.showNotification('Please enter a valid email address', 'error');
                return;
            }
        } else if (type === 'signup') {
            email = document.getElementById('signupEmail').value;
            name = document.getElementById('signupName').value;
            mobile = document.getElementById('signupMobile').value;
            address = document.getElementById('signupAddress').value;
            
            if (!email || !name || !mobile || !address) {
                this.showNotification('Please fill in all required fields', 'error');
                return;
            }
            
            if (!this.validateEmail(email)) {
                this.showNotification('Please enter a valid email address', 'error');
                return;
            }
            
            if (!/^[0-9]{10}$/.test(mobile)) {
                this.showNotification('Please enter a valid 10-digit mobile number', 'error');
                return;
            }
        }
        
        try {
            // Show loading state
            const sendButton = type === 'login' ? 
                document.getElementById('sendOtpBtn') : 
                document.getElementById('sendSignupOtpBtn');
            const originalText = sendButton.innerHTML;
            sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            sendButton.disabled = true;

            const payload = { email: email, type: type };
            if (type === 'signup') {
                payload.name = name;
                payload.mobile = mobile;
                payload.address = address;
            }
            
            const response = await fetch(`${this.apiBase}/send_otp.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            
            const data = await response.json();
            
            // Restore button state
            sendButton.innerHTML = originalText;
            sendButton.disabled = false;
            
            if (data.success) {
                this.showNotification('OTP sent to your email!', 'success');
                
              
                
                if (type === 'login') {
                    document.getElementById('otpSection').style.display = 'block';
                    this.startOtpTimer('otpTimer', 'resendOtpLink');
                } else {
                    document.getElementById('signupOtpSection').style.display = 'block';
                    document.getElementById('addressFields').style.display = 'none';
                    this.startOtpTimer('signupOtpTimer', 'resendSignupOtpLink');
                }
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Error sending OTP:', error);
            this.showNotification('Failed to send OTP. Please try again.', 'error');
            
            // Restore button state on error
            const sendButton = type === 'login' ? 
                document.getElementById('sendOtpBtn') : 
                document.getElementById('sendSignupOtpBtn');
            sendButton.innerHTML = type === 'login' ? 'Send OTP' : 'Send Verification OTP';
            sendButton.disabled = false;
        }
    }

    startOtpTimer(timerId, resendLinkId) {
        let timeLeft = 300; // 5 minutes in seconds
        const timerElement = document.getElementById(timerId);
        const resendLink = document.getElementById(resendLinkId);
        
        if (resendLink) {
            resendLink.style.display = 'none';
        }
        
        const timer = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(timer);
                if (timerElement) {
                    timerElement.textContent = 'OTP expired';
                    timerElement.style.color = '#f5576c';
                }
                if (resendLink) {
                    resendLink.style.display = 'inline';
                }
            } else {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                if (timerElement) {
                    timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    timerElement.style.color = 'var(--text-light)';
                }
                timeLeft--;
            }
        }, 1000);
        
        // Store timer reference for cleanup if needed
        this.currentTimer = timer;
    }

    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    async handleCustomerLogin() {
        const email = document.getElementById('loginEmail').value;
        const otp = document.getElementById('loginOtp').value;
        
        if (!email) {
            this.showNotification('Please enter your email', 'error');
            return;
        }
        
        if (!otp || otp.length !== 6) {
            this.showNotification('Please enter a valid 6-digit OTP', 'error');
            return;
        }
        
        try {
            const submitButton = document.querySelector('#customerLoginFormElement button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
            submitButton.disabled = true;

            const response = await fetch(`${this.apiBase}/verify_otp.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: email,
                    otp: otp,
                    type: 'login'
                })
            });
            
            const data = await response.json();
            
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
            
            if (data.success) {
                this.currentUser = data.data.user;
                localStorage.setItem('winzUser', JSON.stringify(data.data.user));
                this.updateUI();
                this.showNotification(`Welcome back, ${data.data.user.name.split(' ')[0]}!`, 'success');
                
                document.getElementById('authModal').style.display = 'none';
                document.body.style.overflow = '';
                this.resetForms();
                
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showNotification('Login failed. Please try again.', 'error');
        }
    }
    
    async handleAdminLogin() {
        const username = document.getElementById('adminUsername').value;
        const password = document.getElementById('adminPassword').value;
        
        if (!username || !password) {
            this.showNotification('Please enter both username and password', 'error');
            return;
        }
        
        try {
            const submitButton = document.querySelector('#adminLoginFormElement button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            submitButton.disabled = true;

            const response = await fetch(`${this.apiBase}/admin_login.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            });
            
            const data = await response.json();
            
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
            
            if (data.success) {
                this.currentUser = data.data.user;
                localStorage.setItem('winzUser', JSON.stringify(data.data.user));
                this.updateUI();
                this.showNotification(`Welcome, ${data.data.user.name}!`, 'success');
                
                document.getElementById('authModal').style.display = 'none';
                document.body.style.overflow = '';
                this.resetForms();
                
                setTimeout(() => {
                    window.location.href = 'admin/index.php';
                }, 1000);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Admin login error:', error);
            this.showNotification('Admin login failed. Please try again.', 'error');
        }
    }
    
    async handleSignup() {
        const name = document.getElementById('signupName').value;
        const email = document.getElementById('signupEmail').value;
        const mobile = document.getElementById('signupMobile').value;
        const address = document.getElementById('signupAddress').value;
        const otp = document.getElementById('signupOtp').value;
        
        if (!name || !email || !mobile || !address) {
            this.showNotification('Please fill in all fields', 'error');
            return;
        }
        
        if (!otp || otp.length !== 6) {
            this.showNotification('Please enter a valid 6-digit OTP', 'error');
            return;
        }
        
        if (!/^[0-9]{10}$/.test(mobile)) {
            this.showNotification('Please enter a valid 10-digit mobile number', 'error');
            return;
        }
        
        try {
            const submitButton = document.querySelector('#signupFormElement button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
            submitButton.disabled = true;

            const response = await fetch(`${this.apiBase}/verify_otp.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: email,
                    otp: otp,
                    type: 'signup',
                    name: name,
                    mobile: mobile,
                    address: address
                })
            });
            
            const data = await response.json();
            
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
            
            if (data.success) {
                this.currentUser = data.data.user;
                localStorage.setItem('winzUser', JSON.stringify(data.data.user));
                this.updateUI();
                this.showNotification(`Welcome to WInz Enterprises, ${data.data.user.name.split(' ')[0]}!`, 'success');
                
                document.getElementById('authModal').style.display = 'none';
                document.body.style.overflow = '';
                this.resetForms();
                
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Signup error:', error);
            this.showNotification('Signup failed. Please try again.', 'error');
        }
    }
    
    async logout() {
        try {
            // Show loading state
            this.showNotification('Logging out...', 'info');
            
            const response = await fetch(`${this.apiBase}/logout.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Clear all client-side data
                this.currentUser = null;
                localStorage.removeItem('winzUser');
                localStorage.removeItem('cart');
                
                // Update UI immediately
                this.updateUI();
                
                this.showNotification('You have been logged out successfully!', 'success');
                
                // Redirect to home page after short delay
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1500);
                
            } else {
                throw new Error(data.message || 'Logout failed');
            }
        } catch (error) {
            console.error('Logout error:', error);
            
            // Fallback: Clear client-side data even if server request fails
            this.currentUser = null;
            localStorage.removeItem('winzUser');
            localStorage.removeItem('cart');
            this.updateUI();
            
            this.showNotification('Logged out successfully', 'info');
            
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1500);
        }
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: ${type === 'error' ? '#f5576c' : type === 'success' ? '#4CAF50' : type === 'info' ? '#2196F3' : 'var(--gradient)'};
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 300px;
        `;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

// Initialize User Authentication
const userAuth = new UserAuth();

// Mobile menu functionality
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const mobileMenu = document.getElementById('mobileMenu');
const mobileMenuClose = document.getElementById('mobileMenuClose');

if (mobileMenuToggle && mobileMenu && mobileMenuClose) {
    mobileMenuToggle.addEventListener('click', () => {
        mobileMenu.classList.add('active');
        document.body.style.overflow = 'hidden';
    });
    
    mobileMenuClose.addEventListener('click', () => {
        mobileMenu.classList.remove('active');
        document.body.style.overflow = '';
    });
    
    const mobileNavLinks = mobileMenu.querySelectorAll('a');
    mobileNavLinks.forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
}

// Header scroll effect
window.addEventListener('scroll', () => {
    const header = document.getElementById('mainHeader');
    if (header) {
        if (window.scrollY > 100) {
            header.classList.add('header-scrolled');
        } else {
            header.classList.remove('header-scrolled');
        }
    }
});

// Cart functionality
let cart = JSON.parse(localStorage.getItem('cart')) || [];

function updateCartCount() {
    const cartCount = document.getElementById('cartCount');
    const mobileCartCount = document.getElementById('mobileCartCount');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    if (cartCount) cartCount.textContent = totalItems;
    if (mobileCartCount) mobileCartCount.textContent = totalItems;
}

function addToCart(product) {
    if (!userAuth.currentUser) {
        userAuth.showNotification('Please login to add items to cart', 'error');
        document.getElementById('openAuthModal').click();
        return;
    }
    
    const existingItem = cart.find(item => item.id === product.id);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            quantity: 1
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    userAuth.showNotification('Product added to cart!');
    
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenu) {
        mobileMenu.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});