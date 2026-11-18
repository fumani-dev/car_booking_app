<?php
    session_start();
    $mysqli = require __DIR__ . '/../includes/database.php';
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied. Administrator access is required.');
    }

    $bookings = $mysqli->query(
           "SELECT bookings.id, bookings.customer_name, bookings.status, bookings.booked_at, cars.name AS car_name
         FROM bookings
         JOIN cars ON bookings.car_id = cars.id
            WHERE bookings.status = 'pending'
         ORDER BY bookings.booked_at DESC"
    );

    $title = "Dashboard";
    $dashboard_css_version = filemtime(__DIR__ . '/../css/dashboard.css') ?: time();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> | OpenRoad SA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/dashboard.css?v=<?= urlencode((string) $dashboard_css_version) ?>">
</head>

<body>

    <header class="dashboard_header">
            <div class="logo" style="display: flex; gap: 0.50rem;">
                <a href="../index.php">OpenRoad <span>SA</span></a>
                <p>| <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <nav class="dashboard_nav">
                <a href="/index.php">Home</a>
                <a
                    href="<?= isset($_SESSION['user_id']) ? '/signout.php' : '/login.php' ?>"><?= isset($_SESSION['user_id']) ? 'Sign out' : 'Login' ?></a>
                <a href="/admin/summary.php">Summary</a>
            </nav>
    </header>

    <main>

    <h1 class="welcome-message">Welcome, <?= $_SESSION['username'] ?></h1>
    <section class="orders">
        <table>
            <tr>
                <th>ID</th>
                <th>Car</th>
                <th class="customer-col">Customer</th>
                <th>Booked At</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php if ($bookings && $bookings->num_rows > 0): ?>
                <?php while ($booking = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td class="id-col"><?= htmlspecialchars($booking['id'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($booking['car_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="customer-col"><?= htmlspecialchars($booking['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($booking['booked_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="action">
                            <form method="post" action="/admin/booking_action.php">
                                    <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                    <button type="submit" name="action" value="approve">Approve</button>
                                    <button type="submit" name="action" value="decline">Decline</button>
                                </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No pending bookings found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </section>
        
<?php require __DIR__ . '/../includes/footer.php';
