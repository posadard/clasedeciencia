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
    this.proyectosData = null; // clases
    this.kitsData = null;
    this.componentesData = null;
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
    console.log('📡 [ClaseDeCienciaSearch] Cargando datos (clases/kits/componentes) desde API...');
    
    try {
      // Cache-busting to ensure fresh data after content updates
      const ver = (typeof window !== 'undefined' && window.SEARCH_VERSION) ? `&v=${encodeURIComponent(window.SEARCH_VERSION)}` : '';
      const t = `t=${Date.now()}`;
      const urls = {
        clases: `/api/clases-data.php?${t}${ver}`,
        kits: `/api/kits-data.php?${t}${ver}`,
        componentes: `/api/componentes-data.php?${t}${ver}`
      };

      const [resC, resK, resM] = await Promise.all([
        fetch(urls.clases, { method: 'GET', headers: { 'Accept': 'application/json' }, cache: 'no-store' }),
        fetch(urls.kits, { method: 'GET', headers: { 'Accept': 'application/json' }, cache: 'no-store' }),
        fetch(urls.componentes, { method: 'GET', headers: { 'Accept': 'application/json' }, cache: 'no-store' })
      ]);

      console.log('📡 [ClaseDeCienciaSearch] Status:', { clases: resC.status, kits: resK.status, componentes: resM.status });

      const [dataC, dataK, dataM] = await Promise.all([
        resC.ok ? resC.json() : Promise.resolve({ success: false, proyectos: [], error: `HTTP ${resC.status}` }),
        resK.ok ? resK.json() : Promise.resolve({ success: false, kits: [], error: `HTTP ${resK.status}` }),
        resM.ok ? resM.json() : Promise.resolve({ success: false, componentes: [], error: `HTTP ${resM.status}` })
      ]);

      this.proyectosData = (dataC && dataC.success && Array.isArray(dataC.proyectos)) ? dataC.proyectos : [];
      this.kitsData = (dataK && dataK.success && Array.isArray(dataK.kits)) ? dataK.kits : [];
      this.componentesData = (dataM && dataM.success && Array.isArray(dataM.componentes)) ? dataM.componentes : [];

      console.log('✅ [ClaseDeCienciaSearch] Cargados:', {
        clases: this.proyectosData?.length || 0,
        kits: this.kitsData?.length || 0,
        componentes: this.componentesData?.length || 0
      });
    } catch (error) {
      console.log('❌ [ClaseDeCienciaSearch] Error cargando API:', error.message);
      this.proyectosData = [];
      this.kitsData = [];
      this.componentesData = [];
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
          <div class="search-category" data-category="clases">
            <h4>Clases</h4>
            <div class="category-results" id="clases-results"></div>
          </div>
          <div class="search-category" data-category="kits">
            <h4>Kits</h4>
            <div class="category-results" id="kits-results"></div>
          </div>
          <div class="search-category" data-category="componentes">
            <h4>Componentes</h4>
            <div class="category-results" id="componentes-results"></div>
          </div>
        </div>
        <div class="search-actions">
          <a href="/clases" class="view-all-link">Ver Catálogo Completo →</a>
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
    
    // Normalizar query (quitar acentos)
    const normalizeQuery = (text) => {
      return text
        .toLowerCase()
        .trim()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '') // Quitar diacríticos
        .replace(/[áàäâ]/g, 'a')
        .replace(/[éèëê]/g, 'e')
        .replace(/[íìïî]/g, 'i')
        .replace(/[óòöô]/g, 'o')
        .replace(/[úùüû]/g, 'u')
        .replace(/ñ/g, 'n');
    };
    
    const queryNormalized = normalizeQuery(query);
    console.log('🔍 [ClaseDeCienciaSearch] Query normalizada:', queryNormalized);
    
    const match = (item) => item.search_text && item.search_text.includes(queryNormalized);

    const clases = (this.proyectosData || []).filter(match).slice(0, 6);
    const kits = (this.kitsData || []).filter(match).slice(0, 4);
    const componentes = (this.componentesData || []).filter(match).slice(0, 4);

    console.log('✅ [ClaseDeCienciaSearch] Encontrados:', { clases: clases.length, kits: kits.length, componentes: componentes.length }, 'para:', query);

    this.displayResults({ clases, kits, componentes, query });
    this.showSearchResults();
  }
  
  showDefaultResults() {
    if (!this.proyectosData) return;
    const clases = (this.proyectosData || []).slice(0, 5);
    const kits = (this.kitsData || []).slice(0, 3);
    const componentes = (this.componentesData || []).slice(0, 3);
    this.displayResults({ clases, kits, componentes, query: '' });
  }

  displayResults({ clases, kits, componentes, query }) {
    const clasesContainer = document.getElementById('clases-results');
    const kitsContainer = document.getElementById('kits-results');
    const compContainer = document.getElementById('componentes-results');

    console.log('📊 [ClaseDeCienciaSearch] displayResults - containers:', {
      clasesContainer: !!clasesContainer,
      kitsContainer: !!kitsContainer,
      compContainer: !!compContainer
    });

    if (!clasesContainer || !kitsContainer || !compContainer) {
      console.log('❌ [ClaseDeCienciaSearch] Containers no encontrados');
      return;
    }

    const total = (clases?.length || 0) + (kits?.length || 0) + (componentes?.length || 0);
    const countElement = this.searchResults.querySelector('.results-count');
    countElement.textContent = query ? `${total} resultado${total !== 1 ? 's' : ''} para "${query}"` : 'Explora clases, kits y componentes';

    // Render por categoría
    if (clases && clases.length > 0) {
      clasesContainer.innerHTML = clases.map(item => this.createResultItem(item)).join('');
      clasesContainer.parentElement.style.display = 'block';
    } else {
      clasesContainer.innerHTML = '';
      clasesContainer.parentElement.style.display = 'none';
    }

    if (kits && kits.length > 0) {
      kitsContainer.innerHTML = kits.map(item => this.createResultItem(item)).join('');
      kitsContainer.parentElement.style.display = 'block';
    } else {
      kitsContainer.innerHTML = '';
      kitsContainer.parentElement.style.display = 'none';
    }

    if (componentes && componentes.length > 0) {
      compContainer.innerHTML = componentes.map(item => this.createResultItem(item)).join('');
      compContainer.parentElement.style.display = 'block';
    } else {
      compContainer.innerHTML = '';
      compContainer.parentElement.style.display = 'none';
    }
  }
  
  displayNoResults(message) {
    const clasesContainer = document.getElementById('clases-results');
    if (!clasesContainer) return;
    
    clasesContainer.innerHTML = `
      <div class="no-search-results">
        <h4>${message}</h4>
        <p>Intenta con otras palabras clave o explora el catálogo completo</p>
      </div>
    `;
    // Ocultar otras secciones
    const kitsContainer = document.getElementById('kits-results');
    const compContainer = document.getElementById('componentes-results');
    if (kitsContainer) kitsContainer.parentElement.style.display = 'none';
    if (compContainer) compContainer.parentElement.style.display = 'none';
  }
  
  createResultItem(item) {
    const type = (item.type || 'clase').toLowerCase();
    const isClase = type === 'clase';
    const isKit = type === 'kit';
    const isComp = type === 'componente';

    let iconText = 'CL';
    if (isClase) iconText = `C${item.ciclo || ''}`.trim();
    if (isKit) iconText = 'KIT';
    if (isComp) iconText = 'CMP';

    const iconClass = item.featured ? 'featured' : '';
    const difficultyClass = (item.difficulty || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    const subjectBadge = isClase && item.subject ? `<span class="result-badge result-subject">${this.escapeHtml(item.subject)}</span>` : '';
    const gradosBadge = isClase && item.grados ? `<span class="result-badge result-grados">${this.escapeHtml(item.grados)}</span>` : '';
    const cicloBadge = isClase && item.ciclo_nombre ? `<span class="result-badge result-ciclo">${this.escapeHtml(item.ciclo_nombre)}</span>` : '';
    const diffBadge = isClase && item.difficulty ? `<span class="result-difficulty ${difficultyClass}">${this.escapeHtml(item.difficulty)}</span>` : '';
    const durationBadge = isClase && item.duration ? `<span class="result-badge result-duration">⏱ ${this.escapeHtml(item.duration)}</span>` : '';
    const kitBadge = isKit && item.codigo ? `<span class="result-badge result-duration">Código: ${this.escapeHtml(item.codigo)}</span>` : '';
    const compBadge = isComp && item.categoria ? `<span class="result-badge result-subject">${this.escapeHtml(item.categoria)}</span>` : '';

    return `
      <a href="${item.url}" class="search-result-item">
        <div class="result-icon ${iconClass}">${iconText}</div>
        <div class="result-content">
          <div class="result-title">${this.escapeHtml(item.title)}</div>
          <div class="result-description">${this.escapeHtml(item.description)}</div>
          <div class="result-meta">
            ${cicloBadge}
            ${gradosBadge}
            ${subjectBadge}
            ${diffBadge}
            ${durationBadge}
            ${kitBadge}
            ${compBadge}
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
    if (!query) return;

    // Parse intent: ciclo, área, grado, dificultad
    const normalize = (text) => {
      return (text || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9°\s]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
    };
    const qn = normalize(query);
    const tokens = qn.split(' ').filter(Boolean);
    const params = new URLSearchParams();
    params.set('busqueda', query);

    // grado: patterns like "grado 6", "6°", "sexto"
    const gradoWords = {
      'primero': 1, 'primer': 1, 'segundo': 2, 'tercero': 3, 'cuarto': 4, 'quinto': 5,
      'sexto': 6, 'septimo': 7, 'séptimo': 7, 'octavo': 8, 'noveno': 9,
      'decimo': 10, 'décimo': 10, 'once': 11, 'undecimo': 11, 'undécimo': 11
    };
    let grado = null;
    const gradoMatch = qn.match(/grado\s*(\d{1,2})/);
    if (gradoMatch) {
      grado = parseInt(gradoMatch[1], 10);
    } else {
      const degreeMatch = qn.match(/(\d{1,2})\s*°/);
      if (degreeMatch) grado = parseInt(degreeMatch[1], 10);
    }
    if (!grado) {
      for (const t of tokens) {
        if (gradoWords[t] && !isNaN(gradoWords[t])) { grado = gradoWords[t]; break; }
      }
    }
    if (grado && grado >= 1 && grado <= 11) {
      params.set('grado', String(grado));
    }

    // ciclo: "ciclo 1|2|3" or names
    const cicloMatch = qn.match(/ciclo\s*(\d)/);
    let ciclo = cicloMatch ? parseInt(cicloMatch[1], 10) : null;
    if (!ciclo) {
      if (tokens.includes('exploracion')) ciclo = 1;
      else if (tokens.includes('experimentacion')) ciclo = 2;
      else if (tokens.includes('analisis')) ciclo = 3;
    }
    if (ciclo && [1,2,3].includes(ciclo)) {
      params.set('ciclo', String(ciclo));
    }

    // dificultad: facil | medio/media/intermedio | dificil/avanzado
    let dificultad = null;
    if (tokens.includes('facil')) dificultad = 'facil';
    if (tokens.includes('medio') || tokens.includes('media') || tokens.includes('intermedio')) dificultad = 'medio';
    if (tokens.includes('dificil') || tokens.includes('avanzado')) dificultad = 'dificil';
    if (dificultad) params.set('dificultad', dificultad);

    // área: match known slugs by token
    const areaMap = {
      'fisica': 'fisica', 'quimica': 'quimica', 'biologia': 'biologia',
      'ambiental': 'ambiental', 'tecnologia': 'tecnologia'
    };
    let area = null;
    for (const t of tokens) {
      if (areaMap[t]) { area = areaMap[t]; break; }
    }
    if (area) params.set('area', area);

    console.log('🔍 [ClaseDeCienciaSearch] Intent parse:', { query, grado, ciclo, dificultad, area });
    console.log('✅ [ClaseDeCienciaSearch] Redirigiendo a resultados de búsqueda');

    this.hideSearchResults();
    // Enviar a página exclusiva de resultados
    const term = encodeURIComponent(query.trim());
    window.location.href = `/clases/buscar/${term}`;
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
