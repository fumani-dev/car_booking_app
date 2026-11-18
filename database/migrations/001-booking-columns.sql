SET @statement = IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE() AND table_name = 'bookings' AND column_name = 'status'
    ),
    'DO 0',
    'ALTER TABLE bookings ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT ''pending'''
);
PREPARE migration FROM @statement;
EXECUTE migration;
DEALLOCATE PREPARE migration;

SET @statement = IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE() AND table_name = 'bookings' AND column_name = 'user_id'
    ),
    'DO 0',
    'ALTER TABLE bookings ADD COLUMN user_id INT NULL AFTER car_id'
);
PREPARE migration FROM @statement;
EXECUTE migration;
DEALLOCATE PREPARE migration;

UPDATE bookings
JOIN users ON users.username = bookings.customer_name
SET bookings.user_id = users.id
WHERE bookings.user_id IS NULL;
