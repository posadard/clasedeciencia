// SEO and Schema.org Implementation for FreeScienceProject.com
class SEOManager {
    constructor() {
        this.initializeStructuredData();
        this.initializeAnalytics();
        this.optimizeImages();
        this.implementLocalSEO();
        // Expose instance for other modules to reuse structured-data helpers
        try { window.SEO_MANAGER = this; } catch (e) { /* ignore in non-browser env */ }
    }

    // Initialize Schema.org structured data
    initializeStructuredData() {
        this.addOrganizationSchema();
        // Skip WebsiteSchema - handled by individual pages
        this.addBreadcrumbSchema();
        this.addEducationalResourceSchema();
        // Skip FAQ schema - handled by page-specific microdata
    }

    // Organization Schema for the website
    addOrganizationSchema() {
        const organizationSchema = {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "Free Science Project",
            "url": "https://freescienceproject.com",
            "logo": "https://freescienceproject.com/images/freescienceproject02.jpg",
            "description": "Free science fair project ideas and educational science kits for students of all ages. From elementary to high school level experiments in physics, chemistry, biology, and more.",
            "foundingDate": "2005",
            "contactPoint": {
                "@type": "ContactPoint",
                "telephone": "+1-973-405-6247",
                "contactType": "customer service",
                "availableLanguage": "English"
            },
            "sameAs": [
                "https://www.scienceproject.com",
                "https://www.kidslovekits.com"
            ],
            "address": {
                "@type": "PostalAddress",
                "addressCountry": "US",
                "addressRegion": "NJ"
            }
        };
        
        this.addStructuredData(organizationSchema);
    }

    // Website Schema
    addWebsiteSchema() {
        const websiteSchema = {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "Free Science Project",
            "url": "https://freescienceproject.com",
            "description": "Comprehensive collection of free science fair project ideas, experiments, and educational resources for students grades K-12.",
            "potentialAction": {
                "@type": "SearchAction",
                "target": {
                    "@type": "EntryPoint",
                    "urlTemplate": "https://freescienceproject.com/search?q={search_term_string}"
                },
                "query-input": "required name=search_term_string"
            },
            "publisher": {
                "@type": "Organization",
                "name": "Free Science Project"
            }
        };
        
        this.addStructuredData(websiteSchema);
    }

    // Dynamic breadcrumb schema
    addBreadcrumbSchema() {
        const path = window.location.pathname;
        const breadcrumbs = this.generateBreadcrumbs(path);
        
        if (breadcrumbs.length > 1) {
            const breadcrumbSchema = {
                "@context": "https://schema.org",
                "@type": "BreadcrumbList",
                "itemListElement": breadcrumbs.map((item, index) => ({
                    "@type": "ListItem",
                    "position": index + 1,
                    "name": item.name,
                    "item": item.url
                }))
            };
            
            this.addStructuredData(breadcrumbSchema);
        }
    }

    // Educational Resource Schema for project pages
    addEducationalResourceSchema() {
        const projectData = this.extractProjectData();
        
        if (projectData) {
            const educationalSchema = {
                "@context": "https://schema.org",
                "@type": "Course",
                "name": projectData.title,
                "description": projectData.description,
                "provider": {
                    "@type": "Organization",
                    "name": "Free Science Project",
                    "url": "https://freescienceproject.com"
                },
                "educationalLevel": projectData.gradeLevel,
                "teaches": projectData.subjects,
                "timeRequired": projectData.timeRequired || "P1W",
                "image": projectData.image,
                "isAccessibleForFree": true,
                "inLanguage": "en-US",
                "keywords": projectData.keywords,
                "learningResourceType": "Experiment",
                "educationalUse": "assignment"
            };
            
            this.addStructuredData(educationalSchema);
        }
    }

    // Add FAQ Schema for project pages
    addFAQSchema(faqs) {
        const faqSchema = {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": faqs.map(faq => ({
                "@type": "Question",
                "name": faq.question,
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": faq.answer
                }
            }))
        };
        
        this.addStructuredData(faqSchema);
    }

    // Generate breadcrumbs from URL path
    generateBreadcrumbs(path) {
        const segments = path.split('/').filter(segment => segment);
        const breadcrumbs = [{
            name: "Home",
            url: "https://freescienceproject.com"
        }];

        let currentPath = "";
        const pathMap = {
            "projects": "Science Projects",
            "elementary": "Elementary Projects",
            "intermediate": "Intermediate Projects", 
            "primary": "Primary Projects",
            "senior": "Senior Projects"
        };

        segments.forEach(segment => {
            currentPath += "/" + segment;
            const name = pathMap[segment] || this.formatSegmentName(segment);
            breadcrumbs.push({
                name: name,
                url: "https://freescienceproject.com" + currentPath
            });
        });

        return breadcrumbs;
    }

    // Format URL segment to readable name
    formatSegmentName(segment) {
        return segment
            .replace(/[-_]/g, ' ')
            .replace(/\b\w/g, l => l.toUpperCase());
    }

    // Extract project data from current page
    extractProjectData() {
        const title = document.title;
        const description = document.querySelector('meta[name="description"]')?.content;
        const path = window.location.pathname;
        
        // Determine grade level from path or content
        let gradeLevel = "K-12";
        if (path.includes('elementary')) gradeLevel = "Elementary School";
        else if (path.includes('intermediate')) gradeLevel = "Middle School";
        else if (path.includes('primary')) gradeLevel = "Primary School";
        else if (path.includes('senior')) gradeLevel = "High School";

        // Extract subjects from content
        const subjects = this.extractSubjects();
        
        // Generate keywords
        const keywords = this.generateKeywords(title, description);

        return {
            title,
            description,
            gradeLevel,
            subjects,
            keywords,
            image: this.findMainImage(),
            timeRequired: this.estimateTimeRequired()
        };
    }

    // Extract science subjects from page content
    extractSubjects() {
        const content = document.body.textContent.toLowerCase();
        const subjects = [];
        
        const subjectMap = {
            'physics': ['physics', 'electricity', 'magnetism', 'motion', 'energy', 'force'],
            'chemistry': ['chemistry', 'chemical', 'reaction', 'acid', 'base', 'molecule'],
            'biology': ['biology', 'plant', 'animal', 'cell', 'dna', 'organism'],
            'earth science': ['earth', 'geology', 'weather', 'climate', 'soil', 'rock'],
            'engineering': ['engineering', 'build', 'construct', 'design', 'machine']
        };

        Object.entries(subjectMap).forEach(([subject, keywords]) => {
            if (keywords.some(keyword => content.includes(keyword))) {
                subjects.push(subject);
            }
        });

        return subjects.length > 0 ? subjects : ['General Science'];
    }

    // Generate SEO keywords
    generateKeywords(title, description) {
        const text = (title + ' ' + description).toLowerCase();
        const commonWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        
        return text
            .split(/\W+/)
            .filter(word => word.length > 3 && !commonWords.includes(word))
            .slice(0, 10)
            .join(', ');
    }

    // Find main image for the page
    findMainImage() {
        const images = document.querySelectorAll('img');
        for (let img of images) {
            if (img.src && !img.src.includes('banner') && !img.src.includes('btn')) {
                return img.src;
            }
        }
        return "https://freescienceproject.com/images/freescienceproject02.jpg";
    }

    // Estimate time required for project
    estimateTimeRequired() {
        const content = document.body.textContent.toLowerCase();
        if (content.includes('week') || content.includes('weeks')) return "P1W";
        if (content.includes('day') || content.includes('days')) return "P1D";
        if (content.includes('hour') || content.includes('hours')) return "PT2H";
        return "P1W"; // Default to 1 week
    }

    // Add structured data to page
    addStructuredData(data) {
        const script = document.createElement('script');
        script.type = 'application/ld+json';
        script.textContent = JSON.stringify(data);
        document.head.appendChild(script);
    }

    // Initialize analytics tracking
    initializeAnalytics() {
        // Google Analytics 4
        if (typeof gtag !== 'undefined') {
            gtag('config', 'GA_MEASUREMENT_ID', {
                page_title: document.title,
                page_location: window.location.href,
                custom_map: {
                    'custom_parameter_1': 'grade_level',
                    'custom_parameter_2': 'project_type'
                }
            });
        }

        // Track project interactions
        this.trackProjectViews();
        this.trackDownloads();
        this.trackSearches();
    }

    // Track project page views
    trackProjectViews() {
        if (window.location.pathname.includes('/projects/')) {
            const projectName = this.extractProjectName();
            const gradeLevel = this.extractGradeLevel();
            
            if (typeof gtag !== 'undefined') {
                gtag('event', 'view_item', {
                    item_id: projectName,
                    item_name: projectName,
                    item_category: 'Science Project',
                    item_category2: gradeLevel
                });
            }
        }
    }

    // Track file downloads
    trackDownloads() {
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && (link.href.includes('.pdf') || link.href.includes('.doc') || link.href.includes('download'))) {
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'file_download', {
                        file_name: link.href.split('/').pop(),
                        link_url: link.href
                    });
                }
            }
        });
    }

    // Track search functionality
    trackSearches() {
        const searchInputs = document.querySelectorAll('input[type="search"], .search-input');
        searchInputs.forEach(input => {
            input.addEventListener('keyup', (e) => {
                if (e.key === 'Enter' && e.target.value.trim()) {
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'search', {
                            search_term: e.target.value.trim()
                        });
                    }
                }
            });
        });
    }

    // Extract project name from URL or title
    extractProjectName() {
        const path = window.location.pathname;
        const segments = path.split('/');
        return segments[segments.length - 1] || segments[segments.length - 2] || 'Unknown Project';
    }

    // Extract grade level from current page
    extractGradeLevel() {
        const path = window.location.pathname.toLowerCase();
        if (path.includes('elementary')) return 'Elementary';
        if (path.includes('intermediate')) return 'Intermediate';
        if (path.includes('primary')) return 'Primary';
        if (path.includes('senior')) return 'Senior';
        return 'General';
    }

    // Optimize images for SEO
    optimizeImages() {
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            // Add alt text if missing
            if (!img.alt && img.src) {
                img.alt = this.generateAltText(img.src);
            }
            
            // Add loading="lazy" for performance
            if (!img.loading) {
                img.loading = 'lazy';
            }
            
            // Add width and height if available
            if (img.naturalWidth && img.naturalHeight) {
                img.width = img.naturalWidth;
                img.height = img.naturalHeight;
            }
        });
    }

    // Generate alt text from image filename
    generateAltText(src) {
        const filename = src.split('/').pop().split('.')[0];
        return filename
            .replace(/[-_]/g, ' ')
            .replace(/\b\w/g, l => l.toUpperCase())
            + ' - Science Project Image';
    }

    // Implement local SEO
    implementLocalSEO() {
        // Add location-based meta tags
        const locationMeta = document.createElement('meta');
        locationMeta.name = 'geo.region';
        locationMeta.content = 'US-NJ';
        document.head.appendChild(locationMeta);

        const placeMeta = document.createElement('meta');
        placeMeta.name = 'geo.placename';
        placeMeta.content = 'New Jersey, United States';
        document.head.appendChild(placeMeta);

        // Add ICBM coordinates (approximate)
        const coordMeta = document.createElement('meta');
        coordMeta.name = 'ICBM';
        coordMeta.content = '40.0583, -74.4057';
        document.head.appendChild(coordMeta);
    }
}

// GEO (Generative Engine Optimization) Implementation
class GEOOptimizer {
    constructor() {
        // Disable site-wide contextual injections: prefer per-page SEO/GEO handled by each page.
        // Assign no-op implementations to avoid runtime errors while preventing DOM insertion.
        this.addRelatedQuestions = function() {};
        this.addContextualInformation = function() {};
        this.addRelatedTopics = function() {};
        this.addEducationalContext = function() {};
        this.addPracticalApplications = function() {};

        this.initializeGEO();
    }

    initializeGEO() {
        this.addConversationalContent();
        this.implementQuestionAnswering();
        this.addContextualInformation();
        this.optimizeForVoiceSearch();
        // addRelatedQuestions may be missing in older builds; guard the call
        if (typeof this.addRelatedQuestions === 'function') {
            try { this.addRelatedQuestions(); } catch (e) { console && console.warn && console.warn('addRelatedQuestions failed', e); }
        } else {
            // Provide a lightweight fallback to avoid runtime TypeError
            this.addRelatedQuestions = function() {
                // generate a minimal related-questions block based on page keywords
                try {
                    const related = (this.generateRelatedTopics && typeof this.generateRelatedTopics === 'function') ? this.generateRelatedTopics().slice(0,3) : [];
                    if (related.length === 0) return;
                    const container = document.createElement('div');
                    container.className = 'related-questions';
                    container.innerHTML = `\n                        <h3>Related Questions</h3>\n                        <ul>\n                            ${related.map(r => `<li>What is ${r}?</li>`).join('')}\n                        </ul>\n                    `;
                    const main = document.querySelector('main, .main-content, body');
                    if (main) main.appendChild(container);
                } catch (err) {
                    console && console.debug && console.debug('related questions fallback failed', err);
                }
            };
            // call the fallback once
            try { this.addRelatedQuestions(); } catch (e) { /* ignore */ }
        }
    }

    // Add conversational content patterns
    addConversationalContent() {
        const projectElements = document.querySelectorAll('.project-card, .project-content');
        projectElements.forEach(element => {
            this.enhanceWithConversationalText(element);
        });
    }

    // Enhance elements with conversational patterns
    enhanceWithConversationalText(element) {
        const title = element.querySelector('h1, h2, h3')?.textContent;
        if (title) {
            const conversationalIntro = this.generateConversationalIntro(title);
            const introElement = document.createElement('div');
            introElement.className = 'conversational-intro';
            introElement.innerHTML = conversationalIntro;
            element.insertBefore(introElement, element.firstChild);
        }
    }

    // Generate conversational introductions
    generateConversationalIntro(title) {
        const templates = [
            `Looking to learn about ${title.toLowerCase()}? You're in the right place!`,
            `Curious about ${title.toLowerCase()}? Let's explore this fascinating science concept together.`,
            `Want to understand ${title.toLowerCase()}? Here's everything you need to know.`,
            `${title} is one of the most interesting topics in science. Here's why:`
        ];
        
        return templates[Math.floor(Math.random() * templates.length)];
    }

    // Implement question-answering format
    implementQuestionAnswering() {
        this.addCommonQuestions();
        // If SEO_MANAGER is available, publish FAQ schema for these common questions
        try {
            if (window.SEO_MANAGER && typeof window.SEO_MANAGER.addFAQSchema === 'function' && this._commonQuestions) {
                window.SEO_MANAGER.addFAQSchema(this._commonQuestions);
            }
        } catch (e) {
            // Silently ignore schema publishing errors
            console.warn('FAQ schema publish failed', e);
        }
    }

    // Add common questions to project pages
    addCommonQuestions() {
        const commonQuestions = [
            {
                question: "What grade level is this project suitable for?",
                answer: this.generateGradeLevelAnswer()
            },
            {
                question: "How long does this science project take?",
                answer: this.generateTimeAnswer()
            },
            {
                question: "What materials do I need for this experiment?",
                answer: this.generateMaterialsAnswer()
            },
            {
                question: "Is this project safe for kids?",
                answer: this.generateSafetyAnswer()
            },
            {
                question: "What will students learn from this project?",
                answer: this.generateLearningAnswer()
            }
        ];

        // Only inject site-level FAQ if the page doesn't already provide a per-page FAQ OR has dynamic FAQ container
        if (!document.querySelector('.faq, .faq-section, .related-projects .abantecart_product, #dynamic-faq-container')) {
            this.displayQuestions(commonQuestions);
        }
        // store for schema publishing
        this._commonQuestions = commonQuestions;
    }

    // Generate grade level answer
    generateGradeLevelAnswer() {
        const gradeLevel = this.extractGradeLevel();
        return `This science project is designed for ${gradeLevel} students, typically ages ${this.getAgeRange(gradeLevel)}. However, with adult supervision, younger students can also participate and learn from this experiment.`;
    }

    // Generate time requirement answer
    generateTimeAnswer() {
        return "Most of our science projects can be completed in 1-2 hours of active work time, though some experiments may require observation over several days or weeks for complete results.";
    }

    // Generate materials answer
    generateMaterialsAnswer() {
        return "We provide a complete list of materials needed for each project. Most items can be found around the house or purchased inexpensively at local stores. For convenience, we also offer complete science kits with all materials included.";
    }

    // Generate safety answer
    generateSafetyAnswer() {
        return "Safety is our top priority. All our projects are designed to be safe when proper precautions are followed. We always recommend adult supervision for younger students and provide clear safety guidelines for each experiment.";
    }

    // Generate learning objectives answer
    generateLearningAnswer() {
        const subjects = this.extractSubjects();
        return `Students will learn key concepts in ${subjects.join(', ')}, develop scientific thinking skills, and gain hands-on experience with the scientific method through observation, hypothesis formation, and experimentation.`;
    }

    // Extract grade level from page context
    extractGradeLevel() {
        const path = window.location.pathname.toLowerCase();
        if (path.includes('elementary')) return 'elementary school';
        if (path.includes('intermediate')) return 'middle school';
        if (path.includes('primary')) return 'primary school';
        if (path.includes('senior')) return 'high school';
        return 'all grade levels';
    }

    // Get age range for grade level
    getAgeRange(gradeLevel) {
        const ageMap = {
            'primary school': '6-10',
            'elementary school': '10-14',
            'middle school': '13-15',
            'high school': '14-18',
            'all grade levels': '6-18'
        };
        return ageMap[gradeLevel] || '6-18';
    }

    // Extract subjects from page content
    extractSubjects() {
        // Reuse the method from SEOManager
        const content = document.body.textContent.toLowerCase();
        const subjects = [];
        
        const subjectMap = {
            'physics': ['physics', 'electricity', 'magnetism', 'motion', 'energy'],
            'chemistry': ['chemistry', 'chemical', 'reaction', 'acid', 'base'],
            'biology': ['biology', 'plant', 'animal', 'cell', 'dna'],
            'earth science': ['earth', 'geology', 'weather', 'climate'],
        };

        Object.entries(subjectMap).forEach(([subject, keywords]) => {
            if (keywords.some(keyword => content.includes(keyword))) {
                subjects.push(subject);
            }
        });

        return subjects.length > 0 ? subjects : ['general science'];
    }

    // Display questions in a structured format
    displayQuestions(questions) {
    // If the page already has an FAQ or FAQ container, do not inject to avoid duplicates
    if (document.querySelector('.faq, .faq-section, #dynamic-faq-container')) return;

    const questionsContainer = document.createElement('div');
    questionsContainer.className = 'faq-section';
        questionsContainer.innerHTML = `
            <h3>Frequently Asked Questions</h3>
            ${questions.map(q => `
                <div class="faq-item">
                    <h4 class="faq-question">${q.question}</h4>
                    <div class="faq-answer">${q.answer}</div>
                </div>
            `).join('')}
        `;

        // Insert before footer or at end of main content
        const main = document.querySelector('main, .main-content, body');
        const footer = document.querySelector('footer, .footer');
        
        if (footer && main) {
            main.insertBefore(questionsContainer, footer);
        } else if (main) {
            main.appendChild(questionsContainer);
        }
    }

    // Add contextual information
    addContextualInformation() {
        this.addRelatedTopics();
        this.addEducationalContext();
        this.addPracticalApplications();
    }

    // Add related topics section
    addRelatedTopics() {
        const relatedTopics = this.generateRelatedTopics();
        if (relatedTopics.length > 0) {
            const relatedSection = document.createElement('div');
            relatedSection.className = 'related-topics';
            relatedSection.innerHTML = `
                <h3>Related Science Topics</h3>
                <ul>
                    ${relatedTopics.map(topic => `<li>${topic}</li>`).join('')}
                </ul>
            `;
            
            const main = document.querySelector('main, .main-content, body');
            if (main) {
                main.appendChild(relatedSection);
            }
        }
    }

    // Generate related topics based on current content
    generateRelatedTopics() {
        const content = document.body.textContent.toLowerCase();
        const topics = [];
        
        const topicMap = {
            'electricity': ['Magnetism', 'Circuits', 'Conductors and Insulators', 'Static Electricity'],
            'magnetism': ['Electricity', 'Magnetic Fields', 'Electromagnets', 'Compass Navigation'],
            'plant': ['Photosynthesis', 'Plant Growth', 'Botany', 'Ecosystems'],
            'chemistry': ['Chemical Reactions', 'pH and Acids', 'Molecules', 'States of Matter'],
            'physics': ['Motion and Forces', 'Energy', 'Light and Sound', 'Simple Machines']
        };

        Object.entries(topicMap).forEach(([keyword, relatedTopics]) => {
            if (content.includes(keyword)) {
                topics.push(...relatedTopics);
            }
        });

        return [...new Set(topics)].slice(0, 5); // Remove duplicates and limit to 5
    }

    // Add educational context
    addEducationalContext() {
        const context = document.createElement('div');
        context.className = 'educational-context';
        context.innerHTML = `
            <h3>Educational Value</h3>
            <p>This science project aligns with STEM education standards and helps students develop critical thinking, problem-solving, and scientific inquiry skills. Perfect for science fair competitions, classroom demonstrations, or home learning activities.</p>
        `;
        
        const main = document.querySelector('main, .main-content');
        if (main) {
            main.appendChild(context);
        }
    }

    // Add practical applications
    addPracticalApplications() {
        const applications = this.generatePracticalApplications();
        if (applications.length > 0) {
            const applicationsSection = document.createElement('div');
            applicationsSection.className = 'practical-applications';
            applicationsSection.innerHTML = `
                <h3>Real-World Applications</h3>
                <ul>
                    ${applications.map(app => `<li>${app}</li>`).join('')}
                </ul>
            `;
            
            const main = document.querySelector('main, .main-content');
            if (main) {
                main.appendChild(applicationsSection);
            }
        }
    }

    // Generate practical applications based on content
    generatePracticalApplications() {
        const content = document.body.textContent.toLowerCase();
        const applications = [];
        
        if (content.includes('electricity')) {
            applications.push('Power generation and distribution systems');
            applications.push('Electronic devices and circuits');
        }
        
        if (content.includes('magnet')) {
            applications.push('MRI machines in medical imaging');
            applications.push('Electric motors and generators');
        }
        
        if (content.includes('plant')) {
            applications.push('Agriculture and crop optimization');
            applications.push('Environmental conservation');
        }
        
        if (content.includes('chemistry')) {
            applications.push('Pharmaceutical development');
            applications.push('Materials science and engineering');
        }

        return applications.slice(0, 4);
    }

    // Optimize for voice search
    optimizeForVoiceSearch() {
        this.addNaturalLanguageQueries();
        this.optimizeForLocalVoiceSearch();
    }

    // Add natural language query patterns
    addNaturalLanguageQueries() {
        const queries = this.generateVoiceSearchQueries();
        
        // Add these as hidden content for search engines
        const voiceOptimization = document.createElement('div');
        voiceOptimization.className = 'voice-search-optimization';
        voiceOptimization.style.display = 'none';
        voiceOptimization.innerHTML = queries.map(query => `<span>${query}</span>`).join(' ');
        
        document.body.appendChild(voiceOptimization);
    }

    // Generate voice search friendly queries
    generateVoiceSearchQueries() {
        const title = document.title.toLowerCase();
        const projectName = title.split('-')[0]?.trim() || 'science project';
        
        return [
            `How do I do a ${projectName}`,
            `What is a ${projectName} for kids`,
            `${projectName} science fair project ideas`,
            `Easy ${projectName} experiments for students`,
            `Step by step ${projectName} instructions`,
            `${projectName} materials list`,
            `${projectName} for elementary students`,
            `Safe ${projectName} activities for children`
        ];
    }

    // Optimize for local voice search
    optimizeForLocalVoiceSearch() {
        const localQueries = [
            'science projects near me',
            'science fair help in New Jersey',
            'local science education resources',
            'science kits delivery in NJ'
        ];
        
        const localOptimization = document.createElement('div');
        localOptimization.className = 'local-voice-optimization';
        localOptimization.style.display = 'none';
        localOptimization.innerHTML = localQueries.join(' ');
        
        document.body.appendChild(localOptimization);
    }
}

// Initialize SEO and GEO optimization when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new SEOManager();
    new GEOOptimizer();
});

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { SEOManager, GEOOptimizer };
}