<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$filters = [
    'q' => trim((string) ($_GET['q'] ?? '')),
    'author' => (string) ($_GET['author'] ?? ''),
    'genre' => (string) ($_GET['genre'] ?? ''),
    'language' => (string) ($_GET['language'] ?? ''),
];

render_header('Knihy');

try {
    $options = lookup_options();
    $books = fetch_books($filters);
    $hasActiveFilter = implode('', $filters) !== '';
    ?>
    <section class="section-heading">
        <div>
            <p class="eyebrow">Databázový seznam</p>
            <h1>Knihy</h1>
        </div>
        <a class="button" href="/add-book.php">Přidat knihu</a>
    </section>

    <form class="panel filters" method="get" action="/books.php" data-filter-form>
        <label>
            Hledat
            <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Nazev nebo ISBN">
        </label>
        <label>
            Autor
            <select name="author">
                <option value="">Všichni autoři</option>
                <?php foreach ($options['authors'] as $author): ?>
                    <option value="<?= e($author['id']) ?>"<?= selected_attr($filters['author'], $author['id']) ?>>
                        <?= e($author['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Žánr
            <select name="genre">
                <option value="">Všechny žánry</option>
                <?php foreach ($options['genres'] as $genre): ?>
                    <option value="<?= e($genre['id']) ?>"<?= selected_attr($filters['genre'], $genre['id']) ?>>
                        <?= e($genre['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Jazyk
            <select name="language">
                <option value="">Všechny jazyky</option>
                <?php foreach ($options['languages'] as $language): ?>
                    <option value="<?= e($language['id']) ?>"<?= selected_attr($filters['language'], $language['id']) ?>>
                        <?= e($language['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="filter-actions">
            <button class="button" type="submit">Filtrovat</button>
            <?php if ($hasActiveFilter): ?>
                <a class="button ghost" href="/books.php">Zrušit filtry</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="tools">
        <label>
            Rychlý filtr na strance
            <input type="search" id="quickFilter" placeholder="Vyhledat...">
        </label>
        <p class="muted"><span id="visibleCount"><?= count($books) ?></span> / <?= count($books) ?> knih zobrazeno</p>
    </div>

    <div id="activeFilters" class="chips" aria-live="polite"></div>

    <?php if ($books === []): ?>
        <section class="notice">
            <h2>Žádné knihy nenalezeny</h2>
            <p>Zkus upravit filtr nebo přidej novou knihu.</p>
        </section>
    <?php else: ?>
        <div class="book-list" id="bookList">
            <?php foreach ($books as $book): ?>
                <article class="book-row">
                    <div>
                        <h2><a href="/book.php?id=<?= e($book['book_id']) ?>"><?= e($book['title']) ?></a></h2>
                        <p class="muted"><?= e($book['authors']) ?></p>
                    </div>
                    <dl>
                        <div>
                            <dt>Rok</dt>
                            <dd><?= e($book['publication_year'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt>Žánry</dt>
                            <dd><?= e($book['genres']) ?></dd>
                        </div>
                        <div>
                            <dt>Jazyk</dt>
                            <dd><?= e($book['language']) ?></dd>
                        </div>
                        <div>
                            <dt>Format</dt>
                            <dd><?= e($book['book_format']) ?></dd>
                        </div>
                    </dl>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php
} catch (Throwable $exception) {
    render_db_error($exception);
}

render_footer();
