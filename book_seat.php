<?php
include 'db_connect.php';
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header('Location: login.php');
    exit;
}


if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    die('Invalid Event ID.');
}

$event_id = (int)$_GET['event_id'];


$user_id = $_SESSION['user_id'];
$user_sql = "SELECT username FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_row = $user_result->fetch_assoc();
$username = $user_row['username'];


$_SESSION['username'] = $username;

$query = "SELECT seat_number, status FROM seats WHERE event_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$seats = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $seat_number = $_POST['seat_number'];
    $_SESSION['seat_number'] = $seat_number;
    $_SESSION['event_id'] = $event_id;

    header('Location: payment.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Seat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
    
    <style>
        .seat-container {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin: 20px 0;
        }
        .seat {
            width: 100%;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            border: 1px solid #ccc;
            border-radius: 5px;
            position: relative;
        }
        .seat input[type="radio"] {
            opacity: 0;
            position: absolute;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .seat.available label {
            background-color: #aaffaa;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .seat.booked label {
            background-color: #ffaaaa;
            cursor: not-allowed;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .seat input[type="radio"]:checked + label {
            background-color: #45a049;
        }
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
                <span class="username fw-bold"><?php echo htmlspecialchars($username); ?></span>
            </div>
        </div>
        <div class="space"></div>
        <a href="customer_dashboard.php"><i class="fa-solid fa-home fa-xl"></i> Home</a>
        <a href="view_events_customer.php"><i class="fa-solid fa-calendar fa-xl"></i> View Events</a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket fa-xl"></i> Logout</a>
    </div>

    <div class="main-content">
        <h2 class="text-center">Seat Plan</h2>
        <form method="POST" action="process_booking.php?event_id=<?php echo htmlspecialchars($event_id); ?>">
            <div class="seat-container">
                <?php
                while ($seat = $seats->fetch_assoc()) {
                    $status = $seat['status'] == 'available' ? 'available' : 'booked';
                    $seat_number = htmlspecialchars($seat['seat_number']);
                    echo "<div class='seat $status'>";
                    echo "<input type='radio' name='seat_number' value='$seat_number' id='seat_$seat_number' " . ($status == 'booked' ? 'disabled' : '') . ">";
                    echo "<label for='seat_$seat_number'>$seat_number</label>";
                    echo "</div>";
                }
                ?>
            </div>
            <button type="submit" class="btn btn-primary">Book Seat</button>
            <a href="view_events_customer.php" class="btn btn-danger">Back</a>
        </form>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
