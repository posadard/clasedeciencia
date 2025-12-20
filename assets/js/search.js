document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('searchForm');
  const q = document.getElementById('q');
  const results = document.getElementById('results');
  if (!form || !q || !results) return;
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const term = q.value.trim();
    console.log('🔍 [Search] Term:', term);
    if (!term) { results.innerHTML = '<p>Ingresa un término.</p>'; return; }
    try {
      const res = await fetch('/api/buscar.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ busqueda: term })
      });
      console.log('📡 [Search] Status:', res.status);
      const data = await res.json();
      console.log('✅ [Search] Results:', data.length);
      results.innerHTML = (data || []).map(p => (
        `<article class="proyecto-card">
           <h2><a href="/proyecto.php?slug=${encodeURIComponent(p.slug)}">${p.nombre}</a></h2>
           <p>Ciclo ${p.ciclo} · ${p.dificultad || ''}</p>
         </article>`
      )).join('') || '<p>Sin resultados.</p>';
    } catch (err) {
      console.log('❌ [Search] Error:', err.message);
      results.innerHTML = '<p>Error de búsqueda.</p>';
    }
  });
});