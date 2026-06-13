<?php
header('Content-Type: application/json');

// Handle GET requests to let the frontend know what is already booked
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists('appointments.txt')) {
        echo json_encode(file('appointments.txt', FILE_IGNORE_NEW_LINES));
    } else {
        echo json_encode([]);
    }
    exit;
}

// Handle POST requests to save new appointments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = trim($_POST['booking_date'] ?? '');
    $time = trim($_POST['booking_time'] ?? '');
    $name = trim($_POST['name'] ?? 'N/A');
    $service = trim($_POST['service'] ?? 'N/A');
    $issue = trim($_POST['issue_description'] ?? 'N/A');

    // Basic validation
    if (empty($date) || empty($time)) {
        echo json_encode(["success" => false, "message" => "Error: Date or Time is missing."]);
        exit;
    }

    $file = 'appointments.txt';
    
    // Check for double booking
    if (file_exists($file)) {
        $bookings = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($bookings as $booking) {
            // Check if this exact Date AND Time combo exists in the line
            if (strpos($booking, "Date: $date | Time: $time") !== false) {
                echo json_encode(["success" => false, "message" => "This slot is already booked! Please select another."]);
                exit;
            }
        }
    }
    
    // Format the log line (sanitize input to avoid breaking the file structure)
    $logLine = sprintf(
        "Date: %s | Time: %s | Name: %s | Service: %s | Issue: %s",
        $date, $time, $name, $service, $issue
    );
    
    file_put_contents($file, $logLine . PHP_EOL, FILE_APPEND);
    
    echo json_encode(["success" => true, "message" => "Appointment saved successfully!"]);
}
?>