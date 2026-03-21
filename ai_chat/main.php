<?php
/**
 * AI Chat Assistant Main Configuration
 * Enhanced search functionality for AbanteCart
 * 
 * @author posadard.com
 * @copyright Copyright (c) 2025 posadard.com
 * @license MIT License
 * @website https://posadard.com
 */

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

// Include the main AI Chat Assistant extension class
if (!class_exists('ExtensionAiChat')) {
    include_once 'core/ai_chat.php';
}

// Language files configuration for AI Chat Assistant
$languages = [
    'storefront' => ['ai_chat/ai_chat'],
    'admin' => ['ai_chat/ai_chat'],
];

// Model files configuration for AI Chat Assistant
$models = [
    'storefront' => ['tool/global_search'],
    'admin' => [],
];

// Controller files configuration for AI Chat Assistant
$controllers = [
    'storefront' => [
        'responses/search_auto/global_search_result',
        'ai_chat/ai_chat',
    ],
    'admin' => [],
];

// Template files configuration for AI Chat Assistant
$templates = [
    'storefront' => [
        'common/footer.post.tpl',
        'blocks/ai_chat/ai_chat.tpl',
    ],
    'admin' => ['common/head.post.tpl'],
];