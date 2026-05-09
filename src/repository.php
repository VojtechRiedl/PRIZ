<?php

declare(strict_types=1);

function publication_year_column(): string
{
    static $column = null;

    if ($column !== null) {
        return $column;
    }

    $sql = "
        SELECT column_name
        FROM information_schema.columns
        WHERE table_schema = 'public'
            AND table_name = 'books'
            AND column_name IN ('publication_year', 'publication_yea')
        ORDER BY CASE column_name WHEN 'publication_year' THEN 1 ELSE 2 END
        LIMIT 1
    ";

    $found = db()->query($sql)->fetchColumn();
    $column = $found !== false ? (string) $found : 'publication_year';

    return $column;
}

function dashboard_stats(): array
{
    return [
        'books' => (int) db()->query('SELECT COUNT(*) FROM books')->fetchColumn(),
        'authors' => (int) db()->query('SELECT COUNT(*) FROM authors')->fetchColumn(),
        'genres' => (int) db()->query('SELECT COUNT(*) FROM genres')->fetchColumn(),
    ];
}

function recent_books(int $limit = 4): array
{
    $year = publication_year_column();
    $stmt = db()->prepare("
        SELECT b.book_id, b.title, b.isbn, b.$year AS publication_year, p.name AS publisher
        FROM books b
        LEFT JOIN publishers p ON p.publisher_id = b.publisher_id
        ORDER BY b.title
        LIMIT :limit
    ");
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function option_rows(string $table, string $idColumn): array
{
    $allowed = [
        'authors' => 'author_id',
        'genres' => 'genre_id',
        'languages' => 'language_id',
        'publishers' => 'publisher_id',
        'book_formats' => 'book_format_id',
    ];

    if (($allowed[$table] ?? null) !== $idColumn) {
        throw new InvalidArgumentException('Nepovolena tabulka pro ciselnik.');
    }

    return db()
        ->query("SELECT $idColumn AS id, name FROM $table ORDER BY name")
        ->fetchAll();
}

function lookup_options(): array
{
    return [
        'authors' => option_rows('authors', 'author_id'),
        'genres' => option_rows('genres', 'genre_id'),
        'languages' => option_rows('languages', 'language_id'),
        'publishers' => option_rows('publishers', 'publisher_id'),
        'formats' => option_rows('book_formats', 'book_format_id'),
    ];
}

function fetch_books(array $filters = []): array
{
    $year = publication_year_column();
    $conditions = [];
    $params = [];

    $query = trim((string) ($filters['q'] ?? ''));
    if ($query !== '') {
        $conditions[] = '(b.title ILIKE :query OR COALESCE(b.isbn, \'\') ILIKE :query)';
        $params['query'] = '%' . $query . '%';
    }

    foreach (['author' => 'authors', 'genre' => 'genres', 'language' => 'languages'] as $key => $type) {
        $value = (string) ($filters[$key] ?? '');
        if (!is_uuid($value)) {
            continue;
        }

        if ($type === 'authors') {
            $conditions[] = 'EXISTS (
                SELECT 1 FROM books_authors filter_ba
                WHERE filter_ba.book_id = b.book_id
                    AND filter_ba.author_id = CAST(:author_id AS uuid)
            )';
            $params['author_id'] = $value;
        } elseif ($type === 'genres') {
            $conditions[] = 'EXISTS (
                SELECT 1 FROM books_genres filter_bg
                WHERE filter_bg.book_id = b.book_id
                    AND filter_bg.genre_id = CAST(:genre_id AS uuid)
            )';
            $params['genre_id'] = $value;
        } else {
            $conditions[] = 'b.language_id = CAST(:language_id AS uuid)';
            $params['language_id'] = $value;
        }
    }

    $where = $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);
    $sql = "
        SELECT
            b.book_id,
            b.title,
            b.isbn,
            b.$year AS publication_year,
            b.pages,
            COALESCE(p.name, 'Neznámý vydavatel') AS publisher,
            COALESCE(l.name, '-') AS language,
            COALESCE(f.name, '-') AS book_format,
            COALESCE(string_agg(DISTINCT a.name, ', ' ORDER BY a.name), 'Bez autora') AS authors,
            COALESCE(string_agg(DISTINCT g.name, ', ' ORDER BY g.name), 'Bez žánru') AS genres
        FROM books b
        LEFT JOIN publishers p ON p.publisher_id = b.publisher_id
        LEFT JOIN languages l ON l.language_id = b.language_id
        LEFT JOIN book_formats f ON f.book_format_id = b.format_id
        LEFT JOIN books_authors ba ON ba.book_id = b.book_id
        LEFT JOIN authors a ON a.author_id = ba.author_id
        LEFT JOIN books_genres bg ON bg.book_id = b.book_id
        LEFT JOIN genres g ON g.genre_id = bg.genre_id
        $where
        GROUP BY b.book_id, b.title, b.isbn, b.$year, b.pages, p.name, l.name, f.name
        ORDER BY b.title
    ";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function fetch_book(string $id): ?array
{
    if (!is_uuid($id)) {
        return null;
    }

    $year = publication_year_column();
    $stmt = db()->prepare("
        SELECT
            b.book_id,
            b.title,
            b.isbn,
            b.$year AS publication_year,
            b.pages,
            b.description,
            p.name AS publisher,
            p.founded AS publisher_founded,
            pc.name AS publisher_country,
            l.name AS language,
            f.name AS book_format
        FROM books b
        LEFT JOIN publishers p ON p.publisher_id = b.publisher_id
        LEFT JOIN countries pc ON pc.country_id = p.country_id
        LEFT JOIN languages l ON l.language_id = b.language_id
        LEFT JOIN book_formats f ON f.book_format_id = b.format_id
        WHERE b.book_id = CAST(:id AS uuid)
    ");
    $stmt->execute(['id' => $id]);
    $book = $stmt->fetch();

    if ($book === false) {
        return null;
    }

    $book['authors'] = fetch_book_authors($id);
    $book['genres'] = fetch_book_genres($id);

    return $book;
}

function fetch_book_authors(string $bookId): array
{
    $stmt = db()->prepare("
        SELECT a.author_id, a.name, a.birthdate, a.biography, c.name AS country
        FROM authors a
        INNER JOIN books_authors ba ON ba.author_id = a.author_id
        LEFT JOIN countries c ON c.country_id = a.country_id
        WHERE ba.book_id = CAST(:book_id AS uuid)
        ORDER BY a.name
    ");
    $stmt->execute(['book_id' => $bookId]);

    return $stmt->fetchAll();
}

function fetch_book_genres(string $bookId): array
{
    $stmt = db()->prepare("
        SELECT g.genre_id, g.name
        FROM genres g
        INNER JOIN books_genres bg ON bg.genre_id = g.genre_id
        WHERE bg.book_id = CAST(:book_id AS uuid)
        ORDER BY g.name
    ");
    $stmt->execute(['book_id' => $bookId]);

    return $stmt->fetchAll();
}

function create_book(array $data): string
{
    $year = publication_year_column();
    $pdo = db();

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO books (
                title,
                isbn,
                pages,
                description,
                publisher_id,
                language_id,
                format_id,
                $year
            ) VALUES (
                :title,
                :isbn,
                :pages,
                :description,
                CAST(:publisher_id AS uuid),
                CAST(:language_id AS uuid),
                CAST(:format_id AS uuid),
                :publication_year
            )
            RETURNING book_id
        ");
        $stmt->execute([
            'title' => $data['title'],
            'isbn' => $data['isbn'],
            'pages' => $data['pages'],
            'description' => $data['description'],
            'publisher_id' => $data['publisher_id'],
            'language_id' => $data['language_id'],
            'format_id' => $data['format_id'],
            'publication_year' => $data['publication_year'],
        ]);
        $bookId = (string) $stmt->fetchColumn();

        $authorStmt = $pdo->prepare("
            INSERT INTO books_authors (book_id, author_id)
            VALUES (CAST(:book_id AS uuid), CAST(:author_id AS uuid))
        ");
        foreach ($data['author_ids'] as $authorId) {
            $authorStmt->execute(['book_id' => $bookId, 'author_id' => $authorId]);
        }

        $genreStmt = $pdo->prepare("
            INSERT INTO books_genres (book_id, genre_id)
            VALUES (CAST(:book_id AS uuid), CAST(:genre_id AS uuid))
        ");
        foreach ($data['genre_ids'] as $genreId) {
            $genreStmt->execute(['book_id' => $bookId, 'genre_id' => $genreId]);
        }

        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }

    return $bookId;
}
