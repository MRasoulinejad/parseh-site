<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $cfg = config('db');
        $pdo = new PDO($cfg['dsn'], $cfg['user'] ?? null, $cfg['pass'] ?? null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        if (str_starts_with($cfg['dsn'], 'sqlite:')) {
            $pdo->exec('PRAGMA foreign_keys = ON');
        } else {
            $pdo->exec("SET time_zone = '" . date('P') . "'");
        }
    }
    return $pdo;
}

function db_query(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute(array_values($params) === $params ? $params : $params);
    return $stmt;
}

function db_all(string $sql, array $params = []): array
{
    return db_query($sql, $params)->fetchAll();
}

function db_one(string $sql, array $params = []): ?array
{
    $row = db_query($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function db_value(string $sql, array $params = [])
{
    $v = db_query($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

function db_insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = 'INSERT INTO `' . $table . '` (`' . implode('`, `', $cols) . '`) VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')';
    db_query($sql, array_values($data));
    return (int) db()->lastInsertId();
}

function db_update(string $table, array $data, string $where, array $params = []): int
{
    $set = implode(', ', array_map(fn ($c) => "`{$c}` = ?", array_keys($data)));
    return db_query("UPDATE `{$table}` SET {$set} WHERE {$where}", array_merge(array_values($data), $params))->rowCount();
}

function db_find(string $table, int $id): ?array
{
    return db_one("SELECT * FROM `{$table}` WHERE id = ?", [$id]);
}
