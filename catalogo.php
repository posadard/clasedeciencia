<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db-functions.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Catálogo de Proyectos';
$page_description = 'Explora proyectos por ciclo, grado, área y dificultad.';
$canonical_url = canonical_url('catalogo.php');

$filtros = [
  'ciclo' => isset($_GET['ciclo']) ? (int)$_GET['ciclo'] : null,
  'grado' => isset($_GET['grado']) ? (int)$_GET['grado'] : null,
  'area' => isset($_GET['area']) ? $_GET['area'] : null,
  'dificultad' => isset($_GET['dificultad']) ? $_GET['dificultad'] : null,
];

try {
  $proyectos = get_proyectos($pdo, $filtros);
} catch (PDOException $e) {
  error_log($e->getMessage());
  $proyectos = [];
}

include __DIR__ . '/includes/header.php';
?>
<main class="container">
  <h1><?= h($page_title) ?></h1>
  <aside class="filters">
    <form id="filtrosForm">
      <select name="ciclo" id="ciclo">
        <option value="">Ciclo</option>
        <option value="1" <?= $filtros['ciclo']==1?'selected':'' ?>>Exploración</option>
        <option value="2" <?= $filtros['ciclo']==2?'selected':'' ?>>Experimentación</option>
        <option value="3" <?= $filtros['ciclo']==3?'selected':'' ?>>Análisis</option>
      </select>
      <select name="grado" id="grado">
        <option value="">Grado</option>
        <?php for($g=6;$g<=11;$g++): ?>
        <option value="<?= $g ?>" <?= $filtros['grado']==$g?'selected':'' ?>><?= $g ?>°</option>
        <?php endfor; ?>
      </select>
      <select name="area" id="area">
        <option value="">Área</option>
        <?php foreach (['Física','Química','Biología','Tecnología','Ambiental'] as $a): ?>
        <option value="<?= h($a) ?>" <?= $filtros['area']===$a?'selected':'' ?>><?= h($a) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="dificultad" id="dificultad">
        <option value="">Dificultad</option>
        <?php foreach (['baja','media','alta'] as $d): ?>
        <option value="<?= h($d) ?>" <?= $filtros['dificultad']===$d?'selected':'' ?>><?= h($d) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn-primary" type="submit">Aplicar</button>
    </form>
  </aside>
  <section class="grid" id="listaProyectos">
    <?php foreach ($proyectos as $p): ?>
      <article class="proyecto-card">
        <h2><a href="/proyecto.php?slug=<?= h($p['slug']) ?>"><?= h($p['nombre']) ?></a></h2>
        <p>Ciclo <?= h($p['ciclo']) ?> · <?= h($p['dificultad']) ?> · <?= h($p['duracion_minutos']) ?> min</p>
      </article>
    <?php endforeach; ?>
    <?php if (empty($proyectos)): ?><p>Sin resultados.</p><?php endif; ?>
  </section>
</main>
<script src="/assets/js/catalogo-filtros.js"></script>
<script>console.log('🔍 [Catálogo] Render con <?= count($proyectos) ?> proyectos');</script>
<?php include __DIR__ . '/includes/footer.php'; ?>