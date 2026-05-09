<?php

declare(strict_types=1);

function render_header(string $title): void
{
    $page = current_page();
    $items = [
        'index.php' => 'Přehled',
        'books.php' => 'Knihy',
        'add-book.php' => 'Přidat knihu',
        'about.php' => 'O projektu',
    ];
    ?>
    <!doctype html>
    <html lang="cs">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Katalog knih</title>
        <link rel="stylesheet" href="/assets/styles.css">
        <script src="/assets/app.js" defer></script>
    </head>
    <body>
        <header class="site-header">
            <a class="brand" href="/index.php">Katalog knih</a>
            <nav class="nav" aria-label="Hlavni navigace">
                <?php foreach ($items as $href => $label): ?>
                    <a class="<?= $page === $href ? 'active' : '' ?>" href="/<?= e($href) ?>">
                        <?= e($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </header>
        <main class="page">
    <?php
}

function render_footer(): void
{
    ?>
        </main>
        <footer class="site-footer">
            <div class="footer-inner">
                <div>
                    <strong>Katalog knih</strong>
                    <p>Jednoduchá školní aplikace pro práci s knihami v databázi.</p>
                </div>
                <nav class="footer-links" aria-label="Patička">
                    <a href="/index.php">Přehled</a>
                    <a href="/books.php">Knihy</a>
                    <a href="/add-book.php">Přidat knihu</a>
                    <a href="/about.php">O projektu</a>
                </nav>
                <div class="footer-tech" aria-label="Použité technologie">
                    <span>PHP</span>
                    <span>PostgreSQL</span>
                    <span>Docker</span>
                    <span>HTML/CSS/JS</span>
                </div>
            </div>
        </footer>
    </body>
    </html>
    <?php
}

function render_db_error(Throwable $exception): void
{
    ?>
    <section class="notice error">
        <h2>Databaze není dostupná</h2>
        <p>Zkontroluj hodnoty v <code>.env</code> a jestli je PostgreSQL kontejner dostupny jako <code>db:5432</code>.</p>
        <p class="muted"><?= e($exception->getMessage()) ?></p>
    </section>
    <?php
}
