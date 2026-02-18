<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1; 
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c1c1c1; 
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8; 
        }

        /* PAGINATION STYLE */
        .custom-pager ul {
            display: flex;
            gap: 0.25rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .custom-pager li a, .custom-pager li span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            height: 2rem;
            padding: 0 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid #e5e7eb;
            background-color: #fff;
            color: #374151;
        }
        .custom-pager li a:hover:not(.active) {
            background-color: #f9fafb;
            border-color: #d1d5db;
        }
        .custom-pager li.active a, 
        .custom-pager li.active span,
        .custom-pager li a.active {
            background-color: #3b82f6 !important;
            color: white !important;
            border-color: #3b82f6 !important;
        }
        .custom-pager li.disabled span {
            color: #9ca3af;
            cursor: not-allowed;
            background-color: #f3f4f6;
        }

        /* --- FITUR RESIZE KOLOM (KAYAK EXCEL) --- */
        table {
            table-layout: fixed; /* Penting agar resize mulus */
            width: 100%;
        }
        
        th {
            position: relative; /* Agar resizer menempel di th */
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .resizer {
            position: absolute;
            top: 0;
            right: 0;
            width: 5px;
            height: 100%;
            cursor: col-resize;
            user-select: none;
            touch-action: none;
            z-index: 20;
        }

        /* Garis biru saat dihover atau ditarik */
        .resizer:hover, .resizing {
            background-color: #3b82f6; 
            width: 7px; /* Sedikit melebar saat disentuh */
        }
    </style>

<body class="bg-gray-100">
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Generator Slip Gaji & Email Sender</h1>
        </div>

        <?php if(session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= session()->getFlashdata('success') ?>
        </div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= session()->getFlashdata('error') ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">
                <i class="fas fa-upload mr-2"></i>1. Upload Data Excel/CSV
            </h2>
            <form action="<?= base_url('slip-gaji/upload') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                    <i class="fas fa-file-excel text-6xl text-gray-400 mb-4"></i>
                    <div class="mb-4">
                        <label class="cursor-pointer bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 inline-block">
                            <i class="fas fa-folder-open mr-2"></i>Pilih File Excel/CSV
                            <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" class="hidden" required onchange="showFileName(this)">
                        </label>
                    </div>
                    <p class="text-sm text-gray-500" id="file-name">Belum ada file dipilih</p>
                    <p class="text-xs text-gray-400 mt-2">
                        Format: No, Tanggal Slip, NIK, Nama, Jabatan, Status, Bulan, Site, UMK, Insentif, dll.
                    </p>
                    <button type="submit" class="mt-4 bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-cloud-upload-alt mr-2"></i>Upload Data
                    </button>
                </div>
            </form>
        </div>

        <?php if(!empty($karyawan)): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-wrap justify-between items-center mb-4 gap-3">
                <h2 class="text-xl font-semibold text-gray-700">
                    <i class="fas fa-users mr-2"></i>2. Data Karyawan
                </h2>
                <div class="flex gap-2">
                    <button id="btn-delete-selected" class="flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" disabled>
                        <i class="fas fa-trash"></i> Hapus Terpilih
                    </button>
                    <button onclick="sendAllEmailsBackground()" id="btn-async" class="flex items-center gap-2 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                        <i class="fas fa-paper-plane"></i> Kirim Background
                    </button>
                    <button onclick="resendAllFailed()" id="btn-resend-failed" class="flex items-center gap-2 bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700" style="display:none;">
                        <i class="fas fa-redo"></i> Kirim Ulang Gagal
                    </button>
                    <a href="<?= base_url('slip-gaji/export-excel') ?>" class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                </div>
            </div>

            <div id="progressMonitor" class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200" style="display:none;">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-blue-800">
                        <i class="fas fa-cog fa-spin"></i> Status Pengiriman Email (Background)
                    </h3>
                    <button onclick="stopMonitoring()" class="text-sm text-blue-600 hover:text-blue-800">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                </div>
                
                <div class="grid grid-cols-5 gap-3 mb-3">
                    <div class="text-center p-2 bg-white rounded"><div class="text-2xl font-bold text-gray-700" id="stat-total">0</div><div class="text-xs text-gray-500">Total</div></div>
                    <div class="text-center p-2 bg-white rounded"><div class="text-2xl font-bold text-green-600" id="stat-sent">0</div><div class="text-xs text-gray-500">Terkirim</div></div>
                    <div class="text-center p-2 bg-white rounded"><div class="text-2xl font-bold text-red-600" id="stat-failed">0</div><div class="text-xs text-gray-500">Gagal</div></div>
                    <div class="text-center p-2 bg-white rounded"><div class="text-2xl font-bold text-yellow-600" id="stat-pending">0</div><div class="text-xs text-gray-500">Menunggu</div></div>
                    <div class="text-center p-2 bg-white rounded"><div class="text-2xl font-bold text-purple-600" id="stat-processing">0</div><div class="text-xs text-gray-500">Diproses</div></div>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                    <div id="progress-bar" class="h-4 bg-gradient-to-r from-green-500 to-green-600 transition-all duration-500" style="width:0%"></div>
                </div>
                <div class="text-xs text-gray-600 mt-2 text-center" id="progress-text">0% selesai</div>
                <div class="mt-3 text-sm text-blue-700">
                    <i class="fas fa-info-circle"></i> <span id="status-message">Worker berjalan di background. Anda bisa logout atau tutup halaman ini.</span>
                </div>
            </div>

            <div class="mb-4 relative">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="Cari NIK, nama, jabatan, site..." 
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    onkeyup="searchTable()">
            </div>

            <div class="flex flex-wrap items-center justify-between mb-4 gap-2">
                <form method="get" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <select name="site" class="border px-3 py-2 rounded text-sm bg-white" onchange="this.form.submit()">
                        <option value="">-- Semua Site --</option>
                        <?php foreach ($siteList as $s): ?>
                            <option value="<?= esc($s['site']) ?>" <?= ($filters['site'] ?? '') == $s['site'] ? 'selected' : '' ?>><?= esc($s['site']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="bulan" class="border px-3 py-2 rounded text-sm bg-white" onchange="this.form.submit()">
                        <option value="">-- Semua Bulan --</option>
                        <?php foreach ($bulanList as $b): ?>
                            <option value="<?= esc($b['bulan']) ?>" <?= ($filters['bulan'] ?? '') == $b['bulan'] ? 'selected' : '' ?>><?= esc($b['bulan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status_kirim" class="border px-3 py-2 rounded text-sm bg-white" onchange="this.form.submit()">
                        <option value="">-- Status Kirim --</option>
                        <option value="pending" <?= ($filters['status_kirim'] ?? '')=='pending'?'selected':'' ?>>Pending</option>
                        <option value="sent" <?= ($filters['status_kirim'] ?? '')=='sent'?'selected':'' ?>>Sent</option>
                        <option value="failed" <?= ($filters['status_kirim'] ?? '')=='failed'?'selected':'' ?>>Failed</option>
                    </select>
                    
                    <a href="<?= base_url('slip-gaji') ?>" class="bg-gray-200 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-300">Reset</a>
                </form>

                <form method="get" class="flex items-center gap-2">
                    <?php foreach ($filters as $key => $val): if ($val !== null && $val !== '') echo "<input type='hidden' name='".esc($key)."' value='".esc($val)."'>"; endforeach; ?>
                    <label class="text-sm text-gray-600">Show</label>
                    <select name="perPage" onchange="this.form.submit()" class="border px-2 py-1 rounded text-sm bg-white">
                        <option value="10" <?= $perPage==10?'selected':'' ?>>10</option>
                        <option value="50" <?= $perPage==50?'selected':'' ?>>50</option>
                        <option value="100" <?= $perPage==100?'selected':'' ?>>100</option>
                        <option value="100000" <?= $perPage==100000?'selected':'' ?>>All</option>
                    </select>
                </form>
            </div>

            <div class="bg-white border rounded-lg shadow-sm flex flex-col">
                
                <div class="overflow-x-auto overflow-y-auto max-h-[60vh] custom-scrollbar rounded-t-lg">
                    <table class="w-full min-w-[1200px]" id="dataTable"> <thead class="bg-gray-50 border-b sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 bg-gray-50 border-b w-12 text-center">
                                    <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b w-12">No</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b w-32 cursor-pointer hover:bg-gray-100" onclick="sortTable(2)">
                                    NIK <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b w-48 cursor-pointer hover:bg-gray-100" onclick="sortTable(3)">
                                    Nama <i class="fas fa-sort ml-1"></i>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b w-40">Jabatan</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b w-32">Site</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b w-32">Bulan</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b w-48">Email</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b w-36">Gaji Bersih</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b w-28">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 border-b w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <?php 
                                // Perhitungan Offset Data
                                $currentPage = $pager->getCurrentPage();
                                $perPage = $pager->getPerPage();
                                $total = $pager->getTotal();
                                $offset = ($currentPage - 1) * $perPage;
                                
                                // Hitung range data
                                $startItem = ($total == 0) ? 0 : $offset + 1;
                                $endItem = min($offset + $perPage, $total);
                            ?>
                            <?php foreach($karyawan as $index => $k): ?>
                            <tr class="hover:bg-blue-50 transition duration-150 ease-in-out" id="row-<?= $k['id'] ?>">
                                <td class="px-4 py-3 text-sm text-center">
                                    <input type="checkbox" class="row-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="<?= $k['id'] ?>">
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500"><?= $offset + $index + 1 ?></td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 truncate"><?= $k['nik'] ?></td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-700 truncate"><?= $k['nama'] ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600 truncate"><?= $k['jabatan'] ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <span class="px-2 py-0.5 inline-flex text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                        <?= $k['site'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 truncate"><?= $k['bulan'] ?></td>
                                <td class="px-4 py-3 text-sm text-blue-600 truncate" title="<?= $k['email'] ?>">
                                    <?= $k['email'] ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-mono text-gray-700">
                                    Rp <?= number_format($k['gaji_bersih'], 0, ',', '.') ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if($k['status_kirim'] == 'sent'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                            Terkirim
                                        </span>
                                    <?php elseif($k['status_kirim'] == 'failed'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                            Gagal
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">
                                            Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= base_url('slip-gaji/preview/' . $k['id']) ?>" target="_blank" class="text-gray-500 hover:text-blue-600" title="Preview"><i class="fas fa-eye"></i></a>
                                        <button onclick="openEditModal(<?= $k['id'] ?>)" class="text-gray-500 hover:text-yellow-600" title="Edit"><i class="fas fa-pen"></i></button>
                                        <button onclick="sendEmail(<?= $k['id'] ?>, '<?= $k['nama'] ?>')" class="text-gray-500 hover:text-purple-600" title="Kirim" id="btn-email-<?= $k['id'] ?>"><i class="fas fa-paper-plane"></i></button>
                                        <button onclick="deleteData(<?= $k['id'] ?>, '<?= $k['nama'] ?>')" class="text-gray-500 hover:text-red-600" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t bg-white rounded-b-lg flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-gray-600">
                        Menampilkan <span class="font-semibold text-gray-900"><?= $startItem ?> - <?= $endItem ?></span> dari <span class="font-semibold text-gray-900"><?= $total ?></span> data
                    </div>
                    <div class="custom-pager">
                        <?= $pager->links('default', 'default_full') ?>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-semibold mb-2 text-blue-800"><i class="fas fa-info-circle mr-2"></i>Cara Penggunaan:</h3>
            <ol class="list-decimal list-inside space-y-2 text-sm text-blue-700">
                <li>Upload file Excel/CSV dengan format kolom sesuai template.</li>
                <li>Pastikan kolom "Email" terisi.</li>
            </ol>
        </div>
        <?php endif; ?>
    </div>

    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h3 class="text-xl font-semibold"><i class="fas fa-edit mr-2"></i>Edit Data Karyawan</h3>
                <button onclick="closeEditModal()" class="text-white hover:text-gray-200"><i class="fas fa-times text-2xl"></i></button>
            </div>
            <form id="editForm" method="post" class="p-6">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_id" name="id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">NIK</label><input type="text" id="edit_nik" readonly class="w-full px-3 py-2 border rounded-lg bg-gray-100"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">Nama <span class="text-red-500">*</span></label><input type="text" id="edit_nama" name="nama" required class="w-full px-3 py-2 border rounded-lg"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">Jabatan <span class="text-red-500">*</span></label><input type="text" id="edit_jabatan" name="jabatan" required class="w-full px-3 py-2 border rounded-lg"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">Site <span class="text-red-500">*</span></label><input type="text" id="edit_site" name="site" required class="w-full px-3 py-2 border rounded-lg"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">Bulan <span class="text-red-500">*</span></label><input type="text" id="edit_bulan" name="bulan" required class="w-full px-3 py-2 border rounded-lg"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label><input type="email" id="edit_email" name="email" required class="w-full px-3 py-2 border rounded-lg"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">Gaji Bersih</label><input type="text" id="edit_gaji_bersih" readonly class="w-full px-3 py-2 border rounded-lg bg-gray-100"></div>
                    <input id="edit_status_kirim" type="hidden" name="status_kirim" value="pending">
                </div>
                <div class="mt-6 flex gap-3 justify-end">
                    <button type="button" onclick="closeEditModal()" class="px-6 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showFileName(input) { document.getElementById('file-name').textContent = input.files[0]?.name || 'Belum ada file dipilih'; }

        // --- SORTING ---
        function sortTable(n) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.getElementById("dataTable");
            switching = true;
            dir = "asc";
            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    if (dir == "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) { shouldSwitch = true; break; }
                    } else if (dir == "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) { shouldSwitch = true; break; }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount == 0 && dir == "asc") { dir = "desc"; switching = true; }
                }
            }
        }

        // --- SEARCH ---
        function searchTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("dataTable");
            tr = table.getElementsByTagName("tr");
            for (i = 1; i < tr.length; i++) { 
                var match = false;
                var tds = tr[i].getElementsByTagName("td");
                for (var j = 1; j < tds.length; j++) { 
                    if (tds[j]) {
                        txtValue = tds[j].textContent || tds[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            match = true;
                            break;
                        }
                    }
                }
                tr[i].style.display = match ? "" : "none";
            }
        }

        // --- COLUMN RESIZE LOGIC ---
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('dataTable');
            if(table) enableColumnResizing(table);
            
            // Checkbox logic init
            checkboxInit();
            
            // Initial Status Check
            checkInitialQueueStatus();
        });

        function enableColumnResizing(table) {
            const cols = table.querySelectorAll('th');
            [].forEach.call(cols, function(col) {
                // Add resizer only if not already present
                if(col.querySelector('.resizer')) return;

                const resizer = document.createElement('div');
                resizer.classList.add('resizer');
                resizer.style.height = `${table.offsetHeight}px`;
                col.appendChild(resizer);

                createResizableColumn(col, resizer);
            });
        }

        function createResizableColumn(col, resizer) {
            let x = 0;
            let w = 0;

            const mouseDownHandler = function(e) {
                x = e.clientX;
                const styles = window.getComputedStyle(col);
                w = parseInt(styles.width, 10);

                document.addEventListener('mousemove', mouseMoveHandler);
                document.addEventListener('mouseup', mouseUpHandler);
                resizer.classList.add('resizing');
            };

            const mouseMoveHandler = function(e) {
                const dx = e.clientX - x;
                col.style.width = `${w + dx}px`;
            };

            const mouseUpHandler = function() {
                document.removeEventListener('mousemove', mouseMoveHandler);
                document.removeEventListener('mouseup', mouseUpHandler);
                resizer.classList.remove('resizing');
            };

            resizer.addEventListener('mousedown', mouseDownHandler);
        }

        function checkboxInit() {
            const selectAll = document.getElementById('select-all');
            const btnDelete = document.getElementById('btn-delete-selected');
            
            function updateBtnState() {
                const checkboxes = document.querySelectorAll('.row-checkbox');
                const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                if(btnDelete) btnDelete.disabled = !anyChecked;
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    checkboxes.forEach(cb => cb.checked = this.checked);
                    updateBtnState();
                });
            }

            document.getElementById('dataTable')?.addEventListener('change', function(e) {
                if (e.target.classList.contains('row-checkbox')) {
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    if(selectAll) selectAll.checked = allChecked;
                    updateBtnState();
                }
            });

            btnDelete?.addEventListener('click', async function() {
                const checkboxes = document.querySelectorAll('.row-checkbox:checked');
                const ids = Array.from(checkboxes).map(cb => cb.value);
                if (ids.length === 0) return;
                
                if (!confirm(`Hapus ${ids.length} data terpilih?`)) return;
                
                const originalHtml = btnDelete.innerHTML;
                btnDelete.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btnDelete.disabled = true;

                try {
                    const data = new URLSearchParams();
                    ids.forEach(id => data.append('ids[]', id));
                    data.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                    const res = await fetch('<?= base_url('slip-gaji/delete-multiple') ?>', {
                        method: 'POST',
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        body: data
                    });
                    const json = await res.json();
                    if(json.success) { alert(json.message); location.reload(); } 
                    else { alert('Gagal: ' + json.message); }
                } catch(err) { alert('Error: ' + err); } 
                finally { btnDelete.innerHTML = originalHtml; btnDelete.disabled = false; }
            });
        }

        // --- EMAIL & EDIT LOGIC (Standard) ---
        let pollInterval = null;
        async function checkInitialQueueStatus() {
            try {
                const r = await fetch('<?= base_url('slip-gaji/queue-status') ?>');
                const data = await r.json();
                if (data.pending > 0 || data.processing > 0) {
                    document.getElementById('progressMonitor').style.display = 'block';
                    updateProgress(data);
                    startMonitoring();
                    const btn = document.getElementById('btn-async');
                    if(btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...'; }
                }
                syncResendButton(data.failed || 0);
            } catch (e) { console.error(e); }
        }

        async function sendAllEmailsBackground() {
            if (!confirm('Kirim semua email di background?')) return;
            const btn = document.getElementById('btn-async');
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            try {
                const res = await fetch('<?= base_url('slip-gaji/enqueue-all') ?>', {
                    method: 'POST',
                    headers: {'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded'},
                    body: '<?= csrf_token() ?>=' + encodeURIComponent('<?= csrf_hash() ?>')
                });
                const json = await res.json();
                if (json.success) {
                    document.getElementById('progressMonitor').style.display = 'block';
                    startMonitoring();
                    btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Background';
                    alert('Email dimasukkan ke antrian.');
                } else {
                    alert('Error: ' + json.message);
                    btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Background';
                }
            } catch (err) {
                alert('Error: ' + err);
                btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Background';
            }
        }

        function startMonitoring() {
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(async () => {
                try {
                    const r = await fetch('<?= base_url('slip-gaji/queue-status') ?>');
                    const data = await r.json();
                    updateProgress(data);
                    if ((data.sent + data.failed) >= data.total && data.total > 0 && data.pending === 0 && data.processing === 0) {
                        clearInterval(pollInterval);
                        document.getElementById('status-message').innerHTML = `<i class="fas fa-check-circle"></i> Selesai!`;
                    }
                } catch (e) {}
            }, 3000);
        }

        function updateProgress(data) {
            document.getElementById('stat-total').textContent = data.total || 0;
            document.getElementById('stat-sent').textContent = data.sent || 0;
            document.getElementById('stat-failed').textContent = data.failed || 0;
            document.getElementById('stat-pending').textContent = data.pending || 0;
            document.getElementById('stat-processing').textContent = data.processing || 0;
            const total = data.total || 1;
            const completed = (data.sent || 0) + (data.failed || 0);
            const percent = Math.round((completed / total) * 100);
            document.getElementById('progress-bar').style.width = percent + '%';
            document.getElementById('progress-text').textContent = percent + '% selesai';
            syncResendButton(data.failed || 0);
        }

        function stopMonitoring() {
            if (pollInterval) clearInterval(pollInterval);
            document.getElementById('progressMonitor').style.display = 'none';
        }

        function syncResendButton(failedCount) {
            const btn = document.getElementById('btn-resend-failed');
            if (!btn) return;
            if (failedCount > 0) {
                btn.style.display = 'flex';
                btn.innerHTML = '<i class="fas fa-redo"></i> Kirim Ulang Gagal (' + failedCount + ')';
            } else {
                btn.style.display = 'none';
            }
        }

        async function resendAllFailed() {
            const btn = document.getElementById('btn-resend-failed');
            if (!confirm('Kirim ulang yang gagal?')) return;
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            try {
                const res = await fetch('<?= base_url('slip-gaji/resend-all-failed') ?>', {
                    method: 'POST',
                    headers: {'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded'},
                    body: '<?= csrf_token() ?>=' + encodeURIComponent('<?= csrf_hash() ?>')
                });
                const json = await res.json();
                if(json.success) {
                    document.getElementById('progressMonitor').style.display = 'block';
                    startMonitoring();
                    btn.style.display = 'none';
                } else { alert(json.message); }
            } catch(e) { alert(e); } 
            finally { btn.disabled = false; }
        }

        function sendEmail(id, nama) {
            if (!confirm(`Kirim ke ${nama}?`)) return;
            const btn = document.getElementById(`btn-email-${id}`);
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; btn.disabled = true;
            fetch(`<?= base_url('slip-gaji/send-email/') ?>${id}`, {
                method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}
            }).then(r => r.json()).then(d => {
                if(d.success) { alert(d.message); location.reload(); } else { alert(d.message); btn.innerHTML = '<i class="fas fa-paper-plane"></i>'; btn.disabled = false; }
            }).catch(e => { alert(e); btn.innerHTML = '<i class="fas fa-paper-plane"></i>'; btn.disabled = false; });
        }

        function deleteData(id, nama) {
            if (!confirm(`Hapus ${nama}?`)) return;
            fetch(`<?= base_url('slip-gaji/delete/') ?>${id}`, {
                method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: new URLSearchParams({'_method': 'DELETE', '<?= csrf_token() ?>': '<?= csrf_hash() ?>'})
            }).then(r => r.json()).then(d => {
                if(d.success) { document.getElementById(`row-${id}`).remove(); } else { alert(d.message); }
            }).catch(e => alert(e));
        }

        async function openEditModal(id) {
            document.getElementById('editModal').classList.remove('hidden');
            try {
                const res = await fetch(`<?= base_url('slip-gaji/edit/') ?>${id}`, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
                const data = await res.json();
                document.getElementById('edit_id').value = data.id;
                document.getElementById('edit_nik').value = data.nik;
                document.getElementById('edit_nama').value = data.nama;
                document.getElementById('edit_jabatan').value = data.jabatan;
                document.getElementById('edit_site').value = data.site;
                document.getElementById('edit_bulan').value = data.bulan;
                document.getElementById('edit_email').value = data.email;
                document.getElementById('edit_gaji_bersih').value = 'Rp ' + parseFloat(data.gaji_bersih).toLocaleString('id-ID');
                document.getElementById('editForm').action = `<?= base_url('slip-gaji/update/') ?>${id}`;
            } catch(e) { alert(e); closeEditModal(); }
        }
        function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); }
    </script>
</body>
<?= $this->endSection() ?>