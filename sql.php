<?php
/**
 * Shared MySQL connection.
 *
 * Credentials are read from environment variables / the project .env file so
 * the same code can run on XAMPP, a shared host or Docker without edits.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'config.php';

$servername = agri_env('DB_HOST', '127.0.0.1');
$username   = agri_env('DB_USER', 'root');
$password   = agri_env('DB_PASSWORD', '');
$dbname     = agri_env('DB_NAME', 'agriculture_portal');
$dbport     = (int)agri_env('DB_PORT', '3306');

if (!isset($conn) || !($conn instanceof mysqli)) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @mysqli_connect($servername, $username, $password, $dbname, $dbport);

    if (!$conn) {
        error_log('Agriculture Portal database connection failed: ' . mysqli_connect_error());
        http_response_code(500);
        exit('Unable to connect to the database. Check the DB_* values in your .env file and make sure MySQL is running.');
    }

    mysqli_set_charset($conn, 'utf8mb4');
}
