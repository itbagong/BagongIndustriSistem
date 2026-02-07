<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

        <!-- Content -->
        <main class="content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span class="icon">👥</span>
                    <h1>Manajemen Karyawan</h1>
                </div>
                <div class="header-actions">
                    <button class="btn btn-warning" onclick="openImportModal()">
                        <span class="btn-icon">📤</span>
                        Import Excel
                    </button>
                    <button class="btn btn-success" onclick="exportData()">
                        <span class="btn-icon">📥</span>
                        Export Excel
                    </button>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <span class="btn-icon">➕</span>
                        Tambah Karyawan
                    </button>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="stats-summary">
                <div class="stat-box">
                    <span class="icon">👨‍💼</span>
                    <div class="content">
                        <h4>Total Karyawan</h4>
                        <div class="value" id="totalEmployees">127</div>
                    </div>
                </div>
                <div class="stat-box">
                    <span class="icon">✅</span>
                    <div class="content">
                        <h4>Karyawan Aktif</h4>
                        <div class="value" id="activeEmployees">118</div>
                    </div>
                </div>
                <div class="stat-box">
                    <span class="icon">⏸️</span>
                    <div class="content">
                        <h4>Non-Aktif</h4>
                        <div class="value" id="inactiveEmployees">9</div>
                    </div>
                </div>
                <div class="stat-box">
                    <span class="icon">🆕</span>
                    <div class="content">
                        <h4>Bulan Ini</h4>
                        <div class="value" id="newEmployees">5</div>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Bar -->
            <div class="search-filter-bar">
                <div class="form-group">
                    <label>🔍 Cari Karyawan</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Cari NIK, Nama, Position...">
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <select class="form-control" id="departmentFilter">
                        <option value="">Semua Department</option>
                        <option value="Production">Production</option>
                        <option value="HR">HR</option>
                        <option value="Finance">Finance</option>
                        <option value="IT">IT</option>
                        <option value="Warehouse">Warehouse</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Employment Status</label>
                    <select class="form-control" id="employmentFilter">
                        <option value="">Semua Status</option>
                        <option value="Permanent">Permanent</option>
                        <option value="Contract">Contract</option>
                        <option value="Probation">Probation</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Employee Status</label>
                    <select class="form-control" id="statusFilter">
                        <option value="">Semua</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Resigned">Resigned</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button class="btn btn-info" onclick="applyFilters()">
                        <span class="btn-icon">🔍</span>
                        Filter
                    </button>
                </div>
            </div>

            <!-- Employee Table -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Daftar Karyawan</h3>
                    <div class="table-info">
                        Menampilkan <strong id="showingCount">0</strong> dari <strong id="totalCount">0</strong> data
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="employee-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Gender</th>
                                <th>Department</th>
                                <th>Division</th>
                                <th>Job Position</th>
                                <th>Golongan</th>
                                <th>Employment Status</th>
                                <th>Employee Status</th>
                                <th>Masa Kerja</th>
                                <th>Site Name</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="employeeTableBody">
                            <!-- Data will be inserted here by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination" id="pagination">
                    <!-- Pagination buttons will be inserted here -->
                </div>
            </div>
        </main>


       
    </div>

    <!-- Import Excel Modal -->
    <div class="modal" id="importModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📤 Import Data Karyawan dari Excel</h2>
                <button class="modal-close" onclick="closeModal('importModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="import-info">
                    <h4>📋 Petunjuk Import:</h4>
                    <ul>
                        <li>File harus berformat <strong>.xlsx</strong></li>
                        <li>Sheet harus bernama <strong>"Employees"</strong></li>
                        <li>Pastikan kolom sesuai dengan template yang disediakan</li>
                        <li>Kolom wajib: NIK, Nama, Gender, Department</li>
                    </ul>
                    <a href="<?= base_url('assets/templates/template_employee_import.xlsx') ?>" class="btn btn-info btn-sm" download>
                        <span class="btn-icon">📥</span>
                        Download Template Excel
                    </a>
                </div>

                <form id="importForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Pilih File Excel <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <input type="file" 
                                   class="form-control" 
                                   id="excelFile" 
                                   name="file" 
                                   accept=".xlsx,.xls" 
                                   onchange="handleFileSelect(this)"
                                   required>
                            <div class="file-info" id="fileInfo" style="display:none; margin-top: 10px;">
                                <span class="file-name"></span>
                                <span class="file-size"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="overwriteData" name="overwrite" value="1">
                            <span>Hapus semua data lama sebelum import (Truncate)</span>
                        </label>
                        <small class="text-warning">⚠️ Hati-hati! Opsi ini akan menghapus SEMUA data karyawan yang ada</small>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="verboseLog" name="verbose" value="1">
                            <span>Tampilkan log detail (verbose mode)</span>
                        </label>
                    </div>

                    <!-- Progress Bar -->
                    <div id="importProgress" style="display:none;">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill" style="width: 0%"></div>
                        </div>
                        <div class="progress-text" id="progressText">Memproses...</div>
                    </div>

                    <!-- Import Result -->
                    <div id="importResult" style="display:none;">
                        <!-- Result will be shown here -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('importModal')">Batal</button>
                <button class="btn btn-primary" onclick="startImport()" id="btnImport">
                    <span class="btn-icon">📤</span>
                    Mulai Import
                </button>
            </div>
        </div>
    </div>

    <!-- Add/Edit Employee Modal -->
    <div class="modal" id="employeeModal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 id="modalTitle">Tambah Karyawan Baru</h2>
                <button class="modal-close" onclick="closeModal('employeeModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="employeeForm">
                    <input type="hidden" id="employeeId" name="id">
                    
                    <h3 class="form-section-title">📋 Informasi Dasar</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>NIK <span class="required">*</span></label>
                            <input type="text" class="form-control" name="nik" id="nik" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap <span class="required">*</span></label>
                            <input type="text" class="form-control" name="nama" id="nama" required>
                        </div>
                        <div class="form-group">
                            <label>Gender <span class="required">*</span></label>
                            <select class="form-control" name="gender" id="gender" required>
                                <option value="">Pilih Gender</option>
                                <option value="Male">Laki-laki</option>
                                <option value="Female">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <h3 class="form-section-title">🏢 Informasi Pekerjaan</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Department <span class="required">*</span></label>
                            <select class="form-control" name="department" id="department" required>
                                <option value="">Pilih Department</option>
                                <option value="Production">Production</option>
                                <option value="HR">HR</option>
                                <option value="Finance">Finance</option>
                                <option value="IT">IT</option>
                                <option value="Warehouse">Warehouse</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Division <span class="required">*</span></label>
                            <input type="text" class="form-control" name="division" id="division" required>
                        </div>
                        <div class="form-group">
                            <label>Job Position <span class="required">*</span></label>
                            <input type="text" class="form-control" name="job_position" id="job_position" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>User Level</label>
                            <select class="form-control" name="user_level" id="user_level">
                                <option value="">Pilih Level</option>
                                <option value="Staff">Staff</option>
                                <option value="Supervisor">Supervisor</option>
                                <option value="Manager">Manager</option>
                                <option value="Director">Director</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Golongan</label>
                            <input type="text" class="form-control" name="golongan" id="golongan">
                        </div>
                        <div class="form-group">
                            <label>Site Name</label>
                            <input type="text" class="form-control" name="site_name" id="site_name">
                        </div>
                    </div>

                    <h3 class="form-section-title">📅 Status & Tanggal</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Employment Status <span class="required">*</span></label>
                            <select class="form-control" name="employment_status" id="employment_status" required>
                                <option value="">Pilih Status</option>
                                <option value="Permanent">Permanent</option>
                                <option value="Contract">Contract</option>
                                <option value="Probation">Probation</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Employee Status <span class="required">*</span></label>
                            <select class="form-control" name="employee_status" id="employee_status" required>
                                <option value="">Pilih Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Resigned">Resigned</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tanggal PKWT</label>
                            <input type="date" class="form-control" name="tanggal_pkwt" id="tanggal_pkwt">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Masa Kerja</label>
                            <input type="text" class="form-control" name="masa_kerja" id="masa_kerja" placeholder="contoh: 2 Tahun 3 Bulan">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Resign/PHK</label>
                            <input type="date" class="form-control" name="tanggal_resign" id="tanggal_resign">
                        </div>
                        <div class="form-group">
                            <label>Place of Hire</label>
                            <input type="text" class="form-control" name="place_of_hire" id="place_of_hire">
                        </div>
                    </div>

                    <h3 class="form-section-title">👤 Informasi Pribadi</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>National ID (KTP)</label>
                            <input type="text" class="form-control" name="national_id" id="national_id" maxlength="16">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" class="form-control" name="phone_number" id="phone_number">
                        </div>
                        <div class="form-group">
                            <label>Religion</label>
                            <select class="form-control" name="religion" id="religion">
                                <option value="">Pilih Agama</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Place of Birth</label>
                            <input type="text" class="form-control" name="place_of_birth" id="place_of_birth">
                        </div>
                        <div class="form-group">
                            <label>Birth Date</label>
                            <input type="date" class="form-control" name="birth_date" id="birth_date">
                        </div>
                        <div class="form-group">
                            <label>Age</label>
                            <input type="number" class="form-control" name="age" id="age" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Last Education</label>
                            <select class="form-control" name="last_education" id="last_education">
                                <option value="">Pilih Pendidikan</option>
                                <option value="SD">SD</option>
                                <option value="SMP">SMP</option>
                                <option value="SMA/SMK">SMA/SMK</option>
                                <option value="D3">D3</option>
                                <option value="S1">S1</option>
                                <option value="S2">S2</option>
                                <option value="S3">S3</option>
                            </select>
                        </div>
                        <div class="form-group full">
                            <label>Address</label>
                            <textarea class="form-control" name="address" id="address" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('employeeModal')">Batal</button>
                <button class="btn btn-primary" onclick="saveEmployee()">
                    <span class="btn-icon">💾</span>
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- View Detail Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Detail Karyawan</h2>
                <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div id="employeeDetail">
                    <!-- Detail will be inserted here -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('viewModal')">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h2>⚠️ Konfirmasi Hapus</h2>
                <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data karyawan ini?</p>
                <p><strong id="deleteEmployeeName"></strong></p>
                <p class="text-warning">Data yang dihapus tidak dapat dikembalikan!</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('deleteModal')">Batal</button>
                <button class="btn btn-danger" onclick="confirmDelete()">
                    <span class="btn-icon">🗑️</span>
                    Hapus
                </button>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/employee.js?v=2') ?>"></script>
    <script>
    // Import Modal Functions
    function openImportModal() {
        document.getElementById('importModal').classList.add('show');
        resetImportForm();
    }

    function resetImportForm() {
        document.getElementById('importForm').reset();
        document.getElementById('fileInfo').style.display = 'none';
        document.getElementById('importProgress').style.display = 'none';
        document.getElementById('importResult').style.display = 'none';
        document.getElementById('btnImport').disabled = false;
    }

    function handleFileSelect(input) {
        const fileInfo = document.getElementById('fileInfo');
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const fileSize = (file.size / 1024).toFixed(2); // KB
            
            fileInfo.querySelector('.file-name').textContent = `📄 ${file.name}`;
            fileInfo.querySelector('.file-size').textContent = `(${fileSize} KB)`;
            fileInfo.style.display = 'block';
        } else {
            fileInfo.style.display = 'none';
        }
    }

    async function startImport() {
        const fileInput = document.getElementById('excelFile');
        const file = fileInput.files[0];

        if (!file) {
            alert('Pilih file Excel terlebih dahulu!');
            return;
        }

        if (!file.name.match(/\.(xlsx|xls)$/)) {
            alert('File harus berformat .xlsx atau .xls');
            return;
        }

        const overwrite = document.getElementById('overwriteData').checked ? '1' : '0';
        const verbose = document.getElementById('verboseLog').checked ? '1' : '0';

        const formData = new FormData();
        formData.append('file', file);

        // Show progress
        document.getElementById('importProgress').style.display = 'block';
        document.getElementById('importResult').style.display = 'none';
        document.getElementById('btnImport').disabled = true;

        updateProgress(10, 'Mengunggah file...');

        try {
            const response = await fetch(`<?= base_url('api/import-employee') ?>?overwrite=${overwrite}&verbose=${verbose}`, {
                method: 'POST',
                body: formData
            });

            updateProgress(90, 'Memproses data...');

            const result = await response.json();

            updateProgress(100, 'Selesai!');

            setTimeout(() => {
                showImportResult(result);
            }, 500);

        } catch (error) {
            console.error('Import error:', error);
            showImportError(error.message);
        }
    }

    function updateProgress(percent, text) {
        document.getElementById('progressFill').style.width = percent + '%';
        document.getElementById('progressText').textContent = text + ' (' + percent + '%)';
    }

    function showImportResult(result) {
        document.getElementById('importProgress').style.display = 'none';
        const resultDiv = document.getElementById('importResult');
        
        if (result.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <h4>✅ Import Berhasil!</h4>
                    <div class="import-stats">
                        <div class="stat-item">
                            <span class="label">Data Baru:</span>
                            <span class="value">${result.data.inserted}</span>
                        </div>
                        <div class="stat-item">
                            <span class="label">Data Diupdate:</span>
                            <span class="value">${result.data.updated}</span>
                        </div>
                        <div class="stat-item">
                            <span class="label">Data Dilewati:</span>
                            <span class="value">${result.data.skipped}</span>
                        </div>
                    </div>
                    <button class="btn btn-success btn-sm" onclick="closeModal('importModal'); loadEmployees();">
                        Tutup & Refresh Data
                    </button>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h4>❌ Import Gagal</h4>
                    <p>${result.message || 'Terjadi kesalahan saat import'}</p>
                </div>
            `;
        }
        
        resultDiv.style.display = 'block';
        document.getElementById('btnImport').disabled = false;
    }

    function showImportError(message) {
        document.getElementById('importProgress').style.display = 'none';
        const resultDiv = document.getElementById('importResult');
        
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <h4>❌ Error</h4>
                <p>${message}</p>
            </div>
        `;
        
        resultDiv.style.display = 'block';
        document.getElementById('btnImport').disabled = false;
    }
    </script>

    <style>
    /* Import Modal Styles */
    .import-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .import-info h4 {
        margin-top: 0;
        color: #333;
    }

    .import-info ul {
        margin: 10px 0;
        padding-left: 20px;
    }

    .import-info li {
        margin: 5px 0;
        color: #666;
    }

    .file-upload-wrapper {
        margin-top: 5px;
    }

    .file-info {
        background: #e8f5e9;
        padding: 10px;
        border-radius: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .file-name {
        font-weight: 500;
        color: #2e7d32;
    }

    .file-size {
        color: #666;
        font-size: 0.9em;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .progress-bar {
        width: 100%;
        height: 30px;
        background: #e0e0e0;
        border-radius: 15px;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #4CAF50, #66BB6A);
        transition: width 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 500;
    }

    .progress-text {
        text-align: center;
        color: #666;
        font-size: 0.9em;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-top: 15px;
    }

    .alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    .alert-danger {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .alert h4 {
        margin-top: 0;
        margin-bottom: 10px;
    }

    .import-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin: 15px 0;
    }

    .stat-item {
        background: white;
        padding: 10px;
        border-radius: 5px;
        text-align: center;
    }

    .stat-item .label {
        display: block;
        font-size: 0.85em;
        color: #666;
        margin-bottom: 5px;
    }

    .stat-item .value {
        display: block;
        font-size: 1.5em;
        font-weight: bold;
        color: #2e7d32;
    }

    .text-warning {
        color: #856404;
        font-size: 0.9em;
        margin-top: 5px;
        display: block;
    }
    </style>

 <?= $this->endSection() ?>