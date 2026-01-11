<?php

use PDO;
use PDOException;
use PDOStatement;
/**
 * Lightweight PDO-based shim for environments where the sqlsrv extension
 * is not installed. It provides the subset of sqlsrv_* functions that the
 * application relies on so the rest of the codebase can continue to use
 * the same API regardless of driver availability.
 */

if (!defined('SQLSRV_FETCH_ASSOC')) {
    define('SQLSRV_FETCH_ASSOC', 2);
}

if (!defined('SQLSRV_ERR_ERRORS')) {
    define('SQLSRV_ERR_ERRORS', 0);
}

if (!defined('SQLSRV_AUTH_NEGOTIATE')) {
    define('SQLSRV_AUTH_NEGOTIATE', 2);
}

final class SqlsrvShim
{
    /** @var array<int, array{SQLSTATE:string,message:string,code:int}> */
    public static array $errors = [];

    public static function recordError(string $sqlState, string $message, int $code = 0): void
    {
        self::$errors[] = [
            'SQLSTATE' => $sqlState,
            'message' => $message,
            'code' => $code,
        ];
    }

    public static function clearErrors(): void
    {
        self::$errors = [];
    }
}

final class SqlsrvShimStatement
{
    public PDOStatement $pdoStatement;
    /** @var array<int, mixed> */
    public array $params;

    /**
     * @param PDOStatement $pdoStatement
     * @param array<int, mixed> $params
     */
    public function __construct(PDOStatement $pdoStatement, array $params)
    {
        $this->pdoStatement = $pdoStatement;
        $this->params = $params;
    }
}

/**
 * @param string $serverName
 * @param array<string, mixed> $connectionOptions
 * @return PDO|false
 */
function sqlsrv_connect($serverName, array $connectionOptions)
{
    SqlsrvShim::clearErrors();
    $parts = ['sqlsrv:Server=' . $serverName];

    if (!empty($connectionOptions['Database'])) {
        $parts[] = 'Database=' . $connectionOptions['Database'];
    }
    if (!empty($connectionOptions['CharacterSet'])) {
        $parts[] = 'CharacterSet=' . $connectionOptions['CharacterSet'];
    }
    if (!empty($connectionOptions['TrustServerCertificate'])) {
        $parts[] = 'TrustServerCertificate=1';
    }

    $integrated = !empty($connectionOptions['IntegratedSecurity']);
    if ($integrated) {
        $parts[] = 'Integrated Security=SSPI';
    }

    $dsn = implode(';', $parts);

    $user = $integrated ? null : ($connectionOptions['UID'] ?? null);
    $password = $integrated ? null : ($connectionOptions['PWD'] ?? null);

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    if (defined('PDO::SQLSRV_ATTR_DIRECT_QUERY')) {
        $options[PDO::SQLSRV_ATTR_DIRECT_QUERY] = true;
    }

    try {
        return new PDO($dsn, $user, $password, $options);
    } catch (PDOException $e) {
        SqlsrvShim::recordError($e->getCode() ?: 'IMSSP', $e->getMessage(), (int)$e->getCode());
        return false;
    }
}

/**
 * @param PDO $conn
 * @param string $sql
 * @param array<int, mixed> $params
 * @param array<string, mixed> $options
 * @return SqlsrvShimStatement|false
 */
function sqlsrv_prepare($conn, $sql, array $params = [], array $options = [])
{
    SqlsrvShim::clearErrors();
    try {
        $stmt = $conn->prepare($sql, $options);
        return new SqlsrvShimStatement($stmt, $params);
    } catch (PDOException $e) {
        SqlsrvShim::recordError($e->getCode() ?: 'IMSSP', $e->getMessage(), (int)$e->getCode());
        return false;
    }
}

/**
 * @param SqlsrvShimStatement $stmt
 * @return bool
 */
function sqlsrv_execute($stmt)
{
    SqlsrvShim::clearErrors();
    if (!($stmt instanceof SqlsrvShimStatement)) {
        return false;
    }

    try {
        return $stmt->pdoStatement->execute($stmt->params);
    } catch (PDOException $e) {
        SqlsrvShim::recordError($e->getCode() ?: 'IMSSP', $e->getMessage(), (int)$e->getCode());
        return false;
    }
}

/**
 * @param SqlsrvShimStatement $stmt
 * @param int $fetchType
 * @return array<string, mixed>|null
 */
function sqlsrv_fetch_array($stmt, $fetchType = SQLSRV_FETCH_ASSOC)
{
    if (!($stmt instanceof SqlsrvShimStatement)) {
        return null;
    }

    $mode = $fetchType === SQLSRV_FETCH_ASSOC ? PDO::FETCH_ASSOC : PDO::FETCH_BOTH;
    $row = $stmt->pdoStatement->fetch($mode);
    return $row === false ? null : $row;
}

/**
 * Mirrors sqlsrv_errors by returning the last captured error set (if any).
 *
 * @return array<int, array{SQLSTATE:string,message:string,code:int}>|null
 */
function sqlsrv_errors($errorsOrWarnings = SQLSRV_ERR_ERRORS)
{
    return SqlsrvShim::$errors ?: null;
}

