<?php
session_start();
include 'db_connect.php';

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


$stmt = $conn->prepare("
SELECT DISTINCT events.title, events.date, events.description, events.organizer_id, seats.seat_number, organizers.username as organizer_username
FROM bookings
INNER JOIN events ON bookings.event_id = events.id
INNER JOIN seats ON bookings.event_id = seats.event_id AND bookings.user_id = seats.user_id
INNER JOIN users as organizers ON events.organizer_id = organizers.id
WHERE bookings.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$booked_events = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeaTable</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
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
        <h2 class="text-center">My Booked Events</h2>
        <div class="input-group mb-3 search-container">
            <input type="text" id="searchInput" class=" float-right search" placeholder="Search for events">
            <button class="btn float-right search-icon" onclick="searchTable()"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>
        <div class="table-responsive">
            <table class="table" id="eventsTable">
                <thead>
                    <tr class="table-header">
                        <th>Organizer</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Seat Number</th>
                        <th>Print</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php
                   foreach ($booked_events as $event) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($event['organizer_username']) . "</td>";
                    echo "<td>" . htmlspecialchars($event['title']) . "</td>";
                    $date = date("M j", strtotime($event['date']));
                    echo "<td>" . htmlspecialchars($date) . "</td>";
                    echo "<td>" . htmlspecialchars($event['description']) . "</td>";
                    echo "<td>" . htmlspecialchars($event['seat_number']) . "</td>";

                    $jsonData = json_encode($event);
                    echo "<td><button class='btn btn-primary' onclick='printReceipt(" . htmlspecialchars($jsonData) . ", \"" . htmlspecialchars($username) . "\")'>Print Receipt</button></td>";
                    echo "</tr>";
                }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var input = document.getElementById("searchInput");
        input.addEventListener("keyup", function(event) {
            if (event.key === "Enter") {
                searchTable();
            }
        });
    });

    function searchTable() {
        var input, filter, table, tr, td, i, j, txtValue, found;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("eventsTable");
        tr = table.getElementsByTagName("tr");
        for (i = 1; i < tr.length; i++) {
            found = false;
            for (j = 0; j < tr[i].cells.length; j++) {
                td = tr[i].cells[j];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            tr[i].style.display = found ? "" : "none";
        }
    }
    
    function printReceipt(eventData, customerName) {
    try {
        var event = eventData;
        var seatPrice = 300; 
        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Receipt</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { text-align: center; }'); 
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h1>Receipt</h1>');
        printWindow.document.write('<p><strong>Title: </strong>' + event.title + '</p>');
        printWindow.document.write('<p><strong>Organizer: </strong>' + event.organizer_username + '</p>');
        printWindow.document.write('<p><strong>Date: </strong>' + event.date + '</p>');
        printWindow.document.write('<p><strong>Description: </strong>' + event.description + '</p>');
        printWindow.document.write('<p><strong>Seat Number: </strong>' + event.seat_number + '</p>');
        printWindow.document.write('<p><strong>Seat Price: </strong>₱' + seatPrice + '</p>'); 
        printWindow.document.write('<p><strong>Booked by: </strong>' + customerName + '</p>');
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    } catch (error) {
        console.error('Error printing receipt:', error);
    }
}


</script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
