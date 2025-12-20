// Home Page Search Functionality
// Enhanced search with autocomplete and real-time results

class HomePageSearch {
  constructor() {
    this.searchInput = null;
    this.searchResults = null;
    this.isInitialized = false;
    this.debounceTimer = null;
    this.init();
  }
  
  init() {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.setupSearch());
    } else {
      this.setupSearch();
    }
  }
  
  setupSearch() {
    this.searchInput = document.querySelector('.search-input');
    if (!this.searchInput) return;
    
    this.createSearchResultsContainer();
    this.bindEvents();
    this.isInitialized = true;
  }
  
  createSearchResultsContainer() {
    // Create search results dropdown
    const searchContainer = this.searchInput.closest('.search-box');
    if (!searchContainer) return;
    
    this.searchResults = document.createElement('div');
    this.searchResults.className = 'search-results-dropdown';
    this.searchResults.innerHTML = `
      <div class="search-results-header">
        <span class="results-count">Search Results</span>
        <button class="close-search" aria-label="Close search results">&times;</button>
      </div>
      <div class="search-results-content">
        <div class="search-categories">
          <div class="search-category" data-category="all">
            <h4>All Projects</h4>
            <div class="category-results" id="all-results"></div>
          </div>
          <div class="search-category" data-category="popular">
            <h4>Popular Projects</h4>
            <div class="category-results" id="popular-results"></div>
          </div>
        </div>
        <div class="search-actions">
          <a href="/projects/" class="view-all-link">View All Projects →</a>
        </div>
      </div>
    `;
    
    searchContainer.appendChild(this.searchResults);
    this.addSearchStyles();
  }
  
  addSearchStyles() {
    if (document.getElementById('home-search-styles')) return;
    
    const styles = document.createElement('style');
    styles.id = 'home-search-styles';
    styles.textContent = `
      .search-box {
        position: relative;
      }
      
      .search-results-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        max-height: 500px;
        overflow: hidden;
        display: none;
        margin-top: 4px;
      }
      
      .search-results-dropdown.show {
        display: block;
      }
      
      .search-results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
      }
      
      .results-count {
        font-weight: 600;
        color: #2c5aa0;
        font-size: 14px;
      }
      
      .close-search {
        background: none;
        border: none;
        font-size: 18px;
        color: #666;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      
      .close-search:hover {
        color: #2c5aa0;
      }
      
      .search-results-content {
        max-height: 400px;
        overflow-y: auto;
        padding: 16px;
      }
      
      .search-category {
        margin-bottom: 20px;
      }
      
      .search-category h4 {
        margin: 0 0 12px 0;
        color: #2c5aa0;
        font-size: 16px;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 4px;
      }
      
      .category-results {
        display: grid;
        gap: 8px;
      }
      
      .search-result-item {
        display: flex;
        align-items: center;
        padding: 12px;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
      }
      
      .search-result-item:hover {
        background: #f8f9fa;
        border-color: #2c5aa0;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(44,90,160,0.1);
      }
      
      .result-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #2c5aa0, #4a90e2);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 12px;
        margin-right: 12px;
        flex-shrink: 0;
      }
      
      .result-content {
        flex: 1;
      }
      
      .result-title {
        font-weight: 600;
        color: #2c5aa0;
        margin-bottom: 4px;
        font-size: 14px;
      }
      
      .result-description {
        color: #666;
        font-size: 12px;
        line-height: 1.4;
        margin-bottom: 4px;
      }
      
      .result-meta {
        display: flex;
        gap: 8px;
        font-size: 11px;
      }
      
      .result-grade {
        background: #e9ecef;
        color: #495057;
        padding: 2px 6px;
        border-radius: 3px;
      }
      
      .result-subject {
        background: #e3f2fd;
        color: #1976d2;
        padding: 2px 6px;
        border-radius: 3px;
      }
      
      .result-difficulty {
        padding: 2px 6px;
        border-radius: 3px;
        color: white;
      }
      
      .result-difficulty.easy { background: #4caf50; }
      .result-difficulty.medium { background: #ff9800; }
      .result-difficulty.hard { background: #f44336; }
      
      .search-actions {
        padding-top: 16px;
        border-top: 1px solid #eee;
        text-align: center;
      }
      
      .view-all-link {
        display: inline-block;
        background: #2c5aa0;
        color: white;
        padding: 12px 24px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.2s ease;
      }
      
      .view-all-link:hover {
        background: #1e3f73;
      }
      
      .no-search-results {
        text-align: center;
        padding: 40px 20px;
        color: #666;
      }
      
      .no-search-results h4 {
        margin-bottom: 8px;
        color: #2c5aa0;
      }
      
      @media (max-width: 768px) {
        .search-results-dropdown {
          left: -16px;
          right: -16px;
          border-radius: 0;
          border-left: none;
          border-right: none;
        }
        
        .search-results-content {
          padding: 12px;
        }
        
        .search-result-item {
          padding: 10px;
        }
        
        .result-icon {
          width: 28px;
          height: 28px;
          margin-right: 10px;
        }
      }
    `;
    
    document.head.appendChild(styles);
  }
  
  bindEvents() {
    // Search input events
    this.searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
    this.searchInput.addEventListener('focus', () => this.showSearchResults());
    
    // Multiple event listeners for better mobile compatibility
    this.searchInput.addEventListener('keydown', (e) => this.handleKeyboard(e));
    this.searchInput.addEventListener('keyup', (e) => this.handleKeyboard(e));
    this.searchInput.addEventListener('keypress', (e) => this.handleKeyboard(e));
    
    // Touch device specific events
    this.searchInput.addEventListener('search', (e) => {
      // This fires when user taps the search button on virtual keyboard
      this.handleEnterKey();
    });
    
    // Form submission handler for mobile
    const searchForm = this.searchInput.closest('form, .search-form');
    if (searchForm) {
      searchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        this.handleEnterKey();
      });
    }
    
    // Close search events
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.search-box')) {
        this.hideSearchResults();
      }
    });
    
    // Close button
    this.searchResults.addEventListener('click', (e) => {
      if (e.target.classList.contains('close-search')) {
        this.hideSearchResults();
      }
    });
  }
  
  handleSearch(query) {
    clearTimeout(this.debounceTimer);
    
    if (query.length < 2) {
      this.showDefaultResults();
      return;
    }
    
    this.debounceTimer = setTimeout(() => {
      this.performSearch(query);
    }, 300);
  }
  
  performSearch(query) {
    const results = searchUtils.searchProjects(query);
    const popularResults = results.filter(p => p.popular).slice(0, 3);
    const allResults = results.slice(0, 8);
    
    this.displayResults(allResults, popularResults, query);
    this.showSearchResults();
  }
  
  showDefaultResults() {
    const popularProjects = searchUtils.getPopularProjects(4);
    const randomProjects = searchUtils.getRandomProjects(4);
    
    this.displayResults(randomProjects, popularProjects, '');
  }
  
  displayResults(allResults, popularResults, query) {
    const allContainer = document.getElementById('all-results');
    const popularContainer = document.getElementById('popular-results');
    
    if (!allContainer || !popularContainer) return;
    
    // Update results count
    const countElement = this.searchResults.querySelector('.results-count');
    if (query) {
      countElement.textContent = `${allResults.length} results for "${query}"`;
    } else {
      countElement.textContent = 'Popular & Random Projects';
    }
    
    // Display all results
    if (allResults.length > 0) {
      allContainer.innerHTML = allResults.map(project => this.createResultItem(project)).join('');
    } else {
      allContainer.innerHTML = `
        <div class="no-search-results">
          <h4>No projects found</h4>
          <p>Try different keywords or browse all projects</p>
        </div>
      `;
    }
    
    // Display popular results
    if (popularResults.length > 0) {
      popularContainer.innerHTML = popularResults.map(project => this.createResultItem(project)).join('');
      popularContainer.parentElement.style.display = 'block';
    } else {
      popularContainer.parentElement.style.display = 'none';
    }
  }
  
  createResultItem(project) {
    const gradeIcon = this.getGradeIcon(project.category);
    const subjectIcon = this.getSubjectIcon(project.subject);
    
    return `
      <a href="${project.url}" class="search-result-item">
        <div class="result-icon">${gradeIcon}</div>
        <div class="result-content">
          <div class="result-title">${project.title}</div>
          <div class="result-description">${project.description}</div>
          <div class="result-meta">
            <span class="result-grade">${project.grade}</span>
            <span class="result-subject">${project.subject}</span>
            <span class="result-difficulty ${project.difficulty}">${project.difficulty}</span>
          </div>
        </div>
      </a>
    `;
  }
  
  getGradeIcon(category) {
    const icons = {
      primary: 'K-4',
      elementary: '4-6',
      intermediate: '7-8',
      senior: '9-12'
    };
    return icons[category] || '?';
  }
  
  getSubjectIcon(subject) {
    // Use text-based icons instead of emojis for compatibility
    const icons = {
      physics: 'PHY',
      chemistry: 'CHE',
      biology: 'BIO',
      'earth-science': 'GEO',
      space: 'SPA',
      engineering: 'ENG'
    };
    return icons[subject] || 'SCI';
  }
  
  showSearchResults() {
    if (!this.searchResults) return;
    
    if (!this.searchInput.value.trim()) {
      this.showDefaultResults();
    }
    
    this.searchResults.classList.add('show');
  }
  
  hideSearchResults() {
    if (this.searchResults) {
      this.searchResults.classList.remove('show');
    }
  }
  
  handleKeyboard(e) {
    if (e.key === 'Escape') {
      this.hideSearchResults();
      this.searchInput.blur();
      return;
    }
    
    if (e.key === 'Enter' || e.keyCode === 13) {
      e.preventDefault();
      e.stopPropagation();
      this.handleEnterKey();
      return;
    }
  }
  
  handleEnterKey() {
    const query = this.searchInput.value.trim();
    if (query) {
      // Hide results first
      this.hideSearchResults();
      // Navigate to search results
      window.location.href = `/projects/?search=${encodeURIComponent(query)}`;
    }
  }
}

// Initialize when the script loads
let homeSearch = null;

// Initialize search functionality
function initHomeSearch() {
  if (typeof searchUtils !== 'undefined') {
    homeSearch = new HomePageSearch();
  } else {
    // Retry after a short delay if searchUtils isn't loaded yet
    setTimeout(initHomeSearch, 100);
  }
}

// Start initialization
initHomeSearch();