<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Go - Driver Registration</title>
    <link rel="stylesheet" href="css/driver-registration.css">
</head>

<body>

<div class="registration-container">

    <div class="registration-header">
        <h1>Campus Go</h1>
        <p>Driver Registration</p>
    </div>

    <?php if(isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <?php 
            foreach($_SESSION['errors'] as $error) {
                echo "<p>❌ $error</p>";
            }
            unset($_SESSION['errors']);
            ?>
        </div>
    <?php endif; ?>

    <form action="register_process.php" method="POST" enctype="multipart/form-data" id="registrationForm">

        <!-- DRIVER INFORMATION -->

        <h2>Driver Information</h2>

        <div class="form-group">
            <label for="id_number">ID Number *</label>
            <input 
                type="text" 
                id="id_number"
                name="id_number"
                placeholder="e.g. 9001011234089"
                maxlength="13"
                required
            >
            <small>13 digits (e.g. 9001011234089)</small>
            <span id="id_error" class="error-message"></span>
        </div>

        <div class="form-row">

            <div class="form-group">
                <label for="name">Name *</label>
                <input 
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter your name"
                    required
                >
                <span id="name_error" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="surname">Surname *</label>
                <input 
                    type="text"
                    id="surname"
                    name="surname"
                    placeholder="Enter your surname"
                    required
                >
                <span id="surname_error" class="error-message"></span>
            </div>

        </div>

        <div class="form-group">
            <label for="gender">Gender *</label>
            <select id="gender" name="gender" required>
                <option value="">Select gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="email">Email Address *</label>
            <input 
                type="email"
                id="email"
                name="email"
                placeholder="example@email.com"
                required
            >
            <span id="email_error" class="error-message"></span>
        </div>

        <div class="form-group">
            <label for="cell_number">Cellphone Number *</label>
            <input 
                type="tel"
                id="cell_number"
                name="cell_number"
                placeholder="e.g. 0712345678"
                maxlength="10"
                required
            >
            <small>10 digits (e.g. 0712345678)</small>
            <span id="cell_error" class="error-message"></span>
        </div>

        <div class="form-group">
            <label for="residence">Residence Address *</label>
            <textarea
                id="residence"
                name="residence"
                placeholder="Enter your residence address"
                rows="3"
                required
            ></textarea>
        </div>

        <!-- 🔐 PASSWORD SECTION -->
        <h2>🔐 Create Account Password</h2>

        <div class="form-group">
            <label for="password">Create Password *</label>
            <input 
                type="password"
                id="password"
                name="password"
                placeholder="Min 8 characters"
                minlength="8"
                required
            >
            <small>
                Password must contain:
                <ul>
                    <li>At least 8 characters</li>
                    <li>One uppercase letter (A-Z)</li>
                    <li>One lowercase letter (a-z)</li>
                    <li>One number (0-9)</li>
                    <li>One special character (!@#$%^&*)</li>
                </ul>
            </small>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password *</label>
            <input 
                type="password"
                id="confirm_password"
                name="confirm_password"
                placeholder="Re-enter your password"
                required
            >
            <span id="password_match_message"></span>
        </div>

        <!-- Password Strength Indicator -->
        <div class="password-strength">
            <div id="strength_bar"></div>
            <span id="strength_text"></span>
        </div>

        <!-- DRIVER PHOTO -->
        <div class="form-group">
            <label for="driver_photo">Driver's Picture *</label>
            <input 
                type="file"
                id="driver_photo"
                name="driver_photo"
                accept="image/*"
                required
            >
            <small>Allowed: JPG, PNG, JPEG (Max 5MB)</small>
        </div>

        <!-- AUTHENTICATION -->

        <h2>Authentication</h2>

        <div class="form-group">
            <label>Choose verification method *</label>
            <div class="verification-options">
                <label>
                    <input 
                        type="radio"
                        name="verification_method"
                        value="email"
                        required
                    >
                    Email
                </label>
                <label>
                    <input 
                        type="radio"
                        name="verification_method"
                        value="cellphone"
                    >
                    Cellphone
                </label>
            </div>
        </div>

        <!-- VEHICLE INFORMATION -->

        <h2>Vehicle Information</h2>

        <div class="form-group">
            <label for="vehicle_registration">
                Vehicle Registration Number *
            </label>
            <input 
                type="text"
                id="vehicle_registration"
                name="vehicle_registration"
                placeholder="e.g. CA 123-45"
                required
            >
            <span id="vehicle_reg_error" class="error-message"></span>
        </div>

        <div class="form-group">
            <label for="vehicle_model">Vehicle Model *</label>
            <input 
                type="text"
                id="vehicle_model"
                name="vehicle_model"
                placeholder="e.g. Toyota Corolla 2020"
                required
            >
            <span id="vehicle_model_error" class="error-message"></span>
        </div>

        <div class="form-group">
            <label for="vehicle_photo">Vehicle Picture *</label>
            <input 
                type="file"
                id="vehicle_photo"
                name="vehicle_photo"
                accept="image/*"
                required
            >
            <small>Allowed: JPG, PNG, JPEG (Max 5MB)</small>
        </div>

        <!-- SUBMIT -->

        <button type="submit" class="register-button">
            Register as Driver
        </button>

        <p class="login-link">Already registered? <a href="login.php">Login here</a></p>

    </form> 

</div>

<!-- ========================================== -->
<!-- COMPLETE JAVASCRIPT VALIDATION -->
<!-- ========================================== -->
<script>
    // Get form elements
    const form = document.getElementById('registrationForm');
    const idInput = document.getElementById('id_number');
    const nameInput = document.getElementById('name');
    const surnameInput = document.getElementById('surname');
    const emailInput = document.getElementById('email');
    const cellInput = document.getElementById('cell_number');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const vehicleRegInput = document.getElementById('vehicle_registration');
    const vehicleModelInput = document.getElementById('vehicle_model');

    // Error message elements
    const idError = document.getElementById('id_error');
    const nameError = document.getElementById('name_error');
    const surnameError = document.getElementById('surname_error');
    const emailError = document.getElementById('email_error');
    const cellError = document.getElementById('cell_error');
    const vehicleRegError = document.getElementById('vehicle_reg_error');
    const vehicleModelError = document.getElementById('vehicle_model_error');

    // ============================================
    // 1. ID NUMBER VALIDATION (13 digits only)
    // ============================================
    idInput.addEventListener('input', function() {
        // Remove any non-digit characters
        this.value = this.value.replace(/\D/g, '');
        
        if (this.value.length > 0 && this.value.length !== 13) {
            idError.textContent = `❌ ID must be exactly 13 digits (currently ${this.value.length})`;
            idError.style.color = 'red';
            this.style.borderColor = 'red';
        } else if (this.value.length === 13) {
            // Validate South African ID format
            const isValid = validateSAID(this.value);
            if (isValid) {
                idError.textContent = '✅ Valid South African ID';
                idError.style.color = 'green';
                this.style.borderColor = 'green';
            } else {
                idError.textContent = '❌ Invalid South African ID format';
                idError.style.color = 'red';
                this.style.borderColor = 'red';
            }
        } else {
            idError.textContent = '';
            this.style.borderColor = '';
        }
    });

    // South African ID validation function
    function validateSAID(id) {
        if (id.length !== 13) return false;
        if (!/^\d{13}$/.test(id)) return false;
        
        // Check date of birth (YYMMDD)
        const year = parseInt(id.substring(0, 2));
        const month = parseInt(id.substring(2, 4));
        const day = parseInt(id.substring(4, 6));
        
        if (month < 1 || month > 12) return false;
        if (day < 1 || day > 31) return false;
        
        // Check Luhn algorithm (checksum)
        let sum = 0;
        for (let i = 0; i < 12; i++) {
            let digit = parseInt(id.charAt(i));
            if (i % 2 === 0) {
                digit *= 2;
                if (digit > 9) digit -= 9;
            }
            sum += digit;
        }
        const checkDigit = (10 - (sum % 10)) % 10;
        return checkDigit === parseInt(id.charAt(12));
    }

    // ============================================
    // 2. NAME VALIDATION (Letters only)
    // ============================================
    nameInput.addEventListener('input', function() {
        // Only allow letters, spaces, hyphens, and apostrophes
        const validName = this.value.replace(/[^a-zA-Z\s\-']/g, '');
        if (this.value !== validName) {
            this.value = validName;
            nameError.textContent = '❌ Only letters, spaces, hyphens, and apostrophes allowed';
            nameError.style.color = 'red';
            this.style.borderColor = 'red';
        } else if (this.value.length > 0 && this.value.length < 2) {
            nameError.textContent = '❌ Name must be at least 2 characters';
            nameError.style.color = 'red';
            this.style.borderColor = 'red';
        } else if (this.value.length > 0) {
            nameError.textContent = '✅ Valid name';
            nameError.style.color = 'green';
            this.style.borderColor = 'green';
        } else {
            nameError.textContent = '';
            this.style.borderColor = '';
        }
    });

    // ============================================
    // 3. SURNAME VALIDATION (Letters only)
    // ============================================
    surnameInput.addEventListener('input', function() {
        const validSurname = this.value.replace(/[^a-zA-Z\s\-']/g, '');
        if (this.value !== validSurname) {
            this.value = validSurname;
            surnameError.textContent = '❌ Only letters, spaces, hyphens, and apostrophes allowed';
            surnameError.style.color = 'red';
            this.style.borderColor = 'red';
        } else if (this.value.length > 0 && this.value.length < 2) {
            surnameError.textContent = '❌ Surname must be at least 2 characters';
            surnameError.style.color = 'red';
            this.style.borderColor = 'red';
        } else if (this.value.length > 0) {
            surnameError.textContent = '✅ Valid surname';
            surnameError.style.color = 'green';
            this.style.borderColor = 'green';
        } else {
            surnameError.textContent = '';
            this.style.borderColor = '';
        }
    });

    // ============================================
    // 4. EMAIL VALIDATION
    // ============================================
    emailInput.addEventListener('input', function() {
        const email = this.value;
        const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        
        if (email.length > 0) {
            if (emailPattern.test(email)) {
                emailError.textContent = '✅ Valid email address';
                emailError.style.color = 'green';
                this.style.borderColor = 'green';
            } else {
                emailError.textContent = '❌ Invalid email format (e.g., name@domain.com)';
                emailError.style.color = 'red';
                this.style.borderColor = 'red';
            }
        } else {
            emailError.textContent = '';
            this.style.borderColor = '';
        }
    });

    // ============================================
    // 5. CELL NUMBER VALIDATION (10 digits only)
    // ============================================
    cellInput.addEventListener('input', function() {
        // Remove any non-digit characters
        this.value = this.value.replace(/\D/g, '');
        
        // Limit to 10 digits
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
        
        if (this.value.length > 0 && this.value.length < 10) {
            cellError.textContent = `❌ Cell number must be 10 digits (currently ${this.value.length})`;
            cellError.style.color = 'red';
            this.style.borderColor = 'red';
        } else if (this.value.length === 10) {
            // Check if starts with 0
            if (this.value.startsWith('0')) {
                cellError.textContent = '✅ Valid cell number';
                cellError.style.color = 'green';
                this.style.borderColor = 'green';
            } else {
                cellError.textContent = '❌ Cell number must start with 0';
                cellError.style.color = 'red';
                this.style.borderColor = 'red';
            }
        } else {
            cellError.textContent = '';
            this.style.borderColor = '';
        }
    });

    // ============================================
    // 6. VEHICLE REGISTRATION VALIDATION
    // ============================================
    vehicleRegInput.addEventListener('input', function() {
        // Allow letters, numbers, spaces, and hyphens
        const validReg = this.value.replace(/[^a-zA-Z0-9\s\-]/g, '');
        if (this.value !== validReg) {
            this.value = validReg;
            vehicleRegError.textContent = '❌ Only letters, numbers, spaces, and hyphens allowed';
            vehicleRegError.style.color = 'red';
            this.style.borderColor = 'red';
        } else if (this.value.length > 0 && this.value.length < 4) {
            vehicleRegError.textContent = '❌ Registration too short';
            vehicleRegError.style.color = 'red';
            this.style.borderColor = 'red';
        } else if (this.value.length > 0) {
            vehicleRegError.textContent = '✅ Valid registration format';
            vehicleRegError.style.color = 'green';
            this.style.borderColor = 'green';
        } else {
            vehicleRegError.textContent = '';
            this.style.borderColor = '';
        }
    });

    // ============================================
    // 7. VEHICLE MODEL VALIDATION
    // ============================================
    vehicleModelInput.addEventListener('input', function() {
        // Allow letters, numbers, spaces, and hyphens
        const validModel = this.value.replace(/[^a-zA-Z0-9\s\-]/g, '');
        if (this.value !== validModel) {
            this.value = validModel;
            vehicleModelError.textContent = '❌ Only letters, numbers, spaces, and hyphens allowed';
            vehicleModelError.style.color = 'red';
            this.style.borderColor = 'red';
        } else if (this.value.length > 0 && this.value.length < 2) {
            vehicleModelError.textContent = '❌ Model name too short';
            vehicleModelError.style.color = 'red';
            this.style.borderColor = 'red';
        } else if (this.value.length > 0) {
            vehicleModelError.textContent = '✅ Valid model name';
            vehicleModelError.style.color = 'green';
            this.style.borderColor = 'green';
        } else {
            vehicleModelError.textContent = '';
            this.style.borderColor = '';
        }
    });

    // ============================================
    // 8. PASSWORD STRENGTH CHECKER
    // ============================================
    const strengthBar = document.getElementById('strength_bar');
    const strengthText = document.getElementById('strength_text');

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) strength++;
        
        const width = (strength / 5) * 100;
        strengthBar.style.width = width + '%';
        
        const colors = ['#ff4444', '#ff8844', '#ffcc00', '#88cc44', '#44cc44'];
        const texts = ['Very Weak', 'Weak', 'Medium', 'Strong', 'Very Strong'];
        
        if (password.length > 0) {
            strengthBar.style.background = colors[strength - 1] || '#ff4444';
            strengthText.textContent = texts[strength - 1] || 'Very Weak';
        } else {
            strengthBar.style.width = '0%';
            strengthText.textContent = '';
        }
    });

    // ============================================
    // 9. PASSWORD MATCH CHECKER
    // ============================================
    const matchMessage = document.getElementById('password_match_message');

    confirmInput.addEventListener('input', function() {
        if (this.value.length > 0) {
            if (this.value === passwordInput.value) {
                matchMessage.innerHTML = '✅ Passwords match';
                matchMessage.style.color = 'green';
            } else {
                matchMessage.innerHTML = '❌ Passwords do not match';
                matchMessage.style.color = 'red';
            }
        } else {
            matchMessage.innerHTML = '';
        }
    });

    // ============================================
    // 10. FORM SUBMIT VALIDATION
    // ============================================
    form.addEventListener('submit', function(e) {
        const errors = [];
        
        // Validate ID
        const id = document.getElementById('id_number').value;
        if (id.length !== 13 || !/^\d{13}$/.test(id)) {
            errors.push("ID Number must be exactly 13 digits");
        }
        
        // Validate Name
        const name = document.getElementById('name').value;
        if (!/^[a-zA-Z\s\-']{2,}$/.test(name)) {
            errors.push("Name must contain only letters (minimum 2 characters)");
        }
        
        // Validate Surname
        const surname = document.getElementById('surname').value;
        if (!/^[a-zA-Z\s\-']{2,}$/.test(surname)) {
            errors.push("Surname must contain only letters (minimum 2 characters)");
        }
        
        // Validate Email
        const email = document.getElementById('email').value;
        const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailPattern.test(email)) {
            errors.push("Please enter a valid email address");
        }
        
        // Validate Cell
        const cell = document.getElementById('cell_number').value;
        if (cell.length !== 10 || !/^\d{10}$/.test(cell) || !cell.startsWith('0')) {
            errors.push("Cell number must be exactly 10 digits starting with 0");
        }
        
        // Validate Password
        const password = document.getElementById('password').value;
        if (password.length < 8) {
            errors.push("Password must be at least 8 characters");
        }
        if (!password.match(/[A-Z]/)) {
            errors.push("Password must contain at least one uppercase letter");
        }
        if (!password.match(/[a-z]/)) {
            errors.push("Password must contain at least one lowercase letter");
        }
        if (!password.match(/[0-9]/)) {
            errors.push("Password must contain at least one number");
        }
        if (!password.match(/[!@#$%^&*(),.?":{}|<>]/)) {
            errors.push("Password must contain at least one special character");
        }
        
        // Check if passwords match
        const confirm = document.getElementById('confirm_password').value;
        if (password !== confirm) {
            errors.push("Passwords do not match");
        }
        
        // Validate Vehicle Registration
        const vehicleReg = document.getElementById('vehicle_registration').value;
        if (vehicleReg.length < 4) {
            errors.push("Vehicle registration must be at least 4 characters");
        }
        
        // Validate Vehicle Model
        const vehicleModel = document.getElementById('vehicle_model').value;
        if (vehicleModel.length < 2) {
            errors.push("Vehicle model must be at least 2 characters");
        }
        
        // If errors, prevent submission
        if (errors.length > 0) {
            e.preventDefault();
            alert("❌ Please fix the following errors:\n\n" + errors.join("\n"));
            return false;
        }
        
        return true;
    });
</script>

</body>
</html>