<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
/* ========== ENTERPRISE TABLE STYLING ========== */
:root {
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-900: #111827;
    --primary: #3b82f6;
}

/* Typography & Layout */
.text-xs { font-size: 0.75rem; }
.text-sm { font-size: 0.875rem; }
.font-medium { font-weight: 500; }
.font-semibold { font-weight: 600; }
.text-gray-500 { color: var(--gray-500); }
.text-gray-900 { color: var(--gray-900); }

/* Summary Cards (Clean Enterprise Look) */
.stat-card {
    background: #fff;
    border: 1px solid var(--gray-200);
    border-radius: 12px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}
.stat-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.icon-primary { background: #eff6ff; color: #3b82f6; }
.icon-warning { background: #fef3c7; color: #d97706; }
.icon-info { background: #e0f2fe; color: #0284c7; }
.icon-success { background: #dcfce7; color: #16a34a; }

/* Filter Section */
.filter-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--gray-200);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    margin-bottom: 24px;
}
.filter-card .form-control,
.filter-card .form-control-sm {
    border-radius: 8px;
    border: 1px solid var(--gray-300);
    height: 38px;
    font-size: 0.875rem;
}
.filter-card .form-control:focus,
.filter-card .form-control-sm:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.filter-card .form-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--gray-500);
    margin-bottom: 6px;
}

/* Modern Table Wrapper */
.modern-table-wrapper {
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--gray-200);
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    overflow: hidden;
    margin-bottom: 24px;
}

/* Table Toolbar (Controls above table) */
.table-toolbar {
    padding: 16px;
    border-bottom: 1px solid var(--gray-200);
    background-color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

/* Dropdown Baris per Halaman Modern */
.rows-dropdown {
    display: inline-block;
    width: auto;
    border-radius: 6px;
    border: 1px solid var(--gray-300);
    color: var(--gray-700);
    font-size: 0.85rem;
    font-weight: 500;
    padding: 6px 32px 6px 12px;
    background-color: #fff;
    cursor: pointer;
    outline: none;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 14px;
}
.rows-dropdown:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

/* Export Buttons */
.btn-export {
    border-radius: 6px;
    padding: 6px 14px;
    font-size: 0.85rem;
    font-weight: 500;
    border: 1px solid var(--gray-300);
    background: #fff;
    color: var(--gray-700);
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-export:hover {
    background: var(--gray-50);
    color: var(--gray-900);
    border-color: var(--gray-400);
}

/* The Table */
.modern-table {
    margin-bottom: 0;
    font-size: 0.875rem;
    color: var(--gray-700);
    width: 100%;
}
.modern-table thead th {
    background: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    color: var(--gray-500);
    padding: 14px 16px;
    white-space: nowrap;
}
.modern-table tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--gray-100);
}
.modern-table tbody tr:hover {
    background-color: var(--gray-50);
}

/* Table Footer & Pagination */
.table-footer {
    padding: 16px;
    background-color: #fff;
    border-top: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

/* Pagination Styling for CI4 */
.pagination-container ul {
    display: flex;
    gap: 6px;
    margin: 0;
    padding: 0;
    list-style: none;
    align-items: center;
}
.pagination-container li a,
.pagination-container li span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 34px;
    padding: 0 10px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--gray-600);
    border: 1px solid var(--gray-200);
    background: #fff;
    text-decoration: none;
    transition: all 0.2s ease;
}
.pagination-container li a:hover {
    background: var(--gray-50);
    border-color: var(--gray-300);
    color: var(--gray-900);
}
.pagination-container li.active a,
.pagination-container li.active span {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.25);
}
.pagination-container li.disabled a,
.pagination-container li.disabled span {
    color: var(--gray-400);
    background-color: var(--gray-50);
    border-color: var(--gray-100);
    pointer-events: none;
}

/* Badges */
.badge-soft {
    padding: 5px 12px;
    border-radius: 9999px;
    font-weight: 600;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}
.badge-mess { background: #eff6ff; color: #1d4ed8; }
.badge-workshop { background: #f3e8ff; color: #7e22ce; }
.badge-priority-segera { background: #fee2e2; color: #b91c1c; }
.badge-priority-normal { background: #fef3c7; color: #b45309; }
.badge-priority-rendah { background: #f3f4f6; color: #4b5563; }
.badge-status-approved { background: #dcfce7; color: #15803d; }
.badge-status-pending { background: #fef3c7; color: #b45309; }
.badge-status-rejected { background: #fee2e2; color: #b91c1c; }

/* Action Buttons */
.btn-action {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid transparent;
    background: transparent;
    color: var(--gray-500);
    transition: all 0.2s ease;
}
.btn-action:hover { transform: translateY(-1px); }
.btn-action-view:hover { background: #eff6ff; color: #3b82f6; border-color: #bfdbfe; }
.btn-action-edit:hover { background: #fef3c7; color: #d97706; border-color: #fde68a; }
.btn-action-delete:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

/* Resizable Columns */
.resizable-table th { position: relative; }
.resizable-table th .resizer {
    position: absolute;
    top: 0; right: 0;
    width: 4px;
    cursor: col-resize;
    user-select: none;
    height: 100%;
    background: transparent;
    transition: background 0.2s;
}
.resizable-table th .resizer:hover,
.resizable-table th .resizer:active {
    background: var(--gray-300);
}
</style>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3 class="font-semibold text-gray-900 mb-0">Pengajuan Perbaikan Aset</h3>
        <button type="button" class="btn btn-primary shadow-sm" style="border-radius: 8px;" id="btnPengajuanBaru">
            <i class="fas fa-plus mr-2"></i> Ajukan Perbaikan
        </button>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-wrapper icon-primary mr-3"><i class="fas fa-layer-group"></i></div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 mb-0 text-uppercase">Total Pengajuan</p>
                        <h4 class="font-semibold text-gray-900 mb-0"><?= $total_pengajuan ?? 0 ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-wrapper icon-warning mr-3"><i class="fas fa-clock"></i></div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 mb-0 text-uppercase">Menunggu</p>
                        <h4 class="font-semibold text-gray-900 mb-0"><?= $total_pending ?? 0 ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-wrapper icon-info mr-3"><i class="fas fa-shield-alt"></i></div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 mb-0 text-uppercase">Disetujui</p>
                        <h4 class="font-semibold text-gray-900 mb-0"><?= $total_approved ?? 0 ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-wrapper icon-success mr-3"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 mb-0 text-uppercase">Selesai</p>
                        <h4 class="font-semibold text-gray-900 mb-0"><?= $total_completed ?? 0 ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="filter-card">
    <div class="card-body p-3">
        <form method="get" id="filterFormSemua" class="mb-0">
            <div class="row align-items-end">
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="form-label">Tipe Aset</label>
                    <select name="tipe_aset" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <option value="Mess" <?= (isset($_GET['tipe_aset']) && $_GET['tipe_aset'] == 'Mess') ? 'selected' : '' ?>>Mess</option>
                        <option value="Workshop" <?= (isset($_GET['tipe_aset']) && $_GET['tipe_aset'] == 'Workshop') ? 'selected' : '' ?>>Workshop</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="form-label">Site/Lokasi</label>
                    <select name="site" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua Site</option>
                        <?php foreach($site_list ?? [] as $site): ?>
                            <option value="<?= esc($site['id']) ?>" <?= (isset($_GET['site']) && $_GET['site'] == $site['id']) ? 'selected' : '' ?>>
                                <?= esc($site['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="Pending" <?= (isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Approved" <?= (isset($_GET['status']) && $_GET['status'] == 'Approved') ? 'selected' : '' ?>>Approved</option>
                        <option value="Rejected" <?= (isset($_GET['status']) && $_GET['status'] == 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="form-label">Prioritas</label>
                    <select name="prioritas" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <option value="Rendah" <?= (isset($_GET['prioritas']) && $_GET['prioritas'] == 'Rendah') ? 'selected' : '' ?>>Rendah</option>
                        <option value="Normal" <?= (isset($_GET['prioritas']) && $_GET['prioritas'] == 'Normal') ? 'selected' : '' ?>>Normal</option>
                        <option value="Segera" <?= (isset($_GET['prioritas']) && $_GET['prioritas'] == 'Segera') ? 'selected' : '' ?>>Segera</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label">Cari Data</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px; border-color: var(--gray-300);">
                                <i class="fas fa-search text-gray-400"></i>
                            </span>
                        </div>
                        <input type="text" name="search" class="form-control border-left-0" style="border-radius: 0 8px 8px 0; border-color: var(--gray-300);" placeholder="Nama/NIK/Kode..." value="<?= $_GET['search'] ?? '' ?>">
                    </div>
                </div>
                <div class="col-md-1 mb-2 mb-md-0">
                    <?php if(!empty($_GET['tipe_aset']) || !empty($_GET['status']) || !empty($_GET['prioritas']) || !empty($_GET['site']) || !empty($_GET['search'])): ?>
                        <a href="<?= base_url('general-service/repair-request') ?>" class="btn btn-sm btn-outline-danger w-100" style="height: 38px; display: flex; align-items: center; justify-content: center; border-radius: 8px;" title="Reset Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modern-table-wrapper">
    <div class="table-toolbar">
        <div class="d-flex align-items-center" style="gap: 16px;">
            <?php $perPageCalc = (int) ($_GET['per_page'] ?? 10); ?>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <span style="font-size: 0.85rem; color: var(--gray-500);">Tampilkan</span>
                <select class="rows-dropdown" onchange="changePerPage(this.value)">
                    <option value="10" <?= ($perPageCalc == 10) ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= ($perPageCalc == 25) ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= ($perPageCalc == 50) ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= ($perPageCalc == 100) ? 'selected' : '' ?>>100</option>
                </select>
                <span style="font-size: 0.85rem; color: var(--gray-500);">data</span>
            </div>
            
            <div style="width: 1px; height: 20px; background-color: var(--gray-300);"></div>
            
            <div class="d-flex" style="gap: 8px;">
                <button class="btn-export" onclick="exportToExcel()">
                    <i class="fas fa-file-excel text-success"></i> Excel
                </button>
                <button class="btn-export" onclick="exportToPDF()">
                    <i class="fas fa-file-pdf text-danger"></i> PDF
                </button>
            </div>
        </div>
        
        <div class="text-sm text-gray-500">
            Total <span style="font-weight: 600; color: var(--gray-900);"><?= $total_pengajuan ?? 0 ?></span> pengajuan
        </div>
    </div>

    <div class="table-responsive">
        <table class="table modern-table resizable-table mb-0" id="repairTable">
            <thead>
                <tr>
                    <th width="50">No<div class="resizer"></div></th>
                    <th>Kode Pengajuan<div class="resizer"></div></th>
                    <th>Tipe<div class="resizer"></div></th>
                    <th>Lokasi<div class="resizer"></div></th>
                    <th>Penanggung Jawab<div class="resizer"></div></th>
                    <th>Jenis Kerusakan<div class="resizer"></div></th>
                    <th>Prioritas<div class="resizer"></div></th>
                    <th>Tgl Pengajuan<div class="resizer"></div></th>
                    <th>Status<div class="resizer"></div></th>
                    <th style="text-align: right;" width="120" data-noresize>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($repairs)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div class="text-gray-400 mb-2"><i class="fas fa-inbox fa-3x"></i></div>
                            <p class="text-gray-500 mb-0 font-medium">Belum ada data pengajuan</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $currentPageCalc = (int) ($_GET['page'] ?? 1);
                    $offset = ($currentPageCalc - 1) * $perPageCalc;
                    foreach ($repairs as $i => $item): 
                    ?>
                    <tr>
                        <td class="text-gray-500"><?= $offset + $i + 1 ?></td>
                        <td class="font-medium text-gray-900"><?= esc($item['kode_pengajuan'] ?? '') ?></td>
                        <td>
                            <?php if($item['tipe_aset'] == 'Mess'): ?>
                                <span class="badge-soft badge-mess"><i class="fas fa-home" style="font-size: 0.7rem;"></i> Mess</span>
                            <?php else: ?>
                                <span class="badge-soft badge-workshop"><i class="fas fa-tools" style="font-size: 0.7rem;"></i> Workshop</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($item['site_name'] ?? '-') ?></td>
                        <td>
                            <div class="font-medium text-gray-900"><?= esc($item['nama_karyawan'] ?? '') ?></div>
                            <div class="text-xs text-gray-500">NIK: <?= esc($item['nik'] ?? '') ?></div>
                        </td>
                        <td class="text-gray-700"><?= esc($item['jenis_kerusakan'] ?? '') ?></td>
                        <td>
                            <?php
                            $prioritas = $item['prioritas'] ?? 'Normal';
                            $prioritasClass = 'badge-priority-normal';
                            if($prioritas == 'Segera') $prioritasClass = 'badge-priority-segera';
                            elseif($prioritas == 'Rendah') $prioritasClass = 'badge-priority-rendah';
                            ?>
                            <span class="badge-soft <?= $prioritasClass ?>"><?= $prioritas ?></span>
                        </td>
                        <td class="text-gray-500"><?= date('d M Y', strtotime($item['tanggal_pengajuan'] ?? 'now')) ?></td>
                        <td>
                            <?php
                            $status = $item['status'] ?? 'Pending';
                            $statusClass = 'badge-status-pending';
                            if($status == 'Approved') $statusClass = 'badge-status-approved';
                            elseif($status == 'Rejected') $statusClass = 'badge-status-rejected';
                            ?>
                            <span class="badge-soft <?= $statusClass ?>"><?= $status ?></span>
                        </td>
                        <td style="text-align: right;">
                            <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                <button type="button" class="btn-action btn-action-view" 
                                        onclick="viewDetail(<?= $item['id'] ?>)"
                                        data-toggle="tooltip" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if($item['status'] == 'Pending'): ?>
                                <button type="button" class="btn-action btn-action-edit"
                                    onclick="window.location.href='<?= base_url('general-service/repair-request/edit/' . $item['id']) ?>'"
                                    data-toggle="tooltip" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn-action btn-action-delete" 
                                        onclick="deletePengajuan(<?= $item['id'] ?>, '<?= esc($item['kode_pengajuan'] ?? '') ?>')"
                                        data-toggle="tooltip" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($pager) && !empty($repairs)): ?>
    <div class="table-footer">
        <div class="text-sm text-gray-500">
            <?php 
            $startData = $offset + 1;
            $endData = min($offset + $perPageCalc, $total_pengajuan);
            ?>
            Menampilkan <span class="font-medium text-gray-900"><?= $startData ?></span> - <span class="font-medium text-gray-900"><?= $endData ?></span> dari <span class="font-medium text-gray-900"><?= $total_pengajuan ?? 0 ?></span> data
        </div>
        
        <nav class="pagination-container">
            <?= $pager->links() ?>
        </nav>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnPengajuan = document.getElementById('btnPengajuanBaru');
    if (btnPengajuan) {
        btnPengajuan.addEventListener('click', function () {
            window.location.href = '<?= base_url('general-service/repair-request/create') ?>';
        });
    }
    
    // Initialize tooltips (Jika menggunakan jQuery/Bootstrap 4)
    if (typeof $ !== 'undefined' && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Resizable columns script
    const table = document.getElementById('repairTable');
    if (table) {
        const cols = table.querySelectorAll('th');
        cols.forEach((col) => {
            const resizer = col.querySelector('.resizer');
            if (!resizer) return;
            
            let x = 0;
            let w = 0;
            
            const mouseDownHandler = function(e) {
                x = e.clientX;
                const styles = window.getComputedStyle(col);
                w = parseInt(styles.width, 10);
                
                document.addEventListener('mousemove', mouseMoveHandler);
                document.addEventListener('mouseup', mouseUpHandler);
            };
            
            const mouseMoveHandler = function(e) {
                const dx = e.clientX - x;
                col.style.width = `${w + dx}px`;
            };
            
            const mouseUpHandler = function() {
                document.removeEventListener('mousemove', mouseMoveHandler);
                document.removeEventListener('mouseup', mouseUpHandler);
                
                // Save to localStorage
                const widths = {};
                cols.forEach((c, index) => {
                    widths[index] = c.style.width;
                });
                localStorage.setItem('repairTableColumnWidths', JSON.stringify(widths));
            };
            
            resizer.addEventListener('mousedown', mouseDownHandler);
        });
        
        // Load saved column widths
        const widths = JSON.parse(localStorage.getItem('repairTableColumnWidths') || '{}');
        cols.forEach((col, index) => {
            if (widths[index]) {
                col.style.width = widths[index];
            }
        });
    }
});
function viewDetail(id) {
    window.location.href = '<?= base_url('general-service/repair-request/detail/') ?>' + id;
}
function deletePengajuan(id, kode) {
    if (!confirm('Yakin ingin menghapus pengajuan ' + kode + '?')) return;

    fetch('<?= base_url('general-service/repair-request/delete/') ?>' + id, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Gagal: ' + data.message);
        }
    })
    .catch(() => alert('Terjadi kesalahan jaringan'));
}
function changePerPage(limit) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', limit);
    url.searchParams.set('page', 1); // Reset ke halaman 1
    window.location.href = url.toString();
}
</script>

<?= $this->endSection() ?>