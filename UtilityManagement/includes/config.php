<?php
/**
 * Global configuration for the Utility Management System.
 * Updates:
 *  - Adjust DB_SERVER/DB_USERNAME/DB_PASSWORD to match your SQL Server.
 *  - Ensure the sqlsrv PHP extension is enabled.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('sqlsrv_connect')) {
    require_once __DIR__ . '/sqlsrv_shim.php';
}

if (!defined('SQLSRV_AUTH_NEGOTIATE')) {
    define('SQLSRV_AUTH_NEGOTIATE', 2);
}

define('DB_SERVER', 'DESKTOP-QDH7DM4\SQLEXPRESS');
define('DB_USERNAME', 'sa');
define('DB_PASSWORD', '12345');
define('DB_NAME', 'UtilityManagementSystem');
define('DB_USE_WINDOWS_AUTH', false);
define('APP_NAME', 'Utility Management System');

$connectionOptions = [
    'Database' => DB_NAME,
    'CharacterSet' => 'UTF-8',
    'TrustServerCertificate' => true,
];

if (DB_USE_WINDOWS_AUTH) {
    $connectionOptions['IntegratedSecurity'] = true;
    $connectionOptions['Authentication'] = SQLSRV_AUTH_NEGOTIATE;
} else {
    $connectionOptions['UID'] = DB_USERNAME;
    $connectionOptions['PWD'] = DB_PASSWORD;
}

$conn = sqlsrv_connect(DB_SERVER, $connectionOptions);

if (!$conn) {
    die('Database connection failed: ' . print_r(sqlsrv_errors(), true));
}

function db_last_error(): string
{
    $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
    if (!$errors) {
        return 'Unknown error';
    }
    $messages = array_map(
        fn($err) => '[' . $err['SQLSTATE'] . '] ' . $err['message'],
        $errors
    );
    return implode('; ', $messages);
}

