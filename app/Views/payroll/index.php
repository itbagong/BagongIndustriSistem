<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator Slip Gaji & Email Sender</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
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
                    <button onclick="sendAllEmails()" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-envelope"></i>
                        Kirim Semua Email
                    </button>
                    <!-- Tombol Enqueue (ganti tombol Kirim Semua) -->
                    <!-- <button id="btn-send-all"
                            onclick="sendAllEmails()"
                            class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-envelope"></i>
                        Enqueue Semua Email
                    </button> -->

                    <!-- Progress UI (kosongkan default) -->
                    <div id="queueProgressWrap" class="mt-4" style="display:none;">
                        <div class="w-full bg-gray-200 rounded h-3 overflow-hidden">
                            <div id="queueProgressBar" class="h-3 rounded" style="width:0%"></div>
                        </div>
                        <div id="queueProgress" class="mt-2 text-sm text-gray-700"></div>
                    </div>
                    <a href="<?= base_url('slip-gaji/export-excel') ?>" class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-file-excel"></i>
                        Export Excel
                    </a>
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
                <table class="w-full" id="dataTable">
                    <thead class="bg-gray-50 border-b">
                        <tr>
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
                        <?php foreach($karyawan as $index => $k): ?>
                        <tr class="hover:bg-gray-50" id="row-<?= $k['id'] ?>">
                            <td class="px-4 py-3 text-sm text-gray-900"><?= $index + 1 ?></td>
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
        // Show filename when selected
        function showFileName(input) {
            const fileName = input.files[0]?.name || 'Belum ada file dipilih';
            document.getElementById('file-name').textContent = fileName;
        }

        // Search function
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('dataTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }

                row.style.display = found ? '' : 'none';
            }
        }

        // Sort table
        function sortTable(columnIndex) {
            const table = document.getElementById('dataTable');
            const rows = Array.from(table.rows).slice(1);
            const isAscending = table.rows[0].cells[columnIndex].classList.toggle('asc');

            rows.sort((a, b) => {
                const aText = a.cells[columnIndex].textContent.trim();
                const bText = b.cells[columnIndex].textContent.trim();
                return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });

            rows.forEach(row => table.tBodies[0].appendChild(row));
        }

        // Send email to single employee
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

        // Send email to all employees
        // Show/hide overlay
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

    // Send email to all employees (POST via fetch)
    function sendAllEmails() {
        if (!confirm('Kirim slip gaji ke semua karyawan? Proses ini mungkin memakan waktu beberapa menit.')) return;

        showOverlay();

        fetch('<?= base_url('slip-gaji/send-all-emails') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                // optional: include json accept
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            })
        })
        .then(async res => {
            hideOverlay();

            const raw = await res.text(); // <-- dibaca sekali

            let data;
            try {
                data = JSON.parse(raw); // jika valid JSON
            } catch (_) {
                data = null; // fallback non-JSON
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

    // Enqueue all emails for background sending
    // async function sendAllEmails() {
    //     if (!confirm('Enqueue semua email untuk pengiriman background?')) return;

    //     const btn = document.getElementById('btn-send-all');
    //     btn.disabled = true;
    //     btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    //     try {
    //         const res = await fetch('<?= base_url('slip-gaji/enqueue-all') ?>', {
    //             method: 'POST',
    //             headers: {
    //                 'X-Requested-With': 'XMLHttpRequest',
    //                 'Content-Type': 'application/x-www-form-urlencoded'
    //             },
    //             body: '<?= csrf_token() ?>=' + encodeURIComponent('<?= csrf_hash() ?>')
    //         });
            
    //         const json = await res.json();
            
    //         if (json.success) {
    //             alert(json.message || 'Queue created');
    //             // Show progress UI
    //             document.getElementById('queueProgressWrap').style.display = 'block';
    //             // Start polling
    //             pollProgress();
    //         } else {
    //             alert('Error: ' + (json.message || 'Unknown error'));
    //             btn.disabled = false;
    //             btn.innerHTML = '<i class="fas fa-envelope"></i> Enqueue Semua Email';
    //         }
    //     } catch (err) {
    //         alert('Error: ' + err);
    //         btn.disabled = false;
    //         btn.innerHTML = '<i class="fas fa-envelope"></i> Enqueue Semua Email';
    //     }
    // }

    // let pollInterval = null;

    // function pollProgress() {
    //     if (pollInterval) clearInterval(pollInterval);
        
    //     pollInterval = setInterval(async () => {
    //         try {
    //             const r = await fetch('<?= base_url('slip-gaji/queue-status') ?>');
    //             const data = await r.json();
                
    //             const total = data.total || 0;
    //             const sent = data.sent || 0;
    //             const failed = data.failed || 0;
    //             const pending = data.pending || 0;
    //             const processing = data.processing || 0;

    //             const percent = total > 0 ? Math.round(((sent + failed) / total) * 100) : 0;
                
    //             // Update progress bar
    //             const progressBar = document.getElementById('queueProgressBar');
    //             const progressText = document.getElementById('queueProgress');
                
    //             if (progressBar) {
    //                 progressBar.style.width = percent + '%';
    //                 progressBar.className = 'h-3 rounded transition-all duration-300 ' + 
    //                     (percent < 50 ? 'bg-blue-500' : percent < 100 ? 'bg-green-500' : 'bg-green-600');
    //             }
                
    //             if (progressText) {
    //                 progressText.innerHTML = `
    //                     <strong>Progress:</strong> ${sent} terkirim, ${failed} gagal, ${pending} menunggu, ${processing} diproses
    //                     <br><small class="text-gray-500">${percent}% selesai dari ${total} total</small>
    //                 `;
    //             }

    //             // Stop polling when complete
    //             if ((sent + failed) >= total && total > 0) {
    //                 clearInterval(pollInterval);
    //                 alert(`Proses selesai!\n✓ Terkirim: ${sent}\n✗ Gagal: ${failed}`);
    //                 location.reload();
    //             }
    //         } catch (e) {
    //             console.error('Poll error:', e);
    //         }
    //     }, 3000); // poll setiap 3 detik
    // }

    // Delete data (POST + _method=DELETE)
    function deleteData(id, nama) {
        if (!confirm(`Hapus data ${nama}?`)) return;

        // optional: bisa tampil loading kecil
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
    </script>
</body>
<?= $this->endSection() ?>