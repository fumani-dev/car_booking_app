<?php
session_start();

// A booking must come from the car form and belong to a signed-in user.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?booking=invalid#car-results');
    exit;
}

if (empty($_SESSION['user_id'])) {
    header('Location: index.php?booking=login_required#car-results');
    exit;
}

$carId = filter_var($_POST['car_id'] ?? null, FILTER_VALIDATE_INT);
if (!$carId || $carId < 1) {
    header('Location: index.php?booking=invalid#car-results');
    exit;
}

$mysqli = require __DIR__ . '/includes/database.php';
$userId = (int) $_SESSION['user_id'];
$customerName = (string) ($_SESSION['username'] ?? 'Customer');

try {
    $status = 'pending';
    $booking = $mysqli->prepare('INSERT INTO bookings (car_id, user_id, customer_name, status) VALUES (?, ?, ?, ?)');
    $booking->bind_param('iiss', $carId, $userId, $customerName, $status);
    $booking->execute();
    $booking->close();

    header('Location: index.php?booking=success#car-results');
    exit;
} catch (Throwable $exception) {
    error_log('Booking failed: ' . $exception->getMessage());
    header('Location: index.php?booking=error#car-results');
    exit;
}
