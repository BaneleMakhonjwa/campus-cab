<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login_student.php");
    exit();
}

// Database connection
include_once __DIR__ . '/db_connect.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: request_grocery.php");
    exit();
}

// Get form data
$student_id = $_SESSION['student_id'];
$store_name = isset($_POST['store_name']) ? trim($_POST['store_name']) : '';
$items_list = isset($_POST['items_list']) ? trim($_POST['items_list']) : '';
$max_budget = isset($_POST['max_budget']) ? floatval($_POST['max_budget']) : 0;

// Validate
if (empty($store_name)) {
    header("Location: request_grocery.php?error=Please select a store");
    exit();
}

if (empty($items_list)) {
    header("Location: request_grocery.php?error=Please enter your shopping list");
    exit();
}

if ($max_budget <= 0) {
    header("Location: request_grocery.php?error=Please enter a valid budget amount");
    exit();
}

// Insert into database
$sql = "INSERT INTO grocery_orders (student_id, store_name, items_list, max_budget, status) 
        VALUES (?, ?, ?, ?, 'PENDING')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issd", $student_id, $store_name, $items_list, $max_budget);

if ($stmt->execute()) {
    header("Location: request_grocery.php?success=1");
} else {
    header("Location: request_grocery.php?error=" . urlencode($conn->error));
}

$stmt->close();
$conn->close();
?>