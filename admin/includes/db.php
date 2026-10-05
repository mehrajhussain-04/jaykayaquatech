<?php
/**
 * Database connection (MySQL / MariaDB via PDO).
 *
 * Reads credentials from the single centralized admin/includes/db-config.php
 * file — nothing else in the app hardcodes host/user/password, and nothing
 * touches JavaScript. All queries elsewhere use PDO prepared statements.
 *
 * Convenience safety-net: if the configured database has no tables yet
 * (e.g. someone forgot the phpMyAdmin import step), we import database.sql
 * automatically on the very first request. Once tables exist this is a
 * no-op every time after, so it's safe to leave in place for production.
 */

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $config = require __DIR__ . '/db-config.php';

    $dsn = sprintf(
        '%s:host=%s;port=%s;dbname=%s;charset=%s',
        $config['driver'],
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );

    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        // Never leak connection details to visitors/admins in the error output.
        error_log('Jay Kay Aqua Tech DB connection failed: ' . $e->getMessage());
        http_response_code(500);
        die('Database connection failed. Please check admin/includes/db-config.php and confirm database.sql has been imported. (Details were written to the server error log, not shown here for security.)');
    }

    ensure_schema_imported($pdo);

    return $pdo;
}

function ensure_schema_imported(PDO $pdo): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        $tables = [];
    }

    if (!empty($tables)) {
        return; // schema already present — nothing to do.
    }

    $sqlFile = dirname(__DIR__, 2) . '/database.sql';
    if (!file_exists($sqlFile)) {
        return;
    }

    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        return;
    }

    try {
        // PDO::exec() can't run a script with multiple statements reliably
        // across all drivers, so split on statement-terminating semicolons
        // that end a line (our file's own formatting is consistent enough
        // for this, since it's fully controlled/authored by this project).
        $statements = array_filter(array_map('trim', explode(";\n", $sql)));
        $pdo->beginTransaction();
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '' || str_starts_with($statement, '--')) {
                continue;
            }
            $pdo->exec($statement);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Jay Kay Aqua Tech: automatic schema import failed: ' . $e->getMessage());
    }
}
