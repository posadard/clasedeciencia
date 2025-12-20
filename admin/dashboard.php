<?php
<?php
/**
 * Admin Dashboard (CdC)
 */

require_once 'auth.php';

$page_title = 'Panel';

// Debug instrumentation
$debug_messages = [];
set_error_handler(function($severity, $message, $file, $line) use (&$debug_messages) {
    $debug_messages[] = "PHP Error ($severity): $message in $file:$line";
    return false; // allow normal error handling too
});
set_exception_handler(function($e) use (&$debug_messages) {
    $debug_messages[] = 'Uncaught Exception: ' . $e->getMessage();
});
register_shutdown_function(function() use (&$debug_messages) {
    $err = error_get_last();
    if ($err) {
        // Try to emit a minimal console log even on fatal shutdown
        echo '<script>console.log("❌ [Admin] Fatal shutdown:", ' . json_encode($err, JSON_UNESCAPED_UNICODE) . ');</script>';
    }
});

// Helper seguro para conteos
$tableExists = function (PDO $pdo, string $table) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        if (!$stmt) { return false; }
        $stmt->execute([$table]);
        return ((int)$stmt->fetchColumn()) > 0;
    } catch (PDOException $e) {
        error_log('Admin table exists check error: ' . $e->getMessage());
        $debug_messages[] = 'Table check failed: ' . $table . ' -> ' . $e->getMessage();
        return false;
    }
};

$getCount = function (PDO $pdo, string $sql) {
    try {
        $stmt = $pdo->prepare($sql);
        if (!$stmt) { return 0; }
        if (!$stmt->execute([])) { return 0; }
        $val = $stmt->fetchColumn();
        return is_numeric($val) ? (int)$val : 0;
    } catch (PDOException $e) {
        error_log('Admin count error: ' . $e->getMessage());
        return 0;
    }
};

// Estadísticas principales
try {
    $stats = [
        'proyectos' => $tableExists($pdo, 'proyectos') ? $getCount($pdo, "SELECT COUNT(*) FROM proyectos WHERE activo = 1") : 0,
        'materiales' => $tableExists($pdo, 'materiales') ? $getCount($pdo, "SELECT COUNT(*) FROM materiales") : 0,
        'contratos' => $tableExists($pdo, 'contratos') ? $getCount($pdo, "SELECT COUNT(*) FROM contratos") : 0,
        'entregas' => $tableExists($pdo, 'entregas') ? $getCount($pdo, "SELECT COUNT(*) FROM entregas") : 0,
        'lotes' => $tableExists($pdo, 'lotes_kits') ? $getCount($pdo, "SELECT COUNT(*) FROM lotes_kits") : 0,
    ];
} catch (PDOException $e) {
    error_log('Admin stats error: ' . $e->getMessage());
    $stats = ['proyectos' => 0, 'materiales' => 0, 'contratos' => 0, 'entregas' => 0, 'lotes' => 0];
    $debug_messages[] = 'Stats error: ' . $e->getMessage();
}

// DB ping
$pdo_ok = false;
try {
    $pdo->query('SELECT 1');
    $pdo_ok = true;
} catch (PDOException $e) {
    $debug_messages[] = 'DB ping failed: ' . $e->getMessage();
}

// Table presence snapshot
$tables_to_check = ['proyectos','materiales','contratos','entregas','lotes_kits','ia_logs'];
$tables_snapshot = [];
foreach ($tables_to_check as $t) {
    $tables_snapshot[$t] = $tableExists($pdo, $t);
}

// Proyectos recientes
try {
    if ($tableExists($pdo, 'proyectos')) {
        $stmt = $pdo->prepare("\n            SELECT id, nombre, slug, ciclo, updated_at, activo, destacado\n            FROM proyectos\n            ORDER BY updated_at DESC\n            LIMIT 5\n        ");
        if ($stmt && $stmt->execute([])) {
            $recent_proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $recent_proyectos = [];
            $debug_messages[] = 'Recent proyectos query failed to execute';
        }
    } else {
        $recent_proyectos = [];
        $debug_messages[] = 'Table missing: proyectos';
    }
} catch (PDOException $e) {
    error_log('Admin recent proyectos error: ' . $e->getMessage());
    $recent_proyectos = [];
    $debug_messages[] = 'Recent proyectos error: ' . $e->getMessage();
}

// IA actividad (últimos 7 días)
try {
    if ($tableExists($pdo, 'ia_logs')) {
        $ia_stats = [
            'consultas' => $getCount($pdo, "SELECT COUNT(*) FROM ia_logs WHERE tipo_evento = 'consulta' AND fecha_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)"),
            'respuestas' => $getCount($pdo, "SELECT COUNT(*) FROM ia_logs WHERE tipo_evento = 'respuesta' AND fecha_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)"),
            'guardrails' => $getCount($pdo, "SELECT COUNT(*) FROM ia_logs WHERE tipo_evento = 'guardrail_activado' AND fecha_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)"),
        ];
    } else {
        $ia_stats = ['consultas' => 0, 'respuestas' => 0, 'guardrails' => 0];
        $debug_messages[] = 'Table missing: ia_logs';
    }
} catch (PDOException $e) {
    error_log('Admin IA stats error: ' . $e->getMessage());
    $ia_stats = ['consultas' => 0, 'respuestas' => 0, 'guardrails' => 0];
    $debug_messages[] = 'IA stats error: ' . $e->getMessage();
}

include 'header.php';
?>

<div class="page-header">
    <h2>Panel</h2>
        <p>Bienvenido, <?= htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8') ?>.</p>
    <p class="help-text">Resumen del estado del sitio y acceso rápido a módulos.</p>
        <script>
            console.log('✅ [Admin] Dashboard cargado');
            console.log('🔍 [Admin] DB ping OK:', <?= $pdo_ok ? 'true' : 'false' ?>);
            console.log('🔍 [Admin] Tablas presentes:', <?= json_encode($tables_snapshot, JSON_UNESCAPED_UNICODE) ?>);
            console.log('🔍 [Admin] Stats:', {
                proyectos: <?= (int)$stats['proyectos'] ?>,
                materiales: <?= (int)$stats['materiales'] ?>,
                contratos: <?= (int)$stats['contratos'] ?>,
                entregas: <?= (int)$stats['entregas'] ?>,
                lotes: <?= (int)$stats['lotes'] ?>
            });
            console.log('🔍 [Admin] IA (7d):', {
                consultas: <?= (int)$ia_stats['consultas'] ?>,
                respuestas: <?= (int)$ia_stats['respuestas'] ?>,
                guardrails: <?= (int)$ia_stats['guardrails'] ?>
            });
            <?php if (!empty($debug_messages)): ?>
            console.log('⚠️ [Admin] Debug mensajes:');
            (<?= json_encode($debug_messages, JSON_UNESCAPED_UNICODE) ?>).forEach(m => console.log('❌ [Admin] ', m));
            <?php endif; ?>
        </script>
    </div>

<!-- Estadísticas -->
<div class="stats-grid">
    <div class="stat-card">
        <h3><?= $stats['proyectos'] ?></h3>
        <p>Proyectos activos</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['materiales'] ?></h3>
        <p>Materiales</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['contratos'] ?></h3>
        <p>Contratos</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['entregas'] ?></h3>
        <p>Entregas</p>
    </div>
    <div class="stat-card">
        <h3><?= $stats['lotes'] ?></h3>
        <p>Lotes de kits</p>
    </div>
</div>

<!-- IA actividad -->
<div class="card">
    <h3>Actividad IA (7 días)</h3>
    <p>Consultas: <strong><?= $ia_stats['consultas'] ?></strong> · Respuestas: <strong><?= $ia_stats['respuestas'] ?></strong> · Guardrails: <strong><?= $ia_stats['guardrails'] ?></strong></p>
</div>

<!-- Acciones rápidas -->
<div class="card">
    <h3>Acciones rápidas</h3>
    <div class="actions">
        <a href="/admin/proyectos/edit.php" class="btn">+ Nuevo Proyecto</a>
        <a href="/admin/materiales/edit.php" class="btn btn-secondary">+ Nuevo Material</a>
    </div>
</div>

<!-- Proyectos recientes -->
<div class="card">
    <h3>Proyectos recientes</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Ciclo</th>
                <th>Estado</th>
                <th>Actualizado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_proyectos as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($p['ciclo'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <span style="padding:0.25rem 0.5rem;background:<?= $p['activo'] ? '#4caf50' : '#ff9800' ?>;color:#fff;font-size:0.75rem;font-weight:600;">
                        <?= $p['activo'] ? 'ACTIVO' : 'INACTIVO' ?><?= $p['destacado'] ? ' · ★' : '' ?>
                    </span>
                </td>
                <td><?= htmlspecialchars(date('Y-m-d', strtotime($p['updated_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="actions">
                    <a href="/proyecto.php?slug=<?= htmlspecialchars($p['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn action-btn btn-secondary">Ver</a>
                    <a href="/admin/proyectos/edit.php?id=<?= (int)$p['id'] ?>" class="btn action-btn">Editar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Seguridad -->
<div class="message info">
    <strong>🔒 Nota:</strong> Cambia las credenciales por defecto en <span class="help-text">/admin/index.php</span>.
</div>

<?php include 'footer.php'; ?>
<div class="message info">
    <strong>🔒 Security Note:</strong> Remember to change the default admin password in <code>/admin/index.php</code> before going to production!
</div>

<?php include 'footer.php'; ?>
