<!-- AI Chat Assistant Template - OPTIMIZADO CON SISTEMA DE PAGINACIÓN + SOPORTE MULTILINGÜE - Created by posadard.com -->

<!-- AI Chat Full Height Panel Container -->
<div id="ai-chat-container" class="ai-chat-floating-panel">
    <!-- Toggle Button (Fixed on Right Side) -->
    <button id="ai-chat-toggle" class="ai-chat-toggle-btn">
        <i class="fa fa-search"></i>
<i class="fa fa-robot ai-chat-icon"></i>

    </button>
    
    <!-- Full Height Chat Panel -->
    <div id="ai-chat-panel" class="ai-chat-panel">
        <!-- Header Compacto -->
        <div class="ai-chat-header">
            <div class="ai-chat-title">
                <div class="ai-title-content">
                    <i class="fa fa-robot"></i>
                    <span>AI Search</span>
                </div>
                
                <button id="ai-chat-close" class="ai-chat-close-btn">
                    <i class="fa fa-angle-right"></i>
                </button>
            </div>
            
        </div>
                       <!-- Disclaimer Note (Fixed above input) -->
<!-- 🛡️ DISCLAIMER MULTILINGÜE SIMPLIFICADO - Reemplazar en ai_chat.tpl -->

<!-- Disclaimer Note (Fixed above input) -->
<div class="ai-disclaimer">
    <div id="ai-disclaimer-content" style="padding: 8px 12px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 6px; font-size: 10px; color: #6c757d; text-align: center; border-left: 3px solid #17a2b8; line-height: 1.3;">
        ℹ️ <strong>Automated AI search system.</strong> Provides product suggestions only. Does not constitute professional, medical, technical, or safety advice. Always verify product specifications, compatibility, and safety information independently before purchase or use.
    </div>
</div>
        <!-- Chat Messages Area (Scrollable) -->
        <div class="ai-chat-messages" id="ai-chat-messages">
            <div class="ai-chat-welcome">
                <div class="ai-message">
                    <div class="ai-avatar">
                        <i class="fa fa-robot"></i>
                    </div>
                    <div class="ai-bubble">
                        <p><strong>🤖 AI Assistant - ACTIVE</strong></p>
                      <p>
• Ask me anything<br>
• Hola, ¿en qué te puedo ayudar?<br>
• Demande-moi ce que tu veux<br>
• Me pergunte o que precisar!
</p>



                        <div class="ai-mode-help">
                            ✨ <strong>Multilingual AI (ES/EN/FR/PT) with English Search</strong> - Best product match guaranteed
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
 
        
        <!-- Search Input (Fixed at Bottom) -->
        <div class="ai-chat-input-container">
            <div class="ai-search-wrapper">
                <input type="text" 
                       id="ai-chat-input" 
                       class="ai-chat-input" 
                       placeholder="Ask in any language..."
                       autocomplete="off">
                <button id="ai-chat-send" class="ai-chat-send-btn">
                    <i class="fa fa-paper-plane"></i>
                </button>
            </div>


<script>
// 🌐 DISCLAIMERS MULTILINGÜES
const disclaimerTexts = {
    'es': 'ℹ️ <strong>Sistema de búsqueda IA automatizado.</strong> Proporciona solo sugerencias de productos. No constituye asesoramiento profesional, médico, técnico o de seguridad. Siempre verifique las especificaciones del producto, compatibilidad e información de seguridad de forma independiente antes de comprar o usar.',
    
    'en': 'ℹ️ <strong>Automated AI search system.</strong> Provides product suggestions only. Does not constitute professional, medical, technical, or safety advice. Always verify product specifications, compatibility, and safety information independently before purchase or use.',
    
    'fr': 'ℹ️ <strong>Système de recherche IA automatisé.</strong> Fournit uniquement des suggestions de produits. Ne constitue pas un conseil professionnel, médical, technique ou de sécurité. Vérifiez toujours les spécifications du produit, la compatibilité et les informations de sécurité de manière indépendante avant l\'achat ou l\'utilisation.',
    
    'pt': 'ℹ️ <strong>Sistema de busca IA automatizado.</strong> Fornece apenas sugestões de produtos. Não constitui aconselhamento profissional, médico, técnico ou de segurança. Sempre verifique as especificações do produto, compatibilidade e informações de segurança independentemente antes da compra ou uso.'
};

// 🔄 FUNCIÓN PARA ACTUALIZAR DISCLAIMER
function updateDisclaimer(detectedLanguage) {
    const disclaimerElement = document.getElementById('ai-disclaimer-content');
    if (!disclaimerElement) return;
    
    const lang = detectedLanguage || AIChatConfig.defaultLanguage || 'en';
    const disclaimer = disclaimerTexts[lang] || disclaimerTexts['en'];
    
    disclaimerElement.innerHTML = disclaimer;
}

// 🎯 INICIALIZAR CON IDIOMA DEL SERVIDOR
document.addEventListener('DOMContentLoaded', function() {
    const serverLanguage = AIChatConfig.defaultLanguage || 'en';
    updateDisclaimer(serverLanguage);
});

// 🔗 FUNCIÓN GLOBAL PARA INTEGRACIÓN
window.updateDisclaimerLanguage = updateDisclaimer;
</script>    
            
            <div class="ai-search-status" id="ai-search-status">
                <span class="ai-status-text"></span>
                <div class="ai-typing-indicator" style="display: none;">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Legacy search form (hidden, for compatibility) -->
    <div id="legacy-search-form" style="display: none;">
        <form id="search_form2" class="form-search top-search">
            <input name="filter_category_id_block" id="filter_category_id_block" value="0"/>
        </form>
    </div>
</div>



// Cambiar de CSS estático a CSS dinámico basado en tema seleccionado
<link rel="stylesheet" href="<?php echo $this->templateResource('/css/' . $ai_chat_css_file); ?>" />

<!-- AI Chat Assistant JavaScript - OPTIMIZADO CON PAGINACIÓN + SOPORTE MULTILINGÜE -->
<script>
/**
 * AI Chat Assistant - CONTEXTO CONVERSACIONAL EXTENDIDO + SOPORTE MULTILINGÜE
 * Combina IA de Groq con búsqueda real de productos de AbanteCart
 * Created by posadard.com
 * 
 * MODIFICACIÓN: Añadido soporte para Francés y Portugués manteniendo toda la funcionalidad existente
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Configuración integrada desde AbanteCart
const AIChatConfig = {
    // IA Configuration from AbanteCart Admin Panel
    groqEnabled: <?php echo $ai_chat_groq_enabled ? 'true' : 'false'; ?>,
    groqApiKey: '<?php echo $ai_chat_groq_api_key; ?>',
    groqModel: '<?php echo $ai_chat_groq_model; ?>',
    groqTemperature: <?php echo $ai_chat_groq_temperature ?? 0.3; ?>,
    groqMaxTokens: <?php echo $ai_chat_groq_max_tokens ?? 150; ?>,
    naturalLanguageThreshold: <?php echo $ai_chat_natural_language_threshold ?? 3; ?>,
    
    // Search Configuration from AbanteCart
    searchAuto: '<?php echo $search_auto; ?>',
    newWindow: <?php echo $ai_chat_new_window ? 'true' : 'false'; ?>,
    
    // Language Configuration - MODIFIED
    defaultLanguage: '<?php echo $ai_default_language ?? 'en'; ?>',
    storefrontDefaultLanguage: '<?php echo $storefront_default_language ?? 'en'; ?>', // 🌐 NEW: For keywords extraction
    supportedLanguages: ['en', 'es', 'fr', 'pt'],
    languageInfo: <?php echo json_encode($ai_language_info ?? []); ?>,
    
    // 🆕 NEW: Configurable Keywords from Admin Panel
    genericWordsAvoid: '<?php echo $ai_chat_generic_words_avoid ?? ''; ?>',
    supportKeywords: '<?php echo $ai_chat_support_keywords ?? ''; ?>',
    
    // Debug
    debug: false
};

// 🆕 NEW: Get generic words from admin configuration or use fallback
function getGenericWordsFromConfig() {
    if (AIChatConfig.genericWordsAvoid && AIChatConfig.genericWordsAvoid.trim() !== '') {
        return AIChatConfig.genericWordsAvoid
            .split(',')
            .map(word => word.trim().toLowerCase())
            .filter(word => word.length > 0);
    }
    
    return [
        'solution', 'product', 'care', 'treatment', 'medicine', 'chemical', 
        'liquid', 'powder', 'item', 'supply', 'supplies', 'material', 
        'substance', 'compound', 'preparation', 'formula', 'agent', 
        'therapeutic', 'medical', 'health', 'use', 'application', 'tool'
    ];
}

// 🆕 NEW: Get support keywords from admin configuration or use fallback
function getSupportKeywordsFromConfig() {
    if (AIChatConfig.supportKeywords && AIChatConfig.supportKeywords.trim() !== '') {
        return AIChatConfig.supportKeywords
            .split(',')
            .map(word => word.trim().toLowerCase())
            .filter(word => word.length > 0);
    }
    
    return [
        'contact', 'support', 'help', 'shipping', 'returns', 'payment', 
        'warranty', 'faq', 'guide', 'policy', 'delivery', 'refund'
    ];
}

// Use dynamic keywords instead of hardcoded arrays
const genericWords = getGenericWordsFromConfig();
const customerServiceTerms = getSupportKeywordsFromConfig();

    console.log('🤖 AI Chat Assistant Multilingual (ES/EN/FR/PT) with Pagination loaded');
    console.log('⚙️ Configuration:', AIChatConfig);

    // Optimized Chat Class CON SISTEMA DE PAGINACIÓN + CONTEXTO EXTENDIDO + MULTILINGÜE
    class AIProductChat {
        constructor() {
            this.isOpen = false;
            this.isProcessing = false;
            
            // ============ CONTEXTO CONVERSACIONAL EXTENDIDO ============
            this.fullConversationHistory = []; // Historial completo sin límites
            this.conversationHistory = []; // ORIGINAL: Mantener para compatibilidad
            this.conversationContext = { // Contexto adicional
                lastSearchTerms: [],
                discussedTopics: [],
                userPreferences: {},
                sessionStartTime: Date.now(),
                detectedLanguages: [] // NUEVO: Historial de idiomas detectados
            };
            // ============================================================
            
            this.pendingSearchKeywords = null;
            this.lastAIResponse = null;
            this.config = AIChatConfig;
            
            // PROPIEDADES PARA PAGINACIÓN
            this.searchResultsCache = new Map();
            this.currentDisplayedResults = new Map();
            this.resultsPerPage = 3;
            
            this.init();
        }

        init() {
            this.validateConfig();
            this.bindEvents();
            console.log('✅ AI Product Chat multilingual (ES/EN/FR/PT) with pagination initialized');
        }

        validateConfig() {
            if (!this.config.searchAuto) {
                const fallbackUrl = window.location.origin + '/index.php?rt=search_auto/global_search_result/suggest';
                this.config.searchAuto = fallbackUrl;
                console.log('🔧 Using fallback search URL:', fallbackUrl);
            }
            
            if (this.config.groqEnabled && !this.config.groqApiKey) {
                console.warn('⚠️ AI enabled but no API key configured - using hardcoded key for testing');
            }
            
            console.log('✅ Configuration validated - AI MULTILINGUAL ENABLED');
        }

        bindEvents() {
            // Toggle button
            const toggleBtn = document.getElementById('ai-chat-toggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.togglePanel();
                });
            }

            // Close button
            const closeBtn = document.getElementById('ai-chat-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.closePanel();
                });
            }

            // Search input
            const input = document.getElementById('ai-chat-input');
            const sendBtn = document.getElementById('ai-chat-send');

            if (input) {
                input.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.processQuery();
                    }
                });
            }

            if (sendBtn) {
                sendBtn.addEventListener('click', () => {
                    this.processQuery();
                });
            }

            // Close panel when clicking outside
            document.addEventListener('click', (e) => {
                if (this.isOpen && 
                    !e.target.closest('.ai-chat-panel') && 
                    !e.target.closest('.ai-chat-toggle-btn')) {
                    this.closePanel();
                }
            });

            // ESC key to close
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.closePanel();
                }
            });
        }

        togglePanel() {
            if (this.isOpen) {
                this.closePanel();
            } else {
                this.openPanel();
            }
        }

      openPanel() {
    const panel = document.getElementById('ai-chat-panel');
    const toggle = document.getElementById('ai-chat-toggle');
    
    if (panel && toggle) {
        panel.classList.add('open');
        toggle.classList.add('active');
        this.isOpen = true;
        
        // Solo hacer focus automático en desktop, no en móvil
        setTimeout(() => {
            const input = document.getElementById('ai-chat-input');
            if (input && !this.isMobileDevice()) {
                input.focus();
            }
        }, 400);
    }
}

        closePanel() {
            const panel = document.getElementById('ai-chat-panel');
            const toggle = document.getElementById('ai-chat-toggle');
            
            if (panel) panel.classList.remove('open');
            if (toggle) toggle.classList.remove('active');
            this.isOpen = false;
        }

        async processQuery() {
            const input = document.getElementById('ai-chat-input');
            const query = input.value.trim();
            
            if (!query || this.isProcessing) return;

            this.isProcessing = true;
            this.addUserMessage(query);
            input.value = '';
            
            // ============ AGREGAR AL HISTORIAL COMPLETO CON DETECCIÓN DE IDIOMA ============
            const detectedLang = this.detectLanguage(query);
            this.fullConversationHistory.push({
                role: 'user',
                content: query,
                timestamp: Date.now(),
                language: detectedLang, // NUEVO: Guardar idioma detectado
                context: {
                    searchTerms: this.conversationContext.lastSearchTerms.slice(-3),
                    topics: this.conversationContext.discussedTopics.slice(-3)
                }
            });
            
            // Actualizar historial de idiomas detectados
            if (!this.conversationContext.detectedLanguages.includes(detectedLang)) {
                this.conversationContext.detectedLanguages.push(detectedLang);
            }
            
            // MANTENER compatibilidad con el sistema original
            this.conversationHistory.push({ role: 'user', content: query });
            
            // Actualizar contexto conversacional
            this.updateConversationContext(query, detectedLang);
            // =============================================================================
            
            try {
                // Check if this is a confirmation response
                if (this.isConfirmationResponse(query, detectedLang)) {
                    await this.executeSearchFromPendingKeywords();
                } else if (this.config.groqEnabled) {
                    // AI-first conversational approach
                    await this.processConversationalAI(query, detectedLang);
                } else {
                    // Traditional search fallback
                    await this.processTraditionalSearch(query);
                }
            } catch (error) {
                this.handleError('Error procesando consulta: ' + error.message);
                console.error('❌ Processing Error:', error);
            } finally {
                this.isProcessing = false;
                this.updateStatus('');
            }
        }

        // ============ NUEVO: MÉTODOS PARA CONTEXTO EXTENDIDO + MULTILINGÜE ============
        updateConversationContext(query, detectedLang) {
            // Extraer temas discutidos en múltiples idiomas
            const topics = this.extractTopicsFromQuery(query, detectedLang);
            topics.forEach(topic => {
                if (!this.conversationContext.discussedTopics.includes(topic)) {
                    this.conversationContext.discussedTopics.push(topic);
                }
            });
            
            // Limitar a los últimos 10 temas para rendimiento
            if (this.conversationContext.discussedTopics.length > 10) {
                this.conversationContext.discussedTopics = this.conversationContext.discussedTopics.slice(-10);
            }
            
            console.log(`📝 Updated context (${detectedLang}):`, this.conversationContext);
        }

       extractTopicsFromQuery(query, detectedLang) {
    const queryLower = query.toLowerCase();
    
    // ✅ NUEVO: Términos de servicio al cliente en múltiples idiomas
    const serviceTopics = {
        // ========== INGLÉS ==========
        'en': [
            // 📞 Contacto y Soporte
            'contact', 'support', 'help', 'assistance', 'customer service', 'customer care',
            
            // 📦 Envíos y Devoluciones
            'shipping', 'delivery', 'returns', 'refund', 'exchange', 'return policy',
            'free shipping', 'express delivery', 'overnight shipping',
            
            // 💳 Pagos y Garantías
            'payment', 'warranty', 'guarantee', 'policy', 'terms', 'payment methods',
            'credit card', 'paypal', 'cash on delivery', 'installments',
            
            // ❓ Información y Guías
            'faq', 'guide', 'instructions', 'manual', 'tutorial', 'how to',
            'frequently asked questions', 'user guide', 'step by step',
            
            // 🏪 Información de Tienda
            'about us', 'store hours', 'location', 'privacy policy', 'terms of service',
            'opening hours', 'store location', 'contact information'
        ],
        
        // ========== ESPAÑOL ==========
        'es': [
            // 📞 Contacto y Soporte
            'contacto', 'soporte', 'ayuda', 'asistencia', 'servicio al cliente', 'atención al cliente',
            'servicio', 'asistencia técnica', 'chat en vivo',
            
            // 📦 Envíos y Devoluciones
            'envío', 'envíos', 'entrega', 'devolución', 'devoluciones', 'reembolso', 'cambio',
            'política de devoluciones', 'envío gratis', 'entrega express', 'envío nocturno',
            
            // 💳 Pagos y Garantías
            'pago', 'pagos', 'garantía', 'garantías', 'política', 'políticas', 'términos',
            'métodos de pago', 'tarjeta de crédito', 'paypal', 'pago contra entrega', 'cuotas',
            
            // ❓ Información y Guías
            'preguntas frecuentes', 'guía', 'guías', 'instrucciones', 'manual', 'tutorial',
            'cómo', 'paso a paso', 'ayuda en línea',
            
            // 🏪 Información de Tienda
            'sobre nosotros', 'acerca de', 'horarios', 'ubicación', 'privacidad',
            'horarios de atención', 'ubicación de tienda', 'información de contacto'
        ],
        
        // ========== FRANCÉS ==========
        'fr': [
            // 📞 Contacto y Soporte
            'contact', 'support', 'aide', 'assistance', 'service client', 'service clientèle',
            'assistance technique', 'chat en direct',
            
            // 📦 Envíos y Devoluciones
            'livraison', 'expédition', 'retour', 'retours', 'remboursement', 'échange',
            'politique de retour', 'livraison gratuite', 'livraison express', 'livraison rapide',
            
            // 💳 Pagos y Garantías
            'paiement', 'paiements', 'garantie', 'garanties', 'politique', 'politiques',
            'termes', 'méthodes de paiement', 'carte de crédit', 'paypal', 'paiement à la livraison',
            
            // ❓ Información y Guías
            'faq', 'questions fréquentes', 'guide', 'guides', 'instructions', 'manuel',
            'tutoriel', 'comment', 'étape par étape', 'aide en ligne',
            
            // 🏪 Información de Tienda
            'à propos', 'qui sommes nous', 'horaires', 'localisation', 'confidentialité',
            'heures d\'ouverture', 'emplacement du magasin', 'informations de contact'
        ],
        
        // ========== PORTUGUÉS ==========
        'pt': [
            // 📞 Contacto y Soporte
            'contato', 'suporte', 'ajuda', 'assistência', 'atendimento ao cliente', 'serviço ao cliente',
            'assistência técnica', 'chat ao vivo',
            
            // 📦 Envíos y Devoluciones
            'entrega', 'envio', 'devolução', 'devoluções', 'reembolso', 'troca',
            'política de devolução', 'frete grátis', 'entrega expressa', 'entrega rápida',
            
            // 💳 Pagos y Garantías
            'pagamento', 'pagamentos', 'garantia', 'garantias', 'política', 'políticas',
            'termos', 'métodos de pagamento', 'cartão de crédito', 'paypal', 'pagamento na entrega',
            
            // ❓ Información y Guías
            'perguntas frequentes', 'guia', 'guias', 'instruções', 'manual', 'tutorial',
            'como', 'passo a passo', 'ajuda online',
            
            // 🏪 Información de Tienda
            'sobre nós', 'quem somos', 'horários', 'localização', 'privacidade',
            'horários de funcionamento', 'localização da loja', 'informações de contato'
        ]
    };
    
    // Buscar temas en el idioma detectado y en inglés (idioma universal)
    const topicsToCheck = [
        ...(serviceTopics[detectedLang] || []),
        ...serviceTopics['en'] // Siempre incluir inglés
    ];
    
    // ✅ MEJORADO: Buscar coincidencias parciales y completas
    const foundTopics = [];
    
    topicsToCheck.forEach(topic => {
        // Buscar coincidencia exacta
        if (queryLower.includes(topic.toLowerCase())) {
            foundTopics.push(topic);
        }
        // Buscar coincidencias de palabras individuales para frases largas
        else if (topic.includes(' ')) {
            const topicWords = topic.toLowerCase().split(' ');
            const matchingWords = topicWords.filter(word => queryLower.includes(word));
            // Si coincide más del 50% de las palabras, incluir el tema
            if (matchingWords.length / topicWords.length > 0.5) {
                foundTopics.push(topic);
            }
        }
    });
    
    // Remover duplicados y retornar
    return [...new Set(foundTopics)];
}

        buildContextualPrompt(basePrompt) {
            // Construir prompt con TODO el contexto conversacional MULTILINGÜE
            let contextualPrompt = basePrompt;
            
            if (this.fullConversationHistory.length > 0) {
                const recentHistory = this.fullConversationHistory.slice(-8);
                contextualPrompt += '\n\nCONVERSATION HISTORY (MULTILINGUAL):\n';
                recentHistory.forEach(entry => {
                    contextualPrompt += `${entry.role} (${entry.language || 'unknown'}): ${entry.content}\n`;
                });
            }
            
            if (this.conversationContext.discussedTopics.length > 0) {
                contextualPrompt += '\n\nDISCUSSED TOPICS: ' + this.conversationContext.discussedTopics.join(', ');
            }
            
            if (this.conversationContext.lastSearchTerms.length > 0) {
                contextualPrompt += '\n\nPREVIOUS SEARCHES: ' + this.conversationContext.lastSearchTerms.join(', ');
            }
            
            if (this.conversationContext.detectedLanguages.length > 0) {
                contextualPrompt += '\n\nUSER LANGUAGES: ' + this.conversationContext.detectedLanguages.join(', ');
            }
            
            contextualPrompt += '\n\nIMPORTANT: Use this multilingual conversation history to provide contextual, relevant responses. Reference previous discussions when appropriate and respond in the user\'s preferred language.';
            
            return contextualPrompt;
        }
        // ===========================================================================

        isConfirmationResponse(query, detectedLang) {
            // Confirmaciones en múltiples idiomas - EXTENDIDO
            const confirmations = {
                'es': ['si', 'sí', 'vale', 'perfecto', 'exacto', 'correcto', 'adelante', 'probemos', 'buscar', 'busca'],
                'en': ['yes', 'ok', 'perfect', 'exact', 'correct', 'go ahead', 'let\'s try', 'search', 'proceed'],
                'fr': ['oui', 'ok', 'd\'accord', 'parfait', 'exact', 'correct', 'allons-y', 'essayons', 'chercher', 'recherche'], // NUEVO
                'pt': ['sim', 'ok', 'perfeito', 'exato', 'correto', 'vamos lá', 'vamos tentar', 'buscar', 'procurar'] // NUEVO
            };
            
            const queryLower = query.toLowerCase().trim();
            
            // Obtener confirmaciones para el idioma detectado + inglés
            const allConfirmations = [
                ...(confirmations[detectedLang] || []),
                ...confirmations['en'] // Siempre incluir inglés
            ];
            
            return this.pendingSearchKeywords && 
                   query.length < 20 && 
                   allConfirmations.some(conf => queryLower.includes(conf));
        }

        async processConversationalAI(query, detectedLang) {
            console.log(`💬 Processing conversational AI (${detectedLang}):`, query);
            
            // Mensaje de status en el idioma detectado
            const statusMessages = {
                'es': '🤖 Analizando tu consulta...',
                'en': '🤖 Analyzing your query...',
                'fr': '🤖 Analyse de votre requête...', // NUEVO
                'pt': '🤖 Analisando sua consulta...' // NUEVO
            };
            
            this.updateStatus(statusMessages[detectedLang] || statusMessages['en'], true);
            
            try {
                const aiResponse = await this.getConversationalAIResponse(query, detectedLang);
                const suggestedKeywords = await this.extractSuggestedKeywords(query, aiResponse, detectedLang);
                
                this.pendingSearchKeywords = suggestedKeywords;
                this.lastAIResponse = aiResponse;
                
                // AGREGAR RESPUESTA AL HISTORIAL CON IDIOMA
                this.fullConversationHistory.push({
                    role: 'assistant',
                    content: aiResponse,
                    timestamp: Date.now(),
                    suggestedKeywords: suggestedKeywords,
                    responseLanguage: detectedLang // NUEVO
                });
                
                this.displayConversationalResponse(aiResponse, suggestedKeywords, detectedLang);
                
            } catch (error) {
                console.error('Conversational AI failed:', error);
                await this.processTraditionalSearch(query);
            }
        }

  async executeSearchFromPendingKeywords() {
    if (!this.pendingSearchKeywords) {
        // Mensaje de error en múltiples idiomas
        const errorMessages = {
            'es': '<strong>🤔 ¿Buscar qué?</strong><br>No tengo palabras clave pendientes. ¿Puedes decirme qué quieres buscar?',
            'en': '<strong>🤔 Search what?</strong><br>I don\'t have pending keywords. Can you tell me what you want to search for?',
            'fr': '<strong>🤔 Chercher quoi ?</strong><br>Je n\'ai pas de mots-clés en attente. Pouvez-vous me dire ce que vous voulez chercher ?',
            'pt': '<strong>🤔 Buscar o quê?</strong><br>Não tenho palavras-chave pendentes. Pode me dizer o que quer buscar?'
        };
        
        const lastUserLang = this.getLastUserLanguage();
        this.addAIMessage(`<div class="ai-error">${errorMessages[lastUserLang] || errorMessages['en']}</div>`);
        return;
    }

    console.log('🔍 Executing search with keywords:', this.pendingSearchKeywords);
    
    // Status message en idioma apropiado
    const searchMessages = {
        'es': '🔍 Buscando productos...',
        'en': '🔍 Searching products...',
        'fr': '🔍 Recherche de produits...',
        'pt': '🔍 Procurando produtos...'
    };
    
    const lastUserLang = this.getLastUserLanguage();
    this.updateStatus(searchMessages[lastUserLang] || searchMessages['en'], true);
    
    try {
        const searchResults = await this.searchProducts(this.pendingSearchKeywords);
        this.displaySearchResults(this.pendingSearchKeywords, searchResults, lastUserLang);
        
        // Actualizar contexto con búsqueda
        this.conversationContext.lastSearchTerms.push(this.pendingSearchKeywords);
        if (this.conversationContext.lastSearchTerms.length > 5) {
            this.conversationContext.lastSearchTerms = this.conversationContext.lastSearchTerms.slice(-5);
        }
        
        this.fullConversationHistory.push({
            role: 'system',
            content: `Search executed for: ${this.pendingSearchKeywords}`,
            timestamp: Date.now(),
            searchResults: searchResults.response?.length || 0
        });
        
        this.pendingSearchKeywords = null;
        
    } catch (error) {
        this.handleError('Error buscando productos: ' + error.message);
    }
}

// NUEVO: Obtener el último idioma del usuario
getLastUserLanguage() {
    const lastUserMessage = this.fullConversationHistory
        .filter(msg => msg.role === 'user')
        .slice(-1)[0];
    return lastUserMessage ? lastUserMessage.language || 'en' : 'en';
}

async getConversationalAIResponse(query, detectedLang) {
    console.log(`🌐 AI Response for language: ${detectedLang} query: "${query}"`);
    
    // Si no se detectó idioma específico, usar el idioma por defecto del servidor
    if (!detectedLang && AIChatConfig.defaultLanguage) {
        detectedLang = AIChatConfig.defaultLanguage;
        console.log(`🎯 No language detected, using server default: ${detectedLang}`);
    }
    
    // Fallback final a inglés si no hay nada configurado
    if (!detectedLang || !AIChatConfig.supportedLanguages.includes(detectedLang)) {
        detectedLang = 'en';
        console.log(`⚠️ Unsupported language, falling back to English`);
    }

    // Sistema de prompts multilingües - MODIFICADO CON MÁXIMO 3 PALABRAS Y MÍNIMO 5 KEYWORDS
    const systemPrompts = {
         'es': `DETECCIÓN DE IDIOMA: Siempre detecta el idioma del mensaje del usuario y responde en el MISMO idioma que están usando. Si escriben en español, responde en español. Si escriben en inglés, responde en inglés. Si escriben en francés, responde en francés. Adapta tu idioma naturalmente al suyo.

Eres un asistente experto para una tienda virtual con múltiples tipos de productos, incluyendo salud, ciencia, hogar y más. Tu trabajo es: 1) ENTENDER lo que necesita el usuario 2) EXPLICAR qué es el producto, nombres alternativos, nombres científicos o técnicos, y usos comunes 3) SUGERIR palabras clave específicas en ${AIChatConfig.storefrontDefaultLanguage?.toUpperCase() || 'INGLÉS'} para buscar en nuestra base de datos 4) PREGUNTAR si quieren que busques con esas palabras clave. IMPORTANTE: TODAS las sugerencias deben estar orientadas a proteger la salud y la vida del cliente. NUNCA debes sugerir productos o usos que puedan dañar al usuario. Si por alguna razón se menciona un producto o uso potencialmente dañino, DEBES advertir claramente que es contraproducente para la salud. SIEMPRE sugiere palabras clave en ${AIChatConfig.storefrontDefaultLanguage?.toUpperCase() || 'INGLÉS'} de MÁXIMO 3 palabras cada una y MÍNIMO 5 palabras clave. Ejemplos: "blue", "methylene blue", "wound care", "stainless funnel", "protective gloves". Recuerda: detecta el idioma del usuario y responde en ese mismo idioma, pero sugiere keywords en ${AIChatConfig.storefrontDefaultLanguage?.toUpperCase() || 'INGLÉS'}. NUNCA busques directamente. Solo sugiere y pregunta.`,
    
    'en': `LANGUAGE DETECTION: Always detect the language of the user's message and respond in the SAME language they are using. If they write in Spanish, respond in Spanish. If they write in English, respond in English. If they write in French, respond in French. Match their language naturally.

You are an expert assistant for a virtual store offering a wide range of products, including health, science, home, and more. Your job is to: 1) UNDERSTAND what the user needs 2) EXPLAIN what the product is, including alternative names, scientific or technical terms, and common uses 3) SUGGEST specific keywords in ${AIChatConfig.storefrontDefaultLanguage?.toUpperCase() || 'ENGLISH'} for searching our product database 4) ASK if they want you to search with those keywords. IMPORTANT: ALL suggestions must prioritize the safety and health of the customer. NEVER suggest products or uses that could harm the user. If a potentially harmful product or use is mentioned, you MUST include a clear disclaimer that it is detrimental to health. ALWAYS suggest keywords in ${AIChatConfig.storefrontDefaultLanguage?.toUpperCase() || 'ENGLISH'} with MAXIMUM 3 words each and MINIMUM 5 keywords. Examples: "blue", "methylene blue", "wound care", "stainless funnel", "protective gloves". Remember: detect the user's language and respond in that same language, but suggest keywords in ${AIChatConfig.storefrontDefaultLanguage?.toUpperCase() || 'ENGLISH'}. NEVER search directly. Only suggest and ask.`,
    
    'fr': `DÉTECTION DE LANGUE: Détectez toujours la langue du message de l'utilisateur et répondez dans la MÊME langue qu'ils utilisent. S'ils écrivent en espagnol, répondez en espagnol. S'ils écrivent en anglais, répondez en anglais. S'ils écrivent en français, répondez en français. Adaptez votre langue naturellement à la leur.

Vous êtes un assistant expert pour une boutique en ligne offrant une large gamme de produits, y compris santé, science, maison et plus. Votre travail est de : 1) COMPRENDRE les besoins de l'utilisateur 2) EXPLIQUER ce qu'est le produit, y compris les noms alternatifs, les termes scientifiques ou techniques, et les usages courants 3) SUGGÉRER des mots-clés spécifiques en ${AIChatConfig.storefrontDefaultLanguage?.toUpperCase() || 'ANGLAIS'} pour rechercher dans notre base de données 4) DEMANDER s'ils veulent que vous recherchiez avec ces mots-clés. IMPORTANT : TOUTES les suggestions doivent viser à protéger la santé et la vie du client. NE suggérez JAMAIS un produit ou un usage pouvant nuire à l'utilisateur. Si un produit ou un usage potentiellement dangereux est mentionné, vous DEVEZ inclure un avertissement clair indiquant qu'il est nuisible pour la santé. TOUJOURS suggérer des mots-clés en ${AIChatConfig.storefrontDefaultLanguage?.toUpperCase() || 'ANGLAIS'} avec MAXIMUM 3 mots chacun et MINIMUM 5 mots-clés. Exemples : "blue", "methylene blue", "wound care", "stainless funnel", "protective gloves". Rappelez-vous : détectez la langue de l'utilisateur et répondez dans cette même langue, mais suggérez des keywords en ${AIChatConfig.storefrontDefaultLanguage?.toUpperCase() || 'ANGLAIS'}. NE recherchez JAMAIS directement. Suggérez seulement et demandez.`,
    
    'pt': `DETECÇÃO DE IDIOMA: Sempre detecte o idioma da mensagem do usuário e responda no MESMO idioma que estão usando. Se escrevem em espanhol, responda em espanhol. Se escrevem em inglês, responda em inglês. Se escrevem em francês, responda em francês. Adapte seu idioma naturalmente ao deles.

Você é um assistente especialista para uma loja virtual com uma ampla variedade de produtos, incluindo saúde, ciência, casa e mais. Seu trabalho é: 1) ENTENDER o que o usuário precisa 2) EXPLICAR o que é o produto, incluindo nomes alternativos, termos científicos ou técnicos, e usos comuns 3) SUGERIR palavras-chave específicas em ${AIChatConfig.storefrontDefaultLanguage?.toUpperCase() || 'INGLÊS'} para buscar em nossa base de dados 4) PERGUNTAR se querem que você busque com essas palavras-chave. IMPORTANTE: TODAS as sugestões devem priorizar a segurança e a vida do cliente. NUNCA sugira produtos ou usos que possam prejudicar o usuário. Se for mencionado algo potencialmente prejudicial, você DEVE incluir um aviso claro de que é prejudicial à saúde. SEMPRE sugira palavras-chave em ${AIChatConfig.storefrontDefaultLanguage?.toUpperCase() || 'INGLÊS'} com no MÁXIMO 3 palavras cada e no MÍNIMO 5 palavras-chave. Exemplos: "blue", "methylene blue", "wound care", "stainless funnel", "protective gloves". Lembre-se: detecte o idioma do usuário e responda nesse mesmo idioma, mas sugira keywords em ${AIChatConfig.storefrontDefaultLanguage?.toUpperCase() || 'INGLÊS'}. NUNCA pesquise diretamente. Apenas sugira e pergunte.`
 };

    // CONSTRUIR PROMPT CON TODO EL CONTEXTO MULTILINGÜE
    let systemPrompt = systemPrompts[detectedLang] || systemPrompts['en'];
    systemPrompt = this.buildContextualPrompt(systemPrompt);

    // Log para debug
    console.log(`🎯 Using system prompt for language: ${detectedLang}`);
    if (AIChatConfig.debug) {
        console.log(`📝 System prompt preview: ${systemPrompt.substring(0, 200)}...`);
        console.log(`🗣️ Language detection info:`, AIChatConfig.languageInfo);
    }

    const messages = [
        {
            role: 'system',
            content: systemPrompt
        },
        // Usar historial completo con información de idioma
        ...this.fullConversationHistory.slice(-6).map(entry => ({
            role: entry.role === 'system' ? 'assistant' : entry.role,
            content: entry.content
        })),
        {
            role: 'user',
            content: query
        }
    ];

    return await this.callGroqAPI(messages);
}

detectLanguage(text) {
    // Si es la primera interacción y no hay historial, usar idioma detectado del servidor
    if (this.fullConversationHistory.length === 0 && AIChatConfig.defaultLanguage) {
        console.log(`🌐 Using server-detected default language: ${AIChatConfig.defaultLanguage}`);
        console.log(`📊 Language detection info:`, AIChatConfig.languageInfo);
        return AIChatConfig.defaultLanguage;
    }
    
    const textLower = text.toLowerCase();
    const scores = { 'es': 0, 'en': 0, 'fr': 0, 'pt': 0 };

    // ========== INDICADORES ÚNICOS POR IDIOMA ==========
    
    // 🇪🇸 INDICADORES ÚNICOS DEL ESPAÑOL
    const spanishUnique = [
        // Palabras que NO existen en portugués
        'cómo', 'dónde', 'qué', 'cuándo', 'cuánto', 'cuál',
        'ñ', 'niño', 'niña', 'año', 'baño', 'español',
        'muy', 'bien', 'también', 'sí', 'no', 'hola',
        'gracias', 'por favor', 'de nada', 'hasta luego',
        'bueno', 'malo', 'grande', 'pequeño',
        // Verbos conjugados únicos del español
        'estoy', 'estas', 'somos', 'tengo', 'tienes', 'tiene',
        'quiero', 'quieres', 'necesito', 'necesitas',
        'puedo', 'puedes', 'puede', 'hacemos', 'hacen'
    ];
    
    // 🇵🇹 INDICADORES ÚNICOS DEL PORTUGUÉS
    const portugueseUnique = [
        // Palabras que NO existen en español
        'não', 'sim', 'obrigado', 'obrigada', 'por favor',
        'de nada', 'com licença', 'olá', 'tchau', 'até logo',
        'muito', 'bem', 'também', 'português', 'brasil',
        'ção', 'são', 'mão', 'pão', 'coração',
        // Terminaciones únicas portuguesas
        'ãe', 'ão', 'õe', 'nh', 'lh',
        // Verbos conjugados únicos del portugués
        'estou', 'está', 'somos', 'tenho', 'tem', 'têm',
        'quero', 'quer', 'preciso', 'precisa', 'precisam',
        'posso', 'pode', 'podem', 'fazemos', 'fazem',
        // Artículos y preposiciones únicas
        'numa', 'numa', 'desta', 'deste', 'dessa', 'desse'
    ];
    
    // 🇫🇷 INDICADORES ÚNICOS DEL FRANCÉS
    const frenchUnique = [
        'où', 'quand', 'comment', 'pourquoi', 'combien',
        'bonjour', 'bonsoir', 'merci', 's\'il vous plaît',
        'de rien', 'excusez-moi', 'pardon', 'au revoir',
        'très', 'bien', 'aussi', 'français', 'france',
        'ç', 'è', 'é', 'à', 'ù', 'ê', 'ô', 'î', 'â',
        // Artículos y contracciones únicas
        'du', 'des', 'au', 'aux', 'le', 'la', 'les',
        'je', 'tu', 'il', 'elle', 'nous', 'vous', 'ils', 'elles',
        'suis', 'est', 'sommes', 'êtes', 'sont',
        'ai', 'as', 'avons', 'avez', 'ont'
    ];
    
    // 🇺🇸 INDICADORES ÚNICOS DEL INGLÉS
    const englishUnique = [
        'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by',
        'what', 'where', 'when', 'how', 'why', 'which', 'who',
        'hello', 'hi', 'goodbye', 'thank you', 'thanks', 'please',
        'you\'re welcome', 'excuse me', 'sorry',
        'very', 'good', 'bad', 'big', 'small', 'also', 'too',
        'english', 'america', 'united states',
        // Verbos auxiliares únicos
        'am', 'is', 'are', 'was', 'were', 'have', 'has', 'had',
        'do', 'does', 'did', 'will', 'would', 'should', 'could',
        'can', 'may', 'might', 'must'
    ];

    // ========== PUNTUACIÓN POR INDICADORES ÚNICOS ==========
    
    // Verificar indicadores únicos (peso máximo)
    spanishUnique.forEach(indicator => {
        if (textLower.includes(indicator)) {
            scores['es'] += 10; // Alto peso para indicadores únicos
        }
    });
    
    portugueseUnique.forEach(indicator => {
        if (textLower.includes(indicator)) {
            scores['pt'] += 10;
        }
    });
    
    frenchUnique.forEach(indicator => {
        if (textLower.includes(indicator)) {
            scores['fr'] += 10;
        }
    });
    
    englishUnique.forEach(indicator => {
        if (textLower.includes(indicator)) {
            scores['en'] += 10;
        }
    });

    // ========== CARACTERES ESPECÍFICOS (PESO MEDIO) ==========
    
    const languageChars = {
        'es': /[ñáéíóúü¿¡]/g,
        'pt': /[ãõáàâéêíóôúç]/g,
        'fr': /[àâäéèêëïîôöùûüÿç]/g,
        'en': /\b(th[ei]s?|that|these|those)\b/gi // Palabras con 'th' únicas del inglés
    };

    Object.keys(languageChars).forEach(lang => {
        const matches = text.match(languageChars[lang]) || [];
        scores[lang] += matches.length * 5; // Peso medio para caracteres
    });

    // ========== TERMINACIONES ESPECÍFICAS (PESO MEDIO) ==========
    
    // Terminaciones españolas vs portuguesas
    const spanishEndings = /\b\w+(ción|sión|dad|tad|mente|oso|osa|ito|ita)\b/gi;
    const portugueseEndings = /\b\w+(ção|são|dade|mente|oso|osa|inho|inha)\b/gi;
    const frenchEndings = /\b\w+(tion|sion|té|ment|eux|euse|ique)\b/gi;
    const englishEndings = /\b\w+(tion|sion|ty|ness|ful|less|ing|ed)\b/gi;
    
    const spanishEndingMatches = text.match(spanishEndings) || [];
    const portugueseEndingMatches = text.match(portugueseEndings) || [];
    const frenchEndingMatches = text.match(frenchEndings) || [];
    const englishEndingMatches = text.match(englishEndings) || [];
    
    scores['es'] += spanishEndingMatches.length * 3;
    scores['pt'] += portugueseEndingMatches.length * 3;
    scores['fr'] += frenchEndingMatches.length * 3;
    scores['en'] += englishEndingMatches.length * 3;

    // ========== PALABRAS ESPECÍFICAS TÉCNICAS (PESO BAJO) ==========
    
    const technicalWords = {
        'es': ['medicamento', 'tratamiento', 'químico', 'antiséptico'],
        'pt': ['medicamento', 'tratamento', 'químico', 'antisséptico'],
        'fr': ['médicament', 'traitement', 'chimique', 'antiseptique'],
        'en': ['medicine', 'treatment', 'chemical', 'antiseptic']
    };

    Object.keys(technicalWords).forEach(lang => {
        technicalWords[lang].forEach(word => {
            if (textLower.includes(word)) {
                scores[lang] += 2; // Peso bajo para palabras técnicas
            }
        });
    });

    // ========== VERIFICACIÓN ESPECIAL ESPAÑOL VS PORTUGUÉS ==========
    
    // Si tanto español como portugués tienen puntuación alta, hacer verificación adicional
    if (scores['es'] > 5 && scores['pt'] > 5) {
        console.log('🔍 Spanish vs Portuguese conflict detected, applying additional checks...');
        
        // Verificaciones específicas para desempatar
        const spanishSpecific = /\b(cómo|dónde|qué|cuándo|muy|bien|también|sí|hola|gracias)\b/gi;
        const portugueseSpecific = /\b(não|sim|obrigado|muito|bem|também|olá|tchau)\b/gi;
        
        const spanishSpecificMatches = text.match(spanishSpecific) || [];
        const portugueseSpecificMatches = text.match(portugueseSpecific) || [];
        
        if (spanishSpecificMatches.length > portugueseSpecificMatches.length) {
            scores['es'] += 5;
            console.log('🇪🇸 Resolved as Spanish due to specific indicators');
        } else if (portugueseSpecificMatches.length > spanishSpecificMatches.length) {
            scores['pt'] += 5;
            console.log('🇵🇹 Resolved as Portuguese due to specific indicators');
        }
    }

    // ========== DECISIÓN FINAL ==========
    
    // Encontrar el idioma con mayor puntuación
    const detectedLang = Object.keys(scores).reduce((a, b) => scores[a] > scores[b] ? a : b);
    
    // Si no hay suficiente puntuación, usar el idioma por defecto del servidor
    const maxScore = Math.max(...Object.values(scores));
    if (maxScore < 3 && AIChatConfig.defaultLanguage) {
        console.log(`🎯 Low detection confidence (${maxScore}), using server default: ${AIChatConfig.defaultLanguage}`);
        return AIChatConfig.defaultLanguage;
    }
    
    console.log(`🌐 Language detection scores:`, scores, `→ Detected: ${detectedLang} for: "${text}"`);
    
    // Log adicional para casos de español vs portugués
    if (scores['es'] > 0 && scores['pt'] > 0) {
        console.log(`🔍 ES vs PT conflict: ES=${scores['es']}, PT=${scores['pt']}, Final: ${detectedLang}`);
    }
    
    return detectedLang;
}

async extractSuggestedKeywords(originalQuery, aiResponse, detectedLang) {
    // 🌐 MODIFICADO: Usar idioma del storefront para keywords en lugar del detectado
    const storefrontLang = AIChatConfig.storefrontDefaultLanguage || 'en';
    
    // 🌐 NUEVO: Prompts predefinidos para idiomas principales
    const predefinedPrompts = {
        'es': `Extrae SOLO las palabras clave más importantes en ESPAÑOL mencionadas en la respuesta del asistente para buscar productos en nuestra base de datos. IMPORTANTE: Cada palabra clave debe tener MÁXIMO 3 palabras. EVITA palabras genéricas como: "solución", "producto", "cuidado", "tratamiento", "medicina", "químico", "líquido", "polvo". Prefiere términos específicos y técnicos. Ejemplos: "azul", "azul de metileno", "antiséptico heridas", "desinfectante antiséptico", "colorante metileno". Responde SOLO con las palabras clave en español separadas por comas pero me tienes que dar como mínimo 5 palabras clave específicas.`,
        
        'en': `Extract ONLY the most important ENGLISH keywords mentioned in the assistant's response for searching products in our database. IMPORTANT: Each keyword must have MAXIMUM 3 words. AVOID generic words like: "solution", "product", "care", "treatment", "medicine", "chemical", "liquid", "powder". Prefer specific and technical terms. Examples: "blue", "methylene blue", "wound antiseptic", "antiseptic disinfectant", "methylene dye". Always respond with English keywords separated by commas, no explanations, but you must give me at least 5 specific keywords.`,
        
        'fr': `Extrayez SEULEMENT les mots-clés les plus importants en FRANÇAIS mentionnés dans la réponse de l'assistant pour rechercher des produits dans notre base de données. IMPORTANT: Chaque mot-clé doit avoir MAXIMUM 3 mots. ÉVITEZ les mots génériques comme: "solution", "produit", "soin", "traitement", "médecine", "chimique", "liquide", "poudre". Préférez les termes spécifiques et techniques. Exemples: "bleu", "bleu de méthylène", "antiseptique plaie", "désinfectant antiseptique", "colorant méthylène". Répondez SEULEMENT avec les mots-clés français séparés par des virgules mais vous devez me donner au moins 5 mots-clés spécifiques.`,
        
        'pt': `Extraia APENAS as palavras-chave mais importantes em PORTUGUÊS mencionadas na resposta do assistente para buscar produtos em nossa base de dados. IMPORTANTE: Cada palavra-chave deve ter NO MÁXIMO 3 palavras. EVITE palavras genéricas como: "solução", "produto", "cuidado", "tratamento", "medicina", "químico", "líquido", "pó". Prefira termos específicos e técnicos. Exemplos: "azul", "azul de metileno", "antisséptico ferida", "desinfetante antisséptico", "corante metileno". Responda APENAS com as palavras-chave em português separadas por vírgulas mas você deve me dar pelo menos 5 palavras-chave específicas.`
    };

    // 🌐 NUEVO: Función para generar prompt dinámico para cualquier idioma
    function generateDynamicPrompt(languageCode) {
        // Mapeo de códigos de idioma a nombres completos
        const languageNames = {
            'en': 'ENGLISH',
            'es': 'SPANISH',
            'fr': 'FRENCH', 
            'pt': 'PORTUGUESE',
            'de': 'GERMAN',
            'it': 'ITALIAN',
            'zh': 'CHINESE',
            'ja': 'JAPANESE',
            'ko': 'KOREAN',
            'ru': 'RUSSIAN',
            'ar': 'ARABIC',
            'hi': 'HINDI',
            'nl': 'DUTCH',
            'sv': 'SWEDISH',
            'da': 'DANISH',
            'no': 'NORWEGIAN',
            'pl': 'POLISH',
            'tr': 'TURKISH',
            'th': 'THAI',
            'vi': 'VIETNAMESE'
        };
        
        const languageName = languageNames[languageCode] || languageCode.toUpperCase();
        
        return `Extract ONLY the most important ${languageName} keywords mentioned in the assistant's response for searching products in our database. IMPORTANT: Each keyword must have MAXIMUM 3 words. AVOID generic words like: "solution", "product", "care", "treatment", "medicine", "chemical", "liquid", "powder". Prefer specific and technical terms. Always respond with ${languageName} keywords separated by commas, no explanations, but you must give me at least 5 specific keywords.`;
    }

    // 🌐 MODIFICADO: Usar prompt predefinido o generar dinámicamente
    let systemPrompt;
    
    if (predefinedPrompts[storefrontLang]) {
        // Usar prompt predefinido optimizado
        systemPrompt = predefinedPrompts[storefrontLang];
        console.log(`🎯 Using predefined prompt for "${storefrontLang}"`);
    } else {
        // Generar prompt dinámico para idiomas no soportados
        systemPrompt = generateDynamicPrompt(storefrontLang);
        console.log(`🔧 Generated dynamic prompt for "${storefrontLang}"`);
    }
    
    // 🌐 MODIFICADO: Especificar idioma del storefront en el prompt del usuario
    const languageNames = {
        'en': 'ENGLISH',
        'es': 'SPANISH', 
        'fr': 'FRENCH',
        'pt': 'PORTUGUESE',
        'de': 'GERMAN',
        'it': 'ITALIAN',
        'zh': 'CHINESE',
        'ja': 'JAPANESE',
        'ko': 'KOREAN',
        'ru': 'RUSSIAN',
        'ar': 'ARABIC',
        'hi': 'HINDI',
        'nl': 'DUTCH',
        'sv': 'SWEDISH',
        'da': 'DANISH',
        'no': 'NORWEGIAN',
        'pl': 'POLISH',
        'tr': 'TURKISH',
        'th': 'THAI',
        'vi': 'VIETNAMESE'
    };
    
    const targetLanguageName = languageNames[storefrontLang] || storefrontLang.toUpperCase();
    
    const userPrompt = `Original query: "${originalQuery}"
Assistant response: "${aiResponse}"

Extract the best ${targetLanguageName} keywords for product search:`;

    const messages = [
        {
            role: 'system',
            content: systemPrompt
        },
        {
            role: 'user',
            content: userPrompt
        }
    ];

    // 🌐 MODIFICADO: Log para mostrar idiomas usados
    console.log(`🌐 Extracting keywords in storefront language "${storefrontLang}" (user language: "${detectedLang}")`);

    try {
        const keywords = await this.callGroqAPI(messages);
        
        // 🌐 MODIFICADO: Validar usando idioma del storefront
        const validatedKeywords = this.validateAndFixKeywords(keywords, storefrontLang);
        console.log(`🔍 Original keywords: "${keywords}"`);
        console.log(`✅ Validated keywords: "${validatedKeywords}" for storefront language "${storefrontLang}" from query: "${originalQuery}"`);
        return validatedKeywords;
        
    } catch (error) {
        console.error('Keyword extraction failed:', error);
        // 🌐 MODIFICADO: Fallback usando idioma del storefront
        const fallbackKeywords = this.extractEnglishKeywordsFallback(originalQuery, storefrontLang);
        console.log(`🔧 Fallback ${storefrontLang} keywords: "${fallbackKeywords}"`);
        return fallbackKeywords;
    }
}


// ========== NUEVA FUNCIÓN: VALIDACIÓN Y CORRECCIÓN DE KEYWORDS ==========
validateAndFixKeywords(keywords, detectedLang) {
    if (!keywords || typeof keywords !== 'string') {
        return '';
    }
    
    // 🌐 MODIFICADO: Usar idioma del storefront para validación en lugar del detectado
    const storefrontLang = AIChatConfig.storefrontDefaultLanguage || 'en';
    
    // Dividir keywords por comas
    const keywordList = keywords.split(',').map(k => k.trim()).filter(k => k.length > 0);
    const validatedList = [];
    
    console.log(`🌐 Using storefront language "${storefrontLang}" for keyword validation (detected user language: "${detectedLang}")`);
    
    keywordList.forEach(keyword => {
        // Remover comillas si existen
        const cleanKeyword = keyword.replace(/["']/g, '').trim();
        
        // Contar palabras (dividir por espacios)
        const words = cleanKeyword.split(/\s+/).filter(w => w.length > 0);
        
        // Verificar si la keyword contiene palabras genéricas
        const isGeneric = this.isGenericKeyword(cleanKeyword, genericWords);
        
        if (!isGeneric && words.length >= 1 && words.length <= 3) {
            // Keyword válida (1, 2 o 3 palabras) y no genérica
            validatedList.push(cleanKeyword);
        } else if (!isGeneric && words.length > 3) {
            // Keyword muy larga pero no genérica - tomar solo las 3 primeras palabras más importantes
            const truncatedKeyword = words.slice(0, 3).join(' ');
            if (!this.isGenericKeyword(truncatedKeyword, genericWords)) {
                validatedList.push(truncatedKeyword);
                console.log(`⚠️ Keyword truncated: "${cleanKeyword}" → "${truncatedKeyword}"`);
            }
        } else if (isGeneric) {
            console.log(`❌ Generic keyword rejected: "${cleanKeyword}"`);
        }
        // Si words.length === 0, se ignora la keyword vacía
    });
    
    // Verificar mínimo de 5 keywords
    if (validatedList.length < 5) {
        console.log(`⚠️ Only ${validatedList.length} specific keywords generated, minimum required: 5`);
        
        // 🌐 MODIFICADO: Usar idioma del storefront para generar keywords adicionales
        const additionalKeywords = this.generateAdditionalKeywords(validatedList, storefrontLang, genericWords);
        validatedList.push(...additionalKeywords);
    }
    
    // 🌐 MODIFICADO: Log con información de idiomas para debug
    console.log(`🔧 Keyword validation using storefront language "${storefrontLang}" (user: "${detectedLang}"): ${keywordList.length} → ${validatedList.length} specific keywords processed (min: 5)`);
    
    return validatedList.join(', ');
}

// ========== NUEVA FUNCIÓN: VERIFICAR SI KEYWORD ES GENÉRICA ==========
isGenericKeyword(keyword, genericWords) {
    const keywordLower = keyword.toLowerCase();
    
    // Verificar si la keyword es completamente genérica
    if (genericWords.includes(keywordLower)) {
        return true;
    }
    
    // Verificar si la keyword contiene solo palabras genéricas
    const words = keywordLower.split(/\s+/);
    const genericWordCount = words.filter(word => genericWords.includes(word)).length;
    
    // Si más del 50% de las palabras son genéricas, rechazar
    if (genericWordCount / words.length > 0.5) {
        return true;
    }
    
    return false;
}

// ========== NUEVA FUNCIÓN: GENERAR KEYWORDS ADICIONALES ESPECÍFICAS ==========
generateAdditionalKeywords(existingKeywords, detectedLang, genericWords) {
    const additionalKeywords = [];
    
    // Generar variaciones de keywords existentes
    existingKeywords.forEach(keyword => {
        const words = keyword.split(' ');
        
        // Si tiene 2-3 palabras, crear keywords de palabras individuales (solo si no son genéricas)
        if (words.length > 1) {
            words.forEach(word => {
                if (word.length > 2 && 
                    !genericWords.includes(word.toLowerCase()) &&
                    !existingKeywords.some(k => k.includes(word)) && 
                    !additionalKeywords.includes(word)) {
                    additionalKeywords.push(word);
                }
            });
        }
    });
    
    // Keywords específicas técnicas como respaldo (NO genéricas)
  // ✅ NUEVO: Keywords de servicio al cliente (REEMPLAZAR specificFallbackKeywords)
const specificFallbackKeywords = [
    // 📞 Contacto y Soporte
    'contact', 'support', 'help', 'assistance', 'service', 'customer service',
    
    // 📦 Envíos y Devoluciones
    'shipping', 'delivery', 'returns', 'refund', 'exchange', 'return policy',
    
    // 💳 Pagos y Garantías
    'payment', 'warranty', 'guarantee', 'policy', 'terms', 'payment methods',
    
    // ❓ Información y Guías
    'faq', 'guide', 'instructions', 'manual', 'tutorial', 'how to',
    
    // 🏪 Información de Tienda
    'about us', 'store hours', 'location', 'privacy policy', 'terms of service'
];
    
    for (const fallback of specificFallbackKeywords) {
        if (additionalKeywords.length + existingKeywords.length >= 5) break;
        if (!existingKeywords.some(k => k.includes(fallback)) && 
            !additionalKeywords.includes(fallback) &&
            !genericWords.includes(fallback)) {
            additionalKeywords.push(fallback);
        }
    }
    
    console.log(`🔧 Generated ${additionalKeywords.length} additional specific keywords: ${additionalKeywords.join(', ')}`);
    return additionalKeywords.slice(0, Math.max(0, 5 - existingKeywords.length));
}
// ====================================================================

extractEnglishKeywordsFallback(query, detectedLang) {
    // Lista de palabras genéricas a evitar
  // ✅ NUEVO: Palabras genéricas universales para múltiples tipos de negocio
const genericWords = [
    // 🛍️ TÉRMINOS COMERCIALES GENÉRICOS
    'product', 'products', 'item', 'items', 'thing', 'things', 'stuff',
    'merchandise', 'goods', 'article', 'articles', 'piece', 'pieces',
    
    // 🏪 TÉRMINOS DE VENTA GENÉRICOS
    'sale', 'buy', 'purchase', 'order', 'shopping', 'store', 'shop',
    'business', 'company', 'brand', 'service', 'services',
    
    // 💰 TÉRMINOS FINANCIEROS GENÉRICOS
    'price', 'cost', 'money', 'cheap', 'expensive', 'discount', 'offer',
    'deal', 'promotion', 'special', 'value', 'budget',
    
    // 📦 TÉRMINOS LOGÍSTICOS GENÉRICOS
    'supply', 'supplies', 'material', 'materials', 'equipment',
    'inventory', 'stock', 'available', 'delivery', 'shipping',
    
    // 🔧 TÉRMINOS TÉCNICOS GENÉRICOS
    'tool', 'tools', 'device', 'machine', 'system', 'technology',
    'solution', 'solutions', 'application', 'applications', 'use', 'usage',
    
    // 🏥 TÉRMINOS MÉDICOS/SALUD GENÉRICOS
    'medicine', 'medical', 'health', 'healthcare', 'treatment', 'therapy',
    'care', 'healing', 'cure', 'remedy', 'therapeutic', 'clinical',
    
    // 🧪 TÉRMINOS QUÍMICOS/CIENTÍFICOS GENÉRICOS
    'chemical', 'chemicals', 'substance', 'substances', 'compound', 'compounds',
    'formula', 'formulas', 'preparation', 'preparations', 'agent', 'agents',
    'liquid', 'powder', 'solid', 'mixture', 'blend',
    
    // 🏠 TÉRMINOS DOMÉSTICOS GENÉRICOS
    'household', 'home', 'domestic', 'family', 'personal', 'daily',
    'everyday', 'routine', 'regular', 'common', 'standard',
    
    // 👕 TÉRMINOS TEXTILES/MODA GENÉRICOS
    'clothing', 'apparel', 'wear', 'fashion', 'style', 'design',
    'fabric', 'textile', 'garment', 'outfit', 'accessory',
    
    // 🍕 TÉRMINOS ALIMENTARIOS GENÉRICOS
    'food', 'foods', 'nutrition', 'nutritional', 'dietary', 'ingredient',
    'ingredients', 'supplement', 'supplements', 'organic', 'natural',
    
    // 💻 TÉRMINOS TECNOLÓGICOS GENÉRICOS
    'digital', 'electronic', 'software', 'hardware', 'computer',
    'internet', 'online', 'virtual', 'smart', 'automated',
    
    // 🚗 TÉRMINOS AUTOMOTRICES GENÉRICOS
    'automotive', 'vehicle', 'car', 'auto', 'motor', 'engine',
    'parts', 'component', 'components', 'accessory', 'accessories',
    
    // 🏋️ TÉRMINOS DEPORTIVOS/FITNESS GENÉRICOS
    'fitness', 'sport', 'sports', 'exercise', 'workout', 'training',
    'athletic', 'performance', 'activity', 'recreation',
    
    // 🎨 TÉRMINOS CREATIVOS/ARTE GENÉRICOS
    'creative', 'artistic', 'craft', 'crafts', 'hobby', 'decoration',
    'decorative', 'ornament', 'design', 'pattern',
    
    // 🏗️ TÉRMINOS CONSTRUCCIÓN/HOGAR GENÉRICOS
    'construction', 'building', 'repair', 'maintenance', 'improvement',
    'renovation', 'installation', 'assembly', 'mounting',
    
    // 📚 TÉRMINOS EDUCATIVOS GENÉRICOS
    'educational', 'learning', 'study', 'academic', 'school',
    'training', 'course', 'lesson', 'tutorial',
    
    // 🌱 TÉRMINOS JARDÍN/EXTERIOR GENÉRICOS
    'garden', 'gardening', 'outdoor', 'landscape', 'plant', 'plants',
    'growing', 'cultivation', 'maintenance', 'seasonal',
    
    // 🎁 TÉRMINOS REGALO/OCASIÓN GENÉRICOS
    'gift', 'gifts', 'present', 'presents', 'celebration', 'occasion',
    'holiday', 'seasonal', 'special', 'commemorative',
    
    // 📱 TÉRMINOS COMUNICACIÓN GENÉRICOS
    'communication', 'connection', 'contact', 'message', 'signal',
    'transmission', 'reception', 'interface', 'display',
    
    // ⚡ TÉRMINOS ENERGÍA/PODER GENÉRICOS
    'power', 'energy', 'electric', 'electrical', 'battery', 'charging',
    'voltage', 'current', 'efficiency', 'consumption',
    
    // 🔒 TÉRMINOS SEGURIDAD GENÉRICOS
    'security', 'safety', 'protection', 'secure', 'safe', 'guard',
    'surveillance', 'monitoring', 'alarm', 'detection',
    
    // 🌍 TÉRMINOS AMBIENTALES GENÉRICOS
    'environmental', 'eco', 'green', 'sustainable', 'recycled',
    'biodegradable', 'renewable', 'conservation', 'pollution'
];

    // ✅ NUEVO: Diccionarios multilingües para servicio al cliente
    const multilingualToEnglish = {
        // ========== ESPAÑOL → INGLÉS ==========
        // 📞 Contacto y Soporte
        'contacto': 'contact',
        'ayuda': 'help',
        'soporte': 'support',
        'asistencia': 'assistance',
        'servicio': 'service',
        'servicio al cliente': 'customer service',
        'atención al cliente': 'customer service',
        
        // 📦 Envíos y Devoluciones
        'envío': 'shipping',
        'envíos': 'shipping',
        'entrega': 'delivery',
        'devolución': 'returns',
        'devoluciones': 'returns',
        'reembolso': 'refund',
        'cambio': 'exchange',
        'política de devoluciones': 'return policy',
        
        // 💳 Pagos y Garantías
        'pago': 'payment',
        'pagos': 'payment',
        'garantía': 'warranty',
        'garantías': 'warranty',
        'política': 'policy',
        'políticas': 'policy',
        'términos': 'terms',
        'métodos de pago': 'payment methods',
        
        // ❓ Información y Guías
        'preguntas frecuentes': 'faq',
        'guía': 'guide',
        'guías': 'guide',
        'instrucciones': 'instructions',
        'manual': 'manual',
        'tutorial': 'tutorial',
        'cómo': 'how to',
        
        // 🏪 Información de Tienda
        'sobre nosotros': 'about us',
        'acerca de': 'about us',
        'horarios': 'store hours',
        'ubicación': 'location',
        'privacidad': 'privacy policy',
        
        // ========== FRANCÉS → INGLÉS ==========
        // 📞 Contacto y Soporte
        'contact': 'contact',
        'aide': 'help',
        'support': 'support',
        'assistance': 'assistance',
        'service': 'service',
        'service client': 'customer service',
        'service clientèle': 'customer service',
        
        // 📦 Envíos y Devoluciones
        'livraison': 'shipping',
        'expédition': 'shipping',
        'retour': 'returns',
        'retours': 'returns',
        'remboursement': 'refund',
        'échange': 'exchange',
        'politique de retour': 'return policy',
        
        // 💳 Pagos y Garantías
        'paiement': 'payment',
        'paiements': 'payment',
        'garantie': 'warranty',
        'garanties': 'warranty',
        'politique': 'policy',
        'politiques': 'policy',
        'termes': 'terms',
        'méthodes de paiement': 'payment methods',
        
        // ❓ Información y Guías
        'faq': 'faq',
        'questions fréquentes': 'faq',
        'guide': 'guide',
        'guides': 'guide',
        'instructions': 'instructions',
        'manuel': 'manual',
        'tutoriel': 'tutorial',
        'comment': 'how to',
        
        // 🏪 Información de Tienda
        'à propos': 'about us',
        'qui sommes nous': 'about us',
        'horaires': 'store hours',
        'localisation': 'location',
        'confidentialité': 'privacy policy',
        
        // ========== PORTUGUÉS → INGLÉS ==========
        // 📞 Contacto y Soporte
        'contato': 'contact',
        'ajuda': 'help',
        'suporte': 'support',
        'assistência': 'assistance',
        'serviço': 'service',
        'atendimento ao cliente': 'customer service',
        'serviço ao cliente': 'customer service',
        
        // 📦 Envíos y Devoluciones
        'entrega': 'shipping',
        'envio': 'shipping',
        'devolução': 'returns',
        'devoluções': 'returns',
        'reembolso': 'refund',
        'troca': 'exchange',
        'política de devolução': 'return policy',
        
        // 💳 Pagos y Garantías
        'pagamento': 'payment',
        'pagamentos': 'payment',
        'garantia': 'warranty',
        'garantias': 'warranty',
        'política': 'policy',
        'políticas': 'policy',
        'termos': 'terms',
        'métodos de pagamento': 'payment methods',
        
        // ❓ Información y Guías
        'perguntas frequentes': 'faq',
        'guia': 'guide',
        'guias': 'guide',
        'instruções': 'instructions',
        'manual': 'manual',
        'tutorial': 'tutorial',
        'como': 'how to',
        
        // 🏪 Información de Tienda
        'sobre nós': 'about us',
        'quem somos': 'about us',
        'horários': 'store hours',
        'localização': 'location',
        'privacidade': 'privacy policy',
        
        // ========== INGLÉS → INGLÉS (normalización) ==========
        'customer service': 'customer service',
        'return policy': 'return policy',
        'payment methods': 'payment methods',
        'privacy policy': 'privacy policy',
        'terms of service': 'terms of service',
        'about us': 'about us',
        'store hours': 'store hours',
        'how to': 'how to'
    };

    let englishQuery = query.toLowerCase();
    
    // Reemplazar términos multilingües con equivalentes en inglés (filtrando genéricos)
    Object.keys(multilingualToEnglish).forEach(multilingual => {
        const english = multilingualToEnglish[multilingual];
        if (english && english.length > 0) { // Solo reemplazar si no está vacío
            englishQuery = englishQuery.replace(new RegExp(multilingual, 'gi'), english);
        }
    });

    // ✅ ACTUALIZADO: Palabras vacías expandidas para múltiples idiomas + servicio
    const stopWords = [
        // Español
        'busco', 'necesito', 'quiero', 'estoy buscando', 'para', 'con', 'sin', 'el', 'la', 'los', 'las', 'un', 'una',
        'dónde', 'donde', 'cómo', 'como', 'cuál', 'cual', 'qué', 'que',
        
        // Inglés
        'I need', 'I want', 'looking for', 'where is', 'how do', 'what is', 'the', 'a', 'an', 'for', 'with', 'without', 'and', 'or', 'but',
        
        // Francés
        'je cherche', 'j\'ai besoin', 'je veux', 'où est', 'comment', 'qu\'est-ce que', 'pour', 'avec', 'sans', 'le', 'la', 'les', 'un', 'une', 'et', 'ou',
        
        // Portugués
        'procuro', 'preciso', 'quero', 'estou procurando', 'onde está', 'como', 'o que é', 'para', 'com', 'sem', 'o', 'a', 'os', 'as', 'um', 'uma', 'e', 'ou',
        
        // Agregar palabras genéricas a las stop words
        ...genericWords
    ];

    // Limpiar y extraer palabras significativas
    stopWords.forEach(stopWord => {
        englishQuery = englishQuery.replace(new RegExp(stopWord, 'gi'), '');
    });

    // ✅ ACTUALIZADO: Generar keywords específicas de servicio al cliente
    const cleanWords = englishQuery
        .split(/[\s,.-]+/)
        .filter(word => word.length > 2 && !genericWords.includes(word.toLowerCase()));
    
    // Generar todas las keywords posibles en grupos de 1-3 palabras (evitando genéricas)
    const keywords = [];
    
    // Agregar keywords de 2-3 palabras
    for (let i = 0; i < cleanWords.length - 1; i++) {
        const twoWordKeyword = `${cleanWords[i]} ${cleanWords[i + 1]}`;
        if (!this.isGenericKeyword(twoWordKeyword, genericWords)) {
            keywords.push(twoWordKeyword);
        }
        
        // Keyword de 3 palabras si hay suficientes
        if (i < cleanWords.length - 2) {
            const threeWordKeyword = `${cleanWords[i]} ${cleanWords[i + 1]} ${cleanWords[i + 2]}`;
            if (!this.isGenericKeyword(threeWordKeyword, genericWords)) {
                keywords.push(threeWordKeyword);
            }
        }
    }
    
    // Agregar keywords de 1 palabra (solo si son específicas)
    cleanWords.forEach(word => {
        if (!genericWords.includes(word.toLowerCase()) &&
            !keywords.some(keyword => keyword.includes(word))) {
            keywords.push(word);
        }
    });
    


// ✅ ACTUALIZADO: Si no hay suficientes keywords específicas, usar términos de servicio al cliente
if (keywords.length < 3) {
    customerServiceTerms.forEach(term => {
        if (keywords.length < 5 && !keywords.includes(term)) {
            keywords.push(term);
        }
    });
}

    const result = keywords.join(', ');
    console.log(`🔧 Customer service fallback extraction (${detectedLang}): "${query}" → "${result}" (${keywords.length} specific keywords)`);
    
    return result;
}

       displayConversationalResponse(aiResponse, suggestedKeywords, detectedLang) {
    // Mantener compatibilidad con sistema original
    this.conversationHistory.push(
        { role: 'assistant', content: aiResponse }
    );

    if (this.conversationHistory.length > 10) {
        this.conversationHistory = this.conversationHistory.slice(-8);
    }

    const keywordsList = suggestedKeywords ? 
        suggestedKeywords.split(',').map(k => k.trim()).filter(k => k.length > 2) : 
        [];

    // Interfaz SIEMPRE EN INGLÉS - Simplificado
    const texts = {
        assistant: 'AI Assistant',
        searchTerms: '🔍 English search terms:',
        optimized: '🌐 Optimized English keywords for best results',
        searchAll: '🚀 Search all',
        alternatives: '🔄 Suggest alternatives',
        tellMore: '💡 Tell me more specifically',
        describeMore: 'Describe better what you are looking for so I can help with specific terms'
    };

    let responseHTML = `
        <div class="ai-success">
            <strong>${texts.assistant}</strong>
        </div>
        <div style="margin: 10px 0;">
            ${this.formatAIResponse(aiResponse)}
        </div>
    `;

    if (keywordsList.length > 0) {
        responseHTML += `
            <div style="margin-top: 15px; padding: 12px; background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%); border-radius: 10px; border-left: 4px solid #667eea;">
                <div style="margin-bottom: 12px;">
                    <strong>${texts.searchTerms}</strong>
                    <div style="font-size: 11px; color: #6c757d; margin-top: 4px;">${texts.optimized}</div>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
        `;
        
        keywordsList.forEach((keyword, index) => {
            responseHTML += `
                <button onclick="AIProductChatInstance.searchSpecificTerm('${keyword.replace(/'/g, "\\'")}', ${index})" 
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 8px 12px; border-radius: 15px; cursor: pointer; font-size: 11px; font-weight: 600; transition: all 0.2s;"
                        onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(102,126,234,0.3)'"
                        onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                    🔍 ${keyword}
                </button>
            `;
        });
        
        responseHTML += `
                </div>
                <div style="margin-top: 12px; display: flex; gap: 8px;">
                    <button onclick="AIProductChatInstance.searchAllTerms()" 
                            style="background: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 15px; cursor: pointer; font-size: 11px; font-weight: 600;">
                        ${texts.searchAll}
                    </button>
                </div>
            </div>
        `;
    } else {
        responseHTML += `
            <div style="margin-top: 15px; padding: 12px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                <strong>${texts.tellMore}</strong>
                <div style="margin-top: 8px; font-size: 12px; color: #6c757d;">
                    ${texts.describeMore}
                </div>
            </div>
        `;
    }

    this.addAIMessage(responseHTML);
}

        // ============== SISTEMA DE PAGINACIÓN CON MULTILINGÜE ==============
        
        displaySearchResults(searchTerm, searchResults, detectedLang) {
            // Textos multilingües para resultados de búsqueda
            const searchTexts = {
                'es': {
                    searchLabel: '🔍 Búsqueda:',
                    noResults: '😔 Sin resultados',
                    noResultsDesc: `No encontré productos para "${searchTerm}".`
                },
                'en': {
                    searchLabel: '🔍 Search:',
                    noResults: '😔 No results',
                    noResultsDesc: `No products found for "${searchTerm}".`
                },
                'fr': {
                    searchLabel: '🔍 Recherche :',
                    noResults: '😔 Aucun résultat',
                    noResultsDesc: `Aucun produit trouvé pour "${searchTerm}".`
                },
                'pt': {
                    searchLabel: '🔍 Busca:',
                    noResults: '😔 Sem resultados',
                    noResultsDesc: `Nenhum produto encontrado para "${searchTerm}".`
                }
            };

            const texts = searchTexts[detectedLang] || searchTexts['en'];

            let responseHTML = `
                <div style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); padding: 10px; border-radius: 8px; margin-bottom: 10px;">
                    <strong>${texts.searchLabel}</strong> "${searchTerm}"
                </div>
            `;

            if (!searchResults.response || searchResults.response.length === 0) {
                responseHTML += `
                    <div class="ai-error">
                        <strong>${texts.noResults}</strong><br>
                        ${texts.noResultsDesc}
                    </div>
                `;
            } else {
                // GUARDAR RESULTADOS COMPLETOS EN CACHE
                this.searchResultsCache.set(searchTerm, searchResults.response);
                this.currentDisplayedResults.set(searchTerm, 0);
                
                responseHTML += this.formatProductResultsWithPagination(searchResults, searchTerm, 0, detectedLang);
            }

            this.addAIMessage(responseHTML);
        }

    formatProductResultsWithPagination(searchResults, searchTerm, startIndex = 0, detectedLang = 'en') {
    const allResults = this.searchResultsCache.get(searchTerm) || searchResults.response;
    const totalResults = allResults.length;
    const resultsToShow = allResults.slice(startIndex, startIndex + this.resultsPerPage);
    const hasMoreResults = startIndex + this.resultsPerPage < totalResults;
    const currentPage = Math.floor(startIndex / this.resultsPerPage) + 1;
    const totalPages = Math.ceil(totalResults / this.resultsPerPage);

    // ✅ CORREGIDO: Textos multilingües para paginación
    const paginationTexts = {
        'es': {
            resultsFound: 'resultados encontrados',    // ✅ CAMBIADO: productos → resultados
            page: 'Página',
            of: 'de',
            previous: 'Anterior',
            viewMore: 'Ver más',
            showing: 'Mostrando',
            viewAll: 'Ver todos'
        },
        'en': {
            resultsFound: 'results found',             // ✅ CAMBIADO: products → results
            page: 'Page',
            of: 'of',
            previous: 'Previous',
            viewMore: 'View more',
            showing: 'Showing',
            viewAll: 'View all'
        },
        'fr': {
            resultsFound: 'résultats trouvés',         // ✅ CAMBIADO: produits → résultats
            page: 'Page',
            of: 'de',
            previous: 'Précédent',
            viewMore: 'Voir plus',
            showing: 'Affichage',
            viewAll: 'Voir tout'
        },
        'pt': {
            resultsFound: 'resultados encontrados',    // ✅ CAMBIADO: produtos → resultados
            page: 'Página',
            of: 'de',
            previous: 'Anterior',
            viewMore: 'Ver mais',
            showing: 'Mostrando',
            viewAll: 'Ver todos'
        }
    };

    const texts = paginationTexts[detectedLang] || paginationTexts['en'];

    // Agrupar los resultados a mostrar
    const groupedResults = {};
    resultsToShow.forEach(item => {
        const category = item.category || 'products';
        if (!groupedResults[category]) {
            groupedResults[category] = [];
        }
        groupedResults[category].push(item);
    });

    let html = `
        <div style="margin-top: 15px;">
            <strong>🛍️ ${totalResults} ${texts.resultsFound}</strong>
            <small style="color: #6c757d; margin-left: 8px;">
                (${texts.page} ${currentPage} ${texts.of} ${totalPages})
            </small>
        </div>
        <div class="ai-results-container" id="results-${searchTerm.replace(/[^a-zA-Z0-9]/g, '')}-${startIndex}">
    `;

    Object.keys(groupedResults).forEach(category => {
        const items = groupedResults[category];
        html += `
            <div class="ai-result-section">
                <div class="ai-result-section-title">
                    ${this.getCategoryIcon(category)} ${this.getCategoryName(category, detectedLang)} (${items.length})
                </div>
        `;
        
        items.forEach(item => {
            html += this.createProductHTML(item);
        });
        
        html += '</div>';
    });

    html += '</div>';

    // AGREGAR BOTONES DE PAGINACIÓN MULTILINGÜES
    if (hasMoreResults || startIndex > 0) {
        const remainingResults = totalResults - (startIndex + this.resultsPerPage);
        const nextBatchSize = Math.min(this.resultsPerPage, remainingResults);
        const showingText = `${texts.showing} ${startIndex + 1}-${Math.min(startIndex + this.resultsPerPage, totalResults)} ${texts.of} ${totalResults}`;

        html += `
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; border: 1px solid #e9ecef;">
                <div style="text-align: center; margin-bottom: 12px; font-size: 12px; color: #6c757d;">
                    ${showingText}
                </div>
                <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
        `;

        // Botón Anterior
        if (startIndex > 0) {
            html += `
                <button onclick="AIProductChatInstance.showPreviousResults('${searchTerm}', ${startIndex})" 
                        style="background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.2s;"
                        onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'"
                        onmouseout="this.style.background='#6c757d'; this.style.transform='none'">
                    ⬅️ ${texts.previous}
                </button>
            `;
        }

        // Botón Ver Más
        if (hasMoreResults) {
            html += `
                <button onclick="AIProductChatInstance.showMoreResults('${searchTerm}', ${startIndex})" 
                        style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(40, 167, 69, 0.4)'"
                        onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 8px rgba(40, 167, 69, 0.3)'">
                    ➡️ ${texts.viewMore} (${nextBatchSize})
                </button>
            `;
        }

        // Botón "Ver todos"
        if (totalResults > 9) {
            html += `
                <button onclick="AIProductChatInstance.showAllResults('${searchTerm}')" 
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.2s;"
                        onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(102, 126, 234, 0.3)'"
                        onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                    📋 ${texts.viewAll} (${totalResults})
                </button>
            `;
        }

        html += `
                </div>
            </div>
        `;
    }

    return html;
}

        // FUNCIONES DE PAGINACIÓN ACTUALIZADAS CON MULTILINGÜE
        showMoreResults(searchTerm, currentStartIndex) {
            const nextStartIndex = currentStartIndex + this.resultsPerPage;
            const allResults = this.searchResultsCache.get(searchTerm);
            
            if (!allResults) {
                console.error('No cached results found for:', searchTerm);
                return;
            }

            const detectedLang = this.getLastUserLanguage();
            const pageTexts = {
                'es': 'Página',
                'en': 'Page',
                'fr': 'Page',
                'pt': 'Página'
            };

            console.log(`📄 Showing more results for "${searchTerm}" - page ${Math.floor(nextStartIndex / this.resultsPerPage) + 1}`);

            this.currentDisplayedResults.set(searchTerm, nextStartIndex);

            const paginatedResults = {
                response: allResults,
                metadata: { pagination: true }
            };

            const nextPageHTML = this.formatProductResultsWithPagination(paginatedResults, searchTerm, nextStartIndex, detectedLang);

            this.addAIMessage(`
                <div style="background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%); padding: 10px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid #2196f3;">
                    <strong>📄 ${pageTexts[detectedLang] || pageTexts['en']} ${Math.floor(nextStartIndex / this.resultsPerPage) + 1} - "${searchTerm}"</strong>
                </div>
                ${nextPageHTML}
            `);
        }

        showPreviousResults(searchTerm, currentStartIndex) {
            const prevStartIndex = Math.max(0, currentStartIndex - this.resultsPerPage);
            const allResults = this.searchResultsCache.get(searchTerm);
            
            if (!allResults) {
                console.error('No cached results found for:', searchTerm);
                return;
            }

            const detectedLang = this.getLastUserLanguage();
            const pageTexts = {
                'es': 'Página',
                'en': 'Page',
                'fr': 'Page',
                'pt': 'Página'
            };

            console.log(`📄 Showing previous results for "${searchTerm}" - page ${Math.floor(prevStartIndex / this.resultsPerPage) + 1}`);

            this.currentDisplayedResults.set(searchTerm, prevStartIndex);

            const paginatedResults = {
                response: allResults,
                metadata: { pagination: true }
            };

            const prevPageHTML = this.formatProductResultsWithPagination(paginatedResults, searchTerm, prevStartIndex, detectedLang);

            this.addAIMessage(`
                <div style="background: linear-gradient(135deg, #f3e5f5 0%, #f8f9fa 100%); padding: 10px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid #9c27b0;">
                    <strong>⬅️ ${pageTexts[detectedLang] || pageTexts['en']} ${Math.floor(prevStartIndex / this.resultsPerPage) + 1} - "${searchTerm}"</strong>
                </div>
                ${prevPageHTML}
            `);
        }

        showAllResults(searchTerm) {
            const allResults = this.searchResultsCache.get(searchTerm);
            
            if (!allResults) {
                console.error('No cached results found for:', searchTerm);
                return;
            }

            const detectedLang = this.getLastUserLanguage();
            const allTexts = {
                'es': {
                    title: 'TODOS los resultados para',
                    subtitle: 'productos en total',
                    showing: 'Mostrando los',
                    complete: 'productos completos para'
                },
                'en': {
                    title: 'ALL results for',
                    subtitle: 'products in total',
                    showing: 'Showing all',
                    complete: 'complete products for'
                },
                'fr': {
                    title: 'TOUS les résultats pour',
                    subtitle: 'produits au total',
                    showing: 'Affichage de tous les',
                    complete: 'produits complets pour'
                },
                'pt': {
                    title: 'TODOS os resultados para',
                    subtitle: 'produtos no total',
                    showing: 'Mostrando todos os',
                    complete: 'produtos completos para'
                }
            };

            const texts = allTexts[detectedLang] || allTexts['en'];

            console.log(`📋 Showing ALL results for "${searchTerm}" - ${allResults.length} total`);

            // Agrupar todos los resultados
            const groupedResults = {};
            allResults.forEach(item => {
                const category = item.category || 'products';
                if (!groupedResults[category]) {
                    groupedResults[category] = [];
                }
                groupedResults[category].push(item);
            });

            let html = `
                <div style="background: linear-gradient(135deg, #fff3e0 0%, #f8f9fa 100%); padding: 12px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #ff9800;">
                    <strong>📋 ${texts.title} "${searchTerm}"</strong><br>
                    <small>${allResults.length} ${texts.subtitle}</small>
                </div>
                <div class="ai-results-container">
            `;

            Object.keys(groupedResults).forEach(category => {
                const items = groupedResults[category];
                html += `
                    <div class="ai-result-section">
                        <div class="ai-result-section-title">
                            ${this.getCategoryIcon(category)} ${this.getCategoryName(category, detectedLang)} (${items.length})
                        </div>
                `;
                
                items.forEach(item => {
                    html += this.createProductHTML(item);
                });
                
                html += '</div>';
            });

            html += `
                </div>
                <div style="margin-top: 15px; padding: 10px; background: #e8f5e8; border-radius: 8px; text-align: center; font-size: 12px; color: #2e7d32;">
                    ✅ ${texts.showing} ${allResults.length} ${texts.complete} "${searchTerm}"
                </div>
            `;

            this.addAIMessage(html);
        }
        
        // ✅ NUEVA FUNCIÓN: showMoreCategoryResults()
// Ubicación: Agregar en la clase AIProductChat después de showAllResults() línea ~1400

showMoreCategoryResults(category, currentShown) {
    const categoryItems = this.searchResultsCache.get(`category_${category}`);
    
    if (!categoryItems || !Array.isArray(categoryItems) || categoryItems.length === 0) {
        console.error('❌ No cached category results found for:', category);
        
        const detectedLang = this.getLastUserLanguage();
        const errorTexts = {
            'es': 'Error cargando más resultados de esta categoría',
            'en': 'Error loading more results from this category',
            'fr': 'Erreur lors du chargement de plus de résultats de cette catégorie',
            'pt': 'Erro ao carregar mais resultados desta categoria'
        };
        
        this.addAIMessage(`
            <div class="ai-error">
                <strong>⚠️ ${errorTexts[detectedLang] || errorTexts['en']}</strong>
            </div>
        `);
        return;
    }

    const detectedLang = this.getLastUserLanguage();
    
    // 🔢 CALCULAR PAGINACIÓN POR CATEGORÍA
    const nextShown = currentShown + 3;
    const hasMoreInCategory = nextShown < categoryItems.length;
    const itemsToShow = categoryItems.slice(0, nextShown);
    const remainingCount = categoryItems.length - nextShown;

    console.log(`📄 Showing more ${category} results: ${nextShown}/${categoryItems.length} total`);

    // 🌐 TEXTOS MULTILINGÜES
    const categoryTexts = {
        'es': {
            showingMore: 'Mostrando más',
            viewMore: 'Ver más',
            showingAll: 'Mostrando todos los'
        },
        'en': {
            showingMore: 'Showing more',
            viewMore: 'View more', 
            showingAll: 'Showing all'
        },
        'fr': {
            showingMore: 'Affichage de plus de',
            viewMore: 'Voir plus',
            showingAll: 'Affichage de tous les'
        },
        'pt': {
            showingMore: 'Mostrando mais',
            viewMore: 'Ver mais',
            showingAll: 'Mostrando todos os'
        }
    };

    const texts = categoryTexts[detectedLang] || categoryTexts['en'];

    // 🎨 GENERAR HTML PARA LA CATEGORÍA EXPANDIDA
    let categoryHTML = `
        <div style="background: linear-gradient(135deg, #e8f5e8 0%, #f8f9fa 100%); padding: 10px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid #28a745;">
            <strong>📂 ${texts.showingMore} ${this.getCategoryName(category, detectedLang)}</strong>
            <small style="margin-left: 8px; color: #6c757d;">
                (${itemsToShow.length}/${categoryItems.length})
            </small>
        </div>
        <div class="ai-results-container">
            <div class="ai-result-section">
                <div class="ai-result-section-title">
                    ${this.getCategoryIcon(category)} ${this.getCategoryName(category, detectedLang)} (${itemsToShow.length})
                </div>
    `;

    // 📦 GENERAR HTML PARA TODOS LOS ITEMS A MOSTRAR
    itemsToShow.forEach(({ item, matchingKeywords }) => {
        categoryHTML += this.createProductHTMLWithOrigin(item, matchingKeywords, detectedLang);
    });

    categoryHTML += '</div>';

    // 🎛️ BOTÓN PARA CONTINUAR SI HAY MÁS RESULTADOS
    if (hasMoreInCategory) {
        categoryHTML += `
            <div style="text-align: center; margin-top: 15px;">
                <button onclick="AIProductChatInstance.showMoreCategoryResults('${category}', ${nextShown})" 
                        style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.2s;"
                        onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(40, 167, 69, 0.3)'"
                        onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                    ➡️ ${texts.viewMore} ${this.getCategoryName(category, detectedLang)} (+${remainingCount})
                </button>
            </div>
        `;
    } else {
        // 🎉 MENSAJE DE COMPLETADO
        categoryHTML += `
            <div style="margin-top: 15px; padding: 10px; background: #e8f5e8; border-radius: 8px; text-align: center; font-size: 12px; color: #2e7d32;">
                ✅ ${texts.showingAll} ${this.getCategoryName(category, detectedLang).toLowerCase()} (${categoryItems.length})
            </div>
        `;
    }

    categoryHTML += '</div>';

    // 💬 AGREGAR MENSAJE AL CHAT
    this.addAIMessage(categoryHTML);

    // 📊 LOGGING PARA DEBUG
    if (this.config.debug) {
        console.log(`📊 Category pagination: ${category} - showing ${nextShown}/${categoryItems.length}, hasMore: ${hasMoreInCategory}`);
    }
}

   // ✅ FUNCIÓN 1: displayMultipleTermResults() - CON AGRUPACIÓN POR CATEGORÍA
displayMultipleTermResults(successfulSearches, totalCount) {
    const detectedLang = this.getLastUserLanguage();
    
    const multipleTexts = {
        'es': {
            completed: 'Búsqueda múltiple completada',
            found: 'Encontré',
            in: 'en',
            categories: 'categorías',
            results: 'resultados',
            unique: 'únicos',
            foundBy: 'Encontrado por',
            helpful: '¿Te sirven estos resultados?',
            refine: 'Refinar búsqueda',
            specific: 'Ser más específico'
        },
        'en': {
            completed: 'Multiple search completed',
            found: 'Found',
            in: 'in',
            categories: 'categories',
            results: 'results',
            unique: 'unique',
            foundBy: 'Found by',
            helpful: 'Are these results helpful?',
            refine: 'Refine search',
            specific: 'Be more specific'
        },
        'fr': {
            completed: 'Recherche multiple terminée',
            found: 'Trouvé',
            in: 'dans',
            categories: 'catégories',
            results: 'résultats',
            unique: 'uniques',
            foundBy: 'Trouvé par',
            helpful: 'Ces résultats vous aident-ils ?',
            refine: 'Affiner la recherche',
            specific: 'Être plus spécifique'
        },
        'pt': {
            completed: 'Busca múltipla concluída',
            found: 'Encontrei',
            in: 'em',
            categories: 'categorias',
            results: 'resultados',
            unique: 'únicos',
            foundBy: 'Encontrado por',
            helpful: 'Estes resultados ajudam?',
            refine: 'Refinar busca',
            specific: 'Ser mais específico'
        }
    };
    
    const texts = multipleTexts[detectedLang] || multipleTexts['en'];
    
    // ✅ Deduplicar resultados
    const { uniqueResults, totalUniqueCount } = this.deduplicateSearchResults(successfulSearches);
    
    let responseHTML = `
        <div style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); padding: 12px; border-radius: 8px; margin-bottom: 15px;">
            <strong>🚀 ${texts.completed}</strong><br>
            <small>${texts.found} ${totalUniqueCount} ${texts.results} ${texts.unique}</small>
        </div>
    `;

    responseHTML += '<div class="ai-results-container">';
    
    // ✅ NUEVO: Agrupar resultados únicos POR CATEGORÍA
    const groupedByCategory = {};
    uniqueResults.forEach(resultData => {
        const { item, matchingKeywords } = resultData;
        const category = item.category || 'products';
        
        if (!groupedByCategory[category]) {
            groupedByCategory[category] = [];
        }
        
        groupedByCategory[category].push({
            item: item,
            matchingKeywords: matchingKeywords
        });
    });
    
    // ✅ NUEVO: Mostrar una sección por categoría
    Object.keys(groupedByCategory).forEach((category, categoryIndex) => {
        const categoryItems = groupedByCategory[category];
        
        responseHTML += `
            <div class="ai-result-section">
                <div class="ai-result-section-title">
                    ${this.getCategoryIcon(category)} ${this.getCategoryName(category, detectedLang)}
                </div>
        `;
        
        // Mostrar todos los items de esta categoría
        categoryItems.forEach(({ item, matchingKeywords }) => {
            responseHTML += this.createProductHTMLWithOrigin(item, matchingKeywords, detectedLang);
        });
        
        responseHTML += '</div>';
        
        // Separador entre categorías (solo si no es la última)
        if (categoryIndex < Object.keys(groupedByCategory).length - 1) {
            responseHTML += '<div style="border-bottom: 1px solid #e9ecef; margin: 15px 0;"></div>';
        }
    });

    responseHTML += '</div>';

    this.addAIMessage(responseHTML);
    
    console.log(`✅ Multiple search with proper category grouping: ${totalCount} total → ${totalUniqueCount} unique results`);
}

// ✅ FUNCIÓN 2: createProductHTMLWithOrigin() - CON SKU + BLURB MEJORADO
createProductHTMLWithOrigin(item, matchingKeywords, detectedLang) {
    const imageHTML = item.image ? 
        `<img src="${item.image}" alt="${item.title || item.text}" />` : 
        `<div style="width:48px;height:48px;background:linear-gradient(135deg,#f0f0f0,#e0e0e0);border-radius:8px;display:flex;align-items:center;justify-content:center;margin-right:12px;flex-shrink:0;"><i class="fa fa-cube" style="color:#ccc;"></i></div>`;
    
    // Textos para "Encontrado por"
    const foundByTexts = {
        'es': 'Encontrado por',
        'en': 'Found by',
        'fr': 'Trouvé par',
        'pt': 'Encontrado por'
    };
    
    const foundByText = foundByTexts[detectedLang] || foundByTexts['en'];
    
    // Crear lista de keywords que encontraron este resultado
    const keywordBadges = matchingKeywords.map(keyword => 
        `<span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px; margin-right: 4px; display: inline-block;">${keyword}</span>`
    ).join('');
    
    // ✅ FUNCIÓN HELPER: Limpiar HTML tags
    const stripHTML = (str) => {
        if (!str) return '';
        return str.replace(/<[^>]*>/g, '').trim();
    };
    
    // Determinar el título
    let displayTitle = stripHTML(item.title || item.name || item.text || 'Sin título');
    let displayDescription = '';
    
    // ✅ SEPARAR LÓGICA: Solo productos vs. otras categorías
    if (item.category === 'products' || item.product_id) {
        // ========== SOLO PARA PRODUCTOS: Usar SKU + Blurb ==========
        const parts = [];
        
        // Agregar Model/SKU
        if (item.model && item.model.trim()) {
            parts.push(item.model.trim());
        }
        
        // Agregar Blurb
        if (item.blurb && item.blurb.trim()) {
            parts.push(stripHTML(item.blurb));
        }
        
        // Fallback para productos: description o text
        if (parts.length === 0) {
            if (item.description && item.description.trim()) {
                const cleanDesc = stripHTML(item.description);
                parts.push(cleanDesc.length > 60 ? cleanDesc.substring(0, 60) + '...' : cleanDesc);
            } else if (item.text && item.text.trim()) {
                const cleanText = stripHTML(item.text);
                parts.push(cleanText.length > 60 ? cleanText.substring(0, 60) + '...' : cleanText);
            }
        }
        
        displayDescription = parts.length > 0 ? parts.join(' • ') : displayTitle;
        
    } else {
        // ========== PARA PÁGINAS/OTRAS: Solo contenido limpio ==========
        // Para páginas, usar solo el título como descripción (más limpio)
        displayDescription = displayTitle;
        
        // O si quieres mostrar algo de contenido (opcional):
        /*
        if (item.text && stripHTML(item.text) !== displayTitle) {
            const cleanText = stripHTML(item.text);
            displayDescription = cleanText.length > 80 ? 
                cleanText.substring(0, 80) + '...' : 
                cleanText;
        } else {
            displayDescription = displayTitle;
        }
        */
    }
    
    return `
        <div class="ai-result-item" onclick="AIProductChatInstance.openResult('${item.page || '#'}')">
            ${imageHTML}
            <div class="ai-result-content">
                <div class="ai-result-category">${item.category_name || ''}</div>
                <div class="ai-result-title">${displayTitle}</div>
                <div class="ai-result-text">${displayDescription}</div>
                <div style="margin-top: 6px; font-size: 10px; color: #6c757d;">
                    <strong>${foundByText}:</strong> ${keywordBadges}
                </div>
            </div>
        </div>
    `;
}

// ✅ NUEVA FUNCIÓN: displayMultipleTermResults con PAGINACIÓN por categoría
displayMultipleTermResults(successfulSearches, totalCount) {
    const detectedLang = this.getLastUserLanguage();
    
    const multipleTexts = {
        'es': {
            completed: 'Búsqueda múltiple completada',
            found: 'Encontré',
            results: 'resultados',
            unique: 'únicos',
            viewMore: 'Ver más'
        },
        'en': {
            completed: 'Multiple search completed',
            found: 'Found',
            results: 'results',
            unique: 'unique',
            viewMore: 'View more'
        },
        'fr': {
            completed: 'Recherche multiple terminée',
            found: 'Trouvé',
            results: 'résultats',
            unique: 'uniques',
            viewMore: 'Voir plus'
        },
        'pt': {
            completed: 'Busca múltipla concluída',
            found: 'Encontrei',
            results: 'resultados',
            unique: 'únicos',
            viewMore: 'Ver mais'
        }
    };
    
    const texts = multipleTexts[detectedLang] || multipleTexts['en'];
    
    // Deduplicar resultados
    const { uniqueResults, totalUniqueCount } = this.deduplicateSearchResults(successfulSearches);
    
    let responseHTML = `
        <div style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); padding: 12px; border-radius: 8px; margin-bottom: 15px;">
            <strong>🚀 ${texts.completed}</strong><br>
            <small>${texts.found} ${totalUniqueCount} ${texts.results} ${texts.unique}</small>
        </div>
    `;

    responseHTML += '<div class="ai-results-container">';
    
    // Agrupar por categoría
    const groupedByCategory = {};
    uniqueResults.forEach(resultData => {
        const { item, matchingKeywords } = resultData;
        const category = item.category || 'products';
        
        if (!groupedByCategory[category]) {
            groupedByCategory[category] = [];
        }
        
        groupedByCategory[category].push({
            item: item,
            matchingKeywords: matchingKeywords
        });
    });
    
    // ✅ NUEVO: Mostrar con PAGINACIÓN por categoría (máximo 3 por página)
    Object.keys(groupedByCategory).forEach((category, categoryIndex) => {
        const categoryItems = groupedByCategory[category];
        const itemsToShow = categoryItems.slice(0, 3); // ✅ Máximo 3 por página
        const hasMoreInCategory = categoryItems.length > 3;
        
        responseHTML += `
            <div class="ai-result-section">
                <div class="ai-result-section-title">
                    ${this.getCategoryIcon(category)} ${this.getCategoryName(category, detectedLang)} (${itemsToShow.length}${hasMoreInCategory ? `/${categoryItems.length}` : ''})
                </div>
        `;
        
        // Mostrar primeros 3 items de esta categoría
        itemsToShow.forEach(({ item, matchingKeywords }) => {
            responseHTML += this.createProductHTMLWithOrigin(item, matchingKeywords, detectedLang);
        });
        
        // ✅ NUEVO: Botón "Ver más" por categoría si hay más resultados
        if (hasMoreInCategory) {
            const remainingCount = categoryItems.length - 3;
            responseHTML += `
                <div style="text-align: center; margin-top: 10px;">
                    <button onclick="AIProductChatInstance.showMoreCategoryResults('${category}', 3)" 
                            data-category="${category}"
                            data-total="${categoryItems.length}"
                            style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 6px 12px; border-radius: 15px; cursor: pointer; font-size: 11px; font-weight: 600; transition: all 0.2s;"
                            onmouseover="this.style.transform='translateY(-1px)'"
                            onmouseout="this.style.transform='none'">
                        ➡️ ${texts.viewMore} ${this.getCategoryName(category, detectedLang)} (+${remainingCount})
                    </button>
                </div>
            `;
            
            // Guardar en cache para paginación
            this.searchResultsCache.set(`category_${category}`, categoryItems);
        }
        
        responseHTML += '</div>';
        
        // Separador entre categorías
        if (categoryIndex < Object.keys(groupedByCategory).length - 1) {
            responseHTML += '<div style="border-bottom: 1px solid #e9ecef; margin: 15px 0;"></div>';
        }
    });

    responseHTML += '</div>';
    this.addAIMessage(responseHTML);
    
    console.log(`✅ Multiple search with category pagination: ${totalCount} total → ${totalUniqueCount} unique results`);
}

// ✅ NUEVA FUNCIÓN: Deduplicar resultados por ID único
deduplicateSearchResults(successfulSearches) {
    const uniqueMap = new Map();
    const keywordOrigins = new Map();
    let totalOriginalCount = 0;
    
    successfulSearches.forEach(search => {
        search.results.forEach(item => {
            totalOriginalCount++;
            
            // Crear clave única basada en content_id, product_id, o URL
            const uniqueKey = item.content_id || 
                             item.product_id || 
                             item.category_id || 
                             item.manufacturer_id || 
                             item.page || 
                             item.title;
            
            if (!uniqueMap.has(uniqueKey)) {
                // Primer resultado con esta clave
                uniqueMap.set(uniqueKey, {
                    item: item,
                    matchingKeywords: [search.keyword],
                    totalFound: search.totalFound || search.results.length,
                    displayedCount: 1
                });
            } else {
                // Resultado duplicado - solo agregar keyword origen
                const existing = uniqueMap.get(uniqueKey);
                if (!existing.matchingKeywords.includes(search.keyword)) {
                    existing.matchingKeywords.push(search.keyword);
                }
            }
        });
    });
    
    const uniqueResults = Array.from(uniqueMap.values());
    
    return {
        uniqueResults: uniqueResults,
        keywordOrigins: keywordOrigins,
        totalUniqueCount: uniqueResults.length,
        totalOriginalCount: totalOriginalCount
    };
}

// ✅ NUEVA FUNCIÓN: Crear HTML con información de origen
createProductHTMLWithOrigin(item, matchingKeywords, detectedLang) {
    const imageHTML = item.image ? 
        `<img src="${item.image}" alt="${item.title || item.text}" />` : 
        `<div style="width:48px;height:48px;background:linear-gradient(135deg,#f0f0f0,#e0e0e0);border-radius:8px;display:flex;align-items:center;justify-content:center;margin-right:12px;flex-shrink:0;"><i class="fa fa-cube" style="color:#ccc;"></i></div>`;
    
    // ✅ FUNCIÓN HELPER: Limpiar HTML tags
    const stripHTML = (str) => {
        if (!str) return '';
        return str.replace(/<[^>]*>/g, '').trim();
    };
    
    // Determinar el título
    let displayTitle = stripHTML(item.title || item.name || item.text || 'Sin título');
    let displayDescription = '';
    
    // ✅ SEPARAR LÓGICA: Solo productos vs. otras categorías
    if (item.category === 'products' || item.product_id) {
        // ========== SOLO PARA PRODUCTOS: Usar SKU + Blurb ==========
        const parts = [];
        
        // Agregar Model/SKU
        if (item.model && item.model.trim()) {
            parts.push(item.model.trim());
        }
        
        // Agregar Blurb
        if (item.blurb && item.blurb.trim()) {
            parts.push(stripHTML(item.blurb));
        }
        
        // ✅ MEJORADO: Fallback más inteligente para productos
        if (parts.length === 0) {
            // Si no hay model ni blurb, mostrar "Producto disponible" en lugar del título
            const fallbackTexts = {
                'es': 'Producto disponible',
                'en': 'Product available', 
                'fr': 'Produit disponible',
                'pt': 'Produto disponível'
            };
            parts.push(fallbackTexts[detectedLang] || fallbackTexts['en']);
        }
        
        displayDescription = parts.join(' • ');
        
    } else {
        // ========== PARA PÁGINAS/OTRAS: Solo mostrar el título ==========
        displayDescription = displayTitle;
    }
    
    // ✅ NUEVO: Solo mostrar "Found by" si hay MÚLTIPLES keywords diferentes
    let foundByHTML = '';
    if (matchingKeywords && matchingKeywords.length > 1) {
        // Textos para "Encontrado por"
        const foundByTexts = {
            'es': 'Encontrado por',
            'en': 'Found by',
            'fr': 'Trouvé par',
            'pt': 'Encontrado por'
        };
        
        const foundByText = foundByTexts[detectedLang] || foundByTexts['en'];
        
        // Crear lista de keywords que encontraron este resultado
        const keywordBadges = matchingKeywords.map(keyword => 
            `<span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px; margin-right: 4px; display: inline-block;">${keyword}</span>`
        ).join('');
        
        foundByHTML = `
            <div style="margin-top: 6px; font-size: 10px; color: #6c757d;">
                <strong>${foundByText}:</strong> ${keywordBadges}
            </div>
        `;
    }
    
    return `
        <div class="ai-result-item" onclick="AIProductChatInstance.openResult('${item.page || '#'}')">
            ${imageHTML}
            <div class="ai-result-content">
                <div class="ai-result-title">${displayTitle}</div>
                <div class="ai-result-text">${displayDescription}</div>
                ${foundByHTML}
            </div>
        </div>
    `;
}

        // Interactive methods ACTUALIZADOS CON MULTILINGÜE
        searchSpecificTerm(keyword, index) {
            const detectedLang = this.getLastUserLanguage();
            const searchTexts = {
                'es': `Buscar: "${keyword}"`,
                'en': `Search: "${keyword}"`,
                'fr': `Chercher : "${keyword}"`,
                'pt': `Buscar: "${keyword}"`
            };
            
            console.log('🎯 Searching specific term:', keyword);
            this.addUserMessage(searchTexts[detectedLang] || searchTexts['en']);
            this.pendingSearchKeywords = keyword;
            this.executeSearchFromPendingKeywords();
        }

        searchAllTerms() {
            const detectedLang = this.getLastUserLanguage();
            const searchAllTexts = {
                'es': "Buscar todos los términos",
                'en': "Search all terms",
                'fr': "Chercher tous les termes",
                'pt': "Buscar todos os termos"
            };
            
            if (this.pendingSearchKeywords) {
                this.addUserMessage(searchAllTexts[detectedLang] || searchAllTexts['en']);
                this.searchMultipleTermsSequentially();
            }
        }

       async searchMultipleTermsSequentially() {
    const keywords = this.pendingSearchKeywords.split(',').map(k => k.trim()).filter(k => k.length > 2);
    const detectedLang = this.getLastUserLanguage();
    
    const searchingTexts = {
        'es': '🔍 Buscando múltiples términos...',
        'en': '🔍 Searching multiple terms...',
        'fr': '🔍 Recherche de termes multiples...',
        'pt': '🔍 Procurando múltiplos termos...'
    };
    
    console.log('🚀 Starting multiple search for keywords:', keywords);
    this.updateStatus(searchingTexts[detectedLang] || searchingTexts['en'], true);
    
    let allResults = [];
    let successfulSearches = [];
    
    for (let i = 0; i < keywords.length && i < 4; i++) {
        const keyword = keywords[i];
        try {
            console.log(`🔍 Searching term ${i+1}/${Math.min(keywords.length, 4)}: "${keyword}"`);
            
            if (i > 0) {
                await new Promise(resolve => setTimeout(resolve, 500));
            }
            
            const results = await this.searchProducts(keyword);
            console.log(`📊 Results for "${keyword}":`, results.response?.length || 0, 'items');
            
            if (results.response && results.response.length > 0) {
                // ✅ MODIFICADO: Mantener TODOS los resultados para deduplicación posterior
                successfulSearches.push({
                    keyword: keyword,
                    results: results.response, // ✅ CAMBIO: Usar todos los resultados, no limitados
                    totalFound: results.response.length,
                    allResults: results.response
                });
                allResults = allResults.concat(results.response); // ✅ CAMBIO: Concatenar todos
                console.log(`✅ Added ${results.response.length} results for "${keyword}"`);
            } else {
                console.log(`❌ No results found for "${keyword}"`);
            }
        } catch (error) {
            console.error(`❌ Search failed for term "${keyword}":`, error);
        }
    }
    
    this.updateStatus('');
    
    console.log(`🎯 Multiple search completed: ${successfulSearches.length} successful searches, ${allResults.length} total results`);
    
    if (successfulSearches.length > 0) {
        // ✅ NUEVO: Calcular conteo real después de deduplicación
        const { totalUniqueCount } = this.deduplicateSearchResults(successfulSearches);
        console.log(`📊 Deduplication: ${allResults.length} total → ${totalUniqueCount} unique results`);
        
        this.displayMultipleTermResults(successfulSearches, allResults.length);
    } else {
        // ✅ CORREGIDO: Cambiar "productos" por "resultados"
        const noResultsTexts = {
            'es': `😔 No encontré resultados para ninguno de los términos: ${keywords.join(', ')}`,
            'en': `😔 No results found for any of the terms: ${keywords.join(', ')}`,
            'fr': `😔 Aucun résultat trouvé pour aucun des termes : ${keywords.join(', ')}`,
            'pt': `😔 Nenhum resultado encontrado para nenhum dos termos: ${keywords.join(', ')}`
        };
        
        this.addAIMessage(`<div class="ai-error">${noResultsTexts[detectedLang] || noResultsTexts['en']}</div>`);
    }
}

        async suggestAlternatives() {
            const detectedLang = this.getLastUserLanguage();
            const suggestTexts = {
                'es': "Sugerir alternativas",
                'en': "Suggest alternatives", 
                'fr': "Suggérer des alternatives",
                'pt': "Sugerir alternativas"
            };
            
            const thinkingTexts = {
                'es': '🤖 Pensando en alternativas...',
                'en': '🤖 Thinking of alternatives...',
                'fr': '🤖 Réflexion sur les alternatives...',
                'pt': '🤖 Pensando em alternativas...'
            };
            
            this.addUserMessage(suggestTexts[detectedLang] || suggestTexts['en']);
            this.updateStatus(thinkingTexts[detectedLang] || thinkingTexts['en'], true);
            
            try {
                const alternatives = await this.getAlternativeSuggestions(detectedLang);
                this.displayAlternatives(alternatives, detectedLang);
            } catch (error) {
                this.handleError('Error obteniendo alternativas');
            } finally {
                this.updateStatus('');
            }
        }

        async getAlternativeSuggestions(detectedLang) {
            const contextualPrompts = {
                'es': 'Basándote en la conversación anterior, sugiere términos de búsqueda alternativos que podrían encontrar productos similares o relacionados. Sé específico y práctico. Responde en español.',
                'en': 'Based on the previous conversation, suggest alternative search terms that could find similar or related products. Be specific and practical.',
                'fr': 'Basé sur la conversation précédente, suggérez des termes de recherche alternatifs qui pourraient trouver des produits similaires ou connexes. Soyez spécifique et pratique. Répondez en français.',
                'pt': 'Com base na conversa anterior, sugira termos de busca alternativos que possam encontrar produtos similares ou relacionados. Seja específico e prático. Responda em português.'
            };
            
            const contextualPrompt = this.buildContextualPrompt(
                contextualPrompts[detectedLang] || contextualPrompts['en']
            );
            
            const messages = [
                {
                    role: 'system',
                    content: contextualPrompt
                },
                {
                    role: 'user',
                    content: 'Sugiere términos de búsqueda alternativos para encontrar productos similares'
                }
            ];

            return await this.callGroqAPI(messages);
        }

        displayAlternatives(alternatives, detectedLang) {
            const alternativeTexts = {
                'es': {
                    title: 'Alternativas sugeridas',
                    question: '¿Alguna de estas te interesa?',
                    description: 'Escríbeme cuál te llama la atención o describe algo más específico'
                },
                'en': {
                    title: 'Suggested alternatives',
                    question: 'Any of these interest you?',
                    description: 'Tell me which one catches your attention or describe something more specific'
                },
                'fr': {
                    title: 'Alternatives suggérées',
                    question: 'L\'une de ces options vous intéresse-t-elle ?',
                    description: 'Dites-moi laquelle attire votre attention ou décrivez quelque chose de plus spécifique'
                },
                'pt': {
                    title: 'Alternativas sugeridas',
                    question: 'Alguma dessas te interessa?',
                    description: 'Me diga qual chama sua atenção ou descreva algo mais específico'
                }
            };

            const texts = alternativeTexts[detectedLang] || alternativeTexts['en'];

            const responseHTML = `
                <div class="ai-success">
                    <strong>🔄 ${texts.title}</strong>
                </div>
                <div style="margin: 10px 0;">
                    ${this.formatAIResponse(alternatives)}
                </div>
                <div style="margin-top: 15px; padding: 12px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #17a2b8;">
                    <strong>💡 ${texts.question}</strong>
                    <div style="margin-top: 8px; font-size: 12px; color: #6c757d;">
                        ${texts.description}
                    </div>
                </div>
            `;

            this.addAIMessage(responseHTML);
        }

        searchMoreSpecific() {
            const detectedLang = this.getLastUserLanguage();
            const specificTexts = {
                'es': "Buscar algo más específico",
                'en': "Search something more specific",
                'fr': "Chercher quelque chose de plus spécifique", 
                'pt': "Buscar algo mais específico"
            };
            
            const helpTexts = {
                'es': {
                    title: '¡Perfecto! Seamos más específicos',
                    options: [
                        'Marca específica que prefieres',
                        'Concentración o presentación', 
                        'Para qué lo vas a usar exactamente',
                        'Características especiales que necesitas'
                    ],
                    question: '¿Qué detalles específicos tienes en mente?'
                },
                'en': {
                    title: 'Perfect! Let\'s be more specific',
                    options: [
                        'Specific brand you prefer',
                        'Concentration or presentation',
                        'What you\'ll use it for exactly',
                        'Special characteristics you need'
                    ],
                    question: 'What specific details do you have in mind?'
                },
                'fr': {
                    title: 'Parfait ! Soyons plus spécifiques',
                    options: [
                        'Marque spécifique que vous préférez',
                        'Concentration ou présentation',
                        'À quoi vous allez l\'utiliser exactement',
                        'Caractéristiques spéciales dont vous avez besoin'
                    ],
                    question: 'Quels détails spécifiques avez-vous en tête ?'
                },
                'pt': {
                    title: 'Perfeito! Vamos ser mais específicos',
                    options: [
                        'Marca específica que você prefere',
                        'Concentração ou apresentação',
                        'Para que você vai usar exatamente',
                        'Características especiais que você precisa'
                    ],
                    question: 'Que detalhes específicos você tem em mente?'
                }
            };
            
            const texts = helpTexts[detectedLang] || helpTexts['en'];
            
            this.addUserMessage(specificTexts[detectedLang] || specificTexts['en']);
            
            const optionsList = texts.options.map(option => `<li>${option}</li>`).join('');
            
            this.addAIMessage(`
                <div style="background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%); padding: 12px; border-radius: 8px; border-left: 4px solid #2196f3;">
                    <strong>🎯 ${texts.title}</strong><br>
                    Puedes decirme:
                    <ul style="margin: 8px 0; padding-left: 20px;">
                        ${optionsList}
                    </ul>
                    ${texts.question}
                </div>
            `);
        }

        async processTraditionalSearch(query) {
            console.log('🔍 Processing traditional search:', query);
            
            const detectedLang = this.getLastUserLanguage();
            const searchingTexts = {
                'es': '🔍 Buscando productos...',
                'en': '🔍 Searching products...',
                'fr': '🔍 Recherche de produits...',
                'pt': '🔍 Procurando produtos...'
            };
            
            this.updateStatus(searchingTexts[detectedLang] || searchingTexts['en'], true);
            
            try {
                const results = await this.searchProducts(query);
                this.displayTraditionalResults(query, results, detectedLang);
            } catch (error) {
                throw error;
            }
        }

        displayTraditionalResults(query, results, detectedLang) {
            const searchTexts = {
                'es': 'Búsqueda:',
                'en': 'Search:',
                'fr': 'Recherche :',
                'pt': 'Busca:'
            };
            
            let responseHTML = `<p><strong>🔍 ${searchTexts[detectedLang] || searchTexts['en']}</strong> "${query}"</p>`;
            responseHTML += this.formatProductResults(results, query, detectedLang);
            this.addAIMessage(responseHTML);
        }

        formatProductResults(searchResults, searchTerm, detectedLang = 'en') {
            if (!searchResults.response || searchResults.response.length === 0) {
                const noResultsTexts = {
                    'es': `<strong>😔 Sin resultados</strong><br>No encontré productos para "${searchTerm}". Intenta con otros términos.`,
                    'en': `<strong>😔 No results</strong><br>No products found for "${searchTerm}". Try with other terms.`,
                    'fr': `<strong>😔 Aucun résultat</strong><br>Aucun produit trouvé pour "${searchTerm}". Essayez avec d'autres termes.`,
                    'pt': `<strong>😔 Sem resultados</strong><br>Nenhum produto encontrado para "${searchTerm}". Tente com outros termos.`
                };
                
                return `<div class="ai-error">${noResultsTexts[detectedLang] || noResultsTexts['en']}</div>`;
            }

            const groupedResults = {};
            searchResults.response.forEach(item => {
                const category = item.category || 'products';
                if (!groupedResults[category]) {
                    groupedResults[category] = [];
                }
                groupedResults[category].push(item);
            });

            const foundTexts = {
                'es': 'productos encontrados:',
                'en': 'products found:',
                'fr': 'produits trouvés :',
                'pt': 'produtos encontrados:'
            };

            let html = `
                <div style="margin-top: 15px;">
                    <strong>🛍️ ${searchResults.response.length} ${foundTexts[detectedLang] || foundTexts['en']}</strong>
                </div>
                <div class="ai-results-container">
            `;

            Object.keys(groupedResults).forEach(category => {
                const items = groupedResults[category];
                html += `
                    <div class="ai-result-section">
                        <div class="ai-result-section-title">
                            ${this.getCategoryIcon(category)} ${this.getCategoryName(category, detectedLang)} (${items.length})
                        </div>
                `;
                
                items.slice(0, 4).forEach(item => {
                    html += this.createProductHTML(item);
                });
                
                html += '</div>';
            });

            html += '</div>';
            return html;
        }

        getCategoryIcon(category) {
            const icons = {
                'products': '🛍️',
                'product_categories': '📂', 
                'manufacturers': '🏭',
                'contents': '📄',
                'reviews': '💬'
            };
            return icons[category] || '📦';
        }

        getCategoryName(category, detectedLang = 'en') {
            const names = {
                'products': {
                    'es': 'Productos',
                    'en': 'Products',
                    'fr': 'Produits',
                    'pt': 'Produtos'
                },
                'product_categories': {
                    'es': 'Categorías',
                    'en': 'Categories',
                    'fr': 'Catégories', 
                    'pt': 'Categorias'
                },
                'manufacturers': {
                    'es': 'Marcas',
                    'en': 'Brands',
                    'fr': 'Marques',
                    'pt': 'Marcas'
                },
                'contents': {
                    'es': 'Páginas',
                    'en': 'Pages',
                    'fr': 'Pages',
                    'pt': 'Páginas'
                },
                'reviews': {
                    'es': 'Reviews',
                    'en': 'Reviews',
                    'fr': 'Avis',
                    'pt': 'Avaliações'
                }
            };
            
            return names[category] ? (names[category][detectedLang] || names[category]['en']) : 'Otros';
        }

        createProductHTML(item) {
            const imageHTML = item.image ? 
                `<img src="${item.image}" alt="${item.title || item.text}" />` : 
                `<div style="width:48px;height:48px;background:linear-gradient(135deg,#f0f0f0,#e0e0e0);border-radius:8px;display:flex;align-items:center;justify-content:center;margin-right:12px;flex-shrink:0;"><i class="fa fa-cube" style="color:#ccc;"></i></div>`;
            
            return `
                <div class="ai-result-item" onclick="AIProductChatInstance.openResult('${item.page || '#'}')">
                    ${imageHTML}
                    <div class="ai-result-content">
                        <div class="ai-result-category">${item.category_name || ''}</div>
                        <div class="ai-result-title">${item.title || item.text}</div>
                        <div class="ai-result-text">${(item.text || '').substring(0, 100)}${item.text && item.text.length > 100 ? '...' : ''}</div>
                    </div>
                </div>
            `;
        }

        // Resto de métodos sin cambios (callGroqAPI, searchProducts, formatAIResponse, etc.)
        async callGroqAPI(messages) {
            if (!this.config.groqApiKey) {
                throw new Error('No AI API key configured');
            }

            const requestData = {
                model: this.config.groqModel,
                messages: messages,
                temperature: this.config.groqTemperature,
                max_tokens: this.config.groqMaxTokens,
                stream: false
            };

            const response = await fetch('https://api.groq.com/openai/v1/chat/completions', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.config.groqApiKey}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData),
                signal: AbortSignal.timeout(15000)
            });

            if (!response.ok) {
                throw new Error(`AI API error: ${response.status}`);
            }

            const data = await response.json();
            
            if (!data.choices || !data.choices[0] || !data.choices[0].message) {
                throw new Error('Invalid AI API response');
            }

            return data.choices[0].message.content.trim();
        }

        async searchProducts(searchTerm) {
            let searchUrl = this.config.searchAuto;
            
            try {
                new URL(searchUrl);
                const separator = searchUrl.includes('?') ? '&' : '?';
                searchUrl += `${separator}term=${encodeURIComponent(searchTerm)}`;
            } catch (e) {
                const baseUrl = window.location.origin;
                const cleanPath = searchUrl.startsWith('/') ? searchUrl : '/' + searchUrl;
                const separator = cleanPath.includes('?') ? '&' : '?';
                searchUrl = baseUrl + cleanPath + separator + 'term=' + encodeURIComponent(searchTerm);
            }
            
            console.log('📡 Searching products at:', searchUrl);

            const response = await fetch(searchUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`Store search error: ${response.status}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Invalid response format from store search');
            }

            return await response.json();
        }

        formatAIResponse(response) {
            return response
                .replace(/\n\n/g, '</p><p>')
                .replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>');
        }

        openResult(url) {
            if (url && url !== '#') {
                if (this.config.newWindow) {
                    window.open(url, '_blank');
                } else {
                    window.location.href = url;
                }
            }
        }

        addUserMessage(text) {
            const messagesContainer = document.getElementById('ai-chat-messages');
            
            const messageDiv = document.createElement('div');
            messageDiv.className = 'ai-message user-message';
            messageDiv.innerHTML = `
                <div class="ai-avatar">
                    <i class="fa fa-user"></i>
                </div>
                <div class="ai-bubble">
                    ${this.escapeHtml(text)}
                </div>
            `;
            
            messagesContainer.appendChild(messageDiv);
            this.scrollToBottom();
        }

        addAIMessage(html) {
            const messagesContainer = document.getElementById('ai-chat-messages');
            
            const messageDiv = document.createElement('div');
            messageDiv.className = 'ai-message';
            messageDiv.innerHTML = `
                <div class="ai-avatar">
                    <i class="fa fa-robot"></i>
                </div>
                <div class="ai-bubble">
                    ${html}
                </div>
            `;
            
            messagesContainer.appendChild(messageDiv);
            this.scrollToBottom();
        }

        handleError(message) {
            this.addAIMessage(`
                <div class="ai-error">
                    <strong>❌ Error</strong><br>
                    ${message}
                </div>
                <p>Verifica tu conexión e intenta nuevamente.</p>
            `);
        }

        updateStatus(text, showTyping = false) {
            const statusElement = document.getElementById('ai-search-status');
            if (!statusElement) return;
            
            const textElement = statusElement.querySelector('.ai-status-text');
            const typingElement = statusElement.querySelector('.ai-typing-indicator');
            
            if (textElement) textElement.textContent = text;
            if (typingElement) typingElement.style.display = showTyping ? 'flex' : 'none';
            
            const sendBtn = document.getElementById('ai-chat-send');
            if (sendBtn) sendBtn.disabled = this.isProcessing;
        }

        scrollToBottom() {
            const messagesContainer = document.getElementById('ai-chat-messages');
            setTimeout(() => {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 100);
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ============ MÉTODOS DE CONTEXTO CONVERSACIONAL MULTILINGÜE ============
        
        getConversationSummary() {
            const recentHistory = this.fullConversationHistory.slice(-10);
            const userMessages = recentHistory.filter(msg => msg.role === 'user');
            const topics = this.conversationContext.discussedTopics;
            const searches = this.conversationContext.lastSearchTerms;
            const languages = this.conversationContext.detectedLanguages;
            
            return {
                messageCount: this.fullConversationHistory.length,
                recentTopics: topics.slice(-3),
                recentSearches: searches.slice(-3),
                lastUserQueries: userMessages.slice(-3).map(msg => ({
                    content: msg.content,
                    language: msg.language
                })),
                sessionDuration: Date.now() - this.conversationContext.sessionStartTime,
                detectedLanguages: languages, // NUEVO
                primaryLanguage: this.getLastUserLanguage() // NUEVO
            };
        }

        debugConversationContext() {
            console.log('🔍 MULTILINGUAL CONVERSATION CONTEXT DEBUG:');
            console.log('📊 Full History Length:', this.fullConversationHistory.length);
            console.log('🗣️ Recent Messages:', this.fullConversationHistory.slice(-5));
            console.log('🏷️ Discussed Topics:', this.conversationContext.discussedTopics);
            console.log('🔍 Last Search Terms:', this.conversationContext.lastSearchTerms);
            console.log('🌐 Detected Languages:', this.conversationContext.detectedLanguages);
            console.log('🎯 Primary Language:', this.getLastUserLanguage());
            console.log('⏱️ Session Duration:', (Date.now() - this.conversationContext.sessionStartTime) / 1000, 'seconds');
            
            return this.getConversationSummary();
        }

        resetConversationContext() {
            this.fullConversationHistory = [];
            this.conversationHistory = [];
            this.conversationContext = {
                lastSearchTerms: [],
                discussedTopics: [],
                userPreferences: {},
                sessionStartTime: Date.now(),
                detectedLanguages: [] // NUEVO
            };
            
            console.log('🔄 Multilingual conversation context reset');
        }
    }

    // Initialize AI Product Chat Multilingual
    window.AIProductChatInstance = new AIProductChat();

    // ============ FUNCIONES GLOBALES PARA DEBUGGING MULTILINGÜE ============
    window.debugAIContext = function() {
        return window.AIProductChatInstance.debugConversationContext();
    };

    window.resetAIContext = function() {
        window.AIProductChatInstance.resetConversationContext();
    };

    window.getAIConversationSummary = function() {
        return window.AIProductChatInstance.getConversationSummary();
    };

    window.testLanguageDetection = function(text) {
        return window.AIProductChatInstance.detectLanguage(text);
    };
    // ==========================================================

    console.log('🎉 AI Product Chat Multilingual (ES/EN/FR/PT) with Extended Context ready!');
    console.log('🔧 Debug functions: debugAIContext(), resetAIContext(), getAIConversationSummary(), testLanguageDetection()');
    console.log('🌐 Supported languages: Spanish, English, French, Portuguese');
});

// Legacy compatibility function for Enter key
function myclickFunction() {
    <?php if ($ai_chat_enter): ?>
        var searchkey = document.querySelector('#ai-chat-input').value || 'búsqueda';
        window.location = "index.php?rt=product/search&keyword=" + encodeURIComponent(searchkey) + "&category_id=0&description=1&model=1";
    <?php endif; ?>
}
</script>