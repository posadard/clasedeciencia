<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Contacto';
$page_description = 'Contáctanos';
$canonical_url = canonical_url('contact.php');
include __DIR__ . '/includes/header.php';
?>
<main class="container">
  <h1><?= h($page_title) ?></h1>
  <form id="contactForm">
    <input type="text" name="nombre" placeholder="Tu nombre" required />
    <input type="email" name="email" placeholder="Tu email" required />
    <textarea name="mensaje" placeholder="Mensaje" required></textarea>
    <button class="btn-primary" type="submit">Enviar</button>
  </form>
  <p>Este formulario es demostrativo; el envío real se configurará luego.</p>
</main>
<script>
  document.getElementById('contactForm').addEventListener('submit', (e)=>{
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target).entries());
    console.log('📬 [Contacto] Datos:', data);
    alert('Gracias por escribir. Verifica la consola (🔍).');
  });
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>