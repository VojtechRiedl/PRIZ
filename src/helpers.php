<?php

declare(strict_types=1);

// Vrátí hodnotu proměnné prostředí. Nejdřív zkouší getenv(), potom pole $_ENV
// a nakonec použije výchozí hodnotu, aby aplikace běžela i bez .env souboru.
function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);

    if ($value === false || $value === '') {
        return $_ENV[$key] ?? $default;
    }

    return $value;
}

// Escapuje výstup do HTML. Používá se u všech hodnot z databáze nebo formuláře,
// aby se do stránky nedostal nechtěný HTML/JS kód.
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Zjistí název aktuálně spuštěného PHP souboru, například books.php.
// Layout podle toho zvýrazňuje aktivní položku v navigaci.
function current_page(): string
{
    return basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
}

// Přesměruje uživatele na jinou stránku a ukončí další zpracování skriptu.
function redirect_to(string $path): never
{
    header('Location: ' . $path);
    exit;
}

// Ověří, že řetězec vypadá jako UUID. Díky tomu neposíláme do SQL náhodný text
// tam, kde databáze očekává uuid typ.
function is_uuid(string $value): bool
{
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
}

// Pomocník pro <select>. Když je volba právě vybraná, vrátí HTML atribut selected.
function selected_attr(string $current, string $expected): string
{
    return $current === $expected ? ' selected' : '';
}

// Pomocník pro checkboxy. Když je hodnota mezi aktuálně vybranými, vrátí checked.
function checked_attr(array $current, string $expected): string
{
    return in_array($expected, $current, true) ? ' checked' : '';
}
