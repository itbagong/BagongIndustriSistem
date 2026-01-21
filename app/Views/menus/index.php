<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content-header d-flex justify-content-between align-items-center mb-3">
    <h1>Manajemen Menu Sidebar</h1>
    <a href="<?= base_url('menus/new') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Menu
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Icon</th>
                    <th>Nama Menu</th>
                    <th>Route (URL)</th>
                    <th>Permission Kunci</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menus as $m): ?>
                    <tr style="<?= $m['parent_id'] ? 'background-color: #f9f9f9;' : '' ?>">
                        <td class="text-center">
                            <i class="<?= esc($m['icon']) ?> fa-lg text-secondary"></i>
                        </td>
                        <td>
                            <?php if($m['parent_id']): ?>
                                <span class="text-muted ml-3">↳ </span> <?php endif; ?>
                            <strong><?= esc($m['name']) ?></strong>
                        </td>
                        <td><code><?= esc($m['route']) ?></code></td>
                        <td>
                            <?php if($m['permission']): ?>
                                <span class="badge badge-warning"><?= esc($m['permission']) ?></span>
                            <?php else: ?>
                                <span class="badge badge-success">Public</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($m['sort_order']) ?></td>
                        <td>
                            <?php if($m['is_active']): ?>
                                <span class="badge badge-primary">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <a href="<?= base_url('menus/edit/'.$m['id']) ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteMenu(<?= $m['id'] ?>)" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deleteMenu(id) {
    Swal.fire({
        title: 'Hapus Menu?',
        text: "Menu akan hilang dari sidebar.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= base_url('menus/delete/') ?>${id}`, { method: 'DELETE' })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Deleted!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            });
        }
    })
}
</script>
<?= $this->endSection() ?>