<?php
function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
function canonical_url($path = '') { return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/'); }
function slugify($text) {
    $text = strtolower(preg_replace('/[^a-z0-9\s-]/', '', $text ?? ''));
    $text = preg_replace('/\s+/', '-', $text);
    return preg_replace('/-+/', '-', $text);
}
?>