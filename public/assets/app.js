// Spouští se až po načtení HTML, aby už existovaly prvky formulářů a seznamů.
document.addEventListener('DOMContentLoaded', () => {
    // Prvky pro rychlé filtrování seznamu knih bez nového dotazu na server.
    const quickFilter = document.querySelector('#quickFilter');
    const rows = Array.from(document.querySelectorAll('.book-row'));
    const visibleCount = document.querySelector('#visibleCount');

    // Skryje nebo zobrazí řádky podle textu zadaného do rychlého filtru.
    function updateVisibleBooks() {
        if (!quickFilter || !visibleCount) {
            return;
        }

        const needle = quickFilter.value.trim().toLowerCase();
        let visible = 0;

        rows.forEach((row) => {
            const haystack = row.textContent.toLowerCase();
            const match = haystack.includes(needle);
            row.hidden = !match;
            visible += match ? 1 : 0;
        });

        visibleCount.textContent = String(visible);
    }

    // Rychlý filtr reaguje na každé psaní do inputu.
    if (quickFilter) {
        quickFilter.addEventListener('input', updateVisibleBooks);
    }

    // Aktivní serverové filtry zobrazí jako štítky nad seznamem.
    const filterForm = document.querySelector('[data-filter-form]');
    const activeFilters = document.querySelector('#activeFilters');

    if (filterForm && activeFilters) {
        const labels = [];

        // Projde všechna vyplněná pole formuláře a připraví čitelné popisky.
        filterForm.querySelectorAll('input[name], select[name]').forEach((field) => {
            if (!field.value) {
                return;
            }

            const label = field.closest('label')?.firstChild?.textContent?.trim() || field.name;
            const value = field.tagName === 'SELECT'
                ? field.options[field.selectedIndex].text
                : field.value;
            labels.push(`${label}: ${value}`);
        });

        activeFilters.innerHTML = labels.map((label) => `<span>${escapeHtml(label)}</span>`).join('');
    }

    // Klientská kontrola formuláře pro rychlou zpětnou vazbu před odesláním.
    // Stejná pravidla se ale musí kontrolovat i v PHP, protože JS jde vypnout.
    const bookForm = document.querySelector('[data-validate-book]');
    if (bookForm) {
        bookForm.addEventListener('submit', (event) => {
            const errors = [];
            const title = bookForm.querySelector('[name="title"]').value.trim();
            const isbn = bookForm.querySelector('[name="isbn"]').value.trim();
            const authors = bookForm.querySelectorAll('[name="author_ids[]"]:checked');
            const genres = bookForm.querySelectorAll('[name="genre_ids[]"]:checked');

            if (!title) {
                errors.push('Název knihy je povinný.');
            }

            if (isbn && !/^\d{13}$/.test(isbn)) {
                errors.push('ISBN musí mít přesně 13 číslic.');
            }

            if (authors.length === 0) {
                errors.push('Vyber alespoň jednoho autora.');
            }

            if (genres.length === 0) {
                errors.push('Vyber alespoň jeden žánr.');
            }

            const box = bookForm.querySelector('.client-errors');
            if (errors.length > 0 && box) {
                // Při chybě se odeslání zastaví a nad formulář se vypíše seznam chyb.
                event.preventDefault();
                box.hidden = false;
                box.innerHTML = `<h2>Zkontroluj formulář</h2><ul>${errors.map((error) => `<li>${escapeHtml(error)}</li>`).join('')}</ul>`;
                box.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
});

// Jednoduché escapování textu, který vkládáme přes innerHTML.
// Chrání třeba popisky filtrů nebo validační chyby před vložením HTML.
function escapeHtml(value) {
    return value.replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));
}
