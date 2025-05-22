<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FASCO - Create Account</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .signup-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
        }

        .left-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .model-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: relative;
            z-index: 0;
        }

        .right-panel {
            padding: 60px 50px;
            position: relative;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
        }

        .create-account-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
        }

        .social-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .social-btn {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            background: white;
            color: #6c757d;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .social-btn:hover {
            border-color: #007bff;
            color: #007bff;
            text-decoration: none;
        }

        .google-btn:hover {
            border-color: #db4437;
            color: #db4437;
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
            color: #6c757d;
            font-weight: 500;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
            z-index: 1;
        }

        .divider span {
            background: white;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-control {
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            background: white;
        }

        .input-group {
            position: relative;
        }

        .input-group .form-control {
            padding-right: 50px;
        }

        .input-group .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 3;
        }

        .create-btn {
            width: 100%;
            padding: 15px;
            background: #000;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .create-btn:hover {
            background: #333;
            transform: translateY(-2px);
        }

        .create-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .login-link {
            text-align: center;
            color: #6c757d;
        }

        .login-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .terms-link {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 12px;
            color: #6c757d;
            text-decoration: none;
        }

        .terms-link:hover {
            text-decoration: underline;
            color: #007bff;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .right-panel {
                padding: 40px 30px;
            }
            
            .social-buttons {
                flex-direction: column;
            }
            
            .logo {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="row g-0 h-100">
            <div class="col-lg-6 left-panel">
                <img src="/api/placeholder/500/700" alt="Fashion Model" class="model-image">
            </div>
            <div class="col-lg-6 right-panel">
                <div class="logo">FASCO</div>
                
                <h2 class="create-account-title">Create Account</h2>
                
                <!-- Social Login Buttons -->
                <div class="social-buttons">
                    <a href="#" class="social-btn google-btn" id="googleSignUp">
                        <i class="fab fa-google"></i>
                        Sign up with Google
                    </a>
                    <a href="#" class="social-btn" id="emailToggle">
                        <i class="fas fa-envelope"></i>
                        Sign up with Email
                    </a>
                </div>
                
                <div class="divider">
                    <span>— OR —</span>
                </div>
                
                <!-- Alert Messages -->
                <div id="alertContainer"></div>
                
                <!-- Sign Up Form -->
                <form id="signUpForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form-control" id="firstName" name="first_name" placeholder="First Name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Last Name" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email/Phone Toggle -->
                    <div class="form-group">
                        <div class="d-flex gap-2 mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="emailModeBtn">Email</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="phoneModeBtn">Phone</button>
                        </div>
                        <input type="email" class="form-control" id="emailAddress" name="email" placeholder="Email Address" required>
                        <input type="tel" class="form-control d-none" id="phoneNumber" name="phone" placeholder="Phone Number">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                    <button type="button" class="toggle-password" data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm Password" required>
                                    <button type="button" class="toggle-password" data-target="confirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="create-btn" id="createAccountBtn">
                        <span class="btn-text">Create Account</span>
                        <div class="loading-spinner"></div>
                    </button>
                </form>
                
                <div class="login-link">
                    Already have an account? <a href="/login">Login</a>
                </div>
                
                <a href="#" class="terms-link">FASCO Terms & Conditions</a>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // WordPress AJAX URL (set this in your PHP template)
        const ajaxUrl = window.location.origin + '/wp-admin/admin-ajax.php';
        
        // Form elements
        const signUpForm = document.getElementById('signUpForm');
        const emailModeBtn = document.getElementById('emailModeBtn');
        const phoneModeBtn = document.getElementById('phoneModeBtn');
        const emailInput = document.getElementById('emailAddress');
        const phoneInput = document.getElementById('phoneNumber');
        const createBtn = document.getElementById('createAccountBtn');
        const alertContainer = document.getElementById('alertContainer');
        
        // Toggle between email and phone mode
        emailModeBtn.addEventListener('click', function() {
            emailInput.classList.remove('d-none');
            phoneInput.classList.add('d-none');
            emailInput.required = true;
            phoneInput.required = false;
            this.classList.replace('btn-outline-primary', 'btn-primary');
            phoneModeBtn.classList.replace('btn-primary', 'btn-outline-secondary');
        });
        
        phoneModeBtn.addEventListener('click', function() {
            phoneInput.classList.remove('d-none');
            emailInput.classList.add('d-none');
            phoneInput.required = true;
            emailInput.required = false;
            this.classList.replace('btn-outline-secondary', 'btn-primary');
            emailModeBtn.classList.replace('btn-primary', 'btn-outline-primary');
        });
        
        // Password visibility toggle
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    targetInput.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });
        
        // Show alert function
        function showAlert(message, type = 'danger') {
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }
        
        // Validate form
        function validateForm(formData) {
            const password = formData.get('password');
            const confirmPassword = formData.get('confirm_password');
            const email = formData.get('email');
            const phone = formData.get('phone');
            
            // Check if either email or phone is provided
            if (!email && !phone) {
                showAlert('Please provide either email address or phone number.');
                return false;
            }
            
            // Validate email format
            if (email && !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                showAlert('Please enter a valid email address.');
                return false;
            }
            
            // Validate phone format (basic)
            if (phone && !phone.match(/^[\+]?[1-9][\d]{0,15}$/)) {
                showAlert('Please enter a valid phone number.');
                return false;
            }
            
            // Password validation
            if (password.length < 6) {
                showAlert('Password must be at least 6 characters long.');
                return false;
            }
            
            if (password !== confirmPassword) {
                showAlert('Passwords do not match.');
                return false;
            }
            
            return true;
        }
        
        // Handle form submission
        signUpForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Validate form
            if (!validateForm(formData)) {
                return;
            }
            
            // Show loading state
            createBtn.disabled = true;
            createBtn.querySelector('.btn-text').textContent = 'Creating Account...';
            createBtn.querySelector('.loading-spinner').style.display = 'inline-block';
            
            try {
                // Prepare data for WordPress AJAX
                const data = {
                    action: 'fasco_register_user',
                    nonce: fascoAjax.nonce, // Set in PHP
                    first_name: formData.get('first_name'),
                    last_name: formData.get('last_name'),
                    email: formData.get('email') || null,
                    phone: formData.get('phone') || null,
                    password: formData.get('password')
                };
                
                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('Account created successfully! Please check your email for verification.', 'success');
                    signUpForm.reset();
                    
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = result.data.redirect || '/login';
                    }, 2000);
                } else {
                    showAlert(result.data.message || 'Registration failed. Please try again.');
                }
                
            } catch (error) {
                console.error('Registration error:', error);
                showAlert('An error occurred. Please try again.');
            } finally {
                // Reset button state
                createBtn.disabled = false;
                createBtn.querySelector('.btn-text').textContent = 'Create Account';
                createBtn.querySelector('.loading-spinner').style.display = 'none';
            }
        });
        
        // Google Sign Up (placeholder)
        document.getElementById('googleSignUp').addEventListener('click', function(e) {
            e.preventDefault();
            // Implement Google OAuth integration
            showAlert('Google sign-up coming soon!', 'info');
        });
        
        // Email toggle button
        document.getElementById('emailToggle').addEventListener('click', function(e) {
            e.preventDefault();
            // Already showing email by default
        });
        
        // Form validation on input
        document.querySelectorAll('input[required]').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        });
    </script>

    <!-- WordPress Integration Script -->
    <script>
        // This will be populated by WordPress
        window.fascoAjax = window.fascoAjax || {
            nonce: '<?php echo wp_create_nonce("fasco_ajax_nonce"); ?>',
            ajaxUrl: '<?php echo admin_url("admin-ajax.php"); ?>'
        };
    </script>
</body>
</html>