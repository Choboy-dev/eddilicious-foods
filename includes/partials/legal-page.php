<?php
/**
 * Legal page layout — terms, privacy, cookies
 *
 * Expects $legal (from get_legal_page()).
 */
$legal = $legal ?? get_legal_page('terms');
$legal_nav = get_legal_nav();
$current_slug = $legal['slug'] ?? 'terms';
$page_class = $legal['page_class'] ?? '';
?>

<main class="legal-page<?= $page_class !== '' ? ' ' . htmlspecialchars($page_class) : '' ?>" id="legal-main">
    <div class="legal-page__container">
        <header class="legal-header">
            <p class="legal-header__eyebrow"><?= htmlspecialchars($legal['eyebrow']) ?></p>
            <h1 class="legal-header__title"><?= htmlspecialchars($legal['title']) ?></h1>
            <p class="legal-header__lead"><?= htmlspecialchars($legal['lead']) ?></p>
            <p class="legal-header__updated">Last updated: <?= htmlspecialchars($legal['updated']) ?></p>
        </header>

        <?php if (!empty($legal['callout'])): ?>
            <?php $callout = $legal['callout']; ?>
            <section
                class="legal-callout"
                id="<?= htmlspecialchars($callout['id'] ?? 'legal-callout') ?>"
                aria-labelledby="legal-callout-title"
            >
                <div class="legal-callout__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                </div>
                <div class="legal-callout__body">
                    <h2 id="legal-callout-title" class="legal-callout__title"><?= htmlspecialchars($callout['title']) ?></h2>
                    <p class="legal-callout__text"><?= htmlspecialchars($callout['text']) ?></p>
                </div>
                <button
                    type="button"
                    class="legal-callout__btn"
                    data-cookie-settings<?= !empty($callout['button_detail']) ? ' data-cookie-settings-detail' : '' ?>
                >
                    <?= htmlspecialchars($callout['button']) ?>
                </button>
            </section>
        <?php endif; ?>

        <div class="legal-layout">
            <nav class="legal-nav" aria-label="Legal pages">
                <p class="legal-nav__label">On this site</p>
                <ul class="legal-nav__list">
                    <?php foreach ($legal_nav as $item): ?>
                        <li>
                            <a
                                href="<?= htmlspecialchars($item['url']) ?>"
                                class="legal-nav__link<?= $current_slug === $item['slug'] ? ' is-active' : '' ?>"
                                <?= $current_slug === $item['slug'] ? 'aria-current="page"' : '' ?>
                            >
                                <?= htmlspecialchars($item['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="legal-content">
                <nav class="legal-toc" aria-label="Contents">
                    <p class="legal-toc__label">Contents</p>
                    <ol class="legal-toc__list">
                        <?php foreach ($legal['sections'] as $section): ?>
                            <?php
                            $toc_heading = preg_replace('/^\d+\.\s*/', '', $section['heading']) ?? $section['heading'];
                            ?>
                            <li>
                                <a href="#<?= htmlspecialchars($section['id']) ?>" class="legal-toc__link">
                                    <?= htmlspecialchars($toc_heading) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>

                <div class="legal-sections">
                    <?php foreach ($legal['sections'] as $section): ?>
                        <section id="<?= htmlspecialchars($section['id']) ?>" class="legal-section">
                            <h2 class="legal-section__heading"><?= htmlspecialchars($section['heading']) ?></h2>
                            <?php foreach ($section['paragraphs'] as $paragraph): ?>
                                <p class="legal-section__text"><?= htmlspecialchars($paragraph) ?></p>
                            <?php endforeach; ?>
                            <?php if (!empty($section['list'])): ?>
                                <ul class="legal-section__list">
                                    <?php foreach ($section['list'] as $item): ?>
                                        <li><?= htmlspecialchars($item) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if (!empty($section['table'])): ?>
                                <?php $table = $section['table']; ?>
                                <div class="legal-table-wrap">
                                    <table class="legal-table">
                                        <?php if (!empty($table['caption'])): ?>
                                            <caption class="legal-table__caption"><?= htmlspecialchars($table['caption']) ?></caption>
                                        <?php endif; ?>
                                        <thead>
                                            <tr>
                                                <?php foreach ($table['headers'] as $header): ?>
                                                    <th scope="col"><?= htmlspecialchars($header) ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($table['rows'] as $row): ?>
                                                <tr>
                                                    <?php foreach ($row as $cell): ?>
                                                        <td><?= htmlspecialchars($cell) ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($section['documents'])): ?>
                                <div class="legal-documents">
                                    <?php foreach ($section['documents'] as $document): ?>
                                        <article class="legal-document">
                                            <div class="legal-document__icon" aria-hidden="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M10 13H8"/><path d="M16 13h-2"/><path d="M10 17H8"/><path d="M16 17h-2"/></svg>
                                            </div>
                                            <div class="legal-document__body">
                                                <h3 class="legal-document__title"><?= htmlspecialchars($document['title']) ?></h3>
                                                <?php if (!empty($document['description'])): ?>
                                                    <p class="legal-document__text"><?= htmlspecialchars($document['description']) ?></p>
                                                <?php endif; ?>
                                                <div class="legal-document__actions">
                                                    <a href="<?= htmlspecialchars($document['url']) ?>" class="legal-document__btn legal-document__btn--primary" target="_blank" rel="noopener noreferrer">
                                                        Open PDF
                                                    </a>
                                                    <a href="<?= htmlspecialchars($document['url']) ?>" class="legal-document__btn" download="<?= htmlspecialchars($document['filename'] ?? 'document.pdf') ?>">
                                                        Download
                                                    </a>
                                                </div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                </div>

                <footer class="legal-footer">
                    <?php
                    $other_legal = array_values(array_filter($legal_nav, static function (array $item) use ($current_slug): bool {
                        return $item['slug'] !== $current_slug;
                    }));
                    ?>
                    <?php if ($other_legal !== []): ?>
                        <p class="legal-footer__text">
                            See also:
                            <?php foreach ($other_legal as $index => $item): ?>
                                <a href="<?= htmlspecialchars($item['url']) ?>" class="legal-footer__link"><?= htmlspecialchars($item['label']) ?></a><?= $index < count($other_legal) - 1 ? ', ' : '' ?>
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>
                    <a href="contact.php" class="legal-footer__cta">Contact us</a>
                </footer>
            </div>
        </div>
    </div>
</main>
