<?php
/**
 * Homepage - Clase de Ciencia (adaptado)
 */

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';
// Nota: el módulo de materiales se adaptará luego al esquema CdC

$page_title = 'Inicio';
$page_description = 'Proyectos científicos interactivos para estudiantes colombianos (6°-11°).';
$canonical_url = SITE_URL . '/';

// Proyectos destacados y recientes (CdC)
$featured_projects = cdc_get_featured_proyectos($pdo, 3);
$recent_projects = cdc_get_recent_proyectos($pdo, 6);
// Áreas para acceso rápido
$areas = cdc_get_areas($pdo);

include 'includes/header.php';
?>

<div class="container">
    <!-- Hero Section -->
    <section class="hero">
        <h2>Bienvenido a <?= SITE_NAME ?></h2>
        <p class="hero-subtitle">Proyectos científicos para grados 6°-11°</p>
        <p>Explora guías paso a paso, materiales del kit y explicaciones científicas alineadas con competencias MEN.</p>
        <div class="hero-actions">
            <a href="/catalogo.php" class="btn btn-primary">Explorar Clases</a>
        </div>
    </section>
        <!-- Acceso Rápido por Ciclo -->
        <section class="sections-overview">
            <h2>Explorar por Ciclo</h2>
            <div class="sections-grid">
                <a href="/catalogo.php?ciclo=1" class="section-card">
                    <h3>Ciclo 1: Exploración (6°-7°)</h3>
                    <p>Observar, describir y reconocer fenómenos.</p>
                </a>
                <a href="/catalogo.php?ciclo=2" class="section-card">
                    <h3>Ciclo 2: Experimentación (8°-9°)</h3>
                    <p>Explicar, comparar y establecer relaciones causales.</p>
                </a>
                <a href="/catalogo.php?ciclo=3" class="section-card">
                    <h3>Ciclo 3: Análisis (10°-11°)</h3>
                    <p>Analizar, argumentar y conectar con el mundo real.</p>
                </a>
            </div>
        </section>

    
    <!-- Proyectos Destacados -->
    <?php if (!empty($featured_projects)): ?>
    <section class="featured-articles">
        <h2>Clases recomendadas</h2>
        <div class="articles-grid featured">
            <?php foreach ($featured_projects as $p): ?>
            <article class="article-card featured" data-href="/proyecto.php?slug=<?= h($p['slug']) ?>">
                <a class="card-link" href="/proyecto.php?slug=<?= h($p['slug']) ?>">
                    <div class="card-content">
                        <div class="card-meta">
                            <span class="section-badge">Ciclo <?= h($p['ciclo']) ?></span>
                            <span class="difficulty-badge"><?= h(ucfirst($p['dificultad'])) ?></span>
                        </div>
                        <h3><?= h($p['nombre']) ?></h3>
                        <?php if (!empty($p['objetivo_aprendizaje'])): ?>
                        <p class="objective"><?= h($p['objetivo_aprendizaje']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($p['resumen'])): ?>
                        <p class="excerpt"><small><?= h($p['resumen']) ?></small></p>
                        <?php endif; ?>
                        <div class="card-footer">
                            <?php
                            $edad_label = '';
                            if (!empty($p['seguridad'])) {
                                $seg = json_decode($p['seguridad'], true);
                                if (is_array($seg) && isset($seg['edad_min'], $seg['edad_max'])) {
                                    $edad_label = 'Edad ' . (int)$seg['edad_min'] . '–' . (int)$seg['edad_max'];
                                }
                            }
                            if ($edad_label === '' && !empty($p['grados'])) {
                                $gr = json_decode($p['grados'], true);
                                if (is_array($gr) && count($gr) > 0) {
                                    $minG = min($gr); $maxG = max($gr);
                                    $edad_label = 'Grados ' . (int)$minG . '°–' . (int)$maxG . '°';
                                }
                            }
                            $area_label = !empty($p['areas_nombres']) ? $p['areas_nombres'] : '';
                            ?>
                            <?php if ($area_label): ?><span class="area">Área: <?= h($area_label) ?></span><?php endif; ?>
                            <?php if ($edad_label): ?><span class="age"><?= h($edad_label) ?></span><?php endif; ?>
                        </div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Acceso Rápido por Área -->
    <section class="sections-overview">
        <h2>Explorar por Área</h2>
        <div class="sections-grid">
            <?php foreach ($areas as $a): ?>
            <a href="/catalogo.php?area=<?= h($a['slug']) ?>" class="section-card area-card">
                <h3><?= h($a['nombre']) ?></h3>
                <p class="area-description"><?= h($a['descripcion']) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- Materiales (se adaptará al esquema CdC en una fase posterior) -->
    
    <!-- Proyectos Recientes -->
    <?php if (!empty($recent_projects)): ?>
    <section class="recent-articles">
        <h2>Proyectos Recientes</h2>
        <div class="articles-grid">
            <?php foreach ($recent_projects as $p): ?>
            <article class="article-card" data-href="/proyecto.php?slug=<?= h($p['slug']) ?>">
                <a class="card-link" href="/proyecto.php?slug=<?= h($p['slug']) ?>">
                    <div class="card-content">
                        <div class="card-meta">
                            <span class="section-badge">Ciclo <?= h($p['ciclo']) ?></span>
                            <span class="difficulty-badge"><?= h(ucfirst($p['dificultad'])) ?></span>
                        </div>
                        <h3><?= h($p['nombre']) ?></h3>
                        <?php if (!empty($p['objetivo_aprendizaje'])): ?>
                        <p class="objective"><?= h($p['objetivo_aprendizaje']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($p['resumen'])): ?>
                        <p class="excerpt"><small><?= h($p['resumen']) ?></small></p>
                        <?php endif; ?>
                        <div class="card-footer">
                            <?php
                            $edad_label = '';
                            if (!empty($p['seguridad'])) {
                                $seg = json_decode($p['seguridad'], true);
                                if (is_array($seg) && isset($seg['edad_min'], $seg['edad_max'])) {
                                    $edad_label = 'Edad ' . (int)$seg['edad_min'] . '–' . (int)$seg['edad_max'];
                                }
                            }
                            if ($edad_label === '' && !empty($p['grados'])) {
                                $gr = json_decode($p['grados'], true);
                                if (is_array($gr) && count($gr) > 0) {
                                    $minG = min($gr); $maxG = max($gr);
                                    $edad_label = 'Grados ' . (int)$minG . '°–' . (int)$maxG . '°';
                                }
                            }
                            $area_label = !empty($p['areas_nombres']) ? $p['areas_nombres'] : '';
                            ?>
                            <?php if ($area_label): ?><span class="area">Área: <?= h($area_label) ?></span><?php endif; ?>
                            <?php if ($edad_label): ?><span class="age"><?= h($edad_label) ?></span><?php endif; ?>
                        </div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="/catalogo.php" class="btn btn-secondary">Ver Clases</a>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Quick Links -->
    <section class="quick-links">
        <div class="quick-links-grid">
            <div class="quick-link-card">
                <h3>Clases</h3>
                <p>Explora clases por ciclo, grado y área.</p>
                <a href="/catalogo.php">Ir a las clases &rarr;</a>
            </div>
            <div class="quick-link-card">
                <h3>Contacto</h3>
                <p>¿Preguntas o sugerencias? Escríbenos.</p>
                <a href="/contact.php">Contacto &rarr;</a>
            </div>
        </div>
    </section>
</div>

<script>
console.log('✅ [home] Proyectos destacados:', <?= isset($featured_projects) ? count($featured_projects) : 0 ?>);
console.log('✅ [home] Proyectos recientes:', <?= isset($recent_projects) ? count($recent_projects) : 0 ?>);
console.log('✅ [home] Áreas disponibles:', <?= isset($areas) ? count($areas) : 0 ?>);
</script>

<?php include 'includes/footer.php'; ?>
