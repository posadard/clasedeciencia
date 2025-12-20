<?php
/**
 * Admin - Material Click Statistics
 * View analytics on which materials get the most interest
 */

require_once '../config.php';
require_once '../includes/material-tracking.php';
require_once '../includes/materials-functions.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Material Statistics';

// Get time period from query
$days = isset($_GET['days']) ? intval($_GET['days']) : 30;
if ($days < 1) $days = 30;

// Get statistics
$summary = get_click_statistics_summary($pdo, $days);
$top_materials = get_top_clicked_materials($pdo, 20, $days);
$top_articles = get_clicks_by_article($pdo, 10, $days);

include 'header.php';
?>

<div class="admin-header">
    <h1><svg class="admin-icon" width="18" height="18" aria-hidden="true"><use xlink:href="#icon-chart"/></svg> Material Click Statistics</h1>
    <div class="period-selector">
    <a href="?days=7" class="btn <?= $days === 7 ? 'btn-primary' : 'btn-secondary' ?>" style="<?= $days === 7 ? '' : 'background:#333;color:#fff;border-color:#333;' ?>">Last 7 Days</a>
    <a href="?days=30" class="btn <?= $days === 30 ? 'btn-primary' : 'btn-secondary' ?>" style="<?= $days === 30 ? '' : 'background:#333;color:#fff;border-color:#333;' ?>">Last 30 Days</a>
    <a href="?days=90" class="btn <?= $days === 90 ? 'btn-primary' : 'btn-secondary' ?>" style="<?= $days === 90 ? '' : 'background:#333;color:#fff;border-color:#333;' ?>">Last 90 Days</a>
    </div>
    <div class="stats-controls" style="display:flex;gap:.5rem;align-items:center">
        <button id="stats-refresh" class="btn btn-sm btn-secondary" type="button" title="Refresh now" style="background:#333;color:#fff;border-color:#333;">Refresh</button>
        <div id="stats-last-updated" style="font-size:0.9rem;color:#666">Last updated: -</div>
    </div>
</div>

<!-- Compact Summary Row -->
<div class="summary-row" style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;align-items:center">
    <div style="flex:1;min-width:160px;background:#fff;border:1px solid #ddd;padding:.75rem;border-radius:6px;text-align:center">
        <div style="font-size:1.1rem;font-weight:700" id="stat-total-clicks"><?= number_format($summary['total_clicks']) ?></div>
        <div style="font-size:.9rem;color:#666">Total Clicks<br><small>Last <?= $days ?> days</small></div>
    </div>
    <div style="flex:1;min-width:160px;background:#fff;border:1px solid #ddd;padding:.75rem;border-radius:6px;text-align:center">
        <div style="font-size:1.1rem;font-weight:700" id="stat-unique-visitors"><?= number_format($summary['unique_visitors']) ?></div>
        <div style="font-size:.9rem;color:#666">Unique Visitors</div>
    </div>
    <div style="flex:1;min-width:220px;background:#fff;border:1px solid #ddd;padding:.75rem;border-radius:6px;text-align:center">
        <div style="font-size:1rem;font-weight:700">Purchase / Detail</div>
        <div style="margin-top:.5rem;display:flex;gap:.75rem;justify-content:center">
            <div id="stat-purchase-links" style="min-width:80px;text-align:center"><?= number_format($summary['by_type']['purchase_link'] ?? 0) ?></div>
            <div id="stat-detail-views" style="min-width:80px;text-align:center"><?= number_format($summary['by_type']['detail_view'] ?? 0) ?></div>
        </div>
        <div style="font-size:.8rem;color:#666;margin-top:.5rem">purchase · detail</div>
    </div>
</div>

<!-- Clicks by Day -->
<?php if (!empty($summary['by_day'])): ?>
<div class="card">
    <h2>Activity Last 7 Days</h2>
    <div class="chart-container">
        <table class="chart-table">
            <tbody>
                <?php 
                $max_count = max(array_column($summary['by_day'], 'count'));
                foreach ($summary['by_day'] as $day): 
                $percentage = $max_count > 0 ? ($day['count'] / $max_count) * 100 : 0;
                ?>
                <tr>
                    <td class="chart-label"><?= date('M j', strtotime($day['date'])) ?></td>
                    <td class="chart-bar-cell">
                        <div class="chart-bar" style="width: <?= $percentage ?>%;">
                            <span class="chart-value"><?= $day['count'] ?> clicks</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Top Clicked Materials -->
<div class="card">
    <h2>Most Popular Materials</h2>
    <p class="card-subtitle">Top <?= count($top_materials) ?> materials by clicks in the last <?= $days ?> days</p>
    
    <?php if (empty($top_materials)): ?>
    <div class="empty-state">
        <p>No click data available for this period.</p>
    </div>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Material</th>
                <th>Category</th>
                <th>Clicks (Total)</th>
                <th>Purchase</th>
                <th>Detail</th>
                <th>Unique</th>
                <th>Last Clicked</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $rank = 1; foreach ($top_materials as $mat): ?>
            <tr>
                <td><strong>#<?= $rank++ ?></strong></td>
                <td>
                    <a href="/material.php?slug=<?= urlencode($mat['slug']) ?>" target="_blank">
                        <?= htmlspecialchars($mat['common_name']) ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($mat['category_name']) ?></td>
                <td><strong><?= number_format($mat['total_clicks'] ?? 0) ?></strong></td>
                <td><?= number_format($mat['purchase_clicks'] ?? 0) ?></td>
                <td><?= number_format($mat['detail_views'] ?? 0) ?></td>
                <td><?= number_format($mat['unique_visitors'] ?? 0) ?></td>
                <td><small><?= $mat['last_clicked_at'] ? htmlspecialchars(date('M j, Y H:i', strtotime($mat['last_clicked_at']))) : '-' ?></small></td>
                <td>
                    <a href="material-edit.php?id=<?= $mat['id'] ?>" class="btn btn-sm">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Top Source Articles -->
<?php if (!empty($top_articles)): ?>
<div class="card">
    <h2>Top Referring Articles</h2>
    <p class="card-subtitle">Articles that drive the most material clicks</p>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Article</th>
                <th>Total Clicks</th>
                <th>Unique Materials</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($top_articles as $art): ?>
            <tr>
                <td>
                    <a href="/article.php?slug=<?= urlencode($art['slug']) ?>" target="_blank">
                        <?= htmlspecialchars($art['title']) ?>
                    </a>
                </td>
                <td><strong><?= number_format($art['clicks']) ?></strong></td>
                <td><?= $art['unique_materials'] ?> materials</td>
                <td>
                    <a href="article-edit.php?id=<?= $art['id'] ?>" class="btn btn-sm">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Export Options -->
<div class="card">
    <h2>Export Data</h2>
    <p>Download click data for external analysis</p>
    <div class="export-buttons">
        <a href="export-clicks.php?days=<?= $days ?>&format=csv" class="btn btn-secondary" style="background:#333;color:#fff;border-color:#333;">
            📥 Export CSV
        </a>
        <a href="export-clicks.php?days=<?= $days ?>&format=json" class="btn btn-secondary" style="background:#333;color:#fff;border-color:#333;">
            📥 Export JSON
        </a>
    </div>
</div>

<style>
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.period-selector {
    display: flex;
    gap: 0.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border: 2px solid #000;
    padding: 1.5rem;
    text-align: center;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: #2c5f2d;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.stat-period {
    font-size: 0.875rem;
    color: #666;
}

.card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.card h2 {
    margin-bottom: 0.5rem;
}

.card-subtitle {
    color: #666;
    margin-bottom: 1.5rem;
}

.chart-container {
    overflow-x: auto;
}

.chart-table {
    width: 100%;
    border-collapse: collapse;
}

.chart-table tr {
    border-bottom: 1px solid #eee;
}

.chart-label {
    padding: 0.75rem 1rem;
    font-weight: 600;
    color: #666;
    width: 100px;
}

.chart-bar-cell {
    padding: 0.75rem 0;
}

.chart-bar {
    background: #2c5f2d;
    height: 32px;
    display: flex;
    align-items: center;
    padding: 0 1rem;
    border-radius: 4px;
    transition: width 0.3s ease;
    min-width: 80px;
}

.chart-value {
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.data-table th {
    background: #f5f5f5;
    font-weight: 600;
}

.data-table tr:hover {
    background: #fafafa;
}

.export-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #666;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}
</style>

<?php include 'footer.php'; ?>

<script>
// Live polling for material stats
(function(){
    const days = <?= json_encode($days) ?>;
    const endpoint = '/api/material-stats.php?days=' + encodeURIComponent(days);

    function updateSummary(summary) {
        const total = document.querySelector('#stat-total-clicks .stat-value');
        const unique = document.querySelector('#stat-unique-visitors .stat-value');
        const purchase = document.querySelector('#stat-purchase-links .stat-value');
        const detail = document.querySelector('#stat-detail-views .stat-value');

        if (total) total.textContent = Number(summary.total_clicks || 0).toLocaleString();
        if (unique) unique.textContent = Number(summary.unique_visitors || 0).toLocaleString();
        if (purchase) purchase.textContent = Number((summary.by_type && summary.by_type.purchase_link) || 0).toLocaleString();
        if (detail) detail.textContent = Number((summary.by_type && summary.by_type.detail_view) || 0).toLocaleString();
    }

    function renderTopMaterials(topMaterials) {
        const tbody = document.querySelector('table.data-table tbody');
        if (!tbody) return;
        // If the page contains other tables, try to narrow by checking heading
        // We'll replace rows under "Most Popular Materials" table only
        // Find the table by header text "Most Popular Materials"
        const tables = Array.from(document.querySelectorAll('table.data-table'));
        let target = null;
        for (const t of tables) {
            const h = t.closest('.card')?.querySelector('h2');
            if (h && /Most Popular Materials/i.test(h.textContent)) { target = t; break; }
        }
        if (!target) return;
        const body = target.querySelector('tbody');
        if (!body) return;
        body.innerHTML = '';
        let rank = 1;
        for (const mat of topMaterials) {
            const tr = document.createElement('tr');
            const last = mat.last_clicked_at ? new Date(mat.last_clicked_at).toLocaleString() : '-';
            tr.innerHTML = `
                <td><strong>#${rank++}</strong></td>
                <td><a href="/material.php?slug=${encodeURIComponent(mat.slug)}" target="_blank">${escapeHtml(mat.common_name)}</a></td>
                <td>${escapeHtml(mat.category_name || '')}</td>
                <td><strong>${Number(mat.total_clicks || 0).toLocaleString()}</strong></td>
                <td>${Number(mat.purchase_clicks || 0).toLocaleString()}</td>
                <td>${Number(mat.detail_views || 0).toLocaleString()}</td>
                <td>${Number(mat.unique_visitors || 0).toLocaleString()}</td>
                <td><small>${escapeHtml(last)}</small></td>
                <td><a href="material-edit.php?id=${encodeURIComponent(mat.id)}" class="btn btn-sm">Edit</a></td>
            `;
            body.appendChild(tr);
        }
    }

    function renderTopArticles(topArticles) {
        const cards = document.querySelectorAll('.card');
        let target = null;
        for (const c of cards) {
            const h = c.querySelector('h2');
            if (h && /Top Referring Articles|Top Referring Articles/i.test(h.textContent)) { target = c; break; }
        }
        if (!target) return;
        const table = target.querySelector('table.data-table');
        if (!table) return;
        const body = table.querySelector('tbody');
        if (!body) return;
        body.innerHTML = '';
        for (const art of topArticles) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><a href="/article.php?slug=${encodeURIComponent(art.slug)}" target="_blank">${escapeHtml(art.title)}</a></td>
                <td><strong>${Number(art.clicks||0).toLocaleString()}</strong></td>
                <td>${Number(art.unique_materials||0).toLocaleString()} materials</td>
                <td><a href="article-edit.php?id=${encodeURIComponent(art.id)}" class="btn btn-sm">Edit</a></td>
            `;
            body.appendChild(tr);
        }
    }

    function escapeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str).replace(/[&<>"'`]/g, function(s){
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;','`':'&#x60'})[s];
        });
    }

    let abortController = null;
    async function poll() {
        try {
            if (abortController) abortController.abort();
            abortController = new AbortController();
            console.log('[material-stats] polling', endpoint);
            const res = await fetch(endpoint, {signal: abortController.signal, credentials: 'same-origin'});
            if (!res.ok) throw new Error('Network response was not ok');
            const json = await res.json();
            console.log('[material-stats] response', json);
            if (json.success) {
                updateSummary(json.summary || {});
                renderTopMaterials(json.top_materials || []);
                renderTopArticles(json.top_articles || []);
                const now = new Date();
                const el = document.getElementById('stats-last-updated');
                if (el) el.textContent = 'Last updated: ' + now.toLocaleTimeString();
            } else if (json.auth === false) {
                console.warn('[material-stats] auth failure', json);
                const el = document.getElementById('stats-last-updated');
                if (el) el.textContent = 'Not authenticated';
            }
        } catch (err) {
            // Ignore polling errors but keep retrying
            console.warn('Material stats polling error:', err);
        } finally {
            setTimeout(poll, 10000); // 10s
        }
    }

    // Start polling after DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function(){
            poll();
            const btn = document.getElementById('stats-refresh');
            if (btn) btn.addEventListener('click', function(){
                console.log('[material-stats] manual refresh');
                poll();
            });
        });
    } else {
        poll();
        const btn = document.getElementById('stats-refresh');
        if (btn) btn.addEventListener('click', function(){
            console.log('[material-stats] manual refresh');
            poll();
        });
    }
})();
</script>
