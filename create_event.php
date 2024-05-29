<?php
session_start();
include 'db_connect.php';

if ($_SESSION['role'] != 'organizer') {
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
$username = $user_row['username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $total_seats = 35;


    $check_sql = "SELECT COUNT(*) AS count FROM events WHERE date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $date);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $count_row = $check_result->fetch_assoc();
    $event_count = $count_row['count'];

    if ($event_count > 0) {
        echo "<script>var errorMessage = 'An event already exists for the selected date. Please choose a different date.';</script>";
    } else {
        $conn->begin_transaction();

        try {

            $sql = "INSERT INTO events (title, date, description, organizer_id, total_seats) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssii", $title, $date, $description, $organizer_id, $total_seats);
            $stmt->execute();
            

            $event_id = $stmt->insert_id;

            $seat_sql = "INSERT INTO seats (event_id, seat_number, status) VALUES (?, ?, 'available')";
            $seat_stmt = $conn->prepare($seat_sql);
            
            for ($i = 1; $i <= 35; $i++) {
                $seat_stmt->bind_param("ii", $event_id, $i);
                $seat_stmt->execute();
            }

            $conn->commit();
            header('Location: organizer_dashboard.php');
        } catch (Exception $e) {

            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
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
        <a href="organizer_dashboard.php"><i class="fa-solid fa-chart-line fa-xl"></i> Dashboard</a>
        <a href="view_events_organizer.php"><i class="fa-solid fa-calendar fa-xl"></i> View Events</a>
        <a href="create_event.php"><i class="fa-solid fa-clipboard-list fa-xl"></i> Create Event</a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket fa-xl"></i> Logout</a>
    </div>

    <div class="main-content">
    <h2 class="text-center">Create Event</h2>
    <form method="POST" class="form-inline" id="eventForm">
        <div class="form-group mb-2">
            <input type="text" class="form-control" name="title" placeholder="Title" required >
        </div>
        <div class="form-group mx-sm-3 mb-2">
            <input type="date" class="form-control" name="date" required>           
        </div>
        <div class="form-group mx-sm-3 mb-2">
            <input type="text" class="form-control" name="description" placeholder="description" required>
        </div>
        <button type="submit" class="btn btn-primary mb-2">Create Event</button>
    </form>
</div>
<script>
    document.querySelector(".main-content form").addEventListener("submit", function(event) {
        const titleInput = document.getElementsByName("title")[0];
        const descriptionInput = document.getElementsByName("description")[0];
        if (titleInput.value.trim() === "") {
            alert("Please enter a title.");
            event.preventDefault(); 
        }
        if (descriptionInput.value.trim() === "") {
            alert("Please enter a description.");
            event.preventDefault(); 
        }
        const dateValue = dateInput.value;
        const isValidDate = !isNaN(Date.parse(dateValue)); 
        const inputDate = new Date(dateValue);
        const year = inputDate.getFullYear();
        
        if (!isValidDate || year !== 2024) {
            document.getElementById("dateError").textContent =
            event.preventDefault(); 
        } else {
            document.getElementById("dateError").textContent = ""; 
        }
    });
</script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    <script>  
    if (typeof errorMessage !== 'undefined') {
        document.querySelector('input[name="date"]').setCustomValidity(errorMessage);
        document.querySelector('input[name="date"]').reportValidity(); 
        errorMessage = undefined;
    }
</script>

</body>
</html>
<?php
$conn->close();
?>
