<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <span class="icon">⚙️</span>
                <div>
                    <h1>Setting Menu & Privileges</h1>
                    <p>Kelola hak akses dan privilege pengguna</p>
                </div>
            </div>
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('privileges')">
                    🔐 Manage Privileges
                </button>
                <button class="tab-btn" onclick="switchTab('assignment')">
                    👥 User Assignment
                </button>
            </div>
        </div>

        <!-- Tab 1: Manage Privileges -->
        <div id="privilegesTab" class="tab-content active">
            <div class="privilege-grid">
                <!-- Privilege List -->
                <div class="privilege-list">
                    <h3>
                        Daftar Privileges
                        <button class="btn btn-primary btn-sm" onclick="openAddPrivilegeModal()">
                            ➕ Tambah
                        </button>
                    </h3>
                    <div id="privilegeListContainer">
                        <!-- Will be populated by JS -->
                    </div>
                </div>

                <!-- Privilege Detail -->
                <div class="privilege-detail" id="privilegeDetail">
                    <div class="empty-state">
                        <div class="icon">🔐</div>
                        <h3>Pilih Privilege</h3>
                        <p>Pilih privilege dari daftar untuk melihat detail</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: User Assignment -->
        <div id="assignmentTab" class="tab-content">
            <div class="user-assignment">
                <h3 style="margin-bottom: 20px;">🎯 Assign Privileges to Users</h3>
                
                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-card">
                        <h3 id="totalUsers">0</h3>
                        <p>Total Users</p>
                    </div>
                    <div class="stat-card">
                        <h3 id="totalPrivileges">0</h3>
                        <p>Total Privileges</p>
                    </div>
                    <div class="stat-card">
                        <h3 id="totalAssignments">0</h3>
                        <p>Total Assignments</p>
                    </div>
                </div>

                <!-- Search -->
                <div class="search-box">
                    <input type="text" id="userSearch" placeholder="🔍 Cari user berdasarkan email...">
                    <span class="search-icon">🔍</span>
                </div>

                <!-- User List -->
                <div class="user-grid" id="userGridContainer">
                    <!-- Will be populated by JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Privilege Modal -->
    <div class="modal" id="privilegeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="privilegeModalTitle">Tambah Privilege</h2>
                <button class="modal-close" onclick="closeModal('privilegeModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="privilegeForm">
                    <input type="hidden" id="privilegeId">
                    
                    <div class="form-group">
                        <label>Nama Privilege <span class="required">*</span></label>
                        <input type="text" class="form-control" id="privilegeName" placeholder="e.g., Manage Employees" required>
                    </div>

                    <div class="form-group">
                        <label>Slug <span class="required">*</span></label>
                        <input type="text" class="form-control" id="privilegeSlug" placeholder="e.g., manage-employees" required>
                        <small style="color: #666; font-size: 12px;">Otomatis generate dari nama, atau isi manual</small>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" id="privilegeDescription" placeholder="Deskripsi privilege..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('privilegeModal')">Batal</button>
                <button class="btn btn-primary" onclick="savePrivilege()">💾 Simpan</button>
            </div>
        </div>
    </div>

    <!-- User Privilege Assignment Modal -->
    <div class="modal" id="assignmentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="assignmentModalTitle">Assign Privileges</h2>
                <button class="modal-close" onclick="closeModal('assignmentModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div id="userAssignmentInfo" style="margin-bottom: 20px;"></div>
                <div class="privilege-checkboxes" id="privilegeCheckboxes">
                    <!-- Will be populated by JS -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('assignmentModal')">Batal</button>
                <button class="btn btn-success" onclick="saveUserPrivileges()">✅ Simpan</button>
            </div>
        </div>
    </div>

    <script>
        // Global state
        let privileges = [];
        let users = [];
        let userPrivileges = [];
        let selectedPrivilege = null;
        let selectedUser = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadPrivileges();
            loadUsers();
            loadStats();
            setupEventListeners();
        });

        // Event Listeners
        function setupEventListeners() {
            // Auto-generate slug from name
            document.getElementById('privilegeName').addEventListener('input', (e) => {
                const slug = e.target.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
                document.getElementById('privilegeSlug').value = slug;
            });

            // User search
            document.getElementById('userSearch').addEventListener('input', (e) => {
                filterUsers(e.target.value);
            });
        }

        // Tab Switching
        function switchTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            
            if (tab === 'privileges') {
                document.getElementById('privilegesTab').classList.add('active');
                event.target.classList.add('active');
            } else {
                document.getElementById('assignmentTab').classList.add('active');
                event.target.classList.add('active');
            }
        }

        // Load Privileges
        async function loadPrivileges() {
            try {
                const response = await fetch('/user');
                const result = await response.json();
                privileges = result.data || [];
                renderPrivilegeList();
            } catch (error) {
                console.error('Error loading privileges:', error);
            }
        }

        // Render Privilege List
        function renderPrivilegeList() {
            const container = document.getElementById('privilegeListContainer');
            
            if (!privileges.length) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="icon">📋</div>
                        <p>Belum ada privilege</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = privileges.map(p => `
                <div class="privilege-item ${selectedPrivilege?.id === p.id ? 'active' : ''}" 
                     onclick="selectPrivilege('${p.id}')">
                    <div class="privilege-info">
                        <h4>${p.name}</h4>
                        <span class="privilege-slug">${p.slug}</span>
                    </div>
                </div>
            `).join('');
        }

        // Select Privilege
        function selectPrivilege(id) {
            selectedPrivilege = privileges.find(p => p.id === id);
            renderPrivilegeDetail();
            renderPrivilegeList();
        }

        // Render Privilege Detail
        function renderPrivilegeDetail() {
            const container = document.getElementById('privilegeDetail');
            
            if (!selectedPrivilege) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="icon">🔐</div>
                        <h3>Pilih Privilege</h3>
                        <p>Pilih privilege dari daftar untuk melihat detail</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                    <div>
                        <h2 style="margin-bottom: 10px;">${selectedPrivilege.name}</h2>
                        <span class="privilege-slug" style="font-size: 14px;">${selectedPrivilege.slug}</span>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-primary btn-sm" onclick="editPrivilege('${selectedPrivilege.id}')">
                            ✏️ Edit
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deletePrivilege('${selectedPrivilege.id}')">
                            🗑️ Hapus
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Deskripsi</label>
                    <p style="color: #666;">${selectedPrivilege.description || 'Tidak ada deskripsi'}</p>
                </div>

                <div class="form-group">
                    <label>Dibuat pada</label>
                    <p style="color: #666;">${new Date(selectedPrivilege.created_at).toLocaleString('id-ID')}</p>
                </div>

                <div class="form-group">
                    <label>Terakhir diupdate</label>
                    <p style="color: #666;">${new Date(selectedPrivilege.updated_at).toLocaleString('id-ID')}</p>
                </div>
            `;
        }

        // Load Users
        async function loadUsers() {
            try {
                const response = await fetch('/api/user');
                const result = await response.json();
                users = result.data || [];
                await loadUserPrivileges();
                renderUserGrid();
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }

        // Load User Privileges
        async function loadUserPrivileges() {
            try {
                const response = await fetch('/api/user-privileges');
                const result = await response.json();
                userPrivileges = result.data || [];
            } catch (error) {
                console.error('Error loading user privileges:', error);
            }
        }

        // Render User Grid
        function renderUserGrid(filteredUsers = null) {
            const container = document.getElementById('userGridContainer');
            const displayUsers = filteredUsers || users;

            if (!displayUsers.length) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="icon">👥</div>
                        <p>Tidak ada user ditemukan</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = displayUsers.map(user => {
                const userPrivs = userPrivileges.filter(up => up.user_id === user.id);
                const initials = user.email.substring(0, 2).toUpperCase();
                
                return `
                    <div class="user-card">
                        <div class="user-info">
                            <div class="user-avatar">${initials}</div>
                            <div class="user-details">
                                <h4>${user.email}</h4>
                                <p>
                                    ${userPrivs.length} privilege${userPrivs.length !== 1 ? 's' : ''} 
                                    • 
                                    <span class="badge ${user.active_status ? 'badge-success' : 'badge-danger'}">
                                        ${user.active_status ? 'Active' : 'Inactive'}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="openAssignmentModal('${user.id}')">
                            ⚙️ Manage
                        </button>
                    </div>
                `;
            }).join('');
        }

        // Filter Users
        function filterUsers(searchTerm) {
            const filtered = users.filter(user => 
                user.email.toLowerCase().includes(searchTerm.toLowerCase())
            );
            renderUserGrid(filtered);
        }

        // Load Stats
        async function loadStats() {
            try {
                const response = await fetch('/api/privilege-stats');
                const result = await response.json();
                const stats = result.data || {};

                document.getElementById('totalUsers').textContent = stats.total_users || users.length;
                document.getElementById('totalPrivileges').textContent = stats.total_privileges || privileges.length;
                document.getElementById('totalAssignments').textContent = stats.total_assignments || userPrivileges.length;
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Modals
        function openAddPrivilegeModal() {
            document.getElementById('privilegeModalTitle').textContent = 'Tambah Privilege';
            document.getElementById('privilegeForm').reset();
            document.getElementById('privilegeId').value = '';
            openModal('privilegeModal');
        }

        function editPrivilege(id) {
            const privilege = privileges.find(p => p.id === id);
            if (!privilege) return;

            document.getElementById('privilegeModalTitle').textContent = 'Edit Privilege';
            document.getElementById('privilegeId').value = privilege.id;
            document.getElementById('privilegeName').value = privilege.name;
            document.getElementById('privilegeSlug').value = privilege.slug;
            document.getElementById('privilegeDescription').value = privilege.description || '';
            openModal('privilegeModal');
        }

        function openAssignmentModal(userId) {
            selectedUser = users.find(u => u.id === userId);
            if (!selectedUser) return;

            const userPrivs = userPrivileges.filter(up => up.user_id === userId);
            
            document.getElementById('assignmentModalTitle').textContent = `Assign Privileges - ${selectedUser.email}`;
            document.getElementById('userAssignmentInfo').innerHTML = `
                <div style="background: #f8f9ff; padding: 15px; border-radius: 8px;">
                    <strong>User:</strong> ${selectedUser.email}<br>
                    <strong>Status:</strong>
                    <span class="badge ${selectedUser.active_status ? 'badge-success' : 'badge-danger'}">
                        ${selectedUser.active_status ? 'Active' : 'Inactive'}
                    </span>
                </div>
            `;
            renderPrivilegeCheckboxes(userPrivs);
            openModal('assignmentModal');
        }

        function renderPrivilegeCheckboxes(userPrivs) {
            const container = document.getElementById('privilegeCheckboxes');
            container.innerHTML = privileges.map(p => {
                const isChecked = userPrivs.some(up => up.privilege_id === p.id);
                return `
                    <div class="checkbox-item">
                        <input type="checkbox" id="privCheckbox_${p.id}" value="${p.id}" ${isChecked ? 'checked' : ''}>
                        <label for="privCheckbox_${p.id}">${p.name} <span class="privilege-slug">${p.slug}</span></label>
                    </div>
                `;
            }).join('');
        }
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }
        // Save Privilege
        async function savePrivilege() {
            const name = document.getElementById('privilegeName').value;
            const slug = document.getElementById('privilegeSlug').value;
            const description = document.getElementById('privilegeDescription').value;
            const id = document.getElementById('privilegeId').value;
            const payload = { name, slug, description };

            try {
                let response;
                if (id) {
                    response = await fetch(`/api/privileges/${id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                } else {
                    response = await fetch('/api/privileges', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                }

                if (response.ok) {
                    closeModal('privilegeModal');
                    await loadPrivileges();
                    loadStats();
                } else {
                    const errorData = await response.json();
                    alert('Error: ' + (errorData.message || 'Gagal menyimpan privilege'));
                }
            } catch (error) {
                console.error('Error saving privilege:', error);
                alert('Error: Gagal menyimpan privilege');
            }
        }
        // Delete Privilege
        async function deletePrivilege(id) {
            if (confirm('Apakah Anda yakin ingin menghapus privilege ini?')) {
                try {
                    const response = await fetch(`/api/privileges/${id}`, {
                        method: 'DELETE'
                    });

                    if (response.ok) {
                        selectedPrivilege = null;
                        renderPrivilegeDetail();
                        await loadPrivileges();
                        loadStats();
                    } else {
                        const errorData = await response.json();
                        alert('Error: ' + (errorData.message || 'Gagal menghapus privilege'));
                    }
                } catch (error) {
                    console.error('Error deleting privilege:', error);
                    alert('Error: Gagal menghapus privilege');
                }
            }
        }
        // Save Assignment
        async function saveAssignment() {
            const userPrivs = userPrivileges.filter(up => up.user_id === selectedUser.id);
            const privilegeIds = document.querySelectorAll('input[name="privilege"]:checked').map(input => input.value);
            const newPrivs = privilegeIds.filter(id => !userPrivs.some(up => up.privilege_id === id));
            const deletedPrivs = userPrivs.filter(up => !privilegeIds.includes(up.privilege_id)).map(up => up.privilege_id);

            try {
                const response = await fetch('/api/privilege-assignments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ user_id: selectedUser.id, new_privileges: newPrivs, deleted_privileges: deletedPrivs })
                });

                if (response.ok) {
                    closeModal('assignmentModal');
                    await loadPrivileges();
                    loadStats();
                } else {
                    const errorData = await response.json();
                    alert('Error: ' + (errorData.message || 'Gagal menyimpan assignment'));
                }
            } catch (error) {
                console.error('Error saving assignment:', error);
                alert('Error: Gagal menyimpan assignment');
            }
        }
    </script>
<?= $this->endSection() ?>