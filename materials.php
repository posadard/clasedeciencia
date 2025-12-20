<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db-functions.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Materiales';
$page_description = 'Catálogo de materiales y advertencias';
$canonical_url = canonical_url('materials.php');

$categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
try { $materiales = get_materiales($pdo, $categoria); } catch (PDOException $e) { error_log($e->getMessage()); $materiales = []; }

include __DIR__ . '/includes/header.php';
?>
<main class="container">
  <h1><?= h($page_title) ?></h1>
  <form id="categoriaForm" class="filters">
    <select name="categoria" id="categoria">
      <option value="">Todas las categorías</option>
      <?php for($c=1;$c<=10;$c++): ?>
        <option value="<?= $c ?>" <?= $categoria===$c?'selected':'' ?>>Categoría <?= $c ?></option>
      <?php endfor; ?>
    </select>
    <button class="btn-primary" type="submit">Aplicar</button>
  </form>
  <section class="grid">
    <?php foreach ($materiales as $m): ?>
      <article class="proyecto-card">
        <h2><a href="/material.php?id=<?= h($m['id']) ?>"><?= h($m['nombre_comun']) ?></a></h2>
        <p>Cat: <?= h($m['categoria_id']) ?></p>
        <?php if (!empty($m['advertencias_seguridad'])): ?>
          <p><strong>⚠️ Seguridad:</strong> <?= h($m['advertencias_seguridad']) ?></p>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
    <?php if (empty($materiales)): ?><p>Sin materiales.</p><?php endif; ?>
  </section>
</main>
<script>
  document.getElementById('categoriaForm').addEventListener('submit', (e)=>{
    e.preventDefault();
    const params = new URLSearchParams(new FormData(e.target)).toString();
    console.log('🔍 [Materiales] Filtro:', params);
    window.location.href = '/materials.php?' + params;
  });
  console.log('✅ [Materiales] Render con <?= count($materiales) ?>');
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>