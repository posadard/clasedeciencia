<?php
/**
 * Simple search endpoint: returns JSON array of matches from articles and materials.
 * Query: ?q=term
 * Returns: [{type:'article'|'material', title, url, excerpt}]
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db-functions.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo json_encode([]);
    exit;
}

try {
    // Search articles (title, excerpt, body)
    $sqlA = "SELECT id, title, slug, excerpt FROM articles WHERE status='published' AND (title LIKE :q1 OR excerpt LIKE :q2 OR body LIKE :q3) ORDER BY published_at DESC LIMIT 6";
    $stmtA = $pdo->prepare($sqlA);
    $like = '%' . $q . '%';
    $stmtA->execute(['q1' => $like, 'q2' => $like, 'q3' => $like]);
    $articles = $stmtA->fetchAll(PDO::FETCH_ASSOC);
    $articlesCount = is_array($articles) ? count($articles) : 0;

    // Search materials (common_name, technical_name, description, other_names)
    $sqlM = "SELECT id, common_name, slug, technical_name, cas_number, description, other_names FROM materials WHERE status='published' AND (common_name LIKE :q1 OR technical_name LIKE :q2 OR cas_number LIKE :q5 OR description LIKE :q3 OR other_names LIKE :q4) ORDER BY featured DESC, updated_at DESC LIMIT 6";
    $stmtM = $pdo->prepare($sqlM);
    $stmtM->execute(['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like, 'q5' => $like]);
    $materials = $stmtM->fetchAll(PDO::FETCH_ASSOC);
    $materialsCount = is_array($materials) ? count($materials) : 0;

    // If we didn't find enough via SQL (or SQL LIKE missed JSON elements),
    // fetch materials that have other_names and perform JSON-aware matching in PHP.
    // This avoids changing the DB schema and improves matching of JSON array items.
    $RESULT_LIMIT = 6;
    if ($materialsCount < $RESULT_LIMIT) {
        // Build a set of already-seen material IDs to avoid duplicates
        $seen = [];
        foreach ($materials as $m) { if (!empty($m['id'])) $seen[(int)$m['id']] = true; }

        $need = $RESULT_LIMIT - $materialsCount;

        $stmtOther = $pdo->prepare("SELECT id, common_name, slug, technical_name, cas_number, description, other_names, featured, updated_at FROM materials WHERE status='published' AND other_names IS NOT NULL ORDER BY featured DESC, updated_at DESC LIMIT 200");
        $stmtOther->execute();
        $candidates = $stmtOther->fetchAll(PDO::FETCH_ASSOC);

        // Normalization helper: lowercase, strip punctuation, collapse whitespace
        $normalize = function($s) {
            $s = (string)$s;
            $s = mb_strtolower($s, 'UTF-8');
            // Replace any non-letter/number characters with space (keeps unicode letters)
            $s = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $s);
            $s = preg_replace('/\s+/u', ' ', $s);
            return trim($s);
        };

        $qNorm = $normalize($q);
        $qTokens = $qNorm === '' ? [] : explode(' ', $qNorm);

        foreach ($candidates as $cand) {
            if ($need <= 0) break;
            $id = (int)($cand['id'] ?? 0);
            if ($id && isset($seen[$id])) continue; // already included

            $matched = false;
            if (!empty($cand['other_names'])) {
                $decoded = json_decode($cand['other_names'], true);
                if (is_array($decoded)) {
                    foreach ($decoded as $nameItem) {
                        if (!is_string($nameItem)) continue;
                        $itemNorm = $normalize($nameItem);
                        // Direct substring match of normalized query
                        if ($qNorm !== '' && mb_strpos($itemNorm, $qNorm) !== false) { $matched = true; break; }
                        // Or require all tokens to appear (in any order) — helps partial/token searches
                        $allTokensPresent = true;
                        foreach ($qTokens as $t) {
                            if ($t === '') continue;
                            if (mb_strpos($itemNorm, $t) === false) { $allTokensPresent = false; break; }
                        }
                        if ($allTokensPresent && count($qTokens) > 0) { $matched = true; break; }
                    }
                } else {
                    // other_names not valid JSON — do a normalized textual match
                    $itemNorm = $normalize($cand['other_names']);
                    if ($qNorm !== '' && mb_strpos($itemNorm, $qNorm) !== false) $matched = true;
                }
            }

            if ($matched) {
                // Add to materials result set
                $materials[] = $cand;
                if ($id) $seen[$id] = true;
                $need--;
            }
        }
        // update count
        $materialsCount = count($materials);
    }

    $out = [];
    foreach ($articles as $a) {
        $out[] = [
            'type' => 'article',
            'title' => $a['title'],
            'url' => '/article.php?slug=' . urlencode($a['slug']),
            'excerpt' => $a['excerpt'] ?? ''
        ];
    }

    foreach ($materials as $m) {
        // Prefer technical name, then other_names (decoded), then description
        $excerpt = '';
        if (!empty($m['technical_name'])) {
            $excerpt = $m['technical_name'];
        } elseif (!empty($m['other_names'])) {
            // other_names stored as JSON array — try to decode and join nicely
            $decoded = json_decode($m['other_names'], true);
            if (is_array($decoded)) {
                $excerpt = implode(', ', $decoded);
            } else {
                // fallback to raw text
                $excerpt = $m['other_names'];
            }
        } elseif (!empty($m['cas_number'])) {
            $excerpt = $m['cas_number'];
        } else {
            $excerpt = substr(strip_tags($m['description'] ?? ''), 0, 120);
        }
        $out[] = [
            'type' => 'material',
            'title' => $m['common_name'],
            'url' => '/material.php?slug=' . urlencode($m['slug']),
            'excerpt' => $excerpt
        ];
    }

    echo json_encode($out);
    // If debug requested, include additional diagnostic info
    if (!empty($_GET['debug'])) {
        $debug = [
            'sql_articles' => $sqlA,
            'sql_materials' => $sqlM,
            'articles_count' => $articlesCount ?? 0,
            'materials_count' => $materialsCount ?? 0,
            'sample_material' => $materials[0] ?? null
        ];
        // append debug to response (useful during development)
        echo "\n" . json_encode(['debug' => $debug]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
