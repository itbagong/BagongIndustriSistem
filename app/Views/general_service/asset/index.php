    <?= $this->extend('layouts/main') ?>

    <?= $this->section('content') ?>
    <style>
    @keyframes shimmer {
        0%,100% { opacity:1; }
        50% { opacity:0.4; }
    }
    </style>
    <div class="content-header mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-building mr-2"></i>Data Aset General Service</h1>
            <div>
                <button type="button" class="btn btn-primary" id="btnAddMess">
                    <i class="fas fa-plus"></i> Tambah Mess
                </button>
                <button type="button" class="btn btn-success" id="btnAddWorkshop">
                    <i class="fas fa-plus"></i> Tambah Workshop
                </button>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card bg-gradient-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Aset</h6>
                            <h3 class="mb-0"><?= ($total_mess ?? 0) + ($total_workshop ?? 0) ?></h3>
                            <small>Mess & Workshop</small>
                        </div>
                        <i class="fas fa-layer-group fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-gradient-info text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Mess</h6>
                            <h3 class="mb-0"><?= $total_mess ?? 0 ?></h3>
                            <small><?= number_format($total_luas_mess ?? 0, 0, ',', '.') ?> m²</small>
                        </div>
                        <i class="fas fa-home fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-gradient-warning text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Workshop</h6>
                            <h3 class="mb-0"><?= $total_workshop ?? 0 ?></h3>
                            <small><?= number_format($total_luas_workshop ?? 0, 0, ',', '.') ?> m²</small>
                        </div>
                        <i class="fas fa-tools fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-gradient-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Milik Perusahaan</h6>
                            <h3 class="mb-0"><?= ($total_milik_mess ?? 0) + ($total_milik_workshop ?? 0) ?></h3>
                            <small><?= ($total_sewa_mess ?? 0) + ($total_sewa_workshop ?? 0) ?> Sewa</small>
                        </div>
                        <i class="fas fa-building fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" id="assetTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="mess-tab" data-toggle="tab" href="#mess" role="tab">
                        <i class="fas fa-home"></i> Data Mess
                        <span class="badge badge-primary ml-1"><?= $total_mess ?? 0 ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="workshop-tab" data-toggle="tab" href="#workshop" role="tab">
                        <i class="fas fa-tools"></i> Data Workshop
                        <span class="badge badge-success ml-1"><?= $total_workshop ?? 0 ?></span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="assetTabContent">
                
                <div class="tab-pane fade show active" id="mess" role="tabpanel">
                    
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <form method="get" id="filterFormMess">
                                <input type="hidden" name="tab" value="mess">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Divisi</label>
                                        <select name="mess_divisi" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="">Semua Divisi</option>
                                            <?php foreach($divisi_list ?? [] as $div): ?>
                                                <option value="<?= $div['id'] ?>" <?= (isset($_GET['mess_divisi']) && $_GET['mess_divisi'] == $div['id']) ? 'selected' : '' ?>>
                                                    <?= esc($div['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Job Site</label>
                                        <select name="mess_job_site" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="">Semua Job Site</option>
                                            <?php foreach($mess_job_sites ?? [] as $site): ?>
                                                <option value="<?= $site ?>" <?= (isset($_GET['mess_job_site']) && $_GET['mess_job_site'] == $site) ? 'selected' : '' ?>>
                                                    <?= esc($site) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Status</label>
                                        <select name="mess_status" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="">Semua</option>
                                            <option value="Milik PT Bagong Dekaka Makmur" <?= (isset($_GET['mess_status']) && $_GET['mess_status'] == 'Milik PT Bagong Dekaka Makmur') ? 'selected' : '' ?>>Milik</option>
                                            <option value="Sewa" <?= (isset($_GET['mess_status']) && $_GET['mess_status'] == 'Sewa') ? 'selected' : '' ?>>Sewa</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Cari</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="mess_search" class="form-control" placeholder="Nama/NIK..." value="<?= $_GET['mess_search'] ?? '' ?>">
                                            <button class="btn btn-outline-secondary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label d-block">&nbsp;</label>
                                        <?php if(!empty($_GET['mess_divisi']) || !empty($_GET['mess_job_site']) || !empty($_GET['mess_status']) || !empty($_GET['mess_search'])): ?>
                                            <a href="<?= base_url('general-service?tab=mess') ?>" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-sm btn-outline-success mr-2" onclick="exportMessToExcel()">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="exportMessToPDF()">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                    </div> -->

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">No</th>
                                    <th>ID Mess</th>
                                    <th>Job Site</th>
                                    <th>PIC</th>
                                    <th>NIK</th>
                                    <th class="text-center">Luas</th>
                                    <th class="text-center">Kamar</th>
                                    <th class="text-center">Fasilitas</th>
                                    <th>Status</th>
                                    <th class="text-center" width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($mess_data)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Belum ada data mess</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($mess_data as $i => $mess): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><small><?= esc($mess['mess_code'] ?? '-') ?></small></td>
                                        <td><small class="text-muted"><?= esc($mess['site_name'] ?? '-') ?></small></td>
                                        <td><strong><?= esc($mess['nama_karyawan'] ?? '-') ?></strong></td>
                                        <td><span class="badge badge-secondary"><?= esc($mess['nik'] ?? '-') ?></span></td>
                                        <td class="text-center"><strong><?= number_format($mess['luasan_mess'] ?? 0, 0) ?></strong> m²</td>
                                        <td class="text-center">
                                            <small>
                                                <i class="fas fa-bed"></i> <?= $mess['jumlah_kamar_tidur'] ?? 0 ?>
                                                <i class="fas fa-bath ml-1"></i> <?= $mess['jumlah_kamar_mandi'] ?? 0 ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-xs btn-outline-info" 
                                                    data-toggle="popover" 
                                                    data-placement="left"
                                                    title="Fasilitas" 
                                                    data-content="<?= esc($mess['fasilitas'] ?? '-') ?>">
                                                <i class="fas fa-list"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <?php if(($mess['status_kepemilikan'] ?? '') == 'Milik PT Bagong Dekaka Makmur'): ?>
                                                <span class="badge badge-success badge-sm">Milik</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning badge-sm">Sewa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-info btn-sm" 
                                                        onclick="viewMessDetail(<?= $mess['id'] ?>)" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="<?= base_url('general-service/mess/edit/'.$mess['id']) ?>" 
                                                class="btn btn-warning btn-sm"
                                                data-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <!-- button download pengajuan perbaikan -->
                                                <button type="button" class="btn btn-secondary btn-sm" 
                                                       
                                                        data-toggle="tooltip" title="Download Pengajuan Perbaikan">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        onclick="deleteMessData(<?= $mess['id'] ?>, '<?= esc($mess['nama_karyawan'] ?? '') ?>')"
                                                        data-toggle="tooltip" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if(isset($pager_mess) && $total_mess > 10): ?>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div>
                                <small>Menampilkan <strong><?= count($mess_data) ?></strong> dari <strong><?= $total_mess ?? 0 ?></strong> data</small>
                            </div>
                            <div>
                                <?= $pager_mess->links('mess', 'default_full') ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="workshop" role="tabpanel">
                    
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <form method="get" id="filterFormWorkshop">
                                <input type="hidden" name="tab" value="workshop">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Divisi</label>
                                        <select name="workshop_divisi" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="">Semua Divisi</option>
                                            <?php foreach($divisi_list ?? [] as $div): ?>
                                                <option value="<?= $div['id'] ?>" <?= (isset($_GET['workshop_divisi']) && $_GET['workshop_divisi'] == $div['id']) ? 'selected' : '' ?>>
                                                    <?= esc($div['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Job Site</label>
                                        <select name="workshop_job_site" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="">Semua Job Site</option>
                                            <?php foreach($workshop_job_sites ?? [] as $site): ?>
                                                <option value="<?= $site ?>" <?= (isset($_GET['workshop_job_site']) && $_GET['workshop_job_site'] == $site) ? 'selected' : '' ?>>
                                                    <?= esc($site) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Status</label>
                                        <select name="workshop_status" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="">Semua</option>
                                            <option value="Milik PT Bagong Dekaka Makmur" <?= (isset($_GET['workshop_status']) && $_GET['workshop_status'] == 'Milik PT Bagong Dekaka Makmur') ? 'selected' : '' ?>>Milik</option>
                                            <option value="Sewa" <?= (isset($_GET['workshop_status']) && $_GET['workshop_status'] == 'Sewa') ? 'selected' : '' ?>>Sewa</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Cari</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="workshop_search" class="form-control" placeholder="Nama/NIK..." value="<?= $_GET['workshop_search'] ?? '' ?>">
                                            <button class="btn btn-outline-secondary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label d-block">&nbsp;</label>
                                        <?php if(!empty($_GET['workshop_divisi']) || !empty($_GET['workshop_job_site']) || !empty($_GET['workshop_status']) || !empty($_GET['workshop_search'])): ?>
                                            <a href="<?= base_url('general-service?tab=workshop') ?>" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-sm btn-outline-success mr-2" onclick="exportWorkshopToExcel()">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="exportWorkshopToPDF()">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                    </div> -->

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">No</th>
                                    <th>ID Workshop</th>
                                    <th>Job Site</th>
                                    <th>PIC</th>
                                    <th>NIK</th>
                                    <th class="text-center">Luas</th>
                                    <th class="text-center">Bays</th>
                                    <th class="text-center">Kompartemen</th>
                                    <th>Status</th>
                                    <th class="text-center" width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($workshop_data)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Belum ada data workshop</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($workshop_data as $index => $workshop): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><small><?= esc($workshop['workshop_code'] ?? '-') ?></small></td>
                                            <td><small class="text-muted"><?= esc($workshop['site_name'] ?? '-') ?></small></td>
                                            
                                            <td><strong><?= esc($workshop['name_karyawan'] ?? '-') ?></strong></td>
                                            
                                            <td><span class="badge badge-secondary"><?= esc($workshop['nik'] ?? '-') ?></span></td>
                                            <td class="text-center"><strong><?= number_format($workshop['luasan'] ?? 0, 0) ?></strong> m²</td>
                                            <td class="text-center">
                                                <span class="badge badge-light">
                                                    <i class="fas fa-car"></i> <?= $workshop['bays'] ?? 0 ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-xs btn-outline-info" 
                                                        data-toggle="popover" 
                                                        data-placement="left"
                                                        data-trigger="hover"
                                                        title="Kompartemen" 
                                                        data-html="true"
                                                        data-content="<?= esc(str_replace(',', '<br>', $workshop['kompartemen'] ?? '-')) ?>">
                                                    <i class="fas fa-list"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <?php if(($workshop['status_workshop'] ?? '') == 'Milik PT Bagong Dekaka Makmur'): ?>
                                                    <span class="badge badge-success badge-sm">Milik</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning badge-sm">Sewa</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-info btn-sm" 
                                                            onclick="viewWorkshopDetail(<?= $workshop['id'] ?>)" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="<?= base_url('general-service/workshop/edit/'.$workshop['id']) ?>" 
                                                    class="btn btn-warning btn-sm"
                                                    data-toggle="tooltip" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <!-- button download pengajuan perbaikan -->
                                                    <button type="button" class="btn btn-secondary btn-sm"
                                                            data-toggle="tooltip" title="Download Pengajuan Perbaikan">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                            onclick="deleteWorkshopData(<?= $workshop['id'] ?>, '<?= esc($workshop['name_karyawan'] ?? '') ?>')"
                                                            data-toggle="tooltip" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if(isset($pager_workshop) && $total_workshop > 10): ?>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div>
                                <small>Menampilkan <strong><?= count($workshop_data) ?></strong> dari <strong><?= $total_workshop ?? 0 ?></strong> data</small>
                            </div>
                            <div>
                                <?= $pager_workshop->links('workshop', 'default_full') ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- SATU MODAL UNTUK MESS & WORKSHOP -->
    <div class="modal fade" id="modalRiwayatPerbaikan" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" style="max-width:700px;">
            <div class="modal-content" style="border-radius:12px;overflow:hidden;">
                
                <div class="modal-header" id="modalRiwayatHeader" style="padding:16px 20px; border-bottom:1px solid #e5e7eb;">
                    <div>
                        <h6 class="mb-0 font-weight-bold" id="modalRiwayatTitle">Riwayat Perbaikan</h6>
                        <small id="modalRiwayatSubtitle" class="text-muted"></small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <!-- Search & Filter — sticky -->
                <div style="padding:12px 16px; border-bottom:1px solid #f0f0f0; background:#fafafa; position:sticky; top:0; z-index:10;">
                    <div class="d-flex" style="gap:8px; flex-wrap:wrap;">
                        <div style="position:relative; flex:1; min-width:160px;">
                            <i class="fas fa-search" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.75rem;"></i>
                            <input type="text" id="modalSearchInput" placeholder="Cari kode / deskripsi..."
                                oninput="filterModalList()"
                                style="width:100%;padding:6px 10px 6px 28px;border:1px solid #e5e7eb;border-radius:7px;font-size:0.82rem;outline:none;background:#fff;">
                        </div>
                        <select id="modalFilterStatus" onchange="filterModalList()"
                                style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:0.82rem;background:#fff;outline:none;cursor:pointer;">
                            <option value="">Semua Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <select id="modalFilterPrioritas" onchange="filterModalList()"
                                style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:0.82rem;background:#fff;outline:none;cursor:pointer;">
                            <option value="">Semua Prioritas</option>
                            <option value="Segera">Segera</option>
                            <option value="Normal">Normal</option>
                            <option value="Rendah">Rendah</option>
                        </select>
                    </div>
                    <div id="modalFilterInfo" style="font-size:0.75rem;color:#9ca3af;margin-top:6px;"></div>
                </div>

                <!-- List -->
                <div class="modal-body" style="padding:12px 16px; background:#f8fafc; min-height:300px;">
                    
                    <!-- Skeleton Loading -->
                    <div id="modalSkeleton">
                        <?php for($sk=0; $sk<4; $sk++): ?>
                        <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;margin-bottom:8px;background:#fff;">
                            <div style="height:12px;background:#f0f0f0;border-radius:6px;width:40%;margin-bottom:8px;animation:shimmer 1.2s infinite;"></div>
                            <div style="height:10px;background:#f0f0f0;border-radius:6px;width:80%;margin-bottom:6px;animation:shimmer 1.2s infinite;"></div>
                            <div style="height:10px;background:#f0f0f0;border-radius:6px;width:60%;animation:shimmer 1.2s infinite;"></div>
                        </div>
                        <?php endfor; ?>
                    </div>

                    <!-- Empty State -->
                    <div id="modalEmptyState" style="display:none; text-align:center; padding:48px 0;">
                        <div style="width:64px;height:64px;background:#f3f4f6;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                            <i class="fas fa-clipboard-list" style="font-size:1.5rem;color:#d1d5db;"></i>
                        </div>
                        <p style="color:#6b7280;font-size:0.88rem;margin:0;" id="modalEmptyMsg">Belum ada riwayat perbaikan</p>
                    </div>

                    <!-- Card List -->
                    <div id="modalCardList"></div>

                    <!-- Load More -->
                    <div id="modalLoadMore" style="display:none; text-align:center; padding:8px 0;">
                        <button type="button" onclick="loadMoreItems()"
                                style="padding:7px 20px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;font-size:0.82rem;color:#374151;cursor:pointer;">
                            <i class="fas fa-chevron-down mr-1"></i> Tampilkan lebih banyak
                        </button>
                    </div>
                </div>

                <div class="modal-footer" style="padding:10px 16px;background:#fff;border-top:1px solid #e5e7eb;">
                    <div id="modalStatsBar" style="flex:1;font-size:0.78rem;color:#6b7280;"></div>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>
    <script>
    // ===== RIWAYAT PERBAIKAN MODAL =====
    const ITEMS_PER_PAGE = 20;
    let _allData = [];
    let _filteredData = [];
    let _shownCount = 0;

    const STATUS_CFG = {
        'Pending':     { color:'#92400e', bg:'#fef3c7', icon:'fa-clock' },
        'Approved':    { color:'#0369a1', bg:'#e0f2fe', icon:'fa-check-circle' },
        'In Progress': { color:'#6d28d9', bg:'#ede9fe', icon:'fa-tools' },
        'Completed':   { color:'#166534', bg:'#dcfce7', icon:'fa-flag-checkered' },
        'Rejected':    { color:'#991b1b', bg:'#fee2e2', icon:'fa-times-circle' },
        'Cancelled':   { color:'#4b5563', bg:'#f3f4f6', icon:'fa-ban' },
    };
    const PRIORITAS_CFG = {
        'Segera': { color:'#991b1b', bg:'#fee2e2' },
        'Normal': { color:'#92400e', bg:'#fef3c7' },
        'Rendah': { color:'#374151', bg:'#f3f4f6' },
    };

    function viewMessDetail(id) { openRiwayatModal(id, 'Mess'); }
    function viewWorkshopDetail(id) { openRiwayatModal(id, 'Workshop'); }

    function openRiwayatModal(id, tipe) {
        // Reset UI
        _allData = []; _filteredData = []; _shownCount = 0;
        document.getElementById('modalSearchInput').value = '';
        document.getElementById('modalFilterStatus').value = '';
        document.getElementById('modalFilterPrioritas').value = '';
        document.getElementById('modalCardList').innerHTML = '';
        document.getElementById('modalSkeleton').style.display = 'block';
        document.getElementById('modalEmptyState').style.display = 'none';
        document.getElementById('modalLoadMore').style.display = 'none';
        document.getElementById('modalFilterInfo').textContent = '';
        document.getElementById('modalStatsBar').textContent = '';

        // Header
        const isMess = tipe === 'Mess';
        document.getElementById('modalRiwayatTitle').textContent = 'Riwayat Perbaikan ' + tipe;
        document.getElementById('modalRiwayatSubtitle').textContent = 'Memuat data...';
        document.getElementById('modalRiwayatHeader').style.borderLeft = '4px solid ' + (isMess ? '#3b82f6' : '#f59e0b');

        $('#modalRiwayatPerbaikan').modal('show');

        // Fetch
        const url = '<?= base_url('general-service/repair-request/repair-list/') ?>' 
            + tipe.toLowerCase() + '/' + id;

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(res => {
                document.getElementById('modalSkeleton').style.display = 'none';

                if (!res.success) throw new Error(res.message);

                _allData = res.data || [];
                _filteredData = _allData;

                const nama = res.nama || '';
                document.getElementById('modalRiwayatSubtitle').textContent = nama + ' — ' + _allData.length + ' pengajuan';

                // Stats bar
                const pending   = _allData.filter(d => d.status === 'Pending').length;
                const progress  = _allData.filter(d => d.status === 'In Progress').length;
                const completed = _allData.filter(d => d.status === 'Completed').length;
                document.getElementById('modalStatsBar').innerHTML =
                    `<span style="margin-right:10px;">⏳ Pending: <b>${pending}</b></span>` +
                    `<span style="margin-right:10px;">🔧 Proses: <b>${progress}</b></span>` +
                    `<span>✅ Selesai: <b>${completed}</b></span>`;

                renderModalCards(true);
            })
            .catch(err => {
                document.getElementById('modalSkeleton').style.display = 'none';
                document.getElementById('modalEmptyState').style.display = 'block';
                document.getElementById('modalEmptyMsg').textContent = err.message || 'Gagal memuat data';
            });
    }

    function filterModalList() {
        const search    = document.getElementById('modalSearchInput').value.toLowerCase().trim();
        const status    = document.getElementById('modalFilterStatus').value;
        const prioritas = document.getElementById('modalFilterPrioritas').value;

        _filteredData = _allData.filter(item => {
            const matchSearch = !search ||
                (item.kode_pengajuan||'').toLowerCase().includes(search) ||
                (item.deskripsi_kerusakan||'').toLowerCase().includes(search) ||
                (item.jenis_kerusakan||'').toLowerCase().includes(search);
            const matchStatus    = !status    || item.status    === status;
            const matchPrioritas = !prioritas || item.prioritas === prioritas;
            return matchSearch && matchStatus && matchPrioritas;
        });

        const hasFilter = search || status || prioritas;
        document.getElementById('modalFilterInfo').textContent = hasFilter
            ? `Menampilkan ${_filteredData.length} dari ${_allData.length} pengajuan`
            : '';

        renderModalCards(true);
    }

    function renderModalCards(reset = false) {
        if (reset) { _shownCount = 0; document.getElementById('modalCardList').innerHTML = ''; }

        if (_filteredData.length === 0) {
            document.getElementById('modalEmptyState').style.display = 'block';
            document.getElementById('modalEmptyMsg').textContent = 
                _allData.length > 0 ? 'Tidak ada hasil yang cocok' : 'Belum ada riwayat perbaikan';
            document.getElementById('modalLoadMore').style.display = 'none';
            return;
        }

        document.getElementById('modalEmptyState').style.display = 'none';

        const slice = _filteredData.slice(_shownCount, _shownCount + ITEMS_PER_PAGE);
        const container = document.getElementById('modalCardList');

        slice.forEach(item => {
            const s = STATUS_CFG[item.status] || STATUS_CFG['Pending'];
            const p = PRIORITAS_CFG[item.prioritas] || PRIORITAS_CFG['Normal'];
            const tgl = item.tanggal_pengajuan
                ? new Date(item.tanggal_pengajuan).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'})
                : '-';
            const biaya = item.estimasi_biaya > 0
                ? 'Rp ' + parseInt(item.estimasi_biaya).toLocaleString('id-ID') : null;
            const deskripsi = item.deskripsi_kerusakan
                ? item.deskripsi_kerusakan.substring(0, 90) + (item.deskripsi_kerusakan.length > 90 ? '…' : '')
                : '-';

            const card = document.createElement('div');
            card.style.cssText = 'border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;margin-bottom:8px;background:#fff;transition:box-shadow 0.15s;';
            card.onmouseover = () => card.style.boxShadow = '0 2px 10px rgba(0,0,0,0.07)';
            card.onmouseout  = () => card.style.boxShadow = 'none';

            card.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex:1; padding-right:10px;">
                        <div class="d-flex align-items-center flex-wrap mb-1" style="gap:5px;">
                            <span style="font-size:0.82rem;font-weight:700;color:#1d4ed8;">${item.kode_pengajuan||'-'}</span>
                            <span style="padding:2px 7px;border-radius:9999px;font-size:0.68rem;font-weight:600;background:${s.bg};color:${s.color};">
                                <i class="fas ${s.icon}" style="font-size:0.6rem;"></i> ${item.status}
                            </span>
                            <span style="padding:2px 7px;border-radius:9999px;font-size:0.68rem;font-weight:600;background:${p.bg};color:${p.color};">
                                ${item.prioritas||'-'}
                            </span>
                            ${item.kategori_kerusakan ? `<span style="padding:2px 7px;border-radius:9999px;font-size:0.68rem;background:#f3f4f6;color:#4b5563;">${item.kategori_kerusakan}</span>` : ''}
                        </div>
                        <p style="margin:0;font-size:0.82rem;color:#374151;line-height:1.45;">${deskripsi}</p>
                        ${item.status === 'In Progress' && item.progress_percentage ? `
                        <div class="mt-2" style="max-width:200px;">
                            <div style="height:4px;background:#ede9fe;border-radius:9999px;">
                                <div style="height:4px;background:#7c3aed;border-radius:9999px;width:${item.progress_percentage}%;transition:width 0.3s;"></div>
                            </div>
                            <small style="color:#6d28d9;font-size:0.68rem;">${item.progress_percentage}% selesai</small>
                        </div>` : ''}
                    </div>
                    <div style="text-align:right;white-space:nowrap;">
                        <div style="font-size:0.73rem;color:#9ca3af;">${tgl}</div>
                        ${biaya ? `<div style="font-size:0.75rem;font-weight:600;color:#059669;margin-top:2px;">${biaya}</div>` : ''}
                        <!-- <button onclick="goToDetail(${item.id})"
                                style="margin-top:6px;padding:3px 10px;border-radius:6px;border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;font-size:0.72rem;cursor:pointer;">
                            <i class="fas fa-eye" style="font-size:0.65rem;"></i> Detail
                        </button> -->
                    </div>
                </div>
            `;
            container.appendChild(card);
        });

        _shownCount += slice.length;
        document.getElementById('modalLoadMore').style.display = _shownCount < _filteredData.length ? 'block' : 'none';
    }

    function loadMoreItems() { renderModalCards(false); }

    function goToDetail(id) {
        $('#modalRiwayatPerbaikan').modal('hide');
        setTimeout(() => {
            window.location.href = '<?= base_url('general-service/repair-request/detail/') ?>' + id;
        }, 300);
    }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const btnMess = document.getElementById('btnAddMess');
            const btnWorkshop = document.getElementById('btnAddWorkshop');

            if (btnMess) {
                btnMess.addEventListener('click', function () {
                    window.location.href = '<?= base_url('general-service/mess') ?>';
                });
            }

            if (btnWorkshop) {
                btnWorkshop.addEventListener('click', function () {
                    window.location.href = '<?= base_url('general-service/workshop') ?>';
                });
            }

        });
    </script>

    <script>
    $(document).ready(function() {
        // 1. Initialize Tooltips & Popovers
        $('[data-toggle="tooltip"]').tooltip();
        $('[data-toggle="popover"]').popover({
            html: true,
            trigger: 'hover'
        });

        // 2. Handle Tab Switching from URL Parameter
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        
        if (activeTab === 'workshop') {
            // Hapus kelas active dari Mess
            $('#mess-tab').removeClass('active');
            $('#mess').removeClass('show active');
            
            // Tambah kelas active ke Workshop
            $('#workshop-tab').addClass('active');
            $('#workshop').addClass('show active');
        }

        // 3. Update URL when clicking tabs (Bootstrap 4 Event)
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            const tabId = $(e.target).attr('href').replace('#', '');
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        });

        // 4. Auto hide alert
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });

    function deleteMessData(id, nama) {
        if(confirm(`Hapus data mess atas nama ${nama}?`)) {
            fetch('<?= base_url('general-service/mess/delete') ?>/' + id, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Data berhasil dihapus');
                    location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            });
        }
    }

    function deleteWorkshopData(id, nama) {
        if(confirm(`Hapus data workshop atas nama ${nama}?`)) {
            fetch('<?= base_url('general-service/workshop/delete') ?>/' + id, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Data berhasil dihapus');
                    location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            });
        }
    }

    // Export Functions
    function exportMessToExcel() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '<?= base_url('general-service/mess/export-excel') ?>?' + params.toString();
    }

    function exportMessToPDF() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '<?= base_url('general-service/mess/export-pdf') ?>?' + params.toString();
    }

    function exportWorkshopToExcel() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '<?= base_url('general-service/workshop/export-excel') ?>?' + params.toString();
    }

    function exportWorkshopToPDF() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '<?= base_url('general-service/workshop/export-pdf') ?>?' + params.toString();
    }
    </script>

    <style>
    .opacity-50 { opacity: 0.5; }
    .table-hover tbody tr:hover { background-color: #f8f9fa; }
    .badge-sm { font-size: 0.75em; padding: 0.25em 0.5em; }
    .btn-xs { padding: 0.15rem 0.4rem; font-size: 0.75rem; }
    .bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .bg-gradient-info { background: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%); }
    .bg-gradient-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    </style>
    <script>
    (function(){
    // util
    function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
    function qsa(sel, ctx){ return Array.from((ctx||document).querySelectorAll(sel)); }

    // Try to use jQuery/Bootstrap if present
    var hasJQ = (typeof window.jQuery !== 'undefined');
    var hasBootstrapTab = hasJQ && (typeof jQuery.fn !== 'undefined') && (typeof jQuery.fn.tab !== 'undefined');

    // Fallback tab manager (vanilla)
    function vanillaTabsInit() {
        var tabLinks = qsa('a[data-toggle="tab"]');
        var tabPanes = qsa('.tab-pane');

        if (!tabLinks.length || !tabPanes.length) return;

        function activateTabById(id, pushState=true){
        // links
        tabLinks.forEach(function(a){
            var target = a.getAttribute('href') || '';
            if (target.replace('#','') === id) {
            a.classList.add('active');
            a.setAttribute('aria-selected', 'true');
            } else {
            a.classList.remove('active');
            a.setAttribute('aria-selected', 'false');
            }
        });
        // panes
        tabPanes.forEach(function(p){
            if (p.id === id) {
            p.classList.add('show','active');
            p.style.display = '';
            } else {
            p.classList.remove('show','active');
            // hide non-active panes
            p.style.display = 'none';
            }
        });
        if (pushState) {
            try {
            var url = new URL(window.location);
            url.searchParams.set('tab', id);
            window.history.replaceState({}, '', url);
            } catch(e){}
        }
        }

        // Click handlers
        tabLinks.forEach(function(a){
        a.addEventListener('click', function(ev){
            ev.preventDefault();
            var target = (a.getAttribute('href') || '').replace('#','');
            if (!target) return;
            activateTabById(target, true);
        });
        });

        // initial: read URL param ?tab=...
        var params = new URLSearchParams(window.location.search);
        var active = params.get('tab');
        if (!active) {
        // if some link already has .active, use it; else use first tab pane id
        var activeLink = tabLinks.find(l => l.classList.contains('active'));
        if (activeLink) active = (activeLink.getAttribute('href')||'').replace('#','');
        else active = tabPanes[0] && tabPanes[0].id;
        }
        if (active) activateTabById(active, false);
    }

    // Very small popover fallback: show content in a tooltip-like box on click (for elements data-toggle="popover")
    function vanillaPopoversInit(){
        var popovers = qsa('[data-toggle="popover"]');
        if (!popovers.length) return;

        popovers.forEach(function(el){
        // if bootstrap exists, let it handle popover (we will not interfere)
        if (hasJQ && typeof jQuery.fn.popover === 'function') return;

        // ensure there is a data-content or title
        var content = el.getAttribute('data-content') || el.getAttribute('title') || '';
        // create a small popover element on click
        var popup;
        function show(){
            hide();
            if (!content) return;
            popup = document.createElement('div');
            popup.className = 'vanilla-popover';
            popup.innerHTML = content;
            document.body.appendChild(popup);
            var r = el.getBoundingClientRect();
            popup.style.position = 'absolute';
            popup.style.left = (window.scrollX + r.left) + 'px';
            popup.style.top = (window.scrollY + r.bottom + 6) + 'px';
            popup.style.zIndex = 2000;
            popup.style.background = '#fff';
            popup.style.border = '1px solid rgba(0,0,0,0.12)';
            popup.style.padding = '6px 8px';
            popup.style.borderRadius = '4px';
            popup.style.boxShadow = '0 2px 6px rgba(0,0,0,0.08)';
        }
        function hide(){
            if (popup && popup.parentNode) popup.parentNode.removeChild(popup);
            popup = null;
        }
        el.addEventListener('click', function(e){
            e.stopPropagation();
            if (popup) hide(); else show();
        });
        // hide on outside click
        document.addEventListener('click', function(){ hide(); });
        });
    }

    // Tooltip fallback: use native title attribute when bootstrap tooltip missing
    function ensureTooltips(){
        // If bootstrap tooltip exists, init it
        if (hasJQ && typeof jQuery.fn.tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
        return;
        }
        // Otherwise, nothing to do: browser will use title attr as native tooltip
        // But ensure elements with data-toggle="tooltip" still have title attribute set (if they used title in markup, it's fine)
    }

    // Try to use bootstrap/jQuery if available; if not, run vanilla fallback
    function init() {
        // If jQuery+bootstrap present, prefer them
        if (hasJQ) {
        try {
            // If bootstrap's tab plugin exists, show target tab from URL
            if (hasBootstrapTab) {
            var urlParams = new URLSearchParams(window.location.search);
            var activeTab = urlParams.get('tab');
            if (activeTab === 'workshop') {
                // trigger bootstrap tab show using jQuery
                $('#workshop-tab').tab('show');
            } else {
                $('#mess-tab').tab('show');
            }
            // init bootstrap tooltips/popovers safely
            $('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle="popover"]').popover({ html: true, trigger: 'hover' });
            // attach history update if not attached
            $('a[data-toggle="tab"]').off('shown.bs.tab.myfix').on('shown.bs.tab.myfix', function (e) {
                var tabId = $(e.target).attr('href').replace('#','');
                var url = new URL(window.location);
                url.searchParams.set('tab', tabId);
                window.history.replaceState({}, '', url);
            });
            return; // done
            }
        } catch(err){
            // fallthrough to vanilla
            console.warn('Bootstrap tab init failed, falling back to vanilla: ', err);
        }
        }

        // vanilla fallback
        vanillaTabsInit();
        vanillaPopoversInit();
        ensureTooltips();
    }

    // Small delay to ensure DOM ready (page likely already loaded since this file is at bottom)
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(init, 10);
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }

    })();
    </script>

    <style>
    /* tiny styles for vanilla popover */
    .vanilla-popover { max-width: 280px; word-wrap: break-word; font-size: 0.9rem; }
    </style>

    <?= $this->endSection() ?>