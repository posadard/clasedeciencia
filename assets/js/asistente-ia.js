(function(){
  console.log('🔍 [IA] Widget init');
  window.consultarIA = async function(contexto, pregunta){
    console.log('📡 [IA] Enviando pregunta:', pregunta);
    try {
      const res = await fetch('/api/ia-consulta.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({contexto, pregunta})
      });
      console.log('📡 [IA] Status:', res.status);
      const data = await res.json();
      console.log('✅ [IA] Respuesta:', data);
      return data;
    } catch (err) {
      console.log('❌ [IA] Error:', err.message);
      return {respuesta: 'Error de red', error: err.message};
    }
  }
})();