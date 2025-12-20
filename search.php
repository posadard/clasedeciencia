<?php
/**
 * Búsqueda (servidor) — resultados cuando JS está desactivado
 */

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$q = trim($_GET['q'] ?? '');

$page_title = $q ? 'Búsqueda: ' . h($q) : 'Búsqueda';
$page_description = $q ? 'Resultados de búsqueda para: ' . h($q) : 'Busca proyectos y materiales';
$canonical_url = SITE_URL . '/search.php' . ($q ? ('?q=' . urlencode($q)) : '');

include 'includes/header.php';
?>

<div class="container">
    <div class="breadcrumb"><a href="/">Inicio</a> / <strong>Búsqueda</strong></div>
    <h1><?= h($page_title) ?></h1>

    <?php if (!$q): ?>
        <p>Escribe un término para buscar proyectos y materiales.</p>
    <?php else: ?>
        <?php
        // 🔍 Debug
        echo '<script>console.log("🔍 [search.php] Query:", ' . json_encode($q) . ');</script>';

        $like = '%' . $q . '%';
        // Proyectos: buscar por nombre, resumen, objetivo
        try {
            $stmtP = $pdo->prepare(
                "SELECT id, nombre, slug, resumen, updated_at
                 FROM proyectos
                 WHERE activo = 1 AND (nombre LIKE :q1 OR resumen LIKE :q2 OR objetivo_aprendizaje LIKE :q3)
                 ORDER BY orden_popularidad DESC, updated_at DESC
                 LIMIT 20"
            );
            $stmtP->execute(['q1' => $like, 'q2' => $like, 'q3' => $like]);
            $proyectos = $stmtP->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error búsqueda proyectos: ' . $e->getMessage());
            $proyectos = [];
        }

        // Materiales: nombre común/técnico y descripción
        try {
            $stmtM = $pdo->prepare(
                "SELECT id, nombre_comun, slug, nombre_tecnico, descripcion
                 FROM materiales
                 WHERE (nombre_comun LIKE :q1 OR nombre_tecnico LIKE :q2 OR descripcion LIKE :q3)
                 ORDER BY created_at DESC
                 LIMIT 20"
            );
            $stmtM->execute(['q1' => $like, 'q2' => $like, 'q3' => $like]);
            $materiales = $stmtM->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error búsqueda materiales: ' . $e->getMessage());
            $materiales = [];
        }
        ?>

        <section class="search-results-page">
            <?php if (empty($proyectos) && empty($materiales)): ?>
                <div class="empty-state">
                    <h2>Sin resultados</h2>
                    <p>No encontramos proyectos ni materiales para tu búsqueda. Prueba con términos más generales.</p>
                </div>
            <?php else: ?>
                <?php if (!empty($proyectos)): ?>
                <div class="content-section">
                    <h2>Proyectos</h2>
                    <ul>
                        <?php foreach ($proyectos as $p): ?>
                        <li>
                            <a href="/proyecto.php?slug=<?= h($p['slug']) ?>"><?= h($p['nombre']) ?></a>
                            — <?= h($p['resumen'] ?: '') ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($materiales)): ?>
                <div class="content-section">
                    <h2>Materiales</h2>
                    <ul>
                        <?php foreach ($materiales as $m): ?>
                        <li>
                            <a href="/material.php?slug=<?= h($m['slug']) ?>"><?= h($m['nombre_comun']) ?></a>
                            — <?= h($m['nombre_tecnico'] ?: substr(strip_tags($m['descripcion'] ?? ''), 0, 120)) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
