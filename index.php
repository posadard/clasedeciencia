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

// Áreas y ciclos para acceso rápido
$areas = cdc_get_areas($pdo);
$ciclos = cdc_get_ciclos($pdo, true); // Solo ciclos activos

include 'includes/header.php';
?>

<div class="container">
    <!-- Hero Section -->
    <section class="hero">
        <h2>Bienvenido a <?= SITE_NAME ?></h2>
        <p class="hero-subtitle">Plataforma de formación científica para grados 1° a 11°</p>
        <p>Apoya y fortalece el desarrollo de competencias científicas con guías interactivas, proyectos prácticos y orientación personalizada para cada proceso de aprendizaje.</p>
        <div class="hero-actions">
            <a href="/clases" class="btn btn-primary">Explorar Clases</a>
        </div>
    </section>
        <!-- Acceso Rápido por Ciclo -->
        <section class="sections-overview">
            <h2>Explorar por Ciclo</h2>
            <div class="ciclos-grid">
                <?php foreach ($ciclos as $c): ?>
                <article class="ciclo-card" data-ciclo="<?= h($c['numero']) ?>">
                    <!-- Icono central con número de ciclo -->
                    <div class="ciclo-icon ciclo-<?= h($c['numero']) ?>">
                        <span class="ciclo-numero"><?= h($c['numero']) ?></span>
                    </div>
                    
                    <!-- Información jerárquica -->
                    <div class="ciclo-header">
                        <h3 class="ciclo-nombre"><?= h($c['nombre']) ?></h3>
                        <p class="ciclo-grados"><?= h($c['grados_texto']) ?></p>
                        <span class="ciclo-edad"><?= h($c['edad_min']) ?>-<?= h($c['edad_max']) ?> años</span>
                    </div>
                    
                    <!-- Propósito (descripción breve) -->
                    <p class="ciclo-proposito"><?= h($c['proposito']) ?></p>
                    
                    <!-- Metadata en badges -->
                    <div class="ciclo-meta">
                        <span class="badge badge-nivel"><?= h($c['nivel_educativo']) ?></span>
                        <?php if (!empty($c['isced_level'])): ?>
                        <span class="badge badge-isced"><?= h($c['isced_level']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Call to action -->
                    <div class="ciclo-footer">
                        <a href="/<?= h($c['slug']) ?>" class="btn btn-primary">
                            Explorar proyectos
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-left: 0.25rem;">
                                <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>


    
    <!-- Acceso Rápido por Área -->
    <section class="sections-overview">
        <h2>Explorar por Área</h2>
        <div class="sections-grid">
            <?php foreach ($areas as $a): ?>
            <a href="/<?= h($a['slug']) ?>" class="section-card area-card">
                <h3><?= h($a['nombre']) ?></h3>
                <p class="area-description"><?= h($a['descripcion']) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- Materiales (se adaptará al esquema CdC en una fase posterior) -->
    
    <!-- Quick Links -->
    <section class="quick-links">
        <div class="quick-links-grid">
            <div class="quick-link-card">
                <h3>Clases</h3>
                <p>Explora clases por ciclo, grado y área.</p>
                <a href="/clases">Ir a las clases &rarr;</a>
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
console.log('✅ [home] Áreas disponibles:', <?= isset($areas) ? count($areas) : 0 ?>);
console.log('✅ [home] Ciclos activos:', <?= isset($ciclos) ? count($ciclos) : 0 ?>);
</script>

<?php include 'includes/footer.php'; ?>
