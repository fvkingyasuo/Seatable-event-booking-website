<?php
session_start();
include 'db_connect.php';

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
$user_row = $user_result->fetch_assoc();
$username = htmlspecialchars($user_row['username']);

$search_query = "";
if (isset($_POST['search'])) {
    $search_query = $_POST['search'];
}

$sql = "SELECT events.*, users.username AS organizer_name 
        FROM events 
        JOIN users ON events.organizer_id = users.id
        WHERE events.title LIKE ? OR 
              events.date LIKE ? OR 
              events.description LIKE ? OR 
              users.username LIKE ?";
$search_term = '%' . $search_query . '%';
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
$stmt->execute();
$events = $stmt->get_result();

function get_available_seats($conn, $event_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS available_seats FROM seats WHERE event_id = ? AND status = 'available'");
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['available_seats'];
}

$calendar_sql = "SELECT title, date FROM events";
$calendar_stmt = $conn->prepare($calendar_sql);
$calendar_stmt->execute();
$calendar_events = $calendar_stmt->get_result();
$calendar_events_data = [];
while ($event = $calendar_events->fetch_assoc()) {
    $calendar_events_data[] = [
        'title' => htmlspecialchars($event['title']),
        'start' => $event['date']
    ];
}
$calendar_events_json = json_encode($calendar_events_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeaTable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#calendar').fullCalendar({
                events: <?php echo $calendar_events_json; ?>,
                height: 800 
            });
        });
    </script>
    <style>
        .calendar-container {
            max-width: 1000px; 
            margin: 0 auto;
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
        <h2 class="text-center">All Events</h2>
        <form method="POST" action="view_events_organizer.php" class="d-flex flex-column align-items-center mb-4">
            <div class="input-group w-50">
                <input type="text" name="search" class="form-control" placeholder="Search events" value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
        <table class="table">
            <thead>
                <tr>
                    <th>Organizer</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Available Seats</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($event = $events->fetch_assoc()) {
                    $event_id = $event['id'];
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($event['organizer_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($event['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($event['date']) . "</td>";
                    echo "<td>" . htmlspecialchars($event['description']) . "</td>";
                    echo "<td>" . get_available_seats($conn, $event_id) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <hr class="my-4">

        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
