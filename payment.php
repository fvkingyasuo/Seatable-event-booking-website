<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['seat_number']) || !isset($_SESSION['event_id']) || $_SESSION['role'] != 'customer') {
    header('Location: login.php');
    exit;
}
$seat_number = $_SESSION['seat_number'];
$event_id = $_SESSION['event_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $amount = 300;
    $sql = "UPDATE seats SET status = 'booked', user_id = ? WHERE event_id = ? AND seat_number = ? AND status = 'available'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $user_id, $event_id, $seat_number);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $update_sales = "UPDATE events SET sales = sales + ? WHERE id = ?";
        $sales_stmt = $conn->prepare($update_sales);
        $sales_stmt->bind_param('di', $amount, $event_id);
        $sales_stmt->execute();
    unset($_SESSION['seat_number'], $_SESSION['event_id']);
        header('Location: confirmation.php');
        exit;
    } else {
        echo "Error booking seat: The seat is already be booked.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeatAble</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
    <style>
    .btn{
        width: 100%;
    }
    </style>
</head>
<body>
    <div class="sidebar">
<div class="logo-holder">
            <img src="image\event_booking_logo.jpg" alt="Logo">
        </div>
        <div class="usercontainer">
            <div class="userpicture"><i class="fa-solid fa-circle-user fa-xl"></i></div>
            <div class="userdata">
                <span class="username fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
        </div>
        <div class="space"></div>
        <a href="customer_dashboard.php"><i class="fa-solid fa-home fa-xl"></i> Home</a>
        <a href="view_events_customer.php"><i class="fa-solid fa-calendar fa-xl"></i> View Events</a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket fa-xl"></i> Logout</a>
    </div>
    <div class="main-content">
        <h2 class="text-center">Payment</h2>
        <div class="alert alert-info text-center">
            <h2>Are You Sure You Want to Book Seat Number: <?php echo htmlspecialchars($_SESSION['seat_number']); ?>?</h2>
            <h3>Price of this Seat: 300 Pesos</h3>
            <form method="POST">
                <button type="submit" class="btn btn-primary">Confirm Payment</button>
                <a href="book_seat.php?event_id=<?php echo htmlspecialchars($_SESSION['event_id']); ?>" class="btn btn-danger">Back</a>
            </form>
        </div>
    </div>
</body>
</html>
