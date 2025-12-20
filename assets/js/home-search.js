/**
 * Búsqueda de Clases - Clase de Ciencia
 * Adaptado de FreeScienceProject para estructura de ClaseDeCiencia
 * Consulta API dinámica en lugar de JSON estático
 */

class ClaseDeCienciaSearch {
  constructor() {
    this.searchInput = null;
    this.searchResults = null;
    this.isInitialized = false;
    this.debounceTimer = null;
    this.proyectosData = null;
    this.isLoading = false;
    this.init();
  }
  
  init() {
    console.log('🔍 [ClaseDeCienciaSearch] Inicializando búsqueda...');
    
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.setupSearch());
    } else {
      this.setupSearch();
    }
  }
  
  async setupSearch() {
    this.searchInput = document.querySelector('.search-input');
    if (!this.searchInput) {
      console.log('⚠️ [ClaseDeCienciaSearch] No se encontró .search-input');
      return;
    }
    
    // Cargar datos de proyectos desde API
    await this.loadProyectosData();
    
    this.createSearchResultsContainer();
    this.bindEvents();
    this.isInitialized = true;
    
    console.log('✅ [ClaseDeCienciaSearch] Búsqueda inicializada con', this.proyectosData?.length || 0, 'proyectos');
  }
  
  async loadProyectosData() {
    if (this.isLoading) return;
    
    this.isLoading = true;
    console.log('📡 [ClaseDeCienciaSearch] Cargando datos desde API...');
    
    try {
      const response = await fetch('/api/clases-data.php', {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
      });
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }
      
      const data = await response.json();
      
      if (data.success && Array.isArray(data.proyectos)) {
        this.proyectosData = data.proyectos;
        console.log('✅ [ClaseDeCienciaSearch] API response:', data.total, 'proyectos');
      } else {
        console.log('❌ [ClaseDeCienciaSearch] API error:', data);
        this.proyectosData = [];
      }
    } catch (error) {
      console.log('❌ [ClaseDeCienciaSearch] Error cargando API:', error.message);
      this.proyectosData = [];
    } finally {
      this.isLoading = false;
    }
  }
  
  createSearchResultsContainer() {
    const searchContainer = this.searchInput.closest('.search-box');
    if (!searchContainer) return;
    
    this.searchResults = document.createElement('div');
    this.searchResults.className = 'search-results-dropdown';
    this.searchResults.innerHTML = `
      <div class="search-results-header">
        <span class="results-count">Resultados de búsqueda</span>
        <button class="close-search" aria-label="Cerrar resultados">&times;</button>
      </div>
      <div class="search-results-content">
        <div class="search-categories">
          <div class="search-category" data-category="all">
            <h4>Todas las Clases</h4>
            <div class="category-results" id="all-results"></div>
          </div>
          <div class="search-category" data-category="destacadas">
            <h4>⭐ Clases Destacadas</h4>
            <div class="category-results" id="destacadas-results"></div>
          </div>
        </div>
        <div class="search-actions">
          <a href="/catalogo.php" class="view-all-link">Ver Catálogo Completo →</a>
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
        border: 1px solid rgba(31, 60, 136, 0.15);
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(31, 60, 136, 0.12);
        z-index: 1000;
        max-height: 520px;
        overflow: hidden;
        display: none;
        margin-top: 8px;
      }
      
      .search-results-dropdown.show {
        display: block;
        animation: slideDown 0.2s ease-out;
      }
      
      @keyframes slideDown {
        from {
          opacity: 0;
          transform: translateY(-8px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      
      .search-results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 20px;
        border-bottom: 1px solid rgba(31, 60, 136, 0.1);
        background: linear-gradient(135deg, #f8f9fc 0%, #fff 100%);
      }
      
      .results-count {
        font-weight: 700;
        color: var(--color-primary, #1f3c88);
        font-size: 14px;
      }
      
      .close-search {
        background: none;
        border: none;
        font-size: 22px;
        color: #666;
        cursor: pointer;
        padding: 0;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
      }
      
      .close-search:hover {
        background: rgba(31, 60, 136, 0.08);
        color: var(--color-primary, #1f3c88);
      }
      
      .search-results-content {
        max-height: 440px;
        overflow-y: auto;
        padding: 16px;
      }
      
      .search-results-content::-webkit-scrollbar {
        width: 6px;
      }
      
      .search-results-content::-webkit-scrollbar-thumb {
        background: rgba(31, 60, 136, 0.2);
        border-radius: 3px;
      }
      
      .search-category {
        margin-bottom: 20px;
      }
      
      .search-category:last-of-type {
        margin-bottom: 0;
      }
      
      .search-category h4 {
        margin: 0 0 12px 0;
        color: var(--color-primary, #1f3c88);
        font-size: 15px;
        font-weight: 700;
        border-bottom: 2px solid rgba(31, 60, 136, 0.1);
        padding-bottom: 6px;
      }
      
      .category-results {
        display: grid;
        gap: 10px;
      }
      
      .search-result-item {
        display: flex;
        align-items: center;
        padding: 14px 16px;
        border: 1px solid rgba(31, 60, 136, 0.12);
        border-radius: 8px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        background: white;
      }
      
      .search-result-item:hover {
        background: linear-gradient(135deg, #f8f9fc 0%, #fff 100%);
        border-color: var(--color-primary, #1f3c88);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(31, 60, 136, 0.15);
      }
      
      .result-icon {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, var(--color-primary, #1f3c88), #3a5ba8);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 13px;
        margin-right: 14px;
        flex-shrink: 0;
      }
      
      .result-icon.featured {
        background: linear-gradient(135deg, #f9a825, #f57c00);
      }
      
      .result-content {
        flex: 1;
        min-width: 0;
      }
      
      .result-title {
        font-weight: 700;
        color: var(--color-primary, #1f3c88);
        margin-bottom: 6px;
        font-size: 14px;
        line-height: 1.3;
      }
      
      .result-description {
        color: #666;
        font-size: 12px;
        line-height: 1.5;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }
      
      .result-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 11px;
      }
      
      .result-badge {
        padding: 3px 8px;
        border-radius: 4px;
        white-space: nowrap;
      }
      
      .result-ciclo {
        background: rgba(31, 60, 136, 0.1);
        color: var(--color-primary, #1f3c88);
        font-weight: 600;
      }
      
      .result-grados {
        background: #e3f2fd;
        color: #1976d2;
      }
      
      .result-subject {
        background: rgba(249, 168, 37, 0.12);
        color: #f57c00;
      }
      
      .result-difficulty {
        padding: 3px 8px;
        border-radius: 4px;
        color: white;
        font-weight: 600;
      }
      
      .result-difficulty.facil { background: #4caf50; }
      .result-difficulty.media { background: #ff9800; }
      .result-difficulty.dificil { background: #f44336; }
      
      .result-duration {
        background: #f5f5f5;
        color: #666;
      }
      
      .search-actions {
        padding-top: 16px;
        border-top: 1px solid rgba(31, 60, 136, 0.1);
        text-align: center;
        margin-top: 12px;
      }
      
      .view-all-link {
        display: inline-block;
        background: linear-gradient(135deg, var(--color-primary, #1f3c88), #3a5ba8);
        color: white;
        padding: 12px 28px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s ease;
      }
      
      .view-all-link:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(31, 60, 136, 0.3);
      }
      
      .no-search-results {
        text-align: center;
        padding: 48px 20px;
        color: #666;
      }
      
      .no-search-results h4 {
        margin-bottom: 12px;
        color: var(--color-primary, #1f3c88);
        font-size: 18px;
      }
      
      .no-search-results p {
        font-size: 14px;
        line-height: 1.6;
      }
      
      @media (max-width: 768px) {
        .search-results-dropdown {
          left: 0;
          right: 0;
          border-radius: 12px;
          max-height: 60vh;
        }
        
        .search-results-content {
          padding: 12px;
          max-height: calc(60vh - 60px);
        }
        
        .search-result-item {
          padding: 12px;
        }
        
        .result-icon {
          width: 36px;
          height: 36px;
          font-size: 11px;
          margin-right: 12px;
        }
        
        .result-title {
          font-size: 13px;
        }
        
        .result-description {
          font-size: 11px;
          -webkit-line-clamp: 1;
        }
      }
    `;
    
    document.head.appendChild(styles);
  }
  
  bindEvents() {
    // Input events
    this.searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
    this.searchInput.addEventListener('focus', () => this.showSearchResults());
    
    // Keyboard events
    this.searchInput.addEventListener('keydown', (e) => this.handleKeyboard(e));
    
    // Form submission (móviles)
    const searchForm = this.searchInput.closest('form, .search-form');
    if (searchForm) {
      searchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        this.handleEnterKey();
      });
    }
    
    // Close events
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.search-box')) {
        this.hideSearchResults();
      }
    });
    
    // Close button
    if (this.searchResults) {
      this.searchResults.addEventListener('click', (e) => {
        if (e.target.classList.contains('close-search')) {
          this.hideSearchResults();
        }
      });
    }
  }
  
  handleSearch(query) {
    clearTimeout(this.debounceTimer);
    
    if (query.length < 2) {
      if (this.searchResults) {
        this.hideSearchResults();
      }
      return;
    }
    
    this.debounceTimer = setTimeout(() => {
      console.log('🔍 [ClaseDeCienciaSearch] Buscando:', query);
      this.performSearch(query);
    }, 300);
  }
  
  performSearch(query) {
    if (!this.proyectosData || this.proyectosData.length === 0) {
      console.log('⚠️ [ClaseDeCienciaSearch] No hay datos para buscar');
      this.displayNoResults('Cargando datos...');
      this.showSearchResults();
      return;
    }
    
    const queryLower = query.toLowerCase().trim();
    const results = this.proyectosData.filter(proyecto => {
      return proyecto.search_text && proyecto.search_text.includes(queryLower);
    });
    
    console.log('✅ [ClaseDeCienciaSearch] Encontrados:', results.length, 'resultados para:', query);
    
    const destacadas = results.filter(p => p.featured).slice(0, 3);
    const todas = results.slice(0, 8);
    
    this.displayResults(todas, destacadas, query);
    this.showSearchResults();
  }
  
  showDefaultResults() {
    if (!this.proyectosData || this.proyectosData.length === 0) {
      return;
    }
    
    const destacadas = this.proyectosData.filter(p => p.featured).slice(0, 3);
    const todas = this.proyectosData.slice(0, 6);
    
    this.displayResults(todas, destacadas, '');
  }
  
  displayResults(allResults, destacadasResults, query) {
    const allContainer = document.getElementById('all-results');
    const destacadasContainer = document.getElementById('destacadas-results');
    
    console.log('📊 [ClaseDeCienciaSearch] displayResults - containers:', {
      allContainer: !!allContainer,
      destacadasContainer: !!destacadasContainer,
      resultCount: allResults.length
    });
    
    if (!allContainer || !destacadasContainer) {
      console.log('❌ [ClaseDeCienciaSearch] Containers no encontrados');
      return;
    }
    
    // Update results count
    const countElement = this.searchResults.querySelector('.results-count');
    if (query) {
      countElement.textContent = `${allResults.length} resultado${allResults.length !== 1 ? 's' : ''} para "${query}"`;
    } else {
      countElement.textContent = 'Explora nuestras clases';
    }
    
    // Display all results
    if (allResults.length > 0) {
      allContainer.innerHTML = allResults.map(proyecto => this.createResultItem(proyecto)).join('');
      console.log('✅ [ClaseDeCienciaSearch] Resultados renderizados:', allResults.length);
    } else {
      this.displayNoResults('No se encontraron clases para tu búsqueda');
    }
    
    // Display destacadas results
    if (destacadasResults.length > 0 && query) {
      destacadasContainer.innerHTML = destacadasResults.map(proyecto => this.createResultItem(proyecto)).join('');
      destacadasContainer.parentElement.style.display = 'block';
    } else {
      destacadasContainer.parentElement.style.display = 'none';
    }
  }
  
  displayNoResults(message) {
    const allContainer = document.getElementById('all-results');
    if (!allContainer) return;
    
    allContainer.innerHTML = `
      <div class="no-search-results">
        <h4>${message}</h4>
        <p>Intenta con otras palabras clave o explora el catálogo completo</p>
      </div>
    `;
    
    // Hide destacadas section
    const destacadasContainer = document.getElementById('destacadas-results');
    if (destacadasContainer) {
      destacadasContainer.parentElement.style.display = 'none';
    }
  }
  
  createResultItem(proyecto) {
    const cicloIcon = `C${proyecto.ciclo}`;
    const iconClass = proyecto.featured ? 'featured' : '';
    const difficultyClass = proyecto.difficulty.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    
    return `
      <a href="${proyecto.url}" class="search-result-item">
        <div class="result-icon ${iconClass}">${cicloIcon}</div>
        <div class="result-content">
          <div class="result-title">${this.escapeHtml(proyecto.title)}</div>
          <div class="result-description">${this.escapeHtml(proyecto.description)}</div>
          <div class="result-meta">
            <span class="result-badge result-ciclo">${this.escapeHtml(proyecto.ciclo_nombre)}</span>
            ${proyecto.grados ? `<span class="result-badge result-grados">${this.escapeHtml(proyecto.grados)}</span>` : ''}
            ${proyecto.subject ? `<span class="result-badge result-subject">${this.escapeHtml(proyecto.subject)}</span>` : ''}
            <span class="result-difficulty ${difficultyClass}">${this.escapeHtml(proyecto.difficulty)}</span>
            ${proyecto.duration ? `<span class="result-badge result-duration">⏱ ${proyecto.duration}</span>` : ''}
          </div>
        </div>
      </a>
    `;
  }
  
  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
  }
  
  showSearchResults() {
    if (!this.searchResults) {
      console.log('⚠️ [ClaseDeCienciaSearch] showSearchResults - no hay dropdown');
      return;
    }
    
    console.log('👁️ [ClaseDeCienciaSearch] Mostrando dropdown');
    this.searchResults.classList.add('show');
    
    // Verificar que se aplicó la clase
    setTimeout(() => {
      const isVisible = this.searchResults.classList.contains('show');
      const display = window.getComputedStyle(this.searchResults).display;
      console.log('👁️ [ClaseDeCienciaSearch] Estado dropdown:', { isVisible, display });
    }, 50);
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
      this.hideSearchResults();
      window.location.href = `/catalogo.php?busqueda=${encodeURIComponent(query)}`;
    }
  }
}

// Inicialización automática
let claseDeCienciaSearch = null;

function initClaseDeCienciaSearch() {
  if (!claseDeCienciaSearch) {
    claseDeCienciaSearch = new ClaseDeCienciaSearch();
  }
}

// Auto-iniciar
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initClaseDeCienciaSearch);
} else {
  initClaseDeCienciaSearch();
}
