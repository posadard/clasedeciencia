<?php
/**
 * AI Chat Assistant Uninstall Script
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

// Delete AI Chat Assistant block from all layouts
$layout = new ALayoutManager();
$layout->deleteBlock('ai_chat');

// Clear cache after uninstallation
(version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('*') : $this->cache->delete('*');