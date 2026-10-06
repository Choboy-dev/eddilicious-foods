<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/data/admin-help-content.php';

AuthSession::requireAdmin();

$admin_current = 'help';
$admin_title = 'User manual';
$admin_stylesheets = ['../assets/css/admin-help.css'];
$admin_scripts = ['../assets/js/admin-help.js'];

$help_manual = get_admin_help_manual();
$help_categories = $help_manual['categories'];
$help_articles = $help_manual['articles'];

include dirname(__DIR__) . '/includes/admin/shell-start.php';
?>

<div class="admin-help" data-admin-help>
    <section class="admin-help-hero" aria-labelledby="admin-help-title">
        <div class="admin-help-hero__copy">
            <h2 id="admin-help-title" class="admin-help-hero__title">User manual</h2>
            <p class="admin-help-hero__lead">How to run your shop step by step. Search for any topic below.</p>
        </div>
        <div class="admin-help-search">
            <label class="admin-help-search__label" for="admin-help-search-input">Search help</label>
            <div class="admin-help-search__field">
                <svg class="admin-help-search__icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input
                    type="search"
                    id="admin-help-search-input"
                    class="admin-help-search__input"
                    placeholder="Try: upload photo, coupon, orders, sign in…"
                    autocomplete="off"
                    data-admin-help-search
                >
                <button type="button" class="admin-help-search__clear" data-admin-help-search-clear hidden aria-label="Clear search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
            <p class="admin-help-search__meta" data-admin-help-search-meta><?= count($help_articles) ?> topics</p>
        </div>
    </section>

    <div class="admin-help-layout">
        <nav class="admin-help-nav" aria-label="Manual sections">
            <p class="admin-help-nav__label">Jump to</p>
            <ul class="admin-help-nav__list">
                <?php foreach ($help_categories as $category): ?>
                    <li>
                        <a href="#help-cat-<?= htmlspecialchars($category['id']) ?>" class="admin-help-nav__link" data-admin-help-nav>
                            <?= htmlspecialchars($category['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="admin-help-content">
            <p class="admin-help-empty" data-admin-help-empty hidden role="status">No topics match your search. Try fewer words or another topic.</p>

            <?php foreach ($help_categories as $category): ?>
                <?php
                $category_articles = array_values(array_filter(
                    $help_articles,
                    static fn (array $article): bool => ($article['category'] ?? '') === $category['id']
                ));
                if ($category_articles === []) {
                    continue;
                }
                ?>
                <section
                    id="help-cat-<?= htmlspecialchars($category['id']) ?>"
                    class="admin-help-category"
                    data-admin-help-category="<?= htmlspecialchars($category['id']) ?>"
                >
                    <header class="admin-help-category__head">
                        <h3 class="admin-help-category__title"><?= htmlspecialchars($category['label']) ?></h3>
                        <span class="admin-help-category__count"><?= count($category_articles) ?> topics</span>
                    </header>

                    <div class="admin-help-articles">
                        <?php foreach ($category_articles as $article): ?>
                            <?php
                            $block_text = [];
                            foreach ($article['blocks'] ?? [] as $block) {
                                if (!empty($block['text'])) {
                                    $block_text[] = $block['text'];
                                }
                                if (!empty($block['title'])) {
                                    $block_text[] = $block['title'];
                                }
                                if (!empty($block['items'])) {
                                    $block_text = array_merge($block_text, $block['items']);
                                }
                            }
                            $search_blob = strtolower(implode(' ', array_merge([
                                $article['title'] ?? '',
                                $article['summary'] ?? '',
                                $article['keywords'] ?? '',
                                $category['label'] ?? '',
                            ], $block_text)));
                            ?>
                            <article
                                id="help-<?= htmlspecialchars($article['id']) ?>"
                                class="admin-help-article"
                                data-admin-help-article
                                data-search="<?= htmlspecialchars($search_blob) ?>"
                            >
                                <header class="admin-help-article__head">
                                    <h4 class="admin-help-article__title">
                                        <a href="#help-<?= htmlspecialchars($article['id']) ?>" class="admin-help-article__anchor">
                                            <?= htmlspecialchars($article['title']) ?>
                                        </a>
                                    </h4>
                                    <?php if (!empty($article['summary'])): ?>
                                        <p class="admin-help-article__summary"><?= htmlspecialchars($article['summary']) ?></p>
                                    <?php endif; ?>
                                </header>

                                <div class="admin-help-article__body">
                                    <?php foreach ($article['blocks'] ?? [] as $block): ?>
                                        <?php if (($block['type'] ?? '') === 'text' && !empty($block['text'])): ?>
                                            <p class="admin-help-article__text"><?= htmlspecialchars($block['text']) ?></p>
                                        <?php elseif (($block['type'] ?? '') === 'list' && !empty($block['items'])): ?>
                                            <?php if (!empty($block['title'])): ?>
                                                <p class="admin-help-article__label"><?= htmlspecialchars($block['title']) ?></p>
                                            <?php endif; ?>
                                            <ul class="admin-help-article__list">
                                                <?php foreach ($block['items'] as $item): ?>
                                                    <li><?= htmlspecialchars($item) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php elseif (($block['type'] ?? '') === 'steps' && !empty($block['items'])): ?>
                                            <?php if (!empty($block['title'])): ?>
                                                <p class="admin-help-article__label"><?= htmlspecialchars($block['title']) ?></p>
                                            <?php endif; ?>
                                            <ol class="admin-help-article__steps">
                                                <?php foreach ($block['items'] as $item): ?>
                                                    <li><?= htmlspecialchars($item) ?></li>
                                                <?php endforeach; ?>
                                            </ol>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

            <section class="admin-help-escalation" id="help-contact" data-admin-help-category="help">
                <h3 class="admin-help-escalation__title">Still stuck?</h3>
                <p class="admin-help-escalation__text">Email your developer with the page name and what you tried. They can change site code and server settings you cannot reach here.</p>
            </section>
        </div>
    </div>
</div>

<script>
    window.EDDILICIOUS_ADMIN_HELP = {
        articleCount: <?= (int) count($help_articles) ?>
    };
</script>

<?php include dirname(__DIR__) . '/includes/admin/shell-end.php'; ?>
