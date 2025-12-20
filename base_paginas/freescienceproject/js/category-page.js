// Category Page Project Manager
// Handles project display and search for specific grade category pages

class CategoryPageManager {
  constructor(category) {
    this.category = category;
    this.projects = [];
    this.filteredProjects = [];
    this.currentSearch = '';
    this.isReady = false;
    this.isApplyingUrlSearch = false;
    
    // Manual initialization instead of auto-init to avoid conflicts
    console.log('CategoryPageManager created for:', category);
  }
  
  init() {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.setup());
    } else {
      this.setup();
    }
  }
  
  setup() {
    console.log('CategoryPageManager setup starting...');
    this.loadProjects();
    this.setupSearch();
    this.displayProjects();
    this.isReady = true;
    console.log('CategoryPageManager setup complete. Ready for URL search.');
    
    // Dispatch custom event to signal readiness
    document.dispatchEvent(new CustomEvent('categoryManagerReady', { 
      detail: { manager: this } 
    }));
  }
  
  loadProjects() {
    console.log('Loading projects for category:', this.category);
    console.log('searchUtils available:', typeof searchUtils !== 'undefined');
    console.log('scienceProjectsData available:', typeof scienceProjectsData !== 'undefined');
    
    if (typeof searchUtils !== 'undefined') {
      this.projects = searchUtils.getProjectsByCategory(this.category);
      this.filteredProjects = [...this.projects];
      console.log(`Loaded ${this.projects.length} projects for ${this.category}`);
      console.log('First few projects:', this.projects.slice(0, 3));
    } else if (typeof scienceProjectsData !== 'undefined') {
      // Fallback: direct access to data
      this.projects = scienceProjectsData[this.category] || [];
      this.filteredProjects = [...this.projects];
      console.log(`Loaded ${this.projects.length} projects via fallback for ${this.category}`);
    } else {
      console.error('Neither searchUtils nor scienceProjectsData is available');
    }
  }
  
  setupSearch() {
    const searchInput = document.getElementById('search-projects');
    if (searchInput) {
      // Handle input changes
      searchInput.addEventListener('input', (e) => {
        // Don't interfere if we're applying URL search
        if (this.isApplyingUrlSearch) {
          console.log('Ignoring input event during URL search application');
          return;
        }
        this.currentSearch = e.target.value.trim();
        this.filterProjects();
      });
      
      // Handle keyboard events for mobile compatibility
      searchInput.addEventListener('keydown', (e) => this.handleSearchKeyboard(e));
      searchInput.addEventListener('keyup', (e) => this.handleSearchKeyboard(e));
      searchInput.addEventListener('keypress', (e) => this.handleSearchKeyboard(e));
      
      // Handle search event for mobile virtual keyboards
      searchInput.addEventListener('search', (e) => {
        this.currentSearch = e.target.value.trim();
        this.filterProjects();
      });
      
      // Handle form submission
      const searchForm = searchInput.closest('form, .search-form');
      if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
          e.preventDefault();
          this.currentSearch = searchInput.value.trim();
          this.filterProjects();
        });
      }
    }
    
    // Setup all filter checkboxes
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
      checkbox.addEventListener('change', () => this.filterProjects());
    });
    
    // Setup sort dropdown
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
      sortSelect.addEventListener('change', (e) => {
        this.sortProjects(e.target.value);
      });
    }
  }
  
  handleSearchKeyboard(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
      e.preventDefault();
      e.stopPropagation();
      const searchInput = document.getElementById('search-projects');
      if (searchInput) {
        this.currentSearch = searchInput.value.trim();
        this.filterProjects();
      }
    }
  }
  
  filterProjects() {
    console.log('filterProjects called. Current search:', this.currentSearch, 'URL search in progress:', this.isApplyingUrlSearch);
    let filtered = [...this.projects];
    
    // Apply search filter
    if (this.currentSearch) {
      filtered = filtered.filter(project => 
        project.title.toLowerCase().includes(this.currentSearch) ||
        project.description.toLowerCase().includes(this.currentSearch) ||
        project.subject.toLowerCase().includes(this.currentSearch)
      );
    }
    
    // Apply checkbox filters
    const selectedSubjects = this.getCheckedValues('subject');
    const selectedDifficulties = this.getCheckedValues('difficulty');
    const selectedMaterials = this.getCheckedValues('materials');
    const selectedQuick = this.getCheckedValues('quick');
    
    filtered = filtered.filter(project => {
      const matchesSubject = selectedSubjects.length === 0 || selectedSubjects.includes(project.subject);
      const matchesDifficulty = selectedDifficulties.length === 0 || selectedDifficulties.includes(project.difficulty);
      const matchesMaterials = selectedMaterials.length === 0 || selectedMaterials.includes(project.materials);
      const matchesQuick = selectedQuick.length === 0 || 
                         (selectedQuick.includes('popular') && project.popular) ||
                         (selectedQuick.includes('new') && project.new);
      
      return matchesSubject && matchesDifficulty && matchesMaterials && matchesQuick;
    });
    
    this.filteredProjects = filtered;
    this.displayProjects();
  }

  getCheckedValues(name) {
    return Array.from(document.querySelectorAll(`input[name="${name}"]:checked`))
                .map(checkbox => checkbox.value);
  }

  sortProjects(sortBy) {
    if (!sortBy) return;
    
    this.filteredProjects.sort((a, b) => {
      switch(sortBy) {
        case 'name':
          return a.title.localeCompare(b.title);
        case 'subject':
          return a.subject.localeCompare(b.subject);
        case 'difficulty':
          const difficultyOrder = { 'easy': 1, 'medium': 2, 'hard': 3, 'advanced': 3 };
          return difficultyOrder[a.difficulty] - difficultyOrder[b.difficulty];
        case 'popular':
          return (b.popular ? 1 : 0) - (a.popular ? 1 : 0);
        default:
          return 0;
      }
    });
    
    this.displayProjects();
  }

  clearAllFilters() {
    // Reset search input
    const searchInput = document.querySelector('#search-projects, .search-input');
    if (searchInput) {
      searchInput.value = '';
    }
    
    // Reset all checkboxes
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
      checkbox.checked = false;
    });
    
    // Reset sort dropdown
    const sortSelect = document.querySelector('#sort-select, .sort-select');
    if (sortSelect) {
      sortSelect.value = '';
    }
    
    // Reset to show all projects
    this.filteredProjects = [...this.projects];
    this.displayProjects();
    
    console.log('All filters cleared');
  }
  
  applyUrlSearch(searchTerm) {
    console.log('applyUrlSearch method called with term:', searchTerm);
    
    // Set flag to prevent interference
    this.isApplyingUrlSearch = true;
    
    // Update search input
    const searchInput = document.getElementById('search-projects');
    if (searchInput) {
      searchInput.value = searchTerm;
    }
    
    // Clear any existing filters
    document.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => cb.checked = false);
    
    // Set search term and filter
    this.currentSearch = searchTerm.toLowerCase();
    this.filterProjects();
    
    // Clear flag after completion
    setTimeout(() => {
      this.isApplyingUrlSearch = false;
      console.log('URL search application completed via method');
    }, 500);
    
    return true;
  }
  
  displayProjects() {
    const container = document.getElementById('projects-container');
    const resultsCount = document.getElementById('results-count');
    const noResults = document.getElementById('no-results');
    
    if (!container) {
      console.error('Projects container not found');
      return;
    }
    
    console.log(`Displaying ${this.filteredProjects.length} projects`);
    
    // Update results count
    if (resultsCount) {
      resultsCount.textContent = `Showing ${this.filteredProjects.length} projects`;
    }
    
    // Show/hide no results message
    if (noResults) {
      noResults.style.display = this.filteredProjects.length === 0 ? 'block' : 'none';
    }
    
    // Always clear container first to ensure clean state
    container.innerHTML = '';
    
    // Display projects
    if (this.filteredProjects.length > 0) {
      // Generate all cards
      const cardsHTML = this.filteredProjects.map(project => this.createProjectCard(project)).join('');
      container.innerHTML = cardsHTML;
      
      // Force layout recalculation
      container.offsetHeight;
      
      // Debug: log number of widgets created
      const widgetCount = container.querySelectorAll('.abantecart-widget-container').length;
      const cardCount = container.querySelectorAll('.project-card').length;
      console.log(`Rendered ${cardCount} cards with ${widgetCount} shop widgets`);
      
      // Reinitialize AbanteCart widgets after DOM update
      this.reinitializeShopWidgets();
    }
  }
  
  reinitializeShopWidgets() {
    // Wait for DOM to be fully updated
    setTimeout(() => {
      const widgets = document.querySelectorAll('.abantecart-widget-container');
      const supplySections = document.querySelectorAll('.related-supplies-section');
      
      console.log(`Reinitializing: ${widgets.length} widgets in ${supplySections.length} sections`);
      
      // Ensure all containers and their parents are visible
      supplySections.forEach((section, sectionIndex) => {
        // Make sure parent section is visible
        section.style.display = 'block';
        section.style.visibility = 'visible';
        section.style.opacity = '1';
        
        // Find widget container within this section
        const widget = section.querySelector('.abantecart-widget-container');
        if (widget) {
          // Reset and show widget
          widget.style.display = 'grid';
          widget.style.visibility = 'visible';
          widget.style.opacity = '1';
          
          // Ensure proper grid layout
          widget.style.gridTemplateColumns = 'repeat(4, 1fr)';
          
          // Force layout recalculation
          widget.offsetHeight;
          
          console.log(`Section ${sectionIndex + 1} widget reinitialized`);
        }
      });
      
      // Try to reinitialize AbanteCart if available
      if (typeof window.AbanteCart !== 'undefined') {
        try {
          if (window.AbanteCart.initWidgets) {
            window.AbanteCart.initWidgets();
            console.log('AbanteCart.initWidgets() called successfully');
          } else if (window.AbanteCart.init) {
            window.AbanteCart.init();
            console.log('AbanteCart.init() called successfully');
          }
        } catch (error) {
          console.log('AbanteCart reinit error:', error);
        }
      }
      
      // Final verification
      const visibleWidgets = document.querySelectorAll('.abantecart-widget-container:not([style*="display: none"])');
      console.log(`${visibleWidgets.length} widgets are now visible`);
      
      // Add click handlers to make entire li elements clickable
      this.addClickHandlersToSupplyItems();
      
    }, 250); // Increased timeout for better reliability
  }
  
  addClickHandlersToSupplyItems() {
    // Add click handlers to all supply items to make the entire li clickable
    const supplyItems = document.querySelectorAll('.abantecart_category');
    
    supplyItems.forEach(item => {
      // Remove any existing click handlers to avoid duplicates
      item.replaceWith(item.cloneNode(true));
    });
    
    // Re-select items after cloning
    const refreshedItems = document.querySelectorAll('.abantecart_category');
    
    refreshedItems.forEach(item => {
      item.style.cursor = 'pointer';
      
      item.addEventListener('click', function(e) {
        // Prevent default behavior
        e.preventDefault();
        e.stopPropagation();
        
        // Get category ID and construct URL
        const categoryId = this.dataset.categoryId;
        if (categoryId) {
          const url = `https://shop.miniscience.com/index.php?rt=product/category&path=${categoryId}`;
          console.log('Opening supply category:', url);
          window.open(url, '_blank');
        } else {
          console.error('No category ID found for supply item');
        }
      });
      
      // Add hover effect
      item.addEventListener('mouseenter', function() {
        this.style.backgroundColor = '#f0f3ff';
      });
      
      item.addEventListener('mouseleave', function() {
        this.style.backgroundColor = '';
      });
    });
    
    console.log(`Added click handlers to ${refreshedItems.length} supply items`);
  }
  
  createProjectCard(project) {
    const popularBadge = project.popular ? '<div class="popular-badge">Popular</div>' : '';
    const gradeColors = {
      primary: '#4caf50',
      elementary: '#2196f3', 
      intermediate: '#ff9800',
      senior: '#9c27b0'
    };
    
    // Use project's actual category, not page category (important for "all projects" page)
    const projectCategory = project.category || this.category;
    const iconColor = gradeColors[projectCategory] || gradeColors['primary']; // fallback to primary
    
    // Generate shop widget categories only for individual category pages, not for "all projects"
    const shopCategories = this.category !== 'all' ? this.getShopCategories(project) : [];
    const shopWidget = this.category !== 'all' ? this.generateShopWidget(shopCategories) : '';
    
    // Determine if we should show Related Supplies section
    const showRelatedSupplies = this.category !== 'all' && shopWidget;
    
    return `
      <div class="project-card${showRelatedSupplies ? ' with-shop-widget' : ''}" data-subject="${project.subject}" data-difficulty="${project.difficulty}">
        ${popularBadge}
        <div class="project-main-content">
          <div class="project-header">
            <div class="project-icon" style="background: ${iconColor}">
              ${this.getGradeIconForCategory(projectCategory)}
            </div>
            <div class="project-meta">
              <span class="difficulty-badge ${project.difficulty}">${project.difficulty}</span>
              <span class="subject-badge">${project.subject.replace('-', ' ')}</span>
            </div>
          </div>
          <div class="project-content">
            <h3 class="project-title">${project.title}</h3>
            <p class="project-description">${project.description}</p>
            <div class="project-tags">
              <span class="tag materials-tag">${project.materials}</span>
              <span class="tag grade-tag">${project.grade}</span>
            </div>
          </div>
          <div class="project-actions">
            ${showRelatedSupplies ? `
              <div class="related-supplies-section">
                <h4>Related Supplies</h4>
                ${shopWidget}
              </div>
            ` : ''}
            <a href="${project.url}" class="btn-primary">Start Project</a>
          </div>
        </div>
      </div>
    `;
  }
  
  getGradeIcon() {
    // Use CSS-based icons instead of emojis
    const icons = {
      primary: 'K-4',
      elementary: '4-6',
      intermediate: '7-8',
      senior: '9-12'
    };
    return icons[this.category] || 'K-12';
  }
  
  getGradeIconForCategory(category) {
    // Use CSS-based icons instead of emojis - version that accepts category parameter
    const icons = {
      primary: 'K-4',
      elementary: '4-6',
      intermediate: '7-8',
      senior: '9-12'
    };
    return icons[category] || 'K-12';
  }
  
  getShopCategories(project) {
    const projectTitle = project.title.toLowerCase();
    const projectDescription = (project.description || '').toLowerCase();
    const projectText = projectTitle + ' ' + projectDescription;
    
    // Advanced smart categorization based on subject + materials + content
    const smartMapping = this.getSmartMapping(project, projectText);
    if (smartMapping) {
      return smartMapping;
    }
    
    // Fallback to subject-based categories
    const subjectMap = {
      'physics': [
        { id: 132, name: 'Physics > Magnetism > Magnets' },
        { id: 145, name: 'Tools > Thermometers' },
        { id: 108, name: 'Lab Supplies > Glassware' },
        { id: 140, name: 'Tools > Caliper' }
      ],
      'chemistry': [
        { id: 116, name: 'Lab Supplies > Test Tubes' },
        { id: 108, name: 'Lab Supplies > Glassware' },
        { id: 144, name: 'Tools > Scales' },
        { id: 111, name: 'Lab Supplies > pH Indicators' }
      ],
      'biology': [
        { id: 74, name: 'Biology > Specimen samples' },
        { id: 110, name: 'Lab Supplies > Magnifiers' },
        { id: 112, name: 'Lab Supplies > Plasticware' },
        { id: 73, name: 'Biology > Instruments' }
      ],
      'earth-science': [
        { id: 108, name: 'Lab Supplies > Glassware' },
        { id: 144, name: 'Tools > Scales' },
        { id: 110, name: 'Lab Supplies > Magnifiers' },
        { id: 145, name: 'Tools > Thermometers' }
      ],
      'space': [
        { id: 110, name: 'Lab Supplies > Magnifiers' },
        { id: 145, name: 'Tools > Thermometers' },
        { id: 108, name: 'Lab Supplies > Glassware' },
        { id: 140, name: 'Tools > Caliper' }
      ],
      'meteorology': [
        { id: 145, name: 'Tools > Thermometers' },
        { id: 142, name: 'Tools > Hygrometer' },
        { id: 144, name: 'Tools > Scales' },
        { id: 108, name: 'Lab Supplies > Glassware' }
      ],
      'engineering': [
        { id: 140, name: 'Tools > Caliper' },
        { id: 155, name: 'KITS > Gears-Wheels-Parts' },
        { id: 138, name: 'Physics > Pulleys' },
        { id: 115, name: 'Lab Supplies > Steelware' }
      ],
      'psychology': [
        { id: 141, name: 'Tools > Counter' },
        { id: 145, name: 'Tools > Thermometers' },
        { id: 112, name: 'Lab Supplies > Plasticware' },
        { id: 110, name: 'Lab Supplies > Magnifiers' }
      ]
    };
    
    if (subjectMap[project.subject]) {
      return subjectMap[project.subject];
    }
    
    // Grade-level defaults
    const gradeDefaults = {
      'primary': [
        { id: 112, name: 'Lab Supplies > Plasticware' },
        { id: 110, name: 'Lab Supplies > Magnifiers' },
        { id: 132, name: 'Physics > Magnetism > Magnets' },
        { id: 144, name: 'Tools > Scales' }
      ],
      'elementary': [
        { id: 116, name: 'Lab Supplies > Test Tubes' },
        { id: 145, name: 'Tools > Thermometers' },
        { id: 108, name: 'Lab Supplies > Glassware' },
        { id: 110, name: 'Lab Supplies > Magnifiers' }
      ],
      'intermediate': [
        { id: 102, name: 'Electricity > Wires' },
        { id: 108, name: 'Lab Supplies > Glassware' },
        { id: 98, name: 'Electricity > Multimeters' },
        { id: 144, name: 'Tools > Scales' }
      ],
      'senior': [
        { id: 98, name: 'Electricity > Multimeters' },
        { id: 144, name: 'Tools > Scales' },
        { id: 116, name: 'Lab Supplies > Test Tubes' },
        { id: 140, name: 'Tools > Caliper' }
      ]
    };
    
    return gradeDefaults[this.category] || gradeDefaults['primary'];
  }
  
  getSmartMapping(project, projectText) {
    const subject = project.subject;
    const materials = project.materials;
    const difficulty = project.difficulty;
    
    // Electrical/Physics projects with electrical materials
    if ((subject === 'physics' && materials === 'electrical') || 
        projectText.includes('electric') || projectText.includes('circuit') || 
        projectText.includes('battery') || projectText.includes('motor')) {
      if (projectText.includes('motor') || projectText.includes('generator')) {
        return [
          { id: 97, name: 'Electricity > Motors' },
          { id: 132, name: 'Physics > Magnetism > Magnets' },
          { id: 102, name: 'Electricity > Wires' },
          { id: 94, name: 'Electricity > Generators' }
        ];
      }
      if (projectText.includes('battery') || projectText.includes('electrode')) {
        return [
          { id: 88, name: 'Electricity > Battery Holders' },
          { id: 92, name: 'Electricity > Electrodes' },
          { id: 98, name: 'Electricity > Multimeters' },
          { id: 102, name: 'Electricity > Wires' }
        ];
      }
      if (projectText.includes('light') || projectText.includes('bulb')) {
        return [
          { id: 96, name: 'Electricity > Light Bulbs' },
          { id: 95, name: 'Electricity > Lamp Holders' },
          { id: 100, name: 'Electricity > Switches' },
          { id: 102, name: 'Electricity > Wires' }
        ];
      }
      // Default electrical
      return [
        { id: 102, name: 'Electricity > Wires' },
        { id: 90, name: 'Electricity > Connectors' },
        { id: 98, name: 'Electricity > Multimeters' },
        { id: 100, name: 'Electricity > Switches' }
      ];
    }
    
    // Magnetism projects
    if (projectText.includes('magnet') || projectText.includes('compass') || 
        projectText.includes('levitation') || projectText.includes('magnetic field')) {
      return [
        { id: 132, name: 'Physics > Magnetism > Magnets' },
        { id: 131, name: 'Physics > Magnetism > Compass' },
        { id: 117, name: 'Metals' },
        { id: 102, name: 'Electricity > Wires' }
      ];
    }
    
    // Optics projects
    if (projectText.includes('light') || projectText.includes('lens') || 
        projectText.includes('prism') || projectText.includes('mirror') || 
        projectText.includes('reflection') || projectText.includes('refraction')) {
      return [
        { id: 134, name: 'Physics > Optics > Lenses' },
        { id: 136, name: 'Physics > Optics > Prisms' },
        { id: 135, name: 'Physics > Optics > Mirrors' },
        { id: 96, name: 'Electricity > Light Bulbs' }
      ];
    }
    
    // Chemistry projects with laboratory materials
    if (subject === 'chemistry' && materials === 'laboratory') {
      if (projectText.includes('acid') || projectText.includes('base') || projectText.includes('ph')) {
        return [
          { id: 111, name: 'Lab Supplies > pH Indicators' },
          { id: 108, name: 'Lab Supplies > Glassware' },
          { id: 113, name: 'Lab Supplies > Rubber Stoppers' },
          { id: 143, name: 'Tools > pH Indicators' }
        ];
      }
      if (projectText.includes('crystal') || projectText.includes('solution') || projectText.includes('evaporation')) {
        return [
          { id: 116, name: 'Lab Supplies > Test Tubes' },
          { id: 108, name: 'Lab Supplies > Glassware' },
          { id: 144, name: 'Tools > Scales' },
          { id: 110, name: 'Lab Supplies > Magnifiers' }
        ];
      }
      if (projectText.includes('heat') || projectText.includes('temperature') || projectText.includes('thermal')) {
        return [
          { id: 145, name: 'Tools > Thermometers' },
          { id: 127, name: 'Physics > Heat' },
          { id: 108, name: 'Lab Supplies > Glassware' },
          { id: 116, name: 'Lab Supplies > Test Tubes' }
        ];
      }
      // Default chemistry lab
      return [
        { id: 116, name: 'Lab Supplies > Test Tubes' },
        { id: 108, name: 'Lab Supplies > Glassware' },
        { id: 111, name: 'Lab Supplies > pH Indicators' },
        { id: 144, name: 'Tools > Scales' }
      ];
    }
    
    // Biology projects
    if (subject === 'biology') {
      if (projectText.includes('microscope') || projectText.includes('cell') || projectText.includes('dna')) {
        return [
          { id: 73, name: 'Biology > Instruments' },
          { id: 74, name: 'Biology > Specimen samples' },
          { id: 110, name: 'Lab Supplies > Magnifiers' },
          { id: 112, name: 'Lab Supplies > Plasticware' }
        ];
      }
      if (projectText.includes('plant') || projectText.includes('seed') || projectText.includes('growth')) {
        return [
          { id: 74, name: 'Biology > Specimen samples' },
          { id: 112, name: 'Lab Supplies > Plasticware' },
          { id: 145, name: 'Tools > Thermometers' },
          { id: 144, name: 'Tools > Scales' }
        ];
      }
      if (projectText.includes('anatomy') || projectText.includes('body') || projectText.includes('heart')) {
        return [
          { id: 72, name: 'Biology > Anatomy' },
          { id: 73, name: 'Biology > Instruments' },
          { id: 110, name: 'Lab Supplies > Magnifiers' },
          { id: 112, name: 'Lab Supplies > Plasticware' }
        ];
      }
      // Default biology
      return [
        { id: 74, name: 'Biology > Specimen samples' },
        { id: 110, name: 'Lab Supplies > Magnifiers' },
        { id: 112, name: 'Lab Supplies > Plasticware' },
        { id: 73, name: 'Biology > Instruments' }
      ];
    }
    
    // Meteorology projects
    if (subject === 'meteorology') {
      if (projectText.includes('weather') || projectText.includes('atmospheric')) {
        return [
          { id: 145, name: 'Tools > Thermometers' },
          { id: 142, name: 'Tools > Hygrometer' },
          { id: 144, name: 'Tools > Scales' },
          { id: 108, name: 'Lab Supplies > Glassware' }
        ];
      }
      if (projectText.includes('wind') || projectText.includes('air pressure')) {
        return [
          { id: 142, name: 'Tools > Hygrometer' },
          { id: 108, name: 'Lab Supplies > Glassware' },
          { id: 114, name: 'Lab Supplies > Rubber tubing' },
          { id: 145, name: 'Tools > Thermometers' }
        ];
      }
    }
    
    // Engineering/Mechanical projects
    if (subject === 'engineering' || projectText.includes('bridge') || 
        projectText.includes('gear') || projectText.includes('pulley') || 
        projectText.includes('lever')) {
      return [
        { id: 155, name: 'KITS > Gears-Wheels-Parts' },
        { id: 138, name: 'Physics > Pulleys' },
        { id: 140, name: 'Tools > Caliper' },
        { id: 115, name: 'Lab Supplies > Steelware' }
      ];
    }
    
    // Volcano-specific (very popular)
    if (projectText.includes('volcano') || projectText.includes('eruption')) {
      return [
        { id: 116, name: 'Lab Supplies > Test Tubes' },
        { id: 75, name: 'Chemicals' },
        { id: 144, name: 'Tools > Scales' },
        { id: 108, name: 'Lab Supplies > Glassware' }
      ];
    }
    
    // Material-based mapping for craft projects
    if (materials === 'craft') {
      if (subject === 'physics' || subject === 'engineering') {
        return [
          { id: 155, name: 'KITS > Gears-Wheels-Parts' },
          { id: 140, name: 'Tools > Caliper' },
          { id: 115, name: 'Lab Supplies > Steelware' },
          { id: 117, name: 'Metals' }
        ];
      }
      // Default craft
      return [
        { id: 112, name: 'Lab Supplies > Plasticware' },
        { id: 110, name: 'Lab Supplies > Magnifiers' },
        { id: 144, name: 'Tools > Scales' },
        { id: 155, name: 'KITS > Gears-Wheels-Parts' }
      ];
    }
    
    // Food-based projects
    if (materials === 'food') {
      return [
        { id: 111, name: 'Lab Supplies > pH Indicators' },
        { id: 144, name: 'Tools > Scales' },
        { id: 145, name: 'Tools > Thermometers' },
        { id: 108, name: 'Lab Supplies > Glassware' }
      ];
    }
    
    // Specialized equipment projects (advanced)
    if (materials === 'specialized' && (difficulty === 'expert' || difficulty === 'advanced')) {
      return [
        { id: 98, name: 'Electricity > Multimeters' },
        { id: 140, name: 'Tools > Caliper' },
        { id: 105, name: 'KITS > Technology Kits' },
        { id: 144, name: 'Tools > Scales' }
      ];
    }
    
    return null; // Let it fall back to subject mapping
  }
  
  generateShopWidget(categories) {
    const timestamp = Date.now();
    const random = Math.floor(Math.random() * 10000);
    
    // Helper function to get clean category name (only last part after >)
    const getCleanName = (fullName) => {
      if (fullName.includes(' > ')) {
        return fullName.split(' > ').pop().trim();
      }
      return fullName;
    };
    
    const categoryItems = categories.map((cat, index) => {
      const cleanName = getCleanName(cat.name);
      return `
        <li id="abc_${timestamp}${random}${index}" class="abantecart_category" data-category-id="${cat.id}" data-language="en" data-currency="USD">
          <h3 class="abantecart_name">${cleanName}</h3>
        </li>
      `;
    }).join('');

    return `
      <ul class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        ${categoryItems}
      </ul>
    `;
  }
}

// Auto-initialize based on page category
function initCategoryPage() {
  // Determine category from page URL or title
  const path = window.location.pathname;
  let category = '';
  
  if (path.includes('primary')) category = 'primary';
  else if (path.includes('elementary')) category = 'elementary';
  else if (path.includes('intermediate')) category = 'intermediate';
  else if (path.includes('senior')) category = 'senior';
  
  console.log('Initializing category page for:', category);
  console.log('Path:', path);
  
  if (category) {
    // Check if required data is available
    if (typeof searchUtils !== 'undefined' || typeof scienceProjectsData !== 'undefined') {
      console.log('Creating CategoryPageManager for', category);
      window.categoryManager = new CategoryPageManager(category);
      
      // Manually initialize the manager
      window.categoryManager.init();
    } else {
      console.log('Data not ready, retrying in 200ms...');
      // Retry after a short delay if data isn't loaded yet
      setTimeout(initCategoryPage, 200);
    }
  } else {
    console.log('No category detected from path:', path);
  }
}

// Initialize
initCategoryPage();

// Global function for HTML onclick handlers
function clearAllFilters() {
  if (window.categoryManager && typeof window.categoryManager.clearAllFilters === 'function') {
    window.categoryManager.clearAllFilters();
  } else {
    console.log('CategoryManager not available, trying fallback...');
    // Fallback method
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
      checkbox.checked = false;
    });
    const searchInput = document.querySelector('#search-projects, .search-input');
    if (searchInput) {
      searchInput.value = '';
    }
    // Trigger search to update display
    if (window.categoryManager && typeof window.categoryManager.handleSearch === 'function') {
      window.categoryManager.handleSearch();
    }
  }
}

// Global function for mobile filter toggle
function toggleMobileFilters() {
  const filterPanel = document.getElementById('filter-panel');
  if (filterPanel) {
    filterPanel.classList.toggle('mobile-open');
  }
}

// Close mobile filters when clicking outside
document.addEventListener('click', function(e) {
  const filterPanel = document.getElementById('filter-panel');
  const toggleButton = document.querySelector('.mobile-filter-toggle');
  
  if (filterPanel && toggleButton && 
      !filterPanel.contains(e.target) && 
      !toggleButton.contains(e.target) && 
      filterPanel.classList.contains('mobile-open')) {
    filterPanel.classList.remove('mobile-open');
  }
});

// Global function to add click handlers to AbanteCart items
function addGlobalClickHandlers() {
  // Skip if we're on an individual project page
  if (window.location.pathname.includes('/projects/') && 
      window.location.pathname !== '/projects/' && 
      !window.location.pathname.endsWith('/projects/index.php')) {
    return;
  }
  
  const supplyItems = document.querySelectorAll('.abantecart_category:not([data-click-handled])');
  
  supplyItems.forEach(item => {
    // Mark as handled to avoid duplicate handlers
    item.setAttribute('data-click-handled', 'true');
    item.style.cursor = 'pointer';
    
    item.addEventListener('click', function(e) {
      // Only trigger if we didn't click on an existing link
      if (e.target.tagName !== 'A') {
        e.preventDefault();
        e.stopPropagation();
        
        // Find the link within this li element
        const link = this.querySelector('a[data-href]');
        if (link && link.dataset.href) {
          const url = link.dataset.href;
          window.open(url, '_blank');
        } else {
          // Fallback: try to construct URL from data-category-id
          const categoryId = this.dataset.categoryId;
          if (categoryId) {
            const fallbackUrl = `https://shop.miniscience.com/index.php?rt=product/category&category_id=${categoryId}`;
            window.open(fallbackUrl, '_blank');
          }
        }
      }
    });
    
    // Add hover effect
    item.addEventListener('mouseenter', function() {
      this.style.backgroundColor = '#f0f3ff';
    });
    
    item.addEventListener('mouseleave', function() {
      this.style.backgroundColor = '';
    });
  });
}

// Observe DOM changes to catch dynamically added AbanteCart content
const observer = new MutationObserver(function(mutations) {
  // Skip if we're on an individual project page
  if (window.location.pathname.includes('/projects/') && 
      window.location.pathname !== '/projects/' && 
      !window.location.pathname.endsWith('/projects/index.php')) {
    return;
  }
  
  mutations.forEach(function(mutation) {
    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
      // Check if any added nodes contain abantecart_category elements
      const hasAbanteCartItems = Array.from(mutation.addedNodes).some(node => {
        return node.nodeType === 1 && (
          node.classList?.contains('abantecart_category') ||
          node.querySelector?.('.abantecart_category')
        );
      });
      
      if (hasAbanteCartItems) {
        setTimeout(addGlobalClickHandlers, 100);
      }
    }
  });
});

// Start observing
observer.observe(document.body, {
  childList: true,
  subtree: true
});

// Also run initially and periodically to catch any missed items
document.addEventListener('DOMContentLoaded', function() {
  // Skip if we're on an individual project page
  if (window.location.pathname.includes('/projects/') && 
      window.location.pathname !== '/projects/' && 
      !window.location.pathname.endsWith('/projects/index.php')) {
    return;
  }
  
  setTimeout(addGlobalClickHandlers, 1000);
  setInterval(addGlobalClickHandlers, 3000);
});