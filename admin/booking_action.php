<?php
session_start();

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied. Administrator access is required.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/dashboard.php');
    exit;
}

$bookingId = filter_var($_POST['booking_id'] ?? null, FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

if (!$bookingId || $bookingId < 1 || !in_array($action, ['approve', 'decline'], true)) {
    header('Location: /admin/dashboard.php?booking_action=invalid');
    exit;
}

$mysqli = require __DIR__ . '/../includes/database.php';

try {
    $mysqli->begin_transaction();

    $bookingQuery = $mysqli->prepare('SELECT car_id, status FROM bookings WHERE id = ? FOR UPDATE');
    $bookingQuery->bind_param('i', $bookingId);
    $bookingQuery->execute();
    $booking = $bookingQuery->get_result()->fetch_assoc();
    $bookingQuery->close();

    if (!$booking) {
        $mysqli->rollback();
        header('Location: /admin/dashboard.php?booking_action=missing');
        exit;
    }

    if ($booking['status'] !== 'pending') {
        $mysqli->rollback();
        header('Location: /admin/dashboard.php?booking_action=already_processed');
        exit;
    }

    if ($action === 'approve') {
        $carQuery = $mysqli->prepare('SELECT available_cars FROM cars WHERE id = ? FOR UPDATE');
        $carQuery->bind_param('i', $booking['car_id']);
        $carQuery->execute();
        $car = $carQuery->get_result()->fetch_assoc();
        $carQuery->close();

        if (!$car || (int) $car['available_cars'] < 1) {
            $mysqli->rollback();
            header('Location: /admin/dashboard.php?booking_action=unavailable');
            exit;
        }

        $approveBooking = $mysqli->prepare('UPDATE bookings SET status = "approved" WHERE id = ?');
        $approveBooking->bind_param('i', $bookingId);
        $approveBooking->execute();
        $approveBooking->close();

        $updateInventory = $mysqli->prepare('UPDATE cars SET available_cars = available_cars - 1 WHERE id = ?');
        $updateInventory->bind_param('i', $booking['car_id']);
        $updateInventory->execute();
        $updateInventory->close();

        $mysqli->commit();
        header('Location: /admin/dashboard.php?booking_action=approved');
        exit;
    }

    $declineBooking = $mysqli->prepare('UPDATE bookings SET status = "declined" WHERE id = ?');
    $declineBooking->bind_param('i', $bookingId);
    $declineBooking->execute();
    $declineBooking->close();

    $mysqli->commit();
    header('Location: /admin/dashboard.php?booking_action=declined');
    exit;
} catch (Throwable $exception) {
    if ($mysqli->errno === 0) {
        $mysqli->rollback();
    }

    error_log('Booking status update failed: ' . $exception->getMessage());
    header('Location: /admin/dashboard.php?booking_action=error');
    exit;
}