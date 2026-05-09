<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

render_header('O projektu');
?>
<section class="detail">
    <p class="eyebrow">O projektu</p>
    <h1>Katalog knih</h1>
    <p>
        Tento projekt je malá full-stack webová aplikace pro práci s knihami.
        Ukazuje čtení z databáze, filtrování, detail záznamu a vložení nové knihy
        včetně vazeb na autory a žánry.
    </p>

    <div class="meta-grid">
        <div><span>Backend</span><strong>PHP 8.3 + PDO</strong></div>
        <div><span>Server</span><strong>Apache v Dockeru</strong></div>
        <div><span>Databáze</span><strong>PostgreSQL</strong></div>
        <div><span>Frontend</span><strong>HTML5, CSS, JS</strong></div>
    </div>

    <section>
        <h2>Datový model</h2>
        <p>
            Aplikace používá existující tabulky pro knihy, autory, žánry, vydavatele,
            jazyky a formaty knih. Vazby mezi knihami a autory nebo žánry jsou uloženy
            v propojovacích tabulkách.
        </p>
    </section>
</section>
<?php
render_footer();
