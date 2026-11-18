CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_iNCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    type VARCHAR(100) NOT NULL,
    seats INT NOT NULL,
    price_per_day DECIMAL (8, 2) NOT NULL,
    total_cars INT NOT NULL,
    available_cars INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'client'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    user_id INT NULL,
    customer_name VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    booked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT IGNORE INTO cars (name, type, seats, price_per_day, total_cars, available_cars) VALUES
    ('VW Tiguan', 'SUV', 5, 850.00, 5, 5),
    ('Toyota Corolla', 'Sedan', 5, 600.00, 3, 3),
    ('Toyota Quantum', 'Van', 10, 1200.00, 2, 2),
    ('Toyota Hilux', 'Pickup', 5, 1000.00, 4, 4),
    ('Volkswagen Polo', 'Hatchback', 5, 500.00, 5, 5),
    ('Toyota Fortuner', 'SUV', 7, 1100.00, 3, 3),
    ('Suzuki Swift', 'Hatchback', 5, 450.00, 4, 4),
    ('Ford Ranger', 'Pickup', 2, 1050.00, 3, 3),
    ('Toyota Corolla Cross', 'SUV', 5, 750.00, 3, 3);
