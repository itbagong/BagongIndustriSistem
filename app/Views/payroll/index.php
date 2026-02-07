<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <!-- TAILWIND (TETAP BOLEH) -->
    <script src="https://cdn.tailwindcss.com"></script>
<body class="bg-gray-100">
    <div class="p-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Generator Slip Gaji & Email Sender</h1>
        </div>

        <!-- Alert Messages -->
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

        <!-- Upload Section -->
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

        <!-- Data Table Section -->
        <?php if(!empty($karyawan)): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Toolbar -->
            <div class="flex flex-wrap justify-between items-center mb-4 gap-3">
                <h2 class="text-xl font-semibold text-gray-700">
                    <i class="fas fa-users mr-2"></i>2. Data Karyawan (<?= count($karyawan) ?> orang)
                </h2>
                <div class="flex gap-2">
                    <!-- Tombol Kirim Biasa (Original) -->
                    <!-- <button onclick="sendAllEmails()" id="btn-sync" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-envelope"></i>
                        Kirim Semua Email
                    </button> -->
                    <button id="btn-delete-selected" class="flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" disabled>
                        <i class="fas fa-trash"></i>
                        Hapus Terpilih
                    </button>
                    <!-- Tombol Background (NEW) -->
                    <button onclick="sendAllEmailsBackground()" id="btn-async" class="flex items-center gap-2 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                        <i class="fas fa-paper-plane"></i>
                        Kirim Background
                    </button>
                    <!-- Resend Failed -->
                    <button onclick="resendAllFailed()" id="btn-resend-failed" class="flex items-center gap-2 bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700" style="display:none;">
                        <i class="fas fa-redo"></i>
                        Kirim Ulang Gagal
                    </button>
                    <a href="<?= base_url('slip-gaji/export-excel') ?>" class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-file-excel"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <!-- Progress Monitor (NEW) -->
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
                    <div class="text-center p-2 bg-white rounded">
                        <div class="text-2xl font-bold text-gray-700" id="stat-total">0</div>
                        <div class="text-xs text-gray-500">Total</div>
                    </div>
                    <div class="text-center p-2 bg-white rounded">
                        <div class="text-2xl font-bold text-green-600" id="stat-sent">0</div>
                        <div class="text-xs text-gray-500">Terkirim</div>
                    </div>
                    <div class="text-center p-2 bg-white rounded">
                        <div class="text-2xl font-bold text-red-600" id="stat-failed">0</div>
                        <div class="text-xs text-gray-500">Gagal</div>
                    </div>
                    <div class="text-center p-2 bg-white rounded">
                        <div class="text-2xl font-bold text-yellow-600" id="stat-pending">0</div>
                        <div class="text-xs text-gray-500">Menunggu</div>
                    </div>
                    <div class="text-center p-2 bg-white rounded">
                        <div class="text-2xl font-bold text-purple-600" id="stat-processing">0</div>
                        <div class="text-xs text-gray-500">Diproses</div>
                    </div>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                    <div id="progress-bar" class="h-4 bg-gradient-to-r from-green-500 to-green-600 transition-all duration-500" style="width:0%"></div>
                </div>
                <div class="text-xs text-gray-600 mt-2 text-center" id="progress-text">0% selesai</div>
                
                <div class="mt-3 text-sm text-blue-700">
                    <i class="fas fa-info-circle"></i> 
                    <span id="status-message">Worker berjalan di background. Anda bisa logout atau tutup halaman ini.</span>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mb-4 relative">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                <input 
                    type="text" 
                    id="searchInput"
                    placeholder="Cari NIK, nama, jabatan, site..." 
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    onkeyup="searchTable()"
                >
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                    <!-- <input name="nik" value="<?= esc($filters['nik'] ?? '') ?>" placeholder="NIK" class="border px-3 py-2 rounded">
                    <input name="nama" value="<?= esc($filters['nama'] ?? '') ?>" placeholder="Nama" class="border px-3 py-2 rounded"> -->
                    <!-- <select name="jabatan" class="border px-3 py-2 rounded">
                        <option value="">-- Jabatan --</option>
                        <?php foreach ($jabatanList as $j): ?>
                            <option value="<?= esc($j['jabatan']) ?>"
                                <?= ($filters['jabatan'] ?? '') == $j['jabatan'] ? 'selected' : '' ?>>
                                <?= esc($j['jabatan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select> -->

                    <select name="site" class="border px-3 py-2 rounded">
                        <option value="">-- Site --</option>
                        <?php foreach ($siteList as $s): ?>
                            <option value="<?= esc($s['site']) ?>"
                                <?= ($filters['site'] ?? '') == $s['site'] ? 'selected' : '' ?>>
                                <?= esc($s['site']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="bulan" class="border px-3 py-2 rounded">
                        <option value="">-- Bulan --</option>
                        <?php foreach ($bulanList as $b): ?>
                            <option value="<?= esc($b['bulan']) ?>"
                                <?= ($filters['bulan'] ?? '') == $b['bulan'] ? 'selected' : '' ?>>
                                <?= esc($b['bulan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <!-- <select name="status_kirim" class="border px-3 py-2 rounded">
                        <option value="">-- Status Kirim --</option>
                        <?php foreach ($statusList as $s): ?>
                            <option value="<?= esc($s['status_kirim']) ?>"
                                <?= ($filters['status_kirim'] ?? '') == $s['status_kirim'] ? 'selected' : '' ?>>
                                <?= esc($s['status_kirim']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select> -->

                    <select name="status_kirim" class="border px-3 py-2 rounded">
                        <option value="">-- Status --</option>
                        <option value="pending" <?= ($filters['status_kirim'] ?? '')=='pending'?'selected':'' ?>>Pending</option>
                        <option value="sent" <?= ($filters['status_kirim'] ?? '')=='sent'?'selected':'' ?>>Sent</option>
                        <option value="failed" <?= ($filters['status_kirim'] ?? '')=='failed'?'selected':'' ?>>Failed</option>
                    </select>

                    <!-- <input name="gaji_min" value="<?= esc($filters['gaji_min'] ?? '') ?>" placeholder="Gaji ≥" class="border px-3 py-2 rounded">
                    <input name="gaji_max" value="<?= esc($filters['gaji_max'] ?? '') ?>" placeholder="Gaji ≤" class="border px-3 py-2 rounded"> -->

                    <div class="flex gap-2">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
                        <a href="<?= base_url('slip-gaji') ?>" class="bg-gray-200 px-4 py-2 rounded">Reset</a>
                    </div>
                </form>
                <form method="get" class="flex items-center gap-2 mb-3">
                    <!-- pertahankan filter lain -->
                    <?php foreach ($filters as $key => $val): ?>
                        <?php if ($val !== null && $val !== ''): ?>
                            <input type="hidden" name="<?= esc($key) ?>" value="<?= esc($val) ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <label class="text-sm text-gray-600">Tampilkan</label>

                    <select name="perPage"
                            onchange="this.form.submit()"
                            class="border px-2 py-1 rounded text-sm">
                        <option value="10" <?= $perPage==10?'selected':'' ?>>10</option>
                        <option value="50" <?= $perPage==50?'selected':'' ?>>50</option>
                        <option value="100" <?= $perPage==100?'selected':'' ?>>100</option>
                        <option value="100000" <?= $perPage==100000?'selected':'' ?>>All</option>
                    </select>

                    <span class="text-sm text-gray-600">data</span>
                </form>

                <table class="w-full" id="dataTable">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3">
                                <input type="checkbox" id="select-all" title="Pilih semua">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(1)">
                                NIK <i class="fas fa-sort"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(2)">
                                Nama <i class="fas fa-sort"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Site</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gaji Bersih</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $offset = ($pager->getCurrentPage() - 1) * $pager->getPerPage(); ?>
                        <?php foreach($karyawan as $index => $k): ?>
                        <tr class="hover:bg-gray-50" id="row-<?= $k['id'] ?>">
                            <td class="px-4 py-3 text-sm text-gray-900 text-center">
                                <input type="checkbox" class="row-checkbox" value="<?= $k['id'] ?>">
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900"><?= $offset + $index + 1 ?></td>
                            <td class="px-4 py-3 text-sm text-gray-900"><?= $k['nik'] ?></td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900"><?= $k['nama'] ?></td>
                            <td class="px-4 py-3 text-sm text-gray-900"><?= $k['jabatan'] ?></td>
                            <td class="px-4 py-3 text-sm text-gray-900"><?= $k['site'] ?></td>
                            <td class="px-4 py-3 text-sm text-gray-900"><?= $k['bulan'] ?></td>
                            <td class="px-4 py-3 text-sm text-blue-600"><?= $k['email'] ?></td>
                            <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">
                                Rp <?= number_format($k['gaji_bersih'], 0, ',', '.') ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if($k['status_kirim'] == 'sent'): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle"></i> Terkirim
                                    </span>
                                <?php elseif($k['status_kirim'] == 'failed'): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle"></i> Gagal
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2 justify-center">
                                    <a href="<?= base_url('slip-gaji/preview/' . $k['id']) ?>" 
                                       target="_blank"
                                       class="p-2 text-blue-600 hover:bg-blue-50 rounded" 
                                       title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick="openEditModal(<?= $k['id'] ?>)" 
                                            class="p-2 text-indigo-600 hover:bg-indigo-50 rounded" 
                                            title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="<?= base_url('slip-gaji/generate-pdf/' . $k['id']) ?>" 
                                       class="p-2 text-green-600 hover:bg-green-50 rounded" 
                                       title="Download PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button onclick="sendEmail(<?= $k['id'] ?>, '<?= $k['nama'] ?>')" 
                                            class="p-2 text-purple-600 hover:bg-purple-50 rounded" 
                                            title="Kirim Email"
                                            id="btn-email-<?= $k['id'] ?>">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                    <button onclick="deleteData(<?= $k['id'] ?>, '<?= $k['nama'] ?>')" 
                                            class="p-2 text-red-600 hover:bg-red-50 rounded" 
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="mt-4 text-sm text-gray-700">
                Total: <?= count($karyawan) ?> data karyawan
            </div>
            <div class="mt-4">
                <?= $pager->links() ?>
            </div>

            <div class="mt-2 text-sm text-gray-600">
                Menampilkan <?= count($karyawan) ?> dari <?= $pager->getTotal() ?> data
            </div>

        </div>
        <?php else: ?>
        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-semibold mb-2 text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>Cara Penggunaan:
            </h3>
            <ol class="list-decimal list-inside space-y-2 text-sm text-blue-700">
                <li>Upload file Excel/CSV dengan format kolom sesuai template</li>
                <li>Pastikan kolom "Email" terisi untuk setiap karyawan</li>
                <li>Review data yang ter-upload pada tabel</li>
                <li>Preview slip gaji untuk memastikan format benar</li>
                <li>Download PDF atau kirim langsung ke email karyawan</li>
                <li>Kirim email satu per satu atau kirim semua sekaligus</li>
            </ol>
            <div class="mt-4 p-4 bg-white rounded border border-blue-300">
                <p class="text-xs text-gray-600 mb-2"><strong>Format Excel yang Dibutuhkan:</strong></p>
                <p class="text-xs text-gray-500">
                    No | Tanggal Slip | NIK | Nama | Jabatan | Status | Bulan | Site | UMK | Insentif Lain | 
                    Insentif Pulsa | Kompensasi Cuti | Insentif Lembur | Insentif Makan | Uang Tunggu | 
                    Gaji Prorate | Total Pendapatan | BPJS Kes | BPJS TK | Pot. PPh 21 | Lainnya | 
                    Total Pot | Gaji Bersih | <strong class="text-red-600">Email</strong>
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        let pollInterval = null;

        function showFileName(input) {
            const fileName = input.files[0]?.name || 'Belum ada file dipilih';
            document.getElementById('file-name').textContent = fileName;
        }

        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('dataTable');
            const tbody = table.getElementsByTagName('tbody')[0];
            const rows = tbody.getElementsByTagName('tr');

            let visibleCount = 0;

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                // Search di semua cell kecuali checkbox (index 0) dan action buttons (last)
                for (let j = 1; j < cells.length - 1; j++) {
                    const cell = cells[j];
                    const textContent = cell.textContent || cell.innerText;
                    
                    if (textContent.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }

                if (found) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }

            // Optional: Show result count
            console.log(`Found ${visibleCount} matching rows`);
        }

        // ✅ FIX: Sort Table (preserve original data for search)
        let sortDirection = {}; // Track sort direction per column

        function sortTable(columnIndex) {
            const table = document.getElementById('dataTable');
            const tbody = table.getElementsByTagName('tbody')[0];
            const rows = Array.from(tbody.getElementsByTagName('tr'));
            
            // Determine sort direction
            if (!sortDirection[columnIndex]) {
                sortDirection[columnIndex] = 'asc';
            } else {
                sortDirection[columnIndex] = sortDirection[columnIndex] === 'asc' ? 'desc' : 'asc';
            }
            
            const isAscending = sortDirection[columnIndex] === 'asc';

            // Sort rows
            rows.sort((a, b) => {
                const aCell = a.cells[columnIndex];
                const bCell = b.cells[columnIndex];
                
                if (!aCell || !bCell) return 0;
                
                const aText = aCell.textContent.trim();
                const bText = bCell.textContent.trim();
                
                // Try numeric comparison first
                const aNum = parseFloat(aText.replace(/[^\d.-]/g, ''));
                const bNum = parseFloat(bText.replace(/[^\d.-]/g, ''));
                
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return isAscending ? aNum - bNum : bNum - aNum;
                }
                
                // Fallback to string comparison
                return isAscending 
                    ? aText.localeCompare(bText, 'id-ID') 
                    : bText.localeCompare(aText, 'id-ID');
            });

            // Update sort icons
            const headers = table.getElementsByTagName('th');
            for (let i = 0; i < headers.length; i++) {
                const icon = headers[i].querySelector('.fa-sort, .fa-sort-up, .fa-sort-down');
                if (icon) {
                    icon.className = 'fas fa-sort';
                }
            }
            
            const currentIcon = headers[columnIndex].querySelector('i');
            if (currentIcon) {
                currentIcon.className = isAscending ? 'fas fa-sort-up' : 'fas fa-sort-down';
            }

            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
            
            // ✅ Re-apply search filter after sorting
            const searchInput = document.getElementById('searchInput');
            if (searchInput && searchInput.value) {
                searchTable();
            }
        }


        function sendEmail(id, nama) {
            if (!confirm(`Kirim slip gaji ke ${nama}?`)) return;

            const btn = document.getElementById(`btn-email-${id}`);
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            fetch(`<?= base_url('slip-gaji/send-email/') ?>${id}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                    btn.disabled = false;
                }
            })
            .catch(error => {
                alert('Error: ' + error);
                btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                btn.disabled = false;
            });
        }

        // ========================================
        // ORIGINAL: Kirim Semua Email (Synchronous)
        // ========================================
        function showOverlay() {
            const overlay = document.createElement('div');
            overlay.id = 'email-overlay';
            overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            overlay.innerHTML = `
                <div class="bg-white rounded-lg p-8 text-center min-w-[300px]">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                    <p class="text-lg font-semibold">Mengirim email...</p>
                    <p class="text-sm text-gray-600">Mohon tunggu, jangan tutup halaman ini</p>
                </div>
            `;
            document.body.appendChild(overlay);
        }

        function hideOverlay() {
            const ov = document.getElementById('email-overlay');
            if (ov) ov.remove();
        }

        function sendAllEmails() {
            if (!confirm('Kirim slip gaji ke semua karyawan? Proses ini mungkin memakan waktu beberapa menit dan Anda harus tetap di halaman ini.')) return;

            showOverlay();

            fetch('<?= base_url('slip-gaji/send-all-emails') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new URLSearchParams({
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                })
            })
            .then(async res => {
                hideOverlay();

                const raw = await res.text();

                let data;
                try {
                    data = JSON.parse(raw);
                } catch (_) {
                    data = null;
                }

                if (!res.ok) {
                    alert(
                        (data && data.message)
                        || raw
                        || 'Terjadi kesalahan server'
                    );
                    return;
                }

                alert(
                    (data && data.message)
                    || 'Proses pengiriman selesai'
                );

                location.reload();
            })
            .catch(err => {
                hideOverlay();
                alert('Error: ' + err);
            });
        }

        // ========================================
        // NEW: Kirim Background (Asynchronous)
        // ========================================
        async function sendAllEmailsBackground() {
            if (!confirm('Kirim semua email di background?\n\nProses akan berjalan otomatis di server dan Anda bisa meninggalkan halaman ini atau bahkan logout.')) return;

            const btn = document.getElementById('btn-async');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            try {
                const res = await fetch('<?= base_url('slip-gaji/enqueue-all') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: '<?= csrf_token() ?>=' + encodeURIComponent('<?= csrf_hash() ?>')
                });
                
                const json = await res.json();
                
                if (json.success) {
                    // Show progress monitor
                    document.getElementById('progressMonitor').style.display = 'block';
                    
                    // Start polling
                    startMonitoring();
                    
                    // Reset button
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Background';
                    
                    alert('✓ Email telah ditambahkan ke queue!\n\nProses pengiriman berjalan di background. Anda bisa tutup halaman ini.');
                } else {
                    alert('Error: ' + (json.message || 'Unknown error'));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Background';
                }
            } catch (err) {
                alert('Error: ' + err);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Background';
            }
        }

        function startMonitoring() {
            if (pollInterval) clearInterval(pollInterval);
            
            // Poll setiap 3 detik
            pollInterval = setInterval(async () => {
                try {
                    const r = await fetch('<?= base_url('slip-gaji/queue-status') ?>');
                    const data = await r.json();
                    
                    updateProgress(data);
                    
                    // Stop jika selesai
                    if ((data.sent + data.failed) >= data.total && data.total > 0 && data.pending === 0 && data.processing === 0) {
                        clearInterval(pollInterval);
                        document.getElementById('status-message').innerHTML = 
                            `<i class="fas fa-check-circle"></i> <strong>Selesai!</strong> ${data.sent} terkirim, ${data.failed} gagal.`;
                        
                        setTimeout(() => {
                            if (confirm('Proses selesai!\n\n✓ Terkirim: ' + data.sent + '\n✗ Gagal: ' + data.failed + '\n\nReload halaman untuk melihat status terbaru?')) {
                                location.reload();
                            }
                        }, 2000);
                    }
                } catch (e) {
                    console.error('Poll error:', e);
                }
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

        function deleteData(id, nama) {
            if (!confirm(`Hapus data ${nama}?`)) return;

            const btn = document.getElementById(`row-${id}`);
            if (btn) btn.style.opacity = '0.6';

            fetch(`<?= base_url('slip-gaji/delete/') ?>${id}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new URLSearchParams({
                    '_method': 'DELETE',
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                })
            })
            .then(async res => {
                const raw = await res.text();

                let data;
                try {
                    data = JSON.parse(raw);
                } catch (_) {
                    data = null;
                }

                if (!res.ok) {
                    alert((data && data.message) || raw);
                    return;
                }

                alert((data && data.message) || 'Data dihapus');

                const row = document.getElementById(`row-${id}`);
                if (row) row.remove();
            })
            .catch(err => {
                if (btn) btn.style.opacity = '';
                alert('Error: ' + err);
            });
        }
        // Cek status saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', function() {
        checkInitialQueueStatus();
    });

    async function checkInitialQueueStatus() {
        try {
            const r = await fetch('<?= base_url('slip-gaji/queue-status') ?>');
            const data = await r.json();
            
            // Jika masih ada yang pending atau processing, tampilkan monitor lagi
            if (data.pending > 0 || data.processing > 0) {
                document.getElementById('progressMonitor').style.display = 'block';
                updateProgress(data);
                startMonitoring(); // Lanjutkan polling
                
                // Disable tombol agar tidak double click
                const btn = document.getElementById('btn-async');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            }
            syncResendButton(data.failed || 0);
        } catch (e) {
            console.error('Auto check error:', e);
        }
    }
    // ========================================
        // RESEND ALL FAILED
        // ========================================
        async function resendAllFailed() {
            const btn = document.getElementById('btn-resend-failed');

            if (!confirm('Kirim ulang semua email yang gagal?\n\nStatus akan direset ke Pending dan masuk ke queue.')) return;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            try {
                const res = await fetch('<?= base_url('slip-gaji/resend-all-failed') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: '<?= csrf_token() ?>=' + encodeURIComponent('<?= csrf_hash() ?>')
                });

                const json = await res.json();

                if (json.success) {
                    alert('✓ ' + json.message);

                    // Tampilkan progress monitor & mulai polling
                    document.getElementById('progressMonitor').style.display = 'block';
                    startMonitoring();

                    // Sembunyikan button resend (sudah masuk queue)
                    btn.style.display = 'none';
                } else {
                    alert('Error: ' + (json.message || 'Unknown error'));
                }
            } catch (err) {
                alert('Error: ' + err);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-redo"></i> Kirim Ulang Gagal';
            }
        }

        /**
         * Show/hide tombol "Kirim Ulang Gagal" berdasarkan count failed
         * Dipanggil dari updateProgress() setiap polling
         */
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
    </script>

            

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h3 class="text-xl font-semibold">
                    <i class="fas fa-edit mr-2"></i>Edit Data Karyawan
                </h3>
                <button onclick="closeEditModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <!-- Form -->
            <form id="editForm" method="post" class="p-6">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_id" name="id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- NIK (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card text-gray-400"></i> NIK
                        </label>
                        <input type="text" id="edit_nik" readonly
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
                    </div>

                    <!-- Nama -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user text-gray-400"></i> Nama <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit_nama" name="nama" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Jabatan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-briefcase text-gray-400"></i> Jabatan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit_jabatan" name="jabatan" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Site -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt text-gray-400"></i> Site <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit_site" name="site" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Bulan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar text-gray-400"></i> Bulan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit_bulan" name="bulan" required placeholder="Contoh: Januari 2024"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope text-gray-400"></i> Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="edit_email" name="email" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <!-- Gaji Bersih (Read-only, untuk info) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-money-bill-wave text-gray-400"></i> Gaji Bersih
                        </label>
                        <input type="text" id="edit_gaji_bersih" readonly
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
                    </div>
                    <!-- Status Kirim -->
                    <div>
                        <!-- <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-info-circle text-gray-400"></i> Status Kirim
                        </label> -->
                        <!-- <select id="edit_status_kirim" name="status_kirim"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="pending">Pending</option>
                            <option value="sent">Sent</option>
                            <option value="failed">Failed</option>
                        </select> -->
                        <input id="edit_status_kirim" type="hidden" name="status_kirim" value="pending">
                    </div>

                    
                </div>

                <!-- Info Notice -->
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                        <div class="text-sm text-yellow-800">
                            <strong>Perhatian:</strong>
                            <ul class="list-disc list-inside mt-1 space-y-1">
                                <li>Setelah update, status kirim akan direset ke <strong>Pending</strong></li>
                                <li>Email akan ditambahkan kembali ke queue untuk dikirim ulang</li>
                                <li>Data gaji tidak bisa diubah di sini, upload ulang Excel jika perlu mengubah nilai</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-6 flex gap-3 justify-end">
                    <button type="button" onclick="closeEditModal()"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // ========================================
        // MODAL EDIT FUNCTIONS
        // ========================================

        async function openEditModal(id) {
            try {
                // Show loading state
                const modal = document.getElementById('editModal');
                modal.classList.remove('hidden');
                
                // Fetch data
                const response = await fetch(`<?= base_url('slip-gaji/edit/') ?>${id}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Gagal mengambil data');
                }
                
                const data = await response.json();
                
                // Populate form
                document.getElementById('edit_id').value = data.id;
                document.getElementById('edit_nik').value = data.nik || '';
                document.getElementById('edit_nama').value = data.nama || '';
                document.getElementById('edit_jabatan').value = data.jabatan || '';
                document.getElementById('edit_site').value = data.site || '';
                document.getElementById('edit_bulan').value = data.bulan || '';
                document.getElementById('edit_email').value = data.email || '';
                document.getElementById('edit_status_kirim').value = data.status_kirim || 'pending';
                
                // Format gaji bersih untuk display
                const gajiBersih = parseFloat(data.gaji_bersih || 0);
                document.getElementById('edit_gaji_bersih').value = 'Rp ' + gajiBersih.toLocaleString('id-ID');
                
                // Set form action
                document.getElementById('editForm').action = `<?= base_url('slip-gaji/update/') ?>${id}`;
                
            } catch (error) {
                alert('Error: ' + error.message);
                closeEditModal();
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editForm').reset();
        }

        // Handle form submit
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const id = document.getElementById('edit_id').value;
            
            // Disable submit button
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    // Jika bukan redirect (200-299), coba parse error
                    return response.text().then(text => {
                        throw new Error(text || 'Update gagal');
                    });
                }
                // Jika success, redirect akan terjadi otomatis
                // Tapi kita bisa juga reload manual
                alert('Data berhasil diperbarui!');
                window.location.reload();
            })
            .catch(error => {
                alert('Error: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Simpan Perubahan';
            });
        });

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
            }
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = () => Array.from(document.querySelectorAll('.row-checkbox'));
        const btnDelete = document.getElementById('btn-delete-selected');

        function updateBtnState() {
            const anyChecked = checkboxes().some(cb => cb.checked);
            btnDelete.disabled = !anyChecked;
        }

        // select all toggle
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes().forEach(cb => cb.checked = this.checked);
                updateBtnState();
            });
        }

        // row checkbox change -> update selectAll & button
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList && e.target.classList.contains('row-checkbox')) {
                const all = checkboxes();
                const checked = all.filter(cb => cb.checked);
                if (selectAll) selectAll.checked = (checked.length === all.length);
                updateBtnState();
            }
        });

        // klik tombol Hapus Terpilih
        btnDelete.addEventListener('click', async function() {
            const ids = checkboxes().filter(cb => cb.checked).map(cb => cb.value);
            if (ids.length === 0) return;

            if (!confirm(`Hapus ${ids.length} data terpilih? Tindakan ini tidak dapat dibatalkan.`)) return;

            // disable tombol dan beri loading state
            btnDelete.disabled = true;
            const originalHtml = btnDelete.innerHTML;
            btnDelete.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';

            try {
                // kirim form-encoded (CSRF included)
                const data = new URLSearchParams();
                ids.forEach(id => data.append('ids[]', id));
                data.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                const res = await fetch('<?= base_url('slip-gaji/delete-multiple') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: data
                });

                const json = await res.json();

                if (!res.ok || !json.success) {
                    alert('Gagal hapus: ' + (json.message || res.statusText));
                    btnDelete.disabled = false;
                    btnDelete.innerHTML = originalHtml;
                    return;
                }

                // sukses -> hapus baris dari DOM
                json.deleted.forEach(id => {
                    const row = document.getElementById('row-' + id);
                    if (row) row.remove();
                });

                alert(json.message || `${json.count} data berhasil dihapus.`);
                // reset select all and button
                if (selectAll) selectAll.checked = false;
                updateBtnState();

                // Opsional: refresh page untuk sinkronisasi count/pager
                // location.reload();
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan: ' + err);
                btnDelete.disabled = false;
                btnDelete.innerHTML = originalHtml;
            } finally {
                // restore button (if not reloaded)
                btnDelete.innerHTML = originalHtml;
            }
        });
    });
    </script>

</body>
<?= $this->endSection() ?>