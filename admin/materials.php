<?php
/**
 * Admin - Materials Management
 * List all materials with filters and search
 */

require_once '../config.php';
require_once '../includes/materials-functions.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
<?php
// Redirigir al módulo CdC
header('Location: /admin/materiales/index.php');
exit;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: bold;
    font-size: 0.9rem;
}

.filter-group select,
.filter-group input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 200px;
}

.search-group {
    flex-direction: row;
    align-items: center;
    flex: 1;
}

.search-group input {
    flex: 1;
}

.table-container {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #2c5f2d;
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: bold;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #eee;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.text-muted {
    color: #666;
}

.formula {
    font-family: 'Courier New', monospace;
    color: #2c5f2d;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: bold;
}

.badge-substance { background: #d4edda; color: #155724; }
.badge-equipment { background: #d1ecf1; color: #0c5460; }
.badge-tool { background: #fff3cd; color: #856404; }
.badge-container { background: #f8d7da; color: #721c24; }
.badge-safety { background: #f0e68c; color: #8b4513; }
.badge-consumable { background: #e7e7e7; color: #333; }

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
}

.status-published { background: #d4edda; color: #155724; }
.status-draft { background: #fff3cd; color: #856404; }
.status-discontinued { background: #f8d7da; color: #721c24; }

.actions {
    white-space: nowrap;
}

.actions .btn {
    margin-right: 0.5rem;
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.875rem;
}

.btn-edit {
    background: #007bff;
    color: white;
}

.btn-edit:hover {
    background: #0056b3;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.text-center {
    text-align: center;
}

.icon {
    display: inline-block;
}
</style>

<?php include 'footer.php'; ?>
