<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$id = (string) ($_GET['id'] ?? '');

render_header('Detail knihy');

try {
    $book = fetch_book($id);

    if ($book === null) {
        ?>
        <section class="notice">
            <h1>Kniha nebyla nalezena</h1>
            <p>Zkontroluj odkaz nebo se vrať na seznam knih.</p>
            <a class="button" href="/books.php">Zpět na knihy</a>
        </section>
        <?php
    } else {
        ?>
        <?php if (isset($_GET['created'])): ?>
            <div class="notice success">Kniha byla uložena do databáze.</div>
        <?php endif; ?>

        <article class="detail">
            <p class="eyebrow">Detail knihy</p>
            <h1><?= e($book['title']) ?></h1>
            <p class="muted">
                <?= e($book['publisher'] ?? 'Neznámy vydavatel') ?>
                <?php if (!empty($book['publication_year'])): ?>
                    &middot; <?= e($book['publication_year']) ?>
                <?php endif; ?>
            </p>

            <div class="meta-grid">
                <div><span>ISBN</span><strong><?= e($book['isbn'] ?? '-') ?></strong></div>
                <div><span>Strany</span><strong><?= e($book['pages'] ?? '-') ?></strong></div>
                <div><span>Jazyk</span><strong><?= e($book['language'] ?? '-') ?></strong></div>
                <div><span>Format</span><strong><?= e($book['book_format'] ?? '-') ?></strong></div>
            </div>

            <?php if (!empty($book['description'])): ?>
                <section>
                    <h2>Popis</h2>
                    <p><?= nl2br(e($book['description'])) ?></p>
                </section>
            <?php endif; ?>

            <section>
                <h2>Autoři</h2>
                <div class="grid">
                    <?php foreach ($book['authors'] as $author): ?>
                        <article class="card">
                            <h3><?= e($author['name']) ?></h3>
                            <p class="muted">
                                <?= e($author['country'] ?? 'Země neuvedena') ?>
                                <?php if (!empty($author['birthdate'])): ?>
                                    &middot; <?= e($author['birthdate']) ?>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($author['biography'])): ?>
                                <p><?= e($author['biography']) ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section>
                <h2>Žánry</h2>
                <div class="chips">
                    <?php foreach ($book['genres'] as $genre): ?>
                        <span><?= e($genre['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            </section>

            <a class="button ghost detail-back" href="/books.php">Zpět na seznam</a>
        </article>
        <?php
    }
} catch (Throwable $exception) {
    render_db_error($exception);
}

render_footer();
