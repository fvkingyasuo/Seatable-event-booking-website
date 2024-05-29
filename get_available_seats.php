<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    echo 'Invalid Event ID';
    exit;
}

$event_id = (int)$_GET['event_id'];

$stmt = $conn->prepare("SELECT COUNT(*) AS available_seats FROM seats WHERE event_id = ? AND status = 'available'");
$stmt->bind_param('i', $event_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$available_seats = $row['available_seats'];

if (isset($_SESSION['role']) && $_SESSION['role'] == 'customer') {
    if ($available_seats > 0) {
        echo $available_seats . " available <a class='btn btn-primary' href='book_seat.php?event_id=$event_id'>Book Now</a>";
    } else {
        echo "No available seats";
    }
} elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'organizer') {
    if ($available_seats > 0) {
        echo $available_seats . " available";
    } else {
        echo "No available seats";
    }
}

$stmt->close();
$conn->close();
?>
