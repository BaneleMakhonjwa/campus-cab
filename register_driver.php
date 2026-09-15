<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $id_number = trim($_POST['id_number']);
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $gender = trim($_POST['gender']);
    $email = trim($_POST['email']);
    $cell_number = trim($_POST['cell_number']);
    
    // Address fields
    $street_address = isset($_POST['street_address']) ? trim($_POST['street_address']) : '';
    $suburb = isset($_POST['suburb']) ? trim($_POST['suburb']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $province = isset($_POST['province']) ? trim($_POST['province']) : '';
    $postal_code = isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '';
    $complex_name = isset($_POST['complex_name']) ? trim($_POST['complex_name']) : '';
    
    // Combine address for database
    $residence = $street_address . ', ' . $suburb . ', ' . $city . ', ' . $province . ' ' . $postal_code;
    if (!empty($complex_name)) {
        $residence = $complex_name . ', ' . $residence;
    }
    
    // Vehicle details
    $vehicle_type = trim($_POST['vehicle_type']);
    $vehicle_make = trim($_POST['vehicle_make']);
    $vehicle_color = trim($_POST['vehicle_color']);
    $vehicle_capacity = intval($_POST['vehicle_capacity']);
    
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $verification_method = isset($_POST['verification_method']) ? trim($_POST['verification_method']) : '';
    $vehicle_registration = trim($_POST['vehicle_registration']);
    $vehicle_model = trim($_POST['vehicle_model']);
    
    // Array to collect errors
    $errors = [];
    
    // ============================================
    // 1. VALIDATE REQUIRED FIELDS
    // ============================================
    if (empty($id_number) || empty($name) || empty($surname) || empty($gender) || 
        empty($email) || empty($cell_number) || empty($residence) || empty($password) || 
        empty($vehicle_registration) || empty($vehicle_model) || empty($vehicle_type) ||
        empty($vehicle_make) || empty($vehicle_color) || empty($vehicle_capacity)) {
        $errors[] = "All fields are required.";
    }
    
    // ============================================
    // 2. ID NUMBER VALIDATION (13 digits)
    // ============================================
    if (!preg_match('/^[0-9]{13}$/', $id_number)) {
        $errors[] = "ID Number must be exactly 13 digits.";
    }
    
    // ============================================
    // 3. NAME VALIDATION (Letters only)
    // ============================================
    if (!preg_match('/^[a-zA-Z\s\-]{2,}$/', $name)) {
        $errors[] = "Name must contain only letters (minimum 2 characters).";
    }
    
    // ============================================
    // 4. SURNAME VALIDATION (Letters only)
    // ============================================
    if (!preg_match('/^[a-zA-Z\s\-]{2,}$/', $surname)) {
        $errors[] = "Surname must contain only letters (minimum 2 characters).";
    }
    
    // ============================================
    // 5. EMAIL VALIDATION
    // ============================================
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    // ============================================
    // 6. CELL NUMBER VALIDATION (10 digits, starts with 0)
    // ============================================
    if (!preg_match('/^0[0-9]{9}$/', $cell_number)) {
        $errors[] = "Cell number must be exactly 10 digits starting with 0.";
    }
    
    // ============================================
    // 7. VEHICLE CAPACITY VALIDATION
    // ============================================
    if ($vehicle_capacity < 1 || $vehicle_capacity > 20) {
        $errors[] = "Vehicle capacity must be between 1 and 20.";
    }
    
    // ============================================
    // 8. STRONG PASSWORD VALIDATION
    // ============================================
    $password_errors = [];
    
    if (strlen($password) < 8) {
        $password_errors[] = "at least 8 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $password_errors[] = "at least 1 uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $password_errors[] = "at least 1 lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $password_errors[] = "at least 1 number";
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $password_errors[] = "at least 1 special character (!@#$%^&*)";
    }
    
    if (!empty($password_errors)) {
        $errors[] = "Password must contain: " . implode(", ", $password_errors) . ".";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    // ============================================
    // 9. VEHICLE VALIDATION
    // ============================================
    if (strlen($vehicle_registration) < 4) {
        $errors[] = "Vehicle registration must be at least 4 characters.";
    }
    if (strlen($vehicle_model) < 2) {
        $errors[] = "Vehicle model must be at least 2 characters.";
    }
    if (strlen($vehicle_make) < 2) {
        $errors[] = "Vehicle make must be at least 2 characters.";
    }
    if (strlen($vehicle_color) < 2) {
        $errors[] = "Vehicle color must be at least 2 characters.";
    }
    
    // ============================================
    // 10. CHECK IF DRIVER ALREADY EXISTS
    // ============================================
    if (empty($errors)) {
        // Check ID number
        $check_id_sql = "SELECT DriverID FROM driver WHERE DriverIDNumber = ?";
        $check_id_stmt = $conn->prepare($check_id_sql);
        $check_id_stmt->bind_param("s", $id_number);
        $check_id_stmt->execute();
        $check_id_stmt->store_result();
        
        if ($check_id_stmt->num_rows > 0) {
            $errors[] = "This ID number is already registered as a driver.";
        }
        $check_id_stmt->close();
        
        // Check Email
        $check_email_sql = "SELECT DriverID FROM driver WHERE DriverEmail = ?";
        $check_email_stmt = $conn->prepare($check_email_sql);
        $check_email_stmt->bind_param("s", $email);
        $check_email_stmt->execute();
        $check_email_stmt->store_result();
        
        if ($check_email_stmt->num_rows > 0) {
            $errors[] = "This email is already registered as a driver.";
        }
        $check_email_stmt->close();
        
        // Check Cell
        $check_cell_sql = "SELECT DriverID FROM driver WHERE DriverPhoneNo = ?";
        $check_cell_stmt = $conn->prepare($check_cell_sql);
        $check_cell_stmt->bind_param("s", $cell_number);
        $check_cell_stmt->execute();
        $check_cell_stmt->store_result();
        
        if ($check_cell_stmt->num_rows > 0) {
            $errors[] = "This cell number is already registered as a driver.";
        }
        $check_cell_stmt->close();
    }
    
    // ============================================
    // 11. HANDLE FILE UPLOADS
    // ============================================
    $driver_photo_path = null;
    $vehicle_photo_path = null;
    
    // Create uploads directory if it doesn't exist
    $upload_dir = __DIR__ . '/uploads/drivers/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Handle driver photo
    if (isset($_FILES['driver_photo']) && $_FILES['driver_photo']['error'] === 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['driver_photo']['type'], $allowed)) {
            $ext = pathinfo($_FILES['driver_photo']['name'], PATHINFO_EXTENSION);
            $filename = 'driver_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $target = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['driver_photo']['tmp_name'], $target)) {
                $driver_photo_path = 'uploads/drivers/' . $filename;
            } else {
                $errors[] = "Failed to upload driver photo.";
            }
        } else {
            $errors[] = "Driver photo must be JPG, PNG, or JPEG.";
        }
    } else {
        $errors[] = "Driver photo is required.";
    }
    
    // Handle vehicle photo
    if (isset($_FILES['vehicle_photo']) && $_FILES['vehicle_photo']['error'] === 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['vehicle_photo']['type'], $allowed)) {
            $ext = pathinfo($_FILES['vehicle_photo']['name'], PATHINFO_EXTENSION);
            $filename = 'vehicle_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $target = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['vehicle_photo']['tmp_name'], $target)) {
                $vehicle_photo_path = 'uploads/drivers/' . $filename;
            } else {
                $errors[] = "Failed to upload vehicle photo.";
            }
        } else {
            $errors[] = "Vehicle photo must be JPG, PNG, or JPEG.";
        }
    } else {
        $errors[] = "Vehicle photo is required.";
    }
    
    // ============================================
    // 12. IF ERRORS, DISPLAY THEM
    // ============================================
    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    } else {
        // ============================================
        // 13. HASH PASSWORD AND INSERT INTO DATABASE
        // ============================================
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $insert_sql = "INSERT INTO driver (
            DriverIDNumber, 
            DriverFname, 
            DriverLname, 
            DriverGender, 
            DriverEmail, 
            DriverPhoneNo, 
            DriverAddress, 
            `password`, 
            DriverPhoto, 
            VehicleReg, 
            VehicleModel,
            vehicle_type,
            vehicle_color,
            vehicle_capacity,
            vehicle_make,
            VehiclePhoto
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param(
            "ssssssssssssiss",
            $id_number,
            $name,
            $surname,
            $gender,
            $email,
            $cell_number,
            $residence,
            $hashed_password,
            $driver_photo_path,
            $vehicle_registration,
            $vehicle_model,
            $vehicle_type,
            $vehicle_color,
            $vehicle_capacity,
            $vehicle_make,
            $vehicle_photo_path
        );
        
        if ($insert_stmt->execute()) {
            $driver_id = $conn->insert_id;
            $_SESSION['driver_id'] = $driver_id;
            $_SESSION['driver_name'] = $name . ' ' . $surname;
            $success = "Registration successful! Welcome " . $name . "! You can now login.";
        } else {
            $error = "Registration failed: " . $conn->error;
        }
        $insert_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Registration | Campus Cab</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .registration-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .registration-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .registration-header h1 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .registration-header p {
            color: #6c757d;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
        }
        .form-group small {
            display: block;
            color: #6c757d;
            font-size: 12px;
            margin-top: 5px;
        }
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .error-message {
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .register-button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .register-button:hover {
            background: #0056b3;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .password-strength {
            margin: 10px 0 20px 0;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        .password-strength #strength_bar {
            height: 8px;
            width: 0%;
            background: #ff4444;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        .password-strength #strength_text {
            font-size: 14px;
            font-weight: bold;
            display: block;
            margin-top: 5px;
        }
        .btn-primary {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .verification-options {
            display: flex;
            gap: 20px;
        }
        h2 {
            margin-top: 30px;
            margin-bottom: 15px;
            color: #2c3e50;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .form-group input.error,
        .form-group select.error,
        .form-group textarea.error {
            border-color: #dc3545;
        }
        .form-group input.success,
        .form-group select.success,
        .form-group textarea.success {
            border-color: #28a745;
        }
        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            .registration-container {
                margin: 20px;
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<?php include 'header.php'; ?>

<div class="registration-container">

    <div class="registration-header">
        <h1>🚗 Driver Registration</h1>
        <p>Join our team and start earning with Campus Cab.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            ❌ <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            ✅ <?php echo $success; ?>
            <br><br>
            <a href="login.php" class="btn-primary">🔑 Go to Login</a>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form action="" method="POST" enctype="multipart/form-data" id="registrationForm">

        <!-- DRIVER INFORMATION -->
        <h2>👤 Driver Information</h2>

        <div class="form-group">
            <label for="id_number">ID Number *</label>
            <input 
                type="text" 
                id="id_number"
                name="id_number"
                placeholder="e.g. 9001011234089"
                maxlength="13"
                value="<?php echo isset($id_number) ? htmlspecialchars($id_number) : ''; ?>"
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
                    value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"
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
                    value="<?php echo isset($surname) ? htmlspecialchars($surname) : ''; ?>"
                    required
                >
                <span id="surname_error" class="error-message"></span>
            </div>
        </div>

        <div class="form-group">
            <label for="gender">Gender *</label>
            <select id="gender" name="gender" required>
                <option value="">Select gender</option>
                <option value="Male" <?php echo (isset($gender) && $gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo (isset($gender) && $gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo (isset($gender) && $gender == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="email">Email Address *</label>
            <input 
                type="email"
                id="email"
                name="email"
                placeholder="example@email.com"
                value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
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
                value="<?php echo isset($cell_number) ? htmlspecialchars($cell_number) : ''; ?>"
                required
            >
            <small>10 digits (e.g. 0712345678)</small>
            <span id="cell_error" class="error-message"></span>
        </div>

        <!-- ADDRESS SECTION -->
        <h2>📍 Residence Address</h2>

        <div class="form-group">
            <label for="street_address">Street Address *</label>
            <input 
                type="text"
                id="street_address"
                name="street_address"
                placeholder="e.g. 123 Nelson Mandela Drive"
                value="<?php echo isset($street_address) ? htmlspecialchars($street_address) : ''; ?>"
                required
            >
            <small>House number and street name</small>
        </div>

        <div class="form-group">
            <label for="suburb">Suburb / Area *</label>
            <input 
                type="text"
                id="suburb"
                name="suburb"
                placeholder="e.g. Sunnyside"
                value="<?php echo isset($suburb) ? htmlspecialchars($suburb) : ''; ?>"
                required
            >
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="city">City / Town *</label>
                <input 
                    type="text"
                    id="city"
                    name="city"
                    placeholder="e.g. Alice Town"
                    value="<?php echo isset($city) ? htmlspecialchars($city) : ''; ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="province">Province *</label>
                <select id="province" name="province" required>
                    <option value="">Select province</option>
                    <option value="Eastern Cape" <?php echo (isset($province) && $province == 'Eastern Cape') ? 'selected' : ''; ?>>Eastern Cape</option>
                    <option value="Free State" <?php echo (isset($province) && $province == 'Free State') ? 'selected' : ''; ?>>Free State</option>
                    <option value="Gauteng" <?php echo (isset($province) && $province == 'Gauteng') ? 'selected' : ''; ?>>Gauteng</option>
                    <option value="KwaZulu-Natal" <?php echo (isset($province) && $province == 'KwaZulu-Natal') ? 'selected' : ''; ?>>KwaZulu-Natal</option>
                    <option value="Limpopo" <?php echo (isset($province) && $province == 'Limpopo') ? 'selected' : ''; ?>>Limpopo</option>
                    <option value="Mpumalanga" <?php echo (isset($province) && $province == 'Mpumalanga') ? 'selected' : ''; ?>>Mpumalanga</option>
                    <option value="North West" <?php echo (isset($province) && $province == 'North West') ? 'selected' : ''; ?>>North West</option>
                    <option value="Northern Cape" <?php echo (isset($province) && $province == 'Northern Cape') ? 'selected' : ''; ?>>Northern Cape</option>
                    <option value="Western Cape" <?php echo (isset($province) && $province == 'Western Cape') ? 'selected' : ''; ?>>Western Cape</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="postal_code">Postal Code *</label>
                <input 
                    type="text"
                    id="postal_code"
                    name="postal_code"
                    placeholder="e.g. 5200"
                    maxlength="4"
                    value="<?php echo isset($postal_code) ? htmlspecialchars($postal_code) : ''; ?>"
                    required
                >
                <small>4-digit postal code</small>
            </div>

            <div class="form-group">
                <label for="complex_name">Complex / Building Name</label>
                <input 
                    type="text"
                    id="complex_name"
                    name="complex_name"
                    placeholder="e.g. Green Acres"
                    value="<?php echo isset($complex_name) ? htmlspecialchars($complex_name) : ''; ?>"
                >
                <small>Optional - if applicable</small>
            </div>
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

        <!-- VEHICLE PHOTO -->
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

        <!-- ============================================ -->
        <!-- 🚗 VEHICLE INFORMATION 
        <!-- ============================================ -->
        <h2>🚗 Vehicle Information</h2>

        <div class="form-group">
            <label for="vehicle_type">Vehicle Type *</label>
            <select id="vehicle_type" name="vehicle_type" required>
                <option value="">Select vehicle type</option>
                <option value="Car" <?php echo (isset($vehicle_type) && $vehicle_type == 'Car') ? 'selected' : ''; ?>>🚗 Car</option>
                <option value="Motor Bike" <?php echo (isset($vehicle_type) && $vehicle_type == 'Motor Bike') ? 'selected' : ''; ?>>🏍️ Motor Bike</option>
                <option value="Van" <?php echo (isset($vehicle_type) && $vehicle_type == 'Van') ? 'selected' : ''; ?>>🚐 Van</option>
                
            </select>
            <small>Select your vehicle type</small>
        </div>

        <div class="form-group">
            <label for="vehicle_make">Vehicle Make *</label>
            <input 
                type="text"
                id="vehicle_make"
                name="vehicle_make"
                placeholder="e.g. Toyota, Ford, Honda"
                value="<?php echo isset($vehicle_make) ? htmlspecialchars($vehicle_make) : ''; ?>"
                required
            >
            <small>Brand of your vehicle</small>
        </div>

        <div class="form-group">
            <label for="vehicle_model">Vehicle Model *</label>
            <input 
                type="text"
                id="vehicle_model"
                name="vehicle_model"
                placeholder="e.g. Corolla, Focus, Civic"
                value="<?php echo isset($vehicle_model) ? htmlspecialchars($vehicle_model) : ''; ?>"
                required
            >
            <small>Model name</small>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="vehicle_color">Vehicle Color *</label>
                <input 
                    type="text"
                    id="vehicle_color"
                    name="vehicle_color"
                    placeholder="e.g. Red, Blue, White"
                    value="<?php echo isset($vehicle_color) ? htmlspecialchars($vehicle_color) : ''; ?>"
                    required
                >
                <small>Color of your vehicle</small>
            </div>

            <div class="form-group">
                <label for="vehicle_capacity">Vehicle Capacity *</label>
                <input 
                    type="number"
                    id="vehicle_capacity"
                    name="vehicle_capacity"
                    placeholder="e.g. 4"
                    min="1"
                    max="8"
                    value="<?php echo isset($vehicle_capacity) ? htmlspecialchars($vehicle_capacity) : '4'; ?>"
                    required
                >
                <small>Number of passengers (including driver)</small>
            </div>
        </div>

        <div class="form-group">
            <label for="vehicle_registration">Vehicle Registration Number *</label>
            <input 
                type="text"
                id="vehicle_registration"
                name="vehicle_registration"
                placeholder="e.g. CA 123-45"
                value="<?php echo isset($vehicle_registration) ? htmlspecialchars($vehicle_registration) : ''; ?>"
                required
            >
            <span id="vehicle_reg_error" class="error-message"></span>
        </div>

        <!-- AUTHENTICATION -->
        <h2>📱 Authentication</h2>

        <div class="form-group">
            <label>Choose verification method *</label>
            <div class="verification-options">
                <label>
                    <input 
                        type="radio"
                        name="verification_method"
                        value="email"
                        <?php echo (isset($verification_method) && $verification_method == 'email') ? 'checked' : ''; ?>
                        required
                    >
                    📧 Email
                </label>
                <label>
                    <input 
                        type="radio"
                        name="verification_method"
                        value="cellphone"
                        <?php echo (isset($verification_method) && $verification_method == 'cellphone') ? 'checked' : ''; ?>
                    >
                    📱 Cellphone
                </label>
            </div>
        </div>

        <!-- SUBMIT -->
        <button type="submit" class="register-button">✅ Register as Driver</button>

        <p class="login-link">
            Already registered as a driver? <a href="login.php">Login here</a>
        </p>
        <p class="login-link">
            <a href="register.php">← Change Role</a>
        </p>

    </form>
    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>

<!-- JavaScript Validation -->
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

    // Error message elements
    const idError = document.getElementById('id_error');
    const nameError = document.getElementById('name_error');
    const surnameError = document.getElementById('surname_error');
    const emailError = document.getElementById('email_error');
    const cellError = document.getElementById('cell_error');
    const vehicleRegError = document.getElementById('vehicle_reg_error');

    // ID NUMBER VALIDATION
    if (idInput) {
        idInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            
            if (this.value.length > 0 && this.value.length !== 13) {
                idError.textContent = `❌ ID must be exactly 13 digits (currently ${this.value.length})`;
                idError.style.color = 'red';
                this.style.borderColor = 'red';
            } else if (this.value.length === 13) {
                idError.textContent = '✅ Valid ID number';
                idError.style.color = 'green';
                this.style.borderColor = 'green';
            } else {
                idError.textContent = '';
                this.style.borderColor = '';
            }
        });
    }

    // NAME VALIDATION
    if (nameInput) {
        nameInput.addEventListener('input', function() {
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
    }

    // SURNAME VALIDATION
    if (surnameInput) {
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
    }

    // EMAIL VALIDATION
    if (emailInput) {
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
    }

    // CELL NUMBER VALIDATION
    if (cellInput) {
        cellInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
            
            if (this.value.length > 0 && this.value.length < 10) {
                cellError.textContent = `❌ Cell number must be 10 digits (currently ${this.value.length})`;
                cellError.style.color = 'red';
                this.style.borderColor = 'red';
            } else if (this.value.length === 10) {
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
    }

    // VEHICLE REGISTRATION VALIDATION
    if (vehicleRegInput) {
        vehicleRegInput.addEventListener('input', function() {
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
    }

    // PASSWORD STRENGTH CHECKER
    const strengthBar = document.getElementById('strength_bar');
    const strengthText = document.getElementById('strength_text');

    if (passwordInput && strengthBar && strengthText) {
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
                strengthText.style.color = colors[strength - 1] || '#ff4444';
            } else {
                strengthBar.style.width = '0%';
                strengthText.textContent = '';
            }
        });
    }

    // PASSWORD MATCH CHECKER
    const matchMessage = document.getElementById('password_match_message');

    if (confirmInput && passwordInput && matchMessage) {
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
    }

    // FORM SUBMIT VALIDATION
    if (form) {
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
            
            // Validate Vehicle Type
            const vehicleType = document.getElementById('vehicle_type').value;
            if (!vehicleType) {
                errors.push("Please select a vehicle type");
            }
            
            // Validate Vehicle Make
            const vehicleMake = document.getElementById('vehicle_make').value;
            if (vehicleMake.length < 2) {
                errors.push("Vehicle make must be at least 2 characters");
            }
            
            // Validate Vehicle Color
            const vehicleColor = document.getElementById('vehicle_color').value;
            if (vehicleColor.length < 2) {
                errors.push("Vehicle color must be at least 2 characters");
            }
            
            // Validate Vehicle Capacity
            const vehicleCapacity = document.getElementById('vehicle_capacity').value;
            if (vehicleCapacity < 1 || vehicleCapacity > 20) {
                errors.push("Vehicle capacity must be between 1 and 20");
            }
            
            // If errors, prevent submission
            if (errors.length > 0) {
                e.preventDefault();
                alert("❌ Please fix the following errors:\n\n" + errors.join("\n"));
                return false;
            }
            
            return true;
        });
    }
</script>

</body>
</html>
