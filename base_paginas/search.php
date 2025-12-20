<?php
/**
 * Server-side search results page (fallback if JS disabled)
 */

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$q = trim($_GET['q'] ?? '');

$page_title = $q ? 'Search: ' . h($q) : 'Search';
$page_description = $q ? 'Search results for: ' . h($q) : 'Search the site';

include 'includes/header.php';

?>

<div class="container">
    <div class="breadcrumb"><a href="/">Home</a> / <strong>Search</strong></div>
    <h1><?= $page_title ?></h1>

    <?php if (!$q): ?>
        <p>Type a query in the search box above to find articles and materials.</p>
    <?php else: ?>
        <?php
        // Reuse simple search logic
    $like = '%' . $q . '%';
    $stmtA = $pdo->prepare("SELECT id, title, slug, excerpt FROM articles WHERE status='published' AND (title LIKE :q1 OR excerpt LIKE :q2 OR body LIKE :q3) ORDER BY published_at DESC LIMIT 20");
    $stmtA->execute(['q1' => $like, 'q2' => $like, 'q3' => $like]);
        $articles = $stmtA->fetchAll(PDO::FETCH_ASSOC);

    $stmtM = $pdo->prepare("SELECT id, common_name, slug, technical_name, description FROM materials WHERE status='published' AND (common_name LIKE :q1 OR technical_name LIKE :q2 OR description LIKE :q3) ORDER BY featured DESC, updated_at DESC LIMIT 20");
    $stmtM->execute(['q1' => $like, 'q2' => $like, 'q3' => $like]);
        $materials = $stmtM->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <section class="search-results-page">
            <?php if (empty($articles) && empty($materials)): ?>
                <div class="empty-state">
                    <h2>No results</h2>
                    <p>No articles or materials matched your query. Try broader terms.</p>
                </div>
            <?php else: ?>
                <?php if (!empty($articles)): ?>
                <div class="content-section">
                    <h2>Articles</h2>
                    <ul>
                        <?php foreach ($articles as $a): ?>
                        <li><a href="/article.php?slug=<?= h($a['slug']) ?>"><?= h($a['title']) ?></a> — <?= h($a['excerpt']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($materials)): ?>
                <div class="content-section">
                    <h2>Materials</h2>
                    <ul>
                        <?php foreach ($materials as $m): ?>
                        <li><a href="/material.php?slug=<?= h($m['slug']) ?>"><?= h($m['common_name']) ?></a> — <?= h($m['technical_name'] ?: substr(strip_tags($m['description']), 0, 120)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
