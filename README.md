# OpenRoad SA

OpenRoad SA is a car booking app built with PHP and MySQL. Customers can browse and filter the fleet, create an account, request a booking, and check its status. Administrators can approve or decline requests and view a fleet summary.

## Run locally

Install Docker with the Compose plugin. If you do not have a local `.env` yet, copy the template.

```sh
cp .env.example .env
chmod 600 .env
openssl rand -hex 24
openssl rand -hex 24
```

Set `DB_PASSWORD` and `MYSQL_ROOT_PASSWORD` to the two independently generated values. Use letters, digits, and underscores for `DB_NAME` and `DB_USER`. Keep `.env` local. If you already have a MySQL volume from an earlier version, follow the upgrade steps below before starting PHP.

```sh
docker compose up --build -d
```

Open [OpenRoad SA](http://localhost:8080). MySQL creates the database, seeds the fleet, and grants the app account access to the tables it uses when it initializes a fresh volume.

To stop the services, run:

```sh
docker compose down
```

The MySQL named volume preserves data between runs.

## Try the booking flow

1. Choose **Create an account** on the [login page](http://localhost:8080/login.php).
2. Browse the fleet and submit a booking request.
3. Open the [booking status page](http://localhost:8080/status.php) to see the result.

New accounts have the client role. To try the admin flow locally, sign up with a dedicated test account and promote it in MySQL. Replace `admin@example.test` with the address you used.

```sh
docker compose exec mysql mysql -u root -p OPENROAD_DB
```

Enter the password from `MYSQL_ROOT_PASSWORD`, then run:

```sql
UPDATE users SET role = 'admin' WHERE email = 'admin@example.test';
```

Sign out and back in, then open the [admin dashboard](http://localhost:8080/admin/dashboard.php).

## Upgrade an existing MySQL volume

Keep `DB_NAME` set to the name of your existing database and set `MYSQL_ROOT_PASSWORD` to its current root password. Earlier versions used `OPENROAD_DB` and `root` by default. MySQL applies its initialization variables and scripts only to fresh volumes.

Start MySQL and open a root session. Substitute your database name if it differs.

```sh
docker compose up -d mysql
docker compose exec -e MYSQL_HISTFILE=/dev/null mysql mysql -u root -p OPENROAD_DB
```

Apply the [legacy booking migration](database/migrations/001-booking-columns.sql) and create the app account in that session. Substitute your configured user, password, and database name.

```sql
SOURCE /migrations/001-booking-columns.sql;
CREATE USER IF NOT EXISTS 'openroad_app'@'%' IDENTIFIED BY 'REPLACE_WITH_DB_PASSWORD';
ALTER USER 'openroad_app'@'%' IDENTIFIED BY 'REPLACE_WITH_DB_PASSWORD';
GRANT SELECT, INSERT ON OPENROAD_DB.users TO 'openroad_app'@'%';
GRANT SELECT, UPDATE ON OPENROAD_DB.cars TO 'openroad_app'@'%';
GRANT SELECT, INSERT, UPDATE ON OPENROAD_DB.bookings TO 'openroad_app'@'%';
```

Exit the MySQL client, then run `docker compose up --build -d`. For production, inject `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` into PHP through the hosting platform's secret manager. Set `DB_PORT` if MySQL uses a port other than 3306. Apply migrations with a separate database administrator account.

## Project layout

| Path | Purpose |
| --- | --- |
| `index.php`, `booking.php`, `status.php` | Fleet listing, booking requests, and customer booking status |
| `login.php`, `signout.php` | Account creation, login, and logout |
| `admin/` | Booking review and fleet summary |
| `includes/` | Database connection and shared page elements |
| `database/setup.sql` | Database schema and sample fleet |
| `css/`, `images/` | Styles and image assets |
