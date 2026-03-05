<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/planner-site.css') ?>">

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="font-semibold text-gray-900 mb-0">
                <i class="fas fa-hard-hat mr-2 text-warning"></i>List Pekerjaan Saya
            </h3>
            <small class="text-muted">Pengajuan yang sudah disetujui dan siap dikerjakan</small>
        </div>
    </div>
</div>

<!-- STAT CARDS -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-wrapper icon-primary mr-3">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 mb-0 text-uppercase">Total Pekerjaan</p>
                        <h4 class="font-semibold text-gray-900 mb-0"><?= $stat_total ?? 0 ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-wrapper icon-info mr-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 mb-0 text-uppercase">Approved</p>
                        <h4 class="font-semibold text-gray-900 mb-0"><?= $stat_approved ?? 0 ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-wrapper icon-warning mr-3">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 mb-0 text-uppercase">In Progress</p>
                        <h4 class="font-semibold text-gray-900 mb-0"><?= $stat_inprogress ?? 0 ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-wrapper icon-success mr-3">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 mb-0 text-uppercase">Completed</p>
                        <h4 class="font-semibold text-gray-900 mb-0"><?= $stat_completed ?? 0 ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FILTER -->
<div class="filter-card mb-3">
    <div class="card-body p-3">
        <form method="get" id="filterForm" class="mb-0">
            <div class="row align-items-end">
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="form-label">Tipe Aset</label>
                    <select name="tipe_aset" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <option value="Mess"     <?= (($_GET['tipe_aset'] ?? '') == 'Mess')     ? 'selected' : '' ?>>Mess</option>
                        <option value="Workshop" <?= (($_GET['tipe_aset'] ?? '') == 'Workshop') ? 'selected' : '' ?>>Workshop</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="form-label">Site/Lokasi</label>
                    <select name="site" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua Site</option>
                        <?php foreach ($site_list ?? [] as $site): ?>
                            <option value="<?= esc($site['id']) ?>" <?= (($_GET['site'] ?? '') == $site['id']) ? 'selected' : '' ?>>
                                <?= esc($site['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <option value="Approved"    <?= (($_GET['status'] ?? '') == 'Approved')    ? 'selected' : '' ?>>Approved</option>
                        <option value="In Progress" <?= (($_GET['status'] ?? '') == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                        <option value="Completed"   <?= (($_GET['status'] ?? '') == 'Completed')   ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="form-label">Prioritas</label>
                    <select name="prioritas" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <option value="Segera" <?= (($_GET['prioritas'] ?? '') == 'Segera') ? 'selected' : '' ?>>Segera</option>
                        <option value="Normal" <?= (($_GET['prioritas'] ?? '') == 'Normal') ? 'selected' : '' ?>>Normal</option>
                        <option value="Rendah" <?= (($_GET['prioritas'] ?? '') == 'Rendah') ? 'selected' : '' ?>>Rendah</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label">Cari</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0" style="border-radius:8px 0 0 8px;">
                                <i class="fas fa-search text-gray-400"></i>
                            </span>
                        </div>
                        <input type="text" name="search" class="form-control border-left-0"
                               style="border-radius:0 8px 8px 0;"
                               placeholder="Kode/Nama/Jenis..."
                               value="<?= esc($_GET['search'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-1 mb-2 mb-md-0">
                    <?php if (!empty($_GET['tipe_aset']) || !empty($_GET['site']) || !empty($_GET['status']) || !empty($_GET['prioritas']) || !empty($_GET['search'])): ?>
                        <a href="<?= base_url('general-service/planner-site') ?>"
                           class="btn btn-sm btn-outline-danger w-100"
                           style="height:38px; display:flex; align-items:center; justify-content:center; border-radius:8px;"
                           title="Reset Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- TABLE -->
<div class="modern-table-wrapper">
    <div class="table-toolbar">
        <div class="d-flex align-items-center" style="gap:16px;">
            <?php $perPageCalc = (int)($_GET['per_page'] ?? 10); ?>
            <div class="d-flex align-items-center" style="gap:8px;">
                <span style="font-size:0.85rem; color:var(--gray-500);">Tampilkan</span>
                <select class="rows-dropdown" onchange="changePerPage(this.value)">
                    <option value="10"  <?= ($perPageCalc == 10)  ? 'selected' : '' ?>>10</option>
                    <option value="25"  <?= ($perPageCalc == 25)  ? 'selected' : '' ?>>25</option>
                    <option value="50"  <?= ($perPageCalc == 50)  ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= ($perPageCalc == 100) ? 'selected' : '' ?>>100</option>
                </select>
                <span style="font-size:0.85rem; color:var(--gray-500);">data</span>
            </div>
        </div>
        <div class="text-sm text-gray-500">
            Total <span style="font-weight:600; color:var(--gray-900);"><?= $stat_total ?? 0 ?></span> pekerjaan
        </div>
    </div>

    <div class="table-responsive">
        <table class="table modern-table mb-0" id="plannerSiteTable">
            <thead>
                <tr>
                    <th width="45">No</th>
                    <th>Kode</th>
                    <th>Tipe</th>
                    <th>Lokasi</th>
                    <th>Penanggung Jawab</th>
                    <th>Jenis Kerusakan</th>
                    <th>Prioritas</th>
                    <th>Tgl Disetujui</th>
                    <th>Status</th>
                    <th>Dok. TTD</th>
                    <th class="text-right" width="100">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($repairs)): ?>
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <div class="text-gray-400 mb-2"><i class="fas fa-inbox fa-3x"></i></div>
                            <p class="text-gray-500 mb-0">Belum ada pekerjaan yang perlu dikerjakan</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php
                    $currentPageCalc = (int)($_GET['page'] ?? 1);
                    $offset = ($currentPageCalc - 1) * $perPageCalc;
                    foreach ($repairs as $i => $item):
                        $status   = $item['status'] ?? 'Approved';
                        $prioritas = $item['prioritas'] ?? 'Normal';

                        $statusClass = match($status) {
                            'Approved'    => 'badge-status-approved',
                            'In Progress' => 'badge-status-inprogress',
                            'Completed'   => 'badge-status-completed',
                            default       => 'badge-status-pending',
                        };
                        $prioritasClass = match($prioritas) {
                            'Segera' => 'badge-priority-segera',
                            'Rendah' => 'badge-priority-rendah',
                            default  => 'badge-priority-normal',
                        };
                    ?>
                    <tr>
                        <td class="text-gray-500"><?= $offset + $i + 1 ?></td>
                        <td class="font-medium text-gray-900"><?= esc($item['kode_pengajuan'] ?? '-') ?></td>
                        <td>
                            <?php if ($item['tipe_aset'] == 'Mess'): ?>
                                <span class="badge-soft badge-mess"><i class="fas fa-home" style="font-size:0.7rem;"></i> Mess</span>
                            <?php else: ?>
                                <span class="badge-soft badge-workshop"><i class="fas fa-tools" style="font-size:0.7rem;"></i> Workshop</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($item['site_name'] ?? '-') ?></td>
                        <td>
                            <div class="font-medium text-gray-900"><?= esc($item['nama_karyawan'] ?? '-') ?></div>
                            <div class="text-xs text-gray-500">NIK: <?= esc($item['nik'] ?? '-') ?></div>
                        </td>
                        <td class="text-gray-700"><?= esc($item['jenis_kerusakan'] ?? '-') ?></td>
                        <td>
                            <span class="badge-soft <?= $prioritasClass ?>"><?= $prioritas ?></span>
                        </td>
                        <td class="text-gray-500">
                            <?= !empty($item['tanggal_disetujui'])
                                ? date('d M Y', strtotime($item['tanggal_disetujui']))
                                : '-' ?>
                        </td>
                        <td>
                            <span class="badge-soft <?= $statusClass ?>"><?= $status ?></span>
                        </td>

                        <!-- Kolom Dokumen TTD -->
                        <td class="text-center">
                            <?php if (!empty($item['dokumen_ttd_path'])): ?>
                                <a href="<?= base_url($item['dokumen_ttd_path']) ?>" target="_blank"
                                   class="btn btn-xs btn-outline-success" title="Lihat Dokumen TTD">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:0.75rem;">
                                    <i class="fas fa-minus"></i>
                                </span>
                            <?php endif; ?>
                        </td>

                        <td class="text-right">
                            <div class="d-inline-flex align-items-center" style="gap:4px;">
                                <!-- Tombol utama: Kerjakan / Lihat Detail -->
                                <?php if ($status === 'Approved'): ?>
                                    <button type="button"
                                            class="btn btn-xs btn-info font-weight-bold"
                                            onclick="kerjakan(<?= $item['id'] ?>)"
                                            title="Mulai Kerjakan"
                                            style="border-radius:6px; padding:4px 10px;">
                                        <i class="fas fa-play mr-1"></i> Kerjakan
                                    </button>
                                <?php elseif ($status === 'In Progress'): ?>
                                    <button type="button"
                                            class="btn btn-xs btn-warning font-weight-bold"
                                            onclick="kerjakan(<?= $item['id'] ?>)"
                                            title="Update Pekerjaan"
                                            style="border-radius:6px; padding:4px 10px;">
                                        <i class="fas fa-tools mr-1"></i> Update
                                    </button>
                                <?php else: ?>
                                    <button type="button"
                                            class="btn-action btn-action-view"
                                            onclick="kerjakan(<?= $item['id'] ?>)"
                                            title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                <?php endif; ?>

                                <!-- Cetak -->
                                <a href="<?= base_url('general-service/repair-request/print/' . $item['id']) ?>"
                                   target="_blank"
                                   class="btn-action"
                                   style="text-decoration:none;"
                                   title="Cetak">
                                    <i class="fas fa-print" style="font-size:0.8rem;"></i>
                                </a>
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
            $endData   = min($offset + $perPageCalc, $stat_total);
            ?>
            Menampilkan
            <span class="font-medium text-gray-900"><?= $startData ?></span> -
            <span class="font-medium text-gray-900"><?= $endData ?></span> dari
            <span class="font-medium text-gray-900"><?= $stat_total ?? 0 ?></span> data
        </div>
        <nav class="pagination-container">
            <?= $pager->links() ?>
        </nav>
    </div>
    <?php endif; ?>
</div>

<script>
function kerjakan(id) {
    window.location.href = '<?= base_url('general-service/repair-request/detail/') ?>' + id;
}
function changePerPage(limit) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', limit);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ !== 'undefined' && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }
});
</script>

<!-- CSS tambahan untuk status badge yang belum ada di planner-site.css -->
<style>
.badge-status-inprogress { background:#ede9fe; color:#6d28d9; }
.badge-status-completed  { background:#dcfce7; color:#166534; }
</style>

<?= $this->endSection() ?>