<?php
/**
 * AI Chat Assistant Controller
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

class ControllerAiChatAiChat extends AController
{
    public function main()
    {
        // Pass AI Chat configuration from admin panel to template
        $this->view->assign('ai_chat_groq_enabled', $this->config->get('ai_chat_groq_enabled'));
        $this->view->assign('ai_chat_groq_api_key', $this->config->get('ai_chat_groq_api_key'));
        $this->view->assign('ai_chat_groq_model', $this->config->get('ai_chat_groq_model'));
        $this->view->assign('ai_chat_groq_temperature', $this->config->get('ai_chat_groq_temperature'));
        $this->view->assign('ai_chat_groq_max_tokens', $this->config->get('ai_chat_groq_max_tokens'));
        $this->view->assign('ai_chat_natural_language_threshold', $this->config->get('ai_chat_natural_language_threshold'));
        $this->view->assign('ai_chat_new_window', $this->config->get('ai_chat_new_window'));
        $this->view->assign('search_auto', $this->html->getSecureURL('search_auto/global_search_result/suggest'));
        
        // 🎨 NEW: Pass color theme configuration to template
        $colorTheme = $this->config->get('ai_chat_color_theme');
        $cssFileName = $this->getCSSFileName($colorTheme);
        $this->view->assign('ai_chat_color_theme', $colorTheme);
        $this->view->assign('ai_chat_css_file', $cssFileName);
        
        // 🆕 NEW: Pass configurable keywords to template
        $this->view->assign('ai_chat_generic_words_avoid', $this->config->get('ai_chat_generic_words_avoid'));
        $this->view->assign('ai_chat_support_keywords', $this->config->get('ai_chat_support_keywords'));
        
        // 🌐 MODIFIED: Get storefront default language for keywords extraction
        $storefrontDefaultLanguage = $this->getStorefrontDefaultLanguage();
        $this->view->assign('storefront_default_language', $storefrontDefaultLanguage);
        
        // AI Language Detection and Configuration (for user responses)
        $aiDefaultLanguage = $this->detectAIDefaultLanguage();
        $this->view->assign('ai_default_language', $aiDefaultLanguage);
        
        // Pass language information for debugging
        $this->view->assign('ai_language_info', $this->getLanguageDetectionInfo());
        
        $this->processTemplate('blocks/ai_chat/ai_chat.tpl');
        // init controller data
        $this->extensions->hk_UpdateData($this);
    }
    
    /**
     * 🌐 NEW: Get storefront default language from settings
     * This is the language that should be used for keywords extraction
     * because products are catalogued in this language
     * 
     * @return string Language code (en, es, fr, pt)
     */
    private function getStorefrontDefaultLanguage()
    {
        try {
            // Get storefront language from settings table
            $storefrontLang = $this->config->get('config_storefront_language');
            
            if ($storefrontLang) {
                // Map to AI supported language
                $mappedLang = $this->mapToAILanguage($storefrontLang, 'en');
                return $mappedLang;
            }
            
            // Fallback: try storefront_language_id
            $languageId = $this->config->get('storefront_language_id');
            if ($languageId) {
                $this->load->model('localisation/language');
                $language = $this->model_localisation_language->getLanguage($languageId);
                if ($language && isset($language['code'])) {
                    $mappedLang = $this->mapToAILanguage($language['code'], 'en');
                    return $mappedLang;
                }
            }
            
            // Ultimate fallback
            return 'en';
            
        } catch (Exception $e) {
            // If anything fails, return English
            return 'en';
        }
    }
    
    /**
     * 🎨 NEW: Get CSS filename based on selected color theme
     * 
     * @param string $colorTheme Selected theme from admin
     * @return string CSS filename to load
     */
    private function getCSSFileName($colorTheme)
    {
        // Validate and sanitize theme selection
        $validThemes = [
            'default' => 'ai_chat.css',
            'yellow' => 'ai_chat_yellow.css',
            'blue' => 'ai_chat_blue.css',
            'red' => 'ai_chat_red.css',
            'green' => 'ai_chat_green.css',
            'orange' => 'ai_chat_orange.css',
            'purple' => 'ai_chat_purple.css'
        ];
        
        // Return corresponding CSS file or fallback to default
        if (!empty($colorTheme) && isset($validThemes[$colorTheme])) {
            return $validThemes[$colorTheme];
        }
        
        // Fallback to default theme
        return $validThemes['default'];
    }
    
    /**
     * Detect the default language for AI responses (user interaction)
     * Priority: AbanteCart Language → Browser Language → English fallback
     * 
     * @return string Language code (en, es, fr, pt)
     */
    private function detectAIDefaultLanguage()
    {
        // Get AbanteCart current language
        $abanteLanguage = $this->getAbanteCartLanguage();
        
        // Get browser language
        $browserLanguage = $this->detectBrowserLanguage();
        
        // Map to AI supported language
        return $this->mapToAILanguage($abanteLanguage, $browserLanguage);
    }
    
    /**
     * Get current AbanteCart language code
     * 
     * @return string Language code
     */
    private function getAbanteCartLanguage()
    {
        try {
            // Try to get language code from AbanteCart language object
            if (isset($this->language) && method_exists($this->language, 'get')) {
                $langCode = $this->language->get('code');
                if ($langCode) {
                    return strtolower($langCode);
                }
            }
            
            // Fallback: try to get from config
            $languageId = $this->config->get('storefront_language_id');
            if ($languageId) {
                // Load language model to get language details
                $this->load->model('localisation/language');
                $language = $this->model_localisation_language->getLanguage($languageId);
                if ($language && isset($language['code'])) {
                    return strtolower($language['code']);
                }
            }
            
            // Ultimate fallback
            return 'en';
            
        } catch (Exception $e) {
            // If anything fails, return English
            return 'en';
        }
    }
    
    /**
     * Detect browser language from HTTP headers
     * 
     * @return string Browser language code
     */
    private function detectBrowserLanguage()
    {
        try {
            $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en';
            
            // Parse Accept-Language header: "es-ES,es;q=0.9,en;q=0.8"
            $languages = explode(',', $acceptLanguage);
            $primaryLanguage = explode(';', $languages[0])[0];
            
            // Clean and return first part (es-ES → es)
            $cleanLang = strtolower(trim($primaryLanguage));
            return explode('-', $cleanLang)[0];
            
        } catch (Exception $e) {
            return 'en';
        }
    }
    
    /**
     * Map AbanteCart/Browser language to AI supported language
     * Supported: en, es, fr, pt
     * 
     * @param string $abanteLang AbanteCart language
     * @param string $browserLang Browser language
     * @return string AI language code
     */
    private function mapToAILanguage($abanteLang, $browserLang)
    {
        // Mapping table for various language codes
        $languageMapping = [
            // English
            'en' => 'en',
            'eng' => 'en',
            'english' => 'en',
            
            // Spanish
            'es' => 'es',
            'esp' => 'es',
            'spa' => 'es',
            'spanish' => 'es',
            'español' => 'es',
            'castellano' => 'es',
            
            // French
            'fr' => 'fr',
            'fra' => 'fr',
            'fre' => 'fr',
            'french' => 'fr',
            'français' => 'fr',
            'francais' => 'fr',
            
            // Portuguese
            'pt' => 'pt',
            'por' => 'pt',
            'portuguese' => 'pt',
            'português' => 'pt',
            'portugues' => 'pt'
        ];
        
        // Supported AI languages in priority order
        $supportedLanguages = ['en', 'es', 'fr', 'pt'];
        
        // 1. Try AbanteCart language first (highest priority)
        $abanteLangCode = $this->mapLanguageCode($abanteLang, $languageMapping);
        if (in_array($abanteLangCode, $supportedLanguages)) {
            return $abanteLangCode;
        }
        
        // 2. Try browser language second
        $browserLangCode = $this->mapLanguageCode($browserLang, $languageMapping);
        if (in_array($browserLangCode, $supportedLanguages)) {
            return $browserLangCode;
        }
        
        // 3. Fallback to English
        return 'en';
    }
    
    /**
     * Helper function to map language code using mapping table
     * 
     * @param string $langCode Language code to map
     * @param array $mapping Mapping table
     * @return string|null Mapped language code or null
     */
    private function mapLanguageCode($langCode, $mapping)
    {
        $cleanCode = strtolower(trim($langCode));
        return isset($mapping[$cleanCode]) ? $mapping[$cleanCode] : null;
    }
    
    /**
     * Get language detection information for debugging
     * 
     * @return array Language detection details
     */
    private function getLanguageDetectionInfo()
    {
        return [
            'abante_language' => $this->getAbanteCartLanguage(),
            'browser_language' => $this->detectBrowserLanguage(),
            'detected_ai_language' => $this->detectAIDefaultLanguage(),
            'storefront_default_language' => $this->getStorefrontDefaultLanguage(), // 🌐 NEW
            'supported_languages' => ['en', 'es', 'fr', 'pt'],
            'http_accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'not available',
            // 🎨 NEW: Add color theme info for debugging
            'color_theme' => $this->config->get('ai_chat_color_theme'),
            'css_file' => $this->getCSSFileName($this->config->get('ai_chat_color_theme')),
            // 🆕 NEW: Add keywords info for debugging
            'generic_words_avoid' => $this->config->get('ai_chat_generic_words_avoid'),
            'support_keywords' => $this->config->get('ai_chat_support_keywords'),
            // 🌐 NEW: Add storefront language configuration details
            'config_storefront_language' => $this->config->get('config_storefront_language'),
            'storefront_language_id' => $this->config->get('storefront_language_id')
        ];
    }
}