<?php

if (!defined('DIR_CORE') || IS_ADMIN) {
    header('Location: static_pages/');
}

class ControllerResponsesSearchAutoGlobalSearchResult extends AController
{
    public $error = [];
    public $data = [];

    public function main()
    {
        $registry = Registry::getInstance();
        // init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $this->loadModel('tool/global_search');

        // $this->loadModel('catalog/product');
        // $this->loadModel('tool/seo_url');
        // $this->html->getSEOURL('product/category', '&path=' . $path, '&encode');
        // $this->html->getSEOURL('product/manufacturer', '&manufacturer_id=' . $request['manufacturer_id'], '&encode');
        // $this->html->getSEOURL('product/product', $url . '&product_id=' . $product_id, '&encode');
        // $this->loadModel('catalog/category');

        // $this->loadModel('tool/seo_url', 'storefront');
        if ($this->config->get('ai_chat_images')) {
            $this->loadModel('catalog/product', 'storefront');
        }

        $this->loadLanguage('ai_chat/ai_chat');
        // $this->baseObject->
        $page = (int) $this->request->post['page']; // get the requested page
        $limit = $this->request->post['rows']; // get how many rows we want to have into the grid

        $results = $this->model_tool_global_search->getResult($this->request->get['search_category'], $this->request->get['keyword']);
        // prevent repeat request to db for total
        if (!isset($this->session->data['search_totals'][$this->request->get['search_category']])) {
            $total = $this->model_tool_global_search->getTotal($this->request->get['search_category'], $this->request->get['keyword']);
        } else {
            $total = $this->session->data['search_totals'][$this->request->get['search_category']];
            unset($this->session->data['search_totals'][$this->request->get['search_category']]);
        }

        if ($total > 0) {
            $total_pages = (int) ceil($total / $limit);
        } else {
            $total_pages = 0;
        }

        // $page = $page>$total_pages ? $total_pages : $page;

        $response = new stdClass();
        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $total;
        $response->userdata = new stdClass();
        $i = 0;
        foreach ($results['result'] as $result) {
            $response->rows[$i]['id'] = $i + 1;
            $response->userdata->type[$i + 1] = $result['type'];
            $response->rows[$i]['cell'] = [$i + 1, $result['text'],
            ];
            ++$i;
        }
        $this->data['response'] = $response;

        // update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode($this->data['response']));
    }

    /**
     * function check access rights to search results
     *
     * @param string $permissions
     *
     * @return bool
     */
    private function validate($permissions = null)
    {
        // check access to global search
        if (!$this->user->canAccess('tool/global_search')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        $this->extensions->hk_ValidateData($this);

        return !$this->error ? true : false;
    }

    public function suggest()
    {
        // init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadModel('tool/global_search');
        $this->loadLanguage('ai_chat/ai_chat');

        // Get search term from request
        $searchTerm = $this->request->get['term'] ?? '';
        $originalSearchTerm = $searchTerm;
        
        // ============ VALIDACIÓN ADICIONAL PARA DEBUG ============
        if (empty($searchTerm) || strlen(trim($searchTerm)) < 2) {
            $this->response->addJSONHeader();
            $this->response->setOutput(AJson::encode([
                'error' => 'Search term too short or empty',
                'term' => $searchTerm
            ]));
            return;
        }
        
        // Log para debug
        if ($this->config->get('ai_chat_debug_logging')) {
            error_log("AI Chat Search - Term: '$searchTerm', Reviews enabled: " . 
                      ($this->config->get('ai_chat_reviews') ? 'yes' : 'no') . 
                      ", Pages enabled: " . 
                      ($this->config->get('ai_chat_pages') ? 'yes' : 'no'));
        }
        // =========================================================
        
        // AI Chat Assistant: Check if AI processing is explicitly requested
        $aiRequested = isset($this->request->get['ai_enabled']) && $this->request->get['ai_enabled'] == '1';
        
        // AI Chat Assistant: Check if we should use AI processing
        $useAI = $aiRequested && $this->shouldUseAIProcessing($searchTerm);
        $aiKeywords = '';
        
        if ($useAI) {
            // Try to get AI-enhanced keywords
            $aiKeywords = $this->processWithAI($searchTerm);
            if ($aiKeywords) {
                // Use AI keywords for search, but keep original for context
                $searchTerm = $aiKeywords;
                $this->logAIActivity("AI processed query: '$originalSearchTerm' -> '$aiKeywords'");
            } else {
                // AI failed, use fallback keyword extraction
                $searchTerm = $this->getFallbackKeywords($originalSearchTerm);
                $this->logAIActivity("AI failed, using fallback for: '$originalSearchTerm' -> '$searchTerm'");
                $useAI = false; // Mark as failed AI attempt
            }
        }

        $search_categories = $this->model_tool_global_search->getSearchSources($searchTerm);
        $result_controllers = $this->model_tool_global_search->results_controllers;
        $results['response'] = [];

        // Track search performance for AI optimization
        $searchStartTime = microtime(true);

        foreach ($search_categories as $id => $name) {
            // $this->log->write(print_r($id, true).' search id');

            try {
                switch ($id) {
                    case 'product_categories':
                        if ($this->config->get('ai_chat_categories')) {
                            $r = $this->model_tool_global_search->getResult($id, $searchTerm, 'suggest');
                        } else {
                            $r = ['result' => []];
                        }
                        break;
                    case 'products':
                        if ($this->config->get('ai_chat_products')) {
                            $r = $this->model_tool_global_search->getResult($id, $searchTerm, 'suggest');
                        } else {
                            $r = ['result' => []];
                        }
                        break;
                    case 'reviews':
                        if ($this->config->get('ai_chat_reviews')) {
                            $r = $this->model_tool_global_search->getResult($id, $searchTerm, 'suggest');
                        } else {
                            $r = ['result' => []];
                        }
                        break;
                    case 'manufacturers':
                        if ($this->config->get('ai_chat_brands')) {
                            $r = $this->model_tool_global_search->getResult($id, $searchTerm, 'suggest');
                        } else {
                            $r = ['result' => []];
                        }
                        break;
                    case 'contents':
                        if ($this->config->get('ai_chat_pages')) {
                            $r = $this->model_tool_global_search->getResult($id, $searchTerm, 'suggest');
                        } else {
                            $r = ['result' => []];
                        }
                        break;
                    default:
                        $r = ['result' => []];
                        break;
                }
                
                // Log successful search category
                if ($this->config->get('ai_chat_debug_logging')) {
                    $resultCount = is_array($r['result']) ? count($r['result']) : 0;
                    error_log("AI Chat Search - Category '$id': $resultCount results");
                }
                
            } catch (Exception $e) {
                // Log error and continue with empty results
                error_log("AI Chat Search Error in category '$id': " . $e->getMessage());
                $this->logAIActivity("Search error in category '$id': " . $e->getMessage(), 'error');
                $r = ['result' => []];
            }

            if (is_array($r['result'])) {
                foreach ($r['result'] as $item) {
                    if (!$item) {
                        continue;
                    }
                    $tmp = [];
                    // exception for extension settings
                    /*if( $id=='settings' && !empty($item['extension'])){
                        $tmp_id='extensions';
                        if($item['type']=='total'){
                            $page_rt = sprintf($result_controllers[$tmp_id]['page2'],$item['extension']);
                        }else{
                            $page_rt = $result_controllers[ $tmp_id ]['page'];
                        }
                    } else {*/
                    $tmp_id = $id;
                    $page_rt = $result_controllers[$tmp_id]['page'];
                    // }

                    if (!is_array($result_controllers[$tmp_id]['id'])) {
                        $tmp[] = $result_controllers[$tmp_id]['id'] . '=' . $item[$result_controllers[$tmp_id]['id']];
                    } else {
                        foreach ($result_controllers[$tmp_id]['id'] as $al => $j) {
                            // if some id have alias - build link with it
                            $tmp[] = $j . '=' . $item[$j];
                        }
                    }

                    // $this->log->write(print_r($item, true).' $item array');
                    if ($item['controller'] == 'product/product' && $this->config->get('enable_seo_url')) {
                        $item['product_url'] = $this->html->getSecureSEOURL('product/product', $url . '&product_id=' . (int) $item['product_id'], '&encode');
                        // $this->log->write(print_r($item, true).' $item product array');
                    }
                    if ($item['controller'] == 'product/product' && $this->config->get('ai_chat_images')) {
                        // $this->log->write(print_r($item, true).' $item product array');
                        $resource = new AResource('image');
                        // $this->config->get('config_image_cart_width')
                        $thumbnail = $resource->getMainThumb(
                            'products',
                            (int) $item['product_id'],
                            (int) $this->config->get('config_image_grid_width'),
                            (int) $this->config->get('config_image_grid_height')
                        );
                        if (!preg_match('/no_image/', $thumbnail['thumb_url'])) {
                            $item['image'] = $thumbnail['thumb_url'];  // only path
                            // $item['image'] = $thumbnail['thumb_html']; // img src
                        }
                        // $thumbnail = $thumbnails[$item['product_id']];
                        // $this->log->write(print_r($thumbnail, true).' $item product image');
                    }

                    if ($item['controller'] == 'product/manufacturer' && $this->config->get('ai_chat_images')) {
                        // $this->log->write(print_r($item, true).' $item product array');
                        $resource = new AResource('image');
                        // $this->config->get('config_image_cart_width')
                        $thumbnail = $resource->getMainThumb(
                            'manufacturers',
                            $item['manufacturer_id'],
                            (int) $this->config->get('config_image_grid_width'),
                            (int) $this->config->get('config_image_grid_height')
                        );
                        if (!preg_match('/no_image/', $thumbnail['thumb_url'])) {
                            $item['image'] = $thumbnail['thumb_url'];  // only path
                            // $item['image'] = $thumbnail['thumb_html']; // img src
                        }
                        // $thumbnail = $thumbnails[$item['product_id']];
                        // $this->log->write(print_r($thumbnail, true).' $item product image');
                    }

                    if ($item['controller'] == 'product/category' && $this->config->get('ai_chat_images')) {
                        // $this->log->write(print_r($item, true).' $item product array');
                        $resource = new AResource('image');
                        // $this->config->get('config_image_cart_width')
                        $thumbnail = $resource->getMainThumb(
                            'categories',
                            $item['category_id'],
                            (int) $this->config->get('config_image_grid_width'),
                            (int) $this->config->get('config_image_grid_height')
                        );
                        if (!preg_match('/no_image/', $thumbnail['thumb_url'])) {
                            $item['image'] = $thumbnail['thumb_url'];  // only path
                            // $item['image'] = $thumbnail['thumb_html']; // img src
                        }
                        // $thumbnail = $thumbnails[$item['product_id']];
                        // $this->log->write(print_r($thumbnail, true).' $item product image');
                    }

                    // $this->log->write(print_r($item, true).' $item  with iamge');

                    if ($item['controller'] == 'setting/setting') {
                        $a = explode('-', $item['active']);
                        if ($a[0] == 'appearance' || $a[0] == 'im') {
                            unset($result_controllers[$tmp_id]['response']);
                        }
                    }

                    /*if( $id=='commands'){
                        $item['page'] = $item['url'];
                        unset($item['url']);
                    } else {*/
                    $item['controller'] = $result_controllers[$tmp_id]['response'] ? $this->html->getSecureURL($result_controllers[$tmp_id]['response'], '&' . implode('&', $tmp)) : '';
                    $item['page'] = $this->html->getSecureURL($page_rt, '&' . implode('&', $tmp));
                    // }

                    $item['category'] = $id;
                    $item['category_name'] = $this->language->get('text_' . $id);
                    $item['label'] = mb_strlen($item['title']) > 40 ? mb_substr($item['title'], 0, 40) . '...' : $item['title'];

                    $item['text'] = htmlentities($item['text'], ENT_QUOTES, 'utf-8', false);
                    $item['text'] = !$item['text'] ? $item['title'] : $item['text'];

                    // AI Chat Assistant: Add AI context if used
                    if ($useAI && $aiKeywords) {
                        $item['ai_enhanced'] = true;
                        $item['original_query'] = $originalSearchTerm;
                        $item['ai_keywords'] = $aiKeywords;
                    }

                    $results['response'][] = $item;
                }
            }
        }

        // AI Chat Assistant: Log performance metrics
        $searchEndTime = microtime(true);
        $searchDuration = round(($searchEndTime - $searchStartTime) * 1000, 2);
        $resultCount = count($results['response']);
        
        if ($useAI) {
            $this->logAIActivity("Search completed in {$searchDuration}ms, {$resultCount} results found" . 
                               ($aiKeywords ? " (AI enhanced)" : " (AI fallback)"));
        }

        // Add metadata for frontend
        $results['metadata'] = [
            'ai_used' => $useAI,
            'ai_keywords' => $aiKeywords,
            'original_query' => $originalSearchTerm,
            'search_duration' => $searchDuration,
            'result_count' => $resultCount,
            'search_mode' => $this->config->get('ai_chat_search_mode') ?: 'balanced'
        ];

        $this->data['response'] = $results;
        // update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode($this->data['response']));
    }

    /**
     * AI Chat Assistant: Determine if query should use AI processing
     */
    private function shouldUseAIProcessing($query)
    {
        // Check if AI is enabled
        if (!$this->config->get('ai_chat_groq_enabled')) {
            $this->logAIActivity("AI disabled in config");
            return false;
        }

        // Check if API key is configured
        if (empty($this->config->get('ai_chat_groq_api_key'))) {
            $this->logAIActivity("No Groq API key configured");
            return false;
        }

        // Simple natural language detection (self-contained)
        return $this->isNaturalLanguageQuery($query);
    }

    /**
     * AI Chat Assistant: Built-in natural language detection
     */
    private function isNaturalLanguageQuery($query)
    {
        $queryLower = strtolower(trim($query));
        $wordCount = count(explode(' ', trim($query)));
        $threshold = (int)$this->config->get('ai_chat_natural_language_threshold') ?: 4;
        
        // Spanish indicators
        $spanishIndicators = [
            'busco', 'necesito', 'quiero', 'como', 'cómo', 'donde', 'dónde',
            'para', 'con', 'sin', 'que', 'el', 'la', 'un', 'una', 'algo',
            'algún', 'alguna', 'puedo', 'puede', 'podría', 'debería'
        ];
        
        // English indicators
        $englishIndicators = [
            'looking', 'need', 'want', 'how', 'where', 'what', 'can', 'could',
            'for', 'with', 'without', 'the', 'a', 'an', 'some', 'any'
        ];
        
        $allIndicators = array_merge($spanishIndicators, $englishIndicators);
        
        // Check for natural language indicators
        foreach ($allIndicators as $indicator) {
            if (strpos($queryLower, $indicator) !== false) {
                $this->logAIActivity("Natural language detected: '$indicator' in '$query'");
                return true;
            }
        }
        
        // Check for question marks
        if (strpos($queryLower, '?') !== false || strpos($queryLower, '¿') !== false) {
            $this->logAIActivity("Question detected in: '$query'");
            return true;
        }
        
        // Check word count threshold
        if ($wordCount >= $threshold) {
            $this->logAIActivity("Long query detected: $wordCount words >= $threshold threshold");
            return true;
        }
        
        $this->logAIActivity("Traditional search query: '$query' ($wordCount words)");
        return false;
    }

    /**
     * AI Chat Assistant: Process query with Groq AI
     */
    private function processWithAI($query)
    {
        try {
            $apiKey = $this->config->get('ai_chat_groq_api_key');
            $model = $this->config->get('ai_chat_groq_model') ?: 'llama-3.1-70b-versatile';
            $temperature = (float)$this->config->get('ai_chat_groq_temperature') ?: 0.3;
            $maxTokens = (int)$this->config->get('ai_chat_groq_max_tokens') ?: 100;
            
            if (empty($apiKey)) {
                $this->logAIActivity("No API key available for Groq processing");
                return false;
            }

            // Build the prompt
            $prompt = "Eres un asistente de ecommerce. Extrae palabras clave de esta consulta de usuario para buscar productos: \"$query\"\n\nExtrae solo las palabras clave más importantes separadas por comas. Responde SOLO con las palabras clave.";

            // Prepare the API request
            $data = [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Eres un asistente que extrae palabras clave para búsquedas de ecommerce. Responde solo con palabras clave separadas por comas.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $query
                    ]
                ],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens
            ];

            // Make the API call
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://api.groq.com/openai/v1/chat/completions',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                $this->logAIActivity("cURL error: $error", 'error');
                return false;
            }

            if ($httpCode !== 200) {
                $this->logAIActivity("API error: HTTP $httpCode - $response", 'error');
                return false;
            }

            $result = json_decode($response, true);
            
            if (!$result || !isset($result['choices'][0]['message']['content'])) {
                $this->logAIActivity("Invalid API response format", 'error');
                return false;
            }

            $keywords = trim($result['choices'][0]['message']['content']);
            $this->logAIActivity("AI keywords extracted: '$keywords' from '$query'");
            
            return $keywords;
            
        } catch (Exception $e) {
            $this->logAIActivity("AI processing error: " . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * AI Chat Assistant: Fallback keyword extraction when AI fails
     */
    private function getFallbackKeywords($query)
    {
        // Remove common Spanish/English stop words
        $stopWords = [
            'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas', 'para', 'con', 'sin',
            'the', 'a', 'an', 'for', 'with', 'without', 'and', 'or', 'but', 'in', 'on', 'at'
        ];
        
        // Split into words and filter
        $words = preg_split('/[\s,.-]+/', strtolower($query));
        $keywords = [];
        
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) > 2 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }
        
        $result = implode(',', array_unique($keywords));
        $this->logAIActivity("Fallback keywords: '$result' from '$query'");
        
        return $result;
    }

    /**
     * AI Chat Assistant: Log activity for debugging and optimization
     */
    private function logAIActivity($message, $level = 'info')
    {
        // Simple logging - can be enhanced based on AbanteCart's logging system
        if ($this->config->get('ai_chat_debug_logging')) {
            error_log("AI Chat [$level]: $message");
        }
        
        // Also log to AbanteCart log if available
        if (method_exists($this, 'log') && is_object($this->log)) {
            $this->log->write("AI Chat [$level]: $message");
        }
    }

    /**
     * AI Chat Assistant: New endpoint for AI-only processing
     */
    public function ai_suggest()
    {
        // init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('ai_chat/ai_chat');

        // Get search term
        $searchTerm = $this->request->get['term'] ?? '';
        
        if (empty($searchTerm)) {
            $this->response->addJSONHeader();
            $this->response->setOutput(AJson::encode(['error' => 'No search term provided']));
            return;
        }

        // Force AI processing
        $aiKeywords = $this->processWithAI($searchTerm);
        
        $response = [
            'original_query' => $searchTerm,
            'ai_keywords' => $aiKeywords,
            'ai_enabled' => $this->config->get('ai_chat_groq_enabled'),
            'api_key_configured' => !empty($this->config->get('ai_chat_groq_api_key')),
            'fallback_keywords' => $aiKeywords ? null : $this->getFallbackKeywords($searchTerm)
        ];

        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode($response));
    }
}