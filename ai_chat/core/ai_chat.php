<?php
/**
 * AI Chat Assistant
 * Enhanced search functionality with Groq AI integration for AbanteCart
 * 
 * @author posadard.com
 * @copyright Copyright (c) 2025 posadard.com
 * @license MIT License
 * @website https://posadard.com
 */

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

class ExtensionAiChat extends Extension
{
    private $groqApiEndpoint = 'https://api.groq.com/openai/v1/chat/completions';
    private $cachePrefix = 'ai_chat_groq_';
    
    public function onControllerCommonPage_UpdateData()
    {
        if (!IS_ADMIN) {
            $this->baseObject->document->addScript(DIR_EXTENSIONS . 'ai_chat' . DIR_EXT_STORE . 'view/default/js/ajax-chosen.js');

            $this->baseObject->document->addScript(DIR_EXTENSIONS . 'ai_chat' . DIR_EXT_STORE . 'view/default/js/chosen.jquery.js');

            $this->baseObject->document->addStyle(
                [
                    'href' => DIR_EXTENSIONS . 'ai_chat' . DIR_EXT_STORE . 'view/default/css/chosen.bootstrap.css',
                    'rel' => 'stylesheet',
                    'media' => 'screen',
                ]
            );
        }

        if (IS_ADMIN && $this->baseObject->request->get['extension'] == 'ai_chat') {
            $this->baseObject->document->addScript(DIR_EXTENSIONS . 'ai_chat' . DIR_EXT_STORE . 'view/default/js/bootstrap-colorpicker.js');
            $this->baseObject->document->addScript(DIR_EXTENSIONS . 'ai_chat' . DIR_EXT_STORE . 'view/default/js/colorpicker-connect.js');

            $this->baseObject->document->addStyle(
                [
                    'href' => DIR_EXTENSIONS . 'ai_chat' . DIR_EXT_STORE . 'view/default/js/bootstrap-colorpicker.css',
                    'rel' => 'stylesheet',
                    'media' => 'screen',
                ]
            );
        }
    }

    public function onControllerCommonFooter_UpdateData()
    {
        if (!IS_ADMIN) {
            $language_id = (int) $language_id;
            if (!$language_id) {
                $language_id = (int) $this->baseObject->language->getLanguageID();
            }
            $this->baseObject->loadLanguage('ai_chat/ai_chat');

            // FIX: Improved protocol detection
            $protocol = 'http';
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                $protocol = 'https';
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
                $protocol = 'https';
            } elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
                $protocol = 'https';
            }

            // FIX: Generate search_auto URL with proper validation
            $search_auto = $this->baseObject->html->getSecureURL('search_auto/global_search_result/suggest');
            
            // Debug: Log the generated URL
            error_log("AI Chat: Initial search_auto URL: " . $search_auto);
            
            // Validate and fix URL if needed
            $url_parts = parse_url($search_auto);
            
            if (!$url_parts || !isset($url_parts['scheme']) || !isset($url_parts['host'])) {
                // If URL parsing failed or incomplete URL, reconstruct it
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $script_name = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
                $search_auto = $protocol . '://' . $host . $script_name . '?rt=search_auto/global_search_result/suggest';
                error_log("AI Chat: Reconstructed search_auto URL: " . $search_auto);
            } else {
                // Fix protocol if needed
                if ($url_parts['scheme'] != $protocol) {
                    $search_auto = str_replace($url_parts['scheme'], $protocol, $search_auto);
                    error_log("AI Chat: Protocol corrected search_auto URL: " . $search_auto);
                }
            }
            
            // Final validation
            if (!filter_var($search_auto, FILTER_VALIDATE_URL)) {
                // Last resort fallback
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $search_auto = $protocol . '://' . $host . '/index.php?rt=search_auto/global_search_result/suggest';
                error_log("AI Chat: Fallback search_auto URL: " . $search_auto);
            }

            $ai_chat_text_oops = $this->baseObject->language->get('ai_chat_text_oops');
            $ai_chat_text_search = $this->baseObject->language->get('ai_chat_text_search');
            $ai_chat_text_matches = $this->baseObject->language->get('ai_chat_text_matches');
            
            // AI Chat Assistant variables
            $this->baseObject->view->assign('ai_chat_text_oops', $ai_chat_text_oops);
            $this->baseObject->view->assign('ai_chat_text_search', $ai_chat_text_search);
            $this->baseObject->view->assign('search_auto', $search_auto);

            // Apply mode configuration before passing to frontend
            $this->applyModeConfiguration();

            // FIXED: Complete Groq AI configuration for frontend
            $groq_enabled = (bool) $this->baseObject->config->get('ai_chat_groq_enabled');
            $groq_api_key = $this->baseObject->config->get('ai_chat_groq_api_key');
            $search_mode = $this->baseObject->config->get('ai_chat_search_mode') ?: 'balanced';
            $natural_language_threshold = (int) $this->baseObject->config->get('ai_chat_natural_language_threshold') ?: 4;
            
            // Assign Groq configuration
            $this->baseObject->view->assign('ai_chat_groq_enabled', $groq_enabled);
            $this->baseObject->view->assign('ai_chat_groq_api_key', $groq_api_key);
            $this->baseObject->view->assign('ai_chat_groq_model', $this->baseObject->config->get('ai_chat_groq_model') ?: 'llama-3.1-70b-versatile');
            $this->baseObject->view->assign('ai_chat_search_mode', $search_mode);
            $this->baseObject->view->assign('ai_chat_natural_language_threshold', $natural_language_threshold);
            $this->baseObject->view->assign('ai_chat_groq_temperature', (float) $this->baseObject->config->get('ai_chat_groq_temperature') ?: 0.3);
            $this->baseObject->view->assign('ai_chat_groq_max_tokens', (int) $this->baseObject->config->get('ai_chat_groq_max_tokens') ?: 100);
            $this->baseObject->view->assign('ai_chat_cache_duration', (int) $this->baseObject->config->get('ai_chat_cache_duration') ?: 900);

            // Search ID handling
            if (strlen($this->baseObject->config->get('ai_chat_search_id')) > 2) {
                $ai_chat_search_id = $this->baseObject->config->get('ai_chat_search_id');
            } else {
                $ai_chat_search_id = 'filter_keyword';
            }
            $this->baseObject->view->assign('ai_chat_search_id', $ai_chat_search_id);

            // New window setting
            if ((bool) $this->baseObject->config->get('ai_chat_new_window')) {
                $ai_chat_new_window = $this->baseObject->config->get('ai_chat_new_window');
            } else {
                $ai_chat_new_window = 0;
            }
            $this->baseObject->view->assign('ai_chat_new_window', $ai_chat_new_window);
            
            // Debug: Log complete configuration
            $config_debug = [
                'search_auto' => $search_auto,
                'groq_enabled' => $groq_enabled,
                'api_key_configured' => !empty($groq_api_key),
                'search_mode' => $search_mode,
                'threshold' => $natural_language_threshold
            ];
            error_log("AI Chat: Complete configuration - " . json_encode($config_debug));
        }
    }

    /**
     * Apply automatic configuration based on selected search mode
     */
    public function applyModeConfiguration($mode = null)
    {
        if (!$mode) {
            $mode = $this->baseObject->config->get('ai_chat_search_mode') ?: 'balanced';
        }

        // Don't auto-configure if manual config is enabled
        if ($this->baseObject->config->get('ai_chat_manual_config')) {
            return;
        }

        $config = $this->getModeConfiguration($mode);
        
        // Apply configuration dynamically (these would typically be saved to database in admin)
        foreach ($config as $key => $value) {
            $this->baseObject->config->set($key, $value);
        }
        
        // Log applied configuration
        error_log("AI Chat: Applied '$mode' mode configuration - " . json_encode($config));
    }

    /**
     * Get configuration array for specific mode
     */
    private function getModeConfiguration($mode)
    {
        $configurations = [
            'fast' => [
                'ai_chat_items_limit' => 3,
                'ai_chat_groq_temperature' => 0.1,
                'ai_chat_groq_max_tokens' => 50,
                'ai_chat_cache_duration' => 1800,
                'ai_chat_natural_language_threshold' => 3, // Lower threshold for fast mode
                'ai_chat_products' => 1,
                'ai_chat_categories' => 1,
                'ai_chat_brands' => 0,
                'ai_chat_reviews' => 0,
                'ai_chat_pages' => 0,
                'ai_chat_ptags' => 0,
                'ai_chat_pdesc' => 0,
            ],
            'balanced' => [
                'ai_chat_items_limit' => 6,
                'ai_chat_groq_temperature' => 0.3,
                'ai_chat_groq_max_tokens' => 100,
                'ai_chat_cache_duration' => 900,
                'ai_chat_natural_language_threshold' => 4,
                'ai_chat_products' => 1,
                'ai_chat_categories' => 1,
                'ai_chat_brands' => 1,
                'ai_chat_reviews' => 0,
                'ai_chat_pages' => 1,
                'ai_chat_ptags' => 1,
                'ai_chat_pdesc' => 0,
            ],
            'precise' => [
                'ai_chat_items_limit' => 12,
                'ai_chat_groq_temperature' => 0.7,
                'ai_chat_groq_max_tokens' => 200,
                'ai_chat_cache_duration' => 300,
                'ai_chat_natural_language_threshold' => 5, // Higher threshold for precise mode
                'ai_chat_products' => 1,
                'ai_chat_categories' => 1,
                'ai_chat_brands' => 1,
                'ai_chat_reviews' => 1,
                'ai_chat_pages' => 1,
                'ai_chat_ptags' => 1,
                'ai_chat_pdesc' => 1,
            ]
        ];

        return $configurations[$mode] ?? $configurations['balanced'];
    }

    /**
     * Check if query should use natural language processing
     */
    public function isNaturalLanguageQuery($query)
    {
        if (!$this->baseObject->config->get('ai_chat_groq_enabled')) {
            return false;
        }

        $threshold = (int) $this->baseObject->config->get('ai_chat_natural_language_threshold') ?: 4;
        $wordCount = str_word_count(trim($query));
        
        // Check for natural language indicators
        $naturalIndicators = [
            '¿', '?', // Questions
            'busco', 'necesito', 'quiero', 'como', 'cómo', 'donde', 'dónde', // Spanish
            'looking', 'need', 'want', 'how', 'where', 'what', 'can', 'could', // English
            'para', 'con', 'sin', 'que', 'el', 'la', 'un', 'una', // Spanish articles/prepositions
            'for', 'with', 'without', 'the', 'a', 'an', 'is', 'are', // English articles/prepositions
        ];

        $queryLower = mb_strtolower($query);
        $hasNaturalIndicators = false;
        
        foreach ($naturalIndicators as $indicator) {
            if (strpos($queryLower, $indicator) !== false) {
                $hasNaturalIndicators = true;
                break;
            }
        }

        $result = ($wordCount >= $threshold) || $hasNaturalIndicators;
        
        // Log detection result
        error_log("AI Chat: Natural language detection - Query: '$query', Words: $wordCount, Threshold: $threshold, Has indicators: " . 
                 ($hasNaturalIndicators ? 'yes' : 'no') . ", Result: " . ($result ? 'AI' : 'traditional'));

        return $result;
    }

    /**
     * Call Groq API to extract keywords from natural language query
     */
    public function callGroqAPI($query)
    {
        // Check if Groq is enabled and API key is set
        if (!$this->baseObject->config->get('ai_chat_groq_enabled')) {
            error_log("AI Chat: Groq disabled in configuration");
            return false;
        }

        $apiKey = $this->baseObject->config->get('ai_chat_groq_api_key');
        if (empty($apiKey)) {
            error_log("AI Chat: No Groq API key configured");
            return false;
        }

        // Check cache first
        $cacheKey = $this->cachePrefix . md5($query);
        $cacheDuration = (int) $this->baseObject->config->get('ai_chat_cache_duration') ?: 900;
        
        if ($this->baseObject->cache) {
            $cached = $this->baseObject->cache->get($cacheKey);
            if ($cached !== false) {
                error_log("AI Chat: Using cached result for query: '$query'");
                return $cached;
            }
        }

        // Apply mode configuration
        $this->applyModeConfiguration();

        // Prepare API request
        $model = $this->baseObject->config->get('ai_chat_groq_model') ?: 'llama-3.1-70b-versatile';
        $temperature = (float) $this->baseObject->config->get('ai_chat_groq_temperature') ?: 0.3;
        $maxTokens = (int) $this->baseObject->config->get('ai_chat_groq_max_tokens') ?: 100;

        $prompt = $this->buildExtractionPrompt($query);

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an ecommerce search assistant. Extract the most relevant keywords from user queries to search products, categories, brands, pages, and content. Respond ONLY with keywords separated by commas.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'top_p' => 1,
            'stream' => false
        ];

        error_log("AI Chat: Making Groq API call - Model: $model, Temperature: $temperature, Max tokens: $maxTokens");

        // Make API call
        $result = $this->makeGroqRequest($payload, $apiKey);
        
        if ($result) {
            // Cache the result
            if ($this->baseObject->cache) {
                $this->baseObject->cache->set($cacheKey, $result, $cacheDuration);
            }
            error_log("AI Chat: Successfully extracted keywords: '$result' from query: '$query'");
        } else {
            error_log("AI Chat: Failed to extract keywords from query: '$query'");
        }

        return $result;
    }

    /**
     * Build the extraction prompt for Groq API
     */
    private function buildExtractionPrompt($query)
    {
        return "Extract keywords from this query for ecommerce search: \"$query\"\n\n" .
               "Consider:\n" .
               "- Product names and features\n" .
               "- Category names\n" .
               "- Brand names\n" .
               "- Use cases and applications\n" .
               "- Synonyms and related terms\n\n" .
               "Respond with keywords separated by commas:";
    }

    /**
     * Make HTTP request to Groq API
     */
    private function makeGroqRequest($payload, $apiKey)
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->groqApiEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'AI Chat Assistant/2.0 AbanteCart Extension'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("AI Chat Assistant: cURL error - $error");
            return false;
        }

        if ($httpCode !== 200) {
            error_log("AI Chat Assistant: HTTP error $httpCode - $response");
            return false;
        }

        $data = json_decode($response, true);
        
        if (!$data || !isset($data['choices'][0]['message']['content'])) {
            error_log("AI Chat Assistant: Invalid API response - $response");
            return false;
        }

        $keywords = trim($data['choices'][0]['message']['content']);
        
        // Clean and process keywords
        return $this->processExtractedKeywords($keywords);
    }

    /**
     * Process and clean extracted keywords
     */
    private function processExtractedKeywords($keywords)
    {
        // Remove quotes and clean up
        $keywords = str_replace(['"', "'", "\n", "\r"], '', $keywords);
        
        // Split by comma and clean each keyword
        $keywordArray = array_map('trim', explode(',', $keywords));
        
        // Remove empty keywords and duplicates
        $keywordArray = array_filter(array_unique($keywordArray), function($keyword) {
            return !empty($keyword) && strlen($keyword) > 1;
        });

        return implode(',', $keywordArray);
    }

    /**
     * Get fallback keywords when AI fails
     */
    public function getFallbackKeywords($query)
    {
        // Simple keyword extraction as fallback
        $words = preg_split('/[\s,.-]+/', $query);
        $keywords = [];
        
        // Remove Spanish/English stop words
        $stopWords = [
            'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas', 'para', 'con', 'sin',
            'the', 'a', 'an', 'for', 'with', 'without', 'and', 'or', 'but', 'in', 'on', 'at'
        ];
        
        foreach ($words as $word) {
            $word = trim(strtolower($word));
            if (strlen($word) > 2 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }
        
        $result = implode(',', array_unique($keywords));
        error_log("AI Chat: Fallback keywords generated: '$result' from query: '$query'");
        
        return $result;
    }

    /**
     * Log AI Chat Assistant activity
     */
    public function logActivity($message, $level = 'info')
    {
        if ($this->baseObject->config->get('ai_chat_debug_logging')) {
            error_log("AI Chat Assistant [$level]: $message");
        }
    }
}