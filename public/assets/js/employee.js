/**
 * Employee Management - Optimized for Large Dataset (50K+ records)
 * ERP Pergudangan System
 */

let employees = [];
let filteredEmployees = [];

let currentPage = 1;
let itemsPerPage = 10; // ⬆️ Naik dari 10 ke 50
let deleteEmployeeId = null;

// Virtual scrolling cache
let renderCache = new Map();
let debounceTimer = null;

/* ===========================
   CONVERTER: camelCase → snake_case
=========================== */
function convertToSnakeCase(obj) {
    if (!obj) return obj;
    
    // Check cache dulu
    const cacheKey = JSON.stringify(obj);
    if (renderCache.has(cacheKey)) {
        return renderCache.get(cacheKey);
    }
    
    const converted = {};
    for (const key in obj) {
        const snakeKey = key.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
        converted[snakeKey] = obj[key];
    }
    
    // Simpan ke cache (max 1000 items)
    if (renderCache.size > 1000) {
        const firstKey = renderCache.keys().next().value;
        renderCache.delete(firstKey);
    }
    renderCache.set(cacheKey, converted);
    
    return converted;
}

/* ===========================
   INIT
=========================== */
document.addEventListener("DOMContentLoaded", () => {
    loadEmployees();
    loadStats();
    setupEventListeners();
    initLazyLoading();

    console.log("%cEmployee Module Loaded (Optimized)", "color:#3cb371;font-weight:bold");
});

/* ===========================
   EVENT LISTENER (Debounced)
=========================== */
function setupEventListeners() {

    // Debounce search input (tunggu 500ms setelah user berhenti ngetik)
    document.getElementById("searchInput").addEventListener("input", () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => applyFilters(), 500);
    });
    
    document.getElementById("departmentFilter").addEventListener("change", applyFilters);
    document.getElementById("statusFilter").addEventListener("change", applyFilters);

    const birthDateInput = document.getElementById("birth_date");
    if (birthDateInput) birthDateInput.addEventListener("change", calculateAge);

    document.querySelectorAll(".modal").forEach(modal => {
        modal.addEventListener("click", e => {
            if (e.target === modal) closeModal(modal.id);
        });
    });
}

/* ===========================
   LAZY LOADING TABLE
=========================== */
function initLazyLoading() {
    const tableContainer = document.querySelector(".table-container");
    if (!tableContainer) return;

    let isLoading = false;

    tableContainer.addEventListener("scroll", () => {
        if (isLoading) return;

        const { scrollTop, scrollHeight, clientHeight } = tableContainer;
        
        // Jika scroll hampir sampai bawah (80% dari total height)
        if (scrollTop + clientHeight >= scrollHeight * 0.8) {
            isLoading = true;
            
            // Load page berikutnya jika masih ada
            const nextPage = currentPage + 1;
            const maxPage = Math.ceil(filteredEmployees.length / itemsPerPage);
            
            if (nextPage <= maxPage) {
                loadEmployees(nextPage);
            }
            
            setTimeout(() => { isLoading = false; }, 500);
        }
    });
}

/* ===========================
   LOAD EMPLOYEE DATA (Server-side Pagination)
=========================== */
function loadEmployees(page = 1) {

    currentPage = page;

    // Show loading indicator
    showLoadingIndicator();

    const params = new URLSearchParams({
        search: document.getElementById("searchInput").value,
        department: document.getElementById("departmentFilter").value,
        employee_status: document.getElementById("statusFilter").value,
        page: page,
        per_page: itemsPerPage
    });

    fetch(`/employees/data?${params}`, {
        credentials: "include"
    })
    .then(res => res.json())
    .then(res => {

        console.log("📥 API Response (Page " + page + "):", res);

        // Handle berbagai format response
        let rawData = [];
        
        if (res.status === "success" && res.data) {
            rawData = res.data;
        } else if (Array.isArray(res.data)) {
            rawData = res.data;
        } else if (Array.isArray(res)) {
            rawData = res;
        }

        // Convert HANYA data yang tampil (bukan semua 50k sekaligus!)
        const convertedData = rawData.map(emp => convertToSnakeCase(emp));
        
        employees = convertedData;
        filteredEmployees = convertedData;

        // Render dengan requestAnimationFrame untuk smooth UI
        requestAnimationFrame(() => {
            renderTable();
            hideLoadingIndicator();
        });

        // Handle pagination
        if (res.pagination) {
            renderPaginationFromServer(res.pagination);
            updateTableInfoFromServer(res.pagination);
        }
    })
    .catch(err => {
        console.error("❌ Fetch error:", err);
        hideLoadingIndicator();
        showError("Gagal memuat data karyawan");
    });
}


/* ===========================
   RENDER TABLE (Optimized)
=========================== */
function renderTable() {

    const tbody = document.getElementById("employeeTableBody");

    if (!filteredEmployees.length) {
        tbody.innerHTML = `
        <tr>
            <td colspan="13" style="text-align:center; padding:40px;">
                <div style="font-size:40px;margin-bottom:5px">📭</div>
                Tidak ada data karyawan
            </td>
        </tr>`;
        return;
    }

    // Gunakan DocumentFragment untuk batch insert (MUCH faster!)
    const fragment = document.createDocumentFragment();
    
    filteredEmployees.forEach((e, i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${i + 1 + (currentPage - 1) * itemsPerPage}</td>
            <td><strong>${e.employee_number || e.bis_id || "-"}</strong></td>
            <td>${e.employee_name || "-"}</td>
            <td>${e.gender || "-"}</td>
            <td>${e.department || "-"}</td>
            <td>${e.division || "-"}</td>
            <td>${e.job_level || e.sub_job_level || "-"}</td>
            <td>${e.group_name || "-"}</td>
            <td>${badgeEmployment(e.employment_status)}</td>
            <td>${badgeStatus(e.employee_status)}</td>
            <td>${formatMasaKerja(e.join_date)}</td>
            <td>${e.site_name || "-"}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action view" onclick="viewEmployee('${e.id}')">👁️</button>
                </div>
            </td>
        `;
        fragment.appendChild(tr);
    });

    // Single DOM update (bukan 50 kali update!)
    tbody.innerHTML = '';
    tbody.appendChild(fragment);
}

/* ===========================
   BADGES (Cached)
=========================== */
const badgeCache = new Map();

function badgeEmployment(s) {
    if (!s) return `<span class="badge badge-info">-</span>`;
    
    if (badgeCache.has(s)) return badgeCache.get(s);
    
    const status = s.toUpperCase();
    let badge;
    
    if (status.includes("PERMANENT") || status.includes("TETAP")) {
        badge = `<span class="badge badge-success">${s}</span>`;
    } else if (status.includes("CONTRACT") || status.includes("PKWT")) {
        badge = `<span class="badge badge-warning">${s}</span>`;
    } else if (status.includes("PROBATION") || status.includes("PERCOBAAN")) {
        badge = `<span class="badge badge-info">${s}</span>`;
    } else {
        badge = `<span class="badge badge-info">${s}</span>`;
    }
    
    badgeCache.set(s, badge);
    return badge;
}

function badgeStatus(s) {
    if (!s) return `<span class="badge badge-info">-</span>`;
    
    if (badgeCache.has(s)) return badgeCache.get(s);
    
    const status = s.toUpperCase();
    let badge;
    
    if (status.includes("ACTIVE") || status.includes("AKTIF")) {
        badge = `<span class="badge badge-success">${s}</span>`;
    } else if (status.includes("INACTIVE") || status.includes("NON")) {
        badge = `<span class="badge badge-warning">${s}</span>`;
    } else if (status.includes("RESIGN") || status.includes("KELUAR")) {
        badge = `<span class="badge badge-danger">${s}</span>`;
    } else {
        badge = `<span class="badge badge-info">${s}</span>`;
    }
    
    badgeCache.set(s, badge);
    return badge;
}

/* ===========================
   PAGINATION (Optimized)
=========================== */
function renderPaginationFromServer(p) {

    const el = document.getElementById("pagination");
    el.innerHTML = "";

    const maxButtons = 7; // Tampilkan max 7 tombol
    const totalPages = p.total_pages;
    const currentPage = p.page;

    // Hitung range halaman yang ditampilkan
    let startPage = Math.max(1, currentPage - 3);
    let endPage = Math.min(totalPages, startPage + maxButtons - 1);

    if (endPage - startPage < maxButtons - 1) {
        startPage = Math.max(1, endPage - maxButtons + 1);
    }

    // First page
    if (startPage > 1) {
        el.innerHTML += `<button onclick="loadEmployees(1)">« First</button>`;
    }

    // Previous
    if (currentPage > 1) {
        el.innerHTML += `<button onclick="loadEmployees(${currentPage - 1})">‹</button>`;
    }

    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
        el.innerHTML += `
            <button class="${i === currentPage ? "active" : ""}"
                    onclick="loadEmployees(${i})">${i}</button>
        `;
    }

    // Next
    if (currentPage < totalPages) {
        el.innerHTML += `<button onclick="loadEmployees(${currentPage + 1})">›</button>`;
    }

    // Last page
    if (endPage < totalPages) {
        el.innerHTML += `<button onclick="loadEmployees(${totalPages})">Last »</button>`;
    }
}

function updateTableInfoFromServer(p) {
    const start = (p.page - 1) * p.per_page + 1;
    const end = Math.min(p.page * p.per_page, p.total);
    
    document.getElementById("showingCount").textContent = `${start}-${end}`;
    document.getElementById("totalCount").textContent = p.total;
}

/* ===========================
   LOADING INDICATOR
=========================== */
function showLoadingIndicator() {
    const tbody = document.getElementById("employeeTableBody");
    tbody.innerHTML = `
        <tr>
            <td colspan="13" style="text-align:center; padding:20px;">
                <div class="spinner"></div>
                <p>Memuat data...</p>
            </td>
        </tr>
    `;
}

function hideLoadingIndicator() {
    // Loading akan otomatis hilang saat renderTable() dipanggil
}

/* ===========================
   FILTER (Debounced)
=========================== */
function applyFilters() {
    loadEmployees(1);
}

/* ===========================
   STATISTICS (Cached)
=========================== */
let statsCache = null;
let statsCacheTime = 0;
const STATS_CACHE_DURATION = 60000; // 1 menit

function loadStats() {
    
    // Gunakan cache jika masih fresh
    const now = Date.now();
    if (statsCache && (now - statsCacheTime) < STATS_CACHE_DURATION) {
        updateStatsUI(statsCache);
        return;
    }

    fetch(`/employees/statistics`)
        .then(res => res.json())
        .then(res => {
            const data = res.data || res;
            
            statsCache = data;
            statsCacheTime = Date.now();
            
            updateStatsUI(data);
        })
        .catch(err => console.error("Stats error:", err));
}

function updateStatsUI(data) {
    document.getElementById("totalEmployees").textContent = data.total || 0;
    document.getElementById("activeEmployees").textContent = data.active || 0;
    document.getElementById("inactiveEmployees").textContent = data.inactive || 0;
    document.getElementById("newEmployees").textContent = data.new_this_month || data.newThisMonth || 0;
}

/* ===========================
   VIEW EMPLOYEE DETAIL
=========================== */
function viewEmployee(id) {

    fetch(`/employees/view/${id}`)
        .then(res => res.json())
        .then(res => {

            let rawData = res.data || res;
            const e = convertToSnakeCase(rawData);

            document.getElementById("employeeDetail").innerHTML = `
                <div class="detail-grid">

                    <div class="detail-section">
                        <h3>📋 Informasi Dasar</h3>
                        <p><b>NIK:</b> ${e.employee_number || e.bis_id || "-"}</p>
                        <p><b>Nama:</b> ${e.employee_name || "-"}</p>
                        <p><b>Gender:</b> ${e.gender || "-"}</p>
                        <p><b>Tempat Lahir:</b> ${e.place_of_birth || "-"}</p>
                        <p><b>Tanggal Lahir:</b> ${formatDate(e.birth_date)}</p>
                        <p><b>Golongan Darah:</b> ${e.blood_type || "-"}</p>
                        <p><b>Agama:</b> ${e.religion || "-"}</p>
                    </div>

                    <div class="detail-section">
                        <h3>🏢 Informasi Pekerjaan</h3>
                        <p><b>Department:</b> ${e.department || "-"}</p>
                        <p><b>Division:</b> ${e.division || "-"}</p>
                        <p><b>Job Level:</b> ${e.job_level || e.sub_job_level || "-"}</p>
                        <p><b>Group:</b> ${e.group_name || "-"} (Level ${e.group_level || "-"})</p>
                        <p><b>Section:</b> ${e.section || "-"}</p>
                    </div>

                    <div class="detail-section">
                        <h3>📅 Status & Tanggal</h3>
                        <p><b>Employment:</b> ${badgeEmployment(e.employment_status)}</p>
                        <p><b>Status:</b> ${badgeStatus(e.employee_status)}</p>
                        <p><b>Join Date:</b> ${formatDate(e.join_date)}</p>
                        <p><b>Masa Kerja:</b> ${formatMasaKerja(e.join_date)}</p>
                        <p><b>Pendidikan Terakhir:</b> ${e.last_education || "-"}</p>
                    </div>

                    <div class="detail-section">
                        <h3>📍 Lokasi & Kontak</h3>
                        <p><b>Site:</b> ${e.site_name || "-"}</p>
                        <p><b>Place of Hire:</b> ${e.place_of_hire || "-"}</p>
                        <p><b>Alamat:</b> ${e.address || "-"}</p>
                        <p><b>No. HP:</b> ${e.phone_number || "-"}</p>
                        <p><b>Kontak Darurat:</b> ${e.emergency_contact_name || "-"}</p>
                        <p><b>No. Darurat:</b> ${e.emergency_number || "-"}</p>
                    </div>

                </div>
            `;

            openModal("viewModal");
        })
        .catch(err => {
            console.error("View error:", err);
            showError("Gagal memuat detail karyawan");
        });
}

/* ===========================
   EXPORT
=========================== */
function exportData() {
    // Tambahkan loading indicator
    const btn = event.target;
    btn.disabled = true;
    btn.textContent = "Exporting...";
    
    window.location.href = "/employees/export";
    
    // Re-enable button setelah 3 detik
    setTimeout(() => {
        btn.disabled = false;
        btn.textContent = "Export Data";
    }, 3000);
}

/* ===========================
   HELPERS
=========================== */
function formatDate(d) {
    if (!d) return "-";
    return new Date(d).toLocaleDateString("id-ID", {
        year:"numeric", month:"long", day:"numeric"
    });
}

function formatMasaKerja(joinDate) {
    if (!joinDate) return "-";

    const start = new Date(joinDate);
    const now = new Date();

    let years = now.getFullYear() - start.getFullYear();
    let months = now.getMonth() - start.getMonth();

    if (months < 0) {
        years--;
        months += 12;
    }

    return `${years} thn ${months} bln`;
}

function showError(msg) {
    alert(msg);
}

/* ===========================
   MODAL CONTROL
=========================== */
function openModal(id){
    document.getElementById(id).classList.add("show");
    document.body.style.overflow = "hidden";
}

function closeModal(id){
    document.getElementById(id).classList.remove("show");
    document.body.style.overflow = "auto";
}

/* ===========================
   AGE AUTO CALCULATE
=========================== */
function calculateAge() {
    const birth = document.getElementById("birth_date").value;
    if (!birth) return;

    const b = new Date(birth);
    const t = new Date();

    let age = t.getFullYear() - b.getFullYear();
    const diff = t.getMonth() - b.getMonth();

    if (diff < 0 || (diff === 0 && t.getDate() < b.getDate())) age--;

    document.getElementById("age").value = age;
}