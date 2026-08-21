<?php
session_start();

// If already logged in, go to homepage
if (isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

include_once __DIR__ . '/db_connect.php';

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
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($student_number) || empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
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
                $error = "Email already registered. Please use a different email.";
            } else {
                // Hash password for security
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new student - MATCH YOUR DATABASE COLUMNS
                $insert_sql = "INSERT INTO student (studentNumber, studentFname, studentLname, studentEmail, studentPhoneNo, studentPwd) 
                               VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ssssss", $student_number, $first_name, $last_name, $email, $phone, $hashed_password);
                
                if ($insert_stmt->execute()) {
                    $success = "Registration successful! You can now login.";
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | Campus Cab</title>
    <link rel="stylesheet" href="style.css">
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
                    <a href="login_student.php" class="btn-primary">🔑 Login Now</a>
                </div>
            <?php else: ?>

            <form method="POST" action="register_student.php">
                <div class="form-group">
                    <label for="student_number">Student Number *</label>
                    <input type="text" id="student_number" name="student_number" placeholder="e.g., 202412345" value="<?php echo isset($student_number) ? htmlspecialchars($student_number) : ''; ?>" required>
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
                    <input type="email" id="email" name="email" placeholder="e.g., student@university.ac.za" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" placeholder="e.g., 0812345678" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" placeholder="Min 6 characters" required>
                        <small>Password must be at least 6 characters</small>
                    </div>
                    <div class="form-group half">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary btn-block">✅ Register</button>
                
                <p class="login-link">
                    Already have an account? <a href="login_student.php">Login here</a>
                </p>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>