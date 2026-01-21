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
    <script src="<?= base_url('assets/js/employee.js?v=1') ?>"></script>
 <?= $this->endSection() ?>