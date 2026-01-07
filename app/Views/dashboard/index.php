<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #ff8c42 0%, #ff6b35 100%);">
            📋
        </div>
        <div class="stat-content">
            <h3>Total Work Orders</h3>
            <div class="stat-value"><?= $total_work_orders ?? 156 ?></div>
            <div class="stat-trend up">
                <span>↗</span> +12% dari bulan lalu
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            ⏳
        </div>
        <div class="stat-content">
            <h3>Pending Orders</h3>
            <div class="stat-value"><?= $pending_orders ?? 23 ?></div>
            <div class="stat-trend down">
                <span>↘</span> -8% dari kemarin
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            📦
        </div>
        <div class="stat-content">
            <h3>Items in Stock</h3>
            <div class="stat-value"><?= $items_in_stock ?? 1248 ?></div>
            <div class="stat-trend up">
                <span>↗</span> +5% dari minggu lalu
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            ⚠️
        </div>
        <div class="stat-content">
            <h3>Low Stock Alert</h3>
            <div class="stat-value"><?= $low_stock_alert ?? 18 ?></div>
            <div class="stat-trend warning">
                <span>⚠</span> Perlu perhatian
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity & Quick Actions -->
<div class="dashboard-grid">
    <!-- Recent Work Orders -->
    <div class="card">
        <div class="card-header">
            <h2>Recent Work Orders</h2>
            <a href="<?= base_url('work-orders') ?>" class="link-btn">View All →</a>
        </div>
        <div class="card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>WO ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Priority</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_work_orders)): ?>
                        <?php foreach ($recent_work_orders as $wo): ?>
                        <tr>
                            <td><strong><?= esc($wo['wo_id']) ?></strong></td>
                            <td><?= esc($wo['title']) ?></td>
                            <td><span class="badge badge-<?= esc($wo['status_class']) ?>"><?= esc($wo['status']) ?></span></td>
                            <td><span class="badge badge-<?= esc($wo['priority_class']) ?>"><?= esc($wo['priority']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td><strong>WO-2024-001</strong></td>
                            <td>Maintenance Unit A</td>
                            <td><span class="badge badge-warning">Pending</span></td>
                            <td><span class="badge badge-danger">High</span></td>
                        </tr>
                        <tr>
                            <td><strong>WO-2024-002</strong></td>
                            <td>Repair Engine B</td>
                            <td><span class="badge badge-info">In Progress</span></td>
                            <td><span class="badge badge-warning">Medium</span></td>
                        </tr>
                        <tr>
                            <td><strong>WO-2024-003</strong></td>
                            <td>Inspection Unit C</td>
                            <td><span class="badge badge-success">Completed</span></td>
                            <td><span class="badge badge-success">Low</span></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h2>Quick Actions</h2>
        </div>
        <div class="card-body">
            <div class="quick-actions">
                <a href="<?= base_url('work-orders/create') ?>" class="action-btn primary">
                    <span class="action-icon">➕</span>
                    <div class="action-content">
                        <h4>New Work Order</h4>
                        <p>Create new work order</p>
                    </div>
                </a>

                <a href="<?= base_url('work-orders/export/excel') ?>" class="action-btn success">
                    <span class="action-icon">📥</span>
                    <div class="action-content">
                        <h4>Export Data</h4>
                        <p>Download Excel report</p>
                    </div>
                </a>

                <a href="<?= base_url('inventory') ?>" class="action-btn info">
                    <span class="action-icon">🔍</span>
                    <div class="action-content">
                        <h4>Check Stock</h4>
                        <p>View inventory levels</p>
                    </div>
                </a>

                <a href="<?= base_url('reports') ?>" class="action-btn warning">
                    <span class="action-icon">📊</span>
                    <div class="action-content">
                        <h4>View Reports</h4>
                        <p>Analytics & insights</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Items -->
<div class="card">
    <div class="card-header">
        <h2>⚠️ Low Stock Items</h2>
        <a href="<?= base_url('inventory?filter=low-stock') ?>" class="link-btn">View All →</a>
    </div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Item Name</th>
                    <th>Current Stock</th>
                    <th>Min. Required</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($low_stock_items)): ?>
                    <?php foreach ($low_stock_items as $item): ?>
                    <tr>
                        <td><strong><?= esc($item['sku']) ?></strong></td>
                        <td><?= esc($item['name']) ?></td>
                        <td><?= esc($item['current_stock']) ?> units</td>
                        <td><?= esc($item['min_required']) ?> units</td>
                        <td><span class="badge badge-<?= esc($item['status_class']) ?>"><?= esc($item['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td><strong>SKU-001</strong></td>
                        <td>Engine Oil 5W-30</td>
                        <td>8 units</td>
                        <td>20 units</td>
                        <td><span class="badge badge-danger">Critical</span></td>
                    </tr>
                    <tr>
                        <td><strong>SKU-002</strong></td>
                        <td>Air Filter Type A</td>
                        <td>15 units</td>
                        <td>25 units</td>
                        <td><span class="badge badge-warning">Low</span></td>
                    </tr>
                    <tr>
                        <td><strong>SKU-003</strong></td>
                        <td>Brake Pad Set</td>
                        <td>12 units</td>
                        <td>20 units</td>
                        <td><span class="badge badge-warning">Low</span></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>