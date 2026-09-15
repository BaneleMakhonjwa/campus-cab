<?php
session_start();

// If already logged in, go to homepage
if (isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

include_once __DIR__ . '/db_connect.php';
include_once __DIR__ . '/mail_config.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $student_number = trim($_POST['student_number']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['studentPWD'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($student_number) || empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!preg_match('/^[0-9]{9}$/', $student_number)) {
        $error = "Student number must be exactly 9 digits (e.g., 202412345).";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Please enter a valid 10-digit phone number (e.g., 0812345678).";
    } else {
        // Strong password validation
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
            $password_errors[] = "at least 1 special character (!@#$%^&* etc.)";
        }
        
        if (!empty($password_errors)) {
            $error = "Password must contain: " . implode(", ", $password_errors) . ".";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check if student number already exists
            $check_sql = "SELECT studentID FROM student WHERE studentNumber = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $student_number);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $error = "Student number already registered. Please login.";
            } else {
                // Check if email already exists
                $check_email_sql = "SELECT studentID FROM student WHERE studentEmail = ?";
                $check_email_stmt = $conn->prepare($check_email_sql);
                $check_email_stmt->bind_param("s", $email);
                $check_email_stmt->execute();
                $check_email_stmt->store_result();
                
                if ($check_email_stmt->num_rows > 0) {
                    $error = "Email already registered. Please use a different email or login.";
                } else {
                    // Hash password for security
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Generate verification token
                    $verification_token = bin2hex(random_bytes(32));
                    $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    
                    // Insert new student
                    $insert_sql = "INSERT INTO student (studentNumber, studentFname, studentLname, studentEmail, studentPhoneNo, studentPwd, verification_token, token_expiry, email_verified) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("ssssssss", $student_number, $first_name, $last_name, $email, $phone, $hashed_password, $verification_token, $token_expiry);
                    
                    if ($insert_stmt->execute()) {
                        // Send verification email
                        $email_sent = sendVerificationEmail($email, $first_name . ' ' . $last_name, $verification_token);
                        
                        if ($email_sent) {
                            $success = "Registration successful! A verification email has been sent to $email. Please check your inbox and click the verification link to activate your account.";
                        } else {
                            $success = "Registration successful, but we couldn't send the verification email. Please contact support.";
                        }
                        
                        // Clear form
                        $student_number = $first_name = $last_name = $email = $phone = '';
                    } else {
                        $error = "Registration failed: " . $conn->error;
                    }
                    $insert_stmt->close();
                }
                $check_email_stmt->close();
            }
            $check_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | Campus Cab</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Password Strength Styles */
        .password-strength {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0 20px 0;
        }

        .strength-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .strength-fill.weak { width: 25%; background: #dc3545; }
        .strength-fill.medium { width: 50%; background: #ffc107; }
        .strength-fill.good { width: 75%; background: #17a2b8; }
        .strength-fill.strong { width: 100%; background: #28a745; }

        .strength-text {
            font-size: 14px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        .strength-text.weak { color: #dc3545; }
        .strength-text.medium { color: #ffc107; }
        .strength-text.good { color: #17a2b8; }
        .strength-text.strong { color: #28a745; }

        .requirements {
            list-style: none;
            padding: 0;
            margin: 5px 0 0 0;
            font-size: 13px;
        }

        .requirements li {
            padding: 3px 0;
            color: #dc3545;
            transition: all 0.3s ease;
        }

        .requirements li.met {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container register-container">
        <div class="register-box">
            <h1>📝 Student Registration</h1>
            <p>Create your account to start using Campus Cab services.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($success); ?>
                    <br><br>
                    <a href="login_student.php" class="btn-primary">🔑 Go to Login</a>
                </div>
            <?php else: ?>

            <form method="POST" action="register_student.php" id="registerForm">
                <div class="form-group">
                    <label for="student_number">Student Number *</label>
                    <input type="text" id="student_number" name="student_number" placeholder="e.g., 202412345" maxlength="9" value="<?php echo isset($student_number) ? htmlspecialchars($student_number) : ''; ?>" required>
                    <small>Must be exactly 9 digits (e.g. 202412345)</small>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>" required>
                    </div>
                    <div class="form-group half">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" placeholder="e.g. StudentNo@ufh.ac.za" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    <small>We'll send a verification link to this email</small>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" placeholder="e.g. 0812345678" maxlength="10" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                    <small>Enter a valid 10-digit South African phone number</small>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="studentPWD">Password *</label>
                        <input type="password" id="studentPWD" name="studentPWD" placeholder="Enter password" required>
                        <small>Password must contain: 8+ chars, uppercase, lowercase, number & special character</small>
                    </div>
                    <div class="form-group half">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                    </div>
                </div>

                <!-- Password Strength Indicator -->
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <p class="strength-text" id="strengthText">Enter a strong password</p>
                    <ul class="requirements" id="requirements">
                        <li id="reqLength">❌ At least 8 characters</li>
                        <li id="reqUpper">❌ At least 1 uppercase letter</li>
                        <li id="reqLower">❌ At least 1 lowercase letter</li>
                        <li id="reqNumber">❌ At least 1 number</li>
                        <li id="reqSpecial">❌ At least 1 special character (!@#$%^&*)</li>
                    </ul>
                </div>

                <button type="submit" class="btn-primary btn-block">✅ Register</button>
                
                <p class="login-link">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('studentPWD');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        
        // Get all requirement elements
        const reqLength = document.getElementById('reqLength');
        const reqUpper = document.getElementById('reqUpper');
        const reqLower = document.getElementById('reqLower');
        const reqNumber = document.getElementById('reqNumber');
        const reqSpecial = document.getElementById('reqSpecial');
        
        password.addEventListener('input', function() {
            const pwd = this.value;
            let metCount = 0;
            
            // Check each requirement
            const hasLength = pwd.length >= 8;
            const hasUpper = /[A-Z]/.test(pwd);
            const hasLower = /[a-z]/.test(pwd);
            const hasNumber = /[0-9]/.test(pwd);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(pwd);
            
            // Update requirement list in real-time (✅ when met, ❌ when not)
            updateRequirement(reqLength, hasLength, 'At least 8 characters');
            updateRequirement(reqUpper, hasUpper, 'At least 1 uppercase letter');
            updateRequirement(reqLower, hasLower, 'At least 1 lowercase letter');
            updateRequirement(reqNumber, hasNumber, 'At least 1 number');
            updateRequirement(reqSpecial, hasSpecial, 'At least 1 special character');
            
            // Count met requirements
            metCount = [hasLength, hasUpper, hasLower, hasNumber, hasSpecial].filter(Boolean).length;
            let score = (metCount / 5) * 100;
            
            // Update strength bar
            strengthFill.style.width = score + '%';
            strengthFill.className = 'strength-fill';
            
            // Update strength text
            let level, colorClass;
            if (score === 0) {
                level = 'Enter a strong password';
                colorClass = '';
            } else if (score < 40) {
                level = 'Weak - add more characters and variety';
                colorClass = 'weak';
            } else if (score < 60) {
                level = 'Medium - add more variety';
                colorClass = 'medium';
            } else if (score < 80) {
                level = 'Good - almost there!';
                colorClass = 'good';
            } else {
                level = '✅ Strong password!';
                colorClass = 'strong';
            }
            
            strengthText.textContent = level;
            strengthText.className = 'strength-text ' + colorClass;
            strengthFill.classList.add(colorClass);
        });
        
        // Function to update requirement icons in real-time
        function updateRequirement(element, met, text) {
            if (met) {
                element.textContent = '✅ ' + text;
                element.className = 'met';
            } else {
                element.textContent = '❌ ' + text;
                element.className = '';
            }
        }
    });
    </script>
</body>
</html>
