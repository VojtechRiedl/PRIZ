<?php

declare(strict_types=1);

// Vytvoří a znovu používá jedno PDO připojení k PostgreSQL.
// Statická proměnná funguje jako jednoduchá cache, takže během jednoho requestu
// nevzniká nové připojení při každém databázovém dotazu.
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = env_value('DB_HOST', 'db');
    $port = env_value('DB_PORT', '5432');
    $name = env_value('DB_NAME', 'library');
    $user = env_value('DB_USER', 'postgres');
    $password = env_value('DB_PASSWORD', 'postgres');

    // DSN říká PDO, kam se má připojit. Hodnoty jdou z prostředí, aby stejný kód
    // fungoval lokálně, v Dockeru i na jiném serveru.
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $name);
    $pdo = new PDO($dsn, $user, $password, [
        // Výjimky zjednodušují zachycení chyb v try/catch blocích stránek.
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Dotazy vrací asociativní pole, například ['title' => '...'].
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Vypnutí emulace připravených dotazů nechává parametry řešit PostgreSQL.
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
