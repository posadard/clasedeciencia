<!-- AI Chat Assistant Footer Template - Enhanced with Groq AI Integration - Created by posadard.com -->
<style>
/* AI Chat Assistant Styles - Created by posadard.com */
.ultraimage {
  margin-right: 4px;
  float: left;
  margin-bottom: 2px;
}

/*bs5*/
#search-category {
    display:none;
}

<?php if ($this->config->get('ai_chat_textcut')) { ?>
a.search_result {
  overflow: hidden;
  text-overflow: ellipsis;
  -o-text-overflow: ellipsis;
  white-space: nowrap;
}
<?php } ?>

.newsearchchosen:before {
    content: "\f002";
    font-family: FontAwesome;
    position:absolute;
    right: 10px;
    top: 10px;
}

<?php if (!$this->config->get('ai_chat_colour_ignore')) { ?>
.chosen-single.chosen-default {
background-color: #<?php echo $this->config->get('ai_chat_colour_1'); ?>;
}
#global_search_chosen .chosen-single span {
color: #<?php echo $this->config->get('ai_chat_colour_2'); ?>;
}

.chosen-container .chosen-results li.group-result {
background-color: #<?php echo $this->config->get('ai_chat_colour_3'); ?>;
}
.chosen-container .chosen-results li.group-result {
color: #<?php echo $this->config->get('ai_chat_colour_8'); ?>;
}

.chosen-results, .chosen-container-single .chosen-drop {
background-color: #<?php echo $this->config->get('ai_chat_colour_4'); ?>;
}

#global_search_chosen .active-result.group-option > a, #global_search_chosen .chosen-results li.group-option > a.search_result {
    color: #<?php echo $this->config->get('ai_chat_colour_5'); ?> !important;
}

#global_search_chosen .active-result.highlighted {
background-color: #<?php echo $this->config->get('ai_chat_colour_6'); ?>;
}

#global_search_chosen .highlighted a {
color: #<?php echo $this->config->get('ai_chat_colour_7'); ?> !important;
}
<?php } ?>

/* AI Chat Assistant - Legacy chosen integration styles */
#global_search_chosen.ai-enhanced .chosen-single::after {
    content: '🤖';
    position: absolute;
    right: 30px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
}

.ai-enhanced-result {
    border-left: 3px solid #667eea;
    background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, transparent 20%);
}

.ai-search-metadata {
    font-size: 10px;
    color: #667eea;
    font-style: italic;
    margin-top: 2px;
}
</style>

<script type="text/javascript">
/**
 * AI Chat Assistant JavaScript - Enhanced with Groq AI Integration
 * Enhanced search functionality for AbanteCart
 * Created by posadard.com
 */

// AI Chat Assistant Global Configuration - COMPLETE GROQ CONFIGURATION
window.AIChatGlobalConfig = {
    // Basic configuration
    enabled: <?php echo $ai_chat_groq_enabled ? 'true' : 'false'; ?>,
    mode: '<?php echo $ai_chat_search_mode ?: 'balanced'; ?>',
    threshold: <?php echo $ai_chat_natural_language_threshold ?: 4; ?>,
    searchAuto: '<?php echo $search_auto; ?>',
    newWindow: <?php echo $ai_chat_new_window ? 'true' : 'false'; ?>,
    
    // FIXED: Complete Groq AI configuration
    groqApiKey: '<?php echo $ai_chat_groq_api_key ?: ''; ?>',
    groqModel: '<?php echo $ai_chat_groq_model ?: 'llama-3.1-70b-versatile'; ?>',
    groqTemperature: <?php echo $ai_chat_groq_temperature ?: 0.3; ?>,
    groqMaxTokens: <?php echo $ai_chat_groq_max_tokens ?: 100; ?>,
    cacheDuration: <?php echo $ai_chat_cache_duration ?: 900; ?>,
    
    // Text configuration
    textOops: '<?php echo $ai_chat_text_oops; ?>',
    textSearch: '<?php echo $ai_chat_text_search; ?>',
    searchId: '<?php echo $ai_chat_search_id; ?>',
    
    // Color configuration
    colors: {
        primary: '#<?php echo $this->config->get('ai_chat_colour_1'); ?>',
        text: '#<?php echo $this->config->get('ai_chat_colour_2'); ?>',
        bg: '#<?php echo $this->config->get('ai_chat_colour_4'); ?>',
        highlight: '#<?php echo $this->config->get('ai_chat_colour_6'); ?>'
    }
};

console.log('AI Chat Assistant loaded with config:', window.AIChatGlobalConfig);

// AI Chat Assistant - Enhanced Natural Language Detection
function isNaturalLanguageQuery(query) {
    if (!window.AIChatGlobalConfig.enabled) {
        return false;
    }
    
    const threshold = window.AIChatGlobalConfig.threshold;
    const wordCount = query.trim().split(/\s+/).length;
    
    // Comprehensive natural language indicators
    const spanishIndicators = [
        // Questions
        '¿', '?', 'qué', 'que', 'cómo', 'como', 'donde', 'dónde', 'cuál', 'cual',
        // Search verbs
        'busco', 'necesito', 'quiero', 'buscar', 'encontrar', 'ver', 'comprar',
        // Connectors
        'para', 'con', 'sin', 'por', 'de', 'del', 'en', 'el', 'la', 'los', 'las',
        'un', 'una', 'unos', 'unas', 'algo', 'algún', 'alguna',
        // Common phrases
        'me gusta', 'me sirve', 'está bien', 'funciona', 'sirve para',
        'puedo', 'puede', 'podría', 'debería', 'tendría'
    ];
    
    const englishIndicators = [
        // Questions  
        'what', 'how', 'where', 'which', 'can', 'could', 'would', 'should',
        // Search verbs
        'looking', 'need', 'want', 'find', 'search', 'buy', 'get',
        // Connectors
        'for', 'with', 'without', 'the', 'a', 'an', 'some', 'any',
        // Common phrases
        'i need', 'i want', 'i like', 'good for', 'works for'
    ];
    
    const allIndicators = [...spanishIndicators, ...englishIndicators];
    
    const queryLower = query.toLowerCase();
    const hasNaturalIndicators = allIndicators.some(indicator => 
        queryLower.includes(indicator)
    );
    
    // Detect questions by punctuation
    const hasQuestionMarks = queryLower.includes('?') || queryLower.includes('¿');
    
    // Detect long phrases (more likely to be natural)
    const isLongPhrase = wordCount >= threshold;
    
    // Detect natural patterns
    const naturalPatterns = [
        /\b(busco|necesito|quiero).*(para|que|con)\b/,
        /\b(algo|algún|alguna).*(para|que|de)\b/,
        /\b(cómo|como).*(puedo|hacer|usar)\b/,
        /\b(dónde|donde).*(encontrar|comprar|ver)\b/,
        /\b(looking for|need something|want something)\b/,
        /\b(how (to|can|do)).*/,
        /\b(where (to|can|do)).*/
    ];
    
    const hasNaturalPattern = naturalPatterns.some(pattern => 
        pattern.test(queryLower)
    );
    
    const shouldUseAI = hasNaturalIndicators || hasQuestionMarks || isLongPhrase || hasNaturalPattern;
    
    // Enhanced logging
    console.log('AI Detection Enhanced:', {
        query: query,
        wordCount: wordCount,
        threshold: threshold,
        hasNaturalIndicators: hasNaturalIndicators,
        hasQuestionMarks: hasQuestionMarks,
        isLongPhrase: isLongPhrase,
        hasNaturalPattern: hasNaturalPattern,
        shouldUseAI: shouldUseAI,
        groqEnabled: window.AIChatGlobalConfig.enabled,
        apiKeyConfigured: !!window.AIChatGlobalConfig.groqApiKey
    });
    
    return shouldUseAI;
}

// Enhanced myclickFunction with AI Chat integration
function myclickFunction() {
    <?php if ($this->config->get('ai_chat_enter')) { ?>
        // Try to get search term from various inputs
        var searchkey = '';
        
        // Check AI chat input first
        if (document.querySelector('#ai-chat-input')) {
            searchkey = document.querySelector('#ai-chat-input').value;
        }
        
        // Fallback to legacy enterkey input
        if (!searchkey && document.querySelector('#enterkey')) {
            searchkey = document.querySelector('#enterkey').value;
        }
        
        // Fallback to global search chosen input
        if (!searchkey && document.querySelector('#global_search_chosen input')) {
            searchkey = document.querySelector('#global_search_chosen input').value;
        }
        
        // Default fallback
        if (!searchkey) {
            searchkey = 'search';
        }
        
        // Log the search action for AI analytics
        if (window.AIChatGlobalConfig.enabled) {
            console.log('AI Chat: Redirecting to full search page with query:', searchkey);
        }
        
        window.location = "index.php?rt=product/search&keyword=" + encodeURIComponent(searchkey) + "&category_id=0&description=1&model=1";
    <?php } ?>
}

// AI Chat Assistant - Enhanced result processing
function processAIEnhancedResults(data, originalCallback) {
    // Add AI metadata to results if available
    if (data.metadata && data.metadata.ai_used) {
        console.log('AI Chat: Enhanced results detected', data.metadata);
        
        // Mark results as AI enhanced
        if (data.response) {
            data.response.forEach(function(item) {
                if (item.ai_enhanced) {
                    item.category_name = '🤖 ' + item.category_name;
                    item.text = item.text + '<div class="ai-search-metadata">AI: ' + 
                              (data.metadata.ai_keywords || 'processed') + '</div>';
                }
            });
        }
    }
    
    return originalCallback ? originalCallback(data) : data;
}

$(document).ready(function () {
    // ENHANCED: Debug complete configuration on load
    console.log('=== AI CHAT COMPLETE CONFIGURATION ===');
    console.log('Groq Enabled:', window.AIChatGlobalConfig.enabled);
    console.log('API Key Configured:', !!window.AIChatGlobalConfig.groqApiKey);
    console.log('API Key Length:', window.AIChatGlobalConfig.groqApiKey ? window.AIChatGlobalConfig.groqApiKey.length : 0);
    console.log('Model:', window.AIChatGlobalConfig.groqModel);
    console.log('Mode:', window.AIChatGlobalConfig.mode);
    console.log('Threshold:', window.AIChatGlobalConfig.threshold);
    console.log('Search Auto URL:', window.AIChatGlobalConfig.searchAuto);
    
    // AI Chat Assistant initialization
    var searchSectionIcon = function(section) {
        switch(section) {
            case 'product_categories':
                return '<i class="fa fa-tags fa-fw"></i> ';
                break;
            case 'products':
                return '<i class="fa fa-tag fa-fw"></i> ';
                break;
            case 'reviews':
                return '<i class="fa fa-comment fa-fw"></i> ';
                break;
            case 'manufacturers':
                return '<i class="fa fa-bookmark fa-fw"></i> ';
                break;
            case 'pages':
                return '<i class="fa fa-clipboard fa-fw"></i> ';
                break;
            default:
                return '<i class="fa fa-info-circle fa-fw"></i> ';
                break;
        }
    };

    // Place chosen element - Enhanced for AI Chat compatibility
    var replace_id = '<?php echo $ai_chat_search_id; ?>';
    
    if($("#filter_category_id_block").length == 0) {
        // it doesn't exist, use configured ID
        if($("#" + replace_id).length == 0) {
            replace_id = 'filter_keyword'; // ultimate fallback
        }
    } else {
        replace_id = 'filter_category_id_block';
    }
    
    // Only initialize legacy search if AI Chat panel is not present or AI is disabled
    if (!document.getElementById('ai-chat-container') || !window.AIChatGlobalConfig.enabled) {
        console.log('AI Chat: Initializing legacy search system');
        
        $( "#" + replace_id ).before("<select id=\"global_search\" name=\"search\" data-placeholder=\"<?php echo $search_everywhere; ?>\" class=\"chosen-select form-control aselect\" style=\"display:none;\"><option></option></select>");

        // Hide default search input
        var searchw = $( "#" + replace_id ).width();
        console.log('Search width:', searchw);
        var chooswidth = 260;
        if (searchw > 160) { 
            var chooswidth = searchw;
        }
        $( "#" + replace_id ).hide();

        // Global search section initialization
        $("#global_search").chosen({
            'width': chooswidth + 'px', 
            'white-space': 'nowrap',
            no_results_text: window.AIChatGlobalConfig.textOops,
            placeholder_text_single: window.AIChatGlobalConfig.textSearch,
            search_contains: true,
            enable_split_word_search: true,
            search_contains: true
        });

        var new_w = window.AIChatGlobalConfig.newWindow ? 1 : 0;
        
        // AI Chat Assistant Enhanced AJAX functionality
        $("#global_search").ajaxChosen({
            type: 'GET',
            url: window.AIChatGlobalConfig.searchAuto,
            dataType: 'json',
            jsonTermKey: "term",
            keepTypingMsg: "<?php echo $text_continue_typing; ?>",
            lookingForMsg: "<?php echo $text_looking_for; ?>"
        }, function (data) {
            // AI Chat Assistant: Process enhanced results
            data = processAIEnhancedResults(data);
            
            if (data.response.length < 1) {
                $("#searchform").chosen({no_results_text: window.AIChatGlobalConfig.textOops});
                return '';
            }
            
            // Build result array
            var dataobj = new Object;
            $.each(data.response, function (i, row) {
                if (!dataobj[row.category]) {
                    dataobj[row.category] = new Object;
                    dataobj[row.category].name = row.category_name;
                    dataobj[row.category].icon = row.category_icon;
                    dataobj[row.category].items = [];
                    dataobj[row.category].ai_enhanced = row.ai_enhanced || false;
                }

                // Handle product URLs and navigation
                var targetUrl = row.product_url || row.page;
                var onclick = '';
                
                if (new_w == 1) {
                    onclick = 'onClick="window.open(\'' + targetUrl + '\');"';
                } else {
                    onclick = 'onClick="window.location.replace(\'' + targetUrl + '\');"';
                }

                if(typeof row.text !== 'undefined') {
                    // Build HTML for search results
                    var resultClass = row.ai_enhanced ? 'search_result ai-enhanced-result' : 'search_result';
                    var imageHTML = '';
                    
                    if(typeof row.image !== 'undefined'){
                        imageHTML = '<img class="ultraimage" src="' + row.image + '" height="40" width="40">';
                    }
                    
                    var html = '<a ' + onclick + ' style="color:' + window.AIChatGlobalConfig.colors.text + ' !important;" class="' + resultClass + '" title="' + row.text + '">' + 
                              imageHTML + row.title + 
                              (row.ai_enhanced ? '<div class="ai-search-metadata">🤖 AI Enhanced</div>' : '') + 
                              '</a>';
                }

                dataobj[row.category].items.push({value: row.order_id, text: html});
            });
            
            var results = [];
            var search_action = '<?php echo $search_action ?>&search=' + $('#global_search_chosen input').val();
            var onclick = 'onClick="window.open(\'' + search_action + '\');"';
            
            // Add "Search Everywhere" button with AI indicator
            var searchEverywhereText = '<?php echo $search_everywhere; ?>';
            if (data.metadata && data.metadata.ai_used) {
                searchEverywhereText = '🤖 ' + searchEverywhereText + ' (AI Enhanced)';
            }
            
            results.push({
                value: 0,
                text: '<div class="text-center"><a ' + onclick + ' class="btn btn-default">' + searchEverywhereText + '</a></div>'
            });
            
            $.each(dataobj, function (category, datacat) {
                var url = search_action + '#' + category;
                var onclick = 'onClick="window.open(\'' + url + '\');"';
                var header = '<span class="h5">' + searchSectionIcon(category) + datacat.name;
                
                // Add AI indicator to category if enhanced
                if (datacat.ai_enhanced) {
                    header += ' <small style="color: #667eea;">🤖</small>';
                }
                
                header += '</span>';
                
                results.push({
                    group: true,
                    text: header,
                    items: datacat.items
                });
            });
            
            // AI Chat Assistant: Mark chosen container as enhanced if AI was used
            if (data.metadata && data.metadata.ai_used) {
                $('#global_search_chosen').addClass('ai-enhanced');
                
                // Log performance metrics
                console.log('AI Chat: Search completed', {
                    mode: data.metadata.search_mode,
                    duration: data.metadata.search_duration + 'ms',
                    results: data.metadata.result_count,
                    ai_keywords: data.metadata.ai_keywords
                });
            }
            
            // Unbind chosen click events
            $('#global_search_chosen .chosen-results').unbind();

            return results;
        });
    } else {
        console.log('AI Chat: AI Chat panel detected, legacy search disabled');
    }
    
    // AI Chat Assistant: Enhanced Enter key handling for legacy inputs
    $(document).on('keypress', '#global_search_chosen input, input[type="search"]', function(e) {
        if (e.which === 13) { // Enter key
            var query = $(this).val();
            
            // If AI is enabled and this looks like a natural language query, suggest using AI Chat
            if (window.AIChatGlobalConfig.enabled && isNaturalLanguageQuery(query)) {
                console.log('AI Chat: Natural language query detected in legacy search:', query);
                
                // If AI Chat panel exists, suggest using it
                if (document.getElementById('ai-chat-container') && window.AIChatInstance) {
                    // Flash the AI chat button to draw attention
                    var chatToggle = document.getElementById('ai-chat-toggle');
                    if (chatToggle) {
                        chatToggle.style.animation = 'pulse 0.5s ease-in-out 3';
                        setTimeout(function() {
                            chatToggle.style.animation = '';
                        }, 1500);
                    }
                }
            }
            
            // Proceed with normal enter key behavior
            setTimeout(myclickFunction, 100);
        }
    });
    
    // AI Chat Assistant: Integration with legacy chosen results
    $(document).on('click', '.ai-enhanced-result', function(e) {
        console.log('AI Chat: AI enhanced result clicked');
        // Add any specific tracking or behavior for AI enhanced results
    });
    
    // AI Chat Assistant: Performance monitoring
    if (window.AIChatGlobalConfig.enabled) {
        // Monitor search performance
        var originalAjax = $.ajax;
        $.ajax = function(options) {
            if (options.url && options.url.includes('global_search_result')) {
                var startTime = performance.now();
                console.log('AI Chat: Search request started');
                
                var originalSuccess = options.success;
                options.success = function(data) {
                    var endTime = performance.now();
                    console.log('AI Chat: Search completed in', (endTime - startTime).toFixed(2), 'ms');
                    
                    if (originalSuccess) {
                        originalSuccess.apply(this, arguments);
                    }
                };
            }
            
            return originalAjax.apply(this, arguments);
        };
    }
    
    // AI Chat Assistant: Initialization complete
    console.log('AI Chat Assistant: Footer initialization complete');
    
    // Trigger custom event for other scripts
    $(document).trigger('aiChatFooterReady', {
        config: window.AIChatGlobalConfig,
        legacyEnabled: !document.getElementById('ai-chat-container')
    });
});

// ENHANCED: Global debugging functions
window.debugAIChat = function() {
    console.log('=== AI CHAT COMPLETE DEBUG ===');
    console.log('Full Config:', window.AIChatGlobalConfig);
    console.log('API Key Status:', window.AIChatGlobalConfig.groqApiKey ? 'CONFIGURED (' + window.AIChatGlobalConfig.groqApiKey.length + ' chars)' : 'NOT CONFIGURED');
    console.log('Groq Enabled:', window.AIChatGlobalConfig.enabled);
    console.log('Search Auto URL:', window.AIChatGlobalConfig.searchAuto);
    
    // Test natural language detection
    console.log('\n=== TESTING NATURAL LANGUAGE DETECTION ===');
    ['busco algo para depilar', 'iPhone 15', 'necesito un producto para cabello'].forEach(function(query) {
        var result = isNaturalLanguageQuery(query);
        console.log('Query: "' + query + '" -> AI: ' + result);
    });
    
    // Test URL construction
    console.log('\n=== TESTING URL CONSTRUCTION ===');
    var testQuery = 'test';
    var testURL = window.AIChatGlobalConfig.searchAuto + '?term=' + encodeURIComponent(testQuery) + '&ai_enabled=1';
    console.log('Test URL:', testURL);
    
    return {
        config: window.AIChatGlobalConfig,
        hasApiKey: !!window.AIChatGlobalConfig.groqApiKey,
        testURL: testURL
    };
};

// Enhanced debugSearch function
window.debugSearch = async function(query) {
    console.log('=== ENHANCED DEBUG SEARCH ===');
    console.log('Query:', query);
    
    var useAI = isNaturalLanguageQuery(query);
    console.log('Will use AI:', useAI);
    console.log('API Key Available:', !!window.AIChatGlobalConfig.groqApiKey);
    
    var baseURL = window.AIChatGlobalConfig.searchAuto;
    var finalURL = baseURL + '?term=' + encodeURIComponent(query);
    
    if (useAI) {
        finalURL += '&ai_enabled=1';
    }
    
    console.log('Request URL:', finalURL);
    
    try {
        var response = await fetch(finalURL);
        console.log('Response Status:', response.status);
        console.log('Response OK:', response.ok);
        console.log('Content-Type:', response.headers.get('content-type'));
        
        if (response.ok) {
            var data = await response.json();
            console.log('Response Data:', data);
            console.log('AI Used:', data.metadata ? data.metadata.ai_used : 'No metadata');
            console.log('AI Keywords:', data.metadata ? data.metadata.ai_keywords : 'No AI keywords');
            console.log('Results Count:', data.response ? data.response.length : 0);
            return data;
        } else {
            var text = await response.text();
            console.error('Error Response:', text.substring(0, 200));
            return null;
        }
    } catch (error) {
        console.error('Search Failed:', error);
        return null;
    }
};

// AI Chat Assistant: Global utility functions
window.AIChatUtils = {
    isNaturalLanguage: isNaturalLanguageQuery,
    redirectToSearch: myclickFunction,
    config: function() {
        return window.AIChatGlobalConfig;
    },
    log: function(message, data) {
        if (window.AIChatGlobalConfig.enabled) {
            console.log('AI Chat:', message, data || '');
        }
    },
    debug: window.debugAIChat,
    testSearch: window.debugSearch
};

// AI Chat Assistant: Backward compatibility
window.myclickFunction = myclickFunction;
</script>