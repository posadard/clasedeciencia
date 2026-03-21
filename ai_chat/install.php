<?php
/**
 * AI Chat Assistant Installation Script
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

// AI Chat Assistant block configuration and installation
$language_list = $this->model_localisation_language->getLanguages();

$block_info['block_txt_id'] = 'ai_chat';
$block_info['controller'] = 'ai_chat/ai_chat';

$block_info['templates'] = [
    ['parent_block_txt_id' => 'header', 'template' => 'blocks/ai_chat/ai_chat.tpl'],
    ['parent_block_txt_id' => 'column_left', 'template' => 'blocks/ai_chat/ai_chat.tpl'],
    ['parent_block_txt_id' => 'column_right', 'template' => 'blocks/ai_chat/ai_chat.tpl'],
    ['parent_block_txt_id' => 'header_bottom', 'template' => 'blocks/ai_chat/ai_chat.tpl'],
    ['parent_block_txt_id' => 'content_top', 'template' => 'blocks/ai_chat/ai_chat.tpl'],
    ['parent_block_txt_id' => 'content_bottom', 'template' => 'blocks/ai_chat/ai_chat.tpl'],
    ['parent_block_txt_id' => 'footer', 'template' => 'blocks/ai_chat/ai_chat.tpl'],
    ['parent_block_txt_id' => 'footer_top', 'template' => 'blocks/ai_chat/ai_chat.tpl'],
];

$block_info['descriptions'] = [['language_name' => 'english', 'name' => 'AI Chat Assistant']];

$layout = new ALayoutManager();
$layout->saveBlock($block_info);

// Clear cache after installation
(version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('*') : $this->cache->delete('*');