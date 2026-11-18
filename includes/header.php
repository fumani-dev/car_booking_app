<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title> <?= htmlspecialchars($title ?? 'Page', ENT_QUOTES, 'UTF-8') ?> </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <header class="hero_section">
        <div class="hero_nav_container">
            <div class="logo">
                <a href="../index.php">OpenRoad <span>SA</span></a>
            </div>

            <nav class="hero_nav">
                <a href="/index.php">Home</a>
                <a href="<?= isset($_SESSION['user_id']) ? '/signout.php' : '/login.php' ?>"><?= isset($_SESSION['user_id']) ? 'Sign out' : 'Login' ?></a>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a href="/status.php">My status</a>
                <?php endif; ?>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="/admin/dashboard.php">Dashboard</a>
                <?php endif; ?>
            </nav>
        </div>

        <p class="mini-heading">Find a ride for every trip</p>
        <h1>Welcome to openroadsa.co.za</h1>
        </section>
        <p>
            Browse our available vehicles by type and find the right fit for your next South African journey.
        </p>

        <section class="partners">
            <p class="eyebrow">In partnership with</p>
            <div class="partner-list">
                <span>Mzansi Stays</span>
                <span>Roadside Buddy</span>
                <span>Explore Local</span>
                <span>FuelStop SA</span>
            </div>
        </section>
    </header>

    <main class="container">
