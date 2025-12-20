<?php
require_once '../config.php';
require_once 'auth.php';

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Helper to check if a table exists in the current database
function table_exists($pdo, $tableName) {
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
    $stmt->execute([$tableName]);
    return (bool) $stmt->fetchColumn();
}

if ($article_id > 0) {
    try {
        $pdo->beginTransaction();
        
        // Delete all relationships first (only if the tables exist on this schema)
        if (table_exists($pdo, 'article_tags')) {
            $pdo->prepare("DELETE FROM article_tags WHERE article_id = ?")->execute([$article_id]);
        }

        // older deployments used `article_chemicals` name; current schema uses `article_materials`
        if (table_exists($pdo, 'article_materials')) {
            $pdo->prepare("DELETE FROM article_materials WHERE article_id = ?")->execute([$article_id]);
        } elseif (table_exists($pdo, 'article_chemicals')) {
            // fallback for legacy installs
            $pdo->prepare("DELETE FROM article_chemicals WHERE article_id = ?")->execute([$article_id]);
        }

        if (table_exists($pdo, 'article_seasons')) {
            $pdo->prepare("DELETE FROM article_seasons WHERE article_id = ?")->execute([$article_id]);
        }

        if (table_exists($pdo, 'article_ctas')) {
            $pdo->prepare("DELETE FROM article_ctas WHERE article_id = ?")->execute([$article_id]);
        }
        
        // Delete the article
        $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$article_id]);
        
        $pdo->commit();
        header("Location: articles.php?deleted=1");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: articles.php?error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: articles.php");
    exit;
}
