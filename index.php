<?php
    session_start();
    $mysqli = require __DIR__ . '/includes/database.php';
    $title = 'Main Page';
    $selectedType = trim($_GET['type'] ?? '');
    $selectedSeats = trim($_GET['seats'] ?? '');
    $vehicles = $mysqli->query("SELECT * FROM cars ORDER BY type, name");
    $vehiclesByType = [];
    $vehicleTypes = [];
    $passengerOptions = [];
    while ($vehicle = $vehicles->fetch_assoc()) {
        $vehicleTypes[$vehicle['type']] = $vehicle['type'];
        
        $passengerOptions[$vehicle['seats']] = $vehicle['seats'];

        $matchesType = $selectedType === '' || $vehicle['type'] === $selectedType;
        $matchesSeats = $selectedSeats === '' || (int) $vehicle['seats'] === (int) $selectedSeats;
        if ($matchesType && $matchesSeats) {
            $vehiclesByType[$vehicle['type']][] = $vehicle;
        }
    }
    sort($vehicleTypes);
    sort($passengerOptions, SORT_NUMERIC);
    require __DIR__ . '/includes/header.php';
?>

<h1 style="margin: 2rem 0;">Cheap car hire in Nelspruit</h1>

<form method="get" action="index.php#car-results">
    <div class="filter-search-bar">
        <div class="field-container">
            <label for="car-type">Car type</label>
            <select id="car-type" name="type">
                <option value="">All types</option>
                <?php foreach ($vehicleTypes as $type): ?>
                <option value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"
                    <?= $selectedType === $type ? 'selected' : '' ?>>
                    <?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field-container">
            <label for="passengers">Seats</label>
            <select id="passengers" name="seats">
                <option value="">Any number of seats</option>
                <?php foreach ($passengerOptions as $seats): ?>
                <option value="<?= (int) $seats ?>" <?= $selectedSeats === (string) $seats ? 'selected' : '' ?>>
                    <?= (int) $seats ?> seats
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field-container last-container">
            <label for="search"></label>
            <input type="submit" id="search" value="Apply filters">
        </div>
    </div>
</form>

<div id="car-results">
    <?php
        $bookingStatus = $_GET['booking'] ?? '';
        $bookingMessages = [
            'success' => ['Your booking is confirmed. One vehicle has been reserved for your account.', 'status'],
            'login_required' => ['Please <a href="login.php">log in</a> before booking a car.', 'alert'],
            'unavailable' => ['That car is no longer available. Please choose another car.', 'alert'],
            'invalid' => ['The booking request was invalid. Please try again.', 'alert'],
            'error' => ['We could not complete your booking. Please try again.', 'alert'],
        ];
    ?>
    <?php if (isset($bookingMessages[$bookingStatus])): ?>
    <p class="booking-message" role="<?= $bookingMessages[$bookingStatus][1] ?>">
        <?= $bookingMessages[$bookingStatus][0] ?>
    </p>
    <?php endif; ?>

    <?php if (empty($vehiclesByType)): ?>
    <p role="status">No cars match those filters. Try different options.</p>
    <?php endif; ?>

    <?php foreach ($vehiclesByType as $type => $cars): ?>
    <section class="category">
        <div class="section-heading">
            <h2><?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= count($cars) ?> <?= count($cars) === 1 ? 'vehicle' : 'vehicles' ?></p>
        </div>

        <div class="cars">
            <?php foreach ($cars as $vehicle): ?>
            <article class="car">
                <p class="badge"><?= (int) $vehicle['available_cars'] ?> available</p>
                <div class="photo">
                    <img src="images/<?= htmlspecialchars($vehicle['name'], ENT_QUOTES, 'UTF-8')?>.png"
                        alt="<?= htmlspecialchars($vehicle['name'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <h3><?= htmlspecialchars($vehicle['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <div class="details">
                    <p><?= (int) $vehicle['seats'] ?> seats</p>
                    <p class="price">R<?= htmlspecialchars($vehicle['price_per_day'], ENT_QUOTES, 'UTF-8') ?> <span>per
                            day</span></p>
                    <form method="post" action="booking.php">
                        <input type="hidden" name="car_id" value="<?= (int) $vehicle['id'] ?>">
                        <button type="submit" <?= (int) $vehicle['available_cars'] < 1 ? 'disabled' : '' ?>>
                            <?= (int) $vehicle['available_cars'] < 1 ? 'Unavailable' : 'Book Now' ?>
                        </button>
                    </form>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>
</div>

<section class="rental-info">
    <h2>Safety and how renting works</h2>
    <p>Choose a vehicle by type, seats, price and availability, then confirm the final price, insurance, deposit and
        pickup details with the rental provider before booking. At collection, review the rental agreement, check the
        vehicle’s condition with the provider and record any existing damage. Make sure you have a valid driving licence
        and understand the terms before setting off.</p>
</section>
<div></div>

<?php
    require __DIR__ . '/includes/footer.php';
?>
