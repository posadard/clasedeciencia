document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('filtrosForm');
  if (!form) return;
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const params = new URLSearchParams(new FormData(form)).toString();
    console.log('🔍 [Catálogo] Filtros:', params);
    window.location.href = '/catalogo.php?' + params;
  });
});