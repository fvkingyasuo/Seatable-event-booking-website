<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'organizer') {
    header('Location: login.php');
    exit;
}

$organizer_id = $_SESSION['user_id'];
$user_sql = "SELECT username FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $organizer_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
if ($user_result->num_rows > 0) {
    $user_row = $user_result->fetch_assoc();
    $username = htmlspecialchars($user_row['username']);
} else {
    $username = 'Unknown Organizer';
}
$user_stmt->close();

if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    die('Invalid Event ID.');
}

$event_id = (int)$_GET['event_id'];

$query = "
    SELECT seats.seat_number, users.username 
    FROM seats
    JOIN users ON seats.user_id = users.id
    WHERE seats.event_id = ? AND seats.status = 'booked'";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$booked_seats = $stmt->get_result();

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
    <title>SeatAble</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
                <span class="username fw-bold"><?php echo $username; ?></span>
            </div>
        </div>
        <div class="space"></div>
        <a href="organizer_dashboard.php"><i class="fa-solid fa-chart-line fa-xl"></i> Dashboard</a>
        <a href="view_events_organizer.php"><i class="fa-solid fa-calendar fa-xl"></i> View Events</a>
        <a href="create_event.php"><i class="fa-solid fa-clipboard-list fa-xl"></i> Create Event</a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket fa-xl"></i> Logout</a>
    </div>

    <div class="main-content">
        <h2 class="text-center">Booked Seats</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Seat Number</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($seat = $booked_seats->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($seat['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($seat['seat_number']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

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
        </form>


    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>