<?php
include 'db_connect.php';

$events = $conn->query("SELECT id FROM events");

while ($event = $events->fetch_assoc()) {
    $event_id = $event['id'];
    for ($i = 1; $i <= 35; $i++) {
        $conn->query("INSERT INTO seats (event_id, seat_number) VALUES ($event_id, $i)");
    }
}

echo "Seats initialized.";
?>
