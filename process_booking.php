<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'customer') {
    if (!isset($_POST['seat_number']) || !is_numeric($_GET['event_id'])) {
        echo "<script>alert('Please Select a Seat.'); window.history.back();</script>";
        exit;
    }

    $seat_number = $_POST['seat_number'];
    $event_id = (int)$_GET['event_id'];
    $user_id = $_SESSION['user_id']; 

    $query = "INSERT INTO bookings (user_id, event_id, booking_date) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $user_id, $event_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['seat_number'] = $seat_number;
    $_SESSION['event_id'] = $event_id;

   
    header('Location: payment.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}
?>