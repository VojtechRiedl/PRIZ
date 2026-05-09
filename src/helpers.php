<?php

declare(strict_types=1);

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);

    if ($value === false || $value === '') {
        return $_ENV[$key] ?? $default;
    }

    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function current_page(): string
{
    return basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
}

function redirect_to(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function is_uuid(string $value): bool
{
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
}

function selected_attr(string $current, string $expected): string
{
    return $current === $expected ? ' selected' : '';
}

function checked_attr(array $current, string $expected): string
{
    return in_array($expected, $current, true) ? ' checked' : '';
}
