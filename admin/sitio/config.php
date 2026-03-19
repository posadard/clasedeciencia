<?php
require_once '../auth.php';
$page_title = 'Configuración del Sitio';

$msg = '';
$msg_type = '';

// Cargar todos los valores actuales
$config = [];
try {
    $rows = $pdo->query('SELECT clave, valor, descripcion FROM sitio_config ORDER BY clave ASC')
                ->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $config[$row['clave']] = ['valor' => $row['valor'], 'descripcion' => $row['descripcion']];
    }
} catch (PDOException $e) {
    error_log('Admin sitio/config GET: ' . $e->getMessage());
    $msg = '❌ Error cargando configuración: ' . $e->getMessage();
    $msg_type = 'error';
}

// Procesar guardado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare('UPDATE sitio_config SET valor = ? WHERE clave = ?');
        $saved = 0;
        foreach ($_POST['config'] ?? [] as $clave => $valor) {
            // Sanitizar clave: solo alfanumérico + guión bajo
            if (!preg_match('/^[a-z0-9_]+$/', $clave)) continue;
            $stmt->execute([trim($valor), $clave]);
            $saved++;
        }
        $msg = '✅ ' . $saved . ' valores guardados correctamente.';
        $msg_type = 'ok';
        // Recargar
        $rows = $pdo->query('SELECT clave, valor, descripcion FROM sitio_config ORDER BY clave ASC')
                    ->fetchAll(PDO::FETCH_ASSOC);
        $config = [];
        foreach ($rows as $row) {
            $config[$row['clave']] = ['valor' => $row['valor'], 'descripcion' => $row['descripcion']];
        }
        console_log_note('✅ [Admin] Sitio config guardado, claves: ' . $saved);
    } catch (PDOException $e) {
        error_log('Admin sitio/config POST: ' . $e->getMessage());
        $msg = '❌ Error al guardar: ' . $e->getMessage();
        $msg_type = 'error';
    }
}

function console_log_note($m) { /* placeholder for inline JS */ }

// Definir orden y metadatos de presentación de las claves conocidas
$campos = [
    'sitio_nombre'       => ['label' => 'Nombre del sitio',         'tipo' => 'text',     'max' => 120],
    'sitio_descripcion'  => ['label' => 'Meta description global',  'tipo' => 'textarea', 'max' => 320],
    'email_contacto'     => ['label' => 'Email de contacto',        'tipo' => 'text',     'max' => 180],
    'footer_texto_sobre' => ['label' => 'Texto "Acerca de" del footer', 'tipo' => 'textarea', 'max' => 600],
    'facebook_url'       => ['label' => 'Facebook URL',             'tipo' => 'text',     'max' => 512],
    'instagram_url'      => ['label' => 'Instagram URL',            'tipo' => 'text',     'max' => 512],
    'youtube_url'        => ['label' => 'YouTube URL',              'tipo' => 'text',     'max' => 512],
];

include '../header.php';
?>
<div class="page-header">
    <h2>Configuración del Sitio</h2>
    <span class="help-text">Valores globales del sitio: nombre, email, footer, redes sociales.</span>
    <script>
        console.log('🔍 [Admin] Sitio config cargado, claves:', <?= count($config) ?>);
    </script>
</div>

<?php if ($msg): ?>
<div class="alert" style="margin-bottom:1rem;padding:0.75rem 1rem;border-radius:6px;background:<?= $msg_type === 'ok' ? '#d4edda' : '#f8d7da' ?>;color:<?= $msg_type === 'ok' ? '#155724' : '#721c24' ?>;">
    <?= h($msg) ?>
</div>
<?php endif; ?>

<?php if (empty($config)): ?>
<div class="card">
    <p style="padding:1rem;color:#666;">No hay configuración disponible. Verifica que la tabla <code>sitio_config</code> exista y tenga datos.</p>
</div>
<?php else: ?>

<div class="card">
    <form method="POST">
        <?php foreach ($campos as $clave => $meta): ?>
            <?php if (!isset($config[$clave])) continue; ?>
            <?php $valor_actual = $config[$clave]['valor'] ?? ''; ?>
            <?php $desc = $config[$clave]['descripcion'] ?? $meta['label']; ?>

            <div class="form-group" style="margin-bottom:1.25rem;">
                <label for="cfg_<?= h($clave) ?>">
                    <?= h($meta['label']) ?>
                    <small style="color:#666;font-weight:400;display:block;"><?= h($desc) ?></small>
                </label>

                <?php if ($meta['tipo'] === 'textarea'): ?>
                <textarea id="cfg_<?= h($clave) ?>"
                          name="config[<?= h($clave) ?>]"
                          class="form-control"
                          rows="3"
                          maxlength="<?= (int)$meta['max'] ?>"><?= h($valor_actual) ?></textarea>
                <?php else: ?>
                <input type="text"
                       id="cfg_<?= h($clave) ?>"
                       name="config[<?= h($clave) ?>]"
                       class="form-control"
                       value="<?= h($valor_actual) ?>"
                       maxlength="<?= (int)$meta['max'] ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <!-- Claves en BD que no están en $campos (por si se agregaron manualmente) -->
        <?php foreach ($config as $clave => $data): ?>
            <?php if (isset($campos[$clave])) continue; ?>
            <div class="form-group" style="margin-bottom:1.25rem;background:#fffbe6;padding:0.75rem;border-radius:6px;border:1px solid #ffe58f;">
                <label for="cfg_<?= h($clave) ?>">
                    <code><?= h($clave) ?></code>
                    <small style="color:#666;font-weight:400;"> — <?= h($data['descripcion'] ?? '') ?></small>
                </label>
                <input type="text"
                       id="cfg_<?= h($clave) ?>"
                       name="config[<?= h($clave) ?>]"
                       class="form-control"
                       value="<?= h($data['valor'] ?? '') ?>"
                       maxlength="512">
            </div>
        <?php endforeach; ?>

        <div style="margin-top:1.5rem;">
            <button type="submit" class="btn">
                <svg class="admin-icon" width="14" height="14" aria-hidden="true"><use xlink:href="#icon-save"/></svg>
                Guardar configuración
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php include '../footer.php'; ?>
