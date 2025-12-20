// Enhanced Projects Page Search & Filter
// Comprehensive project management with all category integration

class ProjectsPageManager {
  constructor() {
    this.allProjects = [];
    this.filteredProjects = [];
    this.currentFilters = {
      search: '',
      subject: [],
      difficulty: [],
      materials: [],
      category: [],
      popular: false
    };
    this.currentSort = 'name';
    this.init();
  }
  
  init() {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.setupProjectsPage());
    } else {
      this.setupProjectsPage();
    }
  }
  
  setupProjectsPage() {
    this.loadAllProjects();
    this.setupElements();
    this.bindEvents();
    this.loadFromURL(); // This will now trigger updateFilters() if there's a search param
    // displayProjects() will be called by updateFilters() if there's a search param
    // If no search param, display all projects
    if (!this.currentFilters.search) {
      this.displayProjects();
    }
  }
  
  loadAllProjects() {
    // Load all projects from all categories using centralized data
    this.allProjects = searchUtils.getAllProjects();
    this.filteredProjects = [...this.allProjects];
  }
  
  setupElements() {
    this.searchInput = document.getElementById('search-projects');
    this.projectsContainer = document.getElementById('projects-container');
    this.resultsCount = document.getElementById('results-count');
    this.sortSelect = document.getElementById('sort-select');
    this.noResults = document.getElementById('no-results');
    
    // Setup filter checkboxes
    this.setupFilterCheckboxes();
  }
  
  setupFilterCheckboxes() {
    // Subject filters
    const subjectFilters = document.querySelectorAll('input[name="subject"]');
    subjectFilters.forEach(checkbox => {
      checkbox.addEventListener('change', () => this.updateFilters());
    });
    
    // Grade/Category filters  
    const categoryFilters = document.querySelectorAll('input[name="grade"]');
    categoryFilters.forEach(checkbox => {
      checkbox.addEventListener('change', () => this.updateFilters());
    });
    
    // Materials filters
    const materialsFilters = document.querySelectorAll('input[name="materials"]');
    materialsFilters.forEach(checkbox => {
      checkbox.addEventListener('change', () => this.updateFilters());
    });
    
    // Quick filters
    const quickFilters = document.querySelectorAll('input[name="quick"]');
    quickFilters.forEach(checkbox => {
      checkbox.addEventListener('change', () => this.updateFilters());
    });
  }
  
  bindEvents() {
    // Search input
    if (this.searchInput) {
      this.searchInput.addEventListener('input', (e) => {
        this.currentFilters.search = e.target.value;
        this.updateFilters();
      });
    }
    
    // Sort select
    if (this.sortSelect) {
      this.sortSelect.addEventListener('change', (e) => {
        this.currentSort = e.target.value;
        this.displayProjects();
      });
    }
    
    // Clear filters button
    const clearButton = document.querySelector('.clear-filters');
    if (clearButton) {
      clearButton.addEventListener('click', () => this.clearAllFilters());
    }
  }
  
  loadFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('search');
    
    if (searchParam) {
      this.currentFilters.search = searchParam;
      if (this.searchInput) {
        this.searchInput.value = searchParam;
        // Add visual feedback that search is active
        this.searchInput.classList.add('search-active');
        // Focus the search input to show user where the search term is
        setTimeout(() => {
          this.searchInput.focus();
          this.searchInput.setSelectionRange(this.searchInput.value.length, this.searchInput.value.length);
        }, 100);
      }
      // Trigger filtering to show results immediately
      this.updateFilters();
    }
    
    // Also load other URL parameters if present
    const subjectParam = urlParams.get('subject');
    if (subjectParam) {
      const subjects = subjectParam.split(',');
      subjects.forEach(subject => {
        const checkbox = document.querySelector(`input[name="subject"][value="${subject}"]`);
        if (checkbox) checkbox.checked = true;
      });
    }
    
    const categoryParam = urlParams.get('category');
    if (categoryParam) {
      const categories = categoryParam.split(',');
      categories.forEach(category => {
        const checkbox = document.querySelector(`input[name="grade"][value="${category}"]`);
        if (checkbox) checkbox.checked = true;
      });
    }
  }
  
  updateFilters() {
    // Collect all active filters from checkboxes
    this.currentFilters.subject = this.getCheckedValues('subject');
    this.currentFilters.materials = this.getCheckedValues('materials');
    this.currentFilters.category = this.getCheckedValues('grade'); // Map grade to category
    this.currentFilters.popular = document.querySelector('input[value="popular"]')?.checked || false;
    
    // Apply search filter first
    let results = this.allProjects;
    if (this.currentFilters.search) {
      results = searchUtils.searchProjects(this.currentFilters.search);
    }
    
    // Apply other filters using centralized filter function
    this.filteredProjects = searchUtils.filterProjects(results, this.currentFilters);
    
    this.displayProjects();
    this.updateURL();
  }
  
  getCheckedValues(name) {
    const checkboxes = document.querySelectorAll(`input[name="${name}"]:checked`);
    return Array.from(checkboxes).map(cb => cb.value);
  }
  
  displayProjects() {
    if (!this.projectsContainer) return;
    
    // Sort projects using centralized sort function
    const sortedProjects = searchUtils.sortProjects(this.filteredProjects, this.currentSort);
    
    // Update results count
    if (this.resultsCount) {
      this.resultsCount.textContent = `Showing ${sortedProjects.length} projects`;
    }
    
    // Display projects or no results message
    if (sortedProjects.length === 0) {
      this.projectsContainer.style.display = 'none';
      if (this.noResults) {
        this.noResults.style.display = 'block';
      }
      return;
    }
    
    this.projectsContainer.style.display = 'grid';
    if (this.noResults) {
      this.noResults.style.display = 'none';
    }
    
    // Render project cards
    this.projectsContainer.innerHTML = sortedProjects.map(project => this.createProjectCard(project)).join('');
  }
  
  createProjectCard(project) {
    const popularBadge = project.popular ? '<div class="popular-badge">⭐ Popular</div>' : '';
    const gradeColors = {
      primary: '#4caf50',
      elementary: '#2196f3', 
      intermediate: '#ff9800',
      senior: '#9c27b0'
    };
    
    return `
      <div class="project-card" data-subject="${project.subject}" data-difficulty="${project.difficulty}" data-category="${project.category}">
        ${popularBadge}
        <div class="project-header">
          <div class="project-icon" style="background: ${gradeColors[project.category] || '#666'}">
            ${this.getGradeIcon(project.category)}
          </div>
          <div class="project-meta">
            <span class="difficulty-badge ${project.difficulty}">${project.difficulty}</span>
            <span class="subject-badge">${this.formatSubject(project.subject)}</span>
          </div>
        </div>
        <div class="project-content">
          <h3 class="project-title">${project.title}</h3>
          <p class="project-description">${project.description}</p>
          <div class="project-tags">
            <span class="tag materials-tag">${project.materials || 'various'}</span>
            <span class="tag grade-tag">${this.formatCategory(project.category)}</span>
          </div>
        </div>
        <div class="project-actions">
          <a href="${project.url}" class="btn-primary">Start Project</a>
        </div>
      </div>
    `;
  }
  
  formatSubject(subject) {
    const subjects = {
      'physics': 'Physics',
      'chemistry': 'Chemistry', 
      'biology': 'Biology',
      'earth-science': 'Earth Science',
      'earth': 'Earth Science',
      'space': 'Space Science',
      'engineering': 'Engineering'
    };
    return subjects[subject] || subject.charAt(0).toUpperCase() + subject.slice(1);
  }
  
  formatCategory(category) {
    const categories = {
      'primary': 'K-4',
      'elementary': '4-6', 
      'intermediate': '7-8',
      'senior': '9-12'
    };
    return categories[category] || category;
  }
  
  getGradeIcon(category) {
    const icons = {
      primary: 'K-4',
      elementary: '4-6',
      intermediate: '7-8',
      senior: '9-12'
    };
    return icons[category] || 'K-12';
  }
  
  clearAllFilters() {
    // Clear search input
    if (this.searchInput) {
      this.searchInput.value = '';
    }
    
    // Uncheck all checkboxes
    const allCheckboxes = document.querySelectorAll('.filter-panel input[type="checkbox"]');
    allCheckboxes.forEach(checkbox => {
      checkbox.checked = false;
    });
    
    // Reset sort to default
    if (this.sortSelect) {
      this.sortSelect.value = 'name';
    }
    
    // Reset filters object
    this.currentFilters = {
      search: '',
      subject: [],
      difficulty: [],
      materials: [],
      category: [],
      popular: false
    };
    this.currentSort = 'name';
    
    // Refresh display
    this.filteredProjects = [...this.allProjects];
    this.displayProjects();
    this.updateURL();
  }
  
  updateURL() {
    const params = new URLSearchParams();
    
    if (this.currentFilters.search) {
      params.set('search', this.currentFilters.search);
    }
    
    if (this.currentFilters.subject.length > 0) {
      params.set('subject', this.currentFilters.subject.join(','));
    }
    
    if (this.currentFilters.category.length > 0) {
      params.set('category', this.currentFilters.category.join(','));
    }
    
    const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.history.replaceState({}, '', newURL);
  }
}

// Global functions for backward compatibility and external access
function toggleMobileFilters() {
  const filterPanel = document.querySelector('.filter-panel');
  if (filterPanel) {
    filterPanel.classList.toggle('mobile-open');
  }
}

function clearAllFilters() {
  if (window.projectsManager) {
    window.projectsManager.clearAllFilters();
  }
}

function sortProjects() {
  if (window.projectsManager) {
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
      window.projectsManager.currentSort = sortSelect.value;
      window.projectsManager.displayProjects();
    }
  }
}

// Initialize projects page manager
function initProjectsPage() {
  // Ensure searchUtils is available from science-projects-data.js
  if (typeof searchUtils !== 'undefined') {
    window.projectsManager = new ProjectsPageManager();
  } else {
    // Retry after a short delay if searchUtils isn't loaded yet
    setTimeout(initProjectsPage, 100);
  }
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initProjectsPage);
} else {
  initProjectsPage();
}