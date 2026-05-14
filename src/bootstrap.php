<?php

declare(strict_types=1);

// Společný vstupní bod pro všechny veřejné stránky.
// Každá stránka v public/ připojí jen tento soubor a získá pomocné funkce,
// databázové připojení, SQL repository i společný HTML layout.
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/repository.php';
require_once __DIR__ . '/layout.php';
