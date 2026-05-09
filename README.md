# Katalog knih

Mala full-stack aplikace v PHP pro praci s existujici PostgreSQL databazi knih.

## Spusteni

1. Zkopiruj `.env.example` na `.env`.
2. V `.env` nastav pripojeni k PostgreSQL databazi.
3. Spust aplikaci:

```powershell
docker compose up --build
```

4. Otevri `http://localhost:8080`.

## Databaze

Aplikace ocekava hotove schema s tabulkami:

- `books`
- `authors`
- `genres`
- `publishers`
- `languages`
- `book_formats`
- `books_authors`
- `books_genres`

Tabulky se nevytvareji automaticky. Projekt pouze cte existujici data a zapisuje nove knihy.
