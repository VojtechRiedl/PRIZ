<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

render_header('Prehled');

try {
    $stats = dashboard_stats();
    $recentBooks = recent_books();
    ?>
    <section class="hero">
        <div>
            <p class="eyebrow">Školní full-stack projekt</p>
            <h1>Jednoduchý katalog knih</h1>
            <p>
                Aplikace čte a zapisuje knihy do existující PostgreSQL databaze.
                Je postavena jen na PHP, HTML, CSS a malým kousek JavaScriptu.
            </p>
        </div>
        <a class="button" href="/books.php">Prohlížet knihy</a>
    </section>

    <section class="stats" aria-label="Statistiky katalogu">
        <article>
            <strong><?= e($stats['books']) ?></strong>
            <span>Knihy</span>
        </article>
        <article>
            <strong><?= e($stats['authors']) ?></strong>
            <span>Autoři</span>
        </article>
        <article>
            <strong><?= e($stats['genres']) ?></strong>
            <span>Žánry</span>
        </article>
    </section>

    <section class="section-heading">
        <h2>Ukazka knih</h2>
        <a href="/add-book.php">Přidat další</a>
    </section>

    <div class="grid">
        <?php foreach ($recentBooks as $book): ?>
            <article class="card">
                <h3><a href="/book.php?id=<?= e($book['book_id']) ?>"><?= e($book['title']) ?></a></h3>
                <p class="muted">
                    <?= e($book['publisher'] ?? 'Neznámy vydavatel') ?>
                    <?php if (!empty($book['publication_year'])): ?>
                        &middot; <?= e($book['publication_year']) ?>
                    <?php endif; ?>
                </p>
                <?php if (!empty($book['isbn'])): ?>
                    <p>ISBN <?= e($book['isbn']) ?></p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
    <?php
} catch (Throwable $exception) {
    render_db_error($exception);
}

render_footer();
