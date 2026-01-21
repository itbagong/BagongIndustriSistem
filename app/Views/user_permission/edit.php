<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content-header mb-4">
    <h1 class="m-0 text-dark"><?= esc($title) ?></h1>
    <p class="text-muted small">Kelola hak akses khusus (spesial) untuk user ini di luar Role utamanya.</p>
</div>

<div class="permission-container">
    <div class="row-custom">
        
        <div class="col-left">
            <div class="card card-user-info sticky-top-custom">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0"><i class="fas fa-user-circle mr-2"></i> User Profile</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <small class="text-muted d-block">Username</small>
                            <span class="font-weight-bold text-dark"><?= esc($user['username']) ?></span>
                        </div>
                        <div class="list-group-item">
                            <small class="text-muted d-block">Email</small>
                            <span class="text-dark"><?= esc($user['email'] ?? '-') ?></span>
                        </div>
                        <div class="list-group-item">
                            <small class="text-muted d-block">Role Utama</small>
                            <span class="badge badge-info px-2 py-1"><?= esc($user['role_name'] ?? 'No Role') ?></span>
                        </div>
                        <div class="list-group-item bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Dari Role:</small>
                                <span class="badge badge-secondary"><?= count($rolePermissions) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted">Khusus (Custom):</small>
                                <span class="badge badge-primary" id="customPermCount"><?= count($userPermissions) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= base_url('user-permissions') ?>" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> Kembali ke List
                    </a>
                </div>
            </div>
        </div>

        <div class="col-right">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0"><i class="fas fa-shield-alt mr-2"></i> Akses & Izin</h5>
                    
                    <div class="search-box mt-2 mt-md-0">
                        <input type="text" id="searchPerm" class="form-control form-control-sm" placeholder="🔍 Cari permission...">
                    </div>
                </div>

                <div class="card-body bg-light-gray">
                    <form id="permissionForm">
                        
                        <div class="alert alert-info border-0 shadow-sm mb-4">
                            <div class="d-flex">
                                <div class="mr-3"><i class="fas fa-info-circle fa-2x"></i></div>
                                <div>
                                    <strong>Panduan:</strong> Tombol yang <strong>terkunci (abu-abu)</strong> adalah bawaan Role (tidak bisa diubah di sini). 
                                    Aktifkan tombol hijau untuk memberikan akses tambahan spesial.
                                </div>
                            </div>
                        </div>

                        <div id="permissionList">
                            <?php
                            // Grouping Permission
                            $grouped = [];
                            foreach ($allPermissions as $perm) {
                                $parts = explode('.', $perm['name']);
                                $category = ucfirst($parts[0] ?? 'Lainnya');
                                $grouped[$category][] = $perm;
                            }
                            ?>

                            <?php foreach ($grouped as $category => $permissions): ?>
                            <div class="category-section mb-4">
                                <h6 class="category-header text-uppercase text-secondary font-weight-bold border-bottom pb-2 mb-3">
                                    <?= esc($category) ?>
                                </h6>
                                
                                <div class="permission-grid">
                                    <?php foreach ($permissions as $perm): ?>
                                        <?php
                                        $isFromRole = in_array($perm['name'], $rolePermissions, true);
                                        $isUserPerm = in_array($perm['name'], $userPermissions, true);
                                        $isChecked  = $isFromRole || $isUserPerm;
                                        ?>
                                        
                                        <div class="permission-card permission-item <?= $isFromRole ? 'is-role' : '' ?>" data-name="<?= esc(strtolower($perm['name'])) ?>">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="perm-text pr-2">
                                                    <strong class="d-block text-dark"><?= esc($perm['name']) ?></strong>
                                                    <small class="text-muted" style="line-height: 1.2; display:block; margin-top:4px;">
                                                        <?= esc($perm['description'] ?? 'No description') ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="perm-switch">
                                                    <label class="switch">
                                                        <input type="checkbox" 
                                                               name="permissions[]" 
                                                               value="<?= esc($perm['id']) ?>"
                                                               <?= $isChecked ? 'checked' : '' ?>
                                                               <?= $isFromRole ? 'disabled' : '' ?>>
                                                        <span class="slider round <?= $isFromRole ? 'role-locked' : '' ?>"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <?php if ($isFromRole): ?>
                                                <div class="role-badge"><i class="fas fa-lock"></i> Role</div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="form-actions-sticky">
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-outline-danger btn-sm" id="clearBtn">
                                    <i class="fas fa-trash-alt"></i> Reset Custom
                                </button>
                                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Layout Grid Responsif */
    .row-custom {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -15px;
    }
    .col-left {
        width: 100%;
        padding: 0 15px;
        margin-bottom: 20px;
    }
    .col-right {
        width: 100%;
        padding: 0 15px;
    }
    
    /* Desktop View */
    @media (min-width: 992px) {
        .col-left { width: 30%; }
        .col-right { width: 70%; }
        .sticky-top-custom {
            position: sticky;
            top: 20px; /* Jarak dari atas saat scroll */
            z-index: 10;
        }
    }

    /* Card Styling */
    .card-user-info { box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: none; }
    .bg-light-gray { background-color: #f8f9fa; }
    
    /* Grid Permission Cards */
    .permission-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 15px;
    }

    .permission-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        position: relative;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .permission-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border-color: #dee2e6;
    }

    .permission-card.is-role {
        background-color: #f1f3f5;
        border-color: #e9ecef;
    }

    .role-badge {
        position: absolute;
        top: 0;
        right: 0;
        background: #6c757d;
        color: white;
        font-size: 0.65rem;
        padding: 2px 6px;
        border-bottom-left-radius: 6px;
        border-top-right-radius: 6px;
    }

    /* MODERN TOGGLE SWITCH CSS */
    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
    }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc;
        transition: .4s;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    input:checked + .slider { background-color: #28a745; }
    input:focus + .slider { box-shadow: 0 0 1px #28a745; }
    input:checked + .slider:before { transform: translateX(22px); }
    
    /* Rounded sliders */
    .slider.round { border-radius: 34px; }
    .slider.round:before { border-radius: 50%; }

    /* Role Locked Switch */
    .slider.role-locked {
        background-color: #17a2b8 !important; /* Biru Info */
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Sticky Footer Action Bar */
    .form-actions-sticky {
        position: sticky;
        bottom: 0;
        background: rgba(255, 255, 255, 0.95);
        border-top: 1px solid #dee2e6;
        padding: 15px;
        margin: 20px -20px -20px -20px; /* Negatif margin untuk full width card body */
        backdrop-filter: blur(5px);
        z-index: 100;
        box-shadow: 0 -4px 10px rgba(0,0,0,0.05);
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Fitur Pencarian Cepat
    const searchInput = document.getElementById('searchPerm');
    searchInput.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const items = document.querySelectorAll('.permission-item');
        
        items.forEach(item => {
            const name = item.getAttribute('data-name');
            // Tampilkan/Sembunyikan Card
            if(name.includes(term)) {
                item.style.display = 'block';
                item.parentElement.style.display = 'grid'; // Pastikan grid parent tampil
            } else {
                item.style.display = 'none';
            }
        });

        // Sembunyikan judul kategori jika semua item di dalamnya hidden
        document.querySelectorAll('.category-section').forEach(section => {
            const visibleItems = section.querySelectorAll('.permission-item[style="display: block;"]');
            const defaultVisible = section.querySelectorAll('.permission-item:not([style*="display: none"])');
            
            // Logika sederhana: jika tidak ada item visible, sembunyikan satu section
            if(visibleItems.length === 0 && defaultVisible.length === 0 && term !== '') {
                section.style.display = 'none';
            } else {
                section.style.display = 'block';
            }
        });
    });

    // 2. Submit Form dengan SweetAlert2
    const form = document.getElementById('permissionForm');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Tampilkan Loading
        Swal.fire({
            title: 'Menyimpan Perubahan...',
            text: 'Mohon tunggu sebentar',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading() }
        });
        
        const formData = new FormData(this);
        const userId = <?= $user['id'] ?>;
        
        try {
            const response = await fetch(`<?= base_url('user-permissions/update/') ?>${userId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Update badge counter di panel kiri
                const checkedCustom = document.querySelectorAll('input[name="permissions[]"]:checked:not(:disabled)').length;
                document.getElementById('customPermCount').textContent = checkedCustom;
                
            } else {
                Swal.fire('Gagal!', result.message || 'Terjadi kesalahan sistem', 'error');
            }
        } catch (error) {
            Swal.fire('Error!', 'Gagal menghubungi server: ' + error.message, 'error');
        }
    });

    // 3. Tombol Reset / Clear
    document.getElementById('clearBtn').addEventListener('click', function() {
        Swal.fire({
            title: 'Reset Permission Khusus?',
            text: "User hanya akan memiliki hak akses bawaan dari Role-nya saja.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Uncheck semua checkbox yang tidak disabled
                document.querySelectorAll('input[name="permissions[]"]:not(:disabled)').forEach(cb => {
                    cb.checked = false;
                });
                // Trigger submit otomatis untuk simpan ke DB
                form.dispatchEvent(new Event('submit'));
            }
        });
    });
});
</script>

<?= $this->endSection() ?>