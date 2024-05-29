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
if ($user_result->num_rows > 0) {
    $user_row = $user_result->fetch_assoc();
    $username = htmlspecialchars($user_row['username']);
} else {
    $username = 'Unknown Organizer';
}
$user_stmt->close();

$sql = "SELECT * FROM events WHERE organizer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$events = $stmt->get_result();

$total_sales_sql = "
    SELECT SUM(seat_count * 300) AS total_sales
    FROM (
        SELECT COUNT(*) AS seat_count
        FROM seats
        INNER JOIN events ON seats.event_id = events.id
        WHERE events.organizer_id = ? AND seats.status = 'booked'
        GROUP BY seats.event_id
    ) AS booked_seats";
$total_sales_stmt = $conn->prepare($total_sales_sql);
$total_sales_stmt->bind_param("i", $organizer_id);
$total_sales_stmt->execute();
$total_sales_result = $total_sales_stmt->get_result();
$total_sales_row = $total_sales_result->fetch_assoc();
$total_sales = $total_sales_row['total_sales'] ?? 0;
$total_sales_stmt->close();

$total_events_sql = "SELECT COUNT(*) AS total_events FROM events WHERE organizer_id = ?";
$total_events_stmt = $conn->prepare($total_events_sql);
$total_events_stmt->bind_param("i", $organizer_id);
$total_events_stmt->execute();
$total_events_result = $total_events_stmt->get_result();
$total_events_row = $total_events_result->fetch_assoc();
$total_events = $total_events_row['total_events'] ?? 0;
$total_events_stmt->close();

$may_sales_sql = "SELECT * FROM events WHERE organizer_id = ? AND MONTH(date) = 5";
$may_sales_stmt = $conn->prepare($may_sales_sql);
$may_sales_stmt->bind_param("i", $organizer_id);
$may_sales_stmt->execute();
$may_events = $may_sales_stmt->get_result();

$event_titles = [];
$event_sales = [];
$total_may_sales = 0;

while ($event = $may_events->fetch_assoc()) {
    $event_id = $event['id'];
    $event_titles[] = htmlspecialchars($event['title']);

    $sales_sql = "SELECT COUNT(*) AS booked_count FROM seats WHERE event_id = ? AND status = 'booked'";
    $sales_stmt = $conn->prepare($sales_sql);
    $sales_stmt->bind_param("i", $event_id);
    $sales_stmt->execute();
    $sales_result = $sales_stmt->get_result();
    $sales_row = $sales_result->fetch_assoc();
    $booked_count = $sales_row['booked_count'];
    $event_sales_amount = $booked_count * 300;
    $event_sales[] = $event_sales_amount;
    $total_may_sales += $event_sales_amount;
    $sales_stmt->close();
}
$may_sales_stmt->close();
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
    
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.2.2/dist/echarts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        .custom-card {
            background-color: #148a0c;
            color: white;
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
        <h1 class="text-center">Organizer Dashboard</h1>
        <div class="row">
            <div class="col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <h5 class="card-title text-center">Total Events Created</h5>
                        <div class="text-center mb-3">
                            <h6 class="card-subtitle mb-2 display-4"><?php echo $total_events; ?></h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <h5 class="card-title text-center">Overall Sales</h5>
                        <div class="text-center mb-3">
                            <h6 class="card-subtitle mb-2 display-4">₱<?php echo number_format($total_sales, 2); ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <h3 class="text-center">List of Events</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Event Date</th>
                    <th>Description</th>
                    <th>Total Sales</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $events->data_seek(0); 
                while ($event = $events->fetch_assoc()) {
                    $event_id = $event['id'];

                    $event_sales_sql = "
                        SELECT SUM(300) AS event_sales
                        FROM seats
                        WHERE event_id = ? AND status = 'booked'";
                    $event_sales_stmt = $conn->prepare($event_sales_sql);
                    $event_sales_stmt->bind_param("i", $event_id);
                    $event_sales_stmt->execute();
                    $event_sales_result = $event_sales_stmt->get_result();
                    $event_sales_row = $event_sales_result->fetch_assoc();
                    $event_sales_amount = $event_sales_row['event_sales'] ?? 0;

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($event['title']) . "</td>";
                    $event_date = date('M d', strtotime($event['date']));
                    echo "<td>" . htmlspecialchars($event_date) . "</td>";
                    echo "<td>" . htmlspecialchars($event['description']) . "</td>";
                    echo "<td>₱" . number_format($event_sales_amount, 2) . "</td>";
                    echo "<td><a href='view_booked_seats.php?event_id={$event_id}' class='btn btn-primary'>View Booked Seats</a></td>"; 
                    echo "</tr>";

                    $event_sales_stmt->close();
                }
                ?>
            </tbody>
        </table>
        <hr>
        <div class="d-flex justify-content-between align-items-center">
            <h4>Total Sales for May: ₱<?php echo number_format($total_may_sales, 2); ?></h4>
            <button class="btn btn-primary" style="width: 200px; height: 40px;" onclick="printPDF()">Print Sales Report</button>
        </div>
        <div id="salesChart" style="width: 100%; height: 400px;"></div>
    </div>
    <script>
        var myChart = echarts.init(document.getElementById('salesChart'));

        var option = {
            title: {
                text: 'Sales Chart in the Month of May',
                left: 'center'
            },
            tooltip: {},
            xAxis: {
                type: 'category',
                data: <?php echo json_encode($event_titles); ?>
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: 'Sales',
                type: 'bar',
                data: <?php echo json_encode($event_sales); ?>,
                itemStyle: {
                    color: function(params) {
                        var colorList = ['#c23531', '#2f4554', '#61a0a8', '#d48265', '#91c7ae', '#749f83', '#ca8622', '#bda29a', '#6e7074', '#546570', '#c4ccd3'];
                        return colorList[params.dataIndex % colorList.length];
                    }
                }
            }]
        };

        myChart.setOption(option);

        function printPDF() {
    try {
        var eventTitles = <?php echo json_encode($event_titles); ?>;
        var eventSales = <?php echo json_encode($event_sales); ?>;
        var totalMaySales = <?php echo json_encode(number_format($total_may_sales, 2)); ?>;
        var username = <?php echo json_encode($username); ?>;
        
        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Sales Report</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { text-align: center; font-family: Arial, sans-serif; }');
        printWindow.document.write('table { width: 80%; margin: 20px auto; border-collapse: collapse; }');
        printWindow.document.write('th, td { border: 1px solid #000; padding: 8px; }');
        printWindow.document.write('th { background-color: #f2f2f2; }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h1>Sales Report for the Month of May</h1>');
        printWindow.document.write('<h2><strong>Organizer: </strong>' + username + '</h2>');
        printWindow.document.write('<h3>Event Sales Breakdown:</h3>');
        printWindow.document.write('<table>');
        printWindow.document.write('<tr><th>Event Name</th><th>Sales Amount</th></tr>');
        
        for (var i = 0; i < eventTitles.length; i++) {
            printWindow.document.write('<tr>');
            printWindow.document.write('<td>' + eventTitles[i] + '</td>');
            printWindow.document.write('<td>₱' + eventSales[i].toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')) + '</td>';
            printWindow.document.write('</tr>');
        }
        
        printWindow.document.write('</table>');
        printWindow.document.write('<h2>Total Sales for May: ₱' + totalMaySales.replace(/\d(?=(\d{3})+\.)/g, '$&,')) + '</h2>';
        var chartDataURL = myChart.getDataURL({ pixelRatio: 2 });
        printWindow.document.write('<img src="' + chartDataURL + '" style="max-width: 100%; height: auto; margin-top: 20px;">');

        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    } catch (error) {
        console.error('Error printing sales report:', error);
    }
}
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>