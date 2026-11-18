<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$mysqli = require __DIR__ . '/includes/database.php';
$userId = (int) $_SESSION['user_id'];
$username = (string) ($_SESSION['username'] ?? 'User');
$title = 'My Bookings';
$dashboard_css_version = filemtime(__DIR__ . '/css/dashboard.css') ?: time();

$bookings = $mysqli->prepare(
    "SELECT bookings.id, bookings.customer_name, bookings.status, bookings.booked_at, cars.name AS car_name
     FROM bookings
     JOIN cars ON bookings.car_id = cars.id
     WHERE bookings.user_id = ? OR bookings.customer_name = ?
     ORDER BY bookings.booked_at DESC"
);
$bookings->bind_param('is', $userId, $username);
$bookings->execute();
$bookingsResult = $bookings->get_result();
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
                <a href="/index.php">OpenRoad <span>SA</span></a>
                <p>| <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <nav class="dashboard_nav">
                <a href="/index.php">Home</a>
                <a href="<?= isset($_SESSION['user_id']) ? '/signout.php' : '/login.php' ?>"><?= isset($_SESSION['user_id']) ? 'Sign out' : 'Login' ?></a>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="/admin/dashboard.php">Dashboard</a>
                <?php endif; ?>
            </nav>
    </header>

    <main>

    <h1 class="welcome-message">Bookings, <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></h1>
    <section class="orders">
        <table>
            <tr>
                <th>ID</th>
                <th>Car</th>
                <th>Booked At</th>
                <th>Status</th>
            </tr>

            <?php if ($bookingsResult && $bookingsResult->num_rows > 0): ?>
                <?php while ($booking = $bookingsResult->fetch_assoc()): ?>
                    <tr>
                        <td class="id-col"><?= htmlspecialchars($booking['id'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($booking['car_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($booking['booked_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="status"><span class="status-label status-<?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">You do not have any bookings yet.</td>
                </tr>
            <?php endif; ?>
        </table>
    </section>
        
<?php require __DIR__ . '/includes/footer.php';
