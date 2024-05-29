<?php
include 'db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header('Location: login.php');
    exit;
}


$user_id = $_SESSION['user_id'];
$user_sql = "SELECT username FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_row = $user_result->fetch_assoc();
$username = $user_row['username'];
$_SESSION['username'] = $username;

$user_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar">
<div class="logo-holder">
            <img src="image\event_booking_logo.jpg" alt="Logo">
        </div>
        <div class="usercontainer">
            <div class="userpicture"><i class="fa-solid fa-circle-user fa-xl"></i></div>
            <div class="userdata">
                <span class="username fw-bold"><?php echo htmlspecialchars($username); ?></span>
            </div>
        </div>
        <div class="space"></div>
        <a href="customer_dashboard.php"><i class="fa-solid fa-home fa-xl"></i> Home</a>
        <a href="view_events_customer.php"><i class="fa-solid fa-calendar fa-xl"></i> View Events</a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket fa-xl"></i> Logout</a>
    </div>
    <div class="main-content">
        <h2 class="text-center">Booking Confirmation</h2>
        <div class="alert alert-success text-center">
            <h4>Booking Successful.</h4> 
            <div class="button">
            <a href="customer_dashboard.php" class="btn btn-primary">Go back to Home</a>
            </div>        
        </div>
      
    </div>
</body>
</html>

<?php

$conn->close();
?>
