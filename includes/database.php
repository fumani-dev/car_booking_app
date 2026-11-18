<?php

$config = [];
foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'] as $key) {
    $value = getenv($key);
    if ($value === false || $value === '') {
        error_log("Missing database configuration: $key");
        http_response_code(500);
        exit('Database connection failed.');
    }
    $config[$key] = $value;
}

$port = filter_var(getenv('DB_PORT') ?: '3306', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 65535],
]);
if ($port === false) {
    error_log('Invalid database configuration: DB_PORT');
    http_response_code(500);
    exit('Database connection failed.');
}

try {
    return new mysqli(
        $config['DB_HOST'],
        $config['DB_USER'],
        $config['DB_PASSWORD'],
        $config['DB_NAME'],
        $port
    );
} catch (mysqli_sql_exception $exception) {
    error_log('Database connection failed.');
    http_response_code(500);
    exit('Database connection failed.');
}
