document.addEventListener('DOMContentLoaded', ()=>{
  const title = document.getElementById('nombre');
  const slug = document.getElementById('slug');
  if (title && slug) {
    title.addEventListener('input', ()=>{
      const s = (title.value||'').toLowerCase().replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-');
      slug.value = s;
      console.log('🔍 [Admin] Slug:', s);
    });
  }
});