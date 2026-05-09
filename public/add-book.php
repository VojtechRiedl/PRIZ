<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

function post_array(string $key): array
{
    $value = $_POST[$key] ?? [];
    return is_array($value) ? array_values(array_filter(array_map('strval', $value))) : [];
}

function validate_book_form(array $options): array
{
    $data = [
        'title' => trim((string) ($_POST['title'] ?? '')),
        'isbn' => trim((string) ($_POST['isbn'] ?? '')),
        'publication_year' => trim((string) ($_POST['publication_year'] ?? '')),
        'pages' => trim((string) ($_POST['pages'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
        'publisher_id' => (string) ($_POST['publisher_id'] ?? ''),
        'language_id' => (string) ($_POST['language_id'] ?? ''),
        'format_id' => (string) ($_POST['format_id'] ?? ''),
        'author_ids' => post_array('author_ids'),
        'genre_ids' => post_array('genre_ids'),
    ];
    $errors = [];

    if ($data['title'] === '') {
        $errors[] = 'Nazev knihy je povinny.';
    }

    if ($data['isbn'] !== '' && preg_match('/^\d{13}$/', $data['isbn']) !== 1) {
        $errors[] = 'ISBN musi mit presne 13 cislic bez pomlcek.';
    }

    $currentYear = (int) date('Y') + 1;
    if ($data['publication_year'] !== '') {
        if (!ctype_digit($data['publication_year']) || (int) $data['publication_year'] < 1400 || (int) $data['publication_year'] > $currentYear) {
            $errors[] = 'Rok vydani musi byt mezi 1400 a ' . $currentYear . '.';
        }
    }

    if ($data['pages'] !== '') {
        if (!ctype_digit($data['pages']) || (int) $data['pages'] < 1 || (int) $data['pages'] > 30000) {
            $errors[] = 'Pocet stran musi byt kladne cislo.';
        }
    }

    foreach (['publisher_id' => 'vydavatele', 'language_id' => 'jazyk', 'format_id' => 'format'] as $key => $label) {
        if (!is_uuid($data[$key])) {
            $errors[] = 'Vyber ' . $label . '.';
        }
    }

    if ($data['author_ids'] === []) {
        $errors[] = 'Vyber alespon jednoho autora.';
    }

    if ($data['genre_ids'] === []) {
        $errors[] = 'Vyber alespon jeden zanr.';
    }

    foreach (array_merge($data['author_ids'], $data['genre_ids']) as $id) {
        if (!is_uuid($id)) {
            $errors[] = 'Formular obsahuje neplatne ID.';
            break;
        }
    }

    $data['isbn'] = $data['isbn'] === '' ? null : $data['isbn'];
    $data['publication_year'] = $data['publication_year'] === '' ? null : (int) $data['publication_year'];
    $data['pages'] = $data['pages'] === '' ? null : (int) $data['pages'];
    $data['description'] = $data['description'] === '' ? null : $data['description'];

    return [$data, $errors];
}

$errors = [];
$data = [
    'title' => '',
    'isbn' => '',
    'publication_year' => '',
    'pages' => '',
    'description' => '',
    'publisher_id' => '',
    'language_id' => '',
    'format_id' => '',
    'author_ids' => [],
    'genre_ids' => [],
];

render_header('Pridat knihu');

try {
    $options = lookup_options();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        [$data, $errors] = validate_book_form($options);

        if ($errors === []) {
            $bookId = create_book($data);
            redirect_to('/book.php?id=' . rawurlencode($bookId) . '&created=1');
        }
    }
    ?>
    <section class="section-heading">
        <div>
            <p class="eyebrow">Zápis do databaze</p>
            <h1>Přidat knihu</h1>
        </div>
    </section>

    <?php if ($errors !== []): ?>
        <section class="notice error">
            <h2>Formulář obsahuje chyby</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <form class="panel form" method="post" action="/add-book.php" data-validate-book novalidate>
        <div class="client-errors notice error" hidden></div>

        <label>
            Název knihy
            <input type="text" name="title" value="<?= e((string) $data['title']) ?>" required maxlength="100">
        </label>

        <div class="form-grid">
            <label>
                ISBN
                <input type="text" name="isbn" value="<?= e((string) ($data['isbn'] ?? '')) ?>" inputmode="numeric" maxlength="13" placeholder="9788072038848">
            </label>
            <label>
                Rok vydání
                <input type="number" name="publication_year" value="<?= e((string) ($data['publication_year'] ?? '')) ?>" min="1400" max="<?= e((string) ((int) date('Y') + 1)) ?>">
            </label>
            <label>
                Počet stran
                <input type="number" name="pages" value="<?= e((string) ($data['pages'] ?? '')) ?>" min="1">
            </label>
        </div>

        <label>
            Popis
            <textarea name="description" rows="5"><?= e((string) ($data['description'] ?? '')) ?></textarea>
        </label>

        <div class="form-grid">
            <label>
                Vydavatel
                <select name="publisher_id" required>
                    <option value="">Vyber vydavatele</option>
                    <?php foreach ($options['publishers'] as $publisher): ?>
                        <option value="<?= e($publisher['id']) ?>"<?= selected_attr((string) $data['publisher_id'], $publisher['id']) ?>>
                            <?= e($publisher['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Jazyk
                <select name="language_id" required>
                    <option value="">Vyber jazyk</option>
                    <?php foreach ($options['languages'] as $language): ?>
                        <option value="<?= e($language['id']) ?>"<?= selected_attr((string) $data['language_id'], $language['id']) ?>>
                            <?= e($language['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Format
                <select name="format_id" required>
                    <option value="">Vyber format</option>
                    <?php foreach ($options['formats'] as $format): ?>
                        <option value="<?= e($format['id']) ?>"<?= selected_attr((string) $data['format_id'], $format['id']) ?>>
                            <?= e($format['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <fieldset>
            <legend>Autoři</legend>
            <div class="checks">
                <?php foreach ($options['authors'] as $author): ?>
                    <label>
                        <input type="checkbox" name="author_ids[]" value="<?= e($author['id']) ?>"<?= checked_attr($data['author_ids'], $author['id']) ?>>
                        <?= e($author['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <fieldset>
            <legend>Žánry</legend>
            <div class="checks">
                <?php foreach ($options['genres'] as $genre): ?>
                    <label>
                        <input type="checkbox" name="genre_ids[]" value="<?= e($genre['id']) ?>"<?= checked_attr($data['genre_ids'], $genre['id']) ?>>
                        <?= e($genre['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <button class="button" type="submit">Uložit knihu</button>
    </form>
    <?php
} catch (Throwable $exception) {
    render_db_error($exception);
}

render_footer();
