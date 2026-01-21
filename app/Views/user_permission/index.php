<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="content-header">
    <h1><?= esc($title) ?></h1>
    <p class="text-muted">Kelola permission khusus untuk user tertentu</p>
</div>

<div class="card">
    <div class="card-header">
        <h3>Daftar User</h3>
        <div class="card-tools">
            <input type="text" id="searchUser" class="form-control" placeholder="Cari user...">
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="userTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= esc($user['id']) ?></td>
                        <td>
                            <strong><?= esc($user['username']) ?></strong>
                        </td>
                        <td><?= esc($user['email'] ?? '-') ?></td>
                        <td>
                            <span class="badge badge-info">
                                <?= esc($user['role_name'] ?? 'No Role') ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= base_url('user-permissions/edit/' . $user['id']) ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-key"></i> Kelola Permissions
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    font-size: 1.25rem;
}

.card-body {
    padding: 20px;
}

.card-tools {
    display: flex;
    gap: 10px;
}

.form-control {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 250px;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table thead th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.table tbody td {
    padding: 12px;
    border-bottom: 1px solid #e9ecef;
}

.table-hover tbody tr:hover {
    background: #f8f9fa;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85em;
    font-weight: 500;
}

.badge-info {
    background: #17a2b8;
    color: white;
}

.badge-success {
    background: #28a745;
    color: white;
}

.badge-danger {
    background: #dc3545;
    color: white;
}

.btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 0.9rem;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 0.85rem;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.content-header {
    margin-bottom: 20px;
}

.content-header h1 {
    margin: 0 0 5px 0;
}

.text-muted {
    color: #6c757d;
}
</style>

<script>
// Simple search functionality
document.getElementById('searchUser').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#userTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?= $this->endSection() ?>