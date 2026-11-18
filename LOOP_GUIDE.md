# How the vehicle loops work

This guide explains the loops in `index.php` that take the rows from the `cars` table and display them as sections of vehicle cards.

## The whole process

```text
Database rows
    |
    v
Read each car and put it into a group named after its type
    |
    v
For each type, print a section heading
    |
    v
For each car in that type, print one card
```

For example, if the database has these cars:

```text
Toyota Corolla       Sedan
Volkswagen Polo      Hatchback
Toyota Camry         Sedan
Suzuki Swift         Hatchback
```

The page groups them like this before displaying them:

```text
$vehiclesByType
|
+-- Sedan
|   +-- Toyota Camry
|   +-- Toyota Corolla
|
+-- Hatchback
    +-- Suzuki Swift
    +-- Volkswagen Polo
```

## 1. Get the rows from the database

```php
$vehicles = $mysqli->query("SELECT * FROM cars ORDER BY type, name");
```

The SQL asks the database for every car. `ORDER BY type, name` sorts the results by type first, and then by name within each type. Sorting makes the page predictable; it does not do the grouping by itself.

## 2. Group cars by their type

```php
$vehiclesByType = [];
while ($vehicle = $vehicles->fetch_assoc()) {
    $vehiclesByType[$vehicle['type']][] = $vehicle;
}
```

- `$vehiclesByType = [];` starts with an empty grouping.
- `fetch_assoc()` gets one database row as an associative array. For example, `$vehicle['name']` is the car's name and `$vehicle['type']` is its type.
- `while` repeats while there is another row to read. When there are no rows left, `fetch_assoc()` returns `null` and the loop ends.
- `$vehiclesByType[$vehicle['type']][] = $vehicle;` adds the current car to the list for its type. The `[]` means “append this car to the end of the list.”

After the first two Sedan cars, the data is conceptually:

```php
$vehiclesByType['Sedan'] = [
    $firstSedanCar,
    $secondSedanCar,
];
```

## 3. Print one section for each type

```php
foreach ($vehiclesByType as $type => $cars):
```

`foreach` visits each group. On each visit:

- `$type` is the group name, such as `Sedan`.
- `$cars` is the list of cars that belong to that type.

The section heading prints `$type`, and `count($cars)` prints how many cars are in the group.

## 4. Print one card for each car in that section

```php
<?php foreach ($cars as $vehicle): ?>
    <article class="car-card">
        <h3><?= htmlspecialchars($vehicle['name'], ENT_QUOTES, 'UTF-8') ?></h3>
        <p><?= (int) $vehicle['seats'] ?> seats</p>
    </article>
<?php endforeach; ?>
```

This inner `foreach` runs once for every car in the current type. Each run prints one card. The real card also prints its image, availability, price, and button.

The outer loop makes sections; the inner loop makes cards inside a section:

```text
outer loop: Sedan
    inner loop: Toyota Camry card
    inner loop: Toyota Corolla card

outer loop: SUV
    inner loop: Toyota Fortuner card
    inner loop: VW Tiguan card
```

## Why some values use `htmlspecialchars()` or `(int)`

Car names and types come from the database. `htmlspecialchars()` safely displays text in HTML, so characters in a name are treated as text rather than markup. Values such as seat counts and availability are converted to integers with `(int)` before display.

## In short

The `while` loop reads and groups database rows. The outer `foreach` prints a section for each group. The inner `foreach` prints a card for each car in that section.
